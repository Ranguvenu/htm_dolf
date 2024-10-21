<?php
// This file is part of the tool_certificate plugin for Moodle - http://moodle.org/
//
// Moodle is free software: you can redistribute it and/or modify
// it under the terms of the GNU General Public License as published by
// the Free Software Foundation, either version 3 of the License, or
// (at your option) any later version.
//
// Moodle is distributed in the hope that it will be useful,
// but WITHOUT ANY WARRANTY; without even the implied warranty of
// MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
// GNU General Public License for more details.
//
// You should have received a copy of the GNU General Public License
// along with Moodle.  If not, see <http://www.gnu.org/licenses/>.

/**
 * Class observer for tool_certificate.
 *
 * @package    tool_certificate
 * @author     2020 Mikel Martín <mikel@moodle.com>
 * @copyright  2020 Moodle Pty Ltd <support@moodle.com>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

/**
 * Class tool_certificate_observer
 *
 * @package    tool_certificate
 * @author     2020 Mikel Martín <mikel@moodle.com>
 * @copyright  2020 Moodle Pty Ltd <support@moodle.com>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

use tool_certificate\template;

class tool_certificate_observer {

    /**
     * Course deleted observer
     *
     * @param \core\event\course_content_deleted $event
     */
    public static function on_course_content_deleted(\core\event\course_content_deleted $event): void {
        global $DB;

        $fs = get_file_storage();
        $issues = $DB->get_records('tool_certificate_issues', ['courseid' => $event->courseid]);
        foreach ($issues as $issue) {
            $fs->delete_area_files(context_system::instance()->id, 'tool_certificate', 'issues', $issue->id);
        }

        $DB->delete_records('tool_certificate_issues', ['courseid' => $event->courseid]);

    }
    //eabyas added local_certificate abserver data
    /**
     * Triggered when program completed for a user
     *
     * @param \local_trainingprogram\event\trainingprogram_completion_updated $event
     */
    public static function issue_trainingprogram_certificate(\local_trainingprogram\event\trainingprogram_completion_updated $event) {
        global $DB,$CFG,$USER;

        $eventdata = $event->get_record_snapshot('program_completions', $event->objectid);

        $programcompletions=$DB->get_record('program_completions', array('id'=>$event->objectid));

        $userid = $event->relateduserid;

        if ($eventdata->userid == $userid && ($programcompletions->completion_status == 1 || $programcompletions->completion_status == 2)) {


            $courseid = $DB->get_field('local_trainingprogram','courseid',array('id'=>$eventdata->programid));

            $categoryid = $DB->get_field('course_categories', 'id', ['idnumber' => 'trainingprogram']);

            if($courseid && $categoryid){


                $contextid=context_coursecat::instance($categoryid);

                if($contextid){

                    $prgrm_certificate = $DB->get_field('tool_certificate_templates','id',array('contextid'=>$contextid->id));
                    
                    if(!empty($prgrm_certificate)){
                        self::issue_certificate($event->relateduserid, $programcompletions->offeringid, 'trainingprogram', $prgrm_certificate,$eventdata->completion_status,$expiresdate=0);

                        $traineesql = "SELECT ra.id
                        FROM {role_assignments} ra 
                        JOIN {role} as r ON r.id = ra.roleid
                        WHERE ra.contextid = 1 AND r.shortname = 'trainee' AND ra.userid = ".$event->relateduserid;
                        $traineerole = $DB->get_field_sql($traineesql);
                        if($traineerole){

                            $certid = $DB->get_field('tool_certificate_issues', 'code', array('moduleid'=>$eventdata->programid,'userid'=>$event->relateduserid,'moduletype'=>'trainingprogram'));

                            $program=new \stdClass();

                            $localuserdata=$DB->get_record('local_users',array('userid'=>$event->relateduserid));
                            $program->program_name=$DB->get_field('local_trainingprogram','name',array('id'=>$eventdata->programid));
                            $program->program_arabicname=$DB->get_field('local_trainingprogram','namearabic',array('id'=>$eventdata->programid));
                            $program->program_arabicuserfullname =$localuserdata->firstnamearabic.' '.$localuserdata->middlenamearabic.' '.$localuserdata->thirdnamearabic.' '.$localuserdata->lastnamearabic;
                            $program->program_userfullname= $localuserdata->firstname.' '.$localuserdata->middlenameen.' '.$localuserdata->thirdnameen.' '.$localuserdata->lastname;
                            $program->program_certificatelink=$CFG->wwwroot.'/admin/tool/certificate/view.php?code='.$certid.'';  
                            $trainee=$DB->get_record('user',array('id'=>$event->relateduserid));   
                            (new \local_trainingprogram\notification())->trainingprogram_notification('trainingprogram_certificate_assignment', $touser=$trainee,$fromuser=$USER,$program,$waitinglistid=0);
                        }

                    }
                }

            }

        }

    }
    /**
     * Triggered when exam completed for a user
     *
     * @param \local_exams\event\exam_completion_updated $event
     */
    public static function issue_exam_certificate(\local_exams\event\exam_completion_updated $event) {
        global $DB,$CFG,$USER;

        $eventdata = $event->get_record_snapshot('exam_completions', $event->objectid);


        $userid = $event->relateduserid;

        if ($eventdata->userid == $userid && ($eventdata->completion_status == 1 || $eventdata->completion_status == 2)) {

            $exams = $DB->get_record('local_exams',array('id'=>$eventdata->examid),'courseid,certificatevalidity');

            $categoryid = $DB->get_field('course_categories', 'id', ['idnumber' => 'exams']);

            if($exams->courseid && $categoryid){


                $contextid=context_coursecat::instance($categoryid);

                if($contextid){

                    $exm_certificate = $DB->get_field('tool_certificate_templates','id',array('contextid'=>$contextid->id));
                    
                    if(!empty($exm_certificate)){

                        self::issue_certificate($event->relateduserid,$eventdata->examid, 'exams', $exm_certificate,$eventdata->completion_status,$exams->certificatevalidity);


                        $traineesql = "SELECT ra.id
                        FROM {role_assignments} ra 
                        JOIN {role} as r ON r.id = ra.roleid
                        WHERE ra.contextid = 1 AND r.shortname = 'trainee' AND ra.userid = ".$event->relateduserid;
                        $traineerole = $DB->get_field_sql($traineesql);
                        if($traineerole){

                            $certid = $DB->get_field('tool_certificate_issues', 'code', array('moduleid'=>$eventdata->examid,'userid'=>$event->relateduserid,'moduletype'=>'exams'));

                            $localuserdata=$DB->get_record('local_users',array('userid'=>$event->relateduserid));
                            $exm_certificate_notificaton=new \stdClass();                       
                            $exm_certificate_notificaton->exam_arabicuserfullname =$localuserdata->firstnamearabic.' '.$localuserdata->middlenamearabic.' '.$localuserdata->thirdnamearabic.' '.$localuserdata->lastnamearabic;
                            $exm_certificate_notificaton->exam_userfullname= $localuserdata->firstname.' '.$localuserdata->middlenameen.' '.$localuserdata->thirdnameen.' '.$localuserdata->lastname;
                            $exm_certificate_notificaton->exam_name=$DB->get_field('local_exams','exam',array('id'=>$eventdata->examid));
                            $exm_certificate_notificaton->exam_certificatelink=$CFG->wwwroot.'/admin/tool/certificate/view.php?code='.$certid.'';   
                            $trainee=$DB->get_record('user',array('id'=>$event->relateduserid));                    
                            (new \local_exams\notification())->exams_notification('exams_certificate_assignment', $touser=$trainee,$fromuser=$USER,$exm_certificate_notificaton,$waitinglistid=0);
                        }
                    }
                }

            }

        }
    }

    public static function issue_event_certificate(\local_events\event\events_completions $event) {
        global $DB,$CFG;
        $eventdata = $DB->get_record('local_events',array('id'=> $event->objectid), 'id, certificate');
        $categoryid = $DB->get_field('course_categories', 'id', ['idnumber' => 'events']);
     
        if($eventdata->id  && $eventdata->certificate > 0 && $categoryid) {
            $contextid = context_coursecat::instance($categoryid);
                if($contextid){

                    // if($eventdata->certificate) {
                    //     $event_certificate = $DB->get_field('tool_certificate_templates','id',array('id' => $eventdata->certificate));
                    // } else {
                    //     $event_certificate = $DB->get_field('tool_certificate_templates','id',array('contextid' => $contextid->id));
                    // }

                    if($eventdata->certificate) {
                        $event_certificate = $DB->get_field('tool_certificate_templates','id',array('contextid' => $contextid->id));
                    }
                    if(!empty($event_certificate)) {
                        $event_attendees = $DB->get_records('local_event_attendees',array('eventid'=> $eventdata->id));
                        if($event_attendees) {
                            foreach($event_attendees as $user) {
                                self::issue_certificate($user->userid, $eventdata->id, 'events', $event_certificate, 1, $expiresdate=0);
                
                            }
                        }

                    }
                    

                }

        }

    }
     /**
     * Triggered when exam completed for a user
     *
     * @param \local_exams\event\exam_completion_updated $event
     */
    public static function issue_learningtrack_certificate(\local_learningtracks\event\learningtracks_completion_updated $event) {
        global $DB,$CFG,$USER;


        $eventdata =$DB->get_record('local_lts_enrolment',array('id'=> $event->objectid));


        $userid = $event->relateduserid;

        if ($eventdata->userid == $userid) {

            $learningtracks = $DB->get_record('local_learningtracks',array('id'=>$eventdata->trackid));


            $categoryid = $DB->get_field('course_categories', 'id', ['idnumber' => 'learningtracks']);


            if($learningtracks && $categoryid){


                $contextid=context_coursecat::instance($categoryid);


                if($contextid){

                    $learningtrack_certificate = $DB->get_field('tool_certificate_templates','id',array('contextid'=>$contextid->id));

                    
                    if(!empty($learningtrack_certificate)){

                        self::issue_certificate($event->relateduserid,$eventdata->trackid, 'learningtracks', $learningtrack_certificate,$eventdata->status,$expiresdate=0);
     
                    }
                }

            }

        }
    }
    private static function issue_certificate($userid, $moduleid, $moduletype, $certificateid,$completion_status,$expiresdate=0){
        global $DB, $USER;
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

            if($moduletype=="exams")
            {
                if($expiresdate > 0)
		 {
                	$dataobj->expires = strtotime(date('Y-m-d', strtotime('+'.$expiresdate.' years',$dataobj->timecreated)));
                } 
		else 
		{
                    $dataobj->expires = strtotime(date('Y-m-d', strtotime('+1 years',$dataobj->timecreated)));
                }
            }
            else
            {
                $dataobj->expires = "";
            }


            
            $array = array('userid'=>$userid,'moduleid'=>$moduleid,
                            'moduletype'=>$moduletype);
            $exist_recordid = $DB->get_record('tool_certificate_issues',$array, 'id');
            if($exist_recordid){

                if($completion_status == 0){

                    $DB->delete_records('tool_certificate_issues', ['id' => $exist_recordid->id]);

                }

                $dataobj->id = $exist_recordid->id;
                $DB->update_record('tool_certificate_issues',$dataobj);

            }elseif($completion_status > 0){

                $dataobj->id = $DB->insert_record('tool_certificate_issues',$dataobj);
            }
        
           // if($record) {
                // self::storing_certificate($moduleid, $userid, $moduletype);
           // }
         
        }catch(exception $e){
            print_object($e);
        }
    }
// missmatch certificate code
    /*public function storing_certificate($moduleid, $userid, $type) {
        global $DB;
        $certissues = $DB->get_record('tool_certificate_issues', array('moduleid'=>$moduleid,'userid'=>$userid,'moduletype'=> $type));
        if(empty($certissues)) {
            return false;
        }
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
        $issue->courseid = 0;
        $issue->moduletype = $type;
        $issue->moduleid = $moduleid;
        // Store user fullname.
        $data['userfullname'] = fullname($DB->get_record('user', ['id' => $userid]));
        $issue->data = json_encode($data);
        // Create the issue file and send notification.
        $issuefile = $template->create_issue_file($issue);

    }*/
}
