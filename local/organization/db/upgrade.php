<?php
defined('MOODLE_INTERNAL') || die();
function xmldb_local_organization_upgrade($oldversion) {
   global $DB, $CFG;
   if ($oldversion < 2021051700.010) {
     $dbman = $DB->get_manager();
     $table = new xmldb_table('organization_draft');
     $table->add_field('id', XMLDB_TYPE_INTEGER, '10', null, XMLDB_NOTNULL, XMLDB_SEQUENCE, null);
     $table->add_field('fullname', XMLDB_TYPE_CHAR, '255',  null, null, null, null, null, null);
     $table->add_field('fullnameinarabic', XMLDB_TYPE_CHAR, '255',  null, null, null, null, null, null);
     $table->add_field('shortname', XMLDB_TYPE_CHAR, '255',  null, null, null, null, null, null);
     $table->add_field('parentid', XMLDB_TYPE_INTEGER, '10',  null, null, null, null, null, null);
     $table->add_field('path', XMLDB_TYPE_CHAR, '255',  null, null, null, null, null, null);
     $table->add_field('depth', XMLDB_TYPE_INTEGER, '20',  null, null, null, null, null, null);
     $table->add_field('description', XMLDB_TYPE_TEXT, 'medium', null, null, null, null, null, null);
     $table->add_field('visible', XMLDB_TYPE_INTEGER, '2', null, XMLDB_NOTNULL, null, '1');
     $table->add_field('status', XMLDB_TYPE_INTEGER, '2', null, XMLDB_NOTNULL, null, '2');
     $table->add_field('orgsegment', XMLDB_TYPE_INTEGER, '10',  null, null, null, null, null, null);
     $table->add_field('orgsector', XMLDB_TYPE_INTEGER, '10',  null, null, null, null, null, null);
     $table->add_field('orgfieldofwork', XMLDB_TYPE_CHAR,'255',  null, null, null, null, null, null);
     $table->add_field('hrfullname', XMLDB_TYPE_CHAR, '255', null, XMLDB_NOTNULL,null,'0');
     $table->add_field('hrjobrole', XMLDB_TYPE_CHAR, '255', null, null, null, null, null, null);
     $table->add_field('hremail', XMLDB_TYPE_CHAR, '255', null, null, null, null, null, null);
     $table->add_field('hrmobile', XMLDB_TYPE_CHAR, '255', null, null, null, null, null, null);
     $table->add_field('alfullname', XMLDB_TYPE_CHAR, '255', null, XMLDB_NOTNULL,null,'0');
     $table->add_field('aljobrole', XMLDB_TYPE_CHAR, '255', null, null, null, null, null, null);
     $table->add_field('alemail', XMLDB_TYPE_CHAR, '255', null, null, null, null, null, null);
     $table->add_field('almobile', XMLDB_TYPE_CHAR, '255', null, null, null, null, null, null);
     $table->add_field('approval_letter', XMLDB_TYPE_INTEGER, '10', null, XMLDB_NOTNULL, null, '0');
     $table->add_field('licensekey', XMLDB_TYPE_CHAR, '255', null, null, null, null);
     $table->add_field('usercreated', XMLDB_TYPE_INTEGER, '10', null, XMLDB_NOTNULL, null, '0');
     $table->add_field('timecreated', XMLDB_TYPE_INTEGER, '10', null, XMLDB_NOTNULL, null, '0');
     $table->add_field('timemodified', XMLDB_TYPE_INTEGER, '10', null, XMLDB_NOTNULL, null, '0');
     $table->add_field('usermodified', XMLDB_TYPE_INTEGER, '10', null, XMLDB_NOTNULL, null, '0');
     $table->add_key('primary', XMLDB_KEY_PRIMARY, ['id']);
     if (!$dbman->table_exists($table)) {
        $dbman->create_table($table);
     }
     $table2 = new xmldb_table('local_organization');
     $field1 = new xmldb_field('status',XMLDB_TYPE_INTEGER, '2', null, XMLDB_NOTNULL, null, '2');
     if ($dbman->field_exists($table2, $field1)) {
        $dbman->change_field_default($table2, $field1);
     }
      upgrade_plugin_savepoint(true, 2021051700.010, 'local', 'organization');
   } 

   if ($oldversion < 2021051700.016) {
     $dbman = $DB->get_manager();
     $drafttable = new xmldb_table('organization_draft');
     $statusfield = new xmldb_field('status',XMLDB_TYPE_INTEGER, '2', null, XMLDB_NOTNULL, null, '2');
     if ($dbman->field_exists($drafttable, $statusfield)) {
        $dbman->change_field_default($drafttable, $statusfield);
     }
     $orgfieldofworkfield = new xmldb_field('orgfieldofwork', XMLDB_TYPE_CHAR, '255', null, null, null, null, null, null);
     $dbman->change_field_type($drafttable, $orgfieldofworkfield);

     upgrade_plugin_savepoint(true, 2021051700.016, 'local', 'organization');
   }
   if ($oldversion < 2021051700.021) {
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
      $initcontent = array('name' => 'Organizations','shortname' => 'organizations','parent_module' => '0','usercreated' => '2','timecreated' => $time,'usermodified' => 2,'timemodified' => NULL, 'pluginname' => 'organization','plugintype' => 'local');
      $parentid = $DB->get_field('local_notification_type', 'id', array('shortname' => 'organization'));
      if(!$parentid){
          $parentid = $DB->insert_record('local_notification_type', $initcontent);
      }
      $notification_type_data = array(
        array('name' => 'Organization Registration','shortname' => 'organization_registration','parent_module' => $parentid,'usercreated' => '2','timecreated' => $time,'usermodified' => 2,'timemodified' => NULL, 'pluginname' => 'organization','plugintype' => 'local'),
        array('name' => 'Assigning Official','shortname' => 'organization_assigning_official','parent_module' => $parentid,'usercreated' => '2','timecreated' => $time,'usermodified' => 2,'timemodified' => NULL, 'pluginname' => 'organization','plugintype' => 'local'),
        array('name' => 'Assigning Trainee','shortname' => 'organization_assigning_trainee','parent_module' => $parentid,'usercreated' => '2','timecreated' => $time,'usermodified' => 2,'timemodified' => NULL, 'pluginname' => 'organization','plugintype' => 'local'),
        array('name' => 'Enrollment','shortname' => 'organization_enrollment','parent_module' => $parentid,'usercreated' => '2','timecreated' => $time,'usermodified' => 2,'timemodified' => NULL, 'pluginname' => 'organization','plugintype' => 'local'),
       array('name' => 'Wallet Update','shortname' => 'organization_wallet_update','parent_module' => $parentid,'usercreated' => '2','timecreated' => $time,'usermodified' => 2,'timemodified' => NULL, 'pluginname' => 'organization','plugintype' => 'local'),
       
    );
      foreach($notification_type_data as $notification_type){
          unset($notification_type['timecreated']);
          if(!$DB->record_exists('local_notification_type',  $notification_type)){
              $notification_type['timecreated'] = $time;
              $DB->insert_record('local_notification_type', $notification_type);
          }
      }
      upgrade_plugin_savepoint(true, 2021051700.021, 'local', 'organization');
   }

  if ($oldversion < 2021051700.023) {
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

  upgrade_plugin_savepoint(true, 2021051700.023, 'local', 'organization');
}

if ($oldversion < 2021051700.026) {
  $dbman = $DB->get_manager(); // loads ddl manager and xmldb classes
  $orgtable = new xmldb_table('local_organization');
  $drafttable = new xmldb_table('organization_draft');
  $field = new xmldb_field('fullnameinarabic',XMLDB_TYPE_CHAR, '255', null, null, null,null,'fullname');
  if (!$dbman->field_exists($orgtable, $field)) {
      $dbman->add_field($orgtable, $field);
  }
  if (!$dbman->field_exists($drafttable, $field)) {
      $dbman->add_field($drafttable, $field);
  }
  upgrade_plugin_savepoint(true, 2021051700.026, 'local', 'organization');
}

if ($oldversion < 2021051700.029) {
    
  $dbman = $DB->get_manager();
  $table = new xmldb_table('local_organization');
  $oldidfield = new xmldb_field('oldid',XMLDB_TYPE_INTEGER, '12', null, null, null, null,'id');
   if (!$dbman->field_exists($table, $oldidfield)) {
      $dbman->add_field($table, $oldidfield);
  }  
  upgrade_plugin_savepoint(true, 2021051700.029, 'local', 'organization'); 
} 

if ($oldversion < 2021051700.033) {        
    $dbman = $DB->get_manager();
    $table = new xmldb_table('local_organization');
    $field = new xmldb_field('oldid', XMLDB_TYPE_CHAR, '255', null, null, null, null);
    if ( $dbman->field_exists($table, $field) ) {
        $dbman->change_field_type($table, $field);
    }

    // Main savepoint reached.
    upgrade_plugin_savepoint(true, 2021051700.033, 'local', 'organization');
}

if ($oldversion < 2021051700.13) {
    
  $dbman = $DB->get_manager();
  $table = new xmldb_table('local_organization');
  $field1 = new xmldb_field('logo', XMLDB_TYPE_CHAR, '255', null, null, null, null);
    if ( !$dbman->field_exists($table, $field1) ) {
        $dbman->add_field($table, $field1);
    }
    $field2 = new xmldb_field('partnertype', XMLDB_TYPE_CHAR, '255', null, null, null, null);
    if ( !$dbman->field_exists($table, $field2) ) {
        $dbman->add_field($table, $field2);
    }
    $field3 = new xmldb_field('partner', XMLDB_TYPE_CHAR, '255', null, null, null, null);
    if ( !$dbman->field_exists($table, $field3) ) {
        $dbman->add_field($table, $field3);
    }
  upgrade_plugin_savepoint(true, 2021051700.13, 'local', 'organization'); 
} 

// if ($oldversion < 2021051700.14) {
//      $dbman = $DB->get_manager();
//      $table = new xmldb_table('partnertypes');
//      $table->add_field('id', XMLDB_TYPE_INTEGER, '10', null, XMLDB_NOTNULL, XMLDB_SEQUENCE, null);
//      $table->add_field('name', XMLDB_TYPE_CHAR, '255',  null, null, null, null, null, null);
//      $table->add_field('description', XMLDB_TYPE_CHAR, '255',  null, null, null, null, null, null);
//      $table->add_field('timecreated', XMLDB_TYPE_INTEGER, '10', null, XMLDB_NOTNULL, null, '0');
//      $table->add_field('timemodified', XMLDB_TYPE_INTEGER, '10', null, XMLDB_NOTNULL, null, '0');
//      $table->add_key('primary', XMLDB_KEY_PRIMARY, ['id']);
//      if (!$dbman->table_exists($table)) {
//         $dbman->create_table($table);
//      }
//       upgrade_plugin_savepoint(true, 2021051700.14, 'local', 'organization');
//    }

  if ($oldversion < 2021051700.28) {
     $dbman = $DB->get_manager();
     $table = new xmldb_table('local_org_partnertypes');
     $table->add_field('id', XMLDB_TYPE_INTEGER, '10', null, XMLDB_NOTNULL, XMLDB_SEQUENCE, null);
     $table->add_field('name', XMLDB_TYPE_CHAR, '255',  null, null, null, null, null, null);
     $table->add_field('arabicname', XMLDB_TYPE_CHAR, '255',  null, null, null, null, null, null);
     $table->add_field('description', XMLDB_TYPE_TEXT, null, null, null, null, null);
     $table->add_field('usercreated', XMLDB_TYPE_INTEGER, '10', null, XMLDB_NOTNULL, null, '0');
     $table->add_field('timecreated', XMLDB_TYPE_INTEGER, '10', null, XMLDB_NOTNULL, null, '0');
     $table->add_field('timemodified', XMLDB_TYPE_INTEGER, '10', null, XMLDB_NOTNULL, null, '0');
     $table->add_key('primary', XMLDB_KEY_PRIMARY, ['id']);
     if (!$dbman->table_exists($table)) {
        $dbman->create_table($table);
     }

    $table = new xmldb_table('local_organization');
    $field = new xmldb_field('partner', XMLDB_TYPE_INTEGER, '10', null, XMLDB_NOTNULL, null, '0');
     if (!$dbman->field_exists($table, $field)) {
        $dbman->add_field($table, $field);
    }
    $field = new xmldb_field('partnertype', XMLDB_TYPE_INTEGER, '10', null, XMLDB_NOTNULL, null, '0');
     if (!$dbman->field_exists($table, $field)) {
        $dbman->add_field($table, $field);
    }
    $field = new xmldb_field('orgrank', XMLDB_TYPE_INTEGER, '10',  null, null, null, null, null, null);
     if (!$dbman->field_exists($table, $field)) {
        $dbman->add_field($table, $field);
    }
    upgrade_plugin_savepoint(true, 2021051700.28, 'local', 'organization');
  }



  if ($oldversion < 2021051700.3) {
    $dbman = $DB->get_manager();
    $table = new xmldb_table('local_organization');
    $field = new xmldb_field('orglogo', XMLDB_TYPE_INTEGER, '11', null, null, null, null);
     if (!$dbman->field_exists($table, $field)) {
        $dbman->add_field($table, $field);
    }
    upgrade_plugin_savepoint(true, 2021051700.3, 'local', 'organization');
  }

  if ($oldversion < 2021051700.4) {
    $dbman = $DB->get_manager();
    $tableA = new xmldb_table('local_organization');
    $tableB = new xmldb_table('organization_draft');
    $fieldA = new xmldb_field('orgsegment',XMLDB_TYPE_CHAR, '255',null, null, null, null, null, null);
    $fieldB = new xmldb_field('orgsector',XMLDB_TYPE_CHAR, '255',null, null, null, null, null, null);

    if ($dbman->field_exists($tableA, $fieldA)) {
        $dbman->change_field_type($tableA, $fieldA);
    }

    if ($dbman->field_exists($tableA, $fieldB)) {
        $dbman->change_field_type($tableA, $fieldB);
    }

    if ($dbman->field_exists($tableB, $fieldA)) {
        $dbman->change_field_type($tableB, $fieldA);
    }

    if ($dbman->field_exists($tableB, $fieldB)) {
        $dbman->change_field_type($tableB, $fieldB);
    }
  

    upgrade_plugin_savepoint(true, 2021051700.4, 'local', 'organization');
  }
  // ******************** DL-397 ***************************
  if ($oldversion < 2021051700.5) {
    $dbman = $DB->get_manager();
    $table = new xmldb_table('local_organization');
    $field = new xmldb_field('tax_number', XMLDB_TYPE_INTEGER, '20', null, null, null, null, 'discount_percentage');
     if (!$dbman->field_exists($table, $field)) {
        $dbman->add_field($table, $field);
    }
    $field = new xmldb_field('tax_certificate', XMLDB_TYPE_CHAR, '255', null, null, null, null, 'tax_number');
     if (!$dbman->field_exists($table, $field)) {
        $dbman->add_field($table, $field);
    }
    upgrade_plugin_savepoint(true, 2021051700.5, 'local', 'organization');
  }


  if ($oldversion < 2021051700.9) {
        $dbman = $DB->get_manager();
        $table = new xmldb_table('local_organization');
        $fieldA = new xmldb_field('autoapproval', XMLDB_TYPE_INTEGER, '5', null, XMLDB_NOTNULL, null, '1');
        $fieldB = new xmldb_field('orglogo',XMLDB_TYPE_INTEGER, '20', null, XMLDB_NOTNULL, null, '0');
        $fieldC = new xmldb_field('logo',XMLDB_TYPE_INTEGER, '20', null, XMLDB_NOTNULL, null, '0');


        if (!$dbman->field_exists($table, $fieldA)) {
            $dbman->add_field($table, $fieldA);
        }
        if (!$dbman->field_exists($table, $fieldB)) {
            $dbman->add_field($table, $fieldB);
        }
        if (!$dbman->field_exists($table, $fieldC)) {
            $dbman->add_field($table, $fieldC);
        }


        upgrade_plugin_savepoint(true, 2021051700.9, 'local', 'organization');
    }

    if ($oldversion < 2021051701) {
        $dbman = $DB->get_manager();
        $table = new xmldb_table('local_org_partnertypes');
        $field = new xmldb_field('partnerimage', XMLDB_TYPE_INTEGER, '10', NULL, null, null, null, null);
        if (!$dbman->field_exists($table, $field)) {
            $dbman->add_field($table, $field);
        }

        upgrade_plugin_savepoint(true, 2021051701, 'local', 'organization');
    }
    
  
  if ($oldversion < 2021051701.4) {
    $dbman = $DB->get_manager();
    $table = new xmldb_table('local_organization');
    $fieldA = new xmldb_field('otherfieldofwork', XMLDB_TYPE_CHAR, '255',null, null, null, null, null, null);

    if (!$dbman->field_exists($table, $fieldA)) {
        $dbman->add_field($table, $fieldA);
    }

    upgrade_plugin_savepoint(true,2021051701.4, 'local', 'organization');
}

if ($oldversion < 2021051701.8) {
    $dbman = $DB->get_manager(); // loads ddl manager and xmldb classes
    $table = new xmldb_table('local_organization');
    $fielda = new xmldb_field('discount_percentage',XMLDB_TYPE_INTEGER, '12', null, XMLDB_NOTNULL, null,0,'licensekey');
    if ($dbman->field_exists($table, $fielda)) {
        $dbman->drop_field($table, $fielda);
    }
    upgrade_plugin_savepoint(true, 2021051701.8, 'local', 'organization');
}
   
  return true;
}
