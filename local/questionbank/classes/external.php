<?php
defined('MOODLE_INTERNAL') || die;
require_once($CFG->libdir.'/externallib.php');
require_once($CFG->libdir.'/filelib.php');
require_once($CFG->dirroot.'/local/questionbank/lib.php');
class local_questionbank_external extends external_api {
    public static function questionbankview_parameters() {
        return new external_function_parameters([
            'status' => new external_value(PARAM_RAW, 'The paging data for the service', VALUE_DEFAULT, 0),
            'options' => new external_value(PARAM_RAW, 'The paging data for the service', VALUE_OPTIONAL),
            'dataoptions' => new external_value(PARAM_RAW, 'The data for the service', VALUE_OPTIONAL),
            'offset' => new external_value(PARAM_INT, 'Number of items to skip from the begging of the result set',
                VALUE_DEFAULT, 0),
            'limit' => new external_value(PARAM_INT, 'Maximum number of results to return',
                VALUE_DEFAULT, 0),
            'contextid' => new external_value(PARAM_INT, 'contextid', VALUE_OPTIONAL),
            'filterdata' => new external_value(PARAM_RAW, 'filters applied', VALUE_OPTIONAL),
        ]);
    }
    public static function questionbankview($status=false, $options=false, $dataoptions=false, $offset = 0, $limit = 0, $contextid=1, $filterdata=false) {
        global $DB, $PAGE, $CFG;
        require_login();
        $context = context_system::instance();
        $PAGE->set_context($context);
        $params = self::validate_parameters(
            self::questionbankview_parameters(),
            [
                'options' => $options,
                'dataoptions' => $dataoptions,
                'offset' => $offset,
                'limit' => $limit,
                'contextid' => $contextid,
                'filterdata' => $filterdata,
                'status' => $status,
            ]
        );
        $offset = $params['offset'];
        $limit = $params['limit'];
        $filtervalues = json_decode($filterdata);

        if ($status==1) {
            $filtervalues->status = 1;
        }

        $stable = new \stdClass();
        $stable->thead = false;
        $stable->start = $offset;
        $stable->length = $limit;
       
        $data = (new questionbank )->get_listof_questionbank($stable, $filtervalues);
        $totalcount = $data['totalquestionbank'];
        return [
            'totalcount' => $totalcount,
            'length' => $totalcount,
            'filterdata' => $filterdata,
            'records' =>$data,
            'options' => $options,
            'dataoptions' => $dataoptions,
            
        ];
    }
    public static function questionbankview_returns() {
        return new external_single_structure([
          'options' => new external_value(PARAM_RAW, 'The paging data for the service', VALUE_OPTIONAL),
          'dataoptions' => new external_value(PARAM_RAW, 'The data for the service', VALUE_OPTIONAL),
          'totalcount' => new external_value(PARAM_INT, 'total number of challenges in result set', VALUE_OPTIONAL),
          'filterdata' => new external_value(PARAM_RAW, 'total number of challenges in result set', VALUE_OPTIONAL),
          'length' => new external_value(PARAM_RAW, 'total number of challenges in result set', VALUE_OPTIONAL),
          'records' => new external_single_structure(
                  array(
                      'hascourses' => new external_multiple_structure(
                          new external_single_structure(
                              array(
                                  'id' => new external_value(PARAM_INT, 'id'),
                                  'workshopname' => new external_value(PARAM_TEXT, 'workshopname'),
                                   'noofquestions' => new external_value(PARAM_INT, 'noofquestions'),
                                  'workshopdate' => new external_value(PARAM_RAW, 'workshopdate'),
                                  'workshopdatetime' => new external_value(PARAM_INT, 'workshopdate'),
                                  'workshopenddate' => new external_value(PARAM_RAW, 'workshopenddate'),
                                  'workshopstarttime' => new external_value(PARAM_RAW, 'workshopstarttime'),
                                  'halladdress' => new external_value(PARAM_RAW, 'halladdress'),
                                  'qcategory' => new external_value(PARAM_RAW, 'qcategory'),
                                  'location' => new external_value(PARAM_RAW, 'location'),
                                  'actions' => new external_value(PARAM_RAW, 'actions',VALUE_OPTIONAL),
                                  'workshopadmin' => new external_value(PARAM_RAW, 'workshopadmin'),
                                  'qcategoryid' => new external_value(PARAM_RAW, 'qcategoryid'),
                                  'questionbank_workshop_url' => new external_value(PARAM_RAW, 'questionbank_workshop_url'),
                                  'questionbank_edit_url' => new external_value(PARAM_RAW, 'questionbank_edit_url'),
                                  'availableseats' => new external_value(PARAM_RAW, 'availableseats'),
                                  'qbstatus' => new external_value(PARAM_RAW, 'qbstatus',VALUE_OPTIONAL),
                                  'display_questions' => new external_value(PARAM_RAW, 'display_questions'),
                                  'ontime' => new external_value(PARAM_RAW, 'ontime',VALUE_OPTIONAL),
                                  'questionscount' => new external_value(PARAM_RAW, 'questionscount',VALUE_OPTIONAL),
                                  'coursescount' => new external_value(PARAM_RAW, 'coursescount',VALUE_OPTIONAL),
                                  'workshopstatus' => new external_value(PARAM_RAW, 'workshopstatus',VALUE_OPTIONAL),
                                  'locationstatus' => new external_value(PARAM_RAW, 'locationstatus',VALUE_OPTIONAL),
                                  'wcompleted' => new external_value(PARAM_RAW, 'wcompleted',VALUE_OPTIONAL), 
                                  'tocategory' => new external_value(PARAM_RAW, 'tocategory',VALUE_OPTIONAL),
                                  'generatecode' => new external_value(PARAM_RAW, 'generatecode',VALUE_OPTIONAL),                                    
                              )
                          )
                      ),
                      'request_view' => new external_value(PARAM_INT, 'request_view', VALUE_OPTIONAL),
                      'nocourses' => new external_value(PARAM_BOOL, 'nocourses', VALUE_OPTIONAL),
                      'totalquestionbank' => new external_value(PARAM_INT, 'totalquestionbank', VALUE_OPTIONAL),
                      'length' => new external_value(PARAM_INT, 'length', VALUE_OPTIONAL),
                  )
              )
        ]);
    }
     public static function competenciesinfo_parameters() {
        return new external_function_parameters([
                'options' => new external_value(PARAM_RAW, 'The paging data for the service'),
                'dataoptions' => new external_value(PARAM_RAW, 'The data for the service'),
                'offset' => new external_value(PARAM_INT, 'Number of items to skip from the begging of the result set',
                    VALUE_DEFAULT, 0),
                'limit' => new external_value(PARAM_INT, 'Maximum number of results to return',
                    VALUE_DEFAULT, 0),
                 'filterdata' => new external_value(PARAM_RAW, 'The data for the service'),
        ]);
    }
 
    public static function competenciesinfo($options, $dataoptions, $offset = 0, $limit = 0, $filterdata) {
        global $OUTPUT, $CFG, $DB,$USER,$PAGE;
        $sitecontext = context_system::instance();
        $PAGE->set_context($sitecontext);
        $params = self::validate_parameters(
            self::competenciesinfo_parameters(),
            [
                'options' => $options,
                'dataoptions' => $dataoptions,
                'offset' => $offset,
                'limit' => $limit,
                'filterdata' => $filterdata
            ]
        );
        $data_object = (json_decode($dataoptions));
        $offset = $params['offset'];
        $limit = $params['limit'];
        $filtervalues = json_decode($filterdata);
        $qbinfo =  $DB->get_field('local_questionbank', 'movedtoprod', ['id' => $filtervalues->workshopid]);
        $qbstatus = true;
        if($qbinfo == 1){
            $qbstatus = false;
        }
        $stable = new \stdClass();
        $stable->thead = true;
        $stable->start = $offset;
        $stable->length = $limit;
        $competencies = (new questionbank)->competency_data($stable,$filtervalues);
        return [
            'totalcount' => $competencies['totalcount'],
            'records' => $competencies['acompetencies'],
            'options' => $options,
            'dataoptions' => $dataoptions,
            'filterdata' => $filterdata,
            'workshopid' => $filtervalues->workshopid,
            'qbstatus' => $qbstatus,
            'nodata' => get_string('nocompetencys','local_competency')
        ];
    }

    /**
     * Returns description of method result value.
     */
    public static function  competenciesinfo_returns() {
        return new external_single_structure([
            'options' => new external_value(PARAM_RAW, 'The paging data for the service'),
            'workshopid' => new external_value(PARAM_INT, 'Workshop id'),
            'qbstatus' => new external_value(PARAM_RAW, 'qbstatus', VALUE_OPTIONAL),
            'dataoptions' => new external_value(PARAM_RAW, 'The data for the service'),
            'totalcount' => new external_value(PARAM_INT, 'total number of competencies in result set'),
            'filterdata' => new external_value(PARAM_RAW, 'The data for the service'),
            'nodata' => new external_value(PARAM_RAW, 'The data for the service'),
            'records' => new external_multiple_structure(
                new external_single_structure(
                    array(
                        'id' => new external_value(PARAM_INT, 'competency id'),
                        'name' => new external_value(PARAM_TEXT, 'competency name'),
                        'code' => new external_value(PARAM_TEXT, 'competency name'),
                        'type' => new external_value(PARAM_TEXT, 'competency type'),
                        'level' => new external_value(PARAM_RAW, 'competency level',VALUE_OPTIONAL)
                    )
                )
            ),
        ]);
    }
     public static function questiontopics_parameters() {
        return new external_function_parameters(
            array(
               'workshopid' => new external_value(PARAM_INT,'Workshop id',0),
               'questionid' => new external_value(PARAM_INT,'Question Id',0),
               'questionname' => new external_value(PARAM_TEXT,'Question Name',0),
            )
        );
    }
    public static function questiontopics($workshopid,$questionid,$questionname) {
        global $DB,$CFG, $PAGE;
        $PAGE->set_context(context_system::instance());
        $params = self::validate_parameters(self::questiontopics_parameters(),
        ['workshopid'=>$workshopid,'questionid'=>$questionid, 'questionname'=>$questionname]);
        // $data = (new questionbank )->viewquestiontopics($workshopid,$questionname,$questionid);
        $topics = (new questionbank )->getquestionmappedtopics($questionid, $workshopid);
        // print_r($topics);die;
        $data = (new questionbank )->render_topics($topics);
        return [
            'options' => $data,
        ];
    }
    public static function questiontopics_returns() {
        return new external_single_structure([
          'options' => new external_value(PARAM_RAW, 'The paging data for the service'),
        ]);
    } 

    public static function deletequestionbank_parameters(){
        return new external_function_parameters(
            array(
                'workshopid' => new external_value(PARAM_INT,'Workshop id',0),
                'workshopname' => new external_value(PARAM_TEXT,'Workshop name'),
            )
        );
    }
    public static  function deletequestionbank($workshopid, $workshopname){
        global $CFG;
        global $USER;
        global $DB;
        $params=self::validate_parameters(
            self::deletequestionbank_parameters(),
            array('workshopid'=>$workshopid, 'workshopname'=>$workshopname)
        );
        $systemcontext = context_system::instance();
        self::validate_context($systemcontext);
       if ($workshopid) {
            // notification Questionbank  onchange
          $questionbankname = $DB->get_record('local_questionbank', array('id'=>$workshopid));
          $sql="SELECT u.* FROM {user} u
          JOIN {local_qb_experts} le ON le.expertid = u.id
          WHERE le.questionbankid = $workshopid AND u.confirmed = 1 AND u.suspended = 0 AND u.deleted = 0 AND u.id > 2";
        $touser = $DB->get_records_sql($sql);
        if(!$touser)
        {
            $touser=null;
        }
         $row1=[];
         $row1['RelatedModuleName']=$questionbankname->workshopname;
         $myobject=(new \local_questionbank\notification);
         $myobject->questionbank_notification('questionbank_cancel',$touser, $USER,$row1,$waitinglistid=0);
            $deletequestionbankrecord= $DB->delete_records('local_questionbank', array('id' => $workshopid));
             // Trigger delete questionbank.
             $eventparams = array('context' => context_system::instance(),'objectid'=>$workshopid);
             $event = local_questionbank\event\delete_questionbank::create($eventparams);
             $event->trigger();
        } else {
          throw new moodle_exception('Error in submission');
        }
        return true;    
    }
    public static function deletequestionbank_returns() {
        return new external_value(PARAM_BOOL, 'return');
    } 

    public static function viewtopics_parameters() {
        return new external_function_parameters(
            array(
               'workshopid' => new external_value(PARAM_INT,'Workshop id',0),
               'workshopname' => new external_value(PARAM_TEXT,'Workshop name'),
            )
        );
    }
    public static function viewtopics($workshopid,$workshopname) {
        global $DB,$CFG;
        $params = self::validate_parameters(self::viewtopics_parameters(),
        ['workshopid'=>$workshopid, 'workshopname'=>$workshopname]);
        $data = (new questionbank )->viewcoursetopics($workshopid,$workshopname);
        return [
            'options' => $data,
        ];
    }
    public static function viewtopics_returns() {
        return new external_single_structure([
          'options' => new external_value(PARAM_RAW, 'The paging data for the service'),
        ]);
    }
     public static function viewcompetencies_parameters() {
        return new external_function_parameters(
            array(
               'workshopid' => new external_value(PARAM_INT,'Workshop id',0),
               'workshopname' => new external_value(PARAM_TEXT,'Workshop name'),
            )
        );
    }
    public static function viewcompetencies($workshopid,$workshopname) {
        global $DB,$CFG;
        $params = self::validate_parameters(self::viewtopics_parameters(),
        ['workshopid'=>$workshopid, 'workshopname'=>$workshopname]);
        $data = (new questionbank )->viewcompetencies($workshopid,$workshopname);
        return [
            'options' => $data,
        ];
    }
    public static function viewcompetencies_returns() {
        return new external_single_structure([
          'options' => new external_value(PARAM_RAW, 'The paging data for the service'),
        ]);
    }
    public static function questionbankviewexperts_parameters() {
        return new external_function_parameters([
            'options' => new external_value(PARAM_RAW, 'The paging data for the service'),
            'dataoptions' => new external_value(PARAM_RAW, 'The data for the service'),
            'offset' => new external_value(PARAM_INT, 'Number of items to skip from the begging of the result set',
                VALUE_DEFAULT, 0),
            'limit' => new external_value(PARAM_INT, 'Maximum number of results to return',
                VALUE_DEFAULT, 0),
             'filterdata' => new external_value(PARAM_RAW, 'The data for the service'),
    ]);
    }

    public static function questionbankviewexperts($options, $dataoptions, $offset = 0, $limit = 0, $filterdata) {
        global $OUTPUT, $CFG, $DB,$USER,$PAGE;
        $sitecontext = context_system::instance();
        $PAGE->set_context($sitecontext);
        $params = self::validate_parameters(
            self::questionbankviewexperts_parameters(),
            [
                'options' => $options,
                'dataoptions' => $dataoptions,
                'offset' => $offset,
                'limit' => $limit,
                'filterdata' => $filterdata
            ]
        );
        $data_object = (json_decode($dataoptions));
        $alloptions = json_decode($options);
        $offset = $params['offset'];
        $limit = $params['limit'];
        $filtervalues = json_decode($filterdata);
        $stable = new \stdClass();
        $stable->thead = true;
        $stable->workshopid = $alloptions->workshopid;
        $data = (new questionbank )->viewexperts($stable, $filtervalues);
        $totalcount = $data['totalcount'];
        return [
            'length' => $totalcount,
            'programid' => $filtervalues->programid,
            'totalcount' =>  $totalcount,
            'records' => $data,
            'options' => $options,
            'dataoptions' => $dataoptions,
            'filterdata' => $filterdata,
            'programid' => $filtervalues->programid,
            'nodata' => get_string('noexperts','local_questionbank')
        ];
    }

    public static function questionbankviewexperts_returns() {
        return new external_single_structure([
            'options' => new external_value(PARAM_RAW, 'The paging data for the service'),
            'dataoptions' => new external_value(PARAM_RAW, 'The data for the service'),
            'totalcount' => new external_value(PARAM_INT, 'total number of challenges in result set'),
            'filterdata' => new external_value(PARAM_RAW, 'total number of challenges in result set'),
            'length' => new external_value(PARAM_RAW, 'total number of challenges in result set'),
            'records' => new external_single_structure(
                    array(
                        'hasrecords' => new external_multiple_structure(
                            new external_single_structure(
                                array(
                                    'id' => new external_value(PARAM_INT, 'id'),
                                    'username' => new external_value(PARAM_RAW, 'username'),
                                    'dateadded' => new external_value(PARAM_RAW, 'dateadded'),

                                   // 'totalquestions' => new external_value(PARAM_RAW, 'totalquestions'),
                                    'userid' => new external_value(PARAM_INT, 'userid'),
                                    'expertinfo' => new external_value(PARAM_RAW, 'expertinfo',VALUE_OPTIONAL),
                                    
                                    //'wid' => new external_value(PARAM_INT, 'wid'),
                                    //'availablequestions' => new external_value(PARAM_RAW, 'availablequestions'),
                                    //'assignedqcount' => new external_value(PARAM_RAW, 'assignedqcount'),
                                )
                            )
                        ),
                        'request_view' => new external_value(PARAM_INT, 'request_view', VALUE_OPTIONAL),
                        'nodata' => new external_value(PARAM_BOOL, 'nodata', VALUE_OPTIONAL),
                        'totalcount' => new external_value(PARAM_INT, 'totalusers', VALUE_OPTIONAL),
                        'length' => new external_value(PARAM_INT, 'length', VALUE_OPTIONAL),
                    )
                )
        ]);
    }
    
     public static function assignexperts_parameters() {
        return new external_function_parameters(
            array(
               'questionbankid' => new external_value(PARAM_INT,'Workshop id',0),
            )
        );
    }
    public static function assignexperts($questionbankid) {
        global $DB,$CFG;
        $params = self::validate_parameters(self::assignexperts_parameters(),
        ['questionbankid' => $questionbankid]);
        try{
            $DB->delete_records('local_qb_experts', array('id' => $questionbankid));
            return true;
        }catch(Exception $e){
            throw new moodle_exception('Error in unassigning role '. $e);
            return false;
        }
    }
    public static function assignexperts_returns() {
        return new external_value(PARAM_BOOL, 'return');
    }
    public static function unassigntopics_parameters() {
        return new external_function_parameters(
            array(
               'questionbankid' => new external_value(PARAM_INT,'Workshop id',0),
               'topicid' => new external_value(PARAM_INT,'Topic id',0),
            )
        );
    }
    public static function unassigntopics($questionbankid,$topic) {
        global $DB,$CFG,$USER;
        $params = self::validate_parameters(self::unassigntopics_parameters(),
        ['questionbankid' => $questionbankid,'topicid' => $topic]);
        try{
            $DB->delete_records('local_qb_coursetopics', array('questionbankid' => $questionbankid,'topic'=>$topic));
             // notification Questionbank  onchange
        $questionbankrecord=$DB->get_record('local_questionbank',array('id' => $questionbankid));
        $sql="SELECT u.* FROM {user} u
        JOIN {local_qb_experts} le ON le.expertid = u.id
        WHERE le.questionbankid = $questionbankid AND u.confirmed = 1 AND u.suspended = 0 AND u.deleted = 0 AND u.id > 2";
        $touser = $DB->get_records_sql($sql);
        $thispageurl = new moodle_url('/local/questionbank/questionbank_workshop.php?id='.$questionbankid);
         if(!$touser)
         {
          $touser=null;
         }
       $row1=[];
       $row1['RelatedModuleName']=$questionbankrecord->workshopname;
       $row1['RelatedModulesLink']=$thispageurl;
       $myobject=(new \local_questionbank\notification);
       $myobject->questionbank_notification('questionbank_onchange',$touser, $USER,$row1,$waitinglistid=0);
            return true;
        }catch(Exception $e){
            throw new moodle_exception('Error in unassigning role '. $e);
            return false;
        }
    }
    public static function unassigntopics_returns() {
        return new external_value(PARAM_BOOL, 'return');
    }
    public static function deletecompetency_parameters(){
        return new external_function_parameters(
            array(
                'competencyid' => new external_value(PARAM_INT,'Competency id',0),
                'wid' => new external_value(PARAM_INT,'Workshop id',0),
                'competencyname' => new external_value(PARAM_TEXT,'Competency name'),
            )
        );
    }
    public static  function deletecompetency($competencyid,$wid,$competencyname){
        global $CFG;
        global $USER;
        global $DB;
        $params=self::validate_parameters(
            self::deletecompetency_parameters(),
            array('competencyid'=>$competencyid, 'wid'=>$wid,'competencyname'=>$competencyname)
        );
        $systemcontext = context_system::instance();
        self::validate_context($systemcontext);
       if ($competencyid) {
            $getcompetencies =$DB->get_record('local_questionbank',array('id'=>$wid)); 
            $competencies = explode(',',$getcompetencies->competency);
            $key = array_search($competencyid, $competencies);
            unset($competencies[$key]);
            $updatedcomp = implode(',',$competencies);
            $updaterecord= $DB->update_record('local_questionbank', array('id' => $wid,'competency'=> $updatedcomp));
             // notification Questionbank  onchange
            $questionbankrecord=$DB->get_record('local_questionbank',array('id' => $wid));
            $sql="SELECT u.* FROM {user} u
            JOIN {local_qb_experts} le ON le.expertid = u.id
            WHERE le.questionbankid = $wid AND u.confirmed = 1 AND u.suspended = 0 AND u.deleted = 0 AND u.id > 2";
            $touser = $DB->get_records_sql($sql);
        $thispageurl = new moodle_url('/local/questionbank/questionbank_workshop.php?id='.$wid);
            if(!$touser)
            {
            $touser=null;
            }
        $row1=[];
        $row1['RelatedModuleName']=$questionbankrecord->workshopname;
        $row1['RelatedModulesLink']=$thispageurl;
        $myobject=(new \local_questionbank\notification);
        $myobject->questionbank_notification('questionbank_onchange',$touser, $USER,$row1,$waitinglistid=0);
         /*------------------------------------------------------------*/   
        } else {
          throw new moodle_exception('Error in submission');
        }
        return true;    
    }
    public static function deletecompetency_returns() {
        return new external_value(PARAM_BOOL, 'return');
    } 
    public static function deletecourse_parameters(){
        return new external_function_parameters(
            array(
                'courseid' => new external_value(PARAM_INT,'Course id',0),
                'wid' => new external_value(PARAM_INT,'Workshop id',0),
                'coursename' => new external_value(PARAM_TEXT,'Course name',0),
            )
        );
    }
    public static  function deletecourse($courseid,$wid, $coursename){
        global $CFG;
        global $USER;
        global $DB;
        $params=self::validate_parameters(
            self::deletecourse_parameters(),
            array('courseid'=>$courseid,'wid'=>$wid, 'coursename'=>$coursename)
        );
        $systemcontext = context_system::instance();
        self::validate_context($systemcontext);
       if ($wid) {
            $getcourses =$DB->get_record('local_questionbank',array('id'=>$wid)); 
            $courses = explode(',',$getcourses->course);
            $key = array_search($courseid, $courses);
            unset($courses[$key]);
            $updatedcourses = implode(',',$courses);
            $updaterecord= $DB->update_record('local_questionbank', array('id' => $wid,'course'=> $updatedcourses));
            // notification Questionbank  onchange
            $questionbankrecord=$DB->get_record('local_questionbank',array('id' => $wid));
            $sql="SELECT u.* FROM {user} u
            JOIN {local_qb_experts} le ON le.expertid = u.id
            WHERE le.questionbankid = $wid AND u.confirmed = 1 AND u.suspended = 0 AND u.deleted = 0 AND u.id > 2";
            $touser = $DB->get_records_sql($sql);
            $thispageurl = new moodle_url('/local/questionbank/questionbank_workshop.php?id='.$wid);
            if(!$touser)
            {
            $touser=null;
            }
        $row1=[];
        $row1['RelatedModuleName']=$questionbankrecord->workshopname;
        $row1['RelatedModulesLink']=$thispageurl;
        $myobject=(new \local_questionbank\notification);
        $myobject->questionbank_notification('questionbank_onchange',$touser, $USER,$row1,$waitinglistid=0);
         /*------------------------------------------------------------*/  
        } else {
          throw new moodle_exception('Error in submission');
        }
        return true;    
    }
    public static function deletecourse_returns() {
        return new external_value(PARAM_BOOL, 'return');
    }

    public function form_selector_parameters() {
        return new external_function_parameters([
            'type' => new external_value(PARAM_RAW, 'type of the organization'),
        ]);    
    }

    public function form_selector($type) {
        global $PAGE;
        $params = array(         
            'type' => $type
        );
        $params = self::validate_parameters(self::form_selector_parameters(), $params);

        switch($params['type']) {
            case 'expertlist':
                $list =  (new questionbank )->expertlist();
            break;
        }
        return ['status' => true, 'data' => $list];
    }

    public function form_selector_returns() {
        return new external_single_structure(
            array(
                'status' => new external_value(PARAM_INT, 'status: true if success'),
                'data' => new external_multiple_structure(new external_single_structure(
                    array(
                        'id' => new external_value(PARAM_INT, 'status: true if success'),
                        'fullname' => new external_value(PARAM_RAW, 'status: true if success',VALUE_OPTIONAL))
                   )) 
             )
        );
    }
    public function topic_selector_parameters() {
        return new external_function_parameters([
            'type' => new external_value(PARAM_RAW, 'type of the organization'),
            'courseid' => new external_value(PARAM_RAW, 'Selected course',VALUE_OPTIONAL),
            'questionbankid' => new external_value(PARAM_RAW, 'Selected questionbankid',VALUE_OPTIONAL),
        ]);    
    }

    public function topic_selector($type,$courseid,$questionbankid=null) {
        global $PAGE,$DB;
        $params = array(         
            'type' => $type,
            'courseid' =>$courseid,
            'questionbankid' =>$questionbankid
        );
        $params = self::validate_parameters(self::topic_selector_parameters(), $params);
        $courseid =  json_decode($courseid);
        if(is_array($courseid)){
          $courses = implode(',', $courseid);
        }else{
          $courses =  $courseid;
        }
        if(!empty($courses)){
            $existingtopics = $DB->get_fieldset_sql("SELECT topic FROM {local_qb_coursetopics} where questionbankid=".$questionbankid." AND courseid IN ($courses) ");
            if(!empty($existingtopics )){
                 $existingtopics = implode(',',$existingtopics);
                 $topicslist  = " and  id NOT IN ($existingtopics)";
            }
            $topicssql = "SELECT id AS id,(CASE WHEN name IS NULL THEN CONCAT('Topic',section) ELSE name END) as fullname ,FROM_UNIXTIME(timemodified,'%D %M %Y') AS dateadded 
                            FROM {course_sections} WHERE course in($courses) AND section <> 0  $topicslist ";
            $data = $DB->get_records_sql($topicssql);
        }else{
            $data = array();
        }
        return ['status' => true, 'data' => $data];
    }

    public function topic_selector_returns() {
        return new external_single_structure(
            array(
                'status' => new external_value(PARAM_INT, 'status: true if success'),
                'data' => new external_multiple_structure(new external_single_structure(
                    array(
                        'id' => new external_value(PARAM_INT, 'status: true if success'),
                        'fullname' => new external_value(PARAM_RAW, 'status: true if success',VALUE_OPTIONAL))
                   )) 
             )
        );
    }
    public function changequestionstatus_parameters() {
        return new external_function_parameters([
            'questionid' => new external_value(PARAM_INT, 'Question ID'),
            'workshopid' => new external_value(PARAM_INT,'Workshop id',0),
            'status' => new external_value(PARAM_RAW ,'Question status',VALUE_OPTIONAL),
        ]);    
    }

    public function changequestionstatus($questionid,$category,$status) {
        global $PAGE,$DB;
        $params = array(         
            'questionid' => $questionid,
            'workshopid' =>$category,
            'status' =>$status
        );
        $params = self::validate_parameters(self::changequestionstatus_parameters(), $params);
        $workshopid =  $DB->get_field('local_questionbank','id',array('qcategoryid'=>$category));
        $data = (new questionbank )->questions_reviewstatus($questionid,$category,$status);
        // if($workshopid){
        // $questioninfo =  $DB->get_field('local_qb_questionreview','id',array('questionid'=>$questionid,'questionbankid'=>$workshopid,'categoryid'=>$category));
        //  $data->reviewdon = time();
        //  $data->qstatus = $status;
        //  $data->id  =  $questioninfo;
        //  $DB->update_record('local_qb_questionreview',$data);
        // }
         //$data = (new questionbank )->questions_review($questionid,$workshopid,$reviewerid);
        return true;
    }

    public function changequestionstatus_returns() {
        return new external_value(PARAM_BOOL, 'return');
    }

    /**
     * Bulk Question Status Update
     * @param string questionids
     * @return Boolean
     */
    public function bulk_update_question_status_parameters(){
        return new external_function_parameters([
            'questionids' => new external_value(PARAM_RAW, 'Question IDs'),
            'wid' => new external_value(PARAM_INT,'Workshop id',0),
            'status' => new external_value(PARAM_RAW ,'Question status',VALUE_OPTIONAL),
        ]);
    }
    public function bulk_update_question_status(string $questionids, $category,$status){
        global $PAGE,$DB;
        $params = array(         
            'questionids' => $questionids,
            'wid' =>$category,
            'status' =>$status
        );
        $params = self::validate_parameters(self::bulk_update_question_status_parameters(), $params);
        
        $workshopid =  $DB->get_field('local_questionbank','id',array('qcategoryid'=>$category));
        $arrquestion_ids = explode(',', $questionids);
        for ($i=0; $i < count($arrquestion_ids); $i++) { 
            $data[$arrquestion_ids[$i]] = (new questionbank )->questions_reviewstatus($arrquestion_ids[$i],$category,$status);
        }
        return true;
    }

    public function bulk_update_question_status_returns() {
        return new external_value(PARAM_BOOL, 'return');
    }
    public function assignreviewer_parameters() {
        return new external_function_parameters([
            'questionid' => new external_value(PARAM_INT, 'Question ID'),
            'workshopid' => new external_value(PARAM_INT,'Workshop id',0),
            'reviewerid' => new external_value(PARAM_INT, 'Question status',VALUE_OPTIONAL),
        ]);    
    }

    public function assignreviewer($questionid,$workshopid,$reviewerid) {
        global $PAGE,$DB,$USER;
        $params = array(         
            'questionid' => $questionid,
            'workshopid' =>$workshopid,
            'reviewerid' =>$reviewerid
        );
        //print_r($params );
        $params = self::validate_parameters(self::assignreviewer_parameters(), $params);
        // print_r($params);
        // exit;
        $data = (new questionbank )->questions_review($questionid,$workshopid,$reviewerid);
         //-----------------------
         $questionbankname = $DB->get_field('local_questionbank','workshopname', array('qcategoryid'=>$workshopid));
         $questiontext = $DB->get_field('question','questiontext', array('id'=>$questionid)); 
         $reviewer = $DB->get_record('user', array('id'=>$reviewerid));
          $row=[];
          $row['QuestionBankName']=$questionbankname;
          $row['QuestionText']=$questiontext;
          $localuserrecord = $DB->get_record('local_users',['userid'=> $reviewerid]);
          $fullname = ($localuserrecord)? ((current_language() == 'ar') ? $localuserrecord->firstnamearabic.' '.$localuserrecord->middlenamearabic.' '.$localuserrecord->thirdnamearabic.' '.$localuserrecord->lastnamearabic  : $localuserrecord->firstname.' '.$localuserrecord->middlenameen.' '.$localuserrecord->thirdnameen.' '.$localuserrecord->lastname) :fullname($DB->get_record('user',array('id'=>(int)$reviewerid)));
          $row['ReviewerName']=$fullname;
        //print_r($row);exit;
         $emailtype='questionbank_assign_reviewer';
         $myobject=(new \local_questionbank\notification);
         $myobject->questionbank_notification($emailtype,$touser=$reviewer, $USER,$row,$waitinglistid=0);
        $qb_id = $DB->get_field('local_questionbank', 'id', ['qcategoryid'=> $params['workshopid']]);
        $experts = $DB->get_fieldset_sql("SELECT expertid FROM {local_qb_experts} WHERE questionbankid = :questionbankid ", ['questionbankid'=> $qb_id]);
        if ($experts) {
            $remove_current_user = array_search($USER->id, $experts);
            if($remove_current_user !== false){
                unset($experts[$remove_current_user]);
            }
            $remove_selected_user = array_search($reviewerid, $experts);
            if($remove_selected_user !== false){
                unset($experts[$remove_selected_user]);
            }
            if (count($experts) > 0) {
                $exp_ids = implode(',', $experts);
                $fullname = (new \local_trainingprogram\local\trainingprogram())->user_fullname_case();
                $all_reviewers = $DB->get_records_sql("SELECT u.id, $fullname FROM  {user} as u LEFT JOIN {local_users} AS lc ON lc.userid = u.id WHERE u.id IN ($exp_ids)");
                $options = '<option value="">Assign Reviewer</option>';
                foreach($all_reviewers as $reviewer){
                    $options .= '<option value="'.$reviewer->id.'">'.$reviewer->fullname.'</option>';
                }
                $data = [
                    'options' => $options,
                    'return' => true
                ];
                return $data;
            }
        }
        return ['return' => true];
    }

    public function assignreviewer_returns() {
        // return new external_value(PARAM_BOOL, 'return');
        return new external_single_structure(
            array(
                'options' => new external_value(PARAM_RAW, 'Dropdown Options', VALUE_OPTIONAL),
                'return' => new external_value(PARAM_BOOL, 'return', VALUE_OPTIONAL)
             )
        );
    }
     public function qbcompetencieslist_parameters() {
        return new external_function_parameters([
            'query' => new external_value(PARAM_RAW, 'Query'),
            'ctype' => new external_value(PARAM_RAW, 'Competency Type'),
        ]);    
    }

    public function qbcompetencieslist($query,$ctype) {
        global $PAGE,$DB;
        $params = array(  
            'query'=>$query,       
            'ctype' => $ctype,

        );
        $params = self::validate_parameters(self::qbcompetencieslist_parameters(), $params);
         $currentlang= current_language();
        if( $currentlang == 'ar'){
            $display_name = 'lc.arabicname';

        } else {
            $display_name = 'lc.name';

        }
        $sql = "SELECT lc.id,$display_name AS fullname FROM {local_competencies} lc  WHERE  lc.type IN ('$ctype') ";
        if($query!="")
        {
            $formsql .= " AND $display_name  LIKE '%$query%'";
        }

        //echo $sql . $formsql;exit;
        $data = $DB->get_records_sql($sql.$formsql);
        $return = array_values(json_decode(json_encode(($data)), true));
        
        return ['status' => true, 'data' => $data];
    }

    public function qbcompetencieslist_returns() {
        return new external_single_structure(
            array(
                'status' => new external_value(PARAM_INT, 'status: true if success'),
                'data' => new external_multiple_structure(new external_single_structure(
                    array(
                        'id' => new external_value(PARAM_INT, 'status: true if success'),
                        'fullname' => new external_value(PARAM_RAW, 'status: true if success',VALUE_OPTIONAL))
                   )) 
             )
        );
    }
    //  public static function reservequestionsforexpert_parameters() {
    //     return new external_function_parameters(
    //         array(
    //             'eid' => new external_value(PARAM_INT, 'hallid', 0),
    //             'userid' => new external_value(PARAM_INT, 'examid', 0),
    //             'wid' => new external_value(PARAM_RAW, 'examdate', 0),
    //             'qcount' => new external_value(PARAM_RAW, 'start', 0),
    //             )
    //     );
    // }
    // public static function reservequestionsforexpert($eid, $userid, $wid,$qcount) {
    //     global $DB, $USER,$CFG;
    //     require_login();
    //     $systemcontext = context_system::instance();
    //     $params = self::validate_parameters(self::reservequestionsforexpert_parameters(),
    //                     ['eid' => $eid, 'userid' => $userid, 'wid' => $wid, 'qcount' => $qcount]);

    //     $record = $DB->get_record('local_qb_experts', ['id' => $eid, 'expertid' => $userid, 'questionbankid' => $wid]);
    //     if($record->id > 0) {
    //         $updaterecord= $DB->update_record('local_qb_experts', array('id' => $eid,'noofquestions'=> $qcount));
    //     } 

    //     return true;
    // }
    // public static function reservequestionsforexpert_returns() {
    //     // return new external_single_structure([
    //     //     'name' => new external_value(PARAM_RAW, 'The paging data for the service'),
    //     //     'seats' => new external_value(PARAM_RAW, 'The paging data for the service'),
    //     //     'examdate' => new external_value(PARAM_RAW, 'The paging data for the service'),
    //     // ]);
    //    return new external_value(PARAM_BOOL, 'return');
    // }


     public function fetch_expertname_parameters() {
        return new external_function_parameters([
            'expert_id' => new external_value(PARAM_INT, 'Expert ID'),
            'ctype' => new external_value(PARAM_RAW, 'Competency Type'),
        ]);    
    }

    public function fetch_expertname($expert_id) {
        global $PAGE,$DB;
        $params = array(  
            'expert_id'=>$expert_id,
        );
        $params = self::validate_parameters(self::fetch_expertname_parameters(), $params);
        // print_r($params);die;
        $currentlang= current_language();
        $fullname = (new \local_trainingprogram\local\trainingprogram())->user_fullname_case();
        $expertsnames=$DB->get_fieldset_sql("SELECT $fullname 
            FROM {local_qb_experts} as qe 
            JOIN {user} as u ON u.id = qe.expertid
            JOIN {local_users} AS lc ON lc.userid = qe.expertid
            WHERE u.id = :expertid ", ['expertid' => $expert_id]);
        return ['status' => true, 'data' => $data];
    }

    public function fetch_expertname_returns() {
        return new external_single_structure(
            array(
                'status' => new external_value(PARAM_INT, 'status: true if success'),
                'data' => new external_multiple_structure(new external_single_structure(
                    array(
                        'id' => new external_value(PARAM_INT, 'status: true if success'),
                        'fullname' => new external_value(PARAM_RAW, 'status: true if success',VALUE_OPTIONAL))
                   )) 
             )
        );
    }
}
