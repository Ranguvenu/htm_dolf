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

class plugin_name extends pluginbase {

    public function init() {
        $this->form = false;
        $this->unique = true; 
        $this->placeholder = true;
        $this->singleselection = true;
        $this->fullname = get_string('name', 'block_learnerscript');
        $this->reporttypes = array('');
        $this->filtertype = 'custom';
        if (!empty($this->reportclass->basicparams)) {
            foreach ($this->reportclass->basicparams as $basicparam) {
                if ($basicparam['name'] == 'name') {
                    $this->filtertype = 'basic';
                }
            }
        }
    }

    public function summary($data) {
        return get_string('filtername_summary', 'block_learnerscript');
    }

    public function execute($finalelements, $data, $filters) {

        $filtername = isset($filters['filter_name']) ? $filters['filter_name'] : 0;
        if (!$filtername) {
            return $finalelements;
        }

        if ($this->report->type != 'sql') {
            return array($filtername);
        } else {
            if (preg_match("/%%FILTER_NAME:([^%]+)%%/i", $finalelements, $output)) {
                $replace = ' AND ' . $output[1] . ' = ' . $filtername;
                return str_replace('%%FILTER_NAME:' . $output[1] . '%%', $replace, $finalelements);
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
            if ($this->report->type == 'learningtrackinfo') {
                $namelist = array_keys($DB->get_records('local_learningtracks'));
            } else {
                $namelist = array_keys($DB->get_records('hall'));
            }
        }

        $nameoptions = array();
        if ($this->report->type == 'learningtrackinfo') {
            if($selectoption){
                $nameoptions[0] = $this->singleselection ?
                    get_string('filter_name', 'block_learnerscript') : get_string('select') .' '. get_string('name', 'block_learnerscript');
            }
            if (empty($namelist)) {
                $names = $DB->get_records_select('local_learningtracks', '', array(), '', 'id, name');

                foreach ($names as $e) {
                    $nameoptions[$e->name] = format_string($e->name);
                }
            }
        } else if ($this->report->type == 'competencies' || $this->report->type == 'usercompetencies') {
            if($selectoption){
                $nameoptions[0] = $this->singleselection ?
                    get_string('filter_name', 'block_learnerscript') : get_string('select') .' '. get_string('name', 'block_learnerscript');
            }
            if (empty($namelist)) {
                $names = $DB->get_records_select('local_competencies', '', array(), '', 'id, name');
                foreach ($names as $e) {
                    $nameoptions[$e->name] = format_string($e->name);
                }
            }
        }  else if ($this->report->type == 'compprograms' || $this->report->type == 'trainingprograms') {
            if($selectoption){
                $nameoptions[0] = $this->singleselection ?
                    get_string('filter_trainingprogram', 'block_learnerscript') : get_string('select') .' '. get_string('trainingprogram', 'block_learnerscript');
            }
            if (empty($namelist)) {
                $lang= current_language();
                if ($lang == 'en') {
                    $names = $DB->get_records_select('local_trainingprogram', '', array(), '', 'id, name');
                } else {
                    $names = $DB->get_records_sql("SELECT id, namearabic AS name FROM {local_trainingprogram} WHERE 1 = 1");
                }
                foreach ($names as $e) {
                    $nameoptions[$e->name] = format_string($e->name);
                }
            }
        } else {
            if($selectoption){
                $nameoptions[0] = $this->singleselection ?
                    get_string('filter_hall', 'block_learnerscript') : get_string('select') .' '. get_string('hall', 'block_learnerscript');
            }
            if (empty($namelist)) {
                $names = $DB->get_records_select('hall', '', array(), '', 'id, name');

                foreach ($names as $e) {
                    $nameoptions[$e->name] = format_string($e->name);
                }
            }
        }
        return $nameoptions;
    }
    public function selected_filter($selected) {
        $filterdata = $this->filter_data();
        return $filterdata[$selected];
    }
    public function print_filter(&$mform) {
        $nameoptions = $this->filter_data(); 
        if ((!$this->placeholder || $this->filtertype == 'basic') && COUNT($nameoptions) > 1) {
            unset($nameoptions[0]);
        }
        $select = $mform->addElement('select', 'filter_name', get_string('name', 'block_learnerscript'), $nameoptions, array('data-select2' => 1));
        $select->setHiddenLabel(true);
        $mform->setType('filter_name', PARAM_INT);
    }

}
