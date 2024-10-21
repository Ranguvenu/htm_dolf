<?php
require_once('../../config.php');
global $CFG, $PAGE, $OUTPUT, $DB;

$systemcontext = context_system::instance();
$PAGE->set_context($systemcontext);
$PAGE->set_url('/local/hall/index.php');
$PAGE->set_title(get_string('schedule', 'local_hall'));
$PAGE->set_heading(get_string('schedule', 'local_hall'));
require_login();
//require_capability('local/organization:manage_communication_officer', $systemcontext);
require_capability('local/organization:manage_hall_manager', $systemcontext);
$PAGE->navbar->add(get_string('hall', 'local_hall'), new moodle_url('/local/hall/index.php'));
$PAGE->navbar->add(get_string('schedule', 'local_hall'), new moodle_url('/local/hall/schedule.php'));

$renderer = $PAGE->get_renderer('local_hall');
$renderer->action_btn();

echo $OUTPUT->header();
(new local_hall\hall)->schedule();
echo $OUTPUT->footer();
