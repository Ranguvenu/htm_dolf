<?php
defined('MOODLE_INTERNAL') || die();

$settingspage = new admin_settingpage('hallcities', new lang_string('hallcities', 'local_hall'));

if ($ADMIN->fulltree) {
    $settingspage->add(new admin_setting_configtext('local_hall/city', get_string('city', 'local_hall'), get_string('city', 'local_hall'), '', PARAM_TEXT, 40));

    $settingspage->add(new admin_setting_configtext('local_hall/citycode', get_string('citycode', 'local_hall'), get_string('citycode', 'local_hall'), '', PARAM_TEXT, 40));

    $settingspage->add(new admin_setting_configtext('local_hall/cityregion', get_string('cityregion', 'local_hall'), get_string('cityregion', 'local_hall'), '', PARAM_TEXT, 40));
}

$ADMIN->add('localplugins', $settingspage);