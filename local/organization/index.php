<?php
require_once('../../config.php');
global $CFG, $PAGE, $OUTPUT, $DB;
require_login();
$systemcontext = context_system::instance();
$PAGE->set_context($systemcontext);
require_capability('local/organization:visible',$systemcontext);
$PAGE->set_url('/local/organization/index.php');
$PAGE->set_title(get_string('organization', 'local_organization'));
$PAGE->set_heading(get_string('listoforganization', 'local_organization'));
$PAGE->navbar->add(get_string('listoforganization', 'local_organization'), new moodle_url('/local/organization/index.php'));
// $renderer = $PAGE->get_renderer('local_organization');
//echo $renderer->action_btn();
echo $OUTPUT->header();
(new local_organization\organization)->organizationsinfo();
echo $OUTPUT->footer();