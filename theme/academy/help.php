<?php
require_once('../../config.php');
global $DB,  $OUTPUT, $PAGE, $USER;
$systemcontext = context_system::instance();
$gettraineeidnumber = $DB->get_record('course_modules',array('idnumber'=>"help"));
$getorgofficialidnumber = $DB->get_record('course_modules',array('idnumber'=>"org_help"));
$url =  $CFG->wwwroot.'/mod/resource/view.php?id='.$getidnumber->id.'&forceview=1';
$traineeroleid = $DB->get_field('role', 'id', array('shortname' => 'trainee'));
$orgofficialroleid = $DB->get_field('role', 'id', array('shortname' => 'organizationofficial'));

$PAGE->set_context($systemcontext);
$PAGE->set_title(get_string('help', 'theme_academy'));
$PAGE->set_url('/theme/academy/help.php');
$PAGE->set_heading(get_string('help', 'theme_academy'));
$PAGE->navbar->add(get_string('home', 'theme_academy'),new moodle_url('/?redirect=0'));
$PAGE->navbar->add(get_string('help', 'theme_academy'), new moodle_url('/theme/academy/help.php'));
$PAGE->set_pagelayout('frontvideospage');
echo $OUTPUT->header();

$data = [

	'traineedocumenturl' => $CFG->wwwroot.'/mod/resource/view.php?id='.$gettraineeidnumber->id.'&forceview=1',
	'orgofficialdocumenturl' => $CFG->wwwroot.'/mod/resource/view.php?id='.$getorgofficialidnumber->id.'&forceview=1',
	'isloggedin' => isloggedin() ? true : false,
	'organizationofficial' => (!is_siteadmin() && user_has_role_assignment($USER->id,$orgofficialroleid,$systemcontext->id)) ? true : false,

];

echo $OUTPUT->render_from_template('theme_academy/frontvideospage',$data);
echo $OUTPUT->footer();
