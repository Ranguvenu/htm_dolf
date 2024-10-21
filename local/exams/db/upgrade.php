<?php
defined('MOODLE_INTERNAL') || die();
function xmldb_local_exams_upgrade($oldversion) {
    global $DB, $CFG;
   if ($oldversion < 2021051700.01) {
        $dbman = $DB->get_manager();
        $table = new xmldb_table('exam_enrollments');
        $table->add_field('id', XMLDB_TYPE_INTEGER, '10', null, XMLDB_NOTNULL, XMLDB_SEQUENCE, null);
        $table->add_field('examid', XMLDB_TYPE_INTEGER, '12', null, XMLDB_NOTNULL, null, '0');
        $table->add_field('courseid', XMLDB_TYPE_INTEGER, '12', null, XMLDB_NOTNULL, null, '0');
        $table->add_field('userid', XMLDB_TYPE_INTEGER, '12', null, XMLDB_NOTNULL, null, '0');
        $table->add_field('timecreated', XMLDB_TYPE_INTEGER, '10', null, XMLDB_NOTNULL, null, '0');
        $table->add_field('usercreated', XMLDB_TYPE_INTEGER, '10', null, XMLDB_NOTNULL, null, '0');
        $table->add_key('primary', XMLDB_KEY_PRIMARY, ['id']);
        if (!$dbman->table_exists($table)) {
           $dbman->create_table($table);
        }
        upgrade_plugin_savepoint(true, 2021051700.01, 'local', 'exams');
    }
    if ($oldversion < 2021051700.06) {
        $dbman = $DB->get_manager();
        $table = new xmldb_table('local_exams');
        $field = new xmldb_field('quizid',XMLDB_TYPE_INTEGER, '10', null, XMLDB_NOTNULL, null, '0');
        if (!$dbman->field_exists($table, $field)) {
            $dbman->add_field($table, $field);
        }
        upgrade_plugin_savepoint(true, 2021051700.06, 'local', 'exams');
    }
    if ($oldversion < 2021051700.13) {
        $dbman = $DB->get_manager();
        $table = new xmldb_table('local_exams');
        $field = new xmldb_field('examdatetime',XMLDB_TYPE_INTEGER, '10', null, XMLDB_NOTNULL, null, '0');
        if (!$dbman->field_exists($table, $field)) {
            $dbman->add_field($table, $field);
        }
        upgrade_plugin_savepoint(true, 2021051700.13, 'local', 'exams');
    }   

    if ($oldversion < 2021051700.15) {
    
        $dbman = $DB->get_manager();
    

        $table = new xmldb_table('exam_completions');
        $table->add_field('id', XMLDB_TYPE_INTEGER, '10', null, XMLDB_NOTNULL, XMLDB_SEQUENCE, null);
        $table->add_field('examid', XMLDB_TYPE_INTEGER, '10', null, XMLDB_NOTNULL, null, '0');
        $table->add_field('userid', XMLDB_TYPE_INTEGER, '10', null, XMLDB_NOTNULL, null, '0');
        $table->add_field('completion_status', XMLDB_TYPE_INTEGER, '2', null, null, null, '0');
        $table->add_field('completiondate', XMLDB_TYPE_INTEGER, '10', null, null, null, '0');
        $table->add_field('timecreated', XMLDB_TYPE_INTEGER, '10', null, XMLDB_NOTNULL, null, '0');
        $table->add_field('usercreated', XMLDB_TYPE_INTEGER, '10', null, XMLDB_NOTNULL, null, '0');
        $table->add_field('timemodified', XMLDB_TYPE_INTEGER, '10', null, null, null, '0');
        $table->add_field('usermodified', XMLDB_TYPE_INTEGER, '10', null, null, null, '0');
        $table->add_key('primary', XMLDB_KEY_PRIMARY, array('id'));

        if (!$dbman->table_exists($table)) {
            $dbman->create_table($table);
        }
        upgrade_plugin_savepoint(true, 2021051700.15, 'local', 'exams');
    }
    if ($oldversion < 2021051700.18) {
        $dbman = $DB->get_manager();
        $table = new xmldb_table('local_exams');
        $field = new xmldb_field('ctype', XMLDB_TYPE_CHAR, '255',  null, null, null, null, null, null);
        if (!$dbman->field_exists($table, $field)) {
            $dbman->add_field($table, $field);
        }
        upgrade_plugin_savepoint(true, 2021051700.18, 'local', 'exams');
    }
    if ($oldversion < 2021051700.21) {
        $dbman = $DB->get_manager();
        $table = new xmldb_table('local_exams');
        $field = new xmldb_field('seatingcapacity', XMLDB_TYPE_INTEGER, '10', null, null, null, '0');
        if (!$dbman->field_exists($table, $field)) {
            $dbman->add_field($table, $field);
        }
        upgrade_plugin_savepoint(true, 2021051700.21, 'local', 'exams');
    }
    if ($oldversion < 2021051700.22) {
        $dbman = $DB->get_manager();
        $table = new xmldb_table('local_exams');
        $field = new xmldb_field('quizpassword', XMLDB_TYPE_CHAR, '255',  null, null, null, null, null, null);
        if (!$dbman->field_exists($table, $field)) {
            $dbman->add_field($table, $field);
        }
        upgrade_plugin_savepoint(true, 2021051700.22, 'local', 'exams');
    }    

    if ($oldversion < 2021051701.27) {
        $dbman = $DB->get_manager();
        $table = new xmldb_table('exam_enrollments');
        $field = new xmldb_field('hallid', XMLDB_TYPE_INTEGER, '10', null, null, null, '0');
        if (!$dbman->field_exists($table, $field)) {
            $dbman->add_field($table, $field);
        }
        $field = new xmldb_field('examdate', XMLDB_TYPE_INTEGER, '10', null, null, null, '0');
        if (!$dbman->field_exists($table, $field)) {
            $dbman->add_field($table, $field);
        }        
        upgrade_plugin_savepoint(true, 2021051701.27, 'local', 'exams');
    }
    if ($oldversion < 2021051701.28) {
        $dbman = $DB->get_manager();
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
    $initcontent = array('name' => 'Exams','shortname' => 'exams','parent_module' => '0','usercreated' => '2','timecreated' => $time,'usermodified' => 2,'timemodified' => NULL, 'pluginname' => 'exams','plugintype' => 'local');
    $parentid = $DB->get_field('local_notification_type', 'id', array('shortname' => 'exams'));
    if(!$parentid){
        $parentid = $DB->insert_record('local_notification_type', $initcontent);
    }
    $notification_type_data = array(
        array('name' => 'Create Exam','shortname' => 'exams_create','parent_module' => $parentid,'usercreated' => '2','timecreated' => $time,'usermodified' => 2,'timemodified' => NULL, 'pluginname' => 'exams','plugintype' => 'local'),
       array('name' => 'Update Exam','shortname' => 'exams_update','parent_module' => $parentid,'usercreated' => '2','timecreated' => $time,'usermodified' => 2,'timemodified' => NULL, 'pluginname' => 'exams','plugintype' => 'local'),
	   array('name' => 'Enrolment','shortname' => 'exams_enrolment','parent_module' => $parentid,'usercreated' => '2','timecreated' => $time,'usermodified' => 2,'timemodified' => NULL, 'pluginname' => 'exams','plugintype' => 'local'),
	   array('name' => 'Completion','shortname' => 'exams_completion','parent_module' => $parentid,'usercreated' => '2','timecreated' => $time,'usermodified' => 2,'timemodified' => NULL, 'pluginname' => 'exams','plugintype' => 'local'),
	   array('name' => 'Certificate Assignment','shortname' => 'exams_certificate_assignment','parent_module' => $parentid,'usercreated' => '2','timecreated' => $time,'usermodified' => 2,'timemodified' => NULL, 'pluginname' => 'exams','plugintype' => 'local'),
       array('name' => 'Before 7 days','shortname' => 'exams_before_7_days','parent_module' => $parentid,'usercreated' => '2','timecreated' => $time,'usermodified' => 2,'timemodified' => NULL, 'pluginname' => 'exams','plugintype' => 'local'),
       array('name' => 'Before 48 Hours','shortname' => 'exams_before_48_hours','parent_module' => $parentid,'usercreated' => '2','timecreated' => $time,'usermodified' => 2,'timemodified' => NULL, 'pluginname' => 'exams','plugintype' => 'local'),       
       array('name' => 'Before 24 Hours','shortname' => 'exams_before_24_hours','parent_module' => $parentid,'usercreated' => '2','timecreated' => $time,'usermodified' => 2,'timemodified' => NULL, 'pluginname' => 'exams','plugintype' => 'local'),
       array('name' => 'Send Conclusion','shortname' => 'exams_send_conclusion','parent_module' => $parentid,'usercreated' => '2','timecreated' => $time,'usermodified' => 2,'timemodified' => NULL, 'pluginname' => 'exams','plugintype' => 'local'),
       array('name' => 'After Session','shortname' => 'exams_after_session','parent_module' => $parentid,'usercreated' => '2','timecreated' => $time,'usermodified' => 2,'timemodified' => NULL, 'pluginname' => 'exams','plugintype' => 'local')    
    );
    
    foreach($notification_type_data as $notification_type){
        unset($notification_type['timecreated']);
        if(!$DB->record_exists('local_notification_type',  $notification_type)){
            $notification_type['timecreated'] = $time;
            $DB->insert_record('local_notification_type', $notification_type);
        }
    }
    upgrade_plugin_savepoint(true, 2021051701.28, 'local', 'exams');
}

    if ($oldversion < 2021051701.25) {
        $dbman = $DB->get_manager();
        $table = new xmldb_table('local_exam_grievance');
        $table->add_field('id', XMLDB_TYPE_INTEGER, '10', null, XMLDB_NOTNULL, XMLDB_SEQUENCE, null);
        $table->add_field('examid', XMLDB_TYPE_INTEGER, '10', null, XMLDB_NOTNULL, null, '0');
        $table->add_field('userid', XMLDB_TYPE_INTEGER, '10', null, XMLDB_NOTNULL, null, '0');
        $table->add_field('submittedon', XMLDB_TYPE_INTEGER, '10', null, XMLDB_NOTNULL, null, '0');
        $table->add_field('actiontaken', XMLDB_TYPE_INTEGER, '10', null, null, null, '0');
        $table->add_field('approvedon', XMLDB_TYPE_INTEGER, '10', null, null, null, '0');
        $table->add_field('paymentstatus', XMLDB_TYPE_INTEGER, '10', null, null, null, '0');
        $table->add_field('status', XMLDB_TYPE_INTEGER, '10', null, null, null, '0');
        $table->add_field('reason', XMLDB_TYPE_TEXT, null, null, null, null, null);
        $table->add_field('actionby', XMLDB_TYPE_INTEGER, '10', null, XMLDB_NOTNULL, null, '0');
        $table->add_field('timecreated', XMLDB_TYPE_INTEGER, '10', null, null, null, '0');
        $table->add_field('timemodified', XMLDB_TYPE_INTEGER, '10', null, null, null, '0');
        $table->add_key('primary', XMLDB_KEY_PRIMARY, array('id'));
        if (!$dbman->table_exists($table)) {
            $dbman->create_table($table);
        }
        upgrade_plugin_savepoint(true, 2021051701.25, 'local', 'exams');
    }
    if ($oldversion < 2021051702) {
        $dbman = $DB->get_manager();
        $table = new xmldb_table('local_exams');
        $field = new xmldb_field('clevels', XMLDB_TYPE_CHAR, '255', null, XMLDB_NOTNULL, null, null);
        if (!$dbman->field_exists($table, $field)) {
            $dbman->add_field($table, $field);
        }
        upgrade_plugin_savepoint(true, 2021051702, 'local', 'exams');
    }
    if ($oldversion < 2021051702.1) {
        $dbman = $DB->get_manager();
        $table = new xmldb_table('local_exams');
        $field = new xmldb_field('arprofile', XMLDB_TYPE_INTEGER, '10', null, null, null, '0');
        if (!$dbman->field_exists($table, $field)) {
            $dbman->add_field($table, $field);
        }
        $field = new xmldb_field('enprofile', XMLDB_TYPE_INTEGER, '10', null, null, null, '0');
        if (!$dbman->field_exists($table, $field)) {
            $dbman->add_field($table, $field);
        }        
        upgrade_plugin_savepoint(true, 2021051702.1, 'local', 'exams');
    }

    if ($oldversion < 2021051702.3) {
        $dbman = $DB->get_manager();
        $table = new xmldb_table('exam_enrollments');
        $field = new xmldb_field('examdate', XMLDB_TYPE_DATETIME, '6', null, XMLDB_NOTNULL, false, null);
        $dbman->change_field_type($table, $field);

        // Main savepoint reached.
        upgrade_plugin_savepoint(true, 2021051702.3, 'local', 'exams');

    } 
        if ($oldversion < 2021051702.4) {
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
        upgrade_plugin_savepoint(true, 2021051702.4, 'local', 'exams');
    }   
    if ($oldversion < 2021051702.5) {
    
        $dbman = $DB->get_manager();
        $table = new xmldb_table('local_exams');
        $arabicnameforexams = new xmldb_field('examnamearabic',XMLDB_TYPE_TEXT,null, null, null, null, null, null);
         if (!$dbman->field_exists($table, $arabicnameforexams)) {
            $dbman->add_field($table, $arabicnameforexams);
        }     
         upgrade_plugin_savepoint(true, 2021051702.5, 'local', 'exams'); 
    }

    if ($oldversion < 2021051702.6) {
        $dbman = $DB->get_manager();
        $table = new xmldb_table('local_exams');
        $field = new xmldb_field('type', XMLDB_TYPE_CHAR, '255', null, XMLDB_NOTNULL, null, '0');
        $dbman->change_field_type($table, $field);

        // Main savepoint reached.
        upgrade_plugin_savepoint(true, 2021051702.6, 'local', 'exams');

    }

    if ($oldversion < 2021051702.8) {
        $dbman = $DB->get_manager();
        $table = new xmldb_table('exam_enrollments');
        $field = new xmldb_field('hallreservationid',XMLDB_TYPE_INTEGER, '10', null, XMLDB_NOTNULL, null, '0');
        if (!$dbman->field_exists($table, $field)) {
            $dbman->add_field($table, $field);
        }

        upgrade_plugin_savepoint(true, 2021051702.8, 'local', 'exams');
    }

   if ($oldversion < 2021051702.9) {
        $dbman = $DB->get_manager();
        $table = new xmldb_table('exam_grievance_payments');
        $table->add_field('id', XMLDB_TYPE_INTEGER, '10', null, XMLDB_NOTNULL, XMLDB_SEQUENCE, null);
        $table->add_field('grievanceid', XMLDB_TYPE_INTEGER, '12', null, XMLDB_NOTNULL, null, '0');
        $table->add_field('status', XMLDB_TYPE_INTEGER, '12', null, XMLDB_NOTNULL, null, '0');
        $table->add_field('usercreated', XMLDB_TYPE_INTEGER, '10', null, XMLDB_NOTNULL, null, '0');
        $table->add_field('timecreated', XMLDB_TYPE_INTEGER, '10', null, XMLDB_NOTNULL, null, '0');
        $table->add_key('primary', XMLDB_KEY_PRIMARY, ['id']);
        if (!$dbman->table_exists($table)) {
           $dbman->create_table($table);
        }
        upgrade_plugin_savepoint(true, 2021051702.9, 'local', 'exams');
    }

    if ($oldversion < 2021051703.01) {
        $dbman = $DB->get_manager();
        $table = new xmldb_table('local_exams');
        $field = new xmldb_field('targetaudience', XMLDB_TYPE_TEXT, null, null, null, null, null);
        $dbman->change_field_type($table, $field);

        // Main savepoint reached.
        upgrade_plugin_savepoint(true, 2021051703.01, 'local', 'exams');

    }
    if ($oldversion < 2021051703.02) {
        $dbman = $DB->get_manager();
        $table = new xmldb_table('local_exams');
        $field = new xmldb_field('old_id',XMLDB_TYPE_INTEGER, '10', null, XMLDB_NOTNULL, null, '0');
        if (!$dbman->field_exists($table, $field)) {
            $dbman->add_field($table, $field);
        }
        upgrade_plugin_savepoint(true, 2021051703.02, 'local', 'exams');
    }



    if ($oldversion < 2021051703.04) {
        
        $dbman = $DB->get_manager();
        $table = new xmldb_table('local_exams');
        $field = new xmldb_field('old_id', XMLDB_TYPE_CHAR, '255', null, XMLDB_NOTNULL, null, '0');
        $dbman->change_field_type($table, $field);

        // Main savepoint reached.
        upgrade_plugin_savepoint(true, 2021051703.04, 'local', 'exams');
    }

    if ($oldversion < 2021051703.07) {
        $dbman = $DB->get_manager();
        $table = new xmldb_table('local_exams');
        $field = new xmldb_field('targetgroup', XMLDB_TYPE_CHAR, '255', null, XMLDB_NOTNULL, null, '0');
        if (!$dbman->field_exists($table, $field)) {
            $dbman->add_field($table, $field);
        }
        upgrade_plugin_savepoint(true, 2021051703.07, 'local', 'exams');
    }

    if ($oldversion < 2021051703.2) {

        $dbman = $DB->get_manager();
        $table = new xmldb_table('local_exams');
        $field = new xmldb_field('learningmaterial',XMLDB_TYPE_INTEGER, '10', null, XMLDB_NOTNULL, null, '0');
        if ($dbman->field_exists($table, $field)) {
            $dbman->drop_field($table, $field);
        }

        $field = new xmldb_field('additionalrequirements', XMLDB_TYPE_TEXT, null, null, null, null, null);
        if (!$dbman->field_exists($table, $field)) {
            $dbman->add_field($table, $field);
        }

        upgrade_plugin_savepoint(true, 2021051703.2, 'local', 'exams');
    }
    
    if ($oldversion < 2021051703.3) {
    
        $dbman = $DB->get_manager();
        $table = new xmldb_table('local_exams');
        $tax_freefield = new xmldb_field('tax_free',XMLDB_TYPE_INTEGER, '10', null, null, null, null);
         if (!$dbman->field_exists($table, $tax_freefield)) {
            $dbman->add_field($table, $tax_freefield);
        }  
        upgrade_plugin_savepoint(true, 2021051703.3, 'local', 'exams'); 
    }

    if ($oldversion < 2021051704.03) {

        $dbman = $DB->get_manager();
        $table = new xmldb_table('local_exams');
        $field = new xmldb_field('halladdress', XMLDB_TYPE_CHAR, '50', null, XMLDB_NOTNULL, null, null);
        $dbman->change_field_type($table, $field);
        upgrade_plugin_savepoint(true, 2021051704.03, 'local', 'exams');

    }

    if ($oldversion < 2021051706.1) {

        $dbman = $DB->get_manager();
        $table = new xmldb_table('local_exams');
        $targetaudience = new xmldb_field('targetaudience', XMLDB_TYPE_CHAR, '50', null, XMLDB_NULL, null, null);
        if ($dbman->field_exists($table, $targetaudience)) {
            $dbman->drop_field($table, $targetaudience);
        }

        $field = new xmldb_field('targetcategories', XMLDB_TYPE_TEXT, null, null, null, null, null);
        if ($dbman->field_exists($table, $field)) {
            $dbman->rename_field($table, $field, 'targetaudience', true, true);
        }
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

        upgrade_plugin_savepoint(true, 2021051706.1, 'local', 'exams');
    }

    if ($oldversion < 2021051706.2) {
    
        $dbman = $DB->get_manager();
        $table = new xmldb_table('local_exams');
        $field = new xmldb_field('targetaudience', XMLDB_TYPE_TEXT, null, null, null, null, null);
         if (!$dbman->field_exists($table, $field)) {
            $dbman->add_field($table, $field);
        }  
        upgrade_plugin_savepoint(true, 2021051706.2, 'local', 'exams'); 
    }

    if ($oldversion < 2021051707.03) {
        $dbman = $DB->get_manager();

        $table = new xmldb_table('local_exams');
        $language = new xmldb_field('language', XMLDB_TYPE_CHAR, '255',  null, null, null, null, null, null);
        if ($dbman->field_exists($table, $language)) {
            $dbman->drop_field($table, $language);
        }
        $examduration = new xmldb_field('examduration', XMLDB_TYPE_INTEGER, '10', null, null, null, null);
        if ($dbman->field_exists($table, $examduration)) {
            $dbman->drop_field($table, $examduration);
        }
        $seatingcapacity = new xmldb_field('seatingcapacity', XMLDB_TYPE_INTEGER, '10', null, null, null, null);
        if ($dbman->field_exists($table, $seatingcapacity)) {
            $dbman->drop_field($table, $seatingcapacity);
        }
        $noofquestions = new xmldb_field('noofquestions', XMLDB_TYPE_INTEGER, '10', null, null, null, null);
        if ($dbman->field_exists($table, $noofquestions)) {
            $dbman->drop_field($table, $noofquestions);
        }
        $examdatetime = new xmldb_field('examdatetime', XMLDB_TYPE_INTEGER, '10', null, null, null, null);
        if ($dbman->field_exists($table, $examdatetime)) {
            $dbman->drop_field($table, $examdatetime);
        }
        $enddate = new xmldb_field('enddate', XMLDB_TYPE_INTEGER, '10', null, null, null, null);
        if ($dbman->field_exists($table, $enddate)) {
            $dbman->drop_field($table, $enddate);
        }
        $slot = new xmldb_field('slot', XMLDB_TYPE_INTEGER, '10', null, null, null, null);
        if ($dbman->field_exists($table, $slot)) {
            $dbman->drop_field($table, $slot);
        }
        $halladdress = new xmldb_field('halladdress', XMLDB_TYPE_CHAR, '255',  null, null, null, null, null, null);
        if ($dbman->field_exists($table, $halladdress)) {
            $dbman->drop_field($table, $halladdress);
        }
        $quizpassword = new xmldb_field('quizpassword', XMLDB_TYPE_CHAR, '255',  null, null, null, null, null, null);
        if ($dbman->field_exists($table, $quizpassword)) {
            $dbman->drop_field($table, $quizpassword);
        }
        $quizpassgrade = new xmldb_field('quizpassgrade', XMLDB_TYPE_CHAR, '255',  null, null, null, null, null, null);
        if ($dbman->field_exists($table, $quizpassgrade)) {
            $dbman->drop_field($table, $quizpassgrade);
        }
        $quizpassgrade = new xmldb_field('arprofile', XMLDB_TYPE_INTEGER, '10', null, null, null, null);
        if ($dbman->field_exists($table, $quizpassgrade)) {
            $dbman->drop_field($table, $quizpassgrade);
        }
        $quizpassgrade = new xmldb_field('enprofile', XMLDB_TYPE_INTEGER, '10', null, null, null, null);
        if ($dbman->field_exists($table, $quizpassgrade)) {
            $dbman->drop_field($table, $quizpassgrade);
        }

        $field = new xmldb_field('attachedmessage', XMLDB_TYPE_TEXT, null, null, null, null, null);
        if (!$dbman->field_exists($table, $field)) {
            $dbman->add_field($table, $field);
        }
        $noofattempts = new xmldb_field('noofattempts',XMLDB_TYPE_INTEGER, '8', null, null, null, null);
        if (!$dbman->field_exists($table, $noofattempts)) {
            $dbman->add_field($table, $noofattempts);
        }
        $appliedperiod = new xmldb_field('appliedperiod',XMLDB_TYPE_INTEGER, '5', null, null, null, null);
        if (!$dbman->field_exists($table, $appliedperiod)) {
            $dbman->add_field($table, $appliedperiod);
        }
        $attemptnumber = new xmldb_field('attemptnumber',XMLDB_TYPE_INTEGER, '5', null, null, null, null);
        if (!$dbman->field_exists($table, $attemptnumber)) {
            $dbman->add_field($table, $attemptnumber);
        }
        $daysbeforeattempt = new xmldb_field('daysbeforeattempt',XMLDB_TYPE_INTEGER, '5', null, null, null, null);
        if (!$dbman->field_exists($table, $daysbeforeattempt)) {
            $dbman->add_field($table, $daysbeforeattempt);
        }
        $daysbeforeattempt = new xmldb_field('fee',XMLDB_TYPE_INTEGER, '5', null, null, null, null);
        if (!$dbman->field_exists($table, $daysbeforeattempt)) {
            $dbman->add_field($table, $daysbeforeattempt);
        }

        $table = new xmldb_table('local_exam_profiles');
        $table->add_field('id', XMLDB_TYPE_INTEGER, '10', null, XMLDB_NOTNULL, XMLDB_SEQUENCE, null);
        $table->add_field('examid', XMLDB_TYPE_INTEGER, '10', null, XMLDB_NOTNULL, null, '0');
        $table->add_field('sectionid', XMLDB_TYPE_INTEGER, '10', null, XMLDB_NOTNULL, null, '0');
        $table->add_field('quizid', XMLDB_TYPE_INTEGER, '10', null, XMLDB_NOTNULL, null, '0');
        $table->add_field('profilecode', XMLDB_TYPE_CHAR, '255',  null, null, null, null, null, null);
        $table->add_field('activestatus', XMLDB_TYPE_INTEGER, '10', null, XMLDB_NOTNULL, null, '0');
        $table->add_field('publishstatus', XMLDB_TYPE_INTEGER, '10', null, XMLDB_NOTNULL, null, '0');
        $table->add_field('decision', XMLDB_TYPE_INTEGER, '10', null, XMLDB_NOTNULL, null, '0');
        $table->add_field('language', XMLDB_TYPE_CHAR, '255',  null, null, null, null, null, null);
        $table->add_field('arprofile', XMLDB_TYPE_INTEGER, '10', null, XMLDB_NOTNULL, null, '0');
        $table->add_field('enprofile', XMLDB_TYPE_INTEGER, '10', null, XMLDB_NOTNULL, null, '0');
        $table->add_field('duration', XMLDB_TYPE_INTEGER, '10', null, null, null, '0');
        $table->add_field('seatingcapacity', XMLDB_TYPE_INTEGER, '10', null, null, null, '0');
        $table->add_field('questions', XMLDB_TYPE_INTEGER, '10', null, null, null, '0');
        $table->add_field('trailquestions', XMLDB_TYPE_INTEGER, '10', null, null, null, '0');
        $table->add_field('material', XMLDB_TYPE_TEXT, null, null, null, null, null);
        $table->add_field('targetaudience', XMLDB_TYPE_INTEGER, '10', null, null, null, '0');
        $table->add_field('nondisclosure', XMLDB_TYPE_TEXT, null, null, null, null, null);
        $table->add_field('instructions', XMLDB_TYPE_TEXT, null, null, null, null, null);
        $table->add_field('registrationstartdate', XMLDB_TYPE_INTEGER, '10', null, null, null, '0');
        $table->add_field('registrationenddate', XMLDB_TYPE_INTEGER, '10', null, null, null, '0');
        $table->add_field('password', XMLDB_TYPE_CHAR, '255',  null, null, null, null, null, null);
        $table->add_field('passinggrade', XMLDB_TYPE_CHAR, '255',  null, null, null, null, null, null);
        $table->add_field('hascertificate', XMLDB_TYPE_INTEGER, '10', null, XMLDB_NOTNULL, null, '0');
        $table->add_field('preexampage', XMLDB_TYPE_INTEGER, '10', null, null, null, '0');
        $table->add_field('successrequirements', XMLDB_TYPE_INTEGER, '10', null, null, null, '0');
        $table->add_field('showquestions', XMLDB_TYPE_INTEGER, '10', null, XMLDB_NOTNULL, null, '0');
        $table->add_field('showexamduration', XMLDB_TYPE_INTEGER, '10', null, null, null, '0');
        $table->add_field('showremainingduration', XMLDB_TYPE_INTEGER, '10', null, null, null, '0');
        $table->add_field('commentsoneachque', XMLDB_TYPE_INTEGER, '10', null, XMLDB_NOTNULL, null, '0');
        $table->add_field('commentsaftersub', XMLDB_TYPE_INTEGER, '10', null, null, null, '0');
        $table->add_field('showexamresult', XMLDB_TYPE_INTEGER, '10', null, null, null, '0');
        $table->add_field('showexamgrade', XMLDB_TYPE_INTEGER, '10', null, null, null, '0');
        $table->add_field('competencyresult', XMLDB_TYPE_INTEGER, '10', null, XMLDB_NOTNULL, null, '0');
        $table->add_field('resultofeachcompetency', XMLDB_TYPE_INTEGER, '10', null, null, null, '0');
        $table->add_field('evaluationform', XMLDB_TYPE_INTEGER, '10', null, null, null, '0');
        $table->add_field('notifybeforeexam', XMLDB_TYPE_INTEGER, '10', null, null, null, '0');
        $table->add_field('usercreated', XMLDB_TYPE_INTEGER, '10', null, XMLDB_NOTNULL, null, '0');
        $table->add_field('timemodified', XMLDB_TYPE_INTEGER, '10', null, null, null, '0');
        $table->add_field('timecreated', XMLDB_TYPE_INTEGER, '10', null, null, null, '0');

        $table->add_key('primary', XMLDB_KEY_PRIMARY, array('id'));
        if (!$dbman->table_exists($table)) {
            $dbman->create_table($table);
        }

        $table = new xmldb_table('exam_enrollments');
        $field = new xmldb_field('hallid', XMLDB_TYPE_INTEGER, 10, null, null, null);
        if ($dbman->field_exists($table, $field)) {
            $dbman->rename_field($table, $field, 'profileid', true, true);
        }
        $field = new xmldb_field('hallreservationid',XMLDB_TYPE_INTEGER, '10', null, XMLDB_NOTNULL, null, '0');
        if ($dbman->field_exists($table, $field)) {
            $dbman->drop_field($table, $field);
        }        
        $field = new xmldb_field('hallscheduleid', XMLDB_TYPE_INTEGER, '10', null, null, null, '0');
         if (!$dbman->field_exists($table, $field)) {
            $dbman->add_field($table, $field);
        }  

        upgrade_plugin_savepoint(true, 2021051707.03, 'local', 'exams');
    }

    if ($oldversion < 2021051707.05) {
    
        $dbman = $DB->get_manager();
        $table = new xmldb_table('local_exam_grievance');
        $field = new xmldb_field('profileid',XMLDB_TYPE_INTEGER, '10', null, null, null, '0');
         if (!$dbman->field_exists($table, $field)) {
            $dbman->add_field($table, $field);
        }  
        upgrade_plugin_savepoint(true, 2021051707.05, 'local', 'exams'); 
    }

    if ($oldversion < 2021051707.07) {
        $dbman = $DB->get_manager();
        $table = new xmldb_table('exam_enrollments');
        $field = new xmldb_field('examdate', XMLDB_TYPE_INTEGER, '10', null, null, null, '0');
        $dbman->change_field_type($table, $field);

        // Main savepoint reached.
        upgrade_plugin_savepoint(true, 2021051707.07, 'local', 'exams');
    }

    if ($oldversion < 2021051707.5) {
        $dbman = $DB->get_manager();
        $table = new xmldb_table('local_exam_profiles');

        $field = new xmldb_field('material', XMLDB_TYPE_TEXT, null, null, null, null, null);
        if ($dbman->field_exists($table, $field)) {
            $dbman->drop_field($table, $field);
        }

        $field = new xmldb_field('material',XMLDB_TYPE_INTEGER, '5', null, XMLDB_NOTNULL, null, '0');
        if (!$dbman->field_exists($table, $field)) {
            $dbman->add_field($table, $field);
        }

        $field = new xmldb_field('materialfile', XMLDB_TYPE_INTEGER, '10', null, null, null, '0');
        if (!$dbman->field_exists($table, $field)) {
            $dbman->add_field($table, $field);
        }

        $field = new xmldb_field('materialurl', XMLDB_TYPE_CHAR, '255',  null, null, null, null, null, null);
        if (!$dbman->field_exists($table, $field)) {
            $dbman->add_field($table, $field);
        }

        $field = new xmldb_field('attemptnumber', XMLDB_TYPE_INTEGER, '5', null, null, null, '0');
        if ($dbman->field_exists($table, $field)) {
            $dbman->drop_field($table, $field);
        }

        $field = new xmldb_field('daysbeforeattempt', XMLDB_TYPE_INTEGER, '5', null, null, null, '0');
        if ($dbman->field_exists($table, $field)) {
            $dbman->drop_field($table, $field);
        }

        $field = new xmldb_field('fee', XMLDB_TYPE_TEXT, XMLDB_TYPE_INTEGER, '3', null, null, null, '0');
        if ($dbman->field_exists($table, $field)) {
            $dbman->drop_field($table, $field);
        }

        $table = new xmldb_table('local_exam_attempts');
        $table->add_field('id', XMLDB_TYPE_INTEGER, '10', null, XMLDB_NOTNULL, XMLDB_SEQUENCE, null);
        $table->add_field('examid', XMLDB_TYPE_INTEGER, '10', null, XMLDB_NOTNULL, null, '0');
        $table->add_field('attemptid', XMLDB_TYPE_INTEGER, '10', null, XMLDB_NOTNULL, null, '0');
        $table->add_field('daysbeforeattempt', XMLDB_TYPE_INTEGER, '10', null, XMLDB_NOTNULL, null, '0');
        $table->add_field('fee', XMLDB_TYPE_INTEGER, '10', null, XMLDB_NOTNULL, null, '0');
        $table->add_field('usercreated', XMLDB_TYPE_INTEGER, '10', null, XMLDB_NOTNULL, null, '0');
        $table->add_field('timemodified', XMLDB_TYPE_INTEGER, '10', null, null, null, '0');
        $table->add_field('timecreated', XMLDB_TYPE_INTEGER, '10', null, null, null, '0');

        $table->add_key('primary', XMLDB_KEY_PRIMARY, array('id'));
        if (!$dbman->table_exists($table)) {
            $dbman->create_table($table);
        }

        $table = new xmldb_table('local_exam_attemptpurchases');
        $table->add_field('id', XMLDB_TYPE_INTEGER, '10', null, XMLDB_NOTNULL, XMLDB_SEQUENCE, null);
        $table->add_field('productid', XMLDB_TYPE_INTEGER, '10', null, XMLDB_NOTNULL, null, '0');
        $table->add_field('examid', XMLDB_TYPE_INTEGER, '10', null, XMLDB_NOTNULL, null, '0');
        $table->add_field('referenceid', XMLDB_TYPE_INTEGER, '10', null, XMLDB_NOTNULL, null, '0');
        $table->add_field('referencetable', XMLDB_TYPE_CHAR, '100',  null, null, null, null, null, null);
        $table->add_field('userid', XMLDB_TYPE_INTEGER, '10', null, XMLDB_NOTNULL, null, '0');
        $table->add_field('timecreated', XMLDB_TYPE_INTEGER, '10', null, null, null, '0');

        $table->add_key('primary', XMLDB_KEY_PRIMARY, array('id'));
        if (!$dbman->table_exists($table)) {
            $dbman->create_table($table);
        }
        // Main savepoint reached.
        upgrade_plugin_savepoint(true, 2021051707.5, 'local', 'exams');
    }

    if ($oldversion < 2021051707.6) {
        $dbman = $DB->get_manager();
        $table = new xmldb_table('local_exam_profiles');

        $field = new xmldb_field('arprofile', XMLDB_TYPE_INTEGER, '10', null, null, null, '0');
        if ($dbman->field_exists($table, $field)) {
            $dbman->drop_field($table, $field);
        }
        
        $field = new xmldb_field('enprofile', XMLDB_TYPE_INTEGER, '10', null, null, null, '0');
        if ($dbman->field_exists($table, $field)) {
            $dbman->drop_field($table, $field);
        }

        // Main savepoint reached.
        upgrade_plugin_savepoint(true, 2021051707.6, 'local', 'exams');
    }

    if ($oldversion < 2021051707.64) {
        $dbman = $DB->get_manager();

        $table = new xmldb_table('local_exam_userhallschedules');
        $table->add_field('id', XMLDB_TYPE_INTEGER, '10', null, XMLDB_NOTNULL, XMLDB_SEQUENCE, null);
        $table->add_field('examid', XMLDB_TYPE_INTEGER, '10', null, XMLDB_NOTNULL, null, '0');
        $table->add_field('profileid', XMLDB_TYPE_INTEGER, '10', null, XMLDB_NOTNULL, null, '0');
        $table->add_field('hallscheduleid', XMLDB_TYPE_INTEGER, '10', null, XMLDB_NOTNULL, null, '0');
        $table->add_field('attemptid', XMLDB_TYPE_INTEGER, '10', null, XMLDB_NOTNULL, null, '0');
        $table->add_field('examdate', XMLDB_TYPE_INTEGER, '10', null, XMLDB_NOTNULL, null, '0');
        $table->add_field('userid', XMLDB_TYPE_INTEGER, '10', null, XMLDB_NOTNULL, null, '0');
        $table->add_field('timecreated', XMLDB_TYPE_INTEGER, '10', null, null, null, '0');

        $table->add_key('primary', XMLDB_KEY_PRIMARY, array('id'));
        if (!$dbman->table_exists($table)) {
            $dbman->create_table($table);
        }
        // Main savepoint reached.
        upgrade_plugin_savepoint(true, 2021051707.64, 'local', 'exams');
    }
    if ($oldversion < 2021051708.65) {
        $dbman = $DB->get_manager();
        $table = new xmldb_table('local_exams');

        $field = new xmldb_field('ownedbystatus', XMLDB_TYPE_INTEGER, '5', null, XMLDB_NOTNULL, null, 0);
        if (!$dbman->field_exists($table, $field)) {
            $dbman->add_field($table, $field);
        }
        upgrade_plugin_savepoint(true, 2021051708.65, 'local', 'exams');
    }

    if ($oldversion < 2021051708.69) {
    
        $dbman = $DB->get_manager();
        $table = new xmldb_table('local_fast_examenrol');
        $table->add_field('id', XMLDB_TYPE_INTEGER, '10', null, XMLDB_NOTNULL, XMLDB_SEQUENCE, null);
        $table->add_field('username', XMLDB_TYPE_INTEGER, '10', null, XMLDB_NOTNULL, null, '0');
        $table->add_field('centercode', XMLDB_TYPE_CHAR, '255',  null, null, null, null, null, null);
        $table->add_field('examcode', XMLDB_TYPE_CHAR, '255',  null, null, null, null, null, null);
        $table->add_field('profilecode', XMLDB_TYPE_CHAR, '255',  null, null, null, null, null, null);
        $table->add_field('examlanguage', XMLDB_TYPE_CHAR, '255',  null, null, null, null, null, null);
        $table->add_field('examdatetime', XMLDB_TYPE_INTEGER, '10', null, XMLDB_NOTNULL, null, '0');
        $table->add_field('purchasedatetime', XMLDB_TYPE_INTEGER, '10', null, XMLDB_NOTNULL, null, '0');
        $table->add_field('createdbyusername', XMLDB_TYPE_INTEGER, '10', null, XMLDB_NOTNULL, null, '0');
        $table->add_field('billnumber', XMLDB_TYPE_CHAR, '255', null, XMLDB_NOTNULL, null, '0');
        $table->add_field('paymentrefid', XMLDB_TYPE_TEXT, null, null, null, null, null);
        $table->add_field('payementtypes', XMLDB_TYPE_INTEGER, '10', null, null, null, '0');
        $table->add_field('timemodified', XMLDB_TYPE_INTEGER, '10', null, null, null, '0');
        $table->add_field('status', XMLDB_TYPE_INTEGER, '10', null, null, null, '0');
        $table->add_key('primary', XMLDB_KEY_PRIMARY, array('id'));

        if (!$dbman->table_exists($table)) {
            $dbman->create_table($table);
        }
        upgrade_plugin_savepoint(true, 2021051708.69, 'local', 'exams');
    }

    if ($oldversion < 2021051708.71) {
        $dbman = $DB->get_manager();
        $table = new xmldb_table('local_fast_examenrol');
        $field = new xmldb_field('examdatetime', XMLDB_TYPE_CHAR, '255', null, XMLDB_NOTNULL, null, '0');
        if ( $dbman->field_exists($table, $field) ) {
            $dbman->change_field_type($table, $field);
        }

        
        $field1 = new xmldb_field('purchasedatetime', XMLDB_TYPE_CHAR, '255', null, XMLDB_NOTNULL, null, '0');
        if ( $dbman->field_exists($table, $field1) ) {
            $dbman->change_field_type($table, $field1);
        }

        // Main savepoint reached.
        upgrade_plugin_savepoint(true, 2021051708.71, 'local', 'exams');

    }

    if ($oldversion < 2021051708.73) {
        $dbman = $DB->get_manager();
        $table = new xmldb_table('local_fast_examenrol');
        $field = new xmldb_field('createdbyusername', XMLDB_TYPE_CHAR, '255', null, XMLDB_NOTNULL, null, '0');
        if ( $dbman->field_exists($table, $field) ) {
            $dbman->change_field_type($table, $field);
        }
        
        $field1 = new xmldb_field('username', XMLDB_TYPE_CHAR, '255', null, XMLDB_NOTNULL, null, '0');
        if ( $dbman->field_exists($table, $field1) ) {
            $dbman->change_field_type($table, $field1);
        }

       $field2 = new xmldb_field('examid',XMLDB_TYPE_INTEGER, '10', null, XMLDB_NOTNULL, null, '0');
        if (!$dbman->field_exists($table, $field2)) {
            $dbman->add_field($table, $field2);
        }

        $field3 = new xmldb_field('errorcode',XMLDB_TYPE_CHAR, '255', null, null, null, null);
        if (!$dbman->field_exists($table, $field3)) {
            $dbman->add_field($table, $field3);
        }

        $field4 = new xmldb_field('errormessage',XMLDB_TYPE_CHAR, '255', null, null, null, null);
        if (!$dbman->field_exists($table, $field4)) {
            $dbman->add_field($table, $field4);
        }

        $field5 = new xmldb_field('createdbyuserid',XMLDB_TYPE_INTEGER, '10', null, null, null, null);
        if (!$dbman->field_exists($table, $field5)) {
            $dbman->add_field($table, $field5);
        }
        // Main savepoint reached.
        upgrade_plugin_savepoint(true, 2021051708.73, 'local', 'exams');
    }

    if ($oldversion < 2021051708.74) {
        $dbman = $DB->get_manager();
        $table = new xmldb_table('exam_completions');

        $field = new xmldb_field('profileid',XMLDB_TYPE_INTEGER, '10', null, null, null, 0);
        if (!$dbman->field_exists($table, $field)) {
            $dbman->add_field($table, $field);
        }
        // Main savepoint reached.
        upgrade_plugin_savepoint(true, 2021051708.74, 'local', 'exams');
    }

    if ($oldversion < 2021051709.6) {    
        $dbman = $DB->get_manager();
        $table = new xmldb_table('trainee_wallet');
        $table->add_field('id', XMLDB_TYPE_INTEGER, '10', null, XMLDB_NOTNULL, XMLDB_SEQUENCE, null);
        $table->add_field('userid', XMLDB_TYPE_INTEGER, '10', null, XMLDB_NOTNULL, null, '0');
        $table->add_field('roleid', XMLDB_TYPE_INTEGER, '10', null, XMLDB_NOTNULL, null, '0');
        $table->add_field('wallet', XMLDB_TYPE_FLOAT, null, null, XMLDB_NOTNULL, null, '0');
        $table->add_field('usermodified', XMLDB_TYPE_INTEGER, '10', null, XMLDB_NOTNULL, null, '0');
        $table->add_field('timecreated', XMLDB_TYPE_INTEGER, '10', null, XMLDB_NOTNULL, null, '0');
        $table->add_field('timemodified', XMLDB_TYPE_INTEGER, '10', null, XMLDB_NOTNULL, null, '0');
        $table->add_key('primary', XMLDB_KEY_PRIMARY, array('id'));

        if (!$dbman->table_exists($table)) {
            $dbman->create_table($table);
        }

        $table = new xmldb_table('trainee_walletlog');
        $table->add_field('id', XMLDB_TYPE_INTEGER, '10', null, XMLDB_NOTNULL, XMLDB_SEQUENCE, null);
        $table->add_field('walletid', XMLDB_TYPE_INTEGER, '10', null, XMLDB_NOTNULL, null, 0);
        $table->add_field('userid', XMLDB_TYPE_INTEGER, '10', null, null, null, null);
        $table->add_field('roleid', XMLDB_TYPE_INTEGER, '10', null, null, null, '0');
        $table->add_field('paymentstatus', XMLDB_TYPE_CHAR, '255', null, null, null, null);
        $table->add_field('entitytype', XMLDB_TYPE_CHAR, '255', null, null, null, null);
        $table->add_field('entityid', XMLDB_TYPE_INTEGER, '10', null, XMLDB_NOTNULL, null, '0');
        $table->add_field('usercreated', XMLDB_TYPE_INTEGER, '10', null, XMLDB_NOTNULL, null, '0');
        $table->add_field('timecreated', XMLDB_TYPE_INTEGER, '10', null, XMLDB_NOTNULL, null, '0');                
        $table->add_key('primary', XMLDB_KEY_PRIMARY, array('id'));

        if (!$dbman->table_exists($table)) {
            $dbman->create_table($table);
        }
        upgrade_plugin_savepoint(true, 2021051709.6, 'local', 'exams');
    }
    if ($oldversion < 2021051711.6) { 
        $dbman = $DB->get_manager(); 
        $table = new xmldb_table('refund_settings');
        if($dbman->table_exists($table)){
            $time = time();
            $record = new stdClass();
            $record->type = 'cancel';
            $record->entitytype = 'exam';
            $record->dayfrom = 0;
            $record->dayto = 6;
            $record->dedtype = 0;
            $record->dedpercentage = 100;
            $record->dedamount = null;
            $record->timecreated = $time;
            $record->usercreated = 2;
            $record->userid = 2;
            $record->timemodified = 0;
            $record->usermodified = 0;
            $DB->insert_record('refund_settings',$record);
            $time = time();
            $record->type = 'cancel';
            $record->entitytype = 'exam';
            $record->dayfrom = 7;
            $record->dayto = 14;
            $record->dedtype = 0;
            $record->dedpercentage = 50;
            $record->dedamount = null;
            $record->timecreated = $time;
            $record->usercreated = 2;
            $record->userid = 2;
            $record->timemodified = 0;
            $record->usermodified = 0;
            $DB->insert_record('refund_settings',$record);
            $time = time();
            $record->type = 'cancel';
            $record->entitytype = 'exam';        
            $record->dayfrom = 15;
            $record->dayto = 30;
            $record->dedtype = 0;
            $record->dedpercentage = 25;
            $record->dedamount = null;
            $record->timecreated = $time;
            $record->usercreated = 2;
            $record->userid = 2;
            $record->timemodified = 0;
            $record->usermodified = 0;
            $DB->insert_record('refund_settings',$record);
            $time = time();
            $record->type = 'cancel';
            $record->entitytype = 'exam';        
            $record->dayfrom = 31;
            $record->dayto = 334;
            $record->dedtype = 0;
            $record->dedpercentage = 0;
            $record->dedamount = null;
            $record->timecreated = $time;
            $record->usercreated = 2;
            $record->userid = 2;
            $record->timemodified = 0;
            $record->usermodified = 0;
            $DB->insert_record('refund_settings',$record);
    
            $time = time();
            $record = new stdClass();
            $record->type = 'reschedule';
            $record->entitytype = 'exam';
            $record->dayfrom = 8;
            $record->dayto = 358;
            $record->dedtype = 1;
            $record->dedpercentage = null;
            $record->dedamount = 100;
            $record->timecreated = $time;
            $record->usercreated = 2;
            $record->userid = 2;
            $record->timemodified = 0;
            $record->usermodified = 0;
            $DB->insert_record('refund_settings',$record);
            $time = time();
            $record->type = 'reschedule';
            $record->entitytype = 'exam';        
            $record->dayfrom = 3;
            $record->dayto = 7;
            $record->dedtype = 0;
            $record->dedpercentage = 50;
            $record->dedamount = NULL;
            $record->timecreated = $time;
            $record->usercreated = 2;
            $record->userid = 2;
            $record->timemodified = 0;
            $record->usermodified = 0;        
            $DB->insert_record('refund_settings',$record);
            //  Rescheduling policies for CISI (for First Attempt)
            $time = time();
            $record->type = 'reschedule';
            $record->entitytype = 'exam';
            $record->ownedbycisi = 1;
            $record->moreattempts = 0;
            $record->dayfrom = 15;
            $record->dayto = 350;
            $record->dedtype = 1;
            $record->dedpercentage = NULL;
            $record->dedamount = 0;
            $record->timecreated = $time;
            $record->usercreated = 2;
            $record->userid = 2;
            $record->timemodified = 0;
            $record->usermodified = 0;        
            $DB->insert_record('refund_settings',$record);
    
            $time = time();
            $record->type = 'reschedule';
            $record->entitytype = 'exam';
            $record->ownedbycisi = 1;
            $record->moreattempts = 0;
            $record->dayfrom = 2;
            $record->dayto = 14;
            $record->dedtype = 1;
            $record->dedpercentage = NULL;
            $record->dedamount = 100;
            $record->timecreated = $time;
            $record->usercreated = 2;
            $record->userid = 2;
            $record->timemodified = 0;
            $record->usermodified = 0;        
            $DB->insert_record('refund_settings',$record);
            //  Rescheduling policies for CISI (for More than First Attempt)
            $time = time();
            $record->type = 'reschedule';
            $record->entitytype = 'exam';
            $record->ownedbycisi = 1;
            $record->moreattempts = 1;
            $record->dayfrom = 3;
            $record->dayto = 362;
            $record->dedtype = 0;
            $record->dedpercentage = 100;
            $record->dedamount = NULL;
            $record->timecreated = $time;
            $record->usercreated = 2;
            $record->userid = 2;
            $record->timemodified = 0;
            $record->usermodified = 0;        
            $DB->insert_record('refund_settings',$record);
            // Exam Replacement policies for FA
            $time = time();
            $record = new stdClass();
            $record->type = 'replace';
            $record->entitytype = 'exam';
            $record->dayfrom = 0;
            $record->dayto = 2;
            $record->dedtype = 1;
            $record->dedpercentage = null;
            $record->dedamount = 100;
            $record->timecreated = $time;
            $record->usercreated = 2;
            $record->userid = 2;
            $record->timemodified = 0;
            $record->usermodified = 0;
            $DB->insert_record('refund_settings',$record);           
        }
        upgrade_plugin_savepoint(true, 2021051711.6, 'local', 'exams');
    }
    if ($oldversion < 2021051711.7) {    
        $dbman = $DB->get_manager();
        $table = new xmldb_table('local_cancel_logs');
        $table->add_field('id', XMLDB_TYPE_INTEGER, '10', null, XMLDB_NOTNULL, XMLDB_SEQUENCE, null);
        $table->add_field('entitytype', XMLDB_TYPE_CHAR, '255', null, null, null, null);
        $table->add_field('entityid', XMLDB_TYPE_INTEGER, '10', null, XMLDB_NOTNULL, null, '0');
        $table->add_field('productid', XMLDB_TYPE_INTEGER, '10', null, XMLDB_NOTNULL, null, '0');
        $table->add_field('userid', XMLDB_TYPE_INTEGER, '10', null, XMLDB_NOTNULL, null, '0');
        $table->add_field('reason', XMLDB_TYPE_TEXT, null, null, null, null, null);
        $table->add_field('policy', XMLDB_TYPE_INTEGER, '10', null, XMLDB_NOTNULL, null, '0');
        $table->add_field('usercreated', XMLDB_TYPE_INTEGER, '10', null, XMLDB_NOTNULL, null, '0');
        $table->add_field('timecreated', XMLDB_TYPE_INTEGER, '10', null, XMLDB_NOTNULL, null, '0');
        $table->add_field('timemodified', XMLDB_TYPE_INTEGER, '10', null, XMLDB_NOTNULL, null, '0');
        $table->add_key('primary', XMLDB_KEY_PRIMARY, array('id'));

        if (!$dbman->table_exists($table)) {
            $dbman->create_table($table);
        }
        upgrade_plugin_savepoint(true, 2021051711.7, 'local', 'exams');
    }

    if ($oldversion < 2021051711.8) {
        $dbman = $DB->get_manager();
        $table = new xmldb_table('local_cancel_logs');
        $field = new xmldb_field('entityid',XMLDB_TYPE_INTEGER, '10', null, XMLDB_NOTNULL, null, '0');
        if ($dbman->field_exists($table, $field)) {
            $dbman->drop_field($table, $field);
        }
        upgrade_plugin_savepoint(true, 2021051711.8, 'local', 'exams');
    }
    if ($oldversion < 2021051712) {
        $dbman = $DB->get_manager();
        $table = new xmldb_table('local_fast_examenrol');
        $field1 = new xmldb_field('userorganization',XMLDB_TYPE_CHAR, '255', null, XMLDB_NOTNULL, null, '0');
        $field2 = new xmldb_field('reservationid',XMLDB_TYPE_CHAR, '155', null, XMLDB_NOTNULL, null, '0');
        $field3 = new xmldb_field('transactiontypes',XMLDB_TYPE_INTEGER, '1', null, XMLDB_NOTNULL, null, '0');
        $field4 = new xmldb_field('registrationdate',XMLDB_TYPE_CHAR, '255', null, XMLDB_NOTNULL, null, '0');
        $field5 = new xmldb_field('oldexamdatetime',XMLDB_TYPE_CHAR, '255', null, XMLDB_NOTNULL, null, '0');
        $field6 = new xmldb_field('oldcentercode',XMLDB_TYPE_CHAR, '255', null, XMLDB_NOTNULL, null, '0');
        if (!$dbman->field_exists($table, $field1)) {
            $dbman->add_field($table, $field1);
        }
        if (!$dbman->field_exists($table, $field2)) {
            $dbman->add_field($table, $field2);
        }
        if (!$dbman->field_exists($table, $field3)) {
            $dbman->add_field($table, $field3);
        }
        if (!$dbman->field_exists($table, $field4)) {
            $dbman->add_field($table, $field4);
        }
        if (!$dbman->field_exists($table, $field5)) {
            $dbman->add_field($table, $field5);
        }
        if (!$dbman->field_exists($table, $field6)) {
            $dbman->add_field($table, $field6);
        }

        upgrade_plugin_savepoint(true, 2021051712, 'local', 'exams');
    }

    if ($oldversion < 2021051715.1) {
        $dbman = $DB->get_manager();
        $table = new xmldb_table('local_fast_examenrol');

        $field = new xmldb_field('validation',XMLDB_TYPE_INTEGER, '10', null, XMLDB_NOTNULL, null, 0);
        if (!$dbman->field_exists($table, $field)) {
            $dbman->add_field($table, $field);
        }
        // Main savepoint reached.
        upgrade_plugin_savepoint(true, 2021051715.1, 'local', 'exams');
    }
    if ($oldversion < 2021051715.8) {
        $dbman = $DB->get_manager();
        $table = new xmldb_table('local_cancel_logs');

        $field = new xmldb_field('refundamount',XMLDB_TYPE_INTEGER, '10', null, XMLDB_NOTNULL, null, 0);
        if (!$dbman->field_exists($table, $field)) {
            $dbman->add_field($table, $field);
        }
        // Main savepoint reached.
        upgrade_plugin_savepoint(true, 2021051715.8, 'local', 'exams');
    }
    if ($oldversion < 2021051715.9) {
        $dbman = $DB->get_manager();
        $table = new xmldb_table('exam_enrollments');

        $field = new xmldb_field('enrolstatus',XMLDB_TYPE_INTEGER, '10', null, XMLDB_NOTNULL, null, 1);
        if (!$dbman->field_exists($table, $field)) {
            $dbman->add_field($table, $field);
        }
        $field = new xmldb_field('orderid',XMLDB_TYPE_INTEGER, '10', null, XMLDB_NOTNULL, null, 0);
        if (!$dbman->field_exists($table, $field)) {
            $dbman->add_field($table, $field);
        }        
        // Main savepoint reached.
        upgrade_plugin_savepoint(true, 2021051715.9, 'local', 'exams');
    } 
    
    
    if ($oldversion < 2021051716) {    
        $dbman = $DB->get_manager();
        $table = new xmldb_table('local_exams_absenties');
        $table->add_field('id', XMLDB_TYPE_INTEGER, '10', null, XMLDB_NOTNULL, XMLDB_SEQUENCE, null);
        $table->add_field('examid', XMLDB_TYPE_INTEGER, '10', null, XMLDB_NOTNULL, null, '0');
        $table->add_field('profileid', XMLDB_TYPE_INTEGER, '10', null, XMLDB_NOTNULL, null, '0');
        $table->add_field('quizid', XMLDB_TYPE_INTEGER, '10', null, XMLDB_NOTNULL, null, '0');
        $table->add_field('userid', XMLDB_TYPE_INTEGER, '10', null, XMLDB_NOTNULL, null, '0');        
        $table->add_field('status', XMLDB_TYPE_INTEGER, '10', null, XMLDB_NOTNULL, null, '0');       
        $table->add_field('timecreated', XMLDB_TYPE_INTEGER, '10', null, XMLDB_NOTNULL, null, '0');       
        $table->add_key('primary', XMLDB_KEY_PRIMARY, array('id'));

        if (!$dbman->table_exists($table)) {
            $dbman->create_table($table);
        }
        upgrade_plugin_savepoint(true, 2021051716, 'local', 'exams');
    }
    if ($oldversion < 2021051717.2) {
        $dbman = $DB->get_manager();
        $table = new xmldb_table('exam_enrollments');
        $field = new xmldb_field('enrolledby',XMLDB_TYPE_INTEGER, '10', null, null, null, '0');
        if (!$dbman->field_exists($table,$field)) {
            $dbman->add_field($table, $field);
        }
        upgrade_plugin_savepoint(true, 2021051717.2, 'local', 'exams');
    }
    if ($oldversion < 2021051717.6) {
        $dbman = $DB->get_manager();
        $table = new xmldb_table('local_fast_examenrol');

        $fielda = new xmldb_field('usercreated',XMLDB_TYPE_INTEGER, '10', null, null, null, '0');
        $fieldb = new xmldb_field('timecreated',XMLDB_TYPE_INTEGER, '10', null, null, null, '0');
        $fieldc = new xmldb_field('usermodified',XMLDB_TYPE_INTEGER, '10', null, null, null, '0');
        $fieldd = new xmldb_field('timemodified',XMLDB_TYPE_INTEGER, '10', null, null, null, '0');
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
        upgrade_plugin_savepoint(true,2021051717.6, 'local', 'exams');
    }

    if ($oldversion < 2021051717.7) {
        $dbman = $DB->get_manager();
        $table = new xmldb_table('local_exams');
        $field = new xmldb_field('examineeshouldpass',XMLDB_TYPE_INTEGER, '5', null, null, null, '0');
        if (!$dbman->field_exists($table,$field)) {
            $dbman->add_field($table, $field);
        }
        upgrade_plugin_savepoint(true, 2021051717.7, 'local', 'exams');
    }

    if ($oldversion < 2021051718.2) {
        $dbman = $DB->get_manager();
        $table = new xmldb_table('local_exam_attemptpurchases');

        $field = new xmldb_field('usercreated', XMLDB_TYPE_INTEGER, '10', null, null, null, '0');
        if (!$dbman->field_exists($table, $field)) {
            $dbman->add_field($table, $field);
        }
        // Main savepoint reached.
        upgrade_plugin_savepoint(true, 2021051718.2, 'local', 'exams');
    }

    if ($oldversion < 2021051718.6) {
        $dbman = $DB->get_manager();
        $table = new xmldb_table('local_exam_userhallschedules');
       
        $field = new xmldb_field('enrolstatus', XMLDB_TYPE_INTEGER, '5', null, null, null, 1);
        if (!$dbman->field_exists($table, $field)) {
            $dbman->add_field($table, $field);
        }

        $field = new xmldb_field('orderid', XMLDB_TYPE_INTEGER, '10', null, null, null, 0);
        if (!$dbman->field_exists($table, $field)) {
            $dbman->add_field($table, $field);
        }

        $field = new xmldb_field('productid', XMLDB_TYPE_INTEGER, '10', null, null, null, 0);
        if (!$dbman->field_exists($table, $field)) {
            $dbman->add_field($table, $field);
        }

        $field = new xmldb_field('usercreated', XMLDB_TYPE_INTEGER, '10', null, null, null, 0);
        if (!$dbman->field_exists($table, $field)) {
            $dbman->add_field($table, $field);
        }

        $field = new xmldb_field('timemodified', XMLDB_TYPE_INTEGER, '10', null, null, null, 0);
        if (!$dbman->field_exists($table, $field)) {
            $dbman->add_field($table, $field);
        }

        // Main savepoint reached.
        upgrade_plugin_savepoint(true, 2021051718.6, 'local', 'exams');
    }

    if ($oldversion < 2021051718.7) {
        $dbman = $DB->get_manager();
        $table = new xmldb_table('exam_enrollments');

        $field = new xmldb_field('organization', XMLDB_TYPE_INTEGER, '10', null, null, null, '0');
        if (!$dbman->field_exists($table, $field)) {
            $dbman->add_field($table, $field);
        }
        // Main savepoint reached.
        upgrade_plugin_savepoint(true, 2021051718.7, 'local', 'exams');
    }

    if ($oldversion < 2021051718.8) {
        $dbman = $DB->get_manager();
        $table = new xmldb_table('local_exam_userhallschedules');

        $field = new xmldb_field('organization', XMLDB_TYPE_INTEGER, '10', null, null, null, '0');
        if (!$dbman->field_exists($table, $field)) {
            $dbman->add_field($table, $field);
        }
        // Main savepoint reached.
        upgrade_plugin_savepoint(true, 2021051718.8, 'local', 'exams');
    }



    if ($oldversion < 2021051718.9) {
        $dbman = $DB->get_manager();
        $table = new xmldb_table('local_exams');

        $fielda = new xmldb_field('classification',XMLDB_TYPE_INTEGER, '10', null, null, null, '0');
       
        if (!$dbman->field_exists($table, $fielda)) {
            $dbman->add_field($table, $fielda);
        } 
        // Main savepoint reached.
        upgrade_plugin_savepoint(true,2021051718.9, 'local', 'exams');
    }
    if ($oldversion < 2021051719.1) {
        $dbman = $DB->get_manager();
        $table = new xmldb_table('local_exam_profiles');

        $fielda = new xmldb_field('classification',XMLDB_TYPE_INTEGER, '10', null, null, null, '0');
       
        if (!$dbman->field_exists($table, $fielda)) {
            $dbman->add_field($table, $fielda);
        } 
        // Main savepoint reached.
        upgrade_plugin_savepoint(true,2021051719.19, 'local', 'exams');
    }


    if ($oldversion < 2021051721.1) {
        $dbman = $DB->get_manager();

        $table = new xmldb_table('local_exams');

        $field = new xmldb_field('termsconditions', XMLDB_TYPE_TEXT, null, null, null, null, null);
        if (!$dbman->field_exists($table, $field)) {
            $dbman->add_field($table, $field);
        }
    


        $table = new xmldb_table('exam_enrollments');

        $field = new xmldb_field('tandcconfirm',XMLDB_TYPE_INTEGER, '10', null, XMLDB_NOTNULL, null, 0);
        if (!$dbman->field_exists($table, $field)) {
            $dbman->add_field($table, $field);
            
        }
        upgrade_plugin_savepoint(true, 2021051721.1, 'local', 'exams');
    }

    if ($oldversion < 2021051721.4) {
        $dbman = $DB->get_manager();
        $tableA = new xmldb_table('exam_enrollments');
        $tableB = new xmldb_table('local_exam_userhallschedules');
        $field = new xmldb_field('enrolltype', XMLDB_TYPE_INTEGER, '12', null, XMLDB_NOTNULL, null, '0',null);
        if (!$dbman->field_exists($tableA, $field)) {
            $dbman->add_field($tableA, $field);
        } 
        if (!$dbman->field_exists($tableB, $field)) {
            $dbman->add_field($tableB, $field);
        } 
        // Main savepoint reached.
        upgrade_plugin_savepoint(true,2021051721.4, 'local', 'exams');
    }

if ($oldversion < 2021051721.3) {    
        $dbman = $DB->get_manager();
        $table = new xmldb_table('local_absent_logs');
        $table->add_field('id', XMLDB_TYPE_INTEGER, '10', null, XMLDB_NOTNULL, XMLDB_SEQUENCE, null);
        $table->add_field('entitytype', XMLDB_TYPE_CHAR, '255', null, null, null, null);
        $table->add_field('entityid', XMLDB_TYPE_INTEGER, '10', null, XMLDB_NOTNULL, null, '0');
        $table->add_field('productid', XMLDB_TYPE_INTEGER, '10', null, XMLDB_NOTNULL, null, '0');
        $table->add_field('userid', XMLDB_TYPE_INTEGER, '10', null, XMLDB_NOTNULL, null, '0');
        $table->add_field('reason', XMLDB_TYPE_TEXT, null, null, null, null, null);
        $table->add_field('policy', XMLDB_TYPE_INTEGER, '10', null, XMLDB_NOTNULL, null, '0');
        $table->add_field('usercreated', XMLDB_TYPE_INTEGER, '10', null, XMLDB_NOTNULL, null, '0');
        $table->add_field('timecreated', XMLDB_TYPE_INTEGER, '10', null, XMLDB_NOTNULL, null, '0');
        $table->add_field('timemodified', XMLDB_TYPE_INTEGER, '10', null, XMLDB_NOTNULL, null, '0');
        $table->add_key('primary', XMLDB_KEY_PRIMARY, array('id'));

        if (!$dbman->table_exists($table)) {
            $dbman->create_table($table);
        }
        upgrade_plugin_savepoint(true, 2021051721.3, 'local', 'exams');
    }

    if ($oldversion < 2021051721.4) {
        $dbman = $DB->get_manager();
        $tableA= new xmldb_table('exam_enrollments');
        $tableB= new xmldb_table('exam_completions');
        $tableC= new xmldb_table('local_exam_grievance');
        $tableD= new xmldb_table('local_exam_attemptpurchases');
        $tableE= new xmldb_table('local_exam_userhallschedules');
        $tableF= new xmldb_table('local_fast_examenrol');
        $tableG= new xmldb_table('local_cancel_logs');
        $tableH= new xmldb_table('local_exams_absenties');
        $tableI= new xmldb_table('local_absent_logs');
        $field=  new xmldb_field('realuser', XMLDB_TYPE_INTEGER, '10', null, XMLDB_NOTNULL, null, '0','userid');
        $fieldO=  new xmldb_field('realuser', XMLDB_TYPE_INTEGER, '10', null, XMLDB_NOTNULL, null, '0','username');
        
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

        if (!$dbman->field_exists($tableE, $field)) {
            $dbman->add_field($tableE, $field);
        }

        if (!$dbman->field_exists($tableF, $fieldO)) {
            $dbman->add_field($tableF, $fieldO);
        }

        if (!$dbman->field_exists($tableG, $field)) {
            $dbman->add_field($tableG, $field);
        }

        if (!$dbman->field_exists($tableH, $field)) {
            $dbman->add_field($tableH, $field);
        }

         if (!$dbman->field_exists($tableI, $field)) {
            $dbman->add_field($tableI, $field);
        }

        upgrade_plugin_savepoint(true, 2021051721.4, 'local', 'exams');
    }
    if ($oldversion < 2021051721.8) {
        $dbman = $DB->get_manager();
        $tableA = new xmldb_table('exam_enrollments');
        $tableB = new xmldb_table('local_exam_userhallschedules');
        $field = new xmldb_field('orgofficial',XMLDB_TYPE_INTEGER, '12', null, XMLDB_NOTNULL, null, '0','organization');
        if (!$dbman->field_exists($tableA, $field)) {
            $dbman->add_field($tableA, $field);
        } 
        if (!$dbman->field_exists($tableB, $field)) {
            $dbman->add_field($tableB, $field);
        } 
        upgrade_plugin_savepoint(true,2021051721.8, 'local', 'exams');
    }

    if ($oldversion < 2021051722.4) {
        $dbman = $DB->get_manager();
        $tableA = new xmldb_table('exam_enrollments');
        $tableB = new xmldb_table('local_exam_userhallschedules');
        $field = new xmldb_field('enrolltype', XMLDB_TYPE_INTEGER, '12', null, XMLDB_NOTNULL, null, '0',null);

    //    $DB->execute("Update {exam_enrollments} SET enrolltype = 0 WHERE enrolltype IS NULL") ;
    //    $DB->execute("Update {exam_enrollments} SET enrolltype = 0 WHERE enrolltype =''") ;
    //    $DB->execute("Update {exam_enrollments} SET enrolltype = 1 WHERE enrolltype ='bulkenrollment'") ;
    //    $DB->execute("Update {exam_enrollments} SET enrolltype = 2 WHERE enrolltype ='bulkenroll'") ;
    //    $DB->execute("Update {exam_enrollments} SET enrolltype = 0 WHERE enrolltype NOT regexp '^[0-9]+$'") ;

    //    $DB->execute("Update {local_exam_userhallschedules} SET enrolltype = 0 WHERE enrolltype IS NULL") ;
    //    $DB->execute("Update {local_exam_userhallschedules} SET enrolltype = 0 WHERE enrolltype =''") ;
    //    $DB->execute("Update {local_exam_userhallschedules} SET enrolltype = 1 WHERE enrolltype ='bulkenrollment'") ;
    //    $DB->execute("Update {local_exam_userhallschedules} SET enrolltype = 2 WHERE enrolltype ='bulkenroll'") ;
    //    $DB->execute("Update {local_exam_userhallschedules} SET enrolltype = 0 WHERE enrolltype NOT regexp '^[0-9]+$'") ;

        if ($dbman->field_exists($tableA, $field)) {
            $dbman->change_field_type($tableA, $field);
        } 
        if ($dbman->field_exists($tableB, $field)) {
            $dbman->change_field_type($tableB, $field);
        } 
        // Main savepoint reached.
        upgrade_plugin_savepoint(true,2021051722.4, 'local', 'exams');
    }
    if ($oldversion < 2021051723.8) {
        $dbman = $DB->get_manager();
        $table = new xmldb_table('local_exam_profiles');
        $field = new xmldb_field('discount',XMLDB_TYPE_INTEGER, '10', null, XMLDB_NOTNULL, null, 0);
        if ($dbman->field_exists($table, $field)) {
            $dbman->drop_field($table, $field);
        } 
         upgrade_plugin_savepoint(true,2021051723.8, 'local', 'exams');

        }

    if ($oldversion < 2021051723.9) {
        $dbman = $DB->get_manager();
        $table = new xmldb_table('local_absent_logs');
        $table->add_field('id', XMLDB_TYPE_INTEGER, '10', null, XMLDB_NOTNULL, XMLDB_SEQUENCE, null);
        $table->add_field('entitytype', XMLDB_TYPE_CHAR, '255', null, null, null, null);
        $table->add_field('entityid', XMLDB_TYPE_INTEGER, '10', null, XMLDB_NOTNULL, null, '0');
        $table->add_field('productid', XMLDB_TYPE_INTEGER, '10', null, XMLDB_NOTNULL, null, '0');
        $table->add_field('userid', XMLDB_TYPE_INTEGER, '10', null, XMLDB_NOTNULL, null, '0');
        $table->add_field('reason', XMLDB_TYPE_TEXT, null, null, null, null, null);
        $table->add_field('policy', XMLDB_TYPE_INTEGER, '10', null, XMLDB_NOTNULL, null, '0');
        $table->add_field('usercreated', XMLDB_TYPE_INTEGER, '10', null, XMLDB_NOTNULL, null, '0');
        $table->add_field('timecreated', XMLDB_TYPE_INTEGER, '10', null, XMLDB_NOTNULL, null, '0');
        $table->add_field('timemodified', XMLDB_TYPE_INTEGER, '10', null, XMLDB_NOTNULL, null, '0');
        $table->add_key('primary', XMLDB_KEY_PRIMARY, array('id'));
        if (!$dbman->table_exists($table)) {
            $dbman->create_table($table);
        }
        upgrade_plugin_savepoint(true, 2021051723.9, 'local', 'exams');
    }
    return true;
}
