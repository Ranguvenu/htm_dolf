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
 * @author: Jahnavi Nanduri
 * @date: 2023
 */
namespace block_learnerscript\lsreports;
use block_learnerscript\local\reportbase;
use block_learnerscript\report;
use block_learnerscript\local\querylib;
use block_learnerscript\local\ls as ls;
use context_system;

defined('MOODLE_INTERNAL') || die();
class report_eventenrol extends reportbase implements report {

    public function __construct($report, $reportproperties) {
        global $DB;
        parent::__construct($report, $reportproperties);
        $columns = ['participant', 'identity', 'email', 'eventname', 'hascertificate', 'eventdate', 'eventenddate', 'enrollmentdate', 'enrolledby', 'completiondate', 'certificatecode'];
        $this->columns = ['eventenrolcolumns' => $columns];
        $this->components = array('columns', 'conditions', 'ordering', 'filters','permissions', 'plot');
        $this->filters = array('idnumber', 'title', 'startdate', 'enrollmentdate');
        $this->parent = true;
        $this->orderable = array('participant', 'identity', 'eventname', 'hascertificate', 'eventdate', 'enrollmentdate', 'completiondate', 'certificatecode');
        $lang= current_language();
        if( $lang == 'ar'){
            $this->searchable = array('CONCAT(lu.firstnamearabic, " ", lu.lastnamearabic)', 'le.titlearabic', 'lu.id_number');
        } else{
            $this->searchable = array('CONCAT(lu.firstname, " ", lu.lastname)', 'le.title', 'lu.id_number');
        }
        $this->defaultcolumn = 'lea.id';
        $this->excludedroles = array("'manager', 'coursecreator', 'editingteacher', 'teacher', 'student', 'examofficial', 'co', 'expert', 'trainee', 'competencies_official', 'cpd', 'hall_manager', 'financial_manager', 'trainer', 'to'");
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
       $this->sql = "SELECT COUNT(DISTINCT lea.id)";

    }

    public function select() {

        $lang= current_language();
        if( $lang == 'ar'){
            $this->sql = "SELECT DISTINCT lea.id, u.id AS userid, lu.id_number AS identity, CONCAT(lu.firstnamearabic, ' ', lu.lastnamearabic) AS participant, le.titlearabic AS eventname, le.startdate AS eventdate, lea.timecreated AS enrollmentdate, tci.timecreated AS completiondate, lea.usercreated AS enrolledby, tci.id AS certificateid, tci.code AS certificatecode, lu.email, le.slot, le.enddate, le.eventduration ";

        } else{
            $this->sql = "SELECT DISTINCT lea.id, u.id AS userid, lu.id_number AS identity, CONCAT(lu.firstname, ' ', lu.lastname) AS participant, le.title AS eventname, le.startdate AS eventdate, lea.timecreated AS enrollmentdate, tci.timecreated AS completiondate, lea.usercreated AS enrolledby, tci.id AS certificateid, tci.code AS certificatecode, lu.email, le.slot, le.enddate, le.eventduration ";
        }

        parent::select();
    }

    public function from() {
        $this->sql .= " FROM {user} u";
    }
    public function joins() {
        global $DB,$USER;
        $this->sql .= " JOIN {local_users} lu ON lu.userid = u.id 
                        JOIN {local_event_attendees} lea ON lea.userid = lu.userid 
                        JOIN {local_events} le ON le.id = lea.eventid 
                        LEFT JOIN {tool_certificate_issues} tci ON tci.moduleid = le.id AND tci.moduletype = 'events' AND tci.userid = lu.userid";
        parent::joins();
    }

    public function where() {
       global $DB,$USER;
        $this->sql .= " WHERE 1 = 1 AND lu.deleted = 0 AND lu.approvedstatus = 2 AND lea.enrolstatus = 1 ";
        $systemcontext = context_system::instance();
        if(is_siteadmin()){
            $this->sql .= " "; 
        } else if (!is_siteadmin() && has_capability('local/organization:manage_organizationofficial',$systemcontext)) {
            $organization = $DB->get_field('local_users','organization',array('userid'=>$USER->id));
            $this->sql .= " AND lu.organization = $organization";
        }
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
        if (!empty($_SESSION['filter_userid']) && $_SESSION['filter_userid'] > 0) {
            $this->sql .= " AND lu.userid = " . $_SESSION['filter_userid'];
        }
        if (!empty($_SESSION['filter_orguser']) && $_SESSION['filter_orguser'] > 0) {
            $this->sql .= " AND lea.usercreated = " . $_SESSION['filter_orguser'];
        }
        if (!empty($_SESSION['filter_referenceid']) && $_SESSION['filter_referenceid'] > 0) {
            $this->sql .= " AND le.id = " . $_SESSION['filter_referenceid'];
        }
        if (!empty($this->params['filter_title'])) {
            $lang= current_language();
            if( $lang == 'ar'){
                $this->sql .= " AND le.titlearabic IN (:filter_title)";
            } else {
                $this->sql .= " AND le.title IN (:filter_title)";
            }
        }
        if (!empty($this->params['filter_idnumber'])) {
            $this->sql .= " AND lu.id_number = (:filter_idnumber)";
        }  
        if ($this->params['date_examfromtime']['enabled'] == 1 && $this->params['date_examtotime']['enabled'] == 1) {
            $estday = $this->params['date_examfromtime']['day'];
            $estmonth = $this->params['date_examfromtime']['month'];
            $estyear = $this->params['date_examfromtime']['year'];

            $eenday = $this->params['date_examtotime']['day'];
            $eenmonth = $this->params['date_examtotime']['month'];
            $eenyear = $this->params['date_examtotime']['year'];

            $eststartdate = mktime(0,0,0, $estmonth, $estday, $estyear);
            $eenenddate = mktime(23,59,59, $eenmonth, $eenday, $eenyear);
            $this->sql .= " AND le.startdate BETWEEN $eststartdate AND $eenenddate";
        }
        if ($this->params['date_fromtime']['enabled'] == 1 && $this->params['date_totime']['enabled'] == 1) {
            $stday = $this->params['date_fromtime']['day'];
            $stmonth = $this->params['date_fromtime']['month'];
            $styear = $this->params['date_fromtime']['year'];

            $enday = $this->params['date_totime']['day'];
            $enmonth = $this->params['date_totime']['month'];
            $enyear = $this->params['date_totime']['year'];

            $ststartdate = mktime(0,0,0, $stmonth, $stday, $styear);
            $enenddate = mktime(23,59,59, $enmonth, $enday, $enyear);
            $this->sql .= " AND lea.timecreated BETWEEN $ststartdate AND $enenddate";
        }    
        if ($this->ls_startdate > 0 && $this->ls_enddate) {
            $this->params['ls_fstartdate'] = ROUND($this->ls_startdate);
            $this->params['ls_fenddate'] = ROUND($this->ls_enddate);
            $this->sql .= " AND le.timecreated BETWEEN :ls_fstartdate AND :ls_fenddate ";
        }   
    }


    public function groupby() {

    }

    public function get_rows($trainingprograms) {
        return $trainingprograms;
    }

    public function column_queries($columnname, $trainingprogramid, $trainingprograms = null) { 
        
    }
}
