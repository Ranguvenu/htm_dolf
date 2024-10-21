<?php
namespace local_exams\output;
use local_exams\local\exams;

use plugin_renderer_base;
use context_system;
use moodle_url;

class renderer extends plugin_renderer_base {

    function render_exams($page)
    {
        $data = $page->export_for_template($this);
        return parent::render_from_template('local_trainingprogram/mainpage', $data);         
    }
    function render_examoff($page)
    {
        $data = $page->export_for_template($this);
        return parent::render_from_template('local_trainingprogram/mainpage', $data);         
    }
    public function action_btn($examid=NULL) {
        global $DB, $PAGE, $OUTPUT;
        $systemcontext = context_system::instance();
        if (has_capability('local/exams:create', $systemcontext) || has_capability('local/organization:manage_examofficial', $systemcontext) || has_capability('local/organization:manage_trainingofficial', $systemcontext)) {
            if($examid > 0) {
                $data['examdetailsactions'] = true;
                $data['examid'] = $examid;
                $questionbankurl = new moodle_url('/local/exams/questionbank.php', array('id' =>$examid));
                $data['questionbankurl']=$questionbankurl->out();
                $header_btns = $this->render_from_template('local_exams/createexam', $data);
                $actionbtns = $PAGE->add_header_action($header_btns);
            } else {
                $data['examdetailsactions'] = false;
               $header_btns = $this->render_from_template('local_exams/createexam', $data);
               $actionbtns = $PAGE->add_header_action($header_btns);
            }
            return true;
        } else {
            return false;
        }
    }
    public function action_examattempts($examid=NULL) {
        global $DB, $PAGE, $OUTPUT;
        $systemcontext = context_system::instance();
        $data['examdetailsactions'] = true;
        $data['examattempts'] = true;
        $data['examid'] = $examid;
        $questionbankurl = new moodle_url('/local/exams/questionbank.php', array('id' =>$examid));
        $data['questionbankurl']=$questionbankurl->out();
        $header_btns = $this->render_from_template('local_exams/createexam', $data);
        $actionbtns = $PAGE->add_header_action($header_btns);

        return true;
    }

    public function global_filter($filterparams) {
        global $DB, $PAGE, $OUTPUT;
        return $this->render_from_template('theme_academy/global_filter', $filterparams);
    }
    public function exam_info($data) {
        global $DB, $PAGE, $OUTPUT, $CFG;
        $org = $this->render_from_template('local_exams/exam_view', $data);
        return $org;
    }
    public function sectors_info($data) {
        global $DB, $PAGE, $OUTPUT, $CFG;
        $org = $this->render_from_template('local_exams/sectors_view', array('sectors' => $data));
        return $org;
    }
    public function userattempts($data) {
        echo $this->render_from_template('local_exams/userattempts', array('attempts' => $data));
    }
    public function get_catalog_reviewexams($filter = false) {
        $systemcontext = context_system::instance();
        $options = array('targetID' => 'manage_reviewexams','perPage' => 5, 'cardClass' => 'col-md-6 col-12', 'viewType' => 'card');
        $options['methodName']='local_reviewexams_view';
        $options['templateName']='local_exams/reviewexams';
        $options = json_encode($options);
        $filterdata = json_encode(array());
        $dataoptions = json_encode(array('contextid' => $systemcontext->id));
        $context = [
                'targetID' => 'manage_reviewexams',
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
    public function get_catalog_publishexams($filter = false) {
        $systemcontext = context_system::instance();
        $options = array('targetID' => 'exams_wrapper','perPage' => 5, 'cardClass' => 'col-md-6 col-12', 'viewType' => 'card');
        $options['methodName']='local_exams_view';
        $options['templateName']='local_exams/scheduledexams';
        $options = json_encode($options);
        $filterdata = json_encode(array());
        $dataoptions = json_encode(array('contextid' => $systemcontext->id));
        $context = [
                'targetID' => 'exams_wrapper',
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
    public function get_catalog_userexams($filter = false) {
        $systemcontext = context_system::instance();
        $options = array('targetID' => 'exams_wrapper','perPage' => 5, 'cardClass' => 'col-md-6 col-12', 'viewType' => 'card');
        $options['methodName']='local_exams_view';
        $options['templateName']='local_exams/userblock';
        $options = json_encode($options);
        $filterdata = json_encode(array());
        $dataoptions = json_encode(array('contextid' => $systemcontext->id));
        $context = [
                'targetID' => 'exams_wrapper',
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
    public function examstabs() {
        echo $this->render_from_template('local_exams/examstabs', null);
    }
    public function examdetails($exam) {
        $systemcontext = context_system::instance();
        if( (!is_siteadmin() && has_capability('local/organization:manage_organizationofficial', $systemcontext)) || (!is_siteadmin() && (has_capability('local/organization:manage_communication_officer', $systemcontext) || has_capability('local/organization:manage_trainee', $systemcontext)) )){

            $exam['orgoff'] = false;

        } else {

            $exam['orgoff'] = true;

        }
        echo $this->render_from_template('local_exams/examdetails', $exam);
    }
    public function examreservations($exam) {
        $systemcontext = context_system::instance();
        $examreservation['examhallreservation'] = $exam;
        if( is_siteadmin() || has_capability('local/organization:manage_examofficial', $systemcontext) ) {
            $examreservation['examselectbtn'] = true;
        } else if(has_capability('local/organization:manage_organizationofficial', $systemcontext)) {
            $examreservation['exambooknowbtn'] = true;
        }
        echo $this->render_from_template('local_exams/examreservations', $examreservation);
    }
    public function competencydetails($data) {
        return $this->render_from_template('local_exams/competencies_view', ['comptencies' => $data]);
    }
    public function get_catalog_competencies($filter = false) {
        $systemcontext = context_system::instance();
        $options = array('targetID' => 'manage_competencies','perPage' => 10, 'cardClass' => 'col-md-6 col-12', 'viewType' => 'card');
        $options['methodName']='local_competeny_info';
        $options['templateName']='local_exams/competencies_view';
        $options = json_encode($options);
        $filterdata = json_encode(array());
        $dataoptions = json_encode(array('contextid' => $systemcontext->id));
        $context = [
                'targetID' => 'manage_competencies',
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
    public function get_catalog_reservations($filter = false) {
        $examid = optional_param('id', 0, PARAM_INT);
        $reservationid = optional_param('reservationid', 0 , PARAM_INT);

        $systemcontext = context_system::instance();
        $options = array('targetID' => 'manage_reservations','perPage' => 5, 'cardClass' => 'col-md-6 col-12', 'viewType' => 'card');
        $options['methodName']='local_exam_reservations';
        $options['templateName']='local_exams/examreservations';
        $options = json_encode($options);
        $filterdata = json_encode(array());
        $dataoptions = json_encode(array('examid' => $examid, 'contextid' => $systemcontext->id, 'reservationid' => $reservationid));
        $context = [
                'targetID' => 'manage_reservations',
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

    public function examenrollment($from_userstotal, $from_users, $to_userstotal, $to_users, $myJSON, $exam_id,$availableseats,$examprice, $profileid, $showusers, $orgid) {
        global $DB, $PAGE, $OUTPUT, $CFG;
        $systemcontext = context_system::instance(); 
        $orgofficial = (!is_siteadmin() && has_capability('local/organization:manage_organizationofficial', $systemcontext)) ? true: false;
        $bulkenrollment =(is_siteadmin() || has_capability('local/organization:manage_examofficial', $systemcontext) || has_capability('local/organization:manage_organizationofficial', $systemcontext)) ? true: false;
        $sql = "SELECT tp.id productid, ex.exam examname, ex.ownedby, ex.sellingprice productprice
            FROM {local_exams} ex
            JOIN {local_exam_profiles} ep ON ep.examid = ex.id
            JOIN {tool_products} tp ON tp.referenceid = ep.id
            WHERE ex.id = $exam_id AND tp.referenceid = $profileid AND tp.category = 2
        ";
        $examrecords = (array)$DB->get_record_sql($sql);
        $dataarray = array(
            'from_userstotal' => $from_userstotal, 
            'from_users' => $from_users, 
            'to_userstotal' => $to_userstotal, 
            'to_users' => $to_users,
            'orgid' => $orgid,
            'myJSON' => $myJSON, 
            'exam_id' => $exam_id,
            'availableseats'=>$availableseats,
            'examprice'=>$examprice, 
            'profileid' => $profileid,
            'orgofficial'=>$orgofficial,
            'refundtype' => 0,
            'showusers' => $showusers,
            'entitytype' => 'exam',
            'bulkenrollment'=>$bulkenrollment
        );
        $finaldata = array_merge($dataarray, $examrecords);
        echo $this->render_from_template('local_exams/examenrollment', $finaldata);
    }
    /**
     * Display the competency
     * @return string The text to render
     */
    public function get_questionbanks_exams($examsinfo) {
        global $DB,$CFG, $OUTPUT,$PAGE,$USER;
        $return = '';
        $context = context_system::instance();

        $stable = new \stdClass();
        $stable->thead = false;
        $stable->start = 0;
        $stable->length = -1;
        $stable->examid=$examsinfo->id;
        $stable->courseid=$examsinfo->courseid;
        $stable->noofquestions=$examsinfo->noofquestions;
        $stable->quizid=$examsinfo->quizid;
        $getquestionbanks = (new \local_exams\local\exams)->get_questionbanks_exams($stable,$filterdata=null);        
        if($data=$getquestionbanks['questionbanks']){
            $return .=$OUTPUT->render_from_template('local_exams/listquestionbankmaps',array('records'=>array_values((array)$data),'examid'=>$stable->examid,'examnoofquestions'=>$stable->noofquestions,'mappedquestionbankscount'=>$getquestionbanks['mappedquestionbankscount']));
        } else {
            $return .="<div class='alert alert-danger'>" . get_string('noexamquestionbanks', 'local_exams') . "</div>";
        }
        return $return;
    }        
    
    public function get_catalog_exam_qualification($filter = false, $searchquery='') {
        $systemcontext = context_system::instance();
        $options = array('targetID' => 'viewexamqualification','perPage' => 6, 'cardClass' => 'col-md-6 col-12', 'viewType' => 'card');
        $options['methodName'] ='local_exams_exam_qualifications';
        //$options['templateName'] ='local_exams/exams_qualifications_cards';
        $options['templateName'] ='local_exams/exam_card';
        $options = json_encode($options);
        $filterdata = json_encode(['q' => $searchquery]);
        $dataoptions = json_encode(array('contextid' => $systemcontext->id, 'append' => 1));
        $context = [
                'targetID' => 'viewexamqualification',
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

    public function get_catalog_userreservations($filter = false, $searchquery='') {
        $examid = optional_param('id', 0, PARAM_INT);
        $systemcontext = context_system::instance();
        $options = array('targetID' => 'manage_userreservations','perPage' => 5, 'cardClass' => 'col-md-6 col-12', 'viewType' => 'card');
        $options['methodName']='local_exam_userreservations';
        $options['templateName']='local_exams/examreservations';
        $options = json_encode($options);
        $filterdata = json_encode(['q' => $searchquery]);
        $dataoptions = json_encode(array('examid' => $examid, 'contextid' => $systemcontext->id ));
        $context = [
                'targetID' => 'manage_userreservations',
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

    public function listofexamqualification($filterparams) {
        global $DB, $PAGE, $OUTPUT;
       // echo $this->render_from_template('local_exams/exams_qualifications_list', $filterparams);
       echo $this->render_from_template('local_exams/exams_front', $filterparams);
    }

    public function listofreservations($filterparams) {
        global $DB, $PAGE, $OUTPUT;
        return $this->render_from_template('local_exams/listofreservations', $filterparams);
    }

    /**
     * List Exam reservations
     * 
     */
    public function get_catalog_schedules($filter = false, $filterdata = false) {
        global $DB, $PAGE, $OUTPUT;
        $examid = optional_param('id', 0, PARAM_INT);
        $systemcontext = context_system::instance();
        $options = array('targetID' => 'view_exam_schedules','perPage' => 10, 'cardClass' => 'col-md-6 col-12', 'viewType' => 'card');
        $options['methodName']='local_exams_view_exam_schedules';
        $options['templateName']='local_exams/listschedules';

        $options = json_encode($options);
        $dataoptions = json_encode(array('examid' => $examid, 'contextid' => $systemcontext->id, 'append' => 1));
        $filterdata = json_encode($filterdata);
        $context = [
                'targetID' => 'view_exam_schedules',
                'options' => $options,
                'dataoptions' => $dataoptions,
                'filterdata' => $filterdata,
        ];
        if ($filter) {
            return $context;
        }
       return $this->render_from_template('theme_academy/cardPaginate', $context);
    }
    /**
     * List Exam reservations
     * 
     */
    public function listexam_schedules($filterdata) {
        
        return $this->render_from_template('local_exams/exam_schedules', $filterdata);
    }
    
    public function listofpublishedexams($filterparams) {
        global $DB, $PAGE, $OUTPUT;
        $context = context_system::instance();
        if($filterparams['examid'] > 0) {

            $filterparams['examdetailsactions'] = true;
            $filterparams['examid'] = $filterparams['examid'];
            $filterparams['examattempts'] = false;
            
            $filterparams['profileid'] = $filterparams['profileid'];
            $questionbankurl = new moodle_url('/local/exams/questionbank.php', array('id' =>$examid));
            $filterparams['questionbankurl']=$questionbankurl->out();
        } else {
            $filterparams['examdetailsactions'] = false;
            $filterparams['canissuecertificate'] = has_capability('local/exams:canissuecertificate', $context) ? true : false;
        }
        return $this->render_from_template('local_exams/listofpublishedexams', $filterparams);
    }


    public function exam_qualification_details($data) {
        global $DB, $PAGE, $OUTPUT;
        echo $this->render_from_template('local_exams/exam_qualification_details', $data);
    }
    public function hall_data($data) {
        global $DB, $PAGE, $OUTPUT, $CFG;
        $hall = $this->render_from_template('local_exams/hallreservation', ['hallinfo' => $data]);
        return $hall;
    }
    //Vinod - Exams fake block for exam official - Starts//

    public function all_exams_block($filter = false) {
        $systemcontext = context_system::instance();
        $options = array('targetID' => 'manage_exams_block','perPage' => 3, 'cardClass' => 'col-md-6 col-12', 'viewType' => 'card');
        $options['methodName']='local_exams_manage_examofficial_block';
        $options['templateName']='local_exams/exams_block';
        $options = json_encode($options);
        $filterdata = json_encode(array());
        $dataoptions = json_encode(array('contextid' => $systemcontext->id));
        $context = [
                'targetID' => 'manage_exams_block',
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
    public function listofexams_block_data($filterparams) {
        global $DB, $PAGE, $OUTPUT;
        return $this->render_from_template('local_exams/listofexams_block_data', $filterparams);
    }
    //Vinod - Exams fake block for exam official - Ends//

    // APARNA grievance_details
    public function grievance_details($data) {
        global $DB, $PAGE, $OUTPUT, $CFG;
        return $this->render_from_template('local_exams/grievance_details', $data);
    }


    public function listofexamusers($filterparams) 
    {
        global $DB, $PAGE, $OUTPUT;   
        echo  $this->render_from_template('local_exams/listofexamusers', $filterparams);
    }

    public function get_catalog_examusers($filter = false, $searchquery='') {
        $examid = optional_param('id', 0, PARAM_INT);
        $profileid = optional_param('profileid', 0, PARAM_INT);
        $systemcontext = context_system::instance();
        $options = array('targetID' => 'viewexamusers','perPage' => 10, 'cardClass' => 'col-md-6 col-12', 'viewType' => 'card');
        $options['methodName'] ='local_viewexamusers';
        $options['templateName'] ='local_exams/examusers';
        $options = json_encode($options);
        $filterdata = json_encode(['q' => $searchquery]);
        $dataoptions = json_encode(array('examid' => $examid,'profileid' => $profileid, 'contextid' => $systemcontext->id, 'append' => 1));
        $context = [
                'targetID' => 'viewexamusers',
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

    public function get_catalog_profiles($filter = false) {
        $examid = optional_param('id', 0, PARAM_INT);
        $profileid = optional_param('profileid', 0, PARAM_INT);
        $hallscheduleid = optional_param('hallscheduleid', 0, PARAM_INT);
        $tuserid = optional_param('tuserid', 0, PARAM_INT);
        $systemcontext = context_system::instance();
        $options = array('targetID' => 'exams_profiles','perPage' => 5, 'cardClass' => 'col-md-6 col-12', 'viewType' => 'card');
        $options['methodName']='local_exams_profiles';
        $options['templateName']='local_exams/profiles';
        $options = json_encode($options);
        $filterdata = json_encode(array());
        $dataoptions = json_encode(array('examid' => $examid, 'profileid' => $profileid, 'hallscheduleid' => $hallscheduleid, 'contextid' => $systemcontext->id, 'tuserid'=>$tuserid));
        $context = [
                'targetID' => 'exams_profiles',
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
    public function exam_profileinfo($data) {
        return $this->render_from_template('local_exams/profileview', $data);
    }
    public function get_catalog_hallschedules($filter = false) {
        global $PAGE;
        $examidpurchase = optional_param('examid', 0, PARAM_INT);
        $profileid = optional_param('profileid', 0, PARAM_INT);
        $examidschedule = optional_param('id', 0, PARAM_INT);
        $status = optional_param('status', 'pr', PARAM_RAW);
        $examid = ($examidpurchase > 0) ? $examidpurchase : (($examidschedule > 0) ? $examidschedule : 0);
        $type = optional_param('type','',PARAM_RAW);
        $tuserid = optional_param('tuserid', 0, PARAM_RAW);
        $damount = optional_param('damount', 0, PARAM_RAW);
        $organization = optional_param('organization', 0, PARAM_INT);
        $orgofficial = optional_param('orgofficial', 0, PARAM_INT);
        $systemcontext = context_system::instance();
        $options = array('targetID' => 'hall_schedules','perPage' => 5, 'cardClass' => 'col-md-6 col-12', 'viewType' => 'card');
        $options['methodName']='local_exams_hall_schedules';
        $options['templateName']='local_exams/hallschedules';
        $options = json_encode($options);
        $filterdata = json_encode(array());
        $dataoptions = json_encode(array('contextid' => $systemcontext->id, 'examid' => $examid,'type' =>$type, 'tuserid' => $tuserid, 'profileid'=>$profileid, 'status'=>$status, 'damount'=>$damount,'organization'=>$organization,'orgofficial'=>$orgofficial));
        
        $context = [
                'targetID' => 'hall_schedules',
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
    public function get_catalog_examattempts($filter = false) {
        $examid = optional_param('id', 0, PARAM_INT);
        $systemcontext = context_system::instance();
        $options = array('targetID' => 'manage_examattempts','perPage' => 5, 'cardClass' => 'col-md-6 col-12', 'viewType' => 'card');
        $options['methodName']='local_exams_attempts';
        $options['templateName']='local_exams/attempts';
        $options = json_encode($options);
        $filterdata = json_encode(array());
        $dataoptions = json_encode(array('examid' => $examid, 'contextid' => $systemcontext->id));
        $context = [
                'targetID' => 'manage_examattempts',
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

     public function get_catalog_fast_examenrol($filter = false) {
        $systemcontext = context_system::instance();
        $options = array('targetID' => 'manage_examenrol','perPage' => 10, 'cardClass' => 'col-md-6 col-12', 'viewType' => 'card');
        $options['methodName']='local_fast_examenrolview';
        $options['templateName']='local_exams/fastexamenrol';

        $options = json_encode($options);
        $filterdata = json_encode(array());
        $dataoptions = json_encode(array('contextid' => $systemcontext->id));
        $context = [
                'targetID' => 'manage_examenrol',
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

    public function listoffexamenrol($filterparams) {
        global $DB, $PAGE, $OUTPUT;
       
        echo  $this->render_from_template('local_exams/listofexamenrol', $filterparams);
    }   

    public function listofreviewmodeexams($filterparams) {
        global $DB, $PAGE, $OUTPUT;
        return $this->render_from_template('local_exams/listofreviewmodeexams', $filterparams);
    }  
    
    public function get_enrollmentconfirmationlist($filter = false) {
        $systemcontext = context_system::instance();
        $examid = optional_param('examid','',PARAM_INT);
        $profileid = optional_param('profileid','',PARAM_INT);
        $organization = optional_param('organization','',PARAM_INT);
        $orgofficial = optional_param('orgofficial','',PARAM_INT);
        $cusers = optional_param('cusers','',PARAM_RAW);
        $scheduleid = optional_param('scheduleid','',PARAM_RAW);
        $systemcontext = context_system::instance();
        $options = array('targetID' => 'manage_enrollmentconfirmations','perPage' => 5, 'cardClass' => 'col-md-6 col-12', 'viewType' => 'card');
        $options['methodName']='local_exams_enrollmentconfirmations';
        $options['templateName']='local_exams/enrollmentconfirmations';
        $options = json_encode($options);
        $filterdata = json_encode(array());
        $dataoptions = json_encode(array('contextid' => $systemcontext->id, 'examid' => $examid,'profileid' => $profileid,'organization'=>$organization,'orgofficial'=>$orgofficial,'cusers' => $cusers,'scheduleid'=>$scheduleid));
        $context = [
                'targetID' => 'manage_enrollmentconfirmations',
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
    public function get_catelog_reservations($filter = false, $filterparams = null) {
        $examid = optional_param('id', 0, PARAM_INT);
         $systemcontext = context_system::instance();
        $options = array('targetID' => 'view_reservations','perPage' => 10, 'cardClass' => 'col-md-6 col-12', 'viewType' => 'card');
        $options['methodName']='local_exam_view_reservations';
        $options['templateName']='local_exams/exam_list_reservations';

        $options = json_encode($options);
        $filterdata = json_encode(array());
        $dataoptions = json_encode(array('examid' => $examid, 'contextid' => $systemcontext->id));
        $context = [
                'targetID' => 'view_reservations',
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

    public function listofenrollmentconfirmation($filterparams) {
        global $DB, $PAGE, $OUTPUT,$CFG;
        $systemcontext = context_system::instance();
        echo $this->render_from_template('local_exams/listofenrollmentconfirmation', $filterparams);
    }
    /**
     * View Exam Reservations
     * 
     */
    public function display_reservations($filterparams) {
        return  $this->render_from_template('local_exams/exam_reservations', $filterparams);
    }
    /**
     * Render users to issue certificates
     * 
     */
    public function get_catelog_examusers($filter = false) {
        $systemcontext = context_system::instance();
        $options = array('targetID' => 'issuecertificates','perPage' => 10, 'cardClass' => 'col-md-6 col-12', 'viewType' => 'card');
        $options['methodName']='local_exam_get_exam_users';
        $options['templateName']='local_exams/issuecertificates';
        $options = json_encode($options);
        $filterdata = json_encode(array());
        $dataoptions = json_encode(array('contextid' => $systemcontext->id));
        $context = [
                'targetID' => 'issuecertificates',
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
    public function display_exam_users($filterparams) {
        return  $this->render_from_template('local_exams/issue_exam_certificates', $filterparams);
    }
}
