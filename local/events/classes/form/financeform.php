<?php
    namespace local_events\form;
    use core_form\dynamic_form ;
    use moodle_url;
    use context;
    use context_system;
    use local_events;
    use local_events\events;
    /**
     * 
     */
    class financeform extends dynamic_form
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
        $eventid = $this->optional_param('eventid', 0, PARAM_INT);
        $mform->addElement('hidden', 'id', $id, array('id' => 'id'));
        $mform->setType('id', PARAM_INT);
        $mform->addElement('hidden', 'eventid', $eventid, array('eventid' => 'eventid'));
        $mform->setType('eventid', PARAM_INT);
        $attributes = array('1' => 'income','2' => 'expenses');
        $radioarray = array();
        $radioarray[] = $mform->createElement('radio', 'type', '', get_string('income', 'local_events'), 1, $attributes);
        $radioarray[] = $mform->createElement('radio', 'type', '', get_string('expenses', 'local_events'), 2, $attributes);
        $mform->addGroup($radioarray, 'type','', array('class' => 'regtype'), false);
        $mform->setDefault('type', 1);

        $exp_array = array();
        $exp_array[] = $mform->createElement('radio', 'expensetype', '', get_string('speakers', 'local_events'), 1, $attributes);
        $exp_array[] = $mform->createElement('radio', 'expensetype', '', get_string('partners', 'local_events'), 2, $attributes);
        $exp_array[] = $mform->createElement('radio', 'expensetype', '', get_string('logistics', 'local_events'), 3, $attributes);
        $mform->addGroup($exp_array, 'expensetype','', array('class' => 'regtype1'), false);
        $mform->setDefault('expensetype', 1);
        $mform->hideIf('expensetype', 'type', 'eq', 1); 

        $speaker = array();
        $speaker[''] = get_string('selectspeaker','local_events');
        $speakerlist = $this->_ajaxformdata['speakerid'];
        if (!empty($speakerlist) || $speakerlist == '0') {
            $speaker = events::ajax_datalist(array($speakerlist), $id, 'local_speakers');
        }
        $speakeroptions = array(
            'ajax' => 'local_events/form_selector_datasource',
            'data-type' => 'speakerlist',
            'id' => 'el_speaker',
            'data-speakerid' => '',
            'class' => 'speakerid',
            'data-eventid' => $eventid ,
            'data-finance' =>  true,
            'multiple' => false,
            'noselectionstring' => get_string('selectspeaker', 'local_events'),
        );
        $mform->addElement('autocomplete','speakerid', get_string('speaker', 'local_events'), $speaker, $speakeroptions);
        $mform->setType('speakerid', PARAM_RAW);
        $mform->hideIf('speakerid', 'type', 'eq', 1); 
        $mform->hideIf('speakerid', 'expensetype', 'eq', 2);
        $mform->hideIf('speakerid', 'expensetype', 'eq', 3);

        $partner = array();
        $partner[''] = get_string('selectpartner','local_events');
        $partnerlist = $this->_ajaxformdata['partnerid'];
        if (!empty($partnerlist) || $partnerlist == '0') {
            $partner = events::ajax_datalist(array($partnerlist), $id, 'local_partners');
        }
        $partneroptions = array(
            'ajax' => 'local_events/form_selector_datasource',
            'data-type' => 'partnerlist',
            'id' => 'el_partner',
            'data-partnerid' => '',
            'data-eventid' => $eventid,
            'data-finance' =>  true,
            'multiple' => false,
            'noselectionstring' => get_string('selectpartner', 'local_events'),
        );

        $mform->addElement('autocomplete','partnerid', get_string('partner', 'local_events'), $partner, $partneroptions);
        $mform->setType('partnerid', PARAM_RAW);
        $mform->hideIf('partnerid', 'type', 'eq', 1); 
        $mform->hideIf('partnerid', 'expensetype', 'eq', 1);
        $mform->hideIf('partnerid', 'expensetype', 'eq', 3);

        $mform->addElement('text','logistic', get_string('estimatedbudget', 'local_events'),'maxlength="254" size="50"');
        $mform->setType('logistic', PARAM_TEXT);
        $mform->hideIf('logistic', 'type', 'eq', 1); 
        $mform->hideIf('logistic', 'expensetype', 'eq', 1);
        $mform->hideIf('logistic', 'expensetype', 'eq', 2);

        $sponsor = array();
        $sponsor[''] = get_string('selectsponsor','local_events');
        $sponsorlist = $this->_ajaxformdata['sponsorid'];
        if (!empty($sponsorlist) || $sponsorlist == '0') {
            $sponsor = events::ajax_datalist(array($sponsorlist), $id, 'local_sponsors');
        }

        $sponsoroptions = array(
            'ajax' => 'local_events/form_selector_datasource',
            'data-type' => 'sponsorlist',
            'id' => 'el_sponsor',
            'data-sponsorid' => '',
            'data-eventid' => $eventid ,
            'data-finance' =>  true,
            'multiple' => false,
            'noselectionstring' => get_string('selectsponsor', 'local_events'),
        );
        $mform->addElement('autocomplete','sponsorid', get_string('sponsors', 'local_events') , $sponsor, $sponsoroptions);
        $mform->setType('sponsorid', PARAM_RAW);
        $mform->hideIf('sponsorid', 'type', 'eq', 2); 

        $mform->addElement('text','itemname', get_string('itemname', 'local_events'),'maxlength="254" size="50"');
        $mform->addRule('itemname', get_string('required'), 'required', null, 'server');
        $mform->setType('itemname', PARAM_TEXT);

        $mform->addElement('text','amount', get_string('amount', 'local_events'),'maxlength="254" size="50"');
        $mform->addRule('amount', get_string('required'), 'required', null, 'server');
        $mform->setType('amount', PARAM_TEXT);
        //$mform->hideIf('amount', 'expensetype', 'eq', 2);
       
        $mform->addElement('filepicker','billingfile', get_string('billingfile', 'local_events'),'maxlength="254" size="50"');
        $mform->setType('billingfile', PARAM_TEXT);
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
        if($data['type'] == "2") {
            if ($data['expensetype'] == "1") {
                if (empty($data['speakerid']) && $data['speakerid']!='0') {
                    $errors['speakerid'] = get_string('pleaseaddspeaker', 'local_events');
                }
                if (empty($data['amount'])) {
                    $errors['amount'] = get_string('missingamountofmoney', 'local_events');
                }
            }
            if ($data['expensetype'] == "2") {
                if (empty($data['partnerid']) && $data['partnerid']!='0') {
                    $errors['partnerid'] = get_string('pleaseaddpartner', 'local_events');
                }
            }
            if ($data['expensetype'] == "3") {
                if (empty($data['logistic'])) {
                    $errors['logistic'] = get_string('missingestimatedbudget', 'local_events');
                }
                if(!is_numeric(trim($data['logistic']))){
                    $errors['logistic'] = get_string('numeric','local_events');
                }
                if(is_numeric(trim($data['logistic']))&&trim($data['logistic'])<0){
                    $errors['logistic'] = get_string('positive_numeric','local_events');
                }
                if (empty($data['amount'])) {
                    $errors['amount'] = get_string('missingamountofmoney', 'local_events');
                }
            }
        }
        if ($data['type'] == "1") {
            if (empty($data['sponsorid']) && $data['sponsorid']!='0') {
                $errors['sponsorid'] = get_string('pleaseaddsponsor', 'local_events');
            }
            if (empty($data['amount'])) {
                $errors['amount'] = get_string('missingamountofmoney', 'local_events');
            }
        }

        if(!empty($data['amount']) && !is_numeric(trim($data['amount']))){
            $errors['amount'] = get_string('numeric','local_events');
        }
        if(!empty($data['amount']) && is_numeric(trim($data['amount']))&&trim($data['amount'])<0){
            $errors['amount'] = get_string('positive_numeric','local_events');
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
        (new local_events\events)->create_finance($data);
    }

    /**
     * Load in existing data as form defaults
     */
    public function set_data_for_dynamic_submission(): void {
        global $DB;
        if ($id = $this->optional_param('id', 0, PARAM_INT)) {
            $data = $DB->get_record('local_event_attendees', ['id' => $id], '*', MUST_EXIST);
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
        return new moodle_url('/local/events/index.php',
            ['action' => 'editcategory', 'id' => $id]);
    }
    }