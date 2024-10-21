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
 * @author: Sudharani Sadula
 * @date: 2023
 */
namespace block_learnerscript\lsreports;
use block_learnerscript\local\pluginbase;
use stdClass;

class plugin_paymentdate extends pluginbase {

    public function init() {
        $this->form = false;
        $this->unique = true; 
        $this->placeholder = true;
        $this->singleselection = true;
        $this->fullname = get_string('filterpaymentdate', 'block_learnerscript');
        $this->reporttypes = array('revenue','transaction');
        $this->filtertype = 'custom';
        if (!empty($this->reportclass->basicparams)) {
            foreach ($this->reportclass->basicparams as $basicparam) {
                if ($basicparam['name'] == 'paymentdate') {
                    $this->filtertype = 'basic';
                }
            }
        }
    }

    public function summary($data) {
        return get_string('filterpaymentdate_summary', 'block_learnerscript');
    }

    public function execute($finalelements, $data, $filters) {

        $filterpaymentdate = isset($filters['filter_paymentdate']) ? $filters['filter_paymentdate'] : 0;
        if (!$filterpaymentdate) {
            return $finalelements;
        }

        if ($this->report->type != 'sql') {
            return array($filterpaymentdate);
        } else {
            if (preg_match("/%%FILTER_PAYMENTDATE:([^%]+)%%/i", $finalelements, $output)) {
                $replace = ' AND ' . $output[1] . ' = ' . $filterpaymentdate;
                return str_replace('%%FILTER_PAYMENTDATE:' . $output[1] . '%%', $replace, $finalelements);
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
            $component = (new \block_learnerscript\local\ls)->cr_unserialize($this->report->paymentdate);
        } else {
            $paymentdatelist = array();
        }
        $paymentdateoptions = array();
        if($selectoption){
            $paymentdateoptions[0] = $this->singleselection ?
                get_string('filter_paymentdate', 'block_learnerscript') : get_string('select') .' '. get_string('paymentdate', 'block_learnerscript');
        }

        if (empty($paymentdatelist)) {
            $paymentdateoptions = '';
        }
        return $paymentdateoptions;
    }
    public function selected_filter($selected) {
        $filterdata = $this->filter_data();
        return $filterdata[$selected];
    }
    public function print_filter(&$mform) {
        $paymentdateoptions = $this->filter_data(); 
        if($this->report->type == 'traineerefundpayments'){
            $mform->addElement('date_selector', 'paymentfrom', get_string('transactiondatefrom', 'block_learnerscript'),array('optional'=>true));
            $mform->setType('paymentfrom', PARAM_RAW);

            $mform->addElement('date_selector', 'paymentto', get_string('transactiondateto', 'block_learnerscript'),array('optional'=>true));
            $mform->setType('paymentto', PARAM_RAW);
        }else{
            $mform->addElement('date_selector', 'paymentfrom', get_string('paymentfrom', 'block_learnerscript'),array('optional'=>true));
            $mform->setType('paymentfrom', PARAM_RAW);

            $mform->addElement('date_selector', 'paymentto', get_string('paymentto', 'block_learnerscript'),array('optional'=>true));
            $mform->setType('paymentto', PARAM_RAW);
        }
       
    }

}
