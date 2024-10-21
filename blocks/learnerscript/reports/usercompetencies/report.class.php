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
class report_usercompetencies extends reportbase implements report {

    public function __construct($report, $reportproperties) {
        global $DB;
        parent::__construct($report, $reportproperties);
        $columns = ['status'];
        $this->columns = ['compfields' => ['compfields'], 'usercompcolumns' => $columns];
        $this->components = array('columns', 'conditions', 'ordering', 'filters','permissions', 'plot');
        if (is_siteadmin() || $this->role != 'trainee') {
            $this->basicparams = [['name' => 'users']];
        }
        $this->filters = array('competencyfilters', 'trainingstatus');
        $this->parent = true;
        $this->orderable = array('');

        $lang= current_language();
        if( $lang == 'ar'){
            $this->searchable = array('lc.arabicname');
        } else{
            $this->searchable = array('lc.name');
        }
        
        $this->defaultcolumn = 'lc.id';
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
       $this->sql = "SELECT COUNT(DISTINCT lc.id)";

    }

    public function select() {
        $this->sql = "SELECT DISTINCT lc.id, lc.*, lc.jobroleid AS jobrole";
        parent::select();
    }

    public function from() {
        $this->sql .= " FROM {program_enrollments} pe ";
    }
    public function joins() {
        $this->sql .= " JOIN {local_trainingprogram} tp ON tp.id = pe.programid 
                        JOIN {local_competencies} lc ON FIND_IN_SET(lc.id, tp.competencyandlevels) ";
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
        global $DB;
        $compfields =  $DB->get_columns('local_competencies');
        foreach ($compfields as $key => $value) {
            if (!empty($this->params['filter_'.$key]) && $this->params['filter_'.$key] > -1) {
                if ($key == 'level') {
                    if ($this->params['filter_'.$key] != -1) {
                        $filtervalue = $this->params['filter_'.$key];
                        $this->sql .= " AND FIND_IN_SET(('".$filtervalue."'), lc.$key)";
                    }
                }else{
                    $filtervalue = $this->params['filter_'.$key];
                    $this->sql .= " AND lc.$key IN ('".$filtervalue."')";
                    }
            }
        }
        if ($this->ls_startdate >= 0 && $this->ls_enddate) {
            $this->params['ls_fstartdate'] = ROUND($this->ls_startdate);
            $this->params['ls_fenddate'] = ROUND($this->ls_enddate);
            $this->sql .= " AND lc.timecreated BETWEEN :ls_fstartdate AND :ls_fenddate ";
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
    }


    public function groupby() {
        $this->sql .= " GROUP BY lc.id";

    }

    public function get_rows($competencies) {
        return $competencies;
    }

    public function column_queries($columnname, $competencyid, $competencies = null) { 
        global $DB;
        $where = " AND %placeholder% = $competencyid";
        $userid = isset($this->params['filter_users']) && $this->params['filter_users'] > 0
                    ? $this->params['filter_users'] : $this->userid;
        switch ($columnname) {
            case 'status':
               $identy = 'lcc.competencyid'; 
               $query = "SELECT COUNT(DISTINCT lcc.id) AS status
                            FROM {local_cmtncy_completions} lcc 
                            WHERE 1 = 1 $where AND lcc.userid = $userid";
            break;
            default:
            return false;
                break;
        }
        $query = str_replace('%placeholder%', $identy, $query);
        return $query;
    }
}
