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
 * A scheduled task.
 *
 * @package    local_exams
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
namespace local_exams\task;

use core\task;
class delete_bulk_upload_log extends \core\task\scheduled_task {

    /**
     * Get a descriptive name for this task (shown to admins).
     *
     * @return string
     */
    public function get_name() {
        return get_string('delete_bulk_upload_log', 'local_exams');
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
        $fromtime = $timenow - (60*60*24*7);
        if($nextruntime <= $timenow){
                    $DB->delete_records_select('logstore_standard_log', "timecreated < $fromtime
                    AND (eventname ='\\\\local_exams\\\\event\\\\invalid_field' 
                    OR eventname ='\\\\local_exams\\\\event\\\\field_exists'
                    OR eventname ='\\\\local_exams\\\\event\\\\missing_field'
                    OR eventname ='\\\\local_exams\\\\event\\\\header_missing'
                    OR eventname ='\\\\local_exams\\\\event\\\\row_inserted')");
        }
    }
}