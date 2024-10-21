<?php
/*
* This file is a part of e abyas Info Solutions.
*
* Copyright e abyas Info Solutions Pvt Ltd, India.
*
* This program is free software; you can redistribute it and/or modify
* it under the terms of the GNU General Public License as published by
* the Free Software Foundation; either version 3 of the License, or
* (at your option) any later version.
*
* This program is distributed in the hope that it will be useful,
* but WITHOUT ANY WARRANTY; without even the implied warranty of
* MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
* GNU General Public License for more details.
*
* You should have received a copy of the GNU General Public License
* along with this program.  If not, see <http://www.gnu.org/licenses/>.
*
* @author e abyas  <info@eabyas.com>
*/
/**
 * Defines learningtracks rendering functions.
 *
 * @package    local_learningtracks
 * @copyright  e abyas  <info@eabyas.com>
 */
defined('MOODLE_INTERNAL') || die;

use context_system;
use html_table;
use html_writer;
use local_learningtracks\learningtracks as learningtracks;
use plugin_renderer_base;
use moodle_url;
use stdClass;
use single_button;
use core_user;
use core_completion\progress;
use local_trainingprogram\local\trainingprogram;
use local_exams\local\exams;
use tool_product\product as product;
require_once($CFG->dirroot . '/local/learningtracks/lib.php');
require_once($CFG->dirroot . '/local/trainingprogram/lib.php');
require_once($CFG->dirroot .'/local/exams/lib.php');
class local_learningtracks_renderer extends plugin_renderer_base {
    public function render_learningtracks($page)
    {
        $data = $page->export_for_template($this);                                                                                  
        return parent::render_from_template('local_trainingprogram/mainpage', $data);         
    }

    public function get_content($filter = false) {
        $systemcontext = context_system::instance();
        $options = array('targetID' => 'manage_learningtracks','perPage' => 5, 'cardClass' => 'col-md-6 col-12', 'viewType' => 'card');
        $options['methodName'] = 'local_learningtracks_get_learningtracks';
        $options['templateName'] = 'local_learningtracks/learningtracks_list';
        $options = json_encode($options);
        $filterdata = json_encode(array());
        $dataoptions = json_encode(array('contextid' => $systemcontext->id));
        $context = [
                'targetID' => 'manage_learningtracks',
                'options' => $options,
                'dataoptions' => $dataoptions,
                'filterdata' => $filterdata,
        ];
        if($filter){
            return  $context;
        }else{
            return  $this->render_from_template('theme_academy/cardPaginate', $context);
        }
    }

    public function lis_tracks($tracks) {
        global $USER, $CFG;
        $systemcontext = context_system::instance();
        $row = array();
        $stable = new stdClass();
        $stable->thead = true;
        $count = 0;

        $lang= current_language();

        foreach ($tracks as $list) {
            $record = array();
            $record['id'] = $list->id;

            if( $lang == 'ar'){
                $name = $list->namearabic;                
            }else{                
                $name = $list->name;               
            } 

            $record['name'] = $name;
            $record['code'] = $list->code;
            $statusarry = array(0 =>get_string('pending','local_learningtracks'), 1 => get_string('approve','local_learningtracks'), 2 => get_string('completed','local_learningtracks'), 3 => get_string('pending','local_learningtracks'));
            $record['status'] = $statusarry[$list->status];
            $stable->trackid = $list->id;
            $stable->start = 0;
            $stable->length = 1;

            $trackitems =  (new learningtracks)->get_listof_learningitems($list->id, $stable);
            $learningitems_count = $trackitems['learningitemscount'];
            $trackusers = (new learningtracks)->get_listof_users($list->id, $stable);
            $enrollcount = $trackusers['userscount'];
            $completed_count = (new learningtracks)->completed_items_count($list->id);

            $record['completed_count'] =  $completed_count;
            $record['nolearningitems'] =  $learningitems_count;
            $record['enrollcount'] =  $enrollcount;
            $record['action'] = false;
            $record['delete'] = false;
            $record['edit'] = false;
            $record['view'] = false;
      
            if(is_siteadmin() || has_capability('local/learningtracks:editlearningtracks', $systemcontext)) {
                $record['edit'] = true;
                $record['action'] = true;
            }
            if(is_siteadmin() || has_capability('local/learningtracks:deletelearningtracks', $systemcontext)) {
                $record['delete'] = true;
                $record['action'] = true;
            }
            $record['viewurl'] = $CFG->wwwroot."/local/learningtracks/view.php?id=".$list->id;
            if(is_siteadmin() || has_capability('local/learningtracks:viewlearningtracks', $systemcontext)) {
                $record['view'] = true;
                $record['action'] = true;
               
            }
            $record['count'] = $count+1;
            $count++;
            $row[] = $record;
         }
        return array_values($row);
    }

    public function get_content_viewtrack($trackid) {
        global $OUTPUT, $CFG, $DB, $USER, $PAGE;
        $systemcontext = context_system::instance();
        $stable = new stdClass();
        $stable->trackid = $trackid;
        $stable->thead = false;
        $stable->start = 0;
        $stable->length = 1;
        $learningtrack = (new learningtracks)->get_listof_learningtracks($stable);
        if(!empty($learningtrack->description)){
            $description = format_text($learningtrack->description);
        }else{
            $description = "";
        }
        $isdescription = '';
        if (empty($description)) {
           $isdescription = false;
        } else {
            $isdescription = true;
        }
        if ($learningtrack->logo > 0) {
            $trackimg = tracklogo_url($learningtrack->logo);
            if($trackimg == false){
                $trackimg = $OUTPUT->image_url('eventsampleimage', 'local_learningtracks');
                $trackimg = $trackimg->out();
            }
        } else {
            $trackimg = $OUTPUT->image_url('eventsampleimage', 'local_learningtracks');
            $trackimg = $trackimg->out();
        }
        $trackimg = $trackimg;
        $stable = new stdClass();
        $stable->trackid = $trackid;
        $stable->thead = false;
        $stable->start = 0;
        $stable->length = 1;
        $trackitems =  (new learningtracks)->get_listof_learningitems($trackid, $stable);
        $learningitems_count = $trackitems['learningitemscount'];
        $trackusers = (new learningtracks)->get_listof_users($trackid, $stable);
        $usercount = $trackusers['userscount'];
        $learningitems =  $trackitems['learningitems'];
        $completed_count = (new learningtracks)->completed_items_count($trackid);
        $edit =  false;
        $action = false;

        $lang= current_language();
        if( $lang == 'ar'){
            $trackname = $learningtrack->namearabic;                
        }else{                
            $trackname = $learningtrack->name;               
        } 

        $tabs = false;
        if ((has_capability('local/learningtracks:managelearningtracks', $systemcontext) || is_siteadmin())) {
            $action = true;
            $coursestab = true;
            $audiencetab = true;
            $tabs = true;
            $user_tab = true;
        }
        if(!is_siteadmin() &&  has_capability('local/organization:manage_organizationofficial', $systemcontext)) {
            $tabs = true;
            $user_tab = true;
            $coursestab = true;
        }
        $learningtrackcontext = [
            'learningtrack' => $learningtrack,
            'trackid' => $trackid,
            'trackname' => $trackname,
            'description' => $description,
            'isdescription' => $isdescription,
            'edit' => $edit,
            'action' => $action,
            'tabs' => $tabs,
            'course_tab' => $coursestab,
            'user_tab' =>  $user_tab,
            'audience_tab' => $audiencetab,
            'nolearningitems' => $learningitems_count,
            'usercount' => $usercount,
            'trackimg' => $trackimg,
            'completed_count' => $completed_count
        ];

      if(!is_siteadmin() && has_capability('local/organization:manage_organizationofficial', $systemcontext)) {

            $bookseatsurl = $CFG->wwwroot.'/local/learningtracks/enrollment.php?trackid='.$trackid;


            $learningtrackcontext['bookseats'] = $bookseatsurl;
      

        } 
        $return = $this->render_from_template('local_learningtracks/learningtracksContent', $learningtrackcontext);
        return $return;
    }

    public function competency_list($data) {
        global $DB, $PAGE, $OUTPUT, $CFG;
        return $this->render_from_template('local_learningtracks/competecylist', $data);
    }

    public function trackview_courses($courses, $trackid,$userid=0) {
        global $OUTPUT, $CFG, $DB,$USER;

        if($userid==0){

            $userid=$USER->id;

        }
        $context = context_system::instance();
        $data = array();

        $lang= current_language();

        foreach ($courses as $sdata) {

            $enrolbutton=true;

            $line = array();
            $line['id'] = $sdata->id;

            if ($sdata->itemtype == '1') {
                $itemdata = $DB->get_record('local_trainingprogram',['id' =>  $sdata->itemid]);

                if(!$itemdata){
                    continue;
                }
                
                if( $lang == 'ar'){
                    $name = $itemdata->namearabic;                
                }else{                
                    $name = $itemdata->name;               
                }              
                $description = format_text($itemdata->description);
        
                $viewurl =$item_url =  $CFG->wwwroot.'/local/trainingprogram/programcourseoverview.php?programid='.$itemdata->id; //$viewurl;

            
                $imageurl = trainingprogramlogo_url($itemdata->image);
                $startdate =  date('d/m/Y',$itemdata->availablefrom);
                $enddate =  date('d/m/Y',$itemdata->availableto);
                $type = get_string('pluginname', 'local_trainingprogram');
                $ownedby = '';
          
                $usercount = $DB->count_records('local_lts_item_enrolment',['trackid' => $sdata->trackid, 'itemid' => $itemdata->id, 'itemtype' => 1]);
              

                if($DB->record_exists('program_enrollments',array ('programid' =>$sdata->itemid,'userid' => $userid))) {

                    $enrolbutton=false;


                    if($DB->record_exists_sql("SELECT * FROM {local_lts_item_enrolment} WHERE trackid = $sdata->trackid AND itemid = $itemdata->id AND itemtype = 1 AND status IN (1,2) AND userid = $userid")) {
                        $complete = true;

                    }else{

                        $complete = false;

                    }

                }else{

                    $complete_status = (new learningtracks)->trackitem_completion_status($sdata->trackid, $sdata->itemid, 1);

                    if(!empty($complete_status) && $complete_status->enroluerscount == $complete_status->compltuerscount) {
                        $complete = true;
                    } else {
                        $complete = false;
                    }
                }

                $datedisplay =true;
                $trainingprograms = true;
                $exams = false;

            } else if ($sdata->itemtype == '2') {

                $itemdata = $DB->get_record('local_exams',['id' =>  $sdata->itemid]);

                if(!$itemdata){
                    continue;
                }


                if( $lang == 'ar'){
                    $name = $itemdata->examnamearabic;                
                }else{                
                    $name = $itemdata->exam;           
                } 

                $description = format_text($itemdata->programdescription);
                
               
                $itemid = $itemdata->learningmaterial;
                $ownedby = format_string($itemdata->ownedby,FORMAT_HTML);
                $type = get_string('pluginname', 'local_exams');
                $exams = true;
                $trainingprograms = false;

                $usercount = $DB->count_records('local_lts_item_enrolment',['trackid' => $sdata->trackid, 'itemid' => $itemdata->id, 'itemtype' => 2]);

                if($DB->record_exists('exam_enrollments',array ('examid' =>$sdata->itemid,'userid' => $userid))) {

                    $enrolbutton=false;


                    if($DB->record_exists_sql("SELECT * FROM {local_lts_item_enrolment} WHERE trackid = $sdata->trackid AND itemid = $itemdata->id AND itemtype = 2 AND status IN (1,2) AND userid = $userid")) {

                        $complete = true;

                    }else{

                        $complete = false;

                    }

                }else{


                    $complete_status = (new learningtracks)->trackitem_completion_status($sdata->trackid, $sdata->itemid, 2);

                    if(!empty($complete_status) && $complete_status->enroluerscount == $complete_status->compltuerscount) {

                        $complete = true;

                    } else {

                        $complete = false;

                    }
                }

                $datedisplay =false;


                if(!is_siteadmin() && has_capability('local/organization:manage_trainee',$context)) {

                    if($enrolbutton==false){

                        $datedisplay =true;

                        $moduleid = $DB->get_field('modules', 'id', ['name' => 'quiz']);

                        $quizid = $DB->get_field('course_modules', 'id', ['course' => $itemdata->courseid, 'module' => $moduleid, 'instance' => $itemdata->quizid]);

                        $quizid = !empty($quizid) ? $quizid : 0;

                       // $item_url =$CFG->wwwroot.'/mod/quiz/view.php?id='.$quizid;

                        $profileid = $DB->get_field('exam_enrollments', 'profileid', ['examid' => $sdata->itemid,'userid' => $USER->id]);
                        $profile = $DB->get_record('local_exam_profiles',['id'=>$profileid]);
                        $quizid = $DB->get_field('local_exam_profiles', 'quizid', ['id'=>$profileid ,'examid' => $sdata->itemid]);


                        $hallsheduled = (new \local_exams\local\exams)->userscheduledate($sdata->itemid, $profile,$quizid);

                        $examshedulesql = "SELECT id
                              FROM {local_exam_userhallschedules} 
                             WHERE examid =:examid
                                           AND userid =:userid";
                        $recordexists = $DB->record_exists_sql($examshedulesql,['examid'=>$sdata->itemid,'userid'=>$USER->id]);
                        if($recordexists) {
                            $examsheduledate = $DB->get_field('local_exam_userhallschedules','examdate',['examid'=>$sdata->itemid,'userid'=>$USER->id]);
                        }
                       
                        $startdate =($examsheduledate > 0) ? userdate($examsheduledate, get_string('strftimedatefullshort', 'langconfig')) : '-';
                        $enddate =  '-';
                        
                        $item_url = ($hallsheduled['purchase']) ? $CFG->wwwroot.'/local/exams/examdetails.php?id='.$sdata->itemid.'&profileid='.$profileid  : $CFG->wwwroot.'/local/exams/hallschedule.php?examid='.$sdata->itemid.'&profileid='.$profileid;



                    }else{

                        $item_url = $CFG->wwwroot.'/local/exams/exams_qualification_details.php?id='.$sdata->itemid;
                    }
                    

                } else {


                    if($enrolbutton==false){

                        $item_url = $CFG->wwwroot.'/local/exams/examdetails.php?id='.$sdata->itemid;


                    }else{

                        $item_url = $CFG->wwwroot.'/local/exams/examdetails.php?id='.$sdata->itemid.'&action=enrol';
                    }


                }

            }
            $line['progress_status'] =  '0';
            if($imageurl == false){
                $imageurl = $OUTPUT->image_url('eventsampleimage', 'local_learningtracks');
                $imageurl = $imageurl->out();
            }
            
            $isdescription = '';
            if (empty($description)) {
               $isdescription = false;
               $decsriptionstring="";
            } else {
                $isdescription = true;
                if (strlen($description) > 270) {
                    $decsriptionCut = substr($description, 0, 270); 
                    $decsriptionstring =  format_text($decsriptionCut);
                }else{
                     $decsriptionstring="";
                }
            }
            if(has_capability('local/learningtracks:viewusers', context_system::instance()) || is_siteadmin()){
                $is_progressbar =  false;
                $viewdetails = true;
            } else if(!is_siteadmin() && has_capability('local/organization:manage_organizationofficial', context_system::instance())) {
                $is_progressbar =  false;
            } else {
                $is_progressbar =  true;
                $viewdetails = false;
            }

            $line['name'] = $name;
            $line['description'] = $description;
            $line['descriptionstring'] = $decsriptionstring;
            $line['isdescription'] = $isdescription;
            $line['viewitemurl'] = $item_url;
            $line['imageurl'] = $imageurl;
            $line['startdate'] = $startdate;
            $line['datedisplay'] = $datedisplay;
            $line['enddate'] = $enddate;
            $line['ownedby'] = $ownedby;
            $line['is_progressbar'] = $is_progressbar;
            $line['viewdetails'] = $viewdetails;
            $line['type'] = $type;
            $line['item_url'] = $item_url;
            $line['itemtype'] = $sdata->itemtype;
            $line['itemid'] = $sdata->itemid;
            $line['trackid'] = $sdata->trackid;
            $line['usercount'] = $usercount;
            $line['exams'] = $exams;
            $line['trainingprograms'] = $trainingprograms;
            $line['complete_status'] = $complete;
            $line['enrolbutton']=$enrolbutton;
            $data[] = $line; 
        }
        return array('data' => $data);
    }

    public function get_learningpath($filter = false) {
        $systemcontext = context_system::instance();
        $options = array('targetID' => 'manage_learningpath','perPage' => 1, 'cardClass' => 'col-md-6 col-12', 'viewType' => 'card');
        $options['methodName'] = 'local_learningtracks_get_learningpath';
        $options['templateName'] = 'local_learningtracks/learning_path';
        $options = json_encode($options);
        $filterdata = json_encode(array());
        $dataoptions = json_encode(array('contextid' => $systemcontext->id));
        $context = [
                'targetID' => 'manage_learningpath',
                'options' => $options,
                'dataoptions' => $dataoptions,
                'filterdata' => $filterdata,
        ];
        if($filter){
            return  $context;
        }else{
            return  $this->render_from_template('theme_academy/cardPaginate', $context);
        }
    }
    public function track_check($id) {
        global $OUTPUT, $CFG, $DB, $USER, $PAGE;
        $stable = new stdClass();
        $stable->trackid = $id;
        $stable->thead = false;
        $stable->start = 0;
        $stable->length = 1;
        $track =  (new learningtracks)->get_listof_learningtracks($stable);
        $context = context_system::instance();
        if (empty($track)) {
            print_error("Data Not Found!", 'error');
        }
        return $track;
    }

    public function trackview_users($users, $trackid) {
        global $OUTPUT, $CFG, $DB,$USER;
        $context = context_system::instance();
        $data = array();

         $currentlang= current_language();

        foreach ($users as $sdata) {
            $line = array();
            $line['id'] = $sdata->id;

            $fullname=(new \local_trainingprogram\local\trainingprogram)->user_fullname_case();

            $sql="SELECT u.id,$fullname 
                FROM {user} AS u 
                JOIN {local_users} lc ON lc.userid = u.id
                JOIN {local_organization} org ON org.id = lc.organization
                WHERE  u.id=:orguserid ";

            $user=$DB->get_record_sql($sql,array('orguserid'=>$sdata->id));      
            if($user->fullname=="")
            {
                if(current_language() == 'ar')
                {
                     $sqlname = "SELECT firstnamearabic AS firstname,lastnamearabic AS lastname FROM {local_users} WHERE email=:email";
                }
                else
                {
                     $sqlname = "SELECT firstname,lastname FROM {local_users} WHERE email=:email";
                }
               
                $fullname = $DB->get_record_sql($sqlname,array('email'=>$sdata->email));
               
            }
            $concatname = $fullname->firstname.' '.$fullname->lastname;
            $line['username']= ($user) ? $user->fullname : $concatname;

            $line['email'] = $sdata->email;

            $total_courses = (new learningtracks)->get_total_learningitems($trackid);

            $total_enrolledcourses = (new learningtracks)->get_listof_enrolledcourses($trackid, $sdata->id);
            $completed_enrolledcourses = (new learningtracks)->get_listof_enrolledcourses($trackid, $sdata->id, 1);
            $remaining_completed_enrolledcourses = (new learningtracks)->get_listof_enrolledcourses($trackid, $sdata->id, 0);

            $line['totalcourses'] = $total_courses;
            $line['enrollcount'] = $total_enrolledcourses;
            $line['completed_count'] = $completed_enrolledcourses;
            $line['remaining_count'] = $remaining_completed_enrolledcourses;
            $line['completedstatus'] = false;
            if($total_courses > 0) {
                if($total_courses == $completed_enrolledcourses) {
                    $line['completedstatus'] = true;
                } else {
                    $line['completedstatus'] = false;
                }
            }
            $line['viewurl'] = $CFG->wwwroot."/local/learningtracks/learningpathview.php?id=".$trackid.'&userid='.$sdata->id;
            $data[] = $line; 
        }
        return array('data' => $data);
    }

    public function get_learningpath_cardview($filter = false) {
        $systemcontext = context_system::instance();
        $options = array('targetID' => 'viewlearninpathdata','perPage' => 5, 'cardClass' => 'col-md-6 col-12', 'viewType' => 'card');
        $options['methodName'] = 'local_learningtracks_viewlearningpath';
        $options['templateName'] = 'local_learningtracks/learning_path_cards';
        $options = json_encode($options);
        $filterdata = json_encode(array());
        $dataoptions = json_encode(array('contextid' => $systemcontext->id, 'append' => 1));
        $context = [
                'targetID' => 'viewlearninpathdata',
                'options' => $options,
                'dataoptions' => $dataoptions,
                'filterdata' => $filterdata,
        ];
        if($filter) {
            return  $context;
        } else {
            return  $this->render_from_template('theme_academy/cardPaginate', $context);
        }
    }

    public function listoflearningtracks($filterparams) {
        global $DB, $PAGE, $OUTPUT;
        $systemcontext = context_system::instance();
        $filterparams['addaction'] = (is_siteadmin() || has_capability('local/organization:manage_trainingofficial',$systemcontext)) ? true : false;
        echo $this->render_from_template('local_learningtracks/learningtracks_card', $filterparams);
    }

    public function listofcardviewlearningpath($filterparams) {
        global $DB, $PAGE, $OUTPUT;
        echo $this->render_from_template('local_learningtracks/learning_path_list', $filterparams);
    }
    public function global_filter($filterparams) {
        global $DB, $PAGE, $OUTPUT;
        return $this->render_from_template('theme_academy/global_filter', $filterparams);;
    }

    public function learningpath_list($stable, $filterdata) {
        global $USER, $CFG, $DB, $OUTPUT;
        $systemcontext = context_system::instance();
        $gettracks = learningtracks::get_listof_learningtracks($stable, $filterdata);
        $tracks = array_values($gettracks['tracks']);
        $row = array();
        $stable = new \stdClass();
        $stable->thead = true;
        $count = 0;
        foreach ($tracks as $list) {
            $record = array();
            $record['id'] = $list->id;

            $lang= current_language();
            if( $lang == 'ar'){
                $name = $list->namearabic;                
            }else{                
                $name = $list->name;               
            } 

            $record['name'] = $name;
            $record['code'] = $list->code;
            $statusarry = array(0 => get_string('pending','local_learningtracks'), 1 => get_string('approve','local_learningtracks'), 2 => get_string('completed','local_learningtracks'), 3 =>get_string('rejected','local_learningtracks'));
            $record['status'] = $statusarry[$list->status];
            $stable->trackid = $list->id;
            $stable->start = 0;
            $stable->length = 1;
            $courses =  (new learningtracks)->get_listof_learningitems($list->id, $stable, 1);
            $record['courses_count'] = $courses['learningitemscount'];

            $exams =  (new learningtracks)->get_listof_learningitems($list->id, $stable, 2);
            $record['exams_count'] = $exams['learningitemscount'];

            $trackitems =  (new learningtracks)->get_listof_learningitems($list->id, $stable);
            $learningitems_count = $trackitems['learningitemscount'];
            $trackusers = (new learningtracks)->get_listof_users($list->id, $stable);
            $enrollcount = $trackusers['userscount'];
            $record['nolearningitems'] =  $learningitems_count;
            $record['enrollcount'] =  $enrollcount;
            if(!empty($list->description)){
                $description = format_text($list->description);
            }else{
                $description = "";
            }
            $isdescription = '';
            if (empty($description)) {
               $isdescription = false;
            } else {
                $isdescription = true;
                if (strlen($description) > 200) {
                    $decsriptionCut = substr($description, 0, 200);
                    $descriptionstring =format_text($decsriptionCut);
                }else{
                    $descriptionstring = "";
                }
            }
            $record['description'] =  $description;
            $record['isdescription'] =  $isdescription;
            $record['descriptionstring'] =  $descriptionstring;
            if ($list->logo > 0) {
                $trackimg = tracklogo_url($list->logo);
                if($trackimg == false){
                    $trackimg = $OUTPUT->image_url('eventsampleimage', 'local_learningtracks');
                    $trackimg = $trackimg->out();
                }
            } else {
                $trackimg = $OUTPUT->image_url('eventsampleimage', 'local_learningtracks');
                $trackimg = $trackimg->out();
            }
            $trackimg = $trackimg;
            $record['trackimg'] =  $trackimg;
            $record['count'] = $count+1;
            $count++;
            $row[] = $record;
        }
        return array_values($row);
    }

    public function get_enrolled_learningpath($filter = false) {
        $systemcontext = context_system::instance();
        $options = array('targetID' => 'viewenrollerlp','perPage' => 5, 'cardClass' => 'col-md-6 col-12', 'viewType' => 'card');
        $options['methodName'] = 'local_learningtracks_enrolledlearningpath';
        $options['templateName'] = 'local_learningtracks/mylearningpath_list';
        $options = json_encode($options);
        $filterdata = json_encode(array());
        $dataoptions = json_encode(array('contextid' => $systemcontext->id));
        $context = [
                'targetID' => 'viewenrollerlp',
                'options' => $options,
                'dataoptions' => $dataoptions,
                'filterdata' => $filterdata,
        ];
        if($filter) {
            return  $context;
        } else {
            return  $this->render_from_template('theme_academy/cardPaginate', $context);
        }
    }

    public function get_enrolled_courses($filter = false) {
        $systemcontext = context_system::instance();
        $trackid = optional_param('id', 0, PARAM_INT);
        $options = array('targetID' => 'my_enrolled_courses','perPage' => 5, 'cardClass' => 'col-md-6 col-12', 'viewType' => 'card');
        $options['methodName'] = 'local_learningtracks_trackview_courses';
        $options['templateName'] = 'local_learningtracks/my_enrolled_courses';
        $options['trackid'] = $trackid;
        $options = json_encode($options);
        $filterdata = json_encode(array());
        $dataoptions = json_encode(array('contextid' => $systemcontext->id, 'trackid' => $trackid));
        $context = [
                'targetID' => 'my_enrolled_courses',
                'options' => $options,
                'dataoptions' => $dataoptions,
                'filterdata' => $filterdata,
        ];
        if($filter) {
            return  $context;
        } else {
            return  $this->render_from_template('theme_academy/cardPaginate', $context);
        }
    }


    public function my_learning_tracks($tracks) {
        global $USER, $CFG, $DB;
        $systemcontext = context_system::instance();
        $row = array();
        $stable = new \stdClass();
        $stable->thead = true;
        $stable->userid = $USER->id;
        $count = 0;
        foreach ($tracks as $list) {
            $record = array();
            $record['id'] = $list->id;

            $lang= current_language();
            if( $lang == 'ar'){
                $name = $list->namearabic;                
            }else{                
                $name = $list->name;               
            } 

            $record['name'] = $name;
            $record['code'] = $list->code;
            $statusarry = array(0 => get_string('pending','local_learningtracks'), 1 => get_string('approve','local_learningtracks'), 2 => get_string('completed','local_learningtracks'), 3 =>  get_string('rejected','local_learningtracks'));
            $record['status'] = $statusarry[$list->status];
            
            $total_courses = (new learningtracks)->get_total_learningitems($list->id);

            $total_enrolledcourses = (new learningtracks)->get_listof_enrolledcourses($list->id, $USER->id);
            $completed_enrolledcourses = (new learningtracks)->get_listof_enrolledcourses($list->id, $USER->id, 1);
            $remaining_completed_enrolledcourses = (new learningtracks)->get_listof_enrolledcourses($list->id, $USER->id, 0);

            $record['totalcourses'] = $total_courses;
            $record['enrollcount'] = $total_enrolledcourses;
            $record['completed_count'] = $completed_enrolledcourses;
            $record['remaining_count'] = $remaining_completed_enrolledcourses;
            $record['completedstatus'] = false;
            $trackstatus = (new learningtracks)->get_current_track_status($list->id, $USER->id);


           
            $record['completedstatus'] = ($trackstatus) ? true : false;
               
            $stable->trackid = $list->id;
            $stable->start = 0;
            $stable->length = 1;
            $trackitems =  (new learningtracks)->get_listof_learningitems($list->id, $stable);
            $learningitems_count = $trackitems['learningitemscount'];
            $trackusers = (new learningtracks)->get_listof_users($list->id, $stable);
            $enrollcount = $trackusers['userscount'];
            $record['nolearningitems'] =  $learningitems_count;
            $record['enrollcount1'] =  $enrollcount;
            $record['action'] = false;
            $record['delete'] = false;
            $record['edit'] = false;
            $record['view'] = false;
            $record['viewurl'] = $CFG->wwwroot."/local/learningtracks/learningpathview.php?id=".$list->id;
            $record['count'] = $count+1;
            $count++;
            $certid = $DB->get_field('tool_certificate_issues', 'code', array('moduleid'=>$list->id,'userid'=>$USER->id,'moduletype'=>'learningtracks'));
            $record['certid'] = $certid? $certid : 0;
            $row[] = $record;
         }
        return array_values($row);
    }
    public function get_mytrackview($trackid,$userid) {
        global $OUTPUT, $CFG, $DB, $USER, $PAGE;
        $stable = new stdClass();
        $stable->trackid = $trackid;
        $stable->thead = false;
        $stable->start = 0;
        $stable->length = 1;
        $learningtrack = (new learningtracks)->get_listof_learningtracks($stable);
        if(!empty($learningtrack->description)){
            $trackdescription = format_text($learningtrack->description, FORMAT_HTML);
        }else{
            $trackdescription = "";
        }
        $isdescription = '';
        if (empty($trackdescription)) {
           $isdescription = false;
        } else {
            $isdescription = true;
        }
        if ($learningtrack->logo > 0) {
            $trackimg = tracklogo_url($learningtrack->logo);
            if($trackimg == false){
                $trackimg = $OUTPUT->image_url('eventsampleimage', 'local_learningtracks');
                $trackimg = $trackimg->out();
            }
        } else {
            $trackimg = $OUTPUT->image_url('eventsampleimage', 'local_learningtracks');
            $trackimg = $trackimg->out();
        }
        $trackimg = $trackimg;

        $total_courses = (new learningtracks)->get_total_learningitems($trackid);

        $total_enrolledcourses = (new learningtracks)->get_listof_enrolledcourses($trackid, $userid);
        $completed_enrolledcourses = (new learningtracks)->get_listof_enrolledcourses($trackid,$userid, 1);
        $remaining_completed_enrolledcourses = (new learningtracks)->get_listof_enrolledcourses($trackid,$userid, 0);


        $trackusers = (new learningtracks)->get_listof_users($trackid, $stable);
        $learning_items = [];
        $stable->search = false;
        $stable->thead = false;
        $stable->start = 0;
        $stable->length = '';
        $courses =  learningtracks::get_listof_learningitems($trackid, $stable);
        if($courses) {
            $i =1;
            foreach($courses['learningitems'] as $item) {
                $count = $i;
                $item_status = (new learningtracks)->get_listof_enrolledcourses($trackid, $userid, null, $item->itemid, $item->itemtype);

                $completedstatus = ($item_status->status =='1' || $item_status->status =='2') ?  true : false;
                $completed = ($item_status->status =='1' || $item_status->status =='2') ?  'completed' : '';
              
                $learning_items[] = ['count' => $count, 'completed' =>  $completed , 'completedstatus' => $completedstatus];
                $i++;
            }
        }
        $
        $usercount = $trackusers['userscount'];

         $lang= current_language();
        if( $lang == 'ar'){
            $name = $learningtrack->namearabic;                
        }else{                
            $name = $learningtrack->name;               
        } 

        $learningtrackcontext = [
            'learningtrack' => $learningtrack,
            'trackid' => $trackid,
            'trackname' => $name,
            'trackdescription' => $trackdescription,
            'isdescription' => $isdescription,
            'totalcourses' => $total_courses,
            'nolearningitems' => $total_enrolledcourses,
            'remainingcount' => $remaining_completed_enrolledcourses,
            'trackimg' => $trackimg,
            'completed_count' => $completed_enrolledcourses,
            'enrolled_users' => $usercount,
            'learning_items' => $learning_items,
            'userid' => $userid,
        ];
        $return = $this->render_from_template('local_learningtracks/mylearningtracksContent', $learningtrackcontext);
        return $return;
    }

    public function manage_capability() {
        $systemcontext = context_system::instance();
        if(is_siteadmin() || has_capability('local/learningtracks:managelearningtracks', $systemcontext)  
         || has_capability('local/organization:manage_organizationofficial',$systemcontext) ){
           return true;
        } else {
            print_error(get_string('permissionerror', 'local_learningtracks'));
        }
    }

    public function listoftracks($filterparams) {
        $filterparams['addaction'] = is_siteadmin() ? true : false;
        echo $this->render_from_template('local_learningtracks/listoftracks', $filterparams);
    }

}
