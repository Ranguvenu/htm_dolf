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

class plugin_meetingdates extends pluginbase {

    public function init() {
        $this->form = false;
        $this->unique = true; 
        $this->placeholder = true;
        $this->singleselection = true;
        $this->fullname = get_string('filtermeetingdates', 'block_learnerscript');
        $this->reporttypes = array('offerings');
        $this->filtertype = 'custom';
        if (!empty($this->reportclass->basicparams)) {
            foreach ($this->reportclass->basicparams as $basicparam) {
                if ($basicparam['name'] == 'meetingdates') {
                    $this->filtertype = 'basic';
                }
            }
        }
    }

    public function summary($data) {
        return get_string('filtermeetingdates_summary', 'block_learnerscript');
    }

    public function execute($finalelements, $data, $filters) {

        $filtermeetingdates = isset($filters['filter_meetingdates']) ? $filters['filter_meetingdates'] : 0;
        if (!$filtermeetingdates) {
            return $finalelements;
        }

        if ($this->report->type != 'sql') {
            return array($filtermeetingdates);
        } else {
            if (preg_match("/%%FILTER_MEETINGDATES:([^%]+)%%/i", $finalelements, $output)) {
                $replace = ' AND ' . $output[1] . ' = ' . $filtermeetingdates;
                return str_replace('%%FILTER_MEETINGDATES:' . $output[1] . '%%', $replace, $finalelements);
            }
        }
        return $finalelements;
    }
    public function filter_data($selectoption = true, $request = array()){
        global $DB, $CFG;
        $properties = new stdClass();
        $properties->meetingdates = SITEID;

        $reportclassname = 'block_learnerscript\lsreports\report_' . $this->report->type;
        $reportclass = new $reportclassname($this->report, $properties);

        if ($this->report->type != 'sql') {
            $components = (new \block_learnerscript\local\ls)->cr_unserialize($this->report->components);
        } else {
            $meetingdateslist = array_keys($DB->get_records('course'));
        }

        $meetingdatesoptions = array();
        if($selectoption){
            $meetingdatesoptions[0] = $this->singleselection ?
                get_string('filter_meetingdates', 'block_learnerscript') : get_string('select') .' '. get_string('meetingdates', 'block_learnerscript');
        }

        if (empty($meetingdateslist)) {
            if(empty($request['filter_code'])){
                $trainingid = $DB->get_field_sql("SELECT id FROM {local_trainingprogram} WHERE 1 = 1 ORDER BY id ASC LIMIT 0, 1");
                $offeringid = $DB->get_field_sql("SELECT id FROM {tp_offerings} WHERE 1 = 1 AND trainingid = $trainingid ORDER BY id ASC LIMIT 0, 1");
                $offeringid = !empty($offeringid) ? $offeringid : 0;
                $meetingdatess = $DB->get_records_sql("SELECT ats.sessdate
                            FROM {offering_sessions} AS ofs 
                        JOIN {attendance_sessions} AS ats ON ofs.sessionid = ats.id 
                            WHERE ofs.offeringid = " . $offeringid);
            }else{
                $meetingdatess = $DB->get_records_sql("SELECT ats.sessdate
                            FROM {offering_sessions} AS ofs 
                        JOIN {attendance_sessions} AS ats ON ofs.sessionid = ats.id 
                            WHERE ofs.offeringid = " . $request['filter_code']);
            }
            if (!empty($meetingdatess)) {
                foreach ($meetingdatess as $tp) {
                    $meetingdatesoptions[$tp->sessdate] = date('jS F Y',$tp->sessdate);
                }
            }
        }
        return $meetingdatesoptions;
    }
    public function selected_filter($selected, $request) {
        $filterdata = $this->filter_data(true, $request);
        return $filterdata[$selected];
    }
    public function print_filter(&$mform) {
        $request = array_merge($_POST, $_GET);
        $meetingdatesoptions = $this->filter_data(true, $request); 
        if ((!$this->placeholder || $this->filtertype == 'basic') && COUNT($meetingdatesoptions) > 1) {
            unset($meetingdatesoptions[0]);
        }
        $select = $mform->addElement('select', 'filter_meetingdates', get_string('meetingdates', 'block_learnerscript'), $meetingdatesoptions, array('data-select2' => 1));
        $select->setHiddenLabel(true);
        $mform->setType('filter_meetingdates', PARAM_INT);
    }

}
