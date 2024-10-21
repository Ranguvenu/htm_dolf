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
 * @package    local_events
 * @copyright  2022 e abyas  <info@eabyas.com>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */


defined('MOODLE_INTERNAL') || die();
 function events_filters_form($filterparams) {
    global $CFG;
    require_once($CFG->dirroot . '/local/organization/dynamicfilters_form.php');
     $systemcontext = context_system::instance();
    if (!is_siteadmin() && (has_capability('local/events:manage', context_system::instance())|| has_capability('local/organization:manage_event_manager', context_system::instance()))) {
        $filters = array(
            'events'=> array('local'=>array('event_status','event_date','event_speaker','event_method','event_audi_gender','event_reg_date', 'event_lang', 'event_type','event_manager')),
            );
    } 
     else if(!is_siteadmin() && has_capability('local/organization:manage_trainee',$systemcontext) || !is_siteadmin() && has_capability('local/organization:manage_organizationofficial',$systemcontext))  {
        $filters = array(
            'events'=> array('local'=>array('event_status','event_date','event_speaker','event_method','event_audi_gender','event_reg_date', 'event_lang', 'event_type','event_favs')),
            );
    }

    else {
        $filters = array(
            'events'=> array('local'=>array('event_status','event_date','event_speaker','event_method','event_audi_gender','event_reg_date', 'event_lang', 'event_type')),
            );
    }
    $mform = new dynamicfilters_form(null, array('filterlist'=>$filters,'filterparams' => $filterparams,'submitid' =>'events','ajaxformsubmit'=>true), 'post', '', null, true,$_REQUEST);
    return $mform;
}

function events_front_filters_form($filterparams) {
    global $CFG;
    require_once($CFG->dirroot . '/local/organization/dynamicfilters_form.php');
    $filters = array(
        'events'=> array('local' => array('event_method','event_audi_gender','event_lang','event_type', 'event_date')),
        );
    $mform = new dynamicfilters_form(null, array('filterlist'=>$filters,'filterparams' => $filterparams,'submitid' =>'events','ajaxformsubmit'=>true, 'disablebuttons' => 1), 'post', '', null, true,$_REQUEST);
    return $mform;
}

function event_manager_filter($mform) {
    $optionarray = array(1 => get_string('assignme', 'local_events'), 2 => get_string('others', 'local_events'));

    $select = array(null => get_string('select','local_events'));
    $optionslist = $select + $optionarray;
    $mform->addElement('select',  'eventmanager', get_string('eventmanager','local_events'), $optionslist); 
}
function event_status_filter($mform){
    $statusarray = array(0 => get_string('active', 'local_events'),
                            1 => get_string('inactive', 'local_events'),
                            2 => get_string('cancelled', 'local_events'),
                            3 => get_string('closed', 'local_events'),
                            4 => get_string('archieved', 'local_events'));

    $select = array(null => get_string('select','local_events'));
    $statuslist = $select + $statusarray;
    $mform->addElement('select',  'eventstatus', get_string('status','local_events'), $statuslist);
}

function event_date_filter($mform) {
    $mform->addElement('date_selector', 'startdate', get_string('fromdate','local_events'),array('optional'=>true));
    $mform->setType('startdate', PARAM_RAW);
    $mform->addElement('date_selector', 'enddate', get_string('todate','local_events'),array('optional'=>true));
    $mform->setType('enddate', PARAM_RAW);
}

function event_speaker_filter($mform) {
    global $DB;
   /* $users_sql = "SELECT u.id,concat(u.firstname,' ',u.lastname) as fullname FROM {user} u
        WHERE  u.id > 2 AND u.suspended = 0 AND u.deleted = 0 ";*/
    $users = $DB->get_records_sql_menu("SELECT id, name AS fullname FROM {local_speakers}");

    $select = array(null => get_string('select','local_events'));
    $userslist = $select + $users;
    $mform->addElement('select','speaker', get_string('speakers', 'local_events'), $userslist);
}

function event_method_filter($mform) {
    $radioarray = array();
    $methodarray = array(1 => get_string('onsite', 'local_events'),
    2 =>get_string('virtual', 'local_events'));
   // $radioarray[] = $mform->createElement('radio', 'method', '', get_string('onsite', 'local_events'), 1);
    //$radioarray[] = $mform->createElement('radio', 'method', '', get_string('virtual', 'local_events'), 2);
    //$mform->addGroup($radioarray, 'radioar', get_string('eventmethod', 'local_events'), array(' '), false);
    $select = array(null => get_string('eventmethod','local_events'));
    $methodlist = $select + $methodarray;
    $mform->addElement('select','method', get_string('eventmethod','local_events'), $methodlist);
}

function event_audi_gender_filter($mform) {
    $radioarray = array();
    /*$radioarray[] = $mform->createElement('radio', 'audiencegender', '', get_string('male', 'local_events'), 1);
    $radioarray[] = $mform->createElement('radio', 'audiencegender', '', get_string('female', 'local_events'),2);
    $mform->addGroup($radioarray, 'radioar', get_string('gender', 'local_events'), array(' '), false);*/
    $genderarray = array(1 => get_string('male', 'local_events'),
    2 =>get_string('female', 'local_events'));
    $select = array(null => get_string('gender','local_events'));
    $gengerlist = $select + $genderarray;
    $mform->addElement('select','audiencegender', get_string('gender','local_events'),$gengerlist);
}

function event_reg_date_filter($mform) {
    $mform->addElement('date_selector', 'registrationstart', get_string('reg_startdate','local_events'),array('optional'=>true));
    $mform->setType('registrationstart', PARAM_RAW);
    $mform->addElement('date_selector', 'registrationend', get_string('reg_enddate','local_events'),array('optional'=>true));
    $mform->setType('registrationend', PARAM_RAW);
}

function event_lang_filter($mform) {
    /*$radioarray = array();
    $radioarray[] = $mform->createElement('radio', 'language', '', get_string('arabic', 'local_events'), 1);
    $radioarray[] = $mform->createElement('radio', 'language', '', get_string('english', 'local_events'), 2);
    $mform->addGroup($radioarray, 'radioar', get_string('language', 'local_events'), array(' '), false);*/
    $langarray = array(1 => get_string('arabic', 'local_events'),
    2 =>get_string('english', 'local_events'));
    $select = array(null => get_string('language','local_events'));
    $langlist = $select + $langarray;
    $mform->addElement('select',  'language', get_string('language','local_events'), $langlist);
}

function event_type_filter($mform) {
    $typearray = array(0 => get_string('symposium','local_events'),
    1 => get_string('forum','local_events'),
    2 => get_string('conference','local_events'),
    3 => get_string('workshop','local_events'),
    4 => get_string('cermony','local_events'));
    $select = array(null => get_string('eventtype','local_events'));
    $typelist = $select + $typearray;
    $mform->addElement('select',  'type', get_string('eventtype','local_events'), $typelist);
    $mform->setType('eventtype', PARAM_INT);
}
function event_favs_filter($mform){
    global $DB,$USER;
        $mform->addElement('advcheckbox', 'favourites', get_string('favourites', 'local_exams'),  array(), array(0, 1));
}
function attendees_filters_form($filterparams) {
    global $CFG;
    require_once($CFG->dirroot . '/local/organization/dynamicfilters_form.php');
    $filters = array(
        'events'=>array('local'=> array('attendee_status'))
        );
    $mform = new dynamicfilters_form(null, array('filterlist'=>$filters,'filterparams' => $filterparams,'submitid' =>'viewattendees','ajaxformsubmit'=>true), 'post', '', null, true,$_REQUEST);
    return $mform;
}


function attendee_status_filter($mform) {
    $radioarray = array();
    $radioarray[] = $mform->createElement('radio', 'status', '', get_string('approved', 'local_events'), 2);
    $radioarray[] = $mform->createElement('radio', 'status', '', get_string('rejected', 'local_events'), 3);
    $mform->addGroup($radioarray, 'radioar', get_string('status', 'local_events'), array(' '), false);
    $mform->setType('radioar', PARAM_INT);
}
function local_events_leftmenunode(){
    global $DB, $USER;
    $systemcontext = context_system::instance();
    $referralcode = '';
     if(is_siteadmin() || has_capability('local/organization:manage_event_manager',$systemcontext) || has_capability('local/organization:manage_trainee',$systemcontext) || has_capability('local/organization:manage_organizationofficial',$systemcontext) || has_capability('local/organization:manage_communication_officer',$systemcontext)){
        $referralcode .= html_writer::start_tag('li', array('id'=> 'id_leftmenu_events', 'class'=>'pull-left user_nav_div events'));
        $check_event = $DB->record_exists('local_event_attendees', ['userid' => $USER->id]);
        if(!is_siteadmin() && has_capability('local/organization:manage_trainee',$systemcontext)) {
            if($check_event) {
                $referral_url = new moodle_url('/local/events/index.php');
                $referral_label = get_string('myevents','local_events');
                $referral =  html_writer::link($referral_url, '<span class="side_menu_img_icon events_icon"></span><span class="user_navigation_link_text">'.$referral_label.'</span>', array('class'=>'user_navigation_link'));
                $referralcode .= $referral;
            }
        } else {
            if(is_siteadmin() || !has_capability('local/organization:manage_eventmanager',$systemcontext)) {
                $referral_url = new moodle_url('/local/events/index.php');
                $referral_label = get_string('pluginname','local_events');
                $referral =  html_writer::link($referral_url, '<span class="side_menu_img_icon events_icon"></span><span class="user_navigation_link_text">'.$referral_label.'</span>', array('class'=>'user_navigation_link'));
                $referralcode .= $referral;
            }
        }
        $referralcode .= html_writer::end_tag('li');
    }
    return array('6' => $referralcode);
}


function sponsorlogo_url($itemid = 0) {
    global $DB;
    $context = context_system::instance();
    if ($itemid > 0) {
        $sql = "SELECT * FROM {files} WHERE itemid = :logo AND filearea='eventimage' AND filename != '.' ORDER BY id DESC";
        $sponsorlogorecord = $DB->get_record_sql($sql,array('logo' => $itemid),1);
    }
    if (!empty($sponsorlogorecord)) {
        $logourl = moodle_url::make_pluginfile_url($sponsorlogorecord->contextid, $sponsorlogorecord->component,
        $sponsorlogorecord->filearea, $sponsorlogorecord->itemid, $sponsorlogorecord->filepath,
        $sponsorlogorecord->filename);
        $logourl = $logourl->out();
    }
    return $logourl;
}

function logo_url($itemid = 0) {
    global $DB;
    $context = context_system::instance();
   
    if ($itemid > 0) {
        $sql = "SELECT * FROM {files} WHERE itemid = :logo AND component = 'local_events'  AND filearea='logo' AND filename != '.' ORDER BY id DESC";
        $logorecord = $DB->get_record_sql($sql,array('logo' => $itemid),1);
    }
    if (!empty($logorecord)) {
        $logourl = moodle_url::make_pluginfile_url($logorecord->contextid, $logorecord->component,
        $logorecord->filearea, $logorecord->itemid, $logorecord->filepath,
        $logorecord->filename);
        $logourl = $logourl->out();
    }else{
        return false;
    }
   // var_dump($logourl); exit;
    return $logourl;
}

function local_events_pluginfile($course, $cm, $context, $filearea, $args, $forcedownload, array $options = array()) {
    if ($context->contextlevel != CONTEXT_SYSTEM) {
        return false;
    }
    if ($filearea !== 'logo') {
        return false;
    }
    //require_login();
    $itemid = array_shift($args);
    $filename = array_pop($args); 
    if (!$args) {
        $filepath = '/';
    } else {
        $filepath = '/'.implode('/', $args).'/';
    }
    $fs = get_file_storage();
    $file = $fs->get_file($context->id, 'local_events', $filearea, $itemid, $filepath, $filename);
    if (!$file) {
        return false;
    }
    send_stored_file($file, 86400, 0, $forcedownload, $options);
}
/*
* Displays financialpayments
* @return  query
*/
function local_events_product_orders($stable,$filterdata){

    global $DB;

    $params          = array();

    $currentlang= current_language();

    if( $currentlang == 'ar'){

        $tpname='evnt.titlearabic as trainingname';

    }else{

        $tpname='evnt.title as trainingname';
    }
    if($stable->tablename == 'tool_org_order_payments'){
        $tpname .= ' ,tppmnt.payableamount';
    }


    $fromsql = "SELECT tppmnt.id,$tpname,0 as organization,tppmnt.tablename,tppmnt.fieldname,tppmnt.fieldid,evnt.id as trainingid ,(evnt.startdate + evnt.slot) as availablefrom,evnt.enddate as availableto,tppmnt.purchasedseats,tppmnt.orguserid,evnt.title as englishname,evnt.titlearabic as arabicname,evnt.code as ofrcode,evnt.code as tpcode  ";
    if($stable->tablename == 'tool_order_approval_seats'){
        $fromsql .= ", tppmnt.paymentid";
    }

    $sql = " FROM {" .$stable->tablename. "} AS tppmnt
             JOIN {local_events} AS evnt ON tppmnt.fieldid=evnt.id";
    /* if(!empty($filterdata->favourites) ){
    $sql .= " JOIN {favourite} AS fav ON fav.itemid = evnt.id AND fav.component LIKE '%local_events%' AND fav.userid = tppmnt.orguserid";
    }*/

    if (isset($stable->orguserid) && $stable->orguserid > 0) {
        $organization = $DB->get_field('local_users','organization',array('userid'=>$stable->orguserid));
        $sql .=  " JOIN {local_users} lu ON lu.userid = tppmnt.orguserid ";
        $sql.= " AND lu.organization = $organization  AND lu.deleted = 0 AND lu.approvedstatus = 2";
       // $sql .= " AND tppmnt.orguserid = $stable->orguserid ";
    }  

    $sql .= " WHERE tppmnt.tablename='local_events' AND tppmnt.fieldname='id'";

    return array('sql'=>$fromsql.$stable->selectparams. $sql,'params'=>$params);
}
/*
* Displays financialpayments
* @return  query
*/
function local_events_product_userorders($stable,$filterdata){

    global $DB;

    $params          = array();

    $currentlang= current_language();

    if( $currentlang == 'ar'){

        $tpname='evnt.titlearabic as trainingname';

    }else{

        $tpname='evnt.title as trainingname';
    }


    $fromsql = "SELECT tppmnt.id,$tpname,0 as organization,tppmnt.tablename,tppmnt.fieldname,tppmnt.fieldid,evnt.id as trainingid ,(evnt.startdate + evnt.slot) as availablefrom,evnt.enddate as availableto,tppmnt.purchasedseats,tppmnt.userid,evnt.title as englishname,evnt.titlearabic as arabicname,evnt.code as ofrcode,evnt.code as tpcode, evnt.sellingprice ";


    $sql = " FROM {" .$stable->tablename. "} AS tppmnt
             JOIN {local_events} AS evnt ON tppmnt.fieldid=evnt.id
             WHERE tppmnt.tablename='local_events' AND tppmnt.fieldname='id' ";
    

    if (isset($stable->userid) && $stable->userid > 0) {
        $sql .= " AND tppmnt.userid = $stable->userid ";
    }  


    return array('sql'=>$fromsql.$stable->selectparams. $sql,'params'=>$params);
}
