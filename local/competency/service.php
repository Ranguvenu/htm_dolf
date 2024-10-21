<?php
/*
* This file is a part of e abyas Info Solutions.
*
* Copyright e abyas Info Solutions Pvt Ltd, India.
*
* This course_sync is free software; you can redistribute it and/or modify
* it under the terms of the GNU General Public License as published by
* the Free Software Foundation; either version 3 of the License, or
* (at your option) any later version.
*
* This course_sync is distributed in the hope that it will be useful,
* but WITHOUT ANY WARRANTY; without even the implied warranty of
* MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
* GNU General Public License for more details.
*
* You should have received a copy of the GNU General Public License
* along with this course_sync.  If not, see <http://www.gnu.org/licenses/>.
*
 */
define('AJAX_SCRIPT', true);

require_once('../../config.php');

global $DB, $CFG, $USER, $PAGE;

require_once($CFG->libdir . '/externallib.php');


define('PREFERRED_RENDERER_TARGET', RENDERER_TARGET_GENERAL);

$rawjson = file_get_contents('php://input');



// $args=array('offset'=>0,'limit'=>2,'filterdata'=>json_encode(array('search_query'=>'','competencytype'=>'corecompetencies')));

// $tabmethodname="local_competency_viewallcompetencies_service";
// $response = external_api::call_external_function($tabmethodname, $args, true);

$args=array('id'=>4);

$tabmethodname="local_competency_detailedcompetencyview_service";
$response = external_api::call_external_function($tabmethodname, $args, true);

echo json_encode($response);