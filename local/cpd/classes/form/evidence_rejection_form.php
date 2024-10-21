<?php
namespace local_cpd\form;
use core_form\dynamic_form ;
use moodle_url;
use context;
use context_system;
use coding_exception;
use MoodleQuickForm_autocomplete;
use local_cpd;
class evidence_rejection_form extends dynamic_form { 
    public function definition() {
        global $USER, $CFG,$DB;
        $corecomponent = new \core_component();
        $mform = $this->_form;
        $evedid = $this->optional_param('evedid', 0, PARAM_INT);
    
        $systemcontext = context_system::instance();
        $mform->addElement('hidden', 'evedid', $evedid);
        $mform->setType('evedid',PARAM_INT);
       
        $mform->addElement('editor', 'reason', get_string('reason','local_cpd'));
        $mform->setType('reason', PARAM_TEXT);
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
        has_capability('local/organization:manage_cpd', $this->get_context_for_dynamic_submission()) 
        || has_capability('local/cpd:manage', $this->get_context_for_dynamic_submission());
    }

    /**
     * Process the form submission, used if form was submitted via AJAX
     */
    public function process_dynamic_submission() {
        global $CFG, $DB;
        require_once($CFG->dirroot.'/user/profile/definelib.php');
        $data = $this->get_data();
        $postings = (new local_cpd\lib)->evidence_rejection_update($data);
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
        return new moodle_url('/local/cpd/view.php');
    }    
}
