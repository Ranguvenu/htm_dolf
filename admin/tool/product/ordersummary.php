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

/**
 * TODO describe file ordersummary
 *
 * @package    tool_product
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
require("../../../config.php");
require_once("lib.php");
require_once("{$CFG->dirroot}/local/exams/lib.php");

use local_exams\local\cisi_services;

$id = optional_param('id', 0, PARAM_INT);
$telrid = optional_param('telrid', 0, PARAM_INT);
$productdata = optional_param('productdata', '', PARAM_RAW);

$context = context_system::instance();
$PAGE->set_context($context);

require_login();
if($id){
    $pd = $DB->get_record("tool_product_telr_pending", array('id'=>$id), "*", MUST_EXIST);

    if($pd->status != 1) {
        notice(get_string('paymentsorry', '', $a), $destination);
    }

}
$PAGE->set_url('/admin/tool/product/ordersummary.php', ['id' => $id]);
echo $OUTPUT->header();

if($pd){
    $productdata = $pd->productdata;
    $orderdetails = $pd;
}
$summary = unserialize(base64_decode($productdata));
$PAGE->requires->js_call_amd('too_product/product', 'reset_cart');

if($summary['total'] == 0 || ($summary['total'] > 0 && $pd->lastorderstatuscode == 3)){

    echo $OUTPUT->notification(get_string('success'), 'success');
    echo $OUTPUT->render_from_template('tool_product/checkout/ordersummary', $summary);
    
    // if($telrid) {
    //     $paymentdetails = (new local_exams\local\exams)->order_details($telrid);
    // } else{ // ************* DL-304: IKRAM CODE START ***************************************
    //     $items = $summary['items'];
    //     cisi_exams_booking($items);
    // }
    // ************* DL-304: IKRAM CODE END ***************************************

    // **************** cisi_exams_booking($orderinfo); commented in order_summary page and keep it here only by modifying it as below. ******************
    if($telrid) {
        $data = $DB->get_record("tool_product_telr", array('id'=>$telrid), "*", MUST_EXIST);
        $summary = unserialize(base64_decode($data->productdata));
        $orderinfo = $summary['items'];
    } else {
        $orderinfo = $summary;
    }
    // $items = $summary['items'];
    $paymentdetails = (new local_exams\local\exams)->attemptpurchase($summary);
    // $paymentdetails = (new local_exams\local\exams)->order_details($telrid,$items);  // Commented this one
   // $paymentdetails = (new local_exams\local\exams)->order_details($orderinfo);
    cisi_exams_booking($orderinfo);

    // Now it should work properly..
    
    
}else{
    echo $OUTPUT->box(get_string('canceldeclined', 'tool_product', $pd), 'errorbox alert alert-danger');
}
echo $OUTPUT->continue_button('/my');
echo $OUTPUT->footer();
