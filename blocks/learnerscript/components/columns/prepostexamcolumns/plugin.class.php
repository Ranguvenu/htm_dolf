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
  * @author: jahnavi<jahnavi@eabyas.com>
  * @date: 2022
  */
namespace block_learnerscript\lsreports;
use block_learnerscript\local\pluginbase;
use block_learnerscript\local\ls;
use block_learnerscript\local\reportbase;
use context_system;
use html_writer;
class plugin_prepostexamcolumns extends pluginbase{
    public function init(){
        $this->fullname = get_string('prepostexamcolumns','block_learnerscript');
        $this->type = 'undefined';
        $this->form = true;
        $this->reporttypes = array('prepostexams');
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
    public function execute($data,$row,$user,$courseid,$starttime=0,$endtime=0,$reporttype){
        global $DB, $CFG;
        switch ($data->column) {
            case 'notattemptedusers':
                if (!isset($row->notattemptedusers)) {
                    $notattemptedusers =  $DB->get_field_sql($data->subquery);
                } else {
                    $notattemptedusers = $row->{$data->column};
                } 
                $row->{$data->column} = !empty($notattemptedusers) ? $notattemptedusers : 0;
                break;
            case 'inprogressusers':
                if (!isset($row->inprogressusers)) {
                    $inprogressusers =  $DB->get_field_sql($data->subquery);
                } else {
                    $inprogressusers = $row->{$data->column};
                }
                if (!empty($inprogressusers)) {
                    $row->{$data->column} = html_writer::link("$CFG->wwwroot/mod/quiz/report.php?id=$row->activityid&mode=overview", $inprogressusers, array("target" => "_blank"));
                } else {
                    $row->{$data->column} = 0;
                }
                break;
            case 'completedusers':
                if (!isset($row->completedusers)) {
                    $completedusers =  $DB->get_field_sql($data->subquery);
                } else {
                    $completedusers = $row->{$data->column};
                }
                if (!empty($completedusers)) {
                    $row->{$data->column} = html_writer::link("$CFG->wwwroot/mod/quiz/report.php?id=$row->activityid&mode=overview", $completedusers, array("target" => "_blank"));
                } else {
                    $row->{$data->column} = 0;
                }               
            break;
        case 'gradepass':
                if (!isset($row->gradepass)) {
                    $gradepass =  $DB->get_field_sql($data->subquery);
                } else {
                    $gradepass = $row->{$data->column};
                }
                if($reporttype == 'table'){
                        $row->{$data->column} = !empty($gradepass) ? ROUND($gradepass, 2) : '--';
                }else{
                        $row->{$data->column} = !empty($gradepass) ? ROUND($gradepass, 2) : 0;
                }
        break;
        case 'grademax':
                if (!isset($row->grademax)) {
                    $grademax =  $DB->get_field_sql($data->subquery);
                } else {
                    $grademax = $row->{$data->column};
                }
                if($reporttype == 'table'){
                        $row->{$data->column} = !empty($grademax) ? ROUND($grademax, 2) : '--';
                }else{
                        $row->{$data->column} = !empty($grademax) ? ROUND($grademax, 2) : 0;
                }
        break;
         case 'avggrade':
                if (!isset($row->avggrade)) {
                    $avggrade =  $DB->get_field_sql($data->subquery);
                } else {
                    $avggrade = $row->{$data->column};
                }
                if($reporttype == 'table'){
                        $row->{$data->column} = !empty($avggrade) ? ROUND($avggrade, 2) : '--';
                }else{
                        $row->{$data->column} = !empty($avggrade) ? ROUND($avggrade, 2) : 0;
                }
        break;
        }
        return (isset($row->{$data->column})) ? $row->{$data->column} : ' ';
    }
}
