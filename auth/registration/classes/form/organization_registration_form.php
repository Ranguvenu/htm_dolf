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

namespace auth_registration\form;

defined('MOODLE_INTERNAL') || die();

require_once($CFG->libdir . '/formslib.php');
use core_form\moodleform ;
use context;
use context_system;
use moodle_exception;
use moodle_url;
use auth_registration;
use auth_registration\action\registration as registration;
use auth_registration\action\manageuser as manageuser;
use render_from_template;

/**
 * Competency modal form
 *
 * @package    auth_registration
 * @copyright  2022 e abyas  <info@eabyas.com>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class organization_registration_form extends \moodleform {

    /**
     * Form definition
     */
    protected function definition() {
        global $CFG,$DB,$OUTPUT;

        $mform = $this->_form;
        $attr = $mform->getAttributes();
        $attr['enctype'] = "multipart/form-data";
        $mform->setAttributes($attr);
        $form_status = $this->_customdata['form_status'] ? $this->_customdata['form_status'] : 0 ;

        $licensekey = $this->_customdata['licensekey'] ? $this->_customdata['licensekey'] : 0 ;


        if($DB->record_exists('organization_draft',array('licensekey'=>$licensekey)) && !$DB->record_exists('local_organization',array('licensekey'=>$licensekey))) {
            $organisation=$DB->get_record('organization_draft',array('licensekey'=>$licensekey));
            $organisation->sectors = $organisation->orgsector;
            $organisation->segment = $organisation->orgsegment;
            $this->set_data($organisation);
        }

        $mform->addElement('hidden', 'form_status');
        $mform->setDefault('form_status',$form_status);
        $mform->setType('form_status', PARAM_INT);
        
        $org_registration_heading = '<div class="d-flex col-8 justify-content-center p-4 form-heading dynamic_form_id_orgdetails">
                  <span class="org_img mr-2"></span><h5>'.get_string('orgdetails', 'auth_registration').'</h5>
                  <span class="sub_head ml-2">'. get_string('compinst','auth_registration').'</span>
              </div>';
        $mform->addElement('html', $org_registration_heading);
        
        if($form_status == 0){
            $mform->addElement('html','<div class="row mt-5 p-4">');
            $mform->addElement('html','<div class="license_num col-md-3">'.get_string('licensenumber','auth_registration').'</div>');
            $mform->addElement('html','<div class="col-md-9">');
            $validate_input=array();

            $validate_input[] = $mform->createElement('text', 'licensekey','', array('placeholder'=>get_string('licensekey', 'auth_registration')));
            $validate_input[] = $mform->createElement('button', 'licensekeyvalidatebutton', get_string('validate', 'auth_registration'),['id'=>"licensekeyvalidate_btn"]);
            $mform->addGroup($validate_input, 'licensekey','', array('class' => 'm-0'), false);
            $mform->addElement('html',get_string('process','auth_registration'));
            $mform->addElement('html','</div>');
            $mform->addElement('html','</div>');
            

        }elseif ($form_status == 1){
            $mform->addElement('hidden', 'licensekey');
            $mform->setDefault('licensekey',$licensekey);
            $mform->setType('licensekey', PARAM_ALPHANUMEXT);
            $data = [
                'step1active'=>true,
                'step2active'=>false,
                'step3active'=>false,
                'step4active'=>false,
            ];

            $orgformtabs = $OUTPUT->render_from_template('auth_registration/orgform_tabs', $data);
            $mform->addElement('static', '', '',$orgformtabs );


            $mform->addElement('text', 'fullname','', array('placeholder'=>get_string('orgfullname', 'auth_registration')));
        
            $mform->setType('fullname', PARAM_NOTAGS);

            $mform->addElement('text', 'fullnameinarabic','',array('placeholder'=>get_string('fullnameinarabic', 'auth_registration')));
            $mform->setType('fullnameinarabic', PARAM_TEXT);


            $mform->addElement('text', 'shortname','',array('placeholder'=>get_string('orgshortname', 'auth_registration')));
        
            $mform->setType('shortname', PARAM_NOTAGS);
        

            $mform->addElement('textarea', 'description','',array('rows'=>4,'placeholder'=>get_string('orgdescription', 'auth_registration')));
            $mform->setType('description', PARAM_RAW);
            
            $sectors = registration::organization_sector();
            $mform->addElement('html','<div class="sector_options">');
            $sectoroptions = array(
            'multiple' => false,
            'class' => 'el_sectorlist',
            'id'=>'el_sectorlist',
            'noselectionstring' => get_string('sector', 'auth_registration'),
            'onchange' => "(function(e){ require(['local_trainingprogram/dynamic_dropdown_ajaxdata'], function(s) {s.sectorschanged();}) }) (event)",
            );
         
            $mform->addElement('autocomplete', 'sectors','',[null => get_string('sector', 'auth_registration')]+$sectors,$sectoroptions);

            $segments = array();

            if($organisation->segment) {
                 $segmentslist = $organisation->segment;
             } else {
                 $segmentslist = $this->_ajaxformdata['segment'];
             }

            if (!empty($segmentslist)) {
                  
                $segments = registration::organization_form_segment(0,$segmentslist);

            } 


            $segmentdattributes = array(
                'ajax' => 'local_trainingprogram/sector_datasource',
                'data-type' => 'segment',
                'id' => 'el_segmentlist',
                'data-sectorid' => '',
                'multiple' => true,
                'data-single_sector' => 1,
                'noselectionstring' => get_string('segment', 'local_trainingprogram'),
            );

            $fieldworkclassarray = array(
                 'class'=>'dynamic_form_job_family'
            );

            $fieldworks = registration::organization_fieldwork();
            
            $groupelemnts=array();
            $groupelemnts[] = $mform->createElement('autocomplete','segment','',$segments,$segmentdattributes);
            $groupelemnts[] = $mform->createElement('autocomplete','orgfieldofwork','',$fieldworks,$fieldworkclassarray);
            $mform->addGroup($groupelemnts, 'orgsegmentorgfieldofwork','', array('class' => 'orgsegmentorgfieldofwork'), false);

            $mform->addElement('html','</div>');
            $groupelemnts=array();
            $groupelemnts[] = $mform->createElement('html', \html_writer::tag('h6', get_string('hrcontactinfo', 'auth_registration'),array('class' => 'dynamic_form_id_hrcontactinfo')));
            $groupelemnts[] = $mform->createElement('html', \html_writer::tag('h6', get_string('alternativecontact', 'auth_registration'),array('class' => 'dynamic_form_id_altcontactinfo')));
            $mform->addGroup($groupelemnts, 'orgcontactinfo','', array('class' => 'orgcontactinfo'), false);


            $groupelemnts=array();
            $groupelemnts[] = $mform->createElement('text', 'hrfullname','', array('placeholder'=>get_string('hrfullname', 'auth_registration'),'class' => 'dynamic_form_id_hrfullname'));
            $groupelemnts[] = $mform->createElement('text', 'alfullname','', array('placeholder'=>get_string('altfullname', 'auth_registration'),'class' => 'dynamic_form_id_alfullname'));
            $mform->addGroup($groupelemnts, 'contactfullname','', array('class' => 'contactfullname'), false);


            $groupelemnts=array();
            $groupelemnts[] = $mform->createElement('text', 'hrjobrole','', array('placeholder'=>get_string('hrjobrole', 'auth_registration'),'class' => 'dynamic_form_id_hrjobrole'));
            $groupelemnts[] = $mform->createElement('text', 'aljobrole','', array('placeholder'=>get_string('altjobrole', 'auth_registration'),'class' => 'dynamic_form_id_aljobrole'));
            $mform->addGroup($groupelemnts, 'contactjobrole','', array('class' => 'contactjobrole'), false);


            $groupelemnts=array();
            $groupelemnts[] = $mform->createElement('text', 'hremail','', array('placeholder'=>get_string('hremail', 'auth_registration'),'class' => 'dynamic_form_id_hremail'));
            $groupelemnts[] = $mform->createElement('text', 'alemail','', array('placeholder'=>get_string('altemail', 'auth_registration'),'class' => 'dynamic_form_id_alemail'));
            $mform->addGroup($groupelemnts, 'contactemail','', array('class' => 'contactemail'), false);


            $groupelemnts=array();
            $groupelemnts[] = $mform->createElement('text', 'hrmobile','', array('placeholder'=>get_string('hrmobile', 'auth_registration'),'class' => 'dynamic_form_id_hrmobile'));
            $groupelemnts[] = $mform->createElement('text', 'almobile','', array('placeholder'=>get_string('altmobile', 'auth_registration'),'class' => 'dynamic_form_id_almobile'));
            $mform->addGroup($groupelemnts, 'contactmobile','', array('class' => 'contactmobile'), false);
            $mform->addElement('submit', 'submit', get_string('next','auth_registration'),['class'=>'btn-block my-4']);

        }elseif ($form_status == 2){

            $mform->addElement('hidden', 'licensekey');
            $mform->setDefault('licensekey',$licensekey);
            $mform->setType('licensekey', PARAM_ALPHANUMEXT);

            $data = [
                'step1active'=>false,
                'step2active'=>true,
                'step3active'=>false,
                'step4active'=>false,
            ];

            $orgformtabs = $OUTPUT->render_from_template('auth_registration/orgform_tabs', $data);
            $mform->addElement('static', '', '',$orgformtabs );

            
            $orgdetails = $DB->get_record('organization_draft',array('licensekey'=>$licensekey));
            $lang = current_language();
            if($lang == 'ar') {
                $title = 'seg.titlearabic';
            } else {
                $title = 'seg.title';
    
            }
            if( $orgdetails->orgsector &&  $orgdetails->orgsegment ){           
                $sectors = $DB->get_fieldset_sql("SELECT $title as title FROM {local_sector} as seg WHERE seg.id IN( $orgdetails->orgsector) ");
                $orgdetails->orgsector = implode(',',$sectors);
    
                $segment = $DB->get_fieldset_sql("SELECT $title as title FROM {local_segment} as seg WHERE seg.id IN( $orgdetails->orgsegment) ");
                $orgdetails->orgsegment = implode(',',$segment);
            }  else {   
    
                $orgdetails->orgsector = !empty($orgsector) ? $orgsector : "";
                $orgdetails->orgsegment = !empty($segment) ? $segment : "";
            
            }
            $orgformtabs = $OUTPUT->render_from_template('auth_registration/approvalletter',  $orgdetails);

            $mform->addElement('static', '', '',$orgformtabs );
            $groupelemnts=array();
            $mform->addElement('html','<div class="d-flex align-items-center flex-wrap bottom_buttons">');       
            $groupelemnts[] = $mform->createElement('button', 'downloadapprovalletter', get_string('downloadapprovalletter','auth_registration'),array('class'=>'download_approvalletter'));
            $mform->addGroup($groupelemnts, 'approvalletter_group','', array('class' => 'orgcontactinfo'), false);            
            $mform->addElement('submit', 'cancel', get_string('back','auth_registration'),['class'=>'my-2']);     
            $mform->addElement('submit', 'submit', get_string('next','auth_registration'),['class'=>'my-2']); 
            $mform->addElement('html','</div>');
 
        } elseif ($form_status == 3){

            $data = [
                'step1active'=>false,
                'step2active'=>false,
                'step3active'=>true,
                'step4active'=>false,
            ];
            $orgformtabs = $OUTPUT->render_from_template('auth_registration/orgform_tabs', $data);
            $mform->addElement('static', '', '',$orgformtabs );

            $mform->addElement('hidden', 'licensekey');
            $mform->setDefault('licensekey',$licensekey);
            $mform->setType('licensekey', PARAM_ALPHANUMEXT);

     
            $groupelemnts=array();
            $groupelemnts[] = $mform->createElement('file','approval_letter','', array('placeholder'=>get_string('approval_letter', 'auth_registration'),'class' => 'dynamic_form_id_approval_letter'));           
            $mform->addGroup($groupelemnts, 'approvalletter_group','', array('class' => 'orgcontactinfo'), false);
            $mform->addElement('submit', 'cancel', get_string('back','auth_registration'),['class'=>'btn-block my-2']);     
            $mform->addElement('submit', 'submit', get_string('next','auth_registration'),['class'=>'btn-block my-2']); 


        } elseif($form_status == 4){
            $data = [
                'step1active'=>false,
                'step2active'=>false,
                'step3active'=>false,
                'step4active'=>true,
            ];
            $orgformtabs = $OUTPUT->render_from_template('auth_registration/orgform_tabs',   $data);
            $mform->addElement('static', '', '',$orgformtabs );

        }


        $mform->disable_form_change_checker();
    }
    /**
     * Perform some moodle validation.
     * @param array $data
     * @param array $files
     * @return array
     */
    public function validation($data, $files) {

        global $DB;


        $errors = parent::validation($data, $files);

        $form_status = $data['form_status'];

        if($form_status == 0){// as these fields are in only form part 1(form_status=0)

            if(isset($data['licensekey']) &&empty(trim($data['licensekey']))){
                $errors['licensekey'] = get_string('vallicensekeyrequired','auth_registration');
            } else {
               // $validatelicense=registration::organization_license_validation($data['licensekey']);
               //  if(isset($validatelicense->code)){ 
               //      $errors['licensekey'] = $validatelicense->message;
               //  }
                
                if($DB->record_exists('local_organization',array('licensekey'=>$data['licensekey'],'status'=> 1))){
                   $errors['licensekey'] = get_string('licensekeyewaitingforapprovalerror','auth_registration');
                }

                if ($DB->record_exists('local_organization', array('licensekey' => trim($data['licensekey'])), '*', IGNORE_MULTIPLE)) {
                    $department = $DB->get_record('local_organization', array('licensekey' => trim($data['licensekey'])), '*', IGNORE_MULTIPLE);
                    $errors['licensekey'] = get_string('licensekeyexistserror', 'auth_registration', $department->fullname);
                    
                }
            }
        }elseif($form_status == 1){

            if(isset($data['fullname']) &&empty(trim($data['fullname']))){
                $errors['fullname'] = get_string('valfullnamerequired','auth_registration');
            }

            if(isset($data['fullnameinarabic']) &&empty(trim($data['fullnameinarabic']))){
                $errors['fullnameinarabic'] = get_string('valfullnameinarabicrequired','auth_registration');
            }

            

            if(isset($data['shortname']) &&empty(trim($data['shortname']))){
                $errors['shortname'] = get_string('valshortnamerequired','auth_registration');
            }else{
                // Add field validation check for duplicate code.
                if ($costcenter = $DB->get_record('local_organization', array('shortname' => $data['shortname']), '*', IGNORE_MULTIPLE)) {

                    if (empty($data['id']) || $costcenter->id != $data['id']) {
                        $errors['shortname'] = get_string('shortnametakenlp', 'auth_registration', $costcenter->fullname);
                    }
                }
            }

            if(isset($data['description']) &&empty(trim($data['description']))){
                $errors['description'] = get_string('descriptionrequired','auth_registration');
            }

            if(empty($data['sectors']) || $data['sectors'] == '_qf__force_multiselect_submission'){
                $errors['sectors'] = get_string('sectorrequired','auth_registration');
            }

           /* if(empty($data['segment']) || $data['sectors'] == '_qf__force_multiselect_submission'){
                $errors['orgsegmentorgfieldofwork'] = get_string('segmentrequired','auth_registration');
            }*/
            if(isset($data['orgfieldofwork']) && empty(trim($data['orgfieldofwork']))){
                $errors['orgsegmentorgfieldofwork'] = get_string('valorgfieldofworkrequired','auth_registration');
            }

            if(isset($data['hrfullname']) &&empty(trim($data['hrfullname']))){
                $errors['contactfullname'] = get_string('valhrfullnamerequired','auth_registration');
            }

             if(isset($data['alfullname']) &&empty(trim($data['alfullname']))){
                $errors['contactfullname'] = get_string('valalfullnamerequired','auth_registration');
            }

            if(isset($data['hrjobrole']) &&empty(trim($data['hrjobrole']))){
                $errors['contactjobrole'] = get_string('valhrjobrolerequired','auth_registration');
            }

            if(isset($data['aljobrole']) &&empty(trim($data['aljobrole']))){
                $errors['contactjobrole'] = get_string('valaljobrolerequired','auth_registration');
            }

            if (empty($data['alemail'])) {
                $errors['contactemail'] = get_string('alemailrequired', 'auth_registration');
            }
             if (!empty($data['alemail']) && !validate_email($data['alemail'])) {
                 $errors['contactemail'] = get_string('requiredvalidalemail', 'auth_registration');
             }

             if (empty($data['hremail'])) {
                $errors['contactemail'] = get_string('hremailrequired', 'auth_registration');
             }
             if (!empty($data['hremail']) && !validate_email($data['hremail'])) {
                 $errors['contactemail'] = get_string('requiredvalidhremail', 'auth_registration');
             }

            if (empty(trim($data['hrmobile'])))  {
              $errors['contactmobile'] = get_string('hrmobilerequired', 'auth_registration');
            }
            if (!empty(trim($data['hrmobile'])) && !is_numeric($data['hrmobile'])){
               $errors['contactmobile'] = get_string('hrmobilerequirednumeric','auth_registration');
            }
            if  (!empty(trim($data['hrmobile'])) && is_numeric(trim($data['hrmobile'])) && ( strlen(trim($data['hrmobile'])) < 5  || strlen(trim($data['hrmobile'])) > 10 )) {
                $errors['contactmobile'] = get_string('hrmobileminimum5digitsallowed','auth_registration');
            }
            if (!empty(trim($data['hrmobile'])) && is_numeric(trim($data['hrmobile'])) && (strlen(trim($data['hrmobile'])) >= 5  &&  strlen(trim($data['hrmobile'])) <= 10) &&  !preg_match('/^[5-9][0-9]/',trim($data['hrmobile']))) {
                $errors['contactmobile'] = get_string('hrmobilestartswith5','auth_registration');
            }

             if (empty(trim($data['almobile'])))  {
              $errors['contactmobile'] = get_string('almobilerequired', 'auth_registration');
            }
            if (!empty(trim($data['almobile'])) && !is_numeric($data['almobile'])){
               $errors['contactmobile'] = get_string('almobilerequirednumeric','auth_registration');
            }
            if  (!empty(trim($data['almobile'])) && is_numeric(trim($data['almobile'])) && ( strlen(trim($data['almobile'])) < 5  || strlen(trim($data['almobile'])) > 10 )) {
                $errors['contactmobile'] = get_string('almobileminimum5digitsallowed','auth_registration');
            }
            if (!empty(trim($data['almobile'])) && is_numeric(trim($data['almobile'])) && (strlen(trim($data['almobile'])) >= 5  &&  strlen(trim($data['almobile'])) <= 10) &&  !preg_match('/^[5-9][0-9]/',trim($data['almobile']))) {
                $errors['contactmobile'] = get_string('almobilestartswith5','auth_registration');
            }
        }
        elseif($form_status == 3   ){

            if(!empty($data['submit']) && empty($data['cancel']))

                if(empty($_REQUEST['files']['approval_letter']['name'])) {
                    $errors['approvalletter_group']= get_string('approvalletterrequired','auth_registration');
                }    

        }
        if(!empty($_REQUEST['files']['approval_letter']['name'])) {

            $file_name = $_REQUEST['files']['approval_letter']['name'];
            $file_size =$_REQUEST['files']['approval_letter']['size'];
            $file_tmp =$_REQUEST['files']['approval_letter']['tmp_name'];
            $file_type=$_REQUEST['files']['approval_letter']['type'];
            $file_ext=strtolower(end(explode('.',$_REQUEST['files']['approval_letter']['name'])));
            
            $extensions= array("pdf");
            
            if(in_array($file_ext,$extensions)=== false){
               $errors['approvalletter_group']="Extension not allowed, please choose a Downloaded approval letter file.";
            }  
          }
        return $errors;
    }
}
