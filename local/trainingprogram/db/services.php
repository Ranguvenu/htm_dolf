<?php
/**
 * Defines the version of Training program
 *
 * @package    local_trainingprogram
 * @copyright  2022 Naveen kumar <naveen@eabyas.com>
 */
defined('MOODLE_INTERNAL') || die();
$functions = array(


  //get_organization_users ..renu
     
    'local_organization_trainers' => array(
      'classname' => 'local_trainingprogram_external',
      'methodname' => 'get_organization_users',
      'classpath'   => 'local/trainingprogram/externallib.php',
      'description' => 'View all organizations based user',
      'services'      => array(MOODLE_OFFICIAL_MOBILE_SERVICE),
      'type' => 'write',
      'ajax' => true,
    ),

    'local_trainingprogram_viewprograms' => array(
      'classname' => 'local_trainingprogram_external',
      'methodname' => 'viewprograms',
      'classpath'   => 'local/trainingprogram/externallib.php',
      'description' => 'View all programs',
      'services'      => array(MOODLE_OFFICIAL_MOBILE_SERVICE),
      'type' => 'write',
      'ajax' => true,
    ),
    'local_trainingprogram_segmentlist' => array(
        'classname'     => 'local_trainingprogram_external',
        'methodname'    => 'segmentlist',
        'description'   => 'List of segments for given sector.',
        'type'          => 'read',
        'capabilities'  => 'local/trainingprogram:manage',
        'services'      => array(MOODLE_OFFICIAL_MOBILE_SERVICE),
        'ajax'          => true,
        'loginrequired' => false,
    ),
    
    'local_trainingprogram_viewprogramsectors' => array(
       'classname'     => 'local_trainingprogram_external',
       'methodname' => 'viewprogramsectors',
        'classpath'   => 'local/trainingprogram/externallib.php',
       'description' => 'View all sectors',
       'ajax' => true,
       'type' => 'read',
   ), 
    'local_trainingprogram_programcards' => array(
       'classname'     => 'local_trainingprogram_external',
       'methodname' => 'programcards',
        'classpath'   => 'local/trainingprogram/externallib.php',
       'description' => 'View program cards on dashboard',
       'ajax' => true,
       'type' => 'read',
       'loginrequired' => false
   ), 
    'display_competenciesinfo' => array(
        'classname' => 'local_trainingprogram_external',
        'methodname' => 'competenciesinfo',
        'classpath'   => 'local/trainingprogram/externallib.php',
        'description' => 'Competencies view',
        'ajax' => true,
        'type' => 'read',
    ), 

    'local_trainingprogram_ajaxdatalist' => array(
        'classname' => 'local_trainingprogram_external',
        'methodname'  => 'ajaxdatalist',
        'classpath'   => 'local/trainingprogram/externallib.php',
        'description' => 'Competencies data',
        'ajax' => true,
        'type' => 'read',
         'loginrequired' => false,
    ),

    'local_trainingprogram_viewtprograms' => array(
      'classname' => 'local_trainingprogram_external',
      'methodname' => 'cardviewprograms',
      'classpath'   => 'local/trainingprogram/externallib.php',
      'description' => 'View all programs',
      'type' => 'write',
      'services'      => array(MOODLE_OFFICIAL_MOBILE_SERVICE),
      'ajax' => true,
       'loginrequired' => false,
    ),

    'local_trainingprogram_detailedprogramviewservice' => array(
          'classname' => 'local_trainingprogram_external',
          'methodname' => 'detailedprogramviewservice',
          'classpath'   => 'local/trainingprogram/externallib.php',
          'description' => 'training program details',
          'services'      => array(MOODLE_OFFICIAL_MOBILE_SERVICE),
          'type' => 'write',
          'ajax' => true,
    ),

    'local_trainingprogram_deleteshedule' => array(
      'classname' => 'local_trainingprogram_external',
      'methodname' => 'deleteshedule',
      'classpath'   => 'local/trainingprogram/externallib.php',
      'description' => 'Delete Shedule',
      'type' => 'write',
      'ajax' => true,
    ),
    'local_trainingprogram_deleteprogram' => array(
      'classname' => 'local_trainingprogram_external',
      'methodname' => 'deleteprogram',
      'classpath'   => 'local/trainingprogram/externallib.php',
      'description' => 'Delete Shedule',
      'type' => 'write',
      'ajax' => true,
    ),
    'local_trainingprogram_publishprogram' => array(
      'classname' => 'local_trainingprogram_external',
      'methodname' => 'publishprogram',
      'classpath'   => 'local/trainingprogram/externallib.php',
      'description' => 'Publish Selected Program',
      'type' => 'write',
      'ajax' => true,
    ), 

    'local_trainingprogram_unpublishprogram' => array(
      'classname' => 'local_trainingprogram_external',
      'methodname' => 'unpublishprogram',
      'classpath'   => 'local/trainingprogram/externallib.php',
      'description' => 'Publish Selected Program',
      'type' => 'write',
      'ajax' => true,
    ), 
    'local_trainingprogram_viewptrainers' => array(
      'classname' => 'local_trainingprogram_external',
      'methodname' => 'viewptrainers',
      'classpath'   => 'local/trainingprogram/externallib.php',
      'description' => 'Publish Selected Program',
      'type' => 'write',
      'ajax' => true,
    ), 
    'local_trainingprogram_unassignuser' => array(
      'classname' => 'local_trainingprogram_external',
      'methodname' => 'unassignuser',
      'classpath'   => 'local/trainingprogram/externallib.php',
      'description' => 'Publish Selected Program',
      'type' => 'write',
      'ajax' => true,
    ), 
    'local_trainingprogram_viewprogramenrolledusers' => array(
      'classname' => 'local_trainingprogram_external',
      'methodname' => 'viewprogramenrolledusers',
      'classpath'   => 'local/trainingprogram/externallib.php',
      'description' => 'Publish Selected Program',
      'type' => 'write',
      'ajax' => true,
    ), 

    'local_trainingprogram_other_programs_course_view' => array(
      'classname' => 'local_trainingprogram_external',
      'methodname' => 'otherprogramscourseview',
      'classpath'   => 'local/trainingprogram/externallib.php',
      'description' => 'Publish Selected Program',
      'services'      => array(MOODLE_OFFICIAL_MOBILE_SERVICE),
      'type' => 'write',
      'ajax' => true,
    ), 
    'display_currentofferings' => array(
        'classname' => 'local_trainingprogram_external',
        'methodname' => 'currentofferingsdisplay',
        'classpath'   => 'local/trainingprogram/externallib.php',
        'description' => 'Currentofferings view',
        'ajax' => true,
        'type' => 'read',
    ), 
    'local_tphall_details' => array(
      'classname' => 'local_trainingprogram_external',
      'methodname' => 'tphall_data',
      'classpath'   => 'local/trainingprogram/externallib.php',
      'description' => 'get Program offerings post financial payments',
      'type' => 'read',
      'ajax' => true,
    ),  
    'tprogram_slotbooking' => array(
      'classname' => 'local_trainingprogram_external',
      'methodname' => 'tprogram_slotbooking',
      'classpath'   => 'local/trainingprogram/externallib.php',
      'description' => 'get Program offerings post financial payments',
      'type' => 'read',
      'ajax' => true,
    ),
    'remove_reservations' => array(
      'classname' => 'local_trainingprogram_external',
      'methodname' => 'remove_reservations',
      'classpath'   => 'local/trainingprogram/externallib.php',
      'description' => 'get Program offerings post financial payments',
      'type' => 'read',
      'ajax' => true,
    ),

    'local_trainingprogram_deletecoupon' => array(
      'classname' => 'local_trainingprogram_external',
      'methodname' => 'deletecoupon',
      'classpath'   => 'local/trainingprogram/externallib.php',
      'description' => 'Delete Coupon',
      'type' => 'write',
      'ajax' => true,
    ),
    'local_trainingprogram_enrollment' => array(
      'classname' => 'local_trainingprogram_external',
      'methodname' => 'enrollment_search',
      'classpath'   => 'local/trainingprogram/externallib.php',
      'description' => 'enrollment_search',
      'type' => 'read',
      'ajax' => true,
    ),
    'local_trainingprogram_deleteearlyregistration' => array(
      'classname' => 'local_trainingprogram_external',
      'methodname' => 'deleteearlyregistration',
      'classpath'   => 'local/trainingprogram/externallib.php',
      'description' => 'Delete earlyregistration',
      'type' => 'write',
      'ajax' => true,
    ),

   
    'local_trainingprogram_program_offerings' => array(
      'classname' => 'local_trainingprogram_external',
      'methodname' => 'get_programdetails',
      'classpath'   => 'local/trainingprogram/externallib.php',
      'description' => 'training program details',
      'services'      => array(MOODLE_OFFICIAL_MOBILE_SERVICE),
      'type' => 'write',
      'ajax' => true,
    ),
 

    'local_trainingprogram_competencies' => array(
      'classname' => 'local_trainingprogram_external',
      'methodname' => 'get_competencies',
      'classpath'   => 'local/trainingprogram/externallib.php',
      'description' => 'Competency Ids',
      'services'      => array(MOODLE_OFFICIAL_MOBILE_SERVICE),
      'type' => 'write',
      'ajax' => true,
    ),
    
    'local_trainingprogram_orgofficialprogramview' => array(
      'classname' => 'local_trainingprogram_external',
      'methodname' => 'orgofficialprogramview',
      'classpath'   => 'local/trainingprogram/externallib.php',
      'description' => 'training program details',
      'services'      => array(MOODLE_OFFICIAL_MOBILE_SERVICE),
      'type' => 'write',
      'ajax' => true,
    ),
    'local_trainingprogram_viewetrainingtopicsdata' => array(
      'classname' => 'local_trainingprogram_external',
      'methodname' => 'viewetrainingtopicsdata',
      'classpath'   => 'local/trainingprogram/externallib.php',
      'description' => 'training program details',
      'type' => 'write',
      'ajax' => true,
    ),
    'local_trainingprogram_deletetrainingtopic' => array(
      'classname' => 'local_trainingprogram_external',
      'methodname' => 'deletetrainingtopic',
      'classpath'   => 'local/trainingprogram/externallib.php',
      'description' => 'training program details',
      'type' => 'write',
      'ajax' => true,
    ),

    'viewprogramgoals' => array(
      'classname' => 'local_trainingprogram_external',
      'methodname' => 'viewprogramgoals',
      'classpath'   => 'local/trainingprogram/externallib.php',
      'description' => 'view program goals details',
      'type' => 'write',
      'ajax' => true,
    ),
    'deleteprogramgoals' => array(
      'classname' => 'local_trainingprogram_external',
      'methodname' => 'deleteprogramgoals',
      'classpath'   => 'local/trainingprogram/externallib.php',
      'description' => 'Delete the record',
      'ajax' => true,
      'type' => 'read',
     ), 
    'local_trainingprogram_viewrefundsettings' => array(
      'classname' => 'local_trainingprogram_external',
      'methodname' => 'viewrefundsetting',
      'classpath'   => 'local/trainingprogram/externallib.php',
      'description' => 'View the record',
      'ajax' => true,
      'type' => 'read',
    ), 

    'local_trainingprogram_deleterefundsetting' => array(
      'classname' => 'local_trainingprogram_external',
      'methodname' => 'deleterefundsetting',
      'classpath'   => 'local/trainingprogram/externallib.php',
      'description' => 'Delete the record',
      'ajax' => true,
      'type' => 'read',
    ), 

    'local_trainingprogram_replaceprogramuser' => array(
      'classname' => 'local_trainingprogram_external',
      'methodname' => 'replaceprogramuser',
      'classpath'   => 'local/trainingprogram/externallib.php',
      'description' => 'Replace the program user',
      'ajax' => true,
      'type' => 'read',
    ), 

    'local_trainingprogram_getdataforprogramcancellation' => array(
      'classname' => 'local_trainingprogram_external',
      'methodname' => 'getdataforprogramcancellation',
      'classpath'   => 'local/trainingprogram/externallib.php',
      'description' => 'Data for program cancellations',
      'ajax' => true,
      'type' => 'read',
    ), 

    'local_trainingprogram_cancelprogramuser' => array(
      'classname' => 'local_trainingprogram_external',
      'methodname' => 'cancelprogramuser',
      'classpath'   => 'local/trainingprogram/externallib.php',
      'description' => 'Cancel the program user',
      'ajax' => true,
      'type' => 'read',
    ), 
    'local_trainingprogram_org_enroluser' => array(
      'classname' => 'local_trainingprogram_external',
      'methodname' => 'org_enroluser',
      'classpath'   => 'local/trainingprogram/externallib.php',
      'description' => 'Organizational official enrolling trainees',
      'ajax' => true,
      'type' => 'read',
    ), 
     'display_addcpdinfo' => array(
      'classname' => 'local_trainingprogram_external',
      'methodname' => 'addcpdinfo',
      'classpath'   => 'local/trainingprogram/externallib.php',
      'description' => 'Addcpd view',
      'ajax' => true,
      'type' => 'read',
    ), 
   'local_trainingprogram_publishorunpublishoffering' => array(
        'classname' => 'local_trainingprogram_external',
        'methodname' => 'publishorunpublishoffering',
        'classpath'   => 'local/trainingprogram/externallib.php',
        'description' => 'Publish or unpublish offering',
        'ajax' => true,
        'type' => 'read',
      ),

    'local_trainingprogram_cancelentity' => array(
      'classname' => 'local_trainingprogram_external',
      'methodname' => 'cancelentity',
      'classpath'   => 'local/trainingprogram/externallib.php',
      'description' => 'Cancel Entity',
      'ajax' => true,
      'type' => 'read',
    ),

    'local_trainingprogram_entitycancellationrequests' => array(
      'classname' => 'local_trainingprogram_external',
      'methodname' => 'entitycancellationrequests',
      'classpath'   => 'local/trainingprogram/externallib.php',
      'description' => 'Entity Calculation Requests',
      'ajax' => true,
      'type' => 'read',
    ),
    'local_trainingprogram_programenrollmentsviewdata' => array(
          'classname' => 'local_trainingprogram_external',
          'methodname' => 'programenrollmentsviewdata',
          'classpath'   => 'local/trainingprogram/externallib.php',
          'description' => 'training program details',
          'type' => 'write',
          'ajax' => true,
    ),  
      'local_trainingprogram_edditingtrainer_confirmation' => array(
        'classname' => 'local_trainingprogram_external',
        'methodname' => 'edditingtrainer_confirmation',
        'classpath'   => 'local/trainingprogram/externallib.php',
        'description' => 'Un published Entities List',
        'ajax' => true,
        'type' => 'read',
      ),

      'local_trainingprogram_assign_edditingtrainer' => array(
        'classname' => 'local_trainingprogram_external',
        'methodname' => 'assign_edditingtrainer',
        'classpath'   => 'local/trainingprogram/externallib.php',
        'description' => 'Un published Entities List',
        'ajax' => true,
        'type' => 'read',
      ),
      'local_trainingprogram_newjobfamily_options' => array(
        'classname' => 'local_trainingprogram_external',
        'methodname' => 'newjobfamily_options',
        'classpath'   => 'local/trainingprogram/externallib.php',
        'description' => 'newjobfamily_options',
        'ajax' => true,
        'type' => 'read',
      ),
    'local_trainingprogram_update_offering_financially_closed_status' => array(
        'classname' => 'local_trainingprogram_external',
        'methodname' => 'update_offering_financially_closed_status',
        'classpath'   => 'local/trainingprogram/externallib.php',
        'description' => 'Previous Offering list',
        'ajax' => true,
        'type' => 'read',
      ),
    'local_trainingprogram_activity_approoved' => array(
      'classname' => 'local_trainingprogram_external',
      'methodname' => 'activity_approoved',
      'classpath'   => 'local/trainingprogram/externallib.php',
      'description' => 'Activity was approoved',
      'ajax' => true,
      'type' => 'write',
    ),
    'local_trainingprogram_getActivityCreator' => array(
      'classname' => 'local_trainingprogram_external',
      'methodname' => 'getActivityCreator',
      'classpath'   => 'local/trainingprogram/externallib.php',
      'description' => 'Activity was approoved',
      'ajax' => true,
      'type' => 'write',
    ),

       
    //program methods....renu
    'local_trainingprogram_programmethod' => array(
      'classname' => 'local_trainingprogram_external',
      'methodname' => 'get_programmethod',
      'classpath'   => 'local/trainingprogram/externallib.php',
      'description' => 'View all program methods',
      'type' => 'write',
      'ajax' => true,
    ),

    //delete program methods....renu
    'local_trainingprogram_deleteprogrammethod' => array(
      'classname' => 'local_trainingprogram_external',
      'methodname' => 'delete_programmethod',
      'classpath'   => 'local/trainingprogram/externallib.php',
      'description' => 'delete program methods',
      'type' => 'write',
      'ajax' => true,
    ),

     //evalution methods....renu
     'local_trainingprogram_evalutionmethod' => array(
      'classname' => 'local_trainingprogram_external',
      'methodname' => 'get_evalutionmethod',
      'classpath'   => 'local/trainingprogram/externallib.php',
      'description' => 'View all evalution methods', 
      'type' => 'write',
      'ajax' => true,
    ),

    // delete evalution methods....renu
      'local_trainingprogram_deleteevaluation' => array(
        'classname' => 'local_trainingprogram_external',
        'methodname' => 'delete_evaluationmethod',
        'classpath'   => 'local/trainingprogram/externallib.php',
        'description' => 'delete evalution methods',   
        'type' => 'write',
        'ajax' => true,
      ),


      'local_trainingprogram_viewprogramtopics' => array(
        'classname'     => 'local_trainingprogram_external',
        'methodname' => 'viewprogramtopics',
         'classpath'   => 'local/trainingprogram/externallib.php',
        'description' => 'View all sectors',
        'ajax' => true,
        'type' => 'read',
    ),
      'local_trainingprogram_checkofficial_availibility' => array(
        'classname'     => 'local_trainingprogram_external',
        'methodname' => 'checkofficial_availibility',
        'classpath'   => 'local/trainingprogram/externallib.php',
        'description' => 'Check whether the given slots are free or not',
        'ajax' => true,
        'type' => 'read',
      ),

    'local_trainingprogram_offeringprogramrequests' => array(
      'classname' => 'local_trainingprogram_external',
      'methodname' => 'offeringprogramrequests',
      'classpath'   => 'local/trainingprogram/externallib.php',
      'description' => 'Offering Program create/update/delete Requests',
      'ajax' => true,
      'type' => 'read',
    ),

    'local_trainingprogram_offering_program_action' => array(
      'classname' => 'local_trainingprogram_external',
      'methodname' => 'offering_program_action',
      'classpath'   => 'local/trainingprogram/externallib.php',
      'description' => 'Offering Program create/update/delete Requests',
      'ajax' => true,
      'type' => 'read',
    ),

    'local_trainingprogram_tofficialdeleteaction' => array(
      'classname' => 'local_trainingprogram_external',
      'methodname' => 'tofficialdeleteaction',
      'classpath'   => 'local/trainingprogram/externallib.php',
      'description' => 'Offering/Program delete action from training official',
      'ajax' => true,
      'type' => 'read',
    ),
    'local_trainingprogram_viewcurrentoffering' => array(
      'classname' => 'local_trainingprogram_external',
      'methodname' => 'viewcurrentoffering',
      'classpath'   => 'local/trainingprogram/externallib.php',
      'description' => 'View Current Offering',
      'ajax' => true,
      'type' => 'read',
    ),
    'local_trainingprogram_managementdiscountdata' => array(
      'classname' => 'local_trainingprogram_external',
      'methodname' => 'managementdiscountdata',
      'classpath'   => 'local/trainingprogram/externallib.php',
      'description' => 'View Current Offering',
      'ajax' => true,
      'type' => 'read',
    ),

    'local_trainingprogram_coupondiscountdata' => array(
      'classname' => 'local_trainingprogram_external',
      'methodname' => 'viewcoupondata',
      'classpath'   => 'local/trainingprogram/externallib.php',
      'description' => 'View coupons',
      'ajax' => true,
      'type' => 'read',
    ),

    'local_trainingprogram_earlyregistrationdiscountdata' => array(
      'classname' => 'local_trainingprogram_external',
      'methodname' => 'viewearlyregistrationdata',
      'classpath'   => 'local/trainingprogram/externallib.php',
      'description' => 'View early registrationsg',
      'ajax' => true,
      'type' => 'read',
    ),

    'local_trainingprogram_groupsdiscountdata' => array(
      'classname' => 'local_trainingprogram_external',
      'methodname' => 'groupsdiscountdata',
      'classpath'   => 'local/trainingprogram/externallib.php',
      'description' => 'View groups discounts',
      'ajax' => true,
      'type' => 'read',
    ),

    'local_trainingprogram_deletegroupdiscount' => array(
      'classname' => 'local_trainingprogram_external',
      'methodname' => 'deletegroupdiscount',
      'classpath'   => 'local/trainingprogram/externallib.php',
      'description' => 'Delete groups discounts',
      'ajax' => true,
      'type' => 'read',
    ),

    'local_trainingprogram_viewdiscountentity' => array(
      'classname' => 'local_trainingprogram_external',
      'methodname' => 'viewdiscountentity',
      'classpath'   => 'local/trainingprogram/externallib.php',
      'description' => 'View coupon/earlyregistration/group discounts',
      'ajax' => true,
      'type' => 'read',
    ),
);
