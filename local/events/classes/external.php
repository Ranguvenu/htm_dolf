<?php
/**
 * This file is part of eAbyas
 *
 * Copyright eAbyas Info Solutons Pvt Ltd, India
 *
 * This program is free software; you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation; either version 3 of the License, or
 * (at your option) any later version.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with this program.  If not, see <http://www.gnu.org/licenses/>.
 *
 */
defined('MOODLE_INTERNAL') || die();
global $PAGE, $OUTPUT;
require_once("$CFG->libdir/externallib.php");


/**
 * TODO describe file external
 *
 * @package    local_events
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class local_events_external extends external_api {
    public function deleteevent_parameters(){
        return new external_function_parameters(
            array(
                'action' => new external_value(PARAM_ACTION, 'Action of the event', false),
                'eventid' => new external_value(PARAM_INT, 'ID of the record', 0),
                'confirm' => new external_value(PARAM_BOOL, 'Confirm', false),
            )
        );
    }

    public static function deleteevent($action, $eventid, $confirm) {
        global $DB,$CFG,$USER;
        $systemcontext = context_system::instance();
        try {
            if ($confirm) {
                  // notification events  onchange
             $touser = $DB->get_records('local_event_attendees', ['eventid' => $eventid]);
             $eventdata=$DB->get_record('local_events',array('id'=>$eventid));           
             if(!$touser)
                {$touser=null;}
            $row1=[];
            $row1['RelatedModuleName']=$eventdata->title;
            $myobject=(new \local_events\notification);
            $myobject->event_notification('events_cancel',$touser, $USER,$row1,$waitinglistid=0);
                $DB->delete_records('local_events', array('id' => $eventid));
                $event = \local_events\event\events_deleted::create(array( 'context' => $systemcontext, 'objectid' => $eventid));// ... code that may add some record snapshots
                $event->trigger();
                $return = true;
            } else {
                $return = false;
            }  
        } catch (dml_exception $ex) {
            print_error($ex);
            $return = false;
        }
        return $return;
    }

    public static function deleteevent_returns() {
        return new external_value(PARAM_BOOL, 'return');
    }

    public function view_events_parameters() {
        return new external_function_parameters([
            'status' => new external_value(PARAM_RAW, 'The paging data for the service', VALUE_DEFAULT, 0),
            'options' => new external_value(PARAM_RAW, 'The paging data for the service', VALUE_OPTIONAL),
            'dataoptions' => new external_value(PARAM_RAW, 'The data for the service', VALUE_OPTIONAL),
            'offset' => new external_value(PARAM_INT, 'Number of items to skip from the begging of the result set',
                VALUE_DEFAULT, 0),
            'limit' => new external_value(PARAM_INT, 'Maximum number of results to return',
                VALUE_DEFAULT, 0),
            'contextid' => new external_value(PARAM_INT, 'contextid', VALUE_OPTIONAL),
            'filterdata' => new external_value(PARAM_RAW, 'filters applied', VALUE_OPTIONAL),    
        ]);
    }

    public function view_events($status=false, $options=false, $dataoptions=false, $offset=0, $limit=0, $contextid=1, $filterdata=false) {
        global $DB, $PAGE, $USER;
        $systemcontext = context_system::instance();
        // Parameter validation.
        $params = self::validate_parameters(
            self::view_events_parameters(),
            [
                'options' => $options,
                'dataoptions' => $dataoptions,
                'offset' => $offset,
                'limit' => $limit,
                'contextid' => $contextid,
                'filterdata' => $filterdata,
                'status' => $status,
            ]
        );
        $settings = external_settings::get_instance();

        $offset = $params['offset'];
        $limit = $params['limit'];
        $filtervalues = json_decode($filterdata);
    
        if ($status==1 || $filtervalues->status==1) {
            $filtervalues->status = 1;
        } elseif($status==2) {
            $filtervalues->status = 'local_events';
            $filtervalues->type = 'mobile';
        } else {
            $filtervalues->status = 0;
        }

        $stable = new \stdClass();
        $stable->thead = false;
        $stable->start = $offset;
        $stable->length = $limit;

        // Parameters for My Events tab
        $stable->orguserid = $USER->id;
        $stable->tablename = 'tool_org_order_seats';
        $stable->selectparams = ',tppmnt.approvalseats,tppmnt.availableseats';
        $stable->mlang =  $settings->get_lang();

        $data = (new local_events\events)->get_listof_events($stable, $filtervalues);
        $totalcount = $data['totalevents'];
        $traineeview = (!is_siteadmin() && has_capability('local/organization:manage_trainee', $systemcontext));
        $orgoff =(!is_siteadmin() && has_capability('local/organization:manage_organizationofficial', $systemcontext));
        if($traineeview || $orgoff){
          $isorgoffortrainee= true;
        
        }else{
             $isorgoffortrainee= false;
        }
        return [
            'totalcount' => $totalcount,
            'length' => $totalcount,
            'filterdata' => $filterdata,
            'records' =>$data,
            'options' => $options,
            'dataoptions' => $dataoptions,
            'events' => $data['hasevents'],
            'products' => !empty($data['products']) ? $data['products'] : [],
            'userid' => $USER->id, 
            'traineeview'=> (!is_siteadmin() && has_capability('local/organization:manage_trainee', $systemcontext)) ? true : false,
            'orgoff'=> (!is_siteadmin() && has_capability('local/organization:manage_organizationofficial', $systemcontext)) ? true : false,
            'isorgoffortrainee' => $isorgoffortrainee,
        ];

    }

    public function view_events_returns() {
        return new external_single_structure([
            'products' => new external_multiple_structure(
                new external_single_structure(
                    array(
                        'id' => new external_value(PARAM_INT, 'payment id'),
                        'name' => new external_value(PARAM_RAW, 'training name'),
                        'datebegin' => new external_value(PARAM_INT, 'startdate'),
                        'dateend' => new external_value(PARAM_INT, 'enddate'),
                        'purchasedseats' => new external_value(PARAM_INT, 'purchased seats'),
                        'approvalseats' => new external_value(PARAM_INT, 'approval seats'),
                        'availableseats' => new external_value(PARAM_INT, 'available seats'),
                        'enrollbtn' => new external_value(PARAM_RAW, 'training enrollbtn'),
                        'timelimit' => new external_value(PARAM_RAW, 'Time Limit'),
                        'offeringview' => new external_value(PARAM_RAW, 'offeringview'),
                        'referenceid' => new external_value(PARAM_INT, 'offeringview'),
                        'entityid' => new external_value(PARAM_INT, 'offeringview'),
                        'courseid' => new external_value(PARAM_INT, 'courseid'),
                        'enrolledseats' => new external_value(PARAM_INT, 'Enrolled seats'),
                    )
                )
            ),
            'events' => new external_multiple_structure(
                new external_single_structure(
                    array(
                        'id' => new external_value(PARAM_INT, 'id'),
                        'title' => new external_value(PARAM_RAW, 'name'),
                        'start' => new external_value(PARAM_INT, 'startdate'),
                        'end' => new external_value(PARAM_INT, 'enddate'),
                        'registrationstart' => new external_value(PARAM_INT, 'reg_startdate'),
                        'registrationend' => new external_value(PARAM_INT, 'reg_enddate'),
                        'eventtype' => new external_value(PARAM_RAW, 'eventtype'),
                        'attendeescount' => new external_value(PARAM_INT, 'attendeescount'),
                        'location' => new external_value(PARAM_RAW, 'location'),
                        'logo' => new external_value(PARAM_URL, 'logo'),
                        'certificateid'  => new external_value(PARAM_RAW, 'certificateid', VALUE_OPTIONAL),
                        'speakerslist' => new external_multiple_structure(
                            new external_single_structure(
                                array(
                                    'name' => new external_value(PARAM_RAW, 'name'),
                                )
                            )
                        ),
                        'bookseats' => new external_value(PARAM_RAW, 'bookseats'),

                        
                    )
                )
            ),
            'options' => new external_value(PARAM_RAW, 'The paging data for the service'),
            'orgoff' => new external_value(PARAM_BOOL, 'The paging data for the service'),
            'isorgoffortrainee' => new external_value(PARAM_BOOL, 'The paging data for the service'),
            'dataoptions' => new external_value(PARAM_RAW, 'The data for the service'),
            'totalcount' => new external_value(PARAM_INT, 'total number of challenges in result set'),
            'filterdata' => new external_value(PARAM_RAW, 'total number of challenges in result set'),
            'length' => new external_value(PARAM_RAW, 'total number of challenges in result set'),
            'userid' => new external_value(PARAM_INT, 'userid', VALUE_OPTIONAL),
            'traineeview' => new external_value(PARAM_BOOL, 'traineeview'),
            'records' => new external_single_structure(
                    array(
                        'hasevents' => new external_multiple_structure(
                            new external_single_structure(
                                array(
                                    'id' => new external_value(PARAM_INT, 'id'),
                                    'title' => new external_value(PARAM_RAW, 'name'),
                                    'description' => new external_value(PARAM_RAW, 'description'),
                                    'startdate' => new external_value(PARAM_RAW, 'startdate'),
                                    'enddate' => new external_value(PARAM_RAW, 'enddate'),
                                    'reg_startdate' => new external_value(PARAM_RAW, 'reg_startdate'),
                                    'reg_enddate' => new external_value(PARAM_RAW, 'reg_enddate'),
                                    'eventtype' => new external_value(PARAM_RAW, 'eventtype'),
                                    'attendeescount' => new external_value(PARAM_INT, 'attendeescount'),
                                    'action' => new external_value(PARAM_BOOL, 'action'),
                                    'editurl' => new external_value(PARAM_RAW, 'editurl'),
                                    'viewurl' => new external_value(PARAM_RAW, 'viewurl'),
                                    'eventseats' => new external_value(PARAM_RAW, 'eventseats'),
                                    'durationstatus' => new external_value(PARAM_RAW, 'eventseats', VALUE_OPTIONAL),
                                    'certificateid'  => new external_value(PARAM_RAW, 'certificateid'),
                                    'certificateurl'  => new external_value(PARAM_RAW, 'certificateurl'),
                                    'view_attendees_action' => new external_value(PARAM_BOOL, 'view_attendees_action'),
                                    'sellingprice' => new external_value(PARAM_RAW, 'sellingprice', VALUE_OPTIONAL),
                                    'canceloption' => new external_value(PARAM_BOOL, 'canceloption', VALUE_OPTIONAL),
                                    'productid' => new external_value(PARAM_INT, 'productid'),
                                    'eventprice' => new external_value(PARAM_INT, 'eventprice', VALUE_OPTIONAL),
                                    'disableallactions' => new external_value(PARAM_RAW, 'disableallactions', VALUE_OPTIONAL),
                                    'code' => new external_value(PARAM_RAW, 'code', VALUE_OPTIONAL),
                                    'currentuser' => new external_value(PARAM_RAW, 'currentuser', VALUE_OPTIONAL),
                                    'costtype' => new external_value(PARAM_RAW, 'costtype', VALUE_OPTIONAL),
                                    'cancelevent' => new external_value(PARAM_BOOL, 'cancelevent', VALUE_OPTIONAL),
                                    'hasenrollments' => new external_value(PARAM_INT, 'hasenrollments', VALUE_OPTIONAL),
                                    'iscancelled' => new external_value(PARAM_BOOL, 'iscancelled', VALUE_OPTIONAL),
                                    'cancelledrequestpending' => new external_value(PARAM_BOOL, 'cancelledrequestpending', VALUE_OPTIONAL),
                                    'cancelledstatustext' => new external_value(PARAM_RAW, 'cancelledstatustext', VALUE_OPTIONAL),
                                    'candelete' => new external_value(PARAM_BOOL, 'candelete', VALUE_OPTIONAL),
                                )
                            )
                        ),
                        'request_view' => new external_value(PARAM_INT, 'request_view', VALUE_OPTIONAL),
                        'noevents' => new external_value(PARAM_BOOL, 'nocourses', VALUE_OPTIONAL),
                        'totalevents' => new external_value(PARAM_INT, 'totalhalls', VALUE_OPTIONAL),
                        'length' => new external_value(PARAM_INT, 'length', VALUE_OPTIONAL),
                        'manageevents' => new external_value(PARAM_RAW, 'managehall', VALUE_OPTIONAL),
                    )
                )
        ]);
    }

    public function agenda_list_parameters() {
        return new external_function_parameters([
            'options' => new external_value(PARAM_RAW, 'The paging data for the service'),
            'dataoptions' => new external_value(PARAM_RAW, 'The data for the service'),
            'offset' => new external_value(PARAM_INT, 'Number of items to skip from the begging of the result set',
                VALUE_DEFAULT, 0),
            'limit' => new external_value(PARAM_INT, 'Maximum number of results to return',
                VALUE_DEFAULT, 0),
            'contextid' => new external_value(PARAM_INT, 'contextid'),
            'filterdata' => new external_value(PARAM_RAW, 'filters applied'),
        ]);
    }

    public function agenda_list($options, $dataoptions, $offset = 0, $limit = 0, $contextid, $filterdata) {
        global $DB, $PAGE;
        $PAGE->set_context(context_system::instance());
        // Parameter validation.
        $params = self::validate_parameters(
            self::agenda_list_parameters(),
            [
                'options' => $options,
                'dataoptions' => $dataoptions,
                'offset' => $offset,
                'limit' => $limit,
                'contextid' => $contextid,
                'filterdata' => $filterdata,
            ]
        );
        $offset = $params['offset'];
        $limit = $params['limit'];
        $filtervalues = json_decode($filterdata);
        $alloptions = json_decode($options);
        $stable = new \stdClass();
        $stable->thead = false;
        $stable->start = $offset;
        $stable->length = $limit;
        $stable->eventid = $alloptions->eventid;
        $agenda = (new local_events\events)->get_listof_agenda($stable, $filtervalues);
        $totalcount = $agenda['agendacount'];
        $data = array();
        if($totalcount>0){
            $renderer = $PAGE->get_renderer('local_events');
            $data = array_merge($data, $renderer->list_agenda($stable,$filtervalues));
        }
        return [
            'totalcount' => $totalcount,
            'length' => $totalcount,
            'filterdata' => $filterdata,
            'records' =>$data,
            'options' => $options,
            'dataoptions' => $dataoptions,
        ];
    }

    public function agenda_list_returns() {
        return new external_single_structure([
            'options' => new external_value(PARAM_RAW, 'The paging data for the service'),
            'dataoptions' => new external_value(PARAM_RAW, 'The data for the service'),
            'totalcount' => new external_value(PARAM_INT, 'total number of competencies in result set'),
            'filterdata' => new external_value(PARAM_RAW, 'The data for the service'),
            'records' => new external_multiple_structure(
                            new external_single_structure(
                                array(
                                    'id' => new external_value(PARAM_INT, 'id'),
                                    'eventid'  => new external_value(PARAM_INT, 'eventid'),
                                    'title' => new external_value(PARAM_RAW, 'name'),
                                    'speaker' => new external_value(PARAM_RAW, 'speaker'),
                                    'time' => new external_value(PARAM_RAW, 'time'),
                                    'day' => new external_value(PARAM_RAW, 'day'),
                                    'action'  => new external_value(PARAM_BOOL, 'action'),
                                )
                            )
            )
        ]);
    }

    public function delete_agenda_parameters(){
        return new external_function_parameters(
            array(
                'action' => new external_value(PARAM_ACTION, 'Action of the event', false),
                'agendaid' => new external_value(PARAM_INT, 'ID of the record', 0),
                'eventid' => new external_value(PARAM_INT, 'ID of the record', 0),
                'confirm' => new external_value(PARAM_BOOL, 'Confirm', false),
            )
        );
    }

    public static function delete_agenda($action, $agendaid, $eventid, $confirm) {
        global $DB,$CFG,$USER;
        try {
            if ($confirm) { 
                 // notification events  onchange
                 $attendees = $DB->get_records('local_event_attendees', ['eventid' => $eventid]);          
                 foreach( $attendees as  $attendee){               
                     if($attendee->userid==0){
                         $touser=get_admin();
                         $touser->firstname=$attendee->name;
                         $touser->lastname=$attendee->name;
                         $touser->email=$attendee->email;
                     }
                     else{
                         $touser=$DB->get_record('user',array('id'=>$attendee->userid));
                     }
                     $thispageurl = new \moodle_url('/local/events/view.php?id='.$eventid);                 
                     $row1=[];
                     $row1['RelatedModuleName']=$DB->get_field('local_events','title',array('id'=>$eventid));
                     $row1['RelatedModulesLink']=$thispageurl;
                     $localuserrecord = $DB->get_record('local_users',['userid'=> $attendee->userid]);
                     $fname = ($localuserrecord)? (($localuserrecord->lang  == 'ar') ? $localuserrecord->firstnamearabic.' '.$localuserrecord->middlenamearabic.' '.$localuserrecord->thirdnamearabic.' '.$localuserrecord->lastnamearabic  : $localuserrecord->firstname.' '.$localuserrecord->middlenameen.' '.$localuserrecord->thirdnameen.' '.$localuserrecord->lastname) :fullname($touser);
                     $row1['FullName']=$fname;                         
                     $myobject=(new \local_events\notification);
                     $myobject->event_notification('events_onchange',$touser, $USER,$row1,$waitinglistid=0);                   
                 }
                $DB->delete_records('local_eventagenda', ['id' => $agendaid, 'eventid' => $eventid]);
                $return = true;
            }else {
                $return = false;
            }
        } catch (dml_exception $ex) {
            print_error('deleteerror', 'local_events');
            $return = false;
        }
        return $return;
    }

    public static function delete_agenda_returns() {
        return new external_value(PARAM_BOOL, 'return');
    }

    public function attendees_list_parameters() {
        return new external_function_parameters([
            'options' => new external_value(PARAM_RAW, 'The paging data for the service'),
            'dataoptions' => new external_value(PARAM_RAW, 'The data for the service'),
            'offset' => new external_value(PARAM_INT, 'Number of items to skip from the begging of the result set',
                VALUE_DEFAULT, 0),
            'limit' => new external_value(PARAM_INT, 'Maximum number of results to return',
                VALUE_DEFAULT, 0),
            'contextid' => new external_value(PARAM_INT, 'contextid'),
            'filterdata' => new external_value(PARAM_RAW, 'filters applied'),
        ]);
    }

    public function attendees_list($options, $dataoptions, $offset = 0, $limit = 0, $contextid, $filterdata) {
        global $DB, $PAGE;
        $PAGE->set_context(context_system::instance());
        // Parameter validation.
        $params = self::validate_parameters(
            self::attendees_list_parameters(),
            [
                'options' => $options,
                'dataoptions' => $dataoptions,
                'offset' => $offset,
                'limit' => $limit,
                'contextid' => $contextid,
                'filterdata' => $filterdata,
            ]
        );
        $offset = $params['offset'];
        $limit = $params['limit'];
        $filtervalues = json_decode($filterdata);
        $alloptions = json_decode($options);
        $stable = new \stdClass();
        $stable->thead = false;
        $stable->start = $offset;
        $stable->length = $limit;
        $stable->eventid = $alloptions->eventid;
        $attendees = (new local_events\events)->get_listof_attendees($stable, $filtervalues);
        $totalcount = $attendees['attendeescount'];
        $data = array();
        //if($totalcount>0){
            $renderer = $PAGE->get_renderer('local_events');
            $data = array_merge($data, $renderer->list_attendees($stable,$filtervalues));
        //}
        return [
           // 'availableseats' => $data['availableseats'],
            'totalcount' => $totalcount,
            'length' => $totalcount,
            'filterdata' => $filterdata,
            'records' =>$data,
            'options' => $options,
            'dataoptions' => $dataoptions,
        ];
    }


    public function attendees_list_returns(){
        return new external_single_structure([
            //'availableseats' => new external_value(PARAM_INT, 'availableseats'),
            'options' => new external_value(PARAM_RAW, 'The paging data for the service'),
            'dataoptions' => new external_value(PARAM_RAW, 'The data for the service'),
            'totalcount' => new external_value(PARAM_INT, 'total number of competencies in result set'),
            'filterdata' => new external_value(PARAM_RAW, 'The data for the service'),
            'records' => new external_multiple_structure(
                new external_single_structure(
                    array(
                        'id' => new external_value(PARAM_INT, 'id'),
                        'eventid'  => new external_value(PARAM_INT, 'eventid'),
                        'name' => new external_value(PARAM_RAW, 'name'),
                        'email' => new external_value(PARAM_RAW, 'email'),
                        'attenddeid' => new external_value(PARAM_RAW, 'attenddeid'),
                        'organization' => new external_value(PARAM_RAW, 'organization'),
                        'linkedprofile' =>  new external_value(PARAM_RAW, 'linkedprofile'),
                        'action'  => new external_value(PARAM_BOOL, 'action'),
                        'status' => new external_value(PARAM_RAW, 'status'),
                        'delete'  => new external_value(PARAM_BOOL, 'delete'),
                        'eventprice' => new external_value(PARAM_INT, 'eventprice'),
                        'replacementfee' => new external_value(PARAM_INT, 'replacementfee'),
                        'remainingdays' => new external_value(PARAM_INT, 'remainingdays'),
                        'replacebuttonview' => new external_value(PARAM_RAW, 'replacebuttonview'),
                        'cancelbuttonview' => new external_value(PARAM_RAW, 'cancelbuttonview'),
                        'eventname' => new external_value(PARAM_RAW, 'eventname'),
                        'currentuserisadmin' => new external_value(PARAM_INT, 'currentuserisadmin'),
                        'eventstartdate' => new external_value(PARAM_INT, 'eventstartdate'),
                        'productid' => new external_value(PARAM_INT, 'productid'),
                        'userid' => new external_value(PARAM_INT, 'userid'),
                        'id_number' => new external_value(PARAM_RAW, 'id_number'),
                        'orgofficialenrolled' => new external_value(PARAM_INT, 'orgofficialenrolled'),
                        'enrolledrole'=> new external_value(PARAM_RAW, 'enrolledrole', VALUE_OPTIONAL),
                        'sellingprice'=> new external_value(PARAM_RAW, 'sellingprice', VALUE_OPTIONAL),
                        'currentuserorgoff'=> new external_value(PARAM_INT, 'sellingprice', VALUE_OPTIONAL),
                        'certificateid'=> new external_value(PARAM_RAW, 'certificateid', VALUE_OPTIONAL),
                        'certificateurl'=> new external_value(PARAM_RAW, 'certificateurl', VALUE_OPTIONAL),
                        'disableallactions'=> new external_value(PARAM_RAW, 'disableallactions', VALUE_OPTIONAL),
                        'iswaitingforapproval' => new external_value(PARAM_RAW, 'iswaitingforapproval', VALUE_OPTIONAL),

                    )
                )
            )
        ]);
    }

    public function delete_attendees_parameters() {
        return new external_function_parameters(
            array(
                'action' => new external_value(PARAM_ACTION, 'Action of the event', false),
                'attid' => new external_value(PARAM_INT, 'ID of the record', 0),
                'eventid' => new external_value(PARAM_INT, 'ID of the record', 0),
                'confirm' => new external_value(PARAM_BOOL, 'Confirm', false),
            )
        );

    }

    public function delete_attendees($action, $attid, $eventid, $confirm) {
        global $DB, $USER;
        $systemcontext = context_system::instance();
        try {
            if ($confirm) {
                $attendeedata = $DB->get_record('local_event_attendees', ['id' => $attid, 'eventid' => $eventid]);
                $getdata=$DB->get_record('tool_org_order_seats', array('tablename'=>'local_events','fieldname'=>'id','fieldid'=>$eventid,'orguserid'=>$attendeedata->usercreated),'id,purchasedseats,availableseats,approvalseats');
                if($getdata) {
                    if($getdata->availableseats < $getdata->approvalseats) {
                        $getdata->availableseats = $getdata->availableseats+1;
                        $getdata->timemodified = time();
                        $getdata->usermodified = $USER->id;
                        $id=$DB->update_record('tool_org_order_seats', $getdata);
                    }
                }
                $DB->delete_records('local_event_attendees', ['id' => $attid, 'eventid' => $eventid]);
                //(new \tool_product\product)->upadte_availableseats('local_events', 'id', $eventid, +1);
                $return = true;
            }else {
                $return = false;
            }
        } catch (dml_exception $ex) {
            print_error('deleteerror', 'local_events');
            $return = false;
        }
      
        return $return;
    }

    public function delete_attendees_returns() {
        return new external_value(PARAM_BOOL, 'return');
    }

    public function view_attendee_parameters() {
        return new external_function_parameters(
            array(
                'attid' => new external_value(PARAM_INT, 'ceid', 0),
                'eventid' => new external_value(PARAM_INT, 'evdtype', 0),
                )
        );
    }

    public function view_attendee($attid, $eventid ) {
        global $DB, $USER;
        $params = self::validate_parameters(self::view_attendee_parameters(),
                                    ['attid' => $attid, 'eventid' => $eventid]);
                                    
        $data = (new local_events\events)->attendee_info($attid, $eventid);
        return [
            'options' => $data,
        ];
    }

    public function view_attendee_returns() {
        return new external_single_structure([
            'options' => new external_value(PARAM_RAW, 'The paging data for the service'),
        ]);
    }

    public function view_agenda_parameters() {
        return new external_function_parameters(
            array(
                'agdid' => new external_value(PARAM_INT, 'ceid', 0),
                'eventid' => new external_value(PARAM_INT, 'evdtype', 0),
                )
        );
    }

    public function view_agenda($agdid, $eventid ) {
        global $DB, $USER;
        $params = self::validate_parameters(self::view_agenda_parameters(),
                                    ['agdid' => $agdid, 'eventid' => $eventid]);
                                    
        $data = (new local_events\events)->agenda_info($agdid, $eventid);
        return [
            'options' => $data,
        ];
    }

    public function view_agenda_returns() {
        return new external_single_structure([
            'options' => new external_value(PARAM_RAW, 'The paging data for the service'),
        ]);
    }
    //view_agenda
    public function speakers_list_parameters() {
        return new external_function_parameters([
            'options' => new external_value(PARAM_RAW, 'The paging data for the service'),
            'dataoptions' => new external_value(PARAM_RAW, 'The data for the service'),
            'offset' => new external_value(PARAM_INT, 'Number of items to skip from the begging of the result set',
                VALUE_DEFAULT, 0),
            'limit' => new external_value(PARAM_INT, 'Maximum number of results to return',
                VALUE_DEFAULT, 0),
            'contextid' => new external_value(PARAM_INT, 'contextid'),
            'filterdata' => new external_value(PARAM_RAW, 'filters applied'),
        ]);

    }

    public function speakers_list($options, $dataoptions, $offset = 0, $limit = 0, $contextid, $filterdata) {
        global $DB, $PAGE;
        $PAGE->set_context(context_system::instance());
        // Parameter validation.
        $params = self::validate_parameters(
            self::speakers_list_parameters(),
            [
                'options' => $options,
                'dataoptions' => $dataoptions,
                'offset' => $offset,
                'limit' => $limit,
                'contextid' => $contextid,
                'filterdata' => $filterdata,
            ]
        );
        $offset = $params['offset'];
        $limit = $params['limit'];
        $filtervalues = json_decode($filterdata);
        $alloptions = json_decode($options);
        $stable = new \stdClass();
        $stable->thead = false;
        $stable->start = $offset;
        $stable->length = $limit;
        $stable->eventid = $alloptions->eventid;
        $speaker = (new local_events\events)->get_listof_speakers($stable, $filtervalues);
        $totalcount = $speaker['speakerscount'];
        $data = array();
        if($totalcount > 0){
            $renderer = $PAGE->get_renderer('local_events');
            $data = array_merge($data, $renderer->list_speakers($stable,$filtervalues));
        }
        return [
            'totalcount' => $totalcount,
            'length' => $totalcount,
            'filterdata' => $filterdata,
            'records' =>$data,
            'options' => $options,
            'dataoptions' => $dataoptions,
        ];
    }

    public function speakers_list_returns() {
        return new external_single_structure([
            'options' => new external_value(PARAM_RAW, 'The paging data for the service'),
            'dataoptions' => new external_value(PARAM_RAW, 'The data for the service'),
            'totalcount' => new external_value(PARAM_INT, 'total number of competencies in result set'),
            'filterdata' => new external_value(PARAM_RAW, 'The data for the service'),
            'records' => new external_multiple_structure(
                            new external_single_structure(
                                array(
                                    'id' => new external_value(PARAM_INT, 'id'),
                                    'eventid'  => new external_value(PARAM_INT, 'eventid'),
                                    'eventname' => new external_value(PARAM_RAW, 'eventname'),
                                    'speakername' => new external_value(PARAM_RAW, 'speakername'),
                                    'specialist' => new external_value(PARAM_RAW, 'specialist'),
                                    'linked_profile' => new external_value(PARAM_RAW, 'linked_profile'),
                                    'action' => new external_value(PARAM_RAW, 'action'),
                                )
                            )
            )
        ]);
    }

    public function delete_speaker_parameters() {
        return new external_function_parameters(
            array(
                'action' => new external_value(PARAM_ACTION, 'Action of the event', false),
                'speakerid' => new external_value(PARAM_INT, 'ID of the record', 0),
                'eventid' => new external_value(PARAM_INT, 'ID of the record', 0),
                'confirm' => new external_value(PARAM_BOOL, 'Confirm', false),
            )
        );
    }
    public function delete_speaker($action, $speakerid, $eventid, $confirm) {
        global $DB,$CFG,$USER;
        try {
            if ($confirm) {
                 // notification events  onchange
                 $attendees = $DB->get_records('local_event_attendees', ['eventid' => $eventid]);          
                 foreach( $attendees as  $attendee){               
                     if($attendee->userid==0){
                         $touser=get_admin();
                         $touser->firstname=$attendee->name;
                         $touser->lastname=$attendee->name;
                         $touser->email=$attendee->email;
                     }
                     else{
                         $touser=$DB->get_record('user',array('id'=>$attendee->userid));
                     }
                     $thispageurl = new \moodle_url('/local/events/view.php?id='.$eventid);                
                     $row1=[];
                     $row1['RelatedModuleName']=$DB->get_field('local_events','title',array('id'=>$eventid));;
                     $row1['RelatedModulesLink']=$thispageurl;
                     $localuserrecord = $DB->get_record('local_users',['userid'=> $attendee->userid]);
                     $fname = ($localuserrecord)? (($localuserrecord->lang == 'ar') ? $localuserrecord->firstnamearabic.' '.$localuserrecord->middlenamearabic.' '.$localuserrecord->thirdnamearabic.' '.$localuserrecord->lastnamearabic  : $localuserrecord->firstname.' '.$localuserrecord->middlenameen.' '.$localuserrecord->thirdnameen.' '.$localuserrecord->lastname) :fullname($touser);
                     $row1['FullName']=$fname;            
                     $myobject=(new \local_events\notification);
                     $myobject->event_notification('events_onchange',$touser, $USER,$row1,$waitinglistid=0);
                   
                 }
                $DB->delete_records('local_event_speakers', ['id' => $speakerid, 'eventid' => $eventid]);
                $return = true;
            }else {
                $return = false;
            }
        } catch (dml_exception $ex) {
            print_error('deleteerror', 'local_events');
            $return = false;
        }
      
        return $return;
    }

    public function delete_speaker_returns() {
        return new external_value(PARAM_BOOL, 'return');
    }

    public function view_speaker_parameters() {
        return new external_function_parameters(
            array(
                'speakerid' => new external_value(PARAM_INT, 'speakerid', 0),
                'eventid' => new external_value(PARAM_INT, 'eventid', 0),
                )
        );
    }

    public function view_speaker($speakerid, $eventid) {
        global $DB, $USER;
        $params = self::validate_parameters(self::view_speaker_parameters(),
                                    ['speakerid' => $speakerid, 'eventid' => $eventid]);
                                    
        $data = (new local_events\events)->speaker_view($speakerid, $eventid);
        return [
            'options' => $data,
        ];
    }

    public function view_speaker_returns() {
        return new external_single_structure([
            'options' => new external_value(PARAM_RAW, 'The paging data for the service'),
        ]);
    }

    public function sponsors_list_parameters() {
        return new external_function_parameters([
            'options' => new external_value(PARAM_RAW, 'The paging data for the service'),
            'dataoptions' => new external_value(PARAM_RAW, 'The data for the service'),
            'offset' => new external_value(PARAM_INT, 'Number of items to skip from the begging of the result set',
                VALUE_DEFAULT, 0),
            'limit' => new external_value(PARAM_INT, 'Maximum number of results to return',
                VALUE_DEFAULT, 0),
            'contextid' => new external_value(PARAM_INT, 'contextid'),
            'filterdata' => new external_value(PARAM_RAW, 'filters applied'),
        ]);
    }

    public function sponsors_list($options, $dataoptions, $offset = 0, $limit = 0, $contextid, $filterdata) {
        global $DB, $PAGE;
        // Parameter validation.
       // require_login();
        $PAGE->set_context(context_system::instance());
        $params = self::validate_parameters(
            self::sponsors_list_parameters(),
            [
                'options' => $options,
                'dataoptions' => $dataoptions,
                'offset' => $offset,
                'limit' => $limit,
                'contextid' => $contextid,
                'filterdata' => $filterdata,
            ]
        );
        $offset = $params['offset'];
        $limit = $params['limit'];
        $filtervalues = json_decode($filterdata);
        $alloptions = json_decode($options);
        $stable = new \stdClass();
        $stable->thead = false;
        $stable->start = $offset;
        $stable->length = $limit;
        $stable->eventid = $alloptions->eventid;
        $sponsor = (new local_events\events)->get_listof_sponsors($stable, $filtervalues);
        $totalcount = $sponsor['sponsorscount'];
        $data = array();
        if($totalcount>0){
            $renderer = $PAGE->get_renderer('local_events');
            $data = array_merge($data, $renderer->list_sponsors($stable,$filtervalues));
        }
        return [
            'totalcount' => $totalcount,
            'length' => $totalcount,
            'filterdata' => $filterdata,
            'records' =>$data,
            'options' => $options,
            'dataoptions' => $dataoptions,
        ];
    }

    public function sponsors_list_returns() {
        return new external_single_structure([
            'options' => new external_value(PARAM_RAW, 'The paging data for the service'),
            'dataoptions' => new external_value(PARAM_RAW, 'The data for the service'),
            'totalcount' => new external_value(PARAM_INT, 'total number of competencies in result set'),
            'filterdata' => new external_value(PARAM_RAW, 'The data for the service'),
            'records' => new external_multiple_structure(
                            new external_single_structure(
                                array(
                                    'id' => new external_value(PARAM_INT, 'id'),
                                    'eventid'  => new external_value(PARAM_INT, 'eventid'),
                                    'sponsorname' => new external_value(PARAM_RAW, 'sponsorname'),
                                    'category' => new external_value(PARAM_RAW, 'category'),
                                    'amount' => new external_value(PARAM_RAW, 'amount'),
                                    'logo' => new external_value(PARAM_RAW, 'logo'),
                                    'action' => new external_value(PARAM_BOOL, 'action'),
                                )
                            )
            )
        ]);
    }

    public function delete_sponsor_parameters() {
        return new external_function_parameters(
            array(
                'action' => new external_value(PARAM_ACTION, 'Action of the event', false),
                'sponsorid' => new external_value(PARAM_INT, 'ID of the record', 0),
                'eventid' => new external_value(PARAM_INT, 'ID of the record', 0),
                'confirm' => new external_value(PARAM_BOOL, 'Confirm', false),
            )
        );
    }

    public function delete_sponsor($action, $sponsorid, $eventid, $confirm) {
        global $DB,$USER,$CFG;
        try {
            if ($confirm) {
                 // notification events  onchange
                 
                 $attendees = $DB->get_records('local_event_attendees', ['eventid' => $eventid]);          
                 foreach( $attendees as  $attendee){               
                     if($attendee->userid==0){
                         $touser=get_admin();
                         $touser->firstname=$attendee->name;
                         $touser->lastname=$attendee->name;
                         $touser->email=$attendee->email;
                     }
                     else{
                         $touser=$DB->get_record('user',array('id'=>$attendee->userid));
                     }
                     $thispageurl = new \moodle_url('/local/events/view.php?id='.$eventid);                 
                     $row1=[];
                     $row1['RelatedModuleName']=$DB->get_field('local_events','title',array('id'=>$data->eventid));
                     $row1['RelatedModulesLink']=$thispageurl;
                     $localuserrecord = $DB->get_record('local_users',['userid'=> $attendee->userid]);
                     $fname = ($localuserrecord)? (($localuserrecord->lang == 'ar') ? $localuserrecord->firstnamearabic.' '.$localuserrecord->middlenamearabic.' '.$localuserrecord->thirdnamearabic.' '.$localuserrecord->lastnamearabic  : $localuserrecord->firstname.' '.$localuserrecord->middlenameen.' '.$localuserrecord->thirdnameen.' '.$localuserrecord->lastname) :fullname($touser);
                     $row1['FullName']=$fname;                        
                     $myobject=(new \local_events\notification);
                     $myobject->event_notification('events_onchange',$touser, $USER,$row1,$waitinglistid=0);
                   
                 }
                $DB->delete_records('local_event_sponsors', ['id' => $sponsorid, 'eventid' => $eventid]);
                $return = true;
            } else {
                $return = false;
            }
        } catch (dml_exception $ex) {
            print_error('deleteerror', 'local_events');
            $return = false;
        }
        return $return;
    }

    public function delete_sponsor_returns() {
        return new external_value(PARAM_BOOL, 'return');
    }

    public function view_sponsor_parameters() {
        return new external_function_parameters(
            array(
                'sponsorid' => new external_value(PARAM_INT, 'sponsorid', 0),
                'eventid' => new external_value(PARAM_INT, 'eventid', 0),
                )
        );
    }

    public function view_sponsor($sponsorid, $eventid) {
        global $DB, $USER;
        $params = self::validate_parameters(self::view_sponsor_parameters(),
                                    ['sponsorid' => $sponsorid, 'eventid' => $eventid]);                            
        $data = (new local_events\events)->sponsor_view($sponsorid, $eventid);
        return [
            'options' => $data,
        ];
    }

    public function view_sponsor_returns() {
        return new external_single_structure([
            'options' => new external_value(PARAM_RAW, 'The paging data for the service'),
        ]);
    }

    public function partners_list_parameters() {
        return new external_function_parameters([
            'options' => new external_value(PARAM_RAW, 'The paging data for the service'),
            'dataoptions' => new external_value(PARAM_RAW, 'The data for the service'),
            'offset' => new external_value(PARAM_INT, 'Number of items to skip from the begging of the result set',
                VALUE_DEFAULT, 0),
            'limit' => new external_value(PARAM_INT, 'Maximum number of results to return',
                VALUE_DEFAULT, 0),
            'contextid' => new external_value(PARAM_INT, 'contextid'),
            'filterdata' => new external_value(PARAM_RAW, 'filters applied'),
        ]);
    }

    public function partners_list($options, $dataoptions, $offset = 0, $limit = 0, $contextid, $filterdata) {
        global $DB, $PAGE;
        // Parameter validation.
        $PAGE->set_context(context_system::instance());
        $params = self::validate_parameters(
            self::partners_list_parameters(),
            [
                'options' => $options,
                'dataoptions' => $dataoptions,
                'offset' => $offset,
                'limit' => $limit,
                'contextid' => $contextid,
                'filterdata' => $filterdata,
            ]
        );
        $offset = $params['offset'];
        $limit = $params['limit'];
        $filtervalues = json_decode($filterdata);
        $alloptions = json_decode($options);
        $stable = new \stdClass();
        $stable->thead = false;
        $stable->start = $offset;
        $stable->length = $limit;
        $stable->eventid = $alloptions->eventid;
        $partners = (new local_events\events)->get_listof_partners($stable, $filtervalues);
        $totalcount = $partners['partnerscount'];
        $data = array();
        if($totalcount>0){
            $renderer = $PAGE->get_renderer('local_events');
            $data = array_merge($data, $renderer->list_partners($stable,$filtervalues));
        }
        return [
            'totalcount' => $totalcount,
            'length' => $totalcount,
            'filterdata' => $filterdata,
            'records' => $data,
            'options' => $options,
            'dataoptions' => $dataoptions,
        ];
    }

    public function partners_list_returns() {
        return new external_single_structure([
            'options' => new external_value(PARAM_RAW, 'The paging data for the service'),
            'dataoptions' => new external_value(PARAM_RAW, 'The data for the service'),
            'totalcount' => new external_value(PARAM_INT, 'total number of competencies in result set'),
            'filterdata' => new external_value(PARAM_RAW, 'The data for the service'),
            'records' => new external_multiple_structure(
                            new external_single_structure(
                                array(
                                    'id' => new external_value(PARAM_INT, 'id'),
                                    'eventid'  => new external_value(PARAM_INT, 'eventid'),
                                    'partnername'  => new external_value(PARAM_RAW, 'partnername'),
                                    'description'  => new external_value(PARAM_RAW, 'description'),
                                    'descriptionstring'  => new external_value(PARAM_RAW, 'descriptionstring'),
                                    'isdescription'  => new external_value(PARAM_BOOL, 'isdescription'),
                                    'strlength'  => new external_value(PARAM_BOOL, 'strlength'),
                                    'action' => new external_value(PARAM_BOOL, 'action'),
                                    'logo' => new external_value(PARAM_RAW, 'logo'),
                                )
                            )
            )
        ]);
    }

    public function delete_partner_parameters() {
        return new external_function_parameters(
            array(
                'action' => new external_value(PARAM_ACTION, 'Action of the event', false),
                'partnerid' => new external_value(PARAM_INT, 'ID of the record', 0),
                'eventid' => new external_value(PARAM_INT, 'ID of the record', 0),
                'confirm' => new external_value(PARAM_BOOL, 'Confirm', false),
            )
        );
    }

    public function delete_partner($action, $partnerid, $eventid, $confirm) {
        global $DB,$CFG,$USER;
        try {
            if ($confirm) {
                 // notification events  onchange                
                $attendees = $DB->get_records('local_event_attendees', ['eventid' => $eventid]);          
                 foreach( $attendees as  $attendee){               
                     if($attendee->userid==0){
                         $touser=get_admin();
                         $touser->firstname=$attendee->name;
                         $touser->lastname=$attendee->name;
                         $touser->email=$attendee->email;
                     }
                     else{
                         $touser=$DB->get_record('user',array('id'=>$attendee->userid));
                     }
                     $thispageurl = new \moodle_url('/local/events/view.php?id='.$eventid);                 
                     $row1=[];
                     $row1['RelatedModuleName']=$DB->get_field('local_events','title',array('id'=>$eventid));
                     $row1['RelatedModulesLink']=$thispageurl;
                     $localuserrecord = $DB->get_record('local_users',['userid'=> $attendee->userid]);
                     $fname = ($localuserrecord)? (($localuserrecord->lang == 'ar') ? $localuserrecord->firstnamearabic.' '.$localuserrecord->middlenamearabic.' '.$localuserrecord->thirdnamearabic.' '.$localuserrecord->lastnamearabic  : $localuserrecord->firstname.' '.$localuserrecord->middlenameen.' '.$localuserrecord->thirdnameen.' '.$localuserrecord->lastname) :fullname($touser);
                     $row1['FullName']=$fname;                         
                     $myobject=(new \local_events\notification);
                     $myobject->event_notification('events_onchange',$touser, $USER,$row1,$waitinglistid=0);                   
                 }
                $DB->delete_records('local_event_partners', ['id' => $partnerid, 'eventid' => $eventid]);
                $return = true;
            } else {
                $return = false;
            }
        } catch (dml_exception $ex) {
            print_error('deleteerror', 'local_events');
            $return = false;
        }
        return $return;
    }

    public function delete_partner_returns() {
        return new external_value(PARAM_BOOL, 'return');
    }

    public function view_partner_parameters() {
        return new external_function_parameters(
            array(
                'partnerid' => new external_value(PARAM_INT, 'partnerid', 0),
                'eventid' => new external_value(PARAM_INT, 'eventid', 0),
               )
        );
    }

    public function view_partner($partnerid, $eventid) {
        global $DB, $USER;
        $params = self::validate_parameters(self::view_partner_parameters(),
                                    ['partnerid' => $partnerid, 'eventid' => $eventid]);                           
        $data = (new local_events\events)->partner_view($partnerid, $eventid);
        return [
            'options' => $data,
        ];
    }

    public function view_partner_returns() {
        return new external_single_structure([
            'options' => new external_value(PARAM_RAW, 'The paging data for the service'),
        ]);
    }

    public function form_selector_parameters() {
        $query = new external_value(PARAM_RAW, 'search query');
        $type = new external_value(PARAM_ALPHANUMEXT, 'Type of data', VALUE_REQUIRED);
        $listid = new external_value(PARAM_INT, 'The sector id', VALUE_OPTIONAL,0);
        $module = new external_value(PARAM_ALPHANUMEXT, 'The module', VALUE_OPTIONAL,0);
        $valselect = new external_value(PARAM_INT, 'Program id');
        $params = array(
            'query' => $query,
            'type' => $type,
            'listid' => $listid,
            'module' => $module,
            'valselect' => $valselect,
        );
        return new external_function_parameters($params);
    }

    public function form_selector($query=null,$type=null, $listid=0, $module=null, $valselect=null) {
        global $PAGE;
    
        $params = array(         
            'type' => $type,
            'listid' => $listid,
            'query' => $query,
            'module' => $module,
            'valselect' => $valselect
        );
        // var_dump($module);
         //var_dump($valselect); exit;
        $params = self::validate_parameters(self::form_selector_parameters(), $params);
        switch($params['type']) {
            case 'speakerlist':
                $list = (new local_events\events)->get_speakerlist($params['listid'], $params['module'], $params['query']);
            break;
            case 'sponsorlist':
                $list = (new local_events\events)->get_sponsorlist($params['listid'], $params['module'], $params['query']);
            break;
            case 'partnerlist':
               // var_dump($params['valselect']); 
                $list = (new local_events\events)->get_partnerlist($params['listid'], $params['module'], $params['valselect'], $params['query']);
            break;
            case 'userlist':
                $list = (new local_events\events)->get_userlist($params['listid'],$params['query']);
            break;
            case 'managerlist':
                $list = (new local_events\events)->get_managerlist($params['query']);
            break;
            case 'halllist':
                $list = (new local_events\events)->get_halllist($params['query']);
            break;
            case 'examhalls':
                $list = (new local_events\events)->get_examhalls($params['query']);
            break;
            case 'agenda_speakerlist':
                $list = (new local_events\events)->agenda_speakerlist($params['listid']);
        }
        return ['status' => true, 'data' => $list];
    }

    public function form_selector_returns() {
        return new external_single_structure(
            array(
                'status' => new external_value(PARAM_INT, 'status: true if success'),
                'data' => new external_multiple_structure(new external_single_structure(
                    array(
                        'id' => new external_value(PARAM_INT, 'status: true if success'),
                        'fullname' => new external_value(PARAM_RAW, 'status: true if success',VALUE_OPTIONAL))
                    )) 
            )
        );
    }

    public function financeamount_parameters() {
        $type = new external_value(PARAM_ALPHANUMEXT, 'Type of data', VALUE_REQUIRED);
        $listid = new external_value(PARAM_INT, 'The sector id', VALUE_OPTIONAL,0);
        $itemid = new external_value(PARAM_INT, 'The item id', VALUE_OPTIONAL,0);
        $params = array(
            'type' => $type,
            'listid' => $listid,
            'itemid' => $itemid
        );
        return new external_function_parameters($params);
    }

    public function financeamount($type,$listid=0,$itemid=0) {
        global $PAGE;
        $params = array(
            'type' => $type,      
            'listid' => $listid,
            'itemid' => $itemid
        );
        $params = self::validate_parameters(self::financeamount_parameters(), $params);
        $amount = (new local_events\events)->get_itemamount($params['type'],$params['listid'], $params['itemid']);
        return $amount;
    }

    public function financeamount_returns() {
        return new external_value(PARAM_INT, 'return');
    }

    public function speaker_formdata_parameters() {
        return new external_function_parameters(
            array(
                'speakerid' => new external_value(PARAM_INT, 'speakerid', 0),
               )
        );
    }

    public function speaker_formdata($speakerid) {
        global $DB, $USER;
        $params = self::validate_parameters(self::speaker_formdata_parameters(),
                                    ['speakerid' => $speakerid]);                           
        $data = (new local_events\events)->speaker_formdata($speakerid);
        return [
            'options' => $data,
        ];
    }

    public function speaker_formdata_returns() {
        return new external_single_structure([
            'options' => new external_value(PARAM_RAW, 'The paging data for the service'),
        ]);
    }


    public static function eventincome_parameters() {
        return new external_function_parameters([
            'options' => new external_value(PARAM_RAW, 'The paging data for the service'),
            'dataoptions' => new external_value(PARAM_RAW, 'The data for the service'),
            'offset' => new external_value(PARAM_INT, 'Number of items to skip from the begging of the result set',
                VALUE_DEFAULT, 0),
            'limit' => new external_value(PARAM_INT, 'Maximum number of results to return',
                VALUE_DEFAULT, 0),
            'contextid' => new external_value(PARAM_INT, 'contextid'),
            'filterdata' => new external_value(PARAM_RAW, 'filters applied'),
        ]);
    }

    public static function eventincome($options, $dataoptions, $offset = 0, $limit = 0, $contextid, $filterdata) {
        global $DB, $PAGE, $CFG;
        $params = self::validate_parameters(
            self::eventincome_parameters(),
            [
                'options' => $options,
                'dataoptions' => $dataoptions,
                'offset' => $offset,
                'limit' => $limit,
                'contextid' => $contextid,
                'filterdata' => $filterdata,
            ]
        );
        $offset = $params['offset'];
        $limit = $params['limit'];
        $filtervalues = json_decode($filterdata);
        $decodeddataoptions = json_decode($dataoptions);
        $stable = new \stdClass();
        $stable->thead = false;
        $stable->start = $offset;
        $stable->length = $limit;
        $stable->class = 'income';
        $stable->eventid = $decodeddataoptions->eventid;
        $data = (new local_events\events)->get_listof_income($stable, $filtervalues);
        $totalcount = $data['totalcount'];
        $manageactions = $data['manageactions'];
    //  var_dump($totalcount); exit;
        return [
            'totalcount' => $totalcount,
            'length' => $totalcount,
            'filterdata' => $filterdata,
            'records' =>$data,
            'options' => $options,
            'dataoptions' => $dataoptions,
            'url' => $CFG->wwwroot,
            'manageactions' => $manageactions, 
        ];
    }

  /**
   * Returns description of method result value
   * @return external_description
   */

    public static function eventincome_returns () {
        return new external_single_structure([
          'options' => new external_value(PARAM_RAW, 'The paging data for the service'),
          'url' => new external_value(PARAM_RAW, 'url'),
          'manageactions' => new external_value(PARAM_BOOL, 'total number of challenges in result set'),
          'dataoptions' => new external_value(PARAM_RAW, 'The data for the service'),
          'totalcount' => new external_value(PARAM_INT, 'total number of challenges in result set'),
          'filterdata' => new external_value(PARAM_RAW, 'total number of challenges in result set'),
          'length' => new external_value(PARAM_RAW, 'total number of challenges in result set'),
          'records' => new external_single_structure(
                  array(
                      'incomelist' => new external_multiple_structure(
                          new external_single_structure(
                              array(
                                  'id' => new external_value(PARAM_INT, 'id'),
                                  'itemname'  => new external_value(PARAM_RAW, 'itemname'),
                                  'amount'  => new external_value(PARAM_RAW, 'itemname'),
                                  'type'  => new external_value(PARAM_RAW, 'type'),
                              )
                          )
                      ),                   
                      'request_view' => new external_value(PARAM_INT, 'request_view', VALUE_OPTIONAL),
                      'norecords' => new external_value(PARAM_BOOL, 'norecords', VALUE_OPTIONAL),
                      'totalcount' => new external_value(PARAM_INT, 'totalcount', VALUE_OPTIONAL),
                      'length' => new external_value(PARAM_INT, 'length', VALUE_OPTIONAL),
                  )
              )
        ]);
    }

    public static function eventexpenses_parameters() {
        return new external_function_parameters([
            'options' => new external_value(PARAM_RAW, 'The paging data for the service'),
            'dataoptions' => new external_value(PARAM_RAW, 'The data for the service'),
            'offset' => new external_value(PARAM_INT, 'Number of items to skip from the begging of the result set',
                VALUE_DEFAULT, 0),
            'limit' => new external_value(PARAM_INT, 'Maximum number of results to return',
                VALUE_DEFAULT, 0),
            'contextid' => new external_value(PARAM_INT, 'contextid'),
            'filterdata' => new external_value(PARAM_RAW, 'filters applied'),
        ]);
    }

    public static function eventexpenses($options, $dataoptions, $offset = 0, $limit = 0, $contextid, $filterdata) {
        global $DB, $PAGE, $CFG;
        $params = self::validate_parameters(
            self::eventexpenses_parameters(),
            [
                'options' => $options,
                'dataoptions' => $dataoptions,
                'offset' => $offset,
                'limit' => $limit,
                'contextid' => $contextid,
                'filterdata' => $filterdata,
            ]
        );
        $offset = $params['offset'];
        $limit = $params['limit'];
        $filtervalues = json_decode($filterdata);
        $decodeddataoptions = json_decode($dataoptions);
        $stable = new \stdClass();
        $stable->thead = false;
        $stable->start = $offset;
        $stable->length = $limit;
        $stable->class = 'expenses';
        $stable->eventid = $decodeddataoptions->eventid;
        $data = (new local_events\events)->get_listof_income($stable, $filtervalues);
        $totalcount = $data['totalcount'];
        $manageactions = $data['manageactions'];
    //  var_dump($totalcount); exit;
        return [
            'totalcount' => $totalcount,
            'length' => $totalcount,
            'filterdata' => $filterdata,
            'records' =>$data,
            'options' => $options,
            'dataoptions' => $dataoptions,
            'url' => $CFG->wwwroot,
            'manageactions' => $manageactions, 
            //'total_estimated' => $data['total_estimated'],
        ];
    }

  /**
   * Returns description of method result value
   * @return external_description
   */

    public static function eventexpenses_returns () {
        return new external_single_structure([
          'options' => new external_value(PARAM_RAW, 'The paging data for the service'),
          'url' => new external_value(PARAM_RAW, 'url'),
          'manageactions' => new external_value(PARAM_BOOL, 'total number of challenges in result set'),
          'dataoptions' => new external_value(PARAM_RAW, 'The data for the service'),
          //'total_estimated' => new external_value(PARAM_RAW, 'The data for the service'),
          'totalcount' => new external_value(PARAM_INT, 'total number of challenges in result set'),
          'filterdata' => new external_value(PARAM_RAW, 'total number of challenges in result set'),
          'length' => new external_value(PARAM_RAW, 'total number of challenges in result set'),
          'records' => new external_single_structure(
                  array(
                      'incomelist' => new external_multiple_structure(
                          new external_single_structure(
                              array(
                                  'id' => new external_value(PARAM_INT, 'id'),
                                  'itemname'  => new external_value(PARAM_RAW, 'itemname'),
                                  'amount'  => new external_value(PARAM_RAW, 'itemname'),
                                  'type'  => new external_value(PARAM_RAW, 'type'),
                              )
                          )
                      ),                   
                      'request_view' => new external_value(PARAM_INT, 'request_view', VALUE_OPTIONAL),
                      'norecords' => new external_value(PARAM_BOOL, 'norecords', VALUE_OPTIONAL),
                      'totalcount' => new external_value(PARAM_INT, 'totalcount', VALUE_OPTIONAL),
                      'length' => new external_value(PARAM_INT, 'length', VALUE_OPTIONAL),
                  )
              )
        ]);
    }

    public static function viewevents_parameters() {
        return new external_function_parameters([
            'options' => new external_value(PARAM_RAW, 'The paging data for the service', VALUE_OPTIONAL),
            'dataoptions' => new external_value(PARAM_RAW, 'The data for the service', VALUE_OPTIONAL),
            'offset' => new external_value(PARAM_INT, 'Number of items to skip from the begging of the result set',
                VALUE_DEFAULT, 0),
            'limit' => new external_value(PARAM_INT, 'Maximum number of results to return',
                VALUE_DEFAULT, 0),
            'contextid' => new external_value(PARAM_INT, 'contextid', VALUE_OPTIONAL),
            'filterdata' => new external_value(PARAM_RAW, 'filters applied', VALUE_OPTIONAL),
        ]);
    }

    public static function viewevents($options=false, $dataoptions=false, $offset = 0, $limit = 0,$contextid=1, $filterdata=false) {
        global $DB, $PAGE;
        // Parameter validation.
        $params = self::validate_parameters(
            self::viewevents_parameters(),
            [
                'options' => $options,
                'dataoptions' => $dataoptions,
                'offset' => $offset,    
                'limit' => $limit,
                'contextid' => $contextid,
                'filterdata' => $filterdata
            ]
        );

        $settings = external_settings::get_instance();

      
        $offset = $params['offset'];
        $limit = $params['limit'];
        $filtervalues = json_decode($filterdata);
        $stable = new \stdClass();
        $stable->thead = false;
        $stable->start = $offset;
        $stable->length = $limit;
        $stable->mlang =  $settings->get_lang();
        $data = (new local_events\events)->get_cardview_events($stable, $filtervalues);
        $totalevents = $data['totalevents'];

        
        return [
            'totalcount' => $totalevents,
            'length' => $totalevents,
            'filterdata' => $filterdata,
            'records' =>$data,
            'options' => $options,
            'dataoptions' => $dataoptions,
        ];
    }
    public static function viewevents_returns() {
        return new external_single_structure([
            'options' => new external_value(PARAM_RAW, 'The paging data for the service'),
            'dataoptions' => new external_value(PARAM_RAW, 'The data for the service'),
            'totalcount' => new external_value(PARAM_INT, 'total number of challenges in result set'),
            'filterdata' => new external_value(PARAM_RAW, 'total number of challenges in result set'),
            'length' => new external_value(PARAM_RAW, 'total number of challenges in result set'),
            'records' => new external_single_structure(
                    array(
                        'hasevents' => new external_multiple_structure(
                            new external_single_structure(
                                array(
                                    'id' => new external_value(PARAM_INT, 'id'),
                                    'title' => new external_value(PARAM_RAW, 'title'),
                                    'description' => new external_value(PARAM_RAW, 'description'),
                                    'fromdate' => new external_value(PARAM_RAW, 'startdate'),
                                    'todate' => new external_value(PARAM_RAW, 'enddate'),
                                    'reg_startdate' => new external_value(PARAM_RAW, 'reg_startdate'),
                                    'reg_enddate' => new external_value(PARAM_RAW, 'reg_enddate'),
                                    'startdate'  => new external_value(PARAM_INT, 'eventstartdate'),
                                    'enddate'  => new external_value(PARAM_INT, 'eventenddate'),
                                    'registrationstart' => new external_value(PARAM_INT, 'reg_startdate'),
                                    'registrationend' => new external_value(PARAM_INT, 'reg_enddate'),
                                    'logo' => new external_value(PARAM_RAW, 'logo'),
                                    //'viewurl' => new external_value(PARAM_RAW, 'viewurl'),
                                    'sellingprice' => new external_value(PARAM_RAW, 'sellingprice'),
                                    'actualprice' => new external_value(PARAM_RAW, 'actualprice'),
                                    'openstatus' => new external_value(PARAM_RAW, 'openstatus'),
                                    'starttime' => new external_value(PARAM_RAW, 'starttime'),
                                    'endtime' => new external_value(PARAM_RAW, 'endtime'),
                                    'isdescription' => new external_value(PARAM_BOOL, 'isdescription'),
                                    'descriptionstring' => new external_value(PARAM_RAW, 'descriptionstring'),
                                    'managerslimit' => new external_value(PARAM_BOOL, 'Event managerslimit'),
                                    'speakers' => new external_value(PARAM_RAW, 'Event speakers'),
                                    'openstatus_key' => new external_value(PARAM_RAW, 'Event status'),
                                    'viewdetails' => new external_value(PARAM_RAW, 'Event Details'),
                                    'isenrolled' => new external_value(PARAM_RAW, 'Is Enrolled'),
                                    /*'eventmanager' => new external_multiple_structure(
                                        new external_single_structure(
                                            array(
                                                'managername' => new external_value(PARAM_RAW, 'managername'),
                                                )
                                        )
                                    ),*/
                                    /*'moremanagers' => new external_value(PARAM_RAW, 'moremanagers'),*/
                                    'location'  => new external_value(PARAM_RAW, 'eventlocation'),
                                    'status' => new external_value(PARAM_TEXT, 'status', VALUE_OPTIONAL)
                                )
                            )
                        ),
                        'request_view' => new external_value(PARAM_INT, 'request_view', VALUE_OPTIONAL),
                        'noevents' => new external_value(PARAM_BOOL, 'nocourses', VALUE_OPTIONAL),
                        'totalevents' => new external_value(PARAM_INT, 'totalhalls', VALUE_OPTIONAL),
                        'noloadmore' => new external_value(PARAM_BOOL, 'noloadmore', VALUE_OPTIONAL),
                        'length' => new external_value(PARAM_INT, 'length', VALUE_OPTIONAL),
                        'manageevents' => new external_value(PARAM_RAW, 'managehall', VALUE_OPTIONAL),
                    )
                )

        ]);
    }

    
    public function events_listing_parameters() {
        return new external_function_parameters([
            'offset' => new external_value(PARAM_INT, 'Number of items to skip from the begging of the result set',
                VALUE_DEFAULT, 0),
            'limit' => new external_value(PARAM_INT, 'Maximum number of results to return',
                VALUE_DEFAULT, 0),
             'status' => new external_value(PARAM_INT, 'Page Status',VALUE_DEFAULT, 0),

        ]);
    }
    public function events_listing($offset, $limit,$status) {
        $context = context_system::instance();
        self::validate_context($context);
        $params = self::validate_parameters(self::events_listing_parameters(), 
                array('offset' => $offset,
                'limit' => $limit, 
            ));
        $offset = $params['offset'];
        $limit = $params['limit'];
        $stable = new \stdClass();
        $stable->thead = false;
        $stable->start = $offset;
        $stable->length = $limit;
        $stable->status = $status;
        $data = (new local_events\events)->get_cardview_events($stable);
        if($data) {
            $eventslist = $data['hasevents'];
            $totalcount = $data['totalevents'];
            $eventstatus = $data['eventstatus'];
        } else {
            $eventslist = 0;
            $totalcount = 0;
            $eventstatus = 0;
        }
        return ['records' => $eventslist, 'totalcount' => $totalcount,'status' => $eventstatus];
    }
    public function events_listing_returns() {
        return new external_function_parameters(
            array(
                //'filterdata' => new external_value(PARAM_RAW, 'total number of challenges in result set'),
                'status' => new external_value(PARAM_TEXT, 'eventstatus', VALUE_OPTIONAL),
                'totalcount' => new external_value(PARAM_INT, 'totalcount', VALUE_OPTIONAL),
                'records' => new external_multiple_structure(
                        new external_single_structure(
                            array(
                                'id' => new external_value(PARAM_INT, 'id'),
                                'title' => new external_value(PARAM_RAW, 'title'),
                                'fromdate' => new external_value(PARAM_RAW, 'startdate'),
                                'todate' => new external_value(PARAM_RAW, 'enddate'),
                                //'reg_startdate' => new external_value(PARAM_RAW, 'reg_startdate'),
                                //'reg_enddate' => new external_value(PARAM_RAW, 'reg_enddate'),
                                'starttime' => new external_value(PARAM_RAW, 'starttime'),
                                'endtime' => new external_value(PARAM_RAW, 'endtime'),
                                'logo' => new external_value(PARAM_RAW, 'logo'),
                                'sellingprice' => new external_value(PARAM_RAW, 'sellingprice'),
                                'actualprice' => new external_value(PARAM_RAW, 'actualprice'),
                                'openstatus' => new external_value(PARAM_RAW, 'openstatus'),
                                'openstatus_key' => new external_value(PARAM_RAW, 'Event status'),
                                'description' => new external_value(PARAM_RAW, 'description'),
                                'isdescription' => new external_value(PARAM_BOOL, 'isdescription'),
                                'descriptionstring' => new external_value(PARAM_RAW, 'descriptionstring'),
                                //'managerslimit' => new external_value(PARAM_BOOL, 'Event managerslimit'),
                                'speakers' => new external_value(PARAM_RAW, 'Event speakers'),
                                /*'eventmanager' => new external_multiple_structure(
                                    new external_single_structure(
                                        array(
                                            'managername' => new external_value(PARAM_RAW, 'managername'),
                                            )
                                        )
                                    ),
                                'moremanagers' => new external_value(PARAM_RAW, 'moremanagers'),*/
                                'location'  => new external_value(PARAM_RAW, 'eventlocation'),
                                //'status' => new external_value(PARAM_BOOL, 'status', VALUE_OPTIONAL)
                            )
                        )
                    ),
            )
        );
    }
    public function event_listing_details_parameters() {
        return new external_function_parameters(
            array(
                'eventid' => new external_value(PARAM_INT, 'eventid', 0),
                )
        );
    }

    public function event_listing_details($eventid) {
        global $DB, $PAGE;
        $context = context_system::instance();
        self::validate_context($context);
        $params = self::validate_parameters(self::event_listing_details_parameters(), array('eventid' => $eventid));
        $settings = external_settings::get_instance();    
        $renderer = $PAGE->get_renderer('local_events');
        $mlang =  $settings->get_lang();
        $content = $renderer->get_eventinfo($eventid,$mlang);

        if($content) {
            $return = $content;
        } else {
            $return = 0;
        }
        return ['eventrecord' => $return];
    }

    public function event_listing_details_returns() {
        return new external_single_structure([
            'eventrecord'=> new external_single_structure([
                   'id' => new external_value(PARAM_INT, 'id'),
                    'title' => new external_value(PARAM_RAW, 'eventname'),
                    'titlearabic' => new external_value(PARAM_RAW, 'eventname'),
                    'code' => new external_value(PARAM_RAW, 'code'),
                    'logo'  => new external_value(PARAM_RAW, 'eventlogo'),
                    'type'  => new external_value(PARAM_RAW, 'eventtype'),
                    'gender'  => new external_value(PARAM_RAW, 'eventgender'),
                    'language'  => new external_value(PARAM_RAW, 'eventlanguage'),
                    'attendee_list'  => new external_value(PARAM_INT, 'eventlanguage'),
                    'startdate'  => new external_value(PARAM_INT, 'eventstartdate'),
                    'enddate'  => new external_value(PARAM_INT, 'eventenddate'),
                    'registrationstart' => new external_value(PARAM_INT, 'reg_startdate'),
                    'registrationend' => new external_value(PARAM_INT, 'reg_enddate'),
                    'slot' => new external_value(PARAM_INT, 'slot'),
                    'duration' => new external_value(PARAM_INT, 'eventduration'),
                    'price' => new external_value(PARAM_INT, 'price'),
                    'sellingprice' => new external_value(PARAM_INT, 'sellingprice'),
                    'actualprice' => new external_value(PARAM_INT, 'actualprice'),
                    'status' => new external_value(PARAM_INT, 'status'),
                    'method' => new external_value(PARAM_RAW, 'method'),
                    'bookseats' => new external_value(PARAM_BOOL, 'bookseats', VALUE_OPTIONAL),
                    'joinurl' => new external_value(PARAM_URL, 'method', VALUE_OPTIONAL),
                    'halldata' => new external_multiple_structure(
                        new external_single_structure(
                            array(
                                'hallid' => new external_value(PARAM_INT, 'hallid'),
                                'hallname' => new external_value(PARAM_RAW, 'hallname'),
                                'city' => new external_value(PARAM_RAW, 'city'),
                                'buildingname' => new external_value(PARAM_RAW, 'buildingname'),
                                'maplocation' => new external_value(PARAM_RAW, 'maplocation'),
                                'seatingcapacity' => new external_value(PARAM_RAW, 'seatingcapacity'),
                                'hallavailableon' => new external_value(PARAM_INT, 'hallavailableon'),
                            )
                        )
                    ),
                    'manager' => new external_multiple_structure(
                        new external_single_structure(
                            array(
                                'id' => new external_value(PARAM_RAW, 'id'),
                                'name' => new external_value(PARAM_RAW, 'name'),
                            )
                        )
                    ),
                    'agendadata' => new external_multiple_structure(
                        new external_single_structure(
                            array(
                                'agendano' => new external_value(PARAM_INT, 'agendano'),
                                'agendatopic' => new external_value(PARAM_RAW, 'agendatopic'),
                                'agendatime' => new external_value(PARAM_RAW, 'agendatime'),
                                'agendaday' => new external_value(PARAM_RAW, 'agendaday'),
                                'agendaspeaker' => new external_value(PARAM_RAW, 'agendaspeaker'),
                                'agenda_description' => new external_value(PARAM_RAW, 'agenda_description'),
                            )
                        )
                    ),
                    'partnerdata' => new external_multiple_structure(
                        new external_single_structure(
                            array(
                                'partnerlogo' => new external_value(PARAM_RAW, 'partnerlogo'),
                                'partnername' => new external_value(PARAM_RAW, 'partnername'),
                            )
                        )
                    ),

                    'platinum_sponsordata' => new external_multiple_structure(
                        new external_single_structure(
                            array(
                                'sponsorlogo' => new external_value(PARAM_RAW, 'sponsorlogo'),
                                'sponsorname' => new external_value(PARAM_RAW, 'sponsorname'),
                            )
                        )
                    ),
                    'gold_sponsordata' => new external_multiple_structure(
                        new external_single_structure(
                            array(
                                'sponsorlogo' => new external_value(PARAM_RAW, 'sponsorlogo'),
                                'sponsorname' => new external_value(PARAM_RAW, 'sponsorname'),
                            )
                        )
                    ),
                    'silver_sponsordata' => new external_multiple_structure(
                        new external_single_structure(
                            array(
                                'sponsorlogo' => new external_value(PARAM_RAW, 'sponsorlogo'),
                                'sponsorname' => new external_value(PARAM_RAW, 'sponsorname'),
                            )
                        )
                    ),
                    
                    'product_attributes' => new external_single_structure(
                        array(
                            'product' => new external_value(PARAM_INT, 'product'),
                            'category' => new external_value(PARAM_INT, 'category'),
                            'variation' => new external_value(PARAM_INT, 'variation'),
                            'label' => new external_value(PARAM_RAW, 'label'),
                            'quantity' => new external_value(PARAM_INT, 'quantity'),
                            'isloggedin' => new external_value(PARAM_INT, 'isloggedin'),
                            'hasvariations' => new external_value(PARAM_BOOL, 'hasvariations'),
                            'checkout' => new external_value(PARAM_RAW, 'checkout'),
                            'grouped' => new external_value(PARAM_INT, 'grouped'),
                            'errortext' => new external_value(PARAM_RAW, 'errortext'),
                        )
                    ),
                    'description' => new external_value(PARAM_RAW, 'eventdescription'),
                    'available_seats' =>  new external_value(PARAM_RAW, 'available_seats'),
                    'userid' =>  new external_value(PARAM_RAW, 'userid'),
                 
                
                
            ])

        ]);
    }
    public static function get_eventstatistic_parameters(){
        return new external_function_parameters([
            'offset' => new external_value(PARAM_INT, 'Number of items to skip from the begging of the result set',
                VALUE_DEFAULT, 0),
            'limit' => new external_value(PARAM_INT, 'Maximum number of results to return',
                VALUE_DEFAULT, 0),       

        ]);
    }
    public static  function get_eventstatistic($offset = 0, $limit = 0){
      global $DB;      
      $context = context_system::instance();
      require_login();
      $params = self::validate_parameters(
        self::get_eventstatistic_parameters(),
        [
            'offset' => $offset,
            'limit' => $limit,         
        ]
        );      
        $offset = $params['offset'];
        $limit =$params['limit'];      
        $stable = new \stdClass();
        $stable->thead = false;
        $stable->start = $offset;
        $stable->length = $limit;       
        $data = (new local_events\events)->eventstatistic($stable); 
        if($data){
            $event = \local_events\event\success_response::create(
                array(
                'context'=>$context,
                'objectid' => 1,
                'other'=>array(
                    'Function'=>'local_events_get_event_statistic',
                    'Success'=>'Successfully Fetched the Response'
                )
                )
            );
            $event->trigger(); 
            return $data;    
        } else{
            $event = \local_events\event\error_response::create(
                array( 
                    'context'=>$context,
                    'objectid' =>0,
                    'other'=>array(
                        'Function'=>'local_events_get_event_statistic',
                        'Error'=>'Invalid Response Value Detected'
        
                    )
                    )
                );  
            $event->trigger();
        }
       
    
            
    }

    public static function get_eventstatistic_returns() {
    
        return new external_multiple_structure(
            new external_single_structure(
                array(

                  'numberOfEvents' => new external_value(PARAM_INT, 'numberOfEvents'), 
                  'year' => new external_value(PARAM_INT, 'year'),  
                  'name' => new external_value(PARAM_TEXT, 'Name'),
                )
            )

        );
    }

    public function viewalleventsservice_parameters() {
        return new external_function_parameters([
            'pageNumber' => new external_value(PARAM_INT, 'Page Number',VALUE_DEFAULT,1),
            'pageSize' => new external_value(PARAM_INT, 'Page Size',VALUE_DEFAULT,5),
            'isArabic' => new external_value(PARAM_RAW, 'Language in use', VALUE_DEFAULT, false),
            'startDate' => new external_value(PARAM_RAW, 'startDate',VALUE_DEFAULT,null),
            'endDate' => new external_value(PARAM_RAW, 'endDate',VALUE_DEFAULT,null),
            'query' => new external_value(PARAM_RAW, 'query',VALUE_DEFAULT,null),
            'EventTypeId' => new external_value(PARAM_RAW, 'Event TypeId', VALUE_DEFAULT, NULL),
        ]);
    }

    public function viewalleventsservice($pageNumber,$pageSize, $isArabic, $startDate, $endDate, $query, $EventTypeId) {
        global $DB, $PAGE;
            $context = context_system::instance();
        $params = self::validate_parameters(
            self::viewalleventsservice_parameters(),
            [
                
                'pageNumber' => $pageNumber,
                'pageSize' => $pageSize,
                'isArabic' => $isArabic,
                'startDate' => $startDate,
                'endDate' => $endDate,
                'query' => $query,
                'EventTypeId' => $EventTypeId
            ]
        );
        $stable = new \stdClass();
        $stable->thead = false;
        $stable->start = ($pageNumber > 1) ? (($pageNumber-1)* $pageSize) : 0;
        $stable->length = $pageSize;
        $stable->isArabic = $isArabic;
        $stable->startDate = $startDate;
        $stable->endDate = $endDate;
        $stable->query = $query;
        $stable->EventTypeId = $EventTypeId;
        $data = (new local_events\events)->all_events_for_api_listing($stable);
            if($data){
                $event = \local_events\event\success_response::create(
                    array(
                    'context'=>$context,
                    'objectid' => 1,
                    'other'=>array(
                        'Function'=>'local_events_viewalleventsservice',
                        'Success'=>'Successfully Fetched the Response'
                    )
                    )
                );
                $event->trigger(); 

                return [
                    'pageData' => $data['apilist'],
                    'totalItemCount'=>$data['totalevents'],
                    'pageSize' => $pageSize,
                    'pageNumber' => $pageNumber
                ];

            } else{
                $event = \local_events\event\error_response::create(
                    array( 
                        'context'=>$context,
                        'objectid' =>0,
                        'other'=>array(
                            'Function'=>'local_events_viewalleventsservice',
                            'Error'=>'Invalid Response Value Detected'
            
                        )
                        )
                    );  
                $event->trigger();

            }
           
    
        }
    
        public function viewalleventsservice_returns() {
            return new external_single_structure([
                'pageData' => new external_multiple_structure(
                     new external_single_structure(
                         array(
                              'activityType' => new external_value(PARAM_TEXT, 'activityType'),
                              'activityID' => new external_value(PARAM_INT, 'activityID'),
                              'name' => new external_value(PARAM_TEXT, 'name'),
                              'description' => new external_value(PARAM_RAW, 'description'),
                              'date' => new external_value(PARAM_TEXT, 'date'),
                              'location'  => new external_value(PARAM_TEXT, 'location'),
                              'detailsPageURL' => new external_value(PARAM_URL, 'detailsPageURL'),
                              'eventTypeId' => new external_value(PARAM_INT, 'eventTypeId'),
                              'eventTypeName' => new external_value(PARAM_TEXT, 'eventTypeName'),
                              'stratDate' => new external_value(PARAM_TEXT, 'stratDate'),
                              'endDate' => new external_value(PARAM_TEXT, 'endDate'),
                              'startTime' => new external_value(PARAM_TEXT, 'startTime'),
                              'endTime' => new external_value(PARAM_TEXT, 'endTime'),
                              'hallname' => new external_value(PARAM_TEXT, 'endTime'),
                              'longitude' => new external_value(PARAM_FLOAT , 'longitude'),
                              'latitude' => new external_value(PARAM_FLOAT, 'latitude'),
                              'locationTypeId' => new external_value(PARAM_INT, 'locationTypeId'),
                              'locationTypeName' => new external_value(PARAM_TEXT, 'locationTypeName'),
                            )
                      )
                    ),
                    'totalItemCount' => new external_value(PARAM_INT, 'total number of events in result set'),
                    'pageSize' => new external_value(PARAM_INT, 'total number of events in result set'),
                    'pageNumber' => new external_value(PARAM_INT, 'total number of events in result set'),
    
              ]);
        }
    
        public function geteventstypes_parameters() {
            return new external_function_parameters([
                'isArabic' => new external_value(PARAM_RAW, 'Language in use', VALUE_DEFAULT, false),
            ]);
        }
    
        public function geteventstypes($isArabic) {
            global $DB, $PAGE;
            $context = context_system::instance();
            $params = self::validate_parameters(
                self::geteventstypes_parameters(),
                [
                    'isArabic' => $isArabic,
                ]
            );
            $data = (new local_events\events)->get_events_types($isArabic);
            if($data) {
                $event = \local_events\event\success_response::create(
                    array(
                    'context'=>$context,
                    'objectid' => 1,
                    'other'=>array(
                        'Function'=>'local_events_geteventstypes',
                        'Success'=>'Successfully Fetched the Response'
                    )
                    )
                );
                $event->trigger(); 
                return $data;                
            } else {
                $event = \local_events\event\error_response::create(
                    array( 
                        'context'=>$context,
                        'objectid' =>0,
                        'other'=>array(
                            'Function'=>'local_events_geteventstypes',
                            'Error'=>'Invalid Response Value Detected'
            
                        )
                        )
                    );  
                $event->trigger();

            }
         
        }
    
        public function geteventstypes_returns() {
            return  new external_multiple_structure(
                new external_single_structure(
                    array(
                    'name'=> new external_value(PARAM_TEXT, ' Program Name'),
                    'description'=> new external_value(PARAM_RAW, ' Program Description'),
                    'value'=> new external_value(PARAM_INT, ' Program ID'),
                   )
                )
            );
        }
    
    
        public function geteventinfo_parameters() {
            return new external_function_parameters([
                'eventId' => new external_value(PARAM_RAW, 'Event ID'),
                'isArabic' => new external_value(PARAM_RAW, 'Language in use', VALUE_DEFAULT, false),
            ]);
        }
    
        public function geteventinfo($eventId, $isArabic) {
            global $DB, $PAGE;
            $context = context_system::instance();
            $params = self::validate_parameters(
                self::geteventinfo_parameters(),
                [
                    'eventId' => $eventId,
                    'isArabic' => $isArabic,
                ]
            );
    
            $eventId = $params['eventId'];
            $isArabic = $params['isArabic'];
            
            $data = (new local_events\events)->get_event_info($eventId,$isArabic);
            if ($data) {
                $event = \local_events\event\success_response::create(
                    array(
                    'context'=>$context,
                    'objectid' => 1,
                    'other'=>array(
                        'Function'=>'local_events_geteventinfo',
                        'Success'=>'Successfully Fetched the Response'
                    )
                    )
                );
                $event->trigger(); 
                return $data;
            } else {
                $event = \local_events\event\error_response::create(
                    array( 
                        'context'=>$context,
                        'objectid' =>0,
                        'other'=>array(
                            'Function'=>'local_events_geteventinfo',
                            'Error'=>'Invalid Response Value Detected'
            
                        )
                        )
                    );  
                $event->trigger();
                return null;
            }
        }
    
    
    
        public function geteventinfo_returns() {
           return new external_single_structure([
                   'eventFees' => new external_value(PARAM_TEXT, 'eventFees'),
                        'eventFeesWithTaxs' => new external_value(PARAM_TEXT, 'eventFeesWithTaxs'),
                        'id' => new external_value(PARAM_INT, 'id'),
                        'isEventForRegisteredUsersOnly' => new external_value(PARAM_TEXT, 'isEventForRegisteredUsersOnly'),
                        'languageName' => new external_value(PARAM_RAW, 'languageName'),
                        'language' => new external_value(PARAM_RAW, 'language'),
                        'lstEventSpeakers' => new external_multiple_structure(
                            new external_single_structure(
                                array(
                                    'name' => new external_value(PARAM_TEXT, 'name'),
                                    'description' => new external_value(PARAM_RAW, 'description'),
                                    'value' => new external_value(PARAM_INT, 'value'),
                                )
                            )
                        ),
                        'lstEventSponsorOrganizations' => new external_multiple_structure(
                            new external_single_structure(
                                array(
                                    'name' => new external_value(PARAM_TEXT, 'name'),
                                    'description' => new external_value(PARAM_RAW, 'description'),
                                    'value' => new external_value(PARAM_INT, 'value'),
                                )
                            )
                        ),
                        'scheduleDays' => new external_multiple_structure(
                            new external_single_structure(
                                array(
                                    
                                )
                            )
                        ),
                        'lstSchedule' => new external_multiple_structure(
                            new external_single_structure(
                                array(
                                    
                                )
                            )
                        ),
                       'name' => new external_value(PARAM_TEXT, 'name'),
                       'requestNumber' => new external_value(PARAM_TEXT, 'requestNumber'),
                       'requestOperation' => new external_value(PARAM_INT, 'requestNumber'),
                        'scheduleObj' => new external_multiple_structure(
                            new external_single_structure(
                                array(
                                    'cityId' => new external_value(PARAM_TEXT, 'cityId'),
                                    'countryId' => new external_value(PARAM_TEXT, 'countryId'),
                                    'tempCountryId' => new external_value(PARAM_TEXT, 'tempCountryId'),
                                    'tempCityId' => new external_value(PARAM_TEXT, 'tempCountryId'),
    
                                    'endDate' => new external_value(PARAM_TEXT, 'endDate'),
                                    'endTime' => new external_value(PARAM_TEXT, 'endTime'),
                                    'eventId' => new external_value(PARAM_INT, 'eventId'),
                                    'id' => new external_value(PARAM_INT, 'id'),
                                    'isDaysRequireSpecificTimes' => new external_value(PARAM_TEXT, 'isDaysRequireSpecificTimes'),
                                    'isEventHasRoomReservation' => new external_value(PARAM_TEXT, 'isEventHasRoomReservation'),
                                    'locationTypeId' => new external_value(PARAM_INT, 'locationTypeId'),
                                    'locationTypeName' => new external_value(PARAM_RAW, 'locationTypeName'),
                                    'coordinates' => new external_single_structure(
                                        array(
                                            'longitude' => new external_value(PARAM_FLOAT, 'longitude'),
                                            'latitude' => new external_value(PARAM_FLOAT, 'latitude'),
                                        )
                                    ),
                                    'lstCities' => new external_multiple_structure(
                                        new external_single_structure(
                                            array(
    
                                            'name' => new external_value(PARAM_TEXT, 'name'),
                                            'description' => new external_value(PARAM_RAW, 'description'),
                                            'value' => new external_value(PARAM_TEXT, 'value'),
                                                
                                            )
                                        )
                                    ),
    
                                    'lstCountries' => new external_multiple_structure(
                                        new external_single_structure(
                                            array(
    
                                            'name' => new external_value(PARAM_TEXT, 'name'),
                                            'description' => new external_value(PARAM_RAW, 'description'),
                                            'value' => new external_value(PARAM_INT, 'value'),
                                                
                                            )
                                        )
                                    ),
                                    'lstScheduleDays' => new external_value(PARAM_TEXT, 'lstScheduleDays'),
                                    'registrationEndDate' => new external_value(PARAM_TEXT, 'registrationEndDate'),
                                     'registrationStartDate' => new external_value(PARAM_TEXT, 'registrationStartDate'),
                                     'rescheduleReason' => new external_value(PARAM_TEXT, 'rescheduleReason'),
                                    'stratDate' => new external_value(PARAM_TEXT, 'stratDate'),
                                    'startTime' => new external_value(PARAM_TEXT, 'startTime'),
                                    'tempEndTim' => new external_value(PARAM_TEXT, 'tempEndTim'),
                                    'tempRegistrationEndDate' => new external_value(PARAM_TEXT, 'tempRegistrationEndDate'),
                                    'tempRegistrationStartDate' => new external_value(PARAM_TEXT, 'tempRegistrationStartDate'),
                                    'tempStartTime' => new external_value(PARAM_TEXT, 'tempStartTime'),
                                    'withinKingdom' => new external_value(PARAM_TEXT, 'withinKingdom'),
                                    'countryName' => new external_value(PARAM_TEXT, 'countryName'),
                                    'cityName' => new external_value(PARAM_TEXT, 'cityName'),
                                )
                            )
                        ),
    
                        'seatsAvailable'  => new external_value(PARAM_INT, 'seatsAvailable'),
                        'seatsHasLimit'  => new external_value(PARAM_TEXT, 'seatsHasLimit'),
                        'seatsReserved'  => new external_value(PARAM_INT, 'seatsReserved'),
                        'seatsTotal'  => new external_value(PARAM_INT, 'seatsTotal'),
                        'summary' => new external_value(PARAM_RAW, 'summary'),
                        'targetAudienceGenderName' => new external_value(PARAM_TEXT, 'targetAudienceGenderName'),
                        'estimatedBudget' => new external_value(PARAM_TEXT, 'estimatedBudget'),
                        'eventIDPortal' => new external_value(PARAM_TEXT, 'eventIDPortal'),
                        'taskStatusId' => new external_value(PARAM_TEXT, 'taskStatusId'),
                        'individualsSeatsCount' => new external_value(PARAM_INT, 'individualsSeatsCount'),
                        'reservedIndividualsSeatsCount' => new external_value(PARAM_TEXT, 'reservedIndividualsSeatsCount'),
                        'locationTypeName' => new external_value(PARAM_TEXT, 'locationTypeName'),
                        'roomName' => new external_value(PARAM_TEXT, 'roomName'),
                        'roomAddress' => new external_value(PARAM_TEXT, 'roomAddress'),
                        'agendaAttachmentId' => new external_value(PARAM_TEXT, 'agendaAttachmentId'),
                        'externalRegistrationUrl' => new external_value(PARAM_TEXT, 'externalRegistrationUrl'),
                        'detailsPageURL' => new external_value(PARAM_URL, 'Event detailsPageURL'),
           
             ]);
        }
    

}
