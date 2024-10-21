<?php
public static function userexamhistory($examcode, $profilecode,$username, $hallcode, $grade,$achievementdate, $attemptcount,$certificatecode,$examdate,$starttime,$endtime)
    {
        global $OUTPUT, $PAGE, $DB, $USER,$CFG;
        $CFG->debug = (E_ALL | E_STRICT);   // === DEBUG_DEVELOPER - NOT FOR PRODUCTION SERVERS!
        $CFG->debugdisplay = 1;

         $start = explode(':', $starttime);
        $endtime = explode(':', $endtime);
        $starthour= $start[0]+3;
        $startmin= $start[1];
        $startsec= $start[2];
        $endhour= $endtime[0]+3;
        $endmin= $endtime[1];
        $endsec= $endtime[2];
        $starthour=$starthour*3600;
        $startmin=$startmin*60;
        $starttimes = $starthour+$startmin;
        $endhour=$endhour*3600;
        $endmin=$endmin*60;
        $endtimes = $endhour+$endmin;
    $getstarttime = usertime($starttimes);
    $getendtime = usertime($endtimes);
     $examdate = strtotime($examdate);
     $getfinalstarttime = $getstarttime+$examdate;
     $getfinalgetendtime = $getendtime+$examdate;
      
        if ($DB->record_exists('externalprovider_userdetails', ['externaluserid' => $username], '*', MUST_EXIST)) {
            $externalgetuserid = $DB->get_record('externalprovider_userdetails', ['externaluserid' => $username], '*', MUST_EXIST);
            $cisiuserid=$externalgetuserid->userid;
            
            if (!$DB->record_exists('user', ['id' => $cisiuserid], '*', MUST_EXIST)) {

                $throw = new invalid_parameter_exception('cisi userid is not exists: ' . $username.' and LMS userid is not exists: ' . $cisiuserid);
                throw $throw;
            } else {
                $getuserid = $DB->get_record('user', ['id' => $cisiuserid], '*', MUST_EXIST);
            }

           // $getuserid = $DB->get_record('user', ['id' => $cisiuserid], '*', MUST_EXIST);
            //$throw = new invalid_parameter_exception('Usernames is not exists: ' . $username);
            //throw $throw;
        } else {
            if (!$DB->record_exists('user', ['username' => $username], '*', MUST_EXIST)) {

                $throw = new invalid_parameter_exception('Username is not exists: ' . $username);
                throw $throw;
            } else {
                $getuserid = $DB->get_record('user', ['username' => $username], '*', MUST_EXIST);
            }
        }
       if($examcode!=$profilecode){
        if (!$DB->record_exists('local_exams', ['code' => $examcode], '*', MUST_EXIST)) {
            $throw = new invalid_parameter_exception('Examcode is not exists: ' . $examcode);
            throw $throw;
        } else {
            $examids = $DB->get_record('local_exams', ['code' => $examcode], '*', MUST_EXIST);
            $examid = $examids->id;
        }}
        {
            $examidcode = $DB->get_record('local_exam_profiles', ['profilecode' => $profilecode], '*', MUST_EXIST);
            $examidprofile= $examidcode->examid;
            $examids = $DB->get_record('local_exams', ['id' => $examidprofile], '*', MUST_EXIST);
            $examid = $examids->id;
           
        }

        if (!$DB->record_exists('hall', ['code' => $hallcode], '*', MUST_EXIST)) {
            $throw = new invalid_parameter_exception('Hallcode is not exists: ' . $hallcode);
            throw $throw;
        } else {
            $hailids = $DB->get_record('hall', ['code' => $hallcode], '*', MUST_EXIST);
        }
         if (!$DB->record_exists('local_exam_profiles', ['profilecode' => $profilecode], '*', MUST_EXIST)) {
            $throw = new invalid_parameter_exception('Profiles code is not exists: ' . $profilecode);
            throw $throw;
        } else {
            $profileids = $DB->get_record('local_exam_profiles', ['profilecode' => $profilecode], '*', MUST_EXIST);
        }
        $hailid = $hailids->id;
        
        $profileid = $profileids->id;


        if (!$DB->record_exists('local_exam_attempts', ['examid' => $examid, 'attemptid' => $attemptcount], '*', MUST_EXIST)) {
            //$throw = new invalid_parameter_exception('attempts count is not exists. Please verify attempt count in exam setting: ' . $attemptcount);
           // throw $throw;
            $result->message = 'attempts count is not exists. Please verify attempt count in exam setting: ' . $attemptcount;
            $result->exception = 'Null';
            $result->errorcode = '400';
            $result->debuginfo = 'Null';
            return $result;
        } 
        if($attemptcount!=0){
        $local_exam_attempts = $DB->get_record('local_exam_attempts', ['examid' => $examid,'attemptid' => $attemptcount], '*');
        $exam_attemptscount = $local_exam_attempts->id;
        }else{
        $exam_attemptscount =0;  
        }
        $achievementdates = strtotime($achievementdate);
        if ($achievementdates == 0) {
            $throw = new invalid_parameter_exception('achievementdate date should be correct format: ' . $achievementdate);
            throw $throw;
        }
        $courseid = $examids->courseid;
       // $examdate = $examids->examdatetime;
        $quizid = $profileids->quizid;
        //echo $examdate =date('Y-m-d H:i:s', $examdate/1000);
        //$examdate = date('Y-m-d H:i:s', $examdate);
        $exam_reservationid = $DB->get_record('hallschedule', ['hallid' => $hailid], '*');
        $hallreservationid = $exam_reservationid->id;

      
        $userid = $getuserid->id;
        $studentid = $userid;
        $userid = (explode(" ", $userid));
        $exam_logdata = new stdClass();
        $exam_logdata->examid = $examid;
        $exam_logdata->courseid = $courseid;
        $exam_logdata->userid = $studentid;
        $exam_logdata->hallid = $hailid;
        $schedulerecord = $DB->get_record('hallschedule', ['id' => $hallreservationid]);
        $examdates = !empty($schedulerecord->startdate) ? $schedulerecord->startdate : 0;
        $exam_logdata->examdate = $examdate;
        $exam_logdata->profileid = $profileid;
        $exam_logdata->usercreated = 2;
        $exam_logdata->timecreated = $achievementdates;
        $exam_logdata->hallscheduleid = $hallreservationid;
        $exam_enrollments = $DB->get_record('exam_enrollments', ['examid' => $examid,'profileid' => $profileid, 'userid' => $studentid, 'hallscheduleid' => $hallreservationid], '*');
        if (($exam_enrollments->id) == "") {
            $timestart = 0;
            $timeend = 0;
            $manual = enrol_get_plugin('manual');
            $roleid = $DB->get_field('role', 'id', array('shortname' => 'trainee'));
            $instance = $DB->get_record('enrol', array('courseid' => $courseid, 'enrol' => 'manual'), '*', MUST_EXIST);
            $manual->enrol_user($instance, $studentid, $roleid, $timestart, $timeend);
                  $sql = "SELECT grop.id 
                  FROM {groups} as grop
                  JOIN {local_exam_profiles} as lep ON lep.profilecode = grop.idnumber
                 WHERE lep.id =".$profileid;
        $group = $DB->get_field_sql($sql);
        $groupid = (int) $group;
        if ($groupid) {
            groups_add_member($groupid, $studentid,null,0);
        }
            $id = $DB->insert_record('exam_enrollments', $exam_logdata);
            $result->insertid = $id;
            $exam_completions_logdata = new stdClass();
            $exam_completions_logdata->examid = $examid;
            $exam_completions_logdata->courseid = $courseid;
            $exam_completions_logdata->userid = $studentid;
            $exam_completions_logdata->hallid = $hailid;
            $exam_completions_logdata->usercreated = 2;
            $exam_completions_logdata->completion_status = 0;
            $exam_completions_logdata->completiondate = $examdate;
            $exam_completions_logdata->examdate = $examdate;
            $exam_completions_logdata->profileid = $profileid;
            $exam_completions_logdata->attemptid = $exam_attemptscount;
            $exam_completions_logdata->timecreated = $achievementdates;
            $exam_completions_logdata->hallscheduleid = $hallreservationid;
            $ids = $DB->insert_record('exam_completions', $exam_completions_logdata);

               if ($attemptcount == 1) {
                
                $exam_completions_logdata->attemptid = 0;
            }
            $record= $DB->insert_record('local_exam_userhallschedules', $exam_completions_logdata);


            $gradeget = $DB->get_record('grade_items', ['iteminstance' => $quizid, 'courseid' => $courseid, 'itemmodule' => 'quiz'], '*', MUST_EXIST);
            $gradegetresult = $gradeget->id;
            $gradedata = new stdClass();
            $gradedata->itemid = $gradegetresult;
            $gradedata->userid = $studentid;
            $gradedata->rawgrademax = 100.0000;
            $gradedata->rawgrademin = 0.0000;
            $gradedata->finalgrade = round($grade, 4);
            $gradedata->timecreated = $achievementdates;
            $gradedata->timemodified = $achievementdates;
            $gradedata->aggregationstatus = 'unknown';
            if($grade!='No'){
           
           $idgrade = $DB->insert_record('grade_grades', $gradedata);

            $quiz_data = new stdClass();
            $lastuniqueids=$DB->get_record_sql("SELECT MAX(uniqueid) as lastuniqueid   FROM {quiz_attempts}");
            $lastunique=$lastuniqueids->lastuniqueid;
            
            $quiz_data->quiz = $quizid;
            $quiz_data->userid = $studentid;
            $quiz_data->attempt = $attemptcount;
            $quiz_data->uniqueid = $lastunique+1;
           // $quiz_data->id = $lastunique+1;
            $quiz_data->layout = 0;
            $quiz_data->currentpage = 2;
            $quiz_data->preview = 0;
            $quiz_data->state = 'finished';
            $quiz_data->timestart = $getfinalstarttime;
            $quiz_data->timefinish = $getfinalgetendtime;
            $quiz_data->timemodified = $achievementdates;
            $quiz_data->timemodifiedoffline = 0;
            //$quiz_data->timecheckstate = 'NULL';
            $quiz_data->sumgrades = round($grade, 4);
            $quiz_data->gradednotificationsenttime = $achievementdates;


            $quiz_attempts = $DB->insert_record('quiz_attempts', $quiz_data);
            $updateexam_quiz_attempts = $DB->execute("UPDATE {quiz_attempts} SET id = $lastunique+1 WHERE uniqueid=$lastunique+1");

        if($attemptcount >1)
        {
             $local_exam_attempts = $DB->get_record('tool_products', ['code' => $examcode, 'referenceid' => $exam_attemptscount], '*', MUST_EXIST);
             $productid=$local_exam_attempts->id;
        }
        else{
             
             $local_exam_attempts = $DB->get_record('tool_products', ['code' => $profilecode, 'referenceid' => $profileid], '*', MUST_EXIST);
             $productid=$local_exam_attempts->id;
                
        }
       // $local_exam_attempts = $DB->get_record('tool_products', ['code' => $examcode, 'referenceid' => $exam_attemptscount], '*', MUST_EXIST);
        
            // $productid=$local_exam_attempts->id;
             
           if($attemptcount >1){
              $attemptpurchases_data = new stdClass();
             $attemptpurchases_data->productid = $productid;
             $attemptpurchases_data->examid = $examid;
             $attemptpurchases_data->referenceid = $exam_attemptscount;
             $attemptpurchases_data->referencetable = 'local_exam_attempts';
             $attemptpurchases_data->userid = $studentid;
             $attemptpurchases_data->timecreated =$getfinalgetendtime;
            $quiz_attempts = $DB->insert_record('local_exam_attemptpurchases', $attemptpurchases_data);
    }
         
            

        $updateexam_enrollments = $DB->execute("UPDATE {exam_enrollments} SET examdate = $examdate WHERE examid=$examid and userid=$studentid and profileid=$profileid ");
        $userhallschedules = $DB->execute("UPDATE {local_exam_userhallschedules} SET examdate = $examdate WHERE examid=$examid and userid=$studentid and profileid=$profileid and attemptid=$exam_attemptscount");


                }
        } else {
            $gradeget = $DB->get_record('grade_items', ['iteminstance' => $quizid, 'courseid' => $courseid, 'itemmodule' => 'quiz'], '*', MUST_EXIST);
            $gradegetresult = $gradeget->id;
            //$DB->delete_records('grade_grades', ['itemid' => $gradegetresult]);
            $gradedata = new stdClass();
            $gradedata->itemid = $gradegetresult;
            $gradedata->userid = $studentid;
            $gradedata->rawgrademax = 100.0000;
            $gradedata->rawgrademin = 0.0000;
            $gradedata->finalgrade = round($grade, 4);
            $gradedata->timecreated = $achievementdates;
            $gradedata->timemodified = $achievementdates;
            $gradedata->aggregationstatus = 'unknown';
             if($grade!='No'){

            $result->message = 'Already Exam Completed and certificate: created: examcode:' . $gradegetresult. ' user:' . $grade. ' hallcode:' . $grade;
           /* if(!$DB->record_exists('grade_grades', ['itemid' => $gradegetresult,'userid' => $studentid], '*', MUST_EXIST)){*/
           // $idgrade = $DB->insert_record('grade_grades', $gradedata);
            $quiz_data = new stdClass();
            $lastuniqueids=$DB->get_record_sql("SELECT MAX(uniqueid) as lastuniqueid   FROM {quiz_attempts}");
            $lastunique=$lastuniqueids->lastuniqueid;

            $quiz_data->quiz = $quizid;
            $quiz_data->userid = $studentid;
            $quiz_data->attempt = $attemptcount;
            $quiz_data->uniqueid = $lastunique+1;
            $quiz_data->layout = 0;
            $quiz_data->currentpage = 2;
            $quiz_data->preview = 0;
            $quiz_data->state = 'finished';
            $quiz_data->timestart = $getfinalstarttime;
            $quiz_data->timefinish = $getfinalgetendtime;
            $quiz_data->timemodified = $achievementdates;
            $quiz_data->timemodifiedoffline = 0;
            //$quiz_data->timecheckstate = 'NULL';
            $quiz_data->sumgrades = round($grade, 4);
            $quiz_data->gradednotificationsenttime = $achievementdates;
            $quiz_attempts = $DB->insert_record('quiz_attempts', $quiz_data);
            $updateexam_quiz_attempts = $DB->execute("UPDATE {quiz_attempts} SET id = $lastunique+1 WHERE uniqueid=$lastunique+1");
             
             $local_exam_attempts = $DB->get_record('tool_products', ['code' => $examcode, 'referenceid' => $exam_attemptscount], '*', MUST_EXIST);
             $productid=$local_exam_attempts->id;
             
           if($attemptcount >1){
              $attemptpurchases_data = new stdClass();
             $attemptpurchases_data->productid = $productid;
             $attemptpurchases_data->examid = $examid;
             $attemptpurchases_data->referenceid = $exam_attemptscount;
             $attemptpurchases_data->referencetable = 'local_exam_attempts';
             $attemptpurchases_data->userid = $studentid;
             $attemptpurchases_data->timecreated =$getfinalgetendtime;

            $quiz_attempts = $DB->insert_record('local_exam_attemptpurchases', $attemptpurchases_data);
    }

            

        $updateexam_enrollments = $DB->execute("UPDATE {exam_enrollments} SET examdate = $examdate,timecreated=$achievementdates WHERE examid=$examid and userid=$studentid and profileid=$profileid ");
        


             $result->insertid = $id;
            $exam_completions_logdata = new stdClass();
            $exam_completions_logdata->examid = $examid;
            $exam_completions_logdata->courseid = $courseid;
            $exam_completions_logdata->userid = $studentid;
            $exam_completions_logdata->hallid = $hailid;
            $exam_completions_logdata->usercreated = 2;
            $exam_completions_logdata->completion_status = 0;
            $exam_completions_logdata->completiondate = $achievementdates;
            $exam_completions_logdata->examdate = $examdate;
            $exam_completions_logdata->profileid = $profileid;

            $exam_completions_logdata->attemptid = $exam_attemptscount;
            $exam_completions_logdata->timecreated = $achievementdates;
            $exam_completions_logdata->hallscheduleid = $hallreservationid;
            //$ids = $DB->insert_record('exam_completions', $exam_completions_logdata);
            $record= $DB->insert_record('local_exam_userhallschedules', $exam_completions_logdata);
           
            $userexam_completions = $DB->execute("UPDATE {exam_completions} SET completiondate = $examdate WHERE examid=$examid and userid=$studentid and profileid=$profileid");

            $userhallschedules = $DB->execute("UPDATE {local_exam_userhallschedules} SET examdate = $examdate WHERE examid=$examid and userid=$studentid and profileid=$profileid and attemptid=$exam_attemptscount");

            //}
 $update = $DB->execute("UPDATE {grade_grades} SET finalgrade = round($grade, 4), timecreated  = $achievementdates, timemodified  = $achievementdates  WHERE itemid=$gradegetresult and userid=$studentid");

            }
            if($certificatecode!='No'){
            $result->message = 'Already Exam Completed and certificate: created: examcode:' . $examcode . ' user:' . $username . ' hallcode:' . $hallcode;
             }
             else{
            $result->message = 'Already Exam Completed  examcode:' . $examcode . ' user:' . $username . ' hallcode:' . $hallcode;
             }
            $result->exception = 'Null';
            $result->errorcode = 'Null';
            $result->debuginfo = 'Null';
        }
        if ($id) {
                if($certificatecode!='No'){
            $result->message = 'Exam Completed Successfully and Certificate generated: examcode: ' . $examcode . ' user: ' . $username . ' hallcode: ' . $hallcode;
             }
             else{
             $result->message = 'Exam enrolment Successfully examcode: ' . $examcode . ' user: ' . $username . ' hallcode: ' . $hallcode;
             }
            $result->exception = 'Null';
            $result->errorcode = 'Null';
            $result->debuginfo = 'Null';
        } else {
        }
        $result->course = $id;
        $exams = $DB->get_record('local_exams', array('id' => $examid), 'courseid,certificatevalidity');
        $categoryid = $DB->get_field('course_categories', 'id', ['idnumber' => 'exams']);
        if ($exams->courseid && $categoryid) {
            $contextid = context_coursecat::instance($categoryid);
            if ($contextid) {
                $exm_certificate = $DB->get_field('tool_certificate_templates', 'id', array('contextid' => $contextid->id));
                if (!empty($exm_certificate)) {
                    $status = 1;                              
                    //self::issue_certificate($exam_completions_logdata->userid, $exam_completions_logdata->examid, 'exams', $exm_certificate, $status, $expiresdate = 0);
                    //self::issue_certificate_mig($studentid, $examid, 'exams', $exm_certificate, $status, $expiresdate = 0,$certificatecode,$achievementdate);
                  
                }
            }
        }
        return $result;
    }