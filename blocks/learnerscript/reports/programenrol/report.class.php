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
class report_programenrol extends reportbase implements report {

    public function __construct($report, $reportproperties) {
        global $DB;
        parent::__construct($report, $reportproperties);
        $columns = ['trainee', 'identity', 'email', 'programname', 'programcode', 'offeringcode', 'startdate', 'enddate', 'enrollmentdate', 'enrolledby', 'completiondate', 'certificatecode', 'traineestatus'];
        $this->columns = ['programenrolcolumns' => $columns];
        $this->components = array('columns', 'conditions', 'ordering', 'filters','permissions', 'plot');
        $this->filters = array('trainingprogram', 'code', 'idnumber', 'enrollmentdate', 'startdate', 'trainingstatus', 'type', 'typeorg', 'hallplace');
        $this->parent = true;
        $this->orderable = array('trainee', 'identity', 'programname', 'programcode', 'offeringcode', 'startdate', 'enddate', 'enrollmentdate', 'enrolledby', 'completiondate', 'certificatecode');
        $lang= current_language();
        if( $lang == 'ar'){
            $this->searchable = array('CONCAT(lu.firstnamearabic, " ", lu.lastnamearabic)', 'ltp.namearabic', 'ltp.code', 'tpo.code', 'lu.id_number');
        } else{
            $this->searchable = array('CONCAT(lu.firstname, " ", lu.lastname)', 'ltp.name', 'ltp.code', 'tpo.code', 'lu.id_number');
        }
        $this->defaultcolumn = "CONCAT(pe.offeringid, '-', pe.userid)";
        $this->excludedroles = array("'manager', 'coursecreator', 'editingteacher', 'teacher', 'student', 'examofficial', 'em', 'expert', 'trainee', 'competencies_official', 'cpd', 'hall_manager', 'financial_manager', 'trainer'");
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
       $this->sql = "SELECT COUNT(DISTINCT CONCAT(pe.offeringid, '-', pe.userid))";

    }

    public function select() {

        $lang= current_language();
        if( $lang == 'ar'){
            $this->sql = "SELECT DISTINCT CONCAT(pe.offeringid, '-', pe.userid),lu.id_number AS identity, CONCAT(lu.firstnamearabic, ' ', lu.lastnamearabic) AS trainee, ltp.namearabic AS programname, ltp.code AS programcode, tpo.code AS offeringcode, tpo.startdate, tpo.enddate, pe.timecreated AS enrollmentdate, pc.completiondate AS completiondate, pe.usercreated AS enrolledby, tci.code AS certificatecode, lu.email, tpo.time, tpo.duration, pe.userid AS userid ";

        } else{
            $this->sql = "SELECT DISTINCT CONCAT(pe.offeringid, '-', pe.userid),lu.id_number AS identity, CONCAT(lu.firstname, ' ', lu.lastname) AS trainee, ltp.name AS programname, ltp.code AS programcode, tpo.code AS offeringcode, tpo.startdate, tpo.enddate, pe.timecreated AS enrollmentdate, pc.completiondate AS completiondate, pe.usercreated AS enrolledby, tci.code AS certificatecode, lu.email, tpo.time, tpo.duration, pe.userid AS userid ";
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
                        JOIN {role} r ON r.id = pe.roleid AND r.shortname = 'trainee' 
                        JOIN {tp_offerings} tpo ON tpo.id = pe.offeringid AND pe.programid = tpo.trainingid
                        JOIN {local_trainingprogram} ltp ON ltp.id = tpo.trainingid AND ltp.id = pe.programid
                        LEFT JOIN {program_completions} pc ON pc.programid = pe.programid AND pc.offeringid = pe.offeringid AND pc.userid = pe.userid AND pc.completion_status != 0 
                        LEFT JOIN {tool_certificate_issues} tci ON tci.moduleid = tpo.id AND tci.moduletype = 'trainingprogram' AND tci.userid = lu.userid ";
        parent::joins();
    }

    public function where() {
        global $DB,$USER;
        $this->sql .= " WHERE 1 = 1 AND lu.deleted = 0 AND lu.approvedstatus = 2 AND pe.enrolstatus = 1";
        $systemcontext = context_system::instance();
        if(is_siteadmin()){
            $this->sql .= " "; 
        } else if (!is_siteadmin() && has_capability('local/organization:manage_organizationofficial',$systemcontext)) {
            $organization = $DB->get_field('local_users','organization',array('userid'=>$USER->id));
            $this->sql .= " AND lu.organization = $organization";
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
        if (!empty($_SESSION['filter_userid']) && $_SESSION['filter_userid'] > 0) {
            $this->sql .= " AND lu.userid = " . $_SESSION['filter_userid'];
        }
        if (!empty($_SESSION['filter_orguser']) && $_SESSION['filter_orguser'] > 0) {
            $this->sql .= " AND pe.usercreated = " . $_SESSION['filter_orguser'];
        }
        if (!empty($_SESSION['filter_referenceid']) && $_SESSION['filter_referenceid'] > 0) {
            $this->sql .= " AND tpo.id = " . $_SESSION['filter_referenceid'];
        }
        if (!empty($this->params['filter_trainingprogram']) && $this->params['filter_trainingprogram'] > 0) {
            $this->sql .= " AND ltp.id IN (:filter_trainingprogram)";
        }
        if (!empty($this->params['filter_code']) && $this->params['filter_code'] > 0) {
            $this->sql .= " AND tpo.id = (:filter_code)";
        } 
        if (!empty($this->params['filter_idnumber'])) {
            $this->sql .= " AND lu.id_number = (:filter_idnumber)";
        }  
        if ($this->params['date_examfromtime']['enabled'] == 1 && $this->params['date_examtotime']['enabled'] == 1) {
            $estday = $this->params['date_examfromtime']['day'];
            $estmonth = $this->params['date_examfromtime']['month'];
            $estyear = $this->params['date_examfromtime']['year'];

            $eenday = $this->params['date_examtotime']['day'];
            $eenmonth = $this->params['date_examtotime']['month'];
            $eenyear = $this->params['date_examtotime']['year'];

            $eststartdate = mktime(0,0,0, $estmonth, $estday, $estyear);
            $eenenddate = mktime(23,59,59, $eenmonth, $eenday, $eenyear);
            $this->sql .= " AND tpo.startdate BETWEEN $eststartdate AND $eenenddate";
        }
        if ($this->params['date_fromtime']['enabled'] == 1 && $this->params['date_totime']['enabled'] == 1) {
            $stday = $this->params['date_fromtime']['day'];
            $stmonth = $this->params['date_fromtime']['month'];
            $styear = $this->params['date_fromtime']['year'];

            $enday = $this->params['date_totime']['day'];
            $enmonth = $this->params['date_totime']['month'];
            $enyear = $this->params['date_totime']['year'];

            $ststartdate = mktime(0,0,0, $stmonth, $stday, $styear);
            $enenddate = mktime(23,59,59, $enmonth, $enday, $enyear);
            $this->sql .= " AND pe.timecreated BETWEEN $ststartdate AND $enenddate";
        }   
        if ($this->ls_startdate > 0 && $this->ls_enddate) {
            $this->params['ls_fstartdate'] = ROUND($this->ls_startdate);
            $this->params['ls_fenddate'] = ROUND($this->ls_enddate);
            $this->sql .= " AND ltp.availableto BETWEEN :ls_fstartdate AND :ls_fenddate ";
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
        if ($this->params['filter_type'] > -1) {
            $this->sql .= " AND tpo.type = (:filter_type)";
        }
        if (!empty($this->params['filter_typeorg']) && $this->params['filter_typeorg'] > 0) {
            $this->sql .= " AND tpo.organization = (:filter_typeorg)";
        }
        if (!empty($this->params['filter_hallplace'])) {
            if ($this->params['filter_hallplace'] == 'clientheadquarters') {
                $this->sql .= " AND FIND_IN_SET(:filter_hallplace, tpo.halllocation) ";
            } else {
                $this->sql .= " AND tpo.halladdress IN (SELECT id FROM {hall} WHERE halllocation = (:filter_hallplace)) ";
            }
        }
    }


    public function groupby() {
        $this->sql .=" GROUP BY CONCAT(pe.offeringid, '-', pe.userid)";
    }

    public function get_rows($trainingprograms) {
        return $trainingprograms;
    }

    public function column_queries($columnname, $trainingprogramid, $trainingprograms = null) { 
        
    }
}
