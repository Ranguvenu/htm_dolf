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
 * CPD Observer Page
 *
 * @package    local_cpd
 * @copyright  2022 e abyas  <info@eabyas.com>
 * @license    htexm://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
defined('MOODLE_INTERNAL') || die();
class local_cpd_observer {
    public static function cpd_dependency_deleted(\local_cpd\event\cpd_deleted $event) {
        global $DB;
        $program_exist = $DB->record_exists('local_cpd_training_programs', ['cpdid' => $event->objectid]);
        if($program_exist){
            $DB->delete_records('local_cpd_training_programs', array('cpdid' => $event->objectid));
        }
        $evidence_exist = $DB->get_field('local_cpd_evidence', 'id', ['cpdid' => $event->objectid]);
        if ($evidence_exist) {
            $formal_evid_exist = $DB->record_exists('local_cpd_formal_evidence', ['evidenceid' => $evidence_exist]);
            if($formal_evid_exist){
                $DB->delete_records('local_cpd_formal_evidence', array('evidenceid' => $evidence_exist));
            }
            $informal_evid_exist = $DB->record_exists('local_cpd_informal_evidence', ['evidenceid' => $evidence_exist]);
            if($informal_evid_exist){
                $DB->delete_records('local_cpd_informal_evidence', array('evidenceid' => $evidence_exist));
            }
            $DB->delete_records('local_cpd_evidence', array('cpdid' => $event->objectid));
        }
    }

    public static function trainingprogram_completed(local_trainingprogram\event\trainingprogram_completion_updated $event){
        global $DB;
        $eventdata = $event->get_record_snapshot('program_completions', $event->objectid);
        $userid = $event->relateduserid;
        if ((($eventdata->completion_status == 0 || $eventdata->completion_status == 1)) && ($eventdata->userid == $userid)) {
            $cpd_programs = $DB->get_records('local_cpd_training_programs',['programid' => $eventdata->programid]);
            if($cpd_programs) {
                foreach($cpd_programs as $program) {
                    $programdata = new stdClass();
                    $programdata->cpdid = $program->cpdid;
                    $programdata->programid = $program->programid;
                    $programdata->userid = $userid;
                    $programdata->realuser = ($USER->realuser) ? $USER->realuser :0;
                    $programdata->hoursachieved = $program->creditedhours;
                    $programdata->timecreated = time();
                    $DB->insert_record('trainingprogram_completion', $programdata);	
                    $logdata = new stdClass();
                    $logdata->cpdid = $program->cpdid;
                    $logdata->userid = $userid;
                    $logdata->realuser = ($USER->realuser) ? $USER->realuser :0;
                    $logdata->hoursachieved = $program->creditedhours;
                    $logdata->source = 'trainingpragram';
                    $logdata->dateachieved = time();
                    $logdata->timecreated = time();
                    $program_log = $DB->insert_record('local_cpd_hours_log', $logdata);
                }
            }
        }
    }
}
