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
 * @author: Jahnavi Nanduri
 * @date: 2023
 */
namespace block_learnerscript\lsreports;
use block_learnerscript\local\pluginbase;
use block_learnerscript\local\querylib;
use stdClass;

class plugin_idnumber extends pluginbase {

    public function init() {
        $this->form = false;
        $this->unique = true; 
        $this->placeholder = true;
        $this->singleselection = true;
        $this->fullname = get_string('idnumber', 'block_learnerscript');
        $this->reporttypes = array('enrolledidnumberiner', 'enrolledtrainees');
        $this->filtertype = 'custom';
        if (!empty($this->reportclass->basicparams)) {
            foreach ($this->reportclass->basicparams as $basicparam) {
                if ($basicparam['name'] == 'idnumber') {
                    $this->filtertype = 'basic';
                }
            }
        }
    }

    public function summary($data) {
        return get_string('filteridnumber_summary', 'block_learnerscript');
    }

    public function execute($finalelements, $data, $filters) {

        $filteridnumber = isset($filters['filter_idnumber']) ? $filters['filter_idnumber'] : 0;
        if (!$filteridnumber) {
            return $finalelements;
        }

        if ($this->report->type != 'sql') {
            return array($filteridnumber);
        } else {
            if (preg_match("/%%FILTER_IDNUMBER:([^%]+)%%/i", $finalelements, $output)) {
                $replace = ' AND ' . $output[1] . ' = ' . $filteridnumber;
                return str_replace('%%FILTER_IDNUMBER:' . $output[1] . '%%', $replace, $finalelements);
            }
        }
        return $finalelements;
    }
    public function filter_data($selectoption = true){
        global $DB, $CFG;

        $filter_idnumber = '';
        $fidnumbers = isset($request['filter_idnumber']) ? $request['filter_idnumber'] : 0;
        $filteridnumber = optional_param('filter_idnumber', $fidnumbers, PARAM_RAW);

        $properties = new stdClass();
        $properties->courseid = SITEID;

        $reportclassname = 'block_learnerscript\lsreports\report_' . $this->report->type;
        $reportclass = new $reportclassname($this->report, $properties);

        if ($this->report->type != 'sql') {
            $components = (new \block_learnerscript\local\ls)->cr_unserialize($this->report->components);
        } else {
            $idnumberlist = array_keys($DB->get_records('local_users'));
        }

        // $idnumberoptions = array();
        // if($selectoption){
        //     $idnumberoptions[0] = $this->singleselection ?
        //         get_string('filter_idnumber', 'block_learnerscript') : get_string('select') .' '. get_string('idnumber', 'block_learnerscript');
        // }

        // if (empty($idnumberlist)) {
        //     $idnumbers = $DB->get_records_sql("SELECT DISTINCT id_number AS id, id_number AS idnumber FROM {local_users}");

        //     foreach ($idnumbers as $e) {
        //         $idnumberoptions[$e->id] = format_string($e->idnumber);
        //     }
        // }
        $idnumberoptions = (new \block_learnerscript\local\querylib)->filter_get_idnumber($this, $selectoption, false, false, $filteridnumber);
        return $idnumberoptions;
    }
    public function selected_filter($selected) {
        $filterdata = $this->filter_data();
        return $filterdata[$selected];
    }
    public function print_filter(&$mform) {
        $idnumberoptions = $this->filter_data(); 
        if ((!$this->placeholder || $this->filtertype == 'basic') && COUNT($idnumberoptions) > 1) {
            unset($idnumberoptions[0]);
        }
        $select = $mform->addElement('select', 'filter_idnumber', null,
            $idnumberoptions,
            array('data-select2-ajax' => true,
                  'data-maximum-selection-length' => $this->maxlength,
                  'data-action' => 'filteridnumber',
                  'data-instanceid' => $this->reportclass->config->id));
        $select->setHiddenLabel(true);
        $mform->setType('filter_idnumber', PARAM_INT);

        $mform->addElement('hidden', 'filter_idnumber_type', $this->filtertype); 
        $mform->setType('filter_idnumber_type', PARAM_RAW);
    }

}
