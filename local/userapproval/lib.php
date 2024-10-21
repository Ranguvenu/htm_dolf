<?php
// This file is part of Moodle - http://moodle.org/
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
 * @package    local_userapproval
 * @copyright  2022 eAbyas Info Solutions<info@eabyas.com>
 * @author     Vinod Kumar  <vinod.p@eabyas.com>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
function local_userapproval_leftmenunode(){
    $systemcontext = context_system::instance();
    $referralcode = '';
    $orgpendingrequests = '';
    $trainer_expert_requestrequests = '';
    if(is_siteadmin() || has_capability('local/organization:manage_organizationofficial',$systemcontext) || has_capability('local/organization:manage_trainingofficial', $systemcontext)|| has_capability('local/organization:manage_examofficial',$systemcontext)){

        $referralcode .= html_writer::start_tag('li', array('id'=> 'id_leftmenu_referralcode', 'class'=>'pull-left user_nav_div referralcode'));
        $referral_url = new moodle_url('/local/userapproval/index.php');
        $referral_label = get_string('listofusers','local_userapproval');

        $referral = html_writer::link($referral_url, '<span class="users_icon side_menu_img_icon"></span><span class="user_navigation_link_text">'.$referral_label.'</span>',
            array('class'=>'user_navigation_link'));
        $referralcode .= $referral;
        $referralcode .= html_writer::end_tag('li');
        $orgpendingrequests .= html_writer::start_tag('li', array('id'=> 'id_leftmenu_referralcode', 'class'=>'pull-left user_nav_div referralcode'));
        $orgreferral_url = new moodle_url('/local/userapproval/orgofficialtotalrequests.php');

        if (is_siteadmin()|| has_capability('local/organization:manage_trainingofficial', $systemcontext) || has_capability('local/organization:manage_examofficial',$systemcontext)) {
            $orgreferral_label = get_string('orgpendingrequests','local_userapproval');

        } else {
            $orgreferral_label = get_string('myorgpendingrequests','local_userapproval');
        }
        $referral = html_writer::link($orgreferral_url, '<span class="org_pending_icon side_menu_img_icon"></span><span class="user_navigation_link_text">'.$orgreferral_label.'</span>',
            array('class'=>'user_navigation_link'));
        $orgpendingrequests .= $referral;
        $orgpendingrequests .= html_writer::end_tag('li');


    if(is_siteadmin()){

            $trainer_expert_requestrequests .= html_writer::start_tag('li', array('id'=> 'id_leftmenu_referralcode', 'class'=>'pull-left user_nav_div referralcode'));
            $url = new moodle_url('/local/userapproval/trainer_expert_request.php');
            $label = get_string('trainer_expert_request','local_userapproval');

            $referral = html_writer::link($url, '<span class="trainerrequest_icon side_menu_img_icon"></span><span class="user_navigation_link_text">'.$label.'</span>',
                array('class'=>'user_navigation_link'));
            $trainer_expert_requestrequests .= $referral;
            $trainer_expert_requestrequests .= html_writer::end_tag('li');

        }

        else if(has_capability('local/organization:manage_trainingofficial', $systemcontext)){

            $trainer_expert_requestrequests .= html_writer::start_tag('li', array('id'=> 'id_leftmenu_referralcode', 'class'=>'pull-left user_nav_div referralcode'));
            $url = new moodle_url('/local/userapproval/trainer_expert_request.php');
            $label = get_string('trainerrequest','local_userapproval');

            $referral = html_writer::link($url, '<span class="trainerrequest_icon side_menu_img_icon"></span><span class="user_navigation_link_text">'.$label.'</span>',
                array('class'=>'user_navigation_link'));
            $trainer_expert_requestrequests .= $referral;
            $trainer_expert_requestrequests .= html_writer::end_tag('li');

        }

         else if(has_capability('local/organization:manage_examofficial',$systemcontext)){

            $trainer_expert_requestrequests .= html_writer::start_tag('li', array('id'=> 'id_leftmenu_referralcode', 'class'=>'pull-left user_nav_div referralcode'));
            $url = new moodle_url('/local/userapproval/trainer_expert_request.php');
            $label = get_string('expertrequest','local_userapproval');

            $referral = html_writer::link($url, '<span class="trainerrequest_icon side_menu_img_icon"></span><span class="user_navigation_link_text">'.$label.'</span>',
                array('class'=>'user_navigation_link'));
            $trainer_expert_requestrequests .= $referral;
            $trainer_expert_requestrequests .= html_writer::end_tag('li');

        }


    }
    $recode = '';
    if(is_siteadmin() ||( has_capability('local/organization:manage_organizationofficial', $systemcontext)) ) {


        $orgrequest = $orgpendingrequests;
        $recode = $referralcode;
    }
    
    return array('12' => $orgrequest,'13' => $trainer_expert_requestrequests,'14' => $recode);
}

function userapproval_filters_form($filterparams){

    global $CFG;
    $systemcontext = context_system::instance();
    require_once($CFG->dirroot . '/local/organization/dynamicfilters_form.php');
    if(!is_siteadmin() && has_capability('local/organization:manage_organizationofficial', $systemcontext) ) {
        $filters = array(
        'userapproval'=>array('local'=>array('email','deletedusers')),
        );
    } else {
        $filters = array(
            'userapproval'=>array('local'=>array('email','status','role','deletedusers')),
        );
    }
    $mform = new dynamicfilters_form(null, array('filterlist'=>$filters,'filterparams' => $filterparams,'submitid' =>'viewregistrations','ajaxformsubmit'=>true), 'post', '', null, true,$_REQUEST);
    return $mform;
}
function email_filter($mform){
    global $DB,$USER;
    $systemcontext = context_system::instance();
    $userattributes = array(
        'ajax' => 'local_organization/organization_datasource',
        'data-type' => 'usersemail',
        'id' => 'usersemail',
        'data-org' => 'listofusers',
        'multiple'=>true,
    );
    $mform->addElement('autocomplete', 'email', get_string('email', 'local_userapproval'),[], $userattributes);
}
function status_filter($mform,$query='',$searchanywhere=false, $page=0, $perpage=25){
    global $DB,$USER;
    $systemcontext = context_system::instance();
    $statuslist = [];
    $statuslist['1'] = get_string('pending','local_userapproval');
    $statuslist['2'] = get_string('approved','local_userapproval');
    $statuslist['3'] = get_string('rejected','local_userapproval');
    $options = array(
	    'multiple' => true,
        'noselectionstring' => get_string('status'),
    );
    $statuselement = $mform->addElement('autocomplete', 'status', get_string('status', 'local_userapproval'),$statuslist, ['id' => 'elstatuslist']);
     $statuselement->setMultiple(true);
} 

function deletedusers_filter($mform,$query='',$searchanywhere=false, $page=0, $perpage=25){
	 global $DB,$USER;
    $systemcontext = context_system::instance();
	$mform->addElement('checkbox', 'deletedusers', get_string('deletedusers', 'local_userapproval'));
    $mform->setType('deletedusers', PARAM_INT);
}

function role_filter($mform,$query='',$searchanywhere=false, $page=0, $perpage=25){
    global $DB,$USER;
    $systemcontext = context_system::instance();
  
    $roleslists=$DB->get_records_sql(
        "SELECT id,name AS fullname FROM {role}
        WHERE shortname <> 'manager' AND 
        shortname <> 'coursecreator'AND 
        shortname <> 'editingteacher' AND
        shortname <> 'teacher' AND
        shortname <> 'student' AND
        shortname <> 'guest' AND
        shortname <> 'user' AND
        shortname <> 'frontpage' ORDER BY name ASC"
    );
    $roles=[];
    foreach ($roleslists AS $roleslist){ 
        $roles[$roleslist->id] = $roleslist->fullname;
    }
    $roleelement = $mform->addElement('autocomplete', 'role', get_string('role', 'local_userapproval'),$roles, ['id' => 'elrolelist']);
     $roleelement->setMultiple(true);
} 

function local_userapproval_pluginfile($course, $cm, $context, $filearea, $args, $forcedownload, array $options=array()) {

    if ($context->contextlevel != CONTEXT_SYSTEM) {
        return false;
    }
    if($filearea != 'image' && $filearea != 'entitylogo' && $filearea != 'qualification'){
        return false;
    }    
    $postid = (int)array_shift($args);
    $fs = get_file_storage();
    $relativepath = implode('/', $args);
    $fullpath = "/$context->id/local_userapproval/$filearea/$postid/$relativepath";
    if (!$file = $fs->get_file_by_hash(sha1($fullpath)) or $file->is_directory()) {
        return false;
    }
    // finally send the file
    send_stored_file($file, 0, 0, true, $options); // download MUST be forced - security!
}
function profileimage_url($itemid = 0) {
    global $DB;
    $context = context_system::instance();
    if ($itemid > 0) {
        $sql = "SELECT * FROM {files} WHERE itemid = :logo AND filearea='image' AND filename != '.' ORDER BY id DESC";
        $profileimagerecord = $DB->get_record_sql($sql,array('logo' => $itemid),1);
    }
    if (!empty($profileimagerecord)) {
        $logourl = moodle_url::make_pluginfile_url($profileimagerecord->contextid, $profileimagerecord->component,
       $profileimagerecord->filearea, $profileimagerecord->itemid, $profileimagerecord->filepath,
       $profileimagerecord->filename);
        $logourl = $logourl->out();
    }
    return $logourl;
}
function bannerimage_url($itemid = 0) {
    global $DB;
    $context = context_system::instance();
    if ($itemid > 0) {
        $sql = "SELECT * FROM {files} WHERE itemid = :logo AND filearea='image' AND filename != '.' ORDER BY id DESC";
        $bannerimagerecord = $DB->get_record_sql($sql,array('logo' => $itemid),1);
    }
    if (!empty($bannerimagerecord)) {
        $logourl = moodle_url::make_pluginfile_url($bannerimagerecord->contextid, $bannerimagerecord->component,
       $bannerimagerecord->filearea, $bannerimagerecord->itemid, $bannerimagerecord->filepath,
       $bannerimagerecord->filename);
        $logourl = $logourl->out();
    }
    return $logourl;
}

function ifcisi_update($eventdata){
    global $DB, $CFG;
    require_once($CFG->dirroot.'/local/exams/lib.php');
    $context = context_system::instance();
    $data = $eventdata->get_data();
    $userid = $data['relateduserid'];
    $is_cisi_user = $DB->get_record_sql("SELECT u.id, u.firstname, u.lastname, u.email,lu.dateofbirth, ext.externaluserid, ext.id ext_recordid 
        FROM {user} u
        JOIN {local_users} lu ON lu.userid = u.id
        JOIN {externalprovider_userdetails} ext ON ext.userid = u.id
        WHERE u.id = :userid
        ", ['userid' => $userid]);
    $cisi_accessdetails = get_cisi_user_login_details();
    if ($is_cisi_user->externaluserid) {
        $cisiservises = new local_exams\local\cisi_services();

        $authToken = $cisiservises->AuthenticationSigninAuthToken($cisi_accessdetails->clientid, $cisi_accessdetails->clientsecret, $cisi_accessdetails->cisiusername, $cisi_accessdetails->cisipassword, $cisi_accessdetails->cisiscope);
        if ($authToken->success) {
            $event = \local_exams\event\cisi_auth_token_created::create(array( 'context'=>$context, 'objectid' => $is_cisi_user->id, 'userid' => $is_cisi_user->id, 'other' => $authToken));
            $event->trigger();
        }else{
            $event = \local_exams\event\cisi_auth_token_creation_failed::create(array( 'context'=>$context, 'objectid' => $is_cisi_user->id, 'userid' => $is_cisi_user->id, 'other' => $authToken));
            $event->trigger();
        }
        $accessToken = $cisiservises->AuthenticationTokenAccessToken($authToken->results->authToken, $cisi_accessdetails->clientid, $cisi_accessdetails->clientsecret);
        if ($accessToken->success) {
            $event = \local_exams\event\cisi_access_token_created::create(array( 'context'=>$context, 'objectid' => $is_cisi_user->id, 'userid' => $is_cisi_user->id, 'other' => $accessToken));
            $event->trigger();
        }else{
            $event = \local_exams\event\cisi_access_token_creation_failed::create(array( 'context'=>$context, 'objectid' => $is_cisi_user->id, 'userid' => $is_cisi_user->id, 'other' => $accessToken));
            $event->trigger();
        }
        // Check if user belongs to CISI or not.
        $user_cisi = new stdClass();
            
        $user_cisi = $cisiservises->cisi_update_user($accessToken->results->accessToken, $is_cisi_user->externaluserid, 'Mr./Ms', $is_cisi_user->email, $is_cisi_user->lastname, $is_cisi_user->firstname, date('Y-m-d',$is_cisi_user->dateofbirth));
        if ($user_cisi->success) {
            $user_cisi->externaluserid = $user_cisi->results->id;
            // update time update column in mdl_externalprovider_userdetails.
            $DB->update_record('externalprovider_userdetails', ['id' => $is_cisi_user->ext_recordid, 'updatedtime'=> time()]);

            $event = \local_exams\event\cisi_user_updation_successful::create(array( 'context'=>$context, 'objectid' => $user_cisi->externaluserid, 'userid' => $is_cisi_user->id, 'other' => $user_cisi));
            $event->trigger();
        }else{
            $event = \local_exams\event\cisi_user_updation_failed::create(array( 'context'=>$context, 'objectid' => $user_cisi->externaluserid, 'userid' => $is_cisi_user->id, 'other' => $user_cisi));
            $event->trigger();
        }
    }
    return true;
}
//renu
function local_userapproval_trainerexpert_filters_form($filterparams){

    global $CFG;
    $systemcontext = context_system::instance();
    require_once($CFG->dirroot . '/local/organization/dynamicfilters_form.php');
   
        $filters = array(
            'userapproval'=>array('local'=>array('emailid','rolename', 'betweendatetime')),
        );
    
    $mform = new dynamicfilters_form(null, array('filterlist'=>$filters,'filterparams' => $filterparams,'submitid' =>'alldata','ajaxformsubmit'=>true), 'post', '', null, true,$_REQUEST);
    return $mform;  
}
function emailid_filter($mform){
    global $DB,$USER;
    $systemcontext = context_system::instance();
    $userattributes = array(
        'ajax' => 'local_organization/organization_datasource',
        'data-type' => 'usersemail',
        'email' => 'usersemail',
        'data-org' => 'listofusers',
        'multiple'=>true,
    );

    // print_r($userattributes);
    // exit;
    $mform->addElement('autocomplete', 'email', get_string('email', 'local_userapproval'),[], $userattributes);
}

function rolename_filter($mform){
    global $DB,$USER;
    $systemcontext = context_system::instance();

     $expertRoleName = 'expert';
        $trainerRoleName = 'trainer';

        $expertRoleId = $DB->get_field(
            'role',
            'id',
            ['shortname' => $expertRoleName]
        );

     

        // Get trainer role ID
        $trainerRoleId = $DB->get_field(
            'role',
            'id',
            ['shortname' => $trainerRoleName]
        );
  
    $roleslists=$DB->get_records_sql(
        "SELECT id,name AS fullname FROM {role}
        WHERE id IN($expertRoleId,$trainerRoleId) ORDER BY name ASC"
    );
    $roles=[];
    foreach ($roleslists AS $roleslist){ 
        $roles[$roleslist->id] = $roleslist->fullname;
    }
    $roleelement = $mform->addElement('autocomplete', 'role', get_string('role', 'local_userapproval'),$roles, ['id' => 'elrolelist']);
     $roleelement->setMultiple(true);
} 

function betweendatetime_filter($mform){

    $systemcontext = context_system::instance();
    $mform->addElement('date_selector', 'availablefrom', get_string('availablefrom', 'local_userapproval'),array('optional'=>true));
    $mform->setType('availablefrom', PARAM_RAW);

    $mform->addElement('date_selector', 'availableto', get_string('availableto', 'local_userapproval'),array('optional'=>true));
    $mform->setType('availableto', PARAM_RAW);

}

///Ramanjaneyulu Added
function exams_fastuserenroll_filters_form($filterparams){
    global $CFG;
    require_once($CFG->dirroot . '/local/organization/dynamicfilters_form.php');
    $filters = array(
        'userapproval'=>array('local'=>array('fast_timecreated','fast_user','fast_email','fast_phone','fast_idnumber')),
    );
    $mform = new dynamicfilters_form(null, array('filterlist'=>$filters,'filterparams' => $filterparams,'submitid' =>'manage_userenrol','ajaxformsubmit'=>true), 'post', '', null, true,$_REQUEST);
    return $mform;


    //$filters = array('userapproval'=>array('local'=>array('fast_examdatetime','fast_purchaseddatetime','fast_type','fast_centercode','fast_examcode','fast_profilecode','fast_examlanguage')),);
    //$mform = new dynamicfilters_form(null, array('filterlist'=>$filters,'filterparams' => $filterparams,'submitid' =>'manage_examenrol','ajaxformsubmit'=>true), 'post', '', null, true,$_REQUEST);
    return $mform;
}

function fast_user_filter($mform,$query='',$searchanywhere=false, $page=0, $perpage=25){
    global $DB;
    $usernamelists=$DB->get_records_sql(
        "SELECT DISTINCT username,username AS username FROM {local_fast_user} ORDER BY username ASC"
    );
    $usernames=[];
    foreach ($usernamelists AS $usernamelist){ 
        $usernames[$usernamelist->username] = $usernamelist->username;
    }
    $usernameelement = $mform->addElement('autocomplete', 'username', get_string('username', 'local_userapproval'),$usernames, ['id' => 'elfastuser']);
    $usernameelement->setMultiple(true);
}
function fast_email_filter($mform,$query='',$searchanywhere=false, $page=0, $perpage=25){
    global $DB;
    $emaillists=$DB->get_records_sql(
        "SELECT DISTINCT email,email AS email FROM {local_fast_user} ORDER BY email ASC"
    );
    $emails=[];
    foreach ($emaillists AS $emaillist){ 
        $emails[$emaillist->email] = $emaillist->email;
    }
    $emailelement = $mform->addElement('autocomplete', 'email', get_string('email', 'local_userapproval'),$emails, ['id' => 'elfastemail']);
    $emailelement->setMultiple(true);
}
function fast_phone_filter($mform,$query='',$searchanywhere=false, $page=0, $perpage=25){
    global $DB;
    $mobilelists=$DB->get_records_sql(
        "SELECT DISTINCT phonenumber,phonenumber AS phonenumber FROM {local_fast_user} ORDER BY phonenumber ASC"
    );
    $mobiles=[];
    foreach ($mobilelists AS $mobilelist){ 
        $mobiles[$mobilelist->phonenumber] = $mobilelist->phonenumber;
    }
    $mobileelement = $mform->addElement('autocomplete', 'phonenumber', get_string('mobile', 'local_userapproval'),$mobiles, ['id' => 'elfastphonenumber']);
    $mobileelement->setMultiple(true);
}

function fast_idnumber_filter($mform,$query='',$searchanywhere=false, $page=0, $perpage=25){
    global $DB;
    $idnumberlists=$DB->get_records_sql(
        "SELECT DISTINCT id_number,id_number AS idnumber FROM {local_fast_user} ORDER BY phonenumber ASC"
    );
    $idnumbers=[];
    foreach ($idnumberlists AS $idnumberlist){ 
        $idnumbers[$idnumberlist->idnumber] = $idnumberlist->idnumber;
    }
    $idnumberelement = $mform->addElement('autocomplete', 'idnumber', get_string('idnumber', 'local_userapproval'),$idnumbers, ['id' => 'elfastidnumber']);
    $idnumberelement->setMultiple(true);
}

function fast_timecreated_filter($mform){

    $mform->addElement('date_time_selector', 'userenroltimefrom', get_string('userenrol_datefrom', 'local_userapproval'),array('optional'=>true));
    $mform->setType('availablefrom', PARAM_RAW);

    $mform->addElement('date_time_selector', 'userenroltimeto', get_string('userenrol_dateto', 'local_userapproval'),array('optional'=>true));
    $mform->setType('availableto', PARAM_RAW);

}


//renu
 function local_trainerexpert_req_filters_form($filterparams){

    global $CFG;
    $systemcontext = context_system::instance();
    require_once($CFG->dirroot . '/local/organization/dynamicfilters_form.php');
   
        $filters = array(
            'userapproval'=>array('local'=>array('requesttype')),
        );
    
    $mform = new dynamicfilters_form(null, array('filterlist'=>$filters,'filterparams' => $filterparams,'submitid' =>'alldata','ajaxformsubmit'=>true), 'post', '', null, true,$_REQUEST);
    return $mform;  
}


function requesttype_filter($mform){
    global $DB,$USER;

    $systemcontext = context_system::instance();
    $expertRoleName = 'expert';
        $trainerRoleName = 'trainer';
    $roles=[];

  
     if(is_siteadmin()){
        $expertRoleId = $DB->get_field(
            'role',
            'id',
            ['shortname' => $expertRoleName]
        );
         // Get trainer role ID
        $trainerRoleId = $DB->get_field(
            'role',
            'id',
            ['shortname' => $trainerRoleName]
        );

         $roleslists=$DB->get_records_sql(
        "SELECT id,name AS fullname FROM {role}
        WHERE id IN($expertRoleId,$trainerRoleId) ORDER BY name ASC"
    );

    }
  
   
   
    foreach ($roleslists AS $roleslist){ 
        $roles[$roleslist->id] = $roleslist->fullname;
    }
    $roleelement = $mform->addElement('autocomplete', 'requesttype', get_string('requesttype', 'local_userapproval'),$roles, ['id' => 'elrolelist']);
     $roleelement->setMultiple(true);

 }

