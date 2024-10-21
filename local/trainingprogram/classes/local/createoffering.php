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
namespace local_trainingprogram\local;

require_once($CFG->dirroot.'/mod/msteams/lib.php');
require_once($CFG->dirroot . '/mod/zoom/lib.php');
require_once($CFG->dirroot . '/mod/webexactivity/lib.php');
require_once($CFG->dirroot.'/mod/zoom/locallib.php');
require_once($CFG->dirroot.'/group/lib.php');
require_once($CFG->dirroot.'/mod/quiz/lib.php');
require_once($CFG->dirroot.'/mod/attendance/lib.php');
require_once($CFG->dirroot.'/mod/teamsmeeting/lib.php');
require_once($CFG->dirroot.'/course/dnduploadlib.php');

use \core_availability\tree;
use \availability_group\condition;
use mod_attendance_external;
use stdClass;
use \local_trainingprogram\local\dataprovider as dataprovider;


/**
 * TODO describe file createoffering
 *
 * @package    local_trainingprogram
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class createoffering {

    private $courseid;

    private $offeringcode;

    public $groupid;

    public $sectionid;

    private $groupcode;

    private $json;

    public $attendanceid;

    public $quizid;

    public $meetingid;

    public function __construct($courseid, $offeringcode, $data, $program){
         global $DB;
        $this->courseid = $courseid;
        $this->offeringcode = $offeringcode;
        $this->meetingid = $meetingid;

        $meetingtype = (int) $data->meetingtype;



       // $group = groups_get_group_by_idnumber((int)$this->courseid,$this->offeringcode);
        $group = $DB->get_record_sql("SELECT * FROM {groups} WHERE idnumber = '$this->offeringcode'");

        if($group){
            $this->groupid = $group->id;
            $this->json = \core_availability\tree::get_root_json(array(
                \availability_group\condition::get_json($group->id)), \core_availability\tree::OP_AND, false);
        } else {
            
            $groupdata = new stdClass;
            $groupdata->name= $this->offeringcode;
            $groupdata->idnumber= $this->offeringcode;
            $groupdata->courseid= $this->courseid;
            $this->groupid = groups_create_group($groupdata);
            $this->json = \core_availability\tree::get_root_json(array(
                \availability_group\condition::get_json($this->groupid)), \core_availability\tree::OP_AND, false);
          
        }
        
        if($data->id > 0) {

            $sectionid = $DB->get_field_sql('SELECT sections FROM {tp_offerings} WHERE id = '.$data->id .'');

            if($sectionid){
                $section = $DB->get_field('course_sections','section',['id'=>$sectionid]);
               
               $this->sectionid = $section;
            } else {

                $this->createsection_seperategroup();
            }

            $offering = $DB->get_record('tp_offerings',array('id'=> $data->id));
           
        } else {

            $this->createsection_seperategroup();

            $offering = null;
        }  

        if ($data->trainingmethod == 'offline'){

            if(!is_null($offering)) {

                if($offering->trainingmethod == 'online') {
                    if((int)$offering->meetingid != 0){
                        if((int)$offering->meetingtype == 2) {
                            $webexid = (int)$offering->meetingid;
                            $mod_name = 'webexactivity';
                            $moduleid = $DB->get_field_sql("SELECT id FROM {modules} WHERE name = '$mod_name'");
                            $cmidsql = 'SELECT com.id FROM {course_modules} as com JOIN {course_sections} as cos ON com.section = cos.id AND com.course = cos.course WHERE  cos.course = '.$this->courseid.' AND cos.section = '.$sectionid.' AND com.module = '.$moduleid.' AND com.instance = '.$webexid.'';
                            $cmid = (int)$DB->get_field_sql($cmidsql);
                            course_delete_module($cmid);
                           //$this->deleteteamsmeeting($teamsid);
                            $this->meetingid = 0;
                        } elseif((int)$offering->meetingtype == 3) {

                          $teamsid = (int)$offering->meetingid;
                          $mod_name = 'teamsmeeting';
                           $moduleid = $DB->get_field_sql("SELECT id FROM {modules} WHERE name = '$mod_name'");
                            $cmidsql = 'SELECT com.id FROM {course_modules} as com JOIN {course_sections} as cos ON com.section = cos.id AND com.course = cos.course WHERE  cos.course = '.$this->courseid.' AND cos.section = '.$sectionid.' AND com.module = '.$moduleid.' AND com.instance = '.$teamsid.'';
                            $cmid = (int)$DB->get_field_sql($cmidsql);
                          course_delete_module($cmid);
                          //$this->deleteteamsmeeting($teamsid);
                          $this->meetingid = 0;

                        } else {

                          $zoomid = (int)$offering->meetingid;
                           $mod_name = 'zoom';
                           $moduleid = $DB->get_field_sql("SELECT id FROM {modules} WHERE name = '$mod_name'");
                            $cmidsql = 'SELECT com.id FROM {course_modules} as com JOIN {course_sections} as cos ON com.section = cos.id AND com.course = cos.course WHERE  cos.course = '.$this->courseid.' AND cos.section = '.$sectionid.' AND com.module = '.$moduleid.' AND com.instance = '.$zoomid.'';
                            $cmid = (int)$DB->get_field_sql($cmidsql);
                          course_delete_module($cmid);
                          //$this->deletezoom($zoomid);
                          $this->meetingid = 0;

                        } 
                        
                    } 
                } 

            }
            
        }

        if ($data->trainingmethod == 'elearning'){

            if(!is_null($offering)) {
            
                if($offering->trainingmethod == 'online' ||  $data->trainingmethod == 'offline') {

                    $sectionid = $DB->get_field_sql('SELECT sections FROM {tp_offerings} WHERE id = '.$data->id .'');
                    $mod_name = 'attendance';
                    $attendancemoduleid = $DB->get_field_sql("SELECT id FROM {modules} WHERE name = '$mod_name'");
                    $sql = 'SELECT com.instance FROM {course_modules} as com JOIN {course_sections} as cos ON com.section = cos.id AND com.course = cos.course WHERE  cos.course = '.$this->courseid.' AND cos.section = '.$sectionid.' AND com.module = '.$attendancemoduleid.'';
                    $attendanceid = (int)$DB->get_field_sql($sql);
                    if($attendanceid) {
                        $cmidsql = 'SELECT com.id FROM {course_modules} as com JOIN {course_sections} as cos ON com.section = cos.id AND com.course = cos.course WHERE  cos.course = '.$this->courseid.' AND cos.section = '.$sectionid.' AND com.module = '.$attendancemoduleid.' AND com.instance = '.$attendanceid.'';
                        $cmid = (int)$DB->get_field_sql($cmidsql);
                        course_delete_module($cmid);
                        //attendance_delete_instance($attendanceid);
                    }

                } 
                if($offering->trainingmethod == 'online') {
                    if((int)$offering->meetingid != 0){
                        if((int)$offering->meetingtype == 2) {
                            $webexid = (int)$offering->meetingid;
                            $mod_name = 'webexactivity';
                            $moduleid = $DB->get_field_sql("SELECT id FROM {modules} WHERE name = '$mod_name'");
                            $cmidsql = 'SELECT com.id FROM {course_modules} as com JOIN {course_sections} as cos ON com.section = cos.id AND com.course = cos.course WHERE  cos.course = '.$this->courseid.' AND cos.section = '.$sectionid.' AND com.module = '.$moduleid.' AND com.instance = '.$webexid.'';
                            $cmid = (int)$DB->get_field_sql($cmidsql);
                            course_delete_module($cmid);
                           //$this->deleteteamsmeeting($teamsid);
                            $this->meetingid = 0;
                        } elseif((int)$offering->meetingtype == 3) {

                          $teamsid = (int)$offering->meetingid;
                           $mod_name = 'teamsmeeting';
                           $moduleid = $DB->get_field_sql("SELECT id FROM {modules} WHERE name = '$mod_name'");
                            $cmidsql = 'SELECT com.id FROM {course_modules} as com JOIN {course_sections} as cos ON com.section = cos.id AND com.course = cos.course WHERE  cos.course = '.$this->courseid.' AND cos.section = '.$sectionid.' AND com.module = '.$moduleid.' AND com.instance = '.$teamsid.'';
                            $cmid = (int)$DB->get_field_sql($cmidsql);
                            course_delete_module($cmid);
                          //$this->deleteteamsmeeting($teamsid);
                          $this->meetingid = 0;

                        } else {

                           $zoomid = (int)$offering->meetingid;
                           $mod_name = 'zoom';
                           $moduleid = $DB->get_field_sql("SELECT id FROM {modules} WHERE name = '$mod_name'");
                            $cmidsql = 'SELECT com.id FROM {course_modules} as com JOIN {course_sections} as cos ON com.section = cos.id AND com.course = cos.course WHERE  cos.course = '.$this->courseid.' AND cos.section = '.$sectionid.' AND com.module = '.$moduleid.' AND com.instance = '.$zoomid.'';
                            $cmid = (int)$DB->get_field_sql($cmidsql);
                            course_delete_module($cmid);
                          //$this->deletezoom($zoomid);
                          $this->meetingid = 0;

                        }  
                    } 
                } 

            }  

        }

        if ($data->trainingmethod == 'online') {

            $currentdate = time();

            if ($meetingtype == 1){

                if($data->id > 0) {

                    $sql = 'SELECT com.id AS cmid FROM {course_modules} as com JOIN {course_sections} as cos ON com.section = com.id AND com.course cos.course WHERE  cos.course = '.$this->courseid.' AND cos.section = '.$sectionid.'';
                
                    $offering = $DB->get_record('tp_offerings',array('id'=> $data->id));

                    if((int)$offering->meetingid != 0){

                        if((int)$offering->meetingtype == 2) {

                            $webexid = (int)$offering->meetingid;
                            $mod_name = 'webexactivity';
                            $moduleid = $DB->get_field_sql("SELECT id FROM {modules} WHERE name = '$mod_name'");
                            $cmidsql = 'SELECT com.id FROM {course_modules} as com JOIN {course_sections} as cos ON com.section = cos.id AND com.course = cos.course WHERE  cos.course = '.$this->courseid.' AND cos.section = '.$sectionid.' AND com.module = '.$moduleid.' AND com.instance = '.$webexid.'';
                            $cmid = (int)$DB->get_field_sql($cmidsql);
                            course_delete_module($cmid);
                            //$this->deletewebex($webexid);
                            if(($data->enddate + ($data->endtime['hours'] * 3600) + ($data->endtime['minutes'] * 60)) > $currentdate) {
                               $this->createzoom($courseid,$data, $program);
                            }

                        } elseif((int)$offering->meetingtype == 3) {

                            $teamsid = (int)$offering->meetingid;
                            $mod_name = 'teamsmeeting';
                            $moduleid = $DB->get_field_sql("SELECT id FROM {modules} WHERE name = '$mod_name'");
                            $cmidsql = 'SELECT com.id FROM {course_modules} as com JOIN {course_sections} as cos ON com.section = cos.id AND com.course = cos.course WHERE  cos.course = '.$this->courseid.' AND cos.section = '.$sectionid.' AND com.module = '.$moduleid.' AND com.instance = '.$teamsid.'';
                            $cmid = (int)$DB->get_field_sql($cmidsql);
                            course_delete_module($cmid);
                            ////$this->deleteteamsmeeting($teamsid);
                            if(($data->enddate + ($data->endtime['hours'] * 3600) + ($data->endtime['minutes'] * 60)) > $currentdate) {
                               $this->createzoom($courseid,$data, $program);
                            }

                        } else{
                           
                           $zoommeetingid = (int)$offering->meetingid;
                           if($DB->record_exists('zoom',array('id'=>$zoommeetingid))){
                                if(($data->enddate + ($data->endtime['hours'] * 3600) + ($data->endtime['minutes'] * 60)) > $currentdate) {
                                    $this->updatezoom($courseid,$data,$program,$zoommeetingid);
                                } else {
                                    $zoommeetingid = (int)$offering->meetingid;
                                    $mod_name = 'zoom';
                                    $moduleid = $DB->get_field_sql("SELECT id FROM {modules} WHERE name = '$mod_name'");
                                    $cmidsql = 'SELECT com.id FROM {course_modules} as com JOIN {course_sections} as cos ON com.section = cos.id AND com.course = cos.course WHERE  cos.course = '.$this->courseid.' AND cos.section = '.$sectionid.' AND com.module = '.$moduleid.' AND com.instance = '.$zoommeetingid.'';
                                    $cmid = (int)$DB->get_field_sql($cmidsql);
                                    course_delete_module($cmid);
                                }

                            } else {

                                if(($data->enddate + ($data->endtime['hours'] * 3600) + ($data->endtime['minutes'] * 60)) > $currentdate) {
                                   $this->createzoom($courseid,$data, $program);
                                }

                            }

                        }

                    } else {
                       if(($data->enddate + ($data->endtime['hours'] * 3600) + ($data->endtime['minutes'] * 60)) > $currentdate) {
                            $this->createzoom($courseid,$data, $program);
                        }
                    }

                } else {

                    if(($data->enddate + ($data->endtime['hours'] * 3600) + ($data->endtime['minutes'] * 60)) > $currentdate) {
                        $this->createzoom($courseid,$data, $program);
                    }
                }
               
            }

            if ($meetingtype == 3){
                

                if($data->id > 0) {


                    $offering = $DB->get_record('tp_offerings',array('id'=> $data->id));

                    if((int)$offering->meetingid != 0){

                        if((int)$offering->meetingtype == 1) {

                            $zoommeetingid = (int)$offering->meetingid;
                            $mod_name = 'zoom';
                            $moduleid = $DB->get_field_sql("SELECT id FROM {modules} WHERE name = '$mod_name'");
                            $cmidsql = 'SELECT com.id FROM {course_modules} as com JOIN {course_sections} as cos ON com.section = cos.id AND com.course = cos.course WHERE  cos.course = '.$this->courseid.' AND cos.section = '.$sectionid.' AND com.module = '.$moduleid.' AND com.instance = '.$zoommeetingid.'';
                            $cmid = (int)$DB->get_field_sql($cmidsql);
                            course_delete_module($cmid);
                            //$this->deletezoom($zoommeetingid);
                            if(($data->enddate + ($data->endtime['hours'] * 3600) + ($data->endtime['minutes'] * 60)) > $currentdate) {
                               $this->createteamsmeeting($courseid,$data, $program);
                            }

                        } elseif((int)$offering->meetingtype == 2) {

                            $webexid = (int)$offering->meetingid;
                            $mod_name = 'webexactivity';
                            $moduleid = $DB->get_field_sql("SELECT id FROM {modules} WHERE name = '$mod_name'");
                            $cmidsql = 'SELECT com.id FROM {course_modules} as com JOIN {course_sections} as cos ON com.section = cos.id AND com.course = cos.course WHERE  cos.course = '.$this->courseid.' AND cos.section = '.$sectionid.' AND com.module = '.$moduleid.' AND com.instance = '.$webexid.'';
                            $cmid = (int)$DB->get_field_sql($cmidsql);
                            course_delete_module($cmid);
                            //$this->deletewebex($webexid);
                            if(($data->enddate + ($data->endtime['hours'] * 3600) + ($data->endtime['minutes'] * 60)) > $currentdate) {
                               $this->createteamsmeeting($courseid,$data, $program);
                            }

                        } else{

                            $teamsid = (int)$offering->meetingid;

                            if($DB->record_exists('teamsmeeting',array('id'=>$teamsid))){

                                if(($data->enddate + ($data->endtime['hours'] * 3600) + ($data->endtime['minutes'] * 60)) > $currentdate) {
                                   $this->updateteamsmeeting($courseid,$data,$program,$teamsid);
                                } else {

                                    $teamsid = (int)$offering->meetingid;
                                    $mod_name = 'teamsmeeting';
                                    $moduleid = $DB->get_field_sql("SELECT id FROM {modules} WHERE name = '$mod_name'");
                                    $cmidsql = 'SELECT com.id FROM {course_modules} as com JOIN {course_sections} as cos ON com.section = cos.id AND com.course = cos.course WHERE  cos.course = '.$this->courseid.' AND cos.section = '.$sectionid.' AND com.module = '.$moduleid.' AND com.instance = '.$teamsid.'';
                                    $cmid = (int)$DB->get_field_sql($cmidsql);
                                    course_delete_module($cmid);

                                }

                            } else {

                                if(($data->enddate + ($data->endtime['hours'] * 3600) + ($data->endtime['minutes'] * 60)) > $currentdate) {
                                   $this->createteamsmeeting($courseid,$data, $program);
                                }

                            }

                        }

                    } else {

                       if(($data->enddate + ($data->endtime['hours'] * 3600) + ($data->endtime['minutes'] * 60)) > $currentdate) {
                             $this->createteamsmeeting($courseid,$data, $program);
                        }
                    }

                } else {

                    if(($data->enddate + ($data->endtime['hours'] * 3600) + ($data->endtime['minutes'] * 60)) > $currentdate) {
                        $this->createteamsmeeting($courseid,$data, $program);
                    }
                }

            }

            if ($meetingtype == 2){

                if($data->id > 0) {

                    $offering = $DB->get_record('tp_offerings',array('id'=> $data->id));


                    if((int)$offering->meetingid != 0){

                        if((int)$offering->meetingtype == 1) { 


                            $zoommeetingid = (int)$offering->meetingid;
                            $mod_name = 'zoom';
                            $moduleid = $DB->get_field_sql("SELECT id FROM {modules} WHERE name = '$mod_name'");
                            $cmidsql = 'SELECT com.id FROM {course_modules} as com JOIN {course_sections} as cos ON com.section = cos.id AND com.course = cos.course WHERE  cos.course = '.$this->courseid.' AND cos.section = '.$sectionid.' AND com.module = '.$moduleid.' AND com.instance = '.$zoommeetingid.'';
                            $cmid = (int)$DB->get_field_sql($cmidsql);
                            course_delete_module($cmid);
                            $this->deletezoom($zoommeetingid);
                            if(($data->enddate + ($data->endtime['hours'] * 3600) + ($data->endtime['minutes'] * 60)) > $currentdate) {
                               $this->createwebex($courseid,$data, $program);
                            }

                        } elseif((int)$offering->meetingtype == 3) {
                            $teamsid = (int)$offering->meetingid;
                            $mod_name = 'teamsmeeting';
                            $moduleid = $DB->get_field_sql("SELECT id FROM {modules} WHERE name = '$mod_name'");
                            $cmidsql = 'SELECT com.id FROM {course_modules} as com JOIN {course_sections} as cos ON com.section = cos.id AND com.course = cos.course WHERE  cos.course = '.$this->courseid.' AND cos.section = '.$sectionid.' AND com.module = '.$moduleid.' AND com.instance = '.$teamsid.'';
                            $cmid = (int)$DB->get_field_sql($cmidsql);
                            course_delete_module($cmid);
                            //$this->deleteteamsmeeting($teamsid);
                            if(($data->enddate + ($data->endtime['hours'] * 3600) + ($data->endtime['minutes'] * 60)) > $currentdate) {
                               $this->createwebex($courseid,$data, $program);
                            }

                        } else{
                           
                          $webexid = (int)$offering->meetingid;

        
                           if($DB->record_exists('webexactivity',array('id'=>$webexid))){

                    
                                if(($data->enddate + ($data->endtime['hours'] * 3600) + ($data->endtime['minutes'] * 60)) > $currentdate) {
                                   $this->updatewebex($courseid,$data,$program,$webexid);
                                } else {

                                    $webexid = (int)$offering->meetingid;
                                    $mod_name = 'webexactivity';
                                    $moduleid = $DB->get_field_sql("SELECT id FROM {modules} WHERE name = '$mod_name'");
                                    $cmidsql = 'SELECT com.id FROM {course_modules} as com JOIN {course_sections} as cos ON com.section = cos.id AND com.course = cos.course WHERE  cos.course = '.$this->courseid.' AND cos.section = '.$sectionid.' AND com.module = '.$moduleid.' AND com.instance = '.$webexid.'';
                                    $cmid = (int)$DB->get_field_sql($cmidsql);
                                    course_delete_module($cmid);

                                }

                            } else {


                                if(($data->enddate + ($data->endtime['hours'] * 3600) + ($data->endtime['minutes'] * 60)) > $currentdate) {
                                   $this->createwebex($courseid,$data, $program);
                                }

                            }

                        }

                    } else {
                       if(($data->enddate + ($data->endtime['hours'] * 3600) + ($data->endtime['minutes'] * 60)) > $currentdate) {
                            $this->createwebex($courseid,$data, $program);
                        }
                    }

                } else {

                    if(($data->enddate + ($data->endtime['hours'] * 3600) + ($data->endtime['minutes'] * 60)) > $currentdate) {
                        $this->createwebex($courseid,$data, $program);
                    }
                }

            }

        }
    }   
         
    // private function creategroup() {

    //     $groupdata = new stdClass;
    //     $groupdata->name= $this->offeringcode;
    //     $groupdata->idnumber= $this->offeringcode;
    //     $groupdata->courseid= $this->courseid;
    //     $this->groupid = groups_create_group($groupdata);
    //     $this->json = \core_availability\tree::get_root_json(array(
    //             \availability_group\condition::get_json($this->groupid)), \core_availability\tree::OP_AND, false);
        
    // }

    private function createsection_seperategroup(){
        $section = course_create_section($this->courseid);
        $this->sectionid = $section->section;

        // $this->json = \core_availability\tree::get_root_json(array(
        //         \availability_group\condition::get_json($this->groupid)), \core_availability\tree::OP_AND, false);

        $data = new stdClass;
        $data->availability = json_encode($this->json);

        course_update_section($this->courseid, $section, $data);
    }

      

    public function createquiz($type, $data){

        global $DB;


        $quiz = new stdClass();
        $quiz->name=$type;
        $quiz->modulename = 'quiz';
        if($data->trainingmethod !='elearning') {
            $quiz->timeopen=$data->startdate + ($data->starttime['hours'] * 3600) + ($data->starttime['minutes'] * 60);
            if($data->enddate) {
                $quiz->timeclose= $data->enddate + ($data->endtime['hours'] * 3600) + ($data->endtime['minutes'] * 60);
            }
            $quiz->timelimit=$data->duration;
        }
        $quiz->grade=10;
        $quiz->course = $this->courseid;
        $quiz->gradecat = $DB->get_field('course_categories', 'id', ['idnumber' => 'trainingprogram']);
        $quiz->section = $this->sectionid;
        $quiz->visible = 1;
        $quiz->quizpassword=0;
        $quiz->completion = 2;
        $quiz->completiongradeitemnumber=0;
        $quiz->cmidnumber = '';
        $quiz->preferredbehaviour='deferredfeedback';
        $quiz->attempts=0;
        //************ */
        $quiz->introeditor = ['text' => '', 'format' => FORMAT_HTML, 'itemid' => null];
        $quiz->hidden = 0;
        $quiz->overduehandling = 'autosubmit';
        $quiz->attemptimmediately = 1;
        $quiz->correctnessimmediately = 1;
        $quiz->marksimmediately = 1;
        $quiz->specificfeedbackimmediately = 1;
        $quiz->generalfeedbackimmediately = 1;
        $quiz->rightanswerimmediately = 1;
        $quiz->overallfeedbackimmediately = 1;
        $quiz->attemptopen = 1;
        $quiz->correctnessopen = 1;
        $quiz->marksopen = 1;
        $quiz->specificfeedbackopen = 1;
        $quiz->generalfeedbackopen = 1;
        $quiz->rightansweropen = 1;
        $quiz->overallfeedbackopen = 1;
        $quiz->questionsperpage = 1;
        $quiz->shuffleanswers = 1;
        //***************/
        $quiz->groupmode=1;
        $quiz->availability = json_encode($this->json);
        $quiz->introeditor = ['text' => '', 'format' => FORMAT_HTML, 'itemid' => null];
        $moduleinfo = create_module($quiz);
        return $moduleinfo->id;
    }

    public function createattendance($enddate = false){
        global $DB;
   
        $attendance = new stdClass();
        // $att = mod_attendance_external::add_attendance($this->courseid, 'att8', '', 0);
        // $attendance->id = $att['attendanceid'];
        $attendance->name = '{mlang en}Attendance{mlang}{mlang ar}حضور{mlang}';
        $attendance->course = $this->courseid;
        $attendance->gradecat = $DB->get_field('course_categories', 'id', ['idnumber' => 'trainingprogram']);
        $attendance->grade = 20;
        $attendance->gradepass = 20;
        $attendance->modulename = 'attendance';
        $attendance->section = $this->sectionid;
        $attendance->visible=1;
        $attendance->introeditor = ['text' => '', 'format' => FORMAT_HTML, 'itemid' => null];
        $attendance->completion =2;
        $attendance->completionusegrade=1;
        $attendance->completionpassgrade=1;
        $attendance->groupmode=1;
        $attendance->completionexpected= $enddate ? $enddate : 0;

        $attendance->availability = json_encode($this->json);

        $moduleinfo =  create_module($attendance);
        $this->attendanceid = $moduleinfo->id;

    }

    public function createzoom($courseid,$data,$program) {
        global $DB, $USER;
         
        $startdate = $data->startdate;
        $enddate = $data->enddate;

        $course = $DB->get_record('course',array('id' =>$courseid));
        $zoom = new stdClass();
        $start_time = $data->startdate + ($data->starttime['hours'] * 3600) + ($data->starttime['minutes'] * 60);
        $zoom->modulename = 'zoom';
        $zoom->host_id = zoom_get_user_id();
        $zoom->course = (int) $courseid;
        $zoom->showdescription = 0;
        $zoom->name =  '{mlang en}'.$program->name.'{mlang}{mlang ar}'.$program->namearabic.'{mlang}';
        $zoom->intro = $program->description;
        $zoom->introformat = 1;
        $zoom->type = 1;
        $zoom->start_time = $start_time;
        $zoom->introeditor = ['text' => '', 'format' => FORMAT_HTML, 'itemid' => null];
        $zoom->timezone = date_default_timezone_get();
        $zoom->section = $this->sectionid;
        if($enddate > $startdate) {
            $zoom->recurring = 1;
            $zoom->recurrence_type = 1;
            $zoom->repeat_interval = 1;
        }
        $zoom->duration =  $data->duration;
        $zoom->visible = 1;
        $zoom->grade = 0;
        $zoom->availability = json_encode($this->json);
        $zoom->option_jbh = 0;
        $zoom->option_waiting_room = 1;
        $zoom->end_times = 1;
        $zoom->end_date_time = $data->enddate + ($data->endtime['hours'] * 3600) + ($data->endtime['minutes'] * 60);
        $zoom->end_date_option = 1;
        $zoom->option_mute_upon_entry = 1;
        $zoom->option_waiting_room = 1;
        $zoom->requirepasscode = 1;
        $zoom->registration = 2;
        $zoom->completion = 2;
        $zoom->completionview = 1;
        $zoom->monthly_repeat_option = null;
        $moduleinfo =  create_module($zoom);

        $zoomrecord = $DB->get_record('zoom', array('id' => $moduleinfo->instance), '*', MUST_EXIST);
        $this->meetingid = $moduleinfo->instance;

    }


    public function updatezoom($courseid,$data,$program,$zoommeetingid){
        global $DB, $USER;

        $offering = $DB->get_record('tp_offerings',array('id'=> $data->id));

        $startdate = $data->startdate;
        $enddate = $data->enddate;

        $zoom = $DB->get_record('zoom', ['id' => $zoommeetingid]);
        $start_time = $data->startdate + ($data->starttime['hours'] * 3600) + ($data->starttime['minutes'] * 60);
        $zoom->course = $courseid;
        $zoom->instance = $zoommeetingid;
        $zoom->name = '{mlang en}'.$program->name.'{mlang}{mlang ar}'.$program->namearabic.'{mlang}';
        $zoom->intro = $program->description;
        $zoom->introformat = 1;
        $zoom->type = 1;
        $zoom->start_time = $start_time;
        $zoom->introeditor = ['text' => '', 'format' => FORMAT_HTML, 'itemid' => null];
        $zoom->timezone = date_default_timezone_get();
        if($enddate > $startdate) {
            $zoom->recurring = 1;
            $zoom->recurrence_type = 1;
            $zoom->repeat_interval = 1;
        }
        $zoom->duration =  $data->duration;
        $zoom->visible = 1;
        $zoom->grade = 0;
        $zoom->availability = json_encode($this->json);
        $zoom->option_jbh = 0;
        $zoom->option_waiting_room = 1;
        $zoom->end_times = 1;
        $zoom->end_date_time = $data->enddate + ($data->endtime['hours'] * 3600) + ($data->endtime['minutes'] * 60);
        $zoom->end_date_option = 1;
        $zoom->option_mute_upon_entry = 1;
        $zoom->option_waiting_room = 1;
        $zoom->requirepasscode = 1;
        $zoom->registration = 2;
        $zoom->completion = 2;
        $zoom->completionview = 1;
        $zoom->monthly_repeat_option = null;
        zoom_update_instance($zoom);
        $this->meetingid = $zoommeetingid;

    }

    public function deletezoom($zoommeetingid){

       global $DB;
       zoom_delete_instance($zoommeetingid);
       $this->meetingid = 0;

    }

    public function createteamsmeeting($courseid,$data,$program) {
        global $DB;

        $team = new stdClass();
        $team->modulename = 'teamsmeeting';
        $team->course =(int) $courseid;
        $team->name = '{mlang en}'.$program->name.'{mlang}{mlang ar}'.$program->namearabic.'{mlang}';
       // $team->subject = $program->name;
        $team->start_time = $data->startdate + ($data->starttime['hours'] * 3600) + ($data->starttime['minutes'] * 60);
        $team->end_time = $data->enddate + ($data->endtime['hours'] * 3600) + ($data->endtime['minutes'] * 60);
        $team->duration = $data->duration;
        $team->occurs_until = $data->enddate + ($data->endtime['hours'] * 3600) + ($data->endtime['minutes'] * 60);
        $team->timecreated = time();
        $team->section = $this->sectionid;
        $team->visible = 1;
        $team->isrecuring = 1;
        $team->introeditor = ['text' => '', 'format' => FORMAT_HTML, 'itemid' => null];
        $team->intro = $program->description;
        $team->introformat = 1;
        $team->completion = 2;
        $team->completionview = 1;
        $moduleinfo =  create_module($team);
        $this->meetingid = $moduleinfo->instance;
    }

    public function updateteamsmeeting($courseid,$data,$program,$teamsid){
        global $DB;

        $team = $DB->get_record('teamsmeeting', ['id' => $teamsid]);
        $team->instance = $teamsid;
        $team->course =(int) $courseid;
        $team->name = '{mlang en}'.$program->name.'{mlang}{mlang ar}'.$program->namearabic.'{mlang}';
        //$team->subject = $program->name;
        $team->start_time = $data->startdate + ($data->starttime['hours'] * 3600) + ($data->starttime['minutes'] * 60);
        $team->end_time = $data->enddate + ($data->endtime['hours'] * 3600) + ($data->endtime['minutes'] * 60);
        $team->occurs_until = $data->enddate + ($data->endtime['hours'] * 3600) + ($data->endtime['minutes'] * 60);
        $team->duration = $data->duration;
        $team->timecreated = time();
        $team->section = $this->sectionid;
        $team->visible = 1;
        $team->isrecuring = 1;
        $team->introeditor = ['text' => '', 'format' => FORMAT_HTML, 'itemid' => null];
        $team->intro = $program->description;
        $team->introformat = 1;
        $team->completion = 2;
        $team->completionview = 1;
        teamsmeeting_update_instance($team);
        $this->meetingid = $teamsid;

    }

    public function deleteteamsmeeting($teamsid){
        global $DB;
        teamsmeeting_delete_instance($teamsid);
        $this->meetingid = 0;
    }


    public function createwebex($courseid,$data,$program) {
        global $DB, $USER;

        $course = $DB->get_record('course',array('id' =>$courseid));
      
        $webex = new stdClass();
        $webex->modulename = 'webexactivity';
        $webex->course = (int) $courseid;
        // $webex->name =  '{mlang en}'.$program->name.'{mlang}{mlang ar}'.$program->namearabic.'{mlang}';
        $webex->name =  $program->name;
        $webex->intro = $program->description;
        $webex->introformat = 1;
        $webex->type = 1;
        $webex->starttime = $data->startdate + ($data->starttime['hours'] * 3600) + ($data->starttime['minutes'] * 60);
        $webex->endtime = $data->enddate + ($data->endtime['hours'] * 3600) + ($data->endtime['minutes'] * 60);
        $webex->introeditor = ['text' => '', 'format' => FORMAT_HTML, 'itemid' => null];
        $webex->section = $this->sectionid;
        $webex->duration =  $data->duration/60;
        $webex->calpublish = 0;
        $webex->visible = 1;
        $webex->allchat = 1;
        $webex->completion = 2;
        $webex->completionview = 1;
        $webex->studentdownload = 1;
        $webex->laststatuscheck  = 1;
        $webex->longavailability = 1;
        $webex->availability = json_encode($this->json);

        $moduleinfo =  create_module($webex);

        $webexrecord = $DB->get_record('webexactivity', array('id' => $moduleinfo->instance), '*', MUST_EXIST);
        $this->meetingid = $moduleinfo->instance;
        // $webexrecord->name = $program->name;
        $webexmeeting = \mod_webexactivity\meeting::load($webexrecord);
        
        $webexrecord->coursemodule = $moduleinfo->coursemodule;
        $webexrecord->meetinglink = $webexmeeting->get_moodle_join_url($USER, null);
        $webexrecord->starttime = $data->startdate + ($data->starttime['hours'] * 3600) + ($data->starttime['minutes'] * 60);
        $webexid = webexactivity_update_instance($webexrecord);

        $webexmeeting->get_external_join_url();
    }


    public function updatewebex($courseid,$data,$program,$webexid){
        global $DB, $USER;

        $course_id = (int) $courseid;
        $webexid = (int) $webexid;
        $offering = $DB->get_record('tp_offerings',array('id'=> $data->id));
        $sectionid = (int) $DB->get_field_sql('SELECT id FROM {course_sections} WHERE course = '.$course_id.' AND section = '.$offering->sections.'');
        $webexmoduleid = (int) $DB->get_field_sql("SELECT id FROM {modules} WHERE name = 'webexactivity'");
        $coursemoduleid= (int)$DB->get_field_sql('SELECT id FROM {course_modules} WHERE course = '.$course_id.' AND module = '.$webexmoduleid.' AND instance = '.$webexid.' AND section = '.$sectionid.'');
        $webex = $DB->get_record('webexactivity', array('id' => $webexid), '*', MUST_EXIST);
        $webex->course = $course_id;
        $webex->name =  '{mlang en}'.$program->name.'{mlang}{mlang ar}'.$program->namearabic.'{mlang}';
        $webex->intro = $program->description;
        $webex->introformat = 1;
        $webex->type = 1;
        $webex->longavailability = 1;
        $webex->starttime = $data->startdate + ($data->starttime['hours'] * 3600) + ($data->starttime['minutes'] * 60);
        $webex->endtime = $data->enddate + ($data->endtime['hours'] * 3600) + ($data->endtime['minutes'] * 60);
        $webex->introeditor = ['text' => '', 'format' => FORMAT_HTML, 'itemid' => null];
        $webex->duration =  $data->duration/60;
        $webex->calpublish = 0;
        $webex->visible = 1;
        $webex->allchat = 1;
        $webex->completion = 2;
        $webex->completionview = 1;
        $webex->studentdownload = 1;
        $webex->laststatuscheck  = 1;
        $webex->coursemodule = $coursemoduleid;
        webexactivity_update_instance($webex);
        $this->meetingid = $webexid;

    }

    public function deletewebex($webexid){
       global $DB;
       $id = (int) $webexid;

       webexactivity_delete_instance($id);
       $this->meetingid = 0;

    }

    public function create_course_module($course,$modulename,$sectionid) {
        global $CFG;

        require_once($CFG->dirroot.'/course/modlib.php');
        list($module, $context, $cw, $cm, $data) = prepare_new_moduleinfo_data($course, $modulename, $sectionid);

        return add_course_module($data);
   
    }

    public function updatequiz($instanceid, $data){
      
        global $DB;
        $quiz = new stdClass();
        $quiz->instance = (int)$instanceid;
        $quiz->modulename = 'quiz';
        if($data->trainingmethod !='elearning') {
            $quiz->timeopen=$data->startdate + ($data->starttime['hours'] * 3600) + ($data->starttime['minutes'] * 60);
            if($data->enddate) {
                $quiz->timeclose= $data->enddate + ($data->endtime['hours'] * 3600) + ($data->endtime['minutes'] * 60);
            }
            $quiz->timelimit=$data->duration;
        } else {

            $quiz->timeopen = 0;
            $quiz->timeclose = 0;
            $quiz->timelimit = 0;
        }
        $quiz->grade=10;
        $quiz->intro = '';
        $quiz->visible = 1;
        $quiz->course = $this->courseid;
        $quiz->coursemodule=$data->cmid;
        $quiz->introeditor = ['text' => '', 'format' => FORMAT_HTML, 'itemid' => null];
        $quiz->completionpassgrade = 1;
        $quiz->quizpassword=$data->password | '';
        $moduleinfo = update_module($quiz);
        return $moduleinfo->id;
    }

}
