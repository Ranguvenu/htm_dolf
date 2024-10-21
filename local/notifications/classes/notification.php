<?php
/**
 * This file is part of eAbyas
 *
 * Copyright eAbyas Info Solutons Pvt Ltd, India
 *
 * This program is free software; you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation; either version 3 of the License, or
 * (at your option) any later version.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with this program.  If not, see <http://www.gnu.org/licenses/>.
 *
 * @author eabyas  <info@eabyas.in>
 * @package local_notification
 */
namespace local_notifications;

global $CFG;
require_once($CFG->libdir . '/filelib.php');

use context_system;
use curl;

class notification{

    public $db;
    public $user;
    public function __construct($db=null, $user=null){
        global $DB, $USER;
        $this->db = $db ? $db :$DB;
        $this->user = $user ? $user :$USER;
    }

    public function get_existing_notification($notificationinstance, $emailtype){
        $corecomponent = new \core_component();
        $params = array();
        $notification_typesql = "SELECT lni.*,lnt.plugintype,lnt.pluginname FROM {local_notification_info} AS lni
            JOIN {local_notification_type} AS lnt ON lnt.id=lni.notificationid
            WHERE concat(',',lni.moduleid,',') LIKE concat('%,',:moduleid,',%') AND lnt.shortname LIKE :emailtype AND lni.active=1 ";
        $params['moduleid'] = $notificationinstance->id;
        $params['emailtype'] = $emailtype;
 
        $notification = $this->db->get_record_sql($notification_typesql, $params);
        if(empty($notification)){ // sends the default notification for the type.
            $params = array();
            $notification_typesql = "SELECT lni.*,lnt.plugintype,lnt.pluginname FROM {local_notification_info} AS lni
                JOIN {local_notification_type} AS lnt ON lnt.id=lni.notificationid
                WHERE (lni.moduleid IS NULL OR lni.moduleid LIKE '0')
                AND lnt.shortname LIKE :emailtype AND lni.active=1 ";
            $params['emailtype'] = $emailtype;
     
            $notification = $this->db->get_record_sql($notification_typesql, $params);
        }
        if(empty($notification)){
            return false;
        }else{
            return $notification;
        }
    }
    public function log_email_notification($touser, $fromuser, $datamailobj){

        global $DB, $USER;

        if(!$touser){
            return true;
        }
        $dataobject = clone $datamailobj;
        

        if(isset($datamailobj->userlang) && !empty($datamailobj->userlang)){

            if($datamailobj->userlang == 'ar'){


                $dataobject->arabic_subject = $this->replace_strings($datamailobj, $datamailobj->arabic_subject);
                $arabicemailbody = $this->replace_strings($datamailobj, $datamailobj->arabic_body);
                $dataobject->arabic_body = $arabicemailbody;

                $dataobject->subject = '';
                $dataobject->emailbody = '';

            }else{

                $dataobject->subject = $this->replace_strings($datamailobj, $datamailobj->subject);
                $emailbody = $this->replace_strings($datamailobj, $datamailobj->body);
                $dataobject->emailbody = $emailbody;

                $dataobject->arabic_subject = '';
                $dataobject->arabic_body = '';

            }

        }else{

            $dataobject->subject = $this->replace_strings($datamailobj, $datamailobj->subject);
            $emailbody = $this->replace_strings($datamailobj, $datamailobj->body);
            $dataobject->emailbody = $emailbody;

            $dataobject->arabic_subject = $this->replace_strings($datamailobj, $datamailobj->arabic_subject);
            $arabicemailbody = $this->replace_strings($datamailobj, $datamailobj->arabic_body);
            $dataobject->arabic_body = $arabicemailbody;

        }

        $dataobject->from_emailid = $fromuser->email;
        $dataobject->from_userid = $fromuser->id;
        $dataobject->to_emailid = $touser->email;
        $dataobject->to_userid = $touser->id;
        $dataobject->realuser =($USER->realuser) ? $USER->realuser :0;
        $dataobject->ccto = 0;
        $dataobject->sentdate = 0;
        $dataobject->sent_by = $this->user->id;
        $dataobject->moduleid = $datamailobj->moduleid;

        $dataobject->sendsms = $datamailobj->sendsms ? $datamailobj->sendsms : 0;

        if($logid = $this->check_pending_mail_exists($touser, $fromuser, $dataobject)){
            $dataobject->id = $logid;
            $dataobject->timemodified = time();
            $dataobject->usermodified = $this->user->id;
            if($touser->ccusers){
                $dataobject->ccusers =  json_encode($touser->ccusers);

            }

            $logid = $this->db->update_record('local_emaillogs', $dataobject);

        }else{
            $dataobject->timecreated = time();
            $dataobject->usercreated = $this->user->id;      
         
            if($touser->ccusers){

                $dataobject->ccusers =  json_encode($touser->ccusers);
            }
     

            $this->db->insert_record('local_emaillogs', $dataobject);

        }        

        if($dataobject->sendsms){

            $dataobject->response_result='';

            $userinfo = $DB->get_record('user', array('id'=>$dataobject->to_userid,'email'=>$dataobject->to_emailid),'id,phone1');

            $dataobject->to_phonenumber = $userinfo?$userinfo->phone1:'';

            if(isset($datamailobj->userlang) && !empty($datamailobj->userlang)){

                if($datamailobj->userlang == 'ar'){

                    $dataobject->arabic_smstext = html_to_text($dataobject->arabic_body);

                    $dataobject->english_smstext ='';

                }else{

                    $dataobject->english_smstext = html_to_text($dataobject->emailbody);

                    $dataobject->arabic_smstext ='';

                }
            }else{

                $dataobject->arabic_smstext = html_to_text($dataobject->arabic_body);

                $dataobject->english_smstext = html_to_text($dataobject->emailbody);

            }

            if($smslogid = $this->check_pending_sms_exists($touser, $fromuser, $dataobject)){

                    $dataobject->id = $smslogid;
                    $dataobject->timemodified = time();
                    $dataobject->usermodified = $this->user->id;
                    $dataobject->realuser = ($USER->realuser) ? $USER->realuser :0;

                    $this->db->update_record('local_smslogs', $dataobject);
            }else{

                $dataobject->timecreated = time();
                $dataobject->usercreated = $this->user->id;
                $dataobject->realuser = ($USER->realuser) ? $USER->realuser :0;

                $this->db->insert_record('local_smslogs', $dataobject);

            }
        }

        if($extra_trigger_roles=$datamailobj->extra_trigger_roles){

            foreach($extra_trigger_roles as $roleshortname){

                $roleusers=$this->getsystemlevel_role_users($roleshortname,$datamailobj->organizationroles);

                if($roleusers){

                    foreach($roleusers as $roleuser){

                            $dataobject->to_emailid = $roleuser->email;
                            $dataobject->to_userid = $roleuser->id;

                        if($logid = $this->check_pending_mail_exists($roleuser, $fromuser, $dataobject)){
                            $dataobject->id = $logid;
                            $dataobject->timemodified = time();
                            $dataobject->usermodified = $this->user->id;
                            $dataobject->realuser =($USER->realuser) ? $USER->realuser :0;


                            $logid = $this->db->update_record('local_emaillogs', $dataobject);

                        }else{
                            $dataobject->timecreated = time();
                            $dataobject->usercreated = $this->user->id;
                            $dataobject->realuser =($USER->realuser) ? $USER->realuser :0;

                            $this->db->insert_record('local_emaillogs', $dataobject);
                        }

                        if($dataobject->sendsms){

                            $dataobject->response_result='';

                            $userinfo = $DB->get_record('user', array('id'=>$dataobject->to_userid,'email'=>$dataobject->to_emailid),'id,phone1');

                            $dataobject->to_phonenumber = $userinfo->phone1;

                            if($smslogid = $this->check_pending_sms_exists($touser, $fromuser, $dataobject)){

                                    $dataobject->id = $smslogid;
                                    $dataobject->timemodified = time();
                                    $dataobject->usermodified = $this->user->id;
                                    $dataobject->realuser =($USER->realuser) ? $USER->realuser :0;

                                    $this->db->update_record('local_smslogs', $dataobject);
                            }else{

                                $dataobject->timecreated = time();
                                $dataobject->usercreated = $this->user->id;
                                $dataobject->realuser =($USER->realuser) ? $USER->realuser :0;

                                $this->db->insert_record('local_smslogs', $dataobject);

                            }
                        }

                    }
                }


            }
        }
    }
    public function check_pending_mail_exists($user, $fromuser, $dataobject){

        global $DB, $USER;

        $mainadminuser = get_admin();

        if($mainadminuser->id != $user->id){

            $sql =  " SELECT id FROM {local_emaillogs} WHERE to_userid = :userid AND notification_infoid = :infoid AND (".$DB->sql_compare_text('emailbody')." = ".$DB->sql_compare_text(':emailbody')." AND ".$DB->sql_compare_text('arabic_body')." = ".$DB->sql_compare_text(':arabic_body').") ";
            $params['userid'] = $user->id;
            $params['emailbody'] = $dataobject->emailbody;
            $params['arabic_body'] = $dataobject->arabic_body;
            $params['infoid'] = $dataobject->notification_infoid;

            return $this->db->get_field_sql($sql ,$params);

        }else{

            return false;
        }

    }

     public function check_pending_sms_exists($user, $fromuser, $dataobject){

        global $DB, $USER;

        $sql =  " SELECT id FROM {local_smslogs} WHERE to_userid = :userid AND notification_infoid = :infoid AND (".$DB->sql_compare_text('arabic_smstext')." = ".$DB->sql_compare_text(':arabic_smstext')." OR ".$DB->sql_compare_text('english_smstext')." = ".$DB->sql_compare_text(':english_smstext').") ";
        $params['userid'] = $user->id;
        $params['arabic_smstext'] = $dataobject->arabic_smstext;
        $params['english_smstext'] = $dataobject->english_smstext;
        $params['infoid'] = $dataobject->notification_infoid;

        return $this->db->get_field_sql($sql ,$params);
    }

    public function replace_strings($dataobject, $data){


        $classlib = ''.$dataobject->plugintype.'_' .$dataobject->pluginname.'\notification';

        $lib = new $classlib();

        $strings = $lib->get_string_identifiers($dataobject->emailtype);

        $strings = explode(',', $strings);
        if($strings){
            foreach($strings as $string){
                $string = trim($string);
                foreach($dataobject as $key => $dataval){
                    $key = '['.$key.']';
                    if("$string" == "$key"){
                        $data = str_replace("$string", "$dataval", $data);
                    }
                }
            }
        }

        return $data;
    }
    /**
    * create / update notification template
    *
    * @param string $table
    * @param int $action insert / update value
    * @param object $dataobject object containing notiifcation info
    * @return boolean true / false based on db execution
    */
    public function insert_update_record($table, $action, $dataobject){
        global $DB;
        if($action == 'insert'){
            $systemcontext = \context_system::instance();
            $str=$dataobject->body;
            $keywords = preg_split("/[\s,]+/", $dataobject->body);
            $keywords2 = preg_split("/[\s,]+/", $keywords[1]);
            $pieces = explode("/", $keywords2[0]);          
            file_save_draft_area_files($pieces[8], $systemcontext->id, 'local', 'notifications',$pieces[8], array('maxfiles' => 5));
            $result = $DB->insert_record("$table", $dataobject);
        } elseif($action == 'update') {
             $systemcontext = \context_system::instance();
            $str=$dataobject->body;
            $keywords = preg_split("/[\s,]+/", $dataobject->body);
            $keywords2 = preg_split("/[\s,]+/", $keywords[1]);
            $pieces = explode("/", $keywords2[0]);  
            file_save_draft_area_files($pieces[8], $systemcontext->id, 'local', 'notifications',$pieces[8], array('maxfiles' => 5));
             $DB->update_record("$table", $dataobject);
             $result =$dataobject->id;
        }else{
            $result = false;
        }
        return $result;
    }
    //////For display on index page//////////
    public function notification_details($tablelimits, $filtervalues){
        global $DB, $PAGE,$USER,$CFG,$OUTPUT;
        $systemcontext = \context_system::instance();

        $countsql = "SELECT count(ni.id)
                FROM {local_notification_info} AS ni
                JOIN {local_notification_type} AS nt ON ni.notificationid = nt.id";
    
        $selectsql = "SELECT ni.id, nt.name, nt.shortname,nt.arabicname, ni.subject, ni.arabic_subject, ni.moduleid, ni.active
                FROM {local_notification_info} ni
                JOIN {local_notification_type} nt ON ni.notificationid = nt.id
               
            WHERE 1=1 ";
        $queryparam = array();
        if(!is_siteadmin() && !has_capability('local/organization:manage_communication_officer',$systemcontext)){

            print_error('You dont have permissions to view this page.');
            die();
        }

        if(!empty($filtervalues->notificationid)){
            $notificationslis = explode(',', $filtervalues->notificationid);
            list($notifysql, $notifyparam) = $DB->get_in_or_equal($notificationslis, SQL_PARAMS_NAMED);
            $concatsql .= " AND ni.notificationid $notifysql ";
            $queryparam = array_merge($queryparam,$notifyparam);
        }

        $count = $DB->count_records_sql($countsql.$concatsql, $queryparam);

        $concatsql.=" order by ni.id desc";
        $notifications_info = $DB->get_records_sql($selectsql.$concatsql, $queryparam, $tablelimits->start, $tablelimits->length);

        $list=array();
        $data = array();
        if ($notifications_info) {
            foreach ($notifications_info as $each_notification) { 
                

               $list['notification_id'] = $each_notification->id;
               $list['contextid'] = $systemcontext->id;
                $lang= current_language();
                if($lang == 'ar'){
                    $list['notification_type'] = $each_notification->arabicname;
                }else{
                    $list['notification_type'] = $each_notification->name;
                }
              
               $list['code'] = $each_notification->shortname;
               $list['subject'] = $each_notification->subject;
               $list['arabic_subject'] = $each_notification->arabic_subject;
               
               $data[] = $list;
            }
        }
        return array('count' => $count, 'data' => $data); 
    }
    public function getsystemlevel_role_users($roleshortname='',$organizationroles=false){

        global $DB;

        $params=array();
        $context = context_system::instance();

         $sql = "SELECT u.*
                FROM {role} r 
                JOIN {role_assignments} ra ON ra.roleid = r.id AND ra.contextid = $context->id
                JOIN {user} u ON  u.id =ra.userid 
                JOIN {local_users} AS lc ON lc.userid = u.id
                WHERE u.deleted = 0  AND u.suspended = 0 ";

        if($roleshortname){

            $sql.=" AND r.shortname =:shortname ";

            $params['shortname']=$roleshortname;

        }  
        if($organizationroles){

            $sql.= " AND lc.organization =:organization ";

            $params['organization']=$organizationroles;

        }          
        $sql.=" ORDER BY ra.id ASC ";

        $systemlevelroleusers = $DB->get_records_sql($sql, $params);

        return $systemlevelroleusers;
    } 
    public function gettrainingprogram_users($programid){

        global $DB, $PAGE, $OUTPUT;

        $selectsql = "SELECT u.* 
                        FROM {user} u 
                        JOIN {program_enrollments} pe ON pe.programid=$programid AND pe.userid=u.id  
                        WHERE  u.deleted = 0 AND u.suspended = 0 ";

        $formsql .=" ORDER BY u.id DESC";
        $users = $DB->get_records_sql($selectsql.$formsql, $params);

        return $users;
    } 
    public function getexam_users($examid){

        global $DB, $PAGE, $OUTPUT;

        $selectsql = "SELECT u.* 
                        FROM {user} u 
                        JOIN {exam_enrollments} exe ON exe.examid=$examid AND exe.userid=u.id  
                        WHERE  u.deleted = 0 AND u.suspended = 0 ";

        $formsql .=" ORDER BY u.id DESC";
        $users = $DB->get_records_sql($selectsql.$formsql, $params);

        return $users;
    } 
    public static function get_integrations_sms_token() {


        $curl = new curl();
        $url = get_config('local_notifications', 'smsapiauthenticateurl');

 
        $curl->setHeader(array('Content-type: application/json'));
        

        $params = [
                'userName' => get_config('local_notifications', 'smsapiauthenticateusername'),
                'password' =>  get_config('local_notifications', 'smsapiauthenticatepassword')
        ];
        $post = json_encode($params);
            
 
        $curl->setHeader(array('Accept: application/json', 'Expect:'));

        $response = $curl->post($url, $post);
        
        $response = json_decode($response);
    

        return $response;

    }
    public static function lms_integrations_sms($tokenkey,$phonenumber,$text) {


        $curl = new curl();
        $url = get_config('local_notifications', 'smsapicccounturl');

        $curl->setHeader(array('Content-type: application/json'));
 
        $curl->setHeader('Authorization: Bearer '.$tokenkey);
        

        $params = [
                'number' => $phonenumber,
                'text' => $text,
                'createdBy' => 'fa'
        ];
        $post = json_encode($params);
            
 
        $curl->setHeader(array('Accept: application/json', 'Expect:'));

        $response = $curl->post($url, $post);
        
        $response = json_decode($response);

        return $response;

    }

}
