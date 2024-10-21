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
class report_certficatesinfo extends reportbase implements report {

    public function __construct($report, $reportproperties) {
        global $DB;
        parent::__construct($report, $reportproperties);
       $columns = ['code','issuedto', 'issuedon', 'expires', 'moduletype'];
        //
        $this->columns = ['certficatesfields' => ['certficatesfields'],'certficatescolumns' =>  $columns];
        $this->components = array('columns', 'conditions', 'ordering', 'filters','permissions', 'plot');
        $this->filters = array();
        $this->parent = true;
        $this->orderable = array('code', 'timecreated');

        $this->searchable = array();
        $this->defaultcolumn = 'tci.id';
        $this->excludedroles = array("");
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
       $this->sql = "SELECT COUNT(DISTINCT tci.id)";

    }

    public function select() {
        $this->sql = "SELECT DISTINCT tci.id, tci.userid, tct.name, tci.code, tci.moduleid, tci.moduletype, tci.timecreated AS issuedon, tci.expires AS expires ";
        parent::select();
    }

    public function from() {
        $this->sql .= " FROM {tool_certificate_issues} tci";
    }
    public function joins() {
        $this->sql .=" JOIN {tool_certificate_templates} tct ON tct.id = tci.templateid"; 
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
