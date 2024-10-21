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

                $competencyfieldsdatanine[$key]=1;

                $competencyfieldsdataten[$key]=1;

                $competencyfieldsdataeleven[$key]=1;

                $competencyfieldsdatatwelve[$key]=1;

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

                $competencyfieldsdatanine[$key]='Data Management';

                $competencyfieldsdataten[$key]='Performance criteria 1';

                $competencyfieldsdataeleven[$key]='KPI 1';

                $competencyfieldsdatatwelve[$key]='Objective 1';



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

                $competencyfieldsdatanine[$key]='إدارة البيانات';

                $competencyfieldsdataten[$key]='معايير الأداء 1';

                $competencyfieldsdataeleven[$key]='مؤشر الأداء الرئيسي 1';

                $competencyfieldsdatatwelve[$key]='الهدف 1';


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

                $competencyfieldsdatanine[$key]='{mlang en}English Desc{mlang}{mlang ar}AR Description{mlang}';

                $competencyfieldsdataten[$key]='';

                $competencyfieldsdataeleven[$key]='';

                $competencyfieldsdatatwelve[$key]='';


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

                $competencyfieldsdatanine[$key]='Competency';

                $competencyfieldsdataten[$key]='PerformanceCriteria';

                $competencyfieldsdataeleven[$key]='PerformanceKPI';

                $competencyfieldsdatatwelve[$key]='PerformanceObjective';


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

                $competencyfieldsdatanine[$key]='T25';

                $competencyfieldsdataten[$key]='PC01';

                $competencyfieldsdataeleven[$key]='PK01';

                $competencyfieldsdatatwelve[$key]='PO01';



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

                $competencyfieldsdatanine[$key]='T';

                $competencyfieldsdataten[$key]='T25';

                $competencyfieldsdataeleven[$key]='PC01';

                $competencyfieldsdatatwelve[$key]='PK01';


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

                $competencyfieldsdatanine[$key]='';

                $competencyfieldsdataten[$key]='';

                $competencyfieldsdataeleven[$key]='';

                $competencyfieldsdatatwelve[$key]='EX56,EX665';


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

                $competencyfieldsdatanine[$key]='';

                $competencyfieldsdataten[$key]='';

                $competencyfieldsdataeleven[$key]='';

                $competencyfieldsdatatwelve[$key]='TP30222,TP30,TP500';


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

                $competencyfieldsdatanine[$key]='';

                $competencyfieldsdataten[$key]='';

                $competencyfieldsdataeleven[$key]='';

                $competencyfieldsdatatwelve[$key]='QU30222,QUP30,QU500';


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

                $competencyfieldsdatanine[$key]='L1,L2,L3';

                $competencyfieldsdataten[$key]='';

                $competencyfieldsdataeleven[$key]='';

                $competencyfieldsdatatwelve[$key]='';


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

    $csvexport->add_data($competencyfieldsdatanine);

    $csvexport->add_data($competencyfieldsdataten);

    $csvexport->add_data($competencyfieldsdataeleven);

    $csvexport->add_data($competencyfieldsdatatwelve);


    $csvexport->download_file();

    die;

}else{

    throw new required_capability_exception($context, 'local/competency:canbulkuploadcompetency', 'nopermissions', '');
}