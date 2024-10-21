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

namespace tool_product\task;

use core\task;
use tool_product\telr;
class sadadpayment extends \core\task\scheduled_task {

    /**
     * Get a descriptive name for this task (shown to admins).
     *
     * @return string
     */
    public function get_name() {
        return get_string('sadadpayments', 'tool_product');
    }

    /**
     * Do the job.
     * Throw exceptions on errors (the job will be retried).
     */
    public function execute() {
        global $CFG, $DB;

        $lastruntime=self::get_last_run_time();

        $nextruntime=self::get_next_run_time();

        $timenow   = time();

        if($nextruntime <= $timenow){
            $invoices = $DB->get_records_select('tool_product_sadad_invoice', 'status=1 AND payment_status=0', null,'','*',250,301);
            $i=0;
         
            foreach($invoices as $invoice){
             
                $invoicedata = (new telr)->check_invoice_status($invoice->invoice_number);
               
                if($invoicedata[0]->paymentStatus == 'Paid'){
                    $orgpayment = $DB->get_record('tool_org_order_payments', ['transactionid' => $invoice->invoice_number]);
                  
                    if($orgpayment){
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
        } 
      
    }
}
