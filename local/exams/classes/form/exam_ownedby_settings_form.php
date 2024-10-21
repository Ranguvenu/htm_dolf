<?php
namespace local_exams\form;
defined('MOODLE_INTERNAL') || die();
require_once($CFG->libdir.'/formslib.php');
require_once($CFG->libdir.'/csvlib.class.php');
use moodle_url;
use context;
use context_system;
use moodleform;
class exam_ownedby_settings_form extends moodleform { 
    public function definition() {
        global $DB;
        $mform = $this->_form;
        $records =$DB->get_records_sql("SELECT DISTINCT ownedby FROM {local_exams} WHERE ownedby IS NOT NULL AND ownedby !=''");
        $totalrecords = COUNT($records);
        $mform->addElement('hidden', 'totalrecords',$totalrecords);
        $mform->setType('totalrecords',PARAM_INT);
        foreach($records AS $record ) {
            $elementlable =str_replace(' ','_',$record->ownedby);
            $emailelementlable =$elementlable.'-email';
            $naminglable = $record->ownedby.' - Days';
            $emailnaminglable = $record->ownedby.' - Email';
            $mform->addElement('hidden', 'ownedby', $elementlable);
            $mform->setType('ownedby',PARAM_RAW);

            $mform->addElement('hidden', 'ownedbyemail', $emailelementlable);
            $mform->setType('ownedbyemail',PARAM_RAW);

            $mform->addElement('text',$elementlable,$naminglable,array('class' => 'exam_ownedby_settings','size="40"'));
            $mform->addRule($elementlable, get_string('requirednumeric','local_exams'), 'numeric', null);
            $mform->setType($elementlable,PARAM_RAW);

            $mform->addElement('textarea',$emailelementlable,$emailnaminglable,array('class' => 'exam_ownedby_settings'));
           // $mform->addRule($emailelementlable, get_string('requiredvalidemail','local_exams'), 'email', null);
            $mform->setType($emailelementlable, PARAM_RAW);
            
        }
        $this->add_action_buttons(false);

    }
    public function validation($data, $files) {
        global $DB, $CFG;
        $errors = array();
        $newdata=[];
        foreach($data AS $key=>$value) {
            $key = str_replace('_',' ',$key);
            $newdata[$key] = $value;
        }
        $records =$DB->get_records_sql("SELECT DISTINCT ownedby FROM {local_exams} WHERE ownedby IS NOT NULL AND ownedby !=''");
        foreach($records AS $record ) {
            $elementlable =str_replace(' ','_',$record->ownedby);
            $emailelementlable =$elementlable.'-email';
            $allemails =explode(',',$newdata[$emailelementlable]);
            if(!empty($allemails)) {
                foreach($allemails AS $email){
                    if(!preg_match('/^[A-Za-z0-9._%+-]+@[a-z0-9.-]+\.[a-z]{2,8}$/',$email)){
                        $errors[$emailelementlable] = get_string('validemailrequired','local_exams', $email);
                    }
                }
            }
        }
        return $errors;
    }

}

