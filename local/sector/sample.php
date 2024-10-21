<?php
/**
 * script for downloading admissions
 */
require_once(dirname(__FILE__) . '/../../config.php');
require_once($CFG->libdir . '/adminlib.php');
$format = optional_param('format', '', PARAM_ALPHA);
global $DB,$USER;

$context = context_system::instance();
    $fields = array(
        'old_id' => 'OLD_ID',
        'en_name' => 'EN_Name',
        'ar_name' => 'AR_Name',
        'description' => 'Description',
        'type' => 'TYPE',
        'code' => 'CODE',
        'parent_code' => 'PARENT_CODE',
        'shared' => 'SHARED',
        'level' => 'LEVEL',
        'ctype' => 'ctype',
        'competencies' => 'Competencies'
    );
// $sectorlist = $DB->get_records_sql("SELECT jf.id,s.title as sectorname, s.code as sectorcode,sg.title as segmenttitle ,sg.code as segmentcode,jf.familyname as jobfamily,jf.code as jobfamilycode FROM `mdl_local_jobfamily` as jf JOIN mdl_local_segment as sg on sg.id = jf.segmentid JOIN mdl_local_sector as s on s.id = sg.sectorid LIMIT 0,5");

require_once($CFG->libdir . '/csvlib.class.php');
$filename = clean_filename('Sectorupload');
$csvexport = new csv_export_writer();
$csvexport->set_filename($filename);
$csvexport->add_data($fields);
/*if(!empty($sectorlist)){
    foreach($sectorlist as $sectors){
        $sectorinfo = array();
        $sectorinfo[] = $sectors->sectorname;
        $sectorinfo[]  = $sectors->sectorcode;
        $sectorinfo[]  = $sectors->segmenttitle;
        $sectorinfo[] = $sectors->segmentcode;
        $sectorinfo[]  = $sectors->jobfamily;
        $sectorinfo[] = $sectors->jobfamilycode;
        $csvexport->add_data($sectorinfo);
    }
}elseif(empty($sectorlist)){*/
    $sector1 = array(1, 'Banking', 'الخدمات المصرفية', 'Sector for Insurance', 'Sector', 'B', 'B05', 0, 0,0,0);
    $csvexport->add_data($sector1);
    $sector2 = array(2, 'Retail Banking', 'الخدمات المصرفية للأفراد', 'Segment for Insurance', 'Segment', 'B05', 'B', 0, 0,0,0);
    $csvexport->add_data($sector2);
//}
$csvexport->download_file();
die;

