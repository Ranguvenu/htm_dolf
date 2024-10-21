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

class plugin_questionbankfields extends pluginbase {

    public function init() {
        $this->fullname = get_string('questionbankfields', 'block_learnerscript');
        $this->type = 'advanced';
        $this->form = true;
        $this->reporttypes = array('questionbankinfo');
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
            case 'status': 
                if ($row->movedtoprod == 1) { 
                    $row->{$data->column} = get_string('completed', 'block_learnerscript');
                } else {
                    if ($row->{$data->column} == 1) {
                        $row->{$data->column} = get_string('active', 'block_learnerscript');
                    } else {
                        $row->{$data->column} = get_string('inactive', 'block_learnerscript');
                    }
                }
                $row->{$data->column} = !empty($row->{$data->column}) ? $row->{$data->column} : '--';
            break;
            case 'workshopadmin':
                $adminslist = $row->{$data->column};
                $admininfo = $DB->get_fieldset_sql("SELECT CONCAT(firstname,' ',lastname) as fullname FROM {user} WHERE id IN ($adminslist)");
                $admininfo = implode(',', $admininfo);
                $row->{$data->column} = !empty( $admininfo) ? $admininfo : '--';
            break;
            case 'competency':
                $competencies = $row->{$data->column};
                $comp = $DB->get_records_sql("SELECT id, name FROM {local_competencies} WHERE id IN ($competencies)"); 
                foreach ($comp as $c) {
                    $complist[] = $c->name;
                }
                $competencieslist = implode(',', $complist);
                $row->{$data->column} = !empty($complist) ? $competencieslist : '--';
            break;
            case 'course':
                $course = $row->{$data->column};
                $courses = $DB->get_records_sql("SELECT id, fullname FROM {course} WHERE id IN ($course)"); 
                foreach ($courses as $c) {
                    $courseslist[] = $c->fullname;
                }
                $courseslist = implode(',', $courseslist);
                $row->{$data->column} = !empty($courseslist) ? $courseslist : '--';
            break;
            case 'halladdress':
                $halladdress = $row->{$data->column};
                $hallinfo = $DB->get_field('hall','name',array('id'=>$halladdress)); 
                $row->{$data->column} = !empty($hallinfo) ? $hallinfo : '--';
            break;
            case 'tocategoryid':
                $tocategoryid = $row->{$data->column}; 
                $row->{$data->column} = !empty($tocategoryid) ? $DB->get_field('question_categories','name',array('id'=>$tocategoryid))  : '--';
            break;//   gmdate("H:i A",$questionbank->workshopstarttime); 
            case 'workshopdate':
                $workshopdate = $row->{$data->column}; 
                $row->{$data->column} = !empty($workshopdate) ? userdate($workshopdate,get_string('strftimedatemonthabbr', 'core_langconfig'))  : '--';
            break;
            case 'workshopstarttime':
                $workshopstarttime = $row->{$data->column}; 
                $row->{$data->column} = !empty($workshopstarttime) ?  gmdate("H:i A",$workshopstarttime)  : '--';
            break;
            case 'duration':
                $minutes = intval($values/60); 
                $min = $row->{$data->column};
                $row->{$data->column} = !empty( $min) ? intval( $min/60).' Mins'  : '--';
            break;
      
        }
        return (isset($row->{$data->column}))? $row->{$data->column} : ' -- ';
    }
}
