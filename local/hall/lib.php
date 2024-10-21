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
 * Defines plugin library.
 *
 * @package    local_hall
 * @copyright  2022 eabyas  <info@eabyas.com>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */


defined('MOODLE_INTERNAL') || die();

function hall_filters_form($filterparams){
    global $CFG;
    require_once($CFG->dirroot . '/local/organization/dynamicfilters_form.php');
    $filters = array(
        'hall'=>array('local'=>array('halltype','roomlocation','halldate')),
        );
    $mform = new dynamicfilters_form(null, array('filterlist'=>$filters,'filterparams' => $filterparams,'submitid' =>'hall','ajaxformsubmit'=>true), 'post', '', null, true,$_REQUEST);
    return $mform;
}
function schedulehall_filters_form($filterparams) {
    global $CFG;
    require_once($CFG->dirroot . '/local/organization/dynamicfilters_form.php');
    $filters = array(
        'hall'=>array('local'=>array('city', 'building',  'hall', 'halldate')),        
        );
    $mform = new dynamicfilters_form(null, array('filterlist'=>$filters,'filterparams' => $filterparams,'submitid' =>'hall','ajaxformsubmit'=>true), 'post', '', null, true,$_REQUEST);
    return $mform;    
}
function hallsinfo_filters_form($filterparams) {
    global $CFG;
    require_once($CFG->dirroot . '/local/organization/dynamicfilters_form.php');
    $filters = array(
        'hall'=>array('local'=>array('halldate')),
        );
    $mform = new dynamicfilters_form(null, array('filterlist'=>$filters,'filterparams' => $filterparams,'submitid' =>'hall','ajaxformsubmit'=>true), 'post', '', null, true,$_REQUEST);
    return $mform;    
}
function hall_filter($mform){
    $jrattributes = array(
        'ajax' => 'local_hall/hall_datasource',
        'data-type' => 'schedulehalls',
        'id' => 'schedulehalls',
        'data-hallid' => $hallid
    );
    $mform->addElement('autocomplete', 'halladdress', get_string('hall', 'local_hall'),array(), $jrattributes);
}
function city_filter($mform){
    $cityattributes = array(
        'onchange' => "(function(e){ require(['local_hall/hall_datasource'], function(s) {s.city();}) }) (event)",
        'id' => 'city',
        'placeholder' => get_string('city','local_hall')
    );
    $cities = (new local_hall\hall)->listofcities();
    $mform->addElement('autocomplete', 'city',get_string('city','local_hall'), [null => ''] + $cities, $cityattributes);
}
function building_filter($mform){
    $jrattributes = array(
        'onchange' => "(function(e){ require(['local_hall/hall_datasource'], function(s) {s.buildingname();}) }) (event)",
        'ajax' => 'local_hall/hall_datasource',
        'data-type' => 'buildingname',
        'id' => 'buildingname',
    );
    $mform->addElement('autocomplete', 'buildingname', get_string('buildingname', 'local_hall'),[], $jrattributes);
   
}
function halltype_filter($mform){
    $attributes=array('class'=>'type');
    $mform->addElement('static', 'labeltype', get_string('type', 'local_hall'));

    $radioarray=array();
    $radioarray[] = $mform->createElement('radio', 'type', '', get_string('exam', 'local_hall'), 1, $attributes);
    $radioarray[] = $mform->createElement('radio', 'type', '', get_string('trainingprogram', 'local_hall'),2, $attributes);
    $radioarray[] = $mform->createElement('radio', 'type', '', get_string('event', 'local_hall'), 3, $attributes);
    $mform->addGroup($radioarray, 'radioar','', array(' '), false);

}
function roomlocation_filter($mform){    
    $roomlocationoptions = array(null=>get_string('selectroomshape', 'local_hall'),
                                1=>get_string('circle', 'local_hall'),
                                2=>get_string('arectangle', 'local_hall'),
                                3=>get_string('square', 'local_hall'),
                                );

    $mform->addElement('autocomplete', 'roomshape', get_string('roomlocation', 'local_hall'),$roomlocationoptions);
    $mform->setType('type', PARAM_ALPHANUMEXT);
}
function halldate_filter($mform){
    $systemcontext = context_system::instance();    
    $mform->addElement('date_selector', 'datefrom', get_string('datefrom', 'local_hall'),array('optional'=>true));
    $mform->setType('datefrom', PARAM_RAW);
    $mform->addElement('date_selector', 'dateto', get_string('dateto', 'local_hall'),array('optional'=>true));
    $mform->setType('dateto', PARAM_RAW);
}
function local_hall_leftmenunode(){
    $systemcontext = context_system::instance();
    $referralcode = '';
     if(is_siteadmin() ||  has_capability('local/organization:manage_communication_officer', $systemcontext) || has_capability('local/organization:manage_hall_manager', $systemcontext) || has_capability('local/organization:manage_event_manager', $systemcontext) || has_capability('local/hall:view', $systemcontext) ){
        $referralcode .= html_writer::start_tag('li', array('id'=> 'id_leftmenu_referralcode', 'class'=>'pull-left user_nav_div referralcode'));
        $referral_url = new moodle_url('/local/hall/index.php');
        $referral_label = get_string('pluginname','local_hall');
        $referral = html_writer::link($referral_url, '<span class="halls_icon side_menu_img_icon"></span><span class="user_navigation_link_text">'.$referral_label.'</span>',
            array('class'=>'user_navigation_link'));
        $referralcode .= $referral;
        $referralcode .= html_writer::end_tag('li');
    }
    return array('10' => $referralcode);
}
function local_hall_output_fragment_listofhallsform($args){
    global $DB,$CFG,$PAGE;

    $context = context_system::instance();
    $contextid = $context->id;

    $args = (object) $args;
    $context = $args->context;
    $categoryid = $args->categoryid;
    $hallid = $args->hallid;
    $typeid = $args->typeid;
    $examdate = $args->examdate;
    $type = $args->type;
    $startdate = $args->startdate;
    $enddate = $args->enddate;
    $duration = $args->duration;
    $entityid = $args->entityid;
    $submit_type = $args->submit_type;

    $o = '';

    $selectedhall = $DB->get_field('hall', 'city', ['id' => $hallid]);
    $city = $selectedhall;

    $formdata = new stdClass();
    $formdata->halls = $hallid;
    $formdata->typeid = $typeid;
    $formdata->type = $type;
    $formdata->startdate = $startdate;
    $formdata->enddate = $enddate;
    $formdata->duration = $duration;
    $formdata->entitiesseats = $args->entitiesseats;
    $formdata->city = $city;
    $formdata->entityid = $entityid;
    $formdata->buildingname = $DB->get_field('hall', 'buildingname', ['id' => $hallid]);
    $formdata->starttime = $args->starttime;
    $formdata->reservationid = $args->reservationid;
    $formdata->contextid = $contextid;
    $formdata->submit_type = $submit_type;
    $params = array(
    'categoryid' => $id,
    'hallid' => $args->hallid,
    'parent' => $category->parent,
    'context' => $context,
    'itemid' => $itemid,
    'typeid' => $args->typeid,
    'type' => $args->type,
    'startdate' => $args->startdate,
    'enddate' =>  $args->enddate,
    'duration' =>  $args->duration,
    'entityid' =>  $args->entityid,
    'entitiesseats' => $args->entitiesseats,
    'city' => $city,
    'starttime' => $args->starttime,
    'reservationid' => $args->reservationid,
    'contextid' => $contextid,
    'contextid' => $contextid,
    'submit_type' => $submit_type,
    'buildingname' => $DB->get_field('hall', 'buildingname', ['id' => $hallid]),
    );

    $mform = new local_hall\form\listofhallsform(null, $params, 'post', '', null, true, (array)$formdata);
    // Used to set the courseid.

    $mform->set_data($formdata);

    if (!empty($args->jsonformdata)) {
        // If we were passed non-empty form data we want the mform to call validation functions and show errors.
        $mform->is_validated();
    }

    ob_start();
    $mform->display();
    $validateddata = $mform->get_data();
    // $halldata = (new local_hall\hall)->hall_data($hallid, $typeid, $examdate, $type);
    echo "<div class='selecthall'>{$halldata}</div>";
    $o .= ob_get_contents();
    ob_end_clean();
    return $o;
}
function local_hall_output_fragment_hallseats($args){
 global $DB,$CFG,$PAGE;
    $args = (object) $args;
    $context = $args->context;
    $hallid = $args->hallid;
    $seats = $DB->get_field("hall", 'seatingcapacity', ['id' => $hallid]);
    if($args->type == 'questionbank' || $args->type == 'event') {
        $entitiesseats = $seats;
    } else {
        $entitiesseats = $args->entitiesseats;
    }
    $data = array(
        'hallseats' => $seats,
        'entitiesseats' => $entitiesseats,
    ); 
    return json_encode($data);
}
function local_hall_output_fragment_halls($args){
 global $DB,$CFG,$PAGE;
    $args = (object) $args;
    $context = $args->context;
    $categoryid = $args->categoryid;
    return $O;
}
function exams_filter($mform,$query='',$searchanywhere=false, $page=0, $perpage=25) {
    global $DB,$USER;       
    $equipments = ['exam' => 'Exam', 'tprogram' => 'Trainingsession', 'event' => 'Seminar/Webinar'];
    $equipment = [];
    foreach($equipments AS $equipmentkey => $equipmentname){
            $equipment[] = $mform->createElement('radio', "type", '', $equipmentname, $equipmentkey);
    }
    $mform->addGroup($equipment, 'type', get_string('type', 'local_hall'), ['<br/>'], false);
    $mform->addRule('type', get_string('missingtype', 'local_hall'), 'required', null, 'client');
}

function local_hall_output_fragment_hallattributes($args){
    global $DB,$CFG,$PAGE;
    $args = (object) $args;
    $context = $args->context;
    $hallid = $args->hallid;
    $seats = $DB->get_record("hall", ['id' => $hallid]);
    $arr = ['1' => 'Riyadh'];
    $city = $arr[$seats->city];
    $buildingname = $seats->buildingname;
    $data = array(
        'city' => $city,
        'cityid' => $seats->city,
        'buildingname' => $buildingname,
    ); 
    return json_encode($data);
}

function local_hall_output_fragment_hallvalidations($args) {
    global $DB,$CFG,$PAGE;
    
    $args = (object) $args;

    $halltimings = $DB->get_record('hall', ['id'=> $args->hallid], 'hallstarttime, hallendtime');

    $mform = new local_hall\form\listofhallsform();
    $validateddata = $mform->validation($args);

    if($args->type == 'exam') {
        $duration = $DB->get_field('local_exams', 'examduration', ['id' => $args->typeid]);
    } elseif ($args->type == 'event') {
        if($args->typeid > 0) {
            $duration = $DB->get_field('local_events', 'eventduration', ['id' => $args->typeid]);
        } else {
            $duration = $args->duration*60;
        }
    } elseif ($args->type == 'tprogram') {
        if($args->typeid > 0 && $args->duration == 0) {
            $duration = $DB->get_field('tp_offerings', 'duration', ['id' => $args->typeid]);
        } else {
            $duration = $args->duration;
        }
    } elseif ($args->type == 'questionbank') {
        $duration = $args->duration*60;
    }

    $data = array(
        'hallseats' => $validateddata,
        'hallstarttime' => $halltimings->hallstarttime,
        'hallendtime' => $halltimings->hallendtime,
        'entityduration' => $duration
    );
    return json_encode($data);
}
