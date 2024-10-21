<?php
require_once(dirname(__FILE__) . '/../../config.php');
global $CFG, $DB;
$systemcontext = context_system::instance();
$PAGE->set_context($systemcontext);
$PAGE->set_url('/local/exams/bulkupload_help.php');
$strheading = get_string('pluginname', 'local_exams');
$PAGE->set_title($strheading);

if ($CFG->forcelogin) {
    require_login();
} else {
    user_accesstime_log();
}
require_capability('local/organization:manage_examofficial', $systemcontext);
$PAGE->navbar->add(get_string('pluginname', 'local_exams'), new moodle_url('/local/exams/index.php'));
$PAGE->navbar->add(get_string('bulkupload', 'local_exams'), new moodle_url('/local/exams/bulkupload.php'));
$PAGE->navbar->add(get_string('uploadexamhelp', 'local_exams'), new moodle_url('local/exams/bulkupload.php'));
echo $OUTPUT->header();
echo get_string('exam_upload_help', 'local_exams');
echo $OUTPUT->footer();
