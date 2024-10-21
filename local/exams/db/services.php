<?php

// This file is part of Moodle - http://moodle.org/
//
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
 * local local_exams
 *
 * @package    local_exams
 * @copyright  2022 Revanth kumar grandhi <revanth.g@eabyas.com>
 */

// We defined the web service functions to install.

defined('MOODLE_INTERNAL') || die;
$services = array(

  'All Exams' => array( 
    'functions' => array ('local_exams_apiexam_qualifications'),
    'requiredcapability' => '',               
    'restrictedusers' => 0,                                           
    'enabled' => 1,                                                    
    'shortname' =>  'allexams',     
    'downloadfiles' => 0,    
    'uploadfiles'  => 0    
  ),

  'Exam Details' => array(
    'functions' => array ('local_exams_apiexam_qualificationdetails'), 
    'requiredcapability' => '',               
    'restrictedusers' => 0,                                             
    'enabled' => 1,                                                       
    'shortname' =>  'examdetails',      
    'downloadfiles' => 0,   
    'uploadfiles'  => 0      
  ),
  'Exam Qualifications' => array(
    'functions' => array ('local_exams_exam_qualifications'), 
    'requiredcapability' => '',               
    'restrictedusers' => 0,                                             
    'enabled' => 1,                                                       
    'shortname' =>  'examsfrontpage',      
    'downloadfiles' => 0,   
    'uploadfiles'  => 0      
  ),
  'Exam Details for Org Off' => array(
    'functions' => array ('local_exams_examdetails'), 
    'requiredcapability' => '',               
    'restrictedusers' => 0,                                             
    'enabled' => 1,                                                       
    'shortname' =>  'examsdetailsfororgoff',      
    'downloadfiles' => 0,   
    'uploadfiles'  => 0      
  )
);

$functions = array(
    'local_exams_view' => array(
        'classname' => 'local_exams_external',
        'methodname' => 'exams_view',
        'classpath' => 'local/exams/classes/external.php',
        'description' => 'List of all exams',
        'services'      => array(MOODLE_OFFICIAL_MOBILE_SERVICE),
        'ajax' => true,
        'type' => 'read',
    ),
    'local_deleteexam' => array(
        'classname' => 'local_exams_external',
        'methodname' => 'deleteexam',
        'classpath' => 'local/exams/classes/external.php',
        'description' => 'List of all exams',
        'ajax' => true,
        'type' => 'read',
    ),
    'local_publishexam' => array(
        'classname' => 'local_exams_external',
        'methodname' => 'publishexam',
        'classpath' => 'local/exams/classes/external.php',
        'description' => 'To publish the exams',
        'ajax' => true,
        'type' => 'read',
    ),
    'local_exam_info' => array(
        'classname' => 'local_exams_external',
        'methodname' => 'exam_info',
        'classpath' => 'local/exams/classes/external.php',
        'description' => 'Exam view',
        'ajax' => true,
        'type' => 'read',
    ),
    'local_sectors_info' => array(
        'classname' => 'local_exams_external',
        'methodname' => 'sectors_info',
        'classpath' => 'local/exams/classes/external.php',
        'description' => 'Exam view',
        'ajax' => true,
        'type' => 'read',
    ),
    'local_reviewexams_view' => array(
        'classname' => 'local_exams_external',
        'methodname' => 'reviewexams_view',
        'classpath' => 'local/exams/classes/external.php',
        'description' => 'Exam view',
        'ajax' => true,
        'type' => 'read',
    ),
    'local_userexams_view' => array(
        'classname' => 'local_exams_external',
        'methodname' => 'userexams_view',
        'classpath' => 'local/exams/classes/external.php',
        'description' => 'Exam view',
        'ajax' => true,
        'type' => 'read',
    ),
    'local_competencies_info' => array(
        'classname' => 'local_exams_external',
        'methodname' => 'competencies_info',
        'classpath' => 'local/exams/classes/external.php',
        'description' => 'Competencies view',
        'ajax' => true,
        'type' => 'read',
    ),
    'local_exams_exam_qualifications' => array(
        'classname' => 'local_exams_external',
        'methodname' => 'exam_qualifications',
        'classpath'   => 'local/exams/classes/external.php',
        'description' => 'Exam Qualification View',
        'services'      => array(MOODLE_OFFICIAL_MOBILE_SERVICE),
        'loginrequired' => false,
        'ajax' => true,
        'type' => 'write',
    ),

    'local_exams_apiexam_qualifications' => array(
        'classname' => 'local_exams_external',
        'methodname' => 'apiexam_qualifications',
        'classpath'   => 'local/exams/classes/external.php',
        'description' => 'Exam Qualification View',
        'loginrequired' => false,
        'ajax' => true,
        'type' => 'write',
    ),

    'local_exams_apiexam_qualificationdetails' => array(
        'classname' => 'local_exams_external',
        'methodname' => 'apiexam_qualificationdetails',
        'classpath'   => 'local/exams/classes/external.php',
        'description' => 'Exam Qualification View',
        'services'      => array(MOODLE_OFFICIAL_MOBILE_SERVICE),
        'loginrequired' => false,
        'ajax' => true,
        'type' => 'write',
    ),

     //Vinod - Exams fake block for exam official - Starts//
      'local_exams_manage_examofficial_block' => array(
        'classname' =>  'local_exams_external',
        'methodname' => 'examofficialblock',
        'classpath' => 'local/exams/classes/external.php',
        'description' => 'Exam Official Block',
        'ajax' => true,
        'type' => 'read'
    ),
    //Vinod - Exams fake block for exam official - Ends//

    'local_halls' => array(
            'classname'   => 'local_exams_external',
            'methodname'  => 'listofhalls',
            'classpath'   => 'local/exams/classes/external.php',
            'description' => 'hall booking',
            'type'        => 'write',
            'ajax' => true,
    ),
    'local_exams_ajaxdatalist' => array(
        'classname' => 'local_exams_external',
        'methodname'  => 'ctypeajaxdatalist',
        'classpath'   => 'local/exams/external.php',
        'description' => 'Competencies data',
        'ajax' => true,
        'type' => 'read',
         'loginrequired' => false,
    ),
    'local_exams_grievance_info'  => array(
        'classname' => 'local_exams_external',
        'methodname'  => 'grievance_info',
        'classpath'   => 'local/exams/external.php',
        'description' => 'grievance_info',
        'services'      => array(MOODLE_OFFICIAL_MOBILE_SERVICE),
        'ajax' => true,
        'type' => 'write',
    ),
    'local_exams_grievancestatus' => array(
        'classname' => 'local_exams_external',
        'methodname'  => 'grievancestatus',
        'classpath'   => 'local/exams/external.php',
        'description' => 'grievancestatus',
        'ajax' => true,
        'type' => 'write',
    ),

    'local_exams_view_grievance' => array(
        'classname' => 'local_exams_external',
        'methodname'  => 'view_grievance',
        'classpath'   => 'local/exams/external.php',
        'description' => 'view grievance details',
        'ajax' => true,
        'type' => 'read',
    ),
    'local_exams_enrollment' => array(
        'classname' => 'local_exams_external',
        'methodname'  => 'enrollment_search',
        'classpath'   => 'local/exams/external.php',
        'description' => 'enrollment_search',
        'ajax' => true,
        'type' => 'read',
    ),
    'local_viewexamusers' => array(
        'classname' => 'local_exams_external',
        'methodname'  => 'viewexamusers',
        'classpath'   => 'local/exams/external.php',
        'description' => 'examusers',
        'ajax' => true,
        'type' => 'read',
    ),

    'local_exam_reservations' => array(
        'classname' => 'local_exams_external',
        'methodname'  => 'exam_reservations',
        'classpath'   => 'local/exams/external.php',
        'description' => 'Exam Reservations',
        'ajax' => true,
        'type' => 'read',
    ),

    'local_exam_userreservations' => array(
        'classname' => 'local_exams_external',
        'methodname'  => 'exam_userreservations',
        'classpath'   => 'local/exams/external.php',
        'description' => 'Exam Reservations',
        'loginrequired' => false,
        'ajax' => true,
        'type' => 'read',
    ),

    'local_exams_exam_details' => array(
        'classname' => 'local_exams_external',
        'methodname'  => 'exam_details',
        'classpath'   => 'local/exams/external.php',
        'description' => 'Exam Details',
        'services'      => array(MOODLE_OFFICIAL_MOBILE_SERVICE),
        'loginrequired' => false,
        'ajax' => true,
        'type' => 'read',
    ),
    'local_exams_examdetails' => array(
        'classname' => 'local_exams_external',
        'methodname'  => 'examdetails',
        'classpath'   => 'local/exams/external.php',
        'description' => 'Exam details for Organization official',
        'loginrequired' => false,
        'services'      => array(MOODLE_OFFICIAL_MOBILE_SERVICE),
        'ajax' => true,
        'type' => 'read',
    ),
    'local_exams_profiles' => array(
        'classname' => 'local_exams_external',
        'methodname'  => 'exam_profiles',
        'classpath'   => 'local/exams/external.php',
        'description' => 'Exam details for Organization official',
        'loginrequired' => false,
        'services'      => array(MOODLE_OFFICIAL_MOBILE_SERVICE),
        'ajax' => true,
        'type' => 'read',
    ),
    'local_deleteprofile' => array(
        'classname' => 'local_exams_external',
        'methodname'  => 'delete_profile',
        'classpath'   => 'local/exams/external.php',
        'description' => 'Exam details for Organization official',
        'loginrequired' => false,
        'services'      => array(MOODLE_OFFICIAL_MOBILE_SERVICE),
        'ajax' => true,
        'type' => 'read',
    ),
    'local_exam_profileinfo' => array(
        'classname' => 'local_exams_external',
        'methodname'  => 'exam_profileinfo',
        'classpath'   => 'local/exams/external.php',
        'description' => 'Exam details for Organization official',
        'loginrequired' => false,
        'services'      => array(MOODLE_OFFICIAL_MOBILE_SERVICE),
        'ajax' => true,
        'type' => 'read',
    ),
    'local_exams_hall_schedules' => array(
        'classname' => 'local_exams_external',
        'methodname'  => 'hall_schedules',
        'classpath'   => 'local/exams/external.php',
        'description' => 'Exam details for Organization official',
        'loginrequired' => false,
        'services'      => array(MOODLE_OFFICIAL_MOBILE_SERVICE),
        'ajax' => true,
        'type' => 'read',
    ),
    'local_exam_enrouser' => array(
        'classname' => 'local_exams_external',
        'methodname'  => 'enrouser',
        'classpath'   => 'local/exams/external.php',
        'description' => 'Enrol user to Exam',
        'loginrequired' => false,
        'services'      => array(MOODLE_OFFICIAL_MOBILE_SERVICE),
        'ajax' => true,
        'type' => 'read',
    ),
    'local_exams_attempts' => array(
        'classname' => 'local_exams_external',
        'methodname'  => 'exam_attempts',
        'classpath'   => 'local/exams/external.php',
        'description' => 'Enrol Attempts',
        'loginrequired' => false,
        'services'      => array(MOODLE_OFFICIAL_MOBILE_SERVICE),
        'ajax' => true,
        'type' => 'read',
    ),
    'local_exam_deleteattempt' => array(
        'classname' => 'local_exams_external',
        'methodname'  => 'exam_deleteattempt',
        'classpath'   => 'local/exams/external.php',
        'description' => 'Enrol Attempts',
        'loginrequired' => false,
        'services'      => array(MOODLE_OFFICIAL_MOBILE_SERVICE),
        'ajax' => true,
        'type' => 'read',
    ),
    'local_fast_examenrolview' => array(
        'classname' => 'local_exams_external',
        'methodname'  => 'fast_examenrolview',
        'classpath'   => 'local/exams/external.php',
        'description' => 'List of fast exam enrol',
        'ajax' => true,
        'type' => 'read',
    ),
    'local_exams_tobereplacedusers' => array(
        'classname' => 'local_exams_external',
        'methodname'  => 'tobereplacedusers',
        'classpath'   => 'local/exams/external.php',
        'description' => 'List OF To Be Replace Users',
        'ajax' => true,
        'type' => 'read',
    ),

    // ***************** DL-304 IKRAM CODE STARTS HERE **************************
     'local_exam_cisi_authenticate' => array(
        'classname' => 'local_exams_external',
        'methodname'  => 'cisi_authenticate_signin',
        'classpath'   => 'local/exams/external.php',
        'description' => 'This service will return the auth token for CISI',
        'ajax' => true,
        'type' => 'read',
    ),
     'local_exam_cisi_get_access_token' => array(
        'classname' => 'local_exams_external',
        'methodname'  => 'cisi_get_access_token',
        'classpath'   => 'local/exams/external.php',
        'description' => 'This service will return the access token for CISI',
        'ajax' => true,
        'type' => 'read',
    ),
     'local_exam_cisi_create_user' => array(
        'classname' => 'local_exams_external',
        'methodname'  => 'cisi_create_user',
        'classpath'   => 'local/exams/external.php',
        'description' => 'This service will create new user for CISI',
        'ajax' => true,
        'type' => 'read',
    ),
     'local_exam_cisi_update_user' => array(
        'classname' => 'local_exams_external',
        'methodname'  => 'cisi_update_user',
        'classpath'   => 'local/exams/external.php',
        'description' => 'This service will update the user record for CISI',
        'ajax' => true,
        'type' => 'read',
    ),
     'local_exam_cisi_exam_mapping' => array(
        'classname' => 'local_exams_external',
        'methodname'  => 'cisi_exam_mapping',
        'classpath'   => 'local/exams/external.php',
        'description' => 'This service is use to map CISI Exams ',
        'ajax' => true,
        'type' => 'read',
     ),
    // ***************** DL-304 IKRAM CODE ENDS HERE **************************
    'local_exams_cancelexamcalc' => array(
        'classname' => 'local_exams_external',
        'methodname'  => 'exam_calculations',
        'classpath'   => 'local/exams/external.php',
        'description' => 'Exam cancel refund details',
        'ajax' => true,
        'type' => 'read',
    ),
    'local_exams_cancellationrefund' => array(
        'classname' => 'local_exams_external',
        'methodname'  => 'cancellationrefund',
        'classpath'   => 'local/exams/external.php',
        'description' => 'Refund the Exam cancel amount ',
        'ajax' => true,
        'type' => 'read',
    ),
    'local_exams_canceluser' =>  array(
        'classname' => 'local_exams_external',
        'methodname'  => 'canceluser',
        'classpath'   => 'local/exams/external.php',
        'description' => 'Cancel Exam User',
        'ajax' => true,
        'type' => 'read',
    ),
    'local_exams_getdataforexamcancellation' =>  array(
        'classname' => 'local_exams_external',
        'methodname'  => 'dataforexamcancellation',
        'classpath'   => 'local/exams/external.php',
        'description' => 'Exam Cancellation Data',
        'ajax' => true,
        'type' => 'read',
    ),
    'local_exams_replaceuser' =>  array(
        'classname' => 'local_exams_external',
        'methodname'  => 'replaceuser',
        'classpath'   => 'local/exams/external.php',
        'description' => 'Replace Exam User',
        'ajax' => true,
        'type' => 'read',
    ),

    'local_exams_rescheduleuser' =>  array(
        'classname' => 'local_exams_external',
        'methodname'  => 'rescheduleuser',
        'classpath'   => 'local/exams/external.php',
        'description' => 'Reschedule Exam User',
        'ajax' => true,
        'type' => 'read',
    ),

    'local_exams_validateexamschedule' =>  array(
        'classname' => 'local_exams_external',
        'methodname'  => 'validateexamschedule',
        'classpath'   => 'local/exams/external.php',
        'description' => 'Validate Exam Schedule',
        'ajax' => true,
        'loginrequired' => false,
        'type' => 'read',
    ),
    'local_exams_get_orgorderdetails' =>  array(
        'classname' => 'local_exams_external',
        'methodname'  => 'get_orgorderdetails',
        'classpath'   => 'local/exams/external.php',
        'description' => 'Validate Exam Schedule',
        'ajax' => true,
        'loginrequired' => false,
        'type' => 'read',
    ),
    'local_exams_attempt_request' =>  array(
        'classname' => 'local_exams_external',
        'methodname'  => 'attempt_request',
        'classpath'   => 'local/exams/external.php',
        'description' => 'Requesting the next atttempt',
        'ajax' => true,
        'loginrequired' => false,
        'type' => 'read',
    ),
    'local_exams_enrollmentconfirmations' =>  array(
        'classname' => 'local_exams_external',
        'methodname'  => 'enrollmentconfirmations',
        'classpath'   => 'local/exams/external.php',
        'description' => 'enrollmentconfirmations',
        'ajax' => true,
        'loginrequired' => false,
        'type' => 'read',
    ),

    'local_exams_removeenrollmentconfirmation' =>  array(
        'classname' => 'local_exams_external',
        'methodname'  => 'removeenrollmentconfirmation',
        'classpath'   => 'local/exams/external.php',
        'description' => 'removeenrollmentconfirmation',
        'ajax' => true,
        'loginrequired' => false,
        'type' => 'read',
    ),
    'local_exams_registerbulkenrollusers' =>  array(
        'classname' => 'local_exams_external',
        'methodname'  => 'registerbulkenrollusers',
        'classpath'   => 'local/exams/external.php',
        'description' => 'registerbulkenrollusers',
        'ajax' => true,
        'loginrequired' => false,
        'type' => 'read',
    ),
    'local_exams_generate_sadad_for_bulkenrollusers' =>  array(
        'classname' => 'local_exams_external',
        'methodname'  => 'generate_sadad_for_bulkenrollusers',
        'classpath'   => 'local/exams/external.php',
        'description' => 'generate_sadad_for_bulkenrollusers',
        'ajax' => true,
        'loginrequired' => false,
        'type' => 'read',
    ),
    'local_exams_addtofavourites' => array( 
                   'classname'   => 'local_exams_external', 
                   'methodname'  => 'addtofavourites',
                   'classpath'   => 'local/exams/classes/external.php',
                   'description' => 'Add to Favourites',
                   'type'        => 'write', 
                   'ajax'        => true,
    ),
    'local_exams_removefavourites' => array(
                     'classname'   => 'local_exams_external', 
                     'classpath'   => 'local/exams/classes/external.php',
                     'methodname'  => 'removefavourites', 
                     'description' => 'Remove Favourites',
                     'type'        => 'write',
                     'ajax'        => true,
    ),
    'local_exams_view_exam_schedules' => array(
        'classname'   => 'local_exams_external', 
        'methodname'  => 'list_exam_schedules', 
        'classpath'   => 'local/exams/classes/external.php',
        'description' => 'Remove Favourites',
        'type'        => 'write',
        'ajax'        => true,
    ),
    'local_exam_delete_schedule' => array(
        'classname'   => 'local_exams_external', 
        'methodname'  => 'delete_schedule', 
        'classpath'   => 'local/exams/classes/external.php',
        'description' => 'Remove Favourites',
        'type'        => 'write',
        'ajax'        => true,
    ),
    'local_exam_view_reservations' => array(
        'classname'   => 'local_exams_external', 
        'methodname'  => 'view_reservations', 
        'classpath'   => 'local/exams/classes/external.php',
        'description' => 'Remove Favourites',
        'type'        => 'write',
        'ajax'        => true,
    ),
    'local_exams_calculate_invoice' => array(
        'classname'   => 'local_exams_external', 
        'methodname'  => 'calculate_invoice', 
        'classpath'   => 'local/exams/classes/external.php',
        'description' => 'Remove Favourites',
        'type'        => 'write',
        'ajax'        => true,
    ),
    'local_exams_get_org_officials' => array(
        'classname'   => 'local_exams_external', 
        'methodname'  => 'get_org_officials', 
        'classpath'   => 'local/exams/classes/external.php',
        'description' => 'Remove Favourites',
        'type'        => 'write',
        'ajax'        => true,
    ),
    'local_exams_send_invoice' => array(
        'classname'   => 'local_exams_external', 
        'methodname'  => 'send_invoice', 
        'classpath'   => 'local/exams/classes/external.php',
        'description' => 'Remove Favourites',
        'type'        => 'write',
        'ajax'        => true,
    ),
    'local_exam_get_exam_users' => array(
        'classname'   => 'local_exams_external', 
        'methodname'  => 'get_exam_users', 
        'classpath'   => 'local/exams/classes/external.php',
        'description' => 'List of Exam enrolled users',
        'type'        => 'write',
        'ajax'        => true,
    ),
    'local_exam_fetch_filterdata' => array(
        'classname'   => 'local_exams_external', 
        'methodname'  => 'fetch_filterdata', 
        'classpath'   => 'local/exams/classes/external.php',
        'description' => 'List of Exam enrolled users',
        'type'        => 'write',
        'ajax'        => true,
    ),
    'local_exam_revert_enroluser' => array(
        'classname'   => 'local_exams_external', 
        'methodname'  => 'revert_enroluser', 
        'classpath'   => 'local/exams/classes/external.php',
        'description' => 'Revert Enroluser',
        'type'        => 'write',
        'ajax'        => true,
    ),

);
