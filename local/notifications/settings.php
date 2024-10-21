<?php
defined('MOODLE_INTERNAL') || die();

$settingspage = new admin_settingpage('notificationssmsapi', new lang_string('notificationssmsapi', 'local_notifications'));

if ($ADMIN->fulltree) {

    $settingspage->add(new admin_setting_configtext('local_notifications/smsapiauthenticateurl', get_string('smsapiauthenticateurl', 'local_notifications'), get_string('smsapiauthenticateurl_desc', 'local_notifications'), '', PARAM_RAW));

    $settingspage->add(new admin_setting_configtext('local_notifications/smsapiauthenticateusername', get_string('smsapiauthenticateusername', 'local_notifications'), get_string('smsapiauthenticateusername_desc', 'local_notifications'), '', PARAM_TEXT));

    $settingspage->add(new admin_setting_configpasswordunmask('local_notifications/smsapiauthenticatepassword', get_string('smsapiauthenticatepassword', 'local_notifications'), get_string('smsapiauthenticatepassword_desc', 'local_notifications'), '', PARAM_TEXT));

    $settingspage->add(new admin_setting_configtext('local_notifications/smsapicccounturl', get_string('smsapicccounturl', 'local_notifications'), get_string('smsapicccounturl_desc', 'local_notifications'), '', PARAM_RAW));

    $settingspage->add(new admin_setting_configcheckbox('local_notifications/smsapienable', get_string('smsapienable', 'local_notifications'), '', 0));

}

$ADMIN->add('localplugins', $settingspage);