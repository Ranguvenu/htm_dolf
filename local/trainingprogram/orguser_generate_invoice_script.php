<?php
require_once('../../config.php');
global $CFG, $PAGE, $OUTPUT, $DB, $USER;
$systemcontext = context_system::instance();
$PAGE->set_url('/local/trainingprogram/reissued_invoces_script.php');
echo $OUTPUT->header();
    $sql = " SELECT DISTINCT(pe.userid), oas.*
    FROM mdl_tool_order_approval_seats AS oas 
    JOIN mdl_program_enrollments pe ON oas.id = pe.orderid   
    AND oas.orguserid = 290131 AND oas.tablename = 'tp_offerings' AND oas.fieldid = 51161
    AND pe.userid IN IN(353392, 353259, 353257, 353190, 10106,185804, 228212, 109120)
    ";
$records = $DB->get_records_sql($sql);
if($records) {
    foreach($records as $record) {
        try {
            $product = $DB->get_record('tool_products',['id' => $record->productid]);
            $order_payment = $DB->get_record('tool_org_order_payments',['id' => $record->paymentid]);
            switch ($order_payment->tablename) {
                case 'tp_offerings':
                    $entityid = $DB->get_field('tp_offerings','	trainingid',['id' => $order_payment->fieldid]);
                    break;
                case 'local_exam_profiles':
                    $entityid = $DB->get_field('local_exam_profiles','examid',['id' => $order_payment->fieldid]);
                    break;
                case 'local_events':
                    $entityid = $DB->get_field('local_events','	id',['id' => $order_payment->fieldid]);
                    break;
            }
            $orgorder = new stdClass();
            $orgorder->selectedseats = $order_payment->purchasedseats;
            $orgorder->fieldname = 'id';
            $orgorder->tablename = $order_payment->tablename;
            $orgorder->fieldid = $order_payment->fieldid;
            $orgorder->parentfieldid = $entityid;
            $orgorderresponse = (new local_exams\local\exams)->get_orgorderinfo($orgorder);
            $productdata = unserialize(base64_decode($orgorderresponse['returnparams']));
            $details['productname'] = $product->name;
            $details['paymentid'] = $record->paymentid;
            $details['approvalseats'] = $order_payment->purchasedseats;
            $details['productid'] = $product->id;
            $details['seats'] = $order_payment->purchasedseats;;
            $details['type'] = 'postpaid';
            $details['total'] = $productdata['total'];
            $details['userid'] =$order_payment->orguserid;
            $details['approvaluserid'] = $order_payment->orguserid;
            $order_payment = $DB->get_record('tool_org_order_payments',['id' => $record->paymentid]);
            $productdataen =  base64_encode(serialize($details));
            $organizationid = (new tool_product\product)->get_user_organization($order_payment->orguserid);
            $response = (new tool_product\telr)->generate_sadad_bill($productdataen);
             // SADAD
             if(!empty($response)) {
                $responsedata = $response[0];
                if($responsedata->invoiceNumber) {
                    $newrecord = new stdClass();
                    $newrecord->productid =$product->id;
                    $newrecord->userid = $order_payment->orguserid;
                    $newrecord->realuser = ($USER->realuser) ? $USER->realuser :0;
                    $newrecord->telrid = 0;
                    $newrecord->invoice_number =$responsedata->invoiceNumber;
                    $newrecord->seats = $order_payment->purchasedseats;
                    $newrecord->type = 'purchase';
                    $newrecord->amount =$productdata['total_price'];
                    $newrecord->payableamount = $productdata['total'];
                    $newrecord->status =1;
                    $newrecord->payment_status =0;
                    $newrecord->timecreated = time();
                    $newrecord->usercreated = $USER->id;
                    $newrecord->organization = $organizationid?$organizationid:0;
                    $insertid = $DB->insert_record('tool_product_sadad_invoice', $newrecord);
                    // ORDER PAYMENT UPDATE
                    $order_payment->transactionid = $responsedata->invoiceNumber;
                    $order_payment->payableamount = $productdata['total'];
                    $order_payment->taxes = $productdata['taxes'];
                    $order_payment->timemodified = time(); 
                    
                    $DB->update_record('tool_org_order_payments', $order_payment);
                    // approval_seats UPDATE
                    $record->timemodified = time();
                    $record->approvalseats = $order_payment->purchasedseats;
                    $DB->update_record('tool_order_approval_seats', $record);

                    echo '<div class="col-md-10 alert alert-success"> SADAD Generated Successfully for ORDER PAYMENT ID  '.$record->paymentid.'</div>';
                }
            }
        } catch(moodle_exception $e){
            print_r($e);
        }
    }
}
echo $OUTPUT->footer();