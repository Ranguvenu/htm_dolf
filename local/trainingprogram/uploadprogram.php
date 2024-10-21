<?php
require_once('../../config.php');
require_once($CFG->libdir . '/adminlib.php');
require_once($CFG->libdir . '/csvlib.class.php');
global $CFG, $PAGE, $OUTPUT, $USER, $DB,$_SESSION;
require_login();
$context = context_system::instance();
require_capability('local/trainingprogram:bulkuploadaccess',$context);
if(!is_siteadmin() AND !has_capability('local/organization:manage_trainingofficial', $context)){
    throw new required_capability_exception($context, 'local/trainingprogram:bulkuploadaccess', 'nopermissions', '');
}

@set_time_limit(60 * 60); // 1 hour should be enough.
raise_memory_limit(MEMORY_HUGE);
$returnurl = new moodle_url('/local/trainingprogram/index.php');
$PAGE->set_context($context);
$PAGE->set_pagelayout('user');
$PAGE->set_url($returnurl);
$title = get_string('uploadprogram', 'local_trainingprogram');
$PAGE->set_title($title);
$PAGE->navbar->add(get_string('pluginname','local_trainingprogram'),new moodle_url('/local/trainingprogram/index.php'));
$PAGE->navbar->add(get_string('uploadprogram', 'local_trainingprogram'));
echo $OUTPUT->header();

echo html_writer::tag('a',get_string('help','local_trainingprogram'),array('href'=>$CFG->wwwroot. '/local/trainingprogram/upload_program_help.php','class'=>"btn btn-secondary ml-2 float-right"));

echo html_writer::tag('a',get_string('sample','local_trainingprogram'),array('href'=>$CFG->wwwroot. '/local/trainingprogram/upload_program_sample.php','class'=>"btn btn-secondary float-right"));

// echo html_writer::tag('a',get_string('back_to_program','local_trainingprogram'),array('href'=>$CFG->wwwroot. '/local/trainingprogram/index.php','class'=>"btn btn-secondary mr-2 float-right"));

$uploadprogram = new local_trainingprogram\form\uploadprogram_form();
if($uploadprogram->is_cancelled()){
    redirect($returnurl);
}
if($data = $uploadprogram->get_data()){
 
    raise_memory_limit(MEMORY_EXTRA);
    @set_time_limit(HOURSECS);    
    $programupload = new local_trainingprogram\local\program_upload();
    $file = $programupload->get_program_file($data->programfile);
    echo $programupload->upload_program_file($file, $context);

}else{
    $uploadprogram->display();
}
echo $OUTPUT->footer();
