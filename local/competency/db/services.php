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
 * Class containing helper methods for processing data requests.
 *
 * @package    local_competency
 * @copyright  2022 e abyas  <info@eabyas.com>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
defined('MOODLE_INTERNAL') || die;
$functions = array(
    'local_competency_search_identity' => array(
        'classname' => '\local_competency\external\search_identity',
        'classpath' => 'local/competency/classes/external/search_identity.php',
        'description' => 'Return list of competency identities fields.',
        'type' => 'read',
        'ajax' => true,
        'loginrequired' => true,
    ),
    'local_competency_get_competencies' => array(
        'classname' => '\local_competency\external',
        'methodname'=> 'get_competencies',
        'classpath' => 'local/competency/classes/external.php',
        'description' => 'Return list of competencies.',
        'type' => 'read',
        'capabilities' => 'local/competency:managecompetencies,local/competency:viewcompetencies',
        'services'      => array(MOODLE_OFFICIAL_MOBILE_SERVICE),
        'ajax' => true,
        'loginrequired' => true,
    ),
    'local_competency_delete_data_competency' => array(
        'classname' => '\local_competency\external',
        'methodname'=> 'delete_data_competency',
        'classpath' => 'local/competency/classes/external.php',
        'description' => 'Return list of competencies.',
        'type' => 'write',
        'capabilities' => 'local/competency:managecompetencies,local/competency:candeletecompetency',
        'ajax' => true,
        'loginrequired' => true,
    ),
    'local_competency_get_competencypc' => array(
        'classname' => '\local_competency\external',
        'methodname'=> 'get_competencypc',
        'classpath' => 'local/competency/classes/external.php',
        'description' => 'Return list of competency performance and criterias.',
        'type' => 'read',
        'capabilities' => 'local/competency:managecompetencyperformance,local/competency:viewcompetencyperformance',
        'ajax' => true,
        'loginrequired' => true,
    ),
    'local_competency_get_mycompetencies' => array(
        'classname' => '\local_competency\external',
        'methodname'=> 'get_mycompetencies',
        'classpath' => 'local/competency/classes/external.php',
        'description' => 'Return list of my competencies.',
        'type' => 'read',
        'services'      => array(MOODLE_OFFICIAL_MOBILE_SERVICE),
        'ajax' => true,
        'loginrequired' => true,
    ),
    'local_competency_get_myallcompetencies' => array(
        'classname' => '\local_competency\external',
        'methodname'=> 'get_myallcompetencies',
        'classpath' => 'local/competency/classes/external.php',
        'description' => 'Return list of my all competencies.',
        'services' => array(MOODLE_OFFICIAL_MOBILE_SERVICE),
        'type' => 'read',
        'ajax' => true,
        'loginrequired' => true,
    ),
    'local_competency_get_objectivesinfo' => array(
        'classname' => '\local_competency\external',
        'methodname'=> 'get_objectivesinfo',
        'classpath' => 'local/competency/classes/external.php',
        'description' => 'Return list of competency objectives.',
        'type' => 'read',
        'ajax' => true,
        'loginrequired' => true,
    ),
    'local_competency_delete_data_competencypc' => array(
        'classname' => '\local_competency\external',
        'methodname'=> 'delete_data_competencypc',
        'classpath' => 'local/competency/classes/external.php',
        'description' => 'Return list of competency performance and criterias.',
        'type' => 'write',
        'capabilities' => 'local/competency:managecompetencyperformance,local/competency:candeletecompetencyperformance',
        'ajax' => true,
        'loginrequired' => true,
    ),
    'local_competency_delete_competencypcobjectives' => array(
        'classname' => '\local_competency\external',
        'methodname'=> 'delete_competencypcobjectives',
        'classpath' => 'local/competency/classes/external.php',
        'description' => 'Return list of competency objectives.',
        'type' => 'write',
        'capabilities' => 'local/competency:managecompetencyobjectives,local/competency:candeletecompetencyobjectives',
        'ajax' => true,
        'loginrequired' => true,
    ),
    'local_competency_get_competencyquestions' => array(
        'classname' => '\local_competency\external',
        'methodname'=> 'get_competencyquestions',
        'classpath' => 'local/competency/classes/external.php',
        'description' => 'Return list of competency questions.',
        'type' => 'read',
        'ajax' => true,
        'loginrequired' => true,
    ),
    //API for listing all competencies with filtration option
    'local_competency_viewallcompetencies_service' => array(
      'classname' => '\local_competency\external',
      'methodname' => 'viewallcompetencies_service',
      'classpath'   => 'local/competency/classes/external.php',
      'description' => 'View all competencies',
      'type' => 'read',
      'ajax' => true,
    ),

    //API for listing competency details
    'local_competency_detailedcompetencyview_service' => array(
      'classname' => '\local_competency\external',
      'methodname' => 'detailedcompetencyview_service',
      'classpath'   => 'local/competency/classes/external.php',
      'services'      => array(MOODLE_OFFICIAL_MOBILE_SERVICE),
      'description' => 'competency details',
      'type' => 'read',
      'ajax' => true,
    ),
);
$services = array(

  'All Competencies Listing' => array( 
    'functions' => array ('local_competency_viewallcompetencies_service'),
    'requiredcapability' => '',               
    'restrictedusers' => 0,                                           
    'enabled' => 1,                                                    
    'shortname' =>  'allcompetencieslisting',     
    'downloadfiles' => 0,    
    'uploadfiles'  => 0    
  ),

  'Competency Listing Details' => array(                                             
    'functions' => array ('local_competency_detailedcompetencyview_service'), 
    'requiredcapability' => '',               
    'restrictedusers' => 0,                                             
    'enabled' => 1,                                                       
    'shortname' =>  'competencylistingdetails',      
    'downloadfiles' => 0,   
    'uploadfiles'  => 0      
  )
);
