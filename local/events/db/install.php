<?php
defined('MOODLE_INTERNAL') || die();
function xmldb_local_events_install(){
	global $CFG, $USER, $DB, $OUTPUT;
	$dbman = $DB->get_manager(); // loads ddl manager and xmldb classes
 	$time = time();  
    $category = array('name' => 'Events', 'idnumber' => 'events', 'description' => 'This category is related to  events','parent' => '0', 'visible' => 1, 'depth' => 1, 'timemodified' => $time);
    $category = core_course_category::create($category);

    if (!defined('BEHAT_SITE_RUNNING') && !(defined('PHPUNIT_TEST') && PHPUNIT_TEST)) {
        \tool_certificate\certificate::create_events_template();
    }
}
