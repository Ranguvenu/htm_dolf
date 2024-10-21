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
 * @date: 2022
 */
namespace block_learnerscript\lsreports;
use block_learnerscript\local\pluginbase;
use stdClass;

class plugin_components extends pluginbase {

    public function init() {
        $this->form = false;
        $this->unique = true; 
        $this->placeholder = true;
        $this->singleselection = true;
        $this->fullname = get_string('filtercomponents', 'block_learnerscript');
        $this->reporttypes = array('offerings');
        $this->filtertype = 'custom';
        if (!empty($this->reportclass->basicparams)) {
            foreach ($this->reportclass->basicparams as $basicparam) {
                if ($basicparam['name'] == 'components') {
                    $this->filtertype = 'basic';
                }
            }
        }
    }

    public function summary($data) {
        return get_string('filtercomponents_summary', 'block_learnerscript');
    }

    public function execute($finalelements, $data, $filters) {

        $filtercomponents = isset($filters['filter_components']) ? $filters['filter_components'] : 0;
        if (!$filtercomponents) {
            return $finalelements;
        }

        if ($this->report->type != 'sql') {
            return array($filtercomponents);
        } else {
            if (preg_match("/%%FILTER_COMPONENTS:([^%]+)%%/i", $finalelements, $output)) {
                $replace = ' AND ' . $output[1] . ' = ' . $filtercomponents;
                return str_replace('%%FILTER_COMPONENTS:' . $output[1] . '%%', $replace, $finalelements);
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
            $component = (new \block_learnerscript\local\ls)->cr_unserialize($this->report->components);
        } else {
            $componentslist = array();
        }

        $componetoptions = array();
        if($selectoption){
            $componetoptions[0] = $this->singleselection ?
                get_string('filter_components', 'block_learnerscript') : get_string('select') .' '. get_string('components', 'block_learnerscript');
        }

        if (empty($componentslist)) {
            $componetoptions = array(0 => get_string('filter_components', 'block_learnerscript'), 1 => 'User Approval', 2 => 'Training Program', 3 => 'Competency', 4 => 'Exams', 5 => 'Sector', 6 => 'Organization');

            // $componetoptions = array_merge($componetoptions[0], $componetoptions);
        }
        return $componetoptions;
    }
    public function selected_filter($selected) {
        $filterdata = $this->filter_data();
        return $filterdata[$selected];
    }
    public function print_filter(&$mform) {
        $componetoptions = $this->filter_data(); 
        if ((!$this->placeholder || $this->filtertype == 'basic') && COUNT($componetoptions) > 1) {
            unset($componetoptions[0]);
        }
        $select = $mform->addElement('select', 'filter_components', get_string('components', 'block_learnerscript'), $componetoptions, array('data-select2' => 1));
        $select->setHiddenLabel(true);
        $mform->setType('filter_components', PARAM_INT);
    }

}
