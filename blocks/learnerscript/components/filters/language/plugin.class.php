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
use stdClass;

class plugin_language extends pluginbase {

    public function init() {
        $this->form = false;
        $this->unique = true; 
        $this->placeholder = true;
        $this->singleselection = true;
        $this->fullname = get_string('filterlanguage', 'block_learnerscript');
        $this->reporttypes = array('offerings');
        $this->filtertype = 'custom';
        if (!empty($this->reportclass->basicparams)) {
            foreach ($this->reportclass->basicparams as $basicparam) {
                if ($basicparam['name'] == 'language') {
                    $this->filtertype = 'basic';
                }
            }
        }
    }

    public function summary($data) {
        return get_string('filterlanguage_summary', 'block_learnerscript');
    }

    public function execute($finalelements, $data, $filters) {

        $filterlanguage = isset($filters['filter_language']) ? $filters['filter_language'] : 0;
        if (!$filterlanguage) {
            return $finalelements;
        }

        if ($this->report->type != 'sql') {
            return array($filterlanguage);
        } else {
            if (preg_match("/%%FILTER_LANGUAGES:([^%]+)%%/i", $finalelements, $output)) {
                $replace = ' AND ' . $output[1] . ' = ' . $filterlanguage;
                return str_replace('%%FILTER_LANGUAGES:' . $output[1] . '%%', $replace, $finalelements);
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
            $languagelist = array_keys($DB->get_records('local_trainingprogram'));
        }

        $languageoptions = array();
        if($selectoption){
            $languageoptions[-1] = $this->singleselection ?
                get_string('filter_languages', 'block_learnerscript') : get_string('select') .' '. get_string('languages', 'block_learnerscript');
        }

        if (empty($languagelist)) {
            if($this->report->type == 'events'){
                $languageoptions = array(-1 => get_string('filter_languages', 'block_learnerscript'), 1 => get_string('arabic','block_learnerscript'), 2 => get_string('english', 'block_learnerscript'));

            }else if($this->report->type == 'userevents'){
                $languageoptions = array(-1 => get_string('filter_languages', 'block_learnerscript'), 1 => get_string('arabic','block_learnerscript'), 2 => get_string('english', 'block_learnerscript'));

            }else{
                $languageoptions = array(-1 => get_string('filter_languages', 'block_learnerscript'), 0 => get_string('arabic','block_learnerscript'), 1 => get_string('english', 'block_learnerscript'));

            }
        }
        return $languageoptions;
    }
    public function selected_filter($selected) {
        $filterdata = $this->filter_data();
        return $filterdata[$selected];
    }
    public function print_filter(&$mform) {
        $languageoptions = $this->filter_data(); 
        if ((!$this->placeholder || $this->filtertype == 'basic') && COUNT($languageoptions) > 1) {
            unset($languageoptions[0]);
        }
        $select = $mform->addElement('select', 'filter_language', get_string('languages', 'block_learnerscript'), $languageoptions, array('data-select2' => 1));
        $select->setHiddenLabel(true);
        $mform->setType('filter_language', PARAM_INT);
    }

}
