<?php
/**
 * This file is part of eAbyas
 *
 * Copyright eAbyas Info Solutons Pvt Ltd, India
 *
 * This program is free software; you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation; either version 3 of the License, or
 * (at your option) any later version.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with this program.  If not, see <http://www.gnu.org/licenses/>.
 *
 * @author eabyas  <info@eabyas.in>
 * @package local_notifications
 */


define('AJAX_SCRIPT', true);
require_once(dirname(__FILE__) . '/../../config.php');
global $CFG, $USER, $PAGE, $OUTPUT,$DB;

$sitecontext = context_system::instance();
require_login();

if(!has_capability('local/notifications:manage', $sitecontext) && !has_capability('local/organization:manage_communication_officer',$sitecontext)){

    throw new required_capability_exception($sitecontext, 'local/notifications:manage', 'nopermissions', '');
}

if ($_REQUEST['columns'][1]['search'] != "" ){
   $organization_search=$_REQUEST['columns'][1]['search']['value'] ;
}
if ( $_REQUEST['columns'][2]['search'] != "" ){
      $status_search=$_REQUEST['columns'][2]['search']['value'] ;
}


$countquery="SELECT count(le.id) FROM {local_emaillogs} AS le INNER JOIN {local_notification_info} AS ni ON le.notification_infoid=ni.id where 1=1 ";

$select_query = "SELECT le.id,le.notification_infoid,le.from_userid,le.to_userid,le.status,
	le.timecreated,le.sent_date,ni.notificationid, lnt.name AS notification_type,le.from_emailid,le.to_emailid
	FROM {local_emaillogs} AS le
	INNER JOIN {local_notification_info} AS ni ON le.notification_infoid=ni.id 
	JOIN {local_notification_type} as lnt ON lnt.id=ni.notificationid
	WHERE 1=1";

$systemcontext = context_system::instance();
$params = array();

if(isset($status_search) && $status_search != ""){

	
	if($status_search){
		$cond_query .= " AND le.status=:status ";
	}else{
		$cond_query .= " AND (le.status=:status OR le.status IS NULL) ";
	}
	$params['status'] = $status_search;

	
}

 $resultcount=$DB->count_records_sql($countquery.$cond_query, $params);

 $cond_query .=" order by id desc";
 $select_query.=$cond_query;
 $result = $DB->get_records_sql($select_query, $params, $_REQUEST['start'], $_REQUEST['length']);
	
$data = array();
foreach ($result as $key => $value){

	if($value->status==2){
		$status=get_string('option_sent_notdelivered','local_notifications');
	}elseif($value->status==1){
		$status=get_string('sent','local_notifications');
	}else{
		$status=get_string('notsent','local_notifications');
	}
	
	$created_date= \local_notifications\lib::get_userdate("d/m/Y H:i", $value->timecreated);
	if($value->sent_date!="" && $value->sent_date!=0 ){
		$send_date= \local_notifications\lib::get_userdate("d/m/Y H:i", $value->sent_date);
	}else{
		$send_date=get_string('n/a','local_notifications');
	}

	$fromlocaluserrecord = $DB->get_record('local_users',['userid'=>$value->from_userid]);
    $value->from_username = ($fromlocaluserrecord)? ((current_language() == 'ar') ? $fromlocaluserrecord->firstnamearabic.' '.$fromlocaluserrecord->middlenamearabic.' '.$fromlocaluserrecord->thirdnamearabic.' '.$fromlocaluserrecord->lastnamearabic  : $fromlocaluserrecord->firstname.' '.$fromlocaluserrecord->middlenameen.' '.$fromlocaluserrecord->thirdnameen.' '.$fromlocaluserrecord->lastname) :fullname($DB->get_record('user',['id'=>$value->from_userid]));

    $tolocaluserrecord = $DB->get_record('local_users',['userid'=>$value->to_userid]);
    $value->to_username = ($tolocaluserrecord)? ((current_language() == 'ar') ? $tolocaluserrecord->firstnamearabic.' '.$tolocaluserrecord->middlenamearabic.' '.$tolocaluserrecord->thirdnamearabic.' '.$tolocaluserrecord->lastnamearabic  : $tolocaluserrecord->firstname.' '.$tolocaluserrecord->middlenameen.' '.$tolocaluserrecord->thirdnameen.' '.$tolocaluserrecord->lastname) :fullname($DB->get_record('user',['id'=>$value->from_userid]));

	$row = array($value->from_username,$value->to_username,$value->notification_type,$created_date,$send_date,$status,'<a href="'.$CFG->wwwroot.'/local/notifications/email_status_details.php?id='.$value->id.'" target="_blank">'.get_string('view','local_notifications').'</a>');
	$data[] = $row;

}

$iTotal = $resultcount;
$outputs = array(
        "draw" => isset($_GET['draw']) ? intval($_GET['draw']) : 0,
        "sEcho" => intval($requestData['sEcho']),
        "iTotalRecords" => $iTotal,
        "iTotalDisplayRecords" => $iTotal,
        "aaData" => $data
    );
echo json_encode($outputs);
          

