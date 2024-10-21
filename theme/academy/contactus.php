<?php
require_once('../../config.php');
global $DB, $CFG, $OUTPUT,$USER, $PAGE;
$systemcontext = context_system::instance();
$PAGE->set_context($systemcontext);
$PAGE->set_title(get_string('contactus', 'theme_academy'));
$PAGE->set_url('/theme/academy/contactus.php');
$PAGE->set_heading(get_string('contactus', 'theme_academy'));
$PAGE->navbar->add(get_string('home', 'theme_academy'),new moodle_url('/?redirect=0'));
$PAGE->navbar->add(get_string('contactus', 'theme_academy'), new moodle_url('/theme/academy/contactus.php'));
$PAGE->set_pagelayout('sitefrontpage');
echo $OUTPUT->header();
echo $OUTPUT->render_from_template('theme_academy/contactus',[]);
echo $OUTPUT->footer();