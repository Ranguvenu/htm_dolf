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
 * @package local_questionbank
 */
namespace local_questionbank;

class notification extends \local_notifications\notification {
	public $db;
	public $user;
	// public function __construct($db=null, $user=null){
	// 	global $DB, $USER;
	// 	$this->db = $db ? $db :$DB;
	// 	$this->user = $user ? $user :$USER;
	// }
    public function get_string_identifiers($emailtype){
        switch($emailtype){
            case 'questionbank_workshop_created':
                $strings = "[FullName],[WorkshopName],[QuestionBankName],[WorkshopDate],[WorkshopTime]";
                break;

            case 'questionbank_workshop_updated':
                $strings ="[FullName],[WorkshopName],[QuestionBankName],[WorkshopDate],[WorkshopTime]";
                break;
            
            case 'questionbank_assign_expert':
                $strings ="[FullName],[ExpertName],[QuestionBankName]";
                break; 
            case 'questionbank_assign_exam_official':
                $strings ="[FullName],[ExamofficialName],[QuestionBankName]";
                break;   
            case 'questionbank_assign_reviewer':
                $strings ="[FullName],[ReviewerName],[QuestionBankName],[QuestionText]";
                break;  
            case 'questionbank_question_under_review':
                $strings ="[QuestionText], [FullName]";
                break;  
            case 'questionbank_question_reviewed':
                $strings ="[QuestionText],[ReviewerName],[FullName]";
                break;  
            case 'questionbank_question_added':
                $strings ="[FullName],[QuestionText],[QuestionBank]";
                break;  
            case 'questionbank_onchange':
                $strings ="[RelatedModuleName], [RelatedModulesLink],[FullName]";
                break;  
            case 'questionbank_cancel':
                $strings ="[RelatedModuleName], [FullName]";
                break;  
            case 'questionbank_reschedule':
                $strings ="[FullName], [RelatedModuleName],[ProgramLink]";
                break;  
        }

        return $strings;
    }
    public function questionbank_notification($emailtype, $touser, $fromuser, $questionbankinstance,$row,$waitinglistid=0){
        if($notification = $this->get_existing_notification($questionbankinstance, $emailtype)){
          
            $functionname = 'send_'.$emailtype.'_notification';

            $this->$functionname($questionbankinstance, $touser, $fromuser, $emailtype, $notification,$row,$waitinglistid);  //$this->send_questionbank_notification($questionbankinstance, $touser, $fromuser, $emailtype, $notification,$waitinglistid);
        }
        
    }
	public function send_questionbank_workshop_created_notification($questionbankinstance, $touser, $fromuser, $emailtype, $notification,$row,$waitinglistid=0){
        global $PAGE,$DB;
        $datamailobject = new \stdClass();

        $datamailobject->emailtype = $emailtype;
        $datamailobject->notification_infoid = $notification->id;
        $datamailobject->plugintype = $notification->plugintype;
        $datamailobject->pluginname = $notification->pluginname;
        $datamailobject->moduleid = 1;


        $datamailobject->WorkshopName =$questionbankinstance['WorkshopName'];
        $datamailobject->QuestionBankName =$questionbankinstance['QuestionBankName'];
        $datamailobject->WorkshopDate =$questionbankinstance['WorkshopDate'];
        $datamailobject->WorkshopTime =$questionbankinstance['WorkshopTime'];
  

        $datamailobject->body = $notification->body;
        $datamailobject->subject = $notification->subject;
        $datamailobject->arabic_body = $notification->arabic_body;
        $datamailobject->arabic_subject = $notification->arabic_subject;
        $datamailobject->fromuserid = $fromuser->id;
        $datamailobject->teammemberid = 0;
        $roleusers = $this->getsystemlevel_role_users('examofficial', 0);
        if ($roleusers) {
            foreach ($roleusers as $roleuser) {
                $localuserrecord = $DB->get_record('local_users',['userid'=> $roleuser->id]);
                $fullname = ($localuserrecord)? (($roleuser->lang == 'ar') ? $localuserrecord->firstnamearabic.' '.$localuserrecord->middlenamearabic.' '.$localuserrecord->thirdnamearabic.' '.$localuserrecord->lastnamearabic  : $localuserrecord->firstname.' '.$localuserrecord->middlenameen.' '.$localuserrecord->thirdnameen.' '.$localuserrecord->lastname) :fullname($DB->get_record('user',array('id'=>(int)$roleuser->id)));
                $datamailobject->FullName = $fullname;
               // print_r($datamailobject);exit;
                $this->log_email_notification($roleuser, $fromuser, $datamailobject);
            }
        }
     
	}
    public function send_questionbank_workshop_updated_notification($questionbankinstance, $touser, $fromuser, $emailtype, $notification,$row,$waitinglistid=0){
        global $PAGE,$DB;
        $datamailobject = new \stdClass();

        $datamailobject->emailtype = $emailtype;
        $datamailobject->notification_infoid = $notification->id;
        $datamailobject->plugintype = $notification->plugintype;
        $datamailobject->pluginname = $notification->pluginname;
        $datamailobject->moduleid = 1;
        $datamailobject->WorkshopName =$questionbankinstance['WorkshopName'];
        $datamailobject->QuestionBankName =$questionbankinstance['QuestionBankName'];
        $datamailobject->WorkshopDate =$questionbankinstance['WorkshopDate'];
        $datamailobject->WorkshopTime =$questionbankinstance['WorkshopTime'];
        $datamailobject->body = $notification->body;
        $datamailobject->subject = $notification->subject;
        $datamailobject->arabic_body = $notification->arabic_body;
        $datamailobject->arabic_subject = $notification->arabic_subject;
        $datamailobject->fromuserid = $fromuser->id;
        $datamailobject->teammemberid = 0;
        $roleusers = $this->getsystemlevel_role_users('examofficial', 0);
        if ($roleusers) {
            foreach ($roleusers as $roleuser) {
                $localuserrecord = $DB->get_record('local_users',['userid'=> $roleuser->id]);
                $fullname = ($localuserrecord)? (($roleuser->lang == 'ar') ? $localuserrecord->firstnamearabic.' '.$localuserrecord->middlenamearabic.' '.$localuserrecord->thirdnamearabic.' '.$localuserrecord->lastnamearabic  : $localuserrecord->firstname.' '.$localuserrecord->middlenameen.' '.$localuserrecord->thirdnameen.' '.$localuserrecord->lastname) :fullname($DB->get_record('user',array('id'=>(int)$roleuser->id)));
                $datamailobject->FullName = $fullname;
             //  print_r($datamailobject);exit;
                $this->log_email_notification($roleuser, $fromuser, $datamailobject);
            }
        }

        //$this->log_email_notification($touser, $fromuser, $datamailobject);
     
    }
    public function send_questionbank_assign_expert_notification($questionbankinstance, $touser, $fromuser, $emailtype, $notification,$row,$waitinglistid=0){
       global $PAGE,$DB;
        $datamailobject = new \stdClass();

        $datamailobject->emailtype = $emailtype;
        $datamailobject->notification_infoid = $notification->id;
        $datamailobject->plugintype = $notification->plugintype;
        $datamailobject->pluginname = $notification->pluginname;
        $datamailobject->moduleid = 1;


        $datamailobject->ExpertName = $questionbankinstance['ExpertName'];
        $datamailobject->QuestionBankName = $questionbankinstance['QuestionBankName'];
  

        $datamailobject->body = $notification->body;
        $datamailobject->subject = $notification->subject;
        $datamailobject->arabic_body = $notification->arabic_body;
        $datamailobject->arabic_subject = $notification->arabic_subject;
        $datamailobject->questionbankid = $questionbankinstance->id;
        $datamailobject->touserid = $touser->id;
        $datamailobject->fromuserid = $fromuser->id;
        $datamailobject->teammemberid = 0;
        $roleusers = $this->getsystemlevel_role_users('expert', 0);
        if ($roleusers) {
            foreach ($roleusers as $roleuser) {
                $localuserrecord = $DB->get_record('local_users',['userid'=> $roleuser->id]);
                $fullname = ($localuserrecord)? (($roleuser->lang == 'ar') ? $localuserrecord->firstnamearabic.' '.$localuserrecord->middlenamearabic.' '.$localuserrecord->thirdnamearabic.' '.$localuserrecord->lastnamearabic  : $localuserrecord->firstname.' '.$localuserrecord->middlenameen.' '.$localuserrecord->thirdnameen.' '.$localuserrecord->lastname) :fullname($DB->get_record('user',array('id'=>(int)$roleuser->id)));
                $datamailobject->FullName = $fullname;
             //  print_r($datamailobject);exit;
                $this->log_email_notification($roleuser, $fromuser, $datamailobject);
            }
        }

        //$this->log_email_notification($touser, $fromuser, $datamailobject);
     
    }
    public function send_questionbank_assign_exam_official_notification($questionbankinstance, $touser, $fromuser, $emailtype, $notification,$row,$waitinglistid=0){
        global $PAGE,$DB;
        $datamailobject = new \stdClass();

        $datamailobject->emailtype = $emailtype;
        $datamailobject->notification_infoid = $notification->id;
        $datamailobject->plugintype = $notification->plugintype;
        $datamailobject->pluginname = $notification->pluginname;
        $datamailobject->moduleid = 1;


        $datamailobject->QuestionBankName = $questionbankinstance['QuestionBankName'];
        $datamailobject->ExamofficialName = $questionbankinstance['ExamofficialName'];
  

        $datamailobject->body = $notification->body;
        $datamailobject->subject = $notification->subject;
        $datamailobject->arabic_body = $notification->arabic_body;
        $datamailobject->arabic_subject = $notification->arabic_subject;
        $datamailobject->questionbankid = $questionbankinstance->id;
        $datamailobject->touserid = $touser->id;
        $datamailobject->fromuserid = $fromuser->id;
        $datamailobject->teammemberid = 0;
        $roleusers = $this->getsystemlevel_role_users('examofficial', 0);
        if ($roleusers) {
            foreach ($roleusers as $roleuser) {
                $localuserrecord = $DB->get_record('local_users',['userid'=> $roleuser->id]);
                $fullname = ($localuserrecord)? (($roleuser->lang == 'ar') ? $localuserrecord->firstnamearabic.' '.$localuserrecord->middlenamearabic.' '.$localuserrecord->thirdnamearabic.' '.$localuserrecord->lastnamearabic  : $localuserrecord->firstname.' '.$localuserrecord->middlenameen.' '.$localuserrecord->thirdnameen.' '.$localuserrecord->lastname) :fullname($DB->get_record('user',array('id'=>(int)$roleuser->id)));
                $datamailobject->FullName = $fullname;
            // print_r($datamailobject);exit;
                $this->log_email_notification($roleuser, $fromuser, $datamailobject);
            }
        }

      //  $this->log_email_notification($touser, $fromuser, $datamailobject);
     
    }
    public function send_questionbank_assign_reviewer_notification($questionbankinstance, $touser, $fromuser, $emailtype, $notification,$row,$waitinglistid=0){
        global $PAGE,$DB;
        $datamailobject = new \stdClass();

        $datamailobject->emailtype = $emailtype;
        $datamailobject->notification_infoid = $notification->id;
        $datamailobject->plugintype = $notification->plugintype;
        $datamailobject->pluginname = $notification->pluginname;
        $datamailobject->moduleid = 1;


        $datamailobject->QuestionBankName = $questionbankinstance['QuestionBankName'];
        $datamailobject->QuestionText = $questionbankinstance['QuestionText'];
        $datamailobject->ReviewerName = $questionbankinstance['ReviewerName'];

        $datamailobject->body = $notification->body;
        $datamailobject->subject = $notification->subject;
        $datamailobject->arabic_body = $notification->arabic_body;
        $datamailobject->arabic_subject = $notification->arabic_subject;
        $datamailobject->questionbankid = $questionbankinstance->id;
        $datamailobject->touserid = $touser->id;
        $datamailobject->fromuserid = $fromuser->id;
        $datamailobject->teammemberid = 0;
        if ($touser) {
                $localuserrecord = $DB->get_record('local_users',['userid'=> $roleuser->id]);
                $fullname = ($localuserrecord)? (($roleuser->lang == 'ar') ? $localuserrecord->firstnamearabic.' '.$localuserrecord->middlenamearabic.' '.$localuserrecord->thirdnamearabic.' '.$localuserrecord->lastnamearabic  : $localuserrecord->firstname.' '.$localuserrecord->middlenameen.' '.$localuserrecord->thirdnameen.' '.$localuserrecord->lastname) :fullname($DB->get_record('user',array('id'=>(int)$roleuser->id)));
                $datamailobject->FullName = $fullname;
                $this->log_email_notification($touser, $fromuser, $datamailobject);
        }
     
    }
    public function send_questionbank_question_under_review_notification($questionbankinstance, $touser, $fromuser, $emailtype, $notification,$row,$waitinglistid=0){
        global $PAGE,$DB;
        $datamailobject = new \stdClass();

        $datamailobject->emailtype = $emailtype;
        $datamailobject->notification_infoid = $notification->id;
        $datamailobject->plugintype = $notification->plugintype;
        $datamailobject->pluginname = $notification->pluginname;
        $datamailobject->moduleid = 1;
        $datamailobject->QuestionText = $questionbankinstance['QuestionText'];
        $datamailobject->body = $notification->body;
        $datamailobject->subject = $notification->subject;
        $datamailobject->arabic_body = $notification->arabic_body;
        $datamailobject->arabic_subject = $notification->arabic_subject;
        $datamailobject->questionbankid = $questionbankinstance->id;
        $datamailobject->touserid = $touser->id;
        $datamailobject->fromuserid = $fromuser->id;
        $datamailobject->teammemberid = 0;

        $roleusers = $this->getsystemlevel_role_users('expert', 0);
        if ($roleusers) {
            foreach ($roleusers as $roleuser) {
                $localuserrecord = $DB->get_record('local_users',['userid'=> $roleuser->id]);
                $fullname = ($localuserrecord)? (($roleuser->lang == 'ar') ? $localuserrecord->firstnamearabic.' '.$localuserrecord->middlenamearabic.' '.$localuserrecord->thirdnamearabic.' '.$localuserrecord->lastnamearabic  : $localuserrecord->firstname.' '.$localuserrecord->middlenameen.' '.$localuserrecord->thirdnameen.' '.$localuserrecord->lastname) :fullname($DB->get_record('user',array('id'=>(int)$roleuser->id)));
                $datamailobject->FullName = $fullname;
            // print_r($datamailobject);exit;
                $this->log_email_notification($roleuser, $fromuser, $datamailobject);
            }
        }
        $roleusers = $this->getsystemlevel_role_users('examofficial', 0);
        if ($roleusers) {
            foreach ($roleusers as $roleuser) {
                $localuserrecord = $DB->get_record('local_users',['userid'=> $roleuser->id]);
                $fullname = ($localuserrecord)? (($roleuser->lang == 'ar') ? $localuserrecord->firstnamearabic.' '.$localuserrecord->middlenamearabic.' '.$localuserrecord->thirdnamearabic.' '.$localuserrecord->lastnamearabic  : $localuserrecord->firstname.' '.$localuserrecord->middlenameen.' '.$localuserrecord->thirdnameen.' '.$localuserrecord->lastname) :fullname($DB->get_record('user',array('id'=>(int)$roleuser->id)));
                $datamailobject->FullName = $fullname;
            // print_r($datamailobject);exit;
                $this->log_email_notification($roleuser, $fromuser, $datamailobject);
            }
        }
     
    }
    public function send_questionbank_question_reviewed_notification($questionbankinstance, $touser, $fromuser, $emailtype, $notification,$row,$waitinglistid=0){
        global $PAGE,$DB;
        $datamailobject = new \stdClass();

        $datamailobject->emailtype = $emailtype;
        $datamailobject->notification_infoid = $notification->id;
        $datamailobject->plugintype = $notification->plugintype;
        $datamailobject->pluginname = $notification->pluginname;
        $datamailobject->moduleid = 1;


        $datamailobject->QuestionText = $questionbankinstance['QuestionText'];
        $datamailobject->ReviewerName = $questionbankinstance['ReviewerName'];
  

        $datamailobject->body = $notification->body;
        $datamailobject->subject = $notification->subject;
        $datamailobject->arabic_body = $notification->arabic_body;
        $datamailobject->arabic_subject = $notification->arabic_subject;
        $datamailobject->questionbankid = $questionbankinstance->id;
        $datamailobject->touserid = $touser->id;
        $datamailobject->fromuserid = $fromuser->id;
        $datamailobject->teammemberid = 0;

        $roleusers = $this->getsystemlevel_role_users('expert', 0);
        if ($roleusers) {
            foreach ($roleusers as $roleuser) {
                $localuserrecord = $DB->get_record('local_users',['userid'=> $roleuser->id]);
                $fullname = ($localuserrecord)? (($roleuser->lang == 'ar') ? $localuserrecord->firstnamearabic.' '.$localuserrecord->middlenamearabic.' '.$localuserrecord->thirdnamearabic.' '.$localuserrecord->lastnamearabic  : $localuserrecord->firstname.' '.$localuserrecord->middlenameen.' '.$localuserrecord->thirdnameen.' '.$localuserrecord->lastname) :fullname($DB->get_record('user',array('id'=>(int)$roleuser->id)));
                $datamailobject->FullName = $fullname;
            // print_r($datamailobject);exit;
                $this->log_email_notification($roleuser, $fromuser, $datamailobject);
            }
        }
        $roleusers = $this->getsystemlevel_role_users('examofficial', 0);
        if ($roleusers) {
            foreach ($roleusers as $roleuser) {
                $localuserrecord = $DB->get_record('local_users',['userid'=> $roleuser->id]);
                $fullname = ($localuserrecord)? (($roleuser->lang == 'ar') ? $localuserrecord->firstnamearabic.' '.$localuserrecord->middlenamearabic.' '.$localuserrecord->thirdnamearabic.' '.$localuserrecord->lastnamearabic  : $localuserrecord->firstname.' '.$localuserrecord->middlenameen.' '.$localuserrecord->thirdnameen.' '.$localuserrecord->lastname) :fullname($DB->get_record('user',array('id'=>(int)$roleuser->id)));
                $datamailobject->FullName = $fullname;
            // print_r($datamailobject);exit;
                $this->log_email_notification($roleuser, $fromuser, $datamailobject);
            }
        }
     
    }
    public function send_questionbank_question_added_notification($questionbankinstance, $touser, $fromuser, $emailtype, $notification,$row,$waitinglistid=0){
       global $PAGE,$DB;
        $datamailobject = new \stdClass();

        $datamailobject->emailtype = $emailtype;
        $datamailobject->notification_infoid = $notification->id;
        $datamailobject->plugintype = $notification->plugintype;
        $datamailobject->pluginname = $notification->pluginname;
        $datamailobject->moduleid = 1;


        $datamailobject->QuestionBank = $questionbankinstance['QuestionBank'];
        $datamailobject->QuestionText = $questionbankinstance['QuestionText'];
  

        $datamailobject->body = $notification->body;
        $datamailobject->subject = $notification->subject;
        $datamailobject->arabic_body = $notification->arabic_body;
        $datamailobject->arabic_subject = $notification->arabic_subject;
        $datamailobject->questionbankid = $questionbankinstance->id;
        $datamailobject->touserid = $touser->id;
        $datamailobject->fromuserid = $fromuser->id;
        $datamailobject->teammemberid = 0;

        $roleusers = $this->getsystemlevel_role_users('expert', 0);
        if ($roleusers) {
            foreach ($roleusers as $roleuser) {
                $localuserrecord = $DB->get_record('local_users',['userid'=> $roleuser->id]);
                $fullname = ($localuserrecord)? (($roleuser->lang == 'ar') ? $localuserrecord->firstnamearabic.' '.$localuserrecord->middlenamearabic.' '.$localuserrecord->thirdnamearabic.' '.$localuserrecord->lastnamearabic  : $localuserrecord->firstname.' '.$localuserrecord->middlenameen.' '.$localuserrecord->thirdnameen.' '.$localuserrecord->lastname) :fullname($DB->get_record('user',array('id'=>(int)$roleuser->id)));
                $datamailobject->FullName = $fullname;
            // print_r($datamailobject);exit;
                $this->log_email_notification($roleuser, $fromuser, $datamailobject);
            }
        }
        $roleusers = $this->getsystemlevel_role_users('examofficial', 0);
        if ($roleusers) {
            foreach ($roleusers as $roleuser) {
                $localuserrecord = $DB->get_record('local_users',['userid'=> $roleuser->id]);
                $fullname = ($localuserrecord)? (($roleuser->lang == 'ar') ? $localuserrecord->firstnamearabic.' '.$localuserrecord->middlenamearabic.' '.$localuserrecord->thirdnamearabic.' '.$localuserrecord->lastnamearabic  : $localuserrecord->firstname.' '.$localuserrecord->middlenameen.' '.$localuserrecord->thirdnameen.' '.$localuserrecord->lastname) :fullname($DB->get_record('user',array('id'=>(int)$roleuser->id)));
                $datamailobject->FullName = $fullname;
            // print_r($datamailobject);exit;
                $this->log_email_notification($roleuser, $fromuser, $datamailobject);
            }
        }
     
    }
    public function send_questionbank_onchange_notification($questionbankinstance, $touser, $fromuser, $emailtype, $notification,$questionbankinfo,$waitinglistid=0){
        global $PAGE,$DB;
        $datamailobject = new \stdClass();

        $datamailobject->emailtype = $emailtype;
        $datamailobject->notification_infoid = $notification->id;
        $datamailobject->plugintype = $notification->plugintype;
        $datamailobject->pluginname = $notification->pluginname;
        $datamailobject->moduleid = 1;
        $datamailobject->RelatedModuleName = $questionbankinstance['RelatedModuleName'];
        $datamailobject->RelatedModulesLink = $questionbankinstance['RelatedModulesLink'];
        $datamailobject->body = $notification->body;
        $datamailobject->subject = $notification->subject;
        $datamailobject->arabic_body = $notification->arabic_body;
        $datamailobject->arabic_subject = $notification->arabic_subject;
        $datamailobject->fromuserid = $fromuser->id;
        $datamailobject->teammemberid = 0;
        if ($touser) {
                foreach ($touser as $tousers) {
                    $localuserrecord = $DB->get_record('local_users',['userid'=> $roleuser->id]);
                   $fullname = ($localuserrecord)? (($roleuser->lang == 'ar') ? $localuserrecord->firstnamearabic.' '.$localuserrecord->middlenamearabic.' '.$localuserrecord->thirdnamearabic.' '.$localuserrecord->lastnamearabic  : $localuserrecord->firstname.' '.$localuserrecord->middlenameen.' '.$localuserrecord->thirdnameen.' '.$localuserrecord->lastname) :fullname($DB->get_record('user',array('id'=>(int)$roleuser->id)));
                $datamailobject->FullName = $fullname;
                 //  print_r($datamailobject);exit;
                    $this->log_email_notification($tousers, $fromuser, $datamailobject);
                }
            }

     
    }
    public function send_questionbank_cancel_notification($questionbankinstance, $touser, $fromuser, $emailtype, $notification,$questionbankinfo,$waitinglistid=0){
        global $PAGE,$DB;
        $datamailobject = new \stdClass();
        $datamailobject->emailtype = $emailtype;
        $datamailobject->notification_infoid = $notification->id;
        $datamailobject->plugintype = $notification->plugintype;
        $datamailobject->pluginname = $notification->pluginname;
        $datamailobject->moduleid = 1;
        $datamailobject->RelatedModuleName = $questionbankinstance['RelatedModuleName'];
        $datamailobject->body = $notification->body;
        $datamailobject->subject = $notification->subject;
        $datamailobject->arabic_body = $notification->arabic_body;
        $datamailobject->arabic_subject = $notification->arabic_subject;
        $datamailobject->fromuserid = $fromuser->id;
        $datamailobject->teammemberid = 0;

        if ($touser) {
            foreach ($touser as $tousers) {
                $localuserrecord = $DB->get_record('local_users',['userid'=> $roleuser->id]);
                $fullname = ($localuserrecord)? (($roleuser->lang == 'ar') ? $localuserrecord->firstnamearabic.' '.$localuserrecord->middlenamearabic.' '.$localuserrecord->thirdnamearabic.' '.$localuserrecord->lastnamearabic  : $localuserrecord->firstname.' '.$localuserrecord->middlenameen.' '.$localuserrecord->thirdnameen.' '.$localuserrecord->lastname) :fullname($DB->get_record('user',array('id'=>(int)$roleuser->id)));
                $datamailobject->FullName = $fullname;
             //  print_r($datamailobject);exit;
                $this->log_email_notification($tousers, $fromuser, $datamailobject);
            }
        }
     
    }
    public function send_questionbank_reschedule_notification($questionbankinstance, $touser, $fromuser, $emailtype, $notification,$questionbankinfo,$waitinglistid=0){
        global $PAGE,$DB;
        $datamailobject = new \stdClass();

        $datamailobject->emailtype = $emailtype;
        $datamailobject->notification_infoid = $notification->id;
        $datamailobject->plugintype = $notification->plugintype;
        $datamailobject->pluginname = $notification->pluginname;
        $datamailobject->moduleid = 1;
        $datamailobject->RelatedModuleName = $questionbankinstance['RelatedModuleName'];
        $datamailobject->ProgramLink = $questionbankinstance['ProgramLink'];
        $datamailobject->body = $notification->body;
        $datamailobject->subject = $notification->subject;
        $datamailobject->arabic_body = $notification->arabic_body;
        $datamailobject->arabic_subject = $notification->arabic_subject;
        $datamailobject->fromuserid = $fromuser->id;
        $datamailobject->teammemberid = 0;

        if ($touser) {
            foreach ($touser as $tousers) {
                $localuserrecord = $DB->get_record('local_users',['userid'=> $roleuser->id]);
                $fullname = ($localuserrecord)? (($roleuser->lang == 'ar') ? $localuserrecord->firstnamearabic.' '.$localuserrecord->middlenamearabic.' '.$localuserrecord->thirdnamearabic.' '.$localuserrecord->lastnamearabic  : $localuserrecord->firstname.' '.$localuserrecord->middlenameen.' '.$localuserrecord->thirdnameen.' '.$localuserrecord->lastname) :fullname($DB->get_record('user',array('id'=>(int)$roleuser->id)));
                $datamailobject->FullName = $fullname;
             //  print_r($datamailobject);exit;
                $this->log_email_notification($tousers, $fromuser, $datamailobject);
            }
        }
     
    }
    

}
