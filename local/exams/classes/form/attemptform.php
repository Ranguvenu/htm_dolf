<?php
namespace local_exams\form;

use core_form\dynamic_form;
use moodle_url;
use context;
use context_system;
use local_exams;
use local_trainingprogram\local\trainingprogram as tp;

class attemptform extends dynamic_form { 
    public function definition() {
        global $USER, $CFG,$DB;
        $corecomponent = new \core_component();

        $mform = $this->_form;
        $id = $this->optional_param('id', 0, PARAM_INT);
        $examid = $this->optional_param('examid', 0, PARAM_INT);
        $editoroptions = $this->_customdata['editoroptions'];
         
        $systemcontext = context_system::instance();
    
        $mform->addElement('hidden', 'id', $id);
        $mform->setType('id',PARAM_INT);

        $mform->addElement('hidden', 'examid', $examid);
        $mform->setType('examid',PARAM_INT);

        $attemptno = $DB->get_field('local_exam_attempts','attemptid', ['id' => $id]);
        $mform->addElement('hidden', 'attemptnumber', $attemptno);
        $mform->setType('attemptnumber',PARAM_INT);

        $attemptid = $DB->get_field('local_exam_attempts','COUNT(id)',array('examid'=>$examid));
        $mform->addElement('text', 'attemptid', get_string('attemptnumber', 'local_exams'), ['size' => 3]);
        $mform->setDefault('attemptid', ++$attemptid);
        $mform->disabledIf('attemptid', 'id', 'neq', '');

        $mform->addElement('text', 'daysbeforeattempt', get_string('daysbeforeattempt', 'local_exams'), ['size' => 4]);
        $mform->setDefault('daysbeforeattempt', 14);
        $mform->disabledIf('daysbeforeattempt', 'attemptnumber', 'eq', 1);

        $mform->addElement('text', 'fee', get_string('fee', 'local_exams'), ['size' => 5]);
        $mform->disabledIf('fee', 'attemptnumber', 'eq', 1);
    }
    public function validation($data, $files) {
        global $DB, $CFG;
        $errors = array();
        if($data['daysbeforeattempt'] < 0) {
            $errors['daysbeforeattempt'] = get_string('dayscannotbenegative','local_exams');
        }
        if($data['fee'] < 0) {
            $errors['fee'] = get_string('feecannotbenegative','local_exams');
        }

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
        // require_capability('moodle/site:config', $this->get_context_for_dynamic_submission());
         has_capability('local/exams:create', $this->get_context_for_dynamic_submission()) 
                || has_capability('local/organization:manage_examofficial', $this->get_context_for_dynamic_submission());
    }

    /**
     * Process the form submission, used if form was submitted via AJAX
     */
    public function process_dynamic_submission() {
        global $CFG, $DB, $USER;
        require_once($CFG->dirroot.'/user/profile/definelib.php');
        $systemcontext = context_system::instance();
        $data = $this->get_data();
        if ($data->id > 0) {
            $id = $DB->update_record('local_exam_attempts', $data);
            $event =  \local_exams\event\exam_attemptupdated::create(array( 'context'=>$systemcontext, 'objectid' => $data->id));
            $event->trigger();
        } else {
            $data->usercreated = $USER->id;
            $data->timecreated = time();
            $attemptid = $DB->insert_record('local_exam_attempts', $data);
            $data->id = $data->examid;
            $data->noofattempts = $data->attemptid;
            $DB->update_record('local_exams', $data);

            $quizids = $DB->get_fieldset_sql("SELECT quizid FROM {local_exam_profiles} where examid =". $data->examid);
            foreach($quizids as $quizid) {
                $quiz->id = $quizid;
                $quiz->attempts = $data->attemptid;
                $DB->update_record('quiz', $quiz);
            }
            $event =  \local_exams\event\exam_attempt::create(array( 'context'=>$systemcontext, 'objectid' => $attemptid));
            $event->trigger();
        }
    }

    /**
     * Load in existing data as form defaults
     */
    public function set_data_for_dynamic_submission(): void {
        global $DB;
        if ($id = $this->optional_param('id', 0, PARAM_INT)) {
            $attempt = $DB->get_record('local_exam_attempts', ['id' => $id]);
            $this->set_data($attempt);
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
