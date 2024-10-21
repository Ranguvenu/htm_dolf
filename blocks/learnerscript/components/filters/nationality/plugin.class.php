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

class plugin_nationality extends pluginbase {

    public function init() {
        $this->form = false;
        $this->unique = true; 
        $this->placeholder = true;
        $this->singleselection = true;
        $this->fullname = get_string('nationality', 'block_learnerscript');
        $this->reporttypes = array('enrolledidnumberiner', 'enrolledtrainees');
        $this->filtertype = 'custom';
        if (!empty($this->reportclass->basicparams)) {
            foreach ($this->reportclass->basicparams as $basicparam) {
                if ($basicparam['name'] == 'nationality') {
                    $this->filtertype = 'basic';
                }
            }
        }
    }

    public function summary($data) {
        return get_string('filternationality_summary', 'block_learnerscript');
    }

    public function execute($finalelements, $data, $filters) {

        $filternationality = isset($filters['filter_nationality']) ? $filters['filter_nationality'] : 0;
        if (!$filternationality) {
            return $finalelements;
        }

        if ($this->report->type != 'sql') {
            return array($filternationality);
        } else {
            if (preg_match("/%%FILTER_NATIONALITY:([^%]+)%%/i", $finalelements, $output)) {
                $replace = ' AND ' . $output[1] . ' = ' . $filternationality;
                return str_replace('%%FILTER_NATIONALITY:' . $output[1] . '%%', $replace, $finalelements);
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
            $nationalitylist = array_keys($DB->get_records('local_users'));
        }

        $nationalityoptions = array();
        if($selectoption){
            $nationalityoptions[0] = $this->singleselection ?
                get_string('filter_nationality', 'block_learnerscript') : get_string('select') .' '. get_string('nationality', 'block_learnerscript');
        }

        if (empty($nationalitylist)) {
            $nationalitys = $DB->get_records_sql("SELECT DISTINCT nationality FROM {local_users}");

            foreach ($nationalitys as $e) {
                $nationalityoptions[$e->nationality] = format_string($e->nationality);
            }
        }
        return $nationalityoptions;
    }
    public function selected_filter($selected) {
        $filterdata = $this->filter_data();
        return $filterdata[$selected];
    }
    public function print_filter(&$mform) {
        $nationalityoptions = $this->filter_data(); 
        if ((!$this->placeholder || $this->filtertype == 'basic') && COUNT($nationalityoptions) > 1) {
            unset($nationalityoptions[0]);
        }
        $select = $mform->addElement('select', 'filter_nationality', get_string('nationality', 'block_learnerscript'), $nationalityoptions, array('data-select2' => 1));
        $select->setHiddenLabel(true);
        $mform->setType('filter_nationality', PARAM_INT);
    }

}
