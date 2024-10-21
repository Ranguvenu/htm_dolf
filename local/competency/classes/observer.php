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
 * Competency view page
 *
 * @package    local_competency
 * @copyright  2022 e abyas  <info@eabyas.com>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
defined('MOODLE_INTERNAL') || die();

use local_competency\competency as competency;


class local_competency_observer{

	public static function competency_trainingprogram_completed(\local_trainingprogram\event\trainingprogram_completion_updated $event){
		global $DB, $CFG;

        $eventdata = $event->get_record_snapshot('program_completions', $event->objectid);

        $userid = $event->relateduserid;

        if ((($eventdata->completion_status == 0 || $eventdata->completion_status == 1)) && ($eventdata->userid == $userid)) {
            

            $sql = "SELECT cpcbj.competencypc as competencypcid,cpcbj.competency as competencyid,trgprgm.id as programid
                    FROM {local_trainingprogram} AS trgprgm
                    JOIN {local_competencypc_obj} AS cpcbj ON FIND_IN_SET(trgprgm.id,cpcbj.trainingprogramids) > 0
                    WHERE trgprgm.id=:trainingprogramid ";


            $params = array('trainingprogramid' => $eventdata->programid); 

        
            $competencies_tp = $DB->get_records_sql($sql, $params);

            if ($competencies_tp) {

                foreach($competencies_tp as $competency_tp ){
 

                    $competency_tp->completion_status=$event->other['completion_status'];

                    $competency_tp->userid=$userid;

                    (new competency)->update_competency_trainingprogram_status($competency_tp);
                }
            }
            
        }
	}
    public static function competency_exam_completed(\local_exams\event\exam_completion_updated $event){
        global $DB, $CFG;

        $eventdata = $event->get_record_snapshot('exam_completions', $event->objectid);


        $userid = $event->relateduserid;

        if (($eventdata->completion_status == 0 || $eventdata->completion_status == 1) && $eventdata->userid == $userid) {
            

            $sql = "SELECT cpcbj.competencypc as competencypcid,cpcbj.competency as competencyid,exm.id as examid
                    FROM {local_exams} AS exm
                    JOIN {local_competencypc_obj} AS cpcbj ON FIND_IN_SET(exm.id,cpcbj.examids) > 0
                    WHERE exm.id=:examid ";


            $params = array('examid' => $eventdata->examid); 

        
            $competencies_exam = $DB->get_records_sql($sql, $params);


            if ($competencies_exam) {

                foreach($competencies_exam as $competency_exam ){

                    $competency_exam->completion_status=$event->other['completion_status'];

                    $competency_exam->userid=$userid;

                    (new competency)->update_competency_exam_status($competency_exam);
                }
            }
            
        }
    }
    public static function competency_trainingprogram_deleted(\local_trainingprogram\event\trainingprogram_deleted $event){
        global $DB, $CFG;

            (new competency)->update_competency_trainingprogram_delete($event->objectid);
            
        
    }
    public static function competency_exam_deleted(\local_exams\event\exam_deleted $event){
        global $DB, $CFG;

            (new competency)->update_competency_exam_delete($event->objectid);
            
        
    }
}
