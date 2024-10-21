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

class plugin_cpdfilters extends pluginbase {

    public function init() {
        $this->form = false;
        $this->unique = true; 
        $this->placeholder = true;
        $this->singleselection = true;
        $this->fullname = get_string('filtercpdfilters', 'block_learnerscript');
        $this->reporttypes = array('competencies');
        $this->filtertype = 'custom';
        $this->selecttype = 'advanced';
        if (!empty($this->reportclass->basicparams)) {
            foreach ($this->reportclass->basicparams as $basicparam) {
                if ($basicparam['name'] == 'cpdfilters') {
                    $this->filtertype = 'basic';
                }
            }
        }
    }

    public function summary($data) {
        return get_string('filtercpdfilters_summary', 'block_learnerscript');
    }

    public function execute($finalelements, $data, $filters) {

        $filtercpdfilters = isset($filters['filter_cpdfilters']) ? $filters['filter_cpdfilters'] : 0;
        if (!$filtertrainingtype) {
            return $finalelements;
        }

        if ($this->report->type != 'sql') {
            return array($filtercpdfilters);
        } else {
            if (preg_match("/%%FILTER_CPDFILTERS:([^%]+)%%/i", $finalelements, $output)) {
                $replace = ' AND ' . $output[1] . ' = ' . $filtertrainingtype;
                return str_replace('%%FILTER_CPDFILTERS:' . $output[1] . '%%', $replace, $finalelements);
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
            $cpdfilterslist = array();
        }

        $cpdfilteroptions = array();
        if($selectoption){
            $cpdfilteroptions[0] = $this->singleselection ?
                get_string('filter_cpdfilters', 'block_learnerscript') : get_string('select') .' '. get_string('cpdfilters', 'block_learnerscript');
        }
        return $cpdfilteroptions;
    }
    public function selected_filter($selected) {
        $filterdata = $this->filter_data();
        return $filterdata[$selected];
    }
    public function print_filter(&$mform) {
        $cpdfilteroptions = $this->filter_data(); 
        if ((!$this->placeholder || $this->filtertype == 'basic') && COUNT($cpdfilteroptions) > 1) {
            unset($cpdfilteroptions[0]);
        }
        $select = $mform->addElement('select', 'filter_cpdfilters', get_string('cpdfilters', 'block_learnerscript'), $cpdfilteroptions, array('data-select2' => 1));
        $select->setHiddenLabel(true);
        $mform->setType('filter_cpdfilters', PARAM_INT);
    }

}