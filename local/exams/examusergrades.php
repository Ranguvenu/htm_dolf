<?php
require_once('../../config.php');
global $CFG, $PAGE, $OUTPUT, $DB;
$examid = optional_param('id', 0, PARAM_INT);
$systemcontext = context_system::instance();
$PAGE->set_context($systemcontext);
require_login();
$PAGE->set_url('/local/exams/index.php');
$PAGE->set_title(get_string('exams', 'local_exams'));
$PAGE->set_heading(get_string('exams', 'local_exams'));

echo $OUTPUT->header();
(new local_exams\local\examgrades)->examusergrades($examid);
echo $OUTPUT->footer();
