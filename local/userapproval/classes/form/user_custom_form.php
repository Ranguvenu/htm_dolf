<?php
namespace local_userapproval\form;
use core_form\dynamic_form ;
use moodle_url;
use context;
use context_system;
use html_writer;
use render_from_template;
use local_userapproval\action\manageuser as manageuser;
class user_custom_form extends  dynamic_form{
    public function definition() {
      global $CFG,$DB,$PAGE,$OUTPUT,$USER;
      $mform = $this->_form; // Don't forget the underscore!
      $id = $this->optional_param('id', 0, PARAM_INT);
      $userid = $this->optional_param('userid', 0, PARAM_INT);

      $lang  = current_language();

      $systemcontext = context_system::instance();

      $mform->addElement('hidden', 'id',$id);
      $mform->setType('int', PARAM_INT);

      $mform->addElement('hidden', 'userid',$userid);
      $mform->setType('int', PARAM_INT);
     
      $mform->addElement('filepicker', 'bannerimages', get_string('bannerimage','local_userapproval'), null,
      array('maxbytes' => $maxbytes, 'accepted_types' => '*'));
    
      $mform->addElement('filepicker', 'profileimages', get_string('profileimage','local_userapproval'), null,
      array('maxbytes' => $maxbytes, 'accepted_types' => '*'));

      $userrecord = $DB->get_record('local_users',['id'=>$id]);
      $lang = current_language();
    
      $firstname =  ($lang == 'ar') ? $userrecord->firstnamearabic : $userrecord->firstname; 
      $lastname =  ($lang == 'ar') ? $userrecord->lastnamearabic : $userrecord->lastname; 
      $middlename =  ($lang == 'ar') ? $userrecord->middlenamearabic : $userrecord->middlenameen; 
      $thirdname =  ($lang == 'ar') ? $userrecord->thirdnamearabic : $userrecord->thirdnameen; 

      $displaycontactnumber = (substr($userrecord->phone1,0,3) == 966) ? str_replace('966','',$userrecord->phone1) : $userrecord->phone1;

      $mform->addElement('static', 'firstnamestatic',  get_string('first_name', 'local_userapproval'),$firstname);
      $mform->addElement('static', 'lastnamestatic',  get_string('last_name', 'local_userapproval'),$lastname);
      $mform->addElement('static', 'middlenamestatic',  get_string('middle_name', 'local_userapproval'),$middlename);
      $mform->addElement('static', 'thirdnamestatic',  get_string('third_name', 'local_userapproval'),$thirdname);
      $mform->addElement('text', 'email',  get_string('email', 'local_userapproval'));
      $mform->setType('email', PARAM_NOTAGS); 
      $mform->setDefault('text','');
      // $mform->addElement('static', 'contactstatic',  get_string('contact', 'local_userapproval'),$displaycontactnumber);
      $mform->addElement('text', 'contact',  get_string('contact', 'local_userapproval'));
      $mform->setType('contact', PARAM_NOTAGS); 
      $mform->setDefault('text','');

      $mform->addElement('static', 'idnumberstatic',  get_string('idnumber', 'local_userapproval'),$userrecord->id_number);
      $mform->addElement('static', 'citystatic',  get_string('city', 'local_userapproval'),$userrecord->city);
       $gendername = ($userrecord->gender) ? (($userrecord->gender == 1) ? get_string('male','local_userapproval') : get_string('female','local_userapproval')): '';
      $mform->addElement('static', 'genderstatic',  get_string('gender', 'local_userapproval'),$gendername);
      $mform->addElement('static', 'dob',  get_string('dateofbirth', 'local_userapproval'),$userrecord->dateofbirth ? userdate($userrecord->dateofbirth,get_string('strftimedatemonthabbr', 'core_langconfig')) : 'N/A') ;
      $countries = get_string_manager()->get_list_of_countries();
      $mform->addElement('static', 'countrystatic',  get_string('country', 'local_userapproval'),$countries[$userrecord->country]);


      $mform->addElement('hidden', 'idnumber', $userrecord->id_number);
      $mform->setType('idnumber', PARAM_RAW);

      $mform->addElement('hidden', 'country', $userrecord->country);
      $mform->setType('country', PARAM_TEXT);

      $mform->addElement('hidden', 'city', $userrecord->city);
      $mform->setType('city', PARAM_TEXT);

      $mform->addElement('hidden', 'gender', $userrecord->gender);
      $mform->setType('gender', PARAM_INT);

      $mform->addElement('hidden', 'firstname', $userrecord->firstname);
      $mform->setType('firstname', PARAM_TEXT);

      $mform->addElement('hidden', 'lastname', $userrecord->lastname);
      $mform->setType('lastname', PARAM_TEXT);

      $mform->addElement('hidden', 'firstnamearabic', $userrecord->firstnamearabic);
      $mform->setType('firstnamearabic', PARAM_TEXT);

      $mform->addElement('hidden', 'lastnamearabic', $userrecord->lastnamearabic);
      $mform->setType('lastnamearabic', PARAM_TEXT);


      $mform->addElement('hidden', 'middlenameen', $userrecord->middlenameen);
      $mform->setType('middlenameen', PARAM_TEXT);

      $mform->addElement('hidden', 'thirdnameen', $userrecord->thirdnameen);
      $mform->setType('thirdnameen', PARAM_TEXT);


      $mform->addElement('hidden', 'middlenamearabic', $userrecord->middlenamearabic);
      $mform->setType('middlenamearabic', PARAM_TEXT);

      $mform->addElement('hidden', 'thirdnamearabic', $userrecord->thirdnamearabic);
      $mform->setType('thirdnamearabic', PARAM_TEXT);

      $mform->addElement('hidden', 'dateofbirth', $userrecord->dateofbirth);
      $mform->setType('dateofbirth', PARAM_TEXT);

      $mform->addElement('hidden', 'contact', $userrecord->phone1);
      $mform->setType('contact', PARAM_TEXT);

      $mform->addElement('hidden', 'email', $userrecord->email);
      $mform->setType('email', PARAM_TEXT);
          

      // if(!is_siteadmin()){
      //   $mform->addElement('text', 'certificates',  get_string('certificates', 'local_userapproval'));
      //   $mform->addRule('certificates', get_string('certificates_numeric','local_userapproval'), 'numeric');
      // }

      $mform->addElement('textarea', 'address',  get_string('address', 'local_userapproval'));
      $mform->setType('address', PARAM_NOTAGS); 
      $mform->setDefault('textarea','');
      //$mform->addRule('address', get_string('address','local_userapproval'), 'required');         
         
      if(!is_siteadmin() && has_capability('local/organization:manage_trainee', $systemcontext) && !$DB->record_exists('organization_requests',array('userid' => $userid,'status' => 1))){
        $organizations = ['' => get_string('selectorganisation','local_userapproval')];
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
          );
          $mform->addElement('autocomplete','organization',get_string('organization','local_userapproval'),$organizations,$attributes);
       }

       if($DB->get_record('organization_requests',array('userid' => $userid, 'status' => 1))) {
            
            $orginfo = $DB->get_record_sql('SELECT * 
                                              FROM {organization_requests} 
                                             WHERE userid=:userid AND status != 2
                                             ORDER by id desc', array('userid' => $userid));
            $organizationname = $DB->get_field('local_organization','fullname',array('id'=>$orginfo->orgid));
            if ($orginfo->status == 1) {
                $requeststatus = get_string('pending', 'local_userapproval');
            } else if($orginfo->status == 2) {
                $requeststatus = get_string('approved', 'local_userapproval');
            } else {
                $requeststatus=  get_string('rejected', 'local_userapproval');
            }
            $data=[
                'organizationname'=>$organizationname,
                'requeststatus'=>$requeststatus,
                'orgid'=>$orginfo->orgid,
                'userid'=>$userid,
                'title'=>get_string('remove_request', 'local_userapproval'),
                'action'=>(($orginfo->status == 1 || $orginfo->status == 2) && !is_siteadmin()) ? true : false,
            ];

            $statictext = $OUTPUT->render_from_template('local_userapproval/orgrequeststaticdisplay', $data);
            $mform->addElement('static', '', get_string('yourorganization', 'local_userapproval'),$statictext);
        }

       $sectors = manageuser::org_sector();

       $sectoroptions = array(
            'onchange' => "(function(e){ require(['local_trainingprogram/dynamic_dropdown_ajaxdata'], function(s) {s.sectorschanged();}) }) (event)",
            'class' => 'el_sectorlist',
        );
       
       $mform->addElement('autocomplete', 'sectors',get_string('sector', 'local_userapproval'),$sectors,$sectoroptions);
       //$mform->addRule('sectors', get_string('sectorrequired', 'local_userapproval'), 'required');

      $sectorid = $this->_ajaxformdata['sectors'];

           $segmentslist = $this->_ajaxformdata['segment'];
        if (!empty($segmentslist)) {

          
         $segments = manageuser::org_segment(0,array($segmentslist),$id);

        }elseif ($id > 0) {


          $segments = manageuser::org_segment(0,array(),$id);
        }

       $segmentdattributes = array(
         'ajax' => 'local_trainingprogram/sector_datasource',
         'data-type' => 'segment',
         'id' => 'el_segmentlist',
         'data-sectorid' => $sectorid,
         'onchange' => "(function(e){ require(['local_trainingprogram/dynamic_dropdown_ajaxdata'], function(s) {s.segmentschanged();}) }) (event)",
       );
      $mform->addElement('autocomplete', 'segment',get_string('segment', 'local_userapproval'),$segments,$segmentdattributes);
      //$mform->addRule('segment', get_string('segmentrequired', 'local_userapproval'), 'required');

      $jobfamilies = array();
      $jobfamilieslist = $this->_ajaxformdata['jobfamily'];

      if (!empty($jobfamilieslist)) {

         $jobfamilies = manageuser::org_jobfamily(0,array($jobfamilieslist ),$id);

      } elseif ($id > 0) {

         $jobfamilies = manageuser::org_jobfamily(0,array(),$id);

      }

      $jfdattributes = array(
         'ajax' => 'local_trainingprogram/sector_datasource',
         'data-type' => 'jobfamily',
         'id' => 'el_jobfamily',
         'data-segmentid' => $segmentslist,
         'onchange' => "(function(e){ require(['local_trainingprogram/dynamic_dropdown_ajaxdata'], function(s) {s.jfamilychanged();}) }) (event)",
      );

        $mform->addElement('autocomplete', 'jobfamily',  get_string('selectjob_family', 'local_userapproval'),$jobfamilies, $jfdattributes);
        //$mform->addRule('jobfamily', get_string('jobfamilyrequired', 'local_userapproval'), 'required');

      $jobroles = array();
      $jobroleslist = $this->_ajaxformdata['jobrole'];


      if (!empty($jobroleslist)) {

        $jobroles = manageuser::org_jobrole(0,array($jobroleslist),$id);

      } elseif ($id > 0) {

        $jobroles = manageuser::org_jobrole(0,array(),$id);
            
      }

      $jrattributes = array(
         'ajax' => 'local_trainingprogram/sector_datasource',
         'data-type' => 'jobrole',
         'id' => 'el_jobroles',
         'data-jobfamilyid' => $jobroleslist,
         'noselectionstring' => get_string('jobrole', 'local_userapproval'),
      );

        $mform->addElement('autocomplete', 'jobrole',  get_string('jobrole', 'local_userapproval'),$jobroles,$jrattributes);
        //$mform->addRule('jobrole', get_string('jobrolerequired', 'local_userapproval'), 'required');

        $mform->addElement('text', 'linkedin',  get_string('linkedin', 'local_userapproval'));
        $mform->setType('linkedin', PARAM_NOTAGS); 
        $mform->setDefault('text','');

        $passwordclassarray = array('class' => 'dynamic_form_password');  
        $mform->addElement('passwordunmask','password',get_string('password', 'local_userapproval'),array('placeholder'=>get_string('password', 'local_userapproval')),$passwordclassarray); // Add elements to your form
        $mform->addHelpButton('password', 'passwordhint', 'local_userapproval');
        $mform->setType('password', PARAM_NOTAGS);
   
        $dynamic_form_confirm_passwordclassarray = array('class' => 'dynamic_form_confirm_password');  
        $mform->addElement('passwordunmask','confirm_password',get_string('confirm_password', 'local_userapproval'),array('placeholder'=>get_string('confirm_password', 'local_userapproval')),$dynamic_form_confirm_passwordclassarray); // Add elements to your form
        $mform->addHelpButton('confirm_password', 'confirm_passwordhint', 'local_userapproval');
        $mform->setType('confirm_password', PARAM_NOTAGS);
   

        $mform->addElement('hidden', 'status');
        $mform->setType('int', PARAM_INT);  
                    
    }                                                             
    public function validation($data, $files) {
        global $DB;
        $errors = parent::validation($data, $files);


       if (empty(trim($data['firstname'])) && empty(trim($data['lastname']))){
        $errors['fullname'] = get_string('fullnamerequired','local_userapproval');
       }
       if (empty(trim($data['firstname'])) && !empty(trim($data['lastname']))){
        $errors['fullname'] = get_string('firstnamerequired','local_userapproval');
      }
       if (!empty(trim($data['firstname'])) && empty(trim($data['lastname']))){
        $errors['fullname'] = get_string('lastnamerequired','local_userapproval');
      }

      // if (empty(trim($data['firstnamearabic'])) && empty(trim($data['lastnamearabic']))){
      //   $errors['fullnamearabic'] = get_string('fullnamearabicrequired','local_userapproval');
      // }
      //  if (empty(trim($data['firstnamearabic'])) && !empty(trim($data['lastnamearabic']))){
      //   $errors['fullnamearabic'] = get_string('firstnamearabicrequired','local_userapproval');
      // }
      //  if (!empty(trim($data['firstnamearabic'])) && empty(trim($data['lastnamearabic']))){
      //   $errors['fullnamearabic'] = get_string('lastnamearabicrequired','local_userapproval');
      // }

      // if (empty(trim($data['middlenameen'])) && empty(trim($data['thirdnameen']))){
      //    $errors['enmiddleandthirdname'] = get_string('enmiddleandthirdnamerequired','local_userapproval');
      // }
      // if (empty(trim($data['middlenamearabic'])) && empty(trim($data['thirdnamearabic']))){
      //   $errors['armiddleandthirdname'] = get_string('armiddleandthirdnamerequired','local_userapproval');
      // }

      // if (empty(trim($data['middlenameen'])) && !empty(trim($data['thirdnameen']))){
      //  $errors['enmiddleandthirdname'] = get_string('middlenameenrequired','local_userapproval');
      // }
      // if (!empty(trim($data['middlenameen'])) && empty(trim($data['thirdnameen']))){
      //  $errors['enmiddleandthirdname'] = get_string('thirdnameenrequired','local_userapproval');
      // }

      // if (empty(trim($data['middlenamearabic'])) && !empty(trim($data['thirdnamearabic']))){
      //  $errors['armiddleandthirdname'] = get_string('middlenamearabicrequired','local_userapproval');
      // }
      
      // if (!empty(trim($data['middlenamearabic'])) && empty(trim($data['thirdnamearabic']))){
      //  $errors['armiddleandthirdname'] = get_string('thirdnamearabicrequired','local_userapproval');
      // }
        $url=$data['linkedin'];
        
        if (!empty(trim($data['linkedin']))  && !preg_match('/\b(?:(?:https?|ftp):\/\/|www\.)[-a-z0-9+&@#\/%?=~_|!:,.;]*[-a-z0-9+&@#\/%=~_|]/i', $data['linkedin']) ) {
            $errors['linkedin'] = get_string('linkedin_err', 'local_userapproval'); 
        }

       //  if (!empty(trim($data['idnumber'])) && !preg_match('/^[a-zA-Z0-9,]*$/',trim($data['idnumber']))) {
       //      $errors['idnumber'] = get_string('acceptsstringsandnumericonly', 'local_userapproval');
       //  }
       // /* if (!empty(trim($data['city'])) && !preg_match('/^[a-zA-Z, ]*$/',trim($data['city']))) {
       //      $errors['city'] = get_string('cityacceptsstringsandspacesonly', 'local_userapproval');
       //  }*/
        if (empty($data['contact']))  {
          $errors['contact'] = get_string('phone1required', 'local_userapproval');
        }
        if (!empty(trim($data['contact'])) && !is_numeric($data['contact'])){
            $errors['contact'] = get_string('requirednumeric','local_userapproval');
        }
        if  (!empty(trim($data['contact'])) && is_numeric(trim($data['contact'])) && ( strlen(trim($data['contact'])) < 5  || strlen(trim($data['contact'])) > 10 )) {
            $errors['contact'] = get_string('minimum5digitsallowed','local_userapproval');
        }
        if (!empty(trim($data['contact'])) && is_numeric(trim($data['contact'])) && (strlen(trim($data['contact'])) >= 5  &&  strlen(trim($data['contact'])) <= 10) &&  !preg_match('/^[5-9][0-9]/',trim($data['contact']))) {
            $errors['contact'] = get_string('startswith5','local_userapproval');
        }

        if(!empty(trim($data['certificates'])) && !preg_match('/^[0-9]*$/',trim($data['certificates']))) {
            $errors['certificates'] = get_string('validcirtificatecountrequired', 'local_userapproval'); 
        }

        if (!empty($data['password']) && !check_password_policy($data['password'], $errmsg)) {

            $errors['password'] = $errmsg;
        }
        if ($data['confirm_password'] != $data['password'])  {
            $errors['confirm_password'] = get_string('confirmpasswordnotmatchederror', 'local_userapproval');
        }

        return $errors;
    }
    protected function get_context_for_dynamic_submission(): context {
        return \context_system::instance();
    }
    protected function check_access_for_dynamic_submission(): void {
       // require_capability('moodle/site:config', $this->get_context_for_dynamic_submission());    
    }
    public function process_dynamic_submission() {
        global $CFG, $DB,$USER;
        $context= context_system::instance();
        $data = $this->get_data();
        if ($data){
            if ($data->id >0){
                
              $customdata = (new manageuser)->create_customization($data);
              $currentorganization = $DB->get_field('local_users', 'organization', ['userid' => $USER->id]);
              if($currentorganization != $data->organization){
                $orgrequestcustomdat = (new manageuser)->organization_request_update_record($data->organization,$data->userid);
              }
              
              $profile=$data->profileimages;
            }
        }
    }
    public function set_data_for_dynamic_submission(): void {
        global $DB;
    
        if ($id = $this->optional_param('id', 0, PARAM_INT)) {
            $userid = $this->optional_param('userid', 0, PARAM_INT);

            $sql= "SELECT u.id as id,u.*,lu.userid as userid, lu.id as ids,lu.*
                     FROM {user} as u   
                     JOIN {local_users} as lu ON u.id = lu.userid
                    WHERE lu.id=$id";
            
            $result=$DB->get_record_sql($sql);
            $countries = get_string_manager()->get_list_of_countries();

            $result->country=$countries[$result->country];
            $itemid =$result->picture;
            $fs = get_file_storage();
            $files = $fs->get_area_files($context->id, 'local_userapproval', 'profileimage', $itemid);
            foreach($files as $file){
                $url = moodle_url::make_pluginfile_url($file->get_contextid(), $file->get_component(), $file->get_filearea(),
                $file->get_itemid(), $file->get_filepath(), $file->get_filename(), false);
                $result->picture=$url;
            }
               $banneritemid =$result->bannerimage;
                $bannerfs = get_file_storage();
                $bannerfiles = $fs->get_area_files($context->id, 'local_userapproval', 'profileimage', $banneritemid);
                foreach($bannerfiles as $bannerfile){
                    $bannerurl = moodle_url::make_pluginfile_url($bannerfile->get_contextid(), $bannerfile->get_component(), $bannerfile->get_filearea(),
                    $bannerfile->get_itemid(), $bannerfile->get_filepath(), $bannerfile->get_filename(), false);
                    $result->bannerimage=$bannerurl;
                
            }

            if( $result->organization > 0 ) {
                $organization = $result->organization;
            
            } else {
                if($DB->get_record('organization_requests',array('userid' => $userid, 'status' => 1))){
                    $organization = $DB->get_field_sql('SELECT orgid 
                                                          FROM {organization_requests} 
                                                         WHERE userid=:userid AND status=:status
                                                         ORDER by id desc',
                                    array('userid' => $userid, 'status' => 1));
                }
            }
    
            $this->set_data(['id'=>$result->id,
                            'bannerimages' =>  $result->bannerimage,
                            'firstname'=>$result->firstname,
                            'lastname'=>$result->lastname,
                            'address'=>$result->address,
                            'profileimages'=> $result->picture, 
                            'fullname'=>$result->firstname.$result->lastname, 
                            'email'=>$result->email,
                            'contact'=>$result->phone1,
                            'city'=>$result->city,
                            'gender'=>$result->gender,
                            'country'=>$result->nationality,
                            'address'=>$result->address,
                            'idnumber'=>$result->id_number,
                            'organization'=>$organization ,
                            'segment'=>$result->segment,
                            'sectors'=>$result->sector ,
                            'jobfamily'=>$result->jobfamily ,
                            'jobrole'=>$result->jobrole ,
                            'linkedin'=>$result->linkedinprofile,
                            'certificates'=>$result->certificates,
                            'password'=>$result->confirm_password,
                            'confirm_password'=>$result->confirm_password,
                        
                        ]);
           
        }
    }    
    protected function get_page_url_for_dynamic_submission(): moodle_url {
        $id = $this->optional_param('id', 0, PARAM_INT);
        return new moodle_url('/local/local_userapproval/index.php');
    }
}

