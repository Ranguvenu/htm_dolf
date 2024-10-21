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
function xmldb_local_cpd_upgrade($oldversion) {
	global $DB, $CFG;
	$dbman = $DB->get_manager();
    if ($oldversion < 2022041700.18) {

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
            $table->add_field('costcenterid', XMLDB_TYPE_INTEGER, '10', null, XMLDB_NOTNULL, null, null);
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
            $table->add_key('foreign', XMLDB_KEY_FOREIGN, array('costcenterid'));
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
     
        upgrade_plugin_savepoint(true, 2022041700.18, 'local', 'trainingprogram');
    }
    if ($oldversion < 2022041700.26) {
        $dbman = $DB->get_manager();
        $table = new xmldb_table('local_cpd_completion');
        $table->add_field('id', XMLDB_TYPE_INTEGER, '10', null, XMLDB_NOTNULL, XMLDB_SEQUENCE, null);
        $table->add_field('cpdid', XMLDB_TYPE_INTEGER, '10', null, XMLDB_NOTNULL, null, null);
        $table->add_field('userid', XMLDB_TYPE_INTEGER, '10', null, XMLDB_NOTNULL, null, null);
        $table->add_field('evidenceid', XMLDB_TYPE_INTEGER, '10', null, XMLDB_NOTNULL, null, '0');
        $table->add_field('hourcompleted', XMLDB_TYPE_INTEGER, '10', null, XMLDB_NOTNULL, null, '0');
        $table->add_field('status', XMLDB_TYPE_INTEGER, '10', null, XMLDB_NOTNULL, null, '0');
        $table->add_field('completiondate', XMLDB_TYPE_INTEGER, '10', null, null, null, '0');
        $table->add_field('timecreated', XMLDB_TYPE_INTEGER, '10', null, null, null, '0');
        $table->add_field('timemodified', XMLDB_TYPE_INTEGER, '10', null, null, null, '0');
        $table->add_key('primary', XMLDB_KEY_PRIMARY, array('id'));
        if (!$dbman->table_exists($table)) {
            $dbman->create_table($table);
        }
        $table2 = new xmldb_table('local_cpd_hours_log');
        $table2->add_field('id', XMLDB_TYPE_INTEGER, '10', null, XMLDB_NOTNULL, XMLDB_SEQUENCE, null);
        $table2->add_field('cpdid', XMLDB_TYPE_INTEGER, '10', null, XMLDB_NOTNULL, null, null);
        $table2->add_field('userid', XMLDB_TYPE_INTEGER, '10', null, XMLDB_NOTNULL, null, null);
        $table2->add_field('hoursachieved', XMLDB_TYPE_INTEGER, '10', null, XMLDB_NOTNULL, null, '0');
        $table2->add_field('source', XMLDB_TYPE_CHAR, '255', null, XMLDB_NOTNULL, null, null);
        $table2->add_field('dateachieved', XMLDB_TYPE_INTEGER, '10', null, XMLDB_NOTNULL, null, '0');
        $table2->add_field('timecreated', XMLDB_TYPE_INTEGER, '10', null, null, null, '0');
        $table2->add_field('timemodified', XMLDB_TYPE_INTEGER, '10', null, null, null, '0');
        $table2->add_key('primary', XMLDB_KEY_PRIMARY, array('id'));
        if (!$dbman->table_exists($table2)) {
            $dbman->create_table($table2);
        }
        upgrade_plugin_savepoint(true, 2022041700.26, 'local', 'cpd');
    }
    if ($oldversion < 2022041700.27) {
        $table = new xmldb_table('trainingprogram_completion');
        $table->add_field('id', XMLDB_TYPE_INTEGER, '10', null, XMLDB_NOTNULL, XMLDB_SEQUENCE, null);
        $table->add_field('cpdid', XMLDB_TYPE_INTEGER, '10', null, XMLDB_NOTNULL, null, null);
        $table->add_field('programid', XMLDB_TYPE_INTEGER, '10', null, XMLDB_NOTNULL, null, null);
        $table->add_field('userid', XMLDB_TYPE_INTEGER, '10', null, XMLDB_NOTNULL, null, null);
        $table->add_field('hoursachieved', XMLDB_TYPE_INTEGER, '10', null, XMLDB_NOTNULL, null, '0');
        $table->add_field('timecreated', XMLDB_TYPE_INTEGER, '10', null, null, null, '0');
        $table->add_field('timemodified', XMLDB_TYPE_INTEGER, '10', null, null, null, '0');
        $table->add_key('primary', XMLDB_KEY_PRIMARY, array('id'));
        if (!$dbman->table_exists($table)) {
            $dbman->create_table($table);
        }
        upgrade_plugin_savepoint(true, 2022041700.27, 'local', 'cpd');
    }
    if ($oldversion < 2022041700.29) {
        $time = time();
        $notification_info_data = array(
            array(
                'subject' => '[cpd_name] is created',
                'body' => '<p dir="ltr" style="text-align: left;">Dear [cpd_user_fullname],</p><p dir="ltr" style="text-align: left;">&nbsp;The continuous professional development named: [cpd_name] has been successfully created.<br><br></p>',
                'arabic_subject' =>'تم إنشاء [cpd_name]',
                'arabic_body' => '<p dir="ltr" style="text-align: left;">عزيزي [cpd_user_fullname]،</p><p dir="ltr" style="text-align: left;">&nbsp;برنامج التعليم المهني المستمر المسمى: [cpd_name] تم تحديثه بنجاح. شكرًا.<br></p>',
                'notification_type_shortname'=>'cpd_create'
            ),
            array(
                'subject' => '[cpd_name] is Updated',
                'body' => '<p dir="ltr" style="text-align: left;">Dear [cpd_user_fullname],&nbsp;</p><p dir="ltr" style="text-align: left;">The continuous professional development named: [cpd_name] has been successfully updated.<br></p>',
                'arabic_subject' =>'تم تحديث [cpd_name]',
                'arabic_body' => '<p dir="ltr" style="text-align: left;">عزيزي [cpd_user_fullname]، برنامج التعليم المهني المستمر المسمى:[cpd_name]&nbsp;تم ( إنشاؤه - تحديثه) بنجاح. شكرًا.<br></p>',
                'notification_type_shortname'=>'cpd_update'
            ),
            array(
                'subject' => 'Certificate Expiration',
                'body' => '<p dir="ltr" style="text-align: left;">Dear [cpd_user_fullname], The remaining days for [cpd_certificate_name] certificate to expire are less than 180 days.<br></p>',
                'arabic_subject' =>'انتهاء صلاحية الشهادة',
                'arabic_body' => '<p dir="ltr" style="text-align: left;">عزيزي [cpd_user_fullname]، الأيام المتبقية لانتهاء صلاحية شهادة [cpd_certificate_name] هي أقل من 180 يومًا.<br></p>',
                'notification_type_shortname'=>'cpd_expiration_lt_180days_and_gt_90_days'
            ),
            array(
                'subject' => 'Certificate Expiration', 
                'body' => '<p dir="ltr" style="text-align: left;">Dear [cpd_user_fullname], The remaining days for [cpd_certificate_name] certificate to expire are less than 90 days.<br></p>',
                'arabic_subject' =>'انتهاء صلاحية الشهادة',
                'arabic_body' => '<p dir="ltr" style="text-align: left;">عزيزي [cpd_user_fullname]، الأيام المتبقية لانتهاء صلاحية شهادة [cpd_certificate_name] هي أقل من 90 يومًا.<br></p>',
                'notification_type_shortname'=>'cpd_expiration_lt_90_days'
            ),   
            array(
                'subject' => 'Evidence Submitted',
                'body' => '<p dir="ltr" style="text-align: left;">Dear [cpd_user_fullname],</p><p dir="ltr" style="text-align: left;">&nbsp;The evidence for the certificate [cpd_certificate_name] has been successfully submitted.<br></p>',
                'arabic_subject' =>'تم تقديم الاثبات',
                'arabic_body' => '<p dir="ltr" style="text-align: left;">عزيزي [cpd_user_fullname] ،</p><p dir="ltr" style="text-align: left;">&nbsp;لقد تم تقديم الاثبات الخاص بشهادة [cpd_certificate_name] بنجاح.<br></p>',
                'notification_type_shortname'=>'cpd_evidence_submit'
            ),
            array(
                'subject' => ' Evidence Status Update',
                'body' => '<p dir="ltr" style="text-align: left;">Dear [cpd_user_fullname],</p><p dir="ltr" style="text-align: left;">&nbsp;The evidence submitted for the certificate [cpd_certificate_name] has been approved.<br><br></p>',
                'arabic_subject' =>'تحديث حالة الإثبات',
                'arabic_body' => '<p dir="ltr" style="text-align: left;">عزيزي [cpd_user_fullname] ،</p><p dir="ltr" style="text-align: left;">&nbsp;تم اعتماد الاثبات المقدم للشهادة [cpd_certificate_name].<br><br></p>',
                'notification_type_shortname'=>'cpd_evidence_approve'
            ), 
            array(
                'subject' => 'Evidence Status Update',
                'body' => '<p dir="ltr" style="text-align: left;">&nbsp;Dear [cpd_user_fullname],&nbsp;</p><p dir="ltr" style="text-align: left;">The evidence submitted for the certificate [cpd_certificate_name] has been rejected.<br></p>',
                'arabic_subject' =>'تحديث حالة الإثبات',
                'arabic_body' => '<p dir="ltr" style="text-align: left;">عزيزي [cpd_user_fullname]،</p><p dir="ltr" style="text-align: left;">&nbsp;تم رفض الاثبات المقدم للشهادة [cpd_certificate_name].<br></p>',
                'notification_type_shortname'=>'cpd_evidence_reject'
            ),
            array(
                'subject' => 'New program is assigned',
                'body' => '<p dir="ltr" style="text-align: left;"></p><div>&nbsp;Dear [cpd_user_fullname],</div><div><br></div><div>[cpd_programname] program has been assigned to your continuous professional development.&nbsp;</div><div><br></div><div>Thanks.</div><div><br></div><br><p></p>',
                'arabic_subject' =>' تم تعيين برنامج جديد ',
                'arabic_body' => '<p dir="ltr" style="text-align: left;"></p><div>&nbsp;عزيزي [cpd_user_fullname] ،&nbsp;</div><div><br></div><div>تم تعيين برنامج [cpd_programname] لبرنامج التعليم المهني المستمر الخاص بك. شكرًا</div><div><br></div><br><p></p>',
                'notification_type_shortname'=>'cpd_training_program_assign'
            ), 
            array(
                'subject' => '[cpd_programname] program is unassigned',
                'body' => '<p dir="ltr" style="text-align: left;">&nbsp;Dear [cpd_user_fullname],</p><p dir="ltr" style="text-align: left;">[cpd_programname] program has been unassigned from your continuous professional development.&nbsp;</p><p dir="ltr" style="text-align: left;">Thanks<br></p>',
                'arabic_subject' =>' تم إلغاء تعيين برنامج [cpd_programname]',
                'arabic_body' => '<p dir="ltr" style="text-align: left;">عزيزي [cpd_user_fullname],</p><p dir="ltr" style="text-align: left;">تم إلغاء تعيين برنامج [cpd_programname] من برنامج التعليم المهني المستمر الخاص بك. شكرًا<br></p> ',
                'notification_type_shortname'=>'cpd_training_program_unassign'
            ),        
            array(
                'subject' => '[RelatedModuleName] Cancellation ',
                'body' => '<p dir="ltr" style="text-align: left;"></p><p dir="ltr">Dear [FullName] ,</p><p dir="ltr">&nbsp;We are sorry to inform you about the cancellation of [RelatedModuleName].<br></p><p dir="ltr">Thanks</p><br><p></p>',
                'arabic_subject' =>'إلغاء [RelatedModuleName] ',
                'arabic_body' => '<p dir="ltr" style="text-align: left;"></p><p dir="ltr">عزيزي [FullName]،</p><p dir="ltr">&nbsp;نأسف بإشعاركم بـخصوص إلغاء [RelatedModuleName].</p><p dir="ltr">&nbsp;شكرًا لك.</p><br><p></p> ',
                'notification_type_shortname'=>'cpd_cancel'
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
        upgrade_plugin_savepoint(true, 2022041700.29, 'local', 'cpd');
    }
    if ($oldversion < 2022041700.3) {
        $dbman = $DB->get_manager();
        $table = new xmldb_table('local_cpd_training_programs');
        $table->add_field('id', XMLDB_TYPE_INTEGER, '10', null, XMLDB_NOTNULL, XMLDB_SEQUENCE, null);
        $table->add_field('cpdid', XMLDB_TYPE_INTEGER, '10', null, XMLDB_NOTNULL, null, '0');
        $table->add_field('programid', XMLDB_TYPE_INTEGER, '10', null, XMLDB_NOTNULL, null, '0');
        $table->add_field('creditedhours', XMLDB_TYPE_INTEGER, '10', null, XMLDB_NOTNULL, null, '0');
        $table->add_field('timecreated', XMLDB_TYPE_INTEGER, '10', null, null, null, '0');
        $table->add_field('timemodified', XMLDB_TYPE_INTEGER, '10', null, null, null, '0');
        $table->add_key('primary', XMLDB_KEY_PRIMARY, array('id'));
        if (!$dbman->table_exists($table)) {
            $dbman->create_table($table);
        }
        upgrade_plugin_savepoint(true, 2022041700.3, 'local', 'cpd');
    }

    if ($oldversion < 2022041700.31) {
        $dbman = $DB->get_manager();
        $table = new xmldb_table('local_cpd_informal_evidence');
        $field = new xmldb_field('published',XMLDB_TYPE_INTEGER, '10', null, null, null, '0');
        if (!$dbman->field_exists($table, $field)) {
            $dbman->add_field($table, $field);
        }
        $field1 = new xmldb_field('wordcount',XMLDB_TYPE_INTEGER, '10', null, null, null, '0');
        if (!$dbman->field_exists($table, $field1)) {
            $dbman->add_field($table, $field1);
        }
        $field2 = new xmldb_field('pagecount',XMLDB_TYPE_INTEGER, '10', null, null, null, '0');
        if (!$dbman->field_exists($table, $field2)) {
            $dbman->add_field($table, $field2);
        }
        upgrade_plugin_savepoint(true, 2022041700.31, 'local', 'cpd');
    }

    if ($oldversion < 2022041700.36) {
        $dbman = $DB->get_manager();
        $table = new xmldb_table('local_cpd_formal_evidence');
        $field = new xmldb_field('institutelink',XMLDB_TYPE_CHAR, '255', null, null, null, '0');
        if (!$dbman->field_exists($table, $field)) {
            $dbman->add_field($table, $field);
        }
        $field1 = new xmldb_field('attachment',XMLDB_TYPE_CHAR, '255', null, null, null, '0');
        if (!$dbman->field_exists($table, $field1)) {
            $dbman->add_field($table, $field1);
        }
        $field2 = new xmldb_field('comment',XMLDB_TYPE_TEXT, null, null, null, null, '0');
        if (!$dbman->field_exists($table, $field2)) {
            $dbman->add_field($table, $field2);
        }
        upgrade_plugin_savepoint(true, 2022041700.36, 'local', 'cpd');
    }

    if ($oldversion < 2022041700.37) {
        $dbman = $DB->get_manager();
        $table = new xmldb_table('local_cpd_informal_evidence');
        $field = new xmldb_field('institutelink',XMLDB_TYPE_CHAR, '255', null, null, null, '0');
        if (!$dbman->field_exists($table, $field)) {
            $dbman->add_field($table, $field);
        }
        $field1 = new xmldb_field('attachment',XMLDB_TYPE_CHAR, '255', null, null, null, '0');
        if (!$dbman->field_exists($table, $field1)) {
            $dbman->add_field($table, $field1);
        }
        $field2 = new xmldb_field('comment',XMLDB_TYPE_TEXT, null, null, null, null, '0');
        if (!$dbman->field_exists($table, $field2)) {
            $dbman->add_field($table, $field2);
        }
        upgrade_plugin_savepoint(true, 2022041700.37, 'local', 'cpd');
    }
    //	relationtocpd
    if ($oldversion < 2022041700.38) {
        $dbman = $DB->get_manager();
        $table = new xmldb_table('local_cpd_formal_evidence');
        $field = new xmldb_field('relationtocpd',XMLDB_TYPE_TEXT, null, null, null, null, null);
        if (!$dbman->field_exists($table, $field)) {
            $dbman->add_field($table, $field);
        }
        upgrade_plugin_savepoint(true, 2022041700.38, 'local', 'cpd');
    }

    if ($oldversion < 2022041700.39) {
        $dbman = $DB->get_manager();
        $table = new xmldb_table('local_cpd_evidence');
        $field = new xmldb_field('rejectionreason',XMLDB_TYPE_TEXT, null, null, null, null, null);
        if (!$dbman->field_exists($table, $field)) {
            $dbman->add_field($table, $field);
        }
        upgrade_plugin_savepoint(true, 2022041700.39, 'local', 'cpd');
    }

    if ($oldversion < 2022041700.44) {
        $dbman = $DB->get_manager();
        $tableA= new xmldb_table('local_cpd_evidence');
        $tableB= new xmldb_table('local_cpd_completion');
        $tableC= new xmldb_table('local_cpd_hours_log');
        $tableD= new xmldb_table('trainingprogram_completion');
        $field=  new xmldb_field('realuser', XMLDB_TYPE_INTEGER, '10', null, XMLDB_NOTNULL, null, '0','userid');
        
        if (!$dbman->field_exists($tableA, $field)) {
            $dbman->add_field($tableA, $field);
        }

        if (!$dbman->field_exists($tableB, $field)) {
            $dbman->add_field($tableB, $field);
        }

        if (!$dbman->field_exists($tableC, $field)) {
            $dbman->add_field($tableC, $field);
        }

        if (!$dbman->field_exists($tableD, $field)) {
            $dbman->add_field($tableD, $field);
        }

        upgrade_plugin_savepoint(true, 2022041700.44, 'local', 'cpd');
    }
    return true;
}
