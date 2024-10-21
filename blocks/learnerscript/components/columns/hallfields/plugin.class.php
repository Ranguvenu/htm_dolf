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
use block_learnerscript\local\ls;

class plugin_hallfields extends pluginbase {

    public function init() {
        $this->fullname = get_string('hallfields', 'block_learnerscript');
        $this->type = 'advanced';
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
        $types = [1 => get_string('trainingcourse','local_hall'), 2 => get_string('atest','local_hall'), 3 => get_string('effectiveness','local_hall'), 4 => get_string('other','local_hall')];
        $roomshape = ['1' => get_string('circle','local_hall'), '2' => get_string('rectangle','local_hall'), '3' => get_string('square','local_hall')];
        switch ($data->column) {
            case 'type': 
                $row->{$data->column} = !empty($row->{$data->column}) ? $types[$row->{$data->column}] : '--';
            break;
            case 'city':
                $city = $row->{$data->column};
                $row->{$data->column} = !empty($row->{$data->column}) ? get_string($city, 'local_hall') : '--';
            break;
            case 'maplocation':
                $maplocation = $row->{$data->column};

                if (filter_var($maplocation, FILTER_VALIDATE_URL)) {
                    $location = '<a href='.$row->{$data->column}.'>'. get_string('view_on_map', 'local_hall') .'</a>';
                } else {
                    $location = $row->{$data->column};
                }

                $row->{$data->column} = !empty($row->{$data->column}) ? $location : '--';
            break;
            case 'roomshape':
                $row->{$data->column} = !empty($row->{$data->column}) ? $roomshape[$row->{$data->column}] : '--';
            break;

            case 'hallstarttime':
                $hallstartime = date('h:i A', mktime(0, 0, $row->{$data->column}));
                $row->{$data->column} = !empty($row->{$data->column}) ? $hallstartime : '--';
            break;

            case 'hallendtime':
                $hallendtime = date('h:i A', mktime(0, 0, $row->{$data->column}));
                $row->{$data->column} = !empty($row->{$data->column}) ? $hallendtime : '--';
            break;

            case 'description':
                $description = format_text($row->{$data->column});
                $row->{$data->column} = !empty($row->{$data->column}) ? $description : '--';
            break;

            case 'equipmentavailable':
                    $equipments = ['1' => get_string('projector','local_hall'), '2' => get_string('earphone','local_hall'), '3' => get_string('television','local_hall')];
                    $equipmentsdata = explode(',', $data);
                    $data = [];
                    foreach($equipmentsdata as $equipment) {
                        $data[] = $equipments[$row->{$data->column}];
                    }
                    $equipmentavailable = implode(',', $data);
                $row->{$data->column} = !empty($row->{$data->column}) ? $equipmentavailable : '--';
            break;
        }
        return (isset($row->{$data->column}))? $row->{$data->column} : ' -- ';
    }
}