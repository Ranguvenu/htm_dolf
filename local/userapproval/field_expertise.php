<?php
use local_userapproval\form\field_expertise_form as  field_expertise_form ;
require_once('../../config.php');
require_once($CFG->dirroot.'/local/userapproval/lib.php');
global $CFG, $PAGE, $OUTPUT, $DB;
require_login();
$id = optional_param('id', 0, PARAM_INT);
$userid= required_param('userid',PARAM_INT);
$systemcontext = context_system::instance();
$PAGE->set_context($systemcontext);

$trainerroleid = (int) $DB->get_field('role','id',['shortname'=>'trainer']);
$expertroleid = (int) $DB->get_field('role','id',['shortname'=>'expert']);

require_capability('local/userapproval:field_expertise_view', $systemcontext);
if(is_siteadmin() || !has_capability('local/organization:manage_trainee', $systemcontext) || ((user_has_role_assignment($userid, $trainerroleid, SYSCONTEXTID) && user_has_role_assignment($userid, $expertroleid, SYSCONTEXTID)))) {
    redirect($CFG->wwwroot);
}
$PAGE->set_url('/local/userapproval/field_expertise.php?id='.$id.'&userid='.$userid.'');
$PAGE->set_title(get_string('fieldofexpertise', 'local_userapproval'));
$PAGE->navbar->add(get_string('manage_users','local_userapproval'),new moodle_url('/local/userapproval/index.php'));
$PAGE->navbar->add(get_string('user_profile', 'local_userapproval'), new moodle_url('/local/userapproval/userprofile.php?id='.$userid));
$PAGE->navbar->add(get_string('fieldofexpertise', 'local_userapproval'), new moodle_url('/local/userapproval/field_expertise.php?id='.$id.'&userid='.$userid.''));
$options= new field_expertise_form(null,array('id'=>$id,'userid'=>$userid)) ;
echo $OUTPUT->header();
if($options->is_cancelled()){
    
    redirect($CFG->wwwroot . '/local/userapproval/listof_allrequests.php?id='.$id.'&userid='.$userid.'');

}
if($data=$options->get_data()){
   
    $fieldexpertise=(new local_userapproval\action\manageuser)->add_field_expertise($data);
    if(is_siteadmin()) {
        $message_url =$CFG->wwwroot.'/local/userapproval/trainer_expert_request.php';
    } else {
        $message_url =$CFG->wwwroot.'/local/userapproval/userprofile.php?id='.$data->userid.'';
    }
     
    if($fieldexpertise){
        $smsdata=[
           'userid'=>$userid,
           'message_title'=>get_string('message_title', 'local_userapproval'),
           'message_body'=>get_string('message_body', 'local_userapproval'),
           'message_url'=>$message_url,
           'message_footer'=>get_string('message_footer', 'local_userapproval'),
        ];
        echo $OUTPUT->render_from_template('local_userapproval/successdialogbox',$smsdata);
    }
}
$options->display();
echo $OUTPUT->footer();
