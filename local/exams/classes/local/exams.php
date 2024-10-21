<?php
namespace local_exams\local;
use dml_exception;
use local_trainingprogram\local\trainingprogram;

require_once($CFG->dirroot .'/course/lib.php');
require_once($CFG->dirroot .'/local/exams/lib.php');
require_once($CFG->dirroot .'/group/lib.php');
require_once($CFG->libdir . '/completionlib.php');
use local_trainingprogram\local\trainingprogram as tp;
use \local_exams\form\exam_card_filters as exam_card_filters;
use \local_exams\local\profile as profile;
use local_learningtracks\learningtracks as learningtracks;
use local_hall\hall as hall;
use tool_product\product;
use tool_product\telr;
use local_competency\competency as competency;
use local_exams\local\fast_service as fastservice;
use tool_certificate\certificate as certificate_manager;

use context_system;
use stdClass;
use moodle_url;
use moodle_exception;
use block_contents;
use html_writer;
use single_button;
use csv_import_reader;
use core_text;
use local_userapproval\action\manageuser as manageuser;

class exams {

    const APPROVED = 1;
    const REJECTED = 2;
    const UNDERREVIEW = 3;
    const DRAFT = 4;

    const COUPONS = 1;
    const EARLYREGISTARATION = 2;
    const GROUP = 3;

    const SAUDI = 1;
    const NONSAUDI = 2;
    const BOTH = 3;    

    public const TRAINING_PROGRAM = 1;
    public const EXAMS = 2;
    public const EVENTS = 3;
    public const GRIEVANCE = 4;
    public const LEARNINGTRACKS = 5;
    public const EXAMATTEMPT = 6;

    public function examsfakeblock () {
        global $PAGE;
        $renderer = $PAGE->get_renderer('local_exams');
        $renderable = new \local_exams\output\examoff();
        $bc = new block_contents();
        $bc->title = get_string('pluginname','local_exams');
        $bc->attributes['class'] = 'examsfakeblock';
        $bc->content = $renderer->render($renderable);
        $PAGE->blocks->add_fake_block($bc, 'content');
    }
    public function add_update_exam($data) {
        global $DB, $CFG, $USER;
        require_once($CFG->dirroot.'/course/lib.php');
        $systemcontext = context_system::instance();
        if($data->ownedbystatus == 0){
            $data->ownedby = $data->previousownedby;
        } else{
            $data->ownedby = $data->ownedby;
        }
        $data->sectors = implode(',', array_filter($data->sectors));
        $data->ctype = implode(',', array_filter($data->ctype));
        $data->competencies = implode(',', array_filter($data->competencylevel));
        $data->clevels = str_replace("Level","Level ", $data->clevels);
        $data->exam = trim($data->examnamearabic);
        $data->examnamearabic = trim($data->examnamearabic);
        
        $data->programs = implode(',', array_filter($data->programs));
        $data->programdescription = is_array($data->programdescription) ? $data->programdescription['text'] : $data->programdescription;
        $data->requirements = implode(',', array_filter($data->requirements));
        $data->termsconditions = is_array($data->termsconditions) ? $data->termsconditions['text'] : $data->termsconditions;
        $data->additionalrequirements = is_array($data->additionalrequirements) ? $data->additionalrequirements['text'] : $data->additionalrequirements;
        $data->competencyweights = is_array($data->competencyweights) ? $data->competencyweights['text'] : $data->competencyweights;
        $data->targetaudience = is_array($data->targetaudience) ? $data->targetaudience['text'] : $data->targetaudience;
        $data->attachedmessage = is_array($data->attachedmessage) ? $data->attachedmessage['text'] : $data->attachedmessage;
        $data->classification=$data->classification;
        $data->termsconditions=$data->termsconditions;
        if($data->type == 2) {
            $data->type = $data->typename;
        } else {
            $data->type = 'professionaltest';
        }

        if($data->alltargetgroup == 1 ) {
            $data->targetgroup = '-1';
        } elseif(empty($data->targetgroup)) {
            $data->targetgroup = 0;
        } else {
            $data->targetgroup = implode(',', array_filter($data->targetgroup));
        }

        if( $data->examprice == 0 ) {
            $data->sellingprice = 0;
            $data->actualprice = 0;
            $data->tax_free = null;
        } else {
            $data->tax_free = $data->tax;
        }
        $moduleinfo->name = $data->exam;
        if($data->id > 0) {
            $data->timemodified = time();

            try{
                $transaction = $DB->start_delegated_transaction();
                $record->id = $DB->update_record('local_exams', $data);
                $course = $this->create_update_course($data);
                $profiles = $DB->get_fieldset_sql("SELECT id FROM {local_exam_profiles} WHERE examid = $data->id");
                foreach($profiles as $profile) {
                    $event = \local_hall\event\reservation_update::create(array( 'context'=>$systemcontext, 'objectid' =>  $profile ));
                    $event->trigger();
                }
                $data->exam_name= $data->exam;              
                (new \local_exams\notification())->exams_notification('exams_update', $touser=null,$fromuser=$USER, $data,$waitinglistid=0);
                $transaction->allow_commit();
                return $record;
            } catch(moodle_exception $e){
                $transaction->rollback($e);
                return false;
            }

        } else {

           try{
                $transaction = $DB->start_delegated_transaction();
                $course = $this->create_update_course($data);
                $data->classification=$data->classification;
                $data->courseid = $course->id;
                $data->timecreated = time();
                $record = $DB->insert_record('local_exams', $data);
                $attempts = $this->addexamattempts($record);

                $transaction->allow_commit();

                $event = \local_exams\event\exam_created::create(array( 'context'=>$systemcontext, 'objectid' =>$record));
                $event->trigger();

                $data->exam_name= $data->exam;           
                (new \local_exams\notification())->exams_notification('exams_create', $touser=null,$fromuser=$USER, $data,$waitinglistid=0);

                return $record;
            } catch(moodle_exception $e){
                $transaction->rollback($e);
                return false;
            }

        }
    }
    public function set_exam($id, $entitytype=false) {
        global $DB;
        $systemcontext = context_system::instance();
        if ($entitytype == 'program') {
            $data = $DB->get_record('local_trainingprogram', ['id' => $id], '*', MUST_EXIST);
            $data->type = $entitytype;

            $sql = "SELECT cmt.type,cmt.type as fullname FROM {local_competencies} AS cmt WHERE cmt.id IN ($data->competencyandlevels)";
            $competencietypes=$DB->get_records_sql_menu($sql);
            $data->ctype = $competencietypes;

            $data->competencylevel = explode(',', $data->competencyandlevels);
        } else {
            $data = $DB->get_record('local_exams', ['id' => $id], '*', MUST_EXIST);
            if (!empty($entitytype)) {
                $data->type = $entitytype;
            } else {
                if($data->type == 'professionaltest') {
                    $data->type = 1;
                } else {
                    $data->typename = $data->type;
                    $data->type = 2;
                }                
            }
            if($data->ownedbystatus == '0'){               
                $data->previousownedby = $data->ownedby; 
                $data->ownedby  = '';
            } else{
                $data->ownedby = $data->ownedby;
                $data->previousownedby = '';
            }
            $data->requirements = $data->requirements;
            $data->programdescription = ['text' => $data->programdescription];
            $data->termsconditions = ['text'=> $data->termsconditions];
            $data->competencyweights = ['text' => $data->competencyweights];
            $data->targetaudience = ['text' => $data->targetaudience];
            $data->additionalrequirements = ['text' => $data->additionalrequirements];
            $data->attachedmessage = ['text' => $data->attachedmessage];
            if($data->targetgroup == '-1') {
                $data->alltargetgroup = 1;
                $data->targetgroup = '';
            }

            $data->tax = $data->tax_free;
            
            $data->competencylevel = explode(',', $data->competencies);

        }
       
        return $data;
    }
    public function addexamattempts($examid)
    {
        global $DB, $USER;
        $systemcontext = context_system::instance();
        $exam = $DB->get_record('local_exams', ['id' => $examid]);
        $attempts = !empty($exam->noofattempts) ? $exam->noofattempts : 1;
        $sql = "SELECT COUNT(id) 
                  FROM {local_exam_attempts} 
                 WHERE examid = $examid ";
        $record = $DB->get_field_sql($sql);
        if (!empty($record)) {
            $i = ++$record;
        } else {
            $i = 1;
        }
        for($i=$i; $i<=$attempts; $i++) {
            $row = [];
            $row['examid'] = $exam->id;
            $row['attemptid'] = $i;
            if ($i == 1) {
                $row['daysbeforeattempt'] = 0; // By default first attempt
                $row['fee'] = $exam->sellingprice;
            } else {
                $row['daysbeforeattempt'] = 14; // By default days between two attempts                
            }

            $row['usercreated'] = $USER->id;
            $row['timecreated'] = time();

            $attemptid = $DB->insert_record('local_exam_attempts', $row);
            $event =  \local_exams\event\exam_attempt::create(array( 'context'=>$systemcontext, 'objectid' => $attemptid));
            $event->trigger();
        }
    }
    public function create_update_course($data)
    {
        global $DB;

        $exam = $DB->get_record('local_exams', ['code' => $data->code]);
        $test = new stdClass();
        $test->category = $DB->get_field('course_categories', 'id', ['idnumber' => 'exams']);
        $test->fullname = '{mlang en}'.$data->exam.'{mlang}{mlang ar}'.$data->examnamearabic.'{mlang}';
        $test->shortname = $data->code;
        $test->startdate = 0;
        $test->enddate = 0;
        //set default value for completion
        $courseconfig = get_config('moodlecourse');

        if (\completion_info::is_enabled_for_site()) {
            $test->enablecompletion = 1;
        } else {
            $test->enablecompletion = 0;
        }

        if( $exam ) {
    
            $test->id = $exam->courseid;
            $course = update_course($test);        
        
        } else {
        
            $course = create_course($test);
        
        }

        return $course;
    }
    public function create_coursemodule($data, $courseid)
    {
        global $DB;
        $exam = $DB->get_record('local_exams', ['id' => $data->examid]);
        $profile = $DB->get_record('local_exam_profiles', ['id' => $data->id]);
        $moduleinfo->name = '{mlang en}'.$data->exam.'{mlang}{mlang ar}'.$data->examnamearabic.'{mlang}';
        $moduleinfo->modulename = 'quiz';
        $moduleinfo->gradepass = $data->passinggrade;
        $moduleinfo->gradecat = $DB->get_field('course_categories', 'id', ['idnumber' => 'exams']);;
        $moduleinfo->grade = 100;
        $moduleinfo->section = $data->section;
        $moduleinfo->course = $exam->courseid;
        $moduleinfo->visible = 1;
        $moduleinfo->introeditor['text'] = '';
        $moduleinfo->introeditor['format'] = FORMAT_HTML;
        $moduleinfo->quizpassword = !empty($data->password) ? $data->password : 0;
        $moduleinfo->completion = 2;
        $moduleinfo->completiongradeitemnumber = 0;
        $moduleinfo->preferredbehaviour = 'deferredfeedback';
        $moduleinfo->hidden = 0;
        $moduleinfo->overduehandling = 'autosubmit';

        $moduleinfo->attempts = $exam->noofattempts;
        $moduleinfo->attemptimmediately = 1;
        $moduleinfo->correctnessimmediately = 1;
        $moduleinfo->marksimmediately = 1;
        $moduleinfo->specificfeedbackimmediately = 1;
        $moduleinfo->generalfeedbackimmediately = 1;
        $moduleinfo->rightanswerimmediately = 1;
        $moduleinfo->overallfeedbackimmediately = 1;
        $moduleinfo->attemptopen = 1;
        $moduleinfo->correctnessopen = 1;
        $moduleinfo->marksopen = 1;
        $moduleinfo->specificfeedbackopen = 1;
        $moduleinfo->generalfeedbackopen = 1;
        $moduleinfo->rightansweropen = 1;
        $moduleinfo->overallfeedbackopen = 1;
        
        $moduleinfo->questionsperpage = 1;
        $moduleinfo->shuffleanswers = 1;

        $moduleinfo->timeopen = 0;
        $moduleinfo->timeclose = 0;
        $moduleinfo->timelimit = $data->duration;

        if($data->id > 0) {
            $moduleid = $DB->get_field('modules', 'id', ['name' => 'quiz']);
            $cmid = $DB->get_field('course_modules', 'id', ['course' => $moduleinfo->course, 'module' => $moduleid, 'instance' => $profile->quizid]);
            $moduleinfo->id = $cmid;
            $moduleinfo->cmid = $cmid;
            $moduleinfo->coursemodule = $cmid;
            $quizid = update_module($moduleinfo);
        } else {
            $quizid = create_module($moduleinfo);
        }

        return $quizid;
    }
    public static function examhall($competencies = array(),$programid=0) {
        global $DB, $USER;
        if ($programid){
            $competency= $DB->get_records_sql_menu("SELECT h.id, h.name as title 
                                                    FROM {hall} as h
                                                    JOIN {local_exams} as lot 
                                                    ON concat(',', lot.halladdress, ',') LIKE concat('%,',h.id,',%')
                                                    WHERE availability = 1 AND lot.id=:programid",['programid' => $programid]);
        }
        return $competency;            
    }

    public function add_update_seating($data) {
        global $DB, $USER;
        $data->typeid = $data->examid;
        $data->type = 'exam';
        $record = $DB->insert_record('hall_reservations', $data);        
    }
    public function get_listof_exams($stable, $filterdata) {
        global $DB, $PAGE, $OUTPUT, $USER,$SESSION;
        $SESSION->lang = ($stable->mlang) ? $stable->mlang : current_language() ;
        $examlist = [];
        $completedlist = [];
        $reviewexms = [];
        $slr = 1;
        $sle = 1;
        $slc = 1;
        $products = [];

        if ($filterdata->status == 2) {

            $filterdata->status = 'local_exams';
            $renderer = $PAGE->get_renderer('tool_product');
            $products = $renderer->lis_org_purchases($stable,$filterdata);
            $recordscount = COUNT($products);
        
        } else {
            $systemcontext = context_system::instance();
            $searchparams = [];

            $selectsql = "SELECT le.*,lep.profilecode   FROM {local_exams} le
                        LEFT JOIN {local_exam_profiles} lep ON lep.examid = le.id
                        "; 
             /*if(!empty($filterdata->favourites) ){
            $selectsql .=" LEFT JOIN {favourite} fav ON le.id = fav.itemid ";
            }*/  
             $selectsql .=" WHERE 1=1 ";            
            $countsql  = "SELECT COUNT(le.id) FROM {local_exams} le
                        LEFT JOIN {local_exam_profiles} lep ON lep.examid = le.id
                       ";
            /* if(!empty($filterdata->favourites) ){
            $countsql.=" LEFT JOIN {favourite} fav ON le.id = fav.itemid ";
            }*/  
            $countsql .=" WHERE 1=1 ";            
            if (!$filterdata->status && $stable->class != 'reviewexams') {
                $formsql .=" AND status = 1 ";
            } elseif ($stable->class == 'reviewexams') {
                $formsql .=" AND status = 0 "; 
            } elseif ($filterdata->status) {
                $formsql .=" AND status = 1 ";
            }

            $lang = current_language();
           
            if(isset($filterdata->search_query) && trim($filterdata->search_query) != ''){
                $formsql .= " AND (le.exam LIKE :ennamesearch  OR le.examnamearabic LIKE :examnamearabicsearch   OR le.code LIKE :codesearch OR lep.profilecode LIKE :profilecodesearch )";
                $searchparams = array(
                                       'ennamesearch' => '%'.trim($filterdata->search_query).'%',
                                       'examnamearabicsearch' => '%'.trim($filterdata->search_query).'%',
                                       'codesearch' => '%'.trim($filterdata->search_query).'%',
                                       'profilecodesearch' => '%'.trim($filterdata->search_query).'%',
                                    );
            }
           
            if (!empty($filterdata->type)){
                $formsql .= " AND le.type = '{$filterdata->type}' "; 
            }
            if (!empty($filterdata->halladdress)){
                $formsql .= " AND le.halladdress =".$filterdata->halladdress; 
            }
            
            if(!empty($filterdata->sectors)){
                $formsql.=" AND  le.sectors IN ($filterdata->sectors) ";
            } 

            //  if(!empty($filterdata->favourites) ){
            //     $formsql .=" AND fav.component = 'local_exams' AND  fav.userid='$USER->id' ";
            // }

            $params = array_merge($searchparams);
            $totalexams = $DB->count_records_sql($countsql.$formsql, $params);
            $formsql .=" ORDER BY le.id DESC";
            $exams = $DB->get_records_sql($selectsql.$formsql, $params, $stable->start,$stable->length);


            foreach($exams as $exam) {
                if($exam->status == 0) {
                    $reviewexms[] = $this->examdetails($exam->id, $slr);
                    if(has_capability('local/exams:publishexams',$systemcontext) || is_siteadmin() || has_capability('local/organization:manage_examofficial', $systemcontext)){ 
                        $publishexams = true;
                    } else {
                        $publishexams = false;
                    }
                    $recordscount = $totalexams;
                    $slr++;
                } else {
                        $recordscount = $totalexams;
                        $examlist[] = $this->examdetails($exam->id, $slc);
                        if(has_capability('local/exams:manageactions',$systemcontext) || is_siteadmin() || has_capability('local/organization:manage_examofficial', $systemcontext)){ 
                            $manageactions = true;
                        } else {
                            $manageactions = false;
                        }
                        $slc++;
                }
            }
        }

        $coursesContext = array(
            "exams" => $examlist,
            "products" => $products,
            "nocourses" => $nocourse,
            "totalexams" => $recordscount,
            "length" => $recordscount,
            "completedlist" => $completedlist,
            "reviewexms" => $reviewexms,
            "manageactions" => $manageactions,
            "publishexams" => $publishexams,
            "userview" => $userview,
            "certificate" => $certificate,
            "grievance" => $grievance,
            "examcompleted" => $examcompleted,
        );
        return $coursesContext;
    }
    public function get_listof_userexams($stable, $filterdata)
    {
        global $DB, $PAGE, $OUTPUT, $USER,$SESSION;
        $systemcontext = context_system::instance();
        $searchparams = [];
        // $SESSION->lang = ($stable->mlang) ? $stable->mlang : current_language() ;
        if($filterdata->status == 1) {
            $selectsql = "SELECT leu.examid, leu.userid,lep.profilecode,le.courseid, lep.showexamgrade, lep.quizid, lep.passinggrade, lep.id AS profileid, ROUND(gg.finalgrade, 2) as finalgrade
                            FROM
                            (
                                SELECT max(id) as id FROM {local_exam_userhallschedules} GROUP BY examid, userid
                            ) lleu
                            LEFT JOIN {local_exam_userhallschedules} leu on leu.id = lleu.id
                            JOIN {local_exams} le ON le.id = leu.examid 
                            LEFT JOIN {local_exam_profiles} lep ON lep.id = leu.profileid AND le.id = lep.examid AND lep.examid = leu.examid 
                            LEFT JOIN {exam_enrollments} lee ON lee.examid =leu.examid  AND lee.userid = leu.userid
                            LEFT JOIN {grade_items} gi ON gi.courseid = le.courseid AND gi.iteminstance = lep.quizid AND gi.itemtype = 'mod' AND gi.itemmodule='quiz'
                            LEFT JOIN {grade_grades} gg ON gg.itemid = gi.id AND gg.userid = leu.userid
                             ";

            //   if(!empty($filterdata->favourites) ){
            //     $selectsql.=" LEFT JOIN {favourite} fav ON le.id = fav.itemid ";
            // }               
            $selectsql.=" WHERE leu.examdate !=0 AND leu.examdate < UNIX_TIMESTAMP() AND lep.passinggrade <= gg.finalgrade  ";
            $formsql  = " AND leu.userid = $USER->id AND lee.enrolstatus = 1 "; 

            if(isset($filterdata->search_query) && trim($filterdata->search_query) != ''){
                $formsql .= " AND (le.exam LIKE :search OR lep.profilecode LIKE :profilecodesearch)";
                $searchparams = array('search' => '%'.trim($filterdata->search_query).'%','profilecodesearch' => '%'.trim($filterdata->search_query).'%');
            }
            if( $filterdata->{'examdatetime[enabled]'} == 1 && $filterdata->{'enddate[enabled]'} == 1 ){
                $start_year = $filterdata->{'examdatetime[year]'};
                $start_month = $filterdata->{'examdatetime[month]'};
                $start_day = $filterdata->{'examdatetime[day]'};
                $filter_starttime_con = mktime(0,0,0, $start_month, $start_day, $start_year);

                $end_year = $filterdata->{'enddate[year]'};
                $end_month = $filterdata->{'enddate[month]'};
                $end_day = $filterdata->{'enddate[day]'};
                $filter_endtime_con=mktime(23,59,59, $end_month, $end_day, $end_year);
                $formsql.= " AND (le.examdatetime >= $filter_starttime_con AND le.examdatetime < $filter_endtime_con) ";
                $formsql.= " OR (le.enddate >= $filter_starttime_con AND le.enddate < $filter_endtime_con) ";
            } elseif($filterdata->{'examdatetime[enabled]'} == 1 ){
                $start_year = $filterdata->{'examdatetime[year]'};
                $start_month = $filterdata->{'examdatetime[month]'};
                $start_day = $filterdata->{'examdatetime[day]'};
                $filter_starttime_con = mktime(0,0,0, $start_month, $start_day, $start_year);
                $formsql.= " AND le.examdatetime >= '$filter_starttime_con' ";
            } elseif($filterdata->{'enddate[enabled]'} == 1 ){
                $start_year = $filterdata->{'enddate[year]'};
                $start_month = $filterdata->{'enddate[month]'};
                $start_day = $filterdata->{'enddate[day]'};
                $filter_endtime_con=mktime(23,59,59, $start_month, $start_day, $start_year);
                $formsql.=" AND le.enddate <= '$filter_endtime_con' ";
            }

            if (!empty($filterdata->type) && $filterdata->type > 0 ){
                $formsql .= " AND le.type = '{$filterdata->type}' "; 
            }
            if(!empty($filterdata->sectors)){
                $formsql.=" AND  le.sectors IN ($filterdata->sectors) ";
            } 
            if(!empty($filterdata->startdate) ){
                $formsql.= " AND le.examdatetime = '$filterdata->startdate' ";
            }
            if(!empty($filterdata->enddate)){
                $formsql.=" AND le.enddate = '$filterdata->enddate' ";
            }
            //  if(!empty($filterdata->favourites) ){
            //     $formsql.=" AND fav.component = 'local_exams' AND  fav.userid='$USER->id' ";
            // }
            $groupsql = " ORDER BY leu.id DESC ";
            $params = array_merge($searchparams);
            $exams = $DB->get_records_sql($selectsql.$formsql.$groupsql, $params, $stable->start, $stable->length);
            $completedexams = $DB->get_records_sql($selectsql.$formsql.$groupsql, $params);
            $totalexams = COUNT($completedexams);
            $certificate = true;
            $grievance = false;
        } elseif($filterdata->status == 3) {
            $filterdata->status = 'local_exams';
            $filterdata->type = 'exam';
            $filterdata->view = 'mobile';
            $filtervalues = (array)$filterdata;
            $recommendedexams = (new manageuser)->recommendedentities($stable, $filtervalues);
            $recordscount = $recommendedexams['totalentities'];
        } else {
            $selectsql = "SELECT leu.examid, leu.userid,lep.profilecode,le.courseid, lep.showexamgrade, lep.quizid, lep.passinggrade, lep.id AS profileid, ROUND(gg.finalgrade, 2) as finalgrade
                            FROM
                            (
                                SELECT max(id) as id FROM {local_exam_userhallschedules} GROUP BY examid, userid
                            ) lleu
                            LEFT JOIN {local_exam_userhallschedules} leu on leu.id = lleu.id
                            JOIN {local_exams} le ON le.id = leu.examid 
                            LEFT JOIN {local_exam_profiles} lep ON lep.id = leu.profileid AND le.id = lep.examid AND lep.examid = leu.examid 
                            LEFT JOIN {exam_enrollments} lee ON lee.examid =leu.examid  AND lee.userid = leu.userid
                            LEFT JOIN {grade_items} gi ON gi.courseid = le.courseid AND gi.iteminstance = lep.quizid AND gi.itemtype = 'mod' AND gi.itemmodule='quiz'
                            LEFT JOIN {grade_grades} gg ON gg.itemid = gi.id AND gg.userid = leu.userid
                            ";
            //  if(!empty($filterdata->favourites) ){
            //     $selectsql.=" LEFT JOIN {favourite} fav ON le.id = fav.itemid ";
            // }
              $selectsql.=" WHERE (lep.passinggrade > gg.finalgrade OR gg.finalgrade IS NULL)  ";              
            $formsql  = " AND leu.userid = $USER->id ";

            if(isset($filterdata->search_query) && trim($filterdata->search_query) != ''){
                $formsql .= " AND (le.exam LIKE :search OR lep.profilecode LIKE :profilecodesearch)";
                $searchparams = array('search' => '%'.trim($filterdata->search_query).'%','profilecodesearch' => '%'.trim($filterdata->search_query).'%');
            }
            if( $filterdata->{'examdatetime[enabled]'} == 1 && $filterdata->{'enddate[enabled]'} == 1 ){
                $start_year = $filterdata->{'examdatetime[year]'};
                $start_month = $filterdata->{'examdatetime[month]'};
                $start_day = $filterdata->{'examdatetime[day]'};
                $filter_starttime_con = mktime(0,0,0, $start_month, $start_day, $start_year);

                $end_year = $filterdata->{'enddate[year]'};
                $end_month = $filterdata->{'enddate[month]'};
                $end_day = $filterdata->{'enddate[day]'};
                $filter_endtime_con=mktime(23,59,59, $end_month, $end_day, $end_year);
                $formsql.= " AND (le.examdatetime >= $filter_starttime_con AND le.examdatetime < $filter_endtime_con) ";
                $formsql.= " OR (le.enddate >= $filter_starttime_con AND le.enddate < $filter_endtime_con) ";
            } elseif($filterdata->{'examdatetime[enabled]'} == 1 ){
                $start_year = $filterdata->{'examdatetime[year]'};
                $start_month = $filterdata->{'examdatetime[month]'};
                $start_day = $filterdata->{'examdatetime[day]'};
                $filter_starttime_con = mktime(0,0,0, $start_month, $start_day, $start_year);
                $formsql.= " AND le.examdatetime >= '$filter_starttime_con' ";
            } elseif($filterdata->{'enddate[enabled]'} == 1 ){
                $start_year = $filterdata->{'enddate[year]'};
                $start_month = $filterdata->{'enddate[month]'};
                $start_day = $filterdata->{'enddate[day]'};
                $filter_endtime_con=mktime(23,59,59, $start_month, $start_day, $start_year);
                $formsql.=" AND le.enddate <= '$filter_endtime_con' ";
            }

            if (!empty($filterdata->type) && $filterdata->type > 0){
                $formsql .= " AND le.type = '{$filterdata->type}' "; 
            }
            if(!empty($filterdata->sectors)){
                $formsql.=" AND  le.sectors IN ($filterdata->sectors) ";
            } 
            if(!empty($filterdata->startdate) ){
                $formsql.= " AND le.examdatetime = '$filterdata->startdate' ";
            }
            if(!empty($filterdata->enddate)){
                $formsql.=" AND le.enddate = '$filterdata->enddate' ";
            }
            //  if(!empty($filterdata->favourites) ){
            //     $formsql.=" AND fav.component = 'local_exams' AND  fav.userid='$USER->id' ";
            // }

            $params = array_merge($searchparams);
            $formsql .=" ORDER BY leu.id DESC "; 
            $totalexams = $DB->get_records_sql($selectsql.$formsql, $params);
            $exams = $DB->get_records_sql($selectsql.$formsql, $params, $stable->start,$stable->length);
            $totalexams = COUNT($totalexams);
            // $grievance = true;
        }
        $userview = true;

        $examlist = [];
        $completedlist = [];
        $isArabic = $stable->mlang;
        foreach($exams as $exam) {
           if($filterdata->status == 1) {
                $examlist[] = $this->userexamdetails($exam->examid, $exam->userid,'completed',$isArabic, $exam);  
            } else {

                $examlist[] = $this->userprofiles((int)$exam->examid);
            }
            if(has_capability('local/exams:manageactions',$systemcontext) || is_siteadmin() || has_capability('local/organization:manage_examofficial', $systemcontext)){ 
                $manageactions = true;
            } else {
                $manageactions = false;
            }
        }

        $coursesContext = array(
            "exams" => $examlist,
            "totalexams" => $totalexams,
            "length" => $totalexams,
            "completedlist" => $completedlist,
            "manageactions" => $manageactions,
            "userview" => $userview,
            "certificate" => $certificate,
            "grievance" => $grievance,
            "recommendedexams" => $recommendedexams['hascourses'],
        );
        return $coursesContext;
    }

    public function userexamdetails($id, $userid, $status,$isArabic = NULL, $examdata) {
        global $DB,$USER;

        if($status == 'completed') {
            $exams = $DB->get_record('local_exams', ['id' => $id]);
            $enrolment = $DB->get_record('exam_completions', ['examid' => $id, 'userid' => $userid]);
            $examenrolment = $DB->get_record('exam_enrollments', ['examid' => $id, 'userid' => $userid]);
            $profile = $DB->get_record('local_exam_profiles', ['id' => $examdata->profileid]);

            $examlist = $this->examdetails($exams->id,null,$isArabic);
            $examlist['completedon'] = !empty($enrolment->completiondate) ? date('Y-m-d', $enrolment->completiondate) : '--';
            $examlist['usercompletedon'] = !empty($enrolment->completiondate) ? $enrolment->completiondate : '--';
           // $grade = $this->usergrade($examenrolment->id);
            $examlist['showgrade'] = $profile->showexamgrade;
            $examlist['passinggrade'] = $examdata->finalgrade;//$grade['grade'];
            $examlist['gradeachieved'] = true;
            $endtime = date('Y-m-d', (strtotime($examenrolment->examdate) + ($exams->slot + $exams->examduration)));
            if( (date('Y-m-d') > $endtime) && empty($enrolment) ) {
                $examcompleted = true;
            }
            $examlist['examcompleted'] = $examcompleted;
            $examlist['completedtab'] = true;
            $examlist['reservationid'] = $examenrolment->hallreservationid;
            $examlist['profileid'] = $examdata->profileid;
            $examlist['hascertificate'] = !empty($profile->hascertificate) ? true : false;
            $examlist['certificateid'] = $this->certificate($exams->id);
            $examlist['result'] = ($examdata->finalgrade >= (float)$profile->passinggrade) ? get_string('pass','local_exams'): get_string('fail','local_exams');
            $examlist['attemptnumber'] = $this->quiz_attemptscount($id);
            $component = 'local_exams';
            $examlist['checkfavornot'] =$this->checkfavourites($id,$USER->id,$component);
        }

        return $examlist;
    }

     public function checkfavourites($itemid,$userid,$component){
        global $DB,$USER;
        $query = "SELECT itemid from {favourite} where itemid = $itemid AND userid = $USER->id AND component = '$component' ";
        $getfavourite = $DB->get_field_sql($query);
        return $getfavourite;

    }

    public function usergrade($id)
    {
        global $DB;
        $sql = "SELECT le.courseid, lep.quizid, ee.userid, lep.passinggrade 
                  FROM {local_exams} le 
                  JOIN {local_exam_profiles} lep ON lep.examid = le.id 
                  JOIN {exam_enrollments} ee ON ee.examid = le.id AND ee.profileid = lep.id 
                 WHERE ee.id=".$id;
        $record = $DB->get_record_sql($sql);
        if(!$record){
            $gradeachieved = false;
            $grade = 0;
        }else{
            $quizname = $DB->get_field('quiz', 'name', ['id' => $record->quizid]);
            $sql = "SELECT ROUND(gg.finalgrade, 0) as finalgrade, gi.gradepass
                    FROM {grade_items} gi
                    JOIN {grade_grades} gg on gg.itemid = gi.id
                    WHERE gi.courseid = {$record->courseid} AND gi.itemtype = 'mod' AND gi.itemmodule  = 'quiz' AND gi.iteminstance = {$record->quizid} AND gg.userid = {$record->userid} ";
            $passinggrade = $DB->get_record_sql($sql);

            $grade = !empty($passinggrade->finalgrade) ? $passinggrade->finalgrade : 0;
            if ($grade >= $record->passinggrade) {
                $gradeachieved = true;
            } else {
                $gradeachieved = false;
            }
        }
        return COMPACT('grade', 'gradeachieved');
    }


    public function exam_info($examid) {
        global $DB, $PAGE, $OUTPUT;
        $systemcontext = context_system::instance();
        $PAGE->set_context($systemcontext);
        $examlist = $this->examdetails($examid);
        $renderer = $PAGE->get_renderer('local_exams');
        $org  = $renderer->exam_info($examlist);
        return $org;
    }
    public function sectors_info($examid) {
        global $DB, $PAGE, $OUTPUT;
        $systemcontext = context_system::instance();
        $PAGE->set_context($systemcontext);
        $sectors = $DB->get_field('local_exams', 'sectors', ['id' => $examid], '*', MUST_EXIST);
        $renderer = $PAGE->get_renderer('local_exams');

        $lang= current_language();

        if( $lang == 'ar'){
            $sectordata = $DB->get_records_sql_menu("SELECT id, titlearabic as title FROM {local_sector} WHERE id IN ($sectors)");
        } else{
            $sectordata = $DB->get_records_sql_menu("SELECT id, title FROM {local_sector} WHERE id IN ($sectors)");
        }

        foreach ($sectordata AS $key => $sector ) {
            $row['sector'] = $sector;
            $data[] = $row;
        }
        $org  = $renderer->sectors_info($data);
        return $org;
    }
    public function examdetails($examid, $slno = NULL,$isArabic = NULL) {
        global $DB, $PAGE, $OUTPUT, $USER,$SESSION;
        $SESSION->lang = ($isArabic) ? $isArabic : current_language() ;
        $systemcontext = context_system::instance();
        $PAGE->set_context($systemcontext);
        $exams = $DB->get_record('local_exams', ['id' => $examid], '*', MUST_EXIST);

        $examlist["examstartdate"] = $exams->examdatetime;
        $examlist["examenddate"] = $exams->enddate;
        $slotstart = $exams->slot;
        $slotend = $exams->slot + $exams->examduration;
        $examlist['slot'] = $slotstart .' to '. $slotend;
        $examlist["starttime"] = $slotstart;
        $examlist["endtime"] = $slotend;

        
        if($SESSION->lang == 'ar') {

            if($slotstart > 43200) {
                $endtmeridian = 'مساءً';
            } else {
                $endtmeridian = 'صباحا';
            }

        } else {

            if($slotend > 43200) {
                $endtmeridian = 'PM';            
            } else {
                $endtmeridian = 'AM';
            }

        }
        $examlist['examstarttime'] = date('h:i', mktime(0, 0, $slotstart)).' '.$endtmeridian;

        if($SESSION->lang == 'ar') {

            if($slotend > 43200) {
                $endtmeridian = 'مساءً';
            } else {
                $endtmeridian = 'صباحا';
            }

        } else {
            
            if($slotend > 43200) {
                $endtmeridian = 'PM';            
            } else {
                $endtmeridian = 'AM';
            }

        }

        $examlist['examendtime'] = date('h:i', mktime(0, 0, $slotend)).' '.$endtmeridian;

        $userrolesql = " SELECT r.shortname
                    FROM {role_assignments} ra 
                    JOIN {context} c ON c.id = ra.contextid 
                    JOIN {role} r ON r.id = ra.roleid
                    WHERE 1 = 1 AND ra.userid = :userid AND (";
        foreach ($USER->access['ra'] as $key => $value) {
            $userrolesql .= " c.path LIKE '".$key."' OR ";
        }
        $userrolesql .= " 1 = 1) GROUP BY ra.roleid, c.contextlevel, r.shortname LIMIT 1 ";
        $userroles = $DB->get_record_sql($userrolesql, ['userid' => $USER->id]);
        if($userroles->shortname == 'trainee') {
            $examdetails = $DB->get_record_sql("SELECT *
                                                FROM {exam_enrollments} 
                                                WHERE examid = {$examid} AND userid = {$USER->id} AND CAST(examdate AS DATE) = CURDATE() ");
            if($examdetails) {
                $examlist["examdatetime"] = date('Y-m-d', strtotime($examdetails->examdate));
                $examlist["enddate"] = date('Y-m-d', strtotime($examdetails->examdate));
                $examtime = date('Y-m-d H:i:s', (strtotime($examdetails->examdate) + ($exams->slot - 300)));
                $endtime = date('Y-m-d', (strtotime($examdetails->examdate) + ($exams->slot + $exams->examduration)));
                $enrolment = $DB->get_record('exam_completions', ['examid' => $examid, 'userid' => $USER->id]);

                if(date('Y-m-d H:i:s') >= $examtime && date('Y-m-d') <= $endtime) {
                    $examlist["todayexam"] = true;
                } elseif( (date('Y-m-d') > $endtime) && empty($enrolment) ) {
                    $examlist["examcompleted"] = true;
                }

            } else {
                $examlist["examdatetime"] = strftime('%d %B %Y', $exams->examdatetime);
                $examlist["enddate"] = strftime('%d %B %Y', $exams->enddate);
            }

        } else {

            $examlist["examdatetime"] = userdate($exams->examdatetime,get_string('strftimedatemonthabbr', 'core_langconfig'));
            $examlist["enddate"] = userdate($exams->enddate,get_string('strftimedatemonthabbr', 'core_langconfig'));

        }
        // CHECK grievance Payment
        $gre_sql = " SELECT eg.examid, eg.userid, eg.status 
                    FROM {local_exam_grievance} eg
                    JOIN {exam_grievance_payments} AS egp ON eg.id = egp.grievanceid    
                    WHERE eg.examid = $exams->id AND eg.userid = $USER->id";
        $grievance = $DB->get_record_sql($gre_sql);

        if($grievance) {
            $examlist["grievanceexist"] = true;
        } else {
            $examlist["grievanceexist"] = false;
        }

        $today = date('d-m-Y');
        $enddate = date('d-m-Y', $exams->enddate);
        $diff = date_diff(date_create($today),date_create($enddate));
        $examlist["slno"] = $slno;
        $examlist["id"] = $exams->id;
        $lang= current_language();
        if( $SESSION->lang == 'ar' && !empty($exams->examnamearabic)){
                $exam = $exams->examnamearabic;
        } else{
                $exam = $exams->exam;
        }

        $examlist["exam"] = format_string($exam);
        $examlist["code"] = $exams->code;

        if(!is_siteadmin() && has_capability('local/organization:manage_organizationofficial',$systemcontext))
        {
            $examlist["orgoff"] = true;
            $examlist["sellingprice"] = !empty($exams->sellingprice) ? $exams->sellingprice : 0;
        } else {
            $examlist["sellingprice"] = !empty($exams->sellingprice) ? $exams->sellingprice : 0;
        }
        $examlist["courseid"] = $exams->courseid;
        $examlist["sellingprice"] = !empty($exams->sellingprice) ? number_format($exams->sellingprice,0,'.','') : 0;
        $examlist["actualprice"] = !empty($exams->actualprice) ? number_format($exams->actualprice,0,'.','') : 0;
        $examlist["programdescription"] = format_text($exams->programdescription, FORMAT_HTML);
        $examlist["certificatevalidity"] = $exams->certificatevalidity;
        $examlist["ownedby"] = format_string($exams->ownedby);
        $examlist["scheduleaction"] = ($exams->ownedby != 'FA') ? true : false;
        $examlist["quizpassgrade"] = !empty($exams->quizpassgrade) ? $exams->quizpassgrade : 0;
        $examlist["hallid"] = $exams->halladdress;
        $hall = $DB->get_record('hall', ['id' => $exams->halladdress]);
        $examlist["hallseats"] = $hall->seatingcapacity;
        $examlist["halladdress"] = $hall->city.'-'.$hall->buildingname.'-'.$hall->name;
        $examlist["city"] = !empty($hall->city) ? $this->examfields('city', $hall->city) : '--';
        $examlist["buildingname"] = $hall->buildingname;
        $examlist["seatingcapacity"] = $exams->seatingcapacity;
        $examlist["name"] = $hall->name;
        $examlist["targetaudience"] = format_text($exams->targetaudience, FORMAT_HTML);
        $examlist["description"] = format_text($exams->programdescription, FORMAT_HTML);
        $examlist["competencies"] = !empty($exams->competencies) ? $this->examfields('compentency', $exams->competencies) : '--';
        $examlist["competencyweights"] = !empty($exams->competencyweights) ? format_text($exams->competencyweights, FORMAT_HTML) : '--';
        $examlist["requirements"] = $DB->get_field('local_exams', 'exam', ['id' => $exams->requirements]);
        $examlist["sectors"] = $this->examfields('sector', $exams->sectors);
        $examlist["sectorsinfo"] = !empty($this->examsectors($exams->sectors)) ? $this->examsectors($exams->sectors) : [];

        $hall_sql =  " SELECT hr.id as reservationid, hr.slotstart, hr.slotend, hr.seats, hr.examdate,h.* 
                        FROM {hall_reservations} hr 
                        JOIN {hall} h ON hr.hallid = h.id 
                        WHERE hr.typeid = $exams->id AND hr.type = 'exam' AND hr.status=1";
        $hasreservation = $DB->get_record_sql($hall_sql, null, IGNORE_MULTIPLE);
        $examlist["hasreservation"] = $hasreservation->id ? true : false;
        $stable = new \stdClass();
        $stable->thead = false;
        $stable->start = 0;
        $stable->length = -1;
        $stable->examid = $exams->id;
        $stable->courseid=$exams->courseid;
        $stable->noofquestions=$exams->noofquestions;
        $stable->quizid=$exams->quizid;
        $nn = $this->get_questionbanks_exams($stable);
        $examlist["noofquestions"] = !empty($nn['mappedquestionbankscount']) ? $nn['mappedquestionbankscount'] : 0;

        if($exams->type == 'professionaltest') {
            $examlist["type"] = get_string('professionaltest', 'local_exams');
        } else {
            $examlist["type"] = format_string($exams->type);
        }

        $examlist["programs"] = $this->examfields('programs', $exams->programs);
        $examlist["examprice"] = $this->examfields('examprice', $exams->examprice);
        $examlist["pricetype"] = $exams->examprice;
        $examlist["language"] = $this->examfields('language', $exams->language);
        $examlist["status"] = $this->examfields('status', $exams->status);
        $examlist["examduration"] = $exams->examduration/60 .' '. get_string('min','local_exams');

        if (filter_var($hall->maplocation, FILTER_VALIDATE_URL)) {
            $examlist["locationstatus"] = true;
        } else {
            $examlist["locationstatus"] = false;
        }

        $examlist["maplocation"] = $hall->maplocation;
        // $userexamdate = $DB->get_field('exam_enrollments', 'examdate', ['examid' => $exams->id, 'userid' => $USER->id]);
        $examlist["userexamdate"] = !empty($userexamdate) ? date('d/m/Y', $userexamdate) : '--';
        $enrole = $DB->get_field('exam_enrollments', 'COUNT(id)', ['examid' => $exams->id]);
        $examlist['enrolled'] = !empty($enrole) ? $enrole : 0;
        $examlist["quizid"] = $this->coursemoduleid($exam->courseid, $exam->quizid);

        if($diff->format("%R%a")>=0) { 
            $examlist["examleftdays"] = $diff->format("%a");
        } else {
            $examlist["examleftdays"] = 0;
        }
        //Rajut added to get tool certificate
        $examlist['certificateid'] = $this->certificate($exams->id);
        $examlist["enrolled"] = $DB->count_records_sql("SELECT COUNT(id) FROM {exam_enrollments} WHERE examid = ".$examid);
        $context = context_system::instance();
        $examlist["contextid"] = $context->id;
       // $examlist["level"] = !empty($exams->clevels) ? str_replace('level', 'Level ', $exams->clevels) : '--';
       $examlist["level"] = !empty($exams->clevels) ? get_string($exams->clevels,'local_competency') : '--';
        $examlist["profilescount"] = $DB->count_records_sql("SELECT COUNT(le.id) FROM {local_exam_profiles} le where le.examid =".$examid);
        $examlist['attachedmessage_display'] = strip_tags(format_text($exams->attachedmessage, FORMAT_HTML)) ? true:false;
        $examlist['attachedmessage'] = format_text($exams->attachedmessage, FORMAT_HTML);

         $component = 'local_exams';
         $examlist['checkfavornot'] =$this->checkfavourites($examid,$USER->id,$component);
        return $examlist;
    }

    public function coursemoduleid($courseid, $quizid)
    {
        global $DB;
        $moduleid = $DB->get_field('modules', 'id', ['name' => 'quiz']);
        $id = $DB->get_field('course_modules', 'id', ['course' => $courseid, 'module' => $moduleid, 'instance' => $quizid]);
        $cmid = !empty($id) ? $id : 0;

        return $cmid;
    }


    public function certificate($examid)
    {
        global $DB, $USER;
        $systemcontext = context_system::instance();
        if(has_capability('local/organization:manage_trainee', $systemcontext)) {
            $certid = $DB->get_field('tool_certificate_issues', 'code', array('moduleid'=>$examid,'userid'=>$USER->id,'moduletype'=>'exams'));
            $certificateid = !empty($certid) ? $certid : 0;
        } else {
            $certificateid = 0;
        }

        return $certificateid;
    }

    public function exam_enrolled_users($type = null, $profileid = 0, $params){
        global $DB, $USER;
        $context = context_system::instance();
        $profile = $DB->get_record('local_exam_profiles', ['id' => $params['profileid']]);
        $exam = $DB->get_record('local_exams', ['id' => $profile->examid]);

        $courseid = $exam->courseid;

        $traineerole= $DB->get_field('role', 'id', array('shortname' => 'trainee'));

        $fullname = tp::user_fullname_case($method = 'enrollment');

        $sql = "SELECT DISTINCT u.id,$fullname";
        $where = '';
        $sql.=" FROM {user} AS u 
                JOIN {local_users} AS lc ON lc.userid = u.id 
                JOIN {role_assignments} AS roa ON roa.userid = u.id
                JOIN {course} as c ON c.id=$courseid
                WHERE  u.id > 2 AND u.deleted = 0  AND lc.deleted = 0 AND roa.roleid = $traineerole AND roa.contextid = $context->id ";
        if ($params['orgid']) {
            $where = " AND lc.organization = ".$params['orgid'];
        }
        if(!is_siteadmin() && has_capability('local/organization:manage_organizationofficial',$context)) {
            $organization = $DB->get_field('local_users','organization',array('userid'=>$USER->id));
            $sql.= " AND  
            CASE
                WHEN  lc.organization >0 THEN lc.organization = $organization
                ELSE lc.organization <> 0
            END  ";
        }
        if($lastitem!=0){
           $sql.=" AND u.id > $lastitem ";
        }
        $sql .=" AND u.id <> $USER->id ";

        if (!empty($params['query'])) {
            $search = trim($params['query']);
           
            $sql .= " AND (u.firstname LIKE '%$search%' OR 
                        u.lastname LIKE '%$search%' OR
                        lc.firstnamearabic LIKE '%$search%' OR
                        lc.lastnamearabic LIKE '%$search%' OR
                        lc.middlenameen LIKE '%$search%' OR
                        lc.middlenamearabic LIKE '%$search%' OR
                        lc.thirdnameen LIKE '%$search%' OR 
                        lc.thirdnamearabic LIKE '%$search%' OR 
                        u.email LIKE '%$search%' OR 
                        lc.id_number LIKE '%$search%') ";
        }
        if (!empty($params['email'])) {
             $sql.=" AND u.id IN ({$params['email']})";
        }
        if (!empty($params['halls']) && !empty($params['examdate']) && $type=='remove') {
            $sql1 = "SELECT userid FROM {exam_enrollments} as ee WHERE examid = {$params['examid']} AND hallid = {$params['halls']} AND examdate = '{$params['examdate']}'" ;
            $data = $DB->get_fieldset_sql($sql1);
            if(count($data) != 0) {
                $users = implode(',', $data);
                $sql.=" AND 1=1 AND u.id IN ($users) ";                
            } else {
                $sql.=" AND u.id IN (0) ";
            }
        }
        if (!empty($params['organizationusers'])) {
            $sql.=" AND lc.organization IN ({$params['organizationusers']})";
        }

        if ($type=='add') {
            if ($profile->targetaudience == 1) {
               if(is_siteadmin() || !has_capability('local/organization:manage_organizationofficial',$context))
                {
                   
                     $sql .= " AND (lc.nationality = 'SA' OR lc.nationality = 113) ";
                }
               
            } elseif ($profile->targetaudience == 2) {
                $sql .= " AND (lc.nationality != 'SA' AND lc.nationality != 113) ";
            }
            $sql .= " AND u.id NOT IN (SELECT ee.userid
                                 FROM {exam_enrollments} AS ee
                                 WHERE examid = {$exam->id} )";
        }elseif ($type=='remove') {
            $sql .= " AND u.id IN (SELECT ee.userid
                                 FROM {exam_enrollments} AS ee
                                 WHERE ee.examid=$exam->id AND ee.profileid = {$params['profileid']} AND ee.enrolstatus = 1";
            if (!empty($params['scheduleid'])) {
                $sql .= " AND ee.hallscheduleid = {$params['scheduleid']} ";                
            }


            if(!is_siteadmin() && has_capability('local/organization:manage_organizationofficial',$context)) {
                $get_all_orgofficials = (new \local_exams\local\exams())->get_all_orgofficials($USER->id);
                $orgusers = implode(',',$get_all_orgofficials);
                $user = " AND ee.usercreated IN($orgusers) ";
                // $user = " AND ee.usercreated = {$USER->id} ";
            }
            $user .= ")";
        }

        $availableusers = $DB->get_records_sql_menu($sql .$where . $user, null, 0, 250);
    
        return $availableusers;
    }

     public function exam_enrolled_users_count($type = null, $profileid = 0, $params){
        global $DB, $USER;
        $context = context_system::instance();
        $profile = $DB->get_record('local_exam_profiles', ['id' => $params['profileid']]);
        $exam = $DB->get_record('local_exams', ['id' => $profile->examid]);
        $courseid = $exam->courseid;
        $traineerole= $DB->get_field('role', 'id', array('shortname' => 'trainee'));
        $where = '';
        $sql = "SELECT count(lc.userid) as total";
        $sql.=" FROM {local_users} AS lc 
                JOIN {role_assignments} AS roa ON roa.userid = lc.userid
                JOIN {course} as c ON c.id=$courseid
                WHERE  lc.userid > 2 AND lc.deleted = 0  AND roa.roleid = $traineerole AND roa.contextid = $context->id ";
        if ($params['orgid']) {
            $where = " AND lc.organization = ".$params['orgid'];
        }
        if(!is_siteadmin() && has_capability('local/organization:manage_organizationofficial',$context)) {
            $organization = $DB->get_field('local_users','organization',array('userid'=>$USER->id));
            $sql.= " AND  
            CASE
                WHEN  lc.organization >0 THEN lc.organization = $organization
                ELSE lc.organization <> 0
            END  ";
        }
        $sql .=" AND lc.userid <> $USER->id ";

        if (!empty($params['query'])) {
            $search = trim($params['query']);
            $sql .= " AND (lc.firstname LIKE '%$search%' OR 
                        lc.lastname LIKE '%$search%' OR
                        lc.firstnamearabic LIKE '%$search%' OR
                        lc.lastnamearabic LIKE '%$search%' OR
                        lc.middlenameen LIKE '%$search%' OR
                        lc.middlenamearabic LIKE '%$search%' OR
                        lc.thirdnameen LIKE '%$search%' OR 
                        lc.thirdnamearabic LIKE '%$search%' OR 
                        lc.email LIKE '%$search%' OR 
                        lc.id_number LIKE '%$search%') ";
        }
        if (!empty($params['email'])) {
             $sql.=" AND lc.userid IN ({$params['email']})";
        }
        if (!empty($params['organizationusers'])) {
            $sql.=" AND lc.organization IN ({$params['organizationusers']})";
        }
        if ($type=='add') {
            if ($profile->targetaudience == 1) {
               if(is_siteadmin() || !has_capability('local/organization:manage_organizationofficial',$context)){
                    $sql .= " AND (lc.nationality = 'SA' OR lc.nationality = 113) ";
                }
            } elseif ($profile->targetaudience == 2) {
                $sql .= " AND (lc.nationality != 'SA' AND lc.nationality != 113) ";
            }
            $sql .= " AND lc.userid NOT IN (SELECT ee.userid
                                 FROM {exam_enrollments} AS ee
                                 WHERE examid = {$exam->id} )";
        }elseif ($type=='remove') {
            $sql .= " AND lc.userid IN (SELECT ee.userid
                                 FROM {exam_enrollments} AS ee
                                 WHERE ee.examid=$exam->id AND ee.profileid = {$params['profileid']} AND ee.enrolstatus = 1";
            if (!empty($params['scheduleid'])) {
                $sql .= " AND ee.hallscheduleid = {$params['scheduleid']} ";                
            }

            if(!is_siteadmin() && has_capability('local/organization:manage_organizationofficial',$context)) {
                $get_all_orgofficials = (new \local_exams\local\exams())->get_all_orgofficials($USER->id);
                $orgusers = implode(',',$get_all_orgofficials);
                $user = " AND ee.usercreated IN($orgusers) ";
               // $user = " AND ee.usercreated = {$USER->id} ";
            }
            $user .= ")";
        }
        $availableuserscount = $DB->count_records_sql($sql.$where.$user);

        return $availableuserscount;
    }
    public function exam_enrollmet($profileid, $userid, $hallscheduleid, $enrolmenttype = false, $usercreated = false, $orgorderid=false,$productid=false,$organization=false,$tandcconfirm = null) {
        global $DB,$USER;
        if(empty($userid)) {
            $userid = $USER->id;
        }
        $systemcontext = context_system::instance();

        $currentuser = ($usercreated) ? $usercreated : $USER->id;
        $examid = $DB->get_field('local_exam_profiles', 'examid', ['id' => $profileid]);
        $id = $DB->get_field('exam_enrollments', 'id', ['examid' => $examid, 'userid' => $userid]);
        $profile = $DB->get_record('local_exam_profiles', ['id' => $profileid]);

        $sql = "SELECT lep.id, le.id as examid, le.courseid, le.sellingprice as examprice
                  FROM {local_exam_profiles} lep
                  JOIN {local_exams} le ON le.id = lep.examid
                 WHERE lep.id =".$profileid;
        $exam = $DB->get_record_sql($sql);
       
        $exam_record = $DB->get_record_sql("SELECT ex.*, ep.profilecode examcode 
                                            FROM {local_exams} ex 
                                            JOIN {local_exam_profiles} ep ON ex.id = ep.examid 
                                            WHERE ex.id = '$examid' AND ep.id = '$profileid' ");
        $availableseats = $this->availableseats($profileid);
        $timestart = $course->startdate;
        $timeend = 0;
        if ($timestart==''){
          $timestart=0;
        }
        $manual = enrol_get_plugin('manual');
        $roleid=$DB->get_field('role','id',array('shortname'=>'trainee'));
        $instance = $DB->get_record('enrol', array('courseid' => $exam->courseid, 'enrol' => 'manual'), '*', MUST_EXIST);
        $manual->enrol_user($instance, $userid,$roleid,$timestart, $timeend);

        $sql = "SELECT grop.id 
                  FROM {groups} as grop
                  JOIN {local_exam_profiles} as lep ON REPLACE(lep.profilecode,' ','') = grop.idnumber
                 WHERE lep.id =".$profileid;

        $group = $DB->get_field_sql($sql);

        $groupid = (int) $group;

        if ($groupid) {
            groups_add_member($groupid, $userid,null,0);
        }

        $row = array();
        $row['examid'] = $exam->examid;
        $row['organization'] =($organization) ?  $organization : 0;
        $row['courseid'] = $exam->courseid;
        if (!empty($productid)) {
            $row['productid'] = $productid;
        }
        $row['userid'] = $userid;
        $row['realuser'] =($USER->realuser > 0) ? $USER->realuser :0;
        $row['timecreated'] = time();
        $row['usercreated'] = $currentuser;
        $row['profileid'] = $profileid;
        $schedulerecord = $DB->get_record('hallschedule', ['id' => $hallscheduleid]);
        $row['examdate'] = !empty($schedulerecord->startdate) ? $schedulerecord->startdate : 0;
        $row['hallscheduleid'] = $hallscheduleid;
        $row['usercreated'] = $USER->id;
        if($tandcconfirm != null && $tandcconfirm != '' && !empty($tandcconfirm) ){
            $row['tandcconfirm'] = $tandcconfirm;
         
        }
        $row['enrolltype'] =($enrolmenttype == 'bulkenrollment') ? 1 : 0;
        $autoapproval = self::autoapproval();
        if($enrolmenttype == 'bulkenrollment'){
            $row['enrolstatus'] = 0;
        } 
        $orderid = $DB->get_field('tool_org_order_seats', 'id', ['tablename' => 'local_exam_profiles', 'orguserid' => $currentuser, 'fieldid' => $profileid]);
        if ($orgorderid && (!is_siteadmin() && has_capability('local/organization:manage_organizationofficial',$systemcontext))) {
            $row['orderid'] = $orgorderid;
            $row['enrolstatus'] = $autoapproval;
        }
        $row['enroluser'] = $autoapproval;
        if (!empty($id)) {
            $row['timemodified'] = time();
            $row['usermodified'] = $USER->id;
            $userschedule = $this->user_scheduledata($profile, false, $userid);


            $oldexamdate = $DB->get_record('local_exam_userhallschedules',array('id'=>$userschedule->id));
            $pasthallcode = $DB->get_field_sql("SELECT h.code FROM {hallschedule} as hs 
                                              JOIN {hall} as h ON hs.hallid=h.id where hs.id=:hallscheduleid",array('hallscheduleid'=> $oldexamdate->hallscheduleid));
            $pastdate = ($oldexamdate->examdate != '' && !empty($oldexamdate->examdate) ) ? userdate($oldexamdate->examdate, '%d-%m-%Y') : 0;
            $starttime = ($oldexamdate->hallscheduleid != '' && !empty($oldexamdate->hallscheduleid)) ?  $DB->get_field('hallschedule','starttime',array('id'=> $oldexamdate->hallscheduleid)) : 0;
            $pastexamtime = ($starttime != 0) ? userdate($starttime, get_string('strftimetime24', 'langconfig')) : 0;
            try{
                $DB->update_record('exam_enrollments', ['id' => $id, 'examdate' => $row['examdate'], 'hallscheduleid' => $hallscheduleid]);
                $attempsnumber = $this->quiz_attemptscount($exam->examid);
                $userschedule = $this->user_scheduledata($profile, $attempsnumber, $userid);
                $product = $DB->get_record('tool_products', ['id'=>$productid]);
                $row['referenceid'] = $product->referenceid;
                if ($product->category == 6) {
                    $attemptrecord = $DB->get_record('local_exam_attempts', ['id'=>$product->referenceid]);
                    if ($attemptrecord->attemptid == 1) {
                        $attemptid = 0;
                    } else {
                        $attemptid = $attemptrecord->id;
                    }
                    $row['attemptid'] = $attemptid;
                    $row['referencetable'] = 'local_exam_attempts';
                } else {
                    $attemptid = 0;
                }
                $sql = "SELECT id 
                        FROM {local_exam_userhallschedules} 
                        WHERE examid = $exam->examid AND userid = $userid AND attemptid= $attemptid AND FROM_UNIXTIME(timecreated, '%Y')=YEAR(CURDATE())";
                $examscheduleid = $DB->get_field_sql($sql);
                if (!empty($examscheduleid)) {
                    $row['id'] = $userschedule->id;
                    $record= $DB->update_record('local_exam_userhallschedules', $row);
                } else {
                    $attempt = $DB->record_exists_sql("SELECT id FROM {local_exam_attemptpurchases} WHERE userid = $USER->id AND referenceid = ". $product->referenceid);
                    if ($product->category == 6 && empty($attempt)) {
                        $DB->insert_record('local_exam_attemptpurchases', $row);
                        $record= $DB->insert_record('local_exam_userhallschedules', $row);
                        $DB->update_record('exam_enrollments', ['id' => $id, 'examdate' => $row['examdate'], 'hallscheduleid' => $hallscheduleid]);
                    }
                }

            } catch(moodle_exception $e){
                print_r($e);
            }

            $eventparams = array('context' => context_system::instance(),
                                'objectid'=>$row['id'],
                                'other'=>array('category' => 2,
                                                'entityid' => $row['profileid'],    // profile id
                                                'examdate' => $row['examdate'],
                                                'userid' => $row['userid'],
                                                'hallscheduleid' => $row['hallscheduleid'])
                                );
            $event = \local_exams\event\trainee_schedules::create($eventparams);// ... code that may add some record snapshots
            $event->trigger();

            $newexamdate = $DB->get_record('local_exam_userhallschedules',array('id'=>$userschedule->id));  
            $examstarttime = $DB->get_field('hallschedule','starttime',array('id'=> $newexamdate->hallscheduleid));
            $presentexamtime = userdate($examstarttime , get_string('strftimetime24', 'langconfig'));

            if($enrolmenttype == 'reschedule'){
                if($pastdate != 0 && $pastexamtime != 0){
                $notificationdetails = $DB->get_record('local_exams',array('id'=>$newexamdate->examid));
                $localuserrecord = $DB->get_record('local_users',['userid'=> $userid]);
                $fname = ($localuserrecord)? (($localuserrecord->lang == 'ar') ? $localuserrecord->firstnamearabic.' '.$localuserrecord->middlenamearabic.' '.$localuserrecord->thirdnamearabic.' '.$localuserrecord->lastnamearabic  : $localuserrecord->firstname.' '.$localuserrecord->middlenameen.' '.$localuserrecord->thirdnameen.' '.$localuserrecord->lastname) :fullname($DB->get_record('user',array('id'=>$userid)));
                $notificationdetails->exam_userfullname = $localuserrecord->firstname.' '.$localuserrecord->middlenameen.' '.$localuserrecord->thirdnameen.' '.$localuserrecord->lastname;
                $notificationdetails->exam_arabicuserfullname =  $fname;

                $notificationdetails->pastexam_date = $pastdate ;
                $notificationdetails->pastexam_time =   $pastexamtime;               
                $notificationdetails->presentexam_date = userdate($newexamdate->examdate, '%d-%m-%Y');
                $notificationdetails->presentexam_time = $presentexamtime; 
                $notificationdetails->exam_name =  $notificationdetails->exam;
                $notificationdetails->arabicexam_name = $notificationdetails->examnamearabic;
                $trainee = $DB->get_record('user',array('id'=>$newexamdate->userid));                

                (new \local_exams\notification)->exams_notification('exam_reschedule', $touser=$trainee,$fromuser= $USER, $notificationdetails,$waitinglistid=0);
                }
                $event = \local_exams\event\exam_reschedule::create(
                    array( 
                        'context'=>$systemcontext,
                        'relateduserid'=>$currentuser,
                        'objectid' =>$exam->examid,
                        'other'=>array(
                            'Message'=>'Reschedule In Exam'    
                        )
                        )
                    );  
                $event->trigger();
            }
            //$attemptid = $DB->get_record_select('local_exam_userhallschedules'," attemptid > 0 AND userid = $currentuser AND examid = $exam->examid AND profileid =$profileid AND hallscheduleid = $hallscheduleid ORDER BY id DESC"); 
           
            return  true;
        }
        // $row['orderid'] = 0;
        try{
            $record = $DB->insert_record('exam_enrollments', $row);
            $record = $DB->insert_record('local_exam_userhallschedules', $row);  
        } catch(moodle_exception $e){
            print_r($e);
        }


        if (($row['hallscheduleid']!=0 && !has_capability('local/organization:manage_organizationofficial',$systemcontext)&& $enrolmenttype !='bulkenrollment')|| (is_siteadmin() && $enrolmenttype !='bulkenrollment' && $row['hallscheduleid']!=0)) {
             
            $exam_record = $DB->get_record_sql("SELECT ex.*, ep.profilecode examcode 
                                                                FROM {local_exams} ex 
                                                                JOIN {local_exam_profiles} ep ON ex.id = ep.examid 
                                                                WHERE ex.id = $exam->examid AND ep.id = $profileid ");
            $notification = (new \local_exams\local\exams)->exams_enrol_notification($exam_record, $userid, $row['hallscheduleid']);
        } elseif ($row['hallscheduleid']!=0 && has_capability('local/organization:manage_organizationofficial',$systemcontext) && !is_siteadmin() && $enrolmenttype !='bulkenrollment') {
            if ($autoapproval == 1){
           
            $exam_record = $DB->get_record_sql("SELECT ex.*, ep.profilecode examcode 
                                                                FROM {local_exams} ex 
                                                                JOIN {local_exam_profiles} ep ON ex.id = ep.examid 
                                                                WHERE ex.id = $exam->examid AND ep.id = $profileid ");
            $notification = (new \local_exams\local\exams)->exams_enrol_notification($exam_record, $userid, $row['hallscheduleid']);
            }
       
        }


        if(empty($orderid) && has_capability('local/organization:manage_organizationofficial',$systemcontext)) {
            $row['productid'] = $DB->get_field('tool_products', 'id', ['referenceid' => $row['profileid'], 'category' => 2]);
            $row['fieldid'] = $row['profileid'];
            $row['tablename'] = 'local_exam_profiles';
            $row['orguserid'] = $currentuser;
            $row['fieldname'] = 'id';
            $row['purchasedseats'] = 0;
            $row['approvalseats'] = 0;

            $enrolledcount = $DB->get_field('exam_enrollments', 'COUNT(id)', ['examid' => $profile->examid, 'profileid' => $profileid]);
            $totalenrolled = $profile->seatingcapacity -  $enrolledcount;
            $row['availableseats'] = $totalenrolled;
            $record= $DB->insert_record('tool_org_order_seats', $row);
        }
        $eventparams = array('context' => \context_system::instance(),
                            'objectid'=>$record->id,
                            'other'=>array('category' => 2,
                                            'entityid' => $row['profileid'],    // profile id
                                            'examdate' => $row['examdate'],
                                            'userid' => $row['userid'],
                                            'hallscheduleid' => $row['hallscheduleid'])
                        );

        $event = \local_exams\event\trainee_schedules::create($eventparams);// ... code that may add some record snapshots
        $event->trigger();
        if($enrolmenttype == 'replace'){
                      
            $examenrolled = $DB->get_record('local_exams',array('id'=>$exam->examid));         
            $localuserrecord = $DB->get_record('local_users',['userid'=> $userid]);
            $fname = ($localuserrecord)? (( $localuserrecord->lang == 'ar') ? $localuserrecord->firstnamearabic.' '.$localuserrecord->middlenamearabic.' '.$localuserrecord->thirdnamearabic.' '.$localuserrecord->lastnamearabic  : $localuserrecord->firstname.' '.$localuserrecord->middlenameen.' '.$localuserrecord->thirdnameen.' '.$localuserrecord->lastname) :fullname($DB->get_record('user',array('id'=>$userid)));
            $examenrolled->exam_userfullname = $localuserrecord->firstname.' '.$localuserrecord->middlenameen.' '.$localuserrecord->thirdnameen.' '.$localuserrecord->lastname;
            $examenrolled->exam_arabicuserfullname =  $fname; 
            $examenrolled->exam_name = $examenrolled->exam;
            $examenrolled->exam_arabicname = $examenrolled->examnamearabic;
            $examenrolled->exam_date = date('d-m-Y');
            $examenrolled->exam_time = date("H:i:s", mktime(0,0, $exam->slotstart) );
            $trainee=$DB->get_record('user',array('id'=>$userid));         
            (new \local_exams\notification)->exams_notification('exams_enrolment', $touser=$trainee,$fromuser = get_admin(), $examenrolled,$waitinglistid=0);
            $event = \local_exams\event\exam_replace::create(
                array( 
                    'context'=>$systemcontext,
                    'relateduserid'=>$currentuser,
                    'objectid' =>$record->id,
                    'other'=>array(
                        'Message'=>'Replacement In Exam'    
                    )
                    )
                );  
            $event->trigger();  
                  
        }           
                 
        $event =  \local_exams\event\exam_enrolled::create(array( 'context'=>$systemcontext, 'objectid' =>$exam->examid));
        $event->trigger();
            
        
        if( $availableseats && $exam->examprice > 0 && $enrolmenttype !='cancel' && $enrolmenttype !='replace' ) {
            (new \tool_product\product)->upadte_availableseats('local_exam_profiles', 'id', $profileid, -1, $currentuser);
        }
        return $record;
    }
    public function exam_unenrollmet($profileid, $userid,$examtype=false) {
        global $DB,$USER;
        $systemcontext = context_system::instance();
        $sql = "SELECT le.id, le.courseid 
                  FROM {local_exams} le 
                  JOIN {local_exam_profiles} lep ON lep.examid = le.id 
                 WHERE lep.id=".$profileid;
        $exam = $DB->get_record_sql($sql);

        $course = $DB->get_record('course', ['id' => $exam->courseid], '*', MUST_EXIST);
        $manual = enrol_get_plugin('manual');
        $instance = $DB->get_record('enrol', array('courseid' => $exam->courseid, 'enrol' => 'manual'), '*', MUST_EXIST);
        $enrolledbyuserid = self::enrolledbyuser($exam->id, $userid, $profileid, $examtype);

        try{
            $transaction = $DB->start_delegated_transaction();
            $status = $manual->unenrol_user($instance, $userid);                                             
            $record=$DB->delete_records('exam_enrollments',array('examid'=>$exam->id,'courseid'=>$exam->courseid,'userid'=>$userid));
            $DB->delete_records('local_exam_userhallschedules',array('examid'=>$exam->id,'profileid'=>$profileid,'userid'=>$userid));
            $DB->delete_records('exam_completions',array('examid'=>$exam->id, 'userid'=>$userid));
            $transaction->allow_commit();

        } catch(moodle_exception $e){
            $transaction->rollback($e);
            return false;
        }

        if ($examtype == 'cancel'){
            $localuserrecord = $DB->get_record('local_users',['userid'=> $userid]);
            $fname = ($localuserrecord)? (($localuserrecord->lang == 'ar') ? $localuserrecord->firstnamearabic.' '.$localuserrecord->middlenamearabic.' '.$localuserrecord->thirdnamearabic.' '.$localuserrecord->lastnamearabic  : $localuserrecord->firstname.' '.$localuserrecord->middlenameen.' '.$localuserrecord->thirdnameen.' '.$localuserrecord->lastname) :fullname($DB->get_record('user',array('id'=>$userid)));
            $examrecord = $DB->get_record('local_exams',array('id'=>$exam->id));
            $examrecord->exam_userfullname = $localuserrecord->firstname.' '.$localuserrecord->middlenameen.' '.$localuserrecord->thirdnameen.' '.$localuserrecord->lastname;
            $examrecord->exam_arabicuserfullname =  $fname;  
            $examrecord->exam_arabicname =  $examrecord->examnamearabic;
            $examrecord->exam_name =  $examrecord->exam;
            $examrecord->exam_date = date('d-m-Y');
            $examrecord->exam_time = date("H:i:s",time());    
            $trainee=$DB->get_record('user',array('id'=>$userid));             
            (new \local_exams\notification)->exams_notification('exam_unenroll', $touser=$trainee,$fromuser = get_admin(), $examrecord,$waitinglistid=0);

            $event = \local_exams\event\exam_cancel::create(
                array( 
                    'context'=>$systemcontext,
                    'relateduserid'=>$userid,
                    'objectid' =>$exam->examid,
                    'other'=>array(
                        'Message'=>'Cancellation of Exam'    
                    )
                    )
                );  
            $event->trigger();  


        }
      

        return $record;
    }   

    public function enrolledbyuser($examid, $userid, $profileid, $type=false)
    {
        global $DB;
        $usercreatedid = $DB->get_field('exam_enrollments', 'usercreated', ['examid' => $examid, 'userid' => $userid]);
        if($usercreatedid) {
            $roleid = $DB->record_exists_sql("SELECT ra.id FROM {role_assignments} ra JOIN {role} r ON r.id = ra.roleid WHERE r.shortname = 'organizationofficial' AND ra.userid =".$usercreatedid );
        }
        if($roleid && $type !='cancel' && $type !='replace') {
            (new \tool_product\product)->upadte_availableseats('local_exam_profiles', 'id', $profileid, +1, $usercreatedid);
        }
        return false;
    }

    public function examfields($field, $fielddata) {
        global $DB;
        $lang= current_language();
        if($field == 'sector') {

            if( $lang == 'ar'){
                $field = $DB->get_records_sql_menu("SELECT id, titlearabic as title FROM {local_sector}");
            } else{
                $field = $DB->get_records_sql_menu("SELECT id, title FROM {local_sector}");
            }

        } elseif($field == 'type') {
            $field = ['1' => 'Professional test', '2' => 'other'];
        } elseif($field == 'programs') {
            $field = ['1' => 'Venture capital and investment in start-ups', '2' => 'Work ethics'];
        } elseif($field == 'examprice') {
            $field = ['0' => get_string('complimentary','local_exams'), '1' => get_string('paid','local_exams')];
        } elseif($field == 'language') {
            $field = ['arabic' => get_string('arabic', 'local_exams'), 'english' => get_string('english', 'local_exams')];            
            // $field = ['1' => 'Arabic', '2' => 'English'];
        } elseif($field == 'status') {
            $field = ['0' => 'Publish', '1' => 'Published'];
        } elseif($field == 'compentency') {
            $field = $DB->get_records_sql_menu("SELECT id, name FROM {local_competencies}");
        } elseif($field == 'city') {
            $field = ['1' => 'Riyadh'];
        }
        $fieldvalues = explode(',', $fielddata);
        foreach ($fieldvalues AS $fieldvalue) {
            $row[] = $field[$fieldvalue];
        }
        return implode(',', $row);
    }
    public function examsdetails($examid,$profileid=false) {
        global $DB, $PAGE, $OUTPUT, $USER;
        $systemcontext = context_system::instance();
        $PAGE->set_context($systemcontext);
        $renderer = $PAGE->get_renderer('local_exams');
        if($examid > 0) {
            $exams = $this->examdetails($examid);
            if ($profileid) {
                $profile = $this->examprofile($profileid);
                $exams['instructions'] = $profile['instructions'];
                $exams['nondisclosure'] = $profile['nondisclosure'];                
            }
            $renderer->examdetails($exams);
        }
        if((!is_siteadmin() && has_capability('local/organization:manage_trainee',$systemcontext))) {
            $userattempts = $this->userattemptsinfo($exams['id']);
        }
        $renderer = $PAGE->get_renderer('local_exams');
        $filterparams  = $renderer->get_catalog_profiles(true);
        $filterparams['submitid'] = 'form#filteringform';
        $filterparams['widthclass'] = 'col-md-12';
        $filterparams['globalinput'] = $renderer->global_filter($filterparams);
        $filterparams['halldetails'] = $renderer->get_catalog_profiles();
        $filterparams['q'] = $searchquery;
        
        $filterparams['inputclasses'] = 'examssearchinput';
        $filterparams['examid'] = $examid;
        $filterparams['profileid'] = $profileid;
        $filterparams['placeholder'] = get_string('search_skill','local_exams');
        $filterparams['examdetailsactions'] = true;
        $filterparams['examattempts'] = false;
        $filterparams['actionaccess'] = (is_siteadmin()|| has_capability('local/organization:manage_examofficial',$systemcontext)) ? true : false;
        if((is_siteadmin()|| has_capability('local/organization:manage_examofficial',$systemcontext) || has_capability('local/exams:veiw_exam_details',$systemcontext))) {
            echo $renderer->listofpublishedexams($filterparams);
        } else {
            echo $renderer->listofreservations($filterparams);
        }
       
    }
    public function userattemptsinfo($examid)
    {
        global $DB, $USER, $PAGE;
        $renderer = $PAGE->get_renderer('local_exams');
        $sql = "SELECT * 
                FROM {local_exam_userhallschedules} as leuh 
                WHERE leuh.examid = {$examid} AND leuh.userid=".$USER->id;
        $records = $DB->get_records_sql($sql);

        $userscount = $DB->count_records('local_exam_userhallschedules', array('examid'=>$examid, 'userid' => $USER->id));
        $data = [];
        $count = 0;
        foreach ($records as $record) {
            $userscount = ++$count;
            $row = [];
            if ($record->attemptid == 0) {
                $row['attempt'] = 1;
            } else {
                $examattemptid = $DB->get_field('local_exam_attempts', 'attemptid', ['id'=>$record->attemptid]);
                $row['attempt'] = $examattemptid;
            }
            $profile = $DB->get_record('local_exam_profiles', ['id'=>$record->profileid]);
            $row['profilecode'] = $profile->profilecode;
            $scheduledetails = $DB->get_record_sql('SELECT h.name as hallname, hs.starttime, hs.endtime, leu.examdate
                                          FROM {hall} as h  
                                          JOIN {hallschedule} as hs ON hs.hallid = h.id
                                          JOIN {local_exam_userhallschedules} as leu ON leu.hallscheduleid=hs.id
                                          WHERE leu.id=:id', 
                                          ['id' => $record->id]);
            $row['hallname'] = format_string($scheduledetails->hallname);
            $schedulestarttime = $scheduledetails->starttime;

            $starttime = !empty($scheduledetails->examdate) ? userdate($scheduledetails->examdate, get_string('strftimedate','core_langconfig')).' '.userdate($schedulestarttime, get_string('strftimetime12', 'langconfig')) : '--';
            $endedtime = !empty($scheduledetails->examdate) ? userdate($scheduledetails->examdate, get_string('strftimedate','core_langconfig')).' '.userdate(($schedulestarttime+$profile->duration), get_string('strftimetime12', 'langconfig')) : '--'; 

            $row['timestart'] = $starttime;
            $row['timefinish'] = $endedtime;

            $sql = "SELECT *
                      FROM {local_exam_userhallschedules} 
                     WHERE examid = {$examid} AND userid = {$record->userid} 
                     ORDER BY id ASC ";
            $userschedules = $DB->get_records_sql($sql);
            $i=0;
            foreach($userschedules as $userschedule) {
                $userattempt = $userschedule->attemptid;
                $usercurrentattempt = $record->attemptid;
                $attempt = ++$i;
                if ($i == $userscount) {
                    break;
                }
            }

            $startedtime = (strtotime(userdate($scheduledetails->examdate, '%d-%m-%Y'))+userdate((($schedulestarttime)), '%H')*3600 + userdate(($schedulestarttime), '%M')*60);
            $sql = "SELECT qa.* 
                    FROM {quiz_attempts} qa 
                    WHERE qa.quiz = {$profile->quizid} AND qa.attempt = {$attempt} AND qa.userid =". $USER->id;
            $quizattempt = $DB->get_record_sql($sql);

            if ($quizattempt) {
                if($quizattempt->sumgrades == -1) {
                    $row['grade'] = get_string('absent', 'local_exams');
                } elseif($quizattempt->sumgrades == -2) {
                    $row['grade'] = get_string('unknow', 'local_exams');
                } else {
                    $row['grade'] = ROUND($quizattempt->sumgrades, 2);
                }
            } elseif(time() < $startedtime) {
                $row['grade'] = get_string('notstarted', 'local_exams');
            } else {
                $row['grade'] = get_string('unknow', 'local_exams');
            }

            $data[] = $row;
        }

        $renderer->userattempts($data);
        return $data;
       
    }
    public function gradestatus($scheduleid) {
        global $DB, $USER;
        $schedule = $DB->get_record('local_exam_userhallschedules', ['id'=>$scheduleid]);
        $profile = $DB->get_record('local_exam_profiles', ['id'=>$schedule->profileid]);
        if ($schedule->attemptid == 0) {
            $attempt = 1;
        } else {
            $examattemptid = $DB->get_field('local_exam_attempts', 'attemptid', ['id'=>$schedule->attemptid]);
            $attempt = $examattemptid;
        }       

        $scheduledetails = $DB->get_record('hallschedule', ['id'=>$schedule->hallscheduleid]);
        $schedulestarttime = $scheduledetails->starttime;
        $startedtime = (strtotime(userdate($schedule->examdate, '%d-%m-%Y'))+userdate((($schedulestarttime)), '%H')*3600 + userdate(($schedulestarttime), '%M')*60);

        $sql = "SELECT *
                  FROM {local_exam_userhallschedules} 
                 WHERE examid = {$schedule->examid} AND userid = {$schedule->userid} 
                 ORDER BY id ASC ";
        $userschedules = $DB->get_records_sql($sql);
        $i=0;
        foreach($userschedules as $userschedule) {
            $userattempt = $userschedule->attemptid;
            $usercurrentattempt = $schedule->attemptid;
            $attempt = ++$i;
            if ($userattempt == $usercurrentattempt) {
                break;
            }
        }

        if($profile->quizid) {

            $sql = "SELECT qa.* 
            FROM {quiz_attempts} qa 
            WHERE qa.quiz = {$profile->quizid} AND qa.attempt = {$attempt} AND qa.userid =". $schedule->userid;
            $quizattempt = $DB->get_record_sql($sql);

            if ($quizattempt) {
                if($quizattempt->sumgrades == -1) {
                    $gradestatus = get_string('absent', 'local_exams');
                } elseif($quizattempt->sumgrades == -2) {
                    $gradestatus = get_string('unknow', 'local_exams');
                } else {
                    $gradestatus = ROUND($quizattempt->sumgrades, 2);
                }
            } elseif(time() < $startedtime) {
                $gradestatus = get_string('notstarted', 'local_exams');
            } else {
                $gradestatus = get_string('unknow', 'local_exams');
            }
            return $gradestatus;
            
        }
     
       
    }
    public function scheduledates($scheduleid) {
        global $DB;
        $sql = "SELECT * 
                FROM {hallschedule} hs 
                WHERE hs.id = ".$scheduleid;
        $schedule = $DB->get_record('hallschedule', ['id'=>$scheduleid]);


        $schedulestarttime = $schedule->starttime;
        $scheduleendtime = $schedule->endtime;

        $starttime = (userdate((($schedulestarttime)), '%H')*3600 + userdate(($schedulestarttime), '%M')*60);
        $endtime = (userdate((($scheduleendtime)), '%H')*3600 + userdate(($scheduleendtime), '%M')*60);
        $date = strtotime(userdate($schedule->startdate, '%d-%m-%Y'));

        return COMPACT('starttime', 'endtime', 'date');
    }
    public function examreservations($examid) {
        global $DB, $PAGE, $OUTPUT, $USER;
        $systemcontext = context_system::instance();
        $PAGE->set_context($systemcontext);
        $renderer = $PAGE->get_renderer('local_exams');
        $examreservations = $this->get_examreservations($examid,array('examid'=>$examid));
        $renderer->examreservations($examreservations);
    }

    public function update_compentecies($data) {
        global $DB;
        $row['id'] = $data->id;
        $row['competencies'] = implode(',', $data->competencies);
        return $DB->update_record('local_exams', $row);
    }
    public function competencies_info($examid) {
        global $DB, $PAGE;
        $competencies = $DB->get_field('local_exams', 'competencies', ['id' => $examid]);
        $fieldvalues = explode(',', $competencies);
        foreach ($fieldvalues AS $fieldvalue) {
            $row[] = $DB->get_record('local_competencies', ['id' => $fieldvalue]);
        }
        $systemcontext = context_system::instance();
        $PAGE->set_context($systemcontext);
        $renderer = $PAGE->get_renderer('local_exams');
        return $renderer->competencydetails($row);
    }
    public function competency_info($stable, $filterdata) {
        global $DB, $PAGE, $OUTPUT;
        $systemcontext = context_system::instance();

        $competencies =  $DB->get_field('local_exams', 'competencies', ['id' =>$stable->typeid]);
        if(empty($competencies)) {
            $addedcompetencies = [];
            $totalcompetency = 0;
        } else {
            /*$selectsql = "SELECT * FROM {local_competencies} le WHERE 1=1 AND id IN ($competencies) "; 
            $countsql  = "SELECT COUNT(le.id) FROM {local_competencies} le WHERE 1=1 AND id IN ($competencies) ";
            if(isset($filterdata->search_query) && trim($filterdata->search_query) != ''){
                $formsql .= " AND le.name LIKE :search";
                $searchparams = array('search' => '%'.trim($filterdata->search_query).'%');
            }else{
                $searchparams = array();
            }     
            $params = array_merge($searchparams);
            $totalcompetency = $DB->count_records_sql($countsql.$formsql, $params);
            $formsql .=" ORDER BY le.id DESC";
            $addedcompetencies = $DB->get_records_sql($selectsql.$formsql, $params, $stable->start,$stable->length);
            $coursesContext = array(
                "acompetencies" => $addedcompetencies,
                "nocourses" => $nocourse,
                "totalcount" => $totalcompetency,
                "length" => $totalcompetency
            );
            return $coursesContext;*/

            $existingcompletencies = explode(',' ,$competencies);
            $existingcompletencies = array_filter($existingcompletencies);
            $competencylist = implode(',', $existingcompletencies);

            $lang = current_language();
            if( $lang == 'ar'){

               $selectsql ="SELECT le.id, le.arabicname AS competencyname, le.type,le.code FROM {local_competencies} as le WHERE 1=1 AND le.id IN ($competencylist)" ;
                   
            } else {

                $selectsql ="SELECT le.id, le.name AS competencyname, le.type,le.code FROM {local_competencies} as le WHERE 1=1 AND le.id IN ($competencylist)" ;

            }
            $countsql  = "SELECT COUNT(le.id) FROM {local_competencies} le WHERE 1=1 AND id IN ($competencylist) ";
            if(isset($filterdata->search_query) && trim($filterdata->search_query) != ''){
                $formsql .= " AND le.name LIKE :search";
                $searchparams = array('search' => '%'.trim($filterdata->search_query).'%');
            }else{
                $searchparams = array();
            }     
            $params = array_merge($searchparams);
            $totalcompetency = $DB->count_records_sql($countsql.$formsql, $params);
            $formsql .=" ORDER BY le.id DESC";
            $addedcompetencies = $DB->get_records_sql($selectsql.$formsql, $params, $stable->start,$stable->length);

            $constcompetencytypes = tp::constcompetency_types();
         
            foreach ($addedcompetencies AS $competenciestype) {

                $competenciestype->fullname = $constcompetencytypes[$competenciestype->type];

            }
        }    

        $coursesContext = array(
            "acompetencies" => $addedcompetencies,
            "nocourses" => $nocourse,
            "totalcount" => $totalcompetency,
            "length" => $totalcompetency
        );        
        return $coursesContext;
    }
    public static function get_questionbanks_exams($stable,$filterdata=null) {
        global $DB, $USER,$CFG;
        require_once($CFG->dirroot . '/mod/quiz/locallib.php');
        require_once($CFG->dirroot . '/question/editlib.php');
        $params = array();
        $questionbanks = array();
        $questionbankscount = 0;
        $mappedquestionbankscount = 0;
        $concatsql = '';

        if (!empty($filterdata->search_query)) {
            $fields = array(
                "qc.name"
            );
            $fields = implode(" LIKE :search1 OR ", $fields);
            $fields .= " LIKE :search2 ";
            $params['search1'] = '%' . $filterdata->search_query . '%';
            $concatsql .= " AND ($fields) ";
        }
        if (isset($stable->examid) && $stable->examid > 0 && $stable->quizid) {
            $sql = "SELECT cm.id
                    FROM {course_modules} cm
                    JOIN {modules} mo ON mo.id = cm.module
                    JOIN {quiz} m ON m.id = cm.instance
                    WHERE mo.name=:modulename AND
                    m.id=:moduleid";
            $cmparams = array('modulename' =>'quiz', 'moduleid' => $stable->quizid);
            $cmid = $DB->get_field_sql($sql, $cmparams);
            $quizcontextid=\context_module::instance($cmid);
            if($cmid){

                $countsql = "SELECT COUNT(qc.id) ";
                $fromsql = "SELECT  qc.*, qc.id as quesionbankid,
                            (SELECT COUNT(qbe.id) FROM {question_bank_entries} qbe 
                                    JOIN {question_versions} qv ON qbe.id = qv.questionbankentryid 
                                    JOIN {question} q ON qv.questionid = q.id 
                                    WHERE qbe.questioncategoryid = qc.id 
                             AND qv.version = (SELECT MAX(v.version) FROM {question_versions} v JOIN {question_bank_entries} be ON be.id = v.questionbankentryid WHERE be.id = qbe.id) 
                                    AND ((qv.status = 'ready'))) as catavilablequestions ";
                $sql = " FROM {question_categories} AS qc 
                WHERE (qc.parent IN (1, 2) AND qc.idnumber IS NULL) OR (qc.parent IN (1, 2) AND qc.idnumber NOT IN ('workshop_categories')) ";
                $sql .= $concatsql;

                try {
                    $questionbankscount = $DB->count_records_sql($countsql . $sql, $params);
                    if ($stable->thead == false) {
                        $sql .= " ORDER BY qc.id DESC";
                        $questionbanks = $DB->get_records_sql($fromsql . $sql, $params, $stable->start, $stable->length);
                        foreach($questionbanks as $questionbank){
                            $questionbank->quizcatavilablequestions=$DB->count_records_sql("SELECT COUNT(qsr.id) FROM {question_set_references} as qsr WHERE qsr.usingcontextid = $quizcontextid->id AND JSON_OBJECT(qsr.filtercondition,'questioncategoryid') LIKE '%:$questionbank->quesionbankid,%'");

                            $sql = "SELECT COUNT(q.id)
                                   FROM {question} q
                                   JOIN {question_versions} qv ON qv.questionid = q.id
                                   JOIN {question_bank_entries} qbe ON qbe.id = qv.questionbankentryid
                                  WHERE questioncategoryid = $questionbank->quesionbankid AND q.parent = '0'
                                     and qv.status = 'ready' 
                                        AND (
                                             (qv.version = (SELECT MAX(v.version)
                                                                FROM {question_versions} v
                                                                JOIN {question_bank_entries} be ON be.id = v.questionbankentryid
                                                               WHERE be.id = qbe.id)
                                               )
                                            )
                                        ";

                            $questionbank->noofquestions =  $DB->get_field_sql($sql);
                            $questionbank->totalnoofquestions = $questionbank->noofquestions;
                            $questionbank->mappednoofquestions = $questionbank->quizcatavilablequestions;
                            if($questionbank->catavilablequestions){
                                if($questionbank->noofquestions > $questionbank->catavilablequestions){
                                    $questionbank->totalnoofquestions = $questionbank->catavilablequestions;
                                }
                                if($questionbank->quizcatavilablequestions >= $questionbank->noofquestions){
                                    $questionbank->noofquestions = 0;
                                }elseif($questionbank->quizcatavilablequestions < $questionbank->noofquestions){
                                    $questionbank->noofquestions = $questionbank->totalnoofquestions - $questionbank->quizcatavilablequestions;
                                }
                            }else{
                                $questionbank->noofquestions=0;
                            }
                            $mappedquestionbankscount =$mappedquestionbankscount+$questionbank->mappednoofquestions;
                        }
                    }
                } catch (\dml_exception $ex) {
                    $questionbankscount = 0;
                    $mappedquestionbankscount = 0;
                }
            }
        }
        return compact('questionbanks', 'questionbankscount','mappedquestionbankscount');
    }    
    public function exam_qualification_info($searchquery='') {
        global $DB, $PAGE, $OUTPUT,$CFG;
        $systemcontext = context_system::instance();
        $PAGE->set_context($systemcontext);
        require_once($CFG->dirroot . '/local/exams/lib.php');
        $renderer = $PAGE->get_renderer('local_exams');
        $filterparams  = $renderer->get_catalog_exam_qualification(true);
        $filterparams['submitid'] = 'form#filteringform';
        $filterparams['globalinput'] = $renderer->global_filter($filterparams);
        $fform = exams_front_filters_form($filterparams);
        $filterparams['examdetails'] = $renderer->get_catalog_exam_qualification();
        $filterparams['q'] = $searchquery;
        $filterparams['widthclass'] = 'col-md-12';
        $filterparams['inputclasses'] = 'examssearchinput';
        $filterparams['placeholder'] = get_string('search_skill','local_exams');
        $filterparams['filterform'] = $fform->render();
        $renderer->listofexamqualification($filterparams);
    }
    public function get_listof_exam_qualification($stable, $filterdata) {
        global $DB, $PAGE, $OUTPUT, $CFG, $USER,$SESSION;
        $SESSION->lang = (empty($stable->isarabic)) ? current_language() : (($stable->isarabic == 'true')?'ar':'en') ;

        $systemcontext = context_system::instance();
        $PAGE->set_context($systemcontext);
        $selectsql = "SELECT DISTINCT le.id,le.* FROM {local_exams} le
                         JOIN {local_exam_profiles} lep ON le.id =lep.examid "; 

        $countsql  = "SELECT COUNT(DISTINCT le.id) FROM {local_exams} le
                         JOIN {local_exam_profiles} lep ON le.id = lep.examid  ";
        $formsql = "  WHERE 1=1 AND status = 1 ";

        if(isset($stable->query) && trim($stable->query) != ''){
            $formsql .= " AND (le.exam LIKE '%".trim($stable->query)."%' OR  le.examnamearabic LIKE '%".trim($stable->query)."%' OR  le.code LIKE '%".trim($stable->query)."%'  ) ";
        } else {
            $searchparams = array();
        }

        if(isset($filterdata->search_query) && trim($filterdata->search_query) != ''){
            $formsql .= " AND ( lep.profilecode LIKE '%".trim($filterdata->search_query)."%' OR le.exam LIKE '%".trim($filterdata->search_query)."%' OR  le.examnamearabic LIKE '%".trim($filterdata->search_query)."%' OR  le.code LIKE '%".trim($filterdata->search_query)."%'  ) ";
        } else {
            $searchparams = array();
        }

        // if($filterdata->{'examdatetime[enabled]'} == 1 ){
        //     $start_year = $filterdata->{'examdatetime[year]'};
        //     $start_month = $filterdata->{'examdatetime[month]'};
        //     $start_day = $filterdata->{'examdatetime[day]'};
        //     $filter_starttime_con = mktime(0,0,0, $start_month, $start_day, $start_year);
        //     $formsql.= " AND le.examdatetime>= '$filter_starttime_con' ";
        // }
        // if($filterdata->{'enddate[enabled]'} == 1 ){
        //     $start_year = $filterdata->{'enddate[year]'};
        //     $start_month = $filterdata->{'enddate[month]'};
        //     $start_day = $filterdata->{'enddate[day]'};
        //     $filter_endtime_con=mktime(23,59,59, $start_month, $start_day, $start_year);
        //     $formsql.=" AND le.enddate <= '$filter_endtime_con' ";
        // }
        if (!empty($filterdata->type) && $filterdata->type > 0){
            $formsql .= " AND le.type =$filterdata->type"; 
        }   
        if(!empty($filterdata->sectors)){
            $formsql.=" AND  le.sectors IN ($filterdata->sectors) ";
        } 

        if(!empty($stable->sectorids)){

            $sectors = str_replace(';', ',', $stable->sectorids);
            $sectorids = explode(',', $sectors);
            foreach ($sectorids as $sector) {
                $sectorquery[] = " CONCAT(',',le.sectors,',') LIKE CONCAT('%,',$sector,',%') "; 
            }
            $sectoqueeryparams =implode('OR',$sectorquery);
            $formsql .= ' AND ('.$sectoqueeryparams.') ';
            /* foreach ($sectorids AS $sector) {
                $formsql.=" AND FIND_IN_SET(".$sector.", le.sectors) ";
            }*/

        } 
        if(!empty($stable->competencyid)){
            $competencies = str_replace(';', ',', $stable->competencyid);
            $competencyids = explode(',', $competencies);
            foreach ($competencyids AS $competency) {
                $formsql.=" AND FIND_IN_SET(".$competency.", le.competencies) ";
            }
        }
        if(!empty($stable->jobfamilyids)) {
            $jobfamilyids = str_replace(';', ',', $stable->jobfamilyids);
            foreach ($jobfamilyids as $jobfamilyid) {
                $formsql.=" AND FIND_IN_SET(".$jobfamilyid.", le.targetgroup) ";
            }
        }
        if(!empty($stable->competencylevelid)){
            $formsql.=" AND le.clevels = 'level{$stable->competencylevelid}' ";
        }
        if (!empty($stable->query) && trim($stable->query) != ''){
            if ($SESSION->lang == 'ar') {
                $sql .= " AND (le.examnamearabic LIKE :firstnamesearch) ";
            } else {
                $sql .= " AND (le.exam LIKE :firstnamesearch) ";                
            }
            $searchparams = array('firstnamesearch' => '%'.trim($stable->query).'%');
        } else {
            $searchparams = array();
        }
        $params = array_merge($searchparams);
        $totalexams = $DB->count_records_sql($countsql.$formsql, $params);

        if (empty($stable->thead)) {
            $ordersql =" ORDER BY le.id DESC";
            $exams = $DB->get_records_sql($selectsql.$formsql.$ordersql, $params, $stable->start,$stable->length);
        } else {
            if($stable->isdescending == 'false' || empty($stable->isdescending)) {
                $formsql .=" ORDER BY le.id ASC ";
            } else {
                $formsql .=" ORDER BY le.id DESC";
            }
            $exams = $DB->get_records_sql($selectsql.$formsql, $params);
        }
        
        $examlist = array();
        $completedlist = array();
        $reviewexms = array();
        $count = 0;
        foreach($exams as $exam) {
            global $DB;
            if($exam->status==1){
                $reviewexms[$count]["id"] = $exam->id;
                $reviewexms[$count]["activityID"] = $exam->id;

                $existingcompletencies = explode(',' ,$exam->competencies);
                $existingcompletencies = array_filter($existingcompletencies);
                $competencylist = implode(',', $existingcompletencies);

                if ($SESSION->lang == 'ar') {

                    $reviewexms[$count]["activityType"] = 'امتحان';
                    $reviewexms[$count]["name"] = $DB->get_field('local_exams', 'examnamearabic', ['id' => $exam->id]);
                    $competencies =($exam->competencie) ? $DB->get_fieldset_sql("SELECT arabicname FROM {local_competencies} WHERE id IN ($competencylist) ") : '';
                } else {
                    $reviewexms[$count]["activityType"] = 'Exam';
                    $reviewexms[$count]["name"] = $DB->get_field('local_exams', 'exam', ['id' => $exam->id]);
                    $competencies =($exam->competencie) ? $DB->get_fieldset_sql("SELECT name FROM {local_competencies} WHERE id IN ($competencylist) ") : '';
                }

                $reviewexms[$count]["exam"] = $this->examname($exam->id);
                $reviewexms[$count]["actualprice"] = number_format($exam->actualprice,0,'.','');
                $reviewexms[$count]["sellingprice"] = number_format($exam->sellingprice,0,'.','');
                $reviewexms[$count]["examdescription"] = format_text($exam->programdescription, FORMAT_HTML);
                $reviewexms[$count]["description"] = strip_tags(format_text($exam->programdescription, FORMAT_HTML));
                
                $hall = $DB->get_record('hall',array('id'=> $exam->halladdress));

                $reviewexms[$count]["city"] = !empty($hall->city) ? 'Riyadh' : '--'; 
                $reviewexms[$count]["buildingname"] = $hall->buildingname;
                $reviewexms[$count]["seatingcapacity"] = $hall->seatingcapacity;  
                $reviewexms[$count]["hall"] = $hall->name;
                $reviewexms[$count]["location"] = $hall->maplocation;
                $reviewexms[$count]["detailsPageURL"] = $CFG->wwwroot.'/local/exams/exams_qualification_details.php?id='.$exam->id;
                $reviewexms[$count]["competencyLevelId"] =($exam->clevels) ? ((substr($exam->clevels,0,5) == 'level') ?  str_replace("level", "", $exam->clevels) :((substr($exam->clevels,0,6) == 'Level ')?str_replace("Level ", "", $exam->clevels) : str_replace("Level", "", $exam->clevels))) : 0 ;
                $reviewexms[$count]["competencyLevelName"] =$competencies ? implode(',', $competencies) : '';
                $reviewexms[$count]["programdescription"] = format_text($exam->programdescription, FORMAT_HTML);
                $reviewexms[$count]["type"] = $exam->type;
                $reviewexms[$count]["examsectors"] = $this->examsectors($exam->sectors);

                $enrolled = $DB->get_record('exam_enrollments', ['examid' => $exam->id, 'userid' => $USER->id]);
                if ($enrolled) {
                    $reviewexms[$count]["isenrolled"] = true;
                } else {
                    $reviewexms[$count]["isenrolled"] = false;
                }

                if ((!is_siteadmin() && has_capability('local/organization:manage_organizationofficial',$systemcontext))) {
                    $reviewexms[$count]["viewdetails"] = $CFG->wwwroot.'/local/exams/examdetails.php?id='.$exam->id;
                } elseif((!is_siteadmin() && has_capability('local/organization:manage_trainee',$systemcontext))) {

                    if (!empty($enrolled)) {
                        $profile = $DB->get_record('local_exam_profiles', ['id' => $enrolled->profileid]);
                        $result = $this->userscheduledate($exam->id, $profile, $profile->quizid);
                        $purchase = $result['purchase'];
                        if (empty($purchase)) {
                            // $reviewexms[$count]["viewdetails"] = $CFG->wwwroot.'/local/exams/hallschedule.php?examid='.$exam->id.'&profileid='.$enrolled->profileid;
                            $reviewexms[$count]["viewdetails"] = $CFG->wwwroot.'/local/exams/hallschedule.php?examid='.$exam->id.'&profileid='.$enrolled->profileid.'&status=en';
                        } else {
                            $reviewexms[$count]["viewdetails"] = $CFG->wwwroot.'/local/exams/examdetails.php?id='.$exam->id.'&profileid='.$enrolled->profileid;
                        }
                    } else {
                        $reviewexms[$count]["viewdetails"] = $CFG->wwwroot.'/local/exams/exams_qualification_details.php?id='.$exam->id;                        
                    }

                } else {
                    $reviewexms[$count]["viewdetails"] = $CFG->wwwroot.'/local/exams/exams_qualification_details.php?id='.$exam->id;
                }

                $BAN = false;
                $CAP = false;
                $FIN = false;
                $INS = false;
                $exam->sectordata = array();

                  $B = false;
                  $V = false;
                  $F = false;
                  $I = false;

                if($exam->sectors){
                    $sectorlist = $DB->get_fieldset_sql('select code from {local_sector} where id in('.$exam->sectors.')');

                    $listedsectors = ['V', 'F', 'I', 'B'];

                    foreach($listedsectors as $sector){
                        if(in_array($sector, $sectorlist)){
                            ${$sector} = true;
                        }
                    }
                }else{
                    $sectorlist = '';
                }
                $reviewexms[$count]['banking'] = $B;
                $reviewexms[$count]['capitalmarket'] = $V;
                $reviewexms[$count]['finance'] = $F;
                $reviewexms[$count]['insurance'] = $I;
                $reviewexms[$count]["sectors"] = $exam->sectordata;
                $reviewexms[$count]["startdate"] = date('d M Y', $exam->examdatetime);
                $reviewexms[$count]["enddate"] = date('d M Y', $exam->enddate);
                $reviewexms[$count]["date"] = date('d/m/Y', $exam->examdatetime). ' '. gmdate('H:i:s', $exam->slot);

                if(date('i', mktime(0, 0, $exam->examduration)) == 0) {

                    $reviewexms[$count]["hours"] = date('g', mktime(0, 0, $exam->examduration));
                    $reviewexms[$count]["durationstatus"] = get_string('hours', 'local_exams');

                } else {

                    $reviewexms[$count]["hours"] = date('H:i', mktime(0, 0, $exam->examduration));
                    $reviewexms[$count]["durationstatus"] = get_string('minutes', 'local_exams');
                }
            }
                
            $count++;
        }

        if($totalexams > 2){
            $noloadmore = true;
        }
        if($totalexams == count($reviewexms)){
            $noloadmore = false;
        }
        $coursesContext = array(
            "hascourses" => $reviewexms,
            "nocourses" => $nocourse,
            "totalexams" => $totalexams,
            "length" =>  count($reviewexms),  
            "noloadmore" => $noloadmore
        );
        return $coursesContext;
    }
    public function exam_qualification_details($id, $type=false,$mlang = NULL) {
        global $DB, $PAGE, $OUTPUT, $USER, $CFG,$SESSION;
        $context = context_system::instance();
        $renderer = $PAGE->get_renderer('local_exams');
        $lang = current_language();
        $SESSION->lang = ($mlang) ? $mlang : current_language() ;
        $result = $DB->get_record('local_exams', array('id'=>$id));
        $result->userexamreqstatus = $this->userrequirements($result->id);
        $result->attachedmessage_display = strip_tags(format_text($result->attachedmessage, FORMAT_HTML)) ? true:false;
        $result->attachedmessage = format_text($result->attachedmessage, FORMAT_HTML);
        $result->oneprerequisiteexams = ($result->requirements && $result->examineeshouldpass ==0)?true:false;
        $result->allprerequisiteexams = ($result->requirements && $result->examineeshouldpass ==1)?true:false;
        $result->ownedby = format_string($result->ownedby);
        $result->programdescription = format_text($result->programdescription, FORMAT_HTML);
        $result->additionalrequirements = format_text($result->additionalrequirements, FORMAT_HTML);
        $result->competencyweights = format_text($result->competencyweights, FORMAT_HTML);

        $sql = "SELECT cm.id
                          FROM {course_modules} cm
                          JOIN {modules} mo ON mo.id = cm.module
                          JOIN {quiz} m ON m.id = cm.instance
                          WHERE mo.name=:modulename AND
                               m.id=:moduleid";
        $cmparams = array('modulename' =>'quiz', 'moduleid' => $result->quizid);
        $cmid = $DB->get_field_sql($sql, $cmparams);
        $result->url = $CFG->wwwroot.'/mod/quiz/view.php?id='.$cmid;
        
        $lang = current_language();
        $result->sectordata = array();
        if( $lang == 'ar' || $SESSION->lang == 'ar'){
            $sector_sql = " SELECT ls.id, ls.titlearabic  as title
                            FROM {local_sector} ls 
                            JOIN {local_exams} e ON concat(',', e.sectors, ',') LIKE concat('%,',ls.id,',%') 
                            WHERE e.id = $id";
        }else{
            $sector_sql = " SELECT ls.id, ls.title
                            FROM {local_sector} ls 
                            JOIN {local_exams} e ON concat(',', e.sectors, ',') LIKE concat('%,',ls.id,',%') 
                            WHERE e.id = $id ";
        }
        $sectorlist =  $DB->get_records_sql($sector_sql);

        if ($sectorlist) {
            foreach($sectorlist  as $list) {
                $result->sectordata[] = array('sectorname' => $list->title, 'description' => NULL, 'value' => $list->id);
            }  
        }

        $result->examduration = $result->examduration/60 .get_string('min','local_exams');

        if (empty($result->targetaudience)) {
            $result->targetaudience = NULL;
        } else {
            $result->targetaudience = format_text($result->targetaudience, FORMAT_HTML);
        }

        $result->noofquestions = $this->examfields('noofquestions', $result->noofquestions);
        $result->language = $this->examfields('language', $result->language);
        $result->type = $result->type;

        if($result->targetgroup != '') {
            if($result->targetgroup == -1) {
                $lang = current_language();
                if ($lang == 'ar' || $SESSION->lang == 'ar') {
                    $targetgroups = $DB->get_records_sql("SELECT id, familynamearabic as name, description,segmentid FROM {local_jobfamily} ");
                } else {
                    $targetgroups = $DB->get_records_sql("SELECT id, familyname as name, description,segmentid FROM {local_jobfamily} ");
                }
            } else {
                $lang = current_language();
                if ($lang == 'ar' || $SESSION->lang == 'ar') {
                    $targetgroups = $DB->get_records_sql("SELECT id, familynamearabic as name, description,segmentid FROM {local_jobfamily} WHERE id IN ($result->targetgroup) ");
                } else {
                    $targetgroups = $DB->get_records_sql("SELECT id, familyname as name, description,segmentid FROM {local_jobfamily} WHERE id IN ($result->targetgroup) ");
                }
            }
        }
        
        $jobfamilies = [];
        foreach($targetgroups as $targetgroup) {

            
            
            if ($lang == 'ar') {
                
                if(empty($targetgroup->segmentid)) {

                     $row['familyname'] = html_writer::tag('a', $targetgroup->name,array('href' =>'https://fa.gov.sa/ar/jadarat/Pages/jobRoles.aspx?FId='.$targetgroup->id));

                } else {

                    $jobsectorid = $DB->get_fieldset_sql('SELECT sectorid FROM {local_segment} WHERE id IN ('.$targetgroup->segmentid.')');
                    $secid = implode(', ', $jobsectorid);
                    $row['familyname'] =  (count($jobsectorid)  == 1) ? html_writer::tag('a', $targetgroup->name,array('href' =>'https://fa.gov.sa/ar/jadarat/Pages/jobRoles.aspx?FId='.$targetgroup->id.'&SId='.$secid.'')): html_writer::tag('a', $targetgroup->name,array('href' =>'https://fa.gov.sa/ar/jadarat/Pages/jobRoles.aspx?FId='.$targetgroup->id));  ; 

                }
            }

            if ($lang == 'en') {
                
                if(empty($targetgroup->segmentid) || count($jobsectorid)  == 0) {

                     $row['familyname'] = html_writer::tag('a', $targetgroup->name,array('href' =>'https://fa.gov.sa/en/jadarat/Pages/jobRoles.aspx?FId='.$targetgroup->id));

                } else {

                    $secid = implode(', ', $jobsectorid);
                    $row['familyname'] =  (count($jobsectorid)  == 1) ? html_writer::tag('a', $targetgroup->name,array('href' =>'https://fa.gov.sa/en/jadarat/Pages/jobRoles.aspx?FId='.$targetgroup->id.'&SId='.$secid.'')): html_writer::tag('a', $targetgroup->name,array('href' =>'https://fa.gov.sa/en/jadarat/Pages/jobRoles.aspx?FId='.$targetgroup->id));  ; 

                }
            }
            // $row['familyname'] = html_writer::tag('a', $targetgroup->name,array('href' =>'https://fa.gov.sa/ar/jadarat/Pages/jobRoles.aspx?Fid='.$targetgroup->id));
            // $row['familyname'] = $targetgroup->name;
            $row['description'] = strip_tags(format_text($targetgroup->description, FORMAT_HTML));
            $row['value'] = $targetgroup->id;
            $jobfamilies[] = $row;
        }
        $result->targetgroup = $jobfamilies;

        if( $lang == 'ar' || $SESSION->lang == 'ar'){
            $examname = 'examnamearabic';
        } else {
            $examname = 'exam';
        }

        if(!empty($result->requirements)) {
            $sql = "SELECT $examname AS examname
                      FROM {local_exams} le 
                     WHERE id IN ($result->requirements) ";
            $requirements = $DB->get_records_sql($sql);            
        }
        //$result->requirements = !empty($requirements) ? implode(',', $requirements) : NULL;
        $result->requirements = !empty($requirements) ?array_values($requirements): NULL;
        $result->hasrequirementsmore = count(array_values($requirements))>4?true :false;
        $result->exam = $this->examname($result->id);        
        $profiles = $this->profiles($id);
        
        if(sizeof($profiles) == 1){
            $result->singleproduct = $profiles[0]['product_variations']['variation'];
            $seatingcapacity = $profiles[0]['seatingcapacity'];
            if (empty($profiles[0]['profileenrollment'])) {
                $result->viewscheules = true;
            }
        }

        if(empty($result->viewscheules)) {
            $renderer = $PAGE->get_renderer('local_exams');
            $filterparams  = $renderer->get_catalog_hallschedules(true);
            $filterparams['submitid'] = 'form#filteringform';
            $filterparams['globalinput'] = $renderer->global_filter($filterparams);
            $hallsmform = exams_reservations_form($filterparams);
            $filterparams['halldetails'] = $renderer->get_catalog_hallschedules();
            $filterparams['q'] = $searchquery;
            $filterparams['widthclass'] = 'col-md-12';
            $filterparams['inputclasses'] = 'examssearchinput';
            $filterparams['placeholder'] = get_string('search_skill','local_exams');
            $filterparams['filterform'] = $hallsmform->render();
            $result->examhallreservation = $renderer->listofreservations($filterparams);
        }

        $result->examcompetencies = array();
        if($result->ctype) {
            if($result->competencies) {

                $existingcompletencytypes = explode(',' ,$result->ctype);
                $examcompetencies = array_filter($existingcompletencytypes);
               
                $existingcompletencies = explode(',' ,$result->competencies);
                $existingcompletencies = array_filter($existingcompletencies);
                $competencylist = implode(',', $existingcompletencies);
                  
                foreach($examcompetencies as $competency){
                   
                    $sql = "SELECT loc.id, loc.name, loc.arabicname, loc.jobroleid, loc.description, loc.type
                             FROM {local_competencies} loc
                            WHERE loc.id IN ($competencylist) AND loc.type = '$competency' ";

                    $comp_data = $DB->get_records_sql($sql);
                    $row = array();
                    foreach($comp_data as $comp){
                        $data = array();

                        if( $lang == 'ar' || $SESSION->lang == 'ar'){
                            $data['name'] = html_writer::tag('a', $comp->arabicname,array('href' =>'https://fa.gov.sa/ar/jadarat/Pages/JedaraCard.aspx?CId='.$comp->id));
                            // $data['name'] = $comp->arabicname;
                        }else{

                            $data['name'] = html_writer::tag('a', $comp->name,array('href' =>'https://fa.gov.sa/en/jadarat/Pages/JedaraCard.aspx?CId='.$comp->id));
                            // $data['name'] = $comp->name;
                        }
                        // $competencylevel = $DB->get_field('local_jobrole_level', 'level', ['id' => $comp->jobroleid]);
                        $level = str_replace("level","",$result->clevels);
                        $data['typeId'] = NULL;
                        $data['typeName'] = $comp->type;
                        $data['description'] = format_text($comp->description, FORMAT_HTML);
                        $data['level'] = $level;
                        $data['value'] = $comp->id;
                        $row[] = $data; 
                    }
                    $result->examcompetencies[] = array('competencytype' => get_string($competency, 'local_exams'), 'competencyname' => $row);
                }
            } else {

                $result->examcompetencies[] = [];
            }
            
        }
          $systemcontext = context_system::instance();
          $isorgofficial=(!is_siteadmin() && has_capability('local/organization:manage_organizationofficial',$systemcontext)) ? true : false;
                $istrainee=(!is_siteadmin() && (has_capability('local/organization:manage_trainee',$systemcontext)));   
                if($isorgofficial || $istrainee){
                 $isorgofficialortrainee= true;
                 $userid=$USER->id;
                 }else{
                  $isorgofficialortrainee= false;
                 }
                 $component='local_exams';
                 $checkfavornot =$this->checkfavourites($id,$USER->id,$component);
        $data = [
            'exam' => $result,
            'description' => format_text($result->programdescription, FORMAT_HTML),
            'targetaudience' => $result->targetaudience,
            'fees' =>  number_format($result->sellingprice,0,'.',''),
            "product_attributes"  => (new \tool_product\product)->get_product_attributes($id, 2, 'addtocart', true),
            "examprofile" => $profiles,
            'isorgofficialortrainee'=>$isorgofficialortrainee,
            'checkfavornot'=>$checkfavornot,
            'userid'=>$userid,
        ];
        if($type == 'api') {

            return $data;

        } else {

            $filterparams  = $renderer->exam_qualification_details($data);

        }
    }

    public function userrequirements($examid, $userid=false)
    {
        global $DB, $USER;
        $context = context_system::instance();
        $requirements = $DB->get_field('local_exams', 'requirements', ['id' => $examid]);
        $examineeshouldpass = $DB->get_field('local_exams','examineeshouldpass',['id'=>$examid]);
        $result = false;
        $oneprerequisiteexams = false;
        $allprerequisiteexams = false;
        if( (!is_siteadmin() && has_capability('local/organization:manage_trainee',$context)) || !empty($userid) ) {
            if ($requirements) {

                if (empty($userid)) {
                    $userid = $USER->id;
                }

                $sql = "SELECT ec.id
                          FROM {exam_completions} ec 
                         WHERE ec.examid IN ($requirements) AND ec.userid =". $userid;
                $examrequirements = $DB->get_fieldset_sql($sql);
                $userreqstatus = COUNT($examrequirements);
                $reqcount = COUNT(explode(',', $requirements));
            } else {
                $reqcount = 0;
                $userreqstatus = 0;
            }
            if ($requirements && $examineeshouldpass == 0 && $userreqstatus <= 0) {
              //  $oneprerequisiteexams = true;
                $result = true; // Not Achieved
            }
            if ($requirements && $examineeshouldpass == 1 && $reqcount != $userreqstatus) {
                $result = true; // Not Achieved
               // $allprerequisiteexams = true;
            }
        }

        return $result;   
    }

    public static function get_userexamreservations($stable, $filterdata=false) {
        global $DB;
        $systemcontext = context_system::instance();
        $examid = $filterdata[examid];
        $result = $DB->get_record('local_exams', ['id' => $examid]);
        $result->examhallreservation = [];
        $reservations = $DB->get_fieldset_sql("SELECT id FROM {hall_reservations} WHERE typeid = $result->id AND type = 'exam'  ");
        $listofreservations = implode(',',$reservations);
        $countsql =  " SELECT COUNT(hr.id) ";
        $selectsql = " SELECT hr.id as reservationid, hr.slotstart, hr.slotend, hr.seats, hr.examdate,hr.hallid, h.*, (SELECT COUNT( DISTINCT ee1.id)
                 FROM {exam_enrollments} ee1 
                 JOIN {hall_reservations} hr1 ON  ee1.examdate = hr1.examdate AND ee1.examid = hr1.typeid 
                 WHERE hr1.id = hr.id ) as enrolled ";
        $sql = " FROM {hall_reservations} hr
                 JOIN {hall} h ON h.id = hr.hallid
                 JOIN {local_exams} le ON le.id = hr.typeid
                 WHERE hr.typeid = $result->id AND hr.type = 'exam' AND FROM_UNIXTIME(UNIX_TIMESTAMP(hr.examdate)+hr.slotend) > Now() ";

        $searchparams = [];

        if  (isset($filterdata[search_query]) && trim($filterdata[search_query]) != ''){
            $sql .= " AND (h.name LIKE :firstnamesearch) ";
            $searchparams = array('firstnamesearch' => '%'.trim($filterdata[search_query]).'%');
        } else {
            $searchparams = array();
        }

        if (!empty($filterdata[type])) {
            $sql .= " AND le.type = '{$filterdata[type]}' "; 
        }
        if (!empty($filterdata[halladdress])){
            $sql .= " AND h.id =".$filterdata[halladdress]; 
        }

        if( $filterdata['examdatetime[enabled]'] == 1 && $filterdata['enddate[enabled]'] == 1 ){
            $start_year = $filterdata['examdatetime[year]'];
            $start_month = $filterdata['examdatetime[month]'];
            $start_day = $filterdata['examdatetime[day]'];
            $filter_starttime_con = mktime(0,0,0, $start_month, $start_day, $start_year);

            $end_year = $filterdata['enddate[year]'];
            $end_month = $filterdata['enddate[month]'];
            $end_day = $filterdata['enddate[day]'];
            $filter_endtime_con=mktime(23,59,59, $end_month, $end_day, $end_year);

            if (!empty($filterdata[halladdress])){
                $hall = " AND hr.typeid = $examid AND hr.type = 'exam' AND h.id =".$filterdata[halladdress]; 
            } else {
                $hall = " AND hr.typeid = $examid  AND hr.type = 'exam' AND h.id =".$result->halladdress; 
            }

            $sql.= " AND (((UNIX_TIMESTAMP(hr.examdate) >= $filter_starttime_con AND UNIX_TIMESTAMP(hr.examdate) < $filter_endtime_con) $hall ) ";
            $sql.= " OR ((UNIX_TIMESTAMP(hr.examdate) >= $filter_starttime_con AND UNIX_TIMESTAMP(hr.examdate) < $filter_endtime_con) $hall )) ";
        } elseif($filterdata['examdatetime[enabled]'] == 1 ){
            $start_year = $filterdata['examdatetime[year]'];
            $start_month = $filterdata['examdatetime[month]'];
            $start_day = $filterdata['examdatetime[day]'];
            $filter_starttime_con = mktime(0,0,0, $start_month, $start_day, $start_year);
            $sql.= " AND UNIX_TIMESTAMP(hr.examdate) >= '$filter_starttime_con' ";
        } elseif($filterdata['enddate[enabled]'] == 1 ){
            $start_year = $filterdata['enddate[year]'];
            $start_month = $filterdata['enddate[month]'];
            $start_day = $filterdata['enddate[day]'];
            $filter_endtime_con=mktime(23,59,59, $start_month, $start_day, $start_year);
            $sql.=" AND UNIX_TIMESTAMP(hr.examdate) <= '$filter_endtime_con' ";
        }

        $params = array_merge($searchparams);

        $ordersql = " ORDER BY hr.id DESC ";
        $examreservations = $DB->get_records_sql($selectsql. $sql.$ordersql, $params, $stable->start,$stable->length);
        $totalreservations = $DB->count_records_sql($countsql. $sql, $params);

        $userreservations = array();
        $count = 0;

        if($examreservations) {
            foreach($examreservations as $examreservation){
                if($examreservation->seats != $examreservation->enrolled) {

                    $reservationid = $examreservation->reservationid;
                    $available = $examreservation->seats;

                    $slotstartdate = date('Y-m-d H:i:s', (strtotime($examreservation->examdate) + $examreservation->slotstart));
                    $slotenddate = date('Y-m-d H:i:s', (strtotime($examreservation->examdate) + $examreservation->slotend));
                    $todaysdate = date('Y-m-d H:i:s');

                    if($slotenddate <= $todaysdate) {
                        $available = false;
                        $availablestatus = true;
                    } elseif($slotstartdate <= $todaysdate) {
                        $available = false;
                        $availablestatus = false;
                    } else {
                        $available = true;
                    }
                    $enrolledcount = $DB->count_records('exam_enrollments', array('examid'=>$result->id, 'hallid' => $examreservation->hallid, 'examdate' => $examreservation->examdate));
                    $userreservations[$count]['reservationid'] = $reservationid;
                    $userreservations[$count]['slotstart'] = date('h:i A', mktime(0, 0, $examreservation->slotstart));
                    $userreservations[$count]['slotend'] = date('h:i A', mktime(0, 0, $examreservation->slotend));
                    $userreservations[$count]['reservestartdate'] = date('Y-m-d', strtotime($examreservation->examdate));
                    $userreservations[$count]['reserveenddate'] = date('Y-m-d', strtotime($examreservation->examdate));
                    $userreservations[$count]['startdate'] = strtotime($examreservation->examdate);
                    $userreservations[$count]['enddate'] = strtotime($examreservation->examdate);                   
                    $userreservations[$count]['seats'] = $examreservation->seats;
                    $userreservations[$count]['buildingname'] = $examreservation->buildingname;
                    $userreservations[$count]['hallname'] = $examreservation->name;

                    if (filter_var($reservation->maplocation, FILTER_VALIDATE_URL)) {
                        $userreservations[$count]['locationstatus'] = true;
                    } else {
                        $userreservations[$count]['locationstatus'] = false;
                    }

                    $fees = $DB->get_field('local_exams', 'sellingprice', ['id' => $result->id]);
                    $userreservations[$count]['fees'] = !empty($fees) ? $fees : 0;
                    $userreservations[$count]['location'] = $examreservation->maplocation? $examreservation->maplocation:'--';
                    $userreservations[$count]['enrolled'] = $enrolledcount ? $enrolledcount: 0;
                    $userreservations[$count]['available'] = $available;
                    $userreservations[$count]['availablestatus'] = $availablestatus;
                    $userreservations[$count]['product_variations'] = (new \tool_product\product)->get_product_variations($result->id, $reservationid, 2);
                    $userreservations[$count]['userreservations'] = true;
                    $userreservations[$count]['examhallreservation'] = true;
                    $userreservations[$count]['reservationview'] = true;
                    $count++;
                }
            }
        }

        $coursesContext = array(
            "hascourses" => $userreservations,
            "nocourses" => $nocourse,
            "totalreservations" => $totalreservations,
            "length" => COUNT($examreservations),
        );
        return $coursesContext;
    }

    public static function get_examreservations($stable, $filterdata,$learningpath=false) {
        global $DB;

        $examid = $filterdata['examid'];
        $reservationid = $filterdata['reservationid'];

        $hallid = $DB->get_field('local_exams', 'halladdress', ['id' => $examid]);
        $systemcontext = context_system::instance();
        $countsql =  " SELECT COUNT(hr.id) ";
        $hall_sql =  " SELECT hr.id as reservationid, hr.slotstart, hr.slotend, hr.seats, hr.examdate, hr.hallid, h.* , hr.typeid as examid, UNIX_TIMESTAMP(hr.examdate) as unixdate ";

        if(!is_siteadmin() && has_capability('local/organization:manage_trainee',$systemcontext)) {

            $sql = "    FROM {hall_reservations} hr 
                        JOIN {exam_enrollments} exe ON exe.examid = hr.typeid AND exe.hallid =hr.hallid 
                        JOIN {hall} h ON hr.hallid = h.id 
                        LEFT JOIN {local_exams} le ON le.id = hr.typeid AND le.halladdress = h.id
                        WHERE hr.typeid = $examid AND hr.type = 'exam' AND hr.status = 1 ";
        } else {

            $sql = "    FROM {hall_reservations} hr 
                        JOIN {hall} h ON hr.hallid = h.id 
                        LEFT JOIN {local_exams} le ON le.id = hr.typeid AND le.halladdress = h.id
                        WHERE hr.typeid = $examid AND hr.type = 'exam' AND hr.status = 1  ";
        }
       

        if($learningpath || (!is_siteadmin() && has_capability('local/organization:manage_organizationofficial',$systemcontext))) {
            $sql .= " AND FROM_UNIXTIME(UNIX_TIMESTAMP(examdate)+slotend) > Now() ";
        }
        $searchparams = [];

        if  (isset($filterdata[search_query]) && trim($filterdata[search_query]) != ''){
            $sql .= " AND (h.name LIKE :firstnamesearch) ";
            $searchparams = array('firstnamesearch' => '%'.trim($filterdata[search_query]).'%');
        } else {
            $searchparams = array();
        }

        if (!empty($filterdata[type])){
            $sql .= " AND le.type = '{$filterdata[type]}' "; 
        }
        if (!empty($filterdata[halladdress])){
            $sql .= " AND h.id =".$filterdata[halladdress]; 
        }

        if( $filterdata['examdatetime[enabled]'] == 1 && $filterdata['enddate[enabled]'] == 1 ){
            $start_year = $filterdata['examdatetime[year]'];
            $start_month = $filterdata['examdatetime[month]'];
            $start_day = $filterdata['examdatetime[day]'];
            $filter_starttime_con = mktime(0,0,0, $start_month, $start_day, $start_year);

            $end_year = $filterdata['enddate[year]'];
            $end_month = $filterdata['enddate[month]'];
            $end_day = $filterdata['enddate[day]'];
            $filter_endtime_con=mktime(23,59,59, $end_month, $end_day, $end_year);

            if (!empty($filterdata[halladdress])){
                $hall = " AND le.id = $examid AND h.id =".$filterdata[halladdress]; 
            } else {
                $hall = " AND le.id = $examid  AND h.id =".$hallid; 
            }

            $sql.= " AND ((UNIX_TIMESTAMP(hr.examdate) >= $filter_starttime_con AND UNIX_TIMESTAMP(hr.examdate) < $filter_endtime_con) $hall )";
            $sql.= " OR ((UNIX_TIMESTAMP(hr.examdate) >= $filter_starttime_con AND UNIX_TIMESTAMP(hr.examdate) < $filter_endtime_con) $hall )";
        } elseif($filterdata['examdatetime[enabled]'] == 1 ){
            $start_year = $filterdata['examdatetime[year]'];
            $start_month = $filterdata['examdatetime[month]'];
            $start_day = $filterdata['examdatetime[day]'];
            $filter_starttime_con = mktime(0,0,0, $start_month, $start_day, $start_year);
            $sql.= " AND UNIX_TIMESTAMP(hr.examdate) >= '$filter_starttime_con' ";
        } elseif($filterdata['enddate[enabled]'] == 1 ){
            $start_year = $filterdata['enddate[year]'];
            $start_month = $filterdata['enddate[month]'];
            $start_day = $filterdata['enddate[day]'];
            $filter_endtime_con=mktime(23,59,59, $start_month, $start_day, $start_year);
            $sql.=" AND UNIX_TIMESTAMP(hr.examdate) <= '$filter_endtime_con' ";
        }

        $params = array_merge($searchparams);

        if($reservationid){
            $sql .=" AND hr.id=$reservationid ";
            /*$examreservation = $DB->get_record_sql($hall_sql. $sql);
            return self::get_reservation($examreservation, $examid);*/
        }
        $sql .= " ORDER BY hr.id DESC ";

        $examreservations = $DB->get_records_sql($hall_sql. $sql, $params, $stable->start,$stable->length);

        $totalreservations = $DB->count_records_sql($countsql. $sql, $params);

        $examhallreservation = [];

        $reservationlowestseats=0;

        if($examreservations) {
            foreach($examreservations as $examreservation){

                $getreservation=self::get_reservation($examreservation, $examid);

                if($reservationlowestseats == 0 || ($reservationlowestseats > $getreservation['availableseats'])){

                    $reservationlowestseats=$getreservation['availableseats'];
                
                }

                $examhallreservation[] = $getreservation;
            }
        }
        if($learningpath){


            return compact('examhallreservation', 'reservationlowestseats');

        }else{

            $coursesContext = array(
                "hascourses" => $examhallreservation,
                "nocourses" => $nocourse,
                "totalreservations" => $totalreservations,
                "length" => COUNT($examhallreservation),
            );

            return $coursesContext;
        }
    }

    public static function get_reservation($reservation, $examid) {
        global $DB, $USER;
        $systemcontext = context_system::instance();
        $reservationid = $reservation->reservationid;
        $reservation->variation_params = array(
            'id'        => $reservation->id,
            'parent'    => $reservation,
            'label'     => get_string('select', 'tool_product')   
        );
        $capacity = $reservation->seatingcapacity;
        $available = $reservation->seats;

        $slotstartdate = date('Y-m-d H:i:s', (strtotime($reservation->examdate) + $reservation->slotstart));
        $slotenddate = date('Y-m-d H:i:s', (strtotime($reservation->examdate) + $reservation->slotend));
        $todaysdate = date('Y-m-d H:i:s');

        if($slotenddate <= $todaysdate) {
            $available = false;
            $availablestatus = true;
        } elseif($slotstartdate <= $todaysdate) {
            $available = false;
            $availablestatus = false;
        } else {
            $available = true;
        }

        $reservation->product_variations = (new \tool_product\product)->get_product_variations($examid, $reservation->reservationid, 2);
        $slotstart = date('h:i A', mktime(0, 0, $reservation->slotstart));
        $slotend = date('h:i A', mktime(0, 0, $reservation->slotend));
        $startdate = strtotime($reservation->examdate);
        $enddate = strtotime($reservation->examdate);
        $reservestartdate = date('Y-m-d', strtotime($reservation->examdate));
        $reserveenddate = date('Y-m-d', strtotime($reservation->examdate));

        $enrolledcount = $DB->count_records('exam_enrollments', array('examid'=>$examid, 'hallid'=>$reservation->id, 'examdate' => $reservation->examdate));

        if (filter_var($reservation->maplocation, FILTER_VALIDATE_URL)) {
            $locationstatus = true;
        } else {
            $locationstatus = false;
        }
        $location = $reservation->maplocation ;

        $enrolled = $enrolledcount ? $enrolledcount: 0;

        $exams = $DB->get_record('local_exams', ['id' => $reservation->examid]);
        if($exams->examprice > 0){

            if(is_siteadmin() || has_capability('local/organization:manage_examofficial', $systemcontext)) {
                $enrolled = self::entity_enrolled($reservationid);
            } else {
                $enrolled = self::entity_enrolled($reservationid, $USER->id);
            }

            // $enrolled = self::entity_enrolled($reservationid, $USER->id);
            $availableseats = $reservation->seats - $enrolled; 

            $seats = $DB->get_field_sql("SELECT SUM(purchasedseats) FROM {tool_org_order_seats} WHERE tablename = 'hall_reservations' AND fieldname = 'id' AND fieldid = $reservation->reservationid");

            $entityseats = self::entity_enrolled($reservationid);
            $offeringvailableseats = $seats + $entityseats;

            $bookingseats=$offeringvailableseats ? ($reservation->seats - $offeringvailableseats) : $availableseats ;

            $examseats = (new \tool_product\product)->get_button_order_seats($label=get_string('bookseats','local_exams'),'hall_reservations','id',$reservation->reservationid, $bookingseats,$reservation->examid);

        }else{ 
            $trainingurl = new moodle_url('/local/exams/examenrollment.php',array('examid'=>$exams->id, 'hallreservationid' => $reservation->reservationid));
            $examseats = html_writer::link($trainingurl,get_string('enrollbtn',  'tool_product'));
        }

        if(is_siteadmin() || has_capability('local/organization:manage_examofficial', $systemcontext)) {
            $purchasedseats=(new \tool_product\product)->purchasedseats_check('hall_reservations','id',$reservationid);
            $approvedseats=(new \tool_product\product)->approvedseats_check('hall_reservations','id',$reservationid);
            $availableseats=(new \tool_product\product)->availableseats_check('hall_reservations','id',$reservationid);
            if ($purchasedseats == 0) {
                $purchasedseats = 0;
                $availableseats=$reservation->seats - $enrolled;
            } else {
                $purchasedseats = $approvedseats;
                $enrolledseats = self::entity_enrolled($reservationid, $USER->id);
                $availableseats=$reservation->seats - $approvedseats - $enrolledseats;
            }
        } else {
            if($exams->examprice == 0) {
                $hallseats =  $DB->get_field('hall_reservations', 'seats', ['id' => $reservationid]);
                $enrolledusers = self::entity_enrolled($reservationid);
                $availableseats = $hallseats - $enrolledusers;
            } else {
                $availableseats=(new \tool_product\product)->availableseats_check('hall_reservations','id',$reservationid);
            }
            $purchasedseats=(new \tool_product\product)->purchasedseats_check('hall_reservations','id',$reservationid);
            $approvedseats=(new \tool_product\product)->approvedseats_check('hall_reservations','id',$reservationid);
        }

        if( is_siteadmin() || has_capability('local/organization:manage_examofficial', $systemcontext) ) {
            $examselectbtn = true;
        } else if(has_capability('local/organization:manage_organizationofficial', $systemcontext)) {
            $exambooknowbtn = true;
        }

         return array(  'reservationid' => $reservationid, 
                        'slotstart' => $slotstart, 
                        'slotend' => $slotend, 
                        'reservestartdate' => $reservestartdate, 
                        'reserveenddate' => $reserveenddate,
                        'startdate' => $startdate,
                        'enddate' => $enddate,
                        'seats' => $reservation->seats, 
                        'buildingname' =>  $reservation->buildingname, 
                        'hallname' => $reservation->name,
                        'locationstatus' => $locationstatus, 
                        'location' => $location, 
                        'enrolled' => $enrolled, 
                        'available' => $available,
                        'status' => $available ? get_string('open','local_exams') : get_string('completed','local_trainingprogram'),
                        'availablestatus' => $availablestatus,
                        'product_variations' =>  $reservation->product_variations, 
                        'examseats' => $examseats, 
                        'examid' => $reservation->examid,
                        'fees' => $exams->sellingprice,
                        'purchasedseats' => $purchasedseats,
                        'availableseats' => $availableseats,
                        'approvedseats' => $approvedseats,
                        'examselectbtn' => $examselectbtn,
                        'exambooknowbtn' => $exambooknowbtn,
                        'examhallreservation' => true,
                        'reservationview' => ((!isloggedin() && !has_capability('local/organization:manage_trainingofficial', $systemcontext) && !has_capability('local/organization:manage_communication_officer',$systemcontext) && !has_capability('local/organization:manage_financial_manager',$systemcontext) && $availableseats > 0 && ($available))) ? true : false

                    );
    }

    public static function set_questionbanks_exams($examid,$questionbanks,$questionbanknumbertoadd) {
        global $DB, $USER,$CFG;
        require_once($CFG->dirroot . '/mod/quiz/locallib.php');
        require_once($CFG->dirroot . '/mod/quiz/addrandomform.php');
        require_once($CFG->dirroot . '/question/editlib.php');
        $addonpage = optional_param('addonpage', 0, PARAM_INT);
        $sql = "SELECT le.courseid, lep.quizid, lep.questions as noofquestions
                  FROM {local_exam_profiles} lep
                  JOIN {local_exams} le ON lep.examid = le.id
                 WHERE lep.id =". $examid;
        $examsinfo = $DB->get_record_sql($sql);

        if($examsinfo->quizid && $examsinfo->noofquestions){
            $sql = "SELECT cm.id
                              FROM {course_modules} cm
                              JOIN {modules} mo ON mo.id = cm.module
                              JOIN {quiz} m ON m.id = cm.instance
                              WHERE mo.name=:modulename AND
                                   m.id=:moduleid";
            $cmparams = array('modulename' =>'quiz', 'moduleid' => $examsinfo->quizid);
            $cmid = $DB->get_field_sql($sql, $cmparams);

            if($cmid){
                list($quiz, $cm) = get_module_from_cmid($cmid);
                if($quiz){
                    $total=0;
                    foreach($questionbanks as $categoryid=>$questionbank){
                        if($examsinfo->noofquestions == $total){
                            break;
                        } else {
                            // If the chosen category is a top category.
                            $includesubcategories = $DB->record_exists('question_categories', ['id' => $categoryid, 'parent' => 0]);
                            quiz_add_random_questions($quiz, $addonpage, $categoryid, $questionbanknumbertoadd[$categoryid], $includesubcategories, $tagids= []);
                            quiz_delete_previews($quiz);
                            quiz_update_sumgrades($quiz);
                        }
                        $total=$total+$questionbanknumbertoadd[$categoryid];
                    }
                    $return="successbanknoquestion";
                } else {
                    $return="<div class='alert alert-danger'>" . get_string('invalidquiz', 'local_exams') . "</div>";
                }
            } else {
                $return="<div class='alert alert-danger'>" . get_string('invalidcoursemodule', 'local_exams') . "</div>";
            }
        } else {
            $return="<div class='alert alert-danger'>" . get_string('noexamquiz', 'local_exams') . "</div>";

        }
        return $return;
    }
    public function hall_data($stable, $filterdata) {
        global $DB, $PAGE;
        $systemcontext = context_system::instance();
        $PAGE->set_context($systemcontext);        
        $renderer = $PAGE->get_renderer('local_exams');
        $hall = $DB->get_record('hall', ['id' => $filterdata->hallid]);
        $exam = $DB->get_record('local_exams', ['id' => $filterdata->typeid]);

        if(empty($hall)) {
            $data['nodata'] = true;
            return $renderer->hall_data($data);
        }

        $duration = $exam->examduration/60;
        $due = '+'.$duration.'minutes';
        $start_time = $hall->hallstarttime;
        $end_time = $hall->hallendtime;
        $slot = strtotime(date('Y-m-d H:i:s',$start_time) . $due);

        $data = [];
        for ($i=0; $slot <= $end_time; $i++) {
            $row['start'] = date('H:i A', $start_time);
            $row['end'] = date('H:i A', $slot);
            $row['start_time'] = $start_time;
            $row['end_time'] = $slot;
            $row['hallid'] = $filterdata->hallid;
            $row['typeid'] = $filterdata->typeid;
            $row['examdate'] = $filterdata->examdate;
            $totalseats = $DB->get_field("hall", 'seatingcapacity', ['id' => $filterdata->hallid]);
            $records = $DB->get_records_sql("SELECT * FROM {hall_reservations} WHERE hallid = {$filterdata->hallid} AND (slotstart = '{$start_time}' AND slotend = '{$slot}' AND examdate = '{$filterdata->examdate}') OR ('{$start_time}' >= slotstart AND '{$start_time}' < slotend AND examdate = '{$filterdata->examdate}') OR ('{$slot}' > slotstart AND '{$slot}' < slotend AND examdate = '{$filterdata->examdate}') ");
            if(empty($records)) {
                $row['booked'] = false;
                $row['examname'] = '';
            } else {
                $bookedseats = [];
                foreach($records as $record) {
                    $bookedseats[] = $record->seats;
                }
                $reservedseats = array_sum($bookedseats);
                $availableseats = ($totalseats-$reservedseats);
                if($availableseats > 0) {
                    $row['booked'] = false;
                    $row['examname'] = '';
                } else {
                    $row['booked'] = true;
                    $row['examname'] = $DB->get_field('local_exams', 'exam', ['id' => $records->typeid]);
                }
            }
            $data[] = $row;
            $start_time = $slot;
            $slot = strtotime(date('Y-m-d H:i:s',$start_time) . $due);
        }
        for($i = $stable->start; $i<($stable->start + $stable->length); $i++) {
            $test[] = $data[$i];
            if($i == COUNT($data)-1) {
                break;
            }
        }
        $coursesContext = array(
            "hascourses" => $test,
            "nocourses" => $nocourse,
            "totalexams" => COUNT($data),
            "length" => COUNT($data),
        );
        return $coursesContext;
    }
    public function update_exams_status($exams) {
        global $DB, $USER;

        $exam         = new stdClass();
        $exam->examid     = $exams->examid;
        $exam->userid     = $exams->userid;
        $exam->realuser     = ($USER->realuser) ? $USER->realuser :0;
        $exam->profileid  = $exams->profileid;
        $completions=$DB->get_record('exam_completions',array('examid'=>$exam->examid,'userid'=>$exam->userid),'id,completion_status');
        try {
            if($exams->cmid==$exams->quizid){
                $exam->completion_status = $exams->completionstate;
                $exam->completiondate = time();                
            }
            if($completions){
                $exam->id   = $completions->id;
                $exam->usermodified   = $USER->id;
                $exam->timemodified   = time();
                $DB->update_record('exam_completions', $exam);

                // Trigger an event for exam completion changed.
                $event = \local_exams\event\exam_completion_updated::create(array(
                    'objectid' => $exam->id,
                    'context' => \context_system::instance(),
                    'relateduserid' => $exam->userid,
                    'other' => array(
                        'relateduserid' => $exam->userid,
                        'examid' => $exam->examid,
                        'completion_status' => $exam->completion_status ? $exam->completion_status : 0,
                    )
                ));
                $event->add_record_snapshot('exam_completions', $exam);
                $event->trigger();

                $iscompleted = $DB->record_exists_sql("SELECT id
                              FROM {exam_completions} 
                             WHERE examid = $exam->examid
                                AND userid = $exam->userid AND completion_status IN(1,2)");

                if($iscompleted) {

                    $trackid = $DB->get_field_sql("SELECT trackid
                              FROM {local_lts_item_enrolment} 
                             WHERE itemid = $exams->examid
                                AND userid = $exams->userid AND itemtype = 2 ");

                    if($trackid) {

                        $lts_enrollment =$DB->get_record('local_lts_enrolment',['trackid' =>$trackid,'userid'=>$exams->userid]);

                        $event = \local_learningtracks\event\learningtracks_completion_updated::create(array(
                            'objectid' =>  $exam->id,
                            'context' => \context_system::instance(),
                            'relateduserid' => $exam->userid,
                            'other' => array(
                                'relateduserid' => $exam->userid,
                                'learningtrackid' => $trackid,
                                'completion_status' =>  $exam->completion_status ? $exam->completion_status : 0,
                            )
                        ));
                        $event->add_record_snapshot('trainingprogram_exam_completion', $lts_enrollment);
                        $event->trigger();

                    }
                    
                }

                 $traineesql = "SELECT ra.id
                 FROM {role_assignments} ra 
                 JOIN {role} as r ON r.id = ra.roleid
                 WHERE ra.contextid = 1 AND r.shortname = 'trainee' AND ra.userid = ". $exam->userid;
                 $traineerole = $DB->get_field_sql($traineesql);
                 if($traineerole){
                    $exam->exam_name= $DB->get_field('local_exams','exam',array('id'=>$exam->examid));

                    $localuserrecord = $DB->get_record('local_users',['userid'=> $exam->userid]);
                    $fname = ($localuserrecord)? (($localuserrecord->lang == 'ar') ? $localuserrecord->firstnamearabic.' '.$localuserrecord->middlenamearabic.' '.$localuserrecord->thirdnamearabic.' '.$localuserrecord->lastnamearabic  : $localuserrecord->firstname.' '.$localuserrecord->middlenameen.' '.$localuserrecord->thirdnameen.' '.$localuserrecord->lastname) :fullname($DB->get_record('user',array('id'=>$exam->userid)));
               
                    $exam->exam_userfullname= $fname;
                    $trainee=$DB->get_record('user',array('id'=>$exam->userid));
                    (new \local_exams\notification())->exams_notification('exams_completion', $touser=$trainee,$fromuser=$USER, $exam,$waitinglistid=0);
                 } 
            }else{
                $exam->usercreated   = $USER->id;
                $exam->timecreated   = time();
                $exam->id=$DB->insert_record('exam_completions', $exam);

                // Trigger an event for exam completion changed.
                $event = \local_exams\event\exam_completion_updated::create(array(
                    'objectid' => $exam->id,
                    'context' => \context_system::instance(),
                    'relateduserid' => $exam->userid,
                    'other' => array(
                        'relateduserid' => $exam->userid,
                        'examid' => $exam->examid,
                        'completion_status' => $exam->completion_status ? $exam->completion_status : 0,
                    )
                ));
                $event->add_record_snapshot('exam_completions', $exam);
                $event->trigger();

                $iscompleted = $DB->record_exists_sql("SELECT id
                              FROM {exam_completions} 
                             WHERE examid = $exams->examid
                                AND userid = $exams->userid AND completion_status IN(1,2)");

                if($iscompleted) {

                    $trackid = $DB->get_field_sql("SELECT trackid
                              FROM {local_lts_item_enrolment} 
                             WHERE itemid = $exams->examid
                                AND userid = $exams->userid AND itemtype = 2 ");

                    if($trackid) {

                        $lts_enrollment =$DB->get_record('local_lts_enrolment',['trackid' =>$trackid,'userid'=>$exams->userid]);

                        $event = \local_learningtracks\event\learningtracks_completion_updated::create(array(
                            'objectid' => $exam->id,
                            'context' => \context_system::instance(),
                            'relateduserid' =>$exam->userid,
                            'other' => array(
                                'relateduserid' => $exam->userid,
                                'learningtrackid' => $trackid,
                                'completion_status' => $exam->completion_status ? $exam->completion_status : 0,
                            )
                        ));
                        $event->add_record_snapshot('trainingprogram_exam_completion', $lts_enrollment);
                        $event->trigger();
                      
                    }
                    
                }

                $traineesql = "SELECT ra.id
                 FROM {role_assignments} ra 
                 JOIN {role} as r ON r.id = ra.roleid
                 WHERE ra.contextid = 1 AND r.shortname = 'trainee' AND ra.userid = ". $exam->userid;
                 $traineerole = $DB->get_field_sql($traineesql);
                 if($traineerole){
                    $exam->exam_name= $DB->get_field('local_exams','exam',array('id'=>$exam->examid)); 
                    $localuserrecord = $DB->get_record('local_users',['userid'=> $exam->userid]);
                    $fname = ($localuserrecord)? (($localuserrecord->lang == 'ar') ? $localuserrecord->firstnamearabic.' '.$localuserrecord->middlenamearabic.' '.$localuserrecord->thirdnamearabic.' '.$localuserrecord->lastnamearabic  : $localuserrecord->firstname.' '.$localuserrecord->middlenameen.' '.$localuserrecord->thirdnameen.' '.$localuserrecord->lastname) :fullname($DB->get_record('user',array('id'=>$exam->userid)));    
                    $exam->exam_userfullname= $fname;
                    $trainee=$DB->get_record('user',array('id'=>$exam->userid));
                    (new \local_exams\notification())->exams_notification('exams_completion', $touser=$trainee,$fromuser=$USER, $exam,$waitinglistid=0);
                 } 
            }
        } catch (dml_exception $ex) {
            print_error($ex);
        }
        return true;
    }
    public function get_listofhalls($query = null) {
        global $DB;
        $fields = array('name');
        $likesql = array();
        $i = 0;
        foreach ($fields as $field) {
            $i++;
            $likesql[] = $DB->sql_like($field, ":queryparam$i", false);
            $sqlparams["queryparam$i"] = "%$query%";
        }
        $sqlfields = implode(" OR ", $likesql);
        $concatsql = " AND ($sqlfields) ";
        $data = $DB->get_records_sql("SELECT id, name AS fullname FROM {hall} WHERE availability = 1 $concatsql", $sqlparams);
        return $data;        
    }

    public function get_listof_competencies($programid=null,$ctype, $fields=array()) {
        global $DB;
        if($programid == null) {
            return $DB->get_records('local_competencies');
        }
        if(empty($ctype)) {
            $data =array();
        }elseif(!empty($ctype) && $ctype !='all' ) {
            $ctype=json_decode($ctype);
            list($competenciessql,$competenciesparams) = $DB->get_in_or_equal($ctype);
            $query = "SELECT id , name AS fullname FROM {local_competencies} WHERE type $competenciessql";
            $data= $DB->get_records_sql($query,$competenciesparams);
        } else {
            $query = "SELECT id , name AS fullname FROM {local_competencies}";
            $data= $DB->get_records_sql($query);
        }
        return $data;
    }

     public function get_listof_trainerusers($programid=null, $fields=array()) {
        global $DB;
        if($programid == null) {
            return $DB->get_records('local_users');
        }

        $courseid=$DB->get_field('local_trainingprogram','courseid', array('id' => $programid));
        $trainerroleid= $DB->get_field('role', 'id', array('shortname' => 'trainer'));
        $approvedstatus=2;

        $fullname =tp::user_fullname_case();
        $data = $DB->get_records_sql("SELECT u.id,$fullname FROM {user} AS u JOIN {local_users} lc ON lc.userid = u.id JOIN {course} as c ON c.id=$courseid  WHERE  u.id > 2 AND u.deleted = 0  AND lc.deleted=0 AND lc.approvedstatus=$approvedstatus AND u.id NOT IN (SELECT ue.userid FROM {user_enrolments} AS ue JOIN {enrol} e ON (e.id = ue.enrolid and e.courseid=$courseid AND (e.enrol='manual' OR e.enrol='self'))) AND u.id  NOT IN (SELECT rla.userid FROM {role_assignments} AS rla JOIN {context} cnt ON cnt.id = rla.contextid AND rla.roleid=$trainerroleid AND cnt.instanceid=$courseid)");

        return $data;
    }

    public static function trainingprogram_competencylevels($competencies= array(),$programid=0) {
        global $DB, $USER;
      $lang = current_language();
      if(!empty($competencies)){

       
            $title = ($lang == 'ar') ? 'arabicname' : 'name';
        

            $params = array();

            list($competenciessql, $competenciesparams) = $DB->get_in_or_equal($competencies);
            $params = array_merge($params, $competenciesparams);
            $competency= $DB->get_records_sql_menu("SELECT id,$title  as title FROM {local_competencies} WHERE id $competenciessql",$params);

        }elseif(!empty($programid)){

             $title = ($lang == 'ar') ? 'loc.arabicname' : 'loc.name';

            $competency= $DB->get_records_sql_menu("SELECT loc.id, $title as title 
                                                        FROM {local_competencies} as loc
                                                        JOIN {local_exams} as lot 
                                                        ON concat(',', lot.competencies, ',') LIKE concat('%,',loc.id,',%')
                                                        WHERE lot.id=:programid",['programid' => $programid]);


            }        
        return $competency;
            
    }
    //Vinod- Exams fake block for exam official - Starts//

    public function listof_examofficial_blockdata($stable, $filterdata) {
        global $DB, $PAGE, $OUTPUT, $CFG;
        $systemcontext = context_system::instance();

        $selectsql = "SELECT lo.id as examid,lo.exam as examname,lo.examdatetime,lo.halladdress,lo.courseid  FROM {local_exams} lo WHERE lo.id NOT IN (SELECT examid FROM {exam_completions} WHERE  completion_status = 1) AND lo.status =1"; 
        $countsql  = "SELECT COUNT(lo.id) FROM {local_exams} lo WHERE lo.id NOT IN (SELECT examid FROM {exam_completions} WHERE  completion_status = 1) AND lo.status =1 ";
        if  (isset($filterdata->search_query) && trim($filterdata->search_query) != ''){
            $formsql = " AND (lo.exam LIKE :firstnamesearch) ";
            $searchparams = array('firstnamesearch' => '%'.trim($filterdata->search_query).'%');
        } else {
            $searchparams = array();
        }
        $params = array_merge($searchparams);
        $totalexams = $DB->count_records_sql($countsql.$formsql,$params);
        $formsql .=" ORDER BY lo.examdatetime DESC";
        $exams = $DB->get_records_sql($selectsql.$formsql, $params, $stable->start,$stable->length);
        $examslist = array();
        $count = 0;
        foreach($exams as $exam) {
                $examslist[$count]['examid'] = $exam->examid;
                $examslist[$count]['examname'] = $exam->examname;
                $examslist[$count]['examdatetime'] = date('jS F Y', $exam->examdatetime );
                $examslist[$count]['location'] =$exam->halladdress?$DB->get_field('hall','maplocation',array('id'=>$exam->halladdress)):'';

            $count++;
        }
        $completedexams = $this->completedexamsforblock();
        $coursesContext = array(
            "hascourses" => $examslist,
            "nocourses" => $nocourse,
            "totalexams" => $totalexams,
            "length" => count($examslist),
            "completedexams" => $completedexams,

            
        );
        return $coursesContext;
    }

     public function completedexamsforblock() {
         global $DB, $PAGE, $OUTPUT, $USER;

          $examslist = array();

            $selectsql = "SELECT lo.id as examid,lo.exam as examname,lo.examdatetime,lo.halladdress,lo.courseid  FROM {local_exams} lo JOIN {exam_completions} ec ON lo.id =ec.examid  WHERE ec.completion_status = 1  AND lo.status=1";
            $exams = $DB->get_records_sql($selectsql);
            $i=1;
            foreach($exams as $exam) {

               $examslist['examid'] = $exam->examid;
               $examslist['examname'] = $exam->examname;
               $examslist['examdatetime'] = date('jS F Y', $exam->examdatetime );
               $examslist['location'] = $DB->get_field('hall','maplocation',array('id'=>$exam->halladdress));
            }

        return $examslist;

    }
    //Vinod - Exams fake block for exam official - Ends//


    public function examdefaultimage_url() {
        global $CFG;
        $url= $CFG->wwwroot.'/local/exams/pix/examdefaultimage.png';
        return $url;
    }

    public function grievance_info($stable, $filterdata) {
        global $DB, $PAGE, $OUTPUT, $USER;
        $systemcontext = context_system::instance();

        $data = $DB->get_fieldset_sql("SELECT productdata FROM {tool_product_telr} WHERE statustext = 'Paid' ");

        foreach($data as $info) {
            $category = unserialize(base64_decode($info))['items'][0]['category'];
            if($category == 4) {
                $product_id[] = unserialize(base64_decode($info))['items'][0]['product_id'];
            }
        }

        $products = implode(',', $product_id);

        if(!empty($products)) {
            $products = $products;
        } else {
            $products = 0;
        }

        $payment_type = $DB->get_field('local_exams', 'examprice',['id' => $filterdata->examid]);

        $selectsql = "SELECT eg.*, e.exam,  u.firstname, u.lastname";
        $countsql  = "SELECT COUNT(eg.id)";
        $formsql = " FROM {local_exam_grievance} eg
                     JOIN {local_exams} e ON e.id = eg.examid 
                     JOIN {exam_grievance_payments} AS egp ON eg.id = egp.grievanceid
                     JOIN {user} u ON u.id = eg.userid ";
        /*if($payment_type == 1) {
            $formsql .= " JOIN {tool_products} tp ON tp.referenceid = eg.id  AND eg.paymentstatus = 1 AND tp.id IN ($products) ";
        }*/
        $formsql .= " WHERE eg.examid = $filterdata->examid ";
        if(!is_siteadmin() && has_capability('local/organization:manage_trainee', $systemcontext)) {
            $formsql .=" AND eg.userid = ". $USER->id;
        }
        $searchparams = array();  
        $params = array_merge($searchparams);
        $totalcount = $DB->count_records_sql($countsql.$formsql, $params);
        $formsql .=" ORDER BY eg.id DESC";
        $grievance_list = $DB->get_records_sql($selectsql.$formsql, $params, $stable->start,$stable->length);

        $grievancelist = array();
        if($grievance_list) {
            $count = 0;
            foreach($grievance_list as $list) {
                $grievancelist[$count]['id'] = $list->id;
                $grievancelist[$count]['srno'] = $count +1;
                $grievancelist[$count]['username'] = fullname($list);
                $grievancelist[$count]['examname'] = $list->exam;
                $grievancelist[$count]['examid'] = $list->examid;
                $grievancelist[$count]['reason'] = strip_tags($list->reason);
                $grievancelist[$count]['userid'] = $list->userid;
                $statusarray = array(1 => get_string('pending', 'local_exams'),
                2 => get_string('approved', 'local_exams'),
                3 => get_string('rejected', 'local_exams'));
                $grievancelist[$count]['status'] = $statusarray[$list->status];
                if(is_siteadmin() || has_capability('local/exams:create', $systemcontext) 
                || has_capability('local/organization:manage_examofficial', $systemcontext)) {
                    $grievancelist[$count]['action'] = true;
                } else {
                    $grievancelist[$count]['action'] = false;
                }
                if($list->status == 1) {
                    $grievancelist[$count]['statusaction'] = true;
                } else {
                    $grievancelist[$count]['statusaction'] =  false;
                }
                $count++;
            }
            $nodata = false;
        } else {
            $nodata = true;
        }
        $coursesContext = array(
            "records" => $grievancelist,
            "nodata" => $nodata,
            "totalcount" => $totalcount,
            "length" => $totalcount
        );
        return $coursesContext;
    }

    public function grievance_details($greivanceid, $examid) {
        global $DB, $PAGE;
        $systemcontext = context_system::instance();
        $PAGE->set_context($systemcontext);
        $params = ['greivanceid' => $greivanceid, 'examid' => $examid];
        $sql = " SELECT ge.*, e.id, e.exam, u.firstname, u.lastname FROM {local_exam_grievance} ge 
        JOIN {local_exams} e ON e.id = ge.examid JOIN {user} u ON u.id = ge.userid  WHERE ge.id = :greivanceid AND ge.examid = :examid";
        $greivancedata = $DB->get_record_sql($sql, $params);
        $data = [];
        $data['id'] =  $greivancedata->id;
        $data['examname'] = $greivancedata->exam;
        $data['username'] =  fullname($greivancedata);
        $data['reason'] = strip_tags(html_entity_decode($greivancedata->reason));
        $data['submittedon'] = date('d-m-Y', $greivancedata->submittedon);
        $renderer = $PAGE->get_renderer('local_exams');
        $org  = $renderer->grievance_details($data);
        return $org;
    }
    public function add_update_grievance($data) {
        global $DB, $PAGE;
        $systemcontext = context_system::instance();        
        $row['examid'] = $data->examid;
        $row['profileid'] = $data->profileid;
        $row['userid'] = $data->userid;
        $row['reason'] = $data->reason['text'];
        $row['status'] = 1;
        $row['paymentstatus'] = 1;
        $row['submittedon'] = time();
        $row['timecreated'] = time();
        $record = $DB->insert_record('local_exam_grievance', $row);

        $test = [];
        $test['context'] = $systemcontext;
        $test['objectid'] =  $record;
        $event = \local_exams\event\grievance_created::create($test);
        $event->trigger();
        return $record;
    }
    public function enrol_exam($add, $profileid,$params) {
        global $DB, $PAGE, $OUTPUT, $USER;
        $language = current_language();
        $type = 'exam_enrol';
        $profile = $DB->get_record('local_exam_profiles', ['id' => $profileid]);
        if (!$this->get_user_role($USER->id)->shortname == 'assessmentop') {
            $availableseats = $this->availableseats($profileid, $USER->id);
        }else{
            $availableseats = $profile->seatingcapacity;
        }
        $seat_reserved_already = count($this->exam_enrolled_users('remove', $profileid, $params));
        $userstoassign = $add;
        $courseid = $DB->get_field('local_exams', 'courseid', ['id' => $params['examid']]);
        $course = $DB->get_record('course', ['id' => $courseid], '*', MUST_EXIST);
        $profile = $DB->get_record('local_exam_profiles', ['id' => $profileid]);
        if(sizeof($userstoassign) > $availableseats) {
            echo "<div class='alert alert-info'>".get_string('userscountismore', 'local_exams', $profile->seatingcapacity)."</div>";
        } else {
            if (!empty($userstoassign)) {
                if($language == 'en')
                {
                    preg_match('/{mlang en}(.*?){mlang}/', $course->fullname, $match);
                    $coursefullnameen =  $match[1];
                    $coursefullname = $coursefullnameen;
                }
                if($language == 'ar')
                {
                    preg_match('/{mlang ar}(.*?){mlang}/', $course->fullname, $match);
                    $coursefullnamear =  $match[1];
                    $coursefullname = $coursefullnamear;
                }

                $progress = 0;
                $progressbar = new \core\progress\display_if_slow(get_string('enrollusers', 'local_exams', $coursefullname));

                foreach($userstoassign as $key=>$adduser){
                    $product = $DB->get_record('tool_products', ['referenceid' => $profileid, 'category' => 2]);
                    $userreq = (new \tool_product\product)->userprofilecountry((array)$product, $adduser);
                    if (!empty($userreq)) {
                        $username = $DB->get_field('user', 'username', ['id' => $adduser]);
                        echo $OUTPUT->notification(get_string('userreqnotmeet', 'local_exams', $username),'danger');
                    } else {
                        $progressbar->start_html();
                        $progressbar->start_progress('',count($userstoassign)-1);
                        $progressbar->progress($progress);
                        $progress++;
                        $this->exam_enrollmet($params['profileid'], $adduser, $params['scheduleid'], null, null, null, null, $product->id); 

                        $roleid = $DB->get_field('role', 'id', ['shortname' => 'trainee']);                        

                        (new learningtracks)->exam_enrolment($params['examid'], $profileid, $roleid,$adduser);

                        $progressbar->end_html();

                        $result=new stdClass();
                        $result->changecount=$progress;
                        $result->course=$course->fullname;

                        echo $OUTPUT->notification(get_string('enrolluserssuccess', 'local_exams', format_string($result->course)),'success');
                    }
                    if($language == 'ar')
                    {
                        preg_match('/{mlang ar}(.*?){mlang}/', $course->fullname, $match);
                        $coursefullnamear =  $match[1];
                        $coursefullname = $coursefullnamear;
                    }

                    $progress = 0;
                    $progressbar = new \core\progress\display_if_slow(get_string('enrollusers', 'local_exams', $coursefullname));
                    $button = new single_button($PAGE->url, get_string('click_continue','local_exams'), 'get', true);
                    $button->class = 'continuebutton';
                    echo $OUTPUT->render($button);
                    die();
                }                
            }
        }
    }
    public function unenrol_exam($remove, $profileid, $params)
    {
        global $DB, $PAGE, $OUTPUT;
        $type = 'exam_unenroll';
        // $availableseats = $this->availableseats($hallreservationid);
        $userstounassign = $remove;
        $courseid = $DB->get_field('local_exams', 'courseid', ['id' => $params['examid']]);
        $course = $DB->get_record('course', ['id' => $courseid], '*', MUST_EXIST);
        if (!empty($userstounassign)) {
            $progress = 0;
            $progressbar = new \core\progress\display_if_slow(get_string('un_enrollusers', 'local_exams',$course->fullname));
            $progressbar->start_html();
            $progressbar->start_progress('', count($userstounassign)-1);
            foreach($userstounassign as $key=>$removeuser){
                $progressbar->progress($progress);
                $progress++;
          
                $this->exam_unenrollmet($profileid, $removeuser);

                $roleid = $DB->get_field('role', 'id', ['shortname' => 'trainee']);    

                (new learningtracks)->exam_unenrol($params['examid'], $profileid, $roleid,$removeuser);

            }
            $progressbar->end_html();
            $result=new stdClass();
            $result->changecount=$progress;
            $result->course=$course->fullname; 
            echo $OUTPUT->notification(get_string('unenrolluserssuccess', 'local_exams',format_string($result->course)),'success');
            $button = new single_button($PAGE->url, get_string('click_continue','local_exams'), 'get', true);
            $button->class = 'continuebutton';
            echo $OUTPUT->render($button);
            die();
        }
    }


    public function availableseats($profileid, $userid=false) {
        global $DB, $USER;
        $systemcontext = context_system::instance();
        $profile = $DB->get_record('local_exam_profiles', ['id' => $profileid]);
        $exam = $DB->get_record('local_exams', ['id' => $profile->examid]);

        if(is_siteadmin() || has_capability('local/organization:manage_examofficial', $systemcontext)) {
            $enrolled = $this->entity_enrolled($profileid, $USER->id);
            $purchasedseats=(new \tool_product\product)->purchasedseats_check('local_exam_profiles','id',$profileid);
            $approvedseats=(new \tool_product\product)->approvedseats_check('local_exam_profiles','id',$profileid);

            if ($purchasedseats == 0) {
                $availableseats=$profile->seatingcapacity - $enrolled;
            } else {
                $availableseats=$profile->seatingcapacity - ($approvedseats + $enrolled);
            }

        } else {
            if($exam->examprice == 0) {
                $profileseats =  $DB->get_field('local_exam_profiles', 'seatingcapacity', ['id' => $profileid]);
                $enrolledusers = $this->entity_enrolled($profileid);
                $availableseats = $profileseats - $enrolledusers;
            } else {
                $availableseats=(new \tool_product\product)->availableseats_check('local_exam_profiles','id', $profileid);
            }
        }

        return $availableseats;
    }

    public function is_enrolled($examid,$userid) {
        global $DB;

        $sql = 'SELECT ee.id FROM {exam_enrollments} as ee 
        WHERE ee.enrolstatus=1 AND ee.examid=:examid AND ee.userid=:userid ';
        $enrolled = $DB->record_exists_sql($sql, ['examid' => $examid, 'userid' => $userid]);
        if($enrolled){
            return true;
        }
        return false;
    }

    public function is_enrolled_grievance($referenceid, $userid) {
        global $DB;

        $sql = " SELECT id FROM {exam_grievance_payments} WHERE grievanceid = :id AND usercreated = :userid  ";

        $enrolled = $DB->record_exists_sql($sql, ['id' => $referenceid, 'userid' => $userid]);
        
        if($enrolled){
            return true;
        }
        return false;
    }


    public function uu_validate_user_upload_columns(csv_import_reader $cir, $stdfields, $profilefields, $returnurl)
    {
        $columns = $cir->get_columns();
        if (empty($columns)) {
            $cir->close();
            $cir->cleanup();
            print_error('cannotreadtmpfile', 'error', $returnurl);
        }
        if (count($columns) < 1) {
            $cir->close();
            $cir->cleanup();
            print_error('csvfewcolumns', 'error', $returnurl);
        }
        $processed = array();
        foreach ($columns as $key => $unused) {
            $field = $columns[$key];
            $lcfield = false;
            if (in_array($field, $stdfields)) {
                // standard fields are only lowercase
                $newfield = $field;
            } else {
                $cir->close();
                $cir->cleanup();
                print_error('invalidfieldname', 'error', $returnurl, $field);
                $eventparams = array('context' => \context_system::instance(),'other'=>array('name'=>$field));
                $event = \local_competency\event\header_missing::create($eventparams);// ... code that may add some record snapshots
                $event->trigger();
            }
            if (in_array($newfield, $processed)) {
                $cir->close();
                $cir->cleanup();
                print_error('duplicatefieldname', 'error', $returnurl, $newfield);
            }
            $processed[$key] = $newfield;
        }
        return $processed;
    }
    public function formatdata_validation($orgid, $data, $linenum, &$formatteddata)
    {
        global $DB, $USER;
        $warnings = array(); // Warnings List
        $errors = array(); // Errors List
        $mfields = array(); // mandatory Fields
        $formatteddata = new stdClass(); //Formatted Data for inserting into DB

        $result=new stdClass();
        $record = $DB->get_record('local_exams', ['code' => $data->code]);
        if($record) {
            $formatteddata->id = $record->id;
        }
        if(empty($data->exam)) {
            $result->column='Exam';
            $result->linenum=$linenum;
            $errors[] = get_string('notbeempty','local_exams', $result);
            $eventparams = array('context' => \context_system::instance(),'objectid'=>$linenum,'other'=>array('name'=>'Exam'));
            $event = \local_exams\event\missing_field::create($eventparams);// ... code that may add some record snapshots
            $event->trigger();
        } elseif(strlen($data->exam) < 3) {
            $errors[] = get_string('examvalidate','local_exams');
        } else {
            $formatteddata->exam = $data->exam;
        }

        if(empty($data->examnamearabic)) {
            $result->column='examnamearabic';
            $result->linenum=$linenum;
            $errors[] = get_string('notbeempty','local_exams', $result);
            $eventparams = array('context' => \context_system::instance(),'objectid'=>$linenum,'other'=>array('name'=>'examnamearabic'));
            $event = \local_exams\event\missing_field::create($eventparams);// ... code that may add some record snapshots
            $event->trigger();
        } else {
            $formatteddata->examnamearabic = $data->examnamearabic;
        }

        if(empty($data->code)) {
            $result->column='code';
            $result->linenum=$linenum;
            $errors[] = get_string('notbeempty','local_exams', $result);
            $eventparams = array('context' => \context_system::instance(),'objectid'=>$linenum,'other'=>array('name'=>'code'));
            $event = \local_exams\event\missing_field::create($eventparams);// ... code that may add some record snapshots
            $event->trigger();
        } else {
            $formatteddata->code = $data->code;
        }

        if(empty($data->old_id)) {
            $result->column='OLD_ID';
            $result->linenum=$linenum;
            $errors[] = get_string('notbeempty','local_exams', $result);
            $eventparams = array('context' => \context_system::instance(),'objectid'=>$linenum,'other'=>array('name'=>'OLD_ID'));
            $event = \local_exams\event\missing_field::create($eventparams);// ... code that may add some record snapshots
            $event->trigger();
        } else {
            $formatteddata->old_id = $data->old_id;
        }

        $formatteddata->certificatevalidity = $data->certificatevalidity;

        if ( !is_numeric ($data->examprice)) {
            $result->columnname='Exam Price';
            $result->linenum=$linenum;
            $errors[]= get_string('columnnumaric', 'local_exams', $result);
            $eventparams = array('context' => \context_system::instance(),'objectid'=>$linenum,'other'=>array('name'=>'Exam Price'));
            $event = \local_exams\event\invalid_field::create($eventparams);// ... code that may add some record snapshots
            $event->trigger();
        } elseif($data->examprice < 0) {
            $result->column='Exam Price';
            $result->linenum=$linenum;
            $errors[] = get_string('notnegative','local_exams', $result);
            $eventparams = array('context' => \context_system::instance(),'objectid'=>$linenum,'other'=>array('name'=>'Exam Price'));
            $event = \local_exams\event\invalid_field::create($eventparams);// ... code that may add some record snapshots
            $event->trigger();
        }  elseif($data->examprice > 2) {
            $result->linenum=$linenum;
            $errors[] = get_string('invalidexamprice','local_exams', $result);
            $eventparams = array('context' => \context_system::instance(),'objectid'=>$linenum,'other'=>array('name'=>'Exam Price'));
            $event = \local_exams\event\invalid_field::create($eventparams);// ... code that may add some record snapshots
            $event->trigger();
        } else {
            $formatteddata->examprice = $data->examprice;
        }

        $sprice = $data->sellingprice;
        $aprice = $data->actualprice;

        if($data->examprice == 1) {  
            if(!is_numeric($sprice)) {
                $result->columnname='Selling price';
                $result->linenum=$linenum;
                $errors[] = get_string('columnnumaric','local_exams', $result);
                $eventparams = array('context' => \context_system::instance(),'objectid'=>$linenum,'other'=>array('name'=>'Selling price'));
                $event = \local_exams\event\invalid_field::create($eventparams);// ... code that may add some record snapshots
                $event->trigger();
            } elseif($aprice > $sprice) {
                $result->linenum=$linenum;
                $errors[] = get_string('apricemoreinbulkupload','local_exams', $result);
                $eventparams = array('context' => \context_system::instance(),'objectid'=>$linenum,'other'=>array('name'=>'Selling price'));
                $event = \local_exams\event\invalid_field::create($eventparams);// ... code that may add some record snapshots
                $event->trigger();
            }
            if(!is_numeric($aprice)) {
                $result->columnname='Actual price';
                $result->linenum=$linenum;
                $errors[] = get_string('columnnumaric','local_exams', $result);
                $eventparams = array('context' => \context_system::instance(),'objectid'=>$linenum,'other'=>array('name'=>'Actual price'));
                $event = \local_exams\event\invalid_field::create($eventparams);// ... code that may add some record snapshots
                $event->trigger();   
            } else {
                $formatteddata->examprice = $data->examprice;
                $formatteddata->sellingprice = $sprice;
                $formatteddata->actualprice = $aprice;
            }
        } elseif($data->examprice == 0) {
            if($sprice != 0 || $aprice != 0) {
                $errors[] = get_string('priceiszero','local_exams');
                $eventparams = array('context' => \context_system::instance(),'objectid'=>$linenum,'other'=>array('name'=>'Price'));
                $event = \local_exams\event\invalid_field::create($eventparams);// ... code that may add some record snapshots
                $event->trigger();   
            } else {
                $formatteddata->examprice = $data->examprice;
                $formatteddata->sellingprice = $sprice;
                $formatteddata->actualprice = $aprice;
            }
        }

        if (empty($data->taxfree)) {
            $formatteddata->tax_free = 0;            
        } elseif(!is_numeric($data->taxfree) || $data->taxfree > 1) {
            $result->linenum=$linenum;
            $errors[] = get_string('taxfreevalidation','local_exams', $result);
            $eventparams = array('context' => \context_system::instance(),'objectid'=>$linenum,'other'=>array('name'=>'Sector'));            
        } else {
            $formatteddata->tax_free = $data->taxfree;
        }

        $formatteddata->programdescription = !empty($data->description) ? $data->description : NULL;

        if(empty($data->sectors)) {
            $formatteddata->sectors = null;
            $formatteddata->targetgroup = 0;
        } else {
            $sectorsdata = str_replace(':', '\',\'', $data->sectors);
            $sectors = $DB->get_fieldset_sql("SELECT id FROM {local_sector} WHERE code IN ('{$sectorsdata}') ");
            if( !$sectors ) {
                $result->linenum=$linenum;
                $errors[] = get_string('sectorsnotpresent','local_exams', $result);
                $eventparams = array('context' => \context_system::instance(),'objectid'=>$linenum,'other'=>array('name'=>'Sector'));
                $event = \local_exams\event\invalid_field::create($eventparams);// ... code that may add some record snapshots
                $event->trigger();  
            } else {
                $formatteddata->sectors = $sectors;
                if (!empty($data->jobfamilies)) {
                    $jfamilies = str_replace(':', ',', $data->jobfamilies);
                    $jfamiliescount = COUNT(explode(',', $jfamilies));
                    $jobfamilies = str_replace(':', '\',\'', $data->jobfamilies);
                    $sql = "SELECT ljf.id 
                            FROM {local_jobfamily} ljf
                            JOIN {local_segment} lsg ON lsg.id = ljf.segmentid 
                            JOIN {local_sector} ls ON ls.id = lsg.sectorid 
                            WHERE  ljf.code IN ('{$jobfamilies}') ";
                    $jobfamilyids = $DB->get_fieldset_sql($sql);
                    if (!$jobfamilyids || $jfamiliescount != COUNT($jobfamilyids)) {
                        $result->linenum=$linenum;
                        $errors[] = get_string('jobfamiliesnotmatched','local_exams', $result);
                    } else {
                        $formatteddata->targetgroup = $jobfamilyids;
                    }
                }
            }
        }

        if(empty($data->requirements)) {
            $formatteddata->requirements = null;
        } else {
            $requirements = str_replace(':', ',', $data->requirements);
            $examrequirements = explode(',', $requirements);

            foreach($examrequirements as $examrequirement) {
                $examcode = $DB->get_field('local_exams', 'id', ['code' => $examrequirement]);
                if( !$examcode ) {
                    $result->exam=$examrequirement;
                    $result->linenum=$linenum;
                    $errors[] = get_string('invalidexam','local_exams', $result);
                    $eventparams = array('context' => \context_system::instance(),'objectid'=>$linenum,'other'=>array('name'=>'Exam'));
                    $event = \local_exams\event\invalid_field::create($eventparams);// ... code that may add some record snapshots
                    $event->trigger();  
                } else {
                    $exmrequirements[] = $examcode;
                }
            }
            $formatteddata->requirements = $exmrequirements;
        }


        if(empty($data->preparationprograms)) {
            $formatteddata->preparationprograms = null;
        } else {
            $preparationprograms = str_replace(':', ',', $data->preparationprograms);
            $programcodes = explode(',', $preparationprograms);

            foreach($programcodes as $programcode) {
                $programcode = $DB->get_field('local_trainingprogram', 'id', ['code' => $programcode]);
                if( !$programcode ) {
                    $result->program=$programcode;
                    $result->linenum=$linenum;
                    $errors[] = get_string('invalidprogram','local_exams', $result);
                    $eventparams = array('context' => \context_system::instance(),'objectid'=>$linenum,'other'=>array('name'=>'Program'));
                    $event = \local_exams\event\invalid_field::create($eventparams);// ... code that may add some record snapshots
                    $event->trigger();  
                } else {
                    $programrequirements[] = $programcode;
                }
            }
            $formatteddata->programs = $programrequirements;
        }

        if(empty($data->clevels)) {
            $formatteddata->clevels = 0;
        } else {
            $sql = "SELECT lc.id
                  FROM {local_competencies} lc
                  WHERE  FIND_IN_SET('$data->clevels', lc.level) ";
            $level = $DB->record_exists_sql($sql);

            if (!$level) {            
                $result->linenum=$linenum;
                $errors[] = get_string('invalidlevel','local_exams', $result);
                $eventparams = array('context' => \context_system::instance(),'objectid'=>$linenum,'other'=>array('name'=>'LEVEL'));
                $event = \local_exams\event\invalid_field::create($eventparams);// ... code that may add some record snapshots
                $event->trigger();            
            } else {            
                $formatteddata->clevels = strtolower($data->clevels);
            }
        }

        if(empty($data->ctype)) {
            $formatteddata->ctype = null;
        } else {
            $ctypes = str_replace(':', ',', $data->ctype);
            $types = explode(',',$ctypes);

            $sql = "SELECT lc.type
                  FROM {local_competencies} lc
                  WHERE  FIND_IN_SET('$data->clevels', lc.level) ";
            $competenciestypesdata = $DB->get_fieldset_sql($sql);    

            foreach($types as $type) {
                $status = in_array($type, $competenciestypesdata);
                if($status != 1) {
                    $result->linenum=$linenum;
                    $errors[] = get_string('invalidctype','local_exams', $result);
                    $eventparams = array('context' => \context_system::instance(),'objectid'=>$linenum,'other'=>array('name'=>'Competency Type'));
                    $event = \local_exams\event\invalid_field::create($eventparams);// ... code that may add some record snapshots
                    $event->trigger(); 
                }
            }
            $formatteddata->ctype = $ctypes;
        }

        if(empty($data->competencies)) {
            $formatteddata->competencies = null;
        } else {
            $ctypes = str_replace(':', '\',\'', $data->ctype);

            $sql = "SELECT replace(lc.code , ' ','') as code
                  FROM {local_competencies} lc 
                  WHERE lc.type IN ('{$ctypes}') AND FIND_IN_SET('$data->clevels', lc.level) ";

            $competencieslist = $DB->get_fieldset_sql($sql);
            $competenciesdata = str_replace(':', ',', $data->competencies);
            $competenciesinfo = explode(',', $competenciesdata);
      
            $count = 0;
            foreach($competenciesinfo as $competency) {
                if( !in_array($competency, $competencieslist) ) {
                    $count++;
                }                
            }
            if( $count > 0 ) {
                $result->linenum=$linenum;
                $errors[] = get_string('invalidcompetency','local_exams', $result);
                $eventparams = array('context' => \context_system::instance(),'objectid'=>$linenum,'other'=>array('name'=>'Competency'));
                $event = \local_exams\event\invalid_field::create($eventparams);// ... code that may add some record snapshots
                $event->trigger(); 
            } else {


                $competenciesdata = explode(':',$data->competencies);
              
                $competencies = array_filter($competenciesdata);



                if(!empty($competencies)) {

                    $ctypes = str_replace(':', '\',\'', $data->ctype);

                    $ctypequery = array();
                    foreach ($competencies as $competency) {

                        $ctypequery[] = " CONCAT(',',lc.code,',') LIKE CONCAT('%,','$competency',',%') "; 
                    }
                    $ctypequeeryparams =implode('OR',$ctypequery);
                    $formsql = ' AND ('.$ctypequeeryparams.') ';

                    $sql = "SELECT lc.id
                          FROM {local_competencies} lc 
                          WHERE FIND_IN_SET('$data->clevels', lc.level) AND lc.type IN ('{$ctypes}')  ";

                    $competencyids = $DB->get_fieldset_sql($sql.$formsql);
                      

                   $formatteddata->competencies =  $competencyids;
               }
  
            }
        }
        
        if(empty($data->competencyweights)) {
            $formatteddata->competencyweights = null;
        } else {
            $formatteddata->competencyweights = $data->competencyweights;
        }

        $formatteddata->type = $data->type;
        $formatteddata->ownedbystatus = 1;
        $formatteddata->ownedby = $data->ownedby;
        $formatteddata->additionalrequirements = !empty($data->additionalrequirements) ? $data->additionalrequirements : NULL;
        $formatteddata->attachedmessage = !empty($data->attachedmsg) ? $data->attachedmsg : NULL;
        $formatteddata->noofattempts = !empty($data->noofattempts) ? $data->noofattempts : 0;
        $formatteddata->appliedperiod = $data->appliedon;
        $formatteddata->targetaudience = $data->targetaudience;
        $formatteddata->timecreated = time();

        return compact('mfields', 'errors', 'formatteddata');
    }

    public function add_bulkuploadexams($validations=null, $schedule_data=null)
    {
        global $DB, $USER;
        if (count($validations['errors']) > 0) {
            echo implode(' ', $validations['errors']);
        }
        if (!empty($validations['errors']) > 0 || !empty($validations['mfields']) > 0) {
            $errorscount++;
            $mfieldscount++;
        } else {
           
                // $record = $DB->get_record('local_exams', ['code' => $validations['formatteddata']->code]);
                // // print_object($validations['formatteddata']);exit;
                // if( $record ) {
                //     $uploadusers = $DB->update_record('local_exams', $validations['formatteddata']);
                //     $course = $this->create_update_course($validations['formatteddata']);
                // } else {
        
            $validations['formatteddata']->ctype = explode('*',$validations['formatteddata']->ctype);
            $validations['formatteddata']->competencylevel =(is_array($validations['formatteddata']->competencies)) ? $validations['formatteddata']->competencies : explode('*',$validations['formatteddata']->competencies);

            $uploadeduser = $this->add_update_exam($validations['formatteddata']);
            // $transaction = $DB->start_delegated_transaction();
            // $course = $this->create_update_course($validations['formatteddata']);
            // $validations['formatteddata']->courseid = $course->id;
            // $uploadusers = $DB->insert_record('local_exams', $validations['formatteddata']);
            // $eventparams = array('context' => \context_system::instance(),'objectid'=>$uploadusers,'other'=>array('name'=>$validations['formatteddata'],'family'=>'Exam'));
            if($uploadeduser) {
                $eventparams = array('context' => \context_system::instance(),'objectid'=>$uploadeduser,'other'=>array('name'=>$validations['formatteddata'],'family'=>'Exam'));
                $event = \local_sector\event\row_inserted::create($eventparams);// ... code that may add some record snapshots
                $event->trigger();
                return true;
            }
            return false;
        }
    }

    public function delete_exam($examid) {
        global $DB;
        try {
            $trainsaction = $DB->start_delegated_transaction();
            $DB->delete_records('hall_reservations', ['typeid' => $examid, 'type' => 'exam']);
            $DB->delete_records('exam_enrollments', ['examid' => $examid]);
            $DB->delete_records('exam_completions', ['examid' => $examid]);
            $DB->delete_records('local_exam_grievance', ['examid' => $examid]);
            $courseid =(int) $DB->get_field('local_exams','courseid',array('id'=>$examid));
            if($courseid) {
              delete_course($courseid,false);
            }
            $this->delete_examprofile($examid);
            $DB->delete_records('local_exams', array('id' => $examid));
            $trainsaction->allow_commit();
        } catch(moodle_exception $e){
            $DB->rollback($e);
        }
        
        return true;
    }

    public function examname($examid) {
        global $DB;
        $lang = current_language();
        if( $lang == 'ar' ){
            $examname = $DB->get_field('local_exams', 'examnamearabic', ['id' => $examid]);
        } else {
            $examname = $DB->get_field('local_exams', 'exam', ['id' => $examid]);
        }
        return $examname;
    }

    public function entity_enrolled($profileid, $userid=false)
    {
        global $DB;
        $systemcontext = context_system::instance();
        $traineerole = $DB->get_field('role', 'id', ['shortname'=>'trainee']);
        $sql = "SELECT COUNT(ee.id) as enrolled 
                  FROM {exam_enrollments} ee 
                  JOIN {local_users} lu ON lu.userid = ee.userid 
                  JOIN {role_assignments} AS roa ON roa.userid = lu.userid AND roa.userid = lu.userid
                 WHERE roa.roleid = $traineerole AND roa.contextid = $systemcontext->id AND ee.enrolstatus=1 AND ee.profileid =".$profileid;
	$user = " ";
        if($userid) {
            if((!is_siteadmin()) && has_capability('local/organization:manage_organizationofficial',$systemcontext)){
                $get_all_orgofficials = $this->get_all_orgofficials($userid);
                $organization = $DB->get_field('local_users', 'organization', ['userid' => $userid]);
                 $orgusers = implode(',',$get_all_orgofficials);
                if ($organization) {
                    $user .= " AND (ee.usercreated IN($orgusers) OR ee.organization = $organization)";
                } else {
                    $user .= " AND ee.usercreated IN ($orgusers) ";
                }
            } else {
                $user .= " AND ee.usercreated = {$userid} ";
            }
            
        }

        return $DB->get_field_sql($sql.$user);
    }

    public function get_all_orgofficials($userid) {
        global $DB, $USER;
        $systemcontext = context_system::instance();
        $organizationofficialroleid = $DB->get_field('role', 'id', array('shortname' => 'organizationofficial'));
        $organization = $DB->get_field('local_users','organization',array('userid'=> $userid));
        $get_all_orgofficial = $DB->get_fieldset_sql(" SELECT lu.userid FROM {local_users} AS lu JOIN {role_assignments} AS roa ON lu.userid = roa.userid  
        WHERE  lu.organization = $organization AND lu.approvedstatus = 2 
        AND lu.deleted = 0  AND roa.contextid = $systemcontext->id 
        AND roa.roleid = $organizationofficialroleid ");
        return $get_all_orgofficial;
    }

    public function enrol_grievance($referenceid, $userid)
    {
        global $DB;
        $grievance_details = $DB->get_record('local_exam_grievance', ['id' => $referenceid]);

        $data = [];
        $data['grievanceid'] = $referenceid;
        $data['status'] = 1;
        $data['usercreated'] = $userid;
        $data['realuser'] = ($USER->realuser > 0) ? $USER->realuser :0;
        $data['timecreated'] = time();
        $record = $DB->insert_record('exam_grievance_payments', $data);
        if($record){
            return true;
        } else {
            return false;
        }
    }

    public function examusers($examid='',$profileid)
    {
        global $DB, $PAGE, $OUTPUT,$CFG;
        $systemcontext = context_system::instance();
        $PAGE->set_context($systemcontext);
        require_once($CFG->dirroot . '/local/exams/lib.php');
        $renderer = $PAGE->get_renderer('local_exams');
        $filterparams  = $renderer->get_catalog_examusers(true);
        $filterparams['submitid'] = 'form#filteringform';
        $filterparams['widthclass'] = 'col-md-6';
        $filterparams['placeholder'] = get_string('searchuser','local_hall');
        if(!is_siteadmin() && has_capability('local/organization:manage_organizationofficial',$systemcontext)) {
            $filterparams['orgoff'] = true;
        }
        $fform = examusers_filters_form($filterparams);
        $filterparams['filterform'] = $fform->render();
        $filterparams['examid'] = $examid;
        $filterparams['profileid'] = $profileid;
        $filterparams['globalinput'] = $renderer->global_filter($filterparams);
        $filterparams['examdetails'] = $renderer->get_catalog_examusers();
        $filterparams['cfgurl'] = $CFG->wwwroot;
        $renderer->listofexamusers($filterparams);
    }

    
    public function get_listof_examusers($stable, $filterdata)
    {
       global $DB, $USER, $CFG;
       $systemcontext = context_system::instance();
       $selectsql = "SELECT ee.*,lu.firstname,lu.lastname,
                            lu.firstnamearabic,
                            lu.lastnamearabic,
                            lu.middlenameen ,
                            lu.middlenamearabic,
                            lu.thirdnameen,
                            lu.thirdnamearabic,
                            lu.email, lu.id_number,ee.enrolltype
                        FROM {exam_enrollments} ee
                        JOIN {local_users} lu ON lu.userid = ee.userid ";
        $countsql = "SELECT COUNT(ee.id) 
                        FROM {exam_enrollments} ee
                        JOIN {local_users} lu ON lu.userid = ee.userid "; 
        $formsql = ($filterdata->profileid) ? " WHERE ee.examid = $filterdata->examid AND ee.profileid = $filterdata->profileid " : " WHERE ee.examid = $filterdata->examid "; 

        if(!is_siteadmin() && has_capability('local/organization:manage_organizationofficial',$systemcontext)) {
            $organization = $DB->get_field('local_users','organization',array('userid'=>$USER->id));
            $formsql.= "  AND ee.enrolltype !=1 AND lu.organization = $organization  AND lu.deleted = 0 AND lu.approvedstatus = 2 ";
            //$formsql .= " AND ee.usercreated = {$USER->id} ";
        } else {
            $formsql.= " AND ee.enrolltype !=1 ";
        }
        $params = [];
        if(isset($filterdata->search_query)) {
            $formsql .= " AND (lu.firstname LIKE :firstnamesearch OR lu.lastname LIKE :lastnamesearch OR lu.firstnamearabic LIKE :firstnamearabicsearch OR lu.lastnamearabic LIKE :llastnamearabicsearch OR lu.middlenameen LIKE :middlenameensearch OR lu.middlenamearabic LIKE :middlenamearabicsearch OR lu.thirdnameen LIKE :thirdnameensearch OR lu.thirdnamearabic LIKE :thirdnamearabicsearch OR lu.email LIKE :emailsearch  OR lu.id_number LIKE :idnumbersearch) ";
            $searchparams = array(
                                     'firstnamesearch' => '%'.trim($filterdata->search_query).'%',
                                     'lastnamesearch' => '%'.trim($filterdata->search_query).'%', 
                                     'firstnamearabicsearch' => '%'.trim($filterdata->search_query).'%', 
                                     'llastnamearabicsearch' => '%'.trim($filterdata->search_query).'%', 
                                     'middlenameensearch' => '%'.trim($filterdata->search_query).'%', 
                                     'middlenamearabicsearch' => '%'.trim($filterdata->search_query).'%', 
                                     'thirdnameensearch' => '%'.trim($filterdata->search_query).'%', 
                                     'thirdnamearabicsearch' => '%'.trim($filterdata->search_query).'%', 
                                     'emailsearch' => '%'.trim($filterdata->search_query).'%',
                                     'idnumbersearch' => '%'.trim($filterdata->search_query).'%',
                                     'profilecodesearch' => '%'.trim($filterdata->search_query).'%'
                                 );
        } else {
            $searchparams = array();
        }
        $params = array_merge($searchparams);
        $formsql .= " ORDER BY ee.timecreated DESC";
        $enrolements = $DB->get_records_sql($selectsql.$formsql, $params, $stable->start,$stable->length);
        $totalenrolements = $DB->count_records_sql($countsql.$formsql, $params);
        $examusers = [];
        $count = 0;
        foreach($enrolements as $enrolement) {
            $examuser = [];
            $examuser["id"] = $enrolement->id;
            $profiledata = $DB->get_record_sql(" SELECT ex.profilecode, ex.quizid, ex.duration, ex.id AS profileid, le.courseid, ex.passinggrade,
            euh.hallscheduleid,euh.timecreated, euh.examdate, euh.id as scheduleid, euh.attemptid, euh.userid 
            FROM {local_exam_profiles} ex 
            LEFT JOIN {local_exam_userhallschedules} euh ON euh.profileid = ex.id
            JOIN {local_exams} le ON le.id = euh.examid 
            WHERE euh.examid = $enrolement->examid AND euh.userid = $enrolement->userid ORDER BY euh.id DESC");

           // $schedulestarttime = $DB->get_field('hallschedule', 'starttime', ['id' => $profiledata->hallscheduleid]);
            if ($profiledata->hallscheduleid) {
                $hallschedule = $DB->get_record_sql(" SELECT hs.starttime, h.name AS hallname FROM {hallschedule} hs 
                JOIN {hall} h ON h.id = hs.hallid WHERE hs.id = $profiledata->hallscheduleid ");
            }

            $scheduletime = !empty($hallschedule->starttime) ? date("h:i",$hallschedule->starttime) : 0;
            $schedulestarttimemeridian = !empty($hallschedule->starttime) ? date('a',$hallschedule->starttime) : 0;
            $schedulestartmeridian = ($schedulestarttimemeridian == 'am')?  get_string('am','local_exams'):get_string('pm','local_exams');
            $examuser["hall"] = ($hallschedule->hallname) ? $hallschedule->hallname:'--';
            $examuser["profilecode"] = $profiledata->profilecode;//$DB->get_field('local_exam_profiles','profilecode',['id'=>$enrolement->profileid]);
            $examuser["userid"] = $DB->get_field('local_users', 'id_number', ['userid' => $enrolement->userid]);
            $user = $DB->get_record('user',  ['id' => $enrolement->userid] );
            $localuserrecord = $DB->get_record('local_users',['userid'=> $enrolement->userid]);
            $fname = ($localuserrecord)? ((current_language() == 'ar') ? $localuserrecord->firstnamearabic.' '.$localuserrecord->middlenamearabic.' '.$localuserrecord->thirdnamearabic.' '.$localuserrecord->lastnamearabic  : $localuserrecord->firstname.' '.$localuserrecord->middlenameen.' '.$localuserrecord->thirdnameen.' '.$localuserrecord->lastname) :fullname($DB->get_record('user',array('id'=>$enrolement->userid)));    
            $examuser["user"] = $fname; 
            $examuser["email"] = $user->email;
            $examuser["enrolledon"] = userdate($profiledata->timecreated, get_string('strftimedatemonthabbr', 'core_langconfig'));
            $attempsnumber = $this->quiz_attemptscount($enrolement->examid, $enrolement->userid);
            $completiondate = $DB->get_field('exam_completions', 'completiondate', ['examid' => $enrolement->examid, 'userid' => $enrolement->userid ]);
            $hallschedulecout = $DB->count_records('local_exam_userhallschedules',['examid' => $enrolement->examid, 'userid' => $enrolement->userid]);
           
            if($profiledata->quizid) {

                $sql = "SELECT ROUND(gg.finalgrade, 2) as finalgrade
                FROM {grade_items} gi
                JOIN {grade_grades} gg on gg.itemid = gi.id
                WHERE gi.courseid = {$profiledata->courseid} AND gi.itemtype = 'mod' AND gi.itemmodule  = 'quiz'
                AND gi.iteminstance = {$profiledata->quizid} AND gg.userid = {$enrolement->userid} ";
                $grade = $DB->get_field_sql($sql);
            }
            $examuser["completeddate"] = ($grade >= $profiledata->passinggrade && !empty($completiondate)) ? userdate($completiondate, get_string('strftimedatemonthabbr', 'core_langconfig')) : '--';

            $sql = "SELECT leu.*, h.starttime, h.endtime 
                      FROM {local_exam_userhallschedules} leu 
                      JOIN {hallschedule} h ON h.id = leu.hallscheduleid
                     WHERE examid = $enrolement->examid AND userid = $enrolement->userid
                     ORDER BY id DESC ";
            $schedulerecord = $DB->get_record_sql($sql);
            if (!empty($schedulerecord->id)) {
                $grade = $this->gradestatus($schedulerecord->id);
            } else {
                $grade = '--';
            }

            if (is_numeric($grade)) {
                $examuser["grade"] =  $grade.' %';
            } else {
                $examuser["grade"] =  $grade;
            }

            $examrecord = $DB->get_record('local_exams',['id'=>$enrolement->examid]);
            $examuser["examid"] = $enrolement->examid;
            $examuser["profileid"] = $profiledata->profileid;
            $examuser["replacorcanceluerid"] = $enrolement->userid;
            $id_number = $DB->get_field('user','idnumber',['id'=>$enrolement->userid]);
            $examuser["replaceorcanceluseridnumber"] = $id_number ? $id_number : 0;
            $examuser["ownedby"] = $examrecord->ownedby;
            $examuser["examprice"] = $examrecord->examprice;
            $examuser["issiteadmin"] = (is_siteadmin() || has_capability('local/organization:manage_examofficial', $systemcontext)) ? 1: 0;
            
            $examuser["currentuserorgoff"] = (!is_siteadmin() && has_capability('local/organization:manage_organizationofficial',$systemcontext)) ? 1 : 0;

            $exam = $DB->get_record('local_exams', ['id' => $enrolement->examid]);
            if ($attempsnumber == 0) {
                $category = self::EXAMS;
                $toolproduct = $DB->get_record('tool_products', ['category' => $category, 'referenceid' => $profiledata->profileid]);
            } else {
                $attemptnum = ++$attempsnumber;
                $category = self::EXAMATTEMPT;
                $attemptid = $DB->get_field('local_exam_attempts', 'id', ['examid' => $enrolement->examid, 'attemptid' => $attemptnum]);
                $toolproduct = $DB->get_record('tool_products', ['category' => $category, 'referenceid' => $attemptid]);
            }
            $examuser["productid"] = ($toolproduct) ? $toolproduct->id: 0;
            $examuser["productprice"] = ($toolproduct) ? $toolproduct->price: 0;
            $examuser["replacementfee"] = 100;
            $examuser["siteurl"] = $CFG->wwwroot;
            $examuser["enrolltype"]=$enrolement->enrolltype;
            $examuser["ispaidexam"] = ($examrecord->examprice == 1) ? true : false;
            $examuser["examdate"] = !empty($schedulerecord->examdate) ? userdate($schedulerecord->examdate, get_string('strftimedate','core_langconfig')).' '.userdate($schedulerecord->starttime, get_string('strftimetime12', 'langconfig')) : '--';
            $sdate = strtotime(userdate($enrolement->examdate,'%Y-%m-%d'));
            $curr_date = strtotime(userdate(time(),'%Y-%m-%d'));
            $remainingdays = floor(($sdate - $curr_date) / (60 * 60 * 24));
            $attemptid = $DB->get_field('local_exam_userhallschedules','attemptid',['examid'=>$enrolement->examid,'profileid'=>$profiledata->profileid,'userid'=>$enrolement->userid]);
            $hsexamdate = $DB->get_field('local_exam_userhallschedules','examdate',['examid'=>$enrolement->examid,'profileid'=>$profiledata->profileid,'userid'=>$enrolement->userid]);

            $examuser["remainingdays"] = ($enrolement->examdate > 0)?$remainingdays: 0 ;
            $examuser["replacebuttonview"] = ($examrecord->examprice == 0 || ($examrecord->examprice == 1  && $remainingdays >= 2)) ? true: false;

            if (is_siteadmin() || has_capability('local/organization:manage_examofficial', $systemcontext)){ 
                $examuser["cancelbuttonview"] = true;
                $examuser["cancelbuttonoption"] = true;
            } else {
                $examuser["cancelbuttonview"] = ($examrecord->examprice == 0 || ($examrecord->examprice == 1  && $hsexamdate > 0 && $remainingdays >= 1)) ? true: false;                
                $examuser["cancelbuttonoption"] = ($attempsnumber == 0) ? true: false;
            }


            $examuser["examname"] = (current_language() == 'ar') ? $examrecord->examnamearabic : $examrecord->exam;

            $enrolleduserid = $enrolement->usercreated;
            $enrolleduseroleinfo = $DB->get_record_sql('SELECT rol.* FROM {role} rol 
                                                JOIN {role_assignments} rola ON rola.roleid = rol.id
                                                WHERE rola.userid =:userid and contextid =:contextid',['userid'=>$enrolleduserid,'contextid'=>$systemcontext->id]);

            $examuser["orgenrolled"]=($enrolleduseroleinfo->shortname == 'organizationofficial') ? true : false;
            $examuser["orgofficialenrolled"]=($enrolleduseroleinfo->shortname == 'organizationofficial') ? 1 : 0;

            $examuser["enrolledrole"]=(empty($enrolleduseroleinfo->shortname) || $enrolleduseroleinfo->shortname == 'examofficial') ?  'admin' :  $enrolleduseroleinfo->shortname;

            $examuser["enrolledbyadmin"]=(empty($enrolleduseroleinfo->shortname) || $enrolleduseroleinfo->shortname == 'examofficial') ?  true :  false;
            //$examuser["reschedulebuttonview"] = ($examrecord->examprice == 0 || ($examrecord->examprice == 1 && $enrolement->examdate > 0 && $attemptid == 0 && $remainingdays > 2)) ? true: false;

            if (is_siteadmin() || !is_siteadmin() && has_capability('local/organization:manage_organizationofficial',$systemcontext)) {
                $examuser["scheduleoption"] = ($attemptid == 0 && $enrolement->examdate == 0) ? true: false;
                $examuser["reschedulebuttonview"] = (($remainingdays > 2)) ? true: false;
            } else {
                $examuser["scheduleoption"] = false;
                $examuser["reschedulebuttonview"] = ($examrecord->examprice == 0 || ($examrecord->examprice == 1 && $enrolement->examdate > 0 && $attemptid == 0 && $remainingdays > 2)) ? true: false;
            }

            $examuser["canapplypolicies"] = ((is_siteadmin() && $enrolleduseroleinfo->shortname == 'organizationofficial') || $enrolement->enrolltype == 2 ) ?  true : false;

            //$moduleid = $DB->get_field_sql(" SELECT examid FROM {exam_completions} WHERE examid = $enrolement->examid AND userid = $enrolement->userid AND completion_status = 2 AND profileid = $enrolement->profileid");
            
            $certid = $DB->get_field('tool_certificate_issues', 'code', array('moduleid'=> $enrolement->examid,'userid'=>$user->id,'moduletype'=>'exams'));
            $examuser["certid"] = ($grade >= $profiledata->passinggrade && $certid)? $certid : 0;
            $examuser["disablereschedulebutton"] = $exam->ownedby !='FA'? true : false;

            $scheduledetails = $DB->get_record('hallschedule', ['id'=>$profiledata->hallscheduleid]);
            $startedtime = (strtotime($schedulerecord->examdate)+userdate(($scheduledetails->starttime), '%H')*3600 + userdate(($scheduledetails->starttime), '%M')*60);
            $endedtime = (($schedulerecord->examdate)+userdate(($scheduledetails->starttime), '%H')*3600 + userdate(($scheduledetails->starttime), '%M')*60)+$profiledata->duration;
            if (time() > $endedtime  && $profiledata->examdate > 0) {
                $examuser["policiesdisabled"] = true;
            } else {
                $examuser["policiesdisabled"] = false;
            }
            $enrolleduserhadsecondpurchase = $DB->record_exists('local_exam_attemptpurchases', ['examid' => $enrolement->examid, 'userid'=> $enrolleduserid]);
            
            // if (!is_siteadmin() && has_capability('local/organization:manage_organizationofficial',$systemcontext) &&  ($enrolleduseroleinfo->shortname == 'trainee')) {echo "sdfsd";exit;
            //     $examuser["disableallactions"] = true;
            // } else
            if(empty($enrolleduseroleinfo) && $enrolement->enrolltype !=2  && (!is_siteadmin() && has_capability('local/organization:manage_organizationofficial',$systemcontext))) { // Admin enrolled and login user is orgoff
                $examuser["disableallactions"] = true;
            } else {
                $examuser["disableallactions"] = false;
                if($enrolement->enrolstatus == 0){
                    $examuser["disableallactions"] = true;
                    $examuser["waitingforapproval"] = get_string('waitingforapproval', 'local_exams');
                }
            }
            $examuser["scheduleid"] = !empty($profiledata->hallscheduleid) ? $profiledata->hallscheduleid : 0;
            $examdate = !empty($schedulerecord->examdate) ? strtotime(userdate($schedulerecord->examdate,'%Y-%m-%d'))+$schedulerecord->endtime : 0;
            $currdate = time();


            /*if ($currdate > $examdate && !empty($examdate) && (!is_siteadmin() && has_capability('local/organization:manage_organizationofficial',$systemcontext))) {
                $examuser["attemptenrol"] = true;
            } else { 
                $examuser["attemptenrol"] = false;
            }*/
            $examuser["scheduleid"] = !empty($profiledata->hallscheduleid) ? $profiledata->hallscheduleid : 0;
            $userexamdate = (strtotime(userdate($schedulerecord->examdate, '%d-%m-%Y'))+userdate(($schedulerecord->endtime), '%H')*3600 + userdate(($schedulerecord->endtime), '%M')*60);
            $examdate = !empty($schedulerecord->examdate) ? $userexamdate : 0;
            $currdate = time();
            $tot_exam_attempts = $DB->count_records_sql("SELECT COUNT(id) FROM {local_exam_attempts} WHERE examid = $enrolement->examid");
            $user_exam_attempts = $DB->count_records_sql("SELECT COUNT(id) FROM {local_exam_userhallschedules} WHERE examid = $enrolement->examid AND userid = $user->id");
            if ($currdate > $examdate && !empty($examdate) && (!is_siteadmin() && has_capability('local/organization:manage_organizationofficial',$systemcontext) && ($tot_exam_attempts > $user_exam_attempts))) {
                $examuser["attemptenrol"] = true;
            } else {
                $examuser["attemptenrol"] = false;
            }
            if (!empty($schedulerecord->id)) {
                $gradehaving = $this->gradestatus($schedulerecord->id, $examuser["userid"]);
            }

            if (is_numeric($gradehaving) && ($schedulerecord->id >= 0)) {
                $examuser['havinggrade'] = true;
            } elseif ($gradehaving == 'Unknow') {
                $examuser['havinggrade'] = false;
            }
            $examuser['scheduleid'] = $schedulerecord->id;
            $examuser['cangenerateinvoice'] = 1;
            if (!empty($filterdata->attemptnumber)) {
                if ($schedulerecord->attemptid == 0) {
                    $examattemptnumber = 1;
                } else {
                    $examattemptnumber = $DB->get_field('local_exam_attempts', 'attemptid', ['examid'=>$enrolement->examid, 'attemptid'=>$schedulerecord->attemptid]);
                }
                if ($examattemptnumber == $filterdata->attemptnumber && $currdate > $examdate) {
                    $examusers[] = $examuser;  
                }
            } elseif(empty($filterdata->attemptnumber)) {
                $examusers[] = $examuser;
            }

        }
        $coursesContext = array(
            "hascourses" => $examusers,
            "totalenrolements" => $totalenrolements,
            "length" =>  count($examusers),
            "examid" => $filterdata->examid,  
    
        );
        return $coursesContext;
    }

    public function reservationdetails($hallreservationid)
    {
        global $DB;
        $sql =  " SELECT hr.id as reservationid, hr.slotstart, hr.slotend, hr.seats, hr.examdate, hr.hallid, h.* , hr.typeid as examid  
                        FROM {hall_reservations} hr 
                        JOIN {hall} h ON hr.hallid = h.id 
                        WHERE hr.id = $hallreservationid AND hr.status = 1  ";

        return $DB->get_record_sql($sql);
    }

    public function get_exams($stable) {
        global $DB, $PAGE, $OUTPUT,$SESSION;
        $SESSION->lang = ($stable->isarabic == 'true') ?'ar':'en';
        $systemcontext = context_system::instance();
        $PAGE->set_context($systemcontext);
        $selectsql = "SELECT * FROM {local_exams} le"; 
        $countsql  = " SELECT COUNT(le.id) FROM {local_exams} le";
        $formsql = "  WHERE 1=1 AND status = 1 ";

        if(isset($stable->keyword) && trim($stable->keyword) != ''){
            $formsql .= " AND le.exam LIKE :search";
            $searchparams = array('search' => '%'.trim($stable->keyword).'%');
        } else {
            $searchparams = array();
        }

        if(!empty($stable->sectorids)){

            $sectors = str_replace(';', ',', $stable->sectorids);
            $sectorids = explode(',', $sectors);
            $count = 1;
            foreach ($sectorids AS $sector) {
                if($count == 1) {
                    $formsql.=" AND FIND_IN_SET(".$sector.", le.sectors) ";
                } else {
                    $formsql.=" OR FIND_IN_SET(".$sector.", le.sectors) ";
                }
                $count++;
            }

        } 
        if(!empty($stable->competencyid)){
            $competencies = str_replace(';', ',', $stable->competencyid);
            $competencyids = explode(',', $competencies);
            $count = 1;
            foreach ($competencyids AS $competency) {

                if($count == 1) {
                    $formsql.=" AND FIND_IN_SET(".$competency.", le.competencies) ";
                } else {
                    $formsql.=" OR FIND_IN_SET(".$competency.", le.competencies) ";
                }
                $count++;
            }
        }
        if(!empty($stable->jobfamilyids)) {
            $jobfamilyids = str_replace(';', ',', $stable->jobfamilyids);
            $targetgroups = explode(',', $jobfamilyids);

            $count = 1;
            foreach ($targetgroups as $jobfamilyid) {
                if($count == 1) {
                    $formsql.=" AND (FIND_IN_SET(".$jobfamilyid.", le.targetgroup) OR le.targetgroup = '-1') ";
                } else {
                    $formsql.=" OR FIND_IN_SET(".$jobfamilyid.", le.targetgroup) ";
                }
                $count++;
            }
        }

        if(!empty($stable->jobroleid)){

            $jobroleids = str_replace(';', ',', $stable->jobroleid);

            $sql = "SELECT ls.id
                     FROM {local_sector} ls 
                     JOIN {local_segment} lsg ON lsg.sectorid = ls.id
                     JOIN {local_jobfamily} ljb ON ljb.segmentid = lsg.id
                     JOIN {local_jobrole_level} ljrl ON ljrl.jobfamily = ljb.id
                     WHERE ljrl.id IN ($jobroleids) ";
            $sectorsids = $DB->get_fieldset_sql($sql);

            foreach ($sectorsids as $sectorsid) {
                $formsql.=" AND FIND_IN_SET(".$sectorsid.", le.sectors) ";
            }

        }

        $params = array_merge($searchparams);
        $formsql .=" ORDER BY le.id DESC";
        $exams = $DB->get_records_sql($selectsql.$formsql, $params);

        $reviewexms = array();
        $count = 0;
        $halls = [];
        foreach($exams as $exam) {
            global $DB;
            $reviewexms[$count]["id"] = $exam->id;
            $reviewexms[$count]["value"] = $exam->id;

            $reviewexms[$count]["name"] = ($SESSION->lang=='ar') ?  $DB->get_field('local_exams', 'examnamearabic', ['id' => $exam->id]) : $DB->get_field('local_exams', 'exam', ['id' => $exam->id]);
            $reviewexms[$count]["typeName"] = 'امتحان';
            $reviewexms[$count]["typeCode"] = 'Exam';
            $reviewexms[$count]["Description"] = format_text($exam->programdescription, FORMAT_HTML);
            $reviewexms[$count]["navigators"] = NULL;
            $halls[] = $exam->halladdress;
            $count++;
        }

        $halls = array_unique($halls);
        $halldata = [];
        if (!empty($halls)) {
            $halls = explode(',',implode(',',$halls));

            foreach($halls as $hall) {
                $hallinfo = $DB->get_record('hall', ['id' => $hall]);
                $hallrow = [];
                $hallrow['examCenterID'] = $hallinfo->id;
                $hallrow['name'] = $hallinfo->name;
    
                if ($SESSION->lang == 'ar') {
                    $hallrow['cityName'] = (new \local_hall\hall())->listofcities($hallinfo->city, true);
                } else {
                    $hallrow['cityName'] = (new \local_hall\hall())->listofcities($hallinfo->city);
                }
                $hallrow['address'] = $hallinfo->maplocation;
                $hallrow['phone'] = NULL;
                
                $headers = get_headers($hallinfo->maplocation , true);
                $str = $headers['Location'];
                $pattern = "\@[-?\d\.]*\,([-?\d\.]*)";
                $location = preg_match('/@(\-?[0-9]+\.[0-9]+),(\-?[0-9]+\.[0-9]+)/', $str, $pattern );
                $hallrow['longitude'] = !empty($pattern[2]) ? $pattern[2] : 0;
                $hallrow['latitude'] = !empty($pattern[1]) ? $pattern[1] : 0;
                $halldata[] = $hallrow;
            }            
        }

        return ['exams' => $reviewexms, 'halls' => $halldata];
    }

    public function get_exam_centers($stable){
        global $DB,$SESSION,$CFG, $SESSION;

        $SESSION->lang =($stable->isarabic == 'true')?'ar':'en';
        $selectsql = "SELECT * FROM {hall} lo "; 
        $countsql  = "SELECT COUNT(lo.id) FROM {hall} lo  ";
        $formsql = " WHERE type=1 ";
        if(isset($stable->query) && trim($stable->query) != ''){
            $formsql .= " AND  ( lo.name LIKE :namesearch OR lo.code LIKE :codesearch ) ";
            $searchparams = array(
                'namesearch' => '%'.trim($stable->query).'%',
                'codesearch' => '%'.trim($stable->query).'%',
             );
        } else {
            $searchparams = array();
        }
        $formsql .= " ORDER BY lo.id DESC";
        $params = array_merge($searchparams);
        $hallscount = $DB->count_records_sql($countsql.$formsql,$params);
        $halls = $DB->get_records_sql($selectsql.$formsql, $params, $stable->start,$stable->length);
        $hallsslist = array();
        $count = 0;
        foreach($halls as $hallinfo) {
      
                $hallsslist[$count]['examCenterID'] = $hallinfo->id;
                $hallsslist[$count]['name'] = $hallinfo->name;
    
                if ($SESSION->lang == 'ar') {
                    $hallsslist[$count]['cityName'] = (new \local_hall\hall())->listofcities($hallinfo->city, true);
                } else {
                    $hallsslist[$count]['cityName'] = (new \local_hall\hall())->listofcities($hallinfo->city);
                }
                $hallsslist[$count]['address'] = $hallinfo->maplocation;
                $hallsslist[$count]['phone'] = NULL;
                $headers = get_headers($hallinfo->maplocation , true);
                $str = $headers['Location'];
                $pattern = "\@[-?\d\.]*\,([-?\d\.]*)";
                $location = preg_match('/@(\-?[0-9]+\.[0-9]+),(\-?[0-9]+\.[0-9]+)/', $str, $pattern );
                $hallsslist[$count]['longitude'] = !empty($pattern[2]) ? $pattern[2] : 0;
                $hallsslist[$count]['latitude'] = !empty($pattern[1]) ? $pattern[1] : 0;

            $count++;
        }
        return $hallsslist;

    }
    public function exam_details($data, $mlang = NULL, $responsetype=false)
    {
        global $DB,$SESSION,$CFG;
        $SESSION->lang = ($mlang) ? $mlang : current_language();
        $exam = $data['exam'];
        $record = $DB->get_record('local_exams', ['id' => $exam->id]);
        $examinfo = [];
        $examinfo['id'] = $exam->id;
        $examinfo['competencyweights'] = strip_tags(format_text($exam->competencyweights, FORMAT_HTML));
        $examinfo['examDescription'] = strip_tags(format_text($exam->programdescription, FORMAT_HTML));
        $examinfo['targetaudience'] = strip_tags(format_text($exam->targetaudience, FORMAT_HTML));
        $examinfo['releasedate'] = $record->examdatetime;

        list($erequirementsql,$erequirementparams) = $DB->get_in_or_equal(explode(',',$record->requirements));
        $examrequirementquerysql = "SELECT * FROM {local_exams} WHERE id $erequirementsql";
        $examrequirements= $DB->get_records_sql($examrequirementquerysql,$erequirementparams);
        foreach ($examrequirements AS $examrequirement) {
            $examname = ($SESSION->lang == 'ar') ? $examrequirement->examnamearabic: $examrequirement->exam;
            $examrequirement->name = $examname;
            $examrequirement->description = null;
            $examrequirement->value =  $examrequirement->id;       
        } 
        if(!empty($examrequirements)){
              $examinfo['prerequisitesOfExams'] = array_values($examrequirements);
        } else {
              $examinfo['prerequisitesOfExams'] = array();
        }

        list($prequirementsql,$prequirementparams) = $DB->get_in_or_equal(explode(',',$record->programs));
        $programrequirementquerysql = "SELECT * FROM {local_trainingprogram} WHERE id $prequirementsql";
        $programrequirements= $DB->get_records_sql($programrequirementquerysql,$prequirementparams);
        foreach ($programrequirements AS $programrequirement) {

            $name = ($SESSION->lang == 'ar') ? $programrequirement->namearabic: $programrequirement->exam;
           
            $programrequirement->name = $name;
            $programrequirement->description = null;
            $programrequirement->value =  $programrequirement->id;
        } 
        if(!empty($programrequirements)){
              $examinfo['prerequisitesOfCourses'] = array_values($programrequirements);
        } else {
              $examinfo['prerequisitesOfCourses'] = array();
        }
    
        $languages = explode(',', $record->language);
        $profiles = $DB->get_records('local_exam_profiles', ['examid' => $exam->id, 'activestatus' => 1, 'publishstatus' => 1, 'decision' => 1]);
        foreach($profiles as $profile) {
            $examname = ($SESSION->lang == 'ar') ? $record->examnamearabic: $record->exam;
            $profile->id = $profile->id;
            $profile->code = $profile->profilecode;
            $profile->name = $examname;
            $profile->title = $examname;
            $profile->successGrade = $profile->passinggrade;
            $profile->totalScore = 100;
            $profile->noOfQuestions = $record->questions;

            if(date('i', mktime(0, 0, $profile->duration)) == 0) {
                $profile->durationInMinutes = date('g', mktime(0, 0, $profile->duration));
                $profile->durationstatus = get_string('hours', 'local_exams');
            } else {
                $profile->durationInMinutes = date('H:i', mktime(0, 0, $profile->duration));
                $profile->durationstatus = get_string('minutes', 'local_exams');
            }
            if ($profile->language == 0) {
                $language = get_string('arabic', 'local_exams');
            } else {
                $language = get_string('english', 'local_exams');
            }
            $profile->language = $language;
            $profile->profileOwners = format_string($record->ownedby);
            $profile->targetAudience = strip_tags(format_text($record->targetaudience, FORMAT_HTML));

            if ($profile->material == 0) {
                $attachmentLink = examlearningmaterial_url($profile->materialfile, 'materialfile');
            } else {
                if(!empty($profile->materialurl)) {
                    $attachmentLink = $profile->materialurl;
                } else {
                    $attachmentLink = NULL;
                }
            }
            $profile->attachmentLink = $attachmentLink;
            $profile->attachmentId= NULL;
            $profile->certificateExpirationYears= $record->certificatevalidity ? $record->certificatevalidity : 0;
            $profile->isExternalRegistration = NULL;
            $profile->externalRegistrationUrl = NULL;            
            $profile->ownerOrganization = NULL;
        }

        $examinfo['examProfiles'] = !empty($profiles) ? $profiles : [];
        $examinfo['code'] = $record->code;
        $examinfo['name'] = ($SESSION->lang == 'ar') ? $record->examnamearabic: $record->exam;
        $examinfo['targetCategories'] = $exam->targetgroup;
        $examinfo['competenciesText'] = NULL;
        $examinfo['targetCategoriesName'] = NULL;
        $examinfo['additionalPrerequisites'] = strip_tags(format_text($record->additionalrequirements, FORMAT_HTML));
        $examinfo['sectors'] = $exam->sectordata;
        $examinfo['examFees'] = $record->sellingprice;
        $examinfo['certificateExpirationYears'] = $record->certificatevalidity ? $record->certificatevalidity : 0;

        $competencytypes = $DB->get_field('local_exams', 'ctype', ['id' => $exam->id]);
        if($record->ctype) {
           $existingcompletencytypes = explode(',' ,$record->ctype);
           $ctypesarray = array_filter($existingcompletencytypes);
        }

        if(!empty($record->competencies) && !empty($ctypesarray)) {
            $existingcompletencies = explode(',' ,$record->competencies);
            $existingcompletencies = array_filter($existingcompletencies);
            $competencylist = implode(',', $existingcompletencies);
            $competencytypes=(new competency)::constcompetencytypes();
            $lang = current_language();

            $sql = "SELECT * FROM {local_competencies} 
                        WHERE id IN ($competencylist)";
            $competencies = $DB->get_records_sql($sql);

            foreach($ctypesarray as $ctype) {
                $row = [];
                $row['type'] = $competencytypes[$ctype];
                $ctypes['data'] = [];
               
                foreach($competencies as $competency) {
                    if($competency->type == $ctype) {
                        $cmptttype = [];
                        if ($SESSION->lang == 'ar') {
                            $cmptttype['name'] = $competency->arabicname;
                        } else {
                            $cmptttype['name'] = $competency->name;
                        }

                        $cmptttype['name'] = ($SESSION->lang == 'ar') ? $competency->arabicname : $competency->name ;
                        $cmptttype['typeId'] = $competency->id;
                        $cmptttype['description'] = strip_tags(format_text($competency->description, FORMAT_HTML));
                        $cmptttype['id'] = $competency->id;
                        $cmptttype['level'] = str_replace('level', '', $record->clevels);
                        $ctypes['data'][] = $cmptttype;
                    }
                }
                $competenciesdata[] = $row+$ctypes;
            }
        }

        if ($SESSION->lang == 'ar') {
            $stable->isArabic = true;
        }

        $alltypes = (new competency)::get_allcompetencytypes($stable);
        $info = [];
        $competencytypes=(new competency)::constcompetencytypes();
        foreach($competencies as $competencyinfo) {
            $cdata = [];
            $key =  array_search($competencyinfo->type, array_column($alltypes, 'type'));
            $cdata['code'] = $competencyinfo->code;
            $cdata['description'] = strip_tags(format_text($competencyinfo->description, FORMAT_HTML));

            if ($SESSION->lang == 'ar') {
                $cdata['name'] = $competencyinfo->arabicname;
            } else {
                $cdata['name'] = $competencyinfo->name;
            }
            $cdata['parentValue'] = NULL;
            $cdata['typeId'] = $alltypes[$key]['value'];
            $cdata['typeName'] = $competencytypes[$competencyinfo->type];
            $cdata['value'] = $competencyinfo->id;
            $info[] = $cdata;
        }

        if ($responsetype == 'mobile') {
            $examinfo['examCompetecies'] = !empty($competenciesdata) ? $competenciesdata : [];
        } else {
            $examinfo['examCompetecies'] = !empty($info) ? $info : [];
        }

        $examinfo['sectorImage'] = NULL;
        $examinfo['examJobFamily'] = $exam->targetgroup;

        if ($SESSION->lang == 'ar') {
            $examinfo['competencyLevelCode'] = str_replace('level', 'مستوى', $record->clevels);
        } else {
            $examinfo['competencyLevelCode'] = str_replace('level', 'Level', $record->clevels);
        }
        $examinfo['competencyLevelId'] = str_replace('level', '', $record->clevels);
        if($record->competencies) {
           
            $existingcompletencies = explode(',' ,$record->competencies);
            $existingcompletencies = array_filter($existingcompletencies);
            $competencylist = implode(',', $existingcompletencies);

            $sql = "SELECT * FROM {local_competencies}
                        WHERE id IN ($competencylist)";
            $competencies = $DB->get_records_sql($sql);
            $competency = [];
            foreach($competencies as $competency) {
                $competencyLevelName = [];
                if ($SESSION->lang == 'ar') {
                    $competencyLevelName['name'] = $competency->arabicname;
                } else {
                    $competencyLevelName['name'] = $competency->name;
                }
                $competencynames[] = $competencyLevelName;
            }
        }
        $examinfo['competencyLevelName'] =!empty($competencynames) ? $competencynames : [];
        $examinfo['examhalls'] = (new \local_hall\hall())->get_examhalls();

        $stable = new \stdClass();
        $stable->thead = false;
        $stable->type = 'mobile';
        $filterdata = [];
        $filterdata['examid'] = $record->id;
        $reservations = $this->get_userexamreservations($stable, $filterdata);
        $examinfo['reservations'] = $reservations['hascourses'];
        $examinfo['detailsPageURL'] =$CFG->wwwroot.'/local/exams/exams_qualification_details.php?id='.$exam->id;

        return $examinfo;
    }

    public function get_exam_attachments($stable)
    {
        global $DB;
        $record = $DB->get_record('local_exams', ['id' => $stable->attachmentid]);
        $examinfo = [];

        $languages = explode(',', $record->language);

        $profile = [];
        foreach($languages as $language) {
            $row = [];
            $row['id'] = $record->id;

            if($language == 'arabic') {

                $sql = "SELECT * FROM {files} WHERE itemid = :logo AND filearea = :filearea AND filename != '.' ORDER BY id DESC";
                $attachment = $DB->get_record_sql($sql,array('logo' => $record->arprofile, 'filearea' => 'arprofile'),1);

            } else {

                $sql = "SELECT * FROM {files} WHERE itemid = :logo AND filearea = :filearea AND filename != '.' ORDER BY id DESC";
                $attachment = $DB->get_record_sql($sql,array('logo' => $record->enprofile, 'filearea' => 'enprofile'),1);

            }

            $row['fileName'] = $attachment->filename;
            $row['extention'] = '.pdf';
            $row['contentType'] = 'application/pdf';
            $row['thumbnail'] = NULL;
            $row['content'] = NULL;
            $profile[] = $row;
        }

        return $profile;
    }

    public function get_exam_statistics()
    {
        global $DB;
        $sql = "SELECT FROM_UNIXTIME(le.examdatetime,'%Y') AS examyear, COUNT(le.id) AS examscount
                  FROM {local_exams} AS le
                 GROUP BY FROM_UNIXTIME(le.examdatetime,'%Y')";
        $records = $DB->get_records_sql($sql);
        $data = [];
        foreach($records as $record) {
            $row = [];
            $row['year'] = $record->examyear;
            $row['numberOfExams'] = $record->examscount;
            $row['numberOfExamTrials'] = NULL;
            $row['femalesExamTrials'] = NULL;
            $row['malesExamTrials'] = NULL;
            $data[] = $row;
        }

        return $data;
    }

    public function examsectors($sectors)
    {
        global $DB;
        $sectors = explode(',', $sectors);
        $data = [];
        foreach($sectors as $sector) {
            $record = $DB->get_record('local_sector', ['id' => $sector]);
            $row = [];
            $row['id'] = $record->id;
            $lang = current_language();
            if ($lang == 'ar') {
                $row['name'] = $record->titlearabic;
            } else {
                $row['name'] = $record->title;
            }
            $data[] = $row;
        }
        
        return $data;
    }
    
    public function  orgoffexamdetails($id,$mlang = NULL) {
        global $DB, $PAGE, $OUTPUT, $USER, $CFG,$SESSION;
        $systemcontext = context_system::instance();
        $renderer = $PAGE->get_renderer('local_exams');
        $lang = current_language();
        $SESSION->lang = ($mlang) ? $mlang : current_language() ;
        $courseid = $DB->get_field('local_exams','courseid',['id'=>$id]);
        $exam = $DB->get_record('local_exams', ['id' => $id]);
        $exam->id = $id;
        $exam->name = ($SESSION->lang == 'ar') ? $exam->examnamearabic : $exam->exam;
        $exam->code = $exam->code;
        $exam->description = format_text($exam->programdescription, FORMAT_HTML);
     
        $profiles= $DB->get_records('local_exam_profiles',['examid'=>$id, 'activestatus' => 1, 'publishstatus' => 1, 'decision' => 1]);
        foreach($profiles as $record) {
             $record->id= $record->id;
             $record->code= $record->profilecode;
             $record->registrationstartdate= !empty($record->registrationstartdate) ? $record->registrationstartdate : 0;
             $record->registrationenddate= !empty($record->registrationenddate) ? $record->registrationenddate : 0;
             $record->passinggrade= $record->passinggrade;
             $record->seatingcapacity= $record->seatingcapacity;
             $record->duration= $record->duration;
             $record->language= $this->profilelanguage($record->language);
           
            $seats = $DB->get_field_sql("SELECT SUM(purchasedseats) FROM {tool_org_order_seats} WHERE tablename = 'local_exam_profiles' AND fieldname = 'id' AND fieldid = $record->id");

            $entityseats = self::entity_enrolled($record->id);
            $offeringvailableseats = $seats + $entityseats;

            $bookingseats=$offeringvailableseats ? ($record->seatingcapacity - $offeringvailableseats) : $record->seatingcapacity;


            if(empty($record->registrationenddate) || $record->registrationenddate > strtotime(userdate(time(), '%d-%m-%Y'))) {
                $record->examseats = 'bookseats';
            } else{
                $record->examseats = 'bookseats';
            }
         
             $record->showquestions= $record->showquestions;
             $record->questions= $record->questions;
             $record->showexamduration= $record->showexamduration;

            $coursemodule = get_coursemodule_from_instance('quiz', $record->quizid, $exam->courseid);
            
             $record->cmid= $coursemodule->id;
            $examdate = $DB->get_field('exam_enrollments', 'examdate',  ['examid' => $exam->id, 'profileid' => $record->id, 'userid' => $USER->id]);
            $examtime = date('Y-m-d', $examdate);

            $lastattempt = $this->lastattempt($record->examid);
            $attempsnumber = $this->quiz_attemptscount($record->examid);
            $attemptpurchase = $this->examattemptpurchases($record->examid);

             $record->attemptid= $attempsnumber;
            if (($attemptpurchase == $attempsnumber) || $attempsnumber == 0) {
                 $record->launch= true;
            } else {
                 $record->launch= false;
            }

            $userexamdate = $this->user_scheduledata($record, $attempsnumber);
            $record->hall = $userexamdate->hallname;
         
            if ($examdate == 0) {
                 $record->examdate= '--';
            } else {

                $schedulestarttime = $DB->get_field('hallschedule', 'starttime', ['id' => $userexamdate->hallscheduleid]);
                 $record->examdate= userdate(strtotime($userexamdate->examdate), get_string('strftimedate','core_langconfig')).' '.userdate($schedulestarttime, get_string('strftimetime12', 'langconfig'));
            }
            if ($attempsnumber == $exam->noofattempts) {
                 $record->launchbtnstatus= true;    
            } else {
                if ($exam->noofattempts > 0) {
                    $attemptnum = ++$attempsnumber;
                    if ($attemptnum <= $exam->noofattempts) {
                        $attemptid = $DB->get_field('local_exam_attempts', 'id', ['examid' => $exam->id, 'attemptid' => $attemptnum]);
                    }
                }
                 $record->launchbtnstatus= false;      
            }

             $record->product_variations= (new \tool_product\product)->get_product_variations($exam->id, $attemptid, 6);

            $variation = $DB->get_field('tool_products', 'id', ['category' => 6, 'referenceid' => $attemptid]);
             $record->product_attributes= (new \tool_product\product)->get_product_attributes($exam->id, 6, 'purchase', true, $variation);
        }

        if(!empty($profiles)){
              $exam->profiles = array_values($profiles);
        } else {
              $exam->profiles  = array();
        }
        return $exam;
    }
    // public function order_details($telrid=0,$items )
    // public function order_details($items )
    // {
    //     global $DB,$USER;

    //     if($telrid) {
    //         $data = $DB->get_record("tool_product_telr", array('id'=>$telrid), "*", MUST_EXIST);
    //         $summary = unserialize(base64_decode($data->productdata));
    //         $orderinfo = $summary['items'];
    //     } else {
    //         $orderinfo = $items;
    //     }
    //     $products = [];
    //     // ************* DL-304: IKRAM CODE START ***************************************
    //     // cisi_exams_booking($orderinfo);
    //     // ************* DL-304: IKRAM CODE END *****************************************
    //     foreach($orderinfo as $info) {
    //         $apidata = [];
    //         $product = $DB->get_record('tool_products', ['id' => $info['product_id']]);
    //         if ($product->category == 2 || $product->category == 6) {
    //             $info['transactiontypes'] = 'register';
    //             $info['hallscheduelid'] = $info['hallscheduleid'];
    //             $apidata = $this->prepare_fast_apidata($info, $data);
    //             $apidata['validation'] = 0;
    //             $fastapi = new fastservice($apidata);
    //             $fastapi->create_exam_reservations();
    //         }
    //         $products[] = $apidata;
    //     }

    //     return $products;
    // }

    public function prepare_fast_apidata($info, $data=false) {
        global $DB, $USER;
        if(!$data){
            $data= new stdClass;
        }
        $productinstance = new product();
        $user_id = ($data->userid) ? $data->userid :(($info['userid']) ? $info['userid'] : $USER->id);
        $username = $DB->get_field('user', 'username', ['id' => $user_id]);
        $productcategory=(int) $DB->get_field('tool_products','category',['id'=>$info['product_id']]);
        if ($productcategory == 2) {
            $category = self::EXAMS;
        } else {
            $category = self::EXAMATTEMPT;
        }
        $orglicensenumber = $DB->get_field_sql('SELECT org.licensekey FROM {local_organization} org 
                                                  JOIN {local_users} lc ON lc.organization  =org.id
                                                   WHERE lc.userid = :userid',['userid'=>$user_id]);
        if(isset($info['hallscheduelid'])){
            $hallinfosql =  ' SELECT  hs.id as hallscheduleid,hs.startdate as examdate,hs.starttime,h.code as hallcode, h.id as hallid
                                FROM {hall} as h 
                                JOIN {hallschedule} as hs ON hs.hallid=h.id 
                                WHERE hs.id=:hallscheduleid';
            $hallinfo = (array)$DB->get_record_sql($hallinfosql, ['hallscheduleid' => $info['hallscheduelid']]);
           if($productcategory == 2) {
                $productinfosql = 'SELECT e.ownedby,e.code,ep.profilecode,ep.registrationstartdate,ep.language,e.id as examid,ep.id as profileid,e.courseid 
                FROM {local_exam_profiles} as ep 
                JOIN {local_exams} as e ON e.id=ep.examid
                JOIN {tool_products} as tp ON tp.category='.$category.' AND tp.referenceid=ep.id
                WHERE tp.id=:productid';
                $productinfo = (array)$DB->get_record_sql($productinfosql, ['productid' => $info['product_id']]);
           } else {
                $productinfosql = 'SELECT e.ownedby,e.code,ep.profilecode,ep.registrationstartdate,ep.language,e.id as examid,ep.id as profileid,e.courseid 
                FROM {local_exam_attempts} as lea 
                JOIN {local_exams} as e ON e.id=lea.examid
                JOIN {local_exam_userhallschedules} as eu ON eu.examid=e.id 
                JOIN {local_exam_profiles} as ep  ON ep.examid=eu.examid AND ep.id=eu.profileid 
                JOIN {tool_products} as tp ON tp.category='.$category.' AND tp.referenceid=lea.id
                WHERE tp.id=:productid AND eu.userid =:userid';
                $productinfo = (array)$DB->get_record_sql($productinfosql, ['productid' => $info['product_id'],'userid' => $user_id]);
           }
            $productdata = (object)array_merge($hallinfo, $productinfo);
        }else{
            $productdatasql ='SELECT e.code,e.id as examid,ep.id as profileid,e.courseid,ep.profilecode,ep.registrationstartdate,ep.language,hs.startdate as examdate,hs.starttime,h.code as hallcode, e.ownedby, hs.id as hallscheduleid, h.id as hallid, e.id as examid
                                FROM {hall} as h 
                                JOIN {hallschedule} as hs ON hs.hallid=h.id 
                                JOIN {local_exam_userhallschedules} as euh ON euh.hallscheduleid=hs.id
                                JOIN {local_exam_profiles} as ep ON ep.examid=euh.examid AND euh.profileid=ep.id
                                JOIN {local_exams} as e ON e.id=ep.examid
                                JOIN {tool_products} as tp ON tp.category=('.$category.' ) AND tp.referenceid=ep.id
                                WHERE tp.id=:productid ';
            $parameters = ['productid' => $info['product_id']];
            
            $productdata = $DB->get_record_sql($productdatasql, $parameters);
        }
        if($productdata->registrationstartdate){
            $row['registrationstartdate'] = userdate($productdata->registrationstartdate,'%Y-%m-%d') .' '. userdate($productdata->registrationstartdate,'%H:%M:%S');
        }else{
            $row['registrationstartdate'] =  null;
        }
        $enrolleduserid =(int) $DB->get_field('exam_enrollments','usercreated',['examid'=>$productdata->examid,'profileid'=>$productdata->profileid,'courseid'=>$productdata->courseid,'userid'=>$user_id]);
        $createdbyusername =($enrolleduserid) ? $DB->get_field('user', 'username', ['id' => $enrolleduserid]) : $USER->username;
        $row['username'] = $username;
        if($info['profileid']) {
            $sql = "SELECT lep.id,le.code as examcode, lep.profilecode,lep.language 
                    FROM {local_exams} le
                    JOIN {local_exam_profiles} lep  ON lep.examid = le.id
                    WHERE lep.id =".$info['profileid'];
            $codes = $DB->get_record_sql($sql);
            $row['examcode'] = $codes->examcode;
            $row['profilecode'] = $codes->profilecode;
            if($codes->language){
                $row['examlanguage'] = 'English';
            }else{
                $row['examlanguage'] = 'Arabic';
            }
        } else {
            $row['examcode'] = $productdata->code;
            $row['profilecode'] = $productdata->profilecode;
            if($productdata->language){
                $row['examlanguage'] = 'English';
            }else{
                $row['examlanguage'] = 'Arabic';
            }
        }
        $row['createdbyusername'] = $createdbyusername;
        $row['examdatetime'] = userdate($productdata->examdate,'%Y-%m-%d') .' '. userdate($productdata->starttime,'%H:%M:%S');
        // $row['examdatetime'] = userdate($productdata->examdate,'%Y-%m-%d') .' '. gmdate("H:i:s", $productdata->starttime);
        
        $row['purchasedatetime'] = userdate(time(),'%Y-%m-%d %H:%M:%S');
        $row['hallscheduleid'] = $productdata->hallscheduleid;
        // $hallcode = (new \local_hall\hall())->get_hallcode($productdata->hallid, $productdata->examid);
        $row['centercode'] = $productdata->hallcode;
        if(!empty($data)){
            
            $row['billnumber'] = $data->transactionref ? $data->transactionref : 0;
            $row['paymentrefid'] = $data->orderref ?  $data->orderref : 0;
            $summary = unserialize(base64_decode($data->productdata));
            $paymenttype =$summary['payment_methods'][0]['name'];
            if ($paymenttype == 'Prepaid' || is_null($paymenttype)) {
                $row['payementtypes'] = 1;
            } else {
                $row['payementtypes'] = 2;
            }
           
        }else{
            $row['billnumber'] =  0;
            $row['paymentrefid'] =  0;
            $row['payementtypes'] = 1;
        }
        $row['userorganization'] = ($orglicensenumber) ? $orglicensenumber : 0 ;
        if($info['transactiontypes'] == 'reschedule'){
            $row['transactiontypes'] = 3;
            $row["oldexamdatetime"] = $info["oldexamdatetime"];
            $row["oldcentercode"] =  $info["oldcentercode"];
        }else if($info['transactiontypes'] == 'cancel'){
            $row['transactiontypes'] = 2;
        }else if($info['transactiontypes'] == 'register'){
            $row['transactiontypes'] = 1;
        }
        return $row;
    }

    public function add_update_profile($data)
    {
        global $DB, $USER;
        $systemcontext = context_system::instance();
        $data->material = !empty($data->material) ? $data->material : 0;
        $data->nondisclosure = $data->nondisclosure['text'];
        $data->instructions = $data->instructions['text'];
        $quizid = 0;
        //if the user is enroled in the exam profile, a new exam profile will be created by editing the profile
        $examuserhall = $DB->get_record('local_exam_userhallschedules',['examid' => $data->examid, 'profileid' => $data->id]);
        if($examuserhall){
            $data->id = 0;
        }
        if ($data->id > 0) {
            $data->cmid = $DB->get_field_sql('SELECT cm.id FROM {course_modules} as cm 
                                                JOIN {quiz} as q ON q.id=cm.instance AND cm.module=(SELECT m.id from {modules} as m where name="quiz") 
                                                JOIN {local_exam_profiles} as ep ON ep.quizid=q.id AND ep.id=:profileid', ['profileid' =>$data->id]);
            $exam = $DB->get_record('local_exams', ['id' => $data->examid]);
           try{
              $transaction = $DB->start_delegated_transaction();
              $transaction->allow_commit();
              $createprofile = new \local_exams\local\profile($exam->courseid, $data->profilecode, $data, $exam);
              // if(!$data->cmid || $data->cmid == null){
              //   $data->quizid = $createprofile->createquiz($data);
              // }else{
                $createprofile->updatequiz($data);
              // }

              $data->timemodified = time();            
              $id = $DB->update_record('local_exam_profiles', $data);
              $event =  \local_hall\event\reservation_update::create(array( 'context'=>$systemcontext, 'objectid' => $data->id));
              $event->trigger();
              // return 2;
           } catch(moodle_exception $e){
              $transaction->rollback($e);
              return false;
            }
        } else {
            $data->usercreated = $USER->id;
            $profile = $DB->get_field_sql("SELECT le.id FROM {local_exam_profiles} le WHERE 1=1 ORDER BY le.id DESC ");
            $exam = $DB->get_record('local_exams', ['id' => $data->examid]);
            try{
                $transaction = $DB->start_delegated_transaction();
                $transaction->allow_commit();
                $createprofile = new profile($exam->courseid, $data->profilecode, $data, $exam);
                $data->section = $createprofile->sectionid;
                $data->exam = $exam->exam;
                $data->examnamearabic = $exam->examnamearabic;
                $quizid = $createprofile->createquiz($data);
                $data->sectionid = $createprofile->sectionid;
                $data->classification=$data->classification;
                 if($quizid){
                   $data->quizid = $quizid;
                    $id = $DB->insert_record('local_exam_profiles', $data);
                    $event =  \local_exams\event\profile_created::create(array( 'context'=>$systemcontext, 'objectid' => $id));
                    $event->trigger();
                    $event =  \local_hall\event\hall_reserved::create(array( 'context'=>$systemcontext, 'objectid' => $id));
                    $event->trigger();
                }
                // return 1;
             } catch(moodle_exception $e){
                $transaction->rollback($e);
                return false;
            }
        }

        return $id;
    }

    public function set_examprofile($id)
    {
        global $DB;
        $data = $DB->get_record('local_exam_profiles', ['id' => $id]);
        $data->nondisclosure = ['text' => $data->nondisclosure];
        $data->instructions = ['text' => $data->instructions];

        return $data;
    }

    public function get_listof_examprofiles($stable, $filterdata)
    {
        global $DB, $USER,$PAGE;
        $systemcontext = context_system::instance();
        $lang = current_language();
        $searchparams = [];
        $ordersql = '';
        $examid = $filterdata->examid;
        $profileid = $filterdata->profileid;
        $selectsql = "SELECT ep.* FROM {local_exam_profiles} ep WHERE 1=1 AND ep.examid =".$examid; 
        $countsql  = "SELECT COUNT(ep.id) FROM {local_exam_profiles} ep WHERE 1=1 AND ep.examid =".$examid;

        if (!is_siteadmin() &&  !has_capability('local/organization:manage_examofficial', $systemcontext)) {
            $wheresql = " AND ep.activestatus=1 AND ep.publishstatus=1 AND ep.decision=1 ";
        }

        if (!is_siteadmin() && has_capability('local/organization:manage_trainee', $systemcontext)) {
            $profile = $DB->get_record('local_exam_profiles', ['id'=>$filterdata->profileid]);
            $userexamdate = $this->user_scheduledata($profile, 1);
            $endtime = (strtotime($userexamdate->examdate)+userdate(($userexamdate->starttime), '%H')*3600 + userdate($userexamdate->endtime, '%M')*60);
            if ($filterdata->profileid && time() < $endtime) {
                $wheresql .= " AND ep.id = ".$filterdata->profileid;
            }
        }

         if(isset($filterdata->search_query) && trim($filterdata->search_query) != ''){
                $wheresql .= " AND (ep.profilecode LIKE :profilecodesearch) ";
                $searchparams = array(
                                       'profilecodesearch' => '%'.trim($filterdata->search_query).'%',
                                      
                                       
                                    );
        }
        
        
        $params = array_merge($searchparams);
        $totalprofiles = $DB->count_records_sql($countsql.$wheresql, $params);
        $ordersql .=" ORDER BY ep.id DESC";
        $records = $DB->get_records_sql($selectsql.$wheresql.$ordersql, $params, $stable->start, $stable->length);

        $exam = $DB->get_record('local_exams', ['id' => $examid]);
        $usernextattempt = '';
        $profiles = [];
        foreach($records as $record) {
            $row = [];
            $row['id'] = $record->id;
            $row['examid'] = $record->examid;
            $row['profilecode'] = $record->profilecode;
            $row['scheduleid'] = $filterdata->hallscheduleid;
            $row['registrationstartdate'] = !empty($record->registrationstartdate) ? userdate($record->registrationstartdate, get_string('strftimedatemonthabbr', 'core_langconfig')) : '--';
            $row['registrationenddate'] = !empty($record->registrationenddate) ? userdate($record->registrationenddate, get_string('strftimedatemonthabbr', 'core_langconfig')) : '--';
            $row['passinggrade'] = $record->passinggrade;
            $row['seatingcapacity'] = $record->seatingcapacity;
            $row['duration'] = ($record->duration/60).' '.get_string('min','local_exams');
            $row['language'] = $this->profilelanguage($record->language);
            $row['lastattemptprofileid'] = !empty($filterdata->profileid) ? $filterdata->profileid : 0;
            if(is_siteadmin() || has_capability('local/organization:manage_examofficial', $systemcontext)) {
                $row['isadmin'] = true;
            } else {
                $row['isadmin'] = false;
            }

            $row['ownedby'] = $exam->ownedby;

            $seats = $DB->get_field_sql("SELECT SUM(approvalseats) FROM {tool_org_order_seats} WHERE tablename = 'local_exam_profiles' AND fieldname = 'id' AND fieldid = $record->id");

            $entityseats = self::entity_enrolled($record->id);
            $offeringvailableseats = $seats; //+ $entityseats;

            $bookingseats=$offeringvailableseats ? ($record->seatingcapacity - $offeringvailableseats) : $record->seatingcapacity;
            $row['orgoff'] = (!is_siteadmin() && has_capability('local/organization:manage_organizationofficial',$systemcontext)) ? true : false;
            $row['trainee'] = (!is_siteadmin() && has_capability('local/organization:manage_trainee', $systemcontext)) ? true : false;
            $row['showquestions'] = $record->showquestions;
            $row['questions'] = $record->questions;
            $row['showexamduration'] = $record->showexamduration;
            $row['is_assesmentoperation'] = has_capability('local/organization:assessment_operator_view', $systemcontext) ? true : false;

            $coursemodule = get_coursemodule_from_instance('quiz', $record->quizid, $exam->courseid);
            
            $row['cmid'] = $coursemodule->id;
            $examdate = $DB->get_field('exam_enrollments', 'examdate',  ['examid' => $exam->id, 'profileid' => $record->id, 'userid' => $USER->id]);
            $examtime = userdate($examdate, '%Y-%m-%d');

            $lastattempt = $this->lastattempt($record->examid);
            $attempsnumber = $this->quiz_attemptscount($record->examid);
            $attemptpurchase = $this->examattemptpurchases($examid);
            $userexamdate = $this->user_scheduledata($record, $attempsnumber);
            if ($userexamdate->enrolstatus == 1 || $userexamdate->enrolstatus =='') {
                $row['examstatus'] = false;
            } else {
                $row['examstatus'] = true;
            }
            $row['exampassed'] = false;
            if (!is_siteadmin() && has_capability('local/organization:manage_trainee', $systemcontext) && $userexamdate->id > 0) {
                $gradehaving = $this->gradestatus($userexamdate->id);
                if (is_numeric($gradehaving) && ($row['scheduleid'] >= 0)) {
                    $row['havinggrade'] = true;
                    if ($gradehaving >= $userexamdate->gradetopass) {
                        $row['exampassed'] = true;
                    } else {
                        $row['exampassed'] = false;
                    }
                } elseif ($gradehaving == 'Unknow') {
                    $row['havinggrade'] = false;
                }
            } else if($userexamdate =='') {
                $row['havinggrade'] = true;
            }
            $row['attemptid'] = $attempsnumber;
            $row["hall"] = $userexamdate->hallname;
            $sql = "SELECT id, daysbeforeattempt, fee
                      FROM {local_exam_attempts}
                     WHERE id = (SELECT min(id) FROM {local_exam_attempts} WHERE examid = $exam->id AND  attemptid > $attempsnumber)";
            $days = $DB->get_record_sql($sql); 
            if (!empty($lastattempt)) {
                $userattemptid = $attempsnumber;
                // $date = strtotime("+". $days->daysbeforeattempt ."day", $lastattempt);
                $date = $lastattempt;
                $usernextattempt = userdate($date, get_string('strftimedatemonthabbr', 'core_langconfig'));
                $row["nextattempt"] = userdate($date, get_string('strftimedatemonthabbr', 'core_langconfig'));
                $row["examattemptid"] = $days->id;
                $nextattempt = userdate($date, '%d-%m-%Y %H:%M');
                if (($attemptpurchase == $attempsnumber) && !empty($userexamdate->examdate)) {
                    $examday = true;
                    $starttime = $DB->get_field('hallschedule', 'starttime', ['id' => $userexamdate->hallscheduleid]);
                    $nextattempt = userdate(strtotime($userexamdate->examdate), '%d-%m-%Y').' '.userdate($starttime, get_string('strftimetime24', 'langconfig'));
                }
                if (((strtotime(userdate(time(), '%d-%m-%Y %H:%M')) >= strtotime($nextattempt)) && empty($examday)) ) {
                    $row["todayexam"] = true;
                } else {
                    $row["todayexam"] = false;
                }
            } else {
                if (strtotime(userdate(time(), '%Y-%m-%d')) >= strtotime($examtime)) {
                    $row["todayexam"] = true;
                } else {
                    $row["todayexam"] = false;
                }
            }
            if ($userexamdate->examdate == 0) {
                $row['examdate'] = '--';
            } else {
                $scheduletime = $DB->get_record('hallschedule', ['id' => $userexamdate->hallscheduleid]);
                $schedulestarttime = $scheduletime->starttime;
                $scheduleendtime = $scheduletime->endtime;
                $row['examdate'] = !empty($userexamdate->examdate) ? userdate(strtotime($userexamdate->examdate), get_string('strftimedate','core_langconfig')).' '.userdate($schedulestarttime, get_string('strftimetime12', 'langconfig')) : '--';
                if (userdate(time(), '%d-%m-%Y') == userdate($userexamdate->examdate, '%d-%m-%Y')) {
                    $row['examday'] = true;
                } else {
                    $row['examday'] = false;
                }
            }
            if ($attempsnumber == $exam->noofattempts) {
                $row['launchbtnstatus'] = true;    
            } else {
                if ($exam->noofattempts > 0) {
                    if ($exam->appliedperiod == 1) {
                        if ($attempsnumber == 0) {
                            $category = self::EXAMS;
                            $toolproduct = $DB->get_record('tool_products', ['category' => $category, 'referenceid' => $record->id]);
                        } else {
                            $attemptnum = ++$attempsnumber;
                            $category = self::EXAMATTEMPT;
                            if ($attemptnum <= $exam->noofattempts) {
                                $attemptid = $DB->get_field('local_exam_attempts', 'id', ['examid' => $exam->id, 'attemptid' => $attemptnum]);
                                $toolproduct = $DB->get_record('tool_products', ['category' => $category, 'referenceid' => $attemptid]);
                            }
                        }
                    } else {
                        $attemptnum = ++$attempsnumber;
                        if ($attemptnum == 1) { 
                            $attemptnum = 2;
                        }
                        $category = self::EXAMATTEMPT;
                        if ($attemptnum <= $exam->noofattempts) {
                            $attemptid = $DB->get_field('local_exam_attempts', 'id', ['examid' => $exam->id, 'attemptid' => $attemptnum]);
                            $toolproduct = $DB->get_record('tool_products', ['category' => $category, 'referenceid' => $attemptid]);
                        }
                    }
                }
                $row['launchbtnstatus'] = false;
            }
            $sql = "SELECT profileid
                      FROM {local_exam_userhallschedules} 
                     WHERE examid = ".$exam->id." AND userid =".$USER->id." ORDER BY id ASC";
            $referid = $DB->get_field_sql($sql);       
            $toolproductid = $DB->get_field('tool_products', 'id', ['referenceid'=> $referid, 'category'=> self::EXAMS]);
            $row['product_profile'] = !empty($toolproductid) ? $toolproductid : 0;
            $row['attemptfee'] = $exam->sellingprice;

            $row['product_variations'] = (new \tool_product\product)->get_product_variations($exam->id, $attemptid, self::EXAMATTEMPT);

            if (empty($row['launchbtnstatus']) && empty($row['launch']) && !empty($row["todayexam"])) {
                $string = 'purchase';
            } else {
                $string = 'reschedule';
            }
            $row['product_attributes'] = (new \tool_product\product)->get_product_attributes($exam->id, $category, $string, true, $toolproduct->id);
            $row['product_attributes']['profileid'] = $record->id;
            $row['product_attributes']['processtype'] = $string;
            
            $startedtime = (strtotime($userexamdate->examdate)+userdate(($schedulestarttime), '%H')*3600 + userdate(($schedulestarttime), '%M')*60);
            $endedtime = (strtotime($userexamdate->examdate)+userdate(($schedulestarttime), '%H')*3600 + userdate(($schedulestarttime), '%M')*60)+$userexamdate->duration;

            if((time() >= $startedtime) && (time() < $endedtime)) {
                $row['readytolaunch'] = true;
            } else {
                $row['readytolaunch'] = false;
            }
            if (!empty($userexamdate) && (time() < $endedtime)) {
                $row['launch'] = true;
            } else {
                $row['launch'] = false;
            }

            if ($row['attemptid'] == 0 && empty($row["todayexam"]) && !empty($row['launch'])) {
                $row['canceloption'] = true;
            } elseif($attempsnumber > 0 && !empty($row['examdate'])) {
                $row['canceloption'] = false;
            }

            if ($userexamdate->profileid == $record->id && (time() < $endedtime)) {
                $row['selectedprofile'] = true;
            } else {
                $row['selectedprofile'] = false;
            }

            $courseid = $DB->get_field('local_exams','courseid',['id'=>$examid]);
            $sql = "SELECT usercreated
                      FROM {local_exam_userhallschedules} 
                     WHERE examid = ".$examid." AND userid =".$USER->id." ORDER BY id DESC ";
            $enrolleduserid = $DB->get_field_sql($sql);
            $enroleduserroleinfo = $DB->get_record_sql('SELECT rol.* FROM {role} rol 
                                                    JOIN {role_assignments} rola ON rola.roleid = rol.id
                                                    WHERE rola.userid =:userid and contextid =:contextid',['userid'=>$enrolleduserid,'contextid'=>$systemcontext->id]);
    
            
            $hadsecondpurchase = $DB->record_exists('local_exam_attemptpurchases', ['examid' => $examid, 'userid'=> $USER->id]);
            if (!is_siteadmin() && has_capability('local/organization:manage_trainee', $systemcontext)  && (empty($enroleduserroleinfo->shortname)  ||  $enroleduserroleinfo->shortname == 'organizationofficial')) {
                $row['disableallactions'] = true;
            } else {
                $row['disableallactions'] = false;
            }
            $sdate = strtotime($userexamdate->examdate);
            $curr_date = strtotime(userdate(time(),'%d-%m-%Y'));
            $remainingdays = floor(($sdate - $curr_date) / (60 * 60 * 24));

            if ($remainingdays >= 3) {
                $row['canreschedule'] = true;
            } else {
                $row['canreschedule'] = false;
            }

            $row['disablereschedule'] = $exam->ownedby !='FA'? true: false;
            if ($userexamdate->attemptid == 0) {
                $row['attemptnumber'] = 1;
            } else {
                $examattemptid = $DB->get_field('local_exam_attempts', 'attemptid', ['id'=>$userexamdate->attemptid]);
                $row['attemptnumber'] = $examattemptid;
            }

            if ($filterdata->tuserid > 0) {
                $row['purchasenextattempt'] = true;
                $row['tuserid'] = base64_encode($filterdata->tuserid);
            } else {
                $row['purchasenextattempt'] = false;
                $row['tuserid'] = 0;
            }

            $enrole = $DB->get_field('local_exam_userhallschedules', 'COUNT(id)', ['profileid' => $record->id]);
            $row['enrolled'] = !empty($enrole) ? $enrole : 0;
            $profiles[] = $row;
        }

        $coursesContext = array(
            "profiles" => $profiles,
            "totalprofiles" => $totalprofiles,
            "length" => COUNT($profiles),
            'usernextattempt' => $usernextattempt,
        );
        return $coursesContext;
    }




    public function launchstatus($examid, $quizid)
    {
        global $DB, $USER;
        $attempsnumber = $this->quiz_attemptscount($quizid);
        $attemptpurchase = $this->examattemptpurchases($examid);

        if (($attemptpurchase == $attempsnumber) || $attempsnumber == 0) {
            return true;
        } else {
            return false;
        }
    }

    public function lastattempt($examid)
    {
        global $DB, $USER;

        $sql = "SELECT GROUP_CONCAT(lep.quizid)
                  FROM {local_exam_profiles} lep
                 WHERE lep.examid = ".$examid;
        $quizids = $DB->get_field_sql($sql);
        
        $sql = "SELECT  qa.timefinish
                  FROM {quiz_attempts} qa 
                 WHERE qa.userid = $USER->id AND qa.quiz IN ($quizids)
                 ORDER by qa.id DESC " ;
        $lastattempt = $DB->get_field_sql($sql);

        return $lastattempt;
    }

    public function profilelanguage($lang)
    {
        if ($lang == 0) {
            $language = get_string('arabic', 'local_exams');
        } else {
            $language = get_string('english', 'local_exams');                
        }

        return $language;
    }

    public function delete_examprofile($id)
    {
        global $DB;
        $sql = "SELECT le.courseid, lep.quizid,lep.profilecode,lep.sectionid
                FROM {local_exams} le 
                JOIN {local_exam_profiles} lep ON lep.examid = le.id
                WHERE lep.id =". $id;
        $profile = $DB->get_record_sql($sql);

        $moduleid = $DB->get_field('modules', 'id', ['name' => 'quiz']);
        $cmid = $DB->get_field('course_modules', 'id', ['course' => $profile->courseid, 'module' => $moduleid, 'instance' => $profile->quizid]);

        try {
            $transaction = $DB->start_delegated_transaction();
            course_delete_module($cmid);
            course_delete_section($profile->courseid, $profile->sectionid);
            $DB->delete_records('course_sections',array('course'=>$profile->courseid, 'section' =>$profile->sectionid));
            (new \local_trainingprogram\local\trainingprogram)->delete_offering_mapped_groups($profile->profilecode);
            $DB->delete_records('local_exam_profiles', array('id' => $id));
            $transaction->allow_commit();
        }catch(moodle_exception $e){
            $DB->rollback($e);
        }

        return true;
    }

    public function exam_profileinfo($profileid) {
        global $DB, $PAGE;
        $profile = $this->examprofile($profileid);
        $renderer = $PAGE->get_renderer('local_exams');
        return $renderer->exam_profileinfo($profile);
    }

    public function examprofile($id)
    {
        global $DB;
        $profile = $DB->get_record('local_exam_profiles', ['id' => $id]);
        $examprofile = [];
        $examprofile['activestatus'] = !empty($profile->activestatus) ? get_string('yes', 'local_exams') : get_string('no', 'local_exams');
        $examprofile['publishstatus'] = !empty($profile->publishstatus) ? get_string('yes', 'local_exams') : get_string('no', 'local_exams');
        $examprofile['hascertificate'] = !empty($profile->hascertificate) ? get_string('yes', 'local_exams') : get_string('no', 'local_exams');
        $examprofile['preexampage'] = !empty($profile->preexampage) ? get_string('yes', 'local_exams') : get_string('no', 'local_exams');
        $examprofile['successrequirements'] = !empty($profile->successrequirements) ? get_string('yes', 'local_exams') : get_string('no', 'local_exams');
        $examprofile['showquestions'] = !empty($profile->showquestions) ? get_string('yes', 'local_exams') : get_string('no', 'local_exams');
        $examprofile['showexamduration'] = !empty($profile->showexamduration) ? get_string('yes', 'local_exams') : get_string('no', 'local_exams');
        $examprofile['showremainingduration'] = !empty($profile->showremainingduration) ? get_string('yes', 'local_exams') : get_string('no', 'local_exams');
        $examprofile['commentsoneachque'] = !empty($profile->commentsoneachque) ? get_string('yes', 'local_exams') : get_string('no', 'local_exams');
        $examprofile['commentsaftersub'] = !empty($profile->commentsaftersub) ? get_string('yes', 'local_exams') : get_string('no', 'local_exams');
        $examprofile['showexamresult'] = !empty($profile->showexamresult) ? get_string('yes', 'local_exams') : get_string('no', 'local_exams');
        $examprofile['showexamgrade'] = !empty($profile->showexamgrade) ? get_string('yes', 'local_exams') : get_string('no', 'local_exams');
        $examprofile['competencyresult'] = !empty($profile->competencyresult) ? get_string('yes', 'local_exams') : get_string('no', 'local_exams');
        $examprofile['resultofeachcompetency'] = !empty($profile->resultofeachcompetency) ? get_string('yes', 'local_exams') : get_string('no', 'local_exams');
        $examprofile['evaluationform'] = !empty($profile->evaluationform) ? get_string('yes', 'local_exams') : get_string('no', 'local_exams');
        $examprofile['notifybeforeexam'] = !empty($profile->notifybeforeexam) ? get_string('yes', 'local_exams') : get_string('no', 'local_exams');
        $descisions = $this->descision();
        $examprofile['decision'] = $descisions[$profile->decision];
        $examprofile['language'] = $this->profilelanguage($profile->language);
        $examprofile['duration'] = ($profile->duration/60).' '.get_string('min','local_exams');
        $examprofile['seatingcapacity'] = $profile->seatingcapacity;
        $examprofile['questions'] = $profile->questions;

        $examprofile['trailquestions'] = $profile->trailquestions;
        $targetaudience = $this->targetaudience();
        $examprofile['targetaudience'] = $targetaudience[$profile->targetaudience];
        $examprofile['nondisclosure'] = format_text($profile->nondisclosure, FORMAT_HTML);
        $examprofile['instructions'] = format_text($profile->instructions, FORMAT_HTML);
        $examprofile['registrationstartdate'] = !empty($profile->registrationstartdate) ? userdate($profile->registrationstartdate, get_string('strftimedatemonthabbr', 'core_langconfig')) : '--';
        $examprofile['registrationenddate'] = !empty($profile->registrationenddate) ? userdate($profile->registrationenddate, get_string('strftimedatemonthabbr', 'core_langconfig')) : '--';
        $examprofile['password'] = !empty($profile->password) ? $profile->password : '--';
        $examprofile['passinggrade'] = $profile->passinggrade;

        if (empty($profile->discount)) {
            $examprofile['discount'] = '--';
        } else {
            $discounts = $this->discount();
            $examprofile['discount'] = $discounts[$profile->discount];
        }

        return $examprofile;
    }

    public static function descision() {
        $descisions = array(
            self::APPROVED => get_string('approved','local_exams'),
            self::REJECTED => get_string('rejected','local_exams'),
            self::UNDERREVIEW => get_string('underreview','local_exams'),
            self::DRAFT => get_string('draft','local_exams'),
        );

        return $descisions;
    }

    public static function discount() {
        $descisions = array(
            self::EARLYREGISTARATION => get_string('earlyregistration','local_exams'),
            self::GROUP => get_string('group','local_exams'),
            self::COUPONS => get_string('coupons','local_exams'),
        );

        return $descisions;
    }

    public static function targetaudience() {
        $descisions = array(
            self::SAUDI => get_string('saudi','local_exams'),
            self::NONSAUDI => get_string('nonsaudi','local_exams'),
            self::BOTH => get_string('both','local_exams'),
        );

        return $descisions;
    }

    public function userprofiles($examid)
    {
        global $DB, $USER;
        $lang = current_language();
        $exam = $DB->get_record('local_exams', ['id' => $examid]);

        $profileinfo = [];
        $profileinfo['id'] = $exam->id;
        if ($lang == 'ar') {
            $profileinfo['exam'] = $exam->examnamearabic;
        } else {
            $profileinfo['exam'] = $exam->exam;
        }
        $profileinfo['code'] = $exam->code;

        $attempsnumber =(int) $this->quiz_attemptscount($examid);
        $sql = "select id, attemptid from {local_exam_attempts} where id = (select min(id) from {local_exam_attempts} where examid = $examid AND  attemptid > $attempsnumber)";
        $examattempt = $DB->get_record_sql($sql);
        if($examattempt->attemptid == 1 || empty($examattempt)) {
            $user_attemptid = !empty($examattempt->id) ? $examattempt->id : 0;
            $data = [0, $user_attemptid];
            $userattemptid = implode(',', $data);
        } else {
            $userattemptid = $examattempt->id;
        }

        // $sql = "SELECT profileid 
        //         FROM {local_exam_userhallschedules} leu
        //         WHERE examid = $examid AND attemptid IN ($userattemptid) AND userid =".$USER->id;
        // $purchasedprofile = $DB->get_field_sql($sql);
        $userid = $USER->id;
        $sql = "SELECT leu.id, leu.profileid 
                  FROM {local_exam_userhallschedules} leu
                 WHERE examid = $examid AND userid = $userid 
                 ORDER BY id DESC ";
        $scheduleinfo = $DB->get_record_sql($sql);
        $selectedprofileid = $scheduleinfo->profileid;
        $selectedid = $scheduleinfo->id;
        $profileinfo['scheduleid'] = !empty($selectedid) ? $selectedid : 0;
        if ($selectedprofileid) {
            $profile = $DB->get_record('local_exam_profiles', ['id' => $selectedprofileid]);
            $profileinfo['profileid'] = !empty($selectedprofileid) ? $selectedprofileid : NULL;
            $profileinfo['purchasedprofile'] = !empty($selectedprofileid) ? $selectedprofileid : NULL;
            $profileinfo['profilecode'] =$profile->profilecode;
            $profileinfo['showexamduration'] = $profile->showexamduration;
            // $profileinfo['language'] = get_string($profile->language, 'local_exams');
            $profileinfo['language'] = $this->profilelanguage($profile->language);        
            $profileinfo['examduration'] = $profile->duration/60;
            $profileinfo['passinggrade'] = $profile->passinggrade;
            $profileinfo['instructions'] = $profile->instructions;
            $examdate = $DB->get_record('exam_enrollments', ['examid' => $exam->id, 'profileid' => $profile->id, 'userid' => $USER->id]);
            $result = $this->userscheduledate($examid, $profile, $profile->quizid);
            $attemptpurchase = $this->examattemptpurchases($exam->id);
            $profileinfo['purchase'] = $result['purchase'];
            if (($attemptpurchase == $attempsnumber) || $attempsnumber == 0) {
                $row['launch'] = true;
                $profileinfo['examdatetime'] = !empty($purchase) ? userdate($purchase, get_string('strftimedatemonthabbr', 'core_langconfig')) : 0;
            } else {
                $row['launch'] = false;
                $profileinfo['examdatetime'] = 0;    
            }
            if($examdate->hallscheduleid){
                $sql = "SELECT h.name, h.maplocation
                      FROM {hall} h 
                      JOIN {hallschedule} hs ON hs.hallid = h.id 
                      WHERE hs.id =".$examdate->hallscheduleid;
                $halldetails = $DB->get_record_sql($sql);
                $profileinfo['name'] = $halldetails->name;
                $profileinfo['maplocation'] = $halldetails->maplocation;
            }            

            $profileinfo['userview'] = true;
            $profileinfo['certificateid'] = $this->certificate($exam->id);
            $profileinfo['quizid'] = $this->coursemoduleid($exam->courseid, $profile->quizid); 
        } else {
            $profileinfo["purchase"] = true;
        }
        $examdetails = $this->examdetails($exam->id);
        $profileinfo['grievanceexist'] = $examdetails['grievanceexist'];
        $profileinfo["courseid"] = $exam->courseid;
        $component = 'local_exams';
        $profileinfo['checkfavornot'] =$this->checkfavourites($examid,$USER->id,$component);

// grievance2 button visibility
        $scheduledata = $DB->get_record_sql("SELECT * FROM {local_exam_userhallschedules} WHERE examid = $examid AND userid = $USER->id AND profileid = $selectedprofileid");
        $grade_grieviance = $this->grieviance_gradestatus($scheduledata->id);
        $profileinfo["grievance2"] = $grade_grieviance;
        return $profileinfo;
    }

    public function quiz_attemptscount($examid, $userid=false, $quizid=false)
    {
        global $DB, $USER;
        if (empty($userid)) {
            $userid = $USER->id;
        }
        
        $appliedperiod = $DB->get_field('local_exams', 'appliedperiod', ['id'=>$examid]);
        $sql = "SELECT * 
                FROM {local_exam_userhallschedules} leu 
                WHERE leu.examid = $examid AND leu.hallscheduleid >0 AND leu.userid = ".$userid;
        if ($appliedperiod != 1) {
            $sql .= " AND FROM_UNIXTIME(leu.examdate, '%Y')=YEAR(CURDATE()) ";
        }
        $userschedules = $DB->get_records_sql($sql);
        $i = 0;
        $attemptnum = 0;
        foreach($userschedules as $userschedule) {
            $info = $this->scheduledates($userschedule->hallscheduleid);
            $userexamdate = $userschedule->examdate+$info['starttime']+$info['endtime'];
            $attempts = $DB->get_record('quiz_attempts', ['userid' => $USER->id, 'quiz' => $quizid, 'attempt' => ++$attemptnum]);

            if ($userexamdate <= time() || !empty($attempts)) {
                $attemptscount = ++$i;
            }
        }

        $attempsnumber = !empty($attemptscount) ? $attemptscount : 0;

        return $attempsnumber;
    }

    public function userscheduledate($examid, $profile, $quizid=false)
    {
        global $DB, $USER;
        if(!$examid){
            $date = '';
            $purchase = '';
        }else{
            $attemptid = $this->quiz_attemptscount($examid);
            $purchase = $this->user_scheduledata($profile, $attemptid);
            $userexamdate = $purchase->examdate;
            $sql = "SELECT ea.timecreated
                      FROM {local_exam_attemptpurchases} as ea
                      JOIN {local_exams} as e ON e.id=ea.examid
                     WHERE ea.examid = $examid AND ea.userid =".$USER->id;
            $lastpurchase = $DB->get_field_sql($sql);

            $daysbeforeattempt = $DB->get_field('local_exam_attempts', 'daysbeforeattempt', ['examid' => $examid, 'attemptid' => $attemptid]);
            $date = strtotime("+".$daysbeforeattempt."day", $lastpurchase);
            $attemptpurchases = $this->examattemptpurchases($examid);

            if (!empty($purchase) && empty($purchase->examdate)) {
                $purchase = false;
            } else {
                $purchase = true;
            }
        }

        return COMPACT('purchase', 'date', 'userexamdate');
    }

    public function examattemptpurchases($examid)
    {
        global $DB, $USER;
        if(!$examid){
            return false;
        }
        $sql = "SELECT COUNT(lea.id) 
                  FROM {local_exam_attemptpurchases} as lea
                  JOIN {local_exams} as e ON e.id=lea.examid
                 WHERE lea.userid = $USER->id AND lea.examid =". $examid;
        $appliedperiod = $DB->get_field('local_exams', 'appliedperiod', ['id'=>$examid]);
        if ($appliedperiod != 1) {
            $sql .= " AND FROM_UNIXTIME(lea.timecreated, '%Y')=YEAR(CURDATE()) ";
        }
        $attemptpurchase = $DB->get_field_sql($sql);

        return $attemptpurchase;
    }

    public function profiles($examid) {
        global $DB;
        $lang = current_language();
        $sql = "SELECT ep.* FROM {local_exam_profiles} ep WHERE 1=1 ";

        $conditionssql = " AND ep.examid = {$examid} AND ep.activestatus = 1 AND ep.publishstatus = 1 AND ep.decision = 1";
        $profiles = $DB->get_records_sql($sql.$conditionssql);
        $data = [];
        foreach($profiles as $profile) {
            if(empty($profile->registrationstartdate) || $profile->registrationstartdate <=strtotime(date('d-m-Y')) ) {
                $row = [];
                $row['examid'] = $examid;
                $examname = $DB->get_record('local_exams', ['id' => $examid], 'exam, examnamearabic');
                if ($lang == 'ar') {
                   $row['exam'] = $examname->examnamearabic;
                } else {
                   $row['exam'] = $examname->exam;
                }
                if(empty($profile->registrationenddate) || $profile->registrationenddate>strtotime(date('d-m-Y'))) {
                   $row['expired'] = "1";
                } else {
                    continue;
                }
                $profilescount = $DB->count_records_sql("SELECT COUNT(id) FROM {local_exam_userhallschedules} WHERE profileid = $profile->id");
                if ($profile->seatingcapacity > 0 && $profilescount == $profile->seatingcapacity || $profilescount > $profile->seatingcapacity) {
                    $row['profileenrollment'] = false;    
                } else {
                    $row['profileenrollment'] = true;
                }
                $row['profilecode'] = $profile->profilecode;
                $row['registrationstart'] = $profile->registrationstartdate ? 
                                            userdate($profile->registrationstartdate, get_string('strftimedaydate','core_langconfig')) : 0;
                $row['registrationend'] = $profile->registrationenddate ? 
                                            userdate($profile->registrationenddate, get_string('strftimedaydate','core_langconfig')) : 0;
                $row['duration'] = $profile->duration/60;
                $row['passinggrade'] = $profile->passinggrade;
                $language = $profile->language;

                if ($profile->material == 0) {
                    $attachmentLink = examlearningmaterial_url($profile->materialfile, 'materialfile');
                } else {
                    if(!empty($profile->materialurl)) {
                        $attachmentLink = $profile->materialurl;
                    } else {
                        $attachmentLink = NULL;
                    }
                }

                $row['language'] = $this->profilelanguage($language);
                $row['profile'] = $attachmentLink;
                $row['product_variations'] = (new \tool_product\product)->get_product_variations($examid, $profile->id, 2);
                $row['currentlang'] = $lang;
                $profilescount = COUNT($profiles);
                if ($profilescount == 1) {
                    $row['profilechecked'] = true;
                } else {
                    $row['profilechecked'] = false;
                }
                $data[] = $row;
            }            
        }
      
        return $data;
    }

       public function get_listof_hallschedules($stable, $filterdata) {
        global $DB, $USER;
        $systemcontext = context_system::instance();
        $filterdata = (array)$filterdata;
        $exam = $DB->get_record('local_exams', ['id'=>$filterdata['examid']]);
        $lang = current_language();
        $data = [];
        if (!empty($filterdata['variation']) || $stable->type == 'bulkenrollment') {
            $referenceid = $DB->get_field('tool_products', 'referenceid', ['id' => $filterdata['variation']]);
            if ($filterdata['profileid'] > 0){
                $profile = $DB->get_record('local_exam_profiles', ['id' => $filterdata['profileid']]);
            } else {
                $referenceid = $DB->get_field('tool_products', 'referenceid', ['id'=>$filterdata['variation']]);
                $profile = $DB->get_record('local_exam_profiles', ['id' => $referenceid]);
            }
            if (!empty($filterdata['halladdress'])) {
                $hallid = $filterdata['halladdress'];
            } else {
                $hallid = 0;
            }

            $hall = $DB->get_record('hall', ['id' => $hallid]); 
            $countsql =  " SELECT COUNT(hs.id) ";
            $selectsql = " SELECT hs.* ";
            $fromsql = " FROM {hallschedule} hs
            WHERE hs.status = 0 AND hs.hallid = {$hallid}  "; // statu
            // $fromsql = " FROM {hallschedule} hs
            //                 WHERE hs.status = 0 AND hs.hallid = {$hallid} AND (hs.endtime - hs.starttime) >= {$profile->duration}  "; 
            if($stable->type != 'bulkenrollment') {

                if(!isloggedin()) {
                    $fromsql.= 'AND hs.directedto IN (3)';
                } elseif (isloggedin() && (!is_siteadmin() && has_capability('local/organization:manage_trainee',$systemcontext))) {
                    $getuserdata = $DB->get_record_sql('SELECT * FROM {local_users} WHERE userid='.$USER->id);                
                    if($getuserdata->gender==1) {
                        $fromsql.= 'AND hs.directedto IN (1,3)';
                    }
                    if($getuserdata->gender==2) {
                        $fromsql.= 'AND hs.directedto IN (2 ,3)';
                    }
                } elseif((!is_siteadmin() && has_capability('local/organization:manage_organizationofficial',$systemcontext))) {
                    $checkuserid = is_numeric($stable->tuserid);
                    if($checkuserid)
                    {
                        $userids = $stable->tuserid;
                    }
                    else
                    {
                        $userids = base64_decode($stable->tuserid);
                        
                    }
                    $getusers = $DB->get_records_sql('SELECT * FROM {local_users} WHERE userid IN ('.$userids.')');
                    $userid = [];
                    foreach($getusers as $user) {
                        $userid[] = $user->gender;
                    }
                    $count = count(array_unique($userid));
                    if($count>1) {
                        $fromsql.= 'AND hs.directedto=3';
                    } else {
                        if($userid[0]==1) {
                            $fromsql.= 'AND hs.directedto IN (1,3)';
                        } else {
                            $fromsql.= 'AND hs.directedto IN (2 ,3)';
                        }
                    }
                } else {
                    $getuserdata = $DB->get_record_sql('SELECT * FROM {local_users} WHERE userid='.$stable->tuserid);                
                    if($getuserdata->gender==1) {
                        $fromsql.= 'AND hs.directedto IN (1,3)';
                    }
                    if($getuserdata->gender==2) {
                        $fromsql.= 'AND hs.directedto IN (2 ,3)';
                    }
                }   
            }

            if($filterdata['examdatetime[enabled]'] == 1 && $filterdata['enddate[enabled]'] == 1){
                $start_year = $filterdata['examdatetime[year]'];
                $start_month = $filterdata['examdatetime[month]'];
                $start_day = $filterdata['examdatetime[day]'];
                $filterstartdate = mktime(0,0,0, $start_month, $start_day, $start_year);

                $start_year = $filterdata['enddate[year]'];
                $start_month = $filterdata['enddate[month]'];
                $start_day = $filterdata['enddate[day]'];
                $filterenddate=mktime(23,59,59, $start_month, $start_day, $start_year);
            } elseif($filterdata['examdatetime[enabled]'] == 1 ){
                $start_year = $filterdata['examdatetime[year]'];
                $start_month = $filterdata['examdatetime[month]'];
                $start_day = $filterdata['examdatetime[day]'];
                $filterstartdate = mktime(0,0,0, $start_month, $start_day, $start_year);
            } elseif($filterdata['enddate[enabled]'] == 1 ){
                $start_year = $filterdata['enddate[year]'];
                $start_month = $filterdata['enddate[month]'];
                $start_day = $filterdata['enddate[day]'];
                $filterenddate=mktime(23,59,59, $start_month, $start_day, $start_year);
            }

            $startdate = 0;
            $enddate = 0;
            if(isset($filterstartdate) && isset($filterenddate)){
                    $startdate = $filterstartdate;
                    $enddate = $filterenddate;
            }else{
                if(isset($filterstartdate) && $filterstartdate < $enddate){
                    $startdate = $filterstartdate;
                }
                if(isset($filterenddate) && $filterenddate < $enddate){
                    $startdate = $filterenddate;
                }
                if (isset($filterenddate) && $filterenddate > $enddate) {
                    $enddate = $filterenddate;
                }        
            }
            
            $searchparams = [];
            $ownedbyvalue = $DB->get_field('local_exams','ownedby',['id'=>$profile->examid]);
            $ownedbyata = get_config('local_exams','ownedby');
            $ownedbyata = json_decode($ownedbyata);
            $getownedbydata= [];
            foreach($ownedbyata AS $key=>$value) {
                $getownedbydata[$key] = $value;
            }
            $finalownedbykey =str_replace(' ','_',$ownedbyvalue);
            $ownedbydays =(int) $getownedbydata[$finalownedbykey];
            $days = ($ownedbydays >=1) ? $ownedbydays : 0;
            $ownedbydate = strtotime("+$days day", time());

            // Attempt before days
            $attempsnumber = self::quiz_attemptscount($filterdata['examid']);
            $lastattempt = $this->lastattempt($exam->id);
            $sql = "SELECT id, daysbeforeattempt, fee
                        FROM {local_exam_attempts}
                        WHERE id = (SELECT min(id) FROM {local_exam_attempts} WHERE examid = $exam->id AND  attemptid > $attempsnumber)";
            $days = $DB->get_record_sql($sql);
            $attemptdate = strtotime("+". $days->daysbeforeattempt ."day", $lastattempt);
            if ($ownedbydate > $attemptdate) {
                $currentdate = $ownedbydate;
            } else {
                $currentdate = $attemptdate;
            }

            $examschedules = $DB->record_exists('hallschedule', ['entityid' => $exam->id, 'hallid'=>$hallid]); // Checking the schedules are present for the exam
            if (!empty($examschedules)) {
                $fromsql.= " AND hs.entityid = ".$exam->id;
                $enddatesql = "SELECT FROM_UNIXTIME(startdate) FROM {hallschedule} WHERE entityid = {$exam->id} ORDER BY startdate DESC";
            } else {
                $fromsql.= " AND hs.entityid = 0 ";
                $enddatesql = "SELECT FROM_UNIXTIME(startdate) FROM {hallschedule} WHERE entityid = 0 ORDER BY startdate DESC";
            }
            $scheduleenddate = $DB->get_field_sql($enddatesql);
            $fascheduledata = [];
            $fascheduledata['FromDate'] = userdate($currentdate, '%Y-%m-%d');
            $fascheduledata['ToDate'] = $scheduleenddate;
            if($profile->registrationstartdate){
                $fascheduledata['FromDate'] = userdate($profile->registrationstartdate, '%Y-%m-%d'). ' 00:00:00';
            }
            if($profile->registrationenddate){
                $fascheduledata['ToDate'] = userdate($profile->registrationenddate, '%Y-%m-%d'). ' 00:00:00';
            }
            if($filterstartdate > 0 && $filterenddate > 0){
                $fascheduledata['FromDate'] = userdate($filterstartdate, '%Y-%m-%d'). ' 00:00:00';
                $fascheduledata['ToDate'] = userdate($filterenddate, '%Y-%m-%d'). ' 00:00:00';
            }else if($filterstartdate > 0 && $filterenddate <= 0){
                $fascheduledata['FromDate'] = userdate($filterstartdate, '%Y-%m-%d'). ' 00:00:00';
            }else if($filterstartdate <= 0 && $filterenddate > 0){
                $fascheduledata['ToDate'] = userdate($filterenddate, '%Y-%m-%d'). ' 00:00:00';
            }

            $fascheduledata['centercode'] = $hall->code;

            // Checking Get FAST Schedules service is Enabled or not
            $accessstatus = $this->access_fast_service('getfaschedules');
            if ($accessstatus) {
                // API to get schedules from fa
                $attemptapi = new \local_exams\local\attemptapi();
                $response = $attemptapi->get_scheduleavailability($fascheduledata);
                if (json_decode($response)) {
                    $faschedules = json_decode($response)->pageData;
                    $facount = 0;
                    foreach($faschedules as $faschedule) {
                        $availableseats = $faschedule->capacity - $faschedule->reservedSeats;
                        if ($availableseats > 0) {
                            if ($facount == 0) {
                                $fromsql.= " AND ";    
                            } else {
                                $fromsql.= " OR ";
                            }
                            $str_time = $faschedule->fromTime;
                            sscanf($str_time, "%d:%d:%d", $fastarthours, $fastartminutes, $fastartseconds);                
                            $fastarttime = isset($seconds) ? $fastarthours * 3600 + $fastartminutes * 60 + $fastartseconds : $fastarthours * 3600 + $fastartminutes*60;
                            
                            $end_time = $faschedule->toTime;
                            sscanf($end_time, "%d:%d:%d", $hours, $minutes, $seconds);
                            $faendtime = isset($seconds) ? $hours * 3600 + $minutes * 60 + $seconds : $hours * 3600 + $minutes*60;
                            
                            $fastarttimeinseconds = usertime($fastarttime);
                            $faendtimeinseconds = usertime($faendtime);
                            $fadate = str_replace("T00:00:00", "", $faschedule->dayDate);
                            $fromsql.= " (FROM_UNIXTIME(hs.startdate, '%Y-%m-%d') = '{$fadate}' AND hs.starttime = {$fastarttimeinseconds} AND hs.endtime = {$faendtimeinseconds} ) ";
                            ++$facount;
                        }
                    }

                    $params = array_merge($searchparams);
                    $ordersql = " ORDER BY hs.id, hs.startdate ASC ";
                    $schedules = $DB->get_records_sql($selectsql. $fromsql .$ordersql, $params, $stable->start,$stable->length);
                    $totalschedules = $DB->count_records_sql($countsql. $fromsql, $params);
                } else {
                    $schedules = [];
                    $totalschedules = 0;
                }
            } else {
                $str_time = userdate(time(), '%X');
                sscanf($str_time, "%d:%d:%d", $hours, $minutes, $seconds);
                $ctseconds = usertime($hours * 3600 + $minutes * 60);
                $starttime = strtotime($fascheduledata['FromDate']) + $ctseconds;

                $fromsql.= " AND (UNIX_TIMESTAMP(FROM_UNIXTIME(hs.startdate, '%Y-%m-%d')) + hs.starttime) >= $starttime ";
                $params = array_merge($searchparams);
                $ordersql = " ORDER BY hs.id, hs.startdate ASC ";
                $schedules = $DB->get_records_sql($selectsql. $fromsql .$ordersql, $params, $stable->start,$stable->length);
                $totalschedules = $DB->count_records_sql($countsql. $fromsql, $params);
            }

            foreach($schedules as $schedule) {
                $profilestartdate = strtotime("+2 day", $profile->registrationstartdate);
                $row = [];
                $row['id'] = $schedule->id;
                $row['hallid'] = $schedule->hallid;
                if($stable->status == 'en') {
                    $row['enrolbtn'] = true;
                } else {
                    $row['enrolbtn'] = false;
                }

                $row['startdate'] = userdate($schedule->startdate,get_string('strftimedatemonthabbr', 'core_langconfig'));
                $row['starttime'] = userdate($schedule->starttime, get_string('strftimetime24', 'langconfig'));
                $row['endtime']=userdate($schedule->endtime, get_string('strftimetime24', 'langconfig'));
                $start = gmdate('a',$schedule->starttime);
                $end = gmdate('a',$schedule->endtime);

                if($lang == 'ar') {    
                    $start = ($start == 'am')? 'صباحا':'مساءً';
                    $end = ($end == 'am')? 'صباحا':'مساءً';
                } else {    
                    $start = ($start == 'am')? 'AM':'PM';
                    $end = ($end == 'am')? 'AM':'PM';
                }
                $row['days'] = get_string($schedule->days,'local_exams');
                $row['product_variations'] = (new product)->get_product_variations($filterdata['examid'], $referenceid, 2);
                $row['product_variations']['hallscheduleid'] = $schedule->id;
                $row['isadmin'] = (is_siteadmin() || has_capability('local/organization:manage_examofficial',$systemcontext)) ? true : false;
                $row['orgoff'] = (!is_siteadmin() && has_capability('local/organization:manage_organizationofficial',$systemcontext)) ? true : false;
                $row['examid'] = $filterdata['examid'];

                $row['profileid'] = $filterdata['profileid'];
                $row['isenrolled'] = self::is_enrolled($filterdata['examid'],$USER->id);
                $seats = $DB->get_field('hall', 'seatingcapacity', ['id' => $schedule->hallid]);
                $enrolledseats = $DB->count_records('local_exam_userhallschedules', ['hallscheduleid' => $schedule->id]);

                if($schedule->seatingcapacity!=0) {
                    $remainingseats = $schedule->seatingcapacity;
                } else {
                    $getsameschedule = $DB->get_fieldset_sql('SELECT seatingcapacity FROM {hallschedule} WHERE startdate ='.$schedule->startdate.' AND starttime='.$schedule->starttime.' AND endtime='.$schedule->endtime.' AND hallid='.$schedule->hallid.'');
                    if($getsameschedule) {
                        $sumofseats = array_sum($getsameschedule);
                    }
                    $remainingseats = $seats - $sumofseats;
                }
                if ($enrolledseats < $remainingseats) {
                    $row['seatscompleted'] = false;
                } else {
                    $row['seatscompleted'] = true;
                }
                if($stable->type){
                    $row['type'] = true;
                } else {
                    $row['type'] = false;
                }
                $row['typename'] = ($stable->type) ? $stable->type : '';
                if ($stable->type == 'reschedule' && (!is_siteadmin() && has_capability('local/organization:manage_trainee',$systemcontext))) {
                    $row['reschedulebtn'] = true;
                } else {
                    $row['reschedulebtn'] = false;
                }
                $row['damount'] = $stable->damount;
                $row['organization'] = $stable->organization;
                $row['orgofficial'] = $stable->orgofficial;
                $row['productid'] = $filterdata['variation'];
                $row['tuserid'] = !empty($stable->tuserid) ? $stable->tuserid : 0;
                // $variation = $DB->get_field('tool_products', 'id', ['category' => 2, 'referenceid' => $attemptid]);
                $toolproduct = new stdClass();
                if ($exam->appliedperiod == 1) {
                    if ($attempsnumber == 0) {
                        $category = self::EXAMS;
                        $toolproduct->id = $filterdata['variation'];
                    } else {
                        $attemptnum = ++$attempsnumber;
                        $category = self::EXAMATTEMPT;
                        if ($attemptnum <= $exam->noofattempts) {
                            $attemptid = $DB->get_field('local_exam_attempts', 'id', ['examid' => $exam->id, 'attemptid' => $attemptnum]);
                            $toolproduct = $DB->get_record('tool_products', ['category' => $category, 'referenceid' => $attemptid]);
                        }
                    }
                } else {
                    $enrolledid = $DB->get_field('exam_enrollments', 'id', ['examid'=>$exam->id, 'userid'=>$USER->id]);
                    if (empty($enrolledid)) {
                        $category = self::EXAMS;
                        $toolproduct->id = $filterdata['variation'];
                    } else {
                        $attemptnum = ++$attempsnumber;
                        $category = self::EXAMATTEMPT;
                        if ($attemptnum <= $exam->noofattempts) {
                            $attemptid = $DB->get_field('local_exam_attempts', 'id', ['examid' => $exam->id, 'attemptid' => $attemptnum]);
                            $toolproduct = $DB->get_record('tool_products', ['category' => $category, 'referenceid' => $attemptid]);
                        }
                    }
                }
                $row['product_attributes'] = (new product)->get_product_attributes($filterdata['examid'], $category, 'purchase', true, $filterdata['variation']);
                $row['product_attributes']['hallscheduleid'] = $schedule->id;
                $row['product_attributes']['profileid'] = $filterdata['profileid'];
                $row['product_attributes']['processtype'] = 'purchase';
                $row['product_attributes']['tandc'] = ($exam->termsconditions != null) ? 1 : 0;
                $data[] = $row;
            }
        }
        $coursesContext = array(
            "hallschedules" => $data,
            "totalschedules" => $totalschedules,
            "length" => COUNT($data),
        );

        return $coursesContext;
    }

    public function hallschedules()
    {
        global $PAGE;
        $renderer = $PAGE->get_renderer('local_exams');
        $filterparams  = $renderer->get_catalog_hallschedules(true);
        $filterparams['submitid'] = 'form#filteringform';
        $filterparams['widthclass'] = 'col-md-12';
        $filterparams['globalinput'] = $renderer->global_filter($filterparams);
        $hallsmform = exams_reservations_form($filterparams);
        $filterparams['halldetails'] = $renderer->get_catalog_hallschedules();
        $filterparams['q'] = $searchquery;
        $filterparams['inputclasses'] = 'examssearchinput';
        $filterparams['placeholder'] = get_string('search_skill','local_exams');
        $filterparams['filterform'] = $hallsmform->render();
        echo $renderer->listofreservations($filterparams);
    }

    public function exams()
    {
        global $PAGE;
        $renderer = $PAGE->get_renderer('local_exams');
        $filterparams  = $renderer->get_catalog_publishexams(true);
        $filterparams['submitid'] = 'form#filteringform';
        $filterparams['widthclass'] = 'col-md-12';
        $filterparams['globalinput'] = $renderer->global_filter($filterparams);
        $fform = exams_filters_form($filterparams);
        $filterparams['halldetails'] = $renderer->get_catalog_publishexams();
        $filterparams['q'] = $searchquery;
        $filterparams['inputclasses'] = 'examssearchinput';
        $filterparams['placeholder'] = get_string('search_skill','local_exams');
        $filterparams['examfilterform'] = $fform->render();
        echo $renderer->listofpublishedexams($filterparams);        
    }

    public function examattempts($stable, $filterdata)
    {
        global $DB;
        $selectsql = "SELECT * ";
        $countsql = "SELECT COUNT(lea.id) ";
        $fromsql = " FROM {local_exam_attempts} lea 
                       WHERE lea.examid =". $filterdata['examid'];
        $params=[];
        if(isset($filterdata['search_query']) && trim($filterdata['search_query']) != ''){
            $fromsql .= " AND lea.attemptid LIKE :search";
            $searchparams = array('search' => '%'.trim($filterdata['search_query']).'%');
        }
        $params = array_merge($searchparams);
        $totalattempts = $DB->count_records_sql($countsql.$fromsql, $params);
        $attempts = $DB->get_records_sql($selectsql.$fromsql, $params, $stable->start,$stable->length);

        $data = [];
        foreach($attempts as $attempt) {
            $row = [];
            $row['id'] = $attempt->id;
            $row['examid'] = $attempt->examid;
            $row['attemptid'] = $attempt->attemptid;
            $row['daysbeforeattempt'] = $attempt->daysbeforeattempt;
            $row['fee'] = $attempt->fee;
            if ($attempt->attemptid == 1) {
                $row['actions'] = false;
            } else {
                $row['actions'] = true;
            }
            $data[] = $row;
        }

        $coursesContext = array(
            "attempts" => $data,
            "nocourses" => $nocourse,
            "totalattempts" => $totalattempts,
            "length" => COUNT($data),
        );

        return $coursesContext;
    }
    public function attemptpurchase($summary)
    {
        global $DB, $USER;
        if ($summary['processtype'] == 'reschedule') {
            self::exam_enrollmet($summary['profileid'], $summary['userid'], $summary['scheduleid'], $summary['processtype']);
            return true;
        }
        $items = $summary['items'];
        foreach($items as $item) {
            $row = [];
            $row['productid'] = $item['product_id'];
            $scheduleid = $item['hallscheduleid'];
            if ($scheduleid) {
                $examdate = $DB->get_field('hallschedule', 'startdate', ['id'=>$scheduleid]);
            }
            $row['examdate'] = !empty($examdate) ? $examdate : 0;
            $row['hallscheduleid'] = !empty($item['hallscheduleid']) ? $item['hallscheduleid'] : 0;
            $row['userid'] = $USER->id;
            $row['realuser'] = ($USER->realuser) ? $USER->realuser :0;
            $row['referencetable'] = 'local_exam_attempts';
            $referenceid = $DB->get_field('tool_products', 'referenceid', ['id' => $item['product_id']]);
            $row['referenceid'] = $referenceid;
            $row['attemptid'] = !empty($referenceid) ? $referenceid : 0;
            $examid = $DB->get_field('local_exam_attempts', 'examid', ['id' => $referenceid]);
            $row['examid'] = $examid;
            $data = new stdClass();
            $data->userid =  $USER->id;
            $data->examid = $examid;

            if ($item['category'] == 4 ){
                (new \local_exams\notification)->exams_notification('exam_result_objection', $touser = null,$fromuser = get_admin(), $data,$waitinglistid=0);
            }

            $row['profileid'] = $item['profileid'];
            $row['timecreated'] = time();
            $attempt = $DB->record_exists_sql("SELECT id FROM {local_exam_attemptpurchases} WHERE userid = $USER->id AND referenceid = ". $referenceid);
            if ($item['category'] == 6 && empty($attempt)) {
                $DB->insert_record('local_exam_attemptpurchases', $row);
                $record= $DB->insert_record('local_exam_userhallschedules', $row);
            }
        }
        return true;
    }
    public function userexamrefundinfo($summary)
    {
        global $DB;
        $referenceid = $DB->get_field('tool_products', 'referenceid', ['id' => $summary['productid']]);
        $data = new stdClass();
        if ($summary['category'] == 6) {
            $examid = $DB->get_field('local_exam_attempts', 'examid', ['id' => $referenceid]);
            $schedule = $DB->get_record('local_exam_userhallschedules', ['examid' => $examid, 'attemptid' => $referenceid, 'userid' => $summary['userid']]);
        } else {
            $examid = $DB->get_field('local_exam_profiles', 'examid', ['id' => $referenceid]);
            $schedule = $DB->get_record('local_exam_userhallschedules', ['examid' => $examid, 'profileid' => $referenceid, 'attemptid' => 0, 'userid' => $summary['userid']]);
        }

        $data->id = $schedule->id;
        $data->examdate = $schedule->examdate;
        $data->examid = $examid;
        $data->profileid = $referenceid;
        
        return $data;
    }
    public function exam_attempts($examid)
    {
        global $PAGE;
        $renderer = $PAGE->get_renderer('local_exams');
        $filterparams  = $renderer->get_catalog_examattempts(true);
        $filterparams['submitid'] = 'form#filteringform';
        $filterparams['globalinput'] = $renderer->global_filter($filterparams);
        $filterparams['halldetails'] = $renderer->get_catalog_examattempts();
        $filterparams['q'] = $searchquery;
        $filterparams['widthclass'] = 'col-md-12';
        $filterparams['inputclasses'] = 'examssearchinput';
        $filterparams['placeholder'] = get_string('search_skill','local_exams');
        $filterparams['examdetailsactions'] = true;
        $filterparams['examattempts'] = true;
        $filterparams['examid'] = $examid;
        $filterparams['actionaccess'] = true;
        echo $renderer->listofreservations($filterparams);
    }

    public function quizreattemptstatus($quizid)
    {
        global $DB, $USER;
        $record = $DB ->get_record('local_exam_profiles', ['quizid' => $quizid]);
        if($record) {

            $attempsnumber = $this->quiz_attemptscount($record->examid, $USER->id, $quizid);
            $attemptpurchase = $this->examattemptpurchases($record->examid);
            $userexamdate = $this->user_scheduledata($record, $attempsnumber);

            if ((($attemptpurchase == $attempsnumber) || $attempsnumber == 0) && userdate(time(), '%d-%m-%Y') >= $userexamdate->examdate ) {
                return true;
            } else {
                return false;
            }  

        } else {

            return true;
        }     
    }

    public function user_scheduledata($profile, $attempsnumber=false, $userid=false)
    {
        global $DB, $USER;
        if (empty($userid)) {
            $userid = $USER->id;
        }
        $where = '';
        $examid = (int)$profile->examid;
        $exam = $DB->get_record('local_exams', ['id'=>$examid]);
        $sql = "SELECT leu.id, leu.examdate
                  FROM {local_exam_userhallschedules} leu
                 WHERE leu.examid = $examid AND leu.userid = $userid  ORDER BY leu.id DESC ";
        $schedule = $DB->get_record_sql($sql);

        $sql = "SELECT leu.id, IF(leu.examdate = 0, '', FROM_UNIXTIME(leu.examdate, '%d-%m-%Y')) as examdate, h.name as hallname, hallscheduleid, leu.profileid, hs.starttime, hs.endtime, leu.attemptid, lep.duration, leu.enrolstatus,lep.passinggrade as gradetopass
                    FROM {local_exam_userhallschedules} leu
                    JOIN {local_exam_profiles} lep ON lep.id = leu.profileid
                LEFT JOIN {hallschedule} hs ON hs.id = leu.hallscheduleid 
                LEFT JOIN {hall} h ON h.id = hs.hallid
                    WHERE leu.examid = $examid  AND leu.userid = $userid ";
                    
        
        if ($exam->appliedperiod != 1 && $schedule->examdate > 0) {
            $where .= " AND FROM_UNIXTIME(leu.examdate, '%Y')=YEAR(CURDATE()) ";
        }
        $ordersql = " ORDER BY leu.id DESC";
        $userexamdate = $DB->get_record_sql($sql.$where.$ordersql);

        if($userexamdate) {
            return $userexamdate;
        } else {
            return $userexamdate = '';
        }
    }

    public function fast_examenrolservices() {
        global  $PAGE;
        $renderer = $PAGE->get_renderer('local_exams');
        $filterparams  = $renderer->get_catalog_fast_examenrol(true);
        $filterparams['submitid'] = 'form#filteringform';
        $filterparams['widthclass'] = 'col-md-12';
        $filterparams['placeholder'] = get_string('search','local_exams');
        $globalinput=$renderer->global_filter($filterparams);
        $details = $renderer->get_catalog_fast_examenrol(null);
        $fform = exams_fastexamenroll_filters_form($filterparams);
        $filterparams['fastexamenrol'] = $details;
        $filterparams['filterform'] = $fform->render();
        $filterparams['globalinput'] = $globalinput;
        echo $renderer->listoffexamenrol($filterparams);
    }

    public function getfastexamenrol($stable,$filterdata) {
        global $DB,$PAGE;
        $systemcontext = context_system::instance();
        $selectsql = "SELECT ee.*,le.code as code,ep.profilecode as examprofilecode,ee.username,
                             ee.transactiontypes,ee.validation,ee.examdatetime,ee.purchasedatetime
                            FROM {local_fast_examenrol} as ee 
                            JOIN {local_exams} as le ON ee.examcode = le.code
                            JOIN {local_exam_profiles} as ep ON ep.profilecode=ee.profilecode";
        $formsql =" WHERE 1=1 ";
       if  (isset($filterdata->search_query) && trim($filterdata->search_query) != ''){
            $formsql .= " AND (le.code LIKE :codesearch OR 
                            ep.profilecode LIKE :profilecodesearch OR 
                            ee.username LIKE :usernamesearch 
                            
                        ) ";
            $searchparams = array(
                  'codesearch' => '%'.trim($filterdata->search_query).'%',
                  'profilecodesearch' => '%'.trim($filterdata->search_query).'%',
                  'usernamesearch' => '%'.trim($filterdata->search_query).'%'
           );
        } else {
            $searchparams = array();
        }
        if($filterdata->{'examdatetimefrom[enabled]'} == 1 ){
            $start_year = $filterdata->{'examdatetimefrom[year]'};
            $start_month = $filterdata->{'examdatetimefrom[month]'};
            $start_day = $filterdata->{'examdatetimefrom[day]'};
            $start_hour = $filterdata->{'examdatetimefrom[hour]'};
            $start_minute = $filterdata->{'examdatetimefrom[minute]'};
            $filter_starttime_con = mktime($start_hour,$start_minute,0,$start_month,$start_day,$start_year);
            $formsql.= " AND UNIX_TIMESTAMP(ee.examdatetime)   >= $filter_starttime_con ";

        }
        if($filterdata->{'examdatetimeto[enabled]'} == 1 ){
            $start_year = $filterdata->{'examdatetimeto[year]'};
            $start_month = $filterdata->{'examdatetimeto[month]'};
            $start_day = $filterdata->{'examdatetimeto[day]'};
            $start_hour = $filterdata->{'examdatetimeto[hour]'};
            $start_minute = $filterdata->{'examdatetimeto[minute]'};
            $filter_endtime_con=mktime( $start_hour, $start_minute,59, $start_month, $start_day, $start_year);
            $formsql.= " AND UNIX_TIMESTAMP(ee.examdatetime)  <= $filter_endtime_con ";
            
        }
        if($filterdata->{'purchasedatetimefrom[enabled]'} == 1 ){

            $start_year = $filterdata->{'purchasedatetimefrom[year]'};
            $start_month = $filterdata->{'purchasedatetimefrom[month]'};
            $start_day = $filterdata->{'purchasedatetimefrom[day]'};
            $start_hour = $filterdata->{'purchasedatetimefrom[hour]'};
            $start_minute = $filterdata->{'purchasedatetimefrom[minute]'};
            $filter_starttime_con =mktime($start_hour,$start_minute,0, $start_month, $start_day, $start_year);
            $fromdate1 = date('Y-m-d H:i:00',$filter_starttime_con);
            $fromdate2= userdate($filter_starttime_con, '%Y-%m-%d %H:%M:00');
            $formsql.= " AND UNIX_TIMESTAMP(ee.purchasedatetime)  >= $filter_starttime_con ";

        }
        if($filterdata->{'purchasedatetimeto[enabled]'} == 1 ){
            $start_year = $filterdata->{'purchasedatetimeto[year]'};
            $start_month = $filterdata->{'purchasedatetimeto[month]'};
            $start_day = $filterdata->{'purchasedatetimeto[day]'};
            $start_hour = $filterdata->{'purchasedatetimeto[hour]'};
            $start_minute = $filterdata->{'purchasedatetimeto[minute]'};
            $filter_endtime_con=mktime($start_hour,$start_minute,59, $start_month, $start_day, $start_year);
            $formsql.= " AND UNIX_TIMESTAMP(ee.purchasedatetime)  <= $filter_endtime_con ";
            

        }
        if (!empty($filterdata->type)){ 
            $types = explode(',',$filterdata->type);
             if(!empty($types)){
                $typesquery = array();
                foreach ($types as $type) {
                    if($type == 'hall_validation') {
                        $typesquery[] = "  ee.transactiontypes = 1 AND ee.validation = 1  "; 
                    }elseif($type == 'register') {
                        $typesquery[] = "  ee.transactiontypes = 1 AND ee.validation = 0 "; 
                    } elseif($type == 'cancel_validation') {
                        $typesquery[] = "  ee.transactiontypes = 2 AND ee.validation = 1  "; 
                    } elseif($type == 'cancel') {
                        $typesquery[] = "  ee.transactiontypes = 2 AND ee.validation = 0 "; 
                    } elseif($type == 'reschedule_validation') {
                        $typesquery[] = "  ee.transactiontypes = 3 AND ee.validation = 1  "; 
                    } elseif($type == 'reschedule') {
                        $typesquery[] = "  ee.transactiontypes = 3 AND ee.validation = 0 "; 
                    }
                }
                $typesqueryparams =implode('OR',$typesquery);
                $formsql .= ' AND ('.$typesqueryparams.') ';
            }
        } 
        if (!empty($filterdata->centercode)){ 
            $centercodes = explode(',',$filterdata->centercode);
             if(!empty($centercodes)){
                $centercodesquery = array();
                foreach ($centercodes as $centercode) {
                    $centercodesquery[] = " CONCAT(',',ee.centercode,',') LIKE CONCAT('%,','$centercode',',%') "; 
                }
                $centercodesqueryparams =implode('OR',$centercodesquery);
                $formsql .= ' AND ('.$centercodesqueryparams.') ';
            }
        } 
        if (!empty($filterdata->examcode)){ 
            $examcodes = explode(',',$filterdata->examcode);
             if(!empty($examcodes)){
                $examcodesquery = array();
                foreach ($examcodes as $examcode) {
                    $examcodesquery[] = " CONCAT(',',ee.examcode,',') LIKE CONCAT('%,','$examcode',',%') "; 
                }
                $examcodesqueryparams =implode('OR',$examcodesquery);
                $formsql .= ' AND ('.$examcodesqueryparams.') ';
            }
        } 
        if (!empty($filterdata->profilecode)){ 
            $profilecodes = explode(',',$filterdata->profilecode);
             if(!empty($profilecodes)){
                $profilecodesquery = array();
                foreach ($profilecodes as $profilecode) {
                    $profilecodesquery[] = " CONCAT(',',ee.profilecode,',') LIKE CONCAT('%,','$profilecode',',%') "; 
                }
                $profilecodesqueryparams =implode('OR',$profilecodesquery);
                $formsql .= ' AND ('.$profilecodesqueryparams.') ';
            }
        } 

        if (!empty($filterdata->examlanguage)){ 
            $examlanguages = explode(',',$filterdata->examlanguage);
             if(!empty($examlanguages)){
                $examlanguagesquery = array();
                foreach ($examlanguages as $examlanguage) {
                    $examlanguagesquery[] = " CONCAT(',',ee.examlanguage,',') LIKE CONCAT('%,','$examlanguage',',%') "; 
                }
                $examlanguagesqueryparams =implode('OR',$examlanguagesquery);
                $formsql .= ' AND ('.$examlanguagesqueryparams.') ';
            }
        }
        $formsql .= " ORDER BY UNIX_TIMESTAMP(ee.purchasedatetime) DESC ";
        $params = array_merge($searchparams);
        $totalrecords = $DB->get_records_sql($selectsql. $formsql, $params);
        $fastexamenrol = $DB->get_records_sql($selectsql. $formsql, $params,$stable->start,$stable->length);
        $list = array();
        $count = 0;
        foreach ($fastexamenrol as $examenrol) {
            if($examenrol->transactiontypes == 1){
                if($examenrol->validation){
                    $servicetype = get_string('hall_validation','local_exams');
                }else{
                    $servicetype = get_string('register','local_exams');
                }
            }else if($examenrol->transactiontypes == 2){
                if($examenrol->validation){
                    $servicetype = get_string('cancel_validation','local_exams');
                }else{
                    $servicetype = get_string('cancel','local_exams');
                }
            }else if($examenrol->transactiontypes == 3){

                if($examenrol->validation){
                    $servicetype = get_string('reschedule_validation','local_exams');
                }else{
                    $servicetype = get_string('reschedule','local_exams');
                }

            }else if($examenrol->transactiontypes == 4){

                if($examenrol->validation){
                    $servicetype = get_string('replacement_validation','local_exams');
                }else{
                    $servicetype = get_string('replacement','local_exams');
                }

            } else {
             
               $servicetype = '';

            }
            $list[$count]["id"] = $examenrol->id;
            $list[$count]["type"] =$servicetype;
            $list[$count]["username"] = $examenrol->username;
            $list[$count]["centercode"] = $examenrol->centercode;
            $list[$count]["examcode"] = $examenrol->code;
            $list[$count]["profilecode"] = $examenrol->examprofilecode;
            $list[$count]["examlanguage"] = $examenrol->examlanguage;
            $list[$count]["examdatetime"] = $examenrol->examdatetime;
            $list[$count]["purchasedatetime"] = $examenrol->purchasedatetime;
            $list[$count]["createdbyusername"] = $examenrol->createdbyusername;
            $list[$count]["billnumber"] = $examenrol->billnumber;
            $list[$count]["paymentrefid"] = $examenrol->paymentrefid;
            $list[$count]["payementtypes"] = $examenrol->payementtypes;
            $list[$count]["errormessage"] = $examenrol->errormessage;
            $list[$count]["statusdisplay"] = ($examenrol->status <= 0) ? get_string('failed','local_exams') :  get_string('success','local_exams');
            $list[$count]["status"]=($examenrol->status <= 0) ?  false : true;
            $count++;
        }
        $examenrolContext = array(
            "hasexamenrol" => $list,
            "totalrecords" => count($totalrecords),
            "length" => count($list),
        );
        return $examenrolContext;
    }

    public function add_update_fastexamenrol($data)
    {
        global $DB, $USER;
        $systemcontext = context_system::instance();
        if ($data->id) {
            $data->username = $data->username;
            $data->centercode = $data->centercode;
            $data->examcode =$data->examcode;
            $data->profilecode =$data->profilecode;
            $data->examlanguage = $data->examlanguage;
            $data->createdbyuserid = $USER->id;
            $data->examdatetime = $data->examdatetime;
            $data->purchasedatetime = $data->purchasedatetime;
            $data->createdbyusername = $data->createdbyusername;
            $data->billnumber = $data->billnumber;
            $data->paymentrefid = $data->paymentrefid;
            $data->timemodified = time();
            $record->id = $DB->update_record('local_fast_examenrol', $data);
            if ($record->id) 
            {
                return $record;
            } 
            else 
            {
                throw new moodle_exception('Error in Updating');
                return false;
            }
        }
    }

     public function set_examenrol($id)
    {
        global $DB;
        $data = $DB->get_record('local_fast_examenrol', ['id' => $id]);
        $sql = "SELECT ee.*,le.code as code,ep.profilecode as examprofilecode
                            FROM {local_fast_examenrol} ee 
                            JOIN {local_exams} le ON ee.examcode = le.code
                            JOIN {local_exam_profiles} as ep ON ep.profilecode=ee.profilecode
                           WHERE ee.id =$id";
        $data = $DB->get_record_sql($sql);
        $data->profilecode = $data->examprofilecode;
        $data->examcode = $data->code;

        return $data;
    }

    public function fast_exam_api($examinfo) {
        global $USER, $DB;
        // Checking Hall Availability settings are Enabled or not
        $accessstatus = (new \local_exams\local\exams)->access_fast_service('examreservation');
        if ($accessstatus) {
            $username = $DB->get_field('user', 'username', ['id'=>$examinfo->userid]);
            $createdbyusername = ($examinfo->createdbyuserid) ? $DB->get_field('user', 'username', ['id' => $examinfo->createdbyuserid]) : $USER->username;
            $row = [];
            $row['username'] = $username;
            $row['centercode'] = $examinfo->hallcode;
            $row['examcode'] = $examinfo->code;
            $row['profilecode'] = $examinfo->profilecode;
            if($examinfo->language){
                $row['examlanguage'] = 'English';
            }else{
                $row['examlanguage'] = 'Arabic';
            }
            $row['examdatetime'] = userdate($examinfo->examdate,'%Y-%m-%d') .' '. userdate($examinfo->starttime,'%H:%M:%S');
            $row['purchasedatetime'] = userdate(time(),'%Y-%m-%d %H:%M:%S');
            $row['createdbyusername'] = $createdbyusername;
            $row['billnumber'] = '';
            $row['paymentrefid'] = '';
            $row['payementtypes'] = 1;
            $row['transactiontypes'] = 1;
            $row['validation'] = 0;
            $row['userorganization'] = $examinfo->userorganization;
            $fastapi = new fastservice($row);
            $response = $fastapi->create_exam_reservations();            
        } else {
            $response = new stdClass();
            $response->success = true; 
        }

        return $response;
    }

    public function reviewmodeexams()
    {
        global $PAGE;
        $renderer = $PAGE->get_renderer('local_exams');
        $filterparams  = $renderer->get_catalog_reviewexams(true);
        $filterparams['submitid'] = 'form#filteringform';
        $filterparams['globalinput'] = $renderer->global_filter($filterparams);
        $filterparams['reviewmodeexams'] = $renderer->get_catalog_reviewexams();
        $filterparams['q'] = $searchquery;
        $filterparams['widthclass'] = 'col-md-12';
        $filterparams['inputclasses'] = 'reviewmodeexamssearchinput';
        $filterparams['placeholder'] = get_string('search_reviewmode_exams','local_exams');
        echo $renderer->listofreviewmodeexams($filterparams);        
    }

    public function get_listof_reviewmode_exams($stable, $filterdata) {

        global $DB, $PAGE, $OUTPUT, $USER;
        $systemcontext = context_system::instance();

        $currentlang = current_language();
        
        $selectsql = "SELECT le.*,lep.profilecode 
            FROM {local_exams} le 
            LEFT JOIN {local_exam_profiles} lep ON lep.examid = le.id
            WHERE le.status = 0 "; 

        $countsql = "SELECT COUNT(le.id) 
            FROM {local_exams} le
            LEFT JOIN {local_exam_profiles} lep ON lep.examid = le.id
            WHERE le.status = 0  ";
        if(isset($filterdata->search_query) && trim($filterdata->search_query) != ''){

            $formsql .= " AND (le.exam LIKE :examnamesearch OR 
                              le.examnamearabic LIKE :examnamearabicsearch  OR 
                              le.code LIKE :codesearch OR lep.profilecode LIKE :profilecodesearch ) ";

            $searchparams = array('examnamesearch' => '%'.trim($filterdata->search_query).'%',
                'examnamearabicsearch' => '%'.trim($filterdata->search_query).'%',
                'codesearch' => '%'.trim($filterdata->search_query).'%',
                'profilecodesearch' => '%'.trim($filterdata->search_query).'%'
            );

        }else{
            $searchparams = array();
        }
        $params = array_merge($searchparams);
        $totalexams = $DB->count_records_sql($countsql.$where.$formsql, $params);
        $formsql .=" ORDER BY le.id DESC";
        $exams = $DB->get_records_sql($selectsql.$where.$formsql, $params, $stable->start,$stable->length);
        $listofreviewmodeexams = array();
        $count = 0;
        $slno = 1;
        foreach($exams as $exam) {

            $listofreviewmodeexams[$count]['slno'] = $slno++;
            $listofreviewmodeexams[$count]['id'] = $exam->id;
            $listofreviewmodeexams[$count]['exam'] = (current_language() == 'ar') ? $exam->examnamearabic :  $exam->exam;
            $listofreviewmodeexams[$count]['email'] = $user->email;
            $listofreviewmodeexams[$count]['publishexams'] = true;
            $count++;
        }
        $usersContext = array(
            "hascourses" => $listofreviewmodeexams,
            "nocourses" => $nocourse,
            "totalexams" => $totalexams,
            "length" => COUNT($listofreviewmodeexams),
        );
        return $usersContext;


   }

   public function  trainee_exam_profile_view($id,$mlang = NULL) {
        global $DB, $PAGE, $OUTPUT, $USER, $CFG,$SESSION;
        $systemcontext = context_system::instance();
        $renderer = $PAGE->get_renderer('local_exams');
        $lang = current_language();
        $SESSION->lang = ($mlang) ? $mlang : current_language() ;
        $exam = $DB->get_record('local_exams', ['id' => $id]);
        if($exam) {
            $profile = $DB->get_record_sql('SELECT lem.* FROM {local_exam_profiles} lem 
                                                     JOIN {exam_enrollments} loe ON  loe.profileid =lem.id 
                                                     WHERE  loe.examid = '.$id.' AND loe.courseid ='.$exam->courseid.'  AND loe.userid = '.$USER->id.'');
            if($profile) {  
                $profile->examid = $id;
                $profile->examname = ($SESSION->lang == 'ar') ? $exam->examnamearabic : $exam->exam;
                $profile->code = $exam->code;
                $profile->profileid = $profile->id;
                $profile->profilecode = $profile->code;
                $profile->description = format_text($exam->programdescription, FORMAT_HTML);
                $profile->instructions = format_text($profile->instructions, FORMAT_HTML);
                $profile->nondisclosure = format_text($profile->nondisclosure, FORMAT_HTML);
               
                list($erequirementsql,$erequirementparams) = $DB->get_in_or_equal(explode(',',$exam->requirements));
                $examrequirementquerysql = "SELECT * FROM {local_exams} WHERE id $erequirementsql";
                $examrequirements= $DB->get_records_sql($examrequirementquerysql,$erequirementparams);
                foreach ($examrequirements AS $examrequirement) {

                    $examname = ($SESSION->lang == 'ar') ? $examrequirement->examnamearabic: $examrequirement->exam;
                   
                    $examrequirement->name = $examname;
                    $examrequirement->description = null;
                    $examrequirement->value =  $examrequirement->id;
               
                } 
                if(!empty($examrequirements)){
                      $profile->prerequisitesOfExams = array_values($examrequirements);
                } else {
                      $profile->prerequisitesOfExams  = array();
                }

                list($prequirementsql,$prequirementparams) = $DB->get_in_or_equal(explode(',',$exam->programs));
                $programrequirementquerysql = "SELECT * FROM {local_trainingprogram} WHERE id $prequirementsql";
                $programrequirements= $DB->get_records_sql($programrequirementquerysql,$prequirementparams);
                foreach ($programrequirements AS $programrequirement) {

                    $name = ($SESSION->lang == 'ar') ? $programrequirement->namearabic: $programrequirement->exam;
                   
                    $programrequirement->name = $name;
                    $programrequirement->description = null;
                    $programrequirement->value =  $programrequirement->id;
                } 
                if(!empty($programrequirements)){
                     $profile->prerequisitesOfCourses = array_values($programrequirements);
                } else {
                     $profile->prerequisitesOfCourses = array();
                }

                $profile->id = $profile->id;
                $profile->examid = $profile->examid;
                $profile->registrationstartdate = !empty($profile->registrationstartdate) ? userdate($profile->registrationstartdate, get_string('strftimedatemonthabbr', 'core_langconfig')) : '--';
                $profile->registrationenddate = !empty($profile->registrationenddate) ? userdate($record->registrationenddate, get_string('strftimedatemonthabbr', 'core_langconfig')) : '--';
                $profile->passinggrade = $profile->passinggrade;
                $profile->seatingcapacity = $profile->seatingcapacity;
                $profile->duration = ($profile->duration/60).' '.get_string('min','local_exams');
                $profile->language = $this->profilelanguage($profile->language);


                $attemptssql = "SELECT qa.* 
                          FROM {quiz_attempts} qa 
                         WHERE qa.quiz = $profile->quizid AND qa.userid =". $USER->id;
                $attempts = $DB->get_records_sql($attemptssql);
                foreach ($attempts as $attempt) {

                    $attempt->attempt = $attempt->attempt;
                    $attempt->grade = ROUND($attempt->sumgrades, 2);
                    if ($record->attempt == 1) {
                        $attemptid = 0;
                    } else {
                        $attemptid = $DB->get_field('local_exam_attempts', 'id', ['examid' => $profile->examid, 'attemptid' => $attempt->attempt]);                
                    }
                    $hallname = $DB->get_field_sql('SELECT h.name 
                                                  FROM {local_exam_userhallschedules} as leu 
                                                  JOIN {hallschedule} as hs ON leu.hallscheduleid=hs.id
                                                  JOIN {hall} as h ON hs.hallid=h.id
                                                  WHERE leu.examid=:examid AND leu.userid=:userid AND leu.attemptid=:attemptid', 
                                                  ['examid' => $profile->examid, 'userid' => $USER->id, 'attemptid' => $attemptid]);
                    $attempt->hallname = format_string($hallname);
                    $attempt->timestart = $attempt->timestart;
                    $attempt->timefinish = $attempt->timefinish;
                   
                }

                $profile->attemptscompleted = (!empty($attempts)) ? array_values($attempts): [];

                $profile->showquestions = $profile->showquestions;
                $profile->questions = $profile->questions;
                $profile->showexamduration = $profile->showexamduration;

                $coursemodule = get_coursemodule_from_instance('quiz', $profile->quizid, $exam->courseid);
                
                $profile->cmid = $coursemodule->id;
                $examdate = $DB->get_field('exam_enrollments', 'examdate',  ['examid' => $exam->id, 'profileid' => $profile->id, 'userid' => $USER->id]);
                $examtime = date('Y-m-d', $examdate);

                $lastattempt = $this->lastattempt($profile->examid);
                $attempsnumber = $this->quiz_attemptscount($profile->examid);
                $attemptpurchase = $this->examattemptpurchases($profile->examid);

                $profile->attemptid = $attempsnumber;
                if (($attemptpurchase == $attempsnumber) || $attempsnumber == 0) {
                    $profile->launch = true;
                } else {
                    $profile->launch = false;
                }
                $userexamdate = $this->user_scheduledata($profile, $attempsnumber);
                $profile->hall = $userexamdate->hallname;
                if (!empty($lastattempt)) {
                    $userattemptid = $attempsnumber;
                    $sql = "SELECT daysbeforeattempt
                              FROM {local_exam_attempts}
                             WHERE id = (SELECT min(id) FROM {local_exam_attempts} WHERE examid = $exam->id AND  attemptid > $attempsnumber)";
                    $days = $DB->get_field_sql($sql);
                    $date = strtotime("+". $days ."day", $lastattempt);
                    $profile->nextattempt = ($date) ?  $date : 0 ;
                    $nextattempt = userdate($date, '%d-%m-%Y');

                    if (($attemptpurchase == $attempsnumber) && !empty($userexamdate->examdate)) {
                        $examday = true;
                        $starttime = $DB->get_field('hallschedule', 'starttime', ['id' => $userexamdate->hallscheduleid]);
                        $nextattempt = userdate(strtotime($userexamdate->examdate), '%d-%m-%Y').' '.userdate($starttime, get_string('strftimetime24', 'langconfig'));
                    }
                    if ( ((userdate(time(), '%d-%m-%Y') >= $nextattempt) && empty($examday)) || ((userdate(time(), '%d-%m-%Y %H:%M') >= $nextattempt) && !empty($examday) ) ) {
                       $profile->todayexam = true;
                    } else {
                        $profile->todayexam = false;
                    }
                } else {
                    if (userdate(time(), '%Y-%m-%d') >= $examtime) {
                       $profile->todayexam = true;
                    } else {
                        $profile->todayexam = false;
                    }
                }

                if ($examdate == 0) {
                    $profile->examdate = '--';
                } else {

                    $schedulestarttime = $DB->get_field('hallschedule', 'starttime', ['id' => $userexamdate->hallscheduleid]);
                    $profile->examdate = (strtotime($userexamdate->examdate) > 0 ) ? (strtotime($userexamdate->examdate) + $schedulestarttime) : 0;
                }
                if ($attempsnumber == $exam->noofattempts) {
                    $profile->launchbtnstatus = true;    
                } else {
                    if ($exam->noofattempts > 0) {
                        $attemptnum = ++$attempsnumber;
                        if ($attemptnum <= $exam->noofattempts) {
                            $attemptid = $DB->get_field('local_exam_attempts', 'id', ['examid' => $exam->id, 'attemptid' => $attemptnum]);
                        }
                    }
                    $profile->launchbtnstatus = false;      
                }
                return $profile;
            }
        }
    }

    public function get_tobereplacedusers($query = null, $replacinguserid = null, $rootid = null, $fieldid = null){
        global $DB,$USER;

        $systemcontext = context_system::instance();
        $courseid=$DB->get_field('local_exams','courseid', array('id' => $rootid));
        $traineeroleid= $DB->get_field('role', 'id', array('shortname' => 'trainee'));
        $fields = array(
            'lc.firstname',
            'lc.lastname',
            'lc.firstnamearabic',
            'lc.lastnamearabic',
            'lc.middlenamearabic',
            'lc.thirdnamearabic',
            'lc.middlenameen',
            'lc.thirdnameen',
            'lc.id_number',
            'lc.email');       
        $likesql = array();
        $i = 0;
        foreach ($fields as $field) {
            $i++;
            $searchquery = trim($query);
            $likesql[] = $DB->sql_like($field, ":queryparam$i", false);
            $sqlparams["queryparam$i"] = "%$searchquery%";
        }
        $sqlfields = implode(" OR ", $likesql);
        $concatsql = " AND ($sqlfields) ";
        $currentlang= current_language();
        $displaying_name = (new trainingprogram)->user_fullname_case();
        $enrolleduser =(int) $DB->get_field('exam_enrollments','usercreated',['examid'=>$rootid,
                                                          'courseid'=>$courseid,
                                                          'profileid'=>$fieldid,
                                                           'userid'=>$replacinguserid
                                                        ]);
        $enrolleduserroleinfo = $DB->get_record_sql('SELECT rol.* FROM {role} rol 
        JOIN {role_assignments} rola ON rola.roleid = rol.id
        WHERE rola.userid =:userid and contextid =:contextid',['userid'=>$enrolleduser,'contextid'=>$systemcontext->id]);
       if(($enrolleduserroleinfo->shortname == 'organizationofficial') || (!is_siteadmin() && has_capability('local/organization:manage_organizationofficial',$systemcontext))) {
          $user_id = ($enrolleduser) ? $enrolleduser: $USER->id;
          $organization = $DB->get_field('local_users','organization',array('userid'=>$user_id));
         $where = " WHERE  lc.organization = $organization ";
        } else {
        $where = " WHERE 1=1  ";
        }
        $sql = "SELECT u.id,$displaying_name
                FROM {user} u 
                JOIN {local_users} lc ON lc.userid = u.id
                JOIN {role_assignments} rla ON rla.userid=u.id  
                JOIN {course} as c ON c.id=$courseid
                $where  AND  u.id > 2 AND u.deleted = 0 AND  rla.contextid = $systemcontext->id AND rla.roleid =$traineeroleid AND u.id NOT IN 
                (SELECT ee.userid FROM {exam_enrollments} AS ee                         
                WHERE  ee.examid = $rootid  AND ee.courseid = $courseid) AND u.id $concatsql ";

        $order = " ORDER BY u.id DESC limit 50";
        $data = $DB->get_records_sql($sql.$order, $sqlparams);
        $return = array_values(json_decode(json_encode(($data)), true));
        return $return;
    }
    public function useraccess($examid, $profileid=null) {
        global $DB, $USER;
        $exam = $DB->get_record('local_exams', ['id' => $examid], 'exam, examnamearabic, sellingprice');
        if ($profileid) {
            $sql = "SELECT id, examdate
                      FROM {local_exam_userhallschedules} 
                     WHERE examid = $examid AND profileid = $profileid AND userid=".$USER->id." 
                     ORDER BY id DESC ";
            $enrolled = $DB->get_record_sql($sql);

            $profile=$DB->get_record('local_exam_profiles', ['id'=> $profileid]);
            // $userschedule = $this->user_scheduledata($profile);

            if (empty($enrolled->examdate) || empty($enrolled)) {
                return false;
            } else {
                return true;
            }
        } else {
            return false;
        }
    }
    public function dataforexamcancellation($data) {
        global $DB,$USER;
        $systemcontext = context_system::instance();
        $returndata =new stdClass();
        $courseid = $DB->get_field('local_exams','courseid',['id'=>$data->examid]);
        $productid =(int) $DB->get_field_sql('SELECT tlp.id FROM {tool_products} tlp 
        JOIN {local_exam_profiles} lep ON lep.profilecode = tlp.code 
        WHERE tlp.category =:category AND tlp.referenceid =:referenceid',['category'=>2,'referenceid'=>$data->profileid]);
        $enrolleduser =(int) $DB->get_field('exam_enrollments','usercreated',['examid'=>$data->examid,
                                                          'courseid'=>$courseid,
                                                          'profileid'=>$data->profileid,
                                                           'userid'=>$data->userid
                                                        ]);
        $user_id = (is_siteadmin()) ? $enrolleduser : $USER->id;
        $policies=new \local_exams\local\policies('exam', $data->examdate, 'cancel');
        $invoice  = (new \tool_product\telr)->get_pending_invoice($productid,$user_id);
        $roleinfo = $DB->get_record_sql('SELECT rol.* FROM {role} rol 
        JOIN {role_assignments} rola ON rola.roleid = rol.id
        WHERE rola.userid =:userid and contextid =:contextid',['userid'=>$enrolleduser,'contextid'=>$systemcontext->id]);
        $returndata->productid = $productid;
        $returndata->orgofficialenrolled =($roleinfo->shortname =='organizationofficial') ? 1 : 0;
        $returndata->trainee = !empty($roleinfo->shortname=='trainee') ? 1 : 0;
        $returndata->enrolledbyadmin = empty($roleinfo->shortname) ? 1 : 0;

        if ($roleinfo->shortname =='organizationofficial') {
            $oneseatamount = ($invoice) ? (($invoice->amount)/$invoice->seats) : 0;
            $remainingamount =  $invoice->amount-$oneseatamount;
            $returndata->amount =($invoice) ? $invoice->amount : 0;
            $returndata->invoicenumber = ($invoice) ? $invoice->invoice_number : 0;
            $policies->refund_amount($data->userid,$productid,$invoice->invoice_number, $data->policyconfirm);
            $refundamount = ($invoice) ? (($policies->refundamount) ? $policies->refundamount :0): 0;
            $returndata->refundamount= $this->is_decimal($refundamount)? round($refundamount,2) : round($refundamount);
            $returndata->deductamount= $oneseatamount - $returndata->refundamount;
            $newinvoiceamount = $remainingamount + ($oneseatamount - $refundamount);
            $returndata->newinvoiceamount = ($newinvoiceamount)? ($this->is_decimal($newinvoiceamount)? round($newinvoiceamount,2) : round($newinvoiceamount)) : 0;
       
        } elseif($roleinfo->shortname =='trainee') {
            $price = $DB->get_field('tool_user_order_payments', 'amount', ['productid' => $productid, 'userid' => $data->userid]);
            $policies->get_refundamount($price, $data->policyconfirm); // 1 means policies will apply for Trainee
            $returndata->refundamount= $policies->refundamount;
            $returndata->amount =($price) ? $price : 0;            
            $returndata->deductamount= ($price - $policies->refundamount);

        } 
        // else {
        //     $policies->refund_amount($data->userid,$productid,$invoice->invoice_number);
        //     $refundamount = ($invoice) ? (($policies->refundamount) ? $policies->refundamount :0): 0;
        //     $returndata->refundamount= $refundamount;
        //     $returndata->newinvoiceamount = $remainingamount + ($oneseatamount - $returndata->refundamount);
        // }

        return $returndata;
    }
    public function exam_replacement_process($data) {
        global $DB,$USER;
        $context = context_system::instance();
        $traineeroleid = $DB->get_field('role', 'id', array('shortname' => 'trainee'));
        $courseid=$DB->get_field('local_trainingprogram','courseid', array('id' => $data->rootid));
        $get_existingenrollment_record= $DB->get_record('local_exam_userhallschedules',[
            'examid'=>$data->rootid,
            'profileid'=>$data->fieldid,
            'userid'=>$data->fromuserid,
        ]);
        $accessstatus = (new \local_exams\local\exams)->access_fast_service('replaceservice');
        if(!$accessstatus || $data->costtype == 0 || ($data->costtype == 1 && $data->enrollinguserid <=0  && is_siteadmin() && $data->policyconfirm == 0)) {
            if(!$accessstatus) {
                $response = $this->exam_unenrollmet($data->fieldid,$data->fromuserid,'replace');
                $response =  $this->exam_enrollmet($data->fieldid,$data->touserid,$sheduleid = 0,'replace',$data->enrollinguserid);
                if($get_existingenrollment_record->orgofficial > 0) {
                    $DB->execute("UPDATE {exam_enrollments} SET enrolltype =$get_existingenrollment_record->enrolltype,organization =$get_existingenrollment_record->organization,orgofficial = $get_existingenrollment_record->orgofficial WHERE examid=$data->rootid AND profileid = $data->fieldid AND courseid = $courseid AND userid = $data->touserid");

                    $DB->execute("UPDATE {local_exam_userhallschedules} SET enrolltype =$get_existingenrollment_record->enrolltype,productid =$get_existingenrollment_record->productid,organization =$get_existingenrollment_record->organization,orgofficial = $get_existingenrollment_record->orgofficial WHERE examid=$data->rootid AND profileid = $data->fieldid AND userid = $data->touserid");
                }
            } else {
                $response =  $this->replacement_fastapi($data);
                if($response == 'success') {
                    $this->exam_unenrollmet($data->fieldid,$data->fromuserid,'replace');
                    $this->exam_enrollmet($data->fieldid,$data->touserid,$sheduleid = 0,'replace',$data->enrollinguserid);
                    if($get_existingenrollment_record->orgofficial > 0) {
                        $DB->execute("UPDATE {exam_enrollments} SET enrolltype =$get_existingenrollment_record->enrolltype,organization =$get_existingenrollment_record->organization,orgofficial = $get_existingenrollment_record->orgofficial WHERE examid=$data->rootid AND profileid = $data->fieldid AND courseid = $courseid AND userid = $data->touserid");
    
                        $DB->execute("UPDATE {local_exam_userhallschedules} SET enrolltype =$get_existingenrollment_record->enrolltype,productid =$get_existingenrollment_record->productid,organization =$get_existingenrollment_record->organization,orgofficial = $get_existingenrollment_record->orgofficial WHERE examid=$data->rootid AND profileid = $data->fieldid AND userid = $data->touserid");
                    }
                }

            }
            
        } else {
            $response =  $this->replacement_fastapi($data);
            if($response == 'success') {
                $sendingdata =new stdClass();
                $sendingdata->productid = $data->productid;
                $sendingdata->examid = $data->rootid;
                $sendingdata->profileid = $data->fieldid;
                $sendingdata->userid =$data->enrollinguserid;
                $sendingdata->entitytype = 'exam';
                $sendingdata->type = 'replacement';
                $sendingdata->seats = 1;
                $sendingdata->total = $data->replacementfee;
                $sendingdata->payableamount = $data->replacementfee;
                $productdata =  base64_encode(serialize((array)$sendingdata));
                $returndata = (new product)->insert_update_sadad_invoice_record($productdata);
                if($returndata){
                    $this->exam_unenrollmet($data->fieldid,$data->fromuserid,'replace');
                    $this->exam_enrollmet($data->fieldid,$data->touserid,$sheduleid = 0,'replace',$data->enrollinguserid);
                    if($get_existingenrollment_record->orgofficial > 0) {
                        $DB->execute("UPDATE {exam_enrollments} SET enrolltype =$get_existingenrollment_record->enrolltype,organization =$get_existingenrollment_record->organization,orgofficial = $get_existingenrollment_record->orgofficial WHERE examid=$data->rootid AND profileid = $data->fieldid AND courseid = $courseid AND userid = $data->touserid");

                        $DB->execute("UPDATE {local_exam_userhallschedules} SET enrolltype =$get_existingenrollment_record->enrolltype,productid =$get_existingenrollment_record->productid,organization =$get_existingenrollment_record->organization,orgofficial = $get_existingenrollment_record->orgofficial WHERE examid=$data->rootid AND profileid = $data->fieldid AND userid = $data->touserid");
                    }
                    return $returndata;
                }
            }
        }

         return $response;
    }
    public function exam_reschedule_process($data) {
        global $DB,$USER;
        $context = context_system::instance();
        $profileid = (int)$DB->get_field('tool_products','referenceid',['id'=>$data->productid]);
        $profilerecord =$DB->get_record('local_exam_profiles',['id'=>$profileid]);
        $courseid = (int)$DB->get_field('local_exams','courseid',['id'=>$profilerecord->examid]);
        $enrolledrecord =$DB->get_record('exam_enrollments',['examid'=>$profilerecord->examid,'profileid'=>$profileid,'courseid'=>$courseid,'userid'=>$data->userid]);                
        $enrolleduseroleinfo = $DB->get_record_sql('SELECT rol.* FROM {role} rol 
                                                    JOIN {role_assignments} rola ON rola.roleid = rol.id
                                                    WHERE rola.userid =:userid and contextid =:contextid',['userid'=>$enrolledrecord->usercreated,'contextid'=>$context->id]);
        $sendingdata =new stdClass();
        $sendingdata->userid =($enrolleduseroleinfo->shortname == 'organizationofficial')?$USER->id : $enrolledrecord->orgofficial;
        $sendingdata->productid = $data->productid;
        $sendingdata->seats = 1;
        $sendingdata->type = 'reschedule';
        $sendingdata->total = $data->deductamount;
        $sendingdata->payableamount = $data->deductamount;
        $productdata =  base64_encode(serialize((array)$sendingdata));
        if($data->deductamount <= 0){
            $returndata = true;
        }else{
            $returndata = (new product)->insert_update_sadad_invoice_record($productdata);
        }
        if($returndata){
            $this->submit_rescheduledata($data);
           return true;
        }
    }
    public function submit_rescheduledata($data) {
        global $DB,$USER;
        $context = context_system::instance();
        $profileid = (int)$DB->get_field('tool_products','referenceid',['id'=>$data->productid]);
        $profilerecord =$DB->get_record('local_exam_profiles',['id'=>$profileid]);
        $courseid = (int)$DB->get_field('local_exams','courseid',['id'=>$profilerecord->examid]);
        $scheduleid =(int) $DB->get_field('local_exam_userhallschedules','id',['examid'=>$profilerecord->examid,'profileid'=>$profileid,'userid'=>$data->userid,'attemptid' => 0]);
        $enrolmentid =(int) $DB->get_field('exam_enrollments','id',['examid'=>$profilerecord->examid,'profileid'=>$profileid,'courseid'=>$courseid,'userid'=>$data->userid]);
        $DB->update_record('local_exam_userhallschedules', ['id' => $scheduleid, 'examdate' => 0, 'hallscheduleid' => 0]);
         if($enrolmentid) {
            $DB->update_record('exam_enrollments', ['id' => $enrolmentid, 'examdate' => 0]);
        }
        return true;
    }
    public function exam_cancel_user($sdata) {
        global $DB,$USER, $CFG;
        $context = context_system::instance();
        $referanceid = (int)$DB->get_field('tool_products','referenceid',['id'=>$sdata->productid]);
        $profile =$DB->get_record('local_exam_profiles',['id'=>$referanceid]);
        $data =new stdClass();
        $data->examid =$profile->examid;
        $data->profileid = $profile->id;
        $data->userid = $sdata->userid;
        $data->examprice = $sdata->examprice;
        $data->amount = $sdata->amount;
        $data->refundamount = $sdata->refundamount;
        $data->newinvoiceamount = $sdata->newinvoiceamount;
        $data->newamount = $sdata->newamount;
        $data->productid = $sdata->productid;
        $data->policyconfirm = $sdata->policyconfirm;
        $data->examdate = $sdata->examdate;
        $data->invoicenumber = $sdata->invoicenumber;
        $data->entitytype = $sdata->entitytype;
        $data->enrolltype = $sdata->enrolltype;
        $data->actiontype = $sdata->actiontype;
        $policies = new \local_exams\local\policies('exam', $sdata->examdate, 'cancel');
        $enrolledroleinfo =  $policies->enrolled_by($sdata->userid,$sdata->productid);
        $sdata->enrolleduserid = $enrolledroleinfo->enrolleduserid;
        $costtype =(int) $DB->get_field('local_exams','examprice',['id'=>$profile->examid]);
        $profileid = (int)$DB->get_field('tool_products','referenceid',['id'=>$sdata->productid]);
        $profilerecord =$DB->get_record('local_exam_profiles',['id'=>$profileid]);
        $courseid = (int)$DB->get_field('local_exams','courseid',['id'=>$profilerecord->examid]);
        $scheduleid = (int) $DB->get_field('exam_enrollments','hallscheduleid',['examid'=>$profilerecord->examid,'profileid'=>$profileid,'courseid'=>$courseid,'userid'=>$sdata->userid]);          
        if($costtype == 0 ||  ($enrolledroleinfo->shortname =='organizationofficial' && $sdata->newinvoiceamount <= 0) || ($sdata->refundamount <= 0 && $sdata->enrolltype !=2 )) {
            $response =  $this->cancel_fastapi($sdata->productid,$sdata->userid, $scheduleid);
            if($response =='success') {
                $enrolleduserid =(int) $DB->get_field('exam_enrollments','usercreated',['examid'=>$profilerecord->examid,'profileid'=>$profileid,'courseid'=>$courseid,'userid'=>$sdata->userid]);                
                if ($costtype > 0) {
                    $data = (array)$data;
                    $data['userid'] =(is_siteadmin() ||  $enrolledroleinfo->shortname =='organizationofficial' ||  $enrolledroleinfo->shortname =='examofficial') ? $enrolleduserid  :  $USER->id;
                    (new \tool_product\product)->update_org_order_seats_for_cancellation((array)$data);
                }
                $this->exam_unenrollmet($profile->id,$sdata->userid,'cancel');
            }
            (new \tool_product\telr)->void_invoice($sdata->invoicenumber);
            $this->exam_unenrollmet($profile->id,$sdata->userid,'cancel');                
            
        } else {
            
            $policies = new \local_exams\local\policies('exam', $sdata->examdate, 'cancel');
            $response = $this->cancel_fastapi($sdata->productid,$sdata->userid, $scheduleid);
            if($response =='success') {
                $policies->cancel_process($data);
            }
               
        }
        return $response;
    }   

    public function cancel_fastapi($productid,$userid, $scheduleid) {

        // Checking Hall Availability settings are Enabled or not
        $accessstatus = (new \local_exams\local\exams)->access_fast_service('cancelservice');
        if ($accessstatus) {
            $info = array();
            $info['product_id'] = $productid;
            $info['userid'] = $userid;
            $info['hallscheduelid'] = $scheduleid;
            $info['transactiontypes'] = 'cancel';
            $apidata = $this->prepare_fast_apidata($info);
            $apidata['validation'] = 1;
            $fastapi = new fastservice($apidata);
            $validatecancel = $fastapi->validate_cancel();
            if(empty($validatecancel) || COUNT($validatecancel->messages) <=0 || $validatecancel->success){
                $apidata['validation'] = 0;
                $fastapi = new fastservice($apidata);
                $cancelresponse =  $fastapi->call_cancel();
                if(is_array($cancelresponse->messages)) {
                    foreach($cancelresponse->messages as $error){
                        $errors[] = $error->message;
                    }
                    $errormessage = implode(',',$errors);
                } else {
                    $errormessage =$cancelresponse->messages; 
                }

                return (empty($cancelresponse) || COUNT($cancelresponse->messages) <=0  || $cancelresponse->success) ? 'success' : $errormessage;
            } else {

                if(is_array($validatecancel->messages)) {
                    foreach($validatecancel->messages as $error){
                        $errors[] = $error->message;
                    }
                    $errormessage = implode(',',$errors);
                } else {
                    $errormessage =$validatecancel->messages; 
                }
                return  $errormessage;
            }
        } else {
            $data = new stdClass();
            $data->success = true;

            return 'success'; //$data
        }
    }

    public function reschedule_fastapi($data) {
        // Checking Hall Availability settings are Enabled or not
        $accessstatus = (new \local_exams\local\exams)->access_fast_service('rescheduleservice');
        if ($accessstatus) {
            $info = array();
            $info['product_id'] = $data->product_id;
            $info['userid'] = $data->userid;
            $info['oldexamdatetime'] = userdate($data->oldexamdate,'%Y-%m-%d') .' '. userdate($data->oldexamtime,'%H:%M:%S');
            $info['oldcentercode'] = $data->oldcentercode;
            $info['hallscheduelid'] = $data->hallscheduelid;
            $info['transactiontypes'] = 'reschedule';
            $apidata = $this->prepare_fast_apidata($info);
            $apidata['validation'] = 1;
            $fastapi = new fastservice($apidata);
            $validatereschedule = $fastapi->validate_reschedule();
            if(empty($validatereschedule) || COUNT($validatereschedule->messages) <=0 || $validatereschedule->success){
                $apidata['validation'] = 0;
                $fastapi = new fastservice($apidata);
                $sheduleresponse = $fastapi->call_reschedule();
               
                return $sheduleresponse;
            }else {
            
                return  $validatereschedule;
            }
        } else {
            $data = new stdClass();
            $data->success = true;

            return $data;
        }
    }
     public function replacement_fastapi($data) {
        global $DB,$USER, $CFG;
        // Checking Hall Availability settings are Enabled or not
        $accessstatus = (new \local_exams\local\exams)->access_fast_service('replaceservice');
        if ($accessstatus) {
            $info = array();
            $examinfodata = $DB->get_record_sql('SELECT e.code,ep.profilecode,ep.language,ee.timecreated,ee.usercreated FROM {local_exams} as e 
                            JOIN {local_exam_profiles} as ep ON ep.examid = e.id    
                            JOIN {exam_enrollments} as ee ON ee.examid=e.id AND ee.profileid=ep.id
                            WHERE e.id =:examid AND ep.id =:profileid
                            ',['examid' => $data->rootid, 'profileid' => $data->fieldid,'userid' => $data->fromuserid]);
            $hallinfodata = $DB->get_record_sql('SELECT eu.hallscheduleid as reservationid,hs.startdate as examdate,hs.starttime,h.code as hallcode FROM  
                            {hall} as h  
                            JOIN {hallschedule} as hs ON hs.hallid=h.id  
                            JOIN {local_exam_userhallschedules} as eu ON eu.hallscheduleid=hs.id   
                            WHERE eu.examid =:examid AND eu.profileid =:profileid AND eu.userid =:userid
                            ',['examid' => $data->rootid, 'profileid' => $data->fieldid,'userid' => $data->fromuserid]);

            $info['username'] = $DB->get_field('user','username',['id'=>$data->touserid]);
            $info['oldusername'] = $DB->get_field('user','username',['id'=>$data->fromuserid]);
            $info['centercode'] = $hallinfodata->hallcode;
            $info['examcode'] = $examinfodata->code;
            $info['profilecode'] = $examinfodata->profilecode;
            $enrolleduserid =(int) $DB->get_field('exam_enrollments','usercreated',['examid'=>$data->rootid,'profileid'=>$data->fieldid,'userid'=>$data->touserid]);
            $createdbyusername =($enrolleduserid) ? $DB->get_field('user', 'username', ['id' => $enrolleduserid]) : $USER->username;
            $info['createdbyusername'] =$createdbyusername;
            $info['billnumber'] =  '';
            $info['paymentrefid'] =  '';
            $info['transactiontypes'] = 4;
            $info['payementtypes'] = 1;
            $info['hallscheduleid'] = $hallinfodata->reservationid;
            if($examinfodata->language){
                $info['examlanguage'] = 'Arabic';
            }else{
                $info['examlanguage'] = 'Arabic';
            }
            $info['examdatetime'] = userdate($hallinfodata->examdate,'%Y-%m-%d') .' '. userdate($hallinfodata->starttime,'%H:%M:%S');
            $info['purchasedatetime'] = userdate(time(),'%Y-%m-%d %H:%M:%S');
            $orglicensenumber = $DB->get_field_sql('SELECT org.licensekey FROM {local_organization} org 
            JOIN {local_users} lc ON lc.organization  =org.id
            WHERE lc.userid = :userid',['userid'=>$data->fromuserid]);
            $info['userorganization'] = ($orglicensenumber) ? $orglicensenumber : 0 ;
            $info['validation'] = 1;
            $fastapi = new fastservice($info);
            $validatereplacement = $fastapi->validate_replacment();
            if(empty($validatereplacement) || COUNT($validatereplacement->messages) <=0 || $validatereplacement->success){
                $info['validation'] = 0;
                $fastapi = new fastservice($info);
                $replacmentresponse = $fastapi->call_replacment();
            
                if(is_array($replacmentresponse->messages)) {
                    foreach($replacmentresponse->messages as $error){
                        $errors[] = $error->message;
                    }
                    $errormessage = implode(',',$errors);
                } else {
                    $errormessage =$replacmentresponse->messages; 
                }

                return (empty($replacmentresponse) || COUNT($replacmentresponse->messages) <=0 || $replacmentresponse->success) ? 'success' : $errormessage;
            }else {

                if(is_array($validatereplacement->messages)) {
                    foreach($validatereplacement->messages as $error){
                        $errors[] = $error->message;
                    }
                    $errormessage = implode(',',$errors);
                } else {
                    $errormessage =$validatereplacement->messages; 
                }
                return  $errormessage;
            }
        } else {
            $data = new stdClass();
            $data->success = true;
            
            return $data;
        }        
    }
    public function is_decimal($val){
 
        return is_numeric( $val ) && floor( $val ) != $val;

    }
    public function get_exam_users($entityid, $referenceid=false) {
        global $DB, $USER;
        $sql = "SELECT ec.userid 
                    FROM {exam_completions} ec
                    JOIN {exam_enrollments} ee ON ee.examid = ec.examid
                    WHERE ee.userid = ec.userid AND ec.completion_status = 2 AND ec.examid = $entityid "; // 2 means completed the exam achieved passgrade
        if(!is_siteadmin())
        {
            $sql .= " AND ee.usercreated = ". $USER->id;
        }
        if ($referenceid) {
            $sql .= " AND ee.profileid = ec.profileid AND ec.profileid =". $referenceid;
        }

        $userids = $DB->get_fieldset_sql($sql);

        return $userids;
    }
    public function get_program_users($entityid, $referenceid=false) {
        global $DB, $USER;
        $sql = "SELECT pc.userid 
                    FROM {program_completions} pc
                    JOIN {program_enrollments} pe ON pe.programid = pc.programid
                    WHERE pe.userid = pc.userid AND pc.completion_status = 1 AND pc.programid = $entityid  "; // 1 means completed the program
        if(!is_siteadmin())
        {
            $sql .= " AND pe.usercreated = ". $USER->id;
        }
        if ($referenceid) {
            $sql .= " AND pc.offeringid = pe.offeringid AND pc.offeringid IN ($referenceid)";
        }
       $userids = $DB->get_fieldset_sql($sql);
          
        return $userids;
    }
    public function get_event_users($entityid) {
        global $DB, $USER;
        $sql = "SELECT tci.userid
                  FROM {tool_certificate_issues} tci
                  JOIN {local_event_attendees} lea ON lea.eventid = tci.moduleid
                 WHERE lea.userid = tci.userid AND moduletype = 'events' AND moduleid = $entityid ";

        if(!is_siteadmin())
        {
            $sql .= " AND lea.usercreated = ". $USER->id;
        }
        $userids = $DB->get_fieldset_sql($sql);

        return $userids;
    }
    public function get_orgorderinfo($data)
    {
        global $DB, $USER;
        $autoapproval = self::autoapproval();
        $tax_free = 0;
        if ($data) {
            $category = 0;
            switch ($data->tablename) {
                case 'tp_offerings':
                    $category = self::TRAINING_PROGRAM;
                    $tax_free = $DB->get_field('local_trainingprogram','tax_free',['id' => $data->parentfieldid]);
                    break;
                case 'local_exam_profiles':
                    $category = self::EXAMS;
                    $tax_free = $DB->get_field('local_exams','tax_free',['id' => $data->parentfieldid]);
                    break;
                case 'local_events':
                    $category = self::EVENTS;
                    $tax_free = $DB->get_field('local_events',' taxfree',['id' => $data->parentfieldid]);
                    break;
            }
            $productparams = (new product)->get_product_attributes($data->fieldid, $category, 'addtocart', 0, 0, $data->selectedseats, true);
            $price = $DB->get_field('tool_products', 'price', ['id' => $productparams['product']]);
            $tax_slab = get_config('tool_product', 'tax_percentage');
            if($data->tablename == 'tp_offerings') {
                $offeringrecord = $DB->get_record('tp_offerings',['id'=>$data->fieldid]);
                if((int)$offeringrecord->type == 1 && (int)$offeringrecord->offeringpricing == 1) {
                    $userorganization =(int)$DB->get_field('local_users','organization',['userid'=>$USER->id]);
                    $existinginvoice_number = $DB->get_field_sql("SELECT invoice_number FROM {tool_product_sadad_invoice}  WHERE  productid =:productid AND organization =:organization  AND status =:status AND type IN ('programsbulkenrollment','purchase')",['productid'=>$productparams['product'],'organization'=>$userorganization,'status'=>1]);
                    if($existinginvoice_number) {
                        $traineeids = explode(',', base64_decode($data->tuserid));
                        $noofusers = COUNT($traineeids);
                        $data->enrolementtype = 'manual';
                        $data->organization = $userorganization;
                        $data->users = $traineeids;
                        $this->insert_data_in_orgofficial_related_tables($data,$existinginvoice_number,$productparams['product'],$noofusers,'alreadyexists');
                        $total_price  = 0;
                        $data->hasprivateandinvoice = 1;
                        $data->existinginvoice_number = $existinginvoice_number;
                    } else {
                        $total_price = $price;
                        $data->hasprivateandinvoice = 0;
                        $data->existinginvoice_number = 0;
                    }
                    
                } else {
                    $total_price = $price*$data->selectedseats;
                    $data->hasprivateandinvoice = 0;
                    $data->existinginvoice_number = 0;     
                }
            } else {
                $total_price = $price*$data->selectedseats;
                $data->hasprivateandinvoice = 0;
                $data->existinginvoice_number = 0;
            }
            $discountdata =(new product)->get_orgofficial_discountdata($data);
            $discount = $discountdata->discount > 0 ? round(($total_price * (($discountdata->discount/100))),2) : 0;
            $priceafterdiscount = $total_price - $discount;
            $taxes = $this->caluculate_taxes($priceafterdiscount, $tax_slab);
            $item_taxes = ($tax_free == 0) ? $taxes :0;
            $params['total'] = ROUND(($priceafterdiscount + $item_taxes),2);

            $formparams = ['tablename' => $data->tablename, 'fieldname' => $data->fieldname, 'fieldid' => $data->fieldid, 'parentfieldid' => $data->parentfieldid, 'selectedseats' => $data->selectedseats, 'sesskey' => sesskey(), 'quantity' => $data->selectedseats, 'product_id' => $productparams['product'], 'total' => $params['total'], 'autoapproval'=>$autoapproval, 'tuserid'=>$data->tuserid];
            $params['items'][0] = array_merge($formparams, $productparams);
            $params['formdata'] = $formparams;
            $params['total_price'] = $price;
            $params['total_discount'] = $discount > 0 ? $discount : 0;
            $params['discounttype'] = $discount > 0 ? $discountdata->type : '';
            $params['discounttableid'] = $discount > 0 ? $discountdata->id : 0;
            $params['taxes'] = $item_taxes;
            $params['tuserid'] = $data->tuserid;
           
            return [
                'returnparams' => base64_encode(serialize($params)),
                'autoapproval' => $autoapproval,
                'hasprivateandinvoice' => $data->hasprivateandinvoice,
                'existinginvoice_number' => $data->existinginvoice_number,
                'discountamount' => $discount > 0 ? $discount : 0,
                'discounttype' => $discount > 0 ? $discountdata->type : '',
                'discounttableid' => $discount > 0 ? $discountdata->id : 0
            ];
        }
    }
    public function caluculate_taxes( $amount, $tax ){
        return ($amount * ($tax/100));
    }
    public function autoapproval()
    {
        global $DB, $USER;
        $sql = "SELECT lc.autoapproval 
                  FROM {local_organization} lc
                  JOIN {local_users} lu ON lu.organization = lc.id 
                 WHERE lu.userid =". $USER->id;
        $autoapproval = $DB->get_field_sql($sql);

        return $autoapproval;
    }
    public function examusergrades()
    {
        global $DB;
        $examssql = "SELECT id, courseid
                       FROM  {local_exams} le 
                      WHERE old_id !=0 ";
        $exams = $DB->get_records_sql($examssql);
        foreach ($exams as $exam) {
            $sql = "SELECT lep.quizid,  
                      FROM {local_exam_profiles} as lep 
                      JOIN {local_exams} le ON le.id = lep.examid 
                      WHERE le.id = ".$exam->id;
            $quizzes = $DB->get_records_sql($sql);

            foreach ($quizzes as $quiz) {
                $gradesql = "SELECT gg.id, gg.finalgrade AS finalgrade
                          FROM {grade_grades} gg
                          JOIN {grade_items} gi ON gg.itemid = gi.id
                         WHERE gi.courseid = {$exam->courseid} AND gi.itemtype = 'mod' AND gi.itemmodule  = 'quiz' AND gi.iteminstance = {$quiz->quizid} ";
                $grades = $DB->get_records_sql($gradesql);

                foreach ($grades as $grade) {
                    $usergrade = ($grade->finalgrade/$grade->questions)*100;
                    if ($usergrade <= 100) {
                        $DB->update_record('grade_grades', ['id'=>$grade->id, 'finalgrade'=>$usergrade]);
                    }
                }
            }
        }
    }


    public function exams_enrol_notification($exam_record, $userid, $scheduleid) {
        global $DB;
      
      
        $context = context_system::instance();

        $hallschedule = $DB->get_record('hallschedule',array('id'=>$scheduleid));

        if($exam_record->ownedby == 'FA' || $exam_record->ownedby == 'Euromoney' || $exam_record->ownedby == 'euromoney' ) {
           
            $localuserrecord = $DB->get_record('local_users',['userid'=> $userid]);
            $fname = ($localuserrecord)? (( $localuserrecord->lang == 'ar') ? $localuserrecord->firstnamearabic.' '.$localuserrecord->middlenamearabic.' '.$localuserrecord->thirdnamearabic.' '.$localuserrecord->lastnamearabic  : $localuserrecord->firstname.' '.$localuserrecord->middlenameen.' '.$localuserrecord->thirdnameen.' '.$localuserrecord->lastname) :fullname($DB->get_record('user',array('id'=>$userid)));
            $exam_record->exam_userfullname = $localuserrecord->firstname.' '.$localuserrecord->middlenameen.' '.$localuserrecord->thirdnameen.' '.$localuserrecord->lastname;
            $exam_record->exam_arabicuserfullname =  $fname;  
            $exam_record->exam_arabicname = $exam_record->examnamearabic;
            $exam_record->exam_name = $exam_record->exam;  
            $exam_record->exam_date = userdate($hallschedule->startdate, '%d-%m-%Y');
            $starttime = userdate($hallschedule->starttime,'%H:%M %P');
            $endttime = userdate($hallschedule->endtime,'%H:%M %P');
           
            $exam_record->exam_time = $starttime ;
            $exam_record->exam_endtime = $endttime;
            $exam_record->exam_ownedby = $exam_record->ownedby;
            $trainee = $DB->get_record('user',array('id'=>$userid));  
            (new \local_exams\notification)->exams_notification('exams_enrolment', $touser=$trainee,$fromuser = get_admin(), $exam_record,$waitinglistid=0);
            $ownedbyvalue = $exam_record->ownedby;
            $ownedbydata = get_config('local_exams','ownedby');
            $ownedbydata = json_decode($ownedbydata);
            $getownedbydata = [];
            foreach($ownedbydata AS $key=>$value) {
            $getownedbydata[$key] = $value;
            }
            $finalownedbykey = str_replace(' ','_',$ownedbyvalue).'-email';
            $ownedbyemail = $getownedbydata[$finalownedbykey];
            $allmails =  explode(",", $ownedbyemail);
          
            $ownedbyuser_details = new stdClass;
            $ownedbyuser_details->name = $ownedbyvalue; 
            $inspectarray = array_filter($allmails);
            
            if(!empty($inspectarray) && count($inspectarray) != 0)  {

                $i = 0;
            foreach ($allmails AS $email) {
                if ($i == 0) {
                    $senduser = get_admin();                        
                    $senduser->firstname = $ownedbyvalue;
                    $senduser->lastname = $ownedbyvalue;
                    $senduser->email = $email;  
                    
                }  else{
                    $row = new stdClass();;
                    $row->email = $email;
                    $row->name =  $senduser->firstname ;
                    $senduser->ccusers[] = $row;
                 }  
               
                ++$i ;        

            }       
            $exam_record->exam_useridno =   $localuserrecord->id_number;
            $exam_record->exam_useremail =    $localuserrecord->email ;
            $exam_record->exam_userorg   =  ($localuserrecord->organization != 0) ? $DB->get_field('local_organization','fullname',array('id'=>$localuserrecord->organization)) : "" ;
            $hallid = $DB->get_field('hallschedule','hallid',array('id'=>$scheduleid));
            $exam_record->exam_center   = $DB->get_field('hall','name',array('id'=>$hallid));  
            $exam_record->exam_profilelang =   $DB->get_field('local_fast_examenrol','examlanguage',array('examcode'=>$exam_record->code));
            $exam_record->exam_userdob = ($localuserrecord->dateofbirth != null) ?  userdate($localuserrecord->dateofbirth, '%d-%m-%Y') : "";     

            (new \local_exams\notification)->exams_notification('exam_service_provider', $touser=$senduser ,$fromuser = get_admin(), $exam_record,$waitinglistid=0);
            $event =  \local_exams\event\exam_enrolled::create(array('context'=>$context, 'objectid' =>$exam_record->id));
            $event->trigger();         
         
        }
        
        } else {
             $localuserrecord = $DB->get_record('local_users',['userid'=> $userid]);
            $fname = ($localuserrecord)? (( $localuserrecord->lang == 'ar') ? $localuserrecord->firstnamearabic.' '.$localuserrecord->middlenamearabic.' '.$localuserrecord->thirdnamearabic.' '.$localuserrecord->lastnamearabic  : $localuserrecord->firstname.' '.$localuserrecord->middlenameen.' '.$localuserrecord->thirdnameen.' '.$localuserrecord->lastname) :fullname($DB->get_record('user',array('id'=>$userid)));
            $exam_record->exam_userfullname = $localuserrecord->firstname.' '.$localuserrecord->middlenameen.' '.$localuserrecord->thirdnameen.' '.$localuserrecord->lastname;
            $exam_record->exam_arabicuserfullname =  $fname;  
            $exam_record->exam_arabicname = $exam_record->examnamearabic;
            $exam_record->exam_name = $exam_record->exam;  
            $exam_record->exam_date = userdate($hallschedule->startdate, '%d-%m-%Y');  

            $starttime = userdate($hallschedule->starttime,'%H:%M %P');
            $endttime = userdate($hallschedule->endtime,'%H:%M %P');
           
            $exam_record->exam_time = $starttime ;
            $exam_record->exam_endtime = $endttime;
            $exam_record->exam_ownedby = $exam_record->ownedby;
            $trainee = $DB->get_record('user',array('id'=>$userid));  
            (new \local_exams\notification)->exams_notification('other_exam_enrollment', $touser=$trainee,$fromuser = get_admin(), $exam_record,$waitinglistid=0);
            $ownedbyvalue = $exam_record->ownedby;
            $ownedbydata = get_config('local_exams','ownedby');
            $ownedbydata = json_decode($ownedbydata);
            $getownedbydata = [];
            foreach($ownedbydata AS $key=>$value) {
                    $getownedbydata[$key] = $value;
            }
            $finalownedbykey = str_replace(' ','_',$ownedbyvalue).'-email';
            $ownedbyemail = $getownedbydata[$finalownedbykey];
            $allmails =  explode(",",$ownedbyemail);  
            $inspectarray = array_filter($allmails);       
            if(!empty($inspectarray) && count($inspectarray) != 0)  {
                $i = 0;


            foreach ($allmails AS $email) {  
                if ($i == 0) {
                    $senduser = get_admin();                        
                    $senduser->firstname = $ownedbyvalue;
                    $senduser->lastname = $ownedbyvalue;
                    $senduser->email = $email;  
                    
                }  else{
                    $row = new stdClass();;
                    $row->email = $email;
                    $row->name =  $senduser->firstname ;
                    $senduser->ccusers[] = $row;
                 }  
               
                ++$i ;                      
                  
                }        
                $exam_record->exam_useridno =   $localuserrecord->id_number;
                $exam_record->exam_useremail =    $localuserrecord->email ;
                $exam_record->exam_userorg   =  ($localuserrecord->organization != 0) ? $DB->get_field('local_organization','fullname',array('id'=>$localuserrecord->organization)) : "" ;
                $hallid = $DB->get_field('hallschedule','hallid',array('id'=>$scheduleid));
                $exam_record->exam_center   = $DB->get_field('hall','name',array('id'=>$hallid)); 
           
                $exam_record->exam_profilelang =   $DB->get_field('local_fast_examenrol','examlanguage',array('examcode'=>$exam_record->code));  
                $exam_record->exam_userdob = ($localuserrecord->dateofbirth != null) ?  userdate($localuserrecord->dateofbirth, '%d-%m-%Y') : "";           
    
                (new \local_exams\notification)->exams_notification('exam_service_provider', $touser=$senduser,$fromuser = get_admin(), $exam_record,$waitinglistid=0);
   
             
            }
          

            }
        }
    
    public function apischecking($userid, $scheduleid, $profileid, $examid, $type, $orderid, $productid=false,$organization=0,$discountprice = 0,$discounttype = null,$discounttableid = 0,$autoapproval = 0) {
        global $DB;
        $data=new stdClass;
        $params = [];
        $params['examid'] = $examid;
        $params['profileid'] = $profileid;
        $params['type'] = $type;
        $params['tuserid'] = $userid;
        $params['orderid'] = $orderid;
        $params['transactiontypes'] = 'register';
        $params['userid'] = $userid;
        $params['hallscheduelid'] = $scheduleid;
        $params['product_id'] = $productid;

        // Checking Hall Availability settings are Enabled or not
        $accessstatus = (new \local_exams\local\exams)->access_fast_service('hallavailability');
        if ($accessstatus) {
            $apidata = $this->prepare_fast_apidata($params, $data);
            $apidata['validation'] = 1;
            $fastapi = new fastservice($apidata);
            $fastresponse = $fastapi->hall_availability();
        } else {
            $fastresponse = new stdClass();
            $fastresponse->success = true; 
        }

        $examdatetime = $DB->get_field('hallschedule','startdate',['id'=>$scheduleid]);
        if(empty($fastresponse) || COUNT($fastresponse->messages) <=0 || $fastresponse->success) {

            $exam_record = $DB->get_record_sql("SELECT ex.*, ep.profilecode examcode 
                                                  FROM {local_exams} ex 
                                                  JOIN {local_exam_profiles} ep ON ex.id = ep.examid 
                                                 WHERE ex.id = '$examid' AND ep.id = '$profileid' ");
            if($exam_record->ownedby == 'FA' || $exam_record->ownedby == 'CISI') {


                // Checking Hall Availability settings are Enabled or not
                $accessstatus = (new \local_exams\local\exams)->access_fast_service('examreservation');
                if ($accessstatus) {
                        $examinfodata = $DB->get_record_sql('SELECT e.code,ep.profilecode,ep.language,ee.timecreated,ee.usercreated FROM {local_exams} as e 
                                        JOIN {local_exam_profiles} as ep ON ep.examid = e.id    
                                        JOIN {exam_enrollments} as ee ON ee.examid=e.id AND ee.profileid=ep.id
                                        WHERE e.id =:examid AND ep.id =:profileid
                                        ',['examid' => $examid, 'profileid' => $profileid]);

                    $hallinfodata = $DB->get_record_sql('SELECT hs.startdate as examdate,hs.starttime,h.code as hallcode FROM  {hall} as h  
                                        JOIN {hallschedule} as hs ON hs.hallid=h.id    
                                        WHERE hs.id =:scheduleid 
                                        ',['scheduleid' => $scheduleid]);


                    $examcode = $DB->get_field('local_exams','code',array('id'=>$examid));
                    $profiledetails = $DB->get_record('local_exam_profiles',array('id'=>$profileid));
                    $examinfo = new stdClass();
                    $examinfo->code = $examcode;
                    $examinfo->hallcode = $hallinfodata->hallcode;
                    $examinfo->profilecode =  $profiledetails->profilecode;
                    $examinfo->examdate = $hallinfodata->examdate;
                    $examinfo->starttime = $hallinfodata->starttime;
                    $examinfo->timecreated = ($examinfodata->timecreated) ? $examinfodata->timecreated : time();
                    $examinfo->language = $profiledetails->language;
                    $examinfo->userid = $userid;
                    $examinfo->createdbyuserid = $examinfodata->usercreated;
                    $examinfo->userorganization = $apidata['userorganization'];
                   
                    $fastresponse= $this->fast_exam_api($examinfo);
                } else {
                    $fastresponse = new stdClass();
                    $fastresponse->success = true;
                }

                if(empty($fastresponse) || COUNT($fastresponse->messages) <=0 || $fastresponse->success) {
                    if($examdatetime) {
                        try{                    
                            $fastresponse =  $this->exam_enrollmet($profileid, $userid, $scheduleid,$type, false, $orderid,$productid, null);
                            if($autoapproval > 0) {
                                (new product)->update_discount_status($productid,$discounttableid,$discounttype);
                            }
                            $id = $DB->get_field('local_exam_userhallschedules', 'id', ['examid' => $examid, 'profileid' => $profileid, 'userid' => $userid]);

                            $eventparams = array('context' => \context_system::instance(),
                            'objectid'=>$id,
                            'other'=>array('category' => 2,
                                            'entityid' => $profileid,    // profile id
                                            'examdate' => $examdatetime,
                                            'userid' => $userid,
                                            'hallscheduleid' => $scheduleid)
                            );
                            $event = \local_exams\event\trainee_schedules::create($eventparams);// ... code that may add some record snapshots
                            $event->trigger();
                          //  $notification = (new \local_exams\local\exams)->exams_enrol_notification($exam_record, $userid, $scheduleid);                            
                            
                        } catch(moodle_exception $e){
                            print_r($e);
                        }
                    }
                }
            } else {
                try{                    
                    $fastresponse = $this->exam_enrollmet($profileid, $userid, $scheduleid,$type, false, $orderid,$productid, null);
                    if($autoapproval > 0) {
                        (new product)->update_discount_status($productid,$discounttableid,$discounttype);
                    }
                } catch(moodle_exception $e){
                    print_r($e);
                }               
            }
        }

        return $fastresponse;
    }

    public function attemptvariation($examid, $profileid, $type=false, $tuserid=false) {
        global $DB, $USER;

        if (!empty($tuserid)) {
            if (!is_numeric($tuserid)) {
                $userid = base64_decode($tuserid);
            } else {
                $userid = $tuserid;
            }
        } else {
            $userid = $USER->id;
        }
        $exam = $DB->get_record('local_exams', ['id' => $examid]);
        $sql = "SELECT COUNT(id) 
                  FROM {local_exam_userhallschedules} leu 
                 WHERE leu.examid={$examid} AND examdate !=0 AND leu.userid IN (".$userid.")";
        if ($exam->appliedperiod != 1) {
            $sql .= " AND FROM_UNIXTIME(leu.examdate, '%Y')=YEAR(CURDATE()) ";
        }
        $attempsnumber = $DB->count_records_sql($sql);
        if ($type == 'reschedule') {
            if ($attempsnumber > 1) {
                $attemptnum = ++$attempsnumber;
                $category = self::EXAMATTEMPT;
                if ($attemptnum <= $exam->noofattempts) {
                    $attemptid = $DB->get_field('local_exam_attempts', 'id', ['examid' => $exam->id, 'attemptid' => $attemptnum]);
                    $category = self::EXAMATTEMPT;
                    $referenceid = $attemptid;
                }
            } else {
                $category = self::EXAMS;
                $referenceid = $profileid;
            }
        } else {
            if ($exam->appliedperiod == 1) {
                if ($attempsnumber == 0) {
                    $category = self::EXAMS;
                    $referenceid = $profileid;
                } else {
                    $attemptnum = ++$attempsnumber;
                    $category = self::EXAMATTEMPT;
                    if ($attemptnum <= $exam->noofattempts) {
                        $attemptid = $DB->get_field('local_exam_attempts', 'id', ['examid' => $exam->id, 'attemptid' => $attemptnum]);
                        $referenceid = $attemptid;
                    }
                }
            } else {
                $enrolledid = $DB->get_field('exam_enrollments', 'id', ['examid'=>$exam->id, 'userid'=>$userid]);
                if (empty($enrolledid)) {
                    $category = self::EXAMS;
                    $referenceid = $profileid;
                    $productid = $DB->get_field('tool_products', 'id', ['category' => $category, 'referenceid' => $profileid]);
                } else {
                    $attemptnum = ++$attempsnumber;
                    $category = self::EXAMATTEMPT;
                    if ($attemptnum <= $exam->noofattempts) {
                        $attemptid = $DB->get_field('local_exam_attempts', 'id', ['examid' => $exam->id, 'attemptid' => $attemptnum]);
                        $referenceid = $attemptid;
                    }
                }
            }
        }
        if($referenceid) {
            $sql = "SELECT tp.id 
            FROM {tool_products} tp
           WHERE tp.category = {$category} AND tp.referenceid = {$referenceid} ORDER BY tp.id DESC "; 
            $productid = $DB->get_field_sql($sql);
        }
    
        return $productid;
    }

    public function get_available_seats($profileid) {
        global $DB;
        $totalseats = $DB->get_field('local_exam_profiles','seatingcapacity',['id' => $profileid]);
        if($totalseats > 0) {
            $enrolled = $DB->get_field_sql(" SELECT count(userid) AS total
            FROM {exam_enrollments} AS ee
            WHERE ee.profileid = $profileid  AND ee.enrolstatus = 1 ");
            $seats = $totalseats - ($enrolled);
            $seatstatus = false;
            $enrolledseats = $enrolled;
        } else {
            $seatstatus = true;
            $seats = 0;
            $enrolledseats = 0;
        }
        
        return ['seatstatus' => $seatstatus , 'availableseats' => $seats,'enrolledseats' => $enrolledseats];
    }

    public function get_listof_preparations_programs($query=false) {
        global $DB;
        $lang = current_language();
        $title = ($lang == 'ar') ? 'namearabic' : 'name';
        $fields = array($title);
        $likesql = array();
        $i = 0;
        foreach ($fields as $field) {
            $i++;
            $likesql[] = $DB->sql_like($field, ":queryparam$i", false);
            $sqlparams["queryparam$i"] = "%$query%";
        }
        $sqlfields = implode(" OR ", $likesql);
        $concatsql = " AND ($sqlfields) ";
        $progranlist = $DB->get_records_sql(" SELECT id, $title AS fullname FROM {local_trainingprogram} WHERE published = 1 $concatsql ORDER BY id DESC LIMIT 50 ", $sqlparams );
        return  $progranlist;

    }
    public function programlist($programs= array(),$examid=0) {
        global $DB;
        $lang = current_language();
        $title = ($lang == 'ar') ? 'namearabic' : 'name';
        if(!empty($programs)){
            $params = array();
            list($programsql, $programparams) = $DB->get_in_or_equal($programs);
            $params = array_merge($params, $programparams);
            $list= $DB->get_records_sql_menu(" SELECT id,$title as fullname FROM {local_trainingprogram} WHERE id $programsql",$params);

        }elseif(!empty($examid)){
            $list = $DB->get_records_sql_menu(" SELECT ltp.id, $title AS fullname
            FROM {local_trainingprogram} ltp JOIN {local_exams} le ON concat(',', le.programs, ',') LIKE concat('%,',ltp.id,',%')
            WHERE le.id = :examid",['examid' => $examid]);
        }
        return  $list;
    }

    public function get_listof_exam_requirements($query=false) {
        global $DB;
        $lang = current_language();
        $title = ($lang == 'ar') ? 'examnamearabic' : 'exam';
        $fields = array($title);
        $likesql = array();
        $i = 0;
        foreach ($fields as $field) {
            $i++;
            $likesql[] = $DB->sql_like($field, ":queryparam$i", false);
            $sqlparams["queryparam$i"] = "%$query%";
        }
        $sqlfields = implode(" OR ", $likesql);
        $concatsql = " AND ($sqlfields) ";
        $examslist = $DB->get_records_sql(" SELECT id, $title AS fullname FROM {local_exams} WHERE 1=1  $concatsql ORDER BY id DESC LIMIT 50 ", $sqlparams );
        return  $examslist;

    }
    public function examslist($exams= array(),$examid=0) {
        global $DB;
        $lang = current_language();
        $title = ($lang == 'ar') ? 'examnamearabic' : 'exam';
        if(!empty($exams)){
            $params = array();
            list($examssql, $examsparams) = $DB->get_in_or_equal($exams);
            $params = array_merge($params, $examsparams);
            $list= $DB->get_records_sql_menu("SELECT id,$title as fullname FROM {local_exams} WHERE id $examssql",$params);
        }elseif(!empty($examid)){
            $requiremts = $DB->get_field('local_exams','requirements',['id' => $examid]);
            if($requiremts) {
                $list= $DB->get_records_sql_menu(" SELECT id, $title as fullname FROM {local_exams} AS e WHERE id IN ($requiremts)");
            } else {
                $list = [];
            }  
        }        
        return $list;    
    }
    public function enrollmentconfirmation($examid,$profileid,$organization,$orgofficial,$cusers,$scheduleid) {
        global $PAGE;
        $renderer = $PAGE->get_renderer('local_exams');
        $filterparams  = $renderer->get_enrollmentconfirmationlist(true);
        $filterparams['submitid'] = 'form#filteringform';
        $filterparams['widthclass'] = 'col-md-12';
        $filterparams['examid'] = $examid;
        $filterparams['profileid'] = $profileid;
        $filterparams['organization'] = $organization;
        $filterparams['orgofficial'] = $orgofficial;
        $filterparams['cusers'] = $cusers;
        $filterparams['scheduleid'] = $scheduleid;
        $filterparams['placeholder'] = get_string('serch_user','local_exams');
        $globalinput=$renderer->global_filter($filterparams);
        $enrollmentconfirmationlist = $renderer->get_enrollmentconfirmationlist();
        $filterparams['enrollmentconfirmationlist'] = $enrollmentconfirmationlist;
        $filterparams['globalinput'] = $globalinput;
        $renderer->listofenrollmentconfirmation($filterparams);

    } 

    public function get_listof_enrollmentconfirmations($stable, $filterdata,$dataoptions) {
        global $DB,$USER,$CFG;
        $systemcontext = context_system::instance();
        $currentlang = current_language();
        $examid = json_decode($dataoptions)->examid;
        $profileid = json_decode($dataoptions)->profileid;
        $organization = json_decode($dataoptions)->organization;
        $orgofficial = json_decode($dataoptions)->orgofficial;
        $scheduleid = json_decode($dataoptions)->scheduleid;
        $cusers =  (json_decode($dataoptions)->cusers) ? base64_decode(json_decode($dataoptions)->cusers) : 0;
        $displaying_name = (new trainingprogram)->user_fullname_case();
        $selectsql = "SELECT u.id,$displaying_name,u.idnumber  
        FROM {user} u 
        JOIN {local_users} lc ON lc.userid = u.id "; 
        $countsql  = " SELECT COUNT(u.id) 
        FROM {user} u JOIN {local_users} lc ON lc.userid = u.id ";
        $formsql = " WHERE 1=1 AND lc.approvedstatus = 2 AND u.deleted = 0 AND lc.deleted = 0 AND 
        lc.bulkenrolltype ='exam' AND (lc.bulkenrollstatus = 0 || lc.bulkenrollstatus = 2) AND u.id IN($cusers)";
        if(isset($filterdata->search_query) && trim($filterdata->search_query) != ''){
            $formsql .= " AND (u.idnumber LIKE :idnumber) ";
            $searchparams = array(
                'idnumber' => '%'.trim($filterdata->search_query).'%'
            );
        } else {
            $searchparams = array();
        }
        $params = array_merge($searchparams);
        $totalrequests = $DB->count_records_sql($countsql.$formsql,$params);
        $formsql .=" ORDER BY u.id DESC";
        $requests = $DB->get_records_sql($selectsql.$formsql, $params, $stable->start,$stable->length);
        $confirmrequestslist = array();
        $count = 0;
        foreach($requests as $request) {
            $exam = $DB->get_record('local_exams',['id'=>$examid]);
            $profile = $DB->get_record('local_exam_profiles',['id'=>$profileid]);
            $hasuserenrolled = $DB->record_exists('exam_enrollments',['examid'=>$examid,'profileid'=>$profileid,'userid'=>$request->id]);
            $sql = "SELECT COUNT(id) 
            FROM {local_exam_userhallschedules} leu 
            WHERE leu.examid={$examid} AND examdate !=0 AND leu.userid=".$request->id." AND enrolstatus = 1";
            $attempsnumber = $DB->count_records_sql($sql);
            $confirmrequestslist[$count]["userid"] = $request->id;
            $confirmrequestslist[$count]["identitynumber"] = $request->idnumber;
            $confirmrequestslist[$count]["fullname"] =$request->fullname;
            $confirmrequestslist[$count]["examname"] =($currentlang == 'ar') ? $exam->examnamearabic  : $exam->exam;
            $confirmrequestslist[$count]["examcode"] =$exam->code;
            $confirmrequestslist[$count]["profilecode"] =$profile->profilecode;
            $confirmrequestslist[$count]["profilelang"] = ($profile->language == 1) ? get_string('english','local_exams'): get_string('arabic','local_exams');
            $finalattemptnumber =  ($attempsnumber == 0)  ? 1 : ($attempsnumber+1);
            $confirmrequestslist[$count]["attemptnumber"] = $finalattemptnumber;
            $errormessage = $this->bulk_enroll_user_validations($examid,$profileid,$request->id,$scheduleid,$organization);
            if($finalattemptnumber == 1) {
                $attemptfee = $DB->get_field('tool_products','price',['referenceid'=>$profileid,'category'=>SELF::EXAMS]);
            } else {
                $attemptid =(int) $DB->get_field('local_exam_attempts','id',['examid'=>$examid,'attemptid'=>$finalattemptnumber]);
                if($attemptid > 0) {
                 $attemptfee = $DB->get_field('tool_products','price',['referenceid'=>$attemptid,'category'=>SELF::EXAMATTEMPT]);
                } else {
                   $attemptfee = 0;
                }
            }
            $schedulerecord = $DB->get_record('hallschedule',['id'=>$scheduleid]);
            $confirmrequestslist[$count]["attemptfee"] = ($attemptfee > 0)  ?  number_format($attemptfee,2) : $attemptfee;
            $confirmrequestslist[$count]["examdateandtime"] = userdate($schedulerecord->startdate,'%Y-%m-%d') .' '.userdate($schedulerecord->starttime,'%H:%M') .' - '.userdate($schedulerecord->endtime,'%H:%M');
            $errormessage = $this->bulk_enroll_user_validations($examid,$profileid,$request->id,$scheduleid,$organization);
            if(!$errormessage) {
                $DB->execute("UPDATE {local_users} SET bulkenrollstatus = 0 WHERE userid = $request->id AND bulkenrollstatus IN(1,2)");
            }
            $confirmrequestslist[$count]["actionview"]=(!$errormessage) ? true : false;
            $confirmrequestslist[$count]["profilecode"] =$profile->profilecode;
            $confirmrequestslist[$count]["errormessage"] =($errormessage) ? array_values($errormessage) : [];
            
            $count++;
        }
        $coursesContext = array(
            "hascourses" => $confirmrequestslist,
            "totalrequests" => $totalrequests,
            "length" => count($confirmrequestslist)
        );
        return $coursesContext;
    }
    public function bulk_enroll_user_validations($examid,$profileid,$userid,$scheduleid,$organization) {
        global $DB,$USER;
        $errormessage =[];
        $systemcontext = context_system::instance();
        //User exist but belong to another organization or empty organization
        if(!is_siteadmin() && has_capability('local/organization:manage_organizationofficial',$systemcontext)) {
            $traineeuserorg =(int) $DB->get_field('local_users','organization',['userid'=>$userid]);
            if($traineeuserorg == 0 || ($traineeuserorg > 0 && ($traineeuserorg != $organization))) {
                $errormessage[]['message']= get_string('noturorganization','local_exams');
                $DB->execute("UPDATE {local_users} SET bulkenrollstatus = 2 WHERE userid = $userid.");
            }
        }
        //Any issue on hall availability
        $currentuserorg =$this->hall_availability_issue($examid,$profileid,$userid,$scheduleid,$organization);
        if($currentuserorg){
            $errormessage[]['message']= $currentuserorg;
            $DB->execute("UPDATE {local_users} SET bulkenrollstatus = 2 WHERE userid = $userid.");
        }
        //User didn't pass the exam requirements
        $requirementresponse = $this->userrequirements($examid,$userid);
       if($requirementresponse) {
            $errormessage[]['message']= get_string('notachivenedrequirements','local_exams');
            $DB->execute("UPDATE {local_users} SET bulkenrollstatus = 2 WHERE userid = $userid.");
        }
        //Exam for Saudis ann user is non-Saudi
        $nonsaudiesponse = $this->tragetaudiancechecking($profileid,$userid);
        if($nonsaudiesponse) {
            $errormessage[]['message']= get_string('tragetaudianceissue','local_exams');
            $DB->execute("UPDATE {local_users} SET bulkenrollstatus = 2 WHERE userid = $userid.");
        }
        //User already enrolled
        $alreadyenrolled = $DB->record_exists_sql('SELECT * FROM {local_exam_userhallschedules} WHERE examid=:examid  AND userid=:userid  AND examdate >= UNIX_TIMESTAMP(NOW()) AND enrolstatus = 1',['examid'=>$examid,'profileid'=>$profileid,'userid'=>(int)$userid]);
        if($alreadyenrolled) {
            $errormessage[]['message']= get_string('alreadyenrolled','local_exams');
            $DB->execute("UPDATE {local_users} SET bulkenrollstatus = 2 WHERE userid = $userid.");

        }
        //User has enrollment at the same date and time
        $hallscheduleexists = $DB->record_exists('local_exam_userhallschedules',['hallscheduleid'=>$scheduleid,'userid'=>$userid,'enrolstatus' =>1]);
        if($hallscheduleexists) {
            $errormessage[]['message']= get_string('alreadyenrolledtoanother','local_exams');
            $DB->execute("UPDATE {local_users} SET bulkenrollstatus = 2 WHERE userid = $userid.");
        }
        //User pass the exam before
        $examhallscheduleid =(int) $DB->get_field_sql('SELECT id FROM {local_exam_userhallschedules} WHERE examid=:examid AND profileid=:profileid AND userid=:userid ORDER BY id DESC limit 1',['examid'=>$examid,'profileid'=>$profileid,'userid'=>(int)$userid]);
        if($examhallscheduleid) {
            $grade = $this->gradestatus($examhallscheduleid);
            if($grade) {
                $passinggrade = (int) $DB->get_field('local_exam_profiles','passinggrade',['id'=>$profileid]);
                if(is_numeric($grade) && $grade >= $passinggrade ){
                    $errormessage[]['message']= get_string('alreadypassedthisexam','local_exams');
                    $DB->execute("UPDATE {local_users} SET bulkenrollstatus = 2 WHERE userid = $userid.");
                }
            }
        }
        //attempts exists
        $hasnextattempt = $this->hasnextattempt($examid,$userid);
        if(!$hasnextattempt) {
            $errormessage[]['message']= get_string('thereisnonextattempt','local_exams');
            $DB->execute("UPDATE {local_users} SET bulkenrollstatus = 2 WHERE userid = $userid.");

        }
             
        return $errormessage;
    }

    public function hasnextattempt($examid,$userid) {
        global $DB;
        $sql = "SELECT COUNT(id) 
        FROM {local_exam_userhallschedules} leu 
        WHERE leu.examid=$examid AND examdate !=0 AND leu.userid=".$userid." AND enrolstatus = 1";
        $attempsnumber = $DB->count_records_sql($sql);
        $nextattempt =  ($attempsnumber == 0)  ? 1 : ($attempsnumber+1);
        $attemptid =  $DB->get_field('local_exam_attempts', 'id', ['examid' => $examid, 'attemptid' => $nextattempt]);
        return $attemptid;

    }
    public function hall_availability_issue($examid,$profileid,$userid,$scheduleid,$organization) {
        global $DB;

        $username = $DB->get_field('user','username',['id'=>$userid]);
        $examcode = $DB->get_field('local_exams','code',['id'=>$examid]);
        $profilecode = $DB->get_field('local_exam_profiles','profilecode',['id'=>$profileid]);
        $centerdata = $DB->get_record_sql('SELECT hl.code,hs.startdate,hs.starttime FROM {hall} hl JOIN {hallschedule} hs ON hs.hallid = hl.id WHERE hs.id = '.$scheduleid.'');
        $userorganization = $DB->get_field('local_organization','licensekey',['id'=>$organization]);
        $examdate = strtotime(userdate($centerdata->startdate,'%Y-%m-%d') .' '. userdate($centerdata->starttime,'%H:%M:%S'));
        $errormessage = $DB->get_field_sql("SELECT errormessage FROM {local_fast_examenrol} 
                                    WHERE username = '$username' AND centercode = '$centerdata->code'
                                    AND examcode = '$examcode' AND profilecode = '$profilecode' 
                                    AND userorganization = $userorganization AND UNIX_TIMESTAMP(examdatetime) = $examdate 
                                    AND transactiontypes = 1 AND validation = 1 ORDER BY id DESC LIMIT 1");
        
        return $errormessage;
    }
    
    public function tragetaudiancechecking($profileid,$userid) {
        global $DB,$USER,$CFG;
        $profile = $DB->get_record('local_exam_profiles', ['id' => $profileid]);
        $usernationality = $DB->get_field('local_users', 'nationality',  ['userid' => $userid]);
        if(is_numeric($usernationality)) {
            $countries = (new manageuser)->get_list_of_nationalities();
            $usercountry=$countries[$usernationality];
        } else {
            $countries = get_string_manager()->get_list_of_countries();
            $usercountry = $countries[$usernationality];
        }
        if (($profile->targetaudience == 1 && ($usercountry == 'Saudi Arabia' || $usercountry == 'السعودية')) || ($profile->targetaudience == 2 && ($usercountry != 'Saudi Arabia' || $usercountry == 'السعودية')) ||  ($profile->targetaudience == 3) ) {
            return  false;
        } else {
            return  true;
        }

    }

    /**
     * 
     * Get list of schedules mapped with an exam
     * 
     */
    public function get_exam_schedules($examid) {
        global $DB, $PAGE, $OUTPUT, $USER;
        $context = context_system::instance();
        $renderer = $PAGE->get_renderer('local_exams');
        $filterparams = $renderer->get_catalog_schedules(true);
        $filterparams['submitid'] = 'form#filteringform';
        $filterparams['widthclass'] = 'col-md-12';
        $filterparams['globalinput'] = $renderer->global_filter($filterparams);
        $filterparams['examid'] = $examid;
        $filterparams['schedules'] = $renderer->get_catalog_schedules(false, $filterparams);
        $fform = exams_reservation_filters_form($filterparams);
        $filterparams['filterform'] = $fform->render();
        echo $renderer->listexam_schedules($filterparams);
       
    }
    /**
     * Exam Reservations
     * 
     * @param INT $examid
     * @return stdClass $reservations
     */
    public function get_exam_reservations($examid) {
        global $PAGE;
        $context = context_system::instance();
        $renderer = $PAGE->get_renderer('local_exams');
        $filterparams = $renderer->get_catelog_reservations(true);
        $filterparams['submitid'] = 'form#filteringform';
        $filterparams['widthclass'] = 'col-md-12';
        $filterparams['examid'] = $examid;
        $filterparams['inputclasses'] = 'searchinput';
        $filterparams['globalinput'] = $renderer->global_filter($filterparams);
        $filterparams['reservationdata'] = $renderer->get_catelog_reservations(false, $filterparams);
        $fform = exams_reservation_filters_form($filterparams);
        $filterparams['filterform'] = $fform->render();
        echo $renderer->display_reservations($filterparams);
    }
    /**
     * Get user role
     * @param $userid (INT)
     * @return stdClass object $user_assigned_role
     */
    public function get_user_role($userid) {
        global $DB, $COURSE;
        if (!$userid || $userid == '') {
            print_error('missinguserid', 'local_exams');
        }
     
        $courseid = $COURSE->id;
        if ($courseid > 1) {
            $join = " JOIN {context} ctx ON ctx.id = ra.contextid JOIN {course} c ON c.id = ctx.instanceid";
            $where = " AND c.id = ". $courseid;
        }
        $user_assigned_role = $DB->get_record_sql(" SELECT u.id, u.firstname, u.lastname, r.shortname 
            FROM {user} u
            JOIN {role_assignments} ra ON ra.userid = u.id
            JOIN {role} r ON r.id = ra.roleid
            $join
            WHERE u.id = :userid $where
            ORDER BY ra.id DESC
        ", ['userid' => $userid]);
        return $user_assigned_role;
    }
    public function sadad_for_bulkenrollusers($sdata) {
        global $DB;
        $traineeids = base64_decode($sdata->tuserid);
        $bulkenrolltype =($sdata->actionfor == 'exams')  ? 'exam' : 'program';
        $ausers =  $DB->get_fieldset_sql("SELECT userid from {local_users} WHERE FIND_IN_SET(userid,'$traineeids') AND bulkenrollstatus = 0 AND bulkenrolltype = '$bulkenrolltype' ");
        $ausers= implode(',',$ausers);
        $sdata->ausers = $ausers;

        if($sdata->actionfor == 'exams') {
            $records = $DB->get_records_sql("SELECT productid,COUNT(id) AS userscount FROM {local_exam_userhallschedules} WHERE examid =$sdata->rootid AND profileid=$sdata->fieldid  AND FIND_IN_SET(userid,'$ausers') AND productid > 0 AND enrolltype = 1 GROUP BY productid ");
        } else {
            $records = $DB->get_records_sql("SELECT productid,COUNT(id) AS userscount FROM {program_enrollments} WHERE programid =$sdata->rootid AND offeringid=$sdata->fieldid  AND FIND_IN_SET(userid,'$ausers')AND productid > 0 AND enrolltype = 1 GROUP BY productid ");
        }
        if($records) {
            foreach($records AS $record) {
                $sdata->enrolementtype = $sdata->actionfor.'bulkenrollment';
                $response = $this->generate_sadad_for_bulkenrollusers($sdata, $record->productid, $record->userscount);
            }  
        } 

        return $response;
         
    }

    public function generate_sadad_for_bulkenrollusers($sdata,$productid,$numofusers) {
        global $DB,$USER,$CFG;
        $context = context_system::instance();
        if($sdata->enrolementtype == 'assessment_operation_enrolments') {
          $sdata->rootid = $sdata->examid;
          $sdata->fieldid = $sdata->profileid;
        }
        $productrecord = $DB->get_record('tool_products',['id'=>$productid]);

        if($productrecord->price > 0) {
            $tablename = ($sdata->actionfor == 'exams'  ||  $sdata->enrolementtype == 'assessment_operation_enrolments')? 'local_exams' : 'local_trainingprogram';
            $tax_free = $DB->get_field($tablename,'tax_free',['id' => $sdata->rootid]);
            $tax_slab = get_config('tool_product', 'tax_percentage');
            if($sdata->actionfor == 'programs'){
                $offeringrecord = $DB->get_record('tp_offerings',['id'=>$sdata->fieldid]);
                if((int)$offeringrecord->type == 1 && (int)$offeringrecord->offeringpricing == 1) {
                    $existinginvoice_number = $DB->get_field_sql("SELECT invoice_number FROM {tool_product_sadad_invoice}  WHERE  productid =:productid AND organization =:organization  AND status =:status AND type IN ('programsbulkenrollment','purchase')",['productid'=>$productid,'organization'=>$sdata->organization,'status'=>1]);
                    if($existinginvoice_number) {
                        $orderid =  $this->insert_data_in_orgofficial_related_tables($sdata,$existinginvoice_number,$productid,$numofusers,'alreadyexists');
                        return $orderid;
                    } else {
                        $total_price = $productrecord->price;
                    }
                    
                } else {
                    $total_price = $productrecord->price*$numofusers;
                }

            } else {
                $total_price = $productrecord->price*$numofusers;
            }
            $discount = !empty($sdata->discount) ? $sdata->discount : 0;
            $priceafterdiscount = $total_price - $discount;
            $taxes = $this->caluculate_taxes($priceafterdiscount, $tax_slab);
            $item_taxes = ($tax_free == 0) ? $taxes :0;
            $totalamount = ROUND(($priceafterdiscount + $item_taxes),2);
            $sdata->discount = $discount;
            $sdata->taxes = $item_taxes;
            $sdata->originalprice = $total_price;
            $sdata->payableamount = $totalamount;
            if($totalamount > 50) {
                $entity  = $DB->get_record($tablename,['id'=>$sdata->rootid]);
                $sendingdata =new stdClass();
                $sendingdata->rootid = $sdata->rootid;
                $sendingdata->fieldid = $sdata->fieldid;
                $sendingdata->userid =$sdata->orgofficial;
                $sendingdata->type = $sdata->enrolementtype;
                $sendingdata->entitytype = $sdata->enrolementtype;
                $sendingdata->productid = $productid;
                $sendingdata->seats = $numofusers;
                $sendingdata->total = $totalamount;
                $sendingdata->payableamount = $totalamount;
                $sendingdata->amount = $totalamount;
                $sendingdata->productname = (($sdata->actionfor == 'exams' ||  $sdata->enrolementtype == 'assessment_operation_enrolments' )? ((current_language() =='ar') ? $entity->examnamearabic : $entity->exam) : ((current_language() =='ar') ? $entity->namearabic : $entity->name));
                $productdata =  base64_encode(serialize((array)$sendingdata));
                $response = (new telr)->generate_sadad_bill($productdata);
                if(!empty($response)) {
                    $responsedata = $response[0];
                    if($responsedata->invoiceNumber) {
                        $record = new stdClass();
                        $record->productid =$productid;
                        $record->userid = $sdata->orgofficial;
                        $record->realuser =($USER->realuser) ? $USER->realuser :0;
                        $record->telrid = 0;
                        $record->invoice_number =$responsedata->invoiceNumber;
                        $record->seats =$numofusers;
                        $record->type = $sdata->enrolementtype;
                        $record->amount =$responsedata->amount;
                        $record->payableamount =$responsedata->amount;
                        $record->status =1;
                        $record->payment_status =0;
                        $record->timecreated = time();
                        $record->usercreated = $USER->id;
                        $record->organization = $sdata->organization;
                        try{
                            $insertid = $DB->insert_record('tool_product_sadad_invoice', $record);
                            if($insertid) {
                                $responsedata->sadadid = $insertid;
                                $this->insert_update_sada_invoice_logs($responsedata);
                                $this->insert_data_in_orgofficial_related_tables($sdata,$responsedata->invoiceNumber,$productid,$numofusers,'new');
                                $responsedata->sadadid = $insertid;
                                $returndata['response'] = 'success' ;
                                $returndata['returnurl'] =($sdata->actionfor == 'exams') ?((!is_siteadmin() && has_capability('local/organization:manage_organizationofficial', $context)) ? $CFG->wwwroot.'/local/exams/examusers.php?id='.$sdata->rootid.'&profileid='.$sdata->fieldid.'' : $CFG->wwwroot.'/local/exams/examenrollment.php?examid='.$sdata->rootid.'&profileid='.$sdata->fieldid.'') : ((!is_siteadmin() && has_capability('local/organization:manage_organizationofficial', $context)) ? $CFG->wwwroot.'/local/trainingprogram/programenrolleduserslist.php?programid='.$sdata->rootid.'&offeringid='.$sdata->fieldid.'' :$CFG->wwwroot.'/local/trainingprogram/programenrollment.php?programid='.$sdata->rootid.'&roleid='.$sdata->roleid.'&offeringid='.$sdata->fieldid.'');
                                return $returndata;
                                
                            }
                           
                        }
                        catch(dml_exception $e){
                            print_error($e);
                        }
                    } else {

                        $traineeids = $sdata->ausers;
                        if($sdata->actionfor == 'exams' || $sdata->enrolementtype == 'assessment_operation_enrolments') { 

                            $DB->execute("UPDATE {local_exam_userhallschedules} SET enrolstatus = 0 WHERE examid = $sdata->rootid AND profileid = $sdata->fieldid  AND  hallscheduleid= $sdata->scheduleid  AND productid = $productid AND FIND_IN_SET(userid,'$traineeids') AND organization = $sdata->organization AND enrolltype = 1 ");
                            $DB->execute("UPDATE {exam_enrollments} SET enrolstatus = 0 WHERE examid = $sdata->rootid AND profileid = $sdata->fieldid  AND  hallscheduleid= $sdata->scheduleid AND FIND_IN_SET(userid,'$traineeids') AND organization = $sdata->organization AND enrolltype = 1 ");
                        }
                    }
                } else {
                    $traineeids = $sdata->ausers;
                    if($sdata->actionfor == 'exams' || $sdata->enrolementtype == 'assessment_operation_enrolments') { 

                        $DB->execute("UPDATE {local_exam_userhallschedules} SET enrolstatus = 0 WHERE examid = $sdata->rootid AND profileid = $sdata->fieldid  AND  hallscheduleid= $sdata->scheduleid  AND productid = $productid AND FIND_IN_SET(userid,'$traineeids') AND organization = $sdata->organization AND enrolltype = 1 ");
                        $DB->execute("UPDATE {exam_enrollments} SET enrolstatus = 0 WHERE examid = $sdata->rootid AND profileid = $sdata->fieldid  AND  hallscheduleid= $sdata->scheduleid AND FIND_IN_SET(userid,'$traineeids') AND organization = $sdata->organization AND enrolltype = 1 ");
                    }
                    
                    
                }

            }


        } else {
            
           $orderid =  $this->insert_data_in_orgofficial_related_tables($sdata,0,$productid,$numofusers,'new');

           return $orderid;
    
           
           
        }
        

    }
    public function insert_update_sada_invoice_logs($data) {
        global $DB, $USER;

        $record = new stdClass();
        $record->sadadid =$data->sadadid;
        $record->invoice_number =$data->invoiceNumber;
        $record->cardtid =$data->cardtId;
        $record->amount =$data->amount;
        $record->is_enterprise =$data->isEnterprise;
        $record->registration_no =$data->registrationNo;
        $record->company_name =$data->companyName;
        $record->commisioner_name =$data->commisionerName;
        $record->commisioner_id =$data->commisionerId;
        $record->commissioner_mobile_no =$data->commissionerMobileNo;
        $record->commissioner_email =$data->commissionerEmail;
        $record->payment_status =($data->paymentStatus == 'Paid') ? 1 : 0;
        $record->issue_date =$data->issueDate? strtotime($data->issueDate) : 0;
        $record->expiry_date =$data->expiryDate ? strtotime($data->expiryDate) : 0;
        $record->timecreated = time();
        $record->usercreated = $USER->id;
        try{
           $DB->insert_record('sadad_invoice_logs', $record);
        }
        catch(dml_exception $e){
            print_error($e);
        }
    }

    public function insert_data_in_orgofficial_related_tables($sdata,$invoiceNumber,$productid,$noofusers,$invoicetype = false) {
        global $DB, $USER,$CFG;
        $context = context_system::instance();
        $enrolstatus = 1;
        $approvalseats = $noofusers ;
        if($invoicetype == 'alreadyexists') {
            // update tool_product_sadad_invoice table
            $sadadrecord = $DB->get_record('tool_product_sadad_invoice',['productid'=>$productid,'invoice_number'=>$invoiceNumber,'organization'=>$sdata->organization]);
            if($sadadrecord) {
                $record1 = new stdClass();
                $record1->id = $sadadrecord->id;
                $record1->seats = $sadadrecord->seats+$noofusers;
                $DB->update_record('tool_product_sadad_invoice', $record1);
             
                // update tool_org_order_payments table
                $orgpaymentrecord = $DB->get_record('tool_org_order_payments',['productid'=>$productid,'tablename'=> 'tp_offerings','fieldname'=>'id','fieldid'=>$sdata->fieldid,    'transactionid'=>$sadadrecord->invoice_number,'organization'=>$sdata->organization]);
                if($orgpaymentrecord) {
                    $record2 = new stdClass();
                    $record2->id = $orgpaymentrecord->id;
                    $record2->purchasedseats = $orgpaymentrecord->purchasedseats+$noofusers;
                    $DB->update_record('tool_org_order_payments', $record2);

                    // update tool_order_approval_seats table
                    $approvalseatsrecord = $DB->get_record('tool_order_approval_seats',['productid'=>$productid,'tablename'=> 'tp_offerings','fieldname'=>'id','fieldid'=>$sdata->fieldid,'paymentid'=>$orgpaymentrecord->id,'organization'=>$sdata->organization]);
                    if($approvalseatsrecord) {
                        $record3 = new stdClass();
                        $record3->id = $approvalseatsrecord->id;
                        $record3->purchasedseats = $approvalseatsrecord->purchasedseats+$noofusers;
                        $record3->approvalseats = $approvalseatsrecord->approvalseats+$noofusers;
                        $DB->update_record('tool_order_approval_seats', $record3);
                    }

                    // update tool_org_order_seats table
                    $orderseatsrecord = $DB->get_record('tool_org_order_seats',['productid'=>$productid,'tablename'=> 'tp_offerings','fieldname'=>'id','fieldid'=>$sdata->fieldid,'organization'=>$sdata->organization]);
                    if($orderseatsrecord) {
                        $record4 = new stdClass();
                        $record4->id = $orderseatsrecord->id;
                        $record4->purchasedseats = $orderseatsrecord->purchasedseats+$noofusers;
                        $record4->approvalseats = $orderseatsrecord->approvalseats+$noofusers;
                        $record4->availableseats = $orderseatsrecord->availableseats+$noofusers;
                        $DB->update_record('tool_org_order_seats', $record4);

                    }

                }
                if($sdata->enrolementtype == 'manual') {
                   $userids = $sdata->users;
                   $traineeroleid = $DB->get_field('role', 'id', array('shortname' => 'trainee'));
                    foreach($userids as $userid){                  
                       (new trainingprogram)->program_enrollment($sdata->fieldid,$userid,$traineeroleid,false,$USER->id,'manual',false,$sdata->organization,$sdata->productid);
                    }
                } else {

                    $traineeids = $sdata->ausers;
                    if($sdata->enrolementtype != 'assessment_operation_enrolments') {
                        $DB->execute("UPDATE {local_users} SET bulkenrollstatus = 1 WHERE FIND_IN_SET(userid,'$traineeids') AND bulkenrollstatus = 0");
                    }
                    $DB->execute("UPDATE {program_enrollments} SET enrolstatus = $enrolstatus,enrolltype = 2,orgofficial =  $sdata->orgofficial WHERE programid = $sdata->rootid AND offeringid = $sdata->fieldid AND productid = $productid AND organization = $sdata->organization AND FIND_IN_SET(userid,'$traineeids') AND enrolstatus = 0 ");
                    $courseid=(int) $DB->get_field('local_trainingprogram','courseid',['id' => $sdata->rootid]);
                    $teacherroleid=$DB->get_field('role','id',array('shortname'=>'teacher'));
                    $instance = $DB->get_record('enrol', array('courseid' => $courseid, 'enrol' => 'manual'), '*', MUST_EXIST);
                    $allorgusers = $DB->get_records_sql("SELECT u.id,u.firstname FROM {user} as u 
                    JOIN {local_users} as lc ON lc.userid = u.id 
                    JOIN {role_assignments} as  ra on ra.userid=u.id
                    JOIN {role} as r ON ra.roleid = r.id  AND r.shortname = 'organizationofficial'
                    WHERE lc.organization =:organization AND  ra.contextid =:contextid",['organization'=>$sdata->organization,'contextid'=>$context->id]);
                    $tpdata = new stdClass();
                    $tps = $DB->get_record('local_trainingprogram',array('id'=>$sdata->rootid));
                    $tpdata->program_name = $tps->name;                
                    $tpdata->program_arabicname = $tps->namearabic;
                    $userids =  explode(",", $traineeids);
    
                    if(!is_siteadmin() && has_capability('local/organization:manage_organizationofficial',$context)){
    
                        $orgoff  = $DB->get_record('local_users',array('userid'=>$USER->id)); 
                        $tp =  $DB->get_record('local_trainingprogram',array('id'=>$sdata->rootid)); 
                        
                        $tpoffering = $DB->get_record('tp_offerings',array('id'=>$sdata->fieldid));
                
                        
                        if($tpoffering->startdate != 0 && $tpoffering->enddate != 0){
                            $tpdata->offering_startdate = userdate($tpoffering->startdate,'%d-%m-%Y');
                            $tpdata->offering_enddate = userdate($tpoffering->enddate,'%d-%m-%Y');
                        }
    
                        $starttimemeridian = gmdate('a',$tpoffering->time);
                        $endtimemeridian = gmdate('a',$tpoffering->endtime);
                        if($orgoff->lang == 'ar')  {
                            $tpdata->org_off = $orgoff->firstnamearabic .' '. $orgoff->middlenamearabic.' '. $orgoff->thirdnamearabic.' '. $orgoff->lastnamearabic;
                            $tpdata->program_name = $tp->namearabic;
                            $startmeridian = ($starttimemeridian == 'am')? 'صباحًا' : 'مساءً';
                            $endtimemeridian = ($endtimemeridian == 'am')? 'صباحًا' : 'مساءً';
                            $lang = 'ar';
    
                        } else{
                            $tpdata->org_off = $orgoff->firstname .' '. $orgoff->middlenameen.' '. $orgoff->thirdnameen.' '. $orgoff->lastname;
                            $tpdata->program_name =  $tp->name;
                            $startmeridian = ($starttimemeridian == 'am')? 'AM': 'PM';
                            $endtimemeridian = ($endtimemeridian == 'am')? 'AM': 'PM';
                            $lang = 'en';
    
                        }
                        if( $tpoffering->time !=0 && $tpoffering->endtime !=0){
                            $tpdata->offering_starttime =  gmdate("H:i",$tpoffering->time) . ' '.$startmeridian ; 
                            $tpdata->offering_endtime =  gmdate("H:i",$tpoffering->endtime) . ' '. $endtimemeridian  ; 
    
                        }
                        $tpdata->trainee_details = $this->get_bulkenrolprogram_users($userids,$lang);                     
    
                        (new \local_trainingprogram\notification())->trainingprogram_notification('bulkenrol_program', $touser = $USER,$fromuser = $USER,$tpdata,$waitinglistid=0);
    
    
                            
                    } elseif(has_capability('local/organization:manage_trainingofficial', $context) &&!is_siteadmin()  ){
                        $tpid = $sdata->rootid;
                        $tpoid = $sdata->fieldid;
    
                        $notification = $this->bulkenrolprogram_notification($sdata->orgofficial,$userids,$tpid,$tpoid); 
    
                    }else{
                        foreach($userids as $userid){                            
                            $tpusers = $DB->get_record('local_users',array('userid'=>$userid));                            
                            $tpdata->program_userfullname=$tpusers->firstname .' '. $tpusers->middlenameen.' '. $tpusers->thirdnameen.' '. $tpusers->lastname;
                            $tpdata->program_userarabicfullname = $tpusers->firstnamearabic .' '. $tpusers->middlenamearabic.' '. $tpusers->thirdnamearabic.' '. $tpusers->lastnamearabic;                            
                            $trainee = $DB->get_record('user',array('id'=> $userid)); 
                            (new \local_trainingprogram\notification())->trainingprogram_notification('trainingprogram_enroll', $touser=$trainee,$fromuser = $USER,$tpdata,$waitinglistid=0);
                        }                                
    
                    }  
                    $returndata['response'] = 'success' ;
                    $returndata['returnurl'] =($sdata->actionfor == 'exams') ?((!is_siteadmin() && has_capability('local/organization:manage_organizationofficial', $context)) ? $CFG->wwwroot.'/local/exams/examusers.php?id='.$sdata->rootid.'&profileid='.$sdata->fieldid.'' : $CFG->wwwroot.'/local/exams/examenrollment.php?examid='.$sdata->rootid.'&profileid='.$sdata->fieldid.'') : ((!is_siteadmin() && has_capability('local/organization:manage_organizationofficial', $context)) ? $CFG->wwwroot.'/local/trainingprogram/programenrolleduserslist.php?programid='.$sdata->rootid.'&offeringid='.$sdata->fieldid.'' :$CFG->wwwroot.'/local/trainingprogram/programenrollment.php?programid='.$sdata->rootid.'&roleid='.$sdata->roleid.'&offeringid='.$sdata->fieldid.'');
                    return $returndata;


                }

            }
            
        } else {

            $record = new stdClass();
            $tablename = ($sdata->actionfor == 'exams' || $sdata->enrolementtype == 'assessment_operation_enrolments')? 'local_exam_profiles' : 'tp_offerings';
            $record->productid =$productid;
            $record->tablename =$tablename;
            $record->fieldname ='id';
            $record->fieldid =$sdata->fieldid;
            $record->orguserid =$sdata->orgofficial;
            $record->realuser =($USER->realuser) ? $USER->realuser :0;
            $record->purchasedseats =$noofusers;
            $record->approvalseats = $approvalseats;
            $record->availableseats =$approvalseats;
            $record->usercreated =$USER->id;
            $record->timecreated =time();
            $record->organization =$sdata->organization;
            try{
            $orgseatsinsertid = $DB->insert_record('tool_org_order_seats', $record);
            if($orgseatsinsertid) {
                    $record->paymenttype ='postpaid';
                    $record->paymenton =time();
                    $record->amount =$sdata->payableamount;
                    $record->approvalon =time();
                    $record->transactionid =$invoiceNumber;
                    $record->originalprice =$sdata->originalprice;
                    $record->taxes =$sdata->taxes;
                    $record->discountprice = $sdata->discount;
                    $record->payableamount =$sdata->payableamount;
                    $orgorderpaymentid = $DB->insert_record('tool_org_order_payments', $record); 
                    if($orgorderpaymentid) {
                        $record->paymentid = $orgorderpaymentid;
                        $orderid = $DB->insert_record('tool_order_approval_seats', $record);
                        $traineeids = $sdata->ausers;
                        if($sdata->enrolementtype != 'assessment_operation_enrolments') {
                            $DB->execute("UPDATE {local_users} SET bulkenrollstatus = 1 WHERE FIND_IN_SET(userid,'$traineeids') AND bulkenrollstatus = 0");
                        }
                    
                        if($sdata->actionfor == 'programs') {

                            $userids =  explode(",", $traineeids);
                            $DB->execute("UPDATE {program_enrollments} SET enrolstatus = $enrolstatus,enrolltype = 2,orgofficial =  $sdata->orgofficial WHERE programid = $sdata->rootid AND offeringid = $sdata->fieldid AND productid = $productid AND organization = $sdata->organization AND FIND_IN_SET(userid,'$traineeids') AND enrolstatus = 0 ");
                            // Updating Discount - starts
                            $discountdata = new stdClass();
                            $discountdata->productid =$productid;
                            $discountdata->userid =$sdata->orgofficial;
                            $discountdata->discount_type =$sdata->discounttype;
                            $discountdata->discountid =$sdata->discounttableid;
                            $discountdata->discountamount =$sdata->discount;
                            $discountdata->status =1;
                            $discountdata->orgorderid =$orgorderpaymentid;
                            (new product)->insert_update_order_discount_usage($discountdata);
                            (new product)->update_discount_status($productid,$sdata->discounttableid,$sdata->discounttype,$noofusers);
                            // Updating Discount - ends
                            $courseid=(int) $DB->get_field('local_trainingprogram','courseid',['id' => $sdata->rootid]);

                            $courseid=(int) $DB->get_field('local_trainingprogram','courseid',['id' => $sdata->rootid]);
                            $teacherroleid=$DB->get_field('role','id',array('shortname'=>'teacher'));
                            $instance = $DB->get_record('enrol', array('courseid' => $courseid, 'enrol' => 'manual'), '*', MUST_EXIST);
                            $allorgusers = $DB->get_records_sql("SELECT u.id,u.firstname FROM {user} as u 
                            JOIN {local_users} as lc ON lc.userid = u.id 
                            JOIN {role_assignments} as  ra on ra.userid=u.id
                            JOIN {role} as r ON ra.roleid = r.id  AND r.shortname = 'organizationofficial'
                            WHERE lc.organization =:organization AND  ra.contextid =:contextid",['organization'=>$sdata->organization,'contextid'=>$context->id]);

                            $offeringrecord =$DB->get_record('tp_offerings',['id' => $sdata->fieldid]); 
                            $programrecord =$DB->get_record('local_trainingprogram',['id' => $sdata->rootid]); 
                            foreach($allorgusers AS $orguser){
                               $manual = enrol_get_plugin('manual');
                               $timestart = time();
                               $timeend = 0;
                               $manual->enrol_user($instance, $orguser->id, $teacherroleid, $timestart, $timeend); 
                            
                                // Creating seperate group for organization - Starts//
                                $orgshortcode=$DB->get_field_sql('
                                SELECT shortname FROM {local_organization} 
                                WHERE id ='.$sdata->organization.''
                               );
                                $groupdata = new stdClass;
                                $groupdata->name = ($orgshortcode) ? $orgshortcode.$offeringrecord->code : $orguser->id.'_'.$offeringrecord->code;
                                $groupdata->idnumber =($orgshortcode) ? $orgshortcode.$offeringrecord->code : $orguser->id.'_'.$offeringrecord->code;
                                $groupdata->courseid= $programrecord->courseid;
           
                                if(!groups_get_group_by_idnumber($programrecord->courseid, $groupdata->idnumber)){
                                    $orgusergroupid = groups_create_group($groupdata);
                                }
                                if($orgusergroupid) {
                                    groups_add_member($orgusergroupid,$orguser->id,null,0);
                                }
                                $offeringgroup = $DB->get_record_sql("SELECT grop.id FROM {groups} as grop JOIN {tp_offerings} as tpo ON tpo.code = grop.idnumber WHERE tpo.id = $sdata->fieldid");
                                $offeringgroupid = (int) $offeringgroup->id;
                                if ($offeringgroupid) {
                                    groups_add_member($offeringgroupid,$orguser->id,null,0);
                                } 
                                foreach($userids AS $userid){
                                    $group = $DB->get_record_sql("SELECT grop.id FROM {groups} as grop JOIN {tp_offerings} as tpo ON tpo.code = grop.idnumber WHERE tpo.id = $sdata->fieldid");
                                    $groupid = (int) $group->id;
                                    if ($groupid) {
                                        groups_add_member($groupid, $userid,null,0);
                                    }
                                    if ($orgusergroupid) {
                                        groups_add_member($orgusergroupid, $userid,null,0);
                                    } 
                                }              
                            }
                            
                            $tpdata = new stdClass();
                            $tps = $DB->get_record('local_trainingprogram',array('id'=>$sdata->rootid));
                            $tpdata->program_name = $tps->name;                
                            $tpdata->program_arabicname = $tps->namearabic;
                            if(!is_siteadmin() && has_capability('local/organization:manage_organizationofficial',$context)){

                                $orgoff  = $DB->get_record('local_users',array('userid'=>$USER->id)); 
                                $tp =  $DB->get_record('local_trainingprogram',array('id'=>$sdata->rootid)); 
                                
                                $tpoffering = $DB->get_record('tp_offerings',array('id'=>$sdata->fieldid));
                        
                                
                                if($tpoffering->startdate != 0 && $tpoffering->enddate != 0){
                                    $tpdata->offering_startdate = userdate($tpoffering->startdate,'%d-%m-%Y');
                                    $tpdata->offering_enddate = userdate($tpoffering->enddate,'%d-%m-%Y');
                                }

                                $starttimemeridian = gmdate('a',$tpoffering->time);
                                $endtimemeridian = gmdate('a',$tpoffering->endtime);
                                if($orgoff->lang == 'ar')  {
                                    $tpdata->org_off = $orgoff->firstnamearabic .' '. $orgoff->middlenamearabic.' '. $orgoff->thirdnamearabic.' '. $orgoff->lastnamearabic;
                                    $tpdata->program_name = $tp->namearabic;
                                    $startmeridian = ($starttimemeridian == 'am')? 'صباحًا' : 'مساءً';
                                    $endtimemeridian = ($endtimemeridian == 'am')? 'صباحًا' : 'مساءً';
                                    $lang = 'ar';

                                } else{
                                    $tpdata->org_off = $orgoff->firstname .' '. $orgoff->middlenameen.' '. $orgoff->thirdnameen.' '. $orgoff->lastname;
                                    $tpdata->program_name =  $tp->name;
                                    $startmeridian = ($starttimemeridian == 'am')? 'AM': 'PM';
                                    $endtimemeridian = ($endtimemeridian == 'am')? 'AM': 'PM';
                                    $lang = 'en';

                                }
                                if( $tpoffering->time !=0 && $tpoffering->endtime !=0){
                                    $tpdata->offering_starttime =  gmdate("H:i",$tpoffering->time) . ' '.$startmeridian ; 
                                    $tpdata->offering_endtime =  gmdate("H:i",$tpoffering->endtime) . ' '. $endtimemeridian  ; 

                                }
                                $tpdata->trainee_details = $this->get_bulkenrolprogram_users($userids,$lang);                     

                                (new \local_trainingprogram\notification())->trainingprogram_notification('bulkenrol_program', $touser = $USER,$fromuser = $USER,$tpdata,$waitinglistid=0);


                                    
                            } elseif(has_capability('local/organization:manage_trainingofficial', $context) &&!is_siteadmin()  ){
                                $tpid = $sdata->rootid;
                                $tpoid = $sdata->fieldid;

                                $notification = $this->bulkenrolprogram_notification($sdata->orgofficial,$userids,$tpid,$tpoid); 

                            }else{
                                foreach($userids as $userid){                            
                                    $tpusers = $DB->get_record('local_users',array('userid'=>$userid));                            
                                    $tpdata->program_userfullname=$tpusers->firstname .' '. $tpusers->middlenameen.' '. $tpusers->thirdnameen.' '. $tpusers->lastname;
                                    $tpdata->program_userarabicfullname = $tpusers->firstnamearabic .' '. $tpusers->middlenamearabic.' '. $tpusers->thirdnamearabic.' '. $tpusers->lastnamearabic;                            
                                    $trainee = $DB->get_record('user',array('id'=> $userid)); 
                                    (new \local_trainingprogram\notification())->trainingprogram_notification('trainingprogram_enroll', $touser=$trainee,$fromuser = $USER,$tpdata,$waitinglistid=0);
                                }                                

                            }                  
    
                        }
                        if($sdata->actionfor == 'exams') {

                            $DB->execute("UPDATE {local_exam_userhallschedules} SET enrolstatus = $enrolstatus,enrolltype = 2,orgofficial =  $sdata->orgofficial WHERE examid = $sdata->rootid AND profileid = $sdata->fieldid  AND  hallscheduleid= $sdata->scheduleid AND FIND_IN_SET(userid,'$traineeids') AND productid = $productid AND organization = $sdata->organization AND enrolltype = 1");

                            $DB->execute("UPDATE {exam_enrollments}  SET enrolstatus = $enrolstatus,enrolltype = 2,orgofficial =  $sdata->orgofficial WHERE examid = $sdata->rootid AND profileid = $sdata->fieldid  AND  hallscheduleid= $sdata->scheduleid AND FIND_IN_SET(userid,'$traineeids') AND organization = $sdata->organization AND enrolltype = 1 ");

                            // Updating Discount - starts
                            $discountdata = new stdClass();
                            $discountdata->productid =$productid;
                            $discountdata->userid =$sdata->orgofficial;
                            $discountdata->discount_type =$sdata->discounttype;
                            $discountdata->discountid =$sdata->discounttableid;
                            $discountdata->discountamount =$sdata->discount;
                            $discountdata->status =1;
                            $discountdata->orgorderid =$orgorderpaymentid;
                            (new product)->insert_update_order_discount_usage($discountdata);
                            (new product)->update_discount_status($productid,$sdata->discounttableid,$sdata->discounttype,$noofusers);
                            // Updating Discount - ends

                            $userids =  explode(",", $traineeids);        
                            $this->bulkenrolexam_notification($sdata->orgofficial,$userids,$sdata->rootid,$sdata->fieldid, $sdata->scheduleid );
                            $exam_record = $DB->get_record_sql("SELECT ex.*, ep.profilecode examcode 
                            FROM {local_exams} ex 
                            JOIN {local_exam_profiles} ep ON ex.id = ep.examid 
                            WHERE ex.id = $sdata->rootid AND ep.id = $sdata->fieldid");
                                          
                            foreach($userids as $userid){
                                $this->exams_enrol_notification($exam_record, $userid, $sdata->scheduleid);
                            }
                        }

                        $productrecord = $DB->get_record('tool_products',['id'=>$productid]);

                        if($productrecord->price <= 0) {

                            if($orderid) {
                                $returndata['response'] = 'success' ;
                                    $returndata['returnurl'] =($sdata->actionfor == 'exams') ?((!is_siteadmin() && has_capability('local/organization:manage_organizationofficial', $context)) ? $CFG->wwwroot.'/local/exams/examusers.php?id='.$sdata->rootid.'&profileid='.$sdata->fieldid.'' : $CFG->wwwroot.'/local/exams/examenrollment.php?examid='.$sdata->rootid.'&profileid='.$sdata->fieldid.'') : ((!is_siteadmin() && has_capability('local/organization:manage_organizationofficial', $context)) ? $CFG->wwwroot.'/local/trainingprogram/programenrolleduserslist.php?programid='.$sdata->rootid.'&offeringid='.$sdata->fieldid.'' :$CFG->wwwroot.'/local/trainingprogram/programenrollment.php?programid='.$sdata->rootid.'&roleid='.$sdata->roleid.'&offeringid='.$sdata->fieldid.'');
                                    return $returndata;
                        
                            }
                        }

                    }
                
            }
            }
            catch(dml_exception $e){
                print_error($e);
            }
        }
    }
    /**
     * Get users exam completions
     * 
     */
    public function get_users_exam_completions($userid = null, $examid = null) {
        global $DB;
        
        $selectsql = "SELECT leu.examid, leu.userid,lep.profilecode,le.courseid, lep.showexamgrade, lep.quizid, lep.passinggrade, lep.id AS profileid, ROUND(gg.finalgrade, 2) as finalgrade
                        FROM
                        (
                            SELECT max(id) as id FROM {local_exam_userhallschedules} GROUP BY examid, userid
                        ) lleu
                        LEFT JOIN {local_exam_userhallschedules} leu on leu.id = lleu.id
                        LEFT JOIN {local_exams} le ON le.id = leu.examid 
                        LEFT JOIN {local_exam_profiles} lep ON lep.id = leu.profileid AND le.id = lep.examid AND lep.examid = leu.examid 
                        LEFT JOIN {grade_items} gi ON gi.courseid = le.courseid AND gi.iteminstance = lep.quizid AND gi.itemtype = 'mod' AND gi.itemmodule='quiz'
                        LEFT JOIN {grade_grades} gg ON gg.itemid = gi.id AND gg.userid = leu.userid
                         ";
              
        $selectsql.=" WHERE leu.examdate !=0 AND leu.examdate < UNIX_TIMESTAMP() AND lep.passinggrade <= gg.finalgrade ";
        $completions = $DB->get_records_sql($selectsql);
        return $completions;
    }
    /**
     * List of users enrolled in an exam
     * @param int $examid
     * @return (object) users
     */
    public function user_get_exam_users($stable = null, $filterdata = null, $completed = false){
        global $DB;
        $formsql = '';
        if(isset($filterdata->search_query) && trim($filterdata->search_query) != ''){
            $search = trim($filterdata->search_query);
            $formsql .= " AND (
                        ep.profilecode LIKE '%$search%' OR 
                        u.firstname LIKE '%$search%' OR 
                        u.lastname LIKE '%$search%' OR
                        lc.firstnamearabic LIKE '%$search%' OR
                        lc.lastnamearabic LIKE '%$search%' OR
                        lc.middlenameen LIKE '%$search%' OR
                        lc.middlenamearabic LIKE '%$search%' OR
                        lc.thirdnameen LIKE '%$search%' OR 
                        lc.thirdnamearabic LIKE '%$search%' OR 
                        lc.id_number LIKE '%$search%' OR
                        h.name LIKE '%$search%' 
                    )";
        }
        $lang = current_language();
        if (!empty($filterdata->idnumber) && $filterdata->idnumber != 'noselectionstring') {
            $formsql .= " AND lc.id_number = $filterdata->idnumber ";
        }
        if (!empty($filterdata->exam_center) && $filterdata->exam_center != 'noselectionstring') {
            $formsql .= " AND h.id = $filterdata->exam_center";
        }
        if (!empty($filterdata->exam) && $filterdata->exam != 'noselectionstring') {
            $formsql .= " AND le.id = $filterdata->exam";
        }
        $join = '';
        if (!empty($filterdata->organization) && $filterdata->organization != 'noselectionstring') {
            $join = " JOIN {local_organization} lo ON lo.id = lc.organization";
            $formsql .= " AND lo.id = $filterdata->organization";
        }
        if ($filterdata->certificate_status == 'issued') {
            $join .= " JOIN {tool_certificate_issues} tci ON tci.moduleid = ee.examid AND tci.moduletype = 'exams' AND tci.userid = lc.userid";
            $tcicode = " tci.id as cer_id, tci.code as certificate_code, ";
        }
        if ($filterdata->certificate_status == 'notissued') {
            $formsql .= " AND u.id NOT IN(SELECT userid FROM {tool_certificate_issues} WHERE moduleid = ee.examid AND moduletype = 'exams') ";
        }
        if (!$filterdata->certificate_status || $filterdata->certificate_status == -1) {
            $join .= " LEFT JOIN {tool_certificate_issues} tci ON tci.moduleid = ee.examid AND tci.moduletype = 'exams' AND tci.userid = lc.userid";
            $tcicode = " tci.id as cer_id, tci.code as certificate_code, ";
        }
        if( $filterdata->{'examdatetimefrom[enabled]'} == 1 && $filterdata->{'examdatetimeto[enabled]'} == 1 ){
            $start_year = $filterdata->{'examdatetimefrom[year]'};
            $start_month = $filterdata->{'examdatetimefrom[month]'};
            $start_day = $filterdata->{'examdatetimefrom[day]'};
            $filter_starttime_con = mktime(0,0,0, $start_month, $start_day, $start_year);

            $end_year = $filterdata->{'examdatetimeto[year]'};
            $end_month = $filterdata->{'examdatetimeto[month]'};
            $end_day = $filterdata->{'examdatetimeto[day]'};
            $filter_endtime_con=mktime(23,59,59, $end_month, $end_day, $end_year);
            $formsql.= " AND (uhs.examdate >= $filter_starttime_con AND uhs.examdate < $filter_endtime_con) ";
        } elseif($filterdata->{'examdatetimefrom[enabled]'} == 1 ){
            $start_year = $filterdata->{'examdatetimefrom[year]'};
            $start_month = $filterdata->{'examdatetimefrom[month]'};
            $start_day = $filterdata->{'examdatetimefrom[day]'};
            $filter_starttime_con = mktime(0,0,0, $start_month, $start_day, $start_year);
            $formsql.= " AND uhs.examdate >= '$filter_starttime_con' ";
        } elseif($filterdata->{'examdatetimeto[enabled]'} == 1 ){
            $start_year = $filterdata->{'examdatetimeto[year]'};
            $start_month = $filterdata->{'examdatetimeto[month]'};
            $start_day = $filterdata->{'examdatetimeto[day]'};
            $filter_endtime_con=mktime(23,59,59, $start_month, $start_day, $start_year);
            $formsql.=" AND uhs.examdate <= '$filter_endtime_con' ";
        }
        $fullname = (new \local_trainingprogram\local\trainingprogram())->user_fullname_case();
        if ($lang == 'ar') {
            $examname = " le.examnamearabic as examname, ";
        }else{
            $examname = " le.exam as examname, ";
        }
        
        $formsql .= " AND uhs.examdate !=0 AND uhs.examdate < UNIX_TIMESTAMP() AND ep.passinggrade <= gg.finalgrade";
        
        $selectsql = "SELECT DISTINCT ee.id enrolid, u.id userid, lc.id_number, $fullname, le.id examid, $examname $tcicode h.name, DATE_FORMAT(FROM_UNIXTIME(uhs.examdate), '%d %M %Y') examdate, DATE_FORMAT(FROM_UNIXTIME(hs.starttime), '%H:%i') examtime, uhs.hallscheduleid
            FROM {user} u
            JOIN {local_users} lc ON lc.userid = u.id AND lc.id_number <> ''
            JOIN {exam_enrollments} ee ON ee.userid = u.id AND u.deleted = 0 AND u.suspended = 0
            JOIN {local_exams} le ON le.id = ee.examid
            JOIN {local_exam_userhallschedules} uhs ON uhs.userid = ee.userid AND uhs.examid = le.id AND uhs.hallscheduleid != 0 
            $join
            LEFT JOIN {local_exam_profiles} ep ON ep.id = uhs.profileid 
            LEFT JOIN {grade_items} gi ON gi.courseid = le.courseid AND gi.iteminstance = ep.quizid 
            LEFT JOIN {grade_grades} gg ON gg.itemid = gi.id AND gg.userid = uhs.userid 
            LEFT JOIN {hallschedule} hs ON hs.id = uhs.hallscheduleid
            LEFT JOIN {hall} h ON h.id = hs.hallid
            
            WHERE 1=1 ";
            $formsql .= " GROUP BY ee.id ORDER BY enrolid DESC ";
            // print_r($selectsql.$formsql);die;
        $users = $DB->get_records_sql($selectsql.$formsql, [], $stable->start,$stable->length);
        $userscount = count($DB->get_records_sql($selectsql.$formsql));
        $template_records = certificate_manager::get_templates_for_dropdown();
        
        $templates = [];
        $defaultcategory = '';
        foreach ($template_records as $temps) {
            $templates[$temps->id] = $temps->name;

            if($temps->categoryname == 'Exam'){
                $defaulTemplateid= $temps->id;
            }
        }
        return ['users' => $users, 'totalcount' => $userscount, 'templateid' => $defaulTemplateid];
    }
    /**
     * This function will list the users enrolled in the exams site wide
     * @return 
     */
    public function render_examusers(){
        global $PAGE;
        $context = context_system::instance();
        $renderer = $PAGE->get_renderer('local_exams');
        $filterparams = $renderer->get_catelog_examusers(true);
        $filterparams['submitid'] = 'form#filteringform';
        $filterparams['widthclass'] = 'col-md-12';
        $filterparams['inputclasses'] = 'searchinput';
        $filterparams['globalinput'] = $renderer->global_filter($filterparams);
        $filterparams['user_certificate_details'] = $renderer->get_catelog_examusers();
        $fform = exam_certificate_filters_form($filterparams);
        $filterparams['filterform'] = $fform->render();
        echo $renderer->display_exam_users($filterparams);
    }
    /**
     * certificate_fetch_filterdata
     * 
     */
    public function certificate_fetch_filterdata($query, $action) {
        global $DB;
        $formsql = '';
        $sql = "SELECT ";
        $lang = current_language();
        $where = '';
        $join2 = " JOIN {local_organization} lo ON lo.id = lc.organization ";
        $join = " FROM {user} u
            JOIN {local_users} lc ON lc.userid = u.id AND lc.id_number <> ''
            $join2
            JOIN {exam_enrollments} ee ON ee.userid = lc.userid
            LEFT JOIN {local_exam_userhallschedules} uhs ON uhs.userid = lc.userid AND uhs.hallscheduleid <> 0
            JOIN {local_exams} le ON le.id = ee.examid
            LEFT JOIN {tool_certificate_issues} tci ON tci.moduleid = ee.examid AND tci.moduletype = 'exams' AND tci.userid = lc.userid
            LEFT JOIN {hallschedule} hs ON hs.id = uhs.hallscheduleid
            LEFT JOIN {hall} h ON h.id = hs.hallid
            WHERE 1=1 ";

        switch ($action) {
            case 'fetch_id_number':
                if ($query) {
                    $where .= " AND lc.id_number LIKE '".$query."%'";
                }
                $sql .= "lc.id_number as id, lc.id_number as name ";
                // print_r($sql.$join.$where)
                $data = $DB->get_records_sql($sql.$join.$where);

                break;
            case 'fetch_organization':
                $fullname = ' lo.fullname ';
                if ($lang == 'ar') {
                    $fullname = ' lo.fullnameinarabic ';
                }
                if ($query) {
                    $where .= " AND $fullname LIKE '%".$query."%'";
                }
                
                $sql .= " lo.id, $fullname as name";
                $data = $DB->get_records_sql($sql.$join.$where);
                break;
            case 'fetch_exam':
                $name = ' le.exam ';
                if ($lang == 'ar') {
                    $name = ' le.examnamearabic ';
                }
                if ($query) {
                    $where .= " AND $name LIKE '%".$query."%'";
                }
                $sql .= " le.id, $name as name ";
                $data = $DB->get_records_sql($sql.$join.$where);
                break;
            case 'fetch_exam_center':
                if ($query) {
                    $where .= " AND h.name LIKE '%".$query."%'";
                }
                $sql .= " h.id, h.name ";
                $data = $DB->get_records_sql($sql.$join.$where);
                break;
        }
        return $data;
    }



    public function get_bulkenrolprogram_users($userid,$lang){

        global $DB,$OUTPUT,$PAGE;

        $systemcontext = context_system::instance();
        $PAGE->set_context($systemcontext);
        $userdata =array();
        $count = 0;
        foreach($userid as $userids){
            $userdetails = $DB->get_record('local_users',array('userid'=>$userids));
            $userdata[$count]['fullname'] = $userdetails->fullname =  ($lang == 'ar') ? $userdetails->firstnamearabic . " ".$userdetails->lastnamearabic." ". $userdetails->middlenamearabic ." ".  $userdetails->thirdnamearabic : $userdetails->firstname . " ".$userdetails->lastname ." ". $userdetails->middlenameen . " ". $userdetails->thirdnameen;
            $userdata[$count]['idnumber'] = $userdetails->id_number;

            $count++;   
        }
        $data = [
            'userdata'=>$userdata,
            'fnheading' => ($lang == 'ar') ? 'الاسم الكامل': 'FullName' ,
            'idheading' => ($lang == 'ar') ? ' رقم الهوية' : 'Id Number'
        ];
      return $OUTPUT->render_from_template('local_exams/bulkenroll_notify',$data);
    }


    public function bulkenrolprogram_notification($orgofficial,$userids,$tpid,$tpoid){
        global $DB,$USER;
        $orgoff  = $DB->get_record('local_users',array('userid'=>$orgofficial)); 
        $tp =  $DB->get_record('local_trainingprogram',array('id'=>$tpid)); 
       
        $tpoffering = $DB->get_record('tp_offerings',array('id'=>$tpoid));
        $tpdata = new stdClass();
        
        if($tpoffering->startdate != 0 && $tpoffering->enddate != 0){
            $tpdata->offering_startdate = userdate($tpoffering->startdate,'%d-%m-%Y');
            $tpdata->offering_enddate = userdate($tpoffering->enddate,'%d-%m-%Y');
        }

        $starttimemeridian = gmdate('a',$tpoffering->time);
        $endtimemeridian = gmdate('a',$tpoffering->endtime);
        if($orgoff->lang == 'ar')  {
                $tpdata->org_off = $orgoff->firstnamearabic .' '. $orgoff->middlenamearabic.' '. $orgoff->thirdnamearabic.' '. $orgoff->lastnamearabic;
                $tpdata->program_name = $tp->namearabic;
                $startmeridian = ($starttimemeridian == 'am')? 'صباحًا' : 'مساءً';
                $endtimemeridian = ($endtimemeridian == 'am')? 'صباحًا' : 'مساءً';
                $lang = 'ar';

        } else{
                $tpdata->org_off = $orgoff->firstname .' '. $orgoff->middlenameen.' '. $orgoff->thirdnameen.' '. $orgoff->lastname;
                $tpdata->program_name =  $tp->name;
                $startmeridian = ($starttimemeridian == 'am')? 'AM': 'PM';
                $endtimemeridian = ($endtimemeridian == 'am')? 'AM': 'PM';
                $lang = 'en';

        }
        if( $tpoffering->time !=0 && $tpoffering->endtime !=0){
            $tpdata->offering_starttime =  gmdate("H:i",$tpoffering->time) . ' '.$startmeridian ; 
            $tpdata->offering_endtime =  gmdate("H:i",$tpoffering->endtime) . ' '. $endtimemeridian  ; 

        }
        $tpdata->trainee_details = $this->get_bulkenrolprogram_users($userids,$lang);  
        $touser = $DB->get_record('user',array('id'=>$orgofficial));                 

        (new \local_trainingprogram\notification())->trainingprogram_notification('bulkenrol_program', $touser = $touser,$fromuser = $USER,$tpdata,$waitinglistid=0);






    }
    public function bulkenrolexam_notification($orgofficial,$userids,$examid,$profileid, $hallscheduleid){

        global $DB,$USER;
        $orgoff  = $DB->get_record('local_users',array('userid'=>$orgofficial)); 
        $examdetails = $DB->get_record('local_exams',array('id'=>$examid)); 
        $hallschedule = $DB->get_record('hallschedule',array('id'=>$hallscheduleid));
        $examdata = new stdClass();

        $examdata->exam_startdate = userdate($hallschedule->startdate, '%d-%m-%Y');
        // $starttime = userdate($hallschedule->starttime,'%H:%M %P');
        // $endttime = userdate($hallschedule->endtime,'%H:%M %P');


        $starttimemeridian = gmdate('a',$hallschedule->starttime);
        $endtimemeridian = gmdate('a',$hallschedule->endtime);

        //  $examdata->exam_starttime = $starttime ;
        //  $examdata->exam_endtime = $endttime;


         if($orgoff->lang == 'ar')  {
            $examdata->org_off = $orgoff->firstnamearabic .' '. $orgoff->middlenamearabic.' '. $orgoff->thirdnamearabic.' '. $orgoff->lastnamearabic;
            $examdata->exam_name = $examdetails->examnamearabic;
            $startmeridian = ($starttimemeridian == 'am')? 'صباحًا' : 'مساءً';
            $endtimemeridian = ($endtimemeridian == 'am')? 'صباحًا' : 'مساءً';
            $lang = 'ar';

        } else{
            $examdata->org_off= $orgoff->firstname .' '. $orgoff->middlenameen.' '. $orgoff->thirdnameen.' '. $orgoff->lastname;
            $examdata->exam_name =  $examdetails->exam;
                $startmeridian = ($starttimemeridian == 'am')? 'AM': 'PM';
                $endtimemeridian = ($endtimemeridian == 'am')? 'AM': 'PM';
                $lang = 'en';

        }
        $examdata->exam_starttime =  gmdate("H:i",$hallschedule->starttime) . ' '.$startmeridian ; 
        $examdata->exam_endtime =  gmdate("H:i",$hallschedule->endtime) . ' '. $endtimemeridian  ; 
        $examdata->trainee_details = $this->get_bulkenrolprogram_users($userids,$lang);  
        $touser = $DB->get_record('user',array('id'=>$orgofficial));        
            

        (new \local_exams\notification())->exams_notification('bulkenrol_exam', $touser = $touser,$fromuser = $USER,$examdata,$waitinglistid=0);

        
    }

    public function access_fast_service($type) {
        $endata = get_config('local_exams', 'fastsettings');
        $decode = json_decode($endata);
        if ($decode->replacefast || $decode->$type) {
            return $decode->$type;
        }

        return false;
    }


    public function grieviance_gradestatus($scheduleid) {
        global $DB, $USER;
        $schedule = $DB->get_record('local_exam_userhallschedules', ['id'=>$scheduleid]);
        $profile = $DB->get_record('local_exam_profiles', ['id'=>$schedule->profileid]);
        if ($schedule->attemptid == 0) {
            $attempt = 1;
        } else {
            $examattemptid = $DB->get_field('local_exam_attempts', 'attemptid', ['id'=>$schedule->attemptid]);
            $attempt = $examattemptid;
        }       

        $scheduledetails = $DB->get_record('hallschedule', ['id'=>$schedule->hallscheduleid]);
        $schedulestarttime = $scheduledetails->starttime;
        $startedtime = (strtotime(userdate($schedule->examdate, '%d-%m-%Y'))+userdate((($schedulestarttime)), '%H')*3600 + userdate(($schedulestarttime), '%M')*60);

        $sql = "SELECT *
                  FROM {local_exam_userhallschedules} 
                 WHERE examid = {$schedule->examid} AND userid = {$schedule->userid} 
                 ORDER BY id ASC ";
        $userschedules = $DB->get_records_sql($sql);
        $i=0;
        foreach($userschedules as $userschedule) {
            $userattempt = $userschedule->attemptid;
            $usercurrentattempt = $schedule->attemptid;
            $attempt = ++$i;
            if ($userattempt == $usercurrentattempt) {
                break;
            }
        }
        $profile->quizid = $profile->quizid ? $profile->quizid : 0;
        $sql = "SELECT qa.* 
                FROM {quiz_attempts} qa 
                WHERE qa.quiz = {$profile->quizid} AND qa.attempt = {$attempt} AND qa.userid =". $schedule->userid;
        $quizattempt = $DB->get_record_sql($sql);

        if ($quizattempt) {
            // if($quizattempt->sumgrades == -1) {
            //     $gradestatus = get_string('absent', 'local_exams');
            // } elseif($quizattempt->sumgrades == -2) {
            //     $gradestatus = get_string('unknow', 'local_exams');
            // } else {
            //     $gradestatus = ROUND($quizattempt->sumgrades, 2);
            // }
            if($quizattempt->sumgrades <= 0){
                $gradestatus = true;
            }
        } elseif(time() < $startedtime) {
            $gradestatus = false; //get_string('notstarted', 'local_exams');
        } else {
            $gradestatus = false; //get_string('unknow', 'local_exams');
        }
        return $gradestatus;
    }
}                                                                  
