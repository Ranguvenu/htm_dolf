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
 * @package    local_trainingprogram
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
namespace local_trainingprogram\task;

use core\task;
class update_offering_status extends \core\task\scheduled_task {

    /**
     * Get a descriptive name for this task (shown to admins).
     *
     * @return string
     */
    public function get_name() {
        return get_string('update_offering_status', 'local_trainingprogram');
    }
    /**
     * Do the job.
     * Throw exceptions on errors (the job will be retried).
     */
    public function execute() {
        global $CFG, $DB;
        $currentdate = strtotime(date('Y-m-d'));
        $firstdayofthismonth = strtotime(date('Y-m-01'));
        if($currentdate == $firstdayofthismonth) {
        	$record = $DB->execute("UPDATE {tp_offerings} SET financially_closed_status = 1 WHERE   enddate > 0 AND enddate < $firstdayofthismonth AND  financially_closed_status = 0 AND fc_status_added_by = 0");
            return $record;

        }
    }
}
     
