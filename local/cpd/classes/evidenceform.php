<?php
    namespace local_cpd;
    use core_form\dynamic_form ;
    use moodle_url;
    use context;
    use context_system;
    use html_writer;
    use moodleform;

    /**
     * 
     */
    require_once($CFG->libdir.'/formslib.php');
    class evidenceform extends dynamic_form
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
    
        $id = $this->optional_param('id', 0, PARAM_INT);
        $mform->addElement('hidden', 'id', $id, array('id' => 'id'));
        $mform->setType('id', PARAM_INT);

        $mform->addElement('hidden', 'data', array('data' => 'data'));

        $cpd_select = [null => get_string('select_cpd','local_cpd')];
        $cpd_sql = "SELECT id,title FROM {cpd}";
        $cpdlist = $cpd_select + $DB->get_records_sql_menu($cpd_sql);
        $mform->addElement('select','cpdid', get_string('exam', 'local_cpd'),$cpdlist);
        $mform->addRule('cpdid', get_string('pleaseselectexam','local_cpd'), 'required', null, 'server');
        $mform->setType('cpdid', PARAM_INT);
       
        $types = array();
        $types[] = $mform->createElement('radio', 'evidencetype', '', get_string('formal', 'local_cpd'), 1);
        $types[] = $mform->createElement('radio', 'evidencetype', '', get_string('informal', 'local_cpd'), 2);
        $mform->addGroup($types, 'evidencetypess', get_string('evidencetype', 'local_cpd'), array(''), false);
        $mform->setType('evidencetypess', PARAM_RAW);
        $mform->setDefault('evidencetype', 1);
        $mform->addRule('evidencetypess', get_string('pleaseselectevidencetype','local_cpd'), 'required', null, 'server');
      
        //Formal Training
        $formaltypes = array("1" => "Training Attendence", "2" => "Conference Attendence", "3" => "Workshop Attendence", "4" => "Lectures", "5" => "Built I timing(Self Training)");
        $mform->addElement('html','<div class ="row">');
        $mform->addElement('html','<div class ="tagscontainer col-md-6" data-tagtype="1">');
        foreach ($formaltypes as $key => $formal) {
         $mform->addElement('html','<a type="button" class ="type_tag_btn btn btn-primary btn-sm m-1" data-action ="createformalevid" data-type = "'.$key.'" data-evidid = "'.$id.'" data-id = "0" >'.$formal.'</a>');
        }
        $mform->addElement('html','</div>');

        //Informal Training
        $informaltypes = array("1" => "Reading", "2" => "Audio Listening", "3" => "TV Programs", "4" => "Professional Activities Attendance", "5" => "Publishing articles and researches");
        $mform->addElement('html','<div class ="tagscontainer col-md-6 invisible" data-tagtype="2">');
        foreach ($informaltypes as $key => $informal) {
        $mform->addElement('html','<a type="button" class ="type_tag_btn  btn btn-primary btn-sm m-1" data-action ="createinformalevid" data-type = "'.$key.'" data-evidid = "'.$id.'" data-id = "0" >'.$informal.'</a>');
        }


        $mform->addElement('html','</div>');
        $mform->addElement('html','</div>');

        $mform->addElement('date_selector','dateofachievement', get_string('dateofachievement', 'local_cpd'));
        $mform->addRule('dateofachievement', get_string('required'), 'required', null, 'server');
        $mform->setType('dateofachievement', PARAM_RAW);
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
        if ($DB->record_exists('cpd_evidence', array('cpdid' => $data['cpdid']))) {
            $status = $DB->get_record('cpd_evidence', array('cpdid' => $data['cpdid']));
            if ($status->status == 0) {
                $errors['cpdid'] = get_string('completepending', 'local_cpd');
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
        global $CFG, $DB;
        require_once($CFG->dirroot.'/user/profile/definelib.php');
        $data = $this->get_data();
        $postings = (new lib)->create_update_evidence($data);
    }

    /**
     * Load in existing data as form defaults
     */
    public function set_data_for_dynamic_submission(): void {
        global $DB;
        $context = context_system::instance();
        if ($id = $this->optional_param('id', 0, PARAM_INT)) {
            $data = $DB->get_record('cpd_evidence', ['id' => $id], '*', MUST_EXIST);
            $this->set_data(['id' => $data->id]);
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