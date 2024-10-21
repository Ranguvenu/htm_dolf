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

class plugin_operation extends pluginbase {

    public function init() {
        $this->form = false;
        $this->unique = true; 
        $this->placeholder = true;
        $this->singleselection = true;
        $this->fulloperation = get_string('operation', 'block_learnerscript');
        $this->reporttypes = array('');
        $this->filtertype = 'custom';
        if (!empty($this->reportclass->basicparams)) {
            foreach ($this->reportclass->basicparams as $basicparam) {
                if ($basicparam['operation'] == 'operation') {
                    $this->filtertype = 'basic';
                }
            }
        }
    }

    public function summary($data) {
        return get_string('filteroperation_summary', 'block_learnerscript');
    }

    public function execute($finalelements, $data, $filters) {

        $filteroperation = isset($filters['filter_operation']) ? $filters['filter_operation'] : 0;
        if (!$filteroperation) {
            return $finalelements;
        }

        if ($this->report->type != 'sql') {
            return array($filteroperation);
        } else {
            if (preg_match("/%%FILTER_OPERATION:([^%]+)%%/i", $finalelements, $output)) {
                $replace = ' AND ' . $output[1] . ' = ' . $filteroperation;
                return str_replace('%%FILTER_OPERATION:' . $output[1] . '%%', $replace, $finalelements);
            }
        }
        return $finalelements;
    }
    public function filter_data($selectoption = true){
        global $DB, $CFG;
        $properties = new stdClass();
        $properties->courseid = SITEID;

        $reportclassoperation = 'block_learnerscript\lsreports\report_' . $this->report->type;
        $reportclass = new $reportclassoperation($this->report, $properties);

        if ($this->report->type != 'sql') {
            $components = (new \block_learnerscript\local\ls)->cr_unserialize($this->report->components);
        } else {
            $operationlist = array_keys($DB->get_records('local_operation'));
        }

        $operationoptions = array();
        if($selectoption){
            $operationoptions[0] = $this->singleselection ?
                get_string('filter_operation', 'block_learnerscript') : get_string('filter_operation', 'block_learnerscript');
        }
        if (empty($operationlist)) {
            $lang = current_language();

            if($this->report->type == 'revenue'){
                 $operationoptions = [''=> get_string('filter_operation', 'block_learnerscript'), 'sale' => get_string('sale', 'block_learnerscript'), 'refund'=>get_string('refund', 'block_learnerscript')];
             }else{
                $operationoptions = [''=> get_string('filter_operation', 'block_learnerscript'),'sale' => get_string('sale', 'block_learnerscript')];
             }
           
        }
        return $operationoptions;
    }
    public function selected_filter($selected) {
        $filterdata = $this->filter_data();
        return $filterdata[$selected];
    }
    public function print_filter(&$mform) {
        $operationoptions = $this->filter_data(); 
        if ((!$this->placeholder || $this->filtertype == 'basic') && COUNT($operationoptions) > 1) {
            unset($operationoptions[0]);
        }
        $select = $mform->addElement('select', 'filter_operation', get_string('operation', 'block_learnerscript'), $operationoptions, array('data-select2' => 1));
        $select->setHiddenLabel(true);
        $mform->setType('filter_operation', PARAM_INT);
    }

}
