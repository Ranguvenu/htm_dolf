<?php
// This file is part of the Zoom plugin for Moodle - http://moodle.org/
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
 * @package    mod_teamsmeeting
 * @copyright  2022 eAbyas Info Solutions Pvt Ltd (www.eabyas.com)
 * @author     Ranga Reddy<rangareddy@eabyas.com>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die();

require_once("$CFG->dirroot/mod/teamsmeeting/classes/graph-teams-client.php");

use mod_teamsmeeting\mod_teamsmeeting_api_connector as api_connector;

// function teamsmeeting_supports($feature) {
//     switch($feature) {
//         case FEATURE_BACKUP_MOODLE2:
//             return true;
//         default:
//             return null;
//     }
// }

function teamsmeeting_add_instance( $data ){
    global $DB;

    $timezone = get_user_timezone();
    $start = userdate($data->start_time, get_string('azure_format', 'mod_teamsmeeting'));
    $endtime = ($data->end_time) ? $data->end_time:$data->start_time + $data->duration;
    $end = userdate($endtime, get_string('azure_format', 'mod_teamsmeeting'));

    $config = get_config('mod_teamsmeeting');

    $teamsclient = new api_connector(
                        $config->tenantid,
                        $config->clientid,
                        $config->clientsecret,
                        $config->userid
                    );
    $params = array();
    if($data->isrecuring){
        $params['isrecuring'] = true;
        $params['recurrence'] = array(
            'type'          =>  'daily',
            'interval'      =>  1,
            'days_of_week'  =>  ["Monday", "Tuesday", "Wednesday", "Thursday", "Friday", "Saturday", "Sunday"],
            'start'         =>  userdate($data->start_time, get_string('Ymd_format', 'mod_teamsmeeting')),
            'end'           =>  userdate($data->occurs_until, get_string('Ymd_format', 'mod_teamsmeeting'))
        );
     $end = $start;
    }
    $meeting = $teamsclient->create_meeting($start, $end, $timezone, format_string($data->name), $params);
    $data->meetingurl = $meeting['join_url'];
    $totalduration = ((date("H",$data->start_time) * 3600) + (date("i",$data->start_time) * 60)) + $data->duration;
    $data->end_time = ($data->end_time) ? $data->end_time : (($data->isrecuring == 1) ? ($data->occurs_until + $totalduration) : null);
    $data->metadata = serialize($meeting['metadata']);
    $data->meetingid = $meeting['id'];
    $data->timecreated = time();
    $data->timemodified = time();
 
    $id = $DB->insert_record('teamsmeeting', $data);
    
    return $id;
}

function teamsmeeting_update_instance( $data ){
    global $DB;
    $data->id           = $data->instance;
    $timezone = get_user_timezone();
    $start = userdate($data->start_time, get_string('azure_format', 'mod_teamsmeeting'));
    $endtime =($data->end_time) ? $data->end_time :  $data->start_time + $data->duration;
    $end = userdate($endtime, get_string('azure_format', 'mod_teamsmeeting'));

    $meeting = $DB->get_record('teamsmeeting', array('id' => $data->instance), 'meetingid');
    
    $config = get_config('mod_teamsmeeting');
    $teamsclient = new api_connector(
                        $config->tenantid,
                        $config->clientid,
                        $config->clientsecret,
                        $config->userid
                    );
    
    $params = array();
    if($data->isrecuring){
        $params['isrecuring'] = true;
        $params['recurrence'] = array(
            'type'          =>  'daily',
            'interval'      =>  1,
            'days_of_week'  =>  ["Monday", "Tuesday", "Wednesday", "Thursday", "Friday", "Saturday", "Sunday"],
            'start'         =>  userdate($data->start_time, get_string('Ymd_format', 'mod_teamsmeeting')),
            'end'           =>  userdate($data->occurs_until, get_string('Ymd_format', 'mod_teamsmeeting'))
        );

        $end = $start;
    }
    $update_meeting = $teamsclient->update_teams_meeting($meeting->meetingid, $start, $end, $timezone, format_string($data->name), $params);
    $totalduration = ((date("H",$data->start_time) * 3600) + (date("i",$data->start_time) * 60)) + $data->duration;
    $data->end_time = ($data->end_time) ? $data->end_time : (($data->isrecuring == 1) ? ($data->occurs_until + $totalduration) : null);
    $data->metadata = serialize($update_meeting['metadata']);
    $data->timemodified = time();
    $id = $DB->update_record('teamsmeeting', $data);
    return $id;
}

function teamsmeeting_delete_instance($id) {
    global $DB;
   
    $teams = $DB->get_record('teamsmeeting', array('id' => $id));
   
    if (!$teams) {
        // For some reason already deleted, so let Moodle take care of the rest.
        return true;
    }
    $config = get_config('mod_teamsmeeting');
    $teamsclient = new api_connector(
                        $config->tenantid,
                        $config->clientid,
                        $config->clientsecret,
                        $config->userid
                    );
    $teamsclient->delete_teams_meeting($teams->meetingid);
    
    $DB->delete_records('teamsmeeting', array('id' => $id));

    return true;
}

function get_attendance_report($teamsrecord){
    $config = get_config('mod_teamsmeeting');
    $apiconnector = new api_connector(
        $config->tenantid,
        $config->clientid,
        $config->clientsecret,
        $config->userid
    );  
    $onlinemeetinginfo = $apiconnector->get_meeting_attendance_records($teamsrecord->meetingurl);
    return $onlinemeetinginfo;
}

function sync_attendance_report($moduleid){
    global $DB;
    $cm = get_coursemodule_from_instance('teamsmeeting', $moduleid, 0, false, MUST_EXIST);
    $teamsrecord = $DB->get_record('teamsmeeting', array('id' => $cm->instance), '*', MUST_EXIST);
    $attendance = get_attendance_report($teamsrecord);
    foreach($attendance['attendance_report'] as $reportid => $attendancereport){
        foreach($attendancereport as $key => $reportdata) {
            $recordexists = $DB->get_field('mod_teams_attendance', 'id', array('module' => $cm->id, 'email' =>  $reportdata['emailAddress'], 'reportid' => $reportid), IGNORE_MISSING);
            if(!$recordexists){
                $data = new stdClass;
                $data->course = $cm->course;
                $data->module = $cm->id;
                $data->reportid = $reportid;
                $data->onlinemeetingid = $attendance['online_meeting_id'];
                $data->email = $reportdata['emailAddress'];
                $data->role = $reportdata['role'];
                $data->totaltime = $reportdata['totalAttendanceInSeconds'];
                $record = $DB->insert_record('mod_teams_attendance', $data, true, false);
                sync_attendance_intervals($record, $reportdata['attendance_intervals']);
            }
        }
    }
    return $attendance;
}

function sync_attendance_intervals($attendanceid, $intervals){
    global $DB;
    foreach($intervals as $interval){
        $data = new StdClass;
        $data->attendanceid = $attendanceid;
        $data->joindatetime = strtotime($interval['joinDateTime']);
        $data->leavedatetime = strtotime($interval['leaveDateTime']);
        $data->duration = $interval['durationInSeconds'];
        $record = $DB->insert_record('teams_attendance_intervals', $data, true, false);
    }
}


