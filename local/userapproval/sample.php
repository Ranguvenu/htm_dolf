<?php
/**
 * script for downloading admissions
 */
require_once(dirname(__FILE__) . '/../../config.php');
require_once($CFG->libdir . '/adminlib.php');
global $CFG, $PAGE, $OUTPUT, $DB, $USER;
$format = optional_param('format', '', PARAM_ALPHA);
$context = context_system::instance();
if(!is_siteadmin() && has_capability('local/organization:manage_organizationofficial', $context)) {
    $fields = array(
        'odlid' => 'OldID',
        'firstname' => 'FirstName',
        'lastname' => 'LastName',
        'firstnamearabic' => 'FirstNameArabic',
        'lastnamearabic' => 'LastNameArabic',
        'middlenameenglish' => 'MiddleNameEnglish',
        'thirdnameenglish' => 'ThirdNameEnglish',
        'middlenamearabic' => 'MiddleNameArabic',
        'thirdnamearabic' => 'ThirdNameArabic',
        'dateofbirth' => 'DateOfBirth',
        'username' => 'UserName',
        'password' => 'Password',
        'email' => 'Email',
        'gender' => 'Gender',
        'language' => 'Language',
        'nationality' => 'Nationality',
        'countrycode' => 'CountryCode',
        'mobileno' => 'Mobileno',
        'idtype' => 'IDtype',
        'idnumber' => 'IDnumber',
        'sector' => 'Sector',
        'segment' => 'Segment',
        'jobfamily' => 'Jobfamily',
        'jobrole' => 'Jobrole',
        'city' => 'City',
    );
} else {
    $fields = array(
        'odlid' => 'OldID',
        'firstname' => 'FirstName',
        'lastname' => 'LastName',
        'firstnamearabic' => 'FirstNameArabic',
        'lastnamearabic' => 'LastNameArabic',
        'middlenameenglish' => 'MiddleNameEnglish',
        'thirdnameenglish' => 'ThirdNameEnglish',
        'middlenamearabic' => 'MiddleNameArabic',
        'thirdnamearabic' => 'ThirdNameArabic',
        'dateofbirth' => 'DateOfBirth',
        'username' => 'UserName',
        'password' => 'Password',
        'email' => 'Email',
        'gender' => 'Gender',
        'language' => 'Language',
        'nationality' => 'Nationality',
        'countrycode' => 'CountryCode',
        'mobileno' => 'Mobileno',
        'organization' => 'Organization',
        'idtype' => 'IDtype',
        'idnumber' => 'IDnumber',
        'sector' => 'Sector',
        'segment' => 'Segment',
        'jobfamily' => 'Jobfamily',
        'jobrole' => 'Jobrole',
        'city' => 'City',
        'role' => 'Role',
    );
}
    
require_once($CFG->libdir . '/csvlib.class.php');
$filename = clean_filename('Usersupload');
$csvexport = new csv_export_writer();
$csvexport->set_filename($filename);
$csvexport->add_data($fields);
if(!is_siteadmin() && has_capability('local/organization:manage_organizationofficial', $context)) {
    $record1 = array("abc-25458-cde-kjasdhf",'Fakhar','Zaman',"فخار",'زمان','Mohammed','Shaikh','محمد','شيخ','1994-03-18','fakhar','Welcome#3','fakhar@maillinator.com','Male','ar','SA',966,523698745,'residentialid','1146987456','B','B01','HCA1','JAV123','Riyadh');
} else {
   $record1 = array("abc-25458-cde-kjasdhf",'Fakhar','Zaman',"فخار",'زمان','Mohammed','Shaikh','محمد','شيخ','1994-03-18','fakhar','Welcome#3','fakhar@maillinator.com','Male','ar','SA',966,523698745,'GSNI','residentialid','1146987456','B','B01','HCA1','JAV123','Riyadh','trainee');
}    

$csvexport->add_data($record1);
$csvexport->download_file();
die;
