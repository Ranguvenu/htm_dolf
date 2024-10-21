<?php
// ini_set('memory_limit', '-1');
// define('NO_OUTPUT_BUFFERING', true);

/**
 * TODO describe file programenrollment
 *
 * @package    local_trainingprogram
 * @copyright  2023 YOUR NAME <your@email.com>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
require('../../config.php');
require_once($CFG->dirroot . '/local/trainingprogram/lib.php');
 require_once($CFG->dirroot . '/local/trainingprogram/filter_form.php');
require_once("$CFG->libdir/externallib.php");
require_once($CFG->dirroot.'/course/lib.php');
require_once($CFG->dirroot.'/group/lib.php');
require_once($CFG->dirroot.'/course/externallib.php');
require_once($CFG->dirroot . '/' . $CFG->admin . '/roles/lib.php');
require_once($CFG->dirroot .'/user/selector/lib.php');
global $CFG,$DB,$USER,$PAGE,$OUTPUT,$SESSION;
$systemcontext = context_system::instance();
$programid     = required_param('programid', PARAM_INT);
$offeringid     = required_param('offeringid', PARAM_INT);
$roleid     = optional_param('roleid',0,PARAM_INT);
$lastitem   = optional_param('lastitem',0, PARAM_INT);
$add        = optional_param('add','', PARAM_RAW);
$remove     =optional_param('remove','', PARAM_RAW);
$tuserids=optional_param('tuserids','', PARAM_RAW);

if($roleid == 0){
    $roleid = $DB->get_field('role','id',['shortname'=>'trainee']);
}

require_capability('local/trainingprogram:view',$systemcontext);
if (!empty($tuserids)) {
    $PAGE->requires->js_call_amd('local_trainingprogram/tpview', 'load', ['tuserids' => $tuserids, 'entityid'=>$programid, 'referenceid'=> $offeringid, 'type'=> 'program']);
}
$offeringcode = $DB->get_field('tp_offerings','code',['id'=>$offeringid]);

$program= $DB->get_record('local_trainingprogram', ['id' => $programid], '*', MUST_EXIST);
$course= $DB->get_record('course', ['id' => $program->courseid], '*', MUST_EXIST);
$groupid = $DB->get_field_sql('SELECT g.id FROM {groups} as g JOIN {tp_offerings} as tpo on g.idnumber=tpo.code where tpo.id=:id',array('id'=>$offeringid));
$offering=$DB->get_record('tp_offerings',array('id'=>$offeringid));

$tpobject = new \local_trainingprogram\local\trainingprogram();

$context = context_system::instance();
$trainingprogram = get_string('trainingprogram', 'local_trainingprogram').' '.$program->name;
$PAGE->set_context($systemcontext);
$PAGE->set_title($trainingprogram);
$PAGE->set_heading($trainingprogram.' - '.$offeringcode);
$PAGE->navbar->add(get_string('manage_programs','local_trainingprogram'),new moodle_url('/local/trainingprogram/index.php'));
$PAGE->navbar->add(get_string('enrolled','local_trainingprogram'),new moodle_url('/local/trainingprogram/programenrollment.php?programid='.$programid.'& roleid='.$roleid.'& offeringid='.$offeringid));
$PAGE->set_url(new moodle_url('/local/trainingprogram/programenrollment.php?programid='.$programid.'&roleid='.$roleid.'&offeringid='.$offeringid.''));

$PAGE->requires->jquery();
$PAGE->requires->jquery_plugin('ui');
$PAGE->requires->js('/local/trainingprogram/js/jquery.bootstrap-duallistbox.js',true);
$PAGE->requires->css('/local/trainingprogram/css/bootstrap-duallistbox.css');
$PAGE->set_title(get_string('manualenrol','local_trainingprogram'));
if(!$add && !$remove){
    $PAGE->set_heading($course->fullname.' - '.$offeringcode);
}
require_login();

echo $OUTPUT->header();
if(!is_siteadmin() && $offering->cancelled == 2) {
    $return = $OUTPUT->notification(get_string('offeringhascancelled','local_trainingprogram'),'error');
    $return.=html_writer::start_tag('div', array('class'=> 'w-100 pull-left')).$OUTPUT->continue_button(new moodle_url('/local/trainingprogram/index.php'));
    echo $return;
} elseif(!is_siteadmin() && $offering->financially_closed_status == 1 ){
    $return = $OUTPUT->notification(get_string('offeringfinanciallyclosed','local_trainingprogram'),'error');
    $return.=html_writer::start_tag('div', array('class'=> 'w-100 pull-left')).$OUTPUT->continue_button(new moodle_url('/local/trainingprogram/index.php'));
    echo $return;
} else {
    if ($add) {
        $traineeids = base64_encode(implode(',', $add));
        if (!is_siteadmin() && has_capability('local/organization:manage_organizationofficial', $context)) {
            $returnurl = new moodle_url('programenrollment.php', array('programid'=>$programid, 'roleid' =>$roleid, 'offeringid'=>$offeringid, 'tuserids'=>$traineeids));
            redirect($returnurl);
        } else {
            $result = $tpobject->enrol_program($add, $offeringid,$programid,$offering);
        }
    }
    if ($remove) {
       $result = $tpobject->unenrol_program($remove, $offeringid,$programid,$offering);
    }
    if(($add || $remove) && $result ){
        echo $OUTPUT->notification(get_string('enrolluserssuccess', 'local_trainingprogram',$result),'success');
        $button = new single_button($PAGE->url, get_string('click_continue','local_trainingprogram'), 'get', true);
        $button->class = 'continuebutton';
        echo $OUTPUT->render($button);
        die();
    }

    $availableseats = (new \local_trainingprogram\local\trainingprogram)->get_available_seats($offeringid);

    if(is_siteadmin() || ($availableseats > -1 && $program->price > 0) || ($program->price == 0)){
        $email        = null;
        $filterlist = array('tprogramuseremail', 'tprogramorganizationusers');
        $filterparams = array('options' => null, 'dataoptions' => null);
        $mform = new filter_form($PAGE->url, array('filterlist' => $filterlist, 'programid' =>$programid,'filterparams' => $filterparams, 'action' => 'programuser_enrolment'));
        if ($mform->is_cancelled()) {
            redirect($PAGE->url);
        } else {
            $filterdata =  $mform->get_data();
            if($filterdata){
                $collapse = false;
            } else{
                $collapse = true;
            }
            $search_query = !empty($filterdata->search_query) ? implode(',', $filterdata->search_query) : null;
            $email = !empty($filterdata->email) ? implode(',', $filterdata->email) : null;
            $organizationusers = !empty($filterdata->organizationusers) ? implode(',', $filterdata->organizationusers) : null;
        }
        $options = array('context' => $context->id,
                         'programid' =>$programid,
                         'email' => $email, 
                         'organizationusers' => $organizationusers, 
                         'search_query' => $search_query);
        $users = (new \local_trainingprogram\local\trainingprogram)->program_enrolled_users($offeringid, $options,$perpage=250,$lastitem);

        print_collapsible_region_start(' ', 'filter_form', ' '.' '.get_string('filters'), false, $collapse);
        $mform->display();
        print_collapsible_region_end();

        $offering = local_trainingprogram\local\dataprovider::get_offering($offeringid);
        $tpoffering = local_trainingprogram\local\trainingprogram::get_offering($offering, true, false);


        $tpoffering->orderseats = 
    (!is_siteadmin() &&  has_capability('local/organization:manage_organizationofficial',$systemcontext)) ? (new \tool_product\product)->purchasedseats_check('tp_offerings','id', $offering->id) : (new \tool_product\product)->approvedseats_check('tp_offerings','id', $offering->id) ;
        $tpoffering->approvalseats = (new \tool_product\product)->approvedseats_check('tp_offerings','id', $offering->id);
        $tpoffering->availableseats = $tpobject->get_available_seats($offering->id);
        $traineeeid = $DB->get_field('role', 'id', array('shortname' => 'trainee'));
        $enrolledsql = " SELECT count(userid) AS total
                                  FROM {program_enrollments} AS pe
                                 WHERE pe.offeringid = $offering->id AND pe.roleid=$traineeeid";


        $enrolled = $tpobject->get_erolled_seats($offering->id);
        //$tpoffering->enrolledseats = $tpoffering->approvalseats-$tpoffering->enavailableseats;
        $tpoffering->enrolledseats = $enrolled;
       

        echo '<div id = "programdetailscontainer">';
        echo $OUTPUT->render_from_template('local_trainingprogram/offering', $tpoffering);
        echo '</div>';

        if ($program) {
            $myJSON = json_encode($options);
            $renderer = $PAGE->get_renderer('local_trainingprogram');
            echo $renderer->programenrollment($users['availableuserscount'], $users['enrolledusers'], $users['enrolleduserscount'], $users['availableusers'], 
                                              $myJSON, $programid, $roleid, $offeringid,$availableseats,$program->price, $program->courseid); 
           
        $backurl = new moodle_url('/local/trainingprogram/programenrollment.php?programid='.$programid.'& roleid='.$roleid.'& offeringid='.$offeringid);
        }
    }else{
        echo "<div class='alert alert-danger'>" . get_string('noseatsavailable', 'tool_product') . "</div>";
    }

}
if ($add) {
    $traineeids = base64_encode(implode(',', $add));
    if (!is_siteadmin() && has_capability('local/organization:manage_organizationofficial', $context)) {
        $availableseats = (new \local_trainingprogram\local\trainingprogram)->get_available_seats($offeringid);
        if(sizeof($add) > $availableseats) {
            echo "<div class='alert alert-info'>".get_string('userscountismore', 'local_exams', $availableseats)."</div>";
        } else {
            $returnurl = new moodle_url('programenrollment.php', array('programid'=>$programid, 'roleid' =>$roleid, 'offeringid'=>$offeringid, 'tuserids'=>$traineeids));
            redirect($returnurl);
        }
    } else {
        $result = $tpobject->enrol_program($add, $offeringid,$programid,$offering);
    }
}
if ($remove) {
   $result = $tpobject->unenrol_program($remove, $offeringid,$programid,$offering);
}
if(($add || $remove) && $result ){
    echo $OUTPUT->notification(get_string('enrolluserssuccess', 'local_trainingprogram',$result),'success');
    $button = new single_button($PAGE->url, get_string('click_continue','local_trainingprogram'), 'get', true);
    $button->class = 'continuebutton';
    echo $OUTPUT->render($button);
    die();
}
   

echo $OUTPUT->footer();
