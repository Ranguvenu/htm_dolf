<?php
require_once('../../config.php');
global $CFG, $PAGE, $OUTPUT, $DB;

$systemcontext = context_system::instance();
$PAGE->set_context($systemcontext);
$PAGE->set_url('/local/hall/reservation.php');
$PAGE->set_title(get_string('reservation', 'local_hall'));
$PAGE->set_heading(get_string('reservation', 'local_hall'));
require_login();
//require_capability('local/organization:manage_communication_officer', $systemcontext);
require_capability('local/organization:manage_hall_manager', $systemcontext);
$PAGE->navbar->add(get_string('hall', 'local_hall'), new moodle_url('/local/hall/index.php'));
$PAGE->navbar->add(get_string('reservation', 'local_hall'), new moodle_url('/local/hall/reservation.php'));

$PAGE->requires->js_call_amd('local_hall/reservations', 'init');


$renderer = $PAGE->get_renderer('local_hall');
$filterview = false;
$renderer->action_btn($filterview);

echo $OUTPUT->header();
(new local_hall\hall)->reservationinfo($data);
echo $OUTPUT->footer();
