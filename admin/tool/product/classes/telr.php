<?php
namespace tool_product;

require_once($CFG->libdir.'/filelib.php');

use tool_product\product as product;
use stdClass;
use curl;
use moodle_url;
use moodle_exception;
use context_system;
use Exception;


    /**
     * 
 * @package tool_product
     */
    class telr
    {
        private static $domain = 'secure.telr.com';
        
        function __construct()
        {
            
        }

        public function get_order_summary($pd) {
            // Now we check the transaction from Telr's side
            $productdata = unserialize(base64_decode($pd->productdata));
            $c = new curl();
            $telrdomain = self::$domain;
            $options = array(
                'httpheader' => array('application/x-www-form-urlencoded', "Host: ".self::$domain.""),
                'timeout' => 30,
                'CURLOPT_HTTP_VERSION' => CURL_HTTP_VERSION_1_1,
            );
            $location = "https://".self::$domain."/gateway/order.json";
            $telrreq = array(
                'ivp_method'    => 'check',
                'ivp_store'     =>  get_config('tool_product', 'storeid'),
                'ivp_authkey'   => get_config('tool_product', 'authkey'),
                'order_ref'     => $pd->orderref
                
            );
            $result = $c->post($location, $telrreq, $options);

            if ($c->get_errno()) {
                throw new moodle_exception('errtelr', 'tool_product_telr', '', null, $result);
            }

            $jResult = json_decode($result);
            if(isset($jResult->error)) {
                throw new moodle_exception('errtelr', 'tool_product_telr', '', null, $result);
            }
            return $jResult;
        } 

        public function create_order($productdata, $orderid) {
            global $CFG, $USER, $DB;
            
            if(!isset($productdata['userid'])){
                $userid = $USER->id;
            }else{
                $userid = $productdata['userid'];
            }

            $userinfo = $DB->get_record('user', ['id' => $userid]);
            
            $systemcontext = context_system::instance();
            /// Open a connection to Telr to get the URL
            $c = new curl();
            $options = array(
                'httpheader' => array('application/x-www-form-urlencoded'),
                // 'timeout' => 30,
                // 'CURLOPT_HTTP_VERSION' => CURL_HTTP_VERSION_1_1,
            );
            $location = "https://".self::$domain."/gateway/order.json";
            /**
             * Random cart ID generating to make sure it works in all environment with unique ID
             */

            $randomcartnumber =  random_int(10000000, 999999999);
            $telrreq = array(
                'ivp_store'     => get_config('tool_product', 'storeid'),
                'ivp_authkey'   => get_config('tool_product', 'authkey'),
                'ivp_method'    => 'create',
                'ivp_test'      => get_config('tool_product', 'testmode'),
                'ivp_amount'    => format_float($productdata['total'],2),
                'ivp_cart'      => $randomcartnumber,
                'ivp_desc'      => $productdata['productinfo'],
                'return_auth'   => "$CFG->wwwroot/admin/tool/product/confirm.php?id=$orderid",
                'return_decl'   => "$CFG->wwwroot/admin/tool/product/confirm.php?id=$orderid",
                'return_can'    => "$CFG->wwwroot/admin/tool/product/confirm.php?id=$orderid",
                'ivp_framed'    => 0,
                'ivp_lang'      => 'ar',
                'ivp_currency'  => 'SAR',
                
                'bill_fname'    => $userinfo->firstname,
                'bill_sname'    => $userinfo->lastname,
                'bill_addr1'    => $userinfo->city,
                'bill_phone'    => $userinfo->phone1,
                'bill_city'     => $userinfo->city,
                'bill_country'  => $userinfo->country,
                'bill_email'    => $userinfo->email,
            );
            
//             if($productdata['paymenttype'] == 'postpaid'){

                
//                 if(!has_capability('local/organization:manage_organizationofficial', $systemcontext, $userinfo)){
//                     return false;
//                 }
//                 $organizationinfo = $DB->get_record_sql('SELECT o.licensekey as reg, o.fullname as compname, u.id_number as natid, 
//                                                                 concat(u.firstname, "", u.lastname) as name, u.phone1 as mob, u.email as email 
//                                                            FROM {local_users} as u 
//                                                            JOIN {local_organization} as o ON u.organization = o.id 
//                                                           WHERE u.userid=:userid', ['userid' => $userinfo->id]);
// // $organizationinfo->name
//                 $postpaid = array(
//                     'ivp_store'     => get_config('tool_product', 'sadad_storeid'),
//                     'ivp_authkey'   => get_config('tool_product', 'sadad_authkey'),
//                     'sadad_bill_type' => 1,
//                     'sadad_comp_reg' => $organizationinfo->reg,
//                     'sadad_comp_name' => $organizationinfo->compname,
//                     'sadad_comm_id' => $organizationinfo->natid,
//                     'sadad_comm_name' => '',
//                     'sadad_ret_url' => "$CFG->wwwroot/admin/tool/product/confirm.php?id=$orderid",
//                     'sadad_comm_mob' => '0'.$organizationinfo->mob,
//                     'sadad_comm_email' => $organizationinfo->email 
//                 );
//                 $telrreq = array_merge($telrreq, $postpaid);
//             }else{
                // $cardpaid = array(
                //         'ivp_store'     => get_config('tool_product', 'storeid'),
                //         'ivp_authkey'   => get_config('tool_product', 'authkey')
                //         );
                // $telrreq = array_merge($telrreq, $cardpaid);
            // }
          
            $result = $c->post($location, $telrreq, $options);

            // if ($c->get_errno()) {
            //     $this->exception("Could not connect to telr", $result);
            // }

            $jsonResult = json_decode($result);
          
            if(isset($jsonResult->error->note)) {
                throw new Exception($jsonResult->error->note);
            }
            return $jsonResult;
        }

        private function exception($errorcode, $data) {
            $site = get_site();
            $message = "$site->fullname:  Transaction failed.\n\n$errorcode\n\n";

            foreach ($data as $key => $value) {
                $message .= "$key => $value\n";
            }
            throw new Exception($message, $errorcode);
            
        }

        public function update_order_summary($pd) {
            global $DB;
            // Now we check the transaction from Telr's side
            $productdata = unserialize(base64_decode($pd->productdata));
            if(!isset($productdata['paymenttype'])){
                $productdata['paymenttype'] = 'prepaid';
            }
            $c = new curl();
            $telrdomain = self::$domain;
            $options = array(
                'httpheader' => array('application/x-www-form-urlencoded', "Host: ".self::$domain.""),
                'timeout' => 30,
                'CURLOPT_HTTP_VERSION' => CURL_HTTP_VERSION_1_1,
            );
            $location = "https://".self::$domain."/gateway/order.json";
            $telrreq = array(
                'ivp_method'    => 'check',
                'ivp_store'     => $productdata['paymenttype'] == 'postpaid' ? get_config('tool_product', 'sadad_storeid') : get_config('tool_product', 'storeid'),
                'ivp_authkey'   => $productdata['paymenttype'] == 'postpaid' ? get_config('tool_product', 'sadad_authkey') : get_config('tool_product', 'authkey'),
                'order_ref'     => $pd->orderref
                
            );
            $result = $c->post($location, $telrreq, $options);

            if ($c->get_errno()) {
                throw new moodle_exception('errtelr', 'tool_product_telr', '', null, $result);
            }

            $jResult = json_decode($result);
            if(isset($jResult->error)) {
                throw new moodle_exception('errtelr', 'tool_product_telr', '', null, $result);
            }
            if($jResult->order->status->code != 3){
                return false;
            }
            $pd->lasttimechecked = time();
            $pd->lastorderstatuscode = $jResult->order->status->code;
            $pd->lastorderstatus = $jResult->order->status->text;
          
            $pd->statuscode = $jResult->order->status->code;
            $pd->statustext = $jResult->order->status->text;

            if(!empty($jResult->order->transaction)) {
                $pd->transactionref = $jResult->order->transaction->ref;
                $pd->transactiontype = $jResult->order->transaction->type;
                $pd->transactionstatus = $jResult->order->transaction->status;
                $pd->transactioncode = $jResult->order->transaction->code;
                $pd->transactionmessage = $jResult->order->transaction->message;
            }
            try{
                $DB->update_record('tool_product_telr', $pd);
                if($pd->orgpaymentid){
                    $orgpayment = $DB->get_record('org_order_payments', ['id' => $pd->orgpaymentid]);
                    $orgpayment->amountrecived = 1;
                    $DB->update_record('org_order_payments', $orgpayment);
                }
            }catch(moodle_exception $e){
                print_r($e->errorcode);
            }
            return true;
        }


        public function generate_sadad_bill($productdata){
            global $DB, $USER;
            
            $productdata = unserialize(base64_decode(($productdata)));
        
            if(!isset($productdata['userid'])){
                $userid = $USER->id;
            }else{
                $userid = $productdata['userid'];
            }

            $userinfo = $DB->get_record('user', ['id' => $userid]);
          
            $systemcontext = context_system::instance();
            $c = new curl();

            // if(!has_capability('local/organization:manage_organizationofficial', $systemcontext, $userinfo)){
            //     return false;
            // }
            $organizationinfo = $DB->get_record_sql('SELECT o.licensekey as reg, o.fullname as compname, u.id_number as natid, 
                                                            concat(u.firstname, "", u.lastname) as name, u.phone1 as mob, u.email as email 
                                                       FROM {local_users} as u 
                                                       JOIN {local_organization} as o ON u.organization = o.id 
                                                      WHERE u.userid=:userid', ['userid' => $userinfo->id]);
            $options = array(
                'httpheader' => array('Content-Type: application/json'),
                'timeout' => 30,
                'CURLOPT_HTTP_VERSION' => CURL_HTTP_VERSION_1_1,
            );
            $randomcartnumber =  random_int(10000000, 999999999);
            $location = "https://".self::$domain."/gateway/remote_sb.json";
            $telrreq = array(
                'StoreID'     => get_config('tool_product', 'sadad_storeid'),
                'AuthKey'   => get_config('tool_product', 'sadad_authkey'),
                'method'    => 'create',
               // 'amount'    => format_float($productdata['total'],2),
                'amount'    => $productdata['total'],
                'currency'  => 'SAR',
                'internal_code'  => $randomcartnumber,
                'desc'      => $productdata['productname'],
                'testmode'      => get_config('tool_product', 'sadad_testmode'),
                'bill_fname'    => $userinfo->firstname,
                'bill_sname'    => $userinfo->lastname,
                'bill_addr1'    => $userinfo->city,
                'bill_phone'    => '0'.$userinfo->phone1,
                'bill_city'     => $userinfo->city,
                'bill_country'  => $userinfo->country,
                'bill_email'    => $userinfo->email,
                'bill_type' => 1,
                'company_reg' => $organizationinfo->reg,
                'company_name' => $organizationinfo->compname,
                'comm_id' => $organizationinfo->natid,
                'comm_name' => $organizationinfo->name
            );
            $telrreq = json_encode($telrreq);
        
            $result = $c->post($location, $telrreq, $options);
            $jResult = json_decode($result);
      
            if($jResult->SadadBillResponse->Status == 'Failed'){
                throw new Exception('Error'.$jResult->SadadBillResponse->Error);
            }
            if($jResult->SadadBillResponse->Status == 'Success'){
                $invoicedata = $this->check_invoice_status($jResult->SadadBillResponse->InvoiceNumber);
                return $invoicedata;
            }
            return false;
        }

        public function check_invoice_status($invoicenumber){
            $c = new curl();

            $options = array(
                'httpheader' => array('Content-Type: application/json'),
                'timeout' => 30,
                'CURLOPT_HTTP_VERSION' => CURL_HTTP_VERSION_1_1,
            );
            $location = "https://".self::$domain."/gateway/getsadadbill.json";
            $telrreq = array('SadadBill' => array(
                                            'StoreID'     => get_config('tool_product', 'sadad_storeid'),
                                            'AuthKey'   => get_config('tool_product', 'sadad_remotekey'),
                                            "invoice" => $invoicenumber,
                                        ));
            $telrreq = json_encode($telrreq);
            $result = $c->post($location, $telrreq, $options);

            if ($c->get_errno()) {
                throw new moodle_exception('errtelr', 'tool_product_telr', '', null, $result);
            }
            $jResult = json_decode($result);
            if(isset($jResult->AgreementAddChargeResponse)){
                throw new Exception($jResult->AgreementAddChargeResponse->Status, $jResult->AgreementAddChargeResponse->Code);
            }
            if(isset($jResult->SadadBillResponse) && $jResult->SadadBillResponse->Status != 'Success'){
                throw new Exception($jResult->SadadBillResponse->Error);
            }
            if(!empty($jResult->SadadBillResponse->data)){
                return $jResult->SadadBillResponse->data;
            }else{
                return false;
            }
        }
        
        public function refund($transactionid, $refundamount){
            global $DB, $USER;
            $transaction = $DB->get_record('tool_product_telr', ['id' => $transactionid]);
            $ordersummary = $this->get_order_summary($transaction);
            $productdata = unserialize(base64_decode($transaction->productdata));
            $products = unserialize(base64_decode($transaction->productdata))['items'];
             
            $testmode = get_config('tool_product', 'testmode');
          
            $store = get_config('tool_product', 'storeid');
            $refundauthkey = get_config('tool_product', 'refund_authkey');
            $c = new curl();
            $location = "https://".self::$domain."/gateway/remote.xml";
            $options = array(
                'httpheader' => array('Content-Type: application/xml; charset=utf-8', "Host: ".self::$domain.""),
                'timeout' => 30,
                'CURLOPT_HTTP_VERSION' => CURL_HTTP_VERSION_1_1,
            );
            $request = <<< EOD
                        <?xml version="1.0" encoding="UTF-8"?> 
                        <remote> 
                        <store>{$store}</store> 
                        <key>{$refundauthkey}</key> 
                        <tran> 
                        <type>refund</type> 
                        <class>ecom</class> 
                        <cartid>{$ordersummary->order->cartid}</cartid> 
                        <description>{$ordersummary->order->description}</description> 
                        <test>{$testmode}</test> 
                        <currency>{$ordersummary->order->currency}</currency> 
                        <amount>{$refundamount}</amount> 
                        <ref>{$ordersummary->order->transaction->ref}</ref> 
                        </tran> 
                        </remote> 
                        EOD;
            $result = $c->post($location, $request, $options);
            if ($c->get_errno()) {
                throw new moodle_exception('errtelr', 'tool_product_telr', '', null, $result);
            }
            $p = xml_parser_create();
            xml_parse_into_struct($p, $result, $vals, $index);
           
            $refund = new stdClass;
            $refund->transactionid = $transactionid;
            $refund->userid = $transaction->userid;
            $refund->realuser = ($USER->realuser) ? $USER->realuser :0;
            $refund->amount = $refundamount;
            $refund->transactiondate = time();
            $refund->usercreated = $USER->id;
            $refund->usermodified = $USER->id;
            $refund->timecreated = time();
            $refund->timemodified = time();
            $refund->productid = $products[0]['product_id'];
           

            $expectedkeys = array('REMOTE', 'AUTH', 'STATUS', 'CODE', 'MESSAGE', 'TRANREF','CVV', 'AVS','TRACE','AUTH','REMOTE','PAYMENT','DESCRIPTION','CARD_END','CARD_BIN','PAYMENT','REMOTE');
            $refundlog = new stdClass;
            
            foreach($vals as $r){
                if(!in_array($r['tag'], $expectedkeys)){
                    continue;
                }
                if($r['tag'] == 'MESSAGE') {
                    if($r['value'] !== 'Accepted' ){
                        $error[] = $r['value'];
                    }
                } 
                if($r['tag'] == 'TRANREF') {
                    if($r['value']  == 000000000000){
                        $error[] = $r['value'];
                    }
                }
              
                switch($r['tag']){
                    case 'AUTH':
                        $refundlog->auth_status = json_encode($r);
                        break;
                    case 'STATUS':
                        $refundlog->auth_status = json_encode($r);
                        break;
                    case 'CODE':
                        $refundlog->auth_code = json_encode($r);
                        break;
                    case 'MESSAGE':
                        $refundlog->auth_message = json_encode($r);
                        break;
                    case 'TRANREF':
                        $refundlog->auth_tranref = json_encode($r);
                        break;
                    case 'CVV':
                        $refundlog->auth_cvv = json_encode($r);
                        break;
                    case 'AVS':
                        $refundlog->auth_avs = json_encode($r);
                        break;
                    case 'TRACE':
                        $refundlog->auth_trace = json_encode($r);
                        break;
                    case 'DESCRIPTION':
                        $refundlog->payment_description = json_encode($r);
                        break;
                    case 'CARD_END':
                        $refundlog->payment_card_end = json_encode($r);
                        break;
                    case 'CARD_BIN':
                        $refundlog->payment_card_bin = json_encode($r);
                        break;
                }
                if($r['type'] == 'CODE' && $r['level'] ==3){
                    $refundlog->payment_code = json_encode($r);
                }
            }
            if(empty($error)){
                $refundid = $DB->insert_record('tool_product_refund', $refund);
            }else{
                $refundid = 0;
            }
            $refundlog->refundid = $refundid;
            $refundlog->usercreated = $USER->id;
            $refundlog->usermodified = $USER->id;
            $refundlog->realuser = ($USER->realuser) ? $USER->realuser :0;
            $refundlog->timecreated = time();
            $refundlog->timemodified = time();
            $refundlogid = $DB->insert_record('tool_product_refund_logs', $refundlog);
            
            if(!empty($error)) {
                throw new moodle_exception('errtelr', 'tool_product_telr', '', null, $error);
            }
           return true;
        }

        public function get_pending_invoice($productid, $userid=false){
            global $USER,$DB;
            if(!$userid){
                $userid = $USER->id;
            }
            $invoice = $DB->get_record_sql('SELECT id,productid,invoice_number,amount,payableamount,seats 
                                              FROM {tool_product_sadad_invoice} 
                                             WHERE productid=:productid AND userid=:userid AND (type="cancel" OR type = "purchase" OR type = "programsbulkenrollment"  OR type = "examsbulkenrollment" OR type = "assessment_operation_enrolments")
                                              AND status=1 AND payment_status=0 ORDER BY id DESC LIMIT 1',['productid' => $productid, 'userid' => $userid]);                                                          
            if(empty($invoice)){
                return false;
            }
            $invoicedetails = $this->check_invoice_status($invoice->invoice_number);
            if(empty($invoicedetails[0])){
                return false;
            }
            return $invoice;
        }

        public function void_invoice($invoicenumber){
            global $DB,$USER;
            $invoice = $DB->get_record('tool_product_sadad_invoice', ['invoice_number' => $invoicenumber]);
            if(!$invoice){
                return false;
            }
            $invoicecancel = $this->cancel_invoice($invoicenumber);
            // if($invoicecancel){
            $invoice->status = -1;
            $DB->update_record('tool_product_sadad_invoice', $invoice);
            return true;
            // }
        }

        protected function cancel_invoice($invoicenumber){
            $c = new curl();

            $options = array(
                'httpheader' => array('Content-Type: application/json'),
                'timeout' => 30,
                'CURLOPT_HTTP_VERSION' => CURL_HTTP_VERSION_1_1,
            );
            $location = "https://".self::$domain."/gateway/remote_sb.json";
            $telrreq = array('StoreID'     => get_config('tool_product', 'sadad_storeid'),
                            'AuthKey'   => get_config('tool_product', 'refund_authkey'),
                            'testmode'   =>get_config('tool_product', 'sadad_testmode'),
                            'RemoteKey'   =>get_config('tool_product', 'sadad_remotekey'),
                            'method' => 'cancel',
                            "invoice_no" => $invoicenumber,
                        );
            $telrreq = json_encode($telrreq);
            $result = $c->post($location, $telrreq, $options);

            if ($c->get_errno()) {
                throw new moodle_exception('errtelr', 'tool_product_telr', '', null, $result);
            }
            $jResult = json_decode($result);
            if(empty($jResult)){
                return false;
            }
            if($jResult->SadadBillResponse->Status == 'Failed'){
                return false;
            }else if($jResult->SadadBillResponse->Status == 'Success'){
                return false;
            }
        }
    }
