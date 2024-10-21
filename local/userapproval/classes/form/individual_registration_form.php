<?php
// This file is part of Moodle - http://moodle.org/
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
 * @package    local_userapproval
 * @copyright  2022 eAbyas Info Solutions<info@eabyas.com>
 * @author     Vinod Kumar  <vinod.p@eabyas.com>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
   namespace local_userapproval\form;
   use core_form\dynamic_form ;
   use dml_transaction_exception;
   use moodle_url;
   use context;
   use context_system;
   use Exception;
   use moodle_exception;
   use stdClass;
   use local_sector\controller as sector;
   use local_userapproval\action\manageuser as manageuser;

   use local_trainingprogram\local\trainingprogram as tp;
   class individual_registration_form extends dynamic_form {
   public function definition () {
     global $USER, $CFG,$DB;
     $corecomponent = new \core_component();
     $mform = $this->_form;
     $id = $this->optional_param('id', 0, PARAM_INT);
     $mform->addElement('hidden', 'id', $id);
     $mform->setType('id', PARAM_INT);
     $mform->addElement('html', \html_writer::tag('h5', get_string('personal_details', 'local_userapproval'),array('class' => 'dynamic_form_id_personal_details heading_label')));
     $systemcontext = context_system::instance();
     $fullnamegroupelemnts=array();
     $fullnamegroupelemnts[] = $mform->createElement('text', 'firstname','', array('placeholder'=>get_string('firstname', 'local_userapproval'),'class' => 'dynamic_form_id_firstname'));
     $fullnamegroupelemnts[] = $mform->createElement('text', 'lastname','', array('placeholder'=>get_string('lastname', 'local_userapproval'),'class' => 'dynamic_form_id_lastname'));
     $mform->addGroup($fullnamegroupelemnts, 'fullname','', array('class' => 'fullname'), false);

     $fullnamearabicgroupelemnts=array();
     $fullnamearabicgroupelemnts[] = $mform->createElement('text', 'firstnamearabic','', array('placeholder'=>get_string('firstnamearabic', 'local_userapproval'),'class' => 'dynamic_form_id_firstname'));
     $fullnamearabicgroupelemnts[] = $mform->createElement('text', 'lastnamearabic','', array('placeholder'=>get_string('lastnamearabic', 'local_userapproval'),'class' => 'dynamic_form_id_lastname'));
     $mform->addGroup($fullnamearabicgroupelemnts, 'fullnamearabic','', array('class' => 'fullnamearabic'), false);

      $enmiddleandthirdnamegroupelemnts=array();
     $enmiddleandthirdnamegroupelemnts[] = $mform->createElement('text', 'middlenameen','', array('placeholder'=>get_string('middlenameen', 'local_userapproval'),'class' => 'dynamic_form_id_middlename'));
     $enmiddleandthirdnamegroupelemnts[] = $mform->createElement('text', 'thirdnameen','', array('placeholder'=>get_string('thirdnameen', 'local_userapproval'),'class' => 'dynamic_form_id_thirdnameen'));
     $mform->addGroup($enmiddleandthirdnamegroupelemnts, 'enmiddleandthirdname','', array('class' => 'enmiddleandthirdname'), false);


     $armiddleandthirdnamegroupelemnts=array();
     $armiddleandthirdnamegroupelemnts[] = $mform->createElement('text', 'middlenamearabic','', array('placeholder'=>get_string('middlenamearabic', 'local_userapproval'),'class' => 'dynamic_form_id_middlenamearabic'));
     $armiddleandthirdnamegroupelemnts[] = $mform->createElement('text', 'thirdnamearabic','', array('placeholder'=>get_string('thirdnamearabic', 'local_userapproval'),'class' => 'dynamic_form_id_thirdnamearabic'));
     $mform->addGroup($armiddleandthirdnamegroupelemnts, 'armiddleandthirdname','', array('class' => 'armiddleandthirdname'), false);
      

    $mform->addElement('html', \html_writer::tag('h4', get_string('gender', 'auth_registration'),array('class' => 'dynamic_form_gender heading_label')));
    $genderdobgroupelemnts=array();
    $genderclassarray = array('class' => 'dynamic_form_id_gender');
    $gender = [];
    $gender[''] = get_string('gender','local_userapproval');
    $gender['1'] = get_string('male','local_userapproval');
    $gender['2'] = get_string('female','local_userapproval');
    $mform->addElement('select', 'gender','', $gender,$genderclassarray);

     $mform->addElement('html', \html_writer::tag('h4', get_string('dateofbirth', 'auth_registration'),array('class' => 'dynamic_form_dateofbirth heading_label')));
     $dobarray = array('class' => 'dynamic_form_id_dateofbirth');
     $mform->addElement('date_selector', 'dateofbirth','','', $dobarray);

     $mform->addElement('html', \html_writer::tag('h5', get_string('preferredlanguage', 'local_userapproval'),array('class' => 'preferredlanguage')));
     $langclassarray = array('class' => 'dynamic_form_id_lang');
     $languages = get_string_manager()->get_list_of_languages();
     $defaultlanguage['ar'] = $languages['ar'];
     $languages = array_merge($defaultlanguage, $languages);
     $mform->addElement('select', 'lang','', $languages, $langclassarray);

     $mform->addElement('html', \html_writer::tag('h5', get_string('nationality', 'local_userapproval'),array('class' => 'dynamic_form_id_nationality heading_label')));
     $nationalityclassarray = array('class' => 'dynamic_form_id_nationality');
     $existingnationality = $DB->get_field('local_users','nationality',['id'=>$id]);

     $allnationalities = get_string_manager()->get_list_of_countries();
     $defaultnationality['SA'] = $allnationalities['SA'];
     $allnationalities = array_merge($defaultnationality, $allnationalities);

     $nationalities = ($id > 0) ?(($existingnationality > 0  && is_numeric($existingnationality)) ?  (new manageuser)->get_list_of_nationalities() : get_string_manager()->get_list_of_countries())    : $allnationalities;
     $newnationalities =  manageuser::hiding_countries($nationalities);

     $mform->addElement('select', 'nationality','',  $newnationalities, $nationalityclassarray);

     $mform->addElement('html', \html_writer::tag('h5', get_string('id_type', 'local_userapproval'),array('class' => 'dynamic_form_id_type heading_label')));
     
     $id_typeclassarray = array('class' => 'dynamic_form_id_type');
     $idtype = [];
     $idtype['1'] = get_string('id','local_userapproval');
     $idtype['2'] = get_string('passport','local_userapproval');
     $idtype['3'] = get_string('saudiid','local_userapproval');
     $idtype['4'] = get_string('residentialid','local_userapproval');
     $mform->addElement('select', 'id_type', '', $idtype,$id_typeclassarray);

     $mform->addElement('html', \html_writer::tag('h5', get_string('id_number', 'local_userapproval'),array('class' => 'dynamic_form_id_number heading_label')));
 
     $id_numberclassarray = array('class' => 'dynamic_form_id_number');
     $mform->addElement('text','id_number','','size="30"',$id_numberclassarray); // Add elements to your 
     $mform->setType('id_number', PARAM_NOTAGS);

    
      
      if(!is_siteadmin() && has_capability('local/organization:manage_organizationofficial', $systemcontext)) {  
         $organization =(int) $DB->get_field('local_users','organization',['userid'=>$USER->id]);

         $fetchingname = ( current_language() == 'ar') ? 'fullnameinarabic' : 'fullname';

         $organizationname = ($organization)? $DB->get_field('local_organization',$fetchingname,['id'=>$organization]) : 'N/A';

         $mform->addElement('static', 'organizationname',  get_string('organization', 'local_userapproval'),'<b>'.$organizationname.'</b>');
         $mform->addElement('hidden', 'organization',$organization);
         $mform->setType('organization', PARAM_INT);
        
      } else {

         $mform->addElement('html', \html_writer::tag('h5', get_string('select_organisation', 'local_userapproval'),array('class' => 'dynamic_form_select_organisation heading_label')));
         $organisationclassarray = array('class' => 'dynamic_form_organisation');
         $organizations = [];
         $organization = $this->_ajaxformdata['organization'];
         if (!empty($organization)) {
             $organizations = manageuser::get_user_organization(array($organization),$id);
         } elseif ($id > 0) {
             $organizations = manageuser::get_user_organization(array(),$id);
         }
         $attributes = array(
           'ajax' => 'local_organization/organization_datasource',
           'data-type' => 'organization_list',
           'data-org' => 1,
           'multiple' => false,
           'id' => 'orgselect',
           'class' => 'femptylabel',
           'placeholder' => get_string('selectorganisation','local_userapproval'),
           );
           $mform->addElement('autocomplete','organization','',$organizations,$attributes);

      }   
     // Add elements to your form
      $mform->addElement('html', \html_writer::tag('h4', get_string('organisation_details', 'local_userapproval'),array('class' => 'dynamic_form_organisation_details heading_label')));
      $sectors =manageuser::org_sector();

      $sectorid = $this->_ajaxformdata['sectors'];

      $segments = array();
   
      $segmentslist = $this->_ajaxformdata['segment'];
      if (!empty($segmentslist)) {
          
         $segments = manageuser::org_segment(0,array($segmentslist),$id);

      }elseif ($id > 0) {

         $segments = manageuser::org_segment(0,array(),$id);
      }

       $segments += array(null => get_string('choosesegment', 'local_userapproval'));



      $segmentdattributes = array(
         'ajax' => 'local_trainingprogram/sector_datasource',
         'data-type' => 'segment',
         'id' => 'el_segmentlist',
         'data-sectorid' => $sectorid,
         'onchange' => "(function(e){ require(['local_trainingprogram/dynamic_dropdown_ajaxdata'], function(s) {s.segmentschanged();}) }) (event)",
          'noselectionstring' => get_string('segment', 'local_userapproval'),
      );

      $jobfamilies = array();
      $jobfamilieslist = $this->_ajaxformdata['jobfamily'];

      if (!empty($jobfamilieslist)) {

         $jobfamilies = manageuser::org_jobfamily(0,array($jobfamilieslist ),$id);

      } elseif ($id > 0) {

         $jobfamilies = manageuser::org_jobfamily(0,array(),$id);

      }
      $jobfamilies += array(null => get_string('selectjob_family', 'local_userapproval'));

      $jfdattributes = array(
         'ajax' => 'local_trainingprogram/sector_datasource',
         'data-type' => 'jobfamily',
         'id' => 'el_jobfamily',
         'data-segmentid' => $segmentslist,
         'onchange' => "(function(e){ require(['local_trainingprogram/dynamic_dropdown_ajaxdata'], function(s) {s.jfamilychanged();}) }) (event)",
         'noselectionstring' => get_string('selectjob_family', 'local_userapproval'),
      );
       

      $jobroles = array();
      $jobroleslist = $this->_ajaxformdata['jobrole'];


      if (!empty($jobroleslist)) {

         $jobroles = manageuser::org_jobrole(0,array($jobroleslist),$id);

      } elseif ($id > 0) {

         $jobroles = manageuser::org_jobrole(0,array(),$id);
            
      }
      $jobroles += array(null => get_string('selectjobrole', 'local_userapproval'));

      $jrattributes = array(
         'ajax' => 'local_trainingprogram/sector_datasource',
         'data-type' => 'jobrole',
         'id' => 'el_jobroles',
         'data-jobfamilyid' => $jobroleslist,
         'noselectionstring' => get_string('jobrole', 'local_userapproval'),
      );
     $levelclassarray = array('class' => 'dynamic_form_level', 'noselectionstring' => get_string('jobrole_level', 'local_userapproval'));   
     $levels = [];
     $levels[''] = get_string('jobrole_level','local_userapproval');
     $levels['1'] = get_string('level-1','local_userapproval');
     $levels['2'] = get_string('level-2','local_userapproval');
     $levels['3'] = get_string('level-3','local_userapproval');
     $levels['4'] = get_string('level-4','local_userapproval');
     $levels['5'] = get_string('level-5','local_userapproval');

     $sectoroptions = array(
      'noselectionstring' => get_string('sector', 'local_userapproval'),
         'onchange' => "(function(e){ require(['local_trainingprogram/dynamic_dropdown_ajaxdata'], function(s) {s.sectorschanged();}) }) (event)",
         'class' => 'el_sectorlist',
     );

     $ord_details_groupelemnts=array();
     $ord_details_groupelemnts[] = $mform->createElement('autocomplete', 'sectors', '',$sectors, $sectoroptions);
     $ord_details_groupelemnts[] =$mform->createElement('autocomplete','segment','',$segments,$segmentdattributes);
     $ord_details_groupelemnts[] =$mform->createElement('autocomplete', 'jobfamily','',$jobfamilies, $jfdattributes);
     $ord_details_groupelemnts[] =$mform->createElement('autocomplete', 'jobrole','',$jobroles,$jrattributes);
     $ord_details_groupelemnts[] = $mform->createElement('autocomplete', 'jobrole_level','',$levels,$levelclassarray);
     $mform->addGroup($ord_details_groupelemnts, 'ord_details_2','', array('class' => 'ord_details_2'), false);
     
     // add new jobrole..renu

     $jobroleheader = [];
     $jobroleheader[] = $mform->createElement('static','jobroleheader');
     $mform->addGroup($jobroleheader, 'jobroleheader',get_string('newjobrole', 'local_userapproval'), false);
     $mform->hideIf('jobroleheader', 'jobrole', 'neq', '-1');

     $mform->addElement('text', 'title', get_string('jobroletitleeng', 'local_sector')); 
     $mform->hideIf('title', 'jobrole', 'neq', '-1');

     $mform->addElement('text', 'titlearabic', get_string('jobroletitlearabic', 'local_sector')); 
     $mform->hideIf('titlearabic', 'jobrole', 'neq', '-1'); 

     $mform->addElement('text', 'code', get_string('jobrolecode', 'local_sector')); 
     $mform->hideIf('code', 'jobrole', 'neq', '-1');  




     $jobroledescription = [];
     $jobroledescription[] = $mform->createElement('editor', 'description');
     $mform->addGroup($jobroledescription, 'description',get_string('description', 'local_sector'), array('class' => ''), false); 
     $mform->hideIf('description','jobrole', 'neq', -1); 
  
         $clevels = [];
         $clevels[null] = get_string('select_level','local_sector');
         $clevels['level1'] =  get_string('level1','local_sector');
         $clevels['level2'] =  get_string('level2','local_sector');
         $clevels['level3'] = get_string('level3','local_sector');
         $clevels['level4'] =  get_string('level4','local_sector');
         $clevels['level5'] = get_string('level5','local_sector') ; 

         $leveloptions = [
            'onchange' => "(function(e){ require(['local_trainingprogram/dynamic_dropdown_ajaxdata'], function(s) {s.clevels();}) }) (event)",
         ];
         $mform->addElement('select', 'clevels', get_string('level','local_sector'), $clevels,$leveloptions);
   
         $mform->hideIf('clevels', 'jobrole', 'neq', '-1');

         $competencytypes = tp::constcompetency_types();

         $competencytypeoptions = [
            'ajax' => 'local_trainingprogram/dynamic_dropdown_ajaxdata',
            'data-type' => 'program_competency',
            'class' => 'el_competencytype',
            'multiple'=>true,
            'onchange' => "(function(e){ require(['local_trainingprogram/dynamic_dropdown_ajaxdata'], function(s) {s.ctype();}) }) (event)",
         ];
         $mform->addElement('autocomplete', 'ctype', get_string('competency_type', 'local_trainingprogram'),$competencytypes,$competencytypeoptions);
         $mform->hideIf('ctype', 'jobrole', 'neq', '-1');

         $jobroleid = $this->optional_param('jobrole', 0, PARAM_INT);
         
        if ($jobroleid) {
         
            $competencieslist = $this->_ajaxformdata['competencylevel']; 
        }

         if (!empty($competencieslist)) {
                  
            $competencies = (new sector)->competencies_data(0,$competencieslist);

         } elseif ($id > 0) {

            $competencies = (new sector)->competencies_data($id,array());

         }

         $clattributes = array(
            'ajax' => 'local_trainingprogram/dynamic_dropdown_ajaxdata',
            'data-type' => 'program_competencylevel',
            'class' => 'el_competencieslist',
            'data-ctype' =>'',
            'multiple'=>true,
            'data-programid' =>1,
            'data-offeringid' =>1
         );

         $mform->addElement('autocomplete', 'competencylevel',get_string('competencies', 'local_trainingprogram'),$competencies,$clattributes);
         //$mform->addRule('competencylevel', get_string('missingcompetencylevel', 'local_exams'), 'required', null);
         $mform->hideIf('competencylevel', 'jobrole', 'neq', '-1');
     
     $mform->addElement('html', \html_writer::tag('h5', get_string('account_information', 'local_userapproval'),array('class' => 'dynamic_form_organisation_details heading_label')));
     $usernameclassarray = array('class' => 'dynamic_form_username');  
     $mform->addElement('text','username','',array('placeholder'=>get_string('username', 'local_userapproval')),$usernameclassarray); // Add elements to your form
     $mform->setType('username', PARAM_NOTAGS);


     $passwordclassarray = array('class' => 'dynamic_form_password');  
     $mform->addElement('password','password','',array('placeholder'=>get_string('password', 'local_userapproval')),$passwordclassarray); // Add elements to your form
     $mform->setType('password', PARAM_NOTAGS);
     $mform->hideIf('password',  'id',  'neq',  0);

     $dynamic_form_confirm_passwordclassarray = array('class' => 'dynamic_form_confirm_password');  
     $mform->addElement('password','confirm_password','',array('placeholder'=>get_string('confirm_password', 'local_userapproval')),$dynamic_form_confirm_passwordclassarray); // Add elements to your form
     $mform->setType('confirm_password', PARAM_NOTAGS);
     $mform->hideIf('confirm_password',  'id',  'neq',  0);

     $dynamic_form_emailclassarray = array('class' => 'dynamic_form_email');  
     $mform->addElement('text','email','',array('placeholder'=>get_string('email', 'local_userapproval')),$dynamic_form_emailclassarray); // Add elements to your form
     $mform->setType('email', PARAM_NOTAGS);
    
     $mform->addElement('html', \html_writer::tag('h5', get_string('address_details', 'local_userapproval'),array('class' => 'dynamic_form_organisation_details heading_label')));
 
     $countryclassarray = array('class' => 'dynamic_form_country');
     $countries = get_string_manager()->get_list_of_countries();
     $defaultcountry['SA'] = $countries['SA'];
     $countries = array_merge($defaultcountry, $countries);
     $cityclassarray = array('class' => 'dynamic_form_city');
     $country_citygroupelemnts=array();
     $country_citygroupelemnts[] = $mform->createElement('select', 'country','',$countries, $countryclassarray);
     $country_citygroupelemnts[] = $mform->createElement('text', 'city', '', ['placeholder'=>get_string('city', 'local_userapproval'), 'class' => 'dynamic_form_city']);

     $mform->addGroup($country_citygroupelemnts, 'country_city','', array('class' => 'country_city'), false);
     $mform->addElement('html','<div class="country_phonetest" style="direction: ltr;">');
     $country_phonegroups = array();
     $phone1classarray = array('class' => ' dynamic_form_phone1');
     $country_phonegroups[] = $mform->createElement('text', 'country_code', '', ['placeholder'=>get_string('country_code', 'auth_registration'), 'class' => 'dynamic_form_ccode']);
     $country_phonegroups[] = $mform->createElement('text', 'phone1', '', ['placeholder'=>get_string('mobile', 'auth_registration'), 'class' => ' dynamic_form_phone1']);
     $mform->addGroup($country_phonegroups, 'country_phone','', array('class' => 'country_phone'), false);
     $mform->addElement('html','</div>');
     // $mform->addElement('text','phone1', '',array('placeholder'=>get_string('mobile', 'local_userapproval'),$phone1classarray,  'class'=>'country_code'));     
   }
    
   public function validation($data, $files) {
      global $DB;
      if (empty(trim($data['firstname'])) && empty(trim($data['lastname']))){
        $errors['fullname'] = get_string('fullnamerequired','local_userapproval');
      }
       if (empty(trim($data['firstname'])) && !empty(trim($data['lastname']))){
        $errors['fullname'] = get_string('firstnamerequired','local_userapproval');
      }
       if (!empty(trim($data['firstname'])) && empty(trim($data['lastname']))){
        $errors['fullname'] = get_string('lastnamerequired','local_userapproval');
      }

      if (empty(trim($data['firstnamearabic'])) && empty(trim($data['lastnamearabic']))){
        $errors['fullnamearabic'] = get_string('fullnamearabicrequired','local_userapproval');
      }
       if (empty(trim($data['firstnamearabic'])) && !empty(trim($data['lastnamearabic']))){
        $errors['fullnamearabic'] = get_string('firstnamearabicrequired','local_userapproval');
      }
       if (!empty(trim($data['firstnamearabic'])) && empty(trim($data['lastnamearabic']))){
        $errors['fullnamearabic'] = get_string('lastnamearabicrequired','local_userapproval');
      }

      if (empty(trim($data['middlenameen'])) && empty(trim($data['thirdnameen']))){
         $errors['enmiddleandthirdname'] = get_string('enmiddleandthirdnamerequired','local_userapproval');
      }
      if (empty(trim($data['middlenamearabic'])) && empty(trim($data['thirdnamearabic']))){
        $errors['armiddleandthirdname'] = get_string('armiddleandthirdnamerequired','local_userapproval');
      }

      if (empty(trim($data['middlenameen'])) && !empty(trim($data['thirdnameen']))){
       $errors['enmiddleandthirdname'] = get_string('middlenameenrequired','local_userapproval');
      }
      if (!empty(trim($data['middlenameen'])) && empty(trim($data['thirdnameen']))){
       $errors['enmiddleandthirdname'] = get_string('thirdnameenrequired','local_userapproval');
      }

       if (empty(trim($data['middlenamearabic'])) && !empty(trim($data['thirdnamearabic']))){
       $errors['armiddleandthirdname'] = get_string('middlenamearabicrequired','local_userapproval');
      }
      if (!empty(trim($data['middlenamearabic'])) && empty(trim($data['thirdnamearabic']))){
       $errors['armiddleandthirdname'] = get_string('thirdnamearabicrequired','local_userapproval');
      }
      /*if (!empty(trim($data['firstname'])) && !empty(trim($data['lastname'])) && (!preg_match('/^[a-zA-Z.,]*$/',trim($data['firstname'])) || !preg_match('/^[a-zA-Z.,]*$/',trim($data['lastname'])))) {
       $errors['fullname'] = get_string('acceptsstringsanddotonly', 'local_userapproval');
      }*/
     if (empty($data['country']) && empty(trim($data['city']))){
        $errors['country_city'] = get_string('country_cityrequired','local_userapproval');
     }
     if (empty($data['country']) && !empty(trim($data['city']))){
        $errors['country_city'] = get_string('countryrequired','local_userapproval');
     }
     if (!empty($data['country']) && empty(trim($data['city']))){
        $errors['country_city'] = get_string('cityrequired','local_userapproval');
     }
     /*if (!empty(trim($data['city'])) && !preg_match('/^[a-zA-Z, ]*$/',trim($data['city']))) {
        $errors['country_city'] = get_string('cityacceptsstringsandspacesonly', 'local_userapproval');
     }*/
     if (empty($data['gender']) || is_null($data['gender']))  {
        $errors['gender'] = get_string('genderrequired', 'local_userapproval');
     }
     if (empty($data['lang']))  {
        $errors['lang'] = get_string('langrequired', 'local_userapproval');
     }
     if (empty($data['id_number']))  {
        $errors['id_number'] = get_string('id_numberrequired', 'local_userapproval');
     }
     if (!empty(trim($data['id_number'])) && $data['id_type'] != 2  && !is_numeric($data['id_number'])){
         $errors['id_number'] = get_string('requirednumeric','local_userapproval');
      }
     if(($data['id_type'] == 3) || ($data['id_type'] == 4)){
        if (!empty(trim($data['id_number'])) && strlen(trim($data['id_number'])) < 10) {
          $errors['id_number'] = get_string('lengthcantbelowerthan10', 'local_userapproval');
        }
        if (!empty(trim($data['id_number'])) && strlen(trim($data['id_number'])) > 10 && strlen(trim($data['id_number'])) > 10) {
           $errors['id_number'] = get_string('lengthcantbemorethan10', 'local_userapproval');
        }
     }
      // if(isset($data['jobrole_level']) && empty(trim($data['jobrole_level']))){
      //    $errors['jobrole_level'] = get_string('jobrole_levelrequired','local_userapproval');
      // }
      // if(isset($data['jobrole']) && empty(trim($data['jobrole']))){
      //    $errors['jobrole'] = get_string('jobrolerequired','local_userapproval');
      // }
      // if(isset($data['jobfamily']) && empty(trim($data['jobfamily']))){
      //    $errors['ord_details_2'] = get_string('jobfamilyrequired','local_userapproval');
      // }
      // if(isset($data['segment']) && empty(trim($data['segment']))){
      //    $errors['ord_details_2'] = get_string('segmentrequired','local_userapproval');
      // }

      // if(empty($data['sectors']) || empty($data['segment']) || empty($data['jobfamily'])  || empty($data['jobrole'])  || empty($data['jobrole_level'])  ){
      //       $errors['ord_details_2'] = get_string('dependenciesarenotempty','local_userapproval');
      // }

      // if(!empty($data['sectors']) && (empty($data['segment']) || empty($data['jobfamily'])  || empty($data['jobrole'])  || empty($data['jobrole_level']))){
      //       $errors['ord_details_2'] = get_string('dependenciesexceptsectorarenotempty','local_userapproval');
      // }
      // if(!empty($data['sectors']) && !empty($data['segment'])  && (empty($data['jobfamily'])  || empty($data['jobrole'])  || empty($data['jobrole_level']))){
      //       $errors['ord_details_2'] = get_string('dependenciesexceptsector_segmentarenotempty','local_userapproval');
      // }

      // if(!empty($data['sectors']) && !empty($data['segment']) && !empty($data['jobfamily']) && (empty($data['jobrole'])  || empty($data['jobrole_level']))){
      //       $errors['ord_details_2'] = get_string('dependenciesexceptsector_segment_jobfamilyarenotempty','local_userapproval');
      // }

      // if(!empty($data['sectors']) && !empty($data['segment']) && !empty($data['jobfamily']) && !empty($data['jobrole']) &&  empty($data['jobrole_level'])){

      //       $errors['ord_details_2'] = get_string('dependenciesexceptsector_segment_jobfamily_jobrolearenotempty','local_userapproval');
      // }

     if (empty($data['username']))  {
        $errors['username'] = get_string('usernamerequired', 'local_userapproval');
     }
     if (!empty($data['username']) && !preg_match('/^[A-Za-z0-9_$%&#@.]+$/',$data['username'])) {
         $errors['username'] = get_string('requiredvalidusername', 'local_userapproval');
      }
     
     if($data['id'] == 0) {

         if (empty($data['password']))  {
              $errors['password'] = get_string('passwordrequired', 'local_userapproval');
         }

         $errmsg = ''; // Prevent eclipse warning.
         if (!empty($data['password']) && !check_password_policy($data['password'], $errmsg)) {

               $errors['password'] = $errmsg;
         }
         if (empty($data['confirm_password']))  {
               $errors['confirm_password'] = get_string('confirm_passwordrequired', 'local_userapproval');
         }

         if ($data['confirm_password'] != $data['password'])  {
            $errors['confirm_password'] = get_string('confirmpasswordnotmatchederror', 'local_userapproval');
         }
     }
     
     if (empty($data['email'])) {
        $errors['email'] = get_string('emailrequired', 'local_userapproval');
     }

     // if (!empty($data['email']) && !validate_email($data['email'])) {
         //     $errors['email'] = get_string('requiredvalidemail', 'local_userapproval');
     // }
     if (!empty($data['email']) &&  !preg_match('/^[A-Za-z0-9._%+-]+@[a-z0-9.-]+\.[a-z]{2,8}$/',$data['email'])) {

         $errors['email'] = get_string('requiredvalidemail', 'local_userapproval');
     }

     //  if (empty($data['phone1']))  {
     //    $errors['country_phone'] = get_string('phone1required', 'auth_registration');
     //  }
     // if (!empty(trim($data['phone1'])) && !is_numeric($data['phone1'])){
     //    $errors['country_phone'] = get_string('requirednumeric','auth_registration');
     // }
      if (empty($data['country_code']))  {
        $errors['country_phone'] = get_string('country_coderequired', 'auth_registration');
      }
     if (!empty(trim($data['country_code'])) && !is_numeric($data['country_code'])){
        $errors['country_phone'] = get_string('requirednumeric','auth_registration');
     }

     if (empty($data['phone1']))  {
        $errors['country_phone'] = get_string('phone1required', 'local_userapproval');
     }
     if (!empty(trim($data['phone1'])) && !is_numeric($data['phone1'])){
        $errors['country_phone'] = get_string('requirednumeric','local_userapproval');
     }
     if  (!empty(trim($data['phone1'])) && is_numeric(trim($data['phone1'])) && ( strlen(trim($data['phone1'])) < 5  || strlen(trim($data['phone1'])) > 10 )) {
         $errors['country_phone'] = get_string('minimum5digitsallowed','local_userapproval');
     }
     /*if (!is_siteadmin() && !empty(trim($data['phone1'])) && is_numeric(trim($data['phone1'])) && (strlen(trim($data['phone1'])) >= 5  &&  strlen(trim($data['phone1'])) <= 10) &&  !preg_match('/^[5-9][0-9]/',trim($data['phone1']))) {
         $errors['country_phone'] = get_string('startswith5','local_userapproval');
     }*/
    
     if (empty($data['id'])) {
         if ($DB->record_exists('local_users', array('username' => $data['username'],'deleted' => 0))) {
            $errors['username'] = get_string('usernameexisterror', 'local_userapproval');
         }
         if ($DB->record_exists('local_users', array('email' => $data['email'],'deleted' => 0))) {
            $errors['email'] = get_string('emailexisterror', 'local_userapproval');
         }

         if ($DB->record_exists('local_users', array('id_number' => $data['id_number'],'deleted' => 0))) {
            $errors['id_number'] = get_string('id_numberexisterror', 'local_userapproval');
         }
      } else {
         $usernameexist= $DB->get_records_sql('SELECT * FROM {local_users} WHERE username = :username AND id = :id', ['username' => $data['username'], 'id' => $data['id']]);
         if (count($usernameexist) <= 0) {
            if ($DB->record_exists('local_users', array('username' => $data['username'],'deleted' => 0))) {
               $errors['username'] = get_string('usernameexisterror', 'local_userapproval');
            }
         }
         $emailexist= $DB->get_records_sql('SELECT * FROM {local_users} WHERE email = :email AND id = :id', ['email' => $data['email'], 'id' => $data['id']]);
         if (count($emailexist) <= 0) {
            if ($DB->record_exists('local_users', array('email' => $data['email'],'deleted' => 0))) {
               $errors['email'] = get_string('emailexisterror', 'local_userapproval');
            }
         }
         $idnumberexist= $DB->get_records_sql('SELECT * FROM {local_users} WHERE id_number = :idnumber AND id = :id', ['idnumber' => $data['id_number'], 'id' => $data['id']]);
         if (count($idnumberexist) <= 0) {
            if ($DB->record_exists('local_users', array('id_number' => $data['id_number'],'deleted' => 0))) {
               $errors['id_number'] = get_string('id_numberexisterror', 'local_userapproval');
            }
         }
      }

      if($data['jobrole'] == '-1'){

         if (empty($data['title'])) {
            $errors['title'] = get_string('titleerr', 'local_sector');
         }

         if (empty($data['titlearabic'])) {
            $errors['titlearabic'] = get_string('titleerr', 'local_sector');
         }

         if (empty($data['code'])) {
            $errors['code'] = get_string('jobcodeerrr', 'local_sector');
         }
         if (empty($data['description'])) {
            $errors['description'] = get_string('id_numberexisterror', 'local_userapproval');
         }

         if (empty($data['clevels'])) {
            $errors['clevels'] = get_string('selectlevel','local_sector');
         }
         
         if (empty($data['ctype'])) {
            $errors['ctype'] =  get_string('missingcompetencytype', 'local_exams');
         }

         if (empty($data['competencylevel'])) {
            $errors['competencylevel'] = get_string('missingcompetencylevel', 'local_exams');
         }
      }
     return $errors;
   }
   protected function get_context_for_dynamic_submission(): context {
        return context_system::instance();
   }
   protected function check_access_for_dynamic_submission(): void {
       
        is_siteadmin()  || has_capability('local/organization:manage_communication_officer', $this->get_context_for_dynamic_submission());
   }
   public function process_dynamic_submission() {
      global $CFG, $DB,$USER;
      require_once($CFG->dirroot.'/user/profile/definelib.php');
      $data = $this->get_data();
  
      if  ($data->id > 0) {
         $approveduserid=$DB->get_field('local_users','userid',array('id'=>$data->id));
         $description= get_string('update_descption','local_userapproval',$data);
         if($approveduserid > 0) {
            $updatecustomrecord = (new manageuser)->update_custom_user($data,$approveduserid);
            $updaterecord = (new manageuser)->user_update_user($data,$approveduserid);
            $insert_user_logs =(new manageuser)->local_users_logs('updated', 'userapproval', $description, $approveduserid);
         } else {
            $updaterecord = (new manageuser)->update_register_user($data);
            $insert_user_logs =(new manageuser)->local_users_logs('updated', 'userapproval', $description, $data->id);
         }    
      } else {

         try{
            $e= new stdClass;
            $failed_masterdata = array();

            if(!empty($data->jobfamily)){
               if($data->jobrole=='-1'){
            //add new job role
            $addjobrole= new stdClass();
            $addjobrole->title = $data->title;
            $addjobrole->titlearabic = $data->titlearabic;
            $addjobrole->code = $data->code;
            $addjobrole->description = $data->description;
            $addjobrole->jobfamily = $data->jobfamily;
            $addjobrole->level = $data->clevels;
            $addjobrole->ctypes = $data->ctypes;
            $addjobrole->competencies = $data->competencies;
            $addjobrole->timemodified = time();
            $addjobrole->usermodified = $USER->id;
      
             $jobrole_id = $DB->insert_record('local_jobrole_level', $addjobrole);
               }
              
            }
         $data->cjobroleid =  $jobrole_id; 
            $transaction = $DB->start_delegated_transaction();
            $custom_ntionalities = get_string_manager()->get_list_of_countries();
            $localusers_ntionalities =  array_flip((new manageuser)->get_list_of_nationalities());
            $submitted_nationality = $custom_ntionalities[$data->nationality];
            $data->nationalitycountryid =  $localusers_ntionalities[$submitted_nationality];
            $data->givenpassword = $data->password;
            $data->password = hash_internal_user_password($data->password);
            $userid = (new manageuser)->create_user($data);
            $data->jobrole = ($data->cjobroleid) ? $data->cjobroleid : $data->jobrole;
            $insertrecord = (new manageuser)->create_custom_user($data,$userid);
            $description= get_string('insert_descption','local_userapproval',$data);
            $insert_user_logs =(new manageuser)->local_users_logs('registered', 'userapproval', $description, $userid);
            if($userid){
               if($data->usersource != 'IAM'){
                  $orgdata = (new manageuser)->get_user_org_info($userid);  
                  $data->orgcode = $orgdata->orgcode;
                  $data->licensekey = $orgdata->licensekey;

                  // Checking Fast Settings(User Registration) is Enabled or not
                  $accessstatus = (new \local_userapproval\local\fast_service)->access_resisteruser_service();
                  if ($accessstatus) {
                     $response =   (new \local_userapproval\local\fast_service)->register_user($data);

                     if(COUNT($response->errors) > 0 ){
                        $error = json_encode($response->errors, JSON_UNESCAPED_UNICODE);
                        $e = new moodle_exception($error);
                     }
   
                     if(COUNT($response->messages) > 0 ){
                        $messages = json_encode($response->messages, JSON_UNESCAPED_UNICODE);
                        $e = new moodle_exception($messages);
                     }
                  }
 
                  
               }        
            }
            if (empty($e) || !($e instanceof moodle_exception)) {

               $transaction->allow_commit();
               return $userid;

            }else{

               $data->status = "-1";
               $failed_masterdata['data'] = $data;
               $failed_masterdata['e'] = $e;
               $transaction->rollback($e);
            }
            
         } catch(moodle_exception | Exception | dml_transaction_exception $e){
            if(!$transaction->is_disposed()){
               $data->status = "-1";
               $failed_masterdata['data'] = $data;
               $failed_masterdata['e'] = $e;
               $transaction->rollback($e);
           }
         }
      }
      if(!empty($failed_masterdata)){
         (new \local_userapproval\local\fast_service)->add_update_service($failed_masterdata['data']);
         throw new moodle_exception($failed_masterdata['e']->errorcode);
      } 

   }
   public function set_data_for_dynamic_submission(): void {
      global $DB;
      if ($id = $this->optional_param('id', 0, PARAM_INT)) {
         $data = $DB->get_record('local_users', ['id' => $id], '*', MUST_EXIST);
         $data->sectors = $data->sector;
         // $data->segment =$data->segment;
         // $data->jobfamily =  $data->jobfamily;
         // $data->jobrole =  $data->jobrole;
         if( $data->organization > 0 ) {
            $data->organization = $data->organization;
                
         } else {

            $orginfo=$DB->get_record('organization_requests',array('userid' => $id));
            if($orginfo->status != 3){
               $data->organization = $DB->get_field('organization_requests','orgid',array('userid' => $id));
            }
         }
         $this->set_data($data);
      }
   }
   protected function get_page_url_for_dynamic_submission(): moodle_url {
      $id = $this->optional_param('id', 0, PARAM_INT);
      return new moodle_url('/local/userapproval/index.php',
      ['action' => 'createuser', 'id' => $id]);
   }
}
