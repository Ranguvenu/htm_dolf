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
 * @package local_trainingprogram
 */

namespace local_trainingprogram;

class notification extends \local_notifications\notification
{
    public $db;
    public $user;
    // public function __construct($db=null, $user=null){
    // 	global $DB, $USER;
    // 	$this->db = $db ? $db :$DB;
    // 	$this->user = $user ? $user :$USER;
    // }
    public function get_string_identifiers($emailtype)
    {
        switch ($emailtype) {
            case 'trainingprogram_create':
                $strings = "[program_name],[program_userfullname]";
                break;

            case 'trainingprogram_update':
                $strings = "[program_name],[program_userfullname]";
                break;

            case 'trainingprogram_enroll':
                $strings = "[program_name],[program_userfullname],[program_trainingoff]";
                break;
        
            case 'trainingprogram_completion':
                $strings = "[program_name],[program_userfullname]";
                break;
            case 'trainingprogram_certificate_assignment':

                $strings = "[program_name],[program_userfullname],[program_certificatelink]";

                break;
            case 'trainingprogram_before_7_days':
                $strings = " [trainingprogram_related_module_name],[trainingprogram_userfullname],[trainingprogram_related_module_date],[trainingprogram_related_module_time]";
                break;          
            case 'trainingprogram_before_48_hours':
                $strings = " [trainingprogram_related_module_name],[trainingprogram_userfullname],[trainingprogram_related_module_date],[trainingprogram_related_module_time]";
                break;
            case 'trainingprogram_before_24_hours':
                $strings = " [trainingprogram_related_module_name],[trainingprogram_userfullname],[trainingprogram_related_module_date],[trainingprogram_related_module_time]";
                break;
            case 'trainingprogram_before_30_minutes':
                $strings = " [trainingprogram_related_module_name],[trainingprogram_userfullname]";
                break;
            case 'trainingprogram_enrolled_inactive_accounts':
                $strings = " [program_name],[program_userfullname],[program_link]";
                break;
            case 'trainingprogram_pre_assessment_opened':
                $strings = " [program_name],[program_userfullname],[assessment_start_date],[assessment_end_date],[assessment_link]";
                break;
            case 'trainingprogram_pre_assessment_closed':
                $strings = " [program_name],[program_userfullname],[assessment_start_date],[assessment_end_date],[assessment_link]";
                break;
            case 'trainingprogram_post_assessment_closed':
                $strings = " [program_name],[program_userfullname],[assessment_start_date],[assessment_end_date],[assessment_link]";
                break;
            case 'trainingprogram_post_assessment_opened':
                $strings = " [program_name],[program_userfullname],[assessment_start_date],[assessment_end_date],[assessment_link]";
                break;
            case 'trainingprogram_assignment_deadline_4_hours':
                $strings = " [program_name],[program_userfullname]";
                break;
            case 'trainingprogram_assignment_deadline_24_hours':
                $strings = " [program_name],[program_userfullname]";
                break;
            case 'trainingprogram_send_conclusion':
                $strings = " [trainingprogram_related_module_name],[trainingprogram_userfullname]";
                break;
            case 'trainingprogram_after_session':
                $strings = " [trainingprogram_related_module_name],[trainingprogram_userfullname]";
                break;
            case 'trainingprogram_unenroll':
                $strings = "[program_name],[program_userfullname],[program_date],[program_time]";
                break;
            case 'trainingprogram_before_10_days':
                       $strings = "[program_name],[program_userfullname],[program_date],[program_time]";
                        break;
            case 'trainee_tp_enrollment':
                            $strings = "[program_trainingoff], [program_userfullname],[program_name]";
                            break;  
             case 'bulkenrol_program':
                                $strings = "[org_off], [trainee_details],[program_name],[offering_startdate],[offering_starttime],[offering_enddate],[offering_endtime]";
                                break;  
                 
              case 'tp_org_traineeenroll':
                         $strings = "[program_orgofficial], [program_userfullname],[program_name]";
                             break;  

            case 'trainer_tp_enrollment':
                   $strings = "[program_userfullname],[program_name]";
                    break;  
            
            case 'trainingprogram_reschedule':
                    $strings = "[program_userfullname],[program_name],[offering_pastdate],[offering_pasttime],[offering_presentdate],[offering_presenttime] ";
                         break; 
            case 'trainingprogram_cancelrequest':
                   $strings = "[program_userfullname],[program_name],[program_tofullname],[program_canceltime] ";
                        break;                                  



                                  
        }

        return $strings;
    }
    public function trainingprogram_notification($emailtype, $touser, $fromuser, $trainingprograminstance, $waitinglistid = 0)
    {
        if ($notification = $this->get_existing_notification($trainingprograminstance, $emailtype)) {

            $functionname = 'send_' . $emailtype . '_notification';

            $this->$functionname($trainingprograminstance, $touser, $fromuser, $emailtype, $notification, $waitinglistid);  //$this->send_trainingprogram_notification($trainingprograminstance, $touser, $fromuser, $emailtype, $notification,$waitinglistid);
        }
    }
    public function send_trainingprogram_create_notification($trainingprograminstance, $touser, $fromuser, $emailtype, $notification, $waitinglistid = 0)
    { 
        global $PAGE,$DB;
        $datamailobject = new \stdClass();

        $datamailobject->emailtype = $emailtype;
        $datamailobject->notification_infoid = $notification->id;
        $datamailobject->plugintype = $notification->plugintype;
        $datamailobject->pluginname = $notification->pluginname;
        $datamailobject->moduleid = 1;             
        $datamailobject->body = $notification->body;
        $datamailobject->subject = $notification->subject;
        $datamailobject->arabic_body = $notification->arabic_body;
        $datamailobject->arabic_subject = $notification->arabic_subject;
        $datamailobject->trainingprogramid = $trainingprograminstance->id;
        $datamailobject->teammemberid = 0;
        $trainingofficialroleusers = $this->getsystemlevel_role_users('to', 0);    
        if ($trainingofficialroleusers) {          
            foreach ($trainingofficialroleusers as $trainingofficialroleuser) {
                     $datamailobject->userlang = $trainingofficialroleuser->lang;
                     if($datamailobject->userlang == 'ar'){
                        $datamailobject->program_name = $trainingprograminstance->program_arabicname;
                        $arabicusernames = $DB->get_record('local_users',array('userid'=>$trainingofficialroleuser->id));     

                     }
                     else{
                         $datamailobject->program_name = $trainingprograminstance->program_name;
                         
                     } 

                    $localuserrecord = $DB->get_record('local_users',['userid'=> $trainingofficialroleuser->id]);
                    $datamailobject->program_userfullname = ($localuserrecord)? (($trainingofficialroleuser->lang == 'ar') ? $localuserrecord->firstnamearabic.' '.$localuserrecord->middlenamearabic.' '.$localuserrecord->thirdnamearabic.' '.$localuserrecord->lastnamearabic  : $localuserrecord->firstname.' '.$localuserrecord->middlenameen.' '.$localuserrecord->thirdnameen.' '.$localuserrecord->lastname) :fullname($DB->get_record('user',array('id'=>$trainingofficialroleuser->id)));        
                $this->log_email_notification($trainingofficialroleuser, $fromuser, $datamailobject);
            }
        }
    }
    public function send_trainingprogram_update_notification($trainingprograminstance, $touser, $fromuser, $emailtype, $notification, $waitinglistid = 0)
    {
        global $PAGE,$DB;
        $datamailobject = new \stdClass();

        $datamailobject->emailtype = $emailtype;
        $datamailobject->notification_infoid = $notification->id;
        $datamailobject->plugintype = $notification->plugintype;
        $datamailobject->pluginname = $notification->pluginname;
        $datamailobject->moduleid = 1;    
        $datamailobject->body = $notification->body;
        $datamailobject->subject = $notification->subject;
        $datamailobject->arabic_body = $notification->arabic_body;
        $datamailobject->arabic_subject = $notification->arabic_subject;
        $datamailobject->trainingprogramid = $trainingprograminstance->id;
        $datamailobject->teammemberid = 0;        
        $trainingofficialroleusers = $this->getsystemlevel_role_users('to', 0);
        if ($trainingofficialroleusers) {
            foreach ($trainingofficialroleusers as $trainingofficialroleuser) {
                     $datamailobject->userlang = $trainingofficialroleuser->lang;
                     if($datamailobject->userlang == 'ar'){
                        $datamailobject->program_name = $trainingprograminstance->program_arabicname;
                        $arabicusernames = $DB->get_record('local_users',array('userid'=>$trainingofficialroleuser->id));

                     }
                     else{
                         $datamailobject->program_name = $trainingprograminstance->program_name;

                     } 

                    $localuserrecord = $DB->get_record('local_users',['userid'=> $trainingofficialroleuser->id]);
                    $datamailobject->program_userfullname = ($localuserrecord)? (($trainingofficialroleuser->lang == 'ar') ? $localuserrecord->firstnamearabic.' '.$localuserrecord->middlenamearabic.' '.$localuserrecord->thirdnamearabic.' '.$localuserrecord->lastnamearabic  : $localuserrecord->firstname.' '.$localuserrecord->middlenameen.' '.$localuserrecord->thirdnameen.' '.$localuserrecord->lastname) :fullname($DB->get_record('user',array('id'=>$trainingofficialroleuser->id)));                
                $this->log_email_notification($trainingofficialroleuser, $fromuser, $datamailobject);
            }
        }
    }
    public function send_trainingprogram_enroll_notification($trainingprograminstance, $touser, $fromuser, $emailtype, $notification, $waitinglistid = 0)
    {
        global $PAGE,$DB;
        $datamailobject = new \stdClass();

        $datamailobject->emailtype = $emailtype;
        $datamailobject->notification_infoid = $notification->id;
        $datamailobject->plugintype = $notification->plugintype;
        $datamailobject->pluginname = $notification->pluginname;
        $datamailobject->moduleid = 1;
        $datamailobject->body = $notification->body;
        $datamailobject->subject = $notification->subject;
        $datamailobject->arabic_body = $notification->arabic_body;
        $datamailobject->arabic_subject = $notification->arabic_subject;
        $datamailobject->sendsms =1;
        $datamailobject->trainingprogramid = $trainingprograminstance->id;
        $datamailobject->teammemberid = 0;
        $datamailobject->userlang=$touser->lang;
        if($datamailobject->userlang=='ar'){            
            $datamailobject->program_name = $trainingprograminstance->program_arabicname;
            $datamailobject->program_userfullname = $trainingprograminstance->program_userarabicfullname;
        }
      else{       
        $datamailobject->program_name = $trainingprograminstance->program_name;
         $datamailobject->program_userfullname = $trainingprograminstance->program_userfullname;

      }
      $trainee = $DB->get_field('role','id',array('shortname'=>'trainee'));
      $trainer = $DB->get_field('role','id',array('shortname'=>'trainer'));
      if($trainingprograminstance->roleid){
         if($trainee == $trainingprograminstance->roleid){
            $this->log_email_notification($touser, $fromuser, $datamailobject);
            // $this->trainingprogram_notification('trainee_tp_enrollment', $touser = null, $fromuser = $fromuser, $trainingprograminstance, $waitinglistid = 0);  
             if($trainingprograminstance->orgoff){
                $this->trainingprogram_notification('tp_org_traineeenroll', $touser = null, $fromuser = $fromuser, $trainingprograminstance, $waitinglistid = 0);  

             }
         }
         elseif($trainer == $trainingprograminstance->roleid) {

            $this->trainingprogram_notification('trainer_tp_enrollment', $touser = $touser, $fromuser = $fromuser, $trainingprograminstance, $waitinglistid = 0);  

         }


      }   else{
            $this->log_email_notification($touser, $fromuser, $datamailobject);      
            //$this->trainingprogram_notification('trainee_tp_enrollment', $touser = null, $fromuser = $fromuser, $trainingprograminstance, $waitinglistid = 0);
            if($trainingprograminstance->orgoff){
                $this->trainingprogram_notification('tp_org_traineeenroll', $touser = null, $fromuser = $fromuser, $trainingprograminstance, $waitinglistid = 0);  

            } 

      }


    }


    public function send_trainee_tp_enrollment_notification($trainingprograminstance, $touser, $fromuser, $emailtype, $notification, $waitinglistid = 0)
    {
        global $PAGE,$DB;
        $datamailobject = new \stdClass();

        $datamailobject->emailtype = $emailtype;
        $datamailobject->notification_infoid = $notification->id;
        $datamailobject->plugintype = $notification->plugintype;
        $datamailobject->pluginname = $notification->pluginname;
        $datamailobject->moduleid = 1;
        $datamailobject->body = $notification->body;
        $datamailobject->subject = $notification->subject;
        $datamailobject->arabic_body = $notification->arabic_body;
        $datamailobject->arabic_subject = $notification->arabic_subject;
        $datamailobject->sendsms =1;
        $datamailobject->trainingprogramid = $trainingprograminstance->id;
        $datamailobject->teammemberid = 0;

    $trainingofficialroleusers = $this->getsystemlevel_role_users('to', 0);
    if ($trainingofficialroleusers) {
        foreach ($trainingofficialroleusers as $trainingofficialroleuser) {
                 $datamailobject->userlang = $trainingofficialroleuser->lang;
                 if($datamailobject->userlang == 'ar'){
                    $datamailobject->program_name = $trainingprograminstance->program_arabicname;
                    $arabicusernames = $DB->get_record('local_users',array('userid'=>$trainingofficialroleuser->id));
                    $datamailobject->program_userfullname = $trainingprograminstance->program_userarabicfullname;       

                 }
                 else{
                     $datamailobject->program_name = $trainingprograminstance->program_name;
                     $datamailobject->program_userfullname = $trainingprograminstance->program_userfullname;                   

                 } 

                $localuserrecord = $DB->get_record('local_users',['userid'=> $trainingofficialroleuser->id]);
                $datamailobject->program_trainingoff = ($localuserrecord)? (($trainingofficialroleuser->lang == 'ar') ? $localuserrecord->firstnamearabic.' '.$localuserrecord->middlenamearabic.' '.$localuserrecord->thirdnamearabic.' '.$localuserrecord->lastnamearabic  : $localuserrecord->firstname.' '.$localuserrecord->middlenameen.' '.$localuserrecord->thirdnameen.' '.$localuserrecord->lastname) :fullname($DB->get_record('user',array('id'=>$trainingofficialroleuser->id))); 
            

            $this->log_email_notification($trainingofficialroleuser, $fromuser, $datamailobject);
        }
    }
  
 }


 public function send_tp_org_traineeenroll_notification($trainingprograminstance, $touser, $fromuser, $emailtype, $notification, $waitinglistid = 0)
 {
    global $PAGE,$DB;
    $datamailobject = new \stdClass();

    $datamailobject->emailtype = $emailtype;
    $datamailobject->notification_infoid = $notification->id;
    $datamailobject->plugintype = $notification->plugintype;
    $datamailobject->pluginname = $notification->pluginname;
    $datamailobject->moduleid = 1;
    $datamailobject->body = $notification->body;
    $datamailobject->subject = $notification->subject;
    $datamailobject->arabic_body = $notification->arabic_body;
    $datamailobject->arabic_subject = $notification->arabic_subject;
    $datamailobject->sendsms =1;
    $datamailobject->trainingprogramid = $trainingprograminstance->id;
    $datamailobject->teammemberid = 0;
    $orgofficialroleusers = $this->getsystemlevel_role_users('organizationofficial', 0);
    if($orgofficialroleusers){

        foreach ($orgofficialroleusers  as $orgofficialroleuser) {
            $datamailobject->userlang = $orgofficialroleuser->lang;
            if($datamailobject->userlang == 'ar'){
               $datamailobject->program_name = $trainingprograminstance->program_arabicname;            
               $datamailobject->program_userfullname = $trainingprograminstance->program_userarabicfullname;       

            }
            else{
                $datamailobject->program_name = $trainingprograminstance->program_name;
                $datamailobject->program_userfullname = $trainingprograminstance->program_userfullname;                   

            } 

           $localuserrecord = $DB->get_record('local_users',['userid'=> $orgofficialroleuser->id]);
           $datamailobject->program_orgofficial = ($localuserrecord)? (($orgofficialroleuser->lang == 'ar') ? $localuserrecord->firstnamearabic.' '.$localuserrecord->middlenamearabic.' '.$localuserrecord->thirdnamearabic.' '.$localuserrecord->lastnamearabic  : $localuserrecord->firstname.' '.$localuserrecord->middlenameen.' '.$localuserrecord->thirdnameen.' '.$localuserrecord->lastname) :fullname($DB->get_record('user',array('id'=>$orgofficialroleuser->id))); 
       

       $this->log_email_notification($orgofficialroleuser, $fromuser, $datamailobject);
  

         }
    }
}
public function send_trainer_tp_enrollment_notification($trainingprograminstance, $touser, $fromuser, $emailtype, $notification, $waitinglistid = 0)
{
   global $PAGE,$DB;
   $datamailobject = new \stdClass();

   $datamailobject->emailtype = $emailtype;
   $datamailobject->notification_infoid = $notification->id;
   $datamailobject->plugintype = $notification->plugintype;
   $datamailobject->pluginname = $notification->pluginname;
   $datamailobject->moduleid = 1;
   $datamailobject->body = $notification->body;
   $datamailobject->subject = $notification->subject;
   $datamailobject->arabic_body = $notification->arabic_body;
   $datamailobject->arabic_subject = $notification->arabic_subject;
   $datamailobject->sendsms =1;
   $datamailobject->trainingprogramid = $trainingprograminstance->id;
   $datamailobject->teammemberid = 0;
   $datamailobject->userlang = $touser->lang;
   if($datamailobject->userlang == 'ar'){            
       $datamailobject->program_name = $trainingprograminstance->program_arabicname;
       $datamailobject->program_userfullname = $trainingprograminstance->program_userarabicfullname;
   }
 else{       
   $datamailobject->program_name = $trainingprograminstance->program_name;
    $datamailobject->program_userfullname = $trainingprograminstance->program_userfullname;

 }
 $this->log_email_notification($touser, $fromuser, $datamailobject);


}  



    public function send_bulkenrol_program_notification($trainingprograminstance, $touser, $fromuser, $emailtype, $notification, $waitinglistid = 0)
    {
        global $PAGE,$DB;
        $datamailobject = new \stdClass();

        $datamailobject->emailtype = $emailtype;
        $datamailobject->notification_infoid = $notification->id;
        $datamailobject->plugintype = $notification->plugintype;
        $datamailobject->pluginname = $notification->pluginname;
        $datamailobject->moduleid = 1;
        $datamailobject->body = $notification->body;
        $datamailobject->subject = $notification->subject;
        $datamailobject->arabic_body = $notification->arabic_body;
        $datamailobject->arabic_subject = $notification->arabic_subject;
        $datamailobject->sendsms = 1;
        $datamailobject->trainingprogramid = $trainingprograminstance->id;
        $datamailobject->teammemberid = 0;
        $datamailobject->userlang = $touser->lang;  
        $datamailobject->org_off = $trainingprograminstance->org_off; 
        $datamailobject->program_name = $trainingprograminstance->program_name;
        $datamailobject->trainee_details = $trainingprograminstance->trainee_details;

        $datamailobject->offering_startdate = $trainingprograminstance->offering_startdate;
        $datamailobject->offering_starttime = $trainingprograminstance->offering_starttime;
        $datamailobject->offering_enddate = $trainingprograminstance->offering_enddate;
        $datamailobject->offering_endtime = $trainingprograminstance->offering_endtime;        
        $this->log_email_notification($touser, $fromuser, $datamailobject);

 

    }






    public function send_trainingprogram_unenroll_notification($trainingprograminstance, $touser, $fromuser, $emailtype, $notification, $waitinglistid = 0)
    {
        global $PAGE;
        $datamailobject = new \stdClass();

        $datamailobject->emailtype = $emailtype;
        $datamailobject->notification_infoid = $notification->id;
        $datamailobject->plugintype = $notification->plugintype;
        $datamailobject->pluginname = $notification->pluginname;
        $datamailobject->moduleid = 1;
        $datamailobject->body = $notification->body;
        $datamailobject->subject = $notification->subject;
        $datamailobject->arabic_body = $notification->arabic_body;
        $datamailobject->arabic_subject = $notification->arabic_subject;
        $datamailobject->sendsms =1;
        $datamailobject->trainingprogramid = $trainingprograminstance->id;
        $datamailobject->teammemberid = 0;
        $datamailobject->userlang=$touser->lang;
        if($datamailobject->userlang=='ar'){            
            $datamailobject->program_name = $trainingprograminstance->program_arabicname;
            $datamailobject->program_userfullname = $trainingprograminstance->program_userarabicfullname;
        }
      else{       
        $datamailobject->program_name = $trainingprograminstance->program_name;
         $datamailobject->program_userfullname = $trainingprograminstance->program_userfullname;

      }
      $datamailobject->program_date = $trainingprograminstance->program_date;
      $datamailobject->program_time = $trainingprograminstance->program_time;

        $this->log_email_notification($touser, $fromuser, $datamailobject);
    }
    
    public function send_trainingprogram_completion_notification($trainingprograminstance, $touser, $fromuser, $emailtype, $notification, $waitinglistid = 0)
    {
        global $PAGE;
        $datamailobject = new \stdClass();

        $datamailobject->emailtype = $emailtype;
        $datamailobject->notification_infoid = $notification->id;
        $datamailobject->plugintype = $notification->plugintype;
        $datamailobject->pluginname = $notification->pluginname;
        $datamailobject->moduleid = 1;     
        $datamailobject->body = $notification->body;
        $datamailobject->subject = $notification->subject;
        $datamailobject->arabic_body = $notification->arabic_body;
        $datamailobject->arabic_subject = $notification->arabic_subject;
        $datamailobject->trainingprogramid = $trainingprograminstance->id;
        $datamailobject->sendsms =1;
        $datamailobject->teammemberid = 0;
        $datamailobject->userlang=$touser->lang;
        if($datamailobject->userlang=='ar'){            
            $datamailobject->program_name = $trainingprograminstance->program_arabicname;
            $datamailobject->program_userfullname = $trainingprograminstance->program_arabicuserfullname;
        }
      else{       
        $datamailobject->program_name = $trainingprograminstance->program_name;
        $datamailobject->program_userfullname = $trainingprograminstance->program_userfullname;

      }


       // $this->log_email_notification($touser, $fromuser, $datamailobject);
    }
    public function send_trainingprogram_before_7_days_notification($trainingprograminstance, $touser, $fromuser, $emailtype, $notification, $waitinglistid = 0)
    {

        global $PAGE,$DB;
        $datamailobject = new \stdClass();

        $datamailobject->emailtype = $emailtype;
        $datamailobject->notification_infoid = $notification->id;
        $datamailobject->plugintype = $notification->plugintype;
        $datamailobject->pluginname = $notification->pluginname;
        $datamailobject->moduleid = 1;
        
        $datamailobject->trainingprogram_related_module_name = $trainingprograminstance->trainingprogram_related_module_name;
        $datamailobject->trainingprogram_userfullname = $trainingprograminstance->trainingprogram_related_user_name;
        $datamailobject->trainingprogram_related_module_date = $trainingprograminstance->trainingprogram_related_module_date;
        $datamailobject->trainingprogram_related_module_time = $trainingprograminstance->trainingprogram_related_module_time;
        $datamailobject->body = $notification->body;
        $datamailobject->subject = $notification->subject;
        $datamailobject->arabic_body = $notification->arabic_body;
        $datamailobject->arabic_subject = $notification->arabic_subject;
        $datamailobject->moduleid = 1;
        $datamailobject->touserid = $touser->id;
        $datamailobject->fromuserid = $fromuser->id;
        $datamailobject->sendsms =1;
        $datamailobject->teammemberid = 0;
        $datamailobject->userlang=$touser->lang; 
        $this->log_email_notification($touser, $fromuser, $datamailobject);
     
    }
   
    public function send_trainingprogram_before_48_hours_notification($trainingprograminstance, $touser, $fromuser, $emailtype, $notification, $waitinglistid = 0)
    {
        global $PAGE;


        $datamailobject = new \stdClass();

        $datamailobject->emailtype = $emailtype;
        $datamailobject->notification_infoid = $notification->id;
        $datamailobject->plugintype = $notification->plugintype;
        $datamailobject->pluginname = $notification->pluginname;
        $datamailobject->moduleid = 1;
        $datamailobject->trainingprogram_related_module_name = $trainingprograminstance->trainingprogram_related_module_name;
        $datamailobject->trainingprogram_userfullname =  $trainingprograminstance->trainingprogram_related_user_name;
        $datamailobject->trainingprogram_related_module_date = $trainingprograminstance->trainingprogram_related_module_date;
        $datamailobject->trainingprogram_related_module_time = $trainingprograminstance->trainingprogram_related_module_time;
        $datamailobject->body = $notification->body;
        $datamailobject->subject = $notification->subject;
        $datamailobject->arabic_body = $notification->arabic_body;
        $datamailobject->arabic_subject = $notification->arabic_subject;
        $datamailobject->moduleid = 1;
        $datamailobject->touserid = $touser->id;
        $datamailobject->fromuserid = $fromuser->id;
        $datamailobject->sendsms =1;
        $datamailobject->teammemberid = 0;
        $datamailobject->userlang=$touser->lang; 

        $this->log_email_notification($touser, $fromuser, $datamailobject);
     
    }
    public function send_trainingprogram_before_24_hours_notification($trainingprograminstance, $touser, $fromuser, $emailtype, $notification, $waitinglistid = 0)
    {
        global $PAGE;


        $datamailobject = new \stdClass();

        $datamailobject->emailtype = $emailtype;
        $datamailobject->notification_infoid = $notification->id;
        $datamailobject->plugintype = $notification->plugintype;
        $datamailobject->pluginname = $notification->pluginname;
        $datamailobject->moduleid = 1;
        $datamailobject->trainingprogram_related_module_name = $trainingprograminstance->trainingprogram_related_module_name;
        $datamailobject->trainingprogram_userfullname = $trainingprograminstance->trainingprogram_related_user_name;
        $datamailobject->trainingprogram_related_module_date = $trainingprograminstance->trainingprogram_related_module_date;
        $datamailobject->trainingprogram_related_module_time = $trainingprograminstance->trainingprogram_related_module_time;
        $datamailobject->body = $notification->body;
        $datamailobject->subject = $notification->subject;
        $datamailobject->arabic_body = $notification->arabic_body;
        $datamailobject->arabic_subject = $notification->arabic_subject;
        $datamailobject->moduleid = 1;
        $datamailobject->touserid = $touser->id;
        $datamailobject->fromuserid = $fromuser->id;
        $datamailobject->sendsms =1;
        $datamailobject->teammemberid = 0;
        $datamailobject->userlang=$touser->lang; 

        $this->log_email_notification($touser, $fromuser, $datamailobject);
     
    }
    public function send_trainingprogram_before_30_minutes_notification($trainingprograminstance, $touser, $fromuser, $emailtype, $notification, $waitinglistid = 0)
    {
        global $PAGE;


        $datamailobject = new \stdClass();

        $datamailobject->emailtype = $emailtype;
        $datamailobject->notification_infoid = $notification->id;
        $datamailobject->plugintype = $notification->plugintype;
        $datamailobject->pluginname = $notification->pluginname;
        $datamailobject->moduleid = 1;
        $datamailobject->trainingprogram_related_module_name = $trainingprograminstance->trainingprogram_related_module_name;
        $datamailobject->trainingprogram_userfullname = $trainingprograminstance->trainingprogram_related_user_name;
        $datamailobject->body = $notification->body;
        $datamailobject->subject = $notification->subject;
        $datamailobject->arabic_body = $notification->arabic_body;
        $datamailobject->arabic_subject = $notification->arabic_subject;
        $datamailobject->moduleid = 1;
        $datamailobject->touserid = $touser->id;
        $datamailobject->fromuserid = $fromuser->id;
        $datamailobject->teammemberid = 0;
        $datamailobject->userlang=$touser->lang; 

        $this->log_email_notification($touser, $fromuser, $datamailobject);
     
    }
    public function send_trainingprogram_after_session_notification($trainingprograminstance, $touser, $fromuser, $emailtype, $notification, $waitinglistid = 0)
    {
        global $PAGE;


        $datamailobject = new \stdClass();

        $datamailobject->emailtype = $emailtype;
        $datamailobject->notification_infoid = $notification->id;
        $datamailobject->plugintype = $notification->plugintype;
        $datamailobject->pluginname = $notification->pluginname;
        $datamailobject->moduleid = 1;
        $datamailobject->trainingprogram_related_module_name = $trainingprograminstance->trainingprogram_related_module_name;
        $datamailobject->trainingprogram_userfullname = $trainingprograminstance->trainingprogram_related_user_name;
        $datamailobject->body = $notification->body;
        $datamailobject->subject = $notification->subject;
        $datamailobject->arabic_body = $notification->arabic_body;
        $datamailobject->arabic_subject = $notification->arabic_subject;
        $datamailobject->moduleid = 1;
        $datamailobject->touserid = $touser->id;
        $datamailobject->sendsms =1;
        $datamailobject->fromuserid = $fromuser->id;
        $datamailobject->teammemberid = 0;
        $datamailobject->userlang=$touser->lang; 

        $this->log_email_notification($touser, $fromuser, $datamailobject);
    }
    public function send_trainingprogram_send_conclusion_notification($trainingprograminstance, $touser, $fromuser, $emailtype, $notification, $waitinglistid = 0)
    {
        global $PAGE;


        $datamailobject = new \stdClass();

        $datamailobject->emailtype = $emailtype;
        $datamailobject->notification_infoid = $notification->id;
        $datamailobject->plugintype = $notification->plugintype;
        $datamailobject->pluginname = $notification->pluginname;
        $datamailobject->moduleid = 1;
        $datamailobject->trainingprogram_related_module_name = $trainingprograminstance->trainingprogram_related_module_name;
        $datamailobject->trainingprogram_userfullname = $trainingprograminstance->trainingprogram_related_user_name;
        $datamailobject->body = $notification->body;
        $datamailobject->subject = $notification->subject;
        $datamailobject->arabic_body = $notification->arabic_body;
        $datamailobject->arabic_subject = $notification->arabic_subject;
        $datamailobject->moduleid = 1;
        $datamailobject->touserid = $touser->id;
        $datamailobject->fromuserid = $fromuser->id;
        $datamailobject->sendsms =1;
        $datamailobject->teammemberid = 0;
        $datamailobject->userlang=$touser->lang; 

        $this->log_email_notification($touser, $fromuser, $datamailobject);
    }
    public function send_trainingprogram_certificate_assignment_notification($trainingprograminstance, $touser, $fromuser, $emailtype, $notification, $waitinglistid = 0)
    {
        global $PAGE;
        $datamailobject = new \stdClass();

        $datamailobject->emailtype = $emailtype;
        $datamailobject->notification_infoid = $notification->id;
        $datamailobject->plugintype = $notification->plugintype;
        $datamailobject->pluginname = $notification->pluginname;
        $datamailobject->moduleid = 1;
        $datamailobject->program_name = $trainingprograminstance->program_name;
        $datamailobject->program_userfullname = $touser->firstname;
        $datamailobject->program_certificatelink = $trainingprograminstance->program_certificatelink;
        $datamailobject->body = $notification->body;
        $datamailobject->subject = $notification->subject;
        $datamailobject->arabic_body = $notification->arabic_body;
        $datamailobject->arabic_subject = $notification->arabic_subject;
        $datamailobject->trainingprogramid = $trainingprograminstance->id;
        $datamailobject->teammemberid = 0;
        $datamailobject->userlang=$touser->lang;
        if($datamailobject->userlang=='ar'){            
            $datamailobject->program_name = $trainingprograminstance->program_arabicname;
            $datamailobject->program_userfullname = $trainingprograminstance->program_arabicuserfullname;
        }
      else{       
        $datamailobject->program_name = $trainingprograminstance->program_name;
        $datamailobject->program_userfullname = $trainingprograminstance->program_userfullname;

      }
        $this->log_email_notification($touser, $fromuser, $datamailobject);
    }
    public function send_trainingprogram_enrolled_inactive_accounts_notification($trainingprograminstance, $touser, $fromuser, $emailtype, $notification, $waitinglistid = 0)
    {
        global $PAGE;
        $datamailobject = new \stdClass();

        $datamailobject->emailtype = $emailtype;
        $datamailobject->notification_infoid = $notification->id;
        $datamailobject->plugintype = $notification->plugintype;
        $datamailobject->pluginname = $notification->pluginname;
        $datamailobject->moduleid = 1;
        $datamailobject->program_name = $trainingprograminstance->program_name;
        $datamailobject->program_userfullname = $trainingprograminstance->program_userfullname;
        $datamailobject->program_link = $trainingprograminstance->program_link;
        $datamailobject->body = $notification->body;
        $datamailobject->subject = $notification->subject;
        $datamailobject->arabic_body = $notification->arabic_body;
        $datamailobject->arabic_subject = $notification->arabic_subject;
        $datamailobject->trainingprogramid = $trainingprograminstance->id;
        $datamailobject->teammemberid = 0;
        $datamailobject->userlang=$touser->lang;
        $this->log_email_notification($touser, $fromuser, $datamailobject);
    }
    public function send_trainingprogram_pre_assessment_opened_notification($trainingprograminstance, $touser, $fromuser, $emailtype, $notification, $waitinglistid = 0)
    {
        global $PAGE;
        $datamailobject = new \stdClass();

        $datamailobject->emailtype = $emailtype;
        $datamailobject->notification_infoid = $notification->id;
        $datamailobject->plugintype = $notification->plugintype;
        $datamailobject->pluginname = $notification->pluginname;
        $datamailobject->moduleid = 1;
        $datamailobject->program_name = $trainingprograminstance->program_name;
        $datamailobject->program_userfullname = $trainingprograminstance->program_userfullname;
        $datamailobject->assessment_start_date = $trainingprograminstance->assessment_start_date;
        $datamailobject->assessment_end_date = $trainingprograminstance->assessment_end_date;
        $datamailobject->assessment_link = $trainingprograminstance->assessment_link;
        $datamailobject->body = $notification->body;
        $datamailobject->subject = $notification->subject;
        $datamailobject->arabic_body = $notification->arabic_body;
        $datamailobject->arabic_subject = $notification->arabic_subject;
        $datamailobject->trainingprogramid = $trainingprograminstance->id;
        $datamailobject->teammemberid = 0;
        $datamailobject->userlang=$touser->lang;
        $this->log_email_notification($touser, $fromuser, $datamailobject);
    }
    public function send_trainingprogram_post_assessment_opened_notification($trainingprograminstance, $touser, $fromuser, $emailtype, $notification, $waitinglistid = 0)
    {
        global $PAGE;
        $datamailobject = new \stdClass();

        $datamailobject->emailtype = $emailtype;
        $datamailobject->notification_infoid = $notification->id;
        $datamailobject->plugintype = $notification->plugintype;
        $datamailobject->pluginname = $notification->pluginname;
        $datamailobject->moduleid = 1;
        $datamailobject->program_name = $trainingprograminstance->program_name;
        $datamailobject->program_userfullname = $trainingprograminstance->program_userfullname;
        $datamailobject->assessment_start_date = $trainingprograminstance->assessment_start_date;
        $datamailobject->assessment_end_date = $trainingprograminstance->assessment_end_date;
        $datamailobject->assessment_link = $trainingprograminstance->assessment_link;
        $datamailobject->body = $notification->body;
        $datamailobject->subject = $notification->subject;
        $datamailobject->arabic_body = $notification->arabic_body;
        $datamailobject->arabic_subject = $notification->arabic_subject;
        $datamailobject->trainingprogramid = $trainingprograminstance->id;
        $datamailobject->teammemberid = 0;
        $datamailobject->userlang=$touser->lang;
        $this->log_email_notification($touser, $fromuser, $datamailobject);
    }
    public function send_trainingprogram_post_assessment_closed_notification($trainingprograminstance, $touser, $fromuser, $emailtype, $notification, $waitinglistid = 0)
    {
        global $PAGE;
        $datamailobject = new \stdClass();

        $datamailobject->emailtype = $emailtype;
        $datamailobject->notification_infoid = $notification->id;
        $datamailobject->plugintype = $notification->plugintype;
        $datamailobject->pluginname = $notification->pluginname;
        $datamailobject->moduleid = 1;
        $datamailobject->program_name = $trainingprograminstance->program_name;
        $datamailobject->program_userfullname = $trainingprograminstance->program_userfullname;
        $datamailobject->assessment_start_date = $trainingprograminstance->assessment_start_date;
        $datamailobject->assessment_end_date = $trainingprograminstance->assessment_end_date;
        $datamailobject->assessment_link = $trainingprograminstance->assessment_link;
        $datamailobject->body = $notification->body;
        $datamailobject->subject = $notification->subject;
        $datamailobject->arabic_body = $notification->arabic_body;
        $datamailobject->arabic_subject = $notification->arabic_subject;
        $datamailobject->trainingprogramid = $trainingprograminstance->id;
        $datamailobject->teammemberid = 0;
        $datamailobject->userlang=$touser->lang;
        $this->log_email_notification($touser, $fromuser, $datamailobject);
    }
    public function send_trainingprogram_pre_assessment_closed_notification($trainingprograminstance, $touser, $fromuser, $emailtype, $notification, $waitinglistid = 0)
    {
        global $PAGE;
        $datamailobject = new \stdClass();

        $datamailobject->emailtype = $emailtype;
        $datamailobject->notification_infoid = $notification->id;
        $datamailobject->plugintype = $notification->plugintype;
        $datamailobject->pluginname = $notification->pluginname;
        $datamailobject->moduleid = 1;
        $datamailobject->program_name = $trainingprograminstance->program_name;
        $datamailobject->program_userfullname = $trainingprograminstance->program_userfullname;
        $datamailobject->assessment_start_date = $trainingprograminstance->assessment_start_date;
        $datamailobject->assessment_end_date = $trainingprograminstance->assessment_end_date;
        $datamailobject->assessment_link = $trainingprograminstance->assessment_link;
        $datamailobject->body = $notification->body;
        $datamailobject->subject = $notification->subject;
        $datamailobject->arabic_body = $notification->arabic_body;
        $datamailobject->arabic_subject = $notification->arabic_subject;
        $datamailobject->trainingprogramid = $trainingprograminstance->id;
        $datamailobject->teammemberid = 0;
        $datamailobject->userlang=$touser->lang;
        $this->log_email_notification($touser, $fromuser, $datamailobject);
    }
    public function send_trainingprogram_assignment_deadline_4_hours_notification($trainingprograminstance, $touser, $fromuser, $emailtype, $notification, $waitinglistid = 0)
    {
        global $PAGE;
        $datamailobject = new \stdClass();

        $datamailobject->emailtype = $emailtype;
        $datamailobject->notification_infoid = $notification->id;
        $datamailobject->plugintype = $notification->plugintype;
        $datamailobject->pluginname = $notification->pluginname;
        $datamailobject->moduleid = 1;
        $datamailobject->program_name = $trainingprograminstance->program_name;
        $datamailobject->program_userfullname = $trainingprograminstance->program_userfullname;
        $datamailobject->body = $notification->body;
        $datamailobject->subject = $notification->subject;
        $datamailobject->arabic_body = $notification->arabic_body;
        $datamailobject->arabic_subject = $notification->arabic_subject;
        $datamailobject->trainingprogramid = $trainingprograminstance->id;
        $datamailobject->teammemberid = 0;
        $datamailobject->userlang=$touser->lang;
        $this->log_email_notification($touser, $fromuser, $datamailobject);
    }
    public function send_trainingprogram_assignment_deadline_24_hours_notification($trainingprograminstance, $touser, $fromuser, $emailtype, $notification, $waitinglistid = 0)
    {
        global $PAGE;
        $datamailobject = new \stdClass();

        $datamailobject->emailtype = $emailtype;
        $datamailobject->notification_infoid = $notification->id;
        $datamailobject->plugintype = $notification->plugintype;
        $datamailobject->pluginname = $notification->pluginname;
        $datamailobject->moduleid = 1;
        $datamailobject->program_name = $trainingprograminstance->program_name;
        $datamailobject->program_userfullname = $trainingprograminstance->program_userfullname;
        $datamailobject->body = $notification->body;
        $datamailobject->subject = $notification->subject;
        $datamailobject->arabic_body = $notification->arabic_body;
        $datamailobject->arabic_subject = $notification->arabic_subject;
        $datamailobject->trainingprogramid = $trainingprograminstance->id;
        $datamailobject->teammemberid = 0;
        $datamailobject->userlang=$touser->lang;
        $this->log_email_notification($touser, $fromuser, $datamailobject);
    }
    public function trainingprogram_before_7_days_notification($lastruntime,$nextruntime,$timenow)
    {
        global $DB, $USER;
        $data = new \stdClass();
        $from = null;
        // $from = date('Y-m-d');
        // $from = date('2022-05-11');
        $fromdate = ($from !== null) ? $from : date('Y-m-d');
       $sqltrainingprograms = "SELECT  pe.userid,pe.roleid,ltp.name,ltp.namearabic,DATE(from_unixtime(tpo.startdate)) AS startdate,tpo.time as ttime  FROM {tp_offerings} AS tpo
        INNER JOIN {local_trainingprogram} AS ltp ON ltp.id = tpo.trainingid
        INNER JOIN {program_enrollments} as pe on pe.offeringid = tpo.id 
        WHERE DATE(from_unixtime(tpo.startdate-60*60*24*7)) = '$fromdate'
                AND ((tpo.startdate+tpo.time)-60*60*24*7) >= $lastruntime
                AND ((tpo.startdate+tpo.time)-60*60*24*7) <= $timenow"; 
        $trainingprogramslist = $DB->get_records_sql($sqltrainingprograms);
        if (!empty($trainingprogramslist)) {
            foreach ($trainingprogramslist as $key => $trainingprogram) {
                $hours = floor(($trainingprogram->ttime%86400)/3600);
                $minutes = floor(($trainingprogram->ttime%3600)/60);
                $sql = "SELECT lnt.* FROM {local_notification_type} AS lnt
                WHERE shortname IN ('trainingprogram_before_7_days') ";
                $notificationtype = $DB->get_record_sql($sql);
               
                $notificationtype->trainingprogram_related_module_date = date('d-M-Y', strtotime($trainingprogram->startdate));
                $notificationtype->trainingprogram_related_module_time = $hours.":".$minutes;
                $trainee=$DB->get_records('role',array('id'=>$trainingprogram->roleid,'shortname'=>'trainee'));                     
                foreach($trainee as  $trainees){
                    if($trainees){
                        $user = $DB->get_record('user', array('id' => $trainingprogram->userid, 'deleted' => 0));
                        if ($user->lang == 'ar'){
                            $notificationtype->trainingprogram_related_module_name = $trainingprogram->namearabic;
                           

                        } else {
                            $notificationtype->trainingprogram_related_module_name = $trainingprogram->name;
                           

                        }

                        $localuserrecord = $DB->get_record('local_users',['userid'=> $user->id]);
                        $notificationtype->trainingprogram_related_user_name  = ($localuserrecord)? (($user->lang == 'ar') ? $localuserrecord->firstnamearabic.' '.$localuserrecord->middlenamearabic.' '.$localuserrecord->thirdnamearabic.' '.$localuserrecord->lastnamearabic  : $localuserrecord->firstname.' '.$localuserrecord->middlenameen.' '.$localuserrecord->thirdnameen.' '.$localuserrecord->lastname) :fullname($DB->get_record('user',array('id'=>$user->id)));  
                        $this->trainingprogram_notification('trainingprogram_before_7_days', $touser = $user, $fromuser = get_admin(), $notificationtype, $waitinglistid = 0);                
                    }               
               }               
            }
        }
    }
    public function send_trainingprogram_before_10_days_notification($trainingprograminstance, $touser, $fromuser, $emailtype, $notification, $waitinglistid = 0)
    {

        global $PAGE,$DB;
        $datamailobject = new \stdClass();

        $datamailobject->emailtype = $emailtype;
        $datamailobject->notification_infoid = $notification->id;
        $datamailobject->plugintype = $notification->plugintype;
        $datamailobject->pluginname = $notification->pluginname;
        $datamailobject->moduleid = 1;
        
        $datamailobject->program_name = $trainingprograminstance->program_name;
        $datamailobject->program_userfullname = $trainingprograminstance->program_userfullname;
        $datamailobject->program_date = $trainingprograminstance->program_date;
        $datamailobject->program_time = $trainingprograminstance->program_time;
        $datamailobject->body = $notification->body;
        $datamailobject->subject = $notification->subject;
        $datamailobject->arabic_body = $notification->arabic_body;
        $datamailobject->arabic_subject = $notification->arabic_subject;
        $datamailobject->moduleid = 1;
        $datamailobject->touserid = $touser->id;
        $datamailobject->fromuserid = $fromuser->id;
        $datamailobject->sendsms =1;
        $datamailobject->teammemberid = 0;
        $datamailobject->userlang=$touser->lang;       
        $this->log_email_notification($touser, $fromuser, $datamailobject);
    
    }



    public function trainingprogram_before_48_hours_notification($lastruntime,$nextruntime,$timenow)
    {
        global $DB, $USER;
        $data = new \stdClass();
        $from = null;
        // $from = date('Y-m-d');
        // $from = date('2022-05-16');
        $fromdate = ($from !== null) ? $from : date('Y-m-d');
        $sqltrainingprograms = "SELECT  pe.userid,pe.roleid,ltp.name, ltp.namearabic,DATE(from_unixtime(tpo.startdate)) AS startdate,tpo.time as ttime  FROM {tp_offerings} AS tpo
        INNER JOIN {local_trainingprogram} AS ltp ON ltp.id = tpo.trainingid
        INNER JOIN {program_enrollments} AS pe ON pe.offeringid = tpo.id 
        WHERE  DATE(from_unixtime(tpo.startdate-60*60*48)) = '$fromdate'
                AND ((tpo.startdate+tpo.time)-60*60*48) >= $lastruntime
                AND ((tpo.startdate+tpo.time)-60*60*48) <= $timenow";  
        // echo $sqltrainingprograms;exit;     
        $trainingprogramslist = $DB->get_records_sql($sqltrainingprograms);  
        if (!empty($trainingprogramslist)) {
            foreach ($trainingprogramslist as $key => $trainingprogram) {
                $hours = floor(($trainingprogram->ttime%86400)/3600);
                $minutes = floor(($trainingprogram->ttime%3600)/60);
                $sql = "SELECT lnt.* FROM {local_notification_type} AS lnt
                WHERE shortname IN ('trainingprogram_before_48_hours') ";
                $notificationtype = $DB->get_record_sql($sql);                
                $notificationtype->trainingprogram_related_module_date = date('d-M-Y', strtotime($trainingprogram->startdate));
                $notificationtype->trainingprogram_related_module_time = $hours.":".$minutes;
                $trainee=$DB->get_records('role',array('id'=>$trainingprogram->roleid,'shortname'=>'trainee'));             
                foreach($trainee as  $trainees){
                    if($trainees){
                        $user = $DB->get_record('user', array('id' => $trainingprogram->userid, 'deleted' => 0));  
                        if ($user->lang == 'ar'){
                            $notificationtype->trainingprogram_related_module_name = $trainingprogram->namearabic;
                            

                        } else {
                            $notificationtype->trainingprogram_related_module_name = $trainingprogram->name;
                            

                        }

                        $localuserrecord = $DB->get_record('local_users',['userid'=> $user->id]);
                        $notificationtype->trainingprogram_related_user_name  = ($localuserrecord)? (($user->lang == 'ar') ? $localuserrecord->firstnamearabic.' '.$localuserrecord->middlenamearabic.' '.$localuserrecord->thirdnamearabic.' '.$localuserrecord->lastnamearabic  : $localuserrecord->firstname.' '.$localuserrecord->middlenameen.' '.$localuserrecord->thirdnameen.' '.$localuserrecord->lastname) :fullname($DB->get_record('user',array('id'=>$user->id)));            
                        $this->trainingprogram_notification('trainingprogram_before_48_hours', $touser = $user, $fromuser = get_admin(), $notificationtype, $waitinglistid = 0);
                    }
                }         
            }
        }
    }
  
    public function trainingprogram_before_24_hours_notification($lastruntime,$nextruntime,$timenow)
    {
        global $DB, $USER;
        $data = new \stdClass();
        $from = null;
        // $from = date('Y-m-d');
        //$from = date('2022-08-04');
        $fromdate = ($from !== null) ? $from : date('Y-m-d');
        $sqltrainingprograms = "SELECT  pe.userid,pe.roleid,ltp.name, ltp.namearabic,DATE(from_unixtime(tpo.startdate)) AS startdate,tpo.time as ttime FROM {tp_offerings} AS tpo
        INNER JOIN {local_trainingprogram} AS ltp ON ltp.id = tpo.trainingid
        INNER JOIN {program_enrollments} AS pe ON pe.offeringid = tpo.id 
        WHERE  DATE(from_unixtime(tpo.startdate-60*60*24)) = '$fromdate'
                AND ((tpo.startdate+tpo.time)-60*60*24) >= $lastruntime
                AND ((tpo.startdate+tpo.time)-60*60*24) <= $timenow";     
        $trainingprogramslist = $DB->get_records_sql($sqltrainingprograms);
        if (!empty($trainingprogramslist)) {
            foreach ($trainingprogramslist as $key => $trainingprogram) {
                $hours = floor(($trainingprogram->ttime%86400)/3600);
                $minutes = floor(($trainingprogram->ttime%3600)/60);
                $sql = "SELECT lnt.* FROM {local_notification_type} AS lnt
                        WHERE shortname IN ('trainingprogram_before_24_hours') ";
                $notificationtype = $DB->get_record_sql($sql);
               
                $notificationtype->trainingprogram_related_module_date = date('d-M-Y', strtotime($trainingprogram->startdate));
                $notificationtype->trainingprogram_related_module_time = $hours.":".$minutes;
                $trainee=$DB->get_records('role',array('id'=>$trainingprogram->roleid,'shortname'=>'trainee'));             
                foreach($trainee as  $trainees){
                    if($trainees){
                        $user = $DB->get_record('user', array('id' => $trainingprogram->userid, 'deleted' => 0));
                        if ($user->lang == 'ar'){
                            $notificationtype->trainingprogram_related_module_name = $trainingprogram->namearabic;
                     
                        } else {
                            $notificationtype->trainingprogram_related_module_name = $trainingprogram->name;
                    
                        } 
                        $localuserrecord = $DB->get_record('local_users',['userid'=> $user->id]);
                        $notificationtype->trainingprogram_related_user_name  = ($localuserrecord)? (($user->lang == 'ar') ? $localuserrecord->firstnamearabic.' '.$localuserrecord->middlenamearabic.' '.$localuserrecord->thirdnamearabic.' '.$localuserrecord->lastnamearabic  : $localuserrecord->firstname.' '.$localuserrecord->middlenameen.' '.$localuserrecord->thirdnameen.' '.$localuserrecord->lastname) :fullname($DB->get_record('user',array('id'=>$user->id)));  
                        $this->trainingprogram_notification('trainingprogram_before_24_hours', $touser = $user, $fromuser = get_admin(), $notificationtype, $waitinglistid = 0);
                    }
                }
            }
        }
    }
    public function trainingprogram_before_30_minutes_notification($lastruntime,$nextruntime,$timenow)
    {
        global $DB, $USER;
        $data = new \stdClass();
        $from = null;
       $fromdate =($from !== null) ? $from : date('Y-m-d');
       $roleid = $DB->get_field('role','id', array('shortname' => 'trainee'));
        $sqltrainingprograms = "SELECT  pe.userid,ltp.name, ltp.namearabic,date(from_unixtime(tpo.startdate+tpo.time)) AS startdate,tpo.time 
                                FROM {tp_offerings} as tpo
                                INNER JOIN {local_trainingprogram} AS ltp ON ltp.id = tpo.trainingid
                                INNER JOIN {program_enrollments} AS pe ON pe.offeringid = tpo.id 
                                WHERE roleid= $roleid 
                                      AND date(from_unixtime((tpo.startdate+tpo.time)-(60*30))) = '$fromdate'
                                      AND ((tpo.startdate+tpo.time)-(60*30)) >= $lastruntime
                                      AND ((tpo.startdate+tpo.time)-(60*30)) <= $timenow";
         $trainingprogramslist = $DB->get_records_sql($sqltrainingprograms);
        if (!empty($trainingprogramslist)) {
            foreach ($trainingprogramslist as $key => $trainingprogram) {
                $sql = "SELECT lnt.* 
                        FROM {local_notification_type} AS lnt
                        WHERE shortname IN ('trainingprogram_before_30_minutes') ";
                $notificationtype = $DB->get_record_sql($sql);
            
                $user = $DB->get_record('user', array('id' => $trainingprogram->userid, 'deleted' => 0));
                if ($user->lang == 'ar'){
                    $notificationtype->trainingprogram_related_module_name = $trainingprogram->namearabic;
                    

                } else {
                    $notificationtype->trainingprogram_related_module_name = $trainingprogram->name;
                    

                }  

                $localuserrecord = $DB->get_record('local_users',['userid'=> $user->id]);
                $notificationtype->trainingprogram_related_user_name  = ($localuserrecord)? (($user->lang == 'ar') ? $localuserrecord->firstnamearabic.' '.$localuserrecord->middlenamearabic.' '.$localuserrecord->thirdnamearabic.' '.$localuserrecord->lastnamearabic  : $localuserrecord->firstname.' '.$localuserrecord->middlenameen.' '.$localuserrecord->thirdnameen.' '.$localuserrecord->lastname) :fullname($DB->get_record('user',array('id'=>$user->id))); 
                $this->trainingprogram_notification('trainingprogram_before_30_minutes', $touser = $user, $fromuser = get_admin(), $notificationtype, $waitinglistid = 0);
            }
        }
    }
    public function trainingprogram_after_session_notification($lastruntime,$nextruntime,$timenow)
    {
        global $DB, $USER;
        $from=null;
        $data = new \stdClass();
        $fromdate = ($from !== null) ? $from : date('Y-m-d');
        $roleid = $DB->get_field('role','id', array('shortname' => 'trainee'));
        $sqltrainingprograms = "SELECT  pe.userid,ltp.name, ltp.namearabic,date(from_unixtime(tpo.startdate)) AS startdate,tpo.time 
                                FROM {tp_offerings} as tpo
                                INNER JOIN {local_trainingprogram} AS ltp ON ltp.id = tpo.trainingid
                                INNER JOIN {program_enrollments} AS pe ON pe.offeringid = tpo.id 
                                INNER JOIN {program_completions} AS pc ON pc.programid = ltp.id
                                WHERE roleid= $roleid 
                                      AND date(from_unixtime(tpo.enddate)) = '$fromdate' 
                                      AND (tpo.enddate+tpo.time+tpo.duration) >= $lastruntime 
                                      AND (tpo.enddate+tpo.time+tpo.duration) <= $timenow";
        $trainingprogramslist = $DB->get_records_sql($sqltrainingprograms);
        if (!empty($trainingprogramslist)) {
            foreach ($trainingprogramslist as $key => $trainingprogram) {
                $sql = "SELECT lnt.* 
                        FROM {local_notification_type} AS lnt
                        WHERE shortname IN ('trainingprogram_after_session') ";
                $notificationtype = $DB->get_record_sql($sql);
               
                $user = $DB->get_record('user', array('id' => $trainingprogram->userid, 'deleted' => 0));
                if ($user->lang == 'ar'){
                    $notificationtype->trainingprogram_related_module_name = $trainingprogram->namearabic;
                   
                } else {
                    $notificationtype->trainingprogram_related_module_name = $trainingprogram->name;
                   

                } 

                $localuserrecord = $DB->get_record('local_users',['userid'=> $user->id]);
                $notificationtype->trainingprogram_related_user_name  = ($localuserrecord)? (($user->lang == 'ar') ? $localuserrecord->firstnamearabic.' '.$localuserrecord->middlenamearabic.' '.$localuserrecord->thirdnamearabic.' '.$localuserrecord->lastnamearabic  : $localuserrecord->firstname.' '.$localuserrecord->middlenameen.' '.$localuserrecord->thirdnameen.' '.$localuserrecord->lastname) :fullname($DB->get_record('user',array('id'=>$user->id))); 
                $this->trainingprogram_notification('trainingprogram_after_session', $touser = $user, $fromuser = get_admin(), $notificationtype, $waitinglistid = 0);
            }
        }
    }
    public function trainingprogram_send_conclusion_notification($lastruntime,$nextruntime,$timenow)
    {
        global $DB, $USER;
        $data = new \stdClass();
        $from = null;
        $fromdate = ($from !== null) ? $from : date('Y-m-d');
        $roleid = $DB->get_field('role','id', array('shortname' => 'trainee'));
        $sqltrainingprograms = "SELECT  pe.userid,ltp.name,ltp.namearabic,date(from_unixtime(tpo.startdate)) AS startdate,tpo.time 
                                FROM {tp_offerings} as tpo
                                INNER JOIN {local_trainingprogram} AS ltp ON ltp.id = tpo.trainingid
                                INNER JOIN {program_enrollments} AS pe ON pe.offeringid = tpo.id 
                                INNER JOIN {program_completions} AS pc ON pc.programid = ltp.id
                                WHERE roleid= $roleid 
                                      AND date(from_unixtime(tpo.enddate)) = '$fromdate' 
                                      AND (tpo.enddate+tpo.time+tpo.duration) >= $lastruntime 
                                      AND (tpo.enddate+tpo.time+tpo.duration) <= $timenow";
        $trainingprogramslist = $DB->get_records_sql($sqltrainingprograms);
        if (!empty($trainingprogramslist)) {
            foreach ($trainingprogramslist as $key => $trainingprogram) {
                $sql = "SELECT lnt.* 
                        FROM {local_notification_type} AS lnt
                        WHERE shortname IN ('trainingprogram_send_conclusion') ";
                $notificationtype = $DB->get_record_sql($sql);
                
                $user = $DB->get_record('user', array('id' => $trainingprogram->userid, 'deleted' => 0));
                if ($user->lang == 'ar'){
                    $notificationtype->trainingprogram_related_module_name = $trainingprogram->namearabic;
                    

                } else {
                    $notificationtype->trainingprogram_related_module_name = $trainingprogram->name;
                    

                } 

                $localuserrecord = $DB->get_record('local_users',['userid'=> $user->id]);
                $notificationtype->trainingprogram_related_user_name  = ($localuserrecord)? (($user->lang == 'ar') ? $localuserrecord->firstnamearabic.' '.$localuserrecord->middlenamearabic.' '.$localuserrecord->thirdnamearabic.' '.$localuserrecord->lastnamearabic  : $localuserrecord->firstname.' '.$localuserrecord->middlenameen.' '.$localuserrecord->thirdnameen.' '.$localuserrecord->lastname) :fullname($DB->get_record('user',array('id'=>$user->id))); 
                $this->trainingprogram_notification('trainingprogram_send_conclusion', $touser = $user, $fromuser = get_admin(), $notificationtype, $waitinglistid = 0);
            }
        }
    }
    public function trainingprogram_enrolled_inactive_accounts_notification($lastruntime,$nextruntime,$timenow)
    {
        global $DB, $USER;
        $from = null;
        $data = new \stdClass();
        $fromdate = ($from !== null) ? $from : date('Y-m-d');
        $sqltrainingprograms = "SELECT pe.userid AS userid,tp.name,tp.namearabic AS program_name,tp.id AS programid 
                                FROM {local_trainingprogram} AS tp 
                                INNER JOIN {program_enrollments} AS pe ON tp.id=pe.programid 
                                INNER JOIN {user} AS us ON pe.userid =us.id 
                                WHERE date(from_unixtime(us.lastaccess+(60*60*48))) ='$fromdate' 
                                AND (us.lastaccess+(60*60*48)) >= $lastruntime 
                                AND (us.lastaccess+(60*60*48)) <= $timenow";
                               // exit($sqltrainingprograms);
        $trainingprogramslist = $DB->get_records_sql($sqltrainingprograms);
        if (!empty($trainingprogramslist)) {
            foreach ($trainingprogramslist as $key => $trainingprogram) {
                $completiondata = $DB->get_record('program_completions', array('userid' => $trainingprogram->userid, 'programid' => $trainingprogram->programid));
                  if(empty($completiondata)){
                $sql = "SELECT lnt.* 
                        FROM {local_notification_type} AS lnt
                        WHERE shortname IN ('trainingprogram_enrolled_inactive_accounts') ";
             $thispageurl = new \moodle_url('/local/trainingprogram/programdetailedview.php?programid='.$trainingprogram->programid);
                $user = $DB->get_record('user', array('id' => $trainingprogram->userid, 'deleted' => 0));
                $notificationtype = $DB->get_record_sql($sql);
                if ($user->lang == 'ar'){
                    $notificationtype->program_name = $trainingprogram->namearabic;
                   

                } else {
                    $notificationtype->program_name = $trainingprogram->name;
                    

                }

                $localuserrecord = $DB->get_record('local_users',['userid'=> $user->id]);
                $notificationtype->program_userfullname  = ($localuserrecord)? (($user->lang == 'ar') ? $localuserrecord->firstnamearabic.' '.$localuserrecord->middlenamearabic.' '.$localuserrecord->thirdnamearabic.' '.$localuserrecord->lastnamearabic  : $localuserrecord->firstname.' '.$localuserrecord->middlenameen.' '.$localuserrecord->thirdnameen.' '.$localuserrecord->lastname) :fullname($DB->get_record('user',array('id'=>$user->id)));                 
                $notificationtype->program_link = $thispageurl;
                $this->trainingprogram_notification('trainingprogram_enrolled_inactive_accounts', $touser = $user, $fromuser = get_admin(), $notificationtype, $waitinglistid = 0);
            }
            }
        }
    }
    public function trainingprogram_pre_assessment_opened_notification($lastruntime,$nextruntime,$timenow)
    {
        global $DB, $USER;
        $data = new \stdClass();
        $from = null;
        $fromdate = ($from !== null) ? $from : date('Y-m-d');
        $roleid = $DB->get_field('role','id', array('shortname' => 'trainee'));
        $sqltrainingprograms = "SELECT  pe.userid,ltp.name,ltp.namearabic,ltp.courseid AS courseid,
                                date(from_unixtime(tpo.startdate)) as startdate,
                                date(from_unixtime(tpo.startdate+ltp.duration)) as enddate,
                                tpo.prequiz AS preexam  
                                FROM {tp_offerings} as tpo
                                INNER JOIN {local_trainingprogram} AS ltp ON ltp.id = tpo.trainingid
                                INNER JOIN {program_enrollments} AS pe ON pe.offeringid = tpo.id 
                                WHERE roleid= $roleid 
                                      AND ltp.evaluationmethods= 0 
                                      AND date(from_unixtime(tpo.startdate)) = '$fromdate' 
                                      AND (tpo.startdate+tpo.time) >=$lastruntime 
                                      AND (tpo.startdate+tpo.time)<= $timenow";
        $trainingprogramslist = $DB->get_records_sql($sqltrainingprograms);
        if (!empty($trainingprogramslist)) {
            foreach ($trainingprogramslist as $key => $trainingprogram) {
                $sql = "SELECT lnt.* 
                        FROM {local_notification_type} AS lnt
                        WHERE shortname IN ('trainingprogram_pre_assessment_opened') ";
            $thispageurl = new \moodle_url('/mod/quiz/view.php?id='.$trainingprogram->preexam);
                $notificationtype = $DB->get_record_sql($sql);
               
                $notificationtype->assessment_link = $thispageurl;
                $notificationtype->assessment_start_date = date('d-M-Y', strtotime($trainingprogram->startdate));
                $notificationtype->assessment_end_date = date('d-M-Y', strtotime($trainingprogram->enddate));
                $user = $DB->get_record('user', array('id' => $trainingprogram->userid, 'deleted' => 0));
                if ($user->lang == 'ar'){
                    $notificationtype->program_name = $trainingprogram->namearabic;
                   

                } else {
                    $notificationtype->program_name = $trainingprogram->name;
                    

                }

                 $localuserrecord = $DB->get_record('local_users',['userid'=> $user->id]);
                $notificationtype->program_userfullname  = ($localuserrecord)? (($user->lang == 'ar') ? $localuserrecord->firstnamearabic.' '.$localuserrecord->middlenamearabic.' '.$localuserrecord->thirdnamearabic.' '.$localuserrecord->lastnamearabic  : $localuserrecord->firstname.' '.$localuserrecord->middlenameen.' '.$localuserrecord->thirdnameen.' '.$localuserrecord->lastname) :fullname($DB->get_record('user',array('id'=>$user->id)));  

                $this->trainingprogram_notification('trainingprogram_pre_assessment_opened', $touser = $user, $fromuser = get_admin(), $notificationtype, $waitinglistid = 0);
            }
        }
    }
    public function trainingprogram_post_assessment_opened_notification($lastruntime,$nextruntime,$timenow)
    {
        global $DB, $USER;
        $data = new \stdClass();
        $from = null;
        $fromdate = ($from !== null) ? $from : date('Y-m-d');
        $roleid = $DB->get_field('role','id', array('shortname' => 'trainee'));
        $sqltrainingprograms = "SELECT  pe.userid,ltp.name,ltp.namearabic,ltp.courseid AS courseid,
                                date(from_unixtime(tpo.enddate)) as startdate,
                                date(from_unixtime(ltp.duration)) AS duration,tpo.time,
                                date(from_unixtime(tpo.enddate+ltp.duration)) as enddate,
                                tpo.postquiz AS postexam 
                                FROM {tp_offerings} as tpo
                                INNER JOIN {local_trainingprogram} AS ltp ON ltp.id = tpo.trainingid
                                INNER JOIN {program_enrollments} AS pe ON pe.offeringid = tpo.id 
                                WHERE roleid= $roleid 
                                      AND ltp.evaluationmethods= 1 
                                      AND date(from_unixtime(tpo.enddate)) = '$fromdate'
                                      AND (tpo.enddate+tpo.time+tpo.duration) >=$lastruntime 
                                      AND (tpo.enddate+tpo.time+tpo.duration)<= $timenow";
        $trainingprogramslist = $DB->get_records_sql($sqltrainingprograms);
        if (!empty($trainingprogramslist)) {
            foreach ($trainingprogramslist as $key => $trainingprogram) {
                $sql = "SELECT lnt.* 
                        FROM {local_notification_type} AS lnt
                        WHERE shortname IN ('trainingprogram_post_assessment_opened') ";
                $notificationtype = $DB->get_record_sql($sql);
                $thispageurl = new \moodle_url('/mod/quiz/view.php?id='.$trainingprogram->postexam);
                $notificationtype->assessment_link = $thispageurl;
                
                $notificationtype->assessment_start_date = date('d-M-Y', strtotime($trainingprogram->startdate));
                $notificationtype->assessment_end_date = date('d-M-Y', strtotime($trainingprogram->enddate));
                $user = $DB->get_record('user', array('id' => $trainingprogram->userid, 'deleted' => 0));
                if ($user->lang == 'ar'){
                    $notificationtype->program_name = $trainingprogram->namearabic;
                   
                } else {
                    $notificationtype->program_name = $trainingprogram->name;
           

                } 

                 $localuserrecord = $DB->get_record('local_users',['userid'=> $user->id]);
                $notificationtype->program_userfullname  = ($localuserrecord)? (($user->lang == 'ar') ? $localuserrecord->firstnamearabic.' '.$localuserrecord->middlenamearabic.' '.$localuserrecord->thirdnamearabic.' '.$localuserrecord->lastnamearabic  : $localuserrecord->firstname.' '.$localuserrecord->middlenameen.' '.$localuserrecord->thirdnameen.' '.$localuserrecord->lastname) :fullname($DB->get_record('user',array('id'=>$user->id))); 
                $this->trainingprogram_notification('trainingprogram_post_assessment_opened', $touser = $user, $fromuser = get_admin(), $notificationtype, $waitinglistid = 0);
            }
        }
    }
    public function trainingprogram_pre_assessment_closed_notification($lastruntime,$nextruntime,$timenow)
    {
        global $DB, $USER;
        $data = new \stdClass();
        $from = null;
        $fromdate = ($from !== null) ? $from : date('Y-m-d');
        $roleid = $DB->get_field('role','id', array('shortname' => 'trainee'));
        $sqltrainingprograms = "SELECT  pe.userid,ltp.name,ltp.namearabic,ltp.courseid AS courseid,
                                date(from_unixtime(tpo.startdate)) AS startdate,ltp.duration AS duration,
                                tpo.time,date(from_unixtime(tpo.startdate+ltp.duration)) AS enddate,
                                tpo.prequiz AS preexam  
                                FROM {tp_offerings} AS tpo
                                INNER JOIN {local_trainingprogram} AS ltp ON ltp.id = tpo.trainingid
                                INNER JOIN {program_enrollments} AS pe ON pe.offeringid = tpo.id 
                                WHERE roleid= $roleid 
                                      AND ltp.evaluationmethods= 0 
                                      AND date(from_unixtime(tpo.startdate+ltp.duration)) = '$fromdate'
                                      AND (tpo.startdate+tpo.time+ltp.duration) >=$lastruntime 
                                      AND (tpo.startdate+tpo.time+ltp.duration) <= $timenow";
        $trainingprogramslist = $DB->get_records_sql($sqltrainingprograms);
        if (!empty($trainingprogramslist)) {
            foreach ($trainingprogramslist as $key => $trainingprogram) {
                $sql = "SELECT lnt.* 
                        FROM {local_notification_type} AS lnt
                        WHERE shortname IN ('trainingprogram_pre_assessment_closed') ";
                $notificationtype = $DB->get_record_sql($sql);
                $thispageurl = new \moodle_url('/mod/quiz/view.php?id='.$trainingprogram->preexam);
                $notificationtype->assessment_link = $thispageurl;                
                $notificationtype->assessment_start_date = date('d-M-Y', strtotime($trainingprogram->startdate));
                $notificationtype->assessment_end_date = date('d-M-Y', strtotime($trainingprogram->enddate));
                $user = $DB->get_record('user', array('id' => $trainingprogram->userid, 'deleted' => 0));
                if ($user->lang == 'ar'){
                    $notificationtype->program_name = $trainingprogram->namearabic;
                    

                } else {
                    $notificationtype->program_name = $trainingprogram->name;
                    

                }

                $localuserrecord = $DB->get_record('local_users',['userid'=> $user->id]);
                $notificationtype->program_userfullname  = ($localuserrecord)? (($user->lang == 'ar') ? $localuserrecord->firstnamearabic.' '.$localuserrecord->middlenamearabic.' '.$localuserrecord->thirdnamearabic.' '.$localuserrecord->lastnamearabic  : $localuserrecord->firstname.' '.$localuserrecord->middlenameen.' '.$localuserrecord->thirdnameen.' '.$localuserrecord->lastname) :fullname($DB->get_record('user',array('id'=>$user->id))); 


                $this->trainingprogram_notification('trainingprogram_pre_assessment_closed', $touser = $user, $fromuser = get_admin(), $notificationtype, $waitinglistid = 0);
            }
        }
    }
    public function trainingprogram_post_assessment_closed_notification($lastruntime,$nextruntime,$timenow)
    {
        global $DB, $USER;
        $data = new \stdClass();
        $from = null;
        $fromdate = ($from !== null) ? $from : date('Y-m-d');
        $roleid = $DB->get_field('role','id', array('shortname' => 'trainee'));
        $sqltrainingprograms = "SELECT  pe.userid,ltp.name,ltp.namearabic,ltp.courseid AS courseid,
                                date(from_unixtime(tpo.enddate)) AS startdate,ltp.duration AS duration,
                                tpo.time,date(from_unixtime(tpo.enddate+ltp.duration)) AS enddate 
                                ,tpo.postquiz AS postexam 
                                FROM {tp_offerings} AS tpo
                                INNER JOIN {local_trainingprogram} AS ltp ON ltp.id = tpo.trainingid
                                INNER JOIN {program_enrollments} AS pe ON pe.offeringid = tpo.id 
                                WHERE roleid= $roleid 
                                      AND ltp.evaluationmethods= 1 
                                      AND date(from_unixtime(tpo.enddate+ltp.duration)) = '$fromdate'
                                      AND (tpo.enddate+tpo.time+tpo.duration+ltp.duration) >=$lastruntime 
                                      AND (tpo.enddate+tpo.time+tpo.duration+ltp.duration)<= $timenow";
        $trainingprogramslist = $DB->get_records_sql($sqltrainingprograms);
        if (!empty($trainingprogramslist)) {
            foreach ($trainingprogramslist as $key => $trainingprogram) {
                $sql = "SELECT lnt.* 
                        FROM {local_notification_type} AS lnt
                        WHERE shortname IN ('trainingprogram_post_assessment_closed') ";
                $notificationtype = $DB->get_record_sql($sql);
                $thispageurl = new \moodle_url('/mod/quiz/view.php?id='.$trainingprogram->postexam);
                $notificationtype->assessment_link = $thispageurl;               
                $notificationtype->assessment_start_date = date('d-M-Y', strtotime($trainingprogram->startdate));
                $notificationtype->assessment_end_date =date('d-M-Y', strtotime($trainingprogram->enddate));
                $user = $DB->get_record('user', array('id' => $trainingprogram->userid, 'deleted' => 0));
                if ($user->lang == 'ar'){
                    $notificationtype->program_name = $trainingprogram->namearabic;
                  

                } else {
                    $notificationtype->program_name = $trainingprogram->name;
                    

                } 

                $localuserrecord = $DB->get_record('local_users',['userid'=> $user->id]);
                $notificationtype->program_userfullname  = ($localuserrecord)? (($user->lang == 'ar') ? $localuserrecord->firstnamearabic.' '.$localuserrecord->middlenamearabic.' '.$localuserrecord->thirdnamearabic.' '.$localuserrecord->lastnamearabic  : $localuserrecord->firstname.' '.$localuserrecord->middlenameen.' '.$localuserrecord->thirdnameen.' '.$localuserrecord->lastname) :fullname($DB->get_record('user',array('id'=>$user->id))); 

                $this->trainingprogram_notification('trainingprogram_post_assessment_closed', $touser = $user, $fromuser = get_admin(), $notificationtype, $waitinglistid = 0);
            }
        }
    }
    public function trainingprogram_assignment_deadline_24_hours_notification($lastruntime,$nextruntime,$timenow)
    {
        global $DB, $USER;
        $data = new \stdClass();
        $from = null;
        $fromdate = ($from !== null) ? $from : date('Y-m-d');
        $roleid = $DB->get_field('role','id', array('shortname' => 'trainee'));
        $sqltrainingprograms = "SELECT pe.userid,ltp.name,ltp.namearabic,ltp.courseid AS courseid,
                                ltp.duration AS duration,a.duedate 
                                FROM {assign} AS a
                                JOIN {local_trainingprogram} AS ltp ON ltp.courseid = a.course
                                JOIN {program_enrollments} AS pe ON pe.courseid = a.course 
                                WHERE roleid= $roleid
                                      AND date(from_unixtime(a.duedate-(24*60*60))) = '$fromdate'
                                      AND (a.duedate-(24*60*60)) <= $lastruntime
                                      AND (a.duedate-(24*60*60)) >= $timenow";
        $trainingprogramslist = $DB->get_records_sql($sqltrainingprograms);
        if (!empty($trainingprogramslist)) {
            foreach ($trainingprogramslist as $key => $trainingprogram) {
                $sql = "SELECT lnt.* 
                        FROM {local_notification_type} AS lnt
                        WHERE shortname IN ('trainingprogram_assignment_deadline_24_hours') ";
                $notificationtype = $DB->get_record_sql($sql);
               
                $user = $DB->get_record('user', array('id' => $trainingprogram->userid, 'deleted' => 0));
                if ($user->lang == 'ar'){
                    $notificationtype->program_name = $trainingprogram->namearabic;
                   

                } else {
                    $notificationtype->program_name = $trainingprogram->name;
                    
                } 


                 $localuserrecord = $DB->get_record('local_users',['userid'=> $user->id]);
                $notificationtype->program_userfullname  = ($localuserrecord)? (($user->lang == 'ar') ? $localuserrecord->firstnamearabic.' '.$localuserrecord->middlenamearabic.' '.$localuserrecord->thirdnamearabic.' '.$localuserrecord->lastnamearabic  : $localuserrecord->firstname.' '.$localuserrecord->middlenameen.' '.$localuserrecord->thirdnameen.' '.$localuserrecord->lastname) :fullname($DB->get_record('user',array('id'=>$user->id))); 

                $this->trainingprogram_notification('trainingprogram_assignment_deadline_24_hours', $touser = $user, $fromuser = get_admin(), $notificationtype, $waitinglistid = 0);
            }
        }
    }
    public function trainingprogram_assignment_deadline_4_hours_notification($lastruntime,$nextruntime,$timenow)
    {
        global $DB, $USER;
        $data = new \stdClass();
        $from = null;
       $fromdate = ($from !== null) ? $from : date('Y-m-d');
        $roleid = $DB->get_field('role','id', array('shortname' => 'trainee'));
        $sqltrainingprograms = "SELECT pe.userid,ltp.name,ltp.namearabic,ltp.courseid AS courseid,
                                ltp.duration AS duration,a.duedate 
                                FROM {assign} AS a
                                JOIN {local_trainingprogram} AS ltp ON ltp.courseid = a.course
                                JOIN {program_enrollments} AS pe ON pe.courseid = a.course 
                                WHERE roleid= $roleid 
                                      AND date(from_unixtime(a.duedate-(4*60*60))) = '$fromdate'
                                      AND (a.duedate-(4*60*60)) <= $lastruntime
                                      AND (a.duedate-(4*60*60)) >= $timenow";
        $trainingprogramslist = $DB->get_records_sql($sqltrainingprograms);
        if (!empty($trainingprogramslist)) {
            foreach ($trainingprogramslist as $key => $trainingprogram) {
                $sql = "SELECT lnt.* 
                        FROM {local_notification_type} AS lnt
                        WHERE shortname IN ('trainingprogram_assignment_deadline_4_hours') ";
                $notificationtype = $DB->get_record_sql($sql);               
                $user = $DB->get_record('user', array('id' => $trainingprogram->userid, 'deleted' => 0));
                   if ($user->lang == 'ar'){
                    $notificationtype->program_name = $trainingprogram->namearabic;
                   
                } else {
                    
                   $notificationtype->program_name = $trainingprogram->name;
                } 

                $localuserrecord = $DB->get_record('local_users',['userid'=> $user->id]);
                $notificationtype->program_userfullname  = ($localuserrecord)? (($user->lang == 'ar') ? $localuserrecord->firstnamearabic.' '.$localuserrecord->middlenamearabic.' '.$localuserrecord->thirdnamearabic.' '.$localuserrecord->lastnamearabic  : $localuserrecord->firstname.' '.$localuserrecord->middlenameen.' '.$localuserrecord->thirdnameen.' '.$localuserrecord->lastname) :fullname($DB->get_record('user',array('id'=>$user->id))); 
                $this->trainingprogram_notification('trainingprogram_assignment_deadline_4_hours', $touser = $user, $fromuser = get_admin(), $notificationtype, $waitinglistid = 0);
            }
        }
    }
 public function trainingprogram_before_10_days_notification($lastruntime,$nextruntime,$timenow)
 {

     global $DB, $USER;
     $data = new \stdClass();
     $from = null;
     // $from = date('Y-m-d');
     // $from = date('2022-05-11');

     $fromdate = ($from !== null) ? $from : date('Y-m-d');                                  
     $sqltrainingprograms = "SELECT  pe.userid,pe.roleid,ltp.name,ltp.namearabic,DATE(from_unixtime(tpo.startdate)) AS startdate,tpo.time as ttime,tpo.id as tpid FROM {tp_offerings} AS tpo
     INNER JOIN {local_trainingprogram} AS ltp ON ltp.id = tpo.trainingid
     INNER JOIN {program_enrollments} as pe on pe.offeringid = tpo.id 
     WHERE DATE(from_unixtime(tpo.startdate-60*60*24*10)) = '$fromdate' 
            AND ((tpo.startdate+tpo.time)-60*60*24*10) >= $lastruntime
            AND ((tpo.startdate+tpo.time)-60*60*24*10) <= $timenow";
     
    $trainingprogramslist = $DB->get_records_sql($sqltrainingprograms);  

     if (!empty($trainingprogramslist)) {
         foreach ($trainingprogramslist as $key => $trainingprogram) {           

             $sql = "SELECT lnt.* FROM {local_notification_type} AS lnt
             WHERE shortname IN ('trainingprogram_before_10_days') ";
             $notificationtype = $DB->get_record_sql($sql);  
            
             $trainingprogram->program_date = $trainingprogram->startdate;
             $starttimemeridian = gmdate('a',$trainingprogram->ttime);
   
             $trainer = $DB->get_field('role','id',array('shortname'=>'trainer')); 
             $tpenrolments = $DB->get_records('program_enrollments',array('offeringid'=>$trainingprogram->tpid));
             foreach($tpenrolments as  $tpenrolment){

             if($trainer == $tpenrolment->roleid )   {

                $tpusers = $DB->get_record('local_users',array('userid'=>$trainingprogram->userid));
                $tpusers->program_userfullname=$tpusers->firstname .' '. $tpusers->middlenameen.' '. $tpusers->thirdnameen.' '. $tpusers->lastname;
                $tpusers->program_userarabicfullname = $tpusers->firstnamearabic .' '. $tpusers->middlenamearabic.' '. $tpusers->thirdnamearabic.' '. $tpusers->lastnamearabic;
                $trainee = $DB->get_record('user',array('id'=>$trainingprogram->userid)); 
                $starttimemeridian = gmdate('a',$trainingprogram->ttime);

                if($tpusers->lang == 'ar'){
                    $trainingprogram->program_name = $trainingprogram->namearabic;
                    $trainingprogram->program_userfullname =  $tpusers->program_userarabicfullname;
                    $startmeridian = ($starttimemeridian == 'am')? 'صباحًا' : 'مساءً';
                } else{
                    $trainingprogram->program_name = $trainingprogram->name;
                    $trainingprogram->program_userfullname =  $tpusers->program_userfullname;
                    $startmeridian = ($starttimemeridian == 'am')? 'AM': 'PM';
                }
                $trainingprogram->program_time =  gmdate("H:i",$trainingprogram->ttime) . ''.$startmeridian ;   
             
         }

        }
        $this->trainingprogram_notification('trainingprogram_before_10_days', $touser = $trainee, $fromuser = get_admin(), $trainingprogram, $waitinglistid = 0);  
     }    
 }
}

public function send_trainingprogram_reschedule_notification($trainingprograminstance, $touser, $fromuser, $emailtype, $notification, $waitinglistid = 0)
{
    global $PAGE,$DB;
    $datamailobject = new \stdClass();
    $datamailobject->emailtype = $emailtype;
    $datamailobject->notification_infoid = $notification->id;
    $datamailobject->plugintype = $notification->plugintype;
    $datamailobject->pluginname = $notification->pluginname;
    $datamailobject->moduleid = 1;
    $datamailobject->body = $notification->body;
    $datamailobject->subject = $notification->subject;
    $datamailobject->arabic_body = $notification->arabic_body;
    $datamailobject->arabic_subject = $notification->arabic_subject;
    $datamailobject->sendsms = 1;
    $datamailobject->trainingprogramid = $trainingprograminstance->id;
    $datamailobject->teammemberid = 0;
    $datamailobject->userlang = $touser->lang;  
    $datamailobject->program_userfullname = $trainingprograminstance->program_userfullname; 
    $datamailobject->program_name = $trainingprograminstance->program_name;

    $datamailobject->offering_pastdate     = $trainingprograminstance->offering_pastdate;
    $datamailobject->offering_pasttime = $trainingprograminstance->offering_pasttime;
    $datamailobject->offering_presentdate = $trainingprograminstance->offering_presentdate;
    $datamailobject->offering_presenttime = $trainingprograminstance->offering_presenttime;        
    $this->log_email_notification($touser, $fromuser, $datamailobject);

    
}
public function send_trainingprogram_cancelrequest_notification($trainingprograminstance, $touser, $fromuser, $emailtype, $notification, $waitinglistid = 0)
{
    global $DB;
    $datamailobject = new \stdClass();
    $datamailobject->emailtype = $emailtype;
    $datamailobject->notification_infoid = $notification->id;
    $datamailobject->plugintype = $notification->plugintype;
    $datamailobject->pluginname = $notification->pluginname;
    $datamailobject->moduleid = 1;
    $datamailobject->body = $notification->body;
    $datamailobject->subject = $notification->subject;
    $datamailobject->arabic_body = $notification->arabic_body;
    $datamailobject->arabic_subject = $notification->arabic_subject;
    $datamailobject->sendsms = 1;
    $datamailobject->trainingprogramid = $trainingprograminstance->id;
    $datamailobject->teammemberid = 0;  
    $financialmangerroleusers = $this->getsystemlevel_role_users('financial_manager', 0);  
    if($financialmangerroleusers){
        foreach( $financialmangerroleusers as  $financialmangerroleuser){
                $datamailobject->userlang =  $financialmangerroleuser->lang; 
                $time = time();
                $starttimemeridian = gmdate('a', $time ); 
                $finanacemanager = $DB->get_record('local_users',array('userid'=>$financialmangerroleuser->id));
                if($datamailobject->userlang == 'ar'){  
                    $startmeridian = ($starttimemeridian == 'am')? 'صباحًا' : 'مساءً';
                    $datamailobject->program_tofullname = $trainingprograminstance->program_tofullnamear  ;  
                    $datamailobject->program_name = $trainingprograminstance->program_namear;         
                    $datamailobject->program_userfullname =   $finanacemanager ->firstnamearabic.''.$finanacemanager ->middlenamearabic.''.$finanacemanager ->thirdnamearabic.'' .$finanacemanager ->lastnamearabic;
                }else{
                    $startmeridian = ($starttimemeridian == 'am')? 'AM': 'PM';    
                    $datamailobject->program_tofullname =  $trainingprograminstance->program_tofullnameen;
                    $datamailobject->program_name = $trainingprograminstance->program_nameen;
                    $datamailobject->program_userfullname = $finanacemanager->firstname.''. $finanacemanager->middlenameen.''.$finanacemanager->thirdnameen.'' .$finanacemanager->lastname;
                }
                $datamailobject->program_canceltime =  gmdate("H:i:s",$time) .'' .$startmeridian; 
            $this->log_email_notification($financialmangerroleuser, $fromuser, $datamailobject);
        }
    }
    
 }
}
