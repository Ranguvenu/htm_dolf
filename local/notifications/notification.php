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
 * @package local_notifications
 */
require_once($CFG->dirroot.'/local/notifications/lib.php');

use local_notifications\local\smsapi;

/**
 * class for notification trigger
 *
 * @package   local_notifications
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class notification_triger {
    
    /* type of notiifcation*/
    private $type;
    
    /**
  * constructor for the notification trigger
  *
  * @param string $type type of notification
  */
    function __construct($type){
        $this->type = $type;
    }

    public function send_emaillog_notifications(){
        global $DB, $CFG;

        require_once($CFG->dirroot.'/lib/moodlelib.php');

        $smsapi = new local_notifications\local\smsapi();

        $starttime = strtotime(date('d/m/Y', time()));
        $endtime = $starttime+86399;

        $supportuser = \core_user::get_support_user();
 
        $logs = $DB->get_records_sql("SELECT *  FROM {local_emaillogs} WHERE status !=:status AND FROM_UNIXTIME(timecreated,'%Y-%m-%d') = CURDATE() ", array('status' => 1));
                
        foreach($logs as $email_log){
            
            $record = new stdClass();
            $record->id = $email_log->id;
            $record->from_userid = $email_log->from_userid;
            $record->to_userid = $email_log->to_userid;
            $record->from_emailid = $email_log->from_emailid;
            $record->to_emailid = $email_log->to_emailid;
            $record->ccto = $email_log->ccto;

            if(!empty($email_log->emailbody) && !empty($email_log->arabic_body)){

                $record->subject = $email_log->subject;
                $record->emailbody = $email_log->emailbody;
                $record->arabic_subject = $email_log->arabic_subject;
                $record->arabic_body = $email_log->arabic_body;

            }elseif(!empty($email_log->emailbody)){

                $record->subject = $email_log->subject;
                $record->emailbody = $email_log->emailbody;
                $record->arabic_subject = $record->subject;
                $record->arabic_body = $record->emailbody;

            }elseif(!empty($email_log->arabic_body)){

              
                $record->arabic_subject = $email_log->arabic_subject;
                $record->arabic_body = $email_log->arabic_body;
                $record->subject = $record->arabic_subject;
                $record->emailbody = $record->arabic_body;

            }

            $record->status = 0;
            $record->usercreated = $email_log->usercreated;
            $record->timecreated = $email_log->timecreated;
            $record->sent_date = time();
            $record->sent_by = $supportuser->id;
            $body = '';

           $touser = $DB->get_record('user', array('id'=>$record->to_userid,'email'=>$email_log->to_emailid));
            
            
        

            $from_user=\core_user::get_support_user();

            if(empty($touser)){// check for not sending emails to deleted users

                $touser = new stdClass();
                $touser->email = $email_log->to_emailid;
                $touser->firstname = $email_log->to_emailid;
                $touser->lastname = '';
                $touser->maildisplay = true;
                $touser->mailformat = 1; // 0 (zero) text-only emails, 1 (one) for HTML/Text emails.
                $touser->id = -99;
                $touser->firstnamephonetic = '';
                $touser->lastnamephonetic = '';
                $touser->middlename = '';
                $touser->alternatename = '';
               
                $ccemails = json_decode($email_log->ccusers);
                
               
                if($ccemails){
               
                   $touser->ccusers = $ccemails;
                    

                }
             
             
                $localtouser=$DB->get_record('local_users',array('email'=>$email_log->to_emailid));

                $mainadminuser = get_admin();

                if($localtouser){

                    $touser->firstname = $localtouser->firstname;
                    $touser->lastname = $localtouser->lastname;


                    if($localtouser->lang == 'ar'){



                         $sendmail = email_to_user($touser, $from_user,$record->arabic_subject,html_to_text($record->arabic_body),$record->arabic_body);

                        if(!$sendmail){

                            $record->status=2;

                        }else{

                            $record->status = 1;

                        }


                    }else{
                       


                        $sendmail = email_to_user($touser, $from_user, $record->subject,html_to_text($record->emailbody),$record->emailbody);

                        if(!$sendmail){

                            $record->status=2;

                        }else{

                            $record->status = 1;

                        }

                    }
                }elseif($mainadminuser->id == $record->to_userid){
         
            


                    $sendmail = email_to_user($touser, $from_user, $record->subject,html_to_text($record->emailbody),$record->emailbody);

                    if(!$sendmail){

                        $record->status=2;

                    }else{

                        $record->status = 1;

                    }
                }

            }else{
           
                $get_notification_infoid = $DB->get_field('local_notification_info','notificationid',array('id'=>$email_log->notification_infoid));

                $get_local_notification_type = $DB->get_record('local_notification_type',array('id'=>$get_notification_infoid));

                $message = new \core\message\message();
                $message->component = $get_local_notification_type->plugintype.'_'.$get_local_notification_type->pluginname;
                $message->name = $get_local_notification_type->shortname;
                $message->userfrom = $from_user;
                $message->userto = $touser;
                $message->notification = 1;
                $message->courseid = 1;

                if($touser->lang == 'ar'){

                    $message->subject = $record->arabic_subject;
                    $message->fullmessage =  html_to_text($record->arabic_body);
                    $message->fullmessageformat = FORMAT_HTML;
                    $message->fullmessagehtml = $record->arabic_body;
                    $message->smallmessage =  $record->arabic_subject;
               
                    $ccemails = json_decode($email_log->ccusers);
                
               
                    if($ccemails){
                   
                       $touser->ccusers = $ccemails;
                        
    
                    }
                    if($touser->ccusers){    
                      
                    $message->userto->ccusers = $touser->ccusers;
                    }                  

                    $messageid = message_send($message);

                    if(!$messageid){

                       $record->status=2;

                    }else{

                        $record->status = 1;

                    }


                }else{

                    $message->subject = $record->subject;
                    $message->fullmessage =  html_to_text($record->emailbody);
                    $message->fullmessageformat = FORMAT_HTML;
                    $message->fullmessagehtml = $record->emailbody;
                    $message->smallmessage =  $record->subject;

                    $ccemails = json_decode($email_log->ccusers);
                
               
                    if($ccemails){
                   
                       $touser->ccusers = $ccemails;
                        
    
                    }
                    if($touser->ccusers){
                        $message->userto->ccusers  = $touser->ccusers;
                    }        
                    $messageid = message_send($message);

                    if(!$messageid){

                       $record->status=2;

                    }else{

                       $record->status = 1;

                    }

                }
            }
            $record->realuser =($USER->realuser) ? $USER->realuser :0;
            $DB->update_record('local_emaillogs',$record);

        }


        $smslogs =$DB->get_records_sql("SELECT *  FROM {local_smslogs} WHERE status !=:status AND FROM_UNIXTIME(timecreated,'%Y-%m-%d') = CURDATE() ", array('status' => 1));

        foreach($smslogs as $sms_log){
            $record = new stdClass();
            $record->id = $sms_log->id;
            $record->to_userid = $sms_log->to_userid;
            $record->to_phonenumber=$sms_log->to_phonenumber;

            if(!empty($sms_log->english_smstext) && !empty($sms_log->arabic_smstext)){

                $record->english_smstext = $sms_log->english_smstext;
                $record->arabic_smstext = $sms_log->arabic_smstext;

            }elseif(!empty($sms_log->english_smstext)){

                $record->english_smstext = $sms_log->english_smstext;
                $record->arabic_smstext = $record->english_smstext;

            }elseif(!empty($sms_log->arabic_smstext)){

                $record->arabic_smstext = $sms_log->arabic_smstext;

                $record->english_smstext = $record->arabic_smstext;

            }

            $record->usercreated = $sms_log->usercreated;
            $record->timecreated = $sms_log->timecreated;
            $record->sent_date = time();
            $record->sent_by = $supportuser->id;
            $body = '';
            $record->status = 0;
            
            $touser = $DB->get_record('user', array('id'=>$record->to_userid,'phone1'=>$sms_log->to_phonenumber));


            if(empty($touser)){// check for not sending emails to deleted users
          

                $localtouser=$DB->get_record('local_users',array('phone1'=>$sms_log->to_phonenumber));

                if($localtouser){

                    if($localtouser->lang == 'ar'){

                        $sendsms =$smsapi->sendsms(html_to_text($record->arabic_smstext),$record->to_phonenumber);

                        $record->response_result=$sendsms;

                        if(!$sendsms){

                            $record->status=2;

                        }else{

                            if(isset($sendsms->success) && $sendsms->success == 1){

                                $record->status = 1;

                            }else{

                                $record->status = 2;
                            }

                            $record->response_result=json_encode($sendsms);

                        }

                    }else{

                        $sendsms =$smsapi->sendsms(html_to_text($record->english_smstext),$record->to_phonenumber);

                        if(!$sendsms){

                            $record->status=2;

                        }else{

                            if(isset($sendsms->success) && $sendsms->success == 1){

                                $record->status = 1;

                            }else{

                                $record->status = 2;
                            }

                            $record->response_result=json_encode($sendsms);

                        }

                    }

                }

            }else{
           

                if($touser->lang == 'ar'){

                    $sendsms =$smsapi->sendsms(html_to_text($record->arabic_smstext),$record->to_phonenumber);

                    if(!$sendsms){

                        $record->status=2;

                    }else{

                        if(isset($sendsms->success) && $sendsms->success == 1){

                            $record->status = 1;

                        }else{

                            $record->status = 2;
                        }

                        $record->response_result=json_encode($sendsms);

                    }

                }else{


                    $sendsms =$smsapi->sendsms(html_to_text($record->english_smstext),$record->to_phonenumber);

                    if(!$sendsms){

                        $record->status=2;

                    }else{

                        if(isset($sendsms->success) && $sendsms->success == 1){

                            $record->status = 1;

                        }else{

                            $record->status = 2;
                        }

                        $record->response_result=json_encode($sendsms);

                    }

                }
            }
            $record->realuser = ($USER->realuser) ? $USER->realuser :0;
            $DB->update_record('local_smslogs',$record);

        }

    }    
}  
