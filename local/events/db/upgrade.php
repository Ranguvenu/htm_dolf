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
function xmldb_local_events_upgrade($oldversion) {
	global $DB, $CFG;
	$dbman = $DB->get_manager();
    if ($oldversion < 2022041700.14) {
        $dbman = $DB->get_manager();
        $table = new xmldb_table('local_event_attendees');
        $field = new xmldb_field('userid',XMLDB_TYPE_INTEGER, '10', null, XMLDB_NOTNULL, null, '0');
        if (!$dbman->field_exists($table, $field)) {
            $dbman->add_field($table, $field);
        }
        upgrade_plugin_savepoint(true, 2022041700.14, 'local', 'events');
    } 
    if ($oldversion < 2022041700.17) {
        $table = new xmldb_table('local_events');
        $field = new xmldb_field('targetaudience');
        $field->set_attributes(XMLDB_TYPE_TEXT, null, null, null, null, null);
        try {
        $dbman->change_field_type($table, $field);
        } catch (moodle_exception $e) {}
        upgrade_plugin_savepoint(true, 2022041700.17, 'local', 'events');
    }   

    if ($oldversion < 2022041700.19) {
        $dbman = $DB->get_manager();
        $table = new xmldb_table('local_events');
        
        $field = new xmldb_field('halladdress',XMLDB_TYPE_INTEGER, '10', null, XMLDB_NOTNULL, null,0,'method');
         if (!$dbman->field_exists($table, $field)) {
            $dbman->add_field($table, $field);
        }
        upgrade_plugin_savepoint(true, 2022041700.19, 'local', 'events');
    }

    if ($oldversion < 2022041700.21) {
        $table = new xmldb_table('local_events');
        $field = new xmldb_field('language');
        $field->set_attributes(XMLDB_TYPE_CHAR, '255', null, null, null, null);
        try {
        $dbman->change_field_type($table, $field);
        } catch (moodle_exception $e) {}

        $field2 = new xmldb_field('audiencegender');
        $field2->set_attributes(XMLDB_TYPE_CHAR, '255', null, null, null, null);
        try {
        $dbman->change_field_type($table, $field2);
        } catch (moodle_exception $e) {}

        $field3 = new xmldb_field('eventmanager');
        $field3->set_attributes(XMLDB_TYPE_CHAR, '255', null, null, null, null);
        try {
        $dbman->change_field_type($table, $field3);
        } catch (moodle_exception $e) {}

        $field4 = new xmldb_field('eventduration',XMLDB_TYPE_INTEGER, '10', null, null, null,0);
         if (!$dbman->field_exists($table, $field4)) {
            $dbman->add_field($table, $field4);
        }

        $field5 = new xmldb_field('slot',XMLDB_TYPE_INTEGER, '10', null, null, null,0);
        if (!$dbman->field_exists($table, $field5)) {
           $dbman->add_field($table, $field5);
       }
        upgrade_plugin_savepoint(true, 2022041700.21, 'local', 'events');
    }
    if ($oldversion < 2022041700.22) {
        $table = new xmldb_table('local_events');
        $field = new xmldb_field('halladdress');
        $field->set_attributes(XMLDB_TYPE_CHAR, '100', null, null, null, null);
        try {
        $dbman->change_field_type($table, $field);
        } catch (moodle_exception $e) {}
        upgrade_plugin_savepoint(true, 2022041700.22, 'local', 'events');
    }
    if ($oldversion < 2022041700.24) {
        $dbman = $DB->get_manager();
        $table = new xmldb_table('local_event_finance');
        $table->add_field('id', XMLDB_TYPE_INTEGER, '10', null, XMLDB_NOTNULL, XMLDB_SEQUENCE, null);
        $table->add_field('eventid', XMLDB_TYPE_INTEGER, '10', null, XMLDB_NOTNULL, null, '0');
        $table->add_field('sponsorid', XMLDB_TYPE_INTEGER, '10', null, XMLDB_NOTNULL, null, '0');
        $table->add_field('type', XMLDB_TYPE_INTEGER, '10', null, XMLDB_NOTNULL, null, '0');
        $table->add_field('amount', XMLDB_TYPE_INTEGER, '10', null, XMLDB_NOTNULL, null, '0');
        $table->add_field('timecreated', XMLDB_TYPE_INTEGER, '10', null, XMLDB_NOTNULL, null, '0');
        $table->add_field('timemodified', XMLDB_TYPE_INTEGER, '10', null, XMLDB_NOTNULL, null, '0');
        $table->add_key('primary', XMLDB_KEY_PRIMARY, ['id']);
        if (!$dbman->table_exists($table)) {
            $dbman->create_table($table);
        }
        upgrade_plugin_savepoint(true, 2022041700.24, 'local', 'events');
    }
    if ($oldversion < 2022041700.27) {
        $table = new xmldb_table('local_event_finance');
        $field = new xmldb_field('sponsorid',XMLDB_TYPE_INTEGER, '10', null, XMLDB_NOTNULL, null);
        $newname1='itemid';
        if ($dbman->field_exists($table, $field)) {
        $dbman->rename_field($table, $field, $newname1);
        }

        $field1 = new xmldb_field('itemname',XMLDB_TYPE_CHAR, '100', null, null, null,null);
        if (!$dbman->field_exists($table, $field1)) {
           $dbman->add_field($table, $field1);
        }

        $field2 = new xmldb_field('expensetype',XMLDB_TYPE_INTEGER, '10', null, null, null,0);
        if (!$dbman->field_exists($table, $field2)) {
           $dbman->add_field($table, $field2);
        }

        $field3 = new xmldb_field('logistic',XMLDB_TYPE_INTEGER, '10', null, null, null,0);
        if (!$dbman->field_exists($table, $field3)) {
           $dbman->add_field($table, $field3);
        }

       $field4 = new xmldb_field('billingfile',XMLDB_TYPE_INTEGER, '10', null, null, null,0);
       if (!$dbman->field_exists($table, $field4)) {
          $dbman->add_field($table, $field4);
        }

        $field5 = new xmldb_field('itemid');
        $field5->set_attributes(XMLDB_TYPE_INTEGER, '10', null, null, null, 0);
        try {
        $dbman->change_field_type($table, $field5);
        } catch (moodle_exception $e) {}
        upgrade_plugin_savepoint(true, 2022041700.27, 'local', 'events');
    }
    if ($oldversion < 2022041700.29) {
        $table = new xmldb_table('local_events');
        $field1 = new xmldb_field('virtualtype', XMLDB_TYPE_INTEGER, '10', null, null, null,0);
        if (!$dbman->field_exists($table, $field1)) {
           $dbman->add_field($table, $field1);
        }

        $field2 = new xmldb_field('zoom',XMLDB_TYPE_INTEGER, '10', null, null, null,0);
        if (!$dbman->field_exists($table, $field2)) {
           $dbman->add_field($table, $field2);
        }

        $field3 = new xmldb_field('webex',XMLDB_TYPE_INTEGER, '10', null, null, null,0);
        if (!$dbman->field_exists($table, $field3)) {
           $dbman->add_field($table, $field3);
        }

        upgrade_plugin_savepoint(true, 2022041700.29, 'local', 'events');
    }
    if($oldversion<2022041700.37){
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
        $initcontent = array('name' => 'events','shortname' => 'events','parent_module' => '0','usercreated' => '2','timecreated' => $time,'usermodified' => 2,'timemodified' => NULL, 'pluginname' => 'events','plugintype' => 'local');
        $parentid = $DB->get_field('local_notification_type', 'id', array('shortname' => 'events'));
        if(!$parentid){
            $parentid = $DB->insert_record('local_notification_type', $initcontent);
        }
        $notification_type_data = array(
            array('name' => 'Create Event','shortname' => 'events_create','parent_module' => $parentid,'usercreated' => '2','timecreated' => $time,'usermodified' => 2,'timemodified' => NULL, 'pluginname' => 'events','plugintype' => 'local'),
           array('name' => 'Update Event','shortname' => 'events_update','parent_module' => $parentid,'usercreated' => '2','timecreated' => $time,'usermodified' => 2,'timemodified' => NULL, 'pluginname' => 'events','plugintype' => 'local'),
           array('name' => 'Registration','shortname' => 'events_registration','parent_module' => $parentid,'usercreated' => '2','timecreated' => $time,'usermodified' => 2,'timemodified' => NULL, 'pluginname' => 'events','plugintype' => 'local'),
           array('name' => 'Speakers','shortname' => 'events_speakers','parent_module' => $parentid,'usercreated' => '2','timecreated' => $time,'usermodified' => 2,'timemodified' => NULL, 'pluginname' => 'events','plugintype' => 'local'),
           array('name' => 'Sponsors','shortname' => 'events_sponsors','parent_module' => $parentid,'usercreated' => '2','timecreated' => $time,'usermodified' => 2,'timemodified' => NULL, 'pluginname' => 'events','plugintype' => 'local'),
           array('name' => 'Partners','shortname' => 'events_partners','parent_module' => $parentid,'usercreated' => '2','timecreated' => $time,'usermodified' => 2,'timemodified' => NULL, 'pluginname' => 'events','plugintype' => 'local'),
           array('name' => 'Completion','shortname' => 'events_completion','parent_module' => $parentid,'usercreated' => '2','timecreated' => $time,'usermodified' => 2,'timemodified' => NULL, 'pluginname' => 'events','plugintype' => 'local'),
           array('name' => 'Before 7 days','shortname' => 'events_before_7_days','parent_module' => $parentid,'usercreated' => '2','timecreated' => $time,'usermodified' => 2,'timemodified' => NULL, 'pluginname' => 'events','plugintype' => 'local'),
           array('name' => 'Before 48 Hours','shortname' => 'events_before_48_hours','parent_module' => $parentid,'usercreated' => '2','timecreated' => $time,'usermodified' => 2,'timemodified' => NULL, 'pluginname' => 'events','plugintype' => 'local'),       
           array('name' => 'Before 24 Hours','shortname' => 'events_before_24_hours','parent_module' => $parentid,'usercreated' => '2','timecreated' => $time,'usermodified' => 2,'timemodified' => NULL, 'pluginname' => 'events','plugintype' => 'local'),
           array('name' => 'Send Conclusion','shortname' => 'events_send_conclusion','parent_module' => $parentid,'usercreated' => '2','timecreated' => $time,'usermodified' => 2,'timemodified' => NULL, 'pluginname' => 'events','plugintype' => 'local'),
           array('name' => 'After Session','shortname' => 'events_after_session','parent_module' => $parentid,'usercreated' => '2','timecreated' => $time,'usermodified' => 2,'timemodified' => NULL, 'pluginname' => 'events','plugintype' => 'local'), 
           array('name' => 'On Change','shortname' => 'events_onchange','parent_module' => $parentid,'usercreated' => '2','timecreated' => $time,'usermodified' => 2,'timemodified' => NULL, 'pluginname' => 'events','plugintype' => 'local'), 
           array('name' => 'Cancel','shortname' => 'events_cancel','parent_module' => $parentid,'usercreated' => '2','timecreated' => $time,'usermodified' => 2,'timemodified' => NULL, 'pluginname' => 'events','plugintype' => 'local'),
           array('name' => 'Re-Schedule','shortname' => 'events_reschedule','parent_module' => $parentid,'usercreated' => '2','timecreated' => $time,'usermodified' => 2,'timemodified' => NULL, 'pluginname' => 'events','plugintype' => 'local'),    
        );
        
        foreach($notification_type_data as $notification_type){
            unset($notification_type['timecreated']);
            if(!$DB->record_exists('local_notification_type',  $notification_type)){
                $notification_type['timecreated'] = $time;
                $DB->insert_record('local_notification_type', $notification_type);
            }
        }
        upgrade_plugin_savepoint(true, 2022041700.37, 'local', 'events');
    } 
    if ($oldversion < 2022041700.38) {


        $time = time();
        $initcontent = array('name' => 'events','shortname' => 'events','parent_module' => '0','usercreated' => '2','timecreated' => $time,'usermodified' => 2,'timemodified' => NULL, 'pluginname' => 'events');
        $parentid = $DB->get_field('local_notification_type', 'id', array('shortname' => 'events'));

        if(!$parentid){

            $parentid = $DB->insert_record('local_notification_type', $initcontent);

        }else{

            $eventcompletionid = $DB->get_field('local_notification_type', 'id', array('shortname' => 'completion','parent_module' => $parentid));

            if($eventcompletionid){

                $DB->delete_records('local_notification_type',  array('id'=>$eventcompletionid));
            }

        }

        $notification_type_data = array(
           array('name' => 'Completion','shortname' => 'events_completion','parent_module' => $parentid,'usercreated' => '2','timecreated' => $time,'usermodified' => 2,'timemodified' => NULL, 'pluginname' => 'events','plugintype' => 'local'),
 
        );
        
        foreach($notification_type_data as $notification_type){
            unset($notification_type['timecreated']);
            if(!$DB->record_exists('local_notification_type',  $notification_type)){
                $notification_type['timecreated'] = $time;
                $DB->insert_record('local_notification_type', $notification_type);
            }
        }
        upgrade_plugin_savepoint(true,2022041700.38, 'local', 'events');
    }
    
    if ($oldversion < 2022041700.4) {
        $dbman = $DB->get_manager();
        $table = new xmldb_table('local_event_attendees');
        $field = new xmldb_field('usercreated',XMLDB_TYPE_INTEGER, '10', null, XMLDB_NOTNULL, null, '0');
        if (!$dbman->field_exists($table, $field)) {
            $dbman->add_field($table, $field);
        }

        $field2 = new xmldb_field('usermodified',XMLDB_TYPE_INTEGER, '10', null, XMLDB_NOTNULL, null, '0');
        if (!$dbman->field_exists($table, $field2)) {
            $dbman->add_field($table, $field2);
        }
        upgrade_plugin_savepoint(true, 2022041700.4, 'local', 'events');
    }
    
    if ($oldversion < 2022041700.4) {
        $dbman = $DB->get_manager();
        $table = new xmldb_table('local_event_attendees');
        $field = new xmldb_field('audiencegender',XMLDB_TYPE_INTEGER, '10', null, null, null, '0');
        if (!$dbman->field_exists($table, $field)) {
            $dbman->add_field($table, $field);
        }
        upgrade_plugin_savepoint(true, 2022041700.4, 'local', 'events');
    }
    if ($oldversion < 2022041700.5) {
        $time = time();

        $notification_info_data = array(
            array(
                'subject' => '[event_name] is Created',
                'body' => '<p dir="ltr" style="text-align: left;">Dear [event_userfullname],&nbsp;</p><p dir="ltr" style="text-align: left;">[event_name] event has been successfully created for your organization members.<br></p>',
                'arabic_subject' =>'تم نشاء فعالية [event_name]',
                'arabic_body' => '<p dir="ltr" style="text-align: left;">عزيزي [event_userfullname]،</p><p dir="ltr" style="text-align: left;">&nbsp;تم بنجاح إنشاء فعالية [event_name] لأعضاء الجهة التابع لها.<br></p>',
                'notification_type_shortname'=>'events_create'
            ),

            array(
                'subject' => '[event_name] is Updated',
                'body' => '<p dir="ltr" style="text-align: left;">Dear [event_userfullname],&nbsp;</p><p dir="ltr" style="text-align: left;">[event_name] event  has been successfully updated  for your organizations members.<br></p>',
                'arabic_subject' =>'تم تحديث فعالية [event_name]',
                'arabic_body' => '<p dir="ltr" style="text-align: left;">عزيزي [event_userfullname] ،</p><p dir="ltr" style="text-align: left;">&nbsp;تم بنجاح تحديث فعالية [event_name]&nbsp; لأعضاء الجهة التابع لها.<br></p>',
                'notification_type_shortname'=>'events_update'
            ),

            array(
                'subject' => ' Registration at [event_name]',
                'body' => '<p dir="ltr" style="text-align: left;">Dear [event_userfullname],</p><p dir="ltr" style="text-align: left;">&nbsp;You have been registered successfully at  [event_name] event.&nbsp;</p><p dir="ltr" style="text-align: left;">Thanks.<br></p>',
                'arabic_subject' =>'التسجيل في [event_name]',
                'arabic_body' => '<p dir="ltr" style="text-align: left;">عزيزي [event_userfullname]،&nbsp;</p><p dir="ltr" style="text-align: left;">لقد تم تسجيلك بنجاح في فعالية [event_name].</p><p dir="ltr" style="text-align: left;">&nbsp;شكرًا.<br></p>',
                'notification_type_shortname'=>'events_registration'
            ),

            array(
                'subject' => '[event_name] Update', 
                'body' => '<p dir="ltr" style="text-align: left;">Dear [event_managername],&nbsp;</p><p dir="ltr" style="text-align: left;">The speaker [event_speakername]  has been assigned for the event:[event_name].</p><p dir="ltr" style="text-align: left;">&nbsp;Thanks.<br></p>',
                'arabic_subject' =>'تحديث [event_name]',
                'arabic_body' => '<p dir="ltr" style="text-align: left;">مدير الفعاليات [event_managername] ،&nbsp;</p><p dir="ltr" style="text-align: left;">تم تعيين المتحدث [event_speakername] لفعالية : [event_name].</p><p dir="ltr" style="text-align: left;">&nbsp;شكرًا.<br></p>',
                'notification_type_shortname'=>'events_speakers'
            ),   
            array(
                'subject' => '[event_name] Update',
                'body' => '<p dir="ltr" style="text-align: left;"></p><div>Dear [event_managername],</div><div><br></div><div>&nbsp;The sponsor [event_sponsorname]  has been assigned for the event: [event_name].</div><div><br></div><div>&nbsp;Thanks.</div><div><br></div><br><p></p>',
                'arabic_subject' =>'تحديث [event_name]',
                'arabic_body' => '<p dir="ltr" style="text-align: left;">عزيزي [event_managername] ،</p><p dir="ltr" style="text-align: left;">&nbsp;تم تعيين الراعي &nbsp;[event_sponsorname] لفعالية: [event_name].</p><p dir="ltr" style="text-align: left;">&nbsp;شكرًا.<br></p>',
                'notification_type_shortname'=>'events_sponsors'
            ),
            array(
                'subject' => '[event_name] Update',
                'body' => '<p dir="ltr" style="text-align: left;">Dear [event_managername],&nbsp;</p><p dir="ltr" style="text-align: left;">The partner [event_partnername]  has been assigned for the event: [event_name].&nbsp;</p><p dir="ltr" style="text-align: left;">Thanks.<br></p>',
                'arabic_subject' =>'تحديث [event_name]',
                'arabic_body' => '<p dir="ltr" style="text-align: left;"></p><div> عزيزي [event_managername], ،</div><div><br></div><div>&nbsp;تم تعيين الشريك [event_partnername] لفعالية: [event_name].&nbsp;</div><div><br></div><div>شكرًا.</div><div><br></div><br><p></p>',
                'notification_type_shortname'=>'events_partners'
            ), 
            array(
                'subject' => ' Attendance reminder [event_related_module_name] ',
                'body' => '<p dir="ltr" style="text-align: left;">Dear [event_userfullname] , This is a reminder that [event_related_module_name] will start after 7 days at [event_related_module_date] [event_related_module_time]. Thanks<br><br></p>',
                'arabic_subject' =>'تذكير حضور  [event_related_module_name] ',
                'arabic_body' => '<p dir="ltr" style="text-align: left;">عزيزي [event_userfullname]، هذا تذكير بأن [event_related_module_name]&nbsp; سيبدأ&nbsp; بعد 7 أيام في تاريخ:&nbsp; [event_related_module_date] [event_related_module_time]. شكرًا<br></p>',
                'notification_type_shortname'=>'events_before_7_days'
            ),
            array(
                'subject' => ' Attendance reminder [event_related_module_name]',
                'body' => '<p dir="ltr" style="text-align: left;">Dear [event_userfullname], This is a reminder that [event_related_module_name] will start after 48 hours at [event_related_module_date] [event_related_module_time]. Thanks<br><br></p>',
                'arabic_subject' =>'تذكير حضور [event_related_module_name] ',
                'arabic_body' => '<p dir="ltr" style="text-align: left;">عزيزي [event_userfullname] ، هذا تذكير بأن [event_related_module_name] سيبدأ بعد 48 ساعة في تاريخ: [event_related_module_date] [event_related_module_time]. شكرًا<br></p>',
                'notification_type_shortname'=>'events_before_48_hours'
            ), 
            array(
                'subject' => ' Attendance reminder [event_related_module_name]',
                'body' => '<p dir="ltr" style="text-align: left;">Dear [event_userfullname], This is a reminder that [event_related_module_name] will start after 24 hours at [event_related_module_date] [event_related_module_time]. Thanks<br><br></p>',
                'arabic_subject' =>'تذكير حضور [event_related_module_name] ',
                'arabic_body' => '<p dir="ltr" style="text-align: left;">عزيزي [event_userfullname] ، هذا تذكير بأن [event_related_module_name] سيبدأ بعد 24 ساعة في تاريخ: [event_related_module_date] [event_related_module_time]. شكرًا<br></p>  ',
                'notification_type_shortname'=>'events_before_24_hours'
            ), 
            array(
                'subject' => 'Thanks for Attending [event_related_module_name]',
                'body' => '<p dir="ltr" style="text-align: left;">Dear [event_userfullname],&nbsp;</p><p dir="ltr" style="text-align: left;">[event_related_module_name] is done, Thank you for attending.<br><br></p>',
                'arabic_subject' =>'شكرًا لحضورك',
                'arabic_body' => '<p dir="ltr" style="text-align: left;">عزيزي [event_userfullname] ،</p><p dir="ltr" style="text-align: left;">&nbsp;تم الانتهاء من [event_related_module_name]، شكرًا لكم على الحضور.<br><br></p> ',
                'notification_type_shortname'=>'events_send_conclusion'
            ), 
            array(
                'subject' => 'Thanks for Attending the session ',
                'body' => '<p dir="ltr" style="text-align: left;">Dear [event_userfullname],</p><p dir="ltr" style="text-align: left;">&nbsp; [event_related_module_name] is done, Thank you for attending.<br></p>',
                'arabic_subject' =>'شكرًا لحضورك',
                'arabic_body' => '<p dir="ltr" style="text-align: left;">عزيزي [event_userfullname] ،&nbsp;</p><p dir="ltr" style="text-align: left;">تم الانتهاء من [event_related_module_name]، شكرًا لكم على الحضور.<br><br></p> ',
                'notification_type_shortname'=>'events_after_session'
            ),
            array(
                'subject' => '[RelatedModuleName] is Update',
                'body' => '<p dir="ltr" style="text-align: left;">Dear [FullName] ,&nbsp;</p><p dir="ltr" style="text-align: left;">We would like to inform you about the new update on [RelatedModuleName]. You can display the details from the following link [RelatedModulesLink].&nbsp;</p><p dir="ltr" style="text-align: left;">Thanks<br></p>',
                'arabic_subject' =>'تحديث [RelatedModuleName] ',
                'arabic_body' => '<p dir="ltr" style="text-align: left;">عزيزي [FullName]،</p><p dir="ltr" style="text-align: left;">&nbsp;نود إشعاركم أنه تم تحديث [RelatedModuleName]يمكنكم استعراض التفاصيل من خلال الرابط التالي [RelatedModulesLink].&nbsp;</p><p dir="ltr" style="text-align: left;">شكرا<br></p> ',
                'notification_type_shortname'=>'events_onchange'
            ),
            array(
                'subject' => '[RelatedModuleName] Cancellation ',
                'body' => '<p dir="ltr" style="text-align: left;"></p><p dir="ltr">Dear [FullName] ,<br></p><p dir="ltr">We are sorry to inform you about the cancellation of&nbsp; [RelatedModuleName].<br></p><p dir="ltr">Thanks</p><br><p></p>',
                'arabic_subject' =>'إلغاء [RelatedModuleName] ',
                'arabic_body' => '<p dir="ltr" style="text-align: left;"></p><p dir="ltr">عزيزي [FullName]،</p><p dir="ltr">&nbsp;نأسف بإشعاركم بـخصوص إلغاء [RelatedModuleName].<br></p><p dir="ltr">شكرًا لك.</p><br><p></p> ',
                'notification_type_shortname'=>'events_cancel'
            ),
            array(
                'subject' => '[RelatedModuleName] Rescheduling',
                'body' => '<p dir="ltr" style="text-align: left;"></p><p dir="ltr">Dear [FullName] ,<br></p><p dir="ltr">We would like to inform you about the rescheduling of [RelatedModuleName]. You can display the details from the following link [ProgramLink].<br></p><p dir="ltr">Thanks</p><br><p></p>',
                'arabic_subject' =>'إعادة الجدولة [RelatedModuleName] ',
                'arabic_body' => '<p dir="ltr" style="text-align: left;"></p><p dir="ltr">عزيزي {FullName}،<br></p><p dir="ltr">نود إشعاركم بـخصوص أنه تمت عملية إعاده جدولة [RelatedModuleName] يمكنكم استعراض التفاصيل من خلال الرابط التالي [ProgramLink].</p><p dir="ltr">&nbsp;شكرا</p><br><p></p> ',
                'notification_type_shortname'=>'events_reschedule'
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
        upgrade_plugin_savepoint(true, 2022041700.5, 'local', 'events');
    }
    if ($oldversion < 2022041700.6) {
        $dbman = $DB->get_manager();
        $table = new xmldb_table('local_events');
        $arabicnameforexams = new xmldb_field('titlearabic',XMLDB_TYPE_TEXT,null, null, null, null, null, null);
        if (!$dbman->field_exists($table, $arabicnameforexams)) {
            $dbman->add_field($table, $arabicnameforexams);
        }     
        upgrade_plugin_savepoint(true, 2022041700.6, 'local', 'events'); 
    }
   
    if ($oldversion < 2022041700.64) {
        $DB->set_field('local_events', 'certificate', 0);
        $dbman = $DB->get_manager();
        $table = new xmldb_table('local_events');
        $field = new xmldb_field('certificate',XMLDB_TYPE_INTEGER, '10', null, null, null, '0');
        $dbman->change_field_type($table, $field);

        $table2 = new xmldb_table('local_partners');
        $description = new xmldb_field('description',XMLDB_TYPE_TEXT,null, null, null, null, null, null);
        if (!$dbman->field_exists($table2, $description)) {
            $dbman->add_field($table2, $description);
        }     
       // Main savepoint reached.
        upgrade_plugin_savepoint(true,2022041700.64, 'local', 'events');
    }

    if ($oldversion < 2022041700.68) {
        $dbman = $DB->get_manager();
        $table = new xmldb_table('local_events');
        $field = new xmldb_field('taxfree',XMLDB_TYPE_INTEGER, '10', null, null, null,'0');
         if (!$dbman->field_exists($table, $field)) {
            $dbman->add_field($table, $field);
        }
        upgrade_plugin_savepoint(true, 2022041700.68, 'local', 'events');
    }

   /* if ($oldversion < 2022041700.62) {
        $dbman = $DB->get_manager();
        $table = new xmldb_table('local_partners');
        $description = new xmldb_field('description',XMLDB_TYPE_TEXT,null, null, null, null, null, null);
        if (!$dbman->field_exists($table, $description)) {
            $dbman->add_field($table, $description);
        }     
        upgrade_plugin_savepoint(true, 2022041700.62, 'local', 'events'); 
    }*/

    if ($oldversion < 2022041700.73) {

        $dbman = $DB->get_manager();
        $table = new xmldb_table('local_events');

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

        upgrade_plugin_savepoint(true, 2022041700.73, 'local', 'events');
    }

    if ($oldversion < 2022041700.74) {
        $dbman = $DB->get_manager();
        $table = new xmldb_table('local_events');
        $field = new xmldb_field('teams',XMLDB_TYPE_INTEGER, '10', null, null, null,'0');
         if (!$dbman->field_exists($table, $field)) {
            $dbman->add_field($table, $field);
        }
        upgrade_plugin_savepoint(true, 2022041700.74, 'local', 'events');
    }

    if ($oldversion < 2022041700.75) {
        $dbman = $DB->get_manager();
        $table = new xmldb_table('local_event_attendees');
        $fieldA= new xmldb_field('name',XMLDB_TYPE_CHAR, '255', null, null, null,null);
        $fieldB= new xmldb_field('email',XMLDB_TYPE_CHAR, '255', null, null, null,null);
        if ($dbman->field_exists($table, $fieldA)) {
            $dbman->change_field_type($table, $fieldA);
        }
        if ($dbman->field_exists($table, $fieldB)) {
            $dbman->change_field_type($table, $fieldB);
        }
        upgrade_plugin_savepoint(true, 2022041700.75, 'local', 'events');
    }

    if ($oldversion < 2022041700.8) {
        $dbman = $DB->get_manager();
        $table = new xmldb_table('local_event_attendees');

        $field = new xmldb_field('enrolstatus',XMLDB_TYPE_INTEGER, '10', null, XMLDB_NOTNULL, null, 1);
        if (!$dbman->field_exists($table, $field)) {
            $dbman->add_field($table, $field);
        }
        $field = new xmldb_field('orderid',XMLDB_TYPE_INTEGER, '10', null, XMLDB_NOTNULL, null, 0);
        if (!$dbman->field_exists($table, $field)) {
            $dbman->add_field($table, $field);
        }        
        // Main savepoint reached.
        upgrade_plugin_savepoint(true, 2022041700.8, 'local', 'events');
    }

    if ($oldversion < 2022041700.9) {
        $dbman = $DB->get_manager();
        $table = new xmldb_table('local_event_attendees');

        $field = new xmldb_field('enrolledby',XMLDB_TYPE_INTEGER, '10', null, null, null, '0');
        if (!$dbman->field_exists($table, $field)) {
            $dbman->add_field($table, $field);
        }      
        // Main savepoint reached.
        upgrade_plugin_savepoint(true, 2022041700.9, 'local', 'events');
    } 
    if ($oldversion < 2022041701) {
        $dbman = $DB->get_manager();
        $table = new xmldb_table('local_event_attendees');

        $field = new xmldb_field('organization',XMLDB_TYPE_INTEGER, '10', null, null, null, '0');
        if (!$dbman->field_exists($table, $field)) {
            $dbman->add_field($table, $field);
        }      
        // Main savepoint reached.
        upgrade_plugin_savepoint(true, 2022041701, 'local', 'events');
    }       
    

    if ($oldversion < 2022041701.3) {
        $dbman = $DB->get_manager();
        $tableA = new xmldb_table('local_events');

        $fieldA = new xmldb_field('cancelled',XMLDB_TYPE_INTEGER, '12', null, XMLDB_NOTNULL, null, '0');
        $fieldB = new xmldb_field('cancelledby',XMLDB_TYPE_INTEGER, '12', null, XMLDB_NOTNULL, null, '0');
        $fieldC = new xmldb_field('cancelledate',XMLDB_TYPE_INTEGER, '12', null, XMLDB_NOTNULL, null, '0');

        if (!$dbman->field_exists($tableA, $fieldA)) {
            $dbman->add_field($tableA, $fieldA);
        } 
        if (!$dbman->field_exists($tableA, $fieldB)) {
            $dbman->add_field($tableA, $fieldB);
        } 

        if (!$dbman->field_exists($tableA, $fieldC)) {
            $dbman->add_field($tableA, $fieldC);
        } 

        $table = new xmldb_table('local_event_attendees');

        $field = new xmldb_field('organization',XMLDB_TYPE_INTEGER, '10', null, null, null, '0');
        if (!$dbman->field_exists($table, $field)) {
            $dbman->add_field($table, $field);
        }

        // Main savepoint reached.
        upgrade_plugin_savepoint(true, 2022041701.3, 'local', 'events');
    }


   if ($oldversion < 2022041701.2) {
        $dbman = $DB->get_manager();
        $tableA= new xmldb_table('local_event_attendees');
        $field=  new xmldb_field('realuser', XMLDB_TYPE_INTEGER, '10', null, XMLDB_NOTNULL, null, '0','userid');
        if (!$dbman->field_exists($tableA, $field)) {
            $dbman->add_field($tableA, $field);
        }
        upgrade_plugin_savepoint(true, 2022041701.2, 'local', 'events');
    }

    return true;
}
