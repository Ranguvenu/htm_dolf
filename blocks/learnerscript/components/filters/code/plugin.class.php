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

class plugin_code extends pluginbase {

    public function init() {
        $this->form = false;
        $this->unique = true; 
        $this->placeholder = true;
        $this->singleselection = true;
        $this->fullname = get_string('filtercode', 'block_learnerscript');
        $this->reporttypes = array('offerings');
        $this->filtertype = 'custom';
        if (!empty($this->reportclass->basicparams)) {
            foreach ($this->reportclass->basicparams as $basicparam) {
                if ($basicparam['name'] == 'code') {
                    $this->filtertype = 'basic';
                }
            }
        }
    }

    public function summary($data) {
        return get_string('filtercode_summary', 'block_learnerscript');
    }

    public function execute($finalelements, $data, $filters) {

        $filtercode = isset($filters['filter_code']) ? $filters['filter_code'] : 0;
        if (!$filtercode) {
            return $finalelements;
        }

        if ($this->report->type != 'sql') {
            return array($filtercode);
        } else {
            if (preg_match("/%%FILTER_CODE:([^%]+)%%/i", $finalelements, $output)) {
                $replace = ' AND ' . $output[1] . ' = ' . $filtercode;
                return str_replace('%%FILTER_CODE:' . $output[1] . '%%', $replace, $finalelements);
            }
        }
        return $finalelements;
    }
    // public function filter_data($selectoption = true){
    public function filter_data($selectoption = true, $request = array()){

        global $DB, $CFG;
        $properties = new stdClass();
        $properties->courseid = SITEID;

        $reportclassname = 'block_learnerscript\lsreports\report_' . $this->report->type;
        $reportclass = new $reportclassname($this->report, $properties);

        if ($this->report->type != 'sql') {
            $components = (new \block_learnerscript\local\ls)->cr_unserialize($this->report->components);
        } else {
            if ($this->report->type == 'enrolledtrainees') {
                $codelist = array_keys($DB->get_records('tp_offerings'));
            } else if ($this->report->type == 'enrolledexaminer') {
                $codelist = array_keys($DB->get_records('local_exam_profiles'));
            } else if ($this->report->type == 'halls') {
                $codelist = array_keys($DB->get_records('hall'));
            } else if ($this->report->type == 'competencies' || $this->report->type == 'usercompetencies') {
                $codelist = array_keys($DB->get_records('local_competencies'));
            } else {
                $codelist = array_keys($DB->get_records('local_trainingprogram'));
            }
        }

        $codeoptions = array();

        if (empty($codelist)) {
            if ($this->report->type == 'enrolledtrainees' || $this->report->type == 'programenrol' || $this->report->type == 'offeringsessions') {
                if($selectoption){
                    $codeoptions[0] = $this->singleselection ?
                        get_string('filter_offeringcode', 'block_learnerscript') : get_string('select') .' '. get_string('offeringcode', 'block_learnerscript');
                }
                $codes = $DB->get_records_sql("SELECT DISTINCT lt.id, lt.code FROM {tp_offerings} lt");
                foreach ($codes as $tp) {
                    $codeoptions[$tp->id] = format_string($tp->code);
                }
            } else if ($this->report->type == 'enrolledexaminer') {
                if($selectoption){
                    $codeoptions[0] = $this->singleselection ?
                        get_string('filter_profilecode', 'block_learnerscript') : get_string('select') .' '. get_string('profilecode', 'block_learnerscript');
                }
                $codes = $DB->get_records_sql("SELECT DISTINCT lt.id, lt.profilecode AS code FROM {local_exam_profiles} lt");
                foreach ($codes as $tp) {
                    $codeoptions[$tp->id] = format_string($tp->code);
                }
            } else if ($this->report->type == 'meetingparticipants') {
                if(empty($request['filter_trainingprogram']) || !isset($request['filter_trainingprogram'])){
                    if($selectoption){
                        $codeoptions[0] = $this->singleselection ?
                            get_string('filter_offeringcode', 'block_learnerscript') : get_string('select') .' '. get_string('offeringcode', 'block_learnerscript');
                    }
                    $trainingid = $DB->get_field_sql("SELECT id FROM {local_trainingprogram} WHERE 1 = 1 ORDER BY id ASC LIMIT 0, 1");
                    $codes = $DB->get_records_sql("SELECT DISTINCT lt.id, lt.code FROM {tp_offerings} lt WHERE lt.trainingid = $trainingid");
                    foreach ($codes as $tp) {
                        $codeoptions[$tp->id] = format_string($tp->code);
                    }                    
                }else{
                    if($selectoption){
                        $codeoptions[0] = $this->singleselection ?
                            get_string('filter_offeringcode', 'block_learnerscript') : get_string('select') .' '. get_string('offeringcode', 'block_learnerscript');
                    }
                    $codes = $DB->get_records_sql("SELECT DISTINCT id, code FROM {tp_offerings} ", ['trainingid' => $request['filter_trainingprogram']]);
                    foreach ($codes as $tp) {
                        $codeoptions[$tp->id] = format_string($tp->code);
                    }
                }

            } else if ($this->report->type == 'halls') {
                if($selectoption){
                    $codeoptions[0] = $this->singleselection ?
                        get_string('filter_hallcode', 'block_learnerscript') : get_string('select') .' '. get_string('hallcode', 'block_learnerscript');
                }
                $codes = $DB->get_records_sql("SELECT DISTINCT lt.id, lt.code AS code FROM {hall} lt");
                foreach ($codes as $tp) {
                    $codeoptions[$tp->code] = format_string($tp->code);
                }
            } else if ($this->report->type == 'userevents') {
                if($selectoption){
                    $codeoptions[0] = $this->singleselection ?
                        get_string('filter_hallcode', 'block_learnerscript') : get_string('select') .' '. get_string('hallcode', 'block_learnerscript');
                }
                $codes = $DB->get_records_sql("SELECT DISTINCT lt.id, lt.code AS code FROM {local_events} lt");
                foreach ($codes as $tp) {
                    $codeoptions[$tp->code] = format_string($tp->code);
                }
            }else if ($this->report->type == 'events') {
                if($selectoption){
                    $codeoptions[0] = $this->singleselection ?
                        get_string('filter_code', 'block_learnerscript') : get_string('select') .' '. get_string('filter_code', 'block_learnerscript');
                }
                $codes = $DB->get_records_sql("SELECT DISTINCT lt.code AS id, lt.code AS code FROM {local_events} lt");
                foreach ($codes as $tp) {
                    $codeoptions[$tp->code] = format_string($tp->code);
                }
            }   else if ($this->report->type == 'learningtrackinfo') {
                if($selectoption){
                    $codeoptions[0] = $this->singleselection ?
                        get_string('filter_learningtrackcode', 'block_learnerscript') : get_string('select') .' '. get_string('learningtrackcode', 'block_learnerscript');
                }
                $codes = $DB->get_records_sql("SELECT DISTINCT lt.code AS id, lt.code AS code FROM {local_learningtracks} lt");
                foreach ($codes as $tp) {
                    $codeoptions[$tp->id] = format_string($tp->code);
                }
            }   else if ($this->report->type == 'exams' || $this->report->type == 'compexams' || $this->report->type == 'examenrol') {
                if($selectoption){
                    $codeoptions[0] = $this->singleselection ?
                        get_string('filter_learningtrackcode', 'block_learnerscript') : get_string('select') .' '. get_string('learningtrackcode', 'block_learnerscript');
                }
                $codes = $DB->get_records_sql("SELECT DISTINCT lt.code AS id, lt.code AS code FROM {local_exams} lt");
                foreach ($codes as $tp) {
                    $codeoptions[$tp->id] = format_string($tp->code);
                }
            }  else if ($this->report->type == 'cpd') {
                if($selectoption){
                    $codeoptions[0] = $this->singleselection ?
                        get_string('filter_code', 'block_learnerscript') : get_string('select') .' '. get_string('learningtrackcode', 'block_learnerscript');
                }
                $codes = $DB->get_records_sql("SELECT DISTINCT lt.code AS id, lt.code AS code FROM {local_cpd} lt");
                foreach ($codes as $tp) {
                    $codeoptions[$tp->code] = format_string($tp->code);
                }
            }  else if ($this->report->type == 'competencies' || $this->report->type == 'usercompetencies') {
                if($selectoption){
                    $codeoptions[0] = $this->singleselection ?
                        get_string('filter_compcode', 'block_learnerscript') : get_string('select') .' '. get_string('compcode', 'block_learnerscript');
                }
                $codes = $DB->get_records_sql("SELECT DISTINCT lt.code AS id, lt.code AS code FROM {local_competencies} lt");
                foreach ($codes as $tp) {
                    $codeoptions[$tp->id] = format_string($tp->code);
                }
            } else if ($this->report->type == 'offerings') {
                if($selectoption){
                    $codeoptions[0] = $this->singleselection ?
                        get_string('filter_offeringcode', 'block_learnerscript') : get_string('select') .' '. get_string('offeringcode', 'block_learnerscript');
                }
                $codes = $DB->get_records_sql("SELECT DISTINCT lt.id, lt.code FROM {tp_offerings} lt");
                foreach ($codes as $tp) {
                    $codeoptions[$tp->code] = format_string($tp->code);
                }
            }else {
                if($selectoption){
                    $codeoptions[0] = $this->singleselection ?
                        get_string('filter_code', 'block_learnerscript') : get_string('select') .' '. get_string('code', 'block_learnerscript');
                }
                $codes = $DB->get_records_sql("SELECT DISTINCT lt.code, lt.id FROM {local_trainingprogram} lt");
                foreach ($codes as $tp) {
                    $codeoptions[$tp->code] = format_string($tp->code);
                }
            }            
        }
        return $codeoptions;
    }
    public function selected_filter($selected, $request) {
        $filterdata = $this->filter_data(true, $request);
        return $filterdata[$selected];
    }
    public function print_filter(&$mform) {
        $request = array_merge($_POST, $_GET);
        $codeoptions = $this->filter_data(true, $request); 
        if ((!$this->placeholder || $this->filtertype == 'basic') && COUNT($codeoptions) > 1) {
            unset($codeoptions[0]);
        }
        $select = $mform->addElement('select', 'filter_code', get_string('code', 'block_learnerscript'), $codeoptions, array('data-select2' => 1));
        $select->setHiddenLabel(true);
        $mform->setType('filter_code', PARAM_INT);
    }

}
