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
class cisicreateperson extends \core\task\scheduled_task {

    /**
     * Get a descriptive name for this task (shown to admins).
     *
     * @return string
     */
    public function get_name() {
        return get_string('cisicreateperson', 'local_lmsws');
    }

    /**
     * Do the job.
     * Throw exceptions on errors (the job will be retried).
     */
    public function execute() {
        global $CFG, $DB;
        $SQLexamusers="SELECT DISTINCT (uhs.userid) as userid,us.firstname as firstname ,us.lastname as lastname ,us.email as email,lu.dateofbirth as dateofbirth
        FROM {local_exams} ee 
        left JOIN {local_exam_profiles} usexpro ON usexpro.examid = ee.id
        left JOIN {local_exam_userhallschedules} uhs ON uhs.examid = usexpro.examid
        left JOIN {user} us ON us.id = uhs.userid
        left JOIN {local_users} lu ON lu.userid = us.id
        WHERE ee.ownedby='CISI' and us.id!='' order by userid";
        $getrecords = $DB->get_records_sql($SQLexamusers);
        foreach ($getrecords as $key => $values) {
          $userid=$values->userid;
          $firstname=$values->firstname;
          $lastname=$values->lastname;
          $email=$values->email; 
          $dateofbirth=$values->dateofbirth;  
          if($dateofbirth==''){
            $dateofbirth='1984-05-12';
          }   

       $authsignin = (new \local_lmsws\lib)->authentication_signin();
       $accessToken = (new \local_lmsws\lib)->authentication_token($authsignin);
       $createresponse = (new \local_lmsws\lib)->createperson($accessToken,$email,$lastname,$firstname,$dateofbirth);
        }

        
    }
}
