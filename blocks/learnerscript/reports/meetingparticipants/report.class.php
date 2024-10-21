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
use context_system;

defined('MOODLE_INTERNAL') || die();
class report_meetingparticipants extends reportbase implements report {

    public function __construct($report, $reportproperties) {

        global $DB, $USER;
        $systemcontext = context_system::instance();
        parent::__construct($report, $reportproperties);
        $columns = ['fullname', 'jointime', 'lefttime', 'status'];
        $this->columns = ['meetingcolumn' => $columns];
        $this->components = array('columns', 'conditions', 'ordering', 'filters','permissions', 'plot');
        $this->parent = true;
        $this->orderable = array('firstname');
        $this->basicparams = [['name' => 'trainingprogram'], ['name' => 'code'], ['name' => 'virtualactivities'], ['name' => 'meetingdates']];
        $this->searchable = array("CONCAT(lu.firstname, ' ', lu.lastname)");
        $this->defaultcolumn = 'pe.id';
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
       $this->sql = "SELECT COUNT(DISTINCT pe.id) ";
    }
    public function select() {
            $this->sql = "SELECT DISTINCT pe.id , CONCAT(lu.firstname, ' ', lu.lastname) AS fullname, lu.email, lu.userid AS userid, tpo.code, lt.id AS trainingid, lt.name, cm.id AS coursemoduleid, m.name AS modulename, cm.instance, tpo.id AS offeringid ";
        parent::select();
    }

    public function from() {
        $this->sql .= " FROM {program_enrollments} pe ";

    }
    public function joins() {
        $this->sql .= " JOIN {local_users} lu ON pe.userid = lu.userid 
                        JOIN {tp_offerings} tpo ON tpo.id = pe.offeringid AND pe.programid = tpo.trainingid
                        JOIN {local_trainingprogram} lt ON lt.id = tpo.trainingid 
                        JOIN {role} r ON r.id = pe.roleid AND r.shortname = 'trainee'
                        LEFT JOIN {course_sections} cs ON cs.course = lt.courseid
                        LEFT JOIN {course_modules} cm ON cm.section = cs.id AND cm.course = cs.course  
                        LEFT JOIN {modules} m ON m.id = cm.module AND m.name IN ('webexactivity', 'teamsmeeting', 'zoom') ";
        parent::joins();
        
    }

    public function where() {
        global $USER;
        $this->sql .= " WHERE 1 = 1 "; 
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
        if (!empty($this->params['filter_trainingprogram']) && $this->params['filter_trainingprogram'] > 0) {           
            $this->sql .= " AND lt.id IN (:filter_trainingprogram) ";
        }
        if (!empty($this->params['filter_code']) && isset($this->params['filter_code']) && $this->params['filter_code'] > 0) {
            $this->sql .= " AND tpo.id IN (:filter_code) ";
        }
        if (!empty($this->params['filter_virtualactivities']) && isset($this->params['filter_virtualactivities']) && $this->params['filter_virtualactivities'] > 0) {
            $this->sql .= " AND cm.id IN (:filter_virtualactivities) ";
        }
    }

    public function groupby() {
        $this->sql .= "  ";
    }

    public function get_rows($users) {
        return $users;
    }
}
