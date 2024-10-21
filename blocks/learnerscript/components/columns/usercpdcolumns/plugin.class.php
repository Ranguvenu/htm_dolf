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
 * @author: Revanth kumar grandhi
 * @date: 2022
 */
namespace block_learnerscript\lsreports;
use block_learnerscript\local\pluginbase;
use block_learnerscript\local\reportbase;
use html_writer;

class plugin_usercpdcolumns extends pluginbase {

    public function init() {
        $this->fullname = get_string('usercpdcolumns', 'block_learnerscript');
        $this->type = 'undefined';
        $this->form = true;
        $this->reporttypes = array('usercpd');
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
        global $DB;
        switch ($data->column) {
            case 'timecreated';
                if(!isset($row->timecreated) && isset($data->subquery)) {
                    $timecreated =  $DB->get_field_sql($data->subquery);
                } else {
                    $timecreated = $row->{$data->column};
                }
                $row->{$data->column} = !empty($timecreated) ? userdate($timecreated, get_string('strftimedatemonthabbr', 'core_langconfig')) : '--';
            break;
            case 'status':
                if(!isset($row->status) && isset($data->subquery)){
                    $status =  $DB->get_field_sql($data->subquery);
                }else{
                    
                    $status = $row->{$data->column};

                    if( $status == 0 ) {
                        $status = get_string('pending', 'block_learnerscript');
                    } elseif( $status == 1 ) {
                        $status = get_string('approved', 'block_learnerscript');
                    } else {
                        $status = get_string('rejected', 'block_learnerscript');
                    }

                }
                $row->{$data->column} = !empty($status) ? $status : '--';
            break;
            case 'submittedhours':
                if( $row->type == 1 ) {

                    $submittedhours = $DB->get_field('local_cpd_formal_evidence', 'creditedhours', ['evidenceid' => $row->id]);
                } else {
                    $submittedhours = $DB->get_field('local_cpd_informal_evidence', 'creditedhours', ['evidenceid' => $row->id]);
                }
                
                $row->{$data->column} = !empty($submittedhours) ? $submittedhours : '--'; 
            break;
            case 'evidencetype':
                if(!isset($row->evidencetype) && isset($data->subquery)){
                    $evidencetype =  $DB->get_field_sql($data->subquery);
                }else{
                    if( $row->evidencetype == 1 ) {
                        $evidencetype = get_string('formalcpd', 'block_learnerscript');
                    } else {
                        $evidencetype = get_string('informalcpd', 'block_learnerscript');
                    }
                }
                $row->{$data->column} = !empty($evidencetype) ? $evidencetype : '--'; 
            break;
        }
        return (isset($row->{$data->column}))? $row->{$data->column} : ' -- ';
    }
}
