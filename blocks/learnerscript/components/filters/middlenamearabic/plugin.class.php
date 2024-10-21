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

class plugin_middlenamearabic extends pluginbase {

    public function init() {
        $this->form = false;
        $this->unique = true; 
        $this->placeholder = true;
        $this->singleselection = true;
        $this->fullname = get_string('middlenamearabic', 'block_learnerscript');
        $this->reporttypes = array('middlenamearabicprofiles');
        $this->filtertype = 'custom';
        if (!empty($this->reportclass->basicparams)) {
            foreach ($this->reportclass->basicparams as $basicparam) {
                if ($basicparam['name'] == 'middlenamearabic') {
                    $this->filtertype = 'basic';
                }
            }
        }
    }

    public function summary($data) {
        return get_string('filtermiddlenamearabic_summary', 'block_learnerscript');
    }

    public function execute($finalelements, $data, $filters) {

        $filtermiddlenamearabic = isset($filters['filter_middlenamearabic']) ? $filters['filter_middlenamearabic'] : 0;
        if (!$filtermiddlenamearabic) {
            return $finalelements;
        }

        if ($this->report->type != 'sql') {
            return array($filtermiddlenamearabic);
        } else {
            if (preg_match("/%%FILTER_MIDDLENAMEARABIC:([^%]+)%%/i", $finalelements, $output)) {
                $replace = ' AND ' . $output[1] . ' = ' . $filtermiddlenamearabic;
                return str_replace('%%FILTER_MIDDLENAMEARABIC:' . $output[1] . '%%', $replace, $finalelements);
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
            $middlenamearabiclist = array_keys($DB->get_records('local_users'));
        }

        $middlenamearabicoptions = array();
        if($selectoption){
            $middlenamearabicoptions[0] = $this->singleselection ?
                get_string('filter_middlenamearabic', 'block_learnerscript') : get_string('select') .' '. get_string('middlenamearabic', 'block_learnerscript');
        }

        if (empty($middlenamearabiclist)) {
            $middlenamearabics = $DB->get_records_sql('SELECT le.id, le.middlenamearabic FROM {local_users} le');
            foreach ($middlenamearabics as $e) {
                $middlenamearabicoptions[$e->middlenamearabic] = format_string($e->middlenamearabic);
            }
        }
        return $middlenamearabicoptions;
    }
    public function selected_filter($selected) {
        $filterdata = $this->filter_data();
        return $filterdata[$selected];
    }
    public function print_filter(&$mform) {
        $middlenamearabicoptions = $this->filter_data(); 
        if ((!$this->placeholder || $this->filtertype == 'basic') && COUNT($middlenamearabicoptions) > 1) {
            unset($middlenamearabicoptions[0]);
        }
        $select = $mform->addElement('select', 'filter_middlenamearabic', get_string('middlenamearabic', 'block_learnerscript'), $middlenamearabicoptions, array('data-select2' => 1));
        $select->setHiddenLabel(true);
        $mform->setType('filter_middlenamearabic', PARAM_INT);
    }

}
