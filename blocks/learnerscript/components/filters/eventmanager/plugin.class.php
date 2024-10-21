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

class plugin_eventmanager extends pluginbase {

    public function init() {
        $this->form = false;
        $this->unique = true; 
        $this->placeholder = true;
        $this->singleselection = true;
        $this->fullname = get_string('filtereventmanager', 'block_learnerscript');
        $this->reporttypes = array('offerings');
        $this->filtertype = 'custom';
        if (!empty($this->reportclass->basicparams)) {
            foreach ($this->reportclass->basicparams as $basicparam) {
                if ($basicparam['name'] == 'eventmanager') {
                    $this->filtertype = 'basic';
                }
            }
        }
    }

    public function summary($data) {
        return get_string('filtereventmanager_summary', 'block_learnerscript');
    }

    public function execute($finalelements, $data, $filters) {

        $filtereventmanager = isset($filters['filter_eventmanager']) ? $filters['filter_eventmanager'] : 0;
        if (!$filtereventmanager) {
            return $finalelements;
        }

        if ($this->report->type != 'sql') {
            return array($filtereventmanager);
        } else {
            if (preg_match("/%%FILTER_EVENTMANAGER:([^%]+)%%/i", $finalelements, $output)) {
                $replace = ' AND ' . $output[1] . ' = ' . $filtereventmanager;
                return str_replace('%%FILTER_EVENTMANAGER:' . $output[1] . '%%', $replace, $finalelements);
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
            $eventmanagerlist = array_keys($DB->get_records('local_sector'));
        }

        $eventmanageroptions = array();
        if($selectoption){
            $eventmanageroptions[0] = $this->singleselection ?
                get_string('filter_eventmanager', 'block_learnerscript') : get_string('select') .' '. get_string('eventmanager', 'block_learnerscript');
        }

        if (empty($eventmanagerlist)) {
            $eventmanager = $DB->get_field_sql("SELECT GROUP_CONCAT(DISTINCT eventmanager) FROM {local_events} WHERE 1=1  AND eventmanager !=''");
            $eventsql = "SELECT u.id, CONCAT(u.firstname, u.lastname) AS fullname
            FROM {user} u
            WHERE u.id IN($eventmanager)";
            $eventmanagers = $DB->get_records_sql($eventsql);

            foreach ($eventmanagers as $tp) {
                $eventmanageroptions[$tp->id] = format_string($tp->fullname);
            }
        }
        return $eventmanageroptions;
    }
    public function selected_filter($selected) {
        $filterdata = $this->filter_data();
        return $filterdata[$selected];
    }
    public function print_filter(&$mform) {
        $eventmanageroptions = $this->filter_data(); 
        if ((!$this->placeholder || $this->filtertype == 'basic') && COUNT($eventmanageroptions) > 1) {
            unset($eventmanageroptions[0]);
        }
        $select = $mform->addElement('select', 'filter_eventmanager', get_string('eventmanager', 'block_learnerscript'), $eventmanageroptions, array('data-select2' => 1));
        $select->setHiddenLabel(true);
        $mform->setType('filter_eventmanager', PARAM_INT);
    }

}
