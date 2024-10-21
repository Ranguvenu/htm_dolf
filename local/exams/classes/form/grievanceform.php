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
    class grievanceform extends dynamic_form {
    /**
     * Define the form
     */
    public function definition () {
        global $CFG;
        $mform = $this->_form;
        $examid = $this->optional_param('examid', 0, PARAM_INT);
        $userid = $this->optional_param('userid', 0, PARAM_INT);
        $profileid = $this->optional_param('profileid', 0, PARAM_INT);

        $mform->addElement('hidden', 'examid', $examid);
        $mform->setType('examid', PARAM_INT);

        $mform->addElement('hidden', 'userid', $userid);
        $mform->setType('userid', PARAM_INT);
       
        $mform->addElement('hidden', 'profileid', $profileid);
        $mform->setType('profileid', PARAM_INT);

        $mform->addElement('editor', 'reason', get_string('reason', 'local_exams'),'maxlength="254" size="50"');
        $mform->addRule('reason',  get_string('required'), 'required', null);
        $mform->setType('reason',  PARAM_RAW);
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
        return \context_system::instance();
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
        global $CFG, $DB, $OUTPUT;
        require_once($CFG->dirroot.'/user/profile/definelib.php');
        $data = $this->get_data();
        $grievanceid = (new local_exams\local\exams)->add_update_grievance($data);

        $sql = "SELECT leg.id, le.exam as name,le.code,le.sellingprice as price, 
                        le.programdescription as description,le.status, leg.profileid
                   FROM {local_exam_grievance} as leg
                   JOIN {local_exams} le ON le.id = leg.examid 
                   WHERE leg.id = ".$grievanceid;

        $examrecord = $DB->get_record_sql($sql);
        $grievanceproduct = product::GRIEVANCE;

        $productdetails = (array)$examrecord;
        $productdetails['price'] = 400;//$examrecord->price;
        $productdetails['category'] = $grievanceproduct;
        $productdetails['referenceid'] = $examrecord->id;
        $productdetails['stock'] = $DB->get_field('local_exam_profiles', 'seatingcapacity', ['id' => $examrecord->profileid]);
        $productdetails['timecreated'] = time();
        $productdetails['timemodified'] = time();
        $productdetails['usercreated'] = $USER->id;
        $record = $DB->insert_record('tool_products', $productdetails);

        $productparams=(new \tool_product\product)->get_product_attributes($record, $grievanceproduct, 'addtocart',0,0, 1, true);


        $formurl = new moodle_url('/admin/tool/product/checkout.php', $formparams);

        $formparams = ['tablename' => 'local_exam_grievance','fieldname' => 'id','fieldid' => $grievanceid,'parentfieldid' => 0,'selectedseats' => 1,'formurl'=>$formurl->out(),'sesskey' => sesskey(),];


        $params=array_merge($formparams,$productparams);

        $buttons =$OUTPUT->render_from_template('tool_product/add_order_seats_loader',$params);

        return ['returnparams' =>$params,'returnurlbtn'=>$buttons];
    }

    /**
     * Load in existing data as form defaults
     */
    public function set_data_for_dynamic_submission(): void {
        global $DB;
        if ($id = $this->optional_param('id', 0, PARAM_INT)) {
            $data = $DB->get_record('local_exam_grievance', ['id' => $id], '*', MUST_EXIST);
            $this->set_data($data);
        }
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
