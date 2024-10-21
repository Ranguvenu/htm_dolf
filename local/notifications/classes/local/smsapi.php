<?php
namespace local_notifications\local;

global $CFG;
require_once($CFG->libdir . '/filelib.php');

use context_user;
use context_system;
use stdClass;
use curl;

class smsapi {

    public function __construct(){

        $smsapienable=get_config('local_notifications', 'smsapienable');
        if(!$smsapienable){
            return false;

            }
    }

    public function sendsms($text,$phonenumber){
            $curl = new curl();
             $url = get_config('local_notifications', 'smsapicccounturl');

            $curl->setHeader(array('Content-type: application/json'));
            $arr = array();
            array_push($arr, 'Accept: application/json, text/javascript, */*; q=0.01');
            array_push($arr, 'Accept-Language: en-us,en;q=0.5');
            array_push($arr, 'Accept-Encoding=gzip,deflate');
            array_push($arr, 'Accept-Charset=ISO-8859-1,utf-8;q=0.7,*;q=0.7');
            array_push($arr, 'Keep-Alive: 115');
            array_push($arr, 'Connection: keep-alive');
            array_push($arr, 'Content-Type: application/json; charset=utf-8');
            $numbers_array = array();
            curl_setopt($curl, CURLOPT_HTTPHEADER, $arr);
            array_push($numbers_array,'966'.$phonenumber);
           $params = [
                    'username' => get_config('local_notifications', 'smsapiauthenticateusername'),
                    'password' => get_config('local_notifications', 'smsapiauthenticatepassword'),
                    'Array' => $numbers_array,
                    'Sender' => 'TFACADEMY',
                    'Text' => $text,
            ];
          
            $curl_post_data = json_encode($params); 

            $response = $curl->post($url, $curl_post_data);
            
            $response = json_decode($response);
      
            if(!$response->ErrorCode){
                $res = new stdClass();
                $res->success = 1;
                return $res;
            }

            return false;
    }
}
