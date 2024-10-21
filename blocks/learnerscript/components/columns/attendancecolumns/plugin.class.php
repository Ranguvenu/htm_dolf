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
 * @author: Jahnavi Nanduri
 * @date: 2022
 */
namespace block_learnerscript\lsreports;
use block_learnerscript\local\pluginbase;
use block_learnerscript\local\reportbase;
use block_learnerscript\local\ls;
use context_system;
use html_writer;

class plugin_attendancecolumns extends pluginbase {

    public function init() {
        $this->fullname = get_string('attendancecolumns', 'block_learnerscript');
        $this->type = 'undefined';
        $this->form = true;
        $this->reporttypes = array('attendance');
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
        global $DB, $USER, $CFG;
        $context = context_system::instance();
        switch ($data->column) {
            case 'joinedtime':
                if(!isset($row->joinedtime) && isset($data->subquery)){
                    $joinedtime =  $DB->get_field_sql($data->subquery);
                }else{
                    $joinedtime = $DB->get_field_sql("SELECT joindatetime FROM {teams_attendance_intervals} WHERE attendanceid = $row->id ORDER BY id ASC LIMIT 0, 1");
                }
                $row->{$data->column} = !empty($joinedtime) ? userdate($joinedtime) : '--';
                // $row->{$data->column} = !empty($joinedtime) ? userdate($joinedtime, get_string('strftimedatemonthabbr', 'core_langconfig')) : '--';
            break;
            case 'leavetime':
                if(!isset($row->leavetime) && isset($data->subquery)){
                    $leavetime =  $DB->get_field_sql($data->subquery);
                }else{
                    $leavetime = $DB->get_field_sql("SELECT leavedatetime FROM {teams_attendance_intervals} WHERE attendanceid = $row->id ORDER BY id DESC LIMIT 0, 1");
                }
                $row->{$data->column} = !empty($leavetime) ? userdate($leavetime) : '--';
            break;
            case 'duration': 
                $row->{$data->column} = !empty($row->duration) ? (new ls)->strTime($row->duration) : '--';
            break;
            case 'meetingname':
                $row->{$data->column} = !empty($row->meetingname) ? format_string($row->meetingname) : '--';
            break;
            // default:
            //     return (isset($row->{$data->column}))? $row->{$data->column} : ' -- ';
            //     break;

        }
        return (isset($row->{$data->column}))? $row->{$data->column} : ' -- ';
    }
}
