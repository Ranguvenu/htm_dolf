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
use block_learnerscript\local\ls;
use html_writer;

class plugin_offeringfields extends pluginbase {

    public function init() {
        $this->fullname = get_string('offeringfields', 'block_learnerscript');
        $this->type = 'advanced';
        $this->form = true;
        $this->reporttypes = array('offerings');
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
            case 'startdate': 
                if (!empty($row->startdate)) {
                    $examdate = userdate($row->startdate,get_string('strftimedatemonthabbr', 'langconfig'));

                    $row->{$data->column} = !empty($examdate) ? $examdate : '--';
                } else {
                    $row->{$data->column} = '--';
                }
            break;
            case 'enddate';
                if (!empty($row->enddate)) {
                    $examdate = userdate($row->enddate,get_string('strftimedatemonthabbr', 'core_langconfig'));

                    $row->{$data->column} = !empty($examdate) ? $examdate : '--';
                } else {
                    $row->{$data->column} = '--';
                }
            break;
            case 'timecreated':
            case 'timemodified':
            case 'usermodified':
                $row->{$data->column} = !empty($row->{$data->column}) ? userdate($row->{$data->column},get_string('strftimedatemonthabbr', 'core_langconfig')) : '--'; 
            break;
            case 'duration':           
                $row->{$data->column} = !empty($row->{$data->column}) ? (new ls)->strTime($row->{$data->column}) : '--';
            break;
            case 'time': 
            $lang= current_language();
            if(!empty($row->{$data->column})){
               $starttime = gmdate("h:i",$row->{$data->column});
                $starttimemeridian = gmdate('a',$row->{$data->column});

                if( $lang == 'ar'){
                    $startmeridian = ($starttimemeridian == 'am')? 'صباحا':'مساءً';

                }else{
                    $startmeridian = ($starttimemeridian == 'am')? 'AM':'PM';
                }
                if(is_numeric($row->startdate)){
                   $startdate = userdate($row->startdate,get_string('strftimedatemonthabbr', 'core_langconfig'));
                }else{
                    $startdate = $row->startdate;
                }
                $row->{$data->column}  =  !empty($row->{$data->column}) ? $startdate.' '.$starttime.' '.$startmeridian : '--'; 
            }else{
                $row->{$data->column} = '--';
            }
            
            break;
            case 'meetingtype': 
                if ($row->trainingmethod == 'online') {            
                    if ($row->{$data->column} == 1) {
                        $row->{$data->column} = get_string('zoom', 'local_trainingprogram');
                    } else if ($row->{$data->column} == 2) {
                        $row->{$data->column} = get_string('webex', 'local_trainingprogram');
                    } else if ($row->{$data->column} == 3) {
                        $row->{$data->column} = get_string('teams', 'local_trainingprogram');
                    } else {
                        $row->{$data->column} = '--';
                    }
                } else {
                    $row->{$data->column} = '--';
                }
            break;
            case 'trainingmethod':
                if ($row->{$data->column} == 'online') {
                    $row->{$data->column} = get_string('scheduleonline','local_trainingprogram');
                } else if ($row->{$data->column} == 'offline') {
                    $row->{$data->column} = get_string('scheduleoffline','local_trainingprogram');
                } else if ($row->{$data->column} == 'elearning') {
                    $row->{$data->column} = get_string('scheduleelearning','local_trainingprogram');
                }
                $row->{$data->column} = !empty($row->{$data->column}) ? $row->{$data->column} : '--';
            break;   
            case 'type':
                if ($row->{$data->column} == 1) {
                    $row->{$data->column} = get_string('private', 'block_learnerscript');
                } else if ($row->{$data->column} == 2) {
                    $row->{$data->column} = get_string('dedicated', 'block_learnerscript');
                } else {
                    $row->{$data->column} = get_string('public', 'block_learnerscript');
                }
            break;
            case 'organization':
                $organizationid = $row->{$data->column};
                if (!empty($organizationid)) {

                    $lang= current_language();
                    if( $lang == 'ar'){
                        $organization = $DB->get_field_sql("SELECT fullnameinarabic FROM {local_organization} WHERE id = $organizationid"); 
                    } else{
                        $organization = $DB->get_field_sql("SELECT fullname FROM {local_organization} WHERE id = $organizationid"); 
                    }

                }
                $row->{$data->column} = !empty($organization) ? $organization : '--';
            break;
            case 'halladdress':
                $hallid = $row->{$data->column};
                if (!empty($hallid)) {
                    $hall = $DB->get_field_sql("SELECT name FROM {hall} WHERE id = $hallid");
                }
                $row->{$data->column} = !empty($hall) ? $hall : '--';
            break;
            case 'usercreated':
                $userid = $row->{$data->column};
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
            case 'trainingid': 
                $tpid = $row->{$data->column};

                $lang= current_language();
                if( $lang == 'ar'){
                    $row->{$data->column} = $DB->get_field_sql("SELECT namearabic AS name FROM {local_trainingprogram} WHERE id = $tpid");
                } else{
                    $row->{$data->column} = $DB->get_field_sql("SELECT name FROM {local_trainingprogram} WHERE id = $tpid");
                }

                $row->{$data->column} = !empty($row->{$data->column}) ? $row->{$data->column} : '--';
            break;
            case 'officials':
                if (!empty($row->officials)) {
                    $officials = explode(',', $row->officials);
                    foreach ($officials as $traineruserid) {
                    if ($lang == 'ar') {
                        $usersdata = $DB->get_field_sql("SELECT CONCAT(lu.firstnamearabic, ' ', lu.lastnamearabic) FROM {local_users} lu WHERE lu.userid = $traineruserid");
                        $userdatalinks[] = html_writer::link("$CFG->wwwroot/local/userapproval/userprofile.php?id=$traineruserid", $usersdata, array("target" => "_blank"));
                    } else {
                        $usersdata = $DB->get_field_sql("SELECT CONCAT(lu.firstname, ' ', lu.lastname) FROM {local_users} lu WHERE lu.userid = $traineruserid");
                        $userdatalinks[] = html_writer::link("$CFG->wwwroot/local/userapproval/userprofile.php?id=$traineruserid", $usersdata, array("target" => "_blank"));
                    }
                }
                $row->{$data->column} = !empty($userdatalinks) ? implode(',', $userdatalinks) : '--';
            } else {
                $row->{$data->column} = '--';
            }
            break;
        }
        return (isset($row->{$data->column}))? $row->{$data->column} : ' -- ';
    }
}
