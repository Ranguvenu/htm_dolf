<?php
defined('MOODLE_INTERNAL') || die();
function xmldb_local_exams_uninstall(){
	global $DB;
	$dbman = $DB->get_manager();

    $table = new xmldb_table('course_categories');
	if ($dbman->table_exists($table)) {
		$id = $DB->get_field('course_categories', 'id', ['idnumber' => 'exams']);
		$DB->delete_records('course', ['category' => $id]);		
		$DB->delete_records('course_categories', ['idnumber' => 'exams']);
	}
	$parentid = $DB->get_field('local_notification_type', 'id', array('shortname' => 'exams'));
	if($parentid){
		$notifytype=$DB->get_records('local_notification_type', array('parent_module' => $parentid));
		foreach($notifytype as $notifytypes){
			$emaillogs=	$DB->get_records('local_notification_info', array('notificationid' => $notifytypes->id));
			foreach($emaillogs as $emaillog){
				$DB->delete_records('local_emaillogs', array('notification_infoid' => $emaillog->id));
			}
			$DB->delete_records('local_notification_info', array('notificationid' => $notifytypes->id));
		}
		$DB->delete_records('local_notification_type', array('parent_module' => $parentid));
		$DB->delete_records('local_notification_type', array('id' => $parentid));
	}
}