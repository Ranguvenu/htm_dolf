<?php

// This file is part of Moodle - http://moodle.org/
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
// along with Moodle.  If not, see <http://www.gnu.org/licenses/>.

/**
 * Scheduled Task
 * 
 * @package    mod_teamsmeeting
 * @copyright  2022 eAbyas Info Solutions Pvt Ltd (www.eabyas.com)
 * @author     Ranga Reddy<rangareddy@eabyas.com>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace mod_teamsmeeting\task;

use core\task;

class get_attendance_report_after_meeting extends \core\task\scheduled_task {

    /**
     * Get a descriptive name for this task (shown to admins).
     *
     * @return string
     */
    public function get_name(){
        return get_string('get_attendance_report_after_meeting', 'mod_teamsmeeting');
    }

        /**
     * Do the job.
     * Throw exceptions on errors (the job will be retried).
     */
    public function execute() {
        global $CFG, $DB;

        $lastruntime=self::get_last_run_time();

        $nextruntime=self::get_next_run_time();

        $timenow   = time();

        if($nextruntime <= $timenow){
            require_once("$CFG->dirroot/mod/teamsmeeting/lib.php");
            $time = time();
            $meetings = $DB->get_fieldset_sql("SELECT tm.id FROM {teamsmeeting} as tm WHERE isreportgenerated = 0 AND end_time < '{$time}'");
            foreach($meetings as $meeting){
                $attendance = sync_attendance_report($meeting);
                if($attendance && isset($attendance['attendance_report']) && sizeof($attendance['attendance_report'])){
                    $DB->update_record('teamsmeeting', array('id'   =>  $meeting, 'isreportgenerated' => 1), false);
                }
            }
        }
      
    }
}