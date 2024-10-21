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

use tool_product\product as product;
use tool_product\telr as telr;
use tool_product_external;


function xmldb_tool_product_upgrade($oldversion) {
    global $DB, $CFG;
    $dbman = $DB->get_manager();

    if ($oldversion < 2022052000.07) {

        $table = new xmldb_table('tool_org_order_payments');
        $table->add_field('id', XMLDB_TYPE_INTEGER, '10', null, XMLDB_NOTNULL, XMLDB_SEQUENCE, null);
        $table->add_field('productid', XMLDB_TYPE_INTEGER, '10', null, XMLDB_NOTNULL, null, '0');
        $table->add_field('tablename', XMLDB_TYPE_CHAR, '255', null, XMLDB_NOTNULL, null, null);
        $table->add_field('fieldname', XMLDB_TYPE_CHAR, '255', null, XMLDB_NOTNULL, null, null);
        $table->add_field('fieldid', XMLDB_TYPE_INTEGER, '10', null, XMLDB_NOTNULL, null, '0');
        $table->add_field('orguserid', XMLDB_TYPE_INTEGER, '10', null, XMLDB_NOTNULL, null, '0');
        $table->add_field('paymenttype',XMLDB_TYPE_CHAR, '255', null, XMLDB_NOTNULL, null, null);
        $table->add_field('paymenton', XMLDB_TYPE_INTEGER, '10', null, XMLDB_NOTNULL, null, '0');
        $table->add_field('amount', XMLDB_TYPE_FLOAT, null, null, null, null,null);
        $table->add_field('purchasedseats', XMLDB_TYPE_INTEGER, '10', null, XMLDB_NOTNULL, null, '0');
        $table->add_field('paymentapprovalstatus', XMLDB_TYPE_INTEGER, '2', null, XMLDB_NOTNULL, null, '0');
        $table->add_field('approvaluserid', XMLDB_TYPE_INTEGER, '10', null, XMLDB_NOTNULL, null, '0');
        $table->add_field('approvalon', XMLDB_TYPE_INTEGER, '10', null, XMLDB_NOTNULL, null, '0');
        $table->add_field('amountrecived', XMLDB_TYPE_FLOAT, null, null, null, null,null);
        $table->add_field('transactionid', XMLDB_TYPE_CHAR, '255', null, XMLDB_NOTNULL, null, '0');
        $table->add_field('transactionref', XMLDB_TYPE_CHAR, '255', null, XMLDB_NOTNULL, null, '0');        
        $table->add_field('checkid', XMLDB_TYPE_INTEGER, '2', null, XMLDB_NOTNULL, null, '0');
        $table->add_field('transactionnote', XMLDB_TYPE_CHAR, '255', null, XMLDB_NOTNULL, null, null);
        $table->add_field('timecreated', XMLDB_TYPE_INTEGER, '10', null, XMLDB_NOTNULL, null, '0');
        $table->add_field('usercreated', XMLDB_TYPE_INTEGER, '10', null, XMLDB_NOTNULL, null, '0');
        $table->add_field('timemodified', XMLDB_TYPE_INTEGER, '10', null, null, null, '0');
        $table->add_field('usermodified', XMLDB_TYPE_INTEGER, '10', null, null, null, '0');
        $table->add_key('primary', XMLDB_KEY_PRIMARY, array('id'));

        if (!$dbman->table_exists($table)) {
            $dbman->create_table($table);
        }

        $table = new xmldb_table('tool_org_order_seats');
        $table->add_field('id', XMLDB_TYPE_INTEGER, '10', null, XMLDB_NOTNULL, XMLDB_SEQUENCE, null);
        $table->add_field('productid', XMLDB_TYPE_INTEGER, '10', null, XMLDB_NOTNULL, null, '0');
        $table->add_field('tablename', XMLDB_TYPE_CHAR, '255', null, XMLDB_NOTNULL, null, null);
        $table->add_field('fieldname', XMLDB_TYPE_CHAR, '255', null, XMLDB_NOTNULL, null, null);
        $table->add_field('fieldid', XMLDB_TYPE_INTEGER, '10', null, XMLDB_NOTNULL, null, '0');
        $table->add_field('orguserid', XMLDB_TYPE_INTEGER, '10', null, XMLDB_NOTNULL, null, '0');
        $table->add_field('purchasedseats', XMLDB_TYPE_INTEGER, '10', null, XMLDB_NOTNULL, null, '0');
        $table->add_field('approvalseats', XMLDB_TYPE_INTEGER, '10', null, null, null, '0');
        $table->add_field('availableseats', XMLDB_TYPE_INTEGER, '10', null, null, null, '0');
        $table->add_field('timecreated', XMLDB_TYPE_INTEGER, '10', null, XMLDB_NOTNULL, null, '0');
        $table->add_field('usercreated', XMLDB_TYPE_INTEGER, '10', null, XMLDB_NOTNULL, null, '0');
        $table->add_field('timemodified', XMLDB_TYPE_INTEGER, '10', null, null, null, '0');
        $table->add_field('usermodified', XMLDB_TYPE_INTEGER, '10', null, null, null, '0');
        $table->add_key('primary', XMLDB_KEY_PRIMARY, array('id'));

        if (!$dbman->table_exists($table)) {
            $dbman->create_table($table);
        }
        upgrade_plugin_savepoint(true, 2022052000.07, 'tool', 'product');
    }
     if ($oldversion < 2022052000.11) {

        $table = new xmldb_table('tool_order_approval_seats');
        $table->add_field('id', XMLDB_TYPE_INTEGER, '10', null, XMLDB_NOTNULL, XMLDB_SEQUENCE, null);
        $table->add_field('productid', XMLDB_TYPE_INTEGER, '10', null, XMLDB_NOTNULL, null, '0');
        $table->add_field('tablename', XMLDB_TYPE_CHAR, '255', null, XMLDB_NOTNULL, null, null);
        $table->add_field('fieldname', XMLDB_TYPE_CHAR, '255', null, XMLDB_NOTNULL, null, null);
        $table->add_field('fieldid', XMLDB_TYPE_INTEGER, '10', null, XMLDB_NOTNULL, null, '0');
        $table->add_field('orguserid', XMLDB_TYPE_INTEGER, '10', null, XMLDB_NOTNULL, null, '0');
        $table->add_field('purchasedseats', XMLDB_TYPE_INTEGER, '10', null, XMLDB_NOTNULL, null, '0');
        $table->add_field('approvalseats', XMLDB_TYPE_INTEGER, '10', null, null, null, '0');
        $table->add_field('timecreated', XMLDB_TYPE_INTEGER, '10', null, XMLDB_NOTNULL, null, '0');
        $table->add_field('usercreated', XMLDB_TYPE_INTEGER, '10', null, XMLDB_NOTNULL, null, '0');
        $table->add_field('timemodified', XMLDB_TYPE_INTEGER, '10', null, null, null, '0');
        $table->add_field('usermodified', XMLDB_TYPE_INTEGER, '10', null, null, null, '0');
        $table->add_key('primary', XMLDB_KEY_PRIMARY, array('id'));

        if (!$dbman->table_exists($table)) {
            $dbman->create_table($table);
        }

        $table = new xmldb_table('tool_org_order_seats');
        $field = new xmldb_field('approvalseats',XMLDB_TYPE_INTEGER, '10', null, null, null, '0');
        if (!$dbman->field_exists($table, $field)) {
            $dbman->add_field($table, $field);
        }

        upgrade_plugin_savepoint(true, 2022052000.11, 'tool', 'product');
    }


    if ($oldversion < 2022052000.15) {

        $table = new xmldb_table('tool_order_discount_usage');
        $table->add_field('id', XMLDB_TYPE_INTEGER, '10', null, XMLDB_NOTNULL, XMLDB_SEQUENCE, null);
        $table->add_field('productid', XMLDB_TYPE_INTEGER, '10', null, XMLDB_NOTNULL, null, '0');
        $table->add_field('userid', XMLDB_TYPE_INTEGER, '10', null, XMLDB_NOTNULL, null,null);
        $table->add_field('orgorderid', XMLDB_TYPE_INTEGER, '10', null, XMLDB_NOTNULL, null,null);
        $table->add_field('discounttype', XMLDB_TYPE_CHAR, '255', null, XMLDB_NOTNULL, null, null);
        $table->add_field('discountid', XMLDB_TYPE_INTEGER, '10', null, XMLDB_NOTNULL, null, '0');
        $table->add_field('discountuse', XMLDB_TYPE_INTEGER, '10', null, XMLDB_NOTNULL, null, null);
        $table->add_field('status', XMLDB_TYPE_INTEGER, '2', null, XMLDB_NOTNULL, null, '0');
        $table->add_field('timecreated', XMLDB_TYPE_INTEGER, '10', null, XMLDB_NOTNULL, null, '0');
        $table->add_field('usercreated', XMLDB_TYPE_INTEGER, '10', null, XMLDB_NOTNULL, null, '0');
        $table->add_field('timemodified', XMLDB_TYPE_INTEGER, '10', null, null, null, '0');
        $table->add_field('usermodified', XMLDB_TYPE_INTEGER, '10', null, null, null, '0');
        $table->add_key('primary', XMLDB_KEY_PRIMARY, array('id'));

        if (!$dbman->table_exists($table)) {
            $dbman->create_table($table);
        }

        upgrade_plugin_savepoint(true, 2022052000.15, 'tool', 'product');
    }
    if($oldversion < 2022052000.16){
        $time = time();
        $notification_info_data = array(
        array(
            'subject' => 'Payment completed',
            'body' => '<p dir="ltr" style="text-align: left;">Dear [payment_userfullname],</p><p dir="ltr" style="text-align: left;">&nbsp;We would like to inform you that your order No#[invoiceno] has been submitted successfully.Your payment details as follows: [payment_details]</p><p dir="ltr" style="text-align: left;">&nbsp;Thanks<br></p>',
            'arabic_subject' =>'تم الدفع',
            'arabic_body' => '<p dir="ltr" style="text-align: left;">عزيزي [payment_userfullname] ،</p><p dir="ltr" style="text-align: left;">&nbsp;نود إشعاركم  بإنة عملية الشراء تمت بنجاح. رقم الفاتورة  [invoiceno]،  وفي مايلي تفاصيل العملية: [payment_details].</p><p dir="ltr" style="text-align: left;">&nbsp;شكرًا<br></p>',
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
        upgrade_plugin_savepoint(true, 2022052000.16, 'tool', 'product');
    }

    if ($oldversion < 2022052000.17) {

    
        $dbman = $DB->get_manager();
        $table = new xmldb_table('tool_org_order_payments');
        $field = new xmldb_field('productid',XMLDB_TYPE_INTEGER, '10', null, null, null, '0');

        if (!$dbman->field_exists($table, $field)) {
            $dbman->add_field($table, $field);
        }

        $table = new xmldb_table('tool_order_discount_usage');
        $field = new xmldb_field('productid',XMLDB_TYPE_INTEGER, '10', null, null, null, '0');

        if (!$dbman->field_exists($table, $field)) {
            $dbman->add_field($table, $field);
        }

        $table = new xmldb_table('tool_org_order_seats');
        $field = new xmldb_field('productid',XMLDB_TYPE_INTEGER, '10', null, null, null, '0');

        if (!$dbman->field_exists($table, $field)) {
            $dbman->add_field($table, $field);
        }


        $table = new xmldb_table('tool_order_approval_seats');
        $field = new xmldb_field('productid',XMLDB_TYPE_INTEGER, '10', null, null, null, '0');

        if (!$dbman->field_exists($table, $field)) {
            $dbman->add_field($table, $field);
        }

        upgrade_plugin_savepoint(true, 2022052000.17, 'tool', 'product');
    }

    if ($oldversion < 2022052000.28) {
        $dbman = $DB->get_manager();
        $table = new xmldb_table('tool_org_order_payments');
        $field = new xmldb_field('originalprice',XMLDB_TYPE_FLOAT, null, null, null, null,null);
        if (!$dbman->field_exists($table, $field)) {
            $dbman->add_field($table, $field);
        }
        $field1 = new xmldb_field('discountprice',XMLDB_TYPE_FLOAT, null, null, null, null,null);
        if (!$dbman->field_exists($table, $field1)) {
            $dbman->add_field($table, $field1);
        }
        $field2 = new xmldb_field('taxes',XMLDB_TYPE_FLOAT, null, null, null, null,null);
        if (!$dbman->field_exists($table, $field2)) {
            $dbman->add_field($table, $field2);
        }
        upgrade_plugin_savepoint(true, 2022052000.28, 'tool', 'product');
    }

    if ($oldversion < 2022052000.32) {

        $table = new xmldb_table('tool_user_order_payments');
        $table->add_field('id', XMLDB_TYPE_INTEGER, '10', null, XMLDB_NOTNULL, XMLDB_SEQUENCE, null);
        $table->add_field('productid', XMLDB_TYPE_INTEGER, '10', null, XMLDB_NOTNULL, null, '0');
        $table->add_field('telrid', XMLDB_TYPE_INTEGER, '10', null, XMLDB_NOTNULL, null, '0');
        $table->add_field('tablename', XMLDB_TYPE_CHAR, '255', null, XMLDB_NOTNULL, null, null);
        $table->add_field('fieldname', XMLDB_TYPE_CHAR, '255', null, XMLDB_NOTNULL, null, null);
        $table->add_field('fieldid', XMLDB_TYPE_INTEGER, '10', null, XMLDB_NOTNULL, null, '0');
        $table->add_field('userid', XMLDB_TYPE_INTEGER, '10', null, XMLDB_NOTNULL, null, '0');
        $table->add_field('paymenttype',XMLDB_TYPE_CHAR, '255', null, XMLDB_NOTNULL, null, null);
        $table->add_field('paymenton', XMLDB_TYPE_INTEGER, '10', null, XMLDB_NOTNULL, null, '0');
        $table->add_field('originalprice', XMLDB_TYPE_INTEGER, '10', null, null, null, '0');
        $table->add_field('discountprice', XMLDB_TYPE_INTEGER, '10', null, null, null, '0');
        $table->add_field('taxes', XMLDB_TYPE_INTEGER, '10', null, null, null, '0');
        $table->add_field('amount', XMLDB_TYPE_INTEGER, '10', null, null, null, '0');
        $table->add_field('purchasedseats', XMLDB_TYPE_INTEGER, '10', null, XMLDB_NOTNULL, null, '0');
        $table->add_field('timecreated', XMLDB_TYPE_INTEGER, '10', null, XMLDB_NOTNULL, null, '0');
        $table->add_field('usercreated', XMLDB_TYPE_INTEGER, '10', null, XMLDB_NOTNULL, null, '0');
        $table->add_field('timemodified', XMLDB_TYPE_INTEGER, '10', null, null, null, '0');
        $table->add_field('usermodified', XMLDB_TYPE_INTEGER, '10', null, null, null, '0');
        $table->add_key('primary', XMLDB_KEY_PRIMARY, array('id'));

        if (!$dbman->table_exists($table)) {
            $dbman->create_table($table);
        }

        $count=$DB->count_records('tool_user_order_payments'); 

        if($count == 0){

            $stable = new \stdClass();
            $stable->thead = false;
            $stable->start = 0;
            $stable->length = -1;

            $getpaymentlist = (new \tool_product\product)::get_listof_mypayments($stable,$filterdata=array());

            $paymentslist = array_values($getpaymentlist['payments']);

            foreach($paymentslist as $list) {

                    $sql="select ra.id 
                            FROM {context} as cxt
                            JOIN {role_assignments} as ra on ra.contextid=cxt.id
                            JOIN {role} as r on r.id=ra.roleid
                            WHERE cxt.contextlevel=10 and r.shortname='organizationofficial' and ra.userid=$list->userid";

                    $organization=$DB->record_exists_sql($sql);

                    if($organization){

                        $organizationhead = true;

                    }else{

                        $organizationhead = false;
            
                        $product_data = unserialize(base64_decode($list->productdata));
                        $product_data['paymenttype']='telr';
                        $product_data['userid']=$list->userid;
                        $product_data['timeupdated']=$list->timeupdated;
                        tool_product_external::user_order_payments($product_data);
                }
              
            }
        }

        upgrade_plugin_savepoint(true, 2022052000.32, 'tool', 'product');
    }

    if ($oldversion < 2022052000.31) {


        $DB->delete_records('tool_user_order_payments');

        $count=$DB->count_records('tool_user_order_payments'); 

        if($count == 0){

            $stable = new \stdClass();
            $stable->thead = false;
            $stable->start = 0;
            $stable->length = -1;

            $getpaymentlist = (new \tool_product\product)::get_listof_mypayments($stable,$filterdata=array());

            $paymentslist = array_values($getpaymentlist['payments']);

            foreach($paymentslist as $list) {

                    $sql="select ra.id 
                            FROM {context} as cxt
                            JOIN {role_assignments} as ra on ra.contextid=cxt.id
                            JOIN {role} as r on r.id=ra.roleid
                            WHERE cxt.contextlevel=10 and r.shortname='organizationofficial' and ra.userid=$list->userid";

                    $organization=$DB->record_exists_sql($sql);

                    if($organization){

                        $organizationhead = true;

                    }else{

                        $organizationhead = false;
            
                        $product_data = unserialize(base64_decode($list->productdata));
                        $product_data['paymenttype']='telr';
                        $product_data['userid']=$list->userid;
                        $product_data['timeupdated']=$list->timeupdated;
                        tool_product_external::user_order_payments($product_data);
                }
              
            }
        }

        upgrade_plugin_savepoint(true, 2022052000.31, 'tool', 'product');
    }

    if ($oldversion < 2022052000.4) {
        $dbman = $DB->get_manager();
        $table = new xmldb_table('tool_org_order_payments');
        $field = new xmldb_field('payableamount',XMLDB_TYPE_FLOAT, null, null, null, null,null);;
        if (!$dbman->field_exists($table, $field)) {
            $dbman->add_field($table, $field);
        }
        upgrade_plugin_savepoint(true, 2022052000.4, 'tool', 'product');
    }

    if ($oldversion < 2022052000.5) {
        $dbman = $DB->get_manager();
        $table = new xmldb_table('tool_order_approval_seats');
        $field = new xmldb_field('paymentid',XMLDB_TYPE_INTEGER, '10', null, null, null, '0');
        if (!$dbman->field_exists($table, $field)) {
            $dbman->add_field($table, $field);
        }
        upgrade_plugin_savepoint(true, 2022052000.5, 'tool', 'product');
    }

      if ($oldversion < 2022052000.7) {
        $dbman = $DB->get_manager();
        $tableA = new xmldb_table('tool_product_telr');
        $tableB = new xmldb_table('tool_product_telr_pending');
        $field = new xmldb_field('orgpaymentid',XMLDB_TYPE_CHAR, '255', null, null, null, null, null, null);
        if (!$dbman->field_exists($tableA, $field)) {
            $dbman->add_field($tableA, $field);
        }
        if (!$dbman->field_exists($tableB, $field)) {
            $dbman->add_field($tableB, $field);
        }
        upgrade_plugin_savepoint(true, 2022052000.7, 'tool', 'product');
    }

    if ($oldversion < 2022052002) {
        $dbman = $DB->get_manager();
        $tableA = new xmldb_table('tool_user_order_payments');
        $tableB = new xmldb_table('tool_org_order_payments');

        $fieldA = new xmldb_field('originalprice',XMLDB_TYPE_FLOAT, null, null, null, null,null);
        $fieldB= new xmldb_field('discountprice',XMLDB_TYPE_FLOAT, null, null, null, null,null);
        $fieldC = new xmldb_field('taxes',XMLDB_TYPE_FLOAT, null, null, null, null,null);
        $fieldD= new xmldb_field('amount',XMLDB_TYPE_FLOAT, null, null, null, null,null);
        $fieldE = new xmldb_field('payableamount',XMLDB_TYPE_FLOAT, null, null, null, null,null);

        if ($dbman->field_exists($tableA, $fieldA)) {
            $dbman->change_field_type($tableA, $fieldA);
        }
        if ($dbman->field_exists($tableA, $fieldB)) {
            $dbman->change_field_type($tableA, $fieldB);
        }
        if ($dbman->field_exists($tableA, $fieldC)) {
            $dbman->change_field_type($tableA, $fieldC);
        }
        if ($dbman->field_exists($tableA, $fieldD)) {
            $dbman->change_field_type($tableA, $fieldD);
        }


        if ($dbman->field_exists($tableB, $fieldA)) {
            $dbman->change_field_type($tableB, $fieldA);
        }
        if ($dbman->field_exists($tableB, $fieldB)) {
            $dbman->change_field_type($tableB, $fieldB);
        }
        if ($dbman->field_exists($tableB, $fieldC)) {
            $dbman->change_field_type($tableB, $fieldC);
        }
        if ($dbman->field_exists($tableB, $fieldD)) {
            $dbman->change_field_type($tableB, $fieldD);
        }
        if ($dbman->field_exists($tableB, $fieldE)) {
            $dbman->change_field_type($tableB, $fieldE);
        }

        
        upgrade_plugin_savepoint(true, 2022052002, 'tool', 'product');
    }
    if ($oldversion < 2022052008) {
        $dbman = $DB->get_manager();
        $table = new xmldb_table('tool_product_telr');
        $table2 = new xmldb_table('tool_user_order_payments');
      
        $field = new xmldb_field('telrpid',XMLDB_TYPE_INTEGER, '10', null, null, null, null, null, null);
        $field2 = new xmldb_field('telrid',XMLDB_TYPE_INTEGER, '10', null, null, null, null, null, null);
        if (!$dbman->field_exists($table, $field)) {
            $dbman->add_field($table, $field);
        }
        if (!$dbman->field_exists($table2, $field2)) {
            $dbman->add_field($table2, $field2);
        }
        
        upgrade_plugin_savepoint(true, 2022052008, 'tool', 'product');
    }

    if ($oldversion < 2022052011) {
    
        $dbman = $DB->get_manager();
        $tableA = new xmldb_table('tool_product_refund');
        $tableA->add_field('id', XMLDB_TYPE_INTEGER, '10', null, XMLDB_NOTNULL, XMLDB_SEQUENCE, null);
        $tableA->add_field('transactionid', XMLDB_TYPE_INTEGER, '10', null, XMLDB_NOTNULL, null, '0');
        $tableA->add_field('userid', XMLDB_TYPE_INTEGER, '10', null, XMLDB_NOTNULL, null, '0');
        $tableA->add_field('amount',XMLDB_TYPE_FLOAT, null, null, null, null,null);
        $tableA->add_field('transactiondate', XMLDB_TYPE_INTEGER, '10', null, XMLDB_NOTNULL, null, '0');
        $tableA->add_field('usercreated', XMLDB_TYPE_INTEGER, '10', null, XMLDB_NOTNULL, null, '0');
        $tableA->add_field('timecreated', XMLDB_TYPE_INTEGER, '10', null, XMLDB_NOTNULL, null, '0');
        $tableA->add_field('usermodified', XMLDB_TYPE_INTEGER, '10', null, XMLDB_NOTNULL, null, '0');
        $tableA->add_field('timemodified', XMLDB_TYPE_INTEGER, '10', null, XMLDB_NOTNULL, null, '0');
        $tableA->add_key('primary', XMLDB_KEY_PRIMARY, array('id'));


        $tableB = new xmldb_table('tool_product_refund_logs');
        $tableB->add_field('id', XMLDB_TYPE_INTEGER, '10', null, XMLDB_NOTNULL, XMLDB_SEQUENCE, null);
        $tableB->add_field('refundid', XMLDB_TYPE_INTEGER, '10', null, XMLDB_NOTNULL, null, '0');
        $tableB->add_field('auth_status', XMLDB_TYPE_CHAR, '255', null, null, null, null);
        $tableB->add_field('auth_code', XMLDB_TYPE_CHAR, '255', null, null, null, null);
        $tableB->add_field('auth_message', XMLDB_TYPE_CHAR, null, null, null, null, null);
        $tableB->add_field('auth_tranref', XMLDB_TYPE_CHAR, '255', null, null, null, null);
        $tableB->add_field('auth_cvv', XMLDB_TYPE_CHAR, '255', null, null, null, null);
        $tableB->add_field('auth_avs', XMLDB_TYPE_CHAR, '255', null, null, null, null);
        $tableB->add_field('auth_trace', XMLDB_TYPE_CHAR, '255', null, null, null, null);
        $tableB->add_field('payment_code', XMLDB_TYPE_CHAR, '255', null, null, null, null);
        $tableB->add_field('payment_description', XMLDB_TYPE_CHAR, null, null, null, null, null);
        $tableB->add_field('payment_card_end', XMLDB_TYPE_CHAR, '255', null, null, null, null);
        $tableB->add_field('payment_card_bin', XMLDB_TYPE_CHAR, '255', null, null, null, null);
        $tableB->add_field('usercreated', XMLDB_TYPE_INTEGER, '10', null, XMLDB_NOTNULL, null, '0');
        $tableB->add_field('timecreated', XMLDB_TYPE_INTEGER, '10', null, XMLDB_NOTNULL, null, '0');
        $tableB->add_field('usermodified', XMLDB_TYPE_INTEGER, '10', null, XMLDB_NOTNULL, null, '0');
        $tableB->add_field('timemodified', XMLDB_TYPE_INTEGER, '10', null, XMLDB_NOTNULL, null, '0');
        $tableB->add_key('primary', XMLDB_KEY_PRIMARY, array('id'));

        if (!$dbman->table_exists($tableA)) {
            $dbman->create_table($tableA);
        }
        if (!$dbman->table_exists($tableB)) {
            $dbman->create_table($tableB);
        }

        upgrade_plugin_savepoint(true, 2022052011, 'tool', 'product');
    }

    if ($oldversion < 2022052012.1) {
    
        $dbman = $DB->get_manager();
       
        $table = new xmldb_table('tool_product_sadad_invoice');
        $table->add_field('id', XMLDB_TYPE_INTEGER, '12', null, XMLDB_NOTNULL, XMLDB_SEQUENCE, null);
        $table->add_field('productid', XMLDB_TYPE_INTEGER, '12', null, XMLDB_NOTNULL, null, '0');
        $table->add_field('telrid', XMLDB_TYPE_INTEGER, '12', null, XMLDB_NOTNULL, null, '0');
        $table->add_field('invoice_number', XMLDB_TYPE_INTEGER, '12', null, XMLDB_NOTNULL, null, '0');
        $table->add_field('seats', XMLDB_TYPE_INTEGER, '12', null, XMLDB_NOTNULL, null, '0');
        $table->add_field('payableamount', XMLDB_TYPE_FLOAT, null, null, null, null,null);
        $table->add_field('amount', XMLDB_TYPE_FLOAT, null, null, null, null,null);
        $table->add_field('status', XMLDB_TYPE_INTEGER, '2', null, XMLDB_NOTNULL, null, 0);
        $table->add_field('payment_status', XMLDB_TYPE_INTEGER, '2', null, XMLDB_NOTNULL, null, 0);
        $table->add_field('usercreated', XMLDB_TYPE_INTEGER, '10', null, XMLDB_NOTNULL, null, '0');
        $table->add_field('timecreated', XMLDB_TYPE_INTEGER, '10', null, XMLDB_NOTNULL, null, '0');
        $table->add_field('usermodified', XMLDB_TYPE_INTEGER, '10', null, XMLDB_NOTNULL, null, '0');
        $table->add_field('timemodified', XMLDB_TYPE_INTEGER, '10', null, XMLDB_NOTNULL, null, '0');
        $table->add_key('primary', XMLDB_KEY_PRIMARY, array('id'));

        if (!$dbman->table_exists($table)) {
            $dbman->create_table($table);
        }

        upgrade_plugin_savepoint(true, 2022052012.1, 'tool', 'product');
    }

    if ($oldversion < 2022052012.3) {
    
        $dbman = $DB->get_manager();
        $table = new xmldb_table('sadad_invoice_logs');
        $table->add_field('id', XMLDB_TYPE_INTEGER, '12', null, XMLDB_NOTNULL, XMLDB_SEQUENCE, null);
        $table->add_field('sadadid', XMLDB_TYPE_INTEGER, '12', null, XMLDB_NOTNULL, null, '0');
        $table->add_field('invoice_number', XMLDB_TYPE_INTEGER, '12', null, XMLDB_NOTNULL, null,'0');
        $table->add_field('cardtid',XMLDB_TYPE_CHAR,'255',null,null,null,null);
        $table->add_field('amount',XMLDB_TYPE_FLOAT, null, null, null, null,null);
        $table->add_field('is_enterprise',XMLDB_TYPE_INTEGER,'2', null,XMLDB_NOTNULL,null,0);
        $table->add_field('registration_no',XMLDB_TYPE_INTEGER,'12',null,XMLDB_NOTNULL,null,0);
        $table->add_field('company_name',XMLDB_TYPE_CHAR,'255',null,null,null,null);
        $table->add_field('commisioner_name',XMLDB_TYPE_CHAR,'255',null,null,null,null);
        $table->add_field('commisioner_id',XMLDB_TYPE_CHAR,'255',null,null,null,null);
        $table->add_field('commissioner_mobile_no',XMLDB_TYPE_CHAR,'255',null,null,null,null);
        $table->add_field('commissioner_email',XMLDB_TYPE_CHAR,'255',null,null,null,null);
        $table->add_field('payment_status',XMLDB_TYPE_INTEGER,'2',null,XMLDB_NOTNULL,null,0);
        $table->add_field('issue_date',XMLDB_TYPE_INTEGER,'10',null,XMLDB_NOTNULL,null,'0');
        $table->add_field('expiry_date',XMLDB_TYPE_INTEGER,'10',null,XMLDB_NOTNULL,null,'0');
        $table->add_field('usercreated',XMLDB_TYPE_INTEGER,'10',null,XMLDB_NOTNULL,null,'0');
        $table->add_field('timecreated',XMLDB_TYPE_INTEGER,'10',null,XMLDB_NOTNULL,null,'0');
        $table->add_field('usermodified',XMLDB_TYPE_INTEGER,'10',null,XMLDB_NOTNULL,null,'0');
        $table->add_field('timemodified',XMLDB_TYPE_INTEGER,'10',null,XMLDB_NOTNULL,null,'0');
        $table->add_key('primary', XMLDB_KEY_PRIMARY, array('id'));

        if (!$dbman->table_exists($table)) {
            $dbman->create_table($table);
        }
        upgrade_plugin_savepoint(true, 2022052012.3, 'tool', 'product');
    }

    if ($oldversion < 2022052012.5) {
        $dbman = $DB->get_manager();
        $tableA = new xmldb_table('tool_product_sadad_invoice');
        $tableB = new xmldb_table('sadad_invoice_logs');
      
        $fieldA = new xmldb_field('userid',XMLDB_TYPE_INTEGER, '10', null, XMLDB_NOTNULL, null, '0');
        $fieldB = new xmldb_field('is_enterprise',XMLDB_TYPE_CHAR,'255',null,null,null,null);

        
        if (!$dbman->field_exists($tableA, $fieldA)) {
            $dbman->add_field($tableA, $fieldA);
        }
        if ($dbman->field_exists($tableB, $fieldB)) {
            $dbman->change_field_type($tableB, $fieldB);
        }
      
        upgrade_plugin_savepoint(true, 2022052012.5, 'tool', 'product');
    }

    if ($oldversion < 2022052012.6) {
        $dbman = $DB->get_manager();
        $table = new xmldb_table('tool_product_sadad_invoice');
      
        $field = new xmldb_field('payableamount',XMLDB_TYPE_FLOAT, null, null, null, null,'seats');
        if (!$dbman->field_exists($table, $field)) {
            $dbman->add_field($table, $field);
        }

        upgrade_plugin_savepoint(true, 2022052012.6, 'tool', 'product');
    }

    if ($oldversion < 2022052012.8) {
        $dbman = $DB->get_manager();
        $table = new xmldb_table('tool_product_sadad_invoice');
      
        $field = new xmldb_field('type',XMLDB_TYPE_CHAR, 255, null, null, null,null,'seats');
        if (!$dbman->field_exists($table, $field)) {
            $dbman->add_field($table, $field);
        }

        upgrade_plugin_savepoint(true, 2022052012.8, 'tool', 'product');
    }

    if ($oldversion < 2022052012.9) {
        $dbman = $DB->get_manager();
        $table = new xmldb_table('tool_product_sadad_invoice');
      
        $field = new xmldb_field('invoice_number',XMLDB_TYPE_CHAR, 255, null, null, null,null);
        if ($dbman->field_exists($table, $field)) {
            $dbman->change_field_type($table, $field);
        }

        upgrade_plugin_savepoint(true, 2022052012.9, 'tool', 'product');
    }  

    if ($oldversion < 2022052013.3) {
        $dbman = $DB->get_manager();
        $table = new xmldb_table('tool_org_order_payments');
      
        $field = new xmldb_field('orderstatus', XMLDB_TYPE_INTEGER, '10', null, XMLDB_NOTNULL, null, '0');
        if (!$dbman->field_exists($table, $field)) {
            $dbman->add_field($table, $field);
        }
        upgrade_plugin_savepoint(true, 2022052013.3, 'tool', 'product');
    }
    if ($oldversion < 2022052013.5) {
        $table1 = new xmldb_table('tool_org_order_payments');
      
        $field1 = new xmldb_field('transactionref', XMLDB_TYPE_CHAR, '255', null, XMLDB_NOTNULL, null, '0');
        if (!$dbman->field_exists($table1, $field1)) {
            $dbman->add_field($table1, $field1);
        }
        upgrade_plugin_savepoint(true, 2022052013.5, 'tool', 'product');

    }

    if ($oldversion < 2022052013.6) {
        $dbman = $DB->get_manager();
        $table = new xmldb_table('tool_products');
        $field = new xmldb_field('price');
        $field->set_attributes(XMLDB_TYPE_FLOAT, null, null, null, null, null);
        try {
            $dbman->change_field_type($table, $field);
        } catch (moodle_exception $e) {}
        upgrade_plugin_savepoint(true, 2022052013.6, 'tool', 'product');
    }

    if ($oldversion < 2022052013.9) {
        $dbman = $DB->get_manager();
        $table1 = new xmldb_table('tool_order_approval_seats');
        $table2 = new xmldb_table('tool_org_order_payments');
        $table3 = new xmldb_table('tool_product_sadad_invoice');
        $table4 = new xmldb_table('tool_org_order_seats');
        $field = new xmldb_field('organization', XMLDB_TYPE_INTEGER, '10', null, null, null, '0');
        if (!$dbman->field_exists($table1, $field)) {
            $dbman->add_field($table1, $field);
        }
        if (!$dbman->field_exists($table2, $field)) {
            $dbman->add_field($table2, $field);
        }
        if (!$dbman->field_exists($table3, $field)) {
            $dbman->add_field($table3, $field);
        }
        if (!$dbman->field_exists($table4, $field)) {
            $dbman->add_field($table4, $field);
        }
        
        upgrade_plugin_savepoint(true, 2022052013.9, 'tool', 'product');
    }



    if ($oldversion < 2022052014.2) {
        $dbman = $DB->get_manager();
        $table = new xmldb_table('tool_product_refund');       
        $field = new xmldb_field('productid',XMLDB_TYPE_INTEGER, '10', null, XMLDB_NOTNULL, null, '0');
        
        if (!$dbman->field_exists($table, $field)) {
            $dbman->add_field($table, $field);
        }
        upgrade_plugin_savepoint(true, 2022052014.2, 'tool', 'product');
    }

     if ($oldversion < 2022052014.2) {
        $dbman = $DB->get_manager();
        $tableA= new xmldb_table('tool_orders');
        $tableB= new xmldb_table('tool_order_discount_usage');
        $tableC= new xmldb_table('tool_product_telr');
        $tableD= new xmldb_table('tool_product_telr_pending');
        $tableE= new xmldb_table('tool_user_order_payments');
        $tableF= new xmldb_table('tool_product_refund');
        $tableG= new xmldb_table('tool_product_sadad_invoice');
        $tableH= new xmldb_table('tool_org_order_payments');
        $tableI= new xmldb_table('tool_org_order_seats');
        $tableJ= new xmldb_table('tool_order_approval_seats');
        $field=  new xmldb_field('realuser', XMLDB_TYPE_INTEGER, '10', null, XMLDB_NOTNULL, null, '0','userid');
        $fieldO=  new xmldb_field('realuser', XMLDB_TYPE_INTEGER, '10', null, XMLDB_NOTNULL, null, '0','orguserid');
        
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

        if (!$dbman->field_exists($tableF, $field)) {
            $dbman->add_field($tableF, $field);
        }

        if (!$dbman->field_exists($tableG, $field)) {
            $dbman->add_field($tableG, $field);
        }

        if (!$dbman->field_exists($tableH, $fieldO)) {
            $dbman->add_field($tableH, $fieldO);
        }

        if (!$dbman->field_exists($tableI, $fieldO)) {
            $dbman->add_field($tableI, $fieldO);
        }

        if (!$dbman->field_exists($tableJ, $fieldO)) {
            $dbman->add_field($tableJ, $fieldO);
        }

        upgrade_plugin_savepoint(true, 2022052014.2, 'tool', 'product');
    }

    return true;
}
