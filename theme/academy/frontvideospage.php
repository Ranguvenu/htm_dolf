<?php
require_once('../../config.php');
global $DB, $CFG, $OUTPUT,$USER, $PAGE;
$getidnumber = $DB->get_record('course_modules',array('idnumber'=>"help"));
$url =  $CFG->wwwroot.'/mod/resource/view.php?id='.$getidnumber->id.'&forceview=1';
$systemcontext = context_system::instance();
$PAGE->set_context($systemcontext);
$PAGE->set_title(get_string('help', 'theme_academy'));
$PAGE->set_url('/theme/frontvideospage.php');
$PAGE->set_heading(get_string('help', 'theme_academy'));
$PAGE->navbar->add(get_string('home', 'theme_academy'),new moodle_url('/?redirect=0'));
$PAGE->navbar->add(get_string('help', 'theme_academy'), new moodle_url('/theme/academy/frontvideospage.php'));
$PAGE->set_pagelayout('frontvideospage');
echo $OUTPUT->header();

echo $OUTPUT->render_from_template('theme_academy/frontvideospage',['url'=>$url]);
echo $OUTPUT->footer();
