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

class plugin_userid extends pluginbase {

    public function init() {
        $this->form = false;
        $this->unique = true; 
        $this->placeholder = true;
        $this->singleselection = true;
        $this->fullname = get_string('filteruserid', 'block_learnerscript');
        $this->reporttypes = array('offerings');
        $this->filtertype = 'custom';
        if (!empty($this->reportclass->basicparams)) {
            foreach ($this->reportclass->basicparams as $basicparam) {
                if ($basicparam['name'] == 'userid') {
                    $this->filtertype = 'basic';
                }
            }
        }
    }

    public function summary($data) {
        return get_string('filteruserid_summary', 'block_learnerscript');
    }

    public function execute($finalelements, $data, $filters) {

        $filteruserid = isset($filters['filter_userid']) ? $filters['filter_userid'] : 0;
        if (!$filteruserid) {
            return $finalelements;
        }

        if ($this->report->type != 'sql') {
            return array($filteruserid);
        } else {
            if (preg_match("/%%FILTER_USERID:([^%]+)%%/i", $finalelements, $output)) {
                $replace = ' AND ' . $output[1] . ' = ' . $filteruserid;
                return str_replace('%%FILTER_USERID:' . $output[1] . '%%', $replace, $finalelements);
            }
        }
        return $finalelements;
    }
    public function filter_data($selectoption = true){
        global $DB, $CFG;
        $properties = new stdClass();
        $properties->courseuserid = SITEuserid;

        $reportclassname = 'block_learnerscript\lsreports\report_' . $this->report->type;
        $reportclass = new $reportclassname($this->report, $properties);

        if ($this->report->type != 'sql') {
            $components = (new \block_learnerscript\local\ls)->cr_unserialize($this->report->components);
        } else {
            $useridlist = array_keys($DB->get_records('local_users'));
        }

        $useridoptions = array();
        if($selectoption){
            $useridoptions[0] = $this->singleselection ?
                get_string('filter_userid', 'block_learnerscript') : get_string('select') .' '. get_string('userid', 'block_learnerscript');
        }

        if (empty($useridlist)) {
            $userids = $DB->get_records_sql("SELECT DISTINCT le.userid, CONCAT(le.firstname, ' ', le.lastname) AS fullname FROM {local_users} le where le.userid != 0");
        }
        foreach($userids as $userid){
            $useridoptions[$userid->userid] = format_string($userid->fullname);
        }

        return $useridoptions;
    }
    public function selected_filter($selected) {
        $filterdata = $this->filter_data();
        return $filterdata[$selected];
    }
    public function print_filter(&$mform) {
        $useridoptions = $this->filter_data(); 
        if ((!$this->placeholder || $this->filtertype == 'basic') && COUNT($useridoptions) > 1) {
            unset($useridoptions[0]);
        }
        $select = $mform->addElement('select', 'filter_userid', get_string('userid', 'block_learnerscript'), $useridoptions, array('data-select2' => 1));
        $select->setHiddenLabel(true);
        $mform->setType('filter_userid', PARAM_INT);
    }

}
