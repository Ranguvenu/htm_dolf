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
 * @author: Revanth kumar grandhi
 * @date: 2022
 */
namespace block_learnerscript\lsreports;
use block_learnerscript\local\reportbase;
use block_learnerscript\report;
use block_learnerscript\local\querylib;
use block_learnerscript\local\ls as ls;
use context_system;

defined('MOODLE_INTERNAL') || die();
class report_usercpd extends reportbase implements report {

    public function __construct($report, $reportproperties) {
        global $DB, $USER;
        $systemcontext = context_system::instance();
        parent::__construct($report, $reportproperties);
        $columns = ['cpdname', 'evidencetype', 'submittedhours', 'timecreated', 'status'];
        $this->columns = ['usercpdcolumns' => $columns];
        $this->components = array('columns', 'conditions', 'ordering', 'filters','permissions', 'plot');
        $this->parent = true;
        $this->orderable = array('cpdname','timecreated', 'status');

        if (is_siteadmin() || $this->role != 'trainee') {
            $this->basicparams = [['name' => 'users']];
        }

        $this->searchable = array('le.exam', 'le.examnamearabic');
        $this->defaultcolumn = 'lc.id';
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
    }
    public function count() {
       $this->sql = "SELECT COUNT(DISTINCT lce.id)";

    }
    public function select() {
        $lang= current_language();
        if( $lang == 'ar'){
            $this->sql = "SELECT DISTINCT lce.id , le.examnamearabic AS cpdname, lce.*, lce.evidencetype as type ";
        } else{
            $this->sql = "SELECT DISTINCT lce.id , le.exam AS cpdname, lce.*, lce.evidencetype as type ";
        }

        parent::select();
    }

    public function from() {
        $this->sql .= " FROM {local_cpd_evidence} lce
                        JOIN {local_cpd} lc ON lce.cpdid = lc.id
                        JOIN {local_exams} le ON le.id = lc.examid  ";
    }
    public function joins() {
        parent::joins();
    }

    public function where() {
        global $USER;
        $userid = isset($this->params['filter_users']) && $this->params['filter_users'] > 0
                    ? $this->params['filter_users'] : $this->userid;
        $this->params['userid'] = $userid;
        $this->sql .= " WHERE 1 = 1 AND lce.userid = :userid ";
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
            $this->sql .= " AND lc.timecreated BETWEEN :ls_fstartdate AND :ls_fenddate ";
        } 
    }


    public function groupby() {

    }

    public function get_rows($cpd) {
        return $cpd;
    }

    public function column_queries($columnname, $cpdid, $cpd = null) { 
        global $DB;
        $where = " AND %placeholder% = $cpdid";
        // switch ($columnname) {
        //     case 'submitted':
        //         $identy = 'lce.id';
        //         $query = " SELECT timecreated 
        //                     FROM {local_cpd_evidence} lce  
        //                    WHERE 1=1 $where";
        //         break;
        //     default:
        //     return false;
        //     break;
        // }
        $query = str_replace('%placeholder%', $identy, $query);
        return $query;
    }
}
