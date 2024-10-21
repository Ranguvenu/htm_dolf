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
 * @date: 2023
 */
namespace block_learnerscript\lsreports;
use block_learnerscript\local\pluginbase;
use stdClass;

class plugin_examnamearabic extends pluginbase {

    public function init() {
        $this->form = false;
        $this->unique = true; 
        $this->placeholder = true;
        $this->singleselection = true;
        $this->fullname = get_string('examnamearabic', 'block_learnerscript');
        $this->reporttypes = array('examnamearabicprofiles');
        $this->filtertype = 'custom';
        if (!empty($this->reportclass->basicparams)) {
            foreach ($this->reportclass->basicparams as $basicparam) {
                if ($basicparam['name'] == 'examnamearabic') {
                    $this->filtertype = 'basic';
                }
            }
        }
    }

    public function summary($data) {
        return get_string('filterexamnamearabic_summary', 'block_learnerscript');
    }

    public function execute($finalelements, $data, $filters) {

        $filterexamnamearabic = isset($filters['filter_examnamearabic']) ? $filters['filter_examnamearabic'] : 0;
        if (!$filterexamnamearabic) {
            return $finalelements;
        }

        if ($this->report->type != 'sql') {
            return array($filterexamnamearabic);
        } else {
            if (preg_match("/%%FILTER_EXAMNAMEARABIC:([^%]+)%%/i", $finalelements, $output)) {
                $replace = ' AND ' . $output[1] . ' = ' . $filterexamnamearabic;
                return str_replace('%%FILTER_EXAMNAMEARABIC:' . $output[1] . '%%', $replace, $finalelements);
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
            $examnamearabiclist = array_keys($DB->get_records('local_users'));
        }

        $examnamearabicoptions = array();
        if($selectoption){
            $examnamearabicoptions[0] = $this->singleselection ?
                get_string('filter_examnamearabic', 'block_learnerscript') : get_string('select') .' '. get_string('examnamearabic', 'block_learnerscript');
        }

        if (empty($examnamearabiclist)) {
            $examnamearabics = $DB->get_records_sql('SELECT le.id, le.examnamearabic FROM {local_exams} le');
            foreach ($examnamearabics as $e) {
                $examnamearabicoptions[$e->examnamearabic] = format_string($e->examnamearabic);
            }
        }
        return $examnamearabicoptions;
    }
    public function selected_filter($selected) {
        $filterdata = $this->filter_data();
        return $filterdata[$selected];
    }
    public function print_filter(&$mform) {
        $examnamearabicoptions = $this->filter_data(); 
        if ((!$this->placeholder || $this->filtertype == 'basic') && COUNT($examnamearabicoptions) > 1) {
            unset($examnamearabicoptions[0]);
        }
        $select = $mform->addElement('select', 'filter_examnamearabic', get_string('examnamearabic', 'block_learnerscript'), $examnamearabicoptions, array('data-select2' => 1));
        $select->setHiddenLabel(true);
        $mform->setType('filter_examnamearabic', PARAM_INT);
    }

}
