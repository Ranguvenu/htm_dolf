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

class plugin_orgorind extends pluginbase {

    public function init() {
        $this->form = false;
        $this->unique = true; 
        $this->placeholder = true;
        $this->singleselection = true;
        $this->fullorgorind = get_string('orgorind', 'block_learnerscript');
        $this->reporttypes = array('');
        $this->filtertype = 'custom';
        if (!empty($this->reportclass->basicparams)) {
            foreach ($this->reportclass->basicparams as $basicparam) {
                if ($basicparam['orgorind'] == 'orgorind') {
                    $this->filtertype = 'basic';
                }
            }
        }
    }

    public function summary($data) {
        return get_string('filterorgorind_summary', 'block_learnerscript');
    }

    public function execute($finalelements, $data, $filters) {

        $filterorgorind = isset($filters['filter_orgorind']) ? $filters['filter_orgorind'] : 0;
        if (!$filterorgorind) {
            return $finalelements;
        }

        if ($this->report->type != 'sql') {
            return array($filterorgorind);
        } else {
            if (preg_match("/%%FILTER_OPERATION:([^%]+)%%/i", $finalelements, $output)) {
                $replace = ' AND ' . $output[1] . ' = ' . $filterorgorind;
                return str_replace('%%FILTER_OPERATION:' . $output[1] . '%%', $replace, $finalelements);
            }
        }
        return $finalelements;
    }
    public function filter_data($selectoption = true){
        global $DB, $CFG;
        $properties = new stdClass();
        $properties->courseid = SITEID;

        $reportclass = 'block_learnerscript\lsreports\report_' . $this->report->type;
        $reportclass = new $reportclass($this->report, $properties);

        if ($this->report->type != 'sql') {
            $components = (new \block_learnerscript\local\ls)->cr_unserialize($this->report->components);
        } else {
            $orgorindlist = array_keys($DB->get_records('local_users'));
        }

        $orgorindoptions = array();
        if($selectoption){
            $orgorindoptions[0] = $this->singleselection ?
                get_string('filter_orgorind', 'block_learnerscript') : get_string('select') .' '. get_string('orgorind', 'block_learnerscript');
        }
        if (empty($orgorindlist)) {            
                $orgorindoptions = ['0'=>  get_string('filter_orgorind', 'block_learnerscript'), '1' => get_string('organization', 'block_learnerscript'), get_string('individual', 'block_learnerscript')];          
        }
        return $orgorindoptions;
    }
    public function selected_filter($selected) {
        $filterdata = $this->filter_data();
        return $filterdata[$selected];
    }
    public function print_filter(&$mform) {
        $orgorindoptions = $this->filter_data(); 
        if ((!$this->placeholder || $this->filtertype == 'basic') && COUNT($orgorindoptions) > 1) {
            unset($orgorindoptions[0]);
        }
        $select = $mform->addElement('select', 'filter_orgorind', get_string('orgorind', 'block_learnerscript'), $orgorindoptions, array('data-select2' => 1));
        $select->setHiddenLabel(true);
        $mform->setType('filter_orgorind', PARAM_INT);
    }

}
