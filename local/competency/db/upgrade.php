<?php
defined('MOODLE_INTERNAL') || die();
function xmldb_local_competency_upgrade($oldversion) {
    global $DB, $CFG;

    $dbman = $DB->get_manager();
   
    if ($oldversion < 2022041100.07) {
    
        $dbman = $DB->get_manager();
        $table = new xmldb_table('local_competencies');
        $field1 = new xmldb_field('jobrole',XMLDB_TYPE_INTEGER, '10', null, XMLDB_NOTNULL, null);

        $newname1='jobroleid';
   
        if ($dbman->field_exists($table, $field1)) {
            $dbman->rename_field($table, $field1, $newname1);
        }
 
        upgrade_plugin_savepoint(true, 2022041100.07, 'local', 'competency');
    }

    if ($oldversion < 2022041100.12) {

        $dbman = $DB->get_manager();

        $table = new xmldb_table('local_competency_obj');

        if ($dbman->table_exists($table)) {

            $dbman->drop_table($table);
        }

        $table = new xmldb_table('local_competencypc_obj');
        if (!$dbman->table_exists($table)) {
            // Adding fields.
            $table->add_field('id', XMLDB_TYPE_INTEGER, '10', null, XMLDB_NOTNULL, XMLDB_SEQUENCE, null);
            $table->add_field('competencypc', XMLDB_TYPE_INTEGER, '10', null, XMLDB_NOTNULL, null, 0);
            $table->add_field('examids', XMLDB_TYPE_TEXT, null, null, null, null, null);
            $table->add_field('trainingprogramids', XMLDB_TYPE_TEXT, null, null, null, null, '0');
            $table->add_field('timemodified', XMLDB_TYPE_INTEGER, '10', null, null, null, null);
            $table->add_field('usermodified', XMLDB_TYPE_INTEGER, '10', null, null, null, null);
            $table->add_field('timecreated', XMLDB_TYPE_INTEGER, '10', null, XMLDB_NOTNULL, null, '0');
            $table->add_field('usercreated', XMLDB_TYPE_INTEGER, '10', null, XMLDB_NOTNULL, null, '0');

            // Adding key.
            $table->add_key('primary', XMLDB_KEY_PRIMARY, array('id'));

            // Create table.
            $dbman->create_table($table);
        }


         upgrade_plugin_savepoint(true, 2022041100.12, 'local', 'competency');
    }
    if ($oldversion < 2022041100.13) {

        $dbman = $DB->get_manager();

        $table = new xmldb_table('local_competencypc_obj');
        
        $field = new xmldb_field('competency', XMLDB_TYPE_INTEGER, '10', null, XMLDB_NOTNULL, null, 0);
        if (!$dbman->field_exists($table, $field)) {
            $dbman->add_field($table, $field);
        }

        upgrade_plugin_savepoint(true, 2022041100.13, 'local', 'competency');
    }
    if ($oldversion < 2022041100.18) {

        $dbman = $DB->get_manager();

        $table = new xmldb_table('local_cmtncypc_completions');
        $table->add_field('id', XMLDB_TYPE_INTEGER, '10', null, XMLDB_NOTNULL, XMLDB_SEQUENCE, null);
        $table->add_field('trainingprogramid', XMLDB_TYPE_INTEGER, '10', null, null, null, '0');
        $table->add_field('examid', XMLDB_TYPE_INTEGER, '10', null, null, null, '0');
        $table->add_field('competencyid', XMLDB_TYPE_INTEGER, '10', null, XMLDB_NOTNULL, null, '0');
        $table->add_field('competencypcid', XMLDB_TYPE_INTEGER, '10', null, XMLDB_NOTNULL, null, '0');
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

        $table = new xmldb_table('local_cmtncy_completions');
        $table->add_field('id', XMLDB_TYPE_INTEGER, '10', null, XMLDB_NOTNULL, XMLDB_SEQUENCE, null);
        $table->add_field('competencyid', XMLDB_TYPE_INTEGER, '10', null, XMLDB_NOTNULL, null, '0');
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

        upgrade_plugin_savepoint(true, 2022041100.18, 'local', 'competency');
    }

    if ($oldversion < 2022041100.22) {

        $dbman = $DB->get_manager();

        $table = new xmldb_table('local_competencypc_obj');
        
        $field = new xmldb_field('questionids', XMLDB_TYPE_TEXT, null, null, null, null, null);
        if (!$dbman->field_exists($table, $field)) {
            $dbman->add_field($table, $field);
        }

        upgrade_plugin_savepoint(true, 2022041100.22, 'local', 'competency');
    }
    if ($oldversion < 2022041100.24) {
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
        $initcontent = array('name' => 'Competency','shortname' => 'competency','parent_module' => '0','usercreated' => '2','timecreated' => $time,'usermodified' => 2,'timemodified' => NULL, 'pluginname' => 'competency');
        $parentid = $DB->get_field('local_notification_type', 'id', array('shortname' => 'competency'));
        if(!$parentid){
            $parentid = $DB->insert_record('local_notification_type', $initcontent);
        }
       $notification_type_data = array(
            array('name' => 'Competency Completions','shortname' => 'competency_completions','parent_module' => $parentid,'usercreated' => '2','timecreated' => $time,'usermodified' => 2,'timemodified' => NULL, 'pluginname' => 'competency','plugintype' => 'local'),
            array('name' => 'Competency Adding Learning Item','shortname' => 'competency_adding_learning_item','parent_module' => $parentid,'usercreated' => '2','timecreated' => $time,'usermodified' => 2,'timemodified' => NULL, 'pluginname' => 'competency','plugintype' => 'local'),
            array('name' => 'Competency Removing Learning Item','shortname' => 'competency_removing_learning_item','parent_module' => $parentid,'usercreated' => '2','timecreated' => $time,'usermodified' => 2,'timemodified' => NULL, 'pluginname' => 'competency','plugintype' => 'local'),

        );
        foreach($notification_type_data as $notification_type){
            unset($notification_type['timecreated']);
            if(!$DB->record_exists('local_notification_type',  $notification_type)){
                $notification_type['timecreated'] = $time;
                $DB->insert_record('local_notification_type', $notification_type);
            }
        }
        upgrade_plugin_savepoint(true, 2022041100.24, 'local', 'competency');
    }
    if ($oldversion < 2022041100.28) {

        $table = new xmldb_table('local_competencies');
        $field = new xmldb_field('jobroleid');
        $field->set_attributes(XMLDB_TYPE_TEXT, null, null, XMLDB_NOTNULL, null, null);
        try {
            $dbman->change_field_type($table, $field);
        } catch (moodle_exception $e) {

        }

        upgrade_plugin_savepoint(true, 2022041100.28, 'local', 'competency');

    }

    if ($oldversion < 2022041100.29) {

        $dbman = $DB->get_manager();

        $table = new xmldb_table('local_competencypc_obj');
        
        $field = new xmldb_field('jobrolelevelids', XMLDB_TYPE_TEXT, null, null, null, null, null);
        if (!$dbman->field_exists($table, $field)) {
            $dbman->add_field($table, $field);
        }

        upgrade_plugin_savepoint(true,2022041100.29, 'local', 'competency');
    }
     if ($oldversion < 2022041100.4) {
    
        $dbman = $DB->get_manager();
        $table = new xmldb_table('local_competencies');
        $competenciesfield = new xmldb_field('arabicname',XMLDB_TYPE_TEXT,null, null, null, null, null, null);
         if (!$dbman->field_exists($table, $competenciesfield)) {
            $dbman->add_field($table, $competenciesfield);
        }
        $table = new xmldb_table('local_competency_pc');
        $criterianamearabic = new xmldb_field('criterianamearabic',XMLDB_TYPE_TEXT,null, null, null, null, null, null);
         if (!$dbman->field_exists($table, $criterianamearabic)) {
            $dbman->add_field($table, $criterianamearabic);
        }
        $kpinamearabic = new xmldb_field('kpinamearabic',XMLDB_TYPE_TEXT,null, null, null, null, null, null);
         if (!$dbman->field_exists($table, $kpinamearabic)) {
            $dbman->add_field($table, $kpinamearabic);
        }
        $objectiveidarabic = new xmldb_field('objectiveidarabic',XMLDB_TYPE_TEXT,null, null, null, null, null, null);
         if (!$dbman->field_exists($table, $objectiveidarabic)) {
            $dbman->add_field($table, $objectiveidarabic);
        }
        
             
        upgrade_plugin_savepoint(true, 2022041100.4, 'local', 'competency');
    }
    if ($oldversion < 2022041100.5) {

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

        upgrade_plugin_savepoint(true,2022041100.5, 'local', 'competency');
    }
    if ($oldversion < 2022041100.6) {
    
        $dbman = $DB->get_manager();
        $table = new xmldb_table('local_competencies');
        $competenciesfield = new xmldb_field('arabicname',XMLDB_TYPE_TEXT,null, null, null, null, null, null);
         if (!$dbman->field_exists($table, $competenciesfield)) {
            $dbman->add_field($table, $competenciesfield);
        }
        $table = new xmldb_table('local_competency_pc');
        $criterianamearabic = new xmldb_field('criterianamearabic',XMLDB_TYPE_TEXT,null, null, null, null, null, null);
         if (!$dbman->field_exists($table, $criterianamearabic)) {
            $dbman->add_field($table, $criterianamearabic);
        }
        $kpinamearabic = new xmldb_field('kpinamearabic',XMLDB_TYPE_TEXT,null, null, null, null, null, null);
         if (!$dbman->field_exists($table, $kpinamearabic)) {
            $dbman->add_field($table, $kpinamearabic);
        }
        $objectiveidarabic = new xmldb_field('objectiveidarabic',XMLDB_TYPE_TEXT,null, null, null, null, null, null);
         if (!$dbman->field_exists($table, $objectiveidarabic)) {
            $dbman->add_field($table, $objectiveidarabic);
        }
        
             
        upgrade_plugin_savepoint(true,2022041100.6, 'local', 'competency');
    }
    if ($oldversion < 2022041100.8) {
    
        $dbman = $DB->get_manager();
        $table = new xmldb_table('local_competencies');
        $competenciesfield = new xmldb_field('oldid', XMLDB_TYPE_INTEGER, '10', null, null, null, '0');
         if (!$dbman->field_exists($table, $competenciesfield)) {
            $dbman->add_field($table, $competenciesfield);
        }
        $table = new xmldb_table('local_competency_pc');
        $competencypcfield = new xmldb_field('oldid', XMLDB_TYPE_INTEGER, '10', null, null, null, '0');
         if (!$dbman->field_exists($table, $competencypcfield)) {
            $dbman->add_field($table, $competencypcfield);
        } 
        $table = new xmldb_table('local_competencypc_obj');
        $competencypcobjfield = new xmldb_field('oldid', XMLDB_TYPE_INTEGER, '10', null, null, null, '0');
         if (!$dbman->field_exists($table, $competencypcobjfield)) {
            $dbman->add_field($table, $competencypcobjfield);
        } 
             
        upgrade_plugin_savepoint(true, 2022041100.8, 'local', 'competency');
    }

    if ($oldversion < 2022041101) {
        $dbman = $DB->get_manager();
        $table = new xmldb_table('local_competencies');
        $field = new xmldb_field('oldid', XMLDB_TYPE_CHAR, '255', null, XMLDB_NOTNULL, null, '0');
        if ( $dbman->field_exists($table, $field) ) {
            $dbman->change_field_type($table, $field);
        }


        $table = new xmldb_table('local_competency_pc');
        $field = new xmldb_field('oldid', XMLDB_TYPE_CHAR, '255', null, XMLDB_NOTNULL, null, '0');
        if ( $dbman->field_exists($table, $field) ) {
            $dbman->change_field_type($table, $field);
        }


        $table = new xmldb_table('local_competencypc_obj');
        $field = new xmldb_field('oldid', XMLDB_TYPE_CHAR, '255', null, XMLDB_NOTNULL, null, '0');
        if ( $dbman->field_exists($table, $field) ) {
            $dbman->change_field_type($table, $field);
        }

        $table = new xmldb_table('local_cmtncypc_completions');
        $field = new xmldb_field('oldid', XMLDB_TYPE_CHAR, '255', null, null, null, null);
        if ( !$dbman->field_exists($table, $field) ) {
            $dbman->add_field($table, $field);
        }

        // Main savepoint reached.
        upgrade_plugin_savepoint(true, 2022041101, 'local', 'competency');

    }

    // if ($oldversion < 2022041101.02) {

    //      $dbman = $DB->get_manager();


    //     $table = new xmldb_table('local_competency_pc');

    //     $criterianamearabic = new xmldb_field('criterianamearabic', XMLDB_TYPE_TEXT, null, null, XMLDB_NOTNULL, null, '0');

    //      if ($dbman->field_exists($table, $criterianamearabic)) {
    //         $dbman->change_field_default($table, $criterianamearabic);
    //     }

    //     $kpinamearabic = new xmldb_field('kpinamearabic', XMLDB_TYPE_TEXT, null, null, XMLDB_NOTNULL, null, '0');

    //      if ($dbman->field_exists($table, $kpinamearabic)) {
    //         $dbman->change_field_default($table, $kpinamearabic);
    //     }

    //     $objectiveidarabic = new xmldb_field('objectiveidarabic', XMLDB_TYPE_TEXT,null, null, XMLDB_NOTNULL, null, '0');

    //      if ($dbman->field_exists($table, $objectiveidarabic)) {
    //         $dbman->change_field_default($table, $objectiveidarabic);
    //     }


    //      upgrade_plugin_savepoint(true, 2022041101.02, 'local', 'competency');

    // }

     if ($oldversion < 2022041101.02) {

        $dbman = $DB->get_manager();

        $table = new xmldb_table('local_cmtncy_level');
        if (!$dbman->table_exists($table)) {
            // Adding fields.
            $table->add_field('id', XMLDB_TYPE_INTEGER, '10', null, XMLDB_NOTNULL, XMLDB_SEQUENCE, null);
            $table->add_field('competencyid', XMLDB_TYPE_INTEGER, '10', null, XMLDB_NOTNULL, null, 0);
            $table->add_field('levelid', XMLDB_TYPE_CHAR, '255', null, null, null, null);
            $table->add_field('description', XMLDB_TYPE_TEXT, null, null, null, null, '0');
            $table->add_field('timemodified', XMLDB_TYPE_INTEGER, '10', null, null, null, null);
            $table->add_field('usermodified', XMLDB_TYPE_INTEGER, '10', null, null, null, null);
            $table->add_field('timecreated', XMLDB_TYPE_INTEGER, '10', null, XMLDB_NOTNULL, null, '0');
            $table->add_field('usercreated', XMLDB_TYPE_INTEGER, '10', null, XMLDB_NOTNULL, null, '0');

            // Adding key.
            $table->add_key('primary', XMLDB_KEY_PRIMARY, array('id'));

            // Create table.
            $dbman->create_table($table);
        }


         upgrade_plugin_savepoint(true,2022041101.02, 'local', 'competency');
    }

     if ($oldversion < 2022041102.1) {
        $dbman = $DB->get_manager();
        $tableA= new xmldb_table('local_cmtncypc_completions');
        $tableB= new xmldb_table('local_cmtncy_completions');
        $field=  new xmldb_field('realuser', XMLDB_TYPE_INTEGER, '10', null, XMLDB_NOTNULL, null, '0','userid');
        
        if (!$dbman->field_exists($tableA, $field)) {
            $dbman->add_field($tableA, $field);
        }

        if (!$dbman->field_exists($tableB, $field)) {
            $dbman->add_field($tableB, $field);
        }

        upgrade_plugin_savepoint(true, 2022041102.1, 'local', 'competency');
    }

    return true;
}
