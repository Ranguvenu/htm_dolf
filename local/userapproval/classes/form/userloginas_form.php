<?php
namespace local_userapproval\form;

use core_form\dynamic_form;
use moodle_url;
use context;
use context_system;
use local_organization;

use coding_exception;
use MoodleQuickForm_autocomplete;
use local_userapproval\action\manageuser;


class userloginas_form extends dynamic_form { 
    public function definition() {
        global $USER, $CFG,$DB;
        $userid = $this->optional_param('userid', 0, PARAM_INT);
        $localuserid = $this->optional_param('localuserid', 0, PARAM_INT);

        $corecomponent = new \core_component();
        $mform = $this->_form;
         
        $systemcontext = context_system::instance();

        $mform->addElement('hidden', 'userid', $userid);
        $mform->setType('userid',PARAM_INT);
    
        $mform->addElement('hidden', 'localuserid', $localuserid);
        $mform->setType('localuserid',PARAM_INT);

        $loginasuser = $this->_ajaxformdata['loginasuser'];
        $loginasusers = array();
        if (!empty($loginasuser)) {
            $loginasuser = is_array($loginasuser) ? $loginasuser : array($loginasuser);
            $loginasusers = manageuser::get_loginasusers($loginasuser,0);
        }
        $trattributes = array(
            'ajax' => 'local_trainingprogram/dynamic_dropdown_ajaxdata',
            'data-type' => 'loginasusers',
            'id' => 0,
            'data-ctype' => 0,
            'data-programid' =>$userid,
            'data-offeringid' =>$localuserid,
            'multiple'=>false,
            );
        $mform->addElement('autocomplete', 'loginasuser', get_string('loginas'),$loginasusers, $trattributes);
        $mform->addRule('loginasuser', get_string('selectloginasuser', 'local_userapproval'), 'required', null);
    }
    public function validation($data, $files) {
        $errors = array();
        global $DB, $CFG;
        if(empty($data['loginasuser'])) {
           $errors['loginasuser']= get_string('selectloginasuser','local_userapproval');
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
       !is_siteadmin() && has_capability('local/organization:manage_trainee', $this->get_context_for_dynamic_submission());
    }

    /**
     * Process the form submission, used if form was submitted via AJAX
     */
    public function process_dynamic_submission() {
        global $CFG, $DB,$SESSION;
        require_once($CFG->dirroot.'/user/profile/definelib.php');
        $data = $this->get_data();
        $data->response = ($data->loginasuser > 0) ? 'success': 'failed';
        $courseid = SITEID;
        $user =$data->loginasuser;
        $sesskey =sesskey();
        $url = $CFG->wwwroot.'/course/loginas.php?id='.$courseid.'&user='.$user.'&sesskey='.$sesskey;
        $data->returnurl = $url; 
        return $data;
    }

    /**
     * Load in existing data as form defaults
     */
    public function set_data_for_dynamic_submission(): void {
        global $DB;
    }

    /**
     * Returns url to set in $PAGE->set_url() when form is being rendered or submitted via AJAX
     *
     * @return moodle_url
     */
    protected function get_page_url_for_dynamic_submission(): moodle_url {
        $id = $this->optional_param('id', 0, PARAM_INT);
        return new moodle_url('/local/userapproval/userprofile.php');
    }    
}
