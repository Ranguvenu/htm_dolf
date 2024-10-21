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
use stdClass;

class plugin_arabicname extends pluginbase {

    public function init() {
        $this->form = false;
        $this->unique = true; 
        $this->placeholder = true;
        $this->singleselection = true;
        $this->fullarabicname = get_string('arabicname', 'block_learnerscript');
        $this->reporttypes = array('');
        $this->filtertype = 'custom';
        if (!empty($this->reportclass->basicparams)) {
            foreach ($this->reportclass->basicparams as $basicparam) {
                if ($basicparam['arabicname'] == 'arabicname') {
                    $this->filtertype = 'basic';
                }
            }
        }
    }

    public function summary($data) {
        return get_string('filterarabicname_summary', 'block_learnerscript');
    }

    public function execute($finalelements, $data, $filters) {

        $filterarabicname = isset($filters['filter_arabicname']) ? $filters['filter_arabicname'] : 0;
        if (!$filterarabicname) {
            return $finalelements;
        }

        if ($this->report->type != 'sql') {
            return array($filterarabicname);
        } else {
            if (preg_match("/%%FILTER_ARABICNAME:([^%]+)%%/i", $finalelements, $output)) {
                $replace = ' AND ' . $output[1] . ' = ' . $filterarabicname;
                return str_replace('%%FILTER_ARABICNAME:' . $output[1] . '%%', $replace, $finalelements);
            }
        }
        return $finalelements;
    }
    public function filter_data($selectoption = true){
        global $DB, $CFG;
        $properties = new stdClass();
        $properties->courseid = SITEID;

        $reportclassarabicname = 'block_learnerscript\lsreports\report_' . $this->report->type;
        $reportclass = new $reportclassarabicname($this->report, $properties);

        if ($this->report->type != 'sql') {
            $components = (new \block_learnerscript\local\ls)->cr_unserialize($this->report->components);
        } else {
            $arabicnamelist = array_keys($DB->get_records('local_competencies'));
        }

        $arabicnameoptions = array();
        if($selectoption){
            $arabicnameoptions[0] = $this->singleselection ?
                get_string('filter_arabicname', 'block_learnerscript') : get_string('select') .' '. get_string('arabicname', 'block_learnerscript');
        }
        if (empty($arabicnamelist)) {
            $arabicnames = $DB->get_records_select('local_competencies', '', array(), '', 'id, arabicname');

            foreach ($arabicnames as $e) {
                $arabicnameoptions[$e->arabicname] = format_string($e->arabicname);
            }
        }
        return $arabicnameoptions;
    }
    public function selected_filter($selected) {
        $filterdata = $this->filter_data();
        return $filterdata[$selected];
    }
    public function print_filter(&$mform) {
        $arabicnameoptions = $this->filter_data(); 
        if ((!$this->placeholder || $this->filtertype == 'basic') && COUNT($arabicnameoptions) > 1) {
            unset($arabicnameoptions[0]);
        }
        $select = $mform->addElement('select', 'filter_arabicname', get_string('arabicname', 'block_learnerscript'), $arabicnameoptions, array('data-select2' => 1));
        $select->setHiddenLabel(true);
        $mform->setType('filter_arabicname', PARAM_INT);
    }

}
