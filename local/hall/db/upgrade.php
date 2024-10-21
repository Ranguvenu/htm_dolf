<?php
defined('MOODLE_INTERNAL') || die();
function xmldb_local_hall_upgrade($oldversion) {
    global $DB, $CFG;
   if ($oldversion < 2021051700.008) {
        $dbman = $DB->get_manager();
        $table = new xmldb_table('hall_slots');
        $table->add_field('id', XMLDB_TYPE_INTEGER, '10', null, XMLDB_NOTNULL, XMLDB_SEQUENCE, null);
        $table->add_field('examid', XMLDB_TYPE_INTEGER, '10', null, XMLDB_NOTNULL, null, '0');
        $table->add_field('hallid', XMLDB_TYPE_INTEGER, '10', null, XMLDB_NOTNULL, null, '0');
        $table->add_field('examdate', XMLDB_TYPE_INTEGER, '10', null, XMLDB_NOTNULL, null, '0');
        $table->add_field('slot', XMLDB_TYPE_INTEGER, '10', null, XMLDB_NOTNULL, null, '0');
        $table->add_field('userid', XMLDB_TYPE_INTEGER, '10', null, XMLDB_NOTNULL, null, '0');
        $table->add_field('duration', XMLDB_TYPE_INTEGER, '10', null, XMLDB_NOTNULL, null, '0');
        $table->add_field('timecreated', XMLDB_TYPE_INTEGER, '10', null, XMLDB_NOTNULL, null, '0');
        $table->add_field('timemodified', XMLDB_TYPE_INTEGER, '10', null, XMLDB_NOTNULL, null, '0');
        $table->add_key('primary', XMLDB_KEY_PRIMARY, ['id']);
        if (!$dbman->table_exists($table)) {
           $dbman->create_table($table);
        }
        upgrade_plugin_savepoint(true, 2021051700.008, 'local', 'hall');
    }
   if ($oldversion < 2021051700.009) {
        $dbman = $DB->get_manager();
        $table = new xmldb_table('hall');
        $field = new xmldb_field('hallendtime',XMLDB_TYPE_INTEGER, '10', null, XMLDB_NOTNULL, null, '0');
        if (!$dbman->field_exists($table, $field)) {
            $dbman->add_field($table, $field);
        }
        $field = new xmldb_field('hallstarttime',XMLDB_TYPE_INTEGER, '10', null, XMLDB_NOTNULL, null, '0');
        if (!$dbman->field_exists($table, $field)) {
            $dbman->add_field($table, $field);
        }        
        upgrade_plugin_savepoint(true, 2021051700.009, 'local', 'hall');
    }
   if ($oldversion < 2021051700.010) {
        $dbman = $DB->get_manager();
        $table = new xmldb_table('hall_reservations');
        $table->add_field('id', XMLDB_TYPE_INTEGER, '10', null, XMLDB_NOTNULL, XMLDB_SEQUENCE, null);
        $table->add_field('examid', XMLDB_TYPE_INTEGER, '10', null, XMLDB_NOTNULL, null, '0');
        $table->add_field('hallid', XMLDB_TYPE_INTEGER, '10', null, XMLDB_NOTNULL, null, '0');
        $table->add_field('examdate', XMLDB_TYPE_INTEGER, '10', null, XMLDB_NOTNULL, null, '0');
        $table->add_field('slotstart', XMLDB_TYPE_INTEGER, '10', null, XMLDB_NOTNULL, null, '0');
        $table->add_field('slotend', XMLDB_TYPE_INTEGER, '10', null, XMLDB_NOTNULL, null, '0');
        $table->add_field('userid', XMLDB_TYPE_INTEGER, '10', null, XMLDB_NOTNULL, null, '0');
        $table->add_key('primary', XMLDB_KEY_PRIMARY, ['id']);
        if (!$dbman->table_exists($table)) {
           $dbman->create_table($table);
        }
        upgrade_plugin_savepoint(true, 2021051700.010, 'local', 'hall');
    }
   if ($oldversion < 2021051700.011) {
        $dbman = $DB->get_manager();
        $table = new xmldb_table('hall_reservations');
        $field = new xmldb_field('seats',XMLDB_TYPE_INTEGER, '10', null, XMLDB_NOTNULL, null, '0');
        if (!$dbman->field_exists($table, $field)) {
            $dbman->add_field($table, $field);
        }
        upgrade_plugin_savepoint(true, 2021051700.011, 'local', 'hall');
    }
    if ($oldversion < 2021051700.012) {
        $dbman = $DB->get_manager();
        $table = new xmldb_table('hall_reservations');
        $field1 = new xmldb_field('examid',XMLDB_TYPE_INTEGER, '10', null, XMLDB_NOTNULL, null);
        $newname1='typeid';   
        if ($dbman->field_exists($table, $field1)) {
            $dbman->rename_field($table, $field1, $newname1);
        }
        $field = new xmldb_field('type', XMLDB_TYPE_CHAR, '25',  null, null, null, null, null, null);
        if (!$dbman->field_exists($table, $field)) {
            $dbman->add_field($table, $field);
        }
        upgrade_plugin_savepoint(true, 2021051700.012, 'local', 'hall');
    }
    if ($oldversion < 2022041203.22) {
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
    $time = time();
    $initcontent = array('name' => 'Hall','shortname' => 'hall','parent_module' => '0','usercreated' => '2','timecreated' => $time,'usermodified' => 2,'timemodified' => NULL, 'pluginname' => 'hall','plugintype' => 'local');
    $parentid = $DB->get_field('local_notification_type', 'id', array('shortname' => 'hall'));
    if(!$parentid){
        $parentid = $DB->insert_record('local_notification_type', $initcontent);
    }
    $notification_type_data = array(
        array('name' => 'Hall Reservation','shortname' => 'hall_reservation','parent_module' => $parentid,'usercreated' => '2','timecreated' => $time,'usermodified' => 2,'timemodified' => NULL, 'pluginname' => 'hall','plugintype' => 'local'),
       //array('name' => 'Update Hall','shortname' => 'update_trainingprogram','parent_module' => $parentid,'usercreated' => '2','timecreated' => $time,'usermodified' => 2,'timemodified' => NULL, 'pluginname' => 'trainingprogram','plugintype' => 'local'),

    );
    foreach($notification_type_data as $notification_type){
        unset($notification_type['timecreated']);
        if(!$DB->record_exists('local_notification_type',  $notification_type)){
            $notification_type['timecreated'] = $time;
            $DB->insert_record('local_notification_type', $notification_type);
        }
    }

        upgrade_plugin_savepoint(true, 2022041203.22, 'local', 'hall');

    }

    if ($oldversion < 2022041700.35) {
        $dbman = $DB->get_manager();
        $table = new xmldb_table('reservations_draft');
        $table->add_field('id', XMLDB_TYPE_INTEGER, '10', null, XMLDB_NOTNULL, XMLDB_SEQUENCE, null);
        $table->add_field('entityid', XMLDB_TYPE_INTEGER, '10', null, XMLDB_NOTNULL, null, '0');
        $table->add_field('entitycode', XMLDB_TYPE_CHAR, '25',  null, null, null, null, null, null);
        $table->add_field('hallid', XMLDB_TYPE_INTEGER, '10', null, XMLDB_NOTNULL, null, '0');
        $table->add_field('seats', XMLDB_TYPE_INTEGER, '10', null, XMLDB_NOTNULL, null, '0');
        $table->add_field('date', XMLDB_TYPE_INTEGER, '10', null, XMLDB_NOTNULL, null, '0');
        $table->add_field('slotstart', XMLDB_TYPE_INTEGER, '10', null, XMLDB_NOTNULL, null, '0');
        $table->add_field('slotend', XMLDB_TYPE_INTEGER, '10', null, XMLDB_NOTNULL, null, '0');
        $table->add_field('userid', XMLDB_TYPE_INTEGER, '10', null, XMLDB_NOTNULL, null, '0');
        $table->add_field('status', XMLDB_TYPE_INTEGER, '10', null, XMLDB_NOTNULL, null, '0');
        $table->add_field('timecreated', XMLDB_TYPE_INTEGER, '10', null, XMLDB_NOTNULL, null, '0');
        $table->add_field('timemodified', XMLDB_TYPE_INTEGER, '10', null, XMLDB_NOTNULL, null, '0');  
        $table->add_key('primary', XMLDB_KEY_PRIMARY, ['id']);
        if (!$dbman->table_exists($table)) {
           $dbman->create_table($table);
        }
        upgrade_plugin_savepoint(true, 2022041700.35, 'local', 'hall');
    }
    if ($oldversion < 2022041700.36) {
        $dbman = $DB->get_manager();
        $table = new xmldb_table('reservations_draft');
        $field = new xmldb_field('type',XMLDB_TYPE_CHAR, '25', null, XMLDB_NOTNULL, null, null);
        if (!$dbman->field_exists($table, $field)) {
            $dbman->add_field($table, $field);
        }
 
        $dbman = $DB->get_manager();
        $table = new xmldb_table('hall_reservations');
        $field = new xmldb_field('status',XMLDB_TYPE_INTEGER, '10', null, XMLDB_NOTNULL, null, '0');
        if (!$dbman->field_exists($table, $field)) {
            $dbman->add_field($table, $field);
        }
        upgrade_plugin_savepoint(true, 2022041700.36, 'local', 'hall');
    }

    if ($oldversion < 2022041700.39) {

        $dbman = $DB->get_manager();
        $table = new xmldb_table('hall_reservations');
        $field = new xmldb_field('examdate', XMLDB_TYPE_DATETIME, '6', null, XMLDB_NOTNULL, false, null);
        $dbman->change_field_type($table, $field);

        $dbman = $DB->get_manager();
        $table = new xmldb_table('reservations_draft');
        $field = new xmldb_field('date', XMLDB_TYPE_DATETIME, '6', null, XMLDB_NOTNULL, false, null);
        $dbman->change_field_type($table, $field);


        upgrade_plugin_savepoint(true, 2022041700.39, 'local', 'hall');

    }
        if ($oldversion < 2022041700.38) {
        $dbman = $DB->get_manager();
        $table = new xmldb_table('reservations_draft');
        $field = new xmldb_field('date', XMLDB_TYPE_DATETIME, '200', null, XMLDB_NOTNULL, false, null);
        $dbman->change_field_type($table, $field);

        // Main savepoint reached.
        upgrade_plugin_savepoint(true, 2022041700.38, 'local', 'hall');

    }
    if ($oldversion < 2022041700.40) {
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
    upgrade_plugin_savepoint(true, 2022041700.40, 'local', 'hall');

}
    if ($oldversion < 2022041700.42) {

        $data = $DB->record_exists('config_plugins', ['plugin' => 'local_hall', 'name' => 'hallcities']);
        if($data != 1) {
            $cities = (new local_hall\hall)->hall_cities();
            set_config('hallcities', serialize($cities), 'local_hall');
        }
        upgrade_plugin_savepoint(true, 2022041700.42, 'local', 'hall');
    }
    if ($oldversion < 2022041700.45) {

        $cities = (new local_hall\hall)->hall_cities();
        $citiesid = $DB->get_field('config_plugins', 'id', ['plugin' => 'local_hall', 'name' => 'hallcities']);
        $data = [];
        $data['id'] = $citiesid;
        $data['value'] = serialize($cities);
        $DB->update_record('config_plugins', $data);

        upgrade_plugin_savepoint(true, 2022041700.45, 'local', 'hall');
    }    
    if ($oldversion < 2022041700.46) {
        $dbman = $DB->get_manager();
        $table = new xmldb_table('hall');
        $field = new xmldb_field('halllocation',XMLDB_TYPE_CHAR, '25', null, XMLDB_NOTNULL, null, null);
        if (!$dbman->field_exists($table, $field)) {
            $dbman->add_field($table, $field);
        }
        upgrade_plugin_savepoint(true, 2022041700.46, 'local', 'hall');
    }
    if ($oldversion < 2022041700.47) {
        $dbman = $DB->get_manager();
        $table = new xmldb_table('hall');
        $field = new xmldb_field('code',XMLDB_TYPE_CHAR, '25', null, XMLDB_NOTNULL, null, null);
        if (!$dbman->field_exists($table, $field)) {
            $dbman->add_field($table, $field);
        }
        upgrade_plugin_savepoint(true, 2022041700.47, 'local', 'hall');
    }
    if ($oldversion < 2022041700.48) {
        $dbman = $DB->get_manager();
        $table = new xmldb_table('hall');
        $fieldone = new xmldb_field('phonenumber',XMLDB_TYPE_CHAR, '20', null, XMLDB_NOTNULL, null, '0');
        $fieldtwo = new xmldb_field('attachment',XMLDB_TYPE_INTEGER, '10', null, XMLDB_NOTNULL, null, '0');
        if (!$dbman->field_exists($table, $fieldone)) {
            $dbman->add_field($table, $fieldone);
        }
        if (!$dbman->field_exists($table, $fieldtwo)) {
            $dbman->add_field($table, $fieldtwo);
        }
        upgrade_plugin_savepoint(true, 2022041700.48, 'local', 'hall');
    }

    if ($oldversion < 2022041700.49) {
        $dbman = $DB->get_manager();
        $table = new xmldb_table('hall');
        $fieldone = new xmldb_field('phonenumber',XMLDB_TYPE_CHAR, '20', null, XMLDB_NOTNULL, null, '0');
        $fieldtwo = new xmldb_field('attachment',XMLDB_TYPE_INTEGER, '10', null, XMLDB_NOTNULL, null, '0');
        if ($dbman->field_exists($table, $fieldone)) {
            $dbman->drop_field($table, $fieldone);
        }
        if ($dbman->field_exists($table, $fieldtwo)) {
            $dbman->drop_field($table, $fieldtwo);
        }

        upgrade_plugin_savepoint(true, 2022041700.49, 'local', 'hall');
    }


    if ($oldversion <  2022041700.6) {
        $dbman = $DB->get_manager();
        $table = new xmldb_table('hallschedule');
        $table->add_field('id', XMLDB_TYPE_INTEGER, '10', null, XMLDB_NOTNULL, XMLDB_SEQUENCE, null);
        $table->add_field('hallid', XMLDB_TYPE_INTEGER, '10', null, XMLDB_NOTNULL, null, '0');
        $table->add_field('startdate', XMLDB_TYPE_INTEGER, '10', null, XMLDB_NOTNULL, null, '0');
        $table->add_field('starttime', XMLDB_TYPE_INTEGER, '10', null, XMLDB_NOTNULL, null, '0');
        $table->add_field('endtime', XMLDB_TYPE_INTEGER, '10', null, XMLDB_NOTNULL, null, '0');
        $table->add_field('days', XMLDB_TYPE_CHAR, '255', null, XMLDB_NOTNULL, null, '0');
       
        $table->add_field('timecreated', XMLDB_TYPE_INTEGER, '10', null, XMLDB_NOTNULL, null, '0');
        $table->add_field('timemodified', XMLDB_TYPE_INTEGER, '10', null, XMLDB_NOTNULL, null, '0');
        $table->add_key('primary', XMLDB_KEY_PRIMARY, ['id']);
        if (!$dbman->table_exists($table)) {
           $dbman->create_table($table);
        }
        upgrade_plugin_savepoint(true,  2022041700.6, 'local', 'hall');
    }
    if ($oldversion <  2022041700.64) {
        $dbman = $DB->get_manager();
        $table = new xmldb_table('local_schedule_logs');
        $table->add_field('id', XMLDB_TYPE_INTEGER, '10', null, XMLDB_NOTNULL, XMLDB_SEQUENCE, null);
        $table->add_field('productid', XMLDB_TYPE_INTEGER, '10', null, XMLDB_NOTNULL, null, '0');
        $table->add_field('entitytype', XMLDB_TYPE_CHAR, '255', null, null, null, null);
        $table->add_field('userid', XMLDB_TYPE_INTEGER, '10', null, null, null, null);
        $table->add_field('newhallscheduleid', XMLDB_TYPE_INTEGER, '12', null, XMLDB_NOTNULL, null, '0');      
        $table->add_field('oldhallscheduleid', XMLDB_TYPE_INTEGER, '12', null, XMLDB_NOTNULL, null, '0');      
        $table->add_field('oldscheduledate', XMLDB_TYPE_INTEGER, '10', null, XMLDB_NOTNULL, null, '0');
        $table->add_field('newscheduledate', XMLDB_TYPE_INTEGER, '10', null, XMLDB_NOTNULL, null, '0'); 
        $table->add_field('usercreated', XMLDB_TYPE_INTEGER, '10', null, XMLDB_NOTNULL, null, '0');      
        $table->add_field('timecreated', XMLDB_TYPE_INTEGER, '10', null, XMLDB_NOTNULL, null, '0');
        $table->add_field('timemodified', XMLDB_TYPE_INTEGER, '10', null, XMLDB_NOTNULL, null, '0');
        $table->add_key('primary', XMLDB_KEY_PRIMARY, ['id']);
        if (!$dbman->table_exists($table)) {
           $dbman->create_table($table);
        }
        upgrade_plugin_savepoint(true,  2022041700.64, 'local', 'hall');
    }

    if ($oldversion <  2022041700.67) {
        $dbman = $DB->get_manager();
        $table = new xmldb_table('local_schedule_logs');
        $fieldone = new xmldb_field('newhallscheduleid',XMLDB_TYPE_INTEGER,'12',null, XMLDB_NOTNULL, null,0,'userid');
        $fieldtwo = new xmldb_field('oldhallscheduleid',XMLDB_TYPE_INTEGER,'12',null, XMLDB_NOTNULL, null,0,'newhallscheduleid');
        if (!$dbman->field_exists($table, $fieldone)) {
            $dbman->add_field($table, $fieldone);
        }
        if (!$dbman->field_exists($table, $fieldtwo)) {
            $dbman->add_field($table, $fieldtwo);
        }

        upgrade_plugin_savepoint(true,2022041700.67, 'local', 'hall');
    }
     
    if ($oldversion <  2022041700.68) {
        $dbman = $DB->get_manager();
        $table = new xmldb_table('hall');
        $fieldone = new xmldb_field('hallcodes', XMLDB_TYPE_TEXT, null, null, null, null, null);
        if (!$dbman->field_exists($table, $fieldone)) {
            $dbman->add_field($table, $fieldone);
        }
        upgrade_plugin_savepoint(true,2022041700.68, 'local', 'hall');
    }

    if ($oldversion <  2022041700.7) {
        $dbman = $DB->get_manager();
        $table = new xmldb_table('hallschedule');
        $field = new xmldb_field('entity', XMLDB_TYPE_CHAR, '255', null, null, null, null);
        if (!$dbman->field_exists($table, $field)) {
            $dbman->add_field($table, $field);
        }

        $field1 = new xmldb_field('entityid', XMLDB_TYPE_INTEGER, '10', null, XMLDB_NOTNULL, null, '0');
        if (!$dbman->field_exists($table, $field1)) {
            $dbman->add_field($table, $field1);
        }

        upgrade_plugin_savepoint(true,2022041700.7, 'local', 'hall');
    }

    if ($oldversion < 2022041700.9) {
        $dbman = $DB->get_manager();
        $table = new xmldb_table('hallschedule');
        $field = new xmldb_field('seatingcapacity',XMLDB_TYPE_INTEGER, '10', null, XMLDB_NOTNULL, null,'0');
      
        if (!$dbman->field_exists($table, $field)) {
            $dbman->add_field($table, $field);
        }

        $field1 = new xmldb_field('directedto',XMLDB_TYPE_INTEGER, '5', null, XMLDB_NOTNULL, null,'0');
      
        if (!$dbman->field_exists($table, $field1)) {
            $dbman->add_field($table, $field1);
        }
    
        $field2 = new xmldb_field('status',XMLDB_TYPE_INTEGER, '5', null, XMLDB_NOTNULL, null,'0');
      
        if (!$dbman->field_exists($table, $field2)) {
            $dbman->add_field($table, $field2);
        }

        upgrade_plugin_savepoint(true, 2022041700.9, 'local', 'hall');
    }
    if ($oldversion < 2022041701.1) {
        $dbman = $DB->get_manager();
        $table = new xmldb_table('local_schedule_logs');
        $field=  new xmldb_field('realuser', XMLDB_TYPE_INTEGER, '10', null, XMLDB_NOTNULL, null, '0','userid');
      
        if (!$dbman->field_exists($table, $field)) {
            $dbman->add_field($table, $field);
        }
        upgrade_plugin_savepoint(true, 2022041701.1, 'local', 'hall');
    }

    return true;
}
