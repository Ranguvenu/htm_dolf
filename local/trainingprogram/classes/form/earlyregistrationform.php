<?php
// This file is part of Moodle - http://moodle.org/
//
// Moodle is free software: you can redistribute it and/or modify
// it under the terms of the GNU General Public License as published by
// the Free Software Foundation, either version 3 of the License, or
// (at your option) any later version.
//
// Moodle is distributed in the hope that it will be useful,
// but WITHOUT ANY WARRANTY; without even the implied warranty of
// MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
// GNU General Public License for more details.
//
// You should have received a copy of the GNU General Public License
// along with Moodle.  If not, see <http://www.gnu.org/licenses/>.
namespace local_trainingprogram\form;
use core_form\dynamic_form ;
use moodle_url;
use context;
use context_system;
use local_trainingprogram\local\trainingprogram as tp;
use coding_exception;
use MoodleQuickForm_autocomplete;


/**
 * TODO describe file earlyregistrationform
 *
 * @package    local_trainingprogram
 * @copyright  2023 YOUR NAME <your@email.com>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class earlyregistrationform extends dynamic_form { 
    public function definition() {
        global $USER, $CFG,$DB;

        $corecomponent = new \core_component();
        $mform = $this->_form;
        $id = $this->optional_param('id', 0, PARAM_RAW);
        $systemcontext = context_system::instance();

        $mform->addElement('hidden', 'id', $id);
        $mform->setType('id',PARAM_INT);

        //programs
        $programs = $this->_ajaxformdata['programs'];
        $allprograms = array();
        if (!empty($programs)) {
            $programs = is_array($programs) ? $programs : array($programs);
            $allprograms = (new tp)::get_entitydetails($programs,0,'programs','earlyregistration');
        } elseif($id > 0) {
            $allprograms = (new tp)::get_entitydetails(array(),$id,'programs','earlyregistration');
        }
        $programsattributes = array(
            'ajax' => 'local_trainingprogram/dynamic_dropdown_ajaxdata',
            'data-type' => 'allentities',
            'id' => 'listof_allprograms',
            'data-ctype' => 'programs',
            'data-programid' => 0,
            'data-offeringid' => 0,
            'multiple'=>true,
            'noselectionstring' => get_string('all', 'local_trainingprogram'),
        );
 
        //exams
        $exams = $this->_ajaxformdata['exams'];
        $allexams = array();
        if (!empty($exams)) {
            $exams = is_array($exams) ? $exams : array($exams);
            $allexams = (new tp)::get_entitydetails($exams,0,'exams','earlyregistration');
        } elseif($id > 0) {
            $allexams = (new tp)::get_entitydetails(array(),$id,'exams','earlyregistration');
        }
        $examsattributes = array(
            'ajax' => 'local_trainingprogram/dynamic_dropdown_ajaxdata',
            'data-type' => 'allentities',
            'id' => 'listof_allexams',
            'data-ctype' => 'exams',
            'data-programid' => 0,
            'data-offeringid' => 0,
            'multiple'=>true,
            'noselectionstring' => get_string('all', 'local_trainingprogram'),

        );
 
        //events
        $events = $this->_ajaxformdata['events'];
        $allevents = array();
        if (!empty($events)) {
        $events = is_array($events) ? $events : array($events);
            $allevents = (new tp)::get_entitydetails($events,0,'events','earlyregistration');
        } elseif($id > 0) {
            $allevents = (new tp)::get_entitydetails(array(),$id,'events','earlyregistration');       
        }
   
        $eventsattributes = array(
            'ajax' => 'local_trainingprogram/dynamic_dropdown_ajaxdata',
            'data-type' => 'allentities',
            'id' => 'listof_allevents',
            'data-ctype' => 'events',
            'data-programid' => 0,
            'data-offeringid' => 0,
            'multiple'=>true,
            'noselectionstring' => get_string('all', 'local_trainingprogram'),

        );
        $mform->addElement('autocomplete', 'programs', get_string('pluginname', 'local_trainingprogram'),$allprograms, $programsattributes);
         
        $mform->addElement('autocomplete', 'exams', get_string('pluginname', 'local_exams'),$allexams, $examsattributes);
 
        $mform->addElement('autocomplete', 'events', get_string('pluginname', 'local_events'),$allevents, $eventsattributes);
 

        $mform->addElement('text', 'days', get_string('days','local_trainingprogram'));
        $mform->addRule('days',get_string('required_field','local_trainingprogram'), 'required');
        $mform->addRule('days',get_string('acceptsnumeric','local_trainingprogram'), 'numeric');
       
        $mform->addElement('date_selector', 'earlyregistration_expired_date', get_string('expired_date','local_trainingprogram'));
        $mform->setType('earlyregistration_expired_date', PARAM_TEXT);

        $mform->addElement('text', 'discount', get_string('discount','local_trainingprogram'));
        $mform->setType('discount', PARAM_INT);
        $mform->addRule('discount', get_string('discountcannotbeempty','local_trainingprogram'), 'required');
    }
    public function validation($data, $files) {
        $errors = array();
        global $DB;
        $days=trim($data['days']);
        $id = $data['id'];
        if ($days <= 0){
            $errors['days'] = get_string('invalid_field','local_trainingprogram');
        } 
        if(!empty($days)) {
            if (empty($id)) {
                if($DB->record_exists_sql(" SELECT * FROM {earlyregistration_management}  WHERE  days = $days")) {
                  $errors['days'] = get_string('daysexists','local_trainingprogram',$days);
                }
            } else {
                $daysexists= $DB->get_records_sql('SELECT * FROM {earlyregistration_management} WHERE days = :days AND id = :id', ['days' => $days, 'id' => $id]);
                if (count($daysexists) <= 0) {
                    if($DB->record_exists_sql(" SELECT * FROM {earlyregistration_management}  WHERE  days = $days")) {
                        $errors['days'] = get_string('daysexists','local_trainingprogram',$days);
                    }
                }
            }
        }
        if(date("Y-m-d",$data['earlyregistration_expired_date']) < date("Y-m-d") ) {
            $errors['earlyregistration_expired_date'] = get_string('exprired_date_error', 'local_trainingprogram');
        }
        if(empty($data['discount']) || $data['discount'] == 0) {
            $errors['discount'] = get_string('discountcannotbeempty', 'local_trainingprogram');
        }
        if(!empty(trim($data['discount'])) && $data['discount'] > 0  && $data['discount'] > 100) {
            $errors['discount'] = get_string('discountlimiterror', 'local_trainingprogram');
        }
        return $errors;
    }
    /**
     * Returns context where this form is used
     *
     * @return context
     */
    protected function get_context_for_dynamic_submission(): context {
        return context_system::instance();
    }

    /**
     * Checks if current user has access to this form, otherwise throws exception
     */
    protected function check_access_for_dynamic_submission(): void {
        //require_capability('moodle/site:config', $this->get_context_for_dynamic_submission());
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
        (new tp)->create_update_earlyregistration($data);
    }

    /**
     * Load in existing data as form defaults
     */
    public function set_data_for_dynamic_submission(): void {
              global $DB;

        if ($id = $this->optional_param('id', 0, PARAM_INT)) {
            $data=$DB->get_record('earlyregistration_management',array ('id' =>$id));
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
        return new moodle_url('/local/trainingprogram/discount_management.php');
    }    
}
