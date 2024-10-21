<?php
namespace local_userapproval\form;
use core_form\dynamic_form ;
use moodle_url;
use context;
use context_system;
use html_writer;
use local_userapproval\action\manageuser as manageuser;
class user_wallet_form extends  dynamic_form{
    public function definition() {
      global $CFG,$DB;
      $mform = $this->_form; // Don't forget the underscore!

      $mform->addElement('text', 'wallet', get_string('wallet', 'local_userapproval'),array('class' => 'dynamic_form_id_wallet'));
      $mform->setType('wallet', PARAM_NOTAGS); 
      $mform->setDefault('text','');
      $mform->addRule('wallet', null, 'required', null, 'client');
      $mform->addRule('wallet', get_string('possitivepointsonly','local_userapproval'), 'regex', '#^([1-9][0-9]*)$#', 'client');
                    
    }                                                             
    public function validation($data, $files) {
        global $DB;
        $errors = parent::validation($data, $files);

        if ((!empty(trim($data['wallet']))) && !preg_match('/^[1-9][0-9]*$/',trim($data['wallet']))) {
            $errors['wallet'] = get_string('possitivepointsonly', 'local_userapproval');
         }
 

        return $errors;
    }
    protected function get_context_for_dynamic_submission(): context {
        return \context_system::instance();
    }
    protected function check_access_for_dynamic_submission(): void {
        require_capability('local/organization:manage_organizationofficial', $this->get_context_for_dynamic_submission());   

    }
    public function process_dynamic_submission() {
        global $CFG, $DB,$USER;
        $context= context_system::instance();
        $data = $this->get_data();
        if ($data){
                
              $customdata = (new manageuser)->add_user_wallet_data($data);
   
        }
        return json_encode(['returnurl' => $customdata]);
    }
    public function set_data_for_dynamic_submission(): void {
        global $DB;
    
        $this->set_data(array());
    
    }    
    protected function get_page_url_for_dynamic_submission(): moodle_url {
        $id = $this->optional_param('id', 0, PARAM_INT);
        return new moodle_url('/local/local_userapproval/index.php');
    }
}

