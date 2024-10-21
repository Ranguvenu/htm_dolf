<?php

defined('MOODLE_INTERNAL') || die();
global $CFG;
if ($hassiteconfig) {
    $settingslink = new admin_externalpage('block_documentupload', get_string('pluginname', 'block_documentupload'),
        new moodle_url('/blocks/documentupload/index.php'),'moodle/site:config',false,null);
    $ADMIN->add('documentupload', $settingslink);
  
} 