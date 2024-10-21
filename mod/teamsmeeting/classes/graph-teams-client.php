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
 * GraphApi Connector.
 * 
 * @package    mod_teamsmeeting
 * @copyright  2022 eAbyas Info Solutions Pvt Ltd (www.eabyas.com)
 * @author     Ranga Reddy<rangareddy@eabyas.com>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace mod_teamsmeeting;

if(file_exists($CFG->dirroot.'/mod/teamsmeeting/vendor/autoload.php')){
    require_once($CFG->dirroot.'/mod/teamsmeeting/vendor/autoload.php');
} else {
    return;
}  
use Microsoft\Graph\Graph;
use Beta\Microsoft\Graph\Model as BetaModel;
use Microsoft\Graph\Model as MicrosoftModel;
class mod_teamsmeeting_api_connector {
    protected $clientid;
    protected $clientsecret;
    protected $scope = "https://graph.microsoft.com/.default";
    protected $apiurl = "https://login.microsoftonline.com/";
    protected $apiversion = "v1.0/";
    protected $tenantid;
    protected $userid;
    protected $session;
    /**
     * Main Constructor
     * 
     * @param string $clientid
     * @param string $tenantid
     * @param string $clientsecret
     */
    public function __construct($tenantid, $clientid, $clientsecret, $userid){
        global $SESSION;
        $this->session = $SESSION;
        $this->tenantid = $tenantid;
        $this->clientid = $clientid;
        $this->clientsecret = $clientsecret;
        $this->userid = $userid;
    }

    /**
     * Sets Access Token
     * 
     * @param string $token
     * @return bool
     */
    protected function set_access_token( $token ){
        $this->session->graph_access_token = $token;
    }

    /**
     * Get Access Token
     * 
     * @return string $access_token [Microsoft Access Token]
     */
    protected function get_access_token(){
        if($this->is_token_expired()){
            $token = $this->generate_access_token();
            return $token;
        }else{
            return $this->session->graph_access_token;
        }
    }

    protected function has_access_token(){
        return $this->session->graph_access_token ? true : false;
    }
    
    protected function set_expires_at( $time ){
        $this->session->graph_access_token_expires_at = $time;
    }

    protected function get_expires_at(){
        return $this->session->graph_access_token_expires_at ? $this->session->graph_access_token_expires_at : false;
    }

    protected function is_token_expired(){
        return ($this->has_access_token() && $this->get_expires_at() > time()) ? false : true;
    }

    /**
     * Generates Access Token
     * 
     * @return string $access_token [Microsoft Access Token]
     */
    public function generate_access_token(){
        $guzzle = new \GuzzleHttp\Client();

        $url = $this->apiurl.$this->tenantid."/oauth2/v2.0/token";

        $token = $guzzle->post($url, [
            'form_params'   =>  [
                'client_id'     =>  $this->clientid,
                'client_secret' =>  $this->clientsecret,
                'scope'         =>  $this->scope,
                'grant_type'    =>  'client_credentials'
            ],
        ])->getBody()->getContents();

        $token = json_decode($token);
        
        $this->set_access_token($token->access_token);

        $this->set_expires_at(time()+$token->expires_in);

        return $token->access_token;
    }

    /**
     * Create Teams Meeting
     * 
     * @param datetime $start [Meeting Start Time 2022-11-12T14:30:34.2444915-07:00]
     * @param datetime $end [Meeting End Time 2022-11-12T15:00:34.2464912-07:00]
     * @param string $subject [Meeting Subject]
     * @return string $join_url [Meeting Join Url]
     */
    public function create_meeting($start, $end, $timezone, $subject, $params = array()) {
        
        $graph = new Graph();
 
        $graph->setAccessToken($this->get_access_token());

        $url = "users/".$this->userid."/calendar/events";
        
        $payload = array(
            "subject"           =>  $subject,
            "start"             =>  array(
                "dateTime"  =>  $start,
                "timeZone"  =>  $timezone
            ),
            "end"               =>  array(
                "dateTime"  =>  $end,
                "timeZone"  =>  $timezone
            ),
            "isOnlineMeeting"       =>  true,
        );
        
        if(isset($params['isrecuring']) && $params['isrecuring']){
            $payload['recurrence'] = array(
                "pattern"   =>  array(
                    "type"      =>  $params['recurrence']['type'],
                    "interval"  =>  $params['recurrence']['interval'],
                    "daysOfWeek"=>  $params['recurrence']['days_of_week']
                ),
                "range"     =>  array(
                    "type"      =>  "endDate",
                    "startDate" =>  $params['recurrence']['start'],
                    "endDate"   =>  $params['recurrence']['end']
                )
            );
        }
        
        $meeting = $graph->setApiVersion($this->apiversion)
                         ->createRequest("POST", $url)
                         ->attachBody($payload)
                         ->execute();
        $meeting = $meeting->getBody();

        $meetinginfo = new BetaModel\OnlineMeetingInfo( $meeting['onlineMeeting'] );
   
        $joinurl = $meetinginfo->getJoinUrl();

        return array(
            'metadata'  =>  $meeting,
            'join_url'  =>  $joinurl,
            'id'        =>  $meeting['id']
        );
    }

    /**
     * Update Teams Meeting
     * 
     * @param string $meeting_id
     * @param datetime $start [Meeting Start Time 2022-11-12T14:30:34.2444915-07:00]
     * @param datetime $end [Meeting End Time 2022-11-12T15:00:34.2464912-07:00]
     * @param string $subject [Meeting Subject]
     * 
     */
    public function update_teams_meeting($meetingid, $start, $end, $timezone, $subject, $params = array()){

        $graph = new Graph();
        $graph->setAccessToken($this->get_access_token());
        $url = "users/".$this->userid."/calendar/events/".$meetingid;
        
        $payload = array(
            "subject"           =>  $subject,
            "start"             =>  array(
                "dateTime"  =>  $start,
                "timeZone"  =>  $timezone
            ),
            "end"               =>  array(
                "dateTime"  =>  $end,
                "timeZone"  =>  $timezone
            )
        );
        
        if(isset($params['isrecuring']) && $params['isrecuring']){
            $payload['recurrence'] = array(
                "pattern"   =>  array(
                    "type"      =>  $params['recurrence']['type'],
                    "interval"  =>  $params['recurrence']['interval'],
                    "daysOfWeek"=>  $params['recurrence']['days_of_week']
                ),
                "range"     =>  array(
                    "type"      =>  "endDate",
                    "startDate" =>  $params['recurrence']['start'],
                    "endDate"   =>  $params['recurrence']['end']
                )
            );
        }
    
        $meeting = $graph->setApiVersion($this->apiversion)
                         ->createRequest("PATCH", $url)
                         ->attachBody($payload)
                         ->execute();
        $meeting = $meeting->getBody();

        $meetinginfo = new BetaModel\OnlineMeetingInfo( $meeting['onlineMeeting'] );
   
        $joinurl = $meetinginfo->getJoinUrl();

        return array(
            'metadata'  =>  $meeting,
            'join_url'  =>  $joinurl,
            'id'        =>  $meeting['id']
        );
    }

    /**
     * Delete Teams Meeting
     * 
     * @param string $meeting_id
     * 
     */
    public function delete_teams_meeting( $meetingid ){
        $graph = new Graph();
        $graph->setAccessToken($this->get_access_token());
        $url = "users/".$this->userid."/calendar/events/".$meetingid;

        $meeting = $graph->setApiVersion("v1.0/")
                         ->createRequest("DELETE", $url)
                         ->execute();
        return $meeting;
    }

    /**
     * Get Online Meeting Id 
     * 
     * @param string $join_url
     * @return string $online_meeting_id
     */
    public function get_online_meeting_id($joinurl){
        $graph = new Graph();
        $graph->setAccessToken($this->get_access_token());
        $url = 'users/'.$this->userid.'/onlineMeetings?$filter=JoinWebUrl eq ';
        $url = $url."'".$joinurl."'";
        $meeting = $graph->setApiVersion("v1.0/")
                            ->createRequest("GET", $url)
                            ->execute();
        $meeting = $meeting->getBody();    
        if($meeting && isset($meeting['value']) && is_array($meeting['value'])){          
            $onlinemeetinginfo = new BetaModel\OnlineMeeting( $meeting['value'][0] );
            return $onlinemeetinginfo->getId();
        }
        return false;
    }

    /**
     * Get Attendance Records
     * 
     * @param string $online_meeting_id
     * @return array $attendance
     */
    public function get_attendance_records($onlinemeetingid, $attendancereportid){
        $graph = new Graph();
        $graph->setAccessToken($this->get_access_token());
        $url = 'users/'.$this->userid.'/onlineMeetings/'.$onlinemeetingid.'/attendanceReports/'.$attendancereportid.'/attendanceRecords';
        $attendancerecords = $graph->setApiVersion("beta/")
                            ->createRequest("GET", $url)
                            ->execute();
        $attendancerecords = $attendancerecords->getBody(); 
        $records = array();
        foreach($attendancerecords['value'] as $key => $attendancerecord){
            $attendance =  new BetaModel\AttendanceRecord( $attendancerecord );            
            $records[] = array(
                "id"                        =>  $attendance->getId(),
                "emailAddress"              =>  $attendance->getEmailAddress(),
                "totalAttendanceInSeconds"  =>  $attendance->getTotalAttendanceInSeconds(),
                "role"                      =>  $attendance->getRole(),
                "attendance_intervals"      =>  $attendance->getAttendanceIntervals()
            );
        }
        return $records;
    }

    /**
     * Get Attendance Reports
     * 
     * @param string $online_meeting_id
     * @return array $response
     */
    public function get_meeting_attendance_reports($onlinemeetingid){
        $graph = new Graph();
        $graph->setAccessToken($this->get_access_token());
        $url = 'users/'.$this->userid.'/onlineMeetings/'.$onlinemeetingid.'/attendanceReports';
        $attendancereports = $graph->setApiVersion("beta/")
                            ->createRequest("GET", $url)
                            ->execute();
        $attendancereports = $attendancereports->getBody(); 
        $response = array();
        foreach($attendancereports['value'] as $attendancereport){
            $report = new BetaModel\MeetingAttendanceReport( $attendancereport );
            $response[] = array(
                'id'                    =>  $report->getId(),
                'total_participants'    =>  $report->getTotalParticipantCount(),
                'start_date_time'       =>  $report->getMeetingStartDateTime(),
                'end_date_time'         =>  $report->getMeetingEndDateTime()
            );
        }
        return $response;
    }

    /**
     * Get Meeting Attendance Report From Meeting Join Url
     * 
     * @param string $join_url
     * @return array $report
     */
    public function get_meeting_attendance_records($joinurl){
        $response = array(
            "has_online_meeting_id"     =>  false,
            "online_meeting_id"         =>  '',
            "has_attendance_report_ids" =>  false,
            "attendance_report"         =>  []
        );
        $onlinemeetingid = $this->get_online_meeting_id($joinurl);
        if($onlinemeetingid){
            $response['online_meeting_id']  = $onlinemeetingid;
            $response['has_online_meeting_id'] = true;
            $attendancereports = $this->get_meeting_attendance_reports($onlinemeetingid);
            if($attendancereports && is_array($attendancereports)){
                $response['has_attendance_report_ids'] = true;
                foreach($attendancereports as $attendancereport){
                    $response['attendance_report'][$attendancereport['id']] = $this->get_attendance_records($onlinemeetingid, $attendancereport['id']);
                }
            }
        }
        return $response;
    }
}