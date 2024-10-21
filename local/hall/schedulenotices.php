<?php
require_once('../../config.php');
global $CFG, $PAGE, $OUTPUT, $DB;

$type = optional_param('type', 0, PARAM_RAW);
$typeid = optional_param('typeid', 0, PARAM_INT);
$errors = optional_param('errors', 0, PARAM_RAW);
$systemcontext = context_system::instance();
$PAGE->set_context($systemcontext);
$PAGE->set_url('/local/hall/schedulenotices.php?type='.$type.'&typeid='.$typeid.'&errors='.$errors);
$PAGE->set_title(get_string('hall', 'local_hall'));
require_login();
$PAGE->set_heading($hallname);
echo $OUTPUT->header();
(new local_hall\hall)->schedulenotices($errors, $type, $typeid);
echo $OUTPUT->footer();