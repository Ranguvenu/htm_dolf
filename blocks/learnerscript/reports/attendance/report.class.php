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
class report_attendance extends reportbase implements report {

    public function __construct($report, $reportproperties) {
        global $DB;
        parent::__construct($report, $reportproperties);
        $columns = ['trainingprogram','offeringcode', 'username', 'email', 'meetingname', 'joinedtime', 'leavetime', 'duration'];
        $this->columns = ['attendancecolumns' => $columns];
        $this->components = array('columns', 'conditions', 'ordering', 'filters','permissions', 'plot');
        $this->basicparams = [['name' => 'trainingprogram']];
        $this->filters = array('trainingstatus', 'idnumber', 'type', 'typeorg');
        $this->parent = true;
        $this->orderable = array('trainingprogram','offeringcode', 'username', 'email', 'meetingname', 'duration');
        $lang= current_language();
        if( $lang == 'ar'){
            $this->searchable = array('ltp.namearabic','tpo.code', 'CONCAT(lu.firstnamearabic, " ", lu.lastnamearabic)');
        } else{
            $this->searchable = array('ltp.name','tpo.code', 'CONCAT(lu.firstname, " ", lu.lastname)');
        }
        $this->defaultcolumn = 'mta.id';
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
       $this->sql = "SELECT COUNT(DISTINCT mta.id)";

    }

    public function select() {

        $lang= current_language();
        if( $lang == 'ar'){
            $this->sql = "SELECT DISTINCT mta.id, ltp.namearabic AS trainingprogram, tpo.code AS offeringcode, CONCAT(lu.firstnamearabic, ' ', lu.lastnamearabic) AS username, lu.email, mt.name AS meetingname, mta.totaltime AS duration ";
        } else{
            $this->sql = "SELECT DISTINCT mta.id, ltp.name AS trainingprogram, tpo.code AS offeringcode, CONCAT(lu.firstname, ' ', lu.lastname) AS username, lu.email,  mt.name AS meetingname, mta.totaltime AS duration ";
        }

        parent::select();
    }

    public function from() {
        $this->sql .= " FROM {mod_teams_attendance} mta";
    }
    public function joins() {
        $this->sql .= " JOIN {course_modules} cm ON cm.id = mta.module 
                        JOIN {modules} m ON m.id = cm.module AND m.name = 'teamsmeeting'
                        JOIN {teamsmeeting} mt ON mt.id = cm.instance 
                        JOIN {local_trainingprogram} ltp ON ltp.courseid = mt.course 
                        JOIN {tp_offerings} tpo ON tpo.trainingid = ltp.id 
                        JOIN {program_enrollments} pe ON pe.offeringid = tpo.id AND pe.programid = tpo.trainingid 
                        JOIN {local_users} lu ON lu.userid = pe.userid AND lu.email = mta.email";
        parent::joins();
    }

    public function where() {
        $this->sql .= " WHERE 1 = 1 AND lu.deleted = 0 AND lu.approvedstatus = 2 AND pe.enrolstatus = 1 ";
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
        if ($this->ls_startdate >= 0 && $this->ls_enddate) {
            $this->params['ls_fstartdate'] = ROUND($this->ls_startdate);
            $this->params['ls_fenddate'] = ROUND($this->ls_enddate);
            $this->sql .= " AND mt.start_time BETWEEN :ls_fstartdate AND :ls_fenddate ";
        }
        if (!empty($this->params['filter_trainingprogram']) && $this->params['filter_trainingprogram'] > 0) {           
            $this->sql .= " AND tpo.trainingid IN (:filter_trainingprogram) ";
        }
        if (!empty($this->params['filter_trainingstatus']) && $this->params['filter_trainingstatus'] > 0) {
            if ($this->params['filter_trainingstatus'] == 1) {
                $this->sql .= " AND ltp.published = 1 AND ltp.id IN (SELECT trainingid FROM {tp_offerings} WHERE UNIX_TIMESTAMP() BETWEEN (startdate+time) AND (enddate+endtime)) ";
            } else if ($this->params['filter_trainingstatus'] == 2) {
                $this->sql .= " AND ltp.published = 1 AND ltp.id IN (SELECT trainingid FROM {tp_offerings} WHERE (startdate+time) < UNIX_TIMESTAMP(DATE_ADD(NOW(), INTERVAL +2 DAY))) ";
            } else if ($this->params['filter_trainingstatus'] == 3) {
                $this->sql .= " AND ltp.id IN (SELECT trainingid FROM {tp_offerings} WHERE cancelled = 1) ";
            } else if ($this->params['filter_trainingstatus'] == 4) {
                $this->sql .= " AND ltp.published = 1 AND ltp.id IN (SELECT trainingid FROM {tp_offerings} WHERE (startdate+time) > UNIX_TIMESTAMP()) ";
            }
        }
        if (!empty($this->params['filter_idnumber'])) {
            $this->sql .= " AND lu.id_number = (:filter_idnumber)";
        }
        if ($this->params['filter_type'] > -1) {
            $this->sql .= " AND tpo.type = (:filter_type)";
        }
        if (!empty($this->params['filter_typeorg']) && $this->params['filter_typeorg'] > 0) {
            $this->sql .= " AND tpo.organization = (:filter_typeorg)";
        }
    }


    public function groupby() {

    }

    public function get_rows($organization) {
        return $organization;
    }

    public function column_queries($columnname, $organizationid, $organization = null) { 
        
    }
}
