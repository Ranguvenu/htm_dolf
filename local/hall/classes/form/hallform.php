<?php
namespace local_hall\form;

use core_form\dynamic_form;
use moodle_url;
use context;
use context_system;
use local_hall;

class hallform extends dynamic_form { 

    private const CIRCLE=1;
    private const RECTANGLE=2;
    private const SQUARE=3;

    private const ENABLED=1;
    private const DISABLED=2;

    private const PROJECTOR=1;
    private const EARPHONE=2;
    private const TELEVISION=3;

    private $roomshape = [self::CIRCLE => 'Circle', 
                          self::RECTANGLE => 'A rectangle', 
                          self::SQUARE => 'Square'];

    private $availability = [self::ENABLED => 'Enabled', 
                             self::DISABLED => 'Not enabled'];

    public function definition() {
        global $USER, $CFG,$DB;
        $systemcontext = context_system::instance();
        $corecomponent = new \core_component();

        $mform = $this->_form;
        $id = $this->_customdata['id'];
        $editoroptions = $this->_customdata['editoroptions'];
        $systemcontext = context_system::instance();
        // Form Arguments
        $halllocation = $this->optional_param('halllocation', '', PARAM_RAW);
        $type = $this->optional_param('type', 0, PARAM_RAW);
        
        $mform->addElement('hidden', 'id', '');
        $mform->setType('id',PARAM_INT);

        $disabled = $halllocation ? ['disabled' => 'disabled'] : false;

        $mform->addElement('text', 'name', get_string('name', 'local_hall'));
        $mform->setType('name', PARAM_TEXT);
        $mform->addRule('name', get_string('missinghallname', 'local_hall'), 'required', null);

        $mform->addElement('text', 'code', get_string('code', 'local_hall'));
        $mform->setType('code', PARAM_TEXT);
        $mform->addRule('code', get_string('missinghallcode', 'local_hall'), 'required', null);

        $types=[];
        $types = ['1' => get_string('exam', 'local_hall'), '2' => get_string('trainingprogram', 'local_hall'), '3' => get_string('event', 'local_hall')];
        // print_r($type);
        if ($type!='null') {
            $selectedtype[$type] = $types[$type];
            $mform->addElement('select', 'type',get_string('type','local_hall'), array(null=>get_string('selecttype','local_hall')) + $selectedtype);
            
        }else{
            $mform->addElement('select', 'type',get_string('type','local_hall'), array(null=>get_string('selecttype','local_hall')) + $types);
        }
        $mform->addRule('type', get_string('missingtype', 'local_hall'), 'required', null);

        $cities = (new local_hall\hall)->listofcities();
        $mform->addElement('autocomplete', 'city',get_string('city','local_hall'), [null => get_string('selectcity','local_hall')] + $cities );
        $mform->addRule('city', get_string('missingcity', 'local_hall'), 'required', null);

        $halllocations=[];
        if (!is_siteadmin() && has_capability('local/organization:manage_event_manager', $systemcontext)) {
            $halllocations = ['Outside' => get_string('outside', 'local_hall')];
        } else {
            $halllocations = ['Inside' => get_string('Inside', 'local_hall'), 'Outside' => get_string('Outside', 'local_hall'), 'Clientside' => get_string('Clientside', 'local_hall')];
        }
        if ($halllocation != 'null') {
            $selectedhalllocations[$halllocation] = $halllocations[$halllocation];
            $mform->addElement('select', 'halllocation',get_string('halllocation','local_hall'), array(null=>get_string('selecthalllocations','local_hall')) + $selectedhalllocations);
        }else{
            $mform->addElement('select', 'halllocation',get_string('halllocation','local_hall'), array(null=>get_string('selecthalllocations','local_hall')) + $halllocations);
        }
    
        $mform->addRule('halllocation', get_string('missinghalllocation', 'local_hall'), 'required', null);


        $mform->addElement('text', 'maplocation', get_string('maplocation', 'local_hall'));
        $mform->setType('maplocation', PARAM_TEXT);
        $mform->addRule('maplocation', get_string('missingmaplocation', 'local_hall'), 'required', null);

        $mform->addElement('text', 'entrancegate', get_string('entrancegate', 'local_hall'));
        $mform->setType('entrancegate', PARAM_TEXT);
        $mform->addRule('entrancegate', get_string('missingentrancegate', 'local_hall'), 'required', null);
         $roomshape=[];
        $roomshape=['1'=>get_string('circle','local_hall'),'2'=>get_string('rectangle','local_hall'),'3'=>get_string('square','local_hall')];
        
        $mform->addElement('select', 'roomshape',get_string('roomshape','local_hall'), array(null=>get_string('selectroomshape','local_hall')) + $roomshape);
        $mform->addRule('roomshape', get_string('missingroomshape', 'local_hall'), 'required', null);

        $mform->addElement('text', 'buildingname', get_string('buildingname', 'local_hall'));
        $mform->setType('buildingname', PARAM_TEXT);
        $mform->addRule('buildingname', get_string('missingbuildingname', 'local_hall'), 'required', null);

        $mform->addElement('text', 'seatingcapacity', get_string('seatingcapacity', 'local_hall'));
        $mform->setType('seatingcapacity', PARAM_TEXT);
        $mform->addRule('seatingcapacity', get_string('missingseatingcapacity', 'local_hall'), 'required', null);
           $availability=[];
        $availability=['1'=>get_string('enabled','local_hall'),'2'=>get_string('notenabled','local_hall')];        
        $mform->addElement('select', 'availability',get_string('availability','local_hall'), array(null=>get_string('selectavailability','local_hall')) + $availability);
        $mform->addRule('availability', get_string('missingavailability', 'local_hall'), 'required', null);

        // start end time
        for ($i = 0; $i <= 23; $i++) {
            $hours[$i] = sprintf("%02d", $i);
        }
        for ($i = 0; $i < 60; $i++) {
            $minutes[$i] = sprintf("%02d", $i);
        }
        $starttimearr = [];
        $starttimearr[] = $mform->createElement('select', 'hallstarthour', get_string('hour', 'form'), $hours);
        $starttimearr[] = $mform->createElement('select', 'hallstartminute', get_string('minute', 'form'), $minutes);
        $mform->addGroup($starttimearr, 'timefrom', get_string('timefrom', 'local_events'), array(' '), false);
        $mform->addRule('timefrom', ['hallstarthour', 'hallstartminute'], 'required');

        $endtimearr = [];
        $endtimearr[] = $mform->createElement('select', 'hallendhour', get_string('hour', 'form'), $hours);
        $endtimearr[] = $mform->createElement('select', 'hallendminute', get_string('minute', 'form'), $minutes);
        $mform->addGroup($endtimearr, 'timeto', get_string('timeto', 'local_events'), array(' '), false);
        $mform->addRule('timeto', ['hallendhour', 'hallendminute'], 'required');      
      

        $mform->addElement('editor', 'description', get_string('description', 'local_hall'), null, $editoroptions);
        $mform->setType('description', PARAM_RAW);

       
        
        $equipment = [];
        $equipments = ['1' => get_string('projector', 'local_hall'), '2' => get_string('earphone', 'local_hall'), '3' => get_string('television', 'local_hall')];
        foreach($equipments AS $equipmentkey => $equipmentname){
                $equipment[] = $mform->createElement('checkbox', "equipmentavailable[{$equipmentkey}]", '', $equipmentname, $equipmentkey);
        }
        $mform->addGroup($equipment, 'equipmentavailable', get_string('equipmentavailable', 'local_hall'), ['<br/>'], false);
        $mform->addRule('equipmentavailable', get_string('missingequipmentavailable', 'local_hall'), 'required', null);
    }
    public function validation($data, $files) {
        $errors = array();
        global $DB, $CFG;
        $record = $DB->get_record('hall', ['code' => $data['code']]);

        if( $record && !$data['id']) {
            $errors['code'] = get_string('hallavailable','local_hall', $data['code']);
        }
        
        $hallname = $data['name'];
        if(strlen($hallname) < 3) {
            $errors['name'] = get_string('namevalidate','local_hall');
        } else if(!empty($record) && $data['id'] == 0 ) {
            $errors['name'] = get_string('hallavailable','local_hall', $data['name']);
        }

        if($data['hallstarthour'] == 0 && $data['hallstartminute'] ==0){
            $errors['timefrom'] = get_string('required');
        }

        if($data['hallendhour'] == 0 && $data['hallendminute'] ==0){
            $errors['timeto'] = get_string('required');
        }

        if($data['seatingcapacity'] < 0){
            $errors['seatingcapacity'] = get_string('seatingcapacitynegative', 'local_hall');
        }
    	// if (empty($data['phonenumber']))  {
         //        $errors['phonenumber'] = get_string('phone1required', 'local_userapproval');
         //    }
         if (!empty(trim($data['phonenumber'])) && !is_numeric($data['phonenumber'])){
            $errors['phonenumber'] = get_string('requirednumeric','local_userapproval');
         }
         if  (!empty(trim($data['phonenumber'])) && is_numeric(trim($data['phonenumber'])) && ( strlen(trim($data['phonenumber'])) < 5  || strlen(trim($data['phonenumber'])) > 10 )) {
             $errors['phonenumber'] = get_string('minimum5digitsallowed','local_userapproval');
         }
         if (!empty(trim($data['phonenumber'])) && is_numeric(trim($data['phonenumber'])) && (strlen(trim($data['phonenumber'])) >= 5  &&  strlen(trim($data['phonenumber'])) <= 10) &&  !preg_match('/^[5-9][0-9]/',trim($data['phonenumber']))) {
             $errors['phonenumber'] = get_string('startswith5','local_userapproval');
         }

        $hours = $data['hallstarthour']*3600;
        $minutes = $data['hallstartminute']*60;
        $starttime = $hours+$minutes;

        $hours = $data['hallendhour']*3600;
        $minutes = $data['hallendminute']*60;
        $endtime = $hours+$minutes;

        if($starttime > $endtime) {
            $errors['timefrom'] = get_string('starttimenotbeless', 'local_hall');
        }

        if($starttime == $endtime) {
            $errors['timefrom'] = get_string('notequal', 'local_hall');
        }

        if ( !is_numeric ($data['seatingcapacity'])) {
            $errors['seatingcapacity']= get_string('seatingnumber', 'local_hall');
        }
        if ( !is_numeric ($data['entrancegate'])) {
            $errors['entrancegate']= get_string('entrancegatenumber', 'local_hall');
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
         has_capability('local/hall:managehall', $this->get_context_for_dynamic_submission()) 
        || has_capability('local/organization:manage_hall_manager', $this->get_context_for_dynamic_submission());
    }
    

    /**
     * Process the form submission, used if form was submitted via AJAX
     */
    public function process_dynamic_submission() {
        global $CFG, $DB;
        require_once($CFG->dirroot.'/user/profile/definelib.php');
        $data = $this->get_data();
        (new local_hall\hall)->add_update_hall($data);
    }

    /**
     * Load in existing data as form defaults
     */
    public function set_data_for_dynamic_submission(): void {
        global $DB;
        $halllocation = $this->optional_param('halllocation', '', PARAM_RAW);
        $type = $this->optional_param('type', '', PARAM_RAW);
        $halldata['halllocation'] = $halllocation;
        $halldata['type'] = $type;
        if ($id = $this->optional_param('id', 0, PARAM_INT)) {
            $halldata = (new local_hall\hall)->set_hall($id);
        }
        $this->set_data($halldata);
    }

    /**
     * Returns url to set in $PAGE->set_url() when form is being rendered or submitted via AJAX
     *
     * @return moodle_url
     */
    protected function get_page_url_for_dynamic_submission(): moodle_url {
        $id = $this->optional_param('id', 0, PARAM_INT);
        return new moodle_url('/local/hall/index.php',
            ['action' => 'editcategory', 'id' => $id]);
    }    
}
