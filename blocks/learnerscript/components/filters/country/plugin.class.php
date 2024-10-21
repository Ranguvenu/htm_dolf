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

class plugin_country extends pluginbase {

    public function init() {
        $this->form = false;
        $this->unique = true; 
        $this->placeholder = true;
        $this->singleselection = true;
        $this->fullcountry = get_string('country', 'block_learnerscript');
        $this->reporttypes = array('');
        $this->filtertype = 'custom';
        if (!empty($this->reportclass->basicparams)) {
            foreach ($this->reportclass->basicparams as $basicparam) {
                if ($basicparam['country'] == 'country') {
                    $this->filtertype = 'basic';
                }
            }
        }
    }

    public function summary($data) {
        return get_string('filtercountry_summary', 'block_learnerscript');
    }

    public function execute($finalelements, $data, $filters) {

        $filtercountry = isset($filters['filter_country']) ? $filters['filter_country'] : 0;
        if (!$filtercountry) {
            return $finalelements;
        }

        if ($this->report->type != 'sql') {
            return array($filtercountry);
        } else {
            if (preg_match("/%%FILTER_COUNTRY:([^%]+)%%/i", $finalelements, $output)) {
                $replace = ' AND ' . $output[1] . ' = ' . $filtercountry;
                return str_replace('%%FILTER_COUNTRY:' . $output[1] . '%%', $replace, $finalelements);
            }
        }
        return $finalelements;
    }
    public function filter_data($selectoption = true){
        global $DB, $CFG;
        $properties = new stdClass();
        $properties->courseid = SITEID;

        $reportclasscountry = 'block_learnerscript\lsreports\report_' . $this->report->type;
        $reportclass = new $reportclasscountry($this->report, $properties);

        if ($this->report->type != 'sql') {
            $components = (new \block_learnerscript\local\ls)->cr_unserialize($this->report->components);
        } else {
            $countrylist = array_keys($DB->get_records('local_users'));
        }

        $countryoptions = array();
        if($selectoption){
            $countryoptions[0] = $this->singleselection ?
                get_string('filter_country', 'block_learnerscript') : get_string('select') .' '. get_string('country', 'block_learnerscript');
        }
        if (empty($countrylist)) {
            $countrys = $DB->get_records_select('local_users', '', array(), '', 'id, country');

            foreach ($countrys as $e) {
                $countryoptions[$e->country] = format_string($e->country);
            }
        }
        return $countryoptions;
    }
    public function selected_filter($selected) {
        $filterdata = $this->filter_data();
        return $filterdata[$selected];
    }
    public function print_filter(&$mform) {
        $countryoptions = $this->filter_data(); 
        if ((!$this->placeholder || $this->filtertype == 'basic') && COUNT($countryoptions) > 1) {
            unset($countryoptions[0]);
        }
        $select = $mform->addElement('select', 'filter_country', get_string('country', 'block_learnerscript'), $countryoptions, array('data-select2' => 1));
        $select->setHiddenLabel(true);
        $mform->setType('filter_country', PARAM_INT);
    }

}
