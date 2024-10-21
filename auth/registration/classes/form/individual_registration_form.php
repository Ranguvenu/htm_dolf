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
 * @package    auth_registration
 * @copyright  2022 eAbyas Info Solutions<info@eabyas.com>
 * @author     Vinod Kumar  <vinod.p@eabyas.com>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
namespace auth_registration\form;
require_once($CFG->libdir.'/formslib.php');
use core_form\moodleform ;
use moodle_url;
use context;
use context_system;
use auth_registration;
use local_userapproval\action\manageuser as usermanager;
use auth_registration\action\manageuser as manageuser;

class individual_registration_form extends \moodleform {
   public function definition() {
     global $USER, $CFG,$DB;
     $corecomponent = new \core_component();
     $mform = $this->_form;
     $systemcontext = context_system::instance();
      $iamloginurl = get_auth_plugin('iam')->get_login_url();
      $registertype =(int) $this->_ajaxformdata['regtype'];
      $saudiid = '';
      $otherid = 'invisible1';
      if($registertype == 0) {
         $saudiid = '';
         $otherid ='invisible1';
      }
      if( $registertype == 1) {
         $saudiid ='invisible1';
         $otherid = '';
      }
   //   $invidual_registration_heading = 
   //    '<div class="row">
   //       <div class="col-md-6">
   //          <div class="dynamic_form_id_ind_user_registration">
   //             <h5>'.get_string('ind_user_registration', 'auth_registration').'</h5>
   //             <div class="headline_border"></div>
   //             </div>
   //       </div>
   //       <div class="col-md-6 text-right"> 
   //          <a href="'.$iamloginurl.'" class="btn btn-primary iam-auth">
                
   //                '.get_string('iam_auth', 'auth_registration').'
   //          </a>
   //       </div>
   //    </div>';
   //   $mform->addElement('html', $invidual_registration_heading);
   $attributes = array('0' => 'saudiid','1' => 'other');
   $radioarray=array();
   $radioarray[] = $mform->createElement('html','<div class = "user_reg col-8 m-auto w-100">');
   $radioarray[] = $mform->createElement('html','<div class= "d-flex align-items-center justify-content-center mb-4"><div class="user_reg_icon mr-2"><i class="fa fa-user-plus"></i></div><div class="header ">'.get_string('userregistration','auth_registration').'</div></div>');
   $radioarray[] = $mform->createElement('html','<div class ="d-flex justify-content-center flex-wrap radio_buttons">');
   $radioarray[] = $mform->createElement('html','<div>');
   $radioarray[] = $mform->createElement('radio', 'regtype', '', get_string('saudiidreg','auth_registration'), 0, $attributes);
   $radioarray[] = $mform->createElement('html','</div>');
   $radioarray[] = $mform->createElement('html','<div class = "other_pwd ml-4">');
   $radioarray[] = $mform->createElement('radio', 'regtype', '', get_string('otherreg','auth_registration'), 1, $attributes);
   $radioarray[] = $mform->createElement('html','</div>');
   $radioarray[] = $mform->createElement('html','</div>');
   $radioarray[] = $mform->createElement('html','</div>');
   $mform->addGroup($radioarray, 'regtype','', array('class' => 'dynamic_form_id_regtype'), false);
    //$mform->setDefault('reg', 1);
   $mform->addElement('html','<div class ="tagscontainer row mt-4 d-flex'.$saudiid.'" data-tagtype="0">');
   $mform->addElement('html', \html_writer::tag('div', get_string('saudiidreg', 'auth_registration'),array('class' => 'saudi_id col-md-4')));
   $mform->addElement('html','<div class ="col-md-8 d-flex flex-wrap">');   
   $validate_id = array();
   $validate_id [] = $mform->createElement('text', 'saudid','', array('placeholder'=>get_string('saudiresid', 'auth_registration')));
  
   $mform->addGroup( $validate_id , 'saudid','', array('class' => 'm-0'), false);
   
    

   //   $buttonarray = array();
   //    $invidual_registration_button = 
   //    '     
        
   //          <a href="'.$iamloginurl.'" class="btn btn-primary iam-auth">
                
   //             '.get_string('iam_auth', 'auth_registration').'
   //          </a>
       
   //    ';
   //     $buttonarray[] = $mform->createElement('html', $invidual_registration_button);

       //$mform->addGroup($buttonarray, 'buttontype','', array('class' => 'dynamic_form_id_regtype'), false);
       $mform->addElement('submit', 'regsubmit', get_string('iam_auth', 'auth_registration'));
       $mform->addElement('html','</div>');
       $mform->addElement('html','</div>');
       

       $mform->addElement('html','<div class ="tagscontainer '.$otherid.'" data-tagtype="1">');
       $mform->addElement('html', \html_writer::tag('h4', get_string('personal_details', 'auth_registration'),array('class' => 'dynamic_form_personal_details heading_label')));

     $fullnamegroupelemnts=array();
     $fullnamegroupelemnts[] = $mform->createElement('text', 'firstname','', array('placeholder'=>get_string('firstname', 'auth_registration'),'class' => 'dynamic_form_id_firstname'));
     $fullnamegroupelemnts[] = $mform->createElement('text', 'lastname','', array('placeholder'=>get_string('lastname', 'auth_registration'),'class' => 'dynamic_form_id_lastname'));
     $mform->addGroup($fullnamegroupelemnts, 'fullname','', array('class' => 'fullname'), false);

     $fullnamearabicgroupelemnts=array();
     $fullnamearabicgroupelemnts[] = $mform->createElement('text', 'firstnamearabic','', array('placeholder'=>get_string('firstnamearabic', 'auth_registration'),'class' => 'dynamic_form_id_firstname'));
     $fullnamearabicgroupelemnts[] = $mform->createElement('text', 'lastnamearabic','', array('placeholder'=>get_string('lastnamearabic', 'auth_registration'),'class' => 'dynamic_form_id_lastname'));
     $mform->addGroup($fullnamearabicgroupelemnts, 'fullnamearabic','', array('class' => 'fullnamearabic'), false);

     $enmiddleandthirdnamegroupelemnts=array();
     $enmiddleandthirdnamegroupelemnts[] = $mform->createElement('text', 'middlenameen','', array('placeholder'=>get_string('middlenameen', 'auth_registration'),'class' => 'dynamic_form_id_middlename'));
     $enmiddleandthirdnamegroupelemnts[] = $mform->createElement('text', 'thirdnameen','', array('placeholder'=>get_string('thirdnameen', 'auth_registration'),'class' => 'dynamic_form_id_thirdnameen'));
     $mform->addGroup($enmiddleandthirdnamegroupelemnts, 'enmiddleandthirdname','', array('class' => 'enmiddleandthirdname'), false);


     $armiddleandthirdnamegroupelemnts=array();
     $armiddleandthirdnamegroupelemnts[] = $mform->createElement('text', 'middlenamearabic','', array('placeholder'=>get_string('middlenamearabic', 'auth_registration'),'class' => 'dynamic_form_id_middlenamearabic'));
     $armiddleandthirdnamegroupelemnts[] = $mform->createElement('text', 'thirdnamearabic','', array('placeholder'=>get_string('thirdnamearabic', 'auth_registration'),'class' => 'dynamic_form_id_thirdnamearabic'));
     $mform->addGroup($armiddleandthirdnamegroupelemnts, 'armiddleandthirdname','', array('class' => 'armiddleandthirdname'), false);

    
     $mform->addElement('html', \html_writer::tag('h4', get_string('gender', 'auth_registration'),array('class' => 'dynamic_form_gender heading_label')));
    $genderdobgroupelemnts=array();
    $genderclassarray = array('class' => 'dynamic_form_id_gender');
    $gender = [];
    $gender[''] = get_string('gender','auth_registration');
    $gender['1'] = get_string('male','auth_registration');
    $gender['2'] = get_string('female','auth_registration');
    $mform->addElement('select', 'gender','', $gender,$genderclassarray);

     $mform->addElement('html', \html_writer::tag('h4', get_string('dateofbirth', 'auth_registration'),array('class' => 'dynamic_form_dateofbirth heading_label')));
     $dobarray = array('class' => 'dynamic_form_id_dateofbirth');
     $mform->addElement('date_selector', 'dateofbirth','','', $dobarray);



     $langclassarray = array('class' => 'dynamic_form_id_lang');
     $languages = get_string_manager()->get_list_of_languages();
     $defaultlanguage['ar'] = $languages['ar'];
     $languages = array_merge($defaultlanguage, $languages);
     $mform->addElement('select', 'lang','', $languages, $langclassarray);
   
   
    
     $mform->addElement('html', \html_writer::tag('h4', get_string('nationality', 'auth_registration'),array('class' => 'dynamic_form_id_nationality heading_label')));
     $nationalityclassarray = array('class' => 'dynamic_form_id_nationality');
     // $countries = get_string_manager()->get_list_of_countries();
     // $defaultcountry['SA'] = $countries['SA'];
     // $countries = array_merge($defaultcountry, $countries);
     // unset($countries['SA']);
     // $mform->addElement('select', 'nationality','', $countries, $nationalityclassarray);
     $nationalities = (new usermanager)->get_list_of_nationalities();
      unset($nationalities[113]);
     $mform->addElement('select', 'nationality','', $nationalities, $nationalityclassarray);
     $mform->setDefault('nationality', 113);

     $mform->addElement('html', \html_writer::tag('h5', get_string('id_type', 'auth_registration'),array('class' => 'dynamic_form_id_type heading_label')));
     
     $id_typeclassarray = array('class' => 'dynamic_form_id_type');
     $idtype = [];
     $idtype['1'] = get_string('id','auth_registration');
     $idtype['2'] = get_string('passport','auth_registration');
     //$idtype['3'] = get_string('saudiid','auth_registration');
     //$idtype['4'] = get_string('residentialid','auth_registration');
     $mform->addElement('select', 'id_type', '', $idtype,$id_typeclassarray);

     $mform->addElement('html', \html_writer::tag('h4', get_string('id_number', 'auth_registration'),array('class' => 'dynamic_form_id_number heading_label')));
 
     $id_numberclassarray = array('class' => 'dynamic_form_id_number');
     $mform->addElement('text','id_number','','size="30"',$id_numberclassarray); // Add elements to your 
     $mform->setType('id_number', PARAM_NOTAGS);

     $mform->addElement('html', \html_writer::tag('h5', get_string('select_organisation', 'auth_registration'),array('class' => 'dynamic_form_select_organisation heading_label')));
     $organisationclassarray = array('class' => 'dynamic_form_organisation');
     $currlang = current_language();

     $orgfullname = ($currlang == 'ar') ? 'fullnameinarabic' : 'fullname';

     $organisationlist=$DB->get_records_sql('SELECT id,'.$orgfullname.' as orgname FROM {local_organization} where visible=1 and status=2');
     $organisations=[];
      $organisations[''] = get_string('selectorganisation','auth_registration');
     foreach ($organisationlist AS $organisation){
         $organisations[$organisation->id]=$organisation->orgname;
     }
     $mform->addElement('select','organization','',$organisations,$organisations); // Add elements to your form
      //$mform->addElement('html', \html_writer::tag('h4', get_string('organisation_details', 'auth_registration'),array('class' => 'dynamic_form_organisation_details heading_label')));

      $sectors =manageuser::org_sector();

      $sectorid = $this->_ajaxformdata['sectors'];


      $segments = array();
   
      $segmentslist = $this->_ajaxformdata['segment'];
      if (!empty($segmentslist)) {
          
         $segments = manageuser::org_segment(0,array($segmentslist));

      } 

       $segments += array(null => get_string('choosesegment', 'auth_registration'));

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

         $jobfamilies = manageuser::org_jobfamily(0,array($jobfamilieslist ));

      }
      $jobfamilies += array(null => get_string('selectjob_family', 'auth_registration'));

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

         $jobroles = manageuser::org_jobrole(0,array($jobroleslist));

      } 
      $jobroles += array(null => get_string('selectjobrole', 'auth_registration'));

      $jrattributes = array(
         'ajax' => 'local_trainingprogram/sector_datasource',
         'data-type' => 'jobrole',
         'id' => 'el_jobroles',
         'data-jobfamilyid' => $jobfamilieslist,
          'noselectionstring' => get_string('jobrole', 'local_userapproval'),
      );
        
     $levelclassarray = array('class' => 'dynamic_form_level', 'noselectionstring' => get_string('jobrole_level', 'local_userapproval'));   
     $levels = [];
     $levels[''] = get_string('jobrole_level','auth_registration');
     $levels['level1'] = get_string('level-1','auth_registration');
     $levels['level2'] = get_string('level-2','auth_registration');
     $levels['level3'] = get_string('level-3','auth_registration');
     $levels['level4'] = get_string('level-4','auth_registration');
     $levels['level5'] = get_string('level-5','auth_registration');
     $sectoroptions = array(
         'onchange' => "(function(e){ require(['local_trainingprogram/dynamic_dropdown_ajaxdata'], function(s) {s.sectorschanged();}) }) (event)",
         'class' => 'el_sectorlist',
         'data-id' => 'el_sectorlist',
         'noselectionstring' => get_string('sector', 'local_userapproval'),
     );

   //   $ord_details_groupelemnts=array();
   //   $ord_details_groupelemnts[] = $mform->createElement('autocomplete', 'sectors', '',$sectors,$sectoroptions);
   //    $ord_details_groupelemnts[] =$mform->createElement('autocomplete','segment','',$segments,$segmentdattributes);
   //   $ord_details_groupelemnts[] =$mform->createElement('autocomplete', 'jobfamily','',$jobfamilies, $jfdattributes);
   //   $ord_details_groupelemnts[] =$mform->createElement('autocomplete', 'jobrole','',$jobroles,$jrattributes);
   //   $ord_details_groupelemnts[] = $mform->createElement('autocomplete', 'jobrole_level','',$levels,$levelclassarray);
   //   $mform->addGroup($ord_details_groupelemnts, 'ord_details_2','', array('class' => 'ord_details_2'), false);

     $mform->addElement('html', \html_writer::tag('h4', get_string('account_information', 'auth_registration'),array('class' => 'dynamic_form_organisation_details heading_label')));
     $usernameclassarray = array('class' => 'dynamic_form_username');  
     $mform->addElement('text','username','',array('placeholder'=>get_string('username', 'auth_registration')),$usernameclassarray); // Add elements to your form
     $mform->setType('username', PARAM_NOTAGS);
    
     $passwordclassarray = array('class' => 'dynamic_form_password');  
     $mform->addElement('password','password','',array('placeholder'=>get_string('password', 'auth_registration')),$passwordclassarray); // Add elements to your form
     $mform->setType('password', PARAM_NOTAGS);

     $dynamic_form_confirm_passwordclassarray = array('class' => 'dynamic_form_confirm_password');  
     $mform->addElement('password','confirm_password','',array('placeholder'=>get_string('confirm_password', 'auth_registration')),$dynamic_form_confirm_passwordclassarray); // Add elements to your form
     $mform->setType('confirm_password', PARAM_NOTAGS);

     $dynamic_form_emailclassarray = array('class' => 'dynamic_form_email');  
     $mform->addElement('text','email','',array('placeholder'=>get_string('email', 'auth_registration')),$dynamic_form_emailclassarray); // Add elements to your form
     $mform->setType('email', PARAM_NOTAGS);
    
     $mform->addElement('html', \html_writer::tag('h4', get_string('address_details', 'auth_registration'),array('class' => 'dynamic_form_address_details heading_label')));
 
     $countryclassarray = array('class' => 'dynamic_form_country');
     $countries = get_string_manager()->get_list_of_countries();
     $defaultcountry['SA'] = $countries['SA'];
     $countries = array_merge($defaultcountry, $countries);
     unset($countries['SA']);
     $cityclassarray = array('class' => 'dynamic_form_city');
     $country_citygroupelemnts=array();
     $country_citygroupelemnts[] = $mform->createElement('select', 'country','',$countries, $countryclassarray);
     $country_citygroupelemnts[] = $mform->createElement('text', 'city','',array('placeholder'=>get_string('city', 'auth_registration'), $cityclassarray));
     $mform->addGroup($country_citygroupelemnts, 'country_city','', array('class' => 'country_city'), false);

     $mform->addElement('html','<div class="country_phonetest row" style="direction: ltr;">');
     $phone1classarray = array('class' => 'dynamic_form_phone1');
     // $mform->addElement('text','phone1','',array('placeholder'=>get_string('mobile', 'auth_registration'),$phone1classarray));    


     $country_phonegroups = array();
     $country_phonegroups[] = $mform->createElement('text','country_code','',array('placeholder'=>get_string('country_code', 'auth_registration'),array('class' => 'dynamic_form_ccode')));
     $country_phonegroups[] = $mform->createElement('text','phone1','',array('placeholder'=>get_string('mobile', 'auth_registration'),$phone1classarray));
     $mform->addGroup($country_phonegroups, 'country_phone','', array('class' => 'country_phone'), false);
     $mform->addElement('html','</div>');
     $mform->addElement('submit', 'regsubmit', get_string('register', 'auth_registration'),array('class'=>'btn-block'));
     $mform->addElement('html','</div>');

      $mform->disable_form_change_checker();
        
   }
   public function validation($data, $files) {
      $errors = array();
      global $DB, $CFG;
      if($data['regtype'] == 0){
         $usersaudiid =$data['saudid'];
         if(empty($data['saudid'])){
            $errors['saudid'] = get_string('saudinotempty','auth_registration');
         }
         if($DB->record_exists('local_users',array('id_number'=>$data['saudid'],'deleted' =>0))){
            $errors['saudid'] = get_string('saudiidexists','auth_registration');
         }
         // TRK164_12118
         if (!empty(trim($data['saudid'])) && !is_numeric($data['saudid'])){
            $errors['saudid'] = get_string('requirednumeric','auth_registration');
         }
         if (!empty(trim($data['saudid'])) && strlen(trim($data['saudid'])) != 10){
            $errors['saudid'] = get_string('saudidonly10digits','auth_registration');
         }
      }

      if($data['regtype'] == 1){         

         if (empty(trim($data['firstname'])) && empty(trim($data['lastname']))){
           $errors['fullname'] = get_string('fullnamerequired','auth_registration');
         }
          if (empty(trim($data['firstname'])) && !empty(trim($data['lastname']))){
           $errors['fullname'] = get_string('firstnamerequired','auth_registration');
         }
          if (!empty(trim($data['firstname'])) && empty(trim($data['lastname']))){
           $errors['fullname'] = get_string('lastnamerequired','auth_registration');
         }

         if (empty(trim($data['firstnamearabic'])) && empty(trim($data['lastnamearabic']))){
         $errors['fullnamearabic'] = get_string('fullnamearabicrequired','auth_registration');
         }
         if (empty(trim($data['firstnamearabic'])) && !empty(trim($data['lastnamearabic']))){
          $errors['fullnamearabic'] = get_string('firstnamearabicrequired','auth_registration');
         }
         if (!empty(trim($data['firstnamearabic'])) && empty(trim($data['lastnamearabic']))){
           $errors['fullnamearabic'] = get_string('lastnamearabicrequired','auth_registration');
         }
         if (empty(trim($data['middlenameen'])) && empty(trim($data['thirdnameen']))){
           $errors['enmiddleandthirdname'] = get_string('enmiddleandthirdnamerequired','auth_registration');
         }
         if (empty(trim($data['middlenamearabic'])) && empty(trim($data['thirdnamearabic']))){
           $errors['armiddleandthirdname'] = get_string('armiddleandthirdnamerequired','auth_registration');
         }

         if (empty(trim($data['middlenameen'])) && !empty(trim($data['thirdnameen']))){
          $errors['enmiddleandthirdname'] = get_string('middlenameenrequired','auth_registration');
         }
         if (!empty(trim($data['middlenameen'])) && empty(trim($data['thirdnameen']))){
          $errors['enmiddleandthirdname'] = get_string('thirdnameenrequired','auth_registration');
         }

          if (empty(trim($data['middlenamearabic'])) && !empty(trim($data['thirdnamearabic']))){
          $errors['armiddleandthirdname'] = get_string('middlenamearabicrequired','auth_registration');
         }
         if (!empty(trim($data['middlenamearabic'])) && empty(trim($data['thirdnamearabic']))){
          $errors['armiddleandthirdname'] = get_string('thirdnamearabicrequired','auth_registration');
         }
      
         // if (!empty(trim($data['firstname'])) && !empty(trim($data['lastname'])) && (!preg_match('/^[a-zA-Z.,]*$/',trim($data['firstname'])) || !preg_match('/^[a-zA-Z.,]*$/',trim($data['lastname'])))) {
         //   $errors['fullname'] = get_string('acceptsstringsanddotonly', 'auth_registration');
         // }
        if (empty($data['country']) && empty(trim($data['city']))){
          $errors['country_city'] = get_string('country_cityrequired','auth_registration');
        }
        if (empty($data['country']) && !empty(trim($data['city']))){
           $errors['country_city'] = get_string('countryrequired','auth_registration');
        }
        if (!empty($data['country']) && empty(trim($data['city']))){
           $errors['country_city'] = get_string('cityrequired','auth_registration');
        }
         // if (!empty(trim($data['city'])) && !preg_match('/^[a-zA-Z, ]*$/',trim($data['city']))) {
         //    $errors['country_city'] = get_string('cityacceptsstringsandspacesonly', 'auth_registration');
         // }
         if (empty($data['gender']) || is_null($data['gender']))  {
            $errors['gender'] = get_string('genderrequired', 'auth_registration');
         }
         if (empty($data['lang']))  {
            $errors['lang'] = get_string('langrequired', 'auth_registration');
         }
         if (empty($data['id_number']))  {
            $errors['id_number'] = get_string('id_numberrequired', 'auth_registration');
         }
         
         if (!empty(trim($data['id_number'])) && $data['id_type'] != 2 && !is_numeric($data['id_number'])){
           $errors['id_number'] = get_string('requirednumeric','auth_registration');
         }

         // if (!empty(trim($data['id_number'])) && strlen(trim($data['id_number'])) < 10) {

         //   $errors['id_number'] = get_string('lengthcantbelowerthan10', 'auth_registration');

         // }
         // if (!empty(trim($data['id_number'])) && strlen(trim($data['id_number'])) > 10 && strlen(trim($data['id_number'])) > 10) {
         //   $errors['id_number'] = get_string('lengthcantbemorethan10', 'auth_registration');
         // }
      
         // if(isset($data['organization']) && empty(trim($data['organization']))){
         //    $errors['organization'] = get_string('organizationrequired','auth_registration');
         // }
         
         // if(isset($data['jobrole_level']) && empty(trim($data['jobrole_level']))){
         //    $errors['ord_details_2'] = get_string('jobrole_levelrequired','auth_registration');
         // }
         //  if(isset($data['jobrole']) && empty(trim($data['jobrole']))){
         //    $errors['ord_details_2'] = get_string('jobrolerequired','auth_registration');
         // }
         // if(isset($data['jobfamily']) && empty(trim($data['jobfamily']))){
         //    $errors['ord_details_2'] = get_string('jobfamilyrequired','auth_registration');
         // }
         // if(isset($data['segment']) && empty(trim($data['segment']))){
         //    $errors['ord_details_2'] = get_string('segmentrequired','auth_registration');
         // }
         // if(isset($data['sectors']) && empty(trim($data['sectors']))){
         //    $errors['ord_details_2'] = get_string('sectorrequired','auth_registration');
         // }

         // if(empty($data['sectors']) || empty($data['segment']) || empty($data['jobfamily'])  || empty($data['jobrole'])  || empty($data['jobrole_level'])  ){
         //    $errors['ord_details_2'] = get_string('dependenciesarenotempty','auth_registration');
         // }

         // if(!empty($data['sectors']) && (empty($data['segment']) || empty($data['jobfamily'])  || empty($data['jobrole'])  || empty($data['jobrole_level']))){
         //       $errors['ord_details_2'] = get_string('dependenciesexceptsectorarenotempty','auth_registration');
         // }
         // if(!empty($data['sectors']) && !empty($data['segment'])  && (empty($data['jobfamily'])  || empty($data['jobrole'])  || empty($data['jobrole_level']))){
         //       $errors['ord_details_2'] = get_string('dependenciesexceptsector_segmentarenotempty','auth_registration');
         // }

         // if(!empty($data['sectors']) && !empty($data['segment']) && !empty($data['jobfamily']) && (empty($data['jobrole'])  || empty($data['jobrole_level']))){
         //       $errors['ord_details_2'] = get_string('dependenciesexceptsector_segment_jobfamilyarenotempty','auth_registration');
         // }

         // if(!empty($data['sectors']) && !empty($data['segment']) && !empty($data['jobfamily']) && !empty($data['jobrole']) &&  empty($data['jobrole_level'])){

         //       $errors['ord_details_2'] = get_string('dependenciesexceptsector_segment_jobfamily_jobrolearenotempty','auth_registration');
         // }

         
         if (empty($data['username']))  {
            $errors['username'] = get_string('usernamerequired', 'auth_registration');
         }

         if (!empty($data['username']) && !preg_match('/^[A-Za-z0-9_$%&#@.]+$/',$data['username'])) {
             $errors['username'] = get_string('requiredvalidusername', 'auth_registration');
         }
         if (empty($data['password']))  {
            $errors['password'] = get_string('passwordrequired', 'auth_registration');
         }
         $errmsg = ''; // Prevent eclipse warning.
        if (!empty($data['password']) && !check_password_policy($data['password'], $errmsg)) {

            $errors['password'] = $errmsg;
        }
         if (empty($data['confirm_password']))  {
            $errors['confirm_password'] = get_string('confirm_passwordrequired', 'auth_registration');
         }
         if (empty($data['email'])) {
            $errors['email'] = get_string('emailrequired', 'auth_registration');
         }
         // if (!empty($data['email']) && !validate_email($data['email'])) {
         //     $errors['email'] = get_string('requiredvalidemail', 'auth_registration');
         // }
          if (!empty($data['email']) &&  !preg_match('/^[A-Za-z0-9._%+-]+@[a-z0-9.-]+\.[a-z]{2,8}$/',$data['email'])) {

            $errors['email'] = get_string('requiredvalidemail', 'auth_registration');
         }

         if (empty($data['phone1']))  {
           $errors['country_phone'] = get_string('phone1required', 'auth_registration');
         }
        if (!empty(trim($data['phone1'])) && !is_numeric($data['phone1'])){
           $errors['country_phone'] = get_string('requirednumeric','auth_registration');
        }
         if (empty($data['country_code']))  {
           $errors['country_phone'] = get_string('country_coderequired', 'auth_registration');
         }
        if (!empty(trim($data['country_code'])) && !is_numeric($data['country_code'])){
           $errors['country_phone'] = get_string('requirednumeric','auth_registration');
        }
        // if  (!empty(trim($data['phone1'])) && is_numeric(trim($data['phone1'])) && ( strlen(trim($data['phone1'])) < 5  || strlen(trim($data['phone1'])) > 10 )) {
        //     $errors['phone1'] = get_string('minimum5digitsallowed','auth_registration');
        // }
        // if (!empty(trim($data['phone1'])) && is_numeric(trim($data['phone1'])) && (strlen(trim($data['phone1'])) >= 5  &&  strlen(trim($data['phone1'])) <= 10) &&  !preg_match('/^[5-9][0-9]/',trim($data['phone1']))) {
        //     $errors['phone1'] = get_string('startswith5','auth_registration');
        // }


         if ($data['confirm_password'] != $data['password'])  {
            $errors['confirm_password'] = get_string('confirmpasswordnotmatchederror', 'auth_registration');
         }
         if ($DB->record_exists('local_users', array('username' => $data['username'],'deleted' => 0))) {
            $errors['username'] = get_string('usernameexisterror', 'auth_registration');
         }
         if ($DB->record_exists('local_users', array('email' => $data['email'],'deleted' => 0))) {
            $errors['email'] = get_string('emailexisterror', 'auth_registration');
         }
         if ($DB->record_exists('local_users', array('id_number' => $data['id_number'],'deleted' => 0))) {
            $errors['id_number'] = get_string('id_numberexisterror', 'local_userapproval');
         }
         if ($DB->record_exists('local_users', array('phone1' => $data['phone1'],'deleted' => 0))) {
            $errors['phone1'] = get_string('mobileexisterror', 'auth_registration');
         }
      }
      return $errors;
   }  
}
