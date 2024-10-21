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

/** LearnerScript Reports
  * A Moodle block for creating customizable reports
  * @package blocks
  * @subpackage learnerscript
  * @author: jahnavi<jahnavi.nanduri@moodle.com>
  * @date: 2023
  */
namespace block_learnerscript\lsreports;
use block_learnerscript\local\pluginbase;
use block_learnerscript\local\reportbase;
use context_system;
use moodle_url;
use html_writer;

class plugin_meetingcolumn extends pluginbase{
    public function init(){
		$this->fullname = get_string('meetingcolumn','block_learnerscript');
		$this->type = 'undefined';
		$this->form = true;
		$this->reporttypes = array('meetingcolumn');
	}
	public function summary($data){
		return format_string($data->columname);
	}
	public function colformat($data){
		$align = (isset($data->align))? $data->align : '';
		$size = (isset($data->size))? $data->size : '';
		$wrap = (isset($data->wrap))? $data->wrap : '';
		return array($align,$size,$wrap);
	}
    public function execute($data, $row, $user, $courseid, $starttime = 0, $endtime = 0) {
        global $DB, $CFG;
        $filterdate = isset($this->reportfilterparams['filter_meetingdates']) ? $this->reportfilterparams['filter_meetingdates'] : 0;
        switch ($data->column) {           
            case 'jointime':
                if ($row->modulename == 'zoom') {
                    $jointime = $DB->get_field_sql("SELECT zmp.join_time
                    FROM {zoom_meeting_participants} zmp 
                    JOIN {zoom_meeting_details} zmd ON zmd.id = zmp.detailsid 
                    JOIN {zoom} z ON z.meeting_id = zmd.meeting_id  
                    JOIN {course_modules} cm ON cm.instance = z.id AND z.course = cm.course
                    JOIN {modules} m ON m.id = cm.module AND m.name = 'zoom'
                    JOIN {local_trainingprogram} lt ON lt.courseid = cm.course
                    JOIN {tp_offerings} tpo ON tpo.trainingid = lt.id
                    JOIN {user} u ON CONCAT(u.firstname, ' ', u.lastname) = zmp.name
                    WHERE u.id = $row->userid AND tpo.id = $row->offeringid AND cm.instance = $row->instance AND FROM_UNIXTIME(zmp.join_time, '%Y-%m-%d') = FROM_UNIXTIME($filterdate, '%Y-%m-%d')");
                    $row->{$data->column} = !empty($jointime) ? userdate($jointime, get_string('strftimedatemonthabbr', 'core_langconfig')) : '--';
                } else if ($row->modulename == 'teamsmeeting') {
                    $jointime = $DB->get_field_sql("SELECT tai.joindatetime
                    FROM {teams_attendance_intervals} tai 
                    JOIN {mod_teams_attendance} mta ON mta.id = tai.attendanceid 
                    JOIN {course_modules} cm ON cm.instance = mta.module AND mta.course = cm.course
                    JOIN {modules} m ON m.id = cm.module AND m.name = 'teamsmeeting'
                    JOIN {local_trainingprogram} lt ON lt.courseid = cm.course
                    JOIN {tp_offerings} tpo ON tpo.trainingid = lt.id
                    JOIN {user} u ON u.email = tai.name
                    WHERE u.id = $row->userid AND tpo.id = $row->offeringid AND cm.instance = $row->instance AND FROM_UNIXTIME(tai.joindatetime, '%Y-%m-%d') = FROM_UNIXTIME($filterdate, '%Y-%m-%d')");
                    $row->{$data->column} = !empty($jointime) ? userdate($jointime, get_string('strftimedatemonthabbr', 'core_langconfig')) : '--';
                } else if ($row->modulename == 'webexactivity') {
                    $jointime = $DB->get_field_sql("SELECT wp.jointime
                    FROM {mod_webex_participants} wp 
                    JOIN {webexactivity} wa ON wp.meetingid LIKE wa.meetid
                    JOIN {course_modules} cm ON cm.instance = wa.id
                    JOIN {modules} m ON m.id = cm.module AND m.name = 'webexactivity'
                    JOIN {local_trainingprogram} lt ON lt.courseid = cm.course
                    JOIN {tp_offerings} tpo ON tpo.trainingid = lt.id
                    JOIN {user} u ON u.email = wp.email
                    WHERE u.id IN ('".$row->userid."') AND tpo.id IN ('".$row->offeringid."') AND wp.email IN ('".$row->email."') AND cm.instance IN ('".$row->instance."') AND DATE(wp.jointime) = FROM_UNIXTIME(('".$filterdate."'), '%Y-%m-%d')");
                    $row->{$data->column} = !empty($jointime) ? $jointime : '--';
                }                
            break;
            case 'lefttime':
                if ($row->modulename == 'zoom') {
                    $lefttime = $DB->get_field_sql("SELECT zmp.leave_time
                    FROM {zoom_meeting_participants} zmp 
                    JOIN {zoom_meeting_details} zmd ON zmd.id = zmp.detailsid 
                    JOIN {zoom} z ON z.meeting_id = zmd.meeting_id  
                    JOIN {course_modules} cm ON cm.instance = z.id AND z.course = cm.course
                    JOIN {modules} m ON m.id = cm.module AND m.name = 'zoom'
                    JOIN {local_trainingprogram} lt ON lt.courseid = cm.course
                    JOIN {tp_offerings} tpo ON tpo.trainingid = lt.id
                    JOIN {user} u ON CONCAT(u.firstname, ' ', u.lastname) = zmp.name
                    WHERE u.id = $row->userid AND tpo.id = $row->offeringid AND cm.instance = $row->instance AND FROM_UNIXTIME(zmp.leave_time, '%Y-%m-%d') = FROM_UNIXTIME($filterdate, '%Y-%m-%d')");
                    $row->{$data->column} = !empty($lefttime) ? userdate($lefttime, get_string('strftimedatemonthabbr', 'core_langconfig')) : '--';
                } else if ($row->modulename == 'teamsmeeting') {
                    $lefttime = $DB->get_field_sql("SELECT tai.leavedatetime
                    FROM {teams_attendance_intervals} tai 
                    JOIN {mod_teams_attendance} mta ON mta.id = tai.attendanceid 
                    JOIN {course_modules} cm ON cm.instance = mta.module AND mta.course = cm.course
                    JOIN {modules} m ON m.id = cm.module AND m.name = 'teamsmeeting'
                    JOIN {local_trainingprogram} lt ON lt.courseid = cm.course
                    JOIN {tp_offerings} tpo ON tpo.trainingid = lt.id
                    JOIN {user} u ON u.email = tai.name
                    WHERE u.id = $row->userid AND tpo.id = $row->offeringid AND cm.instance = $row->instance AND FROM_UNIXTIME(tai.joindatetime, '%Y-%m-%d') = FROM_UNIXTIME($filterdate, '%Y-%m-%d')");
                    $row->{$data->column} = !empty($lefttime) ? userdate($lefttime, get_string('strftimedatemonthabbr', 'core_langconfig')) : '--';
                } else if ($row->modulename == 'webexactivity') {
                    $lefttime = $DB->get_field_sql("SELECT wp.lefttime
                    FROM {mod_webex_participants} wp 
                    JOIN {webexactivity} wa ON wp.meetingid LIKE wa.meetid
                    JOIN {course_modules} cm ON cm.instance = wa.id
                    JOIN {modules} m ON m.id = cm.module AND m.name = 'webexactivity'
                    JOIN {local_trainingprogram} lt ON lt.courseid = cm.course
                    JOIN {tp_offerings} tpo ON tpo.trainingid = lt.id
                    JOIN {user} u ON u.email = wp.email
                    WHERE u.id IN ('".$row->userid."') AND tpo.id IN ('".$row->offeringid."') AND wp.email IN ('".$row->email."') AND cm.instance IN ('".$row->instance."') AND DATE(wp.lefttime) = FROM_UNIXTIME(('".$filterdate."'), '%Y-%m-%d')");
                    $row->{$data->column} = !empty($lefttime) ? $lefttime : '--';
                } 
            break;

            case 'status':
                if ($row->modulename == 'zoom') {
                    $lefttime = $DB->get_field_sql("SELECT zmp.leave_time
                    FROM {zoom_meeting_participants} zmp 
                    JOIN {zoom_meeting_details} zmd ON zmd.id = zmp.detailsid 
                    JOIN {zoom} z ON z.meeting_id = zmd.meeting_id  
                    JOIN {course_modules} cm ON cm.instance = z.id AND z.course = cm.course
                    JOIN {modules} m ON m.id = cm.module AND m.name = 'zoom'
                    JOIN {local_trainingprogram} lt ON lt.courseid = cm.course
                    JOIN {tp_offerings} tpo ON tpo.trainingid = lt.id
                    JOIN {user} u ON CONCAT(u.firstname, ' ', u.lastname) = zmp.name
                    WHERE u.id = $row->userid AND tpo.id = $row->offeringid AND cm.instance = $row->instance AND FROM_UNIXTIME(zmp.leave_time, '%Y-%m-%d') = FROM_UNIXTIME($filterdate, '%Y-%m-%d')");
                    $row->{$data->column} = !empty($lefttime) ? 'Attended' : '--';
                } else if ($row->modulename == 'teamsmeeting') {
                    $lefttime = $DB->get_field_sql("SELECT tai.leavedatetime
                    FROM {teams_attendance_intervals} tai 
                    JOIN {mod_teams_attendance} mta ON mta.id = tai.attendanceid 
                    JOIN {course_modules} cm ON cm.instance = mta.module AND mta.course = cm.course
                    JOIN {modules} m ON m.id = cm.module AND m.name = 'teamsmeeting'
                    JOIN {local_trainingprogram} lt ON lt.courseid = cm.course
                    JOIN {tp_offerings} tpo ON tpo.trainingid = lt.id
                    JOIN {user} u ON u.email = tai.name
                    WHERE u.id = $row->userid AND tpo.id = $row->offeringid AND cm.instance = $row->instance AND FROM_UNIXTIME(tai.joindatetime, '%Y-%m-%d') = FROM_UNIXTIME($filterdate, '%Y-%m-%d')");
                    $row->{$data->column} = !empty($lefttime) ? 'Attended' : '--';
                } else if ($row->modulename == 'webexactivity') {
                    $lefttime = $DB->get_field_sql("SELECT wp.lefttime
                    FROM {mod_webex_participants} wp 
                    JOIN {webexactivity} wa ON wp.meetingid LIKE wa.meetid
                    JOIN {course_modules} cm ON cm.instance = wa.id
                    JOIN {modules} m ON m.id = cm.module AND m.name = 'webexactivity'
                    JOIN {local_trainingprogram} lt ON lt.courseid = cm.course
                    JOIN {tp_offerings} tpo ON tpo.trainingid = lt.id
                    JOIN {user} u ON u.email = wp.email
                    WHERE u.id IN ('".$row->userid."') AND tpo.id IN ('".$row->offeringid."') AND wp.email IN ('".$row->email."') AND cm.instance IN ('".$row->instance."') AND DATE(wp.lefttime) = FROM_UNIXTIME(('".$filterdate."'), '%Y-%m-%d')");
                    $row->{$data->column} = !empty($lefttime) ? 'Attended' : '--';
                } 
            break;
        }
        return (isset($row->{$data->column}))? $row->{$data->column} : ' -- ';
    }
}
