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
 * @date: 2023
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

class report_traineerefundpayments extends reportbase{
    /**
     * [__construct description]
     * @param [type] $report           [description]
     * @param [type] $reportproperties [description]
     */
    public const TRAINING_PROGRAM = 1;
    public const EXAMS = 2;
    public const EVENTS = 3;
    public const GRIEVANCE = 4;
    public const LEARNINGTRACKS = 5;
    public const EXAMATTEMPT = 6;
    public function __construct($report, $reportproperties) {
        parent::__construct($report);
        $this->parent = false;
        $this->courselevel = true;
        $this->components = array('columns', 'filters', 'permissions', 'plot');
        $columns = ['transactiondate','billnumber','amount','vat','deduction','transactiontype','identitynumber','username','activitytype','trainingtype','programtype','activityname','activitycode','activitystartdate','activityenddate','activitystatus','organizationname','organizationcode','paymenttype'];
        $this->columns = ['refundcolumns' => $columns];
        $this->searchable = array('tlr.transactionref','tpr.amount');
        $this->orderable = array();
        $this->filters = array('paymentdate','idnumber','orgcode','organization','transactiontype','learningtype','paymenttype');
        //$this->defaultcolumn = 'tpr.id';
        $this->sqlorder['column'] = 'tpr.transactiondate';
        $this->sqlorder['dir'] = 'DESC';
        $this->excludedroles = array("'manager', 'coursecreator', 'editingteacher', 'teacher', 'student', 'examofficial', 'em', 'co', 'to', 'expert', 'trainer','competencies_official', 'cpd', 'hall_manager','organizationofficial','trainee'");
    }
    function init() {
        global $DB;
      
    }

    function count() {
        global $DB;
        $this->sql = "SELECT COUNT(DISTINCT tpr.id)";
        
    }

    function select() {
        $this->sql = " SELECT DISTINCT tpr.*, tlr.transactionref ";
        parent::select();
      }

    function from() {
        $this->sql .= " FROM {tool_product_refund} tpr";
    }

    function joins() {
        $this->sql .= " JOIN {tool_product_telr} tlr ON tlr.id = tpr.transactionid";
        parent::joins();
    }

    function where() { 
       global $USER;
        $this->sql .= " WHERE 1=1 ";
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
            $this->sql .= " AND tpr.transactiondate >= '$filter_starttime_con' ";

        }
        if(isset($this->params['paymentto']) && ($this->params['paymentto']['enabled'] == 1)){
        $paymentto = $this->params['paymentto'];
           $start_year = $paymentto['year'];
            $start_month = $paymentto['month'];
            $start_day = $paymentto['day'];
            $filter_endtime_con=mktime(23,59,59, $start_month, $start_day, $start_year);
            $this->sql .= " AND tpr.transactiondate <= '$filter_endtime_con' ";
        }

        if(isset($this->params['filter_idnumber']) && !empty($this->params['filter_idnumber'])){
            $idenumber = $this->params['filter_idnumber'];
            $this->sql .= " AND tpr.userid IN(SELECT lus.userid FROM {local_users} lus WHERE lus.id_number = '".$idenumber."')";
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
                $this->sql .= " AND tpr.userid IN(SELECT lc.userid
                        FROM {local_users} lc
                        JOIN {local_organization} org ON org.id = lc.organization
                       WHERE  1=1 $orgid)";
        }
        if(isset($this->params['filter_learningtype']) && !empty($this->params['filter_learningtype'])){
           $learningtype = $this->params['filter_learningtype'];
            if(!empty($learningtype)){
                if($learningtype == 2){
                   $this->sql .= " AND tpr.productid IN(SELECT id FROM {tool_products} WHERE category IN(2,6))"; 
               }else{
                $this->sql .= " AND tpr.productid IN(SELECT id FROM {tool_products} WHERE category = $learningtype)";
               }
                
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
                $userorder = $DB->get_record_sql("SELECT * FROM {tool_user_order_payments} WHERE telrid = $element->transactionid AND productid = $element->productid AND userid = $element->userid"); 
                $productrecord = $DB->get_record('tool_products', ['id' => $element->productid]);
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
                    $userinfo=$DB->get_record_sql($sql,array('orguserid'=>$element->userid));

                    $report->transactiondate = ($element->transactiondate) ? userdate($element->transactiondate, get_string('strftimedate', 'core_langconfig')): '--';
                    $report->billnumber = $element->transactionref ? $element->transactionref : '--';
                    $report->amount = $element->amount ? round($element->amount, 2) : '--';
                    $report->vat = $userorder->taxes ? $userorder->taxes : '--' ;
                    $report->price = $userorder->amount ;
                    $report->deduction = ($userorder->amount) ? round(($userorder->amount-$element->amount),2) : '--';
                    $report->transactiontype = get_string('refund','block_learnerscript');
                    $report->identitynumber = $userinfo->idnumber ? $userinfo->idnumber : '--';
                    $report->username = $userinfo->fullname ? $userinfo->fullname : '--';
                    $activitytype = '';
                    $trainingtype = '';
                    $programtype='';
                    $activityname= '';
                    $activitycode= '';
                    $startdate = '';
                    $enddate='';
                    $activitystatus='';
                    if($productrecord->category == 1){
                        $refund['tablename'] = 'tp_offerings';
                        $offeringid = $DB->get_record('tp_offerings', ['id' => $productrecord->referenceid]);
                        $refund['entityid'] = $offeringid;
                        $activitytype = get_string('trainingprograms','block_learnerscript');
                        $trainingtype = !empty($offeringid->trainingmethod)? ($offeringid->trainingmethod == 'online') ? get_string('scheduleonline','local_trainingprogram') : (($offeringid->trainingmethod == 'offline') ? get_string('scheduleoffline','local_trainingprogram'): (($offeringid->trainingmethod == 'elearning') ?get_string('scheduleelearning','local_trainingprogram'): '')) : '';

                        $programtype = ($offeringid->type ==0) ? get_string('public','local_trainingprogram'):(($offeringid->type==1) ? get_string('private','local_trainingprogram'): (($offeringid->type == 3) ? get_string('dedicated','local_trainingprogram'): ''));

                        $programid = $DB->get_record('local_trainingprogram', ['id' => $offeringid->trainingid]);
                        $activityname = ($lang == 'en') ? $programid->name : $programid->namearabic;
                        $activitycode = (!empty($programid->code) || !empty($offeringid->code)) ? $programid->code.' / '.$offeringid->code : '';

                        $examstartdate = userdate($offeringid->startdate,get_string('strftimedatemonthabbr', 'core_langconfig'));
                        $examstarttime = gmdate("h:i",$offeringid->time);
                        $examstartdatemeridian = gmdate('a',$offeringid->time);

                        $examenddate = userdate($offeringid->enddate,get_string('strftimedatemonthabbr', 'core_langconfig'));
                        $examendtime = gmdate("h:i",$offeringid->endtime);
                        $examenddatemeridian = gmdate('a',$offeringid->endtime);       
                        if( $lang == 'ar'){
                            $examstartdatemeridian = ($examstartdatemeridian == 'am')? 'صباحا':'مساءً';
                            $examendtdatemeridian = ($examendtdatemeridian == 'am')? 'صباحا':'مساءً';

                        }else{
                            $examstartdatemeridian = ($examstartdatemeridian == 'am')? 'AM':'PM';
                            $examendtdatemeridian = ($examendtdatemeridian == 'am')? 'AM':'PM';
                        }               
                        $startdate = !empty($offeringid->startdate) ? $examstartdate . ' ' . $examstarttime . ' ' . $examstartdatemeridian : '';
                        $enddate = !empty($offeringid->enddate) ? $examenddate . ' ' . $examendtime . ' ' . $examenddatemeridian : '';

                    }else if($productrecord->category == 2){
                        $refund['tablename'] = 'local_exam_profiles';
                        $profileid = $DB->get_record('local_exam_profiles', ['id' => $productrecord->referenceid]);
                        $enroldata = $DB->get_record('exam_enrollments', ['profileid' => $productrecord->referenceid]);
                        $activitytype = get_string('exam','block_learnerscript');
                        $examid = $DB->get_record('local_exams', ['id' => $profileid->examid]);
                        $activityname = ($lang == 'en') ? $examid->exam : $examid->examnamearabic;
                        $activitycode = (!empty($profileid->profilecode) || !empty($examid->code)) ? ($examid->code.' / '.$profileid->profilecode) : '';

                        if($examid->id){
                            $examhall = $DB->get_record_sql("SELECT leu.examdate, h.starttime, leu.hallscheduleid, leu.id AS scheduleid FROM {local_exam_userhallschedules} leu JOIN {hallschedule} h ON h.id=leu.hallscheduleid WHERE leu.profileid = $profileid->id"); 
                            $startdate = !empty($examhall->examdate) ? userdate($examhall->examdate, get_string('strftimedate','core_langconfig')).' '.userdate($examhall->starttime, get_string('strftimetime12', 'langconfig')) : '--';
                            $enddate = !empty($examhall->examdate) ? userdate($examhall->examdate, get_string('strftimedate','core_langconfig')).' '.userdate(($examhall->starttime+$profileid->duration), get_string('strftimetime12', 'langconfig')) : '--'; 
                        }
                         if (!empty($examhall->scheduleid)) {
                            $status = (new exams)->gradestatus($examhall->scheduleid);
                            if (is_string($status)) {
                                $activitystatus = $status;
                            } else {
                                $sql = "SELECT ROUND(gg.finalgrade, 0) as finalgrade, gi.gradepass
                                        FROM {grade_items} gi
                                        JOIN {grade_grades} gg on gg.itemid = gi.id
                                        WHERE gi.courseid = {$enroldata->courseid} AND gi.itemtype = 'mod' AND gi.itemmodule  = 'quiz' AND gi.iteminstance = {$profileid->quizid} AND gg.userid = $element->userid ";
                                $passinggrade = $DB->get_record_sql($sql);

                                $grade = !empty($passinggrade->finalgrade) ? $passinggrade->finalgrade : 0;
                                if ($grade >= $profileid->passinggrade) {
                                    $activitystatus = get_string('exampassed', 'block_learnerscript');
                                } else {
                                    $activitystatus = get_string('examfailed', 'block_learnerscript');
                                }
                            }
                        } else {
                            $activitystatus = get_string('notstarted', 'block_learnerscript');
                        } 
                    }else if($productrecord->category == 3){
                        $refund['tablename'] = 'local_events';
                        $eventid = $DB->get_record('local_events', ['id' => $productrecord->referenceid]);
                        $refund['entityid'] = $productrecord->referenceid;
                        $activitytype = get_string('events','block_learnerscript');
                        $methodarray = array(0 => get_string('inclass', 'local_events'),1 => get_string('virtual', 'local_events'));
                        $trainingtype = $methodarray[$eventid->method];
                        $programtype = ($eventid->type ==0) ? get_string('public','local_trainingprogram'):(($eventid->type==1) ? get_string('private','local_trainingprogram'): (($eventid->type == 2) ? get_string('dedicated','local_trainingprogram'): ''));
                        $activityname = ($lang == 'en') ? $eventid->title : $eventid->titlearabic;
                        $activitycode = $eventid->code ;

                        $event_starttimemeridian = date("a",mktime(0, 0, $eventid->slot));
                        $event_endtimemeridian = date("a",mktime(0, 0, ($eventid->slot + $eventid->eventduration)));

                        $event_starttime = date('h:i', mktime(0, 0, $eventid->slot));
                        $event_endttime = date("h:i",mktime(0, 0, ($eventid->slot + $eventid->eventduration)));
                        $get_time_lang = (new events())->time_lang_change($event_starttimemeridian, $event_endtimemeridian);
                        $starttimemeridian = gmdate('a',$eventid->slot);
                        $endtimemeridian = gmdate('a',($eventid->slot + $eventid->eventduration));
                       
                        $eventstarttime = gmdate("h:i",$eventid->slot);
                        $eventendttime = gmdate("h:i",($eventid->slot + $eventid->eventduration));

                        $eventstartmeridian = ($starttimemeridian == 'am')?  get_string('am','local_trainingprogram'):get_string('pm','local_trainingprogram');
                        $eventendmeridian = ($endtimemeridian == 'am')?  get_string('am','local_trainingprogram'):get_string('pm','local_trainingprogram');

                        $startdate = userdate($eventid->startdate, get_string('strftimedatemonthabbr','core_langconfig')).' '.$eventstarttime.' '.$eventstartmeridian;
                        $enddate = userdate(($eventid->enddate), get_string('strftimedatemonthabbr', 'core_langconfig')).' '.$eventendttime.' '.$eventendmeridian;         
                    }
                    $report->activitytype= !empty($activitytype) ? $activitytype : '--';
                    $report->trainingtype = !empty($trainingtype) ? $trainingtype : '--';
                    $report->programtype= !empty($programtype) ? $programtype :'--';
                    $report->activityname = !empty($activityname) ? $activityname:'--';
                    $report->activitycode = !empty($activitycode) ? $activitycode : '--';
                    $report->activitystartdate = !empty($startdate) ? $startdate : '--';
                    $report->activityenddate = !empty($enddate) ? $enddate : '--';
                    $report->activitystatus=!empty($activitystatus) ? $activitystatus :'--';
                    $report->organizationname = !empty($userinfo->orgname) ? $userinfo->orgname : '--';
                    $report->organizationcode = !empty($userinfo->shortname) ? $userinfo->shortname : '--';
                    $report->paymenttype = get_string('prepaid', 'block_learnerscript');
                    $report->category = $productrecord->category ? $productrecord->category : '';
                    $report->productid = $productrecord->id ? $productrecord->id : '';
                    $report->referenceid = $productrecord->referenceid;
                $data[] = $report;
            }

            return $data;
        }
        return $finalelements;
    }
}
