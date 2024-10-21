<?php
// This file is part of Moodle - http://moodle.org/
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
 * @package    local_userapproval
 * @copyright  2022 eAbyas Info Solutions<info@eabyas.com>
 * @author     Vinod Kumar  <vinod.p@eabyas.com>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
defined('MOODLE_INTERNAL') || die;
use dml_transaction_exception;
use context;
use context_system;
use Exception;
use moodle_exception;
use \local_userapproval\form\individual_registration_form as individual_registration_form;
use \local_userapproval\action\manageuser as manageuser;
require_once($CFG->dirroot.'/local/userapproval/lib.php');
require_once($CFG->dirroot.'/course/lib.php');
class local_userapproval_external extends \external_api {
    public static function usersview_parameters() {
        return new external_function_parameters([
            'options' => new external_value(PARAM_RAW, 'The paging data for the service'),
            'dataoptions' => new external_value(PARAM_RAW, 'The data for the service'),
            'offset' => new external_value(PARAM_INT, 'Number of items to skip from the begging of the result set',
                VALUE_DEFAULT, 0),
            'limit' => new external_value(PARAM_INT, 'Maximum number of results to return',
                VALUE_DEFAULT, 0),
            'contextid' => new external_value(PARAM_INT, 'contextid'),
            'filterdata' => new external_value(PARAM_RAW, 'filters applied'),
        ]);
    }
    public static function usersview($options, $dataoptions, $offset = 0, $limit = 0, $contextid, $filterdata) {
        global $DB, $PAGE, $CFG;
        require_login();
        $params = self::validate_parameters(
            self::usersview_parameters(),
            [
                'options' => $options,
                'dataoptions' => $dataoptions,
                'offset' => $offset,
                'limit' => $limit,
                'contextid' => $contextid,
                'filterdata' => $filterdata,
            ]
        );
        $offset = $params['offset'];
        $limit = $params['limit'];
        $filtervalues = json_decode($filterdata);
        $stable = new \stdClass();
        $stable->thead = false;
        $stable->start = $offset;
        $stable->length = $limit;
        $data = (new local_userapproval\action\manageuser)->get_listof_users($stable, $filtervalues);
        $totalcount = $data['totalusers'];
        return [
           'totalcount' => $totalcount,
            'length' => $totalcount,
            'filterdata' => $filterdata,
            'records' =>$data,
            'options' => $options,
            'dataoptions' => $dataoptions,
            'url' => $CFG->wwwroot,
        ];
    }
    public static function usersview_returns() {
        return new external_single_structure([
          'options' => new external_value(PARAM_RAW, 'The paging data for the service'),
          'url' => new external_value(PARAM_RAW, 'url'),
          'dataoptions' => new external_value(PARAM_RAW, 'The data for the service'),
          'totalcount' => new external_value(PARAM_INT, 'total number of challenges in result set'),
          'filterdata' => new external_value(PARAM_RAW, 'total number of challenges in result set'),
          'length' => new external_value(PARAM_RAW, 'total number of challenges in result set'),
          'records' => new external_single_structure(
                  array(
                      'hascourses' => new external_multiple_structure(
                        new external_single_structure(
                            array(
                                'id' => new external_value(PARAM_INT, 'id'),
                                'userid'  => new external_value(PARAM_RAW, 'userid'),
                                'fullname' => new external_value(PARAM_RAW, 'fullname'),
                                'displayfullname' => new external_value(PARAM_RAW, 'fullname'),
                                'username' => new external_value(PARAM_RAW, 'username'),
                                'email' => new external_value(PARAM_RAW, 'email'),
                                'mobile' => new external_value(PARAM_RAW, 'mobile'),
                                'status' => new external_value(PARAM_RAW, 'status'),
                                'sector' => new external_value(PARAM_RAW, 'sector'),
                                'organization' => new external_value(PARAM_RAW, 'organization'),
                                'approvedstatus' => new external_value(PARAM_RAW, 'approvedstatus'),
                                'rejectedactiondisplay' => new external_value(PARAM_RAW, 'rejectedactiondisplay'),
                                'deletedstatus' => new external_value(PARAM_RAW, 'deletedstatus'),
                                'id_number' => new external_value(PARAM_RAW, 'id_number'),
                                'manageuser' => new external_value(PARAM_BOOL, 'manageuser'),
                                'segmentid' => new external_value(PARAM_RAW, 'segmentid'),
                                'jobfamilyid' => new external_value(PARAM_RAW, 'jobfamilyid'),
                                'jobroleid' => new external_value(PARAM_RAW, 'jobroleid'),
                                'view' => new external_value(PARAM_RAW, 'view'),
                                'approve' => new external_value(PARAM_RAW, 'approve'),
                                'reject' => new external_value(PARAM_RAW, 'reject'),
                                'delete' => new external_value(PARAM_RAW, 'delete'),
                                'edit' => new external_value(PARAM_RAW, 'edit'),
                                'sendemail' => new external_value(PARAM_RAW, 'sendemail'),
                                'examenrolledusers' => new external_value(PARAM_RAW, 'examenrolledusers'),
                                'tpenrolledusers' => new external_value(PARAM_RAW, 'tpenrolledusers'),
                                'eventenrolledusers' => new external_value(PARAM_RAW, 'eventenrolledusers'),
                                'isorgofficial' => new external_value(PARAM_RAW, 'isorgofficial'),
                                
                            )
                        )
                    ),
                    'request_view' => new external_value(PARAM_INT, 'request_view', VALUE_OPTIONAL),
                    'nocourses' => new external_value(PARAM_BOOL, 'nocourses', VALUE_OPTIONAL),
                    'totalusers' => new external_value(PARAM_INT, 'totalusers', VALUE_OPTIONAL),
                    'length' => new external_value(PARAM_INT, 'length', VALUE_OPTIONAL),
                )
            )
        ]);
    }    
    public static function deleteteuser_parameters(){
          return new external_function_parameters(
            array(
                'contextid' => new external_value(PARAM_INT, 'The context id for the system',0),
                'userid' => new external_value(PARAM_INT,'User id',0),
                'username' => new external_value(PARAM_TEXT,'User name'),
            )
        );
    }
    public static  function deleteteuser($contextid, $userid, $username){
        global $CFG;
        global $USER;
        global $DB;
        $params=self::validate_parameters(
            self::deleteteuser_parameters(),
            array('contextid'=>$contextid, 'userid'=>$userid, 'username'=>$username)
        );
        $systemcontext = context_system::instance();
        self::validate_context($systemcontext);
       if ($userid) {
          $userrecord=$DB->get_record('local_users',array('id'=>$userid));
          $deleterecord= (new local_userapproval\action\manageuser)->delete_user($userid);
          $approveduserid=$DB->get_field('local_users','userid',array('id'=>$userid));
          $description= get_string('delete_descption','local_userapproval',$userrecord);
          if ($approveduserid) {
             $insert_user_logs =(new local_userapproval\action\manageuser)->local_users_logs('deleted', 'userapproval', $description,$approveduserid);
           } else {
             $insert_user_logs =(new local_userapproval\action\manageuser)->local_users_logs('deleted', 'userapproval', $description,$userid);
           }
        } else {
          throw new moodle_exception('Error in submission');
        }
        return true;    
    }
    public static function deleteteuser_returns() {
          return new external_value(PARAM_BOOL, 'return');
    }
    public static function submit_create_reviewer_course_form_parameters() {
        return new external_function_parameters(
            array(
                'contextid' => new external_value(PARAM_INT, 'The context id for the system',0),
                'userid' => new external_value(PARAM_INT,'User id',0),
                'jsonformdata' => new external_value(PARAM_RAW, 'The data from the create course form, encoded as a json array')
            )
        );
    }
   public static function approveuser_parameters(){

      return new external_function_parameters(
            array(
                'contextid' => new external_value(PARAM_INT, 'The context id for the system',0),
                'userid' => new external_value(PARAM_INT,'User id',0),
                'username' => new external_value(PARAM_TEXT,'User name'),
            )
        );
    }
    public static  function approveuser($contextid, $userid, $username){
        global $CFG, $USER, $DB;
        
        $params=self::validate_parameters(
            self::approveuser_parameters(),
            array('contextid'=>$contextid, 'userid'=>$userid, 'username'=>$username)
        );
        $systemcontext = context_system::instance();
        self::validate_context($systemcontext);
        if ($userid) {
            try{
                $e= new stdClass;
                $failed_masterdata = array();
                $transaction = $DB->start_delegated_transaction();
                $userrecord=$DB->get_record('local_users',array('id'=>$userid));
                $userid = (new local_userapproval\action\manageuser)->create_user($userrecord);
                $insert_user_record = (new local_userapproval\action\manageuser)->approve_custom_user($userrecord,$userid);
                $description= get_string('approve_descption','local_userapproval',$userrecord);
                $insert_user_logs =(new local_userapproval\action\manageuser)->local_users_logs('approved', 'registration', $description,$userid);
                $data = $DB->get_record('local_users', ['userid' => $userid]);
                $data->nationalitycountryid =  $data->nationalitycountryid ? $data->nationalitycountryid : 114; 
                if($userid){
                    $orgdata = (new manageuser)->get_user_org_info($userid);  
                    $data->id = 0;
                    $data->orgcode = $orgdata->orgcode;
                    $data->licensekey = $orgdata->licensekey;

                    // Checking Fast Settings(User Registration) is Enabled or not
                    $accessstatus = (new \local_userapproval\local\fast_service)->access_resisteruser_service();
                    if ($accessstatus) {
                        $response =   (new \local_userapproval\local\fast_service)->register_user($data);
                        if(COUNT($response->errors) > 0 ){
                            $error = json_encode($response->errors, JSON_UNESCAPED_UNICODE);
                            $e = new moodle_exception($error);
                        } 
       
                        if(COUNT($response->messages) > 0 ){
                            $messages = json_encode($response->messages, JSON_UNESCAPED_UNICODE);
                            $e = new moodle_exception($messages);
                        } 
                    }
                }
                if (empty($e) || !($e instanceof moodle_exception)) {
                    $DB->execute('UPDATE {local_users}  SET confirm_password =  NULL WHERE userid = '.$userid.'');
                    $transaction->allow_commit();
                    return $insert_user_record;
                }else{
                    $data->status = "-1";
                    $failed_masterdata['data'] = $data;
                    $failed_masterdata['e'] = $e;
                    $transaction->rollback($e);
                }
            } catch(moodle_exception | Exception | dml_transaction_exception $e){
                if(!$transaction->is_disposed()){
                   $data->status = "-1";
                   $failed_masterdata['data'] = $data;
                   $failed_masterdata['e'] = $e;
                   $transaction->rollback($e);
                }
            }
        } else {
            throw new moodle_exception('Error in submission');
        }
        if(!empty($failed_masterdata)){
            (new \local_userapproval\local\fast_service)->add_update_service($failed_masterdata['data']);
            throw new moodle_exception($failed_masterdata['e']->errorcode);
        }
    }
    public static function approveuser_returns() {
        return new external_value(PARAM_BOOL, 'return');
    }

    public static function rejectuser_parameters(){

        return new external_function_parameters(
            array(
                'contextid' => new external_value(PARAM_INT, 'The context id for the system',0),
                'userid' => new external_value(PARAM_INT,'User id',0),
                'username' => new external_value(PARAM_TEXT,'User name'),
            )
        );
    }
    public static  function rejectuser($contextid, $userid, $username){
        global $CFG;
        global $USER;
        global $DB;
        $params=self::validate_parameters(
            self::rejectuser_parameters(),
            array('contextid'=>$contextid, 'userid'=>$userid, 'username'=>$username)
        );
        $systemcontext = context_system::instance();
        self::validate_context($systemcontext);
 
      if ($userid) {
         $userrecord=$DB->get_record('local_users',array('id'=>$userid));
         $insert_user_record = (new local_userapproval\action\manageuser)->reject_user_registration($userid);
         $description= get_string('reject_descption','local_userapproval',$userrecord);
         $insert_user_logs =(new local_userapproval\action\manageuser)->local_users_logs('rejected', 'userapproval', $description,$userid);
        } else {
            throw new moodle_exception('Error in submission');
        }
        return true;    
    }
    public static function rejectuser_returns() {
        return new external_value(PARAM_BOOL, 'return');
    }
    public static function viewregistration_parameters() {
        return new external_function_parameters(
            array(
                'requestid' => new external_value(PARAM_INT,'request id',0),
                'userid' => new external_value(PARAM_INT,'User id',0),
                'username' => new external_value(PARAM_TEXT,'User name'),
                'requesttype' =>new external_value(PARAM_TEXT,'requesttype',VALUE_OPTIONAL),
            )
        );
    }
    public static function viewregistration($requestid,$userid,$username,$requesttype = null) {
        global $DB;
        require_login();
        $params = self::validate_parameters(self::viewregistration_parameters(),
        ['requestid' => $requestid,'userid' => $userid,'username'=>$username,'requesttype'=>$requesttype]);
        $data = (new local_userapproval\action\manageuser)->registrationinfo($requestid,$userid,$username,$requesttype);
        return [
            'options' => $data,
        ];
    }
    public static function viewregistration_returns() {
        return new external_single_structure([
          'options' => new external_value(PARAM_RAW, 'The paging data for the service'),
        ]);
    }

    public static function orgrequest_parameters() {
        return new external_function_parameters([
            'options' => new external_value(PARAM_RAW, 'The paging data for the service'),
            'dataoptions' => new external_value(PARAM_RAW, 'The data for the service'),
            'offset' => new external_value(PARAM_INT, 'Number of items to skip from the begging of the result set',
                VALUE_DEFAULT, 0),
            'limit' => new external_value(PARAM_INT, 'Maximum number of results to return',
                VALUE_DEFAULT, 0),
            'contextid' => new external_value(PARAM_INT, 'contextid'),
            'filterdata' => new external_value(PARAM_RAW, 'filters applied'),
        ]);
    }
    public static function orgrequest($options, $dataoptions, $offset = 0, $limit = 0, $contextid, $filterdata) {
        global $DB, $PAGE, $CFG;
        require_login();
        $params = self::validate_parameters(
            self::orgrequest_parameters(),
            [
                'options' => $options,
                'dataoptions' => $dataoptions,
                'offset' => $offset,
                'limit' => $limit,
                'contextid' => $contextid,
                'filterdata' => $filterdata,
            ]
        );
        $offset = $params['offset'];
        $limit = $params['limit'];
        $filtervalues = json_decode($filterdata);
        $stable = new \stdClass();
        $stable->thead = false;
        $stable->start = $offset;
        $stable->length = $limit;
        $data = (new local_userapproval\action\manageuser)->orgrequest_dashboard_data($stable, $filtervalues);
        $totalcount = $data['totalusers'];
        return [
           'totalcount' => $totalcount,
            'length' => $totalcount,
            'filterdata' => $filterdata,
            'records' =>$data,
            'options' => $options,
            'dataoptions' => $dataoptions,
            'url' => $CFG->wwwroot,
        ];
    }
    public static function orgrequest_returns() {
        return new external_single_structure([
          'options' => new external_value(PARAM_RAW, 'The paging data for the service'),
          'url' => new external_value(PARAM_RAW, 'url'),
          'dataoptions' => new external_value(PARAM_RAW, 'The data for the service'),
          'totalcount' => new external_value(PARAM_INT, 'total number of challenges in result set'),
          'filterdata' => new external_value(PARAM_RAW, 'total number of challenges in result set'),
          'length' => new external_value(PARAM_RAW, 'total number of challenges in result set'),
          'records' => new external_single_structure(
                  array(
                      'hascourses' => new external_multiple_structure(
                        new external_single_structure(
                            array(
                                // 'coid' => new external_value(PARAM_INT, 'coid'),
                                'userid' => new external_value(PARAM_INT, 'userid'),
                                'orgid' => new external_value(PARAM_INT, 'orgid'),
                                'orgname' => new external_value(PARAM_RAW, 'orgname'),
                                'requestid' => new external_value(PARAM_INT, 'requestid'),
                                'fullname' => new external_value(PARAM_RAW, 'fullname'),
                                'timecreated' => new external_value(PARAM_RAW, 'timecreated'),
                            )
                        )
                    ),
                    'request_view' => new external_value(PARAM_INT, 'request_view', VALUE_OPTIONAL),
                    'nocourses' => new external_value(PARAM_BOOL, 'nocourses', VALUE_OPTIONAL),
                    'totalusers' => new external_value(PARAM_INT, 'totalusers', VALUE_OPTIONAL),
                    'length' => new external_value(PARAM_INT, 'length', VALUE_OPTIONAL),
                )
            )
        ]);
    } 

    public static function rejectorgrequest_parameters(){

          return new external_function_parameters(
            array(
            
                'requestid' => new external_value(PARAM_INT,'Request id',0),
                'orgid' => new external_value(PARAM_INT,'Organization id',0),
                'userid' => new external_value(PARAM_INT,'User id',0),
            )
        );
    }
    public static  function rejectorgrequest($requestid,$orgid,$userid){
        global $CFG;
        global $USER;
        global $DB;
        $params=self::validate_parameters(
            self::rejectorgrequest_parameters(),
            array('requestid'=>$requestid, 'orgid'=>$orgid, 'userid'=>$userid)
        );
        $systemcontext = context_system::instance();
        self::validate_context($systemcontext);
 
      if ($requestid > 0) {
          $data=$DB->execute('UPDATE {organization_requests} SET status = 3 WHERE id='.$requestid);
          
        } else {
            throw new moodle_exception('Error in submission');
        }
        return true;    
    }
    public static function rejectorgrequest_returns() {
        return new external_value(PARAM_BOOL, 'return');
    }

    public static function approveorgrequest_parameters(){

          return new external_function_parameters(
            array(
            
                'requestid' => new external_value(PARAM_INT,'Request id',0),
                'orgid' => new external_value(PARAM_INT,'Organization id',0),
                'userid' => new external_value(PARAM_INT,'User id',0),
            )
        );
    }
    public static  function approveorgrequest($requestid,$orgid,$userid){
        global $CFG;
        global $USER;
        global $DB;
        $params=self::validate_parameters(
            self::approveorgrequest_parameters(),
            array('requestid'=>$requestid, 'orgid'=>$orgid, 'userid'=>$userid)
        );
        $systemcontext = context_system::instance();
        self::validate_context($systemcontext);
 
      if ($requestid > 0) {
          $data=$DB->execute('UPDATE {organization_requests} SET status = 2 WHERE id='.$requestid);

            if($data){
               $updateorganization=$DB->execute("UPDATE {local_users} SET organization = $orgid WHERE userid=$userid ");
               $organizationapproval=$DB->get_record('user',array('id'=>$userid));
               $organizationapproval->user_fullname=  $DB->get_field('local_users','firstname',array('userid'=>$userid)).$DB->get_field('local_users','firstname',array('userid'=>$userid));
               $organizationapproval->user_organization=$DB->get_field('local_organization','fullname',array('id'=>$orgid));
               $organizationapproval->orgid=$orgid;
               (new \local_userapproval\notification())->userapproval_notification('organizational_approval', $touser=$organizationapproval,$fromuser=$USER, $organizationapproval,$waitinglistid=0);
            }

        } else {
            throw new moodle_exception('Error in submission');
        }
        return true;    
    }
    public static function approveorgrequest_returns() {
        return new external_value(PARAM_BOOL, 'return');
    } 

     public static function totalorgpendingrequests_parameters() {
        return new external_function_parameters([
            'options' => new external_value(PARAM_RAW, 'The paging data for the service'),
            'dataoptions' => new external_value(PARAM_RAW, 'The data for the service'),
            'offset' => new external_value(PARAM_INT, 'Number of items to skip from the begging of the result set',
                VALUE_DEFAULT, 0),
            'limit' => new external_value(PARAM_INT, 'Maximum number of results to return',
                VALUE_DEFAULT, 0),
            'contextid' => new external_value(PARAM_INT, 'contextid'),
            'filterdata' => new external_value(PARAM_RAW, 'filters applied'),
        ]);
    }
    public static function totalorgpendingrequests($options, $dataoptions, $offset = 0, $limit = 0, $contextid, $filterdata) {
        global $DB, $PAGE, $CFG;
        require_login();
        $params = self::validate_parameters(
            self::totalorgpendingrequests_parameters(),
            [
                'options' => $options,
                'dataoptions' => $dataoptions,
                'offset' => $offset,
                'limit' => $limit,
                'contextid' => $contextid,
                'filterdata' => $filterdata,
            ]
        );
        $offset = $params['offset'];
        $limit = $params['limit'];
        $filtervalues = json_decode($filterdata);
        $stable = new \stdClass();
        $stable->thead = false;
        $stable->start = $offset;
        $stable->length = $limit;
        $data = (new local_userapproval\action\manageuser)->total_orgrequest_data($stable, $filtervalues);
        $totalcount = $data['totalusers'];
        return [
           'totalcount' => $totalcount,
            'length' => $totalcount,
            'filterdata' => $filterdata,
            'records' =>$data,
            'options' => $options,
            'dataoptions' => $dataoptions,
            'url' => $CFG->wwwroot,
        ];
    }
    public static function totalorgpendingrequests_returns() {
        return new external_single_structure([
          'options' => new external_value(PARAM_RAW, 'The paging data for the service'),
          'url' => new external_value(PARAM_RAW, 'url'),
          'dataoptions' => new external_value(PARAM_RAW, 'The data for the service'),
          'totalcount' => new external_value(PARAM_INT, 'total number of challenges in result set'),
          'filterdata' => new external_value(PARAM_RAW, 'total number of challenges in result set'),
          'length' => new external_value(PARAM_RAW, 'total number of challenges in result set'),
          'records' => new external_single_structure(
                  array(
                      'hascourses' => new external_multiple_structure(
                        new external_single_structure(
                            array(
                                // 'coid' => new external_value(PARAM_INT, 'coid'),
                                'userid' => new external_value(PARAM_INT, 'userid'),
                                'orgid' => new external_value(PARAM_INT, 'orgid'),
                                'orgname' => new external_value(PARAM_RAW, 'orgname'),
                                'requestid' => new external_value(PARAM_INT, 'requestid'),
                                'fullname' => new external_value(PARAM_RAW, 'fullname'),
                                'email' => new external_value(PARAM_RAW, 'email'),
                                'timecreated' => new external_value(PARAM_RAW, 'timecreated'),
                                'actions' => new external_value(PARAM_RAW, 'actions'),
                                'requeststatus' => new external_value(PARAM_RAW, 'requeststatus'),
                            )
                        )
                    ),
                    'request_view' => new external_value(PARAM_INT, 'request_view', VALUE_OPTIONAL),
                    'nocourses' => new external_value(PARAM_BOOL, 'nocourses', VALUE_OPTIONAL),
                    'totalusers' => new external_value(PARAM_INT, 'totalusers', VALUE_OPTIONAL),
                    'length' => new external_value(PARAM_INT, 'length', VALUE_OPTIONAL),
                )
            )
        ]);
    }

    public static function deletebannerimage_parameters(){
        return new external_function_parameters(
          array(
            'contextid' => new external_value(PARAM_INT, 'The context id for the system',0),
            'userid' => new external_value(PARAM_INT,'User id',0),
          
          )
      );
    } 

    public static  function deletebannerimage($contextid, $userid){
      global $CFG;
      global $USER;
      global $DB;
      $params=self::validate_parameters(
          self::deletebannerimage_parameters(),
          array('contextid'=>$contextid, 'userid'=>$userid,)
      );
      $systemcontext = context_system::instance();
      self::validate_context($systemcontext);
     if ($userid) {
        $userrecord=$DB->get_record('local_users',array('id'=>$userid));
        $deleterecord= (new local_userapproval\action\manageuser)->delete_bannerimage($userid);
        
      } else {
        throw new moodle_exception('Error in submission');
      }
      return true;    
    }
    public static function deletebannerimage_returns() {
      return new external_value(PARAM_BOOL, 'return');
    } 

    public static function trainer_expert_request_view_parameters() {
        return new external_function_parameters([
            'options' => new external_value(PARAM_RAW, 'The paging data for the service'),
            'dataoptions' => new external_value(PARAM_RAW, 'The data for the service'),
            'offset' => new external_value(PARAM_INT, 'Number of items to skip from the begging of the result set',
                VALUE_DEFAULT, 0),
            'limit' => new external_value(PARAM_INT, 'Maximum number of results to return',
                VALUE_DEFAULT, 0),
            'contextid' => new external_value(PARAM_INT, 'contextid'),
            'filterdata' => new external_value(PARAM_RAW, 'filters applied'),
        ]);
   }
    public static function trainer_expert_request_view($options, $dataoptions, $offset = 0, $limit = 0, $contextid, $filterdata) {
        global $DB, $PAGE, $CFG;
        
        $params = self::validate_parameters(
            self::trainer_expert_request_view_parameters(),
            [
                'options' => $options,
                'dataoptions' => $dataoptions,
                'offset' => $offset,
                'limit' => $limit,
                'contextid' => $contextid,
                'filterdata' => $filterdata,
            ]
        );
        $offset = $params['offset'];
        $limit = $params['limit'];
        $filtervalues = json_decode($filterdata);
        $stable = new \stdClass();
        $stable->thead = false;
        $stable->start = $offset;
        $stable->length = $limit;
        $data = (new local_userapproval\action\manageuser)->get_listof_trainer_expert_request_data($stable, $filtervalues);
        $totalcount = $data['totalusers'];
        return [
           'totalcount' => $totalcount,
            'length' => $totalcount,
            'filterdata' => $filterdata,
            'records' =>$data,
            'options' => $options,
            'dataoptions' => $dataoptions,
            'url' => $CFG->wwwroot,
        ];
    }
    public static function trainer_expert_request_view_returns() {
        return new external_single_structure([
          'options' => new external_value(PARAM_RAW, 'The paging data for the service'),
          'url' => new external_value(PARAM_RAW, 'url'),
          'dataoptions' => new external_value(PARAM_RAW, 'The data for the service'),
          'totalcount' => new external_value(PARAM_INT, 'total number of challenges in result set'),
          'filterdata' => new external_value(PARAM_RAW, 'total number of challenges in result set'),
          'length' => new external_value(PARAM_RAW, 'total number of challenges in result set'),
          'records' => new external_single_structure(
                  array(
                      'hascourses' => new external_multiple_structure(
                        new external_single_structure(
                            array(
                                'id' => new external_value(PARAM_INT, 'id'),
                                'trainername' => new external_value(PARAM_RAW, 'trainername'),
                                'id_number' => new external_value(PARAM_RAW, 'id_number'),
                                'luserid' => new external_value(PARAM_RAW, 'luserid'),
                                'qualification' => new external_value(PARAM_RAW, 'trainerdocument'),
                                 'requesttype' => new external_value(PARAM_RAW, 'requesttype'),
                                'fieldoftraining' => new external_value(PARAM_RAW, 'fieldoftraining'),
                                'status' => new external_value(PARAM_RAW, 'status'),
                                'actionview' => new external_value(PARAM_RAW, 'actionview'),
                                'expertuserid' => new external_value(PARAM_RAW, 'expertuserid'),
                                'actionviewapprove' => new external_value(PARAM_RAW, 'actionviewapprove'),
                                'actionreject' => new external_value(PARAM_RAW, 'actionreject'),
                                'actioncancelled'=> new external_value(PARAM_RAW, 'actioncancelled'),
                                'disableaction'=> new external_value(PARAM_RAW, 'disableaction'),
                                'role'=> new external_value(PARAM_RAW, 'role'),
                                'enrolled' => new external_value(PARAM_BOOL, 'enrolled', VALUE_OPTIONAL),
        
                            )
                        )
                    ),
                    'request_view' => new external_value(PARAM_INT, 'request_view', VALUE_OPTIONAL),
                    'nocourses' => new external_value(PARAM_BOOL, 'nocourses', VALUE_OPTIONAL),
                    'totalusers' => new external_value(PARAM_INT, 'totalusers', VALUE_OPTIONAL),
                    'length' => new external_value(PARAM_INT, 'length', VALUE_OPTIONAL),
                    
                )
            )
        ]);
    }
    public static function approverequest_parameters(){
        return new external_function_parameters(
          array(
          'contextid' => new external_value(PARAM_INT, 'The context id for the system',0),
          'approverequestid' => new external_value(PARAM_INT,'Request id',0),
          'requesttype' => new external_value(PARAM_TEXT,'Request type',''),
          
          )
      );
    }
    public static  function approverequest($contextid, $requestid, $requesttype){
        global $CFG;
        global $USER;
        global $DB;
        $params=self::validate_parameters(
            self::approverequest_parameters(),
            array('contextid'=>$contextid, 'approverequestid'=>$requestid, 'requesttype'=>$requesttype)
        );
        $systemcontext = context_system::instance();
        self::validate_context($systemcontext);
       if ($requestid) {
          $approverequest= (new local_userapproval\action\manageuser)->approve_request($requestid,$requesttype);
          
        } else {
          throw new moodle_exception('Error in submission');
        }
        return true;    
    }
    public static function approverequest_returns() {
          return new external_value(PARAM_BOOL, 'return');
    }

    public static function rejectrequest_parameters(){
        return new external_function_parameters(
          array(
            'contextid' => new external_value(PARAM_INT, 'The context id for the system',0),
            'requestid' => new external_value(PARAM_INT,'Request id',0),
            'requesttype' => new external_value(PARAM_TEXT,'Request type',''),
          
          )
      );
    }
    public static  function rejectrequest($contextid, $rejectid, $requesttype){
        global $CFG;
        global $USER;
        global $DB;
        $params=self::validate_parameters(
            self::rejectrequest_parameters(),
            array('contextid'=>$contextid, 'requestid'=>$rejectid,'requesttype'=>$requesttype)
        );
        $systemcontext = context_system::instance();
        self::validate_context($systemcontext);
       if ($rejectid){
          $rejectrequest= (new local_userapproval\action\manageuser)->reject_request($rejectid,$requesttype);
          
        } else {
          throw new moodle_exception('Error in submission');
        }
        return true;    
    }
    public static function cancelorgrequest_returns() {
        return new external_value(PARAM_BOOL, 'return');
    }
    //
        public static function cancelorgrequest_parameters(){
        return new external_function_parameters(
          array(
            'contextid' => new external_value(PARAM_INT, 'The context id for the system',0),
            'cancelid' => new external_value(PARAM_INT,'Cancel id',0),
            'requesttype' => new external_value(PARAM_TEXT,'Request type',''),
            'trainerorexpertid' => new external_value(PARAM_INT,'Trainerorexpertid type',''),
          
          )
      );
    }
    public static  function cancelorgrequest($contextid, $cancelid, $requesttype,$trainerorexpertid){
        global $CFG;
        global $USER;
        global $DB;
      
        $params=self::validate_parameters(
            self::cancelorgrequest_parameters(),
            array('contextid'=>$contextid, 'cancelid'=>$cancelid,'requesttype'=>$requesttype,'trainerorexpertid'=>$trainerorexpertid)
        );
        $systemcontext = context_system::instance();
        self::validate_context($systemcontext);
       if ($cancelid){
          $cancelrequest= (new local_userapproval\action\manageuser)->cancel_request($cancelid,$requesttype,$trainerorexpertid);
          
        } else {
          throw new moodle_exception('Error in submission');
        }
        return true;    
    }
//
    public static function rejectrequest_returns() {
        return new external_value(PARAM_BOOL, 'return');
    }

    public static function removeorgrequest_parameters(){
        return new external_function_parameters(
          array(
          'orgid' => new external_value(PARAM_INT, 'Organization ID',0),
          'userid' => new external_value(PARAM_INT, 'TUser ID',0),
          
          )
      );
    }
    public static  function removeorgrequest($orgid, $userid){
        global $CFG;
        global $USER;
        global $DB;
        $params=self::validate_parameters(
            self::removeorgrequest_parameters(),
            array('orgid'=>$orgid, 'userid'=>$userid,)
        );
        $systemcontext = context_system::instance();
        self::validate_context($systemcontext);
       if ($orgid) {
          $removeorgrequest= (new local_userapproval\action\manageuser)->remove_org_request($orgid,$userid);
          
        } else {
          throw new moodle_exception('Error in submission');
        }
        return true;    
    }
    public static function removeorgrequest_returns() {
          return new external_value(PARAM_BOOL, 'return');
    }

       public function registeruser_parameters() {
        return new external_function_parameters(
            array(
                'user' => new external_multiple_structure(
                    new external_single_structure(
                        array(
                            'firstname' => new external_value(PARAM_RAW, 'user firstname'),
                            'lastname' => new external_value(PARAM_RAW, 'user lastname'),
                            'firstnamearabic' => new external_value(PARAM_RAW, 'user firstnamearabic'),
                            'lastnamearabic' => new external_value(PARAM_RAW, 'user lastnamearabic'),
                            'gender' => new external_value(PARAM_INT, 'user gender'),
                            'lang' => new external_value(PARAM_RAW, 'user language'),
                            'id_type' => new external_value(PARAM_INT, 'user id_type'),
                            'id_number' => new external_value(PARAM_RAW, 'user id_number'),
                            'organization' => new external_value(PARAM_RAW, 'user organization', VALUE_OPTIONAL),
                            'sectors' => new external_value(PARAM_RAW, 'user sectors', VALUE_OPTIONAL),
                            'segment' => new external_value(PARAM_RAW, 'user segment', VALUE_OPTIONAL),
                            'jobfamily' => new external_value(PARAM_RAW, 'user jobfamily', VALUE_OPTIONAL),
                            'jobrole' => new external_value(PARAM_RAW, 'user jobrole', VALUE_OPTIONAL),
                            'jobrole_level' => new external_value(PARAM_RAW, 'user jobrole_level', VALUE_OPTIONAL),
                            'username' => new external_value(PARAM_RAW, 'user username'),
                            'password' => new external_value(PARAM_RAW, 'user password'),
                            'email' => new external_value(PARAM_RAW, 'user email'),
                            'country' => new external_value(PARAM_RAW, 'user country'),
                            'city' => new external_value(PARAM_RAW, 'user city'),
                            'phone1' => new external_value(PARAM_RAW, 'user mobile'),
                        )
                    )
                )
            )
        );
    }

    public function registeruser($user) {
        global $DB;
        $context = context_system::instance();
        self::validate_context($context);
        $params = self::validate_parameters(self::registeruser_parameters(), array('user' => $user));
        $requiredparams = ['firstname','lastname','firstnamearabic','lastnamearabic','gender','lang','id_type','id_number','organization','sectors','segment','jobfamily','jobrole','jobrole_level','username','password' ,'email','country','city','phone1'];
        try {
           // $object = json_decode(json_encode($params['user'][0]), FALSE);
            foreach($requiredparams as $param) {
                if(empty($params['user'][0][$param])) {
                    throw new moodle_exception(get_string('invalidvalue', 'local_userapproval', $param));
                }
            }
            foreach($params['user'] as $user) {
                $user = (object)$user;
               /* if (empty(trim($user->firstname)) && empty(trim($user->firstname))){
                    throw new moodle_exception(get_string('fullnamerequired','local_userapproval'));
                }*/

                $is_record_exists = $DB->record_exists_sql('SELECT muser.id FROM  {user} AS muser JOIN {local_users} AS lmuser ON lmuser.userid = muser.id WHERE muser.username = '.$user->username.'');

                if($is_record_exists) {

                    $userid = $DB->get_field_sql('SELECT muser.id FROM  {user} AS muser JOIN {local_users} AS lmuser ON lmuser.userid = muser.id WHERE muser.username = '.$user->username.'');

                    $updatecustomrecord = (new \local_userapproval\action\manageuser)->update_custom_user($user,$userid);
                    $record = (new \local_userapproval\action\manageuser)->user_update_user($data,$userid);
 
                } else {

                    $userid = (new \local_userapproval\action\manageuser)->create_user($user);
                    $record = (new \local_userapproval\action\manageuser)->create_custom_user($user,$userid);
                }

                $return = $insertrecord;
                $status = get_string('userregisteredsuccessfully', 'local_userapproval');
            }
        } catch(Exception $e){
            // throw new moodle_exception('Error in creating the organization');
            $return = 0;
            $status = $e->getMessage();
        }

        return ['id' => $return->id, 'message' => $status];
    }

    public function registeruser_returns() {
        return new external_function_parameters(
            array(
                'id' => new external_value(PARAM_INT, 'User id'),
                'message' => new external_value(PARAM_TEXT, 'status')
            )
        );
        return new external_value(PARAM_INT, 'return');
    }


   public static function recommendedexams_parameters() {
        return new external_function_parameters([
            'options' => new external_value(PARAM_RAW, 'The paging data for the service'),
            'dataoptions' => new external_value(PARAM_RAW, 'The data for the service'),
            'offset' => new external_value(PARAM_INT, 'Number of items to skip from the begging of the result set',
                VALUE_DEFAULT, 0),
            'limit' => new external_value(PARAM_INT, 'Maximum number of results to return',
                VALUE_DEFAULT, 0),
            'contextid' => new external_value(PARAM_INT, 'contextid', VALUE_OPTIONAL),
            'filterdata' => new external_value(PARAM_RAW, 'filters applied'),
        ]);
    }

    public static function recommendedexams($options, $dataoptions, $offset = 0, $limit = 0,$contextid=1, $filterdata) {
        global $DB, $PAGE, $CFG, $USER;
        $systemcontext = context_system::instance();
        $params = self::validate_parameters(
            self::recommendedexams_parameters(),
            [
                'options' => $options,
                'dataoptions' => $dataoptions,
                'offset' => $offset,
                'limit' => $limit,
                'filterdata' => $filterdata,
            ]
        );
        $offset = $params['offset'];
        $limit = $params['limit'];

        $filtervalues = (array)json_decode($params['filterdata']);
        $filtervalues[type] = 'exam';

        $stable = new \stdClass();
        $stable->thead = false;
        $stable->start = $offset;
        $stable->length = $limit;

        $data = (new local_userapproval\action\manageuser)->recommendedentities($stable, $filtervalues);

        $totalcount = $data['totalentities'];
        return [
            'totalcount' => $totalcount,
            'length' => $totalcount,
            'filterdata' => $filterdata,
            'records' =>$data,
            'options' => $options,
            'dataoptions' => $dataoptions,
            'url' => $CFG->wwwroot,
        ];
    }

  /**
   * Returns description of method result value
   * @return external_description
   */

    public static function recommendedexams_returns() {
        return new external_single_structure([
          'options' => new external_value(PARAM_RAW, 'The paging data for the service'),
          'url' => new external_value(PARAM_RAW, 'url'),
          'dataoptions' => new external_value(PARAM_RAW, 'The data for the service'),
          'totalcount' => new external_value(PARAM_INT, 'total number of challenges in result set'),
          'filterdata' => new external_value(PARAM_RAW, 'total number of challenges in result set'),
          'length' => new external_value(PARAM_RAW, 'total number of challenges in result set'),
          'records' => new external_single_structure(
                array(
                   'hascourses' => new external_multiple_structure(
                        new external_single_structure(
                            array(
                                'entityid' => new external_value(PARAM_RAW, 'entityid'),
                                'entityname' => new external_value(PARAM_RAW, 'reservationid'),
                                'entitycode' => new external_value(PARAM_RAW, 'slotstart', VALUE_OPTIONAL),
                                'startdate' => new external_value(PARAM_RAW, 'startdate', VALUE_OPTIONAL),
                                'enddate' => new external_value(PARAM_RAW, 'enddate', VALUE_OPTIONAL),
                                'price' => new external_value(PARAM_RAW, 'price', VALUE_OPTIONAL),
                                 'courseid' => new external_value(PARAM_INT, 'courseid'),
                                    'userid' => new external_value(PARAM_INT, 'userid'),
                                     'checkfavornot' => new external_value(PARAM_RAW,'checkfavornot',VALUE_OPTIONAL),
                                     'checkcomponent' => new external_value(PARAM_RAW,'checkcomponent',VALUE_OPTIONAL),
                                     'checkcomponenttype' => new external_value(PARAM_RAW,'checkcomponenttype',VALUE_OPTIONAL),
                                     'hidefavexamsview' => new external_value(PARAM_RAW,'hidefavexamsview',VALUE_OPTIONAL),

                            )
                        )
                    ),
                   'request_view' => new external_value(PARAM_INT, 'request_view', VALUE_OPTIONAL),
                   'nocourses' => new external_value(PARAM_BOOL, 'nocourses', VALUE_OPTIONAL),
                   'totalentities' => new external_value(PARAM_INT, 'totalentities', VALUE_OPTIONAL),
                   'length' => new external_value(PARAM_INT, 'length', VALUE_OPTIONAL),
                )
            ),
        ]);
    }

    public static function recommendedprograms_parameters() {
        return new external_function_parameters([
            'options' => new external_value(PARAM_RAW, 'The paging data for the service'),
            'dataoptions' => new external_value(PARAM_RAW, 'The data for the service'),
            'offset' => new external_value(PARAM_INT, 'Number of items to skip from the begging of the result set',
                VALUE_DEFAULT, 0),
            'limit' => new external_value(PARAM_INT, 'Maximum number of results to return',
                VALUE_DEFAULT, 0),
            'contextid' => new external_value(PARAM_INT, 'contextid', VALUE_OPTIONAL,1),
            'filterdata' => new external_value(PARAM_RAW, 'filters applied'),
        ]);
    }

    public static function recommendedprograms($options, $dataoptions, $offset = 0, $limit = 0,$filterdata) {
        global $DB, $PAGE, $CFG, $USER;
        $systemcontext = context_system::instance();
        $params = self::validate_parameters(
            self::recommendedprograms_parameters(),
            [
                'options' => $options,
                'dataoptions' => $dataoptions,
                'offset' => $offset,
                'limit' => $limit,
                'contextid' => $contextid,
                'filterdata' => $filterdata,
            ]
        );
        $offset = $params['offset'];
        $limit = $params['limit'];

        $filtervalues = (array)json_decode($params['filterdata']);
        $filtervalues[type] = 'program';

        $stable = new \stdClass();
        $stable->thead = false;
        $stable->start = $offset;
        $stable->length = $limit;

        $data = (new local_userapproval\action\manageuser)->recommendedentities($stable, $filtervalues);

        $totalcount = $data['totalentities'];
        $nocourses = $data['nocourses'];
        
        return [
            'totalcount' => $totalcount,
            'length' => $totalcount,
            'filterdata' => $filterdata,
            'records' =>$data,
            'options' => $options,
            'dataoptions' => $dataoptions,
            'url' => $CFG->wwwroot,
            'nocourses' => $nocourses,
        ];
    }

  /**
   * Returns description of method result value
   * @return external_description
   */

    public static function recommendedprograms_returns() {
        return new external_single_structure([
          'options' => new external_value(PARAM_RAW, 'The paging data for the service'),
          'url' => new external_value(PARAM_RAW, 'url'),
          'nocourses' => new external_value(PARAM_BOOL, 'nocourses', VALUE_OPTIONAL),
          'dataoptions' => new external_value(PARAM_RAW, 'The data for the service'),
          'totalcount' => new external_value(PARAM_INT, 'total number of challenges in result set'),
          'filterdata' => new external_value(PARAM_RAW, 'total number of challenges in result set'),
          'length' => new external_value(PARAM_RAW, 'total number of challenges in result set'),
          'records' => new external_single_structure(
                array(
                   'hascourses' => new external_multiple_structure(
                        new external_single_structure(
                            array(
                                'entityid' => new external_value(PARAM_RAW, 'entityid'),
                                'entityname' => new external_value(PARAM_RAW, 'reservationid'),
                                'entitycode' => new external_value(PARAM_RAW, 'slotstart', VALUE_OPTIONAL),
                                'startdate' => new external_value(PARAM_RAW, 'startdate', VALUE_OPTIONAL),
                                'enddate' => new external_value(PARAM_RAW, 'enddate', VALUE_OPTIONAL),
                                'price' => new external_value(PARAM_RAW, 'price', VALUE_OPTIONAL),
                                 'courseid' => new external_value(PARAM_INT, 'courseid'),
                                    'userid' => new external_value(PARAM_INT, 'userid'),
                                     'checkfavornot' => new external_value(PARAM_RAW,'checkfavornot',VALUE_OPTIONAL),
                                     'checkcomponent' => new external_value(PARAM_RAW,'checkcomponent',VALUE_OPTIONAL),
                                     'checkcomponenttype' => new external_value(PARAM_RAW,'checkcomponenttype',VALUE_OPTIONAL),
                                     'hidefavexamsview' => new external_value(PARAM_RAW,'hidefavexamsview',VALUE_OPTIONAL),
                            )
                        )
                    ),
                   'request_view' => new external_value(PARAM_INT, 'request_view', VALUE_OPTIONAL),
                   'nocourses' => new external_value(PARAM_BOOL, 'nocourses', VALUE_OPTIONAL),
                   'totalentities' => new external_value(PARAM_INT, 'totalentities', VALUE_OPTIONAL),
                   'length' => new external_value(PARAM_INT, 'length', VALUE_OPTIONAL),
                )
            ),
        ]);
    }

    public static function checkvalidsaudinationalid_parameters() {
        return new external_function_parameters(
            array(
                'NationalId' => new external_value(PARAM_INT, 'NationalId', VALUE_DEFAULT, 0),
            )
        );
    } 

    public static function checkvalidsaudinationalid($NationalId){
        global $DB, $CFG, $USER, $PAGE;
        
        
        $params = self::validate_parameters(self::checkvalidsaudinationalid_parameters(),
                                            ['NationalId' => $NationalId]); 

        $isnationalidisvalid = $DB->record_exists_sql('SELECT * FROM {local_users} WHERE id_type =:idtype AND id_number =:idnumber',['idtype'=>3,'idnumber'=>$NationalId]);

        $data = ($isnationalidisvalid) ? 'True':'False';
        return ['response'=>$data];
        
    }

    public static function checkvalidsaudinationalid_returns() {
       return new external_single_structure([
           'response'=>new external_value(PARAM_TEXT, 'response', VALUE_OPTIONAL),
        ]);
    }

    public function itemenrolledlist_parameters() {
        return new external_function_parameters([
            'options' => new external_value(PARAM_RAW, 'The paging data for the service'),
            'dataoptions' => new external_value(PARAM_RAW, 'The data for the service'),
            'offset' => new external_value(PARAM_INT, 'Number of items to skip from the begging of the result set',
                VALUE_DEFAULT, 0),
            'limit' => new external_value(PARAM_INT, 'Maximum number of results to return',
                VALUE_DEFAULT, 0),
             'filterdata' => new external_value(PARAM_RAW, 'The data for the service'),
       ]);
    }

    public function itemenrolledlist($options, $dataoptions, $offset = 0, $limit = 0, $filterdata) {
        global $OUTPUT, $CFG, $DB,$USER,$PAGE;
        $sitecontext = context_system::instance();
        $PAGE->set_context($sitecontext);
        $params = self::validate_parameters(
            self::itemenrolledlist_parameters(),
            [
                'options' => $options,
                'dataoptions' => $dataoptions,
                'offset' => $offset,
                'limit' => $limit,
                'filterdata' => $filterdata
            ]
        );
        
        $data_object = (json_decode($dataoptions));
        $offset = $params['offset'];
        $limit = $params['limit'];
        $filtervalues = json_decode($filterdata);
        $stable = new \stdClass();
        $stable->thead = true;
        $stable->start = $offset;
        $stable->length = $limit;
        $stable->userid = $data_object->userid;
        $stable->type = $data_object->type;
        $data = (new local_userapproval\action\manageuser)->itemenrolledlist($stable, $filtervalues);
        //ar_dump($data); exit;
        return [
            'totalcount' => $data['totalcount'],
            'records' => $data['records'],
            'options' => $options,
            'dataoptions' => $dataoptions,
            'filterdata' => $filterdata,
            'nodata' => get_string('nocompetencys','local_competency')
        ];
    }

    public function itemenrolledlist_returns() {
        return new external_single_structure([
            'options' => new external_value(PARAM_RAW, 'The paging data for the service'),
            'dataoptions' => new external_value(PARAM_RAW, 'The data for the service'),
            'totalcount' => new external_value(PARAM_INT, 'total number of competencies in result set'),
            'filterdata' => new external_value(PARAM_RAW, 'The data for the service'),
            'nodata' => new external_value(PARAM_RAW, 'The data for the service'),
            'records' => new external_multiple_structure(
                new external_single_structure(
                    array(
                        'id' => new external_value(PARAM_INT, 'item id'),
                        'itemname' => new external_value(PARAM_RAW, 'itemname'),
                        'code' => new external_value(PARAM_RAW, 'code'),
                        'startdate' => new external_value(PARAM_RAW, 'startdate'),
                        'enddate' => new external_value(PARAM_RAW, 'enddate'),
                        'cost' => new external_value(PARAM_RAW, 'cost'),
                    ) 
                )
            ),
        ]);
    }

    //tainer/experts assign by admin....renu
    public static function assign_by_admin_parameters() {
        return new external_function_parameters([
            'options' => new external_value(PARAM_RAW, 'The paging data for the service'),
            'dataoptions' => new external_value(PARAM_RAW, 'The data for the service'),
            'offset' => new external_value(PARAM_INT, 'Number of items to skip from the begging of the result set',
                VALUE_DEFAULT, 0),
            'limit' => new external_value(PARAM_INT, 'Maximum number of results to return',
                VALUE_DEFAULT, 0),
            'contextid' => new external_value(PARAM_INT, 'contextid'),
            'filterdata' => new external_value(PARAM_RAW, 'filters applied'),
        ]);
    }
    public static function assign_by_admin($options, $dataoptions, $offset = 0, $limit = 0, $contextid, $filterdata) {
        global $DB, $PAGE, $CFG;
        require_login();
        
        $params = self::validate_parameters(
            self::usersview_parameters(),
            [
                'options' => $options,
                'dataoptions' => $dataoptions,
                'offset' => $offset,
                'limit' => $limit,
                'contextid' => $contextid,
                'filterdata' => $filterdata,
            ]
        );
        $offset = $params['offset'];
        $limit = $params['limit'];
        $filtervalues = json_decode($filterdata);
        
        $stable = new \stdClass();
        $stable->thead = false;
        $stable->start = $offset;
        $stable->length = $limit;
        $data = (new local_userapproval\action\manageuser)->assigntrainer($stable, $filtervalues);
        $totalcount = $data['totalcount'];
        return [
            'totalcount' => $totalcount,
            'length' => $totalcount,
            'filterdata' => $filterdata,
            'records' =>$data,
            'options' => $options,
            'dataoptions' => $dataoptions,
            'url' => $CFG->wwwroot,
        ];
    }
    public static function assign_by_admin_returns() {
        return new external_single_structure([
            'options' => new external_value(PARAM_RAW, 'The paging data for the service'),
            'url' => new external_value(PARAM_RAW, 'url'),
            'dataoptions' => new external_value(PARAM_RAW, 'The data for the service'),
            'totalcount' => new external_value(PARAM_INT, 'total number of challenges in result set'),
            'filterdata' => new external_value(PARAM_RAW, 'total number of challenges in result set'),
            'length' => new external_value(PARAM_RAW, 'total number of challenges in result set'),
            'records' => new external_single_structure(
                    array(
                        'hascourses' => new external_multiple_structure(
                          new external_single_structure(
                              array(
                                'trainername' => new external_value(PARAM_RAW, 'name'),
                                'email' => new external_value(PARAM_RAW, 'email'),
                                'requesttype'=> new external_value(PARAM_RAW, 'requesttype'),
                                'assigndate' => new external_value(PARAM_RAW, 'assigndate'),
                               
            
                              )
                          )
                          
                      ),
                      'request_view' => new external_value(PARAM_INT, 'request_view', VALUE_OPTIONAL),
                      'nocourses' => new external_value(PARAM_BOOL, 'nocourses', VALUE_OPTIONAL),
                      'totalusers' => new external_value(PARAM_INT, 'totalusers', VALUE_OPTIONAL),
                      'length' => new external_value(PARAM_INT, 'length', VALUE_OPTIONAL),
                  )
              )
          ]);
    }  
    
    // deleterequest.....renu

    public static function deleterequest_parameters(){

          return new external_function_parameters(
            array(
            
                'id' => new external_value(PARAM_INT,'Request id',0),
                'requesttype' => new external_value(PARAM_RAW,'Requesttype',0),
                
            )
        );
    }
    public static  function deleterequest($id,$requesttype){
       
        global $DB;
      
        $systemcontext = context_system::instance();
        self::validate_context($systemcontext);

        
        if ($requesttype == 'Trainer') {
            
            $trainer =$DB->delete_records('local_trainer_request',array('id'=>$id));
      
        } 
        
        else if($requesttype == 'Expert'){

            $expert =$DB->delete_records('local_expert_request',array('id'=>$id));
        }

        else {
         
            throw new moodle_exception('Error in deletion');
        }
        return true;    
    }
    public static function deleterequest_returns() {
        return new external_value(PARAM_BOOL, 'return');
    } 
    


     //fast User enrol

    public static function fast_userapprovalenrolview_parameters() {
        return new external_function_parameters([
            'options' => new external_value(PARAM_RAW, 'The paging data for the service'),
            'dataoptions' => new external_value(PARAM_RAW, 'The data for the service'),
            'offset' => new external_value(PARAM_INT, 'Number of items to skip from the begging of the result set',
                VALUE_DEFAULT, 0),
            'limit' => new external_value(PARAM_INT, 'Maximum number of results to return',
                VALUE_DEFAULT, 0),
            'contextid' => new external_value(PARAM_INT, 'contextid'),
            'filterdata' => new external_value(PARAM_RAW, 'filters applied'),
        ]);
  }
    public static function fast_userapprovalenrolview($options, $dataoptions, $offset = 0, $limit = 0, $contextid, $filterdata) {
        global $CFG;
        require_login();
        // Parameter validation.
        $params = self::validate_parameters(
            self::fast_userapprovalenrolview_parameters(),
            [
                'options' => $options,
                'dataoptions' => $dataoptions,
                'offset' => $offset,
                'limit' => $limit,
                'contextid' => $contextid,
                'filterdata' => $filterdata,
               
                
            ]
        );
        $offset = $params['offset'];
        $limit = $params['limit'];
        $filtervalues = json_decode($filterdata);
        $stable = new \stdClass();
        $stable->thead = false;
        $stable->start = $offset;
        $stable->length = $limit;
        $data = (new local_userapproval\action\manageuser)->getfastuserapprovalenrol($stable, $filtervalues);
        $totalcount = $data['totalrecords'];
        return [
            'totalcount' => $totalcount,
            'length' => $totalcount,
            'filterdata' => $filterdata,
            'records' =>$data,
            'options' => $options,
            'dataoptions' => $dataoptions,
            'url' => $CFG->wwwroot,
        ];
    }

    /**
     * Returns description of method result value
     * @return external_description
     */

    public static function fast_userapprovalenrolview_returns() {
         return new external_single_structure([
            'options' => new external_value(PARAM_RAW, 'The paging data for the service'),
            'url' => new external_value(PARAM_RAW, 'url'),
            'dataoptions' => new external_value(PARAM_RAW, 'The data for the service'),
            'totalcount' => new external_value(PARAM_INT, 'total number of challenges in result set'),
            'filterdata' => new external_value(PARAM_RAW, 'total number of challenges in result set'),
            'length' => new external_value(PARAM_RAW, 'total number of challenges in result set'),
            'records' => new external_single_structure(
                    array(
                        'hasuserenrol' => new external_multiple_structure(
                            new external_single_structure(
                                array(
                        
                                    'username' => new external_value(PARAM_RAW, 'username'),
                                    'email' => new external_value(PARAM_RAW, 'email'),
                                    'phonenumber' => new external_value(PARAM_RAW, 'phonenumber'),
                                    'idnumber' => new external_value(PARAM_RAW, 'idnumber'),
                                    'errormessage' => new external_value(PARAM_RAW, 'errormessage'),
                                    'id' => new external_value(PARAM_RAW, 'id'),
                                    'timecreated' => new external_value(PARAM_RAW, 'timecreated'),
                                    'timemodified' => new external_value(PARAM_RAW, 'timemodified'),
                                    'actionbtn' => new external_value(PARAM_BOOL, 'actionbtn'),
                                )
                            )
                        ),
                        'noexamenrol' => new external_value(PARAM_BOOL, 'noexamenrol', VALUE_OPTIONAL),
                        'totalcount' => new external_value(PARAM_INT, 'totalexamenrol', VALUE_OPTIONAL),
                       'length' => new external_value(PARAM_RAW, 'total number of challenges in result set'),
                        
                    )
                )
        ]);
    } 

    public static function get_user_roles_parameters() {
        return new external_function_parameters([]);
    }
    public static function get_user_roles() {
        global $OUTPUT, $PAGE, $CFG, $DB;
        $PAGE->set_context(context_system::instance());
        $context = context_system::instance();
        
        $availableroles = get_switchable_roles($context, ROLENAME_BOTH);
        $roles = (new local_userapproval\action\manageuser)->get_user_switchrols($availableroles);
        
        return $return = ['roles' => $roles];
    }
    public static function get_user_roles_returns(){
        return new external_single_structure([
            'roles' => new external_value(PARAM_RAW, 'status'),
        ]);
    }

    //individual requested data .......renu


    public static function individualrequesteddata_parameters() {
        return new external_function_parameters([
            'options' => new external_value(PARAM_RAW, 'The paging data for the service'),
            'dataoptions' => new external_value(PARAM_RAW, 'The data for the service'),
            'offset' => new external_value(PARAM_INT, 'Number of items to skip from the begging of the result set',
                VALUE_DEFAULT, 0),
            'limit' => new external_value(PARAM_INT, 'Maximum number of results to return',
                VALUE_DEFAULT, 0),
            'contextid' => new external_value(PARAM_INT, 'contextid'),
            'filterdata' => new external_value(PARAM_RAW, 'filters applied'),
        ]);
    }
    public static function individualrequesteddata($options, $dataoptions, $offset = 0, $limit = 0, $contextid, $filterdata) {
        global $DB, $PAGE, $CFG;
        
        $params = self::validate_parameters(
            self::individualrequesteddata_parameters(),
            [
                'options' => $options,
                'dataoptions' => $dataoptions,
                'offset' => $offset,
                'limit' => $limit,
                'contextid' => $contextid,
                'filterdata' => $filterdata,
            ]
        );
        $offset = $params['offset'];
        $limit = $params['limit'];
        $filtervalues = json_decode($filterdata);
        $stable = new \stdClass();
        $stable->thead = false;
        $stable->start = $offset;
        $stable->length = $limit;
        $data = (new local_userapproval\action\manageuser)->list_of_individualsrequest($stable, $filtervalues);
        
        $totalcount = $data['totalusers'];
        return [
           'totalcount' => $totalcount,
            'length' => $totalcount,
            'filterdata' => $filterdata,
            'records' =>$data,
            'options' => $options,
            'dataoptions' => $dataoptions,
            'url' => $CFG->wwwroot,
        ];
    }
    public static function individualrequesteddata_returns() {
        return new external_single_structure([
          'options' => new external_value(PARAM_RAW, 'The paging data for the service'),
          'url' => new external_value(PARAM_RAW, 'url'),
          'dataoptions' => new external_value(PARAM_RAW, 'The data for the service'),
          'totalcount' => new external_value(PARAM_INT, 'total number of challenges in result set'),
          'filterdata' => new external_value(PARAM_RAW, 'total number of challenges in result set'),
          'length' => new external_value(PARAM_RAW, 'total number of challenges in result set'),
          'records' => new external_single_structure(
                  array(
                      'hascourses' => new external_multiple_structure(
                        new external_single_structure(
                            array(
                                'id' => new external_value(PARAM_INT, 'id'),
                                'luserid' => new external_value(PARAM_INT, 'luserid'),
                                'role' => new external_value(PARAM_RAW, 'requesttype'),
                                'fieldoftraining' => new external_value(PARAM_RAW, 'fieldoftraining'),
                                'status' => new external_value(PARAM_RAW, 'status'),
                                'document' => new external_value(PARAM_RAW, 'document'),
                                'requestdate' => new external_value(PARAM_RAW, 'requestdate'),
                               
                            )
                        )
                    ),
                    'request_view' => new external_value(PARAM_INT, 'request_view', VALUE_OPTIONAL),
                    'nocourses' => new external_value(PARAM_BOOL, 'nocourses', VALUE_OPTIONAL),
                    'totalusers' => new external_value(PARAM_INT, 'totalusers', VALUE_OPTIONAL),
                    'length' => new external_value(PARAM_INT, 'length', VALUE_OPTIONAL),
                    
                )
            )
        ]);
    }



}
