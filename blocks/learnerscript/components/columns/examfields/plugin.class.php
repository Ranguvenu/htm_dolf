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
 * LearnerScript Reports
 * A Moodle block for creating customizable reports
 * @package blocks
 * @author: Jahnavi Nanduri
 * @date: 2022
 */
namespace block_learnerscript\lsreports;
use block_learnerscript\local\pluginbase;
use block_learnerscript\local\ls;

class plugin_examfields extends pluginbase {

    public function init() {
        $this->fullname = get_string('examfields', 'block_learnerscript');
        $this->type = 'advanced';
        $this->form = true;
        $this->reporttypes = array('exams');
    }

    public function summary($data) {
        return format_string($data->columname);
    }

    public function colformat($data) {
        $align = (isset($data->align)) ? $data->align : '';
        $size = (isset($data->size)) ? $data->size : '';
        $wrap = (isset($data->wrap)) ? $data->wrap : '';
        return array($align, $size, $wrap);
    }

    public function execute($data, $row, $user, $courseid, $starttime = 0, $endtime = 0) {
        global $DB;
        switch ($data->column) {
            case 'status': 
                if ($row->{$data->column} == 1) {
                    $row->{$data->column} = get_string('published', 'local_trainingprogram');
                } else {
                    $row->{$data->column} = get_string('un_published', 'local_trainingprogram');
                }
                $row->{$data->column} = !empty($row->{$data->column}) ? $row->{$data->column} : '--';
            break;
            case 'examprice':
                if ($row->{$data->column} == 1) {
                    $row->{$data->column} = get_string('paid', 'local_exams');
                } else {
                    $row->{$data->column} = get_string('complimentary', 'local_exams');
                }
                $row->{$data->column} = !empty($row->{$data->column}) ? $row->{$data->column} : '--';
            break;
            case 'sectors':
                $sectorslist = '';
                $sectorslist = $row->sectorid;
                $lang= current_language();
                if(!empty($sectorlist)){
                    $sectors = $DB->get_records_sql("SELECT id AS sectorid, title, titlearabic FROM {local_sector} WHERE id IN($sectorslist)");
                    foreach ($sectors as $sector) {
                        if( $lang == 'ar' && !empty($sector->titlearabic)){
                            $slist[] = $sector->titlearabic;
                        }else{
                            $slist[] =  $sector->title;
                        }
                    }
                    $sectorlist = implode(', ', $slist);
                    $row->{$data->column} = !empty($sectorlist) ? $sectorlist : '--';
                }else{
                    $row->{$data->column} = '--';
                }
                
            break;
            case 'courseid':
                $course_id = $row->courseid;
                $course = $DB->get_field('course', 'fullname', ['id' => $course_id]);
                $row->{$data->column} = !empty($course) ? format_string($course) : '--';
            break;
            case 'quizid':
                $quiz_id = $row->quizid;
                $quiz = $DB->get_field('quiz', 'name', ['id' => $quiz_id]);
                $row->{$data->column} = !empty($quiz) ? format_string($quiz) : '--';
            break;
            case 'targetgroup':
                $target_id = $row->targetgroup;
                $localjobfamily = $DB->get_field('local_jobfamily', 'familyname', ['id' => $target_id]);
                $row->{$data->column} = !empty($localjobfamily) ? format_string($localjobfamily) : '--';
            break;
            case 'competencies':
                $competencies = $row->{$data->column};
                if(!empty($competencies)){
                    $comp = $DB->get_records_sql("SELECT id, 'name' FROM {local_competencies} WHERE id IN ($competencies)"); 
                    foreach ($comp as $c) {
                        $complist[] = $c->name;
                    }
                    $competencieslist = implode(',', $complist);
                }
                
                $row->{$data->column} = !empty($competencies) ? $competencieslist : '--';
            break;
            case 'programs':
                $programs = $row->{$data->column};
                if (!empty($programs) && is_numeric($programs)) {
                    $programssql = $DB->get_records_sql("SELECT id, name FROM {local_trainingprogram} WHERE id IN ($programs)");
                    foreach ($programssql as $program) {
                        $programlist[] = $program->name;
                    }
                    $programslist = implode(',', $programlist);
                } else {
                    $programslist = ' ';
                }
                $row->{$data->column} = !empty($programlist) ? $programslist : '--';
            break;
            case 'requirements':
                $requirements = $row->{$data->column};
                if (!empty($requirements) && is_numeric($requirements)) {
                    $requirementssql = $DB->get_records_sql("SELECT id, exam FROM {local_exams} WHERE id IN ($requirements)");
                    foreach ($requirementssql as $requirement) {
                        $reqlist[] = $requirement->exam;
                    }
                    $requirementslist = implode(',', $reqlist);
                } else {
                    $requirementslist = $requirements;
                }
                $row->{$data->column} = !empty($reqlist) ? $requirementslist : '--';
            break;
            case 'certificatevalidity':
                $row->{$data->column} = !empty($row->{$data->column}) ? $row->{$data->column} . 'yrs' : '--';
            break;
            case 'examduration':
            case 'slot':
                $row->{$data->column} = !empty($row->{$data->column}) ? (new ls)->strTime($row->{$data->column}) : '--';
            break;
            case 'examdatetime':
            case 'enddate':
            case 'timecreated':
            case 'timemodified':
                $row->{$data->column} = !empty($row->{$data->column}) ? userdate($row->{$data->column},get_string('strftimedatemonthabbr', 'core_langconfig')) : '--';
            break;
        }
        return (isset($row->{$data->column}))? $row->{$data->column} : ' -- ';
    }
}
