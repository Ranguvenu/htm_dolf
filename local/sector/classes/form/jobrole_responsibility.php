<?php
namespace local_sector\form;

    
use core_form\dynamic_form ;
use moodle_url;
use context;
use context_system;
use html_writer;
use local_sector\controller as sector;
class jobrole_responsibility extends  dynamic_form{
    //Add elements to form
    public function definition() {
        global $CFG; 

          $mform = $this->_form; // Don't forget the underscore!
          $roleid = required_param('jobfamilyid', PARAM_INT);
        
          $id = $this->_customdata['id'];

        
      

          $mform->addElement('hidden', 'id');
          $mform->setType('int', PARAM_INT);
          $mform->setDefault('id', $id);

          $mform->addElement('hidden', 'jobfamilyid');
          $mform->setType('int', PARAM_INT);
          $mform->setDefault('id', $roleid);

          $mform->addElement('editor', 'responsibility', get_string('responsibility', 'local_sector')); // Add elements to your form.
          $mform->addRule('responsibility', get_string('responsibilityerror', 'local_sector'), 'required');
          $mform->setType('responsibility', PARAM_RAW);  

        //   $mform->addElement('text', 'code', get_string('code', 'local_sector')); // Add elements to your form.
        //   $mform->addRule('code', get_string('course'), 'required', null);
        //   $mform->setType('text', PARAM_NOTAGS);  

         
        //   $mform->addElement('editor', 'description', get_string('description', 'local_sector')); // Add elements to your form.
        //   $mform->addRule('description', get_string('description', 'local_sector'), 'required', null);
        //   $mform->setType('description', PARAM_TEXT); 

          $mform->addElement('hidden', 'status');
          $mform->setType('int', PARAM_INT);  
                         // Set type of element.
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

    public function process_dynamic_submission() {
        $data = $this->get_data();
        if($data){
            if($data->id >0){
                $sectordata = (new sector)->update_jobfamily($data);
            }else{
                $sectordata = (new sector)->create_jobresp($data);
            }
        }
    }
 public function set_data_for_dynamic_submission(): void {
        global $DB;
    //     if ($id = $this->optional_param('id', 0, PARAM_INT)) {
    //         $data = $DB->get_record('sector', ['id' => $id], '*', MUST_EXIST);
    //         $this->set_data(['id'=>$data->id,'title' => $data->title, 'code' => $data->code ,'description'=>$data->description]);
    //     }
    }
      /**
     * Returns url to set in $PAGE->set_url() when form is being rendered or submitted via AJAX
     *
     * @return moodle_url
     */
    protected function get_page_url_for_dynamic_submission(): moodle_url {
        $id = $this->optional_param('id', 0, PARAM_INT);
        return new moodle_url('/local/sector/index.php',
            ['action' => 'addjobrole', 'id' => $id]);
    }

}

