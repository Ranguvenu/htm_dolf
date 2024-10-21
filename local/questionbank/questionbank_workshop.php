<?php
require_once('../../config.php');
global $CFG, $PAGE, $OUTPUT, $DB,$USER;
require_login();
$questionbankid = optional_param('id', 0, PARAM_INT);
require_once($CFG->dirroot.'/local/questionbank/lib.php');
$PAGE->set_url('/local/questionbank/questionbank_workshop.php?id='.$questionbankid);
$systemcontext = context_system::instance();
$questionbankid = optional_param('id', 0, PARAM_INT);
$PAGE->set_context($systemcontext);
$PAGE->set_title(get_string('questionbank_workshop','local_questionbank'));
$PAGE->set_heading(get_string('questionbank_workshop','local_questionbank'));
echo $OUTPUT->header();
$qbadmininfo = $DB->get_field_sql("SELECT id FROM {local_questionbank} WHERE FIND_IN_SET(".$USER->id.", workshopadmin) AND id=".$questionbankid);
if(!(is_siteadmin() || (has_capability('local/questionbank:assignreviewer',$systemcontext) && !empty($qbadmininfo)) || has_capability('local/organization:manage_organizationofficial',$systemcontext) || has_capability('local/questionbank:createquestion',$systemcontext))){
    print_error("You don't have permissions to view this page.");
}
echo $OUTPUT->render_from_template('local_questionbank/createquestionbankbutton', ['questionbankview'=>true]);
$data = (new questionbank )->questionbank_workshop_view($questionbankid);
echo $OUTPUT->footer();