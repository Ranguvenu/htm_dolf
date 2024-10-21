<?php
require_once('config.php');
global $DB, $CFG, $OUTPUT,$USER, $PAGE;
$successmessage= '';
$sql_query = "SELECT * FROM {tp_offerings}";
$allrecords = $DB->get_records_sql($sql_query);
foreach($allrecords AS $offering) {
    $courseid =(int) $DB->get_field('local_trainingprogram','courseid',['id'=>$offering->trainingid]);
    $offeringsection = (int) $offering->sections;
    $sectionid = (int) $DB->get_field('course_sections','id',['course'=>$courseid,'section'=>$offeringsection]);
    $recordexists = $DB->record_exists('tp_offerings',['id'=>$offering->id,'trainingid'=>$offering->trainingid,'sections'=>$sectionid]);
    if(!$recordexists) {
       $updateoffering = new stdClass();
       $updateoffering->id = $offering->id;
       $updateoffering->sections = $sectionid;
       $DB->update_record('tp_offerings',$updateoffering);
       $successmessage.= 'Offering ('.$offering->code.') section  updated from ('.$offeringsection.') to  ('.$sectionid.') for course  ('.$courseid.')  '.'<br>';
    }
}
echo $successmessage;
    
