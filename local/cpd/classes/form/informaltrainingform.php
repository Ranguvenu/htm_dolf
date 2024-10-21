<?php
    namespace local_cpd\form;
    
    use core_form\dynamic_form;
    use moodle_url;
    use context;
    use context_system;
    use local_cpd;
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
        global $CFG, $DB, $USER;
        $mform = $this->_form;
        $cpdid = $this->optional_param('cpdid', 0, PARAM_INT);
        $type = $this->optional_param('type', 0, PARAM_INT);
        $cpdhours = $DB->get_field('local_cpd','hourscreated',['id' => $cpdid]);
        $programhours = $DB->get_field_sql("SELECT SUM(hoursachieved) FROM {trainingprogram_completion} WHERE cpdid =". $cpdid);
        $trainighours = !empty($programhours) ? $programhours : 0;
        $cpddata = $cpdhours - $trainighours;
        $seconds = $cpddata*3600;
        $percent_data = $seconds*0.3;
        $cpdhours = round(($percent_data)/3600);
        $minutes = round(($percent_data)/60);
        $evidence_data = $DB->get_record_sql("SELECT SUM(ie.creditedhours) AS creditedhours FROM {local_cpd_evidence} ce JOIN {local_cpd_informal_evidence} ie
        ON ie.evidenceid = ce.id WHERE ce.cpdid = $cpdid AND ce.userid = $USER->id AND ce.evidencetype = 2 AND ce.status =1");
        if($evidence_data) {
            $informalhours = $cpdhours - $evidence_data->creditedhours;
            if($informalhours < 0) {
                $maximumhours = 0;
            } else {
                $maximumhours = $informalhours;
            }
        } else {
            $maximumhours = $cpdhours; 
        }
        $mform->addElement('hidden', 'maximumhours', $maximumhours);
        $mform->setType('maximumhours', PARAM_INT);

      // var_dump($maximumhours); exit;
        $mform->addElement('hidden', 'cpdid', $cpdid, array('id' => 'id'));
        $mform->setType('cpdid', PARAM_INT);

        $mform->addElement('hidden', 'type', $type, array('type' => 'type'));
        $mform->setType('type', PARAM_INT);

        $mform->addElement('text','title', get_string('title', 'local_cpd'),'maxlength="254" size="50"');
        $mform->addRule('title', get_string('missingtitle', 'local_cpd'), 'required', null, 'server');
        $mform->setType('title', PARAM_TEXT);

        $mform->addElement('text','institutelink', get_string('institutelink', 'local_cpd'),'maxlength="254" size="50"');
        $mform->addRule('institutelink', get_string('missinginstitutelink', 'local_cpd'), 'required', null, 'server');
        $mform->setType('institutelink', PARAM_NOTAGS);

        $mform->addElement('text','author', get_string('author', 'local_cpd'),'maxlength="254" size="50"');
        $mform->addRule('author', get_string('missingauthor', 'local_cpd'), 'required', null, 'server');
        $mform->setType('author', PARAM_TEXT);
        $mform->hideif('author', 'type', 'eq', 4);
        $mform->hideif('author', 'type', 'eq', 5);

        $mform->addElement('text','organizer', get_string('organizer', 'local_cpd'),'maxlength="254" size="50"');
       // $mform->addRule('organizer', get_string('missingorganizer', 'local_cpd'), 'required', null, 'server');
        $mform->setType('organizer', PARAM_TEXT);
        $mform->hideif('organizer', 'type', 'neq', 4);

        $mform->addElement('date_selector','editiondate', get_string('editiondate', 'local_cpd'));
       $mform->addRule('editiondate', get_string('required'), 'required', null, 'server');
        $mform->setType('editiondate', PARAM_RAW);
        $mform->hideif('editiondate', 'type', 'eq', 4);
        $mform->hideif('editiondate', 'type', 'eq', 5);

        $mform->addElement('date_selector','activitydate4', get_string('activitydate', 'local_cpd'));
        // $mform->addRule('activitydate4', get_string('required'), 'required', null, 'server');
        $mform->setType('activitydate4', PARAM_RAW);
        $mform->hideif('activitydate4', 'type', 'neq', 4);

        $mform->addElement('date_selector','activitydate5', get_string('activitydate', 'local_cpd'));
       // $mform->addRule('activitydate5', get_string('required'), 'required', null, 'server');
        $mform->setType('activitydate5', PARAM_RAW);
        $mform->hideif('activitydate5', 'type', 'neq', 5);
        
        $mform->addElement('textarea','relationtocpd', get_string('relationtocpd', 'local_cpd'));
        $mform->setType('relationtocpd', PARAM_RAW);
        $mform->addRule('relationtocpd', get_string('missingrelationtocpd', 'local_cpd'), 'required', null);

        $mform->addElement('editor','whatlearned', get_string('whatlearned', 'local_cpd'));
        $mform->addRule('whatlearned', get_string('required'), 'required', null, 'server');
        $mform->setType('whatlearned', PARAM_RAW);
        $mform->hideif('whatlearned', 'type', 'eq', 5);

        $radioarray = array();
        $radioarray[] = $mform->createElement('radio', 'published', '', get_string('yes', 'local_events'),1);
        $radioarray[] = $mform->createElement('radio', 'published', '', get_string('no', 'local_events'),0);
        $mform->addGroup($radioarray, 'published', get_string('published', 'local_cpd'), array(' '), false);
        $mform->hideif('published', 'type', 'neq', 5);

        $mform->addElement('text','publisher', get_string('publisher', 'local_cpd'),'maxlength="254" size="50"');
        //$mform->addRule('publisher', get_string('missingpublisher', 'local_cpd'), 'required', null, 'server');
        $mform->setType('publisher', PARAM_TEXT);
        $mform->hideif('publisher', 'published', 'eq', 0);
        $mform->hideif('publisher', 'type', 'neq', 5);
       

        $mform->addElement('text','wordcount', get_string('wordcount', 'local_cpd'),'maxlength="254" size="50"');
        $mform->setType('wordcount', PARAM_TEXT);
        $mform->hideif('wordcount', 'type', 'neq', 5);

        $mform->addElement('text','pagecount', get_string('pagecount', 'local_cpd'),'maxlength="254" size="50"');
        $mform->setType('pagecount', PARAM_TEXT);
        $mform->hideif('pagecount', 'type', 'neq', 5);

        $filemanageroptions = array(
            'accepted_types' => array('pdf'),
            'maxbytes' => 2097152,
            'maxfiles' => 1,
        );
        $mform->addElement('filemanager', 'attachment', get_string('attachment', 'local_cpd'), null, $filemanageroptions);
        $mform->addRule('attachment', get_string('missingattachment', 'local_cpd'), 'required', null);

        $mform->addElement('editor','comment', get_string('comment', 'local_cpd'));
        $mform->setType('comment', PARAM_TEXT);

        $mform->addElement('text','creditedhours', get_string('creditedhours', 'local_cpd'),'maxlength="254" size="50" placeholder="'.get_string('maximumhours','local_cpd',$maximumhours).'"');
        $mform->addRule('creditedhours', get_string('missingcreditedhours', 'local_cpd'), 'required', null, 'server');
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
      
        if ($data['type'] == 1 || $data['type'] == 2 || $data['type'] == 3) {
            if (empty($data['author'])) {
                $errors['author'] = get_string('missingauthor', 'local_cpd');
            }
        }
        if ($data['type'] == 4) {
            if (empty($data['organizer'])) {
                $errors['organizer'] = get_string('missingorganizer', 'local_cpd');
            }
            if (empty($data['activitydate4'])) {
                $errors['activitydate4'] = get_string('required','local_cpd');
            }
        }

        if ($data['type'] == 5) {
            if($data['published'] == 1) {
                if (empty($data['publisher'])) {
                    $errors['publisher'] = get_string('missingpublisher', 'local_cpd');
                }
            }
            if (empty($data['wordcount'])) {
                $errors['wordcount'] = get_string('missingwordcount', 'local_cpd');
            }
            if (empty($data['pagecount'])) {
                $errors['pagecount'] = get_string('missingpagecount', 'local_cpd');
            }
            if (empty($data['activitydate5'])) {
                $errors['activitydate5'] = get_string('required','local_cpd');
            }
        }
        
        if(isset($data['pagecount']) &&!empty(trim($data['pagecount']))){
            if(!is_numeric(trim($data['pagecount']))){
                $errors['pagecount'] = get_string('numeric','local_cpd');
            }
            if(is_numeric(trim($data['pagecount']))&&trim($data['pagecount'])<0){
                $errors['pagecount'] = get_string('positive_numeric','local_cpd');
            }
        }
        if(isset($data['wordcount']) &&!empty(trim($data['wordcount']))){
            if(!is_numeric(trim($data['wordcount']))){
                $errors['wordcount'] = get_string('numeric','local_cpd');
            }
            if(is_numeric(trim($data['wordcount']))&&trim($data['wordcount'])<0){
                $errors['wordcount'] = get_string('positive_numeric','local_cpd');
            }
        }

        if(isset($data['creditedhours']) &&!empty(trim($data['creditedhours']))){
            if($data['creditedhours'] > $data['maximumhours']) {
                $errors['creditedhours'] = get_string('maximumhours_err','local_cpd',$data['maximumhours']);
            }
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
        //require_capability('local/cpd:addlearning', $this->get_context_for_dynamic_submission());
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
            $this->set_data(['title' => $data->title, 'author' => $data->author, 'relationtocpd' => $data->relationtocpd, '	whatlearned' => ['text' => $data->whatlearned], 'creditedhours' => $data->creditedhours]);
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
