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
 * @package local_learningtracks
 **/
namespace local_learningtracks;

class notification extends \local_notifications\notification {
	public $db;
	public $user;
    public function get_string_identifiers($emailtype){
        switch($emailtype){
            case 'learningtrack_create':
                $strings = "[learningTrackName],[FullName],
                            [created]";
                break;

            case 'learningtrack_update':
                $strings ="[learningTrackName], [FullName],
                [updated]";
                break;
            
            case 'learningtrack_enroll':
                $strings ="[learningTrackName], [FullName]";
                break; 
            case 'learningtrack_completed':
                $strings ="[FullName],[learningTrackName]";
                break;   
            case 'learningtrack_onchange':
                $strings ="[RelatedModuleName],[RelatedModulesLink],[FullName]
                                ";
                break;   
            case 'learningtrack_cancel':
                $strings ="[RelatedModuleName],[FullName]";
                break;   
            case 'learningtrack_reschedule':
                $strings ="[learningtrack_completed],[learningtrack_username],[learningtrack_timecompleted]
                                        ,[learningtrack_coursename]";
                break;   
        }

        return $strings;
    }
    public function learningtracks_notification($emailtype, $touser, $fromuser, $learningtracksinstance,$row,$waitinglistid=0){
        if($notification = $this->get_existing_notification($learningtracksinstance, $emailtype)){
            $functionname = 'send_'.$emailtype.'_notification';

            $this->$functionname($learningtracksinstance, $touser, $fromuser, $emailtype, $notification,$row,$waitinglistid);
        }
        
    }
	public function send_learningtrack_create_notification($learningtracksinstance, $touser, $fromuser, $emailtype, $notification,$row,$waitinglistid=0){
        global $PAGE,$DB;
        $datamailobject = new \stdClass();

        $datamailobject->emailtype = $emailtype;
        $datamailobject->notification_infoid = $notification->id;
        $datamailobject->plugintype = $notification->plugintype;
        $datamailobject->pluginname = $notification->pluginname;
        $datamailobject->learningTrackName = $learningtracksinstance['learningTrackName'];
        $datamailobject->created = $learningtracksinstance['created'];
        $datamailobject->body = $notification->body;
        $datamailobject->subject = $notification->subject;
        $datamailobject->arabic_body = $notification->arabic_body;
        $datamailobject->arabic_subject = $notification->arabic_subject;
        $datamailobject->moduleid = 1;
        $datamailobject->fromuserid = $fromuser->id;
        $datamailobject->teammemberid = 0;
        $roleusers = $this->getsystemlevel_role_users('to', 0);
        if ($roleusers) {
            foreach ($roleusers as $roleuser) {
                $localuserrecord = $DB->get_record('local_users',['userid'=> $roleuser->id]);
                $fullname = ($localuserrecord)? (($roleuser->lang == 'ar') ? $localuserrecord->firstnamearabic.' '.$localuserrecord->middlenamearabic.' '.$localuserrecord->thirdnamearabic.' '.$localuserrecord->lastnamearabic  : $localuserrecord->firstname.' '.$localuserrecord->middlenameen.' '.$localuserrecord->thirdnameen.' '.$localuserrecord->lastname) :fullname($DB->get_record('user',array('id'=>(int)$roleuser->id)));
                $datamailobject->FullName = $fullname;
                //$this->log_email_notification($roleuser, $fromuser, $datamailobject);
            }
        }
     
	}
    public function send_learningtrack_update_notification($learningtracksinstance, $touser, $fromuser, $emailtype, $notification,$row,$waitinglistid=0){
        global $PAGE,$DB;
        $datamailobject = new \stdClass();

        $datamailobject->emailtype = $emailtype;
        $datamailobject->notification_infoid = $notification->id;
        $datamailobject->plugintype = $notification->plugintype;
        $datamailobject->pluginname = $notification->pluginname;


        $datamailobject->learningTrackName = $learningtracksinstance['learningTrackName'];
        $datamailobject->updated = $learningtracksinstance['updated'];
        $datamailobject->body = $notification->body;
        $datamailobject->subject = $notification->subject;
        $datamailobject->arabic_body = $notification->arabic_body;
        $datamailobject->arabic_subject = $notification->arabic_subject;
        $datamailobject->moduleid = 1;
        $datamailobject->fromuserid = $fromuser->id;
        $datamailobject->teammemberid = 0;
        $roleusers = $this->getsystemlevel_role_users('to', 0);
        if ($roleusers) {
            foreach ($roleusers as $roleuser) {
                $localuserrecord = $DB->get_record('local_users',['userid'=> $roleuser->id]);
                $fullname = ($localuserrecord)? (($roleuser->lang == 'ar') ? $localuserrecord->firstnamearabic.' '.$localuserrecord->middlenamearabic.' '.$localuserrecord->thirdnamearabic.' '.$localuserrecord->lastnamearabic  : $localuserrecord->firstname.' '.$localuserrecord->middlenameen.' '.$localuserrecord->thirdnameen.' '.$localuserrecord->lastname) :fullname($DB->get_record('user',array('id'=>(int)$roleuser->id)));
                $datamailobject->FullName = $fullname;
               // $this->log_email_notification($roleuser, $fromuser, $datamailobject);
            }
        }

     
    }
    public function send_learningtrack_enroll_notification($learningtracksinstance, $touser, $fromuser, $emailtype, $notification,$waitinglistid=0){
        global $PAGE;
        $datamailobject = new \stdClass();

        $datamailobject->emailtype = $emailtype;
        $datamailobject->notification_infoid = $notification->id;
        $datamailobject->plugintype = $notification->plugintype;
        $datamailobject->pluginname = $notification->pluginname;
        $datamailobject->learningTrackName = $learningtracksinstance['learningTrackName'];
        $datamailobject->body = $notification->body;
        $datamailobject->subject = $notification->subject;
        $datamailobject->arabic_body = $notification->arabic_body;
        $datamailobject->arabic_subject = $notification->arabic_subject;
       // $datamailobject->learningtracksid = $learningtracksinstance->id;
       // $datamailobject->touserid = $touser->id;
        $datamailobject->moduleid = 1;
        $datamailobject->fromuserid = $fromuser->id;
        $datamailobject->teammemberid = 0;

        if ($touser) {
             $datamailobject->FullName = $touser->firstname.' '.$touser->lastname;
             $this->log_email_notification($touser, $fromuser, $datamailobject);
             }
            
     
    }
    public function send_learningtrack_completed_notification($learningtracksinstance, $touser, $fromuser, $emailtype, $notification,$row,$waitinglistid=0){
        global $PAGE;
        $datamailobject = new \stdClass();

        $datamailobject->emailtype = $emailtype;
        $datamailobject->notification_infoid = $notification->id;
        $datamailobject->plugintype = $notification->plugintype;
        $datamailobject->pluginname = $notification->pluginname;
        $datamailobject->learningTrackName = $row['learningTrackName'];
        $datamailobject->body = $notification->body;
        $datamailobject->subject = $notification->subject;
        $datamailobject->arabic_body = $notification->arabic_body;
        $datamailobject->arabic_subject = $notification->arabic_subject;
        $datamailobject->moduleid = 1;
        $datamailobject->fromuserid = $fromuser->id;
        $datamailobject->teammemberid = 0;

        if ($touser) {
            foreach ($touser as $tousers) 
            {
            $datamailobject->FullName = $tousers->firstname.' '.$tousers->lastname;
             $this->log_email_notification($tousers, $fromuser, $datamailobject);
             }
            }
     
    }
    public function send_learningtrack_onchange_notification($learningtracksinstance, $touser, $fromuser, $emailtype, $notification,$row1,$waitinglistid=0){
        global $PAGE;
        $datamailobject = new \stdClass();

        $datamailobject->emailtype = $emailtype;
        $datamailobject->notification_infoid = $notification->id;
        $datamailobject->plugintype = $notification->plugintype;
        $datamailobject->pluginname = $notification->pluginname;
        $datamailobject->RelatedModuleName = $learningtracksinstance['RelatedModuleName'];
        $datamailobject->RelatedModulesLink = $learningtracksinstance['RelatedModulesLink'];

        $datamailobject->body = $notification->body;
        $datamailobject->subject = $notification->subject;
        $datamailobject->arabic_body = $notification->arabic_body;
        $datamailobject->arabic_subject = $notification->arabic_subject;
        $datamailobject->moduleid = 1;
        $datamailobject->fromuserid = $fromuser->id;
        $datamailobject->teammemberid = 0;

        if ($touser) {
            foreach ($touser as $tousers) 
            {
            $datamailobject->FullName = $tousers->firstname.' '.$tousers->lastname;
             $this->log_email_notification($tousers, $fromuser, $datamailobject);
             }
            }
        }
     
    public function send_learningtrack_cancel_notification($learningtracksinstance, $touser, $fromuser, $emailtype, $notification,$row,$waitinglistid=0){
        global $PAGE,$DB;
        $datamailobject = new \stdClass();

        $datamailobject->emailtype = $emailtype;
        $datamailobject->notification_infoid = $notification->id;
        $datamailobject->plugintype = $notification->plugintype;
        $datamailobject->pluginname = $notification->pluginname;
        $datamailobject->RelatedModuleName = $learningtracksinstance['RelatedModuleName'];
        $datamailobject->body = $notification->body;
        $datamailobject->subject = $notification->subject;
        $datamailobject->arabic_subject = $notification->arabic_subject;
        $datamailobject->arabic_body = $notification->arabic_body;
        $datamailobject->moduleid = 1;
        $datamailobject->fromuserid = $fromuser->id;
        $datamailobject->teammemberid = 0;

        $roleusers = $this->getsystemlevel_role_users('trainee', 0);
        if ($roleusers) {
            foreach ($roleusers as $roleuser) {
                $localuserrecord = $DB->get_record('local_users',['userid'=> $roleuser->id]);
                $fullname = ($localuserrecord)? (($roleuser->lang == 'ar') ? $localuserrecord->firstnamearabic.' '.$localuserrecord->middlenamearabic.' '.$localuserrecord->thirdnamearabic.' '.$localuserrecord->lastnamearabic  : $localuserrecord->firstname.' '.$localuserrecord->middlenameen.' '.$localuserrecord->thirdnameen.' '.$localuserrecord->lastname) :fullname($DB->get_record('user',array('id'=>(int)$roleuser->id)));
                $datamailobject->FullName = $fullname;
                $this->log_email_notification($roleuser, $fromuser, $datamailobject);
            }
        }
    }
    public function send_learningtrack_reschedule_notification($learningtracksinstance, $touser, $fromuser, $emailtype, $notification,$row,$waitinglistid=0){
        global $PAGE;
        $datamailobject = new \stdClass();

        $datamailobject->emailtype = $emailtype;
        $datamailobject->notification_infoid = $notification->id;
        $datamailobject->plugintype = $notification->plugintype;
        $datamailobject->pluginname = $notification->pluginname;


        $datamailobject->learningtrack_completed = $row['learningtrack_completed'];
        $datamailobject->learningtrack_username = $row['learningtrack_username'];
        $datamailobject->learningtrack_coursename = $row['learningtrack_coursename'];
        $datamailobject->learningtrack_username = $row['learningtrack_username'];

        $datamailobject->body = $notification->body;
        $datamailobject->subject = $notification->subject;
        $datamailobject->learningtracksid = $learningtracksinstance->id;
        $datamailobject->touserid = $touser->id;
        $datamailobject->moduleid = 1;
        $datamailobject->fromuserid = $fromuser->id;
        $datamailobject->teammemberid = 0;

        $this->log_email_notification($touser, $fromuser, $datamailobject);
     
    }

}
