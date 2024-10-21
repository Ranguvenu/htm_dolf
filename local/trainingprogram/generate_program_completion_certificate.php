<?php
require_once('../../config.php');
global $DB, $CFG, $OUTPUT,$USER, $PAGE;
use tool_certificate\template;
$successmessage= '';
$recordids='';
$sql_query = "SELECT pe.* FROM {program_completions} pe WHERE pe.completion_status IN (1,2) AND pe.offeringid NOT IN (SELECT moduleid  FROM {tool_certificate_issues} WHERE  userid = pe.userid AND moduletype= 'trainingprogram') ";
$allcompletionrecords = $DB->get_records_sql( $sql_query);
foreach($allcompletionrecords AS $record) {
    $sql = "SELECT  COUNT(id) FROM {program_completions} WHERE programid=:programid AND offeringid =:offeringid AND userid =:userid";
    $recordscount =  $DB->count_records_sql($sql,['programid'=>$record->programid,'offeringid'=>$record->offeringid,'userid'=>$record->userid]);  
    if($recordscount == 1) {
        $recordid = $record->id;
    } else {
        $recordid=$DB->get_field_sql("SELECT id FROM {program_completions} WHERE programid= $record->programid AND offeringid =$record->offeringid AND userid =$record->userid ORDER BY id DESC LIMIT 1");
    }
    $recordids.= $recordid.',';

}
$recordids = explode(',',rtrim(implode(',',array_unique(explode(',',$recordids))),','));
foreach($recordids AS $rid) {
    $eventdata = $DB->get_record('program_completions',array('id'=>$rid));
    $programcompletions=$DB->get_record('program_completions', array('id'=>$rid));
    $userid = $programcompletions->userid;
    if ($eventdata->userid == $userid && ($programcompletions->completion_status == 1 || $programcompletions->completion_status == 2  )) {
        $courseid = $DB->get_field('local_trainingprogram','courseid',array('id'=>$eventdata->programid));
        $categoryid = $DB->get_field('course_categories', 'id', ['idnumber' => 'trainingprogram']);
        if($courseid && $categoryid){
            $contextid=context_coursecat::instance($categoryid);
            if($contextid){
                $prgrm_certificate = $DB->get_field('tool_certificate_templates','id',array('contextid'=>$contextid->id));
                if(!empty($prgrm_certificate)){
                  // self::issue_certificate( $eventdata->userid, $programcompletions->offeringid, 'trainingprogram', $prgrm_certificate,$eventdata->completion_status,$expiresdate=0);
                    $moduleid = $programcompletions->offeringid;
                    $certificateid = $prgrm_certificate;
                    $userid = $eventdata->userid;
                    $moduletype = 'trainingprogram';

                    try{
                        $dataobj = new stdClass();
            
                        $dataobj->userid = $userid;
                        $dataobj->templateid = $certificateid;
                        $dataobj->code = \tool_certificate\certificate::generate_code($dataobj->userid);
                        $dataobj->moduletype = $moduletype;
                        $dataobj->moduleid = $moduleid;
                        $dataobj->emailed = 0;
                        $dataobj->component = 'tool_certificate';
                        $dataobj->courseid = 0;
                        $dataobj->timecreated = time();
                        $dataobj->usercreated = $USER->id;
                        $dataobj->timemodified = time();
                        $dataobj->usermodified = $USER->id;
                        $dataobj->programid = ($moduletype == 'trainingprogram') ? $DB->get_field('tp_offerings','trainingid',['id'=>$moduleid]): $moduleid;
                        $localuserdata=$DB->get_record('local_users',array('userid'=>$userid));            
                        $data['userfullname'] = ($localuserdata) ? ((current_language() == 'ar') ? $localuserdata->firstnamearabic.' '.$localuserdata->middlenamearabic.' '.$localuserdata->thirdnamearabic.' '.$localuserdata->lastnamearabic  :$localuserdata->firstname.' '.$localuserdata->middlenameen.' '.$localuserdata->thirdnameen.' '.$localuserdata->lastname)  : fullname($DB->get_record('user', ['id' => $userid]));
                        $dataobj->data = json_encode($data);
            
                        if($moduletype=="exams") {
                            if($expiresdate > 0) {
                                $dataobj->expires = strtotime(date('Y-m-d', strtotime('+'.$expiresdate.' years',$dataobj->timecreated)));
                            } else {
                                $dataobj->expires = strtotime(date('Y-m-d', strtotime('+1 years',$dataobj->timecreated)));
                            }
                        } else {
                            $dataobj->expires = "";
                        }
                        $array = array('userid'=>$userid,'moduleid'=>$moduleid,
                                        'moduletype'=>$moduletype);
                        $exist_recordid = $DB->get_record('tool_certificate_issues',$array, 'id');

                        if($exist_recordid){
            
                            if($programcompletions->completion_status == 0){
            
                                $DB->delete_records('tool_certificate_issues', ['id' => $exist_recordid->id]);
            
                            }
            
                            $dataobj->id = $exist_recordid->id;
                            $DB->update_record('tool_certificate_issues',$dataobj);
            
                        }elseif($programcompletions->completion_status > 0){
            
                            $DB->insert_record('tool_certificate_issues',$dataobj);
                        }
                        if($record) {
                            $certissues = $DB->get_record('tool_certificate_issues', array('moduleid'=>$moduleid,'userid'=>$userid,'moduletype'=> $moduletype));

                            $template = template::instance($certissues->templateid);
                            $issue = new \stdClass();
                            $issue->id = $certissues->id;
                            $issue->userid = $userid;
                            $issue->templateid = $certissues->templateid;
                            $issue->code = $certissues->code;
                            $issue->emailed = 0;
                            $issue->timecreated = time();
                            $issue->expires = 0;
                            $issue->component = 'tool_certificate';
                            $issue->courseid = $courseid;
                            $issue->moduletype = $moduletype;
                            $issue->moduleid = $moduleid;
                            // Store user fullname.
                            $data['userfullname'] = fullname($DB->get_record('user', ['id' => $userid]));
                            $issue->data = json_encode($data);
                            // Create the issue file and send notification.
                            $issuefile = $template->create_issue_file($issue);

                            $programcode = $DB->get_field('local_trainingprogram','code',['id'=>$programcompletions->programid]);
                            $offeringcode = $DB->get_field('tp_offerings','code',['id'=>$programcompletions->offeringid]);

                            $successmessage.="New certificate generated for <b>".$data['userfullname']." ($userid)</b> on behalf of program <b>".$programcode."</b> having offering code <b>".$offeringcode."</b>".'</br>'; 
                        }
                     
                    }catch(exception $e){
                        print_object($e);
                    }

                    $traineesql = "SELECT ra.id
                    FROM {role_assignments} ra 
                    JOIN {role} as r ON r.id = ra.roleid
                    WHERE ra.contextid = 1 AND r.shortname = 'trainee' AND ra.userid = ". $eventdata->userid;
                    $traineerole = $DB->get_field_sql($traineesql);
                    if($traineerole){

                        $certid = $DB->get_field('tool_certificate_issues', 'code', array('moduleid'=>$eventdata->programid,'userid'=>$eventdata->userid,'moduletype'=>'trainingprogram'));

                        $program=new \stdClass();

                        $localuserdata=$DB->get_record('local_users',array('userid'=> $eventdata->userid));
                        $program->program_name=$DB->get_field('local_trainingprogram','name',array('id'=>$eventdata->programid));
                        $program->program_arabicname=$DB->get_field('local_trainingprogram','namearabic',array('id'=>$eventdata->programid));
                        $program->program_arabicuserfullname =$localuserdata->firstnamearabic.' '.$localuserdata->middlenamearabic.' '.$localuserdata->thirdnamearabic.' '.$localuserdata->lastnamearabic;
                        $program->program_userfullname= $localuserdata->firstname.' '.$localuserdata->middlenameen.' '.$localuserdata->thirdnameen.' '.$localuserdata->lastname;
                        $program->program_certificatelink=$CFG->wwwroot.'/admin/tool/certificate/view.php?code='.$certid.'';  
                        $trainee=$DB->get_record('user',array('id'=> $eventdata->userid));   
                        (new \local_trainingprogram\notification())->trainingprogram_notification('trainingprogram_certificate_assignment', $touser=$trainee,$fromuser=$USER,$program,$waitinglistid=0);
                    }

                }
            }

        }

    }

}
echo $successmessage;
    
