<?php
require_once('../../config.php');
global $CFG, $PAGE, $OUTPUT, $DB, $USER;
$isuncompletedprofile = $DB->record_exists_sql("SELECT * FROM {local_users} WHERE userid=:userid AND usersource=:usersource AND email = '' AND phone1 = ''",['userid'=>$USER->id,'usersource'=>'IAM']);
if($isuncompletedprofile){
    redirect($CFG->wwwroot.'/local/userapproval/iamregistration.php');
}
$id = optional_param('id', 0, PARAM_INT);
$systemcontext = context_system::instance();
$PAGE->set_context($systemcontext);
$PAGE->set_url('/local/exams/exams_qualification_details.php?id='.$id);
$PAGE->set_pagelayout('sitefrontpage');
$PAGE->set_title(get_string('exams', 'local_exams'));
$PAGE->requires->js_call_amd('local_exams/fav', 'init');
$PAGE->requires->js_call_amd('local_exams/exams', 'init');
$result = $DB->get_record('local_exams', array('id' => $id));
$is_published = $DB->get_field('local_exams','status',array('id'=>$id));

if(!isloggedin() && $is_published == 0 || (isloggedin() && !is_siteadmin() && !has_capability('local/organization:manage_examofficial', $systemcontext) && $is_published == 0)) {
    redirect($CFG->wwwroot);
}
if( !$result ) {
    redirect($CFG->wwwroot.'/local/exams/exams_qualification.php');
}
$enrolled = (new local_exams\local\exams)->useraccess($id);
if($enrolled) {
    $userhalls = $DB->get_record_sql('SELECT eu.examdate,eu.hallscheduleid, eu.profileid FROM {local_exam_userhallschedules} eu  WHERE eu.examid =:examid AND eu.userid=:userid
    ORDER BY eu.id DESC limit 1',['examid'=>$id,'userid'=>$USER->id]);
    if($userhalls->examdate == 0 && $userhalls->hallscheduleid == 0) {
        redirect($CFG->wwwroot.'/local/exams/hallschedule.php?examid='.$id.'&profileid='.$userhalls->profileid.'&tuserid='.$USER->id.'&status=en');
    } else {
        redirect($CFG->wwwroot.'/local/exams/examdetails.php?id='.$id.'&profileid='.$userhalls->profileid);
    }
    //redirect($CFG->wwwroot.'/local/exams/examdetails.php?id='.$id);
}
$examname = (new local_exams\local\exams)->examname($id);
$PAGE->set_heading($examname);
$PAGE->navbar->add(get_string('home', 'theme_academy'),new moodle_url('/?redirect=0'));
$PAGE->navbar->add(get_string('allexams', 'local_exams'), new moodle_url('https://fa.gov.sa/ar/services/Pages/Exams.aspx'));
$PAGE->navbar->add(get_string('navexamname', 'local_exams', $examname), new moodle_url('/local/exams/exams_qualification_details.php'));
echo $OUTPUT->header();
(new local_exams\local\exams)->exam_qualification_details($id);
echo $OUTPUT->footer();
