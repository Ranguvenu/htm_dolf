<?php
namespace local_trainingprogram\form;

use core_form\dynamic_form ;
use moodle_url;
use context;
use context_system;
use local_trainingprogram\local\trainingprogram as tp;

class todoactivities_form extends dynamic_form { 
    public function definition() {
        global $USER, $CFG,$DB, $OUTPUT;
        $corecomponent = new \core_component();
        $offeringid = $this->optional_param('offeringid', 0, PARAM_INT);
        $offeringcode = $this->optional_param('offeringcode', 0, PARAM_RAW);
        $evaluationmethods = $this->optional_param('evaluationmethods', 0, PARAM_RAW);
        $mform = $this->_form;
        $methods = [];
        $mform->addElement('hidden', 'offeringid', $offeringid);
        $mform->setType('offeringid',PARAM_INT);
        $offeringrecord =$DB->get_record('tp_offerings',array('id'=>$offeringid));
        $courseid = $DB->get_field('local_trainingprogram' ,'courseid',['id' =>$offeringrecord->trainingid]);
        $evaluationmethods = $DB->get_field('local_trainingprogram','evaluationmethods',array('id'=>$offeringrecord->trainingid));
        if($evaluationmethods) {
            $methods = explode(',',$evaluationmethods);
        }
        if($methods[0]=='0' || $evaluationmethods ==0) {
            $pre_quiz=[];
            if(!$offeringrecord->prequiz) {
                $pre_quiz[''] = get_string('selectprequiz','local_trainingprogram');
            }
            $pre_quiz_sql = " SELECT q.id AS id, q.name AS fullname FROM {course_modules} cm 
            JOIN {course_sections} cs ON cs.id = cm.section
            JOIN {quiz} q ON q.id = cm.instance 
            WHERE cm.course = $courseid AND cm.deletioninprogress = 0
            AND cm.module = (SELECT id FROM {modules} WHERE name = 'quiz') AND cs.id = $offeringrecord->sections";
            if($offeringrecord->prequiz) {
                $pre_quiz_sql .=  " AND cm.instance = $offeringrecord->prequiz"; 
            }
            if($offeringrecord->postquiz) {
                $pre_quiz_sql .=   " AND cm.instance <> $offeringrecord->postquiz";
            }
            $pre_quiz_list = $DB->get_records_sql($pre_quiz_sql);
            if(empty($pre_quiz_list)) {
                $pre_quiz[''] = get_string('selectprequiz','local_trainingprogram');
                $pre_quiz_sql = " SELECT q.id AS id, q.name AS fullname FROM {course_modules} cm 
                JOIN {course_sections} cs ON cs.id = cm.section
                JOIN {quiz} q ON q.id = cm.instance 
                WHERE cm.course = $courseid AND cm.module = (SELECT id FROM {modules} WHERE name = 'quiz')
                AND cs.id = $offeringrecord->sections AND cm.deletioninprogress = 0";
               /* if($offeringrecord->postquiz) {
                    $pre_quiz_sql .=   " AND cm.instance <> $offeringrecord->postquiz";
                }*/
                $pre_quiz_list = $DB->get_records_sql($pre_quiz_sql);
            }
            if($pre_quiz_list) {
                foreach ($pre_quiz_list AS $pre){
                    $pre_quiz[$pre->id] = format_text($pre->fullname, FORMAT_HTML);
                }
            }
            $mform->addElement('select', 'prequiz', get_string('pre_exam', 'local_trainingprogram'), $pre_quiz);
        } 
        if($methods[1]=='1' || $evaluationmethods==1) {
            $post_quiz=[];
            if(!$offeringrecord->postquiz) { 
                $post_quiz[''] = get_string('selectpostquiz','local_trainingprogram');
            }
            $post_quiz_sql = " SELECT q.id AS id, q.name AS fullname FROM {course_modules} cm 
            JOIN {course_sections} cs ON cs.id = cm.section
            JOIN {quiz} q ON q.id = cm.instance
            WHERE cm.course = $courseid AND cm.module = (SELECT id FROM {modules} WHERE name = 'quiz') 
            AND cs.id = $offeringrecord->sections AND cm.deletioninprogress = 0 ";
            if($offeringrecord->postquiz) {
                $post_quiz_sql .=  " AND  cm.instance = $offeringrecord->postquiz ";
            }
            if($offeringrecord->prequiz) {
                $post_quiz_sql .=   " AND cm.instance <> $offeringrecord->prequiz";
            }
            $post_quiz_list = $DB->get_records_sql($post_quiz_sql);

            if(empty($post_quiz_list)) {
                $post_quiz[''] = get_string('selectpostquiz','local_trainingprogram');
                $post_quiz_sql = " SELECT q.id AS id, q.name AS fullname FROM {course_modules} cm 
                JOIN {course_sections} cs ON cs.id = cm.section
                JOIN {quiz} q ON q.id = cm.instance
                WHERE cm.course = $courseid AND cm.module = (SELECT id FROM {modules} WHERE name = 'quiz') 
                AND cs.id = $offeringrecord->sections AND cm.deletioninprogress = 0";
                /*if($offeringrecord->prequiz) {
                    $post_quiz_sql .=   " AND cm.instance <> $offeringrecord->prequiz";
                }*/
                $post_quiz_list = $DB->get_records_sql($post_quiz_sql);
            }
            if($post_quiz_list) {
                foreach ($post_quiz_list AS $post){
                    $post_quiz[$post->id] = format_text($post->fullname, FORMAT_HTML);
                }
            }
            $mform->addElement('select', 'postquiz', get_string('post_exam', 'local_trainingprogram'), $post_quiz);     
        }
        $attendance = [];
        $attendance_sql = " SELECT a.id AS id, a.name AS fullname FROM {offering_sessions} fs
        JOIN {attendance_sessions} ass ON ass.id = fs.sessionid 
        JOIN {course_modules} cm ON cm.course = fs.courseid
        JOIN {course_sections} cs ON cs.id = cm.section
        JOIN {attendance} a ON a.id = cm.instance AND ass.attendanceid = a.id
        WHERE cs.id = $offeringrecord->sections AND fs.offeringid = $offeringrecord->id 
        AND cm.course = $courseid AND cm.module = (SELECT id FROM {modules} WHERE name = 'attendance') AND cm.deletioninprogress = 0";
        $attendance_list = $DB->get_records_sql($attendance_sql);

        if(empty($attendance_list)) {
            $attendance[''] = get_string('selectattendance','local_trainingprogram');
            $attendance_sql = " SELECT a.id AS id, a.name AS fullname FROM {course_modules} cm 
            JOIN {course_sections} cs ON cs.id = cm.section
            JOIN {attendance} a ON a.id = cm.instance
            WHERE cs.id = $offeringrecord->sections  AND cm.course = $courseid 
            AND cm.module = (SELECT id FROM {modules} WHERE name = 'attendance') AND cm.deletioninprogress = 0";
            $attendance_list = $DB->get_records_sql($attendance_sql);
        }
        if($attendance_list) {
            foreach ($attendance_list AS $att){
                $attendance[$att->id] = format_text($att->fullname, FORMAT_HTML);
            }
        }
        if ($offeringrecord->trainingmethod != 'elearning') {
            $mform->addElement('select', 'attendance', get_string('attendance', 'local_trainingprogram'), $attendance);
        }
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
         has_capability('local/trainingprogram:manage', $this->get_context_for_dynamic_submission()) 
        || has_capability('local/organization:manage_trainingofficial', $this->get_context_for_dynamic_submission());
    }

    /**
     * Process the form submission, used if form was submitted via AJAX
     */
    public function process_dynamic_submission() {
        global $CFG, $DB;
        require_once($CFG->dirroot.'/user/profile/definelib.php');
        $data = $this->get_data();
        (new tp)->add_update_program_activities($data);

    }

    /**
     * Load in existing data as form defaults
     */
    public function set_data_for_dynamic_submission(): void {
        global $DB;

        if ($offeringid = $this->optional_param('offeringid', 0, PARAM_INT)) {
            $data = $DB->get_records('tp_offerings', ['id' => $offeringid]);
            $formdata = new \stdClass();
            $this->set_data($formdata);
        }
        
    }

    /**
     * Returns url to set in $PAGE->set_url() when form is being rendered or submitted via AJAX
     *
     * @return moodle_url
     */
    protected function get_page_url_for_dynamic_submission(): moodle_url {
        return new moodle_url('/local/trainingprogram/index.php');
    }    
}
