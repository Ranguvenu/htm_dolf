<?php
namespace local_exams\form;

use core_form\dynamic_form;
use moodle_url;
use context;
use context_system;
use local_exams;
use Exception;
use local_trainingprogram\local\trainingprogram as tp;

class examenrolform extends dynamic_form { 
    public function definition() {
        global $USER, $CFG,$DB;
        $corecomponent = new \core_component();

        $mform = $this->_form;
        $id = $this->optional_param('id', 0, PARAM_INT);
        $validation = $this->optional_param('validation', 0, PARAM_INT);
        $transactiontypes = $this->optional_param('transactiontypes', 0, PARAM_INT);

        $systemcontext = context_system::instance();
    
        $mform->addElement('hidden', 'id', $id);
        $mform->setType('id',PARAM_INT);

        $mform->addElement('hidden', 'validation', $validation);
        $mform->setType('id',PARAM_INT);

        $mform->addElement('hidden', 'transactiontypes', $transactiontypes);
        $mform->setType('transactiontypes',PARAM_INT);

        $mform->addElement('hidden', 'payementtypes');
        $mform->setType('payementtypes',PARAM_INT);
        

        $mform->addElement('text','username', get_string('username', 'local_exams'));
        // $mform->addRule('username', get_string('required','local_exams'), 'required', null, 'server');
        $mform->setType('username', PARAM_TEXT);

        $mform->addElement('text','centercode', get_string('centercode', 'local_exams'));
        // $mform->addRule('centercode', get_string('required','local_exams'), 'required', null, 'server');
        $mform->setType('centercode', PARAM_TEXT);

        $mform->addElement('text','examcode', get_string('examcode', 'local_exams'));
        // $mform->addRule('examcode', get_string('required','local_exams'), 'required', null, 'server');
        $mform->setType('examcode', PARAM_TEXT);

        $mform->addElement('text','profilecode', get_string('profilecode', 'local_exams'));
        // $mform->addRule('profilecode', get_string('required','local_exams'), 'required', null, 'server');
        $mform->setType('profilecode', PARAM_TEXT);

        $mform->addElement('text','examlanguage', get_string('examlanguage', 'local_exams'));
        // $mform->addRule('examlanguage', get_string('required','local_exams'), 'required', null, 'server');
        $mform->setType('examlanguage', PARAM_TEXT);

        $mform->addElement('text','examdatetime', get_string('examdatetime', 'local_exams'));
        // $mform->addRule('examdatetime', get_string('required','local_exams'), 'required', null, 'server');
        $mform->setType('examdatetime', PARAM_TEXT);

        $mform->addElement('text','purchasedatetime', get_string('purchasedatetime', 'local_exams'));
        // $mform->addRule('purchasedatetime', get_string('required','local_exams'), 'required', null, 'server');
        $mform->setType('purchasedatetime', PARAM_TEXT);

        $mform->addElement('text','createdbyusername', get_string('createdbyusername', 'local_exams'));
        // $mform->addRule('createdbyusername', get_string('required','local_exams'), 'required', null, 'server');
        $mform->setType('createdbyusername', PARAM_TEXT);

        $mform->addElement('text','billnumber', get_string('billnumber', 'local_exams'));
        // $mform->addRule('billnumber', get_string('required','local_exams'), 'required', null, 'server');
        $mform->setType('billnumber', PARAM_TEXT);

        $mform->addElement('text','paymentrefid', get_string('paymentrefid', 'local_exams'));
        // $mform->addRule('paymentrefid', get_string('required','local_exams'), 'required', null, 'server');
        $mform->setType('paymentrefid', PARAM_TEXT);


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
        
    }

    /**
     * Process the form submission, used if form was submitted via AJAX
     */
    public function process_dynamic_submission() {
        global $CFG, $DB;
        $data = (array)$this->get_data();

        $systemcontext = context_system::instance();
        
        
      
        $get_data = $DB->get_record_sql('SELECT * FROM {local_fast_examenrol}  
                                                     WHERE  id = '.$data["id"].'');
        $fastapi = new \local_exams\local\fast_service($data);
       if($get_data->transactiontypes==1 && $get_data->validation==0)
       {
            $response = $fastapi->create_exam_reservations();
            // enroll user to exam if data resend from fast service
            if(empty($response) || COUNT($response->messages) <=0  || $response->success){
                $profileid = $DB->get_field('local_exam_profiles', 'id', ['profilecode'=>$get_data->profilecode]);
                $userid = $DB->get_field('user', 'id', ['username'=>$get_data->username]);
                $dt = strtotime($get_data->examdatetime);
                $date = date('m/d/Y', $dt);
                $examdate = strtotime($date);
                $hallschid = $DB->get_field_sql("SELECT hs.id FROM {hall} h 
                    JOIN {hallschedule} hs ON hs.hallid = h.id 
                    WHERE h.code = '$get_data->centercode' AND hs.startdate = $examdate");
                if($get_data->reservationid > 0){
                    $hallscheduleid = $get_data->reservationid;
                }else{
                    $hallscheduleid = $hallschid;
                }
                $examenrol = (new local_exams\local\exams)->exam_enrollmet($profileid, $userid, $hallscheduleid, null, $get_data->usercreated, null, null,null,null);
            }
       }elseif($get_data->transactiontypes==1 && $get_data->validation==1)
       {
             $fastapi->hall_availability();
       }
       elseif($get_data->transactiontypes==2 && $get_data->validation==0)
       {
             $fastapi->call_cancel();
       }
       elseif($get_data->transactiontypes==2 && $get_data->validation==1)
       {
             $fastapi->validate_cancel();
       }
       elseif($get_data->transactiontypes==3 && $get_data->validation==0)
       {
             $fastapi->call_reschedule();
       }
       elseif($get_data->transactiontypes==3 && $get_data->validation==1)
       {
             $fastapi->validate_reschedule();
       }
       elseif($get_data->transactiontypes==4 && $get_data->validation==0)
       {
             $fastapi->call_replacment();
       }
       elseif($get_data->transactiontypes==4 && $get_data->validation==1)
       {
             $fastapi->validate_replacment();
       }

       
    }

    /**
     * Load in existing data as form defaults
     */
    public function set_data_for_dynamic_submission(): void {
        global $DB;
        $id = $this->optional_param('id', 0, PARAM_INT);
        if ($id) {
            $examenrol = (new local_exams\local\exams)->set_examenrol($id);
            $this->set_data($examenrol);
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
