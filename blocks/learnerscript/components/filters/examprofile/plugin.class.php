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

class plugin_examprofile extends pluginbase {

    public function init() {
        $this->form = false;
        $this->unique = true; 
        $this->placeholder = true;
        $this->singleselection = true;
        $this->fullname = get_string('filterexamprofile', 'block_learnerscript');
        $this->reporttypes = array('offerings');
        $this->filtertype = 'custom';
        if (!empty($this->reportclass->basicparams)) {
            foreach ($this->reportclass->basicparams as $basicparam) {
                if ($basicparam['name'] == 'examprofile') {
                    $this->filtertype = 'basic';
                }
            }
        }
    }

    public function summary($data) {
        return get_string('filterexamprofile_summary', 'block_learnerscript');
    }

    public function execute($finalelements, $data, $filters) {

        $filterexamprofile = isset($filters['filter_examprofile']) ? $filters['filter_examprofile'] : 0;
        if (!$filterexamprofile) {
            return $finalelements;
        }

        if ($this->report->type != 'sql') {
            return array($filterexamprofile);
        } else {
            if (preg_match("/%%FILTER_EXAMPROFILE:([^%]+)%%/i", $finalelements, $output)) {
                $replace = ' AND ' . $output[1] . ' = ' . $filterexamprofile;
                return str_replace('%%FILTER_EXAMPROFILE:' . $output[1] . '%%', $replace, $finalelements);
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
            if ($this->report->type == 'enrolledtrainees') {
                $examprofilelist = array_keys($DB->get_records('tp_offerings'));
            } else if ($this->report->type == 'enrolledexaminer') {
                $examprofilelist = array_keys($DB->get_records('local_exam_profiles'));
            } else if ($this->report->type == 'halls') {
                $examprofilelist = array_keys($DB->get_records('local_hall'));
            } else if ($this->report->type == 'competencies' || $this->report->type == 'usercompetencies') {
                $examprofilelist = array_keys($DB->get_records('local_hall'));
            } else {
                $examprofilelist = array_keys($DB->get_records('local_trainingprogram'));
            }
        }

        $examprofileoptions = array();
        $examprofileoptions[0] = $this->singleselection ?
                        get_string('filter_examprofile', 'block_learnerscript') : get_string('select') .' '. get_string('examprofile', 'block_learnerscript');

        if (empty($examprofilelist)) {
            $examprofiles = $DB->get_records_sql("SELECT DISTINCT lt.profilecode FROM {local_exam_profiles} lt");
                foreach ($examprofiles as $tp) {
                    $examprofileoptions[$tp->profilecode] = format_string($tp->profilecode);
                }
        }
        return $examprofileoptions;
    }
    public function selected_filter($selected) {
        $filterdata = $this->filter_data();
        return $filterdata[$selected];
    }
    public function print_filter(&$mform) {
        $examprofileoptions = $this->filter_data(); 
        if ((!$this->placeholder || $this->filtertype == 'basic') && COUNT($examprofileoptions) > 1) {
            unset($examprofileoptions[0]);
        }
        $select = $mform->addElement('select', 'filter_examprofile', get_string('examprofile', 'block_learnerscript'), $examprofileoptions, array('data-select2' => 1));
        $select->setHiddenLabel(true);
        $mform->setType('filter_examprofile', PARAM_INT);
    }

}
