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


if (!defined('AJAX_SCRIPT')) {
    define('AJAX_SCRIPT', true);
}

require(__DIR__.'/../../config.php');
require_once('lib.php');
global $CFG,$DB,$USER, $PAGE;
$notificationid = required_param('notificationid', PARAM_INT);
$page = required_param('page', PARAM_INT);

$sitecontext=context_system::instance();

$PAGE->set_context($sitecontext);
require_login();

if(!has_capability('local/notifications:manage', $sitecontext) && !has_capability('local/organization:manage_communication_officer',$sitecontext)){

    throw new required_capability_exception($sitecontext, 'local/notifications:manage', 'nopermissions', '');
}

$notif_type = $DB->get_record('local_notification_type', array('id'=>$notificationid),'shortname,plugintype,pluginname');
switch($page){
	case 1:	

		$classlib = ''.$notif_type->plugintype.'_' .$notif_type->pluginname.'\notification';

		$lib = new $classlib();

		$strings = $lib->get_string_identifiers($notif_type->shortname);

		echo json_encode(['datamodule_label'=>$notif_type->pluginname,'datamoduleids' => array(),'datastrings'=>$strings, 'completiondays' =>  array()]);	
	break;
}
