<?php
/**
 * Defines the version of Exams
 * @package    local_exams
 * @copyright  2023 Revanth Kumar Grandhi <revanth.grandhi@moodle.com>
 */
require_once('../../config.php');
require_once($CFG->libdir . '/filelib.php');
global $CFG, $PAGE, $OUTPUT, $DB;
$PAGE->set_url('/local/exams/exam_ownedby_settings.php');
use local_exams\form\exam_ownedby_settings_form as ownedbysettingsform;
$systemcontext = context_system::instance();
require_capability('local/exams:manage_exam_ownedby_settings',$systemcontext);
$PAGE->set_context($systemcontext);
require_login();
$PAGE->set_title(get_string('exam_ownedby_settings','local_exams'));
$PAGE->set_heading(get_string('exam_ownedby_settings','local_exams'));
$settingnode =$PAGE->settingsnav->add(get_string('fasettings', 'local_sector'),new moodle_url('/admin/settings.php',['section' => 'fasettings']));
$settingnode->add(get_string('exam_ownedby_settings','local_exams'), new moodle_url('/local/exams/exam_ownedby_settings.php'));
$get_data = get_config('local_exams','ownedby');
$get_data = json_decode($get_data);
$set_data= [];
foreach($get_data AS $key=>$value) {
   $key = str_replace(' ','_',$key);
   $set_data[$key] = $value;
}
$ownedbysettingsform = new ownedbysettingsform();
$ownedbysettingsform->set_data($set_data);
if ($ownedbysettingsform->is_cancelled()) {
    redirect($CFG->wwwroot . '/local/exams/exam_ownedby_settings.php');
 } else if ($data = $ownedbysettingsform->get_data()) {
   $newdata =[];
    foreach($data AS $key=>$value) {
      $key = str_replace('_',' ',$key);
      $newdata[$key] = $value;
    }
    $newdata= json_encode($newdata);
    set_config('ownedby',$newdata,'local_exams');
   }
 echo $OUTPUT->header();
 $data=[
     'ownedbysettingsform'=>$ownedbysettingsform->render(),
  ];
 echo $OUTPUT->render_from_template('local_exams/examsownedbysettings',$data);
 echo $OUTPUT->footer();

