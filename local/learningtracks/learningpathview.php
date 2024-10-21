<?php
require_once('../../config.php');
global $CFG, $PAGE, $OUTPUT, $DB,$USER;
require_login();
$systemcontext = context_system::instance();
$id = required_param('id', PARAM_INT);
$userid = optional_param('userid',0,PARAM_INT);

$PAGE->set_context(context_system::instance());
$renderer = $PAGE->get_renderer('local_learningtracks');
$track = $renderer->track_check($id);

$PAGE->set_title(get_string('mylearningtracks','local_learningtracks'));

if($userid == 0){

    $PAGE->set_url('/local/learningtracks/learningpathview.php', array('id' =>$id));
    $PAGE->set_heading(get_string('viewitems','local_learningtracks'));

    $PAGE->navbar->add(get_string('mylearningtracks','local_learningtracks'),new moodle_url('/local/learningtracks/learningpath.php'));

}else{

    $PAGE->set_url('/local/learningtracks/learningpathview.php', array('id' =>$id,'userid'=>$userid));
    $localuserrecord = $DB->get_record('local_users',['userid'=>$userid]);
    $userfullname = ($localuserrecord)? ((current_language() == 'ar') ? $localuserrecord->firstnamearabic.' '.$localuserrecord->middlenamearabic.' '.$localuserrecord->thirdnamearabic.' '.$localuserrecord->lastnamearabic  : $localuserrecord->firstname.' '.$localuserrecord->middlenameen.' '.$localuserrecord->thirdnameen.' '.$localuserrecord->lastname) :fullname($DB->get_record('user',['id'=>$userid]));

    $PAGE->set_heading(get_string('viewuseritems','local_learningtracks',$userfullname));

    $PAGE->navbar->add(get_string('learning_track_details', 'local_learningtracks'), new moodle_url('/local/learningtracks/view.php?id='.$id));

}

if($userid == 0){

    $userid=$USER->id;

}
$PAGE->navbar->add(get_string('learning_track_details', 'local_learningtracks'), new moodle_url('/local/learningtracks/learningpathview.php?id='.$id.'&userid='.$userid));
$content = $renderer->get_mytrackview($id,$userid);
echo $OUTPUT->header();
echo $content;
echo $OUTPUT->footer();
