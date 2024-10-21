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
use local_exams\local\exams;
use context_system;

defined('MOODLE_INTERNAL') || die();
class report_examenrol extends reportbase implements report {

    public function __construct($report, $reportproperties) {
        global $DB;
        parent::__construct($report, $reportproperties);
        $columns = ['examinee', 'email', 'identity','organizationname', 'examname', 'examowner', 'examcode', 'profilecode', 'profilelanguage', 'enrollmentdate', 'enrolledby', 'examdate','examtime', 'attemptno', 'hall', 'completiondate', 'result','grade', 'certificatecode'];
        $this->columns = ['examenrolcolumns' => $columns];
        $this->components = array('columns', 'conditions', 'ordering', 'filters','permissions', 'plot');
        $this->filters = array('exam', 'code', 'examprofile', 'idnumber', 'result', 'name', 'examenroldate', 'startdate', 'ownedby');
        $this->parent = true;
        $this->orderable = array('examinee', 'identity', 'examname', 'examcode', 'profilecode', 'profilelanguage', 'enrollmentdate', 'enrolledby', 'examdate', 'attemptno', 'hall', 'completiondate', 'result', 'certificatecode');
        $lang= current_language();
        if( $lang == 'ar'){
            $this->searchable = array('CONCAT(lu.firstnamearabic, " ", lu.lastnamearabic)', 'le.examnamearabic', 'lu.id_number', 'le.code', 'lep.profilecode');
        } else{
            $this->searchable = array('CONCAT(lu.firstname, " ", lu.lastname)', 'le.exam', 'lu.id_number', 'le.code', 'lep.profilecode');
        }
        // $this->defaultcolumn = "leu.id";
        $this->sqlorder['column'] = 'leu.examdate';
        $this->sqlorder['dir'] = 'DESC';
        $this->excludedroles = array("'manager', 'coursecreator', 'editingteacher', 'teacher', 'student', 'em', 'expert', 'trainee', 'competencies_official', 'cpd', 'hall_manager', 'financial_manager', 'trainer', 'to', 'trainee_mof', 'mof_testing'");
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
       $this->sql = "SELECT COUNT(DISTINCT leu.id)";

    }

    public function select() {

        $lang= current_language();
        if( $lang == 'ar'){
            $this->sql = "SELECT DISTINCT leu.id, ee.id AS enrolid, ee.examid, ee.userid, ee.profileid, ee.timecreated AS enrollmentdate, ee.usercreated , leu.hallscheduleid, le.examnamearabic AS examname, le.code AS examcode, ep.language AS profilelanguage, ep.quizid, ee.courseid, ep.passinggrade,ep.profilecode, lu.id_number AS identity, leu.id AS scheduleid, leu.examdate, lu.email, leu.examdate AS examdatetime, leu.attemptid, le.ownedby AS examowner,(SELECT fullnameinarabic FROM {local_organization} lo WHERE lo.id = lu.organization) AS organizationname";
        } else{
            $this->sql = "SELECT DISTINCT leu.id, ee.id AS enrolid, ee.examid, ee.userid, ee.profileid, ee.timecreated AS enrollmentdate, ee.usercreated , leu.hallscheduleid, le.exam AS examname, le.code AS examcode, ep.language AS profilelanguage, ep.quizid, ee.courseid, ep.passinggrade, ep.profilecode, lu.id_number AS identity, leu.id AS scheduleid, leu.examdate, lu.email, leu.examdate AS examdatetime, leu.attemptid, le.ownedby AS examowner, (SELECT fullname FROM {local_organization} lo WHERE lo.id = lu.organization) AS organizationname";
        }

        parent::select();
    }

    public function from() {
        $this->sql .= " FROM {exam_enrollments} ee";
    }
    public function joins() {
        $this->sql .= " JOIN {local_exams} le ON le.id = ee.examid
                        JOIN {local_users} lu ON lu.userid = ee.userid 
                        JOIN {local_exam_userhallschedules} leu ON leu.userid = ee.userid AND leu.examid = ee.examid  
                        JOIN {local_exam_profiles} as ep ON leu.profileid=ep.id
                        LEFT JOIN {hallschedule} hs ON hs.id = leu.hallscheduleid 
                        LEFT JOIN {hall} h ON h.id = hs.hallid ";        
        parent::joins();
    }

    public function where() {
        global $DB,$USER;
        $this->sql .= " WHERE ee.enrolstatus = 1 AND lu.deleted = 0 AND lu.approvedstatus = 2 AND leu.id IN (SELECT MAX(id) FROM {local_exam_userhallschedules} GROUP BY examid, userid) ";
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
        global $DB; 
        
        // if ($this->params['filter_result'] != -1) {
        //     $schedulerecords = $DB->get_records_sql("SELECT DISTINCT ee.id, ee.courseid, ee.userid, ep.quizid, ee.examid, ep.passinggrade, leu.id AS scheduleid, leu.attemptid, leu.hallscheduleid, leu.examdate 
        //             FROM {exam_enrollments} ee 
        //             JOIN {local_exams} le ON le.id = ee.examid 
        //             JOIN {local_users} lu ON lu.userid = ee.userid 
        //             JOIN {local_exam_userhallschedules} leu ON leu.userid = ee.userid AND leu.examid = ee.examid 
        //             JOIN {local_exam_profiles} as ep ON ep.id = leu.profileid 
        //             WHERE ee.enrolstatus = 1 AND lu.deleted = 0 AND lu.approvedstatus = 2 
        //             AND leu.id IN (SELECT MAX(id) FROM {local_exam_userhallschedules} GROUP BY examid, userid)");
        //     foreach ($schedulerecords as $schedulerecord) {
        //         if (!empty($schedulerecord->scheduleid)) {
        //             $scheduledetails = $DB->get_record('hallschedule', ['id'=>$schedulerecord->hallscheduleid]);
        //             $schedulestarttime = $scheduledetails->starttime;
        //             $startedtime = (strtotime(userdate($schedulerecord->examdate, '%d-%m-%Y'))+userdate((($schedulestarttime)), '%H')*3600 + userdate(($schedulestarttime), '%M')*60);
        //             if ($schedulerecord->attemptid == 0) {
        //                 $attemptid = 1;
        //             } else {
        //                 $attemptid = $DB->get_field('local_exam_attempts', 'attemptid', ['id'=>$schedulerecord->attemptid]);
        //             }
        //             $sql = "SELECT qa.* 
        //             FROM {quiz_attempts} qa 
        //             WHERE qa.quiz = $schedulerecord->quizid AND qa.attempt = $attemptid AND qa.userid =". $schedulerecord->userid;
        //             $quizattempt = $DB->get_record_sql($sql);
                    
        //             if ($quizattempt) {
        //                 if($quizattempt->sumgrades == -1) {
        //                     $gradestatus = get_string('absent', 'block_learnerscript');
        //                 } elseif($quizattempt->sumgrades == -2) {
        //                     $gradestatus = get_string('unknow', 'block_learnerscript');
        //                 } else {
        //                     $gradestatus = ROUND($quizattempt->sumgrades, 2);
        //                 }
        //             } elseif(time() < $startedtime) {
        //                 $gradestatus = get_string('notstarted', 'block_learnerscript');
        //             } else {
        //                 $gradestatus = get_string('unknow', 'block_learnerscript');
        //             }
        //             $status = $gradestatus;
        //             if (is_string($status)) {
        //                 $finalstatus[] = $schedulerecord->id . ',' .$status;
        //             } else {
        //                 $sql = "SELECT ROUND(gg.finalgrade, 0) as finalgrade, gi.gradepass
        //                         FROM {grade_items} gi
        //                         JOIN {grade_grades} gg on gg.itemid = gi.id
        //                         WHERE gi.courseid = {$schedulerecord->courseid} AND gi.itemtype = 'mod' AND gi.itemmodule  = 'quiz' AND gi.iteminstance = {$schedulerecord->quizid} AND gg.userid = {$schedulerecord->userid} ";
        //                 $passinggrade = $DB->get_record_sql($sql);

        //                 $grade = !empty($passinggrade->finalgrade) ? $passinggrade->finalgrade : 0;
        //                 if ($grade >= $schedulerecord->passinggrade) {
        //                     $finalstatus[] = $schedulerecord->id . ',' . get_string('exampassed', 'block_learnerscript');
        //                 } else {
        //                     $finalstatus[] = $schedulerecord->id . ',' . get_string('examfailed', 'block_learnerscript');
        //                 }
        //             }
        //         } else {
        //             $finalstatus[] = $schedulerecord->id . ',' . get_string('notstarted', 'block_learnerscript');
        //         }
        //     }
        //     $data = array();
        //     foreach ($finalstatus as $key => $value) {
                
        //         $status = explode(',', $value);
        //         if (!empty($this->params['filter_result']) && $this->params['filter_result'] > 0) {
        //             if ($this->params['filter_result'] == 1 && $status[1] == get_string('unknow', 'block_learnerscript')) {
        //                 if (!empty($status[0])) {
        //                     $data[] = $status[0];
        //                 } else {
        //                     $data[] = -1;
        //                 }
        //             } else if ($this->params['filter_result'] == 2 && $status[1] == get_string('absent', 'block_learnerscript')) {
        //                 if (!empty($status[0])) {
        //                     $data[] = $status[0];
        //                 } else {
        //                     $data[] = -1;
        //                 }
        //             } else if ($this->params['filter_result'] == 3 && $status[1] == get_string('exampassed', 'block_learnerscript')) { 
        //                 if (!empty($status[0])) {
        //                     $data[] = $status[0];
        //                 } else {
        //                     $data[] = -1;
        //                 } 
        //             } else if ($this->params['filter_result'] == 4 && $status[1] == get_string('examfailed', 'block_learnerscript')) {
        //                 if (!empty($status[0])) {
        //                     $data[] = $status[0];
        //                 } else {
        //                     $data[] = -1;
        //                 }

        //             } else {
        //                 $data[] = -1;
        //             }
        //         } else if ($this->params['filter_result'] > -1 && $status[1] == get_string('notstarted', 'block_learnerscript')) {
        //             if (!empty($status[0])) {
        //                 $data[] = $status[0];
        //             } else {
        //                 $data[] = -1;
        //             }
        //         }
        //     }
        //     if (!empty($data)) {
        //         $enrollmentids = implode(',', $data);
        //         $this->sql .= " AND ee.id IN ($enrollmentids)";
        //     }  
        // }
        if (!empty($this->params['filter_result']) && $this->params['filter_result'] == 0) {
            $this->sql .= " AND ((CONCAT(IF(leu.attemptid = 0, 1, (SELECT lea.attemptid FROM mdl_local_exam_attempts lea WHERE lea.id = leu.attemptid)), '', lu.userid, '', ep.quizid) NOT IN (SELECT CONCAT(MAX(qa.attempt), '', qa.userid, '', qa.quiz) FROM mdl_quiz_attempts qa WHERE 1 = 1 GROUP BY qa.userid, qa.quiz)) AND (UNIX_TIMESTAMP() < (UNIX_TIMESTAMP(FROM_UNIXTIME(leu.examdate, '%Y-%m-%d')) + FROM_UNIXTIME(IF(hs.starttime IS NULL, 0, hs.starttime), '%H')*3600 + FROM_UNIXTIME(IF(hs.starttime IS NULL, 0, hs.starttime), '%i')*60)))";
        } else if ($this->params['filter_result'] == 1) {
            $this->sql .= " AND (CONCAT((IF(leu.attemptid = 0, 1, (SELECT lea.attemptid FROM mdl_local_exam_attempts lea WHERE lea.id = leu.attemptid))), '', lu.userid, '', ep.quizid) IN (SELECT CONCAT(qa.attempt, '', qa.userid, '', qa.quiz) FROM mdl_quiz_attempts qa WHERE 1 = 1 AND qa.sumgrades = -2) OR ((CONCAT(lu.userid,'-',ep.quizid) NOT IN (SELECT DISTINCT CONCAT(userid,'-',quiz) FROM mdl_quiz_attempts WHERE 1 = 1 GROUP BY userid, quiz)) AND (UNIX_TIMESTAMP() > (UNIX_TIMESTAMP(FROM_UNIXTIME(leu.examdate, '%Y-%m-%d')) + FROM_UNIXTIME(IF(hs.starttime IS NULL, 0, hs.starttime), '%H')*3600 + FROM_UNIXTIME(IF(hs.starttime IS NULL, 0, hs.starttime), '%i')*60)))) ";
        } else if ($this->params['filter_result'] == 2) {
            $this->sql .= " AND (CONCAT((IF(leu.attemptid = 0, 1, (SELECT lea.attemptid FROM mdl_local_exam_attempts lea WHERE lea.id = leu.attemptid))), '', lu.userid, '', ep.quizid) IN (SELECT CONCAT(qa.attempt, '', qa.userid, '', qa.quiz) FROM mdl_quiz_attempts qa WHERE 1 = 1 AND qa.sumgrades = -1))";
        } else if ($this->params['filter_result'] == 3) {
            $this->sql .= " AND CONCAT(lu.userid,'-',ep.quizid, '-', (IF(leu.attemptid = 0, 1, (SELECT lea.attemptid FROM mdl_local_exam_attempts lea WHERE lea.id = leu.attemptid)))) IN (SELECT DISTINCT(CONCAT(gg.userid, '-', qa.quiz,'-',qa.attempt)) 
                FROM mdl_grade_items gi JOIN mdl_grade_grades gg on gg.itemid = gi.id 
                JOIN mdl_quiz_attempts qa ON qa.quiz = gi.iteminstance AND gg.userid = qa.userid 
                JOIN mdl_local_exam_profiles lep ON lep.quizid = qa.quiz 
                WHERE gi.itemtype = 'mod' AND gi.itemmodule  = 'quiz' 
                AND gg.finalgrade >= lep.passinggrade AND qa.id IN (SELECT MAX(id) FROM mdl_quiz_attempts WHERE sumgrades NOT IN (-1, -2) GROUP BY userid, quiz)) ";
        } else if ($this->params['filter_result'] == 4) {
            $this->sql .= " AND CONCAT(lu.userid,'-',ep.quizid, '-', (IF(leu.attemptid = 0, 1, (SELECT lea.attemptid FROM mdl_local_exam_attempts lea WHERE lea.id = leu.attemptid)))) IN (SELECT DISTINCT(CONCAT(gg.userid, '-', qa.quiz,'-',qa.attempt)) 
                FROM mdl_grade_items gi JOIN mdl_grade_grades gg on gg.itemid = gi.id 
                JOIN mdl_quiz_attempts qa ON qa.quiz = gi.iteminstance AND gg.userid = qa.userid 
                JOIN mdl_local_exam_profiles lep ON lep.quizid = qa.quiz 
                WHERE gi.itemtype = 'mod' AND gi.itemmodule  = 'quiz' 
                AND gg.finalgrade < lep.passinggrade AND qa.id IN (SELECT MAX(id) FROM mdl_quiz_attempts WHERE sumgrades NOT IN (-1, -2) GROUP BY userid, quiz))";
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
            $this->sql .= " AND leu.examdate BETWEEN $eststartdate AND $eenenddate";
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
            $this->sql .= " AND ee.timecreated BETWEEN $ststartdate AND $enenddate";
        } 
    
        if ($this->ls_startdate > 0 && $this->ls_enddate) {
            $this->params['ls_fstartdate'] = ROUND($this->ls_startdate);
            $this->params['ls_fenddate'] = ROUND($this->ls_enddate);
            $this->sql .= " AND le.timecreated BETWEEN :ls_fstartdate AND :ls_fenddate ";
        } 

        if (!empty($_SESSION['filter_userid']) && $_SESSION['filter_userid'] > 0) {
            $this->sql .= " AND lu.userid = " . $_SESSION['filter_userid'];
        }
        if (!empty($_SESSION['filter_orguser']) && $_SESSION['filter_orguser'] > 0) {
            $this->sql .= " AND ee.usercreated = " . $_SESSION['filter_orguser'];
        }
        if (!empty($_SESSION['filter_referenceid']) && $_SESSION['filter_referenceid'] > 0) {
            $this->sql .= " AND ep.id = " . $_SESSION['filter_referenceid'];
        }
        if (!empty($this->params['filter_exam']) && $this->params['filter_exam'] > 0) {
            $this->sql .= " AND le.id IN (:filter_exam)";
        }
        if (!empty($this->params['filter_examprofile'])) {
            $this->sql .= " AND ep.profilecode LIKE (:filter_examprofile)";
        }
        if (!empty($this->params['filter_code'])) {
            $this->sql .= " AND le.code LIKE (:filter_code)";
        } 
        if (!empty($this->params['filter_idnumber'])) {
            $this->sql .= " AND lu.id_number = (:filter_idnumber)";
        }
        if (!empty($this->params['filter_name'])) {
            $this->sql .= " AND h.name = (:filter_name)";
        }
        if (!empty($this->params['filter_ownedby'])) {
            $ownedby = $this->params['filter_ownedby'];
            $this->sql .= " AND le.ownedby LIKE '".$ownedby."'";
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
