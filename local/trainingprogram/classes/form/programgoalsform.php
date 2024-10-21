<?php
namespace local_trainingprogram\form;

    
use core_form\dynamic_form ;
use moodle_url;
use context;
use context_system;
use html_writer;
use local_trainingprogram\local\trainingprogram as tp;
class programgoalsform extends  dynamic_form{
    //Add elements to form
    public function definition() {
        global $CFG,$DB; 

          $mform = $this->_form; // Don't forget the underscore!
          $id = $this->optional_param('id', 0, PARAM_INT);
          $programid = $this->optional_param('programid', 0, PARAM_INT);
         
          $mform->addElement('hidden', 'id',$id);
          $mform->setType('int', PARAM_INT);
        
          $mform->addElement('hidden', 'programid',$programid);
          $mform->setType('int', PARAM_INT);

          $mform->addElement('textarea', 'programgoal', get_string("programgoal", 'local_trainingprogram'), 'wrap="virtual" rows="20" cols="50"');
          $mform->addRule('programgoal', get_string('programgoal', 'local_trainingprogram'), 'required');
          $mform->setType('programgoal', PARAM_RAW); 


          $mform->addElement('textarea', 'programgoalarabic', get_string("programgoalarabic", 'local_trainingprogram'), 'wrap="virtual" rows="20" cols="50"');
          $mform->addRule('programgoalarabic', get_string('programgoalarabic', 'local_trainingprogram'), 'required');
          $mform->setType('programgoalarabic', PARAM_RAW); 
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
        has_capability('local/trainingprogram:manage', $this->get_context_for_dynamic_submission()) 
        || has_capability('local/organization:manage_trainingofficial', $this->get_context_for_dynamic_submission());   
    }

    public function process_dynamic_submission() {
        global $CFG, $DB,$USER;
        $data = $this->get_data();
        (new tp)->createandupdateprogramgoals($data);
    }
    public function set_data_for_dynamic_submission(): void {
        global $DB;
        if ($id = $this->optional_param('id', 0, PARAM_INT)) {
           $data = $DB->get_record('program_goals', ['id' => $id], '*', MUST_EXIST);
           $str = $data->programgoal;
           // Setting name for enlish field
           // preg_match('/{mlang ar}(.?)\s{mlang}/', $str, $match);
            preg_match('/{mlang en}(.*?){mlang}/', $str, $match);

           $englishname =  $match[1];
           // Setting name for arabic field
           // preg_match('/{mlang ar}(.?)\s{mlang}/', $str, $match);
             preg_match('/{mlang ar}(.*?){mlang}/', $str, $match);
           
           $arabicname =  $match[1];
           $data->programgoal = $englishname;
           $data->programgoalarabic = $arabicname;

           $this->set_data($data);
       }
  }
    protected function get_page_url_for_dynamic_submission(): moodle_url {
        return new moodle_url('/local/trainingprogram/index.php');
    }

}
