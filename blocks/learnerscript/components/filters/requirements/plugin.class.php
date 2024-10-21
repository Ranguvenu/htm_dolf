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

class plugin_requirements extends pluginbase {

    public function init() {
        $this->form = false;
        $this->unique = true; 
        $this->placeholder = true;
        $this->singleselection = true;
        $this->fullname = get_string('filterrequirements', 'block_learnerscript');
        $this->reporttypes = array('offerings');
        $this->filtertype = 'custom';
        if (!empty($this->reportclass->basicparams)) {
            foreach ($this->reportclass->basicparams as $basicparam) {
                if ($basicparam['name'] == 'requirements') {
                    $this->filtertype = 'basic';
                }
            }
        }
    }

    public function summary($data) {
        return get_string('filterrequirements_summary', 'block_learnerscript');
    }

    public function execute($finalelements, $data, $filters) {

        $filterrequirements = isset($filters['filter_requirements']) ? $filters['filter_requirements'] : 0;
        if (!$filterrequirements) {
            return $finalelements;
        }

        if ($this->report->type != 'sql') {
            return array($filterrequirements);
        } else {
            if (preg_match("/%%FILTER_REQUIREMENTS:([^%]+)%%/i", $finalelements, $output)) {
                $replace = ' AND ' . $output[1] . ' = ' . $filterrequirements;
                return str_replace('%%FILTER_REQUIREMENTS:' . $output[1] . '%%', $replace, $finalelements);
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
            $requirementslist = array_keys($DB->get_records('local_exams'));
        }

        $requirementsoptions = array();
        if($selectoption){
            $requirementsoptions[0] = $this->singleselection ?
                get_string('filter_requirements', 'block_learnerscript') : get_string('select') .' '. get_string('requirements', 'block_learnerscript');
        }

        if (empty($requirementslist)) {
            $lang = current_language();
            if($lang == 'en'){
                $requirementss = $DB->get_records_select('local_exams', '', array(), '', 'id, exam');

            }else{
                $requirementss = $DB->get_records_select('SELECT le.id, le.examnamearabic AS exam FROM {local_exams} le');

            }

            foreach ($requirementss as $tp) {
                $requirementsoptions[$tp->id] = format_string($tp->exam);
            }
        }
        return $requirementsoptions;
    }
    public function selected_filter($selected) {
        $filterdata = $this->filter_data();
        return $filterdata[$selected];
    }
    public function print_filter(&$mform) {
        $requirementsoptions = $this->filter_data(); 
        if ((!$this->placeholder || $this->filtertype == 'basic') && COUNT($requirementsoptions) > 1) {
            unset($requirementsoptions[0]);
        }
        $select = $mform->addElement('select', 'filter_requirements', get_string('requirements', 'block_learnerscript'), $requirementsoptions, array('data-select2' => 1));
        $select->setHiddenLabel(true);
        $mform->setType('filter_requirements', PARAM_INT);
    }

}
