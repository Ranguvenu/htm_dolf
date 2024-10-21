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

class plugin_userapprovalfields extends pluginbase {

    public function init() {
        $this->fullname = get_string('userapprovalfields', 'block_learnerscript');
        $this->type = 'advanced';
        $this->form = true;
        $this->reporttypes = array('userapproval');
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

        	case 'approvedstatus': 
                if ($row->{$data->column} == 1) {
                    $row->{$data->column} = get_string('pending','local_userapproval');
                } elseif ($row->{$data->column} == 2) {
                     $row->{$data->column} = get_string('approved','local_userapproval');
                } else {
                     $row->{$data->column} = get_string('rejected','local_userapproval');
                }
                $row->{$data->column} = !empty($row->{$data->column}) ? $row->{$data->column} : '--';
            break;

            case 'gender': 
                if ($row->{$data->column} == 1) {
                    $row->{$data->column} = get_string('male','local_userapproval');
                } elseif ($row->{$data->column} == 2) {
                     $row->{$data->column} = get_string('female','local_userapproval');
                } else {
                     $row->{$data->column} = get_string('others','local_userapproval');
                }
                $row->{$data->column} = !empty($row->{$data->column}) ? $row->{$data->column} : '--';
            break;

            case 'id_type': 
                if ($row->{$data->column} == 1) {
                    $row->{$data->column}=get_string('id','local_userapproval');
                } elseif($row->{$data->column} == 2) {
                    $row->{$data->column}=get_string('passport','local_userapproval');
                } elseif($row->{$data->column} == 3) {
                    $row->{$data->column}=get_string('saudiid','local_userapproval');
                } else {
                    $row->{$data->column}=get_string('residentialid','local_userapproval');
                }
                $row->{$data->column} = !empty($row->{$data->column}) ? $row->{$data->column} : '--';
            break;

            case 'organization': 
               
                $row->{$data->column} =  $row->{$data->column}? ((current_language() == 'ar')? $DB->get_field('local_organization','fullnameinarabic',array('id'=>$row->{$data->column})): $DB->get_field('local_organization','fullname',array('id'=>$row->{$data->column}))):'--';
            break;
            case 'country': 

                $countries = get_string_manager()->get_list_of_countries();
               
                $row->{$data->column} =  !empty($row->{$data->column}) ? $countries[$row->{$data->column}] : '--';
            break;

            case 'lang': 

                $languages = get_string_manager()->get_list_of_languages();
               
                $row->{$data->column} =  !empty($row->{$data->column}) ? $languages[$row->{$data->column}] : '--';
            break;

            case 'nationality': 

                $nationalities = get_string_manager()->get_list_of_countries();
               
                $row->{$data->column} =  !empty($row->{$data->column}) ? $nationalities[$row->{$data->column}] : '--';
            break;
         
            case 'sector':
                $sectorslist = $row->{$data->column};
                $lang= current_language();
                if (!empty($sectorslist)) {
                    $sectors = $DB->get_records_sql("SELECT id AS sectorid, title, titlearabic FROM {local_sector} WHERE id IN ($sectorslist)");
                    foreach ($sectors as $sector) {
                        if( $lang == 'ar' && !empty($sector->titlearabic)){
                            $slist[] = $sector->titlearabic;
                        }else{
                            $slist[] =  $sector->title;
                        }
                    }
                    $sectorlist = implode(', ', $slist);
                } else {
                    $sectorlist = '';
                }
                $row->{$data->column} = !empty($sectorlist) ? $sectorlist : '--';
            break;
            case 'segment':
                $segmentslist = $row->{$data->column};
                $lang= current_language();
                if (!empty($segmentslist)) {
                    $segments = $DB->get_records_sql("SELECT id AS segment, title, titlearabic FROM {local_segment} WHERE id IN ($segmentslist)");
                    foreach ($segments as $segment) {
                        if( $lang == 'ar' && !empty($segment->titlearabic)){
                            $slist[] = $segment->titlearabic;
                        }else{
                            $slist[] =  $segment->title;
                        }
                    }
                    $segmentlist = implode(', ', $slist);
                } else {
                    $segmentlist = '';
                }
                $row->{$data->column} = !empty($segmentlist) ? $segmentlist : '--';
            break;
            case 'jobfamily':
                $segmentslist = $row->{$data->column};
                $lang= current_language();
                if (!empty($segmentslist)) {
                    $segments = $DB->get_records_sql("SELECT id, familyname, familynamearabic FROM {local_jobfamily} WHERE id IN ($segmentslist)");
                    foreach ($segments as $segment) {
                        if( $lang == 'ar' && !empty($segment->familynamearabic)){
                            $slist[] = $segment->familynamearabic;
                        }else{
                            $slist[] =  $segment->familyname;
                        }
                    }
                    $segmentlist = implode(', ', $slist);
                } else {
                    $segmentlist = '';
                }
                $row->{$data->column} = !empty($segmentlist) ? $segmentlist : '--';
            break;
            case 'jobrole':
                $segmentslist = $row->{$data->column};
                $lang= current_language();
                if (!empty($segmentslist)) {
                    $segments = $DB->get_records_sql("SELECT id AS segment, title, titlearabic FROM {local_jobrole_level} WHERE id IN ($segmentslist)");
                    foreach ($segments as $segment) {
                        if( $lang == 'ar' && !empty($segment->titlearabic)){
                            $slist[] = $segment->titlearabic;
                        }else{
                            $slist[] =  $segment->title;
                        }
                    }
                    $segmentlist = implode(', ', $slist);
                } else {
                    $segmentlist = '';
                }
                $row->{$data->column} = !empty($segmentlist) ? $segmentlist : '--';
            break;
            case 'timecreated':
            case 'timemodified':
            
                $row->{$data->column} = !empty($row->{$data->column}) ? userdate($row->{$data->column},get_string('strftimedatemonthabbr', 'core_langconfig')) : '--';
            break;
        }
        return (isset($row->{$data->column}))? $row->{$data->column} : ' -- ';
    }
}
