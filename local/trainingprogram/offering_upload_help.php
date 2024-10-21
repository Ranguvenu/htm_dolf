<?php
require_once(dirname(__FILE__) . '/../../config.php');
global $CFG, $DB;
$systemcontext = get_context_instance(CONTEXT_SYSTEM);
$PAGE->set_context($systemcontext);
require_capability('local/trainingprogram:bulkuploadaccess',$systemcontext);
$PAGE->set_url('/local/trainingprogram/offering_upload_help.php');
$strheading = get_string('pluginname', 'local_trainingprogram');
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
$PAGE->navbar->add(get_string('pluginname', 'local_trainingprogram'), new moodle_url('/local/trainingprogram/index.php'));
$PAGE->navbar->add(get_string('uploadoffering', 'local_trainingprogram'), new moodle_url('/local/trainingprogram/uploadofferings.php'));
$PAGE->navbar->add(get_string('offeringuploadhelp', 'local_trainingprogram'), new moodle_url('/local/trainingprogram/offering_upload_help.php'));
echo $OUTPUT->header();
echo html_writer::tag('a',get_string('back_upload','local_trainingprogram'),array('href'=>$CFG->wwwroot. '/local/trainingprogram/uploadofferings.php','class'=>"btn btn-secondary ml-2 float-right"));
echo get_string('offering_upload_help', 'local_trainingprogram');
echo $OUTPUT->footer();
?>
