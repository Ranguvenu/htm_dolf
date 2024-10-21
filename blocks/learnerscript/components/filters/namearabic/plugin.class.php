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

class plugin_namearabic extends pluginbase {

    public function init() {
        $this->form = false;
        $this->unique = true; 
        $this->placeholder = true;
        $this->singleselection = true;
        $this->fullname = get_string('namearabic', 'block_learnerscript');
        $this->reporttypes = array('');
        $this->filtertype = 'custom';
        if (!empty($this->reportclass->basicparams)) {
            foreach ($this->reportclass->basicparams as $basicparam) {
                if ($basicparam['name'] == 'namearabic') {
                    $this->filtertype = 'basic';
                }
            }
        }
    }

    public function summary($data) {
        return get_string('filternamearabic_summary', 'block_learnerscript');
    }

    public function execute($finalelements, $data, $filters) {

        $filternamearabic = isset($filters['filter_namearabic']) ? $filters['filter_namearabic'] : 0;
        if (!$filternamearabic) {
            return $finalelements;
        }

        if ($this->report->type != 'sql') {
            return array($filternamearabic);
        } else {
            if (preg_match("/%%FILTER_NAMEARABIC:([^%]+)%%/i", $finalelements, $output)) {
                $replace = ' AND ' . $output[1] . ' = ' . $filternamearabic;
                return str_replace('%%FILTER_NAMEARABIC:' . $output[1] . '%%', $replace, $finalelements);
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
            $namearabiclist = array_keys($DB->get_records('local_learningtracks'));
        }

        $namearabicoptions = array();
        if($selectoption){
            $namearabicoptions[0] = $this->singleselection ?
                get_string('filter_namearabic', 'block_learnerscript') : get_string('select') .' '. get_string('namearabic', 'block_learnerscript');
        }
        if ($this->report->type == 'compprograms' || $this->report->type == 'trainingprograms') {
            if (empty($namearabiclist)) {
                $namearabics = $DB->get_records_select('local_trainingprogram', '', array(), '', 'id, namearabic');

                foreach ($namearabics as $e) {
                    $namearabicoptions[$e->namearabic] = format_string($e->namearabic);
                }
            }
        } else {
            if (empty($namearabiclist)) {
                $namearabics = $DB->get_records_select('local_learningtracks', '', array(), '', 'id, namearabic');

                foreach ($namearabics as $e) {
                    $namearabicoptions[$e->namearabic] = format_string($e->namearabic);
                }
            }
        }
        return $namearabicoptions;
    }
    public function selected_filter($selected) {
        $filterdata = $this->filter_data();
        return $filterdata[$selected];
    }
    public function print_filter(&$mform) {
        $namearabicoptions = $this->filter_data(); 
        if ((!$this->placeholder || $this->filtertype == 'basic') && COUNT($namearabicoptions) > 1) {
            unset($namearabicoptions[0]);
        }
        $select = $mform->addElement('select', 'filter_namearabic', get_string('namearabic', 'block_learnerscript'), $namearabicoptions, array('data-select2' => 1));
        $select->setHiddenLabel(true);
        $mform->setType('filter_namearabic', PARAM_INT);
    }

}
