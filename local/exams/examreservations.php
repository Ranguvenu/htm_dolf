<?php
require_once('../../config.php');
global $CFG, $PAGE, $OUTPUT, $DB;
$examid = required_param('id', PARAM_INT);
$systemcontext = context_system::instance();
$PAGE->set_context($systemcontext);
require_login();
$PAGE->set_url('/local/exams/examdetails.php');
$title = get_string('exam_reservations', 'local_exams');
$PAGE->set_title($title);
$q = optional_param('q', '', PARAM_RAW);

$examsinfo=$DB->get_record('local_exams', ['id' => $examid], 'id,exam,examnamearabic');
$lang= current_language();
        if( $lang == 'ar'){
            $examsinfo->examname=$examsinfo->examnamearabic;
        }else{
            $examsinfo->examname=$examsinfo->exam;           
        } 
$PAGE->set_heading($examsinfo->examname);
$PAGE->navbar->add(get_string('exams', 'local_exams'), new moodle_url('/local/exams/index.php'));
$PAGE->navbar->add(get_string('examsdetails', 'local_exams'), new moodle_url('/local/exams/examdetails.php', ['id' => $examid]));
$PAGE->navbar->add(get_string('examsreservations', 'local_exams'), new moodle_url('/local/exams/examreservations.php?examid=$examid'));
$renderer = $PAGE->get_renderer('local_exams');

echo $OUTPUT->header();

if(is_siteadmin() || has_capability('local/organization:manage_organizationofficial',$systemcontext) || has_capability('local/organization:manage_examofficial',$systemcontext) || has_capability('local/exam:veiw_exam_reservations',$systemcontext) || has_capability('local/exams:veiw_exam_details',$systemcontext)) {
    (new local_exams\local\exams)->get_exam_reservations($examid);
} else {
    throw new required_capability_exception($systemcontext, 'local/exams:veiw_exam_reservations', 'nopermissions', '');
}

echo $OUTPUT->footer();
