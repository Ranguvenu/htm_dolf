<?php
require_once('../../config.php');
global $CFG, $PAGE, $OUTPUT, $DB;

$hallid = optional_param('id', 0, PARAM_INT);
$systemcontext = context_system::instance();
$PAGE->set_context($systemcontext);
$PAGE->set_url('/local/hall/hallinfo.php?id='.$hallid);
$PAGE->set_title(get_string('hall', 'local_hall'));
require_login();
//require_capability('local/organization:manage_communication_officer', $systemcontext);
if(!is_siteadmin() && !has_capability('local/organization:manage_communication_officer',$systemcontext) && !has_capability('local/organization:manage_hall_manager',$systemcontext) && !has_capability('local/organization:manage_event_manager', $systemcontext)) {

    redirect($CFG->wwwroot);

}
$hallname = $DB->get_field('hall', 'name', ['id' => $hallid]);
$PAGE->set_heading($hallname);
$PAGE->navbar->add(get_string('hall', 'local_hall'), new moodle_url('/local/hall/index.php'));
$PAGE->navbar->add($hallname, new moodle_url('/local/hall/hallinfo.php?id=$hallid'));

echo $OUTPUT->header();
(new local_hall\hall)->hallinfo($hallid);
echo $OUTPUT->footer();
