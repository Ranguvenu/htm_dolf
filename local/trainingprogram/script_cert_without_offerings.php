<?php
require_once('../../config.php');
global $CFG, $PAGE, $OUTPUT, $DB, $USER;
$systemcontext = context_system::instance();
$PAGE->set_context($systemcontext);
require_login();
$PAGE->set_url('/local/exams/examdates_mismatch.php');
$PAGE->set_title(get_string('exams', 'local_exams'));
$PAGE->set_heading(get_string('exams', 'local_exams'));
echo $OUTPUT->header();

$sqlfe = "SELECT (@cnt := @cnt + 1) AS rowNumber, CONCAT(lu.firstname , ' ', lu.lastname) as fullname, lu.id_number, tci.code as cert_code, tpo.code as off_code, FROM_UNIXTIME(tci.timecreated) as cert_time,FROM_UNIXTIME(tpo.startdate) as off_starttime,tpo.time, FROM_UNIXTIME(tpo.startdate+tpo.time) as offeringtime, tpo.id as offeringid,lu.userid,pe.programid, tpo.sections, ltp.name as programname  
	FROM {tool_certificate_issues} tci 
	JOIN {tp_offerings} tpo ON tpo.id = tci.moduleid 
	JOIN {program_enrollments} pe ON pe.offeringid = tpo.id 
	JOIN {local_users} lu ON lu.userid = tci.userid 
	JOIN {local_trainingprogram} ltp ON ltp.id = tpo.trainingid
	CROSS JOIN (SELECT @cnt := 0) AS dummy
	WHERE tci.moduletype = 'trainingprogram' AND tpo.trainingmethod != 'elearning' AND pe.userid = tci.userid AND tci.timecreated < tpo.startdate";
$lfe = $DB->get_records_sql($sqlfe, $params);
$i = 1;

echo '<table border = "1">
		<td></td>
		<td>fullname</td>
		<td>id_number</td>
		<td>cert_code</td>
		<td>off_code</td>
		<td>cert timecreated</td>
		<td>offeringtime</td>
		<td>program name</td>
';

foreach($lfe as $data){
echo '<tr>
		<td>'.$i.'</td>
		<td>'.$data->fullname.'</td>
		<td>'.$data->id_number.'</td>
		<td>'.$data->cert_code.'</td>
		<td>'.$data->off_code.'</td>
		<td>'.$data->cert_time.'</td>
		<td>'.$data->off_starttime.'</td>
		<td>'.$data->programname.'</td>';
$i++;
}

echo '</table>';
echo "<br/><br/> ---------------------Changing users from program completion to in-progress status----------------------------- <br/><br/>";
foreach($lfe as $data){
	echo "UPDATE mdl_program_completions SET completion_status = 0 WHERE programid = $data->programid AND offeringid = $data->offeringid AND userid = $data->userid AND completion_status IN (1,2); <br/>";
}


echo "<br/><br/> ---------------------Attendance/ Grades deletion----------------------------- <br/><br/>";

$attendance_moduleid = $DB->get_field('modules', 'id', array('name' => 'attendance'));
foreach($lfe as $data){
	$program_info = $DB->get_record('local_trainingprogram', array('id'=>$data->programid));

	$cm_sql = "SELECT * FROM {course_modules} WHERE course = $program_info->courseid AND module = $attendance_moduleid AND section=$data->sections";
	$cm_data = $DB->get_record_sql($cm_sql);
	if($cm_data){
		$gi_sql = "SELECT * FROM {grade_items} WHERE courseid = $program_info->courseid AND itemmodule = 'attendance' AND iteminstance=$cm_data->instance";
		$gi_data = $DB->get_record_sql($gi_sql);
		if($gi_data){
			
			/*$att_sess_sql = "SELECT al.* FROM {attendance_log} al 
			JOIN {offering_sessions} os ON os.sessionid = al.sessionid
			WHERE al.studentid = $data->userid AND os.offeringid = $data->offeringid AND os.programid = $data->programid";*/

			$att_sess_sql = "SELECT * FROM mdl_attendance_log WHERE studentid = $data->userid AND sessionid IN (SELECT sessionid FROM mdl_offering_sessions WHERE offeringid = $data->offeringid AND programid = $data->programid)";
			$att_sess_data = $DB->get_records_sql($att_sess_sql);
			foreach($att_sess_data as $att_data){
				echo "UPDATE mdl_attendance_sessions SET lasttaken = null AND lasttakenby = 0 WHERE id=$att_data->sessionid; <br/>";
				echo "DELETE FROM mdl_attendance_log WHERE id=$att_data->id ; <br/>";
			}
			// update attendance_sessions
			$update_data = new stdClass();
			$update_data->id = $att_sess_data->sessionid;
			$update_data->lasttaken = null;
			$update_data->lasttakenby = 0;

			// echo "UPDATE mdl_attendance_sessions SET lasttaken = null AND lasttakenby = 0 WHERE id=$att_sess_data->sessionid; <br/>";

			echo "DELETE FROM grade_grades_history WHERE source='mod/attendance' AND itemid=$gi_data->id,userid=$data->userid ; <br/>";

			echo "DELETE FROM grade_grades WHERE itemid=$gi_data->id AND userid=$data->userid ; <br/>";

			// echo "DELETE FROM attendance_log WHERE id=$att_sess_data->id ; <br/>";

			// $DB->update_record('attendance_sessions', $update_data);
			// $DB->delete_record('grade_grades_history', array(source=>'mod/attendance', itemid=>$gi_data->id,userid=>$data->userid));
			// $DB->delete_record('grade_grades', array(itemid=>$gi_data->id,userid=>$data->userid));
			// $DB->delete_record('attendance_log', array(id=>$att_sess_data->id));
		}
	}
}