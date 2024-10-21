<?php
namespace local_hall\form;

use core_form\dynamic_form;
use moodle_url;
use context;
use context_system;
use local_hall;

class schedulehallform extends dynamic_form { 

    public function definition() {
        global $USER, $CFG,$DB;
        $systemcontext = context_system::instance();
        $corecomponent = new \core_component();
        $entity = $this->optional_param('entity', NULL, PARAM_RAW);
        $entityid = $this->optional_param('entityid', 0, PARAM_INT);

        $mform = $this->_form;

        $systemcontext = context_system::instance();
        $id = $this->optional_param('id', 0, PARAM_INT);
        $mform->addElement('hidden', 'id', '');
        $mform->setType('id',PARAM_INT);
        $hallid = $this->optional_param('hallid', 0, PARAM_RAW);


        $mform->addElement('hidden', 'entity',$entity);
        $mform->setType('int', PARAM_RAW);
        $mform->addElement('hidden', 'entityid',$entityid);
        $mform->setType('int', PARAM_INT);
        if ($entityid!=0) {
            $halls =  $DB->get_records_sql_menu("SELECT id, name AS fullname FROM {hall} WHERE availability = 1 ");

            $halloptions = array(
                'ajax' => 'local_events/form_selector_datasource',
                'data-type' => 'examhalls',
                'id' => 'examhallschedules',
                'data-hallid' => '',
                'multiple' => false,
                'class' => 'examschedules',
                'module' => 'exam',
            );
            $halls = $mform->addElement('autocomplete', 'hallid', get_string('halladress','local_trainingprogram'), [null => get_string('selecthalls', 'local_trainingprogram')]+ $halls, $halloptions);
            $halls->setMultiple(true);
            $mform->addRule('hallid', null, 'required', null);
        } else {

            $mform->addElement('hidden', 'hallid',$hallid);
            $mform->setType('int', PARAM_INT);
        }

        if ($id==0) {
            $startdate=array(
                        'startyear' => 1950, 
                        'stopyear'  => date('Y',time()),
                        'optional'  => true
                    );
            $mform->addElement('date_selector', 'startdate',get_string('startdate', 'local_hall') ,$startdate ,'client');
            $mform->addRule('startdate', null, 'required', null, 'client');

            $enddate=array(
                            'startyear' => 1950, 
                            'stopyear'  => date('Y',time()),
                            'optional'  => true
                        );
            $mform->addElement('date_selector', 'enddate',get_string('enddate', 'local_hall') ,$enddate ,'client');
            $mform->addRule('enddate', null, 'required', null, 'client');
        }

        

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
        $mform->addGroup($starttimearr, 'timefrom', get_string('timefrom', 'local_hall'), array(' '), false);
        $mform->addRule('timefrom', ['starthour', 'startminute'], 'required');

        $endtimearr = [];
        $endtimearr[] = $mform->createElement('select', 'endhour', get_string('hour', 'form'), $hours);
        $endtimearr[] = $mform->createElement('select', 'endminute', get_string('minute', 'form'), $minutes);
        $mform->addGroup($endtimearr, 'timeto', get_string('timeto', 'local_hall'), array(' '), false);
        $mform->addRule('timeto', ['endhour', 'endminute'], 'required');

        if($id==0) {
            $checkboxes = array();
            $checkboxes[] = $mform->createElement('advcheckbox', 'monday',get_string('monday', 'local_hall'),null, array('group' => 1), [0,1]);
            $checkboxes[] = $mform->createElement('advcheckbox', 'tuesday',get_string('tuesday', 'local_hall'),null, array('group' => 1), [0,1]);
            $checkboxes[] = $mform->createElement('advcheckbox', 'wednesday',get_string('wednesday', 'local_hall'),null, array('group' => 1), [0,1]);
            $checkboxes[] = $mform->createElement('advcheckbox', 'thursday',get_string('thursday', 'local_hall'),null, array('group' => 1), [0,1]);
            $checkboxes[] = $mform->createElement('advcheckbox', 'friday',get_string('friday', 'local_hall'),null, array('group' => 1), [0,1]);
            $checkboxes[] = $mform->createElement('advcheckbox', 'satuarday',get_string('satuarday', 'local_hall'),null, array('group' => 1),[0,1]);
            $checkboxes[] = $mform->createElement('advcheckbox', 'sunday',get_string('sunday', 'local_hall'),null, array('group' => 1), [0,1]);
            $mform->addGroup($checkboxes, 'days',  get_string('days', 'local_hall'), array(' '), false);
            $mform->addRule('days', null, 'required');
        }

        $mform->addElement('text', 'seatingcapacity', get_string('seats', 'local_hall'));
        $mform->setType('seatingcapacity', PARAM_TEXT);

        $directedtoarray = [];
        $directedtoarray['0'] = get_string('select','local_hall');
        $directedtoarray['3'] = get_string('all','local_hall');
        $directedtoarray['1'] = get_string('male','local_hall');
        $directedtoarray['2'] = get_string('female','local_hall');
       

        $directedtooptions = array(
            'class' => 'el_gende',
            'multiple' => false,
            'noselectionstring' => get_string('noselection', 'local_trainingprogram'),

        );

        $mform->addElement('autocomplete','directedto', get_string('directedto', 'local_hall'),$directedtoarray, $directedtooptions);
        $mform->addRule('directedto', get_string('selectdirectedto','local_hall'), 'required');

        $radioarray=array();
        $radioarray[] = $mform->createElement('radio', 'status', '', get_string('publish', 'local_hall'), 0, $attributes);
        $radioarray[] = $mform->createElement('radio', 'status', '', get_string('unpublish', 'local_hall'), 1, $attributes);
        $mform->addGroup($radioarray, 'radioar', get_string('status', 'local_hall'), array(' '), false);
    }
    public function validation($data, $files) {
        $errors = array();
        global $DB, $CFG;
        $testerrors = [];
        $halls = $data['hallid'];
        $selectedhalls = (array)$halls;
        if (empty($data['directedto'])) {
            $errors['directedto'] = get_string('gendercannotbeempty', 'local_hall');
        }
        if (COUNT($selectedhalls) > 0) {
            foreach($selectedhalls as $selectedhall) {
                $hall = $DB->get_record('hall', ['id'=>$selectedhall]);
                $date = date('m/d/Y');
                $hallid = $selectedhall;
                $hallstartdate = $data['startdate'];
                $hallenddate = $data['enddate'];
                $id = $data['id'];
                $seatingcapacity = $data['seatingcapacity'];
                $hours = $data['starthour']*3600;
                $minutes = $data['startminute']*60;
                $getstarttime = $hours+$minutes;  
                $hours = $data['endhour']*3600;
                $minutes = $data['endminute']*60;
                $getendtime = $hours+$minutes;             

                $endtime = usertime($getendtime);
                $starttime = usertime($getstarttime);
                $selecteddate = usertime($data['startdate'] +  $getstarttime);
                $ctime=time();
                $curenttime=usertime($ctime);
        
                if($starttime == $endtime) {
                    $errors['timeto'] = get_string('notequal', 'local_hall');
                }        
                if($starttime > $endtime) {
                    $errors['timeto'] = get_string('cannotbeequal', 'local_hall');
                }        
                if($hallstartdate > $hallenddate) {
                    $errors['enddate'] = get_string('notequaldates', 'local_hall');
                }

                
                if($data['seatingcapacity']!="" && !is_numeric($data['seatingcapacity']) || $data['seatingcapacity']<0)
                {
                    $errors['seatingcapacity'] = get_string('validnumber', 'local_hall');
                }

                if($data['seatingcapacity'] > $hall->seatingcapacity)
                {
                    $errors['seatingcapacity'] = get_string('exceedofseats', 'local_hall');
                }


                $da=strtotime(date('Y-m-d', $hallstartdate));
                $sql = "SELECT seatingcapacity
                          FROM {hallschedule} 
                         WHERE hallid = $hallid AND startdate = $da AND starttime = '{$getstarttime}' AND endtime = '{$getendtime}' ";
                $getdatessql = $DB->get_fieldset_sql($sql);
                $scheduledseats = array_sum($getdatessql);
                $totalseats = $scheduledseats+$data['seatingcapacity'];
                if ($totalseats > $hall->seatingcapacity && $data['id']==0) {
                    $remainingseats = $hall->seatingcapacity-$scheduledseats;
                    $errors['seatingcapacity'] = get_string('noseatsavailable', 'local_hall', ['seats'=>$hall->seatingcapacity, 'bookedseats' => $scheduledseats, 'remainingseats'=>$remainingseats]);
                }
                $checkenrolledschedules = $DB->count_records('exam_enrollments',array('hallscheduleid'=>$id));
                if($checkenrolledschedules>$seatingcapacity && $data['id']!=0) {
                    $errors['seatingcapacity'] = get_string('enroledseats', 'local_hall', ['enrolseats'=>$checkenrolledschedules]);
                }               


                return $errors;
            }
        } else {
            $errors['hallid'] = get_string('selecthall', 'local_hall');
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
        //require_capability('moodle/site:config', 
    }
    

    /**
     * Process the form submission, used if form was submitted via AJAX
     */
    public function process_dynamic_submission() {
        global $CFG, $DB;        
        $data = $this->get_data();
        $result = (new local_hall\hall)->add_update_hall_schedule($data);

        return $result;
    }

    /**
     * Load in existing data as form defaults
     */
    public function set_data_for_dynamic_submission(): void {
        global $DB;
       if ($id = $this->optional_param('id', 0, PARAM_INT)) {
            $schedulehalldata = (new local_hall\hall)->set_schedulehall($id);
            $this->set_data($schedulehalldata);
        }
    }

    /**
     * Returns url to set in $PAGE->set_url() when form is being rendered or submitted via AJAX
     *
     * @return moodle_url
     */
    protected function get_page_url_for_dynamic_submission(): moodle_url {
        $id = $this->optional_param('id', 0, PARAM_INT);
        return new moodle_url('/local/hall/schedulehall.php',
            ['action' => 'createhall', 'id' => $id]);
    }    
}
