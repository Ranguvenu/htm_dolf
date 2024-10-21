<?php
defined('MOODLE_INTERNAL') || die();
function xmldb_local_sector_install(){
	global $CFG, $USER, $DB, $OUTPUT;
	$dbman = $DB->get_manager(); // loads ddl manager and xmldb classes
 	$time = time();  
    $sectorsdata = array(
        array('title' => 'Banking','code' => 'B','timemodified' => $time,'usermodified' => '2','titlearabic'=> 'الخدمات المصرفية'),
        array('title' => 'Capital market','code' => 'V','timemodified' => $time,'usermodified' => '2','titlearabic'=> 'سوق رأس المال'),
        array('title' => 'Finance','code' => 'F','timemodified' => $time,'usermodified' => '2','titlearabic'=> 'تمويل'),
        array('title' => 'Insurance','code' => 'I','timemodified' => $time,'usermodified' => '2','titlearabic'=> 'تأمين'),
    );
    foreach($sectorsdata as $sector){
        if(!$DB->record_exists('local_sector',  $sector)){
            $DB->insert_record('local_sector', $sector);
        }
    }
}
