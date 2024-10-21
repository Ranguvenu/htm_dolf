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
 * @author: Jahnavi Nanduri
 * @date: 2023
 */
namespace block_learnerscript\lsreports;
use block_learnerscript\local\pluginbase;
use local_exams\local\exams;
use html_writer;
use context_system;

class plugin_examenrolcolumns extends pluginbase {

    public function init() {
        $this->fullname = get_string('examenrolcolumns', 'block_learnerscript');
        $this->type = 'undefined';
        $this->form = true;
        $this->reporttypes = array('examenrol');
    }

    public function summary($data) {
        return format_string($data->columname);
    }

    public function colformat($data) {
        $align = (isset($data->align)) ? $data->align : '';
        $size = (isset($data->size)) ? $data->size : '';
        $wrap = (isset($data->wrap)) ? $data->wrap : '';
        return array($align, $size, $wrap);
    }

    public function execute($data, $row, $user, $courseid, $starttime = 0, $endtime = 0) {
        global $DB, $CFG;
        $lang= current_language();
        $systemcontext = context_system::instance();
        $certinfo = $DB->get_record_sql("SELECT * FROM {tool_certificate_issues} tci 
                                        WHERE tci.moduleid = $row->examid AND tci.moduletype = 'exams' AND tci.userid = $row->userid");
        // $userscheduledata = $DB->get_record_sql("SELECT * FROM {local_exam_userhallschedules} WHERE examid = $row->examid AND profileid = $row->profileid AND userid =$row->userid ORDER BY id DESC LIMIT 1 ");
        // $row->examdate = $userscheduledata->examdate;
        switch ($data->column) {
            case 'examinee':
                $lang= current_language();
                if($lang == 'ar'){
                    $username = $DB->get_field('local_users', "CONCAT(firstnamearabic, ' ', lastnamearabic)", array('userid' => $row->userid));
                }else{
                    $username = $DB->get_field('local_users', "CONCAT(firstname, ' ', lastname)", array('userid' => $row->userid));
                }
                $row->{$data->column} = !empty($username) ? $username : '--';
            break;
            case 'profilelanguage':
                if ($row->{$data->column} == 1) {
                    $row->{$data->column} = get_string('english','local_exams');
                } else {
                    $row->{$data->column} = get_string('arabic','local_exams');
                }
            break;
            case 'enrollmentdate':
                if (!empty($row->{$data->column})) {
                    $examdate = userdate($row->{$data->column},get_string('strftimedatemonthabbr', 'core_langconfig'));
                    $examtime = date('h:i', $row->{$data->column});
                    $examdatemeridian = gmdate('a', $row->{$data->column});

                    if( $lang == 'ar'){
                        $examdatemeridian = ($examdatemeridian == 'am')? 'ص':' م';

                    }else{
                        $examdatemeridian = ($examdatemeridian == 'am')? 'AM':'PM';
                    }
                    $row->{$data->column} = !empty($examdate) ? $examdate . ' ' . $examtime . ' ' . $examdatemeridian : '--';
                } else {
                    $row->{$data->column} = '--';
                }
            break;
            case 'examtime':
                if (!empty($row->hallscheduleid)) { 
                    $examstarttime = $DB->get_field_sql("SELECT starttime FROM {hallschedule} WHERE id = $row->hallscheduleid");                        
                    $examtime = (strtotime(userdate($row->examdate, '%d-%m-%Y'))+userdate((($examstarttime)), '%H')*3600 + userdate(($examstarttime), '%M')*60);
                    $examtime = date("h:i",$examtime);
                    $meridian = (userdate((($examstarttime)), '%H')*3600 
                + userdate(($examstarttime), '%M')*60);
                    $examdatemeridiann = gmdate('a', $meridian);
                    if( $lang == 'ar'){
                        $examdatemeridiann = ($examdatemeridiann == 'am')? 'ص':' م';

                    }else{
                        $examdatemeridiann = ($examdatemeridiann == 'am')? 'AM':'PM';
                    }
                } else {
                    $examtime = date("h:i",$row->examdate);
                    $examdatemeridiann = gmdate('a',$row->examdate);
                }
                 $row->{$data->column} = !empty($examtime) ? $examtime . ' ' .$examdatemeridiann : '--';
                 break;
            case 'examdate': 
                if (!empty($row->examdate)) {
                    $examdate = userdate($row->examdate,get_string('strftimedatemonthabbr', 'core_langconfig'));

                    if( $lang == 'ar'){
                        $examdatemeridiann = ($examdatemeridiann == 'am')? 'ص':' م';

                    }else{
                        $examdatemeridiann = ($examdatemeridiann == 'am')? 'AM':'PM';
                    }

                    $row->{$data->column} = !empty($examdate) ? $examdate : '--';
                } else {
                    $row->{$data->column} = '--';
                }
            break;
            case 'enrolledby':
                 $roleinfo = $DB->get_field_sql('SELECT rol.shortname FROM {role} rol 
                                        JOIN {role_assignments} rola ON rola.roleid = rol.id
                                        WHERE rola.userid =:userid and contextid =:contextid',['userid'=>$row->usercreated,'contextid'=>$systemcontext->id]);
                if (!empty($roleinfo)) {
                    if ($roleinfo == 'organizationofficial' || $roleinfo == 'examofficial') {
                        $lang= current_language();
                        if($lang == 'ar'){
                            $ooname = $DB->get_field_sql("SELECT CONCAT(lu.firstnamearabic, ' ', lu.lastnamearabic) FROM {local_users} lu WHERE lu.userid = $row->usercreated");
                        } else {
                            $ooname = $DB->get_field_sql("SELECT CONCAT(u.firstname, ' ', u.lastname) FROM mdl_user u WHERE u.id = $row->usercreated");
                        }
                        $row->{$data->column} = html_writer::link("$CFG->wwwroot/local/userapproval/userprofile.php?id=$row->usercreated", $ooname, array("target" => "_blank"));
                    } else {
                        $row->{$data->column} = get_string('selfenrol', 'block_learnerscript');
                    }
                } else {
                    $row->{$data->column} = get_string('facacdemy', 'block_learnerscript');
                }                      
            break;
            case 'attemptno';   
                if (!empty($row->attemptid) && $row->attemptid != 0) {
                    $row->attemptnumber = $DB->get_field_sql("SELECT attemptid FROM {local_exam_attempts} WHERE examid = $row->examid AND id = $row->attemptid");
                    $row->finalattemptno = $row->attemptnumber;
                } else {
                    $row->finalattemptno = $DB->get_field_sql("SELECT id FROM {quiz_attempts} WHERE quiz = $row->quizid AND userid = $row->userid AND attempt = 1 ");
                    if($row->finalattemptno){
                        $row->finalattemptno = 1;
                    }else{
                        $row->finalattemptno = 0;
                    }
                    $quizattempt = $DB->get_field_sql("SELECT id FROM {quiz_attempts} WHERE quiz = $row->quizid AND userid = $row->userid AND attempt = 1 AND sumgrades NOT IN (-1, -2)");
                    if($quizattempt){
                        $row->attemptnumber = 1;
                    }else{
                        $row->attemptnumber = 0;
                    }
                }
                $row->{$data->column} = !empty($row->attemptnumber) ? $row->attemptnumber : 0;        
            break;
            case 'hall':  
                if (!empty($row->hallscheduleid)) {
                    $hallname =  $DB->get_field_sql("SELECT h.name FROM {hall} h JOIN {hallschedule} hs ON hs.hallid = h.id WHERE hs.id = $row->hallscheduleid");
                    $row->{$data->column} = !empty($hallname) ? $hallname : '--';
                }
            break;
            case 'result':  
                if (!empty($row->scheduleid)) {
                    $scheduledetails = $DB->get_record('hallschedule', ['id'=>$row->hallscheduleid]);
                    $schedulestarttime = $scheduledetails->starttime;
                    $startedtime = (strtotime(userdate($row->examdatetime, '%d-%m-%Y'))+userdate((($schedulestarttime)), '%H')*3600 + userdate(($schedulestarttime), '%M')*60);
                    $attemptid = $row->finalattemptno;
                    // if ($row->attemptno == 0) {
                    //     $attemptid = 1;
                    // } else {
                    //     $attemptid = $DB->get_field('local_exam_attempts', 'attemptid', ['id'=>$row->attemptid]);
                    // }
                    if (!empty($attemptid)) {
                        $sql = "SELECT qa.* 
                        FROM {quiz_attempts} qa 
                        WHERE qa.quiz = $row->quizid AND qa.attempt = $attemptid AND qa.userid =". $row->userid;
                        $quizattempt = $DB->get_record_sql($sql);
                    } else {
                        $quizattempt = 0;
                    }
                    
                    if ($quizattempt) {
                        if($quizattempt->sumgrades == -1) {
                            $gradestatus = get_string('absent', 'block_learnerscript');
                        } elseif($quizattempt->sumgrades == -2) {
                            $gradestatus = get_string('unknow', 'block_learnerscript');
                        } else {
                            $gradestatus = ROUND($quizattempt->sumgrades, 2);
                        }
                    } elseif(time() < $startedtime) {
                        $gradestatus = get_string('notstarted', 'block_learnerscript');
                    } else {
                        $gradestatus = get_string('unknow', 'block_learnerscript');
                    }
                    $status = $gradestatus;
                    if (is_string($status)) {
                        $row->{$data->column} = $status;
                    } else {
                        $sql = "SELECT ROUND(gg.finalgrade, 0) as finalgrade, gi.gradepass
                                FROM {grade_items} gi
                                JOIN {grade_grades} gg on gg.itemid = gi.id
                                WHERE gi.courseid = {$row->courseid} AND gi.itemtype = 'mod' AND gi.itemmodule  = 'quiz' AND gi.iteminstance = {$row->quizid} AND gg.userid = {$row->userid} ";
                        $passinggrade = $DB->get_record_sql($sql);

                        $grade = !empty($passinggrade->finalgrade) ? $passinggrade->finalgrade : 0;
                        $row->usergrade = $grade ;
                        if ($grade >= $row->passinggrade) {
                            $row->{$data->column} = get_string('exampassed', 'block_learnerscript');
                        } else {
                            $row->{$data->column} = get_string('examfailed', 'block_learnerscript');
                        }
                    }
                } else {
                    $row->{$data->column} = get_string('notstarted', 'block_learnerscript');
                } 
            break;
            case 'completiondate':
                if(!empty($row->scheduleid)) {
                $status = (new exams)->gradestatus($row->scheduleid);
                    if (!is_string($status)) {
                        $completedon = $DB->get_field('exam_completions', 'completiondate', ['examid' => $row->examid, 'userid' => $row->userid, 'profileid' => $row->profileid]);
                        $row->{$data->column} = !empty($completedon) ? userdate($completedon,get_string('strftimedatemonthabbr', 'core_langconfig')) : '--';
                    }else{
                        $row->{$data->column} = '--'; 
                    }
                } else {
                    $row->{$data->column} = '--';
                }
            break;
            case 'certificatecode':
                if($row->result == get_string('exampassed', 'local_exams')){ 
                    if (!$this->downloading) {
                        $row->certcode = html_writer::link("$CFG->wwwroot/admin/tool/certificate/view.php?code=$certinfo->code", $certinfo->code, array("target" => "_blank"));  
                    } else {
                        $row->certcode = $CFG->wwwroot . "/admin/tool/certificate/view.php?code=$certinfo->code";
                    } 
                }else{
                   $row->certcode = '--'; 
                }
                $row->{$data->column} = $row->certcode;
            break;
            case 'grade':
                if($row->result == get_string('exampassed', 'local_exams') || $row->result == get_string('examfailed', 'local_exams')){ 
                    $row->{$data->column} = $row->usergrade;
                }else{
                   $row->{$data->column} = '--'; 
                }
            break;
        }
        return (isset($row->{$data->column}))? $row->{$data->column} : ' -- ';
    }
}
