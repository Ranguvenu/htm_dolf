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
 * @author: Revanth kumar Grandhi
 * @date: 2022
 */
namespace block_learnerscript\lsreports;
use block_learnerscript\local\pluginbase;
use html_writer;

class plugin_hallcolumns extends pluginbase {

    public function init() {
        $this->fullname = get_string('hallcolumns', 'block_learnerscript');
        $this->type = 'undefined';
        $this->form = true;
        $this->reporttypes = array('halls');
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
            case 'hallname':
                if(!isset($row->hallname) && isset($data->subquery)){
                    $hallname =  $DB->get_field_sql($data->subquery);
                }else{
                    $hallname = $row->{$data->column};
                }
                $row->{$data->column} = !empty($hallname) ? $hallname : 0; 
            break;
            case 'reservations':
                if(!isset($row->reservations) && isset($data->subquery)){
                    $reservations =  $DB->get_field_sql($data->subquery);
                }else{
                    $reservations = $row->{$data->column};
                }
                if (!empty($reservations)) { 
                    $reservations = html_writer::link("$CFG->wwwroot/local/hall/hallinfo.php?id=$row->id", $reservations, array("target" => "_blank"));
                } else {
                    $reservations = 0;
                }
                $row->{$data->column} = $reservations; 
            break;
        }
        return (isset($row->{$data->column}))? $row->{$data->column} : ' -- ';
    }
}
