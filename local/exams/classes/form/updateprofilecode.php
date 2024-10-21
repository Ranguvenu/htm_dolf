<?php
namespace local_exams\form;

use core_form\dynamic_form;
use moodle_url;
use context;
use context_system;
use local_exams;
use \stdClass;

class updateprofilecode extends  dynamic_form{
    //Add elements to form
    public function definition() {
        global $CFG,$DB;
        $mform = $this->_form; 
        
        $examid = $this->optional_param('examid', 0, PARAM_INT);
        $userid = $this->optional_param('userid', 0, PARAM_INT);
        $profileid = $this->optional_param('profileid', 0, PARAM_INT);

        $mform->addElement('hidden', 'examid', $examid);
        $mform->setType('examid',PARAM_INT);

        $mform->addElement('hidden', 'userid', $userid);
        $mform->setType('userid',PARAM_INT);
        
        $mform->addElement('hidden', 'profileid',$profileid);
        $mform->setType('profileid',PARAM_INT);


        $get_profilecode=$DB->get_records_sql_menu("SELECT id, profilecode FROM {local_exam_profiles} WHERE examid= $examid ");

        // $profile=array();

        // foreach($get_profilecode as $p_code){

        //     $id= $p_code->id;
        //     $value = $p_code->profilecode;

        //      $profile[$id] = $value;

        // }

       $select = $mform->addElement('autocomplete', 'profilecode', get_string('profilecode','local_exams'), $get_profilecode);
        $mform->setType('profilecode', PARAM_TEXT); 
        if ($profileid) {
            $select->setSelected($profileid);
        }

      
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
        global $DB,$USER;
        $data = $this->get_data();
// print_r($data);die;
        $p_data=$DB->get_record('local_exam_userhallschedules', array('examid'=>$data->examid,'userid'=>$data->userid));

        
        // $p_data->id=$p_data->id;
        // $p_data->examid=$data->examid;
        $p_data->profileid= $data->profilecode;
        // $p_data->userid=$data->userid;
        
        $updatedprofileid=$DB->update_record('local_exam_userhallschedules',$p_data);

    }

    public function set_data_for_dynamic_submission(): void {
        global $DB;

        $examid = $this->optional_param('examid', 0, PARAM_INT);
           $userid = $this->optional_param('userid', 0, PARAM_INT);

        $p_data=$DB->get_record_sql("SELECT ep.id, ep.profilecode FROM {local_exam_profiles} ep JOIN {local_exam_userhallschedules} uhs ON uhs.profileid = ep.id WHERE uhs.examid= :examid AND uhs.userid = :userid", array('examid'=>$data->examid,'userid'=>$data->userid));
        // print_r($p_data);die;


        $data->profilecode=$p_data->profilecode;

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

