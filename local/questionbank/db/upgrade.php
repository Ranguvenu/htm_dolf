<?php
defined('MOODLE_INTERNAL') || die();
function xmldb_local_questionbank_upgrade($oldversion) {
   global $DB, $CFG;
   if ($oldversion < 2021051702.21) {
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
    $initcontent = array('name' => 'Questionbank','shortname' => 'questionbank','parent_module' => '0','usercreated' => '2','timecreated' => $time,'usermodified' => 2,'timemodified' => NULL, 'pluginname' => 'questionbank','plugintype' => 'local');
    $parentid = $DB->get_field('local_notification_type', 'id', array('shortname' => 'questionbank'));
    if(!$parentid){
        $parentid = $DB->insert_record('local_notification_type', $initcontent);
    }
    $notification_type_data = array(
        array('name' => 'Workshop created','shortname' => 'questionbank_workshop_created','parent_module' => $parentid,'usercreated' => '2','timecreated' => $time,'usermodified' => 2,'timemodified' => NULL, 'pluginname' => 'questionbank','plugintype' => 'local'),
        array('name' => 'Workshop updated','shortname' => 'questionbank_workshop_updated','parent_module' => $parentid,'usercreated' => '2','timecreated' => $time,'usermodified' => 2,'timemodified' => NULL, 'pluginname' => 'questionbank','plugintype' => 'local'),
        array('name' => 'Assign expert','shortname' => 'questionbank_assign_expert','parent_module' => $parentid,'usercreated' => '2','timecreated' => $time,'usermodified' => 2,'timemodified' => NULL, 'pluginname' => 'questionbank','plugintype' => 'local'),
        array('name' => 'Assign exam official','shortname' => 'questionbank_assign_exam_official','parent_module' => $parentid,'usercreated' => '2','timecreated' => $time,'usermodified' => 2,'timemodified' => NULL, 'pluginname' => 'questionbank','plugintype' => 'local'),
        array('name' => 'Assign reviewer','shortname' => 'questionbank_assign_reviewer','parent_module' => $parentid,'usercreated' => '2','timecreated' => $time,'usermodified' => 2,'timemodified' => NULL, 'pluginname' => 'questionbank','plugintype' => 'local'),
        array('name' => 'Question under review','shortname' => 'questionbank_question_under_review','parent_module' => $parentid,'usercreated' => '2','timecreated' => $time,'usermodified' => 2,'timemodified' => NULL, 'pluginname' => 'questionbank','plugintype' => 'local'),
        array('name' => 'Question reviewed','shortname' => 'questionbank_question_reviewed','parent_module' => $parentid,'usercreated' => '2','timecreated' => $time,'usermodified' => 2,'timemodified' => NULL, 'pluginname' => 'questionbank','plugintype' => 'local'),
        array('name' => 'Adding question to questionbank','shortname' => 'questionbank_question_added','parent_module' => $parentid,'usercreated' => '2','timecreated' => $time,'usermodified' => 2,'timemodified' => NULL, 'pluginname' => 'questionbank','plugintype' => 'local'),
        array('name' => 'On Change','shortname' => 'questionbank_onchange','parent_module' => $parentid,'usercreated' => '2','timecreated' => $time,'usermodified' => 2,'timemodified' => NULL, 'pluginname' => 'questionbank','plugintype' => 'local'),
        array('name' => 'Cancel','shortname' => 'questionbank_cancel','parent_module' => $parentid,'usercreated' => '2','timecreated' => $time,'usermodified' => 2,'timemodified' => NULL, 'pluginname' => 'questionbank','plugintype' => 'local'),
        array('name' => 'Re-Schedule','shortname' => 'questionbank_reschedule','parent_module' => $parentid,'usercreated' => '2','timecreated' => $time,'usermodified' => 2,'timemodified' => NULL, 'pluginname' => 'questionbank','plugintype' => 'local'),
      
    );
    foreach($notification_type_data as $notification_type){
        unset($notification_type['timecreated']);
        if(!$DB->record_exists('local_notification_type',  $notification_type)){
            $notification_type['timecreated'] = $time;
            $DB->insert_record('local_notification_type', $notification_type);
        }
    }
    upgrade_plugin_savepoint(true, 2021051702.21, 'local', 'questionbank');
}
 if ($oldversion < 2021051702.27) {
    
        $dbman = $DB->get_manager();
        $table = new xmldb_table('local_questionbank');
        $field = new xmldb_field('duration',XMLDB_TYPE_INTEGER, '10', null, null, null);
         if (!$dbman->field_exists($table, $field)) {
            $dbman->add_field($table, $field);
        }  
        
        upgrade_plugin_savepoint(true, 2021051702.27, 'local', 'questionbank');
    }

    if ($oldversion < 2021051702.26) {
         $thiscontext = context_system::instance();
     $edittab = 'categories';
     if ($thiscontext){
                    $contexts = new question_edit_contexts($thiscontext);
                    $contexts->require_one_edit_tab_cap($edittab);
    } else {
                    $contexts = null;
    }
    $defaultcategory = question_make_default_categories($contexts->all());
    $question_category = $DB->get_record_sql("SELECT * FROM {question_categories} where name ='top' and parent= 0 AND contextid=1");
    $thispageurl = new moodle_url($CFG->wwwroot);
    $qcobject = new question_category_object($thiscontext->id, $thispageurl,
    $contexts->having_one_edit_tab_cap('categories'), $param->edit,
                                $question_category->id, $param->delete, $contexts->having_cap('moodle/question:add'));
    $data->workshopname = 'Workshop Categories';
    $data->workshopcategory = 'workshop_categories';
    if ($question_category) {//new category
        $newparent = $question_category->id.','.$thiscontext->id;
        $categoryid=$qcobject->add_category($newparent, $data->workshopname,
                               $question_category->info, $thiscontext->id, $question_category->infoformat,  $data->workshopcategory);
    } 
    }
    if ($oldversion < 2021051702.50) {
    
        $dbman = $DB->get_manager();
        $table = new xmldb_table('local_questionbank');
        $field = new xmldb_field('movedtoprod',XMLDB_TYPE_INTEGER, '10', null, null, null);
         if (!$dbman->field_exists($table, $field)) {
            $dbman->add_field($table, $field);
        } 
        $field1 = new xmldb_field('tocategoryid',XMLDB_TYPE_INTEGER, '10', null, null, null);
         if (!$dbman->field_exists($table, $field1)) {
            $dbman->add_field($table, $field1);
        }   
        
        upgrade_plugin_savepoint(true,  2021051702.50, 'local', 'questionbank');
    }
    if ($oldversion < 2021051704) {
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
                'body' => '<p dir="ltr" style="text-align: left;"></p><p dir="ltr">Dear [FullName],</p><p dir="ltr">[ReviewerName] has been assigned as reviewer for [QuestionBankName] .<br></p><p dir="ltr">Thanks</p><br><p></p>',
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
        upgrade_plugin_savepoint(true,  2021051704, 'local', 'questionbank');
    }
    //  if ($oldversion < 2021051704.3) {
    
    //     $dbman = $DB->get_manager();
    //     $table = new xmldb_table('local_qb_experts');
    //     $field = new xmldb_field('noofquestions',XMLDB_TYPE_INTEGER, '10', null, null, null);
    //      if (!$dbman->field_exists($table, $field)) {
    //         $dbman->add_field($table, $field);
    //     }  
        
    //     upgrade_plugin_savepoint(true, 2021051704.3, 'local', 'questionbank');
    // }
    if ($oldversion < 2021051704.6) {
    
        $dbman = $DB->get_manager();
        $table = new xmldb_table('local_qb_experts');
        $field = new xmldb_field('userupdated',XMLDB_TYPE_INTEGER, '10', null, null, null);
         if (!$dbman->field_exists($table, $field)) {
            $dbman->add_field($table, $field);
        } 
        $field = new xmldb_field('timemodified',XMLDB_TYPE_INTEGER, '10', null, null, null);
         if (!$dbman->field_exists($table, $field)) {
            $dbman->add_field($table, $field);
        } 
        $field = new xmldb_field('timemodified',XMLDB_TYPE_INTEGER, '10', null, null, null);
         if (!$dbman->field_exists($table, $field)) {
            $dbman->add_field($table, $field);
        }
        upgrade_plugin_savepoint(true, 2021051704.6, 'local', 'questionbank');
    }
    if ($oldversion < 2021051704.9) {
    
        $dbman = $DB->get_manager();
        $table = new xmldb_table('local_qb_experts');
        $field = new xmldb_field('noofquestions',XMLDB_TYPE_INTEGER, '10', null, null, null);
         if (!$dbman->field_exists($table, $field)) {
            $dbman->add_field($table, $field);
        } 
        $field = new xmldb_field('userupdated',XMLDB_TYPE_INTEGER, '10', null, null, null);
         if (!$dbman->field_exists($table, $field)) {
            $dbman->add_field($table, $field);
        } 
        $field = new xmldb_field('timemodified',XMLDB_TYPE_INTEGER, '10', null, null, null);
         if (!$dbman->field_exists($table, $field)) {
            $dbman->add_field($table, $field);
        }
        upgrade_plugin_savepoint(true, 2021051704.9, 'local', 'questionbank');
    }
    if ($oldversion < 2021051705.7) {
    
        $dbman = $DB->get_manager();
        $table = new xmldb_table('local_questionbank');
       
        $field = new xmldb_field('generatecode',XMLDB_TYPE_INTEGER, '10', null, null, null);
         if (!$dbman->field_exists($table, $field)) {
            $dbman->add_field($table, $field);
        }
        upgrade_plugin_savepoint(true, 2021051705.7, 'local', 'questionbank');
    }
    if ($oldversion < 2021051706.1) {
    
        $dbman = $DB->get_manager();
        $table = new xmldb_table('local_questionbank');
       
        $field = new xmldb_field('generatecode',XMLDB_TYPE_CHAR, '255', null, null, null);
         if ($dbman->field_exists($table, $field)) {
            $dbman->change_field_type($table, $field);
        }else{
            $dbman->add_field($table, $field);
        }
        upgrade_plugin_savepoint(true, 2021051706.1, 'local', 'questionbank');
    }
    if ($oldversion < 2021051706.12) {
    
        $dbman = $DB->get_manager();
        $table = new xmldb_table('local_qb_coursetopics');
       
        $field = new xmldb_field('topic',XMLDB_TYPE_CHAR, '255', null, null, null);
         if ($dbman->field_exists($table, $field)) {
            $dbman->change_field_type($table, $field);
        }else{
            $dbman->add_field($table, $field);
        }
        upgrade_plugin_savepoint(true, 2021051706.12, 'local', 'questionbank');
    }
    if ($oldversion < 2021051706.2) {
    
        $dbman = $DB->get_manager();
        $table = new xmldb_table('local_qb_questioncourses');
       
        $field = new xmldb_field('topic',XMLDB_TYPE_CHAR, '255', null, null, null);
         if ($dbman->field_exists($table, $field)) {
            $dbman->change_field_type($table, $field);
        }else{
            $dbman->add_field($table, $field);
        }
        upgrade_plugin_savepoint(true, 2021051706.2, 'local', 'questionbank');
    }
   return true;
}
