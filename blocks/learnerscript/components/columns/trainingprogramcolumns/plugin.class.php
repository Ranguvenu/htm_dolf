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

class plugin_trainingprogramcolumns extends pluginbase {

    public function init() {
        $this->fullname = get_string('trainingprogramcolumns', 'block_learnerscript');
        $this->type = 'undefined';
        $this->form = true;
        $this->reporttypes = array('trainingprograms');
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
        $offeringsreportID = $DB->get_field('block_learnerscript', 'id', array('type' => 'offerings'), IGNORE_MULTIPLE);
        $roleid = $DB->get_field_sql("SELECT id FROM {role} WHERE shortname = 'trainee'");
        switch ($data->column) {
            case 'trainingprogram':
                if(!isset($row->trainingprogram) && isset($data->subquery)){
                    $trainingprogram =  $DB->get_field_sql($data->subquery);
                }else{
                    $trainingprogram = $row->{$data->column};
                }
                $row->{$data->column} = !empty($trainingprogram) ? format_string($trainingprogram) : 0; 
            break;
            case 'enrollments':
                if(!isset($row->enrollments) && isset($data->subquery)){
                    $enrollments =  $DB->get_field_sql($data->subquery);
                }else{
                    $enrollments = $row->{$data->column};
                } 
                if (!empty($enrollments)) { 
                    if(is_siteadmin() || has_capability('local/organization:manage_trainingofficial',$context) 
                           || has_capability('local/organization:manage_organizationofficial',$context)){
                        $enrollments = html_writer::link("$CFG->wwwroot/local/trainingprogram/programenrolleduserslist.php?programid=$row->id&selectedroleid=$roleid", $enrollments, array("target" => "_blank")); 
                    } else if(has_capability('local/organization:manage_trainee', $context) || has_capability('local/organization:manage_trainer', $context)) {
                        $enrollments = $enrollments;
                    }                    
                } else {
                    $enrollments = 0;
                }
                $row->{$data->column} = $enrollments; 
            break;
            case 'offerings': 
                if(!isset($row->offerings) && isset($data->subquery)){
                    $offerings =  $DB->get_field_sql($data->subquery);
                }else{
                    $offerings = $row->{$data->column};
                } 
                $enrolcheckpermissions = empty($offeringsreportID) ? false : (new reportbase($offeringsreportID))->check_permissions($USER->id, $context);
                if(empty($offeringsreportID) || empty($enrolcheckpermissions)){
                    $offerings =  $offerings ;
                } else{
                    $offerings = html_writer::link("$CFG->wwwroot/blocks/learnerscript/viewreport.php?id=$offeringsreportID&filter_trainingprogram=$row->id", $offerings, array("target" => "_blank"));
                }
                $row->{$data->column} = !empty($offerings) ? $offerings : 0; 
            break;
            case 'activeoffering':
                if(!isset($row->activeoffering) && isset($data->subquery)){
                    $activeoffering =  $DB->get_field_sql($data->subquery);
                }else{
                    $activeoffering = $row->{$data->column};
                }
                $enrolcheckpermissions = empty($offeringsreportID) ? false : (new reportbase($offeringsreportID))->check_permissions($USER->id, $context);
                if(empty($offeringsreportID) || empty($enrolcheckpermissions)){
                    $activeoffering =  $activeoffering ;
                } else{
                    $activeoffering = html_writer::link("$CFG->wwwroot/blocks/learnerscript/viewreport.php?id=$offeringsreportID&filter_trainingprogram=$row->id&filter_status=active", $activeoffering, array("target" => "_blank"));
                }
                $row->{$data->column} = !empty($activeoffering) ? $activeoffering : 0; 
            break;
            case 'sector':
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
            case 'completions':
                if(!isset($row->completions) && isset($data->subquery)){
                    $completions =  $DB->get_field_sql($data->subquery);
                }else{
                    $completions = $row->{$data->column};
                }
                $row->{$data->column} = !empty($completions) ? $completions : 0;
                break;
            case 'status':
                $tpstatus = $DB->get_field_sql("SELECT id FROM {local_trainingprogram} WHERE 1 = 1 AND id = $row->id AND availableto > UNIX_TIMESTAMP()");
                $row->{$data->column} = !empty($tpstatus) ? get_string('inprogress', 'local_trainingprogram') : get_string('expired', 'block_learnerscript');
            break;
        }
        return (isset($row->{$data->column}))? $row->{$data->column} : ' -- ';
    }
}
