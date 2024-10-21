<?php
defined('MOODLE_INTERNAL') || die();

$settingspage = new admin_settingpage('notifcisi', new lang_string('notifcisi', 'local_lmsws'));

if ($ADMIN->fulltree) {

    $settingspage->add(new admin_setting_configtext('local_lmsws/cisiurl', get_string('cisiurl', 'local_lmsws'), get_string('cisiurl_desc', 'local_lmsws'), '', PARAM_TEXT));

    $settingspage->add(new admin_setting_configtext('local_lmsws/clientid', get_string('clientid', 'local_lmsws'), get_string('clientid_desc', 'local_lmsws'), '', PARAM_TEXT));

    $settingspage->add(new admin_setting_configtext('local_lmsws/clientsecret', get_string('clientsecret', 'local_lmsws'), get_string('clientsecret_desc', 'local_lmsws'), '', PARAM_TEXT));

    $settingspage->add(new admin_setting_configtext('local_lmsws/cisiusername', get_string('cisiusername', 'local_lmsws'), get_string('cisiusername_desc', 'local_lmsws'), '', PARAM_TEXT));

    $settingspage->add(new admin_setting_configpasswordunmask('local_lmsws/cisipassword', get_string('cisipassword', 'local_lmsws'), get_string('cisipassword_desc', 'local_lmsws'), '', PARAM_TEXT));
   
    $settingspage->add(new admin_setting_configtext('local_lmsws/cisiscope', get_string('cisiscope', 'local_lmsws'), get_string('cisiscope_desc', 'local_lmsws'), '', PARAM_TEXT));



    //$settingspage->add(new admin_setting_configcheckbox('local_lmsws/cisienable', get_string('cisienable', 'local_lmsws'), '', 0));

}

$ADMIN->add('localplugins', $settingspage);