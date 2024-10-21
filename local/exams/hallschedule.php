<?php
require_once('../../config.php');
global $CFG, $PAGE, $OUTPUT, $DB;
$systemcontext = context_system::instance();
$PAGE->set_context($systemcontext);
require_login();

$examid = optional_param('examid', 0, PARAM_INT);
$profileid= optional_param('profileid', 0, PARAM_INT);
$type = optional_param('type', '' , PARAM_RAW);
$tuserid = optional_param('tuserid', NULL , PARAM_RAW);
$exam = $DB->get_record('local_exams', ['id' => $examid]);
$productid = (new local_exams\local\exams)->attemptvariation($examid, $profileid, $type, $tuserid);
$PAGE->set_url('/local/exams/hallschedule.php', ['examid'=> $examid, 'profileid' => $profileid]);
$PAGE->set_title(get_string('hallschedules', 'local_exams'));
$PAGE->requires->js_call_amd('local_exams/exams', 'init');
$PAGE->requires->js_call_amd('local_exams/exams', 'load', ['variation' => $productid]);
$lang = current_language();
if ($lang == 'ar') {
	$PAGE->set_heading($exam->examnamearabic);
} else {
	$PAGE->set_heading($exam->exam);
}
$PAGE->navbar->add(get_string('exams', 'local_exams'), new moodle_url('/local/exams/index.php'));
$PAGE->navbar->add(get_string('hallschedules', 'local_exams'), new moodle_url('/local/exams/hallschedules.php'));

echo $OUTPUT->header();
(new local_exams\local\exams)->hallschedules();
echo $OUTPUT->footer();
