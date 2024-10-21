<?php
defined('MOODLE_INTERNAL') || die();

$settingspage = new admin_settingpage('notificationsfastapi', new lang_string('notificationsfastapi', 'local_exams'));

if ($ADMIN->fulltree) {

    $settingspage->add(new admin_setting_configtext('local_exams/fastapihosturl', get_string('fastapihosturl', 'local_exams'), get_string('fastapihosturl_desc', 'local_exams'), '', PARAM_RAW));

    $settingspage->add(new admin_setting_configtext('local_exams/fastapiauthenticateurl', get_string('fastapiauthenticateurl', 'local_exams'), get_string('fastapiauthenticateurl_desc', 'local_exams'), '', PARAM_RAW));

    $settingspage->add(new admin_setting_configtext('local_exams/fastapiauthenticateusername', get_string('fastapiauthenticateusername', 'local_exams'), get_string('fastapiauthenticateusername_desc', 'local_exams'), '', PARAM_TEXT));

    $settingspage->add(new admin_setting_configpasswordunmask('local_exams/fastapiauthenticatepassword', get_string('fastapiauthenticatepassword', 'local_exams'), get_string('fastapiauthenticatepassword_desc', 'local_exams'), '', PARAM_TEXT));

    $settingspage->add(new admin_setting_configtext('local_exams/fastapicccounturl', get_string('fastapicccounturl', 'local_exams'), get_string('fastapicccounturl_desc', 'local_exams'), '', PARAM_RAW));

    $settingspage->add(new admin_setting_configcheckbox('local_exams/fastapienable', get_string('fastapienable', 'local_exams'), '', 0));

}

$ADMIN->add('localplugins', $settingspage);