<?php
require_once('../../config.php');
global $CFG, $PAGE, $OUTPUT, $DB;
$systemcontext = context_system::instance();
require_login();
$PAGE->set_context($systemcontext);
$PAGE->set_url('/local/userapproval/fast_userenrol.php');
$PAGE->set_title(get_string('fast_userenrol', 'local_userapproval'));
$PAGE->set_heading(get_string('fast_userenrol', 'local_userapproval'));
$PAGE->navbar->add(get_string('fast_userenrol', 'local_userapproval'), new moodle_url('/local/userapproval/fast_userenrol.php'));
if (!is_siteadmin() && !has_capability('local/organization:manage_communication_officer',$systemcontext))
{
	print_error(get_string('nopermissions','local_userapproval'));
	redirect($CFG->wwwroot);
}
echo $OUTPUT->header();
	(new local_userapproval\action\manageuser)->fast_userenrolservices();
echo $OUTPUT->footer();
