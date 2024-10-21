<?php
require_once('../../config.php');
require_once($CFG->libdir . '/adminlib.php');
require_once($CFG->libdir . '/csvlib.class.php');
global $CFG, $PAGE, $OUTPUT, $USER, $DB,$_SESSION;
@set_time_limit(60 * 60); // 1 hour should be enough.
raise_memory_limit(MEMORY_HUGE);
$examid = required_param('examid', PARAM_INT);
$profileid = required_param('profileid', PARAM_INT);
$cusers     = optional_param('cusers',null,PARAM_RAW);
$context = context_system::instance();
$backurl = new moodle_url('/local/exams/bulk_enrollment.php', ['examid'=> $examid, 'profileid' => $profileid]);
//echo $backurl;exit;
$PAGE->set_context($context);
$PAGE->set_url($backurl);
require_capability('local/exams:bulkenrollment',$context);
if(!empty($cusers)) {
    $traineeids = base64_decode($cusers);
    $ausers= explode(',',$traineeids);
    foreach ($ausers as $traineeid) {
       $traineeid = (int) $traineeid;
       $DB->delete_records('local_exam_userhallschedules', ['examid' => $examid,
                                                     'profileid' => $profileid, 
                                                       'userid' => $traineeid, 
                                                       'enrolstatus' => 0,
                                                       'enrolltype' => 1]);

        $DB->delete_records('exam_enrollments', ['examid' => $examid,
                                                     'profileid' => $profileid, 
                                                       'userid' => $traineeid, 
                                                       'enrolstatus' => 0,
                                                       'enrolltype' => 1]);
    }
    
}
if(!is_siteadmin() AND !has_capability('local/organization:manage_examofficial', $context) AND !has_capability('local/organization:manage_organizationofficial', $context)){
  throw new required_capability_exception($context, 'local/exams:bulkenrollment', 'nopermissions', '');
}
$title = get_string('bulkenrollment', 'local_exams');
$PAGE->set_title($title);
$PAGE->navbar->add(get_string('pluginname', 'local_exams'), new moodle_url('/local/exams/index.php'));
$PAGE->navbar->add(get_string('examenrolment', 'local_exams'), new moodle_url('/local/exams/examenrollment.php', ['examid'=> $examid, 'profileid' => $profileid]));
$PAGE->navbar->add(get_string('bulkenrollment', 'local_exams'));
echo $OUTPUT->header();
echo html_writer::tag('a',get_string('help','local_exams'),array('href'=>$CFG->wwwroot. '/local/exams/bulkenrollment_help.php?examid='.$examid.'&profileid='.$profileid,'class'=>"btn btn-secondary ml-2 float-right"));
echo html_writer::tag('a',get_string('sample','local_exams'),array('href'=>$CFG->wwwroot. '/local/exams/bulkenrollment_sample.php?examid='.$examid.'&profileid='.$profileid,'class'=>"btn btn-secondary float-right"));

$organization =optional_param('organization', 0,PARAM_INT);
$orgofficial =optional_param('orgofficial', 0,PARAM_INT);
$sdata= array(
    'examid'=>$examid,
    'profileid'=>$profileid,
    'organization'=>$organization,
    'orgofficial'=>$orgofficial,
);
$bulkenrollment = new local_exams\form\bulkenrollment_form(null,$sdata);
if($bulkenrollment->is_cancelled()){
    redirect(new moodle_url('/local/exams/examenrollment.php', ['examid'=> $examid, 'profileid' => $profileid]));
}
if($data = $bulkenrollment->get_data()){  
    $bulkenrollmentupload = new local_exams\local\enrollment_upload();
    $file = $bulkenrollmentupload->get_enrollment_file($data->enrollmentfile);
    echo $bulkenrollmentupload->upload_enrollment_file($file,$data);
}else{
    $bulkenrollment->display();
}
echo $OUTPUT->footer();
