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
 * @date: 2017
 */
namespace block_learnerscript\lsreports;
use block_learnerscript\local\pluginbase;

class plugin_evolution extends pluginbase {

    public function init() {
        $this->form = false;
        $this->unique = true;
        $this->singleselection = true; 
        $this->placeholder = true;
        $this->fullname = get_string('filterevolution', 'block_learnerscript');
        $this->filtertype = 'custom';
        if (!empty($this->reportclass->basicparams)) {
            foreach ($this->reportclass->basicparams as $basicparam) {
                if ($basicparam['name'] == 'evolution') {
                    $this->filtertype = 'basic';
                }
            }
        }
        $this->reporttypes = array('listofevolution', 'userevolution', 'student_performance', 'gradedactivity');
    }

    public function summary($data) {
        return get_string('filterevolution_summary', 'block_learnerscript');
    }

    public function execute($finalelements, $data, $filters) {

        $filter_evolution = isset($filters['filter_evolution']) ? $filters['filter_evolution'] : 0;
        if (!$filter_evolution) {
            return $finalelements;
        }

        if ($this->report->type != 'sql') {
            return array($filter_evolution);
        }
        return $finalelements;
    }
    public function filter_data($selectoption = true){
        global $DB, $CFG;
        require_once $CFG->dirroot . '/course/lib.php';
        $courseid = optional_param('courseid', SITEID, PARAM_INT);
        if ($courseid <= SITEID) { 
            $courseid = optional_param('filter_courses', SITEID, PARAM_RAW);
        }
        $evolution = array();
        if($selectoption){
            $evolution[0] = $this->singleselection ?
                        get_string('select_evolution', 'block_learnerscript') : get_string('select') .' '. get_string('evolution', 'block_learnerscript');
        }
        
        $evolutionlist = $DB->get_records_sql("SELECT id, name FROM {evolutions} WHERE 1=1");
        foreach ($evolutionlist as $g) {
            $evolution[$g->id] = $g->name;
        }
        return $evolution;
    }
    public function selected_filter($selected) {
        $filterdata = $this->filter_data();
        return $filterdata[$selected];
    }
    public function print_filter(&$mform) {
        $evolution = $this->filter_data();
        if (!$this->placeholder || $this->filtertype == 'basic' && COUNT($evolution) > 1) {
            unset($evolution[0]);
        }
        $select = $mform->addElement('select', 'filter_evolution', get_string('evolution', 'block_learnerscript'), $evolution,array('data-select2'=>1));
        $select->setHiddenLabel(true);
        $mform->setType('filter_evolution', PARAM_INT);
    }

}
