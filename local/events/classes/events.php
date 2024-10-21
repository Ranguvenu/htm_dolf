<?php

// This file is part of Moodle - http://moodle.org/
//
// Moodle is free software: you can redistribute it and/or localify
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
 *
 * @package    local_events
 * @category   external
 * @copyright  eAbyas <www.eabyas.in>
 */
namespace local_events;
use  local_exams\local\exams;
use context_system;
use core_reportbuilder\local\helpers\format_test;
use dml_exception;
use local_trainingprogram\local\trainingprogram as trainingprogram;
use filters_form;
use stdClass;
use moodle_url;
use html_writer;
use local_hall\hall as hall;
defined('MOODLE_INTERNAL') || die;

require_once($CFG->dirroot.'/local/events/filters_form.php');
require_once($CFG->dirroot.'/mod/zoom/lib.php');
require_once($CFG->dirroot.'/mod/webexactivity/lib.php');
require_once($CFG->dirroot.'/mod/zoom/locallib.php');
require_once($CFG->dirroot.'/mod/msteams/lib.php');
require_once($CFG->dirroot.'/mod/teamsmeeting/lib.php');
class events {
    private $json;

    public function create_update_event($data, $editoroptions = NULL) {
        global $DB, $COURSE, $USER,$CFG,$PAGE;
		$systemcontext = context_system::instance();
        $data->titlearabic=trim($data->titlearabic);
        $data->title=trim($data->title);
		if (isset($data->logo)) {
			$data->logo = $data->logo;
			file_save_draft_area_files($data->logo, $systemcontext->id, 'local_events', 'logo', $data->logo);
		}
        $data->price = $data->cost;
        if(isset($data->description)) {
           $data->description =  $data->description['text'];
        }
        if(isset($data->targetaudience)) {
            $data->targetaudience =  $data->targetaudience['text'];
        }
        if($data->id) {
            if (!is_siteadmin() && has_capability('local/organization:manage_event_manager', context_system::instance())) {
                $data->eventmanager = $USER->id;
            } else {
                $data->eventmanager = implode(',',$data->eventmanager);
            }
        } else {
            if (!is_siteadmin() && has_capability('local/organization:manage_event_manager', context_system::instance())) {
                $data->eventmanager = $USER->id;
            } else {
                $data->eventmanager = implode(',',$data->eventmanager);
            }
        }
        if($data->cost == 0) {
            $data->sellingprice = 0;
            $data->actualprice = 0;
        }
        $data->language = implode(',',array_keys($data->language));
        $data->audiencegender = implode(',',array_keys($data->audiencegender));
        $data->halladdress = $data->halladdress;
        $hours = $data->eventslothour*3600;
        $minutes = $data->eventslotmin*60;
        $slot = $hours+$minutes;
        $data->slot = $slot;
        // $data->slot = strtotime("$data->eventslothour:$data->eventslotmin");
        $data->duration = $data->eventduration;

        // Teams Parameters
        $teamsevent = new stdClass();
        $teamsevent->name = $data->title;
        $teamsevent->namearabic = $data->titlearabic;
        $teamsevent->description = $data->description['text'];

        $teamstimings = new stdClass();
        $teamstimings->startdate = $data->startdate;
        $teamstimings->enddate = $data->enddate;
        $teamstimings->starttime['hours'] = $data->eventslothour;
        $teamstimings->starttime['minutes'] = $data->eventslotmin;
        $teamstimings->endtime['hours'] = 0;
        $teamstimings->endtime['minutes'] = $data->eventduration;
        $teamstimings->duration = $data->eventduration;

        if ($data->id) {
            if($data->method == 0){
                if($data->zoom > 0) {
                    $zoomid = (int)$data->zoom;
                   // $section = course_create_section(1);
                   // $sectionid = $section->section;
                    $mod_name = 'zoom';
                    $moduleid = $DB->get_field_sql("SELECT id FROM {modules} WHERE name = '$mod_name'");
                    $cmidsql = 'SELECT com.id FROM {course_modules} as com JOIN {course_sections} as cos ON com.section = cos.id AND com.course = cos.course WHERE  cos.course = 1  AND com.module = '.$moduleid.' AND com.instance = '.$zoomid.'';
                    $cmid = (int)$DB->get_field_sql($cmidsql);
                    //zoom_delete_instance($zoomid);
                    course_delete_module($cmid);
                    $data->zoom = 0;
                } 
                if ($data->webex > 0) {
                     $webexid = (int)$data->webex;
                   // $section = course_create_section(1);
                    //$sectionid = $section->section;
                    $mod_name = 'webexactivity';
                    $moduleid = $DB->get_field_sql("SELECT id FROM {modules} WHERE name = '$mod_name'");
                    $cmidsql = 'SELECT com.id FROM {course_modules} as com JOIN {course_sections} as cos ON com.section = cos.id AND com.course = cos.course WHERE  cos.course = 1  AND com.module = '.$moduleid.' AND com.instance = '.$webexid.'';
                    $cmid = (int)$DB->get_field_sql($cmidsql);
                    //webexactivity_delete_instance($webexid);
                    course_delete_module($cmid);
                    $data->webex = 0;
                } 
                if($data->teams > 0) {

                    $teamsid = (int)$data->teams;
                    $mod_name = 'teamsmeeting';
                    //$section = course_create_section(1);
                   // $sectionid = $section->section;                    
                    $moduleid = $DB->get_field_sql("SELECT id FROM {modules} WHERE name = '$mod_name'");
                    $cmidsql = 'SELECT com.id FROM {course_modules} as com JOIN {course_sections} as cos ON com.section = cos.id AND com.course = cos.course WHERE  cos.course = 1  AND com.module = '.$moduleid.' AND com.instance = '.$teamsid.'';
                    $cmid = (int)$DB->get_field_sql($cmidsql);
                   // $this->deleteteamsmeeting($teamsid);
                    course_delete_module($cmid);
                    $data->teams = 0;
                    
                }
                $data->virtualtype = 0;
            }

            if($data->method == 1){
                if($data->halladdress) {
                    $hallids = $data->halladdress;
                    list($hallssql, $hallparams) = $DB->get_in_or_equal($hallids);
                    $hall_exist = $DB->get_records_select('hall_reservations', "hallid {$hallssql} AND typeid = {$data->id} AND type = 'event' ", $hallparams);
                    if($hall_exist) {
                        $DB->delete_records_select('hall_reservations', "hallid {$hallssql} AND typeid = {$data->id} AND type = 'event' ", $hallparams);
                    }
                    $data->halladdress = null;
                }
                if($data->virtualtype == 2) {

                  
                    if($data->zoom > 0) {

                        $zoomid = (int)$data->zoom;
                        //$section = course_create_section(1);
                        //$sectionid = $section->section;
                        $mod_name = 'zoom';
                        $moduleid = $DB->get_field_sql("SELECT id FROM {modules} WHERE name = '$mod_name'");
                        $cmidsql = 'SELECT com.id FROM {course_modules} as com JOIN {course_sections} as cos ON com.section = cos.id AND com.course = cos.course WHERE  cos.course = 1  AND com.module = '.$moduleid.' AND com.instance = '.$zoomid.'';
                        $cmid = (int)$DB->get_field_sql($cmidsql);
                       // zoom_delete_instance($zoomid);
                        course_delete_module($cmid);
                        $data->zoom = 0;
                    } 
                    if($data->teams > 0) {
                        $teamsid = (int)$data->teams;
                        $mod_name = 'teamsmeeting';
                        //$section = course_create_section(1);
                        //$sectionid = $section->section;
                        $moduleid = $DB->get_field_sql("SELECT id FROM {modules} WHERE name = '$mod_name'");
                        $cmidsql = 'SELECT com.id FROM {course_modules} as com JOIN {course_sections} as cos ON com.section = cos.id AND com.course = cos.course WHERE  cos.course = 1  AND com.module = '.$moduleid.' AND com.instance = '.$teamsid.'';
                        $cmid = (int)$DB->get_field_sql($cmidsql);
                       //$this->deleteteamsmeeting($teamsid);
                        course_delete_module($cmid);
                        $data->teams = 0;
                    } 

                    if($data->webex > 0) {

                        $webexid = $this->update_webex($data);
                    } else {

                        $data->webex = $this->create_webex($data);
                    } 
                    
                } else if($data->virtualtype == 1) {

                    if($data->webex > 0) {
                        $webexid = (int)$data->webex;
                        //$section = course_create_section(1);
                       // $sectionid = $section->section;
                        $mod_name = 'webexactivity';
                        $moduleid = $DB->get_field_sql("SELECT id FROM {modules} WHERE name = '$mod_name'");
                        $cmidsql = 'SELECT com.id FROM {course_modules} as com JOIN {course_sections} as cos ON com.section = cos.id AND com.course = cos.course WHERE  cos.course = 1  AND com.module = '.$moduleid.' AND com.instance = '.$webexid.'';
                        $cmid = (int)$DB->get_field_sql($cmidsql);
                        //webexactivity_delete_instance($webexid);
                        course_delete_module($cmid);
                        $data->webex = 0;
                    } 
                    if($data->teams > 0 ) {
                        $teamsid = (int)$data->teams;
                        $mod_name = 'teamsmeeting';
                        //$section = course_create_section(1);
                        //$sectionid = $section->section;
                        $moduleid = $DB->get_field_sql("SELECT id FROM {modules} WHERE name = '$mod_name'");
                        $cmidsql = 'SELECT com.id FROM {course_modules} as com JOIN {course_sections} as cos ON com.section = cos.id AND com.course = cos.course WHERE  cos.course = 1  AND com.module = '.$moduleid.' AND com.instance = '.$teamsid.'';
                        $cmid = (int)$DB->get_field_sql($cmidsql);
                        //$this->deleteteamsmeeting($teamsid);
                        course_delete_module($cmid);
                        $data->teams = 0;
                    } 
                    if($data->zoom > 0) {
                        $zoomid = $this->update_zoom($data);
                    } else {
                        $data->zoom = $this->create_zoom($data);
                    } 
                } else if($data->virtualtype == 3) {

                    if($data->webex > 0) {
                        $webexid = (int)$data->webex;
                        //$section = course_create_section(1);
                        //$sectionid = $section->section;
                        $mod_name = 'webexactivity';
                        $moduleid = $DB->get_field_sql("SELECT id FROM {modules} WHERE name = '$mod_name'");
                        $cmidsql = 'SELECT com.id FROM {course_modules} as com JOIN {course_sections} as cos ON com.section = cos.id AND com.course = cos.course WHERE  cos.course = 1  AND com.module = '.$moduleid.' AND com.instance = '.$webexid.'';
                        $cmid = (int)$DB->get_field_sql($cmidsql);
                        //webexactivity_delete_instance($webexid);
                        course_delete_module($cmid);
                        $data->webex = 0;
                    }
                    if($data->zoom > 0) {
                        $zoomid = (int)$data->zoom;
                       // $section = course_create_section(1);
                      //  $sectionid = $section->section;
                        $mod_name = 'zoom';
                        $moduleid = $DB->get_field_sql("SELECT id FROM {modules} WHERE name = '$mod_name'");
                        $cmidsql = 'SELECT com.id FROM {course_modules} as com JOIN {course_sections} as cos ON com.section = cos.id AND com.course = cos.course WHERE  cos.course = 1  AND com.module = '.$moduleid.' AND com.instance = '.$zoomid.'';
                        $cmid = (int)$DB->get_field_sql($cmidsql);
                        //zoom_delete_instance($zoomid);
                        course_delete_module($cmid);
                        $data->zoom = 0;
                    } 
                    if($data->teams > 0){
                        $teamsid = $this->updateteamsmeeting(1,$teamstimings,$teamsevent,$data->teams);
                    } else {
                        $data->teams =$this->createteamsmeeting(1, $teamstimings, $teamsevent);
                    }
                    
                }
            } else {
                
                if(empty($data->halladdress)) {

                    $data->halladdress = null;

                }

            }
            $data->timemodified = time();
             $eventdata = $DB->get_record('local_events', array('id'=>$data->id));
             $eventid= $data->id;
            if($data->startdate != $eventdata->startdate ||$data->enddate != $eventdata->enddate
             || $data->registrationstart != $eventdata->registrationstart || $data->registrationend != $eventdata->registrationend
             || $data->slot != $eventdata->slot)
            {
                  // notification events  reschedule
                  $attendeeslist = $DB->get_records('local_event_attendees', ['eventid' => $data->id]);                 
                  foreach( $attendeeslist as  $attendeelist){                   
                      if($attendeelist->userid==0){
                          $touser=get_admin();                        
                          $touser->firstname=$attendeelist->name;
                          $touser->lastname=$attendeelist->name;
                          $touser->email=$attendeelist->email;                          
                        } else{
                          $touser=$DB->get_record('user',array('id'=>$attendeelist->userid));    
                      
                        }                     
                      $thispageurl = new \moodle_url('/local/events/view.php?id='.$data->id);
                      $row1=[];
                      $row1['RelatedModuleName']=$data->title;
                      $row1['ProgramLink']=$thispageurl;
                      $localuserrecord = $DB->get_record('local_users',['userid'=>$attendee->userid]);
                      $row1['FullName']=($localuserrecord)? (($localuserrecord->lang == 'ar') ? $localuserrecord->firstnamearabic.' '.$localuserrecord->middlenamearabic.' '.$localuserrecord->thirdnamearabic.' '.$localuserrecord->lastnamearabic  : $localuserrecord->firstname.' '.$localuserrecord->middlenameen.' '.$localuserrecord->thirdnameen.' '.$localuserrecord->lastname) :fullname($touser);                      
                      (new \local_events\notification())->event_notification('events_reschedule',$touser, $USER,$row1,$waitinglistid=0);
                    }
            }
			$data_update = $DB->update_record('local_events', $data);
            if($data->entitycode) {
                $draftrecord = $DB->get_record('reservations_draft', ['entitycode' => $data->entitycode, 'type' => 'event']);
                if($draftrecord) {
                    $existingrecord = $DB->get_record('hall_reservations', ['typeid' => $data->id, 'type' => 'event']);
                    $reservationdata = new stdClass();
                    if($existingrecord) {
                      $reservationdata->id = $existingrecord->id;
                    }
                    $reservationdata->typeid = $data->id;
                    $reservationdata->hallid = $draftrecord->hallid;
                    $reservationdata->seats = $draftrecord->seats;
                    $reservationdata->examdate = $draftrecord->date;
                    $reservationdata->slotstart = $draftrecord->slotstart;
                    $reservationdata->slotend = $draftrecord->slotend;
                    $reservationdata->userid = $draftrecord->userid;
                    $reservationdata->type = 'event';
                    $reservationdata->status = 1;
                    if($existingrecord) {
                       $DB->update_record('hall_reservations', $reservationdata);
                    } else {

                        $DB->insert_record('hall_reservations', $reservationdata);
                    }
                    $sessionkey = $DB->delete_records('reservations_draft', ['entitycode' => $data->entitycode, 'type' => 'event']);
                }
            }
            $event = \local_events\event\events_updated::create(array( 'context'=>$systemcontext, 'objectid' =>$data->id));// ... code that may add some record snapshots
            $event->trigger(); 
            if ($data->sendemailtopreregister == 1) {                              
                $attendees = $DB->get_records('local_event_attendees');          
                foreach ( $attendees as  $attendee) {          
                    if ($attendee->userid==0) {
                        $touser=get_admin();
                        $data->event_name = $data->title; 
                        $touser->firstname=$attendee->name;
                        $touser->lastname=$attendee->name;
                        $touser->email=$attendee->email;
                        $data->event_userfullname=$attendee->name;
                    } else {
                        $touser=$DB->get_record('user',array('id'=>$attendee->userid));
                        if ($touser->lang == 'ar') {
                            $data->event_name = $data->titlearabic;
                        } else{
                            $data->event_name = $data->title;

                        }
                        $localuserrecord = $DB->get_record('local_users',['userid'=>$attendee->userid]);
                        $data->event_userfullname =($localuserrecord)? (($localuserrecord->lang == 'ar') ? $localuserrecord->firstnamearabic.' '.$localuserrecord->middlenamearabic.' '.$localuserrecord->thirdnamearabic.' '.$localuserrecord->lastnamearabic  : $localuserrecord->firstname.' '.$localuserrecord->middlenameen.' '.$localuserrecord->thirdnameen.' '.$localuserrecord->lastname) :fullname($touser);   
                    }
                    (new \local_events\notification())->event_notification('events_create', $touser,$fromuser=$USER,$data,$waitinglistid=0);
                }
            } 
            
            $manager = $DB->get_records_sql(" SELECT u.id, CONCAT(u.firstname,'',u.lastname) AS fullname 
            FROM {user} u JOIN {local_events} e ON concat(',', e.eventmanager, ',') LIKE concat('%,',u.id,',%')
            WHERE e.eventmanager = :eventmanager",['eventmanager' => $data->eventmanager]);        
            foreach ($manager as $managers ){
                    
                    $completeusers=$DB->get_record('user',array('id'=> $managers->id));
                    if ( $completeusers->lang == 'ar') {
                        $data->event_name = $data->titlearabic;
                       
                    } else{
                        $data->event_name = $data->title;
                        
                    }
                    $localuserrecord = $DB->get_record('local_users',['userid'=>$managers->id]);
                    $data->event_userfullname =($localuserrecord)? (($localuserrecord->lang == 'ar') ? $localuserrecord->firstnamearabic.' '.$localuserrecord->middlenamearabic.' '.$localuserrecord->thirdnamearabic.' '.$localuserrecord->lastnamearabic  : $localuserrecord->firstname.' '.$localuserrecord->middlenameen.' '.$localuserrecord->thirdnameen.' '.$localuserrecord->lastname) :fullname($completeusers);   
                    
                    (new \local_events\notification())->event_notification('events_update', $touser=$completeusers,$fromuser=$USER, $data,$waitinglistid=0);
            }
             // notification events  onchange
            $attendees = $DB->get_records('local_event_attendees', ['eventid' => $eventid]);
            foreach( $attendees as  $attendee){
                if($attendee->userid==0){
                    $touser=get_admin();
                    $touser->firstname=$attendee->name;
                    $touser->lastname=$attendee->name;
                    $touser->email=$attendee->email;
                } else {
                    $touser=$DB->get_record('user',array('id'=>$attendee->userid));
                }
                $thispageurl = new \moodle_url('/local/events/view.php?id='.$eventid);
                $row1=[];
                $row1['RelatedModuleName']=$data->title;
                $row1['RelatedModulesLink']=$thispageurl;
                $localuserrecord = $DB->get_record('local_users',['userid'=>$attendee->userid]);
                $row1['FullName']=($localuserrecord)? (( $localuserrecord->lang == 'ar') ? $localuserrecord->firstnamearabic.' '.$localuserrecord->middlenamearabic.' '.$localuserrecord->thirdnamearabic.' '.$localuserrecord->lastnamearabic  : $localuserrecord->firstname.' '.$localuserrecord->middlenameen.' '.$localuserrecord->thirdnameen.' '.$localuserrecord->lastname) :fullname($touser);              
                $myobject=(new \local_events\notification);
                $myobject->event_notification('events_onchange',$touser, $USER,$row1,$waitinglistid=0);
              
            }
		} else {
            if($data->method == 1){
                if($data->virtualtype == 1) {
                    $data->zoom = $this->create_zoom($data);
                } else if($data->virtualtype == 2) {
                    $data->webex = $this->create_webex($data);
                } else if($data->virtualtype == 3) {
                    $data->teams =$this->createteamsmeeting(1, $teamstimings, $teamsevent);
                }
            }
            $data->timecreated = time();
		    $record = $DB->insert_record('local_events', $data);

            $draftrecords = $DB->get_records('reservations_draft', ['entitycode' => $data->entitycode]);
            foreach($draftrecords AS $draftrecord) {
                $draftdata = new stdClass();
                $draftdata->typeid = $record;
                $draftdata->hallid = $draftrecord->hallid;
                $draftdata->seats = $draftrecord->seats;
                $draftdata->examdate = $draftrecord->date;
                $draftdata->slotstart = $draftrecord->slotstart;
                $draftdata->slotend = $draftrecord->slotend;
                $draftdata->userid = $draftrecord->userid;
                $draftdata->type = 'event';
                $draftdata->status = 1;
                $DB->insert_record('hall_reservations', $draftdata);
            }
            $sessionkey = $DB->delete_records('reservations_draft', ['entitycode' => $data->entitycode, 'type' => 'event']);
          
            $context = context_system::instance();
            $event = \local_events\event\events_created::create(array( 'context'=>$systemcontext, 'objectid' =>$record));// ... code that may add some record snapshots
            $event->trigger();
            $eventdata=$DB->get_record('local_events',array('id'=>$record));           
            if($data->sendemailtopreregister==1){              
                $attendees = $DB->get_records('local_event_attendees');          
                foreach( $attendees as  $attendee){          
                    if($attendee->userid==0) {
                        $touser=get_admin();
                        $touser->firstname=$attendee->name;
                        $touser->lastname=$attendee->name;
                        $touser->email=$attendee->email;
                        $eventdata->event_name=$eventdata->title;
                        $eventdata->event_userfullname=$attendee->name;
                    } else {
                        $touser=$DB->get_record('user',array('id'=>$attendee->userid));
                        if ($touser->lang == 'ar') {
                            $eventdata->event_name=$eventdata->titlearabic;

                        } else{

                            $eventdata->event_name=$eventdata->title;
                        }  
                        $localuserrecord = $DB->get_record('local_users',['userid'=>$attendee->userid]);
                        $eventdata->event_userfullname=($localuserrecord)? (($localuserrecord->lang == 'ar') ? $localuserrecord->firstnamearabic.' '.$localuserrecord->middlenamearabic.' '.$localuserrecord->thirdnamearabic.' '.$localuserrecord->lastnamearabic  : $localuserrecord->firstname.' '.$localuserrecord->middlenameen.' '.$localuserrecord->thirdnameen.' '.$localuserrecord->lastname) :fullname($touser);                            
                    }
                    (new \local_events\notification())->event_notification('events_create', $touser,$fromuser=$USER,$eventdata,$waitinglistid=0);
                }            
            }
            
            $manager = $DB->get_records_sql(" SELECT u.id, CONCAT(u.firstname,'',u.lastname) AS fullname 
            FROM {user} u JOIN {local_events} e ON concat(',', e.eventmanager, ',') LIKE concat('%,',u.id,',%')
            WHERE e.id = :eventid",['eventid' => $record]);
            foreach ($manager as $managers ){                    
                    $completeusers=$DB->get_record('user',array('id'=> $managers->id));
                    if ($completeusers->userlang == 'ar') {
                        $eventdata->event_name=$eventdata->titlearabic;


                    } else {
                        $eventdata->event_name=$eventdata->title;
                        
                    }
                    $localuserrecord = $DB->get_record('local_users',['userid'=>$managers->id]);
                    $eventdata->event_userfullname= ($localuserrecord)? (($localuserrecord->lang == 'ar') ? $localuserrecord->firstnamearabic.' '.$localuserrecord->middlenamearabic.' '.$localuserrecord->thirdnamearabic.' '.$localuserrecord->lastnamearabic  : $localuserrecord->firstname.' '.$localuserrecord->middlenameen.' '.$localuserrecord->thirdnameen.' '.$localuserrecord->lastname) :fullname($completeusers);
                    (new \local_events\notification())->event_notification('events_create', $touser=$completeusers,$fromuser=$USER,$eventdata,$waitinglistid=0);
            } 
        }  
    }
    
    public function createteamsmeeting($courseid,$data,$program) {
        global $DB;

        $team = new stdClass();
        $team->modulename = 'teamsmeeting';
        $team->course =(int) $courseid;
        $team->name = '{mlang en}'.$program->name.'{mlang}{mlang ar}'.$program->namearabic.'{mlang}';
        $team->subject = $program->name;
        $team->start_time = $data->startdate + ($data->starttime['hours'] * 3600) + ($data->starttime['minutes'] * 60);
        $team->end_time = $data->enddate + ($data->endtime['hours'] * 3600) + ($data->endtime['minutes'] * 60);
        $team->duration = $data->duration;
        $team->occurs_until = $data->enddate;
        $team->timecreated = time();
        $section = course_create_section(1);
        $sectionid = $section->section;
        $team->section = $sectionid;
        $team->visible = 1;
        $team->isrecuring = 1;
        $team->introeditor = ['text' => '', 'format' => FORMAT_HTML, 'itemid' => null];
        $team->intro = $program->description;
        $team->introformat = 1;
        $moduleinfo =  create_module($team);
        $this->meetingid = $moduleinfo->instance;
    
        return $this->meetingid; 
    }

    public function updateteamsmeeting($courseid,$data,$program,$teamsid){
        global $DB;
        $team = $DB->get_record('teamsmeeting', ['id' => $teamsid]);
        $team->instance = $teamsid;
        $team->course =(int) $courseid;
        $team->name = '{mlang en}'.$program->name.'{mlang}{mlang ar}'.$program->namearabic.'{mlang}';
        $team->subject = $program->name;
        $team->start_time = $data->startdate + ($data->starttime['hours'] * 3600) + ($data->starttime['minutes'] * 60);
        $team->end_time = $data->enddate + ($data->endtime['hours'] * 3600) + ($data->endtime['minutes'] * 60);
        $team->occurs_until = $data->enddate;
        $team->duration = $data->duration;
        $team->timecreated = time();
        $section = course_create_section(1);
        $sectionid = $section->section;
        $team->section = $sectionid;
        $team->visible = 1;
        $team->isrecuring = 1;
        $team->introeditor = ['text' => '', 'format' => FORMAT_HTML, 'itemid' => null];
        $team->intro = $program->description;
        $team->introformat = 1;
        teamsmeeting_update_instance($team);
        $this->meetingid = $teamsid;

        return $this->meetingid;
    }

    public function deleteteamsmeeting($teamsid){
        global $DB;
        teamsmeeting_delete_instance($teamsid);
        return $this->meetingid = 0;
    }

    public function eventinfo() {
        global $DB, $PAGE, $OUTPUT;
        $systemcontext = context_system::instance();
        $renderer = $PAGE->get_renderer('local_events');
        $filterparams  = $renderer->get_catalog_events(true);
        $filterparams['submitid'] = 'form#filteringform';
        $filterparams['widthclass'] = 'col-md-6';
        $filterparams['placeholder'] = get_string('searchevents','local_events');
        $globalinput = $renderer->global_filter($filterparams);
        $eventdetails = $renderer->get_catalog_events();
        $filtersinfo = $this->get_filters();
        $filterparams['eventdetails'] = $eventdetails;
        $filterparams['filtersinfo'] = $filtersinfo;
        $filterparams['globalinput'] = $globalinput;
        //var_dump($filterparams); exit;
        $renderer->listofevents($filterparams);
    }

    public function get_filters() {
        global $DB;
        $filters = $DB->get_records_sql_menu('SELECT id, firstname FROM {user} WHERE id > 2');
        foreach($filters as $key => $filter) {
            $filtersinfo[] = ['id' => $key, 'name' => $filter];
        };
        return $filtersinfo;        
    }
    public static function get_cardview_events($stable,$filterdata = null) { 
        global $DB, $CFG, $USER, $OUTPUT, $PAGE,$SESSION;
  
        $SESSION->lang = ($stable->mlang) ? $stable->mlang : current_language() ;

        $systemcontext = context_system::instance();
        $pagestatus = $stable->status;
        $PAGE->set_context($systemcontext);
        $selectsql = " SELECT e.* FROM {local_events} AS e "; 
        $countsql  = " SELECT count(e.id) FROM {local_events} AS e ";
        $formsql = " WHERE e.status = 0 "; 
        // cancelled 0 means published(default 0), 1 means pending, 3 means cancel request rejected
        if(isset($pagestatus) && $pagestatus == 0) {
            $formsql .= "AND FROM_UNIXTIME(ROUND(UNIX_TIMESTAMP(FROM_UNIXTIME(e.enddate, '%Y-%m-%d')))+e.slot+e.eventduration) >= NOW() ";
        } 
        if(isset($pagestatus) && $pagestatus == 1) {
            $formsql .= " AND FROM_UNIXTIME(ROUND(UNIX_TIMESTAMP(FROM_UNIXTIME(e.enddate, '%Y-%m-%d')))+e.slot+e.eventduration) < NOW()";
        }

        if(isset($filterdata->search_query) && trim($filterdata->search_query) != ''){
            $formsql .= " AND (e.title LIKE :firstnamesearch OR e.titlearabic LIKE :arabicnamesearch) ";
            $searchparams = array('firstnamesearch' => '%'.trim($filterdata->search_query).'%','arabicnamesearch' => '%'.trim($filterdata->search_query).'%');
        } else {
            $searchparams = array();
        }

        if(!empty($filterdata->method)){           
           $method = ($filterdata->method == 1) ? 0 : 1;
           $formsql .=  " AND e.method = $method ";
        }

        if(!empty($filterdata->audiencegender)){

           $formsql .=  " AND FIND_IN_SET('$filterdata->audiencegender',e.audiencegender) ";
        }

        if(!empty($filterdata->language)){

           $formsql .=  " AND FIND_IN_SET('$filterdata->language',e.language) ";
        }

        if(!empty($filterdata->type)){

           $formsql .=  " AND e.type = '$filterdata->type' ";
        }

        if($filterdata->{'startdate[enabled]'} == 1 ){

            $start_year = $filterdata->{'startdate[year]'};
            $start_month = $filterdata->{'startdate[month]'};
            $start_day = $filterdata->{'startdate[day]'};
            $filter_starttime_con = mktime(0,0,0, $start_month, $start_day, $start_year);
            $formsql.= " AND e.startdate >= '$filter_starttime_con' ";

        }

        if($filterdata->{'enddate[enabled]'} == 1 ){
            $start_year = $filterdata->{'enddate[year]'};
            $start_month = $filterdata->{'enddate[month]'};
            $start_day = $filterdata->{'enddate[day]'};
            $filter_endtime_con=mktime(23,59,59, $start_month, $start_day, $start_year);
            $formsql.=" AND e.enddate <= '$filter_endtime_con' ";
        }

        $params = array_merge($searchparams);
        $totalevents = $DB->count_records_sql($countsql.$formsql,$params);
        $formsql .=" ORDER BY e.id DESC";

        $events = $DB->get_records_sql($selectsql.$formsql, $params, $stable->start,$stable->length);
        $eventslist = array();
        if (!empty( $events)) {
            $count = 0;
            $lang= current_language();
            foreach($events as $event) {
                $eventslist[$count]["id"] = $event->id;
                $current_date = time();
                $event_endttime = ($event->enddate+$event->slot+$event->eventduration);
                if ($event_endttime < $current_date) {
                    $events_completions = \local_events\event\events_completions::create(array('context'=> $systemcontext, 'objectid' => $event->id));// ... code that may add some record snapshots
                    $events_completions->trigger();
                }
                if($SESSION->lang == 'ar'){
                        $title = $event->titlearabic;
                }else{
                        $title = $event->title;
                }
                $eventslist[$count]["title"] = $title;
                if (!is_null($event->logo) &&  $event->logo > 0) {
                $eventimg = logo_url($event->logo);
                if($eventimg == false){
                    $eventimg = (new events)->eventdefaultimage_url();
                }
                } else {
                    $eventimg = (new events)->eventdefaultimage_url();
                }
                $eventslist[$count]["logo"] = $eventimg;

                if($event->startdate >= time() && $event->enddate >= time()){
                    $eventslist[$count]["status"] = 1;
                }else{
                    $eventslist[$count]["status"] = 0;
                }
                $eventslist[$count]["startdate"] = $event->startdate;///date('d/m/Y', $event->startdate);
                if ($event->enddate > 0) {
                    $eventslist[$count]["enddate"] =$event->enddate;
                } else {
                    $eventslist[$count]["enddate"] = "-";
                }
                $eventslist[$count]["reg_startdate"] = userdate($event->registrationstart, get_string('strftimedatefullshort', 'core_langconfig'));//date('d/m/Y', $event->registrationstart);
                $eventslist[$count]["registrationstart"] = $event->registrationstart;//date('d/m/Y', $event->registrationstart);
                if ($event->registrationend) {
                    $eventslist[$count]["reg_enddate"] = userdate($event->registrationend, get_string('strftimedatefullshort', 'core_langconfig'));//date('d/m/Y', $event->registrationend);
                    $eventslist[$count]["registrationend"] = $event->registrationend;//date('d/m/Y', $event->registrationend);
                } else {
                    $eventslist[$count]["reg_enddate"] = '-';
                }
                /*$fullname = (new trainingprogram)->user_fullname_case();
                $managers = $DB->get_records_sql(" SELECT u.id AS id, $fullname 
                FROM {user} u JOIN {local_users} lc ON lc.userid = u.id JOIN {local_events} e ON concat(',', e.eventmanager, ',') LIKE concat('%,',u.id,',%')
                WHERE e.id = :eventid",['eventid' => $event->id]);
                $eventslist[$count]['eventmanager']  = array();
                $managerslimit = false;
                if($managers) {
                    if(count($managers) > 2){
                        $ma = array_slice($managers, 0, 2);
                        $managerslimit = false;
                        $array = array_column($ma, 'fullname');
                        $managername = implode(', ', $array);
                        $eventslist[$count]['moremanagers'] = $managername;
                    } else {
                        $managerslimit = true;
                        $array = array_column($managers, 'fullname');
                        $managername = implode(', ', $array);
                        $eventslist[$count]['eventmanager'][] = array('managername' => $managername);
                        $eventslist[$count]['moremanagers'] = '';
                        }
                } else {
                    $eventslist[$count]['moremanagers'] = '--';
                }*/
                $eventslist[$count]['managerslimit'] = $managerslimit;
                $eventslist[$count]['sellingprice'] = number_format($event->sellingprice); 
                $eventslist[$count]['actualprice'] = number_format($event->actualprice);
                $starttimemeridian = date('a',mktime(0, 0,$event->slot)); 
                $endtimemeridian = date('a',mktime(0, 0,($event->slot + $event->eventduration))); 
        
                $starttime = date("h:i",mktime(0, 0,$event->slot));
                $endttime = date("h:i",mktime(0, 0,($event->slot + $event->eventduration)));
                //var_dump($event->eventduration); exit;
                $get_time_lang = self::time_lang_change($starttimemeridian, $endtimemeridian);
    
                
                $eventstarttime = gmdate("h:i",$event->slot);
                $eventendttime = gmdate("h:i",($event->slot + $event->eventduration));
            

                
                $eventslist[$count]['starttime'] =  $eventstarttime .' '.$get_time_lang['startmeridian'];
                $eventslist[$count]['endtime'] = $eventendttime .' '.$get_time_lang['endmeridian'];
                $endtime = $event->slot + $event->eventduration;
                /*if(date('i', mktime(0, 0, $event->eventduration)) == 0) {
                    $eventduration = date('g', mktime(0, 0, $event->eventduration));
                    $eventslist[$count]["durationstatus"] = get_string('hours', 'local_events');
                } else {
                    $eventduration = date('H:i', mktime(0, 0, $event->eventduration));
                    $eventslist[$count]["durationstatus"] = get_string('minutes', 'local_events');
                }*/
                $eventduration = date('H:i', mktime(0, 0, $event->eventduration));
                $eventslist[$count]["durationstatus"] = get_string('minutes', 'local_events');
                $eventslist[$count]["eventduration"] = $eventduration;
                $eventslist[$count]["eventstartdate"] =  userdate($event->startdate, get_string('strftimedaydate', 'core_langconfig'));//date('l j F Y', $event->startdate);
                
                $eventslist[$count]["fromdate"] = userdate($event->startdate, get_string('strftimedatemonthabbr', 'core_langconfig'));//userdate(date('jS M Y', $event->startdate));
                $eventslist[$count]["todate"] = userdate($event->enddate, get_string('strftimedatemonthabbr', 'core_langconfig'));//date('jS M Y', $event->enddate);
                $statusarray = array(0 => get_string('active', 'local_events'),
                    1 => get_string('inactive', 'local_events'),
                    2 => get_string('cancelled', 'local_events'),
                    3 => get_string('closed', 'local_events'),
                    4 => get_string('archieved', 'local_events'));
                $eventslist[$count]['openstatus'] = ($event->status == 0) ? (($event_endttime >= $current_date) ?  $statusarray[0] : $statusarray[1]) : $statusarray[$event->status];
                $eventslist[$count]['openstatus_key'] =  ($event->status == 0) ? (($event_endttime >= $current_date) ? 0 : 1) : $event->status;

                $eventslist[$count]['status'] = ($event_endttime >= $current_date)? get_string('in_progress_data','local_trainingprogram'):get_string('completed','local_trainingprogram');


                // $description = format_text($event->description,FORMAT_HTML);
                $description =  format_text($event->description,FORMAT_MOODLE);
                $isdescription = '';
                if (empty($description)) {
                    $isdescription = false;
                } else {
                    $isdescription = true;
                    $descriptionstring = '';//format_text($decsriptionCut,FORMAT_HTML);
                }

                // $eventslist[$count]['description'] = mb_substr(strip_tags($description),0,200);
                $eventslist[$count]['description'] = $description;
                $eventslist[$count]['isdescription'] = $isdescription;
                $eventslist[$count]['descriptionstring'] = mb_substr(strip_tags($descriptionstring),0,200);
                $eventslist[$count]['count'] = $count;

                $hall_sql = " SELECT h.maplocation FROM {hall} h JOIN {local_events} e ON e.halladdress = h.id
                WHERE e.id =  $event->id ";
                $hallslist  =  $DB->get_record_sql($hall_sql);
                if ($hallslist) {
                    $eventslist[$count]['location'] = $hallslist->maplocation;
                } else {
                    $eventslist[$count]['location'] = '--';
                }
                // Event Speakers
                $speakers = $DB->get_fieldset_sql("SELECT es.name 
                                                   FROM {local_speakers} es
                                                   JOIN {local_event_speakers} e ON e.speaker = es.id  
                                                  WHERE e.eventid = $event->id");
                $speakerlist = implode(', ', $speakers);
                if ($speakers) {
                    $eventslist[$count]['speakers'] = $speakerlist;
                } else {
                    $eventslist[$count]['speakers'] = '--';
                }
                $eventslist[$count]['eventviewurl'] =  new moodle_url("/local/events/alleventsview.php?id=".$event->id);

                $enrolled = self::is_enrolled($event->id, $USER->id);
                
                if ($enrolled) {
                    $eventslist[$count]['isenrolled'] = true;
                } else {
                    $eventslist[$count]['isenrolled'] = false;
                }

                if ((!is_siteadmin() && has_capability('local/organization:manage_organizationofficial',$systemcontext))) {
                    $eventslist[$count]["viewdetails"] = $CFG->wwwroot.'/local/events/index.php';
                } elseif((!is_siteadmin() && has_capability('local/organization:manage_trainee',$systemcontext))) {

                    if (!empty($enrolled)) {
                        $eventslist[$count]["viewdetails"] = $CFG->wwwroot.'/local/events/index.php';
                    } else {
                        $eventslist[$count]["viewdetails"] = $CFG->wwwroot.'/local/events/alleventsview.php?id='.$event->id;
                    }

                } else {
                    $eventslist[$count]["viewdetails"] = $CFG->wwwroot.'/local/events/alleventsview.php?id='.$event->id;
                }


                $count++;
            }
            $noevent = false;
            $pagination = false;
        } else {
            $noevent = true;
            $pagination = false;
        }
        if($totalevents > 6 ){
            $noloadmore = true;
        }
        if($totalevents == count($eventslist) ){
            $noloadmore = false;
        }
        //var_dump($eventslist); exit;
        $eventsContext = array(
            "hasevents" => $eventslist,
            "noevent" => $noevent,
            "totalevents" => $totalevents,
            "eventstatus" => ($pagestatus == 0) ? get_string('in_progress_data','local_trainingprogram') :  (($pagestatus == 1) ? get_string('completed','local_trainingprogram') : get_string('allevents','local_events')),
            "length" => count($eventslist),
            "noloadmore" => $noloadmore
        );
        return $eventsContext;
    }
   
    public static function get_listof_events($stable, $filterdata) { 
        global $DB, $CFG, $USER, $OUTPUT, $PAGE,$SESSION;
        $systemcontext = context_system::instance();
        $SESSION->lang = ($stable->mlang) ? $stable->mlang : current_language() ;
        $PAGE->set_context($systemcontext);
        $eventslist = [];
        if($filterdata->status && $filterdata->status=='local_events') {

            $renderer = $PAGE->get_renderer('tool_product');
            $products = $renderer->lis_org_purchases($stable,$filterdata);
            $totalevents = COUNT($products);
        
        } else {

            $formsql = '';
    
            $selectsql = " SELECT e.* FROM {local_events} AS e "; 
            $countsql  = " SELECT count(e.id) FROM {local_events} AS e ";
            
            if(!is_siteadmin() && has_capability('local/organization:manage_trainee', $systemcontext)) {
             $selectsql .=" LEFT JOIN {local_event_attendees} ea ON e.id = ea.eventid  ";
             $countsql .=" LEFT JOIN {local_event_attendees} ea ON e.id = ea.eventid  ";
            } 

            if (!is_siteadmin() && has_capability('local/organization:manage_trainee',$systemcontext) || !is_siteadmin() && has_capability('local/organization:manage_organizationofficial',$systemcontext)) {
                if(!empty($filterdata->favourites) ){
                  $selectsql .=" LEFT JOIN {favourite} fav ON e.id = fav.itemid  ";
                }
                if(!empty($filterdata->favourites) ){
                   $countsql .=" LEFT JOIN {favourite} fav ON e.id = fav.itemid  ";
                }
            }  
            // if(isset($filterdata->eventmanager) && trim($filterdata->eventmanager) != '') {
            //     $formsql .= " JOIN {user} u ON concat(',', e.eventmanager, ',') LIKE concat('%,',u.id,',%') ";
            // }
            if (!empty($filterdata->speaker)) {
                $formsql .= " JOIN {local_event_speakers} es ON es.eventid = e.id ";
            }
            if (isset($filterdata->location_query)) {
                $formsql = " JOIN {hall} h ON e.halladdress = h.id ";
            } else if (isset($filterdata->location_query)) {
                $formsql = " JOIN {hall} h ON e.halladdress = h.id ";

            } else if (!empty($filterdata->speaker)) {
                $formsql = " JOIN {local_event_speakers} es ON es.eventid = e.id  WHERE 1=1";
            }
            
            $formsql .= " WHERE 1=1 "; 
            //if (!is_siteadmin() && has_capability('local/organization:manage_event_manager', $systemcontext)) {
            //  $formsql .=  (!empty($filterdata->eventmanager) && $filterdata->eventmanager == 1) ? " AND FIND_IN_SET('$USER->id',e.eventmanager) " : "";
            //  $formsql .=  (!empty($filterdata->eventmanager) && $filterdata->eventmanager == 2) ?  " AND NOT FIND_IN_SET('$USER->id',e.eventmanager) ":'';
            //} 
            if (!is_siteadmin() && has_capability('local/organization:manage_event_manager', $systemcontext)) {
              $formsql .= " AND FIND_IN_SET('$USER->id',e.eventmanager) ";
            } 
            if (!is_siteadmin() && has_capability('local/organization:manage_organizationofficial', $systemcontext)) {
               
                $formsql .=" AND e.cancelled !=2 ";
                 
            } 
            if(!is_siteadmin() && has_capability('local/organization:manage_trainee', $systemcontext)) {

                $formsql .= " AND ea.userid = $USER->id AND ea.enrolstatus = 1 ";
            }
            if (isset($filterdata->search_query) && trim($filterdata->search_query) != ''){
                $formsql .= " AND (e.title LIKE :firstnamesearch OR e.titlearabic LIKE :arabicnamesearch) ";
                $searchparams = array('firstnamesearch' => '%'.trim($filterdata->search_query).'%', 'arabicnamesearch' => '%'.trim($filterdata->search_query).'%');
            } else {
                $searchparams = array();
            }

            if(isset($filterdata->location_query) && trim($filterdata->location_query) != ''){
                $formsql .= " AND (h.maplocation LIKE :locationsearch) ";
                $searchparams = array('locationsearch' => '%'.trim($filterdata->location_query).'%');
            }

            if(!empty($filterdata->eventstatus) || ($filterdata->eventstatus=='0')){
                $formsql .= " AND e.status LIKE '%$filterdata->eventstatus%' ";
            } else {
                $formsql .= " AND e.status IN (0,1,2,3,4) "; // cancelled 0 means published(default 0), 1 means pending, 3 means cancel request rejected
            }

            if(isset($filterdata->status) && $filterdata->status==0){
               $formsql .= " AND FROM_UNIXTIME(ROUND(UNIX_TIMESTAMP(FROM_UNIXTIME(e.enddate, '%Y-%m-%d')))+e.slot+e.eventduration) >= NOW() ";
               // $formsql .= " AND FROM_UNIXTIME((e.enddate + e.slot + e.eventduration)) >= NOW()";
            }
            if($filterdata->status=='1'){
                $formsql .= " AND FROM_UNIXTIME(ROUND(UNIX_TIMESTAMP(FROM_UNIXTIME(e.enddate, '%Y-%m-%d')))+e.slot+e.eventduration) < NOW()";
            }
            if(!empty($filterdata->type)){
                $formsql .= " AND e.type LIKE '%$filterdata->type%' ";
            }

           if ($filterdata->{'startdate[enabled]'} == 1) {
                $start_year = $filterdata->{'startdate[year]'};
                $start_month =$filterdata->{'startdate[month]'};
                $start_day = $filterdata->{'startdate[day]'};
                $filter_startdate = mktime(0,0,0,$start_month, $start_day, $start_year);
                $formsql .= " AND e.startdate >= $filter_startdate ";
            }

            if(!empty($filterdata->{'enddate[enabled]'} == 1)){
                $end_year = $filterdata->{'enddate[year]'};
                $end_month =$filterdata->{'enddate[month]'};
                $end_day = $filterdata->{'enddate[day]'};
                $filter_enddate = mktime(23,59,59,$end_month, $end_day, $end_year);
                $formsql .= " AND e.enddate <= $filter_enddate";
            }
            
            if(!empty($filterdata->speaker)){
                $formsql .= " AND es.speaker LIKE '%$filterdata->speaker%' ";
            }
            if(!empty($filterdata->method)){

               $method = ($filterdata->method == 1) ? 0 : 1;
               $formsql .=  " AND e.method = $method ";
            }
            
            if(!empty($filterdata->audiencegender)){
                $formsql .= " AND e.audiencegender LIKE '%$filterdata->audiencegender%' ";
            }

            if ($filterdata->{'registrationstart[enabled]'} == 1) {
                $start_year = $filterdata->{'registrationstart[year]'};
                $start_month =$filterdata->{'registrationstart[month]'};
                $start_day = $filterdata->{'registrationstart[day]'};
                $filter_startdate = mktime(0,0,0,$start_month, $start_day, $start_year);
                $formsql .= " AND e.registrationstart >= $filter_startdate ";
            }

            if ($filterdata->{'registrationend[enabled]'} == 1) {
                $end_year = $filterdata->{'registrationend[year]'};
                $end_month =$filterdata->{'registrationend[month]'};
                $end_day = $filterdata->{'registrationend[day]'};
                $filter_enddate = mktime(0,0,0,$end_month, $end_day, $end_year)+86399;
                $formsql .= " AND e.registrationend <= $filter_enddate";
            }

            if(!empty($filterdata->language)){
                $formsql .= " AND e.language LIKE '%$filterdata->language%' ";
            }
             if(!empty($filterdata->favourites) ){
                $formsql.=" AND fav.component = 'local_events' AND  fav.userid='$USER->id' ";
            }
            $params = array_merge($searchparams);
            $totalevents = $DB->count_records_sql($countsql.$formsql,$params);
            $formsql .=" ORDER BY e.id DESC";
            // $events = $DB->get_records_sql($selectsql.$formsql, $params);
            $events = $DB->get_records_sql($selectsql.$formsql, $params, $stable->start,$stable->length);
            if (!empty($events)) {
                $count = 0;
                $time = time();
                $lang= current_language();
                foreach($events as $event) {
                    $date = date('y-m-d H:i:s',$event->enddate);
                    $eventslist[$count]["id"] = $event->id;
                   if( $lang == 'ar' ||  $SESSION->lang == 'ar' && !empty($event->titlearabic)){
                            $title = $event->titlearabic;
                    }else{
                            $title = $event->title;
                    }
                    $eventslist[$count]["title"] = $title;
                    //$eventslist[$count]["description"] = $event->description;
                    if (!is_null($event->logo) &&  $event->logo > 0) {
                    $eventimg = logo_url($event->logo);
                    if($eventimg == false){
                        $eventimg = (new events)->eventdefaultimage_url();
                    }
                    } else {
                        $eventimg = (new events)->eventdefaultimage_url();
                    }
                    $eventslist[$count]["logo"] = $eventimg;

                    if($event->startdate >= time() && $event->enddate >= time()){
                        $eventslist[$count]["status"] = 1;
                    }else{
                        $eventslist[$count]["status"] = 0;
                    }
                    $currentuserroleinfo = $DB->get_record_sql('SELECT rol.* 
                                                                FROM {role} rol 
                                                                JOIN {role_assignments} rola ON rola.roleid = rol.id
                                                                WHERE rola.userid =:userid and contextid =:contextid', 
                                                                ['userid'=>$USER->id,'contextid'=>$systemcontext->id]);

                    $eventslist[$count]['currentuser'] =(is_siteadmin()) ? 'admin' : $currentuserroleinfo->shortname;
                    $eventslist[$count]['costtype'] =($event->sellingprice > 0) ? 1 : 0;
                    $eventslist[$count]["code"] = $event->code;
                    $eventslist[$count]["start"] = $event->startdate;
                    $eventslist[$count]["end"] = $event->enddate;
                    $eventslist[$count]["registrationstart"] = $event->registrationstart;
                    $eventslist[$count]["registrationend"] = $event->registrationend;                
                    $eventslist[$count]["startdate"] = userdate($event->startdate, get_string('strftimedatefullshort', 'core_langconfig'));///date('d/m/Y', $event->startdate);

                    $sdate = strtotime(userdate($event->startdate,'%Y-%m-%d'));
                    $curr_date = strtotime(userdate(time(),'%Y-%m-%d'));
                    $remainingdays = floor(($sdate - $curr_date) / (60 * 60 * 24));

                    if ($remainingdays >=1 ) {
                        $eventslist[$count]["canceloption"] = true;
                    } else {
                        $eventslist[$count]["canceloption"] = false;
                    }

                    if ($event->enddate > 0) {
                        $eventslist[$count]["enddate"] = userdate($event->enddate, get_string('strftimedatefullshort', 'core_langconfig'));//date('d/m/Y', $event->enddate);
                    } else {
                        $eventslist[$count]["enddate"] = "-";
                    }
                    $eventslist[$count]["reg_startdate"] = userdate($event->registrationstart, get_string('strftimedatefullshort', 'core_langconfig'));//date('d/m/Y', $event->registrationstart);
                    if ($event->registrationend) {
                        $eventslist[$count]["reg_enddate"] = userdate($event->registrationend, get_string('strftimedatefullshort', 'core_langconfig'));//date('d/m/Y', $event->registrationend);
                    } else {
                        $eventslist[$count]["reg_enddate"] = '-';
                    }

                    $eventslist[$count]['iscancelled'] = ($event->cancelled == 2) ? true:false;
                    $eventslist[$count]['cancelledrequestpending'] = ($event->cancelled == 1 || $event->cancelled == -1) ? true:false;
                    $eventslist[$count]['cancelledstatustext'] = ($event->cancelled == 2) ? get_string('cancelled','local_trainingprogram'):  (($event->cancelled == 1 || $event->cancelled == -1) ? get_string('cancelrequestpending','local_trainingprogram') : get_string('cancelrequestrejected','local_trainingprogram'));      
                    
                    $eventtype = (new events)->get_event_type($event->type);
                    $eventslist[$count]['eventtype'] = $eventtype;

                    $eventslist[$count]["editurl"] = $CFG->wwwroot."/local/events/addevent.php?id=".$event->id;
                    $eventslist[$count]['viewurl'] = $CFG->wwwroot."/local/events/view.php?id=".$event->id;
                    $attendeescount = $DB->count_records('local_event_attendees', ['eventid' => $event->id, 'enrolstatus' =>1]);
                    $eventslist[$count]['attendeescount'] = $attendeescount;
                    
                    if (is_siteadmin() || has_capability('local/events:manage', context_system::instance()) 
                       || has_capability('local/organization:manage_event_manager', context_system::instance()) ) {
                        $eventslist[$count]['action'] = true;
                    } else {
                        $eventslist[$count]['action'] = false; 
                    }
                    $eventslist[$count]['view_attendees_action'] = false;
                    if (!is_siteadmin() && has_capability('local/organization:manage_communication_officer', $systemcontext)) {
                        $eventslist[$count]['view_attendees_action'] = true;
                    }

                    $eventenddatetime = ($event->enddate+$event->slot+$event->eventduration);
                    $currtime = time();
                    if (is_siteadmin() || has_capability('local/organization:manage_event_manager', $systemcontext)) {
                        if($event->cancelled == 1) {
                            $eventslist[$count]['cancelevent'] = false;
                        } else {
                            $eventslist[$count]['cancelevent'] = ($eventenddatetime > $currtime) ? true : false;
                        }
                    } else {
                        $eventslist[$count]['cancelevent'] = false;
                    }
                    $eventslist[$count]['hasenrollments'] = ($attendeescount > 0) ? 1: 0;
                    // $fullname = (new trainingprogram)->user_fullname_case();
                    // $managers = $DB->get_records_sql(" SELECT u.id AS id, $fullname 
                    // FROM {user} u JOIN {local_users} lc ON lc.userid = u.id JOIN {local_events} e ON concat(',', e.eventmanager, ',') LIKE concat('%,',u.id,',%')
                    // WHERE e.id = :eventid",['eventid' => $event->id]);
                    //     $eventslist[$count]['eventmanager']  = array();
                    // if($managers) {
                    //     $managerslimit = false;
                    //     if(count($managers) > 2){
                    //         $ma = array_slice($managers, 0, 2);
                    //         $managerslimit = false;
                    //         $array = array_column($ma, 'fullname');
                    //         $managername = implode(', ', $array);
                    //         $eventslist[$count]['moremanagers'] = $managername;
                    //     } else {
                    //         $managerslimit = true;
                    //         $array = array_column($managers, 'fullname');
                    //         $managername = implode(', ', $array);
                    //         $eventslist[$count]['eventmanager'][] = array('managername' => $managername);
                    //         $eventslist[$count]['moremanagers'] = '';
                    //         }
                    // } else {
                    //     $eventslist[$count]['moremanagers'] = '--';
                    // }
                    //$eventslist[$count]['managerslimit'] = $managerslimit;
                    $eventslist[$count]['eventprice'] = $event->sellingprice;
                    $eventslist[$count]['sellingprice'] = number_format($event->sellingprice); 
                    $eventslist[$count]['actualprice'] = number_format($event->actualprice);
                    $starttimemeridian = date('a',mktime(0, 0, $event->slot)); 
                    $endtimemeridian = date('a',mktime(0, 0, ($event->slot + $event->eventduration))); 
            
                    $starttime = date("h:i",mktime(0, 0, $event->slot));
                    $endttime = date("h:i",mktime(0, 0, ($event->slot + $event->eventduration)));

                    $get_time_lang = self::time_lang_change($starttimemeridian, $endtimemeridian);
                    
                    $eventslist[$count]['starttime'] =  $starttime .' '.$get_time_lang['startmeridian'];
                    $eventslist[$count]['endtime'] = $endttime .' '.$get_time_lang['endmeridian'];

                    $hours = date('H', mktime(0, 0, $event->eventduration));
                    $minutes = date('i', mktime(0, 0, $event->eventduration));
                    $hoursstatus = get_string('hours', 'local_events');
                    $minutesstatus =  get_string('minutes', 'local_events');
                   
                    $eventslist[$count]["eventduration"] = $hours .' '. $hoursstatus .' '. $minutes .' '. $minutesstatus;

                   /* if(date('i', mktime(0, 0, $event->eventduration)) == 0) {
                        $eventduration = date('g', mktime(0, 0, $event->eventduration));
                        $eventslist[$count]["durationstatus"] = get_string('hours', 'local_events');
                    } else {
                        $eventduration = date('H:i', mktime(0, 0, $event->eventduration));
                        $eventslist[$count]["durationstatus"] = get_string('minutes', 'local_events');
                    }*/
                    $eventslist[$count]["durationstatus"] ='';
                    $eventslist[$count]["eventstartdate"] =  userdate($event->startdate, get_string('strftimedaydate', 'core_langconfig'));
                    
                    $eventslist[$count]["fromdate"] = userdate($event->startdate, get_string('strftimedatemonthabbr', 'core_langconfig'));
                    $eventslist[$count]["todate"] = userdate($event->enddate, get_string('strftimedatemonthabbr', 'core_langconfig'));
                    $statusarray = array(0 => get_string('active', 'local_events'),
                        1 => get_string('inactive', 'local_events'),
                        2 => get_string('cancelled', 'local_events'),
                        3 => get_string('closed', 'local_events'),
                        4 => get_string('archieved', 'local_events'));
                    $eventslist[$count]['openstatus'] = $statusarray[$event->status];
                    $description = format_text($event->description,FORMAT_HTML);
                    $isdescription = '';
                    if (empty($description)) {
                        $isdescription = false;
                    } else {
                        $isdescription = true;
                        $descriptionstring = format_text($decsriptionCut,FORMAT_HTML);
                    }

                    $eventslist[$count]['description'] = mb_substr(strip_tags($description),0,200);
                    $eventslist[$count]['isdescription'] = $isdescription;
                    $eventslist[$count]['descriptionstring'] = mb_substr(strip_tags($descriptionstring),0,200);
                    $eventslist[$count]['count'] = $count;
                    $eventslist[$count]['candelete'] =(is_siteadmin() || has_capability('local/organization:manage_event_manager', $systemcontext)) ? true :false;

                    $hall_sql = " SELECT h.maplocation FROM {hall} h JOIN {local_events} e ON e.halladdress = h.id
                    WHERE e.id =  $event->id ";
                    $hallslist  =  $DB->get_record_sql($hall_sql);
                    if ($hallslist) {
                        $eventslist[$count]['location'] = $hallslist->maplocation;
                    } else {
                        $eventslist[$count]['location'] = '--';
                    }

                    // Event Speakers
                    $speakers = $DB->get_fieldset_sql("SELECT es.name 
                                                       FROM {local_speakers} es
                                                       JOIN {local_event_speakers} e ON e.speaker = es.id  
                                                      WHERE e.eventid = $event->id");
                    $speakerlist = implode(', ', $speakers);
                    if ($speakers) {
                        $eventslist[$count]['speakers'] = $speakerlist;
                    } else {
                        $eventslist[$count]['speakers'] = '--';
                    }
                    $eventspeakers = [];
                    foreach($speakers as $speaker) {
                        $row = [];
                        $row['name'] = $speaker;
                        $eventspeakers[] = $row;
                    }

                    $eventslist[$count]['speakerslist'] = !empty($eventspeakers) ? $eventspeakers : [];

                    $eventslist[$count]['eventviewurl'] =  new moodle_url("/local/events/alleventsview.php?id=".$event->id);
                    // Event Booking by organizationofficial

                    $eventslist[$count]["eventseats"] = '';
                    $eventslist[$count]["bookseats"] = false;

                    if(!is_siteadmin() && has_capability('local/organization:manage_organizationofficial', $systemcontext)) {
                        $days_between = self::get_event_reg_dates($event->id);
                        if($days_between) {
                            $eventseats = self::events_available_seats($event->id);
                           // var_dump($eventseats); exit;
                            $eventslist[$count]["eventseats"] = $eventseats['eventseats'];
                            $eventslist[$count]["bookseats"] = true;
                        }
                    }
                    $current_date = date('Y-m-d H:i:s');
                    $event_endttime = date('Y-m-d H:i:s', $event->enddate + $event->slot + $event->eventduration);
                   // var_dump($event_endttime);
                   // var_dump($current_date); exit;
                    if ($event_endttime < $current_date) {
                        $events_completions = \local_events\event\events_completions::create(array('context'=> $systemcontext, 'objectid' => $event->id));// ... code that may add some record snapshots
                        $events_completions->trigger();
                    }
                    if(has_capability('local/organization:manage_trainee', $systemcontext)) {
                        $certid = $DB->get_field('tool_certificate_issues', 'code', array('moduleid' => $event->id, 'userid' => $USER->id, 'moduletype' => 'events'));
                        $eventslist[$count]['certificateid'] = !empty($certid) ? $certid : 0;
                        

                    } else {
                        $eventslist[$count]['certificateid'] = 0;
                    }
                    $productid = $DB->get_field('tool_products', 'id', ['category' => 3, 'referenceid' =>$event->id]);
                    $eventslist[$count]['productid'] = $productid;
                    $eventslist[$count]['certificateurl'] = !empty($certid) ? $CFG->wwwroot.'/admin/tool/certificate/view.php?code='.$certid : '#';
                    $enrolleduserid =(int) $DB->get_field('local_event_attendees','usercreated',['eventid'=>$event->id,'userid'=>$USER->id]);        
                    $enroleduserroleinfo = $DB->get_record_sql('SELECT rol.* FROM {role} rol 
                                                            JOIN {role_assignments} rola ON rola.roleid = rol.id
                                                            WHERE rola.userid =:userid and contextid =:contextid',['userid'=>$enrolleduserid,'contextid'=>$systemcontext->id]);
            
                    
                   
                    if (!is_siteadmin() && has_capability('local/organization:manage_trainee', $systemcontext)  && ( empty($enroleduserroleinfo->shortname)  ||  $enroleduserroleinfo->shortname == 'organizationofficial')) {
                        $eventslist[$count]['disableallactions'] = true;
                    } else {
                        $eventslist[$count]['disableallactions'] = false;
                    }  
                     $component='local_events';
                     $eventslist[$count]['checkfavornot'] =(new exams)->checkfavourites($event->id,$USER->id,$component);                 
                    $count++;
                }
                $noevent = false;
                $pagination = false;
            } else {
                $noevent = true;
                $pagination = false;
            }
            if($totalevents > 6 ){
                $noloadmore = true;
            }
            if($totalevents == count($eventslist) ){
                $noloadmore = false;
            }

        }

        //var_dump($eventslist); exit;
        $eventsContext = array(
            "hasevents" => $eventslist,
            "noevent" => $noevent,
            "totalevents" => $totalevents,
            "length" => count($eventslist),
            "noloadmore" => $noloadmore,
            "products" => $products,
        );
        return $eventsContext;
    }

    public function add_update_agenda($data) {
        global $DB,$CFG,$USER;
		$systemcontext = context_system::instance();
        /*if(isset($data->description)) {
           $data->description =  $data->description['text'];
        }*/
        $data->timefrom  = $data->starthour.':'.$data->startminute;
        $data->timeto = $data->endhour.':'.$data->endminute;
		if ($data->id) {
            $data->timemodified = time();
			$DB->update_record('local_eventagenda', $data);
             // notification events  onchange            
            $attendees = $DB->get_records('local_event_attendees', ['eventid' => $data->eventid]);          
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
                $thispageurl = new \moodle_url('/local/events/view.php?id='.$data->eventid);
            
                $row1=[];
                $row1['RelatedModuleName']=$DB->get_field('local_events','title',array('id'=>$data->eventid));
                $row1['RelatedModulesLink']=$thispageurl;
                $localuserrecord = $DB->get_record('local_users',['userid'=>$attendee->userid]);
                $row1['FullName']=($localuserrecord)? ((  $localuserrecord ->lang == 'ar') ? $localuserrecord->firstnamearabic.' '.$localuserrecord->middlenamearabic.' '.$localuserrecord->thirdnamearabic.' '.$localuserrecord->lastnamearabic  : $localuserrecord->firstname.' '.$localuserrecord->middlenameen.' '.$localuserrecord->thirdnameen.' '.$localuserrecord->lastname) :fullname($touser);             
                $myobject=(new \local_events\notification);
                $myobject->event_notification('events_onchange',$touser, $USER,$row1,$waitinglistid=0);
              
            }
			return $data->id;
		} else {
            $data->timecreated = time();
			$id=$DB->insert_record('local_eventagenda', $data);
            // notification events  onchange                     
            $attendees = $DB->get_records('local_event_attendees', ['eventid' => $data->eventid]);         
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
                $thispageurl = new \moodle_url('/local/events/view.php?id='.$data->eventid);
            
                $row1=[];
                $row1['RelatedModuleName']=$DB->get_field('local_events','title',array('id'=>$data->eventid));
                $row1['RelatedModulesLink']=$thispageurl;
                $localuserrecord = $DB->get_record('local_users',['userid'=>$attendee->userid]);
                $row1['FullName']=($localuserrecord)? (($localuserrecord->lang == 'ar') ? $localuserrecord->firstnamearabic.' '.$localuserrecord->middlenamearabic.' '.$localuserrecord->thirdnamearabic.' '.$localuserrecord->lastnamearabic  : $localuserrecord->firstname.' '.$localuserrecord->middlenameen.' '.$localuserrecord->thirdnameen.' '.$localuserrecord->lastname) :fullname($touser);                
                $myobject=(new \local_events\notification);
                $myobject->event_notification('events_onchange',$touser, $USER,$row1,$waitinglistid=0);
              
            }
            return $id;
		}
    }

    public function set_agenda($id) {
    	global $DB;
        $data = $DB->get_record('local_eventagenda', ['id' => $id], '*', MUST_EXIST);
		$row['id'] = $data->id;
		$row['title'] = $data->title;
		$row['day'] = $data->day;
        $startarr = explode(':', $data->timefrom);
        $row['starthour'] = $startarr[0];
        $row['startminute'] = $startarr[1];
        $endarr = explode(':', $data->timeto);
        $row['endhour'] = $endarr[0];
        $row['endminute'] = $endarr[1];
		$row['speaker'] = $data->speaker;
		$row['eventid'] = $data->eventid;
		$row['description'] = $data->description;//['text' => $data->description];
        return $row;
    }

    public function agendainfo($eventid) {
        global $DB, $PAGE, $OUTPUT;
        $systemcontext = context_system::instance();        
        $renderer = $PAGE->get_renderer('local_events');
        $filterparams  = $renderer->get_catalog_agendainfo(true);
        $agendadetails = $renderer->get_catalog_agendainfo();
        $filterparams['agendadetails'] = $agendadetails;
        $filterparams['eventid'] = $eventid;
        $renderer->listofagenda($filterparams);
    }

    public static function get_listof_agenda($stable, $filterdata = null) {
        global $DB;
        $params          = array();
        $agenda      = array();
        $agendacount = 0;
        $concatsql       = '';

        if (isset($stable->agendaid) && $stable->agendaid > 0) {
            $concatsql .= " AND a.id = :agendaid";
            $params['agendaid'] = $stable->agendaid;
        }
        $countsql = "SELECT COUNT(a.id) ";
        $fromsql = " SELECT a.id, a.title, a.*";
        $sql = " FROM {local_eventagenda} AS a";
        $sql .=  " JOIN {local_events} e ON a.eventid = e.id";
        $sql .= " WHERE a.eventid = $stable->eventid";
        $sql .= $concatsql;
        if (isset($stable->agendaid) && $stable->agendaid > 0) {
            $agenda = $DB->get_record_sql($fromsql . $sql, $params);
        } else {
            try {
                $agendacount = $DB->count_records_sql($countsql . $sql, $params);
                if ($stable->thead == false) {
                    $sql .= " ORDER BY a.id DESC";
                    $agenda = $DB->get_records_sql($fromsql . $sql, $params, $stable->start, $stable->length);
                }
            } catch (dml_exception $ex) {
                $agendacount = 0;
            }
        }
        if (isset($stable->agendaid) && $stable->agendaid > 0) {
            return $agenda;
        } else {
            return compact('agenda', 'agendacount');
        }
    }

    public function add_update_attendee($data) {
        global $DB,$USER;
		$systemcontext = context_system::instance();
        $eventmethod = $data->event_name=$DB->get_field('local_events','method',array('id'=>$data->eventid));   
		if ($data->id) {
            $data->timemodified = time();
            $data->usermodified	= $USER->id;
           
			$DB->update_record('local_event_attendees', $data);
			return $data->id;
		} else {
            
            if(!is_siteadmin() && has_capability('local/organization:manage_organizationofficial',$systemcontext)) {
                $userids = $data->userid;

                foreach($userids as $userid) {
                    $this->enrol_event($data->eventid, $userid, $USER->id);

                    $autoapproval = (new  \local_exams\local\exams())->autoapproval();
                    if( $autoapproval == 1){     

                    $user = $DB->get_record('user', ['id' => $userid]);
                    $userrecord = $DB->get_record('local_users',array('userid'=>$userid));
                    if (current_language() == 'ar') {
                        $data->event_name = $DB->get_field('local_events','titlearabic',array('id'=>$data->eventid));               
               
                    } else {
                        $data->event_name=$DB->get_field('local_events','title',array('id'=>$data->eventid));               
                       
                    } 

                    $localuserrecord = $DB->get_record('local_users',['userid'=>$userid]);
                    $data->event_userfullname= ($localuserrecord)? (($localuserrecord->lang == 'ar') ? $localuserrecord->firstnamearabic.' '.$localuserrecord->middlenamearabic.' '.$localuserrecord->thirdnamearabic.' '.$localuserrecord->lastnamearabic  : $localuserrecord->firstname.' '.$localuserrecord->middlenameen.' '.$localuserrecord->thirdnameen.' '.$localuserrecord->lastname) :fullname($userrecord);       
                 
                    (new \local_events\notification())->event_notification('events_registration', $touser=$user,$fromuser=$USER, $data,$waitinglistid=0);

                }
            } 
        }else {

               
                if ($data->regtype == 1) {
                   
                    $userids = $data->userid;
                    foreach($userids as $userid) {

                
                        $user = $DB->get_record('user', ['id' => $userid]);
                        $userrecord = $DB->get_record('local_users',array('userid'=>$userid));
                        $data->userid = $userid;
                        $data->realuser = ($USER->realuser) ? $USER->realuser :0;
                        

                        if (current_language() == 'ar') {
                            $data->event_name = $DB->get_field('local_events','titlearabic',array('id'=>$data->eventid));               
                                            
                        } else {
                            $data->event_name=$DB->get_field('local_events','title',array('id'=>$data->eventid));               


                        } 

                        $localuserrecord = $DB->get_record('local_users',['userid'=>$userid]);
                        $event_userfullname= ($localuserrecord)? (($localuserrecord->lang == 'ar') ? $localuserrecord->firstnamearabic.' '.$localuserrecord->middlenamearabic.' '.$localuserrecord->thirdnamearabic.' '.$localuserrecord->lastnamearabic  : $localuserrecord->firstname.' '.$localuserrecord->middlenameen.' '.$localuserrecord->thirdnameen.' '.$localuserrecord->lastname) :fullname($userrecord);   
                        $data->name = $event_userfullname;
                        $data->email = $user->email;
                        $data->timecreated = time();
                        $data->usercreated = $USER->id;
                        $DB->insert_record('local_event_attendees', $data);      
                       
                        (new \local_events\notification())->event_notification('events_registration', $touser=$user,$fromuser=$USER, $data,$waitinglistid=0);
                    }
                } else {

                    $data->userid = 0;
                    $data->realuser = ($USER->realuser) ? $USER->realuser :0;
                    $data->timecreated = time();
                    $eventattendees=$DB->insert_record('local_event_attendees', $data);
                    $eventattendeelist=$DB->get_record('local_event_attendees',array('id'=> $eventattendees));
                    $eventattendee=get_admin();              
                    $eventattendee->firstname.$eventattendee->lastname=$eventattendeelist->name;
                    $eventattendee->email=$eventattendeelist->email;
                    $data->event_name=$DB->get_field('local_events','title',array('id'=>$data->eventid));               
                    $data->event_userfullname = $data->name;                   
                    (new \local_events\notification())->event_notification('events_registration', $touser=$eventattendee,$fromuser=$USER, $data,$waitinglistid=0);
                    return $eventattendees;
                }
            }   
		}
    }


    public function enrol_event($eventid, $userid, $usercreated = false, $type =false,$orderid = false) {
        global $DB, $USER;
        $systemcontext = context_system::instance();    
        $current_user = ($usercreated) ? $usercreated : $USER->id;
        if($DB->record_exists('local_event_attendees', ['eventid' => $eventid, 'userid' => $userid])){
            return false;
        }
        $user = $DB->get_record('user', ['id' => $userid]);
        $data = new stdClass();
        $data->userid = $userid;
        $data->realuser =($USER->realuser > 0) ? $USER->realuser :0;
        $data->name = fullname($user);
        $data->email = $user->email;
        $data->eventid = $eventid;
        $data->timecreated = time();
        $data->usercreated = $current_user;
        $data->enrolstatus = ($orderid > 0) ? 0 : 1;
        $data->orderid = ($orderid > 0) ? $orderid : 0;
        $organizationid = (new \tool_product\product)->get_user_organization($current_user);
        $data->organization = $organizationid ? $organizationid : 0;
        $newattendee = $DB->insert_record('local_event_attendees', $data);
        if($newattendee){
            $user = $DB->get_record('user', ['id' => $userid]);
            if($user->lang == 'en'){
                $data->event_name=$DB->get_field('local_events','title',array('id'=>$eventid));               

            } else {
                $data->event_name=$DB->get_field('local_events','titlearabic',array('id'=>$eventid));   

            }
            $localuserrecord = $DB->get_record('local_users',['userid'=>$userid]);
            $data->event_userfullname =($localuserrecord)? (($localuserrecord->lang == 'ar') ? $localuserrecord->firstnamearabic.' '.$localuserrecord->middlenamearabic.' '.$localuserrecord->thirdnamearabic.' '.$localuserrecord->lastnamearabic  : $localuserrecord->firstname.' '.$localuserrecord->middlenameen.' '.$localuserrecord->thirdnameen.' '.$localuserrecord->lastname) :fullname($user);   
            if(has_capability('local/organization:manage_organizationofficial',$systemcontext)){
                $autoapproval = (new  \local_exams\local\exams())->autoapproval();
                if( $autoapproval == 1){ 
                    (new \local_events\notification())->event_notification('events_registration', $touser=$user,$fromuser=get_admin(), $data,$waitinglistid=0);
                }
            } else {
                (new \local_events\notification())->event_notification('events_registration', $touser=$user,$fromuser=get_admin(), $data,$waitinglistid=0);
            }

            }
        
        $eventprice =(int) $DB->get_field('local_events','price',array('id'=>$eventid));  
        $hall =(int) $DB->get_field('local_events','halladdress',array('id'=>$eventid));    
        $org_official_roleid = $DB->get_field('role','id',array('shortname' => 'organizationofficial')); 
        $is_current_user_org_official = $DB->record_exists_sql('SELECT id FROM {role_assignments} WHERE  contextid = '.$systemcontext->id.' AND roleid = '.$org_official_roleid.' AND userid IN ('.$current_user.')');
        if($is_current_user_org_official && $eventprice == 0) {

            $orderseatsexists = $DB->record_exists_sql("SELECT * FROM {tool_org_order_seats} WHERE tablename='local_events' AND fieldname='id' AND fieldid = $eventid AND orguserid = $current_user");

            if(!$orderseatsexists) {
                $orgrecord = new stdClass();
                $orgrecord->productid = $DB->get_field_sql('SELECT id FROM {tool_products} WHERE referenceid = '.$eventid.' AND category = 3');
                $orgrecord->tablename = 'local_events';
                $orgrecord->fieldname = 'id';
                $orgrecord->fieldid = $eventid;
                $orgrecord->orguserid = $current_user;
                $orgrecord->purchasedseats = 0;
                $orgrecord->availableseats = ($hall)?(int)$DB->get_field('hall','seatingcapacity',array('id'=>$hall)):0;
                $orgrecord->approvalseats = 0;
                $orgrecord->usercreated = $current_user;
                $orgrecord->timecreated = time();
                $DB->insert_record('tool_org_order_seats',$orgrecord);

            }

        }
        if($type !='cancel' && $type !='replace' ) {
            (new \tool_product\product)->upadte_availableseats('local_events', 'id', $eventid, -1);
        }
        return $newattendee;
    }
    public function attendeesinfo() {
        global $DB, $PAGE, $OUTPUT;
        $systemcontext = context_system::instance();        
        $renderer = $PAGE->get_renderer('local_events');
        $filterparams  = $renderer->get_catalog_attendees(true);
        echo $agendadetails = $renderer->get_catalog_attendees();
    }

    public static function get_listof_attendees($stable, $filterdata = null) {
        global $DB, $USER;
        $context = context_system::instance();
        $params          = array();
        $attendee     = array();
        $attendeescount = 0;
        $concatsql       = '';
        if (isset($stable->attendeeid) && $stable->attendeeid > 0) {
            $concatsql .= " AND a.id = :attendeeid";
            $params['attendeeid'] = $stable->attendeeid;
        }
        if(!empty($filterdata->status)) {
            $concatsql .= " AND lu.approvedstatus LIKE '%$filterdata->status%' ";
            $concatsql .= " AND a.userid > 0 ";
        }
        $countsql = "SELECT COUNT(a.id) ";
        $fromsql = " SELECT a.id, a.name, a.email, a.eventid, a.userid, e.id AS eventid, e.status, a.enrolstatus";
        $sql = " FROM {local_event_attendees} AS a";
        $sql .=  " JOIN {local_events} e ON a.eventid = e.id";
        if(!empty($filterdata->status)) {
            $sql .=  " JOIN {local_users} lu ON a.userid = lu.userid";
        }
        if(!is_siteadmin() && has_capability('local/organization:manage_organizationofficial',$context)) {
            $organization = $DB->get_field('local_users','organization',array('userid'=>$USER->id));
            $sql .=  " JOIN {local_users} lu ON a.userid = lu.userid";
            $sql.= " AND lu.organization = $organization  AND lu.deleted = 0 AND lu.approvedstatus = 2";
             // $sql.= " AND a.usercreated = $USER->id ";
        } 
        $sql .= " WHERE a.eventid = $stable->eventid ";
       
        $sql .= $concatsql;
        if (isset($stable->attendeeid) && $stable->attendeeid > 0) {
            $attendee = $DB->get_record_sql($fromsql . $sql, $params);
        } else {
            try {
                $attendeescount = $DB->count_records_sql($countsql . $sql, $params);
                if ($stable->thead == false) {
                    $sql .= " ORDER BY a.timecreated DESC";
                  
                    $attendee = $DB->get_records_sql($fromsql . $sql, $params, $stable->start, $stable->length);
                }
            } catch (dml_exception $ex) {
                $attendeescount = 0;
            }
        }
        if (isset($stable->attendeeid) && $stable->attendeeid > 0) {
            return $attendee;
        } else {
            return compact('attendee', 'attendeescount');
        }
    }

    public function attendee_info($id, $eventid) {
        global $DB, $PAGE, $OUTPUT;
        $systemcontext = context_system::instance();
        $PAGE->set_context($systemcontext);
        $stable = new stdClass();
        $stable->attendeeid = $id;
        $stable->eventid = $eventid;
        $stable->thead = false;
        $stable->start = 0;
        $stable->length = 1;
        $attendee = $this->get_listof_attendees($stable);
        $data = [];
        $data['id'] =  $attendee->id;
        $data['name'] = $attendee->name;
        $data['email'] = $attendee->email;
        /*$statusarray = array(0 => get_string('active', 'local_events'),
        1 => get_string('inactive', 'local_events'),
        2 => get_string('cancelled', 'local_events'),
        3 => get_string('closed', 'local_events'),
        4 => get_string('archieved', 'local_events'));*/
        if (!empty($attendee->userid)) {
            $user = $DB->get_record('local_users', ['userid' => $attendee->userid]);
            $organization = $user->organization? $DB->get_field('local_organization','fullname',array('id'=>$user->organization)):'';;
            $attenddeid = $user->id_number;
            if ($user->approvedstatus == 1) {
                $approvedstatus = get_string('pending','local_events');
             } else if($user->approvedstatus == 2) {
                 $approvedstatus = get_string('approved','local_events');
            } else {
                $approvedstatus = get_string('rejected','local_events');
            }
        } else {
            $organization = '--';
            $attenddeid = '--';
            $approvedstatus = '--';
        }
        $data['status'] = $approvedstatus;
        $data['organization'] = $organization;
        $data['attenddeid'] = $attenddeid;
        $renderer = $PAGE->get_renderer('local_events');
        $org  = $renderer->attendee_info($data);
        return $org;
    }

    public function agenda_info($agdid, $eventid) {
        global $DB, $PAGE, $OUTPUT;
        $systemcontext = context_system::instance();
        $PAGE->set_context($systemcontext);
        $stable = new stdClass();
        $stable->agendaid = $agdid;
        $stable->eventid = $eventid;
        $stable->thead = false;
        $stable->start = 0;
        $stable->length = 1;
        $agenda = $this->get_listof_agenda($stable);
        $data = [];
        $data['id'] = $agenda->id;
        $data['eventid'] = $agenda->eventid;
        $data['title'] = $agenda->title;
        $event = $this->get_agenda_dates($agenda->eventid);
        $day = $event[$agenda->day];
        $data['day'] = date('jS M Y', strtotime($day));
        //$day_text = array('1'=> 'Day1', '2' => 'Day2', '3' => 'Day3');
       // $data['day'] = $day_text[$agenda->day];
        if ($agenda->speaker == 0) {
            $name = get_string('others','local_events');
        } else {
            $name = $DB->get_field_select('local_speakers', 'name', " CONCAT(',',$agenda->speaker,',') LIKE CONCAT('%,',id,',%') ", array());//FIND_IN_SET(id, '$sdata->department')
        }
        $data['speaker'] = $name;
        $starttime = strtotime($agenda->timefrom);
        $timefrom = date('h:i A', $starttime);
        $endtime =  strtotime($agenda->timeto);
        $timeto = date('h:i A', $endtime);
        $data['time'] = $timefrom. ' - ' .$timeto;
        $renderer = $PAGE->get_renderer('local_events');
        return  $renderer->agenda_info($data);
    }

    public function add_update_speaker($data) {
        global $DB,$USER,$CFG;
		$systemcontext = context_system::instance();
		if (isset($data->logo)) {
			$data->logo = $data->logo;
			file_save_draft_area_files($data->logo, $systemcontext->id, 'local_events', 'logo', $data->logo);
		}
        if ($data->id) {
            $speaker = $DB->get_record('local_speakers', ['id' => $data->speaker]);
            $speaker->name = $data->name;
            $speaker->specialist = $data->specialist;
            $speaker->biography = $data->biography;
            $speaker->linked_profile = $data->linked_profile; 
            $speaker->logo = $data->logo;
            $speaker->timemodified = time();
            $speaker->id = $DB->update_record('local_speakers', $speaker);
            $data->timemodified = time();
			$DB->update_record('local_event_speakers', $data);
             // notification events  onchange         
            $attendees = $DB->get_records('local_event_attendees', ['eventid' => $data->eventid]);          
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
                $thispageurl = new \moodle_url('/local/events/view.php?id='.$data->eventid);
            
                $row1=[];
                $row1['RelatedModuleName']=$DB->get_field('local_events','title',array('id'=>$data->eventid));;
                $row1['RelatedModulesLink']=$thispageurl;

                $localuserrecord = $DB->get_record('local_users',['userid'=>$userid]);
                $event_userfullname= ($localuserrecord)? (($localuserrecord->lang == 'ar') ? $localuserrecord->firstnamearabic.' '.$localuserrecord->middlenamearabic.' '.$localuserrecord->thirdnamearabic.' '.$localuserrecord->lastnamearabic  : $localuserrecord->firstname.' '.$localuserrecord->middlenameen.' '.$localuserrecord->thirdnameen.' '.$localuserrecord->lastname) :fullname($touser);  
                $row1['FullName']=$event_userfullname;              
                $myobject=(new \local_events\notification);
                $myobject->event_notification('events_onchange',$touser, $USER,$row1,$waitinglistid=0);
              
            }
			return $data->id;
		} else {
            if ($data->speaker == '0') {
                $speaker = new stdClass;
                $speaker->name = $data->name;
                $speaker->specialist = $data->specialist;
                $speaker->biography = $data->biography;
                $speaker->linked_profile = $data->linked_profile; 
                if($data->logo) {
                    $speaker->logo = $data->logo;
                }
                $speaker->timecreated = time();
                $speaker->id = $DB->insert_record('local_speakers', $speaker);
                $data->speaker = $speaker->id;
                $data->timecreated = time();
                // Trigger speaker registered.
                $id=$DB->insert_record('local_event_speakers', $data);
                $eventparams = array('context' => context_system::instance(),'objectid'=>$speaker->id);
                $event = event\speaker_registerd::create($eventparams);
                $event->trigger();
                $eventdata=$DB->get_record('local_events',array('id'=>$data->eventid));           
               
                $eventdata->event_speakername=$DB->get_field('local_speakers','name',array('id'=>$data->speaker));  
                $manager = $DB->get_records_sql(" SELECT u.id, CONCAT(u.firstname,'',u.lastname) AS fullname 
                FROM {user} u JOIN {local_events} e ON concat(',', e.eventmanager, ',') LIKE concat('%,',u.id,',%')
                WHERE e.id = :eventid",['eventid' => $data->eventid]);
                foreach ($manager as $managers ){
                        
                        $completeusers=$DB->get_record('user',array('id'=> $managers->id));
                        if($completeusers->lang == 'ar') {
                            $eventdata->event_name=$eventdata->titlearabic;
                        
                        } else {
                            $eventdata->event_name=$eventdata->title;
                           
                        }

                        $localuserrecord = $DB->get_record('local_users',['userid'=> $managers->id]);
                        $eventdata->event_managername= ($localuserrecord)? (($localuserrecord->lang  == 'ar') ? $localuserrecord->firstnamearabic.' '.$localuserrecord->middlenamearabic.' '.$localuserrecord->thirdnamearabic.' '.$localuserrecord->lastnamearabic  : $localuserrecord->firstname.' '.$localuserrecord->middlenameen.' '.$localuserrecord->thirdnameen.' '.$localuserrecord->lastname) :fullname($completeusers);  

                                                    
                        (new \local_events\notification())->event_notification('events_speakers', $touser= $completeusers,$fromuser=$USER, $eventdata,$waitinglistid=0);
                }
                 // notification events  onchange
                 $attendees = $DB->get_records('local_event_attendees', ['eventid' => $data->eventid]);          
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
                     $thispageurl = new \moodle_url('/local/events/view.php?id='.$data->eventid);                 
                     $row1=[];
                     $row1['RelatedModuleName']=$eventdata->title;
                     $row1['RelatedModulesLink']=$thispageurl;
                    $localuserrecord = $DB->get_record('local_users',['userid'=> $attendee->userid]);
                    $fullname= ($localuserrecord)? (($localuserrecord->lang  == 'ar') ? $localuserrecord->firstnamearabic.' '.$localuserrecord->middlenamearabic.' '.$localuserrecord->thirdnamearabic.' '.$localuserrecord->lastnamearabic  : $localuserrecord->firstname.' '.$localuserrecord->middlenameen.' '.$localuserrecord->thirdnameen.' '.$localuserrecord->lastname) :fullname($touser); 
                     $row1['FullName']=$fullname;              
                     $myobject=(new \local_events\notification);
                     $myobject->event_notification('events_onchange',$touser, $USER,$row1,$waitinglistid=0);
                   
                 }
                return $id;
            } else {
                $data->timecreated = time();
                // Trigger speaker registered.
                $id=$DB->insert_record('local_event_speakers', $data);
                $eventparams = array('context' => context_system::instance(),'objectid'=>$id);
                $event = event\speaker_registerd::create($eventparams);
                $event->trigger();
                              
                $eventdata=$DB->get_record('local_events',array('id'=>$data->eventid));       
                
                $eventdata->event_speakername=$DB->get_field('local_speakers','name',array('id'=>$data->speaker));  
                $manager = $DB->get_records_sql(" SELECT u.id, CONCAT(u.firstname,'',u.lastname) AS fullname 
                FROM {user} u JOIN {local_events} e ON concat(',', e.eventmanager, ',') LIKE concat('%,',u.id,',%')
                WHERE e.id = :eventid",['eventid' => $data->eventid]);
                foreach ($manager as $managers ){                        
                         $completeusers=$DB->get_record('user',array('id'=> $managers->id));    
                         if($completeusers->lang == 'ar') {
                            $eventdata->event_name=$eventdata->titlearabic;
                           
                        } else {
                            $eventdata->event_name=$eventdata->title;
                           
                        }  

                        $localuserrecord = $DB->get_record('local_users',['userid'=> $managers->id]);
                        $eventdata->event_managername= ($localuserrecord)? (($localuserrecord->lang  == 'ar') ? $localuserrecord->firstnamearabic.' '.$localuserrecord->middlenamearabic.' '.$localuserrecord->thirdnamearabic.' '.$localuserrecord->lastnamearabic  : $localuserrecord->firstname.' '.$localuserrecord->middlenameen.' '.$localuserrecord->thirdnameen.' '.$localuserrecord->lastname) :fullname($completeusers);                            
                        (new \local_events\notification())->event_notification('events_speakers', $touser= $completeusers,$fromuser=get_admin(), $eventdata,$waitinglistid=0);
                }
                 // notification events  onchange
                 $attendees = $DB->get_records('local_event_attendees', ['eventid' => $data->eventid]);
          
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
                     $thispageurl = new \moodle_url('/local/events/view.php?id='.$data->eventid);
                 
                     $row1=[];
                     $row1['RelatedModuleName']=$eventdata->title;
                     $row1['RelatedModulesLink']=$thispageurl;
                     $localuserrecord = $DB->get_record('local_users',['userid'=> $attendee->userid]);
                     $fname= ($localuserrecord)? (($localuserrecord->lang  == 'ar') ? $localuserrecord->firstnamearabic.' '.$localuserrecord->middlenamearabic.' '.$localuserrecord->thirdnamearabic.' '.$localuserrecord->lastnamearabic  : $localuserrecord->firstname.' '.$localuserrecord->middlenameen.' '.$localuserrecord->thirdnameen.' '.$localuserrecord->lastname) :fullname($touser);
                     $row1['FullName']=$fname;              
                     $myobject=(new \local_events\notification);
                     $myobject->event_notification('events_onchange',$touser, $USER,$row1,$waitinglistid=0);
                   
                 }
                return $id;
            }  
		}
    }

    public function speakersinfo($eventid) {
        global $DB, $PAGE, $OUTPUT;
        $systemcontext = context_system::instance();        
        $renderer = $PAGE->get_renderer('local_events');
        $filterparams  = $renderer->get_catalog_speakers(true);
        $speakerdetails = $renderer->get_catalog_speakers();
        $filterparams['speakerdetails'] = $speakerdetails;
        $filterparams['eventid'] = $eventid;
        $renderer->listofspeaker($filterparams);
    }

    public static function get_listof_speakers($stable, $filtervalues = null) {
        global $DB;
        $params          = array();
        $speakers      = array();
        $speakerscount = 0;
        $concatsql       = '';

        if (isset($stable->speakerid) && $stable->speakerid > 0) {
            $concatsql .= " AND es.id = :speakerid";
            $params['speakerid'] = $stable->speakerid;
        }
        $countsql = "SELECT COUNT(es.id) ";
        $fromsql = "SELECT  es.*, e.id AS eventid, e.title, ls.id AS speakerid, ls.name, ls.specialist, ls.linked_profile ";
        $sql = " FROM {local_event_speakers} AS es";
        $sql .=  " JOIN {local_events} e ON es.eventid = e.id";
        $sql .=  " JOIN {local_speakers} ls ON ls.id = es.speaker";
        $sql .= " WHERE es.eventid = $stable->eventid";
        $sql .= $concatsql;
        if (isset($stable->speakerid) && $stable->speakerid > 0) {
            $speakers = $DB->get_record_sql($fromsql . $sql, $params);
        } else {
            try {
                $speakerscount = $DB->count_records_sql($countsql . $sql, $params);
                if ($stable->thead == false) {
                    $sql .= " ORDER BY es.id DESC";
                    $speakers = $DB->get_records_sql($fromsql . $sql, $params, $stable->start, $stable->length);
                }
            } catch (\dml_exception $ex) {
                $speakerscount = 0;
            }
        }
        if (isset($stable->speakerid) && $stable->speakerid > 0) {
            return $speakers;
        } else {
            return compact('speakers', 'speakerscount');
        }
    }

    public function speaker_view($id, $eventid) {
        global $DB, $PAGE, $OUTPUT;
        $systemcontext = context_system::instance();
        $PAGE->set_context($systemcontext);
        $stable = new stdClass();
        $stable->speakerid = $id;
        $stable->eventid = $eventid;
        $stable->thead = false;
        $stable->start = 0;
        $stable->length = 1;
        $speaker = $this->get_listof_speakers($stable);
        $data = [];
        $data['id'] = $speaker->id;
        $data['eventid'] = $speaker->eventid;
        $data['eventname'] = $speaker->title;
        $data['speakername'] = $speaker->name;
        $data['specialist'] = $speaker->specialist;
        $data['linked_profile'] = $speaker->linked_profile;
        $renderer = $PAGE->get_renderer('local_events');
        return  $renderer->speaker_view($data);
    }


    public function add_update_sponsor($data) {
        global $DB,$CFG,$USER;
		$systemcontext = context_system::instance();
        if (isset($data->logo)) {
			$data->logo = $data->logo;
			file_save_draft_area_files($data->logo, $systemcontext->id, 'local_events', 'logo', $data->logo);
		}
        if ($data->id) {
            $sponsor = $DB->get_record('local_sponsors',['id' => $data->sponsor]);
            $sponsor->name = $data->name;
            $sponsor->biography = $data->biography;
            $sponsor->logo = $data->logo;
            $sponsor->website = $data->website;
            $sponsor->timemodified = time();
            $sponsor->id = $DB->update_record('local_sponsors', $sponsor);
            $data->timemodified = time();
			$DB->update_record('local_event_sponsors', $data);
                         // notification events  onchange                      
              $attendees = $DB->get_records('local_event_attendees', ['eventid' => $data->eventid]);          
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
                  $thispageurl = new \moodle_url('/local/events/view.php?id='.$data->eventid);              
                  $row1=[];
                  $row1['RelatedModuleName']=$DB->get_field('local_events','title',array('id'=>$data->eventid));
                  $row1['RelatedModulesLink']=$thispageurl;

                  $localuserrecord = $DB->get_record('local_users',['userid'=> $attendee->userid]);
                  $fname= ($localuserrecord)? (($localuserrecord->lang  == 'ar') ? $localuserrecord->firstnamearabic.' '.$localuserrecord->middlenamearabic.' '.$localuserrecord->thirdnamearabic.' '.$localuserrecord->lastnamearabic  : $localuserrecord->firstname.' '.$localuserrecord->middlenameen.' '.$localuserrecord->thirdnameen.' '.$localuserrecord->lastname) :fullname($touser);
                  $row1['FullName']=$fname;              
                  $myobject=(new \local_events\notification);
                  $myobject->event_notification('events_onchange',$touser, $USER,$row1,$waitinglistid=0);                
              }
			return $data->id;
		} else {
            if ($data->sponsor == '0') {
                $sponsor = new stdClass;
                $sponsor->name = $data->name;
                $sponsor->biography = $data->biography;
                $sponsor->logo = $data->logo;
                $sponsor->website = $data->website;
                $sponsor->timecreated = time();
                $sponsor->id = $DB->insert_record('local_sponsors', $sponsor);
                $data->sponsor =  $sponsor->id;
                $data->timecreated = time();
                $id=$DB->insert_record('local_event_sponsors', $data);
                // Event Sponsor Registerd
                $eventparams = array('context' => context_system::instance(),'objectid'=>$id);
                $event = event\sponsor_registered::create($eventparams);
                $event->trigger();
                $eventdata=$DB->get_record('local_events',array('id'=>$data->eventid));              
                $eventdata->event_sponsorname=$DB->get_field('local_sponsors','name',array('id'=>$data->sponsor));  
                $manager = $DB->get_records_sql(" SELECT u.id, CONCAT(u.firstname,'',u.lastname) AS fullname 
                FROM {user} u JOIN {local_events} e ON concat(',', e.eventmanager, ',') LIKE concat('%,',u.id,',%')
                WHERE e.id = :eventid",['eventid' => $data->eventid]);
                foreach ($manager as $managers ){                        
                        $completeusers=$DB->get_record('user',array('id'=> $managers->id));
                        if($completeusers->lang == 'ar') {
                            $eventdata->event_name=$eventdata->titlearabic;
                        } else {
                            $eventdata->event_name=$eventdata->title;
                        } 

                        $localuserrecord = $DB->get_record('local_users',['userid'=> $managers->id]);
                        $eventdata->event_managername = ($localuserrecord)? (($localuserrecord->lang  == 'ar') ? $localuserrecord->firstnamearabic.' '.$localuserrecord->middlenamearabic.' '.$localuserrecord->thirdnamearabic.' '.$localuserrecord->lastnamearabic  : $localuserrecord->firstname.' '.$localuserrecord->middlenameen.' '.$localuserrecord->thirdnameen.' '.$localuserrecord->lastname) :fullname($completeusers);
                                                 
                        (new \local_events\notification())->event_notification('events_sponsors', $touser=$completeusers,$fromuser=$USER,$eventdata,$waitinglistid=0);
                }
                 // notification events  onchange
                 $attendees = $DB->get_records('local_event_attendees', ['eventid' => $data->eventid]);          
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
                     $thispageurl = new \moodle_url('/local/events/view.php?id='.$data->eventid);                 
                     $row1=[];
                     $row1['RelatedModuleName']=$eventdata->title;
                     $row1['RelatedModulesLink']=$thispageurl;

                    $localuserrecord = $DB->get_record('local_users',['userid'=> $attendee->userid]);
                    $fname = ($localuserrecord)? (($localuserrecord->lang  == 'ar') ? $localuserrecord->firstnamearabic.' '.$localuserrecord->middlenamearabic.' '.$localuserrecord->thirdnamearabic.' '.$localuserrecord->lastnamearabic  : $localuserrecord->firstname.' '.$localuserrecord->middlenameen.' '.$localuserrecord->thirdnameen.' '.$localuserrecord->lastname) :fullname($touser);
                     $row1['FullName']=$fname;              
                     $myobject=(new \local_events\notification);
                     $myobject->event_notification('events_onchange',$touser, $USER,$row1,$waitinglistid=0);                   
                 }
                return $id;
            } else {
            $data->timecreated = time();
            $id=$DB->insert_record('local_event_sponsors', $data);
            // Event Sponsor Registerd
            $eventparams = array('context' => context_system::instance(),'objectid'=>$id);
            $event = event\sponsor_registered::create($eventparams);
            $event->trigger();

            $eventdata=$DB->get_record('local_events',array('id'=>$data->eventid));           
            
            $eventdata->event_sponsorname=$DB->get_field('local_sponsors','name',array('id'=>$data->sponsor));  
            $manager = $DB->get_records_sql(" SELECT u.id, CONCAT(u.firstname,'',u.lastname) AS fullname 
            FROM {user} u JOIN {local_events} e ON concat(',', e.eventmanager, ',') LIKE concat('%,',u.id,',%')
            WHERE e.id = :eventid",['eventid' => $data->eventid]);
            foreach ($manager as $managers ){
                    
                    $completeusers=$DB->get_record('user',array('id'=> $managers->id));
                    if($completeusers->lang == 'ar') {
                        $eventdata->event_name=$eventdata->titlearabic;
                    } else {
                        $eventdata->event_name=$eventdata->title;                       
                    }   

                    $localuserrecord = $DB->get_record('local_users',['userid'=> $managers->id]);
                    $eventdata->event_managername= ($localuserrecord)? (($localuserrecord->lang  == 'ar') ? $localuserrecord->firstnamearabic.' '.$localuserrecord->middlenamearabic.' '.$localuserrecord->thirdnamearabic.' '.$localuserrecord->lastnamearabic  : $localuserrecord->firstname.' '.$localuserrecord->middlenameen.' '.$localuserrecord->thirdnameen.' '.$localuserrecord->lastname) :fullname($completeusers);  
                                                          
                    (new \local_events\notification())->event_notification('events_sponsors', $touser=$completeusers,$fromuser=$USER,$eventdata,$waitinglistid=0);
            }
             // notification events  onchange
             $attendees = $DB->get_records('local_event_attendees', ['eventid' => $data->eventid]);
          
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
                 $thispageurl = new \moodle_url('/local/events/view.php?id='.$data->eventid);             
                 $row1=[];
                 $row1['RelatedModuleName']=$eventdata->title;
                 $row1['RelatedModulesLink']=$thispageurl;

                $localuserrecord = $DB->get_record('local_users',['userid'=> $attendee->userid]);
                $fname = ($localuserrecord)? (($localuserrecord->lang  == 'ar') ? $localuserrecord->firstnamearabic.' '.$localuserrecord->middlenamearabic.' '.$localuserrecord->thirdnamearabic.' '.$localuserrecord->lastnamearabic  : $localuserrecord->firstname.' '.$localuserrecord->middlenameen.' '.$localuserrecord->thirdnameen.' '.$localuserrecord->lastname) :fullname($touser);
                 $row1['FullName']=$fname;              
                 $myobject=(new \local_events\notification);
                 $myobject->event_notification('events_onchange',$touser, $USER,$row1,$waitinglistid=0);               
             }
			return $id;
            }
		}
    }

    public static function get_listof_sponsors($stable, $filtervalues=null) {
        global $DB;
        $params          = array();
        $sponsors      = array();
        $sponsorscount = 0;
        $concatsql       = '';

        if (isset($stable->sponsorid) && $stable->sponsorid > 0) {
            $concatsql .= " AND es.id = :sponsorid";
            $params['sponsorid'] = $stable->sponsorid;
        }
        $countsql = "SELECT COUNT(es.id) ";
        $fromsql = "SELECT  es.*, e.id AS eventid, e.title, lp.name, lp.logo ";
        $sql = " FROM {local_event_sponsors} AS es";
        $sql .=  " JOIN {local_events} e ON es.eventid = e.id";
        $sql .=  " JOIN {local_sponsors} lp ON lp.id = es.sponsor";
        $sql .= " WHERE es.eventid = $stable->eventid";
        $sql .= $concatsql;
        if (isset($stable->sponsorid) && $stable->sponsorid > 0) {
            $sponsors = $DB->get_record_sql($fromsql . $sql, $params);
        } else {
            try {
                $sponsorscount = $DB->count_records_sql($countsql . $sql, $params);
                
                if ($stable->thead == false) {
                    $sql .= " ORDER BY es.id DESC";

                    $sponsors = $DB->get_records_sql($fromsql . $sql, $params, $stable->start, $stable->length);
                }
            } catch (\dml_exception $ex) {
                $sponsorscount = 0;
            }
        }
        if (isset($stable->sponsorid) && $stable->sponsorid > 0) {
            return $sponsors;
        } else {
            return compact('sponsors', 'sponsorscount');
        }
    }

    public function sponsorsinfo($eventid) {
        global $DB, $PAGE, $OUTPUT;
        $systemcontext = context_system::instance();        
        $renderer = $PAGE->get_renderer('local_events');
        $filterparams  = $renderer->get_catalog_sponsors(true);
        $sponsordetails = $renderer->get_catalog_sponsors();
        $filterparams['sponsordetails'] = $sponsordetails;
        $filterparams['eventid'] = $eventid;
        $renderer->listofsponsor($filterparams);
    
    }

    public function sponsor_view ($id, $eventid) {
        global $DB, $PAGE, $OUTPUT;
        $params = array('id' => $id, 'eventid' => $eventid);
        $systemcontext = context_system::instance();
        $PAGE->set_context($systemcontext);
        $stable = new stdClass();
        $stable->sponsorid = $id;
        $stable->eventid = $eventid;
        $stable->thead = false;
        $stable->start = 0;
        $stable->length = 1;
        $sponsor = $this->get_listof_sponsors($stable);
        $data = [];
        $data['id'] = $sponsor->id;
        $data['eventid'] = $sponsor->eventid;
        $data['sponsorname'] = $sponsor->name;
        $data['amount'] = $sponsor->amount;
        $catarray = array('0' => get_string('platinum','local_events'), '1' => get_string('gold','local_events'), '2' => get_string('silver','local_events'));
        $data["category"] = $catarray[$sponsor->category];
        if ($sponsor->logo > 0) {
            $logoimg = logo_url($sponsor->logo);
        } else {
            $logoimg = '';
        }
        $data['logo'] = $logoimg;
        $data['showprice'] = false;
        if(has_capability('local/events:manage', $systemcontext) || is_siteadmin() || has_capability('local/organization:manage_event_manager', $systemcontext)) {
            $data['showprice'] = true;
        }
        $renderer = $PAGE->get_renderer('local_events');
        return  $renderer->sponsor_view($data);
    }

    public function add_update_partner($data) {
        global $DB,$USER,$CFG;
		$systemcontext = context_system::instance();
		if (isset($data->logo)) {
			$data->logo = $data->logo;
			file_save_draft_area_files($data->logo, $systemcontext->id, 'local_events', 'logo', $data->logo); 
		}
        
        if (isset($data->picture)) {
			$data->picture = $data->picture;
			file_save_draft_area_files($data->picture, $systemcontext->id, 'local_events', 'logo', $data->picture);
		}
        $logo = $data->logo;
        /*$exists = $DB->record_exists_sql("SELECT id FROM {files} WHERE itemid = :itemid AND filename <> '.' ", array('itemid' => $logo));
        if(!$exists) {
            return 2;
        }*/

        if ($data->id) {
            $partner= $DB->get_record('local_partners', ['id' => $data->partner]);
            $partner->name = $data->name;
            $partner->specialist = $data->specialist;
            $partner->biography = $data->biography;
            $partner->description = $data->description;
            $partner->picture = $data->picture;
            $partner->logo = $data->logo;
            $partner->timemodified = time();
            $DB->update_record('local_partners', $partner);
            $data->timemodified = time();
			$DB->update_record('local_event_partners', $data);
            // notification events  onchange                   
            $attendees = $DB->get_records('local_event_attendees', ['eventid' => $data->eventid]);          
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
                $thispageurl = new \moodle_url('/local/events/view.php?id='.$data->eventid);           
                $row1=[];
                $row1['RelatedModuleName']=$DB->get_field('local_events','title',array('id'=>$data->eventid));;
                $row1['RelatedModulesLink']=$thispageurl;
                $localuserrecord = $DB->get_record('local_users',['userid'=> $attendee->userid]);
                $fname = ($localuserrecord)? (($localuserrecord->lang  == 'ar') ? $localuserrecord->firstnamearabic.' '.$localuserrecord->middlenamearabic.' '.$localuserrecord->thirdnamearabic.' '.$localuserrecord->lastnamearabic  : $localuserrecord->firstname.' '.$localuserrecord->middlenameen.' '.$localuserrecord->thirdnameen.' '.$localuserrecord->lastname) :fullname($touser);
                $row1['FullName']=$fname;              
                $myobject=(new \local_events\notification);
                $myobject->event_notification('events_onchange',$touser, $USER,$row1,$waitinglistid=0);              
            }
			return $data->id;
		} else {
            if ($data->partner == '0') {
                $partner = new stdClass;
                $partner->name = $data->name;
                $partner->specialist = $data->specialist;
                $partner->biography = $data->biography;
                $partner->picture = $data->picture;
                $partner->description = $data->description;
                $partner->logo = $data->logo;
                $partner->timecreated = time();
                $partner->id = $DB->insert_record('local_partners', $partner);
                $data->partner = $partner->id;
                $data->timecreated = time();
                $id=$DB->insert_record('local_event_partners', $data);   
                // Event Sponsor Registerd
                $eventparams = array('context' => context_system::instance(),'objectid'=>$id);
                $event = event\partner_registerd::create($eventparams);
                $event->trigger();

                $eventdata=$DB->get_record('local_events',array('id'=>$data->eventid));   
                
                $eventdata->event_partnername=$DB->get_field('local_partners','name',array('id'=>$data->partner));  
                $manager = $DB->get_records_sql(" SELECT u.id, CONCAT(u.firstname,'',u.lastname) AS fullname 
                FROM {user} u JOIN {local_events} e ON concat(',', e.eventmanager, ',') LIKE concat('%,',u.id,',%')
                WHERE e.id = :eventid",['eventid' => $data->eventid]);
                foreach ($manager as $managers ){                       
                         $completeusers = $DB->get_record('user',array('id'=> $managers->id));
                         if($completeusers->lang == 'ar') {
                            $eventdata->event_name=$eventdata->titlearabic;
                         } else {
                            $eventdata->event_name=$eventdata->title;
                         } 

                        $localuserrecord = $DB->get_record('local_users',['userid'=> $managers->id]);
                        $eventdata->event_managername = ($localuserrecord)? (($localuserrecord->lang  == 'ar') ? $localuserrecord->firstnamearabic.' '.$localuserrecord->middlenamearabic.' '.$localuserrecord->thirdnamearabic.' '.$localuserrecord->lastnamearabic  : $localuserrecord->firstname.' '.$localuserrecord->middlenameen.' '.$localuserrecord->thirdnameen.' '.$localuserrecord->lastname) :fullname($completeusers);                         

                        (new \local_events\notification())->event_notification('events_partners', $touser=$completeusers,$fromuser=$USER,$eventdata,$waitinglistid=0);
                }
                // notification events  onchange
                $attendees = $DB->get_records('local_event_attendees', ['eventid' => $data->eventid]);
            
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
                    $thispageurl = new \moodle_url('/local/events/view.php?id='.$data->eventid);
                
                    $row1=[];
                    $row1['RelatedModuleName']=$eventdata->title;
                    $row1['RelatedModulesLink']=$thispageurl;

                    $localuserrecord = $DB->get_record('local_users',['userid'=> $attendee->userid]);
                    $fname = ($localuserrecord)? (($localuserrecord->lang  == 'ar') ? $localuserrecord->firstnamearabic.' '.$localuserrecord->middlenamearabic.' '.$localuserrecord->thirdnamearabic.' '.$localuserrecord->lastnamearabic  : $localuserrecord->firstname.' '.$localuserrecord->middlenameen.' '.$localuserrecord->thirdnameen.' '.$localuserrecord->lastname) :fullname($touser);
                    $row1['FullName']=$fname;              
                    $myobject=(new \local_events\notification);
                    $myobject->event_notification('events_onchange',$touser, $USER,$row1,$waitinglistid=0);
                
                }
                    return $id;
            } else {
                $data->timecreated = time();
                $id=$DB->insert_record('local_event_partners', $data);
                // Event Sponsor Registerd
                $eventparams = array('context' => context_system::instance(),'objectid'=>$id);
                $event = event\partner_registerd::create($eventparams);
                $event->trigger();
                
                $eventdata=$DB->get_record('local_events',array('id'=>$data->eventid));           
              
                $eventdata->event_partnername=$DB->get_field('local_partners','name',array('id'=>$data->partner));  
                $manager = $DB->get_records_sql(" SELECT u.id, CONCAT(u.firstname,'',u.lastname) AS fullname 
                FROM {user} u JOIN {local_events} e ON concat(',', e.eventmanager, ',') LIKE concat('%,',u.id,',%')
                WHERE e.id = :eventid",['eventid' => $data->eventid]);
                foreach ($manager as $managers ){
                        
                        $completeusers=$DB->get_record('user',array('id'=> $managers->id)); 
                        if($completeusers->lang == 'ar') {
                            $eventdata->event_name=$eventdata->titlearabic;
                        } else {
                            $eventdata->event_name=$eventdata->title;
                        } 

                        $localuserrecord = $DB->get_record('local_users',['userid'=> $managers->id]);
                        $eventdata->event_managername= ($localuserrecord)? (($localuserrecord->lang  == 'ar') ? $localuserrecord->firstnamearabic.' '.$localuserrecord->middlenamearabic.' '.$localuserrecord->thirdnamearabic.' '.$localuserrecord->lastnamearabic  : $localuserrecord->firstname.' '.$localuserrecord->middlenameen.' '.$localuserrecord->thirdnameen.' '.$localuserrecord->lastname) :fullname($completeusers);  
                                   
                        (new \local_events\notification())->event_notification('events_partners', $touser=$completeusers,$fromuser=$USER,$eventdata,$waitinglistid=0);
                }
                // notification events  onchange
                $attendees = $DB->get_records('local_event_attendees', ['eventid' => $data->eventid]);
          
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
                    $thispageurl = new \moodle_url('/local/events/view.php?id='.$data->eventid);                
                    $row1=[];
                    $row1['RelatedModuleName']=$eventdata->title;
                    $row1['RelatedModulesLink']=$thispageurl;
                    $localuserrecord = $DB->get_record('local_users',['userid'=> $attendee->userid]);
                    $fname = ($localuserrecord)? (($localuserrecord->lang  == 'ar') ? $localuserrecord->firstnamearabic.' '.$localuserrecord->middlenamearabic.' '.$localuserrecord->thirdnamearabic.' '.$localuserrecord->lastnamearabic  : $localuserrecord->firstname.' '.$localuserrecord->middlenameen.' '.$localuserrecord->thirdnameen.' '.$localuserrecord->lastname) :fullname($touser);
                    $row1['FullName']=$fname;              
                    $myobject=(new \local_events\notification);
                    $myobject->event_notification('events_onchange',$touser, $USER,$row1,$waitinglistid=0);                  
                }
               $myobject=(new \local_events\notification);
               $myobject->event_notification('events_onchange',$touser, $USER,$row1,$waitinglistid=0);
               return $id;
            }
		}
    }

    public function partnersinfo($eventid) {
        global $DB, $PAGE, $OUTPUT;
        $systemcontext = context_system::instance();        
        $renderer = $PAGE->get_renderer('local_events');
        $filterparams  = $renderer->get_catalog_partners(true);
        $partnerdetails = $renderer->get_catalog_partners();
        $filterparams['partnerdetails'] = $partnerdetails;
        $filterparams['eventid'] = $eventid;
        $renderer->listofpartners($filterparams);
    }

    public static function get_listof_partners($stable, $filtervalues=null) {
        global $DB;
        $params          = array();
        $partners      = array();
        $partnerscount = 0;
        $concatsql       = '';

        if (isset($stable->partnerid) && $stable->partnerid > 0) {
            $concatsql .= " AND ep.id = :partnerid";
            $params['partnerid'] = $stable->partnerid;
        }
        $countsql = "SELECT COUNT(ep.id) ";
        $fromsql = "SELECT  ep.*, e.id AS eventid, e.title, lp.name, lp.logo, lp.picture, lp.description";
        $sql = " FROM {local_event_partners} AS ep";
        $sql .=  " JOIN {local_events} e ON ep.eventid = e.id";
        $sql .=  " JOIN {local_partners} lp ON lp.id = ep.partner";
        $sql .= " WHERE ep.eventid = $stable->eventid";
        $sql .= $concatsql;
        if (isset($stable->partnerid) && $stable->partnerid > 0) {
            $partners = $DB->get_record_sql($fromsql . $sql, $params);
        } else {
            try {
                $partnerscount = $DB->count_records_sql($countsql . $sql, $params);
                if ($stable->thead == false) {
                    $sql .= " ORDER BY ep.id DESC";

                    $partners = $DB->get_records_sql($fromsql . $sql, $params, $stable->start, $stable->length);
                }
            } catch (\dml_exception $ex) {
                $partnerscount = 0;
            }
        }
        if (isset($stable->partnerid) && $stable->partnerid > 0) {
            return $partners;
        } else {
            return compact('partners', 'partnerscount');
        }
    }

    public function partner_view($id, $eventid) {
        global $DB, $PAGE, $OUTPUT;
        $systemcontext = context_system::instance();
        $PAGE->set_context($systemcontext);
        $params = array('id' => $id, 'eventid' => $eventid);
        $stable = new stdClass();
        $stable->partnerid = $id;
        $stable->eventid = $eventid;
        $stable->thead = false;
        $stable->start = 0;
        $stable->length = 1;
        $partner = $this->get_listof_partners($stable);
        $data = [];
        $data['id'] = $partner->id;
        $data['eventid'] = $partner->eventid;
        $data['partnername'] = $partner->name;
        $data['sellingprice'] = $partner->sellingprice;
        $data['actualprice'] = $partner->actualprice;

        $data['description'] = format_text($partner->description, FORMAT_HTML);
        if (empty($partner->description)) {
            $data['isdescription'] = false;
        } else {
            $data['isdescription'] = true;
        }
        if ($partner->logo > 0) {
            $logoimg = logo_url($partner->logo);
            if(!$logoimg){
                $logoimg = '';
            }
        } else {
            $logoimg = '';
        }
        $data['logo'] = $logoimg;
        $data['showprice'] = false;
        if(has_capability('local/events:manage', $systemcontext) || is_siteadmin() || has_capability('local/organization:manage_event_manager', $systemcontext)) {
            $data['showprice'] = true;
        }
        $renderer = $PAGE->get_renderer('local_events');
        return  $renderer->partner_view($data);
    }

    public function get_speakerlist($eventid, $module, $query = null) {
        global $DB;
        $fields = array('ls.name');
        $likesql = array();
        $i = 0;
        foreach ($fields as $field) {
            $i++;
            $likesql[] = $DB->sql_like($field, ":queryparam$i", false);
            $sqlparams["queryparam$i"] = "%$query%";
        }
        $sqlfields = implode(" OR ", $likesql);
        $concatsql = " AND ($sqlfields) ";
        if(!empty($eventid) && !empty($module)) {
            $sql = "SELECT ls.id AS id, ls.name AS fullname FROM {local_speakers} ls
            JOIN {local_event_speakers} e ON e.speaker = ls.id  WHERE 
            ls.id NOT IN(SELECT ef.itemid FROM {local_event_finance} ef
            WHERE ef.itemid = ls.id AND ef.eventid = $eventid AND ef.type = 2 AND ef.expensetype = 1)
            AND e.eventid = $eventid $concatsql";
            $list = $DB->get_records_sql($sql, $sqlparams);
        } elseif(!empty($eventid)) {
            $sql = "SELECT ls.id AS id, ls.name AS fullname FROM {local_speakers} ls
            WHERE ls.id NOT IN(SELECT es.speaker FROM {local_event_speakers} es WHERE es.speaker = ls.id AND es.eventid = $eventid) $concatsql";
            $data = $DB->get_records_sql($sql, $sqlparams);
            $otherdata[] = new \stdClass;
            $otherdata[0]->id = '0';
            $otherdata[0]->fullname = get_string('others', 'local_events');
            $list = array_merge($data,$otherdata);
        } else {
            $list = $DB->get_records_sql("SELECT ls.id AS id, ls.name AS fullname FROM {local_speakers} ls");
        }
        return $list;
    }

    public function get_sponsorlist($eventid, $module, $query = null) {
        global $DB;
        $fields = array('ls.name');
        $likesql = array();
        $i = 0;
        foreach ($fields as $field) {
            $i++;
            $likesql[] = $DB->sql_like($field, ":queryparam$i", false);
            $sqlparams["queryparam$i"] = "%$query%";
        }
        $sqlfields = implode(" OR ", $likesql);
        $concatsql = " AND ($sqlfields) ";
        if(!empty($eventid) && !empty($module)) {
            $sql = "SELECT ls.id AS id, ls.name AS fullname FROM {local_sponsors} ls
            JOIN {local_event_sponsors} es ON es.sponsor = ls.id  WHERE ls.id NOT IN(SELECT ef.itemid FROM {local_event_finance} ef
            WHERE ef.itemid = ls.id AND ef.eventid = $eventid AND ef.type = 1)
            AND es.eventid = $eventid $concatsql";
            $list = $DB->get_records_sql($sql, $sqlparams);
        } elseif(!empty($eventid)) {
            $sql = "SELECT ls.id AS id, ls.name AS fullname FROM {local_sponsors} ls 
            WHERE ls.id NOT IN(SELECT es.sponsor FROM {local_event_sponsors} es WHERE es.sponsor = ls.id AND es.eventid = $eventid) $concatsql";
            $data = $DB->get_records_sql($sql, $sqlparams);
            $otherdata[] = new \stdClass;
            $otherdata[0]->id = '0';
            $otherdata[0]->fullname = get_string('others', 'local_events');
            $list = array_merge($data,$otherdata);
        } else {
            $list = $DB->get_records_sql("SELECT id, name AS fullname FROM {local_sponsors}");
        }
        return $list;
    }

    public function get_partnerlist($eventid, $module, $valselect, $query = null) {
        global $DB;
        $fields = array('lp.name');
        $likesql = array();
        $i = 0;
        foreach ($fields as $field) {
            $i++;
            $likesql[] = $DB->sql_like($field, ":queryparam$i", false);
            $sqlparams["queryparam$i"] = "%$query%";
        }
        $sqlfields = implode(" OR ", $likesql);
        $concatsql = " AND ($sqlfields) ";
        //var_dump($module); exit;
        if(!empty($eventid) && !empty($module)) { 
            $sql = " SELECT lp.id AS id, lp.name AS fullname FROM {local_partners} lp
            JOIN {local_event_partners} ep ON ep.partner = lp.id  WHERE lp.id NOT IN(SELECT ef.itemid FROM {local_event_finance} ef
            WHERE ef.itemid = lp.id AND ef.eventid = $eventid AND ef.type = 2 AND ef.expensetype = 2)
            AND ep.eventid = $eventid $concatsql";
            $list = $DB->get_records_sql($sql, $sqlparams);
        } elseif(!empty($eventid)) {
           //var_dump($valselect); exit;
            $sql = " SELECT lp.id AS id, lp.name AS fullname FROM {local_partners} lp WHERE
            lp.id NOT IN(SELECT ep.partner FROM {local_event_partners} ep WHERE ep.partner = lp.id  AND ep.eventid = $eventid) AND lp.id <> $valselect $concatsql";
            $data = $DB->get_records_sql($sql, $sqlparams);
            $otherdata[] = new \stdClass;
            $otherdata[0]->id = '0';
            $otherdata[0]->fullname = get_string('others', 'local_events');
            $list = array_merge($data,$otherdata);
        } else {
            $list = $DB->get_records_sql("SELECT id, name AS fullname FROM {local_partners}");
        }
        return $list;
    }

    public function get_userlist($eventid, $query = null) {
        global $DB, $USER;
        $context = context_system::instance();
        $fields = array("u.firstname","u.lastname","lc.middlenameen","lc.thirdnameen","lc.middlenamearabic","lc.thirdnamearabic","lc.firstnamearabic","lc.lastnamearabic","u.idnumber","u.email");
        $likesql = array();
        $i = 0;
        foreach ($fields as $field) {
            $i++;
            $query= trim($query);
            $likesql[] = $DB->sql_like($field, ":queryparam$i", false);
            $sqlparams["queryparam$i"] = "%$query%";
        }
        $sqlfields = implode(" OR ", $likesql);
        $concatsql = " AND ($sqlfields) ";
        $fullname = (new trainingprogram)->user_fullname_case();
        $sql = " SELECT u.id, $fullname
                 FROM {user} u 
                 JOIN {local_users} AS lc ON lc.userid = u.id 
                 JOIN {role_assignments} as ra ON ra.userid = u.id 
                 JOIN {role} as r ON r.id = ra.roleid AND r.shortname = 'trainee'
                 WHERE ra.contextid =$context->id AND u.deleted = 0 AND u.suspended = 0 AND lc.deleted = 0 AND u.id NOT IN (SELECT ea.userid FROM {local_event_attendees} ea
                 WHERE ea.userid = u.id AND ea.eventid = $eventid) $concatsql ";
                 if(!is_siteadmin() && has_capability('local/organization:manage_organizationofficial',$context)) {
                    $organization = $DB->get_field('local_users','organization',array('userid'=>$USER->id));
                    if($organization){
                        $sql.= " AND lc.organization = $organization ";
                    } else {
                        $sql.= " AND ( lc.organization = 0 || lc.organization IS NULL) ";
                    }
                   
                }   

        $order = " ORDER BY u.id DESC limit 50";
        $data = $DB->get_records_sql($sql.$order, $sqlparams);
        $return = array_values(json_decode(json_encode(($data)), true));
        return $return;
    }

    public function get_managerlist($query = null) {
        global $DB;

        $lang = current_language();
        $fields = array("lc.firstnamearabic","lc.lastnamearabic","u.firstname","u.lastname","lc.middlenameen","lc.thirdnameen","lc.middlenamearabic","lc.thirdnamearabic");
        $likesql = array();
        $i = 0;
        foreach ($fields as $field) {
            $i++;
            $likesql[] = $DB->sql_like($field, ":queryparam$i", false);
            $sqlparams["queryparam$i"] = "%$query%";
        }
        $sqlfields = implode(" OR ", $likesql);
        $concatsql = " AND ($sqlfields) ";
        $fullname = (new trainingprogram)->user_fullname_case();
        $sql = " SELECT u.id,$fullname 
                  FROM {user} AS u 
                  JOIN {local_users} lc ON lc.userid = u.id 
                  JOIN {role_assignments} as ra ON ra.userid = u.id
                  JOIN {role} as r ON r.id = ra.roleid AND r.shortname = 'em' $concatsql";
        $order = " ORDER BY u.id DESC limit 50";
        $data = $DB->get_records_sql($sql.$order, $sqlparams);
        $return = array_values(json_decode(json_encode(($data)), true));
        return $return;
    }

    public function set_speakerdata($id) {
        global $DB;
        $context = context_system::instance();
        $data = $DB->get_record('local_event_speakers', ['id' => $id], '*', MUST_EXIST);
        $speaker = $DB->get_record('local_speakers', ['id' => $data->speaker], '*', MUST_EXIST);
		$row['id'] = $data->id;
        $row['eventid'] = $data->eventid;
        $row['speaker'] = $data->speaker;
        $row['speakername'] = $speaker->name;
		$row['name'] = $speaker->name;
		$row['sellingprice'] = $data->sellingprice;
		$row['actualprice'] = $data->actualprice;
        $draftitemid = file_get_submitted_draft_itemid('logo');
        file_prepare_draft_area($draftitemid, $context->id, 'local_events', 'logo', $speaker->logo, null);
        $row['logo'] = $draftitemid;
		//$row['logo'] = $speaker->logo;
		$row['specialist'] = $speaker->specialist;
        $row['biography'] = $speaker->biography;
        $row['linked_profile'] = $speaker->linked_profile;
        return $row;
    }

    public function set_sponsordata($id) {
        global $DB;
        $context = context_system::instance();
        $data = $DB->get_record('local_event_sponsors', ['id' => $id], '*', MUST_EXIST);
        $sponsor = $DB->get_record('local_sponsors', ['id' => $data->sponsor], '*', MUST_EXIST);
		$row['id'] = $data->id;
        $row['eventid'] = $data->eventid;
        $row['sponsorname'] = $sponsor->name;
        $row['sponsor'] = $data->sponsor;
		$row['name'] = $sponsor->name;
		$row['amount'] = $data->amount;
		$row['category'] = $data->category;
		//$row['logo'] = $sponsor->logo;
        $draftitemid = file_get_submitted_draft_itemid('logo');
        file_prepare_draft_area($draftitemid, $context->id, 'local_events', 'logo', $sponsor->logo, null);
        $row['logo'] = $draftitemid;
        $row['biography'] = $sponsor->biography;
        $row['website'] = $sponsor->website;
        return $row;
    }

    public function set_partnerdata($id) {
        global $DB;
        $systemcontext = context_system::instance();
        $data = $DB->get_record('local_event_partners', ['id' => $id], '*', MUST_EXIST);
        $partner = $DB->get_record('local_partners', ['id' => $data->partner], '*', MUST_EXIST);
		$row['id'] = $data->id;
        $row['eventid'] = $data->eventid;
        $row['partner'] = $data->partner;
        $row['partnername'] = $partner->name;
		$row['name'] = $partner->name;
		$row['sellingprice'] = $data->sellingprice;
		$row['actualprice'] = $data->actualprice;
        $draftitemid = file_get_submitted_draft_itemid('picture');
        file_prepare_draft_area($draftitemid, $systemcontext->id, 'local_events', 'logo', $partner->picture, null);
        $row['picture'] = $draftitemid;
	
        $draftitemid = file_get_submitted_draft_itemid('logo');
        file_prepare_draft_area($draftitemid, $systemcontext->id, 'local_events', 'logo', $partner->logo, null);
        $row['logo'] = $draftitemid;
		$row['specialist'] = $partner->specialist;
        $row['biography'] = $partner->biography;
        $row['description'] = $data->description;
        return $row;
    }

    public function speaker_formdata($id) {
        global $DB, $PAGE, $OUTPUT;
        $systemcontext = context_system::instance();
        $PAGE->set_context($systemcontext);
        $params = array('id' => $id);
        $speaker = $DB->get_record('local_event_speakers', $params);
        $data = [];
        $data['id'] = $speaker->id;
        $data['eventid'] = $speaker->eventid;
        $data['name'] = $speaker->name;
        $data['sellingprice'] = $speaker->sellingprice;
        $renderer = $PAGE->get_renderer('local_events');
        return  $data;
    }

    // Vinod -Events fake block for event manager - Starts//
    public function eventsfakeblock() {
        global $DB, $PAGE, $OUTPUT, $CFG, $USER;
        $systemcontext = context_system::instance();
        $check_event = $DB->record_exists('local_event_attendees', ['userid' => $USER->id]);
        if(!is_siteadmin() && (has_capability('local/organization:manage_trainee', $systemcontext) || has_capability('local/organization:manage_event_manager', $systemcontext))) {
            $bc = new \block_contents();
            $bc->attributes['class'] = 'events_block';
            if(has_capability('local/organization:manage_trainee', $systemcontext)) {
                if($check_event) {
                    $bc->title = '';
                    $bc->content =(new events)->trinee_events_data();
                    $PAGE->blocks->add_fake_block($bc, 'rightregion');
                }
            } else {
                $bc->title = get_string('manage', 'local_events');
                $bc->content =(new events)->manager_events_data();
                $PAGE->blocks->add_fake_block($bc, 'content');
            }    
        }
    }
    public function events_info() {
        global $DB, $PAGE, $OUTPUT,$CFG;
        require_once($CFG->dirroot . '/local/events/lib.php');
        $renderer = $PAGE->get_renderer('local_events');
        $filterparams  = $renderer->get_events(true);
        //$filterparams['filteroptions']  = $filterparams;
        $filterparams['submitid'] = 'form#filteringform';
        $filterparams['widthclass'] = 'col-md-12';
        $filterparams['placeholder'] = get_string('searchevents','local_events');
        $globalinput = $renderer->global_filter($filterparams);

        $locationinput = $renderer->global_filter($filterparams);
        $fform = events_front_filters_form($filterparams);
        $filterparams['eventsdetails'] = $renderer->get_events();
        $filterparams['filterform'] = $fform->render();
        $filterparams['globalinput'] = $globalinput;
        $filterparams['locationinput'] = $locationinput;
       // var_dump($filterparams); exit;
        $renderer->listof_events($filterparams);
    }

    public function trinee_events_data() {
        global $DB, $PAGE, $OUTPUT, $CFG, $USER;
        $systemcontext = context_system::instance();
        if(!is_siteadmin() && has_capability('local/organization:manage_trainee', $systemcontext)) {
            $eventssql="SELECT * FROM {local_events} le JOIN {local_event_attendees} ea ON le.id=ea.eventid WHERE ea.userid = $USER->id  AND ea.enrolstatus = 1
            AND date(FROM_UNIXTIME(le.enddate)) >= CURDATE()
            ORDER BY le.startdate DESC LIMIT 5";
        } else {
            $eventssql="SELECT * FROM {local_events} ORDER BY startdate DESC LIMIT 5";
        }   
        $events = $DB->get_records_sql($eventssql);
        $i=1;
        foreach ($events AS $event) {
            $eventdata= array();
            $eventdata['coid']=$i++;
            $eventdata['eventid']=$event->eventid;
            $currentlang = current_language();
            if($currentlang == 'ar') {
                $eventdata['eventname'] = $event->titlearabic;
            } else {
                $eventdata['eventname'] = $event->title;
            }
           // $eventdata['eventstartdate'] = date('l j F Y',$event->registrationstart); 
            $eventdata["eventstartdate"] =  userdate($event->startdate, get_string('strftimedaydate', 'core_langconfig'));
            $eventdata['eventstarttime'] = date('h:i A',$event->registrationstart); 
            $eventdata['eventcode'] =$event->code;
            $eventdata['eventlanguage'] =$event->language;
            $eventdata['eventtype'] =$event->type;
            $eventdata['eventsellingprice'] =$event->sellingprice;
            $eventdata['eventactualprice'] =$event->actualprice;
            if($event->status == 0) {
                $eventdata['status'] = get_string('active', 'local_events');
            }elseif ($event->status == 1) {
                $eventdata['status'] = get_string('inactive', 'local_events');
           }elseif ($event->status == 2) {
                $eventdata['status'] = get_string('cancelled', 'local_events');
           }elseif ($event->status == 2) {
                $eventdata['status'] = get_string('closed', 'local_events');
           } else{
                $eventdata['status'] = get_string('archieved', 'local_events');
           }

            $localuserrecord = $DB->get_record('local_users',['userid'=> $event->eventmanager]);
            $managerfullname = ($localuserrecord)? (($localuserrecord->lang  == 'ar') ? $localuserrecord->firstnamearabic.' '.$localuserrecord->middlenamearabic.' '.$localuserrecord->thirdnamearabic.' '.$localuserrecord->lastnamearabic  : $localuserrecord->firstname.' '.$localuserrecord->middlenameen.' '.$localuserrecord->thirdnameen.' '.$localuserrecord->lastname) :fullname($DB->get_record('user',array('id'=>(int)$event->eventmanager)));

            $eventdata['eventmanager'] =$managerfullname;
            
            if (!empty($event->logo)) {
                $eventdata['imageurl']=logo_url($event->logo);
            }
            $eventdata['viewdetailsurl']=$CFG->wwwroot.'/local/events/view.php?id='.$event->eventid;
            $eventdata['viewmoreurl']=$CFG->wwwroot.'/local/events/index.php';

            //$eventdata['location'] = $event->halladdress?$DB->get_field('hall','maplocation',array('id'=>$event->halladdress)):'';
            $hall_sql = " SELECT h.name FROM {hall} h JOIN {local_events} e ON e.halladdress = h.id
            WHERE e.id = $event->eventid ";
            $hallslist  =  $DB->get_record_sql($hall_sql);
            if($hallslist) {
                $eventdata['location'] = $hallslist->name;
            } else {
                $eventdata['location'] = '--';
            }
            $starttimemeridian = date('a',mktime(0, 0,$event->slot)); 
            $endtimemeridian = date('a',mktime(0, 0,($event->slot + $event->eventduration))); 
            $starttime = date("h:i",mktime(0, 0,$event->slot));
            $get_time_lang = self::time_lang_change($starttimemeridian, $endtimemeridian);
            $eventdata['starttime'] =  $starttime .' '.$get_time_lang['startmeridian'];
           /* if(date('i', mktime(0, 0, $event->eventduration)) == 0) {
               $eventduration = date('g', mktime(0, 0, $event->eventduration));
               $eventdata['durationstatus'] = get_string('hours', 'local_events');
            } else {
               $eventduration = date('H:i', mktime(0, 0, $event->eventduration));
               $eventdata['durationstatus'] = get_string('minutes', 'local_events');
            }*/
            //$eventdata['duration'] = $eventduration;
            $hours = date('H', mktime(0, 0, $event->eventduration));
            $minutes = date('i', mktime(0, 0, $event->eventduration));
            $hoursstatus = get_string('hours', 'local_events');
            $minutesstatus =  get_string('minutes', 'local_events');
            $eventdata["duration"] = $hours .' '. $hoursstatus .' '. $minutes .' '. $minutesstatus;
            //$eventdata['durationstatus'] = '';
            $sql = " SELECT ls.name FROM {local_event_speakers} les JOIN {local_speakers} ls ON ls.id = les.speaker
            WHERE les.eventid = $event->eventid";
            $speaker = $DB->get_record_sql($sql);
            if($speaker) {
                $eventdata['speaker'] = $speaker->name;
            } else {
                $eventdata['speaker'] = '';
            }
            $totalevents[] = $eventdata;
        }
        $viewdata=[
        'totalevents'=>$totalevents,
        'path'=>$CFG->wwwroot,
        ];
        $result = $OUTPUT->render_from_template('local_events/events_block', $viewdata);
        return $result;
    }

    public function manager_events_data() {
      global $DB, $PAGE, $OUTPUT, $CFG, $USER;
        $systemcontext = context_system::instance();
        $renderer = $PAGE->get_renderer('local_events');
        $filterparams  = $renderer->get_catalog_events(true);
        $filterparams['submitid'] = 'form#filteringform';
        $filterparams['widthclass'] = 'col-md-6';
        $filterparams['placeholder'] = get_string('searchevents','local_events');
        $globalinput = $renderer->global_filter($filterparams);
        $eventdetails = $renderer->get_catalog_events();
        $filtersinfo = $this->get_filters();
        $filterparams['eventdetails'] = $eventdetails;
        $filterparams['filtersinfo'] = $filtersinfo;
        $filterparams['globalinput'] = $globalinput;
        return $renderer->listofeventsformanager($filterparams);

   }



    // Vinod -Events fake block for event manager - Ends//

    public static function events_managers($users = array(),$eventid = 0) {
        global $DB, $USER;

        $fullname = (new trainingprogram)->user_fullname_case();
        if(!empty($users)){
            $params = array();
            list($userssql, $userseparams) = $DB->get_in_or_equal($users);
            $users = $DB->get_records_sql_menu("SELECT u.id, $fullname 
            FROM {user} u JOIN {local_users} lc ON lc.userid  = u.id WHERE  u.id $userssql AND u.id > 2",$userseparams);
        } elseif($eventid){
          $users = $DB->get_records_sql_menu(" SELECT u.id, $fullname 
          FROM {user} u JOIN {local_users} lc ON lc.userid  = u.id JOIN {local_events} e ON concat(',', e.eventmanager, ',') LIKE concat('%,',u.id,',%')
          WHERE e.id = :eventid",['eventid' => $eventid]);
        }
        return $users;
    }

    public function get_halllist($query=null) {
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
        $halllists = $DB->get_records_sql("SELECT id, name AS fullname FROM {hall} WHERE availability = 1 AND type = 3 $concatsql", $sqlparams );
        return  $halllists;
    }

    public function get_examhalls($query=null) {
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
        $halllists = $DB->get_records_sql("SELECT id, name AS fullname FROM {hall} WHERE type=1 AND availability = 1 $concatsql", $sqlparams );
        
        return  $halllists;
    }

    public static function events_halls($halls = array(),$eventid = 0) {
        global $DB, $USER;
        if(!empty($halls)){
            $params = array();
            list($hallssql, $hallparams) = $DB->get_in_or_equal($halls);
            $halls_list = $DB->get_records_sql_menu("SELECT id, name AS fullname FROM {hall} WHERE id $hallssql ",$hallparams);
        } elseif($eventid){
           $halls_list = $DB->get_records_sql_menu(" SELECT h.id, h.name AS fullname FROM {hall} h 
           JOIN {local_events} e ON e.halladdress = h.id
           WHERE e.id = :eventid",['eventid' => $eventid]);
        }
        return $halls_list;
    }

    public function financeinfo($eventid) {
        global $DB, $PAGE, $OUTPUT;
        $systemcontext = context_system::instance();
        $renderer = $PAGE->get_renderer('local_events');
        //$filterparams  = $renderer->get_catalog_financeinfo(true);
        $total_estimated = $renderer->get_total_estimated($eventid);
        $actual_revenue = $renderer->get_actual_revenue($eventid);
        $filterparams['total_estimated'] = $total_estimated;
        $filterparams['actual_revenue'] = $actual_revenue;
        $total_expenses = $renderer->get_total_expenses($eventid);
        $filterparams['total_expenses'] = $total_expenses;
        $filterparams['eventid'] = $eventid;
        $renderer->listoffinance($filterparams);

    }

    public function create_finance($data) {
        global $DB,$CFG,$USER;
        $systemcontext = context_system::instance();  
        if (isset($data->billingfile)) {
			$data->billingfile = $data->billingfile;
			file_save_draft_area_files($data->billingfile, $systemcontext->id, 'local_events', 'logo', $data->billingfile);
		}
        if ($data->sponsorid) {
            $data->itemid = $data->sponsorid;
        }
        if ($data->speakerid) {
            $data->itemid = $data->speakerid;
        }
        if ($data->partnerid) {
            $data->itemid  = $data->partnerid;
        }
		if ($data->id) {
            $data->timemodified = time();
			$DB->update_record('local_event_finance', $data);
              
            // notification events  onchange
            $attendees = $DB->get_records('local_event_attendees', ['eventid' => $data->eventid]);
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
                $thispageurl = new \moodle_url('/local/events/view.php?id='.$data->eventid);            
                $row1=[];
                $row1['RelatedModuleName']=$DB->get_field('local_events','title',array('id'=>$data->eventid));;
                $row1['RelatedModulesLink']=$thispageurl;
                $localuserrecord = $DB->get_record('local_users',['userid'=> $attendee->userid]);
                $fname = ($localuserrecord)? (($localuserrecord->lang  == 'ar') ? $localuserrecord->firstnamearabic.' '.$localuserrecord->middlenamearabic.' '.$localuserrecord->thirdnamearabic.' '.$localuserrecord->lastnamearabic  : $localuserrecord->firstname.' '.$localuserrecord->middlenameen.' '.$localuserrecord->thirdnameen.' '.$localuserrecord->lastname) :fullname($touser);
                $row1['FullName']=$fname;                            
                $myobject=(new \local_events\notification);
                $myobject->event_notification('events_onchange',$touser, $USER,$row1,$waitinglistid=0);
              
            }
			return $data->id;
		} else {
            $data->timecreated = time();
			 $id=$DB->insert_record('local_event_finance', $data);
             // notification events  onchange               
             $attendees = $DB->get_records('local_event_attendees', ['eventid' => $data->eventid]);          
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
                $thispageurl = new \moodle_url('/local/events/view.php?id='.$data->eventid);            
                $row1=[];
                $row1['RelatedModuleName']=$DB->get_field('local_events','title',array('id'=>$data->eventid));
                $row1['RelatedModulesLink']=$thispageurl;
                $localuserrecord = $DB->get_record('local_users',['userid'=> $attendee->userid]);
                $fname = ($localuserrecord)? (($localuserrecord->lang  == 'ar') ? $localuserrecord->firstnamearabic.' '.$localuserrecord->middlenamearabic.' '.$localuserrecord->thirdnamearabic.' '.$localuserrecord->lastnamearabic  : $localuserrecord->firstname.' '.$localuserrecord->middlenameen.' '.$localuserrecord->thirdnameen.' '.$localuserrecord->lastname) :fullname($touser);
                    $row1['FullName']=$fname;                            
                $myobject=(new \local_events\notification);
                $myobject->event_notification('events_onchange',$touser, $USER,$row1,$waitinglistid=0);              
            }
             return $id;
		}
    }

    public function get_listof_income($stable, $filtervalues) {
        global $DB, $PAGE, $OUTPUT, $USER;
        $systemcontext = context_system::instance();
        $searchparams = [];
        $selectsql = "SELECT ef.*, e.title, e.logisticsestimatedbudget ";
        $countsql = "SELECT count(ef.id) ";
        $formsql = " FROM {local_event_finance} ef ";
        $formsql .= " JOIN {local_events} e ON e.id = ef.eventid ";
        $formsql .= " WHERE ef.eventid = $stable->eventid";
        if($stable->class == 'income') {
            $formsql .= " AND ef.type = 1";
        } else {
            $formsql .= " AND ef.type = 2";
        }
        $params = array_merge($searchparams);
        $totalcount = $DB->count_records_sql($countsql.$formsql, $params);
        $formsql .=" ORDER BY ef.id DESC";
        $eventlist = $DB->get_records_sql($selectsql.$formsql, $params, $stable->start,$stable->length);
        $incomelist = [];
        $expenseslist = [];
        $total_estimated = [];
        if ($eventlist) {
            foreach($eventlist as $list) {
                $eventdata = array(); 
                if($list->type == 1) {
                    $eventdata['type'] = get_string('income', 'local_events');
                } else {
                    $eventdata['type'] = get_string('expenses', 'local_events');
                }
                $eventdata['id'] = $list->id;
                $eventdata['itemname'] = $list->itemname;
                $eventdata['amount'] = $list->amount;
                $incomelist[] = $eventdata;
            }
            $norecords = true;
        } else {
            $norecords = false;
        }
        $coursesContext = array(
            "incomelist" => $incomelist,
            "norecords" => $norecords,
            "totalcount" => $totalcount,
            "length" => $totalcount,
            "manageactions" => false,
            "total_estimated" => $total_estimated,
        );
        return $coursesContext;
    }

    public static function ajax_datalist($partner = array(),$eventid = 0, $tblname) {
        global $DB;
        if(!empty($partner)){
            $params = array();
            if($partner['0'] == '0') {
                $partners = [0 => get_string('others', 'local_events')];
            } else {
                list($partnersql, $partnerparams) = $DB->get_in_or_equal($partner);
                $partners = $DB->get_records_sql_menu("SELECT id, name AS fullname FROM {$tblname} WHERE id $partnersql",$partnerparams);
            }
        }
        return $partners;
    }

    public static function get_agenda_dates($eventid) {
        global $DB;
        $event = $DB->get_record_sql(" SELECT e.slot,e.startdate, e.enddate FROM {local_events} e WHERE e.id = $eventid");
        $start_date = date('Y-m-d',$event->startdate);
        $end_date = date('Y-m-d',$event->enddate);
        $duration = $event->enddate - $event->startdate;
        $days_between = ceil(abs($duration) / 86400);
        $count = $days_between + 1;
        $dates = array();
        for($i = 0; $i <= $days_between; $i++)
        {
           //date("Y-m-d",strtotime($start_date.'+'.$i.' days'));
            $dates[] = userdate(strtotime($start_date.'+'.$i.' days'),'%Y-%m-%d');
        }
        return $dates;
    }

    public static function get_event_reg_dates($eventid) {
        global $DB;
        $event = $DB->get_record_sql(" SELECT e.slot,e.registrationstart, e.registrationend FROM {local_events} e WHERE e.id = $eventid");
        $start_date = userdate($event->registrationstart, '%Y-%m-%d');
        $end_date = userdate($event->registrationend, '%Y-%m-%d');
        $duration = $event->registrationend - $event->registrationstart;
        $days_between = floor(abs($duration) / 86400);
        $count = $days_between + 1;
        $dates = array();
        for($i = 0; $i <= $count; $i++)
        {
            $dates[] = date("Y-m-d",strtotime($start_date.'+'.$i.' days'));
        }
        $today = date('Y-m-d');
        $reg_enddate = userdate($event->registrationend, '%Y-%m-%d');
        $reg_endtime = userdate($event->registrationend, '%H:%M');
        $current_time = userdate(time(), '%H:%M');
        if(in_array($today, $dates)) {
            if($reg_enddate == $today && $reg_endtime < $current_time) {
                $event_reg_status = false;
            } else if ($event->registrationstart <= time() && $event->registrationend >= $event->registrationstart) {
                $event_reg_status = true;
            } else {
                $event_reg_status = false;
            }
        } else {
            $event_reg_status = false;
        }
        return $event_reg_status;
    }

    public function eventdefaultimage_url() {
        global $CFG;
        $url= $CFG->wwwroot.'/local/events/pix/eventdefaultimage.png';
        return $url;
    }

    public function get_itemamount($type, $eventid, $itemid) {
        global $DB;
        if ($type  == 'sponsorlist') {
            $amount = $DB->get_field('local_event_sponsors', 'amount',['eventid' => $eventid, 'sponsor' => $itemid]);
        } else if ($type == 'speakerlist') {
            $amount = $DB->get_field('local_event_speakers', 'actualprice',['eventid' => $eventid, 'speaker' => $itemid]);
        } else if ($type == 'partnerlist') {
            $amount = $DB->get_field('local_event_partners', 'actualprice',['eventid' => $eventid, 'partner' => $itemid]);
        }
        if($amount) {
            return $amount;
        } else {
            return 0;
        }
    }

    public function agenda_speakerlist($eventid) {
        global $DB;
        $sql = "SELECT es.id AS id, es.name AS fullname FROM {local_speakers} es
        JOIN {local_event_speakers} e ON e.speaker = es.id  WHERE e.eventid = $eventid";
        $data = $DB->get_records_sql($sql);
        $otherdata[] = new \stdClass;
        $otherdata[0]->id = '0';
        $otherdata[0]->fullname = get_string('others', 'local_events');
        $list = array_merge($data,$otherdata);
        return $list;
    }
    public function event_completion($lastruntime,$nextruntime,$timenow) {
        global $DB;    
        $eventenddday=$DB->get_records('local_events');
        global $DB;
        $sql = "SELECT e.* FROM {local_events} AS e WHERE FROM_UNIXTIME((e.enddate + e.slot + e.eventduration)) < NOW() ";
        if($lastruntime > 0) {
            $sql .= " AND FROM_UNIXTIME((e.enddate + e.slot + e.eventduration)) >= $lastruntime ";
        }
        // var_dump($eventslist); exit;
        $eventslist = $DB->get_records_sql($sql);
        if(!empty($eventslist)) {
            foreach($eventslist as $list) {
                $list->status = 3;
                $DB->update_record('local_events',$list);
                $eventattendes=$DB->get_records('local_event_attendees',array('eventid'=>$list->id));
                foreach($eventattendes as $eventattende){
                    $eventattende->event_name=$DB->get_field('local_events','title',array('id'=>$eventattende->eventid));
                    $eventattende->event_userfullname=$eventattende->name;
                    if($eventattende->userid==0){
                        $eventattendelist=get_admin();
                        $eventattendelist->firstname=$eventattende->name;
                        $eventattendelist->lastname=$eventattende->name;
                        $eventattendelist->email=$eventattende->email;
                    } else {
                        $eventattendelist=$DB->get_record('user',array('id'=>$eventattende->userid));
                        if ($eventattendelist->lang == 'ar'){
                            $eventattende->event_name=$DB->get_field('local_events','titlearabic',array('id'=>$eventattende->eventid));

                        } else{
                            $eventattende->event_name=$DB->get_field('local_events','title',array('id'=>$eventattende->eventid));

                        }

                     $localuserrecord = $DB->get_record('local_users',['userid'=> $eventattende->userid]);
                      $eventattende->event_name = ($localuserrecord)? (($localuserrecord->lang  == 'ar') ? $localuserrecord->firstnamearabic.' '.$localuserrecord->middlenamearabic.' '.$localuserrecord->thirdnamearabic.' '.$localuserrecord->lastnamearabic  : $localuserrecord->firstname.' '.$localuserrecord->middlenameen.' '.$localuserrecord->thirdnameen.' '.$localuserrecord->lastname) :fullname($eventattendelist);
                    }
                    (new \local_events\notification())->event_notification('events_completion', $touser=$eventattendelist,$fromuser=get_admin(),$eventattende,$waitinglistid=0);  
                }
            }
        } 
        foreach($eventenddday as $eventenddays){
            if(date('d/m/Y', $eventenddays->enddate) == date('d/m/Y')){ 
                $eventattendes=$DB->get_records('local_event_attendees',array('eventid'=>$eventenddays->id));
                foreach($eventattendes as $eventattende){
                    
                    if($eventattende->userid==0){
                        $eventattendelist=get_admin();
                        $eventattendelist->firstname=$eventattende->name;
                        $eventattendelist->lastname=$eventattende->name;
                        $eventattendelist->email=$eventattende->email;
                        $eventattende->event_name=$DB->get_field('local_events','title',array('id'=>$eventattende->eventid));
                        $eventattende->event_userfullname=$eventattende->name;
                    }
                    else{
                        $eventattendelist = $DB->get_record('user',array('id'=>$eventattende->userid));
                        if ($eventattendelist->lang == 'ar'){

                            $eventattende->event_name=$DB->get_field('local_events','titlearabic',array('id'=>$eventattende->eventid));

                        } else{
                            $eventattende->event_name=$DB->get_field('local_events','title',array('id'=>$eventattende->eventid));

                        }

                      $localuserrecord = $DB->get_record('local_users',['userid'=> $eventattende->userid]);
                      $eventattende->event_name = ($localuserrecord)? (($localuserrecord->lang  == 'ar') ? $localuserrecord->firstnamearabic.' '.$localuserrecord->middlenamearabic.' '.$localuserrecord->thirdnamearabic.' '.$localuserrecord->lastnamearabic  : $localuserrecord->firstname.' '.$localuserrecord->middlenameen.' '.$localuserrecord->thirdnameen.' '.$localuserrecord->lastname) :fullname($eventattendelist);
              
                    }
                    (new \local_events\notification())->event_notification('events_completion', $touser=$eventattendelist,$fromuser=get_admin(),$eventattende,$waitinglistid=0);  
                }

            }

        }
	}

    public function create_zoom($data) {
        global $DB;
        $zoom = new stdClass();
        $zoom->modulename = 'zoom';
        $starttime =  $data->startdate + $data->slot;
        $zoom->host_id = zoom_get_user_id();
        $zoom->name = $data->title;
        $zoom->showdescription = 0;
        $zoom->start_time = $starttime;
        $zoom->duration = $data->eventduration;
        //$zoom->recurring = 0;
        $zoom->intro = $data->description;
        $zoom->introeditor = ['text' => '', 'format' => FORMAT_HTML, 'itemid' => null];
        $zoom->introformat = 1;
        $zoom->grade = 0;
        $zoom->timezone = date_default_timezone_get();
        $zoom->course = SITEID;
        $zoom->section = 1;
       // $zoom->option_jbh = 1;
       // $zoom->option_waiting_room = false;
       // $zoom->monthly_repeat_option = true;
        //$zoom->requirepasscode = 1;
        if($data->enddate > $data->startdate) {
            $zoom->recurring = 1;
            $zoom->recurrence_type = 1;
            $zoom->repeat_interval = 1;
        }
        $zoom->visible = 1;
        $zoom->availability = null;
        $zoom->option_jbh = 0;
        $zoom->end_times = 1;
        $zoom->end_date_time = $data->enddate;
        $zoom->end_date_option = 1;
        $zoom->option_mute_upon_entry = 1;
        $zoom->option_waiting_room = 1;
        $zoom->requirepasscode = 1;
        $zoom->monthly_repeat_option = null;
       // $zoomid = zoom_add_instance($zoom);
       $moduleinfo =  create_module($zoom);

       $zoomrecord = $DB->get_record('zoom', array('id' => $moduleinfo->instance), '*', MUST_EXIST);
       $zoomid = $moduleinfo->instance;
        return $zoomid; 
    }
    public function update_zoom($data) {
        global $DB;
        $zoom = $DB->get_record('zoom', ['id' => $data->zoom]);
        $starttime =  $data->startdate + $data->slot;
        $zoom->instance = $data->zoom;
        $zoom->name = $data->title;
        $zoom->start_time = $starttime;
        $zoom->duration = $data->eventduration;
        $zoom->end_date_time = $data->enddate;
        $zoom->intro = $data->description;
        $zoom->introeditor = ['text' => '', 'format' => FORMAT_HTML, 'itemid' => null];
        $zoom->timezone = date_default_timezone_get();
        $zoom->recurring = 1;
        $zoom->recurrence_type = 1;
        $zoom->repeat_interval = 1;
        $zoom->end_date_option = 1;
        $zoom->option_mute_upon_entry = 1;
        $zoom->option_waiting_room = 1;
        $zoom->requirepasscode = 1;
        $zoom->monthly_repeat_option = null;
        $zoom->availability = json_encode($this->json);
        $zoomid = zoom_update_instance($zoom);
        return $zoomid; 
    }

    public function create_webex($data) {
       global $DB, $USER;
       $section = course_create_section(1);
       $sectionid = $section->section;
       $starttime =  $data->startdate + $data->slot;
       $webex = new stdClass();
       $webex->modulename = 'webexactivity';
       $webex->course = 1;
       $webex->name =  $data->title;
       $webex->intro = $data->description;
       $webex->introformat = 1;
       $webex->type = 1;
       $webex->starttime = $starttime;
       $webex->introeditor = ['text' => '', 'format' => FORMAT_HTML, 'itemid' => null];
       $webex->section = 0;
       $webex->endtime = $data->enddate;
       $webex->duration =  $data->eventduration/60;
       $webex->calpublish = 1;
       $webex->visible = 1;
       $webex->allchat = 1;
       $webex->studentdownload = 1;
       $webex->laststatuscheck  = 1;
       $webex->availability = '';
       $moduleinfo = create_module($webex);
       $webexrecord = $DB->get_record('webexactivity', array('id' => $moduleinfo->instance), '*', MUST_EXIST);
       $webexmeeting = \mod_webexactivity\meeting::load($webexrecord);
       $webexrecord->coursemodule = $moduleinfo->coursemodule;
       $webexrecord->meetinglink = $webexmeeting->get_moodle_join_url($USER, null);
       $webexid = webexactivity_update_instance($webexrecord);
       $webexmeeting->get_external_join_url();
       return $webexrecord->id;
    }

    public function update_webex($data) {
        global $DB, $USER;
        $webex = $DB->get_record('webexactivity', ['id' => $data->webex]);
        $starttime =  $data->startdate + $data->slot;
        $webex->course = 1;
        $webex->modulename = 'webexactivity';
        $webex->name = $data->title;
        $webex->intro = $data->description;
        $webex->introformat = 1;
        $webex->starttime = $starttime;
       // var_dump($starttime); 
        //var_dump($webex->starttime); exit;
        $webex->endtime = $data->enddate;
        $webex->duration =  $data->eventduration/60;
        $coursemoduleid =  get_coursemodule_from_instance('webexactivity', $webex->id, 1);
        $webex->coursemodule = $coursemoduleid->id;
        $webexid = webexactivity_update_instance($webex);
        return $webexid;
    }

    // public function events_available_seats($eventid) {
    //     global $DB, $USER;
    //     $systemcontext = context_system::instance();
    //     $event = $DB->get_record('local_events', ['id' => $eventid]);
    //     $hall_sql = " SELECT SUM(hr.seats) FROM {hall} h JOIN {local_events} e ON concat(',', e.halladdress, ',') 
    //     LIKE concat('%,',h.id,',%')
    //     JOIN {hall_reservations} hr ON hr.hallid = h.id
    //     WHERE e.id = $eventid AND hr.type='event' AND hr.typeid = e.id" ;
    //     $seats_available = $DB->get_field_sql($hall_sql);

    //     $enrolledseatsselectsql = "SELECT COUNT(DISTINCT ee.id) as enrolled 
    //     FROM {local_event_attendees} ee 
    //     JOIN {hall_reservations} hr ON ee.eventid = hr.typeid 
    //     WHERE ee.eventid = {$eventid} ";
    //     $enrolled = $DB->get_record_sql($enrolledseatsselectsql);
    //     if($event->sellingprice > 0){
        
    //         if($event->method == 0) {
    //             $availableseats = $seats_available - $enrolled->enrolled; 
    //             $offeringvailableseats = $DB->get_field_sql("SELECT SUM(purchasedseats) FROM {tool_org_order_seats} WHERE tablename = 'local_events' AND fieldname = 'id' AND fieldid = $event->id");
    //             $bookingseats=$offeringvailableseats ? ($seats_available - $offeringvailableseats) : $availableseats ;
               
    //             $eventseats = (new \tool_product\product)->get_button_order_seats($label=get_string('bookseats','local_events'),'local_events','id',$event->id, $bookingseats);
    //         } else {
    //             $eventseats = (new \tool_product\product)->get_button_order_seats($label=get_string('bookseats','local_events'),'local_events','id',$event->id, 1); 
    //         }   
         
    //         if(is_siteadmin() || has_capability('local/organization:manage_event_manager', $systemcontext)) {
    //             $purchasedseats = (new \tool_product\product)->purchasedseats_check('local_events','id',$eventid);
    //             $approvedseats = (new \tool_product\product)->approvedseats_check('local_events','id',$eventid);
    //             $availableseats = (new \tool_product\product)->availableseats_check('local_events','id',$eventid);
    //             if ($purchasedseats == 0) {
    //                 $purchasedseats = 0;
    //                 $availableseats = $seats_available - $enrolled->enrolled;
    //             } else {
    //                 //$purchasedseats = $availableseats;
    //                 $availableseats = $seats_available - $purchasedseats - $enrolled->enrolled;
    //             }
    //         } else if(!is_siteadmin() && has_capability('local/organization:manage_organizationofficial', $systemcontext)) {
    //             $purchasedseats = (new \tool_product\product)->purchasedseats_check('local_events','id',$eventid);
    //             $approvedseats = (new \tool_product\product)->approvedseats_check('local_events','id',$eventid);
    //             $availableseats = (new \tool_product\product)->availableseats_check('local_events','id',$eventid);
    //             if ($purchasedseats == 0) {
    //                 $purchasedseats = 0;
    //                 $availableseats = $seats_available - $enrolled->enrolled;
    //             } else {
    //                 $purchasedseats = $approvedseats;
    //                 $enrolledseats = self::entity_enrolled($eventid, $USER->id);
    //                 $availableseats =  $approvedseats - $enrolledseats;
    //                 //$availableseats = $seats_available - $availableseats - $enrolled->enrolled;
    //             }
    //         } else {
    //             $enrolledseats =  self::entity_enrolled($eventid);
    //             $availableseats = $seats_available - $enrolledseats; 
    //             //var_dump($enrolledseats); exit;
    //         }
    //     } else {
    //         $trainingurl = new moodle_url('/local/events/attendees.php',array('id'=>$event->id));
    //         $eventseats = html_writer::link($trainingurl,get_string('enrollbtn',  'tool_product'));
    //         $availableseats = $seats_available - $enrolled->enrolled; 
    //     }
    //     return ['purchasedseats' => $purchasedseats, 
    //             'approvedseats' => $approvedseats, 
    //             'availableseats' => $availableseats,
    //             'eventseats' => $eventseats,
    //             'enrolled' => $enrolled->enrolled,
    //             'totalseats' => $seats_available
    //         ];
    // }


    public function events_available_seats($eventid) {
        global $DB, $USER;
        $systemcontext = context_system::instance();
        $purchasedseats = 0;
        $approvedseats = 0;
        $event = $DB->get_record('local_events', ['id' => $eventid]);
        $hall_sql = " SELECT SUM(hr.seats) FROM {hall} h JOIN {local_events} e ON concat(',', e.halladdress, ',') 
        LIKE concat('%,',h.id,',%')
        JOIN {hall_reservations} hr ON hr.hallid = h.id
        WHERE e.id = $eventid AND hr.type='event' AND hr.typeid = e.id" ;
        $seats_available = $DB->get_field_sql($hall_sql);

        if($event->price > 0){
            if($event->method == 0) {
                $enrolledseatsselectsql = "SELECT COUNT(DISTINCT ee.id) 
                    FROM {local_event_attendees} ee 
                    WHERE ee.eventid = {$eventid} AND ee.enrolstatus = 1";
                $enrolledseats = $DB->count_records_sql($enrolledseatsselectsql);
                $availableseats = $seats_available - $enrolledseats; 

                $offeringvailableseats = $DB->get_field_sql("SELECT SUM(approvalseats) FROM {tool_org_order_seats} WHERE tablename = 'local_events' AND fieldname = 'id' AND fieldid = $event->id");
                $bookingseats=$offeringvailableseats ? ($seats_available - $offeringvailableseats) : $availableseats ;
               
                $eventseats = (new \tool_product\product)->get_button_order_seats($label=get_string('bookseats','local_events'),'local_events','id',$event->id, $bookingseats);
            } else {
                $eventseats = (new \tool_product\product)->get_button_order_seats($label=get_string('bookseats','local_events'),'local_events','id',$event->id, 1); 
            }  
         
           /* if(is_siteadmin() || has_capability('local/organization:manage_event_manager', $systemcontext)) {
                    
                    $orgofficialroleid = $DB->get_field('role','id',['shortname'=>'organizationofficial']);

                    $enrolledseatsselectsql = " SELECT COUNT(DISTINCT ee.id) 
                    FROM {local_event_attendees} ee 
                    WHERE ee.eventid = {$eventid} AND ee.usercreated NOT IN (SELECT userid FROM {role_assignments} WHERE roleid = $orgofficialroleid AND contextid = $systemcontext->id) ";
                    $enrolledseats = $DB->count_records_sql($enrolledseatsselectsql);

                    $purchasedseats = $DB->get_field_sql(" SELECT SUM(approvalseats) 
                             FROM {tool_org_order_seats} 
                            WHERE tablename = 'local_events' AND fieldname = 'id' AND fieldid = $eventid") ;

                    if ($purchasedseats == 0) {
                        $purchasedseats = 0;
                        $availableseats = $seats_available - $enrolledseats;
                    } else {
                        $purchasedseats = $purchasedseats;
                        $availableseats = $seats_available - ($purchasedseats + $enrolledseats);
                    }

            } elseif(!is_siteadmin() && has_capability('local/organization:manage_organizationofficial', $systemcontext)) {


                $enrolledseatsselectsql = " SELECT COUNT(DISTINCT ee.id) 
                FROM {local_event_attendees} ee 
                WHERE ee.eventid = {$eventid} AND ee.usercreated  = $USER->id ";
                $enrolledseats = $DB->count_records_sql($enrolledseatsselectsql);

                $purchasedseats = $DB->get_field_sql(" SELECT SUM(approvalseats) 
                         FROM {tool_org_order_seats} 
                        WHERE tablename = 'local_events' AND fieldname = 'id' AND fieldid = $eventid AND orguserid = $USER->id") ;

                if ($purchasedseats == 0) {
                    $purchasedseats = 0;
                    // $availableseats = $purchasedseats - $enrolledseats;
                } else {
                    $purchasedseats = $purchasedseats;
                    // $availableseats =  $purchasedseats - $enrolledseats;
                    //$availableseats = $seats_available - $availableseats - $enrolled->enrolled;
                }
                    $availableseats = $seats_available - $enrolledseats;
                
            } else {
               $enrolledseatsselectsql = " SELECT COUNT(DISTINCT ee.id) 
                    FROM {local_event_attendees} ee 
                    WHERE ee.eventid = {$eventid} ";
                    $enrolledseats = $DB->count_records_sql($enrolledseatsselectsql);
               $availableseats = $seats_available - $enrolledseats; 
                //var_dump($enrolledseats); exit;
            }*/
        } else {
            $trainingurl = new moodle_url('/local/events/attendees.php',array('id'=>$event->id));
            $enrolledseatsselectsql = "SELECT COUNT(DISTINCT ee.id) 
                    FROM {local_event_attendees} ee 
                    WHERE ee.eventid = {$eventid} AND ee.enrolstatus = 1";
                    $enrolledseats = $DB->count_records_sql($enrolledseatsselectsql);
            $eventseats = html_writer::link($trainingurl,get_string('enrollbtn',  'tool_product'));
            $availableseats = $seats_available - $enrolledseats; 
        }
        return ['purchasedseats' => $purchasedseats, 
                'approvedseats' => $approvedseats, 
                'availableseats' => $availableseats,
                'eventseats' => $eventseats,
                'enrolled' => $enrolledseats,
                'totalseats' => $seats_available
            ];
    }

    public function entity_enrolled($eventid, $userid=false)
    {
        global $DB;
        $enrolledseatsselectsql = "SELECT COUNT(DISTINCT ee.id) as enrolled 
        FROM {local_event_attendees} ee 
        JOIN {hall_reservations} hr ON ee.eventid = hr.typeid 
        WHERE ee.eventid = {$eventid} ";
         if( $userid ) {
            $enrolledseatsselectsql .= " AND ee.usercreated = {$userid} ";
        }
        return $DB->get_field_sql($enrolledseatsselectsql);

    }

    public static function agenda_datalist($speaker = array(),$id = 0) {
        global $DB;
        if(!empty($speaker)){
            if($speaker['0'] == '0') {
                $speaker = [0 => get_string('others', 'local_events')];
            } else {
                list($speakersql, $speakerparams) = $DB->get_in_or_equal($speaker);
                $speaker = $DB->get_records_sql_menu("SELECT id, name AS fullname FROM {local_speakers} WHERE id $speakersql ",$speakerparams);
            }
        } elseif($id){
           $speaker = $DB->get_records_sql_menu(" SELECT s.id, s.name AS fullname FROM {local_speakers} s
           JOIN {local_eventagenda} a ON concat(',', a.speaker, ',') LIKE concat('%,',s.id,',%')
           WHERE a.id = :agendaid",['agendaid' => $id]);
        }
        return $speaker;
    }

    public function is_enrolled($eventid, $userid) {
        global $DB;
        $sql = 'SELECT ea.id FROM {local_event_attendees} as ea
                  JOIN {local_events} as e ON ea.eventid=e.id 
                   AND date(FROM_UNIXTIME(e.enddate)) > CURDATE()
                WHERE ea.eventid =:eventid AND ea.userid=:userid';
        $enrolled = $DB->record_exists_sql($sql, ['eventid' => $eventid, 'userid' => $userid]);
        if($enrolled){
            return true;
        }
        return false;
    }

    public function get_erolled_seats($eventid, $userid) {
        global $DB;
        $systemcontext = context_system::instance();
        $enrolledsql = " SELECT count(ea.userid) AS total
        FROM {local_event_attendees} AS ea
        WHERE ea.eventid = $eventid AND enrolstatus=1 ";
        if( $userid ) {
          //  $enrolledsql .= " AND ea.usercreated = $userid ";
            if(!is_siteadmin() && has_capability('local/organization:manage_organizationofficial',$systemcontext)){
                $get_all_orgofficials = (new \local_exams\local\exams())->get_all_orgofficials($userid);
                $orgusers = implode(',',$get_all_orgofficials);
                $enrolledsql .= " AND ea.usercreated IN($orgusers) ";
            } else {
                $enrolledsql .= " AND ea.usercreated = $userid ";
            }
        }
        $enrolledseats = $DB->count_records_sql($enrolledsql);
        return $enrolledseats;
    }

    public static function time_lang_change($timefrom, $timeto) {
        $currentlang = current_language();
        if($currentlang == 'ar') {
            $startmeridian = ($timefrom == 'am')? 'صباحا':'مساءً';
            $endmeridian =  ($timeto == 'am')? 'صباحا':'مساءً';
        } else {
            $startmeridian = ($timefrom == 'am')? 'AM':'PM';
            $endmeridian =  ($timeto == 'am')? 'AM':'PM';
        }
        return['startmeridian' => $startmeridian, 'endmeridian' => $endmeridian ];
    }

    public function event_agendainfo($eventid) {
        global $DB;
        $agenda_sql = " SELECT a.*  FROM {local_eventagenda} a  WHERE a.eventid = $eventid ORDER BY a.id ASC"; 
        $agendalist =  $DB->get_records_sql($agenda_sql);
        $agendadata = [];
        if (!empty($agendalist)) {
            $count = 0;
            foreach($agendalist as $list) {
                $days_between = events::get_agenda_dates($eventid);
                $aday = strtotime($days_between[$list->day]);
                $agendaday = userdate($aday, get_string('strftimedatemonthabbr', 'core_langconfig'));
                $agendatopic =  $list->title;
                $starttime = strtotime($list->timefrom);
                $endtime =  strtotime($list->timeto);
                // $timefrom =  userdate($starttime, get_string('strftimetime12', 'core_langconfig'));
                 //$timeto =  userdate($endtime, get_string('strftimetime12', 'core_langconfig'));
                $timefrom = date('h:i', $starttime);
                $timeto = date("h:i",$endtime);
                $get_time_lang = (new events())->time_lang_change( date('a',$starttime), date('a',$endtime));
                $agendatime = $timefrom.' '.$get_time_lang['startmeridian'] .' - ' .$timeto.' '.$get_time_lang['endmeridian'];
                if ($list->speaker == 0) {
                    $agendaspeaker = get_string('others', 'local_events');
                } else {
                    $agendaspeaker = $DB->get_field_select('local_speakers', 'name', " CONCAT(',',$list->speaker,',') LIKE CONCAT('%,',id,',%') ", array());//FIND_IN_SET(id, '$sdata->department')
                }
                $agendano =  $count + 1;
                $agenda_description = format_text($list->description, FORMAT_HTML);
                $agenda_isdescription = '';
                if (empty($agenda_description)) {
                    $agenda_isdescription = false;
                } else {
                    $agenda_isdescription = true;
                    if (strlen($agenda_description) > 100) {
                        $agenda_decsriptionCut = mb_substr(strip_tags($agenda_description), 0, 200);
                        $agenda_descriptionstring = $agenda_decsriptionCut;
                    } else {
                        $agenda_descriptionstring = "";
                    }
                }
                $count++;
                $agendadata[] = array('agendatopic' =>  $agendatopic, 'agendatime' => $agendatime,
                'agendaspeaker' => $agendaspeaker, 'agendano' => $agendano, 'agenda_description' =>  $agenda_description, 'agenda_isdescription' => $agenda_isdescription,
                'agenda_descriptionstring' => $agenda_descriptionstring,
                'agendaday' => $agendaday
                );
            }
        }
        return $agendadata;
       
    }
    public function event_sponsorsinfo($eventid, $category) {
        global $DB;
        $sponsor_sql = " SELECT es.id, ls.name, ls.logo  FROM {local_event_sponsors} es  
        JOIN {local_sponsors} ls ON ls.id = es.sponsor WHERE es.eventid = $eventid AND es.category = $category ORDER BY es.id DESC"; 
        $sponsorlist =  $DB->get_records_sql($sponsor_sql);
        $sponsordata = [];
        if ($sponsorlist) {
            $array = array_column($sponsorlist, 'name');
            $sponsorname = implode(', ', $array);
            foreach($sponsorlist as $list) {
                if ($list->logo > 0) {
                    $logoimg = logo_url($list->logo);
                    if(!$logoimg){
                        continue;
                    }
                } else {
                    $logoimg = '';
                }
                $sponsordata[] = array('sponsorid' => $list->id,'sponsorlogo' => $logoimg,'sponsorname' => $list->name);
            }
        }
        return $sponsordata;
    }

    public function event_speakersinfo($eventid) {
        global $DB;
        $speaker_sql = " SELECT es.id, ls.logo, ls.name, ls.biography  FROM {local_event_speakers} es  
        JOIN {local_speakers} ls ON ls.id = es.speaker WHERE es.eventid = $eventid ORDER BY es.id DESC"; 
        $speakerlist =  $DB->get_records_sql($speaker_sql);
        $speakerdata = [];
        if ($speakerlist) {
            foreach($speakerlist as $list) {
                $speakerdata[] = array('name' => $list->name, 'description' => $list->biography, 'value' => $list->id,);
            }
        }
        return $speakerdata;
    }

    public function event_partnersinfo($eventid) {
        global $DB;
        $partner_sql = " SELECT ep.id, lp.logo, lp.name  FROM {local_event_partners} ep  
        JOIN {local_partners} lp ON lp.id = ep.partner WHERE ep.eventid = $eventid ORDER BY ep.id DESC"; 
        $partnerlist =  $DB->get_records_sql($partner_sql);
        $partnerdata = [];
        if ($partnerlist) {
            foreach($partnerlist as $list) {
                if ($list->logo > 0) {
                    $logoimg = logo_url($list->logo);
                    if(!$logoimg){
                        continue;
                    }
                } else {
                    $logoimg = '';
                }
                $partnerdata[] = array('partnerid' => $list->id,  'partnerlogo' => $logoimg, 'partnername' => $list->name);
            }
        }
        return $partnerdata;
    }
    public function event_hallinfo($eventid) {
        global $DB;
        $hall_sql = " SELECT hr.*, h.id AS hallid, h.name, h.city, h.maplocation, h.entrancegate, h.buildingname, h.seatingcapacity, h.availability FROM {hall} h JOIN {local_events} e ON concat(',', e.halladdress, ',') LIKE concat('%,',h.id,',%') 
        JOIN {hall_reservations} hr ON  hr.hallid = h.id WHERE hr.typeid = :eventid AND hr.type = 'event'";
        $hall_list = $DB->get_records_sql($hall_sql, ['eventid' => $eventid]);
        $halldata = [];
        if($hall_list) {
            $count = 1;
            $cities = (new \local_hall\hall)->listofcities();
            foreach($hall_list as $list) {
                $hallcount = $count++;
                $seats = " SELECT SUM(hr.seats) FROM {hall_reservations} hr WHERE hr.id = $list->id AND hr.typeid = $eventid AND hr.type = 'event' ";
                $reservedavailable = $DB->get_field_sql($seats);
                $seatsavailable = $reservedavailable; //$list->seatingcapacity - $reservedavailable;
                if($list->examdate) {
                    $available_hall = date('jS M Y', strtotime($list->examdate));
                    $hallavailableon = strtotime($list->examdate);
                } else {
                    $available_hall = "--";
                }
                $hallfull = false;
                if ($seatsavailable == 0) {
                    $hallfull = true;
                }

                if (filter_var($list->maplocation, FILTER_VALIDATE_URL)) {
                    $locationstatus = true;
                } else {
                    $locationstatus = false;
                }

                $city = !empty($cities[$list->city]) ? $cities[$list->city] : '--';
                $halldata[] = array('hallid' => $list->hallid,'hallname' => $list->name,'city' => $city, 
                'buildingname' => $list->buildingname,
                'seatingcapacity' => $seatsavailable,//$list->seatingcapacity,
                //'seatsavailble' => $seatsavailable,
                'hallcount' => $hallcount,
                'hallfull' => $hallfull,
                'availableon' => $available_hall,
                'maplocation' => $list->maplocation,
                'locationstatus' => $locationstatus,
                'hallavailableon' => !empty($hallavailableon) ? $hallavailableon : 0,
                //'product_variations' =>  $list->product_variations
             );
            }
        }       
        return $halldata;
    }
    public function eventstatistic($stable) {
        global $DB, $PAGE, $OUTPUT, $CFG; 

        $symposium = get_string('symposium','local_events');
        $forum = get_string('forum','local_events');
        $conference = get_string('conference','local_events');
        $workshop = get_string('workshop','local_events');
        $cermony = get_string('cermony','local_events');

        $selectsql = "SELECT CONCAT(FROM_UNIXTIME(startdate,'%Y'),'Year',COUNT(id),type), FROM_UNIXTIME(startdate,'%Y') as year,COUNT(id) as eventscount,
        CASE
            WHEN type = 0 THEN '$symposium'
            WHEN type = 1 THEN '$forum'
            WHEN type = 2 THEN '$conference'
            WHEN type = 3 THEN '$workshop'
            ELSE '$cermony' 
        END AS type
        FROM {local_events} WHERE 1=1  GROUP BY FROM_UNIXTIME(startdate,'%Y'),type ORDER BY type ASC ";
        $events = $DB->get_records_sql($selectsql);
        $eventlist =array();
        $count = 0;  

        foreach($events as $event){
         
            $eventlist[$count]['numberOfEvents'] = $event->eventscount;
            $eventlist [$count]['year'] = $event->year;
            $eventlist[$count]['name'] =$event->type;                
            $count++;
         
        $count++;
        }      
        return  $eventlist;
    }

    public function all_events_for_api_listing($stable) {
        global $DB, $CFG, $USER, $OUTPUT, $PAGE, $SESSION;
        $systemcontext = context_system::instance();

        $SESSION->lang =($stable->isArabic == 'true')?'ar':'en';
        $PAGE->set_context($systemcontext);
        $formsql = '';
        $selectsql = " SELECT e.* FROM {local_events} AS e "; 
        $countsql  = " SELECT count(e.id) FROM {local_events} AS e ";
        $formsql .= " WHERE 1=1 AND e.status = 0 ";

        if (isset($stable->query) && trim($stable->query) != ''){
            $formsql .= " AND (e.title LIKE :firstnamesearch OR e.titlearabic LIKE :arabicnamesearch OR e.code LIKE :codesearch) ";
            $searchparams = array('firstnamesearch' => '%'.trim($stable->query).'%',
                                  'arabicnamesearch' => '%'.trim($stable->query).'%',
                                  'codesearch' => '%'.trim($stable->query).'%');
        } else {
            $searchparams = array();
        }
        if(!empty($stable->startDate) ){
            $formsql.= " AND FROM_UNIXTIME(e.startdate,'%Y-%m-%d') >= '$stable->startDate'";
        }

        if(!empty($stable->endDate) ){
            $formsql.= " AND FROM_UNIXTIME(e.enddate,'%Y-%m-%d') <= '$stable->endDate'";
        }
        if(!empty($stable->EventTypeId) || ($stable->EventTypeId=='0')){
            $formsql .= " AND e.type = $stable->EventTypeId";
        }
        $params = array_merge($searchparams);
        $totalevents = $DB->count_records_sql($countsql.$formsql,$params);
        $formsql .=" ORDER BY e.startdate ASC";

        $allapievents = $DB->get_records_sql($selectsql.$formsql, $params, $stable->start,$stable->length);
        $apilist = array() ;
        $count = 0;

            foreach($allapievents as  $apievent) {
              
                $apilist[$count]['activityType'] = ($SESSION->lang == 'ar') ?  'الفعاليات': 'Event';
                $apilist[$count]['name'] = ($SESSION->lang == 'ar') ? $apievent->titlearabic : $apievent->title;
                $apilist[$count]['activityID'] = $apievent->id;
               
                $apilist[$count]['description'] = strip_tags(format_text($apievent->description, FORMAT_HTML));
                $apilist[$count]['detailsPageURL'] = $CFG->wwwroot."/local/events/view.php?id=".$apievent->id;
                $hallslist = (new events)->event_hall_info($apievent->id);
                if ($hallslist) {
                    $apilist[$count]['location'] = $hallslist->maplocation;
                } else {
                    $apilist[$count]['location'] = 'null';
                }
                $apilist[$count]['date'] = date('d/m/Y H:i:s',$apievent->startdate);
                $apilist[$count]['eventTypeId'] = $apievent->type;
                $eventtype = (new events)->get_event_type($apievent->type);
                $apilist[$count]['eventTypeName'] = $eventtype;

                $halladdress = $apievent->halladdress;

                if($halladdress) {

                    $hallinfo = $DB->get_record('hall',['id'=>$halladdress]);

                    $headers = get_headers($hallinfo->maplocation , true);
                    $str = $headers['Location'];
                    $pattern = "\@[-?\d\.]*\,([-?\d\.]*)";
                    $location = preg_match('/@(\-?[0-9]+\.[0-9]+),(\-?[0-9]+\.[0-9]+)/', $str, $pattern );
                    $apilist[$count]['hallname'] = $hallinfo->name;
                    $apilist[$count]['longitude'] = !empty($pattern[2]) ? $pattern[2] : 0;
                    $apilist[$count]['latitude'] = !empty($pattern[1]) ? $pattern[1] : 0;

                } else {


                    $apilist[$count]['hallname'] = '';
                    $apilist[$count]['longitude'] =  0;
                    $apilist[$count]['latitude'] = 0;
                }

               
                $apilist[$count]['stratDate'] = date('Y-m-d',$apievent->startdate).'T'.date('H:i:s',$apievent->startdate);
                $apilist[$count]['endDate'] = date('Y-m-d',$apievent->enddate).'T'.date('H:i:s',$apievent->enddate);
                
                $starttime = gmdate("H:i:s",$apievent->slot);
                $endttime = gmdate("H:i:s",$apievent->slot + $apievent->eventduration);
               
                $apilist[$count]['startTime']  =  $starttime;
                $apilist[$count]['endTime'] = $endttime;

                
                $apilist[$count]['locationTypeId'] = $apievent->method;
                $apilist[$count]['locationTypeName'] = ($apievent->method == 0) ? get_string('onsite', 'local_events') : get_string('virtual', 'local_events');
                $count++;
            }

        $eventsApiContext = array(
            "apilist" => $apilist,
            "totalevents" => $totalevents,
            "length" => count($apilist),
        );
        return $eventsApiContext;
    }

    public function event_hall_info($eventid) {
        global $DB;
        $hall_sql = " SELECT h.* FROM {hall} h JOIN {local_events} e ON e.halladdress = h.id WHERE e.id =  $eventid";
        $hallslist  =  $DB->get_record_sql($hall_sql);
        return $hallslist;
    }
    public function get_event_type($eventtype) {
        $typearray = array(0 => get_string('symposium','local_events'),
        1 => get_string('forum','local_events'),
        2 => get_string('conference','local_events'),
        3 => get_string('workshop','local_events'),
        4 => get_string('cermony','local_events'));
        $type = $typearray[$eventtype];
        return $type;
    }
    public function get_events_types($isArabic) {
        global $DB, $PAGE, $OUTPUT, $CFG, $SESSION;
        $SESSION->lang =($isArabic == 'true')?'ar':'en';
        $types = [];
        $typearray = array(0 => get_string('symposium','local_events'),
        1 => get_string('forum','local_events'),
        2 => get_string('conference','local_events'),
        3 => get_string('workshop','local_events'),
        4 => get_string('cermony','local_events'));
        foreach($typearray as $key => $type) {
            $data = [];
            $data['name'] =  $type; 
            $data['description'] =  null; 
            $data['value'] =  $key; 
            $types[] = $data;
        }
        return $types;
    }

    public function get_event_info($eventId, $isArabic) {
        global $DB, $CFG, $OUTPUT, $PAGE, $SESSION;
        $SESSION->lang = ($isArabic == 'true')?'ar':'en';
        $eventsql = " SELECT  e.* FROM {local_events} e WHERE e.id = $eventId";
        $eventinfo = $DB->get_record_sql($eventsql);
        $event = new stdClass();
        if($eventinfo) {
            $event->eventFees = 0.0;
            $event->eventFeesWithTaxs = 0.0;
            $event->id = $eventinfo->id;
            $event->isEventForRegisteredUsersOnly = null;

           $listlang = explode(',',$eventinfo->language);
           $listl = array();
           foreach ($listlang AS $list) {
            if($list == 0) {
                $listl[][]= get_string('arabic','local_trainingprogram');

            } else {
                $listl[][]= get_string('english','local_trainingprogram');

            }
           }
            $event->languageName = $listl;
            $event->language= $eventinfo->language;
            $event->lstEventSponsorOrganizations= array();
            $event->scheduleDays= array();
            $event->lstSchedule= array();
            $event->requestNumber= null;
            $event->requestOperation= 0;
            $event->name =($SESSION->lang == 'ar')? $eventinfo->titlearabic : $eventinfo->title;
            $langarray = array(1 => get_string('arabic','local_events'),
            2 => get_string('english','local_events'));
            $languages = explode(',', $event->language);
            $language = [];
            foreach($languages as $gender) {
                $language[] = $langarray[$gender];
            }
            $event->languageName = implode(', ',$language);

            $event->lstEventSpeakers = array();
            $speakerlist = (new events)->event_speakersinfo($eventinfo->id);
            if($speakerlist) {
                $event->lstEventSpeakers = $speakerlist;
            }
            $hall = (new events)->event_hall_info($eventinfo->id);

            if ($hall) {
                $cityid = $hall->city;
                $cityname = (new hall())->listofcities($hall->city,$isArabic);
            } else {
                $cityid = 0;
                $cityname = '';
            }

            $hallinfo = $DB->get_record('hall',['id'=>$eventinfo->halladdress]);    

            $lstCities[]=array(
                    'name' => $hallinfo->city? (new \local_hall\hall())->listofcities($hallinfo->city):null,
                    'description' => null,
                    'value' =>$cityid?$cityid:0);  

            $lstCountries[]=array(
                    'name' =>  $hallinfo->name,
                    'description' => strip_tags(format_text($hallinfo->description, FORMAT_HTML)),
                    'value' =>$hallinfo->id); 

            $headers = get_headers($hallinfo->maplocation , true);
            $str = $headers['Location'];
            $pattern = "\@[-?\d\.]*\,([-?\d\.]*)";
            $location = preg_match('/@(\-?[0-9]+\.[0-9]+),(\-?[0-9]+\.[0-9]+)/', $str, $pattern );
            $locationTypeId = $eventinfo->method;
            if ($eventinfo->method == 0) {
                $locationTypeName = get_string('inclass', 'local_events');
            } else {
                $locationTypeName = get_string('virtual', 'local_events');
            }
            $longitude = !empty($pattern[2]) ? $pattern[2] : 0;
            $latitude = !empty($pattern[1]) ? $pattern[1] : 0;
            $coordinates = ['longitude' => $longitude, 'latitude' => $latitude];

            $event->scheduleObj[] = [
                'cityId' => $cityid,
                'countryId' => null,
                'tempCountryId' => null,
                'tempCityId' => 0,
                'cityName' => $cityname,
                'endDate' => date('Y-m-d', $eventinfo->enddate).'T'.date('H:i:s', $eventinfo->enddate),
                'endTime' =>  gmdate("H:i:s",($eventinfo->slot + $eventinfo->eventduration)),
                'eventId' => $event->id,
                'id' => $event->id,
                'isDaysRequireSpecificTimes' => null,
                'isEventHasRoomReservation' => null,
                'locationTypeId' => $locationTypeId,
                'locationTypeName' => $locationTypeName,
                'coordinates' => $coordinates,
                // 'longitude' => !empty($pattern[2]) ? $pattern[2] : 0,
                // 'latitude' => !empty($pattern[1]) ? $pattern[1] : 0,
                'lstCities' =>$lstCities ,
                'lstCountries' =>$lstCountries ,
                'lstScheduleDays' =>null ,
                'registrationStartDate' =>  date('Y-m-d', $eventinfo->registrationstart).'T'.date('H:i:s', $eventinfo->registrationstart),
                'registrationEndDate' =>  date('Y-m-d', $eventinfo->registrationend).'T'.date('H:i:s', $eventinfo->registrationend),
                'rescheduleReason' => null,
                'stratDate' => date('Y-m-d', $eventinfo->startdate).'T'.date('H:i:s', $eventinfo->startdate),
                'startTime'  =>  gmdate("H:i:s",$eventinfo->slot),
                'tempEndTim'  =>  null,
                'tempRegistrationEndDate'  =>  null,
                'tempRegistrationStartDate'  =>  null,
                'tempStartTime'  =>  null,
                'withinKingdom'  =>  null,
                'countryName'  =>  null,
            ];
            $availableseats = (new events)->events_available_seats($eventinfo->id);
            $genderarray = array(1 => get_string('male','local_events'),
            2 => get_string('female','local_events'));
            $genders = explode(',', $eventinfo->audiencegender);
            $audiencegender = [];
            foreach($genders as $gender) {
                $audiencegender[] = $genderarray[$gender];
            }
            $event->eventFees = number_format($eventinfo->sellingprice);
            $event->language = $eventinfo->language;
            $event->seatsAvailable = $availableseats['availableseats'];
            $event->seatsHasLimit = false;
            $event->seatsReserved = $availableseats['enrolled'];
            $event->seatsTotal = $availableseats['totalseats'];
            $event->summary =strip_tags(format_text($eventinfo->description, FORMAT_HTML));
            $event->targetAudienceGenderName = implode(', ',$audiencegender);
            $event->estimatedBudget = number_format($eventinfo->logisticsestimatedbudget);
            $event->eventIDPortal = null;
            $event->taskStatusId = null;
            $event->individualsSeatsCount = $availableseats['availableseats'];
            $event->reservedIndividualsSeatsCount = null;
            $event->locationTypeName = ($eventinfo->method == 0) ? get_string('onsite', 'local_events') : get_string('virtual', 'local_events');
            $event->roomName = null;
            $event->roomAddress = null;
            $event->agendaAttachmentId = null;
            $event->externalRegistrationUrl = null;
            $event->detailsPageURL =$CFG->wwwroot.'/local/events/alleventsview.php?id='.$eventinfo->id;
            return $event;
           
        }
    }

    public function unenroll_event($eventid, $userid, $type = false) {
        global $DB, $USER;
        try{
            $DB->delete_records('local_event_attendees',array('eventid'=>$eventid,'userid'=>$userid));
            if($type == 'cancelentity') {
                $DB->execute('UPDATE {local_events} SET status = 2 WHERE id ='.$eventid);
            }
            $notificationdetails = $DB->get_record('local_events',array('id'=>$eventid));
            $notificationdetails->event_name = $notificationdetails->title;
            $notificationdetails->event_arabicname =  $notificationdetails->titlearabic;
            $notificationdetails->event_date = date('d-m-Y');
            $notificationdetails->event_time = date("H:i:s",time()); 
            $localuserrecord = $DB->get_record('local_users',['userid'=>$userid]);
            $trainee = $DB->get_record('user',array('id'=>$userid));
            $notificationdetails->event_userfullname =($localuserrecord)? (($localuserrecord->lang == 'ar') ? $localuserrecord->firstnamearabic.' '.$localuserrecord->middlenamearabic.' '.$localuserrecord->thirdnamearabic.' '.$localuserrecord->lastnamearabic  : $localuserrecord->firstname.' '.$localuserrecord->middlenameen.' '.$localuserrecord->thirdnameen.' '.$localuserrecord->lastname) :fullname($trainee);   
            (new \local_events\notification())->event_notification('event_unregister',$touser= $trainee, $USER,$notificationdetails,$waitinglistid=0);      
             
            return true;
        } catch(dml_exception $e){
            print_r($e);
        }
    
       
    } 

     public function get_tobereplacedusers($query = null ,$replacinguserid = null, $rootid = null, $fieldid = null){
        global $DB,$USER;

        $systemcontext = context_system::instance();
        $courseid=$DB->get_field('local_trainingprogram','courseid', array('id' => $rootid));
        $traineeroleid= $DB->get_field('role', 'id', array('shortname' => 'trainee'));
        $fields = array(
            'lc.firstname',
            'lc.lastname',
            'lc.firstnamearabic',
            'lc.lastnamearabic',
            'lc.middlenamearabic',
            'lc.thirdnamearabic',
            'lc.middlenameen',
            'lc.thirdnameen',
            'lc.id_number',
            'lc.email');       
        $likesql = array();
        $i = 0;
        foreach ($fields as $field) {
            $i++;
            $searchquery = trim($query);
            $likesql[] = $DB->sql_like($field, ":queryparam$i", false);
            $sqlparams["queryparam$i"] = "%$searchquery%";
        }
        $sqlfields = implode(" OR ", $likesql);
        $concatsql = " AND ($sqlfields) ";

        $displaying_name =(new trainingprogram)->user_fullname_case();
        $enrolleduser =(int) $DB->get_field('local_event_attendees','usercreated',[
            'eventid'=>$rootid,
            'userid'=>$replacinguserid
        ]);
        $enrolleduserroleinfo = $DB->get_record_sql('SELECT rol.* FROM {role} rol 
        JOIN {role_assignments} rola ON rola.roleid = rol.id
        WHERE rola.userid =:userid and contextid =:contextid',['userid'=>$enrolleduser,'contextid'=>$systemcontext->id]);

        if(($enrolleduserroleinfo->shortname == 'organizationofficial') || (!is_siteadmin() && has_capability('local/organization:manage_organizationofficial',$systemcontext))) {
            $user_id = ($enrolleduser) ? $enrolleduser: $USER->id;
            $organization = $DB->get_field('local_users','organization',array('userid'=>$user_id));
        $where = " WHERE  lc.organization = $organization ";
        } else {
        $where = " WHERE 1=1  ";
        }
        $sql = "SELECT u.id,$displaying_name
                FROM {user} u 
                JOIN {local_users} lc ON lc.userid = u.id
                JOIN {role_assignments} rla ON rla.userid=u.id  
                $where  AND  u.id > 2 AND u.deleted = 0 AND  rla.contextid = $systemcontext->id AND rla.roleid =$traineeroleid AND u.id NOT IN 
                (SELECT ea.userid FROM {local_event_attendees} AS ea                         
                WHERE  ea.eventid = $rootid) AND u.id $concatsql ";

        $order = " ORDER BY u.id DESC limit 50";
        $data = $DB->get_records_sql($sql.$order, $sqlparams);
        $return = array_values(json_decode(json_encode(($data)), true));
        return $return;
    }

    public function event_replacement_process($data) {
        global $DB,$USER;
        $traineeroleid= $DB->get_field('role', 'id', array('shortname' => 'trainee'));
        $accessstatus = (new exams)->access_fast_service('replaceservice');
        if(!$accessstatus || $data->costtype == 0 || ($data->costtype == 1 && is_siteadmin() && $data->policyconfirm == 0)) {
            $this->unenroll_event($data->rootid,$data->fromuserid,'replace');
            $this->enrol_event($data->rootid,$data->touserid,$data->enrollinguserid,'replace');
            return true;
        } else {
            $sendingdata =new stdClass();
            $sendingdata->eventid = $data->rootid;
            $sendingdata->userid = $data->enrollinguserid;
            $sendingdata->productid = $data->productid;
            $sendingdata->seats = 1;
            $sendingdata->type = 'replacement';
            $sendingdata->total = $data->replacementfee;
            $sendingdata->payableamount = $data->replacementfee;
            $productdata =  base64_encode(serialize((array)$sendingdata));
            $returndata = (new \tool_product\product)->insert_update_sadad_invoice_record($productdata);
            if($returndata){
                $this->unenroll_event($data->rootid,$data->fromuserid,'replace');
                $this->enrol_event($data->rootid,$data->touserid,$data->enrollinguserid,'replace');
            }
            return true;
        }
    }
    public function event_cancel_user($sdata) {
        global $DB,$USER,$CFG;
        $context = context_system::instance();
        $referanceid = (int)$DB->get_field('tool_products','referenceid',['id'=>$sdata->productid]);
        $eventrecord =$DB->get_record('local_events',['id'=>$referanceid]);
        $data =new stdClass();
        $data->eventid =$referanceid;
        $data->userid = $sdata->userid;
        $data->examprice = $sdata->examprice;
        $data->amount = $sdata->amount;
        $data->refundamount = $sdata->refundamount;
        $data->newinvoiceamount = $sdata->newinvoiceamount;
        $data->newamount = $sdata->newamount;
        $data->productid = $sdata->productid;
        $data->policyconfirm = $sdata->policyconfirm;
        $data->examdate = $sdata->examdate;
        $data->invoicenumber = $sdata->invoicenumber;
        $data->entitytype = $sdata->entitytype;
        $policies = new \local_events\policies('event', $sdata->examdate, 'cancel');
        $enrolledroleinfo =  $policies->enrolled_by($sdata->userid,$sdata->productid);
        $sdata->enrolleduserid = $enrolledroleinfo->enrolleduserid;
        $costtype =(int) $DB->get_field('local_events','price',['id'=>$referanceid]);
        if($costtype == 0 || ($enrolledroleinfo->shortname =='organizationofficial' && $sdata->newinvoiceamount <= 0) || $sdata->refundamount <= 0) {
            $referanceid = (int)$DB->get_field('tool_products','referenceid',['id'=>$sdata->productid]);
            $enrolleduserid =(int) $DB->get_field('local_event_attendees','usercreated',['eventid'=>$referanceid,'userid'=>$sdata->userid]);           
           if ($costtype > 0) {
                $data = (array)$data;
                $data['userid'] =(is_siteadmin() ||  $enrolledroleinfo->shortname =='organizationofficial' ||  $enrolledroleinfo->shortname =='em') ? $enrolleduserid  :  $USER->id;
                (new \tool_product\product)->update_org_order_seats_for_cancellation((array)$data);
            }
            (new \tool_product\telr)->void_invoice($sdata->invoicenumber);
            $this->unenroll_event($referanceid,$sdata->userid,'cancel');
        } else {
            $policies = new \local_events\policies($sdata->entitytype, $sdata->examdate, 'cancel');
            $policies->cancel_process($data);
        }

        if(($sdata->newinvoiceamount == 0) && ($sdata->newamount == 0) && ($enrolledroleinfo->shortname =='organizationofficial')){
            $returndata = (new \tool_product\product)->invoice_record_for_0_cancellation($sdata);
        }
        $returnurl = (is_siteadmin() || has_capability('local/organization:manage_organizationofficial', context_system::instance())) ? $CFG->wwwroot.'/local/events/attendees.php?id='.$referanceid:  $CFG->wwwroot.'/local/events/index.php'; 
        return $returnurl;

    }  

    
}
