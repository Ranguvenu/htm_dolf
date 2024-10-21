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

class plugin_targetgroup extends pluginbase {

    public function init() {
        $this->form = false;
        $this->unique = true; 
        $this->placeholder = true;
        $this->singleselection = true;
        $this->fullname = get_string('filtertargetgroup', 'block_learnerscript');
        $this->reporttypes = array('offerings');
        $this->filtertype = 'custom';
        if (!empty($this->reportclass->basicparams)) {
            foreach ($this->reportclass->basicparams as $basicparam) {
                if ($basicparam['name'] == 'targetgroup') {
                    $this->filtertype = 'basic';
                }
            }
        }
    }

    public function summary($data) {
        return get_string('filtertargetgroup_summary', 'block_learnerscript');
    }

    public function execute($finalelements, $data, $filters) {

        $filtertargetgroup = isset($filters['filter_targetgroup']) ? $filters['filter_targetgroup'] : 0;
        if (!$filtertargetgroup) {
            return $finalelements;
        }

        if ($this->report->type != 'sql') {
            return array($filtertargetgroup);
        } else {
            if (preg_match("/%%FILTER_TARGETGROUP:([^%]+)%%/i", $finalelements, $output)) {
                $replace = ' AND ' . $output[1] . ' = ' . $filtertargetgroup;
                return str_replace('%%FILTER_TARGETGROUP:' . $output[1] . '%%', $replace, $finalelements);
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
            $targetgrouplist = array_keys($DB->get_records('local_targetgroup'));
        }

        $targetgroupoptions = array();
        if($selectoption){
            $targetgroupoptions[0] = $this->singleselection ?
                get_string('filter_targetgroup', 'block_learnerscript') : get_string('select') .' '. get_string('targetgroup', 'block_learnerscript');
        }

        if (empty($targetgrouplist)) {
            $lang = current_language();
            if( $lang == 'ar' ){
               $targetgroups = $DB->get_records_select('local_jobfamily', '', array(), '', 'id, familynamearabic');
               foreach ($targetgroups as $tp) {
                    $targetgroupoptions[$tp->id] = format_string($tp->familynamearabic);
                }

            } else {
                $targetgroups = $DB->get_records_select('local_jobfamily', '', array(), '', 'id, familyname');
                foreach ($targetgroups as $tp) {
                    $targetgroupoptions[$tp->id] = format_string($tp->familyname);
                }
        
            }
            
        }
        return $targetgroupoptions;
    }
    public function selected_filter($selected) {
        $filterdata = $this->filter_data();
        return $filterdata[$selected];
    }
    public function print_filter(&$mform) {
        $targetgroupoptions = $this->filter_data(); 
        if ((!$this->placeholder || $this->filtertype == 'basic') && COUNT($targetgroupoptions) > 1) {
            unset($targetgroupoptions[0]);
        }
        $select = $mform->addElement('select', 'filter_targetgroup', get_string('targetgroup', 'block_learnerscript'), $targetgroupoptions, array('data-select2' => 1));
        $select->setHiddenLabel(true);
        $mform->setType('filter_targetgroup', PARAM_INT);
    }

}
