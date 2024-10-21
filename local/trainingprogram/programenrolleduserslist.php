<?php
require_once('../../config.php');
require_login();
global $DB, $CFG, $OUTPUT,$USER, $PAGE;
$systemcontext = context_system::instance();
require_capability('local/trainingprogram:view',$systemcontext);
$PAGE->set_context($systemcontext);
$programid= required_param('programid',PARAM_INT);
$selectedroleid= optional_param('selectedroleid',0,PARAM_RAW);
$offeringid= optional_param('offeringid',0,PARAM_INT);
$programname=$DB->get_record('local_trainingprogram',array('id'=>$programid),'name,namearabic');
$lang= current_language();
if( $lang == 'ar' && !empty($programname->namearabic)){
    $program_name = $programname->namearabic;
}else{
    $program_name = $programname->name;
}
$courseid=$DB->get_field('local_trainingprogram','courseid',array('id'=>$programid));

$PAGE->set_title($program_name);
$PAGE->set_heading($program_name);
$PAGE->set_url('/local/trainingprogram/programenrolleduserslist.php', array('programid' =>$programid,'selectedroleid'=>$selectedroleid,'offeringid'=>$offeringid));
$PAGE->navbar->add(get_string('manage_programs','local_trainingprogram'),new moodle_url('/local/trainingprogram/index.php'));
if($offeringid > 0) {
 $PAGE->navbar->add(get_string('assign_traineeortrainers','local_trainingprogram'),new moodle_url('/local/trainingprogram/program_trainee_trainer_view.php', array('programid' =>$programid,'offeringid'=>$offeringid)));
}
$PAGE->navbar->add(get_string('enrolledusers','local_trainingprogram'), new moodle_url('/local/trainingprogram/programenrolleduserslist.php?programid='.$programid.'&selectedroleid='.$selectedroleid.'&offeringid='.$offeringid));
echo $OUTPUT->header();
 $traineeroleid = $DB->get_field('role', 'id', array('shortname' => 'trainee'));
if(!is_siteadmin() &&  !has_capability('local/organization:manage_trainingofficial',$systemcontext)  &&  !has_capability('local/organization:manage_communication_officer',$systemcontext) && $selectedroleid == $traineeroleid) {

    redirect($CFG->wwwroot);
}

(new local_trainingprogram\local\trainingprogram)->program_enrolled_users_view($programid,$selectedroleid,$offeringid);
echo $OUTPUT->footer();
