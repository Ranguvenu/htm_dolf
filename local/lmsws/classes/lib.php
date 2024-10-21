<?php

// This file is part of Moodle - http://moodle.org/
//
// Moodle is free software: you can redistribute it and/or localify
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
 *
 * @package    local_cpd
 * @category   external
 * @copyright  eAbyas <www.eabyas.in>
 */

namespace local_lmsws;

use context_system;
use moodle_url;
use stdClass;

defined('MOODLE_INTERNAL') || die;
class lib
{
    public function authentication_signin()
    {
        global $DB, $USER;

        
    $cisiurl = get_config('local_lmsws', 'cisiurl');
        $clientid = get_config('local_lmsws', 'clientid');
        $clientsecret = get_config('local_lmsws', 'clientsecret');
        $cisiusername = get_config('local_lmsws', 'cisiusername');
        $cisipassword = get_config('local_lmsws', 'cisipassword');
      $cisiscope = get_config('local_lmsws', 'cisiscope');
$curl = curl_init();

curl_setopt_array($curl, array(
  CURLOPT_URL => "$cisiurl/cisi.security/api/Authentication/SignIn",
  CURLOPT_RETURNTRANSFER => true,
  CURLOPT_ENCODING => '',
  CURLOPT_MAXREDIRS => 10,
  CURLOPT_TIMEOUT => 0,
  CURLOPT_FOLLOWLOCATION => true,
  CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
  CURLOPT_CUSTOMREQUEST => 'POST',
  CURLOPT_POSTFIELDS =>'{
    "username":"'.$cisiusername.'",
    "password":"'.$cisipassword.'",
    "clientID":"'.$clientid.'",
    "scope":"'.$cisiscope.'"
}',
  CURLOPT_HTTPHEADER => array(
    'accept: application/json',
    'Content-Type: application/json-patch+json',
    'Cookie: ApplicationGatewayAffinity=84ea0bdc56575b990eda44d5e19f207b; ApplicationGatewayAffinityCORS=84ea0bdc56575b990eda44d5e19f207b'
  ),
));

$response = curl_exec($curl);

curl_close($curl);

      // Dump all data of the Object
     
      $result = array_values(json_decode($response, true));
     $authToken=($result[2][authToken]);
     

curl_close($curl);

return $authToken;
    }

    public function authentication_token($authsignin)
    {
        global $DB, $USER;
           $curl = curl_init();
        $cisiurl = get_config('local_lmsws', 'cisiurl');
        $clientid = get_config('local_lmsws', 'clientid');
        $clientsecret = get_config('local_lmsws', 'clientsecret');
        $cisiusername = get_config('local_lmsws', 'cisiusername');
        $cisipassword = get_config('local_lmsws', 'cisipassword');
        $cisiscope = get_config('local_lmsws', 'cisiscope');
        $authsignin;       
        $curl = curl_init();
curl_setopt_array($curl, array(
  CURLOPT_URL => "$cisiurl/cisi.security/api/Authentication/Token",
  CURLOPT_RETURNTRANSFER => true,
  CURLOPT_ENCODING => '',
  CURLOPT_MAXREDIRS => 10,
  CURLOPT_TIMEOUT => 0,
  CURLOPT_FOLLOWLOCATION => true,
  CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
  CURLOPT_CUSTOMREQUEST => 'POST',
  CURLOPT_POSTFIELDS =>'{
    "authToken":"'.$authsignin.'",
    "clientID":"'.$clientid.'",
    "clientSecret":"'.$clientsecret.'",
    "codeVerifier":"",
    "state":"",
    "grantType":""
}',
  CURLOPT_HTTPHEADER => array(
    'accept: application/json',
    'Content-Type: application/json-patch+json',
    'Cookie: ApplicationGatewayAffinity=e5386addff9819046a42f99b5c9d97d9; ApplicationGatewayAffinityCORS=e5386addff9819046a42f99b5c9d97d9'
  ),
));

$response = curl_exec($curl);

curl_close($curl);
$result = array_values(json_decode($response, true));
$accessToken=($result[2][accessToken]);

return $accessToken;
    }

    public function createperson($accessToken,$email,$lastname,$firstname,$dateofbirth){
      global $DB, $USER;    
      $cisiurl = get_config('local_lmsws', 'cisiurl');
      $clientid = get_config('local_lmsws', 'clientid');
      $clientsecret = get_config('local_lmsws', 'clientsecret');
        $cisiusername = get_config('local_lmsws', 'cisiusername');
        $cisipassword = get_config('local_lmsws', 'cisipassword');
        $cisiscope = get_config('local_lmsws', 'cisiscope');

      $curl = curl_init();
      curl_setopt_array($curl, array(
        CURLOPT_URL => "$cisiurl/bitmark.webapi/api/person/thirdparty",
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_ENCODING => '',
        CURLOPT_MAXREDIRS => 10,
        CURLOPT_TIMEOUT => 0,
        CURLOPT_FOLLOWLOCATION => true,
        CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
        CURLOPT_CUSTOMREQUEST => 'POST',
      
        CURLOPT_POSTFIELDS =>'{
      "title":"MR.'.$firstname.'",
      "firstName":"'.$firstname.'",
      "lastName":"'.$lastname.'",
      "personTypeID":8,
      "email":"'.$email.'",
      "comment":"",
      "dob":"'.$dateofbirth.'"
      }',
        CURLOPT_HTTPHEADER => array(
          'accept: application/json',
          'Content-Type: application/json-patch+json',
          "Authorization: Bearer $accessToken",
          'Cookie: ApplicationGatewayAffinity=e5386addff9819046a42f99b5c9d97d9; ApplicationGatewayAffinityCORS=e5386addff9819046a42f99b5c9d97d9'
        ),
      ));
       $response = curl_exec($curl);
   
      curl_close($curl);
$result = array_values(json_decode($response, true));
$id=($result[2][id]);
$title=($result[2][title]);
$firstName=($result[2][firstName]);
$lastName=($result[2][lastName]);
$email=($result[2][email]);

$personTypeID=($result[2][personTypeID]);
$countryID=($result[2][countryID]);
$cisipersoninsert = new stdClass();
$cisipersoninsert->externaluserid = $id;
$cisipersoninsert->externalprovidername  = 'cisi';
$cisipersoninsert->status  = 1;
$userid = $DB->get_field('user', 'id', ['email' => $email]);
$cisipersoninsert->userid  = $userid;
$cisipersoninsert->createdtime  = time();
$cisipersoninsert->updatedtime   = time();
$extraInfo=($result[2][0][extraInfo]);
if($extraInfo==''){
$cisiid = $DB->get_field('externalprovider_userdetails', 'id', ['externaluserid' => $id]);
if($cisiid==''){
$DB->insert_record('externalprovider_userdetails', $cisipersoninsert);
}
}

return $response;


    }


    public function updateperson($accessToken,$email,$lastname,$firstname,$cisiuserid,$dateofbirth){



      global $DB, $USER;  

 $cisiurl = get_config('local_lmsws', 'cisiurl');
$curl = curl_init();

curl_setopt_array($curl, array(
  CURLOPT_URL => "$cisiurl/bitmark.webapi/api/person/'.$cisiuserid.'/thirdparty",
  CURLOPT_RETURNTRANSFER => true,
  CURLOPT_ENCODING => '',
  CURLOPT_MAXREDIRS => 10,
  CURLOPT_TIMEOUT => 0,
  CURLOPT_FOLLOWLOCATION => true,
  CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
  CURLOPT_CUSTOMREQUEST => 'PUT',
  CURLOPT_POSTFIELDS =>'{
"id":"'.$cisiuserid.'",
"title":"MR.'.$firstname.'",
      "firstName":"'.$firstname.'",
      "lastName":"'.$lastname.'",
      "personTypeID":8,
      "email":"'.$email.'",
"comment":"",
"dob":"'.$dateofbirth.'",
}',
  CURLOPT_HTTPHEADER => array(
    'accept: application/json',
    'Content-Type: application/json-patch+json',
   "Authorization: Bearer $accessToken",
    'Cookie: ApplicationGatewayAffinity=e5386addff9819046a42f99b5c9d97d9; ApplicationGatewayAffinityCORS=e5386addff9819046a42f99b5c9d97d9'
  ),
));

$response = curl_exec($curl);

curl_close($curl);
echo $response;

      
    }

 public function cisiexam_mapping($accessToken,$email,$lastname,$firstname,$cisiuserid,$examcode,$hallcode,$examdate){
  global $DB, $USER;  
  $curl = curl_init();
  curl_setopt_array($curl, array(
  CURLOPT_URL => 'https://staging.cisi.org/bitmark.webapi/api/externalexambooking',
  CURLOPT_RETURNTRANSFER => true,
  CURLOPT_ENCODING => '',
  CURLOPT_MAXREDIRS => 10,
  CURLOPT_TIMEOUT => 0,
  CURLOPT_FOLLOWLOCATION => true,
  CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
  CURLOPT_CUSTOMREQUEST => 'POST',
  CURLOPT_POSTFIELDS =>'{
"ExamID":"'.$examcode.'",
"Date":"'.$examdate.'",
"VenueID":"'.$hallcode.'",
"PersonID":"'.$cisiuserid.'"
}',
  CURLOPT_HTTPHEADER => array(
    'accept: application/json',
    'Content-Type: application/json-patch+json',
    "Authorization: Bearer $accessToken",
    'Cookie: ApplicationGatewayAffinity=e5386addff9819046a42f99b5c9d97d9; ApplicationGatewayAffinityCORS=e5386addff9819046a42f99b5c9d97d9'
  ),
));

$response = curl_exec($curl);

curl_close($curl);
echo $response;



      
    }


        public function centerinfo($testCenterId, $userName) {
          global $DB, $USER;
              
        $selectsql = " SELECT us.username as username,ee.userid as userid,us.firstname as firstname ,us.lastname as lastname ,us.email as email,ee.hallid as hallid, ee.id as id,h.code as code, ee.examid as examid,lus.firstnamearabic as firstnamearabic, lus.lastnamearabic as lastnamearabic, ee.id as user_id, us.lang as lang FROM {exam_enrollments} ee JOIN {user} us ON us.id = ee.userid JOIN {local_users} lus ON lus.userid = us.id JOIN {role_assignments} ra ON ra.userid = ee.userid JOIN {role} r ON r.id = ra.roleid JOIN {hall} h ON h.id = ee.hallid WHERE r.shortname='examofficial' and h.code = $testCenterId and ee.examdate IN (CURDATE() + INTERVAL 1 DAY) ";
         $user_list = $DB->get_records_sql($selectsql);


        $userlist = array();
        if($user_list) {
            $count = 0;
            foreach($user_list as $list) {
                $userlist[$count]['accessFailedCount'] = 0;
                $userlist[$count]['concurrencyStamp'] = "20d69690-6af9-46b6-be1e-f901090c5e48";
                $userlist[$count]['dateOfBirth'] = "0001-01-01T00:00:00";
                $userlist[$count]['email'] = $list->email;
                $userlist[$count]['emailConfirmed'] = true;
                $userlist[$count]['firstName'] = $list->firstname;
                $userlist[$count]['firstNameAr'] = $list->firstnamearabic;
                $userlist[$count]['firstNameEn'] = $list->firstname;
                $userlist[$count]['fullName'] = $list->firstname.' '.$list->lastName;
                $userlist[$count]['fullNameAr'] = $list->firstnamearabic.' '.$list->lastnamearabic;
                $userlist[$count]['fullNameEn'] = $list->firstname.' '.$list->lastName;
                $userlist[$count]['isActive'] = true;
                $userlist[$count]['isApproved'] = null;
                $userlist[$count]['isEmployee'] = false;
                $userlist[$count]['jobTitle'] = null;
                $userlist[$count]['lastName'] = $list->lastName;
                $userlist[$count]['lastNameAr'] = $list->lastnamearabic;
                $userlist[$count]['lastNameEn'] = $list->lastName;
                $userlist[$count]['lockoutEnabled'] = true;
                $userlist[$count]['lockoutEnd'] = "2022-10-25T13:55:45.367154+00:00";
                $userlist[$count]['lockoutEndDateUtc'] = null;
                $userlist[$count]['middleName'] = "اختبار";
                $userlist[$count]['middleNameAr'] = "اختبار";
                $userlist[$count]['middleNameEn'] = "Exam";
                $userlist[$count]['mobileNotificationToken'] = null;
                $userlist[$count]['normalizedEmail'] = $list->email;
                $userlist[$count]['normalizedUserName'] = $list->username;
                $userlist[$count]['password'] = null;
                $userlist[$count]['passwordHash'] = "AQAAAAEAACcQAAAAEF+6rNGS6nl70TwptBJ9tHEzJcJHG3MUPezgWDNTMPn3Kb6UUJrUd981ODmoIF7Spg==";
                $userlist[$count]['phoneNumber'] = null;
                $userlist[$count]['phoneNumberConfirmed'] = false;
                $userlist[$count]['preferredCommunicationLanguage'] = $list->lang;
                $userlist[$count]['preferredUiLanguage'] = $list->lang;
                $userlist[$count]['securityStamp'] = "BDTBOHQYY7TKLUY2RWMAV7NLU6TJ37IR";
                $userlist[$count]['thirdName'] = null;
                $userlist[$count]['thirdNameAr'] = null;
                $userlist[$count]['thirdNameEn'] = null;
                $userlist[$count]['twoFactorEnabled'] = false;
                $userlist[$count]['userName'] = $list->username;
                $userlist[$count]['deactivationReason'] = null;
                $userlist[$count]['userIDPortal'] = null;
                $userlist[$count]['isSSOUpdated'] = null;
                $userlist[$count]['id'] = $list->user_id;   
                }    
              } 
        $userContext = array(
            "records" => $userlist,
             );
        return $userContext;
    
            }


  public function gettestcentertodayperiods($filterdata)
  {
    global $DB, $PAGE;
    $systemcontext = context_system::instance();
    $PAGE->set_context($systemcontext);
    $lang = current_language();
    $hallcode = 'riyath';
    $selectsql = "SELECT h.id, h.*
                              FROM {hall} h WHERE 1=1 AND code = '$hallcode' AND availability = 1 ";
    $countsql = "SELECT COUNT(h.id) 
                              FROM {hall} h WHERE 1=1 AND code = '$hallcode' AND availability = 1 ";

    $params = array_merge($searchparams);
    $totalhalls = $DB->count_records_sql($countsql . $formsql, $params);
    $formsql .= " ORDER BY h.id ASC";
    $halls = $DB->get_records_sql($selectsql . $formsql, $params);
    $hallslist = array();
    $count = 0;
    $selectsql = " SELECT hr.id as reservationid, le.id as examid, hr.slotstart, hr.slotend, hr.seats, hr.examdate,hr.hallid, h.*, (SELECT COUNT( DISTINCT ee1.id)
                 FROM {exam_enrollments} ee1 
                 JOIN {hall_reservations} hr1 ON ee1.hallid = hr1.hallid AND ee1.examdate = hr1.examdate AND ee1.examid = hr1.typeid 
                 WHERE hr1.id = hr.id ) as enrolled ";
        $sql = " FROM {hall_reservations} hr
                 JOIN {hall} h ON h.id = hr.hallid
                 JOIN {local_exams} le ON le.id = hr.typeid
                 WHERE  h.code = '$hallcode' AND hr.type = 'exam' AND FROM_UNIXTIME(UNIX_TIMESTAMP(hr.examdate)+hr.slotend) > Now() ";
    $examreservations = $DB->get_records_sql($selectsql. $sql);

    //print_r($examreservations);
    

    if ($halls) {
      foreach ($examreservations as $hall) {


        $halldata = array();
        /* if($list->type == 1) {
        $halldata['type'] = get_string('income', 'local_events');
        } else {
        $halldata['type'] = get_string('expenses', 'local_events');
        }*/
        $enrolledcount = $DB->count_records('exam_enrollments', array('examid'=>$hall->examid, 'hallid' => $hall->hallid, 'examdate' => $hall->examdate));
        $halldata['dayPeriodId'] = $hall->reservationid;
        $slotstartdate = date('H:i', (strtotime($hall->examdate) + $hall->slotstart));
        $slotenddate = date('H:i', (strtotime($hall->examdate) + $hall->slotend));
        $halldata['periodFromTo'] = $slotstartdate.' - '.$slotenddate;
        $halldata['confirmedExamineesCount'] = $enrolledcount;
        $halldata['cancelledExamineesCount'] =0;
        $halldata['isGenerated'] ='true';
        $halldatalist[] = $halldata;
      }
      $norecords = true;
    } else {
      $norecords = false;
    }


    $coursesContext = array(
      "testCenterId" => $hallcode,
      "periodExaminees" => $halldatalist,
      "norecords" => $norecords
    );
    return $coursesContext;
  }

  public function gettestcentertoenrol($testCenterId,$dayPeriodId,$isForcedRegeneration)
  {
    global $DB, $PAGE;
    $systemcontext = context_system::instance();
    $PAGE->set_context($systemcontext);
    $lang = current_language();
    $hallcode = 'riyath';
    $selectsql = "SELECT h.id, h.*
                              FROM {hall} h WHERE 1=1 AND code = '$hallcode' AND availability = 1 ";
    $countsql = "SELECT COUNT(h.id) 
                              FROM {hall} h WHERE 1=1 AND code = '$hallcode' AND availability = 1 ";

    $params = array_merge($searchparams);
    $totalhalls = $DB->count_records_sql($countsql . $formsql, $params);
    $formsql .= " ORDER BY h.id ASC";
    $halls = $DB->get_records_sql($selectsql . $formsql, $params);
    $hallslist = array();
    $count = 0;
    echo $selectsql = " SELECT hr.id as reservationid, le.id as examid, hr.slotstart, hr.slotend, hr.seats, hr.examdate,hr.hallid, h.*, (SELECT COUNT( DISTINCT ee1.id)
                 FROM {exam_enrollments} ee1 
                 JOIN {hall_reservations} hr1 ON ee1.hallid = hr1.hallid AND ee1.examdate = hr1.examdate AND ee1.examid = hr1.typeid
                 
                 WHERE hr1.id = hr.id ) as enrolled";
     echo   $sql = " FROM {hall_reservations} hr
                 JOIN {hall} h ON h.id = hr.hallid
                 JOIN {local_exams} le ON le.id = hr.typeid
                 WHERE  h.code = '$hallcode' AND hr.type = 'exam' AND FROM_UNIXTIME(UNIX_TIMESTAMP(hr.examdate)+hr.slotend) > Now()";
    $examreservations = $DB->get_records_sql($selectsql. $sql);

  
    

    if ($halls) {
      foreach ($examreservations as $hall) {


        $halldata = array();
        /* if($list->type == 1) {
        $halldata['type'] = get_string('income', 'local_events');
        } else {
        $halldata['type'] = get_string('expenses', 'local_events');
        }*/
        $enrolledcount = $DB->count_records('exam_enrollments', array('examid'=>$hall->examid, 'hallid' => $hall->hallid, 'examdate' => $hall->examdate));
        $halldata['dayPeriodId'] = $hall->reservationid;
        $slotstartdate = date('H:i', (strtotime($hall->examdate) + $hall->slotstart));
        $slotenddate = date('H:i', (strtotime($hall->examdate) + $hall->slotend));
        $halldata['periodFromTo'] = $slotstartdate.' - '.$slotenddate;
        $halldata['confirmedExamineesCount'] = $enrolledcount;
        $halldata['cancelledExamineesCount'] =0;
        $halldata['isGenerated'] ='true';
        $halldatalist[] = $halldata;
      }
      $norecords = true;
    } else {
      $norecords = false;
    }


    $coursesContext = array(
      "testCenterId" => $hallcode,
      "periodExaminees" => $halldatalist,
      "norecords" => $norecords
    );
    return $coursesContext;
  }

}
