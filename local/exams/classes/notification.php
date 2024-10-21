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
 * @package local_exams
 */

namespace local_exams;


class notification extends \local_notifications\notification
{

    public function get_string_identifiers($emailtype)
    {
        switch ($emailtype) {
            case 'exams_create':
                $strings = "[exam_name],[exam_userfullname]";
                break;

            case 'exams_update':
                $strings = "[exam_name],[exam_userfullname]";
                break;

            case 'exams_enrolment':
                $strings = "[exam_userfullname],[exam_name],
                [exam_date], [exam_time]";
                break;
            case 'exams_completion':
                $strings = "[exam_name],[exam_userfullname]";
                break;

            case 'exams_certificate_assignment':
                $strings = "[exam_name],[exam_userfullname],[exam_certificatelink]";
                break;
            case 'exams_before_7_days':
                $strings = " [exam_related_module_name],[exam_userfullname],[exam_related_module_date],[exam_related_module_time]";
                break;
            case 'exams_before_48_hours':
                $strings = " [exam_related_module_name],[exam_userfullname],[exam_related_module_date],[exam_related_module_time]";
                break;
            case 'exams_before_24_hours':
                $strings = " [exam_related_module_name],[exam_userfullname],[exam_related_module_date],[exam_related_module_time]";
                break;
            case 'exams_send_conclusion':
                $strings = " [exam_related_module_name],[exam_userfullname]";
                break;
            case 'exams_after_session':
                $strings = " [exam_related_module_name],[exam_userfullname]";
                break;
            case 'exam_unenroll':
                $strings = "[exam_name],[exam_userfullname],[exam_date],[exam_time]";
                break;
            case 'exam_reschedule' :
                $strings = "[exam_name],[exam_userfullname],[pastexam_date],[pastexam_time],[presentexam_date],[presentexam_time]";
                break;
            case 'other_exam_enrollment' :
                    $strings = "[exam_ownedby],[exam_userfullname],[exam_name],
                    [exam_date], [exam_time]";   
                    break;
            case 'exam_service_provider' :
                        $strings = "[exam_ownedby],[exam_userfullname],[exam_name],
                        [exam_useridno],[exam_useremail],[exam_userorg],[exam_center],[exam_date], [exam_time],[exam_endtime],[exam_profilelang],[exam_userdob]";  
                        break; 
            case 'exam_result_objection':
                   $strings = "[exam_name],[exam_examoff],[exam_userfullname]";
                   break;
             case 'bulkenrol_exam':
                    $strings = "[org_off],[exam_name],[trainee_details],[exam_startdate],[exam_starttime],[exam_endtime]";
                    break;
        }

        return $strings;
    }
    public function exams_notification($emailtype, $touser, $fromuser, $examinstance, $waitinglistid = 0)
    {
        if ($notification = $this->get_existing_notification($examinstance, $emailtype)) {
            $functionname = 'send_' . $emailtype . '_notification';
            $this->$functionname($examinstance, $touser, $fromuser, $emailtype, $notification, $waitinglistid);
        }
    }
    public function send_exams_create_notification($examinstance, $touser, $fromuser, $emailtype, $notification, $waitinglistid = 0)
    {
        global $PAGE,$DB;
        $datamailobject = new \stdClass();
        $datamailobject->emailtype = $emailtype;
        $datamailobject->notification_infoid = $notification->id;
        $datamailobject->plugintype = $notification->plugintype;
        $datamailobject->pluginname = $notification->pluginname;
        $datamailobject->moduleid = 1;
        $datamailobject->exam_name = $examinstance->exam_name;
        $datamailobject->body = $notification->body;
        $datamailobject->subject = $notification->subject;
        $datamailobject->arabic_body = $notification->arabic_body;
        $datamailobject->arabic_subject = $notification->arabic_subject;
        $datamailobject->moduleid = 1;
        $datamailobject->teammemberid = 0;
        $roleusers = $this->getsystemlevel_role_users('examofficial', 0);
        if ($roleusers) {
            foreach ($roleusers as $roleuser) {
                $localuserrecord = $DB->get_record('local_users',['userid'=> $roleuser->id]);
                $fullname = ($localuserrecord)? (($roleuser->lang == 'ar') ? $localuserrecord->firstnamearabic.' '.$localuserrecord->middlenamearabic.' '.$localuserrecord->thirdnamearabic.' '.$localuserrecord->lastnamearabic  : $localuserrecord->firstname.' '.$localuserrecord->middlenameen.' '.$localuserrecord->thirdnameen.' '.$localuserrecord->lastname) :fullname($DB->get_record('user',array('id'=>(int)$roleuser->id)));
                $datamailobject->FullName = $fullname;
                $this->log_email_notification($roleuser, $fromuser, $datamailobject);
            }
        }      
    }
    public function send_exams_update_notification($examinstance, $touser, $fromuser, $emailtype, $notification, $waitinglistid = 0)
    {
        global $PAGE,$DB;
        $datamailobject = new \stdClass();
        $datamailobject->emailtype = $emailtype;
        $datamailobject->notification_infoid = $notification->id;
        $datamailobject->plugintype = $notification->plugintype;
        $datamailobject->pluginname = $notification->pluginname;
        $datamailobject->moduleid = 1;
        $datamailobject->exam_name = $examinstance->exam_name;
        $datamailobject->exam_userfullname = $examinstance->exam_userfullname;
        $datamailobject->body = $notification->body;
        $datamailobject->subject = $notification->subject;
        $datamailobject->arabic_body = $notification->arabic_body;
        $datamailobject->arabic_subject = $notification->arabic_subject;
        $datamailobject->moduleid = 1;
        $datamailobject->teammemberid = 0;
        $roleusers = $this->getsystemlevel_role_users('examofficial', 0);
        if ($roleusers) {
            foreach ($roleusers as $roleuser) {
                $localuserrecord = $DB->get_record('local_users',['userid'=> $roleuser->id]);
                $fullname = ($localuserrecord)? (($roleuser->lang == 'ar') ? $localuserrecord->firstnamearabic.' '.$localuserrecord->middlenamearabic.' '.$localuserrecord->thirdnamearabic.' '.$localuserrecord->lastnamearabic  : $localuserrecord->firstname.' '.$localuserrecord->middlenameen.' '.$localuserrecord->thirdnameen.' '.$localuserrecord->lastname) :fullname($DB->get_record('user',array('id'=>(int)$roleuser->id)));
                $datamailobject->FullName = $fullname;
                $this->log_email_notification($roleuser, $fromuser, $datamailobject);
            }
        }
    }
    public function send_exams_enrolment_notification($examinstance, $touser, $fromuser, $emailtype, $notification, $waitinglistid = 0)
    {
        global $PAGE;
        $datamailobject = new \stdClass();

        $datamailobject->emailtype = $emailtype;
        $datamailobject->notification_infoid = $notification->id;
        $datamailobject->plugintype = $notification->plugintype;
        $datamailobject->pluginname = $notification->pluginname;      
        $datamailobject->userlang = $touser->lang;  
        if($touser->lang == 'ar'){
            $datamailobject->exam_userfullname = $examinstance->exam_arabicuserfullname;
            $datamailobject->exam_name = $examinstance->exam_arabicname;
            
        } else{
        $datamailobject->exam_userfullname = $examinstance->exam_userfullname;
        $datamailobject->exam_name = $examinstance->exam_name;
        }

        $datamailobject->exam_date = $examinstance->exam_date;
        $datamailobject->exam_time = $examinstance->exam_time;
        $datamailobject->body = $notification->body;
        $datamailobject->subject = $notification->subject;
        $datamailobject->arabic_body = $notification->arabic_body;
        $datamailobject->arabic_subject = $notification->arabic_subject;
        $datamailobject->moduleid = 1;
        $datamailobject->sendsms =1;
        $datamailobject->teammemberid = 0;
       
  
    
      
        $this->log_email_notification($touser, $fromuser, $datamailobject);
    }
    // public function send_other_exam_register_notification($examinstance, $touser, $fromuser, $emailtype, $notification, $waitinglistid = 0)
    // {        
    //     global $PAGE,$DB;      
    //     $datamailobject = new \stdClass();
    //     $datamailobject->emailtype = $emailtype;
    //     $datamailobject->notification_infoid = $notification->id;
    //     $datamailobject->plugintype = $notification->plugintype;
    //     $datamailobject->pluginname = $notification->pluginname; 
    //     $examownedby =  $DB->get_field('local_exams','ownedby',['id'=>$examinstance->id]);  
    //     $ownedbyvalue = $examownedby;
    //     $ownedbydata = get_config('local_exams','ownedby');
    //     $ownedbydata = json_decode($ownedbydata);
    //     $getownedbydata = [];
    //     foreach($ownedbydata AS $key=>$value) {
    //         $getownedbydata[$key] = $value;
    //     }
    //     $finalownedbykey = str_replace(' ','_',$ownedbyvalue).'-email';
    //     $ownedbyemail = $getownedbydata[$finalownedbykey];
    //     $fauser = get_admin();
    //     $fauser->email =  $ownedbyemail;
    //     $fauser->lang = 'en';
    //     $datamailobject->userlang =  $fauser->lang ;
    //     if( $datamailobject->userlang == 'ar'){        
    //         $datamailobject->exam_name = $examinstance->exam_arabicname;             
    //     } else{        
    //         $datamailobject->exam_name = $examinstance->exam_name;
    //     }
    //     $datamailobject->exam_userfullname = $examinstance->exam_userfullname;
    //     $datamailobject->exam_ownedby = $examinstance->exam_ownedby;
    //     $datamailobject->exam_date = $examinstance->exam_date;
    //     $datamailobject->exam_time = $examinstance->exam_time;
    //     $datamailobject->body = $notification->body;
    //     $datamailobject->subject = $notification->subject;
    //     $datamailobject->arabic_body = $notification->arabic_body;
    //     $datamailobject->arabic_subject = $notification->arabic_subject;
    //     $datamailobject->moduleid = 1;
    //     $datamailobject->sendsms =1;
    //     $datamailobject->teammemberid = 0;         
      
    //     $this->log_email_notification($fauser, $fromuser, $datamailobject);
    // }


    public function send_bulkenrol_exam_notification($examinstance, $touser, $fromuser, $emailtype, $notification, $waitinglistid = 0)
    {
        global $PAGE;
        $datamailobject = new \stdClass();

        $datamailobject->emailtype = $emailtype;
        $datamailobject->notification_infoid = $notification->id;
        $datamailobject->plugintype = $notification->plugintype;
        $datamailobject->pluginname = $notification->pluginname;      
        $datamailobject->userlang = $touser->lang;  
        $datamailobject->exam_name = $examinstance->exam_name;      
        $datamailobject->org_off = $examinstance->org_off;
        $datamailobject->trainee_details = $examinstance->trainee_details;
        $datamailobject->exam_startdate = $examinstance->exam_startdate;
        $datamailobject->exam_starttime = $examinstance->exam_starttime;
        $datamailobject->exam_endtime = $examinstance->exam_endtime;
        $datamailobject->body = $notification->body;
        $datamailobject->subject = $notification->subject;
        $datamailobject->arabic_body = $notification->arabic_body;
        $datamailobject->arabic_subject = $notification->arabic_subject;
        $datamailobject->moduleid = 1;
        $datamailobject->sendsms =1;
        $datamailobject->teammemberid = 0;      
  
    
      
        $this->log_email_notification($touser, $fromuser, $datamailobject);
    }
    public function send_exam_unenroll_notification($examinstance, $touser, $fromuser, $emailtype, $notification, $waitinglistid = 0)
    {
        global $PAGE;
        $datamailobject = new \stdClass();
      

        $datamailobject->emailtype = $emailtype;
        $datamailobject->notification_infoid = $notification->id;
        $datamailobject->plugintype = $notification->plugintype;
        $datamailobject->pluginname = $notification->pluginname;
        $datamailobject->moduleid = 1;
        $datamailobject->userlang = $touser->lang;  
        if($touser->lang == 'ar'){
            $datamailobject->exam_userfullname = $examinstance->exam_arabicuserfullname;
            $datamailobject->exam_name = $examinstance->exam_arabicname;
            
        } else{
        $datamailobject->exam_userfullname = $examinstance->exam_userfullname;
        $datamailobject->exam_name = $examinstance->exam_name;
        }
        $datamailobject->exam_date = $examinstance->exam_date;
        $datamailobject->exam_time = $examinstance->exam_time;

        $datamailobject->body = $notification->body;
        $datamailobject->subject = $notification->subject;
        $datamailobject->arabic_body = $notification->arabic_body;
        $datamailobject->arabic_subject = $notification->arabic_subject;
        $datamailobject->moduleid = 1;
        $datamailobject->sendsms = 1;
        $datamailobject->teammemberid = 0;
        
        $this->log_email_notification($touser, $fromuser, $datamailobject);
    }

   
    public function send_exam_reschedule_notification($examinstance, $touser, $fromuser, $emailtype, $notification, $waitinglistid = 0)
    {
        global $PAGE;
        $datamailobject = new \stdClass();
      

        $datamailobject->emailtype = $emailtype;
        $datamailobject->notification_infoid = $notification->id;
        $datamailobject->plugintype = $notification->plugintype;
        $datamailobject->pluginname = $notification->pluginname;
        $datamailobject->moduleid = 1;
        $datamailobject->userlang = $touser->lang;  
        if($touser->lang == 'ar'){
            $datamailobject->exam_userfullname = $examinstance->exam_arabicuserfullname;
            $datamailobject->exam_name = $examinstance->arabicexam_name;
            
        } else{
        $datamailobject->exam_userfullname = $examinstance->exam_userfullname;
        $datamailobject->exam_name = $examinstance->exam_name;
        }
        $datamailobject->pastexam_date = $examinstance->pastexam_date;
        $datamailobject->pastexam_time = $examinstance->pastexam_time;

        $datamailobject->presentexam_date = $examinstance->presentexam_date;
        $datamailobject->presentexam_time = $examinstance->presentexam_time;

        $datamailobject->body = $notification->body;
        $datamailobject->subject = $notification->subject;
        $datamailobject->arabic_body = $notification->arabic_body;
        $datamailobject->arabic_subject = $notification->arabic_subject;
        $datamailobject->moduleid = 1;
        $datamailobject->sendsms = 1;
        $datamailobject->teammemberid = 0;
        
        $this->log_email_notification($touser, $fromuser, $datamailobject);
    }
    public function send_exams_completion_notification($examinstance, $touser, $fromuser, $emailtype, $notification, $waitinglistid = 0)
    {
        global $PAGE;


        $datamailobject = new \stdClass();

        $datamailobject->emailtype = $emailtype;
        $datamailobject->notification_infoid = $notification->id;
        $datamailobject->plugintype = $notification->plugintype;
        $datamailobject->pluginname = $notification->pluginname;
        $datamailobject->moduleid = 1;
        $datamailobject->exam_name = $examinstance->exam_name;
        $datamailobject->exam_userfullname = $examinstance->exam_userfullname;
        $datamailobject->body = $notification->body;
        $datamailobject->subject = $notification->subject;
        $datamailobject->arabic_body = $notification->arabic_body;
        $datamailobject->arabic_subject = $notification->arabic_subject;
        $datamailobject->moduleid = 1;
        $datamailobject->sendsms =1;
        $datamailobject->teammemberid = 0;
       // $this->log_email_notification($touser, $fromuser, $datamailobject);
    }
    public function send_exams_certificate_assignment_notification($examinstance, $touser, $fromuser, $emailtype, $notification, $waitinglistid = 0)
    {
        global $PAGE;
        $datamailobject = new \stdClass();
        $datamailobject->emailtype = $emailtype;
        $datamailobject->notification_infoid = $notification->id;
        $datamailobject->plugintype = $notification->plugintype;
        $datamailobject->pluginname = $notification->pluginname;
        $datamailobject->moduleid = 1;
        $datamailobject->exam_userfullname = $examinstance->exam_userfullname;
        $datamailobject->exam_name = $examinstance->exam_name;
        $datamailobject->exam_certificatelink = $examinstance->exam_certificatelink;
        $datamailobject->body = $notification->body;
        $datamailobject->subject = $notification->subject;
        $datamailobject->arabic_body = $notification->arabic_body;
        $datamailobject->arabic_subject = $notification->arabic_subject;
        $datamailobject->moduleid = 1;
        $datamailobject->teammemberid = 0;

        $this->log_email_notification($touser, $fromuser, $datamailobject);
    }
    public function send_exams_before_7_days_notification($examinstance, $touser, $fromuser, $emailtype, $notification, $waitinglistid = 0)
    {
        global $PAGE;
        $datamailobject = new \stdClass();

        $datamailobject->emailtype = $emailtype;
        $datamailobject->notification_infoid = $notification->id;
        $datamailobject->plugintype = $notification->plugintype;
        $datamailobject->pluginname = $notification->pluginname;
        $datamailobject->moduleid = 1;
        $datamailobject->exam_related_module_name = $examinstance->exam_related_module_name;
        $datamailobject->exam_userfullname = $touser->firstname;
        $datamailobject->exam_related_module_date = $examinstance->exam_related_module_date;
        $datamailobject->exam_related_module_time = $examinstance->exam_related_module_time;
        $datamailobject->body = $notification->body;
        $datamailobject->subject = $notification->subject;
        $datamailobject->arabic_body = $notification->arabic_body;
        $datamailobject->arabic_subject = $notification->arabic_subject;
        $datamailobject->moduleid = 1;
        $datamailobject->touserid = $touser->id;
        $datamailobject->fromuserid = $fromuser->id;
        $datamailobject->sendsms =1;
        $datamailobject->teammemberid = 0;
        $this->log_email_notification($touser, $fromuser, $datamailobject);
    }
    public function send_exams_before_48_hours_notification($examinstance, $touser, $fromuser, $emailtype, $notification, $waitinglistid = 0)
    {
        global $PAGE;


        $datamailobject = new \stdClass();

        $datamailobject->emailtype = $emailtype;
        $datamailobject->notification_infoid = $notification->id;
        $datamailobject->plugintype = $notification->plugintype;
        $datamailobject->pluginname = $notification->pluginname;
        $datamailobject->moduleid = 1;
        $datamailobject->exam_related_module_name = $examinstance->exam_related_module_name;
        $datamailobject->exam_userfullname = $touser->firstname;
        $datamailobject->exam_related_module_date = $examinstance->exam_related_module_date;
        $datamailobject->exam_related_module_time = $examinstance->exam_related_module_time;
        $datamailobject->body = $notification->body;
        $datamailobject->subject = $notification->subject;
        $datamailobject->arabic_body = $notification->arabic_body;
        $datamailobject->arabic_subject = $notification->arabic_subject;
        $datamailobject->moduleid = 1;
        $datamailobject->touserid = $touser->id;
        $datamailobject->fromuserid = $fromuser->id;
        $datamailobject->sendsms =1;
        $datamailobject->teammemberid = 0;

        $this->log_email_notification($touser, $fromuser, $datamailobject);
    }
    public function send_exams_before_24_hours_notification($examinstance, $touser, $fromuser, $emailtype, $notification, $waitinglistid = 0)
    {
        global $PAGE;


        $datamailobject = new \stdClass();

        $datamailobject->emailtype = $emailtype;
        $datamailobject->notification_infoid = $notification->id;
        $datamailobject->plugintype = $notification->plugintype;
        $datamailobject->pluginname = $notification->pluginname;
        $datamailobject->moduleid = 1;
        $datamailobject->exam_related_module_name = $examinstance->exam_related_module_name;
        $datamailobject->exam_userfullname = $touser->firstname;
        $datamailobject->exam_related_module_date = $examinstance->exam_related_module_date;
        $datamailobject->exam_related_module_time = $examinstance->exam_related_module_time;
        $datamailobject->body = $notification->body;
        $datamailobject->subject = $notification->subject;
        $datamailobject->arabic_body = $notification->arabic_body;
        $datamailobject->arabic_subject = $notification->arabic_subject;
        $datamailobject->moduleid = 1;
        $datamailobject->touserid = $touser->id;
        $datamailobject->fromuserid = $fromuser->id;
        $datamailobject->sendsms =1;
        $datamailobject->teammemberid = 0;

        $this->log_email_notification($touser, $fromuser, $datamailobject);
    }
    public function send_exams_send_conclusion_notification($examinstance, $touser, $fromuser, $emailtype, $notification, $waitinglistid = 0)
    {
        global $PAGE;


        $datamailobject = new \stdClass();

        $datamailobject->emailtype = $emailtype;
        $datamailobject->notification_infoid = $notification->id;
        $datamailobject->plugintype = $notification->plugintype;
        $datamailobject->pluginname = $notification->pluginname;
        $datamailobject->moduleid = 1;
        $datamailobject->exam_related_module_name = $examinstance->exam_related_module_name;
        $datamailobject->exam_userfullname = $touser->firstname;
        $datamailobject->body = $notification->body;
        $datamailobject->subject = $notification->subject;
        $datamailobject->arabic_body = $notification->arabic_body;
        $datamailobject->arabic_subject = $notification->arabic_subject;
        $datamailobject->moduleid = 1;
        $datamailobject->touserid = $touser->id;
        $datamailobject->fromuserid = $fromuser->id;
        $datamailobject->sendsms =1;
        $datamailobject->teammemberid = 0;

       // $this->log_email_notification($touser, $fromuser, $datamailobject);
    }
    public function send_exams_after_session_notification($examinstance, $touser, $fromuser, $emailtype, $notification, $waitinglistid = 0)
    {
        global $PAGE;


        $datamailobject = new \stdClass();

        $datamailobject->emailtype = $emailtype;
        $datamailobject->notification_infoid = $notification->id;
        $datamailobject->plugintype = $notification->plugintype;
        $datamailobject->pluginname = $notification->pluginname;
        $datamailobject->moduleid = 1;
        $datamailobject->exam_related_module_name = $examinstance->exam_related_module_name;
        $datamailobject->exam_userfullname = $touser->firstname;
        $datamailobject->body = $notification->body;
        $datamailobject->subject = $notification->subject;
        $datamailobject->arabic_body = $notification->arabic_body;
        $datamailobject->arabic_subject = $notification->arabic_subject;
        $datamailobject->moduleid = 1;
        $datamailobject->touserid = $touser->id;
        $datamailobject->fromuserid = $fromuser->id;
        $datamailobject->sendsms =1;
        $datamailobject->teammemberid = 0;

        $this->log_email_notification($touser, $fromuser, $datamailobject);
    }

    public function exams_before_7_days_notification($lastruntime,$nextruntime,$timenow)
    {    
        global $DB, $USER;
        $data = new \stdClass();
        $from = null;
        // $from = date('Y-m-d');
        $fromdate = ($from !== null) ? $from : date('Y-m-d');
        $sqlexams = "   SELECT le.exam,date(from_unixtime(le.examdatetime)) AS examdatetime,
                        from_unixtime(le.slot) AS slot,ee.userid  
                        FROM {local_exams} AS le 
                        INNER JOIN {exam_enrollments} AS ee ON ee.examid = le.id 
                        WHERE date(from_unixtime(examdatetime-(60*60*24*7))) = '$fromdate'
                            AND (examdatetime-(60*60*24*7)) >= $lastruntime
                            AND (examdatetime-(60*60*24*7)) <= $timenow";
        $examslist = $DB->get_records_sql($sqlexams);
        if (!empty($examslist)) {
            foreach ($examslist as $key => $exam) {
                $sql = "SELECT lnt.* 
                        FROM {local_notification_type} AS lnt
                        WHERE shortname IN ('exams_before_7_days') ";
                $notificationtype = $DB->get_record_sql($sql);
                $notificationtype->exam_related_module_name = $exam->exam;
                $notificationtype->exam_related_module_date = date('d-M-Y', strtotime($exam->examdatetime));
                $notificationtype->exam_related_module_time = date('H:i:s', strtotime($exam->slot));
                $user = $DB->get_record('user', array('id' => $exam->userid, 'deleted' => 0));
                $this->exams_notification('exams_before_7_days', $touser = $user, $fromuser = get_admin(), $notificationtype, $waitinglistid = 0);
            }
        }
    }
    public function exams_before_48_hours_notification($lastruntime,$nextruntime,$timenow)
    {
        global $DB, $USER;
        $data = new \stdClass();
        $from = null;
        // $from = date('Y-m-d');
        $fromdate = ($from !== null) ? $from : date('Y-m-d');
        $sqlexams = "   SELECT le.exam,date(from_unixtime(le.examdatetime)) AS examdatetime,
                        from_unixtime(le.slot) AS slot,ee.userid  
                        FROM {local_exams} AS le 
                        INNER JOIN {exam_enrollments} AS ee on ee.examid = le.id 
                        WHERE date(from_unixtime(examdatetime-(60*60*48))) = '$fromdate'
                            AND (examdatetime-(60*60*48)) >= $lastruntime
                            AND (examdatetime-(60*60*48)) <= $timenow";
        $examslist = $DB->get_records_sql($sqlexams);
        if (!empty($examslist)) {
            foreach ($examslist as $key => $exam) {
                $sql = "SELECT lnt.* 
                        FROM {local_notification_type} AS lnt
                        WHERE shortname IN ('exams_before_48_hours') ";
                $notificationtype = $DB->get_record_sql($sql);
                $notificationtype->exam_related_module_name = $exam->exam;
                $notificationtype->exam_related_module_date = date('d-M-Y', strtotime($exam->examdatetime));
                $notificationtype->exam_related_module_time = date('H:i:s', strtotime($exam->slot));
                $user = $DB->get_record('user', array('id' => $exam->userid, 'deleted' => 0));
                // print_r($user);
                $this->exams_notification('exams_before_48_hours', $touser = $user, $fromuser = get_admin(), $notificationtype, $waitinglistid = 0);
            }
        }
    }
    public function exams_before_24_hours_notification($lastruntime,$nextruntime,$timenow)
    {
        global $DB, $USER;
        $data = new \stdClass();
        $from = null;
        // $from = date('Y-m-d');
        $fromdate = ($from !== null) ? $from : date('Y-m-d');
        $sqlexams = "   SELECT le.exam,date(from_unixtime(le.examdatetime)) AS examdatetime,
                        from_unixtime(le.slot) AS slot,ee.userid  
                        FROM {local_exams} AS le 
                        INNER JOIN {exam_enrollments} AS ee ON ee.examid = le.id 
                        WHERE date(from_unixtime(examdatetime-(60*60*24))) = '$fromdate'
                            AND (examdatetime-(60*60*24)) >= $lastruntime
                            AND (examdatetime-(60*60*24)) <= $timenow";
        $examslist = $DB->get_records_sql($sqlexams);
        if (!empty($examslist)) {
            foreach ($examslist as $key => $exam) {
                $sql = "SELECT lnt.* 
                        FROM {local_notification_type} AS lnt
                        WHERE shortname IN ('exams_before_24_hours') ";
                $notificationtype = $DB->get_record_sql($sql);
                $notificationtype->exam_related_module_name = $exam->exam;
                $notificationtype->exam_related_module_date = date('d-M-Y', strtotime($exam->examdatetime));
                $notificationtype->exam_related_module_time = date('H:i:s', strtotime($exam->slot));
                $user = $DB->get_record('user', array('id' => $exam->userid, 'deleted' => 0));
                $this->exams_notification('exams_before_24_hours', $touser = $user, $fromuser = get_admin(), $notificationtype, $waitinglistid = 0);
            }
        }
    }
    public function exams_after_session_notification($lastruntime,$nextruntime,$timenow)
    {
        global $DB, $USER;
        $data = new \stdClass();
        // $from = date('Y-m-d');
        $from = null;
        $fromdate = ($from !== null) ? $from : date('Y-m-d');
        $sqlexams = "SELECT *  
                     FROM {local_exams} AS le
                     INNER JOIN {exam_enrollments} AS ee ON ee.examid = le.id 
                     INNER JOIN {exam_completions} AS ec ON ec.examid = le.id
                     WHERE from_unixtime(le.enddate)  = '$fromdate' 
                        AND ec.timecreated >= $lastruntime 
                        AND ec.timecreated <= $timenow";
        $examslist = $DB->get_records_sql($sqlexams);
        if (!empty($examslist)) {
            // $examslist->
            foreach ($examslist as $key => $exam) {
                $sql = "SELECT lnt.* 
                        FROM {local_notification_type} AS lnt
                        WHERE shortname IN ('exams_after_session') ";
                $notificationtype = $DB->get_record_sql($sql);
                $notificationtype->exam_related_module_name = $exam->exam;
                $user = $DB->get_record('user', array('id' => $exam->userid, 'deleted' => 0));
                $this->exams_notification('exams_after_session', $touser = $user, $fromuser = get_admin(), $notificationtype, $waitinglistid = 0);
            }
        }
    }
    public function exams_send_conclusion_notification($lastruntime,$nextruntime,$timenow)
    {
        global $DB, $USER;
        $data = new \stdClass();
         $from = Null;
        $fromdate = ($from !== null) ? $from : date('Y-m-d');
        $sqlexams = "SELECT *  
                     FROM {local_exams} AS le
                     INNER JOIN {exam_enrollments} AS ee ON ee.examid = le.id 
                     INNER JOIN {exam_completions} AS ec ON ec.examid = le.id
                     WHERE from_unixtime(le.enddate)  = '$fromdate' 
                        AND ec.timecreated >= $lastruntime 
                        AND ec.timecreated <= $timenow";
        $examslist = $DB->get_records_sql($sqlexams);
        if (!empty($examslist)) {
            // $examslist->
            foreach ($examslist as $key => $exam) {
                $sql = "SELECT lnt.* 
                        FROM {local_notification_type} AS lnt
                        WHERE shortname IN ('exams_send_conclusion') ";
                $notificationtype = $DB->get_record_sql($sql);
                $notificationtype->exam_related_module_name = $exam->exam;
                $user = $DB->get_record('user', array('id' => $exam->userid, 'deleted' => 0));
                $this->exams_notification('exams_send_conclusion', $touser = $user, $fromuser = get_admin(), $notificationtype, $waitinglistid = 0);
            }
        }
    }
    public function send_other_exam_enrollment_notification($examinstance, $touser, $fromuser, $emailtype, $notification, $waitinglistid = 0)
    {
        global $PAGE;
      
        $datamailobject = new \stdClass();


        $datamailobject->emailtype = $emailtype;
        $datamailobject->notification_infoid = $notification->id;
        $datamailobject->plugintype = $notification->plugintype;
        $datamailobject->pluginname = $notification->pluginname;      
        $datamailobject->userlang = $touser->lang;  
        if($touser->lang == 'ar'){
            $datamailobject->exam_userfullname = $examinstance->exam_arabicuserfullname;
            $datamailobject->exam_name = $examinstance->exam_arabicname;
            
        } else{
        $datamailobject->exam_userfullname = $examinstance->exam_userfullname;
        $datamailobject->exam_name = $examinstance->exam_name;
        }
        $datamailobject->exam_ownedby = $examinstance->exam_ownedby;
        $datamailobject->exam_date = $examinstance->exam_date;
        $datamailobject->exam_time = $examinstance->exam_time;
        $datamailobject->body = $notification->body;
        $datamailobject->subject = $notification->subject;
        $datamailobject->arabic_body = $notification->arabic_body;
        $datamailobject->arabic_subject = $notification->arabic_subject;
        $datamailobject->moduleid = 1;
        $datamailobject->sendsms =1;
        $datamailobject->teammemberid = 0; 
    
      
        $this->log_email_notification($touser, $fromuser, $datamailobject);
    }


    public function send_exam_service_provider_notification($examinstance, $touser, $fromuser, $emailtype, $notification, $waitinglistid = 0)
    {
        global $PAGE,$DB;
        $datamailobject = new \stdClass();


        $datamailobject->emailtype = $emailtype;
        $datamailobject->notification_infoid = $notification->id;
        $datamailobject->plugintype = $notification->plugintype;
        $datamailobject->pluginname = $notification->pluginname;      
        $datamailobject->userlang = $touser->lang;  
        $datamailobject->exam_userfullname = $examinstance->exam_userfullname;
        $datamailobject->exam_useridno =    $examinstance->exam_useridno ;
        $datamailobject->exam_useremail =    $examinstance->exam_useremail;
        $datamailobject->exam_userorg   =     $examinstance->exam_userorg ;      
        $datamailobject->exam_center   =  $examinstance->exam_center;
        $datamailobject->exam_profilelang =  $examinstance->exam_profilelang;
        $datamailobject->exam_userdob =  $examinstance->exam_userdob;

        $datamailobject->exam_ownedby = $examinstance->exam_ownedby;
        $datamailobject->exam_date = $examinstance->exam_date;
        $datamailobject->exam_time = $examinstance->exam_time;
        $datamailobject->exam_endtime = $examinstance->exam_endtime;
        $datamailobject->body = $notification->body;
        $datamailobject->subject = $notification->subject;
        $datamailobject->arabic_body = $notification->arabic_body;
        $datamailobject->arabic_subject = $notification->arabic_subject;
        $datamailobject->moduleid = 1;
        $datamailobject->sendsms =1;
        $datamailobject->teammemberid = 0;         

        $touser->lang = 'en';
        $datamailobject->userlang =  $touser->lang ;
        if( $datamailobject->userlang == 'ar'){
        
            $datamailobject->exam_name = $examinstance->exam_arabicname;
             
        } else{
     
        $datamailobject->exam_name = $examinstance->exam_name;
        }
        $this->log_email_notification($touser, $fromuser, $datamailobject);
    }

    public function send_exam_result_objection_notification($examinstance, $touser, $fromuser, $emailtype, $notification, $waitinglistid = 0)
    {
        global $PAGE,$DB;
        $datamailobject = new \stdClass();


        $datamailobject->emailtype = $emailtype;
        $datamailobject->notification_infoid = $notification->id;
        $datamailobject->plugintype = $notification->plugintype;
        $datamailobject->pluginname = $notification->pluginname;     
       
        $datamailobject->body = $notification->body;
        $datamailobject->subject = $notification->subject;
        $datamailobject->arabic_body = $notification->arabic_body;
        $datamailobject->arabic_subject = $notification->arabic_subject;
        $datamailobject->moduleid = 1;
      
        $datamailobject->teammemberid = 0;   


        $roleusers = $this->getsystemlevel_role_users('examofficial', 0);
        if ($roleusers) {
            foreach ($roleusers as $roleuser) {
                $datamailobject->userlang = $roleuser->lang;

                $localuserrecord = $DB->get_record('local_users',['userid'=> $roleuser->id]);
                $rolefullname = ($localuserrecord)? (($roleuser->lang == 'ar') ? $localuserrecord->firstnamearabic.' '.$localuserrecord->middlenamearabic.' '.$localuserrecord->thirdnamearabic.' '.$localuserrecord->lastnamearabic  : $localuserrecord->firstname.' '.$localuserrecord->middlenameen.' '.$localuserrecord->thirdnameen.' '.$localuserrecord->lastname) :fullname($DB->get_record('user',array('id'=>(int)$roleuser->id)));
                $datamailobject->exam_examoff = $rolefullname;

                $userrecord = $DB->get_record('local_users',['userid'=> $examinstance->userid]);
                $fullname = ($userrecord)? (($roleuser->lang == 'ar') ? $userrecord->firstnamearabic.' '.$userrecord->middlenamearabic.' '.$userrecord->thirdnamearabic.' '.$userrecord->lastnamearabic  : $userrecord->firstname.' '.$userrecord->middlenameen.' '.$userrecord->thirdnameen.' '.$userrecord->lastname) :fullname($DB->get_record('user',array('id'=>(int)$examinstance->userid)));
                $datamailobject->exam_userfullname = $fullname;
                $exams =$DB->get_record('local_exams',array('id'=>$examinstance->examid));
                if($roleuser->lang == 'ar'){
                    $datamailobject->exam_name = $exams->examnamearabic;
                } else{
                    $datamailobject->exam_name = $exams->exam;
                }
           
           
                $this->log_email_notification($roleuser, $fromuser, $datamailobject);
            }
          
        }   
    

    }

}
