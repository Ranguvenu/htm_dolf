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

class plugin_segment extends pluginbase {

    public function init() {
        $this->form = false;
        $this->unique = true; 
        $this->placeholder = true;
        $this->singleselection = true;
        $this->fullsegment = get_string('segment', 'block_learnerscript');
        $this->reporttypes = array('');
        $this->filtertype = 'custom';
        if (!empty($this->reportclass->basicparams)) {
            foreach ($this->reportclass->basicparams as $basicparam) {
                if ($basicparam['segment'] == 'segment') {
                    $this->filtertype = 'basic';
                }
            }
        }
    }

    public function summary($data) {
        return get_string('filtersegment_summary', 'block_learnerscript');
    }

    public function execute($finalelements, $data, $filters) {

        $filtersegment = isset($filters['filter_segment']) ? $filters['filter_segment'] : 0;
        if (!$filtersegment) {
            return $finalelements;
        }

        if ($this->report->type != 'sql') {
            return array($filtersegment);
        } else {
            if (preg_match("/%%FILTER_SEGMENT:([^%]+)%%/i", $finalelements, $output)) {
                $replace = ' AND ' . $output[1] . ' = ' . $filtersegment;
                return str_replace('%%FILTER_SEGMENT:' . $output[1] . '%%', $replace, $finalelements);
            }
        }
        return $finalelements;
    }
    public function filter_data($selectoption = true){
        global $DB, $CFG;
        $properties = new stdClass();
        $properties->courseid = SITEID;

        $reportclasssegment = 'block_learnerscript\lsreports\report_' . $this->report->type;
        $reportclass = new $reportclasssegment($this->report, $properties);

        if ($this->report->type != 'sql') {
            $components = (new \block_learnerscript\local\ls)->cr_unserialize($this->report->components);
        } else {
            $segmentlist = array_keys($DB->get_records('local_segment'));
        }

        $segmentoptions = array();
        if($selectoption){
            $segmentoptions[0] = $this->singleselection ?
                get_string('filter_segment', 'block_learnerscript') : get_string('select') .' '. get_string('segment', 'block_learnerscript');
        }
        if (empty($segmentlist)) {
            $segments = $DB->get_records_select('local_segment', '', array(), '', 'id, title');

            foreach ($segments as $e) {
                $segmentoptions[$e->id] = format_string($e->title);
            }
        }
        return $segmentoptions;
    }
    public function selected_filter($selected) {
        $filterdata = $this->filter_data();
        return $filterdata[$selected];
    }
    public function print_filter(&$mform) {
        $segmentoptions = $this->filter_data(); 
        if ((!$this->placeholder || $this->filtertype == 'basic') && COUNT($segmentoptions) > 1) {
            unset($segmentoptions[0]);
        }
        $select = $mform->addElement('select', 'filter_segment', get_string('segment', 'block_learnerscript'), $segmentoptions, array('data-select2' => 1));
        $select->setHiddenLabel(true);
        $mform->setType('filter_segment', PARAM_INT);
    }

}
