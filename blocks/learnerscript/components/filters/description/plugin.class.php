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

class plugin_description extends pluginbase {

    public function init() {
        $this->form = false;
        $this->unique = true; 
        $this->placeholder = true;
        $this->singleselection = true;
        $this->fullname = get_string('description', 'block_learnerscript');
        $this->reporttypes = array('productlog');
        $this->filtertype = 'custom';
        if (!empty($this->reportclass->basicparams)) {
            foreach ($this->reportclass->basicparams as $basicparam) {
                if ($basicparam['name'] == 'description') {
                    $this->filtertype = 'basic';
                }
            }
        }
    }

    public function summary($data) {
        return get_string('description_summary', 'block_learnerscript');
    }

    public function execute($finalelements, $data, $filters) {

        $description = isset($filters['filter_description']) ? $filters['filter_description'] : 0;
        if (!$description) {
            return $finalelements;
        }

        if ($this->report->type != 'sql') {
            return array($description);
        } else {
            if (preg_match("/%%FILTER_DESCRIPTION:([^%]+)%%/i", $finalelements, $output)) {
                $replace = ' AND ' . $output[1] . ' = ' . $description;
                return str_replace('%%FILTER_DESCRIPTION:' . $output[1] . '%%', $replace, $finalelements);
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
            $descriptionlist = array();
        }

        $descriptionoptions = array();
        if($selectoption){
            $descriptionoptions[0] = $this->singleselection ?
                get_string('filter_description', 'block_learnerscript') : get_string('select') .' '. get_string('description', 'block_learnerscript');
        }

        if (empty($descriptionlist)) {
             $description = $DB->get_records_select('local_trainingprogram', '', array(), '', 'description');

            foreach ($description as $td) {
                $descriptionoptions[$td->description] = format_string($td->description);
            }
        }
        return $descriptionoptions;
    }
    public function selected_filter($selected) {
        $filterdata = $this->filter_data();
        return $filterdata[$selected];
    }
    public function print_filter(&$mform) {
        $descriptionoptions = $this->filter_data(); 
        if ((!$this->placeholder || $this->filtertype == 'basic') && COUNT($descriptionoptions) > 1) {
            unset($descriptionoptions[0]);
        }
        $select = $mform->addElement('text', 'filter_description', '', array('placeholder' => get_string('filter_description', 'block_learnerscript')), array('data-select2' => 1));
    }

}
