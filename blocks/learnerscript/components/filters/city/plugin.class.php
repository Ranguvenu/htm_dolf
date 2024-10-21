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

class plugin_city extends pluginbase {

    public function init() {
        $this->form = false;
        $this->unique = true; 
        $this->placeholder = true;
        $this->singleselection = true;
        $this->fullcity = get_string('city', 'block_learnerscript');
        $this->reporttypes = array();
        $this->filtertype = 'custom';
        if (!empty($this->reportclass->basicparams)) {
            foreach ($this->reportclass->basicparams as $basicparam) {
                if ($basicparam['city'] == 'city') {
                    $this->filtertype = 'basic';
                }
            }
        }
    }

    public function summary($data) {
        return get_string('filtercity_summary', 'block_learnerscript');
    }

    public function execute($finalelements, $data, $filters) {

        $filtercity = isset($filters['filter_city']) ? $filters['filter_city'] : 0;
        if (!$filtercity) {
            return $finalelements;
        }

        if ($this->report->type != 'sql') {
            return array($filtercity);
        } else {
            if (preg_match("/%%FILTER_CITY:([^%]+)%%/i", $finalelements, $output)) {
                $replace = ' AND ' . $output[1] . ' = ' . $filtercity;
                return str_replace('%%FILTER_CITY:' . $output[1] . '%%', $replace, $finalelements);
            }
        }
        return $finalelements;
    }
    public function filter_data($selectoption = true){
        global $DB, $CFG;
        $properties = new stdClass();
        $properties->courseid = SITEID;

        $reportclasscity = 'block_learnerscript\lsreports\report_' . $this->report->type;
        $reportclass = new $reportclasscity($this->report, $properties);

        if ($this->report->type != 'sql') {
            $components = (new \block_learnerscript\local\ls)->cr_unserialize($this->report->components);
        } else {
            $citylist = array_keys($DB->get_records('local_hall'));
        }

        $cityoptions = array();
        if($selectoption){
            $cityoptions[0] = $this->singleselection ?
                get_string('filter_city', 'block_learnerscript') : get_string('select') .' '. get_string('city', 'block_learnerscript');
        }

        if (empty($citylist)) {
            if ($this->report->type == 'offerings') {
                $cityoptions = array_merge(array('0' => get_string('filter_halllocation', 'block_learnerscript')), (new \local_hall\hall())->listofcities($city));
            } else {
                if($this->report->type == 'hall'){
                    $citys = $DB->get_records_sql("SELECT DISTINCT city AS id, city FROM {local_users} WHERE 1 = 1");

                }else if($this->report->type == 'userapproval'){
                    $citys = $DB->get_records_sql("SELECT DISTINCT city AS id, city FROM {local_users} WHERE 1 = 1");

                }else{
                    $citys = $DB->get_records_sql("SELECT DISTINCT city AS id, city FROM {hall} WHERE 1 = 1");

                }
                foreach ($citys as $e) {
                    $cityoptions[$e->city] = format_string($e->city);
                }
            }
        }

        return $cityoptions;
    }
    public function selected_filter($selected) {
        $filterdata = $this->filter_data();
        return $filterdata[$selected];
    }
    public function print_filter(&$mform) {
        $cityoptions = $this->filter_data(); 
        if ((!$this->placeholder || $this->filtertype == 'basic') && COUNT($cityoptions) > 1) {
            unset($cityoptions[0]);
        }
        $select = $mform->addElement('select', 'filter_city', get_string('city', 'block_learnerscript'), $cityoptions, array('data-select2' => 1));
        $select->setHiddenLabel(true);
        $mform->setType('filter_city', PARAM_INT);
    }

}
