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

defined('MOODLE_INTERNAL') || die();
class report_myprograms extends reportbase implements report {

    public function __construct($report, $reportproperties) {
        global $DB;
        parent::__construct($report, $reportproperties);
        $columns = ['trainingprogram', 'offering', 'startdate', 'enddate', 'time', 'completiondate', 'status'];
        $this->columns = ['myprogramcolumns' => $columns];
        $this->components = array('columns', 'conditions', 'ordering', 'filters','permissions', 'plot');
        if (is_siteadmin() || $this->role != 'trainee') {
            $this->basicparams = [['name' => 'users']];
        }
        $this->filters = array('trainingstatus', 'type', 'typeorg', 'hallplace');
        $this->parent = true;
        $this->orderable = array('');

        $lang= current_language();
        if( $lang == 'ar'){
            $this->searchable = array('ltp.namearabic');
        } else{
            $this->searchable = array('ltp.name');
        }
        $this->defaultcolumn = 'tpo.id';
        $this->excludedroles = array("'manager', 'coursecreator', 'editingteacher', 'teacher', 'student', 'trainer', 'examofficial', 'em', 'co', 'expert', 'organizationofficial', 'to', 'competencies_official', 'cpd', 'hall_manager', 'financial_manager'");
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
        if($this->role != 'trainee' && !isset($this->params['filter_users'])){
            $this->initial_basicparams('users');
            $fusers = array_keys($this->filterdata);
            $this->params['filter_users'] = array_shift($fusers);
        }
    }
    public function count() {
       $this->sql = "SELECT COUNT(DISTINCT tpo.id)";

    }

    public function select() {
        $lang= current_language();
        if( $lang == 'ar'){
            $this->sql = "SELECT DISTINCT tpo.id, ltp.namearabic AS trainingprogram, tpo.code AS offering, tpo.startdate, tpo.enddate, tpo.time, pc.completion_status AS status, pc.completiondate AS completiondate";
        } else {
            $this->sql = "SELECT DISTINCT tpo.id, ltp.name AS trainingprogram, tpo.code AS offering, tpo.startdate, tpo.enddate, tpo.time, pc.completion_status AS status, pc.completiondate AS completiondate";
        }
        parent::select();
    }

    public function from() {
        $this->sql .= " FROM {local_trainingprogram} ltp ";
    }
    public function joins() {
        $this->sql .= " JOIN {tp_offerings} tpo ON tpo.trainingid = ltp.id
                        JOIN {program_enrollments} pe ON pe.programid = ltp.id AND pe.offeringid = tpo.id 
                        LEFT JOIN {program_completions} pc ON pc.userid = pe.userid AND pc.programid = ltp.id AND pc.offeringid = pe.offeringid";
        parent::joins();
    }

    public function where() { 
        $userid = isset($this->params['filter_users']) && $this->params['filter_users'] > 0
                    ? $this->params['filter_users'] : $this->userid;
        $this->params['userid'] = $userid;
        $this->sql .= " WHERE 1 = 1 AND pe.userid = :userid ";
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
        if ($this->ls_startdate >= 0 && $this->ls_enddate) {
            $this->params['ls_fstartdate'] = ROUND($this->ls_startdate);
            $this->params['ls_fenddate'] = ROUND($this->ls_enddate);
            $this->sql .= " AND ltp.availablefrom BETWEEN :ls_fstartdate AND :ls_fenddate ";
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
        $this->sql .= " GROUP BY tpo.id";

    }

    public function get_rows($competencies) {
        return $competencies;
    }

    public function column_queries($columnname, $competencyid, $competencies = null) { 
        // global $DB;
        // $where = " AND %placeholder% = $competencyid";
        // $userid = isset($this->params['filter_users']) && $this->params['filter_users'] > 0
        //             ? $this->params['filter_users'] : $this->userid;
        // switch ($columnname) {
        //     case 'status':
        //        $identy = 'lcc.competencyid'; 
        //        $query = "SELECT COUNT(DISTINCT lcc.id) AS status
        //                     FROM {local_cmtncy_completions} lcc 
        //                     WHERE 1 = 1 $where AND lcc.userid = $userid";
        //     break;
        //     default:
        //     return false;
        //         break;
        // }
        // $query = str_replace('%placeholder%', $identy, $query);
        // return $query;
    }
}
