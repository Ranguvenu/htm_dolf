<?php

require_once('../../config.php');
global $CFG, $PAGE, $OUTPUT, $DB;
$systemcontext = context_system::instance();
$PAGE->set_url('/local/events/allevents.php');
$PAGE->set_context($systemcontext);
$PAGE->set_pagelayout('sitefrontpage');
$PAGE->set_title(get_string('pluginname','local_events'));
$PAGE->set_heading(get_string('manage','local_events'));

$PAGE->navbar->add(get_string('home', 'theme_academy'),new moodle_url('/?redirect=0'));
$PAGE->navbar->add(get_string('pluginname','local_events'));
echo $OUTPUT->header();
/*if(isloggedin()) {
redirect($CFG->wwwroot);
} else {*/
(new local_events\events)->events_info();
//}
echo $OUTPUT->footer();