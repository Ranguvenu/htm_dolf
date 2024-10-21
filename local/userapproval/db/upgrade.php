<?php
// This file is part of Moodle - http://moodle.org/
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
 * @package    local_competency
 * @copyright  2022 eAbyas Info Solutions<info@eabyas.com>
 * @author     Vinod Kumar  <vinod.p@eabyas.com>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
defined('MOODLE_INTERNAL') || die();
function xmldb_local_userapproval_upgrade($oldversion) {
    global $DB, $CFG;

   
   if ($oldversion < 2021051706.19) {
        $dbman = $DB->get_manager();
        $table = new xmldb_table('local_users');
        $fieldA= new xmldb_field('bannerimage',XMLDB_TYPE_INTEGER, '12', null, XMLDB_NOTNULL, null, '0');
        $fieldB= new xmldb_field('cirtificates',XMLDB_TYPE_INTEGER, '12', null, XMLDB_NOTNULL, null, '0');
        $fieldBA= new xmldb_field('certificates',XMLDB_TYPE_INTEGER, '12', null, XMLDB_NOTNULL, null, '0');
        $fieldC= new xmldb_field('linkedinprofile', XMLDB_TYPE_CHAR, '255', null, null, null, null, null, null);
        $fieldD = new xmldb_field('qualifications',XMLDB_TYPE_INTEGER, '12', null, XMLDB_NOTNULL, null, '0');
        $fieldE= new xmldb_field('yearsofexperience',XMLDB_TYPE_INTEGER, '12', null, XMLDB_NOTNULL, null, '0');
        $fieldF = new xmldb_field('fieldoftraining',XMLDB_TYPE_INTEGER, '12', null, XMLDB_NOTNULL, null, '0');
        $fieldG= new xmldb_field('fieldofexperience',XMLDB_TYPE_INTEGER, '12', null, XMLDB_NOTNULL, null, '0');
        $newnameB='certificates';
        if (!$dbman->field_exists($table, $fieldA)) {
            $dbman->add_field($table, $fieldA);
        }
        if ($dbman->field_exists($table, $fieldB)) {
            $dbman->rename_field($table, $fieldB, $newnameB);
        }
        if (!$dbman->field_exists($table, $fieldBA)) {
            $dbman->add_field($table, $fieldBA);
        }
        if (!$dbman->field_exists($table, $fieldC)) {
            $dbman->add_field($table, $fieldC);
        }

        if ($dbman->field_exists($table, $fieldD)) {  
            $dbman->drop_field($table, $fieldD);
        }
        if ($dbman->field_exists($table, $fieldE)) {  
            $dbman->drop_field($table, $fieldE);
        }
        
        if ($dbman->field_exists($table, $fieldF)) {  
            $dbman->drop_field($table, $fieldF);
        }
        if ($dbman->field_exists($table, $fieldG)) {  
            $dbman->drop_field($table, $fieldG);
        }
        upgrade_plugin_savepoint(true, 2021051706.19, 'local', 'userapproval');
    }

    if ($oldversion < 2021051706.26) {

        $dbman = $DB->get_manager();

        $orgrequesttable = new xmldb_table('organization_requests');
        $orgrequesttable->add_field('id', XMLDB_TYPE_INTEGER, '10', null, XMLDB_NOTNULL, XMLDB_SEQUENCE, null);
        $orgrequesttable->add_field('orgid', XMLDB_TYPE_INTEGER, '10', null, XMLDB_NOTNULL, null, null);
        $orgrequesttable->add_field('userid', XMLDB_TYPE_INTEGER, '5', null, XMLDB_NOTNULL, null, '0');
        $orgrequesttable->add_field('userstatus', XMLDB_TYPE_INTEGER, '5', null, XMLDB_NOTNULL, null, '1');
        $orgrequesttable->add_field('status', XMLDB_TYPE_INTEGER, '12', null, XMLDB_NOTNULL, null, '1');
        $orgrequesttable->add_field('timecreated', XMLDB_TYPE_INTEGER, '10', null, XMLDB_NOTNULL, null, '0');
        $orgrequesttable->add_field('usercreated', XMLDB_TYPE_INTEGER, '10', null, XMLDB_NOTNULL, null, '0');
        $orgrequesttable->add_key('primary', XMLDB_KEY_PRIMARY, ['id']);
        if (!$dbman->table_exists($orgrequesttable)) {
            $dbman->create_table($orgrequesttable);
        }
        $table = new xmldb_table('local_trainer_request');

        // Adding fields to table task_log.
        $table->add_field('id', XMLDB_TYPE_INTEGER, '10', null, XMLDB_NOTNULL, XMLDB_SEQUENCE, null);
        $table->add_field('userid', XMLDB_TYPE_INTEGER, '10', null, XMLDB_NOTNULL, null, null);
        $table->add_field('status', XMLDB_TYPE_INTEGER, '5', null, XMLDB_NOTNULL, null, '1');
        $table->add_field('qualifications', XMLDB_TYPE_INTEGER, '12', null, XMLDB_NOTNULL, null, '0');
        $table->add_field('certificates', XMLDB_TYPE_INTEGER, '10', null, XMLDB_NOTNULL, null, null);
        $table->add_field('yearsofexperience', XMLDB_TYPE_INTEGER, '10', null, XMLDB_NOTNULL, null, null);
       $table->add_field('fieldoftraining', XMLDB_TYPE_INTEGER, '10', null, XMLDB_NOTNULL, null, null);
       $fieldoftraining_others= new xmldb_field('fieldoftrainingothers', XMLDB_TYPE_CHAR, '25', null, null, null, null, null, null);

        // Adding keys to table task_log.
        $table->add_key('primary', XMLDB_KEY_PRIMARY, ['id']);
        if (!$dbman->table_exists($table)) {
            $dbman->create_table($table);
        }
        if (!$dbman->field_exists($table, $fieldoftraining_others)) {
            $dbman->add_field($table,$fieldoftraining_others);
        }
    
        $updatedfield1=new xmldb_field('status', XMLDB_TYPE_INTEGER, '5', null, XMLDB_NOTNULL, null, '1');

        $dbman->change_field_default($table, $updatedfield1);

        $table2 = new xmldb_table('local_expert_request');
        $table2->add_field('id', XMLDB_TYPE_INTEGER, '10', null, XMLDB_NOTNULL, XMLDB_SEQUENCE, null);
        $table2->add_field('userid', XMLDB_TYPE_INTEGER, '10', null, XMLDB_NOTNULL, null, null);
        $table2->add_field('userstatus', XMLDB_TYPE_INTEGER, '10', null, XMLDB_NOTNULL, null,'1');
        $table2->add_field('status', XMLDB_TYPE_INTEGER, '5', null, XMLDB_NOTNULL, null, '1');
        $table2->add_field('qualifications', XMLDB_TYPE_INTEGER, '12', null, XMLDB_NOTNULL, null, '0');
        $table2->add_field('certificates', XMLDB_TYPE_INTEGER, '10', null, XMLDB_NOTNULL, null, null);
        $table2->add_field('yearsofexperience', XMLDB_TYPE_INTEGER, '10', null, XMLDB_NOTNULL, null, null);
       $table2->add_field('fieldofexperience', XMLDB_TYPE_INTEGER, '12', null, XMLDB_NOTNULL, null, '0');      

       $fieldtraining = new xmldb_field('fieldoftraining',XMLDB_TYPE_INTEGER, '12', null, XMLDB_NOTNULL, null, '0');
       $fieldoftraining_others= new xmldb_field('fieldoftrainingothers', XMLDB_TYPE_CHAR, '25', null, null, null, null, null, null);

       $table2->add_key('primary', XMLDB_KEY_PRIMARY, ['id']);

        
        
        if (!$dbman->table_exists($table2)) {
            $dbman->create_table($table2);
        }
        if (!$dbman->field_exists($table2, $fieldtraining)) {
            $dbman->add_field($table2,$fieldtraining);
        }
        if (!$dbman->field_exists($table2, $fieldoftraining_others)) {
            $dbman->add_field($table2,$fieldoftraining_others);
        }

        $updatedfield2=new xmldb_field('status', XMLDB_TYPE_INTEGER, '5', null, XMLDB_NOTNULL, null, '1');
        $dbman->change_field_default($table2, $updatedfield2);
        
        

      // Main savepoint reached.
      upgrade_plugin_savepoint(true, 2021051706.26,'local', 'userapproval');
    }


    if ($oldversion < 2021051706.28) {

        $dbman = $DB->get_manager();

        $table = new xmldb_table('local_orgofficial_wallet');
        $table->add_field('id', XMLDB_TYPE_INTEGER, '10', null, XMLDB_NOTNULL, XMLDB_SEQUENCE, null);
        $table->add_field('userid', XMLDB_TYPE_INTEGER, '10', null, XMLDB_NOTNULL, null, '0');
        $table->add_field('wallet', XMLDB_TYPE_INTEGER, '10', null, XMLDB_NOTNULL, null, '0');
        $table->add_field('timecreated', XMLDB_TYPE_INTEGER, '10', null, XMLDB_NOTNULL, null, '0');
        $table->add_field('timemodified', XMLDB_TYPE_INTEGER, '10', null, null, null, '0');
        $table->add_key('primary', XMLDB_KEY_PRIMARY, array('id'));

        if (!$dbman->table_exists($table)) {
            $dbman->create_table($table);
        }

        $table = new xmldb_table('local_wallet_logs');
        $table->add_field('id', XMLDB_TYPE_INTEGER, '10', null, XMLDB_NOTNULL, XMLDB_SEQUENCE, null);
        $table->add_field('userid', XMLDB_TYPE_INTEGER, '10', null, XMLDB_NOTNULL, null, '0');
        $table->add_field('addedwallet', XMLDB_TYPE_INTEGER, '10', null, XMLDB_NOTNULL, null, '0');
        $table->add_field('timecreated', XMLDB_TYPE_INTEGER, '10', null, XMLDB_NOTNULL, null, '0');
        $table->add_key('primary', XMLDB_KEY_PRIMARY, array('id'));

        if (!$dbman->table_exists($table)) {
            $dbman->create_table($table);
        }

        upgrade_plugin_savepoint(true, 2021051706.28, 'local', 'userapproval');
    }
    if ($oldversion < 2021051706.29) {
        $dbman = $DB->get_manager();
        $table = new xmldb_table('organization_requests');
        $statusfield= new xmldb_field('userstatus', XMLDB_TYPE_INTEGER, '12', null, XMLDB_NOTNULL, null, '1','userid');
        if (!$dbman->field_exists($table,$statusfield)) {
            $dbman->add_field($table, $statusfield);
        }
        
        upgrade_plugin_savepoint(true, 2021051706.29, 'local', 'userapproval');


    }
if ($oldversion < 2021051706.35) {
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
    $initcontent = array('name' => 'User approval','shortname' => 'userapproval','parent_module' => '0','usercreated' => '2','timecreated' => $time,'usermodified' => 2,'timemodified' => NULL, 'pluginname' => 'userapproval','plugintype' => 'local');
    $parentid = $DB->get_field('local_notification_type', 'id', array('shortname' => 'userapproval'));
    if(!$parentid){
        $parentid = $DB->insert_record('local_notification_type', $initcontent);
    }
    $notification_type_data = array(
        array('name' => 'Registration','shortname' => 'registration','parent_module' => $parentid,'usercreated' => '2','timecreated' => $time,'usermodified' => 2,'timemodified' => NULL, 'pluginname' => 'userapproval','plugintype' => 'local'),
        array('name' => 'Approve','shortname' => 'approve','parent_module' => $parentid,'usercreated' => '2','timecreated' => $time,'usermodified' => 2,'timemodified' => NULL, 'pluginname' => 'userapproval','plugintype' => 'local'),
        array('name' => 'Reject','shortname' => 'reject','parent_module' => $parentid,'usercreated' => '2','timecreated' => $time,'usermodified' => 2,'timemodified' => NULL, 'pluginname' => 'userapproval','plugintype' => 'local'),
        array('name' => 'Organization Approval','shortname' => 'organizational_approval','parent_module' => $parentid,'usercreated' => '2','timecreated' => $time,'usermodified' => 2,'timemodified' => NULL, 'pluginname' => 'userapproval','plugintype' => 'local'),
       //array('name' => 'Update Hall','shortname' => 'update_trainingprogram','parent_module' => $parentid,'usercreated' => '2','timecreated' => $time,'usermodified' => 2,'timemodified' => NULL, 'pluginname' => 'trainingprogram','plugintype' => 'local'),

    );
    foreach($notification_type_data as $notification_type){
        unset($notification_type['timecreated']);
        if(!$DB->record_exists('local_notification_type',  $notification_type)){
            $notification_type['timecreated'] = $time;
            $DB->insert_record('local_notification_type', $notification_type);
        }
    }
    upgrade_plugin_savepoint(true, 2021051706.35, 'local', 'userapproval');
}

if ($oldversion <  2021051706.39) {
    $dbman = $DB->get_manager(); 
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
            $notification_info['usercreated'] = 2;
            $notification_info['timecreated'] = $time;
            $notification_info['notificationid']=$notification_typeinfo->id;

            $DB->insert_record('local_notification_info', $notification_info);

        }
    }
}
    upgrade_plugin_savepoint(true,  2021051706.39, 'local', 'userapproval');
}
   if ($oldversion < 2021051706.4) {
    
        $dbman = $DB->get_manager();
        $table = new xmldb_table('local_users');
        $firstnamearabic = new xmldb_field('firstnamearabic',XMLDB_TYPE_TEXT,null, null, null, null, null, null);
         if (!$dbman->field_exists($table, $firstnamearabic)) {
            $dbman->add_field($table, $firstnamearabic);
        }  
        $lastnamearabic = new xmldb_field('lastnamearabic',XMLDB_TYPE_TEXT,null, null, null, null, null, null);
         if (!$dbman->field_exists($table, $lastnamearabic)) {
            $dbman->add_field($table, $lastnamearabic);
        }     
         upgrade_plugin_savepoint(true, 2021051706.4, 'local', 'userapproval'); 
    } 

    if ($oldversion < 2021051706.7) {
    
        $dbman = $DB->get_manager();
        $table = new xmldb_table('local_users');
        $oldidfield = new xmldb_field('oldid',XMLDB_TYPE_INTEGER, '12', null, null, null, null,'id');
         if (!$dbman->field_exists($table, $oldidfield)) {
            $dbman->add_field($table, $oldidfield);
        }  
   
        upgrade_plugin_savepoint(true, 2021051706.7, 'local', 'userapproval'); 
    } 


    if ($oldversion < 2021051707) {        
        $dbman = $DB->get_manager();
        $table = new xmldb_table('local_users');
        $field = new xmldb_field('oldid', XMLDB_TYPE_CHAR, '255', null, null, null, null);
        if ( $dbman->field_exists($table, $field) ) {
            $dbman->change_field_type($table, $field);
        }

        // Main savepoint reached.
        upgrade_plugin_savepoint(true, 2021051707, 'local', 'userapproval');
    }


    if ($oldversion < 2021051708) {        
        $dbman = $DB->get_manager();
        $table = new xmldb_table('local_users');
        $fieldsector = new xmldb_field('sector', XMLDB_TYPE_INTEGER, '12', null, null, null, null);
        $fieldsegment = new xmldb_field('segment', XMLDB_TYPE_INTEGER, '12', null, null, null, null);
        $fieldjobfamily = new xmldb_field('jobfamily', XMLDB_TYPE_INTEGER, '12', null, null, null, null);
        $fieldjobrole = new xmldb_field('jobrole', XMLDB_TYPE_INTEGER, '12', null, null, null, null);
        if ($dbman->field_exists($table, $fieldsector) ) {
            $dbman->change_field_type($table, $fieldsector);
        }
        if ($dbman->field_exists($table, $fieldsegment) ) {
            $dbman->change_field_type($table, $fieldsegment);
        }
        if ($dbman->field_exists($table, $fieldjobfamily) ) {
            $dbman->change_field_type($table, $fieldjobfamily);
        }
        if ($dbman->field_exists($table, $fieldjobrole) ) {
            $dbman->change_field_type($table, $fieldjobrole);
        }
        // Main savepoint reached.
        upgrade_plugin_savepoint(true, 2021051708, 'local', 'users');
    }

    if ($oldversion < 2021051710) {        
        $dbman = $DB->get_manager();
        $table = new xmldb_table('local_users');
        $field = new xmldb_field('jobrole_level', XMLDB_TYPE_CHAR, '55', null, null, null, null);
        if ($dbman->field_exists($table, $field) ) {
            $dbman->change_field_type($table, $field);
        }

        $tablea = new xmldb_table('local_trainer_request');
        $fielda = new xmldb_field('fieldoftraining', XMLDB_TYPE_CHAR, '255', null, null, null, null);
        if ($dbman->field_exists($tablea, $fielda) ) {
            $dbman->change_field_type($tablea, $fielda);
        }

        $tableb = new xmldb_table('local_expert_request');
        $fieldb = new xmldb_field('fieldofexperience', XMLDB_TYPE_CHAR, '255', null, null, null, null);
        if ($dbman->field_exists($tableb, $fieldb) ) {
            $dbman->change_field_type($tableb, $fieldb);
        }

        // Main savepoint reached.
        upgrade_plugin_savepoint(true, 2021051710, 'local', 'userapproval');
    }

    if ($oldversion < 2021051711) {        
        $dbman = $DB->get_manager();
        $table = new xmldb_table('local_users');
        $field = new xmldb_field('city', XMLDB_TYPE_CHAR, '120', null, XMLDB_NOTNULL, null, '');
        if ( $dbman->field_exists($table, $field) ) {
            $dbman->change_field_type($table, $field);
        }

        // Main savepoint reached.
        upgrade_plugin_savepoint(true, 2021051711, 'local', 'userapproval');
    }

     if ($oldversion < 2021051711.4) {        
        $dbman = $DB->get_manager();
        $table = new xmldb_table('local_users');
        $firstnamefield = new xmldb_field('firstname', XMLDB_TYPE_CHAR, '100', null, XMLDB_NOTNULL, null, '');
        $lastnamefield = new xmldb_field('lastname', XMLDB_TYPE_CHAR, '100', null, XMLDB_NOTNULL, null, '');
        $emailfield = new xmldb_field('email', XMLDB_TYPE_CHAR, '100', null, XMLDB_NOTNULL, null, '');
        $phone1field = new xmldb_field('phone1', XMLDB_TYPE_CHAR, '20', null, XMLDB_NOTNULL, null, '');
        $id_numberfield = new xmldb_field('id_number', XMLDB_TYPE_CHAR, '255', null, XMLDB_NOTNULL, null, '');
        $langfield = new xmldb_field('lang', XMLDB_TYPE_CHAR, '30', null, XMLDB_NOTNULL, null, '');
        $countryfield = new xmldb_field('country', XMLDB_TYPE_CHAR, '2', null, XMLDB_NOTNULL, null, '');
        $usernamefield = new xmldb_field('username', XMLDB_TYPE_CHAR, '100', null, XMLDB_NOTNULL, null, '');
       
        if ( $dbman->field_exists($table, $firstnamefield) ) {
            $dbman->change_field_type($table, $firstnamefield);
        }

        if ( $dbman->field_exists($table, $lastnamefield) ) {
            $dbman->change_field_type($table, $lastnamefield);
        }
        if ( $dbman->field_exists($table, $emailfield) ) {
            $dbman->change_field_type($table, $emailfield);
        }
        if ( $dbman->field_exists($table, $phone1field) ) {
            $dbman->change_field_type($table, $phone1field);
        }

        if ( $dbman->field_exists($table, $id_numberfield) ) {
            $dbman->change_field_type($table, $id_numberfield);
        }

        if ( $dbman->field_exists($table, $id_numberfield) ) {
            $dbman->change_field_type($table, $id_numberfield);
        }

        if ( $dbman->field_exists($table, $langfield) ) {
            $dbman->change_field_type($table, $langfield);
        }

        if ( $dbman->field_exists($table, $countryfield) ) {
            $dbman->change_field_type($table, $countryfield);
        }

        if ( $dbman->field_exists($table, $usernamefield) ) {
            $dbman->change_field_type($table, $usernamefield);
        }


        // Main savepoint reached.
        upgrade_plugin_savepoint(true, 2021051711.4, 'local', 'userapproval');
    }


    if ($oldversion < 2021051711.7) {        
        $dbman = $DB->get_manager();
        $table = new xmldb_table('local_users');
        $field = new xmldb_field('usersource', XMLDB_TYPE_CHAR, '120', null, null, null, null);
        if (!$dbman->field_exists($table, $field) ) {
            $dbman->add_field($table, $field);
        }

        // Main savepoint reached.
        upgrade_plugin_savepoint(true, 2021051711.7, 'local', 'userapproval');
    }


    if($oldversion < 2021051712.3){

        $dbman = $DB->get_manager();
        $trainingentitiestable = new xmldb_table('local_trainingentities');
        $trainingentitiestable->add_field('id', XMLDB_TYPE_INTEGER, '10', null, XMLDB_NOTNULL, XMLDB_SEQUENCE, null);
        $trainingentitiestable->add_field('name', XMLDB_TYPE_CHAR, '255',  null, null, null, null, null, null);
        $trainingentitiestable->add_field('shortname', XMLDB_TYPE_CHAR, '55', null, XMLDB_NOTNULL, null, '0');
        $trainingentitiestable->add_field('description', XMLDB_TYPE_TEXT, 'medium', null, null, null, null, null, null);
        $trainingentitiestable->add_field('logo', XMLDB_TYPE_INTEGER, '10', null, XMLDB_NOTNULL, null, '0');
        $trainingentitiestable->add_field('usercreated', XMLDB_TYPE_INTEGER, '10', null, XMLDB_NOTNULL, null, '0');
        $trainingentitiestable->add_field('timecreated', XMLDB_TYPE_INTEGER, '10', null, XMLDB_NOTNULL, null, '0');
        $trainingentitiestable->add_field('timemodified', XMLDB_TYPE_INTEGER, '10', null, XMLDB_NOTNULL, null, '0');
         $trainingentitiestable->add_field('usermodified', XMLDB_TYPE_INTEGER, '10', null, XMLDB_NOTNULL, null, '0');
        $trainingentitiestable->add_key('primary', XMLDB_KEY_PRIMARY, array('id'));

        if ($dbman->table_exists($trainingentitiestable)){
            $dbman->drop_table($trainingentitiestable);
        }
        upgrade_plugin_savepoint(true, 2021051712.3, 'local', 'userapproval');
    }
    if ($oldversion < 2021051712.4) {
    
        $dbman = $DB->get_manager();
        $table = new xmldb_table('local_users');   
        $acidfield = new xmldb_field('addresscountryid',XMLDB_TYPE_INTEGER, '12', null, XMLDB_NOTNULL, null,'0');
        $ncidfield = new xmldb_field('nationalitycountryid',XMLDB_TYPE_INTEGER, '12', null, XMLDB_NOTNULL, null,'0');
         if (!$dbman->field_exists($table, $acidfield)) {
            $dbman->add_field($table, $acidfield);
        } 
        if (!$dbman->field_exists($table, $ncidfield)) {
            $dbman->add_field($table, $ncidfield);
        }

        upgrade_plugin_savepoint(true, 2021051712.4, 'local', 'userapproval'); 
    } 

    if ($oldversion < 2021051712.5) {
    
        $dbman = $DB->get_manager();
        $table = new xmldb_table('local_users');   
        $fielda = new xmldb_field('middlenamearabic',XMLDB_TYPE_CHAR, '255',  null, null, null, null, null, null);
        $fieldb = new xmldb_field('middlenameen',XMLDB_TYPE_CHAR, '255',  null, null, null, null, null, null);
        $fieldc = new xmldb_field('thirdnamearabic',XMLDB_TYPE_CHAR, '255',  null, null, null, null, null, null);
        $fieldd = new xmldb_field('thirdnameen',XMLDB_TYPE_CHAR, '255',  null, null, null, null, null, null);
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

        upgrade_plugin_savepoint(true, 2021051712.5, 'local', 'userapproval'); 
    } 

    if ($oldversion < 2021051712.6) {
    
        $dbman = $DB->get_manager();
        $table = new xmldb_table('local_users');   
        $fielda = new xmldb_field('password',XMLDB_TYPE_CHAR, '255', null, null, null, null,'username');
        $fieldb = new xmldb_field('confirm_password',XMLDB_TYPE_CHAR, '255', null, null, null, null,'password');
       
        if (!$dbman->field_exists($table, $fielda)) {
            $dbman->add_field($table, $fielda);
        } 
        if (!$dbman->field_exists($table, $fieldb)) {
            $dbman->add_field($table, $fieldb);
        }
        upgrade_plugin_savepoint(true, 2021051712.6, 'local', 'userapproval'); 
    }

    if ($oldversion < 2021051712.8) {
        $dbman = $DB->get_manager();
        $tableA = new xmldb_table('local_orgofficial_wallet');
        $tableB= new xmldb_table('local_wallet_logs');
        
        $fieldA = new xmldb_field('wallet',XMLDB_TYPE_FLOAT, null, null, null, null,0);
        $fieldB = new xmldb_field('addedwallet',XMLDB_TYPE_FLOAT, null, null, null, null,0);
       
        if ($dbman->field_exists($tableA, $fieldA)) {
            $dbman->change_field_type($tableA, $fieldA);
        }

        if ($dbman->field_exists($tableB, $fieldB)) {
            $dbman->change_field_type($tableB, $fieldB);
        }
        
        
        upgrade_plugin_savepoint(true, 2021051712.8, 'tool', 'userapproval');
    } 

      if ($oldversion < 2021051713) {
        $dbman = $DB->get_manager();
        $table = new xmldb_table('local_fast_user');
        $field = new xmldb_field('errormessage',XMLDB_TYPE_CHAR, '255', null, null, null, null);
        if (!$dbman->field_exists($table, $field)) {
            $dbman->add_field($table, $field);
        }
        
        upgrade_plugin_savepoint(true, 2021051713, 'local', 'userapproval');
    } 
    
    if ($oldversion < 2021051714.1) {
    
        $dbman = $DB->get_manager();
        $table = new xmldb_table('local_trainer_request');   
        $table2 = new xmldb_table('local_expert_request');   
        $fielda = new xmldb_field('requestdate', XMLDB_TYPE_INTEGER, '10', null, XMLDB_NOTNULL, null, '0');
        $fieldb = new xmldb_field('training_programs',XMLDB_TYPE_CHAR, '255',  null, null, null, null, null, null);
        
        if (!$dbman->field_exists($table, $fielda)) {
            $dbman->add_field($table, $fielda);
        } 
        if (!$dbman->field_exists($table, $fieldb)) {
            $dbman->add_field($table, $fieldb);
        }

        if (!$dbman->field_exists($table2, $fielda)) {
            $dbman->add_field($table2, $fielda);
        }

        upgrade_plugin_savepoint(true, 2021051714.1, 'local', 'userapproval'); 
    } 

    return true;
}
