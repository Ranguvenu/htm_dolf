<?php


define('CLI_SCRIPT', true);
require_once('../../../../config.php');
//require_once 'lib.php';

//global $DB,$USER;


$curl = curl_init();

curl_setopt_array($curl, array(
  CURLOPT_URL => "",
  CURLOPT_RETURNTRANSFER => true,
  CURLOPT_ENCODING => "",
  CURLOPT_MAXREDIRS => 10,
  CURLOPT_TIMEOUT => 30,
  CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
  CURLOPT_CUSTOMREQUEST => "POST",
  CURLOPT_POSTFIELDS => "{\n   \"authBy\": \"c_ex\",\n   \"srcAPP\": \"FA\",\n   \"userName\": \"wasim.tech1@dfmail.org\",\n   \"password\": \"admin@123\"\n}",
  CURLOPT_HTTPHEADER => array(
    "Content-Type: application/json",
    "Postman-Token: 9aac5394-87a4-4d9a-a28a-52efd71373cc",
    "Token: 1",
    "cache-control: no-cache"
  ),
));

$response = curl_exec($curl);
$err = curl_error($curl);

curl_close($curl);

if ($err) {
  echo "cURL Error #:" . $err;
} else {
echo $response;
$response = json_decode($response,TRUE);
  $pass =  $response['pass'];
}



//Getting API response  
//<?php



$apis = array("fa_ins","fa_org","fa_dept","fa_degree","fa_program","fa_cohort","fa_acyr","fa_staff","fa_studs",
"fa_substafmap","fa_chp_subchp","fa_exmschdl" );

foreach ($apis as $api) {

$curl = curl_init();

curl_setopt_array($curl, array(
  CURLOPT_URL => "",
  CURLOPT_RETURNTRANSFER => true,
  CURLOPT_ENCODING => "",
  CURLOPT_MAXREDIRS => 10,
  CURLOPT_TIMEOUT => 60,
  CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
  CURLOPT_CUSTOMREQUEST => "POST", /*,\n\"sts\" : \"F\"*/
  CURLOPT_POSTFIELDS => "{\n\"query\":\"ONE_DAY\",\n\"typ\" : \"$api\"\n}",
  CURLOPT_HTTPHEADER => array(
  	"Authorization: Bearer $pass",
    "Content-Type: application/json",
    "Postman-Token: 4d2bf597-edca-40d5-a9b4-0e1ff50c34f4",
    "cache-control: no-cache"
  ),
));

$response1 = curl_exec($curl);
$err = curl_error($curl);

curl_close($curl);
global $DB;
if ($err) {
  echo "cURL Error #:" . $err;
} else {
	$response1 = json_decode($response1,TRUE);

	$records = array();
	foreach ($response1 as $response) {

		$record = new stdClass();
		$record->apiname=$response['typ'];
		$record->faid=$response['_id'];
		$record->req= json_encode($response['req']);
		$record->refid=$response['refid'];
		$record->typ=$response['typ'];
		$record->inid=$response['InId'];
		$record->crat=strtotime($response['CrAt']);
		$record->fauv=$response['__v'];
		$record->restm=$response['restm'];
		$record->sts=$response['sts'];
		$record->res=json_encode($response['res']);
		$record->timecreated=time();
		$records[] = $record; 
	}

	$DB->insert_records('local_lmsws_fapayload',$records);
  //print_object( count($records[]));
}

}
		
