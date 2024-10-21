<?php
namespace local_hall\form;
use core;
use moodleform;
use context_system;
use DatePeriod;
use DateTime;
use html_writer;
use local_hall\hall as hall;

require_once($CFG->dirroot . '/lib/formslib.php');
class listofhallsform extends moodleform {
	function definition() {
		global $CFG,$DB;
		$mform = & $this->_form;
	    $typeid = & $this->_customdata['typeid'];
	    $type = & $this->_customdata['type'];
	    $reservationid = & $this->_customdata['reservationid'];
        $hallid =  & $this->_customdata['hallid'];
        $contextid =  & $this->_customdata['contextid'];

        $mform->addElement('hidden', 'typeid', '', ['class' => 'typeid']);
        $mform->setType('typeid',PARAM_INT);

        $mform->addElement('hidden', 'type', '', ['class' => 'type']);
        $mform->setType('type',PARAM_INT);

        $mform->addElement('hidden', 'duration', '', ['class' => 'duration']);
        $mform->setType('duration',PARAM_INT);

        $mform->addElement('hidden', 'entitiesseats', '', ['class' => 'entitiesseats']);
        $mform->setType('entitiesseats',PARAM_INT);

        $mform->addElement('hidden', 'entityid', '', ['class' => 'entityid']);
        $mform->setType('entityid',PARAM_RAW);

        $mform->addElement('hidden', 'starttime', '', ['class' => 'starttime']);
        $mform->setType('starttime',PARAM_RAW);
        
        $mform->addElement('hidden', 'reservationid', '', ['class' => 'reservationid']);
        $mform->setType('reservationid',PARAM_RAW);

        $mform->addElement('hidden', 'contextid', '', ['class' => 'contextid']);
        $mform->setType('contextid',PARAM_INT);

        $mform->addElement('hidden', 'submit_type', '', ['class' => 'submit_type']);
        $mform->setType('submit_type',PARAM_RAW);

        $cityattributes = array(
            // 'ajax' => 'local_hall/hall_datasource',
            // 'data-type' => 'city',
            'id' => 'city',
            'placeholder' => get_string('city','local_hall')
        );

        $jrattributes = array(
            'ajax' => 'local_hall/hall_datasource',
            'data-type' => 'buildingname',
            'id' => 'buildingname',
            'placeholder' => get_string('buildingname','local_hall')
        );
        
       // $halls = $DB->get_record_sql("SELECT id, name FROM {hall} WHERE availability = 1 AND id = $hallid");
        $halls = [];
        $select = [null => get_string('selecthallstring', 'local_hall')];
        $hallslist = $this->_ajaxformdata['halls'];
        if (!empty($hallslist)) {
            $halls =   $select + $DB->get_records_menu('hall', ['id'=> $hallslist]);
        } else {
            $halls =  $select;
        }
        $options = array(
            'ajax' => 'local_hall/hall_datasource',
            'data-type' => 'schedulehalls',
            'data-hallid' => $hallid,
            'class' => 'el_currenthall',
		    'data-city' => '',
		    'data-buildingname' => '',
		    'noselectionstring' => get_string('selecthallstring', 'local_hall'),
            'placeholder' => get_string('hall','local_hall')
		);

		// if($type == 'exam') {

  //           $dates = $DB->get_record('local_exams', ['id' => $typeid], 'examdatetime as startdate, enddate, slot as starttime');

		// } elseif($type == 'tprogram' || $type == 'event' || $type == 'questionbank') {

			/*if($typeid > 0 && $type == 'event') { // For already created event
				$dates = $DB->get_record('local_events', ['id' => $typeid], 'startdate, enddate, slot as starttime');
			} else*/ if($typeid > 0 && $type == 'tprogram') {
				$dates = $DB->get_record('tp_offerings', ['id' => $typeid], 'startdate, enddate, time as starttime');
			} else { // At the time of event and trainingprogram creation
				$startdate = & $this->_customdata['startdate'];
                $dates->starttime = & $this->_customdata['starttime'];
				$dates->startdate = strtotime($startdate);
			$enddate = & $this->_customdata['enddate'];
                $dates->enddate = strtotime($enddate);
            }
		// }

        $presenthours = date("H")*3600;
        $presentminutes = date("i")*60;
        $presenttime = $presenthours+$presentminutes;
        $startdate = date('Y-m-d', $dates->startdate);
        $todaysdate = date('Y-m-d');
        if(($startdate >= $todaysdate && $dates->starttime > $presenttime) || $startdate > $todaysdate) {
            $dates->startdate = $dates->startdate;
        } elseif( (date( 'Y-m-d', $dates->enddate) == $todaysdate) && $dates->starttime > $presenttime ){
            $dates->startdate = strtotime($todaysdate);
        } else { 
            $dates->startdate = strtotime("+1 day");
        }

        $dates->enddate = date( 'Y-m-d', $dates->enddate);
        $period = new DatePeriod(
             new DateTime(date( 'Y-m-d', $dates->startdate)),
             new \DateInterval('P1D'),
             new DateTime(date( 'Y-m-d', strtotime($dates->enddate. ' + 1 day')))
        );

        $examslist = [];
        foreach ($period as $value) {
        	$key = $value->format('Y-m-d'); 
            $examslist[$key] =  $value->format('Y-m-d');
        }

        $cities = (new \local_hall\hall)->listofcities();

        $ord_details_groupelemnts=array();

        $ord_details_groupelemnts[] = $mform->createElement('autocomplete', 'city',get_string('city','local_hall'), $cities, $cityattributes);
        $ord_details_groupelemnts[] = $mform->createElement('autocomplete', 'buildingname', get_string('buildingname', 'local_hall'),[], $jrattributes);
        $ord_details_groupelemnts[] =$mform->createElement('autocomplete', 'halls', get_string('hall','local_hall'), $halls, $options);
        $ord_details_groupelemnts[] =$mform->createElement('select', 'moduledates',get_string('selectdate','local_hall'), array(null=>get_string('selectdate','local_hall')) + $examslist, ['class' => 'halldate']);
		$mform->addGroup($ord_details_groupelemnts, 'ord_details_2','', array('class' => 'ord_details_2'), false);	
	
		$mform->addElement('button', 'hallbtn', get_string("apply", "local_hall"), ['class' => 'hallbtn', 'data-action' => 'hallbtn']);

        $mform->addElement('html', "<div class='halldatemandatory'></div>");
	}

	function validation($data, $files=false) {
        global $DB, $CFG;
        $errors = array();

        if( empty($data->hallid) ) {
            $errors['halls'] = get_string('hallismandatory','local_hall');
        }

        if( empty($data->examdate) ) {
            $errors['moduledates'] = get_string('hallismandatory','local_hall');
        }

		return $errors;
	}
}
