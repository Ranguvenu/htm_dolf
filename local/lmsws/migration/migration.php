<?php

require_once(dirname(__FILE__) . '/../lms_webservice.php');
require_once($CFG->dirroot . '/local/trainingprogram/lib.php');
require_once($CFG->dirroot . '/course/lib.php');
require_once($CFG->libdir . '/completionlib.php');
require_once($CFG->dirroot . '/mod/attendance/externallib.php');
require_once($CFG->dirroot . '/mod/zoom/lib.php');
require_once($CFG->dirroot . '/mod/zoom/locallib.php');
require_once($CFG->dirroot . '/user/lib.php');
require_once($CFG->dirroot . '/group/lib.php');
require_once($CFG->dirroot . '/lib/moodlelib.php');
require_once($CFG->dirroot . '/admin/tool/certificate/classes/template.php');
use stdClass;
use dml_exception;
use html_writer;
use moodle_url;
use context_system;
use tabobject;
use user_create_user;
use context_user;
use core_user;
use filters_form;
use mod_attendance_external;
use local_exams\local\exams;
use local_trainingprogram\local\createoffering;
use local_trainingprogram\local\trainingprogram as tp;
use single_button;

// require_once($CFG->dirroot . '/local/trainingprogram/filters_form.php');
class lms_webservice_migration extends lms_webservice
{

    /**
     * Returns description of method parameters
     *
     * @return external_function_parameters
     * @since Moodle 2.2
     */

    public static function create_offering_parameters()
    {
        return new external_function_parameters(
            array(

                'trainingcode' => new external_value(PARAM_RAW, 'trainingcode'),
                'pstartdate' => new external_value(PARAM_RAW, 'pstartdate'),
                'penddate' => new external_value(PARAM_RAW, 'penddate'),

                'startdate' => new external_value(PARAM_RAW, 'startdate'),
                'enddate' => new external_value(PARAM_RAW, 'enddate'),
                'hours' => new external_value(PARAM_RAW, 'hours'),
                'minutes' => new external_value(PARAM_RAW, 'minutes'),
                'duration' => new external_value(PARAM_RAW, 'duration'),
                'type' => new external_value(PARAM_RAW, 'type'),
                'organization' => new external_value(PARAM_RAW, 'organization'),
                'availableseats' => new external_value(PARAM_RAW, 'availableseats'),
                'trainingmethod' => new external_value(PARAM_RAW, 'trainingmethod'),
                'halladdress' => new external_value(PARAM_RAW, 'halladdress'),
            )
        );
    }

    /**
     * Return the list of page question attempts in a given lesson.
     *
     * @param int $lessonid lesson instance id
     * @param int $attempt the lesson attempt number
     * @param bool $correct only fetch correct attempts
     * @param int $pageid only fetch attempts at the given page
     * @param int $userid only fetch attempts of the given user
     * @return array of warnings and page attempts
     * @since Moodle 3.3
     * @throws moodle_exception
     */
    public static function create_offering($trainingcode, $pstartdate, $penddate, $startdate, $enddate, $hours, $minutes, $duration, $type, $organization, $availableseats, $trainingmethod, $halladdress)
    {
        global $DB, $USER;

        // $data->id=$id;
// $trainingidprogram = $DB->get_record('local_trainingprogram',array('code' => $trainingcode));
// $trainingid=$trainingidprogram->id;
// $courseid=$trainingidprogram->courseid;
// $data->trainingid=$trainingid;
// $data->pstartdate=$pstartdate;
// $data->penddate=$penddate;
// $data->courseid=$courseid;

        // $data->startdate=$startdate;
// $data->enddate=$enddate;
// $data->hours=$hours;
// $data->minutes=$minutes;
// $data->duration=$duration;
// $data->type=$type;
// $data->availableseats=$availableseats;
// $data->trainingmethod=$trainingmethod;
// $data->organization=$organization;
// $data->halladdress=$halladdress;
//     // $params = self::validate_parameters(self::jobstudentoffstatus_parameters(), $params);
//       $warnings = array();
//       global $DB, $USER;
//       $row = array();
//         $systemcontext = context_system::instance();
//         $traineeroleid = $DB->get_field('role', 'id', array('shortname' => 'trainee'));
//         if($data->id > 0 && $DB->record_exists('program_enrollments',array('programid' => $data->trainingid, 'offeringid' => $data->id,'courseid'=>$data->courseid, 'roleid' => $traineeroleid))){           
//            $row['availableseats'] = $data->availableseats;
//            $row['timemodified'] = time();
//            $row['useremodified'] = $USER->id;
//            try{
//               $transaction = $DB->start_delegated_transaction();
//               $record= $DB->update_record('tp_offerings', $row);
//                $transaction->allow_commit();
//                return $record;
//             } catch(moodle_exception $e){
//                 $transaction->rollback($e);
//              return false;
//             }
//         }
//         $row['startdate'] = $data->startdate;
//         $row['enddate'] = $data->enddate;
//         $row['duration'] = $data->duration;
//         $row['time'] = ($data->hours * 3600) + ($data->minutes * 60);
//         $duration = $data->enddate - $data->startdate;
//         $starttime = ceil($data->startdate + $row['time']);
//         $days_between = ceil(abs($duration) / 86400);
//         $row['type'] = $data->type;
//         $row['availableseats'] = $data->availableseats;
//         $row['organization'] = $data->organization;
//         $row['sellingprice'] = $data->sellingprice?$data->sellingprice:0;
//         $row['actualprice'] = $data->actualprice?$data->actualprice:0;
//         $row['trainingid'] = $data->trainingid;
//         $row['meetingtype'] = $data->meetingtype ? $data->meetingtype: 0;
//         $row['trainingmethod'] = $data->trainingmethod;
//         if ($data->trainingmethod == 'online'){
//           $row['halladdress'] = 0;
//         } else {
//            $row['halladdress'] = $data->halladdress;
//         }
//         $courseid = $DB->get_field('local_trainingprogram', 'courseid', ['id' => $data->trainingid]);
//         $program = $DB->get_record('local_trainingprogram',array('id' => $data->trainingid));
//         if($data->id > 0) {
//             $existingcode = $DB->get_field('tp_offerings','code',array('id' => $data->id));
//             $code =substr($existingcode, -1);
//             $row['timemodified'] = time();
//             $row['useremodified'] = $USER->id;
//             if($data->trainingmethod == 'offline' && $code !='R'){
//                 $updatedcode = substr($existingcode, 0, -1);
//                 $row['code'] = $updatedcode.'R';
//                 (new trainingprogram)->update_group_idnumber($existingcode,$row['code'],$courseid);
//             } elseif($data->trainingmethod == 'online' && $code !='P') {
//                 $updatedcode = substr($existingcode, 0, -1);
//                 $row['code'] = $updatedcode.'P';
//                 (new trainingprogram)->update_group_idnumber($existingcode,$row['code'],$courseid);
//             }
//             $attendenceidsql="SELECT ats.attendanceid 
//                                     FROM {attendance_sessions} as ats 
//                                     JOIN {offering_sessions} ofs ON ats.id=ofs.sessionid 
//                                    WHERE ofs.offeringid = $data->id";
//             $attendanceid=$DB->get_field_[10:18] Salma Bader Facademy@0123$$ingid = $data->id";
//             $sessionids=$DB->get_records_sql($sessionidsql);
//             $existingsessionstartdate=$DB->get_field_sql("SELECT ats.sessdate 
//                                     FROM {attendance_sessions} as ats 
//                                     JOIN {offering_sessions} ofs ON ats.id=ofs.sessionid 
//                                    WHERE ofs.offeringid = $data->id ORDER BY ofs.id ASC LIMIT 1"); 
//             $existingsessionenddate=$DB->get_field_sql("SELECT ats.sessdate 
//                                     FROM {attendance_sessions} as ats 
//                                     JOIN {offering_sessions} ofs ON ats.id=ofs.sessionid 
//                                    WHERE ofs.offeringid = $data->id ORDER BY ofs.id DESC LIMIT 1");

        //             $existingofferingrecord = $DB->get_record('tp_offerings',array('id'=>$data->id));

        //             if(empty($row['code']) || is_null($row['code'])) {

        //                 $offering_code = $existingofferingrecord->code;

        //             } else {

        //               $offering_code = $row['code'];

        //             }


        //             try{
//               $transaction = $DB->start_delegated_transaction();

        //               $createoffering = new \local_trainingprogram\local\createoffering($courseid,$offering_code,$data,$program);


        //               $row['meetingid'] = $createoffering->meetingid ? $createoffering->meetingid : 0;

        //                 if ($data->trainingmethod == 'online') {
//                     foreach ($sessionids AS $sessionid) {
//                         (new trainingprogram)->delete_session_record($sessionid->sessionid);
//                     }       
//                 }else{
//                      if($existingofferingrecord->trainingmethod == 'offline' && ($existingsessionstartdate != $data->startdate ||  $existingsessionenddate != $data->enddate)){
//                         foreach ($sessionids AS $sessionid) {
//                             (new trainingprogram)->delete_session_record($sessionid->sessionid);
//                         }
//                         for($i=0; $i <= $days_between; $i++){
//                             $sessionid=mod_attendance_external::add_session($attendanceid,'',  $starttime,$data->duration, 0,true);

        //                             (new trainingprogram)->insert_offering_session_record($sessionid['sessionid'],$data->id,$starttime,$data->trainingid,$courseid);
//                             $starttime += 86400;
//                         }
//                     } else {

        //                         $createoffering->createattendance();
//                         $row['groupid'] = $createoffering->groupid;
//                         $row['sections'] = $createoffering->sectionid;

        //                         $evaluationmethods = explode(',', $program->evaluationmethods);
//                         foreach($evaluationmethods as $method ){
//                             if($method == 0){
//                                 $type = 'prequiz';
//                             }else if($method == 1){
//                                 $type = 'postquiz';
//                             }else{
//                                 continue;
//                             }
//                           //  $row[$type] = $createoffering->createquiz(dataprovider::$evaluationmethods[$method],$data);
//                         }


        //                         for($i=0; $i <= $days_between; $i++){
//                             $sessionid=mod_attendance_external::add_session($createoffering->attendanceid,'',  $starttime,$data->duration, 0,true);
//                             (new trainingprogram)->insert_offering_session_record($sessionid['sessionid'],$data->id,$starttime,$data->trainingid,$courseid);
//                             $starttime += 86400;
//                        }

        //                     } 
//                 }


        //               $record= $DB->update_record('tp_offerings', $row);

        //               $event = \local_trainingprogram\event\tpofferings_updated::create(array( 'context'=>$systemcontext, 'objectid' =>$data->id));
//                 $event->trigger();

        //               $transaction->allow_commit();
//               // return $record;
//             } catch(moodle_exception $e){
//                 $transaction->rollback($e);
//              return false;

        //             }
//         } else {
//             $row['timecreated'] = time();
//             $row['usercreated'] = $USER->id;
//             $tpoid = $DB->get_field_sql('SELECT id FROM {tp_offerings} ORDER BY id DESC LIMIT 1');
//             if ($tpoid) {
//                 $autoincnum = $tpoid+1;
//             } else {
//                 $autoincnum = 1;
//             }
//             $num = sprintf("%'.02d", $autoincnum);
//             $incnum = $num + 1;
//             $tpodate = date('dmY',$data->enddate);
//             if($data->trainingmethod == 'online'){
//                 $trmethod='P';
//             } else {
//                 $trmethod='R';
//             }

        //             $ofcode = $data->trainingid.'-'.$tpodate.'-'.$num.'-'.$trmethod;
//             if($DB->record_exists('tp_offerings',array('code' => $ofcode))) {
//                 $row['code'] = $data->trainingid.'-'.$tpodate.'-'.$incnum.'-'.$trmethod;
//             } else {
//                 $row['code'] = $data->trainingid.'-'.$tpodate.'-'.$num.'-'.$trmethod;
//             }[10:18] Salma Bader Facademy@0123$$
//                     $createoffering = new \local_trainingprogram\local\createoffering($courseid, $row['code'],$data,$program);
//                     if($data->trainingmethod == 'offline'){

        //                         $createoffering->createattendance();
//                         $evaluationmethods = explode(',', $program->evaluationmethods);
//                         foreach($evaluationmethods as $method ){
//                             if($method == 0){
//                                 $type = 'prequiz';
//                             }else if($method == 1){
//                                 $type = 'postquiz';
//                             }else{
//                                 continue;
//                             }
//                            // $row[$type] = $createoffering->createquiz(dataprovider::$evaluationmethods[$method],$data);
//                         }


        //                     }

        //                     $row['groupid'] = $createoffering->groupid;
//                     $row['sections'] = $createoffering->sectionid;
//                     $row['meetingid'] = $createoffering->meetingid ? $createoffering->meetingid: 0;

        //                     try{




        //                       $record->id = $DB->insert_record('tp_offerings', $row);



        //                       if($createoffering->attendanceid){

        //                         for($i=0; $i <= $days_between; $i++){
//                             $sessionid=mod_attendance_external::add_session($createoffering->attendanceid,'',  $starttime,$data->duration, 0,true);
//                             //(new trainingprogram)->insert_offering_session_record($sessionid['sessionid'],$record->id,$starttime,$data->trainingid,$courseid);
//                             $starttime += 86400;
//                         }

        //                       }



        //                     /*  $draftrecords = $DB->get_records('reservations_draft', ['entitycode' => $data->entitycode]);
//                         foreach($draftrecords AS $draftrecord) {
//                             $draftdata = new stdClass();
//                             $draftdata->typeid = $record->id;
//                             $draftdata->hallid = $draftrecord->hallid;
//                             $draftdata->seats = $draftrecord->seats;
//                             $draftdata->examdate = $draftrecord->date;
//                             $draftdata->slotstart = $draftrecord->slotstart;
//                             $draftdata->slotend = $draftrecord->slotend;
//                             $draftdata->userid = $draftrecord->userid;
//                             $draftdata->type = 'tprogram';
//                             $draftdata->status = 1;
//                             $DB->insert_record('hall_reservations', $draftdata);
//                         }
//                         $sessionkey = $DB->delete_records('reservations_draft', ['entitycode' => $data->entitycode, 'type' => 'tprogram']);*/
//                     } catch(moodle_exception $e){
//                       print_r($e);
//                     }

        //                 }
//                 $systemcontext = context_system::instance();

        //                 $event = \local_trainingprogram\event\tpofferings_created::create(array( 'context'=>$systemcontext, 'objectid' =>$record->id));

        //                 $event->trigger();
//                 $transaction->allow_commit();
//               //  return $record;
//             } catch(Exception $e) {
//                 $transaction->rollback($e);
//                 return false;
//             }

        //         }

        //          if($record->id){
//                         $results = array();
//                         $results['id'] = $record->id;
//                         $results['status'] = $row['code']." is created successfully" ;
//                         $results['type'] = $record->id;
//                       }else{
//                         $results = array();
//                         $results['type'] = $record->id; 
//                         $results['status'] = $row['code']." is created successfully" ;
//                       }

        //      return $results;

        $trainingidprogram = $DB->get_record('local_trainingprogram', array('code' => $trainingcode));
        $trainingid = $trainingidprogram->id;
        $courseid = $trainingidprogram->courseid;
        $data->trainingid = $trainingid;
        $data->pstartdate = $pstartdate;
        $data->penddate = $penddate;
        $data->courseid = $courseid;
        $data->startdate = strtotime($startdate);
        $data->enddate = strtotime($enddate);
        $data->starttime['hours'] = $hours;
        $data->starttime['minutes'] = $minutes;
        $data->duration = $duration;
        $data->type = $type;
        $data->availableseats = $availableseats;
        $data->trainingmethod = $trainingmethod;
        $data->organization = $organization;
        $data->halladdress = $halladdress;
        $record = (new tp)->add_update_schedule_program($data);
        if ($record->id) {
            $results = array();
            $results['id'] = $record->id;
            $results['status'] = $row['code'] . " is created successfully";
            $results['type'] = $record->id;
        } else {
            $results = array();
            $results['type'] = $record->id;
            $results['status'] = $row['code'] . " is created successfully";
        }

        return $results;

    }
    public static function create_offering_returns()
    {
        return new external_single_structure(
            array(
                'id' => new external_value(PARAM_RAW, 'recordstatus'),
                'status' => new external_value(PARAM_RAW, 'recordstatus'),


            )
        );
    }
    // exam upload



    public static function userenrollorg_parameters()
    {
        return new external_function_parameters(
            array(
                'offeringcode' => new external_value(PARAM_RAW, 'offeringid'),
                'username' => new external_value(PARAM_RAW, 'username'),
                'attendanceid' => new external_value(PARAM_RAW, 'attendanceid'),
                'programcode' => new external_value(PARAM_RAW, 'programcode'),
                'seat' => new external_value(PARAM_RAW, 'seat'),
                'paymenttype' => new external_value(PARAM_RAW, 'paymenttype'),
            )
        );
    }

    /**
     * Return the list of page question attempts in a given lesson.
     *
     * @param int $lessonid lesson instance id
     * @param int $attempt the lesson attempt number
     * @param bool $correct only fetch correct attempts
     * @param int $pageid only fetch attempts at the given page
     * @param int $userid only fetch attempts of the given user
     * @return array of warnings and page attempts
     * @since Moodle 3.3
     * @throws moodle_exception
     */
    public static function userenrollorg($offeringcode, $username, $attendanceid, $programcode, $seat, $paymenttype)
    {
        global $OUTPUT, $PAGE, $DB, $USER;
        $getuserid = $DB->get_record('user', ['username' => $username], '*', MUST_EXIST);
        $userid = $getuserid->id;
        $studentid = $userid;
        $userid = (explode(" ", $userid));
        $type = 'program_enrol';
        $roleid = $DB->get_field('role', 'id', ['shortname' => 'trainee']);
        $offeringcodeid = $DB->get_record('tp_offerings', ['code' => $offeringcode], '*', MUST_EXIST);
        $tp_offeringid = $offeringcodeid->id;
        $offeringid = (int) $tp_offeringid;
        $systemcontext = context_system::instance();
        $traineeeid = $DB->get_field('role', 'id', array('shortname' => 'trainee'));
        $totalseatssql = " SELECT  tp.availableseats AS total
                                     FROM {tp_offerings} tp
                                    WHERE tp.id = $offeringid";
        $total = $DB->get_field_sql($totalseatssql);
        if (!isloggedin() || (isloggedin() && (is_siteadmin() || has_capability('local/organization:manage_trainingofficial', $systemcontext)))) {
            $enrolled = $DB->get_field_sql(" SELECT count(userid) AS total
                              FROM {program_enrollments} AS pe
                             WHERE pe.offeringid = $offeringid AND pe.roleid = $traineeeid  AND pe.usercreated = $USER->id ");
            $purchasedseats = $DB->get_field_sql("SELECT SUM(purchasedseats) 
                         FROM {tool_org_order_seats} 
                        WHERE tablename = 'tp_offerings' AND fieldname = 'id' AND fieldid = $offeringid");
            $seats = $total - ($enrolled + $purchasedseats);
        } else {
            $available_seats = $DB->get_field_sql("SELECT SUM(availableseats)  
                                                         FROM {tool_org_order_seats} 
                                                        WHERE tablename = 'tp_offerings' AND fieldname = 'id' AND fieldid = $offeringid AND orguserid = $USER->id ");
            if ($available_seats > 0) {
                $seats = $available_seats;
            } else {
                $purchasedseats = $DB->get_field_sql("SELECT SUM(purchasedseats) 
                             FROM {tool_org_order_seats} 
                             WHERE tablename = 'tp_offerings' AND fieldname = 'id' AND fieldid = $offeringid");
                $enrolled = $DB->get_field_sql(" SELECT count(userid) AS total
                                  FROM {program_enrollments} AS pe
                                 WHERE pe.offeringid = $offeringid AND pe.roleid = $traineeeid  AND pe.usercreated = $USER->id ");
                $seats = $total - ($enrolled + $purchasedseats);
            }
        }
        $availableseats = $seats;
        $userstoassign = $userid;
        $program = $DB->get_record_sql('SELECT * FROM {local_trainingprogram} as tp 
                                         JOIN {tp_offerings} as tpo ON tpo.trainingid = tp.id 
                                        WHERE tpo.id=:offeringid', ['offeringid' => $offeringid]);
        $course = $DB->get_record('course', ['id' => $program->courseid], '*', MUST_EXIST);
        $groupid = $DB->get_field_sql('SELECT g.id FROM {groups} as g JOIN {tp_offerings} as tpo on g.idnumber=tpo.code where tpo.id=:id', array('id' => $offeringid));
        if (sizeof($userstoassign) > $availableseats) {
            echo "<div class='alert alert-info'>" . get_string('userscountismore', 'local_exams', $availableseats) . "</div>";
        } else {
            if (!empty($userstoassign)) {
                $progress = 0;
                $progressbar = new \core\progress\display_if_slow(get_string('enrollusers', 'local_trainingprogram', $course->fullname));
                //$progressbar->start_html();
                $progressbar->start_progress('', count($userstoassign) - 1);
                foreach ($userstoassign as $key => $adduser) {
                    $progressbar->progress($progress);
                    $progress++;

                    $program_enrollments = $DB->get_record('program_enrollments', ['offeringid' => $offeringid, 'userid' => $adduser], '*');
                    if (($program_enrollments->id) == "") {
                        (new \local_trainingprogram\local\trainingprogram)->program_enrollment($offeringid, $adduser);
                        $result = new stdClass();
                        $result->changecount = $progress;
                        $result->coursename = $course->fullname . "and course purchased";
                        ;
                        $result->enrolment = 'success';
                    } else {
                        $result = new stdClass();
                        $result->changecount = $progress;
                        $result->coursename = $course->fullname . "and course already purchased";
                        $result->enrolment = 'success';
                    }
                }
                //$progressbar->end_html();                 
                $time = time();
                $SQL2 = "SELECT ats.id as atsid, ass.id as assid
                FROM {attendance_sessions} ats 
                JOIN {attendance_statuses} ass ON ass.attendanceid = ats.attendanceid
                WHERE ass.attendanceid=$attendanceid and ass.acronym='p'";
                $getrecords = $DB->get_records_sql($SQL2);

                foreach ($getrecords as $key => $values) {
                    $sessionid = $values->atsid;
                    $statusid = $values->assid;
                    $attendance_logdata = new stdClass();
                    $attendance_logdata->sessionid = $sessionid;
                    $attendance_logdata->statusid = $statusid;
                    $attendance_logdata->studentid = $studentid;
                    $attendance_logdata->statusset = '';
                    $attendance_logdata->timetaken = time();
                    $attendance_logdata->takenby = 2;
                    $DB->insert_record('attendance_log', $attendance_logdata);

                }
                $currenttime = time();
                $program_completionsdata = new stdClass();
                $programid = $DB->get_field('local_trainingprogram', 'id', ['code' => $programcode]);
                $productsid = $DB->get_field('tool_products', 'id', ['referenceid' => $offeringid]);
                $productsprice = $DB->get_field('tool_products', 'price', ['referenceid' => $programcode]);
                $program_completionsdata->programid = $programid;
                $program_completionsdata->completion_status = 1;
                $program_completionsdata->preexam_completion_status = 1;
                $program_completionsdata->postexam_completion_status = 1;
                $program_completionsdata->completiondate = $currenttime;
                $getadminuserid = $DB->get_field('user', 'id', ['username' => 'admin']);
                $program_completionsdata->usercreated = $getadminuserid;
                $program_completionsdata->timecreated = $currenttime;
                $program_completionsdata->usermodified = $currenttime;
                $program_completionsdata->timemodified = $currenttime;
                $program_completionsdata->offeringid = $attendanceid;
                $program_completionsdata->userid = $studentid;
                $DB->insert_record('program_completions', $program_completionsdata);
                $program_org = new stdClass();
                $program_org->productid = $productsid;
                $program_org->tablename = 'tp_offerings';
                $program_org->fieldname = 'id';
                $program_org->fieldid = $offeringid;
                $program_org->orguserid = $studentid;
                $program_org->paymenttype = $paymenttype;
                $program_org->paymenton = $currenttime;
                $program_org->amount = $productsprice;
                $program_org->purchasedseats = $seat;
                $program_org->paymentapprovalstatus = 0;
                $program_org->approvaluserid = 0;
                $program_org->approvalon = 0;
                $program_org->amountrecived = 0;
                $program_org->transactionid = 0;
                $program_org->checkid = 0;
                $program_org->transactionnote = 0;
                $program_org->usercreated = $studentid;
                $program_org->timecreated = $currenttime;
                $program_org->usermodified = $studentid;
                $program_org->timemodified = $currenttime;
                $DB->insert_record('tool_org_order_payments', $program_org);

                $order_approval = new stdClass();
                $order_approval->productid = $productsid;
                $order_approval->tablename = 'tp_offerings';
                $order_approval->fieldname = 'id';
                $order_approval->fieldid = $offeringid;
                $order_approval->orguserid = $studentid;
                $order_approval->purchasedseats = $seat;
                $order_approval->approvalseats = 0;
                $order_approval->approvaluserid = 0;
                $order_approval->usercreated = $studentid;
                $order_approval->timecreated = $currenttime;
                $order_approval->usermodified = $studentid;
                $order_approval->timemodified = $currenttime;
                $DB->insert_record('tool_order_approval_seats', $order_approval);




                $courseid = $DB->get_field('local_trainingprogram', 'courseid', array('id' => $program_completionsdata->programid));

                $categoryid = $DB->get_field('course_categories', 'id', ['idnumber' => 'trainingprogram']);
                $contextid = context_coursecat::instance($categoryid);
                $prgrm_certificate = $DB->get_field('tool_certificate_templates', 'id', array('contextid' => $contextid->id));
                $status = 1;
                if ($paymenttype == 'telr') {
                    self::issue_certificate($program_completionsdata->userid, $program_completionsdata->programid, 'trainingprogram', $prgrm_certificate, $status, $expiresdate = 0);
                }
                $update = $DB->execute("UPDATE {attendance_sessions} SET lasttaken = $time, lasttakenby  = 2,  description = 'Regular class session'  WHERE attendanceid='$attendanceid'");

                return $result;
            }
        }







    }


    public static function userenrollorg_returns()
    {
        return new external_single_structure(
            array(
                'coursename' => new external_value(PARAM_RAW, 'course'),
                'enrolment' => new external_value(PARAM_RAW, 'enrolment'),


            )
        );
    }


    public static function userenrollonly_parameters()
    {
        return new external_function_parameters(
            array(
                'offeringcode' => new external_value(PARAM_RAW, 'offeringid'),
                'username' => new external_value(PARAM_RAW, 'username'),
                'enrolldate' => new external_value(PARAM_RAW, 'enrolldate'),
                //'achievementdate' => new external_value(PARAM_RAW, 'enrolldate'),
               // 'certificatecode' => new external_value(PARAM_RAW, 'certificatecode'),
                'apistatus' => new external_value(PARAM_RAW, 'apistatus'),

            )
        );
    }

 public static function userenrollonly($offeringcode, $username, $enrolldate,$apistatus)
    {
           global $OUTPUT, $PAGE, $DB, $USER,$CFG;
        $CFG->debug = (E_ALL | E_STRICT);   // === DEBUG_DEVELOPER - NOT FOR PRODUCTION SERVERS!
        $CFG->debugdisplay = 1;
        if (!$DB->record_exists('user', ['username' => $username], '*', MUST_EXIST)) {

            $throw = new invalid_parameter_exception('Username is not exists: ' . $username);
            throw $throw;
        } else {
            $getuserid = $DB->get_record('user', ['username' => $username], '*', MUST_EXIST);
        }
        $userid = $getuserid->id;
        $userids = $getuserid->id;
        $studentid = $userid;
        $userid = (explode(" ", $userid));
        $type = 'program_enrol';
        $roleid = $DB->get_field('role', 'id', ['shortname' => 'trainee']);
        

        if (!$DB->record_exists('tp_offerings', ['code' => $offeringcode], '*', MUST_EXIST)) {

            $throw = new invalid_parameter_exception('offeringcode is not exists: ' . $offeringcode);
            throw $throw;
        } else {
            $offeringcodeid = $DB->get_record('tp_offerings', ['code' => $offeringcode], '*', MUST_EXIST);
        }
        $tp_offeringid = $offeringcodeid->id;
        $training_id = $offeringcodeid->trainingid;
        $trainingmethod = $offeringcodeid->trainingmethod;        
        $offeringid = (int) $tp_offeringid;
        $systemcontext = context_system::instance();
        $traineeeid = $DB->get_field('role', 'id', array('shortname' => 'trainee'));
        $totalseatssql = " SELECT  tp.availableseats AS total
                                     FROM {tp_offerings} tp
                                    WHERE tp.id = $offeringid";
        $total = $DB->get_field_sql($totalseatssql);
        if (!isloggedin() || (isloggedin() && (is_siteadmin() || has_capability('local/organization:manage_trainingofficial', $systemcontext)))) {
            $enrolled = $DB->get_field_sql(" SELECT count(userid) AS total
                              FROM {program_enrollments} AS pe
                             WHERE pe.offeringid = $offeringid AND pe.roleid = $traineeeid  AND pe.usercreated = $USER->id ");
            $purchasedseats = $DB->get_field_sql("SELECT SUM(purchasedseats) 
                         FROM {tool_org_order_seats} 
                        WHERE tablename = 'tp_offerings' AND fieldname = 'id' AND fieldid = $offeringid");
            $seats = $total - ($enrolled + $purchasedseats);
        } else {
            $available_seats = $DB->get_field_sql("SELECT SUM(availableseats)  
                                                         FROM {tool_org_order_seats} 
                                                        WHERE tablename = 'tp_offerings' AND fieldname = 'id' AND fieldid = $offeringid AND orguserid = $USER->id ");
            if ($available_seats > 0) {
                $seats = $available_seats;
            } else {
                $purchasedseats = $DB->get_field_sql("SELECT SUM(purchasedseats) 
                             FROM {tool_org_order_seats} 
                             WHERE tablename = 'tp_offerings' AND fieldname = 'id' AND fieldid = $offeringid");
                $enrolled = $DB->get_field_sql(" SELECT count(userid) AS total
                                  FROM {program_enrollments} AS pe
                                 WHERE pe.offeringid = $offeringid AND pe.roleid = $traineeeid  AND pe.usercreated = $USER->id ");
                $seats = $total - ($enrolled + $purchasedseats);
            }
        }
        $availableseats = $seats;
        $userstoassign = $userid;
        $program = $DB->get_record_sql('SELECT * FROM {local_trainingprogram} as tp 
                                         JOIN {tp_offerings} as tpo ON tpo.trainingid = tp.id 
                                        WHERE tpo.id=:offeringid', ['offeringid' => $offeringid]);
        $course = $DB->get_record('course', ['id' => $program->courseid], '*', MUST_EXIST);
        $groupid = $DB->get_field_sql('SELECT g.id FROM {groups} as g JOIN {tp_offerings} as tpo on g.idnumber=tpo.code where tpo.id=:id', array('id' => $offeringid));
        if($apistatus=='migrate'){
                $availableseats=100;
        }
        else{
                if($availableseats <=0){
                     $result->coursename = $course->fullname;
                     $result->enrolment = "Availableseats $availableseats, Not able to enroll";
                     return $result;   
                }
        }




        if (sizeof($userstoassign) > $availableseats) {
                $result = new stdClass();
                $program_enrollments = $DB->get_record('program_enrollments', ['offeringid' => $offeringid, 'userid' => $userids], '*');
                    if (($program_enrollments->id) != "") {                
                        $result = new stdClass();
                        $result->changecount = $progress;

             

                          if($certificatecode > '0'){
                $result->coursename = $course->fullname . "already enrolled and certificate:".$certificatecode."updated";
                        }
                        else{
                        $result->coursename = $course->fullname . "and already enrolled";
                        }
                        $result->enrolment = 'success';
                        //return $result;
                    }else{                     
                        $result->coursename = $course->fullname;
                        $result->enrolment = "Availableseats $availableseats, Not able to enroll";
                        // return $result;
                 }               
  
        } else {
            if (!empty($userstoassign)) {
                $progress = 0;
                $progressbar = new \core\progress\display_if_slow(get_string('enrollusers', 'local_trainingprogram', $course->fullname));
                //$progressbar->start_html();
                $progressbar->start_progress('', count($userstoassign) - 1);
                foreach ($userstoassign as $key => $adduser) {
                    $progressbar->progress($progress);
                    $progress++;

                    $program_enrollments = $DB->get_record('program_enrollments', ['offeringid' => $offeringid, 'userid' => $adduser], '*');
                    if (($program_enrollments->id) == "") {

                        self::program_enrollment($offeringid, $adduser, $enrolldate);
                        $result = new stdClass();
                        $result->changecount = $progress;
                        $result->coursename = $course->fullname . "and course enrolled ";
                        $result->enrolment = 'success';
                    } else {
                        $result = new stdClass();
                        $result->changecount = $progress;
                        if($certificatecode > '0'){
                        $result->coursename = $course->fullname . "already enrolled and certificate:".$certificatecode."updated";
                        }
                        else{
                        $result->coursename = $course->fullname . "and already enrolled";

                        }
                        $result->enrolment = 'success';
                    }
                }
                if ($trainingmethod!=='elearning') {
                    $attendanceid = $DB->get_field_sql('SELECT atss.attendanceid as attendanceid FROM {offering_sessions} offs JOIN {attendance_sessions} atss ON atss.id = offs.sessionid WHERE offs.offeringid=:offeringid and offs.programid=:programid and offs.courseid=:courseid', array('offeringid' => $offeringid, 'programid' => $training_id, 'courseid' => $program->courseid));
                    $program_enrollments = $DB->get_record('program_enrollments', ['offeringid' => $offeringid, 'userid' => $adduser], '*');
                    $time = time();
                    $SQL2 = "SELECT ats.id as atsid, ass.id as assid
                FROM {attendance_sessions} ats 
                JOIN {attendance_statuses} ass ON ass.attendanceid = ats.attendanceid
                WHERE ass.attendanceid=$attendanceid and ass.acronym='p'";
                    $getrecords = $DB->get_records_sql($SQL2);
                    foreach ($getrecords as $key => $values) {
                        $sessionid = $values->atsid;
                        $statusid = $values->assid;
                        $attendance_logdata = new stdClass();
                        $attendance_logdata->sessionid = $sessionid;
                        $attendance_logdata->statusid = $statusid;
                        $attendance_logdata->studentid = $studentid;
                        $attendance_logdata->statusset = '';
                        $attendance_logdata->timetaken = time();
                        $attendance_logdata->takenby = 2;
                        //   print_r($attendance_logdata);
                        $DB->insert_record('attendance_log', $attendance_logdata);
                    }
                }
                $currenttime = $enrolldate;
               // $currenttime = strtotime($achievementdate);
                $program_completionsdata = new stdClass();
                // $programid = $DB->get_field('local_trainingprogram', 'id', ['code' => $programcode]);
                $programid = $training_id;
                $program_completionsdata->programid = $programid;
                $program_completionsdata->completion_status = 0;
                $program_completionsdata->preexam_completion_status = 1;
                $program_completionsdata->postexam_completion_status = 1;
                $program_completionsdata->completiondate = '';
                $getadminuserid = $DB->get_field('user', 'id', ['username' => 'admin']);
               $program_completionsdata->usercreated = $getadminuserid;
                $program_completionsdata->timecreated = $currenttime;
                $program_completionsdata->usermodified = $currenttime;
                $program_completionsdata->timemodified = $currenttime;
                /*$attendance_id= $DB->get_record('attendance_sessions', ['id' => $statusid], '*', MUST_EXIST);  
                $attendanceid=$attendance_id->attendanceid;*/
                $program_completionsdata->offeringid = $offeringid;
                $program_completionsdata->userid = $studentid;
                $program_completionscheck = $DB->get_record('program_completions', ['offeringid' => $offeringid, 'userid' => $studentid,'programid'=>$programid], '*');
                if (($program_completionscheck->id) == "") {
                $DB->insert_record('program_completions', $program_completionsdata);
                 }
                $courseid = $DB->get_field('local_trainingprogram', 'courseid', array('id' => $program_completionsdata->programid));
                $categoryid = $DB->get_field('course_categories', 'id', ['idnumber' => 'trainingprogram']);
                $contextid = context_coursecat::instance($categoryid);
                $prgrm_certificate = $DB->get_field('tool_certificate_templates', 'id', array('contextid' => $contextid->id));
                $status = 1;
              //  self::issue_certificate_mig($program_completionsdata->userid, $offeringid, 'trainingprogram', $prgrm_certificate, $status, $expiresdate = 0,$certificatecode,$achievementdate);
                if ($trainingmethod !== 'elearning') {
                    $update = $DB->execute("UPDATE {attendance_sessions} SET lasttaken = $time, lasttakenby  = 2,  description = 'Regular class session'  WHERE attendanceid='$attendanceid'"); }
                return $result;
            }
        }
    }


    public static function userenrollonly_returns()
    {
        return new external_single_structure(
            array(
                'coursename' => new external_value(PARAM_RAW, 'course'),
                'enrolment' => new external_value(PARAM_RAW, 'enrolment'),


            )
        );
    }

    public static function userenroll_parameters()
    {
        return new external_function_parameters(
            array(
                'offeringcode' => new external_value(PARAM_RAW, 'offeringid'),
                'username' => new external_value(PARAM_RAW, 'username'),
                'enrolldate' => new external_value(PARAM_RAW, 'enrolldate'),
                'achievementdate' => new external_value(PARAM_RAW, 'enrolldate'),
                'certificatecode' => new external_value(PARAM_RAW, 'certificatecode'),
                'apistatus' => new external_value(PARAM_RAW, 'apistatus'),

            )
        );
    }

    /**
     * Return the list of page question attempts in a given lesson.
     *
     * @param int $lessonid lesson instance id
     * @param int $attempt the lesson attempt number
     * @param bool $correct only fetch correct attempts
     * @param int $pageid only fetch attempts at the given page
     * @param int $userid only fetch attempts of the given user
     * @return array of warnings and page attempts
     * @since Moodle 3.3
     * @throws moodle_exception
     */
    public static function userenroll($offeringcode, $username, $enrolldate, $achievementdate,$certificatecode,$apistatus)
    {
           global $OUTPUT, $PAGE, $DB, $USER,$CFG;
        $CFG->debug = (E_ALL | E_STRICT);   // === DEBUG_DEVELOPER - NOT FOR PRODUCTION SERVERS!
        $CFG->debugdisplay = 1;
        if (!$DB->record_exists('user', ['username' => $username], '*', MUST_EXIST)) {

            $throw = new invalid_parameter_exception('Username is not exists: ' . $username);
            throw $throw;
        } else {
            $getuserid = $DB->get_record('user', ['username' => $username], '*', MUST_EXIST);
        }
        $userid = $getuserid->id;
        $userids = $getuserid->id;
        $studentid = $userid;
        $userid = (explode(" ", $userid));
        $type = 'program_enrol';
        $roleid = $DB->get_field('role', 'id', ['shortname' => 'trainee']);
        

        if (!$DB->record_exists('tp_offerings', ['code' => $offeringcode], '*', MUST_EXIST)) {

            $throw = new invalid_parameter_exception('offeringcode is not exists: ' . $offeringcode);
            throw $throw;
        } else {
            $offeringcodeid = $DB->get_record('tp_offerings', ['code' => $offeringcode], '*', MUST_EXIST);
        }
        $tp_offeringid = $offeringcodeid->id;
        $training_id = $offeringcodeid->trainingid;
        $trainingmethod = $offeringcodeid->trainingmethod;        
        $offeringid = (int) $tp_offeringid;
        $systemcontext = context_system::instance();
        $traineeeid = $DB->get_field('role', 'id', array('shortname' => 'trainee'));
        $totalseatssql = " SELECT  tp.availableseats AS total
                                     FROM {tp_offerings} tp
                                    WHERE tp.id = $offeringid";
        $total = $DB->get_field_sql($totalseatssql);
        if (!isloggedin() || (isloggedin() && (is_siteadmin() || has_capability('local/organization:manage_trainingofficial', $systemcontext)))) {
            $enrolled = $DB->get_field_sql(" SELECT count(userid) AS total
                              FROM {program_enrollments} AS pe
                             WHERE pe.offeringid = $offeringid AND pe.roleid = $traineeeid  AND pe.usercreated = $USER->id ");
            $purchasedseats = $DB->get_field_sql("SELECT SUM(purchasedseats) 
                         FROM {tool_org_order_seats} 
                        WHERE tablename = 'tp_offerings' AND fieldname = 'id' AND fieldid = $offeringid");
            $seats = $total - ($enrolled + $purchasedseats);
        } else {
            $available_seats = $DB->get_field_sql("SELECT SUM(availableseats)  
                                                         FROM {tool_org_order_seats} 
                                                        WHERE tablename = 'tp_offerings' AND fieldname = 'id' AND fieldid = $offeringid AND orguserid = $USER->id ");
            if ($available_seats > 0) {
                $seats = $available_seats;
            } else {
                $purchasedseats = $DB->get_field_sql("SELECT SUM(purchasedseats) 
                             FROM {tool_org_order_seats} 
                             WHERE tablename = 'tp_offerings' AND fieldname = 'id' AND fieldid = $offeringid");
                $enrolled = $DB->get_field_sql(" SELECT count(userid) AS total
                                  FROM {program_enrollments} AS pe
                                 WHERE pe.offeringid = $offeringid AND pe.roleid = $traineeeid  AND pe.usercreated = $USER->id ");
                $seats = $total - ($enrolled + $purchasedseats);
            }
        }
        $availableseats = $seats;
        $userstoassign = $userid;
        $program = $DB->get_record_sql('SELECT * FROM {local_trainingprogram} as tp 
                                         JOIN {tp_offerings} as tpo ON tpo.trainingid = tp.id 
                                        WHERE tpo.id=:offeringid', ['offeringid' => $offeringid]);
        $course = $DB->get_record('course', ['id' => $program->courseid], '*', MUST_EXIST);
        $groupid = $DB->get_field_sql('SELECT g.id FROM {groups} as g JOIN {tp_offerings} as tpo on g.idnumber=tpo.code where tpo.id=:id', array('id' => $offeringid));
        if($apistatus=='migrate'){
                $availableseats=100;
        }
        else{
                if($availableseats <=0){
                     $result->coursename = $course->fullname;
                     $result->enrolment = "Availableseats $availableseats, Not able to enroll";
                     return $result;   
                }
        }




        if (sizeof($userstoassign) > $availableseats) {
                $result = new stdClass();
                $program_enrollments = $DB->get_record('program_enrollments', ['offeringid' => $offeringid, 'userid' => $userids], '*');
                    if (($program_enrollments->id) != "") {                
                        $result = new stdClass();
                        $result->changecount = $progress;

             

                          if($certificatecode > '0'){
                $result->coursename = $course->fullname . "already enrolled and certificate:".$certificatecode."updated";
                        }
                        else{
                        $result->coursename = $course->fullname . "and already enrolled";
                        }
                        $result->enrolment = 'success';
                        //return $result;
                    }else{                     
                        $result->coursename = $course->fullname;
                        $result->enrolment = "Availableseats $availableseats, Not able to enroll";
                        // return $result;
                 }               
  
        } else {
            if (!empty($userstoassign)) {
                $progress = 0;
                $progressbar = new \core\progress\display_if_slow(get_string('enrollusers', 'local_trainingprogram', $course->fullname));
                //$progressbar->start_html();
                $progressbar->start_progress('', count($userstoassign) - 1);
                foreach ($userstoassign as $key => $adduser) {
                    $progressbar->progress($progress);
                    $progress++;

                    $program_enrollments = $DB->get_record('program_enrollments', ['offeringid' => $offeringid, 'userid' => $adduser], '*');
                    if (($program_enrollments->id) == "") {

                        self::program_enrollment($offeringid, $adduser, $enrolldate);
                        $result = new stdClass();
                        $result->changecount = $progress;
                        $result->coursename = $course->fullname . "and course enrolled ";
                        $result->enrolment = 'success';
                    } else {
                        $result = new stdClass();
                        $result->changecount = $progress;
                        if($certificatecode > '0'){
                        $result->coursename = $course->fullname . "already enrolled and certificate:".$certificatecode."updated";
                        }
                        else{
                        $result->coursename = $course->fullname . "and already enrolled";

                        }
                        $result->enrolment = 'success';
                    }
                }
                if ($trainingmethod!=='elearning') {
                    $attendanceid = $DB->get_field_sql('SELECT atss.attendanceid as attendanceid FROM {offering_sessions} offs JOIN {attendance_sessions} atss ON atss.id = offs.sessionid WHERE offs.offeringid=:offeringid and offs.programid=:programid and offs.courseid=:courseid', array('offeringid' => $offeringid, 'programid' => $training_id, 'courseid' => $program->courseid));
                    $program_enrollments = $DB->get_record('program_enrollments', ['offeringid' => $offeringid, 'userid' => $adduser], '*');
                    $time = time();
                    $SQL2 = "SELECT ats.id as atsid, ass.id as assid
                FROM {attendance_sessions} ats 
                JOIN {attendance_statuses} ass ON ass.attendanceid = ats.attendanceid
                WHERE ass.attendanceid=$attendanceid and ass.acronym='p'";
                    $getrecords = $DB->get_records_sql($SQL2);
                    foreach ($getrecords as $key => $values) {
                        $sessionid = $values->atsid;
                        $statusid = $values->assid;
                        $attendance_logdata = new stdClass();
                        $attendance_logdata->sessionid = $sessionid;
                        $attendance_logdata->statusid = $statusid;
                        $attendance_logdata->studentid = $studentid;
                        $attendance_logdata->statusset = '';
                        $attendance_logdata->timetaken = time();
                        $attendance_logdata->takenby = 2;
                        //   print_r($attendance_logdata);
                        $DB->insert_record('attendance_log', $attendance_logdata);
                    }
                }
                $currenttime = $achievementdate;
                $currenttime = strtotime($achievementdate);
                $program_completionsdata = new stdClass();
                // $programid = $DB->get_field('local_trainingprogram', 'id', ['code' => $programcode]);
                $programid = $training_id;
                $program_completionsdata->programid = $programid;
                $program_completionsdata->completion_status = 1;
                $program_completionsdata->preexam_completion_status = 1;
                $program_completionsdata->postexam_completion_status = 1;
                $program_completionsdata->completiondate = $currenttime;
                $getadminuserid = $DB->get_field('user', 'id', ['username' => 'admin']);
                $program_completionsdata->usercreated = $getadminuserid;
                $program_completionsdata->timecreated = $currenttime;
                $program_completionsdata->usermodified = $currenttime;
                $program_completionsdata->timemodified = $currenttime;
                /*$attendance_id= $DB->get_record('attendance_sessions', ['id' => $statusid], '*', MUST_EXIST);  
                $attendanceid=$attendance_id->attendanceid;*/
                $program_completionsdata->offeringid = $offeringid;
                $program_completionsdata->userid = $studentid;
                $program_completionscheck = $DB->get_record('program_completions', ['offeringid' => $offeringid, 'userid' => $studentid,'programid'=>$programid], '*');
                if (($program_completionscheck->id) == "") {
                $DB->insert_record('program_completions', $program_completionsdata);
                 }
                $courseid = $DB->get_field('local_trainingprogram', 'courseid', array('id' => $program_completionsdata->programid));
                $categoryid = $DB->get_field('course_categories', 'id', ['idnumber' => 'trainingprogram']);
                $contextid = context_coursecat::instance($categoryid);
                $prgrm_certificate = $DB->get_field('tool_certificate_templates', 'id', array('contextid' => $contextid->id));
                $status = 1;
                self::issue_certificate_mig($program_completionsdata->userid, $offeringid, 'trainingprogram', $prgrm_certificate, $status, $expiresdate = 0,$certificatecode,$achievementdate);
                if ($trainingmethod !== 'elearning') {
                    $update = $DB->execute("UPDATE {attendance_sessions} SET lasttaken = $time, lasttakenby  = 2,  description = 'Regular class session'  WHERE attendanceid='$attendanceid'"); }
                return $result;
            }
        }
    }

    private static function issue_certificate_mig($userid, $moduleid, $moduletype, $certificateid, $completion_status, $expiresdate = 0,$certificatecode,$achievementdate)
    {
        global $DB, $USER;
        $achievementdates = strtotime($achievementdate);
       
        try {
            $dataobj = new stdClass();
            $dataobj->userid = $userid;
            $dataobj->templateid = $certificateid;
          if($certificatecode > '0'){
                   $dataobj->code = $certificatecode;



                        //$completion_status =1;
            }else{
                            $dataobj->code = \tool_certificate\certificate::generate_code($dataobj->userid);

            }
            $dataobj->moduletype = $moduletype;
            $dataobj->moduleid = $moduleid;
            $dataobj->emailed = 0;
            $dataobj->component = 'tool_certificate';
            $dataobj->courseid = 0;
            $dataobj->timecreated = $achievementdates;
            $dataobj->usercreated = $USER->id;
            $dataobj->timemodified = $achievementdates;
            $dataobj->usermodified = $USER->id;
            $dataobj->programid = $moduleid;
            $data['userfullname'] = fullname($DB->get_record('user', ['id' => $userid]));


            $dataobj->data = json_encode($data);
            if ($expiresdate > 0) {
                $dataobj->expires = strtotime(date('Y-m-d', strtotime('+' . $expiresdate . ' years', $dataobj->timecreated)));
            }
            $array = array(
                'userid' => $userid,
                'moduleid' => $moduleid,
                'moduletype' => $moduletype
            );

            $exist_recordid = $DB->get_record('tool_certificate_issues', $array, 'id');

            if ($exist_recordid) {
              

                if ($completion_status == 0) {
                    
                }
                $dataobj->id = $exist_recordid->id;       
                 if($certificatecode > '0'){
              $DB->delete_records('tool_certificate_issues', ['id' => $exist_recordid->id]);
              $DB->insert_record('tool_certificate_issues', $dataobj);
             }
             else{
              $DB->delete_records('tool_certificate_issues', ['id' => $exist_recordid->id]);
              $DB->insert_record('tool_certificate_issues', $dataobj);
             }       
              $DB->update_record('tool_certificate_issues', $dataobj);
            }
             elseif ($completion_status > 0) {        
                 if($certificatecode >= '0'){    
                 $DB->delete_records('tool_certificate_issues', ['id' => $exist_recordid->id]);
                 $DB->insert_record('tool_certificate_issues', $dataobj);
                 }
                //$DB->insert_record('tool_certificate_issues', $dataobj);
            }
              if($certificatecode=='No')
                {
                  $exist_recordid = $DB->get_record('tool_certificate_issues', $array, 'id');
                  $DB->delete_records('tool_certificate_issues', ['id' => $exist_recordid->id]);
                }
        } catch (exception $e) {
            print_object($e);
        }
    }

    private static function issue_certificate($userid, $moduleid, $moduletype, $certificateid, $completion_status, $expiresdate = 0)
    {
        global $DB, $USER;
        try {
            $dataobj = new stdClass();
            $dataobj->userid = $userid;
            $dataobj->templateid = $certificateid;
            $dataobj->code = \tool_certificate\certificate::generate_code($dataobj->userid);
            $dataobj->moduletype = $moduletype;
            $dataobj->moduleid = $moduleid;
            $dataobj->emailed = 0;
            $dataobj->component = 'tool_certificate';
            $dataobj->courseid = 0;
            $dataobj->timecreated = time();
            $dataobj->usercreated = $USER->id;
            $dataobj->timemodified = time();
            $dataobj->usermodified = $USER->id;
            $dataobj->programid = $moduleid;
            $data['userfullname'] = fullname($DB->get_record('user', ['id' => $userid]));
            $dataobj->data = json_encode($data);

            if ($expiresdate > 0) {

                $dataobj->expires = strtotime(date('Y-m-d', strtotime('+' . $expiresdate . ' years', $dataobj->timecreated)));


            }


            $array = array(
                'userid' => $userid,
                'moduleid' => $moduleid,
                'moduletype' => $moduletype
            );
            $exist_recordid = $DB->get_record('tool_certificate_issues', $array, 'id');
            if ($exist_recordid) {

                if ($completion_status == 0) {

                    $DB->delete_records('tool_certificate_issues', ['id' => $exist_recordid->id]);

                }

                $dataobj->id = $exist_recordid->id;
                $DB->update_record('tool_certificate_issues', $dataobj);

            } elseif ($completion_status > 0) {

                $DB->insert_record('tool_certificate_issues', $dataobj);
            }
        } catch (exception $e) {
            print_object($e);
        }
    }
    public static function userenroll_returns()
    {
        return new external_single_structure(
            array(
                'coursename' => new external_value(PARAM_RAW, 'course'),
                'enrolment' => new external_value(PARAM_RAW, 'enrolment'),


            )
        );
    }


//delete traning API

      public static function userenrolldelete_parameters()
    {
        return new external_function_parameters(
            array(
                'offeringcode' => new external_value(PARAM_RAW, 'offeringid'),
                'username' => new external_value(PARAM_RAW, 'username'),          
            )
        );
    }

    /**
     * Return the list of page question attempts in a given lesson.
     *
     * @param int $lessonid lesson instance id
     * @param int $attempt the lesson attempt number
     * @param bool $correct only fetch correct attempts
     * @param int $pageid only fetch attempts at the given page
     * @param int $userid only fetch attempts of the given user
     * @return array of warnings and page attempts
     * @since Moodle 3.3
     * @throws moodle_exception
     */
    public static function userenrolldelete($offeringcode, $username)
    {
        global $OUTPUT, $PAGE, $DB, $USER,$CFG;
        $CFG->debug = (E_ALL | E_STRICT);   // === DEBUG_DEVELOPER - NOT FOR PRODUCTION SERVERS!
        $CFG->debugdisplay = 1;
        if (!$DB->record_exists('user', ['username' => $username], '*', MUST_EXIST)) {
            $throw = new invalid_parameter_exception('Username is not exists: ' . $username);
            throw $throw;
        } else {
            $getuserid = $DB->get_record('user', ['username' => $username], '*', MUST_EXIST);
        }
        $userid = $getuserid->id;
        $userids = $getuserid->id;
        $studentid = $userid;
        $userid = (explode(" ", $userid));
        $type = 'program_enrol';
        $roleid = $DB->get_field('role', 'id', ['shortname' => 'trainee']);
        if (!$DB->record_exists('tp_offerings', ['code' => $offeringcode], '*', MUST_EXIST)) {
            $throw = new invalid_parameter_exception('offeringcode is not exists: ' . $offeringcode);
            throw $throw;
        } else {
            $offeringcodeid = $DB->get_record('tp_offerings', ['code' => $offeringcode], '*', MUST_EXIST);
        }
        $tp_offeringid = $offeringcodeid->id;
        $training_id = $offeringcodeid->trainingid;
        $trainingmethod = $offeringcodeid->trainingmethod;        
        $offeringid = (int) $tp_offeringid;
        $deleteprogram_completions = $DB->delete_records('program_completions', ['offeringid' => $offeringid, 'userid' => $studentid]);
        $deleteprogram_enrollments = $DB->delete_records('program_enrollments', ['offeringid' => $offeringid, 'userid' => $studentid]);
        $deletetool_certificate_issues= $DB->delete_records('tool_certificate_issues', ['moduleid' => $offeringid, 'userid' => $studentid]);
        $result->offeringid = $offeringid. "- offerid and offer code $offeringcode  is deleted ";
        $result->userid = $studentid . "- userid and username $username is deleted ";      
        return $result;
            }
   

    public static function userenrolldelete_returns()
    {
        return new external_single_structure(
            array(
                'offeringid' => new external_value(PARAM_RAW, 'offeringid'),
                'userid' => new external_value(PARAM_RAW, 'userid'),
            )
        );
    }


    //delete traning API

      public static function enrolldelete_parameters()
    {
        return new external_function_parameters(
            array(
                'examcode' => new external_value(PARAM_RAW, 'examcode'),
                'username' => new external_value(PARAM_RAW, 'username'),
                'profilecode' => new external_value(PARAM_RAW, 'profilecode'),

            )
        );
    }

    /**
     * Return the list of page question attempts in a given lesson.
     *
     * @param int $lessonid lesson instance id
     * @param int $attempt the lesson attempt number
     * @param bool $correct only fetch correct attempts
     * @param int $pageid only fetch attempts at the given page
     * @param int $userid only fetch attempts of the given user
     * @return array of warnings and page attempts
     * @since Moodle 3.3
     * @throws moodle_exception
     */
    public static function enrolldelete($examcode, $username,$profilecode)
    {
        global $OUTPUT, $PAGE, $DB, $USER,$CFG;
        $CFG->debug = (E_ALL | E_STRICT);   // === DEBUG_DEVELOPER - NOT FOR PRODUCTION SERVERS!
        $CFG->debugdisplay = 1;
        if (!$DB->record_exists('user', ['username' => $username], '*', MUST_EXIST)) {
            $throw = new invalid_parameter_exception('Username is not exists: ' . $username);
            throw $throw;
        } else {
            $getuserid = $DB->get_record('user', ['username' => $username], '*', MUST_EXIST);
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

           if (!$DB->record_exists('local_exam_profiles', ['profilecode' => $profilecode], '*', MUST_EXIST)) {
            $throw = new invalid_parameter_exception('Profiles code is not exists: ' . $profilecode);
            throw $throw;
        } else {
            $profileids = $DB->get_record('local_exam_profiles', ['profilecode' => $profilecode], '*', MUST_EXIST);
        }
        $hailid = $hailids->id;
        
        $profileid = $profileids->id;

        $userid = $getuserid->id;
        $userids = $getuserid->id;
        $studentid = $userid;
        $userid = (explode(" ", $userid));

        $exam_enrollmentsrecords = $DB->get_records('exam_enrollments', ['examid' => $examid,'userid' => $studentid,'profileid' => $profileid]);
        $SQL2 = "SELECT *
                FROM {exam_enrollments} 
                WHERE examid=$examid and userid=$studentid and profileid= $profileid ORDER BY id Asc LIMIT 1, 100";
                    $getrecords = $DB->get_records_sql($SQL2);
                  
                  //print_r($getrecords);
        $arraycount= count($getrecords);
        
       if($arraycount>=1){
        
        foreach($getrecords AS $exam_enrollmentsrecord) {
                        
                        $id = $exam_enrollmentsrecord->id;

                        $examid = $exam_enrollmentsrecord->examid;
                        $userid = $exam_enrollmentsrecord->userid;
                        $profileid = $exam_enrollmentsrecord->profileid;
                       
                        $DB->delete_records('exam_enrollments', ['id' => $id]);
                }

                $result->profilecode = $profilecode. "- profilecode and examcode  $examcode  is deleted ";
        $result->userid = $studentid . "- userid and username $username is deleted ";  

        }
        else{

       $result->profilecode = $profilecode. "- profilecode and examcode  $examcode  is not deleted only one enrolment this exam";
        $result->userid = $studentid . "- userid and username $username is not deleted only one enrolment this exam";  

        }
       
            
        return $result;
            }
   

    public static function enrolldelete_returns()
    {
        return new external_single_structure(
            array(
                'profilecode' => new external_value(PARAM_RAW, 'offeringid'),
                'userid' => new external_value(PARAM_RAW, 'userid'),
            )
        );
    }


    public static function userexam_parameters()
    {
        return new external_function_parameters(
            array(
                'examcode' => new external_value(PARAM_RAW, 'examcode'),
                'profilecode' => new external_value(PARAM_RAW, 'profilecode'),
                'username' => new external_value(PARAM_RAW, 'username'),
                'hallcode' => new external_value(PARAM_RAW, 'hallcode'),
                'grade' => new external_value(PARAM_RAW, 'grade'),
                'achievementdate' => new external_value(PARAM_RAW, 'achievementdate'),
                'attemptcount' => new external_value(PARAM_RAW, 'attemptcount'),
                'certificatecode' => new external_value(PARAM_RAW, 'certificatecode'),
                'examdate' => new external_value(PARAM_RAW, 'examdate'),
                'starttime' => new external_value(PARAM_RAW, 'starttime'),
                'endtime' => new external_value(PARAM_RAW, 'endtime'),               
                

            )
        );
    }

    /**
     * Return the list of page question attempts in a given lesson.
     *
     * @param int $lessonid lesson instance id
     * @param int $attempt the lesson attempt number
     * @param bool $correct only fetch correct attempts
     * @param int $pageid only fetch attempts at the given page
     * @param int $userid only fetch attempts of the given user
     * @return array of warnings and page attempts
     * @since Moodle 3.3
     * @throws moodle_exception
     */
    public static function userexam($examcode, $profilecode,$username, $hallcode, $grade,  $achievementdate, $attemptcount,$certificatecode,$examdate,$starttime,$endtime)
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
     $examdatefinal = strtotime($examdate);
     $getfinalstarttime = $getstarttime+$examdatefinal;
     $getfinalgetendtime = $getendtime+$examdatefinal;
      
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
        if($attemptcount!=-1){
        if (!$DB->record_exists('local_exam_attempts', ['examid' => $examid, 'attemptid' => $attemptcount], '*', MUST_EXIST)) {
            //$throw = new invalid_parameter_exception('attempts count is not exists. Please verify attempt count in exam setting: ' . $attemptcount);
           // throw $throw;
            $result->message = 'attempts count is not exists. Please verify attempt count in exam setting: ' . $attemptcount;
            $result->exception = 'Null';
            $result->errorcode = '400';
            $result->debuginfo = 'Null';
            return $result;
        } 
}
        if($attemptcount!=1){
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
        $exam_reservationid = $DB->get_record('hallschedule', ['hallid' => $hailid,'startdate' => $examdatefinal], '*');
      
       $hallreservationid = $exam_reservationid->id;

      
        $userid = $getuserid->id;
        $studentid = $userid;
        $gradeget = $DB->get_record('grade_items', ['iteminstance' => $quizid, 'courseid' => $courseid, 'itemmodule' => 'quiz'], '*', MUST_EXIST);
            $gradegetresult = $gradeget->id;
            $gradepass = $gradeget->gradepass;
            if($grade<$gradepass){
          $deletecertificate = $DB->delete_records('tool_certificate_issues', ['userid' => $studentid, 'moduleid' => $examid]);

              }


        

        if($attemptcount==-1){
        $refundtype = false;
        
        (new \local_exams\local\exams)->exam_unenrollmet($profileid, $studentid,$refundtype);
        $result->message = 'Already Exam Completed  examcode:' . $examcode . ' user:' . $username . ' hallcode:' . $hallcode;
             
            $result->exception = 'Null';
            $result->errorcode = 'Null';
            $result->debuginfo = 'Null';
            return  $result;
        }
        $userid = (explode(" ", $userid));
        $exam_logdata = new stdClass();
        $exam_logdata->examid = $examid;
        $exam_logdata->courseid = $courseid;
        $exam_logdata->userid = $studentid;
        $exam_logdata->hallid = $hailid;
        $schedulerecord = $DB->get_record('hallschedule', ['id' => $hallreservationid]);
       // $examdate = !empty($schedulerecord->startdate) ? $schedulerecord->startdate : 0;
        $exam_logdata->examdate = $examdatefinal;
        $exam_logdata->profileid = $profileid;
        $getadminuserid = $DB->get_field('user', 'id', ['username' => 'admin']);
        $exam_logdata->usercreated = $getadminuserid;
        $exam_logdata->timecreated = $achievementdates;
        $exam_logdata->hallscheduleid = $hallreservationid;

      //  $exam_enrollments = $DB->get_record('exam_enrollments', ['examid' => $examid, 'userid' => $studentid, 'hallscheduleid' => $hallreservationid], '*');
       
       $local_quiz_attempts = $DB->get_record('quiz_attempts', ['quiz' => $quizid, 'userid' => $studentid,'attempt' => $attemptcount], '*');

     
        if (($local_quiz_attempts->id) == "") {
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
             
            //$id = $DB->insert_record('exam_enrollments', $exam_logdata);

            $id=1;
            $result->insertid = $id;

            $gradeget = $DB->get_record('grade_items', ['iteminstance' => $quizid, 'courseid' => $courseid, 'itemmodule' => 'quiz'], '*', MUST_EXIST);
            $gradegetresult = $gradeget->id;
            $gradepass = $gradeget->gradepass;

            $exam_completions_logdata = new stdClass();
            $exam_completions_logdata->examid = $examid;
            $exam_completions_logdata->courseid = $courseid;
            $exam_completions_logdata->userid = $studentid;
            $exam_completions_logdata->hallid = $hailid;
            $getadminuserid = $DB->get_field('user', 'id', ['username' => 'admin']);
            $exam_completions_logdata->usercreated = $getadminuserid;
            if($grade>=$gradepass){
              
             $completion_status = 2;   
            }
            else{
            
                $completion_status = 0;
            }
            
            $exam_completions_logdata->completiondate = $achievementdates;
            $exam_completions_logdata->examdate = $examdatefinal;
            $exam_completions_logdata->profileid = $profileid;
            $exam_completions_logdata->attemptid = $exam_attemptscount;
            $exam_completions_logdata->timecreated = $achievementdates;
            $exam_completions_logdata->hallscheduleid = $hallreservationid;
            //$ids = $DB->insert_record('exam_completions', $exam_completions_logdata);

             $ids = $DB->execute("UPDATE {exam_completions} SET completiondate = $examdatefinal,completion_status=$completion_status WHERE examid=$examid and userid=$studentid and profileid=$profileid");

            
            if ($attemptcount == 1) {
                
                $exam_completions_logdata->attemptid = 0;
            }
            //$record= $DB->insert_record('local_exam_userhallschedules', $exam_completions_logdata);
            if ($attemptcount>1) {
         //  $record= $DB->insert_record('local_exam_userhallschedules', $exam_completions_logdata);
            }
            $gradeget = $DB->get_record('grade_items', ['iteminstance' => $quizid, 'courseid' => $courseid, 'itemmodule' => 'quiz'], '*', MUST_EXIST);
            $gradegetresult = $gradeget->id;
             $gradepass = $gradeget->gradepass;
            $gradedata = new stdClass();
            $gradedata->itemid = $gradegetresult;
            $gradedata->userid = $studentid;
            $gradedata->rawgrademax = 100.0000;
            $gradedata->rawgrademin = 0.0000;

              if($grade==-1 || $grade==-2)
            {
                   
                $gradedata->finalgrade = $grade;
            }else{
               $gradedata->finalgrade = round($grade, 4);
            }

            
            $gradedata->timecreated = $achievementdates;
            $gradedata->timemodified = $achievementdates;
            $gradedata->aggregationstatus = 'unknown';
            if($grade!='No'){
                //print_r($gradedata);
            
                $gradegetgrade_grades = $DB->get_record('grade_grades', ['itemid' => $gradegetresult, 'userid' => $studentid]);
              $grade_grades=$gradegetgrade_grades->id;
             
                if($grade_grades!=''){
                    $update = $DB->execute("UPDATE {grade_grades} SET finalgrade = round($grade, 4), timecreated  = $achievementdates, timemodified  = $achievementdates  WHERE itemid=$gradegetresult and userid=$studentid");

                }
                else{
                    $idgrade = $DB->insert_record('grade_grades', $gradedata);

                }


                
            $quiz_data = new stdClass();
            $lastuniqueids=$DB->get_record_sql("SELECT MAX(id) as lastuniqueid   FROM {quiz_attempts}");
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
               if($grade==-1 || $grade==-2)
            {
                   
                $quiz_data->sumgrades = $grade;
            }else{
                $quiz_data->sumgrades = round($grade, 4);
            }
            $quiz_data->timestart = $getfinalstarttime;
            $quiz_data->timefinish = $getfinalgetendtime;
            $quiz_data->timemodified = $achievementdates;
            $quiz_data->timemodifiedoffline = 0;
            //$quiz_data->timecheckstate = 'NULL';

            $quiz_data->sumgrades = round($grade, 4);
            $quiz_data->gradednotificationsenttime = $achievementdates;
            $local_quiz_attempts = $DB->get_record('quiz_attempts', ['quiz' => $examcode, 'userid' => $studentid,'attempt' => $lastunique], '*');
           

            $quiz_attempts = $DB->insert_record('quiz_attempts', $quiz_data);
        //$updateexam_quiz_attempts = $DB->execute("UPDATE {quiz_attempts} SET id = $lastunique+1 WHERE uniqueid=$lastunique+1");

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
         
            

        $updateexam_enrollments = $DB->execute("UPDATE {exam_enrollments} SET examdate = $examdatefinal WHERE examid=$examid and userid=$studentid and profileid=$profileid ");
        $userhallschedules = $DB->execute("UPDATE {local_exam_userhallschedules} SET examdate = $examdatefinal WHERE examid=$examid and userid=$studentid and profileid=$profileid and attemptid=$exam_attemptscount");
                }

        } else {


                // update
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
             if($grade==-1 || $grade==-2)
            {
                   
                $quiz_data->sumgrades = $grade;
            }else{
                $quiz_data->sumgrades = round($grade, 4);
            }
            $quiz_data->timestart = $getfinalgetendtime;
            $quiz_data->timefinish = $getfinalgetendtime;
            $quiz_data->timemodified = $achievementdates;
            $quiz_data->timemodifiedoffline = 0;
            //$quiz_data->timecheckstate = 'NULL';
            $quiz_data->sumgrades = round($grade, 4);
            $quiz_data->gradednotificationsenttime = $achievementdates;
            $updatemark = $DB->get_record('quiz_attempts', ['quiz' => $quizid, 'userid' => $studentid, 'attempt' => $attemptcount], '*');
            if (($updatemark->id) != "") {
                
               $mark=round($grade, 4);
            $updateexam_quiz_attempts = $DB->execute("UPDATE {quiz_attempts} SET sumgrades =$mark,timestart=$getfinalgetendtime,timefinish=$getfinalgetendtime  WHERE id=$updatemark->id");
           

            
            $result->message = 'Exam grade updated mark:'.$mark.' examcode:' . $examcode . ' user:' . $username . ' hallcode:' . $hallcode;
             
            $result->exception = 'Null';
            $result->errorcode = 'Null';
            $result->debuginfo = 'Null';


            }else{
            $quiz_attempts = $DB->insert_record('quiz_attempts', $quiz_data);
          

            
            $updateexam_quiz_attempts = $DB->execute("UPDATE {quiz_attempts} SET id = $lastunique+1 WHERE uniqueid=$lastunique+1");
             //echo "asdasdasd";
              $exam_attemptscount;
            
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

            

        $updateexam_enrollments = $DB->execute("UPDATE {exam_enrollments} SET examdate = $examdatefinal,timecreated=$achievementdates WHERE examid=$examid and userid=$studentid and profileid=$profileid ");
        
          
            

             $result->insertid = $id;
            $exam_completions_logdata = new stdClass();
               if($grade>=$gradepass){

             $completion_status = 2;   
            }
            else{
          
                $completion_status = 0; 
            }
            $exam_completions_logdata->examid = $examid;
            $exam_completions_logdata->courseid = $courseid;
            $exam_completions_logdata->userid = $studentid;
            $exam_completions_logdata->hallid = $hailid;
            $getadminuserid = $DB->get_field('user', 'id', ['username' => 'admin']);
            $exam_completions_logdata->usercreated = $getadminuserid;
            $exam_completions_logdata->completion_status = $completion_status;
            $exam_completions_logdata->completiondate = $achievementdates;
            $exam_completions_logdata->examdate = $examdatefinal;
            $exam_completions_logdata->profileid = $profileid;

            $exam_completions_logdata->attemptid = $exam_attemptscount;
            $exam_completions_logdata->timecreated = $achievementdates;
            $exam_completions_logdata->hallscheduleid = $hallreservationid;
            //$ids = $DB->insert_record('exam_completions', $exam_completions_logdata);
            
            $record= $DB->insert_record('local_exam_userhallschedules', $exam_completions_logdata);
           $userexam_completions = $DB->execute("UPDATE {exam_completions} SET completiondate = $examdatefinal,completion_status=$completion_status WHERE examid=$examid and userid=$studentid and profileid=$profileid");

            $userhallschedules = $DB->execute("UPDATE {local_exam_userhallschedules} SET examdate = $examdatefinal WHERE examid=$examid and userid=$studentid and profileid=$profileid and attemptid=$exam_attemptscount");

            //}
 $update = $DB->execute("UPDATE {grade_grades} SET finalgrade = round($grade, 4), timecreated  = $achievementdates, timemodified  = $achievementdates  WHERE itemid=$gradegetresult and userid=$studentid");

            }
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
                  /*  echo "st";
                    echo $studentid;
                    echo "examid";
                    echo $examid;
                    echo "exm_certificate";
                    echo $exm_certificate;
                    echo "status";
                    echo $status;
                    echo "certificatecode";
                    echo $certificatecode;
                    echo "achievementdate";
                    echo $achievementdate;*/
                           
                    if($grade>=$gradepass){
                    self::issue_certificate_mig($studentid, $examid, 'exams', $exm_certificate, $status, $expiresdate = 0,$certificatecode,$achievementdate);
            }
                  
                }
            }
        }
        return $result;
    }



    public static function userexam_returns()
    {
        return new external_single_structure(
            array(

                'exception' => new external_value(PARAM_RAW, 'exception'),
                'errorcode' => new external_value(PARAM_RAW, 'errorcode'),
                'message' => new external_value(PARAM_RAW, 'message'),
                'debuginfo' => new external_value(PARAM_RAW, 'debuginfo'),


            )
        );
    }


  public static function userexamhistoryupdate_parameters()
    {
        return new external_function_parameters(
            array(
                'examcode' => new external_value(PARAM_RAW, 'examcode'),
                'profilecode' => new external_value(PARAM_RAW, 'profilecode'),
                'username' => new external_value(PARAM_RAW, 'username'),
                'hallcode' => new external_value(PARAM_RAW, 'hallcode'),
                'grade' => new external_value(PARAM_RAW, 'grade'),
                'achievementdate' => new external_value(PARAM_RAW, 'achievementdate'),
                'attemptcount' => new external_value(PARAM_RAW, 'attemptcount'),
                'certificatecode' => new external_value(PARAM_RAW, 'certificatecode'),
                'examdate' => new external_value(PARAM_RAW, 'examdate'),
                'starttime' => new external_value(PARAM_RAW, 'starttime'),
                'endtime' => new external_value(PARAM_RAW, 'endtime'),
                               
                

            )
        );
    }

    /**
     * Return the list of page question attempts in a given lesson.
     *
     * @param int $lessonid lesson instance id
     * @param int $attempt the lesson attempt number
     * @param bool $correct only fetch correct attempts
     * @param int $pageid only fetch attempts at the given page
     * @param int $userid only fetch attempts of the given user
     * @return array of warnings and page attempts
     * @since Moodle 3.3
     * @throws moodle_exception
     */
    public static function userexamhistoryupdate($examcode, $profilecode,$username, $hallcode, $grade,$achievementdate, $attemptcount,$certificatecode,$examdate,$starttime,$endtime)
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
        $getadminuserid = $DB->get_field('user', 'id', ['username' => 'admin']);
        $exam_logdata->usercreated = $getadminuserid;
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
             $getadminuserid = $DB->get_field('user', 'id', ['username' => 'admin']);

            $exam_completions_logdata->usercreated = $getadminuserid;
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
          //  $quiz_attempts = $DB->insert_record('quiz_attempts', $quiz_data);
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
            $getadminuserid = $DB->get_field('user', 'id', ['username' => 'admin']);
            $exam_completions_logdata->usercreated = $getadminuserid;
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



    public static function userexamhistoryupdate_returns()
    {
        return new external_single_structure(
            array(

                'exception' => new external_value(PARAM_RAW, 'exception'),
                'errorcode' => new external_value(PARAM_RAW, 'errorcode'),
                'message' => new external_value(PARAM_RAW, 'message'),
                'debuginfo' => new external_value(PARAM_RAW, 'debuginfo'),


            )
        );
    }


    public static function userexamhistory_parameters()
    {
        return new external_function_parameters(
            array(
                'examcode' => new external_value(PARAM_RAW, 'examcode'),
                'profilecode' => new external_value(PARAM_RAW, 'profilecode'),
                'username' => new external_value(PARAM_RAW, 'username'),
                'hallcode' => new external_value(PARAM_RAW, 'hallcode'),
                'grade' => new external_value(PARAM_RAW, 'grade'),
                'achievementdate' => new external_value(PARAM_RAW, 'achievementdate'),
                'attemptcount' => new external_value(PARAM_RAW, 'attemptcount'),
                'certificatecode' => new external_value(PARAM_RAW, 'certificatecode'),
                'examdate' => new external_value(PARAM_RAW, 'examdate'),
                'starttime' => new external_value(PARAM_RAW, 'starttime'),
                'endtime' => new external_value(PARAM_RAW, 'endtime'),
                               
                

            )
        );
    }

    /**
     * Return the list of page question attempts in a given lesson.
     *
     * @param int $lessonid lesson instance id
     * @param int $attempt the lesson attempt number
     * @param bool $correct only fetch correct attempts
     * @param int $pageid only fetch attempts at the given page
     * @param int $userid only fetch attempts of the given user
     * @return array of warnings and page attempts
     * @since Moodle 3.3
     * @throws moodle_exception
     */
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
        $getadminuserid = $DB->get_field('user', 'id', ['username' => 'admin']);
        $exam_logdata->usercreated = $getadminuserid;
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
            $getadminuserid = $DB->get_field('user', 'id', ['username' => 'admin']);
            $exam_completions_logdata->usercreated = $getadminuserid;
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
            $getadminuserid = $DB->get_field('user', 'id', ['username' => 'admin']);
            $exam_completions_logdata->usercreated = $getadminuserid;
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



    public static function userexamhistory_returns()
    {
        return new external_single_structure(
            array(

                'exception' => new external_value(PARAM_RAW, 'exception'),
                'errorcode' => new external_value(PARAM_RAW, 'errorcode'),
                'message' => new external_value(PARAM_RAW, 'message'),
                'debuginfo' => new external_value(PARAM_RAW, 'debuginfo'),


            )
        );
    }


    public static function userexamenrolonly_parameters()
    {
        return new external_function_parameters(
            array(
                'examcode' => new external_value(PARAM_RAW, 'examcode'),
                'profilecode' => new external_value(PARAM_RAW, 'profilecode'),
                'username' => new external_value(PARAM_RAW, 'username'),
                'hallcode' => new external_value(PARAM_RAW, 'hallcode'),
                'enrollmentdate' => new external_value(PARAM_RAW, 'enrollmentdate'),
                'examdate' => new external_value(PARAM_RAW, 'examdate'),
                'starttime' => new external_value(PARAM_RAW, 'starttime'),
                'endtime' => new external_value(PARAM_RAW, 'endtime'),
                'attemptcount' => new external_value(PARAM_RAW, 'attemptcount'),            
        )
        );
    }

    /**
     * Return the list of page question attempts in a given lesson.
     *
     * @param int $lessonid lesson instance id
     * @param int $attempt the lesson attempt number
     * @param bool $correct only fetch correct attempts
     * @param int $pageid only fetch attempts at the given page
     * @param int $userid only fetch attempts of the given user
     * @return array of warnings and page attempts
     * @since Moodle 3.3
     * @throws moodle_exception
     */
    public static function userexamenrolonly($examcode, $profilecode,$username, $hallcode, $enrollmentdate,  $examdate, $starttime,$endtime,$attemptcount)
    {
        global $OUTPUT, $PAGE, $DB, $USER,$CFG;
        $start = explode(':', $starttime);
        $endtime = explode(':', $endtime);
        $starthour= $start[0];
        $startmin= $start[1];
        $startsec= $start[2];
        $endhour= $endtime[0];
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
        $day = date('l', $examdate);
        $CFG->debug = (E_ALL | E_STRICT);   // === DEBUG_DEVELOPER - NOT FOR PRODUCTION SERVERS!
        $CFG->debugdisplay = 1;
      
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
        $achievementdates = strtotime($enrollmentdate);
        if ($achievementdates == 0) {
            $throw = new invalid_parameter_exception('achievementdate date should be correct format: ' . $achievementdate);
            throw $throw;
        }
        $courseid = $examids->courseid;
       // $examdate = $examids->examdatetime;
        $quizid = $profileids->quizid;
        //echo $examdate =date('Y-m-d H:i:s', $examdate/1000);
        //$examdate = date('Y-m-d H:i:s', $examdate);


        $exam_reservationid = $DB->get_record('hallschedule', ['hallid' => $hailid,'startdate' => $examdate, 'starttime' => $getstarttime], '*');
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
        $exam_logdata->examdate = $examdates;
        $exam_logdata->profileid = $profileid;
        $getadminuserid = $DB->get_field('user', 'id', ['username' => 'admin']);
        $exam_logdata->usercreated = $getadminuserid;
        $exam_logdata->timecreated = $achievementdates;
        $exam_logdata->hallscheduleid = $hallreservationid;
        $exam_enrollments = $DB->get_record('exam_enrollments', ['examid' => $examid,'profileid' => $profileid, 'userid' => $studentid, 'hallscheduleid' => $hallreservationid], '*');

        if (($exam_enrollments->id) !== "") {
                 
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
            //$id = $DB->insert_record('exam_enrollments', $exam_logdata);
            $id=1;
            $result->insertid = $id;
            $exam_completions_logdata = new stdClass();
            $exam_halls= new stdClass();
            $exam_completions_logdata->examid = $examid;
            $exam_completions_logdata->courseid = $courseid;
            $exam_completions_logdata->userid = $studentid;
            $exam_completions_logdata->hallid = $hailid;
            $getadminuserid = $DB->get_field('user', 'id', ['username' => 'admin']);
            $exam_completions_logdata->usercreated = $getadminuserid;

            $exam_completions_logdata->completion_status = 0;
            $exam_completions_logdata->completiondate = '';
            $exam_completions_logdata->examdate = $examdate;
            $exam_completions_logdata->profileid = $profileid;
            $exam_completions_logdata->attemptid = $exam_attemptscount;
            $exam_completions_logdata->timecreated = $achievementdates;
            $exam_completions_logdata->hallscheduleid = $hallreservationid;
            
            $exam_halls->hallid = $hailid;
            $exam_halls->startdate = $examdate;
            $exam_halls->starttime = $getstarttime;
            $exam_halls->endtime = $getendtime;
            $exam_halls->days = $day;
            $exam_halls->timecreated = $achievementdates;
            $exam_halls->timemodified = 0; 
           // $hall = $DB->insert_record('hallschedule', $exam_halls);

            //$ids = $DB->insert_record('exam_completions', $exam_completions_logdata);
 $exam_userhallschedulesid = $DB->get_record('local_exam_userhallschedules', ['examid' => $examid,'profileid' => $profileid, 'hallscheduleid' => $hallreservationid,'attemptid' => $exam_attemptscount,'examdate' => $examdate,'userid' => $studentid], '*');
       $examidexist=$exam_userhallschedulesid->id;
       if($examidexist=='')
       {

            $record= $DB->insert_record('local_exam_userhallschedules', $exam_completions_logdata);
     }
            /*$gradeget = $DB->get_record('grade_items', ['iteminstance' => $quizid, 'courseid' => $courseid, 'itemmodule' => 'quiz'], '*', MUST_EXIST);*/
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
          $local_exam_attempts = $DB->get_record('tool_products', ['code' => $examcode, 'referenceid' => $exam_attemptscount], '*', MUST_EXIST);
             $productid=$local_exam_attempts->id;
                       if($attemptcount >1){
                      
              $attemptpurchases_data = new stdClass();
             $attemptpurchases_data->productid = $productid;
             $attemptpurchases_data->examid = $examid;
             $attemptpurchases_data->referenceid = $exam_attemptscount;
             $attemptpurchases_data->referencetable = 'local_exam_attempts';
             $attemptpurchases_data->userid = $studentid;
             $attemptpurchases_data->timecreated =$achievementdates;
          $local_exam_attemptpurchasesid = $DB->get_record('local_exam_attemptpurchases', ['productid' => $productid,'examid' => $examid, 'referenceid' => $exam_attemptscount,'userid' => $studentid], '*');
       $local_examexamidexist=$local_exam_attemptpurchasesid->id;
       if($local_examexamidexist=='')
       {
            $quiz_attempts = $DB->insert_record('local_exam_attemptpurchases', $attemptpurchases_data);
     }

    }

            
 //$userexam_completions = $DB->execute("UPDATE {exam_completions} SET completiondate = $examdate WHERE examid=$examid and userid=$studentid and profileid=$profileid");

$updateexam_enrollments = $DB->execute("UPDATE {exam_enrollments} SET examdate =$examdate WHERE examid=$examid and userid=$studentid and profileid=$profileid ");
        $userhallschedules = $DB->execute("UPDATE {local_exam_userhallschedules} SET examdate = $examdate WHERE examid=$examid and userid=$studentid and profileid=$profileid and attemptid=$exam_attemptscount");
            if($grade!='No'){
           
           // $idgrade = $DB->insert_record('grade_grades', $gradedata);
                }
        }

        else{
                     $result->message = 'Already Exam erolled  examcode:' . $examcode . ' user:' . $username . ' hallcode:' . $hallcode;
                     $result->exception = 'Null';
            $result->errorcode = 'Null';
            $result->debuginfo = 'Null';
        } 
        /*else {
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
            if(!$DB->record_exists('grade_grades', ['itemid' => $gradegetresult,'userid' => $studentid], '*', MUST_EXIST)){
           // $idgrade = $DB->insert_record('grade_grades', $gradedata);
            }
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
        } */
        if ($id) {
                if($certificatecode!='No'){
                        if(($examidexist=='')&&($local_examexamidexist=='')) {
            $result->message = 'Exam enrolment Successfully: examcode: ' . $examcode . ' user: ' . $username . ' hallcode: ' . $hallcode;
             }
              else{
                                     $result->message = 'Already Exam erolled  examcode:' . $examcode . ' user:' . $username . ' hallcode:' . $hallcode;

              }}
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
                   /* self::issue_certificate_mig($studentid, $examid, 'exams', $exm_certificate, $status, $expiresdate = 0,$certificatecode,$achievementdate);*/
                  
                }
            }
        }
        return $result;
    }



    public static function userexamenrolonly_returns()
    {
        return new external_single_structure(
            array(

                'exception' => new external_value(PARAM_RAW, 'exception'),
                'errorcode' => new external_value(PARAM_RAW, 'errorcode'),
                'message' => new external_value(PARAM_RAW, 'message'),
                'debuginfo' => new external_value(PARAM_RAW, 'debuginfo'),


            )
        );
    }


  public static function userexamenrolmig_parameters()
    {
        return new external_function_parameters(
            array(
                'examcode' => new external_value(PARAM_RAW, 'examcode'),
                'profilecode' => new external_value(PARAM_RAW, 'profilecode'),
                'username' => new external_value(PARAM_RAW, 'username'),
                'hallcode' => new external_value(PARAM_RAW, 'hallcode'),
                'enrollmentdate' => new external_value(PARAM_RAW, 'enrollmentdate'),
                'examdate' => new external_value(PARAM_RAW, 'examdate'),
                'starttime' => new external_value(PARAM_RAW, 'starttime'),
                'endtime' => new external_value(PARAM_RAW, 'endtime'),
                'attemptcount' => new external_value(PARAM_RAW, 'attemptcount'),            
        )
        );
    }

    /**
     * Return the list of page question attempts in a given lesson.
     *
     * @param int $lessonid lesson instance id
     * @param int $attempt the lesson attempt number
     * @param bool $correct only fetch correct attempts
     * @param int $pageid only fetch attempts at the given page
     * @param int $userid only fetch attempts of the given user
     * @return array of warnings and page attempts
     * @since Moodle 3.3
     * @throws moodle_exception
     */
    public static function userexamenrolmig($examcode, $profilecode,$username, $hallcode, $enrollmentdate,  $examdate, $starttime,$endtime,$attemptcount)
    {
        global $OUTPUT, $PAGE, $DB, $USER,$CFG;
        $start = explode(':', $starttime);
        $endtime = explode(':', $endtime);
        $starthour= $start[0];
        $startmin= $start[1];
        $startsec= $start[2];
        $endhour= $endtime[0];
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
        $day = date('l', $examdate);
        $CFG->debug = (E_ALL | E_STRICT);   // === DEBUG_DEVELOPER - NOT FOR PRODUCTION SERVERS!
        $CFG->debugdisplay = 1;
      
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
        $achievementdates = strtotime($enrollmentdate);
        if ($achievementdates == 0) {
            $throw = new invalid_parameter_exception('achievementdate date should be correct format: ' . $achievementdate);
            throw $throw;
        }
        $courseid = $examids->courseid;
       // $examdate = $examids->examdatetime;
        $quizid = $profileids->quizid;
        //echo $examdate =date('Y-m-d H:i:s', $examdate/1000);
        //$examdate = date('Y-m-d H:i:s', $examdate);


        $exam_reservationid = $DB->get_record('hallschedule', ['hallid' => $hailid,'startdate' => $examdate, 'starttime' => $getstarttime], '*');
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
        $exam_logdata->examdate = $examdates;
        $exam_logdata->profileid = $profileid;
        $getadminuserid = $DB->get_field('user', 'id', ['username' => 'admin']);
        $exam_logdata->usercreated = $getadminuserid;
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
            $exam_halls= new stdClass();
            $exam_completions_logdata->examid = $examid;
            $exam_completions_logdata->courseid = $courseid;
            $exam_completions_logdata->userid = $studentid;
            $exam_completions_logdata->hallid = $hailid;
            $getadminuserid = $DB->get_field('user', 'id', ['username' => 'admin']);
            $exam_completions_logdata->usercreated = $getadminuserid;
            $exam_completions_logdata->completion_status = 0;
            $exam_completions_logdata->completiondate = '';
            $exam_completions_logdata->examdate = $examdate;
            $exam_completions_logdata->profileid = $profileid;
            $exam_completions_logdata->attemptid = $exam_attemptscount;
            $exam_completions_logdata->timecreated = $achievementdates;
            $exam_completions_logdata->hallscheduleid = $hallreservationid;
            
            $exam_halls->hallid = $hailid;
            $exam_halls->startdate = $examdate;
            $exam_halls->starttime = $getstarttime;
            $exam_halls->endtime = $getendtime;
            $exam_halls->days = $day;
            $exam_halls->timecreated = $achievementdates;
            $exam_halls->timemodified = 0; 
           // $hall = $DB->insert_record('hallschedule', $exam_halls);

            $ids = $DB->insert_record('exam_completions', $exam_completions_logdata);
            $record= $DB->insert_record('local_exam_userhallschedules', $exam_completions_logdata);
            /*$gradeget = $DB->get_record('grade_items', ['iteminstance' => $quizid, 'courseid' => $courseid, 'itemmodule' => 'quiz'], '*', MUST_EXIST);*/
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
          $local_exam_attempts = $DB->get_record('tool_products', ['code' => $examcode, 'referenceid' => $exam_attemptscount], '*', MUST_EXIST);
             $productid=$local_exam_attempts->id;
                       if($attemptcount >=1){
                      
              $attemptpurchases_data = new stdClass();
             $attemptpurchases_data->productid = $productid;
             $attemptpurchases_data->examid = $examid;
             $attemptpurchases_data->referenceid = $exam_attemptscount;
             $attemptpurchases_data->referencetable = 'local_exam_attempts';
             $attemptpurchases_data->userid = $studentid;
             $attemptpurchases_data->timecreated =$achievementdates;

            $quiz_attempts = $DB->insert_record('local_exam_attemptpurchases', $attemptpurchases_data);
    }
 
 //$userexam_completions = $DB->execute("UPDATE {exam_completions} SET completiondate = $examdate WHERE examid=$examid and userid=$studentid and profileid=$profileid");
$updateexam_enrollments = $DB->execute("UPDATE {exam_enrollments} SET examdate =$examdate WHERE examid=$examid and userid=$studentid and profileid=$profileid ");
        $userhallschedules = $DB->execute("UPDATE {local_exam_userhallschedules} SET examdate = $examdate WHERE examid=$examid and userid=$studentid and profileid=$profileid and attemptid=$exam_attemptscount");
            if($grade!='No'){
           
           // $idgrade = $DB->insert_record('grade_grades', $gradedata);
                }
        }

        else{
                     $result->message = 'Already Exam erolled  examcode:' . $examcode . ' user:' . $username . ' hallcode:' . $hallcode;
                     $result->exception = 'Null';
            $result->errorcode = 'Null';
            $result->debuginfo = 'Null';
        } 
        /*else {
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
            if(!$DB->record_exists('grade_grades', ['itemid' => $gradegetresult,'userid' => $studentid], '*', MUST_EXIST)){
           // $idgrade = $DB->insert_record('grade_grades', $gradedata);
            }
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
        } */
        if ($id) {
                if($certificatecode!='No'){
            $result->message = 'Exam enrolment Successfully: examcode: ' . $examcode . ' user: ' . $username . ' hallcode: ' . $hallcode;
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
                   /* self::issue_certificate_mig($studentid, $examid, 'exams', $exm_certificate, $status, $expiresdate = 0,$certificatecode,$achievementdate);*/
                  
                }
            }
        }
        return $result;
    }



    public static function userexamenrolmig_returns()
    {
        return new external_single_structure(
            array(

                'exception' => new external_value(PARAM_RAW, 'exception'),
                'errorcode' => new external_value(PARAM_RAW, 'errorcode'),
                'message' => new external_value(PARAM_RAW, 'message'),
                'debuginfo' => new external_value(PARAM_RAW, 'debuginfo'),


            )
        );
    }



    // program enrolment

    // program enrolment

    public static function programuserenroll_parameters()
    {
        return new external_function_parameters(
            array(
                'offeringcode' => new external_value(PARAM_RAW, 'offeringid'),
                'usermail' => new external_value(PARAM_RAW, 'userid'),
            )
        );
    }

    public static function programuserenroll($offeringcode, $usermail)
    {
        global $OUTPUT, $PAGE, $DB, $USER;
        $offerings = $DB->get_record('tp_offerings', ['code' => $offeringcode], '*', MUST_EXIST);
        $offeringid = $offerings->id;
        $programid = $offerings->trainingid;

        $getuserid = $DB->get_record('user', ['email' => $usermail], '*', MUST_EXIST);

        $userid = $getuserid->id;
        $studentid = $userid;
        $userid = (explode(" ", $userid));

        $type = 'program_enrol';
        $roleid = $DB->get_field('role', 'id', ['shortname' => 'trainee']);

        $offeringid = (int) $offeringid;
        $systemcontext = context_system::instance();
        $traineeeid = $DB->get_field('role', 'id', array('shortname' => 'trainee'));

        $totalseatssql = " SELECT  tp.availableseats AS total
                                     FROM {tp_offerings} tp
                                    WHERE tp.id = $offeringid";

        $total = $DB->get_field_sql($totalseatssql);
        if (!isloggedin() || (isloggedin() && (is_siteadmin() || has_capability('local/organization:manage_trainingofficial', $systemcontext)))) {

            $enrolled = $DB->get_field_sql(" SELECT count(userid) AS total
                              FROM {program_enrollments} AS pe
                             WHERE pe.offeringid = $offeringid AND pe.roleid = $traineeeid  AND pe.usercreated = $USER->id ");
            $purchasedseats = $DB->get_field_sql("SELECT SUM(purchasedseats) 
                         FROM {tool_org_order_seats} 
                        WHERE tablename = 'tp_offerings' AND fieldname = 'id' AND fieldid = $offeringid");

            $seats = $total - ($enrolled + $purchasedseats);
        } else {

            $available_seats = $DB->get_field_sql("SELECT SUM(availableseats)  
                                                         FROM {tool_org_order_seats} 
                                                        WHERE tablename = 'tp_offerings' AND fieldname = 'id' AND fieldid = $offeringid AND orguserid = $USER->id ");

            if ($available_seats > 0) {

                $seats = $available_seats;

            } else {

                $purchasedseats = $DB->get_field_sql("SELECT SUM(purchasedseats) 
                             FROM {tool_org_order_seats} 
                            WHERE tablename = 'tp_offerings' AND fieldname = 'id' AND fieldid = $offeringid");

                $enrolled = $DB->get_field_sql(" SELECT count(userid) AS total
                                  FROM {program_enrollments} AS pe
                                 WHERE pe.offeringid = $offeringid AND pe.roleid = $traineeeid  AND pe.usercreated = $USER->id ");

                $seats = $total - ($enrolled + $purchasedseats);
            }

        }
        $availableseats = $seats;

        $userstoassign = $userid;
        $program = $DB->get_record_sql('SELECT * FROM {local_trainingprogram} as tp 
                                         JOIN {tp_offerings} as tpo ON tpo.trainingid = tp.id 
                                        WHERE tpo.id=:offeringid', ['offeringid' => $offeringid]);

        $course = $DB->get_record('course', ['id' => $program->courseid], '*', MUST_EXIST);
        $groupid = $DB->get_field_sql('SELECT g.id FROM {groups} as g JOIN {tp_offerings} as tpo on g.idnumber=tpo.code where tpo.id=:id', array('id' => $offeringid));
        if (sizeof($userstoassign) > $availableseats) {
            //echo "<div class='alert alert-info'>".get_string('userscountismore', 'local_exams', $availableseats)."</div>";
        } else {
            if (!empty($userstoassign)) {
                $progress = 0;
                $progressbar = new \core\progress\display_if_slow(get_string('enrollusers', 'local_trainingprogram', $course->fullname));
                //$progressbar->start_html();
                $progressbar->start_progress('', count($userstoassign) - 1);
                foreach ($userstoassign as $key => $adduser) {
                    $progressbar->progress($progress);
                    $progress++;

                    $program_enrollments = $DB->get_record('program_enrollments', ['offeringid' => $offeringid, 'userid' => $adduser], '*');
                    if (($program_enrollments->id) == "") {
                        (new \local_trainingprogram\local\trainingprogram)->program_enrollment($offeringid, $adduser);
                        $result = new stdClass();
                        $result->changecount = $progress;
                        $result->coursename = $course->fullname . "and course enrolled";
                        ;
                        $result->enrolment = 'success';
                    } else {
                        $result = new stdClass();
                        $result->changecount = $progress;
                        $result->coursename = $course->fullname . "and already enrolled";
                        $result->enrolment = 'success';
                    }

                }
                //  $progressbar->end_html();


                return $result;
            }
        }







    }

    public static function programuserenroll_returns()
    {
        return new external_single_structure(
            array(
                'coursename' => new external_value(PARAM_RAW, 'course'),
                'enrolment' => new external_value(PARAM_RAW, 'enrolment'),


            )
        );
    }

    //exam user enrolment

    public static function userexamenrol_parameters()
    {
        return new external_function_parameters(
            array(
                'examcode' => new external_value(PARAM_RAW, 'examcode'),
                'usermail' => new external_value(PARAM_RAW, 'youremailid'),
                'hailid' => new external_value(PARAM_RAW, 'hailid'),
                'hallreservationid' => new external_value(PARAM_RAW, 'youremailid'),
            )
        );
    }

    /**
     * Return the list of page question attempts in a given lesson.
     *
     * @param int $lessonid lesson instance id
     * @param int $attempt the lesson attempt number
     * @param bool $correct only fetch correct attempts
     * @param int $pageid only fetch attempts at the given page
     * @param int $userid only fetch attempts of the given user
     * @return array of warnings and page attempts
     * @since Moodle 3.3
     * @throws moodle_exception
     */
    public static function userexamenrol($examcode, $usermail, $hailid, $hallreservationid)
    {
        global $OUTPUT, $PAGE, $DB, $USER;
        $examids = $DB->get_record('local_exams', ['code' => $examcode], '*', MUST_EXIST);
        $examid = $examids->id;
        $courseid = $examids->courseid;
        $examdate = $examids->examdatetime;
        $examdate = date('Y-m-d H:i:s', $examdate / 1000);
        $getuserid = $DB->get_record('user', ['email' => $usermail], '*', MUST_EXIST);
        $userid = $getuserid->id;
        $studentid = $userid;
        $userid = (explode(" ", $userid));
        $exam_logdata = new stdClass();
        $exam_logdata->examid = $examid;
        $exam_logdata->courseid = $courseid;
        $exam_logdata->userid = $studentid;
        $exam_logdata->hallid = $hailid;
        $exam_logdata->examdate = $examdate;
        $getadminuserid = $DB->get_field('user', 'id', ['username' => 'admin']);
        $exam_logdata->usercreated = $getadminuserid;
        $exam_logdata->timecreated = time();
        $exam_logdata->hallreservationid = $hallreservationid;
        $exam_enrollments = $DB->get_record('exam_enrollments', ['examid' => $examid, 'userid' => $studentid, 'hallid' => $hailid, 'hallreservationid' => $hallreservationid], '*');
        if (($exam_enrollments->id) == "") {
            // $id=$DB->insert_record('exam_enrollments', $exam_logdata);
            $timestart = time();
            $timeend = time();
            $manual = enrol_get_plugin('manual');
            $roleid = $DB->get_field('role', 'id', array('shortname' => 'trainee'));
            $instance = $DB->get_record('enrol', array('courseid' => $courseid, 'enrol' => 'manual'), '*', MUST_EXIST);
            $manual->enrol_user($instance, $studentid, $roleid, $timestart, $timeend);

            $systemcontext = context_system::instance();
            $selectsql = " SELECT  le.*
                        FROM {local_exams} le
                        JOIN {hall_reservations} as hr ON hr.typeid=le.id AND hr.type='exam'
                        WHERE hr.id = " . $hallreservationid;
            $exam = $DB->get_record_sql($selectsql);

            $course = $DB->get_record('course', ['id' => $exam->courseid], '*', MUST_EXIST);
            $reservationinfo = $DB->get_record('hall_reservations', ['id' => $hallreservationid]);



            //  $availableseats = availableseats($hallreservationid);
            $timestart = $course->startdate;
            $timeend = 0;
            if ($timestart == '') {
                $timestart = 0;
            }
            $manual = enrol_get_plugin('manual');
            $roleid = $DB->get_field('role', 'id', array('shortname' => 'trainee'));
            $instance = $DB->get_record('enrol', array('courseid' => $exam->courseid, 'enrol' => 'manual'), '*', MUST_EXIST);
            $manual->enrol_user($instance, $studentid, $roleid, $timestart, $timeend);
            $row = array();
            $row['examid'] = $examid;
            $row['courseid'] = $courseid;
            $row['userid'] = $studentid;
            $row['timecreated'] = time();
            $getadminuserid = $DB->get_field('user', 'id', ['username' => 'admin']);
            $row['usercreated'] = $getadminuserid;
            $row['hallid'] = $hailid;
            $row['examdate'] = $reservationinfo->examdate;
            $row['hallreservationid'] = $hallreservationid;
            $id = $DB->insert_record('exam_enrollments', $row);

            $traineesql = "SELECT ra.id
            FROM {role_assignments} ra 
            JOIN {role} as r ON r.id = ra.roleid
            WHERE ra.contextid = 1 AND r.shortname = 'trainee' AND ra.userid = " . $studentid;
            $traineerole = $DB->get_field_sql($traineesql);
            if ($traineerole) {
                $examenrolled = new \stdClass;
                $examenrolled->exam_userfullname = $DB->get_field('user', 'firstname', array('id' => $studentid)) . $DB->get_field('user', 'lastname', array('id' => $studentid));
                $examenrolled->exam_name = $DB->get_field('local_exams', 'exam', array('id' => $examid));
                $examenrolled->exam_date = date("d.m.y", $DB->get_field('local_exams', 'examdatetime', array('id' => $exam->id)));
                $examenrolled->exam_time = date("H:i:s", $DB->get_field('local_exams', 'slot', array('id' => $exam->id)));
                $trainee = $DB->get_record('user', array('id' => $studentid));
                (new \local_exams\notification)->exams_notification('exams_enrolment', $touser = $trainee, $fromuser = $USER, $examenrolled, $waitinglistid = 0);
            }
            if ($availableseats && $exam->examprice > 0) {

                (new \tool_product\product)->upadte_availableseats('hall_reservations', 'id', $hallreservationid, -1, $USER->id);

            }


            $context = context_system::instance();
            $options = array(
                'context' => $context->id,
                'examid' => $examid,
                'email' => $studentid,
                'organizationusers' => '',
                'hallreservationid' => $hallreservationid,
                'examdate' => $examdate,
                'halls' => $hailid
            );

            $select_to_users = (new \local_exams\local\exams)->exam_enrolled_users('add', $hallreservationid, $options);
            $result->insertid = $id;
        } else {
            $result->insertid = "record already avaiable";
        }
        if ($id) {
            $result->status = 'Exam Code ' . $examcode . ' enrolled successfully';
        } else {
            $result->status = 'Fail';
        }

        return $result;
    }

    public static function userexamenrol_returns()
    {
        return new external_single_structure(
            array(
                'insertid' => new external_value(PARAM_RAW, 'recordstatus'),
                'status' => new external_value(PARAM_RAW, 'status'),

            )
        );
    }

    private static function program_enrollment($offeringid, $userid, $enrolldate)
    {
        global $DB, $USER;
        $timestart = $course->startdate;
        $systemcontext = context_system::instance();
        $timeend = 0;
        if ($timestart == '') {
            $timestart = 0;
        }
        $availableseats = self::get_available_seats($offeringid);

        $trainingmethod = $DB->get_field_sql('SELECT trainingmethod FROM {tp_offerings} WHERE id = ' . $offeringid . '');

        $userstoassign = $add;
        $program = $DB->get_record_sql('SELECT tp.id,tp.courseid,tp.price,tpo.code AS offeringcode FROM {local_trainingprogram} as tp 
                JOIN {tp_offerings} as tpo ON tpo.trainingid = tp.id 
                WHERE tpo.id=:offeringid', ['offeringid' => $offeringid]);


        if (!$roleid) {
            $traineesql = "SELECT id FROM {role} 
                                    WHERE shortname = 'trainee'";
            $role_id = $DB->get_field_sql($traineesql);
        } else {
            $role_id = $roleid;
        }


        $org_official_roleid = $DB->get_field('role', 'id', array('shortname' => 'organizationofficial'));

        $manual = enrol_get_plugin('manual');

        $instance = $DB->get_record('enrol', array('courseid' => $program->courseid, 'enrol' => 'manual'), '*', MUST_EXIST);
        $manual->enrol_user($instance, $userid, $role_id, $timestart, $timeend);

        //$group = groups_get_group_by_idnumber($program->courseid, $program->offeringcode);

        $group = $DB->get_record_sql("SELECT grop.id FROM {groups} as grop JOIN {tp_offerings} as tpo ON tpo.code = grop.idnumber WHERE tpo.id = $offeringid");

        $groupid = (int) $group->id;

        if ($groupid) {
            groups_add_member($groupid, $userid, null, 0);
        }
        $is_current_user_org_official = $DB->record_exists_sql('SELECT id FROM {role_assignments} WHERE  contextid = ' . $systemcontext->id . ' AND roleid = ' . $org_official_roleid . ' AND userid IN (' . $USER->id . ')');
        if ($is_current_user_org_official) {
            $orgshortcode = $DB->get_field_sql('
                                SELECT lorg.shortname FROM {local_organization} AS lorg 
                                JOIN {local_users} AS lou ON lou.organization = lorg.id 
                                WHERE lou.userid =' . $USER->id . ''
            );
            $gorupidnumber1 = $orgshortcode . $program->offeringcode;
            $gorupidnumber2 = $USER->id . '_' . $program->offeringcode;
            $orggroupid = $DB->get_field_sql(
                "
                            SELECT grop.id FROM {groups} AS grop 
                            JOIN {groups_members} AS gropm ON gropm.groupid = grop.id 
                            WHERE gropm.userid =$USER->id AND grop.courseid = $program->courseid AND (grop.idnumber = '$gorupidnumber1' OR  grop.idnumber = '$gorupidnumber2')"
            );
            if ($orggroupid) {
                groups_add_member($orggroupid, $userid, null, 0);
            }
        }
        $row = array();
        $row['programid'] = $program->id;
        $row['offeringid'] = $offeringid ? $offeringid : 0;
        $row['courseid'] = $program->courseid;
        $row['userid'] = $userid;
        $row['roleid'] = $role_id;
        $row['timecreated'] = $enrolldate;
        $row['usercreated'] = $USER->id;
        $record = $DB->insert_record('program_enrollments', $row);

        if ($role_id) {
            $tpdata = new \stdClass();
            $tps = $DB->get_record('local_trainingprogram', array('id' => $program->id));
            $tpdata->program_name = $tps->name;
            $tpdata->program_arabicname = $tps->namearabic;
            $tpusers = $DB->get_record('local_users', array('userid' => $userid));

            $tpdata->program_userfullname = $tpusers->firstname . $tpusers->lastname;
            $tpdata->program_userarabicfullname = $tpusers->firstnamearabic . $tpusers->lastnamearabic;
            $trainee = $DB->get_record('user', array('id' => $userid));
            // (new \local_trainingprogram\notification())->trainingprogram_notification('trainingprogram_enroll', $touser=$trainee,$fromuser=$USER,$tpdata,$waitinglistid=0);
        }

        $trainee_role_id = $DB->get_field('role', 'id', array('shortname' => 'trainee'));

        if ($role_id == $trainee_role_id) {

            (new \tool_product\product)->upadte_availableseats('tp_offerings', 'id', $offeringid, -1);
        }

        return $record;

    }

    public function get_available_seats($offeringid)
    {
        global $DB, $USER;

        $offeringid = (int) $offeringid;
        $systemcontext = context_system::instance();
        $traineeeid = $DB->get_field('role', 'id', array('shortname' => 'trainee'));

        $totalseatssql = " SELECT  tp.availableseats AS total
                                         FROM {tp_offerings} tp
                                        WHERE tp.id = $offeringid";

        $total = $DB->get_field_sql($totalseatssql);
        if (!isloggedin() || (isloggedin() && (is_siteadmin() || has_capability('local/organization:manage_trainingofficial', $systemcontext) || has_capability('local/organization:manage_communication_officer', $systemcontext) || has_capability('local/organization:manage_trainee', $systemcontext)) || !$DB->record_exists('role_assignments', array('contextid' => $systemcontext->id, 'userid' => $USER->id)))) {

            if (!is_siteadmin() && has_capability('local/organization:manage_trainee', $systemcontext)) {

                $enrolled = $DB->get_field_sql(" SELECT count(pe.userid) AS total
                                  FROM {program_enrollments} AS pe
                                 WHERE pe.offeringid = $offeringid AND pe.roleid = $traineeeid");
            } elseif (!is_siteadmin() && has_capability('local/organization:manage_organizationofficial', $systemcontext)) {


                $enrolled = $DB->get_field_sql(" SELECT count(pe.userid) AS total
                                  FROM {program_enrollments} AS pe
                                 WHERE pe.offeringid = $offeringid AND pe.roleid = $traineeeid  AND pe.usercreated = $USER->id");

            } else {

                $enrolled = $DB->get_field_sql(" SELECT count(pe.userid) AS total
                                  FROM {program_enrollments} AS pe
                                 WHERE pe.offeringid = $offeringid AND pe.roleid = $traineeeid  AND ( pe.usercreated = $USER->id  OR pe.usercreated IN (SELECT userid FROM {role_assignments} WHERE  contextid = $systemcontext->id AND roleid = $traineeeid ))");

            }


            $purchasedseats = $DB->get_field_sql("SELECT SUM(purchasedseats) 
                             FROM {tool_org_order_seats} 
                            WHERE tablename = 'tp_offerings' AND fieldname = 'id' AND fieldid = $offeringid");
            $seats = $total - ($enrolled + $purchasedseats);
        } else {

            $available_seats = $DB->get_field_sql("SELECT SUM(availableseats)  
                                                             FROM {tool_org_order_seats} 
                                                            WHERE tablename = 'tp_offerings' AND fieldname = 'id' AND fieldid = $offeringid AND orguserid = $USER->id ");

            if ($available_seats > 0) {

                $seats = $available_seats;


            } else {

                $purchasedseats = $DB->get_field_sql("SELECT SUM(purchasedseats) 
                                 FROM {tool_org_order_seats} 
                                WHERE tablename = 'tp_offerings' AND fieldname = 'id' AND fieldid = $offeringid");

                $enrolled = $DB->get_field_sql(" SELECT count(userid) AS total
                                      FROM {program_enrollments} AS pe
                                     WHERE pe.offeringid = $offeringid AND pe.roleid = $traineeeid  AND pe.usercreated = $USER->id ");

                $seats = $purchasedseats - ($enrolled);
            }

        }

        return $seats;
    }

        public static function userexamenrolonlydelete_parameters()
    {
        return new external_function_parameters(
            array(
                'examcode' => new external_value(PARAM_RAW, 'examcode'),
                'profilecode' => new external_value(PARAM_RAW, 'profilecode'),
                'username' => new external_value(PARAM_RAW, 'username'),
                'hallcode' => new external_value(PARAM_RAW, 'hallcode'),
                'enrollmentdate' => new external_value(PARAM_RAW, 'enrollmentdate'),
                'examdate' => new external_value(PARAM_RAW, 'examdate'),
                'starttime' => new external_value(PARAM_RAW, 'starttime'),
                'endtime' => new external_value(PARAM_RAW, 'endtime'),
                'attemptcount' => new external_value(PARAM_RAW, 'attemptcount'),            
        )
        );
    }

    /**
     * Return the list of page question attempts in a given lesson.
     *
     * @param int $lessonid lesson instance id
     * @param int $attempt the lesson attempt number
     * @param bool $correct only fetch correct attempts
     * @param int $pageid only fetch attempts at the given page
     * @param int $userid only fetch attempts of the given user
     * @return array of warnings and page attempts
     * @since Moodle 3.3
     * @throws moodle_exception
     */
    public static function userexamenrolonlydelete($examcode, $profilecode,$username, $hallcode, $enrollmentdate,  $examdate, $starttime,$endtime,$attemptcount)
    {
        global $OUTPUT, $PAGE, $DB, $USER,$CFG;
        $start = explode(':', $starttime);
        $endtime = explode(':', $endtime);
        $starthour= $start[0];
        $startmin= $start[1];
        $startsec= $start[2];
        $endhour= $endtime[0];
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
        $day = date('l', $examdate);
        $CFG->debug = (E_ALL | E_STRICT);   // === DEBUG_DEVELOPER - NOT FOR PRODUCTION SERVERS!
        $CFG->debugdisplay = 1;
      
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
            $result->profilecode = 'attempts count is not exists. Please verify attempt count in exam setting: ' . $attemptcount;
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
        $achievementdates = strtotime($enrollmentdate);
        if ($achievementdates == 0) {
            $throw = new invalid_parameter_exception('achievementdate date should be correct format: ' . $achievementdate);
            throw $throw;
        }
        $courseid = $examids->courseid;
       // $examdate = $examids->examdatetime;
        $quizid = $profileids->quizid;
        //echo $examdate =date('Y-m-d H:i:s', $examdate/1000);
        //$examdate = date('Y-m-d H:i:s', $examdate);


        $exam_reservationid = $DB->get_record('hallschedule', ['hallid' => $hailid,'startdate' => $examdate, 'starttime' => $getstarttime], '*');
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
        $exam_logdata->examdate = $examdates;
        $exam_logdata->profileid = $profileid;
        $getadminuserid = $DB->get_field('user', 'id', ['username' => 'admin']);
        $exam_logdata->usercreated = $getadminuserid;
        $exam_logdata->timecreated = $achievementdates;
        $exam_logdata->hallscheduleid = $hallreservationid;
        $exam_enrollments = $DB->get_record('exam_enrollments', ['examid' => $examid,'profileid' => $profileid, 'userid' => $studentid, 'hallscheduleid' => $hallreservationid], '*');

        if (($exam_enrollments->id) !== "") {
                 
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
            //$id = $DB->insert_record('exam_enrollments', $exam_logdata);
            $id=1;
            $result->insertid = $id;
            $exam_completions_logdata = new stdClass();
            $exam_halls= new stdClass();
            $exam_completions_logdata->examid = $examid;
            $exam_completions_logdata->courseid = $courseid;
            $exam_completions_logdata->userid = $studentid;
            $exam_completions_logdata->hallid = $hailid;
            $getadminuserid = $DB->get_field('user', 'id', ['username' => 'admin']);
            $exam_completions_logdata->usercreated = $getadminuserid;
            $exam_completions_logdata->completion_status = 0;
            $exam_completions_logdata->completiondate = '';
            $exam_completions_logdata->examdate = $examdate;
            $exam_completions_logdata->profileid = $profileid;
            $exam_completions_logdata->attemptid = $exam_attemptscount;
            $exam_completions_logdata->timecreated = $achievementdates;
            $exam_completions_logdata->hallscheduleid = $hallreservationid;
            
            $exam_halls->hallid = $hailid;
            $exam_halls->startdate = $examdate;
            $exam_halls->starttime = $getstarttime;
            $exam_halls->endtime = $getendtime;
            $exam_halls->days = $day;
            $exam_halls->timecreated = $achievementdates;
            $exam_halls->timemodified = 0; 
           // $hall = $DB->insert_record('hallschedule', $exam_halls);

            //$ids = $DB->insert_record('exam_completions', $exam_completions_logdata);


                   $SQLattemptpurchases = "SELECT *
                FROM {local_exam_userhallschedules} 
                WHERE profileid=$profileid and examid=$examid and hallscheduleid= $hallreservationid and attemptid= $exam_attemptscount and examdate= $examdate and userid= $studentid  ORDER BY id Asc LIMIT 1, 100";
                    $getrecordsSQLattemptpurchases = $DB->get_records_sql($SQLattemptpurchases);
                  
                  //print_r($getrecords);
        $attemptpurchasescounts= count($getrecordsSQLattemptpurchases);
         if($attemptpurchasescounts>=1){
        
        foreach($getrecordsSQLattemptpurchases AS $getrecordsSQLattempt) {
                        
                        $id = $getrecordsSQLattempt->id;

                        $examid = $getrecordsSQLattempt->examid;
                        $userid = $getrecordsSQLattempt->userid;
                        $profileid = $getrecordsSQLattempt->profileid;
                       
                        $DB->delete_records('local_exam_userhallschedules', ['id' => $id]);
                }

                $result->profilecode = $profilecode. "- profilecode and examcode  $examcode  is deleted in  local_exam_userhallschedules table";
        $result->userid = $studentid . "- userid and username $username is deleted in  local_exam_userhallschedules table ";  

        }
        else{

       $result->profilecode = $profilecode. "- profilecode and examcode  $examcode  is not deleted only one enrolment this exam in  local_exam_userhallschedules table ";
        $result->userid = $studentid . "- userid and username $username is not deleted only one enrolment this exam in  local_exam_userhallschedules table ";  

        }
       


 $exam_userhallschedulesid = $DB->get_record('local_exam_userhallschedules', ['examid' => $examid,'profileid' => $profileid, 'hallscheduleid' => $hallreservationid,'attemptid' => $exam_attemptscount,'examdate' => $examdate,'userid' => $studentid], '*');
       $examidexist=$exam_userhallschedulesid->id;
       if($examidexist=='')
       {

            $record= $DB->insert_record('local_exam_userhallschedules', $exam_completions_logdata);
     }
            /*$gradeget = $DB->get_record('grade_items', ['iteminstance' => $quizid, 'courseid' => $courseid, 'itemmodule' => 'quiz'], '*', MUST_EXIST);*/
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
          $local_exam_attempts = $DB->get_record('tool_products', ['code' => $examcode, 'referenceid' => $exam_attemptscount], '*', MUST_EXIST);
             $productid=$local_exam_attempts->id;
                       if($attemptcount >=1){
                      
              $attemptpurchases_data = new stdClass();
             $attemptpurchases_data->productid = $productid;
             $attemptpurchases_data->examid = $examid;
             $attemptpurchases_data->referenceid = $exam_attemptscount;
             $attemptpurchases_data->referencetable = 'local_exam_attempts';
             $attemptpurchases_data->userid = $studentid;
             $attemptpurchases_data->timecreated =$achievementdates;

             $SQLattempt = "SELECT *
                FROM {local_exam_attemptpurchases} 
                WHERE productid=$productid and examid=$examid and referenceid= $exam_attemptscount and userid= $studentid  ORDER BY id Asc LIMIT 1, 100";
                    $getrecords = $DB->get_records_sql($SQLattempt);
                  
                  //print_r($getrecords);
        $arraycounts= count($getrecords);
                 if($arraycounts>=1){
        
        foreach($getrecords AS $SQLattemptlist) {
                        
                        $id = $SQLattemptlist->id;

                        $examid = $SQLattemptlist->examid;
                        $userid = $SQLattemptlist->userid;
                        $productid = $SQLattemptlist->productid;
                       
                        $DB->delete_records('local_exam_attemptpurchases', ['id' => $id]);
                }

        $result->profilecodes = $profilecode. "- profilecode and examcode  $examcode  is deleted in  local_exam_userhallschedules table";
        $result->userids = $studentid . "- userid and username $username is deleted in  local_exam_userhallschedules table ";  

        }
        else{

       $result->profilecodes = $profilecode. "- profilecode and examcode  $examcode  is not deleted only one enrolment this exam in  local_exam_userhallschedules table ";
        $result->userids = $studentid ."- userid and username $username is not deleted only one enrolment this exam in  local_exam_userhallschedules table ";  

        }


          $local_exam_attemptpurchasesid = $DB->get_record('local_exam_attemptpurchases', ['productid' => $productid,'examid' => $examid, 'referenceid' => $exam_attemptscount,'userid' => $studentid], '*');
       $local_examexamidexist=$local_exam_attemptpurchasesid->id;
       if($local_examexamidexist=='')
       {
            $quiz_attempts = $DB->insert_record('local_exam_attemptpurchases', $attemptpurchases_data);
     }
     
    }

            
 //$userexam_completions = $DB->execute("UPDATE {exam_completions} SET completiondate = $examdate WHERE examid=$examid and userid=$studentid and profileid=$profileid");

$updateexam_enrollments = $DB->execute("UPDATE {exam_enrollments} SET examdate =$examdate WHERE examid=$examid and userid=$studentid and profileid=$profileid ");
        $userhallschedules = $DB->execute("UPDATE {local_exam_userhallschedules} SET examdate = $examdate WHERE examid=$examid and userid=$studentid and profileid=$profileid and attemptid=$exam_attemptscount");
            if($grade!='No'){
           
           // $idgrade = $DB->insert_record('grade_grades', $gradedata);
                }
        }

        else{
                     $result->exception = 'Null';
            $result->errorcode = 'Null';
            $result->debuginfo = 'Null';
        } 
        /*else {
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
            if(!$DB->record_exists('grade_grades', ['itemid' => $gradegetresult,'userid' => $studentid], '*', MUST_EXIST)){
           // $idgrade = $DB->insert_record('grade_grades', $gradedata);
            }
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
        } */
        if ($id) {
                if($certificatecode!='No'){
                        if(($examidexist=='')&&($local_examexamidexist=='')) {
          //  $result->message = 'Exam enrolment Successfully: examcode: ' . $examcode . ' user: ' . $username . ' hallcode: ' . $hallcode;
             }
              else{
                                    // $result->message = 'Already Exam erolled  examcode:' . $examcode . ' user:' . $username . ' hallcode:' . $hallcode;

              }}
             else{
            // $result->message = 'Exam enrolment Successfully examcode: ' . $examcode . ' user: ' . $username . ' hallcode: ' . $hallcode;
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
                   /* self::issue_certificate_mig($studentid, $examid, 'exams', $exm_certificate, $status, $expiresdate = 0,$certificatecode,$achievementdate);*/
                  
                }
            }
        }
        return $result;
    }



    public static function userexamenrolonlydelete_returns()
    {
        return new external_single_structure(
            array(

                'exception' => new external_value(PARAM_RAW, 'exception'),
                'errorcode' => new external_value(PARAM_RAW, 'errorcode'),
                'profilecodes' => new external_value(PARAM_RAW, 'profilecodes'),
                'userids' => new external_value(PARAM_RAW, 'userids'),
                'profilecode' => new external_value(PARAM_RAW, 'profilecode'),
                'userid' => new external_value(PARAM_RAW, 'userids'),
                'debuginfo' => new external_value(PARAM_RAW, 'debuginfo'),


            )
        );
    }

    public static function userexamenrolonlyupdate_parameters()
    {
        return new external_function_parameters(
            array(
                'examcode' => new external_value(PARAM_RAW, 'examcode'),
                'profilecode' => new external_value(PARAM_RAW, 'profilecode'),
                'username' => new external_value(PARAM_RAW, 'username'),
                'hallcode' => new external_value(PARAM_RAW, 'hallcode'),
                'enrollmentdate' => new external_value(PARAM_RAW, 'enrollmentdate'),
                'examdate' => new external_value(PARAM_RAW, 'examdate'),
                'starttime' => new external_value(PARAM_RAW, 'starttime'),
                'endtime' => new external_value(PARAM_RAW, 'endtime'),
                'attemptcount' => new external_value(PARAM_RAW, 'attemptcount'),            
        )
        );
    }

    /**
     * Return the list of page question attempts in a given lesson.
     *
     * @param int $lessonid lesson instance id
     * @param int $attempt the lesson attempt number
     * @param bool $correct only fetch correct attempts
     * @param int $pageid only fetch attempts at the given page
     * @param int $userid only fetch attempts of the given user
     * @return array of warnings and page attempts
     * @since Moodle 3.3
     * @throws moodle_exception
     */
    public static function userexamenrolonlyupdate($examcode, $profilecode,$username, $hallcode, $enrollmentdate,  $examdate, $starttime,$endtime,$attemptcount)
    {
        global $OUTPUT, $PAGE, $DB, $USER,$CFG;
        $start = explode(':', $starttime);
        $endtime = explode(':', $endtime);
        $starthour= $start[0];
        $startmin= $start[1];
        $startsec= $start[2];
        $endhour= $endtime[0];
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
        $day = date('l', $examdate);
        $CFG->debug = (E_ALL | E_STRICT);   // === DEBUG_DEVELOPER - NOT FOR PRODUCTION SERVERS!
        $CFG->debugdisplay = 1;
      
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
        $achievementdates = strtotime($enrollmentdate);
        if ($achievementdates == 0) {
            $throw = new invalid_parameter_exception('achievementdate date should be correct format: ' . $achievementdate);
            throw $throw;
        }
        $courseid = $examids->courseid;
       // $examdate = $examids->examdatetime;
        $quizid = $profileids->quizid;
        //echo $examdate =date('Y-m-d H:i:s', $examdate/1000);
        //$examdate = date('Y-m-d H:i:s', $examdate);


        $exam_reservationid = $DB->get_record('hallschedule', ['hallid' => $hailid,'startdate' => $examdate, 'starttime' => $getstarttime], '*');
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
        $exam_logdata->examdate = $examdates;
        $exam_logdata->profileid = $profileid;
        $getadminuserid = $DB->get_field('user', 'id', ['username' => 'admin']);
        $exam_logdata->usercreated = $getadminuserid;
        $exam_logdata->timecreated = $achievementdates;
        $exam_logdata->hallscheduleid = $hallreservationid;
        $exam_enrollments = $DB->get_record('exam_enrollments', ['examid' => $examid,'profileid' => $profileid, 'userid' => $studentid, 'hallscheduleid' => $hallreservationid], '*');

        if (($exam_enrollments->id) !== "") {
                 
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
            //$id = $DB->insert_record('exam_enrollments', $exam_logdata);
            $id=1;
            $result->insertid = $id;
            $exam_completions_logdata = new stdClass();
            $exam_halls= new stdClass();
            $exam_completions_logdata->examid = $examid;
            $exam_completions_logdata->courseid = $courseid;
            $exam_completions_logdata->userid = $studentid;
            $exam_completions_logdata->hallid = $hailid;
            $getadminuserid = $DB->get_field('user', 'id', ['username' => 'admin']);
             $exam_completions_logdata->usercreated = $getadminuserid;
            $exam_completions_logdata->completion_status = 0;
            $exam_completions_logdata->completiondate = '';
            $exam_completions_logdata->examdate = $examdate;
            $exam_completions_logdata->profileid = $profileid;
            $exam_completions_logdata->attemptid = $exam_attemptscount;
            $exam_completions_logdata->timecreated = $achievementdates;
            $exam_completions_logdata->hallscheduleid = $hallreservationid;
            
            $exam_halls->hallid = $hailid;
            $exam_halls->startdate = $examdate;
            $exam_halls->starttime = $getstarttime;
            $exam_halls->endtime = $getendtime;
            $exam_halls->days = $day;
            $exam_halls->timecreated = $achievementdates;
            $exam_halls->timemodified = 0; 
           // $hall = $DB->insert_record('hallschedule', $exam_halls);

            //$ids = $DB->insert_record('exam_completions', $exam_completions_logdata);
 $exam_userhallschedulesid = $DB->get_record('local_exam_userhallschedules', ['examid' => $examid,'profileid' => $profileid, 'hallscheduleid' => $hallreservationid,'attemptid' => $exam_attemptscount,'examdate' => $examdate,'userid' => $studentid], '*');
       $examidexist=$exam_userhallschedulesid->id;
       if($examidexist=='')
       {

           // $record= $DB->insert_record('local_exam_userhallschedules', $exam_completions_logdata);
     }
            /*$gradeget = $DB->get_record('grade_items', ['iteminstance' => $quizid, 'courseid' => $courseid, 'itemmodule' => 'quiz'], '*', MUST_EXIST);*/
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
          $local_exam_attempts = $DB->get_record('tool_products', ['code' => $examcode, 'referenceid' => $exam_attemptscount], '*', MUST_EXIST);
             $productid=$local_exam_attempts->id;
                       if($attemptcount >1){
                      
              $attemptpurchases_data = new stdClass();
             $attemptpurchases_data->productid = $productid;
             $attemptpurchases_data->examid = $examid;
             $attemptpurchases_data->referenceid = $exam_attemptscount;
             $attemptpurchases_data->referencetable = 'local_exam_attempts';
             $attemptpurchases_data->userid = $studentid;
             $attemptpurchases_data->timecreated =$achievementdates;
          $local_exam_attemptpurchasesid = $DB->get_record('local_exam_attemptpurchases', ['productid' => $productid,'examid' => $examid, 'referenceid' => $exam_attemptscount,'userid' => $studentid], '*');
       $local_examexamidexist=$local_exam_attemptpurchasesid->id;
       if($local_examexamidexist=='')
       {
           // $quiz_attempts = $DB->insert_record('local_exam_attemptpurchases', $attemptpurchases_data);
     }

    }

            
 //$userexam_completions = $DB->execute("UPDATE {exam_completions} SET completiondate = $examdate WHERE examid=$examid and userid=$studentid and profileid=$profileid");

$updateexam_enrollments = $DB->execute("UPDATE {exam_enrollments} SET examdate =$examdate WHERE examid=$examid and userid=$studentid and profileid=$profileid ");
        $userhallschedules = $DB->execute("UPDATE {local_exam_userhallschedules} SET examdate = $examdate WHERE examid=$examid and userid=$studentid and profileid=$profileid and attemptid=$exam_attemptscount");
            if($grade!='No'){
           
           // $idgrade = $DB->insert_record('grade_grades', $gradedata);
                }
        }

        else{
                     $result->message = 'Already Exam erolled  examcode:' . $examcode . ' user:' . $username . ' hallcode:' . $hallcode;
                     $result->exception = 'Null';
            $result->errorcode = 'Null';
            $result->debuginfo = 'Null';
        } 
        /*else {
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
            if(!$DB->record_exists('grade_grades', ['itemid' => $gradegetresult,'userid' => $studentid], '*', MUST_EXIST)){
           // $idgrade = $DB->insert_record('grade_grades', $gradedata);
            }
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
        } */
        if ($id) {
                if($certificatecode!='No'){
                        if(($examidexist=='')&&($local_examexamidexist=='')) {
            $result->message = 'Exam enrolment Successfully: examcode: ' . $examcode . ' user: ' . $username . ' hallcode: ' . $hallcode;
             }
              else{
                                     $result->message = 'Already Exam erolled  examcode:' . $examcode . ' user:' . $username . ' hallcode:' . $hallcode;

              }}
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
                   /* self::issue_certificate_mig($studentid, $examid, 'exams', $exm_certificate, $status, $expiresdate = 0,$certificatecode,$achievementdate);*/
                  
                }
            }
        }
        return $result;
    }



    public static function userexamenrolonlyupdate_returns()
    {
        return new external_single_structure(
            array(

                'exception' => new external_value(PARAM_RAW, 'exception'),
                'errorcode' => new external_value(PARAM_RAW, 'errorcode'),
                'message' => new external_value(PARAM_RAW, 'message'),
                'debuginfo' => new external_value(PARAM_RAW, 'debuginfo'),


            )
        );
    }


}
