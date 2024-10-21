<?php

defined('MOODLE_INTERNAL') || die();
global $CFG;
if ($hassiteconfig) {

    $settingslink = new admin_externalpage('block_faq', get_string('pluginname', 'block_faq'),
        new moodle_url('/blocks/faq/index.php'),'moodle/site:config',false,null);
    $ADMIN->add('faq', $settingslink);
  
} 