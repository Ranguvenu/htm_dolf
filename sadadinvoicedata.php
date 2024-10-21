<?php
require_once('config.php');
use tool_product\telr;
$invoices = $DB->get_records_select('tool_product_sadad_invoice', 'invoice_number=00069341515687');

foreach($invoices as $invoice){

    $invoicedata = (new telr)->check_invoice_status($invoice->invoice_number);
   print_object($invoicedata);
    if($invoicedata[0]->paymentStatus == 'Paid'){
     
        $orgpayment = $DB->get_record('tool_org_order_payments', ['transactionid' => $invoice->invoice_number]);
        
        if($orgpayment){
            print_object($orgpayment);
            $orgpayment->amountrecived = 1;
            $orgpayment->transactionref = $invoicedata[0]->billNo;
            $invoice->payment_status = 1;
         

            $DB->update_record('tool_org_order_payments', $orgpayment);
            $DB->update_record('tool_product_sadad_invoice', $invoice);
        }
    }else{
        continue;
    }
}