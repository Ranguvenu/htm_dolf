 <?php
require_once('config.php');
global $DB, $CFG, $OUTPUT,$USER, $PAGE;
 
$PAGE->set_url('/local/trainingprogram/scriptscriptscript.php');
$systemcontext = context_system::instance();
$PAGE->set_context($systemcontext);
$returnurl = new moodle_url('/local/trainingprogram/scriptscript.php');
$PAGE->set_url('/local/trainingprogram/script.php');
 
$PAGE->set_pagelayout('sitefrontpage');
echo $OUTPUT->header();

$record = $DB->get_records_sql("SELECT * FROM {tool_product_refund} ");

foreach($record as $data){

 	$telr = $DB->get_record_sql("SELECT tpt.*
                                           FROM {tool_product_telr} tpt
                                          WHERE tpt.id =".$data->transactionid);
                                          
    $products = unserialize(base64_decode($telr->productdata))['items'];

    try{
 		$updaterec = new \stdClass();
		$updaterec->id               = $data->id;
		$updaterec->productid        = $products[0]['product_id'];
        $updaterecord = $DB->update_record('tool_product_refund', $updaterec); 
    } catch(dml_exception $e){
        print_r($e);
    }        
} 
echo $OUTPUT->footer();

