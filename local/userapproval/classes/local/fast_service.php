<?php
namespace local_userapproval\local;
use curl;
use dml_exception;
use Exception;

/**
 * FAST service call to create user
 */
class fast_service
{
    private $prepareddata = array();
    private $hosturl;
    private $url = '/api/MobileAccount/Register?isArabic=false';

    function __construct()
    {
        //
    }

    private function prepare($data) {
      if($data->idtype == 3) {
        $typeid = 1;
      } else if($data->idtype == 2){
        $typeid = 3;
      }else{
        $typeid = 2;
      }
      if(!isset($data->licensekey)){
        $data->licensekey = '';
      }
      if(!isset($data->orgcode)){
        $data->orgcode = '';
      }

     $gender = ($data->gender == 1) ? true : false;
     $idtype['2'] = get_string('passport','local_userapproval');
     $idtype['3'] = get_string('saudiid','local_userapproval');
     $idtype['4'] = get_string('residentialid','local_userapproval');

      $this->prepareddata=  ["FirstNameAr"=> $data->firstnamearabic,
                            "FirstNameEn"=>$data->firstname,
                            "MiddleNameAr"=> !empty($data->middlenamearabic) ? $data->middlenamearabic : '.',
                            "MiddleNameEn"=>!empty($data->middlenameen) ? $data->middlenameen : '.',
                            "ThirdNameAr"=>!empty($data->thirdnamearabic) ? $data->thirdnamearabic : '.',
                            "ThirdNameEn"=>!empty($data->thirdnameen) ? $data->thirdnameen : '.',
                            "LastNameAr"=>$data->lastnamearabic,
                            "LastNameEn"=>$data->lastname,
                            "NationalityCountryId"=>$data->nationalitycountryid,
                            "NationalityType"=>$typeid,
                            "InsideKSA"=>true,
                            "PhoneNumber"=>'+'.$data->country_code.$data->phone1,
                            "AddressCountryId"=>$data->nationalitycountryid,
                            "PreferredUiLanguage"=>$data->lang,
                            "Gender"=>$gender, 
                            "Email"=>$data->email, 
                            "EmailConfirm"=>$data->email,
                            "Password"=>$data->confirm_password,
                            "ConfirmPassword"=>$data->confirm_password,
                            "RoleCode"=>"4",
                            "RoleName"=>"Individual",
                            "UserName"=>$data->username,
                            "SSOGuid"=>"",
                            "SSOIdNumber"=>"",
                            "SSODes"=>"Individual",
                            "SSOType"=>"4",
                            "GulfIdNumber"=>null,
                            "PassportNumber"=>"",
                            "OrganizationCommercialRegister"=>$data->licensekey,
                            "OrganizationShortCode"=>$data->orgcode];
      if(preg_match('/^1/', $data->id_number)){
        $this->prepareddata['IdNumber'] = $data->id_number;
        $this->prepareddata['ResidencyNumber'] = '';
      }else{
        $this->prepareddata['ResidencyNumber'] = $data->id_number;
        $this->prepareddata['IdNumber'] = '';
      }

    }

    public function register_user($data) {
        global $DB;
        $this->hosturl = get_config('local_userapproval', 'userlogapiauthenticateurl');
        $replacestatus = (new \local_exams\local\exams)->access_fast_service('replacefast');
        $currentlang= current_language();
        if ($replacestatus) {
            $replaceuserregistrationurl = (new \local_exams\local\exams)->access_fast_service('replaceuserregistration');
            if ($replacestatus) {
                $url = $replaceuserregistrationurl;
            }
        } else {
            if( $currentlang == 'ar'){
                $url = '/api/MobileAccount/Register?isArabic=true';
            }else{
                $url = $this->url;
            }
        }
        if ($url) {
            $curl = new curl();
            $curl->setHeader(array('Content-type: application/json-patch+json'));
            $arr = array();
            array_push($arr, 'Accept: application/json');
            array_push($arr, 'Accept-Language: en-us,en;q=0.5');
    
            curl_setopt($curl, CURLOPT_HTTPHEADER, $arr);
            $this->prepare($data);
            $curl_post_data = json_encode($this->prepareddata);
    
            $response = $curl->post($this->hosturl.$url, $curl_post_data);
            $response = json_decode($response);
            if(!empty($response) || COUNT($response->messages) > 0  ||  COUNT($response->errors) > 0  || !$response->success){
              $e = '';
              if(COUNT($response->errors) > 0 ){
                $error = json_encode($response->errors, JSON_UNESCAPED_UNICODE);
                $e=$error;  
              } 
              if(COUNT($response->messages) > 0 ){
                $messages = json_encode($response->messages, JSON_UNESCAPED_UNICODE);
                $e=$messages;  
              } 
              $data->resperror = $e;
              $data->status = "-1";
              $this->add_update_service($data);    
            }else{
              $data->status = "1";
              $this->add_update_service($data);
            }
            return $response;
        }
    }

  public function add_update_service($data){
    global $DB, $USER;
    if($data->idtype == 3) {
          $typeid = 1;
    } else if($data->idtype == 2){
          $typeid = 3;
    }else{
      $typeid = 2;
    }
    if(!isset($data->licensekey)){
      $data->licensekey = '';
    }
    if(!isset($data->orgcode)){
      $data->orgcode = '';
    }
    $fastuserdata = new \stdClass();
    $fastuserdata->firstnamearabic= $data->firstnamearabic;
    $fastuserdata->firstname =$data->firstname;
    $fastuserdata->middlenamearabic =$data->middlenamearabic;
    $fastuserdata->middlenameen =$data->middlenameen;
    $fastuserdata->thirdnamearabic =$data->thirdnamearabic;
    $fastuserdata->thirdnameen =$data->thirdnameen;
    $fastuserdata->lastnamearabic =$data->lastnamearabic ;
    $fastuserdata->lastname =$data->lastname ;
    $fastuserdata->nationalitycountryid =$data->nationalitycountryid ;
    $fastuserdata->nationalitytype =$typeid ;
    $fastuserdata->insideksa =true ;
    $fastuserdata->phonenumber ='+'.$data->country_code.$data->phone1 ;
    $fastuserdata->addresscountryid =$data->nationalitycountryid ;
    $fastuserdata->lang =$data->lang ;
    $fastuserdata->gender =$data->gender ; 
    $fastuserdata->email =$data->email ; 
    $fastuserdata->confirm_email =$data->email ;
    $fastuserdata->password =$data->password;
    $fastuserdata->rolecode ="4" ;
    $fastuserdata->rolename ="Individual" ;
    $fastuserdata->username =$data->username ;
    $fastuserdata->ssoguid ="" ;
    $fastuserdata->ssoidnumber ="" ;
    $fastuserdata->ssodes ="Individual" ;
    $fastuserdata->ssotype ="4" ;
    $fastuserdata->gulfidnumber =null ;
    $fastuserdata->passportnumber = ($data->id_type == 2) ? $data->id_number : "" ;
    $fastuserdata->organizationcommercialregister =$data->licensekey ;
    $fastuserdata->organizationshortcode =$data->orgcode;
    $fastuserdata->errormessage =$data->resperror;
     if(preg_match('/^1/', $data->id_number)){
       $fastuserdata->id_number  = $data->id_number;
       $fastuserdata->residencynumber  = '';
    }else{
       $fastuserdata->id_number  = $data->id_number;
       $fastuserdata->residencynumber  = '';
    }      
    $fastuserdata->status  = $data->status;
    try{
      if($data->id > 0){   
        $fastuserdata->id  = $data->id;
        $fastuserdata->usermodified  = $USER->id;
        $fastuserdata->timemodified  = time();
        $insertedid = $DB->update_record('local_fast_user', $fastuserdata);
      }else{
        $fastuserdata->usercreated  = $USER->id;
        $fastuserdata->timecreated  = time();
        $insertedid = $DB->insert_record('local_fast_user', $fastuserdata);
      }     
    }catch(dml_exception $e){
      print_error($e);
    }
    return $insertedid;
  }

    public function access_resisteruser_service() {
        $endata = get_config('local_exams', 'fastsettings');
        $decode = json_decode($endata);
        
        if ($decode->userregistration || $decode->replacefast) {
            return true;  
        }
        return false;
    }
}
