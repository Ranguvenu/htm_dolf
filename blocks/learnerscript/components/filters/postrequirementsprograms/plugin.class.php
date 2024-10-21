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

class plugin_postrequirementsprograms extends pluginbase {

    public function init() {
        $this->form = false;
        $this->unique = true; 
        $this->placeholder = true;
        $this->singleselection = true;
        $this->fullname = get_string('filterpostrequirementsprograms', 'block_learnerscript');
        $this->reporttypes = array('offerings');
        $this->filtertype = 'custom';
        if (!empty($this->reportclass->basicparams)) {
            foreach ($this->reportclass->basicparams as $basicparam) {
                if ($basicparam['name'] == 'postrequirementsprograms') {
                    $this->filtertype = 'basic';
                }
            }
        }
    }

    public function summary($data) {
        return get_string('filterpostrequirementsprograms_summary', 'block_learnerscript');
    }

    public function execute($finalelements, $data, $filters) {

        $filterpostrequirementsprograms = isset($filters['filter_postrequirements']) ? $filters['filter_postrequirements'] : 0;
        if (!$filterpostrequirementsprograms) {
            return $finalelements;
        }

        if ($this->report->type != 'sql') {
            return array($filterpostrequirementsprograms);
        } else {
            if (preg_match("/%%FILTER_POSTREQUIREMENTS:([^%]+)%%/i", $finalelements, $output)) {
                $replace = ' AND ' . $output[1] . ' = ' . $filterpostrequirementsprograms;
                return str_replace('%%FILTER_POSTREQUIREMENTS:' . $output[1] . '%%', $replace, $finalelements);
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
            $postrequirementsprogramslist = array_keys($DB->get_records('local_trainingprogram'));
        }

        $postrequirementsprogramsoptions = array();
        if($selectoption){
            $postrequirementsprogramsoptions[0] = $this->singleselection ?
                get_string('filter_postrequirements', 'block_learnerscript') : get_string('select') .' '. get_string('postrequirementsprograms', 'block_learnerscript');
        }

        if (empty($postrequirementsprogramslist)) {

            $lang = current_language();
            if( $lang == 'ar' ){
                $postrequirementsprograms = $DB->get_records_select('local_trainingprogram', '', array(), '', 'id, arabicname as name');

            } else {

                $postrequirementsprograms = $DB->get_records_select('local_trainingprogram', '', array(), '', 'id, name');
            
            }
            foreach ($postrequirementsprograms as $c) {
                $postrequirementsprogramsoptions[$c->id] = format_string($c->name);
            }
        }
        return $postrequirementsprogramsoptions;

    }
    public function selected_filter($selected) {
        $filterdata = $this->filter_data();
        return $filterdata[$selected];
    }
    public function print_filter(&$mform) {
        $postrequirementsprogramsoptions = $this->filter_data(); 
        if ((!$this->placeholder || $this->filtertype == 'basic') && COUNT($postrequirementsprogramsoptions) > 1) {
            unset($postrequirementsprogramsoptions[0]);
        }
        $select = $mform->addElement('select', 'filter_postrequirements', get_string('postrequirementsprograms', 'block_learnerscript'), $postrequirementsprogramsoptions, array('data-select2' => 1));
        $select->setHiddenLabel(true);
        $mform->setType('filter_postrequirements', PARAM_INT);
    }

}
