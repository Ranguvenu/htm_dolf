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
class report_exams extends reportbase implements report {

    public function __construct($report, $reportproperties) {
        global $DB;
        parent::__construct($report, $reportproperties);
        $columns = ['examname','profiles', 'enrollments', 'completions', 'courseid', 'quizid', 'targetgroup'];
        $this->columns = ['examfields' => ['examfields'], 'examcolumns' => $columns];
        $this->components = array('columns', 'conditions', 'ordering', 'filters','permissions', 'plot');
        $this->filters = array('examfilters', 'competency', 'learningtrack');
        $this->parent = true;
        $this->orderable = array('examname', 'enrollments', 'completions');

        $lang= current_language();
        if( $lang == 'ar'){
            $this->searchable = array('le.examnamearabic');
        } else{
            $this->searchable = array('le.exam');
        }

        $this->defaultcolumn = 'le.id';
        $this->excludedroles = array("'manager', 'coursecreator', 'editingteacher', 'teacher', 'student', 'trainer', 'em', 'co', 'expert', 'trainee', 'to', 'competencies_official', 'cpd', 'hall_manager', 'financial_manager'");
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
       $this->sql = "SELECT COUNT(DISTINCT le.id)";

    }

    public function select() {

        $lang= current_language();
        if( $lang == 'ar'){
            $this->sql = "SELECT DISTINCT le.id , le.examnamearabic AS examname, le.*, le.sectors AS sectorid";
        } else{
            $this->sql = "SELECT DISTINCT le.id , le.exam AS examname, le.*, le.sectors AS sectorid";
        }

        parent::select();
    }

    public function from() {
        $this->sql .= " FROM {local_exams} le ";
        // if (!empty($this->params['filter_competency']) && $this->params['filter_competency'] > 0) {           
        //     $this->sql .= " JOIN {local_competencypc_obj} lcc ON FIND_IN_SET(le.id, lcc.examids) ";
        // }
        if (!empty($this->params['filter_learningtrack']) && $this->params['filter_learningtrack'] > 0) { 
            $this->sql .= " JOIN {local_learning_items} lli ON lli.itemid = le.id AND lli.itemtype = 2 ";
        }
    }
    public function joins() {
        global $DB,$USER;
        $systemcontext = context_system::instance();
        if (is_siteadmin()) {

        } else if (!is_siteadmin() && has_capability('local/organization:manage_organizationofficial',$systemcontext)) {
            $this->sql .= " JOIN {local_exam_profiles} lep ON lep.examid = le.id 
                            JOIN {tool_org_order_seats} oos ON oos.fieldid = lep.id AND oos.tablename = 'local_exam_profiles' AND oos.orguserid = " . $USER->id;
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
            $this->sql .= " AND FIND_IN_SET((:filter_competency), le.competencies)";
        }
        if (!empty($this->params['filter_learningtrack']) && $this->params['filter_learningtrack'] > 0) {
            $this->sql .= " AND lli.trackid IN (:filter_learningtrack)";
        }
        $profilefields =  $DB->get_columns('local_exams');
        foreach ($profilefields as $key => $value) {
            if (!empty($this->params['filter_'.$key]) && $this->params['filter_'.$key] > -1) {
                $filtervalue = $this->params['filter_'.$key];
                if (is_numeric($filtervalue)){
                    $this->sql .= " AND le.$key IN ('".$filtervalue."')";
                } else {
                    $this->sql .= " AND le.$key LIKE ('%".$filtervalue."%')";
                }
            } else if($key == 'requirements' && $this->params['filter_'.$key] > 0) {
                $filtervalue = $this->params['filter_'.$key];
                $this->sql .= " AND le.id IN ('".$filtervalue."')";
            }else {
                if ($key == 'approvedstatus' || $key == 'id_type' || $key == 'status') {
                    if ($this->params['filter_'.$key] > -1) {
                        $filtervalue = empty($filtervalue) ? '0' : $filtervalue;
                        $this->sql .= " AND FIND_IN_SET((".$filtervalue."), le.$key)";
                    }
                }
            }
        }
        if ($this->ls_startdate > 0 && $this->ls_enddate) {
            $this->params['ls_fstartdate'] = ROUND($this->ls_startdate);
            $this->params['ls_fenddate'] = ROUND($this->ls_enddate);
            $this->sql .= " AND le.timecreated BETWEEN :ls_fstartdate AND :ls_fenddate ";
        }
    }


    public function groupby() {

    }

    public function get_rows($exams) {
        return $exams;
    }

    public function column_queries($columnname, $examid, $exams = null) { 

        $where = " AND %placeholder% = $examid";
        global $DB,$USER;
        $systemcontext = context_system::instance();
        $enrollments = " ";
        if (is_siteadmin()) {
            $enrollments = " ";
        } else if (!is_siteadmin() && has_capability('local/organization:manage_organizationofficial',$systemcontext)) {
            $organization = $DB->get_field('local_users','organization',array('userid'=>$USER->id));
            $enrollments .= " AND lu.organization = $organization ";
        }
        switch ($columnname) {
            case 'profiles':
                $identy = 'lep.examid';
                $query = "SELECT COUNT(DISTINCT lep.id) AS profiles
                            FROM {local_exam_profiles} lep 
                            WHERE 1 = 1 $where";
            break;
            case 'enrollments':
                $identy = 'ee.examid';
                $query = "SELECT COUNT(DISTINCT ee.id) AS enrollments
                            FROM {exam_enrollments} ee 
                            JOIN {local_users} lu ON lu.userid = ee.userid
                            WHERE 1 = 1 $where $enrollments AND lu.deleted = 0 AND lu.approvedstatus = 2 AND ee.enrolstatus = 1";
            break;
            case 'completions':
                $identy = 'ec.examid';
                $query = "SELECT COUNT(DISTINCT ec.id) AS completions 
                            FROM {exam_completions} ec 
                            JOIN {exam_enrollments} ee ON ee.userid = ec.userid AND ee.examid AND ec.examid 
                            JOIN {local_users} lu ON lu.userid = ec.userid
                            WHERE 1 = 1 AND ec.completiondate != 0 $where $enrollments AND lu.deleted = 0 AND lu.approvedstatus = 2 AND ee.enrolstatus = 1";
            break;
            default:
            return false;
                break;
        }
        $query = str_replace('%placeholder%', $identy, $query);
        return $query;
    }
}
