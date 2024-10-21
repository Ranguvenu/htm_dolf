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

class plugin_startdate extends pluginbase {

    public function init() {
        $this->form = false;
        $this->unique = true; 
        $this->placeholder = true;
        $this->singleselection = true;
        $this->fullname = get_string('filterstartdate', 'block_learnerscript');
        $this->reporttypes = array('offerings');
        $this->filtertype = 'custom';
        if (!empty($this->reportclass->basicparams)) {
            foreach ($this->reportclass->basicparams as $basicparam) {
                if ($basicparam['name'] == 'startdate') {
                    $this->filtertype = 'basic';
                }
            }
        }
    }

    public function summary($data) {
        return get_string('filterstartdate_summary', 'block_learnerscript');
    }

    public function execute($finalelements, $data, $filters) {

        $filterstartdate = isset($filters['filter_startdate']) ? $filters['filter_startdate'] : 0;
        if (!$filterstartdate) {
            return $finalelements;
        }

        if ($this->report->type != 'sql') {
            return array($filterstartdate);
        } else {
            if (preg_match("/%%FILTER_STARTDATE:([^%]+)%%/i", $finalelements, $output)) {
                $replace = ' AND ' . $output[1] . ' = ' . $filterstartdate;
                return str_replace('%%FILTER_STARTDATE:' . $output[1] . '%%', $replace, $finalelements);
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
            $component = (new \block_learnerscript\local\ls)->cr_unserialize($this->report->startdate);
        } else {
            $startdatelist = array();
        }

        $startdateoptions = array();
        if($selectoption){
            $startdateoptions[0] = $this->singleselection ?
                get_string('filter_startdate', 'block_learnerscript') : get_string('select') .' '. get_string('startdate', 'block_learnerscript');
        }

        if (empty($startdatelist)) {
            $startdateoptions = '';
        }
        return $startdateoptions;
    }
    public function selected_filter($selected) {
        $filterdata = $this->filter_data();
        return $filterdata[$selected];
    }
    public function print_filter(&$mform) {
        $startdateoptions = $this->filter_data(); 
        if ((!$this->placeholder || $this->filtertype == 'basic') && COUNT($startdateoptions) > 1) {
            unset($startdateoptions[0]);
        }
        if ($this->report->type == 'programenrol') {
            $test = array();
            $test[] =& $mform->createElement('date_selector', 'date_examfromtime', get_string('fromtime', 'block_learnerscript'),array('optional' => true));
            $test[] =& $mform->createElement('date_selector', 'date_examtotime', get_string('totime', 'block_learnerscript'),array('optional' => true));
            $mform->addGroup($test, 'filter_startdate', get_string('ofstartdate', 'block_learnerscript'), ' ', false);       
        } else if($this->report->type == 'revenue' || $this->report->type == 'transaction'){
            $mform->addElement('date_selector', 'startdatefrom', get_string('startdatefrom', 'block_learnerscript'),array('optional'=>true));
            $mform->setType('startdatefrom', PARAM_RAW);

            $mform->addElement('date_selector', 'startdateto', get_string('startdateto', 'block_learnerscript'),array('optional'=>true));
            $mform->setType('startdateto', PARAM_RAW);
        } else if ($this->report->type == 'examenrol') {
            $test = array();
            $test[] =& $mform->createElement('date_selector', 'date_examfromtime', get_string('fromtime', 'block_learnerscript'),array('optional' => true));
            $test[] =& $mform->createElement('date_selector', 'date_examtotime', get_string('totime', 'block_learnerscript'),array('optional' => true));
            $mform->addGroup($test, 'filter_startdate', get_string('examdate', 'block_learnerscript'), ' ', false);
        } else if ($this->report->type == 'eventenrol') {
            $test = array();
            $test[] =& $mform->createElement('date_selector', 'date_examfromtime', get_string('fromtime', 'block_learnerscript'),array('optional' => true));
            $test[] =& $mform->createElement('date_selector', 'date_examtotime', get_string('totime', 'block_learnerscript'),array('optional' => true));
            $mform->addGroup($test, 'filter_startdate', get_string('eventdate', 'block_learnerscript'), ' ', false);
        } else if ($this->report->type == 'traineeactivities') {
            $test = array();
            $test[] =& $mform->createElement('date_selector', 'date_examfromtime', get_string('fromtime', 'block_learnerscript'),array('optional' => true));
            $test[] =& $mform->createElement('date_selector', 'date_examtotime', get_string('totime', 'block_learnerscript'),array('optional' => true));
            $mform->addGroup($test, 'filter_startdate', get_string('activitystartdate', 'block_learnerscript'), ' ', false);
        } else {
            $mform->addElement('date_selector', 'filter_startdate', get_string('startdate', 'block_learnerscript'),array('optional' => true));
        }
        
        $mform->addElement('hidden',  'visible',  0);
        $mform->setType('visible', PARAM_INT);
    }

}
