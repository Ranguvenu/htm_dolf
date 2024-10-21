<?php
// This file is part of Moodle - http://moodle.org/
//
// Moodle is free software: you can redistribute it and/or modify
// it under the terms of the GNU General Public License as published by
// the Free Software Foundation, either version 3 of the License, or
// (at your option) any later version.
//
// Moodle is distributed in the hope that it will be useful,
// but WITHOUT ANY WARRANTY; without even the implied warranty of
// MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
// GNU General Public License for more details.
//
// You should have received a copy of the GNU General Public License
// along with Moodle.  If not, see <http://www.gnu.org/licenses/>.
defined('MOODLE_INTERNAL') || die();

/**
 * TODO describe file upgrade
 *
 * @package    local_trainingprogram
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
function xmldb_local_trainingprogram_upgrade($oldversion) {
    global $DB, $CFG;
    $dbman = $DB->get_manager();
    
    if($oldversion < 2022041203.05){

        $trainingprogramtable = new xmldb_table('local_trainingprogram');
        $trainingprogramtable->add_field('id', XMLDB_TYPE_INTEGER, '10', null, XMLDB_NOTNULL, XMLDB_SEQUENCE, null);
        $trainingprogramtable->add_field('name', XMLDB_TYPE_CHAR, '255',  null, null, null, null, null, null);
        $trainingprogramtable->add_field('courseid', XMLDB_TYPE_INTEGER, '10', null, XMLDB_NOTNULL, null, '0');
        $trainingprogramtable->add_field('image', XMLDB_TYPE_INTEGER, '10', null, XMLDB_NOTNULL, null, '0');
        $trainingprogramtable->add_field('code', XMLDB_TYPE_INTEGER, '10', null, XMLDB_NOTNULL, null, '0');
        $trainingprogramtable->add_field('price', XMLDB_TYPE_INTEGER, '10', null, XMLDB_NOTNULL, null, '0');
        $trainingprogramtable->add_field('sellingprice', XMLDB_TYPE_INTEGER, '10', null, XMLDB_NOTNULL, null, '0');
        $trainingprogramtable->add_field('actualprice', XMLDB_TYPE_INTEGER, '10', null, XMLDB_NOTNULL, null, '0');
        $trainingprogramtable->add_field('description', XMLDB_TYPE_TEXT, 'medium', null, null, null, null, null, null);
        $trainingprogramtable->add_field('languages', XMLDB_TYPE_CHAR, '11', null, null, null, null);
        $trainingprogramtable->add_field('methods', XMLDB_TYPE_CHAR, '55', null, null, null, null);
        $trainingprogramtable->add_field('evaluationmethods', XMLDB_TYPE_CHAR, '55', null, null, null, null);
        $trainingprogramtable->add_field('duration', XMLDB_TYPE_CHAR, '55', null, null, null, null);
        $trainingprogramtable->add_field('availablefrom', XMLDB_TYPE_INTEGER, '12', null, XMLDB_NOTNULL, null, '0');
        $trainingprogramtable->add_field('availableto', XMLDB_TYPE_INTEGER, '12', null, XMLDB_NOTNULL, null, '0');
        $trainingprogramtable->add_field('hour', XMLDB_TYPE_INTEGER, '10', null, null, null, null);
        $trainingprogramtable->add_field('sectors', XMLDB_TYPE_CHAR, '255',  null, null, null, null, null, null);
        $trainingprogramtable->add_field('trainingmethods', XMLDB_TYPE_CHAR, '255',  null, null, null, null, null, null);
        $trainingprogramtable->add_field('competencyandlevels', XMLDB_TYPE_CHAR, '255',  null, null, null, null, null, null);
        $trainingprogramtable->add_field('targetgroup', XMLDB_TYPE_CHAR, '255',  null, null, null, null, null, null);
        $trainingprogramtable->add_field('published', XMLDB_TYPE_INTEGER, '10', null, XMLDB_NOTNULL, null, '0');
        $trainingprogramtable->add_field('deleted', XMLDB_TYPE_INTEGER, '10', null, XMLDB_NOTNULL, null, '0');
        $trainingprogramtable->add_field('usercreated', XMLDB_TYPE_INTEGER, '10', null, XMLDB_NOTNULL, null, '0');
        $trainingprogramtable->add_field('timecreated', XMLDB_TYPE_INTEGER, '10', null, XMLDB_NOTNULL, null, '0');
        $trainingprogramtable->add_field('timemodified', XMLDB_TYPE_INTEGER, '10', null, XMLDB_NOTNULL, null, '0');
        $trainingprogramtable->add_key('primary', XMLDB_KEY_PRIMARY, array('id'));
   

        $logtable = new xmldb_table('tp_offerings');
        $logtable->add_field('id', XMLDB_TYPE_INTEGER, '10', null, XMLDB_NOTNULL, XMLDB_SEQUENCE, null);
        $logtable->add_field('trainingid', XMLDB_TYPE_CHAR, '255', null, null, null, null);
        $logtable->add_field('startdate', XMLDB_TYPE_INTEGER, '10', null, XMLDB_NOTNULL, null, '0');
        $logtable->add_field('enddate', XMLDB_TYPE_INTEGER, '10', null, XMLDB_NOTNULL, null, '0');
        $logtable->add_field('time', XMLDB_TYPE_INTEGER, '10', null, XMLDB_NOTNULL, null, '0');
        $logtable->add_field('sections',XMLDB_TYPE_INTEGER, '12', null, XMLDB_NOTNULL, null, '0');
        $logtable->add_field('duration',  XMLDB_TYPE_INTEGER, '10', null, XMLDB_NOTNULL, null, '0');
        $logtable->add_field('type', XMLDB_TYPE_CHAR, '255', null, null, null, null);
        $logtable->add_field('offeringpricing',XMLDB_TYPE_INTEGER, '12', null, XMLDB_NOTNULL, null, '0');
        $logtable->add_field('availableseats', XMLDB_TYPE_INTEGER, '10', null, XMLDB_NOTNULL, null, '0');
        $logtable->add_field('organization', XMLDB_TYPE_INTEGER, '10', null, XMLDB_NOTNULL, null, '0');
        $logtable->add_field('sellingprice', XMLDB_TYPE_INTEGER, '10', null, XMLDB_NOTNULL, null, '0');
        $logtable->add_field('actualprice', XMLDB_TYPE_INTEGER, '10', null, XMLDB_NOTNULL, null, '0');
        $logtable->add_field('halladdress', XMLDB_TYPE_INTEGER, '10', null, XMLDB_NOTNULL, null, '0');
        $logtable->add_field('timecreated', XMLDB_TYPE_INTEGER, '10', null, XMLDB_NOTNULL, null, '0');
        $logtable->add_field('timemodified', XMLDB_TYPE_INTEGER, '10', null, XMLDB_NOTNULL, null, '0');
        $logtable->add_field('usercreated', XMLDB_TYPE_INTEGER, '10', null, XMLDB_NOTNULL, null, '0');
        $logtable->add_field('usermodified', XMLDB_TYPE_INTEGER, '10', null, XMLDB_NOTNULL, null, '0');
        $logtable->add_field('officials', XMLDB_TYPE_CHAR, '255',  null, null, null, null, null);
        $logtable->add_key('primary', XMLDB_KEY_PRIMARY, array('id'));

        $program_enrollmentstable = new xmldb_table('program_enrollments');
        $program_enrollmentstable->add_field('id', XMLDB_TYPE_INTEGER, '10', null, XMLDB_NOTNULL, XMLDB_SEQUENCE, null);
        $program_enrollmentstable->add_field('programid', XMLDB_TYPE_INTEGER, '10', null, XMLDB_NOTNULL, null, '0');
        $program_enrollmentstable->add_field('offeringid', XMLDB_TYPE_INTEGER, '10', null, XMLDB_NOTNULL, null, '0');
        $program_enrollmentstable->add_field('courseid', XMLDB_TYPE_INTEGER, '10', null, XMLDB_NOTNULL, null, '0');
        $program_enrollmentstable->add_field('userid', XMLDB_TYPE_INTEGER, '10', null, XMLDB_NOTNULL, null, '0');
        $program_enrollmentstable->add_field('roleid', XMLDB_TYPE_INTEGER, '10', null, XMLDB_NOTNULL, null, '0');
        $program_enrollmentstable->add_field('productid', XMLDB_TYPE_INTEGER, '10', null, XMLDB_NOTNULL, null, '0');
        $program_enrollmentstable->add_field('enrolstatus', XMLDB_TYPE_INTEGER, '10', null, XMLDB_NOTNULL, null, '1');
        $program_enrollmentstable->add_field('orderid', XMLDB_TYPE_INTEGER, '10', null, XMLDB_NOTNULL, null, '0');
        $program_enrollmentstable->add_field('enrolledby', XMLDB_TYPE_INTEGER, '10', null, XMLDB_NOTNULL, null, '0');
        $program_enrollmentstable->add_field('organization', XMLDB_TYPE_INTEGER, '10', null, XMLDB_NOTNULL, null, '0');
        $program_enrollmentstable->add_field('orgofficial', XMLDB_TYPE_INTEGER, '10', null, XMLDB_NOTNULL, null, '0');
        $program_enrollmentstable->add_field('timecreated', XMLDB_TYPE_INTEGER, '10', null, XMLDB_NOTNULL, null, '0');
        $program_enrollmentstable->add_field('usercreated', XMLDB_TYPE_INTEGER, '10', null, XMLDB_NOTNULL, null, '0');
        $program_enrollmentstable->add_field('trainertype', XMLDB_TYPE_INTEGER, '10', null, XMLDB_NOTNULL, null, '0');
        $program_enrollmentstable->add_field('enrolltype',XMLDB_TYPE_INTEGER, '12', null, XMLDB_NOTNULL, null, '0');
        $program_enrollmentstable->add_key('primary', XMLDB_KEY_PRIMARY, array('id'));

        // Conditionally launch create table for tool_dataprivacy_contextlist.

        if (!$dbman->table_exists($trainingprogramtable)) {
            $dbman->create_table($trainingprogramtable);
        }

        if (!$dbman->table_exists($logtable)) {
            $dbman->create_table($logtable);
        }

        if (!$dbman->table_exists($program_enrollmentstable)) {
            $dbman->create_table($program_enrollmentstable);
        }

        upgrade_plugin_savepoint(true, 2022041203.05, 'local', 'trainingprogram');
    }
    if ($oldversion < 2022041203.07) {
    
        $dbman = $DB->get_manager();
        $table = new xmldb_table('tp_offerings');
        $field = new xmldb_field('sections',XMLDB_TYPE_INTEGER, '12', null, XMLDB_NOTNULL, null, '0');
         if (!$dbman->field_exists($table, $field)) {
            $dbman->add_field($table, $field);
        }
        upgrade_plugin_savepoint(true, 2022041203.07, 'local', 'trainingprogram');
    }

    if ($oldversion < 2022041203.08) {
    
        $dbman = $DB->get_manager();
        $table = new xmldb_table('tp_offerings');
        $field = new xmldb_field('duration',XMLDB_TYPE_INTEGER, '10', null, null, null);
         if (!$dbman->field_exists($table, $field)) {
            $dbman->add_field($table, $field);
        }

        $fieldstartdate= new xmldb_field('startdatetime', XMLDB_TYPE_INTEGER, '10', null, null, null, null);
        $fieldenddate= new xmldb_field('enddatetime', XMLDB_TYPE_INTEGER, '10', null, null, null, null);
        $dbman->rename_field($table, $fieldstartdate, 'startdate');
        $dbman->rename_field($table, $fieldenddate, 'enddate');

        
        upgrade_plugin_savepoint(true, 2022041203.08, 'local', 'trainingprogram');
    }
    if ($oldversion < 2022041203.20) {
    
        $dbman = $DB->get_manager();
        $table = new xmldb_table('local_trainingprogram');
        
        $field = new xmldb_field('preexam',XMLDB_TYPE_INTEGER, '10', null, null, null,0);
         if (!$dbman->field_exists($table, $field)) {
            $dbman->add_field($table, $field);
        }

        $field = new xmldb_field('postexam',XMLDB_TYPE_INTEGER, '10', null, null, null,0);
        if (!$dbman->field_exists($table, $field)) {
            $dbman->add_field($table, $field);
        }

        $table = new xmldb_table('program_completions');
        $table->add_field('id', XMLDB_TYPE_INTEGER, '10', null, XMLDB_NOTNULL, XMLDB_SEQUENCE, null);
        $table->add_field('programid', XMLDB_TYPE_INTEGER, '10', null, XMLDB_NOTNULL, null, '0');
        $table->add_field('offeringid', XMLDB_TYPE_INTEGER, '10', null, XMLDB_NOTNULL, null, '0');
        $table->add_field('userid', XMLDB_TYPE_INTEGER, '10', null, XMLDB_NOTNULL, null, '0');
        $table->add_field('completion_status', XMLDB_TYPE_INTEGER, '2', null, null, null, '0');
        $table->add_field('preexam_completion_status', XMLDB_TYPE_INTEGER, '10', null, null, null, '0');
        $table->add_field('postexam_completion_status', XMLDB_TYPE_INTEGER, '10', null, null, null, '0');
        $table->add_field('completiondate', XMLDB_TYPE_INTEGER, '10', null, null, null, '0');
        $table->add_field('timecreated', XMLDB_TYPE_INTEGER, '10', null, XMLDB_NOTNULL, null, '0');
        $table->add_field('usercreated', XMLDB_TYPE_INTEGER, '10', null, XMLDB_NOTNULL, null, '0');
        $table->add_field('timemodified', XMLDB_TYPE_INTEGER, '10', null, null, null, '0');
        $table->add_field('usermodified', XMLDB_TYPE_INTEGER, '10', null, null, null, '0');
        $table->add_key('primary', XMLDB_KEY_PRIMARY, array('id'));

        if (!$dbman->table_exists($table)) {
            $dbman->create_table($table);
        }

        
        upgrade_plugin_savepoint(true, 2022041203.20, 'local', 'trainingprogram');
    }

    if ($oldversion < 2022041203.26) {
    
        $dbman = $DB->get_manager();
        $table = new xmldb_table('program_enrollments');
        
        $fielda = new xmldb_field('offeringid',XMLDB_TYPE_INTEGER, '10', null, XMLDB_NOTNULL, null,0,'programid');
         if (!$dbman->field_exists($table, $fielda)) {
            $dbman->add_field($table, $fielda);
        }

        $fieldb = new xmldb_field('roleid',XMLDB_TYPE_INTEGER, '10', null, XMLDB_NOTNULL, null,0,'userid');
        if (!$dbman->field_exists($table, $fieldb)) {
            $dbman->add_field($table, $fieldb);
        }

        $table = new xmldb_table('offering_sessions');
        $table->add_field('id', XMLDB_TYPE_INTEGER, '10', null, XMLDB_NOTNULL, XMLDB_SEQUENCE, null);
        $table->add_field('sessionid', XMLDB_TYPE_INTEGER, '10', null, XMLDB_NOTNULL, null, '0');
        $table->add_field('offeringid', XMLDB_TYPE_INTEGER, '10', null, XMLDB_NOTNULL, null, '0');
        $table->add_field('programid', XMLDB_TYPE_INTEGER, '10', null, XMLDB_NOTNULL, null, '0');
        $table->add_field('courseid', XMLDB_TYPE_INTEGER, '10', null, XMLDB_NOTNULL, null, '0');
        $table->add_field('sessiondate', XMLDB_TYPE_INTEGER, '10', null, null, null, '0');
        $table->add_field('timecreated', XMLDB_TYPE_INTEGER, '10', null, XMLDB_NOTNULL, null, '0');
        $table->add_field('usercreated', XMLDB_TYPE_INTEGER, '10', null, XMLDB_NOTNULL, null, '0');
        $table->add_field('timemodified', XMLDB_TYPE_INTEGER, '10', null, null, null, '0');
        $table->add_field('usermodified', XMLDB_TYPE_INTEGER, '10', null, null, null, '0');
        $table->add_key('primary', XMLDB_KEY_PRIMARY, array('id'));

        if (!$dbman->table_exists($table)) {
            $dbman->create_table($table);
        }
        upgrade_plugin_savepoint(true, 2022041203.26, 'local', 'attendance_sessions');
    }
    if ($oldversion < 2022041203.31) {

        $dbman = $DB->get_manager();
        $table = new xmldb_table('local_trainingprogram');
        
        $fielda = new xmldb_field('attendancecmpltn',XMLDB_TYPE_INTEGER, '2', null, XMLDB_NOTNULL, null,0,'deleted');

        $fieldb = new xmldb_field('attendancepercnt',XMLDB_TYPE_CHAR, '255', null, null, null, null,'attendancecmpltn');

        if (!$dbman->field_exists($table, $fielda)) {
            $dbman->add_field($table, $fielda);
        }

        if (!$dbman->field_exists($table, $fieldb)) {
            $dbman->add_field($table, $fieldb);
        }
    } 
   
    
    if($oldversion < 2022041700.20){
        $dbman = $DB->get_manager(); // loads ddl manager and xmldb classes
        $table = new xmldb_table('local_notification_type');
        if (!$dbman->table_exists($table)) {
            $table->add_field('id', XMLDB_TYPE_INTEGER, '10', null, XMLDB_NOTNULL, XMLDB_SEQUENCE, null);
            $table->add_field('name', XMLDB_TYPE_CHAR, '255', null, XMLDB_NOTNULL, null, '0');
            $table->add_field('shortname', XMLDB_TYPE_CHAR, '255', null, XMLDB_NOTNULL, null, '0');
            $table->add_field('parent_module', XMLDB_TYPE_INTEGER, '10', null, XMLDB_NOTNULL, null, '0');
            $table->add_field('usercreated', XMLDB_TYPE_INTEGER, '10', null, XMLDB_NOTNULL, null, '0');
            $table->add_field('timecreated', XMLDB_TYPE_INTEGER, '10', null, null, null, '0');
            $table->add_field('usermodified', XMLDB_TYPE_INTEGER, '10', null, XMLDB_NOTNULL, null, '0');
            $table->add_field('timemodified', XMLDB_TYPE_INTEGER, '10', null, null, null, '0');
            $table->add_field('pluginname', XMLDB_TYPE_CHAR, '255', null, null, null, '0');
            $table->add_field('plugintype', XMLDB_TYPE_CHAR, '255', null, null, null, '0');
            $table->add_key('primary', XMLDB_KEY_PRIMARY, array('id'));
            $result = $dbman->create_table($table);
        }
        $table = new xmldb_table('local_notification_info');
        if (!$dbman->table_exists($table)) {
            $table->add_field('id', XMLDB_TYPE_INTEGER, '10', null, XMLDB_NOTNULL, XMLDB_SEQUENCE, null);
            $table->add_field('notificationid', XMLDB_TYPE_INTEGER, '10', null, XMLDB_NOTNULL, null, null);
            
            $table->add_field('moduletype', XMLDB_TYPE_CHAR, '255', null, XMLDB_NOTNULL, null, '0');
            $table->add_field('moduleid', XMLDB_TYPE_TEXT, null, null, null, null, null);
            // courses
            $table->add_field('reminderdays', XMLDB_TYPE_INTEGER, '10', null, null, null, '0');
            $table->add_field('attach_certificate', XMLDB_TYPE_INTEGER, '10', null, null, null, '0');
            $table->add_field('completiondays', XMLDB_TYPE_INTEGER, '10', null, null, null, '0');
            $table->add_field('enable_cc', XMLDB_TYPE_INTEGER, '1', null, null, null, '0');
            $table->add_field('active', XMLDB_TYPE_INTEGER, '1', null, XMLDB_NOTNULL, null, '1');
            $table->add_field('subject', XMLDB_TYPE_CHAR, '255', null, XMLDB_NOTNULL, null, '0');
            $table->add_field('body', XMLDB_TYPE_TEXT, null, null, XMLDB_NOTNULL, null, '0');
            $table->add_field('adminbody', XMLDB_TYPE_TEXT, null, null, null, null, '0');
            $table->add_field('attachment_filepath', XMLDB_TYPE_CHAR, null, null, null, null, '0');
            $table->add_field('status', XMLDB_TYPE_INTEGER, 10, null, null, null, '0');
            
            $table->add_field('usercreated', XMLDB_TYPE_INTEGER, '10', null, XMLDB_NOTNULL, null, '0');
            $table->add_field('timecreated', XMLDB_TYPE_INTEGER, '10', null, null, null, '0');
            $table->add_field('usermodified', XMLDB_TYPE_INTEGER, '10', null, XMLDB_NOTNULL, null, '0');
            $table->add_field('timemodified', XMLDB_TYPE_INTEGER, '10', null, null, null, '0');
    
            $table->add_key('primary', XMLDB_KEY_PRIMARY, array('id'));
            $result = $dbman->create_table($table);
        }
        $table = new xmldb_table('local_emaillogs');
        if (!$dbman->table_exists($table)) {
            $table->add_field('id', XMLDB_TYPE_INTEGER, '10', null, XMLDB_NOTNULL, XMLDB_SEQUENCE, null);
            $table->add_field('notification_infoid', XMLDB_TYPE_INTEGER, '10', null, XMLDB_NOTNULL, null, null);
            $table->add_field('from_userid', XMLDB_TYPE_INTEGER, '10', null, XMLDB_NOTNULL, null, null);
            $table->add_field('to_userid', XMLDB_TYPE_INTEGER, '10', null, XMLDB_NOTNULL, null, null);
            
            $table->add_field('from_emailid', XMLDB_TYPE_CHAR, '255', null, XMLDB_NOTNULL, null, null);
            $table->add_field('to_emailid', XMLDB_TYPE_CHAR, '255', null, XMLDB_NOTNULL, null, null);
            $table->add_field('moduletype', XMLDB_TYPE_CHAR, '255', null, XMLDB_NOTNULL, null, null);
            $table->add_field('moduleid', XMLDB_TYPE_TEXT, null, null, XMLDB_NOTNULL, null, null);
            $table->add_field('teammemberid', XMLDB_TYPE_INTEGER, '10', null, null, null, '0');
            // courses
            $table->add_field('reminderdays', XMLDB_TYPE_INTEGER, '10', null, XMLDB_NOTNULL, null, '0');
            $table->add_field('enable_cc', XMLDB_TYPE_INTEGER, '1', null, XMLDB_NOTNULL, null, '0');
            $table->add_field('active', XMLDB_TYPE_INTEGER, '1', null, XMLDB_NOTNULL, null, '0');
            $table->add_field('subject', XMLDB_TYPE_CHAR, '255', null, XMLDB_NOTNULL, null, '0');
            $table->add_field('emailbody', XMLDB_TYPE_TEXT, null, null, XMLDB_NOTNULL, null, '0');
            $table->add_field('adminbody', XMLDB_TYPE_TEXT, null, null, null, null, '0');
            $table->add_field('attachment_filepath', XMLDB_TYPE_CHAR, null, null, null, null, '0');
            $table->add_field('status', XMLDB_TYPE_INTEGER, 10, null, null, null, '0');
            $table->add_field('usercreated', XMLDB_TYPE_INTEGER, '10', null, XMLDB_NOTNULL, null, '0');
            $table->add_field('timecreated', XMLDB_TYPE_INTEGER, '10', null, null, null, '0');
            $table->add_field('usermodified', XMLDB_TYPE_INTEGER, '10', null, XMLDB_NOTNULL, null, '0');
            $table->add_field('timemodified', XMLDB_TYPE_INTEGER, '10', null, null, null, '0');
    
            $table->add_field('sent_date', XMLDB_TYPE_INTEGER, '10', null, null, null, '0');
            $table->add_field('sent_by', XMLDB_TYPE_INTEGER, '10', null, null, null, '0');

            $table->add_field('sendsms', XMLDB_TYPE_INTEGER, 10, null, null, null, '0');
    
            $table->add_key('primary', XMLDB_KEY_PRIMARY, array('id'));
            $result = $dbman->create_table($table);
        }
        $time = time();
        $initcontent = array('name' => 'Traning Program','shortname' => 'trainingprogram','parent_module' => '0','usercreated' => '2','timecreated' => $time,'usermodified' => 2,'timemodified' => NULL, 'pluginname' => 'trainingprogram');
        $parentid = $DB->get_field('local_notification_type', 'id', array('shortname' => 'trainingprogram'));
        if(!$parentid){
            $parentid = $DB->insert_record('local_notification_type', $initcontent);
        }
        $notification_type_data = array(
            array('name' => 'Create traning program','shortname' => 'trainingprogram_create','parent_module' => $parentid,'usercreated' => '2','timecreated' => $time,'usermodified' => 2,'timemodified' => NULL, 'pluginname' => 'trainingprogram','plugintype' => 'local'),
            array('name' => 'Update traning program','shortname' => 'trainingprogram_update','parent_module' => $parentid,'usercreated' => '2','timecreated' => $time,'usermodified' => 2,'timemodified' => NULL, 'pluginname' => 'trainingprogram','plugintype' => 'local'),
            array('name' => 'Enrollment','shortname' => 'trainingprogram_enroll','parent_module' => $parentid,'usercreated' => '2','timecreated' => $time,'usermodified' => 2,'timemodified' => NULL, 'pluginname' => 'trainingprogram','plugintype' => 'local'),
            array('name' => 'Completion','shortname' => 'trainingprogram_completion','parent_module' => $parentid,'usercreated' => '2','timecreated' => $time,'usermodified' => 2,'timemodified' => NULL, 'pluginname' => 'trainingprogram','plugintype' => 'local'),
            array('name' => 'Certificate Assignment','shortname' => 'trainingprogram_certificate_assignment','parent_module' => $parentid,'usercreated' => '2','timecreated' => $time,'usermodified' => 2,'timemodified' => NULL, 'pluginname' => 'trainingprogram','plugintype' => 'local'),
            array('name' => 'Before 7 days','shortname' => 'trainingprogram_before_7_days','parent_module' => $parentid,'usercreated' => '2','timecreated' => $time,'usermodified' => 2,'timemodified' => NULL, 'pluginname' => 'trainingprogram','plugintype' => 'local'),
            array('name' => 'Before 48 Hours','shortname' => 'trainingprogram_before_48_hours','parent_module' => $parentid,'usercreated' => '2','timecreated' => $time,'usermodified' => 2,'timemodified' => NULL, 'pluginname' => 'trainingprogram','plugintype' => 'local'),
            array('name' => 'Before 24 Hours','shortname' => 'trainingprogram_before_24_hours','parent_module' => $parentid,'usercreated' => '2','timecreated' => $time,'usermodified' => 2,'timemodified' => NULL, 'pluginname' => 'trainingprogram','plugintype' => 'local'),
            array('name' => 'After Session','shortname' => 'trainingprogram_after_session','parent_module' => $parentid,'usercreated' => '2','timecreated' => $time,'usermodified' => 2,'timemodified' => NULL, 'pluginname' => 'trainingprogram','plugintype' => 'local'),
            array('name' => 'Send Conclusion','shortname' => 'trainingprogram_send_conclusion','parent_module' => $parentid,'usercreated' => '2','timecreated' => $time,'usermodified' => 2,'timemodified' => NULL, 'pluginname' => 'trainingprogram','plugintype' => 'local'),
            
        );
        foreach($notification_type_data as $notification_type){
            unset($notification_type['timecreated']);
            if(!$DB->record_exists('local_notification_type',  $notification_type)){
                $notification_type['timecreated'] = $time;
                $DB->insert_record('local_notification_type', $notification_type);
            }
        }
        upgrade_plugin_savepoint(true, 2022041700.20, 'local', 'trainingprogram');
    }
    
    if ($oldversion < 2022041700.22) {

        $dbman = $DB->get_manager();
        $tablea = new xmldb_table('local_trainingprogram');
        $tableb = new xmldb_table('tp_offerings');

        $fielda = new xmldb_field('trainingmethods',XMLDB_TYPE_CHAR, '255', null, null, null, null,null);

        $fieldb = new xmldb_field('trainingmethods',XMLDB_TYPE_CHAR, '255', null, null, null, null,'type');

        if ($dbman->field_exists($tablea, $fielda)) {
            $dbman->drop_field($tablea, $fielda);
        }

        if (!$dbman->field_exists($tableb, $fieldb)) {
            $dbman->add_field($tableb, $fieldb);
        }
    } 

    if ($oldversion < 2022041700.27) {

        $dbman = $DB->get_manager();
        $table = new xmldb_table('tp_offerings');
        $fielda = new xmldb_field('meetingtype',XMLDB_TYPE_INTEGER, '10', null, XMLDB_NOTNULL, null,0,'trainingmethods');
        $fieldb = new xmldb_field('meetingid',XMLDB_TYPE_INTEGER, '10', null, XMLDB_NOTNULL, null,0,'meetingtype');
        $fieldc = new xmldb_field('code',XMLDB_TYPE_CHAR, '50', null, null, null, null,'id');
        $fieldd = new xmldb_field('prequiz',XMLDB_TYPE_INTEGER, '12', null, null, null, null,'trainingmethod');
        $fielde = new xmldb_field('postquiz',XMLDB_TYPE_INTEGER, '12', null, null, null, null,'prequiz');
        $fieldf = new xmldb_field('groupid',XMLDB_TYPE_INTEGER, '12', null, null, null, null,'postquiz');

        if (!$dbman->field_exists($table, $fielda)) {
            $dbman->add_field($table, $fielda);
        }

        if (!$dbman->field_exists($table, $fieldb)) {
            $dbman->add_field($table, $fieldb);
        }

        if (!$dbman->field_exists($table, $fieldc)) {
            $dbman->add_field($table, $fieldc);
        }

        if (!$dbman->field_exists($table, $fieldd)) {
            $dbman->add_field($table, $fieldd);
        }

        if (!$dbman->field_exists($table, $fielde)) {
            $dbman->add_field($table, $fielde);
        }
        if (!$dbman->field_exists($table, $fieldf)) {
            $dbman->add_field($table, $fieldf);
        }

        $renamefield= new xmldb_field('trainingmethods',XMLDB_TYPE_CHAR, '255', null, null, null, null,null);
        if ($dbman->field_exists($table, $renamefield)) {
            $dbman->rename_field($table, $renamefield, 'trainingmethod');
        }
       
    }
    if ($oldversion < 2022041700.32) {

        $table = new xmldb_table('tp_offerings');
        $field = new xmldb_field('halladdress');
        $field->set_attributes(XMLDB_TYPE_CHAR, '255', null, null, null, null);
        try {
            $dbman->change_field_type($table, $field);
        } catch (moodle_exception $e) {

        }

        upgrade_plugin_savepoint(true, 2022041700.32, 'local', 'trainingprogram');

    }

    if ($oldversion < 2022041700.34) {
        $dbman = $DB->get_manager();
        $table = new xmldb_table('coupon_management');
        $table->add_field('id', XMLDB_TYPE_INTEGER, '12', null, XMLDB_NOTNULL, XMLDB_SEQUENCE, null);
        $table->add_field('code', XMLDB_TYPE_CHAR, '55', null, null, null, null);
        $table->add_field('number_of_codes', XMLDB_TYPE_INTEGER, '12', null, XMLDB_NOTNULL, null, '0');
        $table->add_field('coupon_amount', XMLDB_TYPE_INTEGER, '12', null, XMLDB_NOTNULL, null, '0');
        $table->add_field('coupon_created_date', XMLDB_TYPE_INTEGER, '12', XMLDB_NOTNULL, null, null, '0');
        $table->add_field('coupon_expired_date', XMLDB_TYPE_INTEGER, '12', XMLDB_NOTNULL, null, null, '0');
        $table->add_field('coupon_status', XMLDB_TYPE_INTEGER, '2', null, XMLDB_NOTNULL, null, '1');
        $table->add_field('addedtocartfor', XMLDB_TYPE_INTEGER, '12', null, XMLDB_NOTNULL, null, '0');
        $table->add_field('addedtocarton', XMLDB_TYPE_INTEGER, '12', null, XMLDB_NOTNULL, null, '0');
        $table->add_field('coupon_applied_to', XMLDB_TYPE_INTEGER, '12', XMLDB_NOTNULL, null, null, '0');
        $table->add_field('programs',XMLDB_TYPE_CHAR, '255', null, null, null, null);
        $table->add_field('exams',XMLDB_TYPE_CHAR, '255', null, null, null, null);
        $table->add_field('events',XMLDB_TYPE_CHAR, '255', null, null, null, null);
        $table->add_field('usercreated', XMLDB_TYPE_INTEGER, '10', null, XMLDB_NOTNULL, null, '0');
        $table->add_field('timecreated', XMLDB_TYPE_INTEGER, '10', null, XMLDB_NOTNULL, null, '0');
        $table->add_field('timemodified', XMLDB_TYPE_INTEGER, '10', null, XMLDB_NOTNULL, null, '0');
        $table->add_field('usermodified', XMLDB_TYPE_INTEGER, '10', null, XMLDB_NOTNULL, null, '0');
        $table->add_key('primary', XMLDB_KEY_PRIMARY, array('id'));


        $logtable = new xmldb_table('coupon_management_emaillogs');
        $logtable->add_field('id', XMLDB_TYPE_INTEGER, '12', null, XMLDB_NOTNULL, XMLDB_SEQUENCE, null);
        $logtable->add_field('email', XMLDB_TYPE_CHAR, '255', null, null, null, null);
        $logtable->add_field('subject', XMLDB_TYPE_CHAR, '255', null, null, null, null);
        $logtable->add_field('message', XMLDB_TYPE_TEXT,null, null, null, null, null);
        $logtable->add_field('code', XMLDB_TYPE_CHAR, '55', null, null, null, null);
        $logtable->add_field('usercreated', XMLDB_TYPE_INTEGER, '10', null, XMLDB_NOTNULL, null, '0');
        $logtable->add_field('timecreated', XMLDB_TYPE_INTEGER, '10', null, XMLDB_NOTNULL, null, '0');
        $logtable->add_key('primary', XMLDB_KEY_PRIMARY, array('id'));

        if (!$dbman->table_exists($table)) {
            $dbman->create_table($table);
        }

        if (!$dbman->table_exists($logtable)) {
            $dbman->create_table($logtable);
        }
        upgrade_plugin_savepoint(true, 2022041700.34, 'local', 'trainingprogram');

    }
    if ($oldversion < 2022041700.35) {

        $dbman = $DB->get_manager();
        $table = new xmldb_table('program_completions');
        
        $fielda = new xmldb_field('offeringid', XMLDB_TYPE_INTEGER, '10', null, XMLDB_NOTNULL, null, '0');

        if (!$dbman->field_exists($table, $fielda)) {
            $dbman->add_field($table, $fielda);
        }

        upgrade_plugin_savepoint(true, 2022041700.35, 'local', 'trainingprogram');
    }

    if ($oldversion < 2022041700.40) {

        $dbman = $DB->get_manager();
        $tablea = new xmldb_table('coupon_management');

        $tableb = new xmldb_table('local_trainingprogram');

    
        $fieldb = new xmldb_field('program_goals',XMLDB_TYPE_TEXT, null, null, null, null, null);
        
        $fieldc = new xmldb_field('program_agenda',XMLDB_TYPE_TEXT, null, null, null, null, null);

        if (!$dbman->field_exists($tableb, $fieldb)) {
            $dbman->add_field($tableb, $fieldb);
        }
        if (!$dbman->field_exists($tableb, $fieldc)) {
            $dbman->add_field($tableb, $fieldc);
        }

        $program_agendatable = new xmldb_table('program_agenda');
        $program_agendatable->add_field('id', XMLDB_TYPE_INTEGER, '10', null, XMLDB_NOTNULL, XMLDB_SEQUENCE, null);
        $program_agendatable->add_field('programid', XMLDB_TYPE_INTEGER, '10', null, XMLDB_NOTNULL, null, '0');
        $program_agendatable->add_field('day', XMLDB_TYPE_CHAR, '255', null, null, null, null);
        $program_agendatable->add_field('description', XMLDB_TYPE_TEXT,null, null, null, null, null);
        $program_agendatable->add_field('usercreated', XMLDB_TYPE_INTEGER, '10', null, XMLDB_NOTNULL, null, '0');
        $program_agendatable->add_field('timecreated', XMLDB_TYPE_INTEGER, '10', null, XMLDB_NOTNULL, null, '0');
        $program_agendatable->add_field('usermodified', XMLDB_TYPE_INTEGER, '10', null, XMLDB_NOTNULL, null, '0');
        $program_agendatable->add_field('timemodified', XMLDB_TYPE_INTEGER, '10', null, XMLDB_NOTNULL, null, '0');
        $program_agendatable->add_key('primary', XMLDB_KEY_PRIMARY, array('id'));

        if (!$dbman->table_exists($program_agendatable)) {
            $dbman->create_table($program_agendatable);
        }

        upgrade_plugin_savepoint(true, 2022041700.40, 'local', 'trainingprogram');
    }     
    if ($oldversion < 2022041700.41) {
        $dbman = $DB->get_manager();
        $table = new xmldb_table('local_trainingprogram');
        $field = new xmldb_field('clevels', XMLDB_TYPE_CHAR, '255', null, XMLDB_NOTNULL, null, null);
        if (!$dbman->field_exists($table, $field)) {
            $dbman->add_field($table, $field);
        }
        upgrade_plugin_savepoint(true, 2022041700.41, 'local', 'trainingprogram');
    } 

    if ($oldversion < 2022041700.42) {

        $dbman = $DB->get_manager();
        $table = new xmldb_table('earlyregistration_management');
        $table->add_field('id', XMLDB_TYPE_INTEGER, '12', null, XMLDB_NOTNULL, XMLDB_SEQUENCE, null);
        $table->add_field('day', XMLDB_TYPE_INTEGER, '12', null, XMLDB_NOTNULL, null, '0');
        $table->add_field('discount', XMLDB_TYPE_INTEGER, '12', null, XMLDB_NOTNULL, null, '0');
        $table->add_field('earlyregistration_created_date', XMLDB_TYPE_INTEGER, '12', XMLDB_NOTNULL, null, null, '0');
        $table->add_field('earlyregistration_expired_date', XMLDB_TYPE_INTEGER, '12', XMLDB_NOTNULL, null, null, '0');
        $table->add_field('earlyregistration_status', XMLDB_TYPE_INTEGER, '2', null, XMLDB_NOTNULL, null, '1');
        $table->add_field('programs',XMLDB_TYPE_CHAR, '255', null, null, null, null);
        $table->add_field('exams',XMLDB_TYPE_CHAR, '255', null, null, null, null);
        $table->add_field('events',XMLDB_TYPE_CHAR, '255', null, null, null, null);
        $table->add_field('beneficiaries',XMLDB_TYPE_CHAR, '255', null, null, null, null);
        $table->add_field('usercreated', XMLDB_TYPE_INTEGER, '10', null, XMLDB_NOTNULL, null, '0');
        $table->add_field('timecreated', XMLDB_TYPE_INTEGER, '10', null, XMLDB_NOTNULL, null, '0');
        $table->add_field('timemodified', XMLDB_TYPE_INTEGER, '10', null, XMLDB_NOTNULL, null, '0');
        $table->add_field('usermodified', XMLDB_TYPE_INTEGER, '10', null, XMLDB_NOTNULL, null, '0');
        $table->add_key('primary', XMLDB_KEY_PRIMARY, array('id'));


        if (!$dbman->table_exists($table)) {
            $dbman->create_table($table);
        }
        upgrade_plugin_savepoint(true,2022041700.42, 'local', 'trainingprogram');

    }  

    if ($oldversion < 2022041700.44) {
        $dbman = $DB->get_manager();
        $table = new xmldb_table('tp_offerings');
        $fielda = new xmldb_field('prequiz',XMLDB_TYPE_INTEGER, '12', null, null, null, null,'trainingmethod');
        $fieldb = new xmldb_field('postquiz',XMLDB_TYPE_INTEGER, '12', null, null, null, null,'prequiz');
        if (!$dbman->field_exists($table, $fielda)) {
            $dbman->add_field($table, $fielda);
        }
        if (!$dbman->field_exists($table, $fieldb)) {
            $dbman->add_field($table, $fieldb);
        }
        
        upgrade_plugin_savepoint(true, 2022041700.44, 'local', 'trainingprogram');
    }
 if ($oldversion < 2022041700.53) {
        $dbman = $DB->get_manager();
        $time = time();
        $initcontent = array('name' => 'Traning Program','shortname' => 'trainingprogram','parent_module' => '0','usercreated' => '2','timecreated' => $time,'usermodified' => 2,'timemodified' => NULL, 'pluginname' => 'trainingprogram');
        $parentid = $DB->get_field('local_notification_type', 'id', array('shortname' => 'trainingprogram'));
        if(!$parentid){
            $parentid = $DB->insert_record('local_notification_type', $initcontent);
        }
        //$table = new xmldb_table('local_notification_type');
        $notification_type_data =array (
            array('name' => 'Session Before 30 Minutes','shortname' => 'trainingprogram_before_30_minutes','parent_module' => $parentid,'usercreated' => '2','timecreated' => $time,'usermodified' => 2,'timemodified' => NULL, 'pluginname' => 'trainingprogram','plugintype' => 'local'),  
            array('name' => 'Enrolled Inactive Accounts For 2 Days','shortname' => 'trainingprogram_enrolled_inactive_accounts','parent_module' => $parentid,'usercreated' => '2','timecreated' => $time,'usermodified' => 2,'timemodified' => NULL, 'pluginname' => 'trainingprogram','plugintype' => 'local'),  
            array('name' => 'Pre Assessment Open','shortname' => 'trainingprogram_pre_assessment_opened','parent_module' => $parentid,'usercreated' => '2','timecreated' => $time,'usermodified' => 2,'timemodified' => NULL, 'pluginname' => 'trainingprogram','plugintype' => 'local'),  
            array('name' => 'Post Assessment Open','shortname' => 'trainingprogram_post_assessment_opened','parent_module' => $parentid,'usercreated' => '2','timecreated' => $time,'usermodified' => 2,'timemodified' => NULL, 'pluginname' => 'trainingprogram','plugintype' => 'local'),  
            array('name' => 'Pre Assessment Closed','shortname' => 'trainingprogram_pre_assessment_closed','parent_module' => $parentid,'usercreated' => '2','timecreated' => $time,'usermodified' => 2,'timemodified' => NULL, 'pluginname' => 'trainingprogram','plugintype' => 'local'),  
            array('name' => 'Post Assessment Closed','shortname' => 'trainingprogram_post_assessment_closed','parent_module' => $parentid,'usercreated' => '2','timecreated' => $time,'usermodified' => 2,'timemodified' => NULL, 'pluginname' => 'trainingprogram','plugintype' => 'local'),  
            array('name' => 'Assignment Deadline 4 Hours','shortname' => 'trainingprogram_assignment_deadline_4_hours','parent_module' => $parentid,'usercreated' => '2','timecreated' => $time,'usermodified' => 2,'timemodified' => NULL, 'pluginname' => 'trainingprogram','plugintype' => 'local'),  
            array('name' => 'Assignment Deadline 24 Hours','shortname' => 'trainingprogram_assignment_deadline_24_hours','parent_module' => $parentid,'usercreated' => '2','timecreated' => $time,'usermodified' => 2,'timemodified' => NULL, 'pluginname' => 'trainingprogram','plugintype' => 'local'),  
        );
        foreach($notification_type_data as $notification_type){
            unset($notification_type['timecreated']);
            if(!$DB->record_exists('local_notification_type',  $notification_type)){
                $notification_type['timecreated'] = $time;
                $DB->insert_record('local_notification_type', $notification_type);
            }
        }
        upgrade_plugin_savepoint(true, 2022041700.53, 'local', 'trainingprogram');
    } 
    
    
     if ($oldversion < 2022041700.54) {
        $time = time();

        $notification_info_data = array(
            array(
                'subject' => '[program_name] is created',
                'body' => '<p dir="ltr" style="text-align: left;">Dear [program_userfullname],</p><p dir="ltr" style="text-align: left;">[program_name] training program has been created .</p><p dir="ltr" style="text-align: left;">&nbsp;Thanks<br></p>',
                'arabic_subject' =>'تحديث البرنامج التدريبي [program_name]',
                'arabic_body' => '<p dir="ltr" style="text-align: left;">عزيزي [program_userfullname]،&nbsp;</p><p dir="ltr" style="text-align: left;">تم إنشاء \ تحديث البرنامج تدريبي [program_name]</p><p dir="ltr" style="text-align: left;">. شكرًا<br></p>',
                'notification_type_shortname'=>'trainingprogram_create'
            ),    
            array(
                'subject' => '[program_name] is updated',
                'body' => '<p dir="ltr" style="text-align: left;">Dear [program_userfullname],</p><p dir="ltr" style="text-align: left;">&nbsp;[program_name] training program has been Updated.</p><p dir="ltr" style="text-align: left;">&nbsp;Thanks<br></p>',
                'arabic_subject' =>'تحديث البرنامج التدريبي تم إنشاء[program_name]',
                'arabic_body' => '<p dir="ltr" style="text-align: left;">عزيزي [program_userfullname] ،</p><p dir="ltr" style="text-align: left;">&nbsp;تم إنشاء \ تحديث البرنامج تدريبي [program_name],</p><p dir="ltr" style="text-align: left;">&nbsp;. شكرًا<br></p>',
                'notification_type_shortname'=>'trainingprogram_update'
            ),    
            array(
                'subject' => 'Enrolment at [program_name]',
                'body' => '<p dir="ltr" style="text-align: left;">Dear [program_userfullname],&nbsp;</p><p dir="ltr" style="text-align: left;">We would like to inform you that you have been enrolled at [program_name] training program.&nbsp;</p><p dir="ltr" style="text-align: left;">Thanks<br></p>',
                'arabic_subject' =>'التسجيل البرنامج التدريبي [program_name]',
                'arabic_body' => '<p dir="ltr" style="text-align: left;">عزيزي [program_userfullname] ،</p><p dir="ltr" style="text-align: left;">&nbsp;نود إشعاركم بأنه قد تم تسجيلك في البرنامج التدريبي [program_name].</p><p dir="ltr" style="text-align: left;">&nbsp;شكرًا<br></p>',
                'notification_type_shortname'=>'trainingprogram_enroll'
            ),    
            array(
                'subject' => '[program_name] Completion', 
                'body' => '<p dir="ltr" style="text-align: left;">Dear [program_userfullname],</p><p dir="ltr" style="text-align: left;">Congratulation! You have successfully completed the training program named [program_name]&nbsp;</p><p dir="ltr" style="text-align: left;">Thanks<br></p>',
                'arabic_subject' =>'إتمام البرنامج التدريبي [program_name]',
                'arabic_body' => '<p dir="ltr" style="text-align: left;">عزيزي [program_userfullname]<span style="font-size: 0.9375rem;">&nbsp;،&nbsp;</span></p><p dir="ltr" style="text-align: left;"><span style="font-size: 0.9375rem;">تهانينا! لقد أكملت بنجاح البرنامج التدريبي [program_name].</span></p><p dir="ltr" style="text-align: left;"><span style="font-size: 0.9375rem;">&nbsp;شكرًا</span></p>',
                'notification_type_shortname'=>'trainingprogram_completion'
            ),   
            array(
                'subject' => '[program_name] certificate issued',
                'body' => '<p dir="ltr" style="text-align: left;">Dear [program_userfullname],</p><p dir="ltr" style="text-align: left;">&nbsp;The certificate for the training program [program_name] has been issued. You could download and share the certificate through the following link&nbsp;</p><p dir="ltr" style="text-align: left;">[program_certificatelink] .</p><p dir="ltr" style="text-align: left;"><br></p><p dir="ltr" style="text-align: left;">&nbsp;Thanks<br></p>',
                'arabic_subject' =>'تم إصدار شهادة البرنامج التدريبي [program_name]',
                'arabic_body' => '<p dir="ltr" style="text-align: left;">عزيزي&nbsp;[program_userfullname]،</p><p dir="ltr" style="text-align: left;">&nbsp;تم إصدار شهادة البرنامج التدريبي [program_name]. يمكنك تنزيل الشهادة ومشاركتها من خلال الرابط التالي&nbsp;</p><p dir="ltr" style="text-align: left;">[program_certificatelink].</p><p dir="ltr" style="text-align: left;">&nbsp;شكرًا<br></p>',
                'notification_type_shortname'=>'trainingprogram_certificate_assignment'
            ),
            array(
                'subject' => ' Attendance reminder [trainingprogram_related_module_name]',
                'body' => '<p dir="ltr" style="text-align: left;">Dear [trainingprogram_userfullname] , This is a reminder that [trainingprogram_related_module_name] will start after 7 days at [trainingprogram_related_module_date]&nbsp; [trainingprogram_related_module_time]. Thanks<br><br></p>',
                'arabic_subject' =>'تذكير حضور  [trainingprogram_related_module_name]',
                'arabic_body' => '<p dir="ltr" style="text-align: left;">عزيزي [trainingprogram_userfullname] ، هذا تذكير بأن [trainingprogram_related_module_name] سيبدأ بعد 7 أيام في تاريخ: [trainingprogram_related_module_date] [trainingprogram_related_module_time]. شكرًا<br></p>',
                'notification_type_shortname'=>'trainingprogram_before_7_days'
            ), 
            array(
                'subject' => 'Attendance reminder [trainingprogram_related_module_name]',
                'body' => '<p dir="ltr" style="text-align: left;"></p><p dir="ltr"><br>Dear&nbsp;[trainingprogram_userfullname],</p>This is a reminder that [trainingprogram_related_module_name] will start after 48 hours at [trainingprogram_related_module_date] [trainingprogram_related_module_time]. Thanks<br><p></p>',
                'arabic_subject' =>'تذكير حضور  [trainingprogram_related_module_name]',
                'arabic_body' => '<p dir="ltr" style="text-align: left;"></p><p dir="ltr">عزيزي&nbsp;[trainingprogram_userfullname]</p>، هذا تذكير بأن [trainingprogram_related_module_name] سيبدأ بعد 48 ساعة في تاريخ: [trainingprogram_related_module_date] [trainingprogram_related_module_time]. شكرًا<br><p></p>',
                'notification_type_shortname'=>'trainingprogram_before_48_hours'
            ),
            array(
                'subject' => 'Attendance reminder [trainingprogram_related_module_name]',
                'body' => '<p dir="ltr" style="text-align: left;">Dear [trainingprogram_userfullname] , This is a reminder that [trainingprogram_related_module_name] will start after 24 hours at [trainingprogram_related_module_date] [trainingprogram_related_module_time]. Thanks<br></p>',
                'arabic_subject' =>'تذكير حضور   [trainingprogram_related_module_name] ',
                'arabic_body' => '<p dir="ltr" style="text-align: left;">عزيزي [trainingprogram_userfullname] ، هذا تذكير بأن [trainingprogram_related_module_name] سيبدأ بعد 24 ساعة في تاريخ: [trainingprogram_related_module_date] [trainingprogram_related_module_time]. شكرًا<br></p>',
                'notification_type_shortname'=>'trainingprogram_before_24_hours'
            ), 
            array(
                'subject' => 'Thanks for Attending [trainingprogram_related_module_name]',
                'body' => '<p dir="ltr" style="text-align: left;">Dear [trainingprogram_userfullname],</p><p dir="ltr" style="text-align: left;">&nbsp;[trainingprogram_related_module_name] is done, Thank you for attending.<br></p>',
                'arabic_subject' =>'شكرًا لحضورك ',
                'arabic_body' => '<p dir="ltr" style="text-align: left;">عزيزي [trainingprogram_userfullname] ،</p><p dir="ltr" style="text-align: left;">&nbsp;تم الانتهاء من [trainingprogram_related_module_name]، شكرًا لكم على الحضور.<br></p>  ',
                'notification_type_shortname'=>'trainingprogram_after_session'
            ), 
            array(
                'subject' => 'Thanks for Attending [trainingprogram_related_module_name]',
                'body' => '<p dir="ltr" style="text-align: left;">Dear [trainingprogram_userfullname],</p><p dir="ltr" style="text-align: left;">&nbsp;[trainingprogram_related_module_name] is done, Thank you for attending.<br></p>',
                'arabic_subject' =>'شكرًا لحضورك',
                'arabic_body' => '<p dir="ltr" style="text-align: left;">عزيزي [trainingprogram_userfullname] ،</p><p dir="ltr" style="text-align: left;">&nbsp;تم الانتهاء من [trainingprogram_related_module_name]، شكرًا لكم على الحضور.<br></p> ',
                'notification_type_shortname'=>'trainingprogram_send_conclusion'
            ),  
    
        );  
        foreach($notification_info_data as $notification_info){
    
            $notification_typeinfo = $DB->get_record('local_notification_type', array('shortname' =>$notification_info['notification_type_shortname']),'id,pluginname');
            if($notification_typeinfo){
    
                if(!$DB->record_exists('local_notification_info', array('notificationid'=>$notification_typeinfo->id))){
                    $notification_info['moduletype'] = $notification_typeinfo->pluginname;
                    $notification_info['notificationid']=$notification_typeinfo->id;
                    $notification_info['usercreated'] = 2;
                    $notification_info['timecreated'] = $time;
                    $DB->insert_record('local_notification_info', $notification_info);
    
                }
            }
        }
        upgrade_plugin_savepoint(true,2022041700.54, 'local', 'trainingprogram'); 
    }  
     if ($oldversion < 2022041700.7) {
    
        $dbman = $DB->get_manager();
        $table = new xmldb_table('local_trainingprogram');
        $arabicnameforprogram = new xmldb_field('namearabic',XMLDB_TYPE_TEXT,null, null, null, null, null, null);
         if (!$dbman->field_exists($table, $arabicnameforprogram)) {
            $dbman->add_field($table, $arabicnameforprogram);
        }     
         upgrade_plugin_savepoint(true, 2022041700.7, 'local', 'trainingprogram'); 
    } 

    if ($oldversion < 2022041701.3) {
    
        $dbman = $DB->get_manager();
        $table = new xmldb_table('local_trainingprogram');
        $oldidfield = new xmldb_field('oldid',XMLDB_TYPE_INTEGER, '12', null, null, null, null,'id');
         if (!$dbman->field_exists($table, $oldidfield)) {
            $dbman->add_field($table, $oldidfield);
        }  
        upgrade_plugin_savepoint(true, 2022041701.3, 'local', 'trainingprogram'); 
    } 

    if ($oldversion < 2022041701.4) {        
        $dbman = $DB->get_manager();
        $table = new xmldb_table('local_trainingprogram');
        $field = new xmldb_field('oldid', XMLDB_TYPE_CHAR, '255', null, null, null, null);
        if ( $dbman->field_exists($table, $field) ) {
            $dbman->change_field_type($table, $field);
        }

        // Main savepoint reached.
        upgrade_plugin_savepoint(true, 2022041701.4, 'local', 'trainingprogram');
    }

    if ($oldversion < 2022041701.8) {
    
        $dbman = $DB->get_manager();
        $table = new xmldb_table('tp_offerings');
        $endtimefield = new xmldb_field('endtime',XMLDB_TYPE_INTEGER, '10', null, XMLDB_NOTNULL, null, '0','time');
         if (!$dbman->field_exists($table, $endtimefield)) {
            $dbman->add_field($table, $endtimefield);
        }  
        upgrade_plugin_savepoint(true, 2022041701.8, 'local', 'trainingprogram'); 
    } 

    if ($oldversion < 2022041702.2) {
    
        $dbman = $DB->get_manager();
        $table = new xmldb_table('local_trainingprogram');
        $tax_freefield = new xmldb_field('tax_free',XMLDB_TYPE_INTEGER, '10', null, null, null, null,'price');
         if (!$dbman->field_exists($table, $tax_freefield)) {
            $dbman->add_field($table, $tax_freefield);
        }  
        upgrade_plugin_savepoint(true, 2022041702.2, 'local', 'trainingprogram'); 
    }

    if ($oldversion < 2022041702.3) {
    
        $dbman = $DB->get_manager();
        $table = new xmldb_table('tp_offerings');
        $halllocationfield = new xmldb_field('halllocation', XMLDB_TYPE_CHAR, '25', null, null, null,null,'halladdress');
         if (!$dbman->field_exists($table, $halllocationfield)) {
            $dbman->add_field($table, $halllocationfield);
        }  
        upgrade_plugin_savepoint(true, 2022041702.3, 'local', 'trainingprogram'); 
    }

    if ($oldversion < 2022041702.6) {
    
        $dbman = $DB->get_manager();
        $table = new xmldb_table('local_trainingprogram');
        $usermodifiedfield = new xmldb_field('usermodified',XMLDB_TYPE_INTEGER, '12', null, XMLDB_NOTNULL, null, '0','usercreated');
         if (!$dbman->field_exists($table, $usermodifiedfield)) {
            $dbman->add_field($table, $usermodifiedfield);
        }  
        upgrade_plugin_savepoint(true, 2022041702.6, 'local', 'trainingprogram'); 
    }

    if ($oldversion < 2022041702.8) {
    
        $dbman = $DB->get_manager();
        $table = new xmldb_table('tp_offerings');
        $field = new xmldb_field('oldid', XMLDB_TYPE_CHAR, '255', null, null, null, null,'id');
         if (!$dbman->field_exists($table, $field)) {
            $dbman->add_field($table, $field);
        }  
        upgrade_plugin_savepoint(true, 2022041702.8, 'local', 'trainingprogram'); 
    } 

    if ($oldversion < 2022041704) {
    
        $dbman = $DB->get_manager();
        $table = new xmldb_table('program_agenda');
        $oldidfield = new xmldb_field('oldid',XMLDB_TYPE_INTEGER, '12', null, null, null, null,'id');
         if (!$dbman->field_exists($table, $oldidfield)) {
            $dbman->add_field($table, $oldidfield);
        }  
        upgrade_plugin_savepoint(true, 2022041704, 'local', 'trainingprogram'); 
    }

    

    if ($oldversion < 2022041706.3) {

        $dbman = $DB->get_manager();
        $table = new xmldb_table('local_trainingprogram');

        $cancelstatus = new xmldb_field('cancelstatus',XMLDB_TYPE_INTEGER, '10', null, null, null, 0);
        if ($dbman->field_exists($table, $cancelstatus)) {
            $dbman->drop_field($table, $cancelstatus);
        }

        $cancelreason = new xmldb_field('cancelreason',XMLDB_TYPE_TEXT,null, null, null, null, null, null);
        if ($dbman->field_exists($table, $cancelreason)) {
            $dbman->drop_field($table, $cancelreason);
        }

        $cancelleduser = new xmldb_field('cancelleduser',XMLDB_TYPE_INTEGER, '10', null, null, null, null);
        if ($dbman->field_exists($table, $cancelleduser)) {
            $dbman->drop_field($table, $cancelleduser);
        }

        $cancelledtime = new xmldb_field('cancelledtime',XMLDB_TYPE_INTEGER, '10', null, null, null, null);
        if ($dbman->field_exists($table, $cancelledtime)) {
            $dbman->drop_field($table, $cancelledtime);
        }

        $cancelrequestuser = new xmldb_field('cancelrequestuser',XMLDB_TYPE_INTEGER, '10', null, null, null, null);
        if ($dbman->field_exists($table, $cancelrequestuser)) {
            $dbman->drop_field($table, $cancelrequestuser);
        }
        
        $cancelrequesttime = new xmldb_field('cancelrequesttime',XMLDB_TYPE_INTEGER, '10', null, null, null, null);
        if ($dbman->field_exists($table, $cancelrequesttime)) {
            $dbman->drop_field($table, $cancelrequesttime);
        }

        upgrade_plugin_savepoint(true, 2022041706.3, 'local', 'trainingprogram');
    }


    if ($oldversion < 2022041706.5) {
    
        $dbman = $DB->get_manager();
        $table = new xmldb_table('tp_offerings');
        $offeringlocationfield = new xmldb_field('offeringlocation',XMLDB_TYPE_CHAR, '255', null, null, null, null,'trainingmethod');
        $discountfield = new xmldb_field('discount',XMLDB_TYPE_INTEGER, '12', null, XMLDB_NOTNULL, null,0,'offeringlocation');
        $targetaudiencefield = new xmldb_field('targetaudience',XMLDB_TYPE_INTEGER, '12',null, XMLDB_NOTNULL, null,0,'discount');
        $issponsoredfield = new xmldb_field('issponsored',XMLDB_TYPE_INTEGER, '2', null, null, null, null,'targetaudience');
        $providertypefield = new xmldb_field('trainingprovidertype',XMLDB_TYPE_INTEGER, '12',null, XMLDB_NOTNULL, null,0,'issponsored');
        $quotationfilefield = new xmldb_field('quotationfile',XMLDB_TYPE_INTEGER,'10',null, XMLDB_NOTNULL, null,0,'trainingprovidertype');

        $quotationfeefield = new xmldb_field('quotationfee',XMLDB_TYPE_INTEGER,'12',null, XMLDB_NOTNULL, null,0,'quotationfile');

        $trainingagreementfilefield = new xmldb_field('trainingagreementfile',XMLDB_TYPE_INTEGER,'10',null, XMLDB_NOTNULL, null,0,'quotationfee');

        $trainingagreementfeefield = new xmldb_field('trainingagreementfee',XMLDB_TYPE_INTEGER,'12',null, XMLDB_NOTNULL, null,0,'trainingagreementfile');

        $approvepurchsedfilefield = new xmldb_field('approvepurchsedfile',XMLDB_TYPE_INTEGER,'10',null, XMLDB_NOTNULL, null,0,'trainingagreementfee');

        $approvepurchsedfeefield = new xmldb_field('approvepurchsedfee',XMLDB_TYPE_INTEGER,'12',null, XMLDB_NOTNULL, null,0,'approvepurchsedfile');

        if ($dbman->field_exists($table, $offeringlocationfield)) {
            $dbman->drop_field($table, $offeringlocationfield);
        }  
        if ($dbman->field_exists($table, $discountfield)) {
            $dbman->drop_field($table, $discountfield);
        } 
        if ($dbman->field_exists($table, $targetaudiencefield)) {
             $dbman->drop_field($table, $targetaudiencefield);
        }  
        if ($dbman->field_exists($table, $issponsoredfield)) {
            $dbman->drop_field($table, $issponsoredfield);
        }  
        if ($dbman->field_exists($table, $providertypefield)) {
            $dbman->drop_field($table, $providertypefield);
        } 
        if ($dbman->field_exists($table, $quotationfilefield)) {
            $dbman->drop_field($table, $quotationfilefield);
        } 
        if ($dbman->field_exists($table, $quotationfeefield)) {
            $dbman->drop_field($table, $quotationfeefield);
        } 
        if ($dbman->field_exists($table, $trainingagreementfilefield)) {
            $dbman->drop_field($table, $trainingagreementfilefield);
        } 
        if ($dbman->field_exists($table, $trainingagreementfeefield)) {
            $dbman->drop_field($table, $trainingagreementfeefield);
        } 
        if ($dbman->field_exists($table, $approvepurchsedfilefield)) {
            $dbman->drop_field($table, $approvepurchsedfilefield);
        } 
        if ($dbman->field_exists($table, $approvepurchsedfeefield)) {
            $dbman->drop_field($table, $approvepurchsedfeefield);
        } 
        upgrade_plugin_savepoint(true, 2022041706.5, 'local', 'trainingprogram'); 
    }

     
    if ($oldversion <  2022041706.7) {
    
        $dbman = $DB->get_manager();


        $training_topics = new xmldb_table('training_topics');
        $training_topics->add_field('id', XMLDB_TYPE_INTEGER, '10', null, XMLDB_NOTNULL, XMLDB_SEQUENCE, null);
        $training_topics->add_field('name', XMLDB_TYPE_TEXT, null,  null, XMLDB_NOTNULL, null, null, null, null);
        $training_topics->add_field('usercreated', XMLDB_TYPE_INTEGER, '10', null, XMLDB_NOTNULL, null, '0');
        $training_topics->add_field('timecreated', XMLDB_TYPE_INTEGER, '10', null, XMLDB_NOTNULL, null, '0');
        $training_topics->add_field('usermodified', XMLDB_TYPE_INTEGER, '10', null, XMLDB_NOTNULL, null, '0');
        $training_topics->add_field('timemodified', XMLDB_TYPE_INTEGER, '10', null, XMLDB_NOTNULL, null, '0');
        $training_topics->add_key('primary', XMLDB_KEY_PRIMARY, array('id'));

        // Conditionally launch create table for tool_dataprivacy_contextlist.

        if (!$dbman->table_exists($training_topics)) {
            $dbman->create_table($training_topics);
        }

        $table = new xmldb_table('local_trainingprogram');
        $field1 = new xmldb_field('trainingtopics',XMLDB_TYPE_CHAR, '255', null, null, null, null,'attendancepercnt');
        $field2 = new xmldb_field('prerequirementsprograms',XMLDB_TYPE_CHAR, '255', null, null, null, null,'trainingtopics');
        $field3 = new xmldb_field('postrequirementsprograms',XMLDB_TYPE_CHAR, '255', null, null, null, null,'prerequirementsprograms');

        if (!$dbman->field_exists($table, $field1)) {
            $dbman->add_field($table, $field1);
        } 

        if (!$dbman->field_exists($table, $field2)) {
            $dbman->add_field($table, $field2);
        }

         if (!$dbman->field_exists($table, $field3)) {
            $dbman->add_field($table, $field3);
        }

       upgrade_plugin_savepoint(true,  2022041706.7, 'local', 'trainingprogram'); 
    }

    if ($oldversion < 2022041707.4) {
    
        $dbman = $DB->get_manager();
        $table = new xmldb_table('local_trainingprogram');
        $field = new xmldb_field('trainingtype',XMLDB_TYPE_CHAR, '255', null, null, null, null,'code');
         if (!$dbman->field_exists($table, $field)) {
            $dbman->add_field($table, $field);
        }  
        upgrade_plugin_savepoint(true, 2022041707.4, 'local', 'trainingprogram'); 
    }

    if ($oldversion < 2022041707.5) {
    
        $dbman = $DB->get_manager();
        $table = new xmldb_table('program_goals');
        $table->add_field('id', XMLDB_TYPE_INTEGER, '10', null, XMLDB_NOTNULL, XMLDB_SEQUENCE, null);
        $table->add_field('programid', XMLDB_TYPE_INTEGER, '10', null, XMLDB_NOTNULL, null, '0');
        $table->add_field('programgoal', XMLDB_TYPE_TEXT, 'medium', null, null, null, null, null, null);
        $table->add_field('usercreated', XMLDB_TYPE_INTEGER, '10', null, XMLDB_NOTNULL, null, '0');
        $table->add_field('timecreated', XMLDB_TYPE_INTEGER, '10', null, XMLDB_NOTNULL, null, '0');
        $table->add_key('primary', XMLDB_KEY_PRIMARY, array('id'));

        if (!$dbman->table_exists($table)) {
            $dbman->create_table($table);
        }

        upgrade_plugin_savepoint(true, 2022041707.5, 'local', 'trainingprogram');
    }

    if ($oldversion < 2022041707.55) {
        $dbman = $DB->get_manager();
        $table = new xmldb_table('local_trainingprogram');
        $field = new xmldb_field('program_goals',XMLDB_TYPE_TEXT, null, null, null, null, null);
        if ($dbman->field_exists($table, $field)) {
            $dbman->drop_field($table, $field);
        }  
        upgrade_plugin_savepoint(true, 2022041707.55, 'local', 'trainingprogram'); 
    }

    if ($oldversion < 2022041708) {

        $dbman = $DB->get_manager();
        $table = new xmldb_table('refund_settings');
        $table->add_field('id', XMLDB_TYPE_INTEGER, '12', null, XMLDB_NOTNULL, XMLDB_SEQUENCE, null);
        $table->add_field('type', XMLDB_TYPE_CHAR, '255', null, null, null, null);
        $table->add_field('entitytype', XMLDB_TYPE_CHAR, '255', null, null, null, null);
        $table->add_field('dayfrom', XMLDB_TYPE_INTEGER, '12',  null, null, null, null);
        $table->add_field('dayto', XMLDB_TYPE_INTEGER, '12',  null, null, null, null);
        $table->add_field('dedtype', XMLDB_TYPE_INTEGER, '2',  null, null, null, null);
        $table->add_field('dedpercentage', XMLDB_TYPE_INTEGER, '12', null, null, null,null);
        $table->add_field('dedamount', XMLDB_TYPE_FLOAT, null, null, null, null, null);
        $table->add_field('timecreated', XMLDB_TYPE_INTEGER, '12', null, XMLDB_NOTNULL, null, '0');
        $table->add_field('usercreated', XMLDB_TYPE_INTEGER, '12', null, XMLDB_NOTNULL, null, '0');
        $table->add_field('timemodified', XMLDB_TYPE_INTEGER, '12', null, XMLDB_NOTNULL, null, '0');
        $table->add_field('usermodified', XMLDB_TYPE_INTEGER, '12', null, XMLDB_NOTNULL, null, '0');
        $table->add_key('primary', XMLDB_KEY_PRIMARY, array('id'));

        if (!$dbman->table_exists($table)) {
            $dbman->create_table($table);
        }

        upgrade_plugin_savepoint(true, 2022041708, 'local', 'trainingprogram'); 
    }


    if ($oldversion < 2022052011.2) {
    
        $dbman = $DB->get_manager();
        $table = new xmldb_table('refund_settings');
        $field = new xmldb_field('ownedbycisi',XMLDB_TYPE_INTEGER, '10', null, null, null, 0);
         if (!$dbman->field_exists($table, $field)) {
            $dbman->add_field($table, $field);
        }
        $field = new xmldb_field('moreattempts',XMLDB_TYPE_INTEGER, '10', null, null, null, 0);
        if (!$dbman->field_exists($table, $field)) {
           $dbman->add_field($table, $field);
        }  
        upgrade_plugin_savepoint(true, 2022052011.2, 'local', 'trainingprogram'); 
    }

    if ($oldversion < 2022052011.7) {
        $dbman = $DB->get_manager();
        $table = new xmldb_table('local_trainingprogram');
        $table2 = new xmldb_table('tp_offerings');
        $field = new xmldb_field('sellingprice');
        $field2 = new xmldb_field('actualprice');
        $field->set_attributes(XMLDB_TYPE_FLOAT, null, null, null, null, null);
        $field2->set_attributes(XMLDB_TYPE_FLOAT, null, null, null, null, null);
        try {
            $dbman->change_field_type($table, $field);
            $dbman->change_field_type($table, $field2);

            $dbman->change_field_type($table2, $field);
            $dbman->change_field_type($table2, $field2);
        } catch (moodle_exception $e) {

        }
        upgrade_plugin_savepoint(true, 2022052011.7, 'local', 'trainingprogram');

    }
    if ($oldversion < 2022052011.8) {
        $dbman = $DB->get_manager();
        $table = new xmldb_table('program_enrollments');

        $field = new xmldb_field('enrolstatus',XMLDB_TYPE_INTEGER, '10', null, XMLDB_NOTNULL, null, 1);
        if (!$dbman->field_exists($table, $field)) {
            $dbman->add_field($table, $field);
        }
        $field = new xmldb_field('orderid',XMLDB_TYPE_INTEGER, '10', null, XMLDB_NOTNULL, null, 0);
        if (!$dbman->field_exists($table, $field)) {
            $dbman->add_field($table, $field);
        }        
        // Main savepoint reached.
        upgrade_plugin_savepoint(true, 2022052011.8, 'local', 'trainingprogram');
    }

    if ($oldversion < 2022052012.1) {
        $dbman = $DB->get_manager();
        $table = new xmldb_table('program_enrollments');

        $field = new xmldb_field('enrolledby',XMLDB_TYPE_INTEGER, '10', null, null, null, '0');
        if (!$dbman->field_exists($table, $field)) {
            $dbman->add_field($table, $field);
        } 
        // Main savepoint reached.
        upgrade_plugin_savepoint(true, 2022052012.1, 'local', 'trainingprogram');
    }


    if ($oldversion < 2022052012.2) {
        $dbman = $DB->get_manager();
        $table = new xmldb_table('program_goals');

        $fielda = new xmldb_field('usermodified',XMLDB_TYPE_INTEGER, '10', null, null, null, '0');
        $fieldb = new xmldb_field('timemodified',XMLDB_TYPE_INTEGER, '10', null, null, null, '0');
        if (!$dbman->field_exists($table, $fielda)) {
            $dbman->add_field($table, $fielda);
        } 
        if (!$dbman->field_exists($table, $fieldb)) {
            $dbman->add_field($table, $fieldb);
        } 
        // Main savepoint reached.
        upgrade_plugin_savepoint(true, 2022052012.2, 'local', 'trainingprogram');
    }

    if ($oldversion < 2022052012.3) {
        $dbman = $DB->get_manager();
        
        $tableA = new xmldb_table('program_goals');
        $tableB=  new xmldb_table('tp_offerings');
        $fielda = new xmldb_field('usermodified',XMLDB_TYPE_INTEGER, '10', null, null, null, '0');
        $fieldb = new xmldb_field('timemodified',XMLDB_TYPE_INTEGER, '10', null, null, null, '0');
        $fieldc = new xmldb_field('trainingid',XMLDB_TYPE_INTEGER, '12', null, null, null, '0');
        
       if (!$dbman->field_exists($tableA, $fielda)) {
            $dbman->add_field($tableA, $fielda);
        } 
        if (!$dbman->field_exists($tableA, $fieldb)) {
            $dbman->add_field($tableA, $fieldb);
        } 
        if ($dbman->field_exists($tableB, $fieldc)) {
            $dbman->change_field_type($tableB, $fieldc);
        } 
        // Main savepoint reached.
        upgrade_plugin_savepoint(true, 2022052012.3, 'local', 'trainingprogram');
    }
   
     if ($oldversion < 2022052015) {
        $dbman = $DB->get_manager();
        $table = new xmldb_table('tp_offerings');

        $fielda = new xmldb_field('attachmentpdf',XMLDB_TYPE_CHAR, '250', null, null, null, '0');
        $fieldb = new xmldb_field('officialproposal',XMLDB_TYPE_CHAR, '250', null, null, null, '0');
        $fieldc = new xmldb_field('officialpo',XMLDB_TYPE_CHAR, '250', null, null, null, '0');
        $fieldd = new xmldb_field('tagrrement',XMLDB_TYPE_CHAR, '250', null, null, null, '0');


        if (!$dbman->field_exists($table, $fielda)) {
            $dbman->add_field($table, $fielda);
        } 
        if (!$dbman->field_exists($table, $fieldb)) {
            $dbman->add_field($table, $fieldb);
        } 
        if (!$dbman->field_exists($table, $fieldc)) {
            $dbman->add_field($table, $fieldc);
        } 
        if (!$dbman->field_exists($table, $fieldd)) {
            $dbman->add_field($table, $fieldd);
        } 

      
        // Main savepoint reached.
        upgrade_plugin_savepoint(true, 2022052015, 'local', 'trainingprogram');
    }

if ($oldversion < 2022052015.1) {
        $dbman = $DB->get_manager();
        $table = new xmldb_table('tp_offerings');

        $fielda = new xmldb_field('languages',XMLDB_TYPE_CHAR, '11', null, null, null, '0');
        if (!$dbman->field_exists($table, $fielda)) {
            $dbman->add_field($table, $fielda);
        } 
      
        // Main savepoint reached.
        upgrade_plugin_savepoint(true, 2022052015.1, 'local', 'trainingprogram');
    }
 if ($oldversion < 2022052016) {
        $dbman = $DB->get_manager();
        $table = new xmldb_table('tp_offerings');

        $fielda = new xmldb_field('estimatedbudget',XMLDB_TYPE_INTEGER, '20', null, null, null, '0');
        $fieldb = new xmldb_field('proposedcost',XMLDB_TYPE_INTEGER, '20', null, null, null, '0');
        $fieldc = new xmldb_field('finalamount',XMLDB_TYPE_INTEGER, '20', null, null, null, '0');
        $fieldd = new xmldb_field('tagrement',XMLDB_TYPE_INTEGER, '10', null, null, null, '0');
        $fielde = new xmldb_field('trainingcost',XMLDB_TYPE_INTEGER, '20', null, null, null, '0');

        if (!$dbman->field_exists($table, $fielda)) {
            $dbman->add_field($table, $fielda);
        } 
        if (!$dbman->field_exists($table, $fieldb)) {
            $dbman->add_field($table, $fieldb);
        } 
        if (!$dbman->field_exists($table, $fieldc)) {
            $dbman->add_field($table, $fieldc);
        } 
        if (!$dbman->field_exists($table, $fieldd)) {
            $dbman->add_field($table, $fieldd);
        } 

        if (!$dbman->field_exists($table, $fielde)) {
            $dbman->add_field($table, $fielde);
        } 
        // Main savepoint reached.
        upgrade_plugin_savepoint(true, 2022052016, 'local', 'trainingprogram');
    }
    
    if ($oldversion < 2022052017) {
        $dbman = $DB->get_manager();
        $table = new xmldb_table('local_trainingprogram');
       
        $field = new xmldb_field('classification');
        
        $field->set_attributes(XMLDB_TYPE_CHAR, null, null, null, null, null);
      
        try {
            $dbman->add_field($table, $field);
            
        } catch (moodle_exception $e) {

        }
        upgrade_plugin_savepoint(true, 2022052017, 'local', 'trainingprogram');

    }

    if ($oldversion < 2022052018) {
        $dbman = $DB->get_manager();
        $table = new xmldb_table('tp_offerings');

        $fielda = new xmldb_field('classification',XMLDB_TYPE_CHAR, '20', null, null, null, '0');
       
        if (!$dbman->field_exists($table, $fielda)) {
            $dbman->add_field($table, $fielda);
        } 
        // Main savepoint reached.
        upgrade_plugin_savepoint(true, 2022052018, 'local', 'trainingprogram');
    }
   

     if ($oldversion < 2022052019) {
        $dbman = $DB->get_manager();
        $table = new xmldb_table('local_trainingprogram');

        $fielda = new xmldb_field('programnature',XMLDB_TYPE_INTEGER, '20', null, null, null, '0');
        $fieldb = new xmldb_field('termsconditions',XMLDB_TYPE_INTEGER, '20', null, null, null, '0');
        $fieldc = new xmldb_field('termsconditionsarea',XMLDB_TYPE_CHAR, '500', null, null, null, '0');
       
        if (!$dbman->field_exists($table, $fielda)) {
            $dbman->add_field($table, $fielda);
        } 
        if (!$dbman->field_exists($table, $fieldb)) {
            $dbman->add_field($table, $fieldb);
        } 
        if (!$dbman->field_exists($table, $fieldc)) {
            $dbman->add_field($table, $fieldc);
        } 
        
        // Main savepoint reached.
        upgrade_plugin_savepoint(true, 2022052019, 'local', 'trainingprogram');
    }

     if ($oldversion < 2022052019.1) {
        $dbman = $DB->get_manager();
        $tableA = new xmldb_table('local_trainingprogram');
        $tableB = new xmldb_table('tp_offerings');
        $tableC = new xmldb_table('program_enrollments');

        $fieldA = new xmldb_field('cancelled',XMLDB_TYPE_INTEGER, '12', null, XMLDB_NOTNULL, null, '0');
        $fieldB = new xmldb_field('cancelledby',XMLDB_TYPE_INTEGER, '12', null, XMLDB_NOTNULL, null, '0');
        $fieldC = new xmldb_field('cancelledate',XMLDB_TYPE_INTEGER, '12', null, XMLDB_NOTNULL, null, '0');
        $fieldD = new xmldb_field('published',XMLDB_TYPE_INTEGER, '12', null, XMLDB_NOTNULL, null, '1');
        $fieldE = new xmldb_field('organization',XMLDB_TYPE_INTEGER, '10', null, null, null, '0');
        $fieldF = new xmldb_field('productid',XMLDB_TYPE_INTEGER, '10', null, null, null, '0','userid');
   


        if (!$dbman->field_exists($tableA, $fieldA)) {
            $dbman->add_field($tableA, $fieldA);
        } 
        if (!$dbman->field_exists($tableA, $fieldB)) {
            $dbman->add_field($tableA, $fieldB);
        } 

        if (!$dbman->field_exists($tableA, $fieldC)) {
            $dbman->add_field($tableA, $fieldC);
        } 

        if (!$dbman->field_exists($tableB, $fieldA)) {
            $dbman->add_field($tableB, $fieldA);
        } 
        if (!$dbman->field_exists($tableB, $fieldB)) {
            $dbman->add_field($tableB, $fieldB);
        } 

        if (!$dbman->field_exists($tableB, $fieldC)) {
            $dbman->add_field($tableB, $fieldC);
        } 

        if (!$dbman->field_exists($tableB, $fieldD)) {
            $dbman->add_field($tableB, $fieldD);
        } 

        if (!$dbman->field_exists($tableC, $fieldE)) {
            $dbman->add_field($tableC, $fieldE);
        }
        if (!$dbman->field_exists($tableC, $fieldF)) {
            $dbman->add_field($tableC, $fieldF);
        }
        // Main savepoint reached.
        upgrade_plugin_savepoint(true,2022052019.1, 'local', 'trainingprogram');
    }

    if ($oldversion < 2022052019.2) {
        $dbman = $DB->get_manager();
        $table = new xmldb_table('local_trainingprogram');
        $fielda = new xmldb_field('trainertype',XMLDB_TYPE_INTEGER, '20', null, XMLDB_NOTNULL, null, '0');
        if (!$dbman->field_exists($table, $fielda)) {
            $dbman->add_field($table, $fielda);
        } 
      
        // Main savepoint reached.
        upgrade_plugin_savepoint(true, 2022052019.2, 'local', 'trainingprogram');
    }

      
    if ($oldversion < 2022052015) {
        $dbman = $DB->get_manager();
        $table = new xmldb_table('tp_offerings');
        $field = new xmldb_field('trainerorg',XMLDB_TYPE_INTEGER, '20', null, null, null, null);
        if (!$dbman->field_exists($table, $field)) {
            $dbman->add_field($table, $field);
        } 
        upgrade_plugin_savepoint(true, 2022052015, 'local', 'trainingprogram');

    }

if ($oldversion < 2022052015.1) {
        $dbman = $DB->get_manager();
        $table = new xmldb_table('tp_offerings');
        $table2 = new xmldb_table('program_enrollments');
        $fieldA = new xmldb_field('trainertype',XMLDB_TYPE_INTEGER, '20', null, XMLDB_NOTNULL, null, '0');
        $fieldB = new xmldb_field('trainerorg',XMLDB_TYPE_INTEGER, '20', null, XMLDB_NOTNULL, null, '0');
        if (!$dbman->field_exists($table, $fieldA)) {
            $dbman->add_field($table, $fieldA);
        } 
        if (!$dbman->field_exists($table2, $fieldA)) {
            $dbman->add_field($table2, $fieldA);
        }

        if (!$dbman->field_exists($table, $fieldB)) {
            $dbman->add_field($table, $fieldB);
        } 
        upgrade_plugin_savepoint(true, 2022052019.3, 'local', 'trainingprogram');

    }
    if ($oldversion < 2022052019.4) {
        $dbman = $DB->get_manager();
        $tableA = new xmldb_table('local_trainingprogram');
        $tableB = new xmldb_table('tp_offerings');
        $tableC = new xmldb_table('program_enrollments');

        $fieldA = new xmldb_field('cancelled',XMLDB_TYPE_INTEGER, '12', null, XMLDB_NOTNULL, null, '0');
        $fieldB = new xmldb_field('cancelledby',XMLDB_TYPE_INTEGER, '12', null, XMLDB_NOTNULL, null, '0');
        $fieldC = new xmldb_field('cancelledate',XMLDB_TYPE_INTEGER, '12', null, XMLDB_NOTNULL, null, '0');
        $fieldD = new xmldb_field('published',XMLDB_TYPE_INTEGER, '12', null, XMLDB_NOTNULL, null, '1');
        $fieldE = new xmldb_field('organization',XMLDB_TYPE_INTEGER, '10', null, null, null, '0');
        $fieldF = new xmldb_field('productid',XMLDB_TYPE_INTEGER, '10', null, null, null, '0','userid');
   


        if (!$dbman->field_exists($tableA, $fieldA)) {
            $dbman->add_field($tableA, $fieldA);
        } 
        if (!$dbman->field_exists($tableA, $fieldB)) {
            $dbman->add_field($tableA, $fieldB);
        } 

        if (!$dbman->field_exists($tableA, $fieldC)) {
            $dbman->add_field($tableA, $fieldC);
        } 

        if (!$dbman->field_exists($tableB, $fieldA)) {
            $dbman->add_field($tableB, $fieldA);
        } 
        if (!$dbman->field_exists($tableB, $fieldB)) {
            $dbman->add_field($tableB, $fieldB);
        } 

        if (!$dbman->field_exists($tableB, $fieldC)) {
            $dbman->add_field($tableB, $fieldC);
        } 

        if (!$dbman->field_exists($tableB, $fieldD)) {
            $dbman->add_field($tableB, $fieldD);
        } 

        if (!$dbman->field_exists($tableC, $fieldE)) {
            $dbman->add_field($tableC, $fieldE);
        }
        if (!$dbman->field_exists($tableC, $fieldF)) {
            $dbman->add_field($tableC, $fieldF);
        }
        // Main savepoint reached.
        upgrade_plugin_savepoint(true,2022052019.4, 'local', 'trainingprogram');
    }

    if ($oldversion < 2022052019.5) {
        $dbman = $DB->get_manager();
        $table = new xmldb_table('program_enrollments');
        $fieldB = new xmldb_field('enrolltype',XMLDB_TYPE_INTEGER, '12', null, XMLDB_NOTNULL, null, '0');
        if (!$dbman->field_exists($table, $fieldB)) {
            $dbman->add_field($table, $fieldB);
        } 
        upgrade_plugin_savepoint(true, 2022052019.5, 'local', 'trainingprogram');

    }

    if ($oldversion < 2022052019.6) {
        $dbman = $DB->get_manager();
        $table = new xmldb_table('tp_offerings');
        $fieldB = new xmldb_field('officials',XMLDB_TYPE_CHAR, '255',  null, null, null, null, null);
        if (!$dbman->field_exists($table, $fieldB)) {
            $dbman->add_field($table, $fieldB);
        } 
        upgrade_plugin_savepoint(true, 2022052019.6, 'local', 'trainingprogram');
    }

   if ($oldversion < 2022052021.1) {
		$dbman = $DB->get_manager();
		$tableA = new xmldb_table('program_enrollments');
		$tableB = new xmldb_table('program_completions');
		$field =  new xmldb_field('realuser', XMLDB_TYPE_INTEGER, '10', null, XMLDB_NOTNULL, null, '0','userid');
		if (!$dbman->field_exists($tableA, $field)) {
		    $dbman->add_field($tableA, $field);
		} 
		if (!$dbman->field_exists($tableB, $field)) {
		    $dbman->add_field($tableB, $field);
		} 
		upgrade_plugin_savepoint(true, 2022052021.1, 'local', 'trainingprogram');
    }


    if ($oldversion < 2022052021.4) {
        $dbman = $DB->get_manager();
        $table = new xmldb_table('local_trainingprogram');
        $fieldB = new xmldb_field('newjobfamilyoption',XMLDB_TYPE_CHAR, '255',  null, null, null, null, null);
        if (!$dbman->field_exists($table, $fieldB)) {
            $dbman->add_field($table, $fieldB);
        } 
        upgrade_plugin_savepoint(true, 2022052021.4, 'local', 'trainingprogram');
    }

    if ($oldversion < 2022052022.6) {
        $dbman = $DB->get_manager();
        $tableA = new xmldb_table('tp_offerings');
        $fieldA = new xmldb_field('sections',XMLDB_TYPE_INTEGER, '12', null, XMLDB_NOTNULL, null, '0');
        $fieldB = new xmldb_field('financially_closed_status',XMLDB_TYPE_INTEGER, '10', null, XMLDB_NOTNULL, null, '0',null);
        $fieldC = new xmldb_field('fc_status_added_by',XMLDB_TYPE_INTEGER, '10', null, XMLDB_NOTNULL, null, '0',null);
        $fieldD = new xmldb_field('fc_status_modified_at',XMLDB_TYPE_INTEGER, '10', null, XMLDB_NOTNULL, null, '0',null);
       
        if ($dbman->field_exists($tableA, $fieldA)) {
            $dbman->change_field_type($tableA, $fieldA);
        } 

        if (!$dbman->field_exists($tableA, $fieldB)) {
            $dbman->add_field($tableA, $fieldB);
        } 

        if (!$dbman->field_exists($tableA, $fieldC)) {
            $dbman->add_field($tableA, $fieldC);
        } 
        if (!$dbman->field_exists($tableA, $fieldD)) {
            $dbman->add_field($tableA, $fieldD);
        } 

        upgrade_plugin_savepoint(true,2022052022.6, 'local', 'trainingprogram');
    }
    
    if ($oldversion < 2022052022.8) {
        $dbman = $DB->get_manager();
        $table = new xmldb_table('program_enrollments');
        $field = new xmldb_field('orgofficial',XMLDB_TYPE_INTEGER, '12', null, XMLDB_NOTNULL, null, '0','organization');
        if (!$dbman->field_exists($table, $field)) {
            $dbman->add_field($table, $field);
        } 
        upgrade_plugin_savepoint(true,2022052022.8, 'local', 'trainingprogram');
    }

  if ($oldversion < 2022052023.3) {
        $dbman = $DB->get_manager();
        $table = new xmldb_table('local_trainingprogram');
        $tableA = new xmldb_table('tp_offerings');
        $field = new xmldb_field('externallink',XMLDB_TYPE_CHAR, '255',  null, null, null, null, null);
        $fieldA = new xmldb_field('externallinkcheck',XMLDB_TYPE_CHAR, '10',  null, null, null, null, null);
        if (!$dbman->field_exists($table, $field)) {
            $dbman->add_field($table, $field);
        } 
        if (!$dbman->field_exists($tableA, $field)) {
            $dbman->add_field($tableA, $field);
        } 

          if (!$dbman->field_exists($table, $fieldA)) {
            $dbman->add_field($table, $fieldA);
        } 
        if (!$dbman->field_exists($tableA, $fieldA)) {
            $dbman->add_field($tableA, $fieldA);
        } 
        upgrade_plugin_savepoint(true, 2022052023.3, 'local', 'trainingprogram');
    }


	if ($oldversion < 2022052023.4) {
		$dbman = $DB->get_manager();
		$tableA = new xmldb_table('tp_offerings');
		$field = new xmldb_field('offeringpricing',XMLDB_TYPE_INTEGER, '12', null, XMLDB_NOTNULL, null, '0','type');
		if (!$dbman->field_exists($tableA, $field)) {
		    $dbman->add_field($tableA, $field);
		} 
		upgrade_plugin_savepoint(true,2022052023.4, 'local', 'trainingprogram');
       }
	 
      if ($oldversion < 2022052023.5) {
    
        $dbman = $DB->get_manager();
        $table1 = new xmldb_table('program_methods');
        $table2 = new xmldb_table('evalution_methods');

        $table1->add_field('id', XMLDB_TYPE_INTEGER, '10', null, XMLDB_NOTNULL, XMLDB_SEQUENCE, null);
        $table1->add_field('name', XMLDB_TYPE_CHAR, '10', null, XMLDB_NOTNULL, null, '0');
        $table1->add_field('timecreated', XMLDB_TYPE_INTEGER, '10', null, XMLDB_NOTNULL, null, '0');
        $table1->add_field('usercreated', XMLDB_TYPE_INTEGER, '10', null, XMLDB_NOTNULL, null, '0');
        $table1->add_field('timemodified', XMLDB_TYPE_INTEGER, '10', null, null, null, '0');
        $table1->add_field('usermodified', XMLDB_TYPE_INTEGER, '10', null, null, null, '0');
        $table1->add_key('primary', XMLDB_KEY_PRIMARY, array('id'));


        $table2->add_field('id', XMLDB_TYPE_INTEGER, '10', null, XMLDB_NOTNULL, XMLDB_SEQUENCE, null);
        $table2->add_field('name', XMLDB_TYPE_CHAR, '10', null, XMLDB_NOTNULL, null, '0');
        $table2->add_field('timecreated', XMLDB_TYPE_INTEGER, '10', null, XMLDB_NOTNULL, null, '0');
        $table2->add_field('usercreated', XMLDB_TYPE_INTEGER, '10', null, XMLDB_NOTNULL, null, '0');
        $table2->add_field('timemodified', XMLDB_TYPE_INTEGER, '10', null, null, null, '0');
        $table2->add_field('usermodified', XMLDB_TYPE_INTEGER, '10', null, null, null, '0');
        $table2->add_key('primary', XMLDB_KEY_PRIMARY, array('id'));

        if (!$dbman->table_exists($table1)) {
            $dbman->create_table($table1);
        }
        
        if (!$dbman->table_exists($table2)) {
            $dbman->create_table($table2);
        }
        
        upgrade_plugin_savepoint(true,  2022052023.5, 'local', 'trainingprogram');
    }

    if ($oldversion <  2022052023.6) {

        $table1 = new xmldb_table('program_methods');
        $table2 = new xmldb_table('evalution_methods');

        $field = new xmldb_field('name',XMLDB_TYPE_TEXT, null, null, null, null, null);
        if ($dbman->field_exists($table1, $field)) {
            $dbman->change_field_type($table1, $field);
        } 
        
        if ($dbman->field_exists($table2, $field)) {
            $dbman->change_field_type($table2, $field);
        } 
        upgrade_plugin_savepoint(true, 2022052023.6, 'local', 'trainingprogram');

    }

    if ($oldversion < 2022052024.1) {
        $dbman = $DB->get_manager();
        $table = new xmldb_table('program_enrollments');
        $fieldB = new xmldb_field('enrolltype',XMLDB_TYPE_INTEGER, '12', null, XMLDB_NOTNULL, null, '0');
        
        // $DB->execute("Update {program_enrollments} SET enrolltype = 0 WHERE enrolltype IS NULL") ;
        // $DB->execute("Update {program_enrollments} SET enrolltype = 0 WHERE enrolltype =''") ;
        // $DB->execute("Update {program_enrollments} SET enrolltype = 1 WHERE enrolltype ='bulkenrollment'") ;
        // $DB->execute("Update {program_enrollments} SET enrolltype = 2 WHERE enrolltype ='bulkenroll'") ;
        // $DB->execute("Update {program_enrollments} SET enrolltype = 0 WHERE enrolltype NOT regexp '^[0-9]+$'") ;

        if ($dbman->field_exists($table, $fieldB)) {
            $dbman->change_field_type($table, $fieldB);
        } 
        upgrade_plugin_savepoint(true, 2022052024.1, 'local', 'trainingprogram');

    }

    if ($oldversion < 2022052026.1) {
        $dbman = $DB->get_manager();
        $table = new xmldb_table('local_trainingprogram');
        $fieldB = new xmldb_field('dynamicevaluationmethod',XMLDB_TYPE_CHAR, '255', null, null, null, null);
        
        if (!$dbman->field_exists($table, $fieldB)) {
           $dbman->add_field($table, $fieldB);
        } 
        upgrade_plugin_savepoint(true, 2022052026.1, 'local', 'trainingprogram');

    }


    if ($oldversion < 2022052026.4) {
        $table = new xmldb_table('offering_program_requests');
        $table->add_field('id', XMLDB_TYPE_INTEGER, '10', null, null, XMLDB_SEQUENCE, null);
        $table->add_field('referenceid', XMLDB_TYPE_INTEGER, '10', null, XMLDB_NOTNULL, null, '0');
        $table->add_field('code', XMLDB_TYPE_CHAR, '255', null, null, null);
        $table->add_field('entity', XMLDB_TYPE_CHAR, '255', null, null, null);
        $table->add_field('type', XMLDB_TYPE_CHAR, '255', null, null, null);
        $table->add_field('startdate', XMLDB_TYPE_INTEGER, '10', null, XMLDB_NOTNULL, null, '0');
        $table->add_field('enddate', XMLDB_TYPE_INTEGER, '10', null, XMLDB_NOTNULL, null, '0');
        $table->add_field('starttime', XMLDB_TYPE_INTEGER, '10', null, XMLDB_NOTNULL, null, '0');
        $table->add_field('endtime', XMLDB_TYPE_INTEGER, '10', null, XMLDB_NOTNULL, null, '0');
        $table->add_field('sellingprice', XMLDB_TYPE_INTEGER, '10', null, XMLDB_NOTNULL, null, '0');
        $table->add_field('actualprice', XMLDB_TYPE_INTEGER, '10', null, XMLDB_NOTNULL, null, '0');
        $table->add_field('trainingmethod',XMLDB_TYPE_CHAR, '255', null, null, null);
        $table->add_field('timecreated', XMLDB_TYPE_INTEGER, '10', null, XMLDB_NOTNULL, null, '0');
        $table->add_field('timemodified', XMLDB_TYPE_INTEGER, '10', null, XMLDB_NOTNULL, null, '0');
        $table->add_field('usercreated', XMLDB_TYPE_INTEGER, '10', null, XMLDB_NOTNULL, null, '0');
        $table->add_field('usermodified', XMLDB_TYPE_CHAR, '50', null, XMLDB_NOTNULL, null, '0');
        $table->add_key('primary', XMLDB_KEY_PRIMARY, array('id'));

        if (!$dbman->table_exists($table)) {
            $dbman->create_table($table);
        }
        upgrade_plugin_savepoint(true, 2022052026.4, 'local', 'trainingprogram');
    }

    if ($oldversion < 2022052026.9) {
        $logtable = new xmldb_table('official_tp_offerings');
        $logtable->add_field('id', XMLDB_TYPE_INTEGER, '10', null, XMLDB_NOTNULL, XMLDB_SEQUENCE, null);
        $logtable->add_field('oldid', XMLDB_TYPE_CHAR, '255', null, null, null, null);
        $logtable->add_field('code', XMLDB_TYPE_CHAR, '50',null, XMLDB_NOTNULL, null, '0');
        $logtable->add_field('trainingid', XMLDB_TYPE_CHAR, '255', null, null, null, null);
        $logtable->add_field('startdate', XMLDB_TYPE_INTEGER, '10', null, XMLDB_NOTNULL, null, '0');
        $logtable->add_field('enddate', XMLDB_TYPE_INTEGER, '10', null, XMLDB_NOTNULL, null, '0');
        $logtable->add_field('time', XMLDB_TYPE_INTEGER, '10', null, XMLDB_NOTNULL, null, '0');
        $logtable->add_field('endtime', XMLDB_TYPE_INTEGER, '10', null, XMLDB_NOTNULL, null, '0');
        $logtable->add_field('sections',XMLDB_TYPE_INTEGER, '12', null, XMLDB_NOTNULL, null, '0');
        $logtable->add_field('duration',  XMLDB_TYPE_INTEGER, '10', null, XMLDB_NOTNULL, null, '0');
        $logtable->add_field('type', XMLDB_TYPE_CHAR, '255', null, null, null, null);
        $logtable->add_field('offeringpricing',XMLDB_TYPE_INTEGER, '12', null, XMLDB_NOTNULL, null, '0');
        $logtable->add_field('trainingmethod',XMLDB_TYPE_CHAR, '255', null, null, null, null);
        $logtable->add_field('meetingtype',XMLDB_TYPE_INTEGER, '12', null, XMLDB_NOTNULL, null, '0');
        $logtable->add_field('meetingid',XMLDB_TYPE_INTEGER, '12', null, XMLDB_NOTNULL, null, '0');
        $logtable->add_field('prequiz',XMLDB_TYPE_INTEGER, '12', null, XMLDB_NOTNULL, null, '0');
        $logtable->add_field('postquiz',XMLDB_TYPE_INTEGER, '12', null, XMLDB_NOTNULL, null, '0');
        $logtable->add_field('availableseats', XMLDB_TYPE_INTEGER, '10', null, XMLDB_NOTNULL, null, '0');
        $logtable->add_field('organization', XMLDB_TYPE_INTEGER, '10', null, XMLDB_NOTNULL, null, '0');
        $logtable->add_field('sellingprice', XMLDB_TYPE_INTEGER, '10', null, XMLDB_NOTNULL, null, '0');
        $logtable->add_field('actualprice', XMLDB_TYPE_INTEGER, '10', null, XMLDB_NOTNULL, null, '0');
        $logtable->add_field('halladdress', XMLDB_TYPE_INTEGER, '10', null, XMLDB_NOTNULL, null, '0');
        $logtable->add_field('halllocation', XMLDB_TYPE_CHAR, '50', null, null, null, null);
        $logtable->add_field('officials', XMLDB_TYPE_CHAR, '255', null, null, null, null);
        $logtable->add_field('estimatedbudget', XMLDB_TYPE_FLOAT, '10', null, XMLDB_NOTNULL, null, '0');
        $logtable->add_field('proposedcost', XMLDB_TYPE_FLOAT, '10', null, XMLDB_NOTNULL, null, '0');
        $logtable->add_field('finalamount', XMLDB_TYPE_FLOAT, '10', null, XMLDB_NOTNULL, null, '0');
        $logtable->add_field('tagrement', XMLDB_TYPE_FLOAT, '10', null, XMLDB_NOTNULL, null, '0');
        $logtable->add_field('trainingcost', XMLDB_TYPE_FLOAT, '10', null, XMLDB_NOTNULL, null, '0');
        $logtable->add_field('attachmentpdf',XMLDB_TYPE_CHAR, '255', null, null, null, null);
        $logtable->add_field('officialproposal',XMLDB_TYPE_CHAR, '255', null, null, null, null);
        $logtable->add_field('officialpo',XMLDB_TYPE_CHAR, '255', null, null, null, null);
        $logtable->add_field('tagrrement',XMLDB_TYPE_CHAR, '255', null, null, null, null);
        $logtable->add_field('languages',XMLDB_TYPE_CHAR, '255', null, null, null, null);
        $logtable->add_field('classification',XMLDB_TYPE_CHAR, '255', null, null, null, null);
        $logtable->add_field('published',XMLDB_TYPE_INTEGER, '10', null, XMLDB_NOTNULL, null, '1');
        $logtable->add_field('trainertype',XMLDB_TYPE_INTEGER, '10', null, XMLDB_NOTNULL, null, '0');
        $logtable->add_field('trainerorg',XMLDB_TYPE_INTEGER, '10', null, XMLDB_NOTNULL, null, '0');
        $logtable->add_field('externallink',XMLDB_TYPE_CHAR, '255', null, null, null, null);
        $logtable->add_field('externallinkcheck',XMLDB_TYPE_CHAR, '255', null, null, null, null);
        $logtable->add_field('timecreated', XMLDB_TYPE_INTEGER, '10', null, XMLDB_NOTNULL, null, '0');
        $logtable->add_field('timemodified', XMLDB_TYPE_INTEGER, '10', null, XMLDB_NOTNULL, null, '0');
        $logtable->add_field('usercreated', XMLDB_TYPE_INTEGER, '10', null, XMLDB_NOTNULL, null, '0');
        $logtable->add_field('usermodified', XMLDB_TYPE_INTEGER, '10', null, XMLDB_NOTNULL, null, '0');
        $logtable->add_key('primary', XMLDB_KEY_PRIMARY, array('id'));

        if (!$dbman->table_exists($logtable)) {
            $dbman->create_table($logtable);
        }
        upgrade_plugin_savepoint(true, 2022052026.9, 'local', 'trainingprogram');
    }

    if ($oldversion < 2022052028) {
        $table = new xmldb_table('groupdiscounts');
        $table->add_field('id', XMLDB_TYPE_INTEGER, '12', null, XMLDB_NOTNULL, XMLDB_SEQUENCE, null);
        $table->add_field('group_count', XMLDB_TYPE_INTEGER, '12', null, XMLDB_NOTNULL, null, '0');
        $table->add_field('organizations', XMLDB_TYPE_CHAR, '255', null, null, null, null);
        $table->add_field('discount', XMLDB_TYPE_INTEGER, '12', null, XMLDB_NOTNULL, null, '0');
        $table->add_field('expired_date', XMLDB_TYPE_INTEGER, '10', null, XMLDB_NOTNULL, null, '0');
        $table->add_field('status', XMLDB_TYPE_INTEGER, '2', null, XMLDB_NOTNULL, null, '1');
        $table->add_field('programs', XMLDB_TYPE_CHAR, '255', null, null, null, null);
        $table->add_field('exams', XMLDB_TYPE_CHAR, '255', null, null, null, null);
        $table->add_field('events', XMLDB_TYPE_CHAR, '255', null, null, null, null);
        $table->add_field('beneficiaries',XMLDB_TYPE_CHAR, '255', null, null, null, null);
        $table->add_field('timecreated', XMLDB_TYPE_INTEGER, '10', null, XMLDB_NOTNULL, null, '0');
        $table->add_field('timemodified', XMLDB_TYPE_INTEGER, '10', null, XMLDB_NOTNULL, null, '0');
        $table->add_field('usercreated', XMLDB_TYPE_INTEGER, '10', null, XMLDB_NOTNULL, null, '0');
        $table->add_field('usermodified', XMLDB_TYPE_INTEGER, '10', null, XMLDB_NOTNULL, null, '0');
        $table->add_key('primary', XMLDB_KEY_PRIMARY, array('id'));
        if (!$dbman->table_exists($table)) {
            $dbman->create_table($table);
        }
        upgrade_plugin_savepoint(true, 2022052028, 'local', 'trainingprogram');
    }

    if ($oldversion < 2022052028.4) {
    
        $dbman = $DB->get_manager();
        $table = new xmldb_table('earlyregistration_management');
        $tableB = new xmldb_table('coupon_management');
        $tableC = new xmldb_table('groupdiscounts');
        $fieldA = new xmldb_field('days',XMLDB_TYPE_INTEGER, '10', null, null, null, 0);
        $fieldB = new xmldb_field('dayfrom',XMLDB_TYPE_INTEGER, '12', null, null, null, 0);
        $fieldC = new xmldb_field('dayto',XMLDB_TYPE_INTEGER, '12', null, null, null, 0);
        $fieldD = new xmldb_field('programs',XMLDB_TYPE_CHAR, '255', null, null, null, null);
        $fieldE = new xmldb_field('exams',XMLDB_TYPE_CHAR, '255', null, null, null, null);
        $fieldF = new xmldb_field('events',XMLDB_TYPE_CHAR, '255', null, null, null, null);
        $fieldG = new xmldb_field('addedtocartfor',XMLDB_TYPE_INTEGER, '12', null, XMLDB_NOTNULL, null, 0,'coupon_status');
        $fieldH = new xmldb_field('addedtocarton',XMLDB_TYPE_INTEGER, '12', null, XMLDB_NOTNULL, null, 0,'addedtocartfor');
        $fieldI = new xmldb_field('beneficiaries',XMLDB_TYPE_CHAR, '255', null, null, null, null);
        if (!$dbman->field_exists($table, $fieldA)) {
            $dbman->add_field($table, $fieldA);
        }
        if ($dbman->field_exists($table, $fieldB)) {
           $dbman->drop_field($table, $fieldB);
        } 
        if ($dbman->field_exists($table, $fieldC)) {
            $dbman->drop_field($table, $fieldC);
        } 
        if (!$dbman->field_exists($table, $fieldD)) {
            $dbman->add_field($table, $fieldD);
        }
        if (!$dbman->field_exists($table, $fieldE)) {
            $dbman->add_field($table, $fieldE);
        }
        if (!$dbman->field_exists($table, $fieldF)) {
            $dbman->add_field($table, $fieldF);
        } 

        if (!$dbman->field_exists($tableB, $fieldD)) {
            $dbman->add_field($tableB, $fieldD);
        }
        if (!$dbman->field_exists($tableB, $fieldE)) {
            $dbman->add_field($tableB, $fieldE);
        }
        if (!$dbman->field_exists($tableB, $fieldF)) {
            $dbman->add_field($tableB, $fieldF);
        } 

        if (!$dbman->field_exists($table, $fieldI)) {
            $dbman->add_field($table, $fieldI);
        } 

        if (!$dbman->field_exists($tableB, $fieldG)) {
            $dbman->add_field($tableB, $fieldG);
        } 

        if (!$dbman->field_exists($tableB, $fieldH)) {
            $dbman->add_field($tableB, $fieldH);
        } 

        if (!$dbman->field_exists($tableC, $fieldI)) {
            $dbman->add_field($tableC, $fieldI);
        } 
        upgrade_plugin_savepoint(true, 2022052028.4, 'local', 'trainingprogram'); 
    }

    if ($oldversion < 2022052028.5) {
        $dbman = $DB->get_manager();
        $table = new xmldb_table('offering_program_requests');
        $fieldA = new xmldb_field('trainingmethod',XMLDB_TYPE_CHAR, '255', null, null, null, null,'endtime');
        $fieldB = new xmldb_field('seats',XMLDB_TYPE_INTEGER, '12', null, XMLDB_NOTNULL, null, 0);
        $fieldC = new xmldb_field('sellingprice',XMLDB_TYPE_CHAR, '12', null, XMLDB_NOTNULL, null, null,'trainingmethod');
        $fieldD = new xmldb_field('actualprice',XMLDB_TYPE_CHAR, '12', null, XMLDB_NOTNULL, null, null,'sellingprice');

        if (!$dbman->field_exists($table, $fieldA)) {
           $dbman->add_field($table, $fieldA);
        } 

        if ($dbman->field_exists($table, $fieldB)) {
            $dbman->drop_field($table, $fieldB);
        } 

        if (!$dbman->field_exists($table, $fieldC)) {
            $dbman->add_field($table, $fieldC);
        } 

        if (!$dbman->field_exists($table, $fieldD)) {
            $dbman->add_field($table, $fieldD);
        } 
        upgrade_plugin_savepoint(true, 2022052028.5, 'local', 'trainingprogram');

    }

    if ($oldversion < 2022052028.6) {
        $dbman = $DB->get_manager();
        $table = new xmldb_table('local_trainingprogram');
        $field = new xmldb_field('discount',XMLDB_TYPE_INTEGER, '12', null, XMLDB_NOTNULL, null, 0);
        if ($dbman->field_exists($table, $field)) {
            $dbman->drop_field($table, $field);
        } 
        upgrade_plugin_savepoint(true,2022052028.6, 'local', 'trainingprogram');

    }

    return true;

}
