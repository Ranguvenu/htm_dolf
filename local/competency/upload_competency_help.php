<?php
require_once(dirname(__FILE__) . '/../../config.php');
global $CFG, $DB;
$systemcontext = get_context_instance(CONTEXT_SYSTEM);
$PAGE->set_context($systemcontext);
$PAGE->set_url('/local/competency/upload_competency_help.php');
$strheading = get_string('pluginname', 'local_competency');
$PAGE->set_title($strheading);
if((!has_capability('local/competency:canbulkuploadcompetency', $systemcontext))){
    throw new required_capability_exception($systemcontext, 'local/competency:canbulkuploadcompetency', 'nopermissions', '');
}
if ($CFG->forcelogin) {
    require_login();
} else {
    user_accesstime_log();
}

$PAGE->navbar->add(get_string('pluginname', 'local_competency'), new moodle_url('/local/competency/index.php'));
$PAGE->navbar->add(get_string('upload_competency', 'local_competency'), new moodle_url('local/competency/uploadcompetency.php'));
echo $OUTPUT->header();
echo html_writer::tag('a',get_string('back_upload','local_sector'),array('href'=>$CFG->wwwroot. '/local/competency/uploadcompetency.php','class'=>"btn btn-secondary ml-2 float-right"));
echo get_string('competency_upload_help', 'local_competency');
echo $OUTPUT->footer();
?>
