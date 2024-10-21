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

class plugin_targetaudience extends pluginbase {

    public function init() {
        $this->form = false;
        $this->unique = true; 
        $this->placeholder = true;
        $this->singleselection = true;
        $this->fullname = get_string('filtertargetaudience', 'block_learnerscript');
        $this->reporttypes = array('offerings');
        $this->filtertype = 'custom';
        if (!empty($this->reportclass->basicparams)) {
            foreach ($this->reportclass->basicparams as $basicparam) {
                if ($basicparam['name'] == 'targetaudience') {
                    $this->filtertype = 'basic';
                }
            }
        }
    }

    public function summary($data) {
        return get_string('filtertargetaudience_summary', 'block_learnerscript');
    }

    public function execute($finalelements, $data, $filters) {

        $filtertargetaudience = isset($filters['filter_targetaudience']) ? $filters['filter_targetaudience'] : 0;
        if (!$filtertargetaudience) {
            return $finalelements;
        }

        if ($this->report->type != 'sql') {
            return array($filtertargetaudience);
        } else {
            if (preg_match("/%%FILTER_TARGETAUDIENCE:([^%]+)%%/i", $finalelements, $output)) {
                $replace = ' AND ' . $output[1] . ' = ' . $filtertargetaudience;
                return str_replace('%%FILTER_TARGETAUDIENCE:' . $output[1] . '%%', $replace, $finalelements);
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
            $targetaudiencelist = array_keys($DB->get_records('local_events'));
        }

        $targetaudienceoptions = array();
        if($selectoption){
            $targetaudienceoptions[0] = $this->singleselection ?
                get_string('filter_targetaudience', 'block_learnerscript') : get_string('select') .' '. get_string('targetaudience', 'block_learnerscript');
        }

        if (empty($targetaudiencelist)) {
            if($this->report->type == 'examprofiles'){
                $targetaudienceoptions = array(0 => get_string('filter_targetaudience', 'block_learnerscript'), 2 => get_string('nonsoudi','block_learnerscript'), 1 => get_string('soudi', 'block_learnerscript'), 3 => get_string('both', 'block_learnerscript'));

            }else if($this->report->type == 'exams' || $this->report->type == 'compexams'){
                $targetaudiences = $DB->get_records_sql("SELECT DISTINCT le.targetaudience, le.id FROM {local_exams} le WHERE le.targetaudience != ''");

                foreach ($targetaudiences as $tp) {
                    $targetaudienceoptions[$tp->targetaudience] = format_string($tp->targetaudience);
                }
            }else{
                $targetaudiences = $DB->get_records_sql("SELECT DISTINCT le.targetaudience, le.id FROM {local_events} le WHERE le.targetaudience != ''");

                foreach ($targetaudiences as $tp) {
                    $targetaudienceoptions[$tp->targetaudience] = format_string($tp->targetaudience);
                }
            }
        }
        return $targetaudienceoptions;
    }
    public function selected_filter($selected) {
        $filterdata = $this->filter_data();
        return $filterdata[$selected];
    }
    public function print_filter(&$mform) {
        $targetaudienceoptions = $this->filter_data(); 
        if ((!$this->placeholder || $this->filtertype == 'basic') && COUNT($targetaudienceoptions) > 1) {
            unset($targetaudienceoptions[0]);
        }
        $select = $mform->addElement('select', 'filter_targetaudience', get_string('targetaudience', 'block_learnerscript'), $targetaudienceoptions, array('data-select2' => 1));
        $select->setHiddenLabel(true);
        $mform->setType('filter_targetaudience', PARAM_INT);
    }

}
