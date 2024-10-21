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

class plugin_certificate extends pluginbase {

    public function init() {
        $this->form = false;
        $this->unique = true; 
        $this->placeholder = true;
        $this->singleselection = true;
        $this->fullname = get_string('filtercertificate', 'block_learnerscript');
        $this->reporttypes = array('offerings');
        $this->filtertype = 'custom';
        if (!empty($this->reportclass->basicparams)) {
            foreach ($this->reportclass->basicparams as $basicparam) {
                if ($basicparam['name'] == 'certificate') {
                    $this->filtertype = 'basic';
                }
            }
        }
    }

    public function summary($data) {
        return get_string('filtercertificate_summary', 'block_learnerscript');
    }

    public function execute($finalelements, $data, $filters) {

        $filtercertificate = isset($filters['filter_certificate']) ? $filters['filter_certificate'] : 0;
        if (!$filtercertificate) {
            return $finalelements;
        }

        if ($this->report->type != 'sql') {
            return array($filtercertificate);
        } else {
            if (preg_match("/%%FILTER_CERTIFICATE:([^%]+)%%/i", $finalelements, $output)) {
                $replace = ' AND ' . $output[1] . ' = ' . $filtercertificate;
                return str_replace('%%FILTER_CERTIFICATE:' . $output[1] . '%%', $replace, $finalelements);
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
            $certificatelist = array_keys($DB->get_records('tool_certificate_templates'));
        }

        $certificateoptions = array();
        if($selectoption){
            $certificateoptions[-1] = $this->singleselection ?
                get_string('filter_certificate', 'block_learnerscript') : get_string('select') .' '. get_string('certificate', 'block_learnerscript');
        }

        if (empty($certificatelist)) {

            if($this->report->type == 'events'){
                $certificatelist = $DB->get_records_sql("SELECT tc.id, tc.name  FROM {tool_certificate_templates} tc");
                if($certificatelist) {
                    foreach ($certificatelist AS $cer){
                        $certificateoptions[$cer->id] = $cer->name;
                    }
                }

            }else{
                $categoryid = $DB->get_field('course_categories', 'id', ['idnumber' => 'events']);
                if($categoryid) {
                    $contextid = \context_coursecat::instance($categoryid);
                    $certificatelist = $DB->get_records_sql("SELECT tc.id, tc.name  FROM {tool_certificate_templates} tc WHERE  contextid = $contextid->id");
                    if($certificatelist) {
                        foreach ($certificatelist AS $cer){
                            $certificateoptions[$cer->id]=$cer->name;
                        }
                    }
                }

            }
        }
        return $certificateoptions;
    }
    public function selected_filter($selected) {
        $filterdata = $this->filter_data();
        return $filterdata[$selected];
    }
    public function print_filter(&$mform) {
        $certificateoptions = $this->filter_data(); 
        if ((!$this->placeholder || $this->filtertype == 'basic') && COUNT($certificateoptions) > 1) {
            unset($certificateoptions[0]);
        }
        $select = $mform->addElement('select', 'filter_certificate', get_string('certificate', 'block_learnerscript'), $certificateoptions, array('data-select2' => 1));
        $select->setHiddenLabel(true);
        $mform->setType('filter_certificate', PARAM_INT);
    }

}
