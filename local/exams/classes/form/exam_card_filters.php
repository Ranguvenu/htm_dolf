<?php
namespace local_exams\form;
require_once($CFG->libdir.'/formslib.php');
use core_form\moodleform ;
use moodle_url;
use context;
use context_system;
class exam_card_filters extends \moodleform {
  public function definition() {
    global $USER, $CFG,$DB;
    $corecomponent = new \core_component();
    $mform = $this->_form;
    $submitid = 'viewexams';
    $this->_form->_attributes['id'] = $submitid;
      $sectors = $DB->get_records_menu('local_sector');
      $sectorsattributes = array(
        'multiple' => true,
        'noselectionstring' => get_string('sectors', 'local_trainingprogram'), 
        'class' => 'el_sectorlist' 
    );
     $filter_groupelemnts=array();
     $filter_groupelemnts[] = $mform->createElement('date_selector','fromdate','',array('optional'=>true));
     $filter_groupelemnts[] = $mform->createElement('date_selector','todate','',array('optional'=>true));
     $filter_groupelemnts[] = $mform->createElement('autocomplete','sectors','',$sectors,$sectorsattributes);
     $mform->addGroup($filter_groupelemnts, 'filter_group_elements','', array('class' => 'exam_card_filter_groupelemnts', 'id' => 'exam_card_filter_groupelemnts'), false);
    //$mform->disable_form_change_checker();        
}
  public function validation($data, $files) {
    global $DB;
    $errors = parent::validation($data, $files);
    return $errors;
  }
}