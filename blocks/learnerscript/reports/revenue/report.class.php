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
 * @author: eAbyas Info Solutions
 * @date: 2017
 */
namespace block_learnerscript\lsreports;
use block_learnerscript\local\reportbase;
use block_learnerscript\local\querylib;
use block_learnerscript\local\ls as ls;
use local_events\events as events;
use local_exams\local\exams;
use local_trainingprogram\local\trainingprogram as trainingprogram;
use context_system;
use stdClass;
use html_writer;
class report_revenue extends reportbase{
    /**
     * [__construct description]
     * @param [type] $report           [description]
     * @param [type] $reportproperties [description]
     */
    public function __construct($report, $reportproperties) {
        parent::__construct($report);
        $this->parent = false;
        $this->courselevel = true;
        $this->components = array('columns', 'filters', 'permissions', 'plot');
        $columns = ['id','paymentdate','activityprice','vat','discount','amount','operation','transactiontype','idnumber','username','trainer','activitytype','trainingtype','programtype','activityname','activitycode','activitystartdate','activityenddate','activitystatus','organizationname','organizationcode','paymenttype','enrolledby','billnumber','paymentstatus','userid','activityid','transactionid','organizationid'];
        $this->columns = ['revenuecolumns' => $columns];
        $this->searchable = array();
        $this->orderable = array();
        $this->filters = array('paymentdate','idnumber','orgcode','organization','transactiontype','operation','learningtype','paymenttype');
        $this->defaultcolumn = 'tuop.id';
         $this->excludedroles = array("'manager', 'coursecreator', 'editingteacher', 'teacher', 'student', 'examofficial', 'em', 'co', 'to', 'expert', 'trainer','competencies_official', 'cpd', 'hall_manager','organizationofficial','trainee'");
    }
    function init() {
        global $DB;
      
    }

    function count() {
        global $DB;
       $this->sql = "SELECT COUNT(DISTINCT tuop.id) ";

    }

    function select() {
        $this->sql = " SELECT DISTINCT tuop.id,tuop.*, tpt.id AS tptid,tpt.transactiontype,tpt.transactioncode,tpt.transactionref, tpt.productdata, tp.referenceid, tp.category,tuop.purchasedseats";
        parent::select();
      }

    function from() {
         $this->sql .= " FROM {tool_user_order_payments} tuop ";
    }

    function joins() {
        $this->sql .= " JOIN {tool_products} tp ON tp.id = tuop.productid
                        LEFT JOIN {tool_product_telr} tpt ON tpt.id = tuop.telrid";
        parent::joins();
    }

    function where() { 
       global $USER;
       
        $this->sql .= "  WHERE 1=1 AND (tuop.userid IN(SELECT ee.usercreated FROM {exam_enrollments} ee WHERE ee.userid = ee.usercreated AND tp.referenceid =ee.profileid ) OR tuop.userid IN(SELECT pe.usercreated FROM {program_enrollments} pe WHERE pe.userid = pe.usercreated AND tp.referenceid =pe.offeringid) OR tuop.userid IN(SELECT ea.usercreated FROM {local_event_attendees} ea WHERE ea.userid = ea.usercreated AND tp.referenceid =ea.eventid))";
        
        $systemcontext = context_system::instance();
        parent::where();

    }
    

    function search() {
        global $DB;
         if (isset($this->search) && $this->search) {
            $statsql = array();
            foreach ($this->searchable as $key => $value) {
                $statsql[] =$DB->sql_like($value, "'%" . $this->search . "%'",$casesensitive = false,$accentsensitive = true, $notlike = false);
            }
            $fields = implode(" OR ", $statsql);          
            $this->sql .= " AND ($fields) ";
        }
    }

    function filters() {
        global $DB;
         if(isset($this->params['paymentfrom']) && ($this->params['paymentfrom']['enabled'] == 1)){
            $paymentfrom = $this->params['paymentfrom'];
            $start_year = $paymentfrom['year'];
            $start_month = $paymentfrom['month'];
            $start_day = $paymentfrom['day'];
            $filter_starttime_con = mktime(0,0,0, $start_month, $start_day, $start_year);
            $this->sql .= " AND tuop.paymenton >= '$filter_starttime_con' ";

        }
        if(isset($this->params['paymentto']) && ($this->params['paymentto']['enabled'] == 1)){
        $paymentto = $this->params['paymentto'];
           $start_year = $paymentto['year'];
            $start_month = $paymentto['month'];
            $start_day = $paymentto['day'];
            $filter_endtime_con=mktime(23,59,59, $start_month, $start_day, $start_year);
            $this->sql .= " AND tuop.paymenton <= '$filter_endtime_con' ";
        }

        if(isset($this->params['filter_idnumber']) && $this->params['filter_idnumber'] > 0){
            $idenumber = $this->params['filter_idnumber'];
            $this->sql .= " AND tuop.userid IN(SELECT lus.userid FROM {local_users} lus WHERE lus.id_number = $idenumber)";
        }
        if((isset($this->params['filter_organization']) && ($this->params['filter_organization'] > 0)) || (isset($this->params['filter_orgcode']) && ($this->params['filter_orgcode']>0) )){
            $orgname = $this->params['filter_organization'] ? $this->params['filter_organization'] : '';
            $orgcode = $this->params['filter_orgcode'] ? $this->params['filter_orgcode'] : '';
            if(!empty($orgname) && !empty($orgcode)){
                $orgid = " AND org.id = $orgcode AND org.id = $orgname";
            }else if(!empty($orgname)){
                $orgid = " AND org.id = $orgname";
            }else if(!empty($orgcode)){
                $orgid = " AND org.id = $orgcode";
            }
                $this->sql .= " AND tuop.userid IN(SELECT lc.userid
                        FROM {local_users} lc
                        LEFT JOIN {local_organization} org ON org.id = lc.organization
                       WHERE  1=1 $orgid)";
        }
        if(isset($this->params['filter_operation']) && !empty($this->params['filter_operation'])){
            $operation = $this->params['filter_operation'];
            if(!empty($operation)){
               $this->sql .= " AND tpt.transactiontype = "."'$operation'"; 
            }
            
        }
        if(isset($this->params['filter_transactiontype']) && !empty($this->params['filter_transactiontype'])){
            $transactiontype = $this->params['filter_transactiontype'];
            if(!empty($transactiontype)){
               $this->sql .= " AND tpt.transactiontype = "."'$transactiontype'"; 
            }
            
        }
        if(isset($this->params['filter_learningtype']) && !empty($this->params['filter_learningtype'])){
           $learningtype = $this->params['filter_learningtype'];
            if(!empty($learningtype)){
                $this->sql .= " AND tp.category = $learningtype";                
            }
            
        }
    }
    function groupby() {
    }
    /**
     * [get_rows description]
     * @param  array  $users [description]
     * @return [type]        [description]
     */
    public function get_rows($elements) {
        global $DB, $CFG, $USER, $OUTPUT;
        $lang = current_language();
        $systemcontext = context_system::instance();
        $finalelements = array();
        if (!empty($elements)){
            foreach($elements as $element){
                $report = new stdClass();
                $report->transactionid = !empty($element->transactioncode) ? $element->transactioncode : '--';
                $orgfullname=($lang == 'ar') ? 'org.fullnameinarabic as orgname' : 'org.fullname as orgname';
                if($lang == 'ar') {
                    $username = "concat(lc.firstnamearabic,' ',lc.lastnamearabic)";
                } else {                             
                    $username = "concat(lc.firstname,' ',lc.lastname)";
                }
                $sql="SELECT lc.userid,$username AS fullname,$orgfullname, org.id AS orgid, lc.id_number AS idnumber, org.shortname
                    FROM {local_users} lc
                    LEFT JOIN {local_organization} org ON org.id = lc.organization
                   WHERE  lc.userid=:orguserid ";
                $user=$DB->get_record_sql($sql,array('orguserid'=>$element->userid));
                $usercreated = '';
                $activitytype = '';
                $trainingtype = '';
                $programtype='';
                $activityname= '';
                $activitycode= '';
                $activitystartdate = '';
                $actvityenddate='';
                $activitystatus='';
                $enrolledby = '';
                $actreferenceid = $element->referenceid ? $element->referenceid : '';
                $category= $element->category ? $element->category : '';
                $activityid = '';
                $usercreated = '';
                $sellingprice = '';
                $userdatalinks = [];
                if($category == 1){
                    $activitytype = get_string('trainingprograms','block_learnerscript');
                    if(!empty($actreferenceid)){
                        if($lang == 'ar'){
                            $offsql = "SELECT pe.id, lt.namearabic AS activityname, lt.code as programcode, tp.code AS offeringcode, tp.trainingmethod, tp.startdate, tp.enddate, tp.time, tp.endtime, tp.type, lt.id AS programid, tp.id AS offeringid, pe.usercreated, tp.sellingprice ";
                        }else{
                            $offsql = "SELECT pe.id, lt.name AS activityname, lt.code as programcode, tp.code AS offeringcode, tp.trainingmethod, tp.startdate, tp.enddate, tp.time, tp.endtime, tp.type, lt.id AS programid, tp.id AS offeringid, pe.usercreated, tp.sellingprice ";
                        }
                        $offsql .= " FROM {local_trainingprogram} lt 
                                    JOIN {tp_offerings} tp ON tp.trainingid = lt.id
                                    JOIN {program_enrollments} pe ON pe.programid = lt.id AND pe.offeringid = tp.id
                                    WHERE tp.id = $actreferenceid AND pe.userid = $element->userid";
                        $offsqldata = $DB->get_record_sql($offsql);
                if(!empty($offsqldata->programid)){

                    $traineruserids = $DB->get_records_sql("SELECT DISTINCT pe.userid FROM {program_enrollments} pe JOIN {role} r ON r.id = pe.roleid 
                        WHERE pe.offeringid = $actreferenceid AND pe.programid = $offsqldata->programid AND pe.enrolstatus = 1 AND r.shortname = 'trainer'");

                    foreach ($traineruserids as $traineruserid) {
                        if ($lang == 'ar') {
                            $usersdata = $DB->get_field_sql("SELECT CONCAT(lu.firstnamearabic, ' ', lu.lastnamearabic) FROM {local_users} lu WHERE lu.userid = $traineruserid->userid");
                            $userdatalinks[] = html_writer::link("$CFG->wwwroot/local/userapproval/userprofile.php?id=$traineruserid->userid", $usersdata, array("target" => "_blank"));
                        } else {
                            $usersdata = $DB->get_field_sql("SELECT CONCAT(lu.firstname, ' ', lu.lastname) FROM {local_users} lu WHERE lu.userid = $traineruserid->userid");
                            $userdatalinks[] = html_writer::link("$CFG->wwwroot/local/userapproval/userprofile.php?id=$traineruserid->userid", $usersdata, array("target" => "_blank"));
                        }
                    }
                }
                        $sellingprice = $offsqldata->sellingprice;
                        $trainingtype = !empty($offsqldata->trainingmethod)? ($offsqldata->trainingmethod == 'online') ? get_string('scheduleonline','local_trainingprogram') : (($offsqldata->trainingmethod == 'offline') ? get_string('scheduleoffline','local_trainingprogram'): (($offsqldata->trainingmethod == 'elearning') ?get_string('scheduleelearning','local_trainingprogram'): '--')) : '';
                        $programtype = ($offsqldata->type ==0) ? get_string('public','local_trainingprogram'):(($offsqldata->type==1) ? get_string('private','local_trainingprogram'): (($offsqldata->type == 2) ? get_string('dedicated','local_trainingprogram'): ''));

                        $activityname = $offsqldata->activityname;
                        $activitycode = (!empty($offsqldata->programcode) || !empty($offsqldata->offeringcode)) ? $offsqldata->programcode.' / '.$offsqldata->offeringcode : '';
                        $examstartdate = userdate($offsqldata->startdate,get_string('strftimedatemonthabbr', 'core_langconfig'));
                        $examstarttime = gmdate("h:i",$offsqldata->time);
                        $examstartdatemeridian = gmdate('a',$offsqldata->time);

                        $examenddate = userdate($offsqldata->enddate,get_string('strftimedatemonthabbr', 'core_langconfig'));
                        $examendtime = gmdate("h:i",$offsqldata->endtime);
                        $examenddatemeridian = gmdate('a',$offsqldata->endtime);       
                        if( $lang == 'ar'){
                            $examstartdatemeridian = ($examstartdatemeridian == 'am')? 'صباحا':'مساءً';
                            $examendtdatemeridian = ($examendtdatemeridian == 'am')? 'صباحا':'مساءً';

                        }else{
                            $examstartdatemeridian = ($examstartdatemeridian == 'am')? 'AM':'PM';
                            $examendtdatemeridian = ($examendtdatemeridian == 'am')? 'AM':'PM';
                        }               
                        $activitystartdate = !empty($offsqldata->startdate) ? $examstartdate . ' ' . $examstarttime . ' ' . $examstartdatemeridian : '';
                        $activityenddate = !empty($offsqldata->enddate) ? $examenddate . ' ' . $examendtime . ' ' . $examenddatemeridian : '';
                        if(!empty($offsqldata->programid) && $offsqldata->trainingmethod == 'elearning'){
                            $activitystartdate = ($element->paymenton) ? userdate($element->paymenton, get_string('strftimedatemonthabbr', 'core_langconfig')): '--';
                        }
                        $usercreated = $offsqldata->usercreated;
                        
                        $activityid = $offsqldata->id;
                    }

                }else if($category == 2){
                    $activitytype = get_string('exam','block_learnerscript');
                     if(!empty($actreferenceid)){
                        if($lang == 'ar'){
                            $profilesql = "SELECT ee.id, ex.id AS examid, ex.examnamearabic AS activityname, ex.code AS examcode, ep.profilecode, ex.status, ep.duration, ee.usercreated,ee.courseid,ep.quizid, ep.passinggrade, ex.sellingprice";
                        }else{
                            $profilesql = "SELECT ee.id, ex.id AS examid, ex.exam AS activityname, ex.code AS examcode, ep.profilecode,ex.status,ep.duration, ee.usercreated, ee.courseid,ep.quizid, ep.passinggrade, ex.sellingprice";
                        }
                        $profilesql .= " FROM {local_exams} ex 
                                        JOIN {local_exam_profiles} ep ON ex.id = ep.examid 
                                        JOIN {exam_enrollments} ee ON ee.examid = ex.id AND ee.profileid = ep.id
                                        WHERE ep.id = $actreferenceid AND ee.userid = $element->userid";

                        $profiledata = $DB->get_record_sql($profilesql);
                        $sellingprice = $profiledata->sellingprice;
                        $activityname = $profiledata->activityname;
                        $activitycode = $profiledata->profilecode;
                        $activitycode = (!empty($profiledata->examcode) || !empty($profiledata->profilecode)) ? $profiledata->examcode.' / '.$profiledata->profilecode : '';

                        if($profiledata->examid){
                            $examhall = $DB->get_record_sql("SELECT leu.examdate, h.starttime, leu.hallscheduleid, leu.id AS scheduleid FROM {local_exam_userhallschedules} leu JOIN {hallschedule} h ON h.id=leu.hallscheduleid WHERE leu.profileid = $actreferenceid AND leu.examid = $profiledata->examid AND leu.userid = $element->userid"); 
                            $activitystartdate = !empty($examhall->examdate) ? userdate($examhall->examdate, get_string('strftimedate','core_langconfig')).' '.userdate($examhall->starttime, get_string('strftimetime12', 'langconfig')) : '--';
                            $activityenddate = !empty($examhall->examdate) ? userdate($examhall->examdate, get_string('strftimedate','core_langconfig')).' '.userdate(($examhall->starttime+$profiledata->duration), get_string('strftimetime12', 'langconfig')) : '--'; 
                        }
                        if (!empty($examhall->scheduleid) && !empty($profiledata->quizid) && !empty($profiledata->courseid)) {
                            $status = (new exams)->gradestatus($examhall->scheduleid);
                            if (is_string($status)) {
                                $activitystatus = $status;
                            } else {
                                $sql = "SELECT ROUND(gg.finalgrade, 0) as finalgrade, gi.gradepass
                                        FROM {grade_items} gi
                                        JOIN {grade_grades} gg on gg.itemid = gi.id
                                        WHERE gi.courseid = {$profiledata->courseid} AND gi.itemtype = 'mod' AND gi.itemmodule  = 'quiz' AND gi.iteminstance = {$profiledata->quizid} AND gg.userid = $element->userid ";
                                $passinggrade = $DB->get_record_sql($sql);

                                $grade = !empty($passinggrade->finalgrade) ? $passinggrade->finalgrade : 0;
                                if ($grade >= $profiledata->passinggrade) {
                                    $activitystatus = get_string('exampassed', 'block_learnerscript');
                                } else {
                                    $activitystatus = get_string('examfailed', 'block_learnerscript');
                                }
                            }
                        } else {
                            $activitystatus = get_string('notstarted', 'block_learnerscript');
                        }  
                        $activityname = $profiledata->activityname;
                        $activitycode = (!empty($profiledata->examcode) || !empty($profiledata->profilecode)) ? $profiledata->examcode .' / '.$profiledata->profilecode : '';                        
                        $usercreated = $profiledata->usercreated;
                        $activityid = $profiledata->id;
                                              
                    }
                }else if($category == 3){
                    $activitytype = get_string('events','block_learnerscript');
                    if(!empty($actreferenceid)){
                        if($lang == 'ar'){
                        $eventsql = "SELECT lea.id, le.titlearabic AS activityname, le.code, le.type, le.startdate, le.enddate,le.status,le.slot, le.eventduration,le.status,le.method, lea.usercreated, le.sellingprice";
                        }else{
                            $eventsql = "SELECT lea.id, le.title AS activityname, le.code, le.type, le.startdate,le.enddate,le.status,le.slot,le.eventduration,le.status,le.method, lea.usercreated, le.sellingprice";
                        }
                        $eventsql .= " FROM {local_event_attendees} lea 
                                        JOIN {local_events} le ON lea.eventid
                                    WHERE le.id = $actreferenceid AND lea.userid = $element->userid";
                        $eventdata = $DB->get_record_sql($eventsql);
                        $sellingprice = $eventdata->sellingprice;
                        $methodarray = array(0 => get_string('inclass', 'local_events'),1 => get_string('virtual', 'local_events'));
                        $trainingtype = $methodarray[$eventdata->method];
                        $programtype = ($eventdata->type ==0) ? get_string('public','local_trainingprogram'):(($eventdata->type==1) ? get_string('private','local_trainingprogram'): (($eventdata->type == 2) ? get_string('dedicated','local_trainingprogram'): ''));
                        $activityname = $eventdata->activityname;
                        $activitycode = $eventdata->code;

                       $event_starttimemeridian = date("a",mktime(0, 0, $eventdata->slot));
                        $event_endtimemeridian = date("a",mktime(0, 0, ($eventdata->slot + $eventdata->eventduration)));

                        $event_starttime = date('h:i', mktime(0, 0, $eventdata->slot));
                        $event_endttime = date("h:i",mktime(0, 0, ($eventdata->slot + $eventdata->eventduration)));
                        $get_time_lang = (new events())->time_lang_change($event_starttimemeridian, $event_endtimemeridian);
                        $starttimemeridian = gmdate('a',$eventdata->slot);
                        $endtimemeridian = gmdate('a',($eventdata->slot + $eventdata->eventduration));
                       
                        $eventstarttime = gmdate("h:i",$eventdata->slot);
                        $eventendttime = gmdate("h:i",($eventdata->slot + $eventdata->eventduration));

                        $eventstartmeridian = ($starttimemeridian == 'am')?  get_string('am','local_trainingprogram'):get_string('pm','local_trainingprogram');
                        $eventendmeridian = ($endtimemeridian == 'am')?  get_string('am','local_trainingprogram'):get_string('pm','local_trainingprogram');

                        $activitystartdate = userdate($eventdata->startdate, get_string('strftimedatemonthabbr','core_langconfig')).' '.$eventstarttime.' '.$eventstartmeridian;
                        $activityenddate = userdate(($eventdata->enddate), get_string('strftimedatemonthabbr', 'core_langconfig')).' '.$eventendttime.' '.$eventendmeridian;


                        $statusarray = array(0 => get_string('active', 'local_events'),
                            1 => get_string('inactive', 'local_events'),
                            2 => get_string('cancelled', 'local_events'),
                            3 => get_string('closed', 'local_events'),
                            4 => get_string('archieved', 'local_events'));                        
                        $usercreated = $eventdata->usercreated;
                        $activityid = $eventdata->id;
                    }
                }
              
                if(!empty($usercreated)){
                    if($usercreated == $element->userid){
                        $enrolledby = get_string('selfenrol', 'block_learnerscript');                        
                    }else{
                        $username = $DB->get_field_sql("SELECT username FROM {user} u WHERE u.id = $usercreated");
                        if($lang == 'en'){
                           $enrolledby = $DB->get_field_sql("SELECT CONCAT(u.firstname, ' ', u.lastname) FROM {user} u WHERE u.id = $usercreated");
                        }else{
                            $enrolledby = $DB->get_field_sql("SELECT CONCAT(lu.firstnamearabic, ' ', lu.lastnamearabic) FROM {local_users} lu WHERE lu.userid = $usercreated");
                        }
                        if($row->{$data->column} == ' '){
                            $enrolledby = $DB->get_field_sql("SELECT CONCAT(u.firstname, ' ', u.lastname) FROM {user} u WHERE u.id = $usercreated");
                        }
                        if($username == 'admin'){
                            if($lang == 'en'){
                                $enrolledby = $DB->get_field_sql("SELECT CONCAT(u.firstname, ' ', u.lastname) FROM {user} u WHERE u.id = $usercreated");
                            }else{
                                $enrolledby = 'مسؤول النظام';
                            }
                        }
                    }
                    
                }else{
                    $enrolledby = '--';
                }
                $report->id = $element->tptid;
                $report->paymentdate = ($element->paymenton) ? userdate($element->paymenton, get_string('strftimedate', 'core_langconfig')): '--';
                $report->amount = ($element->amount) ? (($element->transactiontype == 'refund') ? ('-' .$element->amount) : $element->amount) : '--';
                $report->vat =  ($element->taxes) ? ($element->taxes) : '--';
                $report->discount = ($element->discountprice) ? ($element->discountprice) : '--';
                $report->operation = ($element->transactiontype) ? ucfirst(get_string($element->transactiontype, 'block_learnerscript')) : '--';
                $report->transactiontype = ($element->purchasedseats > 0) ?  get_string('purchase', 'block_learnerscript') : get_string('reschedule', 'block_learnerscript');
                $report->idnumber = !empty($user->idnumber) ? $user->idnumber : '--';
                
                $report->username = !empty($user->fullname) ? $user->fullname : '--'; 
                
                $report->activitytype= !empty($activitytype) ? $activitytype : '--';
                $report->trainingtype = !empty($trainingtype) ? $trainingtype : '--';
                $report->programtype= !empty($programtype) ? $programtype :'--';
                $report->activityname = !empty($activityname) ? $activityname:'--';
                $report->activitycode = !empty($activitycode) ? $activitycode : '--';
                $report->activitystartdate = !empty($activitystartdate) ? $activitystartdate : '--';
                $report->activityenddate = !empty($activityenddate) ? $activityenddate : '--';
                $report->activitystatus=!empty($activitystatus) ? $activitystatus :'--';
                $report->organizationname = !empty($user->orgname) ? $user->orgname : '--';
                $report->organizationcode = !empty($user->shortname) ? $user->shortname : '--';
                $report->paymenttype = get_string('prepaid', 'block_learnerscript');
                $report->enrolledby = !empty($enrolledby) ? $enrolledby : '--';
                $report->billnumber = !empty($element->transactionref) ? $element->transactionref : '--'; 
                $report->paymentstatus = get_string('yes', 'block_learnerscript');
                $report->activityid = !empty($activityid) ? $activityid : '--';
                $report->userid = !empty($user->idnumber) ? $user->idnumber : '--';
                $report->organizationid = !empty($user->orgid) ? $user->orgid : '--';
                $report->actreferenceid = !empty($actreferenceid) ? $actreferenceid : '--';
                $report->transactionid = !empty($element->transactioncode) ? $element->transactioncode : '--';
                $report->activityprice = !empty($sellingprice) ? (($element->purchasedseats <= 0) ? '--' : $sellingprice) : '--';
                $report->trainer = !empty($userdatalinks) ? implode(',', $userdatalinks) : '--';
                $data[] = $report;
            }

            return $data;
        }
        return $finalelements;
    }
}
