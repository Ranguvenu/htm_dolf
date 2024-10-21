<?php

require_once('../../config.php');
require_once($CFG->dirroot.'/local/userapproval/lib.php');
global $CFG, $PAGE, $OUTPUT, $DB;
require_login();
$id = optional_param('id', 0, PARAM_INT);
$userid= required_param('userid',PARAM_INT);
$systemcontext = context_system::instance();
$PAGE->set_context($systemcontext);

$trainerroleid = (int) $DB->get_field('role','id',['shortname'=>'trainer']);
$expertroleid = (int) $DB->get_field('role','id',['shortname'=>'expert']);

$PAGE->set_url('/local/userapproval/listof_allrequests.php?id='.$id.'&userid='.$userid.'');
$PAGE->set_title(get_string('requesteddata', 'local_userapproval'));
$PAGE->navbar->add(get_string('manage_users','local_userapproval'),new moodle_url('/local/userapproval/index.php'));
$PAGE->navbar->add(get_string('user_profile', 'local_userapproval'), new moodle_url('/local/userapproval/userprofile.php?id='.$userid));
$PAGE->navbar->add(get_string('requesteddata', 'local_userapproval'), new moodle_url('/local/userapproval/listof_allrequests.php?id='.$id.'&userid='.$userid.''));
echo $OUTPUT->header();

 (new local_userapproval\action\manageuser)->individual_requests($userid, $id);

echo $OUTPUT->footer();

