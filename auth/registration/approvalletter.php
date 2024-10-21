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
 * Competency view page
 *
 * @package    auth_registration
 * @copyright  2022 e abyas  <info@eabyas.com>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

define('AJAX_SCRIPT', true);

use auth_registration\action\registration as registration;

require_once(dirname(__FILE__) . '/../../config.php');

global $DB, $CFG, $USER, $PAGE;

require_once($CFG->libdir . '/pdflib.php');
require_once($CFG->libdir . '/filelib.php');


if($_SERVER['REQUEST_METHOD'] === 'POST') {

  header('Content-type: application/pdf');
  http_response_code(200);



  $registrationform = new auth_registration\form\organization_registration_form(null, array('form_status' => 2), 'post', '', null, true,$_REQUEST);

  $_REQUEST['form_status']=2;
 


  $validatedata=$registrationform->validation($_REQUEST,$_FILES);

  unset($validatedata['approvalletter_group']); 

  if (empty($validatedata)){
      $formdata = (object)$_REQUEST;
    $licensekey=$formdata->licensekey;

    
    $orgdetails = $DB->get_record('organization_draft',array('licensekey'=>$formdata->licensekey));
    $orgdetails->sectors = $orgdetails->orgsector;
    $orgdetails->segment = $orgdetails->orgsegment;

    
    return registration::generate_organization_approvalletter($orgdetails);

  }else{
    // Bad method
    http_response_code(405);
    exit();
  }


}

// Bad method
http_response_code(405);
exit();
