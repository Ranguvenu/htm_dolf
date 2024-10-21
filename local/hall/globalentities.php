<?php
require_once('../../config.php');
global $CFG, $PAGE, $OUTPUT, $DB;
$systemcontext = context_system::instance();
$PAGE->set_context($systemcontext);
$PAGE->set_url('/local/hall/globalentities.php');
$PAGE->set_title(get_string('globalentities', 'local_hall'));
$PAGE->set_heading(get_string('search', 'local_hall'));
require_login();

$renderer = $PAGE->get_renderer('local_hall');

echo $OUTPUT->header();
(new local_hall\hall)->globalentitysearch();
echo $OUTPUT->footer();