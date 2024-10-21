<?php
// This file is part of Moodle - htexm://moodle.org/
//
// Moodle is free software: you can redistribute it and/or modify
// it under the terms of the GNU General Public License as published by
// the Free Software Foundation, either version 3 of the License, or
// (at your option) any later version.
//
// Moodle is distributed in the hope that it will be useful,
// but WITHOUT ANY WARRANTY; without even the implied warranty of
// MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
// GNU General Public License for more details.
//
// You should have received a copy of the GNU General Public License
// along with Moodle.  If not, see <htexm://www.gnu.org/licenses/>.
/**
 * Events Observer Page
 *
 * @package    local_events
 * @copyright  2022 e abyas  <info@eabyas.com>
 * @license    htexm://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
defined('MOODLE_INTERNAL') || die();
class local_events_observer {
    public static function events_dependency_deleted(\local_events\event\events_deleted $event) {
        global $DB;
        $agenda_exist = $DB->record_exists('local_eventagenda', ['eventid' => $event->objectid]);
        if($agenda_exist){
            $DB->delete_records('local_eventagenda', array('eventid' => $event->objectid));
        }
        $attendee_exist = $DB->record_exists('local_event_attendees', ['eventid' => $event->objectid]);
        if($attendee_exist){
            $DB->delete_records('local_event_attendees', array('eventid' => $event->objectid));
        }
        $partner_exist = $DB->record_exists('local_event_partners', ['eventid' => $event->objectid]);
        if($partner_exist){
            $DB->delete_records('local_event_partners', array('eventid' => $event->objectid));
        }
        $speaker_exist = $DB->record_exists('local_event_speakers', ['eventid' => $event->objectid]);
        if($speaker_exist){
            $DB->delete_records('local_event_speakers', array('eventid' => $event->objectid));
        }
        $sponsor_exist = $DB->record_exists('local_event_sponsors', ['eventid' => $event->objectid]);
        if($sponsor_exist){
            $DB->delete_records('local_event_sponsors', array('eventid' => $event->objectid));
        }
    }

    public static function assign_eventmanager_as_manager_to_event_category(\core\event\role_assigned $event){
        global $DB, $CFG;
        $eventdata = $event->get_data();
        $roleid =  (int)$eventdata['objectid'];
        $userid =  $eventdata['relateduserid'];
        $catagoryid = $DB->get_field('course_categories','id',array('idnumber' => 'events'));
        $context = context_coursecat::instance($catagoryid);

        $to_role_id = (int)$DB->get_field('role','id',array('shortname' => 'em'));
        $manager_role = (int)$DB->get_field('role','id',array('shortname' => 'manager'));

        if($roleid == $to_role_id) {
            role_assign($manager_role, $userid, $context->id);
        }
    }


    public static function unassign_eventmanager_as_manager_to_event_category(\core\event\role_unassigned $event){
        global $DB, $CFG;
        $eventdata = $event->get_data();
        $roleid =  (int)$eventdata['objectid'];
        $userid =  $eventdata['relateduserid'];
        $catagoryid = $DB->get_field('course_categories','id',array('idnumber' => 'events'));
        $context = context_coursecat::instance($catagoryid);

        $to_role_id = (int)$DB->get_field('role','id',array('shortname' => 'em'));
        $manager_role = (int)$DB->get_field('role','id',array('shortname' => 'manager'));

        if($roleid == $to_role_id) {
            role_unassign($manager_role, $userid, $context->id);
        }
    }

    public static function events_completions_status_update(\local_events\event\events_completions $event) {
        global $DB;
        $eventdata = $DB->get_record('local_events',array('id'=> $event->objectid));
        if($eventdata->id) {
            $DB->set_field('local_events','status',3,['id' => $eventdata->id]);
        }
    }
}
