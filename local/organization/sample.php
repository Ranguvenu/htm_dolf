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
 * @package BizLMS
 * @subpackage local_users
 */
require_once(dirname(__FILE__) . '/../../config.php');
require_once($CFG->libdir . '/adminlib.php');
$format = optional_param('format', 'csv', PARAM_ALPHA);
$systemcontext = context_system::instance();
if ($format) {
    $fields = array(
        'oldid'=>'OldID',
        'licensekey'=>'Licensekey',
        'fullname'=>'Organization Name',
        'arabicfullname'=>'Organization Arabic Name',
        'shortname'=>'Organization Code',
        'discription'=>'Organization Description',
        'sector' => 'Sector',
        'segment' => 'Segment',
        'orgfieldofwork' => 'Field Of Work',
        'hrfullname' => 'HR Name',
        'hrjobrole' => 'HR Jobrole',
        'hremail' => 'HR Email',
        'hrmobile' => 'HR Mobile',
        'alfullname' => 'Alternative Name',
        'aljobrole' => 'Alternative Jobrole',
        'alemail' => 'Alternative Email',
        'almobile' => 'Alternative Mobile',
        'discount_percentage' => 'Discount Percentage',
    );
    switch ($format) {
        case 'csv' : organization_download_csv($fields);
    }
    die;
}
function organization_download_csv($fields) {
    global $CFG, $DB;
    require_once($CFG->libdir . '/csvlib.class.php');
    $filename = clean_filename(get_string('organizationupload','local_organization'));
    $csvexport = new csv_export_writer();
    $csvexport->set_filename($filename);
    $csvexport->add_data($fields);
    $userprofiledata = array('2564ff#4','548755454','Organization A', 'Arabicname', 'orga','Organization related description','B*I*F','BSA*IOS','Insurance','HRA','QA','hra@gmail.com',7456981236,'ABC','QA','abc@gmail.com',9874123547,0);
    $userprofiledata1 = array('39de8745','9881415','Organization B', 'Arabicname', 'orgb','Organization related description','B*I*F','BAS*IOS*KLM','Realestate','HRB','HR','hrb@gmail.com',8412365896,'ASD','Marketing','asd@gmail.com',6987456825,0);
    $userprofiledata2 = array('457we8d','21547754','Organization C', 'Arabicname', 'orgc','Organization related description','B*I*F','BSA*IOS','Investment Banking','HRC','Developer','hrc@gmail.com',8412365896,'ASD','Manager','asd@gmail.com',6987456825,0);

    $csvexport->add_data($userprofiledata);
    $csvexport->add_data($userprofiledata1);
    $csvexport->add_data($userprofiledata2);   
    $csvexport->download_file();
    die;
}
