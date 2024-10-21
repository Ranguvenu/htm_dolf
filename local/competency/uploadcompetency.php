<?php
require_once('../../config.php');
global $CFG, $PAGE, $OUTPUT, $USER, $DB,$_SESSION;
require_login();
$context = context_system::instance();

$PAGE->set_context($context);
$PAGE->set_pagelayout('user');
$PAGE->set_url(new moodle_url('/local/competency/uploadcompetency.php'));

$title = get_string('uploadafile', 'local_competency');
$PAGE->set_title($title);

if((!has_capability('local/competency:canbulkuploadcompetency', $context))){
    throw new required_capability_exception($context, 'local/competency:canbulkuploadcompetency', 'nopermissions', '');
}
@set_time_limit(60 * 60); // 1 hour should be enough.
raise_memory_limit(MEMORY_HUGE);
echo $OUTPUT->header();


echo html_writer::tag('a',get_string('help','local_sector'),array('href'=>$CFG->wwwroot. '/local/competency/upload_competency_help.php','class'=>"btn btn-secondary ml-2 float-right"));

echo html_writer::tag('a',get_string('sample','local_sector'),array('href'=>$CFG->wwwroot. '/local/competency/upload_competency_sample.php','class'=>"btn btn-secondary float-right"));

echo html_writer::tag('a',get_string('back_upload','local_sector'),array('href'=>$CFG->wwwroot. '/local/competency/index.php','class'=>"btn btn-secondary mr-2 float-right"));

$uploadcompetency = new local_competency\form\uploadcompetency_form();
if($uploadcompetency->is_cancelled()){
    redirect($CFG->wwwroot . '/local/competency/uploadcompetency.php');
}
if($data = $uploadcompetency->get_data()){
    raise_memory_limit(MEMORY_EXTRA);
    @set_time_limit(HOURSECS);    
    $competencyupload = new local_competency\local\competency_upload();
    $file = $competencyupload->get_competency_file($data->competencyfile);
    echo $competencyupload->upload_competency_file($file, $context);

}else{
    $uploadcompetency->display();
}
echo $OUTPUT->footer();
