<?php
namespace local_trainingprogram\local;

require_once($CFG->dirroot.'/mod/msteams/lib.php');
require_once($CFG->dirroot . '/mod/zoom/lib.php');
require_once($CFG->dirroot . '/mod/webexactivity/lib.php');
require_once($CFG->dirroot.'/mod/zoom/locallib.php');
require_once($CFG->dirroot.'/group/lib.php');
require_once($CFG->dirroot.'/mod/quiz/lib.php');
require_once($CFG->dirroot.'/mod/attendance/lib.php');
require_once($CFG->dirroot.'/course/dnduploadlib.php');

use \core_availability\tree;
use \availability_group\condition;
use mod_attendance_external;
use stdClass;
use \local_trainingprogram\local\dataprovider as dataprovider;

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
               
               $this->sectionid = $sectionid;
            } else {

                $this->createsection_seperategroup();
            }
           
        } else {

            $this->createsection_seperategroup();
        }
        if ($data->trainingmethod == 'offline'){
            if($data->id > 0) {
                $offering = $DB->get_record('tp_offerings',array('id'=> $data->id));

                if($offering->trainingmethod == 'online') {
                    if((int)$offering->meetingid != 0){
                        if((int)$offering->meetingtype == 2) {
                            $webexid = (int)$offering->meetingid;
                            $this->deletewebex($webexid);
                            $this->meetingid = 0;
                        } elseif((int)$offering->meetingtype == 3) {

                          $teamsid = (int)$offering->meetingid;
                          $this->deletemsteams($teamsid);
                          $this->meetingid = 0;

                        } else {

                          $zoomid = (int)$offering->meetingid;
                          $this->deletezoom($zoomid);
                          $this->meetingid = 0;

                        }  
                    } 
                }        
            }    
        }

        if ($data->trainingmethod == 'online') {

            if($data->id > 0) {

                $offering = $DB->get_record('tp_offerings',array('id'=> $data->id));
                
                if($offering->trainingmethod == 'offline') {

                    if(!is_null($offering->prequiz) && $offering->prequiz != 0) {

                        quiz_delete_instance($offering->prequiz);
                    }
                    if(!is_null($offering->postquiz) && $offering->postquiz != 0) {

                        quiz_delete_instance($offering->postquiz);
                    }
                    
                    $sectionid = $DB->get_field_sql('SELECT sections FROM {tp_offerings} WHERE id = '.$data->id .'');
                    $mod_name = 'attendance';
                    $attendancemoduleid = $DB->get_field_sql("SELECT id FROM {modules} WHERE name = '$mod_name'");
                    $sql = 'SELECT com.instance FROM {course_modules} as com JOIN {course_sections} as cos ON com.section = cos.id AND com.course = cos.course WHERE  cos.course = '.$this->courseid.' AND cos.section = '.$sectionid.' AND com.module = '.$attendancemoduleid.'';
                    $attendanceid = (int)$DB->get_field_sql($sql);

                    if($attendanceid) {

                        attendance_delete_instance($attendanceid);
                    }


                }
                
            }    

            if ($meetingtype == 1){

                if($data->id > 0) {

                    $sql = 'SELECT com.id AS cmid FROM {course_modules} as com JOIN {course_sections} as cos ON com.section = com.id AND com.course cos.course WHERE  cos.course = '.$this->courseid.' AND cos.section = '.$sectionid.'';
                
                    $offering = $DB->get_record('tp_offerings',array('id'=> $data->id));

                    if((int)$offering->meetingid != 0){

                        if((int)$offering->meetingtype == 2) {

                            $webexid = (int)$offering->meetingid;
                            $this->deletewebex($webexid);
                            $this->createzoom($courseid,$data, $program);

                        } elseif((int)$offering->meetingtype == 3) {

                            $teamsid = (int)$offering->meetingid;
                            $this->deletemsteams($teamsid);
                            $this->createzoom($courseid,$data, $program);

                        } else{
                           
                           $zoommeetingid = (int)$offering->meetingid;
                           if($DB->record_exists('zoom',array('id'=>$zoommeetingid))){

                              $this->updatezoom($courseid,$data,$program,$zoommeetingid);

                            } else {

                                $this->createzoom($courseid,$data, $program);

                            }

                        }

                    } else {
                       $this->createzoom($courseid,$data, $program);
                    }

                } else {

                    $this->createzoom($courseid,$data, $program);
                }
               
            }

            if ($meetingtype == 3){
                

                if($data->id > 0) {


                    $offering = $DB->get_record('tp_offerings',array('id'=> $data->id));

                    if((int)$offering->meetingid != 0){

                        if((int)$offering->meetingtype == 1) {

                            $zoommeetingid = (int)$offering->meetingid;
                            $this->deletezoom($zoommeetingid);
                            $this->createmsteams($courseid,$data, $program);

                        } elseif((int)$offering->meetingtype == 2) {

                            $webexid = (int)$offering->meetingid;
                            $this->deletewebex($webexid);
                            $this->createmsteams($courseid,$data, $program);

                        } else{

                            $teamsid = (int)$offering->meetingid;

                            if($DB->record_exists('msteams',array('id'=>$teamsid))){

                                $this->updatemsteams($courseid,$data,$program,$teamsid);

                            } else {

                                $this->createmsteams($courseid,$data, $program);

                            }

                        }

                    } else {

                       $this->createmsteams($courseid,$data, $program);
                    }

                } else {

                    $this->createmsteams($courseid,$data, $program);
                }

            }

            if ($meetingtype == 2){

                if($data->id > 0) {

                    $offering = $DB->get_record('tp_offerings',array('id'=> $data->id));


                    if((int)$offering->meetingid != 0){

                        if((int)$offering->meetingtype == 1) { 


                            $zoommeetingid = (int)$offering->meetingid;
                            $this->deletezoom($zoommeetingid);
                            $this->createwebex($courseid,$data, $program);

                        } elseif((int)$offering->meetingtype == 3) {
                            $teamsid = (int)$offering->meetingid;
                            $this->deletemsteams($teamsid);
                            $this->createwebex($courseid,$data, $program);

                        } else{
                           
                          $webexid = (int)$offering->meetingid;

        
                           if($DB->record_exists('webexactivity',array('id'=>$webexid))){

                                $this->updatewebex($courseid,$data,$program,$webexid);

                            } else {


                                $this->createwebex($courseid,$data, $program);

                            }

                        }

                    } else {
                       $this->createwebex($courseid,$data, $program);
                    }

                } else {

                    $this->createwebex($courseid,$data, $program);
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
        $time = ($data->starttime['hours'] * 3600) + ($data->starttime['minutes'] * 60);
        $quiz->timeopen=$data->startdate + $time;
        $quiz->timeclose=$data->enddate;
        $quiz->timelimit=$data->duration;
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
        $quiz->availability = json_encode($this->json);
        $quiz->introeditor = ['text' => '', 'format' => FORMAT_HTML, 'itemid' => null];
        $moduleinfo = create_module($quiz);
        return $moduleinfo->id;
    }

    public function createattendance(){
        global $DB;
   
        $attendance = new stdClass();
        // $att = mod_attendance_external::add_attendance($this->courseid, 'att8', '', 0);

        // $attendance->id = $att['attendanceid'];
        $attendance->name = '{mlang en}Attendance{mlang}{mlang ar}حضور{mlang}';
        $attendance->course = $this->courseid;
        $attendance->gradecat = $DB->get_field('course_categories', 'id', ['idnumber' => 'trainingprogram']);
        $attendance->grade = 10;
        $attendance->gradepass = 20;
        $attendance->modulename = 'attendance';
        $attendance->section = $this->sectionid;
        $attendance->visible=1;
        $attendance->introeditor = ['text' => '', 'format' => FORMAT_HTML, 'itemid' => null];
        $attendance->completion =2;
        $attendance->completionusegrade=1;
        $attendance->completionpassgrade=1;

        $attendance->availability = json_encode($this->json);

        $moduleinfo =  create_module($attendance);
        $this->attendanceid = $moduleinfo->id;

    }

    public function createzoom($courseid,$data,$program) {
        global $DB, $USER;


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
        $zoom->recurring = 1;
        $zoom->recurrence_type = 1;
        $zoom->repeat_interval = 1;
        $zoom->duration =  $data->duration;
        $zoom->visible = 1;
        $zoom->grade = 0;
        $zoom->availability = json_encode($this->json);
        $zoom->timezone = date_default_timezone_get();
        $zoom->option_jbh = 0;
        $zoom->option_waiting_room = 1;
        $zoom->end_times = 1;
        $zoom->end_date_time = $data->enddate;
        $zoom->end_date_option = 1;
        $zoom->option_mute_upon_entry = 1;
        $zoom->option_waiting_room = 1;
        $zoom->requirepasscode = 1;
        $zoom->monthly_repeat_option = null;
        $moduleinfo =  create_module($zoom);

        $zoomrecord = $DB->get_record('zoom', array('id' => $moduleinfo->instance), '*', MUST_EXIST);
        $this->meetingid = $moduleinfo->instance;

    }


    public function updatezoom($courseid,$data,$program,$zoommeetingid){
        global $DB, $USER;

        $offering = $DB->get_record('tp_offerings',array('id'=> $data->id));

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
        $zoom->recurring = 1;
        $zoom->recurrence_type = 1;
        $zoom->repeat_interval = 1;
        $zoom->duration =  $data->duration;
        $zoom->visible = 1;
        $zoom->grade = 0;
        $zoom->availability = json_encode($this->json);
        $zoom->timezone = date_default_timezone_get();
        $zoom->option_jbh = 0;
        $zoom->option_waiting_room = 1;
        $zoom->end_times = 1;
        $zoom->end_date_time = $data->enddate;
        $zoom->end_date_option = 1;
        $zoom->option_mute_upon_entry = 1;
        $zoom->option_waiting_room = 1;
        $zoom->requirepasscode = 1;
        $zoom->monthly_repeat_option = null;
        zoom_update_instance($zoom);
        $this->meetingid = $zoommeetingid;

    }

    public function deletezoom($zoommeetingid){

       global $DB;
       zoom_delete_instance($zoommeetingid);
       $this->meetingid = 0;

    }

    public function createmsteams($courseid,$data,$program) {
        global $DB;


        $team = new stdClass();
        $team->course = $courseid;
        $team->name = '{mlang en}'.$program->name.'{mlang}{mlang ar}'.$program->namearabic.'{mlang}';
        $team->intro = $program->description;
        $team->section =  $this->sectionid ;
        $teamsid = msteams_add_instance($team);
        $this->meetingid = $teamsid;
    }

    public function updatemsteams($courseid,$data,$program,$teamsid){
        global $DB;


        $team = $DB->get_record('msteams', ['id' => $teamsid]);
        $team->instance = $teamsid;
        $team->course = $courseid;
        $team->name = '{mlang en}'.$program->name.'{mlang}{mlang ar}'.$program->namearabic.'{mlang}';
        $team->intro = $program->description;
        $team->introformat = 1;
        msteams_update_instance($team);
        $this->meetingid = $teamsid;

    }

    public function deletemsteams($teamsid){
        global $DB;
        msteams_delete_instance($teamsid);
        $this->meetingid = 0;
    }


    public function createwebex($courseid,$data,$program) {
        global $DB, $USER;

        $course = $DB->get_record('course',array('id' =>$courseid));
      
        $webex = new stdClass();
        $webex->modulename = 'webexactivity';
        $webex->course = (int) $courseid;
        $webex->name =  '{mlang en}'.$program->name.'{mlang}{mlang ar}'.$program->namearabic.'{mlang}';
        $webex->intro = $program->description;
        $webex->introformat = 1;
        $webex->type = 1;
        $webex->starttime = $data->startdate + ($data->starttime['hours'] * 3600) + ($data->starttime['minutes'] * 60);
        $webex->endtime = $data->enddate + ($data->starttime['hours'] * 3600) + ($data->starttime['minutes'] * 60);
        $webex->introeditor = ['text' => '', 'format' => FORMAT_HTML, 'itemid' => null];
        $webex->section = $this->sectionid;
        $webex->duration =  $data->duration/60;
        $webex->calpublish = 0;
        $webex->visible = 1;
        $webex->allchat = 1;
        $webex->studentdownload = 1;
        $webex->laststatuscheck  = 1;
        $webex->longavailability = 1;
        $webex->availability = json_encode($this->json);

      
        $moduleinfo =  create_module($webex);

        $webexrecord = $DB->get_record('webexactivity', array('id' => $moduleinfo->instance), '*', MUST_EXIST);
        $this->meetingid = $moduleinfo->instance;
        $webexmeeting = \mod_webexactivity\meeting::load($webexrecord);
        
        $webexrecord->coursemodule = $moduleinfo->coursemodule;
        $webexrecord->meetinglink = $webexmeeting->get_moodle_join_url($USER, null);
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
        $webex->endtime = $data->enddate;
        $webex->introeditor = ['text' => '', 'format' => FORMAT_HTML, 'itemid' => null];
        $webex->duration =  $data->duration/60;
        $webex->calpublish = 0;
        $webex->visible = 1;
        $webex->allchat = 1;
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

}
