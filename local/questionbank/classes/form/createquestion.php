<?php
namespace local_questionbank\form;
require_once($CFG->dirroot.'/local/questionbank/lib.php');
use core_form\dynamic_form;
use moodle_url;
use context;
use context_system;
use local_questionbank;

class createquestion extends dynamic_form { 
    public function definition() {
        global $USER, $CFG,$DB;
        $corecomponent = new \core_component();
        $questionbankid = $this->optional_param('questionbankid', 0, PARAM_INT);
        $mform = $this->_form;
        //$id = $this->_customdata['id'];
        $wid = $this->_customdata['questionbankid'];
        $questionid = $this->_customdata['questionid'];
        // print_r($this->_customdata);
        // exit;
        $mform->addElement('hidden', 'questionbankid', $wid);
        $mform->setType('questionbankid',PARAM_INT);

        $workshopid = $DB->get_field_sql("SELECT id FROM {local_questionbank} where  qcategoryid =".$wid);
        $currentlang= current_language();
         $fullname = (new \local_trainingprogram\local\trainingprogram())->user_fullname_case();
        $experts_info=$DB->get_records_sql_menu("SELECT u.id, $fullname FROM {local_qb_experts} as qe 
            JOIN {user} as u ON u.id = qe.expertid 
            JOIN {local_users} AS lc ON lc.userid = qe.expertid where questionbankid =".$workshopid);

        $reviewer = array(null => get_string('assign_reviewer', 'local_questionbank')) + $experts_info ;
        $reviewr_selection = $mform->addElement('autocomplete','reviewer', get_string('reviewer', 'local_questionbank'), $reviewer);
        $mform->addRule('reviewr_selection', get_string('courserequired','local_questionbank'), 'required', null);
        $reviewr_selection->setMultiple(false);

        $mform->addElement('hidden', 'workshopid');
        $mform->setType('workshopid', PARAM_INT);
        $mform->setDefault('workshopid', $wid); 
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
        require_capability('moodle/site:config', $this->get_context_for_dynamic_submission());
    }

    /**
     * Process the form submission, used if form was submitted via AJAX
     */
    public function process_dynamic_submission() {
        global $CFG, $DB;
        require_once($CFG->dirroot.'/user/profile/definelib.php');
        $data = $this->get_data();
        (new \questionbank)->create_qb_experts($data);
    }

    /**
     * Load in existing data as form defaults
     */
    public function set_data_for_dynamic_submission(): void {
        global $DB;
        if ($id = $this->optional_param('id', 0, PARAM_INT)) {
            $data = $DB->get_record('local_qb_experts', ['id' => $id], '*', MUST_EXIST);
            $this->set_data(['id' => $data->id]);
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

       


