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
 * An activity to interface with WebEx.
 *
 * @package    mod_webexactvity
 * @author     Eric Merrill <merrill@oakland.edu>
 * @copyright  2014 Oakland University
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die();

require_once($CFG->dirroot.'/calendar/lib.php');

/**
 * Return the list if Moodle features this module supports
 *
 * @param string $feature FEATURE_xx constant for requested feature
 * @return mixed True if module supports feature, null if doesn't know
 */
function webexactivity_supports($feature) {
    switch($feature) {
        case FEATURE_MOD_ARCHETYPE:
            return MOD_ARCHETYPE_ASSIGNMENT;
        case FEATURE_GROUPS:
            return false;
        case FEATURE_GROUPINGS:
            return false;
        case FEATURE_MOD_INTRO:
            return true;
        case FEATURE_COMPLETION_TRACKS_VIEWS:
            return true;
        case FEATURE_GRADE_HAS_GRADE:
            return false;
        case FEATURE_GRADE_OUTCOMES:
            return false;
        case FEATURE_BACKUP_MOODLE2:
            return false;
        case FEATURE_SHOW_DESCRIPTION:
            return true;

        default:
            return null;
    }
}
function refresh_access_token(){
    global $DB, $CFG;
    $curl = curl_init();
    $token =$DB->get_field('config', 'value', ['name' => 'webexrefreshtoken']);

    curl_setopt_array($curl, array(
    CURLOPT_URL => 'https://webexapis.com/v1/access_token',
    CURLOPT_RETURNTRANSFER => true,
    CURLOPT_ENCODING => '',
    CURLOPT_MAXREDIRS => 10,
    CURLOPT_TIMEOUT => 0,
    CURLOPT_FOLLOWLOCATION => true,
    CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
    CURLOPT_CUSTOMREQUEST => 'POST',
    CURLOPT_POSTFIELDS => 'grant_type=refresh_token&client_id='.get_config('webexactivity', 'clientid').'&client_secret='.get_config('webexactivity', 'clientsecretkey').'&refresh_token='.$token,
    CURLOPT_HTTPHEADER => array(
        'Content-Type: application/x-www-form-urlencoded'
    ),
    ));

    $response = curl_exec($curl);
    $newtoken = json_decode($response, true);

    curl_close($curl);
    $response = json_decode($response);

    if($response->errors){
        redirect('https://webexapis.com/v1/authorize?client_id='.get_config('webexactivity', 'clientid').'&response_type=code&redirect_uri='.$CFG->wwwroot.'/local/trainingprogram/index.php&scope=meeting%3Aadmin_preferences_write%20spark%3Aall%20meeting%3Aadmin_preferences_read%20meeting%3Aadmin_participants_read%20spark-admin%3Apeople_write%20spark%3Apeople_write%20spark%3Aorganizations_read%20spark-admin%3Aworkspace_metrics_read%20spark-admin%3Aplaces_read%20spark%3Aplaces_read%20spark-compliance%3Amessages_read%20spark-admin%3Adevices_write%20spark-admin%3Aworkspaces_write%20spark-compliance%3Ameetings_write%20meeting%3Aadmin_schedule_write%20spark-admin%3Aorganizations_write%20spark-admin%3Aworkspace_locations_read%20spark%3Adevices_write%20spark-admin%3Awholesale_sub_partners_write%20spark-admin%3Abroadworks_billing_reports_write%20spark%3Axapi_commands%20spark-admin%3Acall_qualities_read%20spark%3Akms%20spark-admin%3Awholesale_sub_partners_read%20meeting%3Aparticipants_write%20meeting%3Aadmin_transcripts_read%20spark-admin%3Apeople_read%20spark-compliance%3Amemberships_read%20spark-admin%3Aresource_groups_read%20meeting%3Arecordings_read%20meeting%3Aparticipants_read%20meeting%3Apreferences_write%20spark-admin%3Aorganizations_read%20meeting%3Aadmin_recordings_read%20meeting%3Atranscripts_read%20spark%3Axapi_statuses%20meeting%3Aschedules_write%20spark-admin%3Acalling_cdr_read%20spark-admin%3Adevices_read%20meeting%3Acontrols_read%20spark-admin%3Aworkspace_locations_write%20spark-admin%3Atelephony_config_read%20spark-admin%3Atelephony_config_write%20spark-admin%3Abroadworks_billing_reports_read%20meeting%3Aadmin_schedule_read%20meeting%3Aschedules_read%20spark-compliance%3Amemberships_write%20spark%3Acalls_read%20spark-admin%3Aroles_read%20meeting%3Arecordings_write%20meeting%3Apreferences_read%20spark-compliance%3Ameetings_read%20spark-admin%3Aworkspaces_read%20spark%3Adevices_read%20spark-admin%3Aresource_group_memberships_read%20spark-compliance%3Aevents_read%20spark-admin%3Aresource_group_memberships_write%20meeting%3Acontrols_write%20meeting%3Aadmin_recordings_write%20spark-admin%3Aplaces_write%20spark-admin%3Alicenses_read%20spark%3Aplaces_write&state=set_state_here');
    }
    $objdata = new stdClass();
    $objdata->value = $newtoken['access_token'];
    $objdata->name = 'webexaccesstoken';
    if(!($DB->record_exists('config', ['name' => 'webexaccesstoken']))){
        $DB->insert_record('config', $objdata);
    }else{
        $objdata->id =$DB->get_field('config', 'id', ['name' => 'webexaccesstoken']);
        $DB->update_record('config', $objdata);
    }
}
// function update_refreshtoken(){
//     redirect('https://webexapis.com/v1/authorize?client_id=Cbe6696b6e67cdc6a7ddf72a2b407a0f514d661d33f646885ba42efa2346ac01e&response_type=code&redirect_uri=https%3A%2F%2Ffa.webex.com%2Fwebappng%2Fsites%2Ffa%2Fmeeting%2Fhome&scope=meeting%3Arecordings_read%20spark%3Akms%20meeting%3Aschedules_read%20meeting%3Aparticipants_read%20meeting%3Arecordings_write%20meeting%3Aparticipants_write%20meeting%3Aschedules_write&state=set_state_here');
// }
// function autherization(){
//     global $CFG;
//     redirect('https://webexapis.com/v1/authorize?client_id='.get_config('webexactivity', 'clientid').'&response_type=code&redirect_uri='.$CFG->wwwroot.'/local/trainingprogram/index.php&scope=meeting%3Aadmin_preferences_write%20spark%3Aall%20meeting%3Aadmin_preferences_read%20meeting%3Aadmin_participants_read%20spark-admin%3Apeople_write%20spark%3Apeople_write%20spark%3Aorganizations_read%20spark-admin%3Aworkspace_metrics_read%20spark-admin%3Aplaces_read%20spark%3Aplaces_read%20spark-compliance%3Amessages_read%20spark-admin%3Adevices_write%20spark-admin%3Aworkspaces_write%20spark-compliance%3Ameetings_write%20meeting%3Aadmin_schedule_write%20spark-admin%3Aorganizations_write%20spark-admin%3Aworkspace_locations_read%20spark%3Adevices_write%20spark-admin%3Awholesale_sub_partners_write%20spark-admin%3Abroadworks_billing_reports_write%20spark%3Axapi_commands%20spark-admin%3Acall_qualities_read%20spark%3Akms%20spark-admin%3Awholesale_sub_partners_read%20meeting%3Aparticipants_write%20meeting%3Aadmin_transcripts_read%20spark-admin%3Apeople_read%20spark-compliance%3Amemberships_read%20spark-admin%3Aresource_groups_read%20meeting%3Arecordings_read%20meeting%3Aparticipants_read%20meeting%3Apreferences_write%20spark-admin%3Aorganizations_read%20meeting%3Aadmin_recordings_read%20meeting%3Atranscripts_read%20spark%3Axapi_statuses%20meeting%3Aschedules_write%20spark-admin%3Acalling_cdr_read%20spark-admin%3Adevices_read%20meeting%3Acontrols_read%20spark-admin%3Aworkspace_locations_write%20spark-admin%3Atelephony_config_read%20spark-admin%3Atelephony_config_write%20spark-admin%3Abroadworks_billing_reports_read%20meeting%3Aadmin_schedule_read%20meeting%3Aschedules_read%20spark-compliance%3Amemberships_write%20spark%3Acalls_read%20spark-admin%3Aroles_read%20meeting%3Arecordings_write%20meeting%3Apreferences_read%20spark-compliance%3Ameetings_read%20spark-admin%3Aworkspaces_read%20spark%3Adevices_read%20spark-admin%3Aresource_group_memberships_read%20spark-compliance%3Aevents_read%20spark-admin%3Aresource_group_memberships_write%20meeting%3Acontrols_write%20meeting%3Aadmin_recordings_write%20spark-admin%3Aplaces_write%20spark-admin%3Alicenses_read%20spark%3Aplaces_write&state=set_state_here');
// }

function tokens($code){
    $curl = curl_init();
    curl_setopt_array($curl, array(
      CURLOPT_URL => 'https://webexapis.com/v1/access_token',
      CURLOPT_RETURNTRANSFER => true,
      CURLOPT_ENCODING => '',
      CURLOPT_MAXREDIRS => 10,
      CURLOPT_TIMEOUT => 0,
      CURLOPT_FOLLOWLOCATION => true,
      CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
      CURLOPT_CUSTOMREQUEST => 'POST',
      CURLOPT_POSTFIELDS => 'grant_type=authorization_code&client_id='.get_config('webexactivity', 'clientid').'&client_secret='.get_config('webexactivity', 'clientsecretkey').'&code='.$code.'&redirect_uri=http%3A%2F%2Flocalhost%2Fdolf%2Fmod%2Fwebexactivity%2Fredirect.php',
      CURLOPT_HTTPHEADER => array(
        'Content-Type: application/x-www-form-urlencoded'
      ),
    ));
    
    $response = curl_exec($curl);

    curl_close($curl);
    $response = json_decode($response);

    return $response->refresh_token;
    

}

function get_participants($cm = false, $newtoken = false){
    global $DB, $CFG;

    $webexid = $DB->get_field('course_modules', 'instance', ['id' => $cm]);
    $meetid = $DB->get_field('webexactivity', 'meetid', ['id' => $webexid]);
    $curl = curl_init();
    $sql = "SELECT wa.activity
             FROM {webexactivity} wa
             JOIN {course_modules} cm ON cm";

    $token =$DB->get_field('config', 'value', ['name' => 'webexaccesstoken']);


    curl_setopt_array($curl, array(
      CURLOPT_URL => 'https://webexapis.com/v1/meetingParticipants?max=100&meetingId='.$meetid,
      CURLOPT_RETURNTRANSFER => true,
      CURLOPT_ENCODING => '',
      CURLOPT_MAXREDIRS => 10,
      CURLOPT_TIMEOUT => 0,
      CURLOPT_FOLLOWLOCATION => true,
      CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
      CURLOPT_CUSTOMREQUEST => 'GET',
      CURLOPT_HTTPHEADER => array(
        'Authorization: Bearer '.$token,
        'Cookie: trackingSessionID=8FB27DFA232B47FF8074D88C5C7B1E61'
      ),
    ));
    
    $response = curl_exec($curl);
    $responseData = json_decode($response, true);
    $invalid_meetingid = strpos($responseData['message'], 'meeting id');
    if($responseData['errors'] && $invalid_meetingid == false){
        static $i = 0;
        refresh_access_token();
        $i++;
        if($i === 2){
            redirect('https://webexapis.com/v1/authorize?client_id='.get_config('webexactivity', 'clientid').'&response_type=code&redirect_uri='.$CFG->wwwroot.'/local/trainingprogram/index.php&scope=meeting%3Aadmin_preferences_write%20spark%3Aall%20meeting%3Aadmin_preferences_read%20meeting%3Aadmin_participants_read%20spark-admin%3Apeople_write%20spark%3Apeople_write%20spark%3Aorganizations_read%20spark-admin%3Aworkspace_metrics_read%20spark-admin%3Aplaces_read%20spark%3Aplaces_read%20spark-compliance%3Amessages_read%20spark-admin%3Adevices_write%20spark-admin%3Aworkspaces_write%20spark-compliance%3Ameetings_write%20meeting%3Aadmin_schedule_write%20spark-admin%3Aorganizations_write%20spark-admin%3Aworkspace_locations_read%20spark%3Adevices_write%20spark-admin%3Awholesale_sub_partners_write%20spark-admin%3Abroadworks_billing_reports_write%20spark%3Axapi_commands%20spark-admin%3Acall_qualities_read%20spark%3Akms%20spark-admin%3Awholesale_sub_partners_read%20meeting%3Aparticipants_write%20meeting%3Aadmin_transcripts_read%20spark-admin%3Apeople_read%20spark-compliance%3Amemberships_read%20spark-admin%3Aresource_groups_read%20meeting%3Arecordings_read%20meeting%3Aparticipants_read%20meeting%3Apreferences_write%20spark-admin%3Aorganizations_read%20meeting%3Aadmin_recordings_read%20meeting%3Atranscripts_read%20spark%3Axapi_statuses%20meeting%3Aschedules_write%20spark-admin%3Acalling_cdr_read%20spark-admin%3Adevices_read%20meeting%3Acontrols_read%20spark-admin%3Aworkspace_locations_write%20spark-admin%3Atelephony_config_read%20spark-admin%3Atelephony_config_write%20spark-admin%3Abroadworks_billing_reports_read%20meeting%3Aadmin_schedule_read%20meeting%3Aschedules_read%20spark-compliance%3Amemberships_write%20spark%3Acalls_read%20spark-admin%3Aroles_read%20meeting%3Arecordings_write%20meeting%3Apreferences_read%20spark-compliance%3Ameetings_read%20spark-admin%3Aworkspaces_read%20spark%3Adevices_read%20spark-admin%3Aresource_group_memberships_read%20spark-compliance%3Aevents_read%20spark-admin%3Aresource_group_memberships_write%20meeting%3Acontrols_write%20meeting%3Aadmin_recordings_write%20spark-admin%3Aplaces_write%20spark-admin%3Alicenses_read%20spark%3Aplaces_write&state=set_state_here');
        }else if($i === 3){
            throw new exception\token_isnotvalid();
        }

        get_participants();
    }else if($invalid_meetingid == true){
        throw new exception\token_isnotvalid();
    }

    curl_close($curl);
    return $response;
}



function insertion($cm){
    global $DB, $CFG;
    $parti = get_participants($cm);

    $responseData = json_decode($parti, true);


    $objects = [];
    foreach ($responseData as $rs) {
        foreach ($rs as $key => $value) {
            $sdclass = new stdClass();

            // Use property assignment on the $sdclass object
            $meetingid = explode("_", $value['meetingId']);
            $sdclass->meetingid = $meetingid[0];

            $sdclass->hostmail = $value['hostEmail'];
            $sdclass->email = $value['email'];
            $sdclass->jointime = $value['joinedTime'];
            $sdclass->lefttime = $value['leftTime'];
            $sdclass->siteurl = $value['siteUrl'];
            $sdclass->duration = $value['devices']['0']['durationSecond'];
    
            // Add $sdclass object to the $objects array
            $objects[] = $sdclass;
    
        }
        
    
    }
    
    $saml = explode("_", $value['meetingId']);
    if($sdclass->duration){
        $webexid = $DB->get_field('course_modules', 'instance', ['id' => $cm]);
        $meetid = $DB->get_field('webexactivity', 'meetid', ['id' => $webexid]);
   
        $data=$DB->get_record_sql("SELECT * FROM {mod_webex_participants} wp WHERE wp.meetingid LIKE ('%".$meetid."%') AND CURDATE()= DATE(wp.jointime)");
        $jointimedata=$DB->get_record_sql("SELECT * FROM {mod_webex_participants} wp WHERE wp.meetingid LIKE ('%".$meetid."%')");

        $datejoin = explode("T", $jointimedata->jointime);
        $joindate = $objects[0]->jointime;
        $objectdatejoin = explode("T", $joindate);

        if($datejoin[0] == $objectdatejoin[0]){
            return;
        }else{
           $DB->insert_records('mod_webex_participants', $objects);
            if(!empty($sdclass->meetingid)){
             take_attendence_virtualmettings($sdclass->meetingid);
            }
        }
    }else{
        return;
    }


}



function get_meetingparticipants($cm) {
    global $DB;

    $webexid = $DB->get_field('course_modules', 'instance', ['id' => $cm]);
    $meetid = $DB->get_field('webexactivity', 'meetid', ['id' => $webexid]);
    if($DB->get_record_sql("SELECT * FROM {mod_webex_participants} wp WHERE wp.meetingid LIKE ('%".$meetid."%')")){
        return;
    }else{
        $participants = $DB->get_records_sql("SELECT DISTINCT u.firstname, u.email, wp.lefttime
        FROM {mod_webex_participants} wp
        JOIN {user} u ON u.email = wp.email
        JOIN {webexactivity} wa ON wp.meetingid LIKE CONCAT(wa.meetid, '%')
        WHERE wa.meetid = $meetid");
    }

    $participantsArray = [];
    foreach ($participants as $key => $participant) {
        $participantArray = array(
            'firstname' => $participant->firstname,
            'email' => $participant->email,
            'lefttime' => $participant->lefttime
        );

        $participantsArray[] = $participantArray;
    }
    $templatecontext = (object)[
        'data' => $participantsArray
    ];

    return $templatecontext;
}
/**
 * Adds an WebEx Meeting instance.
 *
 * @param stdClass              $data Form data
 * @param mod_assign_mod_form   $form The form
 * @return int The instance id of the new assignment
 */

function webexactivity_add_instance($data, $mform) {
    global $PAGE;


    $meeting = \mod_webexactivity\meeting::create_new($data->type);
    $meeting->starttime = $data->starttime;
    $meeting->duration = $data->duration;
    $meeting->calpublish = !empty($data->calpublish) ? 1 : 0;
    if (isset($data->longavailability)) {
        $meeting->endtime = $data->endtime;
        $meeting->calpublish = 0;
    } else {
        $meeting->endtime = null;
    }
    $meeting->intro = $data->intro;
    $meeting->introformat = $data->introformat;
    $meeting->name = $data->name;
    $meeting->course = $data->course;

    if (isset($data->password) && !empty($data->password)) {
        $meeting->password = $data->password;
    } else {
        $meeting->password = null;
    }

    $meeting->status = \mod_webexactivity\webex::WEBEXACTIVITY_STATUS_NEVER_STARTED;
    if (isset($data->studentdownload) && $data->studentdownload) {
        $meeting->studentdownload = 1;
    } else {
        $meeting->studentdownload = 0;
    }

    $meeting->cmid = $data->coursemodule;

    if (!$meeting->save()) {
        return false;
    }
    return $meeting->id;

}

/**
 * Update an WebEx Meeting instance.
 *
 * @param stdClass              $data Form data
 * @param mod_assign_mod_form   $form The form
 * @return bool                 If the update passed (true) or failed
 */
function webexactivity_update_instance($data) {
    global $PAGE;

    $cmid = $data->coursemodule;
    $cm = get_coursemodule_from_id('webexactivity', $cmid, 0, false, MUST_EXIST);
    $meeting = \mod_webexactivity\meeting::load($cm->instance);

    $meeting->starttime = $data->starttime;
    $meeting->duration = $data->duration;
    $meeting->calpublish = !empty($data->calpublish) ? 1 : 0;
    if (isset($data->longavailability)) {
        $meeting->endtime = $data->endtime;
        $meeting->calpublish = 0;
    } else {
        $meeting->endtime = null;
    }
    $meeting->intro = $data->intro;
    $meeting->introformat = $data->introformat;
    $meeting->name = format_string($data->name);
    $meeting->course = $data->course;

    if (isset($data->password) && !empty($data->password)) {
        $meeting->password = $data->password;
    } else {
        $meeting->password = null;
    }

    if (isset($data->studentdownload) && $data->studentdownload) {
        $meeting->studentdownload = 1;
    } else {
        $meeting->studentdownload = 0;
    }

    $meeting->cmid = $data->coursemodule;

    try {
        return $meeting->save();
    } catch (Exception $e) {
        $collision = ($e instanceof \mod_webexactivity\exception\webex_user_collision);
        $password = ($e instanceof \mod_webexactivity\exception\bad_password);
        if ($collision || $password) {
            \mod_webexactivity\webex::password_redirect($PAGE->url);
        } else {
            throw $e;
        }
        throw $e;
    }

    // TODO - update cal event
}

/**
 * Print an overview of all WebEx Meetings for the courses.
 *
 * @param mixed   $courses The list of courses to print the overview for
 * @param array   $htmlarray The array of html to return
 */
function webexactivity_print_overview($courses, &$htmlarray) {
    global $USER, $CFG, $DB;

    if (empty($courses) || !is_array($courses) || count($courses) == 0) {
        return;
    }

    if (!$meetings = get_all_instances_in_courses('webexactivity', $courses)) {
        return;
    }

    $displaymeetings = array();

    foreach ($meetings as $rec) {
        $meeting = \mod_webexactivity\meeting::load($rec);
        if ($meeting->is_available()) {
            $displaymeetings[] = $meeting;
        }
    }

    if (count($displaymeetings) == 0) {
        return;
    }

    $strmodname = get_string('modulename', 'webexactivity');
    $strinprogress = get_string('inprogress', 'webexactivity');
    $strstartsoon = get_string('startssoon', 'webexactivity');
    $strstarttime = get_string('starttime', 'webexactivity');
    $strstatus = get_string('status');

    foreach ($displaymeetings as $meeting) {
        $href = $CFG->wwwroot . '/mod/webexactivity/view.php?id=' . $meeting->coursemodule;
        $str = '<div class="webexactivity overview"><div class="name">';
        $str .= $strmodname.': <a title="'.$strmodname.'" href="'.$href.'">';
        $str .= format_string($meeting->name).'</a></div>';

        $status = $meeting->get_time_status();
        if (!isset($meeting->endtime)) {
            $str .= '<div class="start">'.$strstarttime.': '.userdate($meeting->starttime).'</div>';
        }
        if ($status == \mod_webexactivity\webex::WEBEXACTIVITY_TIME_IN_PROGRESS) {
            $str .= '<div class="status">'.$strstatus.': '.$strinprogress.'</div>';
        } else if ($status == \mod_webexactivity\webex::WEBEXACTIVITY_TIME_AVAILABLE) {
            $str .= '<div class="status">'.$strstatus.': '.$strstartsoon.'</div>';
        }
        $str .= '</div>';

        if (isset($htmlarray[$meeting->course]['webexactivity'])) {
            $htmlarray[$meeting->course]['webexactivity'] .= $str;
        } else {
            $htmlarray[$meeting->course]['webexactivity'] = $str;
        }
    }

}

/**
 * Delete a WebEx instance.
 *
 * @param int   $id     Record id to delete.
 * @return bool
 */
function webexactivity_delete_instance($id) {
    $meeting = \mod_webexactivity\meeting::load($id);
    return $meeting->delete();
}


/**
 * This function receives a calendar event and returns the action associated with it, or null if there is none.
 *
 * This is used by block_myoverview in order to display the event appropriately. If null is returned then the event
 * is not displayed on the block.
 *
 * @param calendar_event $event
 * @param \core_calendar\action_factory $factory
 * @param int $userid User id to use for all capability checks, etc. Set to 0 for current user (default).
 * @return \core_calendar\local\event\entities\action_interface|null
 */
function mod_webexactivity_core_calendar_provide_event_action(calendar_event $event,
                                                     \core_calendar\action_factory $factory,
                                                     int $userid = 0) {
    global $USER, $DB;

    if ($userid) {
        $user = core_user::get_user($userid, 'id, timezone');
    } else {
        $user = $USER;
    }

    $cm = get_fast_modinfo($event->courseid, $user->id)->instances['webexactivity'][$event->instance];

    return $factory->create_instance(
        get_string('entermeeting', 'webexactivity'),
        new \moodle_url('/mod/webexactivity/view.php', ['id' => $cm->id]),
        1,
        false
    );

}

/**
 * Ramanjaneyulu Added
 * This function take attendence from virtual meetings table (Zoom/webex/teams) and inserted into attendance table .
 * @param metting id $meetingid
 */

    function take_attendence_virtualmettings($meetingid){
              global $USER, $DB;
             //$meetingid="a620ffebbc14750b0e63a99774f24e04";
             $get_meetingid=$DB->get_record_sql("SELECT * FROM {mod_webex_participants} wp
             WHERE wp.meetingid ='".$meetingid."'");
             $get_meetid=$DB->get_record_sql("SELECT * FROM {webexactivity} wa
             WHERE wa.meetid ='".$get_meetingid->meetingid."'");
             $offerings_ref_id=$get_meetid->id;
             $get_webix_id= $DB->get_record_sql("SELECT id FROM {modules} WHERE name IN ('webexactivity')");
             $course_sections=$DB->get_record_sql("SELECT * FROM {tp_offerings} tpo
             WHERE tpo.meetingid ='".$offerings_ref_id."' AND tpo.meetingtype=2 ");
             $course_module_sections=$DB->get_record_sql("SELECT * FROM {course_sections} cs
             WHERE cs.id ='".$course_sections->sections."'");
             $get_att_id= $DB->get_record_sql("SELECT id FROM {modules} WHERE name IN ('attendance')");
             $get_attendence_id=$DB->get_record_sql("SELECT * FROM {course_modules} com
             WHERE com.section ='".$course_module_sections->id."' AND com.course= '".$course_module_sections->course."' AND com.module='".$get_att_id->id."' ");
             $attendanceid=$get_attendence_id->instance;
             $get_users=$DB->get_records_sql("SELECT * FROM {mod_webex_participants} wp
             WHERE wp.meetingid ='".$meetingid."'");
             $get_attendance_statuses=$DB->get_fieldset_sql("SELECT id FROM {attendance_statuses} ats
             WHERE ats.attendanceid ='".$attendanceid."'");
             $statusid=$get_attendance_statuses[0];
             $statusset=implode(",",$get_attendance_statuses);

             foreach($get_users as $user){
                 $id=$user->id;
                 $jointime=$user->jointime;
                 $lefttime=$user->lefttime;
                 //date conversition
                 $dateOnly = substr($jointime, 0, 10);
                 $dateObject = DateTime::createFromFormat('Y-m-d', $dateOnly);
                 $meeting_sessiondate = $dateObject->format('Y-m-d');
                 $studentid=$DB->get_field_sql("SELECT id FROM {user} WHERE email = '".$user->email."' ");
                 $sessionid=$DB->get_field_sql("SELECT id FROM {attendance_sessions} WHERE FROM_UNIXTIME(sessdate, '%Y-%m-%d') = '".$meeting_sessiondate."' AND attendanceid = '".$attendanceid."' ");
                  $takenbyid=2;
                  (new attendance_handler)->update_user_status($sessionid, $studentid, $takenbyid, $statusid, $statusset);
            }
        }



