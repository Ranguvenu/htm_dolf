    public function add_update_schedule_program($data) {
        global $DB, $USER;
        $row = array();

         $systemcontext = context_system::instance();

  
        $traineeroleid = $DB->get_field('role', 'id', array('shortname' => 'trainee'));
        $row['id'] = $data->id;
        if($data->id > 0 && $DB->record_exists('program_enrollments',array('programid' => $data->trainingid, 'offeringid' => $data->id,'courseid'=>$data->courseid, 'roleid' => $traineeroleid))){
         
           
           $row['availableseats'] = $data->availableseats;
           $row['timemodified'] = time();
           $row['useremodified'] = $USER->id;
           try{
              $transaction = $DB->start_delegated_transaction();
              $record= $DB->update_record('tp_offerings', $row);
               $transaction->allow_commit();
               return $record;
            } catch(moodle_exception $e){
                $transaction->rollback($e);
             return false;

            }
        }
        $row['startdate'] = $data->startdate;
        $row['enddate'] = $data->enddate;
        $row['duration'] = $data->duration;
        $row['time'] = ($data->starttime['hours'] * 3600) + ($data->starttime['minutes'] * 60);
        $duration = $data->enddate - $data->startdate;
        $starttime = ceil($data->startdate + $row['time']);
        $days_between = ceil(abs($duration) / 86400);

        $row['type'] = $data->type;
        $row['availableseats'] = $data->availableseats;
        $row['organization'] = $data->organization;
        $row['sellingprice'] = $data->sellingprice?$data->sellingprice:0;
        $row['actualprice'] = $data->actualprice?$data->actualprice:0;
        $row['trainingid'] = $data->trainingid;
        $row['meetingtype'] = $data->meetingtype ? $data->meetingtype: 0;
        $row['trainingmethod'] = $data->trainingmethod;
        if ($data->trainingmethod == 'online'){
          $row['halladdress'] = 0;
        } else {
           $row['halladdress'] = $data->halladdress;
        }

        $courseid = $DB->get_field('local_trainingprogram', 'courseid', ['id' => $data->trainingid]);

        $program = $DB->get_record('local_trainingprogram',array('id' => $data->trainingid));
        
        if($data->id > 0) {

            $existingcode = $DB->get_field('tp_offerings','code',array('id' => $data->id));
            $code =substr($existingcode, -1);
     
            $row['id'] = $data->id;
            $row['timemodified'] = time();
            $row['useremodified'] = $USER->id;
            if($data->trainingmethod == 'offline' && $code !='R'){
                $updatedcode = substr($existingcode, 0, -1);
                $row['code'] = $updatedcode.'R';
                (new trainingprogram)->update_group_idnumber($existingcode,$row['code'],$courseid);
            } elseif($data->trainingmethod == 'online' && $code !='P') {
                $updatedcode = substr($existingcode, 0, -1);
                $row['code'] = $updatedcode.'P';
                (new trainingprogram)->update_group_idnumber($existingcode,$row['code'],$courseid);
            }
            $attendenceidsql="SELECT ats.attendanceid 
                                    FROM {attendance_sessions} as ats 
                                    JOIN {offering_sessions} ofs ON ats.id=ofs.sessionid 
                                   WHERE ofs.offeringid = $data->id";

            $attendanceid=$DB->get_field_sql($attendenceidsql);
            $sessionidsql ="SELECT sessionid 
                              FROM {offering_sessions}
                              WHERE offeringid = $data->id";
            $sessionids=$DB->get_records_sql($sessionidsql);
            $existingsessionstartdate=$DB->get_field_sql("SELECT ats.sessdate 
                                    FROM {attendance_sessions} as ats 
                                    JOIN {offering_sessions} ofs ON ats.id=ofs.sessionid 
                                   WHERE ofs.offeringid = $data->id ORDER BY ofs.id ASC LIMIT 1"); 
            $existingsessionenddate=$DB->get_field_sql("SELECT ats.sessdate 
                                    FROM {attendance_sessions} as ats 
                                    JOIN {offering_sessions} ofs ON ats.id=ofs.sessionid 
                                   WHERE ofs.offeringid = $data->id ORDER BY ofs.id DESC LIMIT 1");

            $existingofferingrecord = $DB->get_record('tp_offerings',array('id'=>$data->id));

            if(empty($row['code']) || is_null($row['code'])) {
                
                $offering_code = $existingofferingrecord->code;

            } else {
              
              $offering_code = $row['code'];

            }

        
            try{
              $transaction = $DB->start_delegated_transaction();

              $createoffering = new \local_trainingprogram\local\createoffering($courseid,$offering_code,$data,$program);

              
              $row['meetingid'] = $createoffering->meetingid ? $createoffering->meetingid : 0;
              
                if ($data->trainingmethod == 'online') {
                    foreach ($sessionids AS $sessionid) {
                        (new trainingprogram)->delete_session_record($sessionid->sessionid);
                    }       
                }else{
                     if($existingofferingrecord->trainingmethod == 'offline' && ($existingsessionstartdate != $data->startdate ||  $existingsessionenddate != $data->enddate)){
                        foreach ($sessionids AS $sessionid) {
                            (new trainingprogram)->delete_session_record($sessionid->sessionid);
                        }
                        for($i=0; $i <= $days_between; $i++){
                            $sessionid=mod_attendance_external::add_session($attendanceid,'',  $starttime,$data->duration, 0,true);
                            (new trainingprogram)->insert_offering_session_record($sessionid['sessionid'],$data->id,$starttime,$data->trainingid,$courseid);
                            $starttime += 86400;
                        }
                    } else {

                        $createoffering->createattendance();
                        $row['groupid'] = $createoffering->groupid;
                        $row['sections'] = $createoffering->sectionid;
                        
                        $evaluationmethods = explode(',', $program->evaluationmethods);
                        foreach($evaluationmethods as $method ){
                            if($method == 0){
                                $type = 'prequiz';
                            }else if($method == 1){
                                $type = 'postquiz';
                            }else{
                                continue;
                            }
                            $row[$type] = $createoffering->createquiz(dataprovider::$evaluationmethods[$method],$data);
                        }


                        for($i=0; $i <= $days_between; $i++){
                            $sessionid=mod_attendance_external::add_session($createoffering->attendanceid,'',  $starttime,$data->duration, 0,true);
                            (new trainingprogram)->insert_offering_session_record($sessionid['sessionid'],$data->id,$starttime,$data->trainingid,$courseid);
                            $starttime += 86400;
                       }

                    } 
                }

               
              $record= $DB->update_record('tp_offerings', $row);

              $event = \local_trainingprogram\event\tpofferings_updated::create(array( 'context'=>$systemcontext, 'objectid' =>$data->id));
                $event->trigger();
                
              $transaction->allow_commit();
               return $record;
            } catch(moodle_exception $e){
                $transaction->rollback($e);
             return false;

            }
        } else {
            $row['timecreated'] = time();
            $row['usercreated'] = $USER->id;
            $tpoid = $DB->get_field_sql('SELECT id FROM {tp_offerings} ORDER BY id DESC LIMIT 1');
            if ($tpoid) {
                $autoincnum = $tpoid+1;
            } else {
                $autoincnum = 1;
            }
            $num = sprintf("%'.02d", $autoincnum);
            $incnum = $num + 1;
            $tpodate = date('dmY',$data->enddate);
            if($data->trainingmethod == 'online'){
                $trmethod='P';
            } else {
                $trmethod='R';
            }
            
            $ofcode = $data->trainingid.'-'.$tpodate.'-'.$num.'-'.$trmethod;
            if($DB->record_exists('tp_offerings',array('code' => $ofcode))) {
                $row['code'] = $data->trainingid.'-'.$tpodate.'-'.$incnum.'-'.$trmethod;
            } else {
                $row['code'] = $data->trainingid.'-'.$tpodate.'-'.$num.'-'.$trmethod;
            }
            // $courseid = $DB->get_field('local_trainingprogram', 'courseid', ['id' => $data->trainingid]); 
             
            try {
                $transaction = $DB->start_delegated_transaction();
                if($courseid){
                    $createoffering = new \local_trainingprogram\local\createoffering($courseid, $row['code'],$data,$program);
                    if($data->trainingmethod == 'offline'){

                        $createoffering->createattendance();
                        $evaluationmethods = explode(',', $program->evaluationmethods);
                        foreach($evaluationmethods as $method ){
                            if($method == 0){
                                $type = 'prequiz';
                            }else if($method == 1){
                                $type = 'postquiz';
                            }else{
                                continue;
                            }
                            $row[$type] = $createoffering->createquiz(dataprovider::$evaluationmethods[$method],$data);
                        }


                    }
                   
                    $row['groupid'] = $createoffering->groupid;
                    $row['sections'] = $createoffering->sectionid;
                    $row['meetingid'] = $createoffering->meetingid ? $createoffering->meetingid: 0;
                    
                    try{
                      $record->id = $DB->insert_record('tp_offerings', $row);

                      if($createoffering->attendanceid){

                        for($i=0; $i <= $days_between; $i++){
                            $sessionid=mod_attendance_external::add_session($createoffering->attendanceid,'',  $starttime,$data->duration, 0,true);
                            (new trainingprogram)->insert_offering_session_record($sessionid['sessionid'],$record->id,$starttime,$data->trainingid,$courseid);
                            $starttime += 86400;
                        }

                      }

                       

                      $draftrecords = $DB->get_records('reservations_draft', ['entitycode' => $data->entitycode]);
                        foreach($draftrecords AS $draftrecord) {
                            $draftdata = new stdClass();
                            $draftdata->typeid = $record->id;
                            $draftdata->hallid = $draftrecord->hallid;
                            $draftdata->seats = $draftrecord->seats;
                            $draftdata->examdate = $draftrecord->date;
                            $draftdata->slotstart = $draftrecord->slotstart;
                            $draftdata->slotend = $draftrecord->slotend;
                            $draftdata->userid = $draftrecord->userid;
                            $draftdata->type = 'tprogram';
                            $draftdata->status = 1;
                            $DB->insert_record('hall_reservations', $draftdata);
                        }
                        $sessionkey = $DB->delete_records('reservations_draft', ['entitycode' => $data->entitycode, 'type' => 'tprogram']);
                    } catch(moodle_exception $e){
                      print_r($e);
                    }
                    
                }
                $systemcontext = context_system::instance();
                $event = \local_trainingprogram\event\tpofferings_created::create(array( 'context'=>$systemcontext, 'objectid' =>$record->id));
                $event->trigger();
                $transaction->allow_commit();
                return $record;
            } catch(Exception $e) {
                $transaction->rollback($e);
                return false;
            }

        }
    }