<?php
    namespace local_events;
    use core_form\dynamic_form ;
    use moodle_url;
    use context;
    use context_system;
    use moodleform;
    use local_events\events as events;
    /**
     * 
     */
    //defined('MOODLE_INTERNAL') || die;
    require_once "{$CFG->dirroot}/lib/formslib.php";
    
/**
 * TODO describe file form
 *
 * @package    local_events
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class form extends moodleform
    {
        protected $context;
    /** @var profile_define_base $field */
    public $field;
    /** @var \stdClass */
    protected $fieldrecord;
    protected $eventdetails;

    /**
     * Define the form
     */
    public function definition () {
        global $CFG, $DB, $PAGE;
        $systemcontext   = context_system::instance();
        $mform = $this->_form;
        $eventdetails = $this->_customdata['eventdetails'];
        $entitycode = $this->_customdata['eventcode'];
        $editoroptions  = $this->_customdata['editoroptions'];
        $id = $this->optional_param('id', 0, PARAM_INT);
        $eventstartdate = $eventdetails->eventstartdate;
        $eventenddate = $eventdetails->eventenddate;
        //$this->context = $context;
        $mform->addElement('hidden', 'submit_type', 'form');
        $mform->addElement('hidden', 'id', $id);
        $mform->setType('id', PARAM_INT);
        $mform->addElement('hidden', 'eventstartdate', $eventdetails->eventstartdate);
        $mform->addElement('hidden', 'eventenddate', $eventdetails->eventenddate);
        $mform->addElement('hidden', 'zoom', $eventdetails->zoom);
        $mform->setType('zoom', PARAM_INT);

        $mform->addElement('hidden', 'webex', $eventdetails->webex);
        $mform->setType('webex', PARAM_INT);

        $mform->addElement('hidden', 'teams', $eventdetails->teams);
        $mform->setType('teams', PARAM_INT);

        $mform->addElement('hidden', 'entitycode', $entitycode);
        $mform->setType('entitycode', PARAM_RAW);

        $contextid = $systemcontext->id;
        $mform->addElement('hidden', 'contextid', '', ['class' => 'contextid']);
        $mform->setType('contextid',PARAM_RAW);
        $mform->setDefault('contextid', $contextid);        

        $mform->addElement('text','title', get_string('titleenglish', 'local_events'),'maxlength="254" size="50"');
        $mform->addRule('title', get_string('missingtitle', 'local_events'), 'required',null,'client');
       // $mform->setType('title', PARAM_TEXT);
        
        $mform->addElement('text','titlearabic', get_string('titlearabic', 'local_events'),'maxlength="254" size="50"');
        $mform->addRule('titlearabic', get_string('missingtitlear', 'local_events'), 'required', null, 'client');
        //$mform->setType('titlearabic', PARAM_TEXT);

        $mform->addElement('editor','description', get_string('description'), null, $editoroptions);
        $mform->addRule('description', get_string('requireddesc','local_events'), 'required', null, 'client');
        $mform->setType('description', PARAM_RAW);
        
        if($eventstartdate == 0 || $eventstartdate==''){
            $mform->addElement('date_selector', 'startdate', get_string('lc_startdate','local_events'),array('optional' => false));
            $mform->addRule('startdate', get_string('required'), 'required', null, 'client');
            $mform->addHelpButton('startdate', 'startdateevents', 'local_events');
        } else {
            $eventstartdate = userdate($eventdetails->startdate, get_string('strftimedaydate', 'langconfig'));
            $mform->addElement('hidden', 'startdate', $eventdetails->startdate);
            $mform->setType('availablefrom', PARAM_INT);

            $mform->addElement('static', 'start_date', get_string('lc_startdate', 'local_events'),$eventstartdate);
            $mform->addHelpButton('start_date', 'startdateevents', 'local_events');
        } 

        if($eventenddate == 0 || $eventenddate=='') {
            $mform->addElement('date_selector', 'enddate', get_string('lc_enddate','local_events'), array('optional' => false));
            $mform->addRule('enddate', get_string('required'), 'required', null, 'client');
            $mform->addHelpButton('enddate', 'enddateevents', 'local_events');

            for ($i = 0; $i <= 23; $i++) {
                $hours[$i] = sprintf("%02d", $i);
            }
            for ($i = 0; $i < 60; $i++) {
                $minutes[$i] = sprintf("%02d", $i);
            }
            $starttimearr = [];
            $currentlang = current_language();
             if($currentlang == 'ar') {
                $starttimearr[] = $mform->createElement('select', 'eventslotmin', get_string('minute', 'form'), $minutes);
                $starttimearr[] = $mform->createElement('select', 'eventslothour', get_string('hour', 'form'), $hours);
            } else {
                $starttimearr[] = $mform->createElement('select', 'eventslothour', get_string('hour', 'form'), $hours);
                $starttimearr[] = $mform->createElement('select', 'eventslotmin', get_string('minute', 'form'), $minutes);
            }
            $mform->addGroup($starttimearr, 'timefrom', get_string('starttime', 'local_events'), array(' '), false);
            $mform->addRule('timefrom', get_string('missingtimefrom', 'local_events'), 'required', null);
        
            $mform->addElement('duration', 'eventduration', get_string('eventduration', 'local_events'),  ['units'=> [MINSECS], 'class' => 'examduration']);
            $mform->addRule('eventduration', get_string('missingeventduration', 'local_events'), 'required', null);

        } else {
            $event_enddate = userdate($eventdetails->enddate, get_string('strftimedaydate', 'langconfig'));
            $mform->addElement('hidden', 'enddate', $eventdetails->enddate);
            $mform->setType('availablefrom', PARAM_INT);
            $mform->addElement('static', 'end_date', get_string('lc_enddate', 'local_events'),$event_enddate);
            $mform->addHelpButton('end_date', 'enddateevents', 'local_events');
            $mform->addElement('hidden', 'eventduration', $eventdetails->slot);
    
            $starttimearr = [];
            $currentlang = current_language();
            if($currentlang == 'ar') {
                $starttimearr[] = $mform->createElement('select', 'eventslotmin', get_string('minute', 'form'), [$eventdetails->eventslotmin=>$eventdetails->eventslotmin]);
                $starttimearr[] = $mform->createElement('select', 'eventslothour', get_string('hour', 'form'), [$eventdetails->eventslothour=>$eventdetails->eventslothour]);
            } else {
                $starttimearr[] = $mform->createElement('select', 'eventslothour', get_string('hour', 'form'), [$eventdetails->eventslothour=>$eventdetails->eventslothour]);
                $starttimearr[] = $mform->createElement('select', 'eventslotmin', get_string('minute', 'form'), [$eventdetails->eventslotmin=>$eventdetails->eventslotmin]);
            }
            $mform->addGroup($starttimearr, 'timefrom', get_string('starttime', 'local_events'), array(' '), false);
            $mform->disabledIf('timefrom', 'eventenddate', 'eq', 1);

            $mform->addElement('hidden', 'eventduration', $eventdetails->eventduration);
            $mform->addElement('duration', 'event_duration', get_string('eventduration', 'local_events'),  ['units'=> [MINSECS], 'class' => 'examduration']);
            $mform->disabledIf('event_duration', 'eventenddate', 'eq', 1);
            // $mform->addRule('eventduration', get_string('missingeventduration', 'local_events'), 'required', null);
        }
        // start end time
       
        $mform->addElement('date_time_selector', 'registrationstart', get_string('reg_startdate','local_events'),array('optional' => false));
        $mform->addRule('registrationstart', get_string('required'), 'required', null, 'client');

        $mform->addElement('date_time_selector', 'registrationend', get_string('reg_enddate','local_events'), array('optional' => false));
        $mform->addRule('registrationend', get_string('required'), 'required', null, 'client');
        
        $mform->addElement('text', 'code', get_string('code','local_events'), 'maxlength="100" size="20"');
        $mform->addRule('code', get_string('missigncode','local_events'), 'required', null, 'client');

        $availablefromgroup=array();
        $availablefromgroup[] =& $mform->createElement('radio', 'cost', '',get_string('paid', 'local_events'), 1);
        $availablefromgroup[] =& $mform->createElement('radio', 'cost', '',get_string('free', 'local_events'), 0);
        $mform->addGroup($availablefromgroup, 'price', get_string('price', 'local_events'), '&nbsp&nbsp', false);
        $mform->setDefault('cost', 0);

        $mform->addElement('text', 'sellingprice', get_string('sellingprice','local_events'), 'maxlength="100" size="20"');
        //$mform->addRule('sellingprice',  get_string('missingsellingprice', 'local_events'), 'required', null, 'client');
       
        $mform->addElement('text', 'actualprice', get_string('actualprice','local_events'), 'maxlength="100" size="20"');
        //$mform->addRule('actualprice',  get_string('missingactualprice', 'local_events'), 'required', null, 'client');

        $mform->hideif('sellingprice', 'cost', 'eq', 0);
        $mform->hideif('actualprice', 'cost', 'eq', 0);

        $traxfree_array = array();
        $traxfree_array[] = $mform->createElement('radio', 'taxfree', '', get_string('yes', 'local_events'), 0);
        $traxfree_array[] = $mform->createElement('radio', 'taxfree', '', get_string('no', 'local_events'), 1);
        $mform->addGroup($traxfree_array, 'traxfreearr', get_string('taxfree', 'local_events'), array(' '), false);
        $mform->hideif('traxfreearr', 'cost', 'eq', 0);

        $typearray = array(0 => get_string('symposium','local_events'),
                            1 => get_string('forum','local_events'),
                            2 => get_string('conference','local_events'),
                            3 => get_string('workshop','local_events'),
                            4 => get_string('cermony','local_events'));
        $mform->addElement('select',  'type', get_string('eventtype','local_events'), $typearray);
        $mform->setType('eventtype', PARAM_INT);

        $filemanageroptions = array(
            'accepted_types' => array(get_string('jpg','local_events'),get_string('png','local_events')),
            'maxbytes' => 0,
            'maxfiles' => 1,
            );
        $mform->addElement('filemanager', 'logo', get_string('eventlogo', 'local_events'), '', $filemanageroptions);
        $mform->addRule('logo', get_string('required'), 'required', null);
       
        $langoptions = [1 => get_string('arabic', 'local_events'), 2 => get_string('english', 'local_events')];
        $languages = array();
        foreach ($langoptions AS $langkey => $langvalue) {
            $languages[] = &$mform->createElement('checkbox', "language[{$langkey}]", '', $langvalue, $langkey);
        }
        $mform->addGroup($languages, 'language', get_string('language', 'local_events'), [''], false);
        $mform->addRule('language', get_string('missinglanguage', 'local_events'), 'required', null);

       /* $languages=array();
        $languages[] = $mform->createElement('checkbox', 'language[arabic]', '', get_string('arabic','local_events'), 1);
        $languages[] = $mform->createElement('checkbox', 'language[english]', '', get_string('english','local_events'), 2);
        $mform->addGroup($languages, 'language', get_string('language', 'local_events'), ['<pre> </pre>'], false);
        $mform->addRule('language', get_string('missinglanguage', 'local_events'), 'required', null);*/

        /*$gender=array();
        $gender[] = $mform->createElement('checkbox', 'audiencegender[male]', '', get_string('male','local_events'), 1);
        $gender[] = $mform->createElement('checkbox', 'audiencegender[female]', '', get_string('female','local_events'), 2);
        $mform->addGroup($gender, 'audiencegender', get_string('gender', 'local_events'), ['<pre> </pre>'], false);
        $mform->addRule('audiencegender', get_string('missingender', 'local_events'), 'required', null);*/

        $typeitem = array();
        $genderoptions = [1 => get_string('male', 'local_events'),2 => get_string('female', 'local_events')];
        foreach ($genderoptions as $key => $value) {
            $typeitem[] = &$mform->createElement('checkbox',"audiencegender[{$key}]", '', $value, $key);
        }
        $mform->addGroup($typeitem, 'audiencegender', get_string('gender', 'local_events'), [''], false);
        $mform->addRule('audiencegender', get_string('missingender', 'local_events'), 'required', null);


        $statusarray = array(0 => get_string('active', 'local_events'),
                            1 => get_string('inactive', 'local_events'),
                            2 => get_string('cancelled', 'local_events'),
                            3 => get_string('closed', 'local_events'),
                            4 => get_string('archieved', 'local_events'));
        
        if($id > 0)
        {

            $event_endttime = ($eventdetails->enddate+$eventdetails->slot+$eventdetails->eventduration);
            $current_date = time();
            $eventdetails->status =  ($eventdetails->status == 0) ? (($event_endttime >= $current_date) ?  0 : 1) : $eventdetails->status;
        }
        $mform->addElement('select',  'status', get_string('eventstatus','local_events'), $statusarray);
        $mform->setType('status', PARAM_INT);

        $certificate=[];
        $certificate[''] = get_string('selectcertificate','local_events');
        $categoryid = $DB->get_field('course_categories', 'id', ['idnumber' => 'events']);
        if($categoryid) {
            $contextid = \context_coursecat::instance($categoryid);
            $certificatelist = $DB->get_records_sql("SELECT tc.id, tc.name  FROM {tool_certificate_templates} tc WHERE  contextid = $contextid->id");
            if($certificatelist) {
                foreach ($certificatelist AS $cer){
                    $certificate[$cer->id]=$cer->name;
                }
            }
        }
        $mform->addElement('autocomplete',  'certificate', get_string('certificateatd','local_events'), $certificate, $certificate);
        //$mform->setType('certificate', PARAM_INT);

        /*$radioarray = array();
        $radioarray[] = $mform->createElement('radio', 'requiredapproval', '', get_string('yes', 'local_events'),1);
        $radioarray[] = $mform->createElement('radio', 'requiredapproval', '', get_string('no', 'local_events'),0);
        $mform->addGroup($radioarray, 'radioarr1', get_string('requiredapproval', 'local_events'), array(' '), false);
        $mform->addGroupRule('radioarr1', get_string('required'), 'required', null, 'client');*/

        $radioarray = array();
        $radioarray[] = $mform->createElement('radio', 'sendemailtopreregister', '', get_string('yes', 'local_events'), 1);
        $radioarray[] = $mform->createElement('radio', 'sendemailtopreregister', '', get_string('no', 'local_events'), 0);
        $mform->addGroup($radioarray, 'radioarr2', get_string('sendemailtopreregister', 'local_events'), array(' '), false);
        $mform->addGroupRule('radioarr2', get_string('required'), 'required', null, 'client');

        $mform->addElement('text', 'logisticsestimatedbudget', get_string('estimatedbudget','local_events'), 'maxlength="100" size="20"');
        $mform->addRule('logisticsestimatedbudget', get_string('missingestimatedbudget','local_events'), 'required', null, 'client');
        //$mform->setType('logisticsestimatedbudget', PARAM_RAW);

        //$mform->addElement('text', 'logisticsactualbudget', get_string('actualbudget','local_events'), 'maxlength="100" size="20"');
        //$mform->addRule('logisticsactualbudget', get_string('missingactualbudget', 'local_events'), 'required', null, 'client');
        //$mform->setType('logisticsactualbudget', PARAM_RAW);

        $eventmanagers = array();
        $eventmanagerlist = $this->_ajaxformdata['eventmanager'];
        
        if (!empty($eventmanagerlist)) {
            $eventmanagers = events::events_managers((array)$eventmanagerlist ,$id);
        } elseif ($id > 0) {
            $eventmanagers = events::events_managers(array(),$id);
        }
        $manageroptions = array(
            'ajax' => 'local_events/form_selector_datasource',
            'data-type' => 'managerlist',
            'id' => 'el_managers',
            'data-managerid' => '',
            'multiple' => true,
            'noselectionstring' => get_string('selecteventmanager', 'local_events'),
        );
        if (is_siteadmin() || has_capability('local/events:manage', context_system::instance())) {
            $mform->addElement('autocomplete','eventmanager', get_string('eventmanager', 'local_events'), $eventmanagers, $manageroptions);
            $mform->setType('eventmanager', PARAM_RAW);
        } else {
            $mform->addElement('hidden', 'eventmanager', $eventdetails->eventmanager);
        }
        $mform->addElement('editor','targetaudience', get_string('targetaudience','local_events'));
        $mform->setType('targetaudience', PARAM_TEXT);

        $methodattributes = array('0' => 'inclass','1' => 'virtual');
        $methodradioarray = array();
        $methodradioarray[] = $mform->createElement('radio', 'method', '', get_string('inclass', 'local_events'),0, $methodattributes);
        $methodradioarray[] = $mform->createElement('radio', 'method', '', get_string('virtual', 'local_events'), 1, $methodattributes);
       
        if($eventenddate == 0 || $eventenddate == '') {
           $mform->addGroup($methodradioarray, 'method',get_string('eventmethod', 'local_events'), array(' '), false);
           // Inclass
            $halls = array();
            $hallslist = $this->_ajaxformdata['halladdress'];
            if (!empty($hallslist)) {
                $halls = events::events_halls((array)$hallslist, $id);
            } elseif ($id > 0) {
                $halls = events::events_halls(array(),$id);
            }
            $halloptions = array(
                'ajax' => 'local_events/form_selector_datasource',
                'data-type' => 'halllist',
                'id' => 'el_halls',
                'data-hallid' => '',
                'multiple' => false,
                'onchange' => "(function(e){ require(['local_hall/hallreserve'], function(s) {s.reservation('event');}) }) (event)",
                'class' => 'entityhall',
            );
            $mform->addElement('autocomplete', 'halladdress', get_string('halladress','local_events'), [null => get_string('selecthall', 'local_hall')]+$halls, $halloptions);
            $mform->hideIf('halladdress', 'method', 'eq', 1);
            $mform->hideIf('halladdress', 'eventduration[number]', 'eq', 0);
            $mform->hideIf('halladdress', 'eventslothour', 'eq', 0);

            $group = [];
            $group[] =& $mform->createElement('html', get_string('eventfiledsmandatory', 'local_events'));
            $mform->addGroup($group, 'hallalertgroup', '', ' ', false);
            $mform->hideIf('hallalertgroup', 'method', 'eq', 1);
            $mform->hideIf('hallalertgroup', 'halladdress', 'neq', '');
            if( $id ) {         
                $reservations = (new \local_hall\hall)->entityreservations($id, 'event');
            }

            $reservationgroup = [];
            $reservationgroup[] =& $mform->createElement('html', '<div id="hallinformation"><table class="generaltable table"><thead class="thead-light"><tr><th scope="col"><b>'.get_string('hall','local_events').'</b></th><th scope="col"><b>'.get_string('date','local_events').'</b></th><th scope="col"><b>'.get_string('seats','local_events').'</b></th></tr></thead><tbody class="entityhalldetails">'. $reservations .'</tbody></table></div>');
            $mform->addGroup($reservationgroup, 'reservationgroup', '', ' ', false);
            $mform->hideIf('reservationgroup', 'method', 'eq', 1);

            // Virtual
            $virtualtypesarray = array(1 => get_string('zoom', 'local_events'),
                                    2 => get_string('webex', 'local_events'),
                                    3 => get_string('teams','local_trainingprogram'));
            $select = array(null => get_string('select', 'local_events')) + $virtualtypesarray;
            $mform->addElement('select', 'virtualtype', [], $select);
            $mform->hideIf('virtualtype', 'method', 'eq', 0);
        } else {
            //var_dump($eventenddate); exit;
            $mform->addGroup($methodradioarray, 'method',get_string('eventmethod', 'local_events'), array(' '), false);
            $mform->disabledIf('method', 'eventenddate', 'eq', 1);
            // Inclass
            $halls = array();
            $hallslist = $this->_ajaxformdata['halladdress'];
            if (!empty($hallslist)) {
                $halls = events::events_halls((array)$hallslist, $id);
            } elseif ($id > 0) {
                $halls = events::events_halls(array(),$id);
            }
            $halloptions = array(
                'ajax' => 'local_events/form_selector_datasource',
                'data-type' => 'halllist',
                'id' => 'el_halls',
                'data-hallid' => '',
                'multiple' => false,
                'onchange' => "(function(e){ require(['local_hall/hallreserve'], function(s) {s.reservation('event');}) }) (event)",
                'class' => 'entityhall',
            );
            $mform->addElement('autocomplete', 'halladdress', get_string('halladress','local_events'), [null => get_string('selecthall', 'local_hall')]+$halls, $halloptions);
            $mform->hideIf('halladdress', 'method', 'eq', 1);
            $mform->hideIf('halladdress', 'eventduration[number]', 'eq', 0);
            $mform->hideIf('halladdress', 'eventslothour', 'eq', 0);
            $mform->hideIf('halladdress', 'eventenddate', 'eq', 1);

            if( $id ) {         
                $reservations = (new \local_hall\hall)->entityreservations($id, 'event');
            }

            $reservationgroup = [];
            $reservationgroup[] =& $mform->createElement('html', '<div id="hallinformation"><table class="generaltable table"><thead class="thead-light"><tr><th scope="col"><b>'.get_string('hall','local_events').'</b></th><th scope="col"><b>'.get_string('date','local_events').'</b></th><th scope="col"><b>'.get_string('seats','local_events').'</b></th></tr></thead><tbody class="entityhalldetails">'. $reservations .'</tbody></table></div>');
            $mform->addGroup($reservationgroup, 'reservationgroup', '', ' ', false);
            $mform->hideIf('reservationgroup', 'method', 'eq', 1);

            // Virtual
            $virtualtypesarray = array(1 => get_string('zoom', 'local_events'),
                                    2 => get_string('webex', 'local_events'),
                                    3 => get_string('teams','local_trainingprogram'));
            $select = array(null => get_string('select', 'local_events')) + $virtualtypesarray;
            $mform->addElement('select', 'virtualtype', [], $select);
            $mform->hideIf('virtualtype', 'method', 'eq', 0);
            $mform->disabledIf('virtualtype', 'eventenddate', 'eq', 1);
        }
        $this->set_data($eventdetails);
        $this->add_action_buttons();
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
        $hours = $data['eventslothour']*3600;
        $minutes = $data['eventslotmin']*60;
        $starttime = $hours+$minutes;

        $presenthours = date("H")*3600;
        $presentminutes = date("i")*60;
        $presenttime = $presenthours+$presentminutes;

        $record = $DB->get_record('local_events', ['id' => $data['id']]);
        if($data['title'] == ''){
            $errors['title'] = get_string('missingtitle', 'local_events');
        }
        if($data['titlearabic'] == ''){
            $errors['titlearabic'] = get_string('missingtitle', 'local_events');
        }
      //var_dump($data); exit;
        if($data['code'] == ''){
            $errors['code'] = get_string('missigncode','local_events');
        }
        if($data['description'] == ''){
            $errors['description'] = get_string('required');
        }
        if($data['logisticsestimatedbudget'] == ''){
            $errors['logisticsestimatedbudget'] = get_string('missingestimatedbudget', 'local_events');
        }
        if($data['cost']){
            if($data['sellingprice'] == '' || trim($data['sellingprice']) == 0){
                $errors['sellingprice'] = get_string('missingsellingprice', 'local_events');
            }
            if($data['actualprice'] == '' || trim($data['actualprice']) == 0 ){
                $errors['actualprice'] = get_string('actualpriceless', 'local_events');
            }
            if($data['actualprice'] > $data['sellingprice']){
                $errors['sellingprice'] = get_string('sellingpricepricehigher', 'local_events');
            }

            if(!empty(trim($data['sellingprice'])) && !preg_match('/^[0-9]*$/',trim($data['sellingprice']))) {
                 $errors['sellingprice'] = get_string('validsellingpricerequired', 'local_events'); 
            }
             if(!empty(trim($data['actualprice'])) && !preg_match('/^[0-9]*$/',trim($data['actualprice']))) {
                 $errors['actualprice'] = get_string('validactualpricerequired', 'local_events'); 
            }

        }

        if (empty($data['audiencegender'])){ 
            $errors['audiencegender'] = get_string('missingender', 'local_events');
        }

        if (empty($data['language'])){ 
            $errors['language'] = get_string('missinglanguage', 'local_events');
        }
        if ($event = $DB->get_record('local_events', array('code' => $data['code']), '*', IGNORE_MULTIPLE)) {
            if (empty($data['id']) || $event->id != $data['id']) {
                $errors['code'] = get_string('shortnametaken', 'local_events', $event->title);
            }
        }
        /*$event_startdate = date('Y-m-d', $data['startdate']);
        $today_date = date('Y-m-d');*/

        $event_startdate = ($data['startdate'] + $starttime);
        $today_date = time();

        $enddate = $data['enddate'];
        if (isset($data['startdate']) && $data['startdate'] &&
            isset($data['enddate']) && $data['enddate']) {
            $today = time();
            if(empty($data['id'])) {
                if($event_startdate < $today_date) {
                    $errors['startdate'] = get_string('startdateerror', 'local_events');
                }
            }
            if ($data['enddate'] < $data['startdate']) {
                $errors['enddate'] = get_string('enddateerror', 'local_events');
            }
        }
       /*if($data['id'] != 0) {
            if($enddate <= date('Y-m-d', strtotime('-1 day', $record->enddate))) {
                $errors['enddate'] = get_string('enddatenotextend','local_events', date('Y-m-d', $record->enddate));
            }            
        }*/
        //var_dump($data['startdate']); exit;
        /*$reg_startdate = date('Y-m-d', $data['registrationstart']);
        $reg_enddate = date('Y-m-d', $data['registrationend']);*/

        $reg_startdate = $data['registrationstart'];
        $reg_enddate = $data['registrationend'];
        $event_starttime = ($data['startdate'] + $starttime);

       
        if (isset($reg_startdate) && $reg_startdate && isset($reg_enddate) && $reg_enddate) {
           
            if($reg_startdate > $event_starttime) {
                $errors['registrationstart'] = get_string('regstarttimeerror', 'local_events');   
            }
            
     
            if($reg_enddate > $event_starttime) {
                $errors['registrationend'] = get_string('regendtimeerror', 'local_events');   
            }
            
            if ($reg_startdate > $event_starttime) {
                $errors['registrationstart'] = get_string('regstarterror', 'local_events');
            }

            if ($reg_enddate > $event_starttime ) {
                $errors['registrationend'] = get_string('regenderror', 'local_events');
            }
            if ($reg_enddate <= $reg_startdate) {
                $errors['registrationend'] = get_string('enddateerror', 'local_events');
            }
        }

        if($data['eventduration'] == 0) {
            $errors['eventduration'] = get_string('eventdurationnotbezero','local_events');
        } elseif (!is_numeric($data['eventduration'])) {
            $errors['eventduration'] = get_string('eventdurationnumaric','local_events');
        }
        if(is_numeric(trim($data['eventduration']))&&trim($data['eventduration']) < 0 ) {
            $errors['eventduration'] = get_string('positive_numeric','local_events');
        }
        if(isset($data['logisticsestimatedbudget']) &&!empty(trim($data['logisticsestimatedbudget']))){
            if(!is_numeric(trim($data['logisticsestimatedbudget']))){
                $errors['logisticsestimatedbudget'] = get_string('numeric','local_events');
            }
            if(is_numeric(trim($data['logisticsestimatedbudget']))&&trim($data['logisticsestimatedbudget'])<0){
                $errors['logisticsestimatedbudget'] = get_string('positive_numeric','local_events');
            }
        }

        if(isset($data['logisticsactualbudget']) &&!empty(trim($data['logisticsactualbudget']))){
            if(!is_numeric(trim($data['logisticsactualbudget']))){
                $errors['logisticsactualbudget'] = get_string('numeric','local_events');
            }
            if(is_numeric(trim($data['logisticsactualbudget']))&&trim($data['logisticsestimatedbudget'])<0){
                $errors['logisticsactualbudget'] = get_string('positive_numeric','local_events');
            }
        }

        if($starttime == 0) {
            $errors['timefrom'] = get_string('eventslothournotbeempty','local_events');
        } elseif($data['id'] == 0) {
            if($event_startdate == $today_date) {
                if ($starttime <= $presenttime) {
                    $errors['timefrom'] = get_string('starttimecannotbelessthannow','local_events');
                } 
            }           
        }
        if($data['method'] == 1 && empty($data['virtualtype'])) {
            $errors['virtualtype'] = get_string('virtualtypecannotbeempty','local_events');
        }

        if($data['method'] == 0 && empty($data['halladdress'])) {
            $errors['halladdress'] = get_string('halladdresscannotbeempty','local_events');
        }
    
        return $errors;
    }
    }
