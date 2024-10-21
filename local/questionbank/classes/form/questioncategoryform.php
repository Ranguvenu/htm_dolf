<?php
namespace local_questionbank\form;
require_once($CFG->dirroot.'/local/questionbank/lib.php');
require_once($CFG->libdir . '/questionlib.php');
//require_once($CFG->dirroot. '/../../editlib.php');
use core_form\dynamic_form;
use moodle_url;
use context;
use context_system;
use context_user;
use question_bank;
use local_questionbank\local\view;
use question_engine;
use qbank_previewquestion\question_preview_options;

class questioncategoryform extends dynamic_form { 
    public function definition() {
        global $USER, $CFG,$DB,$SESSION;
        $corecomponent = new \core_component();
        $systemcontext = context_system::instance();
        $workshopid = $this->optional_param('workshopid', 0, PARAM_INT);
        $qcategoryid = $this->optional_param('qcategoryid', 0, PARAM_INT);
        $mform = $this->_form;
        if($qcategoryid  > 0){
          $_SESSION['qcategoryid'] = $qcategoryid;
        }else{
          $qcategoryid =  $_SESSION['qcategoryid'];
        }
        //$id = $this->_customdata['id'];
        
    
        $mform->addElement('hidden', 'workshopid', $workshopid);
        $mform->setType('workshopid',PARAM_INT);
        $mform->addElement('hidden', 'fromcategory', $qcategoryid);
        $mform->setType('fromcategory',PARAM_INT);
        
        $question_category_parent = $DB->get_field_sql("SELECT id FROM {question_categories} where name ='top' and parent= 0 AND contextid= ".$systemcontext->id);
        $question_category = $DB->get_records_sql_menu("SELECT id,name FROM {question_categories} where parent!= 0 AND contextid=".$systemcontext->id." AND parent=$question_category_parent AND name !='Workshop Categories' AND (idnumber!='workshop_categories' OR idnumber IS NULL)");
      //$categories= $DB->get_records_sql_menu($question_category);

        //echo $querysql ;
        $categories=array(null => get_string('ofcategories', 'local_questionbank')) + $question_category; 
        $select =$mform->addElement('autocomplete','tocategory', get_string('qcategory', 'local_questionbank'), $categories);
        $mform->addRule('tocategory',  get_string('missingcategory', 'local_questionbank'), 'required', null, 'server');
        $mform->setType('tocategory', PARAM_RAW);
        $select->setMultiple(false);

        $get_questions = "SELECT q.id,q.name
                       FROM (SELECT q.*, qbe.questioncategoryid as category
                       FROM {question} q
                       JOIN {question_versions} qv ON qv.questionid = q.id
                       JOIN {question_bank_entries} qbe ON qbe.id = qv.questionbankentryid
                       WHERE questioncategoryid =$qcategoryid ) q";
        $get_questions = $DB->get_records_sql($get_questions);   
        foreach($get_questions as $questions){
          //$lastchanged = $questions->id;
              $question = question_bank::load_question($questions->id);
              $maxvariant = min($question->get_num_variants(), QUESTION_PREVIEW_MAX_VARIANTS);
              $options = new question_preview_options($question);
              $options->load_user_defaults();
              $options->set_from_request();
              $quba = question_engine::make_questions_usage_by_activity(
                      'core_question_preview', context_user::instance($USER->id));
              $quba->set_preferred_behaviour($options->behaviour);
              $slot = $quba->add_question($question, $options->maxmark);

              if ($options->variant) {
                  $options->variant = min($maxvariant, max(1, $options->variant));
              } else {
                  $options->variant = rand(1, $maxvariant);
              }

              $quba->start_question($slot, $options->variant);

              $transaction = $DB->start_delegated_transaction();
              question_engine::save_questions_usage_by_activity($quba);
              $transaction->allow_commit();
              $options->behaviour = $quba->get_preferred_behaviour();
              $options->maxmark = $quba->get_question_max_mark($slot);
              $qinfo =$quba->get_question($slot);
              $res .=  $quba->render_question($slot, $options, $displaynumber);
            }
            //echo $res;
            //$mform->addElement('html','displayquestion',null ,  $res);
           $mform->addElement('html', "<h5>".get_string('listofquestions', 'local_questionbank')."</h5><div>$res</div>");
       
        
    }
    public function validation($data, $files) {
        global $DB, $CFG;
        $errors = array();

        if(empty($data['tocategory'])){
            $errors['tocategory'] = get_string('missingcategory', 'local_questionbank');
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
        $tocategory = (new \questionbank)->movequestions($data);
        $DB->update_record('local_questionbank',array('id'=> $data->workshopid,'movedtoprod'=>1 ,'tocategoryid'=>$data->tocategory));
       
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
