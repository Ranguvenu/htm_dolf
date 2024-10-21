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
class report_traineeactivities extends reportbase implements report {

    public function __construct($report, $reportproperties) {
        global $DB;
        parent::__construct($report, $reportproperties);
        $columns = ['identity', 'fullname', 'email', 'organization', 'activitytype', 'activitynamear', 'activitynameen', 'activitycode', 'code', 'activityplace', 'hall', 'city', 'startdate', 'enddate', 'starttime', 'endtime', 'fees', 'offeringid', 'enrolmentdate', 'enrolledby', 'completiondate', 'certificatecode', 'enrolmentid'];
        $this->columns = ['traineeactivitiescolumns' => $columns];
        $this->components = array('columns', 'conditions', 'ordering', 'filters','permissions', 'plot');
        $this->filters = array('idnumber', 'moduleactivitycode', 'moduleactivityname', 'startdate', 'halladdress', 'certificatecode', 'allmodules');
        $this->parent = true;
        $this->orderable = array('identity', 'fullname', 'organization', 'activitytype', 'activitynamear', 'activitynameen', 'code', 'activityplace', 'hall', 'city', 'startdate', 'enddate', 'starttime', 'endtime', 'fees', 'enrolledby', 'completiondate', 'certificatecode');
        $this->searchable = array('a.identity');
        //$this->defaultcolumn = 'a.id';
        $this->sqlorder['column'] = 'a.enrolmentdate';
        $this->sqlorder['dir'] = 'DESC';
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
       $this->sql = "SELECT COUNT(DISTINCT a.id) FROM (SELECT CONCAT(pe.id, '-', lu.userid, '@') AS id, lu.userid, lu.id_number AS identity, CONCAT(lu.firstname, ' ', lu.lastname) AS fullname, pe.programid AS activityid, 'trainingprogram' AS activitytype, tp.name AS activitynameen, tp.namearabic AS activitynamear, tp.code AS activitycode, tpo.code AS code, tpo.trainingmethod AS activityplace, tpo.trainingmethod AS method, h.name AS hall, tpo.halladdress, tp.availablefrom AS startdate, tp.availableto AS enddate, tp.availablefrom AS starttime, tp.availableto AS endtime, tp.sellingprice, tp.tax_free AS taxfree, pe.usercreated AS enrolledby, 'trainings' AS module, lu.organization, h.id AS hallid, (SELECT tci.code FROM {tool_certificate_issues} tci WHERE tci.moduleid = tpo.id AND tci.userid = lu.userid AND tci.moduletype = 'trainingprogram') AS certificatecode, pe.timecreated AS enrolmentdate, pe.id as enrolmentid
            FROM {local_users} lu 
            JOIN {program_enrollments} pe ON pe.userid = lu.userid AND pe.enrolstatus = 1
            JOIN {role} r ON r.id = pe.roleid AND r.shortname = 'trainee' 
            JOIN {local_trainingprogram} tp ON tp.id = pe.programid 
            JOIN {tp_offerings} tpo ON tpo.trainingid = tp.id AND tpo.id = pe.offeringid 
            LEFT JOIN {hall} h ON h.id = tpo.halladdress 
            WHERE lu.deleted = 0 AND lu.approvedstatus = 2 
            UNION
            SELECT CONCAT(ee.id, '-', lu.userid, '#') AS id, lu.userid, lu.id_number AS identity, CONCAT(lu.firstname, ' ', lu.lastname) AS fullname, ee.examid AS activityid, 'exam' AS activitytype, le.exam AS activitynameen, le.examnamearabic AS activitynamear, le.code AS activitycode, lep.profilecode AS code, '' AS activityplace, 'offline' AS method, h.name AS hall, h.id  AS halladdress, leu.examdate AS startdate, leu.examdate+hs.starttime+hs.endtime AS enddate, hs.starttime AS starttime, hs.starttime+lep.duration AS endtime, le.sellingprice, le.tax_free AS taxfree, ee.usercreated AS enrolledby, 'exams' AS module, lu.organization, h.id AS hallid, (SELECT tci.code FROM {tool_certificate_issues} tci WHERE tci.moduleid = le.id AND tci.userid = lu.userid AND tci.moduletype = 'exams') AS certificatecode, ee.timecreated AS enrolmentdate, ee.id as enrolmentid
            FROM {local_users} lu 
            JOIN {exam_enrollments} ee ON ee.userid = lu.userid AND ee.enrolstatus = 1 
            JOIN {local_exams} le ON le.id = ee.examid 
            JOIN {local_exam_profiles} lep ON lep.examid = le.id 
            JOIN {local_exam_userhallschedules} leu ON leu.profileid = lep.id AND leu.userid = lu.userid 
            LEFT JOIN {hallschedule} hs ON hs.id = leu.hallscheduleid
            LEFT JOIN {hall} h ON h.id = hs.hallid 
            WHERE lu.deleted = 0 AND lu.approvedstatus = 2 
            UNION
            SELECT CONCAT(lea.id, '-', lu.userid, '$') AS id, lu.userid, lu.id_number AS identity, CONCAT(lu.firstname, ' ', lu.lastname) AS fullname, lea.eventid AS activityid, 'events' AS activitytype, lev.title AS activitynameen, lev.titlearabic AS activitynamear, lev.code AS activitycode, '' AS code, lev.method AS activityplace, lev.method AS method, h.name AS hall, lev.halladdress, lev.startdate, lev.enddate, lev.startdate AS starttime, lev.enddate AS endtime, lev.sellingprice, lev.taxfree, lea.usercreated AS enrolledby, 'events' AS module, lu.organization, h.id AS hallid, (SELECT tci.code FROM {tool_certificate_issues} tci WHERE tci.moduleid = lev.id AND tci.userid = lu.userid AND tci.moduletype = 'events') AS certificatecode, lea.timecreated AS enrolmentdate, lea.id as enrolmentid
            FROM {local_users} lu 
            JOIN {local_event_attendees} lea ON lea.userid = lu.userid AND lea.enrolstatus = 1 
            JOIN {local_events} lev ON lev.id = lea.eventid 
            LEFT JOIN {hall} h ON h.id = lev.halladdress 
            WHERE lu.deleted = 0 AND lu.approvedstatus = 2 
            UNION 
            SELECT CONCAT(ll1.id, '-', lu.id, '*') AS id, lu.id AS userid, lu.idnumber AS identity, CONCAT(lu.firstname, ' ', lu.lastname) AS fullname, ll1.id AS activityid, 'learningtrack' AS activitytype, ll1.name AS activitynameen, ll1.name AS activitynamear, ll1.code AS activitycode, '' AS code, '' AS activityplace, '' AS method, '' AS hall, ''  AS halladdress, '' AS startdate, '' AS enddate, '' AS starttime, '' AS endtime, '' AS sellingprice, '' AS taxfree, '' AS enrolledby, 'learningtrack' AS module, (SELECT lu1.organization FROM {local_users} lu1 WHERE lu1.userid = lu.id) AS organization, '' AS hallid, (SELECT tci.code FROM {tool_certificate_issues} tci WHERE tci.moduleid = ll1.id AND tci.userid = lu.id AND tci.moduletype = 'learningtracks') AS certificatecode, le.timecreated AS enrolmentdate, le.id as enrolmentid
            FROM {user} lu 
            JOIN {local_lts_enrolment} le ON le.userid = lu.id
            JOIN {local_learningtracks} ll1 ON ll1.id = le.trackid 
            WHERE lu.deleted = 0 
            UNION
            SELECT CONCAT(lcp.id, '-', lu.userid, '^') AS id, lu.userid, lu.id_number AS identity, CONCAT(lu.firstname, ' ', lu.lastname) AS fullname, lcp.cpdid AS activityid, 'cpd' AS activitytype, mle.exam AS activitynameen, mle.examnamearabic AS activitynamear, mle.code AS activitycode, '' AS code, '' AS activityplace, '' AS method, '' AS hall, ''  AS halladdress, mlep.registrationstartdate AS startdate, mlep.registrationenddate AS enddate, mlep.registrationstartdate AS starttime, mlep.registrationenddate AS endtime, '' AS sellingprice, '' AS taxfree, '' AS enrolledby, 'cpd' AS module, lu.organization, '' AS hallid, '' AS certificatecode, lcp.timecreated AS enrolmentdate, lcp.id as enrolmentid
            FROM {local_users} lu 
            JOIN {local_cpd_evidence} lcp ON lcp.userid = lu.userid 
            JOIN {local_cpd} lc ON lc.id = lcp.cpdid 
            JOIN {local_exams} mle ON mle.id = lc.examid
            JOIN {local_exam_profiles} mlep ON mlep.examid = mle.id 
            WHERE lu.deleted = 0 AND lu.approvedstatus = 2 ) AS a 
            WHERE 1 = 1 ";

    }

    public function select() {        
        $this->sql = "SELECT * FROM (SELECT CONCAT(pe.id, '-', lu.userid, '@') AS id, lu.userid, lu.id_number AS identity, CONCAT(lu.firstname, ' ', lu.lastname) AS fullname, pe.programid AS activityid, 'trainingprogram' AS activitytype, tp.name AS activitynameen, tp.namearabic AS activitynamear, tp.code AS activitycode, tpo.code AS code, tpo.trainingmethod AS activityplace, tpo.trainingmethod AS method, h.name AS hall, tpo.halladdress, tpo.startdate AS startdate, tpo.enddate AS enddate, tpo.time AS starttime, tpo.time+tpo.duration AS endtime, tpo.sellingprice, tp.tax_free AS taxfree, pe.usercreated AS enrolledby, 'trainings' AS module, lu.organization, h.id AS hallid, (SELECT tci.code FROM {tool_certificate_issues} tci WHERE tci.moduleid = tpo.id AND tci.userid = lu.userid AND tci.moduletype = 'trainingprogram') AS certificatecode, pe.timecreated AS enrolmentdate, lu.email, '' AS attemptid, '' AS examdate, tpo.id as purchaseorderid, pe.id as enrolmentid
            FROM {local_users} lu 
            JOIN {program_enrollments} pe ON pe.userid = lu.userid AND pe.enrolstatus = 1
            JOIN {role} r ON r.id = pe.roleid AND r.shortname = 'trainee' 
            JOIN {local_trainingprogram} tp ON tp.id = pe.programid 
            JOIN {tp_offerings} tpo ON tpo.trainingid = tp.id AND tpo.id = pe.offeringid 
            LEFT JOIN {hall} h ON h.id = tpo.halladdress 
            WHERE lu.deleted = 0 AND lu.approvedstatus = 2 
            UNION
            SELECT CONCAT(ee.id, '-', lu.userid, '#') AS id, lu.userid, lu.id_number AS identity, CONCAT(lu.firstname, ' ', lu.lastname) AS fullname, ee.examid AS activityid, 'exam' AS activitytype, le.exam AS activitynameen, le.examnamearabic AS activitynamear, le.code AS activitycode, lep.profilecode AS code, '' AS activityplace, 'offline' AS method, h.name AS hall, h.id  AS halladdress, leu.examdate AS startdate, leu.examdate AS enddate, hs.starttime AS starttime, hs.starttime+lep.duration AS endtime, le.sellingprice, le.tax_free AS taxfree, ee.usercreated AS enrolledby, 'exams' AS module, lu.organization, h.id AS hallid, (SELECT tci.code FROM {tool_certificate_issues} tci WHERE tci.moduleid = le.id AND tci.userid = lu.userid AND tci.moduletype = 'exams') AS certificatecode, ee.timecreated AS enrolmentdate, lu.email, leu.attemptid AS attemptid, leu.examdate AS examdate, lep.id as purchaseorderid, ee.id as enrolmentid
            FROM {local_users} lu 
            JOIN {exam_enrollments} ee ON ee.userid = lu.userid AND ee.enrolstatus = 1 
            JOIN {local_exams} le ON le.id = ee.examid 
            JOIN {local_exam_profiles} lep ON lep.examid = le.id 
            JOIN {local_exam_userhallschedules} leu ON leu.profileid = lep.id AND leu.userid = lu.userid 
            LEFT JOIN {hallschedule} hs ON hs.id = leu.hallscheduleid
            LEFT JOIN {hall} h ON h.id = hs.hallid 
            WHERE lu.deleted = 0 AND lu.approvedstatus = 2 
            UNION
            SELECT CONCAT(lea.id, '-', lu.userid, '$') AS id, lu.userid, lu.id_number AS identity, CONCAT(lu.firstname, ' ', lu.lastname) AS fullname, lea.eventid AS activityid, 'events' AS activitytype, lev.title AS activitynameen, lev.titlearabic AS activitynamear, lev.code AS activitycode, '' AS code, lev.method AS activityplace, lev.method AS method, h.name AS hall, lev.halladdress, lev.startdate, lev.enddate, lev.slot AS starttime, lev.slot+lev.eventduration AS endtime, lev.sellingprice, lev.taxfree, lea.usercreated AS enrolledby, 'events' AS module, lu.organization, h.id AS hallid, (SELECT tci.code FROM {tool_certificate_issues} tci WHERE tci.moduleid = lev.id AND tci.userid = lu.userid AND tci.moduletype = 'events') AS certificatecode, lea.timecreated AS enrolmentdate, lu.email, '' AS attemptid, '' AS examdate, lev.id as purchaseorderid, lea.id as enrolmentid
            FROM {local_users} lu 
            JOIN {local_event_attendees} lea ON lea.userid = lu.userid AND lea.enrolstatus = 1 
            JOIN {local_events} lev ON lev.id = lea.eventid 
            LEFT JOIN {hall} h ON h.id = lev.halladdress 
            WHERE lu.deleted = 0 AND lu.approvedstatus = 2 
            UNION 
            SELECT CONCAT(ll1.id, '-', lu.id, '*') AS id, lu.id AS userid, lu.idnumber AS identity, CONCAT(lu.firstname, ' ', lu.lastname) AS fullname, ll1.id AS activityid, 'learningtrack' AS activitytype, ll1.name AS activitynameen, ll1.namearabic AS activitynamear, ll1.code AS activitycode, '' AS code, '' AS activityplace, '' AS method, '' AS hall, ''  AS halladdress, '' AS startdate, '' AS enddate, '' AS starttime, '' AS endtime, '' AS sellingprice, '' AS taxfree, '' AS enrolledby, 'learningtrack' AS module, (SELECT lu1.organization FROM {local_users} lu1 WHERE lu1.userid = lu.id) AS organization, '' AS hallid, (SELECT tci.code FROM {tool_certificate_issues} tci WHERE tci.moduleid = ll1.id AND tci.userid = lu.id AND tci.moduletype = 'learningtracks') AS certificatecode, le.timecreated AS enrolmentdate, lu.email, '' AS attemptid, '' AS examdate, '' as purchaseorderid, le.id as enrolmentid
            FROM {user} lu 
            JOIN {local_lts_enrolment} le ON le.userid = lu.id
            JOIN {local_learningtracks} ll1 ON ll1.id = le.trackid 
            WHERE lu.deleted = 0
            UNION
            SELECT CONCAT(lcp.id, '-', lu.userid, '^') AS id, lu.userid, lu.id_number AS identity, CONCAT(lu.firstname, ' ', lu.lastname) AS fullname, lcp.cpdid AS activityid, 'cpd' AS activitytype, mle.exam AS activitynameen, mle.examnamearabic AS activitynamear, mle.code AS activitycode, '' AS code, '' AS activityplace, '' AS method, '' AS hall, ''  AS halladdress, mlep.registrationstartdate AS startdate, mlep.registrationenddate AS enddate, mlep.registrationstartdate AS starttime, mlep.registrationenddate AS endtime, '' AS sellingprice, '' AS taxfree, '' AS enrolledby, 'cpd' AS module, lu.organization, '' AS hallid, '' AS certificatecode, lcp.timecreated AS enrolmentdate, lu.email, '' AS attemptid, '' AS examdate, '' as purchaseorderid, lcp.id as enrolmentid
            FROM {local_users} lu 
            JOIN {local_cpd_evidence} lcp ON lcp.userid = lu.userid 
            JOIN {local_cpd} lc ON lc.id = lcp.cpdid 
            JOIN {local_exams} mle ON mle.id = lc.examid
            JOIN {local_exam_profiles} mlep ON mlep.examid = mle.id 
            WHERE lu.deleted = 0 AND lu.approvedstatus = 2 ) AS a 
            WHERE 1 = 1 ";

        parent::select();
    }

    public function from() {
        $this->sql .= " ";
    }
    public function joins() {
        global $DB,$USER;
        $this->sql .= " ";
        parent::joins();
    } 

    public function where() {
        $this->sql .= " ";
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
        $lang = current_language();
        if (!empty($this->params['filter_idnumber'])) {
            $this->sql .= " AND a.identity = (:filter_idnumber)";
        }
        if (!empty($this->params['filter_moduleactivitycodelist'])) {
            $this->sql .= " AND a.activitycode = (:filter_moduleactivitycodelist)";
        }
        if (!empty($this->params['filter_moduleactivitynamelist'])) {
            if ($lang == 'en') {
                $this->sql .= " AND a.activitynameen = (:filter_moduleactivitynamelist)";
            } else {
                $this->sql .= " AND a.activitynamear = (:filter_moduleactivitynamelist)";
            }
        }
        if ($this->params['filter_allmodules_trainingprograms'] != 1) {
            $this->sql .= " AND a.activitytype NOT IN ('trainingprogram')" ;
        }
        if ($this->params['filter_allmodules_exams'] != 1) {
            $this->sql .= " AND a.activitytype NOT IN ('exam')" ;
        }
        if ($this->params['filter_allmodules_events'] != 1) {
            $this->sql .= " AND a.activitytype NOT IN ('events')" ;
        }
        if ($this->params['filter_allmodules_learningtracks'] != 1) {
            $this->sql .= " AND a.activitytype NOT IN ('learningtrack')" ;
        }
        if ($this->params['filter_allmodules_cpd'] != 1) {
            $this->sql .= " AND a.activitytype NOT IN ('cpd')" ;
        }
        if (!empty($this->params['filter_halladdress']) && $this->params['filter_halladdress'] > 0) {
            $this->sql .= " AND a.hallid IN (:filter_halladdress)" ;
        }
        if (!empty($this->params['filter_certificatecode']) && $this->params['filter_certificatecode'] > 0) {
            $this->sql .= " AND a.certificatecode IN (:filter_certificatecode)" ;
        }
        if ($this->params['filter_allmodules_certificates'] == 1) {
            $this->sql .= " AND a.certificatecode != '' AND a.certificatecode IS NOT NULL ";
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
            $this->sql .= " AND a.startdate BETWEEN $eststartdate AND $eenenddate";
        }
    }

    public function groupby() {
        $this->sql .= " GROUP BY a.id ";

    }

    public function get_rows($trainingprograms) {
        return $trainingprograms;
    }

    public function column_queries($columnname, $trainingprogramid, $trainingprograms = null) { 
        
    }
}
