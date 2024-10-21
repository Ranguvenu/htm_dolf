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
 * @package    local_learningtracks
 * @copyright  2022 e abyas  <info@eabyas.com>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
defined('MOODLE_INTERNAL') || die;
$functions = array(
   
    'local_learningtracks_get_learningtracks' => array(
        'classname' => 'local_learningtracks_external',
        'methodname'=> 'get_learningtracks',
        'classpath' => 'local/learningtracks/classes/external.php',
        'description' => 'Return list of learningtracks.',
        'type' => 'read',
        'ajax' => true,
        'loginrequired' => true,
    ),
    'local_learningtracks_form_selector' => array(
        'classname' => 'local_learningtracks_external',
        'methodname'=> 'form_selector',
        'classpath' => 'local/learningtracks/classes/external.php',
        'description' => 'Form Selector',
        'type' => 'read',
        'ajax' => true,
    ),
    'local_learningtracks_competency_list' => array(
        'classname' => 'local_learningtracks_external',
        'methodname'=> 'competency_list',
        'classpath' => 'local/learningtracks/classes/external.php',
        'description' => 'view competencies',
        'type' => 'read',
        'ajax' => true,
    ),
    'local_learningtracks_trackview_courses' => array(
        'classname' => 'local_learningtracks_external',
        'methodname'=> 'trackview_courses',
        'classpath' => 'local/learningtracks/classes/external.php',
        'description' => 'trackview_courses',
        'type' => 'read',
        'ajax' => true,
    ),
    'local_learningtracks_get_learningpath' => array(
        'classname' => 'local_learningtracks_external',
        'methodname'=> 'get_learning_path',
        'classpath' => 'local/learningtracks/classes/external.php',
        'description' => 'Return list of learningtracks.',
        'loginrequired' => false,
        'ajax' => true,
    
    ),
    'local_learningtracks_trackview_users' => array(
        'classname' => 'local_learningtracks_external',
        'methodname'=> 'trackview_users',
        'classpath' => 'local/learningtracks/classes/external.php',
        'description' => 'trackview_users',
        'type' => 'read',
        'ajax' => true,
    ),
    'local_learningtracks_viewlearningpath' => array(
        'classname' => 'local_learningtracks_external',
        'methodname'=> 'viewlearningpath',
        'classpath' => 'local/learningtracks/classes/external.php',
        'description' => 'viewlearningpath',
        'type' => 'read',
        'ajax' => true,
        'loginrequired' => false,
    ),
    
    'local_learningtracks_enrolledlearningpath' => array(
        'classname' => 'local_learningtracks_external',
        'methodname'=> 'enrolledlearningpath',
        'classpath' => 'local/learningtracks/classes/external.php',
        'description' => 'enrolledlearningpath',
        'type' => 'read',
        'ajax' => true,
    ),
    'local_learningtracks_deletetrack' => array(
        'classname' => 'local_learningtracks_external',
        'methodname'=> 'delete_learningtrack',
        'classpath' => 'local/learningtracks/classes/external.php',
        'description' => 'delete_learningtrack',
        'type' => 'write',
        'ajax' => true,
    ),
    'local_learningtracks_enrollment'  => array(
        'classname' => 'local_learningtracks_external',
        'methodname'=> 'learningtrack_enrollment',
        'classpath' => 'local/learningtracks/classes/external.php',
        'description' => 'learningtrack_enrollment',
        'type' => 'write',
        'ajax' => true,
    ),
    'local_learningtracks_deleteitems' => array(
        'classname' => 'local_learningtracks_external',
        'methodname'=> 'deleteitems',
        'classpath' => 'local/learningtracks/classes/external.php',
        'description' => 'deleteitems',
        'type' => 'write',
        'ajax' => true,
    ),
);
