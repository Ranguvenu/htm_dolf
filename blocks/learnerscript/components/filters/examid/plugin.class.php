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
 * @author: eAbyas Info Solutions
 * @date: 2022
 */
namespace block_learnerscript\lsreports;
use block_learnerscript\local\pluginbase;
use stdClass;

class plugin_examid extends pluginbase {

    public function init() {
        $this->form = false;
        $this->unique = true; 
        $this->placeholder = true;
        $this->singleselection = true;
        $this->fullname = get_string('filterexamid', 'block_learnerscript');
        $this->reporttypes = array('offerings');
        $this->filtertype = 'custom';
        if (!empty($this->reportclass->basicparams)) {
            foreach ($this->reportclass->basicparams as $basicparam) {
                if ($basicparam['name'] == 'examid') {
                    $this->filtertype = 'basic';
                }
            }
        }
    }

    public function summary($data) {
        return get_string('filterexamid_summary', 'block_learnerscript');
    }

    public function execute($finalelements, $data, $filters) {

        $filterexamid = isset($filters['filter_examid']) ? $filters['filter_examid'] : 0;
        if (!$filterexamid) {
            return $finalelements;
        }

        if ($this->report->type != 'sql') {
            return array($filterexamid);
        } else {
            if (preg_match("/%%FILTER_EXAMID:([^%]+)%%/i", $finalelements, $output)) {
                $replace = ' AND ' . $output[1] . ' = ' . $filterexamid;
                return str_replace('%%FILTER_EXAMID:' . $output[1] . '%%', $replace, $finalelements);
            }
        }
        return $finalelements;
    }
    public function filter_data($selectoption = true){
        global $DB, $CFG;
        $properties = new stdClass();
        $properties->courseid = SITEID;

        $reportclassname = 'block_learnerscript\lsreports\report_' . $this->report->type;
        $reportclass = new $reportclassname($this->report, $properties);

        if ($this->report->type != 'sql') {
            $components = (new \block_learnerscript\local\ls)->cr_unserialize($this->report->components);
        } else {
            $examidlist = array_keys($DB->get_records('local_exams'));
        }

        $examidoptions = array();
        if($selectoption){
            $examidoptions[0] = $this->singleselection ?
                get_string('filter_examid', 'block_learnerscript') : get_string('select') .' '. get_string('examid', 'block_learnerscript');
        }

        if (empty($examidlist)) {
            $lang = current_language();
            if($lang == 'en'){
                $examids = $DB->get_records_select('local_exams', '', array(), '', 'id, exam');
            }else{
                $examids = $DB->get_records_sql("SELECT lo.id, lo.examnamearabic AS exam FROM local_exams lo");
            }
            foreach ($examids as $tp) {
                $examidoptions[$tp->id] = format_string($tp->exam);
            }
        }
        return $examidoptions;
    }
    public function selected_filter($selected) {
        $filterdata = $this->filter_data();
        return $filterdata[$selected];
    }
    public function print_filter(&$mform) {
        $examidoptions = $this->filter_data(); 
        if ((!$this->placeholder || $this->filtertype == 'basic') && COUNT($examidoptions) > 1) {
            unset($examidoptions[0]);
        }
        $select = $mform->addElement('select', 'filter_examid', get_string('examid', 'block_learnerscript'), $examidoptions, array('data-select2' => 1));
        $select->setHiddenLabel(true);
        $mform->setType('filter_examid', PARAM_INT);
    }

}
