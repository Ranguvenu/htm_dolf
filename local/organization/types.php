<?php
require_once('../../config.php');
global $CFG, $PAGE, $OUTPUT, $DB;
require_login();
$systemcontext = context_system::instance();
$PAGE->set_context($systemcontext);

$PAGE->set_url('/local/organization/types.php');
require_capability('local/organization:visible',$systemcontext);
$PAGE->set_title(get_string('types', 'local_organization'));
$PAGE->set_heading(get_string('listoftype', 'local_organization'));
$PAGE->navbar->add(get_string('listoftype', 'local_organization'), new moodle_url('/local/organization/types.php'));
$renderer = $PAGE->get_renderer('local_organization');
//echo $renderer->action_btn();
echo $OUTPUT->header();
(new local_organization\partnertypes)->partnertypesinfo();
echo $OUTPUT->footer();
