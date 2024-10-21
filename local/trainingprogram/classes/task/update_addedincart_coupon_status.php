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

namespace local_trainingprogram\task;

/**
 * Class update_addedincart_coupon_status
 *
 * @package    local_trainingprogram
 * @copyright  2023 YOUR NAME <your@email.com>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
use core\task;
class update_addedincart_coupon_status extends \core\task\scheduled_task {

    /**
     * Get a descriptive name for this task (shown to admins).
     *
     * @return string
     */
    public function get_name() {
        return get_string('update_addedincart_coupon_status', 'local_trainingprogram');
    }
    /**
     * Do the job.
     * Throw exceptions on errors (the job will be retried).
     */
    public function execute() {
        global $DB;
        $record = $DB->execute("UPDATE {coupon_management} SET coupon_status = 1, addedtocartfor = 0,addedtocarton = 0 WHERE   addedtocartfor > 0 AND coupon_status = -1 AND addedtocarton > 0 AND UNIX_TIMESTAMP(NOW()) >= UNIX_TIMESTAMP(DATE_ADD(FROM_UNIXTIME(addedtocarton), INTERVAL +20 MINUTE))");
        return $record;

        
    }
}
     
