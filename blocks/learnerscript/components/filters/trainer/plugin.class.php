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
 * @date: 2023
 */
namespace block_learnerscript\lsreports;
use block_learnerscript\local\pluginbase;
use stdClass;

class plugin_trainer extends pluginbase {

    public function init() {
        $this->form = false;
        $this->unique = true; 
        $this->placeholder = true;
        $this->singleselection = true;
        $this->fullname = get_string('trainer', 'block_learnerscript');
        $this->reporttypes = array('trainerprofiles');
        $this->filtertype = 'custom';
        if (!empty($this->reportclass->basicparams)) {
            foreach ($this->reportclass->basicparams as $basicparam) {
                if ($basicparam['name'] == 'trainer') {
                    $this->filtertype = 'basic';
                }
            }
        }
    }

    public function summary($data) {
        return get_string('filtertrainer_summary', 'block_learnerscript');
    }

    public function execute($finalelements, $data, $filters) {

        $filtertrainer = isset($filters['filter_trainer']) ? $filters['filter_trainer'] : 0;
        if (!$filtertrainer) {
            return $finalelements;
        }

        if ($this->report->type != 'sql') {
            return array($filtertrainer);
        } else {
            if (preg_match("/%%FILTER_TRAINER:([^%]+)%%/i", $finalelements, $output)) {
                $replace = ' AND ' . $output[1] . ' = ' . $filtertrainer;
                return str_replace('%%FILTER_TRAINER:' . $output[1] . '%%', $replace, $finalelements);
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
            $trainerlist = array_keys($DB->get_records('local_organization'));
        }

        $traineroptions = array();
        if($selectoption){
            $traineroptions[0] = $this->singleselection ?
                get_string('filter_trainer', 'block_learnerscript') : get_string('select') .' '. get_string('trainer', 'block_learnerscript');
        }

        if (empty($trainerlist)) {
            $lang = current_language();
            // if ($lang == 'ar') {
            //     $trainers = $DB->get_records_sql("SELECT DISTINCT pe.userid, CONCAT(lu.firstnamearabic, ' ', lu.lastnamearabic) AS trainer FROM {program_enrollments} pe JOIN {role} r ON r.id = pe.roleid 
            //         JOIN {local_users} lu ON lu.userid = pe.userid
            //         WHERE pe.enrolstatus = 1 AND r.shortname = 'trainer'");
            // } else {
            //     $trainers = $DB->get_records_sql("SELECT DISTINCT pe.userid, CONCAT(lu.firstname, ' ', lu.lastname) AS trainer FROM {program_enrollments} pe JOIN {role} r ON r.id = pe.roleid 
            //         JOIN {local_users} lu ON lu.userid = pe.userid
            //         WHERE pe.enrolstatus = 1 AND r.shortname = 'trainer'");
            // }  
            // foreach ($trainers as $e) {
            //     $traineroptions[$e->userid] = format_string($e->trainer);
            // }
        }
        return $traineroptions;
    }
    public function selected_filter($selected) {
        $filterdata = $this->filter_data();
        return $filterdata[$selected];
    }
    public function print_filter(&$mform) {
        $traineroptions = $this->filter_data(); 
        if ((!$this->placeholder || $this->filtertype == 'basic') && COUNT($traineroptions) > 1) {
            unset($traineroptions[0]);
        }
        $select = $mform->addElement('select', 'filter_trainer', get_string('trainer', 'block_learnerscript'), $traineroptions, array('data-select2' => 1));
        $select->setHiddenLabel(true);
        $mform->setType('filter_trainer', PARAM_INT);
    }

}
