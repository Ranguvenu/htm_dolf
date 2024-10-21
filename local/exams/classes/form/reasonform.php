<?php
    namespace local_exams\form;
    use core_form\dynamic_form ;
    use moodle_url;
    use context;
    use local_exams;
    use context_system;
    use tool_product\product as product;
    /**
     * 
     */
    class reasonform extends dynamic_form {
    /**
     * Define the form
     */
    public function definition () {
        global $CFG;
        $mform = $this->_form;
        $userid = $this->optional_param('userid', 0, PARAM_INT);
        $productid = $this->optional_param('productid', 0, PARAM_INT);
        $entitytype = $this->optional_param('entitytype', NULL, PARAM_RAW);
        $actiontype = $this->optional_param('actiontype', NULL, PARAM_RAW);


        $mform->addElement('hidden', 'userid', $userid);
        $mform->setType('userid', PARAM_INT);
       
        $mform->addElement('hidden', 'productid', $productid);
        $mform->setType('productid', PARAM_INT);

        $mform->addElement('hidden', 'entitytype', $entitytype);
        $mform->setType('entitytype', PARAM_RAW);

        $mform->addElement('hidden', 'actiontype', $actiontype);
        $mform->setType('actiontype', PARAM_RAW);

        $mform->addElement('textarea', 'reason', get_string('reason', 'local_exams'),'maxlength="254" size="50"');
        if(!is_siteadmin() && ($entitytype == 'offering' || $entitytype == 'event')) {
            $mform->addRule('reason', get_string('requiredreason','local_exams'), 'required');
        }
        $mform->setType('reason',  PARAM_RAW);
        if($entitytype == 'offering' || $entitytype == 'event') {
            $mform->addElement('hidden', 'policy', 1);
            $mform->setType('policy', PARAM_INT);
        } else {
            $mform->addElement('advcheckbox', 'policy', get_string('applypolicy', 'local_exams'), '');
            $mform->setDefault('policy', 1);
        }
       
    }

        /**
     * Perform some moodle validation.
     *
     * @param array $datas
     * @param array $files
     * @return array
     */
    public function validation($data, $files) {
        global $DB;
        $errors = parent::validation($data, $files);
       
        return $errors;
    }

    /**
     * Returns context where this form is used
     *
     * @return context
     */
    protected function get_context_for_dynamic_submission(): context {
        return context_system::instance();
    }

    /**
     * Checks if current user has access to this form, otherwise throws exception
     */
    protected function check_access_for_dynamic_submission(): void {
      // require_capability('moodle/site:config', $this->get_context_for_dynamic_submission());
    }

    /**
     * Process the form submission, used if form was submitted via AJAX
     */
    public function process_dynamic_submission() {
        global $CFG, $DB, $OUTPUT, $USER;
        require_once($CFG->dirroot.'/user/profile/definelib.php');
        $data = $this->get_data();
        $data->usercreated = $USER->id;
        $data->timecreated = time();
        $data->realuser= ($USER->realuser) ? $USER->realuser :0; 
        if($data->actiontype=="absent")
        {
            $DB->insert_record('local_absent_logs', $data);
        }
        else
        {
            $DB->insert_record('local_cancel_logs', $data);
        }
        return $data;
    }

    /**
     * Load in existing data as form defaults
     */
    public function set_data_for_dynamic_submission(): void {
        global $DB;
        // if ($id = $this->optional_param('id', 0, PARAM_INT)) {
        //     $data = $DB->get_record('local_exam_grievance', ['id' => $id], '*', MUST_EXIST);
        //     $this->set_data($data);
        // }
    }

    /**
     * Returns url to set in $PAGE->set_url() when form is being rendered or submitted via AJAX
     *
     * @return moodle_url
     */
    protected function get_page_url_for_dynamic_submission(): moodle_url {
        $id = $this->optional_param('id', 0, PARAM_INT);
        return new moodle_url('/local/test/index.php',
            ['action' => 'editcategory', 'id' => $id]);
    }
    }
