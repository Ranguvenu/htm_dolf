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
use block_learnerscript\local\ls;

class plugin_compfields extends pluginbase {

    public function init() {
        $this->fullname = get_string('compfields', 'block_learnerscript');
        $this->type = 'advanced';
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
        global $DB;
        switch ($data->column) {
           case 'type':            
            $competencytypes = array('corecompetencies', 'technicalcompetencies', 'behavioralcompetencies', 'other');
            if (in_array($row->{$data->column}, $competencytypes)) {
                $row->{$data->column} = !empty($row->{$data->column}) ? get_string($row->{$data->column}, 'local_competency') : '--';
            } else {
                $row->{$data->column} = !empty($row->{$data->column}) ? $row->{$data->column} : '--';
            }
           break; 
           case 'timecreated':
           case 'timemodified':
           case 'usermodified':
                $row->{$data->column} = !empty($row->{$data->column}) ? userdate($row->{$data->column},get_string('strftimedatemonthabbr', 'core_langconfig')) : '--';            
           break;
           case 'level':
                if (!empty($row->{$data->column})) {
                    $complevels = explode(',', $row->{$data->column});
                    foreach ($complevels as $complevel) {
                        $clevels[] = get_string($complevel, 'local_competency');
                    }
                    $row->{$data->column} = implode(', ', $clevels);
                }
                $row->{$data->column} = !empty($row->{$data->column}) ? $row->{$data->column} : '--';
           break;
           case 'jobroleid':
            $jobroleid = $row->{$data->column}; 
            $lang= current_language();
            if ($lang == 'ar') {
                $jobroletitle = $DB->get_field_sql("SELECT CONCAT(code, ' ', titlearabic) FROM {local_jobrole_level} WHERE id = $jobroleid");
            } else {
                $jobroletitle = $DB->get_field_sql("SELECT CONCAT(code, ' ', title) FROM {local_jobrole_level} WHERE id = $jobroleid");
            }
            $row->{$data->column} = !empty($jobroletitle) ? $jobroletitle : '--';
           break;
           case 'usercreated':
                $userid = $row->{$data->column};
                if (!empty($userid)) { 
                    $row->{$data->column} = $DB->get_field_sql("SELECT CONCAT(u.firstname, ' ', u.lastname) FROM {user} u WHERE u.id = $userid");
                }
                $row->{$data->column} = !empty($row->{$data->column}) ? $row->{$data->column} : '--';
            break;

        }
        return (isset($row->{$data->column}))? $row->{$data->column} : ' -- ';
    }
}
