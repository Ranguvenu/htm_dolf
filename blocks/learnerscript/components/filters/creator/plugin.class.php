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

class plugin_creator extends pluginbase {

    public function init() {
        $this->form = false;
        $this->unique = true; 
        $this->placeholder = true;
        $this->singleselection = true;
        $this->fullname = get_string('creator', 'block_learnerscript');
        $this->reporttypes = array('creatorprofiles');
        $this->filtertype = 'custom';
        if (!empty($this->reportclass->basicparams)) {
            foreach ($this->reportclass->basicparams as $basicparam) {
                if ($basicparam['name'] == 'creator') {
                    $this->filtertype = 'basic';
                }
            }
        }
    }

    public function summary($data) {
        return get_string('filtercreator_summary', 'block_learnerscript');
    }

    public function execute($finalelements, $data, $filters) {

        $filtercreator = isset($filters['filter_creator']) ? $filters['filter_creator'] : 0;
        if (!$filtercreator) {
            return $finalelements;
        }

        if ($this->report->type != 'sql') {
            return array($filtercreator);
        } else {
            if (preg_match("/%%FILTER_CREATOR:([^%]+)%%/i", $finalelements, $output)) {
                $replace = ' AND ' . $output[1] . ' = ' . $filtercreator;
                return str_replace('%%FILTER_CREATOR:' . $output[1] . '%%', $replace, $finalelements);
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
            $creatorlist = array_keys($DB->get_records('local_organization'));
        }

        $creatoroptions = array();
        if($selectoption){
            $creatoroptions[0] = $this->singleselection ?
                get_string('filter_creator', 'block_learnerscript') : get_string('select') .' '. get_string('creator', 'block_learnerscript');
        }

        if (empty($creatorlist)) {
            $lang = current_language();
            if ($lang == 'ar') {
                $creators = $DB->get_records_sql("SELECT DISTINCT tpo.usercreated AS userid, CONCAT(lu.firstnamearabic, ' ', lu.lastnamearabic) AS creator FROM {tp_offerings} tpo  
                    LEFT JOIN {local_users} lu ON lu.userid = tpo.usercreated
                    WHERE 1 = 1");
            } else {
                $creators = $DB->get_records_sql("SELECT DISTINCT tpo.usercreated AS userid, CONCAT(lu.firstname, ' ', lu.lastname) AS creator FROM {tp_offerings} tpo  
                    LEFT JOIN {local_users} lu ON lu.userid = tpo.usercreated
                    WHERE 1 = 1");
            }  
            foreach ($creators as $e) { 
                $username = $DB->get_field_sql("SELECT username FROM {user} u WHERE u.id = $e->userid");
                if($lang == 'en'){
                   $creatorname = format_string($e->creator);
                }else{
                    if($username == 'admin'){ 
                        $creatorname = 'مسؤول النظام';
                    } else {
                        $creatorname = format_string($e->creator);
                    }
                }
                if(empty($creatorname)){
                    $creatoroptions[$e->userid] = $DB->get_field_sql("SELECT CONCAT(u.firstname, ' ', u.lastname) FROM {user} u WHERE u.id = $e->userid");
                } else {
                    $creatoroptions[$e->userid] = $creatorname;
                }
                
            }
        }
        return $creatoroptions;
    }
    public function selected_filter($selected) {
        $filterdata = $this->filter_data();
        return $filterdata[$selected];
    }
    public function print_filter(&$mform) {
        $creatoroptions = $this->filter_data(); 
        if ((!$this->placeholder || $this->filtertype == 'basic') && COUNT($creatoroptions) > 1) {
            unset($creatoroptions[0]);
        }
        $select = $mform->addElement('select', 'filter_creator', get_string('creator', 'block_learnerscript'), $creatoroptions, array('data-select2' => 1));
        $select->setHiddenLabel(true);
        $mform->setType('filter_creator', PARAM_INT);
    }

}
