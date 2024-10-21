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
 * LearnerScript
 * A Moodle block for creating customizable reports
 * @package blocks
 * @author: Revanth Kumar Grandhi
 * @date: 2022
 */
namespace block_learnerscript\lsreports;
use block_learnerscript\local\reportbase;
use block_learnerscript\report;
use context_system;

defined('MOODLE_INTERNAL') || die();
class report_userevents extends reportbase implements report {

    public function __construct($report, $reportproperties) {
        global $DB, $USER;
        $systemcontext = context_system::instance();
        parent::__construct($report, $reportproperties);
        $columns = ['eventtitle', 'enrolleddate'];
        $this->columns = ['eventfields' => ['eventfields'], 'usereventcolumns' => $columns];
        $this->components = array('columns', 'conditions', 'ordering', 'filters','permissions', 'plot');
        $this->parent = true;
        $this->filters = array('eventfilters');
        $this->orderable = array('eventtitle', 'enrolleddate');
        $this->searchable = array('le.title', 'le.titlearabic');

        if (is_siteadmin() || $this->role != 'trainee') {
            $this->basicparams = [['name' => 'users']];
        }

        $this->defaultcolumn = 'le.id';
        $this->excludedroles = array("'manager', 'coursecreator', 'editingteacher', 'teacher', 'student', 'trainer', 'examofficial', 'co', 'expert', 'organizationofficial', 'to', 'competencies_official', 'cpd', 'hall_manager', 'financial_manager'");
    }

    public function init() {
        if (!$this->scheduling && isset($this->basicparams) && !empty($this->basicparams)) {
            $basicparams = array_column($this->basicparams, 'name');
            foreach ($basicparams as $basicparam) {
                if (empty($this->params['filter_' . $basicparam])) {
                    return false;
                }
            }
        } 
    }
    public function count() {
       $this->sql = "SELECT COUNT(DISTINCT le.id)";

    }
    public function select() {
        $lang= current_language();
        if ($lang == 'ar') {
            $this->sql = "SELECT DISTINCT le.id , le.titlearabic AS eventtitle, le.*, lea.timecreated AS enrolleddate ";
        } else {
            $this->sql = "SELECT DISTINCT le.id , le.title AS eventtitle, le.*, lea.timecreated AS enrolleddate ";
        }
        parent::select();
    }

    public function from() {
        $this->sql .= " FROM {local_events} le 
                        JOIN {local_event_attendees} lea ON lea.eventid = le.id ";
    }
    public function joins() {
        parent::joins();
    }

    public function where() {
        global $USER;
        $userid = isset($this->params['filter_users']) && $this->params['filter_users'] > 0
                    ? $this->params['filter_users'] : $this->userid;
        $this->params['userid'] = $userid;
        $this->sql .= " WHERE 1 = 1 AND lea.userid = :userid ";
        parent::where();
    }

    public function search() {
      global $DB;
        if (isset($this->search) && $this->search) {
            $statsql = array();
            foreach ($this->searchable as $key => $value) {
                $statsql[] =$DB->sql_like($value, "'%" . $this->search . "%'",$casesensitive = false,$accentsensitive = true, $notlike = false);
            }
            $fields = implode(" OR ", $statsql);          
            $this->sql .= " AND ($fields) ";
        }
    }

    public function filters() {
        if (!empty($this->params['filter_title']) && isset($this->params['filter_title'])) {
            $title = $this->params['filter_title'];
            $this->sql .= " AND le.title IN ('".$title."')";
        }
        if (!empty($this->params['filter_code']) && isset($this->params['filter_code'])) {
            $code = $this->params['filter_code'];
            $this->sql .= " AND le.code IN ('".$code."')";
        }
        if (!empty($this->params['filter_titlearabic']) && isset($this->params['filter_titlearabic'])) {
            $titlearabic = $this->params['filter_titlearabic'];
            $this->sql .= " AND le.titlearabic IN ('".$titlearabic."')";
        }
        if (isset($this->params['filter_type']) && $this->params['filter_type'] > -1) {
            $type = $this->params['filter_type'];
            $this->sql .= " AND le.type = $type";
        }
        if (isset($this->params['filter_method']) && $this->params['filter_method'] > -1) {
            $method = $this->params['filter_method'];
            $this->sql .= " AND le.method IN ('".$method."')";
        }
        if (isset($this->params['filter_status']) && $this->params['filter_status'] > -1) {
            $status = $this->params['filter_status'];
            $this->sql .= " AND le.status IN ('".$status."')";
        }
        if (isset($this->params['filter_certificate']) && $this->params['filter_certificate'] > -1) {
            $certificate = $this->params['filter_certificate'];
            $this->sql .= " AND le.certificate IN ('".$certificate."')";
        }
        if (isset($this->params['filter_language']) && $this->params['filter_language'] > 0) {
            $language = $this->params['filter_language'];
            $this->sql .= " AND FIND_IN_SET(('".$language."'), le.language)";
        }
        if (isset($this->params['filter_audiencegender']) && $this->params['filter_audiencegender'] > 0) {
            $audiencegender = $this->params['filter_audiencegender'];
            $this->sql .= " AND le.audiencegender IN ('".$audiencegender."')";
        }
        if (isset($this->params['filter_description']) && !empty($this->params['filter_description'])) {
            $description = $this->params['filter_description'];
            $this->sql .= " AND le.description IN ('".$description."')";
        }
        if (!empty($this->params['filter_method']) && $this->params['filter_method'] > -1) {
            $this->sql .= " AND le.method IN (:filter_method)";
        }
        if (!empty($this->params['filter_halladdress']) && $this->params['filter_halladdress'] > 0) {
            $halladdress = $this->params['filter_halladdress'];
            $this->sql .= " AND le.halladdress IN ('".$halladdress."')";
        }
        if (!empty($this->params['filter_eventmanager']) && $this->params['filter_eventmanager'] > 0) {
            $eventmanager = $this->params['filter_eventmanager'];
            $this->sql .= " AND FIND_IN_SET(('".$eventmanager."'), le.eventmanager)";
        }

        if (isset($this->params['filter_targetaudience']) && !empty($this->params['filter_targetaudience'])) {
            $this->sql .= " AND le.targetaudience IN (:filter_targetaudience)";
        }

        if (isset($this->params['filter_registrationstart']) && $this->params['filter_registrationstart']['enabled'] == 1 ) {
            $datevalue = $this->params['filter_registrationstart'];
            $date = mktime(0, 0, 0, $datevalue['month'], $datevalue['day'], $datevalue['year']);
            $startdate = mktime(23, 59, 59, $datevalue['month'], $datevalue['day'], $datevalue['year']);            
            if(!empty($date)){
                $this->sql .= " AND le.registrationstart BETWEEN ('".$date."') AND ('".$startdate."') ";
            }
        }
        if (isset($this->params['filter_registrationend']) && $this->params['filter_registrationend']['enabled'] == 1 ) {
            $datevalue = $this->params['filter_registrationend'];
            $date = mktime(0, 0, 0, $datevalue['month'], $datevalue['day'], $datevalue['year']);
            $endate = mktime(23, 59, 59, $datevalue['month'], $datevalue['day'], $datevalue['year']);            
            if(!empty($date)){
                $this->sql .= " AND le.registrationend BETWEEN ('".$date."') AND ('".$endate."') ";
            }
        }
        if (isset($this->params['filter_startdate']) && $this->params['filter_startdate']['enabled'] == 1 ) {
            $datevalue = $this->params['filter_startdate'];
            $date = mktime(0, 0, 0, $datevalue['month'], $datevalue['day'], $datevalue['year']);
            $stdate = mktime(23, 59, 59, $datevalue['month'], $datevalue['day'], $datevalue['year']);            
            if(!empty($date)){
                $this->sql .= " AND le.startdate BETWEEN ('".$date."') AND ('".$stdate."') ";
            }
        }
        if (isset($this->params['filter_enddate']) && $this->params['filter_enddate']['enabled'] == 1 ) {
            $datevalue = $this->params['filter_enddate'];
            $date = mktime(0, 0, 0, $datevalue['month'], $datevalue['day'], $datevalue['year']);
            $enddate = mktime(23, 59, 59, $datevalue['month'], $datevalue['day'], $datevalue['year']);            
            if(!empty($date)){
                $this->sql .= " AND le.enddate BETWEEN ('".$date."') AND ('".$enddate."') ";
            }
        }
        if ($this->ls_startdate > 0 && $this->ls_enddate) {
            $this->params['ls_fstartdate'] = ROUND($this->ls_startdate);
            $this->params['ls_fenddate'] = ROUND($this->ls_enddate);
            $this->sql .= " AND le.enddate BETWEEN :ls_fstartdate AND :ls_fenddate ";
        } 
    }


    public function groupby() {

    }

    public function get_rows($events) {
        return $events;
    }

    public function column_queries($columnname, $eventid, $events = null) { 
        global $DB;
        $where = " AND %placeholder% = $eventid";
        $query = str_replace('%placeholder%', $identy, $query);
        return $query;
    }
}
