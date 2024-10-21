<?php
require_once('../../config.php');
global $CFG, $DB;

$type = optional_param('type', 'reschedule', PARAM_RAW);
$PAGE->set_url('/local/exams/payments_script.php?mode='.$type);
$PAGE->set_context(context_system::instance());
$PAGE->set_title(get_string('mypayments', 'tool_product'));
$PAGE->set_heading(get_string('mypayments', 'tool_product'));


echo $OUTPUT->header();
/*$sadad_sql = " SELECT si.* FROM {tool_product_sadad_invoice} AS si 
     WHERE si.invoice_number IN ('00093354585485','00093354191925','00093353903287','00093353903148','00093353900561','00093353890656',
        '00093353889097','00093353868887','00093353862127','00093353849413','00093353844511','00093353830982','00093353825048',	
        '00093353810056','00093353809737','00093353809464', '00093353808830','00093353808629','00093353806231','00093353789568','00093353473354',	
        '00093353457501','00093353455864','00093353455735',	'00093353455664') AND si.type = :type";
$records = $DB->get_records_sql($sadad_sql, ['type'=>$type]);
foreach($records as $record) {
    try{  
        $sendingdata =new stdClass();
        $sendingdata->userid = $record->userid;
        $productname = $DB->get_field('tool_products','name',['id' => $record->productid]);
        $sendingdata->productid = $record->productid;
        $sendingdata->productname = $productname;
        $sendingdata->seats = 1;
        $sendingdata->type = 'reschedule';
        $sendingdata->total = $record->amount;
        $sendingdata->payableamount = $record->payableamount;
        $productdata =  base64_encode(serialize((array)$sendingdata));
        $data = (new \tool_product\product())->insert_update_sadad_invoice_record($productdata);
        echo '<div class="col-md-10 alert alert-success">Inserted Successfully with id '.$record->id.'</div>';
    }catch(moodle_exception $e){
        print_r($e);
    }
}*/

$sql = " SELECT si.*, oop.id AS paymentid, oop.transactionid FROM {tool_product_sadad_invoice} AS si 
JOIN {tool_org_order_payments} AS oop ON oop.transactionid = si.invoice_number 
     WHERE si.invoice_number IN ('00069367368359','00069367368110','00069367368058')";
$sadad_records = $DB->get_records_sql($sql);

foreach($sadad_records as $record) {
   
    try{
        if($DB->record_exists('tool_product_sadad_invoice',['invoice_number' => $record->invoice_number])) {
            (new \tool_product\telr)->void_invoice($record->invoice_number);
            echo '<div class="col-md-10 alert alert-success">tool_product_sadad_invoice Updated Successfully with id '.$record->id.'</div>';
        }
        if($DB->record_exists('tool_org_order_payments',['id' =>$record->paymentid])) {
            $get_record = $DB->get_record('tool_org_order_payments',['id' =>$record->paymentid]);
            $get_record->orderstatus = -1;
            $DB->update_record('tool_org_order_payments', $get_record);
            echo '<div class="col-md-10 alert alert-success">tool_org_order_payments Updated Successfully with id '.$get_record->id.'</div>';
        }
    } catch(moodle_exception $e){
        print_r($e);
    }
}
echo $OUTPUT->footer();