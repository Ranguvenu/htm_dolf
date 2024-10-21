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
 * @package    local_cpd
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
namespace local_lmsws\task;

use core\task;
// use local_events\notification_emails;
class cisiexam_mapping extends \core\task\scheduled_task {

    /**
     * Get a descriptive name for this task (shown to admins).
     *
     * @return string
     */
    public function get_name() {
        return get_string('cisiexam_mapping', 'local_lmsws');
    }

    /**
     * Do the job.
     * Throw exceptions on errors (the job will be retried).
     */
    public function execute() {
        global $CFG, $DB;   
        $SQLexamusers="SELECT cisi.cisiuserid as cisiuserid , exam.code as examcode, ee.examdate as examdate, hall.code as hallcode, ee.userid as userid,us.firstname as firstname ,us.lastname as lastname ,us.email as email
        FROM {exam_enrollments} ee 
        JOIN {user} us ON us.id = ee.userid
        JOIN {cisiuserdetails} cisi ON us.id = cisi.userid
        JOIN {local_exams} exam ON ee.id = exam.id
        JOIN {hall} hall ON ee.hallid = hall.id        
        WHERE ee.examdate IN (CURDATE() + INTERVAL 1 DAY)";
        $getrecords = $DB->get_records_sql($SQLexamusers);
        foreach ($getrecords as $key => $values) {
           $userid=$values->userid;
           $cisiuserid=$values->cisiuserid;
           $firstname=$values->firstname;
           $lastname=$values->lastname;
           $email=$values->email;
           $examcode=$values->examcode;   
           $hallcode=$values->hallcode;
           $examdate=$values->examdate; 
                        
       $authsignin = (new \local_lmsws\lib)->authentication_signin();
       $accessToken = (new \local_lmsws\lib)->authentication_token($authsignin);
      $exammappingresponse = (new \local_lmsws\lib)->cisiexam_mapping($accessToken,$email,$lastname,$firstname,$cisiuserid,$examcode,$hallcode,$examdate);   
    }
}
}
