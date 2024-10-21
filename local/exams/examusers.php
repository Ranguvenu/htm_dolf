<?php
require_once('../../config.php');
require_once($CFG->libdir . '/badgeslib.php');
global $CFG, $PAGE, $OUTPUT, $DB;
$examid = required_param('id', PARAM_INT);
$profileid = optional_param('profileid',0,PARAM_INT);
$systemcontext = context_system::instance();
$PAGE->set_context($systemcontext);
require_login();
$PAGE->set_url('/local/exams/examusers.php?id='.$examid.'&profileid='.$profileid);
$PAGE->set_title(get_string('exams', 'local_exams'));
$PAGE->set_heading(get_string('examusers', 'local_exams'));

if( !is_siteadmin() && !has_capability('local/organization:manage_examofficial',$systemcontext) && !has_capability('local/exams:exam_certificate_downloads',$systemcontext) && !has_capability('local/organization:manage_organizationofficial', $systemcontext) && !has_capability('local/organization:manage_communication_officer',$systemcontext) ){

    throw new required_capability_exception($systemcontext, 'local/exams:exam_certificate_downloads', 'nopermissions', '');

}

$PAGE->navbar->add(get_string('exams', 'local_exams'), new moodle_url('/local/exams/index.php'));
$PAGE->navbar->add(get_string('examsdetails', 'local_exams'), new moodle_url('/local/exams/examdetails.php', ['id' => $examid]));
$PAGE->navbar->add(get_string('examusers', 'local_exams'), new moodle_url('/local/exams/examusers.php?id=$examid'));
$renderer = $PAGE->get_renderer('local_exams');

echo $OUTPUT->header();
(new local_exams\local\exams)->examusers($examid,$profileid);
echo $OUTPUT->footer();
