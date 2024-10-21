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
        global $CFG, $DB;
        $mform = $this->_form;
        $systemcontext   = context_system::instance();
        $id = $this->optional_param('id', 0, PARAM_INT);
        $mform->addElement('hidden', 'id', $id, array('id' => 'id'));
        $mform->setType('id', PARAM_INT);

        //$exam = array();
        $exam = [null => get_string('selectexam','local_cpd')];
        $exam_list = $this->_ajaxformdata['examid'];
        if (!empty($exam_list)) {
            $exam = (new local_cpd\lib)->cpd_examslist(array($exam_list),$id);
        } elseif ($id > 0) {
            $exam = (new local_cpd\lib)->cpd_examslist(array(),$id);
        }
        $options = array(
            'ajax' => 'local_cpd/form_selector_datasource',
            'data-contextid' => $systemcontext->id,
            'data-type' => 'examlist',
            'class' => 'el_examid',
            'data-class' => 'el_examid',
            'id' => 'el_examid',
            'data-examid' => '',
            'multiple' => false,
        );
        $mform->addElement('autocomplete','examid', get_string('title', 'local_cpd'), $exam, $options);
        $mform->addRule('examid', get_string('pleaseselectexam', 'local_cpd'), 'required', null, 'server');
        
        $mform->addElement('text','code', get_string('code', 'local_cpd'),'maxlength="254"');
        $mform->addRule('code', get_string('missingcode', 'local_cpd'), 'required', null, 'server');
        $mform->setType('code', PARAM_TEXT);

        $years = range(0,20);
        $yearslist = [];
        foreach($years as $key => $year){
                $yearslist[$key] = $year;
        }

        $mform->addElement('select','validation', get_string('validationyears', 'local_cpd'), $yearslist);
        $mform->addRule('validation', get_string('required'), 'required', null, 'server');
        $mform->setType('title', PARAM_RAW);

        $mform->addElement('text','hourscreated', get_string('hourscreated', 'local_cpd'),'maxlength="254" size="3"');
        $mform->addRule('hourscreated', get_string('missinghourscreated', 'local_cpd'), 'required', null, 'server');
        $mform->setType('hourscreated', PARAM_RAW);

        $mform->addElement('editor','description', get_string('description'));
        $mform->addRule('description', get_string('missingdescription','local_cpd'), 'required', null, 'server');
        $mform->setType('description', PARAM_RAW);

        $filemanageroptions = array(
            'accepted_types' => array(get_string('jpg','local_events'), get_string('png','local_events')),
            'maxbytes' => 0,
            'maxfiles' => 1,
            );
        $mform->addElement('filemanager', 'logo', get_string('logo', 'local_cpd'), '', $filemanageroptions);
        $mform->addRule('logo', get_string('missinglogo','local_cpd'), 'required', null, 'server');
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
        if ($cpd = $DB->get_record('local_cpd', array('code' => $data['code']), '*', IGNORE_MULTIPLE)) {
            if (empty($data['id']) || $cpd->id != $data['id']) {
                $errors['code'] = get_string('shortnametaken', 'local_cpd');
            }
        }  
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
       has_capability('local/organization:manage_cpd', $this->get_context_for_dynamic_submission()) 
        || has_capability('local/cpd:manage', $this->get_context_for_dynamic_submission());
      // require_capability('local/cpd:manage', $this->get_context_for_dynamic_submission());
    }

    /**
     * Process the form submission, used if form was submitted via AJAX
     */
    public function process_dynamic_submission() {
        global $CFG, $DB;
        require_once($CFG->dirroot.'/user/profile/definelib.php');
        $data = $this->get_data();
        $postings = (new local_cpd\lib)->create_update_cpd($data);
    }

    /**
     * Load in existing data as form defaults
     */
    public function set_data_for_dynamic_submission(): void {
        global $DB;
        $context = context_system::instance();
        if ($id = $this->optional_param('id', 0, PARAM_INT)) {
            $cpddata = (new local_cpd\lib)->set_cpddata($id);
            $this->set_data($cpddata);
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
