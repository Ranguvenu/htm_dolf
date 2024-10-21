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
 * @author: jahnavi.nanduri@moodle.com
 * @date: 2023
 */
namespace block_learnerscript\lsreports;
use block_learnerscript\local\pluginbase;
use stdClass;

class plugin_offeringstatus extends pluginbase {

    public function init() {
        $this->form = false;
        $this->unique = true; 
        $this->placeholder = true;
        $this->singleselection = true;
        $this->fullname = get_string('filterofferingstatus', 'block_learnerscript');
        $this->reporttypes = array('offerings');
        $this->filtertype = 'custom';
        if (!empty($this->reportclass->basicparams)) {
            foreach ($this->reportclass->basicparams as $basicparam) {
                if ($basicparam['name'] == 'offeringstatus') {
                    $this->filtertype = 'basic';
                }
            }
        }
    }

    public function summary($data) {
        return get_string('filterofferingstatus_summary', 'block_learnerscript');
    }

    public function execute($finalelements, $data, $filters) {

        $filterofferingstatus = isset($filters['filter_offeringstatus']) ? $filters['filter_offeringstatus'] : 0;
        if (!$filterofferingstatus) {
            return $finalelements;
        }

        if ($this->report->type != 'sql') {
            return array($filterofferingstatus);
        } else {
            if (preg_match("/%%FILTER_OFFERINGSTATUS:([^%]+)%%/i", $finalelements, $output)) {
                $replace = ' AND ' . $output[1] . ' = ' . $filterofferingstatus;
                return str_replace('%%FILTER_OFFERINGSTATUS:' . $output[1] . '%%', $replace, $finalelements);
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
            $offeringstatuslist = array_keys($DB->get_records('tp_offerings'));
        }

        $offeringstatusoptions = array();
        if($selectoption){
            $offeringstatusoptions[0] = $this->singleselection ?
                get_string('filter_offeringstatus', 'block_learnerscript') : get_string('select') .' '. get_string('offeringstatus', 'block_learnerscript');
        }

        if (empty($offeringstatuslist)) {
            $offeringstatusoptions = array('' => get_string('filter_offeringstatus', 'block_learnerscript'), '1' => get_string('open','block_learnerscript'), '2' => get_string('financiallyclosed','block_learnerscript'), '3' => get_string('cancelled','block_learnerscript'), '4' => get_string('closed','block_learnerscript'));
        }
        return $offeringstatusoptions;
    }
    public function selected_filter($selected) {
        $filterdata = $this->filter_data();
        return $filterdata[$selected];
    }
    public function print_filter(&$mform) {
        $offeringstatusoptions = $this->filter_data(); 
        if ((!$this->placeholder || $this->filtertype == 'basic') && COUNT($offeringstatusoptions) > 1) {
            unset($offeringstatusoptions[0]);
        }
        $select = $mform->addElement('select', 'filter_offeringstatus', get_string('offeringstatus', 'block_learnerscript'), $offeringstatusoptions, array('data-select2' => 1));
        $select->setHiddenLabel(true);
        $mform->setType('filter_offeringstatus', PARAM_INT);
    }

}
