<?php
namespace local_trainingprogram\form;
require_once($CFG->libdir.'/formslib.php');
use core_form\moodleform ;
use moodle_url;
use context;
use context_system;
use local_trainingprogram\local\trainingprogram as tp;
class program_card_filters extends \moodleform {
  public function definition() {
    global $USER, $CFG,$DB;
    $corecomponent = new \core_component();
    $mform = $this->_form;

      $sectors = $DB->get_records_menu('local_sector');
      $sectorsattributes = array(
        'multiple'=>true,
        'noselectionstring' => get_string('sectors', 'local_trainingprogram'), 
        'class' => 'el_sectorlist' 
    );

    $jfdattributes = array(
        'ajax' => 'local_trainingprogram/sector_datasource',
        'data-type' => 'jobfamily',
        'data-sectorid' =>0,
        'multiple'=>true,
        'noselectionstring' => get_string('job_family', 'local_trainingprogram'),
    );
    $clattributes = array(
        'ajax' => 'local_trainingprogram/dynamic_dropdown_ajaxdata',
        'data-type' => 'program_competencylevel',
        'data-ctype' =>'All',
        'data-programid' =>1,
        'data-offeringid' =>1,
        'multiple'=>true,
        'noselectionstring' => get_string('competencies', 'local_trainingprogram'),
    );

      $jobfamilies = array();
      $jobfamilieslist = $this->_ajaxformdata['targetgroup'];

      if (!empty($jobfamilieslist)) {

          $jobfamilies = tp::trainingprogram_jobfamily(0,array($jobfamilieslist ),$id);

      } elseif ($id > 0) {

          $jobfamilies = tp::trainingprogram_jobfamily(0,array(),$id);
          
      }
     $filter_groupelemnts=array();
     $filter_groupelemnts[] = $mform->createElement('date_selector','fromdate','',array('optional'=>true));
     $filter_groupelemnts[] = $mform->createElement('date_selector','todate','',array('optional'=>true));
     $filter_groupelemnts[] =$mform->createElement('autocomplete','sectors','',$sectors,$sectorsattributes);
     $filter_groupelemnts[] =$mform->createElement('autocomplete', 'targetgroup','',$jobfamilies, $jfdattributes);
     $filter_groupelemnts[] =$mform->createElement('autocomplete', 'program_competencylevel','',array(), $clattributes);
     $mform->addGroup($filter_groupelemnts, 'filter_group_elements','', array('class' => 'program_card_filter_groupelemnts', 'id' => 'program_card_filter_groupelemnts'), false);

   /*     $mform->createElement('submit', 'search', get_string('search','local_trainingprogram'), array('class'=> 'searchbtnicon'));*/

  $mform->disable_form_change_checker();        
}
  public function validation($data, $files) {
    global $DB;
    $errors = parent::validation($data, $files);
    return $errors;
  }
}