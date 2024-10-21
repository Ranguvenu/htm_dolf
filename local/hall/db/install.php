<?php
defined('MOODLE_INTERNAL') || die();
function xmldb_local_hall_install(){
	global $CFG, $USER, $DB, $OUTPUT;
	$dbman = $DB->get_manager(); // loads ddl manager and xmldb classes
	
	$cities = (new local_hall\hall)->hall_cities();
	set_config('hallcities', serialize($cities), 'local_hall');
}
