<?php
require_once(dirname(__FILE__) . '/../../config.php');
global $CFG, $DB;
$programid = required_param('programid', PARAM_INT);
$offeringid = required_param('offeringid', PARAM_INT);
$roleid = required_param('offeringid', PARAM_INT);
$systemcontext = context_system::instance();
require_capability('local/exams:bulkenrollment', $systemcontext);
$PAGE->set_context($systemcontext);
$seturl = new moodle_url('/local/trainingprogram/help', ['programid'=> $programid, 'roleid'=> $roleid, 'offeringid' => $offeringid]);
$PAGE->set_url($seturl);
//$PAGE->set_pagelayout('admin');
$PAGE->set_title($strheading);
if ($CFG->forcelogin) {
    require_login();
} else {
    user_accesstime_log();
}
$PAGE->set_heading($strheading);
$PAGE->navbar->add(get_string('pluginname', 'local_exams'), new moodle_url('/local/trainingprogram/index.php'));
$PAGE->navbar->add(get_string('enrollment','local_trainingprogram'),new moodle_url('/local/trainingprogram/programenrollment.php', ['programid'=> $programid,'roleid'=> $roleid,'offeringid' =>$offeringid]));
$PAGE->navbar->add(get_string('bulkenrollment', 'local_exams'), new moodle_url('/local/trainingprogram/bulkenrollments.php', ['programid'=> $programid,'roleid'=> $roleid,'offeringid' => $offeringid]));
$PAGE->navbar->add(get_string('bulkenrollment_help', 'local_exams'));
// $PAGE->navbar->add(get_string('manual', 'local_users'));
echo $OUTPUT->header();
echo get_string('bulkenrollment_help_1', 'local_exams');
echo $OUTPUT->footer();
?>
