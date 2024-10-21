<?php
namespace local_questionbank\form;
require_once($CFG->dirroot.'/local/questionbank/lib.php');
use core_form\dynamic_form;
use moodle_url;
use context;
use context_system;
use local_questionbank;

class topicsform extends dynamic_form { 
    public function definition() {
        global $USER, $CFG,$DB;
        $corecomponent = new \core_component();
        $questionbankid = $this->optional_param('questionbankid', 0, PARAM_INT);
        $mform = $this->_form;
        //$id = $this->_customdata['id'];
        
    
        $mform->addElement('hidden', 'questionbankid', $questionbankid);
        $mform->setType('questionbankid',PARAM_INT);

       $options = array(
            'multiple' => false,
            'onchange' => "(function(e){ require(['local_questionbank/coursetopics'], function(s) {s.selectedcourses();}) }) (event)",
            'class' => 'el_courselist',
            'placeholder' => get_string('courses', 'local_questionbank'),
            
        );
         //['data-action'=>'selectedcourses','class' => 'el_courselist','placeholder' => get_string('courses', 'local_questionbank')]
        //  $competencytypeoptions = [
        //     'ajax' => 'local_trainingprogram/dynamic_dropdown_ajaxdata',
        //     'data-type' => 'program_competency',
        //     'class' => 'el_competencytype',
        //     'multiple'=>true,
        //     'onchange' => "(function(e){ require(['local_trainingprogram/dynamic_dropdown_ajaxdata'], function(s) {s.ctype();}) }) (event)",
        // ];

        $questionbank = $DB->get_field('local_questionbank','course', array('id'=>$questionbankid));
        $querysql = "SELECT c.id AS courseid,c.fullname as coursename FROM {course} as c WHERE id IN ($questionbank)";
        $courses= $DB->get_records_sql($querysql);
        $courselist = array();
        foreach ($courses AS $course) {
            $courselist[$course->courseid] = format_string($course->coursename);
        }
        //echo $querysql ;
        $courselist=array(null => get_string('courses', 'local_questionbank')) + $courselist; 
        $select =$mform->addElement('autocomplete','coursetopic', get_string('course', 'local_questionbank'), $courselist,$options);
        $mform->addRule('coursetopic',  get_string('missingcourse', 'local_questionbank'), 'required', null, 'server');
        $mform->setType('coursetopic', PARAM_RAW);
        $select->setMultiple(false);

        $options = array(
            'ajax' => 'local_questionbank/coursetopics',
            'data-type' => 'topicslist',
            'data-questionbankid' => $questionbankid,
            'id' => 'el_topicsinfo',
            'data-topics' => '',
            'class' => 'topics',
            'multiple' => true,
            'placeholder' => get_string('selecttopics', 'local_questionbank')
        );

        $mform->addElement('autocomplete','topicsid', get_string('topics', 'local_questionbank'), [], $options);
        $mform->addRule('topicsid',  get_string('topicserror', 'local_questionbank'), 'required', null, 'server');
        $mform->setType('topicsid', PARAM_RAW);
        
    }
    public function validation($data, $files) {
        global $DB, $CFG;
        $errors = array();
        if(empty($data['topicsid'])){
            $errors['topicsid'] = get_string('topicserror', 'local_questionbank');
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
        require_capability('local/questionbank:assignreviewer', $this->get_context_for_dynamic_submission());
    }

    /**
     * Process the form submission, used if form was submitted via AJAX
     */
    public function process_dynamic_submission() {
        global $CFG, $DB;
        require_once($CFG->dirroot.'/user/profile/definelib.php');
        $data = $this->get_data();
        (new \questionbank)->create_qb_topics($data);
    }

    /**
     * Load in existing data as form defaults
     */
    public function set_data_for_dynamic_submission(): void {
        global $DB;
        if ($id = $this->optional_param('id', 0, PARAM_INT)) {
            $data = $DB->get_record('local_qb_coursetopics', ['id' => $id], '*', MUST_EXIST);
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
        return new moodle_url('/local/questionbank/index.php',
            ['action' => 'editcategory', 'id' => $id]);
    }    
}
