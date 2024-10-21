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

class plugin_moduleactivityname extends pluginbase {

    public function init() {
        $this->form = false;
        $this->unique = true; 
        $this->placeholder = true;
        $this->singleselection = true;
        $this->fullname = get_string('moduleactivityname', 'block_learnerscript');
        $this->reporttypes = array();
        $this->filtertype = 'custom';
        if (!empty($this->reportclass->basicparams)) {
            foreach ($this->reportclass->basicparams as $basicparam) {
                if ($basicparam['name'] == 'moduleactivitynamelist') {
                    $this->filtertype = 'basic';
                }
            }
        }
    }

    public function summary($data) {
        return get_string('moduleactivityname_summary', 'block_learnerscript');
    }

    public function execute($finalelements, $data, $filters) {

        $moduleactivitynamelist = isset($filters['filter_moduleactivityname']) ? $filters['filter_moduleactivitynamelist'] : 0;
        if (!$moduleactivitynamelist) {
            return $finalelements;
        }

        if ($this->report->type != 'sql') {
            return array($moduleactivitynamelist);
        } else {
            if (preg_match("/%%FILTER_MODULEACTIVIYNAME:([^%]+)%%/i", $finalelements, $output)) {
                $replace = ' AND ' . $output[1] . ' = ' . $moduleactivitynamelist;
                return str_replace('%%FILTER_MODULEACTIVIYNAME:' . $output[1] . '%%', $replace, $finalelements);
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
            $moduleactivitynames = array();
        }

        $moduleactivitynameoptions = array();
        if($selectoption){
            $moduleactivitynameoptions[0] = get_string('filter_moduleactivityname', 'block_learnerscript');
        }

        if (empty($moduleactivitynames)) {
                $sql = "SELECT name AS name, namearabic AS arabicname FROM {local_trainingprogram} WHERE 1=1
                        UNION SELECT exam AS name, examnamearabic AS arabicname FROM {local_exams} WHERE 1=1 
                        UNION SELECT title AS name, titlearabic AS arabicname FROM {local_events} WHERE 1=1
                        UNION SELECT name AS name, namearabic AS arabicname FROM {local_learningtracks} WHERE 1=1";
                $querydata = $DB->get_records_sql($sql);
                foreach($querydata AS $rec){
                    $lang = current_language();
                        if($lang == 'en'){
                            $moduleactivitynameoptions[$rec->name] = $rec->name; 
                        }else{
                            $moduleactivitynameoptions[$rec->arabicname] = $rec->arabicname; 
                        }
                                           
                }
            }
           
        return $moduleactivitynameoptions;
    }
    public function selected_filter($selected) {
        $filterdata = $this->filter_data();
        return $filterdata[$selected];
    }
    public function print_filter(&$mform) {
        $moduleactivitynameoptions = $this->filter_data(); 
        if ((!$this->placeholder || $this->filtertype == 'basic') && COUNT($moduleactivitynameoptions) > 1) {
            unset($moduleactivitynameoptions[0]);
        }
        $select = $mform->addElement('select', 'filter_moduleactivitynamelist', get_string('moduleactivitynamelist', 'block_learnerscript'), $moduleactivitynameoptions, array('data-select2' => 1));
        $select->setHiddenLabel(true);
        $mform->setType('filter_moduleactivitynamelist', PARAM_INT);
    }

}
