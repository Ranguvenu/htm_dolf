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
use block_learnerscript\local\querylib;
use block_learnerscript\local\ls as ls;
use context_system;

defined('MOODLE_INTERNAL') || die();
class report_enrolledexaminer extends reportbase implements report {

    public function __construct($report, $reportproperties) {
        global $DB;
        parent::__construct($report, $reportproperties);
        $columns = ['identity', 'fullname', 'examcode', 'examname', 'profilecode', 'organization'];
        $this->columns = ['enrolledexaminercolumns' => $columns];
        $this->components = array('columns', 'conditions', 'ordering', 'filters','permissions', 'plot');
        $this->filters = array('exam', 'code', 'idnumber', 'purchasedate', 'profiledate');
        $this->parent = true;
        $this->orderable = array('identity', 'fullname', 'examcode', 'examname', 'profilecode', 'organization');
        $lang= current_language();
        if( $lang == 'ar'){
            $this->searchable = array('CONCAT(lu.firstnamearabic, " ", lu.lastnamearabic)', 'le.code', 'le.examnamearabic', 'lep.profilecode');
        } else{
            $this->searchable = array('CONCAT(lu.firstname, " ", lu.lastname)', 'le.code', 'le.exam', 'lep.profilecode');
        }
        $this->defaultcolumn = 'CONCAT(u.id,"-",lep.id)';
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
       $this->sql = "SELECT COUNT(DISTINCT(CONCAT(u.id,'-',lep.id)))";

    }

    public function select() {

        $lang= current_language();
        if( $lang == 'ar'){
            $this->sql = "SELECT DISTINCT CONCAT(u.id,'-',lep.id), lu.id_number AS identity, CONCAT(lu.firstnamearabic, ' ', lu.lastnamearabic) AS fullname, le.code AS examcode, le.examnamearabic AS examname, lep.profilecode AS profilecode, lu.organization AS orgid ";

        } else{
            $this->sql = "SELECT DISTINCT CONCAT(u.id,'-',lep.id), lu.id_number AS identity, CONCAT(lu.firstname, ' ', lu.lastname) AS fullname, le.code AS examcode, le.exam AS examname, lep.profilecode AS profilecode, lu.organization AS orgid";
        }

        parent::select();
    }

    public function from() {
        $this->sql .= " FROM {user} u";
    }
    public function joins() {
        global $DB,$USER;
        $this->sql .= " JOIN {local_users} lu ON lu.userid = u.id 
                        JOIN {exam_enrollments} ee ON ee.userid = lu.userid
                        JOIN {local_exams} le ON le.id = ee.examid 
                        JOIN {local_exam_profiles} lep ON lep.examid = le.id AND ee.profileid = lep.id ";
        // $systemcontext = context_system::instance();
        // $trainerroleid = $DB->get_field('role', 'id', array('shortname' => 'trainer'));
        // if(is_siteadmin() || has_capability('local/organization:manage_trainingofficial',$systemcontext)){
        //     $this->sql .= " "; 
        // } else if (!is_siteadmin() && has_capability('local/organization:manage_organizationofficial',$systemcontext)) {
        //     $this->sql .= " JOIN {tp_offerings} tpo ON tpo.trainingid = ltp.id
        //                     JOIN {tool_org_order_seats} oos ON oos.fieldid = tpo.id AND oos.tablename = 'tp_offerings' AND oos.orguserid = " . $USER->id;
        // } else if(has_capability('local/organization:manage_trainee', $systemcontext) ||
        //               has_capability('local/organization:manage_trainer', $systemcontext)) {
        //     $this->sql .= " JOIN {program_enrollments} ue ON ue.programid = ltp.id AND  ue.courseid = ltp.courseid AND ue.userid = ".$USER->id." AND ue.roleid = ".$trainerroleid. " AND ltp.published = 1";
        // }
        parent::joins();
    }

    public function where() {
        $this->sql .= " WHERE 1 = 1  AND lu.deleted = 0 AND lu.approvedstatus = 2 AND ee.enrolstatus = 1 ";
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
        if (!empty($this->params['filter_exam']) && $this->params['filter_exam'] > 0) {
            $this->sql .= " AND le.id IN (:filter_exam)";
        }
        if (!empty($this->params['filter_code']) && $this->params['filter_code'] > 0) {
            $this->sql .= " AND lep.id = (:filter_code)";
        } 
        if (!empty($this->params['filter_idnumber'])) {
            $this->sql .= " AND lu.id_number = (:filter_idnumber)";
        }  
        if ($this->params['date_purchasedate']['enabled'] == 1 && $this->params['date_comingdate']['enabled'] == 1) {
            $day = $this->params['date_purchasedate']['day'];
            $month = $this->params['date_purchasedate']['month'];
            $year = $this->params['date_purchasedate']['year'];

            $cday = $this->params['date_comingdate']['day'];
            $cmonth = $this->params['date_comingdate']['month'];
            $cyear = $this->params['date_comingdate']['year'];

            $purchasedate = mktime(0,0,0, $month, $day, $year);
            $comingdate = mktime(23,59,59, $cmonth, $cday, $cyear);

            $this->sql .= " AND le.timecreated BETWEEN $purchasedate AND $comingdate";
        }
        if ($this->params['date_profiledate']['enabled'] == 1 && $this->params['date_pfcomingdate']['enabled'] == 1) {
            $day = $this->params['date_profiledate']['day'];
            $month = $this->params['date_profiledate']['month'];
            $year = $this->params['date_profiledate']['year'];

            $cday = $this->params['date_pfcomingdate']['day'];
            $cmonth = $this->params['date_pfcomingdate']['month'];
            $cyear = $this->params['date_pfcomingdate']['year'];

            $profiledate = mktime(0,0,0, $month, $day, $year);
            $pfcomingdate = mktime(23,59,59, $cmonth, $cday, $cyear);

            $this->sql .= " AND lep.registrationstartdate >= $profiledate AND lep.registrationenddate <= $pfcomingdate";
        }
        if ($this->ls_startdate > 0 && $this->ls_enddate) {
            $this->params['ls_fstartdate'] = ROUND($this->ls_startdate);
            $this->params['ls_fenddate'] = ROUND($this->ls_enddate);
            $this->sql .= " AND le.timecreated BETWEEN :ls_fstartdate AND :ls_fenddate ";
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
