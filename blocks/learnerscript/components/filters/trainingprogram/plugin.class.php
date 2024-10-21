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

class plugin_trainingprogram extends pluginbase {

    public function init() {
        $this->form = false;
        $this->unique = true; 
        $this->placeholder = true;
        $this->singleselection = true;
        $this->fullname = get_string('filtertrainingprogram', 'block_learnerscript');
        $this->reporttypes = array('offerings');
        $this->filtertype = 'custom';
        if (!empty($this->reportclass->basicparams)) {
            foreach ($this->reportclass->basicparams as $basicparam) {
                if ($basicparam['name'] == 'trainingprogram') {
                    $this->filtertype = 'basic';
                }
            }
        }
    }

    public function summary($data) {
        return get_string('filtertrainingprogram_summary', 'block_learnerscript');
    }

    public function execute($finalelements, $data, $filters) {

        $filtertrainingprogram = isset($filters['filter_trainingprogram']) ? $filters['filter_trainingprogram'] : 0;
        if (!$filtertrainingprogram) {
            return $finalelements;
        }

        if ($this->report->type != 'sql') {
            return array($filtertrainingprogram);
        } else {
            if (preg_match("/%%FILTER_TRAININGPROGRAM:([^%]+)%%/i", $finalelements, $output)) {
                $replace = ' AND ' . $output[1] . ' = ' . $filtertrainingprogram;
                return str_replace('%%FILTER_TRAININGPROGRAM:' . $output[1] . '%%', $replace, $finalelements);
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
            $trainingprogramlist = array_keys($DB->get_records('local_trainingprogram'));
        }

        $trainingprogramoptions = array();
        if($selectoption){
            $trainingprogramoptions[0] = $this->singleselection ?
                get_string('filter_trainingprogram', 'block_learnerscript') : get_string('select') .' '. get_string('trainingprogram', 'block_learnerscript');
        }

        if (empty($trainingprogramlist)) {
            $lang = current_language();
             if ($lang == 'en') {
                $names = $DB->get_records_sql("SELECT id, name FROM {local_trainingprogram} WHERE 1 = 1");
            } else {
                $names = $DB->get_records_sql("SELECT id, namearabic AS name FROM {local_trainingprogram} WHERE 1 = 1");
            }

            foreach ($names as $tp) {
                $trainingprogramoptions[$tp->id] = format_string($tp->name);
            }
        }
        return $trainingprogramoptions;
    }
    public function selected_filter($selected) {
        $filterdata = $this->filter_data();
        return $filterdata[$selected];
    }
    public function print_filter(&$mform) {
        $trainingprogramoptions = $this->filter_data(); 
        if ((!$this->placeholder || $this->filtertype == 'basic') && COUNT($trainingprogramoptions) > 1) {
            unset($trainingprogramoptions[0]);
        }
        $select = $mform->addElement('select', 'filter_trainingprogram', get_string('trainingprogram', 'block_learnerscript'), $trainingprogramoptions, array('data-select2' => 1));
        $select->setHiddenLabel(true);
        // if(isset($_SESSION['filter_trainingprogram']) && !empty($_SESSION['filter_trainingprogram']) && $this->report->type == 'meetingparticipants'){
        //     $mform->setDefault('filter_trainingprogram',$_SESSION['filter_trainingprogram']); 
        //  }
        $mform->setType('filter_trainingprogram', PARAM_INT);
    }

}
