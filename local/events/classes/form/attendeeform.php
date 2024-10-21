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
    class attendeeform extends dynamic_form
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
        $context = context_system::instance();
        $mform = $this->_form;
        $id = $this->optional_param('id', 0, PARAM_INT);
        $eventid = $this->optional_param('eventid', 0, PARAM_INT);

        $mform->addElement('hidden', 'id', $id, array('id' => 'id'));
        $mform->setType('id', PARAM_INT);
        $mform->addElement('hidden', 'eventid', $eventid, array('eventid' => 'eventid'));
        $mform->setType('eventid', PARAM_INT);
        if (empty($id)) {
            if(!is_siteadmin() && has_capability('local/organization:manage_organizationofficial',$context)) {
                $users = array();
                $userlist = $this->_ajaxformdata['userid'];
                if(!empty($userlist)) {
                    list($usersql, $userparams) = $DB->get_in_or_equal($userlist);
                    $fullname = (new \local_trainingprogram\local\trainingprogram)->user_fullname_case();
                    $users = $DB->get_records_sql_menu("SELECT u.id AS id, $fullname  FROM {user} u JOIN {local_users} lc ON lc.userid = u.id WHERE u.id $usersql",$userparams);
                }
                $programoptions = array(
                    'ajax' => 'local_events/form_selector_datasource',
                    'data-type' => 'userlist',
                    'id' => 'el_users',
                    'data-userid' => '',
                    'data-eventid' => $eventid,
                    'multiple' => true,
                    //'noselectionstring' => get_string('selectuser', 'local_events'),
                );
                $mform->addElement('autocomplete','userid', get_string('selectusers','local_events') , $users, $programoptions);
                $mform->addRule('userid', get_string('required'), 'required', null, 'server');
                //$mform->setType('userid', PARAM_RAW); 
            } else {
                $attributes = array('1' => 'existinguser','2' => 'newuser');
                $radioarray = array();
                $radioarray[] = $mform->createElement('radio', 'regtype', '', get_string('existingusers', 'local_events'), 1, $attributes);
                $radioarray[] = $mform->createElement('radio', 'regtype', '', get_string('newreg', 'local_events'), 2, $attributes);
                $mform->addGroup($radioarray, 'regtype','', array('class' => 'regtype'), false);
                $mform->setDefault('regtype', 1);
                $users = array();
                $userlist = $this->_ajaxformdata['userid'];
                if(!empty($userlist)) {
                    list($usersql, $userparams) = $DB->get_in_or_equal($userlist);
    
                    $fullname = (new \local_trainingprogram\local\trainingprogram)->user_fullname_case();
                    $users = $DB->get_records_sql_menu("SELECT u.id AS id, $fullname  FROM {user} u JOIN {local_users} lc ON lc.userid = u.id WHERE u.id $usersql",$userparams);
                }
                $programoptions = array(
                    'ajax' => 'local_events/form_selector_datasource',
                    'data-type' => 'userlist',
                    'id' => 'el_users',
                    'data-userid' => '',
                    'data-eventid' => $eventid,
                    'multiple' => true,
                    'noselectionstring' => get_string('selectusers', 'local_events'),
                );
                $selectprogram = [null => get_string('selectprogram', 'local_learningtracks')];
                $mform->addElement('autocomplete','userid', '' ,$users ,$programoptions);
                $mform->setType('userid', PARAM_RAW);
                $mform->hideIf('userid', 'regtype', 'eq', 2); 
    
                $mform->addElement('text','name', get_string('attendeename', 'local_events'),'maxlength="254" size="50"');
                $mform->setType('name', PARAM_TEXT);
                $mform->hideIf('name', 'regtype', 'eq', 1);
        
                $mform->addElement('text','email', get_string('email', 'local_events'),'maxlength="254" size="50"');
                $mform->setType('email', PARAM_TEXT);
                $mform->hideIf('email', 'regtype', 'eq', 1);
            }
            
        } else {

            $userid = $DB->get_field('local_event_attendees','userid',['id'=>$id]);

            if($userid) {

                $userrecord = $DB->get_record('local_users',['userid'=>$userid]);

                $fullname = ($lang == 'ar') ?  $userrecord->firstnamearabic.' '.$userrecord->middlenamearabic.' '.$userrecord->thirdnamearabic.' '.$userrecord->lastnamearabic :  $userrecord->firstname.' '.$userrecord->middlenameen.' '.$userrecord->thirdnameen.' '.$userrecord->lastname ;

                $mform->addElement('static','attendeename', get_string('attendeename', 'local_events'),$fullname);
                $mform->addElement('static','attendeeemail', get_string('email', 'local_events'),$userrecord->email);
            

            } else  {
              
                $mform->addElement('text','name', get_string('attendeename', 'local_events'),'maxlength="254" size="50"');
                $mform->addRule('name', get_string('required'), 'required', null, 'server');
                $mform->setType('name', PARAM_TEXT);

                $mform->addElement('text','email', get_string('email', 'local_events'),'maxlength="254" size="50"');
                $mform->addRule('email', get_string('required'), 'required', null, 'server');
                $mform->setType('email', PARAM_TEXT);

            }
            
        }
        $genderarray = array(1 => get_string('male', 'local_events'),
        2 =>get_string('female', 'local_events'));
        $select = array(null => get_string('gender','local_events'));
        $gengerlist = $select + $genderarray;
        $mform->addElement('select','audiencegender', get_string('gender','local_events'),$gengerlist);
   
        
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
        $context = context_system::instance();
        $errors = parent::validation($data, $files);
        $eventid = $data['eventid'];
        $eventdata = $DB->get_record('local_events', ['id' => $eventid]);
      /* if(!is_siteadmin() && has_capability('local/organization:manage_organizationofficial',$context)) {
           $totalseats = (new events)->events_available_seats($eventid);
            if(isset($data['userid']) && $eventdata->price > 0 ) {
                if(count($data['userid']) > $totalseats['availableseats']) {
                    $errors['userid'] = get_string('userscountismore', 'local_events', $totalseats['availableseats']); 
                }
            } 
        }
        if(is_siteadmin() || has_capability('local/organization:manage_event_manager', $context)) {*/

            if($eventdata->method == 0) {
                $totalseats = (new events)->events_available_seats($eventid);
                if(isset($data['userid'])) {
                    if($totalseats['availableseats'] < count($data['userid'])) {
                        $errors['userid'] = get_string('userscountismore', 'local_events', $totalseats['availableseats']); 
                    }
                }
            }
        //}
        $regtype = $data['regtype'];
        if ($regtype == "1") {
            if (empty($data['userid'])) {
                $errors['userid'] = get_string('pleaseselectuser', 'local_events');
            }
        }
        if ($regtype == "2") {
            if (empty($data['name'])) {
                $errors['name'] = get_string('missingname', 'local_events');
            } 
            if (empty($data['email'])) {
                $errors['email'] = get_string('missingemail', 'local_events');
            }
            if (empty($data['audiencegender'])) {
                $errors['audiencegender'] = get_string('required');
            }
            
       }
        $email = $data['email'];
        if ($email) {
            if (!preg_match("^[_a-z0-9-]+(\.[_a-z0-9-]+)*@[a-z0-9-]+(\.[a-z0-9-]+)*(\.[a-z]{2,3})$^",$email)){
                $errors['email'] = get_string('invalidemail');
            }
            else{
                return true;
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
        global $CFG, $DB, $PAGE;
        require_once($CFG->dirroot.'/user/profile/definelib.php');
        $data = $this->get_data();
        $systemcontext = context_system::instance();
        if (!is_siteadmin() && has_capability('local/organization:manage_organizationofficial', $systemcontext)) {
            $traineeids = base64_encode(implode(',', $data->userid));
            return ['returnparams' => $CFG->wwwroot.'/local/events/attendees.php?id='.$data->eventid.'&tuserids='.$traineeids];
        } else {
            (new local_events\events)->add_update_attendee($data);
            return ['returnparams1' => 1];
        }
    }

    /**
     * Load in existing data as form defaults
     */
    public function set_data_for_dynamic_submission(): void {
        global $DB;
        if ($id = $this->optional_param('id', 0, PARAM_INT)) {
            $data = $DB->get_record('local_event_attendees', ['id' => $id], '*', MUST_EXIST);
           /* $attendee = $DB->get_field('local_event_attendees','userid',['id' => $id]);
            if(!empty($attendee)) {
                $usergender = $DB->get_field('local_users','gender',['userid' => $attendee]);
                $gender = $usergender;
            } else {
                $gender = $data->audiencegender;
            }
            $this->set_data(['eventid' => $data->eventid, 'name' => $data->name, 'email' => $data->	email,
             'userid' => $data->userid, 'audiencegender' => $gender]);*/
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
