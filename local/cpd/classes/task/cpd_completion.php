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
namespace local_cpd\task;

use core\task;
// use local_events\notification_emails;
class cpd_completion extends \core\task\scheduled_task {

    /**
     * Get a descriptive name for this task (shown to admins).
     *
     * @return string
     */
    public function get_name() {
        return get_string('cpd_completion', 'local_cpd');
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


            $cpdevidence = $DB->get_records_sql("SELECT * FROM {local_cpd_evidence} WHERE status=:status AND timemodified >= $lastruntime AND timemodified <= $timenow",['status' =>1]);

            // $cpdevidence = $DB->get_records('local_cpd_evidence',['status' =>1]);

            $evidence_text = array(1 => 'formalevidence', 2 =>'informalevidence');

            foreach($cpdevidence as $evidence) {

                if($evidence->evidencetype == 1){

                    $formal_evidence = $DB->get_record('local_cpd_formal_evidence',['evidenceid' => $evidence->id]);

                    $creditedhours = $formal_evidence->creditedhours;

                } elseif($evidence->evidencetype == 2){

                    $informal_evidence = $DB->get_record('local_cpd_informal_evidence',['evidenceid' => $evidence->id]);

                    $creditedhours = $informal_evidence->creditedhours;

                }
               
                $evidencetype = $evidence_text[$evidence->evidencetype];

                $cpd_completion = (new \local_cpd\lib)->cpd_completion($evidence->id, $evidence->cpdid, $evidence->userid, $creditedhours);
                
                $cpd_logs = (new \local_cpd\lib)->cpd_logs($evidence->id, $evidence->cpdid, $evidence->userid, $creditedhours, $evidencetype);

            }
        }
    }
}