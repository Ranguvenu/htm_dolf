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

class plugin_transactiontype extends pluginbase {

    public function init() {
        $this->form = false;
        $this->unique = true; 
        $this->placeholder = true;
        $this->singleselection = true;
        $this->fulltransactiontype = get_string('transactiontype', 'block_learnerscript');
        $this->reporttypes = array('');
        $this->filtertype = 'custom';
        if (!empty($this->reportclass->basicparams)) {
            foreach ($this->reportclass->basicparams as $basicparam) {
                if ($basicparam['transactiontype'] == 'transactiontype') {
                    $this->filtertype = 'basic';
                }
            }
        }
    }

    public function summary($data) {
        return get_string('filtertransactiontype_summary', 'block_learnerscript');
    }

    public function execute($finalelements, $data, $filters) {

        $filtertransactiontype = isset($filters['filter_transactiontype']) ? $filters['filter_transactiontype'] : 0;
        if (!$filtertransactiontype) {
            return $finalelements;
        }

        if ($this->report->type != 'sql') {
            return array($filtertransactiontype);
        } else {
            if (preg_match("/%%FILTER_TRANSACTIONTYPE:([^%]+)%%/i", $finalelements, $output)) {
                $replace = ' AND ' . $output[1] . ' = ' . $filtertransactiontype;
                return str_replace('%%FILTER_TRANSACTIONTYPE:' . $output[1] . '%%', $replace, $finalelements);
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
            $transactiontypelist = array_keys($DB->get_records('local_transactiontype'));
        }

        $transactiontypeoptions = array();
        if($selectoption){
            $transactiontypeoptions[0] = $this->singleselection ?
                get_string('filter_transactiontype', 'block_learnerscript') : get_string('filter_transactiontype', 'block_learnerscript');
        }
        if (empty($transactiontypelist)) {
            $lang = current_language();
            if($this->report->type == 'revenue'){
                $transactiontypeoptions = [''=> get_string('filter_transactiontype', 'block_learnerscript'), 'sale' => get_string('purchase', 'block_learnerscript'), 'refund'=>get_string('cancel', 'block_learnerscript')];
             }else if($this->report->type == 'traineerefundpayments'){
                $transactiontypeoptions = [''=> get_string('filter_transactiontype', 'block_learnerscript'), 'refund'=>get_string('cancel', 'block_learnerscript')];
             }else{
                $transactiontypeoptions = [''=> get_string('filter_transactiontype', 'block_learnerscript'),'purchase' => get_string('purchase', 'block_learnerscript'),'reschedule' => get_string('reschedule', 'block_learnerscript'),'replacement' => get_string('replacement', 'block_learnerscript'),'cancel' => get_string('cancel', 'block_learnerscript')];
             }
          
        }
        return $transactiontypeoptions;
    }
    public function selected_filter($selected) {
        $filterdata = $this->filter_data();
        return $filterdata[$selected];
    }
    public function print_filter(&$mform) {
        $transactiontypeoptions = $this->filter_data(); 
        if ((!$this->placeholder || $this->filtertype == 'basic') && COUNT($transactiontypeoptions) > 1) {
            unset($transactiontypeoptions[0]);
        }
        $select = $mform->addElement('select', 'filter_transactiontype', get_string('transactiontype', 'block_learnerscript'), $transactiontypeoptions, array('data-select2' => 1));
        $select->setHiddenLabel(true);
        $mform->setType('filter_transactiontype', PARAM_INT);
    }

}
