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

class report_transaction extends reportbase{
    /**
     * [__construct description]
     * @param [type] $report           [description]
     * @param [type] $reportproperties [description]
     */
    public function __construct($report, $reportproperties) {
        parent::__construct($report);
        $this->parent = false;
        $this->courselevel = true;
        $this->components = array('columns', 'filters', 'permissions', 'calcs','plot');
        $columns = ['creationdate','paymentstatus','paymentdate','invoicenumber','invoicestatus','noofseats','activityprice','vat','discount','amount','operation','transactiontype','idnumber','username','trainer','activitytype','trainingtype','programtype','activityname','activitycode','activitystartdate','activityenddate','activitystatus','organizationname','organizationcode','paymenttype','billnumber','transactionid','organizationid','userid','activityid'];
        $this->columns = ['transactioncolumns' => $columns];
        $this->searchable = array( 'si.userid','si.invoice_number');
        $this->orderable = array('si.userid','si.invoice_number');
        $this->filters = array('paymentdate','idnumber','orgcode','organization','transactiontype','operation','learningtype','paymenttype');
        $this->defaultcolumn = 'si.id';
         $this->excludedroles = array("'manager', 'coursecreator', 'editingteacher', 'teacher', 'student', 'examofficial', 'em', 'co', 'to', 'expert', 'trainer','competencies_official', 'cpd', 'hall_manager','organizationofficial','trainee'");
    }
    function init() {
        global $DB;
    }

    function count() {
        global $DB;
        $this->sql = "SELECT COUNT(DISTINCT si.id), op.id AS toolorderid ";
    }

    function select() {
        $this->sql = " SELECT si.*,op.taxes, op.discountprice, si.userid AS orguserid, op.id AS transactionid, si.productid, tp.category, tp.referenceid, op.id AS orderid, op.paymenttype As orgpaymenttype, op.paymenton  ";
        parent::select();
      }

    function from() {
            $this->sql .=" FROM {tool_product_sadad_invoice} si
                     LEFT JOIN {tool_org_order_payments} AS op ON op.transactionid = si.invoice_number
                     LEFT JOIN {tool_order_approval_seats} oas ON oas.paymentid = op.id
                     JOIN {tool_products} tp ON tp.id = si.productid";
    }

    function joins() {
        parent::joins();
    }

    function where() { 
        global $USER;
        $this->sql .= "  WHERE 1=1";
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
            $this->sql .= " AND si.timecreated >= '$filter_starttime_con' ";

        }
        if(isset($this->params['paymentto']) && ($this->params['paymentto']['enabled'] == 1)){
        $paymentto = $this->params['paymentto'];
           $start_year = $paymentto['year'];
            $start_month = $paymentto['month'];
            $start_day = $paymentto['day'];
            $filter_endtime_con=mktime(23,59,59, $start_month, $start_day, $start_year);
            $this->sql .= " AND si.timecreated <= '$filter_endtime_con' ";
        }
        if(isset($this->params['filter_idnumber']) && $this->params['filter_idnumber'] > 0){
            $idenumber = $this->params['filter_idnumber'];
            $this->sql .= " AND si.userid IN(SELECT lus.userid FROM {local_users} lus WHERE lus.id_number = $idenumber)";
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
                $this->sql .= " AND si.userid IN(SELECT lc.userid
                        FROM {local_users} lc
                        JOIN {local_organization} org ON org.id = lc.organization
                       WHERE  1=1 $orgid)";
        }
        if(isset($this->params['filter_transactiontype']) && !empty($this->params['filter_transactiontype'])){
            $transactiontype = $this->params['filter_transactiontype'];
            if(!empty($transactiontype)){
               $this->sql .= " AND si.type = "."'$transactiontype'"; 
            }
            
        }
        if(isset($this->params['filter_learningtype']) && !empty($this->params['filter_learningtype'])){
            $learningtype = $this->params['filter_learningtype'];
            if(!empty($learningtype)){
                $this->sql .= " AND si.productid IN(SELECT id FROM {tool_products} WHERE category = $learningtype)";
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
                    $username = "concat(u.firstname,' ',u.lastname)";
                }
                $sql="SELECT u.id,$username AS fullname,$orgfullname, org.id AS orgid, lc.id_number AS idnumber, org.shortname 
                    FROM {user} AS u 
                    JOIN {local_users} lc ON lc.userid = u.id
                    JOIN {local_organization} org ON org.id = lc.organization
                   WHERE  u.id=:orguserid ";
                $user=$DB->get_record_sql($sql,array('orguserid'=>$element->userid));
                if(empty($user)){
                    $sql = " SELECT u.id, concat(u.firstname,' ',u.lastname) AS fullname,'' AS orgfullname, '' AS orgid, u.idnumber AS idnumber, '' AS shortname
                    FROM {user} u WHERE u.id = :orguserid ";
                    $user=$DB->get_record_sql($sql,array('orguserid'=>$element->userid));
                }           
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
                $activityid = '';
                $sellingprice = '';
                $userdatalinks = [];
                $orguserid = $element->orguserid ? $element->orguserid : '';
                $actreferenceid = $element->referenceid ? $element->referenceid : '';
                $category= $element->category ? $element->category : '';
                if($category == 1){
                    $activitytype = get_string('trainingprograms','block_learnerscript');
                    if(!empty($actreferenceid)){
                        if($lang == 'ar'){
                            $offsql = "SELECT lt.namearabic AS activityname, lt.code as programcode, tp.code AS offeringcode, tp.trainingmethod, tp.startdate, tp.enddate, tp.time, tp.endtime, tp.type, lt.id AS programid, tp.id AS offeringid, tp.sellingprice ";
                        }else{
                            $offsql = "SELECT lt.name AS activityname, lt.code as programcode, tp.code AS offeringcode, tp.trainingmethod, tp.startdate, tp.enddate, tp.time, tp.endtime, tp.type, lt.id AS programid, tp.id AS offeringid, tp.sellingprice ";
                        }
                        $offsql .= " FROM {local_trainingprogram} lt 
                                    JOIN {tp_offerings} tp ON tp.trainingid = lt.id 
                                    WHERE tp.id = $actreferenceid ";
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
                        $activityid = $offsqldata->programid;
                       
                    }

                }else if($category == 2){
                    $activitytype = get_string('exam','block_learnerscript');
                     if(!empty($actreferenceid)){
                        if($lang == 'ar'){
                            $profilesql = "SELECT ex.id AS examid,ex.examnamearabic AS activityname, ex.code AS examcode, ep.profilecode, ex.status, ep.duration,ex.courseid,ep.quizid, ep.passinggrade, ex.sellingprice";
                        }else{
                            $profilesql = "SELECT ex.id AS examid,ex.exam AS activityname, ex.code AS examcode, ep.profilecode,ex.status,ep.duration,ex.courseid,ep.quizid, ep.passinggrade, ex.sellingprice";
                        }
                        $profilesql .= " FROM {local_exams} ex 
                                        JOIN {local_exam_profiles} ep ON ex.id = ep.examid 
                                        WHERE ep.id = $actreferenceid";
                        $profiledata = $DB->get_record_sql($profilesql);
                        $sellingprice = $profiledata->sellingprice;
                        $activityname = $profiledata->activityname;
                        $activitycode = (!empty($profiledata->examcode) || !empty($profiledata->profilecode)) ? $profiledata->examcode .' / '.$profiledata->profilecode : '';                         
                       
                        $activityid = $profiledata->examid;
                       
                    }
                }else if($category == 3){
                    $activitytype = get_string('events','block_learnerscript');
                    if(!empty($actreferenceid)){
                        if($lang == 'ar'){
                        $eventsql = "SELECT le.*, le.titlearabic AS activityname";
                        }else{
                            $eventsql = "SELECT le.*,le.title AS activityname";
                        }
                        $eventsql .= " FROM {local_events} le 
                                    WHERE le.id = $actreferenceid";
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
                        $activitystatus =$eventdata->status ?  $statusarray[$eventdata->status]:'';
                        $activityid= $eventdata->id;
                    }
                }
                $programreportid = $DB->get_field('block_learnerscript', 'id', array('type' => 'programenrol'), IGNORE_MULTIPLE);
                $examreportid = $DB->get_field('block_learnerscript', 'id', array('type' => 'examenrol'), IGNORE_MULTIPLE);
                $eventreportid = $DB->get_field('block_learnerscript', 'id', array('type' => 'eventenrol'), IGNORE_MULTIPLE);
                $report->creationdate = ($element->timecreated) ? userdate($element->timecreated, get_string('strftimedate', 'core_langconfig')): '--';
                $report->paymentstatus = ($element->payment_status == 1) ? get_string('yes', 'block_learnerscript') : get_string('no', 'block_learnerscript');
                $report->paymentdate = ($element->payment_status == 1) ? date('d M Y',$element->paymenton) : '--';
                $report->invoicenumber = !empty($element->invoice_number) ? ((($element->type == 'cancel') && ($element->payableamount == 0)) ? '--' : $element->invoice_number) : '--';
                $report->invoicestatus = (($element->type == 'cancel') && ($element->payableamount == 0)) ? '--' : (($element->status == 1) ? get_string('active', 'block_learnerscript') : get_string('inactive', 'block_learnerscript'));
                 $seats = (($element->type == 'cancel') && ($element->seats == 0)) ? 1 : $element->seats;
                $trainereportid = ($category == 1) ? ($programreportid) : (($category == 2) ? ($examreportid) : (($category == 3) ? ($eventreportid) : '') );
                    if(!empty($trainereportid)){
                     $report->noofseats =$seats ? html_writer::div($seats,'btn-link cursor-pointer',array('onclick'=>"window.open('$CFG->wwwroot/blocks/learnerscript/viewreport.php?id=$trainereportid&filter_referenceid=$actreferenceid&filter_orguser=$element->userid')")) : 0;
                    }else{
                        $report->noofseats = $seats;
                    }
                $report->amount = ($element->payableamount) ? (($element->status != 1) ? 0 : $element->payableamount) : 0;
                $report->vat = !empty($element->taxes) ? (($element->status != 1) ? 0 : $element->taxes) : 0;
                $report->discount = !empty($element->discountprice) ? (($element->status != 1) ? 0 : $element->discountprice) : 0;
                 $report->operation = get_string('sale','block_learnerscript');
                $transactions = ['purchase' => get_string('purchase', 'block_learnerscript'),'reschedule' => get_string('reschedule', 'block_learnerscript'),'replacement' => get_string('replacement', 'block_learnerscript'),'cancel' => get_string('cancel', 'block_learnerscript'),'examsbulkenrollment' => get_string('examsbulkenrollment', 'block_learnerscript'),'programsbulkenrollment' => get_string('programsbulkenrollment', 'block_learnerscript'),'assessment_operation_enrolments' => get_string('assessment_operation_enrolments', 'block_learnerscript'),'null'=>'--'];
                $report->transactiontype = $element->type ? $transactions[$element->type] : '--';
                $report->idnumber = $user->idnumber;
                $report->username = $user->fullname;
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
                $report->paymenttype = get_string('postpaid', 'block_learnerscript');
                $report->enrolledby = !empty($enrolledby) ? $enrolledby : '--';
                $billnumber = $DB->get_field('tool_product_telr','transactionref',array('id'=>$element->telrid));
                $report->billnumber = !empty($billnumber) ? $billnumber : '--'; 
                $report->activityid = !empty($activityid) ? $activityid : '--';
                $report->userid = !empty($user->id) ? $user->id : '--';
                $report->organizationid = !empty($user->orgid) ? $user->orgid : '--';
                $report->actreferenceid = !empty($actreferenceid) ? $actreferenceid : '--';
                $report->transactionid = !empty($element->invoice_number) ? $element->invoice_number : '--';
                $report->activityprice = ($element->type == 'replacement' || $element->type == 'cancel' || $element->type == 'reschedule') ? 0 : (($element->status != 1) ? 0 : (!empty($sellingprice) ? ($sellingprice*$seats) : '--'));
                $report->trainer = !empty($userdatalinks) ? implode(',', $userdatalinks) : '--';
                $data[] = $report;
            }

            return $data;
        } 
        return $finalelements;
    }
}
