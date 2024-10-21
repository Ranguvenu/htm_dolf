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

class plugin_firstname extends pluginbase {

    public function init() {
        $this->form = false;
        $this->unique = true; 
        $this->placeholder = true;
        $this->singleselection = true;
        $this->fullname = get_string('firstname', 'block_learnerscript');
        $this->reporttypes = array('firstnameprofiles');
        $this->filtertype = 'custom';
        if (!empty($this->reportclass->basicparams)) {
            foreach ($this->reportclass->basicparams as $basicparam) {
                if ($basicparam['name'] == 'firstname') {
                    $this->filtertype = 'basic';
                }
            }
        }
    }

    public function summary($data) {
        return get_string('filterfirstname_summary', 'block_learnerscript');
    }

    public function execute($finalelements, $data, $filters) {

        $filterfirstname = isset($filters['filter_firstname']) ? $filters['filter_firstname'] : 0;
        if (!$filterfirstname) {
            return $finalelements;
        }

        if ($this->report->type != 'sql') {
            return array($filterfirstname);
        } else {
            if (preg_match("/%%FILTER_FIRSTNAME:([^%]+)%%/i", $finalelements, $output)) {
                $replace = ' AND ' . $output[1] . ' = ' . $filterfirstname;
                return str_replace('%%FILTER_FIRSTNAME:' . $output[1] . '%%', $replace, $finalelements);
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
            $firstnamelist = array_keys($DB->get_records('local_users'));
        }

        $firstnameoptions = array();
        if($selectoption){
            $firstnameoptions[0] = $this->singleselection ?
                get_string('filter_firstname', 'block_learnerscript') : get_string('select') .' '. get_string('firstname', 'block_learnerscript');
        }

        if (empty($firstnamelist)) {
            $lang = current_language();

            if($lang == 'en'){
                $firstnames = $DB->get_records_sql('SELECT le.id, le.firstname FROM {local_users} le');
            }else{
                $firstnames = $DB->get_records_sql('SELECT le.id, le.firstnamearabic AS firstname 
                                                FROM {local_users} le');
            }
            foreach ($firstnames as $e) {
                $firstnameoptions[$e->firstname] = format_string($e->firstname);
            }
        }
        return $firstnameoptions;
    }
    public function selected_filter($selected) {
        $filterdata = $this->filter_data();
        return $filterdata[$selected];
    }
    public function print_filter(&$mform) {
        $firstnameoptions = $this->filter_data(); 
        if ((!$this->placeholder || $this->filtertype == 'basic') && COUNT($firstnameoptions) > 1) {
            unset($firstnameoptions[0]);
        }
        $select = $mform->addElement('select', 'filter_firstname', get_string('firstname', 'block_learnerscript'), $firstnameoptions, array('data-select2' => 1));
        $select->setHiddenLabel(true);
        $mform->setType('filter_firstname', PARAM_INT);
    }

}
