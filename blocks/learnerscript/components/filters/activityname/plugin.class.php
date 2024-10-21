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

class plugin_activityname extends pluginbase {

    public function init() {
        $this->form = false;
        $this->unique = true; 
        $this->placeholder = true;
        $this->singleselection = true;
        $this->fullname = get_string('activityname', 'block_learnerscript');
        $this->reporttypes = array('productlog');
        $this->filtertype = 'custom';
        if (!empty($this->reportclass->basicparams)) {
            foreach ($this->reportclass->basicparams as $basicparam) {
                if ($basicparam['name'] == 'activityname') {
                    $this->filtertype = 'basic';
                }
            }
        }
    }

    public function summary($data) {
        return get_string('activityname_summary', 'block_learnerscript');
    }

    public function execute($finalelements, $data, $filters) {

        $activityname = isset($filters['filter_activityname']) ? $filters['filter_activityname'] : 0;
        if (!$activityname) {
            return $finalelements;
        }

        if ($this->report->type != 'sql') {
            return array($activityname);
        } else {
            if (preg_match("/%%FILTER_activityname:([^%]+)%%/i", $finalelements, $output)) {
                $replace = ' AND ' . $output[1] . ' = ' . $activityname;
                return str_replace('%%FILTER_activityname:' . $output[1] . '%%', $replace, $finalelements);
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
            $activitynamelist = array();
        }

        $activitynameoptions = array();
        if($selectoption){
            $activitynameoptions[0] = $this->singleselection ?
                get_string('filter_activityname', 'block_learnerscript') : get_string('select') .' '. get_string('activityname', 'block_learnerscript');
        }

        if (empty($activitynamelist)) {
            if($this->report->type == 'revenue'){
                $sqlquery = "SELECT DISTINCT tpt.id , pt.referenceid, pt.category, CASE pt.category
                            WHEN '1' THEN lt.name
                            WHEN '2' THEN ex.exam
                            WHEN '3' THEN le.title
                            WHEN '4' THEN legex.exam
                            WHEN '5' THEN ll.name
                            WHEN '6' THEN eatex.exam
                            ELSE '--'
                            END AS 'activityname',
                            CASE pt.category
                            WHEN '1' THEN lt.namearabic
                            WHEN '2' THEN ex.examnamearabic
                            WHEN '3' THEN le.titlearabic
                            WHEN '4' THEN legex.examnamearabic
                            WHEN '5' THEN ll.namearabic
                            WHEN '6' THEN eatex.examnamearabic
                            ELSE '--'
                            END AS 'activitynamearabic'
                            FROM {tool_product_telr} tpt
                            JOIN {tool_user_order_payments} tuop ON tpt.id = tuop.telrid
                            JOIN {tool_products} pt ON pt.id = tuop.productid
                            LEFT JOIN {tp_offerings} tp ON tp.id = pt.referenceid AND pt.category =1
                            LEFT JOIN {local_trainingprogram} lt ON lt.id = tp.trainingid
                            LEFT JOIN {local_exam_profiles} ep ON ep.id = pt.referenceid AND pt.category = 2
                            LEFT JOIN {local_exams} ex ON ex.id = ep.examid
                            LEFT JOIN {local_events} le ON le.id = pt.referenceid AND pt.category =3
                            LEFT JOIN {local_exam_grievance} leg ON leg.id = pt.referenceid AND pt.category =4
                            LEFT JOIN {local_exams} legex ON legex.id = leg.examid 
                            LEFT JOIN {local_learningtracks} ll ON ll.id = pt.referenceid AND pt.category =5
                            LEFT JOIN {local_exam_attempts} lea ON lea.id = pt.referenceid AND pt.category = 6
                            LEFT JOIN {local_exams} eatex ON eatex.id = lea.examid
                            WHERE 1=1 ORDER BY tpt.id DESC";
                    $querydata = $DB->get_records_sql($sqlquery);
                }else{
                     $sqlquery = "SELECT DISTINCT si.id ,pt.referenceid, pt.category, CASE pt.category
                            WHEN '1' THEN lt.name
                            WHEN '2' THEN ex.exam
                            WHEN '3' THEN le.title
                            WHEN '4' THEN legex.exam
                            WHEN '5' THEN ll.name
                            WHEN '6' THEN eatex.exam
                            ELSE '--'
                            END AS 'activityname',
                            CASE pt.category
                            WHEN '1' THEN lt.namearabic
                            WHEN '2' THEN ex.examnamearabic
                            WHEN '3' THEN le.titlearabic
                            WHEN '4' THEN legex.examnamearabic
                            WHEN '5' THEN ll.namearabic
                            WHEN '6' THEN eatex.examnamearabic
                            ELSE '--'
                            END AS 'activitynamearabic'
                            FROM {tool_product_sadad_invoice} si
                            JOIN {tool_products} pt ON pt.id = si.productid
                            LEFT JOIN {tp_offerings} tp ON tp.id = pt.referenceid AND pt.category =1
                            LEFT JOIN {local_trainingprogram} lt ON lt.id = tp.trainingid
                            LEFT JOIN {local_exam_profiles} ep ON ep.id = pt.referenceid AND pt.category = 2
                            LEFT JOIN {local_exams} ex ON ex.id = ep.examid
                            LEFT JOIN {local_events} le ON le.id = pt.referenceid AND pt.category =3
                            LEFT JOIN {local_exam_grievance} leg ON leg.id = pt.referenceid AND pt.category =4
                            LEFT JOIN {local_exams} legex ON legex.id = leg.examid 
                            LEFT JOIN {local_learningtracks} ll ON ll.id = pt.referenceid AND pt.category =5
                            LEFT JOIN {local_exam_attempts} lea ON lea.id = pt.referenceid AND pt.category = 6
                            LEFT JOIN {local_exams} eatex ON eatex.id = lea.examid
                            WHERE 1=1 ORDER BY si.id DESC";
                    $querydata = $DB->get_records_sql($sqlquery);
                }
                
                    foreach($querydata AS $rec){
                        $lang = current_language();
                        if( $lang == 'ar' ){
                            $activitynameoptions[$rec->activityname] = $rec->activityname;
                        } else {
                           $activitynameoptions[$rec->activitynamearabic] = $rec->activitynamearabic;                    
                        }
                        
                    }
           
        }
        return $activitynameoptions;
    }
    public function selected_filter($selected) {
        $filterdata = $this->filter_data();
        return $filterdata[$selected];
    }
    public function print_filter(&$mform) {
        $activitynameoptions = $this->filter_data(); 
        if ((!$this->placeholder || $this->filtertype == 'basic') && COUNT($activitynameoptions) > 1) {
            unset($activitynameoptions[0]);
        }
        $select = $mform->addElement('select', 'filter_activityname', get_string('activityname', 'block_learnerscript'), $activitynameoptions, array('data-select2' => 1));
        $select->setHiddenLabel(true);
        $mform->setType('filter_activityname', PARAM_INT);
    }

}
