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

class plugin_title extends pluginbase {

    public function init() {
        $this->form = false;
        $this->unique = true; 
        $this->placeholder = true;
        $this->singleselection = true;
        $this->fullname = get_string('filtertitle', 'block_learnerscript');
        $this->reporttypes = array('offerings');
        $this->filtertype = 'custom';
        if (!empty($this->reportclass->basicparams)) {
            foreach ($this->reportclass->basicparams as $basicparam) {
                if ($basicparam['name'] == 'title') {
                    $this->filtertype = 'basic';
                }
            }
        }
    }

    public function summary($data) {
        return get_string('filtertitle_summary', 'block_learnerscript');
    }

    public function execute($finalelements, $data, $filters) {

        $filtertitle = isset($filters['filter_title']) ? $filters['filter_title'] : 0;
        if (!$filtertitle) {
            return $finalelements;
        }

        if ($this->report->type != 'sql') {
            return array($filtertitle);
        } else {
            if (preg_match("/%%FILTER_TITLE:([^%]+)%%/i", $finalelements, $output)) {
                $replace = ' AND ' . $output[1] . ' = ' . $filtertitle;
                return str_replace('%%FILTER_TITLE:' . $output[1] . '%%', $replace, $finalelements);
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
            $titlelist = array_keys($DB->get_records('local_events'));
        }

        $titleoptions = array();
        if($selectoption){
            $titleoptions[0] = $this->singleselection ?
                get_string('filter_title', 'block_learnerscript') : get_string('select') .' '. get_string('title', 'block_learnerscript');
        }

        if (empty($titlelist)) {
            $lang = current_language();
            if($lang == 'en'){
                $titles = $DB->get_records_sql("SELECT DISTINCT le.title, le.id FROM {local_events} le");

            }else{
                $titles = $DB->get_records_sql("SELECT DISTINCT le.titlearabic AS title, le.id FROM {local_events} le");

            }
            foreach ($titles as $tp) {
                $titleoptions[$tp->title] = format_string($tp->title);
            }
        }
        return $titleoptions;
    }
    public function selected_filter($selected) {
        $filterdata = $this->filter_data();
        return $filterdata[$selected];
    }
    public function print_filter(&$mform) {
        $titleoptions = $this->filter_data(); 
        if ((!$this->placeholder || $this->filtertype == 'basic') && COUNT($titleoptions) > 1) {
            unset($titleoptions[0]);
        }
        $select = $mform->addElement('select', 'filter_title', get_string('title', 'block_learnerscript'), $titleoptions, array('data-select2' => 1));
        $select->setHiddenLabel(true);
        $mform->setType('filter_title', PARAM_INT);
    }

}
