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

class plugin_allmodules extends pluginbase {

    public function init() {
        $this->form = false;
        $this->unique = true; 
        $this->placeholder = true;
        $this->singleselection = true;
        $this->fullstatus = get_string('allmodules', 'block_learnerscript');
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
        return get_string('filterallmodules_summary', 'block_learnerscript');
    }

    public function execute($finalelements, $data, $filters) {

        $filterstatus = isset($filters['filter_allmodules']) ? $filters['filter_allmodules'] : 0;
        if (!$filterstatus) {
            return $finalelements;
        }

        if ($this->report->type != 'sql') {
            return array($filterstatus);
        } else {
            if (preg_match("/%%FILTER_ALLMODULES:([^%]+)%%/i", $finalelements, $output)) {
                $replace = ' AND ' . $output[1] . ' = ' . $filterstatus;
                return str_replace('%%FILTER_ALLMODULES:' . $output[1] . '%%', $replace, $finalelements);
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
            $allmoduleslist = array();
        }

       $allmodulesoptions =array();
        if (empty($allmoduleslist)) {
            $allmodulesoptions = ['' => get_string('filter_allmodules', 'block_learnerscript'),'successful' => get_string('success', 'block_learnerscript'), 'failed' => get_string('failed', 'block_learnerscript'), 'created' => get_string('create', 'block_learnerscript')];
        }

        return $allmodulesoptions;
    }
    public function selected_filter($selected) {
        $filterdata = $this->filter_data();
        return $filterdata[$selected];
    }
    public function print_filter(&$mform) {
        $allmodulesoptions = $this->filter_data(); 
        if ((!$this->placeholder || $this->filtertype == 'basic') && COUNT($allmodulesoptions) > 1) {
            unset($allmodulesoptions[0]);
        }

       $select = $mform->addElement('checkbox', 'filter_allmodules_exams', '', get_string('showexams', 'block_learnerscript'), array('data-select2' => 1));
       $mform->setDefault('filter_allmodules_exams', 1);
       $select = $mform->addElement('checkbox', 'filter_allmodules_events', '', get_string('showevents', 'block_learnerscript'), array('data-select2' => 1));
       $mform->setDefault('filter_allmodules_events', 1);
       $select = $mform->addElement('checkbox', 'filter_allmodules_trainingprograms', '', get_string('showtrainingprogram', 'block_learnerscript'), array('data-select2' => 1));
       $mform->setDefault('filter_allmodules_trainingprograms', 1);
       $select = $mform->addElement('checkbox', 'filter_allmodules_learningtracks', '', get_string('showlearningtrack', 'block_learnerscript'), array('data-select2' => 1));
       $mform->setDefault('filter_allmodules_learningtracks', 1);
       $select = $mform->addElement('checkbox', 'filter_allmodules_cpd', '', get_string('showcpd', 'block_learnerscript'), array('data-select2' => 1));
       $mform->setDefault('filter_allmodules_cpd', 1);
        $select = $mform->addElement('checkbox', 'filter_allmodules_certificates', '', get_string('certificatesonly', 'block_learnerscript'), array('data-select2' => 1));
        $mform->setDefault('filter_allmodules_certificates', 0);
    }

}
