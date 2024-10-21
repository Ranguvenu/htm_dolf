<?php
defined('MOODLE_INTERNAL') || die();

$settingspage = new admin_settingpage('userlogapi', new lang_string('userlogapi', 'local_userapproval'));

if ($ADMIN->fulltree) {

    $settingspage->add(new admin_setting_configtext('local_userapproval/userlogapiauthenticateurl', get_string('userapiurl', 'local_userapproval'), get_string('userapiauthenticateurl_desc', 'local_userapproval'), '', PARAM_RAW));

}

$ADMIN->add('localplugins', $settingspage);