<?php
// This file is part of Moodle - http://moodle.org/
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
 * @package    local_userapproval
 * @copyright  2022 eAbyas Info Solutions<info@eabyas.com>
 * @author     Vinod Kumar  <vinod.p@eabyas.com>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
defined('MOODLE_INTERNAL') || die;
  $services = array(
      'userregistration' => array(   // the name of the web service
          'functions' => array ('local_userapproval_register_user'), // web service functions of this service
          'requiredcapability' => '',                // if set, the web service user need this capability to access 
                                                                              // any function of this service. For example: 'some/capability:specified'                 
          'restrictedusers' => 0,                                             // if enabled, the Moodle administrator must link some user to this service
                                                                              // into the administration
          'enabled' => 1,                                                       // if enabled, the service can be reachable on a default installation
          'shortname' =>  '',       // optional – but needed if restrictedusers is set so as to allow logins.
          'downloadfiles' => 0,    // allow file downloads.
          'uploadfiles'  => 0      // allow file uploads.
       )
  );
$functions = array(

  'local_userapproval_deleterequest' => array(
    'classname' => 'local_userapproval_external',
    'methodname' => 'deleterequest',
    'classpath'   => 'local/userapproval/classes/external.php',
    'description' => 'Delete Request',
    'type' => 'write',
    'ajax' => true,
   ),

  'local_userapproval_assign_by_admin' => array(
    'classname' => 'local_userapproval_external',
    'methodname' => 'assign_by_admin',
    'classpath'   => 'local/userapproval/classes/external.php',
    'description' => 'Trainer/Experts assign by Admin ',
    'type' => 'write',
    'ajax' => true,
   ),

 'local_users_view' => array(
 'classname' => 'local_userapproval_external',
 'methodname' => 'usersview',
 'classpath'   => 'local/userapproval/classes/external.php',
 'description' => 'List all users',
 'ajax' => true,
 'type' => 'read',
  ),
 'local_userapproval_deleteteuser' => array(
  'classname' => 'local_userapproval_external',
  'methodname' => 'deleteteuser',
  'classpath'   => 'local/userapproval/classes/external.php',
  'description' => 'Delete User',
  'type' => 'write',
  'ajax' => true,
 ),
 'local_userapproval_approveuser' => array(
  'classname' => 'local_userapproval_external',
  'methodname' => 'approveuser',
  'classpath'   => 'local/userapproval/classes/external.php',
  'description' => 'Approve User',
  'type' => 'write',
  'ajax' => true,
 ),
 'local_userapproval_rejectuser' => array(
  'classname' => 'local_userapproval_external',
  'methodname' => 'rejectuser',
  'classpath'   => 'local/userapproval/classes/external.php',
  'description' => 'Reject User',
  'type' => 'write',
  'ajax' => true,
 ),
 'local_userapproval_viewregistration' => array(
  'classname' => 'local_userapproval_external',
  'methodname' => 'viewregistration',
  'classpath'   => 'local/userapproval/classes/external.php',
  'description' => 'Reject User',
  'type' => 'write',
  'ajax' => true,
 ),
'local_users_orgrequest' => array(
  'classname' => 'local_userapproval_external',
  'methodname' => 'orgrequest',
  'classpath'   => 'local/userapproval/classes/external.php',
  'description' => 'Reject User',
  'type' => 'write',
  'ajax' => true,
 ),
'local_userapproval_rejectorgrequest' => array(
  'classname' => 'local_userapproval_external',
  'methodname' => 'rejectorgrequest',
  'classpath'   => 'local/userapproval/classes/external.php',
  'description' => 'Reject User',
  'type' => 'write',
  'ajax' => true,
 ),
'local_userapproval_approveorgrequest' => array(
  'classname' => 'local_userapproval_external',
  'methodname' => 'approveorgrequest',
  'classpath'   => 'local/userapproval/classes/external.php',
  'description' => 'Reject User',
  'type' => 'write',
  'ajax' => true,
),
'local_users_totalorgpendingrequests' => array(
  'classname' => 'local_userapproval_external',
  'methodname' => 'totalorgpendingrequests',
  'classpath'   => 'local/userapproval/classes/external.php',
  'description' => 'Reject User',
  'type' => 'write',
  'ajax' => true,
),
'local_userapproval_deletebannerimage'=> array(
  'classname' => 'local_userapproval_external',
  'methodname' => 'deletebannerimage',
  'classpath'   => 'local/userapproval/classes/external.php',
  'description' => 'Delete banner image',
  'type' => 'write',
  'ajax' => true,
),
'local_trainer_expert_request_view'=> array(
 'classname' => 'local_userapproval_external',
 'methodname' => 'trainer_expert_request_view',
 'classpath'   => 'local/userapproval/classes/external.php',
 'description' => 'Training Offical View ',
 'type' => 'write',
 'ajax' => true,
),
'local_userapproval_rejectrequest'=> array(
 'classname' => 'local_userapproval_external',
 'methodname' => 'rejectrequest',
 'classpath'   => 'local/userapproval/classes/external.php',
 'description' => 'Reject Request',
 'type' => 'write',
 'ajax' => true,
),
'local_userapproval_approverequest'=> array(
 'classname' => 'local_userapproval_external',
 'methodname' => 'approverequest',
 'classpath'   => 'local/userapproval/classes/external.php',
 'description' => 'Approve Request',
 'type' => 'write',
 'ajax' => true,
),
'local_userapproval_removeorgrequest'=> array(
 'classname' => 'local_userapproval_external',
 'methodname' => 'removeorgrequest',
 'classpath'   => 'local/userapproval/classes/external.php',
 'description' => 'Approve Request',
 'type' => 'write',
 'ajax' => true,
),
'local_userapproval_cancelrequest'=> array(
 'classname' => 'local_userapproval_external',
 'methodname' => 'cancelorgrequest',
 'classpath'   => 'local/userapproval/classes/external.php',
 'description' => 'Cancel Request',
 'type' => 'write',
 'ajax' => true,
),

'local_userapproval_register_user' => array(
 'classname' => 'local_userapproval_external',
 'methodname' => 'registeruser',
 'classpath'   => 'local/userapproval/classes/external.php',
 'description' => 'Create User',
 'ajax' => true,
 'type' => 'read',
  ),
'local_userapproval_recommendedexams' => array(
 'classname' => 'local_userapproval_external',
 'methodname' => 'recommendedexams',
 'classpath'   => 'local/userapproval/classes/external.php',
 'description' => 'Recommended Entities',
 'ajax' => true,
 'type' => 'read',
  ),
'local_userapproval_recommendedprograms' => array(
 'classname' => 'local_userapproval_external',
 'methodname' => 'recommendedprograms',
 'classpath'   => 'local/userapproval/classes/external.php',
 'description' => 'Recommended Entities',
 'ajax' => true,
 'type' => 'read',
  ),
'local_userapproval_itemenrolledlist'=> array(
  'classname' => 'local_userapproval_external',
  'methodname' => 'itemenrolledlist',
  'classpath'   => 'local/userapproval/classes/external.php',
  'description' => 'Recommended Entities',
  'ajax' => true,
  'type' => 'read',
),

 'local_fast_userapprovalenrolview' => array(
        'classname' => 'local_userapproval_external',
        'methodname'  => 'fast_userapprovalenrolview',
        'classpath'   => 'local/userapproval/external.php',
        'description' => 'List of fast User enrol',
        'ajax' => true,
        'type' => 'read',
    ),
    'local_user_customswitchrole' => array(
      'classname'   => 'local_userapproval_external', 
      'methodname'  => 'get_user_roles', 
      'classpath'   => 'local/userapproval/external.php',
      'description' => 'Fetching other roles',
      'type'        => 'write',
      'ajax'        => true,
  ),
'local_userapproval_individualrequestdata' => array(
  'classname'   => 'local_userapproval_external', 
  'methodname'  => 'individualrequesteddata', 
  'classpath'   => 'local/userapproval/external.php',
  'description' => 'Individual Requested Data',
  'type'        => 'write',
  'ajax'        => true,
),
  
);
