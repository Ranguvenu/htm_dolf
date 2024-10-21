<?php
namespace local_exams\form;

use core_form\dynamic_form;
use moodle_url;
use context;
use context_system;
use local_exams;
use \stdClass;

class updateuser extends  dynamic_form{
    //Add elements to form
    public function definition() {
        global $CFG,$DB;
        $mform = $this->_form; 
        
        $examid = $this->optional_param('examid', 0, PARAM_INT);
        $userid = $this->optional_param('userid', 0, PARAM_INT);
        
       
        $userdata=$DB->get_record('local_users',array('userid'=>$userid));

        $org=$DB->get_record('local_organization',array('id'=>$userdata->organization));


        $userdetail=$DB->get_record('local_users',array('userid'=>$userid));
         $fullname=$userdetail->firstname .''. $userdetail->lastname;

        $mform->addElement('static', 'name' ,get_string('name','local_exams'));
        $mform->setDefault('name', $fullname);

        $mform->addElement('static', 'email' ,get_string('email','local_exams'));
        $mform->setDefault('email', $userdetail->email);

        $mform->addElement('static', 'id' ,get_string('id','local_exams'));
        $mform->setDefault('id', $userdetail->id_number);

        $mform->addElement('static', 'currentorg' ,get_string('currentorg','local_exams'));
        $mform->setDefault('currentorg', $org->fullname);

        $get_org=$DB->get_records_sql("SELECT * FROM {local_organization}");

        $allorg=array();

        foreach($get_org as $org){          
            $id=$org->id;
            $value = $org->fullname;
            $allorg[$id] = $value;
            
        }

        $select = $mform->addElement('autocomplete', 'organization', get_string('organization','local_exams'),$allorg);
         

        $mform->addElement('hidden', 'userid', $userid);
        $mform->setType('userid',PARAM_INT);

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
    }

    public function process_dynamic_submission() {

        global $DB;
        $data = $this->get_data();

        $userid=$DB->get_record('local_users',array('userid'=>$data->userid));
      
      
        $userid->organization=$data->organization;
       
        $updateuserid=$DB->update_record('local_users',$userid);   

    }


    public function set_data_for_dynamic_submission(): void {
        global $DB;
   
       
        $userid = $this->optional_param('userid', 0, PARAM_INT);

        $userdata=$DB->get_record('local_users',array('userid'=>$userid));
     

        $getorg=$DB->get_record('local_organization',array('id'=>$userdata->organization));

        $data->organization=$getorg->id;

        $this->set_data($data);
      
    }
      /**
     * Returns url to set in $PAGE->set_url() when form is being rendered or submitted via AJAX
     *
     * @return moodle_url
     */
    protected function get_page_url_for_dynamic_submission(): moodle_url {
        $id = $this->optional_param('id', 0, PARAM_INT);
        return new moodle_url('/local/exams/index.php',
            ['action' => 'editprofilecode', 'id' => $id]);
    }

}

