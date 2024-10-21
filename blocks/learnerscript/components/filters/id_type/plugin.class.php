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

class plugin_id_type extends pluginbase {

    public function init() {
        $this->form = false;
        $this->unique = true; 
        $this->placeholder = true;
        $this->singleselection = true;
        $this->fullname = get_string('filterid_type', 'block_learnerscript');
        $this->reporttypes = array('offerings');
        $this->filtertype = 'custom';
        if (!empty($this->reportclass->basicparams)) {
            foreach ($this->reportclass->basicparams as $basicparam) {
                if ($basicparam['name'] == 'id_type') {
                    $this->filtertype = 'basic';
                }
            }
        }
    }

    public function summary($data) {
        return get_string('filterid_type_summary', 'block_learnerscript');
    }

    public function execute($finalelements, $data, $filters) {

        $filterid_type = isset($filters['filter_id_type']) ? $filters['filter_id_type'] : 0;
        if (!$filterid_type) {
            return $finalelements;
        }

        if ($this->report->type != 'sql') {
            return array($filterid_type);
        } else {
            if (preg_match("/%%FILTER_ID_TYPE:([^%]+)%%/i", $finalelements, $output)) {
                $replace = ' AND ' . $output[1] . ' = ' . $filterid_type;
                return str_replace('%%FILTER_ID_TYPE:' . $output[1] . '%%', $replace, $finalelements);
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
            $id_typelist = array_keys($DB->get_records('local_trainingprogram'));
        }

        $id_typeoptions = array();
        if($selectoption){
            $id_typeoptions[0] = $this->singleselection ?
                get_string('filter_id_type', 'block_learnerscript') : get_string('select') .' '. get_string('id_type', 'block_learnerscript');
        }

        if (empty($id_typelist)) {
            $id_typeoptions = array(0 => get_string('filter_id_type', 'block_learnerscript'), 1 => get_string('id','block_learnerscript'), 2 => get_string('passport', 'block_learnerscript'), 3 => get_string('saudiid','local_userapproval'), 4 => get_string('residential', 'block_learnerscript'));
        }
        return $id_typeoptions;
    }
    public function selected_filter($selected) {
        $filterdata = $this->filter_data();
        return $filterdata[$selected];
    }
    public function print_filter(&$mform) {
        $id_typeoptions = $this->filter_data(); 
        if ((!$this->placeholder || $this->filtertype == 'basic') && COUNT($id_typeoptions) > 1) {
            unset($id_typeoptions[0]);
        }
        $select = $mform->addElement('select', 'filter_id_type', get_string('id_type', 'block_learnerscript'), $id_typeoptions, array('data-select2' => 1));
        $select->setHiddenLabel(true);
        $mform->setType('filter_id_type', PARAM_INT);
    }

}
