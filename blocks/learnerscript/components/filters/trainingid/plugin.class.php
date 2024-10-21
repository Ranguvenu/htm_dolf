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

class plugin_trainingid extends pluginbase {

    public function init() {
        $this->form = false;
        $this->unique = true; 
        $this->placeholder = true;
        $this->singleselection = true;
        $this->fullname = get_string('filtertrainingid', 'block_learnerscript');
        $this->reporttypes = array('offerings');
        $this->filtertype = 'custom';
        if (!empty($this->reportclass->basicparams)) {
            foreach ($this->reportclass->basicparams as $basicparam) {
                if ($basicparam['name'] == 'trainingid') {
                    $this->filtertype = 'basic';
                }
            }
        }
    }

    public function summary($data) {
        return get_string('filtertrainingid_summary', 'block_learnerscript');
    }

    public function execute($finalelements, $data, $filters) {

        $filtertrainingid = isset($filters['filter_trainingid']) ? $filters['filter_trainingid'] : 0;
        if (!$filtertrainingid) {
            return $finalelements;
        }

        if ($this->report->type != 'sql') {
            return array($filtertrainingid);
        } else {
            if (preg_match("/%%FILTER_TRAININGID:([^%]+)%%/i", $finalelements, $output)) {
                $replace = ' AND ' . $output[1] . ' = ' . $filtertrainingid;
                return str_replace('%%FILTER_TRAININGID:' . $output[1] . '%%', $replace, $finalelements);
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
            $trainingidlist = array_keys($DB->get_records('local_exams'));
        }

        $trainingidoptions = array();
        if($selectoption){
            $trainingidoptions[0] = $this->singleselection ?
                get_string('filter_trainingid', 'block_learnerscript') : get_string('select') .' '. get_string('trainingid', 'block_learnerscript');
        }

        if (empty($trainingidlist)) {
            $lang = current_language();
            if($lang == 'en'){
                $trainingids = $DB->get_records_select('local_trainingprogram', '', array(), '', 'id, name');
            }else{
                $trainingids = $DB->get_records_sql("SELECT lo.id, lo.namearabic AS name FROM {local_trainingprogram} lo");
            }
            foreach ($trainingids as $tp) {
                $trainingidoptions[$tp->id] = format_string($tp->name);
            }
        }
        return $trainingidoptions;
    }
    public function selected_filter($selected) {
        $filterdata = $this->filter_data();
        return $filterdata[$selected];
    }
    public function print_filter(&$mform) {
        $trainingidoptions = $this->filter_data(); 
        if ((!$this->placeholder || $this->filtertype == 'basic') && COUNT($trainingidoptions) > 1) {
            unset($trainingidoptions[0]);
        }
        $select = $mform->addElement('select', 'filter_trainingid', get_string('trainingid', 'block_learnerscript'), $trainingidoptions, array('data-select2' => 1));
        $select->setHiddenLabel(true);
        $mform->setType('filter_trainingid', PARAM_INT);
    }

}
