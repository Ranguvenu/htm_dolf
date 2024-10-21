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

class plugin_sections extends pluginbase {

    public function init() {
        $this->form = false;
        $this->unique = true; 
        $this->placeholder = true;
        $this->singleselection = true;
        $this->fullname = get_string('filtersections', 'block_learnerscript');
        $this->reporttypes = array('offerings');
        $this->filtertype = 'custom';
        if (!empty($this->reportclass->basicparams)) {
            foreach ($this->reportclass->basicparams as $basicparam) {
                if ($basicparam['name'] == 'sections') {
                    $this->filtertype = 'basic';
                }
            }
        }
    }

    public function summary($data) {
        return get_string('filtersections_summary', 'block_learnerscript');
    }

    public function execute($finalelements, $data, $filters) {

        $filtersections = isset($filters['filter_sections']) ? $filters['filter_sections'] : 0;
        if (!$filtersections) {
            return $finalelements;
        }

        if ($this->report->type != 'sql') {
            return array($filtersections);
        } else {
            if (preg_match("/%%FILTER_SECTIONS:([^%]+)%%/i", $finalelements, $output)) {
                $replace = ' AND ' . $output[1] . ' = ' . $filtersections;
                return str_replace('%%FILTER_SECTIONS:' . $output[1] . '%%', $replace, $finalelements);
            }
        }
        return $finalelements;
    }
    public function filter_data($selectoption = true){
        global $DB, $CFG;
        $properties = new stdClass();
        $properties->sections = SITEID;

        $reportclassname = 'block_learnerscript\lsreports\report_' . $this->report->type;
        $reportclass = new $reportclassname($this->report, $properties);

        if ($this->report->type != 'sql') {
            $components = (new \block_learnerscript\local\ls)->cr_unserialize($this->report->components);
        } else {
            $sectionslist = array_keys($DB->get_records('course'));
        }

        $sectionsoptions = array();
        if($selectoption){
            $sectionsoptions[0] = $this->singleselection ?
                get_string('filter_sections', 'block_learnerscript') : get_string('select') .' '. get_string('sections', 'block_learnerscript');
        }
        $sectionssql = "SELECT DISTINCT tf.sections, c.fullname
        FROM {tp_offerings} tf
        JOIN {local_trainingprogram} lt ON lt.id = tf.sections
        JOIN {course} c ON c.id = lt.courseid";
        if (empty($sectionslist)) {
            $sectionss = $DB->get_records_sql($sectionssql);

            foreach ($sectionss as $tp) {
                $sectionsoptions[$tp->sections] = format_string($tp->fullname);
            }
        }
        return $sectionsoptions;
    }
    public function selected_filter($selected) {
        $filterdata = $this->filter_data();
        return $filterdata[$selected];
    }
    public function print_filter(&$mform) {
        $sectionsoptions = $this->filter_data(); 
        if ((!$this->placeholder || $this->filtertype == 'basic') && COUNT($sectionsoptions) > 1) {
            unset($sectionsoptions[0]);
        }
        $select = $mform->addElement('select', 'filter_sections', get_string('sections', 'block_learnerscript'), $sectionsoptions, array('data-select2' => 1));
        $select->setHiddenLabel(true);
        $mform->setType('filter_sections', PARAM_INT);
    }

}
