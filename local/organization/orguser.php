<?php
require_once('../../config.php');
global $CFG, $PAGE, $OUTPUT, $DB;
require_login();
$id  = required_param('orgid',PARAM_INT);
$systemcontext = context_system::instance();
require_capability('local/organization:visible',$systemcontext);
$PAGE->set_context($systemcontext);
$PAGE->set_url(new moodle_url('/local/organization/orguser.php?orgid='.$id));
$orgname    = (new local_organization\organization)->getorgname($id);
$organization = get_string('organization_detailes', 'local_organization');
$PAGE->set_title($organization);
$PAGE->set_heading($orgname);
$PAGE->navbar->add(get_string('manage_organization','local_organization'),new moodle_url('/local/organization/index.php'));
$PAGE->navbar->add($organization, new moodle_url('/local/organization/orguser.php?orgid='.$id));
echo $OUTPUT->header();
(new local_organization\organization)->assignusersview($id);
echo $OUTPUT->footer();
