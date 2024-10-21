<?php
/**
 * This file is part of eAbyas
 *
 * Copyright eAbyas Info Solutons Pvt Ltd, India
 *
 * This program is free software; you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation; either version 3 of the License, or
 * (at your option) any later version.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with this program.  If not, see <http://www.gnu.org/licenses/>.
 *
 * @author eabyas  <info@eabyas.in>
 * @package F-academy
 * @subpackage local_cpd
 */
defined('MOODLE_INTERNAL') || die;
$functions = array(
    'local_cpd_deletecpd' => array(
        'classname' => 'local_cpd_external',
        'methodname' => 'deletecpd',
        'classpath' => 'local/cpd/externallib.php',
        'description' => 'delete cpd',
        'ajax' => true,
        'type' => 'read'
    ),

    'local_cpd_evidencestatus' => array(
        'classname' => 'local_cpd_external',
        'methodname' => 'evidencestatus',
        'classpath' => 'local/cpd/externallib.php',
        'description' => 'Manage Evidence Status',
        'ajax' => true,
        'type' => 'read'
    ),

    'local_cpd_view' =>  array(
        'classname' => 'local_cpd_external',
        'methodname' => 'cpd_view',
        'classpath' => 'local/cpd/externallib.php',
        'description' => 'Manage CPD',
        'services'      => array(MOODLE_OFFICIAL_MOBILE_SERVICE),
        'ajax' => true,
        'type' => 'read'
    ),

    'local_cpd_cpd_usersview' => array(
        'classname' => 'local_cpd_external',
        'methodname' => 'cpd_usersview',
        'classpath' => 'local/cpd/externallib.php',
        'description' => 'Manage CPD Users',
        'ajax' => true,
        'type' => 'read'
    ),
    'local_cpd_user_evidence' => array(
        'classname'   => 'local_cpd_external',
        'methodname'  => 'cpd_user_evidence',
        'classpath'   => 'local/cpd/externallib.php',
        'description' => 'user evidence details',
        'services'      => array(MOODLE_OFFICIAL_MOBILE_SERVICE),
        'type'        => 'write',
        'ajax' => true,
    ),    

    'local_cpd_evidenceuserinfo' => array(
        'classname'   => 'local_cpd_external',
        'methodname'  => 'evidenceuserinfo',
        'classpath'   => 'local/cpd/externallib.php',
        'description' => 'evidence user details',
        'type'        => 'write',
        'services'      => array(MOODLE_OFFICIAL_MOBILE_SERVICE),
        'ajax' => true,
    ),

    'local_cpd_reported_hrs' => array(
        'classname'   => 'local_cpd_external',
        'methodname'  => 'reported_hrs',
        'classpath'   => 'local/cpd/externallib.php',
        'description' => 'evidence reported_hrs details',
        'type'        => 'read',
        'ajax' => true,
    ),

    'local_cpd_training_programs' => array(
        'classname'   => 'local_cpd_external',
        'methodname'  => 'training_programs',
        'classpath'   => 'local/cpd/externallib.php',
        'description' => 'evidence related_sample_program details',
        'type'        => 'read',
        'ajax' => true,
    ),

    'local_cpd_delete_training_program' => array(
        'classname'   => 'local_cpd_external',
        'methodname'  => 'delete_training_program',
        'classpath'   => 'local/cpd/externallib.php',
        'description' => 'delete_training_program',
        'type'        => 'write',
        'ajax' => true,
    ),

    /*'local_cpd_view_reported_program' => array(
        'classname'   => 'local_cpd_external',
        'methodname'  => 'view_reported_program',
        'classpath'   => 'local/cpd/externallib.php',
        'description' => 'evidence related_sample_program details',
        'type'        => 'read',
        'ajax' => true,
    ),*/

    'local_cpd_deleteevidence' => array(
        'classname'   => 'local_cpd_external',
        'methodname'  => 'user_deleteevidence',
        'classpath'   => 'local/cpd/externallib.php',
        'description' => 'deleteevidence',
        'type'        => 'write',
        'ajax' => true,
    ),

    'local_cpd_viewevidence' => array(
        'classname'   => 'local_cpd_external',
        'methodname'  => 'user_viewevidence',
        'classpath'   => 'local/cpd/externallib.php',
        'description' => 'evidence related_sample_program details',
        'type'        => 'read',
        'ajax' => true,
    ),
    'local_cpd_form_selector'  => array(
        'classname'   => 'local_cpd_external',
        'methodname'  => 'form_selector',
        'classpath'   => 'local/cpd/externallib.php',
        'description' => 'form_selector details',
        'type'        => 'read',
        'ajax' => true,
    ),
    //Vinod- CPD fake block for exam official - Starts//
     'local_cpd_manage_cpd_block'  => array(
        'classname'   => 'local_cpd_external',
        'methodname'  => 'cpdblock',
        'classpath'   => 'local/cpd/externallib.php',
        'description' => 'form_selector details',
        'type'        => 'read',
        'ajax' => true,
    ),
    //Vinod- CPD fake block for exam official - Ends//
    'local_cpd_orgcpdview' => array(
        'classname'   => 'local_cpd_external',
        'methodname'  => 'cpd_orgview',
        'classpath'   => 'local/cpd/externallib.php',
        'description' => 'cpd_orgview',
        'type'        => 'write',
        'ajax' => true,
    ),
    'local_cpd_usercpddetails' => array(
        'classname'   => 'local_cpd_external',
        'methodname'  => 'usercpddetails',
        'classpath'   => 'local/cpd/externallib.php',
        'description' => 'cpd user details',
        'services'      => array(MOODLE_OFFICIAL_MOBILE_SERVICE),
        'type'        => 'write',
        'ajax' => true,
    ),    
);
