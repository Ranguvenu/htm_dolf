<?php
require_once(dirname(__FILE__) . '/../../config.php');
require_once($CFG->libdir . '/adminlib.php');
$format = optional_param('format', '', PARAM_ALPHA);
$context = context_system::instance();
    $fields = array(
        'programname' => 'ProgramName',
        'programcode' => 'ProgramCode',
        'oldid' => 'OldId',
        'programnamearabic' => 'ProgramNameArabic',
        'programdescription' => 'ProgramDescription',
        'programstartdate' => 'ProgramStartdate',
        'programenddate' => 'ProgramEnddate',
        'programcost' => 'ProgramCost',
        'programsellingprice' => 'ProgramSellingPrice',
        'programactualprice' => 'ProgramActualPrice',
        'programtrainingmethods' => 'ProgramTrainingMethods',
        'programevolutionmethods' => 'ProgramEvolutionMethods',
        'programlanguages' => 'ProgramLanguages',
        'programduration' => 'ProgramDuration',
        'programrunningtime' => 'ProgramRunningTime',
        'programsectors' => 'ProgramSectors',
        'programlevel' => 'ProgramLevel',
        'competencytypes' => 'CompetencyTypes',
        'programcompetencies' => 'ProgramCompetencies',
        'programjobfamilies' => 'ProgramJobfamilies',
        'programdiscount' => 'ProgramDiscount',
        'trainingtopic' => 'TrainingTopic',
        'trainingtype' => 'TrainingType',
        'programimage' => 'ProgramImage',
        'attendacepercentage' => 'AttendacePercentage',
        'preprograms' => 'PrePrograms',
        'integratedprograms' => 'IntegratedPrograms',
        'postprograms' => 'PostPrograms',
        'ispublished' => 'isPublished',
    );
require_once($CFG->libdir . '/csvlib.class.php');
$filename = clean_filename(get_string('programupload','local_trainingprogram'));
$csvexport = new csv_export_writer();
$csvexport->set_filename($filename);
$csvexport->add_data($fields);
    $tprogram1 = array('New Testing','ST123',"abc-25458-cde-kjasdhf","اختبار العينة",'Sample Testing update','2022-08-25','2022-10-15','1','1800','1000','0*1*2*3','0*1','0*1',5,3,'banking*insurance*finance',1,'technicalcompetencies*behavioralcompetencies*corecompetencies','T88*B10*C01*com','family1*IHR14*CHR14',0,'1*2*6','online*offline*eleraning','https://lmsstaging.fa.gov.sa/pluginfile.php/1/theme_academy/logo/1674451544/white_logo.png',80,'V1P0476*V1P0488','DLM0476*VRMC0488','BF2R0101*BF2R0097',1);
    $csvexport->add_data($tprogram1);
    $tprogram2 = array('Sample program','SMPTP',"abc-25458-cde-kjasdhf","اختبار العينة",'Sample program for refernce','2022-08-31','2022-10-31','1','1200','1000','0*2*3','online*offline','1',5,3,'banking*insurance*finance',1,'technicalcompetencies*behavioralcompetencies*corecompetencies','T88*B10*C01*com','family1*IHR14*CHR14',0,'1*2*3','1*2','https://lmsstaging.fa.gov.sa/pluginfile.php/1/theme_academy/logo/1674451544/white_logo.png',10,'BF2R0101*BF2R0097','DLM0476*VRMC0488','V1P0476*V1P0488',0);
    $csvexport->add_data($tprogram2);
    $tprogram3 = array('AWS Training','AWSTRG',"abc-25458-cde-kjasdhf","اختبار العينة",'AWS traingin update','2022-07-20','2022-9-20','1','1800','1000','1*2*3','0','0',5,3,'banking*insurance*finance',1,'technicalcompetencies*behavioralcompetencies*corecompetencies','T88*B10*C01*com','family1*IHR14*CHR14',0,'4*6*8','offline*eleraning','https://lmsstaging.fa.gov.sa/pluginfile.php/1/theme_academy/logo/1674451544/white_logo.png',30,'V1P0476*V1P0488','DLM0476*VRMC0488','BF2R0101*BF2R0097',1);
       $csvexport->add_data($tprogram3);
    $tprogram4 = array('Test Program','T123',"abc-25458-cde-kjasdhf","اختبار العينة",'Sample Testing update','2022-08-25','2022-10-15','1','1800','1000','0*1*2*3','0*1','0*1',5,3,'banking*insurance*finance',1,'technicalcompetencies*behavioralcompetencies*corecompetencies','T88*B10*C01*com','family1*IHR14*CHR14',0,'1*2*3','online','https://lmsstaging.fa.gov.sa/pluginfile.php/1/theme_academy/logo/1674451544/white_logo.png',80,'BF2R0101*BF2R0097','DLM0476*VRMC0488','V1P0476*V1P0488',0);
    $csvexport->add_data($tprogram4);
$csvexport->download_file();
die;
