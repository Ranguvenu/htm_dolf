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

class plugin_virtualactivities extends pluginbase {

    public function init() {
        $this->form = false;
        $this->unique = true; 
        $this->placeholder = true;
        $this->singleselection = true;
        $this->fullname = get_string('filtervirtualactivities', 'block_learnerscript');
        $this->reporttypes = array('offerings');
        $this->filtertype = 'custom';
        if (!empty($this->reportclass->basicparams)) {
            foreach ($this->reportclass->basicparams as $basicparam) {
                if ($basicparam['name'] == 'virtualactivities') {
                    $this->filtertype = 'basic';
                }
            }
        }
    }

    public function summary($data) {
        return get_string('filtervirtualactivities_summary', 'block_learnerscript');
    }

    public function execute($finalelements, $data, $filters) {

        $filtervirtualactivities = isset($filters['filter_virtualactivities']) ? $filters['filter_virtualactivities'] : 0;
        if (!$filtervirtualactivities) {
            return $finalelements;
        }

        if ($this->report->type != 'sql') {
            return array($filtervirtualactivities);
        } else {
            if (preg_match("/%%FILTER_VIRTUALACTIVITIES:([^%]+)%%/i", $finalelements, $output)) {
                $replace = ' AND ' . $output[1] . ' = ' . $filtervirtualactivities;
                return str_replace('%%FILTER_VIRTUALACTIVITIES:' . $output[1] . '%%', $replace, $finalelements);
            }
        }
        return $finalelements;
    }
    public function filter_data($selectoption = true, $request = array()){
        global $DB, $CFG;
        $properties = new stdClass();
        $properties->courseid = SITEID;

        $reportclassname = 'block_learnerscript\lsreports\report_' . $this->report->type;
        $reportclass = new $reportclassname($this->report, $properties);

        if ($this->report->type != 'sql') {
            $components = (new \block_learnerscript\local\ls)->cr_unserialize($this->report->components);
        } else {
            $virtualactivitieslist = array_keys($DB->get_records('local_virtualactivities'));
        }

        $virtualactivitiesoptions = array();
        if($selectoption){
            $virtualactivitiesoptions[0] = $this->singleselection ?
                get_string('filter_virtualactivities', 'block_learnerscript') : get_string('select') .' '. get_string('virtualactivities', 'block_learnerscript');
        }

        if (empty($virtualactivitieslist)) {
            $lang = current_language();
            if(empty($request['filter_code'])){
                $trainingid = $DB->get_field_sql("SELECT id FROM {local_trainingprogram} WHERE 1 = 1 ORDER BY id ASC LIMIT 0, 1");
                $offeringid = $DB->get_field_sql("SELECT id FROM {tp_offerings} WHERE 1 = 1 AND trainingid = $trainingid ORDER BY id ASC LIMIT 0, 1");
                $offeringid = !empty($offeringid) ? $offeringid : 0;
                $names = $DB->get_records_sql("SELECT cm.id, m.name AS name, cm.instance
                                FROM {course_modules} cm
                                JOIN {course} as c ON cm.course=c.id
                                JOIN {modules} m ON m.id = cm.module AND m.name IN ('webexactivity', 'teamsmeeting', 'zoom')
                                JOIN {course_sections} cs ON cs.course = c.id AND cs.id = cm.section
                                JOIN {local_trainingprogram} as tp ON tp.courseid=c.id
                                JOIN {tp_offerings} tpo ON tpo.trainingid = tp.id 
                                WHERE 1 = 1 AND tpo.id = $offeringid");  
                if (!empty($names)) {
                    foreach ($names as $n) {
                        if ($n->name == 'zoom') {
                            $zoomname = $DB->get_field_sql("SELECT name FROM {zoom} WHERE id = $n->instance");
                            $virtualactivitiesoptions[$n->id] = format_string($zoomname);
                        } else if ($n->name == 'webexactivity') {
                            $webexactivity = $DB->get_field_sql("SELECT name FROM {webexactivity} WHERE id = $n->instance");
                            $virtualactivitiesoptions[$n->id] = format_string($webexactivity);
                        } else if ($n->name == 'teamsmeeting') {
                            $teamsmeeting = $DB->get_field_sql("SELECT name FROM {teamsmeeting} WHERE id = $n->instance");
                            $virtualactivitiesoptions[$n->id] = format_string($teamsmeeting);
                        }
                    }
                } 
            }else{
                $names = $DB->get_records_sql("SELECT cm.id, m.name AS name, cm.instance
                                FROM {course_modules} cm
                                JOIN {course} as c ON cm.course=c.id
                                JOIN {modules} m ON m.id = cm.module AND m.name IN ('webexactivity', 'teamsmeeting', 'zoom')
                                JOIN {course_sections} cs ON cs.course = c.id AND cs.id = cm.section
                                JOIN {local_trainingprogram} as tp ON tp.courseid=c.id
                                JOIN {tp_offerings} tpo ON tpo.trainingid = tp.id
                                WHERE 1 = 1 AND tpo.id = " . $request['filter_code']);  
                foreach ($names as $n) {
                    if ($n->name == 'zoom') {
                        $zoomname = $DB->get_field_sql("SELECT name FROM {zoom} WHERE id = $n->instance");
                        $virtualactivitiesoptions[$n->id] = format_string($zoomname);
                    } else if ($n->name == 'webexactivity') {
                        $webexactivity = $DB->get_field_sql("SELECT name FROM {webexactivity} WHERE id = $n->instance");
                        $virtualactivitiesoptions[$n->id] = format_string($webexactivity);
                    } else if ($n->name == 'teamsmeeting') {
                        $teamsmeeting = $DB->get_field_sql("SELECT name FROM {teamsmeeting} WHERE id = $n->instance");
                        $virtualactivitiesoptions[$n->id] = format_string($teamsmeeting);
                    }
                }
            }
        }
        return $virtualactivitiesoptions;
    }
    public function selected_filter($selected, $request) {
        $filterdata = $this->filter_data(true, $request);
        return $filterdata[$selected];
    }
    public function print_filter(&$mform) {
        $request = array_merge($_POST, $_GET);
        $virtualactivitiesoptions = $this->filter_data(true, $request); 
        if ((!$this->placeholder || $this->filtertype == 'basic') && COUNT($virtualactivitiesoptions) > 1) {
            unset($virtualactivitiesoptions[0]);
        }
        $select = $mform->addElement('select', 'filter_virtualactivities', get_string('virtualactivities', 'block_learnerscript'), $virtualactivitiesoptions, array('data-select2' => 1));
        $select->setHiddenLabel(true);
        $mform->setType('filter_virtualactivities', PARAM_INT);
    }

}
