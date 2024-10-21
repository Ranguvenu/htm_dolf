<?php
namespace local_trainingprogram\form;
use core_form\dynamic_form ;
use moodle_url;
use context;
use context_system;
use local_trainingprogram\local\evalutionmethod as evalutionmethod;
require_once($CFG->libdir . '/formslib.php');

class evalutionmethod_form extends dynamic_form { 
    public function definition() {
        global $USER, $CFG,$DB;

        $mform = $this->_form;
        $corecomponent = new \core_component();
        $id = $this->optional_param('id', 0, PARAM_RAW);
        $systemcontext = context_system::instance();

        $mform->addElement('hidden', 'id', $id);
        $mform->setType('id',PARAM_INT);

   
        $mform->addElement('text', 'evaluationmethods', get_string('evaluationmethoden','local_trainingprogram'));
        $mform->addRule('evaluationmethods', get_string('evaluationmethoderror', 'local_trainingprogram'), 'required');
        $mform->setType('evaluationmethods',PARAM_RAW);
   
        $mform->addElement('text', 'evaluationmethodsab', get_string('evaluationmethodab','local_trainingprogram'));
        $mform->addRule('evaluationmethodsab', get_string('evaluationmethoderror', 'local_trainingprogram'), 'required');
        $mform->setType('evaluationmethodsab',PARAM_RAW);

    }
    public function validation($data, $files) {
        $errors = array();
        global $DB, $CFG;

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
       //has_capability('local/trainingprogram:manage_refundsettings', $this->get_context_for_dynamic_submission());
    }

    /**
     * Process the form submission, used if form was submitted via AJAX
     */
    public function process_dynamic_submission() {
        global $CFG, $DB;
       // require_once($CFG->dirroot.'/user/profile/definelib.php');
        $data = $this->get_data();
       ($data->id > 0) ? (new evalutionmethod)::update_evaluation_methods($data)  :  (new evalutionmethod)::create_evaluation_methods($data)  ;
    }
    /**
     * Load in existing data as form defaults
     */
    public function set_data_for_dynamic_submission(): void {
              global $DB;

         if ($id = $this->optional_param('id', 0, PARAM_INT)) {
            
            $data=$DB->get_record('evalution_methods',array ('id' =>$id));
            $str = $data->name;
            // Setting name for enlish field
            preg_match('/{mlang en}(.*?){mlang}/', $str, $match);
            $englishname =  $match[1];
            // Setting name for arabic field
             preg_match('/{mlang ar}(.*?){mlang}/', $str, $match);
            $arabicname =  $match[1];
            $data->evaluationmethods = $englishname;
            $data->evaluationmethodsab = $arabicname;
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
        return new moodle_url('/local/trainingprogram/evalution_method.php');
    }    
}
