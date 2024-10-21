<?php
    namespace local_events\form;
    use core_form\dynamic_form ;
    use moodle_url;
    use context;
    use context_system;
    use local_events;
    use local_events\events;
    require_once($CFG->libdir . '/formslib.php');
    /**
     * 
     */
    class speakerform extends dynamic_form
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
        $eventid = $this->optional_param('eventid', 0, PARAM_INT);

        $mform->addElement('hidden', 'id', $id, array('id' => 'id'));
        $mform->setType('id', PARAM_INT);

        $mform->addElement('hidden', 'eventid', $eventid, array('eventid' => 'eventid'));
        $mform->setType('eventid', PARAM_INT);

        $speaker = array();
        $speakerlist = $this->_ajaxformdata['speaker'];
        if (!empty($speakerlist) || $speakerlist == '0') {
            $speaker = events::ajax_datalist(array($speakerlist), $id, 'local_speakers');
        }
        $speakeroptions = array(
            'ajax' => 'local_events/form_selector_datasource',
            'data-type' => 'speakerlist',
            'id' => 'el_speaker',
            'data-speakerid' => '',
            'data-eventid' => $eventid,
            'class' => 'speakerid',
            'multiple' => false,
            'noselectionstring' => get_string('selectspeaker', 'local_events'),
        );
        if ($id > 0) {

            $mform->addElement('static', 'speakername', get_string('speaker', 'local_events'));
            $mform->addElement('hidden', 'speaker');
            $mform->setType('speaker', PARAM_INT);

        } else {
            $mform->addElement('autocomplete','speaker', get_string('speaker', 'local_events'), $speaker, $speakeroptions);
            $mform->setType('speaker', PARAM_RAW);
            $mform->addRule('speaker', get_string('required'), 'required', null, 'server');
        }
        $mform->addElement('text', 'sellingprice', get_string('sellingpricep','local_events', get_string('speaker','local_events')), 'maxlength="100" size = "20"');
        $mform->addRule('sellingprice', get_string('missingsellingpricep','local_events'), 'required', null, 'server');
        $mform->setType('sellingprice', PARAM_TEXT);

        $mform->addElement('text', 'actualprice', get_string('actualpricep','local_events', get_string('speaker', 'local_events')), 'maxlength="100" size="20"');
        $mform->addRule('actualprice', get_string('missingactualpricep','local_events'), 'required', null, 'server');
        $mform->setType('actualprice', PARAM_TEXT);
        if ($id > 0) { 
            $mform->addElement('text', 'name', get_string('speakername','local_events'), 'maxlength="100" size="20"');
            $mform->addRule('name', get_string('missingspeakername','local_events'), 'required', null, 'server');
            $mform->setType('name', PARAM_TEXT);
               
            $mform->addElement('text', 'specialist', get_string('speakerspecialist','local_events'), 'maxlength="100" size="20"');
            $mform->addRule('specialist', get_string('missingspecialist','local_events'), 'required', null, 'server');
            $mform->setType('specialist', PARAM_TEXT);
               
            $mform->addElement('textarea','biography', get_string('biography', 'local_events'));
            $mform->setType('biography', PARAM_TEXT);
    
            $mform->addElement('text','linked_profile', get_string('linked_profile', 'local_events'));
            $mform->setType('linked_profile', PARAM_RAW);
            $filemanageroptions = array(
                'accepted_types' => array('.jpg', '.png'),
                'maxbytes' => 0,
                'maxfiles' => 1,
            );
            $mform->addElement('filemanager', 'logo', get_string('logo', 'local_events'), '', $filemanageroptions);
            $mform->setType('logo', PARAM_RAW);
                
        } else {
            $mform->addElement('text', 'name', get_string('speakername','local_events'), 'maxlength="100" size="20"');
            $mform->setType('name', PARAM_TEXT);
            $mform->hideif('name', 'speaker', 'neq', 0);
    
            $mform->addElement('text', 'specialist', get_string('specialist','local_events'), 'maxlength="100" size="20"');
            $mform->setType('specialist', PARAM_TEXT);
            $mform->hideif('specialist', 'speaker', 'neq', 0);
    
            $mform->addElement('textarea','biography', get_string('biography', 'local_events'));
            $mform->setType('biography', PARAM_TEXT);
            $mform->hideif('biography', 'speaker', 'neq', 0);
    
            $mform->addElement('text','linked_profile', get_string('linked_profile', 'local_events'));
            //$mform->setType('linked_profile', PARAM_RAW);
            $mform->setType('linked_profile', PARAM_NOTAGS);
            $mform->setDefault('text','');
            $mform->hideif('linked_profile', 'speaker', 'neq', 0);

            $filemanageroptions = array(
                'accepted_types' => array(get_string('jpg','local_events'), get_string('png','local_events'),),
                'maxbytes' => 0,
                'maxfiles' => 1,
            );
            $mform->addElement('filemanager', 'logo', get_string('picture', 'local_events'), '', $filemanageroptions);
            $mform->setType('logo', PARAM_TEXT);
            $mform->hideif('logo', 'speaker', 'neq', 0);
        }
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

        if(empty($data['speaker']) && $data['speaker']!='0') {
            $errors['speaker'] = get_string('pleaseaddspeaker','local_events');
        }
        if($data['speaker'] == "0") {
            if (empty($data['name'])) {
                $errors['name'] = get_string('missingspeakername','local_events');
            }
            if (empty($data['specialist'])) {
            $errors['specialist'] = get_string('missingspecialist','local_events');
            }
            /*if (empty($data['linked_profile'])) {
                $errors['linked_profile'] = get_string('missinglinked_profile','local_events');
            }*/
        }
        if (!empty(trim($data['linked_profile']))  && !preg_match('/\b(?:(?:https?|ftp):\/\/|www\.)[-a-z0-9+&@#\/%?=~_|!:,.;]*[-a-z0-9+&@#\/%=~_|]/i', $data['linked_profile']) ) {
            $errors['linked_profile'] = get_string('linkedin_err', 'local_events'); 
        }
        if(isset($data['sellingprice']) &&!empty(trim($data['sellingprice']))){
            if(!is_numeric(trim($data['sellingprice']))){
                $errors['sellingprice'] = get_string('numeric','local_events');
            }
            if(is_numeric(trim($data['sellingprice']))&&trim($data['sellingprice'])<0){
                $errors['sellingprice'] = get_string('positive_numeric','local_events');
            }
            if($data['sellingprice'] < $data['actualprice']) {
                $errors['sellingprice'] = get_string('estbudgeterr','local_events');
            }
        }

        if(isset($data['actualprice']) &&!empty(trim($data['actualprice']))){
            if(!is_numeric(trim($data['actualprice']))){
                $errors['actualprice'] = get_string('numeric','local_events');
            }
            if(is_numeric(trim($data['actualprice']))&&trim($data['actualprice'])<0){
                $errors['actualprice'] = get_string('positive_numeric','local_events');
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
        has_capability('local/organization:manage_event_manager', $this->get_context_for_dynamic_submission()) 
        || has_capability('local/events:manage', $this->get_context_for_dynamic_submission());
    }

    /**
     * Process the form submission, used if form was submitted via AJAX
     */
    public function process_dynamic_submission() {
        global $CFG, $DB;
        $context = context_system::instance();
        require_once($CFG->dirroot.'/user/profile/definelib.php');
         $data = $this->get_data();
         (new local_events\events)->add_update_speaker($data);
    }

    /**
     * Load in existing data as form defaults
     */
    public function set_data_for_dynamic_submission(): void {
        global $DB;
         if ($id = $this->optional_param('id', 0, PARAM_INT)) {
            $data = (new local_events\events)->set_speakerdata($id);
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
         return new moodle_url('/local/events/index.php');
    }
    }
