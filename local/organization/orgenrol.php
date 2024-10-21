<?php
// This file is part of Moodle - http://moodle.org/
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

ini_set('memory_limit', '-1');
define('NO_OUTPUT_BUFFERING', true);
require('../../config.php');
require_once($CFG->dirroot . '/local/organization/lib.php');
require_once($CFG->dirroot . '/local/organization/filters_form.php');
global $CFG,$DB,$USER,$PAGE,$OUTPUT,$SESSION;

$systemcontext = context_system::instance();
$PAGE->set_context($systemcontext);
require_capability('local/organization:visible',$systemcontext);

$view       = optional_param('view','page', PARAM_RAW);
$type       = optional_param('type','', PARAM_RAW);
$lastitem   = optional_param('lastitem',0, PARAM_INT);
$org_id     = required_param('orgid', PARAM_INT);
$submit_value = optional_param('submit_value','', PARAM_RAW);
$add        = optional_param('add',array(), PARAM_RAW);
$remove     =optional_param('remove',array(), PARAM_RAW);
$org = $DB->get_record('local_organization', ['id' => $org_id], '*', MUST_EXIST);
$query     =optional_param('query',array(), PARAM_RAW);
$context = context_system::instance();
$PAGE->set_url('/local/organization/orgenrol.php', array('orgid'=>$org_id ));
//$PAGE->set_pagelayout('admin');
$PAGE->navbar->add(get_string('manage_organization','local_organization'),new moodle_url('/local/organization/index.php'));
$PAGE->navbar->add($org->fullname ,new moodle_url('/local/organization/orguser.php?orgid='.$org_id));
$PAGE->navbar->add(get_string('userenrolments', 'local_organization'),new moodle_url('/local/organization/orgenrol.php?orgid='.$org_id));
$PAGE->requires->jquery();
$PAGE->requires->jquery_plugin('ui');
$PAGE->requires->js('/local/organization/js/jquery.bootstrap-duallistbox.js',true);
$PAGE->requires->css('/local/organization/css/bootstrap-duallistbox.css');
$PAGE->set_title(get_string('manualenrol','local_organization'));

if(!$add && !$remove){
    $PAGE->set_heading($org->fullname);
}
$PAGE->requires->js_call_amd('theme_academy/cardPaginate', 'filteringData', array($context));
require_login();
if($view == 'ajax'){
    $options =(array)json_decode($_GET["options"],false);
    $search_query  = ['query' => $query] ;
    $options  = array_merge($options,$search_query);
    $select_from_users=(new local_organization\organization)->org_enrolled_users($type, $org_id,$options,false,$offset1=-1,$perpage=50,$lastitem);
    $select_from_userstotal = (new local_organization\organization)->org_enrolled_users($type, $org_id, $options, true, $offset1=-1, $perpage=-1);
    echo json_encode(array('content' =>$select_from_users,'count' =>$select_from_userstotal));
    exit;
}
echo $OUTPUT->header();
if ($org) {
    $email        = null;
    $filterlist = array('orgemail');
    $filterparams = array('options'=>null, 'dataoptions'=>null);
    $mform = new filters_form($PAGE->url, array('filterlist'=>$filterlist, 'orgid'=>$org_id,'filterparams' => $filterparams, 'action' => 'user_enrolment'));
    if ($mform->is_cancelled()) {
        redirect($PAGE->url);
    } else {
        $filterdata =  $mform->get_data();
        if($filterdata){
            $collapse = false;
        } else{
            $collapse = true;
        }
         $search_query = !empty($filterdata->search_query) ? implode(',', $filterdata->search_query) : null;
        $email = !empty($filterdata->email) ? implode(',', $filterdata->email) : null;
    }

    $options = array('context' => $context->id, 'orgid' => $org_id, 'email' => $email, 'search_query' => $search_query);
    if ($add) {
        $type = 'org_enrol';
        if($submit_value == "Add_All_Users"){
          $options =json_decode($_REQUEST["options"],false);
              $userstoassign=array_flip((new local_organization\organization)->org_enrolled_users('add', $org_id, (array)$options, false, $offset1=-1, $perpage=-1));
        }else{
            $userstoassign = $add;
        }
        if (!empty($userstoassign)) {
            $progress = 0;
            $progressbar = new \core\progress\display_if_slow(get_string('enrollusers', 'local_organization',$org->fullname));
            $progressbar->start_html();
            $progressbar->start_progress('',count($userstoassign)-1);
            // print_r($userstoassign);
            foreach($userstoassign as $key=>$adduser){
              $progressbar->progress($progress);
              $progress++;
              $timestart = $org->startdate;
              $timeend = 0;
              if($timestart==''){
                $timestart=0;
              }
              
              $row = array();
              $row['id'] = $adduser;
              $row['role'] = 'trainee';
              $row['organization'] = $org_id;
              $row['timemodified'] = time();
              $record->id = $DB->update_record('local_users', $row);
              $notificationlib = new \local_organization\notification();
              $sql = "SELECT lnt.* FROM {local_notification_type} AS lnt
              WHERE shortname IN ('organization_assigning_trainee') ";
  
              $notificationtype = $DB->get_record_sql($sql);
              $sqlorg = "SELECT * FROM {local_organization} 
              WHERE id =".$org_id;
              $orgdata = $DB->get_record_sql($sqlorg);
              $row['fullname'] = $orgdata->fullname;
              $row['fullnamearabic'] = $orgdata->fullnameinarabic;
            //   $sqluser = "SELECT u.* FROM {user} AS u
            //   INNER JOIN {local_users} as lc on lc.userid = u.id  
            //   WHERE lc.id = $adduser";
            //   $user = $DB->get_record_sql($sql);

 


           
            $sql = "SELECT lu.* FROM {local_users} AS lu
            JOIN {role_assignments}  as ra ON ra.userid = lu.userid 
            JOIN {role} as r ON r.id = ra.roleid
            WHERE r.shortname = 'organizationofficial' AND lu.organization = $org_id";

            $orgofficials = $DB->get_records_sql($sql);
       



            
                       



            // foreach($localusers as $localuser){
            //     $user= $DB->get_record('user',array('id'=>$localuser->userid));
            //     $notificationlib->organization_notification('organization_assigning_trainee', $touser=$user,$fromuser=$USER, $notificationtype,$waitinglistid=0,$row);              
            // }
            $traineeuser = $DB->get_record('local_users',array('id'=>$adduser));
           

             foreach($orgofficials as $orgofficial){
              

                $orgdata = new stdClass();
                if($orgofficial->lang = 'ar'){
                    $orgdata->user_fullname = $orgofficial->firstnamearabic. ' '. $orgofficial->lastnamearabic. ' '. $orgofficial->middlenamearabic . ' '.$orgofficial->thirdnamearabic;
                    $orgdata->organization_name = $orgdata->fullnameinarabic;
                    $orgdata->organization_trainee_name = $traineeuser->firstnamearabic. ' '.$traineeuser->lastnamearabic;

                } else{
                    $orgdata->user_fullname = $orgofficial->firstname. ' '. $orgofficial->lastname. ' '. $orgofficial->middlename. ' '.$orgofficial->thirdname; 
                    $orgdata->organization_name = $orgdata->fullname;
                    $orgdata->organization_trainee_name = $traineeuser->firstname. ' '.$traineeuser->lastname;
                }

            $orguser = $DB->get_record('user',array('id'=>$orgofficial->userid));
       
                
                $notificationlib->organization_notification('organization_assigning_trainee', $touser=$orguser,$fromuser=$USER, $notificationtype,$waitinglistid=0,  $orgdata);              
            }
            
            //   $user = $DB->get_record('local_users', array('id'=>$adduser, 'deleted'=>0));
            //   print_r($user);exit;
            //   $row['organization_trainee_name'] = $user->firstname;
        
            }
            $progressbar->end_html();
            $result=new stdClass();
            $result->changecount=$progress;
            $result->course=$org->fullname; 

            echo $OUTPUT->notification(get_string('enrolluserssuccess', 'local_organization',$result),'success');
            $button = new single_button($PAGE->url, get_string('click_continue','local_organization'), 'get', true);
            $button->class = 'continuebutton';
            echo $OUTPUT->render($button);
            die();
        }
    }
    if ($remove) {
        $type = 'org_unenroll';
        if($submit_value=="Remove_All_Users"){
          $options =json_decode($_REQUEST["options"],false);
             $userstounassign = array_flip((new local_organization\organization)->org_enrolled_users('remove',$org_id,(array)$options,false,$offset1=-1,$perpage=-1));
        }else{
            $userstounassign = $remove;
        }
        if (!empty($userstounassign)) {
            $progress = 0;
            $progressbar = new \core\progress\display_if_slow(get_string('un_enrollusers', 'local_organization',$org->fullname));
            $progressbar->start_html();
            $progressbar->start_progress('', count($userstounassign)-1);
            foreach($userstounassign as $key=>$removeuser){
                $progressbar->progress($progress);
                $progress++;
                $row['id'] = $DB->get_field('local_users', 'id', array('id' => $removeuser));
                $row['role'] = 'auth_user';
                $row['organization'] = 0;
                $row['timemodified'] = time();
                $record->id = $DB->update_record('local_users', $row);
            }
            $progressbar->end_html();
            $result=new stdClass();
            $result->changecount=$progress;
            $result->course=$org->fullname; 
            
            echo $OUTPUT->notification(get_string('unenrolluserssuccess', 'local_organization',$result),'success');
            $button = new single_button($PAGE->url, get_string('click_continue','local_organization'), 'get', true);
            $button->class = 'continuebutton';
            echo $OUTPUT->render($button);
            die();
        }
    }
   
    $select_to_users = (new local_organization\organization)->org_enrolled_users('add', $org_id, $options, false, $offset=-1, $perpage=50);
    $select_to_userstotal = (new local_organization\organization)->org_enrolled_users('add', $org_id, $options, true, $offset1=-1, $perpage=-1);
    $select_from_users = (new local_organization\organization)->org_enrolled_users('remove', $org_id, $options, false, $offset1=-1, $perpage=50);
    $select_from_userstotal = (new local_organization\organization)->org_enrolled_users('remove', $org_id, $options, true, $offset1=-1, $perpage=-1);
    foreach ($select_from_users as $key => $value) {
      $data['id'] = $key;
      $data['value'] = $value;
      $fromusers[] = $data;
    }
    foreach ($select_to_users as $key => $value) {
      $data['id'] = $key;
      $data['value'] = $value;
      $tousers[] = $data;
    }
}
/*echo "<div class='bulkupload text-right'><a href='" . $CFG->wwwroot . "/local/organization/bulkupload.php?id=$org_id' class='btn btn-primary'>Bulk Upload</a></div>";*/
print_collapsible_region_start(' ', 'filters_form', ' '.' '.get_string('filters'), false, $collapse);
$mform->display();
print_collapsible_region_end();
if ($org) {
    $myJSON = json_encode($options);
    $renderer = $PAGE->get_renderer('local_organization');
    $renderer->orgenrol($select_from_userstotal, $fromusers, $select_to_userstotal, $tousers, $myJSON, $org_id );
}
$backurl = new moodle_url('/local/organization/index.php');
// echo $OUTPUT->single_button($backurl,get_string('continue'));
echo $OUTPUT->footer();
