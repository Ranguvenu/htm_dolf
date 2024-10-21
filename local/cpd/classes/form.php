<?php
    namespace local_cpd;
    
    use core_form\dynamic_form ;
    use moodle_url;
    use context;
    use context_system;
    /**
     * 
     */
    class form extends dynamic_form
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
        $mform->addElement('hidden', 'id', $id, array('id' => 'id'));
        $mform->setType('id', PARAM_INT);

        $options = array();
        $options[null] = get_string('select_cpd','local_cpd');
        $options['Exam1'] = 'Exam1';
        $options['Exam2'] = 'Exam2';
        $options['Exam3'] = 'Exam3';
        $mform->addElement('select','title', get_string('title', 'local_cpd'),$options);
        $mform->addRule('title', get_string('pleaseselectexam','local_cpd'), 'required', null, 'server');
        $mform->setType('title', PARAM_TEXT);
        
        $mform->addElement('text','code', get_string('code', 'local_cpd'),'maxlength="254" size="50"');
        $mform->addRule('code', get_string('required'), 'required', null, 'server');
        $mform->setType('code', PARAM_TEXT);

        $mform->addElement('editor','description', get_string('description'));
        $mform->setType('description', PARAM_RAW);

        $mform->addElement('filemanager', 'logo', get_string('logo','local_cpd'));

        $years = range(0,20);
        $yearslist = [];
        foreach($years as $key => $year){
                $yearslist[$key] = $year;
        }
        $mform->addElement('select','validation', get_string('validation', 'local_cpd'), $yearslist);
        $mform->addRule('validation', get_string('required'), 'required', null, 'server');
        $mform->setType('title', PARAM_RAW);

        $mform->addElement('text','hourscreated', get_string('hourscreated', 'local_cpd'),'maxlength="254" size="50"');
        $mform->addRule('hourscreated', get_string('required'), 'required', null, 'server');
        $mform->setType('hourscreated', PARAM_RAW);
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
        if(isset($data['hourscreated']) &&!empty(trim($data['hourscreated']))){
            if(!is_numeric(trim($data['hourscreated']))){
                $errors['hourscreated'] = get_string('numeric','local_cpd');
            }
            if(is_numeric(trim($data['hourscreated']))&&trim($data['hourscreated'])<0){
                $errors['hourscreated'] = get_string('positive_numeric','local_cpd');
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
        require_capability('moodle/site:config', $this->get_context_for_dynamic_submission());
    }

    /**
     * Process the form submission, used if form was submitted via AJAX
     */
    public function process_dynamic_submission() {
        global $CFG, $DB;
        require_once($CFG->dirroot.'/user/profile/definelib.php');
        $data = $this->get_data();
        $postings = (new lib)->create_update_cpd($data);
    }

    /**
     * Load in existing data as form defaults
     */
    public function set_data_for_dynamic_submission(): void {
        global $DB;
        $context = context_system::instance();
        if ($id = $this->optional_param('id', 0, PARAM_INT)) {
            $data = $DB->get_record('cpd', ['id' => $id], '*', MUST_EXIST);
            $draftitemid = file_get_submitted_draft_itemid('logo');
            file_prepare_draft_area($draftitemid, $context->id, 'local_cpd', 'logo', $data->logo, null);
            $data->logo = $draftitemid;
            $this->set_data(['title' => $data->title, 'code' => $data->code, 'description' => ['text' => $data->description],'logo' => $data->logo,'validation' => $data->validation, 'hourscreated' => $data->hourscreated]);
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