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

class plugin_trainingprovidercolumns extends pluginbase {

    public function init() {
        $this->fullname = get_string('trainingprovidercolumns', 'block_learnerscript');
        $this->type = 'undefined';
        $this->form = true;
        $this->reporttypes = array('trainingprovider');
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
        $lang= current_language();
        switch ($data->column) {
            case 'provider':
                if ($row->trainertype == 0) {
                    $row->{$data->column} = get_string('individual', 'block_learnerscript');
                } else if ($row->trainertype == 1) {
                    $row->{$data->column} = get_string('organization', 'block_learnerscript');
                } else {
                    $row->{$data->column} = '--';
                }
            break;
            case 'fullname':
                $userid = $row->userid;
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
            case 'offeringstartdate': 
                if (!empty($row->startdate)) {
                    $examdate = userdate($row->startdate,get_string('strftimedatemonthabbr', 'langconfig'));

                    $row->{$data->column} = !empty($examdate) ? $examdate : '--';
                } else {
                    $row->{$data->column} = '--';
                }
            break;
            case 'offeringenddate';
                if (!empty($row->enddate)) {
                    $examdate = userdate($row->enddate,get_string('strftimedatemonthabbr', 'core_langconfig'));

                    $row->{$data->column} = !empty($examdate) ? $examdate : '--';
                } else {
                    $row->{$data->column} = '--';
                }
            break;
            case 'offeringstarttime':
                $examtime = gmdate("h:i",$row->time);
                $examdatemeridian = gmdate('a',$row->time);

                if( $lang == 'ar'){
                    $examdatemeridian = ($examdatemeridian == 'am')? 'صباحا':'مساءً';

                }else{
                    $examdatemeridian = ($examdatemeridian == 'am')? 'AM':'PM';
                }
                $row->{$data->column} = !empty($examtime) ? $examtime . ' ' . $examdatemeridian : '--';
            break;
            case 'offeringendtime':
                $examtime = gmdate("h:i",$row->endtime);
                $examdatemeridian = gmdate('a',$row->endtime);

                if( $lang == 'ar'){
                    $examdatemeridian = ($examdatemeridian == 'am')? 'صباحا':'مساءً';

                }else{
                    $examdatemeridian = ($examdatemeridian == 'am')? 'AM':'PM';
                }
                $row->{$data->column} = !empty($examtime) ? $examtime . ' ' . $examdatemeridian : '--';
            break;
            case 'organization':
                if (!empty($row->trainerorg)) {
                    $lang= current_language();
                    if ($lang == 'ar') {
                        $row->{$data->column} = $DB->get_field_sql("SELECT fullnameinarabic FROM {local_organization} WHERE id = $row->trainerorg");
                    } else {
                        $row->{$data->column} = $DB->get_field_sql("SELECT fullname FROM {local_organization} WHERE id = $row->trainerorg");
                    }
                } else {
                    $row->{$data->column} = '--';
                }
            break;
        }
        return (isset($row->{$data->column}))? $row->{$data->column} : ' -- ';
    }
}
