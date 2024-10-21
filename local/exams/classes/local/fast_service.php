<?php
namespace local_exams\local;

use curl;
use dml_exception;
/**
 * Fast service API calls
 */
class fast_service
{
    private $fasttoken;

    private $apidata=array();

    private $hosturl;

    function __construct($data)
    {
        global $DB;
        $this->apidata = $data;
        if(empty($data)){
            return false;
        }
        if($record = $DB->get_record('local_fast_examenrol', ['username'  => $data['username'], 
                                                              'profilecode'=> $data['profilecode'],
                                                              'examcode' => $data['examcode'],
                                                              'transactiontypes' => $data['transactiontypes'],
                                                              'validation' => $data['validation']])){
                $this->apidata['id'] = $record->id;
        }else{
            $this->apidata['id'] = $this->add_update_fast_service($this->apidata);
        }
        
        $fastapienable=get_config('local_exams', 'fastapienable');

        if($fastapienable){
            $this->hosturl = get_config('local_exams', 'fastapihosturl');
            $curl = new curl();
            $authurl = get_config('local_exams', 'fastapiauthenticateurl');
     
            $curl->setHeader(array('Content-type: application/json-patch+json'));
            $params = [
                    'userName' => get_config('local_exams', 'fastapiauthenticateusername'),
                    'password' => get_config('local_exams', 'fastapiauthenticatepassword')
            ];

            $post = json_encode($params);
                
            $curl->setHeader(array('Accept: application/json', 'Expect:'));
            $response = $curl->post($this->hosturl.$authurl, $post);
            $response = json_decode($response);

            if(!empty($response) && isset($response->token)){
                $this->fasttoken = $this->generate_user_token($response->token);
            }else{
                $this->apidata['status'] = "-1";
                $this->apidata['errormessage'] = get_string('invalid_api_details','local_exams');
                $this->add_update_fast_service($this->apidata);
            }
        }
        return false;
    }

    private function generate_user_token($token) {
        $replacestatus = (new \local_exams\local\exams)->access_fast_service('replacefast');
        if ($replacestatus) {
            $rgeneratetoken = (new \local_exams\local\exams)->access_fast_service('replacegeneratetoken');
            if ($rgeneratetoken) {
                $url = $this->hosturl.$rgeneratetoken;
            }
        } else {
            $url = $this->hosturl.'/api/MobileAuthenticate/GenerateTokenByUsername';
        }

        $curl = new curl();
        $curl->setHeader(array('Content-type: application/json'));
        $curl->setHeader('Authorization: Bearer '.$token);
        $params = [
            "UserName" => $this->apidata["username"],
        ];
        $post = json_encode($params);
        $curl->setHeader(array('Accept: application/json', 'Expect:'));
        $curl_post_data = json_encode($params); 
        $response = $curl->post($url, $curl_post_data);
        $response = json_decode($response);
        if($response->success){
            return $response->token;
        }else{
            $this->apidata['status'] = "-1";
            $this->apidata['errormessage'] = get_string('invalid_api_details','local_exams');
            $this->add_update_fast_service();
        }
        return false;
    }

    public function create_exam_reservations() {
        if($this->fasttoken){

            $replacestatus = (new \local_exams\local\exams)->access_fast_service('replacefast');
            if ($replacestatus) {
                $replaceexamreservation = (new \local_exams\local\exams)->access_fast_service('replaceexamreservation');
                if ($replaceexamreservation) {
                    $accounturl = $replaceexamreservation;
                }
            } else {
                $accounturl = get_config('local_exams', 'fastapicccounturl');
            }

            if ($accounturl) {
                $curl = new curl();
                $curl->setHeader(array('Content-type: application/json'));
                $curl->setHeader('Authorization: Bearer '.$this->fasttoken);
    
                $params = [
                    "RegistrationDate" => null, //$this->apidata['purchasedatetime'],
                    "UserName" => $this->apidata["username"],
                    "CenterCode" => $this->apidata["centercode"],
                    "ExamCode" => $this->apidata["examcode"],
                    "ProfileCode" => $this->apidata["profilecode"],
                    "ExamLanguage" => $this->apidata["examlanguage"],
                    "ExamDateTime" => $this->apidata["examdatetime"],
                    "PurchaseDateTime" => $this->apidata['purchasedatetime'],
                    "CreatedByUserName" => $this->apidata["createdbyusername"],
                    "BillNumber" => $this->apidata["billnumber"],
                    "PaymentRefId" => $this->apidata["paymentrefid"],
                    "PayementTypes" => $this->apidata["payementtypes"],
                    "UserOrganization" => $this->apidata["userorganization"],
                    "ReservationId" => (int) $this->apidata['hallscheduleid'],
                    "TransactionTypes" => 1   //     Register = 1
                ];
                $curl->setHeader(array('Accept: application/json', 'Expect:'));
                $curl_post_data = json_encode($params); 
                $response = $curl->post($this->hosturl.$accounturl, $curl_post_data);
                $response = json_decode($response);
                if(empty($response) || COUNT($response->messages) <=0  || $response->success){
                    $this->apidata['errormessage'] = null;
                    $this->apidata['status'] = "1";
                    $this->add_update_fast_service();
                    return $response;
                } else {
                   foreach($response->messages as $error){
                        $errors[] = $error->message;
                   }
                    $error = implode(',',$errors);
                    $this->apidata['errormessage'] = $error;
                    $this->apidata['status'] = "-1";
                    $this->add_update_fast_service();
                    return $response;
                }
            } else {
                $response->messages = get_string('invalid_api_details','local_exams');
                return $response;                
            }            
        } else {
            $response->messages = get_string('invalid_api_details','local_exams');
            return $response;
        }
        
        return false;
    }

    public function hall_availability() {
        if($this->fasttoken){

            $replacestatus = (new \local_exams\local\exams)->access_fast_service('replacefast');
            if ($replacestatus) {
                $replacehallavailability = (new \local_exams\local\exams)->access_fast_service('replacehallavailability');
                if ($replacehallavailability) {
                    $url = $replacehallavailability;
                }
            } else {
                $url = '/api/MobileExam/LMSValidateBeforeRegistration';
            }

            if ($url) {
                $curl = new curl();
                $accounturl = get_config('local_exams', 'fastapicccounturl');
                $curl->setHeader(array('Content-type: application/json'));
                $curl->setHeader('Authorization: Bearer '.$this->fasttoken);
                $params = [
                    "RegistrationDate" => null, //$this->apidata['purchasedatetime'],
                    "UserName" => $this->apidata["username"],
                    "CenterCode" => $this->apidata["centercode"],
                    "ExamCode" => $this->apidata["examcode"],
                    "ProfileCode" => $this->apidata["profilecode"],
                    "ExamLanguage" => $this->apidata["examlanguage"],
                    "ExamDateTime" => $this->apidata["examdatetime"],
                    "PurchaseDateTime" => $this->apidata['purchasedatetime'],
                    "CreatedByUserName" => $this->apidata["username"],
                    "BillNumber" => $this->apidata["billnumber"],
                    "PaymentRefId" => $this->apidata["paymentrefid"],
                    "PayementTypes" => $this->apidata["payementtypes"], 
                    "UserOrganization" => $this->apidata["userorganization"],
                    "ReservationId" => (int) $this->apidata['hallscheduleid'],
                    "TransactionTypes" => 1   // Exam Register = 1
                ];
                $curl->setHeader(array('Accept: application/json', 'Expect:'));
                $curl_post_data = json_encode($params); 
                $response = $curl->post($this->hosturl.$url, $curl_post_data);
                $response = json_decode($response);
                
                if(empty($response) || COUNT($response->messages) <=0 || $response->success){
                    $this->apidata['errormessage'] = null;
                    $this->apidata['status'] = "1";
                    $this->add_update_fast_service();
                    $response->messages = json_encode($response->messages);
                 
                    return $response;
                } else {
                   foreach($response->messages as $error){
                        $errors[] = $error->message;
                   }
                    $error = implode(',',$errors);
                    $this->apidata['errormessage'] = $error;
                    $this->apidata['status'] = "-1";
                    $this->add_update_fast_service();
                    $response->messages =  $error ;
                 
                    return $response;
                }    
            } else {
                $response->messages = get_string('invalid_api_details','local_exams');
                return $response;                
            }
        } else {
            $response->messages = get_string('invalid_api_details','local_exams');
            return $response;
        }
        return false;
    }

    public function validate_reschedule() {
      
        if($this->fasttoken){

            $replacestatus = (new \local_exams\local\exams)->access_fast_service('replacefast');
            if ($replacestatus) {
                $replacerescheduleservice = (new \local_exams\local\exams)->access_fast_service('replacerescheduleservice');
                if ($replacerescheduleservice) {
                    $url = $replacerescheduleservice;
                }
            } else {
                $url = '/api/MobileExam/LMSValidateBeforeReschedule';
            }

            if ($url) {
                $curl = new curl();
                $accounturl = get_config('local_exams', 'fastapicccounturl');
                $curl->setHeader(array('Content-type: application/json'));
                $curl->setHeader('Authorization: Bearer '.$this->fasttoken);
    
                $params = [
                    "RegistrationDate" => null, //$this->apidata['purchasedatetime'],
                    "UserName" => $this->apidata["username"],
                    "CenterCode" => $this->apidata["centercode"],
                    "ExamCode" => $this->apidata["examcode"],
                    "ProfileCode" => $this->apidata["profilecode"],
                    "ExamLanguage" => $this->apidata["examlanguage"],
                    "ExamDateTime" => $this->apidata["examdatetime"],
                    "PurchaseDateTime" => $this->apidata['purchasedatetime'],
                    "CreatedByUserName" => $this->apidata["createdbyusername"],
                    "ReservationId" =>(int) $this->apidata['hallscheduleid'],
                    "BillNumber" => $this->apidata["billnumber"],
                    "PaymentRefId" => $this->apidata["paymentrefid"],
                    "PayementTypes" => $this->apidata["payementtypes"],
                    "UserOrganization" => $this->apidata["userorganization"],
                    "TransactionTypes" => 3,  //Rescdule = 3
                    "OldExamDateTime"=> $this->apidata['oldexamdatetime'], // with old datetime
                    "OldCenterCode"=>$this->apidata['oldcentercode'], // with old CenterCode
                       
                ];
                $curl->setHeader(array('Accept: application/json', 'Expect:'));
                $curl_post_data = json_encode($params); 
                $response = $curl->post($this->hosturl.$url, $curl_post_data);
                $response = json_decode($response);
                if(empty($response) || COUNT($response->messages) <=0 || $response->success){
                    $this->apidata['errormessage'] = null;
                    $this->apidata['status'] = "1";
                    $this->add_update_fast_service();
                   // return $response;
                } else {
                   foreach($response->messages as $error){
                        $errors[] = $error->message;
                   }
                    $error = implode(',',$errors);
                    $this->apidata['errormessage'] = $error;
                    $this->apidata['status'] = "-1";
                    $this->add_update_fast_service();
                   // return false;
                }
    
                return $response;
            } else {
                $response->messages = get_string('invalid_api_details','local_exams');
                return $response;
            }            
        } else {
            $response->messages = get_string('invalid_api_details','local_exams');
            return $response;
        }
        return false;
    }

    public function call_reschedule() {
      
        if($this->fasttoken){

            $replacestatus = (new \local_exams\local\exams)->access_fast_service('replacefast');
            if ($replacestatus) {
                $replacerescheduleservice = (new \local_exams\local\exams)->access_fast_service('replacerescheduleservice');
                if ($replacerescheduleservice) {
                    $url = $replacerescheduleservice;
                }
            } else {
                $url = '/api/MobileExam/ExamRegistration';
            }

            if ($url) {
                $curl = new curl();
                $accounturl = get_config('local_exams', 'fastapicccounturl');
                $curl->setHeader(array('Content-type: application/json'));
                $curl->setHeader('Authorization: Bearer '.$this->fasttoken);
    
                $params = [
                    "RegistrationDate" =>  null,//$this->apidata['purchasedatetime'],
                    "UserName" => $this->apidata["username"],
                    "CenterCode" => $this->apidata["centercode"],
                    "ExamCode" => $this->apidata["examcode"],
                    "ProfileCode" => $this->apidata["profilecode"],
                    "ExamLanguage" => $this->apidata["examlanguage"],
                    "CreatedByUserName" => $this->apidata["createdbyusername"],
                    "BillNumber" => $this->apidata["billnumber"],
                    "PaymentRefId" => $this->apidata["paymentrefid"],
                    "PayementTypes" => $this->apidata["payementtypes"],
                    "ExamDateTime" => $this->apidata["examdatetime"],
                    "PurchaseDateTime" => $this->apidata['purchasedatetime'],
                    "UserOrganization" =>$this->apidata["userorganization"],
                    "ReservationId" => (int) $this->apidata['hallscheduleid'],
                    "TransactionTypes" => 3, //  Rescdule = 3
                    "OldExamDateTime"=> $this->apidata['oldexamdatetime'],  //Case Rescdule fill it with old datetime
                    "OldCenterCode"=>$this->apidata['oldcentercode'], // old CenterCode
                       
                ];
    
                $curl->setHeader(array('Accept: application/json', 'Expect:'));
                $curl_post_data = json_encode($params); 
                $response = $curl->post($this->hosturl.$url, $curl_post_data);
                $response = json_decode($response);
    
                if(empty($response) || COUNT($response->messages) <=0 || $response->success){
                    $this->apidata['errormessage'] = null;
                    $this->apidata['status'] = "1";
                    $this->add_update_fast_service();
                    //return $response;
                } else {
                   foreach($response->messages as $error){
                        $errors[] = $error->message;
                   }
                    $error = implode(',',$errors);
                    $this->apidata['errormessage'] = $error;
                    $this->apidata['status'] = "-1";
                    $this->add_update_fast_service();
                    //return false;
                }
    
                return $response;
            } else {
                $response->messages = get_string('invalid_api_details','local_exams');
                return $response;
            }            
        } else {
            $response->messages = get_string('invalid_api_details','local_exams');
            return $response;
        }
        return false;
    }

    public function validate_cancel() {      
        if($this->fasttoken){
            $replacestatus = (new \local_exams\local\exams)->access_fast_service('replacefast');
            if ($replacestatus) {
                $replacecancelservice = (new \local_exams\local\exams)->access_fast_service('replacecancelservice');
                if ($replacecancelservice) {
                    $url = $replacecancelservice;
                }
            } else {
                $url = '/api/MobileExam/LMSValidateCancellation';
            }

            if ($url) {
                $curl = new curl();
                $accounturl = get_config('local_exams', 'fastapicccounturl');
                $curl->setHeader(array('Content-type: application/json'));
                $curl->setHeader('Authorization: Bearer '.$this->fasttoken);
    
                $params = [
                    "RegistrationDate" =>  null, //$this->apidata['purchasedatetime'],
                    "UserName" => $this->apidata["username"],
                    "CenterCode" => $this->apidata["centercode"],
                    "ExamCode" => $this->apidata["examcode"],
                    "ProfileCode" => $this->apidata["profilecode"],
                    "ExamLanguage" => $this->apidata["examlanguage"],
                    "CreatedByUserName" => $this->apidata["createdbyusername"],
                    "BillNumber" => $this->apidata["billnumber"],
                    "PaymentRefId" => $this->apidata["paymentrefid"],
                    "PayementTypes" => $this->apidata["payementtypes"],
                    "ExamDateTime" => $this->apidata["examdatetime"],
                    "PurchaseDateTime" => $this->apidata['purchasedatetime'],
                    "UserOrganization" => $this->apidata["userorganization"],
                    "ReservationId" =>(int) $this->apidata['hallscheduleid'],
                    "TransactionTypes" =>2     //  2 Cancel
                    
                ];
                $curl->setHeader(array('Accept: application/json', 'Expect:'));
                $curl_post_data = json_encode($params); 
                $response = $curl->post($this->hosturl.$url, $curl_post_data);
                $response = json_decode($response);
    
                if(empty($response) || COUNT($response->messages) <=0 || $response->success){
                    $this->apidata['errormessage'] = null;
                    $this->apidata['status'] = "1";
                    $this->add_update_fast_service();
                    //return $response;
                } else {
                    foreach($response->messages as $error){
                        $errors[] = $error->message;
                    }
                    $error = implode(',',$errors);
                    $this->apidata['errormessage'] = $error;
                    $this->apidata['status'] = "-1";
                    $this->add_update_fast_service();
                    //return false;
                }
                return $response;
            } else {
                $response->messages = get_string('invalid_api_details','local_exams');
                return $response;
            }
        } else {
            $response->messages = get_string('invalid_api_details','local_exams');
            return $response;
        }
        return false;
    }

    public function call_cancel() {
      
        if($this->fasttoken){
            $replacestatus = (new \local_exams\local\exams)->access_fast_service('replacefast');
            if ($replacestatus) {
                $replacecancelservice = (new \local_exams\local\exams)->access_fast_service('replacecancelservice');
                if ($replacecancelservice) {
                    $url = $replacecancelservice;
                }
            } else {
                $url = '/api/MobileExam/LMSRefund';
            }

            if ($url) {
                $curl = new curl();
                $accounturl = get_config('local_exams', 'fastapicccounturl');
                $curl->setHeader(array('Content-type: application/json'));
                $curl->setHeader('Authorization: Bearer '.$this->fasttoken);
    
                $params = [
                    "RegistrationDate" => null, //$this->apidata['purchasedatetime'],
                    "UserName" => $this->apidata["username"],
                    "CenterCode" => $this->apidata["centercode"],
                    "ExamCode" => $this->apidata["examcode"],
                    "ProfileCode" => $this->apidata["profilecode"],
                    "ExamLanguage" => $this->apidata["examlanguage"],
                    "CreatedByUserName" => $this->apidata["createdbyusername"],
                    "BillNumber" => $this->apidata["billnumber"],
                    "PaymentRefId" => $this->apidata["paymentrefid"],
                    "PayementTypes" => $this->apidata["payementtypes"],
                    "ExamDateTime" => $this->apidata["examdatetime"],
                    "PurchaseDateTime" => $this->apidata['purchasedatetime'],
                    "UserOrganization" => $this->apidata["userorganization"],
                    "ReservationId" =>(int) $this->apidata['hallscheduleid'],
                    "TransactionTypes" =>2     //  2 Cancel
                    
                       
                ];
    
                $curl->setHeader(array('Accept: application/json', 'Expect:'));
                $curl_post_data = json_encode($params); 
                $response = $curl->post($this->hosturl.$url, $curl_post_data);
                $response = json_decode($response);
    
               
                if(empty($response) || COUNT($response->messages) <=0 || $response->success){
                    $this->apidata['errormessage'] = null;
                    $this->apidata['status'] = "1";
                    $this->add_update_fast_service();
                    //return $response;
                } else {
                   foreach($response->messages as $error){
                        $errors[] = $error->message;
                   }
                    $error = implode(',',$errors);
                    $this->apidata['errormessage'] = $error;
                    $this->apidata['status'] = "-1";
                    $this->add_update_fast_service();
                    //return false;
                }
                
                return $response;
            } else {
                $response->messages = get_string('invalid_api_details','local_exams');
                return $response;
            }
        } else {
            $response->messages = get_string('invalid_api_details','local_exams');
            return $response;
        }
        return false;
    }
    public function validate_replacment() {
      
        if($this->fasttoken){
            $replacestatus = (new \local_exams\local\exams)->access_fast_service('replacefast');
            if ($replacestatus) {
                $replacereplaceservice = (new \local_exams\local\exams)->access_fast_service('replacereplaceservice');
                if ($replacereplaceservice) {
                    $url = $replacereplaceservice;
                }
            } else {
                $url = '/api/MobileExam/LMSValidateReplacement';
            }

            if ($url) {
                $curl = new curl();
                $accounturl = get_config('local_exams', 'fastapicccounturl');
                $curl->setHeader(array('Content-type: application/json'));
                $curl->setHeader('Authorization: Bearer '.$this->fasttoken);
    
                $params = [
                    "RegistrationDate" =>  null, //$this->apidata['purchasedatetime'],
                    "NewUserName" => $this->apidata["username"],
                    "OldUserName" => $this->apidata["oldusername"],
                    "CenterCode" => $this->apidata["centercode"],
                    "ExamCode" => $this->apidata["examcode"],
                    "ProfileCode" => $this->apidata["profilecode"],
                    "ExamLanguage" => $this->apidata["examlanguage"],
                    "CreatedByUserName" => $this->apidata["createdbyusername"],
                    "BillNumber" => $this->apidata["billnumber"],
                    "PaymentRefId" => $this->apidata["paymentrefid"],
                    "PayementTypes" => $this->apidata["payementtypes"],
                    "ExamDateTime" => $this->apidata["examdatetime"],
                    "PurchaseDateTime" => $this->apidata['purchasedatetime'],
                    "UserOrganization" => $this->apidata["userorganization"],
                    "ReservationId" =>(int) $this->apidata['hallscheduleid'],
                    "TransactionTypes" =>4     //  4 Replacement
                    
                ];
                $curl->setHeader(array('Accept: application/json', 'Expect:'));
                $curl_post_data = json_encode($params); 
                $response = $curl->post($this->hosturl.$url, $curl_post_data);
                $response = json_decode($response);
    
                if(empty($response) || COUNT($response->messages) <=0 || $response->success){
                    $this->apidata['errormessage'] = null;
                    $this->apidata['status'] = "1";
                    $this->add_update_fast_service();
                    //return $response;
                } else {
                    foreach($response->messages as $error){
                        $errors[] = $error->message;
                    }
                    $error = implode(',',$errors);
                    $this->apidata['errormessage'] = $error;
                    $this->apidata['status'] = "-1";
                    $this->add_update_fast_service();
                    //return false;
                }
                return $response;
            } else {
                $response->messages = get_string('invalid_api_details','local_exams');
                return $response;
            }
        } else {
            $response->messages = get_string('invalid_api_details','local_exams');
            return $response;
        }
        return false;
    }

    public function call_replacment() {
      
        if($this->fasttoken){
            $replacestatus = (new \local_exams\local\exams)->access_fast_service('replacefast');
            if ($replacestatus) {
                $replacereplaceservice = (new \local_exams\local\exams)->access_fast_service('replacereplaceservice');
                if ($replacereplaceservice) {
                    $url = $replacereplaceservice;
                }
            } else {
                $url = '/api/MobileExam/LMSReplacement';
            }

            if ($url) {
                $curl = new curl();
                $accounturl = get_config('local_exams', 'fastapicccounturl');
                $curl->setHeader(array('Content-type: application/json'));
                $curl->setHeader('Authorization: Bearer '.$this->fasttoken);
    
                $params = [
                    "RegistrationDate" =>  null, //$this->apidata['purchasedatetime'],
                    "NewUserName" => $this->apidata["username"],
                    "OldUserName" => $this->apidata["oldusername"],
                    "CenterCode" => $this->apidata["centercode"],
                    "ExamCode" => $this->apidata["examcode"],
                    "ProfileCode" => $this->apidata["profilecode"],
                    "ExamLanguage" => $this->apidata["examlanguage"],
                    "CreatedByUserName" => $this->apidata["createdbyusername"],
                    "BillNumber" => $this->apidata["billnumber"],
                    "PaymentRefId" => $this->apidata["paymentrefid"],
                    "PayementTypes" => $this->apidata["payementtypes"],
                    "ExamDateTime" => $this->apidata["examdatetime"],
                    "PurchaseDateTime" => $this->apidata['purchasedatetime'],
                    "UserOrganization" => $this->apidata["userorganization"],
                    "ReservationId" =>(int) $this->apidata['hallscheduleid'],
                    "TransactionTypes" =>4     //  4 Replacement
                       
                ];
                $curl->setHeader(array('Accept: application/json', 'Expect:'));
                $curl_post_data = json_encode($params); 
    
               
                $response = $curl->post($this->hosturl.$url, $curl_post_data);
                $response = json_decode($response);
    
               
                if(empty($response) || COUNT($response->messages) <=0 || $response->success){
                    $this->apidata['errormessage'] = null;
                    $this->apidata['status'] = "1";
                    $this->add_update_fast_service();
                    //return $response;
                } else {
                   foreach($response->messages as $error){
                        $errors[] = $error->message;
                   }
                    $error = implode(',',$errors);
                    $this->apidata['errormessage'] = $error;
                    $this->apidata['status'] = "-1";
                    $this->add_update_fast_service();
                    //return false;
                }
                
                return $response;
            } else {
                $response->messages = get_string('invalid_api_details','local_exams');
                return $response;
            }
        } else {
            $response->messages = get_string('invalid_api_details','local_exams');
            return $response;
        }
        return false;
    }


    public function add_update_fast_service()
    {
        global $DB,$USER;
        $this->apidata['reservationid'] = $this->apidata['hallscheduleid'] ? $this->apidata['hallscheduleid'] : 0;
        if($this->apidata['id'] > 0) {
            $this->apidata->id = $this->apidata['id'];
            $this->apidata->timemodified=time();
            $this->apidata->status = $this->apidata['status'];
            $this->apidata['usermodified'] = $USER->id;
            $this->apidata['realuser'] = ($USER->realuser) ? $USER->realuser :0;
            $this->apidata['timemodified'] =time();
            $this->apidata['purchasedatetime'] = userdate(time(),'%Y-%m-%d %H:%M:%S');
            $insertedid = $DB->update_record('local_fast_examenrol', $this->apidata);
        }else{
            $this->apidata->status = 0;
            $this->apidata['usercreated'] = $USER->id;
            $this->apidata['realuser'] = ($USER->realuser) ? $USER->realuser :0;
            $this->apidata['timecreated'] =time();
            $this->apidata['userorganization'] = $this->apidata['userorganization'] ? $this->apidata['userorganization'] : 0;
            try{
                $insertedid = $DB->insert_record('local_fast_examenrol', $this->apidata);
            }catch(dml_exception $e){
                print_error($e);
            }
        }
        return $insertedid;
    }
}
