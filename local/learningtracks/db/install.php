<?php
defined('MOODLE_INTERNAL') || die();
function xmldb_local_learningtracks_install(){
	global $CFG, $USER, $DB, $OUTPUT;
	$dbman = $DB->get_manager(); // loads ddl manager and xmldb classes
 	$time = time();  
    $category = array('name' => 'Learning Tracks', 'idnumber' => 'learningtracks', 'description' => 'This category is related to  learningtracks','parent' => '0', 'visible' => 1, 'depth' => 1, 'timemodified' => $time);
    $category = core_course_category::create($category);

    if (!defined('BEHAT_SITE_RUNNING') && !(defined('PHPUNIT_TEST') && PHPUNIT_TEST)) {
        \tool_certificate\certificate::create_learningtracks_template();
    }
}
