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
class report_offeringsessions extends reportbase implements report {

    public function __construct($report, $reportproperties) {
        global $DB;
        parent::__construct($report, $reportproperties);
        $columns = ['id','offeringcode','startdate', 'enddate','enrolledusers','certissueusers','certnotissued','absentusers','notissueother'];
        $this->columns = ['offeringsessioncolumns' => $columns];
        $this->components = array('columns', 'conditions', 'ordering', 'filters','permissions', 'plot');
        $this->filters = array('code','offeringdate');
        $this->parent = true;
        $this->orderable = array();

        $this->searchable = array('tp.code');
        //$this->defaultcolumn = 'tp.id';
        $this->sqlorder['column'] = 'tp.id';
        $this->sqlorder['dir'] = 'DESC';
        $this->excludedroles = array("'manager', 'coursecreator', 'editingteacher', 'teacher', 'student', 'trainer', 'examofficial', 'em', 'expert', 'trainee', 'to', 'competencies_official', 'cpd', 'hall_manager', 'financial_manager', 'organizationofficial'");
    }

    public function init() {
    }
    public function count() {
       $this->sql = "SELECT COUNT(DISTINCT tp.id)";

    }

    public function select() {

        $lang= current_language();
        $this->sql = " SELECT DISTINCT tp.id, tp.code AS offeringcode, tp.startdate, tp.enddate ";
        parent::select();
    }

    public function from() {
        $this->sql .= " FROM {tp_offerings} tp";
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
         if ($this->ls_startdate >= 0 && $this->ls_enddate) {
            $this->params['ls_fstartdate'] = ROUND($this->ls_startdate);
            $this->params['ls_fenddate'] = ROUND($this->ls_enddate);
            $this->sql .= " AND tp.startdate BETWEEN :ls_fstartdate AND :ls_fenddate ";
        }
        if(isset($this->params['filter_code']) && $this->params['filter_code'] > 0){
            $offeringcode = $this->params['filter_code'];
            $this->sql .=" tp.code IN(:filter_code)";
        }
        if ($this->params['date_offeringdate']['enabled'] == 1 && $this->params['date_ofcomingdate']['enabled'] == 1) {
            $estday = $this->params['date_offeringdate']['day'];
            $estmonth = $this->params['date_offeringdate']['month'];
            $estyear = $this->params['date_offeringdate']['year'];

            $eenday = $this->params['date_ofcomingdate']['day'];
            $eenmonth = $this->params['date_ofcomingdate']['month'];
            $eenyear = $this->params['date_ofcomingdate']['year'];

            $eststartdate = mktime(0,0,0, $estmonth, $estday, $estyear);
            $eenenddate = mktime(23,59,59, $eenmonth, $eenday, $eenyear);
            $this->sql .= " AND tp.startdate BETWEEN $eststartdate AND $eenenddate";
        }
    }


    public function groupby() {
        $this->sql .= " GROUP BY tp.id ";
    }

    public function get_rows($offeringsessions) {
        return $offeringsessions;
    }
}
