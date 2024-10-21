<?php
namespace local_exams\form;

use core_form\dynamic_form;
use moodle_url;
use context;
use context_system;
use local_exams;

class seatingform extends dynamic_form { 
    public function definition() {
        global $USER, $CFG,$DB;
        $corecomponent = new \core_component();

        $mform = $this->_form;
        $id = $this->_customdata['id'];
        $editoroptions = $this->_customdata['editoroptions'];
         
        $systemcontext = context_system::instance();
    
        $mform->addElement('hidden', 'id', '');
        $mform->setType('id',PARAM_INT);

        $mform->addElement('hidden', 'hallid', '');
        $mform->setType('hallid',PARAM_INT);

        $mform->addElement('hidden', 'examid', '');
        $mform->setType('examid',PARAM_INT);

        $mform->addElement('hidden', 'examdate', '');
        $mform->setType('examdate',PARAM_INT);

        $mform->addElement('hidden', 'slotstart', '');
        $mform->setType('slotstart',PARAM_INT);

        $mform->addElement('hidden', 'slotend', '');
        $mform->setType('slotend',PARAM_INT);

        $mform->addElement('text', 'seats', get_string('seats', 'local_exams'));
        $mform->setType('seats', PARAM_TEXT);
        $mform->addRule('seats', get_string('missingseats', 'local_exams'), 'required', null, 'client');
    }
    public function validation($data, $files) {
        global $DB, $CFG;
        $errors = array();
        if(!is_numeric($data['seats'])) {
            $errors['seats'] = get_string('numaricvalid','local_exams', $availableseats);
        }
        $totalseats = $DB->get_field('hall', 'seatingcapacity', ['id' => $data['hallid']]);
        $records = $DB->get_records_sql("SELECT * FROM {hall_reservations} WHERE hallid = {$data['hallid']} AND (slotstart = '{$data['slotstart']}' AND slotend = '{$data['slotend']}' AND examdate = {$data['examdate']}) OR ('{$data['slotstart']}' > slotstart AND '{$data['slotstart']}' < slotend AND examdate = {$data['examdate']}) OR ('{$data['slotend']}' > slotstart AND '{$data['slotend']}' < slotend AND examdate = {$data['examdate']}) ");
        foreach($records as $record) {
            $bookedseats[] = $record->seats;
        }
        $reservedseats = array_sum($bookedseats);

        $availableseats = $totalseats - $reservedseats;
        if($totalseats < $data['seats']) {
            $errors['seats'] = get_string('seatsrange','local_exams', $availableseats);
        } elseif($availableseats < $data['seats']) {
            $errors['seats'] = get_string('seatsrange','local_exams', $availableseats);
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
        require_capability('moodle/site:config', $this->get_context_for_dynamic_submission());
    }

    /**
     * Process the form submission, used if form was submitted via AJAX
     */
    public function process_dynamic_submission() {
        global $CFG, $DB;
        require_once($CFG->dirroot.'/user/profile/definelib.php');
        $data = $this->get_data();
        (new local_exams\local\exams)->add_update_seating($data);
    }

    /**
     * Load in existing data as form defaults
     */
    public function set_data_for_dynamic_submission(): void {
        global $DB;
        $seating['hallid'] = $this->optional_param('hallid', 0, PARAM_INT);
        $seating['examid'] = $this->optional_param('examid', 0, PARAM_INT);
        $seating['examdate'] = $this->optional_param('examdate', 0, PARAM_INT);
        $seating['slotstart'] = $this->optional_param('slotstart', 0, PARAM_INT);
        $seating['slotend'] = $this->optional_param('slotend', 0, PARAM_INT);
        $this->set_data($seating);
    }

    /**
     * Returns url to set in $PAGE->set_url() when form is being rendered or submitted via AJAX
     *
     * @return moodle_url
     */
    protected function get_page_url_for_dynamic_submission(): moodle_url {
        $id = $this->optional_param('id', 0, PARAM_INT);
        return new moodle_url('/local/exams/index.php',
            ['action' => 'editcategory', 'id' => $id]);
    }    
}
