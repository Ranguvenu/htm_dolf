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
 * @author: Jahnavi Nanduri
 * @date: 2022
 */
namespace block_learnerscript\lsreports;
use block_learnerscript\local\pluginbase;
use block_learnerscript\local\reportbase;
use context_system;
use html_writer;

class plugin_learningtrackcolumns extends pluginbase {

    public function init() {
        $this->fullname = get_string('learningtrackcolumns', 'block_learnerscript');
        $this->type = 'undefined';
        $this->form = true;
        $this->reporttypes = array('learningtrackinfo');
    }

    public function summary($data) {
        return format_string($data->columname);
    }

    public function colformat($data) {
        $align = (isset($data->align)) ? $data->align : '';
        $size = (isset($data->size)) ? $data->size : '';
        $wrap = (isset($data->wrap)) ? $data->wrap : '';
        return array($align, $size, $wrap);
    }

    public function execute($data, $row, $user, $courseid, $starttime = 0, $endtime = 0) {
        global $DB, $USER, $CFG;
        $context = context_system::instance();
        switch ($data->column) {
            case 'assignedexams':
                $examsreportID = $DB->get_field('block_learnerscript', 'id', array('type' => 'exams'), IGNORE_MULTIPLE);
                $examnames=$DB->get_fieldset_sql("SELECT DISTINCT le.itemid FROM {local_learning_items} as le 
                     where le.trackid   =".$row->id." AND le.itemtype=2");
                if(!empty($examnames)){
                    $examnames = COUNT($examnames);
                }
                $examscount = !empty($examnames) ? $examnames : '0'; 
                $enrolcheckpermissions = empty($examsreportID) ? false : (new reportbase($examsreportID))->check_permissions($USER->id, $context);
                if(empty($examsreportID) || empty($enrolcheckpermissions)){
                    $row->{$data->column} =  $examscount ;
                } else{
                    $row->{$data->column} = html_writer::link("$CFG->wwwroot/blocks/learnerscript/viewreport.php?id=$examsreportID&filter_learningtrack=$row->id", $examscount, array("target" => "_blank"));
                }
               
            break;
            case 'assignedtrainingprograms':
                $programsreportID = $DB->get_field('block_learnerscript', 'id', array('type' => 'trainingprograms', 'name' => 'Training programs'), IGNORE_MULTIPLE);
                $tpames=$DB->get_fieldset_sql("SELECT DISTINCT le.itemid FROM {local_learning_items} as le 
                     where le.trackid   =".$row->id." AND le.itemtype=1");
                if(!empty($tpames)){
                    $tpames = COUNT($tpames);
                }
                $programscount = !empty($tpames) ? $tpames : '0'; 
                $enrolcheckpermissions = empty($programsreportID) ? false : (new reportbase($programsreportID))->check_permissions($USER->id, $context);
                if(empty($programsreportID) || empty($enrolcheckpermissions)){
                    $row->{$data->column} =  $programscount ;
                } else{
                    $row->{$data->column} = html_writer::link("$CFG->wwwroot/blocks/learnerscript/viewreport.php?id=$programsreportID&filter_learningtrack=$row->id", $programscount, array("target" => "_blank"));
                }
               
            break;
            case 'userscount':
                $context = context_system::instance();
                $countsql = "SELECT COUNT(u.id) ";
                $sql      = " FROM {user} u
                 JOIN {local_lts_enrolment} le ON le.userid = u.id 
                 JOIN {local_users} lu ON le.userid = lu.userid";
                if(!is_siteadmin() && has_capability('local/organization:manage_organizationofficial',$context)) {
                    $organization = $DB->get_field('local_users','organization',array('userid'=>$USER->id));
                    $sql.= " AND lu.organization = $organization";
                } 
                $sql .= "  WHERE le.trackid = :trackid AND u.confirmed = 1 AND u.suspended = 0 AND u.deleted = 0 AND u.id > 2 AND lu.deleted = 0 AND lu.approvedstatus = 2 ";
                $params['trackid'] = $row->id;
                $userscount = $DB->count_records_sql($countsql . $sql, $params);
                if (!empty($userscount)) { 
                    $userscount = html_writer::link("$CFG->wwwroot/local/learningtracks/enrollment.php?trackid=$row->id", $userscount, array("target" => "_blank"));
                } else {
                    $userscount = 0;
                }
                $row->{$data->column} = $userscount; 
            break;
            
        }
        return (isset($row->{$data->column}))? $row->{$data->column} : ' -- ';
    }
}
