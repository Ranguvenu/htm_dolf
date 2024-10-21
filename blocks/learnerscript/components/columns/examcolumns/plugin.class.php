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
use html_writer;
use context_system;

class plugin_examcolumns extends pluginbase {

    public function init() {
        $this->fullname = get_string('examcolumns', 'block_learnerscript');
        $this->type = 'undefined';
        $this->form = true;
        $this->reporttypes = array('exams');
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
        global $DB, $CFG, $USER;
        $context = context_system::instance();
        $profilereportID = $DB->get_field('block_learnerscript', 'id', array('type' => 'examprofiles'), IGNORE_MULTIPLE);
        switch ($data->column) {
            case 'profiles':
                if(!isset($row->profiles) && isset($data->subquery)){
                    $profiles =  $DB->get_field_sql($data->subquery);
                }else{
                    $profiles = $row->{$data->column};
                }
                $enrolcheckpermissions = empty($profilereportID) ? false : (new reportbase($profilereportID))->check_permissions($USER->id, $context);
                if(empty($profilereportID) || empty($enrolcheckpermissions)){
                    $profiles =  $profiles ;
                } else{
                    $profiles = html_writer::link("$CFG->wwwroot/blocks/learnerscript/viewreport.php?id=$profilereportID&filter_exam=$row->id", $profiles, array("target" => "_blank"));
                }
                $row->{$data->column} = !empty($profiles) ? $profiles : 0; 
            break;
            case 'enrollments':
                if(!isset($row->enrollments) && isset($data->subquery)){
                    $enrollments =  $DB->get_field_sql($data->subquery);
                }else{
                    $enrollments = $row->{$data->column};
                }
                if (!empty($enrollments)) { 
                    $enrollments = html_writer::link("$CFG->wwwroot/local/exams/examusers.php?id=$row->id", $enrollments, array("target" => "_blank"));
                } else {
                    $enrollments = 0;
                }
                $row->{$data->column} = $enrollments; 
            break;
            case 'completions':
                if(!isset($row->completions) && isset($data->subquery)){
                    $completions =  $DB->get_field_sql($data->subquery);
                }else{
                    $completions = $row->{$data->column};
                }
                $row->{$data->column} = !empty($completions) ? $completions : 0; 
            break;
        }
        return (isset($row->{$data->column}))? $row->{$data->column} : ' -- ';
    }
}
