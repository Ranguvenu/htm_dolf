<?php
    namespace local_events\form;
    use core_form\dynamic_form ;
    use moodle_url;
    use context;
    use context_system;
    use local_events;
    use local_events\events as events;
    /**
     * 
     */
    class partnerform extends dynamic_form
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

        $partner = array();
        //$partner[''] = get_string('selectpartner','local_events');
        $partnerlist = $this->_ajaxformdata['partner'];
        if (!empty($partnerlist) || $partnerlist == '0') {
            $partner = events::ajax_datalist(array($partnerlist), $id, 'local_partners');
        }
      
        $partneroptions = array(
            'ajax' => 'local_events/form_selector_datasource',
            'data-type' => 'partnerlist',
            'id' => 'el_partner',
            'data-partnerid' => '',
            'data-eventid' => $eventid,
            'data-finance' => false,
            'multiple' => false,
            'noselectionstring' => get_string('selectpartner', 'local_events'),
        );

        if ($id > 0) {
            $mform->addElement('static', 'partnername', get_string('partner', 'local_events'));
            $mform->addElement('hidden', 'partner');
            $mform->setType('partner', PARAM_INT);

        } else {
            $mform->addElement('autocomplete','partner', get_string('partner', 'local_events'), $partner, $partneroptions);
            $mform->setType('partner', PARAM_RAW);
            $mform->addRule('partner', get_string('required'), 'required', null, 'server');
        }

        $mform->addElement('text', 'sellingprice', get_string('sellingpricep','local_events', get_string('partner','local_events')), 'maxlength="100" size="20"');
        $mform->addRule('sellingprice',  get_string('missingsellingpricep','local_events'), 'required', null, 'server');
        $mform->setType('sellingprice', PARAM_RAW);

        $mform->addElement('text', 'actualprice', get_string('actualpricep','local_events', get_string('partner','local_events')), 'maxlength="100" size="20"');
        $mform->addRule('actualprice', get_string('missingactualpricep','local_events'), 'required', null, 'server');
        $mform->setType('actualprice', PARAM_RAW);

        if ($id > 0) { 
            $mform->addElement('text', 'name', get_string('partnername','local_events'), 'maxlength="100" size="20"');
            $mform->addRule('name', get_string('missingname','local_events'), 'required', null, 'server');
            $mform->setType('name', PARAM_TEXT);
               
            $mform->addElement('text', 'specialist', get_string('specialist','local_events'), 'maxlength="100" size="20"');
            $mform->addRule('specialist', get_string('missingspecialist','local_events'), 'required', null, 'server');
            $mform->setType('specialist', PARAM_TEXT);
               
            $mform->addElement('textarea','biography', get_string('biography', 'local_events'));
            $mform->setType('biography', PARAM_TEXT);

            
            $filemanageroptions = array(
            'accepted_types' => array(get_string('jpg','local_events'), get_string('png','local_events')),
            'maxbytes' => 0,
            'maxfiles' => 1,
            );
            $mform->addElement('filemanager', 'logo', get_string('partnerlogo', 'local_events'), '', $filemanageroptions);
            $mform->setType('logo', PARAM_RAW);
    
            $filemanageroptions = array(
            'accepted_types' => array(get_string('jpg','local_events'), get_string('png','local_events')),
            'maxbytes' => 0,
            'maxfiles' => 1,
            );
            $mform->addElement('filemanager', 'picture', get_string('picture', 'local_events'), '', $filemanageroptions);
            $mform->setType('picture', PARAM_RAW);
    
            $mform->addElement('textarea', 'description', get_string('description','local_events'));
            $mform->setType('description', PARAM_TEXT);
                
        } else {
            $mform->addElement('text', 'name', get_string('partnername','local_events'), 'maxlength="100" size="20"');
            $mform->setType('name', PARAM_TEXT);
            $mform->hideif('name', 'partner', 'neq', 0);
    
            $mform->addElement('text', 'specialist', get_string('specialist','local_events'), 'maxlength="100" size="20"');
            $mform->setType('specialist', PARAM_TEXT);
            $mform->hideif('specialist', 'partner', 'neq', 0);
    
            $mform->addElement('textarea','biography', get_string('biography', 'local_events'));
            $mform->setType('biography', PARAM_TEXT);
            $mform->hideif('biography', 'partner', 'neq', 0);

            $filemanageroptions = array(
            'accepted_types' => array(get_string('jpg','local_events'), get_string('png','local_events')),
            'maxbytes' => 0,
            'maxfiles' => 1,
            );
            $mform->addElement('filemanager', 'logo', get_string('partnerlogo', 'local_events'), '', $filemanageroptions);
            $mform->setType('logo', PARAM_RAW);
            $mform->hideif('logo', 'partner', 'neq', 0);
    
            $filemanageroptions = array(
            'accepted_types' => array(get_string('jpg','local_events'), get_string('png','local_events')    ),
            'maxbytes' => 0,
            'maxfiles' => 1,
            );
            $mform->addElement('filemanager', 'picture', get_string('picture', 'local_events'), '', $filemanageroptions);
            $mform->setType('picture', PARAM_RAW);
            $mform->hideif('picture', 'partner', 'neq', 0);
    
            $mform->addElement('textarea', 'description', get_string('description','local_events'));
            $mform->setType('description', PARAM_TEXT);
            $mform->hideif('description', 'partner', 'neq', 0);
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
        if(empty($data['partner']) && $data['partner']!='0') {
            $errors['partner'] = get_string('pleaseaddpartner','local_events');
        }
        if($data['partner'] == "0") {
            if (empty($data['name'])) {
                $errors['name'] = get_string('missingname','local_events');
            }
            if (empty($data['specialist'])) {
            $errors['specialist'] = get_string('missingspecialist','local_events');
            }
            /*if (empty($data['description'])) {
                $errors['description'] = get_string('missingdescription','local_events');
            }*/
        }

       if(isset($data['sellingprice']) &&!empty(trim($data['sellingprice']))){
            if(!is_numeric(trim($data['sellingprice']))){
                $errors['sellingprice'] = get_string('numeric','local_events');
            }
            if(is_numeric(trim($data['sellingprice']))&&trim($data['sellingprice'])<0){
                $errors['sellingprice'] = get_string('positive_numeric','local_events');
            }
           /* if($data['sellingprice'] < $data['actualprice']) {
                $errors['sellingprice'] = get_string('estbudgeterr','local_events');
            }*/
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
        require_once($CFG->dirroot.'/user/profile/definelib.php');
         $data = $this->get_data();
         (new local_events\events)->add_update_partner($data);
    }

    /**
     * Load in existing data as form defaults
     */
    public function set_data_for_dynamic_submission(): void {
        global $DB;
         if ($id = $this->optional_param('id', 0, PARAM_INT)) {
            $data = (new local_events\events)->set_partnerdata($id);
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
