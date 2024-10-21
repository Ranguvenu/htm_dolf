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
$sqlfe = "SELECT fe.id as fastid,u.id as userid, le.id as eexamid,ep.id as pprofileid, h.id as hhallid,fe.examdatetime, fe.username, fe.examcode, fe.profilecode 
			FROM mdl_local_fast_examenrol fe
			JOIN mdl_user u ON u.username = fe.username
			JOIN mdl_local_exams le ON le.code = fe.examcode
			JOIN mdl_local_exam_profiles ep ON ep.profilecode = fe.profilecode
			JOIN mdl_hall h ON h.code = fe.centercode
			WHERE fe.transactiontypes = 1 AND fe.validation = 0 AND fe.errormessage IS NULL";

$lfe = $DB->get_records_sql($sqlfe, $params);
$i = 1;
echo '<table border = "1">
		<td></td>
		<td>User_id</td>
		<td>User_name</td>
		<td>Examid</td>
		<td>Exam Code</td>
		<td>Profileid</td>
		<td>Profile Code</td>
		<td>Hallid</td>
		<td>Examdatetime</td>
		<td>hallscheduleid</td>
		<td>hallexamdate</td>
';

foreach($lfe as $data){
	$data->fe_exam_stamp = strtotime($data->examdatetime);
    $data->fe_examdate = userdate($data->fe_exam_stamp,'%Y-%m-%d');
    $data->fe_exam_date_stamp = strtotime(userdate($data->fe_exam_stamp,'%Y-%m-%d'));
    $userhallschedules = $DB->get_record_sql("SELECT * FROM mdl_local_exam_userhallschedules 
		WHERE examid = $data->eexamid AND userid = $data->userid AND profileid = $data->pprofileid ORDER BY id DESC LIMIT 1");
    $data->h_hallscheduleid = $userhallschedules->hallscheduleid;
    $data->h_examdate = $userhallschedules->examdate;
    $data->h_examdatefor = $userhallschedules->examdate ? userdate($userhallschedules->examdate,'%Y-%m-%d') : '';

	if($data->fe_examdate != $data->h_examdatefor){
		echo '<tr>
		<td>'.$i.'</td>
		<td>'.$data->userid.'</td>
		<td>'.$data->username.'</td>
		<td>'.$data->eexamid.'</td>
		<td>'.$data->examcode.'</td>
		<td>'.$data->pprofileid.'</td>
		<td>'.$data->profilecode.'</td>
		<td>'.$data->hhallid.'</td>
		<td>'.$data->examdatetime.'</td>
		<td>'.$data->h_hallscheduleid.'</td>
		<td>'.$data->h_examdatefor.'</td>
		</tr>';
	$i++;
		// echo "UPDATE mdl_local_exam_userhallschedules SET examdate = $data->fe_exam_date_stamp WHERE id = $userhallschedules->id".'</br>';

	}
}
echo '</table>';
echo $OUTPUT->footer();