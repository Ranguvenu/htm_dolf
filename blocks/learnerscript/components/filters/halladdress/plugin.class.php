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

class plugin_halladdress extends pluginbase {

    public function init() {
        $this->form = false;
        $this->unique = true; 
        $this->placeholder = true;
        $this->singleselection = true;
        $this->fullname = get_string('filterhalladdress', 'block_learnerscript');
        $this->reporttypes = array('offerings');
        $this->filtertype = 'custom';
        if (!empty($this->reportclass->basicparams)) {
            foreach ($this->reportclass->basicparams as $basicparam) {
                if ($basicparam['name'] == 'halladdress') {
                    $this->filtertype = 'basic';
                }
            }
        }
    }

    public function summary($data) {
        return get_string('filterhalladdress_summary', 'block_learnerscript');
    }

    public function execute($finalelements, $data, $filters) {

        $filterhalladdress = isset($filters['filter_halladdress']) ? $filters['filter_halladdress'] : 0;
        if (!$filterhalladdress) {
            return $finalelements;
        }

        if ($this->report->type != 'sql') {
            return array($filterhalladdress);
        } else {
            if (preg_match("/%%FILTER_HALLADRESS:([^%]+)%%/i", $finalelements, $output)) {
                $replace = ' AND ' . $output[1] . ' = ' . $filterhalladdress;
                return str_replace('%%FILTER_HALLADDRESS:' . $output[1] . '%%', $replace, $finalelements);
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
            $halladdresslist = array_keys($DB->get_records('hall'));
        }

        $halladdressoptions = array();
        if($selectoption){
            $halladdressoptions[0] = $this->singleselection ?
                get_string('filter_halladdress', 'block_learnerscript') : get_string('select') .' '. get_string('halladdress', 'block_learnerscript');
        }

        if (empty($halladdresslist)) {
            $halladdresss = $DB->get_records_sql("SELECT h.name, h.id FROM {hall} h");

            foreach ($halladdresss as $tp) {
                $halladdressoptions[$tp->id] = format_string($tp->name);
            }
        }
        return $halladdressoptions;
    }
    public function selected_filter($selected) {
        $filterdata = $this->filter_data();
        return $filterdata[$selected];
    }
    public function print_filter(&$mform) {
        $halladdressoptions = $this->filter_data(); 
        if ((!$this->placeholder || $this->filtertype == 'basic') && COUNT($halladdressoptions) > 1) {
            unset($halladdressoptions[0]);
        }
        $select = $mform->addElement('select', 'filter_halladdress', get_string('halladdress', 'block_learnerscript'), $halladdressoptions, array('data-select2' => 1));
        $select->setHiddenLabel(true);
        $mform->setType('filter_halladdress', PARAM_INT);
    }

}
