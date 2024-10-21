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
  * @author: sowmya<sowmya@eabyas.in>
  * @date: 2016
  */
namespace block_learnerscript\lsreports;
use block_learnerscript\local\pluginbase;
use block_learnerscript\local\reportbase;
use context_system;
use moodle_url;
use html_writer;

class plugin_scheduledcolumn extends pluginbase{
    public function init(){
		$this->fullname = get_string('scheduledcolumn','block_learnerscript');
		$this->type = 'undefined';
		$this->form = true;
		$this->reporttypes = array('productinvoice');
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
	public function execute($data,$row,$user,$courseid,$starttime=0,$endtime=0){
        global $DB, $CFG;

		switch ($data->column) {
            case 'entityname';
                if($row->category == 1){
                	$category = $DB->get_field('tp_offerings', 'code', array('id' => $row->referenceid));
                }else if($row->category == 2){
                	if($lang == 'ar'){
                		$examquery = "SELECT ex.examnamearabic ";
                	}else{
                		$examquery = "SELECT ex.exam ";
                	}
                	$examquery .= " FROM {local_exam_profiles} ep  
            						JOIN {local_exams} ex ON ex.id = ep.examid
            						WHERE 1=1 AND ep.id = $row->referenceid";
                	$category = $DB->get_field_sql($examquery);

                }else if($row->category == 3){
                	if($lang == 'ar'){
                		$eventquery = "SELECT le.title ";
                	}else{
                		$eventquery = "SELECT le.titlearabic ";
                	}
                	$eventquery .= " FROM {local_events} le 
                					WHERE le.id = $row->referenceid";
                	$category = $DB->get_field_sql($eventquery);
                }else if($row->category == 4){
                	if($lang == 'ar'){
                		$grievancequery = "SELECT ex.examnamearabic ";
                	}else{
                		$grievancequery = "SELECT ex.exam ";
                	}
                	$grievancequery .= " FROM {local_exam_grievance} leg  
            						JOIN {local_exams} ex ON ex.id = leg.examid
            						WHERE 1=1 AND leg.id = $row->referenceid";
            		$category = $DB->get_record_sql($grievancequery);

                }else if($row->category == 5){
                	$category = $DB->get_field('local_learningtracks', 'name', array('id' => $row->referenceid));
                }else if($row->category == 6){
                	if($lang == 'ar'){
                		$attemptquery = "SELECT ex.examnamearabic ";
                	}else{
                		$attemptquery = "SELECT ex.exam ";
                	}
                	$attemptquery .= " FROM {local_exam_attempts} lea  
            						JOIN {local_exams} ex ON ex.id = lea.examid
            						WHERE 1=1 AND lea.id = $row->referenceid";
            		$category = $DB->get_field_sql($attemptquery);
                }else{
                	$category = '';
                }
                $row->{$data->column} = !empty($category) ? ($category) : '--';
            break;

			case 'oldscheduledate':
				if(isset($row->oldscheduledate) && $row->oldscheduledate != 0){
					$row->{$data->column} = date('d M Y',$row->oldscheduledate);
				}else{
					$row->{$data->column} = "--";
				}
			break;

			case 'newscheduledate':
				if(isset($row->newscheduledate) && $row->newscheduledate != 0){
					$row->{$data->column} = date('d M Y',$row->newscheduledate);
				}else{
					$row->{$data->column} = "--";
				}
			break;
			case 'scheduledon':
				if(isset($row->scheduledon) && $row->scheduledon != 0){
					$row->{$data->column} = date('d M Y',$row->scheduledon);
				}else{
					$row->{$data->column} = "--";
				}
			break;
		}
		return (isset($row->{$data->column}))? $row->{$data->column} : '';
	}
}
