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
    class sponsorform extends dynamic_form
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
        $sponsor = array();
       // $sponsor[''] = get_string('selectsponsor','local_events');
        $sponsorlist = $this->_ajaxformdata['sponsor'];
        if (!empty($sponsorlist) || $sponsorlist == '0') {
            $sponsor = events::ajax_datalist(array($sponsorlist), $id, 'local_sponsors');
        }
        $sponsoroptions = array(
            'ajax' => 'local_events/form_selector_datasource',
            'data-type' => 'sponsorlist',
            'id' => 'el_sponsor',
            'data-sponsorid' => '',
            'data-eventid' => $eventid,
            'multiple' => false,
            'noselectionstring' => get_string('selectsponsor', 'local_events'),
        );

        if ($id > 0) {
            $mform->addElement('static', 'sponsorname', get_string('sponsor', 'local_events'));
            $mform->addElement('hidden', 'sponsor');
            $mform->setType('sponsor', PARAM_INT);

        } else {
            $mform->addElement('autocomplete','sponsor', get_string('sponsor', 'local_events'), $sponsor, $sponsoroptions);
            $mform->setType('sponsor', PARAM_RAW);
            $mform->addRule('sponsor', get_string('required'), 'required', null, 'server');
        }

        $mform->addElement('text', 'amount', get_string('sponsoramount','local_events'), 'maxlength="100" size="20"');
        $mform->addRule('amount', get_string('missingamount', 'local_events'), 'required', null, 'server');
        $mform->setType('amount', PARAM_RAW);
        if ($id > 0) {
            $mform->addElement('text', 'name', get_string('sponsorname','local_events'), 'maxlength="100" size="20"');
            $mform->addRule('name', get_string('missingsponsorname', 'local_events'), 'required', null, 'server');
            $mform->setType('name', PARAM_TEXT);

            $mform->addElement('textarea','biography', get_string('biography', 'local_events'));
            $mform->setType('biography', PARAM_TEXT);
            
        } else {
            $mform->addElement('text', 'name', get_string('sponsorname','local_events'), 'maxlength="100" size="20"');
            $mform->setType('name', PARAM_TEXT);
            $mform->hideif('name', 'sponsor', 'neq', 0);
    
            $mform->addElement('textarea','biography', get_string('biography', 'local_events'));
            $mform->setType('biography', PARAM_TEXT);
            $mform->hideif('biography', 'sponsor', 'neq', 0);
        }
        $options = array();
        $options[null] = get_string('select','local_events');
        $options['0'] = get_string('platinum','local_events');
        $options['1'] = get_string('gold','local_events');
        $options['2'] = get_string('silver','local_events');
        $mform->addElement('select','category', get_string('sponsorcategory', 'local_events'),$options);
        $mform->addRule('category', get_string('missingcategory','local_events'), 'required', null, 'server');
        $mform->setType('category', PARAM_TEXT);

        if ($id > 0) {
            $mform->addElement('text', 'website', get_string('website','local_events'), 'maxlength="100" size="20"');
            $mform->setType('website', PARAM_RAW);
            $filemanageroptions = array(
                'accepted_types' => array('.jpg', '.png'),
                'maxbytes' => 0,
                'maxfiles' => 1,
            );
            $mform->addElement('filemanager', 'logo', get_string('sponsorlogo', 'local_events'), '', $filemanageroptions);
            $mform->setType('logo', PARAM_RAW);
        } else {
            $mform->addElement('text', 'website', get_string('website','local_events'), 'maxlength="100" size="20"');
            $mform->setType('website', PARAM_RAW);
            $mform->hideif('website', 'sponsor', 'neq', 0);
            $filemanageroptions = array(
                'accepted_types' => array(get_string('jpg','local_events'), get_string('png','local_events')),
                'maxbytes' => 0,
                'maxfiles' => 1,
            );
            $mform->addElement('filemanager', 'logo', get_string('sponsorlogo', 'local_events'), '', $filemanageroptions);
            $mform->setType('logo', PARAM_RAW);
            $mform->hideif('logo', 'sponsor', 'neq', 0);
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
        if(empty($data['sponsor']) && $data['sponsor']!='0') {
            $errors['sponsor'] = get_string('pleaseaddsponsor','local_events');
        }
        if($data['sponsor'] == "0") {
            if (empty($data['name'])) {
                $errors['name'] = get_string('missingsponsorname','local_events');
            }
        }
        if(isset($data['amount']) &&!empty(trim($data['amount']))){
            if(!is_numeric(trim($data['amount']))){
                $errors['amount'] = get_string('numeric','local_events');
            }
            if(is_numeric(trim($data['amount']))&&trim($data['amount'])<0){
                $errors['amount'] = get_string('positive_numeric','local_events');
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
        $context = context_system::instance();
        $data = $this->get_data();
        (new local_events\events)->add_update_sponsor($data);
        //file_save_draft_area_files($data->logo, $context->id, 'local_events', 'eventimage', $data->logo);
        //$this->save_stored_file('logo', $context->id, 'local_events', 'sponsorlogo',  $data->logo, '/', null, true);
    }

    /**
     * Load in existing data as form defaults
     */
    public function set_data_for_dynamic_submission(): void {
        global $DB;
        $context = context_system::instance();
         if ($id = $this->optional_param('id', 0, PARAM_INT)) {
            $data = (new local_events\events)->set_sponsordata($id);
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
