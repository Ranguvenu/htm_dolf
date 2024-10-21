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
    class agendaform extends dynamic_form
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
        $days_between = events::get_agenda_dates($eventid);
        $options = array();
        for($i = 0; $i < count($days_between); $i++) {
            $day = $i+1;
            $options[$i] = get_string('day', 'local_events').$day;
        }
       
        $mform->addElement('select','day', get_string('day', 'local_events'),$options);
        $mform->addRule('day', get_string('required'), 'required', null, 'server');
        $mform->setType('day', PARAM_TEXT);

        // start end time
        for ($i = 0; $i <= 23; $i++) {
            $hours[$i] = sprintf("%02d", $i);
        }
        for ($i = 0; $i < 60; $i++) {
            $minutes[$i] = sprintf("%02d", $i);
        }
        $starttimearr = [];
        $starttimearr[] = $mform->createElement('select', 'starthour', get_string('hour', 'form'), $hours);
        $starttimearr[] = $mform->createElement('select', 'startminute', get_string('minute', 'form'), $minutes);
        $mform->addGroup($starttimearr, 'timefrom', get_string('timefrom', 'local_events'), array(' '), false);
        $mform->addRule('timefrom', get_string('missingtimefrom', 'local_events'), 'required', null);
       
        $endtimearr = [];
        $endtimearr[] = $mform->createElement('select', 'endhour', get_string('hour', 'form'), $hours);
        $endtimearr[] = $mform->createElement('select', 'endminute', get_string('minute', 'form'), $minutes);
        $mform->addGroup($endtimearr, 'timeto', get_string('timeto', 'local_events'), array(' '), false);
        $mform->addRule('timeto', get_string('missingtimeto', 'local_events'), 'required', null);

        $select_agenda =[0 => get_string('others', 'local_events')];
        $speaker = array();
        //$speaker[''] = get_string('selectspeaker','local_events');
        $speakerlist = $this->_ajaxformdata['speaker'];
        if (!empty($speakerlist) || $speakerlist == '0') {
            $speaker =  $select_agenda+events::agenda_datalist(array($speakerlist), $id);
        } else if ($id > 0) {
            $speaker =  $select_agenda+events::agenda_datalist(array(), $id);
        } 
        $speakeroptions = array(
            'ajax' => 'local_events/form_selector_datasource',
            'data-type' => 'agenda_speakerlist',
            'id' => 'el_speaker',
            'data-agendaid' => '',
            'data-eventid' => $eventid,
            'multiple' => false,
            'noselectionstring' => get_string('selectspeaker', 'local_events'),
        );

        $mform->addElement('autocomplete','speaker', get_string('by', 'local_events'), $speaker, $speakeroptions);
        $mform->addRule('speaker',  get_string('missingspeaker', 'local_events'), 'required', null, 'server');
        $mform->setType('speaker', PARAM_RAW);

        $mform->addElement('text','title', get_string('topictitle', 'local_events'),'maxlength="254" size="50"');
        $mform->addRule('title',  get_string('missingtitle', 'local_events'), 'required', null, 'server');
        $mform->setType('title', PARAM_TEXT);

        $mform->addElement('textarea', 'description', get_string('agendadescription','local_events'));
        $mform->setType('description', PARAM_TEXT);
        
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
        $start_hours = $data['starthour']*3600;
        $start_minutes = $data['startminute']*60;
        $starttime = $start_hours+$start_minutes;
        if($starttime == 0 ) {
            $errors['timefrom'] = get_string('missingtimefrom','local_events');
        }
        $end_hours = $data['endhour']*3600;
        $end_minutes = $data['endminute']*60;
        $endtime = $end_hours+$end_minutes;
        if($endtime == 0 ) {
            $errors['timeto'] = get_string('missingtimeto','local_events');
        }
        $getevent = $DB->get_record('local_events',['id' => $data['eventid']]);
        $event_endtime = $getevent->slot + $getevent->eventduration;
       
        if(!empty($starttime) && $starttime < $getevent->slot) {
            $errors['timefrom'] = get_string('agendastarttime','local_events');
        }
        if(!empty($endtime) && $endtime < $starttime) {
            $errors['timeto'] = get_string('agendaendtime','local_events');
        }
        if(!empty($endtime) && $endtime > $event_endtime) {
            $errors['timeto'] = get_string('agendaeventendtime','local_events');
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
       // require_capability('moodle/site:config', $this->get_context_for_dynamic_submission());
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
         (new local_events\events)->add_update_agenda($data);
    }

    /**
     * Load in existing data as form defaults
     */
    public function set_data_for_dynamic_submission(): void {
        global $DB;
         if ($id = $this->optional_param('id', 0, PARAM_INT)) {
            $agendadata = (new local_events\events)->set_agenda($id);
            $this->set_data($agendadata);
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