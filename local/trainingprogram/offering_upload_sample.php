<?php
require_once(dirname(__FILE__) . '/../../config.php');
require_once($CFG->libdir . '/adminlib.php');
$format = optional_param('format', '', PARAM_ALPHA);
$context = context_system::instance();
    $fields = array(
        
        'OldId' => 'OldId',
        'OfferingCode' => 'OfferingCode',
        'ProgramCode' => 'ProgramCode',
        'OfferingStartdate' => 'OfferingStartdate',
        'OfferingEnddate' => 'OfferingEnddate',
        'OfferingStartTime' => 'OfferingStartTime',
        'OfferingDuration' => 'OfferingDuration',
        'OfferingType' => 'OfferingType',
        'Seats' => 'Seats',
        'Organization' => 'Organization',
        'SellingPrice' => 'SellingPrice',
        'ActualPrice' => 'ActualPrice',
        'TrainingMethod' => 'TrainingMethod',
        'MeetingType' => 'MeetingType',
    );
require_once($CFG->libdir . '/csvlib.class.php');
$filename = clean_filename(get_string('offeringupload','local_trainingprogram'));
$csvexport = new csv_export_writer();
$csvexport->set_filename($filename);
$csvexport->add_data($fields);
    $tpoffering1 = array("abc-25458-cde-kjasdhf",'HGF00410041','HGF0041','2022-10-01',"2022-10-25",'10:30','100','2','35' ,'SMPTE123','3800','2000','online','1');
    $csvexport->add_data($tpoffering1);
    $tpoffering2 = array("dfghdfg-25458-cde-kjasdhf",'AE-HGF-00410042','AE-HGF-0041','2022-09-24',"2022-10-24",'10','120','1','25' ,'ORG123','1800','1000','eleraning','');
    $csvexport->add_data($tpoffering2);
    $tpoffering3 = array("abc-25458-cde-sdfgsdf",'KLM12340043','KLM1234','2022-10-02',"2022-11-02",'9:30','60','3','50' ,'','900','100','offline','');
    $csvexport->add_data($tpoffering3);
// }
$csvexport->download_file();
die;
