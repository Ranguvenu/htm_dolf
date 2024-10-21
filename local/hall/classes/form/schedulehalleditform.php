<?php
namespace local_hall\form;

use core_form\dynamic_form;
use moodle_url;
use context;
use context_system;
use local_hall;

class schedulehalleditform extends dynamic_form { 

    public function definition() {
        global $USER, $CFG,$DB;
        $systemcontext = context_system::instance();
        $corecomponent = new \core_component();
        $mform = $this->_form;
        $systemcontext = context_system::instance();
        $mform->addElement('hidden', 'id', '');
        $mform->setType('id',PARAM_INT);
        $hallid = $this->optional_param('hallid', 0, PARAM_INT);
        $mform->addElement('hidden', 'hallid',$hallid);
        $mform->setType('int', PARAM_INT);

         $startdate=array(
                        'startyear' => 1950, 
                        'stopyear'  => date('Y',time()),
                        'optional'  => true
                    );
        $mform->addElement('hidden', 'startdate',get_string('startdate', 'local_hall') ,array('disabled') ,'client');

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

        
    }
    public function validation($data, $files) {
        $errors = array();
        global $DB, $CFG;
       
        $hallid = $data['hallid'];
        $hallstartdate = $data['startdate'];
        $hallenddate = $data['enddate'];
        $id = $data['id'];
        $startdate = $data['startdate'];

        $hours = $data['starthour']*3600;
        $minutes = $data['startminute']*60;
        $getstarttime = $hours+$minutes;
        
        
        $hours = $data['endhour']*3600;
        $minutes = $data['endminute']*60;
        $getendtime = $hours+$minutes;

        $endtime = usertime($getendtime);
        $starttime = usertime($getstarttime);

        $sql = "SELECT *
                  FROM {hallschedule} 
                 WHERE (startdate = '{$hallstartdate}' AND id != $id AND hallid=$hallid AND ($starttime = starttime OR $endtime = endtime OR starttime = $endtime OR endtime = $starttime)) OR (startdate = '{$hallstartdate}' AND id != $id AND hallid=$hallid AND ($starttime > starttime AND $starttime < endtime)) OR (startdate = '{$hallstartdate}' AND id != $id AND hallid=$hallid AND ($endtime < endtime AND $endtime > starttime))";
        $getdatessql=$DB->get_records_sql($sql);

        
        if($getdatessql)
        {
            $errors['timeto'] = get_string('slotnotavailable', 'local_hall');
        }
        if($starttime == $endtime)
        {
            $errors['timeto'] = get_string('notequal', 'local_hall');
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
        (new local_hall\hall)->update_hall_schedule($data);
       
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
            ['action' => 'edithall', 'id' => $id]);
    }    
}
