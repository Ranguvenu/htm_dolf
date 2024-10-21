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

class plugin_hascertificate extends pluginbase {

    public function init() {
        $this->form = false;
        $this->unique = true; 
        $this->placeholder = true;
        $this->singleselection = true;
        $this->fullname = get_string('filterhascertificate', 'block_learnerscript');
        $this->reporttypes = array('offerings');
        $this->filtertype = 'custom';
        if (!empty($this->reportclass->basicparams)) {
            foreach ($this->reportclass->basicparams as $basicparam) {
                if ($basicparam['name'] == 'hascertificate') {
                    $this->filtertype = 'basic';
                }
            }
        }
    }

    public function summary($data) {
        return get_string('filterhascertificate_summary', 'block_learnerscript');
    }

    public function execute($finalelements, $data, $filters) {

        $filterhascertificate = isset($filters['filter_hascertificate']) ? $filters['filter_hascertificate'] : 0;
        if (!$filterhascertificate) {
            return $finalelements;
        }

        if ($this->report->type != 'sql') {
            return array($filterhascertificate);
        } else {
            if (preg_match("/%%FILTER_HASCERTIFICATION:([^%]+)%%/i", $finalelements, $output)) {
                $replace = ' AND ' . $output[1] . ' = ' . $filterhascertificate;
                return str_replace('%%FILTER_HASCERTIFICATION:' . $output[1] . '%%', $replace, $finalelements);
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
            $hascertificatelist = array_keys($DB->get_records('local_exam_profile'));
        }

        $hascertificateoptions = array();
        if($selectoption){
            $hascertificateoptions[-1] = $this->singleselection ?
                get_string('filter_hascertificate', 'block_learnerscript') : get_string('select') .' '. get_string('hascertificate', 'block_learnerscript');
        }

        if (empty($hascertificatelist)) {
            $hascertificateoptions = array(-1 => get_string('filter_hascertificate', 'block_learnerscript'), 0 => get_string('no','block_learnerscript'), 1 => get_string('yes', 'block_learnerscript'));
        }
        return $hascertificateoptions;
    }
    public function selected_filter($selected) {
        $filterdata = $this->filter_data();
        return $filterdata[$selected];
    }
    public function print_filter(&$mform) {
        $hascertificateoptions = $this->filter_data(); 
        if ((!$this->placeholder || $this->filtertype == 'basic') && COUNT($hascertificateoptions) > 1) {
            unset($hascertificateoptions[0]);
        }
        $select = $mform->addElement('select', 'filter_hascertificate', get_string('hascertificate', 'block_learnerscript'), $hascertificateoptions, array('data-select2' => 1));
        $select->setHiddenLabel(true);
        $mform->setType('filter_hascertificate', PARAM_INT);
    }

}
