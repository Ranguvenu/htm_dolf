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

class plugin_orgsegment extends pluginbase {

    public function init() {
        $this->form = false;
        $this->unique = true; 
        $this->placeholder = true;
        $this->singleselection = true;
        $this->orgsegment = get_string('orgsegment', 'block_learnerscript');
        $this->reporttypes = array('orgsegmentprofiles');
        $this->filtertype = 'custom';
        if (!empty($this->reportclass->basicparams)) {
            foreach ($this->reportclass->basicparams as $basicparam) {
                if ($basicparam['name'] == 'orgsegment') {
                    $this->filtertype = 'basic';
                }
            }
        }
    }

    public function summary($data) {
        return get_string('filterorgsegment_summary', 'block_learnerscript');
    }

    public function execute($finalelements, $data, $filters) {

        $filterorgsegment = isset($filters['filter_orgsegment']) ? $filters['filter_orgsegment'] : 0;
        if (!$filterorgsegment) {
            return $finalelements;
        }

        if ($this->report->type != 'sql') {
            return array($filterorgsegment);
        } else {
            if (preg_match("/%%FILTER_ORGSEGMENT:([^%]+)%%/i", $finalelements, $output)) {
                $replace = ' AND ' . $output[1] . ' = ' . $filterorgsegment;
                return str_replace('%%FILTER_ORGSEGMENT:' . $output[1] . '%%', $replace, $finalelements);
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
            $orgsegmentlist = array_keys($DB->get_records('local_segment'));
        }

        $orgsegmentoptions = array();
        if($selectoption){
            $orgsegmentoptions[0] = $this->singleselection ?
                get_string('filter_orgsegment', 'block_learnerscript') : get_string('select') .' '. get_string('orgsegment', 'block_learnerscript');
        }

        if (empty($orgsegmentlist)) {
            $lang = current_language();
            if ($lang == 'en'){
                $orgsegments = $DB->get_records_sql('SELECT le.id, le.title FROM {local_segment} le');

            }else{
                $orgsegments = $DB->get_records_sql('SELECT le.id, le.titlearabic AS title FROM {local_segment} le');

            }   
            foreach ($orgsegments as $e) {
                $orgsegmentoptions[$e->id] = format_string($e->title);
            }
        }
        return $orgsegmentoptions;
    }
    public function selected_filter($selected) {
        $filterdata = $this->filter_data();
        return $filterdata[$selected];
    }
    public function print_filter(&$mform) {
        $orgsegmentoptions = $this->filter_data(); 
        if ((!$this->placeholder || $this->filtertype == 'basic') && COUNT($orgsegmentoptions) > 1) {
            unset($orgsegmentoptions[0]);
        }
        $select = $mform->addElement('select', 'filter_orgsegment', get_string('orgsegment', 'block_learnerscript'), $orgsegmentoptions, array('data-select2' => 1));
        $select->setHiddenLabel(true);
        $mform->setType('filter_orgsegment', PARAM_INT);
    }

}
