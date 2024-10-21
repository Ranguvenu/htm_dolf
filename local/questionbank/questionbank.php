<?php
require_once(__DIR__ . '/../../config.php');
require_once($CFG->libdir.'/formslib.php');
require_once($CFG->dirroot.'/local/questionbank/lib.php');
require_once($CFG->dirroot.'/question/editlib.php');
global $CFG, $PAGE, $OUTPUT, $DB;
$form_status = optional_param('form_status', -1, PARAM_INT);
$formid      = optional_param('formid', -1, PARAM_INT);
$edit        = optional_param('edit', -1, PARAM_INT);
$status      = optional_param('st', '', PARAM_RAW);
$sesskey     = $_SESSION['USER']->sesskey;

require_login();
$PAGE->requires->js_call_amd('local_hall/hall', 'init');
$PAGE->requires->js_call_amd('local_hall/hallevents', 'init');
$PAGE->set_url(new moodle_url('/local/questionbank/questionbank.php?form_status='.$form_status.'&edit='.$edit.'&formid='.$formid));
$PAGE->set_context(context_system::instance());
$PAGE->set_title(get_string('questionbank', 'local_questionbank'));
if($edit > 0){
 $PAGE->set_heading(get_string('updatequestionbank', 'local_questionbank')); 
}else{
 $PAGE->set_heading(get_string('addquestionbank', 'local_questionbank')); 
}

$PAGE->navbar->add(get_string('questionbank', 'local_questionbank'), new moodle_url('/local/quesstionbank/questionbank.php'));

echo $OUTPUT->header();
$systemcontext = context_system::instance();
if(!(is_siteadmin() || has_capability('local/questionbank:assignreviewer',$systemcontext) || has_capability('local/organization:manage_organizationofficial',$systemcontext) || has_capability('local/questionbank:createquestion',$systemcontext))){
    print_error("You don't have permissions to view this page.");
}
$questionbank = new local_questionbank\form\questionbank_form(null,array('edit'=>$edit,'form_status' => $form_status,'st'=>$status,'id' => $formid, 'entitycode' => $sesskey ));
  
if($formid >0 || $_POST['id'] > 0){
$formid = $formid > 0 ? $formid : $_POST['id'];
$data = $DB->get_record('local_questionbank', array('id'=>$formid));

if(!empty($data->competency)){

            // $competency_info = !empty($_POST['competencylevel']) ? implode(',',$_POST['competencylevel']) : 0;
            //$data->competency = !empty($data->competency) ? $data->competency :   $competency_info;

            $sql = "SELECT cmt.type,cmt.type as fullname FROM {local_competencies} AS cmt WHERE cmt.id IN ($data->competency)";
            $competencietypes=$DB->get_records_sql_menu($sql);
            $currentlang= current_language();
            if( $currentlang == 'ar'){
                $display_name = 'lc.arabicname';
            } else {
                $display_name = 'lc.name';

            }
            $sql_competencies = "SELECT lc.id,$display_name AS fullname FROM {local_competencies} lc  WHERE  lc.id IN ($data->competency) ";
            $competencies=$DB->get_records_sql_menu($sql_competencies);
            $data->competencylevel =  explode(',',$data->competency);

            $data->ctype=$competencietypes;
}
$data->courses = explode(',',$data->course);
$data->startdate = $data->workshopdate;
$data->duration = $data->duration;
$dur_min = $data->workshopstarttime/60;
if($dur_min){
   $hours = floor($dur_min / 60);
   $minutes = ($dur_min % 60);
}
$data->starttime['hours'] = $hours;
$data->starttime['minutes'] = $minutes;    
$questionbank->set_data($data);

}
if($questionbank->is_cancelled()){
    (new local_hall\hall)->remove_reservation($sesskey, 'questionbank');
   
   redirect($CFG->wwwroot . '/local/questionbank/index.php?c=1');
}

if($data = $questionbank->get_data()){
  $insert = new questionbank();
  if($data->edit_form > 0){
    $formid = $insert->update_questionbank($data);
    $edit = $formid;
  }else{
    $formid = $insert->create_questionbank($data);
  }
  $form_status = $data->form_status + 1;
  if( $form_status < 2){
    redirect(new moodle_url('/local/questionbank/questionbank.php?edit='.$edit.'&form_status='.$form_status.'&st=new&formid='.$formid));
  }else{
    redirect($CFG->wwwroot . '/local/questionbank/questionbank_workshop.php?id='.$formid);
  }
}else{
    $questionbank->display();
}
echo $OUTPUT->footer();
