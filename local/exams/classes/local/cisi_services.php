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
 * CISI Integration.
 *
 * @package local_exams
 * @author  Ikram Ahmad
 * @license http://www.gnu.org/copyleft/gpl.html GNU Public License
 */

namespace local_exams\local;
require_once "{$CFG->libdir}/filelib.php";
use moodle_exception;
use context_system;
/**
 * CISI service API calls
 */

class cisi_services {
    
    /**
     * This function will authenticate the user against the CISI Authentication
     * 
     */
    public function AuthenticationSigninAuthToken($clientid,$clientsecret,$cisiusername,$cisipassword,$cisiscope) {
        global $DB, $USER;
        $context = context_system::instance();
        $cisiurl = get_config('local_lmsws', 'cisiurl');
        $url = "$cisiurl/cisi.security/api/Authentication/SignIn";
        $curl = new \curl();
        $curl->setHeader(array('Content-type: application/json'));
        
        $params = [
            "username" => $cisiusername,
            "password" => $cisipassword,
            "clientID" => $clientid,
            "scope" => $cisiscope
        ];
        $post = json_encode($params);
        $curl->setHeader(array('accept: application/json-patch+json',
                'Cookie: ApplicationGatewayAffinity=84ea0bdc56575b990eda44d5e19f207b;ApplicationGatewayAffinityCORS=84ea0bdc56575b990eda44d5e19f207b'
            ));

        $curl_post_data = json_encode($params); 
        $response = $curl->post($url, $curl_post_data);
        $response = json_decode($response);
        return $response;

    }
    /**
     * This function will return the access token for the authenticated user
     * @param $authToken: Token received while authenticating the user to CISI server
     * @return $accessToken
     */
    public function AuthenticationTokenAccessToken($authToken, $clientid, $clientsecret) {
        global $DB, $USER;  
        $context = context_system::instance();
        $cisiurl = get_config('local_lmsws', 'cisiurl');
        $url = "$cisiurl/cisi.security/api/Authentication/Token";
        $curl = new \curl();
        $curl->setHeader(array('Content-type: application/json'));
        

        $params = [
            "authToken" => $authToken,
            "clientID" => $clientid,
            "clientSecret" => $clientsecret,
            "codeVerifier" => "",
            "state" => "",
            "grantType" => ""
        ];
        $post = json_encode($params);
        $curl->setHeader(array('Accept: application/json-patch+json'));
        $curl->setHeader(array('accept: application/json-patch+json',
                'Cookie: ApplicationGatewayAffinity=e5386addff9819046a42f99b5c9d97d9; ApplicationGatewayAffinityCORS=e5386addff9819046a42f99b5c9d97d9'
            ));
        $curl_post_data = json_encode($params); 
        $response = $curl->post($url, $curl_post_data);
        $response = json_decode($response);
        
        return $response;
    }
    /**
     * This function will Create a user on CISI server
     * @param $accessToken: Token received while authenticating the user to CISI server
     * @param $email: email of the user to be created on CISI server
     * @param $lastname: lastname of the user to be created on CISI server
     * @param $firstname: firstname of the user to be created on CISI server
     * @param $dateofbirth: dateofbirth of the user to be created on CISI server
     * @return Return a user object containing its ID and status of user.
     */
    public function cisi_create_user($accessToken,$title, $email,$lastname,$firstname,$dateofbirth) {
        global $DB, $USER;
        $context = context_system::instance();
        $cisiurl = get_config('local_lmsws', 'cisiurl');
        $url = "$cisiurl/bitmark.webapi/api/person/thirdparty";
        $curl = new \curl();
        $curl->setHeader(array('Content-type: application/json'));
        $params = [
            "title" => $title,
            "firstName" => $firstname,
            "lastName" => $lastname,
            "personTypeID" => "8",
            "email" => $email,
            "comment" => "",
            "dob" => "$dateofbirth"
        ];
        $post = json_encode($params);
        $curl->setHeader(array('Accept: application/json'));
        $curl->setHeader(array(
                "Authorization: Bearer $accessToken",
                'Cookie: ApplicationGatewayAffinity=e5386addff9819046a42f99b5c9d97d9; ApplicationGatewayAffinityCORS=e5386addff9819046a42f99b5c9d97d9'
            ));

        $curl_post_data = json_encode($params); 
        $response = $curl->post($url, $curl_post_data);

        $responseobj    = json_decode($response);
        if (!$responseobj->success) {
            return $responseobj;
        }
        $id             = $responseobj->results->id;
        $title          = $responseobj->results->title;
        $firstName      = $responseobj->results->firstName;
        $lastName       = $responseobj->results->lastName;
        $email          = $responseobj->results->email;
        $personTypeID   = $responseobj->results->personTypeID;
        $countryID      = $responseobj->results->countryID;
        
        $userid = $DB->get_field('user', 'id', ['email' => $email]);
        
        // Creating user object to create a user account in LMS
        $cisipersoninsert = new \stdClass();
        $cisipersoninsert->externaluserid        = $id;
        $cisipersoninsert->externalprovidername  = 'cisi';
        $cisipersoninsert->status                = 1;
        $cisipersoninsert->userid                = $userid;
        $cisipersoninsert->createdtime           = time();
        
        $newrecordid = $DB->insert_record('externalprovider_userdetails', $cisipersoninsert);
        
        $responseobj->lms_insertion_record = $cisipersoninsert;
        return $responseobj;
    }
    /**
     * This function will Create a user on CISI server
     * @param $accessToken: Token received while authenticating the user to CISI server
     * @param $email: email of the user to be created on CISI server
     * @param $lastname: lastname of the user to be created on CISI server
     * @param $firstname: firstname of the user to be created on CISI server
     * @param $dateofbirth: dateofbirth of the user to be created on CISI server
     * @param $cisiuserid: Id of the center of Exam.
     * @return Return a user object containing its ID and status of user.
     */
    public function cisi_update_user($accessToken,$cisiuserid, $title, $email,$lastname,$firstname,$dateofbirth){
        global $DB, $USER;  
        $context = context_system::instance();
        $cisiurl = get_config('local_lmsws', 'cisiurl');
        $url = "$cisiurl/bitmark.webapi/api/person/$cisiuserid/thirdparty";
        $curl = new \curl();
        $curl->setHeader(array('Content-type: application/json'));
       
        $params = [
            "title" => $title,
            "firstName" => $firstname,
            "lastName" => $lastname,
            "personTypeID" => "8",
            "email" => $email,
            "comment" => "",
            "dob" => "$dateofbirth"
        ];
        $post = json_encode($params);
        $curl->setHeader(array('accept: application/json',
            "Authorization: Bearer $accessToken",
            'Cookie: ApplicationGatewayAffinity=e5386addff9819046a42f99b5c9d97d9; ApplicationGatewayAffinityCORS=e5386addff9819046a42f99b5c9d97d9'
        ));

        $curl_post_data = json_encode($params); 
        $response = $curl->put($url, $curl_post_data);
        $updatedresponce = json_decode($response);
        return $updatedresponce;
    }
    /**
     * CISI Exam mapping
     * @param $accessToken
     * @param $cisiuserid
     * @param $examcode Exam ID
     * @param $hallcode Venue ID of Exam
     * @param $examdate Date of Exam
     * @return Array data array.
     */
    
    public function cisi_exam_mapping($accessToken,$cisiuserid,$examcode,$hallcode,$examdate){
        global $DB, $USER;
        $context = context_system::instance();
        $cisiurl = get_config('local_lmsws', 'cisiurl');
        $url = "$cisiurl/bitmark.webapi/api/externalexambooking";
        $curl = new \curl();
        $curl->setHeader(array('Content-type: application/json'));

        $params = [
            "ExamID" => $examcode,
            "Date" => $examdate,
            "VenueID" => $hallcode,
            "PersonID" => $cisiuserid
        ];
        $post = json_encode($params);
        $curl->setHeader(array('accept: application/json-patch+json',
                "Authorization: Bearer $accessToken",
                'Cookie: ApplicationGatewayAffinity=e5386addff9819046a42f99b5c9d97d9; ApplicationGatewayAffinityCORS=e5386addff9819046a42f99b5c9d97d9'
            ));
        $curl_post_data = json_encode($params); 
        $response = $curl->post($url, $curl_post_data);
        return  json_decode($response);
    }
}
