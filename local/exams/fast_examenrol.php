<?php
require_once('../../config.php');
global $CFG, $PAGE, $OUTPUT, $DB;
$systemcontext = context_system::instance();
require_login();
$PAGE->set_context($systemcontext);
$PAGE->set_url('/local/exams/fast_examenrol.php');
$PAGE->set_title(get_string('fast_examenrol', 'local_exams'));
$PAGE->set_heading(get_string('fast_examenrol', 'local_exams'));
$PAGE->navbar->add(get_string('fast_examenrol', 'local_exams'), new moodle_url('/local/exams/fast_examenrol.php'));
if (!is_siteadmin())
{
	print_error(get_string('nopermissions','local_exams'));
	redirect($CFG->wwwroot);
}
echo $OUTPUT->header();
	(new local_exams\local\exams)->fast_examenrolservices();
echo $OUTPUT->footer();
