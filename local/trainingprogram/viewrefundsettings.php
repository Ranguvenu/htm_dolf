<?php
/**
 * Defines the version of Training program
 * @package    local_trainingprogram
 * @copyright  2022 Naveen kumar <naveen@eabyas.com>
 */
require_once('../../config.php');
global $CFG, $PAGE, $OUTPUT, $DB;
$PAGE->set_url('/local/trainingprogram/viewrefundsettings.php');
$systemcontext = context_system::instance();
require_capability('local/trainingprogram:manage_refundsettings',$systemcontext);
$PAGE->set_context($systemcontext);
require_login();
$PAGE->set_title(get_string('refundsettings','local_trainingprogram'));
$PAGE->set_heading(get_string('refundsettings','local_trainingprogram'));
$settingnode =$PAGE->settingsnav->add(get_string('fasettings', 'local_sector'),new moodle_url('/admin/settings.php',['section' => 'fasettings']));
$settingnode->add(get_string('refundsettings','local_trainingprogram'), new moodle_url('/local/trainingprogram/viewrefundsettings.php'));
$renderer = $PAGE->get_renderer('local_trainingprogram');
echo $OUTPUT->header();
    (new local_trainingprogram\local\refundsettings)->refundsettingsview();
echo $OUTPUT->footer();