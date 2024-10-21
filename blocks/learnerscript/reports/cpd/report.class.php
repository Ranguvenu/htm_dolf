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
use external_settings;
require_once($CFG->libdir.'/externallib.php');


defined('MOODLE_INTERNAL') || die();
class report_cpd extends reportbase implements report {

    public function __construct($report, $reportproperties) {
        global $DB;
        parent::__construct($report, $reportproperties);
        $columns = ['cpdname', 'submitted', 'completions','status'];
        $this->columns = ['cpdfields' => ['cpdfields'], 'cpdcolumns' => $columns, 'examid'];
        $this->components = array('columns', 'conditions', 'ordering', 'filters','permissions', 'plot');
        $this->filters = array('cpdfilters');
        $this->parent = true;
        $this->orderable = array('cpdname','submitted','completions','status');

        $this->searchable = array('le.exam', 'le.examnamearabic');
        $this->defaultcolumn = 'lc.id';
        $this->excludedroles = array("'manager', 'coursecreator', 'editingteacher', 'teacher', 'student', 'trainer', 'examofficial', 'em', 'co', 'expert', 'organizationofficial', 'trainee', 'to', 'competencies_official', 'hall_manager', 'financial_manager'");
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
       $this->sql = "SELECT COUNT(DISTINCT lc.id)";

    }

    public function select() {
        global $SESSION;
        $settings = external_settings::get_instance();
        $SESSION->lang = ($settings->get_lang()) ? $settings->get_lang() : current_language();
       // $lang= current_language();
        if($SESSION->lang == 'ar'){
            $this->sql = "SELECT DISTINCT lc.id , le.examnamearabic AS cpdname, lc.* ";
        } else {
            $this->sql = "SELECT DISTINCT lc.id , le.exam AS cpdname, lc.* ";
        }

        parent::select();
    }

    public function from() {
        $this->sql .= " FROM {local_cpd} lc
                        JOIN {local_exams} le ON le.id = lc.examid";
    }
    public function joins() {
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
        if (!empty($this->params['filter_examid']) && $this->params['filter_examid'] > 0) {
            $examid = $this->params['filter_examid'];
            $this->sql .= " AND lc.examid IN ('".$examid."')";
        }
        if (!empty($this->params['filter_code']) && isset($this->params['filter_code'])) {
            $code = $this->params['filter_code'];
            $this->sql .= " AND lc.code IN ('".$code."')";
        }
        if (!empty($this->params['filter_description']) && isset($this->params['filter_description'])) {
            $description = $this->params['filter_description'];
            $this->sql .= " AND lc.description like ('%".$description."%')";
        }


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
        switch ($columnname) {
            case 'submitted':
                $identy = 'lc.id';
                $query = " SELECT COUNT(DISTINCT lce.id) FROM {local_cpd_evidence} lce
                JOIN {local_cpd} lc ON lc.id = lce.cpdid  
                JOIN {local_users} lu ON lu.userid = lce.userid WHERE 1=1 $where  AND lu.deleted = 0 AND lu.approvedstatus = 2 ";
                break;
            case 'completions':
                $identy = 'lc.id';
                $query = " SELECT COUNT(DISTINCT lcc.id) FROM {local_cpd_completion} lcc
                JOIN {local_cpd} lc ON lc.id = lcc.cpdid  
                JOIN {local_users} lu ON lu.userid = lcc.userid WHERE lcc.status=1 $where  AND lu.deleted = 0 AND lu.approvedstatus = 2 ";
                break;
            case 'status':
                $identy = 'lc.id';
                $query = " SELECT COUNT(X.cpdid) FROM (SELECT lcc.cpdid,COUNT(DISTINCT lcc.userid) totalcpd,
                (SELECT COUNT(lcc1.cpdid) FROM {local_cpd_completion} as lcc1 WHERE lcc1.cpdid=lcc.cpdid AND lcc1.status=1) as completecpd 
                FROM {local_cpd_evidence} as lcc JOIN {local_cpd} AS lc ON lc.id=lcc.cpdid WHERE 1=1 $where 
                GROUP BY lcc.cpdid HAVING totalcpd=completecpd) AS X";
            break;
            default:
            return false;
            break;
        }
        $query = str_replace('%placeholder%', $identy, $query);
        return $query;
    }
}
