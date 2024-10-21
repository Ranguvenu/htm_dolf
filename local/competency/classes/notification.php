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
 * @package local_competency
 */
namespace local_competency;


class notification extends \local_notifications\notification{

    public function get_string_identifiers($emailtype){
        switch($emailtype){
            case 'competency_completions':
                $strings = "[competency_name],
                            [competency_userfulname]";
                break;

            case 'competency_adding_learning_item':
                $strings = "[competency_name]";
                break;

            case 'competency_removing_learning_item':
                $strings = "[competency_name]";
                break;
        }

        return $strings;
    }
    public function competency_notification($emailtype, $touser, $fromuser, $competencyinstance,$waitinglistid=0){
        if($notification = $this->get_existing_notification($competencyinstance, $emailtype)){

            $functionname = 'send_'.$emailtype.'_notification';

            $this->$functionname($competencyinstance, $touser, $fromuser, $emailtype, $notification,$waitinglistid);
        }
        
    }
	public function send_competency_adding_learning_item_notification($competencyinstance, $touser, $fromuser, $emailtype, $notification,$waitinglistid=0){
        global $PAGE;
     
  
        $datamailobject = new \stdClass();

        $datamailobject->emailtype = $emailtype;
        $datamailobject->notification_infoid = $notification->id;
        $datamailobject->plugintype = $notification->plugintype;
        $datamailobject->pluginname = $notification->pluginname;

        $datamailobject->competency_name = $competencyinstance->competency_name;  

        $datamailobject->body = $notification->body;
        $datamailobject->subject = $notification->subject;

        $datamailobject->arabic_body = $notification->arabic_body;
        $datamailobject->arabic_subject = $notification->arabic_subject;

        $datamailobject->moduleid = 1;
        $datamailobject->teammemberid = 0;

        if($competencyinstance->learningitemtype == 'trainingprogram'){

            $learningitemids=$competencyinstance->learningitemid;

            foreach($learningitemids as $programid){

                $trainingprogramusers = $this->gettrainingprogram_users($programid);


                if ($trainingprogramusers) {

                    foreach ($trainingprogramusers as $trainingprogramuser) {

                       // $this->log_email_notification($trainingprogramuser, $fromuser, $datamailobject);
                   
                    }
                }
            }

        }elseif($competencyinstance->learningitemtype == 'exam'){

            $learningitemids=$competencyinstance->learningitemid;

            foreach($learningitemids as $examid){

                $getexamusers = $this->getexam_users($examid);


                if ($getexamusers) {

                    foreach ($getexamusers as $getexamuser) {

                      //  $this->log_email_notification($getexamuser, $fromuser, $datamailobject);

                    }
                }
            }

        }

        $roleusers=$this->getsystemlevel_role_users('organizationofficial',0);

        if($roleusers){

            foreach($roleusers as $roleuser){              

                   // $this->log_email_notification($roleuser, $fromuser, $datamailobject);

            }
        }

        $roleusers=$this->getsystemlevel_role_users('to',0);

        if($roleusers){

            foreach($roleusers as $roleuser){        

                  // $this->log_email_notification($roleuser, $fromuser, $datamailobject);

            }
        }
        
	}
    public function send_competency_removing_learning_item_notification($competencyinstance, $touser, $fromuser, $emailtype, $notification,$waitinglistid=0){
        global $PAGE;
  
        $datamailobject = new \stdClass();

        $datamailobject->emailtype = $emailtype;
        $datamailobject->notification_infoid = $notification->id;
        $datamailobject->plugintype = $notification->plugintype;
        $datamailobject->pluginname = $notification->pluginname;

        $datamailobject->competency_name =$competencyinstance->competency_name;  

        $datamailobject->body = $notification->body;
        $datamailobject->subject = $notification->subject;

        $datamailobject->arabic_body = $notification->arabic_body;
        $datamailobject->arabic_subject = $notification->arabic_subject;


        $datamailobject->moduleid = 1;
        $datamailobject->teammemberid = 0;

        if($competencyinstance->learningitemtype == 'trainingprogram'){

            $programid=$competencyinstance->learningitemid;


            $trainingprogramusers = $this->gettrainingprogram_users($programid);

            if ($trainingprogramusers) {

                foreach ($trainingprogramusers as $trainingprogramuser) {

                  //  $this->log_email_notification($trainingprogramuser, $fromuser, $datamailobject);
               
                }
            }
            

        }elseif($competencyinstance->learningitemtype == 'exam'){

            $examid=$competencyinstance->learningitemid;


            $getexamusers = $this->getexam_users($examid);

            if ($getexamusers) {

                foreach ($getexamusers as $getexamuser) {

                   // $this->log_email_notification($getexamuser, $fromuser, $datamailobject);

                }
            }

        }
        
        $roleusers=$this->getsystemlevel_role_users('organizationofficial',0);

        if($roleusers){

            foreach($roleusers as $roleuser){               

                    $this->log_email_notification($roleuser, $fromuser, $datamailobject);

            }
        }

        $roleusers=$this->getsystemlevel_role_users('to',0);

        if($roleusers){

            foreach($roleusers as $roleuser){  

                $this->log_email_notification($roleuser, $fromuser, $datamailobject);

            }
        }
        
    }
    public function send_competency_completions_notification($competencyinstance, $touser, $fromuser, $emailtype, $notification,$waitinglistid=0){
        global $PAGE;


        $datamailobject = new \stdClass();

        $datamailobject->emailtype = $emailtype;
        $datamailobject->notification_infoid = $notification->id;
        $datamailobject->plugintype = $notification->plugintype;
        $datamailobject->pluginname = $notification->pluginname;


        $datamailobject->competency_name = $competencyinstance->competency_name;
        $datamailobject->competency_userfulname = $competencyinstance->competency_userfulname;


        $datamailobject->body = $notification->body;
        $datamailobject->subject = $notification->subject;

        $datamailobject->arabic_body = $notification->arabic_body;
        $datamailobject->arabic_subject = $notification->arabic_subject;

        
        $datamailobject->moduleid = 1;
        $datamailobject->teammemberid = 0;

        $this->log_email_notification($touser, $fromuser, $datamailobject);
        
    }

}
