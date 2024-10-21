<?php
require_once('../../config.php');
global $DB, $CFG, $OUTPUT,$USER, $PAGE;
$searchquery = optional_param('q', '', PARAM_RAW);
$sector = optional_param('sector', '', PARAM_INT);
$PAGE->set_url('/local/trainingprogram/trainingprogram.php');
$systemcontext = context_system::instance();
$PAGE->set_context($systemcontext);
$PAGE->set_title(get_string('all_programs', 'local_trainingprogram'));
$returnurl = new moodle_url('/local/trainingprogram/trainingprogram.php');
$PAGE->set_url('/local/trainingprogram/trainingprogram.php');
$PAGE->set_heading(get_string('all_programs', 'local_trainingprogram'));
$PAGE->navbar->add(get_string('home', 'theme_academy'),new moodle_url('/?redirect=0'));
$PAGE->navbar->add(get_string('all_programs', 'local_trainingprogram'), new moodle_url('/local/trainingprogram/trainingprogram.php'));
$PAGE->set_pagelayout('sitefrontpage');
echo $OUTPUT->header();
(new local_trainingprogram\local\trainingprogram)->get_card_view_trainingprograms_list($searchquery, $sector);
echo $OUTPUT->footer();

