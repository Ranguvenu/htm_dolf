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

defined('MOODLE_INTERNAL') || die();
class report_myexams extends reportbase implements report {

    public function __construct($report, $reportproperties) {
        global $DB;
        parent::__construct($report, $reportproperties);
        $columns = ['exam', 'profile', 'completiondate', 'status'];
        $this->columns = ['myexamcolumns' => $columns];
        $this->components = array('columns', 'conditions', 'ordering', 'filters','permissions', 'plot');
        if (is_siteadmin() || $this->role != 'trainee') {
            $this->basicparams = [['name' => 'users']];
        }
        $this->filters = array();
        $this->parent = true;
        $this->orderable = array('');

        $lang= current_language();
        if( $lang == 'ar'){
            $this->searchable = array('le.examnamearabic');
        } else{
            $this->searchable = array('le.exam');
        }
        
        $this->defaultcolumn = 'le.id';
        $this->excludedroles = array("'manager', 'coursecreator', 'editingteacher', 'teacher', 'student', 'trainer', 'examofficial', 'em', 'co', 'expert', 'organizationofficial', 'to', 'competencies_official', 'cpd', 'hall_manager', 'financial_manager'");
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
        if($this->role != 'trainee' && !isset($this->params['filter_users'])){
            $this->initial_basicparams('users');
            $fusers = array_keys($this->filterdata);
            $this->params['filter_users'] = array_shift($fusers);
        }
    }
    public function count() {
       $this->sql = "SELECT COUNT(DISTINCT le.id)";

    }

    public function select() { 
        $lang= current_language();
        if( $lang == 'ar'){
            $this->sql = "SELECT DISTINCT le.id, le.examnamearabic AS exam, ec.completion_status AS status, ec.completiondate, lep.profilecode AS profile";
        } else {
            $this->sql = "SELECT DISTINCT le.id, le.exam, ec.completion_status AS status, ec.completiondate, lep.profilecode AS profile";
        }
        parent::select();
    }

    public function from() {
        $this->sql .= " FROM {local_exams} le ";
    }
    public function joins() {
        $this->sql .= " JOIN {exam_enrollments} ee ON le.id = ee.examid
                        JOIN {local_exam_profiles} lep ON lep.examid = le.id AND ee.profileid = lep.id 
                        LEFT JOIN {exam_completions} ec ON ec.examid = le.id AND ec.userid = ee.userid";
        parent::joins();
    }

    public function where() { 
        $userid = isset($this->params['filter_users']) && $this->params['filter_users'] > 0
                    ? $this->params['filter_users'] : $this->userid;
        $this->params['userid'] = $userid;
        $this->sql .= " WHERE 1 = 1 AND ee.userid = :userid ";
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
        if ($this->ls_startdate >= 0 && $this->ls_enddate) {
            $this->params['ls_fstartdate'] = ROUND($this->ls_startdate);
            $this->params['ls_fenddate'] = ROUND($this->ls_enddate);
            $this->sql .= " AND le.timecreated BETWEEN :ls_fstartdate AND :ls_fenddate ";
        } 
    }


    public function groupby() {
        $this->sql .= " GROUP BY le.id";

    }

    public function get_rows($competencies) {
        return $competencies;
    }

    public function column_queries($columnname, $competencyid, $competencies = null) { 
        // global $DB;
        // $where = " AND %placeholder% = $competencyid";
        // $userid = isset($this->params['filter_users']) && $this->params['filter_users'] > 0
        //             ? $this->params['filter_users'] : $this->userid;
        // switch ($columnname) {
        //     case 'status':
        //        $identy = 'lcc.competencyid'; 
        //        $query = "SELECT COUNT(DISTINCT lcc.id) AS status
        //                     FROM {local_cmtncy_completions} lcc 
        //                     WHERE 1 = 1 $where AND lcc.userid = $userid";
        //     break;
        //     default:
        //     return false;
        //         break;
        // }
        // $query = str_replace('%placeholder%', $identy, $query);
        // return $query;
    }
}
