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
 * local local_organization
 *
 * @package    local_organization
 * @copyright  2022 eAbyas <eAbyas.in>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

// We defined the web service functions to install.

defined('MOODLE_INTERNAL') || die;

$services = array(
        'Organization Creation' => array(                                                // the name of the web service
            'functions' => array ('local_organization_create_organization'), // web service functions of this service
            'requiredcapability' => '',                // if set, the web service user need this capability to access 
                                                                            // any function of this service. For example: 'some/capability:specified'                 
            'restrictedusers' => 0,                                             // if enabled, the Moodle administrator must link some user to this service                                                                 // into the administration
            'enabled' => 1,                                                       // if enabled, the service can be reachable on a default installation
            'shortname' =>  'organizationcreation',       // optional – but needed if restrictedusers is set so as to allow logins.
            'downloadfiles' => 0,    // allow file downloads.
            'uploadfiles'  => 0      // allow file uploads.
        )
);
$functions = array(
     'local_organization_create_organization' => array(
        'classname' => 'local_organization_external',
        'methodname' => 'create_organization',
        'classpath' => 'local/organization/classes/external.php',
        'description' => 'create new record',
        'ajax' => true,
        'type' => 'write',
     ),
    'local_organization_view' => array(
        'classname' => 'local_organization_external',
        'methodname' => 'organization_view',
        'classpath' => 'local/organization/classes/external.php',
        'description' => 'List of all organizations',
        'ajax' => true,
        'type' => 'read',
    ),
    'local_deleteorganization' => array(
            'classname'   => 'local_organization_external',
            'methodname'  => 'deleteorganization',
            'classpath'   => 'local/organization/classes/external.php',
            'description' => 'delete organization',
            'type'        => 'write',
            'ajax' => true,
    ),
    'local_deleteorguser' => array(
            'classname'   => 'local_organization_external',
            'methodname'  => 'deleteorguser',
            'classpath'   => 'local/organization/classes/external.php',
            'description' => 'deleting organization user',
            'type'        => 'write',
            'ajax' => true,
    ),
    'local_approve_organization' => array(
            'classname'   => 'local_organization_external',
            'methodname'  => 'approve_organization',
            'classpath'   => 'local/organization/classes/external.php',
            'description' => 'approving organization',
            'type'        => 'write',
            'ajax' => true,
    ),
    'local_reject_organization' => array(
            'classname'   => 'local_organization_external',
            'methodname'  => 'reject_organization',
            'classpath'   => 'local/organization/classes/external.php',
            'description' => 'rejecting organization',
            'type'        => 'write',
            'ajax' => true,
    ),
    'local_enrolledusers_view' => array(
            'classname'   => 'local_organization_external',
            'methodname'  => 'enrolledusers_view',
            'classpath'   => 'local/organization/classes/external.php',
            'description' => 'ernrolled users data',
            'type'        => 'write',
            'ajax' => true,
    ),
    'local_organization_info' => array(
            'classname'   => 'local_organization_external',
            'methodname'  => 'organization_info',
            'classpath'   => 'local/organization/classes/external.php',
            'description' => 'organization details',
            'type'        => 'write',
            'ajax' => true,
    ),
    'local_organization_userslist' => array(
            'classname'   => 'local_organization_external',
            'methodname'  => 'organization_userslist',
            'classpath'   => 'local/organization/classes/external.php',
            'description' => 'organization enrolled users',
            'type'        => 'write',
            'ajax' => true,
    ),
    'local_authusers_view' => array(
            'classname'   => 'local_organization_external',
            'methodname'  => 'authusers_view',
            'classpath'   => 'local/organization/classes/external.php',
            'description' => 'organization enrolled users',
            'type'        => 'write',
            'ajax' => true,
    ),

    'local_organization_orgitems_list' => array(
        'classname'   => 'local_organization_external',
        'methodname'  => 'orgitems_list',
        'classpath'   => 'local/organization/classes/external.php',
        'description' => 'organization items list',
        'type'        => 'write',
        'ajax' => true,
        ),

    'local_organization_enrollment' => array(
      'classname' => 'local_organization_external',
      'methodname' => 'enrollment_search',
      'classpath'   => 'local/organization/classes/external.php',
      'description' => 'enrollment_search',
      'type' => 'read',
      'ajax' => true,
    ),
     'viewpartnertypes' => array(
            'classname'   => 'local_organization_external',
            'methodname'  => 'partnertypes_info',
            'classpath'   => 'local/organization/classes/external.php',
            'description' => 'details',
            'type'        => 'write',
            'ajax' => true,
    ),
    'deletepartnertypes' => array(
            'classname'   => 'local_organization_external',
            'methodname'  => 'deletepartnertypes',
            'classpath'   => 'local/organization/classes/external.php',
            'description' => 'delete',
            'type'        => 'write',
            'ajax' => true,
    ),
    'local_organization_partnertypes' => array(
        'classname' => 'local_organization_external',
        'methodname' => 'partner_types',
        'classpath' => 'local/organization/classes/external.php',
        'description' => 'List of all organizations',
        'ajax' => true,
        'type' => 'read',
    ),
    'local_organization_viewinvoices' => array(
        'classname' => 'local_organization_external',
        'methodname' => 'view_invoicesummary',
        'classpath' => 'local/organization/classes/external.php',
        'description' => 'List of all organizations',
        'ajax' => true,
        'type' => 'read',
    ),


);

$services = array(

    'viewpartnertype' => array( 
    'functions' => array ('viewpartnertype'),
    'requiredcapability' => '',               
    'restrictedusers' => 0,                                           
    'enabled' => 1,                                                    
    'shortname' =>  'partnertypes',     
    'downloadfiles' => 0,    
    'uploadfiles'  => 0    
  ),
);
