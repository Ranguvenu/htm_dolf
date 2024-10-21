<?php
defined('MOODLE_INTERNAL') || die();
function xmldb_local_sector_upgrade($oldversion) {
  global $DB, $CFG;
  if ($oldversion <2021051704.04) {
    $dbman = $DB->get_manager();
    $table = new xmldb_table('local_jobrole_responsibility');
    $field = new xmldb_field('responsibility', XMLDB_TYPE_TEXT,null, null, XMLDB_NOTNULL, null, null, null);
    $dbman->change_field_type($table, $field);
    upgrade_plugin_savepoint(true, 2021051704.04, 'local', 'sector');
  }


 if ($oldversion < 2021051704.06) {
    $time = time();  
    $sectorsdata = array(
        array('title' => 'Banking','code' => 'banking','timemodified' => $time,'usermodified' => '2','titlearabic'=> 'الخدمات المصرفية'),
        array('title' => 'Capital market','code' => 'capitalmarket','timemodified' => $time,'usermodified' => '2','titlearabic'=> 'سوق رأس المال'),
        array('title' => 'Finance','code' => 'finance','timemodified' => $time,'usermodified' => '2','titlearabic'=> 'تمويل'),
        array('title' => 'Insurance','code' => 'insurance','timemodified' => $time,'usermodified' => '2','titlearabic'=> 'تأمين'),
    );
    foreach($sectorsdata as $sector){
        if(!$DB->record_exists('local_sector',  $sector)){
            $DB->insert_record('local_sector', $sector);
        }
    }

    upgrade_plugin_savepoint(true, 2021051704.06, 'local', 'sector');
  }
 if ($oldversion < 2021051705.06) {
    $dbman = $DB->get_manager();

    $table = new xmldb_table('local_sector');
    $field = new xmldb_field('old_id',XMLDB_TYPE_INTEGER, '10', null, XMLDB_NOTNULL, null, '0');
    if (!$dbman->field_exists($table, $field)) {
        $dbman->add_field($table, $field);
    }

    $table = new xmldb_table('local_segment');
    $field = new xmldb_field('old_id',XMLDB_TYPE_INTEGER, '10', null, XMLDB_NOTNULL, null, '0');
    if (!$dbman->field_exists($table, $field)) {
        $dbman->add_field($table, $field);
    }

    $table = new xmldb_table('local_jobfamily');
    $field = new xmldb_field('old_id',XMLDB_TYPE_INTEGER, '10', null, XMLDB_NOTNULL, null, '0');
    if (!$dbman->field_exists($table, $field)) {
        $dbman->add_field($table, $field);
    }

    $table = new xmldb_table('local_jobrole_level');
    $field = new xmldb_field('old_id',XMLDB_TYPE_INTEGER, '10', null, XMLDB_NOTNULL, null, '0');
    if (!$dbman->field_exists($table, $field)) {
        $dbman->add_field($table, $field);
    }

    $table = new xmldb_table('local_jobfamily');
    $field = new xmldb_field('shared',XMLDB_TYPE_INTEGER, '10', null, XMLDB_NOTNULL, null, '0');
    if (!$dbman->field_exists($table, $field)) {
        $dbman->add_field($table, $field);
    }

    $table = new xmldb_table('local_jobrole_level');
    $field = new xmldb_field('shared',XMLDB_TYPE_INTEGER, '10', null, XMLDB_NOTNULL, null, '0');
    if (!$dbman->field_exists($table, $field)) {
        $dbman->add_field($table, $field);
    }

    upgrade_plugin_savepoint(true, 2021051705.06, 'local', 'sector');
 } 

  if ($oldversion < 2021051705.08) {
    
        $dbman = $DB->get_manager();
        $table = new xmldb_table('local_jobrole_level');
        $ctypesfield = new xmldb_field('ctypes',XMLDB_TYPE_TEXT, 'medium', null, null, null, null,'level');
        $competenciesfield = new xmldb_field('competencies',XMLDB_TYPE_CHAR, '255', null, null, null, null,'ctypes');
        if (!$dbman->field_exists($table, $ctypesfield)) {
            $dbman->add_field($table, $ctypesfield);
        }  
        if (!$dbman->field_exists($table, $competenciesfield)) {
            $dbman->add_field($table, $competenciesfield);
        }
        upgrade_plugin_savepoint(true, 2021051705.08, 'local', 'sector'); 
    } 

    if ($oldversion < 2021051705.2) {
        $dbman = $DB->get_manager();
        $table = new xmldb_table('local_sector');
        $field = new xmldb_field('old_id', XMLDB_TYPE_CHAR, '255', null, XMLDB_NOTNULL, null, '0');
        $dbman->change_field_type($table, $field);


        $table = new xmldb_table('local_segment');
        $field = new xmldb_field('old_id', XMLDB_TYPE_CHAR, '255', null, XMLDB_NOTNULL, null, '0');
        $dbman->change_field_type($table, $field);


        $table = new xmldb_table('local_jobfamily');
        $field = new xmldb_field('old_id', XMLDB_TYPE_CHAR, '255', null, XMLDB_NOTNULL, null, '0');
        $dbman->change_field_type($table, $field);

        $table = new xmldb_table('local_jobrole_level');
        $field = new xmldb_field('old_id', XMLDB_TYPE_CHAR, '255', null, XMLDB_NOTNULL, null, '0');
        $dbman->change_field_type($table, $field);

        // Main savepoint reached.
        upgrade_plugin_savepoint(true, 2021051705.2, 'local', 'sector');

    }
    if ($oldversion < 2021051705.3) {
        $dbman = $DB->get_manager();
        $table = new xmldb_table('local_jobfamily');
        $field = new xmldb_field('segmentid', XMLDB_TYPE_CHAR, '255', null, XMLDB_NOTNULL, null, '0');
        $dbman->change_field_type($table, $field);

        upgrade_plugin_savepoint(true, 2021051705.3, 'local', 'sector');
    }

    if ($oldversion < 2021051706.1) {
        $dbman = $DB->get_manager();
        $table1 = new xmldb_table('local_segment');
        $table2 = new xmldb_table('local_jobfamily');
        $field1 = new xmldb_field('description', XMLDB_TYPE_TEXT, 'medium', null, null, null, null,'sectorid');
        $field2 = new xmldb_field('careerpath', XMLDB_TYPE_INTEGER, '10', null, XMLDB_NOTNULL, null, '0','description');
        if (!$dbman->field_exists($table1, $field1)) {
            $dbman->add_field($table1, $field1);
        }
        if (!$dbman->field_exists($table2, $field2)) {
            $dbman->add_field($table2, $field2);
        }
        upgrade_plugin_savepoint(true, 2021051706.1, 'local', 'sector');
    }

    if ($oldversion < 2021051706.2) {
        $dbman = $DB->get_manager();
        $table = new xmldb_table('local_jobfamily');
        $field = new xmldb_field('segmentid', XMLDB_TYPE_CHAR, '255', null, XMLDB_NOTNULL, null, '0');

        if (!$dbman->field_exists($table, $field)) {
            $dbman->add_field($table, $field);
        }
        upgrade_plugin_savepoint(true, 2021051706.2, 'local', 'sector');
    }

    if ($oldversion < 2021051706.4) {
        $dbman = $DB->get_manager();
        $table = new xmldb_table('local_jobfamily');
        $field = new xmldb_field('careerpath_ar', XMLDB_TYPE_INTEGER, '10', null, XMLDB_NOTNULL, null, '0');

        if (!$dbman->field_exists($table, $field)) {
            $dbman->add_field($table, $field);
        }
        upgrade_plugin_savepoint(true, 2021051706.4, 'local', 'sector');
    }        

  return true;
}
