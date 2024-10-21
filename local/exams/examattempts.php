<?php
require_once('../../config.php');
global $CFG, $PAGE, $OUTPUT, $DB;
$examid = required_param('id', PARAM_INT);
$systemcontext = context_system::instance();
$PAGE->set_context($systemcontext);
require_login();
if(!has_capability('local/organization:manage_examofficial', $systemcontext) || !has_capability('local/exams:view_attempts', $systemcontext)) {
	require_capability('local/exams:view_attempts', $systemcontext);
}

$exam = $DB->get_record('local_exams', ['id' => $examid], 'exam, examnamearabic');
$PAGE->set_url('/local/exams/examattempts.php?id='.$examid);
$PAGE->set_title(get_string('examattempts', 'local_exams'));
$PAGE->set_heading(get_string('examname', 'local_exams', $exam));
$PAGE->navbar->add(get_string('exams', 'local_exams'), new moodle_url('/local/exams/index.php'));
$PAGE->navbar->add(get_string('examattempts', 'local_exams'), new moodle_url('/local/exams/examattempts.php?id=$examid'));

echo $OUTPUT->header();
(new local_exams\local\exams)->exam_attempts($examid);
echo $OUTPUT->footer();
