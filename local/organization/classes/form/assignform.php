<?php
namespace local_organization\form;

use core_form\dynamic_form;
use moodle_url;
use context;
use context_system;
use local_organization;

use coding_exception;
use MoodleQuickForm_autocomplete;
use \core_competency\competency_framework;


class assignform extends dynamic_form { 
    public function definition() {
        global $USER, $CFG,$DB;
        $orgid = $this->optional_param('orgid', 0, PARAM_INT);
        $roleid = $this->optional_param('roleid', 0, PARAM_RAW);

        $corecomponent = new \core_component();

        $mform = $this->_form;
        $id = $this->_customdata['id'];
        $editoroptions = $this->_customdata['editoroptions'];
         
        $systemcontext = context_system::instance();

        $mform->addElement('hidden', 'id', '');
        $mform->setType('id',PARAM_INT);
    
        $mform->addElement('hidden', 'orgid', $orgid);
        $mform->setType('orgid',PARAM_INT);

        $mform->addElement('hidden', 'roleid', $roleid);
        $mform->setType('roleid',PARAM_RAW);

        $userattributes = array(
        'ajax' => 'local_organization/organization_datasource',
        'data-type' => 'orgusers',
        'id' => 'org_users',
        'data-org' => $orgid,
        'multiple'=>true,
        );
        $mform->addElement('autocomplete', 'user', get_string('users', 'local_organization'),[], $userattributes);
          $mform->addRule('user', get_string('selectorgofficial', 'local_organization'), 'required', null);
    }
    public function validation($data, $files) {
        $errors = array();
        global $DB, $CFG;
        if(empty($data['user'])) {
           $errors['user']= get_string('selectorgofficial','local_organization');
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
        (new local_organization\organization)->enrol_orgofficial($data);
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
        return new moodle_url('/local/organization/index.php',
            ['action' => 'editcategory', 'id' => $id]);
    }    
}
