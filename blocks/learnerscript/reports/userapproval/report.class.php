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
use block_learnerscript\local\querylib;
use block_learnerscript\local\ls as ls;

defined('MOODLE_INTERNAL') || die();
class report_userapproval extends reportbase implements report {

    public function __construct($report, $reportproperties) {
        global $DB;
        parent::__construct($report, $reportproperties);
        $columns = ['userid', 'fullname','email', 'phone1','user_role'];
        $this->columns = ['userapprovalfields' => ['userapprovalfields'], 'userapprovalcolumns' => $columns];
        $this->components = array('columns', 'conditions', 'ordering', 'filters','permissions', 'plot');
        $this->filters = array('userfilters', 'idnumber');
        $this->parent = true;
        $this->orderable = array('fullname','user_role','email','phone1');

        $this->searchable = array("lc.firstname",'lc.lastname','lc.firstnamearabic','lc.lastnamearabic');;
        $this->defaultcolumn = 'lc.id';
        $this->excludedroles = array("");
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
       $this->sql = "SELECT COUNT(DISTINCT lc.id)";

    }

    public function select() {

        $lang= current_language();
        if($lang == 'ar') {

            $displaying_name = "concat(lc.firstnamearabic,' ',lc.lastnamearabic)";

        } else {
                     
            $displaying_name = "concat(lc.firstname,' ',lc.lastname)";

        }
        
        $this->sql = "SELECT DISTINCT lc.id , lc.userid, $displaying_name AS fullname, lc.* ";
        

        parent::select();
    }

    public function from() {
        $this->sql .= " FROM {local_users} lc";
    }
    public function joins() {
        parent::joins();
    }

    public function where() {
        $this->sql .= " WHERE 1 = 1  AND lc.deleted = 0 AND lc.approvedstatus = 2 ";
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
        $profilefields =  $DB->get_columns('local_users');
        foreach ($profilefields as $key => $value) {
            if (!empty($this->params['filter_'.$key]) && $this->params['filter_'.$key] > -1) {
                $filtervalue = $this->params['filter_'.$key];
                if (is_numeric($filtervalue)){
                    $this->sql .= " AND lc.$key IN ('".$filtervalue."')";
                } else {
                    $this->sql .= " AND lc.$key LIKE ('%".$filtervalue."%')";
                }
            } else {
                // if ($key == 'approvedstatus' || $key == 'id_type' || $key == 'discount') {
                    if ($this->params['filter_'.$key] > -1) {
                        $filtervalue = empty($filtervalue) ? '0' : $filtervalue;
                        // $this->sql .= " AND FIND_IN_SET((".$filtervalue."), lc.$key)";
                    }
                // }
            }
        }
        if ($this->ls_startdate >= 0 && $this->ls_enddate) {
            $this->params['ls_fstartdate'] = ROUND($this->ls_startdate);
            $this->params['ls_fenddate'] = ROUND($this->ls_enddate);
            $this->sql .= " AND lc.timecreated BETWEEN :ls_fstartdate AND :ls_fenddate ";
        }
        if (!empty($this->params['filter_idnumber'])) {
            $this->sql .= " AND lc.id_number = (:filter_idnumber)";
        }
    }


    public function groupby() {

    }

    public function get_rows($user) {
        return $user;
    }

    public function column_queries($columnname, $userid, $user = null) { 
        global $DB;
        $where = " AND %placeholder% = $userid";
            switch ($columnname) {
            case 'user_role':
                $identy = 'lc.id';
                $query = "SELECT role.shortname AS rolename
                            FROM {local_users} as lc 
                            JOIN {role_assignments} as rolu ON rolu.userid = lc.id 
                            JOIN {role} as role ON role.id = rolu.roleid 
                            WHERE 1 = 1 AND lc.deleted = 0 AND rolu.contextid = 1 $where AND lc.approvedstatus = 2";
            break;
            default:
            return false;
            break;
        }
        $query = str_replace('%placeholder%', $identy, $query);
        return $query;
    }
}
