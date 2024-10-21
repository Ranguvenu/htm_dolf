<?php
namespace local_organization\form;
use core_form\dynamic_form ;
use moodle_url;
use context;
use context_system;
use coding_exception;
use MoodleQuickForm_autocomplete;
use local_organization;
use local_organization\types as organization;

class partnertypes_form extends dynamic_form { 
    public function definition() {
        global $USER, $CFG,$DB;

        $corecomponent = new \core_component();
        $mform = $this->_form;
        $systemcontext = context_system::instance();
        $id = $this->optional_param('id', 0, PARAM_INT);

        $mform->addElement('hidden', 'id', $id);
        $mform->setType('id',PARAM_INT);
       
        $mform->addElement('text', 'name', get_string('englishname', 'local_organization'));
        $mform->addRule('name', get_string('partnernamenotbeempty', 'local_organization'), 'required', null);
        $mform->setType('name', PARAM_RAW);
       
        $mform->addElement('text', 'arabicname', get_string('arabicname', 'local_organization'));
        $mform->addRule('arabicname', get_string('partnerarabicnamenotbeempty', 'local_organization'), 'required', null);
        $mform->setType('arabicname', PARAM_RAW);

        $mform->addElement('editor', 'description', get_string('description','local_organization'));
        $mform->addRule('description', get_string('descriptionnotbeempty', 'local_organization'), 'required', null);
        $mform->setType('description', PARAM_RAW);
        
        $filemanageroptions = array('maxbytes' => $CFG->maxbytes,
                                        'subdirs' => 0,
                                        'maxfiles' => 1,
                                        'accepted_types' => '*');
        $mform->addElement('filemanager', 'partnerimage', get_string('partnerimage', 'local_organization'), '', $filemanageroptions);
        // $mform->addRule('logo', get_string('required'), 'required', null);

    }
    public function validation($data, $files) {
        global $DB, $CFG;
        $errors = array();

        // if(empty($data['name'])) {
        //     $errors['name'] = get_string('partnernamenotbeempty','local_organization');                
        // }
        // if(empty($data['arabicname'])) {
        //     $errors['arabicname'] = get_string('partnerarabicnamenotbeempty','local_organization');                
        // }
        // if(empty($data['description'])) {
        //     $errors['description'] = get_string('descriptionnotbeempty','local_organization');                
        // }

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
        global $DB, $USER;
        $context = context_system::instance();
        $data = $this->get_data();
        if ($data->id > 0) {
            $data->name = $data->name;
            $data->arabicname = $data->arabicname;
            $data->description =$data->description['text'];
            $data->timemodified = time();
            $data->partnerimage = $data->partnerimage;
            file_save_draft_area_files($data->partnerimage, $context->id, 'local_organization', 'partnerimage', $data->partnerimage);
            $DB->update_record('local_org_partnertypes', $data);
        } else {
            $data->partnerimage = $data->partnerimage;
            file_save_draft_area_files($data->partnerimage, $context->id, 'local_organization', 'partnerimage', $data->partnerimage);
            $data->timecreated = time();
            $data->usercreated = $USER->id;
            $data->description = $data->description['text'];
            $DB->insert_record('local_org_partnertypes', $data);
        }       
    }

    /**
     * Load in existing data as form defaults
     */
    public function set_data_for_dynamic_submission(): void {
        global $DB;
        $id = $this->optional_param('id', 0, PARAM_INT);
        if ($id = $this->optional_param('id', 0, PARAM_INT)) {
            $data = $DB->get_record('local_org_partnertypes', ['id' => $id], '*', MUST_EXIST);
            $this->set_data(['id'=>$data->id,'name' => $data->name, 'arabicname' => $data->arabicname,'partnerimage' => $data->partnerimage, 'description' => ['text' => $data->description]]);
        }
    }

    /**
     * Returns url to set in $PAGE->set_url() when form is being rendered or submitted via AJAX
     *
     * @return moodle_url
     */
    protected function get_page_url_for_dynamic_submission(): moodle_url {
        $id = $this->optional_param('id', 0, PARAM_INT);
        return new moodle_url('/local/organization/partnertype.php',
            ['action' => 'editpartnertype', 'id' => $id]);
    }    
}
