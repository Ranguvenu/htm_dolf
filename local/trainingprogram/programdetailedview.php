<?php
require_once(dirname(__FILE__) . '/../../config.php');
global $DB, $CFG, $OUTPUT,$USER, $PAGE;
$systemcontext = context_system::instance();
$PAGE->set_context($systemcontext);
$programid= optional_param('programid',0,PARAM_INT);
$current_sys_lang = current_language();
$is_published = $DB->get_field('local_trainingprogram','published',array('id'=>$programid));
if(!isloggedin() && $is_published == 0 || (isloggedin() && !is_siteadmin() && !has_capability('local/organization:manage_trainingofficial', $systemcontext) && $is_published == 0)) {
	redirect($CFG->wwwroot);
}
if($current_sys_lang == 'ar') {

	$sql = "SELECT namearabic FROM {local_trainingprogram} WHERE id = $programid";
} else {

	$sql = "SELECT name FROM {local_trainingprogram} WHERE id = $programid";
}
$programname = $DB->get_field_sql($sql);
$PAGE->set_title(get_string('program_view','local_trainingprogram'));
$courseid=$DB->get_field('local_trainingprogram','courseid',array('id'=>$programid));
$PAGE->set_url('/local/trainingprogram/programdetailedview.php', array('programid' =>$programid));
$returnurl = new moodle_url('/local/trainingprogram/programdetailedview.php', array('programid' =>$programid));
echo $OUTPUT->header();
(new local_trainingprogram\local\trainingprogram)->detailed_program_course_overview($programid,$courseid);
echo $OUTPUT->footer();
