<?php
    namespace local_trainingprogram\form;
    
    use core_form\dynamic_form ;
    use moodle_url;
    use context;
    use context_system;
    use local_cpd;
    /**
     * 
     */
    class cpd_form extends dynamic_form
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
        $prgid = $this->optional_param('prgid', 0, PARAM_INT);
        $mform->addElement('hidden', 'id', $id, array('id' => 'id'));
        $mform->setType('id', PARAM_INT);

        $mform->addElement('hidden', 'programid', $prgid, array('prgid' => 'prgid'));
        $mform->setType('prgid', PARAM_INT);
       
        /*if (empty($id)) {
            $program = array();
            $program_list = $this->_ajaxformdata['programid'];
            if(!empty($program_list)) {
                list($programsql, $programparams) = $DB->get_in_or_equal($program_list);
                $program = $DB->get_records_sql_menu("SELECT id, name AS fullname FROM {local_trainingprogram} WHERE id $programsql ",$programparams);
            }
            $programoptions = array(
                'ajax' => 'local_cpd/form_selector_datasource',
                'data-type' => 'programlist',
                'id' => 'el_program',
                'data-programid' => '',
                'data-cpdid' => $cpdid,
                'noselectionstring' => get_string('selectprograms', 'local_cpd'),
                'multiple' => true,
            );
            $mform->addElement('autocomplete','programid',  get_string('programlist', 'local_cpd'), $program, $programoptions);
            $mform->addRule('programid', get_string('required'), 'required', null, 'server');
            $mform->setType('programid', PARAM_RAW);
        } else {
            $mform->addElement('hidden', 'programid');
            $mform->setType('programid', PARAM_INT);
        }*/

           $currlang = current_language();
           $examfullname = ($currlang == 'ar') ? 'ex.examnamearabic' : 'ex.exam';
               $cpdlist=$DB->get_records_sql('SELECT cpd.id,cpd.examid,'.$examfullname.' as cpdname 
                   FROM {local_cpd} as cpd
                   JOIN {local_exams} as ex ON cpd.examid=ex.id
                   WHERE  ex.status=1 AND cpd.id 
                   NOT IN
                   (SELECT cpdid FROM {local_cpd_training_programs} 
                    WHERE programid = '.$prgid.')');
            $cpds=[];
            $cpds[''] = get_string('selectcpd','local_trainingprogram');
            foreach ($cpdlist AS $cpd){
             $cpds[$cpd->id]=$cpd->cpdname;
            }
            $mform->addElement('autocomplete','cpdid',get_string('listofcdp','local_trainingprogram'),$cpds,$cpds);
             $mform->addRule('cpdid', get_string('required'), 'required', null, 'server');
            $mform->setType('cpdid', PARAM_RAW);
      

        $mform->addElement('text', 'creditedhours', get_string('creditedhours', 'local_cpd'),'maxlength="254" size="20"');
        $mform->addRule('creditedhours',  get_string('missingcreditedhours', 'local_cpd'), 'required', null, 'server');
        $mform->setType('creditedhours',  PARAM_TEXT);
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
        $prgid=$data['programid'];
        $cpdid=$data['cpdid'];
        $creditedhours=$data['creditedhours'];
         if (empty($data['cpdid'])) {
            $errors['cpdid'] = get_string('pleaseselectcpd', 'local_trainingprogram');
        }
           if ($data['creditedhours'] <= "0") {
            $errors['creditedhours'] = get_string('cpdtimezeroerror', 'local_trainingprogram');
        }
        if(isset($data['creditedhours']) &&!empty(trim($data['creditedhours']))){
            if(!is_numeric(trim($data['creditedhours']))){
                $errors['creditedhours'] = get_string('numeric','local_cpd');
            }
            if(is_numeric(trim($data['creditedhours']))&&trim($data['hourscreated'])<0){
                $errors['creditedhours'] = get_string('positive_numeric','local_cpd');
            }

             $get_cpd_hrs = $DB->get_record('local_cpd', ['id' => $cpdid], '*', MUST_EXIST);
         if (empty($data['creditedhours'] < $get_cpd_hrs->hourscreated)) {
            $errors['creditedhours'] = get_string('cpdtimeerror', 'local_trainingprogram');
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
       // require_capability('local/cpd:manage', $this->get_context_for_dynamic_submission());
    }

    /**
     * Process the form submission, used if form was submitted via AJAX
     */
    public function process_dynamic_submission() {
        global $CFG, $DB;
        require_once($CFG->dirroot.'/user/profile/definelib.php');
        $data = $this->get_data();
        $postings = (new local_cpd\lib)->create_update_trainingprograms($data);
    }

    /**
     * Load in existing data as form defaults
     */
    public function set_data_for_dynamic_submission(): void {
        global $DB;
        if ($id = $this->optional_param('id', 0, PARAM_INT)) {
            $prgid = $this->optional_param('prgid', 0, PARAM_INT);
            $data = $DB->get_record('local_cpd_training_programs', ['id' => $id], '*', MUST_EXIST);
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
