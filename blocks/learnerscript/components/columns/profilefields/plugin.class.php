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
 * @date: 2023
 */
namespace block_learnerscript\lsreports;
use block_learnerscript\local\pluginbase;
use block_learnerscript\local\ls;

class plugin_profilefields extends pluginbase {

    public function init() {
        $this->fullname = get_string('profilefields', 'block_learnerscript');
        $this->type = 'advanced';
        $this->form = true;
        $this->reporttypes = array('examprofiles');
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
            case 'registrationstartdate': 
            case 'registrationenddate':
            case 'timecreated':
            case 'timemodified':
                $row->{$data->column} = !empty($row->{$data->column}) ? userdate($row->{$data->column},get_string('strftimedatemonthabbr', 'core_langconfig')) : '--'; 
            break;
            case 'duration':
                $row->{$data->column} = !empty($row->{$data->column}) ? (new ls)->strTime($row->{$data->column}) : '--';
            break;
            case 'activestatus':
            case 'publishstatus':
            case 'hascertificate':
            case 'preexampage':
            case 'successrequirements':
            case 'showquestions':
            case 'showexamduration':
            case 'showremainingduration':
            case 'commentsoneachque':
            case 'commentsaftersub':
            case 'showexamresult':
            case 'showexamgrade':
            case 'competencyresult':
            case 'resultofeachcompetency':
            case 'evaluationform':
            case 'notifybeforeexam':
                if ($row->{$data->column} == 1) {
                    $row->{$data->column} = get_string('yes', 'block_learnerscript');
                } else {
                    $row->{$data->column} = get_string('no', 'block_learnerscript');
                }
            break;
            case 'decision':
                if ($row->{$data->column} == 1) {
                    $row->{$data->column} = get_string('approved','local_exams');
                } else if ($row->{$data->column} == 2) {
                    $row->{$data->column} = get_string('rejected','local_exams');
                } else if ($row->{$data->column} == 3) {
                    $row->{$data->column} = get_string('underreview','local_exams');
                } else if ($row->{$data->column} == 4) {
                    $row->{$data->column} = get_string('draft','local_exams');
                }
            break;
            case 'language':
                if ($row->{$data->column} == 1) {
                    $row->{$data->column} = get_string('english','local_exams');
                } else {
                    $row->{$data->column} = get_string('arabic','local_exams');
                }
            break;
            case 'targetaudience':
                if ($row->{$data->column} == 1) {
                    $row->{$data->column} = get_string('saudi','local_exams');
                } else if ($row->{$data->column} == 2) {
                    $row->{$data->column} = get_string('nonsaudi','local_exams');
                } else if ($row->{$data->column} == 3) {
                    $row->{$data->column} = get_string('both','local_exams');
                }
            break;
            case 'discount':
                if ($row->{$data->column} == 1) {
                    $row->{$data->column} = get_string('coupons','local_exams');
                } else if ($row->{$data->column} == 2) {
                    $row->{$data->column} = get_string('earlyregistration','local_exams');
                } else if ($row->{$data->column} == 3) {
                    $row->{$data->column} = get_string('group','local_exams');
                }
            break;
            case 'examid': 
                $examid = $row->{$data->column};

                $lang= current_language();
                if( $lang == 'ar'){
                    $row->{$data->column} = $DB->get_field_sql("SELECT examnamearabic AS name FROM {local_exams} WHERE id = $examid");
                } else{
                    $row->{$data->column} = $DB->get_field_sql("SELECT exam FROM {local_exams} WHERE id = $examid");
                }

                $row->{$data->column} = !empty($row->{$data->column}) ? $row->{$data->column} : '--';
            break;
            case 'material':
            case 'nondisclosure':
            case 'instructions':
                $row->{$data->column} = !empty($row->{$data->column}) ? $row->{$data->column} : '--';
            break;
        }
        return (isset($row->{$data->column}))? $row->{$data->column} : ' -- ';
    }
}
