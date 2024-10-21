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
 * @date: 2023
 */
namespace block_learnerscript\lsreports;
use block_learnerscript\local\reportbase;
use block_learnerscript\report;

defined('MOODLE_INTERNAL') || die();
class report_trainingprovider extends reportbase implements report {

    public function __construct($report, $reportproperties) {
        global $DB;
        parent::__construct($report, $reportproperties);
        $columns = ['provider', 'identity', 'fullname', 'email', 'organization', 'trainingspecialties', 'programnamear', 'programnameen', 'programcode', 'offeringcode', 'offeringstartdate', 'offeringenddate', 'offeringstarttime', 'offeringendtime'];
        $this->columns = ['trainingprovidercolumns' => $columns];
        $this->components = array('columns', 'conditions', 'ordering', 'filters','permissions', 'plot');
        $this->filters = array('trainingprovider', 'idnumber', 'fieldoftraining', 'trainerprograms');
        $this->parent = true;
        $this->orderable = array('provider', 'identity', 'fullname', 'organization', 'trainingspecialties', 'programar', 'programen', 'programcode', 'offeringcode', 'offeringstartdate', 'offeringenddate', 'offeringstarttime', 'offeringendtime');
        $lang= current_language();
        if( $lang == 'ar'){
            $this->searchable = array('CONCAT(lu.firstnamearabic, " ", lu.lastnamearabic)', 'ltp.namearabic', 'ltp.name', 'ltp.code', 'tpo.code', 'lu.id_number');
        } else{
            $this->searchable = array('CONCAT(lu.firstname, " ", lu.lastname)', 'ltp.namearabic', 'ltp.name', 'ltp.code', 'tpo.code', 'lu.id_number');
        }
        $this->defaultcolumn = 'pe.id';
        $this->excludedroles = array("'manager', 'coursecreator', 'editingteacher', 'teacher', 'student', 'examofficial', 'em', 'expert', 'trainee', 'competencies_official', 'cpd', 'hall_manager', 'trainer', 'trainee_mof', 'mof_testing', 'organizationofficial'");
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
       $this->sql = "SELECT COUNT(DISTINCT pe.id)";

    }

    public function select() {

        $lang= current_language();
        if( $lang == 'ar'){
            $this->sql = "SELECT DISTINCT pe.id, lu.id_number AS identity, lu.email, CONCAT(lu.firstnamearabic, ' ', lu.lastnamearabic) AS fullname, ltp.namearabic AS programnamear, ltp.name AS programnameen, ltp.code AS programcode, tpo.code AS offeringcode, tpo.startdate, tpo.enddate, tpo.time, tpo.endtime, tpo.trainertype, tpo.trainerorg, ltr.fieldoftraining AS trainingspecialties, lu.userid ";

        } else{
            $this->sql = "SELECT DISTINCT pe.id, lu.id_number AS identity, lu.email, CONCAT(lu.firstname, ' ', lu.lastname) AS fullname, ltp.namearabic AS programnamear, ltp.name AS programnameen , ltp.code AS programcode, tpo.code AS offeringcode, tpo.startdate, tpo.enddate, tpo.time, tpo.endtime, tpo.trainertype, tpo.trainerorg, ltr.fieldoftraining AS trainingspecialties, lu.userid ";
        }

        parent::select();
    }

    public function from() {
        $this->sql .= " FROM {user} u";
    }
    public function joins() {
        global $DB,$USER;
        $this->sql .= " JOIN {local_users} lu ON lu.userid = u.id 
                        JOIN {program_enrollments} pe ON pe.userid = lu.userid 
                        JOIN {role} r ON r.id = pe.roleid AND r.shortname = 'trainer' 
                        JOIN {tp_offerings} tpo ON tpo.id = pe.offeringid 
                        JOIN {local_trainingprogram} ltp ON ltp.id = tpo.trainingid AND ltp.id = pe.programid 
                        LEFT JOIN {local_trainer_request} ltr ON ltr.userid = lu.userid AND ltr.status = 2 ";
        parent::joins();
    }

    public function where() {
        $this->sql .= " WHERE 1 = 1 AND lu.deleted = 0 AND pe.enrolstatus = 1 ";
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
        if ($this->params['filter_trainingprovider'] > -1) {
            if ($this->params['filter_trainingprovider'] == 0) {
                $this->sql .= " AND (tpo.trainertype = (:filter_trainingprovider) OR tpo.trainertype IS NULL) ";
            } else {
                $this->sql .= " AND tpo.trainertype = (:filter_trainingprovider)";
            }
        }
        if (!empty($this->params['filter_idnumber'])) {
            $this->sql .= " AND lu.id_number = (:filter_idnumber)";
        }
        if (!empty($this->params['filter_trainerprograms']) && $this->params['filter_trainerprograms'] > 0) {
            $this->sql .= " AND ltp.id IN (:filter_trainerprograms)";
        }
        if (!empty($this->params['filter_fieldoftraining']) && $this->params['filter_fieldoftraining'] > 0) {
            $this->sql .= " AND ltr.id LIKE (:filter_fieldoftraining)";
        }
    }

    public function groupby() {

    }

    public function get_rows($trainingprograms) {
        return $trainingprograms;
    }

    public function column_queries($columnname, $trainingprogramid, $trainingprograms = null) { 
        
    }
}
