<?php
/**
 * @package    tool_product
 * @copyright  2022 Naveen kumar <naveen@eabyas.com>
 */
namespace tool_product;

use tool_product\product as product;
use tool_product\telr as telr;
use tool_product_external;
use moodle_exception;
use moodle_url;
use context_system;
use stdClass;
use local_exams\local\exams as exams;
use local_exams\local\fast_service as fastservice;

/**
 * Product manager
 */
class orders
{

    public function begin_transaciton($product) {
        global $CFG, $DB, $USER, $OUTPUT;
        $productdata = unserialize(base64_decode($product['products']));


        
        try {
            $transaction = $DB->start_delegated_transaction();
            $pd = new stdClass();
            if($productdata['paymenttype'] == 'postpaid'){
                $pd->storeid = get_config('tool_product', 'sadad_storeid');
            }else{
                $pd->storeid = get_config('tool_product', 'storeid');
            }
            $pd->status = 0;
            $pd->timecreated = time();
            $pd->timecreated = time();
            $pd->amount = $productdata['total'];
            $pd->productdata = $product['products'];
            $pd->userid = $USER->id;
            $pd->realuser =($USER->realuser) ? $USER->realuser :0;

            $pd->id = $DB->insert_record("tool_product_telr_pending", $pd);
            if($productdata['total'] > 0){
                $jsonResult = (new telr)->create_order($productdata, $pd->id);
                $ref = $jsonResult->order->ref;
            }else{
                $ref = '';
            }

            $pd->orderref = $ref;
            $pd->status = 1;
            $DB->update_record('tool_product_telr_pending', $pd);
            $transaction->allow_commit();
        } catch(moodle_exception $e){
            $transaction->rollback($e);
            return false;
        }
        if($productdata['total'] <= 0){
            $sitecontext = context_system::instance();
           if(!is_siteadmin() && has_capability('local/organization:manage_organizationofficial',$sitecontext)) {
                $productdata['paymenttype']='telr';
                tool_product_external::org_order_payments($productdata);
            } else {
                $productdata['paymenttype']='telr';
                $productdata['userid']=$pd->userid;
                $productdata['telrid']=0;
                $productdata['product_id'] = $productdata['productid'];
                tool_product_external::user_order_payments($productdata);
                (new product)->enrol($productdata, $USER->id);
            }
            $redirecturl =  new moodle_url('/admin/tool/product/ordersummary.php', array('productdata'=> $product['products']));
            return ['noseats' => '',
                'productname' => '',
                'returnurl' => $redirecturl->out()
            ];
        }else{
            return ['noseats' => '',
                'productname' => '',
                'returnurl' => $jsonResult->order->url
            ];
        }
        
    }

    public function post_process($pd, $invoiceid=0) {
        global $DB,$USER;
        $productdata = unserialize(base64_decode($pd->productdata));
        $jResult = (new telr)->get_order_summary($pd);
        $pd->lasttimechecked = time();
        $pd->lastorderstatuscode = $jResult->order->status->code;
        $pd->lastorderstatus = $jResult->order->status->text;
        $DB->update_record('tool_product_telr_pending', $pd);

        if ($pd->lastorderstatuscode < 0) { // Expired, Cancelled or Declined    
            return false;
         //   redirect(new moodle_url('/my/index.php'));
        }
        $sitecontext = context_system::instance();

        // If status is pending and reason is other than echeck then we are on hold until further notice
        // Email user to let them know. Email admin.

        if ($pd->lastorderstatuscode == 1 || $pd->lastorderstatuscode == 2) { // Pending or Authorised 
            // return false;
            // \tool_product_telr\util::message_telr_error_to_admin("Payment pending - manual check required", $pd);
            //redirect(new moodle_url('/enrol/telr/return.php', array('id'=>$course->id)));
        }
        // At this point we only proceed with a status of completed or pending with a reason of echeck
        // Make sure this transaction doesn't exist already.
        if ($existing = $DB->get_record("tool_product_telr", array("orderref" => $pd->orderref), "*", IGNORE_MULTIPLE)) {
            return false;
            // print_r($existing);
            // throw new moodle_exception('errtelr', 'tool_product_telr', '', null, $result);
            // die;
        }

        // if($pd->lastorderstatuscode != 3) {
        //     throw new moodle_exception('errtelr', 'tool_product_telr', '', null, $result);
        //     redirect(new moodle_url('/enrol/telr/return.php', array('id'=>$course->id)));
        // }
        // User has paid, transaction is good

        $d = new stdClass();
        $d->storeid = $pd->storeid;
        $d->productdata = $pd->productdata;
        $d->userid = $pd->userid;
        $d->realuser =($USER->realuser) ? $USER->realuser :0;
        $d->timeupdated = time();
        $d->telrpid = $pd->id;

        // Record all details of the order
        $d->orderref = $jResult->order->ref;
        $d->test = $jResult->order->test;
        $d->amount = $jResult->order->amount;
        $d->currency = $jResult->order->currency;
        $d->description = $jResult->order->description;
        $d->statuscode = $jResult->order->status->code;
        $d->statustext = $jResult->order->status->text;

        if(!empty($jResult->order->transaction)) {
            $d->transactionref = $jResult->order->transaction->ref;
            $d->transactiontype = $jResult->order->transaction->type;
            $d->transactionstatus = $jResult->order->transaction->status;
            $d->transactioncode = $jResult->order->transaction->code;
            $d->transactionmessage = $jResult->order->transaction->message;
        }
         
       
        if ($d->amount < $productdata['total']) {
            return false;
           // throw new moodle_exception('amountmismatch', 'tool_product_telr', '', null, $d);
            // \tool_product_telr\util::message_telr_error_to_admin("Amount paid is not enough ($d->amount < $cost))", $d);
          //  die;
        }
        if($pd->lastorderstatuscode > 0){
            $d->id = $telrid = $DB->insert_record("tool_product_telr", $d);
            if(!is_siteadmin() && has_capability('local/organization:manage_organizationofficial',$sitecontext) && $productdata['category'] == 'wallet'){

                (new product)->add_to_wallet($productdata);
                
            } else if(!is_siteadmin() && has_capability('local/organization:manage_organizationofficial',$sitecontext) && $productdata['category'] == 'booking') {
                
                $productdata['paymenttype']='telr';
                tool_product_external::org_order_payments($productdata);

            }else{

                if($productdata['paymenttype'] == 'postpaid'){
                    
                    $orgpayment = $DB->get_record('tool_org_order_payments', ['id' => $productdata['paymentid']]);
                    $d->orgpaymentid = $productdata['paymentid'];
                    $DB->update_record('tool_product_telr', $d);
                    $orgpayment->payableamount = $productdata['total'];
                    $orgpayment->transactionid = $invoiceid;
                    $orgpayment->realuser = ($USER->realuser) ? $USER->realuser :0;
                    $DB->update_record('tool_org_order_payments', $orgpayment);
                    $orderdata = $DB->get_record('tool_order_approval_seats', ['paymentid' => $productdata['paymentid']], 'id,purchasedseats as purchasedseatschk');
                    $orderdata->approvalseats = $productdata['approvalseats'];
                    (new product)->insert_update_org_order_approvals($orderdata);
                }else{
                    if ($pd->lastorderstatuscode == 1 || $pd->lastorderstatuscode == 2) { // Pending or Authorised 
                        return false;
                    }
                    $productdata['paymenttype']='telr';
                    $productdata['userid']=$pd->userid;
                    $productdata['telrid']=$d->id;
                    $productdata['product_id'] = $productdata['productid'];
                    tool_product_external::user_order_payments($productdata);

                    if(!empty($productdata['fromuserid']) &&  !empty($productdata['touserid'])) {
                       (new product)->insert_replacementprocess_record($productdata);
                    } else {
                        if ($productdata['processtype'] == 'reschedule') {
                            $productdata['items'][0] = $productdata;
                            tool_product_external::user_order_payments($productdata);
                        } else {
                            tool_product_external::user_order_payments($productdata);
                            $traineeroleid = $DB->get_field('role', 'id', array('shortname' => 'trainee'));
                            $othersitelevelroles = $DB->record_exists('role_assignments', ['userid' => $pd->userid, 'contextid' => $sitecontext->id]);

                            if(!user_has_role_assignment($pd->userid,$traineeroleid,$sitecontext->id) && empty($othersitelevelroles)){
                                role_assign($traineeroleid,$pd->userid,$sitecontext->id);
                            }
                            (new product)->enrol($productdata, $pd->userid); 
                        }
                    } 

                    foreach($productdata['items'] as $info) {
                        $apidata = [];
                        $product = $DB->get_record('tool_products', ['id' => $info['product_id']]);
                        if ($product->category == 2 || $product->category == 6) {

                            // Checking Hall Availability settings are Enabled or not
                            $accessstatus = (new \local_exams\local\exams)->access_fast_service('examreservation');
                            if ($accessstatus) {
                                $info['transactiontypes'] = 'register';
                                $info['hallscheduelid'] = $info['hallscheduleid'];
                                $data = new stdClass();
                                $data->userid = $pd->userid;
                                $data->transactionref = $jResult->order->transaction->ref;
                                $data->orderref = $jResult->order->ref;
                                $data->productdata = $pd->productdata;
                                $apidata = (new exams)->prepare_fast_apidata($info, $data);
                                $apidata['validation'] = 0;
                                $fastapi = new fastservice($apidata);
                                $fastapi->create_exam_reservations();
                            }
                        }
                    }  

                }
            }
        }

        return $telrid;
    }

    public function store($data){
        global $DB, $USER;
        $record = $this->prepare($data);
        $record->timecreated = time();
        $record->usercreated = $USER->id;
        $record->realuser =($USER->realuser) ? $USER->realuser :0;

        try {
            $orderid = $DB->insert_record('tool_orders', $record);

            // $paymentorder=new \stdClass();

            // $touser=$DB->get_record('user',array('id'=>$record->userid));

            // $paymentorder->payment_userfullname=$touser->firstname.' '.$touser->lastname;
            // $paymentorder->payment_details=$touser->firstname. $touser->lastname;
            // $paymentorder->invoiceno=$orderid;

            // (new \tool_product\notification())->product_notification('payment_completion', $touser,$fromuser=get_admin(), $paymentorder,$waitinglistid=0);

            return $orderid;
        } catch(dml_exception $e){
            print_error($e->debuginfo);
        }
    }

    private function prepare($data){
        global $DB;
        $record = new stdClass;

        if(!$DB->record_exists('tool_product', ['id' => $data->productid])){
            print_error('Product not available');
        }
        $record->productid = $data->productid;
        if(!$DB->record_exists('user', ['id' => $data->userid, 'confirmed' => 1, 'deleted' =>0 ])){
            print_error('Invalid user');
        }
        $record->userid = $data->userid;

        $record->paymentstatus = $data->paymentstatus;
        $record->orderstatus = $data->orderstatus;
        $record->timepurchased = time();

        return $record;
    }
}
