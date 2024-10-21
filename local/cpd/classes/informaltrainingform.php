<?php
    namespace local_cpd;
    
    use core_form\dynamic_form ;
    use moodle_url;
    use context;
    use context_system;
    /**
     * 
     */
    class informaltrainingform extends dynamic_form
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
        $type = $this->optional_param('type', 0, PARAM_INT);

        $mform->addElement('hidden', 'id', $id, array('id' => 'id'));
        $mform->setType('id', PARAM_INT);

        $mform->addElement('hidden', 'type', $type, array('type' => 'type'));
        $mform->setType('type', PARAM_INT);

        $mform->addElement('text','title', get_string('title', 'local_cpd'),'maxlength="254" size="50"');
        $mform->addRule('title', get_string('required'), 'required', null, 'server');
        $mform->setType('title', PARAM_TEXT);

        $mform->addElement('text','author', get_string('author', 'local_cpd'),'maxlength="254" size="50"');
        $mform->addRule('author', get_string('required'), 'required', null, 'server');
        $mform->setType('author', PARAM_TEXT);

        $mform->addElement('date_selector','editiondate', get_string('editiondate', 'local_cpd'));
        $mform->addRule('editiondate', get_string('required'), 'required', null, 'server');
        $mform->setType('editiondate', PARAM_RAW); 
        
        $mform->addElement('editor','relationtocpd', get_string('relationtocpd', 'local_cpd'));
        $mform->setType('relationtocpd', PARAM_RAW);

        $mform->addElement('editor','whatlearned', get_string('whatlearned', 'local_cpd'));
        $mform->setType('whatlearned', PARAM_RAW);

        $mform->addElement('text','creditedhours', get_string('creditedhours', 'local_cpd'),'maxlength="254" size="50"');
        $mform->addRule('creditedhours', get_string('required'), 'required', null, 'server');
        $mform->setType('creditedhours', PARAM_TEXT);
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
        if(isset($data['creditedhours']) &&!empty(trim($data['creditedhours']))){
            if(!is_numeric(trim($data['creditedhours']))){
                $errors['creditedhours'] = get_string('numeric','local_cpd');
            }
            if(is_numeric(trim($data['creditedhours']))&&trim($data['creditedhours'])<0){
                $errors['creditedhours'] = get_string('positive_numeric','local_cpd');
            }
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
        //require_capability('moodle/site:config', $this->get_context_for_dynamic_submission());
    }

    /**
     * Process the form submission, used if form was submitted via AJAX
     */
    public function process_dynamic_submission() {
        global $CFG, $DB, $_SESSION;
        require_once($CFG->dirroot.'/user/profile/definelib.php');
        $data = $this->get_data();
        $_SESSION['informalid'] = $data;
    }

    /**
     * Load in existing data as form defaults
     */
    public function set_data_for_dynamic_submission(): void {
        global $DB;
        $context = context_system::instance();
        if ($id = $this->optional_param('id', 0, PARAM_INT)) {
            $data = $DB->get_record('cpd_informal_evidence', ['id' => $id], '*', MUST_EXIST);
            $this->set_data(['title' => $data->title, 'author' => $data->author, 'relationtocpd' => ['text' => $data->relationtocpd], '	whatlearned' => ['text' => $data->whatlearned], 'creditedhours' => $data->creditedhours]);
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