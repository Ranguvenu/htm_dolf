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
 * @package local_organization
 */

namespace local_organization;


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
            case 'organization_registration':
                $strings = "[organization_name],[user_fullname] ";
                break;

            case 'organization_assigning_official':
                $strings = "[organization_name],[user_fullname], [organization_official_name]";
                break;

            case 'organization_assigning_trainee':
                $strings = "[organization_name],[user_fullname], [organization_trainee_name]";
                break;
            case 'organization_enrollment':
                $strings = "[organization_name],[user_fullname]";
                break;
            case 'organization_wallet_update':
                $strings = "[user_fullname]";
                break;
        }

        return $strings;
    }
    public function organization_notification($emailtype, $touser, $fromuser, $organizationinstance, $waitinglistid = 0, $data = null)
    {
        if ($notification = $this->get_existing_notification($organizationinstance, $emailtype)) {
            $functionname = 'send_' . $emailtype . '_notification';

            $this->$functionname($organizationinstance, $touser, $fromuser, $emailtype, $notification, $waitinglistid, $data);
        }
    }
    public function send_organization_registration_notification($organizationinstance, $touser, $fromuser, $emailtype, $notification, $waitinglistid = 0, $data = null)
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
        $datamailobject->organizationid = $organizationinstance->id;
        $datamailobject->fromuserid = $fromuser->id;
        $datamailobject->teammemberid = 0;
       
        $contactemails = array($data->hremail, $data->alemail);
      
        if ($contactemails) {
            foreach ($contactemails as $key => $contactemail) {
                $touser = get_admin();                
                $touser->email = $contactemail;    
                $datamailobject->userlang = $touser->lang;           
                $datamailobject->user_fullname = $data->hrfullname;                
                if($key==1){
                    $datamailobject->user_fullname = $data->alfullname;                    
                } 
                if($datamailobject->userlang == 'ar') {
                    $datamailobject->organization_name = $data->fullnameinarabic;
                } else{
                    $datamailobject->organization_name = $data->fullname;   
                }
                              
                $this->log_email_notification($touser, $fromuser, $datamailobject);
            }
        }
    
       $roleusers = $this->getsystemlevel_role_users('to', 0);
        
        if ($roleusers) {
            foreach ($roleusers as $roleuser) {
                $datamailobject->userlang = $roleuser->lang;
                $localuserrecord = $DB->get_record('local_users',['userid'=> $roleuser->id]);
                if ($datamailobject->userlang == 'ar'){

                    $datamailobject->organization_name = $data->fullnameinarabic;
                    $datamailobject->user_fullname = $localuserrecord->firstnamearabic.' '.$localuserrecord->middlenamearabic.' '.$localuserrecord->thirdnamearabic.' '.$localuserrecord->lastnamearabic ;

                }else {
    
                    $datamailobject->organization_name = $data->fullname;
                    $datamailobject->user_fullname  = $localuserrecord->firstname.' '.$localuserrecord->middlenameen.' '.$localuserrecord->thirdnameen.' '.$localuserrecord->lastname;
                }

                //  $localuserrecord = $DB->get_record('local_users',['userid'=> $roleuser->id]);
                //  $datamailobject->user_fullname = ($localuserrecord)? (($datamailobject->userlang == 'ar') ? $localuserrecord->firstnamearabic.' '.$localuserrecord->middlenamearabic.' '.$localuserrecord->thirdnamearabic.' '.$localuserrecord->lastnamearabic  : $localuserrecord->firstname.' '.$localuserrecord->middlenameen.' '.$localuserrecord->thirdnameen.' '.$localuserrecord->lastname) :fullname($roleuser);
                            
                $this->log_email_notification($roleuser, $fromuser, $datamailobject);
            }
        }
     
    }
    public function send_organization_assigning_official_notification($organizationinstance, $touser, $fromuser, $emailtype, $notification, $waitinglistid = 0, $data)
    {
        global $PAGE,$DB;
        $datamailobject = new \stdClass();

        $datamailobject->emailtype = $emailtype;
        $datamailobject->notification_infoid = $notification->id;
        $datamailobject->plugintype = $notification->plugintype;
        $datamailobject->pluginname = $notification->pluginname;
        $datamailobject->moduleid = 1;       
        $datamailobject->organization_official_name = $data->organization_official_name;
        if($touser->lang == 'ar'){
            $datamailobject->organization_name = $data->fullnameinarabic;
            
        } else {
            $datamailobject->organization_name = $data->fullname;       
        }       
        $localuserrecord = $DB->get_record('local_users',['userid'=> $touser->id]);
        $datamailobject->user_fullname = ($localuserrecord)? (($touser->lang == 'ar') ? $localuserrecord->firstnamearabic.' '.$localuserrecord->middlenamearabic.' '.$localuserrecord->thirdnamearabic.' '.$localuserrecord->lastnamearabic  : $localuserrecord->firstname.' '.$localuserrecord->middlenameen.' '.$localuserrecord->thirdnameen.' '.$localuserrecord->lastname) :fullname($touser);
        $datamailobject->arabic_body = $notification->arabic_body;
        $datamailobject->arabic_subject = $notification->arabic_subject;
        $datamailobject->body = $notification->body;
        $datamailobject->subject = $notification->subject;
        $datamailobject->organizationid = $organizationinstance->id;
        $datamailobject->fromuserid = $fromuser->id;
        $datamailobject->teammemberid = 0; 
        $datamailobject->userlang = $touser->lang;       
        $this->log_email_notification($touser, $fromuser, $datamailobject);
        
    }
    public function send_organization_assigning_trainee_notification($organizationinstance, $touser, $fromuser, $emailtype, $notification, $waitinglistid = 0, $data)
    {
        global $PAGE,$DB;
        $datamailobject = new \stdClass();

        $datamailobject->emailtype = $emailtype;
        $datamailobject->notification_infoid = $notification->id;
        $datamailobject->plugintype = $notification->plugintype;
        $datamailobject->pluginname = $notification->pluginname;
        $datamailobject->moduleid = 1;      
    
        $datamailobject->organization_name = $data->organization_name;  
            
  
            
     


           $datamailobject->organization_trainee_name = $data->organization_trainee_name;
           $datamailobject->user_fullname = $data->user_fullname ;

        $datamailobject->arabic_body = $notification->arabic_body;
        $datamailobject->arabic_subject = $notification->arabic_subject;
        $datamailobject->body = $notification->body;
        $datamailobject->subject = $notification->subject;
        $datamailobject->organizationid = $organizationinstance->id;
        $datamailobject->fromuserid = $fromuser->id;
        $datamailobject->teammemberid = 0;    
        $datamailobject->userlang = $touser->lang;    
        $this->log_email_notification($touser, $fromuser, $datamailobject);  


    }
    public function send_organization_enrollment_notification($organizationinstance, $touser, $fromuser, $emailtype, $notification, $waitinglistid = 0)
    {
        global $PAGE;
        $datamailobject = new \stdClass();

        $datamailobject->emailtype = $emailtype;
        $datamailobject->notification_infoid = $notification->id;
        $datamailobject->plugintype = $notification->plugintype;
        $datamailobject->pluginname = $notification->pluginname;
        $datamailobject->moduleid = 1;


        $datamailobject->organization_name = 'organization_name';
        $datamailobject->organization_code = 'organization_code';


        $datamailobject->body = $notification->body;
        $datamailobject->subject = $notification->subject;
        $datamailobject->organizationid = $organizationinstance->id;
        $datamailobject->touserid = $touser->id;
        $datamailobject->fromuserid = $fromuser->id;
        $datamailobject->teammemberid = 0;

        $this->log_email_notification($touser, $fromuser, $datamailobject);
    }
    public function send_organization_wallet_update_notification($organizationinstance, $touser, $fromuser, $emailtype, $notification, $waitinglistid = 0)
    {
        global $PAGE;
        $datamailobject = new \stdClass();

        $datamailobject->emailtype = $emailtype;
        $datamailobject->notification_infoid = $notification->id;
        $datamailobject->plugintype = $notification->plugintype;
        $datamailobject->pluginname = $notification->pluginname;
        $datamailobject->moduleid = 1;


        $datamailobject->user_fullname = $touser->username;

        $datamailobject->body = $notification->arabic_body;
        $datamailobject->arabic_subject = $notification->arabic_subject;
        $datamailobject->arabic_body = $notification->body;
        $datamailobject->subject = $notification->subject;
        $datamailobject->organizationid = $organizationinstance->id;
        $datamailobject->touserid = $touser->id;
        $datamailobject->fromuserid = $fromuser->id;
        $datamailobject->teammemberid = 0;

        $this->log_email_notification($touser, $fromuser, $datamailobject);
    }
}
