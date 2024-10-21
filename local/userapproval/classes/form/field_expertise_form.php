<?php
namespace local_userapproval\form;
use moodleform ;
use moodle_url;
use context;
use context_system;
use html_writer;
require_once("$CFG->libdir/formslib.php");
class field_expertise_form extends  \moodleform{
  public function definition() {
    global $CFG,$DB;
    $mform = $this->_form;
    $id = $this->_customdata['id'];
    $userid = $this->_customdata['userid'];
       
    $mform->addElement('hidden', 'id',$id);
    $mform->setType('id', PARAM_INT);   

    $mform->addElement('hidden', 'userid',$userid);
    $mform->setType('userid', PARAM_INT);  


    $trainerroleid = (int) $DB->get_field('role','id',['shortname'=>'trainer']);
    $expertroleid = (int) $DB->get_field('role','id',['shortname'=>'expert']);

    $traineerequest = $DB->record_exists_sql("SELECT id FROM {local_trainer_request} WHERE userid = $userid AND (status = 1 OR status=2)");  
    $expertrequest = $DB->record_exists_sql("SELECT id FROM {local_expert_request} WHERE userid = $userid AND (status = 1 OR status=2)");
   
    if(!$traineerequest &&  !$expertrequest){  
       

        if(!user_has_role_assignment($userid, $trainerroleid, SYSCONTEXTID) && !user_has_role_assignment($userid, $expertroleid, SYSCONTEXTID)) {
      
              $attributes = array('0' => 'training/coaching','1' => 'expert');
              $radioarray=array();
              $radioarray[] =$mform->createElement('html','<h5>');
              $radioarray[] = $mform->createElement('radio', 'fieldexpertisetype', '', get_string('training_auth','local_userapproval'), 0, $attributes);
              $radioarray[] =$mform->createElement('html','</h5>');
              $radioarray[] =$mform->createElement('html','<h5>');
              $radioarray[] = $mform->createElement('radio', 'fieldexpertisetype', '', get_string('expert_auth','local_userapproval'), 1, $attributes);
              $radioarray[] =$mform->createElement('html','</h5>');
              $mform->addGroup($radioarray, 'fieldexpertisetype','', array('class' => 'fieldexpertisetype'), false); 
      
      }

    } elseif(!$traineerequest || user_has_role_assignment($userid, $expertroleid, SYSCONTEXTID)) {
         
              $mform->addElement('html', \html_writer::tag('h5', get_string('training_auth', 'local_userapproval'),array('class' => '')));

              $mform->addElement('hidden', 'fieldexpertisetype',0);
              $mform->setType('int', PARAM_INT);

    } elseif(!$expertrequest || user_has_role_assignment($userid, $trainerroleid, SYSCONTEXTID)) {

              $mform->addElement('html', \html_writer::tag('h5', get_string('expert_auth', 'local_userapproval'),array('class' => '')));

              $mform->addElement('hidden', 'fieldexpertisetype',1);
              $mform->setType('int', PARAM_INT);

              
    } 


  
    $mform->addElement('filepicker', 'qualification', get_string('qualification','local_userapproval'), null,
      array('maxbytes' => $maxbytes, 'accepted_types' => '*'));
    $mform->addRule('qualification', get_string('file'), 'required');

    $mform->addElement('text', 'certificates',  get_string('certificates', 'local_userapproval'));                    
    $mform->setType('certificates', PARAM_NOTAGS); 
    $mform->setDefault('text','');
    $mform->addRule('certificates', get_string('certificates','local_userapproval'), 'required');
    $mform->addRule('certificates', get_string('certificates_numeric','local_userapproval'), 'numeric');

    $yearsofexperience = array(null=>get_string('select_yearsofexperience','local_userapproval'),'5'=>'5','10'=>'10','15'=>'15','20'=>'20','25'=>'25');
    $mform->addElement('select', 'yearsofexperience', get_string('yearsofexperience','local_userapproval'),$yearsofexperience);
    $mform->addRule('yearsofexperience', get_string('erroryearsofexperience','local_userapproval'), 'required');
    $mform->setType('yearsofexperience', PARAM_RAW);
                                            
    // $fieldoftraining= array(null=>get_string('select_fieldoftraining','local_userapproval'),'5'=>'5','10'=>'10','15'=>'15','20'=>'20','25'=>'25');
    $mform->addElement('text', 'fieldoftraining', get_string('fieldoftraining','local_userapproval'),/*$fieldoftraining*/);
    $mform->setType('fieldoftraining', PARAM_RAW);
    $mform->hideIf('fieldoftraining', 'fieldexpertisetype', 'eq', 1);  

    $mform->addElement('textarea', 'training_programs', get_string("training_programs",'local_userapproval'), 'wrap="virtual" rows="10" cols="20"');
    $mform->hideIf('training_programs', 'fieldexpertisetype', 'eq', 1);  

    $mform->addElement('text', 'fieldofexperience', get_string('fieldofexperience','local_userapproval'),/*$fieldoftraining*/);
    $mform->setType('fieldofexperience', PARAM_RAW);
    $mform->hideIf('fieldofexperience', 'fieldexpertisetype', 'eq', 0);  



    $this->add_action_buttons();

  }

  function validation($data, $files) {
    global $DB;
    $errors = parent::validation($data, $files);
    $data= (object) $data;
    if($data->fieldexpertisetype == 0) {
      if(empty($data->fieldoftraining)){
        $errors['fieldoftraining']=get_string('requiredfot','local_userapproval');
      }
    }
    if(($data->fieldexpertisetype == 1)){
      if(empty($data->fieldofexperience)){
        $errors['fieldofexperience']=get_string('requiredfoe','local_userapproval');
      }
    }
    return $errors;
  }
}
