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
    class termsconditionsform extends dynamic_form {
    /**
     * Define the form
     */
    public function definition () {
        global $CFG,$DB,$OUTPUT;
        $mform = $this->_form;
        $examid = $this->optional_param('examid', 0, PARAM_INT);
        $mform->addElement('hidden', 'examid', $examid);
        $mform->setType('examid',PARAM_INT);
        $exam = $DB->get_record('local_exams', array('id'=>  $examid));
        if($exam->termsconditions !=null){
            $data = [
               'termsconditions'=> $exam->termsconditions ,
            ];
            $tandc = $OUTPUT->render_from_template('local_exams/termsconditions',   $data );

            $mform->addElement('static', '', '',$tandc );
         }
        $mform->addElement('hidden','examid', $examid);
        $mform->addElement('advcheckbox', 'tandc', get_string('tandcdescription', 'local_exams'), '');
      
      

  
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
        if($data['tandc'] == 0) {
            $errors['tandc'] = get_string('termsconditionnotempty','local_exams');
        }
       
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
