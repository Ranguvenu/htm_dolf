<?php
require_once('../../config.php');

global $CFG, $PAGE, $OUTPUT, $DB;
$systemcontext = context_system::instance();
require_capability('local/userapproval:view', $systemcontext);
$PAGE->set_context($systemcontext);
$PAGE->set_url('/local/userapproval/trainer_expert_request.php');
$PAGE->set_title(get_string('trainer_expert_request', 'local_userapproval'));
if(is_siteadmin()){
$PAGE->set_heading(get_string('trainer_expert_request', 'local_userapproval'));
}

else if(has_capability('local/organization:manage_trainingofficial',$systemcontext)){
$PAGE->set_heading(get_string('trainerrequest', 'local_userapproval'));
}

else if(has_capability('local/organization:manage_examofficial',$systemcontext)){
 $PAGE->set_heading(get_string('expertrequest', 'local_userapproval'));
}

$PAGE->navbar->add(get_string('trainer_expert_request', 'local_userapproval'), new moodle_url('/local/userapproval/index.php'));
$renderer=$PAGE->get_renderer('local_userapproval');
$mode = optional_param('mode', 1, PARAM_INT);
echo $OUTPUT->header();



if(is_siteadmin()){
    $selectedtab = $mode;
    if($mode==2 ){
        $selectedtab = 2;  
    }
    else{
        $selectedtab = 1; 
    }
    $tabs = array();

    $tabs[] = new tabobject(1, new moodle_url('/local/userapproval/trainer_expert_request.php',array('mode'=>1)), get_string('requests', 'local_userapproval'));
    $tabs[] = new tabobject(2, new moodle_url('/local/userapproval/trainer_expert_request.php',array('mode'=>2)), get_string('assignbyadmin', 'local_userapproval'));
    echo $OUTPUT->tabtree($tabs, $selectedtab);
    
    if($mode==2){
        (new local_userapproval\action\manageuser)->assign_trainerexpert_byadmin();
    } else {
        (new local_userapproval\action\manageuser)->trainer_expert_request_data();
    }
} else {
    if(has_capability('local/organization:manage_trainingofficial',$systemcontext) || has_capability('local/organization:manage_examofficial',$systemcontext)){
        (new local_userapproval\action\manageuser)->trainer_expert_request_data();
    }
}
echo $OUTPUT->footer();
