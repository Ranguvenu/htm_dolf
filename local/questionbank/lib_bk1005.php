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
class questionbank {
    public function create_questionbank($data) {
        global $DB,$CFG,$COURSE,$USER;
		$systemcontext = context_system::instance();
        require_once($CFG->dirroot."/question/editlib.php");
        require_once($CFG->dirroot."/question/category_class.php");
        $question = new stdClass();
        if(empty($data->id) || $data->id < 1){
            $question->workshopname = $data->workshopname;
            $workshopadmin = implode(',',$data->workshopadmin);
            $question->workshopadmin =  $workshopadmin;// $data->workshopadmin;
            $question->noofquestions =  $data->noofquestions;
            $question->workshopstarttime =  $data->startdate;
            $question->workshopendtime =  $data->enddate;
            $question->halladdress =  $data->halladdress;
            $question->status =  $data->status;
            $question->usermodified =  $USER->id;
            $question->timemodified =  time();
            $insert->id = $DB->insert_record('local_questionbank',$question);
        }else{
            $question->id= $data->id;
            $selectedcourses = $data->courses;
            $question->course= implode(',',$selectedcourses);
            $question->competency= implode(',',$data->competencies);
            $insert->id = $DB->update_record('local_questionbank',$question);
            $data = $DB->get_record('local_questionbank', array('id'=>$data->id));
            $categories=array();
            
            $cat = new stdClass();
            $cat->quesionbankid = $question->id;
            $cat->timemodified = time();
            $cat->usermodified = $USER->id;
            
            foreach($selectedcourses as $key =>$value){// creating question categories for selected courses
                $thiscontext = context_course::instance($value);
                $edittab = 'categories';
                if ($thiscontext){
                        $contexts = new question_edit_contexts($thiscontext);
                        $contexts->require_one_edit_tab_cap($edittab);
                } else {
                        $contexts = null;
                }
                $defaultcategory = question_make_default_categories($contexts->all());
                $question_category = $DB->get_record_sql("SELECT * FROM {question_categories} where name ='top' and parent= 0 and contextid=".$thiscontext->id);
                $thispageurl = new moodle_url('/local/questionbank/index.php');
                $qcobject = new question_category_object($thiscontext->id, $thispageurl,
                           $contexts->having_one_edit_tab_cap('categories'), $param->edit,
                           $question_category->id, $param->delete, $contexts->having_cap('moodle/question:add'));
                if ($insert->id && $question_category) {//new category
                 $newparent = $question_category->id.','.$thiscontext->id;
                $categoryid=$qcobject->add_category($newparent, $data->workshopname,
                         $question_category->info, 1, $question_category->infoformat,  $data->workshopname);
                } 
                $cat->courseid = $value;
                $cat->questioncategory = $categoryid;
                $qb_courses = $DB->insert_record('local_qb_courses',$cat);
                $categories[] = $categoryid;
            }
            $insert->id =  $question->id;
            // if($insert->id && $categories){
            //         $cat = new stdClass();
            //          $cat->id = $question->id;
            //          $cat->quesionbankid = implode(',',$categories);
            //          $DB->update_record('local_questionbank',$cat);
            // }
        }
       
       
        return $insert->id;

	}
     public function update_questionbank($data) {
        global $DB,$CFG,$COURSE,$USER;
        $systemcontext = context_system::instance();
        require_once($CFG->dirroot."/question/editlib.php");
        require_once($CFG->dirroot."/question/category_class.php");
        $question = new stdClass();
        if($data->form_status==0){
            $question->id= $data->id;
            $question->workshopname = $data->workshopname;
            $workshopadmin = implode(',',$data->workshopadmin);
            $question->workshopadmin =  $workshopadmin;// $data->workshopadmin;
            $question->noofquestions =  $data->noofquestions;
            $question->workshopstarttime =  $data->startdate;
            $question->workshopendtime =  $data->enddate;
            $question->halladdress =  $data->halladdress;
            $question->status =  $data->status;
            $question->usermodified =  $USER->id;
            $question->timemodified =  time();
            $update->id = $DB->update_record('local_questionbank',$question);
        }else{
            $questionbankdata = $DB->get_record('local_questionbank', array('id'=>$data->id)); 
            $existingcourses= explode(',',$questionbankdata->course);
            $existingcategories= explode(',',$questionbankdata->quesionbankid);
            $selectedcourses = $data->courses;
            $question->id= $data->id;
            $question->course= implode(',',$data->courses);
            $question->competency= implode(',',$data->competencies);
            $update->id = $DB->update_record('local_questionbank',$question);
            $removed_courses = array_diff($existingcourses,$selectedcourses);
            $new_courses = array_diff($selectedcourses,$existingcourses);

            if(count($removed_courses)> 0){
                foreach($removed_courses as $key =>$val){  
                    $thiscontext = context_course::instance($val);
                    $edittab = 'categories';
                    if ($thiscontext){
                            $contexts = new question_edit_contexts($thiscontext);
                            $contexts->require_one_edit_tab_cap($edittab);
                    } else {
                            $contexts = null;
                    }
                    $question_category = $DB->get_fieldset_sql("SELECT id FROM {question_categories} where name !=  'top' AND idnumber ='$questionbankdata->workshopname' AND  contextid=".$thiscontext->id);
                    $category= array_intersect($existingcategories, $question_category);
                    $thispageurl = new moodle_url('/local/questionbank/index.php');
                    $qcobject = new question_category_object($thiscontext->id, $thispageurl,
                               $contexts->having_one_edit_tab_cap('categories'), $param->edit,
                               $question_category->id, $param->delete, $contexts->having_cap('moodle/question:add'));
                    $qcobject->delete_category(current($category));
                }
            }
            if(count( $new_courses) > 0){
            foreach($new_courses as $key =>$value){
                $thiscontext = context_course::instance($value);
                $edittab = 'categories';
                if ($thiscontext){
                        $contexts = new question_edit_contexts($thiscontext);
                        $contexts->require_one_edit_tab_cap($edittab);
                } else {
                        $contexts = null;
                }
                $defaultcategory = question_make_default_categories($contexts->all());
                $question_category = $DB->get_record_sql("SELECT * FROM {question_categories} where name ='top' and parent= 0 and contextid=".$thiscontext->id);
                $thispageurl = new moodle_url('/local/questionbank/index.php');
                $qcobject = new question_category_object($thiscontext->id, $thispageurl,
                           $contexts->having_one_edit_tab_cap('categories'), $param->edit,
                           $question_category->id, $param->delete, $contexts->having_cap('moodle/question:add'));
                if ($insert->id && $question_category) {//new category
                 $newparent = $question_category->id.','.$thiscontext->id;
                $categoryid=$qcobject->add_category($newparent, $data->workshopname,
                         $question_category->info, 1, $question_category->infoformat,  $data->workshopname);
                } 
                $categories[] = $categoryid;
            } 
            }
            $categories = array_merge($existingcategories,$categories);
            if($question->id && $categories){
                     $cat = new stdClass();
                     $cat->id = $question->id;
                     $cat->quesionbankid = implode(',',$categories);
                     $DB->update_record('local_questionbank',$cat);
            }

        }
        // $thiscontext = context_course::instance($COURSE->id);
        // $contexts = new question_edit_contexts($thiscontext);
        // $edittab = 'categories';
        // $contexts->require_one_edit_tab_cap($edittab);
        // $question_category = $DB->get_record_sql("SELECT * FROM {question_categories} where name ='top' and parent= 0 and contextid=1");
        // $thispageurl = new moodle_url('/local/questionbank/index.php');
        // $qcobject = new question_category_object($COURSE->id, $thispageurl,
        // $contexts->having_one_edit_tab_cap('categories'), $param->edit,
        // $question_category->id, $param->delete, $contexts->having_cap('moodle/question:add'));
        // if ($insert->id && $question_category) {//new category
        // $newparent = $question_category->id.','.$COURSE->id;
        // $categoryid=$qcobject->add_category($newparent, $data->workshopname,
        //         $question_category->info, 1, $question_category->infoformat,  $data->workshopname);
        // } 
        // if($insert->id &&$categoryid){
        //     $cat = new stdClass();
        //     $cat->id = $insert->id;
        //     $cat->quesionbankid = $categoryid;
        //     $DB->update_record('local_questionbank',$cat);
        // }
       
        return $data->id;

    }
    function get_listof_questionbank($stable, $filterdata) {
        global $CFG,$DB,$OUTPUT,$USER,$PAGE;
        $selectsql= "SELECT qb.id, qb.workshopname, qb.* FROM {local_questionbank} AS qb";
     
        
        $countsql="SELECT count(qb.id) FROM {local_questionbank} AS qb";
     if(isset($filterdata->search_query) && trim($filterdata->search_query) != ''){
             $formsql .= " WHERE  qb.workshopname LIKE :search";
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
            $questionbanklist[$count]["id"] = $questionbank->id;
            $questionbanklist[$count]["workshopname"] = $questionbank->workshopname;
            $questionbanklist[$count]["noofquestions"] = $questionbank->noofquestions;
            $created= strtotime();
            $workshopdate= new stdClass();
            $workshopdate->date=date('jS F Y',strtotime(date("Y-m-d", $questionbank->workshopdate)));
            $questionbanklist [$count]["workshopdate"] = $workshopdate->date;
            $createdtime= strtotime($questionbank->workshopstarttime);
    
            $workshopdate->time=date("H:i A", $questionbank->workshopstarttime);
            $questionbanklist [$count]["workshopstarttime"] =$workshopdate->time ;
            $questionbanklist [$count]["halladdress"] =$questionbank->halladdress;
            $username=get_complete_user_data('id',$questionbank->workshopadmin);
            $questionbanklist [$count]["workshopadmin"] = $username->firstname ;
            $questionbanklist [$count]["questionbank_workshop_url"] =$CFG->wwwroot.'/local/questionbank/questionbank_workshop.php?id='.$questionbank->id;

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
        $questionbankrecord=$DB->get_record('local_questionbank',array('id' => $questionbankid));
        $username=get_complete_user_data('id',$questionbankrecord->workshopadmin);
        $hall=$DB->get_field('hall','name',array('id'=>$questionbankrecord->halladdress));
        $workshopslot=date("h:i A", $questionbankrecord->workshopstarttime).' to '.date("h:i A", $questionbankrecord->workshopendtime);
        $competancy=$questionbankrecord->competency;
        list($competencesql,$competenceparams) = $DB->get_in_or_equal(explode(',',$competancy));
        $sql = "SELECT id,shortname,idnumber FROM {competency} WHERE id $competencesql";
        $competencies= $DB->get_records_sql($sql,$competenceparams);
        $course=$questionbankrecord->course;
        list($coursesql,$courseparams) = $DB->get_in_or_equal(explode(',',$course));
        $querysql = "SELECT id,fullname FROM {course} WHERE id $coursesql";
        $courses= $DB->get_records_sql($querysql,$courseparams);
        foreach ($courses AS $course) {
            $topicssql = "SELECT id,name FROM {course_sections} WHERE course = $course->id AND section <> 0";
            $course->topics=array_values($DB->get_records_sql($topicssql));
        }
        $data=[
        'workshopname'=>ucfirst($questionbankrecord->workshopname),
        'noofquestions'=>$questionbankrecord->noofquestions,
        'workshopdate'=>date('jS F Y',strtotime(date("Y-m-d", $questionbankrecord->workshopdate))),
        'workshopslot'=>$workshopslot,
        'workshopadmin'=>$username->firstname,
        'hall'=>$hall,
        'competencylist'=>array_values($competencies),
        'courses'=>array_values($courses),
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
    
  
}
function local_questionbank_leftmenunode(){
            $systemcontext = context_system::instance();
            $referralcode = '';
            if(is_siteadmin()){
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