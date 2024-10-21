<?php
namespace local_organization\form;
use core_form\dynamic_form ;
use moodle_url;
use context;
use context_system;
use coding_exception;
use MoodleQuickForm_autocomplete;
use local_organization;
use local_organization\organization as organization;

class hremail_form extends dynamic_form { 
    public function definition() {
        global $USER, $CFG,$DB;

        $corecomponent = new \core_component();
        $mform = $this->_form;
        $organizationid = $this->optional_param('organizationid', 0, PARAM_INT);
        $hremail = $this->optional_param('hremail', 0, PARAM_RAW);
       
        $systemcontext = context_system::instance();

        $mform->addElement('hidden', 'organizationid', $organizationid);
        $mform->setType('organizationid',PARAM_INT);

        $mform->addElement('hidden', 'hremail', $hremail);
        $mform->setType('hremail',PARAM_RAW);

        $mform->addElement('static', 'sender_email', get_string('sender_email', 'local_organization'));
        $mform->setDefault('sender_email', $USER->email);

        $mform->addElement('static', 'hr_email', get_string('hremail', 'local_organization'));
        $mform->setDefault('hr_email', $hremail);
       
        $mform->addElement('text', 'subject', get_string('subject', 'local_organization'));
        $mform->addRule('subject', get_string('subjectcannotbeempty', 'local_organization'), 'required', null);
        $mform->setType('subject', PARAM_RAW);
       
        $mform->addElement('editor', 'message', get_string('message','local_organization'));
        $mform->addRule('message', get_string('messagecannotbeempty', 'local_organization'), 'required', null);
        $mform->setType('message', PARAM_TEXT);
       

    }
    public function validation($data, $files) {
        global $DB, $CFG;
        $errors = array();
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
        //require_capability('moodle/site:config', $this->get_context_for_dynamic_submission());
        has_capability('moodle/site:config', $this->get_context_for_dynamic_submission()) 
        || has_capability('local/organization:manage_communication_officer', $this->get_context_for_dynamic_submission());
    }

    /**
     * Process the form submission, used if form was submitted via AJAX
     */
    public function process_dynamic_submission() {
        global $CFG, $DB;
        require_once($CFG->dirroot.'/user/profile/definelib.php');
        $data = $this->get_data();
        (new local_organization\organization)->send_hr_mail($data);
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
        return new moodle_url('/local/trainingprogram/couponcodesettings.php');
    }    
}
