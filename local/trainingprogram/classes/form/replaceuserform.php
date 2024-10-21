<?php
namespace local_trainingprogram\form;

use core_form\dynamic_form;
use moodle_url;
use context;
use context_system;
use local_trainingprogram\local\trainingprogram as program;
class replaceuserform extends dynamic_form { 
    public function definition() {
        global $USER, $CFG,$DB;
        $corecomponent = new \core_component();
        $mform = $this->_form;
        $programid = $this->optional_param('programid', 0, PARAM_INT);
        $offeringid = $this->optional_param('offeringid', 0, PARAM_INT);
        $userid = $this->optional_param('userid', 0, PARAM_INT);
        $username = $this->optional_param('username', null, PARAM_RAW);
        $useridnumber = $this->optional_param('useridnumber', 0, PARAM_RAW);
        $replacementfee = $this->optional_param('replacementfee', 0, PARAM_INT);
        $programprice = $this->optional_param('programprice','',PARAM_INT);

        $mform->addElement('hidden', 'programid', $programid);
        $mform->setType('programid',PARAM_INT);
        
        $mform->addElement('hidden', 'offeringid',$offeringid);
        $mform->setType('offeringid',PARAM_INT);

        $mform->addElement('hidden', 'fromuserid',$userid);
        $mform->setType('fromuserid',PARAM_INT);

        $mform->addElement('hidden', 'username',$username);
        $mform->setType('username',PARAM_RAW);

        $mform->addElement('hidden', 'useridnumber',$useridnumber);
        $mform->setType('useridnumber',PARAM_RAW);

        $mform->addElement('hidden', 'replacementfee',$replacementfee);
        $mform->setType('replacementfee',PARAM_INT);

        $mform->addElement('hidden', 'programprice',$programprice);
        $mform->setType('programprice',PARAM_INT);

        $fromuser =$username.'('.$useridnumber.')'; 

        $mform->addElement('static', 'from_user', get_string('replacefrom', 'local_trainingprogram'),$fromuser);

        $userattributes = array(
        'ajax' => 'local_exams/getuserstoreplace',
        'data-type' => 'program',
        'data-programid' => $programid,
        'data-offeringid' => $offeringid,
        'multiple'=>false,
        );
        $mform->addElement('autocomplete', 'touserid', get_string('replaceto', 'local_trainingprogram'),[], $userattributes);
        $mform->addRule('touserid', get_string('selectusertoreplace', 'local_trainingprogram'), 'required');

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
        return context_system::instance();
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
        global $USER,$DB;
        $data =$this->get_data();
        if($data->programprice == 0 || ($data->programprice == 1 && is_siteadmin())) {
            $traineeroleid = $DB->get_field('role', 'id', array('shortname' => 'trainee'));
            $courseid=$DB->get_field('local_trainingprogram','courseid', array('id' => $data->programid));
            (new program)->program_unenrollment($data->programid,$data->offeringid,$courseid,$data->fromuserid,$traineeroleid);
            (new program)->program_enrollment($data->offeringid,$data->touserid,$traineeroleid);
        } else {
            $productid = $DB->get_field_sql('SELECT tlp.id FROM {tool_products} tlp 
                                            JOIN {local_trainingprogram} lot ON lot.code = tlp.code 
                                            WHERE tlp.category =:category AND tlp.referenceid =:referenceid',['category'=>1,'referenceid'=>$data->offeringid]);
            $data->productid = $productid;
            return $data;
        }
    }
    /**
     * Load in existing data as form defaults
     */
    public function set_data_for_dynamic_submission(): void {
        
    }
    /**
     * Returns url to set in $PAGE->set_url() when form is being rendered or submitted via AJAX
     *
     * @return moodle_url
     */
    protected function get_page_url_for_dynamic_submission(): moodle_url {
        $examid = $this->optional_param('examid', 0, PARAM_INT);
        return new moodle_url('/local/exams/examusers.php',
            ['id' => $examid]);
    }    
}
