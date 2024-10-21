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
class report_competencies extends reportbase implements report {

    public function __construct($report, $reportproperties) {
        global $DB;
        parent::__construct($report, $reportproperties);
        $columns = ['competency', 'exams', 'programs'];
        $this->columns = ['compfields' => ['compfields'], 'compcolumns' => $columns];
        $this->components = array('columns', 'conditions', 'ordering', 'filters','permissions', 'plot');
        $this->filters = array('competencyfilters');
        $this->parent = true;
        $this->orderable = array('competency', 'exams', 'programs');

        $lang= current_language();
        if( $lang == 'ar'){
            $this->searchable = array('lc.arabicname');
        } else{
            $this->searchable = array('lc.name');
        }

        $this->defaultcolumn = 'lc.id';
        $this->excludedroles = array("'manager', 'coursecreator', 'editingteacher', 'teacher', 'student', 'trainer', 'examofficial', 'em', 'co', 'expert', 'organizationofficial', 'trainee', 'cpd', 'hall_manager', 'financial_manager'");
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
        $lang= current_language();
        if ( $lang == 'ar') {
            $this->sql = "SELECT DISTINCT lc.id , lc.arabicname AS competency, lc.*, lc.jobroleid as jobrole ";
        } else {
            $this->sql = "SELECT DISTINCT lc.id , lc.name AS competency, lc.*, lc.jobroleid as jobrole ";
        }
        parent::select();
    }

    public function from() {
        $this->sql .= " FROM {local_competencies} lc";
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
        global $DB;
        $compfields =  $DB->get_columns('local_competencies');
        foreach ($compfields as $key => $value) {
            if (!empty($this->params['filter_'.$key]) && $this->params['filter_'.$key] > -1) {
                if ($key == 'level') {
                    if ($this->params['filter_'.$key] != -1) {
                        $filtervalue = $this->params['filter_'.$key];
                        $this->sql .= " AND FIND_IN_SET(('".$filtervalue."'), lc.$key)";
                    }
                }else{
                    $filtervalue = $this->params['filter_'.$key];
                    $this->sql .= " AND lc.$key IN ('".$filtervalue."')";
                    }
            }
        }
        if ($this->ls_startdate >= 0 && $this->ls_enddate) {
            $this->params['ls_fstartdate'] = ROUND($this->ls_startdate);
            $this->params['ls_fenddate'] = ROUND($this->ls_enddate);
            $this->sql .= " AND lc.timecreated BETWEEN :ls_fstartdate AND :ls_fenddate ";
        }
    }


    public function groupby() {

    }

    public function get_rows($competencies) {
        return $competencies;
    }

    public function column_queries($columnname, $competencyid, $competencies = null) { 
        // global $DB;
        // $where = " AND %placeholder% = $competencyid";
        // switch ($columnname) {
        //     case 'exams':
        //        $identy = 'lco.competency'; 
        //        $query = "SELECT lco.examids AS exams
        //                     FROM {local_competencypc_obj} lco 
        //                     WHERE 1 = 1 $where ";
        //     break;
        //     case 'programs':
        //         $identy = 'lco.competency'; 
        //         $query = "SELECT lco.trainingprogramids AS programs
        //                     FROM {local_trainingprogram} lco
        //                     WHERE 1 = 1 $where ";



        //     break;
        //     default:
        //     return false;
        //         break;
        // }
        // $query = str_replace('%placeholder%', $identy, $query);
        // return $query;
    }
}
