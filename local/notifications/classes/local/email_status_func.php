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
 * @package local_notifications
 */

namespace local_notifications\local;
defined('MOODLE_INTERNAL') || die;

use stdClass;
use moodle_exception;

/**
 * Notification master definition class
 */
class notification_master
{
   
    public function getSenderDetails($sender) {
        global $DB;


        $fromlocaluserrecord = $DB->get_record('local_users',['userid'=>$sender->from_userid]);

        $fullname = ($fromlocaluserrecord)? (($fromlocaluserrecord->lang == 'ar') ? $fromlocaluserrecord->firstnamearabic.' '.$fromlocaluserrecord->middlenamearabic.' '.$fromlocaluserrecord->thirdnamearabic.' '.$fromlocaluserrecord->lastnamearabic  : $fromlocaluserrecord->firstname.' '.$fromlocaluserrecord->middlenameen.' '.$fromlocaluserrecord->thirdnameen.' '.$fromlocaluserrecord->lastname) :fullname($DB->get_record('user',['id'=>$sender->from_userid]));

        $lang=$fromlocaluserrecord->lang;

        return array('fullname'=>$fullname,'lang'=>$lang);

    } 
    public function getReceiverDetails($receiver) {
        global $DB;

        $fromlocaluserrecord = $DB->get_record('local_users',['userid'=>$receiver->to_userid]);


        $fullname = ($fromlocaluserrecord)? ((  $fromlocaluserrecord->lang == 'ar') ? $fromlocaluserrecord->firstnamearabic.' '.$fromlocaluserrecord->middlenamearabic.' '.$fromlocaluserrecord->thirdnamearabic.' '.$fromlocaluserrecord->lastnamearabic  : $fromlocaluserrecord->firstname.' '.$fromlocaluserrecord->middlenameen.' '.$fromlocaluserrecord->thirdnameen.' '.$fromlocaluserrecord->lastname) :fullname($DB->get_record('user',['id'=>$sender->from_userid]));

        $lang=$fromlocaluserrecord->lang;

        return array('fullname'=>$fullname,'lang'=>$lang);

    } 


    public function getNotificationType($notification_id) {
        global $DB;

        $result = $DB->get_record_sql('SELECT *  FROM {local_notification_type} where id=?',array($notification_id));

       
        return $result;

    }
    public function getNotificationInfoById($id) {
        global $DB;

        $result = $DB->get_record_sql('SELECT *  FROM {local_emaillogs} where id=?',array($id));
      
        return $result;

    } 
    public function getSMSNotificationInfoById($id) {
        global $DB;

        $result = $DB->get_record_sql('SELECT *  FROM {local_smslogs} where id=?',array($id));
      
        return $result;

    } 

    public function getSMSReceiverDetails($receiver) {
        global $DB;

        $tolocaluserrecord = $DB->get_record('local_users',['userid'=>$receiver->to_userid]);
        $fullname = ($tolocaluserrecord)? ((current_language() == 'ar') ? $tolocaluserrecord->firstnamearabic.' '.$tolocaluserrecord->middlenamearabic.' '.$tolocaluserrecord->thirdnamearabic.' '.$tolocaluserrecord->lastnamearabic  : $tolocaluserrecord->firstname.' '.$tolocaluserrecord->middlenameen.' '.$tolocaluserrecord->thirdnameen.' '.$tolocaluserrecord->lastname) :fullname($DB->get_record('user',['id'=>$receiver->to_userid]));

        $lang=$tolocaluserrecord->lang;
        return array('fullname'=>$fullname,'lang'=>$lang);

    } 
}
