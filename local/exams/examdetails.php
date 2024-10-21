<?php
require_once('../../config.php');
global $CFG, $PAGE, $OUTPUT, $DB;
$examid = required_param('id', PARAM_INT);
$profileid = optional_param('profileid', 0,PARAM_INT);
$systemcontext = context_system::instance();
$PAGE->set_context($systemcontext);
require_login();
if(!has_capability('local/organization:manage_examofficial', $systemcontext) && !has_capability('local/organization:manage_organizationofficial', $systemcontext) && !has_capability('local/organization:manage_communication_officer',$systemcontext) && !has_capability('local/organization:manage_trainee',$systemcontext) && !has_capability('local/exams:veiw_exam_details',$systemcontext)){
    throw new required_capability_exception($systemcontext, 'local/exams:veiw_exam_details', 'nopermissions', '');
}

$exam = $DB->get_record('local_exams', ['id' => $examid], 'exam, examnamearabic, sellingprice');
$enrolled = (new local_exams\local\exams)->useraccess($examid,$profileid);

if(!is_siteadmin() && has_capability('local/organization:manage_trainee',$systemcontext) && empty($enrolled)) {
  redirect(new moodle_url($CFG->wwwroot.'/local/exams/index.php'));
  $PAGE->set_url('/local/exams/index.php');
} else {
   $PAGE->set_url('/local/exams/examdetails.php?id='.$examid);
}

$PAGE->set_title(get_string('exams', 'local_exams'));
$PAGE->set_heading(get_string('examdetails', 'local_exams', $exam));
$PAGE->navbar->add(get_string('exams', 'local_exams'), new moodle_url('/local/exams/index.php'));
$PAGE->navbar->add(get_string('examsdetails', 'local_exams'), new moodle_url('/local/exams/examdetails.php?id=$examid'));
$PAGE->requires->js_call_amd('local_hall/hall', 'init');
$renderer = $PAGE->get_renderer('local_exams');
//$renderer->action_btn($examid);
$is_published = $DB->get_field('local_exams','status',array('id'=>$examid));

if(!is_siteadmin() && has_capability('local/organization:manage_trainee', $systemcontext) && !$is_published) {

  redirect($CFG->wwwroot);
}
echo $OUTPUT->header();
(new local_exams\local\exams)->examsdetails($examid,$profileid);
echo $OUTPUT->footer();
