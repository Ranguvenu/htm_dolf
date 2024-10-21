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
 * @package local_cpd
 */

namespace local_cpd;

use stdClass;

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
            case 'cpd_create':
                $strings = "[cpd_user_fullname],[cpd_name]";
                break;

            case 'cpd_update':
                $strings = "[cpd_user_fullname],[cpd_name]";
                break;
            case 'cpd_expiration_lt_180days_and_gt_90_days':
                $strings = "[cpd_user_fullname],[cpd_certificate_name]";
                break;

            case 'cpd_expiration_lt_90_days':
                $strings = "[cpd_user_fullname],[cpd_certificate_name]";
                break;
            case 'cpd_evidence_submit':
                $strings = "[cpd_user_fullname],[cpd_certificate_name],[cpd_submit_time]";
                break;

            case 'cpd_evidence_approve':
                $strings = "[cpd_user_fullname],[cpd_certificate_name]";
                break;
            case 'cpd_evidence_reject':
                $strings = "[cpd_user_fullname],[cpd_rejection_reason],[cpd_certificate_name],[cpd_reject_time]";
                break;

            case 'cpd_training_program_assign':
                $strings = "[cpd_user_fullname],[cpd_programname]";
                break;
            case 'cpd_training_program_unassign':
                $strings = "[cpd_user_fullname],[cpd_programname]";
                break;
            case 'cpd_completion':
                $strings = "[cpd_user_fullname], [cpd_name],[cpd_certificate_expirydate]";
                break;

            case 'cpd_certificate_renewal':
                $strings = "[cpd_user_fullname], [cpd_name],[cpd_certificate_expirydate]";
                break;

            case 'cpd_onchange':
                $strings = "[cpd_user_fullname], [cpd_name],[cpd_module_link]";
                break;

            case 'cpd_cancel':
                $strings = "[RelatedModuleName],[FullName]";
                break;

            case 'cpd_reschedule':
                $strings = "[cpd_user_fullname], [cpd_name],[cpd_module_link]";
                break;
        }

        return $strings;
    }
    public function cpd_notification($emailtype, $touser, $fromuser, $cpdinstance, $waitinglistid = 0, $data = null)
    {
        if ($notification = $this->get_existing_notification($cpdinstance, $emailtype)) {
            $functionname = 'send_' . $emailtype . '_notification';
            $this->$functionname($cpdinstance, $touser, $fromuser, $emailtype, $notification, $waitinglistid, $data);
        }
        
    }
    public function send_cpd_create_notification($cpdinstance, $touser, $fromuser, $emailtype, $notification, $waitinglistid = 0, $data)
    {
        global $PAGE;
        $datamailobject = new \stdClass();

        $datamailobject->emailtype = $emailtype;
        $datamailobject->notification_infoid = $notification->id;
        $datamailobject->plugintype = $notification->plugintype;
        $datamailobject->pluginname = $notification->pluginname;
        $datamailobject->moduleid = 1;

        // $datamailobject->cpd_user_fullname = $touser->firstname;
        $datamailobject->cpd_name = $data->code;


        $datamailobject->body = $notification->body;
        $datamailobject->subject = $notification->subject;
        $datamailobject->arabic_body = $notification->arabic_body;
        $datamailobject->arabic_subject = $notification->arabic_subject;
        $datamailobject->cpdid = $cpdinstance->id;
        // $datamailobject->touserid = $touser->id;
        $datamailobject->fromuserid = $fromuser->id;
        $datamailobject->teammemberid = 0;

        $roleusers = $this->getsystemlevel_role_users('to', 0);
        if ($roleusers) {
            foreach ($roleusers as $roleuser) {
                $datamailobject->cpd_user_fullname = $roleuser->username;
                // echo $roleuser->username;
                $this->log_email_notification($roleuser, $fromuser, $datamailobject);
            }
        }
        if ($touser) {
            foreach ($touser as $tu) {
                $datamailobject->cpd_user_fullname = $tu->username;
                // echo $tu->username;
                $this->log_email_notification($tu, $fromuser, $datamailobject);
            }
        }
    }
    public function send_cpd_update_notification($cpdinstance, $touser, $fromuser, $emailtype, $notification, $waitinglistid = 0, $data)
    {
        global $PAGE;
        $datamailobject = new \stdClass();

        $datamailobject->emailtype = $emailtype;
        $datamailobject->notification_infoid = $notification->id;
        $datamailobject->plugintype = $notification->plugintype;
        $datamailobject->pluginname = $notification->pluginname;
        $datamailobject->moduleid = 1;
        // $datamailobject->cpd_user_fullname = $touser->firstname;
        $datamailobject->cpd_name = $data->code;

        $datamailobject->body = $notification->body;
        $datamailobject->subject = $notification->subject;
        $datamailobject->arabic_body = $notification->arabic_body;
        $datamailobject->arabic_subject = $notification->arabic_subject;
        $datamailobject->cpdid = $cpdinstance->id;
        // $datamailobject->touserid = $touser->id;
        $datamailobject->fromuserid = $fromuser->id;
        $datamailobject->teammemberid = 0;
        $roleusers = $this->getsystemlevel_role_users('to', 0);
        if ($roleusers) {
            foreach ($roleusers as $roleuser) {
                $datamailobject->cpd_user_fullname = $roleuser->username;
                $this->log_email_notification($roleuser, $fromuser, $datamailobject);
            }
        }
        if ($touser) {
            foreach ($touser as $tu) {
                // print_r($tu);
                $datamailobject->cpd_user_fullname = $tu->username;
                $this->log_email_notification($tu, $fromuser, $datamailobject);
            }
        }
    }
    public function send_cpd_expiration_lt_180days_and_gt_90_days_notification($cpdinstance, $touser, $fromuser, $emailtype, $notification, $waitinglistid = 0, $data)
    {
        global $PAGE;
        $datamailobject = new \stdClass();

        $datamailobject->emailtype = $emailtype;
        $datamailobject->notification_infoid = $notification->id;
        $datamailobject->plugintype = $notification->plugintype;
        $datamailobject->pluginname = $notification->pluginname;
        $datamailobject->moduleid = 1;
        $datamailobject->cpd_user_fullname = $touser->firstname;
        $datamailobject->cpd_certificate_name = $data->certificatename;


        $datamailobject->body = $notification->body;
        $datamailobject->subject = $notification->subject;
        $datamailobject->arabic_body = $notification->arabic_body;
        $datamailobject->arabic_subject = $notification->arabic_subject;
        $datamailobject->cpdid = $cpdinstance->id;
        $datamailobject->touserid = $touser->id;
        $datamailobject->fromuserid = $fromuser->id;
        $datamailobject->teammemberid = 0;

        $this->log_email_notification($touser, $fromuser, $datamailobject);
     
    }
    public function send_cpd_expiration_lt_90_days_notification($cpdinstance, $touser, $fromuser, $emailtype, $notification, $waitinglistid = 0, $data)
    {
        global $PAGE;
        $datamailobject = new \stdClass();

        $datamailobject->emailtype = $emailtype;
        $datamailobject->notification_infoid = $notification->id;
        $datamailobject->plugintype = $notification->plugintype;
        $datamailobject->pluginname = $notification->pluginname;
        $datamailobject->moduleid = 1;

        $datamailobject->cpd_user_fullname = $touser->firstname;
        $datamailobject->cpd_certificate_name = $data->certificatename;


        $datamailobject->body = $notification->body;
        $datamailobject->subject = $notification->subject;
        $datamailobject->arabic_body = $notification->arabic_body;
        $datamailobject->arabic_subject = $notification->arabic_subject;
        $datamailobject->cpdid = $cpdinstance->id;
        $datamailobject->touserid = $touser->id;
        $datamailobject->fromuserid = $fromuser->id;
        $datamailobject->teammemberid = 0;

        $this->log_email_notification($touser, $fromuser, $datamailobject);
     
    }
    public function send_cpd_evidence_submit_notification($cpdinstance, $touser, $fromuser, $emailtype, $notification, $waitinglistid = 0, $data)
    {
        global $PAGE,$DB;
        $datamailobject = new \stdClass();

        $datamailobject->emailtype = $emailtype;
        $datamailobject->notification_infoid = $notification->id;
        $datamailobject->plugintype = $notification->plugintype;
        $datamailobject->pluginname = $notification->pluginname;
        $datamailobject->moduleid = 1;
        $exam = $DB->get_record('local_exams', array('id' => $data->cpdid));
        $cpduser = $DB->get_record('local_users',array('userid'=>$touser->id));
        $time = time();
        $starttimemeridian = gmdate('a', $time );       
        if($touser->lang == 'ar'){
            $datamailobject->cpd_user_fullname = $cpduser->firstnamearabic .''.$cpduser->lastnamearabic.''. $cpduser->middlenamearabic.''. $cpduser->thirdnamearabic;
            $datamailobject->cpd_certificate_name = $exam->examnamearabic;
            $startmeridian = ($starttimemeridian == 'am')? 'صباحًا' : 'مساءً';
        }else{
            $datamailobject->cpd_user_fullname = $cpduser->firstname .''.$cpduser->lastname.''. $cpduser->middlenameen.''. $cpduser->thirdnameen;
            $datamailobject->cpd_certificate_name = $exam->exam;
            $startmeridian = ($starttimemeridian == 'am')? 'AM': 'PM';       
        }   
        $datamailobject->cpd_submit_time    =  gmdate("H:i",$time) .''.$startmeridian;  
        $datamailobject->body = $notification->body;
        $datamailobject->subject = $notification->subject;
        $datamailobject->arabic_body = $notification->arabic_body;
        $datamailobject->arabic_subject = $notification->arabic_subject;
        $datamailobject->cpdid = $cpdinstance->id;
        // $datamailobject->touserid = $touser->id;
        $datamailobject->fromuserid = $fromuser->id;
        $datamailobject->teammemberid = 0;
        $this->log_email_notification($touser, $fromuser, $datamailobject);
        $roleusers = $this->getsystemlevel_role_users('cpd', 0);      
        if ($roleusers) {          
            foreach ($roleusers as $roleuser) {
                $cpduser = $DB->get_record('local_users',array('userid'=>$roleuser->id));
                if($roleuser->lang == 'ar'){                  
                    $datamailobject->cpd_user_fullname = $cpduser->firstnamearabic .''.$cpduser->lastnamearabic.''. $cpduser->middlenamearabic.''. $cpduser->thirdnamearabic;
                    $datamailobject->cpd_certificate_name = $exam->examnamearabic;
                    $startmeridian = ($starttimemeridian == 'am')? 'صباحًا' : 'مساءً';                    
                } else{
                    $datamailobject->cpd_user_fullname = $cpduser->firstname .''.$cpduser->lastname.''. $cpduser->middlenameen.''. $cpduser->thirdnameen;
                    $datamailobject->cpd_certificate_name = $exam->exam;
                    $startmeridian = ($starttimemeridian == 'am')? 'AM': 'PM';  

                }
                $datamailobject->cpd_submit_time    =  gmdate("H:i:s",$time) .''.$startmeridian;   
                $this->log_email_notification($roleuser, $fromuser, $datamailobject);
            }
        }     
    }
    public function send_cpd_evidence_approve_notification($cpdinstance, $touser, $fromuser, $emailtype, $notification, $waitinglistid = 0, $data)
    {
        global $PAGE;
        $datamailobject = new \stdClass();

        $datamailobject->emailtype = $emailtype;
        $datamailobject->notification_infoid = $notification->id;
        $datamailobject->plugintype = $notification->plugintype;
        $datamailobject->pluginname = $notification->pluginname;
        $datamailobject->moduleid = 1;

        $datamailobject->cpd_user_fullname = $touser->firstname;
        $datamailobject->cpd_certificate_name = $cpdinstance->certificatename;

        $datamailobject->userlang = $touser->lang;  
        $datamailobject->body = $notification->body;
        $datamailobject->subject = $notification->subject;
        $datamailobject->arabic_body = $notification->arabic_body;
        $datamailobject->arabic_subject = $notification->arabic_subject;
        $datamailobject->cpdid = $cpdinstance->id;
        $datamailobject->touserid = $touser->id;
        $datamailobject->fromuserid = $fromuser->id;
        $datamailobject->teammemberid = 0;


        $this->log_email_notification($touser, $fromuser, $datamailobject);
     
    }
    public function send_cpd_evidence_reject_notification($cpdinstance, $touser, $fromuser, $emailtype, $notification, $waitinglistid = 0, $data)
    {
        global $PAGE,$DB;
        $datamailobject = new \stdClass();

        $datamailobject->emailtype = $emailtype;
        $datamailobject->notification_infoid = $notification->id;
        $datamailobject->plugintype = $notification->plugintype;
        $datamailobject->pluginname = $notification->pluginname;
        $datamailobject->moduleid = 1;
        $datamailobject->userlang = $touser->lang;
        $exam = $DB->get_record('local_exams', array('id' => $data->examid));
        $cpduser = $DB->get_record('local_users',array('userid'=>$touser->id));
        $time = time();
        $starttimemeridian = gmdate('a', $time );
        if($touser->lang == 'ar'){
            $datamailobject->cpd_user_fullname = $cpduser->firstnamearabic .''.$cpduser->lastnamearabic.''. $cpduser->middlenamearabic.''. $cpduser->thirdnamearabic;
            $datamailobject->cpd_certificate_name = $exam->examnamearabic;
            $startmeridian = ($starttimemeridian == 'am')? 'صباحًا' : 'مساءً';
        }else{
            $datamailobject->cpd_user_fullname = $cpduser->firstname .''.$cpduser->lastname.''. $cpduser->middlenameen.''. $cpduser->thirdnameen;
            $datamailobject->cpd_certificate_name = $exam->exam;
            $startmeridian = ($starttimemeridian == 'am')? 'AM': 'PM';       
        }    
        $datamailobject->cpd_reject_time    =  gmdate("H:i:s",$time) .'' .$startmeridian; 
        $datamailobject->cpd_rejection_reason = $cpdinstance->rejectionreason;     
        $datamailobject->body = $notification->body;
        $datamailobject->subject = $notification->subject;
        $datamailobject->arabic_body = $notification->arabic_body;
        $datamailobject->arabic_subject = $notification->arabic_subject;
        $datamailobject->cpdid = $cpdinstance->id;
        $datamailobject->touserid = $touser->id;
        $datamailobject->fromuserid = $fromuser->id;
        $datamailobject->teammemberid = 0;
        $this->log_email_notification($touser, $fromuser, $datamailobject);
     
    }
    public function send_cpd_training_program_assign_notification($cpdinstance, $touser, $fromuser, $emailtype, $notification, $waitinglistid = 0, $data)
    {
        global $PAGE;
        $datamailobject = new \stdClass();

        $datamailobject->emailtype = $emailtype;
        $datamailobject->notification_infoid = $notification->id;
        $datamailobject->plugintype = $notification->plugintype;
        $datamailobject->pluginname = $notification->pluginname;
        $datamailobject->moduleid = 1;

        // $datamailobject->cpd_user_fullname = $touser->firstname;
        $datamailobject->cpd_programname = $data->programname;


        $datamailobject->body = $notification->body;
        $datamailobject->subject = $notification->subject;
        $datamailobject->arabic_body = $notification->arabic_body;
        $datamailobject->arabic_subject = $notification->arabic_subject;
        $datamailobject->cpdid = $cpdinstance->id;
        // $datamailobject->touserid = $touser->id;
        $datamailobject->fromuserid = $fromuser->id;
        $datamailobject->teammemberid = 0;
        if ($touser) {
            foreach ($touser as $roleuser) {
                $datamailobject->cpd_user_fullname = $roleuser->username;
                $this->log_email_notification($roleuser, $fromuser, $datamailobject);
            }
        }
    }
    public function send_cpd_training_program_unassign_notification($cpdinstance, $touser, $fromuser, $emailtype, $notification, $waitinglistid = 0, $data)
    {
        global $PAGE;
        $datamailobject = new \stdClass();

        $datamailobject->emailtype = $emailtype;
        $datamailobject->notification_infoid = $notification->id;
        $datamailobject->plugintype = $notification->plugintype;
        $datamailobject->pluginname = $notification->pluginname;
        $datamailobject->moduleid = 1;


        // $datamailobject->cpd_user_fullname = $touser->firstname;
        $datamailobject->cpd_programname = $data->programname;


        $datamailobject->body = $notification->body;
        $datamailobject->subject = $notification->subject;
        $datamailobject->arabic_body = $notification->arabic_body;
        $datamailobject->arabic_subject = $notification->arabic_subject;
        $datamailobject->cpdid = $cpdinstance->id;
        // $datamailobject->touserid = $touser->id;
        $datamailobject->fromuserid = $fromuser->id;
        $datamailobject->teammemberid = 0;
        if ($touser) {
            foreach ($touser as $roleuser) {
                $datamailobject->cpd_user_fullname = $roleuser->username;
                $this->log_email_notification($roleuser, $fromuser, $datamailobject);
            }
        }
    }
    public function send_cpd_completion_notification($cpdinstance, $touser, $fromuser, $emailtype, $notification, $waitinglistid = 0, $data)
    {
        global $PAGE;
        $datamailobject = new \stdClass();

        $datamailobject->emailtype = $emailtype;
        $datamailobject->notification_infoid = $notification->id;
        $datamailobject->plugintype = $notification->plugintype;
        $datamailobject->pluginname = $notification->pluginname;
        $datamailobject->moduleid = 1;
        $datamailobject->cpd_name = $data->name;
        $datamailobject->cpd_user_fullname = $touser->firstname;
        $datamailobject->cpd_certificate_expirydate = $data->cpd_certificate_expirydate;



        $datamailobject->body = $notification->body;
        $datamailobject->subject = $notification->subject;
        $datamailobject->arabic_body = $notification->arabic_body;
        $datamailobject->arabic_subject = $notification->arabic_subject;
        $datamailobject->cpdid = $cpdinstance->id;
        $datamailobject->touserid = $touser->id;
        $datamailobject->fromuserid = $fromuser->id;
        $datamailobject->teammemberid = 0;

        $this->log_email_notification($touser, $fromuser, $datamailobject);
     
    }
    public function send_cpd_certificate_renewal_notification($cpdinstance, $touser, $fromuser, $emailtype, $notification, $waitinglistid = 0, $data)
    {
        global $PAGE;
        $datamailobject = new \stdClass();

        $datamailobject->emailtype = $emailtype;
        $datamailobject->notification_infoid = $notification->id;
        $datamailobject->plugintype = $notification->plugintype;
        $datamailobject->pluginname = $notification->pluginname;
        $datamailobject->moduleid = 1;

        $datamailobject->cpd_name = $data->name;
        $datamailobject->cpd_user_fullname = $touser->firstname;
        $datamailobject->cpd_certificate_expirydate = $data->cpd_certificate_expirydate;


        $datamailobject->body = $notification->body;
        $datamailobject->subject = $notification->subject;
        $datamailobject->arabic_body = $notification->arabic_body;
        $datamailobject->arabic_subject = $notification->arabic_subject;
        $datamailobject->cpdid = $cpdinstance->id;
        $datamailobject->touserid = $touser->id;
        $datamailobject->fromuserid = $fromuser->id;
        $datamailobject->teammemberid = 0;

        $this->log_email_notification($touser, $fromuser, $datamailobject);
     
    }
    public function send_cpd_onchange_notification($cpdinstance, $touser, $fromuser, $emailtype, $notification, $waitinglistid = 0, $data)
    {
        global $PAGE;
        $datamailobject = new \stdClass();

        $datamailobject->emailtype = $emailtype;
        $datamailobject->notification_infoid = $notification->id;
        $datamailobject->plugintype = $notification->plugintype;
        $datamailobject->pluginname = $notification->pluginname;
        $datamailobject->moduleid = 1;

        $datamailobject->cpd_name = $data->name;
        $datamailobject->cpd_user_fullname = $touser->firstname;
        $datamailobject->cpd_module_link = $data->cpd_module_link;

        $datamailobject->body = $notification->body;
        $datamailobject->subject = $notification->subject;
        $datamailobject->arabic_body = $notification->arabic_body;
        $datamailobject->arabic_subject = $notification->arabic_subject;
        $datamailobject->cpdid = $cpdinstance->id;
        $datamailobject->touserid = $touser->id;
        $datamailobject->fromuserid = $fromuser->id;
        $datamailobject->teammemberid = 0;

        $this->log_email_notification($touser, $fromuser, $datamailobject);
    }
    public function send_cpd_cancel_notification($cpdinstance, $touser, $fromuser, $emailtype, $notification, $waitinglistid = 0, $data)
    {
        global $PAGE,$DB;
        $datamailobject = new \stdClass();

        $datamailobject->emailtype = $emailtype;
        $datamailobject->notification_infoid = $notification->id;
        $datamailobject->plugintype = $notification->plugintype;
        $datamailobject->pluginname = $notification->pluginname;
        $datamailobject->moduleid = 1;
        $datamailobject->RelatedModuleName = $cpdinstance['RelatedModuleName'];
        $datamailobject->body = $notification->body;
        $datamailobject->subject = $notification->subject;
        $datamailobject->arabic_body = $notification->arabic_body;
        $datamailobject->arabic_subject = $notification->arabic_subject;
        $datamailobject->cpdid = $cpdinstance->id;
        $datamailobject->touserid = $touser->id;
        $datamailobject->fromuserid = $fromuser->id;
        $datamailobject->teammemberid = 0;

        if ($touser) {
            foreach ($touser as $roleuser) {
                $localuserrecord = $DB->get_record('local_users',['userid'=>$roleuser->id]);
                $datamailobject->FullName = ($localuserrecord)? (($roleuser->lang == 'ar') ? $localuserrecord->firstnamearabic.' '.$localuserrecord->middlenamearabic.' '.$localuserrecord->thirdnamearabic.' '.$localuserrecord->lastnamearabic  : $localuserrecord->firstname.' '.$localuserrecord->middlenameen.' '.$localuserrecord->thirdnameen.' '.$localuserrecord->lastname) :fullname($DB->get_record('user',['id'=>$roleuser->id]));
                $this->log_email_notification($roleuser, $fromuser, $datamailobject);
            }
        }
    }
    public function send_cpd_reschedule_notification($cpdinstance, $touser, $fromuser, $emailtype, $notification, $waitinglistid = 0, $data)
    {
        global $PAGE;
        $datamailobject = new \stdClass();

        $datamailobject->emailtype = $emailtype;
        $datamailobject->notification_infoid = $notification->id;
        $datamailobject->plugintype = $notification->plugintype;
        $datamailobject->pluginname = $notification->pluginname;
        $datamailobject->moduleid = 1;

        $datamailobject->cpd_name = $data->name;
        $datamailobject->cpd_user_fullname = $touser->firstname;
        $datamailobject->cpd_module_link = $data->cpd_module_link;


        $datamailobject->body = $notification->body;
        $datamailobject->subject = $notification->subject;
        $datamailobject->arabic_body = $notification->arabic_body;
        $datamailobject->arabic_subject = $notification->arabic_subject;
        $datamailobject->cpdid = $cpdinstance->id;
        $datamailobject->touserid = $touser->id;
        $datamailobject->fromuserid = $fromuser->id;
        $datamailobject->teammemberid = 0;

        $this->log_email_notification($touser, $fromuser, $datamailobject);
    }
    public function cpd_expiration_lt_180days_and_gt_90_days($lastruntime,$nextruntime,$timenow)
    {
        global $DB, $USER;
        $from =null;        
        // $from = optional_param('date', 0, PARAM_INT);        
        $fromdate = ($from !== null) ? $from : userdate(time(), '%Y-%m-%d');
        //$fromdate = ($from !== null) ? $from : date('Y-m-d');
        // echo $fromdate."optional param";exit;
        // $fromdate = date('Y-m-d');
        $data = new stdClass();
        $sql = "SELECT *  
                FROM {tool_certificate_issues} AS tci
                INNER JOIN {local_cpd} AS c ON c.examid =tci.moduleid         
                INNER JOIN {local_exams} AS exm ON exm.id =tci.moduleid 
                WHERE  date(from_unixtime(expires+(60*60*24*180)))='$fromdate'
                    AND (expires-(60*60*24*180)) >= $lastruntime
                    AND (expires-(60*60*24*180)) <= $timenow";
        // echo $sql;
        $exist_recordid = $DB->get_records_sql($sql);
        // print_r($exist_recordid);exit;
        if (!empty($exist_recordid)) {
            foreach($exist_recordid as $key =>$certficate_issue){
                $from = date_create($fromdate);
                $to = date_create(userdate($certficate_issue->expires, '%Y-%m-%d'));
                //$to = date_create(date('Y-m-d', $certficate_issue->expires));
                $remaining_days = date_diff($to, $from);
                $remaining_exp_days = $remaining_days->days;
                $data->certificatename = $certficate_issue->exam;
                if ($remaining_exp_days > 0) {
                    // if ($remaining_exp_days < 180 && $remaining_exp_days > 90) {
                        $sql = "SELECT lnt.* 
                                FROM {local_notification_type} AS lnt
                                WHERE shortname IN ('cpd_expiration_lt_180days_and_gt_90_days') ";
                        $notificationtype = $DB->get_record_sql($sql);
                        $user = $DB->get_record('user', array('id' =>$certficate_issue->userid, 'deleted' => 0));
                        $this->cpd_notification('cpd_expiration_lt_180days_and_gt_90_days', $touser = $user, $fromuser = get_admin(), $notificationtype, $waitinglistid = 0, $data);
                    // } 
                }
            }
            
        } 
    }
    public function cpd_expiration_lt_90_days($lastruntime,$nextruntime,$timenow)
    {
        global $DB, $USER;
        $from =null;
        $data = new stdClass(); 
        $fromdate = ($from !== null) ? $from : userdate(time(), '%Y-%m-%d');          
        //$fromdate = ($from !== null) ? $from : date('Y-m-d');          
        $sql = "SELECT *  
                FROM {tool_certificate_issues} as tci
                INNER JOIN {local_cpd} as c on c.examid =tci.moduleid         
                INNER JOIN {local_exams} as exm on exm.id =tci.moduleid 
                WHERE  date(from_unixtime(expires+(60*60*24*90)))='$fromdate'
                    AND (expires-(60*60*24*90)) >= $lastruntime
                    AND (expires-(60*60*24*90)) <= $timenow";
        // echo $sql;exit; 
        $exist_recordid = $DB->get_records_sql($sql);
        if (!empty($exist_recordid)) {
            foreach($exist_recordid as $key =>$certficate_issue){
                $from = date_create($fromdate);
                $to = date_create(userdate($certficate_issue->expires, '%Y-%m-%d'));
                $remaining_days = date_diff($to, $from);
                $remaining_exp_days = $remaining_days->days;
                $data->certificatename = $certficate_issue->exam;
                // echo $remaining_exp_days."Remaining days";
                if ($remaining_exp_days > 0) {
                    // if ( $remaining_exp_days < 90) {
                        $sql = "SELECT lnt.* 
                                FROM {local_notification_type} AS lnt
                                WHERE shortname IN ('cpd_expiration_lt_90_days') ";
                        $notificationtype = $DB->get_record_sql($sql);
                        $user = $DB->get_record('user', array('id' =>$certficate_issue->userid, 'deleted' => 0));
                        $this->cpd_notification('cpd_expiration_lt_90_days', $touser = $user, $fromuser = get_admin(), $notificationtype, $waitinglistid = 0, $data);
                    // } 
                }
            }
            
        } 
    }
}
