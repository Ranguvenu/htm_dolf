<?php
require_once('../../config.php');
global $CFG, $PAGE, $OUTPUT, $DB;
$systemcontext = context_system::instance();
$PAGE->set_context($systemcontext);
require_login();
$PAGE->set_url('/local/exams/index.php');
$PAGE->set_title(get_string('exams', 'local_exams'));
$PAGE->set_heading(get_string('exams', 'local_exams'));
$PAGE->navbar->add(get_string('exams', 'local_exams'), new moodle_url('/local/exams/index.php'));
$PAGE->requires->js_call_amd('local_exams/exams', 'init');
$PAGE->requires->js_call_amd('local_exams/fav', 'init');

$renderer = $PAGE->get_renderer('local_exams');
// $renderer->action_btn();
echo $OUTPUT->header();

if (!is_siteadmin() && has_capability('local/organization:manage_trainee',$systemcontext) || !is_siteadmin() && has_capability('local/organization:manage_organizationofficial',$systemcontext)) {
	$renderable = new \local_exams\output\exams();
	echo $test = $renderer->render($renderable);
} else {
	(new local_exams\local\exams)->exams();
}

if(has_capability('local/organization:manage_examofficial', $systemcontext) || is_siteadmin() ||  has_capability('local/exams:view', $systemcontext)) {
	//echo $renderer->get_catalog_reviewexams(null,$test);
	(new local_exams\local\exams)->reviewmodeexams();
}
$renderer->examstabs();
echo $OUTPUT->footer();
