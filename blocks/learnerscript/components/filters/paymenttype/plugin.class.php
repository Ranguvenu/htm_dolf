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

class plugin_paymenttype extends pluginbase {

    public function init() {
        $this->form = false;
        $this->unique = true; 
        $this->placeholder = true;
        $this->singleselection = true;
        $this->fullpaymenttype = get_string('paymenttype', 'block_learnerscript');
        $this->reporttypes = array('');
        $this->filtertype = 'custom';
        if (!empty($this->reportclass->basicparams)) {
            foreach ($this->reportclass->basicparams as $basicparam) {
                if ($basicparam['paymenttype'] == 'paymenttype') {
                    $this->filtertype = 'basic';
                }
            }
        }
    }

    public function summary($data) {
        return get_string('filterpaymenttype_summary', 'block_learnerscript');
    }

    public function execute($finalelements, $data, $filters) {

        $filterpaymenttype = isset($filters['filter_paymenttype']) ? $filters['filter_paymenttype'] : 0;
        if (!$filterpaymenttype) {
            return $finalelements;
        }

        if ($this->report->type != 'sql') {
            return array($filterpaymenttype);
        } else {
            if (preg_match("/%%FILTER_PAYMENTTYPE:([^%]+)%%/i", $finalelements, $output)) {
                $replace = ' AND ' . $output[1] . ' = ' . $filterpaymenttype;
                return str_replace('%%FILTER_PAYMENTTYPE:' . $output[1] . '%%', $replace, $finalelements);
            }
        }
        return $finalelements;
    }
    public function filter_data($selectoption = true){
        global $DB, $CFG;
        $properties = new stdClass();
        $properties->courseid = SITEID;

        $reportclasspaymenttype = 'block_learnerscript\lsreports\report_' . $this->report->type;
        $reportclass = new $reportclasspaymenttype($this->report, $properties);

        if ($this->report->type != 'sql') {
            $components = (new \block_learnerscript\local\ls)->cr_unserialize($this->report->components);
        } else {
            $paymenttypelist = array_keys($DB->get_records('local_paymenttype'));
        }

        $paymenttypeoptions = array();
        if($selectoption){
            $paymenttypeoptions[0] = $this->singleselection ?
                get_string('filter_paymenttype', 'block_learnerscript') : get_string('filter_paymenttype', 'block_learnerscript');
        }
        if (empty($paymenttypelist)) {
            $lang = current_language();

            if($this->report->type == 'revenue' || $this->report->type == 'traineerefundpayments'){
                 $paymenttypeoptions = [''=> get_string('filter_paymenttype', 'block_learnerscript'),'prepaid'=>get_string('prepaid', 'block_learnerscript')];
            }else{
                $paymenttypeoptions = [''=> get_string('filter_paymenttype', 'block_learnerscript'),get_string('postpaid', 'block_learnerscript')];
            }
           
        }
        return $paymenttypeoptions;
    }
    public function selected_filter($selected) {
        $filterdata = $this->filter_data();
        return $filterdata[$selected];
    }
    public function print_filter(&$mform) {
        $paymenttypeoptions = $this->filter_data(); 
        if ((!$this->placeholder || $this->filtertype == 'basic') && COUNT($paymenttypeoptions) > 1) {
            unset($paymenttypeoptions[0]);
        }
        $select = $mform->addElement('select', 'filter_paymenttype', get_string('paymenttype', 'block_learnerscript'), $paymenttypeoptions, array('data-select2' => 1));
        $select->setHiddenLabel(true);
        $mform->setType('filter_paymenttype', PARAM_INT);
    }

}
