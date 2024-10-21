<?php
require_once(dirname(__FILE__) . '/../../config.php');
global $CFG, $DB;
$examid = required_param('examid', PARAM_INT);
$profileid = required_param('profileid', PARAM_INT);
$systemcontext = context_system::instance();
require_capability('local/exams:bulkenrollment', $systemcontext);
$PAGE->set_context($systemcontext);
$seturl = new moodle_url('/local/exams/bulkenrollment_help', ['examid'=> $examid, 'profileid' => $profileid]);
$PAGE->set_url($seturl);

//$PAGE->set_pagelayout('admin');
$PAGE->set_title($strheading);
if ($CFG->forcelogin) {
    require_login();
} else {
    user_accesstime_log();
}
$PAGE->set_heading($strheading);
$PAGE->navbar->add(get_string('pluginname', 'local_exams'), new moodle_url('/local/exams/index.php'));
$PAGE->navbar->add(get_string('bulkenrollment', 'local_exams'), new moodle_url('/local/exams/bulk_enrollment.php', ['examid'=> $examid, 'profileid' => $profileid]));
$PAGE->navbar->add(get_string('bulkenrollment_help', 'local_exams'));
// $PAGE->navbar->add(get_string('manual', 'local_users'));
echo $OUTPUT->header();
echo get_string('bulkenrollment_help_1', 'local_exams');
echo $OUTPUT->footer();
?>
