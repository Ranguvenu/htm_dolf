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

class plugin_organizationfields extends pluginbase {

    public function init() {
        $this->fullname = get_string('organizationfields', 'block_learnerscript');
        $this->type = 'advanced';
        $this->form = true;
        $this->reporttypes = array('organization');
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

        	case 'status': 
                if ($row->{$data->column} == 1) {
                    $row->{$data->column} = get_string('pending','local_organization');
                } elseif ($row->{$data->column} == 2) {
                	 $row->{$data->column} = get_string('approved','local_organization');
                } else {
                     $row->{$data->column} = get_string('rejected','local_organization');
                }
                $row->{$data->column} = !empty($row->{$data->column}) ? $row->{$data->column} : '--';
            break;

            case 'description': 
               
                $row->{$data->column} =  format_text($row->{$data->column});
            break;
             case 'licensekey': 
               
                $row->{$data->column} =  !empty($row->{$data->column}) ? $row->{$data->column} : '--';
            break;
         
            case 'orgsector':
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
                $row->{$data->column} = !empty($sectorlist) ? $sectorlist : '--';
            break;

             case 'orgsegment':
                $segmentslist = $row->{$data->column};
                $lang= current_language();
                $segments = $DB->get_records_sql("SELECT id AS segment, title, titlearabic FROM {local_segment} WHERE id IN ($segmentslist)");
                foreach ($segments as $segment) {
                    if( $lang == 'ar' && !empty($segment->titlearabic)){
                        $slist[] = $segment->titlearabic;
                    }else{
                        $slist[] =  $segment->title;
                    }
                }
                $segmentlist = implode(', ', $slist);
                $row->{$data->column} = !empty($segmentlist) ? $segmentlist : '--';
            break;
            case 'timecreated':
            case 'timemodified':
            
                $row->{$data->column} = !empty($row->{$data->column}) ? userdate($row->{$data->column},get_string('strftimedatemonthabbr', 'core_langconfig')) : '--';
            break;
            case 'orgfieldofwork':
                if($row->{$data->column} == 'investmentbanking') {
                    $row->{$data->column}= get_string('investmentbanking','local_organization');
                } elseif($row->{$data->column} == 'realestate') {
                    $row->{$data->column}= get_string('realestate','local_organization');
                } elseif($row->{$data->column} == 'insurance') {
                    $row->{$data->column}= get_string('insurance','local_organization');
                } else {
                    $row->{$data->column}= get_string('other','local_userapproval');
                }
            break;
        }
        return (isset($row->{$data->column}))? $row->{$data->column} : ' -- ';
    }
}