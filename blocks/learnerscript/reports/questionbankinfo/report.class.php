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

defined('MOODLE_INTERNAL') || die();
class report_questionbankinfo extends reportbase implements report {

    public function __construct($report, $reportproperties) {
        global $DB;
        parent::__construct($report, $reportproperties);
        $columns = ['experts','createdquestions','publishedquestions','startdate'];
        $this->columns = ['questionbankfields' => ['questionbankfields'],'questionbankcolumns' =>  $columns];
        $this->components = array('columns', 'conditions', 'ordering', 'filters','permissions', 'plot');
        $this->filters = array();
        $this->parent = true;
        $this->orderable = array('workshopname', 'workshopdate', 'createdquestions');

        $this->searchable = array('qb.workshopname');
        $this->defaultcolumn = 'qb.id';
        $this->excludedroles = array("'manager', 'coursecreator', 'editingteacher', 'teacher', 'student', 'trainer', 'examofficial', 'em', 'co', 'organizationofficial', 'trainee', 'to', 'competencies_official', 'cpd', 'hall_manager', 'financial_manager'");
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
       $this->sql = "SELECT COUNT(DISTINCT qb.id)";

    }

    public function select() {
        $this->sql = "SELECT DISTINCT qb.id , qb.workshopname AS workshopname, qb.* ";
        parent::select();
    }

    public function from() {
        $this->sql .= " FROM {local_questionbank} qb";
    }
    public function joins() {
       // $this->sql .=" JOIN {local_qb_experts} ra ON ra.userid = u.id "; 
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
        if ($this->ls_startdate > 0 && $this->ls_enddate) {
            $this->params['ls_fstartdate'] = ROUND($this->ls_startdate);
            $this->params['ls_fenddate'] = ROUND($this->ls_enddate);
            $this->sql .= " AND qb.workshopdate BETWEEN :ls_fstartdate AND :ls_fenddate ";
        }
    }


    public function groupby() {

    }

    public function get_rows($questionbank) {
        return $questionbank;
    }

    public function column_queries($columnname, $questionbankid, $questionbank = null) { 
        global $DB;
        $where = " AND %placeholder% = $examid";
        // switch ($columnname) {
        //     case 'enrollments':
        //         $identy = 'ee.examid';
        //         $query = "SELECT COUNT(DISTINCT ee.userid) AS enrollments
        //                     FROM {exam_enrollments} ee 
        //                     WHERE 1 = 1 $where ";
        //     break;
        //     case 'completions':
        //         $identy = 'ec.examid';
        //         $query = "SELECT COUNT(DISTINCT ec.userid) AS completions 
        //                     FROM {exam_completions} ec 
        //                     WHERE 1 = 1 AND ec.completiondate != 0 $where";
        //     break;
        //     default:
        //     return false;
        //         break;
        // }
        $query = str_replace('%placeholder%', $identy, $query);
        return $query;
    }
}
