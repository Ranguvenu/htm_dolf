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

class plugin_profilecode extends pluginbase {

    public function init() {
        $this->form = false;
        $this->unique = true; 
        $this->placeholder = true;
        $this->singleselection = true;
        $this->fullname = get_string('filterprofilecode', 'block_learnerscript');
        $this->reporttypes = array('offerings');
        $this->filtertype = 'custom';
        if (!empty($this->reportclass->basicparams)) {
            foreach ($this->reportclass->basicparams as $basicparam) {
                if ($basicparam['name'] == 'profilecode') {
                    $this->filtertype = 'basic';
                }
            }
        }
    }

    public function summary($data) {
        return get_string('filterprofilecode_summary', 'block_learnerscript');
    }

    public function execute($finalelements, $data, $filters) {

        $filtercode = isset($filters['filter_profilecode']) ? $filters['filter_profilecode'] : 0;
        if (!$filtercode) {
            return $finalelements;
        }

        if ($this->report->type != 'sql') {
            return array($filtercode);
        } else {
            if (preg_match("/%%FILTER_PROFILECODE:([^%]+)%%/i", $finalelements, $output)) {
                $replace = ' AND ' . $output[1] . ' = ' . $filtercode;
                return str_replace('%%FILTER_PROFILECODE:' . $output[1] . '%%', $replace, $finalelements);
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
            $codelist = array_keys($DB->get_records('local_exam_profiles'));
        }

        $codeoptions = array();

        if (empty($codelist)) {
            if($selectoption){
                    $codeoptions[0] = $this->singleselection ?
                        get_string('filter_profilecode', 'block_learnerscript') : get_string('select') .' '. get_string('profilecode', 'block_learnerscript');
                }
                $codes = $DB->get_records_sql("SELECT DISTINCT lt.profilecode, lt.id FROM {local_exam_profiles} lt");
                foreach ($codes as $tp) {
                    $codeoptions[$tp->profilecode] = format_string($tp->profilecode);
                }
            }            
        return $codeoptions;
    }
    public function selected_filter($selected) {
        $filterdata = $this->filter_data();
        return $filterdata[$selected];
    }
    public function print_filter(&$mform) {
        $codeoptions = $this->filter_data(); 
        if ((!$this->placeholder || $this->filtertype == 'basic') && COUNT($codeoptions) > 1) {
            unset($codeoptions[0]);
        }
        $select = $mform->addElement('select', 'filter_profilecode', get_string('profilecode', 'block_learnerscript'), $codeoptions, array('data-select2' => 1));
        $select->setHiddenLabel(true);
        $mform->setType('filter_profilecode', PARAM_INT);
    }

}
