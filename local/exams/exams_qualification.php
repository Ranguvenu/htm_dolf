<?php
require_once('../../config.php');
global $CFG, $PAGE, $OUTPUT, $DB;
$searchquery = optional_param('q', '', PARAM_RAW);
$systemcontext = context_system::instance();
$PAGE->set_context($systemcontext);
$PAGE->set_url('/local/exams/exams_qualification.php');
$PAGE->set_title(get_string('exams', 'local_exams'));
$PAGE->set_heading(get_string('allexams', 'local_exams'));

$PAGE->navbar->add(get_string('home', 'theme_academy'),new moodle_url('/?redirect=0'));
$PAGE->navbar->add(get_string('allexams', 'local_exams'), new moodle_url('/local/exams/exams_qualification.php'));
$PAGE->set_pagelayout('sitefrontpage');
echo $OUTPUT->header();
$systemcontext = context_system::instance();
(new local_exams\local\exams)->exam_qualification_info($searchquer);
echo $OUTPUT->footer();
