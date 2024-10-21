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
 * @package    auth_registration
 * @copyright  2022 e abyas  <info@eabyas.com>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
namespace auth_registration\action;

use coding_exception;
use context_helper;
use context_system;
use core\invalid_persistent_exception;
use core\message\message;
use core_user;
use dml_exception;
use moodle_exception;
use moodle_url;
use required_capability_exception;
use stdClass;

defined('MOODLE_INTERNAL') || die();

/**
 * Class containing helper methods for processing data requests.
 *
 * @package    auth_registration
 * @copyright  2022 e abyas  <info@eabyas.com>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

require_once($CFG->libdir . '/pdflib.php');
require_once($CFG->libdir . '/filelib.php');

class registration {


   public static function organization_sector($jobroleid = 0)
    {

        global $DB, $USER;

        $lang = current_language();

        if($lang == 'ar') {

            $sectortitle = 'sect.titlearabic';

        } else {

            $sectortitle = 'sect.title';


        }


        if ($jobroleid) {

            $sector = $DB->get_record_sql('SELECT sect.id,$sectortitle as title
                                            FROM {local_sector} as sect 
                                            JOIN {local_segment} as seg ON seg.sectorid=sect.id 
                                            JOIN  {local_jobfamily} as jbfm ON jbfm.segmentid=seg.id 
                                            JOIN {local_jobrole_level} as jbrl ON jbrl.jobfamily=jbfm.id 
                                            WHERE jbrl.id=:jobroleid', ['jobroleid' => $jobroleid]);
        } else {

            $sector = $DB->get_records_sql_menu("SELECT sect.id,$sectortitle as title FROM {local_sector} as sect");

        }

        return $sector;
    }

    public static function organization_form_segment($jobroleid = 0, $segments = array())
    {

        global $DB, $USER;

        $segment = array();

        $lang = current_language();

        if($lang == 'ar') {

            $segmenttitle = 'seg.titlearabic';

        } else {

            $segmenttitle = 'seg.title';


        }

        if ($jobroleid) {

            $segment = $DB->get_record_sql('SELECT seg.id,$segmenttitle as title
                                                FROM {local_segment} as seg 
                                                JOIN  {local_jobfamily} as jbfm ON jbfm.segmentid=seg.id 
                                                JOIN {local_jobrole_level} as jbrl ON jbrl.jobfamily=jbfm.id 
                                                 WHERE jbrl.id=:jobroleid', ['jobroleid' => $jobroleid]);
        } 
        if(!empty($segments) AND $segments !='_qf__force_multiselect_submission'){

            $segments = is_array($segments) ? implode(',',$segments): $segments;
            $segment = $DB->get_records_sql_menu("SELECT seg.id,$segmenttitle as title FROM {local_segment} as seg WHERE seg.id IN($segments) ");


        }

        return $segment;
    }



    public static function organization_fieldwork() {

        $fieldwork = array();
        $fieldwork[null] = get_string('selectfieldwork','auth_registration');
        $fieldwork['investmentbanking'] = get_string('investmentbanking','auth_registration');
        $fieldwork['realestate'] = get_string('realestate','auth_registration');
        $fieldwork['insurance'] = get_string('insurance','auth_registration');
        $fieldwork['other'] = get_string('fieldworkother','auth_registration');
        
        return $fieldwork;
    }
    public static function organization_license_validation($licensekey) {

        $curl = curl_init();

        curl_setopt_array($curl, array(

          CURLOPT_URL => 'https://api.wathq.sa/v5/commercialregistration/info/'.$licensekey.'',

          CURLOPT_RETURNTRANSFER => true,

          CURLOPT_ENCODING => '',

          CURLOPT_MAXREDIRS => 10,

          CURLOPT_TIMEOUT => 0,

          CURLOPT_FOLLOWLOCATION => true,

          CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,

          CURLOPT_CUSTOMREQUEST => 'POST',

          CURLOPT_HTTPHEADER => array(

            'apikey: WZceYjZByDYGcyuSd5KTsFH3QGnpVIj1'

          ),

        ));
        $response = curl_exec($curl);

        curl_close($curl);


        return json_decode($response);

    }

    public static function organization_registration($formdata) {

        global $DB, $USER;

        try {
            $formdata->timecreated=time();
            $formdata->usercreated=time();
            $formdata->status=1;  
            $id = $DB->insert_record('local_organization', $formdata);
            $formdata->id = $id; 

           (new \local_organization\notification)->organization_notification('organization_registration', $touser = null,$fromuser= get_admin(), $formdata,$waitinglistid=0,$formdata);

          //  (new \local_organization\notification)->organization_notification('organization_registration', $touser = null,$fromuser= get_admin(), $formdata,$waitinglistid=0,$formdata);
        } catch (dml_exception $e) {
            print_error($e);
        }
        return true;
    }
    public static function approval_letter_store($approvalletterfile) {

        global $DB, $USER,$CFG;
// print_r($approvalletterfile);exit;
        // Check if backup directory exists,if not create it.
        check_dir_exists($CFG->tempdir . '/approvalletterupload');

        // Where the file is going to be stored
        $target_dir = $CFG->tempdir.DIRECTORY_SEPARATOR."approvalletterupload".DIRECTORY_SEPARATOR;
        // $file = $approvalletterfile['approval_letter']['name'].'.pdf';
        // $path = pathinfo($file);
        $filename = $approvalletterfile['approval_letter']['name'];
        // $ext = $path['extension'];
        $temp_name = $approvalletterfile['approval_letter']['tmp_name'];

        $path_filename_ext = $target_dir.$filename; 
             
        // Check if file already exists


        if(move_uploaded_file($temp_name, $path_filename_ext)){
            //echo "Successfull";     
        }
          
        $content = file_get_contents($path_filename_ext);  


        $systemcontext = \context_system::instance();

        $record = [];

        $record['contextid'] = $systemcontext->id;
        $record['component'] = 'auth_registration';

        $record['filearea'] = 'approval_letter';

        $params = [
                    'component' => $record['component'],
                    'filearea' => $record['filearea'],
                    'contextid' => $record['contextid'],
                    'filename' => '.',
                ];

        $contextid = $record['contextid'];

        $fs = get_file_storage();
        $draftitemid = rand(1, 999999999);
        while ($files = $fs->get_area_files($contextid, 'user', 'draft', $draftitemid)) {
            $draftitemid = rand(1, 999999999);
        }

        $record['itemid'] = $draftitemid;

        if (!isset($record['filepath'])) {
            $record['filepath'] = '/';
        }

        if (!isset($record['filename'])) {
            $record['filename'] = $filename ;
        }

        // if (!isset($record['userid'])) {
        //     $record['userid'] = $USER->id;
        // }

        // if (!isset($record['filecontent'])) {
        //     print_error('file is missing');
        // }

        $fs = get_file_storage();

        $file=$fs->create_file_from_string($record, $content);

        $save_file_loc = $path_filename_ext;

        if (file_exists($save_file_loc)) {
            unlink($save_file_loc);
        }
            
        return $record['itemid'];
    }
    public static function generate_organization_approvalletter($formdata) {

        global $CFG,$PAGE,$DB,$OUTPUT;  
        $doc = new APPROVALLETTERPDF('L', PDF_UNIT, PDF_PAGE_FORMAT, true, 'UTF-8', false);

        $doc->setPrintHeader(true);
        $doc->setPrintFooter(true);

        // set default header data
        $doc->SetHeaderData(PDF_HEADER_LOGO, PDF_HEADER_LOGO_WIDTH, PDF_HEADER_TITLE . ' 059', PDF_HEADER_STRING);

        // set auto page breaks
        $doc->SetAutoPageBreak(TRUE, PDF_MARGIN_BOTTOM);

        // set image scale factor
        $doc->setImageScale(PDF_IMAGE_SCALE_RATIO);

        // add a page
        $doc->AddPage();

        
        $sectors = is_array($formdata->sectors) ? implode(',',$formdata->sectors): $formdata->sectors;
        $lang = current_language();
        if($lang == 'ar') {
            $title = 'seg.titlearabic';
        } else {
            $title = 'seg.title';

        }
        $sectors = $DB->get_fieldset_sql("SELECT $title as title FROM {local_sector} as seg WHERE seg.id IN($sectors) ");

        $orgsector = implode(',',$sectors);
       

        $segment = is_array($formdata->sectors) ? implode(',',$formdata->segment): $formdata->segment;
        $lang = current_language();
        if($lang == 'ar') {
            $title = 'seg.titlearabic';
        } else {
            $title = 'seg.title';

        }
        $segment = $DB->get_fieldset_sql("SELECT $title as title FROM {local_segment} as seg WHERE seg.id IN($segment) ");

        $segment = implode(',',$segment);
        

        // $orgsector= self::organization_sector();

        // $segment= self::organization_form_segment(array(),$formdata->segment);


        $fieldwork= self::organization_fieldwork();


        //$header =self::get_approvalletterheader_imagepath();
        $header ='<img src="pdflogo/Dolf-_Jpeg.jpeg" alt="Site Logo">';

        $doc->writeHTMLCell($w = 0, $h = 5, $x = '5', $y = '5', $header, $border = 0, $ln = 1, $fill = 0, $reseth = true, $align = '', $autopadding = true);


        $approvalletterdatatopdf= self::gethtmlapprovalletterdatatopdf($formdata,$orgsector,$segment,$fieldwork);
        

        $doc->writeHTML($approvalletterdatatopdf, true, false, true, false, '');

        $doc->Output(get_string('approval_letter', 'auth_registration').time().'.pdf');

    
    }
    public static function gethtmlapprovalletterdatatopdf($formdata,$orgsector,$segment,$fieldwork){
        global $CFG,$PAGE,$DB,$OUTPUT;  
        $return = '';

        ob_start();

        $return.='<html>
                    <body style="margin: 0;">
               
                             <table>
                                <tr>
                                    <td style="width:50%;background-color:#064d97;"></td>
                                    <td style="width:50%;"></td>
                                </tr>
                             </table>    

                            <h1 style="color:#064d97;text-align:center;">'.get_string('approval_letter', 'auth_registration').'</h1>
                            <table style="width:100%;border-collapse:collapse;border-bottom:1px solid #a9a9a9;padding:12px 5px;">
                                <tr>
                                    <td style="width:20%;border-bottom:1px solid #a9a9a9;padding:12px;">'.get_string('pdfletterorgfullname', 'auth_registration').'</td>
                                    <td style="width:10%;border-bottom:1px solid #a9a9a9;padding:12px;">:</td>
                                    <td style="width:69%;border-bottom:1px solid #a9a9a9;padding:12px;">'.$formdata->fullname.'</td>
                                </tr>
                                <tr>
                                    <td style="width:20%;border-bottom:1px solid #a9a9a9;padding:12px;">'.get_string('pdfletterorgfullnameinarabic', 'auth_registration').'</td>
                                    <td style="width:10%;border-bottom:1px solid #a9a9a9;padding:12px;">:</td>
                                    <td style="width:69%;border-bottom:1px solid #a9a9a9;padding:12px;">'.$formdata->fullnameinarabic.'</td>
                                </tr>
                                <tr>
                                    <td style="width:20%;border-bottom:1px solid #a9a9a9;padding:12px;">'.get_string('pdfletterorgshortname', 'auth_registration').'</td>
                                    <td style="width:10%;border-bottom:1px solid #a9a9a9;padding:12px;">:</td>
                                    <td style="width:69%;border-bottom:1px solid #a9a9a9;padding:12px;">'.$formdata->shortname.'</td>
                                </tr>
                                <tr>
                                    <td style="width:20%;border-bottom:1px solid #a9a9a9;padding:12px;">'.get_string('orgdescription', 'auth_registration').'</td>
                                    <td style="width:10%;border-bottom:1px solid #a9a9a9;padding:12px;">:</td>
                                    <td style="width:69%;border-bottom:1px solid #a9a9a9;padding:12px;">'.$formdata->description.'</td>
                                </tr>
                                <tr>
                                    <td style="width:20%;border-bottom:1px solid #a9a9a9;padding:12px;">'.get_string('sector', 'auth_registration').'</td>
                                    <td style="width:10%;border-bottom:1px solid #a9a9a9;padding:12px;">:</td>
                                    <td style="width:69%;border-bottom:1px solid #a9a9a9;padding:12px;">'.$orgsector.'</td>
                                </tr>
                                <tr>
                                    <td style="width:20%;border-bottom:1px solid #a9a9a9;padding:12px;">'.get_string('segment', 'auth_registration').'</td>
                                    <td style="width:10%;border-bottom:1px solid #a9a9a9;padding:12px;">:</td>
                                    <td style="width:69%;border-bottom:1px solid #a9a9a9;padding:12px;">'.$segment.'</td>
                                </tr>
                                <tr>
                                    <td style="width:20%;border-bottom:1px solid #a9a9a9;padding:12px;">'.get_string('fieldwork', 'auth_registration').'</td>
                                    <td style="width:10%;border-bottom:1px solid #a9a9a9;padding:12px;">:</td>
                                    <td style="width:69%;border-bottom:1px solid #a9a9a9;padding:12px;">'.$fieldwork[$formdata->orgfieldofwork].'</td>
                                </tr>
                            </table>
                            <table style="height:150px;width:100%;padding:12px 5px;">
                                <tr>
                                    <td style="height:150px;width:50%;"></td>
                                    <td style="height:150px;width:50%;"></td>
                                </tr>
                             </table>  

                            <h2 style="color:#064d97;text-align:center;">'.get_string('hrcontactinfo', 'auth_registration').'</h2>
                            <h3 style="color:#064d97;border-left:2px solid #064d97;padding-left: 8px;">'.get_string('hrmanager', 'auth_registration').'</h3>
                            <table style="width:100%;border-collapse:collapse;border-bottom:1px solid #a9a9a9;padding:12px 5px;">
                                <tr>
                                    <td style="width:20%;border-bottom:1px solid #a9a9a9;padding:12px;">'.get_string('hrfullname', 'auth_registration').'</td>
                                    <td style="width:10%;border-bottom:1px solid #a9a9a9;padding:12px;">:</td>
                                    <td style="width:69%;border-bottom:1px solid #a9a9a9;padding:12px;">'.$formdata->hrfullname.'</td>
                                </tr>
                                <tr>
                                    <td style="width:20%;border-bottom:1px solid #a9a9a9;padding:12px;">'.get_string('hrjobrole', 'auth_registration').'</td>
                                    <td style="width:10%;border-bottom:1px solid #a9a9a9;padding:12px;">:</td>
                                    <td style="width:69%;border-bottom:1px solid #a9a9a9;padding:12px;">'.$formdata->hrjobrole.'</td>
                                </tr>
                                <tr>
                                    <td style="width:20%;border-bottom:1px solid #a9a9a9;padding:12px;">'.get_string('hremail', 'auth_registration').'</td>
                                    <td style="width:10%;border-bottom:1px solid #a9a9a9;padding:12px;">:</td>
                                    <td style="width:69%;border-bottom:1px solid #a9a9a9;padding:12px;">'.$formdata->hremail.'</td>
                                </tr>
                                <tr>
                                    <td style="width:20%;border-bottom:1px solid #a9a9a9;padding:12px;">'.get_string('hrmobile', 'auth_registration').'</td>
                                    <td style="width:10%;border-bottom:1px solid #a9a9a9;padding:12px;">:</td>
                                    <td style="width:69%;border-bottom:1px solid #a9a9a9;padding:12px;">'.$formdata->hrmobile.'</td>
                                </tr>
                            </table>
                            <h3 style="color:#064d97;border-left:2px solid #064d97; padding-left: 8px;">'.get_string('alternativecontact', 'auth_registration').'</h3>
                            <table style="width:100%;border-collapse:collapse;border-bottom:1px solid #a9a9a9;padding:12px 5px;">
                                <tr>
                                    <td style="width:20%;border-bottom:1px solid #a9a9a9;padding:12px;">'.get_string('altfullname', 'auth_registration').'</td>
                                    <td style="width:10%;border-bottom:1px solid #a9a9a9;padding:12px;">:</td>
                                    <td style="width:69%;border-bottom:1px solid #a9a9a9;padding:12px;">'.$formdata->alfullname.'</td>
                                </tr>
                                <tr>
                                    <td style="width:20%;border-bottom:1px solid #a9a9a9;padding:12px;">'.get_string('altjobrole', 'auth_registration').'</td>
                                    <td style="width:10%;border-bottom:1px solid #a9a9a9;padding:12px;">:</td>
                                    <td style="width:69%;border-bottom:1px solid #a9a9a9;padding:12px;">'.$formdata->aljobrole.'</td>
                                </tr>
                                <tr>
                                    <td style="width:20%;border-bottom:1px solid #a9a9a9;padding:12px;">'.get_string('altemail', 'auth_registration').'</td>
                                    <td style="width:10%;border-bottom:1px solid #a9a9a9;padding:12px;">:</td>
                                    <td style="width:69%;border-bottom:1px solid #a9a9a9;padding:12px;">'.$formdata->alemail.'</td>
                                </tr>
                                <tr>
                                    <td style="width:20%;border-bottom:1px solid #a9a9a9;padding:12px;">'.get_string('altmobile', 'auth_registration').'</td>
                                    <td style="width:10%;border-bottom:1px solid #a9a9a9;padding:12px;">:</td>
                                    <td style="width:69%;border-bottom:1px solid #a9a9a9;padding:12px;">'.$formdata->almobile.'</td>
                                </tr>
                            </table>
                            </br>
                            
                            <h4>Thanking You</h4>
                            </br>
                            </br></br>
                            <p><small>Authorized Signature</small></p>
                            <table>
                                <tr>
                                    <td style="width:50%;"></td>
                                    <td style="width:50%;background-color:#064d97;"></td>
                                </tr>
                            </table>   
    
                    </body>
                </html>';

       ob_get_clean();

        return $return;

    }
    /**
     * [get_reportheader_imagepath description]
     * @param  boolean $excel [description]
     * @return [type]         [description]
     */
    public static function get_approvalletterheader_imagepath() {

        global $CFG,$PAGE;

        $approvalletterheaderimagepath = '';
  
        if($PAGE->theme->setting_file_url('logo', 'logo')) {


            if (empty($PAGE->theme->settings->logo)) {
                return $approvalletterheaderimagepath;
            }
            $component = 'theme_'.$PAGE->theme->name;
            $itemid = theme_get_revision();
            $filepath = $PAGE->theme->settings->logo;
            $syscontext = \context_system::instance();

            $url = moodle_url::make_file_url("$CFG->wwwroot/pluginfile.php", "/$syscontext->id/$component/logo/$itemid".$filepath);

            $approvalletterheaderimagepath.=  '<img src="' . $url->out(false) . '" alt=' . get_string("altapprovalletterimage", "auth_registration") . ' height="80px">';


        }
        return $approvalletterheaderimagepath;
    }
    
}
/* declaring APPROVALLETTERPDF for custom Header and Footer */

class APPROVALLETTERPDF extends \TCPDF {

    // Page header
    public function Header() {
        global $DB, $CFG;

        $this->SetY(100);

        $this->SetFont('helvetica', 'B', 15);
    }

    // Page footer
    public function Footer() {
        global $DB, $CFG;
        // Position at 15 mm from bottom
        // Set font
        $this->SetFont('helvetica', 'I', 10);

        $this->Cell(0, 10, 'Page ' . $this->getAliasNumPage() . '/' . $this->getAliasNbPages(), 0, false, 'C', 0, '', 0, false, 'T', 'M');
        $this->SetY(-15);
    }
}
