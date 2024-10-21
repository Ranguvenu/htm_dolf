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

class plugin_result extends pluginbase {

    public function init() {
        $this->form = false;
        $this->unique = true; 
        $this->placeholder = true;
        $this->singleselection = true;
        $this->fullname = get_string('filterresult', 'block_learnerscript');
        $this->reporttypes = array('offerings');
        $this->filtertype = 'custom';
        if (!empty($this->reportclass->basicparams)) {
            foreach ($this->reportclass->basicparams as $basicparam) {
                if ($basicparam['name'] == 'result') {
                    $this->filtertype = 'basic';
                }
            }
        }
    }

    public function summary($data) {
        return get_string('filterresult_summary', 'block_learnerscript');
    }

    public function execute($finalelements, $data, $filters) {

        $filterresult = isset($filters['filter_result']) ? $filters['filter_result'] : 0;
        if (!$filterresult) {
            return $finalelements;
        }

        if ($this->report->type != 'sql') {
            return array($filterresult);
        } else {
            if (preg_match("/%%FILTER_RESULT:([^%]+)%%/i", $finalelements, $output)) {
                $replace = ' AND ' . $output[1] . ' = ' . $filterresult;
                return str_replace('%%FILTER_RESULT:' . $output[1] . '%%', $replace, $finalelements);
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
            $resultlist = array();
        }

        $resultoptions = array();
        if($selectoption){
            $resultoptions[-1] = $this->singleselection ?
                get_string('filter_result', 'block_learnerscript') : get_string('select') .' '. get_string('result', 'block_learnerscript');
        }

        if (empty($resultlist)) {
            $resultoptions = array('-1' => get_string('filter_result', 'block_learnerscript'), '0' => get_string('notstarted', 'block_learnerscript'), '1' => get_string('unknow', 'block_learnerscript'),  '2' => get_string('absent', 'block_learnerscript'), '3' => get_string('exampassed', 'block_learnerscript'), '4' => get_string('examfailed', 'block_learnerscript'));
        }
        return $resultoptions;
    }
    public function selected_filter($selected) {
        $filterdata = $this->filter_data();
        return $filterdata[$selected];
    }
    public function print_filter(&$mform) {
        $resultoptions = $this->filter_data(); 
        if ((!$this->placeholder || $this->filtertype == 'basic') && COUNT($resultoptions) > 1) {
            unset($resultoptions[0]);
        }
        $select = $mform->addElement('select', 'filter_result', get_string('result', 'block_learnerscript'), $resultoptions, array('data-select2' => 1));
        $select->setHiddenLabel(true);
        $mform->setType('filter_result', PARAM_INT);
    }

}
