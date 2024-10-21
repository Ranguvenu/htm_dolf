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

/** LearnerScript Reports
 * A Moodle block for creating customizable reports
 * @package blocks
 * @subpackage learnerscript
 * @author: sreekanth
 * @date: 2017
 */
namespace block_learnerscript\lsreports;
use block_learnerscript\local\querylib;
use block_learnerscript\local\reportbase;
use block_learnerscript\report;
use block_learnerscript\local\ls as ls;
use context_system;

class report_examgrades extends reportbase implements report {
    /**
     * @param object $report Report object
     * @param object $reportproperties Report properties object
     */
    public function __construct($report, $reportproperties) {
        parent::__construct($report, $reportproperties);
        $this->parent = true;
        $this->columns = array('quizfield' => ['quizfield'] , 'examgradecolumns' => array('exam', 'avggrade', 'grademax', 'gradepass', 'notattemptedusers', 'inprogressusers', 'completedusers'));
        $this->components = array('columns', 'filters', 'permissions', 'plot');
        $this->courselevel = true;
        $this->orderable = array('exam', 'avggrade', 'grademax', 'gradepass', 'notattemptedusers', 'inprogressusers', 'completedusers');
        $this->searchable = array('main.name', 'ltp.name');
        $this->defaultcolumn = 'main.id';
        $this->excludedroles = array("'manager', 'coursecreator', 'editingteacher', 'teacher', 'student', 'trainer', 'em', 'co', 'expert', 'trainee', 'to', 'competencies_official', 'cpd', 'hall_manager', 'financial_manager'");
    }
    public function count() {
        $this->sql = "SELECT COUNT(DISTINCT main.id)";
    }
    public function select() {
        $this->sql = "SELECT DISTINCT main.id, main.name AS name, le.exam AS exam, cm.id AS activityid ";
        parent::select();
    }
    public function from() {
        $this->sql .= " FROM {quiz} as main
                        JOIN {course_modules} as cm ON cm.instance = main.id
                        JOIN {local_exams} le ON le.courseid = cm.course AND le.quizid = main.id";
    }
    public function joins() { 
        global $DB,$USER;
        $systemcontext = context_system::instance();
        if (is_siteadmin()) {

        } else if (!is_siteadmin() && has_capability('local/organization:manage_organizationofficial',$systemcontext)) {
            $this->sql .= " JOIN {local_exam_profiles} lep ON lep.examid = le.id 
                            JOIN {tool_org_order_seats} oos ON oos.fieldid = lep.id AND oos.tablename = 'local_exam_profiles' AND oos.orguserid = " . $USER->id;
        }
        parent::joins();
    }
    public function where() {
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
        if ($this->ls_startdate >= 0 && $this->ls_enddate) {
            $this->sql .= " AND cm.added BETWEEN :ls_fstartdate AND :ls_fenddate ";
            $this->params['ls_fstartdate'] = ROUND($this->ls_startdate);
            $this->params['ls_fenddate'] = ROUND($this->ls_enddate);
        }
    }
    
    public function groupby() { 
        global $CFG; 
    }
    
    /**
     * [get_rows description]
     * @param  array  $users [description]
     * @return [type]        [description]
     */
    public function get_rows($quizs = array()) {
        return $quizs;
    }
    public function column_queries($columnname, $quizid, $courseid = null) {
        global $DB, $USER;
        if($courseid){
            $learnersql  = (new querylib)->get_learners('', $courseid);
        }else{
            $learnersql  = (new querylib)->get_learners('', '%courseid%');
        }
        $usersql = " ";
        $systemcontext = context_system::instance();
        if (is_siteadmin()) {

        } else if (!is_siteadmin() && has_capability('local/organization:manage_organizationofficial',$systemcontext)) {
            $organization = $DB->get_field('local_users','organization',array('userid'=>$USER->id));
            $usersql .= " AND lu.organization = $organization ";
        }

        $where = " AND %placeholder% = $quizid";
        switch ($columnname) {
            case 'grademax':
                $identy = 'q.id';
                $query = "SELECT q.grade AS grademax 
                        FROM {quiz} q
                        WHERE 1 = 1 $where ";
            break;
            case 'gradepass':
                $identy = 'cm.instance';
                $query = "SELECT gi.gradepass AS gradepass 
                        FROM {quiz} q
                        JOIN {course_modules} as cm ON cm.instance = q.id
                        JOIN {modules} m ON cm.module = m.id
                        JOIN {course} c ON c.id = cm.course
                        JOIN {grade_items} gi ON gi.courseid = c.id AND gi.itemmodule = 'quiz' AND gi.iteminstance = q.id  
                        WHERE m.name = 'quiz' AND cm.visible = 1 AND cm.deletioninprogress = 0 AND c.visible = 1 $where ";
            break;
            case 'avggrade':
                $identy = 'gi.iteminstance';
                $query = "SELECT AVG(g.finalgrade) AS avggrade 
                        FROM {grade_grades} g 
                        JOIN {grade_items} gi ON gi.id = g.itemid 
                        JOIN {local_users} lu ON lu.userid = g.userid
                        WHERE g.finalgrade IS NOT NULL  
                        AND gi.itemmodule = 'quiz' $where $usersql  AND lu.deleted = 0 AND lu.approvedstatus = 2";
            break;
            case 'inprogressusers':
                $identy = 'qat.quiz';
                $courseid = 'q.course';
                $query = "SELECT COUNT(DISTINCT qat.userid) AS inprogressusers 
                              FROM {quiz_attempts} qat
                              JOIN {quiz} q ON qat.quiz = q.id 
                              JOIN {local_users} lu ON lu.userid = qat.userid
                             WHERE qat.state = 'inprogress' AND qat.quiz = q.id AND qat.userid IN ($learnersql) $where $usersql  AND lu.deleted = 0 AND lu.approvedstatus = 2 ";
            break;
            case 'completedusers':
                $identy = 'cmo.instance';
                $courseid = 'cmo.course';
                $query = "SELECT COUNT(DISTINCT cmc.userid) AS completedusers 
                            FROM {course_modules_completion} AS cmc
                            JOIN {course_modules} as cmo ON cmo.id = cmc.coursemoduleid
                            JOIN {modules} m ON m.id = cmo.module AND m.name= 'quiz'
                            JOIN {context} con ON con.instanceid = cmo.course
                            JOIN {role_assignments} ra ON ra.contextid = con.id
                            JOIN {role} r ON r.id = ra.roleid AND r.shortname = 'trainee' 
                            JOIN {local_users} lu ON lu.userid = ra.userid
                           WHERE ra.userid = cmc.userid AND cmc.completionstate > 0
                             AND cmo.visible = 1 AND cmc.userid != 2 AND cmc.userid IN ($learnersql) $where $usersql  AND lu.deleted = 0 AND lu.approvedstatus = 2 ";
            break; 
            case 'totalattempts':
                $identy = 'qat.quiz';
                $courseid = 'q.course';
                $query = "SELECT COUNT(DISTINCT qat.userid) AS totalattempts 
                              FROM {quiz_attempts} qat
                              JOIN {quiz} q ON qat.quiz = q.id 
                              JOIN {user} u ON qat.userid = u.id 
                              JOIN {local_users} lu ON lu.userid = u.id
                             WHERE qat.state = 'finished' AND qat.quiz = q.id AND u.deleted = 0 
                               AND u.confirmed = 1 AND qat.userid IN ($learnersql) AND u.suspended = 0 $where $usersql  AND lu.deleted = 0 AND lu.approvedstatus = 2 ";
            break;
            case 'notattemptedusers':
                $identy = 'q.id';
                $courseid = 'cm.course';
                $query = "SELECT COUNT(DISTINCT u.id) AS notattemptedusers 
                            FROM {user} u
                            JOIN {user_enrolments} ue on ue.userid = u.id AND ue.status = 0
                            JOIN {enrol} e ON e.id = ue.enrolid AND e.status = 0
                            JOIN {role_assignments} ra ON ra.userid = ue.userid
                            JOIN {role} r ON r.id = ra.roleid AND r.shortname = 'trainee'
                            JOIN {context} con ON con.id = ra.contextid AND con.contextlevel = 50
                            JOIN {course} c ON c.id = con.instanceid 
                            JOIN {course_modules} as cm ON cm.course = c.id
                            JOIN {modules} m ON m.id = cm.module AND m.name= 'quiz'
                            JOIN {quiz} q ON cm.instance = q.id 
                            JOIN {local_users} lu ON lu.userid = u.id 
                           WHERE  u.id NOT IN ( SELECT qat.userid
                                                  FROM {quiz_attempts} qat
                                                  JOIN {quiz} q1 ON qat.quiz = q1.id 
                                                 WHERE q1.id = q.id $where)
                             AND u.id > 2 AND u.deleted = 0 AND u.confirmed = 1 AND u.suspended = 0 AND ra.userid IN ($learnersql) $where $usersql  AND lu.deleted = 0 AND lu.approvedstatus = 2 ";
            break; 
            default:
                return false;
            break;
        }
        $query = str_replace('%placeholder%', $identy, $query);
        $query = str_replace('%courseid%', $courseid, $query);
        return $query;
    }
}
