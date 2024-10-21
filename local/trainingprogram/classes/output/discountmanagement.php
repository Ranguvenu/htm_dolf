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
namespace local_trainingprogram\output;

/**
 * Defines the version of Training program
 *
 * @package    local_trainingprogram
 * @copyright  2022 Naveen kumar <naveen@eabyas.com>
 */
defined('MOODLE_INTERNAL') || die;
use renderable;
use templatable;
use local_trainingprogram\output\renderer as render;
use context_system;
/**
 * Training program renderer
 */
class discountmanagement implements renderable
{

    public function __construct(){
       
    }
    public function discountdata_for_template(render $output) {
        global $CFG;
        require_once($CFG->dirroot . '/local/trainingprogram/lib.php');
        $data = [];
        $tabA = [
            'active'=>'active',
            'type'=>'management',
            'name'=>get_string('management','local_trainingprogram'),
            'search'=>0,
            'filter'=>0,
            'action'=>0,
            'couponaction'=>0,
            'earlyregction'=>0,
            'groupsaction'=>0,
            'filterform'=>'',
            'searchtext'=>0,
        ];

        // Coupon
        $couponoptions = array(
            'targetID' => 'coupon',
            'perPage' => 15, 
            'cardClass' => 'col-md-6 col-12', 
            'viewType' => 'card'
        );
        $couponoptions['methodName']='local_trainingprogram_coupondiscountdata';
        $couponoptions['templateName']='local_trainingprogram/listofdiscountcoupon';
        $couponoptions = json_encode($couponoptions);
        $coupondataoptions = json_encode(array('objtype' => 'coupon'));
        $couponfilterdata = json_encode(array());
        $couponcontext = [
            'options' => $couponoptions,
            'dataoptions' => $coupondataoptions,
            'filterdata' => $couponfilterdata,
        ];

        $tabB = [
            'active'=>'',
            'type'=>'coupon',
            'name'=>get_string('coupon_settings','local_trainingprogram'),
            'search'=>1,
            'filter'=>1,
            'action'=>1,
            'couponaction'=>1,
            'earlyregction'=>0,
            'groupsaction'=>0,
            'searchtext'=>get_string('serach_coupon_code','local_trainingprogram'),
            'filterform'=> trainingprogram_coupon_management_filters_form($couponcontext)->render(),
          // 'filterform'=>'',

        ];

        // Early registration
        $eroptions = array(
            'targetID' => 'earlyregistration',
            'perPage' => 15, 
            'cardClass' => 'col-md-6 col-12', 
            'viewType' => 'card'
        );
        $eroptions['methodName']='local_trainingprogram_earlyregistrationdiscountdata';
        $eroptions['templateName']='local_trainingprogram/listofdiscountearlyregistration';
        $eroptions = json_encode($eroptions);
        $erdataoptions = json_encode(array('objtype' => 'earlyregistration'));
        $erfilterdata = json_encode(array());
        $ercontext = [
            'options' => $eroptions,
            'dataoptions' => $erdataoptions,
            'filterdata' => $erfilterdata,
        ];
        $tabC = [
            'active'=>'',
            'type'=>'earlyregistration',
            'name'=>get_string('earlyregistration_settings','local_trainingprogram'),
            'search'=>1,
            'filter'=>1,
            'action'=>1,
            'couponaction'=>0,
            'earlyregction'=>1,
            'groupsaction'=>0,
            'searchtext'=>get_string('serach_days','local_trainingprogram'),
            'filterform'=>trainingprogram_early_registration_management_filters_form($ercontext)->render(),
            //'filterform'=>'',
        ];

        //Groups
        $groupsoptions = array(
            'targetID' => 'groups',
            'perPage' => 15, 
            'cardClass' => 'col-md-6 col-12', 
            'viewType' => 'card'
        );
        $groupsoptions['methodName']='local_trainingprogram_groupsdiscountdata';
        $groupsoptions['templateName']='local_trainingprogram/listofdiscountgroups';
        $groupsoptions = json_encode($groupsoptions);
        $groupsdataoptions = json_encode(array('objtype' => 'groups'));
        $groupsfilterdata = json_encode(array());
        $groupscontext = [
            'options' => $groupsoptions,
            'dataoptions' => $groupsdataoptions,
            'filterdata' => $groupsfilterdata,
        ];
        $tabD= [
            'active'=>'',
            'type'=>'groups',
            'name'=>get_string('groups_settings','local_trainingprogram'),
            'search'=>1,
            'filter'=>1,
            'action'=>1,
            'couponaction'=>0,
            'earlyregction'=>0,
            'groupsaction'=>1,
            'searchtext'=>get_string('serach_min_enrollments','local_trainingprogram'),
            'filterform'=>trainingprogram_groupdiscounts_filters_form($groupscontext)->render(),
        ];
        $data['tabs'] = [$tabA,$tabB,$tabC,$tabD];                                  
        return $data ;  
    }
}

