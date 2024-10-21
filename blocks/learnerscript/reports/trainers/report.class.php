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
class report_trainers extends reportbase implements report {

    public function __construct($report, $reportproperties) {
        global $DB;
        parent::__construct($report, $reportproperties);
        $columns = ['userid', 'fullname','email','phone1', 'idnumber','user_role','fieldoftraining','programname'];
        $this->columns = ['userapprovalcolumns' => $columns];
        $this->components = array('columns', 'conditions', 'ordering', 'filters','permissions', 'plot');
        $this->filters = array('orgorind','idnumber','programs','fieldoftraining','trainingprogram');
        //$this->filters = array('userfilters');
        $this->parent = true;
        $this->orderable = array('fullname','user_role','email','phone1');
        $this->searchable = array("concat(lc.firstname,' ',lc.lastname)");
        $this->defaultcolumn = 'lc.userid';
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
       $this->sql = "SELECT COUNT(DISTINCT lc.userid)";

    }

    public function select() {

        $lang= current_language();
        if($lang == 'ar') {

            $displaying_name = "concat(lc.firstnamearabic,' ',lc.lastnamearabic)";

        } else {
                     
            $displaying_name = "concat(lc.firstname,' ',lc.lastname)";

        }
        
           $this->sql = "SELECT DISTINCT lc.userid, $displaying_name AS fullname,lc.email as email,lc.phone1 as phone1, r.shortname AS user_role,lc.id_number AS idnumber,CASE r.shortname
            WHEN 'trainer' THEN ltr.fieldoftraining
            ELSE '--'
            END AS 'fieldoftraining', lt.name AS programname";
        

        parent::select();
    }

    public function from() {
        $this->sql .= " FROM {local_users} lc 
                        ";
    }
    public function joins() {
        $this->sql .= " JOIN {role_assignments} ra ON ra.userid = lc.userid
                        JOIN {role} r ON r.id = ra.roleid
                        JOIN {local_users} lcc ON lcc.userid = lc.userid
                        LEFT JOIN {local_trainer_request} ltr ON ltr.userid = lc.userid AND ltr.status=2
                        LEFT JOIN {program_enrollments} pe ON pe.userid = lc.userid
                        LEFT JOIN {local_trainingprogram} lt ON lt.id = pe.programid";
        parent::joins();
    }

    public function where() {
        $this->sql .= " WHERE 1 = 1  AND lc.deleted = 0 AND lc.approvedstatus = 2 AND r.shortname IN('trainer')";
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
        if(isset($this->params['filter_orgorind']) && $this->params['filter_orgorind']>0){
            $orgvalue = $this->params['filter_orgorind'];
            if($orgvalue == 1 ){
             $this->sql .= " AND lc.organization = 0 ";   
            }else{
                $this->sql .= " AND lc.organization != 0 ";   
            }
        }
        if(isset($this->params['filter_fieldoftraining']) && !empty($this->params['filter_fieldoftraining'])){

            $fieldoftraining = $this->params['filter_fieldoftraining'];
           $this->sql .= " AND ltr.fieldoftraining LIKE '".$fieldoftraining."'";
        }
        if(isset($this->params['filter_idnumber']) && !empty($this->params['filter_idnumber'])){
            $idnumber = $this->params['filter_idnumber'];
               $this->sql .= " AND lc.id_number = '".$idnumber."'";
        }
        if(isset($this->params['filter_trainingprogram']) && $this->params['filter_trainingprogram']>0){
            $trainingprogram = $this->params['filter_trainingprogram'];
               $this->sql .= " AND lt.id = $trainingprogram";
        }
        if ($this->ls_startdate >= 0 && $this->ls_enddate) {
            $this->params['ls_fstartdate'] = ROUND($this->ls_startdate);
            $this->params['ls_fenddate'] = ROUND($this->ls_enddate);
            $this->sql .= " AND lc.timecreated BETWEEN :ls_fstartdate AND :ls_fenddate ";
        }
    }


    public function groupby() {
        $this->sql .=" GROUP BY lc.userid";
    }

    public function get_rows($user) {
        
        return $user;
    }
}
