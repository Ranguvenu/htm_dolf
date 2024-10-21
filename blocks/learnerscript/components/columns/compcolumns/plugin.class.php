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
use context_system;
use html_writer;

class plugin_compcolumns extends pluginbase {

    public function init() {
        $this->fullname = get_string('compcolumns', 'block_learnerscript');
        $this->type = 'undefined';
        $this->form = true;
        $this->reporttypes = array('competencies');
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
            case 'exams':
                $examsreportID = $DB->get_field('block_learnerscript', 'id', array('type' => 'compexams'), IGNORE_MULTIPLE);
                $exams = $DB->get_records_sql("SELECT id FROM {local_exams} WHERE 1 = 1 AND FIND_IN_SET($row->id, competencies)");
                $examscount = !empty($exams) ? COUNT($exams) : 0; 
                $enrolcheckpermissions = empty($examsreportID) ? false : (new reportbase($examsreportID))->check_permissions($USER->id, $context);
                if(empty($examsreportID) || empty($enrolcheckpermissions)){
                    $row->{$data->column} =  $examscount ;
                } else{
                    $row->{$data->column} = html_writer::link("$CFG->wwwroot/blocks/learnerscript/viewreport.php?id=$examsreportID&filter_competency=$row->id", $examscount, array("target" => "_blank"));
                }
            break;
            case 'programs':
                $programsreportID = $DB->get_field('block_learnerscript', 'id', array('type' => 'compprograms'), IGNORE_MULTIPLE);
                $programs = $DB->get_records_sql("SELECT id FROM {local_trainingprogram} WHERE 1 = 1 AND FIND_IN_SET($row->id, competencyandlevels)");
                $programscount = !empty($programs) ? COUNT($programs) : 0; 
                $enrolcheckpermissions = empty($programsreportID) ? false : (new reportbase($programsreportID))->check_permissions($USER->id, $context);
                if(empty($programsreportID) || empty($enrolcheckpermissions)){
                    $row->{$data->column} =  $programscount ;
                } else{
                    $row->{$data->column} = html_writer::link("$CFG->wwwroot/blocks/learnerscript/viewreport.php?id=$programsreportID&filter_competency=$row->id", $programscount, array("target" => "_blank"));
                }
            break;
        }
        return (isset($row->{$data->column}))? $row->{$data->column} : ' -- ';
    }
}
