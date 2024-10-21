<?php
namespace local_questionbank\form;

use core_form\dynamic_form;
use moodle_url;
use context;
use context_system;
use local_exams;
class competencieslistform extends dynamic_form { 
    public function definition() {
        global $USER, $CFG,$DB;
        $corecomponent = new \core_component();
        $id = $this->optional_param('id', 0, PARAM_INT);
        $mform = $this->_form;

        $mform->addElement('hidden', 'id', $id);
        $mform->setType('id',PARAM_INT);
        $currentlang= current_language();
        if( $currentlang == 'ar'){
           $competencies = $DB->get_records_sql_menu("SELECT id, (CASE WHEN arabicname IS NULL THEN name ELSE arabicname END) as name FROM {local_competencies}");
        }else{
            $competencies = $DB->get_records_sql_menu("SELECT id, name FROM {local_competencies}");
        }
        $select = $mform->addElement('autocomplete', 'competency', get_string('competencies','local_trainingprogram'), $competencies);
        $mform->addRule('competency',  get_string('competencieserr','local_questionbank'), 'required', null, 'server');
        $select->setMultiple(true);
    }
    public function validation($data, $files) {
        global $DB, $CFG;
//errors = array();
        $errors = parent::validation($data, $files);
        if(empty($data['competency'])){
           $errors['competency'] = get_string('competencies','local_trainingprogram');
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
        global $CFG, $DB,$USER;
        require_once($CFG->dirroot.'/user/profile/definelib.php');
        $data = $this->get_data();

        $row['id'] = $data->id;
        $row['competency'] = implode(',', $data->competency);
        // Trigger delete questionbank.
        $eventparams = array('context' => context_system::instance(),'objectid'=>$data->id);
        $event = \local_questionbank\event\assign_competancies::create($eventparams);
        $event->trigger();
        // notification Questionbank  onchange
        $questionbankrecord=$DB->get_record('local_questionbank',array('id' => $data->id));
        $sql="SELECT u.* FROM {user} u
        JOIN {local_qb_experts} le ON le.expertid = u.id
        WHERE le.questionbankid = $data->id AND u.confirmed = 1 AND u.suspended = 0 AND u.deleted = 0 AND u.id > 2";
        $touser = $DB->get_records_sql($sql);
         if(!$touser)
         {
          $touser=null;
         }
       $row1=[];
       $row1['RelatedModuleName']=$questionbankrecord->workshopname;
       $row1['RelatedModulesLink']=$CFG->dirroot.'/local/questionbank/questionbank_workshop.php?id='.$data->id;
      $myobject=(new \local_questionbank\notification);
      $myobject->questionbank_notification('questionbank_onchange',$touser, $USER,$row1,$waitinglistid=0);
        return $DB->update_record('local_questionbank', $row);
    }

    /**
     * Load in existing data as form defaults
     */
    public function set_data_for_dynamic_submission(): void {
        global $DB;
         if ($id = $this->optional_param('id', 0, PARAM_INT)) {
            $data = $DB->get_record('local_questionbank', ['id' => $id], '*', MUST_EXIST);
            $this->set_data($data);
        }
    }

    /**
     * Returns url to set in $PAGE->set_url() when form is being rendered or submitted via AJAX
     *
     * @return moodle_url
     */
    protected function get_page_url_for_dynamic_submission(): moodle_url {
        return new moodle_url('/local/questionbank/index.php');
    }    
}
