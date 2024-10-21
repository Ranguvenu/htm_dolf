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
use block_learnerscript\local\ls;
use html_writer;
use context_system;

class plugin_offeringcolumns extends pluginbase {

    public function init() {
        $this->fullname = get_string('offeringcolumns', 'block_learnerscript');
        $this->type = 'undefined';
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
        global $DB, $CFG, $USER;
        $lang= current_language();
        $context = context_system::instance();
        $roleid = $DB->get_field_sql("SELECT id FROM {role} WHERE shortname = 'trainee'");
        switch ($data->column) {
            case 'year':
                $row->{$data->column} = !empty($row->stdate) ? userdate($row->stdate, '%Y') : '--';
            break;
            case 'durationdays':
                $startdatetime = $row->stdate + $row->time;
                $enddatetime = $row->endate + $row->endtime;
                if (!empty($row->endate)) {
                    $datadiff = $enddatetime - $startdatetime;
                } else {
                    $datadiff = '';
                }
                $row->{$data->column} = !empty($datadiff) ? (new ls)->sectodays($startdatetime, $enddatetime) : '--';
            break; 
            case 'enrollments':
                if(!isset($row->enrollments) && isset($data->subquery)){
                    $enrollments =  $DB->get_field_sql($data->subquery);
                }else{
                    $enrollments = $row->{$data->column};
                }
                if (!empty($enrollments)) { 
                    $programenrolid = $DB->get_field('block_learnerscript', 'id', array('type' => 'programenrol'), IGNORE_MULTIPLE);
                    $enrolcheckpermissions = empty($programenrolid) ? false : (new reportbase($programenrolid))->check_permissions($USER->id, $context);
                    if(empty($programenrolid) || empty($enrolcheckpermissions)){
                        $enrollments =  $enrollments ;
                    } else{
                        // $enrollments = html_writer::link("$CFG->wwwroot/blocks/learnerscript/viewreport.php?id=$programenrolid&filter_trainingprogram=$row->programid", $enrollments, array("target" => "_blank"));
                        $enrollments = html_writer::div($enrollments,'btn-link cursor-pointer',array('onclick'=>"window.open('$CFG->wwwroot/blocks/learnerscript/viewreport.php?id=$programenrolid&filter_trainingprogram=$row->programid&filter_code=$row->id')"));
                    }
                    // if(is_siteadmin() || has_capability('local/organization:manage_trainingofficial',$context) 
                    //        || has_capability('local/organization:manage_organizationofficial',$context)){
                    //     $enrollments = html_writer::link("$CFG->wwwroot/blocks/learnerscript/viewreport.php?id=$row->programid&selectedroleid=$roleid", $enrollments, array("target" => "_blank"));
                    // } else if(has_capability('local/organization:manage_trainee', $context) || has_capability('local/organization:manage_trainer', $context)) {
                    //     $enrollments = $enrollments;
                    // }
                } else {
                    $enrollments = 0;
                } 
                $row->{$data->column} = $enrollments;
            break;
            case 'completions':
                if(!isset($row->completions) && isset($data->subquery)){
                    $completions =  $DB->get_field_sql($data->subquery);
                }else{
                    $completions = $row->{$data->column};
                }
                $row->{$data->column} = !empty($completions) ? $completions : 0; 
            break;
            case 'status':
                if ($row->enddate > UNIX_TIMESTAMP()) {
                    $tpstatus = get_string('inprogress', 'local_trainingprogram');
                } else {
                    $tpstatus =  get_string('expired', 'block_learnerscript');
                }
                $row->{$data->column} = !empty($row->enddate) ? $tpstatus : '--';
            break;
            case 'trainingtype':
                if (!empty($row->trainingtype)) {
                    $types = explode(',', $row->trainingtype);
                    foreach ($types as $type) {
                        if ($type == 'online') {
                            $typesdata[] = get_string('scheduleonline', 'block_learnerscript');
                        } else if ($type == 'offline'){
                            $typesdata[] = get_string('scheduleoffline', 'block_learnerscript');
                        } else if ($type == 'elearning'){
                            $typesdata[] = get_string('scheduleelearning', 'block_learnerscript');
                        }
                    }
                    $row->{$data->column} = $typesdata;
                } else {
                    $row->{$data->column} = '';
                }
                
                $row->{$data->column} = !empty($row->{$data->column}) ? implode(',', $row->{$data->column}) : '--';
            break;            
            case 'halllocation':
                if (!empty($row->trainingmethod) && !empty($row->halladdress)) {
                    if ($row->trainingmethod == get_string('scheduleoffline', 'block_learnerscript') || $row->trainingmethod == 'offline') {
                        $city = $DB->get_field_sql("SELECT h.city FROM {hall} h WHERE h.id = $row->halladdress");
                        $row->{$data->column} = (new \local_hall\hall())->listofcities($city);
                    }
                } else {
                    $row->{$data->column} = '';
                }
                $row->{$data->column} = !empty($row->{$data->column}) ? $row->{$data->column} : '--';
            break;
            case 'trainer':
                $traineruserids = $DB->get_records_sql("SELECT DISTINCT pe.userid FROM {program_enrollments} pe JOIN {role} r ON r.id = pe.roleid 
                    WHERE pe.offeringid = $row->id AND pe.programid = $row->programid AND pe.enrolstatus = 1 AND r.shortname = 'trainer'");
                foreach ($traineruserids as $traineruserid) {
                    if ($lang == 'ar') {
                        $usersdata = $DB->get_field_sql("SELECT CONCAT(lu.firstnamearabic, ' ', lu.lastnamearabic) FROM {local_users} lu WHERE lu.userid = $traineruserid->userid");
                        $userdatalinks[] = html_writer::link("$CFG->wwwroot/local/userapproval/userprofile.php?id=$traineruserid->userid", $usersdata, array("target" => "_blank"));
                    } else {
                        $usersdata = $DB->get_field_sql("SELECT CONCAT(lu.firstname, ' ', lu.lastname) FROM {local_users} lu WHERE lu.userid = $traineruserid->userid");
                        $userdatalinks[] = html_writer::link("$CFG->wwwroot/local/userapproval/userprofile.php?id=$traineruserid->userid", $usersdata, array("target" => "_blank"));
                    }
                }
                $row->{$data->column} = !empty($userdatalinks) ? implode(',', $userdatalinks) : '--';
            break;
            case 'sectors':
                if(!empty($row->{$data->column})){
                    $sectorslist = $row->{$data->column};
                    $lang= current_language();
                    $sectors = $DB->get_records_sql("SELECT id AS sectorid, title, titlearabic FROM {local_sector} WHERE id IN ($sectorslist)");
                    foreach ($sectors as $sector) {
                        if( $lang == 'ar' && !empty($sector->titlearabic)){
                            $slist[] = $sector->titlearabic;
                        }else{
                            $slist[] =  $sector->title;
                        }
                    }
                    $sectorlist = implode(', ', $slist);
                }else{
                    $sectorlist = '--';
                }
                $row->{$data->column} = !empty($sectorlist) ? $sectorlist : '--';
            break;
            case 'languages':
                if ($row->languages == 1) {
                    $langlist = get_string('english', 'local_trainingprogram');
                } else {
                    $langlist = get_string('arabic', 'local_trainingprogram');
                }
                $row->{$data->column} = !empty($langlist) ? $langlist : '--';
            break;
            case 'partner':
                $organizationid = $row->{$data->column};
                if (!empty($organizationid)) {
                    $organization = $DB->get_field_sql("SELECT partner FROM {local_organization} WHERE id = $organizationid");
                    $row->{$data->column} = !empty($organization) ? $organization : '--';
                } else {
                    $row->{$data->column} = '--';
                }
            break;
            case 'competencylevel':
                $row->{$data->column} = get_string(lcfirst($row->{$data->column}), 'block_learnerscript');
            break;
            case 'vat':
                if ($row->tax_free != 1) {
                    $taxpercent = get_config('tool_product','tax_percentage');
                    $offeringtax = round(($row->sellingprice * (($taxpercent/100))),2);
                } else {
                    $offeringtax = '';
                }
                $row->{$data->column} = !empty($offeringtax) ? $offeringtax : 0;
            break;
            case 'hallplace':
                if ($row->location == 'clientheadquarters') {
                    $row->{$data->column} = get_string('clientheadquarters', 'block_learnerscript');
                } else {
                    if (!empty($row->halladdress)) {
                        if (is_numeric($row->halladdress) && $row->halladdress != 0) {
                            $halllocation = $DB->get_field_sql("SELECT h.halllocation FROM {hall} h WHERE h.id = $row->halladdress");
                            $row->{$data->column} = get_string($halllocation, 'block_learnerscript');
                        } else {
                            $row->{$data->column} = '--';
                        }
                    } else {
                        $row->{$data->column} = '--';
                    }
                }
            break;
            case 'totalprice':
                $row->{$data->column} = ROUND($row->sellingprice + $row->vat, 2);
            break;
            case 'starttime':
                $examtime = gmdate("h:i",$row->time);
                $examdatemeridian = gmdate('a',$row->time);

                if( $lang == 'ar'){
                    $examdatemeridian = ($examdatemeridian == 'am')? 'صباحا':'مساءً';

                }else{
                    $examdatemeridian = ($examdatemeridian == 'am')? 'AM':'PM';
                }
                $row->{$data->column} = !empty($examtime) ? $examtime . ' ' . $examdatemeridian : '--';
            break;
            case 'endtime':
                $examtime = gmdate("h:i",$row->endtime);
                $examdatemeridian = gmdate('a',$row->endtime);

                if( $lang == 'ar'){
                    $examdatemeridian = ($examdatemeridian == 'am')? 'صباحا':'مساءً';

                }else{
                    $examdatemeridian = ($examdatemeridian == 'am')? 'AM':'PM';
                }
                $row->{$data->column} = !empty($examtime) ? $examtime . ' ' . $examdatemeridian : '--';
            break;
            case 'offeringstatus': 
                if ($row->financially_closed_status == 1) {
                    $row->{$data->column} = get_string('financiallyclosed','block_learnerscript');
                } else if ($row->cancelled == 2) {
                    $row->{$data->column} = get_string('cancelled','block_learnerscript');
                } else if ($row->endate < time()) {
                    $row->{$data->column} = get_string('closed','block_learnerscript');
                } else {
                    $row->{$data->column} = get_string('open','block_learnerscript');
                }
            break;
            case 'trainertype':
                if ($row->trainertype == 0 && $row->trainertype != '') {
                    $row->{$data->column} = get_string('ttindividual', 'block_learnerscript');
                } else if ($row->trainertype == 1) {
                    $row->{$data->column} = get_string('ttorganization', 'block_learnerscript');
                } else {
                    $row->{$data->column} = '--';
                }
                break;
        }
        return (isset($row->{$data->column}))? $row->{$data->column} : ' -- ';
    }
}
