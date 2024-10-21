<?php
/**
 * Defines the version of Training program
 * @package    local_trainingprogram
 * @copyright  2022 Naveen kumar <naveen@eabyas.com>
 */
require_once('../../config.php');
global $CFG, $PAGE, $OUTPUT, $DB;
$PAGE->set_url('/local/trainingprogram/trainingtopics.php');
$systemcontext = context_system::instance();
require_capability('local/trainingprogram:manage',$systemcontext);
$PAGE->set_context($systemcontext);
require_login();
$PAGE->set_title(get_string('trainingtopics','local_trainingprogram'));
$PAGE->set_heading(get_string('trainingtopics','local_trainingprogram'));
$PAGE->navbar->add(get_string('pluginname','local_trainingprogram'),new moodle_url('/local/trainingprogram/index.php'));
$PAGE->navbar->add(get_string('trainingtopics','local_trainingprogram'), new moodle_url('/local/trainingprogram/trainingtopics.php'));
$renderer = $PAGE->get_renderer('local_trainingprogram');
echo $OUTPUT->header();
    (new local_trainingprogram\local\trainingprogram)->trainingtopicsview();
echo $OUTPUT->footer();
