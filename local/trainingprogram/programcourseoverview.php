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

/**
 * TODO describe file programcourseoverview
 *
 * @package    local_trainingprogram
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
require_once(dirname(__FILE__) . '/../../config.php');
global $DB, $CFG, $OUTPUT,$USER, $PAGE;
$systemcontext = context_system::instance();
$isuncompletedprofile = $DB->record_exists_sql("SELECT * FROM {local_users} WHERE userid=:userid AND usersource=:usersource AND email = '' AND phone1 = ''",['userid'=>$USER->id,'usersource'=>'IAM']);
if($isuncompletedprofile){
    redirect($CFG->wwwroot.'/local/userapproval/iamregistration.php');
}
$PAGE->set_context($systemcontext);
$programid= optional_param('programid',0,PARAM_INT);
$login= optional_param('login',0,PARAM_INT);
$current_sys_lang = current_language();
if($current_sys_lang == 'ar') {
  $sql = "SELECT namearabic FROM {local_trainingprogram} WHERE id = $programid";
} else {
  $sql = "SELECT name FROM {local_trainingprogram} WHERE id = $programid";
}
$programname=$DB->get_field_sql($sql);
$courseid=$DB->get_field('local_trainingprogram','courseid',array('id'=>$programid));
$PAGE->set_url('/local/trainingprogram/programcourseoverview.php', array('programid' =>$programid));
$traineeroleid= $DB->get_field('role', 'id', array('shortname' => 'trainee'));
$traineroleid= $DB->get_field('role', 'id', array('shortname' => 'trainer'));
$returnurl = new moodle_url('/local/trainingprogram/programcourseoverview.php');
// (!user_has_role_assignment($USER->id,$traineeroleid,$sitecontext->id) && !user_has_role_assignment($USER->id,$traineroleid,$sitecontext->id)) ?$PAGE->set_heading($programname) :'';
$is_published = $DB->get_field('local_trainingprogram','published',array('id'=>$programid));
if(!isloggedin() && $is_published == 0 || (isloggedin() && !is_siteadmin() && !has_capability('local/organization:manage_trainingofficial', $systemcontext) && !has_capability('local/organization:training_supervisor', $systemcontext) && $is_published == 0)) {
	redirect($CFG->wwwroot);
}
if(empty(isloggedin())) {
  $PAGE->set_pagelayout('sitefrontpage');
  $PAGE->set_title(get_string('programoverview','local_trainingprogram'));
  $PAGE->navbar->add(get_string('home', 'theme_academy'),new moodle_url('/?redirect=0'));
  $PAGE->navbar->add(get_string('all_programs',  'local_trainingprogram'), new moodle_url('https://fa.gov.sa/ar/services/Pages/Programs.aspx'));
  $PAGE->navbar->add(get_string('program_details',  'local_trainingprogram'), new moodle_url('/local/trainingprogram/programcourseoverview.php', array('programid' =>$programid)));
} 

if(isloggedin()){
  $PAGE->set_url($returnurl);
  $PAGE->set_context($systemcontext);
  $PAGE->set_title($programname);
  $PAGE->navbar->add(get_string('pluginname','local_trainingprogram'),new moodle_url('/local/trainingprogram/index.php'));
  $PAGE->navbar->add($programname, new moodle_url('/local/trainingprogram/programcourseoverview.php', array('programid' =>$programid)));
}

echo $OUTPUT->header();

if(is_siteadmin() || has_capability('local/organization:manage_trainingofficial', $systemcontext) || has_capability('local/organization:manage_competencies_official', $systemcontext) || empty(isloggedin())  || has_capability('local/organization:manage_communication_officer',$systemcontext) || has_capability('local/organization:manage_financial_manager',$systemcontext) ||has_capability('local/organization:training_supervisor',$systemcontext) || $login == $USER->id) {
  (new local_trainingprogram\local\trainingprogram)->detailed_program_course_overview($programid,$courseid);
}elseif(!is_siteadmin() && (has_capability('local/organization:manage_trainee', $systemcontext) || has_capability('local/organization:manage_trainer', $systemcontext))) {

  if($DB->record_exists('program_enrollments',array ('programid' => $programid,'courseid' => $courseid,'userid' => $USER->id))) {
    (new local_trainingprogram\local\trainingprogram)->trainee_program_course_overview($programid,$courseid, 'web');
  } else {
    $is_published = $DB->get_field('local_trainingprogram','published',array('id'=>$programid));
    if($is_published == 1) {
      (new local_trainingprogram\local\trainingprogram)->detailed_program_course_overview($programid,$courseid);
    } else {
      redirect($CFG->wwwroot);
    }
  }

} else {
  if(isloggedin() && !$DB->record_exists('role_assignments',array('contextid'=>$systemcontext->id,'userid'=>$USER->id))) { 

      (new local_trainingprogram\local\trainingprogram)->detailed_program_course_overview($programid,$courseid);
        
  } else {
    (new local_trainingprogram\local\trainingprogram)->others_program_course_overview($programid,$courseid);
  
  }


}
echo $OUTPUT->footer();
