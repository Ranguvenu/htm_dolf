<?php
require_once('../../config.php');
global $CFG, $PAGE, $OUTPUT, $DB, $USER;
$eventid = required_param('id', PARAM_INT);

$isuncompletedprofile = $DB->record_exists_sql("SELECT * FROM {local_users} WHERE userid=:userid AND usersource=:usersource AND email = '' AND phone1 = ''",['userid'=>$USER->id,'usersource'=>'IAM']);
if($isuncompletedprofile){
    redirect($CFG->wwwroot.'/local/userapproval/iamregistration.php');
}
$PAGE->set_context(context_system::instance());
$PAGE->set_url('/local/events/alleventsview.php?id='.$eventid);

$PAGE->set_pagelayout('sitefrontpage');
$PAGE->set_title(get_string('pluginname','local_events'));
$lang= current_language();
if( $lang == 'ar'){
    $eventname = $DB->get_field('local_events', 'titlearabic', ['id' => $eventid]);
}else{
    $eventname = $DB->get_field('local_events', 'title', ['id' => $eventid]);
}
$cancelledstatus =(int) $DB->get_field('local_events','cancelled',['id'=>$eventid]);
$PAGE->set_heading($eventname);
$PAGE->navbar->add(get_string('home', 'theme_academy'),new moodle_url('/?redirect=0'));
$PAGE->navbar->add(get_string('allevents','local_events'), new moodle_url('https://fa.gov.sa/ar/services/Pages/Events.aspx'));
$PAGE->navbar->add(get_string('eventdetails', 'local_events')); 
$renderer = $PAGE->get_renderer('local_events');
$content = $renderer->get_eventscontent($eventid);
$PAGE->requires->js_call_amd('local_exams/fav', 'init');
echo $OUTPUT->header();
if($cancelledstatus == 2 && !is_siteadmin()) {
    //throw new moodle_exception(get_string('eventhascancelled','local_events'));
    $return = $OUTPUT->notification(get_string('eventhascancelled','local_events'),'error');
    $return.=html_writer::start_tag('div', array('class'=> 'w-100 pull-left')).$OUTPUT->continue_button(new moodle_url('/local/events/allevents.php'));
    echo $return;
    
} else {
    $event = $renderer->event_check($eventid);
    echo $OUTPUT->render_from_template('local_events/eventview',$content);
}
//echo $content;
echo $OUTPUT->footer();
