<?php
require_once('../../config.php');
global $CFG, $PAGE, $OUTPUT, $DB;
$systemcontext = context_system::instance();
$PAGE->set_context($systemcontext);
$PAGE->set_url('/local/hall/index.php');
$PAGE->set_title(get_string('hall', 'local_hall'));
$PAGE->set_heading(get_string('hall', 'local_hall'));
require_login();
if(!is_siteadmin() && !has_capability('local/hall:managehall',$systemcontext) && !has_capability('local/organization:manage_communication_officer',$systemcontext) && !has_capability('local/organization:manage_hall_manager',$systemcontext) && !has_capability('local/organization:manage_event_manager', $systemcontext) ) {

    redirect($CFG->wwwroot);

}
$PAGE->navbar->add(get_string('hall', 'local_hall'), new moodle_url('/local/hall/index.php'));
$renderer = $PAGE->get_renderer('local_hall');
//$renderer->action_btn();

echo $OUTPUT->header();
(new local_hall\hall)->halls();
echo $OUTPUT->footer();
