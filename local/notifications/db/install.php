<?php
/**
 * This file is part of eAbyas
 *
 * Copyright eAbyas Info Solutons Pvt Ltd, India
 *
 * This program is free software; you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation; either version 3 of the License, or
 * (at your option) any later version.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with this program.  If not, see <http://www.gnu.org/licenses/>.
 *
 * @author eabyas  <info@eabyas.in>
 * @package local_notification
 */
defined('MOODLE_INTERNAL') || die();
function xmldb_local_notifications_install(){
    global $CFG,$DB;
    /*notifictaions content*/
    $dbman = $DB->get_manager(); // loads ddl manager and xmldb classes
    $table = new xmldb_table('local_notification_type');
    if (!$dbman->table_exists($table)) {
        $table->add_field('id', XMLDB_TYPE_INTEGER, '10', null, XMLDB_NOTNULL, XMLDB_SEQUENCE, null);
        $table->add_field('name', XMLDB_TYPE_CHAR, '255', null, XMLDB_NOTNULL, null, '0');
        $table->add_field('arabicname', XMLDB_TYPE_CHAR, '255', null, XMLDB_NOTNULL, null, '0');
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
        $table->add_field('arabic_subject', XMLDB_TYPE_CHAR, '255', null, XMLDB_NOTNULL, null, '0');
        $table->add_field('arabic_body', XMLDB_TYPE_TEXT, null, null, XMLDB_NOTNULL, null, '0');
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
        $table->add_field('realuser', XMLDB_TYPE_INTEGER, '10', null, XMLDB_NOTNULL, null, '0');        
        $table->add_field('from_emailid', XMLDB_TYPE_CHAR, '255', null, XMLDB_NOTNULL, null, null);
        $table->add_field('to_emailid', XMLDB_TYPE_CHAR, '255', null, XMLDB_NOTNULL, null, null);
        $table->add_field('ccusers', XMLDB_TYPE_CHAR, '255', null, null, null,null, null);
        $table->add_field('moduletype', XMLDB_TYPE_CHAR, '255', null, XMLDB_NOTNULL, null, null);
        $table->add_field('moduleid', XMLDB_TYPE_TEXT, null, null, XMLDB_NOTNULL, null, null);
        $table->add_field('teammemberid', XMLDB_TYPE_INTEGER, '10', null, null, null, '0');
        // courses
        $table->add_field('reminderdays', XMLDB_TYPE_INTEGER, '10', null, XMLDB_NOTNULL, null, '0');
        $table->add_field('enable_cc', XMLDB_TYPE_INTEGER, '1', null, XMLDB_NOTNULL, null, '0');
        $table->add_field('active', XMLDB_TYPE_INTEGER, '1', null, XMLDB_NOTNULL, null, '0');
        $table->add_field('subject', XMLDB_TYPE_CHAR, '255', null, XMLDB_NOTNULL, null, '0');
        $table->add_field('emailbody', XMLDB_TYPE_TEXT, null, null, XMLDB_NOTNULL, null, '0');
        $table->add_field('arabic_subject', XMLDB_TYPE_CHAR, '255', null, XMLDB_NOTNULL, null, '0');
        $table->add_field('arabic_body', XMLDB_TYPE_TEXT, null, null, XMLDB_NOTNULL, null, '0');
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
    $initcontent = array('name' => 'User approval','arabicname' => 'إشعارات الموافقة على المستخدمين','shortname' => 'userapproval','parent_module' => '0','usercreated' => '2','timecreated' => $time,'usermodified' => 2,'timemodified' => NULL, 'pluginname' => 'userapproval','plugintype' => 'local');
    $parentid = $DB->get_field('local_notification_type', 'id', array('shortname' => 'userapproval'));
    if(!$parentid){
        $parentid = $DB->insert_record('local_notification_type', $initcontent);
    }
    $notification_type_data = array(
        array('name' => 'Registration','arabicname' => 'التسجيل','shortname' => 'registration','parent_module' => $parentid,'usercreated' => '2','timecreated' => $time,'usermodified' => 2,'timemodified' => NULL, 'pluginname' => 'userapproval','plugintype' => 'local'),
        array('name' => 'Approve','arabicname' => 'الموافقة على مستخدم','shortname' => 'approve','parent_module' => $parentid,'usercreated' => '2','timecreated' => $time,'usermodified' => 2,'timemodified' => NULL, 'pluginname' => 'userapproval','plugintype' => 'local'),
        array('name' => 'Reject','arabicname' => 'رفض مستخدم','shortname' => 'reject','parent_module' => $parentid,'usercreated' => '2','timecreated' => $time,'usermodified' => 2,'timemodified' => NULL, 'pluginname' => 'userapproval','plugintype' => 'local'),
        array('name' => 'Organization Approval','arabicname' => 'موافقة للجهة','shortname' => 'organizational_approval','parent_module' => $parentid,'usercreated' => '2','timecreated' => $time,'usermodified' => 2,'timemodified' => NULL, 'pluginname' => 'userapproval','plugintype' => 'local'),
       //array('name' => 'Update Hall','shortname' => 'update_trainingprogram','parent_module' => $parentid,'usercreated' => '2','timecreated' => $time,'usermodified' => 2,'timemodified' => NULL, 'pluginname' => 'trainingprogram','plugintype' => 'local'),

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
            'subject' => 'Registration Approval',
            'body' => '<p dir="ltr" style="text-align: left;">Dear [user_fullname],&nbsp;</p><p dir="ltr" style="text-align: left;">your registration at Financial Academy has been approved.</p><p dir="ltr" style="text-align: left;">&nbsp;Thanks<br></p>',
            'arabic_subject' =>'الموافقة على التسجيل ',
            'arabic_body' => '<p dir="ltr" style="text-align: left;">عزيزي [user_fullname]،&nbsp;</p><p dir="ltr" style="text-align: left;">تمت الموافقة على تسجيلك في الأكاديمية المالية. شكرًا<br></p>',
            'notification_type_shortname'=>'registration'
        ),

        array(
            'subject' => 'Request is approved',
            'body' => '<p dir="ltr" style="text-align: left;">&nbsp;Dear [user_fullname],&nbsp;</p><p dir="ltr" style="text-align: left;">The sent request has been approved.&nbsp;</p><p dir="ltr" style="text-align: left;">Thanks<br></p>','arabic_subject' =>'تمت الموافقة على الطلب',
            'arabic_body' => '<p dir="ltr" style="text-align: left;">&nbsp;عزيزي [user_fullname] ،</p><p dir="ltr" style="text-align: left;">&nbsp;تمت الموافقة على الطلب المرسل. شكرًا.<br></p>',
            'notification_type_shortname'=>'approve'
        ),

        array(
            'subject' => 'Request is rejected',
            'body' => '<p dir="ltr" style="text-align: left;">Dear [user_fullname],</p><p dir="ltr" style="text-align: left;">&nbsp;The sent request has been rejected.&nbsp;</p><p dir="ltr" style="text-align: left;">Thanks.<br></p>',
            'arabic_subject' =>'تم رفض الطلب',
            'arabic_body' => '<p dir="ltr" style="text-align: left;">عزيزي [user_fullname]،</p><p dir="ltr" style="text-align: left;">&nbsp;تم رفض الطلب المرسل. شكرًا.<br></p>',
            'notification_type_shortname'=>'reject'
        ),
        array(
            'subject' => '[user_organization] approval',
            'body' => '<p dir="ltr" style="text-align: left;">Dear [user_fullname],&nbsp;</p><p dir="ltr" style="text-align: left;">[user_organization] organization registration at Financial Academy has been approved.&nbsp;</p><p dir="ltr" style="text-align: left;">Thanks<br></p>',
            'arabic_subject' =>'تسجيل الجهة [user_organization]',
            'arabic_body' => '<p dir="ltr" style="text-align: left;">عزيزي [user_fullname] ،&nbsp;</p><p dir="ltr" style="text-align: left;">تمت الموافقة على تسجيل الجهة [user_organization] في الأكاديمية المالية. شكرًا<br></p>','notification_type_shortname'=>'organizational_approval'
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
    $time = time();
    $initcontent = array('name' => 'Traning Program','arabicname' => 'إشعارات البرنامج التدريبي','shortname' => 'trainingprogram','parent_module' => '0','usercreated' => '2','timecreated' => $time,'usermodified' => 2,'timemodified' => NULL, 'pluginname' => 'trainingprogram');
    $parentid = $DB->get_field('local_notification_type', 'id', array('shortname' => 'trainingprogram'));
    if(!$parentid){
        $parentid = $DB->insert_record('local_notification_type', $initcontent);
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
        array('name' => 'Unenrollment','arabicname' => ' التسجيل في البرنامج','shortname' => 'trainingprogram_unenroll','parent_module' => $parentid,'usercreated' => '2','timecreated' => $time,'usermodified' => 2,'timemodified' => NULL,'pluginname' => 'trainingprogram','plugintype' => 'local' )
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
        array(
            'subject' => 'Training Session Reminder ',
            'body' => '<p dir="ltr" style="text-align: left;">Dear [trainingprogram_userfullname],</p><p dir="ltr" style="text-align: left;">&nbsp;The training session  for  [trainingprogram_related_module_name] is about to start.<br></p>',
            'arabic_subject' =>'تذكير حضور الجلسة التدريبية',
            'arabic_body' => '<p dir="ltr" style="text-align: left;">عزيزي  [trainingprogram_userfullname],&nbsp;</p><p dir="ltr" style="text-align: left;">&nbsp;الجلسة التدريبية لبرنامج [trainingprogram_related_module_name] على وشك البدء.<br></p>',
            'notification_type_shortname'=>'trainingprogram_before_30_minutes'
        ), 
        array(
            'subject' => 'Action Required -[program_name]',
            'body' => '<p dir="ltr" style="text-align: left;">Dear [program_userfullname],&nbsp;</p><p dir="ltr" style="text-align: left;"><span style="font-size: 0.9375rem;">You are enrolled in [program_name]  but your account has been inactive from a while. Please go to the course from the following link:  [program_link].&nbsp;</span></p><p dir="ltr" style="text-align: left;"><span style="font-size: 0.9375rem;">Thanks.</span></p>',
            'arabic_subject' =>'يتطلب إجراء - [program_name]',
            'arabic_body' => '<p dir="ltr" style="text-align: left;">عزيزي [program_userfullname] ،</p><p dir="ltr" style="text-align: left;">&nbsp;أنت مسجل في [program_name] ولكن حسابك  غير نشط منذ فترة. يرجى الذهاب إلى الدورة من خلال الرابط التالي: [program_link].&nbsp;</p><p dir="ltr" style="text-align: left;">شكرًا.<br></p>',
            'notification_type_shortname'=>'trainingprogram_enrolled_inactive_accounts'
        ), 
        array(
            'subject' => ' Assessment is opened.',
            'body' => '<p dir="ltr" style="text-align: left;"></p><div>Dear [program_userfullname],</div><div><br></div><div>&nbsp;We would like to inform you that you can submit your assessment for [program_name]  through the following link: [assessment_link], starting from today [assessment_start_date] until [assessment_end_date] .</div><div><br></div><div>Thanks.</div><br><br><p></p>',
            'arabic_subject' =>'فتح التقييم',
            'arabic_body' => '<p dir="ltr" style="text-align: left;"></p><div>&nbsp;عزيزي [program_userfullname] ،</div><div><br></div><div>&nbsp;نود إشعاركم بإنه يمكنكم تقييم البرنامج التدريبي [program_name] من خلال الرابط التالي: [assessment_link], ، بدءًا من اليوم [assessment_start_date] وحتى [assessment_end_date].&nbsp;</div><div><br></div><div>شكرًا</div><div><br></div><br><p></p>',
            'notification_type_shortname'=>'trainingprogram_pre_assessment_opened'
        ), 
        array(
            'subject' => ' Assessment is opened.',
            'body' => '<p dir="ltr" style="text-align: left;"></p><div>Dear [program_userfullname],</div><div><br></div><div>&nbsp;We would like to inform you that you can submit your assessment for [program_name]  through the following link: [assessment_link], starting from today [assessment_start_date] until [assessment_end_date] .</div><div><br></div><div>Thanks.</div><br><br><p></p>',
            'arabic_subject' =>'فتح التقييم',
            'arabic_body' => '<p dir="ltr" style="text-align: left;"></p><div>&nbsp;عزيزي [program_userfullname] ،</div><div><br></div><div>&nbsp;نود إشعاركم بإنه يمكنكم تقييم البرنامج التدريبي [program_name] من خلال الرابط التالي: [assessment_link], ، بدءًا من اليوم [assessment_start_date] وحتى [assessment_end_date].&nbsp;</div><div><br></div><div>شكرًا</div><div><br></div><br><p></p>',
            'notification_type_shortname'=>'trainingprogram_post_assessment_opened'
        ), 
        array(
            'subject' => ' Assessment is closed.',
            'body' => '<p dir="ltr" style="text-align: left;">Dear [program_userfullname],&nbsp;</p><p dir="ltr" style="text-align: left;">We would like to inform you that the deadline for submitting  the assessment for [program_name]  has passed.&nbsp;</p><p dir="ltr" style="text-align: left;">Thanks.<br></p>',
            'arabic_subject' =>'فإغلاق التقييم',
            'arabic_body' => '<p dir="ltr" style="text-align: left;">&nbsp;عزيزي&nbsp;[program_userfullname] ،</p><p dir="ltr" style="text-align: left;">&nbsp;نود إشعاركم بانتهاء الموعد النهائي لإرسال تقييم البرنامج التدريبي [program_name].</p><p dir="ltr" style="text-align: left;">&nbsp;شكرًا..<br></p>',
            'notification_type_shortname'=>'trainingprogram_pre_assessment_closed'
        ), 
        array(
            'subject' => ' Assessment is closed.',
            'body' => '<p dir="ltr" style="text-align: left;">Dear [program_userfullname],&nbsp;</p><p dir="ltr" style="text-align: left;">We would like to inform you that the deadline for submitting  the assessment for [program_name]  has passed.&nbsp;</p><p dir="ltr" style="text-align: left;">Thanks.<br></p>',
            'arabic_subject' =>'فإغلاق التقييم',
            'arabic_body' => '<p dir="ltr" style="text-align: left;">&nbsp;عزيزي&nbsp;[program_userfullname] ،</p><p dir="ltr" style="text-align: left;">&nbsp;نود إشعاركم بانتهاء الموعد النهائي لإرسال تقييم البرنامج التدريبي [program_name].</p><p dir="ltr" style="text-align: left;">&nbsp;شكرًا..<br></p>',
            'notification_type_shortname'=>'trainingprogram_post_assessment_closed'
        ),
        array(
            'subject' => ' Assignments Deadlines -[program_name]',
            'body' => '<p dir="ltr" style="text-align: left;"></p><div> Dear [program_userfullname],&nbsp;</div><div><br></div><div>Please be aware that the assignments deadlines for [program_name] will be in 4  Hours.</div><br><br><p></p>',
            'arabic_subject' =>'فالموعد النهائي للواجبات - [program_name]',
            'arabic_body' => '<p dir="ltr" style="text-align: left;">&nbsp;عزيزي [program_name]،</p><p dir="ltr" style="text-align: left;">&nbsp;يرجى الانتباه  أن الموعد النهائي للواجبات المتعلقة ببرنامج ,[program_userfullname] ستكون خلال 4  ساعات.<br></p>',
            'notification_type_shortname'=>'trainingprogram_assignment_deadline_4_hours'
        ),
        array(
            'subject' => ' Assignments Deadlines -[program_name]',
            'body' => '<p dir="ltr" style="text-align: left;"></p><div> Dear [program_userfullname],&nbsp;</div><div><br></div><div>Please be aware that the assignments deadlines for [program_name] will be in 24  Hours.</div><br><br><p></p>',
            'arabic_subject' =>'فالموعد النهائي للواجبات - [program_name]',
            'arabic_body' => '<p dir="ltr" style="text-align: left;">&nbsp;عزيزي [program_name]،</p><p dir="ltr" style="text-align: left;">&nbsp;يرجى الانتباه  أن الموعد النهائي للواجبات المتعلقة ببرنامج ,[program_userfullname] ستكون خلال 24  ساعات.<br></p>',
            'notification_type_shortname'=>'trainingprogram_assignment_deadline_24_hours'
        ),
        array(
            'subject' => 'Cancel Training Program [program_name]',
            'body' => '<p dir="ltr" style="text-align: left;">Dear [program_userfullname],&nbsp;</p><p dir="ltr" style="text-align: left;">We would like to inform you that you are unenrolled in [program_name] at [program_date] [program_time]&nbsp;.&nbsp;</p><p dir="ltr" style="text-align: left;">Thanks<br></p><p dir="ltr" style="text-align: left;"><br></p><p dir="ltr" style="text-align: left;"><br></p>',
            'arabic_subject' =>'إلغاء برنامج تدريبي  [program_name]',
            'arabic_body' => '<p dir="ltr" style="text-align: left;">عزيزي [program_userfullname]&nbsp; ،<br></p><p dir="ltr" style="text-align: left;">&nbsp;نود إشعاركم بأنه قد تم إلغاء تسجيلك في [program_name]. الموافق [program_date] [program_time].</p><p dir="ltr" style="text-align: left;">&nbsp;شكرًا.<br></p>',
            'notification_type_shortname'=>'trainingprogram_unenroll'
        )

        
        
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
    $time = time();
    $initcontent = array('name' => 'Questionbank','arabicname' => 'إشعارات بنك الأسئلة','shortname' => 'questionbank','parent_module' => '0','usercreated' => '2','timecreated' => $time,'usermodified' => 2,'timemodified' => NULL, 'pluginname' => 'questionbank','plugintype' => 'local');
    $parentid = $DB->get_field('local_notification_type', 'id', array('shortname' => 'questionbank'));
    if(!$parentid){
        $parentid = $DB->insert_record('local_notification_type', $initcontent);
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
        unset($notification_type['timecreated']);
        if(!$DB->record_exists('local_notification_type',  $notification_type)){
            $notification_type['timecreated'] = $time;
            $DB->insert_record('local_notification_type', $notification_type);
        }
    }
    $time = time();
    $notification_info_data = array(
        array(
            'subject' => '[WorkshopName] is Created',
            'body' => '<p dir="ltr" style="text-align: left;"></p><p>Dear [FullName],<br></p><p>The [WorkshopName] is created for the [QuestionBankName] at [WorkshopDate] [WorkshopTime] .<br></p><p>Thanks</p><br><p></p>',
            'arabic_subject' =>' تم إنشاء [WorkshopName] ',
            'arabic_body' => '<p dir="ltr" style="text-align: left;"></p><p dir="ltr">عزيزي [FullName] ،</p><p dir="ltr">&nbsp;لقد تم إنشاء [WorkshopName] لـ [QuestionBankName] الموافق [WorkshopDate] [WorkshopTime].</p><p dir="ltr">&nbsp;شكرًا</p><br><p></p>',
            'notification_type_shortname'=>'questionbank_workshop_created'
        ),
        array(
            'subject' => '[WorkshopName] is Updated',
            'body' => '<p dir="ltr" style="text-align: left;"></p><p>Dear [FullName],<br></p><p>The [WorkshopName] is updated for the [QuestionBankName] at [WorkshopDate] [WorkshopTime] .<br></p><p>Thanks</p><br><p></p>',
            'arabic_subject' =>' تم تحديث [WorkshopName]',
            'arabic_body' => '<p dir="ltr" style="text-align: left;"></p><p>عزيزي [FullName]،</p><p>&nbsp;لقد تم تحديث [WorkshopName] لـ [QuestionBankName] الموافق [WorkshopDate] [WorkshopTime].</p><p>&nbsp;شكرًا</p><p><br></p><br><p></p>',
            'notification_type_shortname'=>'questionbank_workshop_updated'
        ),
        array(
            'subject' => 'Assigning expert for [QuestionBankName]',
            'body' => '<p dir="ltr" style="text-align: left;"></p><p>Dear [FullName],</p><p>&nbsp;[ExpertName] has been assigned as expert for [QuestionBankName] .<br></p><p>Thanks</p><br><br><p></p>',
            'arabic_subject' =>'تعيين خبير لـ [QuestionBankName]',
            'arabic_body' => '<p dir="ltr" style="text-align: left;"></p><p>عزيزي [FullName] ،</p><p>&nbsp;تم تعيين [ExpertName] كخبير في [QuestionBankName].</p><p>&nbsp;شكرًا</p><br><p></p>',
            'notification_type_shortname'=>'questionbank_assign_expert'
        ),
        array(
            'subject' => 'Assigning exam official for [QuestionBankName]', 
            'body' => '<p dir="ltr" style="text-align: left;"></p><p>Dear [FullName],</p><p>&nbsp;[ExamofficialName] has been assigned as exam official for [QuestionBankName] .<br></p><p>Thanks</p><br><p></p>',
            'arabic_subject' =>'تعيين مسؤول اختبار لـ [QuestionBankName]',
            'arabic_body' => '<p dir="ltr" style="text-align: left;"></p><p dir="ltr">عزيزي [FullName] ،</p><p dir="ltr">&nbsp;تم تعيين [ExamofficialName] كمسؤول اختبار&amp;nbsp; في [QuestionBankName].</p><p dir="ltr">&nbsp;شكرًا</p><br><p></p>',
            'notification_type_shortname'=>'questionbank_assign_exam_official'
        ),   
        array(
            'subject' => 'Assigning reviewer for [QuestionBankName]',
            'body' => '<p dir="ltr" style="text-align: left;"></p><p dir="ltr">Dear [FullName],</p><p dir="ltr">[ReviewerName] has been assigned as reviewer for Question [QuestionText] in [QuestionBankName] .<br></p><p dir="ltr">Thanks</p><br><p></p>',
            'arabic_subject' =>'تعيين مراجع لـ [QuestionBankName]',
            'arabic_body' => '<p dir="ltr" style="text-align: left;"></p><p>عزيزي [FullName] ،</p><p>&nbsp;تم تعيين [ReviewerName] كمراجع في [QuestionBankName].</p><p>&nbsp;شكرًا</p><br><p></p>',
            'notification_type_shortname'=>'questionbank_assign_reviewer'
        ),
        array(
            'subject' => 'Question is under review',
            'body' => '<p dir="ltr" style="text-align: left;"></p><p dir="ltr">Dear [FullName],<br></p><p dir="ltr">The question [QuestionText] is under review.<br></p><p dir="ltr">Thanks</p><br><p></p>',
            'arabic_subject' =>'السؤال قيد المراجعة',
            'arabic_body' => '<p dir="ltr" style="text-align: left;"></p><p dir="ltr">عزيزي [FullName] ،</p><p dir="ltr">&nbsp;السؤال [QuestionText] قيد المراجعة. شكرًا</p><br><p></p>',
            'notification_type_shortname'=>'questionbank_question_under_review'
        ), 
        array(
            'subject' => 'Question is reviewed',
            'body' => '<p dir="ltr" style="text-align: left;"></p><p dir="ltr">Dear [FullName],<br></p><p dir="ltr">The question [QuestionText] has been reviewed by [ReviewerName].<br></p><p dir="ltr">Thanks</p><br><p></p>',
            'arabic_subject' =>'تمت مراجعة السؤال',
            'arabic_body' => '<p dir="ltr" style="text-align: left;"></p><p dir="ltr">عزيزي [FullName] ،</p><p dir="ltr">&nbsp;تمت مراجعة السؤال [QuestionText] بواسطة [ReviewerName].</p><p dir="ltr">&nbsp;شكرًا</p><br><p></p>',
            'notification_type_shortname'=>'questionbank_question_reviewed' 
        ),
        array(
            'subject' => 'Question is added to question bank',
            'body' => '<p dir="ltr" style="text-align: left;"></p><p dir="ltr">Dear [FullName],<br></p><p dir="ltr">The Question [QuestionText] has been&amp;nbsp; added to [QuestionBank].<br></p><p dir="ltr">Thanks</p><br><p></p>',
            'arabic_subject' =>'تمت إضافة السؤال لبنك الأسئلة',
            'arabic_body' => '<p dir="ltr" style="text-align: left;"></p><p dir="ltr">عزيزي [FullName] ،</p><p dir="ltr">&nbsp;تمت إضافة السؤال [QuestionText] إلى [QuestionBank].</p><p dir="ltr">&nbsp;شكرًا</p><p dir="ltr"><br></p><br><p></p>',
            'notification_type_shortname'=>'questionbank_question_added'
        ), 
        array(
            'subject' => '[RelatedModuleName] is Update',
            'body' => '<p dir="ltr" style="text-align: left;"></p><p dir="ltr">Dear [FullName] ,<br></p><p dir="ltr">We would like to inform you about the new update on [RelatedModuleName]. You can display the details from the following link [RelatedModulesLink].<br></p><p dir="ltr">Thanks</p><br><p></p>',
            'arabic_subject' =>'تحديث [RelatedModuleName] ',
            'arabic_body' => '<p dir="ltr" style="text-align: left;"></p><p>عزيزي [FullName]،</p><p>&nbsp;نود إشعاركم&amp;nbsp; أنه تم تحديث [RelatedModuleName]&amp;nbsp;يمكنكم استعراض التفاصيل من خلال الرابط التالي [RelatedModulesLink].</p><p>&nbsp;شكرا</p><br><p></p> ',
            'notification_type_shortname'=>'questionbank_onchange'
        ), 
        array(
            'subject' => '[RelatedModuleName] Cancellation ',
            'body' => '<p dir="ltr" style="text-align: left;"></p><p dir="ltr">Dear [FullName] ,</p><p dir="ltr">&nbsp;We are sorry to inform you about the cancellation of [RelatedModuleName].<br></p><p dir="ltr">Thanks</p><br><p></p>',
            'arabic_subject' =>'إلغاء [RelatedModuleName]',
            'arabic_body' => '<p dir="ltr" style="text-align: left;"></p><p dir="ltr">عزيزي [FullName]،</p><p dir="ltr">&nbsp; نأسف بإشعاركم بـخصوص إلغاء [RelatedModuleName].<br></p><p dir="ltr">شكرًا لك.</p><br><p></p>',
            'notification_type_shortname'=>'questionbank_cancel'
        ),
        array(
            'subject' => '[RelatedModuleName] Rescheduling ',
            'body' => '<p dir="ltr" style="text-align: left;"></p><p dir="ltr">Dear [FullName] ,</p><p dir="ltr">&nbsp;We would like to inform you about the rescheduling of [RelatedModuleName]. You can display the details from the following link [ProgramLink].<br></p><p dir="ltr">Thanks</p><br><p></p>',
            'arabic_subject' =>'إعادة الجدولة [RelatedModuleName] ',
            'arabic_body' => '<p dir="ltr" style="text-align: left;"></p><p dir="ltr">عزيزي [FullName]،</p><p dir="ltr">&nbsp;نود إشعاركم بـخصوص أنه تمت عملية إعاده جدولة [RelatedModuleName] يمكنكم استعراض التفاصيل من خلال الرابط التالي [ProgramLink].</p><p dir="ltr">&nbsp; شكرا</p><br><p></p>',
            'notification_type_shortname'=>'questionbank_reschedule'
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
    $time = time();
    $initcontent = array('name' => 'Organizations','arabicname' => 'إشعارات الجهات','shortname' => 'organizations','parent_module' => '0','usercreated' => '2','timecreated' => $time,'usermodified' => 2,'timemodified' => NULL, 'pluginname' => 'organization','plugintype' => 'local');
    $parentid = $DB->get_field('local_notification_type', 'id', array('shortname' => 'organization'));
    if(!$parentid){
        $parentid = $DB->insert_record('local_notification_type', $initcontent);
    }
    $notification_type_data = array(
        array('name' => 'Organization Registration','arabicname' => ' تسجيل الجهة','shortname' => 'organization_registration','parent_module' => $parentid,'usercreated' => '2','timecreated' => $time,'usermodified' => 2,'timemodified' => NULL, 'pluginname' => 'organization','plugintype' => 'local'),
        array('name' => 'Assigning Official','arabicname' => 'تعيين مسؤول جهة','shortname' => 'organization_assigning_official','parent_module' => $parentid,'usercreated' => '2','timecreated' => $time,'usermodified' => 2,'timemodified' => NULL, 'pluginname' => 'organization','plugintype' => 'local'),
        array('name' => 'Assigning Trainee','arabicname' => 'تعيين متدرب','shortname' => 'organization_assigning_trainee','parent_module' => $parentid,'usercreated' => '2','timecreated' => $time,'usermodified' => 2,'timemodified' => NULL, 'pluginname' => 'organization','plugintype' => 'local'),
        array('name' => 'Enrollment','arabicname' => 'التسجيل للجهات','shortname' => 'organization_enrollment','parent_module' => $parentid,'usercreated' => '2','timecreated' => $time,'usermodified' => 2,'timemodified' => NULL, 'pluginname' => 'organization','plugintype' => 'local'),
       array('name' => 'Wallet Update','arabicname' => ' تحديث المحفظة','shortname' => 'organization_wallet_update','parent_module' => $parentid,'usercreated' => '2','timecreated' => $time,'usermodified' => 2,'timemodified' => NULL, 'pluginname' => 'organization','plugintype' => 'local'),
       
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
            'subject' => '[organization_name] Registration request',
            'body' => '<p dir="ltr" style="text-align: left;">Dear [user_fullname],&nbsp;</p><p dir="ltr" style="text-align: left;">We would like to inform you that we received your request to register your organization named [organization_name]. You can Display your Request and follow up on its status from the system.&nbsp;</p><p dir="ltr" style="text-align: left;">Thanks.</p><div id="fitem_id_body"><div><br></div></div><br><p></p>',
            'arabic_subject' =>'  طلب تسجيل [organization_name]',
            'arabic_body' => '<p dir="ltr" style="text-align: left;">عزيزي[user_fullname] ،</p><p dir="ltr" style="text-align: left;">&nbsp;نود إشعاركم بأنه قد تلقينا طلبك لتسجيل منظمتك&nbsp; &nbsp;[organization_name]. يمكنك عرض ومتابعة حالة الطلب من خلال النظام. شكرًا.<br><br></p>',
            'notification_type_shortname'=>'organization_registration'
        ),
        array(
            'subject' => 'Assigning official at [organization_name]',
            'body' => '<p dir="ltr" style="text-align: left;">Dear [user_fullname],</p><p dir="ltr" style="text-align: left;">&nbsp;[organization_official_name] has been assigned as official at your organization [organization_name].</p><p dir="ltr" style="text-align: left;">&nbsp;Thanks<br></p>',
            'arabic_subject' =>' تعيين مسؤول في [organization_name]',
            'arabic_body' => '<p dir="ltr" style="text-align: left;">عزيزي [user_fullname]،</p><p dir="ltr" style="text-align: left;">&nbsp;تم تعيين [organization_official_name] كمسؤول في مؤسستك [organization_name].</p><p dir="ltr" style="text-align: left;">&nbsp;شكرًا<br><br></p>',
            'notification_type_shortname'=>'organization_assigning_official'
        ),
        array(
            'subject' => 'Assigning trainee at [organization_name]',
            'body' => '<p dir="ltr" style="text-align: left;">Dear [user_fullname],&nbsp;</p><p dir="ltr" style="text-align: left;">[organization_trainee_name] has been assigned as trainee at your organization [organization_name].&nbsp;</p><p dir="ltr" style="text-align: left;">Thanks<br></p>',
            'arabic_subject' =>'تعيين متدرب في [organization_name]',
            'arabic_body' => '<p dir="ltr" style="text-align: left;">عزيزي [user_fullname] ،</p><p dir="ltr" style="text-align: left;">&nbsp;تم تعيين [organization_trainee_name] كمتدرب في مؤسستك [organization_name].</p><p dir="ltr" style="text-align: left;">&nbsp;شكرًا<br></p>',
            'notification_type_shortname'=>'organization_assigning_trainee'
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
    $time = time();
    $initcontent = array('name' => 'Learning Tracks','arabicname' => 'إشعارات مسارات التعلم','shortname' => 'learningtracks','parent_module' => '0','usercreated' => '2','timecreated' => $time,'usermodified' => 2,'timemodified' => NULL, 'pluginname' => 'learningtracks','plugintype' => 'local');
    $parentid = $DB->get_field('local_notification_type', 'id', array('shortname' => 'learningtracks'));
    if(!$parentid){
        $parentid = $DB->insert_record('local_notification_type', $initcontent);
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
        unset($notification_type['timecreated']);
        if(!$DB->record_exists('local_notification_type',  $notification_type)){
            $notification_type['timecreated'] = $time;
            $DB->insert_record('local_notification_type', $notification_type);
        }
    }
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

    $time = time();
    $initcontent = array('name' => 'Hall','arabicname' => 'إشعارات القاعات','shortname' => 'hall','parent_module' => '0','usercreated' => '2','timecreated' => $time,'usermodified' => 2,'timemodified' => NULL, 'pluginname' => 'hall','plugintype' => 'local');
    $parentid = $DB->get_field('local_notification_type', 'id', array('shortname' => 'hall'));
    if(!$parentid){
        $parentid = $DB->insert_record('local_notification_type', $initcontent);
    }
    $notification_type_data = array(
        array('name' => 'Hall Reservation','arabicname' => 'حجز القاعة','shortname' => 'hall_reservation','parent_module' => $parentid,'usercreated' => '2','timecreated' => $time,'usermodified' => 2,'timemodified' => NULL, 'pluginname' => 'hall','plugintype' => 'local'),
       //array('name' => 'Update Hall','shortname' => 'update_trainingprogram','parent_module' => $parentid,'usercreated' => '2','timecreated' => $time,'usermodified' => 2,'timemodified' => NULL, 'pluginname' => 'trainingprogram','plugintype' => 'local'),

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
            'subject' => '[HallName] Reservation',
            'body' => '<p dir="ltr" style="text-align: left;"></p><p dir="ltr">Dear [FullName],&nbsp; <br></p><p dir="ltr">The
            hall has been booked for [RelatedModuleName] .You can display your 
           reservation through the following link:&nbsp; [reservationLink].</p><p dir="ltr">&nbsp;Thanks<br></p><br><p></p>',
            'arabic_subject' =>'  [HallName] حجز',
            'arabic_body' => '<p dir="ltr" style="text-align: left;"></p><p dir="ltr">عزيزي [FullName] ،</p><p dir="ltr">&nbsp;لقد تم حجز القاعة لـ [RelatedModuleName]. يمكنك عرض حجزك من خلال الرابط التالي: [reservationLink].</p><p dir="ltr">&nbsp;شكرًا<br></p><br><p></p>',
            'notification_type_shortname'=>'hall_reservation'
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
    $time = time();
    $initcontent = array('name' => 'Exams','arabicname' => 'إشعارات الاختبار','shortname' => 'exams','parent_module' => '0','usercreated' => '2','timecreated' => $time,'usermodified' => 2,'timemodified' => NULL, 'pluginname' => 'exams');
    $parentid = $DB->get_field('local_notification_type', 'id', array('shortname' => 'exams'));
    if(!$parentid){
        $parentid = $DB->insert_record('local_notification_type', $initcontent);
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
       array('name' => 'After Session','arabicname' => ' بعد الجلسة','shortname' => 'exams_after_session','parent_module' => $parentid,'usercreated' => '2','timecreated' => $time,'usermodified' => 2,'timemodified' => NULL, 'pluginname' => 'exams','plugintype' => 'local'),
      
       array('name' => 'Unenrollment','arabicname' => ' التسجيل في البرنامج','shortname' => 'exam_unenroll','parent_module' => $parentid,'usercreated' => '2','timecreated' => $time,'usermodified' => 2,'timemodified' => NULL, 'pluginname' => 'exams','plugintype' => 'local'),
       array('name' => 'Reschedule','arabicname' => ' إعادة الجدولة','shortname' => 'exam_reschedule', 'parent_module' => $parentid,'usercreated' => '2','timecreated' => $time,'usermodified' => 2,'timemodified' => NULL, 'pluginname' => 'exams','plugintype' => 'local'),
       array('name' => 'Other Exam Register','arabicname' => 'سجل الامتحانات الأخرى', 'shortname' => 'other_exam_register','parent_module' => $parentid,'usercreated' => '2','timecreated' => $time,'usermodified' => 2,'timemodified' => NULL, 'pluginname' => 'exams','plugintype' => 'local'),
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
            'subject' => '[exam_name] is Created',
            'body' => '<p dir="ltr" style="text-align: left;">Dear [exam_userfullname],&nbsp;</p><p dir="ltr" style="text-align: left;">The exam titled:  [exam_name] has been successfully created.<br></p>',
            'arabic_subject' =>'إنشاء - تحديث اختبار [exam_name]',
            'arabic_body' => '<p dir="ltr" style="text-align: left;">عزيزي [exam_userfullname] ،</p><p dir="ltr" style="text-align: left;">&nbsp;تم بنجاح (إنشاء - تحديث) الاختبار [exam_name].<br></p>',
            'notification_type_shortname'=>'exams_create'
        ),
        array(
            'subject' => '[exam_name] is Updated',
            'body' => '<p dir="ltr" style="text-align: left;">Dear [exam_userfullname],</p><p dir="ltr" style="text-align: left;">&nbsp;The exam titled:  [exam_name] has been successfully updated.<br></p>',
            'arabic_subject' =>'إنشاء - تحديث اختبار [exam_name]',
            'arabic_body' => '<p dir="ltr" style="text-align: left;">عزيزي [exam_userfullname]،&nbsp;</p><p dir="ltr" style="text-align: left;">تم بنجاح (إنشاء - تحديث) الاختبار [exam_name].<br></p>',
            'notification_type_shortname'=>'exams_update'
        ),
        array(
            'subject' => 'Enrolment at [exam_name]',
            'body' => '<p dir="ltr" style="text-align: left;">Dear [exam_userfullname],&nbsp;</p><p dir="ltr" style="text-align: left;">&nbsp;We would like to inform you that you are enrolled in  [exam_name] exam at [exam_date] [exam_time].&nbsp;</p><p dir="ltr" style="text-align: left;">Thanks.<br></p>',
            'arabic_subject' =>'إنشاء - تحديث اختبار [exam_name]',
            'arabic_body' => '<p dir="ltr" style="text-align: left;">عزيزي [exam_userfullname] ،</p><p dir="ltr" style="text-align: left;">&nbsp;نود إشعاركم  بأنه قد تم تسجيلك في  [exam_name] الموافق  [exam_date] [exam_time].&nbsp;</p><p dir="ltr" style="text-align: left;">شكرًا.<br></p>',
            'notification_type_shortname'=>'exams_enrolment'
        ),
        array(
            'subject' => '[exam_name] Completion', 
            'body' => '<p dir="ltr" style="text-align: left;">Dear [exam_userfullname],</p><p dir="ltr" style="text-align: left;">&nbsp;Thank you for attending   [exam_name] exam , wish you all the best.&nbsp;</p><p dir="ltr" style="text-align: left;">Thanks.<br></p>',
            'arabic_subject' =>'إتمام اختبار [exam_name]',
            'arabic_body' => '<p dir="ltr" style="text-align: left;">عزيزي [exam_userfullname] ،&nbsp;</p><p dir="ltr" style="text-align: left;">شكرًا لك على حضور الاختبار [exam_name] ، نتمنى  لك كل التوفيق. شكرًا.<br></p><p dir="ltr" style="text-align: left;">شكرًا.<br></p>',
            'notification_type_shortname'=>'exams_completion'
        ),   
        array(
            'subject' => '[exam_name] Certificate',
            'body' => '<p dir="ltr" style="text-align: left;">Dear [exam_userfullname] ,&nbsp;</p><p dir="ltr" style="text-align: left;">The certificate for the exam  [exam_name] has been issued. You could download and share the certificate through the following link</p><p dir="ltr" style="text-align: left;">&nbsp;[exam_certificatelink] .&nbsp;</p><p dir="ltr" style="text-align: left;">Thanks<br></p>',
            'arabic_subject' =>'شهادة [exam_name]',
            'arabic_body' => '<p dir="ltr" style="text-align: left;">عزيزي [exam_userfullname] ،</p><p dir="ltr" style="text-align: left;">&nbsp;تم إصدار الشهادة لاختبار [exam_name]. يمكنك تنزيل الشهادة ومشاركتها من خلال الرابط التالي&nbsp;</p><p dir="ltr" style="text-align: left;"></p><div id="fitem_id_string_identifiers"><div data-fieldtype="static"><div>[exam_certificatelink]</div></div></div><div id="fitem_id_subject"><div><br></div></div><p></p><p dir="ltr" style="text-align: left;">شكرًا<br></p>',
            'notification_type_shortname'=>'exams_certificate_assignment'
        ),
        array(
            'subject' => '  Attendance reminder [exam_related_module_name]',
            'body' => '<p dir="ltr" style="text-align: left;">Dear [exam_userfullname], This is a reminder that [exam_related_module_name] will start after 7 days at [exam_related_module_date] [exam_related_module_time]. Thanks<br></p>',
            'arabic_subject' =>'تذكير حضور  [exam_related_module_name]',
            'arabic_body' => '<p dir="ltr" style="text-align: left;">عزيزي [exam_userfullname] ، هذا تذكير بأن [exam_related_module_name] سيبدأ بعد 7 أيام في تاريخ:&nbsp; [exam_related_module_date] [exam_related_module_time]. شكرًا<br></p>',
            'notification_type_shortname'=>'exams_before_7_days'
        ), 
        array(
            'subject' => 'Attendance reminder [exam_related_module_name]',
            'body' => '<p dir="ltr" style="text-align: left;">Dear [exam_userfullname], This is a reminder that [exam_related_module_name] will start after 48 hours at [exam_related_module_date] [exam_related_module_time]. Thanks<br><br></p>',
            'arabic_subject' =>'تذكير حضور [exam_related_module_name]',
            'arabic_body' => '<p dir="ltr" style="text-align: left;">عزيزي [exam_userfullname] ، هذا تذكير بأن [exam_related_module_name] سيبدأ بعد 48 ساعة في تاريخ: [exam_related_module_date] [exam_related_module_time]. شكرًا<br></p>',
            'notification_type_shortname'=>'exams_before_48_hours'
        ),
        array(
            'subject' => 'Attendance reminder [exam_related_module_name]',
            'body' => '<p dir="ltr" style="text-align: left;">Dear [exam_userfullname] , This is a reminder that [exam_related_module_name] will start after 24 hours at [exam_related_module_date] [exam_related_module_time]. Thanks<br></p>',
            'arabic_subject' =>'تذكير حضور [exam_related_module_name] ',
            'arabic_body' => '<p dir="ltr" style="text-align: left;">عزيزي [exam_userfullname] ، هذا تذكير بأن [exam_related_module_name] سيبدأ بعد 24 ساعة في تاريخ: [exam_related_module_date] [exam_related_module_time]. شكرًا<br></p>',
            'notification_type_shortname'=>'exams_before_24_hours'
        ), 
        array(
            'subject' => 'Thanks for Attending [exam_related_module_name]',
            'body' => '<p dir="ltr" style="text-align: left;">Dear [exam_userfullname],</p><p dir="ltr" style="text-align: left;">&nbsp;[exam_related_module_name] is done, Thank you for attending.<br></p>',
            'arabic_subject' =>'شكرًا لحضورك',
            'arabic_body' => '<p dir="ltr" style="text-align: left;">عزيزي [exam_userfullname] ،</p><p dir="ltr" style="text-align: left;">&nbsp;تم الانتهاء من [exam_related_module_name]، شكرًا لكم على الحضور.<br></p> ',
            'notification_type_shortname'=>'exams_send_conclusion'
        ), 
        array(
            'subject' => 'Cancel Exam  [exam_name]',
            'body' => '<p dir="ltr" style="text-align: left;">Dear [exam_userfullname],&nbsp;</p><p dir="ltr" style="text-align: left;">We would like to inform you that you are unenrolled in&nbsp; [exam_name]  at [exam_date] [exam_time] .&nbsp;</p><p dir="ltr" style="text-align: left;">Thanks<br></p><p dir="ltr" style="text-align: left;"><br></p><p dir="ltr" style="text-align: left;"><br></p><p dir="ltr" style="text-align: left;"><br></p>',
            'arabic_subject' =>'إلغاء اختبار  [exam_name]',
            'arabic_body' => '<p dir="ltr" style="text-align: left;">عزيزي [exam_userfullname]،&nbsp;<br></p><p dir="ltr" style="text-align: left;">&nbsp;نود إشعاركم بأنه قد تم إلغاء تسجيلك في[exam_name].الموافق &nbsp;[exam_date] [exam_time]&nbsp;.&nbsp;</p><p dir="ltr" style="text-align: left;">&nbsp;شكرًا.<br></p>',
            'notification_type_shortname'=>'exam_unenroll'
        ),
        array(
            'subject' => 'Rescheduling Exam [exam_name]',
            'body' => '<p dir="ltr" style="text-align: left;">Dear [exam_userfullname],&nbsp;</p><p dir="ltr" style="text-align: left;">We would like to inform you that we rescheduled you in&nbsp; [exam_name]  from [pastexam_date] [pastexam_time] to [presentexam_date] [presentexam_time]  .&nbsp;</p><p dir="ltr" style="text-align: left;">Thanks<br></p><p dir="ltr" style="text-align: left;"><br></p><p dir="ltr" style="text-align: left;"><br></p><p dir="ltr" style="text-align: left;"><br></p>',
            'arabic_subject' =>'إعادة جدولة اختبار [exam_name]',
            'arabic_body' => '<p dir="ltr" style="text-align: left;">عزيزي [exam_userfullname]،&nbsp;<br></p><p dir="ltr" style="text-align: left;">&nbsp; نود إشعاركم بأنه قد تم إعادة جدولة تسجيلك في [exam_name] من [pastexam_date] [pastexam_time] الى [presentexam_date] [presentexam_time]. 
            &nbsp;.&nbsp;</p><p dir="ltr" style="text-align: left;">&nbsp;شكرًا.<br></p>',
            'notification_type_shortname'=>'exam_reschedule'
        ),
        array(
            'subject' => 'Enrolment at [exam_ownedby] exam  [exam_name]',
            'body' => '<p dir="ltr" style="text-align: left;">Dear [exam_userfullname],&nbsp;</p><p dir="ltr" style="text-align: left;">&nbsp;We would like to inform you that you are enrolled in CISI exam  [exam_name] exam at [exam_date] [exam_time].&nbsp;</p><p dir="ltr" style="text-align: left;">Thanks.<br></p>',
            'arabic_subject' =>'التسجيل في  [exam_ownedby] [exam_name]اختبار',
            'arabic_body' => '<p dir="ltr" style="text-align: left;">عزيزي [exam_userfullname] ،</p><p dir="ltr" style="text-align: left;">&nbsp; نود إشعاركم بأنه قد تم تسجيلك في [exam_name] التي يملكه[exam_ownedby]    الموافق   [exam_date] [exam_time]. &nbsp;</p><p dir="ltr" style="text-align: left;">شكرًا.<br></p>',
            'notification_type_shortname'=>'other_exam_register'
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
    $time = time();
    $initcontent = array('name' => 'Events','arabicname' => 'إشعارات الفعاليات','shortname' => 'events','parent_module' => '0','usercreated' => '2','timecreated' => $time,'usermodified' => 2,'timemodified' => NULL, 'pluginname' => 'events');
    $parentid = $DB->get_field('local_notification_type', 'id', array('shortname' => 'events'));
    if(!$parentid){
        $parentid = $DB->insert_record('local_notification_type', $initcontent);
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
       array('name' => 'Unregister','arabicname' => ' غير مسجل','shortname' => 'event_unregister','parent_module' => $parentid,'usercreated' => '2','timecreated' => $time,'usermodified' => 2,'timemodified' => NULL,'pluginname' => 'events','plugintype' => 'local'),
      
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
        array(
            'subject' => 'Cancel Event [event_name]',
            'body' => '<p dir="ltr" style="text-align: left;">Dear [event_userfullname],&nbsp;</p><p dir="ltr" style="text-align: left;">We would like to inform you that you are unenrolled in &nbsp; [event_name]  at [event_date] [event_time] .&nbsp;</p><p dir="ltr" style="text-align: left;">Thanks<br></p><p dir="ltr" style="text-align: left;"><br></p><p dir="ltr" style="text-align: left;"><br></p><p dir="ltr" style="text-align: left;"><br></p>',
            'arabic_subject' =>'إلغاء فعالية [event_name]',
            'arabic_body' => '<p dir="ltr" style="text-align: left;">عزيزي [event_userfullname]،&nbsp;<br></p><p dir="ltr" style="text-align: left;">&nbsp;  نود إشعاركم بأنه قد تم إلغاء تسجيلك في [event_name] الموافق [event_date] [event_time]. 
            &nbsp;.&nbsp;</p><p dir="ltr" style="text-align: left;">&nbsp;شكرًا.<br></p>',
            'notification_type_shortname'=>'event_unregister'
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




    $time = time();
    $initcontent = array('name' => 'Competency','arabicname' => 'إشعارات الجدارت','shortname' => 'competency','parent_module' => '0','usercreated' => '2','timecreated' => $time,'usermodified' => 2,'timemodified' => NULL, 'pluginname' => 'competency','plugintype' => 'local');
    $parentid = $DB->get_field('local_notification_type', 'id', array('shortname' => 'competency'));
    if(!$parentid){
        $parentid = $DB->insert_record('local_notification_type', $initcontent);
    }
    $notification_type_data = array(
        array('name' => 'Competency Completions','arabicname' => 'إكمال الجدارة','shortname' => 'competency_completions','parent_module' => $parentid,'usercreated' => '2','timecreated' => $time,'usermodified' => 2,'timemodified' => NULL, 'pluginname' => 'competency','plugintype' => 'local'),
        array('name' => 'Competency Adding Learning Item','arabicname' => 'الجدارة إضافة عنصر التعلم','shortname' => 'competency_adding_learning_item','parent_module' => $parentid,'usercreated' => '2','timecreated' => $time,'usermodified' => 2,'timemodified' => NULL, 'pluginname' => 'competency','plugintype' => 'local'),
        array('name' => 'Competency Removing Learning Item','arabicname' => 'الجدارة إزالة عنصر التعلم','shortname' => 'competency_removing_learning_item','parent_module' => $parentid,'usercreated' => '2','timecreated' => $time,'usermodified' => 2,'timemodified' => NULL, 'pluginname' => 'competency','plugintype' => 'local'),

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
            'subject' => '[competency_name] Completions',
            'body' => '<p dir="ltr" style="text-align: left;">Dear [competency_userfulname],</p><p dir="ltr" style="text-align: left;">&nbsp;Congratulations! you have been successfully completed [competency_name],<br></p>',
            'arabic_subject' =>' إتمام [competency_name]',
            'arabic_body' => '<p dir="ltr" style="text-align: left;">عزيزي [competency_userfulname]،</p><p dir="ltr" style="text-align: left;">&nbsp;تهانينا! لقد أكملت [competency_name], بنجاح.<br></p>',
            'notification_type_shortname'=>'competency_completions'
        ),
        array(
            'subject' => 'New edit in [competency_name]',
            'body' => '<p dir="ltr" style="text-align: left;">The new learning item has been successfully added!<br></p>',
            'arabic_subject' =>'تعديل جديد - [competency_name]',
            'arabic_body' => '<p dir="ltr" style="text-align: left;"></p><div>تمت إضاقة عنصر تعلم جديد بنجاح!</div><br><br><p><p>',
            'notification_type_shortname'=>'competency_adding_learning_item'
        ),
        array(
            'subject' => 'New edit in [competency_name]',
            'body' => '<p dir="ltr" style="text-align: left;">The learning item has been successfully removed!<br></p>',
            'arabic_subject' =>'تعديل جديد - [competency_name]',
            'arabic_body' => '<p dir="ltr" style="text-align: left;">تمت إزالة عنصر التعلم بنجاح!<br></p>',
            'notification_type_shortname'=>'competency_removing_learning_item'
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
    $time = time();
    $initcontent = array('name' => 'Payment','arabicname' => 'إشعارت الدفع','shortname' => 'product','parent_module' => '0','usercreated' => '2','timecreated' => $time,'usermodified' => 2,'timemodified' => NULL, 'pluginname' => 'product');
    $parentid = $DB->get_field('local_notification_type', 'id', array('shortname' => 'product'));
    if(!$parentid){
        $parentid = $DB->insert_record('local_notification_type', $initcontent);
    }
    $notification_type_data = array(
        array('name' => 'Payment completion','arabicname' => 'إتمام الدفع','shortname' => 'payment_completion','parent_module' => $parentid,'usercreated' => '2','timecreated' => $time,'usermodified' => 2,'timemodified' => NULL, 'pluginname' => 'product','plugintype' => 'tool'),
        array('name' => 'Pre payment','arabicname' => 'الدفع المسبق','shortname' => 'pre_payment','parent_module' => $parentid,'usercreated' => '2','timecreated' => $time,'usermodified' => 2,'timemodified' => NULL, 'pluginname' => 'product','plugintype' => 'tool'),
        array('name' => 'Post payment','arabicname' => 'الدفع الآجل','shortname' => 'post_payment','parent_module' => $parentid,'usercreated' => '2','timecreated' => $time,'usermodified' => 2,'timemodified' => NULL, 'pluginname' => 'product','plugintype' => 'tool'),
        array('name' => 'Wallet Update','arabicname' => 'تحديث المحفظة','shortname' => 'wallet_update','parent_module' => $parentid,'usercreated' => '2','timecreated' => $time,'usermodified' => 2,'timemodified' => NULL, 'pluginname' => 'product','plugintype' => 'tool'),
        
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
            'subject' => 'Payment completed',
            'body' => '<p dir="ltr" style="text-align: left;">Dear [payment_userfullname],</p><p dir="ltr" style="text-align: left;">&nbsp;We would like to inform you that your order No#[invoiceno] has been submitted successfully.Your payment details as follows: [payment_details]</p><p dir="ltr" style="text-align: left;">&nbsp;Thanks<br></p>',
            'arabic_subject' =>'تم الدفع',
            'arabic_body' => '<p dir="ltr" style="text-align: left;">عزيزي [payment_userfullname] ،</p><p dir="ltr" style="text-align: left;">&nbsp;نود إشعاركم  بإنة عملية الشراء تمت بنجاح. رقم الفاتورة  [invoiceno]،  وفي مايلي تفاصيل العملية: [payment_details].</p><p dir="ltr" style="text-align: left;">&nbsp;شكرًا<br></p>     ',
            'notification_type_shortname'=>'payment_completion'
        ),
        array(
            'subject' => 'Payment is completed',
            'body' => '<p dir="ltr" style="text-align: left;"></p><p dir="ltr" style="text-align: left;">Dear [payment_userfullname],</p><p dir="ltr" style="text-align: left;">&nbsp;The payment process for order NO# [order] has been successfully completed as follows: [payment_details] .&nbsp;</p><p dir="ltr" style="text-align: left;">Thanks<br></p>',
            'arabic_subject' =>'تم الدفع',
            'arabic_body' => '<p dir="ltr" style="text-align: left;">- عزيزي [payment_userfullname]،&nbsp;</p><p dir="ltr" style="text-align: left;">تمت عملية الدفع للطلب رقم # [order] بنجاح وفي ما يلي تفاصيل العملية:  [payment_details].</p><p dir="ltr" style="text-align: left;">&nbsp;شكرًا<br></p>',
            'notification_type_shortname'=>'pre_payment'
        ),
        array(
            'subject' => 'Payment is required',
            'body' => '<p dir="ltr" style="text-align: left;">Dear [payment_userfullname],</p><p dir="ltr" style="text-align: left;">&nbsp;We would like to inform you that your order has been received and we will contact you to complete the payment process.&nbsp;</p><p dir="ltr" style="text-align: left;">Thanks<br></p>',
            'arabic_subject' =>'مطلوب الدفع ',
            'arabic_body' => '<p dir="ltr" style="text-align: left;">1- عزيزي [payment_userfullname]، نود إشعاركم بأنه قد تم استلام طلبك وسيتم التواصل بكم لإكمال عملية الدفع. شكرًا<br></p>',
            'notification_type_shortname'=>'post_payment'
        ),
        array(
            'subject' => 'Wallet is updated',
            'body' => '<p dir="ltr" style="text-align: left;">Dear [payment_userfullname],&nbsp;</p><p dir="ltr" style="text-align: left;">your wallet has been successfully updated .&nbsp;</p><p dir="ltr" style="text-align: left;">Thanks<br></p>',
            'arabic_subject' =>'تم تحديث المحفظة',
            'arabic_body' => '<p dir="ltr" style="text-align: left;">عزيزي [payment_userfullname] ، لقد تم تحديث محفظتك بنجاح. شكرًا<br></p>',
            'notification_type_shortname'=>'wallet_update'
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
    $table = new xmldb_table('local_smslogs');

    if (!$dbman->table_exists($table)) {

        $table->add_field('id', XMLDB_TYPE_INTEGER, '10', null, XMLDB_NOTNULL, XMLDB_SEQUENCE, null);
        $table->add_field('notification_infoid', XMLDB_TYPE_INTEGER, '10', null, XMLDB_NOTNULL, null, null);
        $table->add_field('from_userid', XMLDB_TYPE_INTEGER, '10', null, XMLDB_NOTNULL, null, null);
        $table->add_field('to_userid', XMLDB_TYPE_INTEGER, '10', null, XMLDB_NOTNULL, null, null);
        $table->add_field('realuser', XMLDB_TYPE_INTEGER, '10', null, XMLDB_NOTNULL, null, '0');  
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
}
