<?php
/**
 * A form for users upload.
 *
 * @package    local_userapproval
 * @
 */

require_once('../../config.php');
ini_set('memory_limit', '-1');
ini_set('max_execution_time', -1);
global $CFG, $PAGE, $OUTPUT, $USER, $DB,$_SESSION;

require_login();
$context = context_system::instance();
$systemcontext = context_system::instance();
require_capability('local/userapproval:view', $systemcontext);
$PAGE->set_context($context);
$PAGE->set_pagelayout('user');
$PAGE->set_url(new moodle_url('/local/userapproval/uploadusers.php'));

@set_time_limit(60 * 360); // 1 hour should be enough.
raise_memory_limit(MEMORY_HUGE);

$title = get_string('uploadusers', 'local_userapproval');
$PAGE->set_title($title);

$PAGE->navbar->add(get_string('users', 'local_userapproval'), new moodle_url('/local/userapproval/index.php') );
$PAGE->navbar->add(get_string('uploadafile', 'local_userapproval'), $PAGE->url);
echo $OUTPUT->header();
$uploadusers = new local_userapproval\form\uploadusers_form();
if($uploadusers->is_cancelled()){
    redirect($CFG->wwwroot . '/local/userapproval/uploadusers.php');
}

if($data = $uploadusers->get_data()){
raise_memory_limit(MEMORY_EXTRA);
    @set_time_limit(HOURSECS);    
    $usersupload = new local_userapproval\local\users_upload();
    $file = $usersupload->get_users_file($data->usersfile);
    echo $usersupload->process_upload_file($file, $context);

}else{
    echo html_writer::tag('a',get_string('back_upload','local_userapproval'),array('href'=>$CFG->wwwroot. '/local/userapproval/index.php','class'=>"btn btn-secondary ml-2 float-right"));
    echo html_writer::tag('a',get_string('sampledata','local_userapproval'),array('href'=>$CFG->wwwroot. '/local/userapproval/rawdata.php','class'=>"btn btn-secondary ml-2 float-right"));
    echo html_writer::tag('a',get_string('help','local_userapproval'),array('href'=>$CFG->wwwroot. '/local/userapproval/help.php','class'=>"btn btn-secondary ml-2 float-right"));
    echo html_writer::tag('a',get_string('sample','local_userapproval'),array('href'=>$CFG->wwwroot. '/local/userapproval/sample.php','class'=>"btn btn-secondary float-right"));
    $uploadusers->display();
}

echo $OUTPUT->footer();
