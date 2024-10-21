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

class plugin_organization extends pluginbase {

    public function init() {
        $this->form = false;
        $this->unique = true; 
        $this->placeholder = true;
        $this->singleselection = true;
        $this->fullorganization = get_string('organization', 'block_learnerscript');
        $this->reporttypes = array('');
        $this->filtertype = 'custom';
        if (!empty($this->reportclass->basicparams)) {
            foreach ($this->reportclass->basicparams as $basicparam) {
                if ($basicparam['organization'] == 'organization') {
                    $this->filtertype = 'basic';
                }
            }
        }
    }

    public function summary($data) {
        return get_string('filterorganization_summary', 'block_learnerscript');
    }

    public function execute($finalelements, $data, $filters) {

        $filterorganization = isset($filters['filter_organization']) ? $filters['filter_organization'] : 0;
        if (!$filterorganization) {
            return $finalelements;
        }

        if ($this->report->type != 'sql') {
            return array($filterorganization);
        } else {
            if (preg_match("/%%FILTER_ORGANIZATION:([^%]+)%%/i", $finalelements, $output)) {
                $replace = ' AND ' . $output[1] . ' = ' . $filterorganization;
                return str_replace('%%FILTER_ORGANIZATION:' . $output[1] . '%%', $replace, $finalelements);
            }
        }
        return $finalelements;
    }
    public function filter_data($selectoption = true){
        global $DB, $CFG;
        $properties = new stdClass();
        $properties->courseid = SITEID;

        $reportclassorganization = 'block_learnerscript\lsreports\report_' . $this->report->type;
        $reportclass = new $reportclassorganization($this->report, $properties);

        if ($this->report->type != 'sql') {
            $components = (new \block_learnerscript\local\ls)->cr_unserialize($this->report->components);
        } else {
            $organizationlist = array_keys($DB->get_records('local_organization'));
        }

        $organizationoptions = array();
        if($selectoption){
            $organizationoptions[0] = $this->singleselection ?
                get_string('filter_organization', 'block_learnerscript') : get_string('filter_organization', 'block_learnerscript');
        }
        if (empty($organizationlist)) {
            $lang = current_language();
            if ($lang == 'en'){
                $organizations = $DB->get_records_select('local_organization', '', array(), '', 'id, fullname');
            }else{
                $organizations = $DB->get_records_sql("SELECT id, fullnameinarabic AS fullname FROM {local_organization}");

            }

            foreach ($organizations as $e) {
                $organizationoptions[$e->id] = format_string($e->fullname);
            }
        }
        return $organizationoptions;
    }
    public function selected_filter($selected) {
        $filterdata = $this->filter_data();
        return $filterdata[$selected];
    }
    public function print_filter(&$mform) {
        $organizationoptions = $this->filter_data(); 
        if ((!$this->placeholder || $this->filtertype == 'basic') && COUNT($organizationoptions) > 1) {
            unset($organizationoptions[0]);
        }
        $select = $mform->addElement('select', 'filter_organization', get_string('organization', 'block_learnerscript'), $organizationoptions, array('data-select2' => 1));
        $select->setHiddenLabel(true);
        $mform->setType('filter_organization', PARAM_INT);
    }

}
