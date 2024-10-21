<?php
/**
 * Defines the version of Training program
 * @package    local_trainingprogram
 * @copyright  2023 Renu <renu.varma@moodle.com>
 */
require_once('../../config.php');
global $CFG, $PAGE, $OUTPUT, $DB;
$PAGE->set_url('/local/trainingprogram/evalution_method.php');
$systemcontext = context_system::instance();
//require_capability('local/trainingprogram:manage_refundsettings',$systemcontext);
$PAGE->set_context($systemcontext);
require_login();
$PAGE->set_title(get_string('evaluationmethod','local_trainingprogram'));
$PAGE->set_heading(get_string('evaluationmethod','local_trainingprogram'));
$settingnode =$PAGE->settingsnav->add(get_string('fasettings', 'local_sector'),new moodle_url('/admin/settings.php',['section' => 'fasettings']));
$settingnode->add(get_string('evaluationmethod','local_trainingprogram'), new moodle_url('/local/trainingprogram/evalution_method.php'));
$renderer = $PAGE->get_renderer('local_trainingprogram');
echo $OUTPUT->header();
    (new local_trainingprogram\local\evalutionmethod)->evalutionmethodview();
echo $OUTPUT->footer();