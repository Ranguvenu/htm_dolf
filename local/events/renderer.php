<?php
require_once($CFG->dirroot . '/local/events/lib.php');
use local_events\events as events;
use local_exams\local\exams;
class local_events_renderer extends plugin_renderer_base {

    public function render_events($page)
    {
        $data = $page->export_for_template($this);                                                                                  
        return parent::render_from_template('local_trainingprogram/mainpage', $data);         
    }

    public function get_catalog_events($filter = false) {
        global $CFG;
        $systemcontext = context_system::instance();
        $options = array('targetID' => 'content_wrapper','perPage' => 6, 'cardClass' => 'col-md-6 col-12', 'viewType' => 'card');
        $options['methodName']='local_events_view_events';
        $options['templateName']='local_events/eventdetails';
        $options = json_encode($options);
        $filterdata = json_encode(array('status' => 0));
        $dataoptions = json_encode(array('contextid' => $systemcontext->id));
        $context = [
                'targetID' => 'content_wrapper',
                'options' => $options,
                'dataoptions' => $dataoptions,
                'filterdata' => $filterdata,
        ];
        if($filter){
            return  $context;
        }else{
            return  $this->render_from_template('theme_academy/cardPaginate', $context);
        }
    }
    public function listofevents($filterparams) {
        global $CFG, $DB, $PAGE, $OUTPUT;
        $systemcontext = context_system::instance();
        $eventsmform = events_filters_form($filterparams);
        $filterparams['filterform'] = $eventsmform->render();
        if(is_siteadmin() || has_capability('local/organization:manage_event_manager', $systemcontext)) {
           $filterparams['addevent'] = $CFG->wwwroot."/local/events/addevent.php";
        }
        echo $this->render_from_template('local_events/eventlist', $filterparams);
    }
     // Vinod -Events fake block for event manager - Starts//

    public function listofeventsformanager($filterparams) {
        global $CFG, $DB, $PAGE, $OUTPUT;
        $systemcontext = context_system::instance();
        return $this->render_from_template('local_events/eventlist', $filterparams);
    }
     // Vinod -Events fake block for event manager - Ends//

    public function get_eventContent($eventid) {

        $systemcontext = context_system::instance();
        global $DB, $CFG, $OUTPUT, $PAGE;
        $eventsql = " SELECT  e.* FROM {local_events} e WHERE e.id = $eventid";
        $event = $DB->get_record_sql($eventsql);
        $lang= current_language();
        if ($event) {
            if($event->eventmanager) {
                $fullname = (new \local_trainingprogram\local\trainingprogram)->user_fullname_case();
                $eventmanagersql = "SELECT $fullname
                                        FROM {user} AS u 
                                        JOIN {local_users} lc ON lc.userid = u.id 
                                        JOIN {local_events} e ON concat(',', e.eventmanager, ',') LIKE concat('%,',u.id,',%')
                                        WHERE e.id = :eventid";
                $eventmanager = $DB->get_fieldset_sql($eventmanagersql,['eventid' => $eventid]);
                $event->eventmanager = $eventmanager ? implode(',', $eventmanager):'--';
            } else {
                $event->eventmanager = '--';
            }
            $event->halldata = array();
            $hallslist = (new events)->event_hallinfo($event->id);
            if ($hallslist) {
                $hallslimit = false;
                $event->halldata = $hallslist;
            }
            if(count($hallslist) > 2){
                $hallslimit = false;
                $event->morehalls = array_slice($event->halldata, 0, 2);
            } else {
                $hallslimit = true;
                $event->morehalls = $event->halldata;
            }
            $event->hallslimit = $hallslimit;
           // print_object($event->halldata); exit;
            //$event->audiencegender = $event->audiencegender;
            //$event->language = $event->language;
            $langarray = array(1 => get_string('arabic','local_events'),
            2 => get_string('english','local_events'));
            $languages = explode(',', $event->language);
            $language = [];
            foreach($languages as $gender) {
                $language[] = $langarray[$gender];
            }
            $eventlanguage = implode(', ',$language);

            $genderarray = array(1 => get_string('male','local_events'),
            2 => get_string('female','local_events'));
            $genders = explode(',', $event->audiencegender);
            $audiencegender = [];
            foreach($genders as $gender) {
                $audiencegender[] = $genderarray[$gender];
            }
            $eventgender = implode(', ',$audiencegender);
             if (!is_null($event->logo) &&  $event->logo > 0) {
                $eventimg = logo_url($event->logo);
                if($eventimg == false){
                    $eventimg = (new events)->eventdefaultimage_url();
                }
            }else{
                $eventimg = (new events)->eventdefaultimage_url();
            }
            $eventimg = $eventimg;
            if (empty($event->description)) {
                $isdescription = false;
            } else {
                $isdescription = true;
                $description =  format_text($event->description,FORMAT_MOODLE);
            }

            $event_starttimemeridian = date("a",mktime(0, 0, $event->slot));
            $event_endtimemeridian = date("a",mktime(0, 0, ($event->slot + $event->eventduration)));

            $event_starttime = date('h:i', mktime(0, 0, $event->slot));
            $event_endttime = date("h:i",mktime(0, 0, ($event->slot + $event->eventduration)));
            $get_time_lang = (new events())->time_lang_change($event_starttimemeridian, $event_endtimemeridian);
            $starttimemeridian = gmdate('a',$event->slot);
            $endtimemeridian = gmdate('a',($event->slot + $event->eventduration));

            $regstarttimemeridian = date('a',$event->registrationstart);
            $regendtimemeridian = date('a',($event->registrationend));
           
            $eventstarttime = gmdate("h:i",$event->slot);
            $eventendttime = gmdate("h:i",($event->slot + $event->eventduration));
            $regstarttime = date("h:i",$event->registrationstart);
            $regendttime = date("h:i",($event->registrationend));
        

            $eventstartmeridian = ($starttimemeridian == 'am')?  get_string('am','local_trainingprogram'):get_string('pm','local_trainingprogram');
            $eventendmeridian = ($endtimemeridian == 'am')?  get_string('am','local_trainingprogram'):get_string('pm','local_trainingprogram');
             
            $regstartmeridian = ($regstarttimemeridian == 'am')?  get_string('am','local_trainingprogram'):get_string('pm','local_trainingprogram');
            $regendmeridian = ($regendtimemeridian == 'am')?  get_string('am','local_trainingprogram'):get_string('pm','local_trainingprogram');

            $event->startdate = userdate($event->startdate, get_string('strftimedatemonthabbr','core_langconfig')).' '.$eventstarttime.' '.$eventstartmeridian;//. ' @ ' .$event_starttime.' '.$get_time_lang['startmeridian'];
            $event->enddate = userdate(($event->enddate), get_string('strftimedatemonthabbr', 'core_langconfig')).' '.$eventendttime.' '.$eventendmeridian;//. ' @ ' .$event_endttime.' '.$get_time_lang['startmeridian'];

            $reg_starttime =  userdate($event->registrationstart, get_string('strftimetime12','core_langconfig'));
            $reg_endttime = userdate($event->registrationend, get_string('strftimetime12','core_langconfig'));
        
           // $reg_starttime =  userdate($event->registrationstart, get_string('strftimetime12','core_langconfig'));
           // $reg_endttime = userdate($event->registrationend, get_string('strftimetime12','core_langconfig'));

            $event->reg_startdate = userdate($event->registrationstart, get_string('strftimedate', 'core_langconfig')).' @ '.$regstarttime.' '.$regstartmeridian;
            $event->reg_enddate = userdate($event->registrationend, get_string('strftimedate', 'core_langconfig')).' @ '.$regendttime.' '.$regendmeridian;

            $geteventtype = (new events())->get_event_type($event->type);
            $eventtype = $geteventtype;

            $current_date = time();
            $eventendttime = ($event->enddate+$event->slot+$event->eventduration);

            $statusarray = array(0 => get_string('active', 'local_events'),
            1 => get_string('inactive', 'local_events'),
            2 => get_string('cancelled', 'local_events'),
            3 => get_string('closed', 'local_events'),
            4 => get_string('archieved', 'local_events'));
            $eventstatus = ($event->status == 0) ? (($eventendttime <= $current_date) ?  $statusarray[0] : $statusarray[1]) : $statusarray[$event->status];
            // $eventstatus = $statusarray[$event->status];
            $launchbtn = false;
            $event->showhall = false; 
            $methodarray = array(0 => get_string('inclass', 'local_events'),1 => get_string('virtual', 'local_events'));
            $eventsmethod = $methodarray[$event->method];
            $starttime = userdate(mktime(0, 0, $event->slot), '%H:%M');
            $endttime = userdate(mktime(0, 0, ($event->slot + $event->eventduration)), '%H:%M');
            $current_time =  userdate(time(), '%H:%M');

            $joinurl = '';
            if(is_siteadmin() || has_capability('local/organization:manage_event_manager', context_system::instance()) || has_capability('local/organization:manage_trainee', context_system::instance())) {
                if($event->method == 1) {
                    $days_between = events::get_agenda_dates($event->id);
                    $curr_date_time = userdate(time(), get_string('strftimedatetime', 'core_langconfig'));
                    if (($curr_date_time >= $event->startdate) && ($event->enddate <= $curr_date_time || $event->enddate >= $curr_date_time)) {
                    
                         if(($current_time >= $starttime)  && ($current_time <= $endttime)){
                            $launchbtn = true; 
                        }else{
                             $launchbtn = false;
                        }
                    } else {
                        $launchbtn = false;
                    }
                    if($event->virtualtype == 1) {
                        $zoom_url = $DB->get_field('zoom','	join_url',['id' => $event->zoom]);
                        $joinurl = !empty($zoom_url)?$zoom_url:0;
                    } else if($event->virtualtype == 2) {
                        $coursemoduleid =  get_coursemodule_from_instance('webexactivity',$event->webex, 1);
                        $urlobj = new moodle_url('/mod/webexactivity/view.php', array('id' => $coursemoduleid->id, 'action' => 'joinmeeting'));
                        $webexurl =  $urlobj->out(false);
                        $joinurl = !empty($webexurl)?$webexurl:0;
                    } elseif($event->virtualtype == 3) {
                        $teams_url = $DB->get_field('teamsmeeting',' meetingurl',['id' => $event->teams]);
                        $joinurl = !empty($teams_url) ? $teams_url : 0;
                    }
                }

                if($event->method == 0) {
                    $event->showhall = true; 
                }
            }
            if ((has_capability('local/events:manage', context_system::instance()) 
                ||has_capability('local/organization:manage_event_manager', context_system::instance())
                || is_siteadmin())) {
                $actions = true;
                $agedatopicurl = new moodle_url("/local/events/agendatopics.php?id=".$event->id."");
                $attendeeusrl = new moodle_url("/local/events/attendees.php?id=".$event->id."");
                $speakersurl = new moodle_url("/local/events/speakers.php?id=".$event->id."");
                $sponsorurl = new moodle_url("/local/events/sponsors.php?id=".$event->id."");
                $partnersurl = new moodle_url("/local/events/partners.php?id=".$event->id."");
                $financeurl = new moodle_url("/local/events/finance.php?id=".$event->id."");
                $logisticsbudget = true;
            }
            $event->agendadata = array();
            $agendalist = (new events)->event_agendainfo($event->id); 
            if($agendalist) {
                $event->agendadata = $agendalist;
            }
            
            $lang= current_language();
            if( $lang == 'ar' && !empty($event->titlearabic)){
                $title = $event->titlearabic;
            }else{
                $title = $event->title;
            }
            $event->platinum_sponsordata = array();
            $event->gold_sponsordata = array();
            $event->silver_sponsordata = array();
            //Platinum 
            $platinum_sporslist = (new events)->event_sponsorsinfo($event->id, 0);
            if($platinum_sporslist) {
                $event->platinum_sponsordata = $platinum_sporslist;
            }
            //Gold
            $gold_sporslist = (new events)->event_sponsorsinfo($event->id, 1);
            if($gold_sporslist) {
                $event->gold_sponsordata = $gold_sporslist;
            }
            //Silver
            $silver_sporslist = (new events)->event_sponsorsinfo($event->id, 2);
            if($silver_sporslist) {
                $event->silver_sponsordata = $silver_sporslist;
            }

            $event->partnerdata = array();
            $partnerslist = (new events)->event_partnersinfo($event->id);
            if($partnerslist) {
                $event->partnerdata = $partnerslist;
            }
          
            $event->orgofficial = false;
            if(!is_siteadmin() &&  has_capability('local/organization:manage_organizationofficial',$systemcontext)) {
            $event->orgofficial = true;
            }
            $eventContent = [
                'event' => $event,
                'eventid' => $event->id,
                'eventlogo' => $eventimg,
                'eventstatus' =>  $eventstatus,
                'eventname' => $title,
                'isdescription' => $isdescription,
                'eventdescription' => $description,
                'eventtype' => $eventtype,
                'estimatedbudget' => number_format($event->logisticsestimatedbudget),
                'actualbudget' => $event->logisticsactualbudget,
                'actions' => $actions,
                'agedatopicurl' => $agedatopicurl,
                'attendeeusrl' => $attendeeusrl,
                'speakersurl' =>  $speakersurl,
                'sponsorurl' =>  $sponsorurl,
                'partnersurl' =>  $partnersurl,
                'eventimg' => $eventimg,
                'financeurl' => $financeurl,
                'actions'=> $actions,
                'eventsmethod' => $eventsmethod,
                'eventlanguage' => $eventlanguage,
                'eventgender' => $eventgender,
                'logisticsbudget' => $logisticsbudget,
                'launchbtn' => $launchbtn,
                'joinurl' => $joinurl,
                'iscancelled'=>($event->cancelled == 2) ? true:false,
                'cancelstatus'=>($event->cancelled == 2) ? get_string('cancelled','local_trainingprogram'):  (($event->cancelled == 1 || $event->cancelled == -1) ? get_string('cancelrequestpending','local_trainingprogram') : get_string('cancelrequestrejected','local_trainingprogram')),
            ];
            return $this->render_from_template('local_events/eventContent', $eventContent);
        }
    }

    public function get_catalog_agendainfo($filter = false) {
        $systemcontext = context_system::instance();
        $eventid = optional_param('id', 0, PARAM_INT);
        $options = array('targetID' => 'manage_agenda','perPage' => 5, 'cardClass' => 'col-md-6 col-12', 'viewType' => 'card');
        $options['methodName'] = 'local_events_agenda';
        $options['templateName'] = 'local_events/agendalist';
        $options['eventid'] = $eventid;
        $options = json_encode($options);
        $filterdata = json_encode(array());
        $dataoptions = json_encode(array('contextid' => $systemcontext->id));
        $context = [
                'targetID' => 'manage_agenda',
                'options' => $options,
                'dataoptions' => $dataoptions,
                'filterdata' => $filterdata,
        ];
        if($filter){
            return  $context;
        }else{
            return  $this->render_from_template('theme_academy/cardPaginate', $context);
        }
    }

    public function get_catalog_attendees($filter = false) {
        global $CFG, $DB;
        $systemcontext = context_system::instance();
        $eventid = optional_param('id', 0, PARAM_INT);
        $options = array('targetID' => 'manage_attendees','perPage' => 5, 'cardClass' => 'col-md-6 col-12', 'viewType' => 'card');
        $options['methodName'] = 'local_events_attendees_list';
        $options['templateName'] = 'local_events/attendeeslist';
        $options['eventid'] = $eventid;
        $options = json_encode($options);
        $filterdata = json_encode(array());
        $dataoptions = json_encode(array('contextid' => $systemcontext->id));
        $context = [
                'targetID' => 'manage_attendees',
                'options' => $options,
                'dataoptions' => $dataoptions,
                'filterdata' => $filterdata,
        ];
        $event = $DB->get_record('local_events',['id' => $eventid]);
        if($event->method == 0) {
            $eventmethod = true;
            $seats = true;
        } else {
            $seats = false;
            $eventmethod = false;
        }
        $organizationofficial = false;
        $totalseats = (new events)->events_available_seats($eventid);
      
        if(!is_siteadmin() && has_capability('local/organization:manage_organizationofficial',$systemcontext)) {
            $organizationofficial = true;

            $availableseats = $totalseats['availableseats'];
            $today_datetime = time();

            if($event->method == 0 && $availableseats > 0 && $event->registrationend >= $today_datetime) {
                $addattendee = true;
            } else if($event->method == 1 && $event->registrationend >= $today_datetime) {    
                $addattendee = true;
            }
            //(new \tool_product\product)->availableseats_check('local_events','id', $eventid);
        } else if(is_siteadmin() || has_capability('local/organization:manage_event_manager', $systemcontext)) {
            $availableseats = $totalseats['availableseats'];
            if($event->method == 0 && $availableseats > 0) {
                $addattendee = true;
            } else if($event->method == 1) {
                $addattendee = true;
            }
        } else {
            $availableseats = $totalseats['availableseats'];
        }
        if($availableseats < 0) {
            $availableseats = 0;
        }
        $fncardparams = $context;
            $attendeesmform = attendees_filters_form($context);
            $context = $fncardparams+array(
                'addattendee'=> $addattendee,//(is_siteadmin() || has_capability('local/events:manage', $systemcontext) ||  has_capability('local/organization:manage_event_manager', $systemcontext) ||  has_capability('local/organization:manage_organizationofficial',$systemcontext)) ? true : false,
                'eventid' => $eventid,
                'contextid' => $systemcontext->id,
                'plugintype' => 'local',
                'plugin_name' =>'events',
                'createattendees' => true,
                'eventmethod' => $eventmethod,
                'organizationofficial' => $organizationofficial,
                'availableseats' => $availableseats,
                'seats' => $seats,
                'cfg' => $CFG,
                'cfgurl' => $CFG->wwwroot,
                'filterform' => $attendeesmform->render());
                
            // print_object($context); exit;
        if ($filter) {
            return  $context;
        } else {
            return  $this->render_from_template('local_events/viewattendees', $context);
        }
    }

    public function attendee_info($data) {
        global $DB, $PAGE, $OUTPUT, $CFG;
        return $this->render_from_template('local_events/attendeedetails', $data);
    }

    public function agenda_info($data) {
        global $DB, $PAGE, $OUTPUT, $CFG;
        return $this->render_from_template('local_events/agendadetails', $data);
    }

    public function get_catalog_speakers($filter = false) {
        $systemcontext = context_system::instance();
        $eventid = optional_param('id', 0, PARAM_INT);
        $options = array('targetID' => 'manage_speakers','perPage' => 5, 'cardClass' => 'col-md-6 col-12', 'viewType' => 'card');
        $options['methodName'] = 'local_events_speakers_list';
        $options['templateName'] = 'local_events/speakerslist';
        $options['eventid'] = $eventid;
        $options = json_encode($options);
        $filterdata = json_encode(array());
        $dataoptions = json_encode(array('contextid' => $systemcontext->id));
        $context = [
                'targetID' => 'manage_speakers',
                'options' => $options,
                'dataoptions' => $dataoptions,
                'filterdata' => $filterdata,
        ];
        if($filter){
            return  $context;
        }else{
            return  $this->render_from_template('theme_academy/cardPaginate', $context);
        }
    }

    public function speaker_view($data) {
        global $DB, $PAGE, $OUTPUT, $CFG;
        return $this->render_from_template('local_events/speakerdetails', $data);
    }


    public function get_catalog_sponsors($filter = false) {
        $systemcontext = context_system::instance();
        $eventid = optional_param('id', 0, PARAM_INT);
        $options = array('targetID' => 'manage_sponsors','perPage' => 5, 'cardClass' => 'col-md-6 col-12', 'viewType' => 'card');
        $options['methodName'] = 'local_events_sponsors_list';
        $options['templateName'] = 'local_events/sponsorslist';
        $options['eventid'] = $eventid;
        $options = json_encode($options);
        $filterdata = json_encode(array());
        $dataoptions = json_encode(array('contextid' => $systemcontext->id));
        $context = [
                'targetID' => 'manage_sponsors',
                'options' => $options,
                'dataoptions' => $dataoptions,
                'filterdata' => $filterdata,
        ];
        if($filter){
            return  $context;
        }else{
            return  $this->render_from_template('theme_academy/cardPaginate', $context);
        }
    }

    public function sponsor_view($data) {
        global $DB, $PAGE, $OUTPUT, $CFG;
        return $this->render_from_template('local_events/sponsordetails', $data);
    }

    public function get_catalog_partners($filter = false) {
        $systemcontext = context_system::instance();
        $eventid = optional_param('id', 0, PARAM_INT);
        $options = array('targetID' => 'manage_partners','perPage' => 5, 'cardClass' => 'col-md-6 col-12', 'viewType' => 'card');
        $options['methodName'] = 'local_events_partners_list';
        $options['templateName'] = 'local_events/partnerslist';
        $options['eventid'] = $eventid;
        $options = json_encode($options);
        $filterdata = json_encode(array());
        $dataoptions = json_encode(array('contextid' => $systemcontext->id));
        $context = [
                'targetID' => 'manage_partners',
                'options' => $options,
                'dataoptions' => $dataoptions,
                'filterdata' => $filterdata,
        ];
        if($filter){
            return  $context;
        }else{
            return  $this->render_from_template('theme_academy/cardPaginate', $context);
        }
    }

    public function partner_view($data) {
        global $DB, $PAGE, $OUTPUT, $CFG;
        return $this->render_from_template('local_events/partnerdetails', $data);
    }


    public function list_partners($stable, $filterdata=null) {
        global $USER, $CFG, $DB;
        $systemcontext = context_system::instance();
        $getpartners = events::get_listof_partners($stable, $filterdata);
        $partners = array_values($getpartners['partners']);
        $row = array();
        $stable = new \stdClass();
        $stable->thead = true;
        $count = 0;
       // var_dump($partners); exit;
        foreach ($partners as $list) {
            $record = array();
            $record['id'] = $list->id;
            $record['eventid'] = $list->eventid;
            $record['partnername'] = $list->name;
            $description = format_text($list->description,FORMAT_HTML);
            $isdescription = '';
            if (empty($description)) {
               $isdescription = false;
            } else {
                $isdescription = true;
                if (strlen($description) > 75) {
                    $strlength = true;
                    $decsriptionCut = mb_substr(strip_tags($description), 0, 75);
                    $descriptionstring = $decsriptionCut;
                } else {
                    $strlength = false;
                    $descriptionstring = $description;
                }
            }
            if ($list->logo > 0) {
                $logoimg = logo_url($list->logo);
                if(!$logoimg){
                    $logoimg = '';
                }
            } else {
                $logoimg = '';
            }
           
            $record['description'] = $description;
            $record['descriptionstring'] = $descriptionstring;
            $record['isdescription'] = $isdescription;
            $record['strlength'] = $strlength;
            if ((is_siteadmin() || has_capability('local/events:manage', context_system::instance()) || has_capability('local/organization:manage_event_manager', context_system::instance()) )) {
                $record['action'] = true;
            }
            $record['logo'] = $logoimg;
            $row[] = $record;
        }
        //var_dump($row); exit;
        return array_values($row);
    }

    public function list_sponsors($stable, $filterdata=null) {
        global $USER, $CFG, $DB, $OUTPUT;
        $systemcontext = context_system::instance();
        $getsponsors = events::get_listof_sponsors($stable, $filterdata);
        $sponsors = array_values($getsponsors['sponsors']);
        $row = array();
        $stable = new \stdClass();
        $stable->thead = true;
        $count = 0;
        foreach ($sponsors as $list) {
            $record = array();
            $record['id'] = $list->id;
            $record['eventid'] = $list->eventid;
            $record['amount'] = $list->amount;
            $record['sponsorname'] = $list->name;
            $catarray = array('0' => get_string('platinum','local_events'), '1' => get_string('gold','local_events'), '2' => get_string('silver','local_events'));
            $record["category"] = $catarray[$list->category];
            if ((is_siteadmin() || has_capability('local/events:manage', context_system::instance()) || has_capability('local/organization:manage_event_manager', context_system::instance()) )) {
                $record['action'] = true;
            }
            if ($list->logo > 0) {
                $logoimg = logo_url($list->logo);
            } else {
                $logoimg = '';
            }
            $record['logo'] = $logoimg;
            $row[] = $record;
        }
        return array_values($row);
    }

    public function list_speakers($stable, $filterdata=null) {
        global $USER, $CFG, $DB;
        $systemcontext = context_system::instance();
        $getspeakers = events::get_listof_speakers($stable, $filterdata);
        $speakers = array_values($getspeakers['speakers']);
        $row = array();
        $stable = new \stdClass();
        $stable->thead = true;
        $count = 0;
        foreach ($speakers as $list) {
            $record = array();
            $record['id'] = $list->id;
            $record['eventid'] = $list->eventid;
            $record['eventname'] = $list->title;
            $record['speakername'] = $list->name;
            $record['specialist'] = $list->specialist;
            $record['linked_profile'] = $list->linked_profile ? $list->linked_profile:'--';
            if ((is_siteadmin() || has_capability('local/events:manage', context_system::instance()) || has_capability('local/organization:manage_event_manager', context_system::instance()))) {
                $record['action'] = true;
            }
            $row[] = $record;
        }
        return array_values($row);
    }

    public function list_agenda($stable, $filterdata=null) {
        global $USER, $CFG, $DB;
        $systemcontext = context_system::instance();
        $getagenda = events::get_listof_agenda($stable, $filterdata);
        $agenda = array_values($getagenda['agenda']);
        $row = array();
        $stable = new \stdClass();
        $stable->thead = true;
        $count = 0;
        foreach ($agenda as $list) {
            $record = array();
            $record['id'] = $list->id;
            $record['eventid'] = $list->eventid;
            $record['title'] = $list->title;
            $event = events::get_agenda_dates($list->eventid);
            $day = strtotime($event[$list->day]);
            $record['day'] = userdate($day, get_string('strftimedatemonthabbr', 'core_langconfig')); //date('d M Y', strtotime($day));
            if ($list->speaker == 0) {
                $name = get_string('others','local_events');
            } else {
                $name = $DB->get_field_select('local_speakers', 'name', " CONCAT(',',$list->speaker,',') LIKE CONCAT('%,',id,',%') ", array());//FIND_IN_SET(id, '$sdata->department')
            }
            $record['speaker'] = $name;
            $starttime = strtotime($list->timefrom);
            $timefrom = userdate($starttime, get_string('strftimetime12', 'core_langconfig'));
            $endtime =  strtotime($list->timeto);
            $timeto = userdate($endtime, get_string('strftimetime12', 'core_langconfig'));
            $record['time'] = $timefrom. ' - ' .$timeto;
            if ((is_siteadmin() || has_capability('local/events:manage', context_system::instance()) || has_capability('local/organization:manage_event_manager', context_system::instance()))) {
                $record['action'] = true;
            }
            $row[] = $record;
        }
        return array_values($row);
    }
    public function get_events($filter = false)
    {
        $systemcontext = context_system::instance();
        $options = array('targetID' => 'programs_container','perPage' => 6, 'cardClass' => 'col-md-6 col-12', 'viewType' => 'card');
        $options['methodName']='local_events_viewevents';
        $options['templateName']='local_events/eventcard';
        $options = json_encode($options);
        $filterdata = json_encode(array());
        $dataoptions = json_encode(array('contextid' => $systemcontext->id));
        $context = [
                'targetID' => 'programs_container',
                'options' => $options,
                'dataoptions' => $dataoptions,
               'filterdata' => $filterdata,
        ];
        if($filter){
            return  $context;
        }else{
            return  $this->render_from_template('theme_academy/cardPaginate', $context);
        }
    }

    public function listof_events($filterparams){
        global $DB, $PAGE, $OUTPUT;
        $eventsmform = events_front_filters_form($filterparams);
        $filterparams['filterform'] = $eventsmform->render();
        echo $this->render_from_template('local_events/events', $filterparams);
    }

    public function list_attendees($stable, $filterdata=null) {
        global $USER, $CFG, $DB;
        $systemcontext = context_system::instance();
        $getattendee = events::get_listof_attendees($stable, $filterdata);
        $attendee = array_values($getattendee['attendee']);
        $row = array();
        $stable = new \stdClass();
        $stable->thead = true;
        $count = 0;
        $lang= current_language();
        foreach ($attendee as $list) {
            $record = array();
            if (!empty($list->userid)) {
                $user = $DB->get_record('local_users', ['userid' => $list->userid]);
                $fullname = ($lang == 'ar') ?  $user->firstnamearabic.' '.$user->middlenamearabic.' '.$user->thirdnamearabic.' '.$user->lastnamearabic :  $user->firstname.' '.$user->middlenameen.' '.$user->thirdnameen.' '.$user->lastname ;
                $organization = $user->organization?(($lang == 'ar')?$DB->get_field('local_organization','fullnameinarabic',array('id'=>$user->organization)):$DB->get_field('local_organization','fullname',array('id'=>$user->organization))):'';
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
                $fullname =  $list->name;
            }
            $record['approvedstatus'] = $approvedstatus;
            $record['organization'] = $organization;
            $record['attenddeid'] = $attenddeid;
            $record['id'] = $list->id;
            $record['userid'] = $list->userid;
            $record['eventid'] = $list->eventid;
            $record['name'] = $fullname;
            $record['email'] = $list->email;
            $record["status"] = $approvedstatus;
            $record["linkedprofile"] = '--';
            $record['action'] = false;
            $record['delete'] = false;
            $eventrecord = $DB->get_record('local_events',['id'=>$list->eventid]);
            $productid =(int) $DB->get_field_sql('SELECT tlp.id FROM {tool_products} tlp 
            JOIN {local_events} loe ON loe.code = tlp.code 
            WHERE tlp.category =:category AND tlp.referenceid =:referenceid',['category'=>3,'referenceid'=>$list->eventid]);
            $remainingdays = floor(($eventrecord->startdate - time()) / (60 * 60 * 24));
            $record["productid"] =($productid) ? $productid : 0;
            $record["eventprice"] = $eventrecord->price;
            $record["sellingprice"] = $eventrecord->sellingprice;
            $record["id_number"] = $DB->get_field('user','idnumber',['id'=>$list->userid]);
            $record["replacementfee"] = 100;
            $record["currentuserisadmin"] = (is_siteadmin() ||  has_capability('local/organization:manage_event_manager',$systemcontext)) ? 1 : 0;
            $record["eventname"] = (current_language() == 'ar') ? $eventrecord->titlearabic : $eventrecord->title;
            $record["eventstartdate"] = ($eventrecord->startdate > 0 )? $eventrecord->startdate: 0 ;
            $record["remainingdays"] = ($eventrecord->price == 1  && $eventrecord->startdate > 0 )?$remainingdays: 0 ;
            $record["replacebuttonview"] = ($eventrecord->price == 0 || ($eventrecord->price == 1  && $remainingdays >= 2)) ? true: false;
            $record["cancelbuttonview"] = ($eventrecord->price == 0 || ($eventrecord->price == 1  && $eventrecord->startdate > 0 )) ? true: false;
            $enrolleduserid =(int) $DB->get_field('local_event_attendees','usercreated',['eventid'=>$list->eventid,'userid'=>$list->userid]);
            $record["currentuserorgoff"] = (!is_siteadmin() && has_capability('local/organization:manage_organizationofficial',$systemcontext)) ? 1 : 0;              
            $enrolleduseroleinfo = $DB->get_record_sql('SELECT rol.* FROM {role} rol 
                                                    JOIN {role_assignments} rola ON rola.roleid = rol.id
                                                    WHERE rola.userid =:userid and contextid =:contextid',['userid'=>$enrolleduserid,'contextid'=>$systemcontext->id]);
            $record["orgofficialenrolled"] =($enrolleduseroleinfo->shortname == 'organizationofficial') ? 1 : 0;
            $record["enrolledrole"]=(empty($enrolleduseroleinfo->shortname) ||  $enrolleduseroleinfo->shortname =='em') ?  'admin' : $enrolleduseroleinfo->shortname ;
            if ((is_siteadmin() || has_capability('local/events:manage', $systemcontext) || has_capability('local/organization:manage_event_manager', $systemcontext))) {
                $record['action'] = true;
            }
            if(!is_siteadmin() && has_capability('local/organization:manage_organizationofficial',$systemcontext)) {
                $record['delete'] = true;
            }
          
            $certid = $DB->get_field('tool_certificate_issues', 'code', array('moduleid' => $list->eventid, 'userid' => $list->userid, 'moduletype' => 'events'));
            $record['certificateid'] = !empty($certid) ? $certid : 0;  
            $record['certificateurl'] = !empty($certid) ? $CFG->wwwroot.'/admin/tool/certificate/view.php?code='.$certid : '#';
            if (!is_siteadmin() && has_capability('local/organization:manage_organizationofficial',$systemcontext) &&  $enrolleduseroleinfo->shortname == 'trainee') {
                $record['disableallactions'] = true;
            } else {
                $record['disableallactions'] = false;
            }
            $record['iswaitingforapproval'] = ($list->enrolstatus == 0)? true :false;
            $row[] = $record;
        }
        return array_values($row);
    }

    public function event_check($id) {
        global $DB, $OUTPUT;
        $lang= current_language();

        if( $lang == 'ar'){
            $event = $DB->get_field('local_events', 'titlearabic', ['id' => $id]);
        }else{
            $event = $DB->get_field('local_events', 'title', ['id' => $id]);
        }       
        if (empty($event)) {
            echo $OUTPUT->notification(get_string('eventnotfounf', 'local_events'),'danger');
            //throw new moodle_exception("Event Not Found!");
        }
        return $event;
    }

    public function global_filter($filterparams) {
        global $DB, $PAGE, $OUTPUT;
        return $this->render_from_template('theme_academy/global_filter', $filterparams);;
    }

    public function listofsponsor($filterparams) {
        global $DB, $PAGE, $OUTPUT;
        if(is_siteadmin() || has_capability('local/events:manage', context_system::instance()) 
        || has_capability('local/organization:manage_event_manager', context_system::instance())){
            $filterparams['action'] = true;
        }
        echo $this->render_from_template('local_events/viewsponsor', $filterparams);
    }

    public function listofagenda($filterparams) {
        global $DB, $PAGE, $OUTPUT;
        if(is_siteadmin() || has_capability('local/events:manage', context_system::instance())
            || has_capability('local/organization:manage_event_manager', context_system::instance())){
            $filterparams['action'] = true;
        }
        echo $this->render_from_template('local_events/viewagenda', $filterparams);
    }

    public function listofspeaker($filterparams) {
        global $DB, $PAGE, $OUTPUT;
        if(is_siteadmin() || has_capability('local/events:manage', context_system::instance())
            || has_capability('local/organization:manage_event_manager', context_system::instance())){
            $filterparams['action'] = true;
        }
        echo $this->render_from_template('local_events/viewspeaker', $filterparams);
    }

    public function listoffinance($filterparams) { 
        global $DB, $PAGE, $OUTPUT;
        if(is_siteadmin() || has_capability('local/events:manage', context_system::instance())
        || has_capability('local/organization:manage_event_manager', context_system::instance())){
            $filterparams['action'] = true;
        }
        echo $this->render_from_template('local_events/finance_list', $filterparams);
    }

    public function manage_capability() {
        $systemcontext = context_system::instance();
        if(is_siteadmin() || has_capability('local/events:manage', $systemcontext)  
         || has_capability('local/organization:manage_event_manager', $systemcontext) ){
           return true;
        } else {
            print_error(get_string('permissionerror', 'local_events'));
        }
    }

    public function listofpartners($filterparams) { 
        global $DB, $PAGE, $OUTPUT;
        if(is_siteadmin() || has_capability('local/events:manage', context_system::instance())
        || has_capability('local/organization:manage_event_manager', context_system::instance())){
            $filterparams['action'] = true;
        }
        echo $this->render_from_template('local_events/viewpartner', $filterparams);
    }

    public function get_total_estimated($eventid) {
        global $DB;
       
        $logistic = $DB->get_field_sql("SELECT SUM(lef.logistic) FROM {local_event_finance} lef WHERE lef.eventid = $eventid");

        $speakersum_sql = " SELECT SUM(les.actualprice) FROM {local_event_speakers} les WHERE les.eventid = $eventid";
        $speaker_sum = $DB->get_field_sql($speakersum_sql);

        $totalincome = $DB->get_field_sql("SELECT SUM(lef.amount) FROM {local_event_finance} lef WHERE lef.eventid = $eventid AND lef.type = 1");
        if($logistic) {
            $total_estimated['logistics'] = $logistic;
        } else {
            $total_estimated['logistics'] = "0";
        }
        if($speaker_sum) {
            $total_estimated['speakers'] = $speaker_sum;
        } else {
            $total_estimated['speakers'] = "0";
        }
        if($totalincome) {
            $total_estimated['totalincome'] = $totalincome;
        } else {
            $total_estimated['totalincome'] = "0";
        }
        return $this->render_from_template('local_events/total_estimated', $total_estimated);
    }

    public function get_actual_revenue($eventid) {
        global $DB;
        $total_estimated = [];
        $logistic =  $DB->get_field_sql("SELECT SUM(lef.logistic) FROM {local_event_finance} lef WHERE lef.eventid = $eventid");
        $speakersum_sql = " SELECT SUM(les.actualprice) FROM {local_event_speakers} les WHERE les.eventid = $eventid";
        $speaker_sum = $DB->get_field_sql($speakersum_sql);
        $totalincome = $DB->get_field_sql("SELECT SUM(lef.amount) FROM {local_event_finance} lef WHERE lef.eventid = $eventid AND lef.type = 1");
        if($logistic) {
            $total_estimated['logistics'] = $logistic;
        } else {
            $total_estimated['logistics'] = "0";
        }
        if($speaker_sum) {
            $total_estimated['speakers'] = $speaker_sum;
        } else {
            $total_estimated['speakers'] = "0";
        }
        if($totalincome) {
            $total_estimated['totalincome'] = $totalincome;
        } else {
            $total_estimated['totalincome'] = "0";
        }
        $total_estimated['revenue'] = $logistic + $speaker_sum - (0);
        
        return $this->render_from_template('local_events/actual_revenue', $total_estimated);
    }

    public function get_total_expenses($eventid) {
        global $DB;
        $total_expenses = [];
        $logistic = $DB->get_field_sql("SELECT SUM(lef.logistic) FROM {local_event_finance} lef WHERE lef.eventid = $eventid AND lef.type = 2");
        $expense_sql = " SELECT SUM(lef.amount) FROM {local_event_finance} lef WHERE lef.eventid = $eventid AND lef.type = 2";
        $totalexpenses = $DB->get_field_sql($expense_sql);
        if($logistic) {
            $total_expenses['logistics'] = $logistic;
        } else {
            $total_expenses['logistics'] = "0";
        }
        if($totalexpenses) {
            $total_expenses['totalexpenses'] = $totalexpenses;
        } else {
            $total_expenses['totalexpenses'] = "0";
        }
        return $this->render_from_template('local_events/total_expenses', $total_expenses);
    }

    public function get_eventscontent($eventid){
        global $DB, $CFG, $OUTPUT, $PAGE, $USER;
        $eventsql = " SELECT  e.* FROM {local_events} e WHERE e.id = $eventid";
        $event = $DB->get_record_sql($eventsql);
        $eventContent = [];
        if ($event) {

            list($usersql,$userparams) = $DB->get_in_or_equal(explode(',',$event->eventmanager));
            $querysql = "SELECT * FROM {user} WHERE id $usersql";
            $managers= $DB->get_records_sql($querysql,$userparams);

            foreach ($managers AS $manager) {

                $localuserrecord = $DB->get_record('local_users',['userid'=>$manager->id]);

                $manager->id =$manager->id;
                $manager->name =($localuserrecord)? (( $localuserrecord->lang == 'ar') ? $localuserrecord->firstnamearabic.' '.$localuserrecord->middlenamearabic.' '.$localuserrecord->thirdnamearabic.' '.$localuserrecord->lastnamearabic  : $localuserrecord->firstname.' '.$localuserrecord->middlenameen.' '.$localuserrecord->thirdnameen.' '.$localuserrecord->lastname) :fullname($manager);
                
           } 
            if(!empty($managers)){
                $event->manager = array_values($managers);
            } else {
                $event->manager = array();
            }
            if($event->eventmanager) {
                $fullname = (new \local_trainingprogram\local\trainingprogram)->user_fullname_case();
                $users = $DB->get_records_sql_menu(" SELECT u.id, $fullname
                FROM {user} u JOIN {local_users} lc ON lc.userid = u.id JOIN {local_events} e ON concat(',', e.eventmanager, ',') LIKE concat('%,',u.id,',%')
                WHERE e.id = :eventid",['eventid' => $eventid]);
                if($users) {
                    $manager = implode(', ',$users);
                } else {
                    $manager = '';
                }
                $event->eventmanager = $manager;
            }
            $languages = explode(',',$event->language);
            $actuallang =array();
            foreach ( $languages AS $language) {
             $actuallang[]=($language == '1') ? get_string('arabic','local_trainingprogram') : get_string('english','local_trainingprogram');
            }  
            $event->language = $actuallang ? implode(',',$actuallang) :'-';

            $genderarray = array(1 => get_string('male','local_events'),
            2 => get_string('female','local_events'));
            $genders = explode(',', $event->audiencegender);
            $audiencegender = [];
            foreach($genders as $gender) {
                $audiencegender[] = $genderarray[$gender];
            }
            $event->gender = implode(', ',$audiencegender);
           
             if (!is_null($event->logo) &&  $event->logo > 0) {
                $eventimg = logo_url($event->logo);
                if($eventimg == false){
                    $eventimg = (new events)->eventdefaultimage_url();
                }
            }else{
                $eventimg = (new events)->eventdefaultimage_url();
            }
            $event->logo = $eventimg;
            if (empty($event->description)) {
                $iseventdescription = false;
            } else {
                $iseventdescription = true;
                $eventdescription = format_text($event->description, FORMAT_MOODLE);
            }
            $event->description = format_text($event->description, FORMAT_HTML);

            $starttimemeridian = gmdate('a',$event->slot);
            $endtimemeridian = gmdate('a',($event->slot + $event->eventduration));

            $regstarttimemeridian = date('a',$event->registrationstart);
            $regendtimemeridian = date('a',($event->registrationend));
           
            $eventstarttime = gmdate("h:i",$event->slot);
            $eventendttime = gmdate("h:i",($event->slot + $event->eventduration));
            $regstarttime = date("h:i",$event->registrationstart);
            $regendttime = date("h:i",($event->registrationend));
            

            $eventstartmeridian = ($starttimemeridian == 'am')?  get_string('am','local_trainingprogram'):get_string('pm','local_trainingprogram');
            $eventendmeridian = ($endtimemeridian == 'am')?  get_string('am','local_trainingprogram'):get_string('pm','local_trainingprogram');
             
            $regstartmeridian = ($regstarttimemeridian == 'am')?  get_string('am','local_trainingprogram'):get_string('pm','local_trainingprogram');
            $regendmeridian = ($regendtimemeridian == 'am')?  get_string('am','local_trainingprogram'):get_string('pm','local_trainingprogram');
            
            $event->startdate = userdate($event->startdate, get_string('strftimedatemonthabbr','core_langconfig')).' '.$eventstarttime.' '.$eventstartmeridian;//. ' @ ' .$event_starttime.' '.$get_time_lang['startmeridian'];
            $event->enddate = userdate($event->enddate, get_string('strftimedatemonthabbr', 'core_langconfig')).' '.$eventendttime.' '.$eventendmeridian;//. ' @ ' .$event_endttime.' '.$get_time_lang['startmeridian'];
        
            $reg_starttime = userdate($event->registrationstart, get_string('strftimetime12','core_langconfig'));
            $reg_endttime = userdate($event->registrationend, get_string('strftimetime12','core_langconfig'));
            // $get_time_lang = (new events())->time_lang_change($starttimemeridian, $endtimemeridian);

            $event->reg_startdate = userdate($event->registrationstart, get_string('strftimedate', 'core_langconfig')).' @ '.$regstarttime.' '.$regstartmeridian;
            $event->reg_enddate = userdate($event->registrationend, get_string('strftimedate', 'core_langconfig')).' @ '.$regendttime.' '.$regendmeridian;

            $event->reg_starttime = $reg_starttime;
            $event->reg_endttime = $reg_endttime;

            $geteventtype = (new events())->get_event_type($event->type);
            $event->type = $geteventtype;

            $statusarray = array(0 => get_string('active', 'local_events'),
            1 => get_string('inactive', 'local_events'),
            2 => get_string('cancelled', 'local_events'),
            3 => get_string('closed', 'local_events'),
            4 => get_string('archieved', 'local_events'));
           // $event->status = $statusarray[$event->status];
            $event->halldata = array();
            $hallslist = (new events)->event_hallinfo($event->id);
            if ($hallslist) {
                $hallslimit = false;
                $event->halldata = $hallslist;
            }
            if(count($hallslist) > 2){
                $hallslimit = false;
                $event->morehalls = array_slice($event->halldata, 0, 2);
            }else{
                $hallslimit = true;
                $event->morehalls = $event->halldata;
            }
            $event->hallslimit = $hallslimit;

            $event->platinum_sponsordata = array();
            $event->gold_sponsordata = array();
            $event->silver_sponsordata = array();
            //Platinum 
            $platinum_sporslist = (new events)->event_sponsorsinfo($event->id, 0);
            if($platinum_sporslist) {
                $event->platinum_sponsordata = $platinum_sporslist;
            }
            //Gold
            $gold_sporslist = (new events)->event_sponsorsinfo($event->id, 1);
            if($gold_sporslist) {
                $event->gold_sponsordata = $gold_sporslist;
            }
            //Silver
            $silver_sporslist = (new events)->event_sponsorsinfo($event->id, 2);
            if($silver_sporslist) {
                $event->silver_sponsordata = $silver_sporslist;
            }

            $event->partnerdata = array();
            $partnerslist = (new events)->event_partnersinfo($event->id);
            if($partnerslist) {
                $event->partnerdata = $partnerslist;
            }
            $event->agendadata = array();
            $agendalist = (new events)->event_agendainfo($event->id); 
            if($agendalist) {
                $event->agendadata = $agendalist;
            }
            $days_between = events::get_event_reg_dates($event->id);
            $available_seats = (new events)->events_available_seats($event->id);

            $event_reg_end = $event->registrationend;
            $today_datetime = time();


            if($event_reg_end > $today_datetime) {
                $event_reg_status = true; 
            } else {
                $event_reg_status = false;
            }
            if($event->method == 0 && $available_seats['availableseats'] <= 0) {
                $bookingseats = false;
            } else {
                $bookingseats = true;
            }
            $event_reg_start = $event->registrationstart;

            if($today_datetime > $event_reg_start) {
                $event_reg_opening = true;
            } else {
                $event_reg_opening = false;
            }
            /*if($event->registrationstart <= time() && $event->registrationend >= $event->registrationstart) {
                $event_reg_status = 1;
            } else {
                $event_reg_status = 0;
            }*/
            if($event->startdate >= time() && $event->enddate >= time() && $event->registrationstart <= time()){
                $event_status = 1;
            }else{
                $event_status = 0;
            }
            $event->attendee_list = $DB->count_records('local_event_attendees',['eventid'=> $event->id]);
            $event->method = ($event->method == 0)?get_string('inclass','local_events'):get_string('virtual','local_events');
            $lang= current_language();
            if( $lang == 'ar' && !empty($event->titlearabic)){
                $event->eventname = $event->titlearabic;
            }else{
                $event->eventname = $event->title;
            }

            $event->duration = $event->eventduration;
            $event->status = (($event->enddate+$event->slot+$event->eventduration) >=time())?get_string('active','local_events'):get_string('inactive','local_events');
            $systemcontext = context_system::instance();
          $isorgofficial=(!is_siteadmin() && has_capability('local/organization:manage_organizationofficial',$systemcontext)) ? true : false;
                $istrainee=(!is_siteadmin() && (has_capability('local/organization:manage_trainee',$systemcontext)));   
                if($isorgofficial || $istrainee){
                 $isorgofficialortrainee= true;
                 $userid=$USER->id;
                 }else{
                  $isorgofficialortrainee= false;
                 }
                 $component='local_events';
                 $checkfavornot =(new exams)->checkfavourites($event->id,$USER->id,$component);          
            $eventContent = [
                  'isorgofficialortrainee'=>$isorgofficialortrainee,
                  'checkfavornot'=>$checkfavornot,
                  'userid'=>$userid,
               // 'eventname' => $eventname,
                'event' => $event,
                'eventid' => $event->id,
               // 'eventlogo' => $eventimg,
               // 'eventstatus' =>  $eventstatus,
                'eventdescription' => $eventdescription,
                'iseventdescription' => $iseventdescription,
               // 'eventtype' => $eventtype,
                'estimatedbudget' => $event->logisticsestimatedbudget,
                'actualbudget' => $event->logisticsactualbudget,
               // 'eventgender' => $eventgender,
               // 'eventlanguage' => $eventlanguage,
                //'attendee_list' => $attendee_list,
                'event_status' => $event_status,
                //'eventstartdate' => $eventstartdate,
                //'eventenddate' => $eventenddate,
                'available_seats' => $available_seats['availableseats'],
                'event_sellingprice' => ($event->sellingprice > 0)?number_format($event->sellingprice):false,
                'event_reg_status' => $event_reg_status,
                'event_reg_opening' => $event_reg_opening,
                'bookingseats' => $bookingseats,
                'scheduleurl' =>  new moodle_url("/local/events/exportpdf.php?id=".$event->id.""),
                'product_attributes' => (new \tool_product\product)->get_product_attributes($event->id, 3, 'addtocart', false),
            ];
            // print_object($eventContent); exit;
            //return $this->render_from_template('local_events/eventview', $eventContent);
        }
        return $eventContent;
    }

    public function get_eventinfo($eventid,$mlang = NULL){
        global $DB, $CFG, $OUTPUT, $PAGE,$SESSION;
        $SESSION->lang = ($mlang) ? $mlang : current_language() ;
        $systemcontext = context_system::instance();
        $eventsql = " SELECT  e.* FROM {local_events} e WHERE e.id = $eventid";
        $event = $DB->get_record_sql($eventsql);
        $eventContent = [];
        if ($event) {
            $starttime = userdate(mktime(0, 0, $event->slot), '%H:%M');
            $endttime = userdate(mktime(0, 0, ($event->slot + $event->eventduration)), '%H:%M');
            $current_time =  userdate(time(), '%H:%M');

            $event->joinurl = '';
            if(is_siteadmin() || has_capability('local/organization:manage_event_manager', context_system::instance()) || has_capability('local/organization:manage_trainee', context_system::instance())) {
                if($event->method == 1) {
                    $days_between = events::get_agenda_dates($event->id);

                    if (in_array(userdate(time(), '%Y-%m-%d'), $days_between) && $starttime <= $current_time && $endttime >= $current_time) {
                        $launchbtn = true; 
                        //$launchbtn = true;
                        if($event->virtualtype == 1) {
                            $zoom_url = $DB->get_field('zoom',' join_url',['id' => $event->zoom]);
                            $event->joinurl = !empty($zoom_url)?$zoom_url:0;
                        } else if($event->virtualtype == 2) {
                            $coursemoduleid =  get_coursemodule_from_instance('webexactivity',$event->webex, 1);
                            $urlobj = new moodle_url('/mod/webexactivity/view.php', array('id' => $coursemoduleid->id, 'action' => 'joinmeeting'));
                            $webexurl =  $urlobj->out(false);
                            $event->joinurl = !empty($webexurl)?$webexurl:0;
                        } elseif($event->virtualtype == 3) {
                            $teams_url = $DB->get_field('teamsmeeting',' meetingurl',['id' => $event->teams]);
                            $event->joinurl = !empty($teams_url) ? $teams_url : 0;
                        }
                    } else {
                        $launchbtn = false;
                        $event->joinurl = '';
                    }
                }

                if($event->method == 0) {
                    $event->showhall = true; 
                }
            }

            $event->bookseats = false;
            if(!is_siteadmin() && has_capability('local/organization:manage_organizationofficial', $systemcontext)) {
                $days_between = (new events)->get_event_reg_dates($event->id);
                if($days_between) {
                    $eventseats = (new events)->events_available_seats($event->id);
                    $event->bookseats = true;
                }
            }
            $langarray = array(1 => get_string('arabic','local_events'),
            2 => get_string('english','local_events'));
            $languages = explode(',', $event->language);
            $eventlanguage = [];
            foreach($languages as $language) {
                $eventlanguage[] = $langarray[$language];
            }
            $event->language = implode(',', $eventlanguage);
            list($usersql,$userparams) = $DB->get_in_or_equal(explode(',',$event->eventmanager));
            $querysql = "SELECT * FROM {user} WHERE id $usersql";
            $managers= $DB->get_records_sql($querysql,$userparams);

            foreach ($managers AS $manager) {

                $localuserrecord = $DB->get_record('local_users',['userid'=>$manager->id]);

                $manager->id =$manager->id;
                $manager->name =($localuserrecord)? ((current_language() == 'ar') ? $localuserrecord->firstnamearabic.' '.$localuserrecord->middlenamearabic.' '.$localuserrecord->thirdnamearabic.' '.$localuserrecord->lastnamearabic  : $localuserrecord->firstname.' '.$localuserrecord->middlenameen.' '.$localuserrecord->thirdnameen.' '.$localuserrecord->lastname) :fullname($manager);
                
           } 
            if(!empty($managers)){
                $event->manager = array_values($managers);
            } else {
                $event->manager = array();
            }
            if($event->eventmanager) {
               $fullname = (new \local_trainingprogram\local\trainingprogram)->user_fullname_case();
                $users = $DB->get_records_sql_menu(" SELECT u.id, $fullname
                FROM {user} u JOIN {local_users} lc ON lc.userid = u.id JOIN {local_events} e ON concat(',', e.eventmanager, ',') LIKE concat('%,',u.id,',%')
                WHERE e.id = :eventid",['eventid' => $eventid]);
                if($users) {
                    $manager = implode(', ',$users);
                } else {
                    $manager = '';
                }
                $event->eventmanager = $manager;
            }
           
            $event->method = ($event->method == 0)?get_string('inclass','local_events'):get_string('virtual','local_events');
            $genderarray = array(1 => get_string('male','local_events'),
            2 => get_string('female','local_events'));
            $genders = explode(',', $event->audiencegender);
            $audiencegender = [];
            foreach($genders as $gender) {
                $audiencegender[] = $genderarray[$gender];
            }
            $event->gender = implode(', ',$audiencegender);
           
             if (!is_null($event->logo) &&  $event->logo > 0) {
                $eventimg = logo_url($event->logo);
                if($eventimg == false){
                    $eventimg = (new events)->eventdefaultimage_url();
                }
            }else{
                $eventimg = (new events)->eventdefaultimage_url();
            }
            $event->logo = $eventimg;
            if (empty($event->description)) {
                $iseventdescription = false;
            } else {
                $iseventdescription = true;
                $eventdescription = format_text($event->description, FORMAT_HTML);
            }
            $event->description = strip_tags(format_text($event->description, FORMAT_HTML));
            $event->startdate = $event->startdate;
            $event->enddate = $event->enddate;
            $starttimemeridian = date('a',$event->registrationstart); 
            $endtimemeridian = date('a',$event->registrationend); 
        
            $reg_starttime = date('h:i', $event->registrationstart);
            $reg_endttime = date("h:i",$event->registrationend);
            $get_time_lang = (new events())->time_lang_change($starttimemeridian, $endtimemeridian);

    
            $event->reg_startdate = userdate($event->registrationstart, get_string('strftimedatemonthabbr', 'core_langconfig')). ' @ ' .$reg_starttime.' '.$get_time_lang['startmeridian'];//userdate($event->registrationstart, get_string('strftimetime12', 'core_langconfig'));
            $event->reg_enddate = userdate($event->registrationend, get_string('strftimedatemonthabbr', 'core_langconfig')). ' @ ' .$reg_endttime. ' '.$get_time_lang['endmeridian'];//userdate($event->registrationend, get_string('strftimetime12', 'core_langconfig'));

            $event->reg_starttime = $reg_starttime.' '.$get_time_lang['startmeridian'];
            $event->reg_endttime = $reg_endttime.' '.$get_time_lang['startmeridian'];

            $geteventtype = (new events())->get_event_type($event->type);
            $event->type = $geteventtype;

            $statusarray = array(0 => get_string('active', 'local_events'),
            1 => get_string('inactive', 'local_events'),
            2 => get_string('cancelled', 'local_events'),
            3 => get_string('closed', 'local_events'),
            4 => get_string('archieved', 'local_events'));
           // $event->status = $statusarray[$event->status];
            $event->halldata = array();
            $hallslist = (new events)->event_hallinfo($event->id);
            if ($hallslist) {
                $hallslimit = false;
                $event->halldata = $hallslist;
            }
            if(count($hallslist) > 2){
                $hallslimit = false;
                $event->morehalls = array_slice($event->halldata, 0, 2);
            }else{
                $hallslimit = true;
                $event->morehalls = $event->halldata;
            }
            $event->hallslimit = $hallslimit;

            $event->platinum_sponsordata = array();
            $event->gold_sponsordata = array();
            $event->silver_sponsordata = array();
            //Platinum 
            $platinum_sporslist = (new events)->event_sponsorsinfo($event->id, 0);
            if($platinum_sporslist) {
                $event->platinum_sponsordata = $platinum_sporslist;
            }
            //Gold
            $gold_sporslist = (new events)->event_sponsorsinfo($event->id, 1);
            if($gold_sporslist) {
                $event->gold_sponsordata = $gold_sporslist;
            }
            //Silver
            $silver_sporslist = (new events)->event_sponsorsinfo($event->id, 2);
            if($silver_sporslist) {
                $event->silver_sponsordata = $silver_sporslist;
            }

            $event->partnerdata = array();
            $partnerslist = (new events)->event_partnersinfo($event->id);
            if($partnerslist) {
                $event->partnerdata = $partnerslist;
            }
            $event->agendadata = array();
            $agendalist = (new events)->event_agendainfo($event->id); 
            if($agendalist) {
                $event->agendadata = $agendalist;
            }
            $days_between = events::get_event_reg_dates($event->id);
            $available_seats = (new events)->events_available_seats($event->id);
        
            if($days_between && $event->status==0) {
                $event_reg_status = true; 
            } else {
                $event_reg_status = false;
            }
            if($event->method == 0 && $available_seats['availableseats'] <= 0) {
                $bookingseats = false;
            } else {
                $bookingseats = true;
            }

           /* $event_reg_start = userdate($event->registrationstart,get_string('strftimedatetime', 'core_langconfig'));

            $today_datetime = userdate(time(),get_string('strftimedatetime', 'core_langconfig'));*/
            

            $today_datetime = time();

            if($event->registrationstart > $today_datetime) {
                $event_reg_opening = false;
            } else {
                $event_reg_opening = true;
            }
            if($event->startdate >= time() && $event->enddate >= time() && $event->registrationstart <= time()){
                $event_status = 1;
            }else{
                $event_status = 0;
            }
            $event->attendee_list = $DB->count_records('local_event_attendees',['eventid'=> $event->id]);
            $lang= current_language();
            if( $lang == 'ar' || $SESSION->lang == 'ar' && !empty($event->titlearabic)){
                $event->eventname = $event->titlearabic;
            }else{
                $event->eventname = $event->title;
            }

            $event->duration = $event->eventduration;
            $event->product_attributes = (new \tool_product\product)->get_product_attributes($event->id, 3, 'addtocart', false);
            $event->available_seats =$available_seats['availableseats'];

            return $event;
          
        }
    }

    

    public function hascapability() {
        global $PAGE;
        $systemcontext = context_system::instance();
        if(is_siteadmin() || has_capability('local/organization:manage_event_manager', $systemcontext)) {
            $this->page->set_title(get_string('pluginname','local_events'));
            $this->page->set_heading(get_string('eventdetails','local_events'));
            $this->page->navbar->add(get_string('manage', 'local_events'), new moodle_url('/local/events/index.php'));
        } else if (!is_siteadmin() && has_capability('local/organization:manage_organizationofficial', $systemcontext) || has_capability('local/organization:manage_communication_officer', $systemcontext)) {
            $this->page->set_title(get_string('pluginname','local_events'));
            $this->page->set_heading(get_string('eventdetails','local_events'));
            $this->page->navbar->add(get_string('pluginname', 'local_events'), new moodle_url('/local/events/index.php'));
        } else {
            $this->page->set_title(get_string('myevents', 'local_events'));
            $this->page->set_heading(get_string('eventdetails','local_events'));
            $this->page->navbar->add(get_string('myevents', 'local_events'), new moodle_url('/local/events/index.php'));
        }
    }
}
