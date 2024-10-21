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
 * @package tool_product
 */
namespace tool_product;


class notification extends \local_notifications\notification{

    public function get_string_identifiers($emailtype){
        switch($emailtype){
            case 'payment_completion':
                $strings = "[payment_userfullname],[payment_details],[invoiceno]";
                break;

            case 'wallet_update':
                $strings = "[payment_userfullname]";
                break;

            case 'post_payment':
                $strings = "[payment_userfullname]";
                break;

            case 'pre_payment':
                $strings = "[payment_userfullname],[payment_details],[order]";
                break;
        }
        return $strings;
    }
    public function product_notification($emailtype, $touser, $fromuser, $productinstance,$waitinglistid=0){
        if($notification = $this->get_existing_notification($productinstance, $emailtype)){

            $functionname = 'send_'.$emailtype.'_notification';

            $this->$functionname($productinstance, $touser, $fromuser, $emailtype, $notification,$waitinglistid);
        }
        
    }
	public function send_payment_completion_notification($productinstance, $touser, $fromuser, $emailtype, $notification,$waitinglistid=0){
        global $PAGE;
     
        $datamailobject = new \stdClass();

        $datamailobject->emailtype = $emailtype;
        $datamailobject->notification_infoid = $notification->id;
        $datamailobject->plugintype = $notification->plugintype;
        $datamailobject->pluginname = $notification->pluginname;

        $datamailobject->payment_userfullname = $productinstance->payment_userfullname;  
        $datamailobject->payment_details = $productinstance->payment_details;  
        $datamailobject->invoiceno = $productinstance->invoiceno;  

        $datamailobject->body = $notification->body;
        $datamailobject->subject = $notification->subject;

        $datamailobject->arabic_body = $notification->arabic_body;
        $datamailobject->arabic_subject = $notification->arabic_subject;

        $datamailobject->moduleid = 1;
        $datamailobject->teammemberid = 0;
        $datamailobject->userlang = $touser->lang;
       

        $this->log_email_notification($touser, $fromuser, $datamailobject);
    
        
	}
    public function send_wallet_update_notification($productinstance, $touser, $fromuser, $emailtype, $notification,$waitinglistid=0){
        global $PAGE;
  
        $datamailobject = new \stdClass();

        $datamailobject->emailtype = $emailtype;
        $datamailobject->notification_infoid = $notification->id;
        $datamailobject->plugintype = $notification->plugintype;
        $datamailobject->pluginname = $notification->pluginname;

        $datamailobject->payment_userfullname =$productinstance->payment_userfullname;  

        $datamailobject->body = $notification->body;
        $datamailobject->subject = $notification->subject;

        $datamailobject->arabic_body = $notification->arabic_body;
        $datamailobject->arabic_subject = $notification->arabic_subject;


        $datamailobject->moduleid = 1;
        $datamailobject->teammemberid = 0;

        $this->log_email_notification($touser, $fromuser, $datamailobject);

        
    }
    public function send_post_payment_notification($productinstance, $touser, $fromuser, $emailtype, $notification,$waitinglistid=0){
        global $PAGE;


        $datamailobject = new \stdClass();

        $datamailobject->emailtype = $emailtype;
        $datamailobject->notification_infoid = $notification->id;
        $datamailobject->plugintype = $notification->plugintype;
        $datamailobject->pluginname = $notification->pluginname;


        $datamailobject->payment_userfullname = $productinstance->payment_userfullname;


        $datamailobject->body = $notification->body;
        $datamailobject->subject = $notification->subject;

        $datamailobject->arabic_body = $notification->arabic_body;
        $datamailobject->arabic_subject = $notification->arabic_subject;

        
        $datamailobject->moduleid = 1;
        $datamailobject->teammemberid = 0;

        $this->log_email_notification($touser, $fromuser, $datamailobject);
        
    }
    public function send_pre_payment_notification($productinstance, $touser, $fromuser, $emailtype, $notification,$waitinglistid=0){
        global $PAGE;

        $datamailobject = new \stdClass();

        $datamailobject->emailtype = $emailtype;
        $datamailobject->notification_infoid = $notification->id;
        $datamailobject->plugintype = $notification->plugintype;
        $datamailobject->pluginname = $notification->pluginname;


        $datamailobject->payment_userfullname = $productinstance->payment_userfullname;
        $datamailobject->payment_details = $productinstance->payment_details;
        $datamailobject->order = $productinstance->order;


        $datamailobject->body = $notification->body;
        $datamailobject->subject = $notification->subject;

        $datamailobject->arabic_body = $notification->arabic_body;
        $datamailobject->arabic_subject = $notification->arabic_subject;

        
        $datamailobject->moduleid = 1;
        $datamailobject->teammemberid = 0;

        $this->log_email_notification($touser, $fromuser, $datamailobject);
        
    }

}
