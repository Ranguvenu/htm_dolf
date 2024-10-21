<?php
namespace local_exams\form;

use core_form\dynamic_form;
use moodle_url;
use stdClass;
use context;
use context_system;
use local_exams;


class attemptdetailsform extends dynamic_form { 
    public function definition() {
        global $USER, $CFG,$DB;
        $corecomponent = new \core_component();

        $mform = $this->_form;
        $id = $this->optional_param('id', 0, PARAM_INT);
        $examid = $this->optional_param('examid', 0, PARAM_INT);
        $profileid = $this->optional_param('profileid', 0, PARAM_INT);
        $userid = $this->optional_param('userid', 0, PARAM_INT);
        $lastattemptprofileid = $this->optional_param('lastattemptprofileid', 0, PARAM_INT);
        $hallscheduleid = $this->optional_param('hallscheduleid', 0, PARAM_INT);
                  
        $mform->addElement('hidden', 'id', $id);
        $mform->setType('id',PARAM_INT);

        $mform->addElement('hidden', 'examid', $examid);
        $mform->setType('examid',PARAM_INT);

        $mform->addElement('hidden', 'profileid', $profileid);
        $mform->setType('profileid',PARAM_INT);        

        $mform->addElement('hidden', 'userid', $userid);
        $mform->setType('userid',PARAM_INT);

        $mform->addElement('hidden', 'lastattemptprofileid', $lastattemptprofileid);
        $mform->setType('lastattemptprofileid',PARAM_INT);

        $mform->addElement('hidden', 'hallscheduleid', $hallscheduleid);
        $mform->setType('hallscheduleid',PARAM_INT);        

        $radioarray=array();
        $radioarray[] = $mform->createElement('radio', 'attemptstatus', '', get_string('absent', 'local_exams'), 0);
        $radioarray[] = $mform->createElement('radio', 'attemptstatus', '', get_string('examfailed', 'local_exams'), 1);
        $mform->addGroup($radioarray, 'radioar', get_string('attemptstatus', 'local_exams'), array(' '), false);

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

        if ($data) {
            $scheduleid = $data->hallscheduleid;

            $sql = "SELECT h.name, h.code as hallcode, leu.examdate, hs.starttime, hs.endtime, leu.attemptid
                    FROM {hall} h 
                    JOIN {hallschedule} hs ON hs.hallid = h.id
                    JOIN {local_exam_userhallschedules} leu ON leu.hallscheduleid = hs.id 
                    WHERE leu.id =".$scheduleid;
            $hallschedule = $DB->get_record_sql($sql);
            $params['attemptstatus'] = $data->attemptstatus;
            $params['previousgrade'] = 0;
            $sql = "SELECT le.code as examcode, lep.profilecode 
                      FROM {local_exams} le
                      JOIN {local_exam_profiles} lep ON lep.examid = le.id
                     WHERE lep.id =".$data->lastattemptprofileid;
            $codes = $DB->get_record_sql($sql);
            $params['examcode'] = $codes->examcode;
            $params['profilecode'] = $codes->profilecode;
            $params['username'] = $DB->get_field('user', 'username', ['id'=>$data->userid]);
            $params['hallcode'] = $hallschedule->hallcode;
            $params['grade'] = !empty($data->previousgrade) ? $data->previousgrade : 0;
            $params['achievementdate'] = userdate($hallschedule->examdate, '%Y-%m-%d');
            if ($hallschedule->attemptid == 0) {
                $params['attemptcount'] = 1;
            } else {
                $examattemptid = $DB->get_field('local_exam_attempts', 'attemptid', ['id'=>$hallschedule->attemptid]);
                $params['attemptcount'] = $examattemptid;
            }
            $params['certificatecode'] = 'No';
            $params['examdate'] = userdate($hallschedule->examdate, '%Y-%m-%d');
            $params['starttime'] = userdate($hallschedule->examdate, '%X');
            $params['endtime'] = userdate($hallschedule->examdate, '%X');

            return ['attemptstatus'=>$data->attemptstatus, 'returnparams' => base64_encode(serialize($params))];
        }
    }

    /**
     * Load in existing data as form defaults
     */
    public function set_data_for_dynamic_submission(): void {
        global $DB;
        $data = new stdClass();
        $data->examid = $this->optional_param('examid', 0, PARAM_INT);
        $data->profileid = $this->optional_param('profileid', 0, PARAM_INT);
        $data->userid = $this->optional_param('userid', 0, PARAM_INT);
        $data->lastattemptprofileid = $this->optional_param('lastattemptprofileid', 0, PARAM_INT);
        $data->hallscheduleid = $this->optional_param('hallscheduleid', 0, PARAM_INT);
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
            ['action' => 'editcategory', 'id' => $id]);
    }    
}
