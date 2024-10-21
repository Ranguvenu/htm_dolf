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
 * LearnerScript Reports
 * A Moodle block for creating customizable reports
 * @package blocks
 * @author: Sudharani Sadula
 * @date: 2022
 */
namespace block_learnerscript\lsreports;
use block_learnerscript\local\pluginbase;
use html_writer;

class plugin_offeringsessioncolumns extends pluginbase {

    public function init() {
        $this->fullname = get_string('offeringsessioncolumns', 'block_learnerscript');
        $this->type = 'undefined';
        $this->form = true;
        $this->reporttypes = array('offeringsessions');
    }

    public function summary($data) {
        return format_string($data->columname);
    }

    public function colformat($data) {
        $align = (isset($data->align)) ? $data->align : '';
        $size = (isset($data->size)) ? $data->size : '';
        $wrap = (isset($data->wrap)) ? $data->wrap : '';
        return array($align, $size, $wrap);
    }

    public function execute($data, $row, $user, $courseid, $starttime = 0, $endtime = 0) {
        global $DB, $CFG;
        switch ($data->column) {  
            case 'startdate': 
                if (!empty($row->startdate)) {
                    $examdate = userdate($row->startdate,get_string('strftimedatemonthabbr', 'langconfig'));

                    $row->{$data->column} = !empty($examdate) ? $examdate : '--';
                } else {
                    $row->{$data->column} = '--';
                }
            break;
            case 'enddate';
                if (!empty($row->enddate)) {
                    $examdate = userdate($row->enddate,get_string('strftimedatemonthabbr', 'core_langconfig'));

                    $row->{$data->column} = !empty($examdate) ? $examdate : '--';
                } else {
                    $row->{$data->column} = '--';
                }
            break;
            case 'enrolledusers':
                if(!isset($row->enrolledusers)){
                    $enrolledusers = $DB->get_field_sql("SELECT count(DISTINCT userid) FROM {program_enrollments} WHERE offeringid = $row->id");
                     $row->{$data->column} = !empty($enrolledusers) ? $enrolledusers : 0; 
                }
                 
            break;
            case 'certissueusers':
                if(!isset($row->certissueusers)){
                    $certissueusers = $DB->get_field_sql("SELECT count(id) FROM {tool_certificate_issues} WHERE moduletype = 'trainingprogram' AND moduleid = $row->id");
                     $row->{$data->column} = !empty($certissueusers) ? $certissueusers : 0; 
                }
            break;
            case 'certnotissued':
                if(!isset($row->certnotissued)){
                    $certnotissued = $enrolledusers - $certissueusers;
                    $row->{$data->column} = !empty($certnotissued) ? $certnotissued : 0; 
                }
            break;
            case 'absentusers':
                if(!isset($row->absentusers)){
                    $absentusers = $DB->get_field_sql("SELECT COUNT(DISTINCT atl.studentid) 
                        FROM {attendance_log} atl
                    JOIN {attendance_statuses} attstatus ON atl.id = attstatus.attendanceid
                    JOIN {attendance_sessions} atts ON atts.id = atl.sessionid
                    JOIN {offering_sessions} os ON os.sessionid = atts.id
                    WHERE os.offeringid = $row->id AND attstatus.acronym = 'A'");
                     $row->{$data->column} = !empty($absentusers) ? $absentusers : 0; 
                }
            break;
            case 'notissueother':
                if(!isset($row->notissueother)){
                    $notissueother = $certnotissued - $absentusers;
                     $row->{$data->column} = !empty($notissueother) ? $notissueother : 0; 
                }
            break;
        }
        return (isset($row->{$data->column}))? $row->{$data->column} : ' -- ';
    }
}
