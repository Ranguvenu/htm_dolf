
<?php
// This file is part of Moodle - http://moodle.org/
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
// along with Moodle.  If not, see <http://www.gnu.org/licenses/>.
/**
 * Competency view page
 *
 * @package    local_trainingprogram
 * @copyright  2022 e abyas  <info@eabyas.com>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
defined('MOODLE_INTERNAL') || die();

require_once($CFG->libdir.'/accesslib.php');


class local_trainingprogram_observer{

	public static function trainingprogram_exams_completion_updated(\core\event\course_module_completion_updated $event){
		global $DB, $CFG;

        $eventdata = $event->get_record_snapshot('course_modules_completion', $event->objectid);


        $userid = $event->relateduserid;
        $mod = $event->contextinstanceid;


        $cmidsql = "SELECT com.id,com.course,com.instance,cos.id as sectionid FROM {course_modules} as com 
        JOIN {course_sections} as cos ON com.section = cos.id AND com.course = cos.course WHERE com.id=:modid";

        $cmparams = array('modid'=>$mod);


        $coursemodule = $DB->get_record_sql($cmidsql, $cmparams);


        $modid=$coursemodule->instance;


        if($modid){


            $sql = "SELECT tp.id as programid,tpofr.prequiz as preexam,tpofr.postquiz as postexam,prel.userid,tp.competencyandlevels as competencies,tp.attendancecmpltn,tp.attendancepercnt,tp.courseid,tpofr.id as offeringid 
                              FROM {local_trainingprogram} as tp
                              JOIN {tp_offerings} as tpofr ON tpofr.trainingid=tp.id
                              JOIN {program_enrollments} as prel ON prel.offeringid=tpofr.id AND prel.programid=tp.id
                              WHERE tp.courseid =:courseid AND tpofr.sections =:sectionid AND prel.userid=:userid";


            $params = array('userid'=>$userid,'courseid'=>$coursemodule->course,'sectionid'=>$coursemodule->sectionid);


            $trainingprogram = $DB->get_record_sql($sql, $params);


            if ($trainingprogram) {

                $trainingprogram->cmid=$modid;
                
                $trainingprogram->completionstate = ($eventdata->completionstate == 1 || $eventdata->completionstate == 2 ) ? 1  : $eventdata->completionstate;

                (new local_trainingprogram\local\trainingprogram)->update_trainingprogram_status($trainingprogram);
            }
        }
        
	}

    public static function trainingprogram_attendance_taken(\mod_attendance\event\attendance_taken $event){
        global $DB, $CFG;

        $eventdata = $event->get_record_snapshot('attendance', $event->objectid);


        $sql = "SELECT atdnlg.id,tp.id as programid,tpofr.prequiz as preexam,tpofr.postquiz as postexam,prel.userid,tp.attendancecmpltn,tp.attendancepercnt,tp.courseid,atdnlg.studentid as userid,tpofr.id as offeringid  
                        FROM {attendance_sessions} as atdnsn 
                        JOIN {attendance_log} as atdnlg ON atdnlg.sessionid=atdnsn.id 
                        JOIN {offering_sessions} as ofsn ON ofsn.sessionid=atdnlg.sessionid 
                        JOIN {tp_offerings} as tpofr ON tpofr.id=ofsn.offeringid 
                        JOIN {local_trainingprogram} as tp ON tp.id=tpofr.trainingid 
                        JOIN {program_enrollments} as prel ON prel.programid=tp.id AND prel.userid=atdnlg.studentid
                        WHERE atdnsn.id=:sessionid";


        $params = array('sessionid'=>$event->other['sessionid']);

        $trainingprograms = $DB->get_records_sql($sql, $params);


        if ($trainingprograms) {

            foreach($trainingprograms as $trainingprogram){

                    (new local_trainingprogram\local\trainingprogram)->update_trainingprogram_status($trainingprogram);
                    
            }

        }
    }

    public static function assign_trainingofficla_as_manager_to_program_category(\core\event\role_assigned $event){
        global $DB, $CFG;

        $eventdata = $event->get_data();
        $roleid =  (int)$eventdata['objectid'];
        $userid =  $eventdata['relateduserid'];
        $catagoryid = $DB->get_field('course_categories','id',array('idnumber' => 'trainingprogram'));
        $context = context_coursecat::instance($catagoryid);

        $to_role_id = (int)$DB->get_field('role','id',array('shortname' => 'to'));
        $manager_role = (int)$DB->get_field('role','id',array('shortname' => 'manager'));

        if($roleid == $to_role_id) {
            role_assign($manager_role, $userid, $context->id);
        }
    }


    public static function unassign_trainingofficla_as_manager_to_program_category(\core\event\role_unassigned $event){
        global $DB, $CFG;

        $eventdata = $event->get_data();
        $roleid =  (int)$eventdata['objectid'];
        $userid =  $eventdata['relateduserid'];
        $catagoryid = $DB->get_field('course_categories','id',array('idnumber' => 'trainingprogram'));
        $context = context_coursecat::instance($catagoryid);

        $to_role_id = (int)$DB->get_field('role','id',array('shortname' => 'to'));
        $manager_role = (int)$DB->get_field('role','id',array('shortname' => 'manager'));

        if($roleid == $to_role_id) {
            role_unassign($manager_role, $userid, $context->id);
        }
    }

    public static function delete_program_based_on_course_deleted(\core\event\course_deleted $event){
        global $DB, $CFG;
        $eventdata = $event->get_data();
        $courseid = (int)$eventdata['courseid'];
        $programid = (int) $DB->get_field('local_trainingprogram','id',array('courseid'=>$courseid));
        (new local_trainingprogram\local\trainingprogram)->remove_training_program($programid);
    }


    public static function restrict_user_to_be_enrolled_multiple_roles(\core\event\role_assigned $event){
        global $DB, $CFG;
   
        $eventdata = $event->get_data();

        $roleid =  (int)$eventdata['objectid'];
        $userid =  $eventdata['relateduserid'];

        $sys_context = context_system::instance();

        $roles_count =(int) $DB->get_field_sql('SELECT COUNT(DISTINCT id) FROM {role_assignments} WHERE   contextid = '.$sys_context->id.' AND  userid = '.$userid.'');


        if($roles_count > 1) {

            $existing_role_id = (int) $DB->get_field_sql('SELECT roleid FROM {role_assignments} WHERE   contextid = '.$sys_context->id.' AND  userid = '.$userid.' AND roleid <> '.$roleid.' ORDER BY timemodified ASC');

            if($existing_role_id) {

                $isexistingroleistrineeortrainerorexpert = $DB->record_exists_sql(
                    "SELECT id FROM {role} 
                    WHERE id = $existing_role_id
                    AND (shortname = 'trainee' OR shortname = 'expert' OR shortname = 'trainer')");

                $iscurrentroleistrineeortrainerorexpert = $DB->record_exists_sql(
                    "SELECT id FROM {role} 
                    WHERE id = $roleid
                    AND (shortname = 'trainee' OR shortname = 'expert' OR shortname = 'trainer')");


                if($isexistingroleistrineeortrainerorexpert){

                    if(!$iscurrentroleistrineeortrainerorexpert) {

                        role_unassign($existing_role_id, $userid, $sys_context->id);  
                    }

                } else {
                    role_unassign($existing_role_id, $userid, $sys_context->id); 
                }

                   
            }
        }

    }
    public static function trainingprogram_course_completion_updated(\core\event\course_completed $event){
        global $DB;

        $eventdata = $event->get_record_snapshot('course_completions', $event->objectid);

        $userid = $event->relateduserid;

        $sql = "SELECT tp.id as programid,prel.userid,tp.courseid,tpofr.id as offeringid 
                              FROM {local_trainingprogram} as tp
                              JOIN {tp_offerings} as tpofr ON tpofr.trainingid=tp.id
                              JOIN {program_enrollments} as prel ON prel.offeringid=tpofr.id AND prel.programid=tp.id
                              WHERE (tp.courseid= :courseid) AND prel.userid=:userid";

        $params = array('courseid' => $event->courseid,'userid'=>$userid);

        $trainingprogram = $DB->get_record_sql($sql, $params);

        if ($trainingprogram) {


            $trainingprogram->course_completed=($eventdata->timecompleted) ? 1 : 0;

            (new local_trainingprogram\local\trainingprogram)->update_trainingprogram_status($trainingprogram);
        }
    }
    public static function orgoff_refundlogs(\local_trainingprogram\event\orgoff_refundlogs_created $event){
        global $DB, $CFG, $USER;

        $data = [];
        $data['productid'] = $event->other['productid'];
        $data['orguserid'] = $event->other['orguserid'];
        $data['oldsadadid'] = $event->other['oldsadadid'];
        $data['oldinvoiceamount'] = $event->other['oldinvoiceamount'];
        $data['newinvoiceamount'] = $event->other['newinvoiceamount'];
        $data['type'] = $event->other['type'];
        $data['usercreated'] = $USER->id;
        $data['timecreated'] = time();
        $data['timemodified'] = time();
        $data['usermodified'] = 0;
        $DB->insert_record('tool_product_orgrefund_logs', $data);

    }


    public static function trainingprogram_activity_deletion_update(\core\event\course_module_deleted $event) {
        global $DB;
        $eventdata = $event->get_record_snapshot('course_modules', $event->objectid);
        if($event->other['modulename'] == 'quiz') {
            $sql = " SELECT tp.id as programid,tpofr.prequiz as preexam,tpofr.postquiz as postexam,prel.userid,
            tp.attendancecmpltn,tp.attendancepercnt,tp.courseid,tpofr.id as offeringid 
            FROM {local_trainingprogram} as tp
            JOIN {tp_offerings} as tpofr ON tpofr.trainingid=tp.id
            JOIN {program_enrollments} as prel ON prel.offeringid=tpofr.id AND prel.programid=tp.id
            WHERE tp.courseid =$eventdata->course AND (tpofr.prequiz =$eventdata->instance OR tpofr.postquiz=$eventdata->instance) ";
            $trainingprograms = $DB->get_records_sql($sql);
            if($trainingprograms) {
                foreach($trainingprograms as $trainingprogram){
                    (new local_trainingprogram\local\trainingprogram)->update_trainingprogram_status($trainingprogram);
                }
            }
            
            if($DB->record_exists('tp_offerings',['prequiz' => $eventdata->instance])) {
                $offering = $DB->get_record('tp_offerings',['prequiz' => $eventdata->instance]);
                $DB->execute(" UPDATE {tp_offerings} SET prequiz= NULL WHERE id = $offering->id");
            }
            
            if($DB->record_exists('tp_offerings',['postquiz' => $eventdata->instance])) {
                $offering = $DB->get_record('tp_offerings',['postquiz' => $eventdata->instance]);
                $DB->execute(" UPDATE {tp_offerings} SET postquiz= NULL WHERE id = $offering->id");
            }
            
        } else if($event->other['modulename'] == 'attendance') {
            $sql = " SELECT tp.id as programid,tpofr.prequiz as preexam,tpofr.postquiz as postexam,prel.userid,
            tp.attendancecmpltn,tp.attendancepercnt,tp.courseid,tpofr.id as offeringid 
                    FROM {local_trainingprogram} as tp
                    JOIN {tp_offerings} as tpofr ON tpofr.trainingid=tp.id
                    JOIN {program_enrollments} as prel ON prel.offeringid=tpofr.id AND prel.programid=tp.id
                    WHERE tp.courseid = $eventdata->course ";
            $trainingprograms = $DB->get_records_sql($sql);
            if ($trainingprograms) {
                foreach($trainingprograms as $trainingprogram){
                    (new local_trainingprogram\local\trainingprogram)->update_trainingprogram_status($trainingprogram);
                }
            }
            $offering_sessions = $DB->get_fieldset_sql(" SELECT sessionid FROM {offering_sessions} WHERE courseid = $eventdata->course");
            if($offering_sessions) {
                foreach($offering_sessions as $session) {
                    //echo $session;
                    if(!$DB->record_exists('attendance_sessions',['id' => $session])) {
                       // echo " ATTENDANCE  " .$session;
                        $DB->delete_records('offering_sessions',['sessionid' => $session, 'courseid' => $eventdata->course]);
                    }
                }
            }
        }
        $offering_sql = " SELECT tpo.sections FROM {tp_offerings} tpo JOIN {local_trainingprogram} tp ON tp.id = tpo.trainingid
        WHERE tp.courseid = $eventdata->course ";
        $offerings = $DB->get_fieldset_sql($offering_sql);
        if($offerings) {
            foreach($offerings as $section) {
                if(!$DB->record_exists('course_sections',['id' => $section])) {
                    $offering_section = $DB->get_record('tp_offerings',['sections' => $section]);
                    $DB->execute(" UPDATE {tp_offerings} SET sections= 0 WHERE id = $offering_section->id");
                }
            }
        }
    }
    /**
     * Observe the creation of modules in a course
     * and if the module creator is a trainer then the module he/she will create will be unpublished unless and untill
     * some higher level authority will approve it.
     * 
     */
    public static function create_module_observer(\core\event\course_module_created $event){
        global $DB, $CFG, $USER;
        // TO DO: Get the current user role and proceed further accordingly.
        $eventdata = $event->get_data();
        $roleobj = (new \local_exams\local\exams())->get_user_role($USER->id);
        if ($roleobj->shortname == 'editingtrainer') {
            $DB->update_record('course_modules', ['id' => $eventdata['objectid'], 'visible' => '0', 'visibleold' => '0']);
        }
        
    }
    /**
     * Observe the Updation of modules in a course
     * and if the module creator is a editingtrainer then the module he/she will create will be unpublished unless and untill
     * some higher level authority will approve it.
     * 
     */
    public static function update_module_observer(\core\event\course_module_updated $event){
        global $DB, $CFG, $USER;
        // TO DO: Get the current user role and proceed further accordingly.
        $eventdata = $event->get_data();
        $roleobj = (new \local_exams\local\exams())->get_user_role($USER->id);
        if ($roleobj->shortname == 'editingtrainer') {
            $DB->update_record('course_modules', ['id' => $eventdata['objectid'], 'visible' => 0, 'visibleold' => '0']);
        }
    }
}
