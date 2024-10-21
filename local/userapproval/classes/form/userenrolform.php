<?php
namespace local_userapproval\form;

use core_form\dynamic_form;
use moodle_url;
use context;
use context_system;
use local_userapproval;
use Exception;
use local_trainingprogram\local\trainingprogram as tp;

class userenrolform extends dynamic_form { 
    public function definition() {
        global $USER, $CFG,$DB;
        $corecomponent = new \core_component();

        $mform = $this->_form;
        $id = $this->optional_param('id', 0, PARAM_INT);

        $systemcontext = context_system::instance();
    
        $mform->addElement('hidden', 'id', $id);
        $mform->setType('id',PARAM_INT);
        $mform->addElement('hidden', 'payementtypes');
        $mform->setType('payementtypes',PARAM_INT);

        $mform->addElement('text','firstname', get_string('firstname', 'local_userapproval'));
        $mform->setType('firstname', PARAM_TEXT);
        
        $mform->addElement('text','firstnamearabic', get_string('firstnamearabic', 'local_userapproval'));
        $mform->setType('firstnamearabic', PARAM_TEXT);

        $mform->addElement('text','middlenamearabic', get_string('middlenamearabic', 'local_userapproval'));
        $mform->setType('middlenamearabic', PARAM_TEXT);

        $mform->addElement('text','middlenameen', get_string('middlenameen', 'local_userapproval'));
        $mform->setType('middlenameen', PARAM_TEXT);

        $mform->addElement('text','thirdnamearabic', get_string('thirdnamearabic', 'local_userapproval'));
        $mform->setType('thirdnamearabic', PARAM_TEXT);

        $mform->addElement('text','thirdnameen', get_string('thirdnameen', 'local_userapproval'));
        $mform->setType('thirdnameen', PARAM_TEXT);

        $mform->addElement('text','lastname', get_string('lastname', 'local_userapproval'));
        $mform->setType('lastname', PARAM_TEXT);

        $mform->addElement('text','lastnamearabic', get_string('lastnamearabic', 'local_userapproval'));
        $mform->setType('lastnamearabic', PARAM_TEXT);

        $mform->addElement('text','phone1', get_string('mobile', 'local_userapproval'));
        $mform->setType('phone1', PARAM_TEXT);
        
        $langclassarray = array('class' => 'dynamic_form_id_lang');
        $languages = get_string_manager()->get_list_of_languages();
        $defaultlanguage['ar'] = $languages['ar'];
        $languages = array_merge($defaultlanguage, $languages);
        $mform->addElement('select', 'lang',get_string('selectalanguage','local_userapproval'), $languages, $langclassarray);
        $mform->setType('lang', PARAM_TEXT);

        $genderdobgroupelemnts=array();
        $genderclassarray = array('class' => 'dynamic_form_id_gender');
        $gender = [];
        $gender[''] = get_string('gender','local_userapproval');
        $gender['1'] = get_string('male','local_userapproval');
        $gender['2'] = get_string('female','local_userapproval');
        $mform->addElement('select', 'gender',get_string('selectgender','local_userapproval'), $gender,$genderclassarray);

        $mform->addElement('text','email', get_string('email', 'local_userapproval'));
        $mform->setType('email', PARAM_TEXT);

        $mform->addElement('text','username', get_string('username', 'local_userapproval'));
        $mform->setType('username', PARAM_TEXT);
        $ssotype = [];
        $ssotypeclassarray = array('class' => 'dynamic_form_id_type');
        $ssotype['1'] = get_string('id','local_userapproval');
        $ssotype['2'] = get_string('passport','local_userapproval');
        $ssotype['3'] = get_string('saudiid','local_userapproval');
        $ssotype['4'] = get_string('residentialid','local_userapproval');
        $mform->addElement('select', 'ssotype',get_string('selectidtype','local_userapproval'), $ssotype,$ssotypeclassarray);
        $mform->setType('ssotype', PARAM_TEXT);


        $mform->addElement('text','id_number', get_string('idnumber', 'local_userapproval'));
        $mform->setType('id_number', PARAM_TEXT);

        $mform->addElement('text','nationalitycountryid', get_string('countrycode', 'local_userapproval'));
        $mform->setType('nationalitycountryid', PARAM_INT);

        /*$mform->addElement('text','organizationshortcode', get_string('organizationshortcode', 'local_userapproval'));
        $mform->setType('organizationshortcode', PARAM_TEXT);*/


    }
    public function validation($data, $files) {
        global $DB, $CFG;
         $errors = parent::validation($data, $files);
           if (empty(trim($data['firstname']))){
             $errors['firstname'] = get_string('firstnamerequired','local_userapproval');
            }
            if (empty(trim($data['firstnamearabic']))){
             $errors['firstnamearabic'] = get_string('firstnamearabicrequired','local_userapproval');
            }
            if (empty(trim($data['middlenamearabic']))){
             $errors['middlenamearabic'] = get_string('middlenamearabicrequired','local_userapproval');
            }

             if (empty(trim($data['middlenameen']))){
             $errors['middlenameen'] = get_string('middlenameenrequired','local_userapproval');
            }

             if (empty(trim($data['thirdnameen']))){
             $errors['thirdnameen'] = get_string('thirdnameenrequired','local_userapproval');
            }
             if (empty(trim($data['thirdnamearabic']))){
             $errors['thirdnamearabic'] = get_string('thirdnamearabicrequired','local_userapproval');
            }
             if (empty(trim($data['lastname']))){
             $errors['lastname'] = get_string('lastnamerequired','local_userapproval');
            }
             if (empty(trim($data['lastnamearabic']))){
             $errors['lastnamearabic'] = get_string('lastnamearabicrequired','local_userapproval');
            }

            if (empty($data['phone1']))  {
            $errors['phone1'] = get_string('phone1required', 'local_userapproval');
            }
             if (empty($data['email'])) {
             $errors['email'] = get_string('emailrequired', 'local_userapproval');
             }
             if (empty($data['username']))  {
             $errors['username'] = get_string('usernamerequired', 'local_userapproval');
             }
             if (!empty($data['username']) && !preg_match('/^[A-Za-z0-9_$%&#@.]+$/',$data['username'])) {
              $errors['username'] = get_string('requiredvalidusername', 'local_userapproval');
              }

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

                 if (!empty(trim($data['id_number'])) && strlen(trim($data['id_number'])) < 6) {

                   $errors['id_number'] = get_string('lengthcantbelowerthan6', 'local_userapproval');

                 }
                 if (!empty(trim($data['id_number'])) && strlen(trim($data['id_number'])) > 6 && strlen(trim($data['id_number'])) > 10) {
                    $errors['id_number'] = get_string('lengthcantbemorethan10', 'local_userapproval');
                 }

                if (empty($data['nationalitycountryid']))  {
                $errors['nationalitycountryid'] = get_string('country_coderequired', 'auth_registration');
                 }
                if (!empty(trim($data['nationalitycountryid'])) && !is_numeric($data['nationalitycountryid'])){
                 $errors['nationalitycountryid'] = get_string('requirednumeric','auth_registration');
     }
        return $errors;
    }
    /**
     * Returns context where this form is used
     *
     * @return context
     */
    protected function get_context_for_dynamic_submission(): context {
        return \context_system::instance();
    }

    /**
     * Checks if current user has access to this form, otherwise throws exception
     */
    protected function check_access_for_dynamic_submission(): void {
        
    }

    /**
     * Process the form submission, used if form was submitted via AJAX
     */
    public function process_dynamic_submission() {
        global $CFG, $DB;
        $data = (object)$this->get_data();
        $systemcontext = context_system::instance();
          $response =   (new \local_userapproval\local\fast_service)->register_user($data);
    }

    /**
     * Load in existing data as form defaults
     */
    public function set_data_for_dynamic_submission(): void {
        global $DB;
        $id = $this->optional_param('id', 0, PARAM_INT);
        if ($id) {
            $userenrol = (new local_userapproval\action\manageuser)->set_userenrol($id);
            $this->set_data($userenrol);
        }
    }

    /**
     * Returns url to set in $PAGE->set_url() when form is being rendered or submitted via AJAX
     *
     * @return moodle_url
     */
    protected function get_page_url_for_dynamic_submission(): moodle_url {
        $id = $this->optional_param('id', 0, PARAM_INT);
        return new moodle_url('/local/userapproval/index.php',
            ['action' => 'editcategory', 'id' => $id]);
    }    
}
