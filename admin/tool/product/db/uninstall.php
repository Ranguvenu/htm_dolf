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
 * @package   tool
 * @subpackage  product
 * @author eabyas  <info@eabyas.in>
**/
defined('MOODLE_INTERNAL') || die();
function xmldb_tool_product_uninstall(){
	global $DB,$USER;
	$parentid = $DB->get_field('local_notification_type', 'id', array('shortname' => 'product'));
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