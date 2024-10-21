<?php
namespace local_trainingprogram\form;

use core_form\dynamic_form ;
use moodle_url;
use context;
use context_system;
use local_trainingprogram\local\trainingprogram as tp;

class programagendaform extends dynamic_form { 
    public function definition() {
        global $USER, $CFG,$DB, $OUTPUT;
        $corecomponent = new \core_component();
        $programid = $this->optional_param('programid', 0, PARAM_INT);
        $programname = $this->optional_param('programname', 0, PARAM_RAW);

        $mform = $this->_form;

        $mform->addElement('hidden', 'programid', $programid);
        $mform->setType('programid',PARAM_INT);
        $programrecord =$DB->get_record('local_trainingprogram',array('id'=>$programid));

        $days =$programrecord->duration / 86400;

        $mform->addElement('hidden', 'days', $days);
        $mform->setType('days',PARAM_INT);
    
        for ($i = 1; $i <= $days; $i++) {
            $day_lable =get_string('day','core').$i;
            $mform->addElement('editor', 'description'.$i,$day_lable,array('class' => 'program_agenda_form_inputs'));
            $mform->setType('description', PARAM_RAW);
        }

       
    }
    public function validation($data, $files) {
        global $DB, $CFG;
        $errors = array();
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
         has_capability('local/trainingprogram:manage', $this->get_context_for_dynamic_submission()) 
        || has_capability('local/organization:manage_trainingofficial', $this->get_context_for_dynamic_submission());
    }

    /**
     * Process the form submission, used if form was submitted via AJAX
     */
    public function process_dynamic_submission() {
        global $CFG, $DB;
        require_once($CFG->dirroot.'/user/profile/definelib.php');
        $data = $this->get_data();
        (new tp)->add_update_program_agenda($data);

    }

    /**
     * Load in existing data as form defaults
     */
    public function set_data_for_dynamic_submission(): void {
        global $DB;

        if ($programid = $this->optional_param('programid', 0, PARAM_INT)) {
            $data = $DB->get_records('program_agenda', ['programid' => $programid]);
            $formdata = new \stdClass();
            foreach ($data AS $singledata) {
               $formdata->{"description$singledata->day"} = ['text' => $singledata->description];
            }
            $this->set_data($formdata);
        }
        
    }

    /**
     * Returns url to set in $PAGE->set_url() when form is being rendered or submitted via AJAX
     *
     * @return moodle_url
     */
    protected function get_page_url_for_dynamic_submission(): moodle_url {
        return new moodle_url('/local/trainingprogram/index.php');
    }    
}
