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
 * Learningtracks Observer Page
 *
 * @package    local_learningtracks
 * @copyright  2022 e abyas  <info@eabyas.com>
 * @license    htexm://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
defined('MOODLE_INTERNAL') || die();
class local_learningtracks_observer {
    // Exam completion
    public static function trainingprogram_exam_completion(\local_exams\event\exam_completion_updated $event) {

        global $DB,$CFG,$USER;

        $eventdata = $event->get_record_snapshot('exam_completions', $event->objectid);

        $userid = $event->relateduserid;

        if (($eventdata->completion_status == 0 || $eventdata->completion_status == 1 ||  $eventdata->completion_status == 2) && $eventdata->userid == $userid) {

            $getitemes = $DB->get_records('local_lts_item_enrolment',['itemid' => $eventdata->examid, 'userid' => $userid, 'itemtype' => 2]);

            foreach($getitemes as $getiteme){

                if($getiteme) {

                    self::track_completion($getiteme, $userid, $eventdata->completiondate,$eventdata->completion_status);
                }
            }
        }
    }
    // Program completion
    public static function trainingprogram_program_completion(\local_trainingprogram\event\trainingprogram_completion_updated $event) {

        global $DB,$CFG,$USER;

        $eventdata = $event->get_record_snapshot('program_completions', $event->objectid);

        $userid = $event->relateduserid;

        if ((($eventdata->completion_status == 0 || $eventdata->completion_status == 1 ||  $eventdata->completion_status == 2)) && ($eventdata->userid == $userid)) {

            $getitemes = $DB->get_records('local_lts_item_enrolment',['itemid' => $eventdata->programid, 'userid' => $userid, 'itemtype' => 1]);

            foreach($getitemes as $item){

                $getiteme=clone $item;

                self::track_completion($getiteme, $userid, $eventdata->completiondate,$eventdata->completion_status);
                
            }

        }
    }

    public static function track_completion($getiteme, $userid, $completiondate,$completion_status) {

        global $DB,$CFG,$USER;

        $getiteme->status = $completion_status;
        $getiteme->completiondate = $completiondate;
        $getiteme->usermodified = $userid;
        $getiteme->timemodified = time();

        $data = $DB->update_record('local_lts_item_enrolment',$getiteme);

        $item_sql = " SELECT COUNT(li.id) FROM {local_learning_items} AS li WHERE li.trackid =$getiteme->trackid";
        $items_count = $DB->count_records_sql($item_sql);


        $completed_count = $DB->count_records_sql("SELECT COUNT(le.trackid) FROM {local_lts_item_enrolment} le WHERE  le.userid = $userid AND le.trackid = $getiteme->trackid ");

        $lts_enrollment =  $DB->get_record('local_lts_enrolment',['trackid' => $getiteme->trackid,'userid'=>$userid]);


        if($lts_enrollment) {

            $lts_enrollment->usermodified = $userid;
            $lts_enrollment->timemodified = time();

            if($items_count == $completed_count) {

                $lts_enrollment->status = $completion_status;
                $lts_enrollment->completiondate = $completiondate;

                $DB->update_record('local_lts_enrolment',$lts_enrollment);

                // notification learningtrack completion
                $trackinfo = $DB->get_record('local_learningtracks', array('id' => $lts_enrollment->trackid));
                $row=[];
                $row['learningTrackName']=$trackinfo->name;
                $sql="SELECT u.* 
                        FROM {user} u
                        JOIN {local_lts_enrolment} le ON le.userid = u.id
                        WHERE le.trackid = $getiteme->trackid 
                            AND u.confirmed = 1 
                            AND u.suspended = 0 
                            AND u.deleted = 0 
                            AND u.id > 2";
                $touser = $DB->get_records_sql($sql);
                $myobject=(new \local_learningtracks\notification);
                $myobject->learningtracks_notification('learningtrack_completed',$touser, get_admin(),$row,$waitinglistid=0);

          

            }else{

                $lts_enrollment->status = 0;
                $lts_enrollment->completiondate = 0;
                
                $DB->update_record('local_lts_enrolment',$lts_enrollment);

            }

            // Trigger an event for learningtracks completion changed.
            $event = \local_learningtracks\event\learningtracks_completion_updated::create(array(
                'objectid' => $lts_enrollment->id,
                'context' => \context_system::instance(),
                'relateduserid' => $userid,
                'other' => array(
                    'relateduserid' => $userid,
                    'learningtrackid' => $getiteme->trackid,
                    'completion_status' => $lts_enrollment->status,
                )
            ));
            $event->add_record_snapshot('local_lts_enrolment', $lts_enrollment);
                $event->trigger();
        }
    }
}
