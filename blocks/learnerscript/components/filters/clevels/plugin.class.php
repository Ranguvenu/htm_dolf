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
 * @author: Sudharani Sadula
 * @date: 2023
 */
namespace block_learnerscript\lsreports;
use block_learnerscript\local\pluginbase;
use stdClass;

class plugin_clevels extends pluginbase {

    public function init() {
        $this->form = false;
        $this->unique = true; 
        $this->placeholder = true;
        $this->singleselection = true;
        $this->fullname = get_string('filterclevels', 'block_learnerscript');
        $this->reporttypes = array('offerings');
        $this->filtertype = 'custom';
        if (!empty($this->reportclass->basicparams)) {
            foreach ($this->reportclass->basicparams as $basicparam) {
                if ($basicparam['name'] == 'clevels') {
                    $this->filtertype = 'basic';
                }
            }
        }
    }

    public function summary($data) {
        return get_string('filterclevels_summary', 'block_learnerscript');
    }

    public function execute($finalelements, $data, $filters) {

        $filterclevels = isset($filters['filter_clevels']) ? $filters['filter_clevels'] : 0;
        if (!$filterclevels) {
            return $finalelements;
        }

        if ($this->report->type != 'sql') {
            return array($filterclevels);
        } else {
            if (preg_match("/%%FILTER_CLEVELS:([^%]+)%%/i", $finalelements, $output)) {
                $replace = ' AND ' . $output[1] . ' = ' . $filterclevels;
                return str_replace('%%FILTER_CLEVELS:' . $output[1] . '%%', $replace, $finalelements);
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
            $component = (new \block_learnerscript\local\ls)->cr_unserialize($this->report->clevels);
        } else {
            $clevelslist = array();
        }

        $clevelsoptions = array();
        if($selectoption){
            $clevelsoptions[0] = $this->singleselection ?
                get_string('filter_clevels', 'block_learnerscript') : get_string('select') .' '. get_string('clevels', 'block_learnerscript');
        }

        if (empty($clevelslist)) {
            if($this->report->type ==  'exams'){
                $clevels = $DB->get_records_sql("SELECT DISTINCT le.clevels, le.id FROM {local_exams} le");
                foreach ($clevels as $tp) {
                    $clevelsoptions[$tp->clevels] = format_string($tp->clevels);
                }
            }else{
                $clevelsoptions = array(0 => get_string('filter_clevels', 'block_learnerscript'), 'level1' => get_string('level1','local_competency'), 'level2' => get_string('level2','local_competency'), 'level3' => get_string('level3','local_competency'), 'level4' => get_string('level4','local_competency'), 'level5' => get_string('level5','local_competency'));
            }
            



            // $clevelsoptions = array_merge($clevelsoptions[0], $clevelsoptions);
        }
        return $clevelsoptions;
    }
    public function selected_filter($selected) {
        $filterdata = $this->filter_data();
        return $filterdata[$selected];
    }
    public function print_filter(&$mform) {
        $clevelsoptions = $this->filter_data(); 
        if ((!$this->placeholder || $this->filtertype == 'basic') && COUNT($clevelsoptions) > 1) {
            unset($clevelsoptions[0]);
        }
        $select = $mform->addElement('select', 'filter_clevels', get_string('clevels', 'block_learnerscript'), $clevelsoptions, array('data-select2' => 1));
        $select->setHiddenLabel(true);
        $mform->setType('filter_clevels', PARAM_INT);
    }

}
