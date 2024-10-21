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
 * @author: Sudharani Sadula
 * @date: 2022
 */
namespace block_learnerscript\lsreports;
use block_learnerscript\local\reportbase;
use block_learnerscript\report;
use block_learnerscript\local\querylib;
use block_learnerscript\local\ls as ls;
use context_system;

defined('MOODLE_INTERNAL') || die();
class report_productlog extends reportbase implements report {

    public function __construct($report, $reportproperties) {
        global $DB;
        parent::__construct($report, $reportproperties);
        $columns = ['username','learningitem', 'learningtype','reason','policy'];
        $this->columns = ['productlogcolumns' => $columns];
        $this->components = array('columns', 'conditions', 'ordering', 'filters','permissions', 'plot');
        $this->filters = array('learningtype');
        $this->parent = true;
        $this->orderable = array('lcl.type','lcl.reason');      
        $this->searchable = array('lcl.type','lcl.reason');

        $this->defaultcolumn = 'lcl.id';
        $this->excludedroles = array("'manager', 'coursecreator', 'editingteacher', 'teacher', 'student', 'trainer', 'em', 'co', 'expert', 'trainee', 'to', 'cpd', 'hall_manager', 'financial_manager', 'examofficial', 'organizationofficial'");
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
       $this->sql = "SELECT COUNT(DISTINCT lcl.id)";

    }

    public function select() {

        $lang= current_language();
        $this->sql = "SELECT lcl.id , tp.id AS productid, tp.category, tp.referenceid, lcl.entitytype AS learningtype, lcl.reason , lcl.userid , lcl.policy";

        parent::select();
    }

    public function from() {
        $this->sql .= " FROM {local_cancel_logs} lcl";
    }
    public function joins() {
        global $DB,$USER;
        $systemcontext = context_system::instance();
        $this->sql .= " JOIN {tool_products} tp ON tp.id = lcl.productid";
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
        if (isset($this->params['filter_learningtype']) && !empty($this->params['filter_learningtype'])) {
            $learningtype = $this->params['filter_learningtype'];
            $this->sql .= " AND lcl.entitytype IN ('".$learningtype."')";
        }
        if ($this->ls_startdate > 0 && $this->ls_enddate) {
            $this->params['ls_fstartdate'] = ROUND($this->ls_startdate);
            $this->params['ls_fenddate'] = ROUND($this->ls_enddate);
            $this->sql .= " AND lcl.timecreated BETWEEN :ls_fstartdate AND :ls_fenddate ";
        }
    }


    public function groupby() {

    }

    public function get_rows($productlogs) {
        return $productlogs;
    }
}
