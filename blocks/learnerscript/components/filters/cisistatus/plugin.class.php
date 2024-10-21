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
 * @author: Sudharani S
 * @date: 2023
 */
namespace block_learnerscript\lsreports;
use block_learnerscript\local\pluginbase;
use stdClass;

class plugin_cisistatus extends pluginbase {

    public function init() {
        $this->form = false;
        $this->unique = true; 
        $this->placeholder = true;
        $this->singleselection = true;
        $this->fullstatus = get_string('cisistatus', 'block_learnerscript');
        $this->reporttypes = array('sql');
        $this->filtertype = 'custom';
        if (!empty($this->reportclass->basicparams)) {
            foreach ($this->reportclass->basicparams as $basicparam) {
                if ($basicparam['status'] == 'status') {
                    $this->filtertype = 'basic';
                }
            }
        }
    }

    public function summary($data) {
        return get_string('filtercisistatus_summary', 'block_learnerscript');
    }

    public function execute($finalelements, $data, $filters) {

        $filterstatus = isset($filters['filter_cisistatus']) ? $filters['filter_cisistatus'] : 0;
        if (!$filterstatus) {
            return $finalelements;
        }

        if ($this->report->type != 'sql') {
            return array($filterstatus);
        } else {
            if (preg_match("/%%FILTER_CISISTATUS:([^%]+)%%/i", $finalelements, $output)) {
                $replace = ' AND ' . $output[1] . ' = ' . $filterstatus;
                return str_replace('%%FILTER_CISISTATUS:' . $output[1] . '%%', $replace, $finalelements);
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
            $cisistatuslist = array();
        }

       $cisistatusoptions =array();
        if (empty($cisistatuslist)) {
            $cisistatusoptions = ['' => get_string('filter_cisistatus', 'block_learnerscript'),'successful' => get_string('success', 'block_learnerscript'), 'failed' => get_string('failed', 'block_learnerscript'), 'created' => get_string('create', 'block_learnerscript')];
        }

        return $cisistatusoptions;
    }
    public function selected_filter($selected) {
        $filterdata = $this->filter_data();
        return $filterdata[$selected];
    }
    public function print_filter(&$mform) {
        $cisistatusoptions = $this->filter_data(); 
        if ((!$this->placeholder || $this->filtertype == 'basic') && COUNT($cisistatusoptions) > 1) {
            unset($cisistatusoptions[0]);
        }
        $select = $mform->addElement('select', 'filter_cisistatus', get_string('status', 'block_learnerscript'), $cisistatusoptions, array('data-select2' => 1));
        $select->setHiddenLabel(true);
        $mform->setType('filter_cisistatus', PARAM_INT);
    }

}
