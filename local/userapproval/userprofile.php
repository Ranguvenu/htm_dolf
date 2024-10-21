<?php
require_once('../../config.php');
require_once($CFG->dirroot.'/user/lib.php');
require_login();
global $CFG, $PAGE,$USER, $OUTPUT, $DB;
$context = context_system::instance();
$userid = optional_param('id', $USER->id, PARAM_INT);

if(!is_siteadmin() && !has_capability('local/organization:manage_cpd', $context) &&  $USER->id !=$userid){
	redirect($CFG->wwwroot);
}
$PAGE->set_context($context);
$PAGE->set_url(new moodle_url('/local/userapproval/userprofile.php?id='.$userid));
$PAGE->set_title(get_string('userapproval', 'local_userapproval'));
if(is_siteadmin() || has_capability('local/organization:manage_organizationofficial',$context)) {
  $PAGE->navbar->add(get_string('manage_users','local_userapproval'),new moodle_url('/local/userapproval/index.php'));
  $PAGE->navbar->add(get_string('user_profile', 'local_userapproval'), new moodle_url('/local/userapproval/userprofile.php?id='.$userid));
}
$PAGE->requires->js_call_amd('local_userapproval/user_custom_form', 'init');
$renderer = $PAGE->get_renderer('local_userapproval');
$renderer = $PAGE->get_renderer('local_userapproval');
echo $OUTPUT->header();
echo $renderer->userprofileview($userid);
echo $OUTPUT->footer();
