<?php
require_once('../../config.php');
global $DB, $CFG, $OUTPUT,$USER, $PAGE;
$systemcontext = context_system::instance();
$PAGE->set_context($systemcontext);

$PAGE->set_url('/local/learningtracks/learningpath.php');

if(!isloggedin()) {
  $PAGE->set_pagelayout('sitefrontpage');


    $PAGE->set_title(get_string('pluginname', 'local_learningtracks'));
    $PAGE->set_heading(get_string('pluginname', 'local_learningtracks'));

    $PAGE->navbar->add(get_string('home', 'theme_academy'),new moodle_url('/?redirect=0'));
    $PAGE->navbar->add(get_string('pluginname','local_learningtracks'));

}else{

    $PAGE->set_title(get_string('mylearningpath', 'local_learningtracks'));
    $PAGE->set_heading(get_string('mylearningpath', 'local_learningtracks'));

}

$renderer = $PAGE->get_renderer('local_learningtracks');

echo $OUTPUT->header();
if(isloggedin()) {
    $content = $renderer->get_enrolled_learningpath();
    echo $content;
} else {
    (new local_learningtracks\learningtracks)->get_learningpath_list();
}
echo $OUTPUT->footer();
