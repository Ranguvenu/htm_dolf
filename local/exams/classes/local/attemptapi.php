<?php
namespace local_exams\local;

use curl;
use dml_exception;
ini_set('display_startup_errors', 1);
error_reporting(E_ALL); 
ini_set('display_errors', 1);
class attemptapi
{
    public function attempt_api($attemptinfo) {
        global $DB, $CFG;

        $curl = curl_init();
        curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, false);
        curl_setopt_array($curl, array(
          CURLOPT_URL => $CFG->wwwroot.'/webservice/rest/server.php?wstoken=a7ce932e5a3af8bb42f683057cd174b1&wsfunction=ws_get_userexam&moodlewsrestformat=json',
          CURLOPT_RETURNTRANSFER => true,
          CURLOPT_ENCODING => '',
          CURLOPT_MAXREDIRS => 10,
          CURLOPT_TIMEOUT => 0,
          CURLOPT_FOLLOWLOCATION => true,
          CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
          CURLOPT_CUSTOMREQUEST => 'POST',
          CURLOPT_POST => true,
          CURLOPT_POSTFIELDS => array('examcode' => $attemptinfo['examcode'],'username' => $attemptinfo['username'],'hallcode' => $attemptinfo['hallcode'],'profilecode' => $attemptinfo['profilecode'],'grade' => $attemptinfo['grade'],'achievementdate' => $attemptinfo['achievementdate'],'attemptcount' => $attemptinfo['attemptcount'],'certificatecode' => '0','examdate' => $attemptinfo['examdate'],'starttime' => $attemptinfo['starttime'],'endtime' => $attemptinfo['endtime']),
          CURLOPT_HTTPHEADER => array(
            'Cookie: Path=/'
          ),
        ));
        
        $response = curl_exec($curl);

        $errors = curl_error($curl);

        curl_close($curl);
        
        return $response;
    }


    public function get_scheduleavailability($schedulesinfo) {
        global $DB, $CFG;
        $curl = curl_init();

        curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, false);
        curl_setopt_array($curl, array(
          CURLOPT_URL => 'https://stagingportal.fa.gov.sa/api/MobileExam/GetTestCenterSchedules',
          CURLOPT_RETURNTRANSFER => true,
          CURLOPT_ENCODING => '',
          CURLOPT_MAXREDIRS => 10,
          CURLOPT_TIMEOUT => 0,
          CURLOPT_FOLLOWLOCATION => true,
          CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
          CURLOPT_CUSTOMREQUEST => 'POST',
          CURLOPT_POSTFIELDS =>'{
            "CenterCode": "'.$schedulesinfo["centercode"].'",
            "FromDate": "'.$schedulesinfo["FromDate"].'",
            "ToDate": "'.$schedulesinfo["ToDate"].'",
            "PageNumber" :1,
            "PageSize":365
          }',
          CURLOPT_HTTPHEADER => array(
            'Content-Type: application/json-patch+json',
            'Cookie: .AspNetCore.Culture=c%3Den-GB%7Cuic%3Den-GB; cookiesession1=678A3E6E6809C209895103776D0800D2'
          ),
        ));

        $response = curl_exec($curl);

        $errors = curl_error($curl);

        curl_close($curl);
        
        return $response;
    }
}
