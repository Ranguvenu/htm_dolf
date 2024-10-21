<?php

error_reporting(E_ALL);
ini_set("display_errors", 1);

require_once('../../../config.php');

use tool_product\product as product;
use tool_product\telr as telr;
use tool_product_external;

global $DB, $USER, $CFG;

$sitecontext=context_system::instance();

require_login();

$count=$DB->count_records('tool_user_order_payments'); 

if($count == 0){

	$stable = new \stdClass();
    $stable->thead = false;
    $stable->start = 0;
    $stable->length = -1;

	$getpaymentlist = (new \tool_product\product)::get_listof_mypayments($stable,$filterdata=array());

	$paymentslist = array_values($getpaymentlist['payments']);

	foreach($paymentslist as $list) {

	    $product_data = unserialize(base64_decode($list->productdata));
	    $product_data['paymenttype']='telr';
	    $product_data['userid']=$list->userid;
	    $product_data['timeupdated']=$list->timeupdated;
	    tool_product_external::user_order_payments($product_data);
	  
	}
}

?>

