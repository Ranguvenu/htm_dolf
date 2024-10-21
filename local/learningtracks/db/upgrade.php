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
function xmldb_local_learningtracks_upgrade($oldversion) {
	global $DB, $CFG;
	$dbman = $DB->get_manager();
    if ($oldversion < 2022050400.12) {
        $table = new xmldb_table('local_learning_items');
        $field = new xmldb_field('itemid');
        $field->set_attributes(XMLDB_TYPE_CHAR, '100', null, null, null, null);
        try {
        $dbman->change_field_type($table, $field);
        } catch (moodle_exception $e) {}
        upgrade_plugin_savepoint(true, 2022050400.12, 'local', 'learningtracks');
    }
    if ($oldversion < 2022050400.14) {
        $dbman = $DB->get_manager();
        $table = new xmldb_table('local_lts_enrolment');
        $table->add_field('id', XMLDB_TYPE_INTEGER, '10', null, XMLDB_NOTNULL, XMLDB_SEQUENCE, null);
        $table->add_field('trackid', XMLDB_TYPE_INTEGER, '10', null, XMLDB_NOTNULL, null, '0');
        $table->add_field('userid', XMLDB_TYPE_INTEGER, '10', null, XMLDB_NOTNULL, null, '0');
        $table->add_field('status', XMLDB_TYPE_INTEGER, '10', null, XMLDB_NOTNULL, null, '0');
        $table->add_field('completiondate', XMLDB_TYPE_INTEGER, '10', null, XMLDB_NOTNULL, null, '0');
        $table->add_field('enrolmentdate', XMLDB_TYPE_INTEGER, '10', null, XMLDB_NOTNULL, null, '0');
        $table->add_field('usercreated', XMLDB_TYPE_INTEGER, '10', null, XMLDB_NOTNULL, null, '0');
        $table->add_field('usermodified', XMLDB_TYPE_INTEGER, '10', null, XMLDB_NOTNULL, null, '0');
        $table->add_field('timecreated', XMLDB_TYPE_INTEGER, '10', null, XMLDB_NOTNULL, null, '0');
        $table->add_field('timemodified', XMLDB_TYPE_INTEGER, '10', null, XMLDB_NOTNULL, null, '0');
        $table->add_key('primary', XMLDB_KEY_PRIMARY, ['id']);
        if (!$dbman->table_exists($table)) {
            $dbman->create_table($table);
        }
        $table2 = new xmldb_table('local_lts_item_enrolment');
        $table2->add_field('id', XMLDB_TYPE_INTEGER, '10', null, XMLDB_NOTNULL, XMLDB_SEQUENCE, null);
        $table2->add_field('trackid', XMLDB_TYPE_INTEGER, '10', null, XMLDB_NOTNULL, null, '0');
        $table2->add_field('itemid', XMLDB_TYPE_INTEGER, '10', null, XMLDB_NOTNULL, null, '0');
        $table2->add_field('userid', XMLDB_TYPE_INTEGER, '10', null, XMLDB_NOTNULL, null, '0');
        $table2->add_field('status', XMLDB_TYPE_INTEGER, '10', null, XMLDB_NOTNULL, null, '0');
        $table2->add_field('completiondate', XMLDB_TYPE_INTEGER, '10', null, XMLDB_NOTNULL, null, '0');
        $table2->add_field('enrolmentdate', XMLDB_TYPE_INTEGER, '10', null, XMLDB_NOTNULL, null, '0');
        $table2->add_field('usercreated', XMLDB_TYPE_INTEGER, '10', null, XMLDB_NOTNULL, null, '0');
        $table2->add_field('usermodified', XMLDB_TYPE_INTEGER, '10', null, XMLDB_NOTNULL, null, '0');
        $table2->add_field('timecreated', XMLDB_TYPE_INTEGER, '10', null, XMLDB_NOTNULL, null, '0');
        $table2->add_field('timemodified', XMLDB_TYPE_INTEGER, '10', null, XMLDB_NOTNULL, null, '0');
        $table2->add_key('primary', XMLDB_KEY_PRIMARY, ['id']);
        if (!$dbman->table_exists($table2)) {
            $dbman->create_table($table2);
        }
        upgrade_plugin_savepoint(true, 2022050400.14, 'local', 'learningtracks');
    }
    if ($oldversion < 2022050400.25) {
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
        $initcontent = array('name' => 'Learning Tracks','shortname' => 'learningtracks','parent_module' => '0','usercreated' => '2','timecreated' => $time,'usermodified' => 2,'timemodified' => NULL, 'pluginname' => 'learningtracks','plugintype' => 'local');
        $parentid = $DB->get_field('local_notification_type', 'id', array('shortname' => 'learningtracks'));
        if(!$parentid){
            $parentid = $DB->insert_record('local_notification_type', $initcontent);
        }
        $notification_type_data = array(
            array('name' => 'Create learning track','shortname' => 'learningtrack_create','parent_module' => $parentid,'usercreated' => '2','timecreated' => $time,'usermodified' => 2,'timemodified' => NULL, 'pluginname' => 'learningtracks','plugintype' => 'local'),
            array('name' => 'Update learning track','shortname' => 'learningtrack_update','parent_module' => $parentid,'usercreated' => '2','timecreated' => $time,'usermodified' => 2,'timemodified' => NULL, 'pluginname' => 'learningtracks','plugintype' => 'local'),
            array('name' => 'Learning track enrollment','shortname' => 'learningtrack_enroll','parent_module' => $parentid,'usercreated' => '2','timecreated' => $time,'usermodified' => 2,'timemodified' => NULL, 'pluginname' => 'learningtracks','plugintype' => 'local'),
            array('name' => 'Learning track complete','shortname' => 'learningtrack_completed','parent_module' => $parentid,'usercreated' => '2','timecreated' => $time,'usermodified' => 2,'timemodified' => NULL, 'pluginname' => 'learningtracks','plugintype' => 'local'),
            array('name' => 'On Change','shortname' => 'learningtrack_onchange','parent_module' => $parentid,'usercreated' => '2','timecreated' => $time,'usermodified' => 2,'timemodified' => NULL, 'pluginname' => 'learningtracks','plugintype' => 'local'),
            array('name' => 'Cancel','shortname' => 'learningtrack_cancel','parent_module' => $parentid,'usercreated' => '2','timecreated' => $time,'usermodified' => 2,'timemodified' => NULL, 'pluginname' => 'learningtracks','plugintype' => 'local'),
            array('name' => 'Re-Schedule','shortname' => 'learningtrack_reschedule','parent_module' => $parentid,'usercreated' => '2','timecreated' => $time,'usermodified' => 2,'timemodified' => NULL, 'pluginname' => 'learningtracks','plugintype' => 'local'),
          
        );
        foreach($notification_type_data as $notification_type){
            unset($notification_type['timecreated']);
            if(!$DB->record_exists('local_notification_type',  $notification_type)){
                $notification_type['timecreated'] = $time;
                $DB->insert_record('local_notification_type', $notification_type);
            }
        }
        upgrade_plugin_savepoint(true, 2022050400.25, 'local', 'learningtracks');
    }

    if ($oldversion < 2022050400.28) {
        $dbman = $DB->get_manager();
        $table = new xmldb_table('local_lts_item_enrolment');
        $field = new xmldb_field('itemtype',XMLDB_TYPE_INTEGER, '10', null, null, null, '0');
        if (!$dbman->field_exists($table, $field)) {
            $dbman->add_field($table, $field);
        }
        upgrade_plugin_savepoint(true, 2022050400.28, 'local', 'learningtracks');
    }
    if ($oldversion < 2022050400.30) {
    $time = time();
    $notification_info_data = array(
        array(
            'subject' => '[learningTrackName] created',
            'body' => '<p dir="ltr" style="text-align: left;"></p><p dir="ltr">Dear [FullName],</p><p dir="ltr">&nbsp;[learningTrackName] learning track has been successfully [created].</p><br><p></p>',
            'arabic_subject' =>'  تحديث - إنشاء [learningTrackName].',
            'arabic_body' => '<p dir="ltr" style="text-align: left;"></p><p dir="ltr">عزيزي [FullName] ،</p><p dir="ltr">&nbsp;لقد تم بنجاح[created] مسار التعلم [learningTrackName].</p><br><p></p>',
            'notification_type_shortname'=>'learningtrack_create'
        ),
        array(
            'subject' => '[learningTrackName] updated',
            'body' => '<p dir="ltr" style="text-align: left;"></p><p dir="ltr">Dear [FullName],</p><p dir="ltr">&nbsp;[learningTrackName] learning track has been successfully [updated].</p><p dir="ltr">Thanks<br></p><br><p></p>',
            'arabic_subject' =>'تحديث [learningTrackName]',
            'arabic_body' => '<p dir="ltr" style="text-align: left;"></p><p dir="ltr">عزيزي [FullName] ، <br></p><p dir="ltr">لقد تم بنجاح [updated] مسار التعلم [learningTrackName].<br></p><br><p></p>',
            'notification_type_shortname'=>'learningtrack_update'
        ),
        array(
            'subject' => 'Enrolment at [learningTrackName]',
            'body' => '<p dir="ltr" style="text-align: left;"></p><p dir="ltr">Dear [FullName],</p><p dir="ltr">We would like to inform you that you are enrolled in [learningTrackName].<br></p><p dir="ltr">Thanks.</p><br><p></p>',
            'arabic_subject' =>'التسجيل في [learningTrackName] ',
            'arabic_body' => '<p dir="ltr" style="text-align: left;"></p><p dir="ltr">عزيزي [FullName]،</p><p dir="ltr">&nbsp;نود إشعاركم&nbsp; بأنه قد تم تسجيلك في مسار&nbsp; [learningTrackName].</p><p dir="ltr">&nbsp;شكرًا.</p><br><p></p>',
            'notification_type_shortname'=>'learningtrack_enroll'
        ),
       
        array(
            'subject' => '[RelatedModuleName] is Update',
            'body' => '<p dir="ltr" style="text-align: left;"></p><p dir="ltr">Dear [FullName],</p><p dir="ltr">&nbsp;We would like to inform you about the new update on [RelatedModuleName]. You can display the details from the following link [RelatedModulesLink].</p><p dir="ltr">Thanks</p><br><p></p>',
            'arabic_subject' =>'تحديث [RelatedModuleName] ',
            'arabic_body' => '<p dir="ltr" style="text-align: left;"></p><p dir="ltr">عزيزي [FullName]،</p><p dir="ltr">&nbsp;نود إشعاركم&nbsp; أنه تم تحديث [RelatedModuleName] يمكنكم استعراض التفاصيل من خلال الرابط التالي [RelatedModulesLink].<br></p><p dir="ltr">&nbsp;شكرا</p><br><p></p>',
            'notification_type_shortname'=>'learningtrack_onchange'
        ),

        array(
            'subject' => '[RelatedModuleName] Cancellation ',
            'body' => '<p dir="ltr" style="text-align: left;"></p><p dir="ltr">Dear [FullName],</p><p dir="ltr">&nbsp; We are sorry to inform you about the cancellation of&nbsp; [RelatedModuleName].</p><p dir="ltr">&nbsp;Thanks</p><br><p></p>',
            'arabic_subject' =>'إلغاء [RelatedModuleName] ',
            'arabic_body' => '<p dir="ltr" style="text-align: left;"></p><p dir="ltr">عزيزي [FullName]،</p><p dir="ltr">&nbsp; نأسف بإشعاركم بـخصوص إلغاء [RelatedModuleName].<br></p><p dir="ltr">شكرًا لك.</p><br><p></p>',
            'notification_type_shortname'=>'learningtrack_cancel'
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
    upgrade_plugin_savepoint(true,2022050400.30, 'local', 'learningtracks');
}
   if ($oldversion < 2022050400.32) {
        $table = new xmldb_table('local_learningtracks');
        $field = new xmldb_field('namearabic');
        $field->set_attributes(XMLDB_TYPE_CHAR, '100', null, null, null, null);

        if (!$dbman->field_exists($table, $field)) {
            $dbman->add_field($table, $field);
        }

        upgrade_plugin_savepoint(true, 2022050400.32, 'local', 'learningtracks');
    }

    if ($oldversion < 2022050400.37) {
        $dbman = $DB->get_manager();
        $tableA= new xmldb_table('local_lts_enrolment');
        $tableB= new xmldb_table('local_lts_item_enrolment');
        $field=  new xmldb_field('realuser', XMLDB_TYPE_INTEGER, '10', null, XMLDB_NOTNULL, null, '0','userid');
        
        if (!$dbman->field_exists($tableA, $field)) {
            $dbman->add_field($tableA, $field);
        }

        if (!$dbman->field_exists($tableB, $field)) {
            $dbman->add_field($tableB, $field);
        }
        
        upgrade_plugin_savepoint(true, 2022050400.37, 'local', 'learningtracks');
    }


    return true;
}
