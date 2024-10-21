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
namespace local_organization\form;

use core_form\dynamic_form;
use moodle_url;
use context;
use context_system;
use local_organization;
use local_organization\organization as organization;


/**
 * TODO describe file organization_form
 *
 * @package    local_organization
 * @copyright  2023 YOUR NAME <your@email.com>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class organization_form extends dynamic_form { 

    public function definition() {
        global $USER, $CFG,$DB;
        $corecomponent = new \core_component();

        $mform = $this->_form;
        $id = $this->optional_param('id', 0, PARAM_INT);
 
        $parentid = $this->_customdata['parentid']; 
        $formtype = $this->_customdata['formtype'];
        $headstring = $this->_customdata['headstring'];
        $systemcontext = context_system::instance();


        $mform->addElement('hidden', 'id', $id);
        $mform->setType('id', PARAM_INT);


        $mform->addElement('text', 'licensekey', get_string('licensekey', 'local_organization'), array());
        $mform->setType('licensekey', PARAM_TEXT);
        $mform->addRule('licensekey', get_string('missinglicensekey', 'local_organization'), 'required', null);

        $mform->addElement('text', 'fullname', get_string('organizationname', 'local_organization'), array());
        $mform->setType('fullname', PARAM_TEXT);
        $mform->addRule('fullname', get_string('missingorganizationname', 'local_organization'), 'required', null);


        $mform->addElement('text', 'fullnameinarabic', get_string('fullnameinarabic', 'local_organization'), array());
        $mform->setType('fullnameinarabic', PARAM_TEXT);
        $mform->addRule('fullnameinarabic', get_string('missingorganizationfullnameinarabic', 'local_organization'), 'required', null);

        
        $mform->addElement('text', 'shortname', get_string('shortname','local_organization'), 'maxlength="100" size="20"');
        $mform->addRule('shortname', get_string('shortnamecannotbeempty', 'local_organization'), 'required', null);
        $mform->setType('shortname', PARAM_TEXT);

        $mform->addElement('editor', 'description', get_string('description','local_organization'));
        $mform->addRule('description', get_string('descriptioncannotbeempty', 'local_organization'), 'required', null);
        $mform->setType('description', PARAM_TEXT);


        $sectors = organization::organization_sector();

        $sectoroptions = array(
        'multiple' => true,
        'class' => 'el_sectorlist',
        'id'=>'el_sectorlist',
         'onchange' => "(function(e){ require(['local_trainingprogram/dynamic_dropdown_ajaxdata'], function(s) {s.sectorschanged();}) }) (event)",
         'noselectionstring' => get_string('noselection', 'local_trainingprogram'),
        );

        $mform->addElement('autocomplete', 'sectors',get_string('sector','local_organization'),$sectors,$sectoroptions);
        $mform->addRule('sectors', get_string('missingsectors', 'local_exams'), 'required', null);

        $segments = array();
         if ($id = $this->optional_param('id', 0, PARAM_INT)) {
            $data = $DB->get_record('local_organization', ['id' => $id], '*', MUST_EXIST);;
            $data->sectors = $data->orgsector;
            $data->segment = $data->orgsegment;
            $this->set_data($data);
           if($data->segment)  {
               $segmentslist = $data->segment;
            } else {
                $segmentslist = $this->_ajaxformdata['segment']; 
            }
           
        } else {

            $segmentslist = $this->_ajaxformdata['segment']; 
        }


       if (!empty($segmentslist)) {

            $segmentslist =$segmentslist;
                  
            $segments = organization::organization_form_segment(0,$segmentslist);

        } 
        $segmentdattributes = array(
            'ajax' => 'local_trainingprogram/sector_datasource',
            'data-type' => 'segment',
            'id' => 'el_segmentlist',
            'data-sectorid' => '',
            'multiple' => true,
        );
        $mform->addElement('autocomplete', 'segment',get_string('segment','local_organization'),$segments,$segmentdattributes);
      //  $mform->addRule('segment', get_string('valorgsegmentrequired', 'local_organization'), 'required', null);

        $fieldwork = array();
        $fieldwork[null] = get_string('selectfieldwork','local_organization');
        $fieldwork['investmentbanking'] = get_string('investmentbanking','local_organization');
        $fieldwork['realestate'] = get_string('realestate','local_organization');
        $fieldwork['insurance'] = get_string('insurance','local_organization');
        $fieldwork['other'] = get_string('fieldworkother','local_organization');   
         
        $select = $mform->addElement('select', 'orgfieldofwork', get_string('Fieldofwork','local_organization'), $fieldwork , $attributes);
        $mform->addRule('orgfieldofwork', get_string('orgfieldofworkcannotbeempty', 'local_organization'), 'required', null);
        $mform->setType('orgfieldofwork', PARAM_TEXT);

       // renu... Enable organization Official to modify the field of work when selecting “Other”. Two fields should appea
        
        $mform->addElement('text', 'orgfieldofworken', get_string('orgfieldofworken','local_organization'));
        $mform->setType('orgfieldofworken',PARAM_RAW);
        $mform->hideIf('orgfieldofworken','orgfieldofwork', 'neq', 'other');

        $mform->addElement('text', 'orgfieldofworkab', get_string('orgfieldofworkab','local_organization'));
        $mform->setType('orgfieldofworkab',PARAM_RAW);
        $mform->hideIf('orgfieldofworkab','orgfieldofwork', 'neq', 'other');

        $mform->addElement('html', '<p class="hrinfo-lebel">'.get_string('HRManager','local_organization').'</p>');
        $mform->addElement('text', 'hrfullname', get_string('hrfullname','local_organization'), 'maxlength="100" size="20"');
        
        $mform->addRule('hrfullname', get_string('hrfullnamecannotbeempty', 'local_organization'), 'required', null);
            
        $mform->setType('hrfullname', PARAM_TEXT);

        $mform->addElement('text', 'hrjobrole', get_string('hrjobrole','local_organization'), 'maxlength="100" size="20"');
        
        $mform->addRule('hrjobrole', get_string('hrjobrolecannotbeempty', 'local_organization'), 'required', null);
        $mform->setType('hrjobrole', PARAM_TEXT);
        $mform->addElement('text', 'hremail', get_string('hremail','local_organization'));
        
        $mform->addRule('hremail', get_string('hremailcannotbeempty', 'local_organization'), 'required', null);
        $mform->setType('hremail', PARAM_TEXT);

        $mform->addElement('text', 'hrmobile', get_string('hrmobile','local_organization'));
        $mform->addRule('hrmobile', get_string('hrmobilecannotbeempty', 'local_organization'), 'required', null);
        $mform->setType('hrmobile', PARAM_TEXT);


        $mform->addElement('html', '<p class="hrinfo-lebel">'.get_string('AlternativeContact','local_organization').'</h3>');
         $mform->addElement('text', 'alfullname', get_string('alfullname','local_organization'), 'maxlength="100" size="20"');
        $mform->addRule('alfullname', get_string('alfullnamecannotbeempty', 'local_organization'), 'required', null);
        $mform->setType('alfullname', PARAM_TEXT);

        $mform->addElement('text', 'aljobrole', get_string('aljobrole','local_organization'), 'maxlength="100" size="20"');
        $mform->addRule('aljobrole', get_string('aljobrolecannotbeempty', 'local_organization'), 'required', null);
        $mform->setType('aljobrole', PARAM_TEXT);

        $mform->addElement('text', 'alemail', get_string('alemail','local_organization'));
        $mform->addRule('alemail', get_string('alemailcannotbeempty', 'local_organization'), 'required', null);
        $mform->setType('alemail', PARAM_TEXT);

        $mform->addElement('text', 'almobile', get_string('almobile','local_organization'));
        $mform->addRule('almobile', get_string('almobilecannotbeempty', 'local_organization'), 'required', null);
        $mform->setType('almobile', PARAM_TEXT);

        

        if(is_siteadmin()) {

            $language = current_language();
            if($language == 'en')
            {
                $types = $DB->get_records_sql_menu("SELECT id, name FROM {local_org_partnertypes} ");
            }

            if($language == 'ar')
            {
                $types = $DB->get_records_sql_menu("SELECT id, arabicname FROM {local_org_partnertypes} ");
            }
           
            $mform->addElement('select', 'partnertype', get_string('partnertype','local_organization'), array(null=>get_string('selecttype','local_exams')) + $types);
            $mform->setType('partnertype', PARAM_TEXT);

            $mform->addElement('checkbox', 'partner', get_string('orgpartner', 'local_organization'),get_string('orgpartner', 'local_organization'),null,[0,1]);
            $mform->setType('partner', PARAM_BOOL);

            $mform->addElement('text', 'orgrank', get_string('rank','local_organization'));
            $mform->setType('orgrank', PARAM_TEXT);


        }

        

        $filemanageroptions = array(
            'accepted_types' => array(get_string('png_format', 'local_organization'), 
                get_string('jpg_format', 'local_organization'),get_string('jpg_format', 'local_organization')),
            'maxbytes' => 0,
            'maxfiles' => 1,
        );

        $mform->addElement('filepicker', 'orglogo', get_string('orglogo', 'local_organization'), '', $filemanageroptions);

        // ******************** DL-397 ***************************
        $mform->addElement('text', 'tax_number', get_string('tax_number','local_organization'));
        $mform->addRule('tax_number', get_string('numeric', 'local_organization'), 'numeric',null, 'client');
        $mform->setType('tax_number', PARAM_INT);

        $certificate_format = array(
            'accepted_types' => array(get_string('png_format', 'local_organization'), 
                get_string('jpg_format', 'local_organization'),get_string('pdf_format', 'local_organization')),
            'maxbytes' => 0,
            'maxfiles' => 1,
        );
        $mform->addElement('filepicker', 'tax_certificate', get_string('tax_certificate', 'local_organization'), '', $certificate_format);
        // ******************** END DL-397 ***************************

        if(is_siteadmin()) {

            $mform->addElement('checkbox', 'autoapproval', get_string('autoapproval', 'local_organization'),get_string('autoapproval', 'local_organization'),null,[0,1]);
            $mform->setType('autoapproval', PARAM_INT);
            $mform->setDefault('autoapproval', 1);

        }

    }

    /**
     * validates organization name and returns instance of this object
     *
     * @param [object] $data 
     * @param [object] $files 
     * @return organization validation errors
     */
     public function validation($data, $files) {
        global $COURSE, $DB, $CFG;
        $errors = parent::validation($data, $files);

        if ($DB->record_exists('local_organization', array('fullname' => trim($data['fullname'])), '*', IGNORE_MULTIPLE)) {
            $department = $DB->get_record('local_organization', array('fullname' => trim($data['fullname'])), '*', IGNORE_MULTIPLE);
            if (empty($data['id']) || $department->id != $data['id']) {
                $errors['fullname'] = get_string('fullnametakenlp', 'local_organization', $department->fullname);
            }
        }

        if ($DB->record_exists('local_organization', array('fullnameinarabic' => trim($data['fullnameinarabic'])), '*', IGNORE_MULTIPLE)) {
            $department = $DB->get_record('local_organization', array('fullnameinarabic' => trim($data['fullnameinarabic'])), '*', IGNORE_MULTIPLE);
            if (empty($data['id']) || $department->id != $data['id']) {
                $errors['fullnameinarabic'] = get_string('fullnameinarabictakenlp', 'local_organization', $department->fullnameinarabic);
            }
        }


        if ($DB->record_exists('local_organization', array('shortname' => trim($data['shortname'])), '*', IGNORE_MULTIPLE)) {
            $department = $DB->get_record('local_organization', array('shortname' => trim($data['shortname'])), '*', IGNORE_MULTIPLE);
            if (empty($data['id']) || $department->id != $data['id']) {
                $errors['shortname'] = get_string('shortnametakenlp', 'local_organization', $department->shortname);
            }
        }

        // if(isset($data['sectors']) &&empty(trim($data['sectors']))){
        //     $errors['sectors'] = get_string('valorgsectorrequired','local_organization');
        // }

        // if(isset($data['segment']) &&empty(trim($data['segment']))){
        //     $errors['segment'] = get_string('valorgsegmentrequired','local_organization');
        // }

        

        if(empty($data['sectors'])){
            $errors['sectors'] = get_string('valorgsectorrequired','local_organization');
        }
       /* if(empty($data['segment'])){
            $errors['segment'] = get_string('valorgsegmentrequired','local_organization');
        }*/

        if(isset($data['orgfieldofwork']) &&empty(trim($data['orgfieldofwork']))){
            $errors['orgsegmentorgfieldofwork'] = get_string('valorgfieldofworkrequired','local_organization');
        }
         if (empty($data['alemail'])) {
            $errors['alemail'] = get_string('emailrequired', 'local_organization');
         }
         if (!empty($data['alemail']) && !validate_email($data['alemail'])) {
             $errors['alemail'] = get_string('requiredvalidemail', 'local_organization');
         }

         if (empty($data['hremail'])) {
            $errors['hremail'] = get_string('emailrequired', 'local_organization');
         }
         if (!empty($data['hremail']) && !validate_email($data['hremail'])) {
             $errors['hremail'] = get_string('requiredvalidemail', 'local_organization');
         }

        if (empty($data['hrmobile']))  {
            $errors['hrmobile'] = get_string('hrmobilerequired', 'local_organization');
        }

        if (!empty(trim($data['hrmobile'])) && !is_numeric($data['hrmobile'])){
           $errors['hrmobile'] = get_string('hrmobilerequirednumeric','local_organization');
        }
        if  (!empty(trim($data['hrmobile'])) && is_numeric(trim($data['hrmobile'])) && ( strlen(trim($data['hrmobile'])) < 5  || strlen(trim($data['hrmobile'])) > 12 )) {
            $errors['hrmobile'] = get_string('hrmobileminimum5digitsallowed','local_organization');
        }
        if (!empty(trim($data['hrmobile'])) && is_numeric(trim($data['hrmobile'])) && (strlen(trim($data['hrmobile'])) >= 5  &&  strlen(trim($data['hrmobile'])) <= 12) &&  !preg_match('/^[5-9][0-9]/',trim($data['hrmobile']))) {
            $errors['hrmobile'] = get_string('hrmobilestartswith5','local_organization');
        }
        if (empty($data['almobile']))  {
            $errors['almobile'] = get_string('almobilemobilerequired', 'local_organization');
        }
        if (!empty(trim($data['almobile'])) && !is_numeric($data['almobile'])){
           $errors['almobile'] = get_string('almobilerequirednumeric','local_organization');
        }
        if  (!empty(trim($data['almobile'])) && is_numeric(trim($data['almobile'])) && ( strlen(trim($data['almobile'])) < 5  || strlen(trim($data['almobile'])) > 12 )) {
            $errors['almobile'] = get_string('almobileminimum5digitsallowed','local_organization');
        }
        if (!empty(trim($data['almobile'])) && is_numeric(trim($data['almobile'])) && (strlen(trim($data['almobile'])) >= 5  &&  strlen(trim($data['almobile'])) <= 12) &&  !preg_match('/^[5-9][0-9]/',trim($data['almobile']))) {
            $errors['almobile'] = get_string('almobilestartswith5','local_organization');
        }

        /*if  (!empty(trim($data['licensekey'])) && is_numeric(trim($data['licensekey'])) && ( strlen(trim($data['licensekey'])) < 10)) {
            $errors['licensekey'] = get_string('licensekey10digitsallowed','local_organization');
        }*/
        if(isset($data['licensekey']) && !empty(trim($data['licensekey']))){
            if(!empty($data['licensekey']) && !preg_match('/^[0-9]+$/',trim($data['licensekey']))){
                $errors['licensekey'] = get_string('vallicensekeyrequired','auth_registration');
            }
            if(!empty($data['licensekey']) && preg_match('/^[0-9]+$/',trim($data['licensekey'])) && strlen(trim($data['licensekey'])) != 10){
                $errors['licensekey'] = get_string('lengthcanbe10','auth_registration');
            }
            
            if ($DB->record_exists('local_organization', array('licensekey' => trim($data['licensekey'])), '*', IGNORE_MULTIPLE)) {
                $department = $DB->get_record('local_organization', array('licensekey' => trim($data['licensekey'])), '*', IGNORE_MULTIPLE);
                if (empty($data['id']) || $department->id != $data['id']) {
                    $errors['licensekey'] = get_string('licensekeyexistserror', 'local_organization', $department->fullname);
                }
            }

            $validatelicense=organization::organization_license_validation($data['licensekey']);
            if(isset($validatelicense->code)){ 
                $errors['licensekey'] = $validatelicense->message;
            }
        }

        if (!empty($data['orgrank'])) {
            if (!is_numeric($data['orgrank'])) {
                $errors['orgrank'] = get_string('rankshouldbenumeric', 'local_organization');   
            } /*else {
                $ranks = $DB->get_fieldset_sql("SELECT orgrank FROM {local_organization}");
                if (in_array($data['orgrank'], $ranks)) 

                {
                    $errors['orgrank'] = get_string('rankfound', 'local_organization');
                }
            }*/

            $rank = trim($data['orgrank']);
            $rankmapped = $DB->record_exists('local_organization',array('orgrank' =>$rank));
                if ($rankmapped) 
                {
                    $rankdata = $DB->get_record('local_organization', array('orgrank'=>$rank));
                    if ($rankdata->id != $data['id']) {
                   $errors['orgrank'] = get_string('rankfound', 'local_organization');
                }
            }
        }
         if($data['orgfieldofwork']=='other'){
            if(empty($data['orgfieldofworken'])){
                $errors['orgfieldofworken'] = get_string('orgfieldofworkenerror','local_organization');
            }

            if(empty($data['orgfieldofworkab'])){
                $errors['orgfieldofworkab'] = get_string('orgfieldofworkaberror','local_organization');
            }
        }

        return $errors;
     }
    /**
     * Returns context where this form is used
     *
     * @return context
     */
    protected function get_context_for_dynamic_submission(): context {
        return context_system::instance();
    }

    /**
     * Checks if current user has access to this form, otherwise throws exception
     */
    protected function check_access_for_dynamic_submission(): void {
        has_capability('moodle/site:config', $this->get_context_for_dynamic_submission()) 
        || has_capability('local/organization:manage_communication_officer', $this->get_context_for_dynamic_submission());
        //require_capability('moodle/site:config', $this->get_context_for_dynamic_submission());
    }

    /**
     * Process the form submission, used if form was submitted via AJAX
     */
    public function process_dynamic_submission() {
        global $CFG, $DB;
        require_once($CFG->dirroot.'/user/profile/definelib.php');
        $data = $this->get_data();
        $context = context_system::instance();
        (new local_organization\organization)->add_update_organization($data);
        if($data){
            $this->save_stored_file('orglogo', $context->id, 'local_organization', 'orglogo',  $data->orglogo, '/', null, true);    
        }
        if($data->tax_certificate){
            $this->save_stored_file('tax_certificate', $context->id, 'local_organization', 'tax_certificate',  $data->tax_certificate, '/', null, true);    
        }
    }

    /**
     * Load in existing data as form defaults
     */
    public function set_data_for_dynamic_submission(): void {
        global $DB;
        if ($id = $this->optional_param('id', 0, PARAM_INT)) {
            $data = $DB->get_record('local_organization', ['id' => $id], '*', MUST_EXIST);;
            $data->sectors = $data->orgsector;
            $data->segment = $data->orgsegment;
            $data->description =  ['text' => $data->description];

        $str = $data->otherfieldofwork;
        // Setting name for enlish field
        preg_match('/{mlang en}(.*?){mlang}/', $str, $match);
        $englishname =  $match[1];
        // Setting name for arabic field
         preg_match('/{mlang ar}(.*?){mlang}/', $str, $match);
        $arabicname =  $match[1];
        $data->orgfieldofworken = $englishname;
        $data->orgfieldofworkab = $arabicname;
            $this->set_data($data);
        }
    }

    /**
     * Returns url to set in $PAGE->set_url() when form is being rendered or submitted via AJAX
     *
     * @return moodle_url
     */
    protected function get_page_url_for_dynamic_submission(): moodle_url {
        $id = $this->optional_param('id', 0, PARAM_INT);
        return new moodle_url('/local/organization/index.php',
            ['action' => 'editcategory', 'id' => $id]);
    }     
}
