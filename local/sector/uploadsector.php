<?php
/**
 * A form for sector upload.
 *
 * @package    local_sector
 * @
 */
require_once('../../config.php');
global $CFG, $PAGE, $OUTPUT, $USER, $DB,$_SESSION;
require_login();
$systemcontext = context_system::instance();
require_capability('local/sector:manage', $systemcontext);
$context = context_system::instance();
@set_time_limit(60 * 60); // 1 hour should be enough.
raise_memory_limit(MEMORY_HUGE);

if(!is_siteadmin() && !has_capability('local/sector:manage',$systemcontext)){
    throw new required_capability_exception($context, 'local/sector:manage', 'nopermissions', '');
}

$PAGE->set_context($context);
$PAGE->set_pagelayout('user');
$PAGE->set_url(new moodle_url('/local/sector/uploadsector.php'));

$title = get_string('uploadafile', 'local_sector');
$PAGE->set_title($title);

$PAGE->navbar->add(get_string('sector', 'local_sector'), new moodle_url('/local/sector/index.php') );
$PAGE->navbar->add(get_string('uploadafile', 'local_sector'), $PAGE->url);
echo $OUTPUT->header();
echo html_writer::tag('a',get_string('back','local_sector'),array('href'=>$CFG->wwwroot. '/local/sector/index.php','class'=>"btn btn-secondary ml-2 float-right"));
echo html_writer::tag('a',get_string('help','local_sector'),array('href'=>$CFG->wwwroot. '/local/sector/help.php','class'=>"btn btn-secondary ml-2 float-right"));
echo html_writer::tag('a',get_string('sample','local_sector'),array('href'=>$CFG->wwwroot. '/local/sector/sample.php','class'=>"btn btn-secondary float-right"));

$uploadsector = new local_sector\form\uploadsector_form();
if($uploadsector->is_cancelled()){
    redirect($CFG->wwwroot . '/local/sector/uploadsector.php');
}

if($data = $uploadsector->get_data()){
raise_memory_limit(MEMORY_EXTRA);
    @set_time_limit(HOURSECS);    
    $sectorupload = new local_sector\local\sector_upload();
    $file = $sectorupload->get_sector_file($data->sectorfile);
    echo $sectorupload->process_upload_file($file, $context);

}else{
    $uploadsector->display();
}

echo $OUTPUT->footer();
