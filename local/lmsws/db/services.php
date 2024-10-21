<?php

// Moodle is free software: you can redistribute it and/or modify
// it under the terms of the GNU General Public License as published by
// the Free Software Foundation, either version 3 of the License, or
// (at your option) any later version.
//
// Moodle is distributed in the hope that it will be useful,
// but WITHOUT ANY WARRANTY; without even the implied warranty of
// MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
// GNU General Public License for more details.
//
// You should have received a copy of the GNU General Public License
// along with Moodle.  If not, see <http://www.gnu.org/licenses/>.

/**
 * Web service local plugin template external functions and service definitions.
 *
 * @package    localwstemplate
 * @copyright  2011 Jerome Mouneyrac
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

// We defined the web service functions to install.
$functions = array(
      
       'ws_get_create_offering' => array(
                'classname'   => 'lms_webservice_migration',
                'methodname'  => 'create_offering',
                'classpath'   => '/local/lmsws/migration/migration.php',
                'description' => 'migration Scheduling program',
                'type'        => 'read',
        ),
        'ws_get_programuserenroll' => array(
                'classname'   => 'lms_webservice_migration',
                'methodname'  => 'programuserenroll',
                'classpath'   => '/local/lmsws/migration/migration.php',
                'description' => 'migration user enrollment',
                'type'        => 'read',
        ),
        'ws_get_userenroll' => array(
                'classname'   => 'lms_webservice_migration',
                'methodname'  => 'userenroll',
                'classpath'   => '/local/lmsws/migration/migration.php',
                'description' => 'migration user enrollment',
                'type'        => 'read',
        ),

         'ws_get_userenrollonly' => array(
                'classname'   => 'lms_webservice_migration',
                'methodname'  => 'userenrollonly',
                'classpath'   => '/local/lmsws/migration/migration.php',
                'description' => 'migration user enrollment',
                'type'        => 'read',
        ),
         
   
           'ws_get_userenrolldelete' => array(
                'classname'   => 'lms_webservice_migration',
                'methodname'  => 'userenrolldelete',
                'classpath'   => '/local/lmsws/migration/migration.php',
                'description' => 'delete users',
                'type'        => 'read',
        ),

              'ws_get_enrolldelete' => array(
                'classname'   => 'lms_webservice_migration',
                'methodname'  => 'enrolldelete',
                'classpath'   => '/local/lmsws/migration/migration.php',
                'description' => 'delete users',
                'type'        => 'read',
        ),
       
       
              'ws_get_userexamenrolonlydelete' => array(
                'classname'   => 'lms_webservice_migration',
                'methodname'  => 'userexamenrolonlydelete',
                'classpath'   => '/local/lmsws/migration/migration.php',
                'description' => 'delete users',
                'type'        => 'read',
        ),

        'ws_get_userenrollorg' => array(
                'classname'   => 'lms_webservice_migration',
                'methodname'  => 'userenrollorg',
                'classpath'   => '/local/lmsws/migration/migration.php',
                'description' => 'migration user enrollment',
                'type'        => 'read',
        ),


          'ws_get_userexamenrolmig' => array(
                'classname'   => 'lms_webservice_migration',
                'methodname'  => 'userexamenrolmig',
                'classpath'   => '/local/lmsws/migration/migration.php',
                'description' => 'migration user enrollment',
                'type'        => 'read',
        ),

                'ws_get_userexamenrolonly' => array(
                'classname'   => 'lms_webservice_migration',
                'methodname'  => 'userexamenrolonly',
                'classpath'   => '/local/lmsws/migration/migration.php',
                'description' => 'migration user enrollment',
                'type'        => 'read',
        ),

                
                'ws_get_userexamenrolonlyupdate' => array(
                'classname'   => 'lms_webservice_migration',
                'methodname'  => 'userexamenrolonlyupdate',
                'classpath'   => '/local/lmsws/migration/migration.php',
                'description' => 'migration user enrollment',
                'type'        => 'read',
        ),

                

        'ws_get_userexam' => array(
                'classname'   => 'lms_webservice_migration',
                'methodname'  => 'userexam',
                'classpath'   => '/local/lmsws/migration/migration.php',
                'description' => 'migration user exam enrollment',
                'type'        => 'read',
        ),

        'ws_get_userexamhistory' => array(
                'classname'   => 'lms_webservice_migration',
                'methodname'  => 'userexamhistory',
                'classpath'   => '/local/lmsws/migration/migration.php',
                'description' => 'migration user exam enrollment',
                'type'        => 'read',
        ),

         'ws_get_userexamhistoryupdate' => array(
                'classname'   => 'lms_webservice_migration',
                'methodname'  => 'userexamhistoryupdate',
                'classpath'   => '/local/lmsws/migration/migration.php',
                'description' => 'migration user exam enrollment',
                'type'        => 'read',
        ),

        'ws_get_userexamenrol' => array(
                'classname'   => 'lms_webservice_migration',
                'methodname'  => 'userexamenrol',
                'classpath'   => '/local/lmsws/migration/migration.php',
                'description' => 'migration user exam enrollment',
                'type'        => 'read',
        ),
        'ws_get_ValidateTestCenterUser' => array(
                'classname'   => 'lms_webservice_exam',
                'methodname'  => 'ValidateTestCenterUser',
                'classpath'   => '/local/lmsws/exam/exam.php',
                'description' => 'exam center validation',
                'type'        => 'read',
        ),

        'ws_get_GetTestCenterTodayPeriods' => array(
                'classname'   => 'lms_webservice_exam',
                'methodname'  => 'GetTestCenterTodayPeriods',
                'classpath'   => '/local/lmsws/exam/exam.php',
                'description' => 'exam center validation',
                'type'        => 'read',
        ),

         'ws_get_GenerateDayPeriodExams' => array(
                'classname'   => 'lms_webservice_exam',
                'methodname'  => 'GenerateDayPeriodExams',
                'classpath'   => '/local/lmsws/exam/exam.php',
                'description' => 'exam center validation',
                'type'        => 'read',
        ),
        //API for listing all training programs with filtration option
        'local_trainingprogram_viewallprogramsservice' => array(
              'classname' => 'local_trainingprogram_external',
              'methodname' => 'viewallprogramsservice',
              'classpath'   => 'local/trainingprogram/externallib.php',
              'description' => 'View all programs',
              'type' => 'write',
              'ajax' => true,
        ),
        //Get All Programs
        'local_trainingprogram_getallprograms' => array(
              'classname' => 'local_trainingprogram_external',
              'methodname' => 'getallprograms',
              'classpath'   => 'local/trainingprogram/externallib.php',
              'description' => 'training program details',
              'type' => 'write',
              'ajax' => true,
        ),
        //Get Program Info
        'local_trainingprogram_getprograminfo' => array(
              'classname' => 'local_trainingprogram_external',
              'methodname' => 'getprograminfo',
              'classpath'   => 'local/trainingprogram/externallib.php',
              'description' => 'training program details by id',
              'type' => 'write',
              'ajax' => true,
        ),
        //Get All Programs By Job Family ID
        'local_trainingprogram_getallprogramsbyjobfamilyid' => array(
              'classname' => 'local_trainingprogram_external',
              'methodname' => 'getallprogramsbyjobfamilyid',
              'classpath'   => 'local/trainingprogram/externallib.php',
              'description' => 'training program details by jobfamily id',
              'type' => 'write',
              'ajax' => true,
        ),
        //Get All Programs By Competency ID
        'local_trainingprogram_getallprogramsbycompetencyid' => array(
              'classname' => 'local_trainingprogram_external',
              'methodname' => 'getallprogramsbycompetencyid',
              'classpath'   => 'local/trainingprogram/externallib.php',
              'description' => 'training program details by competency id',
              'type' => 'write',
              'ajax' => true,
        ),
        //Training Attachment
        'local_trainingprogram_trainingattachment'=>array(
                'classname'   => 'local_trainingprogram_external',
                'methodname'  => 'trainingattachment',
                'classpath'   => 'local/trainingprogram/externallib.php',
                'description' => 'Get attachment by id',
                'type'        => 'read',
        ),
        //Event Attachment
        'local_trainingprogram_eventattachment'=>array(
                'classname'   => 'local_trainingprogram_external',
                'methodname'  => 'eventattachment',
                'classpath'   => 'local/trainingprogram/externallib.php',
                'description' => 'Get attachment by id',
                'type'        => 'read',
        ),
        //Check Certificate
        'local_trainingprogram_checkcertificate'=>array(
                'classname'   => 'local_trainingprogram_external',
                'methodname'  => 'checkcertificate',
                'classpath'   => 'local/trainingprogram/externallib.php',
                'description' => 'Get attachment by id',
                'type'        => 'read',
        ),
        //Get MainSectors
        'local_sector_get_main_sectors'=>array(
                'classname'   => 'local_sector_external',
                'methodname'  => 'getmainsectors',
                'classpath'   => 'local/sector/classes/external.php',
                'description' => 'Get Main Sectors',
                'type'        => 'read',
        ),
        //Get Sectors By ParentId  
        'local_sector_get_sector_by_parentid'=>array(
                'classname'   => 'local_sector_external',
                'methodname'  => 'getsectorbyparentid',
                'classpath'   => 'local/sector/classes/external.php',
                'description' => 'Get Main Sectors',
                'type'        => 'read',
        ),  
        //Get JobFamily By SectorId:
        'local_sector_get_jobfamiles_sectorid'=>array(
                'classname'   => 'local_sector_external',
                'methodname'  => 'getjobfamilies_sectorid',
                'classpath'   => 'local/sector/classes/external.php',
                'description' => 'Get Job Families Using Sector Id',
                'type'        => 'read',
        ),
        //Get JobRole By Family Id
        'local_sector_get_jobrole_jobfamilyid'=>array(
                'classname'   => 'local_sector_external',
                'methodname'  => 'getjobrole_jobfamilyid',
                'classpath'   => 'local/sector/classes/external.php',
                'description' => 'Get Job Roles and Responsibilities Using Job Family Id',
                'type'        => 'read',
        ),
        //Get Event Statistic
        'local_events_get_event_statistic'=>array(
                'classname'   => 'local_events_external',
                'methodname'  => 'get_eventstatistic',
                'classpath'   => 'local/events/classes/external.php',
                'description' => 'Get Event Statistic',
                'type'        => 'read',
        ),
        // Get Training Statistic
        'local_trainingprogram_get_training_statistic'=>array(
                'classname'   => 'local_trainingprogram_external',
                'methodname'  => 'get_trainingstatistic',
                'classpath'   => 'local/trainingprogram/externallib.php',
                'description' => 'Get Training Program Statistic',
                'type'        => 'read',
        ),
        //Get All Events
        'local_events_viewalleventsservice' => array(
              'classname' => 'local_events_external',
              'methodname' => 'viewalleventsservice',
              'classpath'   => 'local/events/classes/external.php',
              'description' => 'View all events',
              'type' => 'read',
              'ajax' => true,
        ),
        //Get All Events Types
        'local_events_geteventstypes' => array(
              'classname' => 'local_events_external',
              'methodname' => 'geteventstypes',
              'classpath'   => 'local/events/classes/external.php',
              'description' => 'View all events types',
              'type' => 'read',
              'ajax' => true,
        ),
        //Get Events Info
        'local_events_geteventinfo' => array(
              'classname' => 'local_events_external',
              'methodname' => 'geteventinfo',
              'classpath'   => 'local/events/classes/external.php',
              'description' => 'event details',
              'type' => 'read',
              'ajax' => true,
        ),
        // API for List of all Exams
        'local_exams_get_exams' => array(
                'classname' => 'local_exams_external',
                'methodname' => 'apiexam_qualifications',
                'classpath'   => 'local/exams/classes/external.php',
                'description' => 'List of ALL Exams',
                'loginrequired' => false,
                'ajax' => true,
                'type' => 'write',
        ),
        // API for Exam details
        'local_exams_exam_info' => array(
                'classname' => 'local_exams_external',
                'methodname' => 'apiexam_qualificationdetails',
                'classpath'   => 'local/exams/classes/external.php',
                'description' => 'Exam Info',
                'loginrequired' => false,
                'ajax' => true,
                'type' => 'write',
        ),
        // API To Get All Exams Jadarat
        'local_exams_exam_filters' => array(
                'classname' => 'local_exams_external',
                'methodname' => 'exam_filters',
                'classpath'   => 'local/exams/classes/external.php',
                'description' => 'Exam Jadarat',
                'loginrequired' => false,
                'ajax' => true,
                'type' => 'write',
        ),
        //Get Competency By Job Role Id
        'local_competency_getcompetency_jobroleid'=>array(
                'classname'   => '\local_competency\external',
                'methodname'  => 'getcompetency_jobroleid',
                'classpath'   => 'local/competency/classes/external.php',
                'description' => 'Get Competencies Using Job Role Id',
                'type'        => 'read',
        ),
        //Get Competency level By competency Id
        'local_competency_getcompetencylevel_cid'=>array(
                'classname'   => '\local_competency\external',
                'methodname'  => 'getcompetencylevel_cid',
                'classpath'   => 'local/competency/classes/external.php',
                'description' => 'Get Competency Level  Using competency Id',
                'type'        => 'read',
        ),
        //Get Competencytypes 
        'local_competency_getcompetencytypes'=>array(
                'classname'   => '\local_competency\external',
                'methodname'  => 'getcompetencytypes',
                'classpath'   => 'local/competency/classes/external.php',
                'description' => 'Get Competencies Types',
                'type'        => 'read',
        ),
            //Get Competency By Type Id
        'local_competency_getcompetencybytypeid'=>array(
                'classname'   => '\local_competency\external',
                'methodname'  => 'getcompetencybytypeid',
                'classpath'   => 'local/competency/classes/external.php',
                'description' => 'Get Competencies Types',
                'type'        => 'read',
        ),
        // API To Get All Exams related to Job Family
        'local_exams_exam_byjobfamily' => array(
                'classname' => 'local_exams_external',
                'methodname' => 'exam_byjobfamily',
                'classpath'   => 'local/exams/classes/external.php',
                'description' => 'Exam based on Job Family',
                'loginrequired' => false,
                'ajax' => true,
                'type' => 'write',
        ),
        // API To Get All Exams related to Competencies
        'local_exams_exam_bycompetencies' => array(
                'classname' => 'local_exams_external',
                'methodname' => 'exam_bycompetencyid',
                'classpath'   => 'local/exams/classes/external.php',
                'description' => 'Exam based on Competencies',
                'loginrequired' => false,
                'ajax' => true,
                'type' => 'write',
        ),
        // API To Get Exam Centers
        'local_exams_exam_centers' => array(
                'classname' => 'local_exams_external',
                'methodname' => 'exam_centers',
                'classpath'   => 'local/exams/classes/external.php',
                'description' => 'Exam Centers',
                'loginrequired' => false,
                'ajax' => true,
                'type' => 'write',
        ),
        // API To Get Exam Attachments
        'local_exams_exam_attachments' => array(
                'classname' => 'local_exams_external',
                'methodname' => 'exam_attachments',
                'classpath'   => 'local/exams/classes/external.php',
                'description' => 'Exam Attachments',
                'loginrequired' => false,
                'ajax' => true,
                'type' => 'write',
        ),
        // API To Get Exam Attachments
        'local_exams_exam_statistics' => array(
                'classname' => 'local_exams_external',
                'methodname' => 'exam_statistics',
                'classpath'   => 'local/exams/classes/external.php',
                'description' => 'Exam Attachments',
                'loginrequired' => false,
                'ajax' => true,
                'type' => 'write',
        ),
        //Get Competency Search
        'local_competency_competencysearch'=>array(
                'classname'   => '\local_competency\external',
                'methodname'  => 'competency_search',
                'classpath'   => 'local/competency/classes/external.php',
                'description' => 'Get Competencies Search',
                'type'        => 'read',

        ), 
        // Get All Training Types
        'local_trainingprogram_trainingtypes' => array(
              'classname' => 'local_trainingprogram_external',
              'methodname' => 'get_trainingtypes',
              'classpath'   => 'local/trainingprogram/externallib.php',
              'description' => 'training types',
              'type' => 'read',
              'ajax' => true,
        ),
        // Get All Training Topics
        'local_trainingprogram_trainingtopics' => array(
              'classname' => 'local_trainingprogram_external',
              'methodname' => 'get_trainingtopics',
              'classpath'   => 'local/trainingprogram/externallib.php',
              'description' => 'training topics',
              'type' => 'read',
              'ajax' => true,
        ),
        //Learning Tracks details
        'local_learningtracks_get_learningtracksinfo' => array(
                'classname' => 'local_learningtracks_external',
                'methodname'=> 'get_learningtracksinfo',
                'classpath' => 'local/learningtracks/classes/external.php',
                'description' => 'Return Learningtracks info.',
                'type' => 'read',
        ),
        // Get All Learning Tracks
        'local_learningtracks_getalllearningtracks' => array(
              'classname' => 'local_learningtracks_external',
              'methodname' => 'getalllearningtracks',
              'classpath'   => 'local/learningtracks/classes/external.php',
              'description' => 'Get All Learning Tracks',
              'type' => 'read',
              'ajax' => true,
        ), 
        // Check Valid Saudi National Id
        'local_userapproval_checkvalidsaudinationalid' => array(
              'classname' => 'local_userapproval_external',
              'methodname' => 'checkvalidsaudinationalid',
              'classpath'   => 'local_userapproval/classes/external.php',
              'description' => 'Check Valid Saudi National Id',
              'type' => 'read',
              'ajax' => true,
        ),   
        //Get list of FAQ's
       'block_faqser' => array(
                'classname' => 'block_faq_external',
                'methodname' => 'getfaqser',
                'classpath'   => 'blocks/faq/classes/external.php',
                'description' => 'Get the faqs list',
                'type' => 'read',
                'ajax' => true,
        ),
        //Get Media Elements
        'block_documentuploadser' => array(
                'classname' => 'block_documentupload_external',
                'methodname' => 'getdocumentuploadser',
                'classpath'   => 'blocks/documentupload/classes/external.php',
                'description' => 'Get the documents card list',
                'type' => 'read',
                'ajax' => true,
        ),
     

        // API to get Parner Types
        'local_organization_get_partnertypes' => array(
                'classname' => 'local_organization_external',
                'methodname' => 'get_partnertypes',
                'classpath'   => 'local/organization/classes/external.php',
                'description' => 'Get Partner Types',
                'loginrequired' => false,
                'ajax' => true,
                'type' => 'write',
        ),
        // API to get Parners
        'local_organization_get_partners' => array(
                'classname' => 'local_organization_external',
                'methodname' => 'get_partners',
                'classpath'   => 'local/organization/classes/external.php',
                'description' => 'Get Partners',
                'loginrequired' => false,
                'ajax' => true,
                'type' => 'write',
        ),
               
        //Get Job Family Career PathsImages
        'local_userapproval_getjobfamilycareerpathsimages' => array(
                'classname'   => 'local_sector_external',
                'methodname'  => 'getjobfamilycareerpathsimages',
                'classpath'   => 'local/sector/classes/external.php',
                'description' => 'Get Main Sectors',
                'type'        => 'read',
                'ajax' => true,
        ),   
        // API to get Parners Statistic
        'local_org_partnerstatistics' => array(
                'classname' => 'local_organization_external',
                'methodname' => 'partnerstatistics',
                'classpath'   => 'local/organization/classes/external.php',
                'description' => 'Get Partners',
                'loginrequired' => false,
                'ajax' => true,
                'type' => 'write',
        ), 

        // API to get training flyer
        'local_trainingprogram_gettrainingflyer' => array(
                'classname' => 'local_trainingprogram_external',
                'methodname' => 'gettrainingflyer',
                'classpath'   => 'local/trainingprogram/externallib.php',
                'description' => 'Get Training Flyer',
                'loginrequired' => false,
                'ajax' => true,
                'type' => 'write',
        ),                 
       
);

// We define the services to install as pre-build services. A pre-build service is not editable by administrator.
$services = array(
        'ws_get_ValidateTestCenterUser' => array(
                'classname' => 'lms_webservice_exam',
                'methodname' => 'ValidateTestCenterUser',
                'classpath' => '/local/lmsws/exam/exam.php',
                'description' => 'exam center validation',
                'services'      => array(MOODLE_OFFICIAL_MOBILE_SERVICE),
                'ajax' => true,
                'type' => 'read',
        ),
        'ws_get_GetTestCenterTodayPeriods' => array(
                'classname' => 'lms_webservice_exam',
                'methodname' => 'GetTestCenterTodayPeriods',
                'classpath' => '/local/lmsws/exam/exam.php',
                'description' => 'exam center validation',
                'services'      => array(MOODLE_OFFICIAL_MOBILE_SERVICE),
                'ajax' => true,
                'type' => 'read',
        ),
           'ws_get_GenerateDayPeriodExams' => array(
                'classname' => 'lms_webservice_exam',
                'methodname' => 'GenerateDayPeriodExams',
                'classpath' => '/local/lmsws/exam/exam.php',
                'description' => 'exam center validation',
                'services'      => array(MOODLE_OFFICIAL_MOBILE_SERVICE),
                'ajax' => true,
                'type' => 'read',
        ),
        'LMS Web Service' => array(
                'functions' => array_keys($functions),
                'restrictedusers' => 0,
                'enabled'=>1,
                'shortname' => 'faws'
        )
);
