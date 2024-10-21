<?php

error_reporting(E_ALL);
ini_set("display_errors", 1);

require_once('../../config.php');

global $DB, $USER, $CFG;

$sitecontext=context_system::instance();

require_login();

if(!has_capability('local/notifications:manage', $sitecontext) && !has_capability('local/organization:manage_communication_officer',$sitecontext)){

    throw new required_capability_exception($sitecontext, 'local/notifications:manage', 'nopermissions', '');
}

$phonenumber = optional_param('phonenumber','966543072283', PARAM_INT);
$text = optional_param('text','لكاديمية', PARAM_RAW);

use local_notifications\notification as notification;

$tokeninfo=notification::get_integrations_sms_token();

print_r($tokeninfo);


if(!empty($tokeninfo->token)){

	$smsapi=notification::lms_integrations_sms($tokeninfo->token,$phonenumber,$text);

	print_r($smsapi);

}

?>

