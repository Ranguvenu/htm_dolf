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

class plugin_programtype extends pluginbase {

    public function init() {
        $this->form = false;
        $this->unique = true; 
        $this->placeholder = true;
        $this->singleselection = true;
        $this->fullname = get_string('programtype', 'block_learnerscript');
        $this->reporttypes = array('productlog');
        $this->filtertype = 'custom';
        if (!empty($this->reportclass->basicparams)) {
            foreach ($this->reportclass->basicparams as $basicparam) {
                if ($basicparam['name'] == 'programtype') {
                    $this->filtertype = 'basic';
                }
            }
        }
    }

    public function summary($data) {
        return get_string('programtype_summary', 'block_learnerscript');
    }

    public function execute($finalelements, $data, $filters) {

        $programtype = isset($filters['filter_programtype']) ? $filters['filter_programtype'] : 0;
        if (!$programtype) {
            return $finalelements;
        }

        if ($this->report->type != 'sql') {
            return array($programtype);
        } else {
            if (preg_match("/%%FILTER_programtype:([^%]+)%%/i", $finalelements, $output)) {
                $replace = ' AND ' . $output[1] . ' = ' . $programtype;
                return str_replace('%%FILTER_programtype:' . $output[1] . '%%', $replace, $finalelements);
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
            $programtypelist = array();
        }

        $programtypeoptions = array();
        if($selectoption){
            $programtypeoptions[0] = $this->singleselection ?
                get_string('filter_programtype', 'block_learnerscript') : get_string('select') .' '. get_string('programtype', 'block_learnerscript');
        }

        if (empty($programtypelist)) {
            if($this->report->type == 'revenue' || $this->report->type == 'transaction'){
                    $programtypeoptions =[-1=>get_string('select') .' '. get_string('programtype', 'block_learnerscript'), 0=>get_string('public','local_trainingprogram'), 1=>get_string('private','local_trainingprogram'),2=>get_string('dedicated','local_trainingprogram')];
                }
            }
           
        return $programtypeoptions;
    }
    public function selected_filter($selected) {
        $filterdata = $this->filter_data();
        return $filterdata[$selected];
    }
    public function print_filter(&$mform) {
        $programtypeoptions = $this->filter_data(); 
        if ((!$this->placeholder || $this->filtertype == 'basic') && COUNT($programtypeoptions) > 1) {
            unset($programtypeoptions[0]);
        }
        $select = $mform->addElement('select', 'filter_programtype', get_string('programtype', 'block_learnerscript'), $programtypeoptions, array('data-select2' => 1));
        $select->setHiddenLabel(true);
        $mform->setType('filter_programtype', PARAM_INT);
    }

}
