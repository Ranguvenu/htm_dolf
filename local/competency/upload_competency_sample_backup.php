<?php
require_once(dirname(__FILE__) . '/../../config.php');
require_once($CFG->libdir . '/adminlib.php');
$format = optional_param('format', '', PARAM_ALPHA);
$context = context_system::instance();

if((has_capability('local/competency:canbulkuploadcompetency', $context))){

    $competencyfields = array(
        'OLD_ID' => 'OLD_ID',
        'EN_Name' => 'EN_Name',
        'AR_Name' => 'AR_Name',
        'Description' => 'Description',
        'TYPE' => 'TYPE',
        'CODE' => 'CODE',
        'PARENT_CODE' => 'PARENT_CODE',
        'EXAM_CODE'=>'EXAM_CODE',
        'PROGRAM_CODE'=>'PROGRAM_CODE',
        'QUESTION_CODE'=>'QUESTION_CODE',
        'LEVEL' => 'LEVEL',
    );

    require_once($CFG->libdir . '/csvlib.class.php');
    $filename = clean_filename(get_string('competencyupload','local_competency'));
    $csvexport = new csv_export_writer();
    $csvexport->set_filename($filename);
    $csvexport->add_data($competencyfields);

    $competencyfieldsdataone=array();

    $competencyfieldsdatatwo=array();

    $competencyfieldsdatathree=array();

    $competencyfieldsdatafour=array();

    $competencyfieldsdatafive=array();

    $competencyfieldsdatasix=array();

    $competencyfieldsdataseven=array();

    $competencyfieldsdataeight=array();

    $competencyfieldsdatanine=array();

    $competencyfieldsdataten=array();

    $competencyfieldsdataeleven=array();

    $competencyfieldsdatatwelve=array();

    $competencyfieldsdatathirteen=array();

    $competencyfieldsdatafourteen=array();

    $competencyfieldsdatafifteen=array();

    $competencyfieldsdatasixteen=array();

    $competencyfieldsdataseventeen=array();

    $competencyfieldsdataeighteen=array();

    $competencyfieldsdatanineteen=array();

    $competencyfieldsdatatwenty=array();

    $competencyfieldsdatatwentyone=array();



    foreach($competencyfields as $key=>$competencyfield){

        switch ($key) {
            case 'OLD_ID':

                $competencyfieldsdataone[$key]=1;

                $competencyfieldsdatatwo[$key]=2;

                $competencyfieldsdatathree[$key]=3;

                $competencyfieldsdatafour[$key]=4;

                $competencyfieldsdatafive[$key]=5;

                $competencyfieldsdatasix[$key]=6;

                $competencyfieldsdataseven[$key]=7;

                $competencyfieldsdataeight[$key]=8;

                $competencyfieldsdatanine[$key]=9;

                $competencyfieldsdataten[$key]=10;

                $competencyfieldsdataeleven[$key]=11;

                $competencyfieldsdatatwelve[$key]=12;

                $competencyfieldsdatathirteen[$key]=13;

                $competencyfieldsdatafourteen[$key]=14;

                $competencyfieldsdatafifteen[$key]=15;

                $competencyfieldsdatasixteen[$key]=16;

                $competencyfieldsdataseventeen[$key]=17;

                $competencyfieldsdataeighteen[$key]=18;

                $competencyfieldsdatanineteen[$key]=1;

                $competencyfieldsdatatwenty[$key]=1;

                $competencyfieldsdatatwentyone[$key]=1;

                break;

            case 'EN_Name':

                $competencyfieldsdataone[$key]='Technical Competencies';

                $competencyfieldsdatatwo[$key]='Core Competencies';

                $competencyfieldsdatathree[$key]='Behavioral Competencies';

                $competencyfieldsdatafour[$key]='Level 1';

                $competencyfieldsdatafive[$key]='Level 2';

                $competencyfieldsdatasix[$key]='Level 3';

                $competencyfieldsdataseven[$key]='Level 4';

                $competencyfieldsdataeight[$key]='Level 5';

                $competencyfieldsdatanine[$key]='Performance criteria 1';

                $competencyfieldsdataten[$key]='Performance criteria 2';

                $competencyfieldsdataeleven[$key]='Performance criteria 3';

                $competencyfieldsdatatwelve[$key]='Performance criteria 4';

                $competencyfieldsdatathirteen[$key]='Performance criteria 5';

                $competencyfieldsdatafourteen[$key]='Performance objective 1';

                $competencyfieldsdatafifteen[$key]='Performance objective 2';

                $competencyfieldsdatasixteen[$key]='Performance objective 3';

                $competencyfieldsdataseventeen[$key]='Performance objective 4';

                $competencyfieldsdataeighteen[$key]='Performance objective 5';

                $competencyfieldsdatanineteen[$key]='Data Management';

                $competencyfieldsdatatwenty[$key]='Performance criteria 1';

                $competencyfieldsdatatwentyone[$key]='KPI 1';


                break;

            case 'AR_Name':

                $competencyfieldsdataone[$key]='';

                $competencyfieldsdatatwo[$key]='';

                $competencyfieldsdatathree[$key]='';

                $competencyfieldsdatafour[$key]='';

                $competencyfieldsdatafive[$key]='';

                $competencyfieldsdatasix[$key]='';

                $competencyfieldsdataseven[$key]='';

                $competencyfieldsdataeight[$key]='';

                $competencyfieldsdatanine[$key]='';

                $competencyfieldsdataten[$key]='';

                $competencyfieldsdataeleven[$key]='';

                $competencyfieldsdatatwelve[$key]='';

                $competencyfieldsdatathirteen[$key]='';

                $competencyfieldsdatafourteen[$key]='';

                $competencyfieldsdatafifteen[$key]='';

                $competencyfieldsdatasixteen[$key]='';

                $competencyfieldsdataseventeen[$key]='';

                $competencyfieldsdataeighteen[$key]='';

                $competencyfieldsdatanineteen[$key]='إدارة البيانات';

                $competencyfieldsdatatwenty[$key]='';

                $competencyfieldsdatatwentyone[$key]='';


                break;

            case 'Description':

                $competencyfieldsdataone[$key]='';

                $competencyfieldsdatatwo[$key]='';

                $competencyfieldsdatathree[$key]='';

                $competencyfieldsdatafour[$key]='';

                $competencyfieldsdatafive[$key]='';

                $competencyfieldsdatasix[$key]='';

                $competencyfieldsdataseven[$key]='';

                $competencyfieldsdataeight[$key]='';

                $competencyfieldsdatanine[$key]='';

                $competencyfieldsdataten[$key]='';

                $competencyfieldsdataeleven[$key]='';

                $competencyfieldsdatatwelve[$key]='';

                $competencyfieldsdatathirteen[$key]='';

                $competencyfieldsdatafourteen[$key]='';

                $competencyfieldsdatafifteen[$key]='';

                $competencyfieldsdatasixteen[$key]='';

                $competencyfieldsdataseventeen[$key]='';

                $competencyfieldsdataeighteen[$key]='';

                $competencyfieldsdatanineteen[$key]='{mlang en}English Desc{mlang}{mlang ar}AR Description{mlang}';

                $competencyfieldsdatatwenty[$key]='';

                $competencyfieldsdatatwentyone[$key]='';

                break;

            case 'TYPE':

                $competencyfieldsdataone[$key]='CompetencyType';

                $competencyfieldsdatatwo[$key]='CompetencyType';

                $competencyfieldsdatathree[$key]='CompetencyType';

                $competencyfieldsdatafour[$key]='CompetencyLevel';

                $competencyfieldsdatafive[$key]='CompetencyLevel';

                $competencyfieldsdatasix[$key]='CompetencyLevel';

                $competencyfieldsdataseven[$key]='CompetencyLevel';

                $competencyfieldsdataeight[$key]='CompetencyLevel';

                $competencyfieldsdatanine[$key]='PerformanceCriteria';

                $competencyfieldsdataten[$key]='PerformanceCriteria';

                $competencyfieldsdataeleven[$key]='PerformanceCriteria';

                $competencyfieldsdatatwelve[$key]='PerformanceCriteria';

                $competencyfieldsdatathirteen[$key]='PerformanceCriteria';

                $competencyfieldsdatafourteen[$key]='PerformanceObjective';

                $competencyfieldsdatafifteen[$key]='PerformanceObjective';

                $competencyfieldsdatasixteen[$key]='PerformanceObjective';

                $competencyfieldsdataseventeen[$key]='PerformanceObjective';

                $competencyfieldsdataeighteen[$key]='PerformanceObjective';

                $competencyfieldsdatanineteen[$key]='Competency';

                $competencyfieldsdatatwenty[$key]='PerformanceCriteria';

                $competencyfieldsdatatwentyone[$key]='PerformanceObjective';

                break;

            case 'CODE':

                $competencyfieldsdataone[$key]='T';

                $competencyfieldsdatatwo[$key]='C';

                $competencyfieldsdatathree[$key]='B';

                $competencyfieldsdatafour[$key]='L1';

                $competencyfieldsdatafive[$key]='L2';

                $competencyfieldsdatasix[$key]='L3';

                $competencyfieldsdataseven[$key]='L4';

                $competencyfieldsdataeight[$key]='L5';

                $competencyfieldsdatanine[$key]='PC01';

                $competencyfieldsdataten[$key]='PC02';

                $competencyfieldsdataeleven[$key]='PC03';

                $competencyfieldsdatatwelve[$key]='PC04';

                $competencyfieldsdatathirteen[$key]='PC05';

                $competencyfieldsdatafourteen[$key]='PO01';

                $competencyfieldsdatafifteen[$key]='PO02';

                $competencyfieldsdatasixteen[$key]='PO03';

                $competencyfieldsdataseventeen[$key]='PO04';

                $competencyfieldsdataeighteen[$key]='PO05';

                $competencyfieldsdatanineteen[$key]='T25';

                $competencyfieldsdatatwenty[$key]='PC01';

                $competencyfieldsdatatwentyone[$key]='PO01';

                break;
                
            case 'PARENT_CODE':

                $competencyfieldsdataone[$key]='NULL';

                $competencyfieldsdatatwo[$key]='NULL';

                $competencyfieldsdatathree[$key]='NULL';

                $competencyfieldsdatafour[$key]='NULL';

                $competencyfieldsdatafive[$key]='NULL';

                $competencyfieldsdatasix[$key]='NULL';

                $competencyfieldsdataseven[$key]='NULL';

                $competencyfieldsdataeight[$key]='NULL';

                $competencyfieldsdatanine[$key]='NULL';

                $competencyfieldsdataten[$key]='NULL';

                $competencyfieldsdataeleven[$key]='NULL';

                $competencyfieldsdatatwelve[$key]='NULL';

                $competencyfieldsdatathirteen[$key]='NULL';

                $competencyfieldsdatafourteen[$key]='NULL';

                $competencyfieldsdatafifteen[$key]='NULL';

                $competencyfieldsdatasixteen[$key]='NULL';

                $competencyfieldsdataseventeen[$key]='NULL';

                $competencyfieldsdataeighteen[$key]='NULL';

                $competencyfieldsdatanineteen[$key]='T';

                $competencyfieldsdatatwenty[$key]='T25';

                $competencyfieldsdatatwentyone[$key]='PC01';

                break;

            case 'EXAM_CODE':

                $competencyfieldsdataone[$key]='NULL';

                $competencyfieldsdatatwo[$key]='NULL';

                $competencyfieldsdatathree[$key]='NULL';

                $competencyfieldsdatafour[$key]='NULL';

                $competencyfieldsdatafive[$key]='NULL';

                $competencyfieldsdatasix[$key]='NULL';

                $competencyfieldsdataseven[$key]='NULL';

                $competencyfieldsdataeight[$key]='NULL';

                $competencyfieldsdatanine[$key]='NULL';

                $competencyfieldsdataten[$key]='NULL';

                $competencyfieldsdataeleven[$key]='NULL';

                $competencyfieldsdatatwelve[$key]='NULL';

                $competencyfieldsdatathirteen[$key]='NULL';

                $competencyfieldsdatafourteen[$key]='NULL';

                $competencyfieldsdatafifteen[$key]='NULL';

                $competencyfieldsdatasixteen[$key]='NULL';

                $competencyfieldsdataseventeen[$key]='NULL';

                $competencyfieldsdataeighteen[$key]='NULL';


                $competencyfieldsdatanineteen[$key]='NULL';

                $competencyfieldsdatatwenty[$key]='';

                $competencyfieldsdatatwentyone[$key]='EX56,EX665';

                break;

            case 'PROGRAM_CODE':

                $competencyfieldsdataone[$key]='NULL';

                $competencyfieldsdatatwo[$key]='NULL';

                $competencyfieldsdatathree[$key]='NULL';

                $competencyfieldsdatafour[$key]='NULL';

                $competencyfieldsdatafive[$key]='NULL';

                $competencyfieldsdatasix[$key]='NULL';

                $competencyfieldsdataseven[$key]='NULL';

                $competencyfieldsdataeight[$key]='NULL';

                $competencyfieldsdatanine[$key]='NULL';

                $competencyfieldsdataten[$key]='NULL';

                $competencyfieldsdataeleven[$key]='NULL';

                $competencyfieldsdatatwelve[$key]='NULL';

                $competencyfieldsdatathirteen[$key]='NULL';

                $competencyfieldsdatafourteen[$key]='NULL';

                $competencyfieldsdatafifteen[$key]='NULL';

                $competencyfieldsdatasixteen[$key]='NULL';

                $competencyfieldsdataseventeen[$key]='NULL';

                $competencyfieldsdataeighteen[$key]='NULL';

                $competencyfieldsdatanineteen[$key]='NULL';

                $competencyfieldsdatatwenty[$key]='';

                $competencyfieldsdatatwentyone[$key]='TP30222,TP30,TP500';

                break;
                
            case 'QUESTION_CODE':

                $competencyfieldsdataone[$key]='NULL';

                $competencyfieldsdatatwo[$key]='NULL';

                $competencyfieldsdatathree[$key]='NULL';

                $competencyfieldsdatafour[$key]='NULL';

                $competencyfieldsdatafive[$key]='NULL';

                $competencyfieldsdatasix[$key]='NULL';

                $competencyfieldsdataseven[$key]='NULL';

                $competencyfieldsdataeight[$key]='NULL';

                $competencyfieldsdatanine[$key]='NULL';

                $competencyfieldsdataten[$key]='NULL';

                $competencyfieldsdataeleven[$key]='NULL';

                $competencyfieldsdatatwelve[$key]='NULL';

                $competencyfieldsdatathirteen[$key]='NULL';

                $competencyfieldsdatafourteen[$key]='NULL';

                $competencyfieldsdatafifteen[$key]='NULL';

                $competencyfieldsdatasixteen[$key]='NULL';

                $competencyfieldsdataseventeen[$key]='NULL';

                $competencyfieldsdataeighteen[$key]='NULL';

                $competencyfieldsdatanineteen[$key]='NULL';

                $competencyfieldsdatatwenty[$key]='';

                $competencyfieldsdatatwentyone[$key]='QU30222,QUP30,QU500';

                break;

            case 'LEVEL':

                $competencyfieldsdataone[$key]='NULL';

                $competencyfieldsdatatwo[$key]='NULL';

                $competencyfieldsdatathree[$key]='NULL';

                $competencyfieldsdatafour[$key]='NULL';

                $competencyfieldsdatafive[$key]='NULL';

                $competencyfieldsdatasix[$key]='NULL';

                $competencyfieldsdataseven[$key]='NULL';

                $competencyfieldsdataeight[$key]='NULL';

                $competencyfieldsdatanine[$key]='NULL';

                $competencyfieldsdataten[$key]='NULL';

                $competencyfieldsdataeleven[$key]='NULL';

                $competencyfieldsdatatwelve[$key]='NULL';

                $competencyfieldsdatathirteen[$key]='NULL';

                $competencyfieldsdatafourteen[$key]='NULL';

                $competencyfieldsdatafifteen[$key]='NULL';

                $competencyfieldsdatasixteen[$key]='NULL';

                $competencyfieldsdataseventeen[$key]='NULL';

                $competencyfieldsdataeighteen[$key]='NULL';

                $competencyfieldsdatanineteen[$key]='L1,L2,L3';

                $competencyfieldsdatatwenty[$key]='';

                $competencyfieldsdatatwentyone[$key]='';


                break;
            
        }



    }

    $csvexport->add_data($competencyfieldsdataone);

    $csvexport->add_data($competencyfieldsdatatwo);

    $csvexport->add_data($competencyfieldsdatathree);

    $csvexport->add_data($competencyfieldsdatafour);

    $csvexport->add_data($competencyfieldsdatafive);

    $csvexport->add_data($competencyfieldsdatasix);

    $csvexport->add_data($competencyfieldsdataseven);

    $csvexport->add_data($competencyfieldsdataeight);

    // $csvexport->add_data($competencyfieldsdatanine);

    // $csvexport->add_data($competencyfieldsdataten);

    // $csvexport->add_data($competencyfieldsdataeleven);

    // $csvexport->add_data($competencyfieldsdatatwelve);

    // $csvexport->add_data($competencyfieldsdatathirteen);

    // $csvexport->add_data($competencyfieldsdatafourteen);

    // $csvexport->add_data($competencyfieldsdatafifteen);

    // $csvexport->add_data($competencyfieldsdatasixteen);

    // $csvexport->add_data($competencyfieldsdataseventeen);

    // $csvexport->add_data($competencyfieldsdataeighteen);

    $csvexport->add_data($competencyfieldsdatanineteen);

    $csvexport->add_data($competencyfieldsdatatwenty);

    $csvexport->add_data($competencyfieldsdatatwentyone);


    $csvexport->download_file();

    die;

}else{

    throw new required_capability_exception($context, 'local/competency:canbulkuploadcompetency', 'nopermissions', '');
}