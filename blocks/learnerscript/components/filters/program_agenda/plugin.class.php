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
 * @author: Sudharani Sadula
 * @date: 2023
 */
namespace block_learnerscript\lsreports;
use block_learnerscript\local\pluginbase;
use stdClass;

class plugin_program_agenda extends pluginbase {

    public function init() {
        $this->form = false;
        $this->unique = true; 
        $this->placeholder = true;
        $this->singleselection = true;
        $this->fullname = get_string('program_agenda', 'block_learnerscript');
        $this->reporttypes = array('productlog');
        $this->filtertype = 'custom';
        if (!empty($this->reportclass->basicparams)) {
            foreach ($this->reportclass->basicparams as $basicparam) {
                if ($basicparam['name'] == 'program_agenda') {
                    $this->filtertype = 'basic';
                }
            }
        }
    }

    public function summary($data) {
        return get_string('program_agenda_summary', 'block_learnerscript');
    }

    public function execute($finalelements, $data, $filters) {

        $program_agenda = isset($filters['filter_program_agenda']) ? $filters['filter_program_agenda'] : 0;
        if (!$program_agenda) {
            return $finalelements;
        }

        if ($this->report->type != 'sql') {
            return array($program_agenda);
        } else {
            if (preg_match("/%%FILTER_PROGRAM_AGENDA:([^%]+)%%/i", $finalelements, $output)) {
                $replace = ' AND ' . $output[1] . ' = ' . $program_agenda;
                return str_replace('%%FILTER_PROGRAM_AGENDA:' . $output[1] . '%%', $replace, $finalelements);
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
            $program_agendalist = array();
        }

        $program_agendaoptions = array();
        if($selectoption){
            $program_agendaoptions[0] = $this->singleselection ?
                get_string('filter_program_agenda', 'block_learnerscript') : get_string('select') .' '. get_string('program_agenda', 'block_learnerscript');
        }

        if (empty($program_agendalist)) {
             $program_agenda = $DB->get_records_select('local_trainingprogram', '', array(), '', 'program_agenda');

            foreach ($program_agenda as $td) {
                $program_agendaoptions[$td->program_agenda] = format_string($td->program_agenda);
            }
        }
        return $program_agendaoptions;
    }
    public function selected_filter($selected) {
        $filterdata = $this->filter_data();
        return $filterdata[$selected];
    }
    public function print_filter(&$mform) {
        $program_agendaoptions = $this->filter_data(); 
        if ((!$this->placeholder || $this->filtertype == 'basic') && COUNT($program_agendaoptions) > 1) {
            unset($program_agendaoptions[0]);
        }
        $select = $mform->addElement('text', 'filter_program_agenda', '', array('placeholder' => get_string('filter_program_agenda', 'block_learnerscript')), array('data-select2' => 1));
    }

}
