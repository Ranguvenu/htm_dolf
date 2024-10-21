<?php
require('../../config.php');
require_once($CFG->dirroot . '/local/exams/lib.php');
require_once($CFG->dirroot . '/local/exams/filters_form.php');
require_once("$CFG->libdir/externallib.php");
require_once($CFG->dirroot.'/course/lib.php');
require_once($CFG->dirroot.'/course/externallib.php');
global $CFG,$DB,$USER,$PAGE,$OUTPUT,$SESSION;

$exam_id        = required_param('examid', PARAM_INT);
$profileid     = optional_param('profileid', 0,PARAM_INT);
$scheduleid     = optional_param('scheduleid', 0,PARAM_INT);

$add            = optional_param('add',array(), PARAM_RAW);
$options        = optional_param('options',array(), PARAM_RAW);
$hallschedule = $DB->get_record('hallschedule', ['id' => $scheduleid]);
$remove         =optional_param('remove',array(), PARAM_RAW);

$exam           = $DB->get_record('local_exams', ['id' => $exam_id], '*', MUST_EXIST);
$course         = $DB->get_record('course', ['id' => $exam->courseid], '*', MUST_EXIST);
$context        = context_system::instance();

$backurl = new moodle_url('/local/exams/examenrollment.php', ['examid'=> $exam->id, 'profileid' => $profileid]);
$PAGE->set_context($context);
$PAGE->set_url($backurl);
$PAGE->navbar->add(get_string('exams','local_exams'),new moodle_url('/local/exams/index.php'));
$lang= current_language();
if( $lang == 'ar'){
    $exam->examname=$exam->examnamearabic;
}else{
    $exam->examname=$exam->exam;           
} 
$PAGE->navbar->add($exam->examname ,new moodle_url('/local/exams/examdetails.php?id='.$exam->id));
$PAGE->navbar->add(get_string('examenrolment','local_exams') ,new moodle_url('/local/exams/examenrollment.php?examid='.$exam->id.'&hallreservationid='.$hallreservationid));
$PAGE->requires->jquery();
$PAGE->requires->jquery_plugin('ui');
$PAGE->requires->js('/local/exams/js/jquery.bootstrap-duallistbox.js',true);
$PAGE->requires->css('/local/exams/css/bootstrap-duallistbox.css');
$PAGE->set_title(get_string('examenrolment','local_exams').': '. $exam->examname);

if(!$add && !$remove){
    $PAGE->set_heading($exam->examname);
}
$PAGE->requires->js_call_amd('theme_academy/cardPaginate', 'filteringData', array($context));

require_login();
$examobject = new \local_exams\local\exams();

echo $OUTPUT->header();

if(is_siteadmin() || has_capability('local/organization:manage_organizationofficial',$context) || has_capability('local/organization:manage_examofficial',$context) || has_capability('local/exams:manage_profile_enrolments',$context)) {
    $params = ['examid' => $exam_id, 'profileid' => $profileid, 'scheduleid' => $scheduleid];
    if ($add) {
        if (!is_siteadmin() && has_capability('local/organization:manage_organizationofficial', $context)) {
            foreach($add as $userid){
                $product = $DB->get_record('tool_products', ['category'=>2, 'referenceid'=>$profileid]);
                $userreq = (new \tool_product\product)->userprofilecountry((array)$product, $userid);
    
                if (!empty($userreq)) {
                    $userswithnoreq = true;
                    $username = $DB->get_field('user', 'email', ['id' => $userid]);
                    echo $OUTPUT->notification(get_string('userreqnotmeet', 'local_exams', $username),'danger');
                }
            }
            if (empty($userswithnoreq)) {
                $traineeids = base64_encode(implode(',', $add));
                $returnurl = new moodle_url('/local/exams/hallschedule.php', array('examid'=>$exam_id, 'profileid' =>$profileid, 'tuserid'=>$traineeids));
                $totalseats = $DB->get_field('local_exam_profiles','seatingcapacity',['id' => $profileid]);
                if($totalseats > 0) {
                    $availableseats = (new \local_exams\local\exams)->get_available_seats($profileid);
                    if(sizeof($add) > $availableseats) {
                        echo "<div class='alert alert-info'>".get_string('userscountismore', 'local_exams', ($availableseats<0)?0:$availableseats)."</div>";
                    } else {
                        redirect($returnurl);
                    }
                } else {
                    redirect($returnurl);
                }
            }
        } else {
            $examobject->enrol_exam($add, $profileid, $params);
        }
    }
    if ($remove) {
        $examobject->unenrol_exam($remove, $profileid, $params);
    }
    $current_user_role = new stdClass();
    if (!is_siteadmin()) {
        $current_user_role = $examobject->get_user_role($USER->id);
        
    }else{
        $current_user_role->shortname = 'manager';
        $showusers = true;
    }

    if ($exam) {
        $email        = null;
        if ($current_user_role->shortname == 'assessmentop') {
            // $showusers = 
            $filterlist = array('organizationusers');
        }else{
            $filterlist = array('useremail', 'organizationusers');
        }
        $filterparams = array('options' => null, 'dataoptions'=>null);

        $mform = new filters_form($PAGE->url, array('filterlist'=>$filterlist,'examid' =>$exam->id, 'hallid' => $hallschedule->hallid, 'examdate' => $hallschedule->startdate, 'hallreservationid' => $hallreservationid,  'filterparams' => $filterparams, 'action' => 'examuser_enrolment', $current_user_role->shortname));
        if ($mform->is_cancelled()) {
            redirect($PAGE->url);
        } else {
            if($filterdata =  $mform->get_data()){
                if (!is_array($filterdata->organizationusers)) {
                    $filterdata->organizationusers = [$filterdata->organizationusers];
                }
                $collapse = false;
                $email = !empty($filterdata->email) ? implode(',', $filterdata->email) : null;
                $organizationusers = !empty($filterdata->organizationusers) ? implode(',', $filterdata->organizationusers) : null;
                
                $halls = !empty($filterdata->halls) ? $filterdata->halls : null;
                $examdate = !empty($filterdata->examdate) ? $filterdata->examdate : null;
            }else{
                $collapse = true;
            }
        }
        $options = array(
            'context' => $context->id,
            'examid' => $exam->id,
            'email' => $email,
            'organizationusers' => $organizationusers,
            'profileid' => $profileid,
            'scheduleid' => $scheduleid,
            'examdate' => $examdate,
            'halls' => $halls
        );

        $select_to_users = $examobject->exam_enrolled_users('add', $profileid, $options);

        $select_to_userstotal = $examobject->exam_enrolled_users_count('add', $profileid, $options);

        $select_from_users = $examobject->exam_enrolled_users('remove', $profileid, $options);
        $select_from_userstotal = $examobject->exam_enrolled_users_count('remove', $profileid, $options);
        foreach ($select_from_users as $key => $value) {
          $data['id'] = $key;
          $data['value'] = $value;
          $fromusers[] = $data;
        }

        foreach ($select_to_users as $key => $value) {
          $data['id'] = $key;
          $data['value'] = $value;
          $tousers[] = $data;
        }
    }
    
    if ($current_user_role->shortname == 'assessmentop') {
        $collapse = false;
        $canremove_user = true;
        if ($exam->ownedby == 'CISI') {
            \core\notification::warning(get_string('cannotbeenrolled', 'local_exams'));
            $collapse = true;
        }
    }
    print_collapsible_region_start(' ', 'filter_form', ' '.' '.get_string('filters'), false, $collapse);
    $mform->display();
    print_collapsible_region_end();
    
    echo html_writer::div('', '', ['id' => 'currentuserrole', 'style' => 'display:none', 'data-value' => $current_user_role->shortname]);

    $enrolledseats = $DB->count_records('local_exam_userhallschedules', ['profileid' => $profileid]);
    $profileseats = $DB->get_field('local_exam_profiles', 'seatingcapacity', ['id'=>$profileid]);

    if (!$examobject->get_user_role($USER->id)->shortname == 'assessmentop') {
        $availableseats = $examobject->availableseats($profileid);
    }else{
        $availableseats = $profileseats;
    }
    $seat_reserved_already = count($examobject->exam_enrolled_users('remove', $profileid, $params));
    if ($seat_reserved_already >= $availableseats) {
        $availableseats = 0;
    }else{
        $availableseats = $availableseats - $seat_reserved_already;
    }
    
    if ($current_user_role->shortname == 'assessmentop' && !$filterdata->organizationusers) {
        $showusers == false;
    }
    if ($exam) {

        $myJSON = json_encode($options);
        $renderer = $PAGE->get_renderer('local_exams');
        $renderer->examenrollment($select_from_userstotal, $fromusers, $select_to_userstotal, $tousers, $myJSON, $exam->id,$availableseats,$exam->examprice, $profileid, $showusers, $options['organizationusers']);
    }
    $backurl = new moodle_url('/local/exams/index.php');
} else {
    throw new required_capability_exception($context, 'local/exams:manage_profile_enrolments', 'nopermissions', '');
}
$PAGE->requires->js_call_amd('local_exams/examenrol', 'init');

echo $OUTPUT->footer();
