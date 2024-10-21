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
class report_halls extends reportbase implements report {

    public function __construct($report, $reportproperties) {
        global $DB;
        parent::__construct($report, $reportproperties);
        $columns = ['hallname', 'reservations'];
        $this->columns = ['hallfields' => ['hallfields'], 'hallcolumns' => $columns];
        $this->components = array('columns', 'conditions', 'ordering', 'filters','permissions', 'plot');
        $this->filters = array('hallfilters');
        $this->parent = true;
        $this->orderable = array('hallname', 'reservations');

        $this->searchable = array('h.name');
        $this->defaultcolumn = 'h.id';
        $this->excludedroles = array("'manager', 'coursecreator', 'editingteacher', 'teacher', 'student', 'trainer', 'examofficial', 'em', 'expert', 'organizationofficial', 'trainee', 'to', 'competencies_official', 'cpd', 'financial_manager'");
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
       $this->sql = "SELECT COUNT(DISTINCT h.id)";

    }

    public function select() {
        $this->sql = "SELECT DISTINCT h.id , h.name AS hallname, h.* ";
        parent::select();
    }

    public function from() {
        $this->sql .= " FROM {hall} h";
    }
    public function joins() {
        parent::joins();
    }

    public function where() {
        $this->sql .= " WHERE 1 = 1 AND h.availability = 1 ";
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
        if (isset($this->params['filter_equipmentavailable']) && $this->params['filter_equipmentavailable'] > 0) {
            $equipmentavailable = $this->params['filter_equipmentavailable'];
            $this->sql .= " AND FIND_IN_SET((".$equipmentavailable."), h.equipmentavailable)";
        }
        $profilefields =  $DB->get_columns('hall');
        foreach ($profilefields as $key => $value) {
            if (!empty($this->params['filter_'.$key]) && $this->params['filter_'.$key] > -1 && $key != 'equipmentavailable') {
                $filtervalue = $this->params['filter_'.$key];
                if (is_numeric($filtervalue)){
                    $this->sql .= " AND h.$key IN ('".$filtervalue."')";
                } else {
                    $this->sql .= " AND h.$key LIKE ('%".$filtervalue."%')";
                }
            } else if(isset($this->params['filter_'.$key])  && $key != 'equipmentavailable' ) {
                if ($key == 'equipmentavailable' || $key == 'id_type' || $key == 'discount') {
                    if ($this->params['filter_'.$key] > -1) {
                        $filtervalue = empty($filtervalue) ? '0' : $filtervalue;
                        $this->sql .= " AND FIND_IN_SET((".$filtervalue."), h.$key)";
                    }
                }
            }
        }
        if ($this->ls_startdate >= 0 && $this->ls_enddate) {
            $this->params['ls_fstartdate'] = ROUND($this->ls_startdate);
            $this->params['ls_fenddate'] = ROUND($this->ls_enddate);
            $this->sql .= " AND h.timecreated BETWEEN :ls_fstartdate AND :ls_fenddate ";
        } 
    }


    public function groupby() {

    }

    public function get_rows($exams) {
        return $exams;
    }

    public function column_queries($columnname, $hallid, $hall = null) { 
        global $DB;
        $where = " AND %placeholder% = $hallid";
        switch ($columnname) {
            case 'reservations':
                $identy = 'hr.hallid';
                $query = "SELECT COUNT(DISTINCT hr.id) AS reservations
                            FROM {hall_reservations} hr 
                            WHERE 1 = 1 $where ";
            break;
            default:
            return false;
                break;
        }
        $query = str_replace('%placeholder%', $identy, $query);
        return $query;
    }
}
