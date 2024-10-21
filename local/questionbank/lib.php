<?php

// This file is part of Moodle - http://moodle.org/
//
// Moodle is free software: you can redistribute it and/or localify
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
 *
 * @package    local_cpd
 * @category   external
 * @copyright  eAbyas <www.eabyas.in>
 */

defined('MOODLE_INTERNAL') || die;
//use qbank_bulkmove;
class questionbank {
    public function create_questionbank($data) {
        global $DB,$CFG,$COURSE,$USER;
		$systemcontext = context_system::instance();
        require_once($CFG->dirroot."/question/editlib.php");
        require_once($CFG->dirroot."/question/category_class.php");
        $question = new stdClass();
        if(empty($data->id) || $data->id < 1){
            $question->workshopname = $data->workshopname;
            if(is_siteadmin()){
                //$data->workshopadmin = array_filter( $data->workshopadmin);
                $workshopadmin = implode(',',array_filter($data->workshopadmin));
                $question->workshopadmin =  $workshopadmin;// $data->workshopadmin;
            }else{
                 $question->workshopadmin = $USER->id;
            }
            $question->noofquestions =  $data->noofquestions;
            $question->workshopdate =  $data->startdate;   
            $time = ($data->starttime['hours'] * 3600) + ($data->starttime['minutes'] * 60);
           // $starttime = ceil($data->startdate + $time );
            $question->workshopstarttime =  $time;
            //$question->workshopendtime =  ceil($data->starttime + $data->duration );
            $question->duration =  $data->duration;
            $question->halladdress =  $data->halladdress;
            $question->status =  $data->status;
            $question->usermodified =  $USER->id;
            $question->timemodified =  time();
            $question->generatecode= $data->generatecode;
            $insert->id = $DB->insert_record('local_questionbank',$question);

            $draftrecords = $DB->get_records('reservations_draft', ['entitycode' => $data->entitycode]);
            foreach($draftrecords AS $draftrecord) {
                $draftdata = new stdClass();
                $draftdata->typeid = $insert->id;
                $draftdata->hallid = $draftrecord->hallid;
                $draftdata->seats = $draftrecord->seats;
                $draftdata->examdate = $draftrecord->date;
                $draftdata->slotstart = $draftrecord->slotstart;
                $draftdata->slotend = $draftrecord->slotend;
                $draftdata->userid = $draftrecord->userid;
                $draftdata->type = 'questionbank';
                $draftdata->status = 1;
                $DB->insert_record('hall_reservations', $draftdata);
            }
            $sessionkey = $DB->delete_records('reservations_draft', ['entitycode' => $data->entitycode, 'type' => 'questionbank']);

             // Trigger create questionbank.
             $eventparams = array('context' => context_system::instance(),'objectid'=>$insert->id);
             $event = local_questionbank\event\create_questionbank::create($eventparams);
             $event->trigger();
             //-----------------------
             $start_date=date('d.m.Y', $data->startdate);
             $row=[];
             $row['WorkshopName']=$data->workshopname;
             $row['QuestionBankName']=$data->workshopname;
             $row['WorkshopDate']=$start_date;
             $row['WorkshopTime']=date("H:i:s",$time);
            $myobject=(new \local_questionbank\notification);
            $myobject->questionbank_notification('questionbank_workshop_created',$touser=null, $USER,$row,$waitinglistid=0);
            //-----------------------
             $currentlang= current_language();
            $fullname = (new \local_trainingprogram\local\trainingprogram)->user_fullname_case();
            $exam_official=$DB->get_fieldset_sql("SELECT $fullname FROM  {user} AS u
            JOIN {local_users} AS lc ON lc.userid = u.id WHERE u.id IN ($question->workshopadmin)");
            if(!empty($exam_official)){
           $examofficaillist = implode(',',$exam_official);
           }else{
               $examofficaillist = 'No Official'; 
            }
            $row=[];
             $row['QuestionBankName']=$data->workshopname;
             $row['ExamofficialName']=$examofficaillist;
           $myobject=(new \local_questionbank\notification);
           $myobject->questionbank_notification('questionbank_assign_exam_official',$touser, $USER,$row,$waitinglistid=0);
            

        }else{
            $question->id= $data->id;
            if(!empty($data->courses)){
                $selectedcourses = array_filter($data->courses);
                $question->course= implode(',',$selectedcourses);
            }else{
                $question->course = '';
            }
            $question->competency= !empty($data->competencylevel) ? implode(',', $data->competencylevel) : '';
            $insert->id = $DB->update_record('local_questionbank',$question);
            // Trigger update questionbank.
            $eventparams = array('context' => context_system::instance(),'objectid'=>$insert->id);
            $event = local_questionbank\event\update_questionbank::create($eventparams);
            $event->trigger();          
            $data = $DB->get_record('local_questionbank', array('id'=>$data->id));
            $thiscontext = context_system::instance();
            $edittab = 'categories';
            if ($thiscontext){
                    $contexts = new question_edit_contexts($thiscontext);
                    $contexts->require_one_edit_tab_cap($edittab);
            } else {
                    $contexts = null;
            }
            $defaultcategory = question_make_default_categories($contexts->all());
            // $question_category = $DB->get_record_sql("SELECT * FROM {question_categories} where name ='top' and parent= 0 AND contextid=1");
            $question_category_parent = $DB->get_field_sql("SELECT id FROM {question_categories} where name ='top' and parent= 0 AND contextid=". $systemcontext->id);
            $question_category = $DB->get_record_sql("SELECT * FROM {question_categories} where name ='Workshop Categories' AND idnumber='workshop_categories' and parent= $question_category_parent AND contextid=$systemcontext->id");
            $thispageurl = new moodle_url('/local/questionbank/index.php');
            $qcobject = new question_category_object($thiscontext->id, $thispageurl,
            $contexts->having_one_edit_tab_cap('categories'), $param->edit,
                                        $question_category->id, $param->delete, $contexts->having_cap('moodle/question:add'));
            if ($insert->id && $question_category) {//new category
                $newparent = $question_category->id.','.$thiscontext->id;
                $categoryid=$qcobject->add_category($newparent, $data->workshopname,
                                       $question_category->info, $systemcontext->id, $question_category->infoformat,  $data->workshopname);
            } 
            $insert->id =  $question->id;
            if($insert->id && $categoryid){
                    $cat = new stdClass();
                     $cat->id = $question->id;
                     $cat->qcategoryid = $categoryid;
                     $DB->update_record('local_questionbank',$cat);
            }
        }
       return $insert->id;
    }
     public function update_questionbank($data) {
        global $DB,$CFG,$COURSE,$USER;
        $systemcontext = context_system::instance();
        require_once($CFG->dirroot."/question/editlib.php");
        require_once($CFG->dirroot."/question/category_class.php");
        $question = new stdClass();

        $questionbankdata = $DB->get_record('local_questionbank', array('id'=>$data->id)); 
        if($data->form_status==0){
            $question->id= $data->id;
            $question->generatecode = $data->generatecode;
            $question->workshopname = $data->workshopname;
           if(is_siteadmin()){
                $workshopadmin = implode(',',array_filter($data->workshopadmin));
                $question->workshopadmin =  $workshopadmin;// $data->workshopadmin;
            }else{
                 $question->workshopadmin = $USER->id;
            }
            //$question->workshopadmin =  $workshopadmin;// $data->workshopadmin;
            $question->noofquestions =  $data->noofquestions;
           // $question->workshopstarttime =  $data->startdate;
            $question->workshopdate =  $data->startdate;   
            $time = ($data->starttime['hours'] * 3600) + ($data->starttime['minutes'] * 60);
           // $starttime = ceil($data->startdate + $time );
            $question->workshopstarttime =  $time;
            $question->duration =  $data->duration;
            //$question->workshopendtime =  $data->enddate;
            $question->halladdress =  $data->halladdress;
            $question->course= $questionbankdata->course;
            $question->competency= $questionbankdata->competency;
            $question->status =  $data->status;
            $question->usermodified =  $USER->id;
            $question->timemodified =  time();
           // exit("-----".$data->startdate."-------a".$questionbankdata->workshopdate."a--------------".$starttime."------".$questionbankdata->workshopstarttime); 

            if($data->startdate != $questionbankdata->workshopdate || $starttime != $questionbankdata->workshopstarttime)
            {
                // notification Questionbank  onchange
            $sql="SELECT u.* FROM {user} u
            JOIN {local_qb_experts} le ON le.expertid = u.id
             WHERE le.questionbankid = $data->id AND u.confirmed = 1 AND u.suspended = 0 AND u.deleted = 0 AND u.id > 2";
             $touser = $DB->get_records_sql($sql);
            $thispageurl = new moodle_url('/local/questionbank/questionbank_workshop.php?id='.$data->id);
             if(!$touser)
                {
                $touser=null;
                }
            $row1=[];
            $row1['RelatedModuleName']=$data->workshopname;
            $row1['ProgramLink']=$thispageurl;
            $myobject=(new \local_questionbank\notification);
            $myobject->questionbank_notification('questionbank_reschedule',$touser, $USER,$row1,$waitinglistid=0);
            }
            $update->id = $DB->update_record('local_questionbank',$question);
            
        }else{
            $data->competencies = $data->competencylevel;
           
            $existingcourses= explode(',',$questionbankdata->course);
            $existingcategories= explode(',',$questionbankdata->qcategoryid);
            $selectedcourses = $data->courses;
            $question->id= $data->id;
            $question->course= implode(',',array_filter($data->courses));
            $question->competency= implode(',',$data->competencies);
            $update->id = $DB->update_record('local_questionbank',$question);
            //****Hall Reservation *******//
            if($data->entitycode) {
                $draftrecord = $DB->get_record('reservations_draft', ['entitycode' => $data->entitycode, 'type' => 'questionbank']);
                if($draftrecord) {
                    $existingrecord = $DB->get_record('hall_reservations', ['typeid' => $data->id, 'type' => 'questionbank']);
                    $reservationdata = new stdClass();
                    if($existingrecord) {
                      $reservationdata->id = $existingrecord->id;
                    }
                    $reservationdata->typeid = $data->id;
                    $reservationdata->hallid = $draftrecord->hallid;
                    $reservationdata->seats = $draftrecord->seats;
                    $reservationdata->examdate = $draftrecord->date;
                    $reservationdata->slotstart = $draftrecord->slotstart;
                    $reservationdata->slotend = $draftrecord->slotend;
                    $reservationdata->userid = $draftrecord->userid;
                    $reservationdata->type = 'questionbank';
                    $reservationdata->status = 1;
                    if($existingrecord) {
                       $DB->update_record('hall_reservations', $reservationdata);
                    } else {

                        $DB->insert_record('hall_reservations', $reservationdata);
                    }
                    $sessionkey = $DB->delete_records('reservations_draft', ['entitycode' => $data->entitycode, 'type' => 'questionbank']);
                }
            }
            $thiscontext = context_system::instance();
            $edittab = 'categories';
            if ($thiscontext){
                    $contexts = new question_edit_contexts($thiscontext);
                    $contexts->require_one_edit_tab_cap($edittab);
            } else {
                    $contexts = null;
            }
            $defaultcategory = question_make_default_categories($contexts->all());
            if($questionbankdata->qcategoryid){
                $question_category = $DB->get_record_sql("SELECT * FROM {question_categories} where id=".$questionbankdata->qcategoryid);
                // $question_parentcategory = $DB->get_record_sql("SELECT * FROM {question_categories} where name ='top' and parent= 0 AND contextid=1");
                $question_category_parent = $DB->get_field_sql("SELECT id FROM {question_categories} where name ='top' and parent= 0 AND contextid=$systemcontext->id");
                $question_parentcategory = $DB->get_record_sql("SELECT * FROM {question_categories} where name ='Workshop Categories' AND idnumber='workshop_categories' and parent= $question_category_parent AND contextid=$systemcontext->id");
                $thispageurl = new moodle_url('/local/questionbank/questionbank_workshop.php?id='.$data->id);
                $qcobject = new question_category_object($thiscontext->id, $thispageurl,
                $contexts->having_one_edit_tab_cap('categories'), $param->edit,
                                            $question_category->id, $param->delete, $contexts->having_cap('moodle/question:add'));
                if ($question_category) {//new category
                    $newparent = $question_parentcategory->id.','.$thiscontext->id;
                    $qcobject->update_category($question_category->id, $newparent,
                    $questionbankdata->workshopname, $question_category->info, $question_category->infoformat,  $questionbankdata->workshopname);
                } 
            }else{
                $question_category_parent = $DB->get_field_sql("SELECT id FROM {question_categories} where name ='top' and parent= 0 AND contextid=$systemcontext->id");
                $question_category = $DB->get_record_sql("SELECT * FROM {question_categories} where name ='Workshop Categories' AND idnumber='workshop_categories' and parent= $question_category_parent AND contextid=$systemcontext->id");
                $thispageurl = new moodle_url('/local/questionbank/index.php');
                $qcobject = new question_category_object($thiscontext->id, $thispageurl,
                $contexts->having_one_edit_tab_cap('categories'), $param->edit,
                                            $question_category->id, $param->delete, $contexts->having_cap('moodle/question:add'));
                if ($questionbankdata->id && $question_category) {//new category
                    $newparent = $question_category->id.','.$thiscontext->id;
                    $categoryid=$qcobject->add_category($newparent, $questionbankdata->workshopname,
                                           $question_category->info, $systemcontext->id, $question_category->infoformat,  $questionbankdata->workshopname);
                } 
            if($questionbankdata->id && $categoryid){
                    $cat = new stdClass();
                     $cat->id = $questionbankdata->id;
                     $cat->qcategoryid = $categoryid;
                     $DB->update_record('local_questionbank',$cat);
            }
            }

        }
         // Trigger update questionbank.
         $eventparams = array('context' => context_system::instance(),'objectid'=>$update->id);
         $event = local_questionbank\event\update_questionbank::create($eventparams);
         $event->trigger();
         // Notification-----------------------
         $start_date=date('d.m.Y', $data->startdate);
         //-----------------------
         $row=[];
         $row['WorkshopName']=$data->workshopname;
         $row['QuestionBankName']=$data->workshopname;
         $row['WorkshopDate']=$start_date;
         $row['WorkshopTime']=date("H:i:s",$time);
         $myobject=(new \local_questionbank\notification);
         $myobject->questionbank_notification('questionbank_workshop_updated',$touser=null, $USER,$row,$waitinglistid=0);
          // notification Questionbank  onchange
          $sql="SELECT u.* FROM {user} u
          JOIN {local_qb_experts} le ON le.expertid = u.id
          WHERE le.questionbankid = $data->id AND u.confirmed = 1 AND u.suspended = 0 AND u.deleted = 0 AND u.id > 2";
        $touser = $DB->get_records_sql($sql);
        $thispageurl = new moodle_url('/local/questionbank/questionbank_workshop.php?id='.$data->id);
        if(!$touser)
        {
            $touser=null;
        }
         $row1=[];
         $row1['RelatedModuleName']=$data->workshopname;
         $row1['RelatedModulesLink']=$thispageurl;
        $myobject=(new \local_questionbank\notification);
        $myobject->questionbank_notification('questionbank_onchange',$touser, $USER,$row1,$waitinglistid=0);
        /*--------------------------------------------------------------------*/
        return $data->id;
    }
    function get_listof_questionbank($stable, $filterdata) {
        global $CFG,$DB,$OUTPUT,$USER,$PAGE;
        $systemcontext = context_system::instance();
         $actions = false;
        if(is_siteadmin() || has_capability('local/questionbank:assignreviewer',$systemcontext)){
            $actions = true;
        }
        if(!is_siteadmin() &&  has_capability('local/questionbank:assignreviewer',$systemcontext)){
           $wheresql = " AND (FIND_IN_SET($USER->id, qb.workshopadmin) OR qb.usermodified = $USER->id)"; 
        }elseif(!is_siteadmin() && !(has_capability('local/questionbank:assignreviewer',$systemcontext))){
           $sql = "JOIN {local_qb_experts} as ex ON ex.questionbankid = qb.id AND ex.expertid=".$USER->id."";
           $wheresql = " AND status=1";
           // if(empty($filterdata->status)) {
           //      $wheresql .= "  AND ( (qb.workshopdate+qb.workshopstarttime)<=UNIX_TIMESTAMP()) AND (UNIX_TIMESTAMP()<=(qb.workshopdate+qb.workshopstarttime+duration))";
           // }

        }
        $wcompleted = false;
        if($filterdata->status){
            $wheresql .= " AND movedtoprod = 1";
            

           // $actions = false;
        }else{
             $wheresql .= " AND (movedtoprod != 1 OR movedtoprod IS NULL)";
        }
        // else{
        //     $wheresql .= " AND date(FROM_UNIXTIME(qb.workshopdate)) >= CURDATE()";
        // }
      // print_r($filterdata);
      // echo $wheresql ;
      // exit;
       // if(!is_siteadmin()){
       // //    $sql = "JOIN {local_qb_experts} as ex ON ex.questionbankid = qb.id AND ex.expertid=".$USER->id."";
       //   if ($stable->class == 'listofexams') {
       //          $formsql .=" AND status = 1 AND date(FROM_UNIXTIME(enddate)) > CURDATE() ";
       //      } elseif ($stable->class == 'reviewexams') {
       //          $formsql .=" AND status = 0 ";
       //      } elseif ($stable->class == 'completedexams') {
       //       $formsql .=" AND status = 1 AND date(FROM_UNIXTIME(enddate)) <= CURDATE() ";
       //   }
       // }
        $selectsql= "SELECT qb.id, qb.workshopname, qb.* FROM {local_questionbank} AS qb   $sql WHERE 1=1  $wheresql";
     // echo  $selectsql;
        $countsql="SELECT count(qb.id) FROM {local_questionbank} AS qb  $sql WHERE 1=1  $wheresql";



     if(isset($filterdata->search_query) && trim($filterdata->search_query) != ''){
             $formsql .= " AND qb.workshopname LIKE :search";
             $searchparams = array('search' => '%'.trim($filterdata->search_query).'%');
         }else{
             $searchparams = array();
         }
         $params = array_merge($searchparams);
         $totalquestionbanks= $DB->count_records_sql($countsql.$formsql, $params);
         $formsql .=" ORDER BY qb.id DESC";
         $questionbanks= $DB->get_records_sql($selectsql.$formsql, $params, $stable->start,$stable->length);
           $questionbanklist = array();
         $count = 0;
        

         
          foreach ($questionbanks as $questionbank ) {
            $questioncount = false;
            $questionbanklist[$count]["id"] = $questionbank->id;
         //   $questionbanklist[$count]["topicid"] = [35,36];
            $questionbanklist[$count]["workshopname"] = $questionbank->workshopname;
            $questionbanklist[$count]["noofquestions"] = $questionbank->noofquestions;
            $questionbanklist[$count]["generatecode"] = $questionbank->generatecode;
            $created= strtotime();
            $workshopdate= new stdClass();
            if($questionbank->movedtoprod == 1){
                $tocategory =   $questionbank->tocategoryid;
                $questionbanklist[$count]["tocategory"] = $tocategory.','.$systemcontext->id;
                $wcompleted = true;
            }else{
                $wcompleted = false;
            }
            $questionbanklist[$count]["workshopdatetime"] = $questionbank->workshopdate;
            $workshopdate->date = userdate($questionbank->workshopdate, get_string('strftimedatemonthabbr', 'langconfig'));
            $questionbanklist[$count]["workshopdate"] = $workshopdate->date;
            $createdtime= strtotime($questionbank->workshopstarttime);
    
            // $workshopdate->time=date("H:i A", $questionbank->workshopstarttime);
            // $dur_min = $questionbank->workshopstarttime/60;
            // if($dur_min){
            //    $hours = floor($dur_min / 60);
            //    $minutes = ($dur_min % 60);
               // $hours.":".$minutes ;
            // }
            //$workshopdate->time = gmdate("H:i A",$questionbank->workshopstarttime);
            $questionbanklist [$count]["workshopstarttime"] =gmdate("H:i ",$questionbank->workshopstarttime);
            $hallinfo= $DB->get_record_sql("SELECT name,city FROM {hall} WHERE id =".$questionbank->halladdress);
            $questionbanklist [$count]["halladdress"] =$hallinfo->name;
            //$questionbanklist [$count]["location"] ='Riyadh';

            $maplocation = $DB->get_field('hall', 'maplocation', ['id' => $questionbank->halladdress]);
            if (filter_var($maplocation, FILTER_VALIDATE_URL)) {
                $questionbanklist [$count]["locationstatus"] = true;
            } else {
                $questionbanklist [$count]["locationstatus"] = false;
            }

            $questionbanklist [$count]["location"] = $maplocation;
            $questionbanklist [$count]["workshopenddate"] = userdate($questionbank->workshopendtime, get_string('strftimedatemonthabbr', 'langconfig'));
            $questionbanklist [$count]["qcategory"]= $questionbank->qcategoryid.','.$systemcontext->id;
            $questionbanklist [$count]["qcategoryid"]= $questionbank->qcategoryid;
            //$username=get_complete_user_data('id',$questionbank->workshopadmin);
            $currentlang= current_language();
           $fullname = (new \local_trainingprogram\local\trainingprogram)->user_fullname_case();
           $qbadmin = $DB->get_fieldset_sql("SELECT $fullname FROM  {user} as u LEFT JOIN {local_users} AS lc ON lc.userid = u.id WHERE u.id IN ($questionbank->workshopadmin)");
            $qbadmin  = implode(',', $qbadmin);
            $questionbanklist [$count]['coursescount'] = !empty($questionbank->course)?true:false;
            $questionbanklist [$count]["workshopadmin"] =  $qbadmin  ;
            $questionbanklist [$count]["questionbank_workshop_url"] =$CFG->wwwroot.'/local/questionbank/questionbank_workshop.php?id='.$questionbank->id;
            $questionbanklist [$count]["questionbank_edit_url"] =$CFG->wwwroot;
            $availableseats = $DB->get_field_sql("SELECT SUM(seats) FROM {hall_reservations} WHERE typeid=$questionbank->id AND type ='questionbank' AND hallid=$questionbank->halladdress");
            if(empty($availableseats)){
                $availableseats = 0;
            }
            $questionbanklist [$count]["availableseats"] =  $availableseats;
            if($questionbank->qcategoryid){
                $qsql = "SELECT q.id 
                                             FROM {question} q  
                                             JOIN {question_versions} qv ON qv.questionid = q.id    
                                             JOIN {question_bank_entries} qbe ON qbe.id = qv.questionbankentryid    
                                             JOIN {question_categories} qc ON qc.id = qbe.questioncategoryid    
                                            WHERE qc.id = $questionbank->qcategoryid ";
                $qstatussql = "  AND qv.status='ready'";
                $publishedquestions= $DB->get_fieldset_sql($qsql.$qstatussql);
                $publishedquestions= count( $publishedquestions);
                $questionslist= $DB->get_fieldset_sql($qsql);
                $totalquestions = count( $questionslist);
            }else{
               $publishedquestions=  0; 
               $totalquestions=  0; 
            }
            $questionbanklist [$count]["display_questions"] = true;
           
            if(!is_siteadmin() && !has_capability('local/questionbank:assignreviewer',$systemcontext)){
                
               $wheresql1 = "  AND ( (q.workshopdate+q.workshopstarttime)<=UNIX_TIMESTAMP()) AND (UNIX_TIMESTAMP()<=(q.workshopdate+q.workshopstarttime+q.duration))";
               $workshop = $DB->get_record_sql("SELECT q.*  FROM {local_questionbank} q 
                                     WHERE q.id = $questionbank->id $wheresql1");
               if(empty($workshop)) {

                $questionbanklist [$count]["display_questions"] = false;
                if(($questionbank->workshopdate+$questionbank->workshopstarttime) >=time()){
                $ontime =  userdate($questionbank->workshopdate, get_string('strftimedatemonthabbr', 'langconfig'))."@" .gmdate("H:i ",$questionbank->workshopstarttime);
                $questionbanklist [$count]["ontime"] = $OUTPUT->box(get_string('questionsdisabled','local_questionbank',$ontime), 'generalbox alert alert-generalbox');
                }
               }
            }
            // $publishedquestions= $DB->get_fieldset_sql($qsql.$qstatussql);
            // $publishedquestions= count( $publishedquestions);
            $questionbanklist [$count]["qbstatus"] =  true  ;
           if($filterdata->status){
              $questionbanklist [$count]["qcategoryid"]= false;
              $questionbanklist [$count]["display_questions"] = false;
              $questionbanklist [$count]['workshopstatus'] =get_string('completed','local_questionbank');
              $questionbanklist [$count]["qbstatus"] =  ''  ;

           }elseif($totalquestions > 0 &&  ($totalquestions ==  $publishedquestions) && ($totalquestions ==  $questionbank->noofquestions)){
                $questioncount = true;
                $questionbanklist [$count]['workshopstatus'] = get_string('approved','local_questionbank');
            }else{
                $questionbanklist [$count]['workshopstatus'] = get_string('draft','local_questionbank');
            }
            $questionbanklist [$count]["questionscount"]  = $questioncount;
            $questionbanklist [$count]['actions'] = $actions;
            $questionbanklist [$count]['wcompleted'] = $wcompleted;
           
             $count++;
             }
     $coursesContext = array(
             "hascourses" => $questionbanklist,
             "nocourses" => $nocourse,     
             "totalquestionbank" => $totalquestionbanks,
             "length" => count($questionbanklist),
             );
         return $coursesContext;
     
    }
    public  function questionbank_workshop_view($questionbankid) { 
        global $DB, $PAGE, $OUTPUT;
        $systemcontext = context_system::instance();
        $PAGE->set_context($systemcontext);
        $actions = false;
        if(is_siteadmin() || has_capability('local/questionbank:assignreviewer',$systemcontext)){
            $actions = true;
        }
        $questionbankrecord=$DB->get_record('local_questionbank',array('id' => $questionbankid));
        $workshopdate = date("Y-m-d",$questionbankrecord->workshopdate);
        $currentdate = date("Y-m-d");
        if($questionbankrecord->movedtoprod == 1){
            $actions = false;
        }
        //exit;
        //if(date($questionbankrecord->workshopdate))
        $currentlang= current_language();
        $fullname = (new \local_trainingprogram\local\trainingprogram)->user_fullname_case();
        $experts_info=$DB->get_fieldset_sql("SELECT $fullname FROM {local_qb_experts} as qe 
            JOIN {user} as u ON u.id = qe.expertid 
            JOIN {local_users} AS lc ON lc.userid = u.id where questionbankid =".$questionbankid);
        if(!empty($experts_info)){
          $expertslist = implode(',',$experts_info);
        }else{
           $expertslist = get_string('noexperts','local_questionbank'); 
        }
        
        $username=get_complete_user_data('id',$questionbankrecord->workshopadmin);
        $hall=$DB->get_record('hall',array('id'=>$questionbankrecord->halladdress));
        //$workshopslot=date("h:i A", $questionbankrecord->workshopstarttime).' to '.date("h:i A", $questionbankrecord->workshopendtime);
        $workshopslot=gmdate("H:i ",$questionbankrecord->workshopstarttime);
        $competancy=$questionbankrecord->competency;
        list($competencesql,$competenceparams) = $DB->get_in_or_equal(explode(',',$competancy));
        $currentlang= current_language();
        if( $currentlang == 'ar'){
             $sql = "SELECT id,(CASE WHEN arabicname IS NULL THEN name ELSE arabicname END) as name,code FROM {local_competencies} WHERE id $competencesql";
        }else{
            $sql = "SELECT id,name,code FROM {local_competencies} WHERE id $competencesql";
        }
        $competencies= $DB->get_records_sql($sql,$competenceparams);
        $course=$questionbankrecord->course;
        list($coursesql,$courseparams) = $DB->get_in_or_equal(explode(',',$course));
        $querysql = "SELECT id,fullname as coursename FROM {course} WHERE id $coursesql";
        $courses= $DB->get_records_sql($querysql,$courseparams);
        foreach ($courses AS $course) {
            $course->fullname = format_string($course->coursename);
            $topics = $DB->get_fieldset_sql("SELECT topic FROM {local_qb_coursetopics} WHERE courseid = $course->id AND questionbankid =$questionbankid");
            if(count($topics) > 0){
                  $course->topicscount = count($topics);
            }else{
                  $course->topicscount =0;
            }
            $topics = implode(',', $topics);

            if($topics){
               $topicssql = "SELECT id,(CASE WHEN name IS NULL THEN CONCAT('Topic',section) ELSE name END) as name FROM {course_sections} WHERE course = $course->id AND section <> 0 AND id IN ($topics)";
                $course->topics=array_values($DB->get_records_sql($topicssql));
               
            }
           
        }

        $maplocation = $DB->get_field('hall', 'maplocation', ['id' => $questionbankrecord->halladdress]);
        if (filter_var($maplocation, FILTER_VALIDATE_URL)) {
            $locationstatus = true;
        } else {
            $locationstatus = false;
        }
        
        $courseactions = count($courses) > 1 ? true : false;
        $competenciesactions = count($competencies) > 1 ? true : false;
        $totalcourses = count($courses) > 0 ?true : false;
        $totalcompetencies= count($competencies) > 0 ?true : false;
        // print_r($courses);
        // exit;
        $data=[
        'workshopname'=>ucfirst($questionbankrecord->workshopname),
        'workshopid'=>ucfirst($questionbankrecord->id),
        'noofquestions'=>$questionbankrecord->noofquestions,
        'generatecode'=>$questionbankrecord->generatecode,
        'workshopdate'=>userdate($questionbankrecord->workshopdate, get_string('strftimedatemonthabbr', 'langconfig')),
        'workshopslot'=>$workshopslot,
        'workshopadmin'=>$username->firstname,
        'hall'=>$hall->name,
        'location'=> !empty($questionbankrecord->halladdress) ? $maplocation : '--',
        'locationstatus' => $locationstatus,
        'competencylist'=>array_values($competencies),
        'courses'=>array_values($courses),
        'experts'=>$expertslist,
        'courseactions' => $courseactions,  
        'competenciesactions' => $competenciesactions,
        'actions' => $actions,
        'tcourses' => $totalcourses,
        'tcompetencies' => $totalcompetencies,

        ];
        echo $OUTPUT->render_from_template('local_questionbank/create_question_bank_workshop', $data);
    }

    public  function viewcoursetopics($workshopid,$workshopname) { 
        global $DB, $PAGE, $OUTPUT;
        $systemcontext = context_system::instance();
        $PAGE->set_context($systemcontext);
        $data = $DB->get_record('local_questionbank', ['id' => $workshopid], '*', MUST_EXIST);
        $renderer = $PAGE->get_renderer('local_questionbank');
        $coursedata  = $renderer->coursetopic_view($data);
        return $coursedata;
    }
    public  function viewquestiontopics($questiobankid,$questionname,$questionid) { 
        global $DB, $PAGE, $OUTPUT;
        $systemcontext = context_system::instance();
      
        $PAGE->set_context($systemcontext);
        $data = $DB->get_records('local_qb_questioncourses', ['questionid' => $questionid]);
        $renderer = $PAGE->get_renderer('local_questionbank');
        $coursedata  = $renderer->questiontopics_view($data);
        return $coursedata;
    }
    public  function viewcompetencies($workshopid,$workshopname) { 
        global $DB, $PAGE, $OUTPUT;
        $systemcontext = context_system::instance();
        $PAGE->set_context($systemcontext);
        $data = $DB->get_record('local_questionbank', ['id' => $workshopid], '*', MUST_EXIST);
        $renderer = $PAGE->get_renderer('local_questionbank');
        $coursedata  = $renderer->competencies_view($data);
        return $coursedata;
    }
    public  function viewexperts($stable, $filterdata) {
        global $DB, $PAGE, $OUTPUT;
        $systemcontext = context_system::instance();
        $currentlang= current_language();
        $fullname = (new \local_trainingprogram\local\trainingprogram)->user_fullname_case();
        //$questionbankinfo = $DB->get_record('local_questionbank',array('id'=>$filterdata->workshopid));
        $selectsql = "SELECT u.id as userid, $fullname,lc.firstname,lc.lastname,lc.firstnamearabic,lc.lastnamearabic,lc.middlenamearabic,lc.thirdnamearabic,lc.middlenameen,lc.thirdnameen,qb.* ";
        $countsql  = "SELECT COUNT(u.id)";
        $formsql =  " FROM {local_qb_experts} qb";
        $formsql .=  " JOIN {user} u ON u.id = qb.expertid";
        $formsql .=  " LEFT JOIN {local_users} AS lc ON lc.userid = qb.expertid"; 
        $formsql .=  "  WHERE qb.questionbankid = $filterdata->workshopid"; 
        if(isset($filterdata->search_query) && trim($filterdata->search_query) != ''){
             $formsql .= " AND (lc.firstname LIKE :firstnamesearch OR lc.lastname LIKE :lastnamesearch OR lc.firstnamearabic LIKE :firstnamearabicsearch OR lc.lastnamearabic LIKE :llastnamearabicsearch OR lc.middlenameen LIKE :middlenameensearch OR lc.middlenamearabic LIKE :middlenamearabicsearch OR lc.thirdnameen LIKE :thirdnameensearch OR lc.thirdnamearabic LIKE :thirdnamearabicsearch ) ";
            $searchparams = array(
                                  'firstnamesearch' => '%'.trim($filterdata->search_query).'%',
                                  'lastnamesearch' => '%'.trim($filterdata->search_query).'%', 
                                  'firstnamearabicsearch' => '%'.trim($filterdata->search_query).'%', 
                                  'llastnamearabicsearch' => '%'.trim($filterdata->search_query).'%', 
                                  'middlenameensearch' => '%'.trim($filterdata->search_query).'%', 
                                  'middlenamearabicsearch' => '%'.trim($filterdata->search_query).'%', 
                                  'thirdnameensearch' => '%'.trim($filterdata->search_query).'%', 
                                  'thirdnamearabicsearch' => '%'.trim($filterdata->search_query).'%');
                                  
        }else{
            $searchparams = array();
        }     
        $params = array_merge($searchparams);
        $totalexperts = $DB->count_records_sql($countsql.$formsql, $params);
        $formsql .=" ORDER BY qb.id DESC";
        $experts = $DB->get_records_sql($selectsql.$formsql, $params, $stable->start,$stable->length);
        $expertslist = array();
        // $qcount = $DB->get_field_sql("SELECT sum(noofquestions) FROM {local_qb_experts} WHERE questionbankid = $filterdata->workshopid");
        // $availablequestions = $questionbankinfo->noofquestions - $qcount;
        $count = 0;
        if ($experts) {
            foreach($experts as $list) {
                $expertslist[ $count]['id'] = $list->id;
                $expertslist[ $count]['userid'] = $list->userid;
                $expertslist[ $count]['username'] = $list->fullname;
                $qbexpertinfo = $DB->get_field_sql("SELECT id FROM  {local_qb_questionreview} as qr  WHERE qr.assignedreviewer =  $list->userid AND qr.questionbankid =   $filterdata->workshopid");
                // $expertslist[ $count]['totalquestions'] = $questionbankinfo->noofquestions;
                // $expertslist[ $count]['assignedqcount'] = $list->noofquestions > 0 ? $list->noofquestions : '' ;
                // $expertslist[ $count]['wid'] = $filterdata->workshopid;
                // $expertslist[ $count]['availablequestions'] = $availablequestions;
                $expertslist[ $count]['dateadded'] = userdate($list->timecreated, get_string('strftimedatemonthabbr', 'langconfig')); 
                $expertslist[ $count]['expertinfo'] = !empty($qbexpertinfo) ? true : false;
                $count++;
            }
            $nodata = true;
        } else {
            $nodata = false;
        }
        // print_r( $expertslist);
        $expertContext = array(
            "hasrecords" => $expertslist,
            "nodata" => $nodata,
            "totalcount" => $totalexperts,
            "length" => $totalexperts
        );        
        return $expertContext;
    }
    
     public function competency_data($stable, $filterdata) {
        global $DB, $PAGE, $OUTPUT;
        $systemcontext = context_system::instance();
        // exit;
         $addedcompetencies =  array();
        $competencies =  $DB->get_field('local_questionbank', 'competency', ['id' => $filterdata->workshopid]);
        if($competencies){
            $currentlang= current_language();
            if( $currentlang == 'ar'){
                $selectsql = "SELECT id,(CASE WHEN arabicname IS NULL THEN name ELSE arabicname END) as name,code,type,level FROM {local_competencies} le WHERE 1=1 AND id IN ($competencies) "; 
            }else{
                $selectsql = "SELECT id,name,code,type,level FROM {local_competencies} le WHERE 1=1 AND id IN ($competencies) "; 
            }
            $countsql  = "SELECT COUNT(le.id) FROM {local_competencies} le WHERE 1=1 AND id IN ($competencies) ";
            if(isset($filterdata->search_query) && trim($filterdata->search_query) != ''){
                $formsql .= " AND (le.name LIKE '%".trim($filterdata->search_query)."%' OR le.code LIKE '%".trim($filterdata->search_query)."%') ";
                $searchparams = array('search' => '%'.trim($filterdata->search_query).'%');
            }else{
                $searchparams = array();
            }     
            //$params = array_merge($searchparams);
            $totalcompetency = $DB->count_records_sql($countsql.$formsql, $params);
            $formsql .=" ORDER BY le.id DESC";
            $added_competencies = $DB->get_records_sql($selectsql.$formsql, $params, $stable->start,$stable->length);
            //  $competencieslist = array();
            //  $count =0;
             foreach($added_competencies as $addedcompetencies) {
               $addedcompetencies->type = get_string($addedcompetencies->type,'local_competency');
            }
            // foreach($added_competencies as $addedcompetencies) {
            //     $competencieslist[ $count]['name'] = $addedcompetencies->name;
            //     $competencieslist[ $count]['code'] = $addedcompetencies->code;
            //     $competencieslist[ $count]['type'] = get_string($addedcompetencies->type,'local_trainingprogram');
            //     $count++;
            // }
          
        }else{
            $added_competencies = array();
        }   
         $coursesContext = array(
                "acompetencies" => array_values($added_competencies),
                "nocourses" => $nocourse,
                "totalcount" => $totalcompetency,
                "length" => $totalcompetency
        );    
        return $coursesContext;
    }
   
    
    public function expertlist() {
        global $DB;
        $currentlang= current_language();
        if($currentlang == 'ar') {
              $displaying_name = "concat((CASE WHEN lu.firstnamearabic IS NULL THEN u.firstname ELSE lu.firstnamearabic END),' ',(CASE WHEN lu.lastnamearabic IS NULL THEN u.lastname ELSE lu.lastnamearabic END))";
        } else {
            $displaying_name = "concat(u.firstname,' ',u.lastname)";
        }
        $data = $DB->get_records_sql("SELECT ra.userid as id, $displaying_name as fullname
        FROM {role_assignments} as  ra 
                JOIN {role} as r ON ra.roleid = r.id  AND r.shortname = 'expert'
                JOIN {user} as u ON u.id= ra.userid
                JOIN {local_users} AS lu ON lu.userid = u.id");
        return $data;
    }


    public function create_qb_experts($data) {
        global $DB, $USER;
        $data = (array) $data;
        $qb_id = $data['questionbankid'];
        $systemcontext = context_system::instance();
        if (is_array($data['expertid'])) {
            $expertids = $data['expertid'];
        }else{
            $expertids[] = $data['expertid'];
        }
        foreach($expertids as $ids) {
            $expertinfo = $DB->get_record('local_qb_experts', array('questionbankid' =>  $qb_id,'expertid'=> $ids));
            if(empty($expertinfo)){
                $addexperts->questionbankid = $qb_id;
                $addexperts->expertid = $ids;
                $addexperts->noofquestions = !empty($data['noofquestionsfor_'.$ids]) ? $data['noofquestionsfor_'.$ids] : NULL;
                $addexperts->timecreated = time();
                $addexperts->usercreated = $USER->id;
                try {
                    
                    $DB->insert_record('local_qb_experts',$addexperts);
                } catch (Exception $e) {
                    throw new Exception($e->getMessage(), 1);
                    
                }
            }
            else{
                $update_experts->id = $expertinfo->id;
                $update_experts->expertid = $ids;
                $update_experts->noofquestions = !empty($data['noofquestionsfor_'.$ids]) ? $data['noofquestionsfor_'.$ids] : NULL;
                $update_experts->timemodified = time();
                $update_experts->userupdated = $USER->id;
                try {
                    
                    $DB->update_record('local_qb_experts',$update_experts);
                } catch (Exception $e) {
                    throw new Exception($e->getMessage(), 1);
                    
                }
            }
            //-----------------------
            $questionbank = $DB->get_record('local_questionbank', array('id'=>$data->questionbankid));
            $currentlang= current_language();
            if($currentlang == 'ar') {
                $displaying_name = "concat((CASE WHEN lu.firstnamearabic IS NULL THEN u.firstname ELSE lu.firstnamearabic END),' ',(CASE WHEN lu.lastnamearabic IS NULL THEN u.lastname ELSE lu.lastnamearabic END))";
            } else {
                $displaying_name = "concat(u.firstname,' ',u.lastname)";
            }
            $experts_info=$DB->get_fieldset_sql("SELECT $displaying_name as fullname FROM {local_qb_experts} as qe JOIN {user} as u ON u.id = qe.expertid JOIN {local_users} AS lu ON lu.userid = qe.expertid where questionbankid =".$data['questionbankid']);
            if(!empty($experts_info)){
               $expertslist = implode(',',$experts_info);
            }else{
                $expertslist = 'No Expert'; 
            }
            $row=[];
            $row['QuestionBankName']=$questionbank->workshopname;
            $row['ExpertName']=$expertslist;
            $emailtype='questionbank_assign_expert';
            $myobject=(new \local_questionbank\notification);
            $myobject->questionbank_notification($emailtype,$touser=null, $USER,$row,$waitinglistid=0);
        }
        return true;  
    }

    public function create_qb_topics($data) {
        global $DB, $USER,$CFG;
        $systemcontext = context_system::instance();
        $topicsid = $data->topicsid;
        foreach($topicsid as $ids) {
            $data->questionbankid = $data->questionbankid;
            $data->topic = $ids;
            $data->courseid = $data->coursetopic;
            $data->timecreated = time();
            $data->usercreated = $USER->id;
            $DB->insert_record('local_qb_coursetopics',$data);
             // notification Questionbank  onchange
             $sql="SELECT u.* FROM {user} u
             JOIN {local_qb_experts} le ON le.expertid = u.id
             WHERE le.questionbankid = $data->questionbankid AND u.confirmed = 1 AND u.suspended = 0 AND u.deleted = 0 AND u.id > 2";
           $touser = $DB->get_records_sql($sql);
           $thispageurl = new moodle_url('/local/questionbank/questionbank_workshop.php?id='.$data->questionbankid);
           if(!$touser)
           {
               $touser=null;
           }
            $row1=[];
            $row1['RelatedModuleName']=$data->workshopname;
            $row1['RelatedModulesLink']=$thispageurl;
           $myobject=(new \local_questionbank\notification);
           $myobject->questionbank_notification('questionbank_onchange',$touser, $USER,$row1,$waitinglistid=0);
        }  
    }
    public function questions_review($questionid,$qcategory,$reviewerid) {
        global $DB, $USER;
        $systemcontext = context_system::instance();
        $workshopid =  $DB->get_field('local_questionbank','id',array('qcategoryid'=>$qcategory));
        if($workshopid){
            //$res = $DB->update_record('question_versions',array('id'=> $qversion,'questionid'=>$id ,'status'=>$cstaus));
            $data->questionbankid = $workshopid;
            $data->questionid =$questionid;
            $data->categoryid =$qcategory;
            $data->reviewdon = time();
            $data->assignedreviewer  = $reviewerid;
           // $data->qversion  = 1;
            $questioninfo =  $DB->get_field('local_qb_questionreview','id',array('questionid'=>$questionid,'questionbankid'=>$workshopid));
            if(empty( $questioninfo)){
                $DB->insert_record('local_qb_questionreview',$data);
            }else{
                $data->id  =  $questioninfo;
                $DB->update_record('local_qb_questionreview',$data);
            }

        }
        //purge_other_caches();
        return true;
            
        
    }
    public function questions_reviewstatus($questionid,$qcategory,$status) {
        global $DB, $USER;
        $systemcontext = context_system::instance();
        $workshopid =  $DB->get_field('local_questionbank','id',array('qcategoryid'=>$qcategory));
        if($workshopid){
            //$res = $DB->update_record('question_versions',array('id'=> $qversion,'questionid'=>$id ,'status'=>$cstaus));
            $data->questionbankid = $workshopid;
            $data->questionid =$questionid;
            $data->categoryid =$qcategory;
            $data->reviewdon = time();
            $data->qstatus = $status;
            $data-> reviewdby  = $USER->id;
           
           // $data->qversion  = 1;
            $questioninfo =  $DB->get_field('local_qb_questionreview','id',array('questionid'=>$questionid,'questionbankid'=>$workshopid));
            if(empty( $questioninfo)){
                $data->id = $DB->insert_record('local_qb_questionreview',$data);
            }else{
                $data->id  =  $questioninfo;
                $DB->update_record('local_qb_questionreview',$data);
            }
            if($data->id && $status == 'publish'){
                 $qvid = $DB->get_field('question_versions','id',array('questionid'=>$questionid));
                 $DB->update_record('question_versions',array('id'=> $qvid,'questionid'=>$questionid ,'status'=>'ready'));
                //notification 
                $questiontext = $DB->get_record('question', array('id'=>$questionid));
                $reviewer = $DB->get_record('user', array('id'=>$USER->id));
                 $row=[];
                 $row['QuestionText']=$questiontext->questiontext;
                 $row['ReviewerName']=$reviewer->firstname.' '.$reviewer->lastname; 
                 $emailtype='questionbank_question_reviewed';
                 $myobject=(new \local_questionbank\notification);
                 $myobject->questionbank_notification($emailtype,$touser=null, $USER,$row,$waitinglistid=0);
                }elseif($data->id && $status !== 'publish'){
                $qvid = $DB->get_field('question_versions','id',array('questionid'=>$questionid));
                $DB->update_record('question_versions',array('id'=> $qvid,'questionid'=>$questionid ,'status'=>'draft'));
                }
                if($data->id && $status == 'underreview'){
                   //notification 
                   $questiontext = $DB->get_record('question', array('id'=>$questionid));
                    $row=[];
                    $row['QuestionText']=$questiontext->questiontext;
                    $emailtype='questionbank_question_under_review';
                    $myobject=(new \local_questionbank\notification);
                    $myobject->questionbank_notification($emailtype,$touser=null, $USER,$row,$waitinglistid=0);
                   }

        }
        purge_other_caches();
        return true;
            
        
    }
    public function movequestions($data) {  
        global $DB,$CFG;    
        require_once($CFG->dirroot . '/mod/quiz/lib.php');  
        //\core_question\local\bank\helper::require_plugin_enabled('qbank_bulkmove');   
         $questionslist= $DB->get_fieldset_sql("SELECT q.id 
                                             FROM {question} q  
                                             JOIN {question_versions} qv ON qv.questionid = q.id    
                                             JOIN {question_bank_entries} qbe ON qbe.id = qv.questionbankentryid    
                                             JOIN {question_categories} qc ON qc.id = qbe.questioncategoryid    
                                            WHERE qc.id = $data->fromcategory   
                                        "); 
       // $movequestionselected= implode(',', $questionslist);    
        $tocategory = $DB->get_record('question_categories',array('id'=>$data->tocategory));    
        list($usql, $params) = $DB->get_in_or_equal($questionslist);    
        $sql = "SELECT q.*, c.contextid 
                          FROM {question} q 
                          JOIN {question_versions} qv ON qv.questionid = q.id   
                          JOIN {question_bank_entries} qbe ON qbe.id = qv.questionbankentryid   
                          JOIN {question_categories} c ON c.id = qbe.questioncategoryid 
                         WHERE q.id 
                         {$usql}";  
        $questions = $DB->get_records_sql($sql, $params);   
        foreach ($questions as $question) { 
            question_require_capability_on($question, 'move');  
        }   
        
        question_move_questions_to_category($questionslist, $tocategory->id);    
        // \qbank_bulkmove\helper::bulk_move_questions($movequestionselected, $tocategory); 
    }

    /**
     * Get competencies Mapped to a Workspace
     * @param stdClass() object workshop
     */
    public function competencies_mapped_to_workshop($workshop) {
        global $DB, $OUTPUT;
        $competency= $workshop->competency;
        list($competencysql,$competencyparams) = $DB->get_in_or_equal(explode(',',$competency));
        $currentlang= current_language();
        if( $currentlang == 'ar'){
            $querysql = "SELECT id,(CASE WHEN arabicname IS NULL THEN name ELSE arabicname END) as name, from_unixtime(timecreated, '%Y %D %M') as timevalue FROM {local_competencies} WHERE id $competencysql";
        }else{
            $querysql = "SELECT id,name, from_unixtime(timecreated, '%Y %D %M') as timevalue FROM {local_competencies} WHERE id $competencysql";
        }
        $competencies= $DB->get_records_sql($querysql,$competencyparams);
        $viewdata=[
            'competencies'=>array_values($competencies),
            'questionbankid'=> $workshop->id,
        ];
        return $viewdata;
    }

    /**
     * get_question_competency($questionid)
     * @param Int $questionid
     * @return Competencis mapped $competencies (object)
     */
    public function get_question_competency($questionid){
        global $DB;
        $sql = "SELECT lc.id, lc.name competenyname
            FROM {local_competencies} lc
            JOIN {local_questioncompetencies} qc ON qc.competency = lc.id
            WHERE qc.questionid = :id
        ";
        $params = ['id' => $questionid];
        $competencies = $DB->get_records_sql($sql, $params);
        return $competencies;
    }
    /**
     * getquestionmappedtopics($id)
     * @param Int $id
     * @return Competencis mapped $competencies (object)
     */
    public function getquestionmappedtopics($questionid, $workshopid){
        global $DB;
        $questiontopicids = $DB->get_record('local_qb_questioncourses', ['questionbankid' => $workshopid, 'questionid' => $questionid]);
        $topicids = $questiontopicids->topic;
        $topics = new stdClass();
        if ($topicids) {
            $sql = "SELECT cs.id, IF(cs.name != NULL, cs.name, CONCAT('Topic', ' ', cs.section)) name, c.fullname
                FROM {course_sections} cs
                JOIN {course} c ON c.id = cs.course
                WHERE cs.id IN($topicids)
            ";
            $topics_data = $DB->get_records_sql($sql);
            foreach ($topics_data as $data) {
                $data->fullname = format_string($data->fullname);
            }
            $questiontopicids->topics = array_values($topics_data);
        }
        return $questiontopicids;
    }
    public function render_topics($data) {
        global $OUTPUT;
        $veiwdata = [
            // 'data' => array_values($data)
            'data' => $data->topics ? $data : false
        ];
        // print_r($veiwdata);die;
        return $OUTPUT->render_from_template('local_questionbank/topics', $veiwdata);
    }
}
function statusinfo($data) {
  $x = 0;//Counter to ensure accuracy
 //Array of Keys
  $retArray = array();//Array of Values

  foreach($data as $key => $value)
  { 
    $ret = new stdClass();
    $ret->key = $key;
    $ret->value = $value;
    $retArray[] = $ret;
    //$x++;
  }

  return $retArray;
}

function local_questionbank_leftmenunode(){
    global $USER, $SESSION, $DB, $CFG;
    $systemcontext = context_system::instance();
    $referralcode = '';
    if((!has_capability('local/organization:manage_organizationofficial', $systemcontext))) {
        if ($SESSION->orole != $DB->get_field('role', 'id', ['shortname' => 'organizationofficial'])) {
            if((!is_siteadmin() && has_capability('local/organization:manage_trainee', $systemcontext))) {
                $traineeid = $DB->get_field('role', 'id', ['shortname' => 'trainee']);
                $SESSION->orole = $traineeid;
                $USER->access['rsw'][$systemcontext->path] = $traineeid;
            } 
        }
    } 

    if(is_siteadmin() || has_capability('local/questionbank:assignreviewer',$systemcontext) || has_capability('local/questionbank:createquestion',$systemcontext) || has_capability('local/organization:manage_examofficial', $systemcontext)){
        $referralcode .= html_writer::start_tag('li', array('id'=> 'id_leftmenu_referralcode', 'class'=>'pull-left user_nav_div referralcode'));
        $referral_url = new moodle_url('/local/questionbank/index.php');
        $referral_label = get_string('questionbank','local_questionbank');
        $referral = html_writer::link($referral_url, '<span class="questionbank_icon side_menu_img_icon"></span><span class="user_navigation_link_text">'.$referral_label.'</span>',
        array('class'=>'user_navigation_link'));
        $referralcode .= $referral;
        $referralcode .= html_writer::end_tag('li');
    }

    return array('8' => $referralcode);
}

function get_noofquestion_added_by_expert($expertid, $categoryid){
    global $DB;
    $sql = "SELECT COUNT(q.id) noofquestionadded
        FROM {question_versions} v
        JOIN {question_bank_entries} be ON be.id = v.questionbankentryid
        JOIN {question} q ON q.id = v.questionid
        WHERE q.createdby = :expertid AND be.questioncategoryid = :categoryid
    ";
    $params['expertid'] = $expertid;
    $params['categoryid'] = $categoryid;
    $noofquestionsadded = $DB->get_record_sql($sql, $params);
    return $noofquestionsadded;
}
function qb_hall_reservations($qb_id, $type) {
    global $DB, $PAGE;
    if($type == 'questionbank' && $qb_id>0){
        $questionbank = $DB->get_record_sql("SELECT * FROM {local_questionbank} WHERE id = {$qb_id}");
        $where  = " AND hallid = $questionbank->halladdress";
    }

    $reservatons = $DB->get_records_sql("SELECT * FROM {hall_reservations} WHERE typeid = {$qb_id} AND type = '{$type}' $where ");
    foreach ($reservatons as $value) {
        $row = [];
        $row['hallname'] = $DB->get_field('hall', 'name', ['id' => $value->hallid]);
        $row['examdate'] = date('Y-m-d', strtotime($value->examdate));
        $row['seats'] = $value->seats;
        $data[] = $row;
    }
    return $row;
}
function get_qb_mapped_topics($qb_id) {
    global $DB;
    if (!$qb_id) {
        print_error('qb_idmissing', 'local_questionbank');
    }
    $data = $DB->get_record('local_questionbank', ['id' => $qb_id]);
    if ($data) {
        $course=$data->course;
        list($coursesql,$courseparams) = $DB->get_in_or_equal(explode(',',$data->course));
        $querysql = "SELECT c.id AS courseid,fullname as coursename FROM {course} as c
                     JOIN {local_qb_coursetopics} AS ct ON  ct.courseid=c.id AND ct.questionbankid=$data->id WHERE c.id $coursesql";
        $courses= $DB->get_records_sql($querysql,$courseparams);
        foreach ($courses AS $course) {
            $topicssql = "SELECT cs.id, (CASE WHEN cs.name IS NULL THEN CONCAT('Topic',cs.section) ELSE cs.name END) as name FROM {course_sections} as cs  
            JOIN {local_qb_coursetopics} AS ct ON  ct.topic = cs.id AND ct.courseid=$course->courseid AND ct.questionbankid=$data->id WHERE cs.course = $course->courseid AND cs.section <> 0";
            $topics_list = $DB->get_records_sql_menu($topicssql);
            $course->fullname=format_string($course->coursename);
            $course->topics = $topics_list;
        }
        return $course;
    }
}
