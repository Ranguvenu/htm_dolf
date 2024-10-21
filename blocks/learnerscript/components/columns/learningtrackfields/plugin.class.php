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

class plugin_learningtrackfields extends pluginbase {

    public function init() {
        $this->fullname = get_string('learningtrackfields', 'block_learnerscript');
        $this->type = 'advanced';
        $this->form = true;
        $this->reporttypes = array('learningtrackinfo');
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
            
            case 'competency':
                $competencies = $row->{$data->column};
                $comp = $DB->get_records_sql("SELECT id, name FROM {local_competencies} WHERE id IN ($competencies)"); 
                foreach ($comp as $c) {
                    $complist[] = $c->name;
                }
                $competencieslist = implode(',', $complist);
                $row->{$data->column} = !empty($complist) ? $competencieslist : '--';
            break;
            case 'organization':
                $organization = $row->{$data->column};
                if( $organization == 0){
                   $organizationlist = 'ALL';
                }else{
                    $organizations = $DB->get_records_sql("SELECT id, fullname FROM {local_organization} WHERE id IN ($organization)"); 
                    foreach ($organizations as $c) {
                        $organizationlist[] = $c->fullname;
                    }
                    $organizationlist = implode(',', $organizationlist);
                }
                $row->{$data->column} = !empty($organizationlist) ? $organizationlist : '--';
            break;
            case 'timecreated':
                $timecreated = $row->{$data->column}; 
                $row->{$data->column} = !empty($timecreated) ? date("Y-F-d", $timecreated)  : '--';
            break;
            case 'description':
                $row->{$data->column} = $row->description ? $row->description : '--';
            break;
            case 'status':
                if ($row->{$data->column} == 1) {
                    $row->{$data->column} = get_string('approve', 'block_learnerscript');
                } else if ($row->{$data->column} == 2) {
                    $row->{$data->column} = get_string('completed', 'block_learnerscript');
                } else if ($row->{$data->column} == 3) {
                    $row->{$data->column} = get_string('rejected', 'block_learnerscript');
                } else {
                    $row->{$data->column} = get_string('pending', 'block_learnerscript');
                }
                $row->{$data->column} = $row->{$data->column} ? $row->{$data->column} : '--';
            break;
      
        }
        return (isset($row->{$data->column}))? $row->{$data->column} : ' -- ';
    }
}