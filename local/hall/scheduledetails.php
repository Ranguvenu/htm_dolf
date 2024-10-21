<?php
require_once('../../config.php');
global $CFG, $PAGE, $OUTPUT, $DB;

$hallid = optional_param('hallid', 0, PARAM_INT);
$halldate = optional_param('date', 0, PARAM_RAW);
$examid = optional_param('examid', 0, PARAM_INT);
$systemcontext = context_system::instance();
$PAGE->set_context($systemcontext);
$PAGE->set_url('/local/hall/scheduledetails.php?hallid='.$hallid.'&date='.$halldate.'&examid='.$examid);
$PAGE->set_title(get_string('reservation', 'local_hall'));
require_login();
//require_capability('local/organization:manage_communication_officer', $systemcontext);
if(!is_siteadmin() && !has_capability('local/organization:manage_communication_officer',$systemcontext) && !has_capability('local/organization:manage_hall_manager',$systemcontext)) {

    redirect($CFG->wwwroot);

}
$hallname = $DB->get_field('hall', 'name', ['id' => $hallid]);
$PAGE->set_heading(get_string('singlehall', 'local_hall').' : '.$hallname);
$PAGE->navbar->add(get_string('hall', 'local_hall'), new moodle_url('/local/hall/index.php'));
$PAGE->navbar->add(get_string('schedule', 'local_hall'), new moodle_url('/local/hall/schedule.php'));
$PAGE->navbar->add($hallname, new moodle_url('/local/hall/scheduledetails.php?id=$hallid'));

$renderer = $PAGE->get_renderer('local_hall');
$renderer->action_btn();

echo $OUTPUT->header();
(new local_hall\hall)->hallreservations($hallid, $halldate, $examid);
echo $OUTPUT->footer();
