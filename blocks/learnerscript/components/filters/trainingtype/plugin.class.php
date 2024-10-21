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

class plugin_trainingtype extends pluginbase {

    public function init() {
        $this->form = false;
        $this->unique = true; 
        $this->placeholder = true;
        $this->singleselection = true;
        $this->fullname = get_string('filtertrainingtype', 'block_learnerscript');
        $this->reporttypes = array('competencies');
        $this->filtertype = 'custom';
        if (!empty($this->reportclass->basicparams)) {
            foreach ($this->reportclass->basicparams as $basicparam) {
                if ($basicparam['name'] == 'trainingtype') {
                    $this->filtertype = 'basic';
                }
            }
        }
    }

    public function summary($data) {
        return get_string('filtertrainingtype_summary', 'block_learnerscript');
    }

    public function execute($finalelements, $data, $filters) {

        $filtertrainingtype = isset($filters['filter_trainingtype']) ? $filters['filter_trainingtype'] : 0;
        if (!$filtertrainingtype) {
            return $finalelements;
        }

        if ($this->report->type != 'sql') {
            return array($filtertrainingtype);
        } else {
            if (preg_match("/%%FILTER_TRAININGTYPE:([^%]+)%%/i", $finalelements, $output)) {
                $replace = ' AND ' . $output[1] . ' = ' . $filtertrainingtype;
                return str_replace('%%FILTER_TRAININGTYPE:' . $output[1] . '%%', $replace, $finalelements);
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
            $trainingtypelist = array();
        }

        $trainingtypeoptions = array();
        if($selectoption){
            $trainingtypeoptions[0] = $this->singleselection ?
                get_string('filter_trainingtype', 'block_learnerscript') : get_string('select') .' '. get_string('trainingtype', 'block_learnerscript');
        }

        if (empty($trainingtypelist)) {
            // if($this->report->type == 'revenue' || $this->report->type == 'transaction'){
                $trainingtypeoptions = array('' => get_string('filter_trainingtype', 'block_learnerscript'), 'online' => get_string('scheduleonline','block_learnerscript'),'offline' => get_string('scheduleoffline','block_learnerscript'), 'elearning' => get_string('scheduleelearning','block_learnerscript'));
            // }else{
            //     $trainingtypeoptions = array(0 => get_string('filter_trainingtype', 'block_learnerscript'),'online' => get_string('online', 'block_learnerscript'), 'offline' => get_string('offline', 'block_learnerscript'));
            // }
            
        }
        return $trainingtypeoptions;
    }
    public function selected_filter($selected) {
        $filterdata = $this->filter_data();
        return $filterdata[$selected];
    }
    public function print_filter(&$mform) {
        $trainingtypeoptions = $this->filter_data(); 
        if ((!$this->placeholder || $this->filtertype == 'basic') && COUNT($trainingtypeoptions) > 1) {
            unset($trainingtypeoptions[0]);
        }
        $select = $mform->addElement('select', 'filter_trainingtype', get_string('trainingtype', 'block_learnerscript'), $trainingtypeoptions, array('data-select2' => 1));
        $select->setHiddenLabel(true);
        $mform->setType('filter_trainingtype', PARAM_INT);
    }

}
