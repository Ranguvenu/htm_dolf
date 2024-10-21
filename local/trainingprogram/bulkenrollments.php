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
 * TODO describe file bulkenrollments
 *
 * @package    local_trainingprogram
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
require_once('../../config.php');
require_once($CFG->libdir . '/adminlib.php');
require_once($CFG->libdir . '/csvlib.class.php');
global $CFG, $PAGE, $OUTPUT, $USER, $DB,$_SESSION;
require_login();
$context = context_system::instance();
$programid     = required_param('programid', PARAM_INT);
$offeringid     = required_param('offeringid', PARAM_INT);
$roleid     = required_param('roleid', PARAM_INT);
$cusers     = optional_param('cusers',null,PARAM_RAW);
$offering=$DB->get_record('tp_offerings',array('id'=>$offeringid));
$offeringenddate = ($offering->enddate+$offering->endtime);
$timestamp = strtotime(date('Y-m-d H:i'));
require_capability('local/trainingprogram:bulkuploadprogramenrollaccess',$context);

if($offering->cancelled == 2) {
    throw new moodle_exception(get_string('offeringhascancelled','local_trainingprogram'));
}
if(!empty($cusers)) {
   $traineeids = base64_decode($cusers);
   $ausers= explode(',',$traineeids);
   foreach ($ausers as $traineeid) {
      $traineeid = (int) $traineeid;
      $DB->delete_records('program_enrollments', ['programid' => $programid,
                                                    'offeringid' => $offeringid, 
                                                      'userid' => $traineeid, 
                                                      'enrolstatus' => 0,
                                                      'enrolltype' => 1]);
   }
   
}
if((!is_siteadmin() AND !has_capability('local/organization:manage_trainingofficial', $context) AND !has_capability('local/organization:manage_communication_officer', $context) AND !has_capability('local/organization:manage_organizationofficial', $context)) || (!is_siteadmin() && has_capability('local/organization:manage_organizationofficial', $context) &&  $offering->trainingmethod !='elearning' && $offeringenddate < $timestamp)){
    throw new required_capability_exception($context, 'local/trainingprogram:bulkuploadprogramenrollaccess', 'nopermissions', '');
}
@set_time_limit(60 * 60); // 1 hour should be enough.
raise_memory_limit(MEMORY_HUGE);
$returnurl =  new moodle_url('/local/trainingprogram/bulkenrollments.php', ['programid'=> $programid,'roleid'=> $roleid,'offeringid' =>$offeringid]);
$PAGE->set_context($context);
$PAGE->set_pagelayout('user');
$PAGE->set_url($returnurl);
$title = get_string('bulkenrollment', 'local_exams');
$PAGE->set_title($title);
$PAGE->navbar->add(get_string('pluginname','local_trainingprogram'),new moodle_url('/local/trainingprogram/index.php'));
$PAGE->navbar->add(get_string('enrollment','local_trainingprogram'),new moodle_url('/local/trainingprogram/programenrollment.php', ['programid'=> $programid,'roleid'=> $roleid,'offeringid' =>$offeringid]));
$PAGE->navbar->add(get_string('bulkenrollment', 'local_exams'));
echo $OUTPUT->header();
echo html_writer::tag('a',get_string('help','local_trainingprogram'),array('href'=>$CFG->wwwroot. '/local/trainingprogram/help.php?programid='.$programid.'&offeringid='.$offeringid.'','class'=>"btn btn-secondary ml-2 float-right"));
echo html_writer::tag('a',get_string('sample','local_trainingprogram'),array('href'=>$CFG->wwwroot. '/local/trainingprogram/bulkupload_program_enroll.php','class'=>"btn btn-secondary float-right"));
$organization =optional_param('organization', 0,PARAM_INT);
$orgofficial =optional_param('orgofficial', 0,PARAM_INT);
$sdata= array(
    'programid'=>$programid,
    'offeringid'=>$offeringid,
    'roleid'=>$roleid,
    'organization'=>$organization,
    'orgofficial'=>$orgofficial,
);
$uploadenrolments = new local_trainingprogram\form\bulkenrollment_form(null,$sdata);
if($uploadenrolments->is_cancelled()){
    redirect(new moodle_url('/local/trainingprogram/programenrollment.php', ['programid'=> $programid,'roleid'=> $roleid, 'offeringid' => $offeringid]));
}
if($data = $uploadenrolments->get_data()){
    raise_memory_limit(MEMORY_EXTRA);
    @set_time_limit(HOURSECS);    
    $usersupload = new local_trainingprogram\local\programenrollusers_upload();
    $file = $usersupload->get_enrollment_file($data->enrollmentfile);
    echo $usersupload->upload_enrollment_file($file, $data);

}else{
    $uploadenrolments->display();
}
echo $OUTPUT->footer();
