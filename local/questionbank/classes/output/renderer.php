<?php
namespace local_questionbank\output;

defined('MOODLE_INTERNAL') || die;

use plugin_renderer_base;
use context_system;

class renderer extends plugin_renderer_base {
    public function render_questionbank($page)
    {
        $data = $page->export_for_template($this);                                                                                  
        return parent::render_from_template('local_trainingprogram/mainpage', $data);         
    }
    public function get_questionsinfo($filter = false) {
        $systemcontext = context_system::instance();
        $options = array('targetID' => 'questionbank_container','perPage' => 10, 'cardClass' => 'col-md-6 col-12', 'viewType' => 'card');
        $options['methodName']='local_questionbank_view';
        $options['templateName']='local_questionbank/questionbankinfo';
        $options = json_encode($options);
        $filterdata = json_encode(array());
        $dataoptions = json_encode(array('contextid' => $systemcontext->id));
        $context = [
            'targetID' => 'questionbank_container',
            'options' => $options,
            'dataoptions' => $dataoptions,
            'filterdata' => $filterdata,
        ];
        if($filter){
            return  $context;
        }else{
            return  $this->render_from_template('theme_academy/cardPaginate', $context);
        }
    }

    public function global_filter($filterparams) {
        global $DB, $PAGE, $OUTPUT;
        return $this->render_from_template('theme_academy/global_filter', $filterparams);

    }

    public function coursetopic_view($data) {
        global $DB, $PAGE, $OUTPUT, $CFG;
        $course=$data->course;
        list($coursesql,$courseparams) = $DB->get_in_or_equal(explode(',',$course));
        $querysql = "SELECT c.id AS courseid,fullname as coursename FROM {course} as c
                     JOIN {local_qb_coursetopics} AS ct ON  ct.courseid=c.id AND ct.questionbankid=$data->id WHERE c.id $coursesql";
        $courses= $DB->get_records_sql($querysql,$courseparams);
        foreach ($courses AS $course) {
            $topicssql = "SELECT cs.id AS topicid,(CASE WHEN cs.name IS NULL THEN CONCAT('Topic',cs.section) ELSE cs.name END) as name ,cs.timemodified AS addeddate FROM {course_sections} as cs  
            JOIN {local_qb_coursetopics} AS ct ON  ct.topic = cs.id AND ct.courseid=$course->courseid AND ct.questionbankid=$data->id WHERE cs.course = $course->courseid AND cs.section <> 0";
            $topics_list = $DB->get_records_sql($topicssql);
            foreach ($topics_list AS $topic) {
                $topic->dateadded= userdate($topic->addeddate, get_string('strftimedatemonthabbr', 'langconfig')); 
            }
            $course->fullname=format_string($course->coursename);
            $course->topics=array_values($topics_list);
        }
        $qbinfo =  $DB->get_field('local_questionbank', 'movedtoprod', ['id' => $data->id]);
        $qbstatus = true;
        if($qbinfo == 1){
            $qbstatus = false;
        }
        $viewdata=[
        'courses'=>array_values($courses),
        'questionbankid'=>$data->id,
        'qbstatus'=>$qbstatus,
        ];
        // print_object($viewdata);die;
        $result = $this->render_from_template('local_questionbank/viewcoursetopic', $viewdata);
        return $result;
    }
    public function questiontopics_view($data) {
        global $DB, $PAGE, $OUTPUT, $CFG;
        // $course=$data->course;
        // list($coursesql,$courseparams) = $DB->get_in_or_equal(explode(',',$course));
        // $querysql = "SELECT c.id AS courseid,fullname FROM {course} as c
        //              JOIN {local_qb_coursetopics} AS ct ON  ct.courseid=c.id AND ct.questionbankid=$data->id WHERE c.id $coursesql";
        // $courses= $DB->get_records_sql($querysql,$courseparams);
        // print_r($data);

        foreach ($data AS $course) {
            $topicssql = "SELECT cs.id AS topicid,(CASE WHEN cs.name IS NULL THEN CONCAT('Topic',cs.section) ELSE cs.name END) as name ,FROM_UNIXTIME(cs.timemodified,'%D %M %Y') AS dateadded,c.id AS courseid,c.fullname FROM {course_sections} as cs 
           JOIN  {course} as c ON cs.course= c.id
            JOIN {local_qb_questioncourses} AS ct ON  ct.topic = cs.id AND ct.course=$course->course AND ct.questionbankid=$course->questionbankid WHERE cs.course = $course->course AND cs.section <> 0";
            $course->topics=array_values($DB->get_records_sql($topicssql));
        }
        // print_r($data);
        //  print_r(array_values($data));
        // exit;
        $viewdata=[
        'data'=>array_values($data),
        // 'questionbankid'=>$data->questionbankid,
        ];
        $result = $this->render_from_template('local_questionbank/questiontopics', $viewdata);
        return $result;
    }
    public function competencies_view($data) {
        global $DB, $PAGE, $OUTPUT, $CFG;
        $competency=$data->competency;
        list($competencysql,$competencyparams) = $DB->get_in_or_equal(explode(',',$competency));
        $currentlang= current_language();
        if( $currentlang == 'ar'){
            $querysql = "SELECT id,(CASE WHEN arabicname IS NULL THEN name ELSE arabicname END) as name, from_unixtime(timecreated, '%Y %D %M') as timevalue FROM {local_competencies} WHERE id $competencysql";
        }else{
            $querysql = "SELECT id,name, from_unixtime(timecreated, '%Y %D %M') as timevalue FROM {local_competencies} WHERE id $competencysql";
        }
        $competencies= $DB->get_records_sql($querysql,$competencyparams);
        // foreach ($competencies AS $competency) {
        //     $competency->name=  $competency->name;
        //     $competency->time=  $competency->timevalue;
        // }
        $viewdata=[
        'competencies'=>array_values($competencies),
        'questionbankid'=>$data->id,
        ];
        $result = $this->render_from_template('local_questionbank/viewcompetency', $viewdata);
        return $result;
    }

    
    public function experts_view($data) {
        global $DB, $PAGE, $OUTPUT, $CFG;
        //var_dump($data); exit;
        $currentlang= current_language();
        if($currentlang == 'ar') {
            $displaying_name = "
                CASE
                        WHEN lc.middlenamearabic IS NOT NULL  AND  lc.thirdnamearabic IS NOT NULL  THEN concat(lc.firstnamearabic,' ',lc.middlenamearabic,' ',lc.thirdnamearabic,' ',lc.lastnamearabic)
                        ELSE concat(lc.firstnamearabic,' ',lc.lastnamearabic)
                END";
        } else {
            $displaying_name = "
                CASE
                        WHEN lc.middlenameen IS NOT NULL  AND  lc.thirdnameen IS NOT NULL THEN concat(u.firstname,' ',lc.middlenameen,' ',lc.thirdnameen,' ',u.lastname)
                        ELSE concat(u.firstname,' ',u.lastname)
                END";
        }
        $expertsinfo = $DB->get_records_sql("SELECT ra.userid, $displaying_name as username, FROM_UNIXTIME(ra.timemodified,'%D %M %Y') AS dateadded
        FROM {role_assignments} as  ra 
                JOIN {role} as r ON ra.roleid = r.id  AND r.shortname = 'expert'
                JOIN {user} as u ON u.id= ra.userid
                JOIN {local_users} AS lc ON lc.userid = ra.userid ");
        $questionbankid = $DB->get_record_sql("SELECT * FROM {local_questionbank} WHERE id = $data->id");
        $availableseats = $DB->get_field_sql("SELECT SUM(seats) FROM {hall_reservations} WHERE typeid=$data->id AND type ='questionbank' AND hallid=$questionbankid->halladdress");
        if(empty($availableseats)){
            $availableseats = 0;
        }
        $qbstatus = true;
        if($questionbankid->movedtoprod ==1){
            $qbstatus = false;
        }
        $viewdata = [
            'expertsinfo' => array_values($expertsinfo),
            'questionbankid' => $data->id,
            'workshopname' => $data->workshopname,
            'availableseats' => $availableseats,
            'qbstatus' => $qbstatus,
            ];
               // print_r($viewdata );
            $result = $this->render_from_template('local_questionbank/viewexperts_old', $viewdata);
            return $result;
    }
    public function question(question_attempt $qa, qbehaviour_renderer $behaviouroutput,
            qtype_renderer $qtoutput, question_display_options $options, $number) {

        $output = '';
        $output .= html_writer::start_tag('div', array(
            'id' => $qa->get_outer_question_div_unique_id(),
            'class' => implode(' ', array(
                'que',
                $qa->get_question(false)->get_type_name(),
                $qa->get_behaviour_name(),
                $qa->get_state_class($options->correctness && $qa->has_marks()),
            ))
        ));

        // $output .= html_writer::tag('div',
        //         $this->info($qa, $behaviouroutput, $qtoutput, $options, $number),
        //         array('class' => 'info'));

        $output .= html_writer::start_tag('div', array('class' => 'content'));

        $output .= html_writer::tag('div',
                $this->add_part_heading($qtoutput->formulation_heading(),
                    $this->formulation($qa, $behaviouroutput, $qtoutput, $options)),
                array('class' => 'formulation clearfix'));
        $output .= html_writer::nonempty_tag('div',
                $this->add_part_heading(get_string('feedback', 'question'),
                    $this->outcome($qa, $behaviouroutput, $qtoutput, $options)),
                array('class' => 'outcome clearfix'));
        $output .= html_writer::nonempty_tag('div',
                $this->add_part_heading(get_string('comments', 'question'),
                    $this->manual_comment($qa, $behaviouroutput, $qtoutput, $options)),
                array('class' => 'comment clearfix'));
        $output .= html_writer::nonempty_tag('div',
                $this->response_history($qa, $behaviouroutput, $qtoutput, $options),
                array('class' => 'history clearfix border p-2'));

        $output .= html_writer::end_tag('div');
        $output .= html_writer::end_tag('div');
        return $output;
    }

    public function render_competencies($competencies) {
        global $OUTPUT;
        return $this->render_from_template('local_questionbank/viewcompetencyoneditquestion', $competencies);
    }
    public function render_topics($questiontopics) {
        global $OUTPUT;
        $data = ['topics' => $questiontopics];
        // print_object($data);die;
        return $this->render_from_template('local_questionbank/viewtopicsonquestion', $data);
    }
}
