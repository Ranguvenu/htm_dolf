<?php
    namespace local_cpd\form;
    use core_form\dynamic_form;
    use moodle_url;
    use context;
    use context_system;
    use html_writer;
    use moodleform;
    use local_cpd;

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
       // $evidencetype = $this->_customdata['evidencetype'];
        $evidencetype = $this->_ajaxformdata['evidencetype'];
        $formaltypeclasse='invisible1';
        $informaltypeclasse='invisible1';
        if($evidencetype) {
            if($evidencetype==1) {
                $formaltypeclasse='';
                $informaltypeclasse='invisible1';
            }
            if($evidencetype==2) {
                $formaltypeclasse='invisible1';
                $informaltypeclasse='';
            }
        }
       // var_dump($evidencetype);  exit;
    
        $id = $this->optional_param('id', 0, PARAM_INT);
        $mform->addElement('hidden', 'id', $id, array('id' => 'id'));
        $mform->setType('id', PARAM_INT);

        $mform->addElement('hidden', 'data', array('data' => 'data'));
        $cpdlist = array();
        $cpd_list = $this->_ajaxformdata['cpdid'];
      
        if (!empty($cpd_list)) {
            $cpdlist = (new local_cpd\lib)->cpd_cpdlist(array($cpd_list),$id);
        } elseif ($id > 0) {
            $cpdlist = (new local_cpd\lib)->cpd_cpdlist(array(),$id);
        }
        $cpdoptions = array(
            'ajax' => 'local_cpd/form_selector_datasource',
            'data-type' => 'cpdlist',
            'id' => 'el_cpd',
            'data-cpdid' => '',
            'multiple' => false,

        );
        $mform->addElement('autocomplete', 'cpdid', get_string('exam', 'local_cpd'), $cpdlist, $cpdoptions);
        $mform->addRule('cpdid', get_string('pleaseselectexam','local_cpd'), 'required', null, 'server');
        $mform->setType('cpdid', PARAM_RAW);
       
        $types = array();
        $types[] = $mform->createElement('radio', 'evidencetype', '', get_string('formal', 'local_cpd'), 1);
        $types[] = $mform->createElement('radio', 'evidencetype', '', get_string('informal', 'local_cpd'), 2);
        $mform->addGroup($types, 'evidencetypess', get_string('evidencetype', 'local_cpd'), array(''), false);
        $mform->setType('evidencetypess', PARAM_RAW);
        $mform->hideif('evidencetypess', 'cpdid', 'eq', '');


        //$mform->addRule('evidencetypess', get_string('pleaseselectevidencetype','local_cpd'), 'required', null, 'server');
      
        //Formal Training
        $formaltypes = array("1" => get_string('trainingattendence', 'local_cpd'), "2" => get_string('conferenceattendence', 'local_cpd'), "3" => get_string('workshopattendence', 'local_cpd'), "4" => get_string('lectures', 'local_cpd'), "5" => get_string('selftraining', 'local_cpd'));
        $mform->addElement('html','<div class ="row">');
        $mform->addElement('html','<div class =" col-md-3">');
        $mform->addElement('html','</div>');
        $mform->addElement('html','<div class =" col-md-9">');
        $mform->addElement('html','<div class ="row d-flex">');
        $mform->addElement('html','<div class ="tagscontainer '.$formaltypeclasse.'" data-tagtype="1">');
        $mform->addElement('html','<div class =" col-md-6 my-2">');
        foreach ($formaltypes as $key => $formal) {
         $mform->addElement('html','<a type="button" class ="type_tag_btn btn btn-primary btn-sm m-1" data-action ="createformalevid" data-type = "'.$key.'" data-evidid = "'.$id.'" data-cpdid="'.$cpd_list.'" data-id="0" data-typename = "'.$formal.'" >'.$formal.'</a>');
        }
        $mform->addElement('html','</div>');
        $mform->addElement('html','</div>');

        //Informal Training
        //$mform->addElement('html','<div class =" col-md-2">'); 
        //$mform->addElement('html','</div>');
        $informaltypes = array("1" => get_string('reading', 'local_cpd'), "2" => get_string('audiolistening', 'local_cpd'), "3" => get_string('tvprograms', 'local_cpd'), "4" => get_string('professionalactivitiesattendance', 'local_cpd'), "5" => get_string('articlesesearches', 'local_cpd'));
        $mform->addElement('html','<div class ="tagscontainer '.$informaltypeclasse.'" data-tagtype="2">');
       
        $mform->addElement('html','<div class =" col-md-6  my-2">');
        foreach ($informaltypes as $key => $informal) {
        $mform->addElement('html','<a type="button" class ="type_tag_btn  btn btn-primary btn-sm m-1" data-action ="createinformalevid" data-type = "'.$key.'" data-evidid= "'.$id.'" data-cpdid="'.$cpd_list.'"  data-id="0" data-typename = "'.$informal.'">'.$informal.'</a>');
        }
        $mform->addElement('html','</div>');
        $mform->addElement('html','</div>');
        $mform->addElement('html','</div>');
        $mform->addElement('html','</div>');
        $mform->addElement('html','</div>');

        $mform->addElement('date_selector','dateofachievement', get_string('dateofachievement', 'local_cpd'));
        $mform->addRule('dateofachievement', get_string('required'), 'required', null, 'server');
        $mform->setType('dateofachievement', PARAM_RAW);
        $mform->hideif('dateofachievement', 'cpdid', 'eq', '');
    }

    /**
     * Perform some moodle validation.
     *
     * @param array $datas
     * @param array $files
     * @return array
     */
    public function validation($data, $files) {
        global $DB, $USER;
        $errors = parent::validation($data, $files);
        //var_dump($data['evidencetype']); exit;
        if (empty($_SESSION['formalid']) && empty($_SESSION['informalid'])) {
            $errors['evidencetypess'] = get_string('pleaseselectevidenceoption', 'local_cpd');
        }
        if (empty($data['cpdid'])) {
            $errors['cpdid'] = get_string('pleaseselectcpdid', 'local_cpd');
        }
        if ($DB->record_exists('local_cpd_evidence', array('cpdid' => $data['cpdid'], 'userid' => $USER->id))) {
            $cpd = $competencies =  $DB->get_field('local_cpd', 'id', ['id' => $data['cpdid']]);
            $sql = " SELECT ce.cpdid, ce.userid, ce.status FROM {local_cpd_evidence} ce WHERE ce.cpdid IN ($cpd) AND ce.status = 0 AND ce.userid = $USER->id";
            $status = $DB->get_records_sql($sql);
            if ($status) {
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
        $postings = (new local_cpd\lib)->create_update_evidence($data);
        $context = context_system::instance();
         if($data->logo) {
            $this->save_stored_file('logo', $context->id, 'local_cpd', 'cpdlogo',  $data->logo, '/', null, true);
        } 
    }

    /**
     * Load in existing data as form defaults
     */
    public function set_data_for_dynamic_submission(): void {
        global $DB;
        $context = context_system::instance();
        if ($id = $this->optional_param('id', 0, PARAM_INT)) {
            $data = $DB->get_record('local_cpd_evidence', ['id' => $id], '*', MUST_EXIST);
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
