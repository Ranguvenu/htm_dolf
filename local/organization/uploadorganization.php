<?php
/**
 * A form for organization upload.
 *
 * @package    local_organization
 * @
 */

require_once('../../config.php');
global $CFG, $PAGE, $OUTPUT, $USER, $DB,$_SESSION;
require_login();
$context = context_system::instance();
require_capability('local/organization:visible',$context);
if(!(is_siteadmin() || has_capability('local/organization:manage_communication_officer',$context))){
    throw new required_capability_exception($context, 'local/kpi:uploadkpi', 'nopermissions', '');
}
@set_time_limit(60 * 60); // 1 hour should be enough.
raise_memory_limit(MEMORY_HUGE);
$PAGE->set_context($context);
$PAGE->set_pagelayout('user');
$PAGE->set_url(new moodle_url('/local/organization/uploadorganization.php'));
$title = get_string('uploadorg', 'local_organization');
$PAGE->set_title($title);
$PAGE->navbar->add(get_string('organization', 'local_organization'), new moodle_url('/local/organization/index.php') );
$PAGE->navbar->add(get_string('uploadorg', 'local_organization'), $PAGE->url);
echo $OUTPUT->header();
echo html_writer::tag('a',get_string('help','local_organization'),array('href'=>$CFG->wwwroot. '/local/organization/help.php','class'=>"btn btn-secondary ml-2 my-2 float-right"));
echo html_writer::tag('a',get_string('sample','local_organization'),array('href'=>$CFG->wwwroot. '/local/organization/sample.php','class'=>"btn btn-secondary  ml-2 my-2 float-right"));
echo html_writer::tag('a',get_string('back_upload','local_organization'),array('href'=>$CFG->wwwroot. '/local/organization/index.php','class'=>"btn btn-secondary ml-2 my-2 float-right"));
$uploadorg = new local_organization\form\uploadorganization_form();
echo $OUTPUT->box_start('boxwidthnarrow boxaligncenter generalbox', 'uploadresults');
echo '<div class="panel panel-primary my-6">';
if($uploadorg->is_cancelled()){
    redirect($CFG->wwwroot . '/local/organization/uploadorganization.php');
}
if($data = $uploadorg->get_data()){
raise_memory_limit(MEMORY_EXTRA);
    @set_time_limit(HOURSECS);    
    $orgupload = new local_organization\organization_upload();
    $file = $orgupload->get_organization_file($data->organizationfile);
    $delimiter_name = $data->delimiter_name;
    $encoding = $data->encoding;
    echo $orgupload->process_upload_file($file, $context);
} else {
    $uploadorg->display();
}
echo '</div>';
echo $OUTPUT->box_end();
echo $OUTPUT->footer();
