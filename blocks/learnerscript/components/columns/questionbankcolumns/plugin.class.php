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
use html_writer;

class plugin_questionbankcolumns extends pluginbase {

    public function init() {
        $this->fullname = get_string('questionbankcolumns', 'block_learnerscript');
        $this->type = 'undefined';
        $this->form = true;
        $this->reporttypes = array('questionbankinfo');
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
        global $DB, $CFG;
        switch ($data->column) {
            case 'experts':
                $expertsnames=$DB->get_fieldset_sql("SELECT CONCAT(u.firstname,' ',u.lastname) as fullname FROM {local_qb_experts} as qe JOIN {user} as u ON u.id = qe.expertid
                 JOIN {local_users} lu ON lu.userid = u.id
                WHERE qe.questionbankid   =".$row->id." AND lu.deleted = 0 AND lu.approvedstatus = 2");
                if(!empty($expertsnames)){
                    $expertsnames = implode(',',$expertsnames);
                }
                $row->{$data->column} = !empty($expertsnames) ? $expertsnames : '--'; 
               
            break;
            case 'createdquestions':
                $qcategory = $row->qcategoryid.',1';
                $createdquestions=$DB->count_records_sql("SELECT count(1) FROM {question} q JOIN {question_versions} qv ON qv.questionid = q.id JOIN {question_bank_entries} qbe on qbe.id = qv.questionbankentryid JOIN {question_categories} qc ON qc.id = qbe.questioncategoryid LEFT JOIN {user} uc ON uc.id = q.createdby WHERE q.parent = 0 AND qv.version = (SELECT MAX(v.version) FROM {question_versions} v JOIN {question_bank_entries} be ON be.id = v.questionbankentryid WHERE be.id = qbe.id) AND ((qbe.questioncategoryid = $row->qcategoryid))");
                if (!empty($createdquestions)) { 
                    $createdquestions = html_writer::link("$CFG->wwwroot/local/questionbank/questionbank_view.php?wid=$row->id&courseid=1&cat=$qcategory", $createdquestions, array("target" => "_blank"));
                } else {
                    $createdquestions = 0;
                }
                $row->{$data->column} = $createdquestions;
            break;
            case 'publishedquestions':
                $publishedquestions=$DB->count_records_sql("SELECT count(1) FROM {question} q JOIN {question_versions} qv ON qv.questionid = q.id JOIN {question_bank_entries} qbe on qbe.id = qv.questionbankentryid JOIN {question_categories} qc ON qc.id = qbe.questioncategoryid LEFT JOIN {user} uc ON uc.id = q.createdby WHERE q.parent = 0 AND qv.version = (SELECT MAX(v.version) FROM {question_versions} v JOIN {question_bank_entries} be ON be.id = v.questionbankentryid WHERE be.id = qbe.id AND v.status = 'ready') AND ((qbe.questioncategoryid = $row->qcategoryid))");
                $row->{$data->column} = !empty($publishedquestions) ? $publishedquestions : '0'; 
            break;
            case 'startdate':
                $row->{$data->column} = $row->workshopdate;
            break;
        }
        return (isset($row->{$data->column}))? $row->{$data->column} : ' -- ';
    }
}
