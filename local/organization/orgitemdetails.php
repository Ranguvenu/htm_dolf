<?php
require_once('../../config.php');
global $CFG, $PAGE, $OUTPUT, $DB;
require_login();
$systemcontext = context_system::instance();
$PAGE->set_context($systemcontext);
require_capability('local/organization:visible',$systemcontext);
$orgid      = optional_param('id', 0, PARAM_INT);
$catid      = optional_param('cat', 0, PARAM_INT);
$PAGE->set_url('/local/organization/orgitemdetails.php',array('id' => $orgid,'cat' => $catid));
$orgname    = (new local_organization\organization)->getorgname($orgid);
$organization = get_string('organization_items_detailes', 'local_organization');
$PAGE->set_title($organization);
$PAGE->set_heading($orgname);
$PAGE->navbar->add(get_string('manage_organization','local_organization'),new moodle_url('/local/organization/index.php'));
$PAGE->navbar->add($organization, new moodle_url('/local/organization/orgitemdetails.php?id='.$orgid.'&'.$catid));
echo $OUTPUT->header();
(new local_organization\organization)->orgitems_catalog($orgid);
echo $OUTPUT->footer();
