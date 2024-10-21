<?php
namespace local_trainingprogram\form;
use core_form\dynamic_form ;
use moodle_url;
use context;
use context_system;
use local_trainingprogram\local\trainingprogram as tp;
use coding_exception;
use MoodleQuickForm_autocomplete;

class trainingtopicsform extends dynamic_form { 
    public function definition() {
        global $USER, $CFG,$DB;

        $corecomponent = new \core_component();
        $mform = $this->_form;
        $id = $this->optional_param('id', 0, PARAM_RAW);
        $systemcontext = context_system::instance();

        $mform->addElement('hidden', 'id', $id);
        $mform->setType('id',PARAM_INT);

        $mform->addElement('text', 'name', get_string('topicname','local_trainingprogram'),'size="100"');
        $mform->addRule('name', get_string('topicnameerror','local_trainingprogram'), 'required');
        $mform->setType('name', PARAM_RAW);


         $mform->addElement('text', 'namearabic', get_string('topicnamearabic','local_trainingprogram'),'size="100"');
        $mform->addRule('namearabic', get_string('topicnameerror','local_trainingprogram'), 'required');
        $mform->setType('namearabic', PARAM_RAW);

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
        return \context_system::instance();
    }

    /**
     * Checks if current user has access to this form, otherwise throws exception
     */
    protected function check_access_for_dynamic_submission(): void {
        //require_capability('moodle/site:config', $this->get_context_for_dynamic_submission());
         has_capability('local/trainingprogram:manage', $this->get_context_for_dynamic_submission()) 
        || has_capability('local/organization:manage_trainingofficial', $this->get_context_for_dynamic_submission());
    }

    /**
     * Process the form submission, used if form was submitted via AJAX
     */
    public function process_dynamic_submission() {
        global $CFG, $DB;
        $data = $this->get_data();
        (new tp)->create_update_trainingtopic($data);
    }

    /**
     * Load in existing data as form defaults
     */
    public function set_data_for_dynamic_submission(): void {
        global $DB;
         if ($id = $this->optional_param('id', 0, PARAM_INT)) {
            $data = $DB->get_record('training_topics', ['id' => $id], '*', MUST_EXIST);
            $context = context_system::instance();

            $str = $data->name;
            
            // Setting name for enlish field
            preg_match('/{mlang en}(.*?){mlang}/', $str, $match);
            $englishname =  $match[1];

            // Setting name for arabic field
             preg_match('/{mlang ar}(.*?){mlang}/', $str, $match);
            $arabicname =  $match[1];
            $data->name = $englishname;
            $data->namearabic = $arabicname;

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
        return new moodle_url('/local/trainingprogram/trainingtopics.php');
    }    
}
