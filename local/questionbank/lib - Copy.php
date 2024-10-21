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
use qbank_bulkmove;
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
                $workshopadmin = implode(',',$data->workshopadmin);
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
             $row=[];
             $row['WorkshopName']=$data->workshopname;
             $row['QuestionBankName']=$data->workshopname;
             $row['WorkshopDate']=$data->startdate;
             $row['WorkshopTime']=$data->duration;
            $myobject=(new \local_questionbank\notification);
            $myobject->questionbank_notification('questionbank_workshop_created',$touser=null, $USER,$row,$waitinglistid=0);
            //-----------------------
            $exam_official=$DB->get_fieldset_sql("SELECT CONCAT(firstname,' ',lastname) as fullname FROM  {user} AS u WHERE id IN ($question->workshopadmin)");
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
            $selectedcourses = $data->courses;
            $question->course= implode(',',$selectedcourses);
            $question->competency= implode(',',$data->competencies);
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
            $question_category_parent = $DB->get_field_sql("SELECT id FROM {question_categories} where name ='top' and parent= 0 AND contextid=1");
            $question_category = $DB->get_record_sql("SELECT * FROM {question_categories} where name ='Workshop Categories' AND idnumber='workshop_categories' and parent= $question_category_parent AND contextid=1");
            $thispageurl = new moodle_url('/local/questionbank/index.php');
            $qcobject = new question_category_object($thiscontext->id, $thispageurl,
            $contexts->having_one_edit_tab_cap('categories'), $param->edit,
                                        $question_category->id, $param->delete, $contexts->having_cap('moodle/question:add'));
            if ($insert->id && $question_category) {//new category
                $newparent = $question_category->id.','.$thiscontext->id;
                $categoryid=$qcobject->add_category($newparent, $data->workshopname,
                                       $question_category->info, 1, $question_category->infoformat,  $data->workshopname);
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
            $question->workshopname = $data->workshopname;
           if(is_siteadmin()){
                $workshopadmin = implode(',',$data->workshopadmin);
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
             if(!$touser)
                {
                $touser=null;
                }
            $row1=[];
            $row1['RelatedModuleName']=$data->workshopname;
            $row1['ProgramLink']=$CFG->dirroot.'/local/questionbank/questionbank_workshop.php?id='.$data->id;
            $myobject=(new \local_questionbank\notification);
            $myobject->questionbank_notification('questionbank_reschedule',$touser, $USER,$row1,$waitinglistid=0);
            }
            $update->id = $DB->update_record('local_questionbank',$question);
            
        }else{
           
           
            $existingcourses= explode(',',$questionbankdata->course);
            $existingcategories= explode(',',$questionbankdata->qcategoryid);
            $selectedcourses = $data->courses;
            $question->id= $data->id;
            $question->course= implode(',',$data->courses);
            $question->competency= implode(',',$data->competencies);
            $update->id = $DB->update_record('local_questionbank',$question);
            $thiscontext = context_system::instance();
            $edittab = 'categories';
            if ($thiscontext){
                    $contexts = new question_edit_contexts($thiscontext);
                    $contexts->require_one_edit_tab_cap($edittab);
            } else {
                    $contexts = null;
            }
            $defaultcategory = question_make_default_categories($contexts->all());
            $question_category = $DB->get_record_sql("SELECT * FROM {question_categories} where id=".$questionbankdata->qcategoryid);
            // $question_parentcategory = $DB->get_record_sql("SELECT * FROM {question_categories} where name ='top' and parent= 0 AND contextid=1");
            $question_category_parent = $DB->get_field_sql("SELECT id FROM {question_categories} where name ='top' and parent= 0 AND contextid=1");
            $question_parentcategory = $DB->get_record_sql("SELECT * FROM {question_categories} where name ='Workshop Categories' AND idnumber='workshop_categories' and parent= $question_category_parent AND contextid=1");
            $thispageurl = new moodle_url('/local/questionbank/questionbank_workshop.php?id='.$data->id);
            $qcobject = new question_category_object($thiscontext->id, $thispageurl,
            $contexts->having_one_edit_tab_cap('categories'), $param->edit,
                                        $question_category->id, $param->delete, $contexts->having_cap('moodle/question:add'));
            if ($question_category) {//new category
                $newparent = $question_parentcategory->id.','.$thiscontext->id;
                $qcobject->update_category($question_category->id, $newparent,
                $questionbankdata->workshopname, $question_category->info, $question_category->infoformat,  $questionbankdata->workshopname);
            } 

        }
         // Trigger update questionbank.
         $eventparams = array('context' => context_system::instance(),'objectid'=>$update->id);
         $event = local_questionbank\event\update_questionbank::create($eventparams);
         $event->trigger();
         // Notification-----------------------
         //-----------------------
         $row=[];
         $row['WorkshopName']=$data->workshopname;
         $row['QuestionBankName']=$data->workshopname;
         $row['WorkshopDate']=$data->startdate;
         $row['WorkshopTime']=$data->duration;
         $myobject=(new \local_questionbank\notification);
         $myobject->questionbank_notification('questionbank_workshop_updated',$touser=null, $USER,$row,$waitinglistid=0);
          // notification Questionbank  onchange
          $sql="SELECT u.* FROM {user} u
          JOIN {local_qb_experts} le ON le.expertid = u.id
          WHERE le.questionbankid = $data->id AND u.confirmed = 1 AND u.suspended = 0 AND u.deleted = 0 AND u.id > 2";
        $touser = $DB->get_records_sql($sql);
        if(!$touser)
        {
            $touser=null;
        }
         $row1=[];
         $row1['RelatedModuleName']=$data->workshopname;
         $row1['RelatedModulesLink']=$CFG->dirroot.'/local/questionbank/questionbank_workshop.php?id='.$data->id;
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

        }

        if($filterdata->status){
            $wheresql .= " AND date(FROM_UNIXTIME(qb.workshopdate)) < CURDATE()";
            $actions = false;
        }else{
            $wheresql .= " AND date(FROM_UNIXTIME(qb.workshopdate)) >= CURDATE()";
        }
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
            $questionbanklist[$count]["topicid"] = [35,36];
            $questionbanklist[$count]["workshopname"] = $questionbank->workshopname;
            $questionbanklist[$count]["noofquestions"] = $questionbank->noofquestions;
            $created= strtotime();
            $workshopdate= new stdClass();
            $workshopdate->date=date("Y-F-d", $questionbank->workshopdate);
            $questionbanklist [$count]["workshopdate"] = $workshopdate->date;
            $createdtime= strtotime($questionbank->workshopstarttime);
    
            $workshopdate->time=date("H:i A", $questionbank->workshopstarttime);
            $questionbanklist [$count]["workshopstarttime"] =$workshopdate->time ;
            $hallinfo= $DB->get_record_sql("SELECT name,city FROM {hall} WHERE id =".$questionbank->halladdress);
            $questionbanklist [$count]["halladdress"] =$hallinfo->name;
            $questionbanklist [$count]["location"] ='Riyadh';
            $questionbanklist [$count]["workshopenddate"] =date("Y-F-d", $questionbank->workshopendtime);
            $questionbanklist [$count]["qcategory"]= $questionbank->qcategoryid.',1';
            $questionbanklist [$count]["qcategoryid"]= $questionbank->qcategoryid;
            $username=get_complete_user_data('id',$questionbank->workshopadmin);
            $questionbanklist [$count]["workshopadmin"] = $username->firstname.' '.$username->lastname ;
            $questionbanklist [$count]["questionbank_workshop_url"] =$CFG->wwwroot.'/local/questionbank/questionbank_workshop.php?id='.$questionbank->id;
            $qsql = "SELECT q.id 
                                         FROM {question} q  
                                         JOIN {question_versions} qv ON qv.questionid = q.id    
                                         JOIN {question_bank_entries} qbe ON qbe.id = qv.questionbankentryid    
                                         JOIN {question_categories} qc ON qc.id = qbe.questioncategoryid    
                                        WHERE qc.id = $questionbank->qcategoryid ";
            $qstatussql = "  AND qv.status='ready'";
            $questionslist= $DB->get_fieldset_sql($qsql);
            $publishedquestions= $DB->get_fieldset_sql($qsql.$qstatussql);
            $publishedquestions= count( $publishedquestions);
            $totalquestions = count( $questionslist);
            if($totalquestions > 0 &&  ($totalquestions ==  $publishedquestions)){
                $questioncount = true;
            }
            $questionbanklist [$count]["questionscount"]  = $questioncount;
            $questionbanklist [$count]['actions'] = $actions;
           
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
        if($workshopdate < $currentdate){
            $actions = false;
        }
        //exit;
        //if(date($questionbankrecord->workshopdate))
        $experts_info=$DB->get_fieldset_sql("SELECT CONCAT(firstname,' ',lastname) as fullname FROM {local_qb_experts} as qe JOIN {user} as u ON u.id = qe.expertid where questionbankid =".$questionbankid);
        if(!empty($experts_info)){
          $expertslist = implode(',',$experts_info);
        }else{
           $expertslist = 'Not Assigned'; 
        }
        
        $username=get_complete_user_data('id',$questionbankrecord->workshopadmin);
        $hall=$DB->get_record('hall',array('id'=>$questionbankrecord->halladdress));
        //$workshopslot=date("h:i A", $questionbankrecord->workshopstarttime).' to '.date("h:i A", $questionbankrecord->workshopendtime);
        $workshopslot=date("h:i A", $questionbankrecord->workshopstarttime);
        $competancy=$questionbankrecord->competency;
        list($competencesql,$competenceparams) = $DB->get_in_or_equal(explode(',',$competancy));
        $sql = "SELECT id,name,code FROM {local_competencies} WHERE id $competencesql";
        $competencies= $DB->get_records_sql($sql,$competenceparams);
        $course=$questionbankrecord->course;
        list($coursesql,$courseparams) = $DB->get_in_or_equal(explode(',',$course));
        $querysql = "SELECT id,fullname FROM {course} WHERE id $coursesql";
        $courses= $DB->get_records_sql($querysql,$courseparams);
        foreach ($courses AS $course) {
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

        $courseactions = count($courses) > 1 ? true : false;
        $competenciesactions = count($competencies) > 1 ? true : false;
        $data=[
        'workshopname'=>ucfirst($questionbankrecord->workshopname),
        'workshopid'=>ucfirst($questionbankrecord->id),
        'noofquestions'=>$questionbankrecord->noofquestions,
        'workshopdate'=>date("Y-F-d", $questionbankrecord->workshopdate),
        'workshopslot'=>$workshopslot,
        'workshopadmin'=>$username->firstname,
        'hall'=>$hall->name,
        'location'=>'Riyadh',
        'competencylist'=>array_values($competencies),
        'courses'=>array_values($courses),
        'experts'=>$expertslist,
        'courseactions' => $courseactions,  
        'competenciesactions' => $competenciesactions,
        'actions' => $actions,
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
        $selectsql = "SELECT u.id as userid, CONCAT(u.firstname,'',u.lastname) as fullname, qb.* ";
        $countsql  = "SELECT COUNT(qb.id)";
        $formsql =  " FROM {local_qb_experts} qb";
        $formsql .=  "  JOIN {user} u ON u.id = qb.expertid"; 
        $formsql .=  "  WHERE qb.questionbankid = $filterdata->workshopid"; 
        if(isset($filterdata->search_query) && trim($filterdata->search_query) != ''){
            $formsql .= " AND u.firstname LIKE :search";
            $searchparams = array('search' => '%'.trim($filterdata->search_query).'%');
        }else{
            $searchparams = array();
        }     
        $params = array_merge($searchparams);
        $totalexperts = $DB->count_records_sql($countsql.$formsql, $params);
        $formsql .=" ORDER BY qb.id DESC";
        $experts = $DB->get_records_sql($selectsql.$formsql, $params, $stable->start,$stable->length);
        $expertslist = array();
        $count = 0;
        if ($experts) {
            foreach($experts as $list) {
                $expertslist[ $count]['id'] = $list->id;
                $expertslist[ $count]['userid'] = $list->userid;
                $expertslist[ $count]['username'] = $list->fullname;
                $expertslist[ $count]['dateadded'] = date('d M Y', $list->timecreated); 
                $count++;
            }
            $nodata = true;
        } else {
            $nodata = false;
        }
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
        $competencies =  $DB->get_field('local_questionbank', 'competency', ['id' => $filterdata->workshopid]);
        if($competencies){
            $selectsql = "SELECT * FROM {local_competencies} le WHERE 1=1 AND id IN ($competencies) "; 
            $countsql  = "SELECT COUNT(le.id) FROM {local_competencies} le WHERE 1=1 AND id IN ($competencies) ";
            if(isset($filterdata->search_query) && trim($filterdata->search_query) != ''){
                $formsql .= " AND le.name LIKE :search";
                $searchparams = array('search' => '%'.trim($filterdata->search_query).'%');
            }else{
                $searchparams = array();
            }     
            $params = array_merge($searchparams);
            $totalcompetency = $DB->count_records_sql($countsql.$formsql, $params);
            $formsql .=" ORDER BY le.id DESC";
            $addedcompetencies = $DB->get_records_sql($selectsql.$formsql, $params, $stable->start,$stable->length);
           
        }    
         $coursesContext = array(
                "acompetencies" => $addedcompetencies,
                "nocourses" => $nocourse,
                "totalcount" => $totalcompetency,
                "length" => $totalcompetency
        );    
        return $coursesContext;
    }
   
    
public function expertlist() {
    global $DB;
    $data = $DB->get_records_sql("SELECT ra.userid as id, CONCAT(u.firstname,'',u.lastname) as fullname
    FROM {role_assignments} as  ra 
            JOIN {role} as r ON ra.roleid = r.id  AND r.shortname = 'expert'
            JOIN {user} as u ON u.id= ra.userid");
    return $data;
}


public function create_qb_experts($data) {
    global $DB, $USER;
    $systemcontext = context_system::instance();
    $expertids = $data->expertid;
    foreach($expertids as $ids) {
        $data->questionbankid = $data->questionbankid;
        $data->expertid = $ids;
        $data->timecreated = time();
        $data->usercreated = $USER->id;
        $DB->insert_record('local_qb_experts',$data);
         //-----------------------
         $questionbank = $DB->get_record('local_questionbank', array('id'=>$data->questionbankid));
         $experts_info=$DB->get_fieldset_sql("SELECT CONCAT(firstname,' ',lastname) as fullname FROM {local_qb_experts} as qe JOIN {user} as u ON u.id = qe.expertid where questionbankid =".$data->questionbankid);
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
       if(!$touser)
       {
           $touser=null;
       }
        $row1=[];
        $row1['RelatedModuleName']=$data->workshopname;
        $row1['RelatedModulesLink']=$CFG->dirroot.'/local/questionbank/questionbank_workshop.php?id='.$data->questionbankid;
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
    //purge_other_caches();
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



}
function statusinfo($data)
{
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
            $systemcontext = context_system::instance();
            $referralcode = '';
            if(is_siteadmin() || has_capability('local/questionbank:assignreviewer',$systemcontext) || has_capability('local/questionbank:createquestion',$systemcontext) || has_capability('local/organization:manage_examofficial', $systemcontext)){
            $referralcode .= html_writer::start_tag('li', array('id'=> 'id_leftmenu_referralcode', 'class'=>'pull-left user_nav_div referralcode'));
            $referral_url = new moodle_url('/local/questionbank/index.php');
            $referral_label = get_string('pluginname','local_questionbank');
            $referral = html_writer::link($referral_url, '<span class="questionbank_icon side_menu_img_icon"></span><span class="user_navigation_link_text">'.$referral_label.'</span>',
            array('class'=>'user_navigation_link'));
            $referralcode .= $referral;
            $referralcode .= html_writer::end_tag('li');
            }

            return array('8' => $referralcode);
}
