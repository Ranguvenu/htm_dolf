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
 * @author: Sudharani Sadula
 * @date: 2022
 */
namespace block_learnerscript\lsreports;
use block_learnerscript\local\pluginbase;
use block_learnerscript\local\reportbase;
use html_writer;

class plugin_productlogcolumns extends pluginbase {
	public const TRAINING_PROGRAM = 1;
    public const EXAMS = 2;
    public const EVENTS = 3;
    public const GRIEVANCE = 4;
    public const LEARNINGTRACKS = 5;
    public const EXAMATTEMPT = 6;

    public function init() {
        $this->fullname = get_string('productlogcolumns', 'block_learnerscript');
        $this->type = 'undefined';
        $this->form = true;
        $this->reporttypes = array('productlog');
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
         $lang= current_language();
        switch ($data->column) {
        	case 'username';
                if(isset($row->username) && !empty($row->username)) {
                    $row->{$data->column} =  $row->username;
                } else {
                	if($lang == 'ar'){
                		 $username = $DB->get_field('local_users', "CONCAT(firstnamearabic, ' ', lastnamearabic)", array('userid' => $row->userid));
                	}else{
                		 $username = $DB->get_field('local_users', "CONCAT(firstname, ' ', lastname)", array('userid' => $row->userid));
                	}
                   
                }
               $row->{$data->column} = !empty($username) ? ($username) : '--';
            break;
            case 'learningitem';
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
            case 'learningtype':
            	if(isset($row->learningtype)){
            		$row->{$data->column} = !empty($row->learningtype) ? ($row->learningtype) : 'NA';
            	}else{
            		$row->{$data->column} = 'NA';
            	}
            break;
            case 'reason':
            	if(isset($row->reason)){
            		$row->{$data->column} = !empty($row->reason) ? ($row->reason) : 'NA';
            	}else{
            		$row->{$data->column} = 'NA';
            	}
            break;            
            case 'policy':
            	if($row->plolicy == 1){
            		$row->{$data->column} = get_string('policyactivated', 'block_learnerscript');
            	}else{
            		$row->{$data->column} = get_string('policyinactivated', 'block_learnerscript');
            	}
            break;
        }
        return (isset($row->{$data->column}))? $row->{$data->column} : ' -- ';
    }
}
