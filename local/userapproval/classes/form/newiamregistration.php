<?php
namespace local_userapproval\form;
use moodle_url;
use context;
use context_system;
use local_userapproval\action\manageuser as manageuser;
class newiamregistration extends \moodleform {
   public function definition() {
     global $USER, $CFG,$DB;
     $corecomponent = new \core_component();
     $mform = $this->_form;
     $userrecord = $DB->get_record('local_users',['userid'=>$USER->id,'deleted'=>0]);
     $mform->addElement('hidden', 'autocomplete', 'false');

     $USER->firstnamearabic = $userrecord->firstnamearabic;
     $USER->lastnamearabic = $userrecord->lastnamearabic;
     $USER->middlenameen = $userrecord->middlenameen;
     $USER->thirdnameen = $userrecord->thirdnameen;
     $USER->middlenamearabic = $userrecord->middlenamearabic;
     $USER->thirdnamearabic = $userrecord->thirdnamearabic;
     $USER->dateofbirth = $userrecord->dateofbirth;
     $USER->usersource = $userrecord->usersource;
     $USER->gender = $userrecord->gender;
     $USER->id_type = ($userrecord->nationality == 'SA') ? 3 : 4;

     $USER->sectors = $userrecord->sector;
     $USER->segment = $userrecord->segment;
     $USER->jobfamily = $userrecord->jobfamily;
     $USER->jobrole = $userrecord->jobrole;
     $USER->jobrole_level = $userrecord->jobrole_level;
     $USER->organization = $userrecord->organization;

      $gendername = ($USER->gender) ? (($USER->gender == 1) ? get_string('male','local_userapproval') : get_string('female','local_userapproval')): '';

      $fullnamegroupelemnts=array();

      $mform->addElement('static','fullnameenglishname',\html_writer::tag('h5', get_string('fullnameenglish', 'local_userapproval')),$USER->firstname.' '.$USER->middlenameen.' '.$USER->thirdnameen.' '.$USER->lastname); // Add elements to your 
      $mform->setType('fullnameenglish', PARAM_TEXT);

      $mform->addElement('static','fullnamearabicname',\html_writer::tag('h5', get_string('fullnamearabic', 'local_userapproval')),$USER->firstnamearabic.' '.$USER->middlenamearabic.' '.$USER->thirdnamearabic.' '.$USER->lastnamearabic); // Add elements to your 
      $mform->setType('fullnamearabic', PARAM_TEXT);

      $mform->addElement('static','gendername',\html_writer::tag('h5', get_string('gender', 'local_userapproval')),$gendername); // Add elements to your 
      $mform->setType('gender', PARAM_TEXT);
      // $fullnamegroupelemnts[] = $mform->createElement('text', 'firstname','', array('placeholder'=>get_string('firstname', 'local_userapproval'),'class' => 'dynamic_form_id_firstname'));
     // $fullnamegroupelemnts[] = $mform->createElement('text', 'lastname','', array('placeholder'=>get_string('lastname', 'local_userapproval'),'class' => 'dynamic_form_id_lastname'));
     // $mform->addGroup($fullnamegroupelemnts, 'fullname',\html_writer::tag('h5', get_string('fullnameenglish', 'local_userapproval')), array('class' => 'fullname','disabled'=>'disabled'), false);

     // $fullnamearabicgroupelemnts=array();
     // $fullnamearabicgroupelemnts[] = $mform->createElement('text', 'firstnamearabic','', array('placeholder'=>get_string('firstnamearabic', 'local_userapproval'),'class' => 'dynamic_form_id_firstname'));
     // $fullnamearabicgroupelemnts[] = $mform->createElement('text', 'lastnamearabic','', array('placeholder'=>get_string('lastnamearabic', 'local_userapproval'),'class' => 'dynamic_form_id_lastname'));
     //  $mform->addGroup($fullnamearabicgroupelemnts, 'fullnamearabic',\html_writer::tag('h5', get_string('fullnamearabic', 'local_userapproval')), array('class' => 'fullnamearabic'), false);
    
     // $genderclassarray = array('class' => 'dynamic_form_id_gender');
     // $gender = [];
     // $gender[''] = get_string('gender','local_userapproval');
     // $gender['1'] = get_string('male','local_userapproval');
     // $gender['2'] = get_string('female','local_userapproval');
     // $mform->addElement('select', 'gender',\html_writer::tag('h5', get_string('gender', 'local_userapproval')), $gender,$genderclassarray);

     $mform->addElement('static', 'userdateofbirth', \html_writer::tag('h5', get_string('dateofbirth', 'local_userapproval')) , $userrecord->dateofbirth ? userdate($userrecord->dateofbirth, get_string('strftimedatemonthabbr', 'core_langconfig')): '');


     $langclassarray = array('class' => 'dynamic_form_id_lang');
     $languages = get_string_manager()->get_list_of_languages();
     $defaultlanguage['ar'] = $languages['ar'];
     $languages = array_merge($defaultlanguage, $languages);
     $mform->addElement('select', 'lang',\html_writer::tag('h5', get_string('language', 'local_userapproval')), $languages, $langclassarray);
    
     // $nationalityclassarray = get_string_manager()->get_list_of_countries();
     $mform->addElement('static', 'usernationality',\html_writer::tag('h5', get_string('nationality', 'auth_registration')), get_string($userrecord->nationalitycountryid, 'local_userapproval'));

     $idtypestring = ($USER->id_type == 3) ? get_string('saudiid','auth_registration') : get_string('residentialid','auth_registration');
     
     $id_typeclassarray = array('class' => 'dynamic_form_id_type');
     $mform->addElement('static', 'idtype', \html_writer::tag('h5', get_string('id_type', 'local_userapproval')),$idtypestring,$id_typeclassarray);

     $id_numberclassarray = array('class' => 'dynamic_form_id_number','disabled'=>'disabled');
     $mform->addElement('static','useridnumber',\html_writer::tag('h5', get_string('idnumber', 'local_userapproval')),$USER->idnumber,$id_numberclassarray); // Add elements to your 
     $mform->setType('useridnumber', PARAM_TEXT);

     $organisationclassarray = array('class' => 'dynamic_form_organisation');

     $displayname = (current_language() == 'ar') ? 'fullnameinarabic' : 'fullname';

     $organisationlist=$DB->get_records_sql('SELECT id,'.$displayname.' as fullname FROM {local_organization} where visible=1 and status=2');
     $organisations=[];
      $organisations[''] = get_string('selectorganisation','local_userapproval');
     foreach ($organisationlist AS $organisation){
         $organisations[$organisation->id]=$organisation->fullname;
     }
     $mform->addElement('select','organization',\html_writer::tag('h5', get_string('select_organisation', 'local_userapproval')),$organisations,$organisations); // Add elements to your form
      $sectors =manageuser::org_sector();
      $sectorid =  $USER->sectors? $USER->sectors : $this->_ajaxformdata['sectors'];
      $segments = array();
      $segmentslist =$USER->segment ? $USER->segment : $this->_ajaxformdata['segment'];

      if (!empty($segmentslist)) {
         $segments = manageuser::org_segment(0,array($segmentslist));
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
      $jobfamilieslist = $USER->jobfamily ? $USER->jobfamily :$this->_ajaxformdata['jobfamily'];

      if (!empty($jobfamilieslist)) {

         $jobfamilies = manageuser::org_jobfamily(0,array($jobfamilieslist ));

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
      $jobroleslist = $USER->jobrole ? $USER->jobrole : $this->_ajaxformdata['jobrole'];

      if (!empty($jobroleslist)) {

         $jobroles = manageuser::org_jobrole(0,array($jobroleslist));

      } 
      $jobroles += array(null => get_string('selectjobrole', 'local_userapproval'));

      $jrattributes = array(
         'ajax' => 'local_trainingprogram/sector_datasource',
         'data-type' => 'jobrole',
         'id' => 'el_jobroles',
         'data-jobfamilyid' => $jobfamilieslist,
          'noselectionstring' => get_string('jobrole', 'local_userapproval'),
      );
        
     $levelclassarray = array('class' => 'dynamic_form_level', 'noselectionstring' => get_string('jobrole_level', 'local_userapproval'));   
     $levels = [];
     $levels[''] = get_string('jobrole_level','local_userapproval');
     $levels['level1'] = get_string('level-1','local_userapproval');
     $levels['level2'] = get_string('level-2','local_userapproval');
     $levels['level3'] = get_string('level-3','local_userapproval');
     $levels['level4'] = get_string('level-4','local_userapproval');
     $levels['level5'] = get_string('level-5','local_userapproval');
     $sectoroptions = array(
         'onchange' => "(function(e){ require(['local_trainingprogram/dynamic_dropdown_ajaxdata'], function(s) {s.sectorschanged();}) }) (event)",
         'class' => 'el_sectorlist',
         'data-id' => 'el_sectorlist',
         'noselectionstring' => get_string('sector', 'local_userapproval'),
     );

     $ord_details_groupelemnts=array();
     $ord_details_groupelemnts[] = $mform->createElement('autocomplete', 'sectors', '',$sectors,$sectoroptions);
      $ord_details_groupelemnts[] =$mform->createElement('autocomplete','segment','',$segments,$segmentdattributes);
     $ord_details_groupelemnts[] =$mform->createElement('autocomplete', 'jobfamily','',$jobfamilies, $jfdattributes);
     $ord_details_groupelemnts[] =$mform->createElement('autocomplete', 'jobrole','',$jobroles,$jrattributes);
     $ord_details_groupelemnts[] = $mform->createElement('autocomplete', 'jobrole_level','',$levels,$levelclassarray);
     $mform->addGroup($ord_details_groupelemnts, 'ord_details_2',\html_writer::tag('h5', get_string('organisation_details', 'local_userapproval')), array('class' => 'ord_details_2'), false);

     $usernameclassarray = array('class' => 'dynamic_form_username','disabled' => 'disabled');  
    
      if($USER->username) {
         $mform->addElement('static','userusername',\html_writer::tag('h5', get_string('username', 'local_userapproval')),$USER->username,$usernameclassarray); // Add elements to 
         $mform->addElement('hidden', 'username', $USER->username);
         $mform->setType('username', PARAM_TEXT);
      } else {
         $mform->addElement('text','username',\html_writer::tag('h5', get_string('username', 'local_userapproval')),array('placeholder'=>get_string('username', 'local_userapproval')),$usernameclassarray); // Add elements to your form
         $mform->addRule('username', get_string('required'), null);
         $mform->setType('username', PARAM_NOTAGS);
      }
     $passwordclassarray = array('class' => 'dynamic_form_password');  
     $mform->addElement('passwordunmask','password',\html_writer::tag('h5', get_string('password', 'local_userapproval')),array('placeholder'=>get_string('password', 'local_userapproval')),$passwordclassarray); // Add elements to your form
     $mform->addHelpButton('password', 'passwordhint', 'local_userapproval');
     $mform->setType('password', PARAM_NOTAGS);

     $dynamic_form_confirm_passwordclassarray = array('class' => 'dynamic_form_confirm_password');  
     $mform->addElement('passwordunmask','confirm_password',\html_writer::tag('h5', get_string('confirm_password', 'local_userapproval')),array('placeholder'=>get_string('confirm_password', 'local_userapproval')),$dynamic_form_confirm_passwordclassarray); // Add elements to your form
     $mform->addHelpButton('confirm_password', 'confirm_passwordhint', 'local_userapproval');
     $mform->setType('confirm_password', PARAM_NOTAGS);

     $dynamic_form_emailclassarray = array('class' => 'dynamic_form_email');  
     $mform->addElement('text','email',\html_writer::tag('h5', get_string('email', 'local_userapproval')),array('placeholder'=>get_string('email', 'local_userapproval')),$dynamic_form_emailclassarray); // Add elements to your form
     $mform->setType('email', PARAM_NOTAGS);
    
 
     $countryclassarray = array('class' => 'dynamic_form_country');
     $countries = get_string_manager()->get_list_of_countries();
     $cityclassarray = array('class' => 'dynamic_form_city');
     $country_citygroupelemnts=array();
     $country_citygroupelemnts[] = $mform->createElement('select', 'country','',$countries, $countryclassarray);
     $country_citygroupelemnts[] = $mform->createElement('text', 'city','',array('placeholder'=>get_string('city', 'local_userapproval'), $cityclassarray));
     $mform->addGroup($country_citygroupelemnts, 'country_city',\html_writer::tag('h5', get_string('countryandcity', 'local_userapproval')), array('class' => 'country_city'), false);
     $phone1classarray = array('class' => 'dynamic_form_phone1');
     $mform->addElement('text','phone1',\html_writer::tag('h5', get_string('mobile', 'local_userapproval')),array('placeholder'=>get_string('mobile', 'local_userapproval'),$phone1classarray));    

      $mform->addElement('submit', 'regsubmit', get_string('register', 'local_userapproval'),array('class'=>'iamregistreationsubmit btn-block'));

      $mform->addElement('hidden', 'userid', $USER->id);
      $mform->setType('userid', PARAM_INT);

      $mform->addElement('hidden', 'id_type', $USER->id_type);
      $mform->setType('id_type', PARAM_INT);

      $mform->addElement('hidden', 'nationality', $USER->country);
      $mform->setType('nationality', PARAM_TEXT);

      $mform->addElement('hidden', 'idnumber', $USER->idnumber);
      $mform->setType('idnumber', PARAM_RAW);
    
      $mform->addElement('hidden', 'country', $USER->country);
      $mform->setType('country', PARAM_TEXT);

      $mform->addElement('hidden', 'gender', $USER->gender);
      $mform->setType('gender', PARAM_INT);

      $mform->addElement('hidden', 'firstname', $USER->firstname);
      $mform->setType('firstname', PARAM_TEXT);

      $mform->addElement('hidden', 'lastname', $USER->lastname);
      $mform->setType('lastname', PARAM_TEXT);

      $mform->addElement('hidden', 'firstnamearabic', $USER->firstnamearabic);
      $mform->setType('firstnamearabic', PARAM_TEXT);

      $mform->addElement('hidden', 'lastnamearabic', $USER->lastnamearabic);
      $mform->setType('lastnamearabic', PARAM_TEXT);

      $mform->addElement('hidden', 'lastnamearabic', $USER->lastnamearabic);
      $mform->setType('lastnamearabic', PARAM_TEXT);

      $mform->addElement('hidden', 'lastnamearabic', $USER->lastnamearabic);
      $mform->setType('lastnamearabic', PARAM_TEXT);

      $mform->addElement('hidden', 'lastnamearabic', $USER->lastnamearabic);
      $mform->setType('lastnamearabic', PARAM_TEXT);

      $mform->addElement('hidden', 'middlenamearabic', $userrecord->middlenamearabic);
      $mform->setType('middlenamearabic', PARAM_TEXT);

       $mform->addElement('hidden', 'middlenameen', $userrecord->middlenameen);
      $mform->setType('middlenameen', PARAM_TEXT);

      $mform->addElement('hidden', 'thirdnamearabic', $userrecord->thirdnamearabic);
      $mform->setType('thirdnamearabic', PARAM_TEXT);

       $mform->addElement('hidden', 'thirdnameen', $userrecord->thirdnameen);
      $mform->setType('thirdnameen', PARAM_TEXT);

       $mform->addElement('hidden', 'dateofbirth', $userrecord->dateofbirth);
      $mform->setType('dateofbirth', PARAM_RAW);

      $mform->addElement('hidden', 'nationalitycountryid', $userrecord->nationalitycountryid);
      $mform->setType('nationalitycountryid', PARAM_INT);

      $mform->addElement('hidden', 'existinsystem', $USER->existinsystem);
      $mform->setType('existinsystem', PARAM_INT);

      $mform->addElement('hidden', 'usersource', $USER->usersource);
      $mform->setType('usersource', PARAM_RAW);

      $this->set_data($USER);

      $mform->disable_form_change_checker();
        
   }
   public function validation($data, $files) {
      $errors = array();
      global $DB, $CFG;

         if (empty($data['username']))  {
            $errors['username'] = get_string('usernamerequired', 'local_userapproval');
         }

         if (!empty($data['username']) && !preg_match('/^[A-Za-z0-9_$%&#@.]+$/',$data['username'])) {
             $errors['username'] = get_string('requiredvalidusername', 'local_userapproval');
         }
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
         if (empty($data['email'])) {
            $errors['email'] = get_string('emailrequired', 'local_userapproval');
         }
         if (!empty($data['email']) &&  !preg_match('/^[a-z0-9._%+-]+@[a-z0-9.-]+\.[a-z]{2,8}$/',$data['email'])) {

            $errors['email'] = get_string('requiredvalidemail', 'local_userapproval');
         }
         if (empty($data['phone1']))  {
           $errors['phone1'] = get_string('phone1required', 'local_userapproval');
         }
        if (!empty(trim($data['phone1'])) && !is_numeric($data['phone1'])){
           $errors['phone1'] = get_string('requirednumeric','local_userapproval');
        }
        if  (!empty(trim($data['phone1'])) && is_numeric(trim($data['phone1'])) && ( strlen(trim($data['phone1'])) < 5  || strlen(trim($data['phone1'])) > 10 )) {
            $errors['phone1'] = get_string('minimum5digitsallowed','local_userapproval');
        }
        if (!empty(trim($data['phone1'])) && is_numeric(trim($data['phone1'])) && (strlen(trim($data['phone1'])) >= 5  &&  strlen(trim($data['phone1'])) <= 10) &&  !preg_match('/^[5-9][0-9]/',trim($data['phone1']))) {
            $errors['phone1'] = get_string('startswith5','local_userapproval');
        }

       if ($data['confirm_password'] != $data['password'])  {
         $errors['confirm_password'] = get_string('confirmpasswordnotmatchederror', 'local_userapproval');
       }
      if ($data['userid'] == 0 && $DB->record_exists('local_users', array('username' => $data['username'],'deleted' => 0))) {
         $errors['username'] = get_string('usernameexisterror', 'local_userapproval');
      }
      return $errors;
   }  
}
