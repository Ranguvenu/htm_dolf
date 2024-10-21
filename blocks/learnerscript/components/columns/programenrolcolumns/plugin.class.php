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
 * @date: 2023
 */
namespace block_learnerscript\lsreports;
use block_learnerscript\local\pluginbase;
use html_writer;
use context_system;

class plugin_programenrolcolumns extends pluginbase {

    public function init() {
        $this->fullname = get_string('programenrolcolumns', 'block_learnerscript');
        $this->type = 'undefined';
        $this->form = true;
        $this->reporttypes = array('programenrol');
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
        global $DB, $CFG;
        $systemcontext = context_system::instance();
        $lang= current_language();
        switch ($data->column) {
            case 'startdate':
                if (!empty($row->{$data->column})) {
                    $examdate = userdate($row->{$data->column},get_string('strftimedatemonthabbr', 'core_langconfig'));
                    $examtime = gmdate('h:i', $row->time);
                    $examdatemeridian = gmdate('a', $row->time);

                    if( $lang == 'ar'){
                        $examdatemeridian = ($examdatemeridian == 'am')? 'ص':' م';

                    }else{
                        $examdatemeridian = ($examdatemeridian == 'am')? 'AM':'PM';
                    }
                    $row->{$data->column} = !empty($examdate) ? $examdate . ' ' . $examtime . ' ' . $examdatemeridian : '--';
                } else {
                    $row->{$data->column} = '--';
                }
            break;
            case 'enddate':
                if (!empty($row->{$data->column})) {
                    $examdate = userdate($row->{$data->column},get_string('strftimedatemonthabbr', 'core_langconfig'));
                    $examtime = gmdate('h:i', $row->time + $row->duration);
                    $examdatemeridian = gmdate('a', $row->time + $row->duration);

                    if( $lang == 'ar'){
                        $examdatemeridian = ($examdatemeridian == 'am')? 'ص':' م';

                    }else{
                        $examdatemeridian = ($examdatemeridian == 'am')? 'AM':'PM';
                    }
                    $row->{$data->column} = !empty($examdate) ? $examdate . ' ' . $examtime . ' ' . $examdatemeridian : '--';
                } else {
                    $row->{$data->column} = '--';
                }
            break;
            case 'enrollmentdate':
            case 'completiondate':
                $row->{$data->column} = !empty($row->{$data->column}) ? userdate($row->{$data->column},get_string('strftimedatemonthabbr', 'core_langconfig')) : '--';
            break;
            case 'enrolledby':
                $roleinfo = $DB->get_field_sql('SELECT rol.shortname FROM {role} rol 
                                        JOIN {role_assignments} rola ON rola.roleid = rol.id
                                        WHERE rola.userid =:userid and contextid =:contextid',['userid'=>$row->enrolledby,'contextid'=>$systemcontext->id]);
                if (!empty($roleinfo)) {
                    if ($roleinfo == 'organizationofficial' || $roleinfo == 'to') {
                        
                        if($lang == 'ar'){
                            $ooname = $DB->get_field_sql("SELECT CONCAT(lu.firstnamearabic, ' ', lu.lastnamearabic) FROM {local_users} lu WHERE lu.userid = $row->enrolledby");
                        } else {
                            $ooname = $DB->get_field_sql("SELECT CONCAT(u.firstname, ' ', u.lastname) FROM mdl_user u WHERE u.id = $row->enrolledby");
                        }
                        $row->{$data->column} = html_writer::link("$CFG->wwwroot/local/userapproval/userprofile.php?id=$row->enrolledby", $ooname, array("target" => "_blank"));
                    } else {
                        $row->{$data->column} = get_string('selfenrol', 'block_learnerscript');
                    }
                } else {
                    $row->{$data->column} = get_string('facacdemy', 'block_learnerscript');
                }
                                
            break;
            case 'certificatecode':
                if (!empty($row->certificatecode)) {
                    if (!$this->downloading) {
                        $row->{$data->column} = html_writer::link("$CFG->wwwroot/admin/tool/certificate/view.php?code=$row->certificatecode", $row->certificatecode, array("target" => "_blank"));   
                    } else {
                        $row->{$data->column} = $CFG->wwwroot."/admin/tool/certificate/view.php?code=$row->certificatecode";
                    }
                } else {
                    $row->{$data->column} = '--';
                }
            break;

            case 'traineestatus': 
                $traineestatus = $DB->get_field_sql("SELECT
                    CASE
                        WHEN lcl.userid IS NOT NULL THEN 'Cancel'
                        WHEN lal.userid IS NOT NULL THEN 'Absent'
                    END AS status
                FROM {user} u
                LEFT JOIN {local_cancel_logs} lcl ON lcl.userid = u.id AND lcl.entitytype = 'trainingprogram'
                LEFT JOIN {local_absent_logs} lal ON lal.userid = u.id AND lal.entitytype = 'trainingprogram'
                WHERE u.id = $row->userid;
                ");

                $row->{$data->column} = $traineestatus;
                break;
        }
        return (isset($row->{$data->column}))? $row->{$data->column} : ' -- ';
    }
}
