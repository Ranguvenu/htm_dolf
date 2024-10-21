<?php
// This file is part of Moodle - htexm://moodle.org/
//
// Moodle is free software: you can redistribute it and/or modify
// it under the terms of the GNU General Public License as published by
// the Free Software Foundation, either version 3 of the License, or
// (at your option) any later version.
//
// Moodle is distributed in the hope that it will be useful,
// but WITHOUT ANY WARRANTY; without even the implied warranty of
// MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
// GNU General Public License for more details.
//
// You should have received a copy of the GNU General Public License
// along with Moodle.  If not, see <htexm://www.gnu.org/licenses/>.
/**
 * Competency view page
 *
 * @package    local_exams
 * @copyright  2022 e abyas  <info@eabyas.com>
 * @license    htexm://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
defined('MOODLE_INTERNAL') || die();
use stdClass;

class local_exams_observer{

    public const EXAMS = 2;

	public static function exam_completion_updated(\core\event\course_module_completion_updated $event){
		global $DB, $CFG;
        $eventdata = $event->get_record_snapshot('course_modules_completion', $event->objectid);
        $userid = $event->relateduserid;
        $mod = $event->contextinstanceid;
        $modid=$DB->get_field('course_modules','instance',  array('id'=>$mod));
        if($modid){
            $sql = "SELECT exm.id as examid, lep.quizid, lep.id as profileid, exmel.userid, exm.competencies 
                      FROM {local_exams} as exm
                      JOIN {exam_enrollments} as exmel ON exmel.examid=exm.id
                      JOIN {local_exam_profiles} as lep ON lep.id = exmel.profileid
                     WHERE lep.quizid= :cminstanceid AND exmel.userid=:userid";

            $params = array('cminstanceid' => $modid,'userid'=>$userid);
            $exams = $DB->get_record_sql($sql, $params);
            if ($exams) {
                $exams->cmid=$modid;
                $exams->completionstate = $eventdata->completionstate;
                (new local_exams\local\exams)->update_exams_status($exams);
            }
        }
	}

    public static function assign_examofficl_as_manager_to_exam_category(\core\event\role_assigned $event){
        global $DB, $CFG;

        $eventdata = $event->get_data();
        $roleid =  (int)$eventdata['objectid'];
        $userid =  $eventdata['relateduserid'];
        $catagoryid = $DB->get_field('course_categories','id',array('idnumber' => 'exams'));
        $context = context_coursecat::instance($catagoryid);

        $to_role_id = (int)$DB->get_field('role','id',array('shortname' => 'examofficial'));
        $manager_role = (int)$DB->get_field('role','id',array('shortname' => 'manager'));

        if($roleid == $to_role_id) {
            role_assign($manager_role, $userid, $context->id);
        }
    }

    public static function unassign_examofficl_as_manager_to_exam_category(\core\event\role_unassigned $event){
        global $DB, $CFG;

        $eventdata = $event->get_data();
        $roleid =  (int)$eventdata['objectid'];
        $userid =  $eventdata['relateduserid'];
        $catagoryid = $DB->get_field('course_categories','id',array('idnumber' => 'exams'));
        $context = context_coursecat::instance($catagoryid);

        $to_role_id = (int)$DB->get_field('role','id',array('shortname' => 'examofficial'));
        $manager_role = (int)$DB->get_field('role','id',array('shortname' => 'manager'));

        if($roleid == $to_role_id) {
            role_unassign($manager_role, $userid, $context->id);
        }
    }

    public static function delete_exams_based_on_course_deleted(\core\event\course_deleted $event){
        global $DB, $CFG;
        $eventdata = $event->get_data();
        $courseid = (int)$eventdata['courseid'];
        $examid = (int) $DB->get_field('local_exams','id',array('courseid'=>$courseid));
        (new local_exams\local\exams)->delete_exam($examid);
    }
    public static function trainee_examschedules(\local_exams\event\trainee_schedules $event){
        global $DB, $USER;
        switch($event->other['category']){
            case self::EXAMS :
                         

                    $productid = $DB->get_field('tool_products', 'id', ['category' => 2, 'referenceid' => $event->other['entityid']]);
                    self::create_traineeschedule($productid, 'exam', $event->other['examdate'], $event->other['userid'], $event->other['hallscheduleid']);
                

                break;
        }
    }
    public function create_traineeschedule($productid, $entitytype, $examdate, $userid, $hallscheduleid)
    {
        global $DB, $USER;
        $systemcontext = context_system::instance();
        $id = $DB->get_field('local_schedule_logs', 'id', ['productid' => $productid, 'entitytype'=> $entitytype, 'userid' => $userid]);
        $data = new stdClass();
        $data->productid = $productid;
        $data->entitytype = $entitytype;
        $data->userid = $userid;
        $data->usercreated = $USER->id;
        $data->hallscheduleid = !empty($hallscheduleid) ? $hallscheduleid : 0;

        if (!empty($id)) {
            $data->id = $id;
            $data->timemodified = time();
            $data->newscheduledate = $examdate;
            $scheduledate = $DB->get_record('local_schedule_logs', array('id'=>$id));
            if( $scheduledate->oldscheduledate == 0){
                $data->oldscheduledate = $data->newscheduledate;
            }
            $data->oldhallscheduleid = ($scheduledate->newhallscheduleid == 0) ? $data->hallscheduleid : $scheduledate->newhallscheduleid ;
            $data->newhallscheduleid = $data->hallscheduleid;

            $DB->update_record('local_schedule_logs', $data);
            //$schedules = $DB->get_record('local_schedule_logs',array('id'=>$id,'entitytype'=>'exam'));
            // if($schedules->oldscheduledate != 0 && $schedules->oldscheduledate != $schedules->newscheduledate  ){
            //     $products =   $DB->get_record('tool_products',array('id'=>$schedules->productid ,'category'=>2));
            //     $profiles = $DB->get_record('local_exam_profiles',array('id'=>$products->referenceid,'profilecode'=>$products->code));
            //     $examdetails = $DB->get_record('local_exams',array('id'=>$profiles->examid));           
            //     $localuserrecord = $DB->get_record('local_users',['userid'=> $schedules->userid]);
            //     $notificationdetails = new stdClass();
            //     $notificationdetails->pastexam_date = userdate( $schedules->oldscheduledate, '%d-%m-%Y');
            //     $notificationdetails->pastexam_time =   $pastexamtime;               
            //     $notificationdetails->presentexam_date = userdate($schedules->newscheduledate, '%d-%m-%Y');
            //     $notificationdetails->presentexam_time = $presentexamtime; 
            //     $notificationdetails->exam_name = $examdetails->exam;
            //     $notificationdetails->arabicexam_name = $examdetails->examnamearabic;
            //     $fname = ($localuserrecord)? (($localuserrecord->lang == 'ar') ? $localuserrecord->firstnamearabic.' '.$localuserrecord->middlenamearabic.' '.$localuserrecord->thirdnamearabic.' '.$localuserrecord->lastnamearabic  : $localuserrecord->firstname.' '.$localuserrecord->middlenameen.' '.$localuserrecord->thirdnameen.' '.$localuserrecord->lastname) :fullname($DB->get_record('user',array('id'=>$userid)));
            //     $notificationdetails->exam_userfullname = $localuserrecord->firstname.' '.$localuserrecord->middlenameen.' '.$localuserrecord->thirdnameen.' '.$localuserrecord->lastname;
            //     $notificationdetails->exam_arabicuserfullname =  $fname;
            //     $trainee = $DB->get_record('user',array('id'=>$schedules->userid));  

            //     (new \local_exams\notification)->exams_notification('exam_reschedule', $touser=$trainee,$fromuser= get_admin(), $notificationdetails,$waitinglistid=0);
            //     $event = \local_exams\event\exam_reschedule::create(
            //         array( 
            //             'context'=>$systemcontext,
            //             'relateduserid'=>$trainee->id,
            //             'objectid' =>$examdetails->id,
            //             'other'=>array(
            //                 'Message'=>'Reschedule In Exam'    
            //             )
            //             )
            //         );  
            //     $event->trigger();
            // }
        } else {
            $data->timecreated = time();
            $data->oldscheduledate = $examdate;
            $data->newscheduledate = $examdate;
            $data->oldhallscheduleid = $data->hallscheduleid;
            $data->newhallscheduleid = $data->hallscheduleid;

            $DB->insert_record('local_schedule_logs', $data);
        }
    }
}
