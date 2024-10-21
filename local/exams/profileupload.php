<?php
require_once('../../config.php');
require_once($CFG->libdir . '/adminlib.php');
require_once($CFG->libdir . '/csvlib.class.php');
global $CFG, $PAGE, $OUTPUT, $USER, $DB,$_SESSION;
require_login();
$context = context_system::instance();
require_capability('local/exams:profileuploadaccess',$context);
if(!is_siteadmin() AND !has_capability('local/organization:manage_trainingofficial', $context)){
    throw new required_capability_exception($context, 'local/exams:profileuploadaccess', 'nopermissions', '');
}

@set_time_limit(60 * 60); // 1 hour should be enough.
raise_memory_limit(MEMORY_HUGE);
$returnurl = new moodle_url('/local/exams/profileupload.php');
$PAGE->set_context($context);
$PAGE->set_pagelayout('user');
$PAGE->set_url($returnurl);
$title = get_string('profileupload', 'local_exams');
$PAGE->set_title($title);
$PAGE->navbar->add(get_string('pluginname','local_exams'),new moodle_url('/local/exams/index.php'));
$PAGE->navbar->add(get_string('profileupload', 'local_exams'));
echo $OUTPUT->header();

// Need to Modify

echo html_writer::tag('a',get_string('help','local_exams'),array('href'=>$CFG->wwwroot. '/local/exams/profileuploadhelp.php','class'=>"btn btn-secondary ml-2 float-right"));

echo html_writer::tag('a',get_string('sample','local_exams'),array('href'=>$CFG->wwwroot. '/local/exams/profileuploadsample.php','class'=>"btn btn-secondary float-right"));
//Ends Need to Modify



$profileupload = new local_exams\form\profileupload_form();
if($profileupload->is_cancelled()){
    redirect($returnurl);
}
if($data = $profileupload->get_data()){
 
    raise_memory_limit(MEMORY_EXTRA);
    @set_time_limit(HOURSECS);    
    $uploadprofile = new local_exams\local\profile_upload();
    $file = $uploadprofile->get_profile_file($data->profilefile);
    echo $uploadprofile->upload_profile_file($file, $context);

}else{
    $profileupload->display();
}
echo $OUTPUT->footer();
