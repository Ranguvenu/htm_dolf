<?php
/**
 * Defines the version of Exams
 * @package    local_exams
 * @copyright  2023 Revanth Kumar Grandhi <revanth.grandhi@moodle.com>
 */
require_once('../../config.php');
require_once($CFG->libdir . '/filelib.php');

use local_exams\form\fastsettingsform as fastsettingsform;

$systemcontext = context_system::instance();

require_capability('local/exams:manage_fastsettings', $systemcontext);
require_login();

$PAGE->set_title(get_string('fastsettings','local_exams'));
$PAGE->set_heading(get_string('fastsettings','local_exams'));
$PAGE->set_url('/local/exams/fastsettings.php');
$PAGE->set_context($systemcontext);

$settingnode =$PAGE->settingsnav->add(get_string('fasettings', 'local_sector'),new moodle_url('/admin/settings.php', ['section' => 'fasettings']));
$settingnode->add(get_string('fastsettings', 'local_exams'), new moodle_url('/local/exams/fastsettings.php'));

$get_data = get_config('local_exams', 'fastsettings');
$get_data = json_decode($get_data);

$set_data= [];
foreach($get_data AS $key=>$value) {
   $key = str_replace(' ','_',$key);
   $set_data[$key] = $value;
}

$fastsettingsform = new fastsettingsform();
$fastsettingsform->set_data($set_data);
if ($fastsettingsform->is_cancelled()) {
    redirect($CFG->wwwroot . '/local/exams/fastsettings.php');
} else if ($data = $fastsettingsform->get_data()) {
    $newdata =[];
    foreach($data AS $key=>$value) {
        $newdata[$key] = $value;
    }
    $newdata= json_encode($newdata);
    set_config('fastsettings', $newdata, 'local_exams');
}
echo $OUTPUT->header();
$data = [
    'fastsettingsform' => $fastsettingsform->render(),
];
echo $OUTPUT->render_from_template('local_exams/fastsettings',$data);
echo $OUTPUT->footer();
