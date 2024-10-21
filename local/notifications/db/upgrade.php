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

/**
 * notifications Upgrade
 *
 * @package     local_notifications
 *
 */
defined('MOODLE_INTERNAL') || die();

function xmldb_local_notifications_upgrade($oldversion) {
    global $DB, $CFG;
    $dbman = $DB->get_manager();
    if ($oldversion < 2017111300) {
        $table = new xmldb_table('local_notification_info');
        $field = new xmldb_field('adminbody',XMLDB_TYPE_TEXT, 'big', null, null, null,null);
        if (!$dbman->field_exists($table, $field)) {
            $dbman->add_field($table, $field);
        }
        upgrade_plugin_savepoint(true, 2017111300, 'local', 'notifications');
    }
    if ($oldversion < 2017111301) {
        $table = new xmldb_table('local_notification_info');
        $field = new xmldb_field('moduletype', XMLDB_TYPE_CHAR, '250', null, null, null,null, null);
        if (!$dbman->field_exists($table, $field)) {
            $dbman->add_field($table, $field);
        }
        $field = new xmldb_field('moduleid', XMLDB_TYPE_TEXT, 'big', null, null, null,null, null);
        if (!$dbman->field_exists($table, $field)) {
            $dbman->add_field($table, $field);
        }
        upgrade_plugin_savepoint(true, 2017111301, 'local', 'notifications');
    }
    if ($oldversion < 2017111305.01) {
        $table = new xmldb_table('local_notification_info');
        $field = new xmldb_field('completiondays', XMLDB_TYPE_INTEGER, '10', null, null, null,0, null);
        if(!$dbman->field_exists($table,  $field)){
            $dbman->add_field($table,  $field);
        }
        upgrade_plugin_savepoint(true, 2017111305.01, 'local', 'notifications');
    }
    if ($oldversion < 2017111305.04) {
        $table = new xmldb_table('local_notification_info');
        if ($dbman->table_exists($table)) {
            $field = new xmldb_field('attach_certificate', XMLDB_TYPE_INTEGER, '10', null, null, null, 0);
            if(!$dbman->field_exists($table, $field)){
                $dbman->add_field($table, $field);
            }
            upgrade_plugin_savepoint(true, 2017111305.04, 'local', 'notifications');
        }
    }
    if ($oldversion < 2017111305.06) {
        $table = new xmldb_table('local_notification_info');
        if ($dbman->table_exists($table)) {

            $field = new xmldb_field('arabic_subject', XMLDB_TYPE_CHAR, '250', null, null, null,null, null);
            if(!$dbman->field_exists($table, $field)){
                $dbman->add_field($table, $field);
            }

            $field = new xmldb_field('arabic_body', XMLDB_TYPE_TEXT, 'big', null, null, null,null, null);
            if(!$dbman->field_exists($table, $field)){
                $dbman->add_field($table, $field);
            }
        }

        $table = new xmldb_table('local_emaillogs');
        if ($dbman->table_exists($table)) {

            $field = new xmldb_field('arabic_subject', XMLDB_TYPE_CHAR, '250', null, null, null,null, null);
            if(!$dbman->field_exists($table, $field)){
                $dbman->add_field($table, $field);
            }

            $field = new xmldb_field('arabic_body', XMLDB_TYPE_TEXT, 'big', null, null, null,null, null);
            if(!$dbman->field_exists($table, $field)){
                $dbman->add_field($table, $field);
            }
        }

        upgrade_plugin_savepoint(true, 2017111305.06, 'local', 'notifications');
    }
    if ($oldversion < 2017111305.11) {
        $table = new xmldb_table('local_notification_info');
        if ($dbman->table_exists($table)) {

            $field = new xmldb_field('sendsms', XMLDB_TYPE_INTEGER, '10', null, null, null, 0);
            if(!$dbman->field_exists($table, $field)){
                $dbman->add_field($table, $field);
            }
        }

        $table = new xmldb_table('local_emaillogs');
        if ($dbman->table_exists($table)) {

            $field = new xmldb_field('sendsms', XMLDB_TYPE_INTEGER, '10', null, null, null, 0);
            if(!$dbman->field_exists($table, $field)){
                $dbman->add_field($table, $field);
            }

        }

        upgrade_plugin_savepoint(true,2017111305.11, 'local', 'notifications');
    }
    if ($oldversion < 2017111305.12) {

        $table = new xmldb_table('local_smslogs');

        if (!$dbman->table_exists($table)) {

            $table->add_field('id', XMLDB_TYPE_INTEGER, '10', null, XMLDB_NOTNULL, XMLDB_SEQUENCE, null);
            $table->add_field('notification_infoid', XMLDB_TYPE_INTEGER, '10', null, XMLDB_NOTNULL, null, null);
            $table->add_field('from_userid', XMLDB_TYPE_INTEGER, '10', null, XMLDB_NOTNULL, null, null);
            $table->add_field('to_userid', XMLDB_TYPE_INTEGER, '10', null, XMLDB_NOTNULL, null, null);
            
            $table->add_field('to_phonenumber', XMLDB_TYPE_CHAR, '255', null, XMLDB_NOTNULL, null, null);

            $table->add_field('english_smstext', XMLDB_TYPE_TEXT, null, null, XMLDB_NOTNULL, null, '0');
            
            $table->add_field('arabic_smstext', XMLDB_TYPE_TEXT, null, null, XMLDB_NOTNULL, null, '0');

            $table->add_field('status', XMLDB_TYPE_INTEGER, 10, null, null, null, '0');

            $table->add_field('usercreated', XMLDB_TYPE_INTEGER, '10', null, XMLDB_NOTNULL, null, '0');
            $table->add_field('timecreated', XMLDB_TYPE_INTEGER, '10', null, null, null, '0');

            $table->add_field('usermodified', XMLDB_TYPE_INTEGER, '10', null, XMLDB_NOTNULL, null, '0');
            $table->add_field('timemodified', XMLDB_TYPE_INTEGER, '10', null, null, null, '0');

            $table->add_field('response_result', XMLDB_TYPE_TEXT, null, null, XMLDB_NOTNULL, null, '0');

            $table->add_field('sent_date', XMLDB_TYPE_INTEGER, '10', null, null, null, '0');

            $table->add_field('sent_by', XMLDB_TYPE_INTEGER, '10', null, null, null, '0');

            $table->add_key('primary', XMLDB_KEY_PRIMARY, array('id'));
            $result = $dbman->create_table($table);
        }

        upgrade_plugin_savepoint(true,2017111305.12, 'local', 'notifications');
    }
    if ($oldversion < 2017111300) {
            foreach($notification_type_data as $notification_type){
                unset($notification_type['timecreated']);

                $DB->get_field('local_notification_type','id',  array());
                if(!$DB->record_exists('local_notification_type',  $notification_type)){
                    $notification_type['timecreated'] = $time;
                    $DB->insert_record('local_notification_type', $notification_type);
                }
            }
    }
  if ($oldversion < 2017111305.13) {
        $table = new xmldb_table('local_notification_type');
        $field = new xmldb_field('arabicname', XMLDB_TYPE_CHAR, '255', null, XMLDB_NOTNULL, null, '0');
        if (!$dbman->field_exists($table, $field)) {
            $dbman->add_field($table, $field);
        }
        $time = time();
        $initcontent = array('name' => 'Organizations','arabicname' => 'إشعارات الجهات','shortname' => 'organizations','parent_module' => '0','usercreated' => '2','timecreated' => $time,'usermodified' => 2,'timemodified' => NULL, 'pluginname' => 'organization','plugintype' => 'local');
        $parentid = $DB->get_field('local_notification_type', 'id', array('shortname' => 'organizations'));
        if($parentid){

            $notificationtype=new stdClass();
            $notificationtype->id=$parentid;
            $notificationtype->arabicname=$initcontent['arabicname'];
            $parentid = $DB->update_record('local_notification_type', $notificationtype);
        }
        $notification_type_data = array(
            array('name' => 'Organization Registration','arabicname' => ' تسجيل الجهة','shortname' => 'organization_registration','parent_module' => $parentid,'usercreated' => '2','timecreated' => $time,'usermodified' => 2,'timemodified' => NULL, 'pluginname' => 'organization','plugintype' => 'local'),
            array('name' => 'Assigning Official','arabicname' => 'تعيين مسؤول جهة','shortname' => 'organization_assigning_official','parent_module' => $parentid,'usercreated' => '2','timecreated' => $time,'usermodified' => 2,'timemodified' => NULL, 'pluginname' => 'organization','plugintype' => 'local'),
            array('name' => 'Assigning Trainee','arabicname' => 'تعيين متدرب','shortname' => 'organization_assigning_trainee','parent_module' => $parentid,'usercreated' => '2','timecreated' => $time,'usermodified' => 2,'timemodified' => NULL, 'pluginname' => 'organization','plugintype' => 'local'),
            array('name' => 'Enrollment','arabicname' => 'التسجيل للجهات','shortname' => 'organization_enrollment','parent_module' => $parentid,'usercreated' => '2','timecreated' => $time,'usermodified' => 2,'timemodified' => NULL, 'pluginname' => 'organization','plugintype' => 'local'),
           array('name' => 'Wallet Update','arabicname' => ' تحديث المحفظة','shortname' => 'organization_wallet_update','parent_module' => $parentid,'usercreated' => '2','timecreated' => $time,'usermodified' => 2,'timemodified' => NULL, 'pluginname' => 'organization','plugintype' => 'local'),
           
        );
        foreach($notification_type_data as $notification_type){
            $id=$DB->get_field('local_notification_type','id',  array('shortname'=>$notification_type['shortname']));
            if($id){
                $notificationtype=new stdClass();
                 $notificationtype->id=$id;
                 $notificationtype->arabicname=$notification_type['arabicname'];
                $DB->update_record('local_notification_type', $notificationtype);
             }
         }

 $time = time();
    $initcontent = array('name' => 'Traning Program','arabicname' => 'إشعارات البرنامج التدريبي','shortname' => 'trainingprogram','parent_module' => '0','usercreated' => '2','timecreated' => $time,'usermodified' => 2,'timemodified' => NULL, 'pluginname' => 'trainingprogram');
    $parentid = $DB->get_field('local_notification_type', 'id', array('shortname' => 'trainingprogram'));
    if($parentid){
       $notificationtype=new stdClass();
            $notificationtype->id=$parentid;
            $notificationtype->arabicname=$initcontent['arabicname'];
            $parentid = $DB->update_record('local_notification_type', $notificationtype);
    }
    $notification_type_data = array(
        array('name' => 'Create traning program','arabicname' => 'إنشاء برنامج تدريب','shortname' => 'trainingprogram_create','parent_module' => $parentid,'usercreated' => '2','timecreated' => $time,'usermodified' => 2,'timemodified' => NULL, 'pluginname' => 'trainingprogram','plugintype' => 'local'),
        array('name' => 'Update traning program','arabicname' => ' تحديث برنامج التدريب','shortname' => 'trainingprogram_update','parent_module' => $parentid,'usercreated' => '2','timecreated' => $time,'usermodified' => 2,'timemodified' => NULL, 'pluginname' => 'trainingprogram','plugintype' => 'local'),
        array('name' => 'Enrollment','arabicname' => ' التسجيل في البرنامج','shortname' => 'trainingprogram_enroll','parent_module' => $parentid,'usercreated' => '2','timecreated' => $time,'usermodified' => 2,'timemodified' => NULL, 'pluginname' => 'trainingprogram','plugintype' => 'local'),
        array('name' => 'Completion','arabicname' => ' إكمال البرنامج','shortname' => 'trainingprogram_completion','parent_module' => $parentid,'usercreated' => '2','timecreated' => $time,'usermodified' => 2,'timemodified' => NULL, 'pluginname' => 'trainingprogram','plugintype' => 'local'),
        array('name' => 'Certificate Assignment','arabicname' => ' إحالة الشهادة','shortname' => 'trainingprogram_certificate_assignment','parent_module' => $parentid,'usercreated' => '2','timecreated' => $time,'usermodified' => 2,'timemodified' => NULL, 'pluginname' => 'trainingprogram','plugintype' => 'local'),
        array('name' => 'Before 7 days','arabicname' => ' تذكير قبل 7 أيام','shortname' => 'trainingprogram_before_7_days','parent_module' => $parentid,'usercreated' => '2','timecreated' => $time,'usermodified' => 2,'timemodified' => NULL, 'pluginname' => 'trainingprogram','plugintype' => 'local'),
        array('name' => 'Before 48 Hours','arabicname' => 'تذكير قبل 48 ساعة','shortname' => 'trainingprogram_before_48_hours','parent_module' => $parentid,'usercreated' => '2','timecreated' => $time,'usermodified' => 2,'timemodified' => NULL, 'pluginname' => 'trainingprogram','plugintype' => 'local'),
        array('name' => 'Before 24 Hours','arabicname' => ' تذكير قبل 24 ساعة','shortname' => 'trainingprogram_before_24_hours','parent_module' => $parentid,'usercreated' => '2','timecreated' => $time,'usermodified' => 2,'timemodified' => NULL, 'pluginname' => 'trainingprogram','plugintype' => 'local'),
        array('name' => 'After Session','arabicname' => ' بعد الجلسة','shortname' => 'trainingprogram_after_session','parent_module' => $parentid,'usercreated' => '2','timecreated' => $time,'usermodified' => 2,'timemodified' => NULL, 'pluginname' => 'trainingprogram','plugintype' => 'local'),
        array('name' => 'Send Conclusion','arabicname' => 'إرسال الخاتمة','shortname' => 'trainingprogram_send_conclusion','parent_module' => $parentid,'usercreated' => '2','timecreated' => $time,'usermodified' => 2,'timemodified' => NULL, 'pluginname' => 'trainingprogram','plugintype' => 'local'),
        array('name' => 'Session Before 30 Minutes','arabicname' => 'تذكير الجلسة قبل 30 دقيقة','shortname' => 'trainingprogram_before_30_minutes','parent_module' => $parentid,'usercreated' => '2','timecreated' => $time,'usermodified' => 2,'timemodified' => NULL, 'pluginname' => 'trainingprogram','plugintype' => 'local'),  
        array('name' => 'Enrolled Inactive Accounts For 2 Days','arabicname' => 'تذكير الحسابات المسجلين الغير نشطة لمدة يومين','shortname' => 'trainingprogram_enrolled_inactive_accounts','parent_module' => $parentid,'usercreated' => '2','timecreated' => $time,'usermodified' => 2,'timemodified' => NULL, 'pluginname' => 'trainingprogram','plugintype' => 'local'),  
        array('name' => 'Pre Assessment Open','arabicname' => ' بفتح الاختبار القبلي','shortname' => 'trainingprogram_pre_assessment_opened','parent_module' => $parentid,'usercreated' => '2','timecreated' => $time,'usermodified' => 2,'timemodified' => NULL, 'pluginname' => 'trainingprogram','plugintype' => 'local'),  
        array('name' => 'Post Assessment Open','arabicname' => ' بفتح الاختبار البعدي','shortname' => 'trainingprogram_post_assessment_opened','parent_module' => $parentid,'usercreated' => '2','timecreated' => $time,'usermodified' => 2,'timemodified' => NULL, 'pluginname' => 'trainingprogram','plugintype' => 'local'),  
        array('name' => 'Pre Assessment Closed','arabicname' => 'تم إغلاق الاختبار القبلي','shortname' => 'trainingprogram_pre_assessment_closed','parent_module' => $parentid,'usercreated' => '2','timecreated' => $time,'usermodified' => 2,'timemodified' => NULL, 'pluginname' => 'trainingprogram','plugintype' => 'local'),  
        array('name' => 'Post Assessment Closed','arabicname' => 'تم إغلاق الاختبار البعدي','shortname' => 'trainingprogram_post_assessment_closed','parent_module' => $parentid,'usercreated' => '2','timecreated' => $time,'usermodified' => 2,'timemodified' => NULL, 'pluginname' => 'trainingprogram','plugintype' => 'local'),  
        array('name' => 'Assignment Deadline 4 Hours','arabicname' => ' تذكير الموعد النهائي للتكليف 4 ساعات','shortname' => 'trainingprogram_assignment_deadline_4_hours','parent_module' => $parentid,'usercreated' => '2','timecreated' => $time,'usermodified' => 2,'timemodified' => NULL, 'pluginname' => 'trainingprogram','plugintype' => 'local'),  
        array('name' => 'Assignment Deadline 24 Hours','arabicname' => 'تذكير الموعد النهائي للتكليف 24 ساعات','shortname' => 'trainingprogram_assignment_deadline_24_hours','parent_module' => $parentid,'usercreated' => '2','timecreated' => $time,'usermodified' => 2,'timemodified' => NULL, 'pluginname' => 'trainingprogram','plugintype' => 'local'), 
        
    );
   foreach($notification_type_data as $notification_type){
            $id=$DB->get_field('local_notification_type','id',  array('shortname'=>$notification_type['shortname']));
            if($id){
                $notificationtype=new stdClass();
                 $notificationtype->id=$id;
                 $notificationtype->arabicname=$notification_type['arabicname'];
                $DB->update_record('local_notification_type', $notificationtype);
             }
         }

$time = time();
    $initcontent = array('name' => 'Questionbank','arabicname' => 'إشعارات بنك الأسئلة','shortname' => 'questionbank','parent_module' => '0','usercreated' => '2','timecreated' => $time,'usermodified' => 2,'timemodified' => NULL, 'pluginname' => 'questionbank','plugintype' => 'local');
    $parentid = $DB->get_field('local_notification_type', 'id', array('shortname' => 'questionbank'));
    if($parentid){
             $notificationtype=new stdClass();
            $notificationtype->id=$parentid;
            $notificationtype->arabicname=$initcontent['arabicname'];
            $parentid = $DB->update_record('local_notification_type', $notificationtype);
    }
    $notification_type_data = array(
        array('name' => 'Workshop created','arabicname' => 'إنشاء ورشة العمل','shortname' => 'questionbank_workshop_created','parent_module' => $parentid,'usercreated' => '2','timecreated' => $time,'usermodified' => 2,'timemodified' => NULL, 'pluginname' => 'questionbank','plugintype' => 'local'),
        array('name' => 'Workshop updated','arabicname' => 'تحديث ورشة العمل','shortname' => 'questionbank_workshop_updated','parent_module' => $parentid,'usercreated' => '2','timecreated' => $time,'usermodified' => 2,'timemodified' => NULL, 'pluginname' => 'questionbank','plugintype' => 'local'),
        array('name' => 'Assign expert','arabicname' => 'تعيين خبير','shortname' => 'questionbank_assign_expert','parent_module' => $parentid,'usercreated' => '2','timecreated' => $time,'usermodified' => 2,'timemodified' => NULL, 'pluginname' => 'questionbank','plugintype' => 'local'),
        array('name' => 'Assign exam official','arabicname' => 'تعيين مسؤول الاختبار','shortname' => 'questionbank_assign_exam_official','parent_module' => $parentid,'usercreated' => '2','timecreated' => $time,'usermodified' => 2,'timemodified' => NULL, 'pluginname' => 'questionbank','plugintype' => 'local'),
        array('name' => 'Assign reviewer','arabicname' => 'تعيين مراجع','shortname' => 'questionbank_assign_reviewer','parent_module' => $parentid,'usercreated' => '2','timecreated' => $time,'usermodified' => 2,'timemodified' => NULL, 'pluginname' => 'questionbank','plugintype' => 'local'),
        array('name' => 'Question under review','arabicname' => ' السؤال قيد المراجعة','shortname' => 'questionbank_question_under_review','parent_module' => $parentid,'usercreated' => '2','timecreated' => $time,'usermodified' => 2,'timemodified' => NULL, 'pluginname' => 'questionbank','plugintype' => 'local'),
        array('name' => 'Question reviewed','arabicname' => ' تم مراجعة السؤال','shortname' => 'questionbank_question_reviewed','parent_module' => $parentid,'usercreated' => '2','timecreated' => $time,'usermodified' => 2,'timemodified' => NULL, 'pluginname' => 'questionbank','plugintype' => 'local'),
        array('name' => 'Adding question to questionbank','arabicname' => ' إضافة سؤال إلى بنك الاسئلة','shortname' => 'questionbank_question_added','parent_module' => $parentid,'usercreated' => '2','timecreated' => $time,'usermodified' => 2,'timemodified' => NULL, 'pluginname' => 'questionbank','plugintype' => 'local'),
        array('name' => 'On Change','arabicname' => ' عند التغيير','shortname' => 'questionbank_onchange','parent_module' => $parentid,'usercreated' => '2','timecreated' => $time,'usermodified' => 2,'timemodified' => NULL, 'pluginname' => 'questionbank','plugintype' => 'local'),
        array('name' => 'Cancel','arabicname' => 'إلغاء','shortname' => 'questionbank_cancel','parent_module' => $parentid,'usercreated' => '2','timecreated' => $time,'usermodified' => 2,'timemodified' => NULL, 'pluginname' => 'questionbank','plugintype' => 'local'),
        array('name' => 'Re-Schedule','arabicname' => ' إعادة الجدولة','shortname' => 'questionbank_reschedule','parent_module' => $parentid,'usercreated' => '2','timecreated' => $time,'usermodified' => 2,'timemodified' => NULL, 'pluginname' => 'questionbank','plugintype' => 'local'),
      
    );
      foreach($notification_type_data as $notification_type){
            $id=$DB->get_field('local_notification_type','id',  array('shortname'=>$notification_type['shortname']));
            if($id){
                $notificationtype=new stdClass();
                 $notificationtype->id=$id;
                 $notificationtype->arabicname=$notification_type['arabicname'];
                $DB->update_record('local_notification_type', $notificationtype);
             }
         }


    $time = time();
    $initcontent = array('name' => 'User approval','arabicname' => 'إشعارات الموافقة على المستخدمين','shortname' => 'userapproval','parent_module' => '0','usercreated' => '2','timecreated' => $time,'usermodified' => 2,'timemodified' => NULL, 'pluginname' => 'userapproval','plugintype' => 'local');
    $parentid = $DB->get_field('local_notification_type', 'id', array('shortname' => 'userapproval'));
    if($parentid){
          $notificationtype=new stdClass();
            $notificationtype->id=$parentid;
            $notificationtype->arabicname=$initcontent['arabicname'];
            $parentid = $DB->update_record('local_notification_type', $notificationtype);
    }
    $notification_type_data = array(
        array('name' => 'Registration','arabicname' => 'التسجيل','shortname' => 'registration','parent_module' => $parentid,'usercreated' => '2','timecreated' => $time,'usermodified' => 2,'timemodified' => NULL, 'pluginname' => 'userapproval','plugintype' => 'local'),
        array('name' => 'Approve','arabicname' => 'الموافقة على مستخدم','shortname' => 'approve','parent_module' => $parentid,'usercreated' => '2','timecreated' => $time,'usermodified' => 2,'timemodified' => NULL, 'pluginname' => 'userapproval','plugintype' => 'local'),
        array('name' => 'Reject','arabicname' => 'رفض مستخدم','shortname' => 'reject','parent_module' => $parentid,'usercreated' => '2','timecreated' => $time,'usermodified' => 2,'timemodified' => NULL, 'pluginname' => 'userapproval','plugintype' => 'local'),
        array('name' => 'Organization Approval','arabicname' => 'موافقة للجهة','shortname' => 'organizational_approval','parent_module' => $parentid,'usercreated' => '2','timecreated' => $time,'usermodified' => 2,'timemodified' => NULL, 'pluginname' => 'userapproval','plugintype' => 'local'),
       //array('name' => 'Update Hall','shortname' => 'update_trainingprogram','parent_module' => $parentid,'usercreated' => '2','timecreated' => $time,'usermodified' => 2,'timemodified' => NULL, 'pluginname' => 'trainingprogram','plugintype' => 'local'),

    );
         foreach($notification_type_data as $notification_type){
            $id=$DB->get_field('local_notification_type','id',  array('shortname'=>$notification_type['shortname']));
            if($id){
                $notificationtype=new stdClass();
                 $notificationtype->id=$id;
                 $notificationtype->arabicname=$notification_type['arabicname'];
                $DB->update_record('local_notification_type', $notificationtype);
             }
         }
          $time = time();
    $initcontent = array('name' => 'Learning Tracks','arabicname' => 'إشعارات مسارات التعلم','shortname' => 'learningtracks','parent_module' => '0','usercreated' => '2','timecreated' => $time,'usermodified' => 2,'timemodified' => NULL, 'pluginname' => 'learningtracks','plugintype' => 'local');
    $parentid = $DB->get_field('local_notification_type', 'id', array('shortname' => 'learningtracks'));
    if($parentid){
  $notificationtype=new stdClass();
            $notificationtype->id=$parentid;
            $notificationtype->arabicname=$initcontent['arabicname'];
            $parentid = $DB->update_record('local_notification_type', $notificationtype);
    }
    $notification_type_data = array(
        array('name' => 'Create learning track','arabicname' => 'إنشاء مسار التعلم','shortname' => 'learningtrack_create','parent_module' => $parentid,'usercreated' => '2','timecreated' => $time,'usermodified' => 2,'timemodified' => NULL, 'pluginname' => 'learningtracks','plugintype' => 'local'),
        array('name' => 'Update learning track','arabicname' => ' تحديث مسار التعلم','shortname' => 'learningtrack_update','parent_module' => $parentid,'usercreated' => '2','timecreated' => $time,'usermodified' => 2,'timemodified' => NULL, 'pluginname' => 'learningtracks','plugintype' => 'local'),
        array('name' => 'Learning track enrollment','arabicname' => 'التسجيل في مسار التعلم','shortname' => 'learningtrack_enroll','parent_module' => $parentid,'usercreated' => '2','timecreated' => $time,'usermodified' => 2,'timemodified' => NULL, 'pluginname' => 'learningtracks','plugintype' => 'local'),
        array('name' => 'Learning track complete','arabicname' => 'إكمال مسار التعلم','shortname' => 'learningtrack_completed','parent_module' => $parentid,'usercreated' => '2','timecreated' => $time,'usermodified' => 2,'timemodified' => NULL, 'pluginname' => 'learningtracks','plugintype' => 'local'),
        array('name' => 'On Change','arabicname' => 'عند التغيير','shortname' => 'learningtrack_onchange','parent_module' => $parentid,'usercreated' => '2','timecreated' => $time,'usermodified' => 2,'timemodified' => NULL, 'pluginname' => 'learningtracks','plugintype' => 'local'),
        array('name' => 'Cancel','arabicname' => ' إلغاء','shortname' => 'learningtrack_cancel','parent_module' => $parentid,'usercreated' => '2','timecreated' => $time,'usermodified' => 2,'timemodified' => NULL, 'pluginname' => 'learningtracks','plugintype' => 'local'),
        array('name' => 'Re-Schedule','arabicname' => ' إعادة الجدولة','shortname' => 'learningtrack_reschedule','parent_module' => $parentid,'usercreated' => '2','timecreated' => $time,'usermodified' => 2,'timemodified' => NULL, 'pluginname' => 'learningtracks','plugintype' => 'local'),
      
    );
           foreach($notification_type_data as $notification_type){
            $id=$DB->get_field('local_notification_type','id',  array('shortname'=>$notification_type['shortname']));
            if($id){
                $notificationtype=new stdClass();
                 $notificationtype->id=$id;
                 $notificationtype->arabicname=$notification_type['arabicname'];
                $DB->update_record('local_notification_type', $notificationtype);
             }
         }

         $time = time();
    $initcontent = array('name' => 'Hall','arabicname' => 'إشعارات القاعات','shortname' => 'hall','parent_module' => '0','usercreated' => '2','timecreated' => $time,'usermodified' => 2,'timemodified' => NULL, 'pluginname' => 'hall','plugintype' => 'local');
    $parentid = $DB->get_field('local_notification_type', 'id', array('shortname' => 'hall'));
    if($parentid){
         $notificationtype=new stdClass();
            $notificationtype->id=$parentid;
            $notificationtype->arabicname=$initcontent['arabicname'];
            $parentid = $DB->update_record('local_notification_type', $notificationtype);
    }
    $notification_type_data = array(
        array('name' => 'Hall Reservation','arabicname' => 'حجز القاعة','shortname' => 'hall_reservation','parent_module' => $parentid,'usercreated' => '2','timecreated' => $time,'usermodified' => 2,'timemodified' => NULL, 'pluginname' => 'hall','plugintype' => 'local'),
       //array('name' => 'Update Hall','shortname' => 'update_trainingprogram','parent_module' => $parentid,'usercreated' => '2','timecreated' => $time,'usermodified' => 2,'timemodified' => NULL, 'pluginname' => 'trainingprogram','plugintype' => 'local'),

    );
      foreach($notification_type_data as $notification_type){
            $id=$DB->get_field('local_notification_type','id',  array('shortname'=>$notification_type['shortname']));
            if($id){
                $notificationtype=new stdClass();
                 $notificationtype->id=$id;
                 $notificationtype->arabicname=$notification_type['arabicname'];
                $DB->update_record('local_notification_type', $notificationtype);
             }
         }
           $time = time();
    $initcontent = array('name' => 'Exams','arabicname' => 'إشعارات الاختبار','shortname' => 'exams','parent_module' => '0','usercreated' => '2','timecreated' => $time,'usermodified' => 2,'timemodified' => NULL, 'pluginname' => 'exams');
    $parentid = $DB->get_field('local_notification_type', 'id', array('shortname' => 'exams'));
    if($parentid){
        $notificationtype=new stdClass();
            $notificationtype->id=$parentid;
            $notificationtype->arabicname=$initcontent['arabicname'];
            $parentid = $DB->update_record('local_notification_type', $notificationtype);
    }
    $notification_type_data = array(
        array('name' => 'Create Exam','arabicname' => ' إنشاء الاختبار','shortname' => 'exams_create','parent_module' => $parentid,'usercreated' => '2','timecreated' => $time,'usermodified' => 2,'timemodified' => NULL, 'pluginname' => 'exams','plugintype' => 'local'),
       array('name' => 'Update Exam','arabicname' => ' تحديث الاختبار','shortname' => 'exams_update','parent_module' => $parentid,'usercreated' => '2','timecreated' => $time,'usermodified' => 2,'timemodified' => NULL, 'pluginname' => 'exams','plugintype' => 'local'),
       array('name' => 'Enrolment','arabicname' => ' التسجيل','shortname' => 'exams_enrolment','parent_module' => $parentid,'usercreated' => '2','timecreated' => $time,'usermodified' => 2,'timemodified' => NULL, 'pluginname' => 'exams','plugintype' => 'local'),
       array('name' => 'Completion','arabicname' => ' إكمال','shortname' => 'exams_completion','parent_module' => $parentid,'usercreated' => '2','timecreated' => $time,'usermodified' => 2,'timemodified' => NULL, 'pluginname' => 'exams','plugintype' => 'local'),
       array('name' => 'Certificate Assignment','arabicname' => ' إحالة الشهادة','shortname' => 'exams_certificate_assignment','parent_module' => $parentid,'usercreated' => '2','timecreated' => $time,'usermodified' => 2,'timemodified' => NULL, 'pluginname' => 'exams','plugintype' => 'local'),
       array('name' => 'Before 7 days','arabicname' => ' تذكير قبل 7 أيام','shortname' => 'exams_before_7_days','parent_module' => $parentid,'usercreated' => '2','timecreated' => $time,'usermodified' => 2,'timemodified' => NULL, 'pluginname' => 'exams','plugintype' => 'local'),
       array('name' => 'Before 48 Hours','arabicname' => 'تذكير قبل 48 ساعة','shortname' => 'exams_before_48_hours','parent_module' => $parentid,'usercreated' => '2','timecreated' => $time,'usermodified' => 2,'timemodified' => NULL, 'pluginname' => 'exams','plugintype' => 'local'),       
       array('name' => 'Before 24 Hours','arabicname' => 'تذكير قبل 24 ساعة','shortname' => 'exams_before_24_hours','parent_module' => $parentid,'usercreated' => '2','timecreated' => $time,'usermodified' => 2,'timemodified' => NULL, 'pluginname' => 'exams','plugintype' => 'local'),
       array('name' => 'Send Conclusion','arabicname' => 'إرسال الخاتمة','shortname' => 'exams_send_conclusion','parent_module' => $parentid,'usercreated' => '2','timecreated' => $time,'usermodified' => 2,'timemodified' => NULL, 'pluginname' => 'exams','plugintype' => 'local'),
       array('name' => 'After Session','arabicname' => ' بعد الجلسة','shortname' => 'exams_after_session','parent_module' => $parentid,'usercreated' => '2','timecreated' => $time,'usermodified' => 2,'timemodified' => NULL, 'pluginname' => 'exams','plugintype' => 'local')
       
    );
       foreach($notification_type_data as $notification_type){
            $id=$DB->get_field('local_notification_type','id',  array('shortname'=>$notification_type['shortname']));
            if($id){
                $notificationtype=new stdClass();
                 $notificationtype->id=$id;
                 $notificationtype->arabicname=$notification_type['arabicname'];
                $DB->update_record('local_notification_type', $notificationtype);
             }
         }

          $time = time();
    $initcontent = array('name' => 'Events','arabicname' => 'إشعارات الفعاليات','shortname' => 'events','parent_module' => '0','usercreated' => '2','timecreated' => $time,'usermodified' => 2,'timemodified' => NULL, 'pluginname' => 'events');
    $parentid = $DB->get_field('local_notification_type', 'id', array('shortname' => 'events'));
    if($parentid){
          $notificationtype=new stdClass();
            $notificationtype->id=$parentid;
            $notificationtype->arabicname=$initcontent['arabicname'];
            $parentid = $DB->update_record('local_notification_type', $notificationtype);
    }
    $notification_type_data = array(
        array('name' => 'Create Event','arabicname' => ' إنشاء فعالية','shortname' => 'events_create','parent_module' => $parentid,'usercreated' => '2','timecreated' => $time,'usermodified' => 2,'timemodified' => NULL, 'pluginname' => 'events','plugintype' => 'local'),
       array('name' => 'Update Event','arabicname' => ' تحديث فعالية','shortname' => 'events_update','parent_module' => $parentid,'usercreated' => '2','timecreated' => $time,'usermodified' => 2,'timemodified' => NULL, 'pluginname' => 'events','plugintype' => 'local'),
       array('name' => 'Registration','arabicname' => ' التسجيل','shortname' => 'events_registration','parent_module' => $parentid,'usercreated' => '2','timecreated' => $time,'usermodified' => 2,'timemodified' => NULL, 'pluginname' => 'events','plugintype' => 'local'),
       array('name' => 'Speakers','arabicname' => ' المتحدثون','shortname' => 'events_speakers','parent_module' => $parentid,'usercreated' => '2','timecreated' => $time,'usermodified' => 2,'timemodified' => NULL, 'pluginname' => 'events','plugintype' => 'local'),
       array('name' => 'Sponsors','arabicname' => ' الرعاة','shortname' => 'events_sponsors','parent_module' => $parentid,'usercreated' => '2','timecreated' => $time,'usermodified' => 2,'timemodified' => NULL, 'pluginname' => 'events','plugintype' => 'local'),
       array('name' => 'Partners','arabicname' => ' الشركاء','shortname' => 'events_partners','parent_module' => $parentid,'usercreated' => '2','timecreated' => $time,'usermodified' => 2,'timemodified' => NULL, 'pluginname' => 'events','plugintype' => 'local'),
       array('name' => 'Completion','arabicname' => ' إكمال','shortname' => 'events_completion','parent_module' => $parentid,'usercreated' => '2','timecreated' => $time,'usermodified' => 2,'timemodified' => NULL, 'pluginname' => 'events','plugintype' => 'local'),
       array('name' => 'Before 7 days','arabicname' => 'تذكير قبل 7 أيام','shortname' => 'events_before_7_days','parent_module' => $parentid,'usercreated' => '2','timecreated' => $time,'usermodified' => 2,'timemodified' => NULL, 'pluginname' => 'events','plugintype' => 'local'),
       array('name' => 'Before 48 Hours','arabicname' => ' تذكير قبل 48 ساعة','shortname' => 'events_before_48_hours','parent_module' => $parentid,'usercreated' => '2','timecreated' => $time,'usermodified' => 2,'timemodified' => NULL, 'pluginname' => 'events','plugintype' => 'local'),       
       array('name' => 'Before 24 Hours','arabicname' => ' تذكير قبل 24 ساعة','shortname' => 'events_before_24_hours','parent_module' => $parentid,'usercreated' => '2','timecreated' => $time,'usermodified' => 2,'timemodified' => NULL, 'pluginname' => 'events','plugintype' => 'local'),
       array('name' => 'Send Conclusion','arabicname' => ' إرسال الخاتمة','shortname' => 'events_send_conclusion','parent_module' => $parentid,'usercreated' => '2','timecreated' => $time,'usermodified' => 2,'timemodified' => NULL, 'pluginname' => 'events','plugintype' => 'local'),
       array('name' => 'After Session','arabicname' => ' بعد الجلسة','shortname' => 'events_after_session','parent_module' => $parentid,'usercreated' => '2','timecreated' => $time,'usermodified' => 2,'timemodified' => NULL, 'pluginname' => 'events','plugintype' => 'local'), 
       array('name' => 'On Change','arabicname' => 'عند التغيير','shortname' => 'events_onchange','parent_module' => $parentid,'usercreated' => '2','timecreated' => $time,'usermodified' => 2,'timemodified' => NULL, 'pluginname' => 'events','plugintype' => 'local'), 
       array('name' => 'Cancel','arabicname' => 'إلغاء','shortname' => 'events_cancel','parent_module' => $parentid,'usercreated' => '2','timecreated' => $time,'usermodified' => 2,'timemodified' => NULL, 'pluginname' => 'events','plugintype' => 'local'),
       array('name' => 'Re-Schedule','arabicname' => ' إعادة الجدولة','shortname' => 'events_reschedule','parent_module' => $parentid,'usercreated' => '2','timecreated' => $time,'usermodified' => 2,'timemodified' => NULL, 'pluginname' => 'events','plugintype' => 'local'),    
    );

foreach($notification_type_data as $notification_type){
            $id=$DB->get_field('local_notification_type','id',  array('shortname'=>$notification_type['shortname']));
            if($id){
                $notificationtype=new stdClass();
                 $notificationtype->id=$id;
                 $notificationtype->arabicname=$notification_type['arabicname'];
                $DB->update_record('local_notification_type', $notificationtype);
             }
         }
          $time = time();
    $initcontent = array('name' => 'Competency','arabicname' => 'إشعارات الجدارت','shortname' => 'competency','parent_module' => '0','usercreated' => '2','timecreated' => $time,'usermodified' => 2,'timemodified' => NULL, 'pluginname' => 'competency','plugintype' => 'local');
    $parentid = $DB->get_field('local_notification_type', 'id', array('shortname' => 'competency'));
    if($parentid){
 $notificationtype=new stdClass();
            $notificationtype->id=$parentid;
            $notificationtype->arabicname=$initcontent['arabicname'];
            $parentid = $DB->update_record('local_notification_type', $notificationtype);
    }
    $notification_type_data = array(
        array('name' => 'Competency Completions','arabicname' => 'إكمال الجدارة','shortname' => 'competency_completions','parent_module' => $parentid,'usercreated' => '2','timecreated' => $time,'usermodified' => 2,'timemodified' => NULL, 'pluginname' => 'competency','plugintype' => 'local'),
        array('name' => 'Competency Adding Learning Item','arabicname' => 'الجدارة إضافة عنصر التعلم','shortname' => 'competency_adding_learning_item','parent_module' => $parentid,'usercreated' => '2','timecreated' => $time,'usermodified' => 2,'timemodified' => NULL, 'pluginname' => 'competency','plugintype' => 'local'),
        array('name' => 'Competency Removing Learning Item','arabicname' => 'الجدارة إزالة عنصر التعلم','shortname' => 'competency_removing_learning_item','parent_module' => $parentid,'usercreated' => '2','timecreated' => $time,'usermodified' => 2,'timemodified' => NULL, 'pluginname' => 'competency','plugintype' => 'local'),

    );
    foreach($notification_type_data as $notification_type){
            $id=$DB->get_field('local_notification_type','id',  array('shortname'=>$notification_type['shortname']));
            if($id){
                $notificationtype=new stdClass();
                 $notificationtype->id=$id;
                 $notificationtype->arabicname=$notification_type['arabicname'];
                $DB->update_record('local_notification_type', $notificationtype);
             }
         }

           $time = time();
    $initcontent = array('name' => 'Payment','arabicname' => 'إشعارت الدفع','shortname' => 'product','parent_module' => '0','usercreated' => '2','timecreated' => $time,'usermodified' => 2,'timemodified' => NULL, 'pluginname' => 'product');
    $parentid = $DB->get_field('local_notification_type', 'id', array('shortname' => 'product'));
    if($parentid){
            $notificationtype=new stdClass();
            $notificationtype->id=$parentid;
            $notificationtype->arabicname=$initcontent['arabicname'];
            $parentid = $DB->update_record('local_notification_type', $notificationtype);
    }
    $notification_type_data = array(
        array('name' => 'Payment completion','arabicname' => 'إتمام الدفع','shortname' => 'payment_completion','parent_module' => $parentid,'usercreated' => '2','timecreated' => $time,'usermodified' => 2,'timemodified' => NULL, 'pluginname' => 'product','plugintype' => 'tool'),
        array('name' => 'Pre payment','arabicname' => 'الدفع المسبق','shortname' => 'pre_payment','parent_module' => $parentid,'usercreated' => '2','timecreated' => $time,'usermodified' => 2,'timemodified' => NULL, 'pluginname' => 'product','plugintype' => 'tool'),
        array('name' => 'Post payment','arabicname' => 'الدفع الآجل','shortname' => 'post_payment','parent_module' => $parentid,'usercreated' => '2','timecreated' => $time,'usermodified' => 2,'timemodified' => NULL, 'pluginname' => 'product','plugintype' => 'tool'),
        array('name' => 'Wallet Update','arabicname' => 'تحديث المحفظة','shortname' => 'wallet_update','parent_module' => $parentid,'usercreated' => '2','timecreated' => $time,'usermodified' => 2,'timemodified' => NULL, 'pluginname' => 'product','plugintype' => 'tool'),
        
    );
    foreach($notification_type_data as $notification_type){
            $id=$DB->get_field('local_notification_type','id',  array('shortname'=>$notification_type['shortname']));
            if($id){
                $notificationtype=new stdClass();
                 $notificationtype->id=$id;
                 $notificationtype->arabicname=$notification_type['arabicname'];
                $DB->update_record('local_notification_type', $notificationtype);
             }
         }

        upgrade_plugin_savepoint(true, 2017111305.13, 'local', 'notifications');
    }
    if ($oldversion < 2017111305.15) {
        $time = time();
        $initcontent = array('name' => 'CPD','arabicname' =>'التدريب المهني المستمر','shortname' => 'cpd','parent_module' => '0','usercreated' => '2','timecreated' => $time,'usermodified' => 2,'timemodified' => NULL, 'pluginname' => 'cpd','plugintype' => 'local');
        $parentid = $DB->get_field('local_notification_type', 'id', array('shortname' => 'cpd'));
        if(!$parentid){
        $parentid = $DB->insert_record('local_notification_type', $initcontent);
        }
        $notification_type_data = array(
            array('name' => 'Create CPD','arabicname' =>'إضافة تدريب مهني مستمر جديد','shortname' => 'cpd_create','parent_module' => $parentid,'usercreated' => '2','timecreated' => $time,'usermodified' => 2,'timemodified' => NULL, 'pluginname' => 'cpd','plugintype' => 'local'),
           array('name' => 'Update CPD','arabicname' =>'رفع تدريب مهني مستمر','shortname' => 'cpd_update','parent_module' => $parentid,'usercreated' => '2','timecreated' => $time,'usermodified' => 2,'timemodified' => NULL, 'pluginname' => 'cpd','plugintype' => 'local'),
           array('name' => 'remaining days for expiration < 180 days and > 90 days','arabicname' =>'الأيام المتبقية لانتهاء الصلاحية أقل من ١٨٠ يوم وأكثر من ٩٠ يوم','shortname' => 'cpd_expiration_lt_180days_and_gt_90_days','parent_module' => $parentid,'usercreated' => '2','timecreated' => $time,'usermodified' => 2,'timemodified' => NULL, 'pluginname' => 'cpd','plugintype' => 'local'),
           array('name' => 'remaining days for expiration < 90 days','arabicname' =>'الأيام المتبقية لانتهاء الصلاحية أقل من ٩٠ يوم','shortname' => 'cpd_expiration_lt_90_days','parent_module' => $parentid,'usercreated' => '2','timecreated' => $time,'usermodified' => 2,'timemodified' => NULL, 'pluginname' => 'cpd','plugintype' => 'local'),
           array('name' => 'Evidence submit','arabicname' =>'تقديم إثبات','shortname' => 'cpd_evidence_submit','parent_module' => $parentid,'usercreated' => '2','timecreated' => $time,'usermodified' => 2,'timemodified' => NULL, 'pluginname' => 'cpd','plugintype' => 'local'),
           array('name' => 'Evidence approve','arabicname' =>'اعتماد الإثبات','shortname' => 'cpd_evidence_approve','parent_module' => $parentid,'usercreated' => '2','timecreated' => $time,'usermodified' => 2,'timemodified' => NULL, 'pluginname' => 'cpd','plugintype' => 'local'),
           array('name' => 'Evidence reject','arabicname' =>'رفض الإثبات','shortname' => 'cpd_evidence_reject','parent_module' => $parentid,'usercreated' => '2','timecreated' => $time,'usermodified' => 2,'timemodified' => NULL, 'pluginname' => 'cpd','plugintype' => 'local'),
           array('name' => 'Training program assign','arabicname' =>'تعيين برنامج تدريبي','shortname' => 'cpd_training_program_assign','parent_module' => $parentid,'usercreated' => '2','timecreated' => $time,'usermodified' => 2,'timemodified' => NULL, 'pluginname' => 'cpd','plugintype' => 'local'),
           array('name' => 'Training program unassign','arabicname' =>'إلغاء تعيين البرنامج التدريبي ','shortname' => 'cpd_training_program_unassign','parent_module' => $parentid,'usercreated' => '2','timecreated' => $time,'usermodified' => 2,'timemodified' => NULL, 'pluginname' => 'cpd','plugintype' => 'local'),
           array('name' => 'CPD completion','arabicname' =>'إكمال التدريب المهني المستمر','shortname' => 'cpd_completion','parent_module' => $parentid,'usercreated' => '2','timecreated' => $time,'usermodified' => 2,'timemodified' => NULL, 'pluginname' => 'cpd','plugintype' => 'local'),
           array('name' => 'Certificate renewal','arabicname' =>'تجديد الشهادة','shortname' => 'cpd_certificate_renewal','parent_module' => $parentid,'usercreated' => '2','timecreated' => $time,'usermodified' => 2,'timemodified' => NULL, 'pluginname' => 'cpd','plugintype' => 'local'),     
           array('name' => 'On Change','arabicname' =>'قيد التغيير','shortname' => 'cpd_onchange','parent_module' => $parentid,'usercreated' => '2','timecreated' => $time,'usermodified' => 2,'timemodified' => NULL, 'pluginname' => 'cpd','plugintype' => 'local'), 
           array('name' => 'Cancel','arabicname' =>'إلغاء','shortname' => 'cpd_cancel','parent_module' => $parentid,'usercreated' => '2','timecreated' => $time,'usermodified' => 2,'timemodified' => NULL, 'pluginname' => 'cpd','plugintype' => 'local'),
           array('name' => 'Re-Schedule','arabicname' =>'إعادة جدولة','shortname' => 'cpd_reschedule','parent_module' => $parentid,'usercreated' => '2','timecreated' => $time,'usermodified' => 2,'timemodified' => NULL, 'pluginname' => 'cpd','plugintype' => 'local'), 
        
        );
        foreach($notification_type_data as $notification_type){
            unset($notification_type['timecreated']);
            if(!$DB->record_exists('local_notification_type',  $notification_type)){
                $notification_type['timecreated'] = $time;
                $DB->insert_record('local_notification_type', $notification_type);
            }
        }
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
	     upgrade_plugin_savepoint(true, 2017111305.15, 'local', 'notifications');
    }
    if ($oldversion < 2017111305.18) {


        $parentid = $DB->get_field('local_notification_type', 'id', array('shortname' => 'trainingprogram'));  
            $notificationtypedata = array('name' => 'Before 72 Hours',
            'arabicname' => 'تذكير قبل 72 ساعة',
            'shortname' => 'trainingprogram_before_72_hours',
            'parent_module' => $parentid,
            'usercreated' => '2',
            'timecreated' => $time,
            'usermodified' => 2,
            'timemodified' => NULL,
            'pluginname' => 'trainingprogram',
            'plugintype' => 'local'
    );
    $DB->insert_record('local_notification_type',  $notificationtypedata );
    upgrade_plugin_savepoint(true, 2017111305.18, 'local', 'notifications');
}
if ($oldversion < 2017111305.19) {
    $time = time();
   

    $notification_info_data =  array(
    'subject' => 'Attendance reminder [trainingprogram_related_module_name]',
    'body' => '<p dir="ltr" style="text-align: left;"></p><p dir="ltr"><br>Dear&nbsp;[trainingprogram_userfullname],</p>This is a reminder that [trainingprogram_related_module_name] will start after 72 hours at [trainingprogram_related_module_date] [trainingprogram_related_module_time]. Thanks<br><p></p>',
    'arabic_subject' =>'تذكير حضور  [trainingprogram_related_module_name]',
    'arabic_body' => '<p dir="ltr" style="text-align: left;"></p><p dir="ltr">عزيزي&nbsp;[trainingprogram_userfullname]</p>، هذا تذكير بأن [trainingprogram_related_module_name] سيبدأ بعد 72 ساعة في تاريخ: [trainingprogram_related_module_date] [trainingprogram_related_module_time]. شكرًا<br><p></p>',
    'notification_type_shortname'=>'trainingprogram_before_72_hours'
    );


    $notification_typeinfo = $DB->get_record('local_notification_type', array('shortname' =>$notification_info_data['notification_type_shortname']),'id,pluginname');
    if($notification_typeinfo){
     
        if(!$DB->record_exists('local_notification_info', array('notificationid'=>$notification_typeinfo->id))){
            $notification_info_data['moduletype'] = $notification_typeinfo->pluginname;
            $notification_info_data['notificationid']=$notification_typeinfo->id;
            $notification_info_data['usercreated'] = 2;
            $notification_info_data['timecreated'] = $time;
            $DB->insert_record('local_notification_info', $notification_info_data);

        }
    }

    // $DB->insert_record('local_notification_type',  $notificationtypedata );
    upgrade_plugin_savepoint(true, 2017111305.19, 'local', 'notifications');
}




    if ($oldversion <  2017111305.21) {
    $parentid = $DB->get_field('local_notification_type', 'id', array('shortname' => 'trainingprogram'));  
    $notificationtypedata = 
    array('name' => 'Unenrollment',
    'arabicname' => ' التسجيل في البرنامج',
    'shortname' => 'trainingprogram_unenroll',
    'parent_module' => $parentid,
    'usercreated' => '2',
    'timecreated' => $time,
    'usermodified' => 2,
    'timemodified' => NULL, 
    'pluginname' => 'trainingprogram',
    'plugintype' => 'local'
    );
    if($DB->record_exists('local_notification_type', array('shortname'=>  $notificationtypedata['shortname'])))
    {

        $DB->delete_records('local_notification_type', array('shortname'=>$notificationtypedata['shortname']));       
        

    }
    $parentid = $DB->get_field('local_notification_type', 'id', array('shortname' => 'trainingprogram'));  
    $notificationtypedata = array('name' => 'Before 72 Hours',
    'arabicname' => 'تذكير قبل 72 ساعة',
    'shortname' => 'trainingprogram_before_72_hours',
    'parent_module' => $parentid,
    'usercreated' => '2',
    'timecreated' => $time,
    'usermodified' => 2,
    'timemodified' => NULL,
    'pluginname' => 'trainingprogram',
    'plugintype' => 'local'
);
if($DB->record_exists('local_notification_type', array('shortname'=>  $notificationtypedata['shortname']))){

$DB->delete_records('local_notification_type', array('shortname'=>$notificationtypedata['shortname']));
// $DB->delete_records('local_notification_info', array('shortname'=>$notificationtypedata['shortname']));
}

    upgrade_plugin_savepoint(true,  2017111305.21, 'local', 'notifications');
 
}

if ($oldversion < 2017111305.22) {
    $time = time();
    $parentid = $DB->get_field('local_notification_type', 'id', array('shortname' => 'exams'));
    $notificationtypedata = 
    array('name' => 'Unenrollment',
    'arabicname' => ' التسجيل في البرنامج',
    'shortname' => 'exam_unenroll',
    'parent_module' => $parentid,
    'usercreated' => '2',
    'timecreated' => $time,
    'usermodified' => 2,
    'timemodified' => NULL, 
    'pluginname' => 'exams',
    'plugintype' => 'local'
  );
    $DB->insert_record('local_notification_type',  $notificationtypedata );
    $time = time();
    $notification_info_data =  array(
        'subject' => 'Cancel Exam  [exam_name]',
        'body' => '<p dir="ltr" style="text-align: left;">Dear [exam_userfullname],&nbsp;</p><p dir="ltr" style="text-align: left;">We would like to inform you that you are unenrolled in&nbsp; [exam_name]  at [exam_date] [exam_time] .&nbsp;</p><p dir="ltr" style="text-align: left;">Thanks<br></p><p dir="ltr" style="text-align: left;"><br></p><p dir="ltr" style="text-align: left;"><br></p><p dir="ltr" style="text-align: left;"><br></p>',
        'arabic_subject' =>'إلغاء اختبار  [exam_name]',
        'arabic_body' => '<p dir="ltr" style="text-align: left;">عزيزي [exam_userfullname]،&nbsp;<br></p><p dir="ltr" style="text-align: left;">&nbsp;نود إشعاركم بأنه قد تم إلغاء تسجيلك في[exam_name].الموافق &nbsp;[exam_date] [exam_time]&nbsp;.&nbsp;</p><p dir="ltr" style="text-align: left;">&nbsp;شكرًا.<br></p>',
        'notification_type_shortname'=>'exam_unenroll'
    );
    $notification_typeinfo = $DB->get_record('local_notification_type', array('shortname' =>$notification_info_data['notification_type_shortname']),'id,pluginname');
    if($notification_typeinfo){
     
        if(!$DB->record_exists('local_notification_info', array('notificationid'=>$notification_typeinfo->id))){
            $notification_info_data['moduletype'] = $notification_typeinfo->pluginname;
            $notification_info_data['notificationid']=$notification_typeinfo->id;
            $notification_info_data['usercreated'] = 2;
            $notification_info_data['timecreated'] = $time;
            $DB->insert_record('local_notification_info', $notification_info_data);
        }
     }
  
           $time = time();
           $parentid = $DB->get_field('local_notification_type', 'id', array('shortname' => 'trainingprogram'));  
           $notificationtypedata = 
           array('name' => 'Unenrollment',
           'arabicname' => ' التسجيل في البرنامج',
           'shortname' => 'trainingprogram_unenroll',
           'parent_module' => $parentid,
           'usercreated' => '2',
           'timecreated' => $time,
           'usermodified' => 2,
           'timemodified' => NULL, 
           'pluginname' => 'trainingprogram',
           'plugintype' => 'local'
       );
           $DB->insert_record('local_notification_type',  $notificationtypedata );
           


           $time = time();
           $notification_info_data =  array(
               'subject' => 'Cancel Training Program [program_name]',
               'body' => '<p dir="ltr" style="text-align: left;">Dear [program_userfullname],&nbsp;</p><p dir="ltr" style="text-align: left;">We would like to inform you that you are unenrolled in [program_name] at [program_date] [program_time]&nbsp;.&nbsp;</p><p dir="ltr" style="text-align: left;">Thanks<br></p><p dir="ltr" style="text-align: left;"><br></p><p dir="ltr" style="text-align: left;"><br></p>',
               'arabic_subject' =>'إلغاء برنامج تدريبي  [program_name]',
               'arabic_body' => '<p dir="ltr" style="text-align: left;">عزيزي [program_userfullname]&nbsp; ،<br></p><p dir="ltr" style="text-align: left;">&nbsp;نود إشعاركم بأنه قد تم إلغاء تسجيلك في [program_name]. الموافق [program_date] [program_time].</p><p dir="ltr" style="text-align: left;">&nbsp;شكرًا.<br></p>',
               'notification_type_shortname'=>'trainingprogram_unenroll'
           );
           $notification_typeinfo = $DB->get_record('local_notification_type', array('shortname' =>$notification_info_data['notification_type_shortname']),'id,pluginname');
           if($notification_typeinfo){
            
               if(!$DB->record_exists('local_notification_info', array('notificationid'=>$notification_typeinfo->id))){
                   $notification_info_data['moduletype'] = $notification_typeinfo->pluginname;
                   $notification_info_data['notificationid']=$notification_typeinfo->id;
                   $notification_info_data['usercreated'] = 2;
                   $notification_info_data['timecreated'] = $time;
                   $DB->insert_record('local_notification_info', $notification_info_data);
               }
            }  
    upgrade_plugin_savepoint(true,  2017111305.22, 'local', 'notifications');
   
  }
  if ($oldversion < 2017111305.23) {
    $time = time();
    $parentid = $DB->get_field('local_notification_type', 'id', array('shortname' => 'exams'));
    $notificationtypedata = 
    array(
    'name' => 'Reschedule',
    'arabicname' => ' إعادة الجدولة',
    'shortname' => 'exam_reschedule',
    'parent_module' => $parentid,
    'usercreated' => '2',
    'timecreated' => $time,
    'usermodified' => 2,
    'timemodified' => NULL, 
    'pluginname' => 'exams',
    'plugintype' => 'local'
  );
    $DB->insert_record('local_notification_type',  $notificationtypedata );
    $time = time();
    $notification_info_data =  array(
        'subject' => 'Rescheduling Exam [exam_name]',
        'body' => '<p dir="ltr" style="text-align: left;">Dear [exam_userfullname],&nbsp;</p><p dir="ltr" style="text-align: left;">We would like to inform you that we rescheduled you in&nbsp; [exam_name]  from [pastexam_date] [pastexam_time] to [presentexam_date] [presentexam_time]  .&nbsp;</p><p dir="ltr" style="text-align: left;">Thanks<br></p><p dir="ltr" style="text-align: left;"><br></p><p dir="ltr" style="text-align: left;"><br></p><p dir="ltr" style="text-align: left;"><br></p>',
        'arabic_subject' =>'إعادة جدولة اختبار [exam_name]',
        'arabic_body' => '<p dir="ltr" style="text-align: left;">عزيزي [exam_userfullname]،&nbsp;<br></p><p dir="ltr" style="text-align: left;">&nbsp; نود إشعاركم بأنه قد تم إعادة جدولة تسجيلك في [exam_name] من [pastexam_date] [pastexam_time] الى [presentexam_date] [presentexam_time]. 
        &nbsp;.&nbsp;</p><p dir="ltr" style="text-align: left;">&nbsp;شكرًا.<br></p>',
        'notification_type_shortname'=>'exam_reschedule'
    );
    $notification_typeinfo = $DB->get_record('local_notification_type', array('shortname' =>$notification_info_data['notification_type_shortname']),'id,pluginname');
    if($notification_typeinfo){
     
        if(!$DB->record_exists('local_notification_info', array('notificationid'=>$notification_typeinfo->id))){
            $notification_info_data['moduletype'] = $notification_typeinfo->pluginname;
            $notification_info_data['notificationid']=$notification_typeinfo->id;
            $notification_info_data['usercreated'] = 2;
            $notification_info_data['timecreated'] = $time;
            $DB->insert_record('local_notification_info', $notification_info_data);
        }
     }
     upgrade_plugin_savepoint(true,  2017111305.23, 'local', 'notifications');
   
    }

    if ($oldversion < 2017111305.24) {
        $time = time();
        $parentid = $DB->get_field('local_notification_type', 'id', array('shortname' => 'events'));
        $notificationtypedata = 
        array(
        'name' => 'Unregister',
        'arabicname' => ' غير مسجل',
        'shortname' => 'event_unregister',
        'parent_module' => $parentid,
        'usercreated' => '2',
        'timecreated' => $time,
        'usermodified' => 2,
        'timemodified' => NULL, 
        'pluginname' => 'events',
        'plugintype' => 'local'
      );
        $DB->insert_record('local_notification_type',  $notificationtypedata );
        $time = time();
        $notification_info_data =  array(
            'subject' => 'Cancel Event [event_name]',
            'body' => '<p dir="ltr" style="text-align: left;">Dear [event_userfullname],&nbsp;</p><p dir="ltr" style="text-align: left;">We would like to inform you that you are unenrolled in &nbsp; [event_name]  at [event_date] [event_time] .&nbsp;</p><p dir="ltr" style="text-align: left;">Thanks<br></p><p dir="ltr" style="text-align: left;"><br></p><p dir="ltr" style="text-align: left;"><br></p><p dir="ltr" style="text-align: left;"><br></p>',
            'arabic_subject' =>'إلغاء فعالية [event_name]',
            'arabic_body' => '<p dir="ltr" style="text-align: left;">عزيزي [event_userfullname]،&nbsp;<br></p><p dir="ltr" style="text-align: left;">&nbsp;  نود إشعاركم بأنه قد تم إلغاء تسجيلك في [event_name] الموافق [event_date] [event_time]. 
            &nbsp;.&nbsp;</p><p dir="ltr" style="text-align: left;">&nbsp;شكرًا.<br></p>',
            'notification_type_shortname'=>'event_unregister'
        );
        $notification_typeinfo = $DB->get_record('local_notification_type', array('shortname' =>$notification_info_data['notification_type_shortname']),'id,pluginname');
        if($notification_typeinfo){
         
            if(!$DB->record_exists('local_notification_info', array('notificationid'=>$notification_typeinfo->id))){
                $notification_info_data['moduletype'] = $notification_typeinfo->pluginname;
                $notification_info_data['notificationid']=$notification_typeinfo->id;
                $notification_info_data['usercreated'] = 2;
                $notification_info_data['timecreated'] = $time;
                $DB->insert_record('local_notification_info', $notification_info_data);
            }
         }
         upgrade_plugin_savepoint(true, 2017111305.24, 'local', 'notifications');
       
        }
        if ($oldversion < 2017111305.25) {
            $time = time();
            $parentid = $DB->get_field('local_notification_type', 'id', array('shortname' => 'exams'));
            $notificationtypedata = 
            array(
            'name' => 'Other Exam Register',
            'arabicname' => ' غير مسجل',
            'shortname' => 'other_exam_register',
            'parent_module' => $parentid,
            'usercreated' => '2',
            'timecreated' => $time,
            'usermodified' => 2,
            'timemodified' => NULL, 
            'pluginname' => 'exams',
            'plugintype' => 'local'
          );
            $DB->insert_record('local_notification_type',  $notificationtypedata );
            $time = time();
            $notification_info_data =  array(
                'subject' => 'Enrolment at  [exam_name]  owned by   [exam_ownedby]',
                'body' => '<p dir="ltr" style="text-align: left;">Dear [exam_userfullname],&nbsp;</p><p dir="ltr" style="text-align: left;">&nbspWe would like to inform you that you are enrolled in [exam_name]  exam  which is owned by [exam_ownedby] at [exam_date] [exam_time].&nbsp;</p><p dir="ltr" style="text-align: left;">Thanks.<br></p>',
                'arabic_subject' =>'التسجيل في  [exam_ownedby] [exam_name]اختبار',
                'arabic_body' => '<p dir="ltr" style="text-align: left;">عزيزي [exam_userfullname] ،</p><p dir="ltr" style="text-align: left;">&nbsp; نود إشعاركم بأنه قد تم تسجيلك في [exam_name] التي يملكه[exam_ownedby]    الموافق   [exam_date] [exam_time]. &nbsp;</p><p dir="ltr" style="text-align: left;">شكرًا.<br></p>',
                'notification_type_shortname'=>'other_exam_register'
            );
            $notification_typeinfo = $DB->get_record('local_notification_type', array('shortname' =>$notification_info_data['notification_type_shortname']),'id,pluginname');
            if($notification_typeinfo){
             
                if(!$DB->record_exists('local_notification_info', array('notificationid'=>$notification_typeinfo->id))){
                    $notification_info_data['moduletype'] = $notification_typeinfo->pluginname;
                    $notification_info_data['notificationid']=$notification_typeinfo->id;
                    $notification_info_data['usercreated'] = 2;
                    $notification_info_data['timecreated'] = $time;
                    $DB->insert_record('local_notification_info', $notification_info_data);
                }
             }
             upgrade_plugin_savepoint(true, 2017111305.25, 'local', 'notifications');
            
            }  
            
            if ($oldversion < 2017111305.26) {
                $notification_typeinfo = $DB->get_record('local_notification_type', array('shortname' =>'other_exam_register'),'id,pluginname');
                if($notification_typeinfo){
                 
                    if($DB->record_exists('local_notification_info', array('notificationid'=>$notification_typeinfo->id))){
                    
                        $DB->delete_records('local_notification_info',['notificationid'=>$notification_typeinfo->id]);
                    }
                 }
                 $notification_type = $DB->get_record('local_notification_type', array('shortname' =>'other_exam_register'),'id,pluginname');
                 if($notification_type){
                  
                     if($DB->record_exists('local_notification_type', array('id'=>$notification_type->id))){
                     
                         $DB->delete_records('local_notification_type',  array('id'=>$notification_type->id));
                     }
                    }

                    $time = time();
                    $parentid = $DB->get_field('local_notification_type', 'id', array('shortname' => 'exams'));
                    $notificationtypedata = 
                    array(
                    'name' => 'Other Exam Enrollment',
                    'arabicname' => ' غير مسجل',
                    'shortname' => 'other_exam_enrollment',
                    'parent_module' => $parentid,
                    'usercreated' => '2',
                    'timecreated' => $time,
                    'usermodified' => 2,
                    'timemodified' => NULL, 
                    'pluginname' => 'exams',
                    'plugintype' => 'local'
                  );
                    $DB->insert_record('local_notification_type',  $notificationtypedata );
                    $time = time();
                    $notification_info_data =  array(
                        'subject' => 'Enrolment at  [exam_name]  owned by   [exam_ownedby]',
                        'body' => '<p dir="ltr" style="text-align: left;">Dear [exam_userfullname],&nbsp;</p><p dir="ltr" style="text-align: left;">&nbspWe would like to inform you that you are enrolled in [exam_name]  exam  which is owned by [exam_ownedby] at [exam_date] [exam_time].&nbsp;</p><p dir="ltr" style="text-align: left;">Thanks.<br></p>',
                        'arabic_subject' =>'التسجيل في  [exam_ownedby] [exam_name]اختبار',
                        'arabic_body' => '<p dir="ltr" style="text-align: left;">عزيزي [exam_userfullname] ،</p><p dir="ltr" style="text-align: left;">&nbsp; نود إشعاركم بأنه قد تم تسجيلك في [exam_name] التي يملكه[exam_ownedby]    الموافق   [exam_date] [exam_time]. &nbsp;</p><p dir="ltr" style="text-align: left;">شكرًا.<br></p>',
                        'notification_type_shortname'=>'other_exam_enrollment'
                    );
                    $notification_typeinfo = $DB->get_record('local_notification_type', array('shortname' =>$notification_info_data['notification_type_shortname']),'id,pluginname');
                    if($notification_typeinfo){
                     
                        if(!$DB->record_exists('local_notification_info', array('notificationid'=>$notification_typeinfo->id))){
                            $notification_info_data['moduletype'] = $notification_typeinfo->pluginname;
                            $notification_info_data['notificationid']=$notification_typeinfo->id;
                            $notification_info_data['usercreated'] = 2;
                            $notification_info_data['timecreated'] = $time;
                            $DB->insert_record('local_notification_info', $notification_info_data);
                        }
                     }

                     $time = time();
                     $parentid = $DB->get_field('local_notification_type', 'id', array('shortname' => 'exams'));
                     $notificationtypedata = 
                     array(
                     'name' => ' Exam Service Provider',
                     'arabicname' => ' غير مسجل',
                     'shortname' => 'exam_service_provider',
                     'parent_module' => $parentid,
                     'usercreated' => '2',
                     'timecreated' => $time,
                     'usermodified' => 2,
                     'timemodified' => NULL, 
                     'pluginname' => 'exams',
                     'plugintype' => 'local'
                   );
                     $DB->insert_record('local_notification_type',  $notificationtypedata );
                     $time = time();
                     $notification_info_data =  array(
                         'subject' => 'Enrolment at  [exam_name]  owned by   [exam_ownedby]',
                         'body' => '<p dir="ltr" style="text-align: left;">Dear [exam_ownedby],&nbsp;</p><p dir="ltr" style="text-align: left;">&nbspWe would like to inform you that an enrollment  in [exam_name]  exam  and trainee name is [exam_userfullname] at [exam_date] [exam_time].&nbsp;</p><p dir="ltr" style="text-align: left;">Thanks.<br></p>',
                         'arabic_subject' =>'التسجيل في  [exam_ownedby] [exam_name]اختبار',
                         'arabic_body' => '<p dir="ltr" style="text-align: left;">عزيزي [exam_ownedby] ،</p><p dir="ltr" style="text-align: left;">&nbsp;      نود أن نعلمك أن التسجيل في [exam_name] التي يملكه[exam_ownedby]   اسم الامتحان والمتدرب هو  [exam_userfullname] الموافق   [exam_date] [exam_time]. &nbsp;</p><p dir="ltr" style="text-align: left;">شكرًا.<br></p>',
                         'notification_type_shortname'=>'exam_service_provider'                   

                     );
                     $notification_typeinfo = $DB->get_record('local_notification_type', array('shortname' =>$notification_info_data['notification_type_shortname']),'id,pluginname');
                     if($notification_typeinfo){
                      
                         if(!$DB->record_exists('local_notification_info', array('notificationid'=>$notification_typeinfo->id))){
                             $notification_info_data['moduletype'] = $notification_typeinfo->pluginname;
                             $notification_info_data['notificationid']=$notification_typeinfo->id;
                             $notification_info_data['usercreated'] = 2;
                             $notification_info_data['timecreated'] = $time;
                             $DB->insert_record('local_notification_info', $notification_info_data);
                         }
                      }
                    upgrade_plugin_savepoint(true, 2017111305.26, 'local', 'notifications');

                }


            if ($oldversion < 2017111305.27) {
                $table = new xmldb_table('local_emaillogs');
                if ($dbman->table_exists($table)) {        
                    $field = new xmldb_field('ccusers', XMLDB_TYPE_CHAR, '255', null, null, null,null, null);
                    if(!$dbman->field_exists($table, $field)){
                        $dbman->add_field($table, $field);
                    }
                }
                upgrade_plugin_savepoint(true, 2017111305.27, 'local', 'notifications');

            }


            if ($oldversion < 2017111305.28) {
                $time = time();
                $parentid = $DB->get_field('local_notification_type', 'id', array('shortname' => 'trainingprogram'));
                $notificationtypedata = 
           
                array(
                'name' => ' Before 15 days',
                'arabicname' => 'تذكير قبل 15 أيام',
                'shortname' => 'trainingprogram_before_15_days',
                'parent_module' => $parentid,
                'usercreated' => '2',
                'timecreated' => $time,
                'usermodified' => 2,
                'timemodified' => NULL, 
                'pluginname' => 'trainingprogram',
                'plugintype' => 'local'
              );
                $DB->insert_record('local_notification_type',  $notificationtypedata );



                $time = time();
                $notification_info_data =  
                array(
                    'subject' => ' Attendance reminder [program_name]',
                    'body' => '<p dir="ltr" style="text-align: left;">Dear [program_userfullname] , This is a reminder that [program_name] will start after 15 days at [program_date]&nbsp; [program_time]. Thanks<br><br></p>',
                    'arabic_subject' =>'تذكير حضور  [program_name]',
                    'arabic_body' => '<p dir="ltr" style="text-align: left;">عزيزي [program_userfullname] ، هذا تذكير بأن [program_name] سيبدأ بعد 15 أيام في تاريخ: [program_date] [program_time]. شكرًا<br></p>',
                    'notification_type_shortname'=>'trainingprogram_before_15_days'
                );
                $notification_typeinfo = $DB->get_record('local_notification_type', array('shortname' =>$notification_info_data['notification_type_shortname']),'id,pluginname');
                if($notification_typeinfo){
                 
                    if(!$DB->record_exists('local_notification_info', array('notificationid'=>$notification_typeinfo->id))){
                        $notification_info_data['moduletype'] = $notification_typeinfo->pluginname;
                        $notification_info_data['notificationid']=$notification_typeinfo->id;
                        $notification_info_data['usercreated'] = 2;
                        $notification_info_data['timecreated'] = $time;
                        $DB->insert_record('local_notification_info', $notification_info_data);
                    }
                 }


                 $time = time();
                 $parentid = $DB->get_field('local_notification_type', 'id', array('shortname' => 'trainingprogram'));
                 $notificationtypedata = 
            
                 array(
                 'name' => ' Exam Result Objection',
                 'arabicname' => 'اعتراض نتيجة الامتحان',
                 'shortname' => 'exam_result_objection',
                 'parent_module' => $parentid,
                 'usercreated' => '2',
                 'timecreated' => $time,
                 'usermodified' => 2,
                 'timemodified' => NULL, 
                 'pluginname' => 'exams',
                 'plugintype' => 'local'
               );
                 $DB->insert_record('local_notification_type',  $notificationtypedata );
 
 
 
                 $time = time();
                 $notification_info_data =  
                 array(
                     'subject' => '[exam_name] Exam Result Objection Submitted',
                     'body' => '<p dir="ltr" style="text-align: left;">Dear [exam_userfullname] , an exam [exam_name] result objection has been submitted  &nbsp; Thanks<br><br></p>',
                     'arabic_subject' =>'[exam_name]تم إرسال اعتراض نتيجة الاختبار  ',
                     'arabic_body' => '<p dir="ltr" style="text-align: left;">عزيزي [exam_userfullname] ، تم تقديم اعتراض على نتيجة الاختبار [exam_name]
 . شكرًا<br></p>',
                     'notification_type_shortname'=>'exam_result_objection'
                 );
                 $notification_typeinfo = $DB->get_record('local_notification_type', array('shortname' =>$notification_info_data['notification_type_shortname']),'id,pluginname');
                 if($notification_typeinfo){
                  
                     if(!$DB->record_exists('local_notification_info', array('notificationid'=>$notification_typeinfo->id))){
                         $notification_info_data['moduletype'] = $notification_typeinfo->pluginname;
                         $notification_info_data['notificationid']=$notification_typeinfo->id;
                         $notification_info_data['usercreated'] = 2;
                         $notification_info_data['timecreated'] = $time;
                         $DB->insert_record('local_notification_info', $notification_info_data);
                     }
                
    


                    }

                upgrade_plugin_savepoint(true, 2017111305.28, 'local', 'notifications');


            }

            if ($oldversion < 2017111305.29) {

                $time = time();
                    $notification_info_data =  
                    array(
                        'subject' => '[exam_name] Exam Result Objection Submitted',
                        'body' => '<p dir="ltr" style="text-align: left;">Dear [exam_examoff] ,&nbsp;</p>
                        <p dir="ltr" style="text-align: left;">an exam [exam_name] result objection has been submitted by a trainee [exam_userfullname]&nbsp;&nbsp;</p>
                        <p dir="ltr" style="text-align: left;">&nbsp; Thanks<br><br></p>',
                        'arabic_subject' =>'[exam_name]تم إرسال اعتراض نتيجة الاختبار  ',
                        'arabic_body' => 'عزيزي [exam_examoff] ،
                        تم تقديم اعتراض على نتيجة [exam_name] من قبل المتدرب [exam_userfullname]  شكرًا
                        ',
                        'notification_type_shortname'=>'exam_result_objection'
                    );
                    $notification_typeinfo = $DB->get_record('local_notification_type', array('shortname' =>$notification_info_data['notification_type_shortname']),'id,pluginname');
                    if($notification_typeinfo){
                     
                        if($DB->record_exists('local_notification_info', array('notificationid'=>$notification_typeinfo->id))){
                            $notificationinfoid = $DB->get_field('local_notification_info','id',array('notificationid'=>$notification_typeinfo->id));
                            $notification_info_data['id'] =   $notificationinfoid ;
     
                            $DB->update_record('local_notification_info', $notification_info_data);
                        }
                     }
    
    
                     upgrade_plugin_savepoint(true, 2017111305.29, 'local', 'notifications');
    
                    }
                
        if ($oldversion < 2017111306.1) {
            $dbman = $DB->get_manager();
            $tableA= new xmldb_table('local_emaillogs');
            $tableB= new xmldb_table('local_smslogs');
            $field=  new xmldb_field('realuser', XMLDB_TYPE_INTEGER, '10', null, XMLDB_NOTNULL, null, '0','to_userid');
            
            if (!$dbman->field_exists($tableA, $field)) {
                $dbman->add_field($tableA, $field);
            }

            if (!$dbman->field_exists($tableB, $field)) {
                $dbman->add_field($tableB, $field);
            }

            upgrade_plugin_savepoint(true, 2017111306.1, 'local', 'notifications');
        }




        if ($oldversion <  2017111306.2) {     
            $time = time();
            $parentid = $DB->get_field('local_notification_type', 'id', array('shortname' => 'trainingprogram')); 
            $notificationtypedata = 
        array(
        'name' => 'Program Enrollment',
        'arabicname' => 'التسجيل في البرنامج
',
        'shortname' => 'trainee_tp_enrollment',
        'parent_module' => $parentid,
        'usercreated' => '2',
        'timecreated' => $time,
        'usermodified' => 2,
        'timemodified' => NULL, 
        'pluginname' => 'trainingprogram',
        'plugintype' => 'local'
      );
        $DB->insert_record('local_notification_type',  $notificationtypedata );
        $time = time();
        $notification_info_data =  array(
            'subject' => 'Enrolment at [program_name]',
            'body' => '<p dir="ltr" style="text-align: left;">Dear [program_trainingoff],&nbsp;</p><p dir="ltr" style="text-align: left;">We would like to inform you that  a trainee [program_userfullname] have been enrolled at [program_name] training program.&nbsp;</p><p dir="ltr" style="text-align: left;">Thanks<br></p>',
            'arabic_subject' =>'التسجيل البرنامج التدريبي [program_name]',
            'arabic_body' => '<p dir="ltr" style="text-align: left;">عزيزي [program_trainingoff] ،</p><p dir="ltr" style="text-align: left;">&nbsp;نود أن نعلمك أن أحد المتدربين [program_userfullname] قد تم تسجيله في [program_name] برنامج تدريبي..</p><p dir="ltr" style="text-align: left;">&nbsp;شكرًا<br></p>',
            'notification_type_shortname'=>'trainee_tp_enrollment'
        );
        $notification_typeinfo = $DB->get_record('local_notification_type', array('shortname' =>$notification_info_data['notification_type_shortname']),'id,pluginname');
        if($notification_typeinfo){
         
            if(!$DB->record_exists('local_notification_info', array('notificationid'=>$notification_typeinfo->id))){
                $notification_info_data['moduletype'] = $notification_typeinfo->pluginname;
                $notification_info_data['notificationid']=$notification_typeinfo->id;
                $notification_info_data['usercreated'] = 2;
                $notification_info_data['timecreated'] = $time;
                $DB->insert_record('local_notification_info', $notification_info_data);
            }
         }

            upgrade_plugin_savepoint(true,  2017111306.2, 'local', 'notifications');



         }




         if ($oldversion < 2017111306.3) {     
            $time = time();
            $parentid = $DB->get_field('local_notification_type', 'id', array('shortname' => 'trainingprogram')); 
            $notificationtypedata = 
        array(
        'name' => 'Bulk Enrol Program',
        'arabicname' => 'برنامج التسجيل الجماعي',
        'shortname' => 'bulkenrol_program',
        'parent_module' => $parentid,
        'usercreated' => '2',
        'timecreated' => $time,
        'usermodified' => 2,
        'timemodified' => NULL, 
        'pluginname' => 'trainingprogram',
        'plugintype' => 'local'
      );
        $DB->insert_record('local_notification_type',  $notificationtypedata );
        $time = time();
        $notification_info_data =  array(
            'subject' => 'Bulk Enrollment [program_name]',
            'body' => '<p dir="ltr" style="text-align: left;">Dear [org_off],&nbsp;</p>
            <p dir="ltr" style="text-align: left;">We would like to inform you that the following trainees have enrolled in the training program entitled &nbsp; “[program_name]”:&nbsp;</p>
            <p dir="ltr" style="text-align: left;">[trainee_details]<br></p>
            <p dir="ltr" style="text-align: left;">&nbsp;which will start on [offering_startdate] [offering_starttime] and end on [offering_enddate] [offering_endtime] </p>
            <p dir="ltr" style="text-align: left;">Thanks<br></p>
            <p dir="ltr" style="text-align: left;"><br></p>
            <p dir="ltr" style="text-align: left;"><br></p>
            <p dir="ltr" style="text-align: left;"><br></p>',
            'arabic_subject' =>'التسجيل الجماعي [program_name]',
            'arabic_body' => 'عزيزي [org_off]،<br><br>&nbsp;نود إعلامكم بأنه قد تم تسجيل المتدربين في البرنامج التدريبي "[program_name]"، وهم<br><br>,[trainee_details]<br><br>والذي سيبدأ في [offering_startdate] [offering_starttime] وينتهي بتاريخ [offering_enddate] [offering_endtime]<br>&nbsp;شكرًا',
            'notification_type_shortname'=>'bulkenrol_program'
        );
        $notification_typeinfo = $DB->get_record('local_notification_type', array('shortname' =>$notification_info_data['notification_type_shortname']),'id,pluginname');
        if($notification_typeinfo){
         
            if(!$DB->record_exists('local_notification_info', array('notificationid'=>$notification_typeinfo->id))){
                $notification_info_data['moduletype'] = $notification_typeinfo->pluginname;
                $notification_info_data['notificationid']=$notification_typeinfo->id;
                $notification_info_data['usercreated'] = 2;
                $notification_info_data['timecreated'] = $time;
                $DB->insert_record('local_notification_info', $notification_info_data);
            }
         }

            upgrade_plugin_savepoint(true,2017111306.3, 'local', 'notifications');



         }

         if ($oldversion <  2017111306.6) {     
            $time = time();
            $parentid = $DB->get_field('local_notification_type', 'id', array('shortname' => 'trainingprogram')); 
            $notificationtypedata = 
            array(
            'name' => ' Trainer Program Enrollment',
            'arabicname' => 'التسجيل في البرنامج',  
            'shortname' => 'trainer_tp_enrollment',
            'parent_module' => $parentid,
            'usercreated' => '2',
            'timecreated' => $time,
            'usermodified' => 2,
            'timemodified' => NULL, 
            'pluginname' => 'trainingprogram',
            'plugintype' => 'local'
            );
            $DB->insert_record('local_notification_type',  $notificationtypedata );
            $time = time();
            $notification_info_data =  array(
                'subject' => 'Enrolment at [program_name]',
                'body' => '<p dir="ltr" style="text-align: left;">Dear [program_userfullname],&nbsp;</p>
                <p dir="ltr" style="text-align: left;">We would like to inform you that you have been enrolled at [program_name] training program.&nbsp;</p>
                <p dir="ltr" style="text-align: left;">Thanks<br></p>',
                'arabic_subject' =>'التسجيل البرنامج التدريبي [program_name]',
                'arabic_body' => '<p dir="ltr" style="text-align: left;">عزيزي [program_userfullname] ،</p>
                <p dir="ltr" style="text-align: left;">&nbsp;نود إشعاركم بأنه قد تم تسجيلك في البرنامج التدريبي [program_name].</p>
                <p dir="ltr" style="text-align: left;">&nbsp;شكرًا<br></p>',
                'notification_type_shortname'=>'trainer_tp_enrollment'
            );
            $notification_typeinfo = $DB->get_record('local_notification_type', array('shortname' =>$notification_info_data['notification_type_shortname']),'id,pluginname');
            if($notification_typeinfo){
            
                if(!$DB->record_exists('local_notification_info', array('notificationid'=>$notification_typeinfo->id))){
                    $notification_info_data['moduletype'] = $notification_typeinfo->pluginname;
                    $notification_info_data['notificationid']=$notification_typeinfo->id;
                    $notification_info_data['usercreated'] = 2;
                    $notification_info_data['timecreated'] = $time;
                    $DB->insert_record('local_notification_info', $notification_info_data);
                }
            }

            upgrade_plugin_savepoint(true, 2017111306.6, 'local', 'notifications');

       }   
       if ($oldversion <  2017111306.7) {     
        $time = time();
        $parentid = $DB->get_field('local_notification_type', 'id', array('shortname' => 'exams')); 
        $notificationtypedata = 
        array(
        'name' => 'Bulk Enroll Exam',
        'arabicname' => 'التسجيل في البرنامج',  
        'shortname' => 'bulkenrol_exam',
        'parent_module' => $parentid,
        'usercreated' => '2',
        'timecreated' => $time,
        'usermodified' => 2,
        'timemodified' => NULL, 
        'pluginname' => 'exams',
        'plugintype' => 'local'
        );
        $DB->insert_record('local_notification_type',  $notificationtypedata );
        $time = time();
        $notification_info_data =  array(
            'subject' => 'Enrolment at [exam_name]',
            'body' => '<p dir="ltr" style="text-align: left;">Dear [org_off],&nbsp;</p>
            <p dir="ltr" style="text-align: left;">We would like to inform you that the following trainees have enrolled in the exam entitled &nbsp; “[exam_name]”:&nbsp;</p>
            <p dir="ltr" style="text-align: left;">[trainee_details]<br></p>
            <p dir="ltr" style="text-align: left;">&nbsp;which will start on [exam_startdate] [exam_starttime] - [exam_endtime] </p>
            <p dir="ltr" style="text-align: left;">Thanks<br></p>
            <p dir="ltr" style="text-align: left;"><br></p>
            <p dir="ltr" style="text-align: left;"><br></p>
            <p dir="ltr" style="text-align: left;"><br></p>',
            'arabic_subject' =>'التسجيل البرنامج التدريبي [exam_name]',
            'arabic_body' => 'عزيزي [org_off]،<br><br>&nbsp;نود إعلامكم بأنه قد تم تسجيل المتدربين في البرنامج التدريبي "[exam_name]"، وهم<br><br>,[trainee_details]<br><br>والذي سيبدأ في [exam_startdate] [exam_starttime] - [exam_endtime] <br>&nbsp;شكرًا',
            'notification_type_shortname'=>'bulkenrol_exam'
        );
        $notification_typeinfo = $DB->get_record('local_notification_type', array('shortname' =>$notification_info_data['notification_type_shortname']),'id,pluginname');
        if($notification_typeinfo){
        
            if(!$DB->record_exists('local_notification_info', array('notificationid'=>$notification_typeinfo->id))){
                $notification_info_data['moduletype'] = $notification_typeinfo->pluginname;
                $notification_info_data['notificationid']=$notification_typeinfo->id;
                $notification_info_data['usercreated'] = 2;
                $notification_info_data['timecreated'] = $time;
                $DB->insert_record('local_notification_info', $notification_info_data);
            }
        }

        upgrade_plugin_savepoint(true, 2017111306.7, 'local', 'notifications');

   }   
   
   if ($oldversion <  2017111306.8) {     
        $time = time();
        $parentid = $DB->get_field('local_notification_type', 'id', array('shortname' => 'trainingprogram')); 
        $notificationtypedata = 
        array(
        'name' => 'Training Program Reschedule',
        'arabicname' => 'إعادة جدولة البرنامج التدريبي',  
        'shortname' => 'trainingprogram_reschedule',
        'parent_module' => $parentid,
        'usercreated' => '2',
        'timecreated' => $time,
        'usermodified' => 2,
        'timemodified' => NULL, 
        'pluginname' => 'trainingprogram',
        'plugintype' => 'local'
        );
        $DB->insert_record('local_notification_type',  $notificationtypedata );
        $time = time();
        $notification_info_data =  array(
            'subject' => 'Rescheduling Training Program [program_name]',
            'body' => '<p dir="ltr" style="text-align: left;">Dear [program_userfullname],&nbsp;</p>
            <p dir="ltr" style="text-align: left;">We would like to inform you that we rescheduled  the  [program_name] training program from [offering_pastdate][offering_pasttime] to [offering_presentdate][offering_presenttime].&nbsp;</p>
            <p dir="ltr" style="text-align: left;">Thanks<br></p>',
            'arabic_subject' =>'إعادة جدولة البرنامج التدريبي[program_name]',
            'arabic_body' => '<p dir="ltr" style="text-align: left;">عزيزي [program_userfullname] ،</p>
            <p dir="ltr" style="text-align: left;">&nbsp;نود اعلامكم بأننا قمنا بإعادة جدولة [program_name]برنامج تدريبي من[offering_pastdate][offering_pasttime] ل  [offering_presentdate][offering_presenttime].&nbsp; </p>
            <p dir="ltr" style="text-align: left;">&nbsp;شكرًا<br></p>',
            'notification_type_shortname'=>'trainingprogram_reschedule'
        );
        $notification_typeinfo = $DB->get_record('local_notification_type', array('shortname' =>$notification_info_data['notification_type_shortname']),'id,pluginname');
        if($notification_typeinfo){
        
            if(!$DB->record_exists('local_notification_info', array('notificationid'=>$notification_typeinfo->id))){
                $notification_info_data['moduletype'] = $notification_typeinfo->pluginname;
                $notification_info_data['notificationid']=$notification_typeinfo->id;
                $notification_info_data['usercreated'] = 2;
                $notification_info_data['timecreated'] = $time;
                $DB->insert_record('local_notification_info', $notification_info_data);
            }
        }

        $time = time();
        $parentid = $DB->get_field('local_notification_type', 'id', array('shortname' => 'trainingprogram')); 
        $notificationtypedata = 
        array(
        'name' => 'Training Program Cancel Request',
        'arabicname' => 'إعادة جدولة البرنامج التدريبي',  
        'shortname' => 'trainingprogram_cancelrequest',
        'parent_module' => $parentid,
        'usercreated' => '2',
        'timecreated' => $time,
        'usermodified' => 2,
        'timemodified' => NULL, 
        'pluginname' => 'trainingprogram',
        'plugintype' => 'local'
        );
        $DB->insert_record('local_notification_type',  $notificationtypedata );
        $time = time();
        $notification_info_data =  array(
            'subject' => 'Cancellation  [program_name]',
            'body' => '<p dir="ltr" style="text-align: left;">Dear [program_userfullname],&nbsp;</p>
            <p dir="ltr" style="text-align: left;">We would like to inform you that  training official [program_tofullname] has cancelled the [program_name] training program  on [program_canceltime] .&nbsp;</p>
            <p dir="ltr" style="text-align: left;">Thanks<br></p>',
            'arabic_subject' =>'إلغاء[program_name]',
            'arabic_body' => '<p dir="ltr" style="text-align: left;">عزيزي [program_userfullname] ،</p>
            <p dir="ltr" style="text-align: left;">&nbsp;نود اعلامكم بأن مسؤول التدريب    [program_tofullname] لقد ألغى  [program_name]برنامج تدريبي من [program_canceltime]  على   &nbsp; </p>
            <p dir="ltr" style="text-align: left;">&nbsp;شكرًا<br></p>',
            'notification_type_shortname'=>'trainingprogram_cancelrequest'
        );
        $notification_typeinfo = $DB->get_record('local_notification_type', array('shortname' =>$notification_info_data['notification_type_shortname']),'id,pluginname');
        if($notification_typeinfo){
        
            if(!$DB->record_exists('local_notification_info', array('notificationid'=>$notification_typeinfo->id))){
                $notification_info_data['moduletype'] = $notification_typeinfo->pluginname;
                $notification_info_data['notificationid']=$notification_typeinfo->id;
                $notification_info_data['usercreated'] = 2;
                $notification_info_data['timecreated'] = $time;
                $DB->insert_record('local_notification_info', $notification_info_data);
            }
        }

        upgrade_plugin_savepoint(true, 2017111306.8, 'local', 'notifications');

}                                
    


    return true;
}



