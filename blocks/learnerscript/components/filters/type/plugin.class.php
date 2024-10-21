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

class plugin_type extends pluginbase {

    public function init() {
        $this->form = false;
        $this->unique = true; 
        $this->placeholder = true;
        $this->singleselection = true;
        $this->fullname = get_string('filtertype', 'block_learnerscript');
        $this->reporttypes = array('offerings');
        $this->filtertype = 'custom';
        if (!empty($this->reportclass->basicparams)) {
            foreach ($this->reportclass->basicparams as $basicparam) {
                if ($basicparam['name'] == 'type') {
                    $this->filtertype = 'basic';
                }
            }
        }
    }

    public function summary($data) {
        return get_string('filtertype_summary', 'block_learnerscript');
    }

    public function execute($finalelements, $data, $filters) {

        $filtertype = isset($filters['filter_type']) ? $filters['filter_type'] : 0;
        if (!$filtertype) {
            return $finalelements;
        }

        if ($this->report->type != 'sql') {
            return array($filtertype);
        } else {
            if (preg_match("/%%FILTER_TYPE:([^%]+)%%/i", $finalelements, $output)) {
                $replace = ' AND ' . $output[1] . ' = ' . $filtertype;
                return str_replace('%%FILTER_TYPE:' . $output[1] . '%%', $replace, $finalelements);
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
            $typelist = array_keys($DB->get_records('local_exams'));
        }

        $typeoptions = array();
        if ($this->report->type == 'halls') {
            if($selectoption){
                $typeoptions[0] = $this->singleselection ?
                    get_string('filter_type', 'block_learnerscript') : get_string('select') .' '. get_string('type', 'block_learnerscript');
            }

            if (empty($typelist)) {
                $types = [1 => get_string('trainingcourse','local_hall'), 2 => get_string('atest','local_hall'), 3 => get_string('effectiveness','local_hall'), 4 => get_string('other','local_hall')];

                foreach ($types as $key => $value) {
                    $typeoptions[$key] = format_string($value);
                }
            }
        } else if ($this->report->type == 'competencies' || $this->report->type == 'usercompetencies') {
            if($selectoption){
                $typeoptions[0] = $this->singleselection ?
                    get_string('filter_type', 'block_learnerscript') : get_string('select') .' '. get_string('type', 'block_learnerscript');
            }

            if (empty($typelist)) {
                $types = ['corecompetencies' => get_string('corecompetencies','local_competency'), 'technicalcompetencies' => get_string('technicalcompetencies','local_competency'), 'behavioralcompetencies' => get_string('behavioralcompetencies','local_competency'), 'other' => get_string('other','local_competency')];

                foreach ($types as $key => $value) {
                    $typeoptions[$key] = format_string($value);
                }
            }
        }   else if ($this->report->type == 'events') {
            if($selectoption){
                $typeoptions[-1] = $this->singleselection ?
                    get_string('filter_type', 'block_learnerscript') : get_string('select') .' '. get_string('type', 'block_learnerscript');
            }

            if (empty($typelist)) {
                $types = [0 => get_string('symposium','block_learnerscript'), 1 => get_string('forum','block_learnerscript'), 2 => get_string('conference','block_learnerscript'), 3 => get_string('workshop','block_learnerscript'), 4 => get_string('cermony','block_learnerscript')];

                foreach ($types as $key => $value) {
                    $typeoptions[$key] = format_string($value);
                }
            }
        } else {
            if (empty($typelist)) {
                // if($selectoption){
                //     $typeoptions[-1] = $this->singleselection ?
                //         get_string('filter_type', 'block_learnerscript') : get_string('select') .' '. get_string('type', 'block_learnerscript');
                // }

                if($this->report->type == 'offerings' || $this->report->type == 'trainingprograms' || $this->report->type == 'programenrol' || $this->report->type == 'prepostexams' || $this->report->type == 'myprograms' || $this->report->type == 'enrolledtrainees' || $this->report->type == 'compprograms' || $this->report->type == 'attendance'){
                    if($selectoption){
                    $typeoptions[-1] = $this->singleselection ?
                        get_string('filter_offeringtype', 'block_learnerscript') : get_string('select') .' '. get_string('offeringtype', 'block_learnerscript');
                    }
                    $typeoptions = array(-1 => get_string('filter_offeringtype', 'block_learnerscript'), 0 => get_string('public','block_learnerscript'), 1 => get_string('private', 'block_learnerscript'), 2 => get_string('dedicated', 'block_learnerscript'));

                }elseif($this->report->type == 'exams' ||$this->report->type == 'compexams'){
                    if($selectoption){
                        $typeoptions[-1] = $this->singleselection ?
                            get_string('filter_type', 'block_learnerscript') : get_string('select') .' '. get_string('type', 'block_learnerscript');
                    }
                    $types = $DB->get_records_sql("SELECT DISTINCT le.type, le.id FROM {local_exams} le");
                    foreach ($types as $tp) {
                        $typeoptions[$tp->type] = format_string($tp->type);
                    }
                }elseif($this->report->type == 'events'){
                    if($selectoption){
                        $typeoptions[-1] = $this->singleselection ?
                            get_string('filter_type', 'block_learnerscript') : get_string('select') .' '. get_string('type', 'block_learnerscript');
                    }
                    $typeoptions = array(-1 => get_string('filter_type', 'block_learnerscript'),0 => get_string('symposium', 'block_learnerscript'), 1 => get_string('forum','block_learnerscript'), 2 => get_string('conference', 'block_learnerscript'), 3 => get_string('wrokshop', 'block_learnerscript'), 4 => get_string('cermony', 'block_learnerscript'));
                }elseif($this->report->type == 'userevents'){
                    if($selectoption){
                        $typeoptions[-1] = $this->singleselection ?
                            get_string('filter_type', 'block_learnerscript') : get_string('select') .' '. get_string('type', 'block_learnerscript');
                    }
                    $typeoptions = array(-1 => get_string('filter_type', 'block_learnerscript'),0 => get_string('symposium', 'block_learnerscript'), 1 => get_string('forum','block_learnerscript'), 2 => get_string('conference', 'block_learnerscript'), 3 => get_string('wrokshop', 'block_learnerscript'), 4 => get_string('cermony', 'block_learnerscript'));

                }else{
                    if($selectoption){
                        $typeoptions[0] = $this->singleselection ?
                            get_string('filter_type', 'block_learnerscript') : get_string('select') .' '. get_string('type', 'block_learnerscript');
                    }
                    $types = $DB->get_records_sql("SELECT DISTINCT le.type, le.id FROM {local_exams} le");
                    foreach ($types as $tp) {
                        $typeoptions[$tp->type] = format_string($tp->type);
                    }
                }
            }
        }
        return $typeoptions;
    }
    public function selected_filter($selected) {
        $filterdata = $this->filter_data();
        return $filterdata[$selected];
    }
    public function print_filter(&$mform) {
        $typeoptions = $this->filter_data(); 
        if ((!$this->placeholder || $this->filtertype == 'basic') && COUNT($typeoptions) > 1) {
            unset($typeoptions[0]);
        }
        $select = $mform->addElement('select', 'filter_type', get_string('type', 'block_learnerscript'), $typeoptions, array('data-select2' => 1));
        $select->setHiddenLabel(true);
        $mform->setType('filter_type', PARAM_INT);
    }

}
