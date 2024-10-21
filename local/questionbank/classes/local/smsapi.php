<?php
namespace local_questionbank\local;

use context_user;
use context_system;

class sendsms {

    // public function __construct($text,$phoneno,$username=null){
       
    //     parent::__construct($username, $phoneno);
    // }

   public function sendsms($text,$phoneno){
                
                $request =""; //initialise the request variable 
                //$param[type]= "smsquicksend";
                // $param[user]= "pjtsaulibrary";
                // $param[pass]= "welcome";
                // $param[sender]= "KMCPJT";
                $param['number'] = $phoneno; 
                //$param[to_mobileno] = 00; 
                $param['text'] = $text; 
                
                                                
                //Have to URL encode the values 
                foreach($param as $key=>$val) {
                $request.= $key."=".urlencode($val)."&"; //we have to urlencode the values $request.= "&";
                //append the ampersand (&) sign after each parameter/value pair
                }
                
                $request = substr($request, 0, strlen($request)-1); //remove final (&) sign from the request

                //print_object($request);

                $url = "https://eservices.fa.gov.sa/api/app/Account/SendSMS?".$request;
                
                $ch = curl_init($url);
                curl_setopt($ch, CURLOPT_RETURNTRANSFER, true); 
                $curl_scraped_page = curl_exec($ch); 
                curl_close($ch);
                return $curl_scraped_page;
}
