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

class plugin_jobrole extends pluginbase {

    public function init() {
        $this->form = false;
        $this->unique = true; 
        $this->placeholder = true;
        $this->singleselection = true;
        $this->fullname = get_string('filterjobrole', 'block_learnerscript');
        $this->reporttypes = array('offerings');
        $this->filtertype = 'custom';
        if (!empty($this->reportclass->basicparams)) {
            foreach ($this->reportclass->basicparams as $basicparam) {
                if ($basicparam['name'] == 'jobrole') {
                    $this->filtertype = 'basic';
                }
            }
        }
    }

    public function summary($data) {
        return get_string('filterjobrole_summary', 'block_learnerscript');
    }

    public function execute($finalelements, $data, $filters) {

        $filterjobrole = isset($filters['filter_jobrole']) ? $filters['filter_jobrole'] : 0;
        if (!$filterjobrole) {
            return $finalelements;
        }

        if ($this->report->type != 'sql') {
            return array($filterjobrole);
        } else {
            if (preg_match("/%%FILTER_JOBROLE:([^%]+)%%/i", $finalelements, $output)) {
                $replace = ' AND ' . $output[1] . ' = ' . $filterjobrole;
                return str_replace('%%FILTER_JOBROLE:' . $output[1] . '%%', $replace, $finalelements);
            }
        }
        return $finalelements;
    }
    public function filter_data($selectoption = true){
        global $DB, $CFG;
        $properties = new stdClass();
        $properties->jobrole = SITEID;

        $reportclassname = 'block_learnerscript\lsreports\report_' . $this->report->type;
        $reportclass = new $reportclassname($this->report, $properties);

        if ($this->report->type != 'sql') {
            $components = (new \block_learnerscript\local\ls)->cr_unserialize($this->report->components);
        } else {
            $jobrolelist = array_keys($DB->get_records('local_jobrole_level'));
        }

        $jobroleoptions = array();
        if($selectoption){
            $jobroleoptions[0] = $this->singleselection ?
                get_string('filter_jobrole', 'block_learnerscript') : get_string('select') .' '. get_string('jobrole', 'block_learnerscript');
        }

        if (empty($jobrolelist)) {
            $lang = current_language();
            if ($lang == 'en'){
                $jobroles = $DB->get_records_select('local_jobrole_level', '', array(), '', 'id, title');

            }else{
                $jobroles = $DB->get_records_sql("SELECT titlearabic AS title FROM {local_jobrole_level}");

            }
            foreach ($jobroles as $tp) {
                $jobroleoptions[$tp->id] = format_string($tp->title);
            }
        }
        return $jobroleoptions;
    }
    public function selected_filter($selected) {
        $filterdata = $this->filter_data();
        return $filterdata[$selected];
    }
    public function print_filter(&$mform) {
        $jobroleoptions = $this->filter_data(); 
        if ((!$this->placeholder || $this->filtertype == 'basic') && COUNT($jobroleoptions) > 1) {
            unset($jobroleoptions[0]);
        }
        $select = $mform->addElement('select', 'filter_jobrole', get_string('jobrole', 'block_learnerscript'), $jobroleoptions, array('data-select2' => 1));
        $select->setHiddenLabel(true);
        $mform->setType('filter_jobrole', PARAM_INT);
    }

}
