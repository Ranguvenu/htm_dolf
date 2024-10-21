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
 * @package local_event
 */
namespace local_events;

class notification extends \local_notifications\notification
{
    public $db;
    public $user;
    public function __construct($db = null, $user = null)
    {
        global $DB, $USER;
        $this->db = $db ? $db : $DB;
        $this->user = $user ? $user : $USER;
    }
    public function get_string_identifiers($emailtype)
    {
        switch ($emailtype) {
            case 'events_create':
                $strings = "[event_name],[event_userfullname]";
                break;

            case 'events_update':
                $strings = "[event_name],[event_userfullname]";
                break;
            case 'events_registration':
                $strings = "[event_name],[event_userfullname]";
                break;
            case 'events_speakers':
                $strings = "[event_name], [event_managername], [event_speakername]";
                break;
            case 'events_sponsors':
                $strings = "[event_name], [event_managername], [event_sponsorname]";
                break;
            case 'events_partners':
                $strings = "[event_name],[event_managername],[event_partnername]";
                break;
            case 'events_completion':
                $strings = "[event_userfullname],[event_name]";
                break;
            case 'events_certificate_assignment':
                $strings = "[event_name],[event_userfullname],[event_certificatelink]";
                break;
            case 'events_before_7_days':
                $strings = " [event_related_module_name],[event_userfullname],[event_related_module_date],[event_related_module_time]";
                break;
            case 'events_before_48_hours':
                $strings = " [event_related_module_name],[event_userfullname],[event_related_module_date],[event_related_module_time]";
                break;
            case 'events_before_24_hours':
                $strings = " [event_related_module_name],[event_userfullname],[event_related_module_date],[event_related_module_time]";
                break;
            case 'events_send_conclusion':
                $strings = " [event_related_module_name],[event_userfullname]";
                break;
            case 'events_after_session':
                    $strings = "[event_related_module_name],[event_userfullname]";
                    break; 
            case 'events_onchange':
                    $strings = "[RelatedModuleName],[RelatedModulesLink],[FullName]";
                    break; 
            case 'events_cancel':
                $strings = "[RelatedModuleName],[FullName]";
                break; 
            case 'events_reschedule':
                $strings = "[RelatedModuleName],[ProgramLink],[FullName]";
                    break; 
            case 'event_unregister':
                        $strings = "[event_name],[event_userfullname],[event_date],[event_time]";
                        break;

                        
                
        }


        return $strings;
    }
    public function event_notification($emailtype, $touser, $fromuser, $eventinstance, $waitinglistid = 0)
    {
        if ($notification = $this->get_existing_notification($eventinstance, $emailtype)) {
            $functionname = 'send_' . $emailtype . '_notification';
            $this->$functionname($eventinstance, $touser, $fromuser, $emailtype, $notification, $waitinglistid);
        }
        
    }
    public function send_events_create_notification($eventinstance, $touser, $fromuser, $emailtype, $notification, $waitinglistid = 0)
    {
        global $PAGE;
        $datamailobject = new \stdClass();
        $datamailobject->emailtype = $emailtype;
        $datamailobject->notification_infoid = $notification->id;
        $datamailobject->plugintype = $notification->plugintype;
        $datamailobject->pluginname = $notification->pluginname;
        $datamailobject->moduleid = 1;
        $datamailobject->event_name = $eventinstance->event_name;
        $datamailobject->event_userfullname = $eventinstance->event_userfullname;
        $datamailobject->body = $notification->body;
        $datamailobject->subject = $notification->subject;
        $datamailobject->arabic_body = $notification->arabic_body;
        $datamailobject->arabic_subject = $notification->arabic_subject;
        $datamailobject->eventid = $eventinstance->id;
        $datamailobject->teammemberid = 0;
        $datamailobject->userlang = $touser->lang; 
        //$this->log_email_notification($touser, $fromuser, $datamailobject); 
    }
    public function send_events_update_notification($eventinstance, $touser, $fromuser, $emailtype, $notification, $waitinglistid = 0)
    {
        global $PAGE;
        $datamailobject = new \stdClass();
        $datamailobject->emailtype = $emailtype;
        $datamailobject->notification_infoid = $notification->id;
        $datamailobject->plugintype = $notification->plugintype;
        $datamailobject->pluginname = $notification->pluginname;
        $datamailobject->moduleid = 1;
        $datamailobject->event_name = $eventinstance->event_name;
        $datamailobject->event_userfullname = $eventinstance->event_userfullname;
        $datamailobject->body = $notification->body;
        $datamailobject->subject = $notification->subject;
        $datamailobject->arabic_body = $notification->arabic_body;
        $datamailobject->arabic_subject = $notification->arabic_subject;
        $datamailobject->eventid = $eventinstance->id;
        $datamailobject->teammemberid = 0;
        $datamailobject->userlang = $touser->lang;  
        $this->log_email_notification($touser, $fromuser, $datamailobject);
        
     
    }
    public function send_events_registration_notification($eventinstance, $touser, $fromuser, $emailtype, $notification, $waitinglistid = 0)
    {
        global $PAGE;
        $datamailobject = new \stdClass();
        $datamailobject->emailtype = $emailtype;
        $datamailobject->notification_infoid = $notification->id;
        $datamailobject->plugintype = $notification->plugintype;
        $datamailobject->pluginname = $notification->pluginname;
        $datamailobject->moduleid = 1;
        $datamailobject->event_name = $eventinstance->event_name;
        $datamailobject->event_userfullname = $eventinstance->event_userfullname;
        $datamailobject->body = $notification->body;
        $datamailobject->subject = $notification->subject;
        $datamailobject->arabic_body = $notification->arabic_body;
        $datamailobject->arabic_subject = $notification->arabic_subject;
        $datamailobject->eventid = $eventinstance->eventid;
        $datamailobject->teammemberid = 0;
        $datamailobject->userlang = $touser->lang;          
        $datamailobject->sendsms = 1;       
       // $this->log_email_notification($touser, $fromuser, $datamailobject);
    }
    public function send_event_unregister_notification($eventinstance, $touser, $fromuser, $emailtype, $notification, $waitinglistid = 0)
    {
        global $PAGE;
        $datamailobject = new \stdClass();
        $datamailobject->emailtype = $emailtype;
        $datamailobject->notification_infoid = $notification->id;
        $datamailobject->plugintype = $notification->plugintype;
        $datamailobject->pluginname = $notification->pluginname;
        $datamailobject->moduleid = 1;
        if($touser->lang == 'ar'){
            $datamailobject->event_name = $eventinstance->event_arabicname;
           

        } else{
            $datamailobject->event_name = $eventinstance->event_name;
           
        }
        $datamailobject->event_userfullname = $eventinstance->event_userfullname;
        $datamailobject->event_date = $eventinstance->event_date;
        $datamailobject->event_time = $eventinstance->event_time;
        $datamailobject->body = $notification->body;
        $datamailobject->subject = $notification->subject;
        $datamailobject->arabic_body = $notification->arabic_body;
        $datamailobject->arabic_subject = $notification->arabic_subject;
        $datamailobject->eventid = $eventinstance->eventid;
        $datamailobject->teammemberid = 0;
        $datamailobject->userlang = $touser->lang;          
        $datamailobject->sendsms = 1;       
       // $this->log_email_notification($touser, $fromuser, $datamailobject);
    }
    public function send_events_speakers_notification($eventinstance, $touser, $fromuser, $emailtype, $notification, $waitinglistid = 0)
    {
        global $PAGE;
        $datamailobject = new \stdClass();

        $datamailobject->emailtype = $emailtype;
        $datamailobject->notification_infoid = $notification->id;
        $datamailobject->plugintype = $notification->plugintype;
        $datamailobject->pluginname = $notification->pluginname;
        $datamailobject->moduleid = 1;
        $datamailobject->event_name = $eventinstance->event_name;
        $datamailobject->event_speakername = $eventinstance->event_speakername;
        $datamailobject->event_managername = $eventinstance->event_managername;
        $datamailobject->body = $notification->body;
        $datamailobject->subject = $notification->subject;
        $datamailobject->arabic_body = $notification->arabic_body;
        $datamailobject->arabic_subject = $notification->arabic_subject;
        $datamailobject->eventid = $eventinstance->id;
        $datamailobject->teammemberid = 0;
        $datamailobject->userlang = $touser->lang;
        $this->log_email_notification($touser, $fromuser, $datamailobject);
     
    }
    public function send_events_sponsors_notification($eventinstance, $touser, $fromuser, $emailtype, $notification, $waitinglistid = 0)
    {
        global $PAGE;
        $datamailobject = new \stdClass();
        $datamailobject->emailtype = $emailtype;
        $datamailobject->notification_infoid = $notification->id;
        $datamailobject->plugintype = $notification->plugintype;
        $datamailobject->pluginname = $notification->pluginname;
        $datamailobject->moduleid = 1;
        $datamailobject->event_name = $eventinstance->event_name;
        $datamailobject->event_managername = $eventinstance->event_managername;
        $datamailobject->event_sponsorname = $eventinstance->event_sponsorname;
        $datamailobject->body = $notification->body;
        $datamailobject->subject = $notification->subject;
        $datamailobject->arabic_body = $notification->arabic_body;
        $datamailobject->arabic_subject = $notification->arabic_subject;
        $datamailobject->eventid = $eventinstance->id;
        $datamailobject->teammemberid = 0;
        $this->log_email_notification($touser, $fromuser, $datamailobject);
     
    }
    public function send_events_partners_notification($eventinstance, $touser, $fromuser, $emailtype, $notification, $waitinglistid = 0)
    {
        global $PAGE;
        $datamailobject = new \stdClass();

        $datamailobject->emailtype = $emailtype;
        $datamailobject->notification_infoid = $notification->id;
        $datamailobject->plugintype = $notification->plugintype;
        $datamailobject->pluginname = $notification->pluginname;
        $datamailobject->moduleid = 1;
        $datamailobject->event_name = $eventinstance->event_name;
        $datamailobject->event_managername = $eventinstance->event_managername;
        $datamailobject->event_partnername =  $eventinstance->event_partnername;
        $datamailobject->body = $notification->body;
        $datamailobject->subject = $notification->subject;
        $datamailobject->arabic_body = $notification->arabic_body;
        $datamailobject->arabic_subject = $notification->arabic_subject;
        $datamailobject->eventid = $eventinstance->id;
        $datamailobject->teammemberid = 0;
        $datamailobject->userlang = $touser->lang;
        $this->log_email_notification($touser, $fromuser, $datamailobject);
     
    }
    public function send_events_completion_notification($eventinstance, $touser, $fromuser, $emailtype, $notification, $waitinglistid = 0)
    {
        global $PAGE;
        $datamailobject = new \stdClass();

        $datamailobject->emailtype = $emailtype;
        $datamailobject->notification_infoid = $notification->id;
        $datamailobject->plugintype = $notification->plugintype;
        $datamailobject->pluginname = $notification->pluginname;
        $datamailobject->moduleid = 1;
        $datamailobject->event_userfullname = $eventinstance->event_userfullname;
        $datamailobject->event_name = $eventinstance->event_name;
        $datamailobject->body = $notification->body;
        $datamailobject->subject = $notification->subject;
        $datamailobject->arabic_body = $notification->arabic_body;
        $datamailobject->arabic_subject = $notification->arabic_subject;
        $datamailobject->eventid = $eventinstance->id;
        $datamailobject->teammemberid = 0;
        $datamailobject->userlang = $touser->lang;       
        $datamailobject->sendsms = 1;       
        $this->log_email_notification($touser, $fromuser, $datamailobject);
     
    }
    public function send_events_before_7_days_notification($eventinstance, $touser, $fromuser, $emailtype, $notification, $waitinglistid = 0)
    {
        global $PAGE;
        $datamailobject = new \stdClass();

        $datamailobject->emailtype = $emailtype;
        $datamailobject->notification_infoid = $notification->id;
        $datamailobject->plugintype = $notification->plugintype;
        $datamailobject->pluginname = $notification->pluginname;
        $datamailobject->moduleid = 1;
        $datamailobject->event_related_module_name = $eventinstance->event_related_module_name;
        $datamailobject->event_userfullname = $touser->firstname;
        $datamailobject->event_related_module_date = $eventinstance->event_related_module_date;
        $datamailobject->event_related_module_time = $eventinstance->event_related_module_time;
        $datamailobject->body = $notification->body;
        $datamailobject->subject = $notification->subject;
        $datamailobject->arabic_body = $notification->arabic_body;
        $datamailobject->arabic_subject = $notification->arabic_subject;
        $datamailobject->moduleid = 1;
        $datamailobject->touserid = $touser->id;
        $datamailobject->fromuserid = $fromuser->id;
        $datamailobject->teammemberid = 0;
        $datamailobject->userlang = $touser->lang;       
        $datamailobject->sendsms = 1;   
        $this->log_email_notification($touser, $fromuser, $datamailobject);
     
    }
    public function send_events_before_48_hours_notification($eventinstance, $touser, $fromuser, $emailtype, $notification, $waitinglistid = 0)
    {
        global $PAGE;


        $datamailobject = new \stdClass();

        $datamailobject->emailtype = $emailtype;
        $datamailobject->notification_infoid = $notification->id;
        $datamailobject->plugintype = $notification->plugintype;
        $datamailobject->pluginname = $notification->pluginname;
        $datamailobject->moduleid = 1;
        $datamailobject->event_related_module_name = $eventinstance->event_related_module_name;
        $datamailobject->event_userfullname = $touser->firstname;
        $datamailobject->event_related_module_date = $eventinstance->event_related_module_date;
        $datamailobject->event_related_module_time = $eventinstance->event_related_module_time;
        $datamailobject->body = $notification->body;
        $datamailobject->subject = $notification->subject;
        $datamailobject->arabic_body = $notification->arabic_body;
        $datamailobject->arabic_subject = $notification->arabic_subject;
        $datamailobject->moduleid = 1;
        $datamailobject->touserid = $touser->id;
        $datamailobject->fromuserid = $fromuser->id;
        $datamailobject->teammemberid = 0;
        $datamailobject->userlang = $touser->lang;        
        $datamailobject->sendsms =1;
       // $this->log_email_notification($touser, $fromuser, $datamailobject);
    }

   public function send_events_before_24_hours_notification($eventinstance, $touser, $fromuser, $emailtype, $notification, $waitinglistid = 0)
    {
        global $PAGE;
        $datamailobject = new \stdClass();

        $datamailobject->emailtype = $emailtype;
        $datamailobject->notification_infoid = $notification->id;
        $datamailobject->plugintype = $notification->plugintype;
        $datamailobject->pluginname = $notification->pluginname;
        $datamailobject->moduleid = 1;
        $datamailobject->event_related_module_name = $eventinstance->event_related_module_name;
        $datamailobject->event_userfullname = $touser->firstname;
        $datamailobject->event_related_module_date = $eventinstance->event_related_module_date;
        $datamailobject->event_related_module_time = $eventinstance->event_related_module_time;
        $datamailobject->body = $notification->body;
        $datamailobject->subject = $notification->subject;
        $datamailobject->arabic_body = $notification->arabic_body;
        $datamailobject->arabic_subject = $notification->arabic_subject;
        $datamailobject->moduleid = 1;
        $datamailobject->touserid = $touser->id;
        $datamailobject->fromuserid = $fromuser->id;
        $datamailobject->teammemberid = 0;
        $datamailobject->userlang = $touser->lang;      
        $datamailobject->sendsms = 1;
        

        $this->log_email_notification($touser, $fromuser, $datamailobject);
    }
    public function send_events_reschedule_notification($eventinstance, $touser, $fromuser, $emailtype, $notification,$waitinglistid=0){
        global $PAGE;
        $datamailobject = new \stdClass();
        $datamailobject->emailtype = $emailtype;        
        $datamailobject->notification_infoid = $notification->id;
        $datamailobject->plugintype = $notification->plugintype;
        $datamailobject->pluginname = $notification->pluginname;
        $datamailobject->moduleid=1;
        $datamailobject->RelatedModuleName = $eventinstance['RelatedModuleName'];
        $datamailobject->ProgramLink = $eventinstance['ProgramLink'];
        $datamailobject->FullName=$eventinstance['FullName'];        
        $datamailobject->body = $notification->body;
        $datamailobject->subject = $notification->subject;
        $datamailobject->arabic_body = $notification->arabic_body;
        $datamailobject->arabic_subject = $notification->arabic_subject;
        $datamailobject->eventid = $eventinstance->id;     
        $datamailobject->teammemberid = 0;
        $datamailobject->userlang = $touser->lang;       
        $datamailobject->sendsms = 1;
        
        //$this->log_email_notification($touser, $fromuser, $datamailobject);
        //  print_r($datamailobject);exit;
    }
    public function send_events_cancel_notification($eventinstance, $touser, $fromuser, $emailtype, $notification,$waitinglistid=0){
        global $PAGE,$DB;
        $datamailobject = new \stdClass();

        $datamailobject->emailtype = $emailtype;        
        $datamailobject->notification_infoid = $notification->id;
        $datamailobject->plugintype = $notification->plugintype;
        $datamailobject->pluginname = $notification->pluginname;
        $datamailobject->moduleid=1;
        $datamailobject->RelatedModuleName = $eventinstance['RelatedModuleName'];
        $datamailobject->body = $notification->body;
        $datamailobject->subject = $notification->subject;
        $datamailobject->arabic_body = $notification->arabic_body;
        $datamailobject->arabic_subject = $notification->arabic_subject;
        $datamailobject->fromuserid = $fromuser->id;
        $datamailobject->teammemberid = 0;
        $datamailobject->userlang = $touser->lang;
        $datamailobject->sendsms =1;

        if ($touser) {
            foreach ($touser as $tousers) {
                $datamailobject->FullName = $tousers->name;
             //  print_r($datamailobject);exit;
             if($tousers->userid==0){
                $touser1=get_admin();
                $touser1->firstname=$tousers->name;
                $touser1->lastname=$tousers->name;
                $touser1->email=$tousers->email;
            }
            else{
                $touser1=$DB->get_record('user',array('id'=>$tousers->userid));
            } 
                $this->log_email_notification($touser1, $fromuser, $datamailobject);
            }
        }
     
    }
    public function send_events_send_conclusion_notification($eventinstance, $touser, $fromuser, $emailtype, $notification,$waitinglistid=0){
        global $PAGE;
        $datamailobject = new \stdClass();

        $datamailobject->emailtype = $emailtype;
        $datamailobject->notification_infoid = $notification->id;
        $datamailobject->plugintype = $notification->plugintype;
        $datamailobject->pluginname = $notification->pluginname;
        $datamailobject->moduleid = 1;
        $datamailobject->event_related_module_name = $eventinstance->event_related_module_name;
        $datamailobject->event_userfullname = $touser->firstname;
        $datamailobject->body = $notification->body;
        $datamailobject->subject = $notification->subject;
        $datamailobject->arabic_body = $notification->arabic_body;
        $datamailobject->arabic_subject = $notification->arabic_subject;
        $datamailobject->moduleid = 1;
        $datamailobject->touserid = $touser->id;
        $datamailobject->fromuserid = $fromuser->id;
        $datamailobject->teammemberid = 0;
        $datamailobject->userlang = $touser->lang;       
       
        $datamailobject->sendsms =1;
       // $this->log_email_notification($touser, $fromuser, $datamailobject);
     
    }
    public function send_events_after_session_notification($eventinstance, $touser, $fromuser, $emailtype, $notification, $waitinglistid = 0)
    {
        global $PAGE;


        $datamailobject = new \stdClass();

        $datamailobject->emailtype = $emailtype;
        $datamailobject->notification_infoid = $notification->id;
        $datamailobject->plugintype = $notification->plugintype;
        $datamailobject->pluginname = $notification->pluginname;
        $datamailobject->moduleid = 1;
        $datamailobject->event_related_module_name = $eventinstance->event_related_module_name;
        $datamailobject->event_userfullname = $touser->firstname;
        $datamailobject->body = $notification->body;
        $datamailobject->subject = $notification->subject;
        $datamailobject->arabic_body = $notification->arabic_body;
        $datamailobject->arabic_subject = $notification->arabic_subject;
        $datamailobject->moduleid = 1;
        $datamailobject->touserid = $touser->id;
        $datamailobject->fromuserid = $fromuser->id;
        $datamailobject->teammemberid = 0;
        $datamailobject->userlang = $touser->lang;        
        $datamailobject->sendsms =1;
        

        $this->log_email_notification($touser, $fromuser, $datamailobject);
    }
    public function send_events_onchange_notification($eventinstance, $touser, $fromuser, $emailtype, $notification,$waitinglistid=0){
        global $PAGE;
        $datamailobject = new \stdClass();

        $datamailobject->emailtype = $emailtype;        
        $datamailobject->notification_infoid = $notification->id;
        $datamailobject->plugintype = $notification->plugintype;
        $datamailobject->pluginname = $notification->pluginname;
        $datamailobject->moduleid=1;
        $datamailobject->RelatedModuleName = $eventinstance['RelatedModuleName'];
        $datamailobject->RelatedModulesLink = $eventinstance['RelatedModulesLink'];
        $datamailobject->FullName=$eventinstance['FullName'];  
        $datamailobject->body = $notification->body;
        $datamailobject->subject = $notification->subject;
        $datamailobject->arabic_body = $notification->arabic_body;
        $datamailobject->arabic_subject = $notification->arabic_subject;         
        $datamailobject->sendsms =1;        
        $datamailobject->teammemberid = 0;             //  print_r($datamailobject);exit;
        $datamailobject->userlang = $touser->lang;
        $this->log_email_notification($touser, $fromuser, $datamailobject);
    
     
    }
public function events_before_7_days_notification($lastruntime,$nextruntime,$timenow)
    {
        global $DB, $USER;
        $user = new \stdClass();
        $from = null;
        // $from = '2022-07-08';
        $fromdate = ($from !== null) ? $from : date('Y-m-d');
        // $from = date_create(date('Y-m-d'));
        $sqlevents = "   SELECT le.id,le.title,le.titlearabic,date(from_unixtime(le.startdate)) AS startdate,
                        from_unixtime(le.slot) AS slot,ee.userid,ee.id,ee.email,ee.name  
                        FROM {local_events} AS le 
                        INNER JOIN {local_event_attendees} AS ee ON ee.eventid = le.id AND 
                        DATE (from_unixtime(startdate-(60*60*24*7))) = '$fromdate'
                            AND ((le.startdate+le.slot)-60*60*24*7) >= $lastruntime
                            AND ((le.startdate+le.slot)-60*60*24*7) <= $timenow";  
        $eventslist = $DB->get_records_sql($sqlevents);
        if (!empty($eventslist)) {
            foreach ($eventslist as $key => $event) {
                $sql = "SELECT lnt.* 
                        FROM {local_notification_type} AS lnt
                        WHERE shortname IN ('events_before_7_days') ";
                $notificationtype = $DB->get_record_sql($sql);
                $notificationtype->event_related_module_name = $event->title;
                $notificationtype->event_related_module_date = date('d-M-Y', strtotime($event->startdate));
                $notificationtype->event_related_module_time = date('H:i:s', strtotime($event->slot));
                $notificationtype->sendsms =1;
                if($event->userid==0){
                    $user=get_admin();
                    $user->firstname=$event->name;
                    $user->lastname=$event->name;
                    $user->email=$event->email;                     
                }
                else{
                    $user=$DB->get_record('user',array('id'=>$event->userid)); 
                    if ($user->lang == 'ar') {
                        $notificationtype->event_related_module_name = $event->titlearabic;
                        $notificationtype->event_related_module_date = date('d-M-Y', strtotime($event->startdate));
                        $notificationtype->event_related_module_time = date('H:i:s', strtotime($event->slot));
                        $user->firstname = $DB->get_field('local_users','firstnamearabic',array('userid'=>$event->userid));
                        $user->lastname = $DB->get_field('local_users','lastnamearabic',array('userid'=>$event->userid));
                    } else {
                        $notificationtype->event_related_module_name = $event->title;
                        $notificationtype->event_related_module_date = date('d-M-Y', strtotime($event->startdate));
                        $notificationtype->event_related_module_time = date('H:i:s', strtotime($event->slot)); 
                        $user->firstname = $event->name;
                        $user->lastname = $event->name;                                           

                    } 
                }              
                $this->event_notification('events_before_7_days', $touser = $user, $fromuser = get_admin(), $notificationtype, $waitinglistid = 0);
            }
        }
    }
    public function events_before_48_hours_notification($lastruntime,$nextruntime,$timenow)
    {
        global $DB, $USER;
        $user = new \stdClass();
        $from = null;
        // $from = '2022-07-13';
        $fromdate = ($from !== null) ? $from : date('Y-m-d');
        // $from = date_create(date('Y-m-d'));
        $sqlevents = "  SELECT le.id,le.title,le.titlearabic,date(from_unixtime(le.startdate)) AS startdate,
        from_unixtime(le.slot) AS slot,ee.userid,ee.id,ee.email,ee.name  
        FROM {local_events} AS le 
        INNER JOIN {local_event_attendees} AS ee ON ee.eventid = le.id 
        AND DATE(from_unixtime(startdate-(60*60*48))) = '$fromdate'
            AND ((le.startdate+le.slot)-60*60*48) >= $lastruntime
            AND ((le.startdate+le.slot)-60*60*48) <= $timenow";
        $eventslist = $DB->get_records_sql($sqlevents);
        if (!empty($eventslist)) {
            foreach ($eventslist as $key => $event) {
                $sql = "SELECT lnt.* 
                        FROM {local_notification_type} AS lnt
                        WHERE shortname IN ('events_before_48_hours') ";
                $notificationtype = $DB->get_record_sql($sql);
                $notificationtype->event_related_module_name = $event->title;
                $notificationtype->event_related_module_date = date('d-M-Y', strtotime($event->startdate));
                $notificationtype->event_related_module_time = date('H:i:s', strtotime($event->slot));
                $notificationtype->sendsms =1;
                if($event->userid==0){
                    $user=get_admin();
                    $user->firstname=$event->name;
                    $user->lastname=$event->name;
                    $user->email=$event->email;                    
                }
                else{
                    $user=$DB->get_record('user',array('id'=>$event->userid)); 
                    if ($user->lang == 'ar') {
                        $notificationtype->event_related_module_name = $event->titlearabic;
                        $notificationtype->event_related_module_date = date('d-M-Y', strtotime($event->startdate));
                        $notificationtype->event_related_module_time = date('H:i:s', strtotime($event->slot));
                        $user->firstname = $DB->get_field('local_users','firstnamearabic',array('userid'=>$event->userid));
                        $user->lastname = $DB->get_field('local_users','lastnamearabic',array('userid'=>$event->userid));
                    } else {
                        $notificationtype->event_related_module_name = $event->title;
                        $notificationtype->event_related_module_date = date('d-M-Y', strtotime($event->startdate));
                        $notificationtype->event_related_module_time = date('H:i:s', strtotime($event->slot)); 
                        $user->firstname = $event->name;
                        $user->lastname = $event->name;                                           
                    }
                } 
                $this->event_notification('events_before_48_hours', $touser = $user, $fromuser = get_admin(), $notificationtype, $waitinglistid = 0);
            }
        }
    }
    public function events_before_24_hours_notification($lastruntime,$nextruntime,$timenow)
    {
        global $DB, $USER;
        $user = new \stdClass();
        $from = null;
        // $from = '2022-07-14';
        $fromdate = ($from !== null) ? $from : date('Y-m-d');
        // $from = date_create(date('Y-m-d'));
        $sqlevents = " SELECT le.id,le.title,le.titlearabic,date(from_unixtime(le.startdate)) AS startdate,
        from_unixtime(le.slot) AS slot,ee.userid,ee.id,ee.email,ee.name  
        FROM {local_events} AS le 
        INNER JOIN {local_event_attendees} AS ee ON ee.eventid = le.id 
        AND DATE(from_unixtime(startdate-(60*60*24))) = '$fromdate'
            AND ((le.startdate+le.slot)-60*60*24) >= $lastruntime
            AND ((le.startdate+le.slot)-60*60*24) <= $timenow";
        $eventslist = $DB->get_records_sql($sqlevents);
        if (!empty($eventslist)) {
            foreach ($eventslist as $key => $event) {
                $sql = "SELECT lnt.* 
                        FROM {local_notification_type} AS lnt
                        WHERE shortname IN ('events_before_24_hours') ";
                $notificationtype = $DB->get_record_sql($sql);
                $notificationtype->event_related_module_name = $event->title;
                $notificationtype->event_related_module_date = date('d-M-Y', strtotime($event->startdate));
                $notificationtype->event_related_module_time = date('H:i:s', strtotime($event->slot));
                $notificationtype->sendsms =1;
                if($event->userid==0){
                    $user=get_admin();
                    $user->firstname=$event->name;
                    $user->lastname=$event->name;
                    $user->email=$event->email;                    
                }
                else{
                    $user=$DB->get_record('user',array('id'=>$event->userid));
                    if ($user->lang == 'ar') {
                        $notificationtype->event_related_module_name = $event->titlearabic;
                        $notificationtype->event_related_module_date = date('d-M-Y', strtotime($event->startdate));
                        $notificationtype->event_related_module_time = date('H:i:s', strtotime($event->slot));
                        $user->firstname = $DB->get_field('local_users','firstnamearabic',array('userid'=>$event->userid));
                        $user->lastname = $DB->get_field('local_users','lastnamearabic',array('userid'=>$event->userid));
                    } else {
                        $notificationtype->event_related_module_name = $event->title;
                        $notificationtype->event_related_module_date = date('d-M-Y', strtotime($event->startdate));
                        $notificationtype->event_related_module_time = date('H:i:s', strtotime($event->slot)); 
                        $user->firstname = $event->name;
                        $user->lastname = $event->name;                                           
                    } 
                }
                $this->event_notification('events_before_24_hours', $touser = $user, $fromuser = get_admin(), $notificationtype, $waitinglistid = 0);
            }
        }
    }
    public function events_after_session_notification($lastruntime,$nextruntime,$timenow)
    {
        global $DB, $USER;
        $user = new \stdClass();
        $from = null;
        // $from = date('Y-m-d');
        // $from = '2022-07-15';
        $fromdate = ($from !== null) ? $from : date('Y-m-d');
        $sqlevents = " SELECT le.id,le.title,le.titlearabic,date(from_unixtime(le.startdate)) AS startdate,
        from_unixtime(le.slot) AS slot,ee.userid,ee.id,ee.email,ee.name  
        FROM {local_events} AS le 
        INNER JOIN {local_event_attendees} AS ee ON ee.eventid = le.id 
        AND DATE(from_unixtime(enddate)) = '$fromdate'
            AND (le.enddate+le.slot+le.eventduration) >= $lastruntime
            AND (le.enddate+le.slot+le.eventduration) <= $timenow";
        $eventslist = $DB->get_records_sql($sqlevents);
        if (!empty($eventslist)) {
            // $eventslist->
            foreach ($eventslist as $key => $event) {
                $sql = "SELECT lnt.* 
                        FROM {local_notification_type} AS lnt
                        WHERE shortname IN ('events_after_session') ";
                $notificationtype = $DB->get_record_sql($sql);
                $notificationtype->event_related_module_name = $event->title;
                $notificationtype->sendsms =1;
                if($event->userid==0){
                    $notificationtype->event_related_module_name = $event->title;                   
                    $user=get_admin();
                    $user->firstname=$event->name;
                    $user->lastname=$event->name;
                    $user->email=$event->email;                    
                }
                else{
                    $user=$DB->get_record('user',array('id'=>$event->userid)); 
                    if ($user->lang == 'ar') {
                        $notificationtype->event_related_module_name = $event->titlearabic;                  
                        $user->firstname = $DB->get_field('local_users','firstnamearabic',array('userid'=>$event->userid));
                        $user->lastname = $DB->get_field('local_users','lastnamearabic',array('userid'=>$event->userid));
                    } else {
                        $notificationtype->event_related_module_name = $event->title;                   
                        $user->firstname = $event->name;
                        $user->lastname = $event->name;                                           
                    }
                }      
                // $user = $DB->get_record('user', array('id' => $event->userid, 'deleted' => 0));
                $this->event_notification('events_after_session', $touser = $user, $fromuser = get_admin(), $notificationtype, $waitinglistid = 0);
            }
        }
    }
    public function events_send_conclusion_notification($lastruntime,$nextruntime,$timenow)
    {
        global $DB, $USER;
        $user = new \stdClass();
        $from = null;
        // $from = date('Y-m-d');
        // $from = '2022-07-15';
        $fromdate = ($from !== null) ? $from : date('Y-m-d');
        $sqlevents = "   SELECT le.id,le.title,le.titlearabic,date(from_unixtime(le.startdate)) AS startdate,
        from_unixtime(le.slot) AS slot,ee.userid,ee.id,ee.email,ee.name  
        FROM {local_events} AS le 
        INNER JOIN {local_event_attendees} AS ee ON ee.eventid = le.id AND
         DATE(from_unixtime(enddate)) = '$fromdate'
            AND (le.enddate+le.slot+le.eventduration) >= $lastruntime
            AND (le.enddate+le.slot+le.eventduration) <= $timenow";
        $eventslist = $DB->get_records_sql($sqlevents);
        if (!empty($eventslist)) {
            // $eventslist->
            foreach ($eventslist as $key => $event) {
                $sql = "SELECT lnt.* 
                        FROM {local_notification_type} AS lnt
                        WHERE shortname IN ('events_send_conclusion') ";
                $notificationtype = $DB->get_record_sql($sql);
                $notificationtype->event_related_module_name = $event->title;
                $notificationtype->sendsms =1;
                if($event->userid==0){
                    $notificationtype->event_related_module_name = $event->title;                    
                    $user=get_admin();
                    $user->firstname=$event->name;
                    $user->lastname=$event->name;
                    $user->email=$event->email;                    
                }
                else{
                    $user=$DB->get_record('user',array('id'=>$event->userid)); 
                    if ($user->lang == 'ar') {
                        $notificationtype->event_related_module_name = $event->titlearabic;                  
                        $user->firstname = $DB->get_field('local_users','firstnamearabic',array('userid'=>$event->userid));
                        $user->lastname = $DB->get_field('local_users','lastnamearabic',array('userid'=>$event->userid));
                    } else {
                        $notificationtype->event_related_module_name = $event->title;                   
                        $user->firstname = $event->name;
                        $user->lastname = $event->name;                                           
                    }
                }
                // $user = $DB->get_record('user', array('id' => $event->userid, 'deleted' => 0));
                $this->event_notification('events_send_conclusion', $touser = $user, $fromuser = get_admin(), $notificationtype, $waitinglistid = 0);
            }
        }
    }

}
