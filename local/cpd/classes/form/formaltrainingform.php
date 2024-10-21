<?php
    namespace local_cpd\form;
    
    use core_form\dynamic_form ;
    use moodle_url;
    use context;
    use context_system;
    use local_cpd;
    /**
     * 
     */
    class formaltrainingform extends dynamic_form
    {

    /** @var profile_define_base $field */
    public $field;
    /** @var \stdClass */
    protected $fieldrecord;

    /**
     * Define the form
     */
    public function definition () {
        global $CFG;
        $mform = $this->_form;
        $id = $this->optional_param('id', 0, PARAM_INT);
        $evidid = $this->optional_param('evidid', 0, PARAM_INT);
        $type = $this->optional_param('type', 0, PARAM_INT);

        $mform->addElement('hidden', 'id', $id, array('id' => 'id'));
        $mform->setType('id', PARAM_INT);

        $mform->addElement('hidden', 'evidid', $evidid, array('evidid' => 'evidid'));
        $mform->setType('evidid', PARAM_INT);

        $mform->addElement('hidden', 'type', $type, array('type' => 'type'));
        $mform->setType('type', PARAM_INT);

        $mform->addElement('text','name', get_string('formalname', 'local_cpd'),'maxlength="254" size="50"');
        $mform->addRule('name',get_string('missingname', 'local_cpd'), 'required', null, 'server');
        $mform->setType('name', PARAM_TEXT);

        $mform->addElement('text','institutename', get_string('institutename', 'local_cpd'),'maxlength="254" size="50"');
        $mform->addRule('institutename', get_string('missinginstitutename', 'local_cpd'), 'required', null, 'server');
        $mform->setType('institutename', PARAM_TEXT);

        $mform->addElement('text','institutelink', get_string('institutelink', 'local_cpd'),'maxlength="254" size="50"');
        $mform->addRule('institutelink', get_string('missinginstitutelink', 'local_cpd'), 'required', null, 'server');
        $mform->setType('institutelink', PARAM_NOTAGS);

        $mform->addElement('text','cityname', get_string('city', 'local_cpd'),'maxlength="254" size="50"');
        $mform->addRule('cityname', get_string('missingcityname', 'local_cpd'), 'required', null, 'server');
        $mform->setType('cityname', PARAM_TEXT); 

        $mform->addElement('date_selector','startdate', get_string('startdate', 'local_cpd'));
        $mform->addRule('startdate', get_string('missingstartdate', 'local_cpd'),'required', null, 'server');
        $mform->setType('startdate', PARAM_RAW); 

        $mform->addElement('date_selector','enddate', get_string('enddate', 'local_cpd'));
        $mform->addRule('enddate', get_string('missingenddate', 'local_cpd'),'required', null, 'server');
        $mform->setType('enddate', PARAM_RAW); 

        $mform->addElement('text','noofdays', get_string('noofdays', 'local_cpd'),'maxlength="254" size="50"');
        $mform->addRule('noofdays', get_string('missingnoofdays', 'local_cpd'),'required', null, 'server');
        $mform->setType('noofdays', PARAM_TEXT);
        
        $mform->addElement('text','creditedhours', get_string('creditedhours','local_cpd'),'maxlength="254" size="50"');
        $mform->addRule('creditedhours', get_string('missingcreditedhours','local_cpd'), 'required', null, 'server');
        $mform->setType('creditedhours', PARAM_TEXT);

        $mform->addElement('textarea','relationtocpd', get_string('relationtocpd', 'local_cpd'));
        $mform->addRule('relationtocpd', get_string('missingrelationtocpd', 'local_cpd'),'required', null, 'server');
        $mform->setType('relationtocpd', PARAM_RAW);

        $filemanageroptions = array(
            'accepted_types' => array('pdf'),
            'maxbytes' => '2097152',
            'maxfiles' => 1,
        );
        $mform->addElement('filemanager', 'attachment', get_string('attachment', 'local_cpd'), null, $filemanageroptions);
        $mform->addRule('attachment', get_string('missingattachment', 'local_cpd'), 'required', null);

        $mform->addElement('editor','comment', get_string('comment', 'local_cpd'));
        $mform->setType('comment', PARAM_TEXT);
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

        if (isset($data['startdate']) && $data['startdate'] &&
        isset($data['enddate']) && $data['enddate']) {
            if ($data['enddate'] < $data['startdate']) {
                $errors['enddate'] = get_string('enddateerror', 'local_cpd');
            }
        }
        if(isset($data['noofdays']) &&!empty(trim($data['noofdays']))){
            if(!is_numeric(trim($data['noofdays']))){
                $errors['noofdays'] = get_string('numeric','local_cpd');
            }
            if(is_numeric(trim($data['noofdays']))&&trim($data['noofdays'])<0){
                $errors['noofdays'] = get_string('positive_numeric','local_cpd');
            }
        }
        if(isset($data['creditedhours']) &&!empty(trim($data['creditedhours']))){
            if(!is_numeric(trim($data['creditedhours']))){
                $errors['creditedhours'] = get_string('numeric','local_cpd');
            }
            if(is_numeric(trim($data['creditedhours']))&&trim($data['creditedhours'])<0){
                $errors['creditedhours'] = get_string('positive_numeric','local_cpd');
            }
        }

        if (!empty(trim($data['institutelink']))  && !preg_match('/\b(?:(?:https?|ftp):\/\/|www\.)[-a-z0-9+&@#\/%?=~_|!:,.;]*[-a-z0-9+&@#\/%=~_|]/i', $data['institutelink']) ) {
            $errors['institutelink'] = get_string('institutelink_err', 'local_cpd'); 
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
       // require_capability('local/cpd:addlearning', $this->get_context_for_dynamic_submission());
    }

    /**
     * Process the form submission, used if form was submitted via AJAX
     */
    public function process_dynamic_submission() {
        global $CFG, $DB, $_SESSION;
        require_once($CFG->dirroot.'/user/profile/definelib.php');
        $data = $this->get_data();
        $_SESSION['formalid'] = $data;
    }

    /**
     * Load in existing data as form defaults
     */
    public function set_data_for_dynamic_submission(): void {
        global $DB;
        $context = context_system::instance();
        if ($id = $this->optional_param('id', 0, PARAM_INT)) {
            $data = $DB->get_record('local_cpd_formal_evidence', ['id' => $id], '*', MUST_EXIST);
            $this->set_data(['name' => $data->name, 'institutename' => $data->institutename, 'cityname' => $data->cityname,'noofdays'=> $data->noofdays, 'creditedhours' => $data->creditedhours]);
        }
    }

    /**
     * Returns url to set in $PAGE->set_url() when form is being rendered or submitted via AJAX
     *
     * @return moodle_url
     */
    protected function get_page_url_for_dynamic_submission(): moodle_url {
        $id = $this->optional_param('id', 0, PARAM_INT);
        return new moodle_url('/local/cpd/evidence.php',
            ['id' => 2]);
    }
    }
