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
 * @package local_userapproval
 */
namespace local_userapproval;


class notification extends \local_notifications\notification{

    public function get_string_identifiers($emailtype){
        switch($emailtype){
            case 'registration':               
                  $strings = "[user_fullname],[user_trainingofficialname],[user_username],[user_password]";
                break;

            case 'approve':               
                 $strings = "[user_fullname]";
                break;

            case 'reject':
                  $strings = "[user_fullname]";
              
                break;
            case 'organizational_approval':
                $strings = "[user_fullname],[user_organization],[user_organizationofficialname]";               
                break;
            case 'individual_registration'  :
                $strings = "[user_fullname]";               
                break; 




        }

        return $strings;
    }
    public function userapproval_notification($emailtype, $touser, $fromuser, $userapprovalinstance,$waitinglistid=0){
        if($notification = $this->get_existing_notification($userapprovalinstance, $emailtype)){

            $functionname = 'send_'.$emailtype.'_notification';

            $this->$functionname($userapprovalinstance, $touser, $fromuser, $emailtype, $notification,$waitinglistid);
        }
        
    }
	public function send_registration_notification($userapprovalinstance, $touser, $fromuser, $emailtype, $notification,$waitinglistid=0){
        global $PAGE,$DB;
  
        $datamailobject = new \stdClass();
        $datamailobject->emailtype = $emailtype;
        $datamailobject->notification_infoid = $notification->id;
        $datamailobject->plugintype = $notification->plugintype;
        $datamailobject->pluginname = $notification->pluginname;
        $datamailobject->user_fullname = $userapprovalinstance->user_fullname;
        $datamailobject->body = $notification->body;
        $datamailobject->subject = $notification->subject;
        $datamailobject->arabic_body=$notification->arabic_body;
        $datamailobject->arabic_subject=$notification->arabic_subject;
        $datamailobject->moduleid = 1;
        $datamailobject->teammemberid = 0;  
        $datamailobject->userlang=$touser->lang; 
        // $localuserrecord = $DB->get_record('local_users',['userid'=> $touser->id]); 
         // if($datamailobject->userlang == "ar"){
         //    $datamailobject->user_fullname = $DB->get_field('local_users','firstnamearabic',array('userid'=>$touser->id)).' '.$DB->get_field('local_users','middlenamearabic',array('userid'=>$touser->id)).' '.$DB->get_field('local_users','thirdnamearabic',array('userid'=>$touser->id)).' '.$DB->get_field('local_users','lastnamearabic',array('userid'=>$touser->id));
         // }
         // else{
           $datamailobject->user_fullname = $userapprovalinstance->user_fullname;

           if($userapprovalinstance->username){
                $datamailobject->user_username = $userapprovalinstance->username;
                $datamailobject->user_password = $userapprovalinstance->givenpassword;
           
           }
         // } 

        
        // $datamailobject->user_fullname  = ($localuserrecord)? (($datamailobject->userlang == 'ar') ? $localuserrecord->firstnamearabic.' '.$localuserrecord->middlenamearabic.' '.$localuserrecord->thirdnamearabic.' '.$localuserrecord->lastnamearabic  : $localuserrecord->firstname.' '.$localuserrecord->middlenameen.' '.$localuserrecord->thirdnameen.' '.$localuserrecord->lastname) :fullname($DB->get_record('user',array('id'=>$touser->id)));           
        $this->log_email_notification($touser, $fromuser, $datamailobject);
        // $roleusers=$this->getsystemlevel_role_users('to',0);
        // if($roleusers){
        //     foreach($roleusers as $roleuser){ 
        //         $datamailobject->userlang = $roleuser->lang;
        //         if($datamailobject->userlang == 'ar'){                    
        //             $arabicusernames = $DB->get_record('local_users',array('userid'=>$roleuser->id));
        //             $datamailobject->user_trainingofficialname = $arabicusernames->firstnamearabic.' '.$arabicusernames->middlenamearabic.' '.$arabicusernames->thirdnamearabic.' '.$arabicusernames->lastnamearabic;     

        //          } else{
        //               $datamailobject->user_trainingofficialname =  $roleuser->firstname.' '.$roleuser->thirdnameen.' '.$roleuser->middlenameen.' '.$roleuser->lastname;
        //              }      
        //         $datamailobject->body = "Dear [user_trainingofficialname], a registration named as [user_fullname] registered  at Financial Academy has been approved. Thanks"; 
        //         $datamailobject->arabic_body="عزيزي [user_trainingofficialname] ، تمت الموافقة على تسجيل باسم [user_fullname] مسجل في Financial Academy. شكرًا";
        //         $datamailobject->user_trainingofficialname =  $roleuser->firstname.$roleuser->lastname;          
        //         $this->log_email_notification($roleuser, $fromuser, $datamailobject);
        //     }
        // }

        
	}





    public function send_individual_registration_notification($userapprovalinstance, $touser, $fromuser, $emailtype, $notification,$waitinglistid=0){
        global $PAGE,$DB;
        $datamailobject = new \stdClass();

        $datamailobject->emailtype = $emailtype;
        $datamailobject->notification_infoid = $notification->id;
        $datamailobject->plugintype = $notification->plugintype;
        $datamailobject->pluginname = $notification->pluginname;
        $datamailobject->user_fullname = $userapprovalinstance->user_fullname;
        $datamailobject->body = $notification->body;
        $datamailobject->subject = $notification->subject;
        $datamailobject->arabic_body = $notification->arabic_body;
        $datamailobject->arabic_subject = $notification->arabic_subject;
        $datamailobject->moduleid = 1;
        $datamailobject->touserid = $touser->id;
        $datamailobject->fromuserid = $fromuser->id;
        $datamailobject->teammemberid = 0;
        $datamailobject->userlang = $touser->lang;
        $this->log_email_notification($touser, $fromuser, $datamailobject);

    }
    public function send_approve_notification($userapprovalinstance, $touser, $fromuser, $emailtype, $notification,$waitinglistid=0){
        global $PAGE,$DB;


        $datamailobject = new \stdClass();

        $datamailobject->emailtype = $emailtype;
        $datamailobject->notification_infoid = $notification->id;
        $datamailobject->plugintype = $notification->plugintype;
        $datamailobject->pluginname = $notification->pluginname;
        $datamailobject->user_fullname = $userapprovalinstance->user_fullname;
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
    public function send_reject_notification($userapprovalinstance, $touser, $fromuser, $emailtype, $notification,$waitinglistid=0){
        global $PAGE,$DB;
        
        $datamailobject = new \stdClass();
        $datamailobject->emailtype = $emailtype;
        $datamailobject->notification_infoid = $notification->id;
        $datamailobject->plugintype = $notification->plugintype;
        $datamailobject->pluginname = $notification->pluginname;
        $datamailobject->user_fullname = $userapprovalinstance->user_fullname;
        $datamailobject->body = $notification->body;
        $datamailobject->subject = $notification->subject;
        $datamailobject->arabic_body = $notification->arabic_body;
        $datamailobject->arabic_subject = $notification->arabic_subject;
        $datamailobject->moduleid = 1;
        $datamailobject->touserid = $touser->id;
        $datamailobject->fromuserid = $fromuser->id;
        $datamailobject->teammemberid = 0;

        $this->log_email_notification($touser, $fromuser, $datamailobject);
        
    }
    public function send_organizational_approval_notification($userapprovalinstance, $touser, $fromuser, $emailtype, $notification,$waitinglistid=0){
        global $PAGE,$DB;


        $datamailobject = new \stdClass();

        $datamailobject->emailtype = $emailtype;
        $datamailobject->notification_infoid = $notification->id;
        $datamailobject->plugintype = $notification->plugintype;
        $datamailobject->pluginname = $notification->pluginname;       
        $datamailobject->user_fullname = $userapprovalinstance->user_fullname;      
        $datamailobject->user_organization =$userapprovalinstance->user_organization;
        $datamailobject->body = $notification->body;
        $datamailobject->subject = $notification->subject;
        $datamailobject->arabic_body = $notification->arabic_body;
        $datamailobject->arabic_subject = $notification->arabic_subject;
        $datamailobject->moduleid = 1;
        $datamailobject->teammemberid = 0; 
        $datamailobject->userlang  = $touser->lang; 

        $this->log_email_notification($touser, $fromuser, $datamailobject);
        $roleusers=$this->getsystemlevel_role_users('organizationofficial',$touser->orgid);
        if($roleusers){
            foreach($roleusers as $roleuser){ 
                 $datamailobject->userlang = $roleuser->lang;
                if($datamailobject->userlang == 'ar'){                    
                    $arabicusernames = $DB->get_record('local_users',array('userid'=>$roleuser->id));
                    $datamailobject->user_organizationofficialname = $arabicusernames->firstnamearabic.' '.$arabicusernames->middlenamearabic.' '.$arabicusernames->thirdnamearabic.' '.$arabicusernames->lastnamearabic;
                    $organizationapproval->user_organization = $DB->get_field('local_organization','fullnameinarabic',array('id'=>$organizationapproval->orgid));
                 } else{
                      $datamailobject->user_organizationofficialname =  $roleuser->firstname.' '.$roleuser->middlenameen.' '.$roleuser->thirdnameen.' '.$roleuser->lastname;
                      $organizationapproval->user_organization = $DB->get_field('local_organization','fullname',array('id'=>$organizationapproval->orgid)); 
                }   
                $datamailobject->body = "Dear [user_organizationofficialname], a registration named as [user_fullname] registered in your [user_organization] organization at Financial Academy has been approved. Thanks";  
                $datamailobject->arabic_body =" عزيزي [user_organizationofficialname] ، تمت الموافقة على تسجيل باسم [user_fullname] مسجل في مؤسستك [user_organization] في Financial Academy. شكرًا";
                $datamailobject->user_organizationofficialname= $roleuser->firstname.$roleuser->lastname;               
                $this->log_email_notification($roleuser, $fromuser, $datamailobject);
            }
        }
        
    }

}
