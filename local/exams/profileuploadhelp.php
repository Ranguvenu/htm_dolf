<?php
require_once(dirname(__FILE__) . '/../../config.php');
global $CFG, $DB;
$systemcontext = get_context_instance(CONTEXT_SYSTEM);
$PAGE->set_context($systemcontext);
require_capability('local/exams:profileuploadaccess',$systemcontext);
$PAGE->set_url('/local/exams/profileuploadhelp.php');
$strheading = get_string('pluginname', 'local_exams');
$PAGE->set_title($strheading);
if(!is_siteadmin() AND !has_capability('local/organization:manage_trainingofficial', $systemcontext)){
    echo print_error('no permission');
}
if ($CFG->forcelogin) {
    require_login();
} else {
    user_accesstime_log();
}
//$PAGE->set_heading($SITE->fullname);
$PAGE->navbar->add(get_string('pluginname', 'local_exams'), new moodle_url('/local/exams/index.php'));
$PAGE->navbar->add(get_string('profileupload', 'local_exams'), new moodle_url('/local/exams/profileupload.php'));
$PAGE->navbar->add(get_string('profileuploadhelp', 'local_exams'), new moodle_url('/local/exams/profileuploadsample.php'));
echo $OUTPUT->header();
echo html_writer::tag('a',get_string('back_upload','local_exams'),array('href'=>$CFG->wwwroot. '/local/exams/profileupload.php','class'=>"btn btn-secondary ml-2 float-right"));
echo get_string('profile_upload_help', 'local_exams');
echo $OUTPUT->footer();
?>
