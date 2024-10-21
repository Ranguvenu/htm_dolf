<?php
namespace local_questionbank\form;
require_once($CFG->dirroot.'/local/questionbank/lib.php');
use core_form\dynamic_form;
use moodle_url;
use context;
use context_system;
use local_questionbank;

class expertsform extends dynamic_form { 
    public function definition() {
        global $USER, $CFG,$DB;
        $corecomponent = new \core_component();
        $questionbankid = $this->optional_param('questionbankid', 0, PARAM_INT);
        $mform = $this->_form;
        $id = $this->optional_param('id', 0, PARAM_INT);
 
        $mform->addElement('hidden', 'questionbankid', $questionbankid);
        $mform->setType('questionbankid',PARAM_INT);
        $allowed_questions = $DB->get_field('local_questionbank', 'noofquestions', ['id' => $questionbankid]);
        
        $mform->addElement('hidden', 'questionallowed', $allowed_questions, ['id' => 'questionallowed']);
        $options = array(
           // 'ajax' => 'local_questionbank/form-options-selector',
            'data-type' => 'expertlist',
            'id' => 'el_expert',
            'data-action' => 'expertselection',
            'data-expertid' => '',
            'class' => 'expertid',
            'multiple' => true,
            'placeholder' => get_string('selectexperts', 'local_questionbank')
        );

        $currentlang= current_language();
        $fullname = (new \local_trainingprogram\local\trainingprogram())->user_fullname_case();

        $reservations = qb_hall_reservations($questionbankid, 'questionbank');
        $seats = $reservations['seats'] ? $reservations['seats'] : 0;

        $mform->addElement('hidden', 'availableseats', $seats, '', ['id' => 'availableseats']);
        // Get Experts for the QB.
        if ($reservations['seats'] > 0) {
            $experts = $DB->get_records_sql("SELECT u.id, ex.noofquestions, $fullname FROM {user} u JOIN {local_users} lc ON lc.userid = u.id JOIN {local_qb_experts} ex ON ex.expertid = u.id WHERE ex.questionbankid = $questionbankid ");
            if ($experts) {
                foreach ($experts as $expert) {
                    $ids[] = $expert->id;
                }
            }
            $where = '';
            if (count($ids) > 0) {
                $uids = implode(',', $ids);
                $where = " AND u.id NOT IN($uids)";
            }
            if ($reservations['seats'] > count($experts)) {
                $expertlists = $DB->get_records_sql_menu("SELECT ra.userid as id, $fullname
                    FROM {role_assignments} as  ra 
                    JOIN {role} as r ON ra.roleid = r.id  AND r.shortname = 'expert'
                    JOIN {user} as u ON u.id= ra.userid
                    JOIN {local_users} AS lc ON lc.userid = u.id
                    WHERE 1=1 $where ");
                
                $mform->addElement('autocomplete','expertid', get_string('expert', 'local_questionbank'), $expertlists, $options);
                $mform->addRule('expertid',  get_string('missingexpert', 'local_questionbank'), 'required', null, 'server');
                $mform->setType('expertid', PARAM_RAW);
            }else{
                $mform->addElement('html', '<div class="alert alert-info">'.get_string('seatsnotavailable', 'local_questionbank').'</div>');
            }
        }
        
    }
    public function validation($data, $files) {
        global $DB, $CFG;
        $errors = array();
        $questionbank = $DB->get_record_sql("SELECT * FROM {local_questionbank} WHERE id = ".$data['questionbankid']);
        $availableseats = $DB->get_field_sql("SELECT SUM(seats) FROM {hall_reservations} WHERE typeid=$questionbank->id AND type ='questionbank' AND hallid=$questionbank->halladdress");
        if(empty($availableseats)){
            $availableseats = 0;
        }
        if($availableseats == 0){
             $errors['expertid'] =  get_string('seatscountempty', 'local_questionbank');
        }elseif(count($data['expertid']) > $availableseats){
            $errors['expertid'] =  get_string('seatscount', 'local_questionbank');
        }
        if(empty($data['expertid'])){
           $errors['expertid'] =  get_string('missingexpert', 'local_questionbank');
        }
        if (!empty($data['noofquestions'])) {
            if (!is_numeric($data['noofquestions'])) {
               $errors['noofquestions'] = get_string('onlynumeric', 'local_questionbank');
            }
        }
        $totalcountallowed = 0;
        for($i=0; $i< count($data['expertid']); $i++){
            if (!empty($data['noofquestionsfor_'.$data['expertid'][$i]])) {
                $totalcountallowed += $data['noofquestionsfor_'.$data['expertid'][$i]];
            }
            elseif ($data['noofquestionsfor_'.$data['expertid'][$i]] === 0) {
                $errors['noofquestionsfor_'.$data['expertid'][$i]] = get_string('invalidvalue', 'local_questionbank');
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
        require_capability('local/questionbank:assignreviewer', $this->get_context_for_dynamic_submission());
    }

    /**
     * Process the form submission, used if form was submitted via AJAX
     */
    public function process_dynamic_submission() {
        global $CFG, $DB;
        require_once($CFG->dirroot.'/user/profile/definelib.php');
        $data = $this->get_data();
       (new \questionbank)->create_qb_experts($data);
    }

    /**
     * Load in existing data as form defaults
     */
    public function set_data_for_dynamic_submission(): void {
        global $DB;
        if ($id = $this->optional_param('questionbankid', 0, PARAM_INT)) {
            $data1 = $DB->get_fieldset_sql('SELECT expertid FROM {local_qb_experts} WHERE questionbankid = '.$id);
            $noofquestions = $DB->get_field_sql('SELECT noofquestions FROM {local_qb_experts} WHERE questionbankid = '.$id);
            if($data1){
             $experts = implode(',',$data1);
             $currentlang= current_language();
             $fullname = (new \local_trainingprogram\local\trainingprogram())->user_fullname_case();
             $userinfo = $DB->get_records_sql_menu("SELECT u.id, $fullname FROM {user} as u 
                JOIN {local_users} AS lc ON lc.userid = u.id WHERE u.id IN ($experts)");

             $data->expertid = $experts;
             $data->noofquestions = $noofquestions;
              $this->set_data($data);
            }
        }
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
