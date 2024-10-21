<?php
 namespace local_userapproval\form;
   use core_form\dynamic_form ;
   use moodle_url;
   use context;
   use context_system;
   use Exception;
   use moodle_exception;
   use local_userapproval\action\manageuser as manageuser;

class requestedit_form extends  dynamic_form{
    public function definition() {
      global $CFG,$DB,$PAGE,$OUTPUT,$USER;
      $mform = $this->_form; // Don't forget the underscore!

        $context = context_system::instance();
        $id = $this->optional_param('id', 0, PARAM_INT);
        $requesttype = $this->optional_param('requesttype', 0, PARAM_RAW);
        $name = $this->optional_param('name', 0, PARAM_RAW);
        $userid= $this->optional_param('userid', 0, PARAM_INT);
         
        $user_id =$DB->get_record('local_users',array('id'=>$userid));

        $mform->addElement('hidden', 'requesttype', $requesttype);
        $mform->setDefault('requesttype', $requesttype); 
        $mform->addElement('hidden', 'id', $id);
        $mform->setDefault('id', $id);

        $mform->addElement('filepicker', 'qualification', get_string('qualification','local_userapproval'), null,
              array('maxbytes' => $maxbytes, 'accepted_types' => '*'));
        $mform->addRule('qualification', get_string('file'), 'required');
        $mform->setType('qualification', PARAM_INT);

        $mform->addElement('text', 'certificates',  get_string('certificates', 'local_userapproval'));                    
        $mform->addRule('certificates', get_string('certificates','local_userapproval'), 'required');
        $mform->addRule('certificates', get_string('certificates_numeric','local_userapproval'), 'numeric');
        $mform->setType('certificates', PARAM_INT);

        $yearsofexperience = array(null=>get_string('select_yearsofexperience','local_userapproval'),'5'=>'5','10'=>'10','15'=>'15','20'=>'20','25'=>'25');
        $mform->addElement('select', 'yearsofexperience', get_string('yearsofexperience','local_userapproval'),$yearsofexperience);
        $mform->addRule('yearsofexperience', get_string('erroryearsofexperience','local_userapproval'), 'required');
        $mform->setType('yearsofexperience', PARAM_INT);
        
 
       if ($requesttype=='Expert'){
          
            $expertrequestdetail=$DB->get_record('local_expert_request',array('id'=>$id,'userid'=>$user_id->userid));
              
            $mform->addElement('text', 'fieldofexperience', get_string('fieldofexperience','local_userapproval'),/*$fieldoftraining*/);
            $mform->setType('fieldofexperience', PARAM_INT); 
            
        }
        else{

            $mform->addElement('text', 'fieldoftraining', get_string('fieldoftraining','local_userapproval'),/*$fieldoftraining*/);
        $mform->setType('fieldoftraining', PARAM_INT);  
        }

    }       

    public function validation($data, $files) {
        global $DB;
        
        return $errors;
    }
    protected function get_context_for_dynamic_submission(): context {
        return \context_system::instance();
    }
    protected function check_access_for_dynamic_submission(): void {
     
    }
    public function process_dynamic_submission() {
        global $CFG, $DB,$USER;
        $context= context_system::instance();
        $data = $this->get_data();
        
        
        if ($data->requesttype=='Trainer'){
           
            $trainer =$DB->get_record('local_trainer_request',array('id'=>$data->id));
            // $trainer->qualifications= $trainer->id;
            $trainer->qualifications= $data->qualification;
            $trainer->yearsofexperience=$data->yearsofexperience;
            $trainer->fieldoftraining=$data->fieldoftraining?$data->fieldoftraining:0;
            $trainer->certificates=$data->certificates;
            $this->save_stored_file('qualification', $context->id, 'local_userapproval', 'qualification',  $data->qualification, '/', null, true);
            $DB->update_record('local_trainer_request',$trainer);
   
        }
         
        if ($data->requesttype=='Expert'){
           
            $expert =$DB->get_record('local_expert_request',array('id'=>$data->id));
            // $expert->qualifications= $trainer->id;
            $expert->qualifications= $data->qualification;
            $expert->yearsofexperience=$data->yearsofexperience;
            $expert->fieldofexperience=$data->fieldofexperience;
            $expert->certificates=$data->certificates;
            
            $this->save_stored_file('qualification', $context->id, 'local_userapproval', 'qualification',  $data->qualification, '/', null, true);
            $DB->update_record('local_expert_request',$expert);
        }


    }
    public function set_data_for_dynamic_submission(): void {
        global $DB;

         $context = context_system::instance();
         $id = $this->optional_param('id', 0, PARAM_INT);
         $requesttype = $this->optional_param('requesttype', 0, PARAM_RAW);
         $name = $this->optional_param('name', 0, PARAM_RAW);
         $userid= $this->optional_param('userid', 0, PARAM_INT);
         $user_id =$DB->get_record('local_users',array('id'=>$userid));

        if ($requesttype=='Trainer'){
           
          $trainerrequestdetail =$DB->get_record('local_trainer_request',array('id'=>$id,'userid'=>$user_id->userid));
          
          $draftitemid = file_get_submitted_draft_itemid($trainerrequestdetail->qualifications);
            file_prepare_draft_area($draftitemid, $context->id, 'local_userapproval', 'qualification',$trainerrequestdetail->qualifications, null);
            
            
            $data->qualification= $draftitemid;
            $data->certificates=$trainerrequestdetail->certificates; 
            $data->yearsofexperience=$trainerrequestdetail->yearsofexperience;
            $data->fieldoftraining=$trainerrequestdetail->fieldoftraining?$trainerrequestdetail->fieldoftraining:0;
        }

        if ($requesttype=='Expert'){
              $expertrequestdetail=$DB->get_record('local_expert_request',array('id'=>$id,'userid'=>$user_id->userid));
              $draftitemid = file_get_submitted_draft_itemid($expertrequestdetail->qualifications);
              file_prepare_draft_area($draftitemid, $context->id, 'local_userapproval', 'qualification',$expertrequestdetail->qualifications, null);
              $data->qualification= $draftitemid;
              $data->certificates=$expertrequestdetail->certificates;
              $data->yearsofexperience=$expertrequestdetail->yearsofexperience;
              $data->fieldofexperience=$expertrequestdetail->fieldofexperience;
              
        }
   
      $this->set_data($data);
    
    }    
    protected function get_page_url_for_dynamic_submission(): moodle_url {
        $id = $this->optional_param('id', 0, PARAM_INT);
        return new moodle_url('/local/local_userapproval/index.php');
    }
}

