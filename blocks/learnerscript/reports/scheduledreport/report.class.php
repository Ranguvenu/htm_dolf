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
 * LearnerScript Reports
 * A Moodle block for creating customizable reports
 * @package blocks
 * @author: eAbyas Info Solutions
 * @date: 2017
 */
namespace block_learnerscript\lsreports;
use block_learnerscript\local\reportbase;
use block_learnerscript\report;
use block_learnerscript\local\querylib;
use block_learnerscript\local\ls as ls;

class report_scheduledreport extends reportbase implements report{
    /**
     * [__construct description]
     * @param [type] $report           [description]
     * @param [type] $reportproperties [description]
     */
    public function __construct($report, $reportproperties) {
        parent::__construct($report);
        $this->parent = false;
        $this->courselevel = true;
        $this->components = array('columns', 'filters', 'permissions', 'plot');
        $columns = ['entityname', 'scheduledby', 'scheduledto', 'oldscheduledate', 'newscheduledate', 'scheduledon'];
        $this->columns = ['scheduledcolumn' => $columns];
        // $this->orderable = array('invoice');
        $this->searchable = array("CONCAT(lu.firstname,' ',lu.lastname)","CONCAT(lua.firstname,' ',lua.lastname)");
        $this->filters = array('idnumber');
        $this->defaultcolumn = 'tp.id';
        $this->excludedroles = array("'student'");
    }
    function init() {
        global $DB; 
       
    }

    function count() {
        $this->sql = "SELECT COUNT( DISTINCT lsl.id) ";
    }
    function select() {
        $this->sql = " SELECT DISTINCT lsl.id, tp.id AS productid, tp.category, tp.referenceid, CONCAT(lu.firstname,' ',lu.lastname) AS scheduledby, CONCAT(lua.firstname,' ',lua.lastname) AS scheduledto, lsl.oldscheduledate, lsl.newscheduledate, IF(lsl.timemodified = 0, lsl.timecreated, lsl.timemodified) AS scheduledon ";
        parent::select();
      }

    function from() {
        $this->sql .= " FROM {local_schedule_logs} lsl";
    }

    function joins() {
        $this->sql .= " JOIN  {tool_products} tp ON lsl.productid = tp.id
                        JOIN {local_users} lu ON lu.userid = lsl.usercreated
                        JOIN {local_users} lua ON lua.userid = lsl.userid";
        parent::joins();
    }

    function where() { 
        $this->sql .= " WHERE 1=1 AND lu.deleted = 0 AND lu.approvedstatus = 2 ";
        parent::where();
    }

    function search() {
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

    function filters() {
        global $DB;
        if (!empty($this->params['filter_idnumber'])) {
            $this->sql .= " AND (lu.id_number = (:filter_idnumber) OR lua.id_number = (:filter_idnumber))";
        }
    }
    function groupby() {
    }
    /**
     * [get_rows description]
     * @param  array  $users [description]
     * @return [type]        [description]
     */
    public function get_rows($users = array()) {
        return $users;
    }
}
