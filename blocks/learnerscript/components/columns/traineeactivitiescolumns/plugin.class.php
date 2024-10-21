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
use local_hall\hall;

class plugin_traineeactivitiescolumns extends pluginbase {

    public function init() {
        $this->fullname = get_string('traineeactivitiescolumns', 'block_learnerscript');
        $this->type = 'undefined';
        $this->form = true;
        $this->reporttypes = array('traineeactivities');
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
        switch ($data->column) {
            case 'activitytype':
                $row->{$data->column} = !empty($row->{$data->column}) ? get_string($row->{$data->column}, 'block_learnerscript') : '--';
            break;
            case 'activityplace':
                if ($row->activityplace == 'online') {
                    $row->{$data->column} = get_string('scheduleonline', 'block_learnerscript');
                } else if ($row->activityplace == 'offline') {
                    $row->{$data->column} = get_string('scheduleoffline', 'block_learnerscript');
                } else if ($row->activityplace == 'elearning') {
                    $row->{$data->column} = get_string('scheduleelearning', 'block_learnerscript');
                } else if ($row->activityplace != '' && $row->activityplace == 0) {
                    $row->{$data->column} = get_string('offline', 'block_learnerscript');
                } else if ($row->activityplace == 1) {
                    $row->{$data->column} = get_string('virtual', 'block_learnerscript');
                } else {
                    $row->{$data->column} = '--';
                }
            break;
            case 'city':
                if (($row->method != '' && $row->method == 0) || ($row->method == 'offline')) {
                    if (!empty($row->halladdress)) {
                        $city = $DB->get_field_sql("SELECT h.city FROM {hall} h WHERE h.id = $row->halladdress");
                        $cities = (new \local_hall\hall)->listofcities();
                        $row->{$data->column} =  !empty($cities[$city]) ? $cities[$city] : '--';
                    } else {
                        $row->{$data->column} = '--';
                    }
                } else {
                    $row->{$data->column} = '--';
                }
            break;
            case 'startdate': 
            case 'enddate':
                if (!empty($row->{$data->column})) {
                    $row->{$data->column} = userdate($row->{$data->column},get_string('strftimedatemonthabbr', 'langconfig'));

                    $row->{$data->column} = !empty($row->{$data->column}) ? $row->{$data->column} : '--';
                } else {
                    $row->{$data->column} = '--';
                }
            break;
            case 'starttime':
                if ($row->module == 'exams') {
                    if (!empty($row->{$data->column})) {
                        $examtime = (strtotime(userdate($row->examdate, '%d-%m-%Y'))+userdate((($row->starttime)), '%H')*3600 + userdate(($row->starttime), '%M')*60);
                        $examtime = date("h:i",$examtime);
                        $meridian = (userdate((($row->starttime)), '%H')*3600 + userdate(($row->starttime), '%M')*60);
                        $examdatemeridian = gmdate('a', $meridian);
                        if ( $lang == 'ar') {
                            $examdatemeridian = ($examdatemeridian == 'am')? 'ص':' م';
                        } else {
                            $examdatemeridian = ($examdatemeridian == 'am')? 'AM':'PM';
                        }
                    }
                } else {
                    if (!empty($row->{$data->column})) {
                        $examtime = gmdate("h:i",$row->{$data->column});
                        $examdatemeridian = gmdate('a',$row->{$data->column});

                        if( $lang == 'ar'){
                            $examdatemeridian = ($examdatemeridian == 'am')? 'صباحا':'مساءً';

                        }else{
                            $examdatemeridian = ($examdatemeridian == 'am')? 'AM':'PM';
                        }
                    } else {
                        $examtime = '';
                    }
                }
                $row->{$data->column} = !empty($examtime) ? $examtime . ' ' . $examdatemeridian : '--';
            break;
            case 'endtime':
                if ($row->module == 'exams') {
                    if (!empty($row->{$data->column})) {
                        $examtime = (strtotime(userdate($row->examdate, '%d-%m-%Y'))+userdate((($row->endtime)), '%H')*3600 + userdate(($row->endtime), '%M')*60);
                        $examtime = date("h:i",$examtime);
                        $meridian = (userdate((($row->endtime)), '%H')*3600 + userdate(($row->endtime), '%M')*60);
                        $examdatemeridian = gmdate('a', $meridian);
                        if ( $lang == 'ar') {
                            $examdatemeridian = ($examdatemeridian == 'am')? 'ص':' م';
                        } else {
                            $examdatemeridian = ($examdatemeridian == 'am')? 'AM':'PM';
                        }
                    }
                } else {
                    if (!empty($row->{$data->column})) {
                        $examtime = gmdate("h:i",$row->{$data->column});
                        $examdatemeridian = gmdate('a',$row->{$data->column});

                        if( $lang == 'ar'){
                            $examdatemeridian = ($examdatemeridian == 'am')? 'صباحا':'مساءً';

                        }else{
                            $examdatemeridian = ($examdatemeridian == 'am')? 'AM':'PM';
                        }
                    } else {
                        $examtime = '';
                    }
                }
                $row->{$data->column} = !empty($examtime) ? $examtime . ' ' . $examdatemeridian : '--';
            break;
            case 'fees':
                if (!empty($row->purchaseorderid)) {
                    $fee =  $DB->get_field_sql("SELECT tup.amount FROM {tool_user_order_payments} tup 
                        JOIN {tool_products} tp ON tp.id = tup.productid
                        WHERE 1=1 AND tp.referenceid = $row->purchaseorderid");
                    $fees = $fee ? $fee : '--';
                } else {
                    $fees = '--';
                }
                $row->{$data->column} = $fees;
            break; 
            case 'enrolledby':
                $userid = $row->enrolledby;
                $lang = current_language();
                if (!empty($userid)) { 
                    $username = $DB->get_field_sql("SELECT username FROM {user} u WHERE u.id = $userid");
                    if($lang == 'en'){
                       $username = $DB->get_field_sql("SELECT CONCAT(u.firstname, ' ', u.lastname) FROM {user} u WHERE u.id = $userid");
                       $row->{$data->column} = html_writer::link("$CFG->wwwroot/local/userapproval/userprofile.php?id=$userid", $username, array("target" => "_blank"));
                    }else{
                        if($username == 'admin'){ 
                            $username = 'مسؤول النظام';
                            $row->{$data->column} = html_writer::link("$CFG->wwwroot/local/userapproval/userprofile.php?id=$userid", $username, array("target" => "_blank"));
                        } else {
                            $username = $DB->get_field_sql("SELECT CONCAT(lu.firstnamearabic, ' ', lu.lastnamearabic) FROM {local_users} lu WHERE lu.userid = $userid");
                            $row->{$data->column} = html_writer::link("$CFG->wwwroot/local/userapproval/userprofile.php?id=$userid", $username, array("target" => "_blank"));
                        }
                    }
                    if($row->{$data->column} == ' '){
                        $username = $DB->get_field_sql("SELECT CONCAT(u.firstname, ' ', u.lastname) FROM {user} u WHERE u.id = $userid");
                        $row->{$data->column} = html_writer::link("$CFG->wwwroot/local/userapproval/userprofile.php?id=$userid", $username, array("target" => "_blank"));
                    }
                }
                $row->{$data->column} = !empty($row->{$data->column}) ? $row->{$data->column} : '--';
            break;
            case 'completiondate': 
                if ($row->module == 'trainings') { 
                    $completiondate = $DB->get_field_sql("SELECT completiondate FROM {program_completions} WHERE programid = $row->activityid AND userid = $row->userid AND completion_status != 0");

                } else if ($row->module == 'exams') {
                    $completiondate = $DB->get_field_sql("SELECT completiondate FROM mdl_exam_completions WHERE examid = $row->activityid AND userid = $row->userid AND completion_status != 0");

                } else if ($row->module == 'events') {
                    $completiondate = $DB->get_field_sql("SELECT timecreated FROM mdl_tool_certificate_issues WHERE moduleid = $row->activityid AND userid = $row->userid AND moduletype = 'events'");
                } else if ($row->module == 'learningtrack') {
                    $completiondate = $DB->get_field_sql("SELECT timecreated FROM mdl_tool_certificate_issues WHERE moduleid = $row->activityid AND userid = $row->userid AND moduletype = 'learningtracks'");
                } else if ($row->module == 'cpd') {
                    $completiondate = $DB->get_field_sql("SELECT completiondate FROM mdl_local_cpd_completion WHERE cpdid = $row->activityid AND userid = $row->userid AND status != 0");
                }
                $row->{$data->column} = !empty($completiondate) ? userdate($completiondate, get_string('strftimedatemonthabbr', 'langconfig')) : '--';
            break;
            case 'enrolmentdate':
                $row->{$data->column} = !empty($row->{$data->column}) ? userdate($row->{$data->column},get_string('strftimedatemonthabbr', 'core_langconfig')) : '--';
            break;
            case 'certificatecode':  
                $certificatecode = $row->certificatecode;
                $row->{$data->column} = !empty($certificatecode) ? html_writer::link("$CFG->wwwroot/admin/tool/certificate/view.php?code=$certificatecode", $certificatecode, array("target" => "_blank")) : '--';
            break;
            case 'organization':
                if (!empty($row->organization)) {
                    $lang= current_language();
                    if ($lang == 'ar') {
                        $row->{$data->column} = $DB->get_field_sql("SELECT fullnameinarabic FROM {local_organization} WHERE id = $row->organization");
                    } else {
                        $row->{$data->column} = $DB->get_field_sql("SELECT fullname FROM {local_organization} WHERE id = $row->organization");
                    }
                } else {
                    $row->{$data->column} = '--';
                }
            break;
            case 'fullname':
                if (!empty($row->fullname)) {
                    $lang= current_language();
                    if ($lang == 'ar') {
                        $userdata = $DB->get_field_sql("SELECT CONCAT(firstnamearabic, ' ', lastnamearabic) AS fullname FROM {local_users} WHERE userid = $row->userid");
                        if(!empty($userdata)){
                            $row->{$data->column} = $userdata;
                        }else{
                            $row->{$data->column} = $row->fullname;
                        }
                    } else {
                        $row->{$data->column} = $row->fullname;
                    }
                } else {
                    $row->{$data->column} = '--';
                }

        }
        return (isset($row->{$data->column}))? $row->{$data->column} : ' -- ';
    }
}
