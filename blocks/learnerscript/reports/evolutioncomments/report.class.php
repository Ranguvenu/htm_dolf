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
 * @author: Sudharani
 * @date: 2023
 */
namespace block_learnerscript\lsreports;
use block_learnerscript\local\reportbase;
use block_learnerscript\report;
use block_learnerscript\local\querylib;
use block_learnerscript\local\ls as ls;
use context_system;
defined('MOODLE_INTERNAL') || die();
class report_evolutioncomments extends reportbase implements report {

    public function __construct($report, $reportproperties) {
        global $DB;
        parent::__construct($report, $reportproperties);
        $columns = ['course', 'evolution','trainername','traineename','timecreated'];
        $this->columns = ['evolutioncomcolumns' => $columns];
        $this->components = array('columns', 'conditions', 'ordering', 'filters','permissions', 'plot');
        $this->filters = array('courses','evolution','users', 'idnumber');
        $this->parent = true;
        $this->orderable = array('course', 'evolution','trainername','traineename','timecreated');
        $lang= current_language();
        if( $lang == 'ar'){
            $this->searchable = array('c.fullname','e.name', 'CONCAT(lu.firstnamearabic, " ", lu.lastnamearabic)');
        } else{
            $this->searchable = array('c.fullname','e.name', 'CONCAT(lu.firstname, " ", lu.lastname)');
        }
        $this->defaultcolumn = 'ec.id';
       $this->excludedroles = array("'manager', 'coursecreator', 'editingteacher', 'teacher', 'student', 'examofficial', 'em', 'co', 'expert', 'trainee', 'competencies_official', 'cpd', 'hall_manager', 'financial_manager'");
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
       $this->sql = "SELECT COUNT(DISTINCT ec.id)";

    }

    public function select() {
        $lang= current_language();
        if($lang == 'ar') {

            $trainername = "concat(lu.firstnamearabic,' ',lu.lastnamearabic)";
            $traineename = "concat(lu1.firstnamearabic,' ',lu1.lastnamearabic)";

        } else {
                     
            $trainername = "concat(lu.firstname,' ',lu.lastname)";
            $traineename = "concat(lu1.firstname,' ',lu1.lastname)";

        }
        $this->sql = "SELECT DISTINCT ec.id, c.fullname AS course, e.name AS evolution, $trainername AS trainername, $traineename AS traineename, ec.timecreated ";
        parent::select();
    }

    public function from() {
        $this->sql .= " FROM {evolutions_comments} ec";
    }
    public function joins() {
        $this->sql .= " JOIN {evolutions} e ON e.id= ec.evolutionid 
                        JOIN {course} c ON c.id = ec.courseid
                        JOIN {local_users} lu ON lu.userid = ec.trainerid
                        JOIN {local_users} lu1 ON lu1.userid = ec.traineeid ";
       
        parent::joins();
    }

    public function where() {
        global $DB,$USER;
         $systemcontext = context_system::instance();
        $this->sql .= " WHERE 1 = 1 AND lu1.deleted = 0 AND lu1.approvedstatus = 2 ";
         $trainerroleid = $DB->get_field('role', 'id', array('shortname' => 'trainer'));
         if(is_siteadmin() || has_capability('local/organization:manage_trainingofficial',$systemcontext)){
            $this->sql .= " "; 
        } else if(has_capability('local/organization:manage_trainer', $systemcontext)) {
            $this->sql .= " AND lu.userid = ".$USER->id."";
        }
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
        if (!empty($this->params['filter_courses']) && $this->params['filter_courses'] > 0) {
            $this->sql .= " AND c.id = (:filter_courses)";
        }
        if (!empty($this->params['filter_users']) && $this->params['filter_users'] > 0) {
            $this->sql .= " AND lu.userid IN (:filter_users)";
        }
        if (!empty($this->params['filter_evolution']) && $this->params['filter_evolution'] > 0) {
            $this->sql .= " AND e.id IN (:filter_evolution)";
        }
        if (!empty($this->params['filter_idnumber'])) {
            $this->sql .= " AND (lu.id_number = (:filter_idnumber) OR lu1.id_number = (:filter_idnumber)) ";
        }
        if ($this->ls_startdate >= 0 && $this->ls_enddate) {
            $this->params['ls_fstartdate'] = ROUND($this->ls_startdate);
            $this->params['ls_fenddate'] = ROUND($this->ls_enddate);
            $this->sql .= " AND ec.timecreated BETWEEN :ls_fstartdate AND :ls_fenddate ";
        }
    }


    public function groupby() {

    }

    public function get_rows($evolutioncomments) {
        return $evolutioncomments;
    }
}
