<?php
require_once('../../config.php');
require_once($CFG->libdir . '/badgeslib.php');
global $CFG, $PAGE, $OUTPUT, $DB;
$examid = required_param('examid', PARAM_INT);
$profileid = required_param('profileid', PARAM_INT);
$organization = required_param('organization', PARAM_INT);
$orgofficial = required_param('orgofficial', PARAM_INT);
$cusers = required_param('cusers', PARAM_RAW);
$scheduleid = required_param('scheduleid', PARAM_INT);

$systemcontext = context_system::instance();
$PAGE->set_context($systemcontext);
require_login();
$PAGE->set_url('/local/exams/enrollmentconfirmation.php', ['examid'=> $examid, 'profileid' => $profileid,'organization' => $organization, 'orgofficial' => $orgofficial,'cusers' => $cusers,'scheduleid'=>$scheduleid]);
$PAGE->set_title(get_string('exams', 'local_exams'));
$PAGE->set_heading(get_string('enrollmentconfirmation', 'local_exams'));
if( !is_siteadmin() && !has_capability('local/organization:manage_examofficial', $systemcontext) && !has_capability('local/organization:manage_organizationofficial', $systemcontext)){
    throw new required_capability_exception($systemcontext, 'local/exams:manage_examofficial', 'nopermissions', '');
}
$PAGE->navbar->add(get_string('pluginname', 'local_exams'), new moodle_url('/local/exams/index.php'));
$PAGE->navbar->add(get_string('bulkenrollment', 'local_exams'), new moodle_url('/local/exams/bulk_enrollment.php', ['examid'=> $examid, 'profileid' => $profileid]));
$PAGE->navbar->add(get_string('enrollmentconfirmation', 'local_exams'));
$renderer = $PAGE->get_renderer('local_exams');
$PAGE->requires->js_call_amd('local_exams/bulkenrollment', 'init');
echo $OUTPUT->header();
(new local_exams\local\exams)->enrollmentconfirmation($examid,$profileid,$organization,$orgofficial,$cusers,$scheduleid);
echo $OUTPUT->footer();
