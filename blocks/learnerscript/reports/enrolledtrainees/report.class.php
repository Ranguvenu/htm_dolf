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
class report_enrolledtrainees extends reportbase implements report {

    public function __construct($report, $reportproperties) {
        global $DB;
        parent::__construct($report, $reportproperties);
        $columns = ['identity', 'fullname', 'programcode', 'programname', 'offeringcode', 'organization'];
        $this->columns = ['enrolledtraineescolumns' => $columns];
        $this->components = array('columns', 'conditions', 'ordering', 'filters','permissions', 'plot');
        $this->filters = array('trainingprogram', 'code', 'idnumber', 'purchasedate', 'offeringdate', 'trainingstatus', 'type', 'typeorg', 'hallplace');
        $this->parent = true;
        $this->orderable = array('identity', 'fullname', 'programcode', 'programname', 'offeringcode', 'organization');
        $lang= current_language();
        if( $lang == 'ar'){
            $this->searchable = array('CONCAT(lu.firstnamearabic, " ", lu.lastnamearabic)', 'ltp.namearabic', 'ltp.code', 'tpo.code', 'lo.fullnameinarabic');
        } else{
            $this->searchable = array('CONCAT(lu.firstname, " ", lu.lastname)', 'ltp.name', 'ltp.code', 'tpo.code', 'lo.fullname');
        }
        $this->defaultcolumn = 'CONCAT(u.id,"-",tpo.id,"@", lu.id)';
        $this->excludedroles = array("'manager', 'coursecreator', 'editingteacher', 'teacher', 'student', 'examofficial', 'em', 'co', 'expert', 'trainee', 'competencies_official', 'cpd', 'hall_manager', 'financial_manager'");
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
       $this->sql = "SELECT COUNT(DISTINCT(CONCAT(u.id,'-',tpo.id,'@', lu.id)))";

    }

    public function select() {

        $lang= current_language();
        if( $lang == 'ar'){
            $this->sql = "SELECT DISTINCT(CONCAT(u.id,'-',tpo.id,'@', lu.id)), lu.id_number AS identity, CONCAT(lu.firstnamearabic, ' ', lu.lastnamearabic) AS fullname, ltp.namearabic AS programname, ltp.code AS programcode, tpo.code AS offeringcode,(SELECT lo.fullnameinarabic FROM {local_organization} lo WHERE lo.id = lu.organization ) AS organization ";

        } else{
            $this->sql = "SELECT DISTINCT(CONCAT(u.id,'-',tpo.id,'@', lu.id)), lu.id_number AS identity, CONCAT(lu.firstname, ' ', lu.lastname) AS fullname, ltp.name AS programname, ltp.code AS programcode, tpo.code AS offeringcode, (SELECT lo.fullname FROM {local_organization} lo WHERE lo.id = lu.organization ) AS organization ";
        }

        parent::select();
    }

    public function from() {
        $this->sql .= " FROM {user} u";
    }
    public function joins() {
        global $DB,$USER;
        $this->sql .= " JOIN {local_users} lu ON lu.userid = u.id 
                        JOIN {program_enrollments} pe ON pe.userid = lu.userid 
                        JOIN {role} r ON r.id = pe.roleid AND r.shortname = 'trainee' 
                        JOIN {tp_offerings} tpo ON tpo.id = pe.offeringid 
                        JOIN {local_trainingprogram} ltp ON ltp.id = tpo.trainingid AND ltp.id = pe.programid 
                         ";
        // $systemcontext = context_system::instance();
        // $trainerroleid = $DB->get_field('role', 'id', array('shortname' => 'trainer'));
        // if(is_siteadmin() || has_capability('local/organization:manage_trainingofficial',$systemcontext)){
        //     $this->sql .= " "; 
        // } else if (!is_siteadmin() && has_capability('local/organization:manage_organizationofficial',$systemcontext)) {
        //     $this->sql .= " JOIN {tp_offerings} tpo ON tpo.trainingid = ltp.id
        //                     JOIN {tool_org_order_seats} oos ON oos.fieldid = tpo.id AND oos.tablename = 'tp_offerings' AND oos.orguserid = " . $USER->id;
        // } else if(has_capability('local/organization:manage_trainee', $systemcontext) ||
        //               has_capability('local/organization:manage_trainer', $systemcontext)) {
        //     $this->sql .= " JOIN {program_enrollments} ue ON ue.programid = ltp.id AND  ue.courseid = ltp.courseid AND ue.userid = ".$USER->id." AND ue.roleid = ".$trainerroleid. " AND ltp.published = 1";
        // }
        parent::joins();
    }

    public function where() {
        $this->sql .= " WHERE 1 = 1";
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
        if (!empty($this->params['filter_trainingprogram']) && $this->params['filter_trainingprogram'] > 0) {
            $this->sql .= " AND ltp.id IN (:filter_trainingprogram)";
        }
        if (!empty($this->params['filter_code']) && $this->params['filter_code'] > 0) {
            $this->sql .= " AND tpo.id = (:filter_code)";
        } 
        if (!empty($this->params['filter_idnumber'])) {
            $this->sql .= " AND lu.id_number = (:filter_idnumber)";
        }  
        if ($this->params['date_purchasedate']['enabled'] == 1 && $this->params['date_comingdate']['enabled'] == 1) {
            $day = $this->params['date_purchasedate']['day'];
            $month = $this->params['date_purchasedate']['month'];
            $year = $this->params['date_purchasedate']['year'];

            $cday = $this->params['date_comingdate']['day'];
            $cmonth = $this->params['date_comingdate']['month'];
            $cyear = $this->params['date_comingdate']['year'];

            $purchasedate = mktime(0,0,0, $month, $day, $year);
            $comingdate = mktime(23,59,59, $cmonth, $cday, $cyear);

            $this->sql .= " AND ltp.availablefrom >= $purchasedate AND ltp.availableto <= $comingdate";
        }
        if ($this->params['date_offeringdate']['enabled'] == 1 && $this->params['date_ofcomingdate']['enabled'] == 1) {
            $day = $this->params['date_offeringdate']['day'];
            $month = $this->params['date_offeringdate']['month'];
            $year = $this->params['date_offeringdate']['year'];

            $cday = $this->params['date_ofcomingdate']['day'];
            $cmonth = $this->params['date_ofcomingdate']['month'];
            $cyear = $this->params['date_ofcomingdate']['year'];

            $offeringdate = mktime(0,0,0, $month, $day, $year);
            $ofcomingdate = mktime(23,59,59, $cmonth, $cday, $cyear);

            $this->sql .= " AND tpo.startdate >= $offeringdate AND tpo.enddate <= $ofcomingdate";
        }    
        if ($this->ls_startdate > 0 && $this->ls_enddate) {
            $this->params['ls_fstartdate'] = ROUND($this->ls_startdate);
            $this->params['ls_fenddate'] = ROUND($this->ls_enddate);
            $this->sql .= " AND ltp.availableto BETWEEN :ls_fstartdate AND :ls_fenddate ";
        } 
        if (!empty($this->params['filter_trainingstatus']) && $this->params['filter_trainingstatus'] > 0) {
            if ($this->params['filter_trainingstatus'] == 1) {
                $this->sql .= " AND ltp.published = 1 AND ltp.id IN (SELECT trainingid FROM {tp_offerings} WHERE UNIX_TIMESTAMP() BETWEEN (startdate+time) AND (enddate+endtime)) ";
            } else if ($this->params['filter_trainingstatus'] == 2) {
                $this->sql .= " AND ltp.published = 1 AND ltp.id IN (SELECT trainingid FROM {tp_offerings} WHERE (startdate+time) < UNIX_TIMESTAMP(DATE_ADD(NOW(), INTERVAL +2 DAY))) ";
            } else if ($this->params['filter_trainingstatus'] == 3) {
                $this->sql .= " AND ltp.id IN (SELECT trainingid FROM {tp_offerings} WHERE cancelled = 1) ";
            } else if ($this->params['filter_trainingstatus'] == 4) {
                $this->sql .= " AND ltp.published = 1 AND ltp.id IN (SELECT trainingid FROM {tp_offerings} WHERE (startdate+time) > UNIX_TIMESTAMP()) ";
            }
        }
        if ($this->params['filter_type'] > -1) {
            $this->sql .= " AND tpo.type = (:filter_type)";
        }
        if (!empty($this->params['filter_typeorg']) && $this->params['filter_typeorg'] > 0) {
            $this->sql .= " AND tpo.organization = (:filter_typeorg)";
        } 
        if (!empty($this->params['filter_hallplace'])) {
            if ($this->params['filter_hallplace'] == 'clientheadquarters') {
                $this->sql .= " AND FIND_IN_SET(:filter_hallplace, tpo.halllocation) ";
            } else {
                $this->sql .= " AND tpo.halladdress IN (SELECT id FROM {hall} WHERE halllocation = (:filter_hallplace)) ";
            }
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
