<?php
namespace local_hall;

use context_system;
use DatePeriod;
use DateTime;
use filters_form;
use block_contents;
use stdClass;
use moodle_url;
use single_button;

class hall {
    
    public function add_update_hall($data) {
    	global $DB,$USER;
        $equipmentavailable = implode(',',array_keys($data->equipmentavailable));
        $row = array();
		$row['name'] = $data->name;
		$row['type'] = $data->type;
		$row['city'] = $data->city;
		$row['maplocation'] = $data->maplocation;
        $row['entrancegate'] = $data->entrancegate;
		$row['halllocation'] = $data->halllocation;
		$row['roomshape'] = $data->roomshape;
		$row['buildingname'] = format_string($data->buildingname);
		$row['seatingcapacity'] = $data->seatingcapacity;
		$row['availability'] = $data->availability;
		$row['description'] = $data->description['text'];
		$row['equipmentavailable'] = $equipmentavailable;
        $row['code'] = $data->code;
      

        $hours = $data->hallstarthour*3600;
        $minutes = $data->hallstartminute*60;
        $starttime = $hours+$minutes;
        $row['hallstarttime'] = $starttime;

        $hours = $data->hallendhour*3600;
        $minutes = $data->hallendminute*60;
        $endtime = $hours+$minutes;
        $row['hallendtime'] = $endtime;

        if($data->id > 0) {
        	$row['id'] = $data->id;
            $row['timemodified'] = time();
            $record->id = $DB->update_record('hall', $row);
        } else {
            $row['timecreated'] = time();
            $record->id = $DB->insert_record('hall', $row);
        }
        return $record;
    }
    public function set_hall($id) {
    	global $DB;
        $data = $DB->get_record('hall', ['id' => $id], '*', MUST_EXIST);
        $existinguser->$key = explode(',',$data->equipmentavailable);
        array_unshift($existinguser->$key, null);
        $equipmentavailable = array_flip($existinguser->$key);
		$row['id'] = $data->id;
		$row['name'] = $data->name;
		$row['type'] = $data->type;
		$row['city'] = $data->city;
        $row['code'] = $data->code;
		$row['halllocation'] = $data->halllocation;
        $row['maplocation'] = $data->maplocation;
		$row['entrancegate'] = $data->entrancegate;
		$row['roomshape'] = $data->roomshape;
		$row['buildingname'] = format_string($data->buildingname);
		$row['seatingcapacity'] = $data->seatingcapacity;
		$row['availability'] = $data->availability;
		$row['description'] = ['text' => $data->description];
		$row['equipmentavailable'] = $equipmentavailable;
     

        $hours = floor(($data->hallstarttime%86400)/3600);
        $minutes = floor(($data->hallstarttime%3600)/60);
        $row['hallstarthour'] = $hours;
        $row['hallstartminute'] = $minutes;

        $hours = floor(($data->hallendtime%86400)/3600);
        $minutes = floor(($data->hallendtime%3600)/60);
        $row['hallendhour'] = $hours;
        $row['hallendminute'] = $minutes;
        return $row; 
    }
    public function halls() {
        global $DB, $PAGE, $OUTPUT;
        $systemcontext = context_system::instance();
        $renderer = $PAGE->get_renderer('local_hall');
        $filterparams  = $renderer->get_catalog_halls(true);
        $filterparams['submitid'] = 'form#filteringform';
        $filterparams['widthclass'] = 'col-md-12';
        $filterparams['placeholder'] = get_string('searchhall','local_hall');
        $globalinput=$renderer->global_filter($filterparams);
        $halldetails = $renderer->get_catalog_halls();
        $hallsmform = hall_filters_form($filterparams);
        $filterparams['filterform'] = $hallsmform->render();
        $filterparams['halldetails'] = $halldetails;
        $filterparams['globalinput'] = $globalinput;
        $filterparams['systemcontext'] = $systemcontext;
        $renderer->listofhalls($filterparams);
    }
    public function hallinfo($id) {
        global $DB, $PAGE, $OUTPUT;
        $systemcontext = context_system::instance();
        $PAGE->set_context($systemcontext);
        $renderer = $PAGE->get_renderer('local_hall');
        $filterparams  = $renderer->get_catalog_hallinfo(true, $id);

        $filterparams['submitid'] = 'form#filteringform';
        $renderer->global_filter($filterparams);
        echo $halldetails = $renderer->get_catalog_hallinfo(null, $id);
        $filterparams['halldetails'] = $halldetails;
        $renderer->hallsinfo($filterparams);
    }
    public function hallinfo_view($stable, $filterdata) {
        global $DB, $PAGE;
        $systemcontext = context_system::instance();
        $PAGE->set_context($systemcontext);        
        $lang = current_language();
        $hallid = $filterdata->hallid;
        $selectsql = "SELECT h.id, h.*
                        FROM {hall} h WHERE 1=1 AND id = $hallid AND availability = 1 "; 
        $countsql  = "SELECT COUNT(h.id) 
                        FROM {hall} h WHERE 1=1 AND id = $hallid AND availability = 1 ";

        $params = array_merge($searchparams);
        $totalhalls = $DB->count_records_sql($countsql.$formsql, $params);
        $formsql .=" ORDER BY h.id ASC";
        $halls = $DB->get_records_sql($selectsql.$formsql, $params, $stable->start,$stable->length);
        $hallslist = array();
        $count = 0;
        $types = ['1' => get_string('exam', 'local_hall'), '2' => get_string('trainingprogram', 'local_hall'), '3' => get_string('event', 'local_hall')];
        $cities = ['1' => 'Riyadh'];
        $roomshape = ['1'=>get_string('circle','local_hall'),'2'=>get_string('rectangle','local_hall'),'3'=>get_string('square','local_hall')];
        $availability = ['1'=>get_string('enabled','local_hall'),'2'=>get_string('notenabled','local_hall')];
        foreach($halls as $hall) {
            $hallslist[$count]["id"] = $hall->id;
            $hallslist[$count]["name"] = $hall->name;
            $hallslist[$count]["type"] = !empty($types[$hall->type]) ? $types[$hall->type] : '--';
            $hallslist[$count]["city"] = $this->listofcities($hall->city);
            $hallslist[$count]["buildingname"] = format_string($hall->buildingname);
            $hallslist[$count]["seatingcapacity"] = $hall->seatingcapacity;

            if (filter_var($hall->maplocation, FILTER_VALIDATE_URL)) {
                $hallslist[$count]["locationstatus"] = true;
            } else {
                $hallslist[$count]["locationstatus"] = false;
            }

            $hallslist[$count]["maplocation"] = $hall->maplocation;
            $hallslist[$count]["roomshape"] = !empty($roomshape[$hall->roomshape]) ? $roomshape[$hall->roomshape] : '--';
            $hallslist[$count]["availability"] = !empty($availability[$hall->availability]) ? $availability[$hall->availability] : '--';
            $hallslist[$count]["equipmentavailable"] = $this->equipmentavailable($hall->equipmentavailable);
            $hallslist[$count]["description"] = format_text($hall->description, FORMAT_HTML);
            $count++;
        }
        $systemcontext = context_system::instance();
        if (has_capability('local/hall:managehall', $systemcontext)) {
            $managehall = true;
        } else {
            $managehall = false;
        }
        $reservations = $DB->get_records('hall_reservations', ['hallid' => $filterdata->hallid]);

        $reservatonlist = [];
        foreach($reservations as $reservations) {
            $reservatonlist[$count]['id'] = $reservations->id; 
            switch($reservations->type){
                case 'tprogram':
                    $reservatonlist[$count]['type'] = get_string('pluginname', 'local_trainingprogram');
                    $entity = $DB->get_record_sql("SELECT ltp.name, namearabic as arabicname, ltp.code FROM {local_trainingprogram} ltp JOIN {tp_offerings} tpo ON tpo.trainingid = ltp.id WHERE tpo.id = $reservations->typeid");
                    $reservatonlist[$count]['code'] = $entity->code;

                    break;
                case 'event':
                    $reservatonlist[$count]['type'] = get_string('event', 'local_hall');
                    $entity = $DB->get_record_sql("SELECT title  as name, titlearabic as arabicname, code FROM {local_events} WHERE id = ".$reservations->typeid);
                    $reservatonlist[$count]['code'] = $entity->code;
                    break;
                case 'exam':
                    $reservatonlist[$count]['type'] = get_string('exam', 'local_exams');
                    $entity = $DB->get_record_sql("SELECT exam as name, examnamearabic as arabicname, code FROM {local_exams} where id =". $reservations->typeid);
                    $reservatonlist[$count]['code'] = $entity->code;                    
                    break;
                case 'questionbank':
                    $reservatonlist[$count]["type"] = get_string('questionbank', 'local_questionbank');
                    $entity = $DB->get_record_sql("SELECT workshopname AS name, workshopname as arabicname FROM {local_questionbank} WHERE id =". $reservations->typeid);
                    $reservatonlist[$count]['code'] = '--';
                    break;                    
            }
            
            if($lang == 'ar') {
                $reservatonlist[$count]['modulename'] = $entity->arabicname;
            } else {
                $reservatonlist[$count]['modulename'] = $entity->name;
            }

            $reservatonlist[$count]['typeid'] = $reservations->typeid;
            $reservatonlist[$count]['bookedseats'] = $reservations->seats;
            $reservatonlist[$count]['date'] = userdate(strtotime($reservations->examdate),get_string('strftimedatemonthabbr', 'core_langconfig'));
            $reservatonlist[$count]['reservationdate'] = strtotime($reservations->examdate);
            $endtimemeridian = gmdate('a',mktime(0, 0, $reservations->slotstart));
            if($lang == 'ar') {
                $endtmeridian = ($endtimemeridian == 'am')? 'صباحا':'مساءً';
                
            } else {
                $endtmeridian = ($endtimemeridian == 'am')? 'AM':'PM';
            }

            $reservatonlist[$count]['entityslotstart'] = date('h:i', mktime(0, 0, $reservations->slotstart)).' '.$endtmeridian;

            $endtimemeridian = gmdate('a',mktime(0, 0, $reservations->slotend));
            if($lang == 'ar') {
                $endtmeridian = ($endtimemeridian == 'am')? 'صباحا':'مساءً';
                
            } else {
                $endtmeridian = ($endtimemeridian == 'am')? 'AM':'PM';
            }

            $reservatonlist[$count]['entityslotend'] = date('h:i', mktime(0, 0, $reservations->slotend)).' '.$endtmeridian;
            $reservatonlist[$count]['entitydate'] = $reservations->examdate;
            $reservatonlist[$count]['slotstart'] = $reservations->slotstart;
            $reservatonlist[$count]['slotend'] = $reservations->slotend;
            $reservatonlist[$count]['hallid'] = $reservations->hallid;
            $reservatonlist[$count]['entitytype'] = $reservations->type;
            $hallseats = $DB->get_field('hall', 'seatingcapacity', ['id' => $reservations->hallid]);
            $reservatonlist[$count]['hallseats'] = $hallseats;
            if($reservations->status == 1) {
                $reservatonlist[$count]['status'] = true;
            } else {
                $reservatonlist[$count]['status'] = false;
            }

            switch($reservations->type) {
                case 'tprogram':
                    $selectsql = "SELECT le.id, ltp.name, availableseats as seating, round(le.time/60, 0) as starttime
                                    FROM {tp_offerings} AS le
                                    JOIN {local_trainingprogram} ltp ON ltp.id = le.trainingid 
                                    WHERE le.id =".$reservations->typeid;
                break;
                case 'event':
                    $selectsql = "SELECT  le.id, le.title as name, le.slot as starttime, {$hallseats} as seating
                                   FROM {local_events} AS le WHERE le.id =".$reservations->typeid; 
                break;
                default:
                    $selectsql = "SELECT le.id, le.exam as name, 0 as seating, 0 as starttime
                                    FROM {local_exams} AS le WHERE le.id =".$reservations->typeid;
            }
            $entityinfo = $DB->get_record_sql($selectsql);
            $reservatonlist[$count]['entitytotalseats'] = $entityinfo->seating;
            $reservatonlist[$count]['entityname'] = $entityinfo->name;
            if($filterdata->type == 'tprogram') {
                $entityinfo->starttime = strtotime(intdiv($reservaton->starttime, 60).':'. ($reservaton->starttime % 60));
            }        
            $reservatonlist[$count]['starttime'] = $entityinfo->starttime;
            $count++;
        }

        $coursesContext = array(
            "hascourses" => $hallslist,
            "nocourses" => $nocourse,
            "totalhalls" => COUNT($reservatonlist),
            "length" => count($reservatonlist),
            "managehall" => $managehall,
            "hallreservations" => $reservatonlist,
        );
        return $coursesContext;
    }
    public function reservationinfo() {
        global $DB, $PAGE, $OUTPUT, $CFG;
        $systemcontext = context_system::instance();

        $renderer = $PAGE->get_renderer('local_hall');
        echo $OUTPUT->render_from_template('local_hall/reservationtype', $systemcontext);
        echo $halldetails = $renderer->get_catalog_reservations();
    }
    public function get_listof_halls($stable, $filterdata) {
        global $DB,$CFG,$PAGE;
        $systemcontext = context_system::instance();
        $PAGE->set_context($systemcontext);
        $selectsql = "SELECT h.id, h.name,h.code, h.*
                        FROM {hall} AS h ";
        $countsql  = "SELECT COUNT(h.id) FROM {hall} AS h ";

        $formsql  = " WHERE 1 = 1 ";

        if(!is_siteadmin() && has_capability('local/organization:manage_event_manager', $systemcontext) ) {

            $formsql .= " AND h.halllocation = 'Outside' ";

        }

        if(isset($filterdata->search_query) && trim($filterdata->search_query) != ''){
            $formsql .= " AND (h.name LIKE :search OR h.code LIKE :codesearch)";
            $searchparams = array(
                               'search' => '%'.trim($filterdata->search_query).'%',
                               'codesearch' => '%'.trim($filterdata->search_query).'%',
                            );
        }else{
            $searchparams = array();
        }
        if(!empty($filterdata->type)){
            $formsql .= " AND h.type LIKE '%$filterdata->type%'";
        }
        if(!empty($filterdata->roomshape)){
            $formsql .= " AND h.roomshape LIKE '%$filterdata->roomshape%'";
        }

        $datefilterdata = (array)$filterdata;

        if ($datefilterdata["datefrom[day]"]) {

            $start_year = $datefilterdata["datefrom[year]"];
            $start_month =$datefilterdata["datefrom[month]"];
            $start_day = $datefilterdata["datefrom[day]"];
            $filter_startdate = mktime(0,0,0,$start_month, $start_day, $start_year);
            $formsql .= " AND h.timecreated >= $filter_startdate ";
        }
        if(!empty($datefilterdata["dateto[day]"])){

            $end_year = $datefilterdata["dateto[year]"];
            $end_month =$datefilterdata["dateto[month]"];
            $end_day = $datefilterdata["dateto[day]"];
            $filter_enddate = mktime(0,0,0,$end_month, $end_day, $end_year)+86399;
            $formsql .= " AND h.timecreated <= $filter_enddate";

        }
  
        $params = array_merge($searchparams);
        $totalhalls = $DB->count_records_sql($countsql.$formsql, $params);
        $formsql .=" ORDER BY h.id DESC";
        $halls = $DB->get_records_sql($selectsql.$formsql, $params, $stable->start,$stable->length);
        $hallslist = array();
        $count = 0;
        $types = ['1' => get_string('exam', 'local_hall'), '2' => get_string('trainingprogram', 'local_hall'), '3' => get_string('event', 'local_hall')];
        $cities = ['1' => 'Riyadh'];
        $roomshape = ['1'=>get_string('circle','local_hall'),'2'=>get_string('rectangle','local_hall'),'3'=>get_string('square','local_hall')];
        $availability = ['1'=>get_string('enabled','local_hall'),'2'=>get_string('notenabled','local_hall')];
        $slno = 0;
        foreach($halls as $hall) {
            $hallslist[$count]["slno"] = ++$slno;
            $hallslist[$count]["id"] = $hall->id;
            $hallslist[$count]["name"] = $hall->name;
            $hallslist[$count]["hallcode"] = $hall->code;
            $hallslist[$count]["type"] = !empty($types[$hall->type]) ? $types[$hall->type] : '--';
            $hallslist[$count]["city"] = $this->listofcities($hall->city);
            $hallslist[$count]["buildingname"] = format_string($hall->buildingname);
            $hallslist[$count]["seatingcapacity"] = $hall->seatingcapacity;

            if (filter_var($hall->maplocation, FILTER_VALIDATE_URL)) {
                $hallslist[$count]["locationstatus"] = true;
            } else {
                $hallslist[$count]["locationstatus"] = false;
            }

            $hallslist[$count]["maplocation"] = $hall->maplocation;
            $hallslist[$count]["roomshape"] = !empty($roomshape[$hall->roomshape]) ? $roomshape[$hall->roomshape] : '--';
            $hallslist[$count]["availability"] = !empty($availability[$hall->availability]) ? $availability[$hall->availability] : '--';
            $hallslist[$count]["equipmentavailable"] = $this->equipmentavailable($hall->equipmentavailable);
            $hallslist[$count]["description"] = format_text($hall->description, FORMAT_HTML);
            $hallslist[$count]["actionsview"] = (is_siteadmin() ||  has_capability('local/organization:manage_hall_manager', $systemcontext) || has_capability('local/organization:manage_event_manager', $systemcontext)) ?  true : false;
            $hallslist[$count]["viewhallurl"] = $CFG->wwwroot;
            $count++;
        }
        $systemcontext = context_system::instance();
        if(is_siteadmin() ||  has_capability('local/organization:manage_communication_officer', $systemcontext) ||  has_capability('local/organization:manage_hall_manager', $systemcontext) || has_capability('local/organization:manage_event_manager', $systemcontext)){
            $managehall = true;
        } else {
            $managehall = false;
        }        
        $coursesContext = array(
            "hascourses" => $hallslist,
            "nocourses" => $nocourse,
            "totalhalls" => $totalhalls,
            "length" => count($hallslist),
            "managehall" => $managehall,
        );
        return $coursesContext;
    }
    public function get_listof_reservations($stable, $filterdata) {
        global $DB;
        $filterdata = (object)$filterdata;
        $lang = current_language();

        $entities = [];
        switch($filterdata->type) {
            case 'tprogram':

                if( $lang == 'ar' ){
                    $selectsql = "SELECT le.id,ltp.id as programid, ltp.namearabic as name, le.halladdress, le.startdate, le.enddate, le.duration, availableseats as seating, le.time as starttime,le.code as code
                                    FROM {tp_offerings} AS le
                                    JOIN {local_trainingprogram} ltp ON ltp.id = le.trainingid 
                                    WHERE le.halladdress > 0  AND date(FROM_UNIXTIME(le.startdate)) >= CURDATE() "; 
                } else {
                    $selectsql = "SELECT le.id,ltp.id as programid, ltp.name, le.halladdress, le.startdate, le.enddate, le.duration, availableseats as seating, le.time as starttime,le.code as code
                                    FROM {tp_offerings} AS le
                                    JOIN {local_trainingprogram} ltp ON ltp.id = le.trainingid 
                                    WHERE le.halladdress > 0  AND date(FROM_UNIXTIME(le.startdate)) >= CURDATE() "; 
                }

             
                $countsql  = "SELECT COUNT(le.id) 
                                FROM {tp_offerings} AS le
                                JOIN {local_trainingprogram} ltp ON ltp.id = le.trainingid
                                WHERE le.halladdress > 0  AND date(FROM_UNIXTIME(le.startdate)) >= CURDATE() ";

                if(isset($filterdata->search_query) && trim($filterdata->search_query) != ''){
                    $formsql .= " AND le.name LIKE :search";
                    $searchparams = array('search' => '%'.trim($filterdata->search_query).'%');
                }else{
                    $searchparams = array();
                }
               
                $formsql .=" ORDER BY le.id DESC";
            break;
            case 'event':

                if( $lang == 'ar' ){
                    $selectsql = "SELECT  le.id, le.titlearabic as name, le.startdate, le.enddate, le.halladdress, le.eventduration as duration, le.slot as starttime,le.code as code
                                   FROM {local_events} AS le WHERE le.method = 0 AND le.halladdress is not null  AND date(FROM_UNIXTIME(le.startdate)) >= CURDATE() "; 
                } else {
                    $selectsql = "SELECT  le.id, le.title as name, le.startdate, le.enddate, le.halladdress, le.eventduration as duration, le.slot as starttime,le.code as code
                                   FROM {local_events} AS le WHERE le.method = 0 AND le.halladdress is not null  AND date(FROM_UNIXTIME(le.startdate)) >= CURDATE() "; 
                }

                $countsql  = "SELECT COUNT(le.id) FROM {local_events} AS le  WHERE (le.halladdress  is not null OR le.halladdress !='')  AND date(FROM_UNIXTIME(le.startdate)) >= CURDATE()";

                if(isset($filterdata->search_query) && trim($filterdata->search_query) != ''){
                    $formsql .= " AND le.title LIKE :search";
                    $searchparams = array('search' => '%'.trim($filterdata->search_query).'%');
                }else{
                    $searchparams = array();
                }
                $formsql .=" ORDER BY le.id DESC";
            break;
            default:

                if( $lang == 'ar' ){
                    $selectsql = "SELECT le.id,ltp.id as programid, ltp.namearabic as name, le.halladdress, le.startdate, le.enddate, le.duration, availableseats as seating, le.time as starttime,le.code as code
                                    FROM {tp_offerings} AS le
                                    JOIN {local_trainingprogram} ltp ON ltp.id = le.trainingid 
                                    WHERE le.halladdress > 0  AND date(FROM_UNIXTIME(le.startdate)) >= CURDATE() "; 
                } else {
                    $selectsql = "SELECT le.id,ltp.id as programid, ltp.name, le.halladdress, le.startdate, le.enddate, le.duration, availableseats as seating, le.time as starttime,le.code as code
                                    FROM {tp_offerings} AS le
                                    JOIN {local_trainingprogram} ltp ON ltp.id = le.trainingid 
                                    WHERE le.halladdress > 0  AND date(FROM_UNIXTIME(le.startdate)) >= CURDATE() "; 
                }

             
                $countsql  = "SELECT COUNT(le.id) 
                                FROM {tp_offerings} AS le
                                JOIN {local_trainingprogram} ltp ON ltp.id = le.trainingid
                                WHERE le.halladdress > 0  AND date(FROM_UNIXTIME(le.startdate)) >= CURDATE() ";

                if(isset($filterdata->search_query) && trim($filterdata->search_query) != ''){
                    $formsql .= " AND le.name LIKE :search";
                    $searchparams = array('search' => '%'.trim($filterdata->search_query).'%');
                }else{
                    $searchparams = array();
                }
                $formsql .=" ORDER BY le.id DESC";
                $filterdata->type = 'tprogram';
        }

        $params = array_merge($searchparams);
        $totalexams = $DB->count_records_sql($countsql.$formsql, $params, $stable->start,$stable->length);
        $reservatons = $DB->get_records_sql($selectsql.$formsql, $params, $stable->start,$stable->length);
        $records = [];
        $i=1;
        foreach ($reservatons as $reservaton) {
            $reserve = array();
            
            if($i==1){
                $programid = $reservaton->programid;
            }
            
            if($programid != $reservaton->programid){
                $programid = $reservaton->programid;
                $i=1;
            }

            $context = context_system::instance();
            $reserve["contextid"] = $context->id;
            $reserve['code'] = $reservaton->code;;
            $reserve['id'] =  $reservaton->id;
            if($filterdata->type == 'tprogram'){
                $reserve['tprogram'] =  1;
            }

            $reserve['exam'] =  $reservaton->name;
            $seating = $DB->get_field('hall', 'seatingcapacity', ['id' => $reservaton->halladdress]);
            $reserve['seats'] =  !empty($reservaton->seating) ? $reservaton->seating : $seating;
            $reserve['startdate'] =  date('Y-m-d', $reservaton->startdate);
            $reserve['enddate'] =  date('Y-m-d', $reservaton->enddate);

            $endtimemeridianstart = gmdate('a',$reservaton->starttime);
            $endtimemeridianend = gmdate('a',($reservaton->starttime + $reservaton->duration));
            $lang = current_language();
            if($lang == 'ar') {
    
                $endtimemeridianstart = ($endtimemeridianstart == 'am')? 'صباحا':'مساءً';
                $endtimemeridianend = ($endtimemeridianend == 'am')? 'صباحا':'مساءً';

            } else {
    
                $endtimemeridianstart = ($endtimemeridianstart == 'am')? 'AM':'PM';
                $endtimemeridianend = ($endtimemeridianend == 'am')? 'AM':'PM';
    
            }

            $starttimefrom = date('h:i', mktime(0, 0, $reservaton->starttime)). ' ' .  $endtimemeridianstart;
            $starttimeto = date('h:i', mktime(0, 0, ($reservaton->starttime + $reservaton->duration))). ' ' .  $endtimemeridianend;

            $reserve['time'] =  $starttimefrom .' '.get_string('to','local_hall').' '. $starttimeto;
            $reserve['hallid'] =  $reservaton->halladdress ? $reservaton->halladdress : 0;
            $reserve['hallseats'] =  !empty($seating) ? $seating : '0';
            $reserve['starttime'] = $reservaton->starttime;
           /* if(empty($filterdata->type))
             {
                $reserve['type'] = 'exam';                    
            }*/ 
            if(empty($filterdata->type) || $filterdata->type == 'tprogram')
            {
                $reserve['type'] = 'tprogram';                    
            }
            else {
                $reserve['type'] = $filterdata->type;
            }
            $records[] = $reserve;
            $i++;
        }
        $coursesContext = array(
            "exams" => $records,
            "nocourses" => $nocourse,
            "totalexams" => $totalexams,
            "length" => count($records)
        );
        return $coursesContext;
    }
    public function hall_info($hallid) {
        global $DB, $PAGE, $OUTPUT;
        $systemcontext = context_system::instance();
        $PAGE->set_context($systemcontext);
        $hall = $DB->get_record('hall', ['id' => $hallid], '*', MUST_EXIST);
        $types = ['1' => get_string('exam', 'local_hall'), '2' => get_string('trainingprogram', 'local_hall'), '3' => get_string('event', 'local_hall')];
        $cities = ['1' => 'Riyadh'];
        $roomshape = ['1'=>get_string('circle','local_hall'),'2'=>get_string('rectangle','local_hall'),'3'=>get_string('square','local_hall')];
        $availability = ['1'=>get_string('enabled','local_hall'),'2'=>get_string('notenabled','local_hall')];
        $hallslist = [];
        $hallslist["id"] = $hall->id;
        $hallslist["name"] = $hall->name;
        $hallslist["code"] = format_string($hall->code);
        $hallslist["type"] = !empty($types[$hall->type]) ? $types[$hall->type] : '--';
        $hallslist["city"] = $this->listofcities($hall->city);
        $hallslist["buildingname"] = format_string($hall->buildingname);
        $hallslist["seatingcapacity"] = $hall->seatingcapacity;

        if (filter_var($hall->maplocation, FILTER_VALIDATE_URL)) {
            $hallslist["locationstatus"] = true;
        } else {
            $hallslist["locationstatus"] = false;
        }

        $hallslist["halllocation"] = get_string($hall->halllocation,'local_hall');
        $hallslist["maplocation"] = $hall->maplocation;
        $hallslist["roomshape"] = !empty($roomshape[$hall->roomshape]) ? $roomshape[$hall->roomshape] : '--';
        $hallslist["availability"] = !empty($availability[$hall->availability]) ? $availability[$hall->availability] : '--';
        $hallslist["equipmentavailable"] = $this->equipmentavailable($hall->equipmentavailable);
        $hallslist["seating"] = $hall->seatingcapacity;

        $endtimemeridianstart = gmdate('a',$hall->hallstarttime);
        $endtimemeridianend = gmdate('a',$hall->hallendtime);
        $lang = current_language();
        if($lang == 'ar') {
            $endtimemeridianstart = ($endtimemeridianstart == 'am')? 'صباحا':'مساءً';
            $endtimemeridianend = ($endtimemeridianend == 'am')? 'صباحا':'مساءً';
            
        } else {
            $endtimemeridianstart = ($endtimemeridianstart == 'am')? 'AM':'PM';
            $endtimemeridianend = ($endtimemeridianend == 'am')? 'AM':'PM';
        }
        $hallslist["starttime"] = date('h:i', mktime(0, 0, $hall->hallstarttime))  . ' ' .  $endtimemeridianstart;
        $hallslist["endtime"] =  date('h:i', mktime(0, 0, $hall->hallendtime)) . ' ' . $endtimemeridianend;
        $hallslist["description"] = format_text($hall->description, FORMAT_HTML);
        $renderer = $PAGE->get_renderer('local_hall');
        $org  = $renderer->hall_info($hallslist);
        return $org;
    }
    public function equipmentavailable($data) {
        global $DB;
        $equipments = ['1' => get_string('projector','local_hall'), '2' => get_string('earphone','local_hall'), '3' => get_string('television','local_hall')];
        $equipmentsdata = explode(',', $data);
        $data = [];
        foreach($equipmentsdata as $equipment) {
            $data[] = $equipments[$equipment];
        }
        return implode(',', $data);
    }

    public function hall_data($stable, $filterdata) {
        global $DB, $PAGE;
        $lang = current_language();
        $systemcontext = context_system::instance();
        $PAGE->set_context($systemcontext);
        $renderer = $PAGE->get_renderer('local_hall');
        $hall = $DB->get_record('hall', ['id' => $filterdata->hallid]);
        if($filterdata->type == 'exam') {
            $moduletype = $DB->get_record('local_exams', ['id' => $filterdata->typeid]);
            if( $lang == 'ar' ){
                $entityname = $moduletype->examnamearabic; 
            } else {
                $entityname = $moduletype->exam;
            }
            $entitystart = $moduletype->slot;
            $duration = $moduletype->examduration;
            $entityseats = $moduletype->seatingcapacity;
            $type = get_string('exam', 'local_hall');

        } elseif ($filterdata->type == 'event') {
            if($filterdata->typeid > 0) {
                $moduletype = $DB->get_record('local_events', ['id' => $filterdata->typeid]);
                $duration = $moduletype->eventduration;
                if( $lang == 'ar' ){
                    $entityname = $moduletype->titlearabic; 
                } else {
                    $entityname = $moduletype->title;
                }
                $entitystart = $moduletype->slot;
            } else {
                $duration = $filterdata->duration*60;
                $entitystart = $filterdata->starttime;
            }
            $type = get_string('event', 'local_hall');
            $entityseats = $DB->get_field("hall", 'seatingcapacity', ['id' => $filterdata->hallid]);;
        } elseif ($filterdata->type == 'tprogram') {
            if($filterdata->typeid > 0 && $filterdata->duration == 0) {
                $moduletype = $DB->get_record('tp_offerings', ['id' => $filterdata->typeid]);

                if( $lang == 'ar' ){
                    $entityname = $DB->get_field('local_trainingprogram', 'namearabic', ['id' => $moduletype->trainingid]);
                } else {
                    $entityname = $DB->get_field('local_trainingprogram', 'name', ['id' => $moduletype->trainingid]);
                }
                $entitystart = $moduletype->time;
                $duration = $moduletype->duration;
                $entityseats = $moduletype->availableseats;
            } else {
                $entityid = $filterdata->entityid;
                $duration = $filterdata->duration;
                $entityseats = $filterdata->entitiesseats;
                $entitystart = $filterdata->starttime;
            }
            $type = get_string('offering', 'local_hall');
        } elseif ($filterdata->type == 'questionbank') {
            $entitystart = $filterdata->starttime;
            $duration = $filterdata->duration*60;
            $type = get_string('questionbank', 'local_hall');
            $entityseats = $DB->get_field("hall", 'seatingcapacity', ['id' => $filterdata->hallid]);
        }

        if($filterdata->starttime >= $hall->hallstarttime &&  ($filterdata->starttime + $duration) <= $hall->hallendtime) {
            $due = $duration;
            $hallstart = $hall->hallstarttime;
            $count = 0;

            for($i=0; $hallstart <= $hall->hallendtime; $i++) {

                if($filterdata->starttime == $hallstart) {
                    $count++;
                    $matchedcount = $count;
                }
                $hallstart = $hallstart + $duration;
            }

            $start_time = $entitystart;
            $slot = $start_time+$due;
            $end_time = $slot;

            $filterstarttime = date('h:i A', mktime(0, 0, $filterdata->starttime));
            $data = [];
            for ($i=0; $slot <= $end_time; $i++) {
                $row['slid'] = $i;

                $endtimemeridianstart = gmdate('a',$start_time);
                $endtimemeridianend = gmdate('a',$end_time);
                $lang = current_language();
                if($lang == 'ar') {

                    $endtimemeridianstart = ($endtimemeridianstart == 'am')? 'صباحا':'مساءً';
                    $endtimemeridianend = ($endtimemeridianend == 'am')? 'صباحا':'مساءً';
                    
                } else {

                    $endtimemeridianstart = ($endtimemeridianstart == 'am')? 'AM':'PM';
                    $endtimemeridianend = ($endtimemeridianend == 'am')? 'AM':'PM';
                
                }

                $row['start'] = date('h:i', mktime(0, 0, $start_time)) . ' ' . $endtimemeridianstart;
                $row['end'] = date('h:i', mktime(0, 0, $slot)) . ' ' . $endtimemeridianend;
                $row['start_time'] = $start_time;
                $row['end_time'] = $slot;
                $row['hallid'] = $filterdata->hallid;
                $row['typeid'] = $filterdata->typeid; 
                $row['submit_type'] = $filterdata->submit_type;
                $row['examdate'] = $filterdata->examdate;
                $row['type'] = $filterdata->type;
                $row['reservationid'] = $filterdata->reservationid;

                // if($filterstarttime == $row['start']) {
                    $row['currentbooking'] = true;
                //     $bookedid = $i;
                // } else {
                //     $row['currentbooking'] = false;
                // }

                $totalseats = $DB->get_field("hall", 'seatingcapacity', ['id' => $filterdata->hallid]);
                $seats= [];

                $bookedseats = $DB->get_records_sql("SELECT id, seats, typeid, type 
                    FROM {hall_reservations}  
                    WHERE (hallid = {$filterdata->hallid} AND slotstart = '{$start_time}' AND slotend = '{$slot}' AND examdate = '{$filterdata->examdate}') OR 
                    (hallid = {$filterdata->hallid} AND  '{$start_time}' >= slotstart AND '{$start_time}' < slotend AND examdate = '{$filterdata->examdate}')  OR 
                    (hallid = {$filterdata->hallid} AND slotend > '{$start_time}' AND slotend <= '{$slot}' AND examdate = '{$filterdata->examdate}') ");

                foreach($bookedseats as $bookedseat) {
                    $seats[] = $bookedseat->seats;
                    if(($bookedseat->typeid != $filterdata->typeid && $filterdata->type != 'exam' && !empty($filterdata->typeid))) {
                        $hallstatus = true;
                    } elseif($bookedseat->typeid != $filterdata->typeid && $bookedseat->type != 'exam'  && !empty($filterdata->typeid)) {
                        $hallstatus = true;
                    }else {
                        $hallstatus = false;
                    }
                }
                $totalbookedseats = array_sum($seats);
                $draftrecords = $DB->get_field_sql("SELECT seats FROM {reservations_draft} WHERE (hallid = $filterdata->hallid AND slotstart  = '{$start_time}' AND slotend = '{$slot}' AND date = '{$filterdata->examdate}') OR (hallid = $filterdata->hallid AND '{$start_time}' >= slotstart AND '{$start_time}' < slotend AND date = '{$filterdata->examdate}') OR (hallid = $filterdata->hallid AND '{$slot}' > slotstart AND '{$slot}' < slotend AND date = '{$filterdata->examdate}') ");

                $reservedseats = $totalbookedseats + $draftrecords;

                // Booked seats per day
                $entitybookedseats = $DB->get_fieldset_sql("SELECT seats 
                    FROM {hall_reservations}  
                    WHERE hallid = {$filterdata->hallid} AND examdate = '{$filterdata->examdate}' ");
                
                // Booked draft seats per day
                $entitydraftseats = $DB->get_fieldset_sql("SELECT seats 
                    FROM {reservations_draft}  
                    WHERE hallid = {$filterdata->hallid} AND date = '{$filterdata->examdate}' AND type = '{$filterdata->type}' AND entitycode = '$filterdata->referencecode' ");

                if(empty(array_sum($entitybookedseats) + array_sum($entitydraftseats))) {
                // if ($reservedseats) {                
                    $row['booked'] = false;
                    $row['examname'] = '';
                    $row['totalseats'] = $totalseats;
                    $row['availableseats'] = $totalseats;
                    $row['reservedseats'] = 0;
                    $row['entitiesseats'] = $entityseats - array_sum($entitybookedseats);
                } else {
                    $reservedseats = $totalbookedseats + $draftrecords;
                    $availableseats = ($totalseats-$reservedseats);
                    if($filterdata->typeid == 0) {
                        $sql = "SELECT SUM(seats) FROM {reservations_draft} WHERE entitycode = '$filterdata->referencecode' AND hallid = {$filterdata->hallid} AND (slotstart = '{$start_time}' AND slotend = '{$slot}' AND date = '{$filterdata->examdate}') OR ('{$start_time}' >= slotstart AND '{$start_time}' < slotend AND date = '{$filterdata->examdate}') OR ('{$slot}' > slotstart AND '{$slot}' < slotend AND date = '{$filterdata->examdate}') ";
                        $offeringseats = $DB->get_field_sql($sql);

                        $row['entitiesseats'] = $entityseats - array_sum($entitydraftseats);
                        $row['reservedseats'] = $offeringseats;
                    } else {
                        $totalbookedseats = $DB->get_field_sql("SELECT SUM(seats) FROM {hall_reservations} WHERE (hallid = {$filterdata->hallid} AND slotstart = '{$start_time}' AND slotend = '{$slot}' AND examdate = '{$filterdata->examdate}' AND type = '{$filterdata->type}' AND typeid = '{$filterdata->typeid}') OR 
                    (hallid = {$filterdata->hallid} AND  '{$start_time}' >= slotstart AND '{$start_time}' < slotend AND examdate = '{$filterdata->examdate}'  AND type = '{$filterdata->type}' AND typeid = '{$filterdata->typeid}')  OR 
                    (hallid = {$filterdata->hallid} AND slotend > '{$start_time}' AND slotend <= '{$slot}' AND examdate = '{$filterdata->examdate}' AND type = '{$filterdata->type}'  AND typeid = '{$filterdata->typeid}') ");
                        $row['entitiesseats'] = $entityseats - array_sum($entitybookedseats);
                        $row['reservedseats'] = $totalbookedseats;
                    }
                    if($hallstatus || $totalbookedseats == $totalseats || $totalbookedseats == $entityseats || $row['entitiesseats'] == 0) {
                        $row['booked'] = true;
                        $row['examname'] = '';
                        $row['availableseats'] = $availableseats;
                    }  else {
                        $row['booked'] = false;
                        $row['examname'] = '';
                        $row['availableseats'] = $availableseats;
                    }
                }
                if(!empty($filterdata->search_query)) {
                    if(in_array( trim($filterdata->search_query) , explode(',',date('h,i',$row[start_time] )))) {
                        $data[] = $row;
                    } 
                } else {
                    $data[] = $row;
                }
                $start_time = $slot;
                $slot = $start_time +$due;
            }
        }

        if(!empty($bookedid)) {
            array_unshift($data, $data[$bookedid]);
            unset($data[$bookedid+1]);
            array_splice($data, $data[$bookedid+1], $data[$bookedid+1]);            
        }

        if(!empty($data)) {
            for($i = $stable->start; $i<($stable->start + $stable->length); $i++) {
                $test[] = $data[$i];

                if($i == COUNT($data)-1) {
                    break;
                }
            }            
        } else {
            $test = [];
        }

        $coursesContext = array(
            "hascourses" => $test,
            "nocourses" => $nocourse,
            "totalexams" => COUNT($data),
            "length" => COUNT($data),
            "totalhallseats" => $totalseats,
            "totalentityseats" => $entityseats,
            "type" => $type,
            "entityname" => $entityname,
            "entityid" => $entityid,
        );
        return $coursesContext;
    }
    
    public function add_update_seating($data) {
        global $DB, $USER;
        $record = $DB->insert_record('hall_reservations', $data);
    }
    public function schedule() {
        global $DB, $PAGE, $OUTPUT;
        $examid = optional_param('id', 0, PARAM_INT);
        $systemcontext = context_system::instance();
        $renderer = $PAGE->get_renderer('local_hall');
        $filterparams  = $renderer->get_catalog_schedule(true);

        $filterparams['submitid'] = 'form#filteringform';
        $renderer->global_filter($filterparams);
        echo $halldetails = $renderer->get_catalog_schedule();
        $filterparams['halldetails'] = $halldetails;
        if($examid == 0) {
            $renderer->schedulehalls($filterparams);
        }
    }
    public function schedule_view($stable, $filterdata) {
        global $DB, $PAGE;
        $systemcontext = context_system::instance();
        $PAGE->set_context($systemcontext);
        $selectsql = "SELECT DISTINCT hr.hallid
                        FROM {hall_reservations} AS hr
                        JOIN {hall} h ON h.id = hr.hallid WHERE 1=1 "; 
        $countsql  = "SELECT COUNT(DISTINCT hr.hallid) FROM {hall_reservations} AS hr
                        JOIN {hall} h ON h.id = hr.hallid WHERE 1=1 ";

        if(!empty($filterdata->examid)) {
            $formsql .= " AND hr.typeid = {$filterdata->examid} AND hr.type = 'exam' ";
        }

        if(isset($filterdata->search_query) && trim($filterdata->search_query) != ''){
            $formsql .= " AND h.name LIKE :search";
            $searchparams = array('search' => '%'.trim($filterdata->search_query).'%');
        }else{
            $searchparams = array();
        }
        $filters = [];
        if(!empty($filterdata->halladdress)){
            $formsql .= " AND h.id = ". $filterdata->halladdress;
            $halladdress = $DB->get_field('hall', 'name', ['id' => $filterdata->halladdress]);
        }

        if(!empty($filterdata->city)){
            $formsql .= " AND h.city LIKE '%$filterdata->city%'";
            $city = $this->listofcities($filterdata->city);
        }
        if(!empty($filterdata->buildingname)){
            $formsql .= " AND h.buildingname LIKE '%$filterdata->buildingname%'";
            $buildingname = $filterdata->buildingname;
        }

        $datefilterdata = (array)$filterdata;

        if (!empty($$datefilterdata["datefrom[day]"])) {
            $start_year = $datefilterdata["datefrom[year]"];
            $start_month =$datefilterdata["datefrom[month]"];
            $start_day = $datefilterdata["datefrom[day]"];
            $filter_startdate = mktime(0,0,0,$start_month, $start_day, $start_year);
            $formsql .= " AND h.timecreated >= $filter_startdate ";
        }
        if(!empty($datefilterdata["dateto[day]"])){
            $end_year = $datefilterdata["dateto[year]"];
            $end_month =$datefilterdata["dateto[month]"];
            $end_day = $datefilterdata["dateto[day]"];
            $filter_enddate = mktime(0,0,0,$end_month, $end_day, $end_year)+86399;
            $formsql .= " AND h.timecreated <= $filter_enddate";
        }
    
        $params = array_merge($searchparams);
        $totalhalls = $DB->count_records_sql($countsql.$formsql, $params);
        $formsql .=" GROUP BY hr.id ORDER BY h.id ASC";
        $halls = $DB->get_records_sql($selectsql.$formsql, $params, $stable->start,$stable->length);

        $hallreservation = [];
        foreach($halls as $hall) {
            $hallinfo = [];
            $hallname = $DB->get_field('hall', 'name', ['id' => $hall->hallid]);

            $sql = "SELECT hr.* FROM {hall_reservations} hr WHERE hallid = ". $hall->hallid;

            if(!empty($filterdata->examid)) {
                $formsql = " AND hr.typeid = {$filterdata->examid} AND hr.type = 'exam' ";
            } else {
                $formsql = '';
            }
            // exam start and end date filter
            $datefilterdata = (array)$filterdata;
            if (!empty($datefilterdata["datefrom[day]"])) {
             
                $start_year = $datefilterdata["datefrom[year]"];
                $start_month =$datefilterdata["datefrom[month]"];
                $start_day = $datefilterdata["datefrom[day]"];
                $filter_startdate = mktime(0,0,0,$start_month, $start_day, $start_year);
                $formsql .= " AND UNIX_TIMESTAMP(hr.examdate) >= $filter_startdate ";
               
            }
            if(!empty($datefilterdata["dateto[day]"])){
                $end_year = $datefilterdata["dateto[year]"];
                $end_month =$datefilterdata["dateto[month]"];
                $end_day = $datefilterdata["dateto[day]"];
                $filter_enddate = mktime(0,0,0,$end_month, $end_day, $end_year)+86399;
                $formsql .= " AND UNIX_TIMESTAMP(hr.examdate) <= $filter_enddate";
            }
            $formsql .= " GROUP BY examdate ";
           
            $reservations = $DB->get_records_sql($sql.$formsql);

            foreach($reservations as $reservation) {
                $hallslist = [];
                $hallslist["id"] = $reservation->hallid;
                $hallslist["hall"] = $DB->get_field('hall', 'name', ['id'=>$reservation->hallid]);
                $hallslist["date"] = date('Y-m-d', strtotime($reservation->examdate));
                $hallslist["examdate"] = strtotime($reservation->examdate);
                switch($reservation->type){
                    case 'tprogram':
                        $hallslist["type"] = get_string('pluginname', 'local_trainingprogram');
                        $duration = $DB->get_field('tp_offerings', 'duration', ['id' => $reservation->typeid]);
                        break;
                    case 'event':
                        $hallslist["type"] = get_string('event', 'local_hall');
                        $duration = $DB->get_field('local_events', 'eventduration', ['id' => $reservation->typeid]);
                        break;
                    case 'exam':
                        $hallslist["type"] = get_string('exam', 'local_exams');
                        $duration = 0;
                        break;
                    case 'questionbank':
                        $hallslist["type"] = get_string('questionbank', 'local_questionbank');
                        $duration = $DB->get_field('local_questionbank', 'duration', ['id' => $reservation->typeid]);
                        break;                    
                }
                $hallslist["duration"] = $duration/60;
                $hallinfo[] = $hallslist;
            }
            $hallreservation[] = array('hallname' => $hallname, 'results' => $hallinfo);
        }
        $coursesContext = array(
            "hascourses" => $hallreservation,
            "nocourses" => $nocourse,
            "totalhalls" => $totalhalls,
            "length" => count($hallslist),
            "halladdress" => $halladdress,
            "city" => $city,
            "buildingname" => format_string($buildingname),
        );
        return $coursesContext;
    }
    public function hallreservationsdetails($stable, $filtervalues) {
        global $DB, $PAGE, $OUTPUT;
        $reservationdate = date('Y-m-d', $filtervalues->halldate);
        $sql = "SELECT id, FROM_UNIXTIME(slotstart, '%h:%i %p'), slotstart as slotstart, slotend as slotend FROM {hall_reservations} WHERE hallid = {$filtervalues->hallid} AND examdate = '{$reservationdate}'";

        if($filtervalues->examid > 0) {
            $formssql = " AND typeid = {$filtervalues->examid} AND type = 'exam' ";
        } else {
            $formssql = " ";
        }

        $timings = $DB->get_records_sql($sql.$formssql);
        foreach($timings as $timing) {
            $row = [];
            $row[reservationdate] = date('d/m/Y', $filtervalues->halldate);

            $endtimemeridian = gmdate('a',mktime(0, 0, $timing->slotstart));
            $lang = current_language();
            if($lang == 'ar') {
                $endtmeridian = ($endtimemeridian == 'am')? 'صباحا':'مساءً';
                
            } else {
                $endtmeridian = ($endtimemeridian == 'am')? 'AM':'PM';
            }

            $row[slotstart] = date('h:i', mktime(0, 0, $timing->slotstart)). ' ' .$endtmeridian;
            
            $endtimemeridian = gmdate('a',mktime(0, 0, $timing->slotend));

            if($lang == 'ar') {
                $endtmeridian = ($endtimemeridian == 'am')? 'صباحا':'مساءً';
                
            } else {
                $endtmeridian = ($endtimemeridian == 'am')? 'AM':'PM';
            }
            $row[slotend] = date('h:i', mktime(0, 0, $timing->slotend)). ' ' .$endtmeridian;
            $row[totalhallseats] = $DB->get_field('hall', 'seatingcapacity', ['id' => $filtervalues->hallid]);


            $sql = "SELECT SUM(seats) FROM {hall_reservations} WHERE hallid = $filtervalues->hallid AND slotstart = $timing->slotstart AND slotend = $timing->slotend AND examdate = '{$reservationdate}' ";

            if($filtervalues->examid > 0) {
                $formssqls .= " AND typeid = {$filtervalues->examid} AND type = 'exam' ";
            } else {
                $formssqls .= " ";
            }

            $reservedseats = $DB->get_field_sql($sql.$formssqls);


            $totalsql = "SELECT SUM(seats) 
                      FROM {hall_reservations} 
                     WHERE hallid = $filtervalues->hallid AND examdate = '{$reservationdate}' ";
            $totalseats = $DB->get_field_sql($totalsql);

            $row[reservedseats] = $reservedseats;
            $row[availableseats] = $row[totalhallseats] - $totalseats;
            $sql = "SELECT * FROM {hall_reservations} WHERE hallid = $filtervalues->hallid AND slotstart = $timing->slotstart AND slotend = $timing->slotend AND examdate = '{$reservationdate}'";

            if($filtervalues->examid > 0) {
                $formssql = " AND typeid = {$filtervalues->examid} AND type = 'exam' ";
            } else {
                $formssql = " ";
            }


            $records = $DB->get_records_sql($sql.$formssql);
                $hh = [];
            foreach($records as $record) {
                $data = [];
                $data['id'] = $record->id; 
                switch($record->type){
                    case 'tprogram':
                        $data['type'] = get_string('pluginname', 'local_trainingprogram');
                        $entity = $DB->get_record_sql("SELECT name, namearabic as arabicname, ltp.code FROM {local_trainingprogram} ltp JOIN {tp_offerings} tpo ON tpo.trainingid = ltp.id WHERE tpo.id = $record->typeid");
                        $data['code'] = $entity->code;
                        break;
                    case 'event':
                        $data['type'] = get_string('event', 'local_hall');
                        $entity = $DB->get_record_sql("SELECT title  as name, titlearabic as arabicname, code FROM {local_events} WHERE id = ".$record->typeid);
                        $data['code'] = $entity->code;
                        break;
                    case 'exam':
                        $data['type'] = get_string('exam', 'local_exams');
                        $entity = $DB->get_record_sql("SELECT exam as name, examnamearabic as arabicname, code FROM {local_exams} where id =". $record->typeid);
                        $data['code'] = $entity->code;
                        break;
                    case 'questionbank':
                        $data["type"] = get_string('questionbank', 'local_questionbank');
                        $entity = $DB->get_record_sql("SELECT workshopname AS name, workshopname as arabicname FROM {local_questionbank} WHERE id =". $record->typeid);
                        $data['code'] = '--';
                        break;                        
                }

                if($lang == 'ar') {
                    $data['modulename'] = $entity->arabicname;                    
                } else {
                    $data['modulename'] = $entity->name;
                }

                $data['bookedseats'] = $record->seats;
                $data['date'] = date('Y-m-d', strtotime($record->examdate));

                $endtimemeridian = gmdate('a',mktime(0, 0, $record->slotstart));
                if($lang == 'ar') {
                    $endtmeridian = ($endtimemeridian == 'am')? 'صباحا':'مساءً';
                    
                } else {
                    $endtmeridian = ($endtimemeridian == 'am')? 'AM':'PM';
                }

                $data['entityslotstart'] = date('h:i', mktime(0, 0, $record->slotstart)).' '.$endtmeridian;

                $endtimemeridian = gmdate('a',mktime(0, 0, $record->slotend));

                if($lang == 'ar') {
                    $endtmeridian = ($endtimemeridian == 'am')? 'صباحا':'مساءً';
                    
                } else {
                    $endtmeridian = ($endtimemeridian == 'am')? 'AM':'PM';
                }


                $data['entityslotend'] = date('h:i', mktime(0, 0, $record->slotend)).' '.$endtmeridian;
                $hh[] = $data;
            }
            $row['entity'] = $data['type'];
            $test['rr'] = $row;
            $test['aa'] = $hh;
            $hallinfo[] = $test;
        }
        for($i = $stable->start; $i<($stable->start + $stable->length); $i++) {
            $hallsrecords[] = $hallinfo[$i];
            if($i == COUNT($hallinfo)-1) {
                break;
            }
        }
        if(empty($data)) {
            $hallsrecords = [];
        }
        $coursesContext = array(
            "hascourses" => $hallsrecords,
            "nocourses" => $nocourse,
            "totalhalls" => count($data),
            "length" => count($data),
        );
        return $coursesContext;
    }
    public function hallreservations($hallid, $halldate, $examid) {
        global $DB, $PAGE, $OUTPUT;
        $systemcontext = context_system::instance();
        $renderer = $PAGE->get_renderer('local_hall');
        echo $halldetails = $renderer->get_catalog_hallreservations($hallid, $halldate, $examid);

        $filterparams['submitid'] = 'form#filteringform';
        $renderer->global_filter($filterparams);
        $filterparams['halldetails'] = $halldetails;
        $renderer->schedulehallsdetails($filterparams);
    }

    //Vinod- Hall fake block for communication officer - Starts//
      
    public function hallfakeblock () {
        global $PAGE;
        $bc = new block_contents();
        $bc->title = get_string('hall','local_hall');
        $bc->attributes['class'] = 'halls_fakeblock';
        $bc->content = $this->halls_block();
        $PAGE->blocks->add_fake_block($bc, 'content');
    }
    public function halls_block() {
        global $DB, $PAGE, $OUTPUT, $USER;
        $systemcontext = context_system::instance();
        $renderer = $PAGE->get_renderer('local_hall');
        $filterparams = $renderer->all_halls_block(true);
        $filterparams['submitid'] = 'form#filteringform';
        $filterparams['widthclass'] = 'col-md-6';
        $filterparams['placeholder'] = get_string('searchhall','local_hall');
        $globalinput=$renderer->global_filter($filterparams);
        $block = $renderer->all_halls_block();
        $filterparams['hall_block_view'] = $block;
        $filterparams['globalinput'] = $globalinput;
        return $renderer->listofhalls_block_data($filterparams);
    }
    public function get_listof_halls_for_block($stable, $filterdata) {
        global $DB, $CFG, $USER, $PAGE;
        $systemcontext = context_system::instance();
        $PAGE->set_context($systemcontext);

        $selectsql = "SELECT h.id, h.name, h.*
                        FROM {hall} AS h WHERE availability = 1 "; 
        $countsql  = "SELECT COUNT(h.id) FROM {hall} AS h  WHERE availability = 1 ";

        if(isset($filterdata->search_query) && trim($filterdata->search_query) != ''){
            $formsql .= " AND h.name LIKE :search";
            $searchparams = array('search' => '%'.trim($filterdata->search_query).'%');
        }else{
            $searchparams = array();
        }
        if(!empty($filterdata->type)){
            $formsql .= " AND h.type LIKE '%$filterdata->type%'";
        }
        if(!empty($filterdata->roomshape)){
            $formsql .= " AND h.roomshape LIKE '%$filterdata->roomshape%'";
        }

        $datefilterdata = (array)$filterdata;

        if ($datefilterdata["datefrom[day]"]) {

            $start_year = $datefilterdata["datefrom[year]"];
            $start_month =$datefilterdata["datefrom[month]"];
            $start_day = $datefilterdata["datefrom[day]"];
            $filter_startdate = mktime(0,0,0,$start_month, $start_day, $start_year);
            $formsql .= " AND h.timecreated >= $filter_startdate ";
        }
        if(!empty($datefilterdata["dateto[day]"])){

            $end_year = $datefilterdata["dateto[year]"];
            $end_month =$datefilterdata["dateto[month]"];
            $end_day = $datefilterdata["dateto[day]"];
            $filter_enddate = mktime(0,0,0,$end_month, $end_day, $end_year)+86399;
            $formsql .= " AND h.timecreated <= $filter_enddate";

        }
  
        $params = array_merge($searchparams);
        $totalhalls = $DB->count_records_sql($countsql.$formsql, $params);
        $formsql .=" ORDER BY h.id DESC";
        $halls = $DB->get_records_sql($selectsql.$formsql, $params, $stable->start,$stable->length);
        $hallslist = array();
        $count = 0;
        $types = ['1' =>get_string('exam', 'local_hall'), '2' => get_string('trainingprogram', 'local_hall'), '3' => get_string('event', 'local_hall')];
        $roomshape = ['1' => get_string('circle','local_hall'),'2'=>get_string('rectangle','local_hall'),'3'=>get_string('square','local_hall')];
        $availability = ['1' => get_string('enabled','local_hall'),'2'=>get_string('notenabled','local_hall')];
        foreach($halls as $hall) {
            $hallslist[$count]["id"] = $hall->id;
            $hallslist[$count]["name"] = $hall->name;
            $hallslist[$count]["type"] = !empty($types[$hall->type]) ? $types[$hall->type] : '--';
            $hallslist[$count]["city"] = $this->listofcities($hall->city);
            $hallslist[$count]["buildingname"] = format_string($hall->buildingname);
            $hallslist[$count]["seatingcapacity"] = $hall->seatingcapacity;

            if (filter_var($hall->maplocation, FILTER_VALIDATE_URL)) {
                $hallslist[$count]["locationstatus"] = true;
            } else {
                $hallslist[$count]["locationstatus"] = false;
            }

            $hallslist[$count]["maplocation"] = $hall->maplocation;
            $hallslist[$count]["roomshape"] = !empty($roomshape[$hall->roomshape]) ? $roomshape[$hall->roomshape] : '--';
            $hallslist[$count]["availability"] = !empty($availability[$hall->availability]) ? $availability[$hall->availability] : '--';
            $hallslist[$count]["equipmentavailable"] = $this->equipmentavailable($hall->equipmentavailable);
            $hallslist[$count]["description"] = format_text($hall->description, FORMAT_HTML);
            $hallslist[$count]["actionsview"] = (!is_siteadmin() && has_capability('local/organization:manage_hall_manager', $systemcontext)) ?  true : false;
            $hallslist[$count]["viewhallurl"] = $CFG->wwwroot;

            $count++;
        }
        $systemcontext = context_system::instance();
        if (is_siteadmin() ||  has_capability('local/organization:manage_communication_officer', $systemcontext) ||  has_capability('local/organization:manage_hall_manager', $systemcontext)) {
            $managehall = true;
        } else {
            $managehall = false;
        }   
        $viewmoreurl=$CFG->wwwroot.'/local/hall/index.php';  
        $coursesContext = array(
            "hascourses" => $hallslist,
            "nocourses" => $nocourse,
            "totalhalls" => $totalhalls,
            "length" => count($hallslist),
            "managehall" => $managehall,
            "viewmoreurl" =>$viewmoreurl,
        );
        return $coursesContext;
    }
    //Vinod- Hall fake block for communication officer - Ends//

    public static function get_hallselect() {
        global $DB;
        return $DB->get_records_sql_menu("SELECT id, name FROM {hall} WHERE availability=1");
    }
    public function get_listof_currenthalls($buildingname=null,$city=null, $halllocation=null, $query=null, $fields=array()) {
        global $DB;

        $fields = array('name');
                $likesql = array();
                $i = 0;
                foreach ($fields as $field) {
                    $i++;
                    $likesql[] = $DB->sql_like($field, ":queryparam$i", false);
                    $sqlparams["queryparam$i"] = "%$query%";
                }
                $sqlfields = implode(" OR ", $likesql);
                $concatsql = " AND ($sqlfields) ";
        $sqlquery = "SELECT id, name AS fullname FROM {hall} WHERE availability = 1 AND type = 2 ";
        $sql='';
        $sql .= $concatsql;
        if(!empty($city)) {
            $arr = $this->listofcities();
            $key = array_search ($city, $arr);
            $sql .= " AND city = '{$key}' ";
        }

        if(!empty($buildingname)) {
           $sql .= " AND buildingname = '{$buildingname}' ";  
        }

       if(!empty($halllocation)) {
           $sql .= " AND LOWER(halllocation) IN (\"{$halllocation}\") ";  
        }
        $data = $DB->get_records_sql($sqlquery. $sql, $sqlparams);

        return $data;
    }
    public function get_buildingname($type=false, $city=false, $query=null, $fields=array()) {
        global $DB;
        switch($type) {
            case 'buildingname':

                if($query) {
                    $fields = array('buildingname');
                    $likesql = array();
                    $i = 0;
                    foreach ($fields as $field) {
                        $i++;
                        $likesql[] = $DB->sql_like($field, ":queryparam$i", false);
                        $sqlparams["queryparam$i"] = "%$query%";
                    }
                    $sqlfields = implode(" OR ", $likesql);
                    $concatsql = " AND ($sqlfields) ";                    
                } else {
                    $concatsql = " ";
                }
                
                if( $city ) {
                   $city = " AND city = '{$city}' ";
                }

                $sql_query = "SELECT buildingname AS id, buildingname AS fullname FROM {hall} WHERE availability = 1 $city $concatsql GROUP BY buildingname ";


                $data= $DB->get_records_sql($sql_query, $sqlparams);
            break;
            case 'city':

                $cities = $this->hall_cities();
                foreach($cities  as $city) {
                
                    $hallcity = new stdClass();
                    $hallcity->id = $city['id'];
                    $hallcity->fullname = get_string($city['code'], 'local_hall');
                    $data[] = $hallcity;

                }

            break;            
        }
        
        return $data;
    }

    public function get_examhalls($query=null, $fields=array()) {
        global $DB;
            if($query) {
                $fields = array('name');
                $likesql = array();
                $i = 0;
                foreach ($fields as $field) {
                    $i++;
                    $likesql[] = $DB->sql_like($field, ":queryparam$i", false);
                    $sqlparams["queryparam$i"] = "%$query%";
                }
                $sqlfields = implode(" OR ", $likesql);
                $concatsql = " AND ($sqlfields) ";                    
            } else {
                $concatsql = " ";
            }
            $sql = "SELECT id, name AS fullname 
                            FROM {hall} 
                           WHERE type = 1 AND availability = 1 $concatsql ";
            $data= $DB->get_records_sql($sql, $sqlparams);
        
        return $data;
    }

    public function remove_reservation($sesskey, $type) {
        global $DB;
        $DB->delete_records('reservations_draft', ['entitycode' => $sesskey, 'type' => $type]);
        return true;
    }
    public function remove_hallreservations($entityid, $type) {
        global $DB;
        $DB->delete_records('hall_reservations', ['typeid' => $entityid, 'type' => $type]);
        
        return true;
    }
    public function hall_cities() {
        global $DB;

        $cities = [
            ['id'=> 1, 'city' => 'Buqayq', 'code' => 'C0202', 'region' => 'Eastern Region'],
            ['id'=> 2, 'city' => 'Al-Ahsa Governorate', 'code' => 'C_000001', 'region' => 'Eastern Region'],
            ['id'=> 3, 'city' => 'Al Jubail', 'code' => 'C_0010', 'region' => 'Eastern Region'],
            ['id'=> 4, 'city' => 'Al-Jafr', 'code' => 'C_0002', 'region' => 'Eastern Region'],
            ['id'=> 5, 'city' => 'El Khabar', 'code' => 'C_0007', 'region' => 'Eastern Region'],
            ['id'=> 6, 'city' => 'Khafji', 'code' => 'C_0012', 'region' => 'Eastern Region'],
            ['id'=> 7, 'city' => 'Dammam', 'code' => 'C_0001', 'region' => 'Eastern Region'],
            ['id'=> 8, 'city' => 'Dhahran', 'code' => 'C_0006', 'region' => 'Eastern Region'],
            ['id'=> 9, 'city' => 'Madinatal Umran', 'code' => 'C_0004', 'region' => 'Eastern Region'],
            ['id'=> 10, 'city' => 'Al Awamiyah', 'code' => 'citycode_001', 'region' => 'Eastern Region'],
            ['id'=> 11, 'city' => 'Laayoune', 'code' => 'citycode_002', 'region' => 'Eastern Region'],
            ['id'=> 12, 'city' => 'Al Qatif', 'code' => 'C0203', 'region' => 'Eastern Region'],
            ['id'=> 13, 'city' => 'Al Qaisumah', 'code' => 'C0201', 'region' => 'Eastern Region'],
            ['id'=> 14, 'city' => 'Al Mubarraz', 'code' => 'C_0003', 'region' => 'Eastern Region'],
            ['id'=> 15, 'city' => 'Nairyah', 'code' => 'C_0011', 'region' => 'Eastern Region'],
            ['id'=> 16, 'city' => 'Al Hofuf', 'code' => 'C_0005', 'region' => 'Eastern Region'],
            ['id'=> 17, 'city' => 'Buqayq', 'code' => 'C_0008', 'region' => 'Eastern Region'],
            ['id'=> 18, 'city' => 'Tarout', 'code' => 'citycode_003', 'region' => 'Eastern Region'],
            ['id'=> 19, 'city' => 'Hafar Al Batin', 'code' => 'C_0013', 'region' => 'Eastern Region'],
            ['id'=> 20, 'city' => 'Ras Tanura', 'code' => 'C_0009', 'region' => 'Eastern Region'],
            ['id'=> 21, 'city' => 'Saihat', 'code' => 'citycode_004', 'region' => 'Eastern Region'],
            ['id'=> 22, 'city' => 'Safwa', 'code' => 'QaryatAlUlya', 'region' => 'Al Qatif'],
            ['id'=> 23, 'city' => 'Qaryat Al Ulya', 'code' => 'C0204', 'region' => 'Eastern Region'],
            ['id'=> 24, 'city' => 'Al Qatif', 'code' => 'C_000003', 'region' => 'Eastern Region'],
            ['id'=> 25, 'city' => 'Al Bahah', 'code' => 'C_00000002', 'region' => 'Al , Baha Area'],
            ['id'=> 26, 'city' => 'Hajrah', 'code' => 'C_0019', 'region' => 'Al , Baha Area'],
            ['id'=> 27, 'city' => 'North', 'code' => 'C_0014', 'region' => 'Al , Baha Area'],
            ['id'=> 28, 'city' => 'Agate', 'code' => 'C_0016', 'region' => 'Al , Baha Area'],
            ['id'=> 29, 'city' => 'Al Qara', 'code' => 'C0208', 'region' => 'Al , Baha Area'],
            ['id'=> 30, 'city' => 'Al Makhwah', 'code' => 'C_0017', 'region' => 'Al , Baha Area'],
            ['id'=> 31, 'city' => 'Almandaq', 'code' => 'C_0015', 'region' => 'Al , Baha Area'],
            ['id'=> 32, 'city' => 'Baljurashi', 'code' => 'C0209', 'region' => 'Al , Baha Area'],
            ['id'=> 33, 'city' => 'Baljurashi', 'code' => 'C_0024', 'region' => 'Al , Baha Area'],
            ['id'=> 34, 'city' => 'Bani Hasan', 'code' => 'C_0022', 'region' => 'Al , Baha Area'],
            ['id'=> 35, 'city' => 'Bani Kabir', 'code' => 'C_0021', 'region' => 'Al , Baha Area'],
            ['id'=> 36, 'city' => 'Ghamid Az Zinad', 'code' => 'C_0023', 'region' => 'Al , Baha Area'],
            ['id'=> 37, 'city' => 'Qilwah', 'code' => 'C_0018', 'region' => 'Al , Baha Area'],
            ['id'=> 38, 'city' => 'Qilwah', 'code' => 'citycode_005', 'region' => 'Al , Baha Area'],
            ['id'=> 39, 'city' => 'Mashuqah', 'code' => 'C_0020', 'region' => 'Al , Baha Area'],
            ['id'=> 40, 'city' => 'Abu Ajram', 'code' => 'C_0034', 'region' => 'Al , Baha Area'],
            ['id'=> 41, 'city' => 'Al Jowf Province', 'code' => 'C_0025', 'region' => 'Al , Baha Area'],
            ['id'=> 42, 'city' => 'Al Hadithah', 'code' => 'C_0033', 'region' => 'Al , Baha Area'],
            ['id'=> 43, 'city' => 'Al Lsawiyah', 'code' => 'C_0031', 'region' => 'Al , Baha Area'],
            ['id'=> 44, 'city' => 'Al Qurayyat', 'code' => 'C_0026', 'region' => 'Al , Baha Area'],
            ['id'=> 45, 'city' => 'An Nasifah', 'code' => 'C_0032', 'region' => 'Al , Baha Area'],
            ['id'=> 46, 'city' => 'Dumah Al Jandal', 'code' => 'C_0028', 'region' => 'Al , Baha Area'],
            ['id'=> 47, 'city' => 'Zallum', 'code' => 'C_0030', 'region' => 'Al , Baha Area'],
            ['id'=> 48, 'city' => 'Sakaka', 'code' => 'C0207', 'region' => 'Al , Baha Area'],
            ['id'=> 49, 'city' => 'Suwayr', 'code' => 'C_0029', 'region' => 'Al , Baha Area'],
            ['id'=> 50, 'city' => 'Tubarjal', 'code' => 'C_0027', 'region' => 'Al , Baha Area'],
            ['id'=> 51, 'city' => 'Al Uwayqilah', 'code' => 'citycode_006', 'region' => 'Northern Border Area'],
            ['id'=> 52, 'city' => 'Rafha', 'code' => 'C_0036', 'region' => 'Northern Border Area'],
            ['id'=> 53, 'city' => 'Rafha', 'code' => 'C0206', 'region' => 'Northern Border Area'],
            ['id'=> 54, 'city' => 'Turaif', 'code' => 'C_0037', 'region' => 'Northern Border Area'],
            ['id'=> 55, 'city' => 'Arar', 'code' => 'C_0035', 'region' => 'Northern Border Area'],
            ['id'=> 56, 'city' => 'Layla', 'code' => 'C_0069', 'region' => 'Riyadh Region'],
            ['id'=> 57, 'city' => 'Al Bijadiyah', 'code' => 'C_0059', 'region' => 'Riyadh Region'],
            ['id'=> 58, 'city' => 'Al Hariq', 'code' => 'C_0065', 'region' => 'Riyadh Region'],
            ['id'=> 59, 'city' => 'Hutat Al Hilwah', 'code' => 'C_0067', 'region' => 'Riyadh Region'],
            ['id'=> 60, 'city' => 'Al-Kharj', 'code' => 'C_0061', 'region' => 'Riyadh Region'],
            ['id'=> 61, 'city' => 'Ad Diriyah', 'code' => 'C_0052', 'region' => 'Riyadh Region'],
            ['id'=> 62, 'city' => 'Al Duwadimi', 'code' => 'C_0058', 'region' => 'Riyadh Region'],
            ['id'=> 63, 'city' => 'Ar Ruwaidhah', 'code' => 'C_0064', 'region' => 'Riyadh Region'],
            ['id'=> 64, 'city' => 'Riyadh', 'code' => 'C_0038', 'region' => 'Riyadh Region'],
            ['id'=> 65, 'city' => 'Ar Rayn', 'code' => 'C_0068', 'region' => 'Riyadh Region'],
            ['id'=> 66, 'city' => 'Az Zulfi', 'code' => 'C_0040', 'region' => 'Riyadh Region'],
            ['id'=> 67, 'city' => 'As Sulayyil', 'code' => 'C_0070', 'region' => 'Riyadh Region'],
            ['id'=> 68, 'city' => 'Al Uyaynah', 'code' => 'C_0054', 'region' => 'Riyadh Region'],
            ['id'=> 69, 'city' => 'Al Ghat', 'code' => 'C_0041', 'region' => 'Riyadh Region'],
            ['id'=> 70, 'city' => 'Al Qasab', 'code' => 'C_0053', 'region' => 'Riyadh Region'],
            ['id'=> 71, 'city' => 'Al Qassim Province', 'code' => 'C0205', 'region' => 'Riyadh Region'],
            ['id'=> 72, 'city' => 'Al Quwaiiyah', 'code' => 'C_0063', 'region' => 'Riyadh Region'],
            ['id'=> 73, 'city' => 'Al Majma\'ah', 'code' => 'C_0042', 'region' => 'Riyadh Region'],
            ['id'=> 74, 'city' => 'Al-Muzahmiya', 'code' => 'C_0057', 'region' => 'Riyadh Region'],
            ['id'=> 75, 'city' => 'Al Hayathem', 'code' => 'C_0062', 'region' => 'Riyadh Region'],
            ['id'=> 76, 'city' => 'Tumair', 'code' => 'C_0044', 'region' => 'Riyadh Region'],
            ['id'=> 77, 'city' => 'Thadiq', 'code' => 'C_0048', 'region' => 'Riyadh Region'],
            ['id'=> 78, 'city' => 'Jalajel', 'code' => 'C_0045', 'region' => 'Riyadh Region'],
            ['id'=> 79, 'city' => 'Huraymila', 'code' => 'C_0051', 'region' => 'Riyadh Region'],
            ['id'=> 80, 'city' => 'Howtat Bani Tamim', 'code' => 'C_0066', 'region' => 'Riyadh Region'],
            ['id'=> 81, 'city' => 'Hautat Sudair', 'code' => 'C_0047', 'region' => 'Riyadh Region'],
            ['id'=> 82, 'city' => 'Rumah', 'code' => 'C_0043', 'region' => 'Riyadh Region'],
            ['id'=> 83, 'city' => 'Raudat Sudair', 'code' => 'C_0046', 'region' => 'Riyadh Region'],
            ['id'=> 84, 'city' => 'Sajir', 'code' => 'C_0050', 'region' => 'Riyadh Region'],
            ['id'=> 85, 'city' => 'Shaqra', 'code' => 'C_0049', 'region' => 'Riyadh Region'],
            ['id'=> 86, 'city' => 'Dhurma', 'code' => 'C_0056', 'region' => 'Riyadh Region'],
            ['id'=> 87, 'city' => 'Afif', 'code' => 'C_0060', 'region' => 'Riyadh Region'],
            ['id'=> 88, 'city' => 'Marat', 'code' => 'C_0055', 'region' => 'Riyadh Region'],
            ['id'=> 89, 'city' => 'Wadi ad-Dawasir', 'code' => 'C_0039', 'region' => 'Riyadh Region'],
            ['id'=> 90, 'city' => 'Abanat', 'code' => 'C_0098', 'region' => 'Qassim Region'],
            ['id'=> 91, 'city' => 'Al Asyah', 'code' => 'C_0077', 'region' => 'Qassim Region'],
            ['id'=> 92, 'city' => 'Al Asyah', 'code' => 'C_0085', 'region' => 'Qassim Region'],
            ['id'=> 93, 'city' => 'Al Basr', 'code' => 'C_0083', 'region' => 'Qassim Region'],
            ['id'=> 94, 'city' => 'Al Bateen', 'code' => 'C_0093', 'region' => 'Qassim Region'],
            ['id'=> 95, 'city' => 'Al Bukayriyah', 'code' => 'C_0075', 'region' => 'Qassim Region'],
            ['id'=> 96, 'city' => 'Al Khabra', 'code' => 'C_0071', 'region' => 'Qassim Region'],
            ['id'=> 97, 'city' => 'Public medical center in Al Khabra', 'code' => 'C_0082', 'region' => 'Qassim Region'],
            ['id'=> 98, 'city' => 'Ad Dilaymiyah', 'code' => 'C_0097', 'region' => 'Qassim Region'],
            ['id'=> 99, 'city' => 'Ar Rass', 'code' => 'C_0087', 'region' => 'Qassim Region'],
            ['id'=> 100, 'city' => 'Ash Shimasiyah', 'code' => 'C_0074', 'region' => 'Qassim Region'],
            ['id'=> 101, 'city' => 'Zahiri', 'code' => 'C_0099', 'region' => 'Qassim Region'],
            ['id'=> 102, 'city' => 'Al Uqlah', 'code' => 'C0199', 'region' => 'Qassim Region'],
            ['id'=> 103, 'city' => 'Al Ammariyah', 'code' => 'C_0094', 'region' => 'Qassim Region'],
            ['id'=> 104, 'city' => 'Al Fawarah', 'code' => 'C_0096', 'region' => 'Qassim Region'],
            ['id'=> 105, 'city' => 'Al-Fuwayliq', 'code' => 'C_0089', 'region' => 'Qassim Region'],
            ['id'=> 106, 'city' => 'Qusaiba', 'code' => 'C_0092', 'region' => 'Qassim Region'],
            ['id'=> 107, 'city' => 'Al Quwarah', 'code' => 'C_0088', 'region' => 'Qassim Region'],
            ['id'=> 108, 'city' => 'Al Midhnab', 'code' => 'C_0086', 'region' => 'Qassim Region'],
            ['id'=> 109, 'city' => 'An Nabhaniyah', 'code' => 'C_0081', 'region' => 'Qassim Region'],
            ['id'=> 110, 'city' => 'Buraydah', 'code' => 'C_0073', 'region' => 'Qassim Region'],
            ['id'=> 111, 'city' => 'Duhknah', 'code' => 'C_0079', 'region' => 'Qassim Region'],
            ['id'=> 112, 'city' => 'Riyadh Al Khabra', 'code' => 'C_0084', 'region' => 'Qassim Region'],
            ['id'=> 113, 'city' => 'Shari', 'code' => 'C_0095', 'region' => 'Qassim Region'],
            ['id'=> 114, 'city' => 'Dariyah', 'code' => 'C_0076', 'region' => 'Qassim Region'],
            ['id'=> 115, 'city' => 'Uglat Asugour', 'code' => 'C_0080', 'region' => 'Qassim Region'],
            ['id'=> 116, 'city' => 'Unayzah', 'code' => 'C_0078', 'region' => 'Qassim Region'],
            ['id'=> 117, 'city' => 'Uyun Al Jawa', 'code' => 'C0200', 'region' => 'Qassim Region'],
            ['id'=> 118, 'city' => 'Uyun Al Jawa', 'code' => 'C_0072', 'region' => 'Qassim Region'],
            ['id'=> 119, 'city' => 'Dome', 'code' => 'C_0091', 'region' => 'Qassim Region'],
            ['id'=> 120, 'city' => 'Qasribn \'Aqil', 'code' => 'C_0090', 'region' => 'Qassim Region'],
            ['id'=> 121, 'city' => 'Al Henakiyah', 'code' => 'C_0102', 'region' => 'Medina Area'],
            ['id'=> 122, 'city' => 'AlUla', 'code' => 'C_0100', 'region' => 'Medina Area'],
            ['id'=> 123, 'city' => 'Medina', 'code' => 'C_0106', 'region' => 'Medina Area'],
            ['id'=> 124, 'city' => 'al muhdaj', 'code' => 'C_0105', 'region' => 'Medina Area'],
            ['id'=> 125, 'city' => 'Badr', 'code' => 'C_0104', 'region' => 'Medina Area'],
            ['id'=> 126, 'city' => 'Khaybar', 'code' => 'C_0101', 'region' => 'Medina Area'],
            ['id'=> 127, 'city' => 'Mahd adh Dhahab', 'code' => 'C0197', 'region' => 'Medina Area'],
            ['id'=> 128, 'city' => 'Yanbu', 'code' => 'C0198', 'region' => 'Medina Area'],
            ['id'=> 129, 'city' => 'Yambu 2', 'code' => 'C_0103', 'region' => 'Medina Area'],
            ['id'=> 130, 'city' => 'Shuwaq', 'code' => 'C_0107', 'region' => 'Tabuk Region'],
            ['id'=> 131, 'city' => 'Umluj', 'code' => 'C_0116', 'region' => 'Tabuk Region'],
            ['id'=> 132, 'city' => 'Al Bad', 'code' => 'C_0108', 'region' => 'Tabuk Region'],
            ['id'=> 133, 'city' => 'Az Zaytah', 'code' => 'citycode_007', 'region' => 'Tabuk Region'],
            ['id'=> 134, 'city' => 'Al Shabhah', 'code' => 'C_0109', 'region' => 'Tabuk Region'],
            ['id'=> 135, 'city' => 'Al Qalibah', 'code' => 'C_0110', 'region' => 'Tabuk Region'],
            ['id'=> 136, 'city' => 'Al Wajh', 'code' => 'C_0115', 'region' => 'Tabuk Region'],
            ['id'=> 137, 'city' => 'Bada', 'code' => 'C_0111', 'region' => 'Tabuk Region'],
            ['id'=> 138, 'city' => 'Tabuk', 'code' => 'C0210', 'region' => 'Tabuk Region'],
            ['id'=> 139, 'city' => 'Tayma', 'code' => 'C_0114', 'region' => 'Tabuk Region'],
            ['id'=> 140, 'city' => 'Haql', 'code' => 'C_0112', 'region' => 'Tabuk Region'],
            ['id'=> 141, 'city' => 'Duba', 'code' => 'C_0113', 'region' => 'Tabuk Region'],
            ['id'=> 142, 'city' => 'Duba', 'code' => 'C0196', 'region' => 'Tabuk Region'],
            ['id'=> 143, 'city' => 'Abu Arish', 'code' => 'C_0121', 'region' => 'Jazan Region'],
            ['id'=> 144, 'city' => 'Ahad Al Masarihah', 'code' => 'C_0122', 'region' => 'Jazan Region'],
            ['id'=> 145, 'city' => 'Al Harth', 'code' => 'C_0139', 'region' => 'Jazan Region'],
            ['id'=> 146, 'city' => 'Al Haqu', 'code' => 'C_0124', 'region' => 'Jazan Region'],
            ['id'=> 147, 'city' => 'Banu al-Hakam', 'code' => 'C_0125', 'region' => 'Jazan Region'],
            ['id'=> 148, 'city' => 'Ad Dair', 'code' => 'C0194', 'region' => 'Jazan Region'],
            ['id'=> 149, 'city' => 'Al Dayer-Bani Malik', 'code' => 'C_0126', 'region' => 'Jazan Region'],
            ['id'=> 150, 'city' => 'Ad Darb', 'code' => 'C_0127', 'region' => 'Jazan Region'],
            ['id'=> 151, 'city' => 'Al Reeth', 'code' => 'C_0140', 'region' => 'Jazan Region'],
            ['id'=> 152, 'city' => 'As Sahi', 'code' => 'C_0128', 'region' => 'Jazan Region'],
            ['id'=> 153, 'city' => 'Ash Shaqiqah', 'code' => 'C_0129', 'region' => 'Jazan Region'],
            ['id'=> 154, 'city' => 'Al Tuwal', 'code' => 'C_0130', 'region' => 'Jazan Region'],
            ['id'=> 155, 'city' => 'Al Aridhah', 'code' => 'C_0131', 'region' => 'Jazan Region'],
            ['id'=> 156, 'city' => 'Al Aliyah', 'code' => 'C_0132', 'region' => 'Jazan Region'],
            ['id'=> 157, 'city' => 'Al Edabi', 'code' => 'C_0133', 'region' => 'Jazan Region'],
            ['id'=> 158, 'city' => 'Al Qufl', 'code' => 'C_0134', 'region' => 'Jazan Region'],            
            ['id'=> 159, 'city' => 'Al Madaya', 'code' => 'citycode_008', 'region' => 'Jazan Region'],
            ['id'=> 160, 'city' => 'Al Muwassam', 'code' => 'C_0135', 'region' => 'Jazan Region'],
            ['id'=> 161, 'city' => 'Harub', 'code' => 'C_0136', 'region' => 'Jazan Region'],
            ['id'=> 162, 'city' => 'bani malik', 'code' => 'citycode_009', 'region' => 'Jazan Region'],
            ['id'=> 163, 'city' => 'bani malik', 'code' => 'citycode_010', 'region' => 'Jazan Region'],
            ['id'=> 164, 'city' => 'Baish', 'code' => 'C_0118', 'region' => 'Jazan Region'],
            ['id'=> 165, 'city' => 'Jazan', 'code' => 'C_000004', 'region' => 'Jazan Region'],
            ['id'=> 166, 'city' => 'Samtah', 'code' => 'C_0123', 'region' => 'Jazan Region'],
            ['id'=> 167, 'city' => 'Sabya', 'code' => 'C_0119', 'region' => 'Jazan Region'],
            ['id'=> 168, 'city' => 'Sabya', 'code' => 'C0195', 'region' => 'Jazan Region'],
            ['id'=> 169, 'city' => 'Damad', 'code' => 'C_0137', 'region' => 'Jazan Region'],
            ['id'=> 170, 'city' => 'Farasan', 'code' => 'C_0141', 'region' => 'Jazan Region'],
            ['id'=> 171, 'city' => 'Jufayfa', 'code' => 'C_0120', 'region' => 'Jazan Region'],
            ['id'=> 172, 'city' => 'Qouzal Jaafrah', 'code' => 'C_0138', 'region' => 'Jazan Region'],            
            ['id'=> 173, 'city' => 'Jazan Valley', 'code' => 'C_0117', 'region' => 'Jazan Region'],
            ['id'=> 174, 'city' => 'Al Hait', 'code' => 'C_0151', 'region' => 'Hail Region'],
            ['id'=> 175, 'city' => 'Al Hulayfah As Sufla', 'code' => 'C_0156', 'region' => 'Hail Region'],
            ['id'=> 176, 'city' => 'Al Khitah', 'code' => 'C_0147', 'region' => 'Hail Region'],
            ['id'=> 177, 'city' => 'Rawda', 'code' => 'C_0152', 'region' => 'Hail Region'],
            ['id'=> 178, 'city' => 'Al Sulaimi', 'code' => 'C_0150', 'region' => 'Hail Region'],
            ['id'=> 179, 'city' => 'Ash Shamli', 'code' => 'C_0149', 'region' => 'Hail Region'],
            ['id'=> 180, 'city' => 'Ash Shinan', 'code' => 'C_0155', 'region' => 'Hail Region'],
            ['id'=> 181, 'city' => 'Al Ghazalah', 'code' => 'C_0157', 'region' => 'Hail Region'],
            ['id'=> 182, 'city' => 'Al Kahafah', 'code' => 'C_0153', 'region' => 'Hail Region'],
            ['id'=> 183, 'city' => 'Baqaa', 'code' => 'C_0145', 'region' => 'Hail Region'],
            ['id'=> 184, 'city' => 'Turbah', 'code' => 'C_0143', 'region' => 'Hail Region'],
            ['id'=> 185, 'city' => 'Trubah', 'code' => 'C_0146', 'region' => 'Hail Region'],
            ['id'=> 186, 'city' => 'Jubbah', 'code' => 'C_0144', 'region' => 'Hail Region'],
            ['id'=> 187, 'city' => 'Hail', 'code' => 'C_000005', 'region' => 'Hail Region'],
            ['id'=> 188, 'city' => 'Simira', 'code' => 'C0193', 'region' => 'Hail Region'],
            ['id'=> 189, 'city' => 'Sumaira\'a', 'code' => 'C_0154', 'region' => 'Hail Region'],
            ['id'=> 190, 'city' => 'Mawqaq', 'code' => 'C_0148', 'region' => 'Hail Region'],
            ['id'=> 191, 'city' => 'Abha', 'code' => 'C_0168', 'region' => 'Asir Region'],
            ['id'=> 192, 'city' => 'Ahad Rafidah', 'code' => 'C_0169', 'region' => 'Asir Region'],
            ['id'=> 193, 'city' => 'Almajaridah', 'code' => 'C_0162', 'region' => 'Asir Region'],
            ['id'=> 194, 'city' => 'Al Namas', 'code' => 'C_0160', 'region' => 'Asir Region'],
            ['id'=> 195, 'city' => 'Balqarn', 'code' => 'C_0159', 'region' => 'Asir Region'],
            ['id'=> 196, 'city' => 'Bisha', 'code' => 'C_0163', 'region' => 'Asir Region'],
            ['id'=> 197, 'city' => 'Tathleeth', 'code' => 'C_0158', 'region' => 'Asir Region'],
            ['id'=> 198, 'city' => 'Tanomah', 'code' => 'C_0161', 'region' => 'Asir Region'],
            ['id'=> 199, 'city' => 'Khamis Mushai', 'code' => 'C_0165', 'region' => 'Asir Region'],
            ['id'=> 200, 'city' => 'Ragal Almaa', 'code' => 'C_0167', 'region' => 'Asir Region'],
            ['id'=> 201, 'city' => 'Sabt Al Alayah', 'code' => 'citycode_011', 'region' => 'Asir Region'],
            ['id'=> 202, 'city' => 'Sarat Abidah', 'code' => 'C_0166', 'region' => 'Asir Region'],
            ['id'=> 203, 'city' => 'Sarat Abida', 'code' => 'citycode_012', 'region' => 'Asir Region'],
            ['id'=> 204, 'city' => 'Dhahran Al Janub', 'code' => 'C_0170', 'region' => 'Asir Region'],
            ['id'=> 205, 'city' => 'Muhayil', 'code' => 'C_0164', 'region' => 'Asir Region'],
            ['id'=> 206, 'city' => 'Al-Jumum', 'code' => 'C_0175', 'region' => 'Mecca Region'],
            ['id'=> 207, 'city' => 'Al Khurma', 'code' => 'C_0176', 'region' => 'Mecca Region'],
            ['id'=> 208, 'city' => 'Taif', 'code' => 'C_0182', 'region' => 'Mecca Region'],
            ['id'=> 209, 'city' => 'Al Qunfudhah', 'code' => 'C_0180', 'region' => 'Mecca Region'],
            ['id'=> 210, 'city' => 'AlQouz', 'code' => 'C_0181', 'region' => 'Mecca Region'],
            ['id'=> 211, 'city' => 'Al Kamil', 'code' => 'C0192', 'region' => 'Mecca Region'],
            ['id'=> 212, 'city' => 'Al Lith', 'code' => 'C_0179', 'region' => 'Mecca Region'],
            ['id'=> 213, 'city' => 'Turbah', 'code' => 'C_0178', 'region' => 'Mecca Region'],
            ['id'=> 214, 'city' => 'Jeddah', 'code' => 'C_0171', 'region' => 'Mecca Region'],
            ['id'=> 215, 'city' => 'Khulais', 'code' => 'C_0174', 'region' => 'Mecca Region'],
            ['id'=> 216, 'city' => 'Rabigh', 'code' => 'C_0173', 'region' => 'Mecca Region'],
            ['id'=> 217, 'city' => 'Ranyah', 'code' => 'C_0177', 'region' => 'Mecca Region'],
            ['id'=> 218, 'city' => 'Mecca', 'code' => 'C_0172', 'region' => 'Mecca Region'],
            ['id'=> 219, 'city' => 'Al Husayniyah', 'code' => 'citycode_013', 'region' => 'Najran Region'],
            ['id'=> 220, 'city' => 'Al Kharkhir', 'code' => 'C_0183', 'region' => 'Najran Region'],
            ['id'=> 221, 'city' => 'Al Wadiah', 'code' => 'C_0189', 'region' => 'Najran Region'],
            ['id'=> 222, 'city' => 'Badr Al Janoub', 'code' => 'C_0188', 'region' => 'Najran Region'],
            ['id'=> 223, 'city' => 'Thar', 'code' => 'C_0187', 'region' => 'Najran Region'],
            ['id'=> 224, 'city' => 'Hubuna', 'code' => 'C_0186', 'region' => 'Najran Region'],
            ['id'=> 225, 'city' => 'Khbash', 'code' => 'C_0184', 'region' => 'Najran Region'],
            ['id'=> 226, 'city' => 'Sharorah', 'code' => 'C_0142', 'region' => 'Najran Region'],
            ['id'=> 227, 'city' => 'Najran', 'code' => 'C0191', 'region' => 'Najran Region'],
            ['id'=> 228, 'city' => 'Yadamah', 'code' => 'C0190', 'region' => 'Najran Region'],
            ['id'=> 229, 'city' => 'Yadamah', 'code' => 'C_0185', 'region' => 'Najran Region'],
        ];

        return $cities;
    }

    public function listofcities($citycode=false, $isarabic=false) {
        global $DB;
        $cities = $this->hall_cities();

        foreach($cities AS $value) {
            $city[$value['code']] = get_string($value['code'], 'local_hall');
        }

        if ($citycode) {
            if ($isarabic) {
                return get_string_manager()->get_string($citycode, 'local_hall', null, 'ar');
            } else {
                return $city[$citycode];
            }
        } else {
            return $city;            
        }
    }

    public function entityreservations($id, $type) 
    {
        global $DB, $PAGE;
        if($type == 'questionbank' && $id>0){
            $questionbank = $DB->get_record_sql("SELECT * FROM {local_questionbank} WHERE id = {$id}");
            $where  = " AND hallid = $questionbank->halladdress";
        }
        $reservatons = $DB->get_records_sql("SELECT * FROM {hall_reservations} WHERE typeid = {$id} AND type = '{$type}' $where ");
        foreach ($reservatons as $value) {
            $row = [];
            $row['hallname'] = $DB->get_field('hall', 'name', ['id' => $value->hallid]);
            $row['examdate'] = date('Y-m-d', strtotime($value->examdate));
            $row['seats'] = $value->seats;
            $data[] = $row;
        }

        $renderer = $PAGE->get_renderer('local_hall');
        return $renderer->entityreservations_renderer($data);
    }

    public function entityreservationsdraft($trainingid, $sesskey, $hallid, $type) 
    {
        global $DB, $PAGE;
        if(!empty($hallid)) {
            $reservatons = $DB->get_records_sql("SELECT * FROM {reservations_draft} WHERE entityid = {$trainingid} AND hallid = {$hallid} AND entitycode = '{$sesskey}' AND '{$type}' ");
            foreach ($reservatons as $value) {
                $row = [];
                $row['hallname'] = $DB->get_field('hall', 'name', ['id' => $value->hallid]);
                $row['examdate'] = date('Y-m-d', strtotime($value->date));
                $row['seats'] = $value->seats;
                $data[] = $row;
            }

            $renderer = $PAGE->get_renderer('local_hall');
            return $renderer->entityreservations_renderer($data);
        } else {
            return false;
        }
    }

    public function globalentitysearch()
    {
        global $PAGE;
        $systemcontext = context_system::instance();
        $renderer = $PAGE->get_renderer('local_hall');
        $filterparams  = $renderer->get_catalog_entitysearch(true);
        $filterparams['submitid'] = 'form#filteringform';
        $filterparams['widthclass'] = 'col-md-6';
        $filterparams['placeholder'] = get_string('search','local_hall');
        $globalinput=$renderer->global_filter($filterparams);
        $halldetails = $renderer->get_catalog_entitysearch();
        $hallsmform = hall_filters_form($filterparams);
        $filterparams['filterform'] = $hallsmform->render();
        $filterparams['halldetails'] = $halldetails;
        $filterparams['globalinput'] = $globalinput;
        $renderer->listofentities($filterparams);
    }

    public function get_listof_entities($stable, $filterdata)
    {
        global $DB;
        $lang = current_language();
        $data = $this->entities($filterdata->search_query);

        if (COUNT($data) > 0) {
            for($i = $stable->start; $i<($stable->start + $stable->length); $i++) {
                $entitiesinfo[] = $data[$i];
                if($i == COUNT($data)-1) {
                    break;
                }
            }
        } else {
            $entitiesinfo = [];
        }

        $entities = array(
            "entities" => $entitiesinfo,
            "nocourses" => $nocourse,
            "length" => COUNT($entitiesinfo),
            "totalentities" => COUNT($data),
        );

        return $entities;
    }

    public function entities($value=false, $isarabic=false)
    {
        global $DB, $SESSION;

        if ($isarabic == true) {
            $SESSION->lang =  'ar';    
        } else {
            $SESSION->lang = 'en';
        }

        $lang = current_language();
        $params = [];
        $search = "LIKE '%".$value."%'";

        // Exams
        $examssql = "SELECT id, exam as enname, examnamearabic as arname, programdescription as description, 'exam' as type, code
                                        FROM {local_exams} 
                                        WHERE status = 1 AND exam $search OR examnamearabic $search";
        $exams = $DB->get_records_sql($examssql, $params);

        // Training programs
        $programsql = "SELECT id, name as enname, namearabic as arname, description, 'program' as type, code
                                        FROM {local_trainingprogram} 
                                        WHERE published = 1 AND name $search OR namearabic $search";
        $programs = $DB->get_records_sql($programsql, $params);

        // Sectors
        $sectorsql = "SELECT id, title as enname, titlearabic as arname, Null as description, 'sector' as type, code
                                        FROM {local_sector} 
                                        WHERE title $search OR titlearabic $search";
        $sectors = $DB->get_records_sql($sectorsql, $params);

        // Segment
        $segmentsql = "SELECT id, title as enname, titlearabic as arname, description, 'segment' as type, code, sectorid
                                        FROM {local_segment} 
                                        WHERE title $search OR titlearabic $search";
        $segments = $DB->get_records_sql($segmentsql, $params);

        // Events
        $eventsql = "SELECT id, title as enname, titlearabic as arname, description, 'event' as type, code
                                        FROM {local_events} 
                                        WHERE title $search OR titlearabic $search";
        $events = $DB->get_records_sql($eventsql, $params);

        // Job Family
        $jobfamilysql = "SELECT id, familyname as enname, familynamearabic as arname, description, 'jobfamily' as type, code, segmentid
                                        FROM {local_jobfamily} 
                                        WHERE familyname $search OR familynamearabic $search";
        $jobfamilies = $DB->get_records_sql($jobfamilysql, $params);

        // Job Roles
        $jobrolesql = "SELECT id, title as enname, titlearabic as arname, description, 'jobrole' as type, code, jobfamily
                                        FROM {local_jobrole_level} 
                                        WHERE title $search OR titlearabic $search";
        $jobroles = $DB->get_records_sql($jobrolesql, $params);

        $entities = array_merge($exams, $programs, $sectors, $segments, $events, $jobfamilies, $jobroles);

        $data = [];
        foreach($entities as $entity) {
            $row = [];
            $row['id'] = $entity->id;
            if ($lang == 'ar' || $isarabic == 'ar') {
                $row['name'] = $entity->arname;
            } else {
                $row['name'] = $entity->enname;
            }
            $row['Description'] = format_text($entity->description, FORMAT_HTML);
            $row['description'] = strip_tags(format_text($entity->description, FORMAT_HTML));
            $row['type'] = get_string($entity->type, 'local_hall');


            if (!empty($entity->sectorid)) {        // Segment
                $row['typeCode'] = get_string('subsector', 'local_hall');
                $row['typeName'] = get_string('section', 'local_hall');
                $row['navigators'][] = $this->sectors($entity->sectorid, $SESSION->lang);
            } elseif (!empty($entity->segmentid)) {   // JobFamily
                $row['navigators'] = $this->segments($entity->segmentid, $SESSION->lang);
            } elseif(!empty($entity->jobfamily)) {
                $row['navigators'] = $this->jobfamily($entity->jobfamily, $SESSION->lang);
            } else {
                // $row['navigators'][0]['id'] = NULL;
                // $row['navigators'][0]['name'] = NULL;
                // $row['navigators'][0]['typeCode'] = NULL;
                // $row['navigators'][0]['typeName'] = NULL;
                $row['navigators'][0][] = null;
            }
            if (empty($entity->sectorid)) {
                $row['typeCode'] = get_string($entity->type, 'local_hall');
                $row['typeName'] = get_string($entity->type, 'local_hall');                
            }

            $data[] = $row;
        }

        return $data;
    }

    public function sectors($id, $isarabic=false)
    {
        global $DB;
        $lang = current_language();
        $sector = $DB->get_record_sql("SELECT * FROM {local_sector} WHERE id IN ($id) ");        
        $row['id'] = $sector->id;
        if ($lang == 'ar' || $isarabic == 'ar') {
            $row['name'] = $sector->titlearabic;
        } else {
            $row['name'] = $sector->title;
        }
        $row['typeCode'] = get_string('sector', 'local_hall');
        $row['typeName'] = get_string('sector', 'local_hall');

        return $row;
    }

    public function segments($id, $isarabic=false)
    {
        global $DB;
        $lang = current_language();
        $segment = $DB->get_record_sql("SELECT id, title, titlearabic, 'subSector' as typeCode, 'Section' as typeName, sectorid FROM  {local_segment} WHERE  id IN ($id)");
        $row['id'] = $segment->id;
        if ($lang == 'ar' || $isarabic == 'ar') {
            $row['name'] = $segment->titlearabic;
        } else {
            $row['name'] = $segment->title;
        }
        $row['typeCode'] = get_string('subsector', 'local_hall');
        $row['typeName'] = get_string('section', 'local_hall');
        $data[] = $row;
        $sector[] = $this->sectors($segment->sectorid);
        $navigators = array_merge($sector, $data);

        return $navigators;
    }

    public function jobfamily($id, $isarabic=false)
    {
        global $DB;
        $lang = current_language();
        $jobfamily = $DB->get_record_sql("SELECT id, familyname, familynamearabic, segmentid FROM  {local_jobfamily} WHERE  id =". $id);

        $row['id'] = $jobfamily->id;
        if ($lang == 'ar' || $isarabic == 'ar') {
            $row['name'] = $jobfamily->familynamearabic;
        } else {
            $row['name'] = $jobfamily->familyname;
        }
        $row['typeCode'] = get_string('jobfamily', 'local_hall');
        $row['typeName'] = get_string('job_family', 'local_hall');
        $data[] = $row;

        if($jobfamily->segmentid > 0) {
            $sector = $this->segments($jobfamily->segmentid);            
        } else {
            $sector =  [];
        }

        $navigators = array_merge($sector, $data);

        return $navigators;
    }

    public function getschedulehalls($stable, $filterdata) {
        global $DB,$CFG,$PAGE;
        $systemcontext = context_system::instance();
        $hallid = $filterdata->hallid;
        $PAGE->set_context($systemcontext);
        $selecthallsql = "SELECT * FROM {hallschedule}  WHERE    hallid = $hallid";
        $counthallsql  = "SELECT id FROM {hallschedule} WHERE   hallid = $hallid";

         if( $filterdata->{'datefrom[enabled]'} == 1 && $filterdata->{'dateto[enabled]'} == 1 ){
                $start_year = $filterdata->{'datefrom[year]'};
                $start_month = $filterdata->{'datefrom[month]'};
                $start_day = $filterdata->{'datefrom[day]'};
                $filter_starttime_con = mktime(0,0,0, $start_month, $start_day, $start_year);
                $end_year = $filterdata->{'dateto[year]'};
                $end_month = $filterdata->{'dateto[month]'};
                $end_day = $filterdata->{'dateto[day]'};
                $filter_endtime_con=mktime(23,59,59, $end_month, $end_day, $end_year);
                $formhallsql.= " AND (startdate >= $filter_starttime_con AND startdate < $filter_endtime_con) ";
                $formhallsql.= " OR (startdate >= $filter_starttime_con AND startdate < $filter_endtime_con) ";
            } elseif($filterdata->{'datefrom[enabled]'} == 1 ){
                $start_year = $filterdata->{'datefrom[year]'};
                $start_month = $filterdata->{'datefrom[month]'};
                $start_day = $filterdata->{'datefrom[day]'};
                $filter_starttime_con = mktime(0,0,0, $start_month, $start_day, $start_year);
                $formhallsql.= " AND startdate >= '$filter_starttime_con' ";
            } elseif($filterdata->{'dateto[enabled]'} == 1 ){
                $start_year = $filterdata->{'datefrom[year]'};
                $start_month = $filterdata->{'datefrom[month]'};
                $start_day = $filterdata->{'datefrom[day]'};
                $filter_endtime_con=mktime(23,59,59, $start_month, $start_day, $start_year);
                $formhallsql.=" AND startdate <= '$filter_endtime_con' ";
            }

            
       $formhallsql .=" GROUP BY startdate  ORDER BY id DESC";
       $totalschedulehalls = $DB->get_records_sql($counthallsql.$formhallsql,null);
       
        $schedulehall = $DB->get_records_sql($selecthallsql.$formhallsql,null,$stable->start,$stable->length);
        $list = array();
        $count = 0;
        $lang = current_language();


        foreach($schedulehall as $dates) {
            $row = [];
            $row['startdate'] = userdate($dates->startdate, get_string('strftimedaydate', 'core_langconfig'));
            $gettimesql = "SELECT * FROM {hallschedule} WHERE startdate = $dates->startdate AND hallid = $hallid";
            $gettime = $DB->get_records_sql($gettimesql);
            $scheduledtime['data'] = [];
            foreach($gettime as $time) {                      
                    $sctime['id'] = $time->id;
                    $sctime['starttime'] = userdate($time->starttime, get_string('strftimetime24', 'langconfig'));
                    $sctime['endtime']=userdate($time->endtime, get_string('strftimetime24', 'langconfig'));
                    $sctime['days'] = $time->days;
                    $sctime['hallurl'] = $CFG->wwwroot;
                    $sctime['dedicatedfor'] = ($time->entityid!=0) ? $DB->get_field('local_exams', 'exam', ['id'=>$time->entityid]) : '';
                    $scheduledtime['data'][] = $sctime;
                }
                $list[] = $row+$scheduledtime;
            }

            
        $selectsql = "SELECT * FROM {hall}  WHERE 1=1 AND id = $hallid  "; 
        $countsql  = "SELECT COUNT(id) FROM {hall} WHERE 1=1 AND id = $hallid  ";
        $totalhall = $DB->count_records_sql($countsql.$formsqls, $params);
        $formsqls .=" ORDER BY id ASC";
        $halls = $DB->get_records_sql($selectsql.$formsqls, $params);
        $hallslist = array();
        $count = 0;
        $types = ['1' => get_string('exam', 'local_hall'), '2' => get_string('trainingprogram', 'local_hall'), '3' => get_string('event', 'local_hall')];
        $cities = ['1' => 'Riyadh'];
        $roomshape = ['1'=>get_string('circle','local_hall'),'2'=>get_string('rectangle','local_hall'),'3'=>get_string('square','local_hall')];
        $availability = ['1'=>get_string('enabled','local_hall'),'2'=>get_string('notenabled','local_hall')];
        foreach($halls as $hall) {
            $hallslist[$count]["id"] = $hall->id;
            $hallslist[$count]["name"] = $hall->name;
            $hallslist[$count]["type"] = !empty($types[$hall->type]) ? $types[$hall->type] : '--';
            $hallslist[$count]["city"] = $this->listofcities($hall->city);
            $hallslist[$count]["buildingname"] = format_string($hall->buildingname);
            $hallslist[$count]["seatingcapacity"] = $hall->seatingcapacity;

            if (filter_var($hall->maplocation, FILTER_VALIDATE_URL)) {
                $hallslist[$count]["locationstatus"] = true;
            } else {
                $hallslist[$count]["locationstatus"] = false;
            }

            $hallslist[$count]["maplocation"] = $hall->maplocation;
            $hallslist[$count]["roomshape"] = !empty($roomshape[$hall->roomshape]) ? $roomshape[$hall->roomshape] : '--';
            $hallslist[$count]["availability"] = !empty($availability[$hall->availability]) ? $availability[$hall->availability] : '--';
            $hallslist[$count]["equipmentavailable"] = $this->equipmentavailable($hall->equipmentavailable);
            $hallslist[$count]["description"] = format_text($hall->description, FORMAT_HTML);
            $count++;
        }
        $systemcontext = context_system::instance();     
        $coursesContext = array(
            "hascourses" => $hallslist,
            "nocourses" => $nocourse,
            "totalhalls" => count($totalschedulehalls),
            "length" => count($list),
            "hashalllist" => $list,

        );
        return $coursesContext;
    }
    public function schedulehall($hallid) {
        global $DB, $PAGE, $OUTPUT;
        $systemcontext = context_system::instance();
        $renderer = $PAGE->get_renderer('local_hall');
        $filterparams  = $renderer->get_catalog_schedulehalls(true,$hallid);
        $filterparams['submitid'] = 'form#filteringform';
        $filterparams['widthclass'] = 'col-md-6';
        $filterparams['placeholder'] = get_string('searchhall','local_hall');
        $globalinput=$renderer->global_filter($filterparams);
        $details = $renderer->get_catalog_schedulehalls(null,$hallid);
        $hallsmform = hallsinfo_filters_form($filterparams);
        $filterparams['filterform'] = $hallsmform->render();
        $filterparams['schedulehall'] = $details;
        $filterparams['globalinput'] = $globalinput;
        $filterparams['hallid'] = $hallid;
        $renderer->listofschedulehalls($filterparams);
    }

    public function add_update_hall_schedule($data) {
        global $DB,$USER, $CFG;
        $halls = (array)$data->hallid;

        $scheduleserror = [];
        foreach($halls as $hallid) {
            $error = [];
            $hall = $DB->get_record('hall', ['id'=>$hallid]);
            $date = date('m/d/Y');

            if ($data->id > 0) {
                $gethallschedule = $DB->get_record('hallschedule', array('id' => $data->id));
                $hallstartdate = $gethallschedule->startdate;
                $hallenddate = $gethallschedule->startdate;
            } else {
                $hallstartdate = $data->startdate;
                $hallenddate = $data->enddate;
            }

            $id = $data->id;

            $listofday = self::get_selecteddays($data);

            $dates = array();
            $getdates = array();
            while($hallstartdate <= $hallenddate) {
                $dates[] = date('Y-m-d', $hallstartdate);
                $hallstartdate = strtotime('+1 day', $hallstartdate);
            }
            foreach($dates as $date) {
                $error = [];
                // Checking date with day
                $dayofday = array();
                $timestamp = strtotime($date);
                $day = date('l', $timestamp);
                $selectedday=in_array($day, $listofday);

                if (!$selectedday && $data->id ==0) {
                    $error[] = get_string('notmatcheddateandday', 'local_hall', ['date'=>$date, 'day'=>$day, 'hallname'=>$hall->name]);
                }

                // checking selected times with hall times
                $hours = $data->starthour*3600;
                $minutes = $data->startminute*60;
                $getstarttime = $hours+$minutes;  
                $hours = $data->endhour*3600;
                $minutes = $data->endminute*60;
                $getendtime = $hours+$minutes;                
             
                $hall = $DB->get_record('hall', ['id' => $hallid]);
                if (($getstarttime < $hall->hallstarttime OR $getstarttime > $hall->hallendtime)) {
                    $error[] = get_string('starttimenotmatced', 'local_hall', $hall->name);
                }
        
                if (($getendtime > $hall->hallendtime OR $getendtime < $hall->hallstarttime)) {
                    $error[] = get_string('endtimenotmatced', 'local_hall', $hall->name);
                }

                $endtime = usertime($getendtime);
                $starttime = usertime($getstarttime);
                $selecteddate = usertime($data->startdate +  $getstarttime);
                $curenttime=usertime(time());
                if($data->id>0) {  
                    $gethallschedule = $DB->get_record('hallschedule',array('id' => $data->id));
                    $curenttim = time();
                        $existingtime = $gethallschedule->startdate + $getstarttime;
                    if(($existingtime < $curenttim)){
                        $error[] = get_string('selectfuturetime', 'local_hall');
                    }        
        
                    $getstartdate = $DB->get_record('hallschedule',['id'=>$id]);
                    $hallstartdate = $getstartdate->startdate;    
                    $da=strtotime($date);
                    $sql = "SELECT seatingcapacity
                            FROM {hallschedule}  
                            WHERE (hallid = $hallid AND starttime = '{$starttime}' AND endtime = '{endtime}' AND startdate = $da) OR 
                            (hallid = $hallid AND  '{$starttime}' >= starttime AND '{$starttime}' < endtime AND startdate = $da)  OR 
                            (hallid = $hallid AND endtime > '{$starttime}' AND endtime <= '{endtime}' AND startdate = $da) ";
                    $getdatessql = $DB->get_fieldset_sql($sql);
                    $scheduledseats = array_sum($getdatessql);
                    if ($scheduledseats < $hall->seatingcapacity) {
                        $id = $data->id;
                        //$data->seatingcapacity = $data->seatingcapacity + $getstartdate->seatingcapacity;
                        $data->seatingcapacity = $data->seatingcapacity;
                    } else {
                        $error[] = get_string('slotsarefull', 'local_hall', ['startdate'=>$date, 'hallname' => $hall->name]);
                    }                    
                } else if($data->id==0) {
                    if($selecteddate < $curenttime){
                        $error[] = get_string('selectfuturetime', 'local_hall');
                    }
        
                    if($hallstartdate<strtotime($date)) {
                        $error[] = get_string('startdateprev', 'local_hall');
                    }
        
                    if($hallenddate<strtotime($date)) {
                        $error[] = get_string('enddateprev', 'local_hall');
                    }
                    if (!empty($data->entityid)) {
                        $conditionsql = " AND entityid = ".$data->entityid;
                    }
                    $da=strtotime($date);
                    $sql = "SELECT seatingcapacity
                              FROM {hallschedule} 
                             WHERE hallid = $hallid AND startdate = $da AND starttime = '{$starttime}' AND endtime = '{$endtime}' ";
                    $getdatessql = $DB->get_fieldset_sql($sql);
                    $scheduledseats = array_sum($getdatessql);
                    if ($scheduledseats < $hall->seatingcapacity) {
                        $sql = "SELECT id, seatingcapacity
                                    FROM {hallschedule} 
                                    WHERE hallid = $hallid AND entityid=$data->entityid AND startdate = $da AND starttime = '{$starttime}' AND endtime = '{$endtime}' ";
                        $schedule = $DB->get_record_sql($sql);
                        if (!empty($schedule)) {
                            $id = $schedule->id;
                            $data->seatingcapacity = $data->seatingcapacity + $schedule->seatingcapacity;
                            if ($data->seatingcapacity > $hall->seatingcapacity) {
                                $error[] = get_string('slotsarefull', 'local_hall', ['startdate'=>$date, 'hallname' => $hall->name]);
                            }
                        } else {
                            $sql = "SELECT *
                                FROM {hallschedule}  
                                WHERE (hallid = $hallid AND  '{$starttime}' >starttime AND '{$starttime}' < endtime AND startdate = $da)  OR 
                                (hallid = $hallid AND endtime > '{$starttime}' AND endtime < '{$endtime}' AND startdate = $da) ";
                            $existingschedule = $DB->get_record_sql($sql);
                            if (!empty($existingschedule)) {
                                $existingdate = userdate($existingschedule->startdate, get_string('strftimedaydate', 'core_langconfig'));
                                $existingstarttime = userdate($existingschedule->starttime, get_string('strftimetime24', 'langconfig'));
                                $existingendtime = userdate($existingschedule->endtime, get_string('strftimetime24', 'langconfig'));
                                $starttime = userdate($starttime, get_string('strftimetime24', 'langconfig'));
                                $endtime = userdate($endtime, get_string('strftimetime24', 'langconfig'));
                                $error[] = get_string('slotavailable', 'local_hall', ['existingdate'=>$existingdate, 'hallname' => $hall->name, 'existingstarttime'=>$existingstarttime, 'existingendtime'=>$existingendtime, 'selecteddate'=>$date, 'selectedstarttime'=>$starttime, 'selectedendtime'=>$endtime]);
                            } else {
                                $id = 0;
                            }
                        }
                    } else {
                        $error[] = get_string('slotsarefull', 'local_hall', ['startdate'=>$date, 'hallname' => $hall->name]);
                    }
                }

                if (empty($error)) {
                    if($id>0) {
                        $row['starttime'] = $starttime;
                        $row['endtime'] = $endtime;
                        $row['seatingcapacity'] = $data->seatingcapacity;
                        $row['directedto'] = $data->directedto;
                        $row['status'] = $data->status;
                        $row['id'] = $id;
                        $row['timemodified'] = time();
                        $id = $DB->update_record('hallschedule', $row);
                    } else {
                        $row['days'] = $day;
                        $row['startdate']=$timestamp;
                        $row['starttime'] = $starttime;
                        $row['endtime'] = $endtime;
                        $row['timecreated'] = time();
                        $row['hallid'] = $hallid;
                        $row['seatingcapacity'] = !empty($data->seatingcapacity) ? $data->seatingcapacity : 0;
                        $row['directedto'] = $data->directedto;
                        $row['status'] = $data->status;
                        $row['entity'] = !empty($data->entity) ? $data->entity : NULL;
                        $row['entityid'] = !empty($data->entityid) ? $data->entityid : 0;
                        $id = $DB->insert_record('hallschedule', $row);
                    }
                } else {
                    $scheduleserror[] = $error;
                }
            }
        }
        
        if (!empty($scheduleserror)) {
            if ($data->entityid > 0) {
                $type = 'exam';
                $typeid = $data->entityid;
            } else {
                $type = 'hall';
                $typeid = $data->hallid;
            }

            $returndata = ['type'=> $type, 'typeid'=> $typeid, 'errors'=> base64_encode(serialize($scheduleserror))];

            return $returndata;
        } else {
            header("Refresh:0");
        }
    }

    public function get_selecteddays($data) {
        $listofday = array();    
        if($data->monday == 1) {
            $listofday[]="Monday";
        }
        if($data->tuesday == 1) {
            $listofday[]="Tuesday";
        }
        if($data->wednesday == 1) {
            $listofday[]="Wednesday";
        }
        if($data->thursday == 1){
            $listofday[]="Thursday";
        }
        if($data->friday == 1) {
            $listofday[]="Friday";
        }
        if($data->satuarday == 1) {
            $listofday[]="Saturday";
        }
        if($data->sunday == 1) {
            $listofday[]="Sunday";
        }

        return $listofday;
    }

    public function schedulenotices($notices, $type, $typeid) {
        global $DB, $CFG, $OUTPUT;
        $issues = unserialize(base64_decode($notices));
        foreach($issues as $issue) {
            foreach($issue as $notice) {
                echo "<div class='alert alert-danger'>".$notice."</div>";
            }
        }
        if ($type=='exam') {
            $url = '/local/exams/index.php';
        } else {
            $url = '/local/hall/schedulehall.php?id='.$typeid;
        }

        $button = new single_button(new moodle_url($url), get_string('click_continue','local_exams'), 'get', true);
        $button->class = 'continuebutton';
        echo $OUTPUT->render($button);

    }

     public function update_hall_schedule($data) {
        global $DB,$USER;
        $row = array();
        $hours = $data->starthour*3600;
        $minutes = $data->startminute*60;
        $starttime = $hours+$minutes;
        
        $hours = $data->endhour*3600;
        $minutes = $data->endminute*60;
        $endtime = $hours+$minutes;

        $getendtime = usertime($endtime);
        $row['endtime'] = $getendtime;
        $getstarttime = usertime($starttime);
        $row['starttime'] = $getstarttime;
        

        if($data->id > 0) {
            $row['id'] = $data->id;
            $row['timemodified'] = time();
            $record->id = $DB->update_record('hallschedule', $row);
        } 
        return $record;
    }

    public function set_schedulehall($id) {
        global $DB;
        $data = $DB->get_record('hallschedule', ['id' => $id], '*', MUST_EXIST);
        $row['id'] = $data->id;
        $row['hallid'] = $data->hallid;
        $row['startdate'] = $data->startdate;
        $row['enddate'] = $data->enddate;
        $row['seatingcapacity'] = $data->seatingcapacity;
        $row['directedto'] = $data->directedto;
        $row['status'] = $data->status;
        $row['starthour'] = userdate($data->starttime, '%H');
        $row['startminute'] = userdate($data->starttime, '%M');
        $row['endhour'] = userdate($data->endtime, '%H');
        $row['endminute'] = userdate($data->endtime, '%M');
        $row['entity'] = $data->entity;
        $row['entityid'] = $data->entityid;

        return $row; 
    }

    public function update_hallcodes($data) {
        global $DB;
        $row = new stdClass();
        $row->id = $data->hallid;
        $row->hallcodes = json_encode($data);

        $DB->update_record('hall', $row);

        return true;
    }
    public function get_hallcode($hallid, $examid=false) {
        global $DB;
        $ownedby = $DB->get_field('local_exams', 'ownedby', ['id'=>$examid]);
        $hallcodes = $DB->get_field('hall', 'hallcodes', ['id'=>$hallid]);
        $codes = json_decode($hallcodes);
        $code = $codes->$ownedby;

        if(empty($code)) {
            $code = $DB->get_field('hall', 'code', ['id'=>$hallid]);
        }

        return $code;
    }
    public function get_scheduletimings($id) {
        global $DB;
        $halltimings = $DB->get_record('hall', ['id'=>$id], 'hallstarttime, hallendtime');


    }
}
