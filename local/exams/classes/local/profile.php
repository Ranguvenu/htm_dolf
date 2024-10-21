<?php
namespace local_exams\local;


require_once($CFG->dirroot.'/group/lib.php');
require_once($CFG->dirroot.'/mod/quiz/lib.php');
require_once($CFG->dirroot.'/course/dnduploadlib.php');

use \core_availability\tree;
use \availability_group\condition;
use stdClass;
use core_text;
/**
 * Exam profile class
 */
class profile
{
    private $courseid;

    private $profilecode;

    public $groupid;

    public $sectionid;

    private $groupcode;

    public $quizid;

    private $json;

    function __construct($courseid, $profilecode, $data, $exam)
    {
        global $DB;
        $this->courseid = $courseid;
        $this->profilecode = $profilecode;

       // $group = groups_get_group_by_idnumber((int)$this->courseid,$this->offeringcode);
        $group = $DB->get_record_sql("SELECT * FROM {groups} WHERE idnumber = '$this->profilecode'");

        if($group){
           $this->groupid = $group->id;
           $this->json = \core_availability\tree::get_root_json(array(
                \availability_group\condition::get_json($group->id)), \core_availability\tree::OP_AND, false);
        } else {
            $groupdata = new stdClass;
            $groupdata->name= $this->profilecode;
            $groupdata->idnumber= preg_replace('/ /', '', $this->profilecode);
            $groupdata->courseid= $this->courseid;
            $groupexist = groups_get_group_by_idnumber($groupdata->courseid, $groupdata->idnumber);
            if(!$groupexist){
                $this->groupid = groups_create_group($groupdata);
            }
            $this->json = \core_availability\tree::get_root_json(array(
                \availability_group\condition::get_json($this->groupid)), \core_availability\tree::OP_AND, false);
        }
        if($data->id > 0) {
            $sectionid = $DB->get_field_sql('SELECT sectionid FROM {local_exam_profiles} WHERE id = '.$data->id .'');
            if($sectionid){
               $this->sectionid = $sectionid;
            } else {
                $this->createsection_seperategroup();
            }

            $profile = $DB->get_record('tp_offerings',array('id'=> $data->id));
           
        } else {
            $this->createsection_seperategroup();
            $profile = null;
        }  
    }

    private function createsection_seperategroup(){
        $section = course_create_section($this->courseid);
        $this->sectionid = $section->section;
        $data = new stdClass;
        $data->availability = json_encode($this->json);

        course_update_section($this->courseid, $section, $data);
    }

    public function createquiz($data){

        global $DB;
        $quiz = new stdClass();
        $quiz->name=$data->profilecode;
        $quiz->modulename = 'quiz';
        $time = ($data->starttime['hours'] * 3600) + ($data->starttime['minutes'] * 60);
        // $quiz->timeopen=$data->startdate + $time;
        // $quiz->timeclose=$data->enddate;
        $quiz->timelimit=$data->duration;
        $quiz->gradepass=$data->passinggrade;
        $quiz->grade=100;
        $quiz->course = $this->courseid;
        // $quiz->gradecat = $DB->get_field('course_categories', 'id', ['idnumber' => 'trainingprogram']);
        $quiz->section = $this->sectionid;
        $quiz->visible = 1;
        $quiz->quizpassword=$data->password | '';
        $quiz->completion = 2;
        $quiz->completiongradeitemnumber=0;
        $quiz->cmidnumber = '';
        $quiz->completionpassgrade = 1;
        $quiz->preferredbehaviour='deferredfeedback';
        $quiz->groupmode=1;
        $quiz->availability = json_encode($this->json);
        $quiz->introeditor = ['text' => '', 'format' => FORMAT_HTML, 'itemid' => null];
        $quiz->hidden = 0;
        $quiz->overduehandling = 'autosubmit';
        $quiz->attempts = $DB->get_field('local_exams', 'noofattempts', ['id' => $data->examid]);
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

        $moduleinfo = create_module($quiz);
        // quiz_grade_item_update($quiz);
        return $moduleinfo->id;
    }

    public function updatequiz($data){
      
        global $DB;
        $quiz = new stdClass();
        $quiz->instance = $DB->get_field('local_exam_profiles','quizid',['id'=>$data->id]);
        $quiz->modulename = 'quiz';
        $quiz->name=$data->profilecode;
        $quiz->intro = '';
        $quiz->visible = 1;
        $quiz->gradepass=$data->passinggrade;
        $quiz->grade=100;
        $quiz->completionpassgrade = 1;
        $quiz->introeditor = ['text' => '', 'format' => FORMAT_HTML, 'itemid' => null];
        $quiz->coursemodule=$data->cmid;
        $quiz->timelimit=$data->duration;
        $quiz->quizpassword=$data->password | '';
        $quiz->completion = 2;
        $quiz->completiongradeitemnumber=0;
        $quiz->cmidnumber = '';
        $quiz->completionpassgrade = 1;
        $quiz->preferredbehaviour='deferredfeedback';
        $quiz->groupmode=1;
        $quiz->availability = json_encode($this->json);
        $quiz->introeditor = ['text' => '', 'format' => FORMAT_HTML, 'itemid' => null];
        $quiz->hidden = 0;
        $quiz->overduehandling = 'autosubmit';
        $quiz->attempts = $DB->get_field('local_exams', 'noofattempts', ['id' => $data->examid]);
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

        $moduleinfo = update_module($quiz);

        return $moduleinfo->id;
    }

}
