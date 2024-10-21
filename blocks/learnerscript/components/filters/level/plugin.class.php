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

class plugin_level extends pluginbase {

    public function init() {
        $this->form = false;
        $this->unique = true; 
        $this->placeholder = true;
        $this->singleselection = true;
        $this->fullname = get_string('filterlevel', 'block_learnerscript');
        $this->reporttypes = array('offerings');
        $this->filtertype = 'custom';
        if (!empty($this->reportclass->basicparams)) {
            foreach ($this->reportclass->basicparams as $basicparam) {
                if ($basicparam['name'] == 'level') {
                    $this->filtertype = 'basic';
                }
            }
        }
    }

    public function summary($data) {
        return get_string('filterlevel_summary', 'block_learnerscript');
    }

    public function execute($finalelements, $data, $filters) {

        $filterlevel = isset($filters['filter_level']) ? $filters['filter_level'] : 0;
        if (!$filterlevel) {
            return $finalelements;
        }

        if ($this->report->type != 'sql') {
            return array($filterlevel);
        } else {
            if (preg_match("/%%FILTER_LEVEL:([^%]+)%%/i", $finalelements, $output)) {
                $replace = ' AND ' . $output[1] . ' = ' . $filterlevel;
                return str_replace('%%FILTER_LEVEL:' . $output[1] . '%%', $replace, $finalelements);
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
            $levellist = array_keys($DB->get_records('local_trainingprogram'));
        }

        $leveloptions = array();
        if($selectoption){
            $leveloptions[-1] = $this->singleselection ?
                get_string('filter_level', 'block_learnerscript') : get_string('select') .' '. get_string('level', 'block_learnerscript');
        }

        if (empty($levellist)) {
            $leveloptions = array(-1 => get_string('filter_level', 'block_learnerscript'), 'level1' => get_string('level1','local_competency'), 'level2' => get_string('level2','local_competency'), 'level3' => get_string('level3','local_competency'), 'level4' => get_string('level4','local_competency'), 'level5' => get_string('level5','local_competency'));
        }
        return $leveloptions;
    }
    public function selected_filter($selected) {
        $filterdata = $this->filter_data();
        return $filterdata[$selected];
    }
    public function print_filter(&$mform) {
        $leveloptions = $this->filter_data(); 
        if ((!$this->placeholder || $this->filtertype == 'basic') && COUNT($leveloptions) > 1) {
            unset($leveloptions[0]);
        }
        $select = $mform->addElement('select', 'filter_level', get_string('level', 'block_learnerscript'), $leveloptions, array('data-select2' => 1));
        $select->setHiddenLabel(true);
        $mform->setType('filter_level', PARAM_INT);
    }

}
