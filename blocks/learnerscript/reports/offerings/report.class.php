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
 * @date: 2022
 */
namespace block_learnerscript\lsreports;
use block_learnerscript\local\reportbase;
use block_learnerscript\report;
use context_system;

defined('MOODLE_INTERNAL') || die();
class report_offerings extends reportbase implements report {

    public function __construct($report, $reportproperties) {
        global $DB;
        parent::__construct($report, $reportproperties);
        $columns = ['year', 'programnamear', 'programnameen', 'programcode', 'offering', 'starttime', 'endtime', 'enrollments', 'completions', 'vat', 'totalprice', 'durationdays', 'halllocation', 'hallplace', 'trainertype', 'trainer', 'competencylevel', 'sectors', 'languages', 'partner', 'offeringstatus'];
        $this->columns = ['offeringfields' => ['offeringfields'], 'offeringcolumns' => $columns];
        $this->components = array('columns', 'conditions', 'ordering', 'filters','permissions', 'plot');
        $this->filters = array('offeringfilters', 'trainingprogram', 'programcode', 'trainertype', 'trainer', 'creator', 'fullname', 'orgcode', 'offeringdate', 'trainingmethod', 'type', 'clevels', 'sectors', 'languages', 'hallplace', 'city', 'offeringstatus');
        $this->parent = true;
        $this->orderable = array('offering', 'enrollments', 'completions');
        $this->searchable = array('tpo.code');
        //$this->defaultcolumn = 'tpo.id';
        $this->sqlorder['column'] = 'tpo.startdate';
        $this->sqlorder['dir'] = 'DESC';
        $this->excludedroles = array("'manager', 'coursecreator', 'editingteacher', 'teacher', 'student', 'examofficial', 'em', 'expert', 'trainee',  'competencies_official', 'cpd', 'hall_manager', 'trainee_mof', 'mof_testing'");
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
       $this->sql = "SELECT COUNT(DISTINCT tpo.id)";

    }

    public function select() { 
        $lang= current_language();
        if( $lang == 'ar'){
            $this->sql = "SELECT DISTINCT tpo.id , tpo.code AS offering, tpo.*, tpo.trainingid AS programid, tp.namearabic AS programnamear, tp.name AS programnameen, tp.code AS programcode, tp.clevels AS competencylevel, tp.sectors, tpo.startdate AS stdate, tpo.enddate AS endate, tp.tax_free, tpo.halllocation AS hallplace, tpo.sellingprice, tpo.halllocation AS location, tpo.time AS starttime, tpo.endtime AS endtime, tpo.cancelled, tpo.financially_closed_status, tpo.trainertype ";
        } else{
            $this->sql = "SELECT DISTINCT tpo.id , tpo.code AS offering, tpo.*, tpo.trainingid AS programid, tp.namearabic AS programnamear, tp.name AS programnameen, tp.code AS programcode, tp.clevels AS competencylevel, tp.sectors, tpo.startdate AS stdate, tpo.enddate AS endate, tp.tax_free, tpo.halllocation AS hallplace, tpo.sellingprice, tpo.halllocation AS location, tpo.time AS starttime, tpo.endtime AS endtime, tpo.cancelled, tpo.financially_closed_status, tpo.trainertype ";
        }
        parent::select();
    }

    public function from() {
        $this->sql .= " FROM {tp_offerings} tpo 
                        JOIN {local_trainingprogram} tp ON tp.id = tpo.trainingid";

        if (!empty($this->params['filter_trainer']) || $this->params['filter_trainingprovider'] > -1) {           
            $this->sql .= " JOIN {program_enrollments} pe ON pe.programid = tp.id AND pe.offeringid = tpo.id 
                            JOIN {role} r ON r.id = pe.roleid AND r.shortname = 'trainer'";
        }
    }
    public function joins() {
        global $DB, $USER;
        $systemcontext = context_system::instance();
        $trainerroleid = $DB->get_field('role', 'id', array('shortname' => 'trainer'));
        if(is_siteadmin() || has_capability('local/organization:manage_trainingofficial',$systemcontext)){
            $this->sql .= " "; 
        } else if (!is_siteadmin() && has_capability('local/organization:manage_organizationofficial',$systemcontext)) {
            $this->sql .= " JOIN {tool_org_order_seats} oos ON oos.fieldid = tpo.id AND oos.tablename = 'tp_offerings' AND oos.orguserid = " . $USER->id;
        } else if(has_capability('local/organization:manage_trainee', $systemcontext) ||
                      has_capability('local/organization:manage_trainer', $systemcontext)) {
            $this->sql .= " JOIN {local_trainingprogram} ltp ON ltp.id = tpo.trainingid AND ltp.published = 1
                            JOIN {program_enrollments} ue ON ue.programid = tpo.trainingid AND ue.userid = ".$USER->id." AND ue.roleid = ".$trainerroleid. " AND tpo.id = ue.offeringid";
        }
        parent::joins();
    }

    public function where() {
        $this->sql .= " WHERE 1 = 1";
        $status = isset($this->params['filter_status']) ? $this->params['filter_status'] : '';
        if ($status == 'active') {
            $this->sql .= " AND (tpo.enddate+tpo.endtime) > UNIX_TIMESTAMP() "; 
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
        global $DB;
        $offeringfields =  $DB->get_columns('tp_offerings');
        foreach ($offeringfields as $key => $value) {
            if (!empty($this->params['filter_'.$key]) && $this->params['filter_'.$key] > -1) {
                $filtervalue = $this->params['filter_'.$key];
                if ($key == 'startdate' || $key == 'enddate') {
                    if(isset($this->params['filter_'.$key]) && $this->params['filter_'.$key]['enabled'] == 1){
                        $datevalue = $this->params['filter_'.$key] ;                            
                        $date = mktime(0, 0, 0, $datevalue['month'], $datevalue['day'], $datevalue['year']);
                        $endate = mktime(23, 59, 59, $datevalue['month'], $datevalue['day'], $datevalue['year']);
                        if(!empty($date)){
                            $datav = " AND tpo.$key BETWEEN $date AND $endate ";
                            $this->sql .= $datav; 
                        }
                                          
                    }
                } else if (is_numeric($filtervalue)) {
                    $this->sql .= " AND tpo.$key IN ('".$filtervalue."')";
                } else {
                    $this->sql .= " AND tpo.$key LIKE ('%".$filtervalue."%')";
                }
            } else {
                if ($key == 'type') {
                    if ($this->params['filter_'.$key] > -1) {
                        $filtervalue = empty($filtervalue) ? '0' : $filtervalue;
                        $this->sql .= " AND FIND_IN_SET((".$filtervalue."), tpo.$key)";
                    }
                }
            }
        }
        if (!empty($this->params['filter_trainingprogram']) && $this->params['filter_trainingprogram'] > 0) {           
            $this->sql .= " AND tpo.trainingid IN (:filter_trainingprogram) ";
        }
        if ($this->ls_startdate > 0 && $this->ls_enddate) {
            $d1 = strtotime(date('Y-m-d', $this->ls_startdate));
            $d2 = strtotime(date('Y-m-d', $this->ls_enddate));
            $this->ls_startdate = $d1;
            $this->ls_enddate = $d2;
            $this->params['ls_fstartdate'] = ROUND($this->ls_startdate);
            $this->params['ls_fenddate'] = ROUND($this->ls_enddate);
            $this->sql .= " AND ROUND(UNIX_TIMESTAMP(FROM_UNIXTIME(tpo.startdate, '%Y-%m-%d')),0) BETWEEN :ls_fstartdate AND :ls_fenddate ";
        }
        if (!empty($this->params['filter_programcode'])) {
            $this->sql .= " AND tp.code IN (:filter_programcode) ";
        }
        if (!empty($this->params['filter_trainer']) && $this->params['filter_trainer'] > 0) {
            $this->sql .= " AND pe.userid IN (:filter_trainer) ";
        }
        // if ($this->params['filter_trainingprovider'] > -1) {
        //     $this->sql .= " AND pe.trainertype = (:filter_trainingprovider) ";
        // }
        if ($this->params['filter_trainertype'] > -1) {
            $this->sql .= " AND tpo.trainertype = (:filter_trainertype) ";
        }
        if (!empty($this->params['filter_creator']) && $this->params['filter_creator'] > 0) {
            $this->sql .= " AND tpo.usercreated IN (:filter_creator)";
        }
        if (!empty($this->params['filter_fullname']) && $this->params['filter_fullname'] > 0) {
            $this->sql .= " AND tpo.organization IN (:filter_fullname)";
        }
        if (!empty($this->params['filter_typeorg']) && $this->params['filter_typeorg'] > 0) {
            $this->sql .= " AND tpo.organization IN (:filter_typeorg)";
        }
        if ($this->params['date_offeringdate']['enabled'] == 1 && $this->params['date_ofcomingdate']['enabled'] == 1) {
            $estday = $this->params['date_offeringdate']['day'];
            $estmonth = $this->params['date_offeringdate']['month'];
            $estyear = $this->params['date_offeringdate']['year'];

            $eenday = $this->params['date_ofcomingdate']['day'];
            $eenmonth = $this->params['date_ofcomingdate']['month'];
            $eenyear = $this->params['date_ofcomingdate']['year'];

            $eststartdate = mktime(0,0,0, $estmonth, $estday, $estyear);
            $eenenddate = mktime(23,59,59, $eenmonth, $eenday, $eenyear);
            $this->sql .= " AND tpo.startdate BETWEEN $eststartdate AND $eenenddate";
        }
        if (!empty($this->params['filter_trainingmethod'])) {
            $this->sql .= " AND tpo.trainingmethod LIKE (:filter_trainingmethod) ";
        }
        if ($this->params['filter_type'] > -1) {
            $this->sql .= " AND tpo.type IN (:filter_type) ";
        }
        if (!empty($this->params['filter_clevels'])) {
            $this->sql .= " AND tp.clevels LIKE (:filter_clevels) ";
        }
        if (!empty($this->params['filter_sectors'])) {
            $this->sql .= " AND FIND_IN_SET(:filter_sectors, tp.sectors) ";
        }
        if ($this->params['filter_languages'] > -1) {
            $this->sql .= " AND FIND_IN_SET(:filter_languages, tp.languages) ";
        }
        if (!empty($this->params['filter_hallplace'])) {
            if ($this->params['filter_hallplace'] == 'clientheadquarters') {
                $this->sql .= " AND FIND_IN_SET(:filter_hallplace, tpo.halllocation) ";
            } else {
                $this->sql .= " AND tpo.halladdress IN (SELECT id FROM {hall} WHERE halllocation = (:filter_hallplace)) ";
            }
        }
        if (!empty($this->params['filter_city'])) {
            $this->sql .= " AND tpo.halladdress IN (SELECT id FROM {hall} WHERE city = (:filter_city)) ";
        }
        if (!empty($this->params['filter_trainingstatus']) && $this->params['filter_trainingstatus'] > 0) {
            if ($this->params['filter_trainingstatus'] == 1) {
                $this->sql .= " AND tp.published = 1 AND tp.id IN (SELECT trainingid FROM {tp_offerings} WHERE UNIX_TIMESTAMP() BETWEEN (startdate+time) AND (enddate+endtime)) ";
            } else if ($this->params['filter_trainingstatus'] == 2) {
                $this->sql .= " AND tp.published = 1 AND tp.id IN (SELECT trainingid FROM {tp_offerings} WHERE (startdate+time) < UNIX_TIMESTAMP(DATE_ADD(NOW(), INTERVAL +2 DAY))) ";
            } else if ($this->params['filter_trainingstatus'] == 3) {
                $this->sql .= " AND tp.id IN (SELECT trainingid FROM {tp_offerings} WHERE cancelled = 1) ";
            } else if ($this->params['filter_trainingstatus'] == 4) {
                $this->sql .= " AND tp.published = 1 AND tp.id IN (SELECT trainingid FROM {tp_offerings} WHERE (startdate+time) > UNIX_TIMESTAMP()) ";
            }
        }
        if (!empty($this->params['filter_offeringstatus']) && $this->params['filter_offeringstatus'] > 0) {
            if ($this->params['filter_offeringstatus'] == 1) {
                $this->sql .= " AND tpo.financially_closed_status != 1 AND tpo.cancelled != 2 AND (tpo.enddate >= UNIX_TIMESTAMP()) ";
            } else if ($this->params['filter_offeringstatus'] == 2) {
                $this->sql .= " AND tpo.financially_closed_status = 1 ";
            } else if ($this->params['filter_offeringstatus'] == 3) {
                $this->sql .= " AND tpo.cancelled = 2 ";
            } else if ($this->params['filter_offeringstatus'] == 4) {
                $this->sql .= " AND tpo.enddate < UNIX_TIMESTAMP() ";
            }
        }
    }


    public function groupby() {

    }

    public function get_rows($offerings) {
        return $offerings;
    }

    public function column_queries($columnname, $offeringid, $exams = null) { 
        global $DB, $USER; 
        $systemcontext = context_system::instance();
        $orgid = $DB->get_field('local_users', 'organization', ['userid' => $USER->id]);
        $where = " AND %placeholder% = $offeringid";
        switch ($columnname) {
            case 'enrollments':
                if (!is_siteadmin() && has_capability('local/organization:manage_organizationofficial',$systemcontext)) {
                    $orgcondition = " AND lu.organization = $orgid ";
                }
                $identy = 'pe.offeringid';
                $query = "SELECT COUNT(DISTINCT pe.id) AS enrollments
                            FROM {program_enrollments} pe 
                            JOIN {role} r ON r.id = pe.roleid AND r.shortname = 'trainee' 
                            JOIN {local_users} lu ON lu.userid = pe.userid 
                            WHERE 1 = 1 $orgcondition $where AND lu.deleted = 0 AND lu.approvedstatus = 2 AND pe.enrolstatus = 1 ";
            break;
            case 'completions':
                $orgsql = " ";
                if (!is_siteadmin()) {
                    $orgsql .= " AND lu.organization = $orgid ";
                } else {
                    $orgsql .= " ";
                }
                $identy = 'pc.offeringid';
                $query = "SELECT COUNT(DISTINCT pc.id) AS completions 
                            FROM {program_completions} pc 
                            JOIN {program_enrollments} pe ON pe.userid = pc.userid AND pe.programid = pc.programid
                            JOIN {local_users} as lu on lu.userid = pc.userid
                            WHERE 1 = 1 AND pc.completiondate != 0 $orgsql $where AND lu.deleted = 0 AND lu.approvedstatus = 2 AND pe.enrolstatus = 1";
            break;
            default:
            return false;
                break;
        }
        $query = str_replace('%placeholder%', $identy, $query);
        return $query;
    }
}
