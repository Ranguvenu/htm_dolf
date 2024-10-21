<?php
/**
 * script for downloading admissions
 */
require_once(dirname(__FILE__) . '/../../config.php');
require_once($CFG->libdir . '/adminlib.php');
global $CFG, $PAGE, $OUTPUT, $DB, $USER;
$examid = required_param('examid', PARAM_INT);
$profileid = required_param('profileid', PARAM_INT);
$format = optional_param('format', '', PARAM_ALPHA);
$systemcontext = context_system::instance();
require_capability('local/exams:bulkenrollment', $systemcontext);
$PAGE->set_context($systemcontext);
$seturl = new moodle_url('/local/exams/bulkenrollment_sample', ['examid'=> $examid, 'profileid' => $profileid]);
$PAGE->set_url($seturl);
// $fields = array(
//     'identitynumber' => 'IdentityNumber',
//     'identitytype' => 'Identitytype',
//     'firstnameen' => 'FirstnameEn',
//     'middlenameen' => 'MiddlenameEn',
//     'thirdnameen' => 'ThirdnameEn',
//     'lastnameen' => 'LastnameEn',
//     'firstnamear' => 'FirstnameAr',
//     'middlenamear' => 'MiddlenameAr',
//     'thirdnamear' => 'ThirdnameAr',
//     'lastnamear' => 'LastnameAr',
//     'birthdate' => 'Birthdate',
//     'email' => 'Email',
//     'nationality' => 'Nationality',
//     'gender' => 'Gender',
//     'countrycode' => 'CountryCode',
//     'phonenumber' => 'PhoneNumber',
   
// );
// require_once($CFG->libdir . '/csvlib.class.php');
// $filename = clean_filename('exambulkenrollment');
// $csvexport = new csv_export_writer();
// $csvexport->set_filename($filename);
// $csvexport->add_data($fields);
// $record = array('1123657896','saudiid','Ibrahim','Khaleel','Mohammed','Khan','ابراهيم','خليل','محمد','محمد','1994-03-18','ibrahim_mohhammed@gmail.com','SA','Male','966','508123987');
// $csvexport->add_data($record);
// $csvexport->download_file();
// die;
$filepath = "bulkenroll_sample_file.csv";

// Process download
if (file_exists($filepath)) {
  header('Content-Description: File Transfer');
  header('Content-Type: application/octet-stream');
  header('Content-Disposition: attachment; filename="' . basename($filepath) . '"');
  header('Expires: 0');
  header('Cache-Control: must-revalidate');
  header('Pragma: public');
  header('Content-Length: ' . filesize($filepath));
  flush(); // Flush system output buffer
  readfile($filepath);
  die();
} else {
  http_response_code(404);
  die();
}
