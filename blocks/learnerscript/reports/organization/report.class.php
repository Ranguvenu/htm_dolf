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
use context_system;

defined('MOODLE_INTERNAL') || die();
class report_organization extends reportbase implements report {

    public function __construct($report, $reportproperties) {
        global $DB;
        parent::__construct($report, $reportproperties);
        $columns = ['organizationname','enrollments', 'organization_official_enrollments'];
        $this->columns = ['organizationfields' => ['organizationfields'], 'organizationcolumns' => $columns];
        $this->components = array('columns', 'conditions', 'ordering', 'filters','permissions', 'plot');
        $this->filters = array('orgfilters');
        $this->parent = true;
        $this->orderable = array('organizationname','enrollments', 'organization_official_enrollments');

        $this->searchable = array('le.fullname','le.shortname');;
        $this->defaultcolumn = 'le.id';
        $this->excludedroles = array("'manager', 'coursecreator', 'editingteacher', 'teacher', 'student', 'trainer', 'examofficial', 'em', 'expert', 'trainee', 'to', 'competencies_official', 'cpd', 'hall_manager', 'financial_manager', 'organizationofficial'");
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
        if( $lang == 'ar'){
            $this->sql = "SELECT DISTINCT le.id , le.fullnameinarabic AS organizationname, le.* ";
        } else{
            $this->sql = "SELECT DISTINCT le.id , le.fullname AS organizationname, le.* ";
        }

        parent::select();
    }

    public function from() {
        $this->sql .= " FROM {local_organization} le";
    }
    public function joins() {
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
        global $DB;

        $orgfieldfilters =  $DB->get_columns('local_organization');
        foreach($orgfieldfilters AS $v=>$k){
            if(isset($this->params['filter_'.$v]) && (!empty($this->params['filter_'.$v]) || $this->params['filter_'.$v] > 0)){
                $filtervalue = $this->params['filter_'.$v];
                if($v == 'description' || $v == 'program_agenda'){
                    $this->sql .= " AND le.$v LIKE ('%".$filtervalue."%')";
                }else{
                    $this->sql .= " AND le.$v IN ('".$filtervalue."')";
                }
            }
        }


         if ($this->ls_startdate >= 0 && $this->ls_enddate) {
            $this->params['ls_fstartdate'] = ROUND($this->ls_startdate);
            $this->params['ls_fenddate'] = ROUND($this->ls_enddate);
            $this->sql .= " AND le.timecreated BETWEEN :ls_fstartdate AND :ls_fenddate ";
        }
    }


    public function groupby() {

    }

    public function get_rows($organization) {
        return $organization;
    }

    public function column_queries($columnname, $organizationid, $organization = null) { 
        global $DB;
        $systemcontext = context_system::instance();
        $where = " AND %placeholder% = $organizationid";
        $roleid = $DB->get_field_sql("SELECT id FROM {role} WHERE shortname = 'trainee'");
        $org_official_roleid = $DB->get_field_sql("SELECT id FROM {role} WHERE shortname = 'organizationofficial'");
            switch ($columnname) {
            case 'enrollments':
                $identy = 'lu.organization';
                $query = "SELECT COUNT(DISTINCT(lu.id)) FROM {local_users} AS lu JOIN {role_assignments} AS roa ON roa.userid = lu.userid  WHERE lu.approvedstatus = 2 AND lu.deleted = 0 AND roa.contextid = $systemcontext->id AND roleid = $roleid $where";
            break;
            case 'organization_official_enrollments':
                $identy = 'lu.organization';
                $query = "SELECT COUNT(DISTINCT(lu.id)) FROM {local_users} AS lu JOIN {role_assignments} AS roa ON roa.userid = lu.userid  WHERE lu.approvedstatus = 2 AND lu.deleted = 0 AND roa.contextid = $systemcontext->id AND roleid = $org_official_roleid $where ";
            break;
            default:
            return false;
            break;
        }
        $query = str_replace('%placeholder%', $identy, $query);
        return $query;
    }
}
