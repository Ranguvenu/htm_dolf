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

class plugin_shortname extends pluginbase {

    public function init() {
        $this->form = false;
        $this->unique = true; 
        $this->placeholder = true;
        $this->singleselection = true;
        $this->shortname = get_string('shortname', 'block_learnerscript');
        $this->reporttypes = array('shortnameprofiles');
        $this->filtertype = 'custom';
        if (!empty($this->reportclass->basicparams)) {
            foreach ($this->reportclass->basicparams as $basicparam) {
                if ($basicparam['name'] == 'shortname') {
                    $this->filtertype = 'basic';
                }
            }
        }
    }

    public function summary($data) {
        return get_string('filtershortname_summary', 'block_learnerscript');
    }

    public function execute($finalelements, $data, $filters) {

        $filtershortname = isset($filters['filter_shortname']) ? $filters['filter_shortname'] : 0;
        if (!$filtershortname) {
            return $finalelements;
        }

        if ($this->report->type != 'sql') {
            return array($filtershortname);
        } else {
            if (preg_match("/%%FILTER_SHORTNAME:([^%]+)%%/i", $finalelements, $output)) {
                $replace = ' AND ' . $output[1] . ' = ' . $filtershortname;
                return str_replace('%%FILTER_SHORTNAME:' . $output[1] . '%%', $replace, $finalelements);
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
            $shortnamelist = array_keys($DB->get_records('local_organization'));
        }

        $shortnameoptions = array();
        if($selectoption){
            $shortnameoptions[0] = $this->singleselection ?
                get_string('filter_shortname', 'block_learnerscript') : get_string('select') .' '. get_string('shortname', 'block_learnerscript');
        }

        if (empty($shortnamelist)) {
            $shortnames = $DB->get_records_sql('SELECT le.id, le.shortname FROM {local_organization} le');
            
            foreach ($shortnames as $e) {
                $shortnameoptions[$e->shortname] = format_string($e->shortname);
            }
        }
        return $shortnameoptions;
    }
    public function selected_filter($selected) {
        $filterdata = $this->filter_data();
        return $filterdata[$selected];
    }
    public function print_filter(&$mform) {
        $shortnameoptions = $this->filter_data(); 
        if ((!$this->placeholder || $this->filtertype == 'basic') && COUNT($shortnameoptions) > 1) {
            unset($shortnameoptions[0]);
        }
        $select = $mform->addElement('select', 'filter_shortname', get_string('shortname', 'block_learnerscript'), $shortnameoptions, array('data-select2' => 1));
        $select->setHiddenLabel(true);
        $mform->setType('filter_shortname', PARAM_INT);
    }

}
