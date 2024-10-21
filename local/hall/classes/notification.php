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
 * @package local_hall
 */
namespace local_hall;

class notification extends \local_notifications\notification
{
    public $db;
    public $user;
    public function get_string_identifiers($emailtype)
    {
        switch ($emailtype) {
            case 'hall_reservation':
                $strings = "[RelatedModuleName],[HallName],[reservationLink],[FullName]";
                break;
        }

        return $strings;
    }
    public function hall_reservation_notification($emailtype, $touser, $fromuser, $hallinstance, $row1, $waitinglistid = 0)
    {

        if ($notification = $this->get_existing_notification($hallinstance, $emailtype)) {
            $functionname = 'send_' . $emailtype . '_notification';

            $this->$functionname($hallinstance, $touser, $fromuser, $emailtype, $row1, $notification, $waitinglistid);
        }
        
    }
    public function send_hall_reservation_notification($hallinstance, $touser, $fromuser, $emailtype, $row1, $notification, $waitinglistid = 0)
    {
       // print_r($hallinstance);exit;
        global $PAGE,$DB;
        $datamailobject = new \stdClass();

        $datamailobject->emailtype = $emailtype;
        $datamailobject->notification_infoid = $notification->id;
        $datamailobject->plugintype = $notification->plugintype;
        $datamailobject->pluginname = $notification->pluginname;
        $datamailobject->HallName = $hallinstance['HallName'];
        $datamailobject->RelatedModuleName = $hallinstance['RelatedModuleName'];
        $datamailobject->reservationLink = $hallinstance['reservationLink'];
        $datamailobject->body = $notification->body;
        $datamailobject->subject = $notification->subject;
        $datamailobject->arabic_body = $notification->arabic_body;
        $datamailobject->arabic_subject = $notification->arabic_subject;
        $datamailobject->touserid = $touser->id;
        $datamailobject->moduleid = 1;
        $datamailobject->fromuserid = $fromuser->id;
        $datamailobject->teammemberid = 0;
        $roleusers = $this->getsystemlevel_role_users('to', 0);
        if ($roleusers) {
            foreach ($roleusers as $roleuser) {
                $localuserrecord = $DB->get_record('local_users',['userid'=> $roleuser->id]);
                $fullname = ($localuserrecord)? (($roleuser->lang == 'ar') ? $localuserrecord->firstnamearabic.' '.$localuserrecord->middlenamearabic.' '.$localuserrecord->thirdnamearabic.' '.$localuserrecord->lastnamearabic  : $localuserrecord->firstname.' '.$localuserrecord->middlenameen.' '.$localuserrecord->thirdnameen.' '.$localuserrecord->lastname) :fullname($DB->get_record('user',array('id'=>(int)$roleuser->id)));
                $datamailobject->FullName = $fullname;
                $this->log_email_notification($roleuser, $fromuser, $datamailobject);
            }
        }
    }
}
