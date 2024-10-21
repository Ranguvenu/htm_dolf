<?php
// This file is part of Moodle - http://moodle.org/
//
// Moodle is free software: you can redistribute it and/or modify
// it under the terms of the GNU General Public License as published by
// the Free Software Foundation, either version 3 of the License, or
// (at your option) any later version.
//
// Moodle is distributed in the hope that it will be useful,
// but WITHOUT ANY WARRANTY; without even the implied warranty of
// MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
// GNU General Public License for more details.
//
// You should have received a copy of the GNU General Public License
// along with Moodle.  If not, see <http://www.gnu.org/licenses/>.
/**
 * Defines plugin library.
 *
 * @package    local_cpd
 * @copyright  2022 e abyas  <info@eabyas.com>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die();

function local_cpd_leftmenunode(){
    global $DB, $USER;
    $systemcontext = context_system::instance();
    $referralcode = '';
    if(is_siteadmin() ||  has_capability('local/organization:manage_cpd',$systemcontext) || has_capability('local/organization:manage_organizationofficial',$systemcontext) || has_capability('local/organization:manage_trainingofficial',$systemcontext) ){
        $referralcode .= html_writer::start_tag('li', array('id'=> 'id_leftmenu_cpd', 'class'=>'pull-left user_nav_div cpd'));
        $referral_url = new moodle_url('/local/cpd/index.php');
        $referral_label = get_string('pluginname','local_cpd');
        $referral =  html_writer::link($referral_url, '<span class="side_menu_img_icon cpd_icon"></span><span class="user_navigation_link_text">'.$referral_label.'</span>', array('class'=>'user_navigation_link'));
        $referralcode .= $referral;
        $referralcode .= html_writer::end_tag('li');
    } elseif (has_capability('local/organization:manage_trainee',$systemcontext)) {
        $check_exam_completion = $DB->record_exists('exam_completions', ['userid' => $USER->id]);
        if($check_exam_completion) {
            $referralcode .= html_writer::start_tag('li', array('id'=> 'id_leftmenu_cpd', 'class'=>'pull-left user_nav_div cpd'));
            $referral_url = new moodle_url('/local/cpd/index.php');
            $referral_label = get_string('my_cpd','local_cpd');
            $referral =  html_writer::link($referral_url, '<span class="side_menu_img_icon cpd_icon"></span><span class="user_navigation_link_text">'.$referral_label.'</span>', array('class'=>'user_navigation_link'));
            $referralcode .= $referral;
            $referralcode .= html_writer::end_tag('li');
        }
    }
    return array('9' => $referralcode);
}

function cpd_users_filters_form($filterparams) {
    global $CFG;
    require_once($CFG->dirroot . '/local/organization/dynamicfilters_form.php');
    $filters = array(
        'cpd'=> array('local'=>array('userid', 'user_status', 'evidence_status')),
        );
    $mform = new dynamicfilters_form(null, array('filterlist'=>$filters,'filterparams' => $filterparams,'submitid' =>'cpduser','ajaxformsubmit'=>true), 'post', '', null, true,$_REQUEST);
    return $mform;
}

function evidence_status_filter($mform){
    $statusarray = array(0 => get_string('statuspending','local_cpd'), 1 => get_string('approve','local_cpd'), 2 => get_string('statusrejected','local_cpd'));

    $select = array(null => get_string('select','local_cpd'));
    $statuslist = $select + $statusarray;
    $mform->addElement('select',  'status', get_string('statusrequest','local_cpd'), $statuslist);
}

function user_status_filter($mform){
    $statusarray = array(1 => get_string('goodstanding','local_cpd'), 
                         2 => get_string('actionpreferred','local_cpd'), 
                         3 => get_string('requiredcloseattention','local_cpd'),
                         4 => get_string('actionrequired','local_cpd'),
                         5 => get_string('immediaterenewalrequired','local_cpd'));

    $select = array(null => get_string('pleaseselectstatus','local_cpd'));
    $statuslist = $select + $statusarray;
    $mform->addElement('select',  'user_status', get_string('status','local_cpd'), $statuslist);
}

function userid_filter($mform){
    global $DB;
    $cpdid = optional_param('id', 0, PARAM_INT);
    $cpdusers=$DB->get_records_sql("SELECT lu.userid, lu.id_number FROM {local_users} AS lu JOIN {local_cpd_evidence} AS ce ON ce.userid = lu.userid WHERE ce.cpdid = $cpdid");
    $users=[];
    //$tp_organization[''] = get_string('selecttp_organization','local_trainingprogram');
    foreach ($cpdusers AS $cpduser){
        $users[$cpduser->userid]=$cpduser->id_number;
    }
    $sectorelement =$mform->addElement('autocomplete','id_number', get_string('id', 'local_cpd'),$users, ['class' => 'el_id_number']);
    $sectorelement->setMultiple(true);
}

function local_cpd_pluginfile($course, $cm, $context, $filearea, $args, $forcedownload, array $options=array()) {

    if ($context->contextlevel != CONTEXT_SYSTEM) {
        return false;
    }
    if($filearea != 'logo'){
        return false;
    }
    $postid = (int)array_shift($args);
    $fs = get_file_storage();
    $relativepath = implode('/', $args);
    $fullpath = "/$context->id/local_cpd/$filearea/$postid/$relativepath";
    if (!$file = $fs->get_file_by_hash(sha1($fullpath)) or $file->is_directory()) {
        return false;
    }
    // finally send the file
    send_stored_file($file, 0, 0, true, $options); // download MUST be forced - security!
}

