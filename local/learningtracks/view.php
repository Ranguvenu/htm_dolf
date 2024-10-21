<?php
require_once('../../config.php');
global $CFG, $PAGE, $OUTPUT, $DB;
require_login();
$systemcontext = context_system::instance();
$PAGE->set_context(context_system::instance());
$renderer = $PAGE->get_renderer('local_learningtracks');
$renderer->manage_capability();
$id = required_param('id', PARAM_INT);
$track = $renderer->track_check($id);
$PAGE->set_url('/local/learningtracks/view.php', array('id' =>$id));
$PAGE->set_title(get_string('pluginname','local_learningtracks'));
$PAGE->set_heading(get_string('viewitems','local_learningtracks'));
$PAGE->navbar->add(get_string('pluginname','local_learningtracks'),new moodle_url('/local/learningtracks/index.php'));
$PAGE->navbar->add(get_string('learning_track_details', 'local_learningtracks'), new moodle_url('/local/learningtracks/view.php?id='.$id));

echo $OUTPUT->header();

$content = $renderer->get_content_viewtrack($id);

echo $content;
echo $OUTPUT->footer();
