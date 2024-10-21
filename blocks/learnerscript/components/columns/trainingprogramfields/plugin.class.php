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

class plugin_trainingprogramfields extends pluginbase {

    public function init() {
        $this->fullname = get_string('trainingprogramfields', 'block_learnerscript');
        $this->type = 'advanced';
        $this->form = true;
        $this->reporttypes = array('trainingprogramfields');
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
            case 'availablefrom': 
            case 'availableto':
            case 'timecreated':
            case 'timemodified':
                $row->{$data->column} = !empty($row->{$data->column}) ? userdate($row->{$data->column},get_string('strftimedatemonthabbr', 'core_langconfig')) : '--'; 
            break;
            case 'price':
                if ($row->{$data->column} == 1) {
                    $row->{$data->column} = get_string('paid', 'local_trainingprogram');
                } else {
                    $row->{$data->column} = get_string('free', 'local_trainingprogram');
                }
                $row->{$data->column} = !empty($row->{$data->column}) ? $row->{$data->column} : '--';
            break;
            case 'languages':
                $languages = explode(',', $row->{$data->column});
                foreach ($languages as $key => $value) {
                    if ($value == 0) {
                        $langlist[] = get_string('arabic', 'local_trainingprogram');
                    } else if ($value == 1) {
                        $langlist[] = get_string('english', 'local_trainingprogram');
                    }
                }
                $row->{$data->column} = !empty($langlist) ? implode(', ', $langlist) : '--';
            break;
            case 'methods':
                $programmethods = explode(',', $row->{$data->column});
                foreach ($programmethods as $key => $value) {
                    if ($value == 0) {
                        $methodslist[] = get_string('lecture', 'local_trainingprogram');
                    } else if ($value == 1) {
                        $methodslist[] = get_string('case_studies', 'local_trainingprogram');
                    } else if ($value == 2) {
                        $methodslist[] = get_string('dialogue_teams', 'local_trainingprogram');
                    } else if ($value == 3) {
                        $methodslist[] = get_string('exercises_assignments', 'local_trainingprogram');
                    }
                }
                $row->{$data->column} = !empty($methodslist) ? implode(', ', $methodslist) : '--';
            break;
            case 'evaluationmethods':
                $evaluationmethods = explode(',', $row->{$data->column});
                foreach ($evaluationmethods as $key => $value) {
                    if ($value == 0) {
                        $evaluationmethodslist[] = get_string('pre_exam', 'local_trainingprogram');
                    } else if ($value == 1) {
                        $evaluationmethodslist[] = get_string('post_exam', 'local_trainingprogram');
                    }
                }
                $row->{$data->column} = !empty($evaluationmethodslist) ? implode(', ', $evaluationmethodslist) : '--';
            break;
            case 'duration':
            case 'hour': 
                $row->{$data->column} = !empty($row->{$data->column}) ? (new ls)->strTime($row->{$data->column}) : '--';
            break;
            case 'competencyandlevels':
                $competencyandlevels = explode(',', $row->{$data->column});
                foreach ($competencyandlevels as $key => $value) {
                    if (!empty($value)) {
                        $complevelslist[] = $DB->get_field_sql("SELECT lc.name FROM {local_competencies} lc WHERE 1 = 1 AND lc.id = $value");
                    }
                }
                $row->{$data->column} = !empty($complevelslist) ? implode(', ', $complevelslist) : '--';
            break;
            case 'targetgroup':
                $targetgroup = explode(',', $row->{$data->column});
                foreach ($targetgroup as $key => $value) {
                    if(!empty($value)) {
                        $targetgrouplist[] = $DB->get_field_sql("SELECT ljf.familyname FROM {local_jobfamily} ljf WHERE 1 = 1 AND ljf.id = $value");
                    }
                }
                $row->{$data->column} = !empty($targetgrouplist) ? implode(', ', $targetgrouplist) : '--';
            break;
            case 'discount':
                if ($row->{$data->column} == 0) {
                    $row->{$data->column} = get_string('coupon', 'local_trainingprogram');
                } else if($row->{$data->column} == 1) {
                    $row->{$data->column} = get_string('early_registration', 'local_trainingprogram');
                } else {
                    $row->{$data->column} = get_string('groups', 'local_trainingprogram');
                }
                $row->{$data->column} = !empty($row->{$data->column}) ? $row->{$data->column} : '--';
            break;
            case 'published':
                if ($row->{$data->column} == 1) {
                    $row->{$data->column} = get_string('published', 'local_trainingprogram');
                } else {
                    $row->{$data->column} = get_string('un_published', 'local_trainingprogram');
                }
                $row->{$data->column} = !empty($row->{$data->column}) ? $row->{$data->column} : '--';
            break;
            case 'trainingtype':
                if (!empty($row->{$data->column})) {
                    $types = explode(',', $row->{$data->column});
                    foreach ($types as $type) {
                        if ($type == 'online') {
                            $typesdata[] = get_string('scheduleonline', 'local_trainingprogram');
                        } else if ($type == 'offline'){
                            $typesdata[] = get_string('scheduleoffline', 'local_trainingprogram');
                        } else if ($type == 'elearning'){
                            $typesdata[] = get_string('scheduleelearning', 'local_trainingprogram');
                        }
                    }
                    $row->{$data->column} = $typesdata;
                } else {
                    $row->{$data->column} = '';
                }
                
                $row->{$data->column} = !empty($row->{$data->column}) ? implode(',', $row->{$data->column}) : '--';
            break;
            case 'usercreated':
                $userid = $row->{$data->column};
                if (!empty($userid)) { 
                    $row->{$data->column} = $DB->get_field_sql("SELECT CONCAT(u.firstname, ' ', u.lastname) FROM {user} u WHERE u.id = $userid");
                }
                $row->{$data->column} = !empty($row->{$data->column}) ? $row->{$data->column} : '--';
            break;
        }
        return (isset($row->{$data->column}))? $row->{$data->column} : ' -- ';
    }
}
