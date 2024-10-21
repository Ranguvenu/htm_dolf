<?php 
namespace local_questionbank\local;

use core_question\local\bank\view as qview;
use question_bank;
use qbank_previewquestion\question_preview_options;
use qbank_editquestion\editquestion_helper;
use question_engine;
use context_user;
use context_system;
use moodle_url;
use core_question\bank\search\category_condition;

class view extends qview {

    public function __construct($contexts, $pageurl, $course, $cm = null){
       
        // parent::__construct($contexts, $pageurl, $course, $cm);
        $this->contexts = $contexts;
        $this->baseurl = $pageurl;
        $this->course = $course;
        $this->cm = $cm;

        // Create the url of the new question page to forward to.
        $this->returnurl = $pageurl->out_as_local_url(false);
        $this->editquestionurl = new \moodle_url('/local/questionbank/editquestion.php', ['returnurl' => $this->returnurl]);
        if ($this->cm !== null) {
            $this->editquestionurl->param('cmid', $this->cm->id);
        } else {
            $this->editquestionurl->param('courseid', $this->course->id);
        }

        $this->lastchangedid = optional_param('lastchanged', 0, PARAM_INT);

        // Possibly the heading part can be removed.
        $this->init_columns($this->wanted_columns(), $this->heading_column());
        $this->init_sort();
        $this->init_search_conditions();
        $this->init_bulk_actions();
    }


    
    public function display($pagevars, $tabname): void {
        global $DB,$USER,$CFG, $OUTPUT;
        $systemcontext = context_system::instance();
        $page = $pagevars['qpage'];
        $perpage = $pagevars['qperpage'];
        $cat = $pagevars['cat'];
        $recurse = $pagevars['recurse'];
        $showhidden = $pagevars['showhidden'];
        $showquestiontext = $pagevars['qbshowtext'];
        $tagids = [];
        if (!empty($pagevars['qtagids'])) {
            $tagids = $pagevars['qtagids'];
        }
       
        if(!is_siteadmin() && !has_capability('local/questionbank:assignreviewer',$systemcontext)){
              $category = explode(',',$cat);
              $get_questions = "SELECT q.id
                             FROM {question} as q
                             JOIN {local_qb_questionreview} qb ON qb.questionid = q.id
                             WHERE qb.categoryid = $category[0] AND (q.createdby = $USER->id OR qb.assignedreviewer =  $USER->id)  ";
              $get_questions = $DB->get_fieldset_sql($get_questions);
              $questionlist = implode(',',$get_questions);
        }

        // $questioninfo =  $DB->get_field('local_qb_questionreview','id',array('questionid'=>$questions->id,'reviewdby'=>$USER->id));
        // if(($qinfo->createdby != $USER->id) && empty( $questioninfo)){
        //         continue;
        // }
        echo \html_writer::start_div('questionbankwindow boxwidthwide boxaligncenter');

        $editcontexts = $this->contexts->having_one_edit_tab_cap($tabname);
         // print_r()
        // Show the filters and search options.
        // $this->wanted_filters($cat, $tagids, $showhidden, $recurse, $editcontexts, $showquestiontext);
        //$this->searchconditions = " AND q.id IN ($questionlist)";
        // print_r($this->searchconditions);
        // exit;
        array_unshift($this->searchconditions, new \core_question\bank\search\category_condition(
                        $cat, $recurse, $editcontexts, $this->baseurl, $this->course));
        list($categoryid, $contextid) = explode(',', $cat);
        //$this->searchconditions += array(" AND q.id IN ($questionlist)");
            $workshop = $DB->get_record_sql("SELECT q.*  FROM {local_questionbank} q 
                                     WHERE q.qcategoryid = $categoryid  ");
            $currentlang= current_language();
            $fullname = (new \local_trainingprogram\local\trainingprogram())->user_fullname_case();
            $expertsnames=$DB->get_fieldset_sql("SELECT $fullname FROM {local_qb_experts} as qe JOIN {user} as u ON u.id = qe.expertid
              JOIN {local_users} AS lc ON lc.userid = qe.expertid
              JOIN {local_questionbank} as qb ON qb.qcategoryid =$categoryid AND qe.questionbankid = qb.id  where qb.qcategoryid =".$categoryid." ");
            $expertsnames= implode(',', $expertsnames);


        echo $OUTPUT->render_from_template('local_questionbank/questionheader', 
               ["workshopname" =>$workshop->workshopname,
                "noofquestions" =>$workshop->noofquestions,
                "expertsnames" =>$expertsnames,
                "generatecode" =>$workshop->generatecode,
                "questionbank_workshop_url" => $CFG->wwwroot.'/local/questionbank/questionbank_workshop.php?id='.$workshop->id]);
        // Continues with list of questions.
        $this->display_question_list($this->baseurl, $cat, null, $page, $perpage,
                                        $this->contexts->having_cap('moodle/question:add'));

        echo \html_writer::end_div();


    }

    protected function display_question_list($pageurl, $categoryandcontext, $recurse = 1, $page = 0,
                                                $perpage = 100, $addcontexts = []): void {
        global $OUTPUT,$DB,$USER;
         $systemcontext = context_system::instance();
        // This function can be moderately slow with large question counts and may time out.
        // We probably do not want to raise it to unlimited, so randomly picking 5 minutes.
        // Note: We do not call this in the loop because quiz ob_ captures this function (see raise() PHP doc).
        \core_php_time_limit::raise(300);

        $category = $this->get_current_category($categoryandcontext);

        list($categoryid, $contextid) = explode(',', $categoryandcontext);
        $catcontext = \context::instance_by_id($contextid);
-
        $canadd = has_capability('moodle/question:add', $catcontext);
        $this->build_query();
        $totalnumber = $this->get_question_count();
        $workshop = $DB->get_record_sql("SELECT q.*  FROM {local_questionbank} q 
                                     WHERE q.qcategoryid = $category->id");

        // if ($totalnumber == 0) {
        //     return;
        // }
        $questionsrs = $this->load_page_questions($page, $perpage);
        $questions = [];
        foreach ($questionsrs as $question) {
            if (!empty($question->id)) {
                $questions[$question->id] = $question;
                if ($question->createdby == $USER->id) {
                    $createdby[] = $question->id;
                }
            }
        }
        $questionsrs->close();
        foreach ($this->requiredcolumns as $name => $column) {
            $column->load_additional_data($questions);
        }

        $pageingurl = new \moodle_url($pageurl, $pageurl->params());
        $pagingbar = new \paging_bar($totalnumber, $page, $perpage, $pageingurl);
        $pagingbar->pagevar = 'qpage';
        $quesstatus = $this->get_ques_statuses();
        
        $is_expert = $DB->get_record_sql("
            SELECT u.id, r.shortname FROM {user} u
            JOIN {role_assignments} ra ON ra.userid = u.id
            JOIN {role} r ON r.id = ra.roleid
            WHERE u.id = :userid AND r.shortname = 'expert'
        ", ['userid' => $USER->id]);

        echo \html_writer::start_tag('div' , ['class' => 'col-md-12 mb-3 row']);
        $options = ['5' => 5, '20' => 20, '50' => 50, '100' => 100];
        $url = new moodle_url('/local/questionbank/questionbank_view.php', ['courseid' => 1, 'cat' => $category->id .',1']);
        $singleselect = $OUTPUT->single_select($url, 'qperpage', $options, $perpage, ['' => 'Questions to show']);
        echo \html_writer::div($singleselect, 'col-md-2');
        echo \html_writer::start_tag('div' , ['class' => 'col-md-3']);
          if( ($totalnumber <  $workshop->noofquestions) &&   $workshop->movedtoprod !=1){

            if ($is_expert->shortname == 'expert') {
                echo \html_writer::div( 'expert', '', ['id' => 'currentuserrole', 'style' => 'display:none']);
                $noofquestionadded = get_noofquestion_added_by_expert($USER->id, $category->id)->noofquestionadded;
                $allowed_questions = $DB->get_field('local_qb_experts', 'noofquestions', ['questionbankid' => $workshop->id, 'expertid' => $USER->id]);
                if (is_numeric($allowed_questions)) {
                    if ($noofquestionadded < $allowed_questions) {
                        $this->create_new_question_form($category, $canadd);
                    }else{
                        \core\notification::info(get_string('allowedquestionsadded', 'local_questionbank'));
                    }
                }else{
                    $this->create_new_question_form($category, $canadd);
                }
            }else{
                $this->create_new_question_form($category, $canadd);
            }
            
        }else{
            // $message = "No more questions can be added to this workshop as the maximum number of allowed questions are already added to this workshop.";
            // \core\notification::warning($message);

        }
        echo \html_writer::end_tag('div');
        echo \html_writer::start_tag('div' , ['class' => 'col-md-7']);
        $systemcontext = context_system::instance();
        $statuschange = true;  
        $hidestatus = false;
        if(is_siteadmin() || has_capability('local/questionbank:assignreviewer',$systemcontext) || count($createdby) > 1){
            
            echo $OUTPUT->render_from_template('local_questionbank/bulkquestionstatusupdate', ['qstatus' => $quesstatus, 'qbid' => $category->id]);
        }
        echo \html_writer::end_tag('div');

        echo \html_writer::end_tag('div');

        $this->display_top_pagnation($OUTPUT->render($pagingbar));

        // This html will be refactored in the bulk actions implementation.
        echo \html_writer::start_tag('form', ['action' => $pageurl, 'method' => 'post', 'id' => 'questionsubmit']);
        echo \html_writer::start_tag('fieldset', ['class' => 'invisiblefieldset', 'style' => "display: block;"]);
        echo \html_writer::empty_tag('input', ['type' => 'hidden', 'name' => 'sesskey', 'value' => sesskey()]);
        echo \html_writer::input_hidden_params($this->baseurl);

        $this->display_questions($questions);

        $this->display_bottom_pagination($OUTPUT->render($pagingbar), $totalnumber, $perpage, $pageurl);

        echo \html_writer::end_tag('fieldset');
        echo \html_writer::end_tag('form');
    }
    /**
     * Display the questions.
     *
     * @param array $questions
     */
    protected function display_questions($questions1): void {
        global $DB, $USER,$OUTPUT,$USER,$CFG,$PAGE;
      
        require_once($CFG->dirroot.'/local/questionbank/lib.php');
        echo \html_writer::start_tag('div',
                ['class' => 'categoryquestionscontainer', 'id' => 'questionscontainer']);
        $res ='';
        $ques_added_count = 0;
        foreach($questions1 as $questions){
            $assigned_reviewer  ='';
            $question = question_bank::load_question($questions->id);
            $maxvariant = min($question->get_num_variants(), QUESTION_PREVIEW_MAX_VARIANTS);
            $options = new question_preview_options($question);
            $options->load_user_defaults();
            $options->set_from_request();
            $quba = question_engine::make_questions_usage_by_activity(
                    'core_question_preview', context_user::instance($USER->id));
            $quba->set_preferred_behaviour($options->behaviour);
            $slot = $quba->add_question($question, $options->maxmark);

            if ($options->variant) {
                $options->variant = min($maxvariant, max(1, $options->variant));
            } else {
                $options->variant = rand(1, $maxvariant);
            }

            $quba->start_question($slot, $options->variant);

           $transaction = $DB->start_delegated_transaction();
           question_engine::save_questions_usage_by_activity($quba);
           $transaction->allow_commit();
           $options->behaviour = $quba->get_preferred_behaviour();
           $options->maxmark = $quba->get_question_max_mark($slot);
           $qinfo =$quba->get_question($slot);
          $currentlang= current_language();
          // print_object($qinfo);die;
          $fullname = (new \local_trainingprogram\local\trainingprogram())->user_fullname_case();
          $createduser = $DB->get_field_sql("SELECT $fullname FROM  {user} as u LEFT JOIN {local_users} AS lc ON lc.userid = u.id WHERE u.id = $qinfo->createdby");
          $reviewer = $DB->get_record_sql("SELECT qb.*  FROM {local_qb_questionreview} qb 
                                     WHERE qb.categoryid = $qinfo->category  and qb.questionid = $questions->id ");
           if(!empty($reviewer->assignedreviewer)){
               $assigned_reviewer = " AND u.id != ".$reviewer->assignedreviewer;
           }
           $expertsql = "SELECT u.id,$fullname FROM {local_qb_experts} as qe JOIN {user} as u ON u.id = qe.expertid
              JOIN {local_users} AS lc ON lc.userid = qe.expertid
              JOIN {local_questionbank} as qb ON qb.qcategoryid =$qinfo->category AND qe.questionbankid = qb.id
              LEFT JOIN {local_qb_questionreview} as qbr ON qbr.questionid =$questions->id   where qb.qcategoryid =".$qinfo->category.$assigned_reviewer;
           $experts_info=$DB->get_records_sql($expertsql);

          $expertsnames=$DB->get_fieldset_sql("SELECT $fullname FROM {local_qb_experts} as qe 
            JOIN {user} as u ON u.id = qe.expertid
            JOIN {local_users} AS lc ON lc.userid = qe.expertid
            JOIN {local_questionbank} as qb ON qb.qcategoryid =$qinfo->category AND qe.questionbankid = qb.id  where qb.qcategoryid =".$qinfo->category." AND u.id != ".$qinfo->createdby);
          $expertsnames= implode(',', $expertsnames);
          $reviewer_name = fullname($reviewer->reviewdby);
          $questioncourses = $DB->get_record_sql("SELECT qb.*  FROM {local_qb_questioncourses} qb 
                                     WHERE qb.questionbankid = $qinfo->category  and qb.questionid = $questions->id AND topic IS NOT NULL");

        $ques = new \stdClass();
        $ques->questionid = $questions->id;
        $questioncompetenciesrecord = (new \local_competency\competency())::get_competencies($ques);
        
            $questioncompetencies = false;
        if ($questioncompetenciesrecord['competencies']) {
            $questioncompetencies = true;
        }
          if((is_siteadmin() || ($qinfo->createdby == $USER->id)) && $questions->status != 'ready'){
             $assignreviewer = true;
           }else{
             $assignreviewer = false;
           }
            $systemcontext = context_system::instance();
            $statuschange = true;  
            $hidestatus = false;
            if(is_siteadmin() || has_capability('local/questionbank:assignreviewer',$systemcontext)){
                $hidestatus = true;
                $qstatus = [null=>get_string('changestatus','local_questionbank'),
                            'underreview'=>get_string('underreview', 'local_questionbank'),
                            'readytoreview'=>get_string('readytoreview', 'local_questionbank'),
                            'draft'=>get_string('draft', 'local_questionbank'),
                            'publish'=>get_string('publish', 'local_questionbank')];
                $splitData =statusinfo($qstatus);
           }elseif($qinfo->status != 'ready' &&  $USER->id == $qinfo->createdby){

                $qstatus = [null=>get_string('changestatus','local_questionbank'),
                            'underreview'=>get_string('underreview', 'local_questionbank'),
                            'readytoreview'=>get_string('readytoreview', 'local_questionbank'),
                            'publish'=>get_string('publish', 'local_questionbank')];
                $splitData =statusinfo($qstatus);
           }elseif($qinfo->status != 'ready' && ($reviewer->qstatus == 'readytoreview' || $reviewer->qstatus == 'underreview') && ($USER->id == $reviewer->assignedreviewer || $USER->id == $qinfo->createdby)){

                $qstatus = [null=>get_string('changestatus','local_questionbank'),
                            'underreview'=>get_string('underreview', 'local_questionbank'),
                            'publish'=>get_string('publish', 'local_questionbank')];
                $splitData =statusinfo($qstatus);
           }
           else{
             $statuschange = false;
           }
           $experts=array_values($experts_info);
           
            $question2 =  $quba->render_question($slot, $options, $displaynumber).$dropdown;
            if($reviewer->assignedreviewer){
              // $revfullname = $DB->get_field('user','CONCAT(firstname," ",lastname) as fullname',array('id'=>$reviewer->assignedreviewer));
                $revfullname = $DB->get_field_sql("SELECT $fullname FROM  {user} as u LEFT JOIN {local_users} AS lc ON lc.userid = u.id WHERE u.id = $reviewer->assignedreviewer");
            }else{
               $revfullname =  get_string('noexperts','local_questionbank');
            }
            if($reviewer->qstatus && $reviewer->reviewdby){
               //$reviewedby=  $revfullname;
               $reviewedby = $DB->get_field_sql("SELECT $fullname FROM  {user} as u LEFT JOIN {local_users} AS lc ON lc.userid = u.id WHERE u.id = $reviewer->reviewdby");
               //$reviewedby = $DB->get_field('user','CONCAT(firstname," ",lastname) as fullname',array('id'=>$reviewer->reviewdby));
               $questionstatus = get_string($reviewer->qstatus, 'local_questionbank');
            }else{
               $questionstatus =  get_string('draft', 'local_questionbank');
               $reviewedby=  get_string('notreviewed', 'local_questionbank');
            }
          
            if(!empty($experts) && !empty($expertsnames)){
              $expertslist = true;
            }else{
              $expertslist = false;
              if(empty($expertsnames) && !empty($experts)){
                  $noexperts = get_string("oneexpertassigned",'local_questionbank');
              }else{
                  $noexperts = get_string("noexpertsassigned",'local_questionbank');
              }
              $noexpertsassigned = empty($expertsnames)? $noexperts:get_string("revieweralreadyassigned",'local_questionbank') ;
            }
            // print_object($qinfo->category);die;
            $qcategory = $qinfo->category.','.$systemcontext->id;
            $workshop = $DB->get_field_sql("SELECT q.id  FROM {local_questionbank} q 
                                     WHERE q.qcategoryid = $qinfo->category ");
            $generatecode= $DB->get_field_sql("SELECT q.generatecode  FROM {local_questionbank} q ");
            $questionbank_view_url =$CFG->wwwroot.'/local/questionbank/questionbank_workshop.php?id='.$workshop;
            $action_icons = false;
            if(is_siteadmin() || has_capability('local/questionbank:assignreviewer',$systemcontext) || ($USER->id == $qinfo->createdby) ){
                $action_icons = true;
                $edit_res = $this->edit_question_url($question->id);
                // print_object($edit_res);die;
                $qid = 'q' . $question->id;
                $returnurl = "/local/questionbank/questionbank_view.php?wid=".$workshop."&courseid=1&cat=".$qcategory;
                $deletequestionurl =  new \moodle_url('/question/bank/deletequestion/delete.php');
                $deleteparams = array(
                    'deleteselected' => $question->id,
                    'courseid' => 1,
                    'q' . $question->id => 1,
                    'sesskey' => sesskey(),
                    'returnurl'=>"/local/questionbank/questionbank_view.php?wid=$workshop&courseid=1&cat=".$qcategory
                );
                $url = new \moodle_url($deletequestionurl,$deleteparams);

                $delete_res = $url;
            }
            $returnurl = "/local/questionbank/questionbank_view.php?courseid=1&cat=$qinfo->category&category=$qinfo->category";
            $duplicate_params = [
                'returnurl' => $returnurl,
                'id' => $question->id,
                'courseid' => 1,
                'makecopy' => true
            ];
            $duplicate_url = '';
            $totalnumber = $this->get_question_count();
            $workshop = $DB->get_record_sql("SELECT q.*  FROM {local_questionbank} q WHERE q.qcategoryid = $qinfo->category");
            if ($qinfo->createdby == $USER->id) {
                $ques_added_count++;
            }
            if( ($totalnumber <  $workshop->noofquestions) &&   $workshop->movedtoprod !=1){
                $canduplicate = true;
                $noofquestionadded = get_noofquestion_added_by_expert($USER->id, $qinfo->category)->noofquestionadded;
                $allowed_questions = $DB->get_field('local_qb_experts', 'noofquestions', ['questionbankid' => $workshop->id, 'expertid' => $USER->id]);
                $is_expert = $DB->get_record_sql("
                    SELECT u.id, r.shortname FROM {user} u
                    JOIN {role_assignments} ra ON ra.userid = u.id
                    JOIN {role} r ON r.id = ra.roleid
                    WHERE u.id = :userid AND r.shortname = 'expert'
                ", ['userid' => $USER->id]);
                $expertallowed_questions = $DB->get_field('local_qb_experts', 'noofquestions', ['questionbankid' => $workshop->id, 'expertid' => $USER->id]);
                
                if ($is_expert->shortname == 'expert' && $noofquestionadded < $allowed_questions ) {
                    if ($expertallowed_questions > $ques_added_count ) {
                        $canduplicate = true;
                    }else{
                        $canduplicate = false;
                    }
                    // $canduplicate = true;
                    $duplicate_url = new \moodle_url("{$CFG->wwwroot}/local/questionbank/editquestion.php", $duplicate_params);
                }else if ($noofquestionadded < $allowed_questions) {
                        $canduplicate = true;
                    $duplicate_url = new \moodle_url("{$CFG->wwwroot}/local/questionbank/editquestion.php", $duplicate_params);
                }else{
                    $canduplicate = false;
                }
            }else{
                $canduplicate = false;
            }
            $data = [
                'createduser'=>$createduser,
                'status'=> $questionstatus,
                'reviewdby'=> $reviewedby,
                'reviewername'=> $revfullname,
                'questionid'=>$questions->id,
                'questionname'=>$questions->name,
                'qstatus'=>$splitData,
                'statuschange'=>$statuschange,
                'hidestatus'=>$hidestatus,
                'qbid'=>$qinfo->category,
                'assignreviewer'=> $assignreviewer,
                'hasrecords'=>$experts,
                'expertslist' => $expertslist,
                'question' => $question2,
                "questionbank_view_url" =>$questionbank_view_url,
                "generatecode"=>$generatecode,
                "action_icons" => $action_icons,
                "edit" =>$edit_res,
                "delete" =>$delete_res,
                "duplicate" =>$duplicate_url,
                "canduplicate" => $canduplicate,
                "questioncourses" =>$questioncourses->topic ? $questioncourses->topic : false,
                "questioncompetencies" => $questioncompetencies,
                "noexpertsassigned" =>$noexpertsassigned

            ];
            $res .= $OUTPUT->render_from_template('local_questionbank/assignreviewer',$data); 
            if (!empty($this->cm->id)) {
                $this->returnparams['cmid'] = $this->cm->id;
            }
            if (!empty($this->course->id)) {
                $this->returnparams['courseid'] = $this->course->id;
            }
            if (!empty($this->returnurl)) {
                $this->returnparams['returnurl'] = $this->returnurl;
            }
            $experts ='';
        }

        echo $res;
        echo \html_writer::end_tag('div');

    }
    public function get_ques_statuses(){
        $systemcontext = context_system::instance();
        $qstatus = [
            null => get_string('bulkchangestatus','local_questionbank'),
            'underreview' => get_string('underreview', 'local_questionbank'),
            'readytoreview' => get_string('readytoreview', 'local_questionbank'),
            'draft' => get_string('draft', 'local_questionbank'),
            'publish' => get_string('publish', 'local_questionbank')
        ];
        $splitData =statusinfo($qstatus);
        return $splitData;
    }

}
