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
 * LearnerScript
 * A Moodle block for creating customizable reports
 * @package blocks
 * @author: Jahnavi Nanduri
 * @date: 2022
 */
namespace block_learnerscript\lsreports;
use block_learnerscript\local\reportbase;
use block_learnerscript\report;
use block_learnerscript\local\querylib;
use block_learnerscript\local\ls as ls;
use context_system;

defined('MOODLE_INTERNAL') || die();
class report_trainingprograms extends reportbase implements report {

    public function __construct($report, $reportproperties) {
        global $DB;
        parent::__construct($report, $reportproperties);
        $columns = ['trainingprogram', 'enrollments', 'completions', 'offerings', 'activeoffering', 'sector', 'status'];
        $this->columns = ['trainingprogramfields' => ['trainingprogramfields'], 'trainingprogramcolumns' => $columns];
        $this->components = array('columns', 'conditions', 'ordering', 'filters','permissions', 'plot');
        $this->filters = array('programfilters', 'competency', 'learningtrack');
        $this->parent = true;
        $this->orderable = array('trainingprogram', 'enrollments', 'completions', 'offerings', 'activeoffering');

        $lang= current_language();
        if( $lang == 'ar'){
            $this->searchable = array('ltp.namearabic');
        } else{
            $this->searchable = array('ltp.name');
        }

        $this->defaultcolumn = 'ltp.id';
        $this->excludedroles = array("'manager', 'coursecreator', 'editingteacher', 'teacher', 'student', 'examofficial', 'em', 'co', 'expert', 'trainee', 'competencies_official', 'cpd', 'hall_manager', 'financial_manager'");
    }

    public function init() {
        if (!$this->scheduling && isset($this->basicparams) && !empty($this->basicparams)) {
            $basicparams = array_column($this->basicparams, 'name');
            foreach ($basicparams as $basicparam) {
                if (empty($this->params['filter_' . $basicparam])) {
                    return false;
                }
            }
        } 
    }
    public function count() {
       $this->sql = "SELECT COUNT(DISTINCT ltp.id)";

    }

    public function select() {

        $lang= current_language();
        if( $lang == 'ar'){
            $this->sql = "SELECT DISTINCT ltp.id , ltp.*, ltp.namearabic AS trainingprogram, ltp.sectors AS sector ";
        } else{
            $this->sql = "SELECT DISTINCT ltp.id , ltp.*, ltp.name AS trainingprogram, ltp.sectors AS sector ";
        }

        parent::select();
    }

    public function from() {
        $this->sql .= " FROM {local_trainingprogram} ltp";
        // if (!empty($this->params['filter_competency']) && $this->params['filter_competency'] > 0) {           
        //     $this->sql .= " JOIN {local_competencypc_obj} lcc ON FIND_IN_SET(ltp.id, lcc.trainingprogramids) ";
        // }
        if (!empty($this->params['filter_learningtrack']) && $this->params['filter_learningtrack'] > 0) { 
            $this->sql .= " JOIN {local_learning_items} lli ON lli.itemid = ltp.id AND lli.itemtype = 1 ";
        }
    }
    public function joins() {
        global $DB,$USER;
        $systemcontext = context_system::instance();
        $trainerroleid = $DB->get_field('role', 'id', array('shortname' => 'trainer'));
        if(is_siteadmin() || has_capability('local/organization:manage_trainingofficial',$systemcontext)){
            $this->sql .= " "; 
        } else if (!is_siteadmin() && has_capability('local/organization:manage_organizationofficial',$systemcontext)) {
            $this->sql .= " JOIN {tp_offerings} tpo ON tpo.trainingid = ltp.id
                            JOIN {course} c ON c.id = ltp.courseid
                            JOIN {tool_org_order_seats} oos ON oos.fieldid = tpo.id AND oos.tablename = 'tp_offerings' AND oos.orguserid = " . $USER->id;
        } else if(has_capability('local/organization:manage_trainee', $systemcontext) ||
                      has_capability('local/organization:manage_trainer', $systemcontext)) {
            $this->sql .= " JOIN {program_enrollments} ue ON ue.programid = ltp.id AND  ue.courseid = ltp.courseid AND ue.userid = ".$USER->id." AND ue.roleid = ".$trainerroleid. " AND ltp.published = 1";
        }
        parent::joins();
    }

    public function where() {
        $this->sql .= " WHERE 1 = 1";
        parent::where();
    }

    public function search() {
      global $DB;
        if (isset($this->search) && $this->search) {
            $statsql = array();
            foreach ($this->searchable as $key => $value) {
                $statsql[] =$DB->sql_like($value, "'%" . $this->search . "%'",$casesensitive = false,$accentsensitive = true, $notlike = false);
            }
            $fields = implode(" OR ", $statsql);          
            $this->sql .= " AND ($fields) ";
        }
    }

    public function filters() {
        global $DB;
        if (!empty($this->params['filter_competency']) && $this->params['filter_competency'] > 0) {
            $this->sql .= " AND FIND_IN_SET((:filter_competency), ltp.competencyandlevels)";
        }
        if (!empty($this->params['filter_learningtrack']) && $this->params['filter_learningtrack'] > 0) {
            $this->sql .= " AND lli.trackid IN (:filter_learningtrack)";
        }
        $trainingprogramfieldfilters =  $DB->get_columns('local_trainingprogram');
        foreach($trainingprogramfieldfilters AS $v=>$k){
            if(isset($this->params['filter_'.$v]) && $this->params['filter_'.$v] > 0 && $v != 'languages'){
                $filtervalue = $this->params['filter_'.$v];
                if (is_numeric($filtervalue) && $filtervalue > -1) { 
                    if($v == 'targetgroup' || $v == 'sectors'){
                        $this->sql .= " AND FIND_IN_SET((".$filtervalue."), ltp.$v)";
                    } else {
                        $this->sql .= " AND ltp.$v IN ('".$filtervalue."')";
                    }
                } else {  

                    if($v == 'description' || $v == 'program_agenda'|| $v == 'level'){
                        $this->sql .= " AND ltp.$v LIKE ('%".$filtervalue."%')";
                    } else if($v == 'availablefrom' || $v == 'availableto'){
                        if(isset($this->params['filter_'.$v]) && $this->params['filter_'.$v]['enabled'] == 1){
                            $datevalue = $this->params['filter_'.$v] ;                            
                            $date = mktime(0, 0, 0, $datevalue['month'], $datevalue['day'], $datevalue['year']);
                            $endate = mktime(23, 59, 59, $datevalue['month'], $datevalue['day'], $datevalue['year']);
                            if(!empty($date)){
                                $datav = " AND ltp.$v BETWEEN $date AND $endate ";
                                $this->sql .= $datav; 
                            }
                                              
                        }
                    } else{
                        $this->sql .= " AND ltp.$v IN ('".$filtervalue."')";
                    }
                }
            } else {
                if ($v == 'methods' || $v == 'evaluationmethods' || $v == 'languages' || $v == 'discount') {
                    if ($this->params['filter_'.$v] > -1) {
                        $filtervalue = empty($filtervalue) ? '0' : $filtervalue;
                        $this->sql .= " AND FIND_IN_SET((".$filtervalue."), ltp.$v)";
                    }

                }else if(($v == 'namearabic' || $v == 'name' || $v == 'code' || $v == 'trainingtype' || $v == 'clevels' || $v == 'description' ) && !empty($this->params['filter_'.$v])){
                    $filtervalue = $this->params['filter_'.$v];
                    if($v == 'trainingtype'){
                        $this->sql .= " AND FIND_IN_SET(('".$filtervalue."'), ltp.$v)";
                    }else if($v == 'description'){
                         $this->sql .= " AND ltp.$v LIKE ('%".$filtervalue."%')";
                    }else{
                        $this->sql .= " AND ltp.$v IN ('".$filtervalue."')";
                    }
                }
            }
        }
        if ($this->ls_startdate > 0 && $this->ls_enddate) {
            $this->params['ls_fstartdate'] = ROUND($this->ls_startdate);
            $this->params['ls_fenddate'] = ROUND($this->ls_enddate);
            $this->sql .= " AND ltp.availableto BETWEEN :ls_fstartdate AND :ls_fenddate ";
        }   
    }


    public function groupby() {

    }

    public function get_rows($trainingprograms) {
        return $trainingprograms;
    }

    public function column_queries($columnname, $trainingprogramid, $trainingprograms = null) { 
        global $DB, $USER;
        $where = " AND %placeholder% = $trainingprogramid"; 
        $systemcontext = context_system::instance();
        $trainerroleid = $DB->get_field('role', 'id', array('shortname' => 'trainer'));
        $trainersql = " ";
        $enrollsql = " ";
        $ofsql = " ";
        if(is_siteadmin() || has_capability('local/organization:manage_trainingofficial',$systemcontext)){
            $trainersql .= " "; 
            $ofsql .= " ";
        } else if (!is_siteadmin() && has_capability('local/organization:manage_organizationofficial',$systemcontext)) {
            $ofsql .= " JOIN {tool_org_order_seats} oos ON oos.fieldid = tpo.id AND oos.tablename = 'tp_offerings' AND oos.orguserid = " . $USER->id;
            $ofusql .= " JOIN {tool_org_order_seats} oos ON oos.fieldid = pe.offeringid AND oos.tablename = 'tp_offerings' AND oos.orguserid = " . $USER->id ;
            $organization = $DB->get_field('local_users','organization',array('userid'=>$USER->id));
            $ofusql .= " AND lu.organization = $organization ";
        } else if(has_capability('local/organization:manage_trainee', $systemcontext) ||
                      has_capability('local/organization:manage_trainer', $systemcontext)) {
            $trainersql .= " JOIN {program_enrollments} ue ON ue.programid = tpo.trainingid AND ue.userid = ".$USER->id." AND ue.roleid = ".$trainerroleid. " AND tpo.id = ue.offeringid";
            $enrollsql .= " JOIN {program_enrollments} ue ON ue.programid = pe.programid AND ue.userid = ".$USER->id." AND ue.roleid = ".$trainerroleid. " 
                            JOIN {tp_offerings} tpo ON tpo.trainingid = ue.programid AND tpo.id = ue.offeringid AND pe.offeringid = ue.offeringid";
        }
        switch ($columnname) {
            case 'enrollments':
                $identy = 'pe.programid';
                $query = "SELECT COUNT(DISTINCT pe.id) AS enrollments
                            FROM {program_enrollments} pe 
                            JOIN {role} r ON r.id = pe.roleid AND r.shortname = 'trainee' 
                            JOIN {local_users} lu ON lu.userid = pe.userid
                            $enrollsql  
                            $ofusql
                            WHERE 1 = 1 $where  AND lu.deleted = 0 AND lu.approvedstatus = 2 AND pe.enrolstatus = 1 ";
            break;
            case 'completions':
                $identy = 'pc.programid';
                $query = "SELECT COUNT(DISTINCT pc.id) AS completions
                            FROM {program_completions} pc 
                            JOIN {program_enrollments} pe ON pe.userid = pc.userid AND pe.programid = pc.programid 
                            JOIN {role} r ON r.id = pe.roleid AND r.shortname = 'trainee' 
                            JOIN {local_users} lu ON lu.userid = pe.userid
                            $enrollsql 
                            $ofusql
                            WHERE 1 = 1 AND pc.completion_status != 0 $where AND lu.deleted = 0 AND lu.approvedstatus = 2 AND pe.enrolstatus = 1  ";
            break;
            case 'offerings':
                $identy = 'tpo.trainingid';
                $query = "SELECT COUNT(DISTINCT tpo.id) AS offerings
                            FROM {tp_offerings} tpo 
                            $ofsql
                            WHERE 1 = 1 $where ";


            break;
            case 'activeoffering':
                $date = new \DateTime();
                $timestamp = $date->getTimestamp();
                $identy = 'tpo.trainingid';
                $query = "SELECT COUNT(DISTINCT tpo.id) AS offerings
                            FROM {tp_offerings} tpo 
                            $trainersql
                            $ofsql
                            WHERE 1 = 1 $where AND (tpo.enddate+tpo.endtime) > $timestamp";
            break;
            default:
            return false;
                break;
        }
        $query = str_replace('%placeholder%', $identy, $query);
        return $query;
    }
}
