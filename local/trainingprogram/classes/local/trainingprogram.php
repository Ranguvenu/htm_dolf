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
namespace local_trainingprogram\local;
use moodle_exception;
use tool_product\telr;

use local_exams\local\exams;
require_once($CFG->dirroot .'/course/lib.php');
require_once($CFG->libdir . '/completionlib.php');
require_once($CFG->dirroot .'/mod/attendance/externallib.php');
require_once($CFG->dirroot . '/mod/zoom/lib.php');
require_once($CFG->dirroot.'/mod/zoom/locallib.php');
require_once($CFG->dirroot.'/user/lib.php');
require_once($CFG->dirroot.'/group/lib.php');
require_once($CFG->dirroot.'/lib/moodlelib.php');
require_once($CFG->dirroot.'/mod/msteams/lib.php');
require_once($CFG->dirroot .'/mod/zoom/lib.php');
require_once($CFG->dirroot .'/mod/webexactivity/lib.php');
require_once($CFG->dirroot.'/mod/quiz/lib.php');
require_once($CFG->dirroot.'/mod/attendance/lib.php');
use tool_product\product;
use stdClass;
use dml_exception;
use html_writer;
use moodle_url;
use context_system;
use tabobject;
use user_create_user;
use context_user;
use core_user;
use filters_form;
use mod_attendance_external;
use local_trainingprogram\local\createoffering;
use local_trainingprogram\local\trainingprogram as tp;
use single_button;
use local_learningtracks\learningtracks as learningtracks;
// require_once($CFG->dirroot . '/local/trainingprogram/filters_form.php');
require_once($CFG->dirroot . '/local/trainingprogram/lib.php');
use local_trainingprogram\local\dataprovider as dataprovider;
use local_userapproval\action\manageuser as manageuser;

/**
 * Training program library file
 */
class trainingprogram
{

    const CORECOMPETENCIES = 'corecompetencies';
    const TECHNICALCOMPETENCIES = 'technicalcompetencies';
    const BEHAVIORALCOMPETENCIES = 'behavioralcompetencies';
    
    public function add_new($data) {
        global $DB,$USER;


        $context = context_system::instance();
        $tpdata = new stdClass();

        $tpdata->name = trim(ucwords($data->name));
        $tpdata->namearabic = trim($data->namearabic);
        $tpdata->externallink = $data->externallink;
        $tpdata->externallinkcheck = $data->externallinkcheck;
        $tpdata->image = $data->image ? $data->image : 0;

        if(!is_null($data->oldid)) {

            $tpdata->oldid = $data->oldid;

        }
        $tpdata->price = $data->cost;
        if($data->cost == 1) {
           $tpdata->sellingprice = $data->sellingprice;
           $tpdata->actualprice = $data->actualprice;
           $tpdata->tax_free = $data->tax;
        } else {
           $tpdata->sellingprice = 0;
           $tpdata->actualprice = 0;
           $tpdata->tax_free = null;
        }
        $tpdata->description = $data->description['text'];
        $tpdata->languages= is_array($data->language)?implode(',',$data->language):$data->language;
        $tpdata->methods = implode(',',$data->programmethod);
        $tpdata->evaluationmethods = $data->evaluationmethod?implode(',',$data->evaluationmethod):null;
        $tpdata->dynamicevaluationmethod = $data->dynamicevaluationmethod?implode(',',$data->dynamicevaluationmethod):null;
        $tpdata->duration = $data->duration;
        $tpdata->availableto = $data->availableto;
        $tpdata->availablefrom = $data->availablefrom;
        $tpdata->trainingtype = $data->trainingtype?implode(',',$data->trainingtype):null;
        $tpdata->hour = $data->hour;
        $tpdata->sectors = implode(',', array_filter($data->sectors));
        $tpdata->trainingtopics = implode(',', array_filter($data->trainingtopics));
        $tpdata->prerequirementsprograms = implode(',', array_filter($data->prerequirementsprograms));
        $tpdata->postrequirementsprograms = implode(',', array_filter($data->postrequirementsprograms));
        $tpdata->clevels =  $data->clevels;

        $tpdata->usercreated  =  $USER->id;
        $tpdata->timecreated =  time();

        $tpdata->classification  =  $data->classification; //Added by renu verma
        $tpdata->programnature  =  $data->pnature; //Added by renu verma
        $tpdata->termsconditions =  $data->termsconditions; //Added by renu verma
        $tpdata->termsconditionsarea =  $data->termsconditionsarea['text']; //Added by renu verma

      
        if($data->alltargetgroup == 1 || $data->targetgroup == '-1') {
            $tpdata->targetgroup = '-1';
        } else if($data->notappliedtargetgroup == 1) {
            $tpdata->targetgroup = '0';
        } else {
            $tpdata->targetgroup = implode(',',array_filter($data->targetgroup));
        }
        if($data->newjobfamilyoption) {
            $tpdata->newjobfamilyoption = $data->newjobfamilyoption;
        }
      
        $tpdata->competencyandlevels = implode(',', array_filter($data->competencylevel));

        if(empty($data->code)) {
            $tpdata->code = self::generate_program_code($data);

        } else {

            $tpdata->code = $data->code;
        }

        $tpdata->attendancecmpltn = $data->attendancecmpltn;
        $tpdata->attendancepercnt =($data->attendancecmpltn) ? $data->attendancepercnt : 0;
       
        if($data->is_published) {
           $tpdata->published = ((int) $data->published == 1) ? 1 : 0;
        }

        

        $course = new stdClass();
        $course->fullname = '{mlang en}'.$data->name.'{mlang}{mlang ar}'.$data->namearabic.'{mlang}';
        $course->shortname = $tpdata->code;
        $course->groupmode = 1;
        $course->format = 'fatopics';
        $course->startdate = $data->availablefrom;
        $course->enddate = $data->availableto;
        $course->category = self::get_category();

        try{

            //set default value for completion
            $courseconfig = get_config('moodlecourse');
            if (\completion_info::is_enabled_for_site()) {
                $course->enablecompletion = 1;
            } else {
                $course->enablecompletion = 0;
            }

            $course = create_course($course);
            $tpdata->courseid = $course->id;

            if($data->evaluationmethod){

                $modinfo = [
                    'modulename' => 'quiz',
                    'gradepass'=>0,
                    'grade'=>10,
                    'gradecat'=>$course->category,
                    'course' => $tpdata->courseid,
                    'section' => 0,
                    'visible' => 1,
                    'quizpassword'=>0,
                    'completion' => 2,
                    'completiongradeitemnumber'=>0,
                    'cmidnumber' => '',
                    'preferredbehaviour'=>'deferredfeedback',
                    'introeditor' => ['text' => '', 'format' => FORMAT_HTML, 'itemid' => null],
            ];

                $evlmethodsfields = dataprovider::$evaluationmethods;

                $evlmethods = array_values(dataprovider::$evaluationmethods);


               

            }

        } catch(moodle_exception $e){
            print_r($e);
        }
       try{
          $createdid =  $DB->insert_record('local_trainingprogram', $tpdata); 
          $event = \local_trainingprogram\event\trainingprogram_created::create(array( 'context'=>$context, 'objectid' =>$createdid));
          $event->trigger();
                $tpdata->program_name=$tpdata->name; 
                $tpdata->program_arabicname=$tpdata->namearabic;
                $tpdata->id  = $createdid;

                    (new \local_trainingprogram\notification())->trainingprogram_notification('trainingprogram_create', $touser=null,$fromuser=$USER,$tpdata,$waitinglistid=0);
          return $createdid;
       } catch(dml_exception $e){
            print_r($e);
       }
    }

    public function update_program($data) {
        global $DB,$USER;
        $tpdata = new stdClass();
    
        $context = context_system::instance();

        $tpdata->id = $data->id;
        $tpdata->name = trim($data->name);
        $tpdata->namearabic = trim($data->namearabic);
        if($data->externallinkcheck == 0 && $data->externallink !=""){
        $tpdata->externallink = "";
        }else{
        $tpdata->externallink = $data->externallink;
        }
       $tpdata->externallinkcheck = $data->externallinkcheck;
        $tpdata->image = $data->image ? $data->image : 0;
        if(!is_null($data->oldid)) {

            $tpdata->oldid = $data->oldid;

        }
        $tpdata->price = $data->cost ? $data->cost : 0;
        if($data->cost == 1) {
           $tpdata->sellingprice = $data->sellingprice;
           $tpdata->actualprice = $data->actualprice;
           $tpdata->tax_free = $data->tax;
        } else {
           $tpdata->sellingprice = 0;
           $tpdata->actualprice = 0;
           $tpdata->tax_free = null;
        }
        $tpdata->description = $data->description['text'];
        $tpdata->languages= implode(',', $data->language);
        $tpdata->methods = implode(',',$data->programmethod);
        $tpdata->evaluationmethods = $data->evaluationmethod?implode(',',$data->evaluationmethod):null;
        $tpdata->dynamicevaluationmethod = $data->dynamicevaluationmethod?implode(',',$data->dynamicevaluationmethod):null;
        $tpdata->duration = $data->duration;
        $tpdata->availableto = $data->availableto;
        $tpdata->availablefrom = $data->availablefrom;
        $tpdata->trainingtype = $data->trainingtype?implode(',',$data->trainingtype):null;
        $tpdata->hour = $data->hour;
        $tpdata->sectors = implode(',', array_filter($data->sectors));
        $tpdata->trainingtopics = implode(',', array_filter($data->trainingtopics));
        $tpdata->prerequirementsprograms = implode(',', array_filter($data->prerequirementsprograms));
        $tpdata->postrequirementsprograms = implode(',', array_filter($data->postrequirementsprograms));
        $tpdata->clevels = $data->clevels;
        if($data->alltargetgroup == 1 || $data->targetgroup == '-1') {
            $tpdata->targetgroup = '-1';
        } else if($data->notappliedtargetgroup == 1) {
            $tpdata->targetgroup = '0';
            $tpdata->newjobfamilyoption  = '';
        } else {
            $tpdata->targetgroup = implode(',',array_filter($data->targetgroup));
        }
        if($data->newjobfamilyoption) {
            $tpdata->newjobfamilyoption = $data->newjobfamilyoption;
        } else {
            $tpdata->newjobfamilyoption = '';
        }
        $tpdata->competencyandlevels =implode(',',array_filter($data->competencylevel));

        $tpdata->attendancecmpltn = $data->attendancecmpltn;
        $tpdata->attendancepercnt = $tpdata->attendancepercnt =($data->attendancecmpltn) ? $data->attendancepercnt : 0;

        if($data->is_published) {
           $tpdata->published = ((int) $data->published == 1) ? 1 : 0;
        }

        $tpdata->usermodified  =  $USER->id;
        $tpdata->timemodified =  time();
        
        $tpdata->classification  =  $data->classification; //updated by renu verma
        $tpdata->programnature  =  $data->pnature; //updated by renu verma
        $tpdata->termsconditions =  $data->termsconditions; //updated by renu verma
        $tpdata->termsconditionsarea =  $data->termsconditionsarea['text']; //updated by renu verma

        $course = new stdClass();
        $course->id = $data->courseid;
        $course->fullname = '{mlang en}'.$data->name.'{mlang}{mlang ar}'.$data->namearabic.'{mlang}';
       // $course->summary = $data->description['text'];
        $course->startdate = $data->availablefrom;
        $course->enddate = $data->availableto;
        $course->category = self::get_category();

        try{
             //set default value for completion
            $courseconfig = get_config('moodlecourse');
            if (\completion_info::is_enabled_for_site()) {
                $course->enablecompletion = 1;
            } else {
                $course->enablecompletion = 0;
            }

            update_course($course);
            $tpdata->courseid = $data->courseid;

            $modinfo = [
                    'modulename' => 'quiz',
                    'gradepass'=>0,
                    'grade'=>10,
                    'gradecat'=>$course->category,
                    'course' => $course->id,
                    'section' => 0,
                    'visible' => 1,
                    'quizpassword'=>0,
                    'completion' => 2,
                    'completiongradeitemnumber'=>0,
                    'cmidnumber' => '',
                    'preferredbehaviour'=>'deferredfeedback',
                    'introeditor' => ['text' => '', 'format' => FORMAT_HTML, 'itemid' => null],
             ];


            $evlmethodsfields = dataprovider::$evaluationmethods;

            $evlmethods = array_values(dataprovider::$evaluationmethods);

            
        } catch(moodle_exception $e){
            print_r($e);
        }
        
        try{
            if(empty($data->code)) {
                $tpdata->code = self::generate_program_code($data);
    
            } else {
    
                $tpdata->code = $data->code;
            }
               $DB->update_record('local_trainingprogram', $tpdata); 
               $tplanguages=count($data->language);
                if($tplanguages < 2){
                    $offdata = new stdClass();
                    $offeringlang=implode(',',$data->language);
                    $offdata->trainingid =$data->id;
                    $offdata->languages =$offeringlang;
                    $gettpids = $DB->get_records_sql('select id,trainingid from {tp_offerings} where trainingid='.$data->id.'');
                    foreach($gettpids as $offeringid){
                      $offdata->id = $offeringid->id;
                     $DB->update_record('tp_offerings', $offdata); 
                    }
            }
           $days = $tpdata->duration / 86400;

            if($DB->record_exists('program_agenda',['programid'=>$tpdata->id])) {
              $DB->execute('DELETE FROM {program_agenda} WHERE day >'.$days.' AND programid='.$tpdata->id.'');
            }

           $event = \local_trainingprogram\event\trainingprogram_updated::create(array( 'context'=>$context, 'objectid' => $data->id));
           $event->trigger();
           $tpdata->program_name=$tpdata->name; 
           $tpdata->program_arabicname = $data->namearabic;                     
               (new \local_trainingprogram\notification())->trainingprogram_notification('trainingprogram_update', $touser=null,$fromuser=$USER,$tpdata,$waitinglistid=0);
         
       } catch(dml_exception $e){
            print_r($e);
       }
    }

    public function get_trainingprograms() {
        global $DB, $PAGE, $OUTPUT;
        $systemcontext = context_system::instance();
        $renderer = $PAGE->get_renderer('local_trainingprogram');
        $training_programs = $renderer->get_catalog_trainingprograms();
    }

    public function get_programdata($stable, $filterdata){
        global $DB,$CFG;

        $datecondition = (new trainingprogram())->from_unixtime_for_live_entities('lo.availableto');
        $sql = "SELECT lo.id, lo.name,lo.namearabic,lo.image,lo.description,lo.sectors, lo.sellingprice,lo.duration, lo.hour,lo.availableto
                  FROM {local_trainingprogram} lo  
                 WHERE lo.published=1 AND lo.id NOT IN (SELECT programid FROM {program_completions} WHERE  completion_status = 1) AND $datecondition "; 

        if($filterdata->sector){
            if($filterdata->sector == 'insurance') {
                $sector = "I";
            } elseif($filterdata->sector == 'finance'){
                $sector = "F";
            } elseif($filterdata->sector == 'banking'){
                $sector = "B";
            } elseif($filterdata->sector == 'capitalmarket'){
                $sector = "V";
            } else {
              $sector = $filterdata->sector;
            }
            $sectorid = $DB->get_field('local_sector', 'id', ['code' => $sector]);
            if($sectorid){
                $sql .=' AND FIND_IN_SET('.$sectorid.', lo.sectors) ';
            }else{
                $othersectors = $DB->get_fieldset_sql('select id from {local_sector} where  code not in("F", "V", "B", "I")');
                if(empty($othersectors)){
                    return [];
                }
                $sectorlist = implode(',', $othersectors);
                $sql .=' AND  lo.sectors not in('.$sectorlist.')';
            }
            
        }
        $programs = $DB->get_records_sql($sql, array(), $stable->start,$stable->length);

        $programdata = [];
        foreach($programs as $program){
          $B = false;
          $V = false;
          $F = false;
          $I = false;
            if (!empty($program->image)) {
                $programimageurl =trainingprogramlogo_url($program->image);
            }else{
                $programimageurl ='';
            }
            if($program->sectors){
                $programsectors = $DB->get_fieldset_sql('select code from {local_sector} where id in('.$program->sectors.')');
                $listedsectors = ['V', 'F', 'I', 'B'];
                foreach($listedsectors as $sector){
                    if(in_array($sector, $programsectors)){
                        ${$sector} = true;
                    }
                }
            }else{
                $programsectors = '';
            }
            if(current_language() == 'ar'){
                $programname = $program->namearabic;
            }else{
                $programname = $program->name;
            }
            $programdata[] = ['id' => $program->id,
                              'title' => $programname,
                              'description' => mb_substr(strip_tags(format_text($program->description, FORMAT_HTML)),0, 200),
                              'sellingprice' => $program->sellingprice,
                              'imageurl'=> $programimageurl,
                              'capitalmarket' => $V,
                              'finance' => $F,
                              'insurance' => $I,
                              'banking' => $B,
                              'hours' =>round($program->hour / 86400),
                              'durationindays' => round($program->duration / 86400),
                              'rooturl' =>$CFG->wwwroot
                            ];
        }
        return $programdata;

    }


    public function get_listof_officials($query = null,$programid = 0,$ctype = null,$offeringid = 0) {
        global $DB;
        $roleid= $DB->get_field('role', 'id', array('shortname' => 'to'));
        $fields = array("lc.firstname",'lc.lastname','lc.firstnamearabic','lc.lastnamearabic','lc.middlenamearabic','lc.thirdnamearabic','lc.middlenameen','lc.thirdnameen');
        $likesql = array();
        $i = 0;

        foreach ($fields as $field) {
            $i++;
            $likesql[] = $DB->sql_like($field, ":queryparam$i", false);
            $sqlparams["queryparam$i"] = "%$query%";
        }
        $sqlfields = implode(" OR ", $likesql);
        $concatsql = " AND ($sqlfields) ";
        $currentlang= current_language();

        $displaying_name = (new trainingprogram)->user_fullname_case();
        
        $sql = " SELECT u.id,$displaying_name
        FROM {user} u
         JOIN  {local_users} lc ON lc.userid = u.id
        JOIN {role_assignments} ra ON ra.userid =  u.id
        JOIN {role} r ON r.id = ra.roleid
        WHERE u.deleted = 0 AND lc.deleted =0 AND r.id = $roleid  AND u.id $concatsql ";

        $order = " ORDER BY u.id DESC limit 50";
        $data = $DB->get_records_sql($sql.$order,$sqlparams);
        $return = array_values(json_decode(json_encode(($data)), true));
        return $return;
    }

    private function program_rolewise_query($filterdata) {
        global $USER,$DB;
        $systemcontext = context_system::instance();

        $traineeroleid = $DB->get_field('role', 'id', array('shortname' => 'trainee'));
        if(!$filterdata->status) {

            $inprogressenddatecondition =(new trainingprogram())->from_unixtime_for_live_entities('lo.availableto');
            if(is_siteadmin() || has_capability('local/organization:manage_trainingofficial',$systemcontext) || has_capability('local/organization:manage_communication_officer',$systemcontext) || has_capability('local/organization:manage_financial_manager',$systemcontext)
               ){
                $where = " WHERE  1=1 AND $inprogressenddatecondition ";
                // if(!is_siteadmin() && has_capability('local/organization:manage_organizationofficial',$systemcontext)){
                //     $where.= " AND lo.id NOT IN (SELECT tpofr.trainingid 
                //                     FROM {tp_offerings} AS tpofr
                //                     JOIN {tool_order_approval_seats} AS ordaprst ON ordaprst.tablename='tp_offerings' AND ordaprst.fieldname='id' AND ordaprst.fieldid=tpofr.id AND ordaprst.orguserid=$USER->id) ";
                // }
            }elseif(!is_siteadmin() && has_capability('local/organization:manage_organizationofficial',$systemcontext)){

                 // $where = " WHERE  1=1 AND $inprogressenddatecondition AND
                 //   CASE
                 //        WHEN lo.price = 1  THEN  lo.published = 1
                 //        ELSE lo.id NOT IN (SELECT programid FROM {program_enrollments} WHERE programid = lo.id AND usercreated = $USER->id) AND lo.published = 1
                 //    END 
                 // ";

                $where = " WHERE  1=1 AND $inprogressenddatecondition AND lo.published = 1 ";
                if($filterdata->favourites){
                    $where .= " LEFT JOIN {favourite} fav ON lo.id = fav.itemid";
  
                   }
                //$where = " WHERE  date(FROM_UNIXTIME(lo.availableto)) >= CURDATE()  AND lo.published = 1 ";

            } else if (!is_siteadmin() && has_capability('local/organization:manage_trainee',$systemcontext)) {
                $traineeid = $DB->get_field('role', 'id', ['shortname' => 'trainee']);
                $where = " ";
                if($filterdata->favourites){
                    $where .= " LEFT JOIN {favourite} fav ON lo.id = fav.itemid";

                }
                $where .= " JOIN {program_enrollments} ue ON ue.programid=lo.id AND  ue.courseid=lo.courseid WHERE ue.roleid = {$traineeid} AND ue.userid = ".$USER->id." AND  ue.enrolstatus = 1 AND lo.id NOT IN (SELECT pc.programid FROM {program_completions} pc WHERE pc.userid = ".$USER->id."  AND pc.completion_status = 1) ";
            } else if(!is_siteadmin() && has_capability('local/organization:manage_trainer', $systemcontext)) {
                // && !user_has_role_assignment($USER->id,$traineeroleid,$systemcontext->id)
                $trainerid = $DB->get_field('role', 'id', ['shortname' => 'trainer']);
                $where = " JOIN {program_enrollments} ue ON ue.programid=lo.id AND ue.courseid=lo.courseid WHERE ue.roleid = {$trainerid} AND ue.userid = ".$USER->id." AND $inprogressenddatecondition ";

            } else {
                $traineeid = $DB->get_field('role', 'id', ['shortname' => 'trainee']);
                $where = " ";
                if($filterdata->favourites){
                    $where .= " LEFT JOIN {favourite} fav ON lo.id = fav.itemid";

                }
                $where .= " JOIN {program_enrollments} ue ON ue.programid=lo.id AND  ue.courseid=lo.courseid WHERE ue.roleid = {$traineeid} AND ue.userid = ".$USER->id." AND  ue.enrolstatus = 1 AND lo.id NOT IN (SELECT pc.programid FROM {program_completions} pc WHERE pc.userid = ".$USER->id."  AND pc.completion_status IN (1,2)) ";
               
            }
        }else{
           
            $expiredenddatecondition =(new trainingprogram())->from_unixtime_for_expired_entities('lo.availableto');

            if(is_siteadmin() || has_capability('local/organization:manage_trainingofficial',$systemcontext) || has_capability('local/organization:manage_communication_officer',$systemcontext) 
               || has_capability('local/organization:manage_organizationofficial',$systemcontext) || has_capability('local/organization:manage_financial_manager',$systemcontext)){

                $where= " WHERE 1=1 AND $expiredenddatecondition"; 
       

            }elseif(!is_siteadmin() && has_capability('local/organization:manage_organizationofficial',$systemcontext) ){
                $where = " ";
                 if($filterdata->favourites){
                    $where .= " LEFT JOIN {favourite} fav ON lo.id = fav.itemid";

                   }
                $where .= " WHERE 1=1 AND $expiredenddatecondition";

            }  else if(!is_siteadmin() && has_capability('local/organization:manage_trainer', $systemcontext) && !user_has_role_assignment($USER->id,$traineeroleid,$systemcontext->id)) {

                $where = " JOIN {program_enrollments} ue ON ue.programid=lo.id AND ue.courseid=lo.courseid WHERE ue.userid = ".$USER->id." AND  ue.enrolstatus = 1 AND $expiredenddatecondition ";
             
            } else {
                $where = " ";
                 if($filterdata->favourites){
                    $where .= " LEFT  JOIN {favourite} fav ON lo.id = fav.itemid";

                   }
                $where .= " JOIN {program_completions} pc ON pc.programid = lo.id 
                          WHERE  pc.userid = ".$USER->id." AND pc.completion_status IN (1,2)";
            }
        }
        return $where;
    }
    private function programs_filter_query($filterdata) {
      global $USER,$DB;
        if(isset($filterdata->search_query) && trim($filterdata->search_query) != ''){
            $formsql = " AND (lo.name LIKE :firstnamesearch OR lo.namearabic LIKE :arabicnamesearch OR lo.code LIKE :codesearch) ";
            $searchparams = array(
                'firstnamesearch' => '%'.trim($filterdata->search_query).'%',
                'arabicnamesearch' => '%'.trim($filterdata->search_query).'%',
                'codesearch' => '%'.trim($filterdata->search_query).'%'
            );
        } else {
            $searchparams = array();
        }

        if (!empty($filterdata->sectors)){
             $sectorids = explode(',', $filterdata->sectors);
             if(!empty($sectorids)){
                $sectorquery = array();
                foreach ($sectorids as $sector) {
                    $sectorquery[] = " CONCAT(',',lo.sectors,',') LIKE CONCAT('%,',$sector,',%') "; 
                }
                $sectoqueeryparams =implode('OR',$sectorquery);
                $formsql .= ' AND ('.$sectoqueeryparams.') ';
            }
        }
        
        if(!empty($filterdata->targetgroup)){

            $jobfamilyids = explode(',', $filterdata->targetgroup);
            if(!empty($jobfamilyids)){
                $jobfamilyquery = array();
                foreach ($jobfamilyids as $jobfamily) {
                    $jobfamilyquery[] = " CONCAT(',',lo.targetgroup,',') LIKE CONCAT('%,',$jobfamily,',%') "; 
                }
                $jobfamilyparams =implode('OR',$jobfamilyquery);
                $formsql .= ' AND ( ('.$jobfamilyparams.') OR lo.targetgroup = -1 ) ';
            }
        }

        if(!empty($filterdata->program_competencylevel)){

            $competencylevelsids = explode(',', $filterdata->program_competencylevel);
            if(!empty($competencylevelsids)){
                $competencylevelquery = array();
                foreach ($competencylevelsids as $competencylevel) {
                    $competencylevelquery[] = " CONCAT(',',lo.competencyandlevels,',') LIKE CONCAT('%,',$competencylevel,',%') "; 
                }
                $competencylevelparams =implode('OR',$competencylevelquery);
                $formsql .= ' AND ('.$competencylevelparams.') ';
            }
        }
    
        if($filterdata->{'availablefrom[enabled]'} == 1 && empty($filterdata->{'availableto[enabled]'})){

            $start_year = $filterdata->{'availablefrom[year]'};
            $start_month = $filterdata->{'availablefrom[month]'};
            $start_day = $filterdata->{'availablefrom[day]'};
            $filter_starttime_con = mktime(0,0,0, $start_month, $start_day, $start_year);
            // $formsql.= " AND lo.availablefrom >= '$filter_starttime_con' ";
            $formsql.= "AND  lo.id IN (SELECT  trainingid FROM {tp_offerings} WHERE startdate >= '$filter_starttime_con' AND trainingmethod != 'elearning' ) ";

        }

        if($filterdata->{'availableto[enabled]'} == 1 && empty($filterdata->{'availablefrom[enabled]'})){

            $start_year = $filterdata->{'availableto[year]'};
            $start_month = $filterdata->{'availableto[month]'};
            $start_day = $filterdata->{'availableto[day]'};
            $filter_endtime_con=mktime(23,59,59, $start_month, $start_day, $start_year);
            // $formsql.=" AND lo.availableto <= '$filter_endtime_con' ";
            $formsql.=" AND  lo.id IN (SELECT  trainingid FROM {tp_offerings} WHERE enddate <= '$filter_endtime_con' AND trainingmethod != 'elearning' )";
        }

        if ($filterdata->{'availablefrom[enabled]'} == 1  && $filterdata->{'availableto[enabled]'} == 1 ) {    
                
            $start_year = $filterdata->{'availablefrom[year]'};
            $start_month = $filterdata->{'availablefrom[month]'};
            $start_day = $filterdata->{'availablefrom[day]'};
            $filter_starttime_con = mktime(0,0,0, $start_month, $start_day, $start_year);

            $start_year = $filterdata->{'availableto[year]'};
            $start_month = $filterdata->{'availableto[month]'};
            $start_day = $filterdata->{'availableto[day]'};
            $filter_endtime_con=mktime(23,59,59, $start_month, $start_day, $start_year);

            $formsql.= " AND  lo.id IN (SELECT  trainingid FROM {tp_offerings} WHERE published = 1 AND startdate >= '$filter_starttime_con' AND enddate <= '$filter_endtime_con' AND trainingmethod != 'elearning' )";
        }

        if (!empty($filterdata->training_name)){ 
            $trainings = explode(',',$filterdata->training_name);
             if(!empty($trainings)){
                $trainingquery = array();
                foreach ($trainings as $training) {
                    $trainingquery[] = " CONCAT(',',lo.id,',') LIKE CONCAT('%,',$training,',%') "; 
                }
                $trainingqueeryparams =implode('OR',$trainingquery);
                $formsql .= ' AND ('.$trainingqueeryparams.') ';
            }
        } 


        if (!empty($filterdata->offering_status)){ 
            $trainings = explode(',',$filterdata->offering_status);
             if(!empty($trainings)){
                $trainingquery = array();
                foreach ($trainings as $training) {
                    if($training == 'inprogress') {
                      $trainingquery[] = "  lo.published = 1 AND lo.id IN (SELECT trainingid FROM {tp_offerings} WHERE (startdate+time) >= UNIX_TIMESTAMP(DATE_ADD(NOW(), INTERVAL +2 DAY)) AND cancelled IN(0,3)) "; 
                    } elseif($training == 'financially_closed') {
                      $trainingquery[] = " lo.id IN (SELECT trainingid FROM {tp_offerings} WHERE financially_closed_status = 1) ";                   
                    } elseif($training == 'cancelled') {
                     $trainingquery[] = " lo.id IN (SELECT trainingid FROM {tp_offerings} WHERE cancelled = 1) "; 
                    } elseif($training == 'completed') {
                      $trainingquery[] = " lo.id IN (SELECT programid FROM {program_completions} WHERE  completion_status = 1) "; 
                    }

                }
                $trainingqueeryparams =implode('OR',$trainingquery);
                $formsql .= ' AND ('.$trainingqueeryparams.') ';
            }
        } 

        if (!empty($filterdata->offering_type)){ 
            $trainings = explode(',',$filterdata->offering_type);
             if(!empty($trainings)){
                $trainingquery = array();
                foreach ($trainings as $training) {
                    $trainingquery[] = " lo.id IN (SELECT trainingid FROM {tp_offerings} WHERE  trainingmethod = '$training' ) "; 
                }
                $trainingqueeryparams =implode('OR',$trainingquery);
                $formsql .= ' AND ('.$trainingqueeryparams.') ';
            }
        } 

        if (!empty($filterdata->offering_creator)){ 
            $trainings = explode(',',$filterdata->offering_creator);
             if(!empty($trainings)){
                $trainingquery = array();
                foreach ($trainings as $training) {
                    $trainingquery[] = " lo.id IN (SELECT trainingid FROM {tp_offerings} WHERE  usercreated = $training) "; 
                }
                $trainingqueeryparams =implode('OR',$trainingquery);
                $formsql .= ' AND ('.$trainingqueeryparams.') ';
            }
        } 
        if ($filterdata->offering_period != NULL){ 
            $offeringperiod = explode(',',$filterdata->offering_period);
             if(!empty($offeringperiod)){
                $offeringperiodquery = array();
                foreach ($offeringperiod as $offeringperiod) {
                    $offeringperiodquery[] = " lo.id IN (SELECT trainingid FROM {tp_offerings} WHERE  type = $offeringperiod) "; 
                }
                $offeringqueeryparams =implode('OR',$offeringperiodquery);
                $formsql .= ' AND ('.$offeringqueeryparams.') ';
            }
        }
        if (!empty($filterdata->organization)){ 
            $organization = explode(',',$filterdata->organization);
             if(!empty($organization)){
                $organizationquery = array();
                foreach ($organization as $organizations) {
                    $organizationquery[] = " lo.id IN (SELECT trainingid FROM {tp_offerings} WHERE type = 1 AND organization = $organizations ) "; 
                }
                $organizationqueeryparams =implode('OR',$organizationquery);
                $formsql .= ' AND ('.$organizationqueeryparams.') ';
            }
        }

        // echo $formsql;die();
         if(!empty($filterdata->favourites) ){
                $formsql.="  AND fav.component = 'local_trainingprogram'  AND  fav.userid='$USER->id' ";
            } 
        return array($formsql, $searchparams);
    }

    
    public function get_listof_programs($stable, $filterdata) {
        global $DB, $PAGE, $OUTPUT, $CFG, $USER,$SESSION;
        $SESSION->lang = ($stable->mlang) ? $stable->mlang : current_language() ;
        $trainingprogramlist = [];
        if ($filterdata->status == 2) {

            $filterdata->status = 'local_trainingprogram';
            $renderer = $PAGE->get_renderer('tool_product');
            $products = $renderer->lis_org_purchases($stable,$filterdata);
            $totalprograms = COUNT($products);

        } elseif($filterdata->status == 3) {

            $filterdata->status = 'local_trainingprogram';
            $filterdata->type = 'program';
            $filterdata->view = 'mobile';
            $filtervalues = (array)$filterdata;
            $recommendedprograms = (new manageuser)->recommendedentities($stable, $filtervalues);
            $totalprograms = $recommendedprograms['totalentities'];

        } else {
        
            $lang= current_language();
            $systemcontext = context_system::instance();
            $selectsql = "SELECT lo.id as traineeid,lo.name as programname,lo.namearabic,lo.availableto,lo.availablefrom,
                                 lo.image,lo.courseid,lo.description,lo.sectors,lo.targetgroup,
                                 lo.competencyandlevels,lo.published,lo.duration,lo.code, lo.duration, lo.sellingprice,lo.price, lo.newjobfamilyoption
                            FROM {local_trainingprogram} lo";

            $countsql  = "SELECT COUNT(lo.id) FROM {local_trainingprogram} lo";

        
            $where = $this->program_rolewise_query($filterdata);
            list($formsql, $searchparams) = $this->programs_filter_query($filterdata);

            $params = array_merge($searchparams);
        
            $totalprograms = $DB->count_records_sql($countsql.$where.$formsql,$params);
            //if(empty($filterdata->tporoff) && empty($filterdata->alphabatesort)){
           //  //   $formsql .=" ORDER BY lo.id DESC";
           // }
            if(current_language() == 'ar'){
                $prgname= 'ltrim(lo.namearabic)';
            }else{
                $prgname= 'ltrim(lo.name)';
            }
            $formsql .= " ORDER BY ";
            if($filterdata->tporoff == 1 && $filterdata->startdatesort == 1) {
             $formsql .="(lo.availablefrom+lo.hour) ASC";
            }
             else if($filterdata->tporoff == 1 && $filterdata->startdatesort == 2) {
             $formsql .=" (lo.availablefrom+lo.hour) DESC";           
            }
             else if($filterdata->tporoff == 1 && $filterdata->startdatesort == 3) {
             $formsql .=" $prgname ASC";
            }
             else if($filterdata->tporoff == 1 && $filterdata->startdatesort == 4) {
             $formsql .=" $prgname DESC";
            }

            /*
            else if($filterdata->tporoff == 1 && $filterdata->startdatesort == 1 && $filterdata->alphabatesort == 1) {
             $formsql .=" lo.name ASC,(lo.availablefrom+lo.hour) ASC";
            }
             else if($filterdata->tporoff == 1 && $filterdata->startdatesort == 2 && $filterdata->alphabatesort == 2) {
             $formsql .=" lo.name DESC,(lo.availablefrom+lo.hour) DESC";
            }
             else if($filterdata->tporoff == 1 && $filterdata->startdatesort == 1 && $filterdata->alphabatesort == 2) {
             $formsql .=" lo.name ASC,(lo.availablefrom+lo.hour) DESC";
            }
             else if($filterdata->tporoff == 1 && $filterdata->startdatesort == 2 && $filterdata->alphabatesort == 1) {
             $formsql .=" lo.name DESC,(lo.availablefrom+lo.hour) ASC";
            }*/
            else{
              $formsql .=" lo.id DESC";
            }
            if($stable->type == 'mobile') {
                $trainingprograms = $DB->get_records_sql($selectsql.$where.$formsql, $params);
            }else{
                $trainingprograms = $DB->get_records_sql($selectsql.$where.$formsql, $params, $stable->start,$stable->length);    
            }

            $count = 0;
            foreach($trainingprograms as $trainingprogram) {

                    if (!empty($trainingprogram->image)) {
                        $trainingprogramlist[$count]['image']=trainingprogramlogo_url($trainingprogram->image);
                    }

                    $enrolled = $DB->count_records_sql("SELECT COUNT(id) FROM {program_enrollments} WHERE programid = ".$trainingprogram->traineeid);

                    $trainingprogramlist[$count]['duration'] = round(($trainingprogram->duration / 86400));


                    $trainingprogramlist[$count]['fee']=!empty($trainingprogram->sellingprice) ? $trainingprogram->sellingprice : 0; 

                   $privateofferings = $DB->count_records('tp_offerings', ['trainingid' => $trainingprogram->traineeid, 
                                                                    'type' => dataprovider::PRIVATEPROGRAM]);

                   $nonprivateofferings = $DB->count_records_sql('SELECT COUNT(DISTINCT id) FROM {tp_offerings} WHERE trainingid = '.$trainingprogram->traineeid.' AND type <> 1');


                   $totalofferings = $DB->count_records_sql('SELECT COUNT(DISTINCT id) FROM {tp_offerings} WHERE trainingid = '.$trainingprogram->traineeid.'');

                   if($privateofferings == 1){
                        if(is_siteadmin() || has_capability('local/organization:manage_trainingofficial',$systemcontext) || has_capability('local/organization:manage_communication_officer',$systemcontext)
                            || has_capability('local/organization:manage_organizationofficial',$systemcontext)){
                            if(!is_siteadmin() && has_capability('local/organization:manage_organizationofficial',$systemcontext)){


                                $organization = $DB->get_field_sql("SELECT lu.organization
                                                                       FROM {local_users} as lu 
                                                                       JOIN {user} as u ON u.id= lu.userid
                                                                       JOIN {role_assignments} as  ra on ra.userid=u.id
                                                                       JOIN {role} as r ON ra.roleid = r.id  AND r.shortname = 'organizationofficial'
                                                                        AND u.id=$USER->id");
                                if(!$organization){
                                    continue;
                                }else{
                                    $offeringorg = $DB->get_field_sql('SELECT tpo.organization 
                                                                         FROM {tp_offerings} as tpo
                                                                        WHERE tpo.organization=:orgnization AND tpo.trainingid =:trainingid', 
                                                                            ['orgnization' => $organization, 'trainingid' => $trainingprogram->traineeid]);
                                    if($offeringorg != $organization){

                                        if($nonprivateofferings == 0 ||  $totalofferings == 0) {
                                            continue;
                                        }
                                        
                                    }
                                }
                            }
                        }/*else{
                            continue;
                        }*/
                    }

                    $trainingprogramlist[$count]['trainingid'] = $trainingprogram->traineeid;
                    
                   

                     $offering_record_data_sql = "SELECT tpo.id as offeringid, tpo.startdate,tpo.time,tpo.endtime, tpo.enddate,tpo.trainingmethod,tpo.financially_closed_status  FROM {local_trainingprogram} loc 
                     JOIN {program_enrollments} ue ON ue.programid=loc.id AND  ue.courseid =loc.courseid  
                     JOIN {tp_offerings} as tpo ON tpo.id=ue.offeringid
                     WHERE ue.userid =:userid AND  ue.programid=loc.id AND loc.id =:programid ";
                     $offering_record_data = $DB->get_record_sql($offering_record_data_sql,['userid' => $USER->id,'programid' => $trainingprogram->traineeid]);

                     $offering_record_data_exists = $DB->record_exists_sql($offering_record_data_sql,['userid' => $USER->id,'programid' => $trainingprogram->traineeid]);

                    $programenddate = date('Y-m-d',$trainingprogram->availableto);
                    $currdate = date('Y-m-d');


                    if(!is_siteadmin() && (has_capability('local/organization:manage_trainee',$systemcontext) || has_capability('local/organization:manage_trainer',$systemcontext))) {

                        $trainingprogramlist[$count]['availablefrom'] = userdate($offering_record_data->startdate, 
                                                                                get_string('strftimedatemonthabbr', 'core_langconfig'));
                        $trainingprogramlist[$count]['availableto'] = userdate($offering_record_data->enddate, 
                                                                                get_string('strftimedatemonthabbr', 'core_langconfig'));

                        $trainingprogramlist[$count]['timefrom'] = $offering_record_data->startdate;
                        $trainingprogramlist[$count]['timeto'] = $offering_record_data->enddate;

                        $trainingprogramlist[$count]['datedisplay']=($offering_record_data->trainingmethod  != 'elearning') ? true : false;

                        $trainingprogramlist[$count]["programdate"] = ($offering_record_data->startdate > 0 )? $offering_record_data->startdate: 0 ;
                        $sdate = strtotime(userdate($offering_record_data->startdate,'%Y-%m-%d'));
                        $curr_date = strtotime(userdate(time(),'%Y-%m-%d'));
                        $remainingdays = floor(($sdate - $curr_date) / (60 * 60 * 24));
                        $trainingprogramlist[$count]["isnotelearningmethod"] = ($offering_record_data->trainingmethod !='elearning' )? 1: 0 ;
                        $trainingprogramlist[$count]["remainingdays"] = ($trainingprogram->price == 1  && $offering_record_data->startdate > 0 )?$remainingdays: 0 ;
                        $offeringstartdate = ($offering_record_data->startdate+$offering_record_data->time);
                        $timestamp = strtotime(date('Y-m-d H:i'));
                        $totaltimeStamp = strtotime('+ 2 days', $timestamp);
                        $trainingprogramlist[$count]["cancelbuttonview"] = ($trainingprogram->price == 0 || ($trainingprogram->price == 1  && $offering_record_data->startdate > 0 && $remainingdays >= 1 && $offeringstartdate > $totaltimeStamp)) ? true: false;
                        if(!is_siteadmin() &&  has_capability('local/organization:manage_trainer',$systemcontext)) {
                            $trainingprogramlist[$count]["istrainee"]= false;
                        } else {
                            $trainingprogramlist[$count]["istrainee"]= true;
                        }
                        $trainingprogramlist[$count]["offeringid"]= $offering_record_data->offeringid;
                        $trainingprogramlist[$count]["traineeuserid"]= $USER->id;
                        $trainingprogramlist[$count]["offeringid"]= $offering_record_data->offeringid;
                        $localuserrecord=$DB->get_record('local_users',array('userid'=>$USER->id));
                        $program_userfullname_en=$localuserrecord->firstname .' '. $localuserrecord->middlenameen.' '. $localuserrecord->thirdnameen.' '. $localuserrecord->lastname;
                        $program_userarabicfullname_ar=$localuserrecord->firstnamearabic .' '. $localuserrecord->middlenamearabic.' '. $localuserrecord->thirdnamearabic.' '. $localuserrecord->lastnamearabic;
                        $trainingprogramlist[$count]["traineefullname"]= (current_language() == 'ar') ? $program_userarabicfullname_ar : $program_userfullname_en;
                        $courseid = $DB->get_field('local_trainingprogram','courseid',['id'=>$trainingprogram->traineeid]);
                        $enrolleduserid =(int) $DB->get_field('program_enrollments','usercreated',['programid'=>$trainingprogram->traineeid,'courseid'=>$courseid,'userid'=>$USER->id]);                    
                       $enrolleduseroleinfo = $DB->get_record_sql('SELECT rol.* FROM {role} rol 
                            JOIN {role_assignments} rola ON rola.roleid = rol.id
                            WHERE rola.userid =:userid and contextid =:contextid',['userid'=> $enrolleduserid,'contextid'=>$systemcontext->id]);
                        $trainingprogramlist[$count]["orgoffiicialenrolled"]=($enrolleduseroleinfo->shortname == 'organizationofficial') ? 1 : 0;
                        $trainingprogramlist[$count]["adminenrolled"]=(!$enrolleduseroleinfo) ? 1 : 0;

                        if (!is_siteadmin() && has_capability('local/organization:manage_trainee', $systemcontext)  && (empty($enrolleduseroleinfo->shortname)  || $enrolleduseroleinfo->shortname == 'organizationofficial' || $enrolleduseroleinfo->shortname == 'to' || $enrolleduseroleinfo->shortname == 'co' || $offering_record_data->financially_closed_status == 1)) {
                            $trainingprogramlist[$count]["disableallactions"] = true;
                        } else {
                            $trainingprogramlist[$count]["disableallactions"] = false;
                        }
                        $productid =(int) $DB->get_field_sql('SELECT tlp.id FROM {tool_products} tlp 
                        JOIN {local_trainingprogram} lot ON lot.code = tlp.code 
                        WHERE tlp.category =:category AND tlp.referenceid =:referenceid',['category'=>1,'referenceid'=>$offering_record_data->offeringid]);
                        $trainingprogramlist[$count]["productid"] =($productid) ? $productid : 0;
            


                    } else {
                        
                        if(!is_siteadmin() &&  has_capability('local/organization:manage_organizationofficial',$systemcontext)) {
                        
                        $trainingprogramlist[$count]["traineeuserid"]=  $USER->id;
                        }else{
                         $trainingprogramlist[$count]["traineeuserid"]=0;
                        }
                      
                        $trainingprogramlist[$count]['availablefrom'] = userdate($trainingprogram->availablefrom, 
                                                                            get_string('strftimedatemonthabbr', 'core_langconfig'));
                        $trainingprogramlist[$count]['availableto'] = userdate($trainingprogram->availableto, 
                                                                            get_string('strftimedatemonthabbr', 'core_langconfig'));

                        $trainingprogramlist[$count]['timefrom'] = $trainingprogram->availablefrom;
                        $trainingprogramlist[$count]['timeto'] = $trainingprogram->availableto;
                        $trainingprogramlist[$count]['datedisplay']= true;
                        $trainingprogramlist[$count]["programdate"] = ($trainingprogram->availablefrom > 0 )? $trainingprogram->availablefrom: 0 ;
                        $trainingprogramlist[$count]["isnotelearningmethod"] =  0 ;
                        $trainingprogramlist[$count]["remainingdays"] =  0 ;
                        $trainingprogramlist[$count]["cancelbuttonview"] =  false;
                        $trainingprogramlist[$count]["istrainee"]= false;
                        $trainingprogramlist[$count]["offeringid"]=0;
                        //$trainingprogramlist[$count]["traineeuserid"]=0;
                        $trainingprogramlist[$count]["traineefullname"]=null;

                        $trainingprogramlist[$count]["orgofficialenrolled"]= 0;
                        $trainingprogramlist[$count]["adminenrolled"]= 0;
                        $trainingprogramlist[$count]["disableallactions"] = false;

                        $trainingprogramlist[$count]["productid"] =0;
            


                    }


                    $trainingprogramlist[$count]["programprice"]=$trainingprogram->price;
                    $trainingprogramlist[$count]["programdisplayname"]=(current_language() == 'ar') ? $trainingprogram->namearabic : $trainingprogram->programname;
                                   
                    if($trainingprogram->sectors != null && $trainingprogram->sectors !=0){
                            
                        $trainingprogramlist[$count]['sectorexists'] =true;        
                    }else{
                       
                        $trainingprogramlist[$count]['sectorexists'] =false;  
                    }
                    list($sectorsql,$sectorparams) = $DB->get_in_or_equal(explode(',',$trainingprogram->sectors));
                    $currentlang= current_language();

                    if( $currentlang == 'ar' || $stable->isArabic == 'ar'){

                        $sectorquerysql = "SELECT id AS sectorid,titlearabic AS sectorname FROM {local_sector} WHERE titlearabic <> '' AND titlearabic IS NOT NULL AND  id $sectorsql";
                    } else {

                        $sectorquerysql = "SELECT id AS sectorid,title AS sectorname FROM {local_sector} WHERE id $sectorsql";
                    }
                    
                    $sectorslists= $DB->get_records_sql($sectorquerysql,$sectorparams);

                    if(!empty($sectorslists)){
                        $trainingprogramlist[$count]['sectors'] = array_values($sectorslists);
                    } else {
                        $trainingprogramlist[$count]['sectors']  = array();
                    }  

                    if($trainingprogram->targetgroup != null && !empty($trainingprogram->targetgroup) && $trainingprogram->targetgroup != '' || $trainingprogram->newjobfamilyoption != null && !empty($trainingprogram->newjobfamilyoption) && $trainingprogram->newjobfamilyoption != ''){
                       
                        if($trainingprogram->targetgroup == '-1') {
                        
                         $jobfamilies = (new trainingprogram)->get_all_job_families_by_sectors($trainingprogram->sectors);
                         $trainingprogram->targetgroup = implode(',', $jobfamilies);

                        } else if ($trainingprogram->newjobfamilyoption && $trainingprogram->targetgroup != '0' && $trainingprogram->targetgroup != '-1' && $trainingprogram->targetgroup != null) {
                            $jobfamilies = (new trainingprogram)->get_all_job_families_by_sectors($trainingprogram->newjobfamilyoption);
                            $targetgroup = implode(',', $jobfamilies);
                            $targroup = $trainingprogram->targetgroup.','.$targetgroup;
                            $trainingprogram->targetgroup = $targroup;
                          
                        } else if ($trainingprogram->newjobfamilyoption) {
                            $jobfamilies = (new trainingprogram)->get_all_job_families_by_sectors($trainingprogram->newjobfamilyoption);
                            $trainingprogram->targetgroup = implode(',', $jobfamilies);
                          
                        } else {
                          $targroup = $trainingprogram->targetgroup;
                          $trainingprogram->targetgroup = $targroup;

                        }
                      
                        if($trainingprogram->targetgroup != null && !empty($trainingprogram->targetgroup) && $trainingprogram->targetgroup != ''){   

                             $total_jobfamily_count = $DB->count_records_sql('SELECT COUNT(id) FROM {local_jobfamily} WHERE id in('.$trainingprogram->targetgroup.')');

                             if( $currentlang == 'ar'|| $stable->isArabic == 'ar'){

                                $limitedjobfamilyquery = 'select familynamearabic AS familyname from {local_jobfamily} where familynamearabic <> "" AND familynamearabic IS NOT NULL AND id in('.$trainingprogram->targetgroup.') LIMIT 2';

                                $alljobbfamiliesquery = 'select familynamearabic AS familyname from {local_jobfamily} where familynamearabic <> "" AND familynamearabic IS NOT NULL AND id in('.$trainingprogram->targetgroup.') LIMIT '.$total_jobfamily_count.' OFFSET 2';
                                

                              } else {

                                $limitedjobfamilyquery = 'select familyname from {local_jobfamily} where id in('.$trainingprogram->targetgroup.') LIMIT 2';

                                 $alljobbfamiliesquery = 'select familyname from {local_jobfamily} where id in('.$trainingprogram->targetgroup.') LIMIT '.$total_jobfamily_count.' OFFSET 2';


                              } 

                            $limitedjobfamily = $DB->get_fieldset_sql($limitedjobfamilyquery);
                            $trainingprogramlist[$count]['limitedjobfamily'] = implode(',', $limitedjobfamily); 

                            $trainingprogramlist[$count]['moreactinview'] = ($total_jobfamily_count > 2) ? true : false;

                            $alljobbfamilies = $DB->get_fieldset_sql($alljobbfamiliesquery);
                            $trainingprogramlist[$count]['alljobbfamilies'] = implode(',', $alljobbfamilies);

                        } else {

                        $trainingprogramlist[$count]['limitedjobfamily'] = '';
                        $trainingprogramlist[$count]['alljobbfamilies'] = '';
                        $trainingprogramlist[$count]['moreactinview'] = false;
                      }
                   
                    }else{
                        $trainingprogramlist[$count]['limitedjobfamily'] = '';
                        $trainingprogramlist[$count]['alljobbfamilies'] = '';
                        $trainingprogramlist[$count]['moreactinview'] = false;
                    }
                    if($trainingprogram->competencyandlevels != null && $trainingprogram->competencyandlevels !=0){
                       $trainingprogramlist[$count]['competenciesexists']=true;
                    } else {
                       $trainingprogramlist[$count]['competenciesexists']=false;
                    }

                    $jobfamilies = $trainingprogramlist[$count]['alljobbfamilies'];

                    $jfamilies = [];
                    if(!empty($jobfamilies)) {

                        $families = explode(',', $jobfamilies);
                        foreach($families as $jfamily) {
                            $row = [];
                            $row['name'] = $jfamily;
                            $jfamilies[] = $row;
                        }
                    }
                    $trainingprogramlist[$count]['jobfamily'] = $jfamilies;
                    $trainingprogramlist[$count]['programstatus']=($trainingprogram->published == 0) ? get_string('un_published','local_trainingprogram'): get_string('published','local_trainingprogram');

                    $moduleid = $DB->get_field_sql('SELECT offeringid FROM {program_completions} WHERE programid ='.$trainingprogram->traineeid.' AND userid = '.$USER->id.' AND completion_status = 1');
                          
                    
                    $certid = $DB->get_field('tool_certificate_issues', 'code', array('moduleid'=>$moduleid,'userid'=>$USER->id,'moduletype'=>'trainingprogram'));
                    $trainingprogramlist[$count]['certid'] = $certid? $certid : '';

                    $trainingprogramlist[$count]['viewcertificateurl'] = $certid? $CFG->wwwroot.'/admin/tool/certificate/view.php?code='.$certid : '#';

                    $trainingprogramlist[$count]['certificateview'] = (!is_siteadmin() && $filterdata->status == 1 && has_capability('local/organization:manage_trainee', $systemcontext))? true : false;


                    $trainingprogramlist[$count]['currentofferingsexists'] =$DB->record_exists('tp_offerings', 
                                                                               ['trainingid' => $trainingprogram->traineeid]) ? true : false;         
                    $trainingprogramlist[$count]['published'] = $trainingprogram->published;
                    $trainingprogramlist[$count]['courseid'] = $trainingprogram->courseid; 
                    $trainingprogramlist[$count]['published'] = $trainingprogram->published == 0 ? true :false; 
                    $trainingprogramlist[$count]['deleteaction'] = $DB->record_exists('tp_offerings',
                                                                        array('trainingid' => $trainingprogram->traineeid))? true :false; 

                    $trainerroleid = $DB->get_field('role', 'id', array('shortname' => 'trainer'));

                    $currentlang= current_language();

                    if($currentlang == 'ar' || $SESSION->lang == 'ar') {

                        $displaying_name = "concat(lu.firstnamearabic,' ',lu.middlenamearabic,' ',lu.thirdnamearabic,' ',lu.lastnamearabic)";

                    } else {

                        $displaying_name = "concat(lu.firstname,' ',lu.middlenameen,' ',lu.thirdnameen,' ',lu.lastname)";
                    }

                  
                    $trainer_sql_query = "SELECT $displaying_name as fullname 
                                            FROM {program_enrollments} as pe
                                            JOIN {local_users} as lu ON pe.userid=lu.userid
                                           WHERE pe.roleid=$trainerroleid AND pe.programid = $trainingprogram->traineeid";

            
                    $trainers=$DB->get_fieldset_sql($trainer_sql_query);
                    $trainerslist = [];
                    foreach($trainers as $triner) {
                        $row = [];
                        $row['name'] = $triner;
                        $trainerslist[] = $row;
                    }

                   $trainingprogramlist[$count]['trainerslist'] = $trainerslist;

                   $trainingprogramlist[$count]['triners'] = $trainers ? implode(', ', $trainers):get_string('no_trainer','local_trainingprogram');

                    if( $lang == 'ar' || $SESSION->lang == 'ar'){
                            $programname = $trainingprogram->namearabic;
                    }else{
                            $programname = $trainingprogram->programname;
                    }

                    if($programenddate >= $currdate) {

                        if(!is_siteadmin() && (has_capability('local/organization:manage_trainee',$systemcontext)  || has_capability('local/organization:manage_trainer',$systemcontext))) {

                            $trainingprogramlist[$count]['tainingprogramname'] =html_writer::tag('a', $programname,array('href' =>$CFG->wwwroot. '/course/view.php?id='.$trainingprogram->courseid)) ;

                        } else {

                            if(!is_siteadmin() && has_capability('local/organization:manage_organizationofficial',$systemcontext)) {
                            
                            $offerings_exists = $DB->record_exists('tp_offerings',['trainingid' => $trainingprogram->traineeid,'published'=>1]);

                            $trainingprogramlist[$count]['tainingprogramname'] =($offerings_exists) ?  html_writer::tag('a', $programname,array('href' =>$CFG->wwwroot. '/local/trainingprogram/programcourseoverview.php?programid='.$trainingprogram->traineeid)) : $programname; 

                            } else {

                                $trainingprogramlist[$count]['tainingprogramname'] = html_writer::tag('a', $programname,array('href' =>$CFG->wwwroot. '/local/trainingprogram/programcourseoverview.php?programid='.$trainingprogram->traineeid)) ; 
                            }

                        }    

                        

                    }else {

                        if(!is_siteadmin() && has_capability('local/organization:manage_organizationofficial',$systemcontext)) {
                            $purchasedseats =$DB->get_field_sql("SELECT SUM(tos.purchasedseats) 
                            FROM {tool_org_order_seats} AS tos 
                            JOIN {tp_offerings} AS tpo ON tpo.id = tos.fieldid
                            WHERE tos.tablename = 'tp_offerings' AND tos.fieldname = 'id' AND tpo.trainingid = $trainingprogram->traineeid") ;

                          $trainingprogramlist[$count]['tainingprogramname'] = ($purchasedseats > 0) ?  html_writer::tag('a', $programname,array('href' =>$CFG->wwwroot. '/course/view.php?id='.$trainingprogram->courseid)) :  $programname ;

                        } elseif(!is_siteadmin() && has_capability('local/organization:manage_trainee',$systemcontext)) {


                            $trainingprogramlist[$count]['tainingprogramname'] =html_writer::tag('a', $programname,array('href' =>$CFG->wwwroot. '/course/view.php?id='.$trainingprogram->courseid)) ;

                        } else {
                          

                           $trainingprogramlist[$count]['tainingprogramname'] = html_writer::tag('a', $programname,array('href' =>$CFG->wwwroot. '/local/trainingprogram/programcourseoverview.php?programid='.$trainingprogram->traineeid)); 

                        }


                    }

                    $trainingprogramlist[$count]['name'] = $programname;
                    $trainingprogramlist[$count]['isorgofficial']=(!is_siteadmin() && has_capability('local/organization:manage_organizationofficial',$systemcontext)) ? true : false;
                    $trainingprogramlist[$count]['manageprogramactions']=((is_siteadmin() || 
                                                                            has_capability('local/organization:manage_trainingofficial',$systemcontext)) && 
                                                                            !$filterdata->status) ? true : false;

                    $trainingprogramlist[$count]['viewcurentofferingbutton']=(!is_siteadmin() &&
                                                                            has_capability('local/organization:manage_communication_officer',$systemcontext)
                                                                            )? true : false;



                   $trainingprogramlist[$count]['completedprofileactionview']=((is_siteadmin() || 
                                                                            has_capability('local/organization:manage_trainingofficial',$systemcontext)) && 
                                                                            $filterdata->status == 1 ) ? true : false;

                 $program_duration_in_days = $trainingprogram->duration/86400;

                 $trainingprogramlist[$count]['programagendaview']=($program_duration_in_days > 0) ? true : false;

                 if(!is_siteadmin() && (has_capability('local/organization:manage_trainee',$systemcontext)  || has_capability('local/organization:manage_trainer',$systemcontext))) {

                    $trainingprogramlist[$count]['detailedprogramviewurl'] = $CFG->wwwroot.'/local/trainingprogram/programcourseoverview.php?programid='.$trainingprogram->traineeid;

                } else {

                     $trainingprogramlist[$count]['detailedprogramviewurl'] = $CFG->wwwroot.'/local/trainingprogram/programdetailedview.php?programid='.$trainingprogram->traineeid;
                }
                $isorgofficial=(!is_siteadmin() && has_capability('local/organization:manage_organizationofficial',$systemcontext)) ? true : false;
                $istrainee=(!is_siteadmin() && (has_capability('local/organization:manage_trainee',$systemcontext)));   
                if($isorgofficial || $istrainee){
                 $trainingprogramlist[$count]['isorgofficialortrainee']= true;
                 }else{
                  $trainingprogramlist[$count]['isorgofficialortrainee']= false;
                 }
            
                 $component='local_trainingprogram';
                 $trainingprogramlist[$count]['checkfavornot'] =(new exams)->checkfavourites($trainingprogram->traineeid,$USER->id,$component);
                 if($filterdata->tporoff ==2 && $filterdata->startdatesort ==1){
                    $trainingprogramlist[$count]['sorting']=1;
                 }else if($filterdata->tporoff ==2 && $filterdata->startdatesort ==2){
                  $trainingprogramlist[$count]['sorting']=2;
                 }else{
                    $trainingprogramlist[$count]['sorting']=0;
                 }
                $trainingprogramlist[$count]['isfinancialmanager']=(!is_siteadmin() && has_capability('local/organization:manage_financial_manager',$systemcontext)) ? true : false; 

                $currentuserroleinfo = $DB->get_record_sql('SELECT rol.* FROM {role} rol 
                JOIN {role_assignments} rola ON rola.roleid = rol.id
                WHERE rola.userid =:userid and contextid =:contextid',['userid'=>$USER->id,'contextid'=>$systemcontext->id]);
                $trainingprogramlist[$count]['userroleshortname'] =(is_siteadmin()) ? 'admin' : $currentuserroleinfo->shortname;
                $trainingprogramlist[$count]['programcode'] = $trainingprogram->code;

                $count++;
            }
        }


        $coursesContext = array(
            "hascourses" => $trainingprogramlist,
            "products" => $products,
            "recommendedprograms" => $recommendedprograms['hascourses'],
            "totalprograms" => $totalprograms,
            "length" => count($trainingprogramlist),
        );
        return $coursesContext;
    }

    public static function get_category() {
        global $DB;
        try{
            $categoryid = $DB->get_field('course_categories', 'id', ['idnumber' => 'trainingprogram']);
         } catch(dml_exception $e){
            print_r($e);
       }
        return $categoryid;
    }

     public function add_update_schedule_program($data) {
        global $DB, $USER;
        $row = array();
        if($data->halllocation){
            $row['halllocation'] = $data->halllocation;
        }
        if($data->halllocation1){
            $row['halllocation'] = $data->halllocation1;
        }

        $systemcontext = context_system::instance();
        $traineeroleid = $DB->get_field('role', 'id', array('shortname' => 'trainee'));
        $row['id'] = $data->id;

        //if offerings have enroled users new offering will be created with the edited offering data
        $offeringhasenrollments = $DB->record_exists('program_enrollments',array('programid' => $data->trainingid, 'offeringid' => $data->id,'courseid'=>$data->courseid,'roleid' => $traineeroleid));
        if($offeringhasenrollments){
            $data->id = 0;
        }
        if($data->id > 0 && $offeringhasenrollments){         
           
           $row['availableseats'] = $data->availableseats;
           $row['timemodified'] = time();
           $row['useremodified'] = $USER->id;
           $row['organization'] =  (is_array($data->organization)) ? implode(" ",$data->organization) : $data->organization;

           try{
                $transaction = $DB->start_delegated_transaction();
                $record= $DB->update_record('tp_offerings',$row);
                $transaction->allow_commit();
                return $record;
            } catch(moodle_exception $e){
               $transaction->rollback($e);
               return false;
            }
        }
        $offeringtype =($data->offeringtype) ?  $data->offeringtype : '';
        $row['type'] = $data->type;
        $row['offeringpricing'] = ($data->type == 1) ? (($data->offeringpricing) ? $data->offeringpricing : 0) : 0 ;
        $row['organization'] = (is_array($data->organization)) ? implode(" ",$data->organization) : $data->organization;
        $row['trainingid'] = $data->trainingid;
        $row['meetingtype'] = $data->meetingtype ? $data->meetingtype: 0;
        $row['availableseats'] = $data->availableseats;
        $row['published'] = 0;
       
        $row['officials'] =(is_array($data->organization)) ?  implode(',', array_filter($data->officials)) : $data->officials;

        $courseid = $DB->get_field('local_trainingprogram', 'courseid', ['id' => $data->trainingid]);

        $program = $DB->get_record('local_trainingprogram',array('id' => $data->trainingid));
        if($data->id > 0) {

            $offering_data  = $DB->get_record('tp_offerings',['id'=>$data->id]);
            $submitted_trainingmethod = $data->trainingmethod;
            $submittedsellingprice = $data->sellingprice;
            $submittedactualprice = $data->actualprice;
            $submitted_startdate =$data->startdate;
            $submitted_enddate =  $data->enddate;
            $submitted_starttime = (($data->starttime['hours'] * 3600) + ($data->starttime['minutes'] * 60));
            $submitted_endtime =  (($data->endtime['hours'] * 3600) + ($data->endtime['minutes'] * 60));
            if(!is_siteadmin() && has_capability('local/organization:manage_trainingofficial',$systemcontext) && ($offering_data->startdate != $submitted_startdate ||  $offering_data->enddate != $submitted_enddate ||  $offering_data->time != $submitted_starttime || $offering_data->endtime != $submitted_endtime || $offering_data->sellingprice != $submittedsellingprice || $offering_data->actualprice != $submittedactualprice ) && (empty($offeringtype) || $offeringtype !='approved')) {
                $data->startdate = $offering_data->startdate;
                $data->enddate = $offering_data->enddate;
                $dur_min = $offering_data->time/60;
                if($dur_min){
                    $hours = floor($dur_min / 60);
                    $minutes = ($dur_min % 60);
                }
                $data->starttime['hours'] = $hours;
                $data->starttime['minutes'] = $minutes;
                if($data->endtime > 0) {
                    $dur__min = $offering_data->endtime/60;
                    if($dur__min){
                        $hours = floor($dur__min / 60);
                        $minutes = ($dur__min % 60);
                    } 
                }
                $data->endtime['hours'] = $hours;
                $data->endtime['minutes'] = $minutes;
                $data->sellingprice = ((int)$offering_data->sellingprice != (int)$data->sellingprice) ? $offering_data->sellingprice : $data->sellingprice; 
                $data->actualprice = ((int)$offering_data->actualprice != (int)$data->actualprice) ? $offering_data->actualprice : $data->actualprice; 
                if($data->trainingmethod == $offering_data->trainingmethod) {
                  $data->trainingmethod = $submitted_trainingmethod;
                } else {
                   $data->trainingmethod = $offering_data->trainingmethod;
                }
               
            }

            $row['trainingmethod'] = $data->trainingmethod;
            if ($data->trainingmethod == 'online' || $data->trainingmethod == 'elearning' || $data->halllocation1=='clientheadquarters'){
                $row['halladdress'] = 0;
                if ($data->id > 0) {
                    (new \local_hall\hall())->remove_hallreservations($data->id, 'tprogram');
                }
    
            } else {
                $row['halladdress'] = $data->halladdress? $data->halladdress : 0;
                $row['halllocation'] = NULL;
            }
            $row['sellingprice'] = $data->sellingprice?$data->sellingprice:0;
            $row['actualprice'] = $data->actualprice?$data->actualprice:0;
            $row['startdate'] = ($data->trainingmethod == 'elearning') ? 0 : $data->startdate;
            $row['enddate'] = ($data->trainingmethod == 'elearning') ? 0 : $data->enddate;
            $row['time'] = ($data->trainingmethod == 'elearning') ? 0 : (($data->starttime['hours'] * 3600) + ($data->starttime['minutes'] * 60));
            $row['endtime'] = ($data->trainingmethod == 'elearning') ? 0 : (($data->endtime['hours'] * 3600) + ($data->endtime['minutes'] * 60));
            $start = $data->startdate+$row['time'];
            $end = $data->startdate+$row['endtime'];
            $duration =($data->trainingmethod == 'elearning') ? 0 : abs($start-$end);
            
            $row['duration'] = $duration;
            $data->duration = $duration;
            $date_duration = $data->enddate - $data->startdate;
            $starttime = ceil($data->startdate + $row['time']);
            $days_between = ceil(abs($date_duration) / 86400);

            $existingcode = $DB->get_field('tp_offerings','code',array('id' => $data->id));
            $offering = $DB->get_record('tp_offerings',array('id' => $data->id));
            $row['id'] = $data->id;
            $row['timemodified'] = time();
            $row['useremodified'] = $USER->id;

            if(!empty($data->code) && !is_null($data->code)) {
                $row['code'] = $data->code;
            } else {
                if($data->startdate == $offering->startdate){
                    $row['code'] = $existingcode;

                } else {
                    $row['code'] = self :: generate_offering_code($data);
                    (new trainingprogram)->update_group_idnumber($existingcode,$row['code'],$courseid);
                }
            }
            $attendenceidsql="SELECT ats.attendanceid 
                                    FROM {attendance_sessions} as ats 
                                    JOIN {offering_sessions} ofs ON ats.id=ofs.sessionid 
                                   WHERE ofs.offeringid = $data->id";

            $attendance_id=$DB->get_field_sql($attendenceidsql);
            $sessionidsql ="SELECT sessionid 
                              FROM {offering_sessions}
                              WHERE offeringid = $data->id";
            $sessionids=$DB->get_records_sql($sessionidsql);
            $existingsessionstartdate=$DB->get_field_sql("SELECT ats.sessdate 
                                    FROM {attendance_sessions} as ats 
                                    JOIN {offering_sessions} ofs ON ats.id=ofs.sessionid 
                                   WHERE ofs.offeringid = $data->id ORDER BY ofs.id ASC LIMIT 1"); 
            $existingsessionenddate=$DB->get_field_sql("SELECT ats.sessdate 
                                    FROM {attendance_sessions} as ats 
                                    JOIN {offering_sessions} ofs ON ats.id=ofs.sessionid 
                                   WHERE ofs.offeringid = $data->id ORDER BY ofs.id DESC LIMIT 1");

            $existingofferingrecord = $DB->get_record('tp_offerings',array('id'=>$data->id));

            if(empty($row['code']) || is_null($row['code'])) {
                
                $offering_code = $existingofferingrecord->code;

            } else {
              
               $offering_code = $row['code'];

            }

            try{
              $transaction = $DB->start_delegated_transaction();

              $createoffering = new createoffering($courseid,$offering_code,$data,$program);

              
              $row['meetingid'] = $createoffering->meetingid ? $createoffering->meetingid : 0;

              
                if ($data->trainingmethod == 'elearning') {
                    foreach ($sessionids AS $sessionid) {
                        (new trainingprogram)->delete_session_record($sessionid->sessionid);
                    }    
                }else{
                    if($attendance_id) {
                        $attendanceid = $attendance_id;
                    } else {
                        //$createoffering->createattendance();
                        $attendanceid = $createoffering->attendanceid;
                    }
                    if(($existingofferingrecord->trainingmethod == 'online' || $existingofferingrecord->trainingmethod == 'offline') && ($existingsessionstartdate != $data->startdate ||  $existingsessionenddate != $data->enddate)){
                        foreach ($sessionids AS $sessionid) {
                            (new trainingprogram)->delete_session_record($sessionid->sessionid);
                        }
                        for($i=0; $i <= $days_between; $i++){
                            // $sessionid=mod_attendance_external::add_session($attendanceid,'',  $starttime,$duration, 0,true);
                            // (new trainingprogram)->insert_offering_session_record($sessionid['sessionid'],$data->id,$starttime,$data->trainingid,$courseid);
                            // $starttime += 86400;

                            $attendance = $DB->get_record('attendance', ['id' => $attendanceid]);
                            $cm = $DB->get_record_sql("SELECT cm.* FROM {course_modules} AS cm JOIN {modules} AS m ON m.name = 'attendance' AND cm.module = m.id WHERE cm.instance = $attendanceid");

                            $course = get_course($cm->course);
                            $session_structure = new \mod_attendance_structure($attendance, $cm, $course);
                            $sessioninfo = new stdClass();
                            $sessioninfo->sessdate= $starttime;
                            $sessioninfo->duration= $duration;
                            $sessioninfo->description= $offering_code;
   
                            $sessioninfo->groupid=0;
                            $sessionid= $session_structure->add_session($sessioninfo);
                            (new trainingprogram)->insert_offering_session_record($sessionid,$data->id,$starttime,$data->trainingid,$courseid);
                            $starttime += 86400;
                        }
                    } else {

                        $createoffering->createattendance($data->enddate);
                        for($i=0; $i <= $days_between; $i++){
                           /* $sessionid=mod_attendance_external::add_session($createoffering->attendanceid,'',  $starttime,$duration, 0,true);
                            (new trainingprogram)->insert_offering_session_record($sessionid['sessionid'],$data->id,$starttime,$data->trainingid,$courseid);
                            $starttime += 86400;*/

                            $attendance = $DB->get_record('attendance', ['id' => $createoffering->attendanceid]);
                            $cm = $DB->get_record_sql("SELECT cm.* FROM {course_modules} AS cm JOIN {modules} AS m ON m.name = 'attendance' AND cm.module = m.id WHERE cm.instance = $createoffering->attendanceid");

                            $course = get_course($cm->course);
                            $session_structure = new \mod_attendance_structure($attendance, $cm, $course);
                            $sessioninfo = new stdClass();
                            $sessioninfo->sessdate= $starttime;
                            $sessioninfo->duration= $duration;
                            $sessioninfo->description= $offering_code;;
   
                            $sessioninfo->groupid=0;
                            $sessionid= $session_structure->add_session($sessioninfo);
                            (new trainingprogram)->insert_offering_session_record($sessionid,$data->id,$starttime,$data->trainingid,$courseid);
                            $starttime += 86400;
                       }

                    } 
                }

                $row['classification'] = $data->classification; //add classification..renu
                $row['estimatedbudget'] = $data->estimatedbudget; //add estimatedbudget..renu    
                $row['proposedcost'] = $data->proposedcost; //add proposedcost..renu
                $row['finalamount'] = $data->finalpoamount; //add finalpoamount..renu
                $row['tagrement'] = $data->traingagrrement; //add traingagrrement..renu
                $row['trainingcost'] = $data->tcost; //add tcost..renu
                $row['attachmentpdf']= $data->attachmentpdf;
                $row['officialproposal']= $data->officialproposal; 
                $row['officialpo']= $data->officialpo;
                $row['tagrrement']= $data->tagrrement;
                $row['languages'] = $data->language;
                if($data->externallinkcheck == 0 && $data->externallink !=""){
                $row['externallink'] = "";
                }else{
                $row['externallink'] = $data->externallink;
                }
               $row['externallinkcheck'] = $data->externallinkcheck;

              $record= $DB->update_record('tp_offerings', $row);

              if(!is_siteadmin() && has_capability('local/organization:manage_trainingofficial',$systemcontext) && ($offering_data->startdate != $submitted_startdate ||  $offering_data->enddate != $submitted_enddate ||  $offering_data->time != $submitted_starttime || $offering_data->endtime != $submitted_endtime || $offering_data->sellingprice != $submittedsellingprice || $offering_data->actualprice != $submittedactualprice) && (empty($offeringtype) || $offeringtype !='approved')) {


                $offeringrequest = new stdClass();
                $offeringrequest->referenceid = $data->id;
                $offeringrequest->code = $row['code'];
                $offeringrequest->entity = 'offering';
                $offeringrequest->type = 'update';
                $offeringrequest->startdate =  ((int)$offering_data->startdate != (int)$submitted_startdate) ? $submitted_startdate : 0;
                $offeringrequest->enddate =  ((int)$offering_data->enddate != (int)$submitted_enddate) ? $submitted_enddate : 0;
                $offeringrequest->starttime =((int)$offering_data->time != (int)$submitted_starttime) ?  $submitted_starttime : 0;
                $offeringrequest->endtime = ((int)$offering_data->endtime != (int)$submitted_endtime) ?  $submitted_endtime : 0;$submitted_endtime;
                $offeringrequest->sellingprice = ((int)$offering_data->sellingprice != (int)$submittedsellingprice) ? $submittedsellingprice : 0; 
                $offeringrequest->actualprice = ((int)$offering_data->actualprice != (int)$submittedactualprice) ? $submittedactualprice : 0; 
                $offeringrequest->trainingmethod = ($submitted_trainingmethod != $offering_data->trainingmethod) ? $submitted_trainingmethod : null;
                $offeringrequest->timemodified = time();
                $offeringrequest->usermodified = $USER->id;
                $DB->insert_record('offering_program_requests', $offeringrequest);

            }
              $offeringrecord = $DB->get_record('tp_offerings',['id'=>$data->id]);

              if($offeringrecord->prequiz) {

                $cmid = $DB->get_field_sql('SELECT cm.id FROM {course_modules} as cm 
                                                JOIN {quiz} as q ON q.id=cm.instance AND cm.module=(SELECT m.id from {modules} as m where name="quiz") 
                                                JOIN {tp_offerings} as ep ON ep.prequiz=q.id AND ep.id=:offeringid', ['offeringid' =>$data->id]);

                if($cmid) {

                     $data->cmid = (int) $cmid;

                    $createoffering->updatequiz($offeringrecord->prequiz,$data);
                }
                
              }
              if($offeringrecord->postquiz) {

                $cmid = $DB->get_field_sql('SELECT cm.id FROM {course_modules} as cm 
                                                JOIN {quiz} as q ON q.id=cm.instance AND cm.module=(SELECT m.id from {modules} as m where name="quiz") 
                                            JOIN {tp_offerings} as ep ON ep.postquiz=q.id AND ep.id=:offeringid', ['offeringid' =>$data->id]);

                if($cmid) {

                    $data->cmid = (int) $cmid;

                    $createoffering->updatequiz($offeringrecord->postquiz,$data);
                }


              }

              if($data->entitycode) {
                    $draftrecord = $DB->get_record('reservations_draft', ['entitycode' => $data->entitycode, 'type' => 'tprogram']);
                    if($draftrecord) {
                        $existingrecord = $DB->get_record('hall_reservations', ['typeid' => $data->id, 'type' => 'tprogram']);
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
                        $reservationdata->type = 'tprogram';
                        $reservationdata->status = 1;
                        if($existingrecord) {
                           $DB->update_record('hall_reservations', $reservationdata);
                        } else {

                            $DB->insert_record('hall_reservations', $reservationdata);
                        }

                        $sessionkey = $DB->delete_records('reservations_draft', ['entitycode' => $data->entitycode, 'type' => 'tprogram']);
                    }
                    
                }
                
              $event = \local_trainingprogram\event\tpofferings_updated::create(array( 'context'=>$systemcontext, 'objectid' =>$data->id));
              $event->trigger();
                
              $transaction->allow_commit();
               return $record;
            } catch(moodle_exception $e){
                $transaction->rollback($e);
             return false;

            }
        } else {
            $row['trainingmethod'] = $data->trainingmethod;
            if ($data->trainingmethod == 'online' || $data->trainingmethod == 'elearning' || $data->halllocation1=='clientheadquarters'){
                $row['halladdress'] = 0;
                if ($data->id > 0) {
                    (new \local_hall\hall())->remove_hallreservations($data->id, 'tprogram');
                }
    
            } else {
                $row['halladdress'] = $data->halladdress? $data->halladdress : 0;
                $row['halllocation'] = NULL;
            }
            $row['sellingprice'] = $data->sellingprice?$data->sellingprice:0;
            $row['actualprice'] = $data->actualprice?$data->actualprice:0;
            $row['startdate'] = ($data->trainingmethod == 'elearning') ? 0 : $data->startdate;
            $row['enddate'] = ($data->trainingmethod == 'elearning') ? 0 : $data->enddate;
            $row['time'] = ($data->trainingmethod == 'elearning') ? 0 : (($data->starttime['hours'] * 3600) + ($data->starttime['minutes'] * 60));
            $row['endtime'] = ($data->trainingmethod == 'elearning') ? 0 : (($data->endtime['hours'] * 3600) + ($data->endtime['minutes'] * 60));
            $start = $data->startdate+$row['time'];
            $end = $data->startdate+$row['endtime'];
            $duration =($data->trainingmethod == 'elearning') ? 0 : abs($start-$end);
            
            $row['duration'] = $duration;
            $data->duration = $duration;
            $date_duration = $data->enddate - $data->startdate;
            $starttime = ceil($data->startdate + $row['time']);
            $days_between = ceil(abs($date_duration) / 86400);


            if(!empty($data->code) && !is_null($data->code)) {
                $offeringcode = $data->code;
                if($DB->record_exists('tp_offerings',array('code' => $data->code))) {
                    $row['code'] = $offeringcode = $data->code.'1';
                } else {
                    $row['code'] = $offeringcode = $data->code;
                }
            } else {
              $row['code']  = $offeringcode =  self :: generate_offering_code($data);
            }
            // $courseid = $DB->get_field('local_trainingprogram', 'courseid', ['id' => $data->trainingid]); 
             
            try {
                $transaction = $DB->start_delegated_transaction();
                if($courseid){
                    $createoffering = new createoffering($courseid, $row['code'],$data,$program);
                    if($data->trainingmethod == 'online' || $data->trainingmethod == 'offline'){

                        $createoffering->createattendance($data->enddate);
                    }
                    $evaluationmethods = explode(',', $program->evaluationmethods);

                    if(!is_null($program->evaluationmethods)) {
                
                        foreach($evaluationmethods as $method){

                            if($method == 0){
                                $type = 'prequiz';
                            }else if($method == 1){
                                $type = 'postquiz';
                            }else{
                                continue;
                            }

                            $evaluationmethods = [];
                            $evaluationmethods['0'] = '{mlang en}Pre Exam{mlang}{mlang ar}الاختبار القبلي{mlang}';
                            $evaluationmethods['1'] = '{mlang en}Post Exam{mlang}{mlang ar}الاختبار البعدي{mlang}';
                            $row[$type] = $createoffering->createquiz($evaluationmethods[$method],$data);

                        }

                    }
                    
                    $row['groupid'] = $createoffering->groupid;
                    $sectionid =(int) $DB->get_field('course_sections','id',['course'=>$courseid,'section'=>$createoffering->sectionid]);
                    $row['sections'] =($sectionid) ? $sectionid :  $createoffering->sectionid;
                    $row['meetingid'] = $createoffering->meetingid ? $createoffering->meetingid: 0;
                    $row['classification'] = $data->classification; //add classification..renu
                    $row['estimatedbudget'] = $data->estimatedbudget; //add estimatedbudget..renu
                    $row['proposedcost'] = $data->proposedcost; //add proposedcost..renu
                    $row['finalamount'] = $data->finalpoamount; //add finalpoamount..renu
                    $row['tagrement'] = $data->traingagrrement; //add traingagrrement..renu 
                    $row['trainingcost'] = $data->tcost; //add tcost..renu      

                    if (isset($data->attachmentpdf)) { 
                        $row['attachmentpdf']= $data->attachmentpdf;

                    }

                    if (isset($data->officialproposal)) {
                      
                        $row['officialproposal']= $data->officialproposal;

                    }
                    if (isset($data->officialpo)) {
                      
                        $row['officialpo']= $data->officialpo;

                    }
                    if (isset($data->tagrrement)) {
                     
                        $row['tagrrement']= $data->tagrrement;

                    }
                    $row['languages'] = $data->language;
                    $row['externallink']    = $data->externallink;
                    $row['externallinkcheck']     = $data->externallinkcheck;
               
                    
                    try{
                      $offeringid = $DB->insert_record('tp_offerings', $row);

                      if($createoffering->attendanceid){

                        for($i=0; $i <= $days_between; $i++){
                            // $sessionid= mod_attendance_external::add_session($createoffering->attendanceid,'',  $starttime,$duration, 0,true);
                            // (new trainingprogram)->insert_offering_session_record($sessionid['sessionid'],$data->id,$starttime,$data->trainingid,$courseid);
                            // $starttime += 86400;
                            $attendance = $DB->get_record('attendance', ['id' => $createoffering->attendanceid]);
                            $cm = $DB->get_record_sql("SELECT cm.* FROM {course_modules} AS cm JOIN {modules} AS m ON m.name = 'attendance' AND cm.module = m.id WHERE cm.instance = $createoffering->attendanceid");

                            $course = get_course($cm->course);
                            $session_structure = new \mod_attendance_structure($attendance, $cm, $course);
                            $sessioninfo = new stdClass();
                            $sessioninfo->sessdate= $starttime;
                            $sessioninfo->duration= $duration;
                            $sessioninfo->description= $offeringcode;
   
                            $sessioninfo->groupid=0;
                            $sessionid= $session_structure->add_session($sessioninfo);
                            (new trainingprogram)->insert_offering_session_record($sessionid,$offeringid,$starttime,$data->trainingid,$courseid);
                            $starttime += 86400;
                        }

                      }

                       if($data->entitycode) {
                            $draftrecords = $DB->get_records('reservations_draft', ['entitycode' => $data->entitycode]);
                            foreach($draftrecords AS $draftrecord) {
                                $draftdata = new stdClass();
                                $draftdata->typeid = $offeringid;
                                $draftdata->hallid = $draftrecord->hallid;
                                $draftdata->seats = $draftrecord->seats;
                                $draftdata->examdate = $draftrecord->date;
                                $draftdata->slotstart = $draftrecord->slotstart;
                                $draftdata->slotend = $draftrecord->slotend;
                                $draftdata->userid = $draftrecord->userid;
                                $draftdata->type = 'tprogram';
                                $draftdata->status = 1;
                                $DB->insert_record('hall_reservations', $draftdata);
                            }
                           $DB->delete_records('reservations_draft', ['entitycode' => $data->entitycode, 'type' => 'tprogram']);
                        }
                      
                    } catch(moodle_exception $e){
                      print_r($e);
                    }
                    
                }
                $systemcontext = context_system::instance();
                $event = \local_trainingprogram\event\tpofferings_created::create(array( 'context'=>$systemcontext, 'objectid' =>$offeringid));
                $event->trigger();
                $transaction->allow_commit();
                return $offeringid;
            } catch(moodle_exception $e) {
                $transaction->rollback($e);
                return false;
            }

        }
    }
     public function set_schedule_program($id, $ajaxdata=false) {
        global $DB;
        $data = $DB->get_record('tp_offerings', ['id' => $id], '*', MUST_EXIST);
        $row['id'] = $data->id;
        $row['startdate'] = $data->startdate;
        $row['enddate'] = $data->enddate;
        $row['time'] = $data->time;
        $row['type'] = $data->type;
        $row['availableseats'] = $data->availableseats;
        $row['organization'] = $data->organization;
        $row['sellingprice'] = $data->sellingprice;
        $row['actualprice'] = $data->actualprice;
        $row['trainingmethod'] = $data->trainingmethod;
        $row['officials'] = $data->officials;
        $row['offeringdiscount'] = $data->discount;
        $row['targetaudience'] = $data->targetaudience;
        $row['trainingprovidertype'] = $data->trainingprovidertype;
        $row['issponsored'] = $data->issponsored;
        $row['offeringpricing'] = $data->offeringpricing;
        $row['quotationfile'] = $data->quotationfile;
        $row['quotationfee'] = $data->quotationfee;
        $row['trainingagreementfile'] = $data->trainingagreementfile;
        $row['trainingagreementfee'] = $data->trainingagreementfee;
        $row['approvepurchsedfile'] = $data->approvepurchsedfile;
        $row['approvepurchsedfee'] = $data->approvepurchsedfee;
        $row['language'] = $data->languages;
        $row['classification'] = $data->classification; //set classification..renu
        $row['estimatedbudget'] = $data->estimatedbudget; //set estimatedbudget..renu
        $row['proposedcost'] = $data->proposedcost; //set proposedcost..renu
        $row['finalpoamount'] = $data->finalamount; //set finalpoamount..renu
        $row['externallink']    = $data->externallink;
        $row['externallinkcheck']     = $data->externallinkcheck;
       if($data->tagrement==1){
        $row['traingagrrement'] = $data->tagrement;
    }
       
        $row['tcost'] = $data->trainingcost; //set tcost..renu
    
        
        $row['attachmentpdf'] = $data->attachmentpdf; 
        $row['officialproposal'] = $data->officialproposal; 
        $row['officialpo'] = $data->officialpo; 
        $row['tagrrement'] = $data->tagrrement; 

        if($row['trainingmethod'] == 'online') {
          $row['meetingtype'] = $data->meetingtype;
        } else {

           $row['halladdress'] = !empty($data->halladdress) ? $data->halladdress : NULL;
        }

        $dur_min = $data->time/60;
        if($dur_min){
            $hours = floor($dur_min / 60);
            $minutes = ($dur_min % 60);
        }
        $row['starttime[hours]'] = $hours;
        $row['starttime[minutes]'] = $minutes;
        if($data->endtime > 0) {
            $dur__min = $data->endtime/60;
            if($dur__min){
                $hours = floor($dur__min / 60);
                $minutes = ($dur__min % 60);
            } 
        $row['endtime[hours]'] = $hours;
        $row['endtime[minutes]'] = $minutes;

        } else {
          
          $dur = $data->duration/60;
            if($dur){
                $hours = floor($dur / 60);
                $minutes = ($dur % 60);
            } 
           $row['endtime[hours]'] = $row['starttime[hours]'] + $hours;
           $row['endtime[minutes]'] = $row['starttime[minutes]']+ $minutes;
        }
        // $halllocation = explode(',',$data->halllocation);
        // array_unshift($halllocation, null);
        // $halllocation = array_flip($halllocation);
        

        if (is_numeric($ajaxdata['type']) && $ajaxdata['type']==0) {
            if($data->halllocation=='inside' || $data->halllocation=='outside'){
                $row['halllocation'] = $data->halllocation;
                $row['halladdress'] = $ajaxdata['halladdress'];
            }
        } else {
            if($data->halllocation=='inside' || $data->halllocation=='outside'){
                $row['halllocation'] = $data->halllocation;
            }
            if($data->halllocation=='clientheadquarters'){
                $row['halllocation1'] = $data->halllocation;
            }
            if($data->halllocation=='inside'){
                $row['halllocation1'] = $data->halllocation;
            }
            if($data->halllocation=='outside'){
                $row['halllocation1'] = $data->halllocation;
            }
        }

        return $row; 
    }
    public  function viewprogramsectors($programid) { 
        global $DB, $PAGE, $OUTPUT;
        $systemcontext = context_system::instance();
        $PAGE->set_context($systemcontext);
        $data = $DB->get_record('local_trainingprogram', ['id' => $programid], '*', MUST_EXIST);
        $renderer = $PAGE->get_renderer('local_trainingprogram');
        $coursedata  = $renderer->programsectors_view($data);
        return $coursedata;
    }
    public function program_enrolled_users($offeringid=0,$params,$perpage = -1, $lastitem = 0){
        global $DB, $USER;
        $systemcontext = context_system::instance();
        $traineerole= $DB->get_field('role', 'id', array('shortname' => 'trainee'));
        $program= $DB->get_record_sql('SELECT tp.* FROM {local_trainingprogram} as tp 
                                         JOIN {tp_offerings} as tpo ON tpo.trainingid = tp.id 
                                        WHERE tpo.id=:offeringid', ['offeringid' => $offeringid]);
        $currentlang= current_language();
        $displaying_name = (new trainingprogram)->user_fullname_case($method = 'enrollment');
        $fromsql = "SELECT distinct u.id,$displaying_name";
        $countsql = "SELECT count(u.id) as total";
        $offeringtype = $DB->get_record('tp_offerings',['id'=>$offeringid]);
        $sql =" FROM {user} AS u 
                JOIN {local_users} AS lc ON lc.userid = u.id 
                JOIN {course} as c ON c.id=$program->courseid
                JOIN {context} as ccnt ON  ccnt.instanceid = c.id AND ccnt.contextlevel = 50  
                WHERE  u.id > 2 AND u.deleted = 0 AND lc.deleted = 0";

        if(!is_siteadmin() && has_capability('local/organization:manage_organizationofficial',$systemcontext)) {
            $get_all_orgofficials = (new exams())->get_all_orgofficials($USER->id);
            $orgusers = implode(',',$get_all_orgofficials);
            $organization = $DB->get_field('local_users','organization',array('userid'=>$USER->id));
            $sql.= " AND 
            CASE
                WHEN  lc.organization >0 THEN lc.organization = $organization
                ELSE  lc.organization <> 0
            END ";
        } 

        if((int)$offeringtype->type == 1 && $offeringtype->organization > 0) {

            $sql.= " AND 
            CASE
                WHEN  lc.organization > 0 THEN lc.organization = $offeringtype->organization
                ELSE  lc.organization <> 0
            END ";
        }

        if($lastitem!=0){
           $sql.=" AND u.id > $lastitem";
        }
        $sql .=" AND u.id <> $USER->id";
        if (!empty($params['query'])) {
            $sql .= " AND (lc.firstname LIKE :firstnamesearch OR lc.lastname LIKE :lastnamesearch OR lc.firstnamearabic LIKE :firstnamearabicsearch OR lc.lastnamearabic LIKE :llastnamearabicsearch OR lc.middlenameen LIKE :middlenameensearch OR lc.middlenamearabic LIKE :middlenamearabicsearch OR lc.thirdnameen LIKE :thirdnameensearch OR lc.thirdnamearabic LIKE :thirdnamearabicsearch OR lc.email LIKE :email  OR lc.id_number LIKE :idnumber) ";
            $searchparams = array(
                                  'firstnamesearch' => '%'.trim($params['query']).'%',
                                  'lastnamesearch' => '%'.trim($params['query']).'%', 
                                  'firstnamearabicsearch' => '%'.trim($params['query']).'%', 
                                  'llastnamearabicsearch' => '%'.trim($params['query']).'%', 
                                  'middlenameensearch' => '%'.trim($params['query']).'%', 
                                  'middlenamearabicsearch' => '%'.trim($params['query']).'%', 
                                  'thirdnameensearch' => '%'.trim($params['query']).'%', 
                                  'thirdnamearabicsearch' => '%'.trim($params['query']).'%', 
                                  'email' => '%'.trim($params['query']).'%',
                                  'idnumber' => '%'.trim($params['query']).'%');
        }  else {
            $searchparams = array();
        }
        if (!empty($params['email'])) {
             $sql.=" AND u.id IN ({$params['email']})";
        }
        if (!empty($params['organizationusers'])) {
            $sql.=" AND lc.organization IN ({$params['organizationusers']})";
        }
        $order = ' ORDER BY u.id ASC ';

        if ($perpage != -1) {
            $order .= " LIMIT $perpage ";
        }
        $sql .= " AND u.id IN (SELECT roa.userid 
                                          FROM {role_assignments} as roa 
                                         WHERE roa.roleid =:roleid1 AND roa.contextid =:contextid )";

         if(is_siteadmin() ||  has_capability('local/organization:manage_trainingofficial',$systemcontext) ||  has_capability('local/organization:manage_communication_officer',$systemcontext))   {

           
           $existingsql = " SELECT pe.userid 
                             FROM {program_enrollments} AS pe 
                            WHERE  pe.programid =:programid AND pe.offeringid =:offeringid AND pe.courseid =:courseid  AND pe.roleid =:roleid  AND pe.enrolstatus = 1";
         }  else {

            $existingsql = " SELECT pe.userid 
                             FROM {program_enrollments} AS pe 
                             WHERE  pe.programid =:programid AND pe.offeringid =:offeringid AND pe.courseid =:courseid  
                             AND pe.roleid =:roleid AND pe.usercreated IN($orgusers)/*=:currentuserid*/ AND pe.enrolstatus = 1";


         }                             

        $availablesql= " AND u.id NOT IN (SELECT pe.userid 
                                              FROM {program_enrollments} AS pe 
                                              JOIN {tp_offerings} as tpo ON pe.offeringid=tpo.id 
                                             WHERE pe.programid =:programid 
                                               AND pe.courseid =:courseid  AND pe.roleid =:roleid )";


        $params = array_merge(['programid' => $program->id, 
                               'offeringid' => $offeringid, 
                               'courseid' => $program->courseid, 
                               'roleid' => $traineerole,
                               'roleid1' => $traineerole,
                               'contextid' => $systemcontext->id,
                               'currentuserid' => $USER->id], $searchparams);

        $ausers = $DB->get_records_sql($fromsql . $sql . $availablesql,$params, 0, 250);

        foreach($ausers as $auser){
            $availableusers[] = ['id' => $auser->id, 'fullname' => $auser->fullname];
        };

        $enrolledsql = " AND u.id IN ($existingsql)";
    
        $eusers = $DB->get_records_sql($fromsql . $sql. $enrolledsql,$params, 0, 250);
        foreach($eusers as $euser){
            $enrolledusers[] = ['id' => $euser->id, 
                                'fullname' => $euser->fullname];
        };

        $enrolleduserscount = $DB->count_records_sql($countsql . $sql . $availablesql,$params);
        $availableuserscount = $DB->count_records_sql($countsql . $sql . $enrolledsql,$params);

        return compact('availableusers', 'enrolledusers', 'availableuserscount', 'enrolleduserscount');
    }
    public function program_enrollment($offeringid,$userid,$roleid=false,$trainertype=false,$usercreated=false, $type =false,$oderid = false,$givenorganization = false,$productid = false) {
        global $DB,$USER;
        $timestart = time();  
        
        $currentuser = ($usercreated) ? $usercreated : $USER->id;
        $systemcontext = context_system::instance();               
        $timeend = 0;
        if ($timestart==''){
            $timestart=0;
        }
        $availableseats = $this->get_available_seats($offeringid);
        $offeringrecord =$DB->get_record('tp_offerings',['id' => $offeringid]); 

        $trainingmethod = $DB->get_field_sql('SELECT trainingmethod FROM {tp_offerings} WHERE id = '.$offeringid.'');

        $program= $DB->get_record_sql('SELECT tp.id,tp.courseid,tp.price,tpo.code AS offeringcode FROM {local_trainingprogram} as tp 
            JOIN {tp_offerings} as tpo ON tpo.trainingid = tp.id 
            WHERE tpo.id=:offeringid', ['offeringid' => $offeringid]);
      
       
            if(!$roleid){
                $traineesql = "SELECT id FROM {role} 
                                WHERE shortname = 'trainee'";
                $role_id = (int) $DB->get_field_sql($traineesql);
            }else{
                $role_id =(int) $roleid;
            }

            
           $org_official_roleid = $DB->get_field('role','id',array('shortname' => 'organizationofficial')); 

            $manual = enrol_get_plugin('manual');
            
            $instance = $DB->get_record('enrol', array('courseid' => $program->courseid, 'enrol' => 'manual'), '*', MUST_EXIST);
            if($userid){
                $manual->enrol_user($instance,$userid,$role_id,$timestart, $timeend);
            }
            $offeringprice =(int)$DB->get_field('tp_offerings','sellingprice',['id'=>$offeringid]);
            if($type !='bulkenrollment') {
                //$group = groups_get_group_by_idnumber($program->courseid, $program->offeringcode);
                $group = $DB->get_record_sql("SELECT grop.id FROM {groups} as grop JOIN {tp_offerings} as tpo ON tpo.code = grop.idnumber WHERE tpo.id = $offeringid");

                $groupid = (int) $group->id;

                if ($groupid) {
                    groups_add_member($groupid, $userid,null,0);
                }

            }
            $organization =(int) $DB->get_field('local_users','organization',['userid'=>$userid]);

            if($organization) {

                $autoapproval =(int) $DB->get_field_sql('
                SELECT autoapproval FROM {local_organization} 
                WHERE id ='.$organization.'');

                $orgshortcode=$DB->get_field_sql('
                                        SELECT shortname FROM {local_organization} 
                                        WHERE id ='.$organization.''
                );
                $allorgusers = $DB->get_records_sql("SELECT u.id,u.firstname FROM {user} as u 
                JOIN {local_users} as lc ON lc.userid = u.id 
                JOIN {role_assignments} as  ra on ra.userid=u.id
                JOIN {role} as r ON ra.roleid = r.id  AND r.shortname = 'organizationofficial'
                WHERE lc.organization =:organization AND  ra.contextid =:contextid",['organization'=>$organization,'contextid'=>$systemcontext->id]);

                foreach($allorgusers AS $orguser){

                    $teacherroleid=$DB->get_field('role','id',array('shortname'=>'teacher'));

                    $courseinstance = \context_course::instance($program->courseid);

                    if(user_has_role_assignment($orguser->id,$teacherroleid,$courseinstance->id)) {  
                        $groupidnumber= ($orgshortcode) ? $orgshortcode.$program->offeringcode : $orguser->id.'_'.$program->offeringcode; 
                    
                        $orggroupid = $DB->get_field_sql("
                            SELECT grop.id FROM {groups} AS grop 
                            JOIN {groups_members} AS gropm ON gropm.groupid = grop.id 
                            WHERE gropm.userid =$orguser->id AND grop.courseid = $program->courseid AND grop.idnumber  LIKE '%$groupidnumber%' ORDER BY grop.id DESC LIMIT 1"
                        );
                        if($orggroupid) {
                            groups_add_member($orggroupid, $userid,null,0);
                        }
                    } else {
                        if($type != 'bulkenrollment') {
                            $manual = enrol_get_plugin('manual');
                            $timestart = time();
                            $timeend = 0;
                            $manual->enrol_user($instance, $orguser->id, $teacherroleid, $timestart, $timeend); 
                            // Creating seperate group for organization - Starts//
                            $groupdata = new stdClass;
                            $groupdata->name = ($orgshortcode) ? $orgshortcode.$offeringrecord->code : $orguser->id.'_'.$offeringrecord->code;
                            $groupdata->idnumber =($orgshortcode) ? $orgshortcode.$offeringrecord->code : $orguser->id.'_'.$offeringrecord->code;
                            $groupdata->courseid= $program->courseid;

                            if(!groups_get_group_by_idnumber($program->courseid, $groupdata->idnumber)){
                                $orgusergroupid = groups_create_group($groupdata);
                            }
                            if($orgusergroupid) {
                                groups_add_member($orgusergroupid,$orguser->id,null,0);
                            }
                            $offeringgroup = $DB->get_record_sql("SELECT grop.id FROM {groups} as grop JOIN {tp_offerings} as tpo ON tpo.code = grop.idnumber WHERE tpo.id = $offeringid");
                                $offeringgroupid = (int) $offeringgroup->id;
                            if ($offeringgroupid) {
                                groups_add_member($offeringgroupid,$orguser->id,null,0);
                            }                    
                            if ($orgusergroupid) {
                                groups_add_member($orgusergroupid, $userid,null,0);
                            }
                        }
                    }
                }
                if($type != 'bulkenrollment' && $type != 'replace') {
                    $orderseatsrecord = $DB->get_record_sql("SELECT * FROM {tool_org_order_seats} WHERE tablename='tp_offerings' AND fieldname='id' AND fieldid = $offeringid AND organization =$organization");
                    if(!$orderseatsrecord) {
                        $orgrecord = new stdClass();
                        $orgrecord->productid = $DB->get_field_sql('SELECT id FROM {tool_products} WHERE referenceid = '.$offeringid.' AND category = 1');
                        $orgrecord->tablename = 'tp_offerings';
                        $orgrecord->fieldname = 'id';
                        $orgrecord->fieldid = $offeringid;
                        $orgrecord->orguserid = (is_siteadmin()) ? 0 : $USER->id;
                        $orgrecord->realuser = ($USER->realuser) ? $USER->realuser :0;
                        $orgrecord->purchasedseats =1;
                        $orgrecord->availableseats = 1;
                        $orgrecord->approvalseats = 1;
                        $orgrecord->organization = $organization;
                        $orgrecord->usercreated = $USER->id;
                        $orgrecord->timecreated = time();
                        $DB->insert_record('tool_org_order_seats',$orgrecord);

                    }
                }
            }
            $entype = (!is_siteadmin() && has_capability('local/organization:manage_trainee',$systemcontext)) ? 'program' : 'offering';
            $rootid = (!is_siteadmin() && has_capability('local/organization:manage_trainee',$systemcontext)) ? $program->id: $offeringid;
            $userenrolled = (new trainingprogram)->is_enrolled($rootid,$userid,$entype);
            if(!$userenrolled) {
                $organizationid = (new product)->get_user_organization($userid);
                $row = array();
                $row['programid'] = $program->id;
                $row['offeringid'] = $offeringid?$offeringid:0;
                $row['courseid'] = $program->courseid;
                $row['userid'] = $userid;
                $row['realuser'] =($USER->realuser > 0) ? $USER->realuser :0;
                $row['roleid'] = $role_id;
                $row['timecreated'] = time();
                $row['usercreated'] =$currentuser;
                $row['enrolstatus'] =($oderid > 0) ? 0 : (($type == 'bulkenrollment') ? 0 : 1);
                $row['orderid'] =($oderid > 0) ? $oderid : 0;
                $row['enrolltype'] = ($type == 'bulkenrollment')? 1 :0;
                $row['organization'] = $givenorganization ? $givenorganization : ($organizationid ?  $organizationid : 0);
                $row['productid'] = $productid ? $productid : 0;
                $row['trainertype'] =$trainertype;
                $record = $DB->insert_record('program_enrollments', $row);

                if(($record &&   !has_capability('local/organization:manage_organizationofficial',$systemcontext) && $type !='bulkenrollment') || (is_siteadmin() && $type !='bulkenrollment')){        
                   $traineeroleid = $DB->get_field('role','id',array('shortname'=>'trainee'));                  
         
                    // if($traineeroleid ==   $row['roleid']){
                        $tpdata = new stdClass();
                        $tps = $DB->get_record('local_trainingprogram',array('id'=>$program->id));
                        $tpdata->program_name = $tps->name;                
                        $tpdata->program_arabicname = $tps->namearabic;
                        $tpusers = $DB->get_record('local_users',array('userid'=>$userid));
                        $tpdata->program_userfullname=$tpusers->firstname .' '. $tpusers->middlenameen.' '. $tpusers->thirdnameen.' '. $tpusers->lastname;
                        $tpdata->program_userarabicfullname = $tpusers->firstnamearabic .' '. $tpusers->middlenamearabic.' '. $tpusers->thirdnamearabic.' '. $tpusers->lastnamearabic;
                        $tpdata->roleid = $row['roleid'];
                     
                        $trainee = $DB->get_record('user',array('id'=>$userid)); 
                        (new \local_trainingprogram\notification())->trainingprogram_notification('trainingprogram_enroll', $touser=$trainee,$fromuser = get_admin(),$tpdata,$waitinglistid=0);
                    // }
                   
                } elseif($record &&   has_capability('local/organization:manage_organizationofficial', 
                    $systemcontext) && !is_siteadmin() && $type !='bulkenrollment'){
                    
                    
                   $autoapproval = (new  exams())->autoapproval();
                   if( $autoapproval == 1){ 
                    $traineeroleid = $DB->get_field('role','id',array('shortname'=>'trainee'));               
                    // if($traineeroleid ==   $row['roleid']){
                        $tpdata = new stdClass();
                        $tps = $DB->get_record('local_trainingprogram',array('id'=>$program->id));
                        $tpdata->program_name = $tps->name;                
                        $tpdata->program_arabicname = $tps->namearabic;
                        $tpusers = $DB->get_record('local_users',array('userid'=>$userid));
                        $tpdata->program_userfullname=$tpusers->firstname .' '. $tpusers->middlenameen.' '. $tpusers->thirdnameen.' '. $tpusers->lastname;
                        $tpdata->program_userarabicfullname = $tpusers->firstnamearabic .' '. $tpusers->middlenamearabic.' '. $tpusers->thirdnamearabic.' '. $tpusers->lastnamearabic;
                        $tpdata->roleid = $row['roleid'];
                        $trainee = $DB->get_record('user',array('id'=>$userid)); 
                        $tpdata->orgoff = 'yes';
                        (new \local_trainingprogram\notification())->trainingprogram_notification('trainingprogram_enroll', $touser=$trainee,$fromuser = get_admin(),$tpdata,$waitinglistid=0);
                    // }
    
                   }
              
                }
            }

            $trainee_role_id = $DB->get_field('role','id',array('shortname' => 'trainee'));   

            if($role_id == $trainee_role_id && $type !='cancel' && $type !='replace' && $type !='bulkenrollment') {

                (new product)->upadte_availableseats('tp_offerings', 'id', $offeringid, -1);
            } 

            return $record;
        
    }
    public function program_unenrollment($programid,$offeringid,$courseid,$userid,$roleid,$type = false) {
        global $DB,$USER;

        $systemcontext = context_system::instance();
        $program= $DB->get_record_sql('SELECT tp.id,tp.courseid,tp.price,tpo.code AS offeringcode FROM {local_trainingprogram} as tp 
                                         JOIN {tp_offerings} as tpo ON tpo.trainingid = tp.id 
                                        WHERE tpo.id=:offeringid', ['offeringid' => $offeringid]);
        $manual = enrol_get_plugin('manual');
        $instance = $DB->get_record('enrol', array('courseid' => $program->courseid, 'enrol' => 'manual'), '*', MUST_EXIST);
        $manual->unenrol_user($instance, $userid);
        $group = $DB->get_record_sql("SELECT grop.id FROM {groups} as grop JOIN {tp_offerings} as tpo ON tpo.code = grop.idnumber WHERE tpo.id = $offeringid");
        $groupid = (int) $group->id;
        if ($groupid) {
            groups_remove_member($groupid, $userid);
        }
        $org_official_roleid = $DB->get_field('role','id',array('shortname' => 'organizationofficial')); 
        $enrolled_by_user = (int) $DB->get_field_sql('SELECT usercreated FROM {program_enrollments} WHERE programid = '.$program->id.' AND offeringid = '.$offeringid.' AND courseid = '.$program->courseid.' AND userid = '.$userid.' AND roleid = '.$roleid.'');
        $is_enrolled_by_user_org_official = $DB->record_exists_sql('SELECT id FROM {role_assignments} WHERE  contextid = '.$systemcontext->id.' AND roleid = '.$org_official_roleid.' AND userid IN ('.$enrolled_by_user.')');
        if($is_enrolled_by_user_org_official) {
            $orgshortcode=$DB->get_field_sql('
                        SELECT lorg.shortname FROM {local_organization} AS lorg 
                        JOIN {local_users} AS lou ON lou.organization = lorg.id 
                        WHERE lou.userid ='.$enrolled_by_user.''
            );
            $gorupidnumber1 = $orgshortcode.$program->offeringcode; 
            $gorupidnumber2 = $enrolled_by_user.'_'.$program->offeringcode; 
            $orggroupid = $DB->get_field_sql("
                    SELECT grop.id FROM {groups} AS grop 
                    JOIN {groups_members} AS gropm ON gropm.groupid = grop.id 
                    WHERE gropm.userid =$enrolled_by_user AND grop.courseid = $program->courseid AND (grop.idnumber = '$gorupidnumber1' OR  grop.idnumber = '$gorupidnumber2')"
            );
            if($orggroupid) {
                groups_remove_member($orggroupid, $userid);
            }
        }
        $enrolled_by_user = (int) $DB->get_field_sql('SELECT usercreated FROM {program_enrollments} WHERE programid = '.$programid.' AND offeringid = '.$offeringid.' AND courseid = '.$courseid.' AND userid = '.$userid.' AND roleid = '.$roleid.'');
      
        $org_official_roleid = $DB->get_field('role','id',array('shortname' => 'organizationofficial')); 

        $is_enrolled_by_user_org_official = $DB->record_exists_sql('SELECT id FROM {role_assignments} WHERE  contextid = '.$systemcontext->id.' AND roleid = '.$org_official_roleid.' AND userid IN ('.$enrolled_by_user.')');

        $record=$DB->delete_records('program_enrollments',array('programid'=>$programid,'offeringid'=>$offeringid,'courseid'=>$courseid,'userid'=>$userid,'roleid'=>$roleid)); 
        
        $trainee_role_id = $DB->get_field('role','id',array('shortname' => 'trainee'));   
        if($roleid == $trainee_role_id && $type !='cancel' && $type !='replace') {
            if((is_siteadmin() ||  has_capability('local/organization:manage_trainingofficial',$systemcontext) ||  has_capability('local/organization:manage_communication_officer',$systemcontext)) && $is_enrolled_by_user_org_official) {
                (new product)->upadte_availableseats('tp_offerings', 'id', $offeringid, +1, $enrolled_by_user);
            } else {
                (new product)->upadte_availableseats('tp_offerings', 'id', $offeringid, +1);
            } 
        }
        if($type == 'cancel'){
                if(!is_siteadmin() &&  has_capability('local/organization:manage_organizationofficial',$systemcontext) && $program->price == 0) {
                    $enrollmentexists = $DB->record_exists('program_enrollments',['programid' =>$programid,
                                                                            'offeringid' =>$offeringid,
                                                                            'courseid' =>$courseid,
                                                                            'roleid' =>$trainee_role_id,
                                                                            'usercreated'=>$USER->id
                                                                        ]);                                       
                    if(!$enrollmentexists) {
                        $productid =(int) $DB->get_field_sql('SELECT id FROM {tool_products} WHERE referenceid = '.$offeringid.' AND category = 1');
                        $DB->delete_records('tool_org_order_seats', array('productid' => $productid, 'tablename' =>'tp_offerings' ,'fieldname' => 'id','fieldid' => $offeringid,'orguserid' => $USER->id,'usercreated' => $USER->id));
                    } 
                }
                $tps = $DB->get_record('local_trainingprogram',array('id'=>$programid));
                $tpdata = new stdClass;  
                $tpdata->id = $tps->id;
                $tpdata->program_name = $tps->name;                
                $tpdata->program_arabicname = $tps->namearabic;
                $tpusers = $DB->get_record('local_users',array('userid'=>$userid));
                $tpdata->program_userfullname = $tpusers->firstname .' '. $tpusers->middlenameen.' '. $tpusers->thirdnameen.' '. $tpusers->lastname;
                $tpdata->program_userarabicfullname = $tpusers->firstnamearabic .' '. $tpusers->middlenamearabic.' '. $tpusers->thirdnamearabic.' '. $tpusers->lastnamearabic;
                $tpdata->program_date = date('d-m-Y');
                $tpdata->program_time = date("H:i:s",time());
                $trainee = $DB->get_record('user',array('id'=>$userid)); 
                (new \local_trainingprogram\notification())->trainingprogram_notification('trainingprogram_unenroll', $touser=$trainee,$fromuser=$USER,$tpdata,$waitinglistid=0);
                $event = \local_trainingprogram\event\trainingprogram_cancel::create(
                    array( 
                        'context'=>$systemcontext,
                        'relateduserid'=>$userid,
                        'objectid' =>$programid,
                        'other'=>array(
                            'Message'=>'Cancellation in Training Program'    
                        )
                        )
                    );  
                $event->trigger(); 

        }
        return $record;
    }
    public function competency_data($stable, $filterdata) {
        global $DB, $PAGE, $OUTPUT;
        $systemcontext = context_system::instance();
        $competencies =  $DB->get_field('local_trainingprogram', 'competencyandlevels', ['id' => $stable->programid]);
        $lang = current_language();
         if( $lang == 'ar'){

               $selectsql ="SELECT le.id,le.name,le.arabicname AS competencyname,le.type,le.code FROM {local_competencies} as le " ;
               
        } else {
                $selectsql ="SELECT le.id,le.name AS competencyname,le.arabicname,le.type,le.code FROM {local_competencies} as le " ;
        }
        $countsql  = "SELECT COUNT(le.id) FROM {local_competencies} le  ";

        $formsql  = "  WHERE le.id IN ($competencies) ";
        if(isset($filterdata->search_query) && trim($filterdata->search_query) != ''){
            $formsql .= " AND (le.name LIKE :search OR le.arabicname LIKE :arabicnamesearch)";
            $searchparams = array('search' => '%'.trim($filterdata->search_query).'%','arabicnamesearch' => '%'.trim($filterdata->search_query).'%');
        }else{
            $searchparams = array();
        }     
        $params = array_merge($searchparams);
        $totalcompetency = $DB->count_records_sql($countsql.$formsql, $params);
        $formsql .=" ORDER BY le.id DESC";
        $addedcompetencies = $DB->get_records_sql($selectsql.$formsql, $params, $stable->start,$stable->length);

        $constcompetencytypes = tp::constcompetency_types();
     
        foreach ($addedcompetencies AS $competenciestype) {

            $competenciestype->fullname = $constcompetencytypes[$competenciestype->type];


        }    

        $coursesContext = array(
            "acompetencies" => $addedcompetencies,
            "totalcount" => $totalcompetency,
            "length" => $totalcompetency
        );        
        return $coursesContext;
    }

    public function get_listof_competencies($query = null,$programid = 0,$ctype = null,$offeringid = 0, $level=0) {
        global $DB;
        $fields = array('lc.name', 'lc.arabicname');
        $likesql = array();
        $i = 0;
        $currentlang= current_language();
        if( $currentlang == 'ar'){
            $display_name = 'lc.arabicname';

        } else {
            $display_name = 'lc.name';

        }
        foreach ($fields as $field) {
            $i++;
            $likesql[] = $DB->sql_like($field, ":queryparam$i", false);
            $sqlparams["queryparam$i"] = "%$query%";
        }
        $sqlfields = implode(" OR ", $likesql);
        $concatsql = " AND ($sqlfields) ";


        $ctype=json_decode($ctype);

        if(empty($ctype)) {
            $data =array();

        }elseif(!empty($ctype) && $ctype !='all') {
            // array_walk($ctype, function($x){
            //    $x= "'$x'";
            //    return $x;
            // });

            $types=implode('\',\'',explode(',', $ctype));
            if (!empty($query)) {
                $searchparams = " AND (lc.name LIKE '%". $query ."%' OR lc.arabicname LIKE '%". $query ."%') ";
            }

            $sql = "SELECT lc.id , $display_name AS fullname
                      FROM {local_competencies} lc 
                     WHERE FIND_IN_SET('$level', lc.level) AND lc.type IN ('$types') $searchparams ";

            $data= $DB->get_records_sql($sql,$sqlparams);
            $return = array_values(json_decode(json_encode(($data)), true));

        } else {
            $sql = "SELECT lc.id,$display_name AS fullname FROM {local_competencies} lc  $concatsql ";
            $data = $DB->get_records_sql($sql, $sqlparams);
            $return = array_values(json_decode(json_encode(($data)), true));
        }

        return $data;
    }
    public function get_listof_competencies_for_filters($query = null,$programid = 0,$ctype = 0,$offeringid = 0) {
        global $DB;
        $fields = array('lc.name');
        $likesql = array();
        $i = 0;
        foreach ($fields as $field) {
            $i++;
            $likesql[] = $DB->sql_like($field, ":queryparam$i", false);
            $sqlparams["queryparam$i"] = "%$query%";
        }
        $sqlfields = implode(" OR ", $likesql);
        $concatsql = " WHERE ($sqlfields) ";

        $currentlang= current_language();

        if( $currentlang == 'ar'){

            $sql = "SELECT lc.id,lc.arabicname AS fullname FROM {local_competencies} lc  $concatsql  AND arabicname <> '' AND arabicname IS NOT NULL ";
        } else {

            $sql = "SELECT lc.id,lc.name AS fullname FROM {local_competencies} lc  $concatsql ";
        }

        
        $data = $DB->get_records_sql($sql, $sqlparams);
        $return = array_values(json_decode(json_encode(($data)), true));
        return $return;
    }

    public function get_listof_trainerusers($query = null,$programid = 0,$ctype = null,$offeringid = 0) {
        global $DB;

        $courseid=$DB->get_field('local_trainingprogram','courseid', array('id' => $programid));
        $trainerroleid= $DB->get_field('role', 'id', array('shortname' => 'trainer'));
        $fields = array("lc.firstname",'lc.lastname','lc.firstnamearabic','lc.lastnamearabic','lc.middlenamearabic','lc.thirdnamearabic','lc.middlenameen','lc.thirdnameen');
        $likesql = array();
        $i = 0;

        foreach ($fields as $field) {
            $i++;
            $likesql[] = $DB->sql_like($field, ":queryparam$i", false);
            $sqlparams["queryparam$i"] = "%$query%";
        }
        $sqlfields = implode(" OR ", $likesql);
        $concatsql = " AND ($sqlfields) ";
        $currentlang= current_language();
        // Already enrolled trainers.
        $trainers = $this->get_programs_users_basedon_roles($programid, 'trainer', $offeringid);
        $editingtrainers = $this->get_programs_users_basedon_roles($programid, 'editingtrainer', $offeringid);
        $combined_trainers = array_merge($trainers, $editingtrainers);

        if ($combined_trainers) {
            foreach($combined_trainers as $trainer) {
                $trainerids[] = $trainer->id;
            }
            $ids = implode(',', $trainerids);
        }
        // print_r($ids);die;
        $where = '';
        if ($ids) {
            $where = " AND u.id NOT IN(".$ids.")";
        }
        $displaying_name = (new trainingprogram)->user_fullname_case();
        $sql = " SELECT u.id,$displaying_name
            FROM {user} u 
            JOIN {local_users} lc ON lc.userid = u.id
            JOIN {role_assignments} as ra ON ra.userid = u.id
            JOIN {role} as r ON r.id = ra.roleid 
            WHERE u.id > 2 AND u.deleted = 0 $where  AND u.id AND r.shortname IN ('trainer', 'editingtrainer') $concatsql ";

        $order = " ORDER BY u.id DESC limit 50";
        $data = $DB->get_records_sql($sql.$order, $sqlparams);
        $return = array_values(json_decode(json_encode(($data)), true));
        return $return;
    }

    public function get_listof_loginasusers($query = null,$userid = 0,$ctype = null,$localuserid = 0) {
        global $DB;

        $courseid=$DB->get_field('local_trainingprogram','courseid', array('id' => $programid));
        $trainerroleid= $DB->get_field('role', 'id', array('shortname' => 'trainer'));
        $fields = array("lc.firstname",'lc.lastname','lc.firstnamearabic','lc.lastnamearabic','lc.middlenamearabic','lc.thirdnamearabic','lc.middlenameen','lc.thirdnameen','u.email','u.idnumber','u.username');
        $likesql = array();
        $i = 0;

        foreach ($fields as $field) {
            $i++;
            $likesql[] = $DB->sql_like($field, ":queryparam$i", false);
            $sqlparams["queryparam$i"] = "%$query%";
        }
        $sqlfields = implode(" OR ", $likesql);
        $concatsql = " AND ($sqlfields) ";
        $currentlang= current_language();
        if ($userid) {
            $where = " AND u.id NOT IN(".$userid.")";
        }
        $displaying_name = (new trainingprogram)->user_fullname_case();
        $sql = " SELECT u.id,$displaying_name
            FROM {user} u 
            JOIN {local_users} lc ON lc.userid = u.id
            JOIN {role_assignments} as ra ON ra.userid = u.id
            JOIN {role} as r ON r.id = ra.roleid AND r.shortname = 'trainee'
            WHERE u.id > 2 AND u.deleted = 0 $where  AND u.id $concatsql ";

        $order = " ORDER BY u.id DESC limit 50";
        $data = $DB->get_records_sql($sql.$order, $sqlparams);
        $return = array_values(json_decode(json_encode(($data)), true));
        return $return;
    }

    

    public function get_listof_orgofficial($query = null,$organization = 0,$ctype = null,$offeringid = 0) {
        global $DB;

        global $DB;
        $roleid= $DB->get_field('role', 'id', array('shortname' => 'organizationofficial'));
        $fields = array("lc.firstname",'lc.lastname','lc.firstnamearabic','lc.lastnamearabic','lc.middlenamearabic','lc.thirdnamearabic','lc.middlenameen','lc.thirdnameen');
        $likesql = array();
        $i = 0;
        foreach ($fields as $field) {
            $i++;
            $likesql[] = $DB->sql_like($field, ":queryparam$i", false);
            $sqlparams["queryparam$i"] = "%$query%";
        }
        $sqlfields = implode(" OR ", $likesql);
        $concatsql = " AND ($sqlfields) ";
        $currentlang= current_language();

        $displaying_name = (new trainingprogram)->user_fullname_case();
        
        $sql = " SELECT u.id,$displaying_name
        FROM {user} u
        JOIN {local_users} lc ON lc.userid = u.id
        JOIN {role_assignments} ra ON ra.userid =  u.id
        JOIN {role} r ON r.id = ra.roleid
        WHERE r.id = $roleid AND lc.organization = $organization AND u.phone1  REGEXP '^[5][0-9]{8}$' AND CHAR_LENGTH(u.idnumber) >=10  AND u.id $concatsql ";
        
        $order = " ORDER BY u.id DESC limit 50";
        $data = $DB->get_records_sql($sql.$order,$sqlparams);
        $return = array_values(json_decode(json_encode(($data)), true));
        return $return;
    }
     public function get_card_view_trainingprograms_list($searchquery='', $sector='') {
        global $DB, $PAGE, $OUTPUT;
        $renderer = $PAGE->get_renderer('local_trainingprogram');
        $filterparams  = $renderer->get_trainingprograms_cardview(true);
        $filterparams['submitid'] = 'form#filteringform';
        $training_programs = $renderer->get_trainingprograms_cardview(false, '', $sector);
        $cfform = trainingprogram_filters_form($filterparams);
        $filterparams['training_programs_cards'] = $training_programs;
        $filterparams['widthclass'] = 'col-md-12';
        $filterparams['inputclasses'] = 'trainingprogramsearchinput';
        $filterparams['placeholder'] = get_string('sreach_program', 'local_trainingprogram');
        $filterparams['q'] = $searchquery;
        $filterparams['filterinput'] = $renderer->global_filter($filterparams);
        $filterparams['card_view_programs_filterform'] = $cfform->render();

        $renderer->listofcardviewprograms($filterparams);
    }

     public function card_view_programs($stable, $filterdata) {
        global $DB, $PAGE, $OUTPUT, $CFG, $USER,$SESSION;
       
        $SESSION->lang = ($stable->mlang) ? $stable->mlang : current_language() ;
        $current_language = current_language();
        $selectsql = "SELECT * FROM {local_trainingprogram} lo  "; 
        $countsql  = "SELECT COUNT(lo.id) FROM {local_trainingprogram} lo  ";
        $inprogressdatecondition = (new trainingprogram())->from_unixtime_for_live_entities('lo.availableto');
        $formsql  = " WHERE published = 1 AND  $inprogressdatecondition ";
        if  (isset($filterdata->search_query) && trim($filterdata->search_query) != ''){
            $formsql .= " AND (lo.name LIKE :firstnamesearch OR lo.namearabic LIKE :arabicnamesearch) ";
            $searchparams = array('firstnamesearch' => '%'.trim($filterdata->search_query).'%','arabicnamesearch' => '%'.trim($filterdata->search_query).'%');
        } else {
            $searchparams = array();
        }

        if (!empty($filterdata->sectors)){
             $sectorids = explode(',', $filterdata->sectors);
             if(!empty($sectorids)){
                $sectorquery = array();
                foreach ($sectorids as $sector) {
                    $sectorquery[] = " CONCAT(',',lo.sectors,',') LIKE CONCAT('%,',$sector,',%') "; 
                }
                $sectoqueeryparams =implode('OR',$sectorquery);
                $formsql .= ' AND ('.$sectoqueeryparams.') ';
            }
        }
        
        if(!empty($filterdata->targetgroup)){

            $jobfamilyids = explode(',', $filterdata->targetgroup);
            if(!empty($jobfamilyids)){
                $jobfamilyquery = array();
                foreach ($jobfamilyids as $jobfamily) {
                    $jobfamilyquery[] = " CONCAT(',',lo.targetgroup,',') LIKE CONCAT('%,',$jobfamily,',%') "; 
                }
                $jobfamilyparams =implode('OR',$jobfamilyquery);
                $formsql .= ' AND ( ('.$jobfamilyparams.') OR lo.targetgroup = -1 ) ';
            }
        }

        if(!empty($filterdata->program_competencylevel)){

            $competencylevelsids = explode(',', $filterdata->program_competencylevel);
            if(!empty($competencylevelsids)){
                $competencylevelquery = array();
                foreach ($competencylevelsids as $competencylevel) {
                    $competencylevelquery[] = " CONCAT(',',lo.competencyandlevels,',') LIKE CONCAT('%,',$competencylevel,',%') "; 
                }
                $competencylevelparams =implode('OR',$competencylevelquery);
                $formsql .= ' AND ('.$competencylevelparams.') ';
            }
        }

        if($filterdata->{'availablefrom[enabled]'} == 1 ){

            $start_year = $filterdata->{'availablefrom[year]'};
            $start_month = $filterdata->{'availablefrom[month]'};
            $start_day = $filterdata->{'availablefrom[day]'};
            $filter_starttime_con = mktime(0,0,0, $start_month, $start_day, $start_year);
            $formsql.= " AND lo.availableto >= '$filter_starttime_con' ";

        }
        if($filterdata->{'availableto[enabled]'} == 1 ){
            $start_year = $filterdata->{'availableto[year]'};
            $start_month = $filterdata->{'availableto[month]'};
            $start_day = $filterdata->{'availableto[day]'};
            $filter_endtime_con=mktime(23,59,59, $start_month, $start_day, $start_year);
            $formsql.=" AND lo.availableto <= '$filter_endtime_con' ";
        }

        $params = array_merge($searchparams);

        $cardviewtotalprograms = $DB->count_records_sql($countsql.$formsql,$params);
        $formsql .=" ORDER BY lo.id DESC";

        if (empty($stable->thead)) {
            $cardviewtrainingprograms = $DB->get_records_sql($selectsql.$formsql, $params, $stable->start,$stable->length);
        } else {
            $cardviewtrainingprograms = $DB->get_records_sql($selectsql.$formsql, $params);
        }

        $cardviewtrainingprogramlist = array();
        $count = 0;
        foreach($cardviewtrainingprograms as $cardviewtrainingprogram) {
            $traineeroleid = $DB->get_field('role', 'id', array('shortname' => 'trainee'));
            $trainerroleid = $DB->get_field('role', 'id', array('shortname' => 'trainer'));

            $isuserenrolledastrainee =  $DB->record_exists('program_enrollments',['programid' => $cardviewtrainingprogram->id, 'userid' => $USER->id, 'roleid' => $traineeroleid]);
            $isuserenrolledastrainer =  $DB->record_exists('program_enrollments',['programid' => $cardviewtrainingprogram->id, 'userid' => $USER->id, 'roleid' => $trainerroleid]);
           
              $cardviewtrainingprogramlist[$count]['isenrolled'] = ($isuserenrolledastrainee || $isuserenrolledastrainer) ? true :  false;
              $cardviewtrainingprogramlist[$count]['trainingid'] = $cardviewtrainingprogram->id;

              if($current_language == 'ar' || $SESSION->lang == 'ar') {
                $cardviewtrainingprogramlist[$count]['programname'] = $cardviewtrainingprogram->namearabic;

              } else {

                $cardviewtrainingprogramlist[$count]['programname'] = $cardviewtrainingprogram->name;
              }
              $programdesciption =format_text($cardviewtrainingprogram->description, FORMAT_HTML);
              
              $cardviewtrainingprogramlist[$count]['sellingprice'] = round($cardviewtrainingprogram->sellingprice,2);
              $cardviewtrainingprogramlist[$count]['actualprice'] = round($cardviewtrainingprogram->actualprice,2);
              $cardviewtrainingprogramlist[$count]['description'] = mb_substr(strip_tags($programdesciption),0,200);
              $cardviewtrainingprogramlist[$count]['courseid'] = $cardviewtrainingprogram->courseid;
              if($current_language == 'ar' || $SESSION->lang == 'ar') {                 
                    list($sectorsql,$ectorparams) = $DB->get_in_or_equal(explode(',',$cardviewtrainingprogram->sectors));
                    $querysql = "SELECT id AS sectorid ,titlearabic AS sectorname FROM {local_sector} WHERE id $sectorsql";
                    $sectorslists= $DB->get_records_sql($querysql,$ectorparams);
              } else{
                    list($sectorsql,$ectorparams) = $DB->get_in_or_equal(explode(',',$cardviewtrainingprogram->sectors));
                    $querysql = "SELECT id AS sectorid ,title AS sectorname FROM {local_sector} WHERE id $sectorsql";
                    $sectorslists= $DB->get_records_sql($querysql,$ectorparams);
              }

              if (!empty($cardviewtrainingprogram->image)) {
                 $cardviewtrainingprogramlist[$count]['imageurl']=trainingprogramlogo_url($cardviewtrainingprogram->image);
                 $cardviewtrainingprogramlist[$count]['image']=trainingprogramlogo_url($cardviewtrainingprogram->image);
               }
              $cardviewtrainingprogramlist[$count]['sectors'] = array_values($sectorslists);
              $cardviewtrainingprogramlist[$count]['actionview'] = (isloggedin() && !$DB->record_exists('tp_offerings', array('trainingid' => $cardviewtrainingprogram->id))) ? false : true;

              $B = false;
              $V = false;
              $F = false;
              $I = false;
                if($cardviewtrainingprogram->sectors){
                    $programsectors = $DB->get_fieldset_sql('select code from {local_sector} where id in('.$cardviewtrainingprogram->sectors.')');
                    $listedsectors = ['V', 'F', 'I', 'B'];
                    foreach($listedsectors as $sector){
                        if(in_array($sector, $programsectors)){
                            ${$sector} = true;
                        }
                    }
                }else{
                    $programsectors = '';
                }

              $cardviewtrainingprogramlist[$count]['banking'] = $B;
              $cardviewtrainingprogramlist[$count]['capitalmarket'] = $V;
              $cardviewtrainingprogramlist[$count]['finance'] = $F;
              $cardviewtrainingprogramlist[$count]['insurance'] = $I;
              $cardviewtrainingprogramlist[$count]['hours'] = round($cardviewtrainingprogram->hour / 86400);
              $cardviewtrainingprogramlist[$count]['durationindays'] = round(($cardviewtrainingprogram->duration / 86400));
              $cardviewtrainingprogramlist[$count]['programdetailsurl'] = $CFG->wwwroot.'/local/trainingprogram/programcourseoverview.php?programid='.$cardviewtrainingprogram->id;                 
            $count++;
        }
        if($cardviewtotalprograms > 6){
            $noloadmore = true;
        }
        if($cardviewtotalprograms == count($cardviewtrainingprogramlist)){
            $noloadmore = false;
        }
        $coursesContext = array(
            "programs" => $cardviewtrainingprogramlist,
            "totalprograms" => $cardviewtotalprograms,
            "length" => count($cardviewtrainingprogramlist),
            'noloadmore' => $noloadmore
        );
        return $coursesContext;
    }

     public function remove_all_program_offerings($programid,$courseid,$deletion = 0) {
        global $DB, $USER;

        $systemcontext = context_system::instance();

        if($DB->record_exists('tp_offerings',array('trainingid'=>$programid))){

            $allofferings  = $DB->get_records_sql('SELECT * FROM {tp_offerings} WHERE trainingid = '.$programid.'');

            foreach ($allofferings AS $offering) {

                $DB->start_delegated_transaction();
                try{
                    $transaction = $DB->start_delegated_transaction();
                   
                    $enrolledusers = $DB->get_records_sql('SELECT * FROM {program_enrollments} WHERE programid = '.$programid.' AND offeringid = '.$offering->id.' AND courseid = '.$courseid.' ');
                    foreach ($enrolledusers AS $enrolleduser) {

                        role_unassign($enrolleduser->roleid,$enrolleduser->userid,$systemcontext->id);
                    }
                    $DB->delete_records('program_enrollments', array('programid' => $programid, 'offeringid' => $offering->id,'courseid' => $courseid));
                    $sectionid = $offering->sections;
                    $cmidsql = "SELECT com.id FROM {course_modules} as com JOIN {course_sections} as cos ON com.section = cos.id AND com.course = cos.course WHERE  cos.course = $courseid AND cos.section = $sectionid AND (com.module = 'attendance' OR com.module = 'quiz' OR com.module = 'zoom' OR com.module = 'webexactivity' OR com.module = 'teamsmeeting')";
                    $allmodules =$DB->get_records_sql($cmidsql);

                    foreach ($allmodules AS $module) {
                        $cmid = (int) $module->id;
                        if($cmid){
                            course_delete_module($cmid);
                        }
                    }
                    $result = course_delete_section($courseid, $offering->sections);
                    (new trainingprogram)->delete_offering_sessions($offering->id);
                    (new trainingprogram)->delete_offering_mapped_groups($offering->code);
                    $offdelete = $DB->delete_records('tp_offerings', array('id' => $offering->id));

                   $transaction->allow_commit();
                    if(!$deletion) {
                      return true;
                    }
                   
                } catch(moodle_exception $e){
                  $transaction->rollback($e);
                  return false;

                }
            }
        }    
    }

    public function remove_schedule_program($sheduleid,$delete = 0) {
        global $DB;
        $record = $DB->get_record('tp_offerings', ['id' => $sheduleid]);
        $courseid = $DB->get_field('local_trainingprogram', 'courseid', ['id' => $record->trainingid]);
        $sections = $DB->get_field('course_sections','section',['id' => $record->sections]);
        try{
            $transaction = $DB->start_delegated_transaction();
            course_delete_section($courseid, $sections);
            (new trainingprogram)->delete_offering_sessions($sheduleid);
            (new trainingprogram)->delete_offering_mapped_groups($record->code);
            $offresult = $DB->delete_records('tp_offerings',array('id' =>$sheduleid));
            $transaction->allow_commit();
            if(!$delete) {
                return true;
            }

        } catch(moodle_exception $e){
            $transaction->rollback($e);
         return false;
        }
    }

    public function detailed_program_course_overview($programid,$courseid,$api = false) {
        global $DB,$USER,$OUTPUT,$CFG;
        $systemcontext = context_system::instance();
        $params = [];
        $program_exists = $DB->record_exists('local_trainingprogram',array('id' => $programid));
        if (!$program_exists) {

            if(!isloggedin()) {

                redirect($CFG->wwwroot.'/local/trainingprogram/trainingprogram.php');

            } else {

                redirect($CFG->wwwroot.'/local/trainingprogram/index.php');

            }
            
        }

        $lang = current_language();
        $tptandc =$this->checktermsandconditions($programid);
         
        $traineeeid = $DB->get_field('role', 'id', array('shortname' => 'trainee'));
        $programrecord=$DB->get_record('local_trainingprogram',array('id'=>$programid));
                           
        list($presql,$preparams) = $DB->get_in_or_equal(explode(',',$programrecord->prerequirementsprograms));

                      
        if( $lang == 'ar'){

            $prequerysql = "SELECT id AS preid ,namearabic AS prename FROM {local_trainingprogram} WHERE id $presql";          

        } else {

            $prequerysql = "SELECT id AS preid,name AS prename FROM {local_trainingprogram} WHERE id $presql";


        } 

        $prelists= $DB->get_records_sql($prequerysql,$preparams);

        foreach($prelists as $pre){

            $pre->prename = $pre->prename;
             
        }

        list($postsql,$postparams) = $DB->get_in_or_equal(explode(',',$programrecord->postrequirementsprograms));

                      
        if( $lang == 'ar'){

            $postquerysql = "SELECT id AS postid,namearabic AS postname FROM {local_trainingprogram} WHERE id $postsql";          

        } else {

            $postquerysql = "SELECT id AS postid,name AS postname  FROM {local_trainingprogram} WHERE id $postsql";


        } 

        $postlists= $DB->get_records_sql($postquerysql,$postparams);

        foreach($postlists as $post){

            $post->postname = $post->postname;
        }
         

        list($topicsql,$topicparams) = $DB->get_in_or_equal(explode(',',$programrecord->trainingtopics));

        list($fourtopicsql,$fourtopicparams) =   $DB->get_in_or_equal(explode(',',$programrecord->trainingtopics));

        
              
        if( $lang == 'ar'){

           
            $fourtopicquery =  "SELECT id AS topicid,name AS topicname FROM {training_topics} WHERE id $fourtopicsql LIMIT 2 " ;
            $topicquerysql = "SELECT id AS topicid,name AS topicname FROM {training_topics} WHERE id  $topicsql";        

        } else {


            $fourtopicquery = "SELECT id AS topicid,name AS topicname FROM {training_topics} WHERE id $fourtopicsql LIMIT 2 ";
            $topicquerysql = "SELECT id AS topicid,name AS topicname FROM {training_topics} WHERE id $topicsql ";
           

        } 



        $topicslists = $DB->get_records_sql($topicquerysql,$topicparams);

        $fourtopics = $DB->get_records_sql($fourtopicquery,$fourtopicparams);
        


        foreach( $fourtopics AS $fourtopicslist) {
                                         
            if( $lang == 'ar'){
                preg_match('/{mlang ar}(.*?){mlang}/',$fourtopicslist->topicname, $match);
                $arabictitle =  $match[1];  

                $fourtopicslist->topicname = $arabictitle;
                
           }else{

               preg_match('/{mlang en}(.*?){mlang}/',$fourtopicslist->topicname, $match);
               $englishtitle =  $match[1];  

               $fourtopicslist->topicname = $englishtitle;

           }

        }







    
        
        foreach($topicslists AS $topicslist) {
                                         
            if( $lang == 'ar'){
                preg_match('/{mlang ar}(.*?){mlang}/',$topicslist->topicname, $match);
                $arabictitle =  $match[1];  

                $topicslist->topicname = $arabictitle;
                
           }else{

               preg_match('/{mlang en}(.*?){mlang}/',$topicslist->topicname, $match);
               $englishtitle =  $match[1];  

               $topicslist->topicname = $englishtitle;

           }

        }
         
        list($sectorsql,$sectorparams) = $DB->get_in_or_equal(explode(',',$programrecord->sectors));

        if( $lang == 'ar'){

            $sectorquerysql = "SELECT id AS sectorid,titlearabic AS sectorname FROM {local_sector} WHERE id $sectorsql";

        } else {

            $sectorquerysql = "SELECT id AS sectorid,title AS sectorname FROM {local_sector} WHERE id $sectorsql";
        }   
        
        $sectorslists= $DB->get_records_sql($sectorquerysql,$sectorparams);

        if($programrecord->targetgroup == '-1') {

         $jobfamilies = (new trainingprogram)->get_all_job_families_by_sectors($programrecord->sectors);
         $targetgroup = implode(',', $jobfamilies);

        } else if ($programrecord->newjobfamilyoption && $programrecord->targetgroup != '0' && $programrecord->targetgroup != '-1' && $programrecord->targetgroup != null) {
            $jobfamilies = (new trainingprogram)->get_all_job_families_by_sectors($programrecord->newjobfamilyoption);
            $targetgroup = $programrecord->targetgroup.','.implode(',', $jobfamilies);

        } else if ($programrecord->newjobfamilyoption) {
            
            $jobfamilies = (new trainingprogram)->get_all_job_families_by_sectors($programrecord->newjobfamilyoption);
            $targetgroup = implode(',', $jobfamilies);
          
        }else {
          $targetgroup = $programrecord->targetgroup;

        }  
      
        list($jobfamilysql,$jobfamilyparams) = $DB->get_in_or_equal(explode(',',$targetgroup));
        if( $lang == 'ar'){

           $jobfamilyquerysql = "SELECT id AS jobfamilyid,familynamearabic AS fullname,segmentid  FROM {local_jobfamily} WHERE id $jobfamilysql";

        } else {

            $jobfamilyquerysql = "SELECT id AS jobfamilyid,familyname AS fullname,segmentid FROM {local_jobfamily} WHERE id $jobfamilysql";
        } 
        
        $jobfamilylist= $DB->get_records_sql($jobfamilyquerysql,$jobfamilyparams);

        foreach($jobfamilylist AS $jobfamily) {
           
            
           if($lang == 'ar'){
                if(empty($jobfamily->segmentid)) {
                     $jobfamily->fullname = html_writer::tag('a', $jobfamily->fullname,array('href' =>'https://fa.gov.sa/ar/jadarat/Pages/jobRoles.aspx?FId='.$jobfamily->jobfamilyid));

                } else {
                    $jobsectorid = $DB->get_fieldset_sql('SELECT sectorid FROM {local_segment} WHERE id IN ('.$jobfamily->segmentid.')');
                    $secid = implode(', ', $jobsectorid);
                    $jobfamily->fullname =  (count($jobsectorid)  == 1) ? html_writer::tag('a', $jobfamily->fullname,array('href' =>'https://fa.gov.sa/ar/jadarat/Pages/jobRoles.aspx?FId='.$jobfamily->jobfamilyid.'&SId='.$secid.'')): html_writer::tag('a', $jobfamily->fullname,array('href' =>'https://fa.gov.sa/ar/jadarat/Pages/jobRoles.aspx?FId='.$jobfamily->jobfamilyid))  ; 

                }
            }

            if($lang == 'en'){
                if(empty($jobfamily->segmentid) || count($jobsectorid)  == 0) {
                     $jobfamily->fullname = html_writer::tag('a', $jobfamily->fullname,array('href' =>'https://fa.gov.sa/en/jadarat/Pages/jobRoles.aspx?FId='.$jobfamily->jobfamilyid));

                } else {

                    $secid = implode(', ', $jobsectorid);
                    $jobfamily->fullname =  (count($jobsectorid)  == 1) ? html_writer::tag('a', $jobfamily->fullname,array('href' =>'https://fa.gov.sa/en/jadarat/Pages/jobRoles.aspx?FId='.$jobfamily->jobfamilyid.'&SId='.$secid.'')): html_writer::tag('a', $jobfamily->fullname,array('href' =>'https://fa.gov.sa/en/jadarat/Pages/jobRoles.aspx?FId='.$jobfamily->jobfamilyid))  ; 

                }
            }

        }
             
        list($competencysql,$competencyparams) = $DB->get_in_or_equal(explode(',',$programrecord->competencyandlevels));
       
        $compquerysql = "SELECT type,type AS fullname, id FROM {local_competencies} WHERE id $competencysql";
        
        $programcompetencies= $DB->get_records_sql($compquerysql,$competencyparams);
         
        $constcompetencytypes = tp::constcompetency_types();
     
        foreach ($programcompetencies AS $competenciestype) {

            $competenciestype->fullname = $constcompetencytypes[$competenciestype->fullname];

            if( $lang == 'ar'){

               $competenciessql ="SELECT id as competencyid, arabicname AS competencyname FROM {local_competencies} WHERE 1=1" ;
            } else {
                $competenciessql ="SELECT id as competencyid, name AS competencyname FROM {local_competencies} WHERE 1=1" ;
            }
            $competencyids = explode(',', $programrecord->competencyandlevels);
            $competencyquery =array();
            foreach ($competencyids as $competency) {
                $competencyquery[] = "  id = $competency "; 
            }
            $ctype = "'$competenciestype->type'";
            $competencyqueryparams =implode('OR',$competencyquery);
            $formsql = ' AND ('.$competencyqueryparams.') AND type = '.$ctype.'';
            $competencieslists = $DB->get_records_sql($competenciessql.$formsql); 
            foreach ($competencieslists AS $competencieslist) {
            
              $competencieslist->competencylevel= $programrecord->clevels ? str_replace("level","", $programrecord->clevels):0;
          
              // $competencieslist->competencyname = html_writer::tag('a', $competencieslist->competencyname,array('href' =>$CFG->wwwroot. '/local/competency/index.php?id='.$competencieslist->competencyid));

              if ($lang == 'ar') {
                $competencieslist->competencyname = html_writer::tag('a', $competencieslist->competencyname,array('href' =>'https://fa.gov.sa/ar/jadarat/Pages/JedaraCard.aspx?CId='.$competencieslist->competencyid));    
              }
              if ($lang == 'en') {
                $competencieslist->competencyname = html_writer::tag('a', $competencieslist->competencyname,array('href' =>'https://fa.gov.sa/en/jadarat/Pages/JedaraCard.aspx?CId='.$competencieslist->competencyid));         
              }

                // $competencieslist->competencyname = html_writer::tag('a', $competencieslist->competencyname,array('href' =>'https://fa.gov.sa/en/jadarat/Pages/JedaraCard.aspx?CId='.$competencieslist->competencyid));

            }
            $competenciestype->competencies=array_values($competencieslists);
        }


        $sectionsquerysql = "SELECT id AS sectionid FROM {course_sections} WHERE course = $courseid";
        $sections= $DB->get_records_sql($sectionsquerysql); 
        $totalsectionscount_sql="SELECT count(id) FROM {course_sections} WHERE course =$courseid";
        $totalsectionscount=$DB->count_records_sql($totalsectionscount_sql); 
        $total_modules_sql="SELECT COUNT(module) FROM {course_modules} WHERE course = $courseid AND section IN (SELECT id FROM {course_sections} WHERE course = $courseid)";
        $total_modules = $DB->count_records_sql($total_modules_sql);
        $i=1;

        $days =$programrecord->duration / 86400;
        $programdays = array();

        for ($i = 1; $i <= $days; $i++) {

            $programday =array();
            $programday['selected'] = ($i == 1)? true : false;
            $programday['day'] = $i;
            $sql ='SELECT * FROM {program_agenda} WHERE programid =:programid AND day =:day';
            $record = $DB->get_record_sql($sql,['programid'=>$programrecord->id,'day'=>$i]);
            $programday['agenda'] = $record->description ? format_text($record->description,FORMAT_HTML) : get_string('no_agenda_available','local_trainingprogram');
            $programdays[] = $programday;

        }
       
        $tpofferings = self::get_offerings($programid);
 
        $programmethods = explode(',',$programrecord->trainingtype);

        foreach ( $programmethods AS $programmethod) {

            if($programmethod == 'online') {
                $aprogramtype[]['name']= get_string('scheduleonline','local_trainingprogram'); 
            } elseif ($programmethod == 'offline') {
                $aprogramtype[]['name']= get_string('scheduleoffline','local_trainingprogram');

            } elseif ($programmethod == 'elearning') {
                $aprogramtype[]['name']= get_string('scheduleelearning','local_trainingprogram');  

            }  
        }

        $eprogrammethods = explode(',',$programrecord->methods);
        foreach ( $eprogrammethods AS $eprogrammethod) {

            if($eprogrammethod == 0) {
                $aprogrammethods[]['name']= get_string('lecture','local_trainingprogram');
            } elseif ($eprogrammethod == 1) {
                $aprogrammethods[]['name']= get_string('case_studies','local_trainingprogram');

            } elseif ($eprogrammethod == 2) {
                $aprogrammethods[]['name']= get_string('dialogue_teams','local_trainingprogram');

            } else {
                $aprogrammethods[]['name']= get_string('exercises_assignments','local_trainingprogram');

            }
           
        }


        $emethods = explode(',',$programrecord->dynamicevaluationmethod);
        foreach ( $emethods AS $method) {
            $evaluation_methodname=$DB->get_record('evalution_methods', array('id'=>$method));
            $aevaluationmethods[]['name']= format_string($evaluation_methodname->name);
        }

      
        $languages = explode(',',$programrecord->languages);
        foreach ( $languages AS $language) {

            $actuallang[]=($language == '1') ? get_string('english','local_trainingprogram') : get_string('arabic','local_trainingprogram');

        }  

    
        if( $lang == 'ar' && !empty($programrecord->namearabic)){
            $programname = $programrecord->namearabic;
        }else{
            $programname = $programrecord->name;
        }
        $goalssql = "SELECT *  FROM {program_goals} WHERE programid = $programid";
        $programgoals= $DB->get_records_sql($goalssql);
        foreach($programgoals AS $goal) {
            $goal->programgoal = format_text($goal->programgoal,FORMAT_HTML);
        }
        $imageurl=trainingprogramlogo_url($programrecord->image);
        $description = format_text($programrecord->description,FORMAT_HTML);
        $stringlength = strlen(strip_tags($description));

        $lang = current_language();

        if ($lang == 'ar') {
            if($stringlength < 600){
                $length = false;
            } else {
                $length = true;
            }             
        }

        if ($lang == 'en') {
            if($stringlength < 350){
                $length = false;
            } else {
                $length = true;
            }             
        }

        $systemcontext = context_system::instance();
          $isorgofficial=(!is_siteadmin() && has_capability('local/organization:manage_organizationofficial',$systemcontext)) ? true : false;
                $istrainee=(!is_siteadmin() && (has_capability('local/organization:manage_trainee',$systemcontext)));   
                if($isorgofficial || $istrainee){
                 $isorgofficialortrainee= true;
                 $userid=$USER->id;
                 }else{
                  $isorgofficialortrainee= false;
                 }
                 $component='local_trainingprogram';
                 $checkfavornot =(new exams)->checkfavourites($programid,$USER->id,$component);

        /*$tpofferings = self::get_offerings($programid);         
        foreach ($tpofferings AS $tpofferingsforlang) {
            $offactuallang=($tpofferingsforlang->languages == '1') ? get_string('english','local_trainingprogram') : get_string('arabic','local_trainingprogram');
           $offcourselanguage=$offactuallang;



        }*/
        $is_enrolled = (new trainingprogram)->is_enrolled($programrecord->id,$USER->id);


  
        $externallink=$programrecord->externallink;
        $viewdata=[
            'isorgofficialortrainee'=>$isorgofficialortrainee,
            'externallink'=>$externallink,
            'is_external_link' => ($externallink)?true:false,
            'checkfavornot'=>$checkfavornot,
            'userid'=>$userid,
            'courseid'=>$courseid,
            'programid'=>$programid,
            'programname'=>$programname,
            'programcode'=>$programrecord->code,
            'programnameview'=>true ,
            'sellingprice'=> round($programrecord->sellingprice,2),
            'program_description'=> format_text(strip_tags($programrecord->description,FORMAT_HTML)),

            'length_description' => $length,
            'program_goals'=> array_values($programgoals),
            'priceview'=>($programrecord->price == 1 &&  !is_null($programrecord->sellingprice)) ? true : false,
            'sectorview'=>$programrecord->sectors? true : false,
            'competenciesview'=>$programrecord->competencyandlevels? true : false,
            'jobfamilyview'=>($programrecord->targetgroup || $programrecord->newjobfamilyoption)? true : false,
            'trainingtopicsview' => $programrecord->trainingtopics? true : false,
            'trainingtypesview' => $programrecord->trainingtype? true : false,
            'preprogramsview' => $programrecord->prerequirementsprograms? true : false,
            'postprogramsview' => $programrecord->postrequirementsprograms? true : false,
            'duration'=>$programrecord->duration,
            'currentofferings'=>$tpofferings,
            'jobfamilylist'=>array_values($jobfamilylist),
            'hasjobfamilylistmore'=>count(array_values($jobfamilylist))>4?true :false,
            'fourtrainingtopiclist'=>array_values($fourtopics),
            'trainingtopiclist'=> array_values($topicslists),
            'hastrainingtopicslistmore'=>count(array_values($topicslists))>2?true :false,
            'trainingtypelist'=>array_values($aprogramtype),
            'hastrainingtypeslistmore'=>count(array_values($aprogramtype))>4?true :false,
            'preprogramlist'=>array_values($prelists),
            'haspreprogramlistmore'=>count(array_values($prelists))>4?true :false,
            'postprogramlist'=>array_values($postlists),
            'haspostprogramslistmore'=>count(array_values($postlists))>4?true :false,
            'releasedate'=>userdate($programrecord->availablefrom, get_string('strftimedatemonthabbr', 'langconfig')),
            'availabletodate'=>userdate($programrecord->availableto, get_string('strftimedatemonthabbr', 'langconfig')),
            'availabletotime'=>date('h:i A', $programrecord->availableto ),
            'sectorslists'=>array_values($sectorslists),
            'evaluationmethod'=>$aevaluationmethods,
            'evaluationmethoddisplay'=>($programrecord->evaluationmethods != '')? true : false,
            'trainingmethods'=>$aprogrammethods,
            'totalmodules'=>$total_modules ? $total_modules : 0,
            'totaltopics'=>$total_topics ? $total_topics : 0,
            'courselanguage'=>$actuallang ? implode('/',$actuallang) :'-',
            //'offcourselanguage'=>$offcourselanguage,
            'programcompetencies'=>array_values($programcompetencies),
            'sections'=>array_values($sections),
            'program_days'=>$programdays,
            'sectionscount'=>$totalsectionscount,
            'durationinweeks'=>round(($programrecord->duration / 86400)/7),
            'durationindays'=>round($programrecord->duration / 86400), 
            'programagendaview'=>(($programrecord->duration / 86400) > 0 && $DB->record_exists('program_agenda',array('programid' => $programrecord->id))) ? true : false, 
            'offeringview'=>($DB->record_exists('tp_offerings',array('trainingid' => $programid)) && COUNT($tpofferings) > 0) ? true : false, 
            'imageurl'=>$imageurl,
            'isloggedin' => (!isloggedin()) ? true : false,
            'product_attributes' => (new product)->get_product_attributes($programid, 1, 'addtocart', true),
            'cartactionview'=> ((isloggedin() && !is_siteadmin() && has_capability('local/organization:manage_trainee', $systemcontext) &&
                                 !$is_enrolled && date('Y-m-d',$programrecord->availableto) > date('Y-m-d')) || (!is_siteadmin() && !$DB->record_exists('role_assignments',array('contextid'=>$systemcontext->id,'userid'=>$USER->id))&& date('Y-m-d',$programrecord->availableto) > date('Y-m-d'))) || 
                                (!isloggedin() && date('Y-m-d',$programrecord->availableto) > date('Y-m-d')) ? true : false,

        ];
        if($api) {
            return $viewdata;

        } else {
           echo $OUTPUT->render_from_template('local_trainingprogram/detailed_program_course_overview', $viewdata);
        }
    }

    public static function get_offerings($programid, $dedicated=false,$learningpath=false) {
        global $DB, $USER;

        $systemcontext = context_system::instance();
        $activeofferingcondition = (new trainingprogram())->from_unixtime_for_live_entities('(tpo.enddate+tpo.endtime+tpo.duration)');
        $offeringsql = "SELECT tpo.id,tpo.trainertype,tpo.trainerorg,tpo.startdate,tpo.enddate,tpo.type,tpo.time,tpo.duration,tpo.trainingmethod,tpo.trainingid,tpo.organization,
                                tpo.availableseats, tpo.sellingprice,h.name AS hallname,
                                h.maplocation, h.seatingcapacity, h.city,tpo.time,tpo.duration,tpo.published,tpo.cancelled,tpo.languages,tpo.financially_closed_status,tpo.externallink
                          FROM  {tp_offerings} AS tpo 
                     LEFT JOIN {hall} AS h ON tpo.halladdress=h.id WHERE tpo.trainingid = '$programid' AND 
                     (CASE
                        WHEN tpo.trainingmethod !='elearning' THEN $activeofferingcondition
                        ELSE (tpo.startdate = 0 OR tpo.startdate IS NULL OR tpo.enddate = 0 OR tpo.enddate = null )
                    END) ";
        if(!isloggedin()){
            $offeringsql .=" AND tpo.type <> 1 ";
        }
        if(!is_siteadmin() && has_capability('local/organization:manage_organizationofficial', $systemcontext)){
            $offeringsql .=" AND tpo.published= 1 ";
        } 
        $offeringsql .=" ORDER BY (tpo.startdate + tpo.time) ASC ";
        $tpofferings = $DB->get_records_sql($offeringsql);
        $i=1;

        $tpofferinglowestseats=0;

        foreach ($tpofferings AS $key => $tpoffering) {

            // $offeringstartdate = date('Y-m-d',$tpoffering->startdate);
            // $currdate = date('Y-m-d');

            // $offeringstarttime = gmdate("H:i",$tpoffering->time);
            // $currenttime = date('H:i');

            // $offeringstartdate = ($tpoffering->startdate+$tpoffering->time);
            // $timestamp = strtotime(date('Y-m-d H:i'));
            // $totaltimeStamp = strtotime('+ 2 days', $timestamp);
            $offeriing_available_seats = (new trainingprogram())->get_available_seats($tpoffering->id);

            if($tpofferinglowestseats == 0 || ($tpofferinglowestseats > $offeriing_available_seats)){

                $tpofferinglowestseats=$offeriing_available_seats;
                
            }
            $currentuserorganization = $DB->get_field_sql('SELECT organization FROM {local_users} WHERE userid = '.$USER->id.'');
           
            if((!isloggedin() && ($tpoffering->type == 1 || $offeriing_available_seats <= 0  || $tpoffering->published == 0 || $tpoffering->cancelled == 2 ||  (int)$tpoffering->financially_closed_status == 1))  || 
             (isloggedin() && (((!is_siteadmin() && has_capability('local/organization:manage_trainee', $systemcontext) && (((int)$tpoffering->financially_closed_status == 1) || $offeriing_available_seats <= 0 || ($tpoffering->type == 1 && $tpoffering->organization != $currentuserorganization) || $tpoffering->published == 0 || $tpoffering->cancelled == 2 )))))){
             unset($tpofferings[$key]);
             continue;
            }
            $coid=$i++;
            $tpoffering = self::get_offering($tpoffering, $coid);
        }
        if($learningpath){

            $tpofferings= array_values($tpofferings);

            return compact('tpofferings', 'tpofferinglowestseats');

        }else{
            return array_values($tpofferings);
        }
    }

    public static function get_offering($tpoffering, $coid=false, $offeringaction=true) {
        global $DB, $CFG,$USER, $PAGE;


        $currentlang = current_language();
        $systemcontext = context_system::instance();

        $programrecord=$DB->get_record('local_trainingprogram',array('id'=>$tpoffering->trainingid));

        $programavailable = $DB->get_field('local_trainingprogram', 'availableto', ['id' => $tpoffering->trainingid]);
        $traineeeid = $DB->get_field('role', 'id', array('shortname' => 'trainee'));
        $tpoffering->coid = (new trainingprogram)->ordinal_number($coid);
        $tpoffering->url = $CFG->wwwroot;
        $tptandc = (new trainingprogram)->checktermsandconditions($tpoffering->trainingid);

        $tpoffering->product_variations = (new product)->get_product_variations($tpoffering->trainingid, $tpoffering->id, 1,$tptandc);
        $tpoffering->offeringstartdate =  userdate($tpoffering->startdate, get_string('strftimedatemonthabbr', 'langconfig'));

        $offeringendtimeforstatus = gmdate("H:i",round($tpoffering->time + $tpoffering->duration));
        $currenttime = date('H:i');

        if($tpoffering->trainingmethod == 'elearning') {

            $tpoffering->offeringstatus = get_string('open','local_exams') ; 
            $tpoffering->offeringstatuslable = 'open';
            $tpoffering->datedisplay = false;

        } else {

            $tpoffering->offeringstatus = ((int)$tpoffering->financially_closed_status == 1) ? get_string('fincnaciallyclosed','local_trainingprogram') : ((date('Y-m-d',$tpoffering->enddate) > date('Y-m-d')  || (date('Y-m-d',$tpoffering->enddate) ==  date('Y-m-d') && $offeringendtimeforstatus >= $currenttime )) ? get_string('open','local_exams') :get_string('complete','local_exams')); 
           $tpoffering->offeringstatuslable =((int)$tpoffering->financially_closed_status == 1) ? 'complete' : ((date('Y-m-d',$tpoffering->enddate) > date('Y-m-d') || (date('Y-m-d',$tpoffering->enddate) ==  date('Y-m-d') && $offeringendtimeforstatus >= $currenttime )) ? 'open' : 'complete');
           $tpoffering->datedisplay = true;
        }
                

        $tpoffering->cityview = ($tpoffering->trainingmethod == 'offline') ? true : false;
        $tpoffering->cityname =$tpoffering->city? (new \local_hall\hall())->listofcities($tpoffering->city):null;  
        // $tpoffering->hall = $tpoffering->hallname?$tpoffering->hallname:'-';

        $trainingmethod=  $tpoffering->trainingmethod;
        
        if($trainingmethod == 'online') {

            $tpoffering->trainingmethod= get_string('scheduleonline','local_trainingprogram'); 
           

        } elseif($trainingmethod == 'offline') {

            $tpoffering->trainingmethod = get_string('scheduleoffline','local_trainingprogram');
                
        } else {

            $tpoffering->trainingmethod = get_string('scheduleelearning','local_trainingprogram');  
         
        }

        $offeriing_available_seats = (new trainingprogram())->get_available_seats($tpoffering->id);
        
        $enrolledsql = " SELECT count(userid) AS total
                              FROM {program_enrollments} AS pe
                             WHERE pe.offeringid = $tpoffering->id AND pe.roleid=$traineeeid";


        $enrolled =  (new trainingprogram())->get_erolled_seats($tpoffering->id);
           
        $purchasedseats = (new product)->approvedseats_check('tp_offerings','id', $tpoffering->id);
        $tpoffering->availableseats = (new trainingprogram())->get_available_seats($tpoffering->id);
        $totalseatssql = " SELECT  tp.availableseats AS total
                                     FROM {tp_offerings} tp
                                    WHERE tp.id = $tpoffering->id";
           
        $total = $DB->get_field_sql($totalseatssql);


        $tpoffering->orderseats = $purchasedseats ? $purchasedseats : 0;

        $tpoffering->enrolledseats = ($enrolled > 0) ? $enrolled : 0;

        $tpoffering->totalseats = $total;


        $offeringstartdate = date('Y-m-d',$tpoffering->startdate);
        $currdate = date('Y-m-d');

        $offeringstarttime = gmdate("H:i",$tpoffering->time);
        $currenttime = date('H:i');

        $offeringtype = (int)$DB->get_field('tp_offerings','type',array('id'=>$tpoffering->id));
        $offeringorganization = (int)$DB->get_field('tp_offerings','organization',array('id'=>$tpoffering->id));

         $tpoffering->offeringorganizationname = $DB->get_field_sql("SELECT org.fullname FROM {local_organization} AS org JOIN {tp_offerings} AS tpo ON tpo.organization = org.id WHERE tpo.id = $tpoffering->id");

        $tpoffering->offeringtype = $offeringtype;
        $tpoffering->seatsview = (isloggedin() && (is_siteadmin() || has_capability('local/organization:manage_communication_officer', $systemcontext) || has_capability('local/organization:manage_organizationofficial', $systemcontext))) ?  true : false;
        $tpoffering->offeringorganization = $offeringorganization;

        $tpoffering->privateofferingmessage = ((is_siteadmin() || has_capability('local/organization:manage_communication_officer',$systemcontext))  && $offeringtype == 1 && $offeringorganization > 0) ? true : false ;
        $tpoffering->nonprivateofferingmessage = ((is_siteadmin() || has_capability('local/organization:manage_communication_officer',$systemcontext)) && $offeringtype != 1 ) ? true : false ;

        $tpoffering->enrolledseatsbyme = (new trainingprogram())->get_erolled_seats($tpoffering->id,true);
        $tpoffering->myenrolled = (isloggedin() && (is_siteadmin() || has_capability('local/organization:manage_communication_officer', $systemcontext))) ?  true : false;
        $is_enrolled = (new trainingprogram)->is_enrolled($tpoffering->trainingid,$USER->id);

        $tpoffering->offeringview = ((!isloggedin() && $offeriing_available_seats > 0 && ( $trainingmethod == 'elearning' || (($trainingmethod != 'elearning' && $offeringstartdate > $currdate )|| ( $trainingmethod != 'elearning' && $offeringstartdate  ==  $currdate &&  $tpoffering->time > 0 &&  $offeringstarttime >= $currenttime))) ) || (isloggedin() && !is_siteadmin() && (has_capability('local/organization:manage_trainee', $systemcontext)) && !$is_enrolled && $offeriing_available_seats > 0 && ( $trainingmethod == 'elearning' || (($trainingmethod != 'elearning' && $offeringstartdate > $currdate) || ($trainingmethod != 'elearning' && $offeringstartdate  ==  $currdate &&  $tpoffering->time > 0 &&  $offeringstarttime >= $currenttime)))) || (!is_siteadmin() && !$DB->record_exists('role_assignments',array('contextid'=>$systemcontext->id,'userid'=>$USER->id)) && $offeriing_available_seats > 0 && ($trainingmethod == 'elearning' || (($trainingmethod != 'elearning' && $offeringstartdate > $currdate) || ($trainingmethod != 'elearning' && $offeringstartdate  ==  $currdate &&  $tpoffering->time > 0 &&  $offeringstarttime >= $currenttime))))) ? true : false;

        $tpoffering->orgofficialseatsview = (isloggedin() && !is_siteadmin() && has_capability('local/organization:manage_organizationofficial', $systemcontext)) ? true :  false;

        $tpoffering->purchasedseatsview = (isloggedin() && (is_siteadmin() || has_capability('local/organization:manage_organizationofficial', $systemcontext))) ? true :  false;

         $tpoffering->purchasedseatstext = (is_siteadmin()) ? get_string('official_purchased_seats','local_trainingprogram') :  get_string('orderseats','local_trainingprogram');

        $starttimemeridian = gmdate('a',$tpoffering->time); 
        $endtimemeridian = gmdate('a',($tpoffering->time + $tpoffering->duration)); 
        $tpoffering->sellingprice = ($programrecord->price > 0)?round($tpoffering->sellingprice,2): 0;
        //Added changes for TRK164_12399
        // if($currentlang == 'ar') {
        //     $starttime = gmdate("i:h",$tpoffering->time);
        //     $endttime = gmdate("i:h",($tpoffering->time + $tpoffering->duration));
        // } else {
            $starttime = gmdate("h:i",$tpoffering->time);
            $endttime = gmdate("h:i",($tpoffering->time + $tpoffering->duration));
        // }

        $startmeridian = ($starttimemeridian == 'am')?  get_string('am','local_trainingprogram'):get_string('pm','local_trainingprogram');
        $endmeridian = ($endtimemeridian == 'am')?  get_string('am','local_trainingprogram'):get_string('pm','local_trainingprogram');

        $tpoffering->starttime =$starttime.' '.$startmeridian;
        $tpoffering->endtime =$endttime .' '.$endmeridian;
        $tpoffering->offeringenddate = userdate($tpoffering->enddate, get_string('strftimedatemonthabbr', 'langconfig'));
        $tpoffering->locationview = ($tpoffering->trainingmethod == 1) ? true : false; 
        $tpoffering->istrinee= (!is_siteadmin() && has_capability('local/organization:manage_trainee', $systemcontext)) ? true : false; 
        $tpoffering->offeringtypename = ($offeringtype == 0 ? get_string('public','local_trainingprogram') : (($offeringtype == 1 ? get_string('private','local_trainingprogram') : get_string('dedicated','local_trainingprogram'))));
        $learningtrack = $DB->get_record('local_learning_items',['itemid'=>$tpoffering->trainingid,'itemtype'=> 1]);
        $tpoffering->haslearningtrack = ($learningtrack->trackid > 0) ? true : false;
        $trackrecord =$DB->get_record('local_learningtracks',['id'=>$learningtrack->trackid]);
        $tpoffering->learningtrackname = ($currentlang == 'ar') ? $trackrecord->namearabic  : $trackrecord->name;
        $tpoffering->offcourselanguage = ($tpoffering->languages == '1') ? get_string('english','local_trainingprogram') : get_string('arabic','local_trainingprogram');
        $tpoffering->externallink = $tpoffering->externallink;
        $tpoffering->is_external_link = ($tpoffering->externallink)?true:false;
        //renu
        if(!empty($tpoffering->trainerorg)){
            $coffering=$DB->get_field_sql("SELECT org.fullname FROM {local_organization} AS org JOIN {tp_offerings} AS tpo ON tpo.trainerorg = org.id WHERE tpo.id = $tpoffering->id AND org.id=$tpoffering->trainerorg");

            if($tpoffering->trainertype ==1){
                $tpoffering->trainingprovider= $coffering;;  
            }
        }      

        return $tpoffering;
    }


    public function others_program_course_overview($programid, $action='booknow') {
        global $DB, $PAGE, $OUTPUT;
        $renderer = $PAGE->get_renderer('local_trainingprogram');
        $filterparams  = $renderer->other_programs_course_view(true);
        $filterparams['submitid'] = 'form#filteringform';
        $filterparams['widthclass'] = 'col-md-6';
        $filterparams['placeholder'] = 'Search Hall..';
        $globalinput=$renderer->global_filter($filterparams);
        $training_programs = $renderer->other_programs_course_view();
        $filterparams['training_programs'] = $training_programs;
        $filterparams['globalinput'] = $globalinput;
        $renderer->listofothers_program_course_view($filterparams);
    }

    public function get_others_program_course_overview($stable, $filterdata,$dataoptions) {
        global $DB, $PAGE, $OUTPUT, $CFG, $USER;
        $systemcontext = context_system::instance();
        $PAGE->set_context($systemcontext);
        $traineeeid = $DB->get_field('role', 'id', array('shortname' => 'trainee'));
        $programid = json_decode($dataoptions)->programid;
        $action = json_decode($dataoptions)->action;
        $lang= current_language();

        if( $lang == 'ar'){
            $programnamesql = "SELECT namearabic FROM {local_trainingprogram} WHERE id = $programid";

        }else{
            $programnamesql = "SELECT name FROM {local_trainingprogram} WHERE id = $programid";
        }

        $programname=$DB->get_field_sql($programnamesql);
        $selectsql = "SELECT tpo.id,tpo.startdate,tpo.enddate,tpo.type,h.city,tpo.availableseats,
                       tpo.organization,tpo.sellingprice,h.name AS hallname,h.maplocation,
                        h.seatingcapacity,h.buildingname,tpo.trainingmethod,tpo.code,tpo.time,
                        tpo.endtime,tpo.published,tpo.cancelled,tpo.languages,tpo.financially_closed_status 
                       FROM  {tp_offerings} AS tpo 
                       LEFT JOIN {hall} AS h ON tpo.halladdress=h.id  
                    ";

        $countsql  = "SELECT COUNT(tpo.id) 
                        FROM  {tp_offerings} AS tpo 
                   LEFT JOIN {hall} AS h ON tpo.halladdress=h.id  
                       ";

        $formsql = " WHERE tpo.trainingid = $programid AND tpo.published = 1 ";
        if  (isset($filterdata->search_query) && trim($filterdata->search_query) != ''){
            $formsql .= " AND (h.name LIKE :firstnamesearch) ";
            $searchparams = array('firstnamesearch' => '%'.trim($filterdata->search_query).'%');
        } else {
            $searchparams = array();
        }
        $params = array_merge($searchparams);
        $totalofferings = $DB->count_records_sql($countsql.$formsql,$params);;
        $formsql .=" ORDER BY tpo.startdate DESC";
        $offerings = $DB->get_records_sql($selectsql.$formsql, $params, $stable->start,$stable->length);
        $offeringslist = array();
        $count = 0;
        $i = 1;
        foreach($offerings as $offering) {

            if($offering->type == dataprovider::PRIVATEPROGRAM){
                if(is_siteadmin() || has_capability('local/organization:manage_trainingofficial',$systemcontext) 
                        || has_capability('local/organization:manage_organizationofficial',$systemcontext)){
                    if(!is_siteadmin() && has_capability('local/organization:manage_organizationofficial',$systemcontext)){


                        $organization = $DB->get_field_sql("SELECT lu.organization
                                                            FROM {local_users} as lu 
                                                            JOIN {user} as u ON u.id= lu.userid
                                                            JOIN {role_assignments} as  ra on ra.userid=u.id
                                                            JOIN {role} as r ON ra.roleid = r.id  AND r.shortname = 'organizationofficial'
                                                                AND u.id=$USER->id");

                        if(!$organization){
                            continue;
                        }else{
                    
                            if($offering->organization != $organization){
                                continue;
                            }
                        }
                    }

                }else{
                    continue;
                }
            }
            if(!is_siteadmin() && has_capability('local/organization:manage_organizationofficial',$systemcontext)){
                if($action == 'enrol'){
                    if(!$DB->record_exists('tool_org_order_seats',  array('tablename'=>'tp_offerings',
                                                                        'fieldname'=>'id',
                                                                        'fieldid'=>$offering->id,
                                                                        'orguserid'=>$USER->id))){

                        continue;
                    }
                }
            }
            $program=$DB->get_record('local_trainingprogram',array('id'=>$programid),'name,price,languages');
            $trainingmethod=  $offering->trainingmethod;
            $totalseatssql = " SELECT  tp.availableseats AS total
                FROM {tp_offerings} tp
                WHERE tp.id = $offering->id";
            $total = $DB->get_record_sql($totalseatssql);
            $enrolledseatsselectsql = "SELECT COUNT(pe.id) as enrolled
                FROM {tp_offerings} tp
                LEFT JOIN {program_enrollments} as pe ON pe.offeringid = tp.id
                WHERE tp.id = $offering->id AND pe.roleid = $traineeeid";
            $enrolled = $DB->get_record_sql($enrolledseatsselectsql);
            $availableseats = $this->get_after_approved_available_seats($offering->id); 
            $totalseats =  $total->total;
            if( $lang == 'ar'){
                $programname = $program->namearabic;

            }else{
                $programname = $program->name;
            }

            $starttime = gmdate("h:i",$offering->time);
            $starttimemeridian = gmdate('a',$offering->time);
            $startmeridian = ($starttimemeridian == 'am')?  get_string('am','local_trainingprogram'):get_string('pm','local_trainingprogram');
            $endtime = gmdate("h:i",$offering->endtime);
            $endtimemeridian = gmdate('a',$offering->endtime);
            $endmeridian = ($endtimemeridian == 'am')?  get_string('am','local_trainingprogram'):get_string('pm','local_trainingprogram');
            $offeringvailableseats = $DB->get_field_sql("SELECT SUM(purchasedseats) FROM {tool_org_order_seats} WHERE tablename = 'tp_offerings' AND fieldname = 'id' AND fieldid = $offering->id");
            $offeringslist[$count]['coid'] = $i++;
            $offeringslist[$count]['code'] = $offering->code;
            $trainingmethod=  $offering->trainingmethod;
        
            if($trainingmethod == 'online') {

                $offeringslist[$count]['offeringmethod'] = get_string('scheduleonline','local_trainingprogram'); 

            } elseif($trainingmethod == 'offline') {

            $offeringslist[$count]['offeringmethod'] = get_string('scheduleoffline','local_trainingprogram');
                    
            } else {

            $offeringslist[$count]['offeringmethod'] = get_string('scheduleelearning','local_trainingprogram');  
            
            }

            $programcost = $DB->get_field('local_trainingprogram','price',['id'=>$programid]);
            $offeringslist[$count]['datedisplay']=($trainingmethod != 'elearning') ? true : false;
            $offeringslist[$count]['offeringid'] = $offering->id;
            $offeringslist[$count]['programid'] = $programid;
            $offeringslist[$count]['offeringid'] = $offering->id;
            $offeringslist[$count]['seatingcapacity'] = $offering->seatingcapacity;
            $offeringslist[$count]['totalseats'] = $totalseats;
            $offeringslist[$count]['availableseats'] =  $availableseats ;
            $offeringslist[$count]['buildingname'] = $offering->buildingname;
            $offeringslist[$count]['hallname'] = $offering->hallname;
            $offeringslist[$count]['city'] = $offering->city?(new \local_hall\hall())->listofcities($offering->city):null;
            $offeringslist[$count]['programname'] = $programname;
            $offeringslist[$count]['sellingprice'] =($programcost > 0) ?  round($offering->sellingprice,2) : 0;
            $offeringslist[$count]['seatingcapacity'] = $offering->seatingcapacity;
            $offeringslist[$count]['startdate'] = userdate($offering->startdate, get_string('strftimedatemonthabbr', 'langconfig'));
            $offeringslist[$count]['enddate'] = userdate($offering->enddate, get_string('strftimedatemonthabbr', 'langconfig'));
            $offeringslist[$count]['starttime'] = $offering->time ? ((current_language() == 'ar') ? gmdate("h:i",$offering->time) : gmdate("h:i",$offering->time)).' '.$startmeridian: '';
            $offeringslist[$count]['endtime'] = $offering->endtime ? ((current_language() == 'ar') ? gmdate("h:i",$offering->endtime) :gmdate("h:i",$offering->endtime)).' '.$endmeridian: '';
            $offeringslist[$count]['halldisplayaction'] = $offering->trainingmethod == 'offline' ? true :false; 
            $offeringslist[$count]['action'] = ($action == 'enrol') ? true :false; 
            $offeringslist[$count]['orderseats'] = (new product)->purchasedseats_check('tp_offerings','id', $offering->id);
            $offeringslist[$count]['approvalseats'] = (new product)->approvedseats_check('tp_offerings','id', $offering->id);
            $offeringslist[$count]['enavailableseats'] = $this->get_available_seats($offering->id);
            $offeringslist[$count]['enrolledseats'] = $this->get_erolled_seats($offering->id);
            $offeringslist[$count]['courselanguage'] =((int)$offering->languages == 1) ? get_string('english','local_trainingprogram') : get_string('arabic','local_trainingprogram'); 

            $offeringstartdate = ($offering->startdate + $offering->time);
            $offeringenddate = ($offering->enddate + $offering->endtime);

            $timestamp = strtotime(date('Y-m-d H:i'));
            $totaltimestamp = strtotime('+ 2 days', $timestamp);


            $offeringstarttime = gmdate("H:i",$offering->time);
            $currenttime = date('H:i');

            $currdate = time();

            if(!is_siteadmin() && has_capability('local/organization:manage_organizationofficial', $systemcontext)) {

                if(($offering->trainingmethod =='elearning' || ($offering->trainingmethod !='elearning' && (int)$offering->financially_closed_status == 0 && ($offeringstartdate > $currdate || ($offeringstartdate  ==  $currdate &&  $offering->time > 0 &&  $offeringstarttime >= $currenttime ))) && $availableseats > 0 && $offering->cancelled !=2 && $offering->published == 1)) {


                    if($program->price == '1'){
                        $trainingurl = new moodle_url('/local/trainingprogram/programenrollment.php',array('programid'=>$programid,'roleid'=>30,'roleid'=>$traineeeid,'offeringid'=>$offering->id));
                        $offeringslist[$count]['bookseats'] = html_writer::link($trainingurl,get_string('enrollbtn',  'tool_product'));//(new \tool_product\product)->get_button_order_seats($label=get_string('booknow','local_trainingprogram'),'tp_offerings','id',$offering->id, $availableseats,$programid, $action);

                    } else {

                        $trainingurl = new moodle_url('/local/trainingprogram/programenrollment.php',array('programid'=>$programid,'roleid'=>$traineeeid,'offeringid'=>$offering->id));
                        $offeringslist[$count]['bookseats'] =html_writer::link($trainingurl,get_string('enrollbtn',  'tool_product'));

                        
                    }

                } else {

                    if($offering->trainingmethod !='elearning' && (int)$offering->financially_closed_status == 1) {

                       $offeringslist[$count]['bookseats'] = get_string('financially_closed','local_trainingprogram');

                    }
                    if($offering->trainingmethod !='elearning' && $offeringstartdate  < $currdate && $offeringenddate > $currdate) {

                        $offeringslist[$count]['bookseats'] = get_string('offering_in_progress','local_trainingprogram');
                    }
                    if($offering->trainingmethod !='elearning' &&  $offeringenddate < $currdate ){

                        $offeringslist[$count]['bookseats'] = get_string('offering_expired','local_trainingprogram');

                    }

                    if($offering->cancelled == 2) {

                        $offeringslist[$count]['bookseats'] = get_string('cancelled','local_trainingprogram');
                    }

                    if($offering->published == 0) {

                        $offeringslist[$count]['bookseats'] = get_string('unpublished','local_trainingprogram');
                    }
                    if($availableseats <= 0){


                        $offeringslist[$count]['bookseats'] = get_string('noseats','local_trainingprogram');
                    }

                } 
            } 
            

            $count++;
        }
        
        $coursesContext = array(
            "hascourses" => $offeringslist,
            "totalofferings" => $totalofferings,
            "length" => count($offeringslist),
            "programname" => $programname,
        );
        return $coursesContext;
   }

    public function trainee_program_course_overview($programid,$courseid, $type=false, $mlang = NULL) {
        global $DB,$USER,$OUTPUT,$CFG,$SESSION;

       
        $SESSION->lang = ($mlang) ? $mlang : current_language() ;
    
        $programrecord=$DB->get_record('local_trainingprogram',array('id'=>$programid));

       
        if (!empty($programrecord->image)) {
            $image=trainingprogramlogo_url($programrecord->image);
        }

        $currentlang= current_language();
        list($competencysql,$competencyparams) = $DB->get_in_or_equal(explode(',',$programrecord->competencyandlevels));
        if($currentlang == 'ar' ||  $SESSION->lang == 'ar') {

            $cname = 'arabicname';

        } else {

            $cname = 'name';
        }
        $compquerysql = "SELECT id AS competencyid,$cname AS competencyname FROM {local_competencies} WHERE id $competencysql";
        $competencieslists= $DB->get_records_sql($compquerysql,$competencyparams);
        foreach ($competencieslists AS $competencieslist) {
          $competencieslist->level=($programrecord->clevels) ? ((substr($programrecord->clevels,0,5) == 'level') ?  str_replace("level", "", $programrecord->clevels) :((substr($programrecord->clevels,0,6) == 'Level ')?str_replace("Level ", "", $programrecord->clevels) : str_replace("Level", "", $programrecord->clevels))) : 0 ;
          $competencieslist->competencylevel=get_string('level','local_trainingprogram').' '.$competencieslist->level;
        }
        $sql = 'SELECT tpo.id,tpo.startdate,tpo.enddate,tpo.availableseats, tpo.sellingprice,tpo.prequiz, tpo.postquiz,tpo.trainingmethod,
                       h.name AS hallname, h.maplocation, h.seatingcapacity, h.city,tpo.languages 
                  FROM {tp_offerings} AS tpo 
                  JOIN {program_enrollments} as pe ON pe.offeringid=tpo.id AND pe.userid =:userid
             LEFT JOIN {hall} AS h ON tpo.halladdress=h.id 
                 WHERE tpo.trainingid =:programid';
        $tpofferings = $DB->get_records_sql($sql, ['programid' => $programid, 'userid' => $USER->id]);
        $i=1;
        foreach ($tpofferings AS $tpoffering) {
            $tpoffering->coid=$i++;
            $tpoffering->url = $CFG->wwwroot;
            $tpoffering->offeringtype = ($tpoffering->trainingmethod == 'online') ? get_string('scheduleonline','local_trainingprogram'):(($tpoffering->trainingmethod == 'offline') ? get_string('scheduleoffline','local_trainingprogram') : get_string('scheduleelearning','local_trainingprogram')) ;
            
            if (filter_var($tpoffering->maplocation, FILTER_VALIDATE_URL)) {
                $tpoffering->locationstatus = true;
            } else {
                $tpoffering->locationstatus = false;
            }

            if ($type=='web') {
                $tpoffering->startdate =  userdate($tpoffering->startdate, get_string('strftimedatemonthabbr', 'langconfig'));
                $tpoffering->enddate = userdate($tpoffering->enddate, get_string('strftimedatemonthabbr', 'langconfig'));
            } else {
                $ofstartdate = $tpoffering->startdate;
                $ofsenddate = $tpoffering->enddate;
                $tpoffering->startdate = $ofstartdate; 
                $tpoffering->enddate = $ofsenddate;
            }
           
            $actuallang=((int)$tpoffering->languages == '1') ? get_string('english','local_trainingprogram') : get_string('arabic','local_trainingprogram');
            $tpoffering->courselanguage = ($actuallang) ? $actuallang :'' ;

            $tpoffering->cityview = ($tpoffering->trainingmethod == 'offline') ? true : false;
            $tpoffering->cityname =$tpoffering->city? (new \local_hall\hall())->listofcities($tpoffering->city):null; 
            // $tpoffering->hall = $tpoffering->hallname?$tpoffering->hallname:'-';

            $tpoffering->offeringstatus = (!$DB->record_exists('program_completions',array('programid' => $programid,'offeringid'=>$tpoffering->id,'completion_status' => 1 ))) ? get_string('in_progress','local_trainingprogram') : get_string('completed','local_trainingprogram');  
           
            $trainerroleid = $DB->get_field('role', 'id', array('shortname' => 'trainer'));


            if($currentlang == 'ar' || $SESSION->lang == 'ar') {

                $displaying_name = "concat(lc.firstnamearabic,' ',lc.middlenamearabic,' ',lc.thirdnamearabic,' ',lc.lastnamearabic)";

            } else {

                $displaying_name = "concat(lc.firstname,' ',lc.middlenameen,' ',lc.thirdnameen,' ',lc.lastname)";
            }

            $trainer_sql_query = "SELECT $displaying_name as fullname 
                                        FROM {user} AS u 
                                        JOIN {local_users} lc ON lc.userid = u.id 
                                        WHERE u.id IN (SELECT ue.userid 
                                        FROM {program_enrollments} AS ue 
                                        WHERE ue.roleid=$trainerroleid AND programid = $programid AND offeringid = $tpoffering->id)";
        
            $triners=$DB->get_fieldset_sql($trainer_sql_query);
	        $trainerslist = [];
            foreach($triners as $triner) {
                $row = [];
                $row['name'] = $triner;
                $trainerslist[] = $row;
            }
            $tpoffering->trainerslist = $trainerslist;
            $tpoffering->trainers = $triners ? implode(', ', $triners):get_string('no_trainer','local_trainingprogram');
            $tpoffering->todoactivities = [];
            if($DB->record_exists('quiz',array('id'=> $tpoffering->prequiz))){
                $prequiz = get_coursemodule_from_instance('quiz',  $tpoffering->prequiz,  0,  false,  MUST_EXIST);
                if(!$DB->record_exists('course_modules_completion', ['coursemoduleid' => $prequiz->id, 'completionstate' => 1, 'userid' => $USER->id])){
                    $tpoffering->todoactivities[] = ['name' => get_string('pre_exam','local_trainingprogram'), 'link' => ''.$CFG->wwwroot.'/mod/quiz/view.php?id='.$prequiz->id.''];                    
                }
            }
            if($DB->record_exists('quiz',array('id'=> $tpoffering->postquiz))){
                $postquiz = get_coursemodule_from_instance('quiz',  $tpoffering->postquiz,  0,  false,  MUST_EXIST);
                if(!$DB->record_exists('course_modules_completion', ['coursemoduleid' => $postquiz->id, 'completionstate' => 1, 'userid' => $USER->id])){
                    $tpoffering->todoactivities[] = ['name' => get_string('post_exam','local_trainingprogram'), 'link' => ''.$CFG->wwwroot.'/mod/quiz/view.php?id='.$postquiz->id.''];                    
                }
            }
         
            $k=1;
            $topssql = "SELECT ofs.sessionid,ats.attendanceid,ats.id as session_id,ats.sessdate,ats.duration,ofs.courseid,ofs.programid 
                          FROM {offering_sessions} AS ofs 
                         LEFT JOIN {attendance_sessions} AS ats ON ofs.sessionid = ats.id 
                            WHERE ofs.offeringid =:offeringid";
            $tpofferingssessions = $DB->get_records_sql($topssql, ['offeringid' => $tpoffering->id]);
            $k=1;
            foreach ($tpofferingssessions AS $tpofferingssession) {
                $tpofferingssession->cid=$k++;
                $starttimemeridian = date('a',$tpofferingssession->sessdate); 
                $endtimemeridian = date('a',($tpofferingssession->sessdate + $tpofferingssession->duration)); 
                $starttime = date('H:i',$tpofferingssession->sessdate);
                $endtime = date("H:i",($tpofferingssession->sessdate + $tpofferingssession->duration));

                if($currentlang == 'ar'||  $SESSION->lang == 'ar') {
                        $startmeridian = ($starttimemeridian == 'am')? 'صباحا':'مساءً';
                        $endmeridian =  ($endtimemeridian == 'am')? 'صباحا':'مساءً';
                } else {
                        $startmeridian = ($starttimemeridian == 'am')? 'AM':'PM';
                        $endmeridian =  ($endtimemeridian == 'am')? 'AM':'PM';
                }
                
                $session_status = $DB->get_field_sql('SELECT sstatus.acronym FROM  {attendance_statuses} as sstatus
                                                   JOIN {attendance_log} as alog ON alog.statusid = sstatus.id 
                                                   WHERE alog.sessionid = '.$tpofferingssession->session_id.' AND 
                                                   alog.studentid = '.$USER->id.' AND sstatus.attendanceid = '.$tpofferingssession->attendanceid.'');

                $off_starttime =$starttime .' '.$startmeridian;
                $off_endtime =$endtime .' '.$endmeridian;
                $tpofferingssession->sessiondate = ($type=='web') ? userdate($tpofferingssession->sessdate, get_string('strftimedatemonthabbr', 'langconfig')).' '.$starttime .' - '.$endtime :$tpofferingssession->sessdate;
                $tpofferingssession->sessiontime =$starttime .' - '.$endtime;

                if($session_status == 'P') {

                    $tpofferingssession->status = get_string('present', 'local_trainingprogram');

                } elseif($session_status == 'L') {
                    $tpofferingssession->status = get_string('late', 'local_trainingprogram');

                } elseif($session_status == 'E') {
                    $tpofferingssession->status = get_string('excused', 'local_trainingprogram');

                } elseif($session_status == 'A') {
                    $tpofferingssession->status = get_string('absent', 'local_trainingprogram');

                } else {
                    $tpofferingssession->status = '-';

                }

            } 
            if(!empty($tpofferingssessions)){
                $tpoffering->sessiondata = array_values($tpofferingssessions);
            } else {
                $tpoffering->sessiondata = array();
            }

         
            $tpoffering->datedisplay = ($tpoffering->trainingmethod != 'elearning') ? true :false;
           
            
        }

        $launchurl=$CFG->wwwroot.'/course/view.php?id='.$programrecord->courseid; 
        $lang= current_language();

        if( $lang == 'ar'||  $SESSION->lang == 'ar'){
            $programname = $programrecord->namearabic;
        } else{
            $programname = $programrecord->name;
        }
        $viewdata=[
            'courseid'=>$courseid,
            'programid'=>$programid,
            'programname'=>$programname,
            'offerings'=>array_values($tpofferings),
            'todoactivitiesdisplay'=> (COUNT($tpoffering->todoactivities) > 0) ? true : false ,
            'sessiondatadisplay'=> (COUNT($tpofferingssessions) > 0) ? true : false ,
            'competencieslists'=>array_values($competencieslists),
            'launchurl'=>$launchurl,
            'image'=>$image,
        ];

        if ($type=='web') {
            echo $OUTPUT->render_from_template('local_trainingprogram/trainee_program_course_overview', $viewdata);
        } else {
            return $viewdata;
        }

    }

    public function remove_training_program($programid) {
        global $DB;
        $systemcontext = context_system::instance();
        $courseid =(int) $DB->get_field('local_trainingprogram','courseid',array('id'=>$programid));
        (new trainingprogram)->remove_all_program_offerings($programid,$courseid);
        if($courseid) {
          delete_course($courseid,false);  
        }
        $data = $DB->delete_records('local_trainingprogram', array('id' => $programid));
        $event = \local_trainingprogram\event\trainingprogram_deleted::create(array( 'context'=>$systemcontext, 'objectid' => $programid));
        $event->trigger();
        return $data;
    }
    public static function trainingprogram_jobfamily($jobroleid=0,$jobfamilies= array(),$programid=0, $type=false) {

        global $DB, $USER;

        $currentlang= current_language();

        if($jobroleid){

            if( $currentlang == 'ar'){

                $sql = "SELECT jbfm.id,jbfm.familynamearabic as title 
                                                    FROM {local_jobfamily} as jbfm 
                                                    JOIN {local_jobrole_level} as jbrl ON jbrl.jobfamily=jbfm.id 
                                                     WHERE jbfm.familynamearabic <> '' AND  jbfm.familynamearabic IS NOT NULL AND jbrl.id=:jobroleid";

            } else {

                $sql = 'SELECT jbfm.id,jbfm.familyname as title 
                                                    FROM {local_jobfamily} as jbfm 
                                                    JOIN {local_jobrole_level} as jbrl ON jbrl.jobfamily=jbfm.id 
                                                     WHERE jbrl.id=:jobroleid';


            }                                          

            $jobfamily= $DB->get_record_sql($sql,['jobroleid' => $jobroleid]);

        }elseif(!empty($jobfamilies)){

            $params = array();

            list($jobfamiliessql, $jobfamiliesparams) = $DB->get_in_or_equal($jobfamilies);
            $params = array_merge($params, $jobfamiliesparams);

            if( $currentlang == 'ar'){

                $jobfamily = $DB->get_records_sql_menu("SELECT jfly.id,jfly.familynamearabic as title FROM {local_jobfamily} jfly  WHERE jfly.familynamearabic <> '' AND  jfly.familynamearabic IS NOT NULL  AND jfly.id $jobfamiliessql",$params);

            } else {

                $jobfamily = $DB->get_records_sql_menu("SELECT jfly.id,jfly.familyname as title FROM {local_jobfamily} jfly  WHERE jfly.id $jobfamiliessql",$params);

            }
            


        }elseif($programid){

            if($type == 'exam') {

                $table = '{local_exams}';

            } else {

                $table = '{local_trainingprogram}';

            }

            if( $currentlang == 'ar'){

                $sql = "SELECT jbfm.id,jbfm.familynamearabic as title 
                                                    FROM {local_jobfamily} as jbfm 
                                                     JOIN $table as cmtc ON concat(',', cmtc.targetgroup, ',') LIKE concat('%,',jbfm.id,',%')
                                                 WHERE  jbfm.familynamearabic <> '' AND  jbfm.familynamearabic IS NOT NULL AND cmtc.id=:programid";

            

            } else {
                
                $sql = "SELECT jbfm.id,jbfm.familyname as title
                          FROM {local_jobfamily} as jbfm 
                          JOIN $table as cmtc ON concat(',', cmtc.targetgroup, ',') LIKE concat('%,',jbfm.id,',%')
                         WHERE cmtc.id=:programid";

            }

            $jobfamily= $DB->get_records_sql_menu($sql,['programid' => $programid]);

        }

        return $jobfamily;
            
    }
    

    public static function trainingprogram_competencytypes($competencietypes= array(),$programid=0) {

        global $DB, $USER;

      if(!empty($competencietypes)){

            $params = array();

            

            list($competenciessql, $competenciesparams) = $DB->get_in_or_equal($competencietypes);
            $params = array_merge($params, $competenciesparams);
            $competencytypes= $DB->get_records_sql_menu("SELECT cmt.type,cmt.type as fullname FROM {local_competencies} AS cmt WHERE cmt.type $competenciessql",$params);

        }elseif(!empty($programid)){

            $competencytypes= $DB->get_records_sql_menu("SELECT loc.type,loc.type as fullname 
                                                        FROM {local_competencies} as loc
                                                        GROUP BY loc.type");


        }
         
        return $competencytypes;
            
    }

     public static function trainingprogram_competencylevels($competencies= array(),$programid=0) {
        global $DB, $USER;

        $lang = current_language();
      if(!empty($competencies)){

       
            $title = ($lang == 'ar') ? 'arabicname' : 'name';
        

            $params = array();

            list($competenciessql, $competenciesparams) = $DB->get_in_or_equal($competencies);
            $params = array_merge($params, $competenciesparams);
            $competency= $DB->get_records_sql_menu("SELECT id,$title  as title FROM {local_competencies} WHERE id $competenciessql",$params);

        }elseif(!empty($programid)){

             $title = ($lang == 'ar') ? 'loc.arabicname' : 'loc.name';

            $competency= $DB->get_records_sql_menu("SELECT loc.id, $title as title 
                                                        FROM {local_competencies} as loc
                                                        JOIN {local_trainingprogram} as lot 
                                                        ON concat(',', lot.competencyandlevels, ',') LIKE concat('%,',loc.id,',%')
                                                        WHERE lot.id=:programid",['programid' => $programid]);


            }
         
        return $competency;
            
    }
    public function programsfakeblock() { 
        global $DB, $PAGE, $OUTPUT, $CFG, $USER;
        $systemcontext = context_system::instance();
        //$trainerroleid = $DB->get_field('role', 'id', array('shortname' => 'trainer'));

        // $sql = "SELECT rc.capability
        //                       FROM {role_capabilities} rc
        //                       JOIN {program_enrollments} pe ON pe.roleid = rc.roleid
        //                       WHERE pe.roleid=$trainerroleid AND
        //                            pe.userid=$USER->id AND
        //                            rc.permission=1";
        // $capabilty = $DB->get_field_sql($sql);
        $bc = new \block_contents();
        if(!is_siteadmin() && (has_capability('local/organization:manage_trainee', $systemcontext) || has_capability('local/organization:manage_trainer', $systemcontext))) {
            $bc->title = '';
            $bc->attributes['class'] = 'my_training';
            $bc->content =(new trainingprogram)->trainee_dashboard_programs_data();
        } elseif(!is_siteadmin() && (has_capability('local/organization:manage_trainingofficial', $systemcontext) || has_capability('local/organization:manage_organizationofficial', $systemcontext))) {
            $bc->title = get_string('trainings','local_trainingprogram');
            $bc->attributes['class'] = 'training';
            $bc->content =(new trainingprogram)->official_dashboard_programs_data();
        } 
        $PAGE->blocks->add_fake_block($bc, 'content');
       
   }   
    public function trainee_dashboard_programs_data() {
        global $DB, $PAGE, $OUTPUT, $CFG, $USER;
        $systemcontext = context_system::instance();
        $selectsql = 'SELECT loc.id as traineeid,loc.name as programname,loc.namearabic as namearabic,tpo.startdate,tpo.enddate,loc.image,loc.courseid,loc.description,loc.availableto,tpo.trainingmethod ';
        $countsql = 'SELECT count(loc.id)';
        $limitsql = ' ORDER BY tpo.startdate DESC LIMIT 5 ';

        $inprogressenddatecondition = (new trainingprogram())->from_unixtime_for_live_entities('loc.availableto');

        $expiredenddatecondition = (new trainingprogram())->from_unixtime_for_expired_entities('loc.availableto');


        if(has_capability('local/organization:manage_trainer', $systemcontext)){
            $trainerid = $DB->get_field('role', 'id', ['shortname' => 'trainer']);

            $sql = ' FROM {local_trainingprogram} loc 
                JOIN {tp_offerings} as tpo ON tpo.trainingid=loc.id
                JOIN {program_enrollments} ue ON ue.programid=loc.id AND ue.offeringid = tpo.id
                WHERE ue.roleid = '.$trainerid.' AND ue.userid ='.$USER->id.' AND ue.programid=loc.id AND   '.$inprogressenddatecondition.' 
                ';

            $completedselect = 'SELECT loc.id as traineeid,loc.name as programname,loc.namearabic as namearabic,loc.image,loc.courseid,loc.description,loc.availableto,tpo.startdate,tpo.enddate,tpo.trainingmethod  ';

            $completedsql =' FROM {local_trainingprogram} loc 
                            JOIN {tp_offerings} as tpo ON tpo.trainingid=loc.id
                            JOIN {program_enrollments} ue ON ue.programid=loc.id AND ue.offeringid =tpo.id  
                            WHERE  ue.userid ='.$USER->id.' AND '.$expiredenddatecondition.' ';
        } else {
            $traineeid = $DB->get_field('role', 'id', ['shortname' => 'trainee']);

            $sql = 'FROM {local_trainingprogram} loc 
                   JOIN {tp_offerings} as tpo ON tpo.trainingid=loc.id
                   JOIN {program_enrollments} ue ON ue.programid=loc.id AND ue.offeringid = tpo.id
                   WHERE ue.roleid = '.$traineeid.' AND ue.userid ='.$USER->id.'  AND  ue.enrolstatus = 1  AND ue.programid=loc.id AND loc.id 
                   NOT IN (SELECT pc.programid FROM {program_completions} pc 
                             WHERE pc.userid ='.$USER->id.'  AND pc.completion_status = 1)';

            $completedselect = 'SELECT loc.id as traineeid,loc.name as programname,loc.namearabic as namearabic,loc.image,loc.courseid,loc.description,loc.availableto,tpo.startdate,tpo.enddate,tpo.trainingmethod,pc.completiondate ';     

             $completedsql =' FROM {local_trainingprogram} loc 
                        JOIN {tp_offerings} as tpo ON tpo.trainingid=loc.id
                        JOIN {program_completions} pc ON pc.programid = loc.id  AND pc.offeringid = tpo.id
                        WHERE  pc.userid ='.$USER->id.' AND pc.completion_status = 1 '; 

            $Jobrole = $DB->get_field('local_users','Jobrole',['userid' => $USER->id]);           
            
            if(!is_null($Jobrole) && $Jobrole != 0) {
                
                $level = $DB->get_field_sql('SELECT  
                    RIGHT(jbl.level,1)
                    FROM {local_jobrole_level} jbl 
                    JOIN {local_users} lu ON lu.jobrole = jbl.id
                    WHERE lu.userid =:userid',['userid'=>$USER->id]);
                $level = $level+1;
                 
                $recommendedprogramssql = "SELECT loc.id, loc.name as name, loc.namearabic as arabicname, loc.code, loc.availablefrom as startdate, loc.availableto as enddate, loc.price, loc.sellingprice "; 

                $recommendedprogramscountsql  = "SELECT COUNT(loc.id) ";
                $recommendedprogramsformsql = "  FROM {local_trainingprogram} loc 
                                WHERE loc.published=1 AND $inprogressenddatecondition
                                AND 
                                RIGHT(loc.clevels,1) = $level
                                AND loc.id 
                       NOT IN (SELECT pc.programid FROM {program_enrollments} pc 
                                 WHERE pc.userid =$USER->id) "; 
                                              
            }
        
        }
        $inprogressprograms = $DB->get_records_sql($selectsql .$sql.$limitsql);
        $inprogresscount =$DB->count_records_sql($countsql .$sql.$limitsql); 

        $completeprograms = $DB->get_records_sql($completedselect .$completedsql.$limitsql);
        $comp_count =$DB->count_records_sql($countsql . $completedsql.$limitsql);

        if(!is_null($Jobrole) && $Jobrole != 0) {

            $recommendedlimit = " ORDER BY loc.availablefrom DESC LIMIT 5 "; 

            $recommendedprograms = $DB->get_records_sql($recommendedprogramssql .$recommendedprogramsformsql.$recommendedlimit);
            $recommendedprogramscount =$DB->count_records_sql($recommendedprogramscountsql . $recommendedprogramsformsql.$recommendedlimit);     
        } 

        $i=1;

        foreach ($inprogressprograms AS $inprogram) {

            $lang= current_language();

            if( $lang == 'ar' && !empty($inprogram->namearabic)){
                $programname = $inprogram->namearabic;

            }else{
                $programname = $inprogram->programname;
            }
            $programsdata= array();
            $programsdata['coid']=$i++;
            $programsdata['programid']=$inprogram->traineeid;
            $programsdata['datedisplay']=($inprogram->trainingmethod != 'elearning') ? true : false;
            $programsdata['programname'] =$programname;
            $programsdata['availableto'] =(has_capability('local/organization:manage_trainer', $systemcontext))?userdate($inprogram->availableto, get_string('strftimedatemonthabbr', 'langconfig')) :userdate($inprogram->enddate, get_string('strftimedatemonthabbr', 'langconfig')); 
            $programsdata['availablefrom'] =(has_capability('local/organization:manage_trainer', $systemcontext))?userdate($inprogram->availablefrom, get_string('strftimedatemonthabbr', 'langconfig')) :userdate($inprogram->startdate, get_string('strftimedatemonthabbr', 'langconfig'));
            $programsdata['courseid'] =$inprogram->courseid;
            if (!empty($inprogram->image)) {
                $programsdata['imageurl']=trainingprogramlogo_url($inprogram->image);
            }
            $programsdata['launchurl']=$CFG->wwwroot.'/course/view.php?id='.$inprogram->courseid;
            $programsdata['programviewurl']=$CFG->wwwroot.'/local/trainingprogram/programcourseoverview.php?programid='.$inprogram->traineeid;
          
            $total_in_progress_programs[] = $programsdata;
        }
        foreach ($completeprograms AS $program) {

            $lang= current_language();

            if( $lang == 'ar' && !empty($inprogram->namearabic)){
                $programname = $program->namearabic;

            }else{
                $programname = $program->programname;
            }

            $compprogramsdata= array();
            $compprogramsdata['coid']=$i++;
            $compprogramsdata['programid']=$program->traineeid;
            $compprogramsdata['programname'] =$programname;
            $compprogramsdata['description'] =format_text($program->description,FORMAT_HTML);
            $compprogramsdata['timecompleted'] = (has_capability('local/organization:manage_trainer', $systemcontext)) ? userdate($program->availableto, get_string('strftimedatemonthabbr', 'langconfig')) :userdate($program->completiondate, get_string('strftimedatemonthabbr', 'langconfig'));
            $compprogramsdata['courseid'] =$program->courseid;
            if (!empty($program->image)) {
                $compprogramsdata['imageurl']=trainingprogramlogo_url($program->image);
            }
            $compprogramsdata['launchurl']=$CFG->wwwroot.'/course/view.php?id='.$program->courseid;
            $compprogramsdata['programviewurl']=$CFG->wwwroot.'/local/trainingprogram/programcourseoverview.php?programid='.$program->traineeid;
            $moduleid = $DB->get_field_sql('SELECT offeringid FROM {program_completions} WHERE programid ='.$program->traineeid.' AND userid = '.$USER->id.' AND completion_status = 1');    
            $certid = $DB->get_field('tool_certificate_issues', 'code', array('moduleid'=>$moduleid,'userid'=>$USER->id,'moduletype'=>'trainingprogram'));
            $compprogramsdata['certid'] = $certid? $certid : 0;
            $compprogramsdata['viewcertificateurl'] = $certid? $CFG->wwwroot.'/admin/tool/certificate/view.php?code='.$certid : '#';

            
            $total_completed_programs[] = $compprogramsdata;
        }

        foreach ($recommendedprograms AS $record) {

            $lang= current_language();

            if( $lang == 'ar' && !empty($program->namearabic)){
                $programname = $program->namearabic;

            }else{
                $programname = $program->programname;
            }
            $programsdata= array();
            $programsdata["entityid"] = $record->id;
            if(current_language() == 'ar') {
            
                $programsdata["entityname"] = $record->arabicname;
            } else {
                $programsdata["entityname"] = $record->name;
            }
            if($record->price == 1) {
                $programsdata["price"] = $record->sellingprice;
            } else {
                $programsdata["price"] = get_string('free', 'local_userapproval');
            }

            $programsdata["enrollurl"] = $CFG->wwwroot.'/local/trainingprogram/programcourseoverview.php?programid='.$record->id;
            $programsdata["entitycode"] = $record->code;

            $programsdata["startdate"] =($record->startdate) ? userdate($record->startdate,get_string('strftimedatemonthabbr', 'core_langconfig')) : '--';
            $programsdata["enddate"] =$record->enddate? userdate($record->enddate,get_string('strftimedatemonthabbr', 'core_langconfig')):'--';
          
            $totalrecommendedprograms[] = $programsdata;
        }
        $viewmoreurl = $CFG->wwwroot.'/local/trainingprogram/index.php';
        $viewdata=[
        'total_in_progress_programs'=>$total_in_progress_programs,
        'total_completed_programs'=>$total_completed_programs,
        'totalrecommendedprograms'=>$totalrecommendedprograms,
        'completed_programs_view'=>(COUNT($total_completed_programs) > 0) ?  true : false,
        'in_progress_view_more_action'=>$inprogresscount > 4 ? true : false,
        'comp_count_view_more_action'=>$comp_count > 4 ? true : false,
        'recommendedprogramscount_view_more_action'=>$recommendedprogramscount > 4 ? true : false,
        'path'=>$CFG->wwwroot,
        'viewmoreurl'=>$viewmoreurl,
        'certificateview'=>(!is_siteadmin() && has_capability('local/organization:manage_trainee', $systemcontext))? true : false,
        ];
        $result = $OUTPUT->render_from_template('local_trainingprogram/trainings_block', $viewdata);
        return $result;
    }

     public function official_dashboard_programs_data()
    { 
        global $DB, $PAGE, $OUTPUT, $CFG, $USER;

        $renderer = $PAGE->get_renderer('local_trainingprogram');
        $renderable = new \local_trainingprogram\output\trainingprogram();
        return $renderer->render($renderable);
    }

    public function publish_current_program($programid) {
        global $DB;
        $data=$DB->execute('UPDATE {local_trainingprogram} SET published = 1 WHERE id='.$programid);
        return $data;
    }

    public function unpublish_current_program($programid) {
        global $DB;
        $data=$DB->execute('UPDATE {local_trainingprogram} SET published = 0 WHERE id='.$programid);
        return $data;
    }
   public function program_enrolled_users_view($programid,$selectedroleid,$offeringid) {
        global $DB, $PAGE, $OUTPUT, $CFG;
        $systemcontext = context_system::instance();        
        $renderer = $PAGE->get_renderer('local_trainingprogram');
        $filterparams  = $renderer->get_catalog_program_enrolled_users(true);
         $filterparams['submitid'] = 'form#filteringform';
        $filterparams['labelclasses'] = 'd-none';
        $filterparams['inputclasses'] = 'form-control';
        $filterparams['widthclass'] = 'col-md-6';
        $filterparams['placeholder'] = get_string('search_enrolled_users','local_trainingprogram');
        $filterparams['programid'] = $programid;
        $filterparams['selectedroleid'] = $selectedroleid;
        $filterparams['offeringid'] = $offeringid;
        $globalinput=$renderer->global_filter($filterparams);
        $fform = trainingprogram_tp_offerings_filters_form($filterparams);
        $enroledusers_view = $renderer->get_catalog_program_enrolled_users();
        $filterparams['enrolledusers_view'] = $enroledusers_view;
        $filterparams['globalinput'] = $globalinput;
        $filterparams['filterform'] = $fform->render();
        $filterparams['cfgurl'] = $CFG->wwwroot;
        $renderer->listofenrolledusers($filterparams);
      

   }

     public function get_listof_programenrolledusers($stable, $filterdata, $dataoptions) {

        global $DB, $PAGE, $OUTPUT, $USER;
        $programid = json_decode($dataoptions)->programid;
        $selectedroleid = json_decode($dataoptions)->selectedroleid;
        $offeringid = json_decode($dataoptions)->offeringid;
        $programrecord=$DB->get_record('local_trainingprogram', array('id' => $programid));
        $systemcontext = context_system::instance();
        $traineeeid = $DB->get_field('role', 'id', array('shortname' => 'trainee'));
        $trainerroleid= $DB->get_field('role', 'id', array('shortname' => 'trainer'));
        $currentlang = current_language();
        $selectsql = "SELECT CONCAT(tpo.code,u.id),u.id,u.firstname,u.lastname,lc.firstnamearabic,
            lc.lastnamearabic,lc.middlenameen,lc.middlenamearabic,lc.thirdnameen,
            lc.thirdnamearabic,u.email,lc.id_number,u.phone1,pe.roleid,pe.offeringid,
            tpo.code,pe.timecreated,lc.organization,tpo.id as offeringid,tpo.trainingmethod,tpo.startdate,tpo.enddate,tpo.time,tpo.endtime,tpo.type as offeringtype,
            pe.usercreated ,pe.enrolstatus,pe.enrolltype,tpo.financially_closed_status,pe.orgofficial 

            FROM {user} u 
            JOIN {local_users} lc ON lc.userid = u.id 
            JOIN {program_enrollments} pe ON pe.userid = u.id  
            JOIN {tp_offerings} tpo ON tpo.id = pe.offeringid 
            JOIN {role} crole ON crole.id = pe.roleid "; 
        $countsql = "SELECT COUNT(tpo.code) 
            FROM {user} u 
            JOIN {local_users} lc ON lc.userid = u.id 
            JOIN {program_enrollments} pe ON  pe.userid=u.id 
            JOIN {tp_offerings} tpo ON tpo.id=pe.offeringid
            JOIN {role} crole ON crole.id=pe.roleid ";
        $formsql = " WHERE 1=1 ";
        if(!is_siteadmin() &&  has_capability('local/organization:manage_organizationofficial',$systemcontext))
        {
          $get_all_orgofficials = (new exams())->get_all_orgofficials($USER->id);
                $users = implode(',',$get_all_orgofficials);
          $organization = $DB->get_field('local_users','organization',array('userid' => $USER->id));
          $formsql .= " AND  pe.enrolltype !=1 AND  u.id > 2 AND u.deleted = 0  AND lc.deleted=0 AND lc.approvedstatus= 2  AND pe.roleid = $traineeeid AND (pe.usercreated IN($users)  ||  lc.organization = $organization)  "; /*AND pe.usercreated = '.$USER->id.'*/
        } else {
            $formsql .= " AND pe.enrolltype !=1 AND u.id > 2 AND u.deleted = 0  AND lc.deleted=0 AND lc.approvedstatus= 2 ";
        }
        if($offeringid > 0) {
           $formsql .= ' AND pe.programid = '.$programid.' AND pe.courseid = '.$programrecord->courseid.' AND pe.offeringid = '.$offeringid.' ';
        } else {

            $formsql .= ' AND pe.programid = '.$programid.' AND pe.courseid = '.$programrecord->courseid.' ';
        }
        if(isset($filterdata->search_query) && trim($filterdata->search_query) != ''){

            $formsql .= " AND (u.firstname LIKE :firstnamesearch OR 
                              u.lastname LIKE :lastnamesearch  OR 
                              lc.firstnamearabic LIKE :firstnamearabicsearch OR 
                              lc.lastnamearabic LIKE :lastnamearabicsearch  OR
                              lc.middlenameen LIKE :middlenameensearch OR
                               lc.middlenamearabic LIKE :middlenamearabicsearch OR
                               lc.thirdnameen LIKE :thirdnameensearch OR 
                               lc.thirdnamearabic LIKE :thirdnamearabicsearch OR 
                               u.email LIKE :emailsearch  OR u.phone1 LIKE :mobilesearch OR 
                               lc.id_number LIKE :id_numbersearch OR 
                               crole.name LIKE :role_namesearch OR 
                               tpo.code LIKE :codesearch) ";

            $searchparams = array('firstnamesearch' => '%'.trim($filterdata->search_query).'%'
                ,'lastnamesearch' => '%'.trim($filterdata->search_query).'%',
                'firstnamearabicsearch' => '%'.trim($filterdata->search_query).'%',
                'lastnamearabicsearch' => '%'.trim($filterdata->search_query).'%',
                'middlenameensearch' => '%'.trim($filterdata->search_query).'%',
                'middlenamearabicsearch' => '%'.trim($filterdata->search_query).'%',
                'thirdnameensearch' => '%'.trim($filterdata->search_query).'%',
                'thirdnamearabicsearch' => '%'.trim($filterdata->search_query).'%',
                'emailsearch' => '%'.trim($filterdata->search_query).'%' ,
                'mobilesearch' => '%'.trim($filterdata->search_query).'%',
                'id_numbersearch' => '%'.trim($filterdata->search_query).'%',
                'role_namesearch' => '%'.trim($filterdata->search_query).'%',
                'codesearch' => '%'.trim($filterdata->search_query).'%');

        }else{
            $searchparams = array();
        }
        if(!empty($filterdata->tp_offerings)){

            $formsql.=" AND  pe.offeringid IN ($filterdata->tp_offerings) ";
        }
        if(!empty($filterdata->tp_role)){

            $formsql.=" AND  pe.roleid IN ($filterdata->tp_role) ";
        }
        if(!empty($filterdata->tp_organization)){

            $formsql.=" AND  lc.organization IN ($filterdata->tp_organization)";
        }

        if(!empty($selectedroleid)) {
            $roleshortname = $DB->get_field('role','shortname',['id'=>$selectedroleid]);
            if($roleshortname == 'trainee'){
              $selected_role_id = (int) $selectedroleid;
              $formsql.=" AND  pe.roleid = $selected_role_id ";
            } 
            if($roleshortname == 'trainer'){
                $traineroreditingtrainer = $DB->get_fieldset_sql("SELECT id FROM {role} WHERE shortname IN('trainer', 'editingtrainer') ");
                $traineroreditingtrainer = implode(',', $traineroreditingtrainer);
                $formsql.=" AND  pe.roleid IN ($traineroreditingtrainer) ";
            }
        } 
        $params = array_merge($searchparams);
        $totalusers = $DB->count_records_sql($countsql.$formsql, $params);
        $formsql .=" ORDER BY pe.timecreated DESC";

        $users = $DB->get_records_sql($selectsql.$formsql, $params, $stable->start,$stable->length);
        $listofusers = array();
        $count = 0;
        foreach($users as $user) {

            $offering = $DB->get_record_sql("SELECT * FROM {tp_offerings} WHERE id=$user->offeringid");
           

            if($currentlang == 'ar') {

                $organization = $DB->get_field('local_organization','fullnameinarabic',array('id'=>$user->organization));

            } else {

                $organization = $DB->get_field('local_organization','fullname',array('id'=>$user->organization));

            }

            $trainerroleid = $DB->get_field_sql("SELECT id FROM {role} WHERE shortname = 'trainer'");
            if( $currentlang == 'ar' && !empty($user->firstnamearabic)){
                $firstname = $user->firstnamearabic;
            }else{
                $firstname = $user->firstname;
            }
            if( $currentlang == 'ar' && !empty($user->lastnamearabic)){
                $lastname = $user->lastnamearabic;
            }else{
                $lastname = $user->lastname;
            }
            if( $currentlang == 'ar' && !empty($user->middlenamearabic)){
                $middlename = $user->middlenamearabic;
            }else{
                $middlename = $user->middlenameen;
            }
            if( $currentlang == 'ar' && !empty($user->thirdnamearabic)){
                $thirdname = $user->thirdnamearabic;
            }else{
                $thirdname = $user->thirdnameen;
            }
            $listofusers[$count]['id'] = $user->id;
            $listofusers[$count]['fullname'] = $firstname.' '.$middlename.' '.$thirdname.' '.$lastname;
            $listofusers[$count]['email'] = $user->email;
            $listofusers[$count]['id_number'] = $user->id_number;
            $listofusers[$count]['phone'] = $user->phone1;
            $listofusers[$count]['offeringid'] = $user->offeringid;
            $listofusers[$count]['organization'] = $user->organization > 0 ? $organization : '';
            $listofusers[$count]['offeringcode'] = $user->code;
            $role = $DB->get_record('role',array('id' => trim($user->roleid)));
            $listofusers[$count]['roleid'] = !empty($role->id) ? $role->id : 0;
            $listofusers[$count]["programprice"] = $programrecord->price ;
            $listofusers[$count]["replacementfee"] = 100;
            $listofusers[$count]['rolename'] = get_string($role->shortname,'local_trainingprogram');
            $listofusers[$count]['enrolledat'] = userdate($user->timecreated,get_string('strftimedatemonthabbr', 'core_langconfig')) ;
            $listofusers[$count]['roleshortname'] = !empty($role->shortname) ? $role->shortname : '--';
            $startdate =  $user->startdate;
            $sdate = strtotime(userdate($user->startdate,'%Y-%m-%d'));
            $curr_date = strtotime(userdate(time(),'%Y-%m-%d'));
            $remainingdays = floor(($sdate - $curr_date) / (60 * 60 * 24));
            $productid =(int) $DB->get_field_sql('SELECT tlp.id FROM {tool_products} tlp 
            JOIN {local_trainingprogram} lot ON lot.code = tlp.code 
            WHERE tlp.category =:category AND tlp.referenceid =:referenceid',['category'=>1,'referenceid'=>$user->offeringid]);
            $listofusers[$count]["productid"] =($productid) ? $productid : 0;
            $listofusers[$count]["sellingprice"] =$programrecord->sellingprice;
            $listofusers[$count]["programdate"] = ($startdate > 0 )? $startdate: 0 ;
            $listofusers[$count]["isnotelearningmethod"] = ($user->trainingmethod !='elearning' )? 1: 0 ;
            $listofusers[$count]["remainingdays"] = ($programrecord->price == 1  && $startdate > 0 )? $remainingdays: 0 ;
            $listofusers[$count]["replacebuttonview"] = ($programrecord->price == 0 || ($programrecord->price == 1  && $remainingdays >= 2)) ? true: false;
            $listofusers[$count]["cancelbuttonview"] = ($programrecord->price == 0 || ($programrecord->price == 1  && $startdate > 0 && $remainingdays >= 1)) ? true: false;
            $offeringenddatetime = ($offering->enddate+$offering->time+$offering->duration);
           
            $currtime = time();
            $listofusers[$count]["absentviewaction"] = ($offering->trainingmethod == 'elearning'  || ($offering->trainingmethod != 'elearning' && $offeringenddatetime < $currtime) && (is_siteadmin() || has_capability('local/organization:manage_organizationofficial',$systemcontext)))
            ? true: false;
            $listofusers[$count]["unassignuser"] = (!is_siteadmin() &&  has_capability('local/organization:manage_communication_officer',$systemcontext)  && $role->shortname == 'trainer') ? false : true ;
            $listofusers[$count]["programid"] = $programid;
            $listofusers[$count]["currentuserisadmin"] =(is_siteadmin() || has_capability('local/organization:manage_communication_officer', $systemcontext) || has_capability('local/organization:manage_trainingofficial', $systemcontext)) ? 1 : 0;
            $enrolleduserid =(int)$user->usercreated;
            $enrolleduseroleinfo = $DB->get_record_sql('SELECT rol.* FROM {role} rol 
            JOIN {role_assignments} rola ON rola.roleid = rol.id
            WHERE rola.userid =:userid and contextid =:contextid',['userid'=>$enrolleduserid,'contextid'=>$systemcontext->id]);
            $listofusers[$count]["orgofficialenrolled"] =($user->orgofficial > 0) ? 1 :(($enrolleduseroleinfo)?(($enrolleduseroleinfo->shortname == 'organizationofficial') ? 1 : 0) : 0);
            $programname= (current_language() == 'ar') ? $programrecord->namearabic : $programrecord->name; 
            $listofusers[$count]["programname"] = $programname;
            $completionstatus = $DB->record_exists_sql('SELECT * FROM {program_completions} WHERE programid = '.$programid.' AND offeringid = '.$user->offeringid.' AND userid = '.$user->id.' AND (completion_status = 1 OR completion_status = 2)');
            $listofusers[$count]["offering_status"] =($user->financially_closed_status == 1) ? get_string('fincnaciallyclosed','local_trainingprogram')  :  (($completionstatus) ? get_string('completed','local_trainingprogram') : get_string('in_progress','local_trainingprogram'));
           // $moduleid = $DB->get_field_sql('SELECT offeringid FROM {program_completions} WHERE programid ='.$programid.' AND userid = '.$user->id.' AND (completion_status = 1 OR completion_status = 2)');
            $certid = $DB->get_field('tool_certificate_issues', 'code', array('moduleid'=>$user->offeringid,'userid'=>$user->id,'moduletype'=>'trainingprogram'));
            $listofusers[$count]['certid'] = $certid? $certid : 0;
            $listofusers[$count]["adminenrolled"]=(!$enrolleduseroleinfo) ? 1 : 0;          
            $listofusers[$count]['certificateview'] = true ;
            $listofusers[$count]["currentuserorgoff"] = (!is_siteadmin() && has_capability('local/organization:manage_organizationofficial',$systemcontext)) ? 1 : 0;
            $listofusers[$count]["enrolledrole"]=(empty($enrolleduseroleinfo->shortname) || $enrolleduseroleinfo->shortname == 'co' || $enrolleduseroleinfo->shortname == 'to') ?  'admin' :  $enrolleduseroleinfo->shortname;
            if (!is_siteadmin() && has_capability('local/organization:manage_organizationofficial',$systemcontext) &&  $enrolleduseroleinfo->shortname == 'trainee') {
                $listofusers[$count]["disableallactions"] = true;
            } else {
                $listofusers[$count]["disableallactions"] = false;
            }
            // $courseinstance = \context_course::instance($programrecord->courseid);
            // $useroleinfo = $DB->get_record_sql('SELECT rol.* FROM {role} rol 
            // JOIN {role_assignments} rola ON rola.roleid = rol.id
            // WHERE rola.userid =:userid and contextid =:contextid',['userid'=>$user->id,'contextid'=>$courseinstance->id]);
            $listofusers[$count]["istrainer"] = ($user->trainingmethod =='elearning' || $role->shortname == 'trainer' || $role->shortname == 'editingtrainer') ? true : false;
            $listofusers[$count]["iswaitingforapproval"] = ($user->enrolstatus == 0) ? true :false;
            $listofusers[$count]["isfinaciallyclosed"] = ($user->financially_closed_status == 1) ? true :false;
            $listofusers[$count]["enrolltype"] = $user->enrolltype;
            $listofusers[$count]["enrolledby"] = (empty($enrolleduseroleinfo->shortname)) ? get_string('admin','local_trainingprogram'):get_string($enrolleduseroleinfo->shortname,'local_trainingprogram');
            $listofusers[$count]["cangenerateinvoice"] =((int)$user->offeringtype == 1) ? 0 : 1;
            $listofusers[$count]["hasofferingcompleted"] = ($completionstatus) ? 1: 0;

            $count++;
        }
        $usersContext = array(
            "hascourses" => $listofusers,
            "totalusers" => $totalusers,
            "length" => COUNT($listofusers),
        );
        return $usersContext;


   }
   public function get_listof_traineeusers($programid,$offeringid) {
        global $DB, $PAGE, $OUTPUT, $CFG, $USER;
        $systemcontext = context_system::instance();
        $role = array();
        if(is_siteadmin()  ||  has_capability('local/organization:manage_trainingofficial',$systemcontext)){
           $assigntrainer = true;
        }   
        
        $courseid=$DB->get_field('local_trainingprogram','courseid', array('id' => $programid));
        $trainerroleid = $DB->get_field('role', 'id', array('shortname' => 'trainer'));
        $editingtrainer = $DB->get_field('role', 'id', array('shortname' => 'editingtrainer'));
        $traineeeid = $DB->get_field('role', 'id', array('shortname' => 'trainee'));
        $editingtrainer = $editingtrainer ? ", $editingtrainer" : false;
        if(!is_siteadmin() &&  has_capability('local/organization:manage_organizationofficial',$systemcontext))
        {
             $organization = $DB->get_field('local_users','organization',array('userid' => $USER->id));

            $sql1 = "SELECT COUNT(userid) FROM {program_enrollments}  WHERE  programid = $programid AND offeringid = $offeringid AND courseid = $courseid AND roleid IN($trainerroleid $editingtrainer) AND usercreated = $USER->id AND userid IN (SELECT lu.userid FROM {local_users} AS lu JOIN {user} AS u ON lu.userid = u.id WHERE lu.organization = $organization AND u.deleted = 0)";

            $sql2 = 'SELECT COUNT(userid) FROM {program_enrollments} WHERE  programid = '.$programid.' AND offeringid = '.$offeringid.' AND courseid = '.$courseid .' AND roleid = '.$traineeeid.' AND usercreated = '.$USER->id.' AND userid IN (SELECT lu.userid FROM {local_users} AS lu JOIN {user} AS u ON lu.userid = u.id WHERE lu.organization = '.$organization.' AND u.deleted = 0) AND enrolstatus = 1';
        } else {

            $sql1 = "SELECT COUNT(userid) FROM {program_enrollments}  WHERE  programid = $programid AND offeringid = $offeringid AND courseid = $courseid  AND roleid IN($trainerroleid $editingtrainer) AND userid IN (SELECT lu.userid FROM {local_users} AS lu JOIN {user} AS u ON lu.userid = u.id WHERE u.deleted = 0) ";

            $sql2 = 'SELECT COUNT(userid) FROM {program_enrollments} WHERE  programid = '.$programid.' AND offeringid = '.$offeringid.' AND courseid = '.$courseid .' AND roleid = '.$traineeeid.' AND userid IN (SELECT id FROM {user} WHERE deleted = 0) AND enrolstatus = 1';

        }
        $programoffusers =$DB->count_records_sql($sql1);

        $traineeusers =$DB->count_records_sql($sql2);
 
        $enrollid = $DB->get_field('enrol','id', array('courseid' => $courseid, 'enrol' => 'manual'));

        $displayname = (current_language() == 'ar') ? 'namearabic' : 'name';
        $traineroreditingtrainer = $DB->get_fieldset_sql("SELECT id FROM {role} WHERE shortname IN('trainer', 'editingtrainer') ");
        // print_object($traineroreditingtrainer);die;
        $viewdata=[
            'trainerid'=>$trainerroleid ,
            'traineeid'=>$DB->get_field('role', 'id', array('shortname' => 'trainee')),
            'programoffusers'=>$programoffusers ? $programoffusers : 0,
            'traineeusers'=>$traineeusers ? $traineeusers : 0,
            'programid'=>$programid,
            'programname'=>$DB->get_field_sql('SELECT '.$displayname.' FROM {local_trainingprogram} WHERE id = '.$programid.''),
            'enrollid'=>$enrollid,
            'courseid'=>$courseid,
            'offeringid'=>$offeringid,
            'url'=>$CFG->wwwroot,
            'assigntrainer'=>$assigntrainer,
            'enrolledurl'=>$CFG->wwwroot.'/local/trainingprogram/programenrolleduserslist.php?programid='.$programid,
            'enrolledaction' => (is_siteadmin() ||  has_capability('local/organization:manage_communication_officer',$systemcontext) ||  has_capability('local/organization:manage_trainingofficial',$systemcontext)) ? true: false,
            'enrolltraineesaction' => (!is_siteadmin() &&  !has_capability('local/organization:manage_trainingofficial',$systemcontext) &&  !has_capability('local/organization:manage_communication_officer',$systemcontext) ) ? false: true,        
        ];

        echo $OUTPUT->render_from_template('local_trainingprogram/viewptrainers', $viewdata);
    }

    public function enroll_trainer($data) {
        global $DB;
        $offeringid = $data->offeringid;
        $programid = $data->programid;
        $roleid = $data->roleid;
        $courseid=$DB->get_field('local_trainingprogram','courseid', array('id' => $programid));
        // If trainers are selected to be assigned as Editing Trainer
        if ($data->editingroleid) {
            $edditingtrainerroleid = $DB->get_field('role', 'id', ['shortname' => 'editingtrainer']);
            $editingtrainers = explode(',', $data->editingroleid);
            if ($data->users) {
                $trainertype = 0;
                if (!is_array($data->users)) {
                    $data->users = [$data->users];
                }
                $data->users = array_diff($data->users, $editingtrainers);
            }else{
                $trainertype=1;
                if (!is_array($data->oguser)) {
                    $data->oguser = [$data->oguser];
                }
                $data->oguser = array_diff($data->oguser, $editingtrainers);
            }
            for ($i=0; $i < count($editingtrainers); $i++) { 
                (new trainingprogram)->program_enrollment($offeringid,$editingtrainers[$i],$edditingtrainerroleid,$trainertype);
            }
        }
        if($data->users) {
            $trainertype=0;
            $trainers = implode(',', $data->users);
            list($trainersql,$trainerparams) = $DB->get_in_or_equal($data->users);
            $querysql = "SELECT id,firstname  FROM {user} WHERE id $trainersql";
            $trainerslist= $DB->get_records_sql($querysql,$trainerparams);
            // For trainers
            foreach ($trainerslist AS $trainer) {
               (new trainingprogram)->program_enrollment($offeringid,$trainer->id,$roleid,$trainertype);
            } 
        }
        if($data->oguser) {
            $trainertype=1;
            $trainers=implode(',', $data->oguser);
            list($trainersql,$trainerparams) = $DB->get_in_or_equal($data->oguser);
            $querysql = "SELECT id,firstname  FROM {user} WHERE id $trainersql";
            $trainerslist= $DB->get_records_sql($querysql,$trainerparams);
            foreach ($trainerslist AS $trainer) {
               (new trainingprogram)->program_enrollment($offeringid,$trainer->id,$roleid,$trainertype);
            } 
        }
        
    }
    public function unassignuser($programid,$offeringid,$userid,$roleid) {
        global $DB, $PAGE, $OUTPUT, $CFG;

        $offering = $DB->get_record('tp_offerings', array('id' => $offeringid), '*', MUST_EXIST);
        $courseid=$DB->get_field('local_trainingprogram','courseid', array('id' => $programid));
        $enrolledofferings =(int) $DB->get_field_sql('SELECT COUNT(id) FROM {program_enrollments} WHERE programid=:programid AND courseid =:courseid AND userid =:userid   AND roleid=:roleid',['programid'=>$programid,'courseid'=>$courseid,'userid'=>$userid,'roleid'=>$roleid]);
        $manual = enrol_get_plugin('manual');
        $instance = $DB->get_record('enrol', array('courseid' => $courseid, 'enrol' => 'manual'), '*', MUST_EXIST);
        $group = groups_get_group_by_idnumber($courseid, $offering->code);
        if($enrolledofferings == 1) {
            $manual->unenrol_user($instance, $userid);
        }
        if ($group->id) {
            groups_remove_member($group->id, $userid,null,0);
        }
        (new trainingprogram)->program_unenrollment($programid,$offeringid,$courseid,$userid,$roleid);

        if($offering->type == 2){

            (new learningtracks)->program_unenrol($programid, $offeringid, $roleid, $userid);
        }

        
    }

      public function update_trainingprogram_status($trainingprogram) {
        global $DB, $USER;
        $program         = new stdClass();
        $program->programid     = $trainingprogram->programid;
        $program->offeringid     = $trainingprogram->offeringid;
        $program->userid     = $trainingprogram->userid;
        $program->realuser    = ($USER->realuser) ? $USER->realuser :0;
        $offeringmethod = $DB->get_field('tp_offerings','trainingmethod',['id'=>$trainingprogram->offeringid]);
        $preexam = ($trainingprogram->preexam) ? $DB->record_exists_sql(" SELECT id FROM {course_modules} WHERE course = $trainingprogram->courseid AND instance = $trainingprogram->preexam AND module=(SELECT id FROM {modules} WHERE name = 'quiz') AND deletioninprogress =0"):false;
        $postexam = ($trainingprogram->postexam) ? $DB->record_exists_sql(" SELECT id FROM {course_modules} WHERE course = $trainingprogram->courseid AND instance = $trainingprogram->postexam AND module=(SELECT id FROM {modules} WHERE name = 'quiz') AND deletioninprogress =0"):false;

        $completions=$DB->get_record('program_completions',array('programid'=>$program->programid,'offeringid'=>$program->offeringid,'userid'=>$program->userid),'id,preexam_completion_status,postexam_completion_status,completion_status,completiondate');
        
        try {

            $program->completion_status = 0;

            if((isset($trainingprogram->cmid) && isset($trainingprogram->preexam)) && ($trainingprogram->cmid==$trainingprogram->preexam)){

                $program->preexam_completion_status = $trainingprogram->completionstate;
                $program->completion_status = $trainingprogram->completionstate;

                if(($completions->postexam_completion_status == 0 || empty($completions)) && $trainingprogram->postexam > 0){

                    $program->completion_status = 0;
                    
                }

            }elseif((isset($trainingprogram->cmid) && isset($trainingprogram->postexam)) && ($trainingprogram->cmid==$trainingprogram->postexam)){

                $program->postexam_completion_status = $trainingprogram->completionstate;
                $program->completion_status = $trainingprogram->completionstate;

                if(($completions->preexam_completion_status == 0 || empty($completions) ) && $trainingprogram->preexam > 0){

                    $program->completion_status = 0;
                    
                }
            }else{
                if($preexam && $trainingprogram->preexam > 0 &&  !empty($completions)){
                    $program->completion_status =  $completions->preexam_completion_status; 
                    
                }
                if($postexam && $trainingprogram->postexam > 0 &&  !empty($completions)){
                    $program->completion_status = $completions->postexam_completion_status;
                }
            }
            
            $completionstatus=$program->completion_status;

            if($trainingprogram->attendancecmpltn > 0 && $trainingprogram->attendancepercnt > 0){

               // $program->completion_status = 0;

                // $sql = "SELECT COUNT(atdnlg.id) 
                //         FROM {attendance} as atdn 
                //         JOIN {attendance_sessions} as atdnsn ON atdnsn.attendanceid=atdn.id 
                //         JOIN {attendance_log} as atdnlg ON atdnlg.sessionid=atdnsn.id 
                //         WHERE atdn.course=:courseid AND atdnlg.studentid=:userid AND atdnlg.statusid IN (SELECT id FROM {attendance_statuses} WHERE attendanceid = atdn.id AND acronym='P') ";

                // $params = array('userid'=>$program->userid,'courseid'=>$trainingprogram->courseid);

                // $user_attendancecount = $DB->count_records_sql($sql, $params);

                $sql = "SELECT COUNT(distinct atdnlg.sessionid) 
                FROM {attendance} as atdn 
                JOIN {attendance_sessions} as atdnsn ON atdnsn.attendanceid=atdn.id 
                JOIN {attendance_log} as atdnlg ON atdnlg.sessionid=atdnsn.id 
                JOIN {offering_sessions} as os ON os.sessionid=atdnlg.sessionid AND os.offeringid=$program->offeringid
                WHERE atdn.course=:courseid AND atdnlg.studentid=:userid AND atdnlg.statusid IN (SELECT id FROM {attendance_statuses} WHERE attendanceid = atdn.id AND acronym='P')";
                $params = array('userid'=>$program->userid,'courseid'=>$trainingprogram->courseid);
                $user_attendancecount = $DB->count_records_sql($sql, $params);

                if($user_attendancecount){

                    $sql = "SELECT COUNT(ofsn.id) 
                        FROM {offering_sessions} as ofsn 
                        JOIN {tp_offerings} as tpofr ON tpofr.id=ofsn.offeringid 
                        WHERE tpofr.trainingid=:trainingid AND tpofr.id=:offeringid  ";


                    $params = array('trainingid'=>$program->programid,'offeringid'=>$program->offeringid);

                    $program_offerings = $DB->count_records_sql($sql, $params);

                    // if($program_offerings){

                    //     $ttlcnt_cmpc_cmpln=$program_offerings;

                    //     $percentage=($user_attendancecount/$ttlcnt_cmpc_cmpln)*100;

                    //     if($ttlcnt_cmpc_cmpln > 0 && $percentage >= $trainingprogram->attendancepercnt ){

                    //         $program->completion_status = $completionstatus;

                    //     }

                    // }
                    if($program_offerings > 0){
                        $percentage=($user_attendancecount/$program_offerings)*100;
                        if($percentage >= 80){
                            $program->completion_status = ($postexam || $preexam)?$completionstatus:1;
                        }
                    }    
                }
            }
            $program->completiondate = ($program->completion_status) ? time() : 0;

            $sectionid = $DB->get_field_sql('SELECT sections FROM {tp_offerings} WHERE id = '.$program->offeringid.'');

            if($sectionid){

                $cmidsql = 'SELECT COUNT(com.id) FROM {course_modules} as com JOIN {course_sections} as cos ON com.section = cos.id AND com.course = cos.course WHERE  cos.course = '.$trainingprogram->courseid.' AND cos.id = '.$sectionid.' AND com.completion <> 0 AND com.deletioninprogress =0 ';

                $offeringcriterias = $DB->count_records_sql($cmidsql);
               
                $usercmidsql = 'SELECT COUNT(com.id) FROM {course_modules_completion} as cmc JOIN {course_modules} as com ON com.id=cmc.coursemoduleid JOIN {course_sections} as cos ON com.section = cos.id AND com.course = cos.course WHERE cos.course = '.$trainingprogram->courseid.' AND cos.id = '.$sectionid.' AND cmc.userid = '.$program->userid.' AND cmc.completionstate <> 0 AND cmc.completionstate <> 3 ';

                $userofferingcriterias = $DB->count_records_sql($usercmidsql);

                if($offeringcriterias == $userofferingcriterias){
                    $program->completion_status = $program->completion_status;
                    $program->completiondate = ($program->completion_status) ? time() : 0;

                }else{

                    $program->completion_status = 0;

                    $program->completiondate =  0;

                }

            }
           
            if($completions){

                if($offeringmethod == 'elearning') {
                        $certificatealreadygenerated = $DB->record_exists_sql(' SELECT tci.* FROM         {tool_certificate_issues}    tci 
                            JOIN {program_completions} pc 
                            ON pc.offeringid = tci.moduleid AND pc.userid = tci.userid AND tci.moduletype =:moduletype 
                            WHERE tci.userid =:userid AND tci.moduleid =:moduleid  AND completion_status IN (1,2)',['userid'=>$trainingprogram->userid,'moduleid'=>$trainingprogram->offeringid,'moduletype'=>'trainingprogram']);

                    if(!$certificatealreadygenerated) {
                        $program->id   = $completions->id;
                        $program->completion_status = ($trainingprogram->course_completed > 0) ?$trainingprogram->course_completed : $program->completion_status; 
                        $program->completiondate =($trainingprogram->course_completed > 0) ? time() : $program->completiondate;
                        $program->usermodified   = $USER->id;
                        $program->timemodified   = time();
                        $DB->update_record('program_completions', $program);

                    }
                } else {

                    $program->id   = $completions->id;
                    $program->usermodified   = $USER->id;
                    $program->timemodified   = time();
    
                    $DB->update_record('program_completions', $program);
                }
    
                // Trigger an event for training program completion changed.
                $event = \local_trainingprogram\event\trainingprogram_completion_updated::create(array(
                    'objectid' => $program->id,
                    'context' => context_system::instance(),
                    'relateduserid' => $trainingprogram->userid,
                    'other' => array(
                        'relateduserid' => $trainingprogram->userid,
                        'programid' => $program->programid,
                        'offeringid' => $program->offeringid,
                        'completion_status' => $program->completion_status ? $program->completion_status : 0,
                    )
                ));
                $event->add_record_snapshot('program_completions', $program);
                $event->trigger();

                $trackid = $DB->get_field_sql("SELECT trackid
                            FROM {local_lts_item_enrolment} 
                            WHERE itemid = $trainingprogram->programid
                            AND userid = $trainingprogram->userid AND itemtype = 1 ");

                if($trackid) {

                    $lts_enrollment =$DB->get_record('local_lts_enrolment',['trackid' =>$trackid,'userid'=>$trainingprogram->userid]);

                    $event = \local_learningtracks\event\learningtracks_completion_updated::create(array(
                        'objectid' => $program->id,
                        'context' => context_system::instance(),
                        'relateduserid' => $trainingprogram->userid,
                        'other' => array(
                            'relateduserid' => $trainingprogram->userid,
                            'learningtrackid' => $trackid,
                            'completion_status' =>  $program->completion_status ? $program->completion_status : 0,
                        )
                    ));
                    $event->add_record_snapshot('trainingprogram_program_completion', $lts_enrollment);
                    $event->trigger();

                }
                    
                 
            }else{

                if($offeringmethod == 'elearning') {
                    $program->completion_status = ($trainingprogram->course_completed > 0) ? 1 : $program->completion_status; 
                    $program->completiondate =($trainingprogram->course_completed > 0) ? time() : $program->completiondate;
                    $program->usercreated   = $USER->id;
                    $program->timecreated   = time();
                    $program->id=$DB->insert_record('program_completions', $program);

                } else {
                    $program->usercreated   = $USER->id;
                    $program->timecreated   = time();
                    $program->id=$DB->insert_record('program_completions', $program);

                }
                // Trigger an event for training program completion changed.
                $event = \local_trainingprogram\event\trainingprogram_completion_updated::create(array(
                    'objectid' => $program->id,
                    'context' => context_system::instance(),
                    'relateduserid' => $trainingprogram->userid,
                    'other' => array(
                        'relateduserid' => $trainingprogram->userid,
                        'programid' => $program->programid,
                        'offeringid' => $program->offeringid,
                        'completion_status' => $program->completion_status ? $program->completion_status : 0,
                    )
                ));
                $event->add_record_snapshot('program_completions', $program);
                $event->trigger();

                $trackid = $DB->get_field_sql("SELECT trackid
                          FROM {local_lts_item_enrolment} 
                         WHERE itemid = $trainingprogram->programid
                            AND userid = $trainingprogram->userid AND itemtype = 1 ");

                if($trackid) {

                    $lts_enrollment =$DB->get_record('local_lts_enrolment',['trackid' =>$trackid,'userid'=>$trainingprogram->userid]);

                    $event = \local_learningtracks\event\learningtracks_completion_updated::create(array(
                        'objectid' => $program->id,
                        'context' => context_system::instance(),
                        'relateduserid' => $trainingprogram->userid,
                        'other' => array(
                            'relateduserid' => $trainingprogram->userid,
                            'learningtrackid' => $trackid,
                            'completion_status' => $program->completion_status ? $program->completion_status : 0,
                        )
                    ));
                    $event->add_record_snapshot('trainingprogram_program_completion', $lts_enrollment);
                    $event->trigger();

                }
                $traineesql = "SELECT ra.id
                FROM {role_assignments} ra 
                JOIN {role} as r ON r.id = ra.roleid
                 WHERE ra.contextid = 1 AND r.shortname = 'trainee' AND ra.userid = ".$trainingprogram->userid;
                $traineerole = $DB->get_field_sql($traineesql);
                if($traineerole){
                    $tpdata=$DB->get_record('local_trainingprogram',array('id'=>$trainingprogram->programid));               
                    $tpdata->program_name=$tpdata->name;
                    $tpdata->program_arabicname=$tpdata->namearabic;
                    $tpusers=$DB->get_record('local_users',array('userid'=>$trainingprogram->userid));
                    $tpdata->program_userfullname=$tpusers->firstname .' '. $tpusers->lastname;
                     $tpdata->program_arabicuserfullname=$tpusers->firstnamearabic .' '. $tpusers->lastnamearabic;  
                     $trainee=$DB->get_record('user',array('id'=>$trainingprogram->userid));           
                (new \local_trainingprogram\notification())->trainingprogram_notification('trainingprogram_completion', $touser=$trainee,$fromuser=$USER,$tpdata,$waitinglistid=0);
                }   
                
            }
        } catch (dml_exception $ex) {
            print_error($ex);
        }
        return true;
    }  

    public function completedprograms() {
         global $DB, $PAGE, $OUTPUT, $USER, $CFG;
          $systemcontext = context_system::instance();
          $trainingprogramlist = array();

          $expiredenddatecondition = (new trainingprogram())->from_unixtime_for_expired_entities('lo.availableto');

           $selectsql = "SELECT lo.id as traineeid,lo.name as programname,lo.availableto,lo.availablefrom,lo.image,lo.courseid,lo.description,lo.sectors,lo.targetgroup,lo.competencyandlevels FROM {local_trainingprogram} lo WHERE $expiredenddatecondition";

            $trainingprograms = $DB->get_records_sql($selectsql);
             $i=1;
            foreach($trainingprograms as $trainingprogram) {
            $lang= current_language();

            if( $lang == 'ar'){
                $programname = $trainingprogram->namearabic;

            }else{
                $programname = $trainingprogram->name;
            }

                $trainingprogramlist['sno'] = $i++;
                $trainingprogramlist['trainingid'] = $trainingprogram->traineeid;
                $trainingprogramlist['tainingprogramname'] = $programname;
                $trainingprogramlist['availablefrom'] = userdate($trainingprogram->availablefrom, get_string('strftimedatemonthabbr', 'langconfig'));
                $trainingprogramlist['availableto'] = userdate($trainingprogram->availableto, get_string('strftimedatemonthabbr', 'langconfig'));         
                $trainingprogramlist['published'] = $trainingprogram->published;
                $trainingprogramlist['courseid'] = $trainingprogram->courseid; 
                $trainingprogramlist['enrolledusers'] = $DB->count_records('program_enrollments', array('programid'=>$trainingprogram->id)); 
                list($competencysql,$competencyparams) = $DB->get_in_or_equal(explode(',',$trainingprogram->competencyandlevels));
                $compquerysql = "SELECT id AS competencyid,name AS competencyname, level AS competencylevel FROM {local_competencies} WHERE id $competencysql";
                $competencieslists= $DB->get_records_sql($compquerysql,$competencyparams);
                $competencies=[];
                foreach ($competencieslists AS $competency) {
                    $competencies[]=$competency->competencyname;
                }    
                $trainingprogramlist['competencies'] = implode(',', $competencies);
                $trainingprogramlist['deleteaction'] = $DB->record_exists('tp_offerings',array('trainingid' => $trainingprogram->traineeid))? true :false; 
                $trainingprogramlist['detailedprogramviewurl'] = $CFG->wwwroot.'/local/trainingprogram/programdetailedview.php?programid='.$trainingprogram->traineeid;    


            }

            return $trainingprogramlist;

    }

    public function mycompletedprograms() {
         global $DB, $PAGE, $OUTPUT, $USER;

          $trainingprogramlist = array();

           $selectsql = "SELECT loc.id as traineeid,loc.name as programname,loc.availableto,loc.availablefrom,loc.image,loc.courseid,loc.description,loc.sectors,loc.targetgroup,loc.competencyandlevels FROM {local_trainingprogram} loc 
                            JOIN {course} c ON loc.courseid =c.id 
                            JOIN {enrol} e ON e.courseid = c.id
                            JOIN {user_enrolments} ue ON e.id = ue.enrolid 
                            JOIN {course_completions} cc ON cc.course = c.id 
                            WHERE ue.userid = ".$USER->id." AND cc.timecompleted IS NOT NULL ORDER BY loc.availablefrom DESC";

            $trainingprograms = $DB->get_records_sql($selectsql);
             $i=1;
            foreach($trainingprograms as $trainingprogram) {

            $lang= current_language();

            if( $lang == 'ar'){
                $programname = $trainingprogram->namearabic;

            }else{
                $programname = $trainingprogram->name;
            }


                $trainingprogramlist['sno'] = $i++;
                $trainingprogramlist['trainingid'] = $trainingprogram->traineeid;
                $trainingprogramlist['tainingprogramname'] = $programname;
                $trainingprogramlist['availablefrom'] = userdate($trainingprogram->availablefrom, get_string('strftimedatemonthabbr', 'langconfig'));
                $trainingprogramlist['availableto'] = userdate($trainingprogram->availableto, get_string('strftimedatemonthabbr', 'langconfig'));         
                $trainingprogramlist['published'] = $trainingprogram->published;
                $trainingprogramlist['courseid'] = $trainingprogram->courseid; 
                $trainingprogramlist['enrolledusers'] = $DB->count_records('program_enrollments', array('programid'=>$trainingprogram->id)); 
                list($competencysql,$competencyparams) = $DB->get_in_or_equal(explode(',',$trainingprogram->competencyandlevels));
                $compquerysql = "SELECT id AS competencyid,name AS competencyname, level AS competencylevel FROM {local_competencies} WHERE id $competencysql";
                $competencieslists= $DB->get_records_sql($compquerysql,$competencyparams);
                $competencies=[];
                foreach ($competencieslists AS $competency) {
                    $competencies[]=$competency->competencyname;
                }    
                $trainingprogramlist['competencies'] = implode(',', $competencies);

            }

            return $trainingprogramlist;

    }


    public function insert_offering_session_record($sessionid,$offeringid,$sessiontime,$trainingid,$courseid) {
        global $DB, $PAGE, $OUTPUT, $USER;

        $tposdata = new stdClass();

        $tposdata->sessionid = $sessionid;
        $tposdata->offeringid = $offeringid;
        $tposdata->sessiondate = $sessiontime;
        $tposdata->programid = $trainingid;
        $tposdata->courseid = $courseid;
        $tposdata->timecreated = time();
        $tposdata->usercreated = $USER->id;

        try{
            $createofferingsession =  $DB->insert_record('offering_sessions', $tposdata); 
        return $createofferingsession;
        } catch(dml_exception $e){
            print_r($e);
        }
      
    }

    public function display_lisfof_current_offerings($stable, $filterdata) {
        global $DB, $PAGE, $OUTPUT, $CFG, $USER;
        $systemcontext = context_system::instance();
        $programid = $filterdata->programid;
        $publicstring = get_string('public','local_trainingprogram');
        $privatestring = get_string('private','local_trainingprogram');
        $dedicatedstring = get_string('dedicated','local_trainingprogram');
        $selectsql = "SELECT tpo.id,tpo.code,tpo.halllocation,tpo.halladdress,tpo.startdate,tpo.enddate,tpo.type,tpo.trainingmethod,h.city,tpo.availableseats,tpo.sellingprice,h.name AS hallname,tpo.time,h.maplocation, h.seatingcapacity,h.buildingname,tpo.published,tpo.cancelled,tpo.cancelledby,tpo.cancelledate,tpo.organization,tpo.endtime,tpo.duration,tpo.languages,tpo.financially_closed_status,tpo.tagrement,tpo.tagrrement,
        CASE
            WHEN tpo.type = 0 THEN '$publicstring'
            WHEN tpo.type = 1 THEN '$privatestring'
            ELSE '$dedicatedstring' 
        END AS type
        FROM  {tp_offerings} AS tpo LEFT JOIN {hall} AS h ON
        tpo.halladdress=h.id ";
        $countsql  = "SELECT COUNT(tpo.id) FROM  {tp_offerings} AS tpo LEFT JOIN {hall} AS h ON
        tpo.halladdress=h.id ";
        $formsql = " WHERE tpo.trainingid = $programid ";
        if(isset($filterdata->search_query) && trim($filterdata->search_query) != ''){
            $formsql .= " AND (h.name LIKE :firstnamesearch) ";
            $searchparams = array('firstnamesearch' => '%'.trim($filterdata->search_query).'%');
        } else {
            $searchparams = array();
        }
        $params = array_merge($searchparams);
        $totalofferings = $DB->count_records_sql($countsql.$formsql,$params);
         if($filterdata->sorting == 1) {
        $formsql .=" ORDER BY (tpo.startdate+tpo.time) ASC";
         }
         else{
          $formsql .=" ORDER BY (tpo.startdate+tpo.time) DESC";  
         }
        $offerings = $DB->get_records_sql($selectsql.$formsql, $params, $stable->start,$stable->length);
        $offeringslist = array();
        $count = 0;
        $i=($stable->start+1);
        foreach($offerings as $offering) {
            $programcost = $DB->get_field('local_trainingprogram','price',['id'=>$programid]);
            $courseid = $DB->get_field('local_trainingprogram', 'courseid', array('id' => $programid));
            $traineeroleid = $DB->get_field('role', 'id', array('shortname' => 'trainee'));
            $enrolledtraineessql=" SELECT COUNT(id) FROM {program_enrollments} WHERE programid = $programid AND offeringid = $offering->id AND courseid = $courseid AND roleid = $traineeroleid AND userid IN (SELECT id FROM {user} WHERE deleted = 0 ) AND enrolstatus = 1";
            $programenrolledcount = $DB->count_records_sql($enrolledtraineessql);
            $offeringslist[$count]['coid']=$i++;

            $offeringstartdate = date('Y-m-d',$offering->startdate);
            $currdate=date('Y-m-d'); 

            $offeringstarttime = gmdate("H:i",$offering->time);
            $currenttime = date('H:i');


            $lang= current_language();

            $starttime = gmdate("h:i",$offering->time);
            $starttimemeridian = gmdate('a',$offering->time);
            $endtimemeridian = gmdate('a',($offering->time+$offering->duration));

            if( $lang == 'ar'){
                $programname=$DB->get_field('local_trainingprogram','namearabic',array('id'=>$programid));
            }else{
                $programname=$DB->get_field('local_trainingprogram','name',array('id'=>$programid));
            }
            if( $lang == 'ar'){
                $organizationname=$DB->get_field('local_organization','fullnameinarabic',array('id'=>$offering->organization));
            }else{
                $organizationname=$DB->get_field('local_organization','fullname',array('id'=>$offering->organization));
            }

            $courseid=$DB->get_field('local_trainingprogram','courseid', array('id' => $programid));
            $trainerroleid = $DB->get_field('role', 'id', array('shortname' => 'trainer'));
                
            $alllimitestrainers = 'SELECT count(lu.id) FROM {local_users} as  lu JOIN {program_enrollments} as pe ON lu.userid = pe.userid  WHERE  pe.programid = '.$programid.' AND pe.offeringid = '.$offering->id.' AND pe.courseid = '.$courseid .' AND pe.roleid = '.$trainerroleid.' AND lu.deleted = 0  ';


            $total_trainers_count = $DB->count_records_sql($alllimitestrainers);             
            if( $lang == 'ar'){

                  $limitestrainers = 'SELECT CONCAT(lu.firstnamearabic," ",lu.lastnamearabic) FROM {local_users} as  lu JOIN {program_enrollments} as pe ON lu.userid = pe.userid  WHERE  pe.programid = '.$programid.' AND pe.offeringid = '.$offering->id.' AND pe.courseid = '.$courseid .' AND pe.roleid = '.$trainerroleid.' AND lu.deleted = 0 LIMIT 2 ';

                $alllimitestrainers = 'SELECT CONCAT(lu.firstnamearabic," ",lu.lastnamearabic) FROM {local_users} as  lu JOIN {program_enrollments} as pe ON lu.userid = pe.userid  WHERE  pe.programid = '.$programid.' AND pe.offeringid = '.$offering->id.' AND pe.courseid = '.$courseid .' AND pe.roleid = '.$trainerroleid.' AND lu.deleted = 0 LIMIT '.$total_trainers_count.' OFFSET 2 ';
            }else{

                $limitestrainers = 'SELECT CONCAT(lu.firstname," ",lu.lastname) FROM {local_users} as  lu JOIN {program_enrollments} as pe ON lu.userid = pe.userid  WHERE  pe.programid = '.$programid.' AND pe.offeringid = '.$offering->id.' AND pe.courseid = '.$courseid .' AND pe.roleid = '.$trainerroleid.' AND lu.deleted = 0 LIMIT 2 ';

                $alllimitestrainers = 'SELECT CONCAT(lu.firstname," ", lu.lastname) FROM {local_users} as  lu JOIN {program_enrollments} as pe ON lu.userid = pe.userid  WHERE  pe.programid = '.$programid.' AND pe.offeringid = '.$offering->id.' AND pe.courseid = '.$courseid .' AND pe.roleid = '.$trainerroleid.' AND lu.deleted = 0 LIMIT '.$total_trainers_count.' OFFSET 2 ';
            }

            $limitedtrainers = $DB->get_fieldset_sql($limitestrainers);

            $offeringslist[$count]['limitedtrainers'] = implode(',', $limitedtrainers); 
            $offeringslist[$count]['hastrainernamelistmore'] = (count($limitedtrainers) > 1) ? true : false;
           
            $alllimitedtrainers = $DB->get_fieldset_sql($alllimitestrainers);
            
            $offeringslist[$count]['trainername'] = implode(',', $alllimitedtrainers);
            
            $startmeridian = ($starttimemeridian == 'am')? get_string('am','local_trainingprogram'):get_string('pm','local_trainingprogram');
            $endmeridian = ($endtimemeridian == 'am')? get_string('am','local_trainingprogram'):get_string('pm','local_trainingprogram');
            $offeringslist[$count]['offeringid'] = $offering->id;
            $offeringslist[$count]['tainingprogramname'] = $programname;
            $offeringslist[$count]['code'] = $offering->code;

            if($offering->trainingmethod == 'online') {
                $offeringslist[$count]['offeringmethod'] = get_string('scheduleonline','local_trainingprogram');
                $offeringslist[$count]['datedisplay']  = true;

            } elseif($offering->trainingmethod == 'offline') {

                $offeringslist[$count]['offeringmethod'] = get_string('scheduleoffline','local_trainingprogram');
                $offeringslist[$count]['datedisplay']  = true;

            } else {

                $offeringslist[$count]['offeringmethod'] = get_string('scheduleelearning','local_trainingprogram');
                $offeringslist[$count]['datedisplay']  = false;
            }

            $offeringslist[$count]['availableseats'] = $offering->availableseats;
            $offeringslist[$count]['buildingname'] = $offering->buildingname;
            //renu
            
            if($offering->halladdress== 0  && $offering->halllocation=='clientheadquarters'){
                $offeringslist[$count]['hallname'] = "At the Client's headquarters";
            }
           
            else{
                $offeringslist[$count]['hallname'] = $offering->hallname;
            }
            $offeringslist[$count]['city'] = $offering->city = 1 ?'Riyad':$offering->city;
            $offeringslist[$count]['sellingprice'] = ($programcost > 0) ? round($offering->sellingprice,2) : 0;
            $offeringslist[$count]['maplocation'] = $offering->maplocation;
            $offeringslist[$count]['enrolled']=$programenrolledcount ? $programenrolledcount : 0;
            $offeringslist[$count]['deleteaction'] = $programenrolledcount > 0 ? true :false; 
            $offeringslist[$count]['halldisplayaction'] = ($offering->trainingmethod == 'offline' || $offering->trainingmethod == 'online') ? true :false; 

            $offeringslist[$count]['privateorg'] =  ($offering->type == 'Private' || $offering->type == 'خاص' );           
                                               
            $offeringslist[$count]['orgname'] = $organizationname ? $organizationname : 'N/A' ;
            $offeringslist[$count]['sessiondatadisplay'] = ($offering->trainingmethod == 'offline' || $offering->trainingmethod == 'online') ? true :false; 
            $offeringslist[$count]['offeringdeleteaction'] = (is_siteadmin() || has_capability('local/organization:manage_trainingofficial',$systemcontext))? true :false; 
            $offeringslist[$count]['programid']=$programid;
            $offeringslist[$count]['type']=$offering->type;
            $offeringslist[$count]['time'] = gmdate("H:i:s", $offering->time);
            $offeringslist[$count]['endtime'] = gmdate("H:i:s", ($offering->time + $offering->duration));
            $offeringslist[$count]['startdate']  = userdate($offering->startdate, get_string('strftimedaydate', 'langconfig')).' '.gmdate("h:i",$offering->time).' '.$startmeridian;
            $offeringslist[$count]['enddate'] = userdate($offering->enddate, get_string('strftimedaydate', 'langconfig')).' '.gmdate("h:i",($offering->time+$offering->duration)).' '.$endmeridian;
            $offeringslist[$count]['offeringenroll_edit_action'] = (($offeringstartdate > $currdate || ($offeringstartdate  ==  $currdate &&  $offering->time > 0 &&  $offeringstarttime >= $currenttime )) && is_siteadmin() || has_capability('local/organization:manage_trainingofficial',$systemcontext)) ? true : false ;

            $topssql = "SELECT ofs.sessionid, ats.attendanceid,ats.sessdate,ofs.courseid,ofs.programid 
                            FROM {offering_sessions} AS ofs 
                        JOIN {attendance_sessions} AS ats ON ofs.sessionid = ats.id 
                            WHERE ofs.offeringid =:offeringid";

            $tpofferingssessions = $DB->get_records_sql($topssql, ['offeringid' => $offering->id]);
            $k=1;
            foreach ($tpofferingssessions AS $tpofferingssession) {
                $tpofferingssession->cid=$k++;
                $attendanceid = $tpofferingssession->attendanceid;
                $moduleid=$DB->get_field('course_modules','id',  array ('instance' =>$attendanceid,'course' => $tpofferingssession->courseid));
                $programid=$DB->get_field_sql("SELECT lt.id FROM {local_trainingprogram} lt JOIN {course_modules} cm ON cm.course = lt.courseid WHERE cm.instance = ('".$attendanceid."') AND cm.course = ('".$tpofferingssession->courseid."')");
                $reportid = $DB->get_field('block_learnerscript', 'id', ['type' => 'meetingparticipants']);

                $virtualsectionid=$DB->get_field('course_modules','section',  array ('instance' =>$attendanceid,'course' => $tpofferingssession->courseid));
                $virtualmoduleid = $DB->get_field_sql("SELECT cm.id FROM {course_modules} cm JOIN {modules} m ON m.id = cm.module 
                    WHERE m.name IN ('zoom', 'webexactivity', 'teamsmeeting') AND cm.section = $virtualsectionid ");
                
                $webexactivity = $DB->get_field_sql("SELECT m.name FROM {course_modules} cm JOIN {modules} m ON m.id = cm.module 
                WHERE m.name IN ('zoom', 'webexactivity', 'teamsmeeting') AND cm.section = $virtualsectionid ");


            $virtual = $DB->record_exists('tp_offerings', ['id' => $offering->id, 'trainingmethod' => 'online']);

            if($virtual){
                    $tpofferingssession->virtual = 1;
                    $tpofferingssession->url = $CFG->wwwroot.'/blocks/learnerscript/viewreport.php?id='.$reportid.'&filter_trainingprogram='.$programid.'&filter_code='.$offering->id.'&filter_meetingdates='.$tpofferingssession->sessdate.'&filter_virtualactivities='.$virtualmoduleid;
                    $tpofferingssession->attendanceurl = $CFG->wwwroot.'/mod/attendance/manage.php?id='.$moduleid;

                    $filters[] = ['reportid' => $reportid, 'reporttype' => 'table', 'instanceid' => $reportid, 'reportdashboard' => false, 'filter_trainingprogram' => $programid, 'filter_code' => $offering->id, 'filter_meetingdates' => $tpofferingssession->sessdate, 'filter_virtualactivities' => $virtualmoduleid];
                    $tpofferingssession->filters = json_encode($filters);
            }else{
                    $tpofferingssession->virtual = null;
                    $tpofferingssession->attendanceurl = $CFG->wwwroot.'/mod/attendance/manage.php?id='.$moduleid;
                    $tpofferingssession->url = $CFG->wwwroot.'/mod/webexactivity/view.php';
                    $filters = [];
                    $tpofferingssession->filters = json_encode($filters);
            }
                $tpofferingssession->sessiondate = date('jS F Y',$tpofferingssession->sessdate);
            } 
            if(!empty($tpofferingssessions)){
                $offeringslist[$count]['sessiondata'] = array_values($tpofferingssessions);
            } else {
                $offeringslist[$count]['sessiondata'] = array();
            }
            $offeringslist[$count]['published'] = $offering->published == 0 ? true :false; 
            $currentuserroleinfo = $DB->get_record_sql('SELECT rol.* FROM {role} rol 
            JOIN {role_assignments} rola ON rola.roleid = rol.id
            WHERE rola.userid =:userid and contextid =:contextid',['userid'=>$USER->id,'contextid'=>$systemcontext->id]);
            $offeringslist[$count]['currentuser'] =(is_siteadmin()) ? 'admin' : $currentuserroleinfo->shortname;
            $offeringslist[$count]['costtype'] =($programcost > 0 && $offering->sellingprice > 0) ? 1 : 0;
            $offeringslist[$count]['assignurl'] =$CFG->wwwroot.'/local/trainingprogram/program_trainee_trainer_view.php?programid='.$programid.'&offeringid='.$offering->id;
            $offeringslist[$count]['productid'] = (int)$DB->get_field_sql('SELECT id FROM {tool_products} WHERE referenceid = '.$offering->id.' AND category = 1');
            $offeringslist[$count]['cancelledstatus'] = $offering->cancelled;
            $offeringslist[$count]['iscancelled'] = ($offering->cancelled == 2) ? true:false;
            $offeringslist[$count]['cancelledstatustext'] = ($offering->cancelled == 2) ? get_string('cancelled','local_trainingprogram'):  (($offering->cancelled == 1 || $offering->cancelled == -1) ? get_string('cancelrequestpending','local_trainingprogram') : get_string('cancelrequestrejected','local_trainingprogram'));
            $offeringenddatetime = ($offering->enddate+$offering->time+$offering->duration);
            $currtime = time();
            $offeringslist[$count]['cancelledrequestpending'] = ($offering->cancelled == 1 || $offering->cancelled == -1) ? true:false;

            $hasenrollments = (new trainingprogram)->offeringhasenrollments($offering->id);
            $offeringslist[$count]['offeringcancelaction'] = (is_siteadmin() || has_capability('local/organization:manage_trainingofficial',$systemcontext) && (($offering->cancelled == 0 || $offering->cancelled == 3) && ($offering->trainingmethod == 'elearning' || $offeringenddatetime > $currtime))) ? true: false;
            $offeringslist[$count]['issiteadmin'] =(is_siteadmin() || (has_capability('local/organization:manage_trainingofficial',$systemcontext) && (($offering->startdate+$offering->time) > time()))) ? true : false;
            $offeringslist[$count]['offeringhasenrollments']=$hasenrollments;
            $offeringslist[$count]['hasenrollments']=($hasenrollments) ? 1 : 0;
            $offactuallang=($offering->languages == '1') ? get_string('english','local_trainingprogram') : get_string('arabic','local_trainingprogram');
            $offeringslist[$count]['offcourselanguage']=$offactuallang;
            $offeringslist[$count]['trainertype'] = false;
            $offeringslist[$count]['trainerprovider'] = '';
            $offeringslist[$count]['trainerproviderlogo'] ='';
            $coffering=$DB->get_records_sql("SELECT toff.trainertype,org.orglogo,org.fullname FROM {tp_offerings} toff JOIN {local_organization} org ON toff.trainerorg=org.id WHERE toff.trainingid =$programid AND toff.id =$offering->id");
            foreach($coffering as $cc){
                if($cc->trainertype) {
                    if($cc->trainertype ==1){
                        if(!empty($cc->orglogo)){
                            $sql = "SELECT * FROM {files} WHERE itemid = $cc->orglogo AND component = 'local_organization'  AND filearea='orglogo' AND filename != '.'";
                            $logorecord = $DB->get_record_sql($sql);

                            if (!empty($logorecord)) {
                                $logourl = moodle_url::make_pluginfile_url($logorecord->contextid, $logorecord->component,
                                $logorecord->filearea, $logorecord->itemid, $logorecord->filepath,
                                $logorecord->filename);
                                $logourl = $logourl->out();
                            }
                                    
                            $offeringslist[$count]['trainerproviderlogo'] =  $logourl;
                        }
                     $offeringslist[$count]['trainerprovider'] = $cc->fullname;
                    }
                    if($cc->trainertype ==0){
                       $offeringslist[$count]['trainerprovider'] = 'Individual';  
                    }

                    $offeringslist[$count]['trainertype'] = true;
                }
            
            }
            $evaluationmethods = $DB->get_field('local_trainingprogram','evaluationmethods',array('id'=>$programid));
            $offeringslist[$count]['todoactivities'] = ($offering->trainingmethod != 'elearning' || $evaluationmethods)?true:false;
            $offeringslist[$count]['evaluationmethods'] = ($evaluationmethods || $evaluationmethods == '0')?true:false;
            $offeringslist[$count]['isfinancialmanager']=(!is_siteadmin() && has_capability('local/organization:manage_financial_manager',$systemcontext)) ? true : false; 
            $offeringslist[$count]['isfinanciallyclosed']=((int)$offering->financially_closed_status == 1) ? true : false; 
            $offeringslist[$count]['canupdatefinanciallstatus']=($offering->trainingmethod != 'elearning') ? true : false; 
            $offeringslist[$count]['canupdatefinanciallstatus']=($offering->trainingmethod != 'elearning') ? true : false; 
            $offeringslist[$count]['onlyadmin']=(is_siteadmin()) ? true : false; 
            $offeringslist[$count]['hastagreement']=($offering->tagrement == 1) ? true:false;
            $offeringslist[$count]['agreementurl']=($offering->tagrement == 1) ? training_agreement_url($offering->tagrrement) : '';
            $count++;
        }
        $coursesContext = array(
            "currentofferings" => $offeringslist,
            "totalofferings" => $totalofferings,
            "length" => count($offeringslist),
        );
        return $coursesContext;
    }

    public function offeringhasenrollments($offeringid) {
        global $DB;
        $traineeroleid = $DB->get_field('role', 'id', array('shortname' => 'trainee'));
        $sql = 'SELECT pe.id FROM {program_enrollments} as pe 
                  JOIN {tp_offerings} as tpo ON pe.offeringid=tpo.id AND pe.programid = tpo.trainingid
                 WHERE tpo.id=:offeringid AND pe.roleid =:traineeroleid   ';
        $enrolled = $DB->record_exists_sql($sql, ['offeringid' => $offeringid,'traineeroleid' => $traineeroleid]);
        if($enrolled){
            return true;
        }
        return false;

    }
    public function delete_session_record($sessionid) {
        global $DB;

        $DB->delete_records('attendance_sessions',array('id' =>$sessionid));
        $DB->delete_records('offering_sessions',array('sessionid' =>$sessionid));

    }

    public function reserveofferingseats($data) {
        global $DB,$OUTPUT,$PAGE;

        $renderer = $PAGE->get_renderer('local_trainingprogram');
        $renderer->seat_summery($data);

    } 
    public static function constcompetencytypes() {

        $competencytypes = array(
            self::CORECOMPETENCIES => get_string('corecompetencies','local_competency'),
            self::TECHNICALCOMPETENCIES => get_string('technicalcompetencies','local_competency'),
            self::BEHAVIORALCOMPETENCIES => get_string('behavioralcompetencies','local_competency')
            );
        return $competencytypes;
    }

    public static function constcompetency_types() {
       global $DB,$OUTPUT,$PAGE;

        $competencytypes = self::constcompetencytypes();

        list($relatedcompetencytypessql, $relatedcompetencytypesparams) = $DB->get_in_or_equal(array_flip($competencytypes), SQL_PARAMS_NAMED, 'competencytypes',false);
        $params = $relatedcompetencytypesparams;
        $sql = "SELECT cmt.type,cmt.type as fullname FROM {local_competencies} AS cmt
                WHERE cmt.type $relatedcompetencytypessql ";
        if (!empty($searchparams)) {
            $sql .= " AND (cmt.type LIKE :search )";
            $params['search'] = '%' . $searchparams . '%';
        }
        $competencietypes=$DB->get_records_sql_menu($sql,$params);
    

        return array_merge($competencytypes,$competencietypes);
    }
    public function get_listof_coupons($stable, $filterdata) {
        global $DB;
        $systemcontext = context_system::instance();
        $selectsql = "SELECT coupon.id,coupon.code,coupon.number_of_codes,
        coupon.discount,coupon.coupon_created_date,
        coupon.coupon_expired_date,coupon.coupon_status
        FROM {coupon_management} AS coupon "; 
        $countsql  = "SELECT COUNT(coupon.id)
        FROM {coupon_management}  AS coupon ";
         $formsql = " WHERE 1=1 ";
        if  (isset($filterdata->search_query) && trim($filterdata->search_query) != ''){
            $formsql .= " AND (coupon.code LIKE :codesearch) ";
            $searchparams = array('codesearch' => '%'.trim($filterdata->search_query).'%');
        } else {
            $searchparams = array();
        }
        if (!empty($filterdata->couponstatus)){
            if(in_array('1', explode(',', $filterdata->couponstatus)) && in_array('2', explode(',', $filterdata->couponstatus)) && in_array('3', explode(',', $filterdata->couponstatus))) {
                $formsql .= " ";
            }elseif($filterdata->couponstatus == 1) {
                $live =(new trainingprogram())->from_unixtime_for_live_entities('coupon.coupon_expired_date');
                $formsql .= " AND $live ";
            } elseif($filterdata->couponstatus == 2) {
                $expired =(new trainingprogram())->from_unixtime_for_expired_entities('coupon.coupon_expired_date');
                $formsql .= " AND $expired ";
            } elseif($filterdata->couponstatus == 3) {

                $formsql .= " AND coupon_status = 2 ";
            }
        }
        if($filterdata->{'expired_date[enabled]'} == 1 ){
            $start_year = $filterdata->{'expired_date[year]'};
            $start_month = $filterdata->{'expired_date[month]'};
            $start_day = $filterdata->{'expired_date[day]'};
            $filter_starttime_con = mktime(0,0,0, $start_month, $start_day, $start_year);
            $formsql.= " AND coupon.coupon_expired_date <= '$filter_starttime_con' ";
        }
        $params = array_merge($searchparams);
        $totalcoupons = $DB->count_records_sql($countsql.$formsql,$params);
        $formsql .=" ORDER BY coupon_created_date DESC";
        $coupons = $DB->get_records_sql($selectsql.$formsql, $params, $stable->start,$stable->length);
        $couponslist = array();
        $count = 0;
        foreach($coupons as $coupon) {
            $couponslist[$count]["id"] = $coupon->id;
            $couponslist[$count]["code"] =$coupon->code;
            $couponslist[$count]["number_of_codes"] =$coupon->number_of_codes;
            $couponslist[$count]["discount"] =$coupon->discount;
            $couponslist[$count]["coupon_created_date"] = userdate($coupon->coupon_created_date,get_string('strftimedatemonthabbr', 'core_langconfig'));
            $couponslist[$count]["coupon_expired_date"] = userdate($coupon->coupon_expired_date,get_string('strftimedatemonthabbr', 'core_langconfig'));
            $coupon_expired_date = date('Y-m-d',$coupon->coupon_expired_date);
            $currdate=date('Y-m-d'); 
            if ($coupon->coupon_status == 2) {
                $couponslist[$count]["coupon_status"] = get_string('applied', 'local_trainingprogram');
                $couponslist[$count]["deletecouponview"] = false;
            }else if ($coupon_expired_date >= $currdate && $coupon->coupon_status == 1) {
               $couponslist[$count]["coupon_status"] = get_string('available', 'local_trainingprogram');
               $couponslist[$count]["deletecouponview"] = false;
            } else {
                $couponslist[$count]["coupon_status"]=  get_string('expired', 'local_trainingprogram');
                $couponslist[$count]["deletecouponview"] = true;
            }
            $couponslist[$count]["caneditcoupon"]= ($couponslist[$count]["coupon_status"] == 'Available' ||  $couponslist[$count]["coupon_status"] == 'Expired')? true : false ;

            $couponslist[$count]["actionview"]= ($coupon_expired_date >= $currdate && $coupon->coupon_status == 1)? true : false ;
            $count++;
        }
        $coursesContext = array(
            "hascourses" => $couponslist,
            "totalcoupons" => $totalcoupons,
            "length" => count($couponslist)
        );
        return $coursesContext;
    }

    public function create_update_coupon($sdata){
        global $DB, $USER,$PAGE;
        $renderer = $PAGE->get_renderer('local_trainingprogram'); 
        $data = new stdClass;
        $data->number_of_codes = $sdata->number_of_codes;
        $data->discount = $sdata->discount;
        $data->coupon_created_date =time();
        $data->coupon_expired_date = $sdata->coupon_expired_date;
        $data->programs = (!empty($sdata->programs)) ? ((is_array($sdata->programs)) ?  implode(',', array_filter($sdata->programs)) : $sdata->programs) : null;
        $data->exams =  (!empty($sdata->exams)) ? ((is_array($sdata->exams)) ?  implode(',', array_filter($sdata->exams)) : $sdata->exams) : null;
        $data->events = (!empty($sdata->events)) ? ((is_array($sdata->events)) ?  implode(',', array_filter($sdata->events)) : $sdata->events) : null;
        if($sdata->id > 0) {
            $data->id = $sdata->id;
            $data->code = $sdata->code;
            $data->timemodified = time();
            $data->usermodified = $USER->id;
            $DB->update_record('coupon_management', $data);

        } else {

            for ($i = 0; $i < $sdata->number_of_codes; $i++) {
                $coupon_code = $renderer->generateCouponCode(15);
                if($DB->record_exists('coupon_management',array('code'=>$coupon_code))) {
                    $data->code = $coupon_code.'F'; 
                } else {
                    $data->code = $coupon_code; 
                }
                $data->timecreated = time();
                $data->usercreated = $USER->id;
                $DB->insert_record('coupon_management', $data);
            }

        }
        
        
    }
    public function send_coupon_mail($data){
        global $DB, $USER, $CFG,$PAGE;
        $logrecord   = new stdClass(); 
        $result=new stdClass();
        $code = $data->couponcode;
        if($data->usertype == 0){
            $userdata = $DB->get_record('user',array('id'=>$data->internaluser));
            $to = $userdata->email;
            $firstname = $userdata->firstname;
            $lastname = $userdata->lastname;
            $fullname=$userdata->firstname.' '.$userdata->lastname;
        } else {
            $to = $data->email;
            $firstname = $data->name;
            $lastname = $data->name;
            $fullname=$data->name;
        }
        $result->name = $fullname;
        $result->code = $code;
        $subject = 'Here is your coupon code!';
        $message = get_string('coupon_email_body', 'local_trainingprogram',$result);
        $logrecord->email = $to;
        $logrecord->subject = $to;
        $logrecord->message = $message;
        $logrecord->code = $code;
        $logrecord->timecreated  = time();
        $logrecord->usercreated  = $USER->id;
        try{
           $insertlogrecord = $DB->insert_record('coupon_management_emaillogs', $logrecord);
        } catch(moodle_exception $e){
          print_r($e);
        }
        $touser=new stdClass();
        $touser->email = $to;
        $touser->firstname = $firstname;
        $touser->lastname = $lastname;
        $touser->maildisplay = true;
        $touser->mailformat = 1; // 0 (zero) text-only emails, 1 (one) for HTML/Text emails.
        $touser->id = -99;
        $touser->firstnamephonetic = '';
        $touser->lastnamephonetic = '';
        $touser->middlename = '';
        $touser->alternatename = '';
        $fromuser = core_user::get_support_user();
        try{
            $sendmail = email_to_user($touser, $fromuser, $subject, $message);
            return $sendmail;
        } catch(moodle_exception $e){
          print_r($e);
        }
        
    }
    public function ordinal_number($number) {
        $ends = array('th','st','nd','rd','th','th','th','th','th','th');
        if ((($number % 100) >= 11) && (($number%100) <= 13)) {
            return $number. 'th';
        } else {
            return $number. $ends[$number % 10];
        }
   }

    public function get_listof_competencytypes($query = null,$level = null) {
        global $DB;
        if(!empty($level))
        {
            $sql = "SELECT lc.type as id, lc.type as fullname
                  FROM {local_competencies} as lc 
                  WHERE  FIND_IN_SET('$level', lc.level) ";
        }else
        {
             $sql = "SELECT lc.type as id, lc.type as fullname
                  FROM {local_competencies} lc WHERE 1=1";
        }
    if($query!="") {
        $searchquery = trim($query);
        $sql .= " AND lc.type LIKE '%$searchquery%'";
    }
    $competenciestypesdata = $DB->get_records_sql($sql);
    $returndata = array();
    $constcompetencytypes = tp::constcompetencytypes();
    if(!empty($competenciestypesdata) && empty($query))
    {
         foreach ($constcompetencytypes AS $key =>$constcompetencytype) {
            $returndata[] = (object)array('id'=> $key, 'fullname' =>$constcompetencytype);
        }
        foreach ($competenciestypesdata AS $competenciestype) {

            if(!array_key_exists($competenciestype->fullname,$constcompetencytypes)) {

                $returndata[] = (object)array('id'=> $competenciestype->fullname, 'fullname' =>$competenciestype->fullname);
            } 
        
        }
    }
    elseif(!empty($competenciestypesdata) && !empty($query))
    {
        foreach ($competenciestypesdata AS $competenciestype) {
           
            if(array_key_exists($competenciestype->fullname,$constcompetencytypes)) {

                switch($competenciestype->fullname)
                {
                 case 'corecompetencies': 
                 $fullname = get_string('corecompetencies','local_trainingprogram');
                 break;
                 case 'technicalcompetencies':
                 $fullname =get_string('technicalcompetencies','local_trainingprogram');
                 break;
                 case 'behavioralcompetencies':
                 $fullname =get_string('behavioralcompetencies','local_trainingprogram');
                 default:
                $fullname = $competenciestype->fullname;
                }
                $returndata[] = (object)array('id'=> $competenciestype->fullname, 'fullname' =>$fullname);
            }
            elseif(!array_key_exists($competenciestype->fullname,$constcompetencytypes) && $competenciestype->fullname) 
            {
               $fullname = $competenciestype->fullname;
               $returndata[] = (object)array('id'=> $competenciestype->fullname, 'fullname' =>$fullname); 
            }
        
        }
    }
        $return = array_values(json_decode(json_encode(($returndata)), true));
        return $return;
    }
    public function get_listof_levels($query = null) {
        global $DB;

        $currentlang= current_language();

        if( $currentlang == 'ar'){
            $sql = " SELECT ljbl.level as id, REPLACE(ljbl.level,'Level ','مستوى ') as fullname 
                    FROM {local_jobrole_level} ljbl ";

        } else {

            $sql = " SELECT ljbl.level as id, ljbl.level as fullname 
                    FROM {local_jobrole_level} ljbl ";

        }    
        
        $data = $DB->get_records_sql($sql);
        $return = array_values(json_decode(json_encode(($data)), true));

        return $return;
    }
    public function remove_coupon($couponid, $couponcode){
        global $DB, $USER, $CFG;
        try{
            $remove_coupon=$DB->delete_records('coupon_management',array ('id' =>$couponid, 'code' =>$couponcode));
            return $remove_coupon;
        } catch(moodle_exception $e){
          print_r($e);
        } 
        
    } 

    public function add_update_program_agenda($data){
        global $DB, $USER, $CFG;
        $days = $data->days;
        $programid = $data->programid;
        $sql ="SELECT id 
                FROM {program_agenda}
                WHERE programid = $programid";
        $current_program_agenda_lists = $DB->get_records_sql($sql);
        foreach($current_program_agenda_lists AS $agenda) {
            (new trainingprogram)->delete_existing_agenda($agenda->id);
        }
        if($days > 0) {
            for ($i = 1; $i <= $days; $i++) {
                $description = $data->{"description$i"}['text'];
                if(!empty($description)) {
                    $data->day = $i;
                    $data->programid = $programid;
                    $data->description = $description;
                    $data->usercreated = $USER->id;
                    $data->timecreated = time();
                    $DB->insert_record('program_agenda', $data);
                }
            }
        }
    }
    public function delete_existing_agenda($id) {
        global $DB;
        $DB->delete_records('program_agenda',array('id' =>$id));
    } 
 
    public static function get_sector_by_jobroleid($jobroleid) {

        global $DB, $USER;

        if(!empty($jobroleid)){

          $sector= $DB->get_record_sql('SELECT sect.id as sectorid,seg.id as segmentid,jbfm.id as jobfamilyid
                                            FROM {local_sector} as sect 
                                            JOIN {local_segment} as seg ON seg.sectorid=sect.id 
                                            JOIN  {local_jobfamily} as jbfm ON jbfm.segmentid=seg.id 
                                            JOIN {local_jobrole_level} as jbrl ON jbrl.jobfamily=jbfm.id 
                                            JOIN {local_competencies} as cmtc ON FIND_IN_SET(jbrl.id,cmtc.jobroleid) > 0
                                                 WHERE cmtc.jobroleid IN ($jobroleid)');

        }else{

            $sector= $DB->get_records_sql_menu("SELECT id,CONCAT(code,' ',title) as title FROM {local_sector}");
        }


        return $sector;
            
    }

    public function get_listof_earlyregistrations($stable, $filterdata) {
        global $DB, $PAGE, $OUTPUT, $CFG, $USER;
        $systemcontext = context_system::instance();
        $selectsql = "SELECT erstn.id,erstn.days,
        erstn.discount,erstn.earlyregistration_created_date,
        erstn.earlyregistration_expired_date,erstn.earlyregistration_status
        FROM {earlyregistration_management} AS erstn "; 
        $countsql  = "SELECT COUNT(erstn.id)
        FROM {earlyregistration_management}  AS erstn ";
        $formsql = " WHERE 1=1 ";
        if  (isset($filterdata->search_query) && trim($filterdata->search_query) != ''){
            $formsql .= " AND erstn.days LIKE :dayssearch ";
            $searchparams = array(
                'dayssearch' => '%'.trim($filterdata->search_query).'%',
            );
        } else {
            $searchparams = array();
        }
        if (!empty($filterdata->earlyregistrationstatus)){

            $currentdate = strtotime(date('Y-m-d'));

            if( in_array('1', explode(',', $filterdata->earlyregistrationstatus)) && in_array('2', explode(',', $filterdata->earlyregistrationstatus))) {
                $formsql .= " ";
            }elseif($filterdata->earlyregistrationstatus == 1) {

                $formsql .= " AND earlyregistration_expired_date  >= $currentdate  AND earlyregistration_status = 1 ";

            } elseif($filterdata->earlyregistrationstatus == 2) {

                $formsql .= " AND earlyregistration_expired_date  <  $currentdate ";

            }

        }
        if($filterdata->{'expired_date[enabled]'} == 1 ){
            $start_year = $filterdata->{'expired_date[year]'};
            $start_month = $filterdata->{'expired_date[month]'};
            $start_day = $filterdata->{'expired_date[day]'};
            $filter_starttime_con = mktime(0,0,0, $start_month, $start_day, $start_year);
            $formsql.= " AND erstn.earlyregistration_expired_date <= '$filter_starttime_con' ";
        }
        $params = array_merge($searchparams);

        $totalearlyregistrations = $DB->count_records_sql($countsql.$formsql,$params);
        $formsql .=" ORDER BY erstn.id DESC";
        $earlyregistrations = $DB->get_records_sql($selectsql.$formsql, $params, $stable->start,$stable->length);
        $earlyregistrationslist = array();
        $count = 0;
        foreach($earlyregistrations as $earlyregistration) {
            $earlyregistrationslist[$count]["id"] = $earlyregistration->id;
            $earlyregistrationslist[$count]["days"] =$earlyregistration->days;
            $earlyregistrationslist[$count]["discount"] =$earlyregistration->discount;
            $earlyregistrationslist[$count]["earlyregistration_created_date"] = userdate($earlyregistration->earlyregistration_created_date,get_string('strftimedatemonthabbr', 'core_langconfig'));
            $earlyregistrationslist[$count]["earlyregistration_expired_date"] =  userdate($earlyregistration->earlyregistration_expired_date,get_string('strftimedatemonthabbr', 'core_langconfig'));;
            $earlyregistration_expired_date = date('Y-m-d',$earlyregistration->earlyregistration_expired_date);
            $currdate=date('Y-m-d'); 
            if ($earlyregistration_expired_date >= $currdate && $earlyregistration->earlyregistration_status == 1) {
               $earlyregistrationslist[$count]["earlyregistration_status"] = get_string('available', 'local_trainingprogram');
            } else {
                $earlyregistrationslist[$count]["earlyregistration_status"]=  get_string('expired', 'local_trainingprogram');
            }
            $earlyregistrationslist[$count]["deleteearlyregistrationview"] = true;
            $earlyregistrationslist[$count]["actionview"]=  true  ;
            $count++;
        }
        $coursesContext = array(
            "hascourses" => $earlyregistrationslist,
            "totalearlyregistrations" => $totalearlyregistrations,
            "length" => count($earlyregistrationslist)
        );
        return $coursesContext;
    }

    public function create_update_earlyregistration($sdata){
        global $DB, $USER;
        $data = new stdClass();
        $data->days = $sdata->days;
        $data->discount = $sdata->discount;
        $data->earlyregistration_created_date =time();
        $data->earlyregistration_expired_date = $sdata->earlyregistration_expired_date;
        $data->programs = (!empty($sdata->programs)) ? ((is_array($sdata->programs)) ?  implode(',', array_filter($sdata->programs)) : $sdata->programs) : null;
        $data->exams =  (!empty($sdata->exams)) ? ((is_array($sdata->exams)) ?  implode(',', array_filter($sdata->exams)) : $sdata->exams) : null;
        $data->events = (!empty($sdata->events)) ? ((is_array($sdata->events)) ?  implode(',', array_filter($sdata->events)) : $sdata->events) : null;
        if($sdata->id > 0){
            $data->id = $sdata->id;
            $data->usermodified = $USER->id;
            $data->timemodified =time();
            $DB->update_record('earlyregistration_management', $data);

        }else{

            $data->usercreated = $USER->id;
            $data->timecreated =time();
            $DB->insert_record('earlyregistration_management', $data);

        }

        
    }
    public function remove_earlyregistration($earlyregistrationid,$days,$discount){
        global $DB;
        try{
            $remove_earlyregistration=$DB->delete_records('earlyregistration_management',array ('id' =>$earlyregistrationid,'days' =>$days,'discount' =>$discount));
            return $remove_earlyregistration;
        } catch(moodle_exception $e){
          print_r($e);
        } 
        
    } 

    public function enrol_program($add, $offeringid,$programid,$offering){
        global $OUTPUT, $PAGE, $DB;
        $type = 'program_enrol';
        $roleid = $DB->get_field('role', 'id', ['shortname' => 'trainee']);
        $availableseats = $this->get_available_seats($offeringid);

        $userstoassign = $add;
        $program= $DB->get_record_sql('SELECT * FROM {local_trainingprogram} as tp 
                                         JOIN {tp_offerings} as tpo ON tpo.trainingid = tp.id 
                                        WHERE tpo.id=:offeringid', ['offeringid' => $offeringid]);

        $course= $DB->get_record('course', ['id' => $program->courseid], '*', MUST_EXIST);
        $groupid = $DB->get_field_sql('SELECT g.id FROM {groups} as g JOIN {tp_offerings} as tpo on g.idnumber=tpo.code where tpo.id=:id',array('id'=>$offeringid));
        if(sizeof($userstoassign) > $availableseats) {
            echo "<div class='alert alert-info'>".get_string('userscountismore', 'local_exams', $availableseats)."</div>";
        } else {
            if (!empty($userstoassign)) {
                $progress = 0;
                $progressbar = new \core\progress\display_if_slow(get_string('enrollusers', 'local_trainingprogram',$course->fullname));
                $progressbar->start_html();
                $progressbar->start_progress('',count($userstoassign)-1);

                foreach($userstoassign as $key=>$adduser){

                  $progressbar->progress($progress);
                  $progress++;

                    (new \local_trainingprogram\local\trainingprogram)->program_enrollment($offeringid,$adduser);

                    if($offering->type == 2){

                        (new learningtracks)->program_enrolment($programid, $offeringid, $roleid, $adduser);
                    }

                }

                $progressbar->end_html();
                $result=new stdClass();
                $result->changecount=$progress;
                $result->course=format_text($course->fullname,FORMAT_HTML); 
                return $result;
            }                
        }
    }

    public function unenrol_program($remove,$offeringid,$programid,$offering) {
        global $DB, $OUTPUT, $PAGE;
        $systemcontext = context_system::instance();
        $roleid = $DB->get_field('role', 'id', ['shortname' => 'trainee']);
        $userstounassign = $remove;
        $program= $DB->get_record_sql('local_trainingprogram',['id' => $programid]);
        $course= $DB->get_record('course', ['id' => $program->courseid], '*', MUST_EXIST);     
        if (!empty($userstounassign)) {
            $progress = 0;
            $progressbar = new \core\progress\display_if_slow(get_string('un_enrollusers', 'local_trainingprogram',$course->fullname));
            $progressbar->start_html();
            $progressbar->start_progress('', count($userstounassign)-1);
            foreach($userstounassign as $key=>$removeuser){
                $progressbar->progress($progress);
                $progress++;
                $this->program_unenrollment($programid,$offeringid,$program->courseid,$removeuser,$roleid);
                if($offering->type == 2){
                    (new learningtracks)->program_unenrol($programid, $offeringid, $roleid, $removeuser);
                }
            }
            $progressbar->end_html();
            $result=new stdClass();
            $result->changecount=$progress;
            $result->course=format_text($course->fullname,FORMAT_HTML); 
            echo $OUTPUT->notification(get_string('unenrolluserssuccess', 'local_trainingprogram',$result),'success');
            $button = new single_button($PAGE->url, get_string('click_continue','local_trainingprogram'), 'get', true);
            $button->class = 'continuebutton';
            echo $OUTPUT->render($button);
            die();
        }
    }

    public function get_available_seats($offeringid) {
        global $DB,$USER;

        $offeringid = (int) $offeringid;
        $systemcontext = context_system::instance();
        $traineeeid = $DB->get_field('role', 'id', array('shortname' => 'trainee'));
        $orgofficialroleid = $DB->get_field('role', 'id', array('shortname' => 'organizationofficial'));


        $offering= $DB->get_record('tp_offerings',['id'=>$offeringid]);

        $programprice =(int) $DB->get_field_sql('SELECT ltp.price FROM {local_trainingprogram} ltp 
                                                   JOIN {tp_offerings} tpo ON tpo.trainingid = ltp.id 
                                                   WHERE tpo.id = '.$offeringid.''
                                               );
        
        $totalseatssql = " SELECT  tp.availableseats AS total
                                     FROM {tp_offerings} tp
                                    WHERE tp.id = $offeringid";
        
        $total = $DB->get_field_sql($totalseatssql);
          if(!isloggedin() || (isloggedin() && (is_siteadmin() || has_capability('local/organization:manage_trainingofficial',$systemcontext) || has_capability('local/organization:manage_competencies_official',$systemcontext)  || has_capability('local/organization:manage_communication_officer',$systemcontext) || has_capability('local/organization:manage_trainee',$systemcontext)) || !$DB->record_exists('role_assignments',array('contextid'=>$systemcontext->id,'userid'=>$USER->id)))) {

            if(!is_siteadmin() && has_capability('local/organization:manage_trainee',$systemcontext)) {

                $enrolled = $DB->get_field_sql(" SELECT count(pe.userid) AS total
                              FROM {program_enrollments} AS pe
                             WHERE pe.offeringid = $offeringid AND pe.roleid = $traineeeid AND pe.enrolstatus = 1");
           }elseif(!is_siteadmin() && has_capability('local/organization:manage_organizationofficial',$systemcontext)) {


                $enrolled = $DB->get_field_sql(" SELECT count(pe.userid) AS total
                              FROM {program_enrollments} AS pe
                             WHERE pe.offeringid = $offeringid AND pe.roleid = $traineeeid  AND pe.usercreated = $USER->id AND pe.enrolstatus = 1");

            } else {

                 if($programprice == 1) {

                    $enrolled = $DB->get_field_sql(" SELECT count(pe.userid) AS total
                              FROM {program_enrollments} AS pe
                             WHERE pe.offeringid = $offeringid AND pe.roleid = $traineeeid  AND ( pe.usercreated = $USER->id  OR pe.usercreated IN (SELECT userid FROM {role_assignments} WHERE  contextid = $systemcontext->id AND roleid = $traineeeid )) AND pe.enrolstatus = 1");
                 } else {


                    $enrolled = $DB->get_field_sql(" SELECT count(pe.userid) AS total
                              FROM {program_enrollments} AS pe
                             WHERE pe.offeringid = $offeringid AND pe.roleid = $traineeeid AND pe.enrolstatus = 1 ");
                 }

            }
            $purchasedseats = $DB->get_field_sql("SELECT SUM(approvalseats) 
                         FROM {tool_org_order_seats} 
                        WHERE tablename = 'tp_offerings' AND fieldname = 'id' AND fieldid = $offeringid") ;
            $seats = $total - ($enrolled + $purchasedseats);
        } else {


                if($programprice == 1) {


                $totalseatssql = " SELECT  tp.availableseats AS total
                                             FROM {tp_offerings} tp
                                            WHERE tp.id = $offeringid";

                $purchasedseatssql = "SELECT SUM(approvalseats) 
                                FROM {tool_org_order_seats} 
                                WHERE tablename = 'tp_offerings' AND fieldname = 'id' AND fieldid = $offeringid" ;
                $purchasedseats = $DB->get_field_sql($purchasedseatssql);
                $total = $DB->get_field_sql($totalseatssql);
                $totalseats = $total - $purchasedseats;

                $enrolledsql = "SELECT count(pe.userid) AS total
                                      FROM {program_enrollments} AS pe
                                     WHERE pe.offeringid = $offeringid AND pe.roleid = $traineeeid AND pe.usercreated NOT IN (SELECT userid FROM {role_assignments} WHERE roleid = $orgofficialroleid AND contextid = $systemcontext->id) AND pe.enrolstatus = 1";
                $enrolled= $DB->get_field_sql($enrolledsql);
                $seats =  $totalseats - $enrolled;


            }  else {


                $total_seats = (int) $DB->get_field('tp_offerings','availableseats',['id'=>$offeringid]);

                $enrolled = $DB->get_field_sql(" SELECT count(userid) AS total
                                      FROM {program_enrollments} AS pe
                                     WHERE pe.offeringid = $offeringid AND pe.roleid = $traineeeid AND pe.enrolstatus = 1 ");


                $seats = $total_seats - ($enrolled);

            }

        }

        return $seats;
    }

    public function get_after_approved_available_seats($offeringid) {

        global $DB,$USER;

        $systemcontext = context_system::instance();

        $offeringid = (int) $offeringid;

        $offering= $DB->get_record('tp_offerings',['id'=>$offeringid]);
        $systemcontext = context_system::instance();
        $traineeeid = $DB->get_field('role', 'id', array('shortname' => 'trainee'));
        $orgofficialroleid = $DB->get_field('role', 'id', array('shortname' => 'organizationofficial'));


        $programprice =(int) $DB->get_field_sql('SELECT ltp.price FROM {local_trainingprogram} ltp 
                                               JOIN {tp_offerings} tpo ON tpo.trainingid = ltp.id 
                                               WHERE tpo.id = '.$offeringid.''
                                           );
        if($programprice == 1) {


            $totalseatssql = " SELECT  tp.availableseats AS total
                                         FROM {tp_offerings} tp
                                        WHERE tp.id = $offeringid";

            $purchasedseatssql = "SELECT SUM(approvalseats) 
                            FROM {tool_org_order_seats} 
                            WHERE tablename = 'tp_offerings' AND fieldname = 'id' AND fieldid = $offeringid" ;
            $purchasedseats = $DB->get_field_sql($purchasedseatssql);
            $total = $DB->get_field_sql($totalseatssql);
            $totalseats = $total - $purchasedseats;

            $enrolledsql = "SELECT count(pe.userid) AS total
                                  FROM {program_enrollments} AS pe
                                 WHERE pe.offeringid = $offeringid AND pe.roleid = $traineeeid AND pe.usercreated NOT IN (SELECT userid FROM {role_assignments} WHERE roleid = $orgofficialroleid AND contextid = $systemcontext->id) AND pe.enrolstatus = 1";
            $enrolled= $DB->get_field_sql($enrolledsql);
            $seats =  $totalseats - $enrolled;


        }  else {


            $total_seats = (int) $DB->get_field('tp_offerings','availableseats',['id'=>$offeringid]);

            $enrolled = $DB->get_field_sql(" SELECT count(userid) AS total
                                  FROM {program_enrollments} AS pe
                                 WHERE pe.offeringid = $offeringid AND pe.roleid = $traineeeid AND pe.enrolstatus = 1 ");


            $seats = $total_seats - ($enrolled);

        }

        return $seats;

    }

    public function get_erolled_seats($offeringid,$enrolled_seats_by_me = false,$userid=0 ) {
        global $DB,$USER;

        $offeringid = (int) $offeringid;

        $systemcontext = context_system::instance();
        $traineeeid = $DB->get_field('role', 'id', array('shortname' => 'trainee'));
        
        if((is_siteadmin() || has_capability('local/organization:manage_trainingofficial',$systemcontext)
         || has_capability('local/organization:manage_competencies_official',$systemcontext))
         && !$enrolled_seats_by_me) {

            $enrolledsql = " SELECT count(pe.userid) AS total
                            FROM {program_enrollments} AS pe
                            JOIN {user} u ON u.id = pe.userid 
                             WHERE pe.offeringid = $offeringid AND u.deleted =0
                             AND pe.roleid = $traineeeid AND pe.enrolstatus = 1";
         
        } else {

            if($userid == 0){

                $userid=$USER->id;
                
            }
            if((!is_siteadmin()) && has_capability('local/organization:manage_organizationofficial',$systemcontext)){
                $organization = $DB->get_field('local_users','organization',array('userid'=> $userid));
                $get_all_orgofficials = (new exams())->get_all_orgofficials($userid);
                $users = implode(',',$get_all_orgofficials);
             
                $enrolledsql = " SELECT count(pe.userid) AS total
                                  FROM {program_enrollments} AS pe
                                  JOIN {user} u ON u.id = pe.userid 
                                  WHERE pe.offeringid = $offeringid AND pe.enrolstatus=1 
                                  AND pe.roleid = $traineeeid AND u.deleted =0
                                  AND (pe.usercreated IN($users) OR pe.organization = $organization)
                                  AND pe.enrolstatus = 1";
                                 
            } else {
              
                $enrolledsql = " SELECT count(pe.userid) AS total
                                 FROM {program_enrollments} AS pe
                                 JOIN {user} u ON u.id = pe.userid 
                                 WHERE pe.offeringid = $offeringid AND pe.enrolstatus=1 AND u.deleted =0
                                 AND pe.roleid = $traineeeid AND pe.usercreated = $userid AND pe.enrolstatus = 1";

            }
        }
        $enrolledseats = $DB->count_records_sql($enrolledsql);

        return $enrolledseats;
    }

    public function is_enrolled($rootid, $userid, $entype = null) {
        global $DB;
        //$live =(new trainingprogram())->from_unixtime_for_live_entities('tpo.enddate');
        if(is_null($entype) || $entype == 'program') {
            $sql = 'SELECT pe.id FROM {program_enrollments} as pe 
            JOIN {tp_offerings} as tpo ON pe.offeringid=tpo.id 
            WHERE pe.programid=:rootid AND pe.userid=:userid ';
        } else {
            $sql = 'SELECT pe.id FROM {program_enrollments} as pe 
            JOIN {tp_offerings} as tpo ON pe.offeringid=tpo.id 
            WHERE pe.offeringid=:rootid AND pe.userid=:userid ';
        }
        $enrolled = $DB->record_exists_sql($sql, ['rootid' => $rootid, 'userid' => $userid]);
        if($enrolled){
            return true;
        }
        return false;
    }

    public function delete_offering_sessions($offeringid) {
        global $DB;

        $result = $DB->delete_records('offering_sessions',array('offeringid' =>$offeringid));

        return $result;
    } 

    public function delete_offering_mapped_groups($offeringcode) {
        global $DB,$CFG;

        require_once($CFG->dirroot.'/group/lib.php');
        $sql = "SELECT id FROM {groups} WHERE idnumber = '$offeringcode'";
        $id = $DB->get_field_sql($sql);
        if($id){
            groups_delete_group($id);
        }
        return $id;
    } 
    
    public function update_group_idnumber($old,$new,$courseid) {
        global $DB,$CFG;

        if($DB->record_exists('groups',array('idnumber' => $old))) {

            $updatesql = "UPDATE {groups} SET  name = '$new' ,idnumber = '$new' WHERE courseid = $courseid AND idnumber = '$old'";

           $DB->execute($updatesql);

        } 

    } 

    public function is_current_user_enrolled_to_offering() {
        global $DB,$USER;
        $live =(new trainingprogram())->from_unixtime_for_live_entities('tpo.enddate');
        $sql = 'SELECT pe.id FROM {program_enrollments} as pe 
                  JOIN {tp_offerings} as tpo ON pe.offeringid=tpo.id 
                   AND '.$live.'
                 WHERE pe.userid=:userid ';

        $enrolled = $DB->record_exists_sql($sql, ['userid' => $USER->id]);
        if($enrolled){
            return true;
        }
        return false;

    }

    public function get_all_job_families_by_sectors($sectors, $onlyjobfamily = false) {
        global $DB;

        if(!empty($sectors)) {

            $sectorslist = is_array($sectors)?implode(',', $sectors):$sectors;

            $segmentdata = $DB->get_fieldset_sql('select id from {local_segment} where sectorid in ('.$sectorslist.')');

            if(COUNT($segmentdata) > 0) {
            $segmentlist = implode(',', $segmentdata);
            if($onlyjobfamily) {
                $jobfamilies=$DB->get_fieldset_sql('select id from {local_jobfamily} where segmentid in('.$segmentlist.')');
            } else {
                 $jobfamilies=$DB->get_fieldset_sql('select id from {local_jobfamily} where segmentid in('.$segmentlist.',0)');
            }
           
            }
            return $jobfamilies;

        }


    } 
  

     public static function generate_program_code($data) {
        global $DB;
        try{

           if(COUNT($data->sectors) == 1) {
               $sectorcode = $DB->get_field('local_sector','code',array('id'=>$data->sectors['0']));
            } elseif(COUNT($data->sectors) > 1 && COUNT($data->sectors) <= 3){

                if(COUNT($data->sectors) == 2) {

                    $sectorcode =$DB->get_field('local_sector','code',array('id'=>$data->sectors['0'])).$DB->get_field('local_sector','code',array('id'=>$data->sectors['1']));
                } else {

                    $sectorcode =$DB->get_field('local_sector','code',array('id'=>$data->sectors['0'])).$DB->get_field('local_sector','code',array('id'=>$data->sectors['1'])).$DB->get_field('local_sector','code',array('id'=>$data->sectors['2']));
                }

            } else {

                $sectorcode = 'COM';
            }
            $level =str_replace("level","", $data->clevels);
            $tpid=$DB->get_field_sql('SELECT id FROM {local_trainingprogram} ORDER BY id DESC LIMIT 1');
            if ($tpid) {
                $autoincnum = $tpid+1;
            } else {
                $autoincnum = 1;
            }
            $num = sprintf("%'.04d", $autoincnum);
           
            
            if($data->pnature==0){

                $pn='P';

            }
            if($data->pnature==1){

                $pn='R';

            }  //renu..program code
 
            $code = $sectorcode.$level.$pn.$num;
            if($DB->record_exists('local_trainingprogram',array('code' => $code))) {
                $code = $sectorcode.$level.$pn.($num+1).'';
            } else {
                $code = $sectorcode.$level.$pn.$num;
            }
        } catch(dml_exception $e){
            print_r($e);
        }
        return $code;
    }

     public static function generate_offering_code($data) {
        global $DB;
        try{
            $programcode = $DB->get_field('local_trainingprogram','code',['id'=>$data->trainingid]);
            $tpoid = $DB->get_field_sql('SELECT id FROM {tp_offerings} ORDER BY id DESC LIMIT 1');
            if ($tpoid) {
                $autoincnum = $tpoid+1;
            } else {
                $autoincnum = 1;
            }
            $num = sprintf("%'.02d", $autoincnum);
            $incnum = $num + 1;
            $tpodate =   userdate($data->startdate,'%Y%m%d');
            $ofcode = $programcode.'S'.$tpodate.$num;
            if($data->id > 0) {
               $code = $programcode.'S'.$tpodate.$num;
            } else {
                if($DB->record_exists('tp_offerings',array('code' => $ofcode))) {
                  $code =  $programcode.'S'.$tpodate.$incnum;
                } else {
                    $code = $programcode.'S'.$tpodate.$num;
                }
            }
            
        } catch(dml_exception $e){
            print_r($e);
        }

        return $code;
    }

    public function all_programs_for_api_listing($stable, $filterdata) {
        global $DB, $PAGE, $OUTPUT, $CFG, $SESSION;

        $SESSION->lang = $stable->langinuse;
        $selectsql = "SELECT * FROM {local_trainingprogram} lo  "; 
        $countsql  = "SELECT COUNT(lo.id) FROM {local_trainingprogram} lo";

        $formsql  = " WHERE 1=1 ";

        if(isset($filterdata->search_query) && trim($filterdata->search_query) != ''){
            $formsql .= " AND (lo.name LIKE :firstnamesearch OR lo.namearabic LIKE :arabicnamesearch) ";
            $searchparams = array('firstnamesearch' => '%'.trim($filterdata->search_query).'%','arabicnamesearch' => '%'.trim($filterdata->search_query).'%');
        } else {
            $searchparams = array();
        }

        if (!empty($filterdata->sectors)){
             $sectorids = explode(',', $filterdata->sectors);
             if(!empty($sectorids)){
                $sectorquery = array();
                foreach ($sectorids as $sector) {
                    $sectorquery[] = " CONCAT(',',lo.sectors,',') LIKE CONCAT('%,',$sector,',%') "; 
                }
                $sectoqueeryparams =implode('OR',$sectorquery);
                $formsql .= ' AND ('.$sectoqueeryparams.') ';
            }
        }
        if(!empty($filterdata->targetgroup)){

            $jobfamilyids = explode(',', $filterdata->targetgroup);
            if(!empty($jobfamilyids)){
                $jobfamilyquery = array();
                foreach ($jobfamilyids as $jobfamily) {
                    $jobfamilyquery[] = " CONCAT(',',lo.targetgroup,',') LIKE CONCAT('%,',$jobfamily,',%') "; 
                }
                $jobfamilyparams =implode('OR',$jobfamilyquery);
                $formsql .= ' AND ( ('.$jobfamilyparams.') OR lo.targetgroup = -1 ) ';
            }
        }

        if(!empty($filterdata->program_competencylevel)){

            $competencylevelsids = explode(',', $filterdata->program_competencylevel);
            if(!empty($competencylevelsids)){
                $competencylevelquery = array();
                foreach ($competencylevelsids as $competencylevel) {
                    $competencylevelquery[] = " CONCAT(',',lo.competencyandlevels,',') LIKE CONCAT('%,',$competencylevel,',%') "; 
                }
                $competencylevelparams =implode('OR',$competencylevelquery);
                $formsql .= ' AND ('.$competencylevelparams.') ';
            }
        }

        if($filterdata->{'availablefrom[enabled]'} == 1 ){

            $start_year = $filterdata->{'availablefrom[year]'};
            $start_month = $filterdata->{'availablefrom[month]'};
            $start_day = $filterdata->{'availablefrom[day]'};
            $filter_starttime_con = mktime(0,0,0, $start_month, $start_day, $start_year);
            $formsql.= " AND lo.availableto >= '$filter_starttime_con' ";

        }
        if($filterdata->{'availableto[enabled]'} == 1 ){
            $start_year = $filterdata->{'availableto[year]'};
            $start_month = $filterdata->{'availableto[month]'};
            $start_day = $filterdata->{'availableto[day]'};
            $filter_endtime_con=mktime(23,59,59, $start_month, $start_day, $start_year);
            $formsql.=" AND lo.availableto <= '$filter_endtime_con' ";
        }

        if (!empty($filterdata->training_name)){ 
            $trainings = explode(',',$filterdata->training_name);
             if(!empty($trainings)){
                $trainingquery = array();
                foreach ($trainings as $training) {
                    $trainingquery[] = " CONCAT(',',lo.id,',') LIKE CONCAT('%,',$training,',%') "; 
                }
                $trainingqueeryparams =implode('OR',$trainingquery);
                $formsql .= ' AND ('.$trainingqueeryparams.') ';
            }
        } 
        if (!empty($filterdata->offering_status)){
            $trainings = explode(',',$filterdata->offering_status);
             if(!empty($trainings)){
                $trainingquery = array();
                foreach ($trainings as $training) {

                   if($training == 'completed') {

                     $trainingquery[] = " lo.id IN (SELECT programid FROM {program_completions} WHERE  completion_status = 1) "; 

                   } elseif($training == 'financially_closed') {
                    $expired =  (new trainingprogram())->from_unixtime_for_expired_entities('enddate');
                    $trainingquery[] = " lo.id IN (SELECT trainingid FROM {tp_offerings} WHERE  $expired "; 
                   }

                }
                $trainingqueeryparams =implode('OR',$trainingquery);
                $formsql .= ' AND ('.$trainingqueeryparams.') ';
            }
        } 

        if (!empty($filterdata->offering_type)){ 
            $trainings = explode(',',$filterdata->offering_type);
             if(!empty($trainings)){
                $trainingquery = array();
                foreach ($trainings as $training) {
                    $trainingquery[] = " lo.id IN (SELECT trainingid FROM {tp_offerings} WHERE  trainingmethod = '$training' ) "; 
                }
                $trainingqueeryparams =implode('OR',$trainingquery);
                $formsql .= ' AND ('.$trainingqueeryparams.') ';
            }
        } 

        if (!empty($filterdata->offering_creator)){ 
            $trainings = explode(',',$filterdata->offering_creator);
             if(!empty($trainings)){
                $trainingquery = array();
                foreach ($trainings as $training) {
                    $trainingquery[] = " lo.id IN (SELECT trainingid FROM {tp_offerings} WHERE  usercreated = $training) "; 
                }
                $trainingqueeryparams =implode('OR',$trainingquery);
                $formsql .= ' AND ('.$trainingqueeryparams.') ';
            }
        } 

        $params = array_merge($searchparams);

        $cardviewtotalprograms = $DB->count_records_sql($countsql.$formsql,$params);
        $formsql .=" ORDER BY lo.id DESC";

        $cardviewtrainingprograms = $DB->get_records_sql($selectsql.$formsql, $params, $stable->start,$stable->length);
        $cardviewtrainingprogramlist = array();
        $count = 0;
        foreach($cardviewtrainingprograms as $cardviewtrainingprogram) {
              
              $cardviewtrainingprogramlist[$count]['arabicprogramname'] = $cardviewtrainingprogram->namearabic;
              $cardviewtrainingprogramlist[$count]['programname'] = $cardviewtrainingprogram->name;
              $cardviewtrainingprogramlist[$count]['programcode'] = $cardviewtrainingprogram->code;
    
              if (!empty($cardviewtrainingprogram->image)) {
                 $cardviewtrainingprogramlist[$count]['imageurl']=trainingprogramlogo_url($cardviewtrainingprogram->image);
               } else {
                  $cardviewtrainingprogramlist[$count]['imageurl'] = get_string('no_image','local_trainingprogram');
               }
              $cardviewtrainingprogramlist[$count]['pricetype'] = ($cardviewtrainingprogram->price == 1) ? get_string('paid','local_trainingprogram'): get_string('free','local_trainingprogram');
              $cardviewtrainingprogramlist[$count]['sellingprice'] = number_format($cardviewtrainingprogram->sellingprice);
              $cardviewtrainingprogramlist[$count]['actualprice'] = number_format($cardviewtrainingprogram->actualprice);
              $cardviewtrainingprogramlist[$count]['taxfree'] =(is_null($cardviewtrainingprogram->tax_free))? get_string('not_set','local_trainingprogram') : (((int)$cardviewtrainingprogram->tax_free == 0)? get_string('no','local_trainingprogram'):get_string('yes','local_trainingprogram'));
              $cardviewtrainingprogramlist[$count]['description'] =strip_tags(format_text($cardviewtrainingprogram->description, FORMAT_HTML));
              $cardviewtrainingprogramlist[$count]['programgoals'] =strip_tags(format_text($cardviewtrainingprogram->program_goals,FORMAT_HTML));

              $cardviewtrainingprogramlist[$count]['programcode'] = $cardviewtrainingprogram->code;
              $languages = explode(',',$cardviewtrainingprogram->languages);
              $actuallang = array();
              foreach ( $languages AS $language) {
                $actuallang[]=($language == '1') ? get_string('english','local_trainingprogram') : get_string('arabic','local_trainingprogram');
              }  
              $cardviewtrainingprogramlist[$count]['programlangauge'] = $actuallang ? implode(',',$actuallang) :'-';
              $eprogrammethods = explode(',',$cardviewtrainingprogram->methods);
              $aprogrammethods = array();
              foreach ( $eprogrammethods AS $eprogrammethod) {
                if($eprogrammethod == 0) {
                    $aprogrammethods[]= get_string('lecture','local_trainingprogram');
                } elseif ($eprogrammethod == 1) {
                    $aprogrammethods[]= get_string('case_studies','local_trainingprogram');

                } elseif ($eprogrammethod == 2) {
                    $aprogrammethods[]= get_string('dialogue_teams','local_trainingprogram');

                } else {
                    $aprogrammethods[]= get_string('exercises_assignments','local_trainingprogram');

                }
               }
               $cardviewtrainingprogramlist[$count]['programmethods'] = $aprogrammethods ? implode(',',$aprogrammethods) :'-';
               $emethods = explode(',',$cardviewtrainingprogram->evaluationmethods);
               $aevaluationmethods = array();
               foreach ( $emethods AS $method) {
                    $aevaluationmethods[]= ($method == '0') ? get_string('pre_exam','local_trainingprogram') : get_string('post_exam','local_trainingprogram');
               }
               $cardviewtrainingprogramlist[$count]['evaluationmethods'] = $aevaluationmethods ? implode(',',$aevaluationmethods) :'-';
               $cardviewtrainingprogramlist[$count]['attendancecompletion'] = ($cardviewtrainingprogram->attendancecmpltn == 0) ? get_string('not_set','local_trainingprogram'):get_string('set','local_trainingprogram');
               $cardviewtrainingprogramlist[$count]['attendancepercentage'] = ($cardviewtrainingprogram->attendancecmpltn == 1) ? $cardviewtrainingprogram->attendancepercnt.'%' : '0';
               $cardviewtrainingprogramlist[$count]['durationindays'] = round($cardviewtrainingprogram->duration / 86400);
                $cardviewtrainingprogramlist[$count]['availablefrom'] =userdate($cardviewtrainingprogram->availablefrom, get_string('strftimedatemonthabbr', 'langconfig'));
               $cardviewtrainingprogramlist[$count]['availableto'] = userdate($cardviewtrainingprogram->availableto, get_string('strftimedatemonthabbr', 'langconfig'));
                $cardviewtrainingprogramlist[$count]['hour'] = round($cardviewtrainingprogram->hour / 3600);
                if(COUNT(explode(',',$cardviewtrainingprogram->sectors)) > 0) {

                    if($SESSION->lang == 'ar') {
                        $titlename = 'titlearabic';
                    } else {
                        $titlename = 'title';
                    }

                    list($sectorsql,$sectorparams) = $DB->get_in_or_equal(explode(',',$cardviewtrainingprogram->sectors));
                   
                        $sectorquerysql = "SELECT $titlename FROM {local_sector} WHERE id $sectorsql";
                      
                    $sectorslists= $DB->get_fieldset_sql($sectorquerysql,$sectorparams);
                    $cardviewtrainingprogramlist[$count]['sectors'] = implode(',',$sectorslists);

                } else {

                    $cardviewtrainingprogramlist[$count]['sectors'] = get_string('no_sector_mapped','local_trainingprogram');
                }

                if(COUNT(explode(',',$cardviewtrainingprogram->targetgroup)) > 0 && $cardviewtrainingprogram->targetgroup != null && !empty($cardviewtrainingprogram->targetgroup) && $cardviewtrainingprogram->targetgroup != '') {

                     if($cardviewtrainingprogram->targetgroup == '-1') {

                     $jobfamilies = (new trainingprogram)->get_all_job_families_by_sectors($cardviewtrainingprogram->sectors);
                     $cardviewtrainingprogram->targetgroup = implode(',', $jobfamilies);

                    } else {
                        $target_group = $cardviewtrainingprogram->targetgroup;
                      $cardviewtrainingprogram->targetgroup = $target_group;

                    } 
                    if($cardviewtrainingprogram->targetgroup != null && !empty($cardviewtrainingprogram->targetgroup) && $cardviewtrainingprogram->targetgroup != ''){  
                        if($SESSION->lang == 'ar') {
                            $titlename = 'familynamearabic';
                        } else {
                            $titlename = 'familyname';
                        } 
                        $total_jobfamily_count = $DB->count_records_sql('SELECT COUNT(id) FROM {local_jobfamily} WHERE id in('.$cardviewtrainingprogram->targetgroup.')');

                        $alljobbfamiliesquery = 'select '.$titlename.' from {local_jobfamily} where id in('.$cardviewtrainingprogram->targetgroup.')';
                        

                        $alljobbfamilies = $DB->get_fieldset_sql($alljobbfamiliesquery);
                        $cardviewtrainingprogramlist[$count]['targetgroup'] = implode(',', $alljobbfamilies);

                    } else {

                      $cardviewtrainingprogramlist[$count]['targetgroup'] = get_string('no_jobfamily_mapped','local_trainingprogram');
                    }

                } else {

                    $cardviewtrainingprogramlist[$count]['targetgroup'] = get_string('no_jobfamily_mapped','local_trainingprogram');
                }

                $cardviewtrainingprogramlist[$count]['level'] = str_replace("level","", $cardviewtrainingprogram->clevels);

                 if(COUNT(explode(',',$cardviewtrainingprogram->competencyandlevels)) > 0) {
                             
                   list($competencysql,$competencyparams) = $DB->get_in_or_equal(explode(',',$cardviewtrainingprogram->competencyandlevels));
                    $compquerysql = "SELECT DISTINCT(type) FROM {local_competencies} WHERE id $competencysql";
                    $addedcompetencies= $DB->get_fieldset_sql($compquerysql,$competencyparams);
                    $cardviewtrainingprogramlist[$count]['competencytype'] = implode(',',$addedcompetencies);
                    
                } else {

                    $cardviewtrainingprogramlist[$count]['competencytype'] = get_string('no_types_mapped','local_trainingprogram');
                }

                if(COUNT(explode(',',$cardviewtrainingprogram->competencyandlevels)) > 0) {
                    if($SESSION->lang == 'ar') {
                        $titlename = 'arabicname';
                    } else {
                        $titlename = 'name';
                    } 
                    list($csql,$cparams) = $DB->get_in_or_equal(explode(',',$cardviewtrainingprogram->competencyandlevels));
                    $cquerysql = "SELECT $titlename FROM {local_competencies} WHERE id $csql";
                    $competencies= $DB->get_fieldset_sql($cquerysql,$cparams);
                    $cardviewtrainingprogramlist[$count]['competencies'] = implode(',',$competencies);

                } else {

                    $cardviewtrainingprogramlist[$count]['competencies'] = get_string('no_competencies_mapped','local_trainingprogram');
                }
                if($cardviewtrainingprogram->discount == 0) {

                    $cardviewtrainingprogramlist[$count]['discount'] = get_string('coupon','local_trainingprogram');

                }elseif($cardviewtrainingprogram->discount == 1){

                    $cardviewtrainingprogramlist[$count]['discount'] = get_string('early_registration','local_trainingprogram');
                }else {

                    $cardviewtrainingprogramlist[$count]['discount'] = get_string('groups','local_trainingprogram');
                }
                $cardviewtrainingprogramlist[$count]['courseid'] = $cardviewtrainingprogram->courseid; 
                $cardviewtrainingprogramlist[$count]['coursecode'] = $cardviewtrainingprogram->code; 

                $cardviewtrainingprogramlist[$count]['trainingid'] = $cardviewtrainingprogram->id;

    
            $count++;
        }
        
        $coursesContext = array(
            "programs" => $cardviewtrainingprogramlist,
            "totalprograms" => $cardviewtotalprograms,
            "length" => count($cardviewtrainingprogramlist)

        );
        return $coursesContext;
    }

    public function detailed_program_view_for_api_listing($programid,$mlang = NULL) {
        global $DB, $PAGE, $OUTPUT, $CFG, $SESSION,$USER;
        
        $SESSION->lang = ($mlang) ? $mlang : current_language() ;
       $cardviewtrainingprogram = $DB->get_record('local_trainingprogram',['id'=>$programid]);
       $tptandc = $this->checktermsandconditions($programid);
      if($cardviewtrainingprogram) {

          $program=new stdClass();
            
          $program->arabicprogramname = $cardviewtrainingprogram->namearabic;
          $program->programname = $cardviewtrainingprogram->name;
          $program->programcode = $cardviewtrainingprogram->code;

          if (!empty($cardviewtrainingprogram->image)) {
             $program->image=trainingprogramlogo_url($cardviewtrainingprogram->image);
           } else {
              $program->image = get_string('no_image','local_trainingprogram');
           }
          $program->pricetype = ($cardviewtrainingprogram->price == 1) ? get_string('paid','local_trainingprogram'): get_string('free','local_trainingprogram');
          $program->sellingprice = number_format($cardviewtrainingprogram->sellingprice);
          $program->actualprice = number_format($cardviewtrainingprogram->actualprice).get_string('sa_currency','local_trainingprogram');
         
          $program->description =strip_tags(format_text($cardviewtrainingprogram->description, FORMAT_HTML));
          
          $program->programgoals =strip_tags(format_text($cardviewtrainingprogram->program_goals,FORMAT_HTML));

          $program->programcode = $cardviewtrainingprogram->code;
          $languages = explode(',',$cardviewtrainingprogram->languages);
          $actuallang =array();
          foreach ( $languages AS $language) {
            $actuallang[]=($language == '1') ? get_string('english','local_trainingprogram') : get_string('arabic','local_trainingprogram');
          }  
          $program->programlangauge = $actuallang ? implode('&',$actuallang) :'-';
          $eprogrammethods = explode(',',$cardviewtrainingprogram->methods);
          $aprogrammethods = array();
          foreach ($eprogrammethods AS $eprogrammethod) {
            if($eprogrammethod == 0) {
                $aprogrammethods[]= get_string('lecture','local_trainingprogram');
            } elseif ($eprogrammethod == 1) {
                $aprogrammethods[]= get_string('case_studies','local_trainingprogram');

            } elseif ($eprogrammethod == 2) {
                $aprogrammethods[]= get_string('dialogue_teams','local_trainingprogram');

            } else {
                $aprogrammethods[]= get_string('exercises_assignments','local_trainingprogram');

            }
           }


           $program->programmethods = $aprogrammethods ? implode(',',$aprogrammethods) :'-';
           $emethods = explode(',',$cardviewtrainingprogram->evaluationmethods);
           $aevaluationmethods = array();
           foreach ( $emethods AS $method) {
                $aevaluationmethods[]= ($method == '0') ? get_string('pre_exam','local_trainingprogram') : get_string('post_exam','local_trainingprogram');
           }
           $program->evaluationmethods = $aevaluationmethods ? implode(',',$aevaluationmethods) :'-';
           $program->attendancecompletion = ($cardviewtrainingprogram->attendancecmpltn == 0) ? get_string('not_set','local_trainingprogram'):get_string('set','local_trainingprogram');
           $program->attendancepercentage = ($cardviewtrainingprogram->attendancecmpltn == 1) ? $cardviewtrainingprogram->attendancepercnt.'%' : '0';
           $program->durationindays = round($cardviewtrainingprogram->duration / 86400);
           $program->durationinhours = round(($cardviewtrainingprogram->duration / 86400)*24);
            $program->availablefrom = $cardviewtrainingprogram->availablefrom;
           $program->availableto = $cardviewtrainingprogram->availableto;
            $program->hour = round($cardviewtrainingprogram->hour / 3600);

            if(COUNT(explode(',',$cardviewtrainingprogram->targetgroup)) > 0 && $cardviewtrainingprogram->targetgroup != null && !empty($cardviewtrainingprogram->targetgroup) && $cardviewtrainingprogram->targetgroup != '') {

             if($cardviewtrainingprogram->targetgroup == '-1') {

             $jobfamilies = (new trainingprogram)->get_all_job_families_by_sectors($cardviewtrainingprogram->sectors);
             $cardviewtrainingprogram->targetgroup = implode(',', $jobfamilies);

            } else {
                $targroup = $cardviewtrainingprogram->targetgroup;
                $cardviewtrainingprogram->targetgroup = $targroup;
            } 
            if($cardviewtrainingprogram->targetgroup != null && !empty($cardviewtrainingprogram->targetgroup) && $cardviewtrainingprogram->targetgroup != ''){   
                $alljobbfamiliesquery = 'select * from {local_jobfamily} where id in('.$cardviewtrainingprogram->targetgroup.')';
                
                $jobbfamilies= $DB->get_records_sql($alljobbfamiliesquery);

                foreach ($jobbfamilies AS $jobbfamily) {

                    $jobbfamily->name =($SESSION->lang == 'ar')? $jobbfamily->familynamearabic : $jobbfamily->familyname ;
                    $jobbfamily->description = strip_tags(format_text($jobbfamily->description, FORMAT_HTML));
                    $jbfmid = $jobbfamily->id;
                    $jobbfamily->id = $jbfmid;
               } 
                if(!empty($jobbfamilies)){
                    $program->targetgroup = array_values($jobbfamilies);
                } else {
                    $program->targetgroup = array();
                }

            } else {

                $program->targetgroup = array();
            }

            } else {

                $program->targetgroup = array();
            }
           list($sectorsql,$sectorparams) = $DB->get_in_or_equal(explode(',',$cardviewtrainingprogram->sectors));
           
           $sectorquerysql = "SELECT * FROM {local_sector} WHERE id $sectorsql";
              
            $sectors= $DB->get_records_sql($sectorquerysql,$sectorparams);
            foreach ($sectors AS $sector) {

                $sector->name =($SESSION->lang == 'ar')? $sector->titlearabic :$sector->title;
                $seccode = $sector->code;
                $secid=  $sector->id;
                $sector->code = $seccode;
                $sector->id = $secid;
            } 
            if(!empty($sectors)){
                $program->sectors = array_values($sectors);
            } else {
                $program->sectors = array();
            }
            $program->level = str_replace("level","", $cardviewtrainingprogram->clevels);

             if(COUNT(explode(',',$cardviewtrainingprogram->competencyandlevels)) > 0) {
                         
               list($competencysql,$competencyparams) = $DB->get_in_or_equal(explode(',',$cardviewtrainingprogram->competencyandlevels));
                $compquerysql = "SELECT DISTINCT type FROM {local_competencies} WHERE id $competencysql";
                $addedcompetencies= $DB->get_fieldset_sql($compquerysql,$competencyparams);
                $program->competencytype = implode(':',$addedcompetencies);
                $competencytypes = [];
                foreach($addedcompetencies as $addedcompetencie) {
                    $row = [];
                    $row['name'] = $addedcompetencie;
                    $competencytypes[] = $row;
                }
                $program->competencytype = $competencytypes;

            } else {

                $program->competencytype = get_string('no_types_mapped','local_trainingprogram');
            }

            $programagendasql ="SELECT id,day,description AS agenda,programid,oldid FROM {program_agenda} WHERE programid =:programid" ;
            $programagendas = $DB->get_records_sql($programagendasql,['programid'=>$programid]);
            foreach ( $programagendas as $programagenda) {
                $programagenda->name = get_string('program_agenda_day','local_trainingprogram').$programagenda->day;
                $programagenda->description = strip_tags(format_text($programagenda->agenda,FORMAT_HTML));
                $agendaid = $programagenda->id;
                $programagenda->id =$agendaid; 
                $programagenda->parentValue = $programagenda->programid;
            }
            if(!empty($programagendas)){
                $program->programagenda = array_values($programagendas);
            } else {
                $program->programagenda = array();
            }
           
            list($competencysql,$competencyparams) = $DB->get_in_or_equal(explode(',',$cardviewtrainingprogram->competencyandlevels));
            $compquerysql = "SELECT * FROM {local_competencies} WHERE id $competencysql";

            $competencies = $DB->get_records_sql($compquerysql,$competencyparams);
            $competencytypes = tp::constcompetencytypes();
            foreach($addedcompetencies as $addedcompetency) {
                $row = [];
                $row['type'] = $competencytypes[$addedcompetency];
                $ctypes['data'] = [];
                foreach($competencies as $competency) {
                    if($competency->type == $addedcompetency) {
                        $type = [];
                        $type['name'] = ($SESSION->lang == 'ar') ? $competency->arabicname : $competency->name ;
                        $type['typeId'] = 0;
                        $type['description'] = strip_tags(format_text($competency->description, FORMAT_HTML));
                        $type['id'] = $competency->id;
                        $type['level'] = $program->level;
                        $ctypes['data'][] = $type;
                    }
                }
                $competenciesdata[] = $row+$ctypes;
            }

            $constcompetencytypes = tp::constcompetency_types();
            if(!empty($competencies)){
                $program->competencies = $competenciesdata;
            } else {
                $program->competencies = array();
            }
            if($cardviewtrainingprogram->discount == 0) {

                $program->discount = get_string('coupon','local_trainingprogram');

            }elseif($cardviewtrainingprogram->discount == 1){

                $program->discount = get_string('early_registration','local_trainingprogram');
            }else {

                $program->discount = get_string('groups','local_trainingprogram');
            }

            $trainerroleid = $DB->get_field('role', 'id', array('shortname' => 'trainer'));
            $totaltrainers=$DB->get_fieldset_sql("SELECT u.username 
                                  FROM {user} AS u JOIN {local_users} AS lu ON lu.userid = u.id 
                                  WHERE u.id IN (SELECT ue.userid FROM {program_enrollments} AS ue 
                                  WHERE ue.roleid=$trainerroleid AND programid = $programid)");

            $trainers = [];
            foreach($totaltrainers as $totaltrainer) {
                $row = [];
                $row['name'] = $totaltrainer;
                $trainers[] = $row;
            }

            $program->assignedtrainers = $trainers;

            $publicstring = get_string('public','local_trainingprogram');
            $privatestring = get_string('private','local_trainingprogram');
            $dedicatedstring = get_string('dedicated','local_trainingprogram');

            $onlinestring = get_string('scheduleonline','local_trainingprogram');
            $offlinestring = get_string('scheduleoffline','local_trainingprogram');
            $elearningstring = get_string('scheduleelearning','local_trainingprogram');

            $userorganization = $DB->get_field_sql("SELECT organization FROM {local_users} WHERE userid = $USER->id");

            $systemcontext = context_system::instance();

            if(!is_siteadmin() && has_capability('local/organization:manage_trainee',$systemcontext)) {
                $live =(new trainingprogram())->from_unixtime_for_live_entities('tpo.startdate');
                $offeringwherecondition = " WHERE tpo.trainingid = $programid AND $live AND ((tpo.type = 1 AND tpo.organization = $userorganization) OR tpo.type = 0) ";
            } else {
                $expired =  (new trainingprogram())->from_unixtime_for_expired_entities('tpo.startdate');
                $offeringwherecondition= " WHERE tpo.trainingid = $programid AND $expired ";
            }
            $tpofferings = $DB->get_records_sql("SELECT tpo.id,tpo.code,tpo.startdate,tpo.enddate,tpo.type,tpo.trainingmethod,h.city,tpo.time,tpo.endtime,tpo.duration,tpo.availableseats,tpo.organization,tpo.sellingprice,tpo.actualprice,h.name AS hallname,tpo.time,h.maplocation, h.seatingcapacity,h.buildingname,tpo.usercreated,tpo.usermodified,tpo.timecreated,tpo.timemodified,tpo.type AS typeid,
                CASE
                    WHEN tpo.type = 0 THEN '$publicstring'
                    WHEN tpo.type = 1 THEN '$privatestring'
                    ELSE '$dedicatedstring' 
                END AS offeringtype,
                CASE
                    WHEN tpo.trainingmethod = 'online' THEN '$onlinestring'
                    WHEN tpo.trainingmethod = 'offline' THEN '$offlinestring'
                    ELSE '$elearningstring' 
                END AS trainingmethod
                FROM  {tp_offerings} AS tpo LEFT JOIN {hall} AS h ON
                tpo.halladdress=h.id  $offeringwherecondition ");

            foreach ($tpofferings AS $tpoffering) {
      
                $starttime = gmdate("h:i",$tpoffering->time);
                $endtime = gmdate("h:i",$tpoffering->endtime);
                $starttimemeridian = gmdate('a',$tpoffering->time);
                $endtimemeridian = gmdate('a',$tpoffering->enddate);
                $startmeridian = ($starttimemeridian == 'am')? get_string('am','local_trainingprogram'): get_string('pm','local_trainingprogram');
                $endmeridian = ($endtimemeridian == 'am')?  get_string('am','local_trainingprogram'): get_string('pm','local_trainingprogram');
                $traineeroleid = $DB->get_field('role', 'id', array('shortname' => 'trainee'));
                $enrolledtraineessql="SELECT COUNT(id) FROM {program_enrollments} WHERE programid = $programid AND offeringid = $tpoffering->id AND courseid = $cardviewtrainingprogram->courseid AND roleid = $traineeroleid";
                $totalofferingtrainers=$DB->get_fieldset_sql("SELECT u.username 
                                  FROM {user} AS u JOIN {local_users} AS lu ON lu.userid = u.id 
                                  WHERE u.id IN (SELECT ue.userid FROM {program_enrollments} AS ue 
                                  WHERE ue.roleid=$trainerroleid AND programid = $programid AND offeringid = $tpoffering->id)");
                $tpoffering->assignedtrainers = $totalofferingtrainers ? implode(':', $totalofferingtrainers):get_string('no_trainer','local_trainingprogram');
                $programenrolledcount = $DB->count_records_sql($enrolledtraineessql);
                $tpoffering->offeringmethod = $tpoffering->trainingmethod;
                $tpoffering->buildingname = $tpoffering->buildingname?$tpoffering->buildingname:'-';
                $tpoffering->organization = ($tpoffering->organization) ? $DB->get_field('local_organization','fullname',['id'=>$tpoffering->organization]):'-';
                $tpoffering->hallname = $tpoffering->hallname?$tpoffering->hallname:'-';
                $tpoffering->city =$tpoffering->city? (new \local_hall\hall())->listofcities($tpoffering->city):null;
                $tpoffering->maplocation = $tpoffering->maplocation?$tpoffering->maplocation:'-';
                $tpoffering->nooftraineesenrolled =$programenrolledcount ? $programenrolledcount : 0;
                $tpoffering->offeringcode = $tpoffering->code;
                $offstartdate = $tpoffering->startdate;
                $tpoffering->startdate = $offstartdate;
                $tpoffering->enddate = ($tpoffering->trainingmethod == 'elearning') ? '-' : $tpoffering->enddate;
                $tpoffering->starttime = $starttime.' '.$startmeridian;
                $tpoffering->endtime = $endtime.' '.$endmeridian;
                $tpoffering->seats = $tpoffering->availableseats;
                $tpoffering->type = $tpoffering->typeid;
                $tpoffering->typename = $tpoffering->offeringtype;
                $tpoffering->sellingprice = $tpoffering->sellingprice.get_string('sa_currency','local_trainingprogram');
                $tpoffering->actualprice = $tpoffering->actualprice.get_string('sa_currency','local_trainingprogram');
                $tpoffering->sellingprice = number_format($tpoffering->sellingprice);
                $tpoffering->actualprice = number_format($tpoffering->actualprice).' SAR';
                $tpoffering->offeringcreater = ($tpoffering->usercreated > 0) ? $DB->get_field('user','username',['id'=>$tpoffering->usercreated]): '-';
                $tpoffering->offeringupdater = ($tpoffering->usermodified > 0) ? $DB->get_field('user','username',['id'=>$tpoffering->usermodified]): '-';
                $tpoffering->offeringcreateddate = ($tpoffering->timecreated > 0) ? $tpoffering->timecreated : 0;
                $tpoffering->offeringmodifieddate = ($tpoffering->timemodified > 0) ? $tpoffering->timemodified : 0;
                $tpoffering->productdetails = (new \tool_product\product)->get_product_variations($programid, $tpoffering->id, 1,$tptandc);
            } 

            if(!empty($tpofferings)){
                $program->offerings = array_values($tpofferings);
            } else {
                $program->offerings = array();
            }
            $program->totalitemcount = COUNT($tpofferings);
            $program->certificatename = $cardviewtrainingprogram->name;
            $program->published = $cardviewtrainingprogram->published; 
            $program->published = $cardviewtrainingprogram->published; 
            $program->courseid = $cardviewtrainingprogram->courseid; 
            $program->coursecode = $cardviewtrainingprogram->code; 
            $program->programcreator = ($cardviewtrainingprogram->usercreated > 0) ? $DB->get_field('user','username',['id'=>$cardviewtrainingprogram->usercreated]): '-';
            $program->programupdator = ($cardviewtrainingprogram->usermodified > 0) ? $DB->get_field('user','username',['id'=>$cardviewtrainingprogram->usermodified]): '-';
            $program->createdat = ($cardviewtrainingprogram->timecreated > 0) ? $cardviewtrainingprogram->timecreated: 0;
            $program->modifiedat = ($cardviewtrainingprogram->timemodified > 0) ? $cardviewtrainingprogram->timemodified : 0;

            return $program;

        }

      
    }
    public function get_programinfo($programId,$isArabic,$offeringcode = null) {
        global $DB, $PAGE, $OUTPUT, $CFG, $SESSION;

      $cardviewtrainingprogram = $DB->get_record('local_trainingprogram',['id'=>$programId]);

      if($cardviewtrainingprogram) {

          $program=new stdClass();
          
          $SESSION->lang =($isArabic == 'true')?'ar':'en';

          list($sectorsql,$sectorparams) = $DB->get_in_or_equal(explode(',',$cardviewtrainingprogram->sectors));
           
           $sectorquerysql = "SELECT * FROM {local_sector} WHERE id $sectorsql";
            $sectors= $DB->get_records_sql($sectorquerysql,$sectorparams);
            foreach ($sectors AS $sector) {

                $sector->name =($SESSION->lang == 'ar')? $sector->titlearabic : $sector->title ;
                $sector->description = ($sector->description) ? $sector->description : null;
                $sector_code = $sector->code;
                $sector->code =$sector_code; 
                $sector->value = $sector->id;
            } 
            if(!empty($sectors)){
                $program->sectorsList = array_values($sectors);
            } else {
                $program->sectorsList = array();
            }
          $program->id = $cardviewtrainingprogram->id;
          $program->name =($SESSION->lang == 'ar')?$cardviewtrainingprogram->namearabic : $cardviewtrainingprogram->name ;

          $program->brief =strip_tags(format_text($cardviewtrainingprogram->description, FORMAT_HTML));

          $program->programType = ($SESSION->lang == 'ar') ?  'برنامج تدريب': 'Training program';

          $trainingtypes = explode(',',$cardviewtrainingprogram->trainingtype);
          $actualtype =array();
          foreach ( $trainingtypes AS $trainingtype) {
            $actualtype[]=($trainingtype == 'online') ? get_string('scheduleonline','local_trainingprogram') : (($trainingtype == 'offline') ? get_string('scheduleoffline','local_trainingprogram'): get_string('scheduleelearning','local_trainingprogram'));
          }  
          $program->trainingType = $actualtype ? implode(',',$actualtype) :null;
          $languages = explode(',',$cardviewtrainingprogram->languages);
          $actuallang =array();
          foreach ( $languages AS $language) {
            $actuallang[]=($language == '1') ? get_string('english','local_trainingprogram') : get_string('arabic','local_trainingprogram');
          }  
          $program->language = $actuallang ? implode(',',$actuallang) :'-';
    
          if (!empty($cardviewtrainingprogram->image)) {
             $program->imgDataUrl=trainingprogramlogo_url($cardviewtrainingprogram->image);
           } else {
              $program->imgDataUrl = get_string('no_image','local_trainingprogram');
           }

            $publicstring = get_string('public','local_trainingprogram');
            $privatestring = get_string('private','local_trainingprogram');
            $dedicatedstring = get_string('dedicated','local_trainingprogram');

            $offeringsql = "SELECT tpo.id,tpo.code,tpo.trainingid,tpo.startdate,tpo.enddate,tpo.type,tpo.trainingmethod,h.city,tpo.time,tpo.endtime,tpo.duration,tpo.availableseats,tpo.organization,tpo.sellingprice,tpo.actualprice,h.name AS hallname,tpo.time,h.maplocation, h.seatingcapacity,h.buildingname,tpo.usercreated,tpo.usermodified,tpo.timecreated,tpo.timemodified,tpo.oldid,
                CASE
                    WHEN tpo.type = 0 THEN '$publicstring'
                    WHEN tpo.type = 1 THEN '$privatestring'
                    ELSE '$dedicatedstring' 
                END AS offeringtype
                FROM  {tp_offerings} AS tpo LEFT JOIN {hall} AS h ON
                tpo.halladdress=h.id  WHERE tpo.trainingid = $programId ";

            if($offeringcode) {
               $offeringsql .= " AND tpo.code = '$offeringcode' ";
            }
            $offeringsql .= " ORDER BY tpo.startdate ASC ";
            $tpofferings = $DB->get_records_sql($offeringsql);

            foreach ($tpofferings AS $tpoffering) {

               $traineeeid = $DB->get_field('role', 'id', array('shortname' => 'trainee'));
                $offeringid = $tpoffering->id;
                $tpoffering->id =$offeringid; 
                $tpoffering->programID = $tpoffering->trainingid;
                $tpoffering->programFees = $tpoffering->sellingprice.'.00';
                $tpoffering->duration =(($tpoffering->duration / 3600) > 1) ? ($tpoffering->duration / 3600).' '.get_string('hours','local_trainingprogram') :($tpoffering->duration / 3600).' '.get_string('hour','local_trainingprogram') ;
                $tpoffering->city = $tpoffering->city? (new \local_hall\hall())->listofcities($tpoffering->city):null;
                $tpoffering->planLocation = $tpoffering->maplocation?$tpoffering->maplocation:'-';
                $tpoffering->planScheduleDayStartDate = date('Y-m-d',$tpoffering->startdate).'T'.date('H:i:s',$tpoffering->startdate);
                $tpoffering->planScheduleDayEndDate = ($tpoffering->trainingmethod == 'elearning')? '-' :date('Y-m-d',$tpoffering->enddate).'T'.date('H:i:s',$tpoffering->enddate);;
                $starttime = gmdate("H:i:s",$tpoffering->time);
                $endtime = gmdate("H:i:s",$tpoffering->endtime);
                $tpoffering->planScheduleDayStartTime = $starttime;
                $tpoffering->planScheduleDayEndTime = $endtime;
                $tpoffering->startdate = userdate($tpoffering->startdate, get_string('strftimedatemonthabbr', 'langconfig'));
                $trainingmethod=  $tpoffering->trainingmethod;
                $tpoffering->enddate = ($trainingmethod == 'elearning') ? '-' :  userdate($tpoffering->enddate, get_string('strftimedatemonthabbr', 'langconfig'));
                $tpoffering->starttime = gmdate("H:i",$tpoffering->time);
                $tpoffering->endtime = ($trainingmethod == 'elearning') ? '' :gmdate("H:i",$tpoffering->endtime);
                $tpoffering->planScheduleDayEndTime = $endtime;
                $tpoffering->programlanguageId = $cardviewtrainingprogram->languages;
                $tpoffering->programLanguage = $program->language;
                $tpoffering->price = $tpoffering->sellingprice;
                $tpoffering->total_seats = $tpoffering->availableseats;
                $tpoffering->available_seats = $this->get_after_approved_available_seats($tpoffering->id);
               
                if($trainingmethod == 'online') {
                    $tpoffering->trainingmethod= get_string('scheduleonline','local_trainingprogram'); 
                } elseif($trainingmethod == 'offline') {
                    $tpoffering->trainingmethod = get_string('scheduleoffline','local_trainingprogram');
                } else {
                    $tpoffering->trainingmethod = get_string('scheduleelearning','local_trainingprogram');  
                }

                $enrolledsql = " SELECT count(userid) AS total
                              FROM {program_enrollments} AS pe
                             WHERE pe.offeringid = $tpoffering->id AND pe.roleid=$traineeeid";

                $enrolled= $DB->get_field_sql($enrolledsql);

                $tpoffering->enrolled_seats = $enrolled;
                $tpoffering->targetGender = null;
                $tpoffering->roomId = null;
                $tpoffering->roomName = null;
            } 
            if(!empty($tpofferings)){
                $program->plans = array_values($tpofferings);
            } else {
                $program->plans = array();
            }

            $programagendasql ="SELECT id,day,description AS agenda,programid,oldid FROM {program_agenda} WHERE programid =:programid" ;
            $programagendas = $DB->get_records_sql($programagendasql,['programid'=>$programId]);
            foreach ( $programagendas as $programagenda) {
                $programagenda->name = get_string('program_agenda_day','local_trainingprogram').$programagenda->day;
                $programagenda->description = format_text($programagenda->agenda,FORMAT_HTML);
                $programagenda->value = $programagenda->id;
                $programagenda->parentValue = $programagenda->programid;
            }
            if(!empty($programagendas)){
                $program->programAgenda = array_values($programagendas);
            } else {
                $program->programAgenda = array();
            }
           
            list($competencysql,$competencyparams) = $DB->get_in_or_equal(explode(',',$cardviewtrainingprogram->competencyandlevels));
            $compquerysql = "SELECT * FROM {local_competencies} WHERE id $competencysql";

            $competencies= $DB->get_records_sql($compquerysql,$competencyparams);
            $constcompetencytypes = tp::constcompetency_types();

            $selectsql = "SELECT  DISTINCT lc.type
        FROM {local_competencies} as lc  ORDER BY lc.type ASC"; 
        $competencytyps= $DB->get_records_sql($selectsql);
        $alltypes = [];
        $count = 0;
        $i= 1;
        foreach($competencytyps as $competencytype){
            $alltypes[$count]['typeid'] = $i++;
            $alltypes[$count]['typeName'] = $competencytype->type;
            $count++;
        }
        
        $i= 1;
        foreach ($competencies AS $competence) {

            $key =  array_search($competence->type, array_column($alltypes, 'typeName'));   

            $competence->name =($SESSION->lang == 'ar')? $competence->arabicname : $competence->name ;
            $competence->typeId =($alltypes[$key]['typeName'] == $competence->type)? $alltypes[$key]['typeid'] : 0;
            $competence->typeName = $constcompetencytypes[$competence->type];
            $competence->description = strip_tags(format_text($competence->description, FORMAT_HTML));
            $competence->value = $competence->id;
        } 
        if(!empty($competencies)){
            $program->programCompetencies = array_values($competencies);
        } else {
            $program->programCompetencies = array();
        }


        list($prepathsql,$prepathparams) = $DB->get_in_or_equal(explode(',',$cardviewtrainingprogram->prerequirementsprograms));
        
        $prepathquerysql = "SELECT * FROM {local_trainingprogram} WHERE id $prepathsql";
            
        $prepathprograms= $DB->get_records_sql($prepathquerysql,$prepathparams);
        foreach ($prepathprograms AS $prepathprogram) {

            $prepathprogram->name =($SESSION->lang == 'ar')? $prepathprogram->namearabic : $prepathprogram->name ;
            $prepathprogram->description = strip_tags(format_text($prepathprogram->description,FORMAT_HTML));
            $prepathprogram->value = $prepathprogram->id;
        } 
        if(!empty($prepathprograms)){
            $program->preProgramPath = array_values($prepathprograms);
        } else {
            $program->preProgramPath = array();
        }

        list($postpathsql,$postpathparams) = $DB->get_in_or_equal(explode(',',$cardviewtrainingprogram->postrequirementsprograms));
        
        $postpathquerysql = "SELECT * FROM {local_trainingprogram} WHERE id $postpathsql";
            
        $postpathprograms= $DB->get_records_sql($postpathquerysql,$postpathparams);
        foreach ($postpathprograms AS $postpathprogram) {

            $postpathprogram->name =($SESSION->lang == 'ar')? $postpathprogram->namearabic : $postpathprogram->name ;
            $postpathprogram->description = strip_tags(format_text($postpathprogram->description,FORMAT_HTML));
            $postpathprogram->value = $postpathprogram->id;
        } 
        if(!empty($postpathprograms)){
            $program->postProgramPath = array_values($postpathprograms);
        } else {
            $program->postProgramPath = array();
        }
        if($cardviewtrainingprogram->trainingtopics) {

            list($trainingtopicssql,$trainingtopicsparams) = $DB->get_in_or_equal(explode(',',$cardviewtrainingprogram->trainingtopics));
            $querysql = "SELECT * FROM {training_topics} WHERE id $trainingtopicssql";
            $topics= $DB->get_records_sql($querysql,$trainingtopicsparams);
            foreach ($topics AS $topic) {
                $topic->name = format_text($topic->name,FORMAT_HTML);
                $topic->description = null;
                $topic->value = $topic->id;
                $topic->parentValue = $programId;
            } 
            if(!empty($topics)){
                $program->trainingTopics = array_values($topics);
            } else {
                $program->trainingTopics = array();
            }

        } else {
            $program->trainingTopics = array();
        }
        $goalssql = "SELECT *  FROM {program_goals} WHERE programid = $programId";
        $programgoals= $DB->get_records_sql($goalssql);
        foreach ($programgoals AS $goal) {
            $goal->name = format_text($goal->programgoal,FORMAT_HTML);
            $goal->description = null;
            $goal->value = $goal->id;
        } 
        if(!empty($programgoals)){
            $program->programMains = array_values($programgoals);
        } else {
            $program->programMains = array();
        }
        $program->integratedProgramPath = array(); 
        if(COUNT(explode(',',$cardviewtrainingprogram->targetgroup)) > 0 && $cardviewtrainingprogram->targetgroup != null && !empty($cardviewtrainingprogram->targetgroup) && $cardviewtrainingprogram->targetgroup != '') {
            if($cardviewtrainingprogram->targetgroup == '-1') {

                $jobfamilies = (new trainingprogram)->get_all_job_families_by_sectors($cardviewtrainingprogram->sectors);
                $cardviewtrainingprogram->targetgroup = implode(',', $jobfamilies);
            } else {
                $target_group = $cardviewtrainingprogram->targetgroup;
                $cardviewtrainingprogram->targetgroup = $target_group;
            } 
            if($cardviewtrainingprogram->targetgroup != null && !empty($cardviewtrainingprogram->targetgroup) && $cardviewtrainingprogram->targetgroup != ''){   
                $alljobbfamiliesquery = 'select * from {local_jobfamily} where id in('.$cardviewtrainingprogram->targetgroup.')';
                
                $jobbfamilies= $DB->get_records_sql($alljobbfamiliesquery);

                foreach ($jobbfamilies AS $jobbfamily) {

                    $jobbfamily->name =($SESSION->lang == 'ar')? $jobbfamily->familynamearabic : $jobbfamily->familyname ;
                    $jobbfamily->description = strip_tags(format_text($jobbfamily->description, FORMAT_HTML));
                    $jobbfamily->value = $jobbfamily->id;
                
            } 
                if(!empty($jobbfamilies)){
                    $program->programTargetCategories = array_values($jobbfamilies);
                } else {
                    $program->programTargetCategories = array();
                }

            } else {

                $program->programTargetCategories = array();
            }

        } else {

            $program->programTargetCategories = array();
        }

        $program->programSectors = $program->sectorsList;
        $program->programRequirements = array();
        $program->competencyLevelCode = null;
        $program->competencyLevelId = ($cardviewtrainingprogram->clevels) ? ((substr($cardviewtrainingprogram->clevels,0,5) == 'level') ?  str_replace("level", "", $cardviewtrainingprogram->clevels) :((substr($cardviewtrainingprogram->clevels,0,6) == 'Level ')?str_replace("Level ", "", $cardviewtrainingprogram->clevels) : str_replace("Level", "", $cardviewtrainingprogram->clevels))) : 0 ;
        $program->competencyLevelName = str_replace("level",get_string('level_spacess','local_trainingprogram'),$cardviewtrainingprogram->clevels);
        $program->actualProgramFees = $cardviewtrainingprogram->sellingprice.'.00';
        $program->actualNumberOfDayesAndHours = ($cardviewtrainingprogram->hour / 3600).' '.get_string('hours','local_trainingprogram').'-'.($cardviewtrainingprogram->duration / 86400).' '.get_string('days','local_trainingprogram');
    
        $program->actualProgramLang = $program->language;
        $eprogrammethods = explode(',',$cardviewtrainingprogram->methods);
        $aprogrammethods = array();
        foreach ($eprogrammethods AS $eprogrammethod) {
        if($eprogrammethod == 0) {
            $aprogrammethods[]= get_string('lecture','local_trainingprogram');
        } elseif ($eprogrammethod == 1) {
            $aprogrammethods[]= get_string('case_studies','local_trainingprogram');

        } elseif ($eprogrammethod == 2) {
            $aprogrammethods[]= get_string('dialogue_teams','local_trainingprogram');

        } else {
            $aprogrammethods[]= get_string('exercises_assignments','local_trainingprogram');
        }
        }
        $listlang = explode(',',$cardviewtrainingprogram->languages);
        $listl = array();
        foreach ($listlang AS $list) {
        if($list == 0) {
            $listl[]= get_string('arabic','local_trainingprogram');
        } else {
            $listl[]= get_string('english','local_trainingprogram');
        }
        }
        $program->actualTrainingMethod = $aprogrammethods;
        $etrainingtypes = explode(',',$cardviewtrainingprogram->trainingtype);
        $listoftrainingtypes = array();
        foreach ($etrainingtypes AS $etrainingtype) {
            $listoftrainingtypes[]= ($etrainingtype == 'online') ? get_string('scheduleonline','local_trainingprogram') : (($etrainingtype == 'offline') ? get_string('scheduleoffline','local_trainingprogram'): get_string('scheduleelearning','local_trainingprogram'));
        }
        $program->actualTrainingType =$actualtype;
        $program->actualLanguage =$listl;

        $emethods = explode(',',$cardviewtrainingprogram->evaluationmethods);
        $aevaluationmethods = array();
        foreach ( $emethods AS $method) {
            $aevaluationmethods[]= ($method == '0') ? get_string('pre_exam','local_trainingprogram') : get_string('post_exam','local_trainingprogram');
        }
        $program->actualEvaluationMethod = $aevaluationmethods;

        $program->organizationPartner = array();

        $program->externalRegistrationURL = null;
        $program->programFees = $cardviewtrainingprogram->actualprice.'.00';
        $program->amount = $cardviewtrainingprogram->sellingprice.'.00';

        $emethods = explode(',',$cardviewtrainingprogram->evaluationmethods);

        if($cardviewtrainingprogram->discount == 0) {
            $program->discount = get_string('coupon','local_trainingprogram');
        }elseif($cardviewtrainingprogram->discount == 1){
            $program->discount= get_string('early_registration','local_trainingprogram');
        }else {
            $program->discount = get_string('groups','local_trainingprogram');
        }
        $program->discountPenalty = null;
        $program->discountAmount = null;
        $program->vat = null;
        $program->totalAmount = null;
        $program->isPercentage = true;
        $program->detailsPageURL =$CFG->wwwroot.'/local/trainingprogram/programcourseoverview.php?programid='.$programId;
        return $program;

        }
    }

    public function get_allprogramsbyjobfamilyid($JobFamilyID, $isArabic) {
        global $DB,$SESSION;
        $SESSION->lang =($isArabic == 'true')?'ar':'en';
        $selectsql = "SELECT * FROM {local_trainingprogram} lo "; 
        $countsql  = "SELECT COUNT(lo.id) FROM {local_trainingprogram} lo ";
        $formsql =" WHERE (FIND_IN_SET('$JobFamilyID',targetgroup) OR lo.targetgroup = -1) AND published = 1 ";
        $cardviewtotalprograms = $DB->count_records_sql($countsql.$formsql);
        $formsql .=" ORDER BY lo.id ASC";
        $cardviewtrainingprograms = $DB->get_records_sql($selectsql.$formsql);
        $cardviewtrainingprogramlist = array();
        $count = 0;
        foreach($cardviewtrainingprograms as $cardviewtrainingprogram) {
              $cardviewtrainingprogramlist[$count]['value'] = $cardviewtrainingprogram->id;
              $cardviewtrainingprogramlist[$count]['name'] =($SESSION->lang == 'ar')?$cardviewtrainingprogram->namearabic : $cardviewtrainingprogram->name ;
              $cardviewtrainingprogramlist[$count]['description'] =strip_tags(format_text($cardviewtrainingprogram->description, FORMAT_HTML));
            $count++;
        }
        $coursesContext = array(
            "programs" => $cardviewtrainingprogramlist,
            "totalprograms" => $cardviewtotalprograms,
            "length" => count($cardviewtrainingprogramlist)

        );
        return $coursesContext;
    
    }

    public function get_allprogramsbycompetencyid($CompetencyID, $isArabic) {
        global $DB,$SESSION;
        $SESSION->lang =($isArabic == 'true')?'ar':'en';
        $selectsql = "SELECT * FROM {local_trainingprogram} lo "; 
        $countsql  = "SELECT COUNT(lo.id) FROM {local_trainingprogram} lo ";
        $formsql =" WHERE FIND_IN_SET('$CompetencyID',lo.competencyandlevels) AND published = 1  ";
        $cardviewtotalprograms = $DB->count_records_sql($countsql.$formsql);
        $formsql .=" ORDER BY lo.id ASC";
        $cardviewtrainingprograms = $DB->get_records_sql($selectsql.$formsql);
        $cardviewtrainingprogramlist = array();
        $count = 0;
        foreach($cardviewtrainingprograms as $cardviewtrainingprogram) {
              $cardviewtrainingprogramlist[$count]['value'] = $cardviewtrainingprogram->id;
              $cardviewtrainingprogramlist[$count]['name'] =($SESSION->lang == 'ar')?$cardviewtrainingprogram->namearabic : $cardviewtrainingprogram->name ;
              $cardviewtrainingprogramlist[$count]['description'] =strip_tags(format_text($cardviewtrainingprogram->description, FORMAT_HTML));

            if (!empty($cardviewtrainingprogram->image)) {
                $cardviewtrainingprogramlist[$count]['imageurl'] = trainingprogramlogo_url($cardviewtrainingprogram->image);
            }else{
                $cardviewtrainingprogramlist[$count]['imageurl'] ='';
            }
            
            $count++;
        }
        $coursesContext = array(
            "programs" => $cardviewtrainingprogramlist,
            "totalprograms" => $cardviewtotalprograms,
            "length" => count($cardviewtrainingprogramlist)

        );
        return $coursesContext;
    
    }

    public function get_allprograms($stable) {
        global $DB, $PAGE, $OUTPUT, $CFG, $SESSION;
        $SESSION->lang =($stable->isArabic == 'true')?'ar':'en';
        // $selectsql = "SELECT * FROM {local_trainingprogram} lo "; 
        $selectsql = "SELECT tpo.id as eventid,lo.*, tpo.startdate, tpo.time,(tpo.startdate+tpo.time) as testtime,date(FROM_UNIXTIME(tpo.startdate)),UNIX_TIMESTAMP(NOW()) FROM {local_trainingprogram} lo "; 

        $countsql  = "SELECT COUNT(distinct(lo.id)) FROM {local_trainingprogram} lo  ";
            
        // $formsql = " LEFT JOIN {tp_offerings} tpo on tpo.trainingid = lo.id AND (tpo.startdate+tpo.time > UNIX_TIMESTAMP(NOW())) ";

        $formsql = " LEFT JOIN {tp_offerings} tpo on tpo.trainingid = lo.id ";

        $formsql .= " WHERE lo.published = 1 ";
        // $formsql = " WHERE 1=1 ";
       
        if(isset($stable->query) && trim($stable->query) != ''){
            $formsql .= " AND (lo.name LIKE :search OR lo.namearabic LIKE :arabicsearch OR lo.code LIKE :code )";
            $searchparams = array(
                'search' => '%'.trim($stable->query).'%',
                'arabicsearch' => '%'.trim($stable->query).'%',
                'code' => '%'.trim($stable->query).'%',
             );
        } else {
            $searchparams = array();
        }
      

        $live =(new trainingprogram())->from_unixtime_for_live_entities('lo.availableto');
        $formsql.= " AND $live ";


        if(!empty($stable->startDate) && empty($stable->endDate)){
            
            $filter_starttime_con = strtotime($stable->startDate);
            
            $roleid = $DB->get_field_sql("SELECT r.id FROM  {role} r  WHERE r.shortname = 'trainee'");

            // $formsql.= " AND  tpo.id IN (SELECT  tpo.id FROM {tp_offerings} AS tpo  WHERE tpo.published = 1 AND tpo.startdate >= '$filter_starttime_con' AND tpo.trainingmethod != 'elearning' AND tpo.type = 0 AND tpo.availableseats > (SELECT COUNT(id) FROM {program_enrollments} WHERE  offeringid = tpo.id  AND enrolstatus = 1 AND roleid = $roleid ) AND (tpo.startdate+tpo.time > UNIX_TIMESTAMP(NOW())) ) ";

            $formsql.= " AND  tpo.id IN (SELECT  tpo.id FROM {tp_offerings} AS tpo  WHERE tpo.published = 1 AND tpo.startdate >= '$filter_starttime_con' AND tpo.trainingmethod != 'elearning' AND tpo.type != 1 AND tpo.availableseats > (SELECT COUNT(id) FROM {program_enrollments} WHERE  offeringid = tpo.id  AND enrolstatus = 1 AND roleid = $roleid) AND (tpo.startdate+tpo.time IN (SELECT MIN(startdate+time) FROM {tp_offerings} WHERE (startdate+time > UNIX_TIMESTAMP(NOW())) GROUP BY trainingid )) GROUP BY tpo.trainingid ORDER BY (tpo.startdate+tpo.time) ASC ) ";

        }

        if(!empty($stable->endDate) && empty($stable->startDate)){        
           
            $filter_endtime_con = strtotime($stable->endDate);
            
            $roleid = $DB->get_field_sql("SELECT r.id FROM  {role} r  WHERE r.shortname = 'trainee'");

            // $formsql.= " AND  tpo.id IN (SELECT  tpo.id FROM {tp_offerings} AS tpo  WHERE tpo.published = 1  AND tpo.enddate <= '$filter_endtime_con' AND tpo.trainingmethod != 'elearning' AND tpo.type = 0 AND tpo.availableseats > (SELECT COUNT(id) FROM {program_enrollments} WHERE  offeringid = tpo.id  AND enrolstatus = 1 AND roleid = $roleid ) AND (tpo.startdate+tpo.time > UNIX_TIMESTAMP(NOW())) ) ";

            $formsql.= " AND  tpo.id IN (SELECT  tpo.id FROM {tp_offerings} AS tpo  WHERE tpo.published = 1  AND tpo.enddate <= '$filter_endtime_con' AND tpo.trainingmethod != 'elearning' AND tpo.type != 1 AND tpo.availableseats > (SELECT COUNT(id) FROM {program_enrollments} WHERE  offeringid = tpo.id  AND enrolstatus = 1 AND roleid = $roleid ) AND (tpo.startdate+tpo.time IN (SELECT MIN(startdate+time) FROM {tp_offerings} WHERE (startdate+time > UNIX_TIMESTAMP(NOW())) GROUP BY trainingid )) GROUP BY tpo.trainingid ORDER BY (tpo.startdate+tpo.time) ASC ) ";

        }

        if (!empty($stable->startDate) && !empty($stable->endDate)) {    
                
            $filter_starttime_con = strtotime($stable->startDate);

            $elist = explode('-', $stable->endDate);
            $edate = implode(',', $elist);
            $b = explode(',',$edate);
            $filter_endtime_con = mktime(23,59,59, $b[1], $b[0], $b[2]);

            $roleid = $DB->get_field_sql("SELECT r.id FROM  {role} r  WHERE r.shortname = 'trainee'");

            // $formsql.= " AND  tpo.id IN (SELECT tpo.id FROM {tp_offerings} AS tpo  WHERE tpo.published = 1 AND tpo.startdate >= '$filter_starttime_con' AND tpo.startdate <= '$filter_endtime_con' AND tpo.trainingmethod != 'elearning' AND tpo.type = 0 AND tpo.availableseats > (SELECT COUNT(id) FROM {program_enrollments} WHERE  offeringid = tpo.id  AND enrolstatus = 1 AND roleid = $roleid) AND (tpo.startdate+tpo.time >= UNIX_TIMESTAMP(NOW())) ORDER BY (tpo.startdate+tpo.time) ASC ) ";

                $formsql.= " AND  tpo.id IN (SELECT tpo.id FROM {tp_offerings} AS tpo  WHERE tpo.published = 1 AND tpo.startdate >= '$filter_starttime_con' AND tpo.startdate <= '$filter_endtime_con' AND tpo.trainingmethod != 'elearning' AND tpo.type != 1 AND tpo.availableseats > (SELECT COUNT(id) FROM {program_enrollments} WHERE  offeringid = tpo.id  AND enrolstatus = 1 AND roleid = $roleid) AND (tpo.startdate+tpo.time IN (SELECT MIN(startdate+time) FROM {tp_offerings} WHERE (startdate+time > UNIX_TIMESTAMP(NOW())) GROUP BY trainingid )) GROUP BY tpo.trainingid ORDER BY (tpo.startdate+tpo.time) ASC ) ";

        }
       
        if (!empty($stable->SectorIds)){
             $sectorids = explode(',',str_replace(';',',',$stable->SectorIds));
             if(!empty($sectorids)){
                $sectorquery = array();
                foreach ($sectorids as $sector) {
                    $sectorquery[] = " CONCAT(',',lo.sectors,',') LIKE CONCAT('%,',$sector,',%') "; 
                }
                $sectoqueeryparams =implode('OR',$sectorquery);
                $formsql .= ' AND ('.$sectoqueeryparams.') ';
            }
        }
        
        if(!empty($stable->JobFamilyIds)){

            $jobfamilyids = explode(',',str_replace(';',',',$stable->JobFamilyIds));
            if(!empty($jobfamilyids)){
                $jobfamilyquery = array();
                foreach ($jobfamilyids as $jobfamily) {
                    $jobfamilyquery[] = " CONCAT(',',lo.targetgroup,',') LIKE CONCAT('%,',$jobfamily,',%') "; 
                }
                $jobfamilyparams =implode('OR',$jobfamilyquery);
                $formsql .= ' AND ( ('.$jobfamilyparams.') OR lo.targetgroup = -1 ) ';
            }
        }
        
        if(!empty($stable->CompetencyLevelId)){

            $formsql.=" AND  REPLACE(lo.clevels,'level','') = '$stable->CompetencyLevelId' ";
        } 

        if(!empty($stable->CompetencyId)){
            $formsql.=" AND  lo.competencyandlevels IN ($stable->CompetencyId) ";
        } 

        // if(!empty($stable->TrainingTypeId)){

        //     $formsql.=" AND  lo.id IN (SELECT trainingid FROM {tp_offerings} WHERE type = '$stable->TrainingTypeId') ";
        // }

        if(!empty($stable->TrainingTypeId)){

            if ($stable->TrainingTypeId == 1) {
                $trainingmethod = 'online';
            }elseif($stable->TrainingTypeId == 2){
                $trainingmethod = 'offline';
            }else{
                $trainingmethod = 'elearning';
            }

            $formsql.=" AND  lo.id IN (SELECT trainingid FROM {tp_offerings} WHERE trainingmethod LIKE '$trainingmethod') ";
        }

        $params = array_merge($searchparams);
        $cardviewtotalprograms = $DB->count_records_sql($countsql.$formsql,$params);
        // $formsql .= ($stable->isDescending == 'true') ? " ORDER BY lo.id DESC" : " ORDER BY lo.id ASC";
        $formsql .= " GROUP BY lo.id ORDER BY tpo.startdate+tpo.time IS NULL, (tpo.startdate+tpo.time >= UNIX_TIMESTAMP(NOW())), (tpo.startdate+tpo.time) ";
        $formsql .= ($stable->isDescending == 'true') ? " DESC " : " ASC ";
        // $formsql .= ($stable->isDescending == 'true') ? " ORDER BY lo.id DESC" : " ORDER BY lo.id ASC";
        // $formsql .= " GROUP BY lo.id ORDER BY tpo.startdate+tpo.time IS NULL, (tpo.startdate+tpo.time >= UNIX_TIMESTAMP(NOW())), (tpo.startdate+tpo.time) ";
        // $formsql .= ($stable->isDescending == 'true') ? " DESC " : " ASC ";

       
        $cardviewtrainingprograms = $DB->get_records_sql($selectsql.$formsql, $params, $stable->start,$stable->length);
        $cardviewtrainingprogramlist = array();
        $count = 0;
        foreach($cardviewtrainingprograms as $cardviewtrainingprogram) {
               $cardviewtrainingprogramlist[$count]['activityType'] =  ($SESSION->lang == 'ar') ?  'برنامج تدريب': 'Training program';
               $cardviewtrainingprogramlist[$count]['location'] = null;
               $cardviewtrainingprogramlist[$count]['activityID'] = $cardviewtrainingprogram->id;
                $cardviewtrainingprogramlist[$count]['name'] =($SESSION->lang == 'ar')?$cardviewtrainingprogram->namearabic : $cardviewtrainingprogram->name ;
                $cardviewtrainingprogramlist[$count]['description'] =strip_tags(format_text($cardviewtrainingprogram->description, FORMAT_HTML));
               $cardviewtrainingprogramlist[$count]['date'] = date('d/m/Y H:i:s',$cardviewtrainingprogram->availablefrom);
               $cardviewtrainingprogramlist[$count]['detailsPageURL'] =$CFG->wwwroot.'/local/trainingprogram/programcourseoverview.php?programid='.$cardviewtrainingprogram->id;
               $cardviewtrainingprogramlist[$count]['imgDataURL']= (!empty($cardviewtrainingprogram->image)) ? trainingprogramlogo_url($cardviewtrainingprogram->image) : null;
               $cardviewtrainingprogramlist[$count]['organizationPartner'] = array();
               $cardviewtrainingprogramlist[$count]['competencyLevelId'] = (int)str_replace("level","",$cardviewtrainingprogram->clevels);
               $cardviewtrainingprogramlist[$count]['competencyLevelName'] = str_replace("level",get_string('level_spacess','local_trainingprogram'),$cardviewtrainingprogram->clevels);
               $cardviewtrainingprogramlist[$count]['programFees'] = $cardviewtrainingprogram->sellingprice.'.00';
               $cardviewtrainingprogramlist[$count]['amount'] = $cardviewtrainingprogram->sellingprice.'.00';
               $cardviewtrainingprogramlist[$count]['discountPenalty'] = null;
               $cardviewtrainingprogramlist[$count]['discountAmount'] = null;
               $cardviewtrainingprogramlist[$count]['vat'] = null;
               $cardviewtrainingprogramlist[$count]['totalAmount'] = null;
               $cardviewtrainingprogramlist[$count]['isPercentage'] = true;
               $cardviewtrainingprogramlist[$count]['isSponsored'] = null;
               // $firstofferingdatesql = "SELECT tpo.startdate, tpo.time FROM {tp_offerings} AS tpo WHERE tpo.trainingid = $cardviewtrainingprogram->id AND tpo.startdate > :ctime";
               // $firstofferingdate = $DB->get_record_sql($firstofferingdatesql, array("ctime"=> time()));
                // $starttimemeridian = gmdate('a',$cardviewtrainingprogram->time);
                // $startmeridian = ($starttimemeridian == 'am')? get_string('am','local_trainingprogram'):get_string('pm','local_trainingprogram');
                // $ofr_dt = userdate($cardviewtrainingprogram->startdate, get_string('strftimedaydate', 'langconfig')).' '.((current_language() == 'ar') ? gmdate("i:h",$cardviewtrainingprogram->time) : gmdate("h:i",$cardviewtrainingprogram->time)).' '.$startmeridian;


               $offeringslist = $DB->get_field_sql("SELECT (tpo1.startdate+tpo1.time) as timetest FROM {tp_offerings} tpo1 WHERE tpo1.id = $cardviewtrainingprogram->eventid ");

               $ofr_dt = '';
               if($offeringslist > 0){
                    $ofr_dt = userdate(($offeringslist), get_string('strftimedatetime','core_langconfig'));
               }

               //  $ofr_dt = '';
               // if($cardviewtrainingprogram->testtime > 0){
               //      $ofr_dt = userdate(($cardviewtrainingprogram->testtime), get_string('strftimedatetime','core_langconfig'));
               // }

               $cardviewtrainingprogramlist[$count]['firstofferingdate'] = $ofr_dt ? $ofr_dt : '';
            $count++;
        }
        $coursesContext = array(
            "programs" => $cardviewtrainingprogramlist,
            "totalprograms" => $cardviewtotalprograms,
            "length" => count($cardviewtrainingprogramlist)

        );
        return $coursesContext;
    
    }

public function trainingstatistic($stable) {
        global $DB;
        $selectsql = "SELECT GROUP_CONCAT(tp.id) AS tpid,COUNT(tp.id) AS tpcount,FROM_UNIXTIME(tp.availablefrom,'%Y')AS tpyear
        FROM {local_trainingprogram} AS tp GROUP BY FROM_UNIXTIME(tp.availablefrom,'%Y')";      
        $trainingprograms = $DB->get_records_sql($selectsql,$stable->start,$stable->length);
        $trainingprogramslist =array();
        $count = 0;            
        foreach($trainingprograms  as $trainingprogram){                        
            $trainingprogramslist[$count]['year'] = $trainingprogram->tpyear;
            $trainingprogramslist[$count]['noOfPlans'] = $trainingprogram->tpcount; 
            $offlineplanscount =  $DB->get_record_sql("SELECT COUNT(tpof.id) AS tpcount FROM {tp_offerings} AS tpof WHERE trainingid IN($trainingprogram->tpid) AND trainingmethod='offline'");          
            $trainingprogramslist[$count]['inClassTrainingPlans']=$offlineplanscount->tpcount;
            $onlineplanscount = $DB->get_record_sql("SELECT COUNT(tpof.id) AS tpcount FROM {tp_offerings} AS tpof WHERE trainingid IN($trainingprogram->tpid) AND trainingmethod='online'");   
            $trainingprogramslist[$count]['onLineTrainingPlans'] = $onlineplanscount->tpcount; 
            $enrolledusers = $DB->get_record_sql("SELECT COUNT(pe.id) AS peid FROM {program_enrollments} AS pe WHERE programid IN ($trainingprogram->tpid)");  
            $trainingprogramslist[$count]['numberOfTrainees'] = $enrolledusers->peid;       
            $count++;

        }  
        return   $trainingprogramslist; 
       
    }

    public function training_attachment($stable) {
        global $DB,$SESSION;

      $cardviewtrainingprogram = $DB->get_record('local_trainingprogram',['id'=>$stable->attachmentId]);

      if($cardviewtrainingprogram) {

          $program=new stdClass();

          if ($cardviewtrainingprogram->image > 0) {

            $sql = "SELECT * FROM {files} WHERE itemid = :logo AND filearea='trainingprogramlogo' AND filename != '.' ORDER BY id DESC";
            $trainingprogramlogorecord = $DB->get_record_sql($sql,array('logo' => $cardviewtrainingprogram->image),1);

            $program->id = $stable->attachmentId;
            $program->fileName = $trainingprogramlogorecord->filename;
            $program->extention = pathinfo($trainingprogramlogorecord->filename, PATHINFO_EXTENSION);
            $program->contentType = $trainingprogramlogorecord->mimetype;
            $program->Thumbnail =null;
            $program->Content = null;
           }

            return $program;

        }
 
    }

     public function event_attachment($stable) {
        global $DB, $PAGE, $OUTPUT, $CFG, $SESSION;

      $record = $DB->get_record('local_events',['id'=>$stable->attachmentId]);

      if($record) {

          $event=new stdClass();
          $event->id = $stable->attachmentId;
            $event->fileName = $CFG->wwwroot.'/local/events/exportpdf.php?id='.$stable->attachmentId;
            $event->extention = '.pdf';
            $event->contentType = 'Application/pdf';
            $event->Thumbnail =null;
            $event->Content = null;

            return $event;

        }
 
    }

    public function check_certificate($stable) {
        global $DB,$SESSION;

      $record = $DB->get_record('tool_certificate_issues',['code'=>$stable->issueNumber]);

      $SESSION->lang =($stable->isArabic == 'true')?'ar':'en';
      if($record) {
          $certificate=new StdClass();
            if($SESSION->lang == 'ar') {
                $displaying_name = "concat(lc.firstnamearabic,' ',lc.middlenamearabic,' ',lc.thirdnamearabic,' ',lc.lastnamearabic)";
            } else {
                $displaying_name = "concat(lc.firstname,' ',lc.middlenameen,' ',lc.thirdnameen,' ',lc.lastname)";
            }
            $userfullnamequery = "SELECT $displaying_name as fullname 
                                        FROM {user} AS u 
                                        JOIN {local_users} lc ON lc.userid = u.id 
                                        WHERE u.id=:userid";
             $fullusername = $DB->get_field_sql( $userfullnamequery,['userid'=>$record->userid]) ;                          
            $certificate->fullUserName = $fullusername;
            $certificate->issueNumber = $stable->issueNumber;
            $certificate->expirationTimeInYears = $record->expires?date('Y',$record->expires):null;
            $certificate->issueDate =date('Y-m-d',$record->timecreated).'T'.date('H:i:s',$record->timecreated);
            if($record->moduletype == 'exams') {
               $event = $DB->get_record('local_events',['id'=>$record->moduleid]);
               $certificate->titleEn = $event->title;
               $certificate->titleAr = $event->titlearabic; 
            } elseif($record->moduletype == 'events') {
               $exam = $DB->get_record('local_exams',['id'=>$record->moduleid]);
               $certificate->titleEn = $exam->exam;
               $certificate->titleAr = $exam->examnamearabic; 
            } elseif($record->moduletype == 'trainingprogram') {
                $program = $DB->get_record_sql('SELECT tp.* FROM {local_trainingprogram} AS tp
                                           JOIN {tp_offerings} AS tpo ON tpo.trainingid = tp.id
                                           WHERE tpo.id =:offeringid',['offeringid'=>$record->moduleid]);
               $certificate->titleAr = $program->namearabic;
               $certificate->titleEn = $program->name; 
            } else {
               $learningtrack = $DB->get_record('local_learningtracks',['id'=>$record->moduleid]);
               $certificate->titleEn = $learningtrack->title;
               $certificate->titleAr = $learningtrack->titlearabic; 
            }

            $programid = $DB->get_field_sql('SELECT tp.id FROM {local_trainingprogram} AS tp
                                           JOIN {tp_offerings} AS tpo ON tpo.trainingid = tp.id
                                           WHERE tpo.id =:offeringid',['offeringid'=>$record->moduleid]);

             $certificate->certificateTypeId =($record->moduletype == 'trainingprogram')? $programid : $record->moduleid; 
             $certificate->certificateTypeName = ($record->moduletype == 'trainingprogram')? get_string('trainingprogram','local_trainingprogram') : (($record->moduletype == 'exams')? get_string('exams','local_exams') : (($record->moduletype == 'events')? get_string('pluginname','local_events') : get_string('learningtracks','local_learningtracks'))); 
             $certificate->prerequisiteCertificateExams = array();
             $certificate->prerequisiteCertificateTrainingCourses = array();
             $certificate->prerequisiteCertificateEvents = array();
            return $certificate;

            

        }
 
    }

    public function get_competencies($level=0, $ctypes, $competencies)
    {
        global $DB;
        $ctypes = explode(',', $ctypes);
        $types = implode('\',\'',$ctypes);

        if($level!="0")
        {
            $sql = "SELECT lc.id 
                  FROM {local_competencies} as lc 
                 WHERE FIND_IN_SET('$level', lc.level) AND lc.type IN ('$types') ";
        }
        else
        {
            $sql = "SELECT lc.id 
                  FROM {local_competencies} as lc 
                 WHERE  lc.type IN ('$types') ";
        }

        

        $data= $DB->get_fieldset_sql($sql);
        $competencies = explode(',',$competencies);
        $result = array_diff($competencies, $data);

        $uniquedata = [];
        foreach($result as $list) {
            $row = [];
            $row['id'] = $list;
            $uniquedata[] = $row;
        }

        return $uniquedata;
    }


    public function orgofficialprogram_view($programid,$isArabic) {
        global $DB,$SESSION;

        $systemcontext = context_system::instance();

        $cardviewtrainingprogram = $DB->get_record('local_trainingprogram',['id'=>$programid]);

          if($cardviewtrainingprogram) {

            $program=new stdClass();

            $SESSION->lang =($isArabic == 'true')?'ar':'en';

            $program->programname = ($SESSION->lang =='ar') ? $cardviewtrainingprogram->namearabic:  $cardviewtrainingprogram->name;
            $program->programcode = $cardviewtrainingprogram->code;
            $program->programid = $cardviewtrainingprogram->id;
            $publicstring = get_string('public','local_trainingprogram');
            $privatestring = get_string('private','local_trainingprogram');
            $dedicatedstring = get_string('dedicated','local_trainingprogram');
            $tpofferings = $DB->get_records_sql("SELECT tpo.id,tpo.code,tpo.startdate,tpo.enddate,tpo.type,tpo.trainingmethod,h.city,tpo.time,tpo.endtime,tpo.duration,tpo.availableseats as totalofferingseats,tpo.organization,tpo.sellingprice,tpo.actualprice,tpo.actualprice,h.id AS hallid,h.name AS hallname,tpo.time,h.maplocation, h.seatingcapacity,h.buildingname,tpo.usercreated,tpo.usermodified,tpo.timecreated,tpo.timemodified,
                CASE
                    WHEN tpo.type = 0 THEN '$publicstring'
                    WHEN tpo.type = 1 THEN '$privatestring'
                    ELSE '$dedicatedstring' 
                END AS offeringtype
                FROM  {tp_offerings} AS tpo
                LEFT JOIN {hall} AS h ON
                tpo.halladdress=h.id  WHERE tpo.trainingid = $programid ORDER BY tpo.startdate DESC");

            foreach ($tpofferings AS $tpoffering) {
                $offering_id = $tpoffering->id;
                $offering_code = $tpoffering->code;                     

                $tpoffering->id = $offering_id;
                $tpoffering->code = $offering_code;

                $traineeeid = $DB->get_field('role', 'id', array('shortname' => 'trainee'));
                $totalseatssql = " SELECT  tp.availableseats AS total
                FROM {tp_offerings} tp
                WHERE tp.id = $tpoffering->id";
                $total = $DB->get_record_sql($totalseatssql);
        
                $enrolledseatsselectsql = "SELECT COUNT(pe.id) as enrolled
                    FROM {tp_offerings} tp
                    LEFT JOIN {program_enrollments} as pe ON pe.offeringid = tp.id
                    WHERE tp.id = $tpoffering->id AND pe.roleid = $traineeeid";
                $enrolled = $DB->get_record_sql($enrolledseatsselectsql);
                $availableseats = $this->get_after_approved_available_seats($tpoffering->id); 
                $tpoffering->totalseats =  $total->total;
                $tpoffering->availableseats = $availableseats;
               
                $starttime = gmdate("H:i",$tpoffering->time);
                $endtime = gmdate("H:i",$tpoffering->endtime);
                $starttimemeridian = gmdate('a',$tpoffering->time);
                $endtimemeridian = gmdate('a',$tpoffering->enddate);
                $startmeridian = ($starttimemeridian == 'am')? 'AM':'PM';
                $endmeridian = ($endtimemeridian == 'am')? 'AM':'PM';
                $traineeroleid = $DB->get_field('role', 'id', array('shortname' => 'trainee'));
                $offstartdate = $tpoffering->startdate;
                $tpoffering->startdate = $offstartdate;
                $tpoffering->enddate = ($tpoffering->trainingmethod == 'elearning') ? '-' : $tpoffering->enddate;
                $tpoffering->starttime = $starttime;
                $tpoffering->endtime = $endtime;


                $tpoffering->hallinfo = [
                    'id'=>$tpoffering->hallid,
                    'hallname'=>$tpoffering->hallname,
                    'buildingname'=>$tpoffering->buildingname,
                    'city'=>$tpoffering->city?(new \local_hall\hall())->listofcities($tpoffering->city):null,
                    'seatingcapacity'=>$tpoffering->seatingcapacity,
                    'seats'=> $availableseats,
                    'servicefor'=> $program->programname,
                ];
                $tpoffering->type = $tpoffering->offeringtype;
                $elling_price = $tpoffering->sellingprice;
                $actual_price = $tpoffering->sellingprice;
                $tpoffering->sellingprice = $elling_price;
                $tpoffering->actualprice = $actual_price;
                $tpoffering->method =$tpoffering->trainingmethod;
                $offeringstartdate = date('Y-m-d',$tpoffering->startdate);
                $offeringenddate = date('Y-m-d',$tpoffering->enddate);

                $currdate = date('Y-m-d');

                $offeringstarttime = gmdate("H:i",$tpoffering->time);
                $currenttime = date('H:i');

                if($offeringstartdate > $currdate || ($offeringstartdate  ==  $currdate &&  $tpoffering->time > 0 &&  $offeringstarttime >= $currenttime ) && $availableseats > 0 ) {
                        if($cardviewtrainingprogram->price == '1'){
                            $tpoffering->action =($availableseats < 1)?'noseatsavailable':'bookseats';
                          $summary =['label'=>get_string('booknow','local_trainingprogram'),'action'=>"seatreservation",'tablename'=>"tp_offerings",'fieldname'=>"id",'fieldid'=>$tpoffering->id,'availableseats'=>$availableseats,'parentfieldid'=>$programid];

                         $tpoffering->seatsdata =  base64_encode(serialize($summary));

                        } else {
                           $tpoffering->action = 'enrollseats';
                           $tpoffering->seatsdata = null;
                        }
                } else {
                       

                    if(($offeringstartdate  < $currdate || ($offeringstartdate  ==  $currdate && $tpoffering->time > 0 &&  $offeringstarttime < $currenttime )) && $offeringenddate > $currdate) {

                       $tpoffering->action = 'offering_in_progress';
                       $tpoffering->seatsdata = null;
                    }

                    if(($offeringstartdate  < $currdate || ($offeringstartdate  ==  $currdate &&  $tpoffering->time > 0 &&  $offeringstarttime < $currenttime )) && $offeringenddate <= $currdate){
                        

                        $tpoffering->action = 'offering_expired';
                        $tpoffering->seatsdata = null;

                    }
                    if($availableseats <= 0){


                        $tpoffering->action = 'noseats';
                        $tpoffering->seatsdata = null;
                    }                

                } 

            } 
            if(!empty($tpofferings)){
                $program->offerings = array_values($tpofferings);
            } else {
                $program->offerings = array();
            }
          

            return $program;

        }

    }

     public function trainingtopicsview() {
        global $DB, $PAGE, $OUTPUT;
        $systemcontext = context_system::instance();
        $renderer = $PAGE->get_renderer('local_trainingprogram');
        $filterparams  = $renderer->get_trainingtopics(true);
        $filterparams['submitid'] = 'form#filteringform';
        $filterparams['widthclass'] = 'col-md-12';
        $filterparams['placeholder'] = get_string('serch_training_topics','local_trainingprogram');
        $globalinput=$renderer->global_filter($filterparams);
        $trainingtopics = $renderer->get_trainingtopics();
        $filterparams['trainingtopics'] = $trainingtopics;
        $filterparams['globalinput'] = $globalinput;
        $renderer->listoftrainingtopics($filterparams);

    }

    public function get_listof_trainingtopics($stable, $filterdata) {
        global $DB, $PAGE, $OUTPUT, $CFG, $USER;
        $lang = current_language();
        $selectsql = "SELECT *
        FROM {training_topics} "; 
        $countsql  = "SELECT COUNT(id)
        FROM {training_topics} ";
         $formsql  = " WHERE 1=1 ";
        if  (isset($filterdata->search_query) && trim($filterdata->search_query) != ''){
            $formsql .= " AND (name LIKE :namesearch) ";
            $searchparams = array('namesearch' => '%'.trim($filterdata->search_query).'%');
        } else {
            $searchparams = array();
        }
        $params = array_merge($searchparams);
        $totaltrainingtopics = $DB->count_records_sql($countsql.$formsql,$params);
        $formsql .=" ORDER BY id DESC";
        $trainingtopics = $DB->get_records_sql($selectsql.$formsql, $params, $stable->start,$stable->length);
        $trainingtopicslist = array();
        $count = 0;
        foreach($trainingtopics as $topic) {
            $trainingtopicslist[$count]["id"] = $topic->id;

            $trainingtopicslist[$count]["name"] =format_text($topic->name,'FORMAT_HTML');
            $trainingtopicslist[$count]["displayname"] =strip_tags(format_text($topic->name,'FORMAT_HTML'));

            if($topic->usercreated == 2) {
                $displaying_name = "concat(u.firstname,' ',u.lastname)";
                $sql_query = "SELECT $displaying_name as fullname 
                                     FROM {user} AS u 
                                     WHERE u.id=:userid";
                $fullname = $DB->get_field_sql( $sql_query,['userid'=>2]) ;
            } else {


                if($lang == 'ar') {
                    $displaying_name = "concat(lc.firstnamearabic,' ',lc.middlenamearabic,' ',lc.thirdnamearabic,' ',lc.lastnamearabic)";
                } else {
                    $displaying_name = "concat(lc.firstname,' ',lc.middlenameen,' ',lc.thirdnameen,' ',lc.lastname)";
                }
                $sql_query = "SELECT $displaying_name as fullname 
                                            FROM {user} AS u 
                                            JOIN {local_users} lc ON lc.userid = u.id 
                                            WHERE u.id=:userid";
                $fullname = $DB->get_field_sql( $sql_query,['userid'=>$topic->usercreated]) ;

            }
            $trainingtopicslist[$count]["usercreated"] =$fullname ;
            $trainingtopicslist[$count]["timecreated"] =userdate($topic->timecreated,get_string('strftimedatemonthabbr', 'core_langconfig'));
            $count++;
        }
        $coursesContext = array(
            "hascourses" => $trainingtopicslist,
            "totaltrainingtopics" => $totaltrainingtopics,
            "length" => count($trainingtopicslist)
        );
        return $coursesContext;
    } 

    public function remove_trainingtopics($id) {
        global $DB;
        try{
            $result = $DB->delete_records('training_topics',array('id' =>$id));
            return $result;
        } catch(moodle_exception $e){
            print_r($e);
        }
    }

     public function create_update_trainingtopic($data) {
        global $DB,$USER;
        $record   = new stdClass(); 
        $record->name = "{mlang en}".$data->name."{mlang}"."{mlang ar}".$data->namearabic."{mlang}";
        if($data->id > 0) {
          $record->id = $data->id;   
          $record->timemodified =time();
          $record->usermodified =$USER->id;  
            try{
                $updatedid =$DB->update_record('training_topics', $record);
                return $updatedid;
            } catch(moodle_exception $e){
              print_r($e);
            } 
        } else {
            $record->timecreated =time();
            $record->usercreated =$USER->id;
            $record->name = "{mlang en}".$data->name."{mlang}"."{mlang ar}".$data->namearabic."{mlang}";
            try{
                $createdid =$DB->insert_record('training_topics', $record);
                return $createdid;
            } catch(moodle_exception $e){
              print_r($e);
            }

        }

    }

    public function is_trainingtopic_mapped($topicid){
       global $DB;
       $sql =  "SELECT topic.id 
                FROM {training_topics} as topic
                JOIN {local_trainingprogram} as lot ON FIND_IN_SET(topic.id,lot.trainingtopics) > 0 WHERE topic.id = $topicid";           
        $topic = $DB->record_exists_sql($sql);
        return ($topic) ? 1 : 0;
    }

    public function getalltrainingtopics($stable) {
        global $DB, $SESSION;
        $SESSION->lang =($stable->isArabic == 'true')?'ar':'en';
        $selectsql = " SELECT lo.* FROM {training_topics} lo ";

        if (!empty($stable->programid)) {
            $formsql = " JOIN {local_trainingprogram} ltp ON  concat(',', ltp.trainingtopics, ',') LIKE concat('%,',lo.id,',%') WHERE ltp.id = $stable->programid  "; 
        } else {
            $formsql = " WHERE 1=1 ";
        }

        $ordersql = " ORDER BY lo.id DESC ";
        $topics = $DB->get_records_sql($selectsql.$formsql.$ordersql);
        $topicslist = array();
        $count = 0;
        foreach($topics as $topic) {
                $topicslist[$count]['name'] =strip_tags(format_text($topic->name,FORMAT_HTML));
                $topicslist[$count]['value'] = $topic->id;

            $count++;
        }
        return $topicslist;
    
    }   

    public function createandupdateprogramgoals($sdata)
    {
        global $DB,$USER;
        $data = new stdClass();
        $data->programid = $sdata->programid;
        $data->programgoal =  "{mlang en}".$sdata->programgoal."{mlang}"."{mlang ar}".$sdata->programgoalarabic."{mlang}";
        if($sdata->id > 0) {
            $data->id = $sdata->id;
            $data->usermodified = $USER->id;
            $data->timemodified = time();
            try{
                $record = $DB->update_record('program_goals', $data);;
                return $record;
            } catch(dml_exception $e){
                print_r($e);
            }

        } else {

            $data->usercreated = $USER->id;
            $data->timecreated = time();

            try{
                $record = $DB->insert_record('program_goals', $data);;
                return $record;
            } catch(dml_exception $e){
                print_r($e);
            }
        }
    }

    public function get_all_program_goals($stable, $filterdata) {
        global $DB;
        $programid = $filterdata->programid;
        $selectsql = "SELECT * FROM {program_goals} WHERE programid = $programid ";
        $countsql  = "SELECT COUNT(id) FROM  {program_goals}  WHERE programid = $programid ";
        $totalgoals = $DB->count_records_sql($countsql);
        $formsql =" ORDER BY id DESC ";
        $goals =$DB->get_records_sql($selectsql.$formsql,array(),$stable->start,$stable->length);
        foreach ($goals AS $goal) {
            $goalid = $goal->id;
            $programid = $goal->programid;
            $goal->id = $goalid;
            $goal->programid = $programid;
            $goal->programgoal = wordwrap(format_text($goal->programgoal,FORMAT_HTML),100,"<br>\n");
        }    
        $coursesContext = array(
            "goals" => $goals,
            "totalcount" => $totalgoals,
            "length" => $totalgoals
        );        
        return $coursesContext;
    }
    public function get_trainingflyer($iFilter) {
        global $CFG;
        $lang = ($iFilter->isArabic =='true') ? 'ar' : 'en';
        $programcode = $iFilter->programCode;
        $offeringcode = $iFilter->offeringCode;
        $returnurl = $CFG->wwwroot.'/local/trainingprogram/trainingflyer.php?lang='.$lang.'&programcode='.$programcode.'&offeringcode='.$offeringcode;
        return $returnurl;
    }

    public function user_fullname_case($method = null)
    { 
      
        if(current_language() == 'ar') {

            if($method == 'enrollment') {

                $fullname = "
                    CASE
                        WHEN lc.middlenamearabic IS NOT NULL  AND  lc.thirdnamearabic IS NOT NULL  THEN concat(lc.firstnamearabic,' ',lc.middlenamearabic,' ',lc.thirdnamearabic,' ',lc.lastnamearabic,' ','(',u.email,')')
                        ELSE concat(lc.firstnamearabic,' ',lc.lastnamearabic,' ','(',u.email,')')
                    END AS fullname";


            } else {

                $fullname = "
                    CASE
                        WHEN lc.middlenamearabic IS NOT NULL  AND  lc.thirdnamearabic IS NOT NULL  THEN concat(lc.firstnamearabic,' ',lc.middlenamearabic,' ',lc.thirdnamearabic,' ',lc.lastnamearabic)
                        ELSE concat(lc.firstnamearabic,' ',lc.lastnamearabic)
                    END AS fullname";

            }
            

        } else {

            if($method == 'enrollment') {

                $fullname = "
                   CASE
                        WHEN lc.middlenameen IS NOT NULL  AND  lc.thirdnameen IS NOT NULL THEN concat(u.firstname,' ',lc.middlenameen,' ',lc.thirdnameen,' ',u.lastname,' ','(',u.email,')')
                        ELSE concat(u.firstname,' ',u.lastname,' ','(',u.email,')')
                    END AS fullname";


            } else {

               $fullname = "
                    CASE
                        WHEN lc.middlenameen IS NOT NULL  AND  lc.thirdnameen IS NOT NULL THEN concat(u.firstname,' ',lc.middlenameen,' ',lc.thirdnameen,' ',u.lastname)
                        ELSE concat(u.firstname,' ',u.lastname)
                    END AS fullname";
            }
                     
        }

        return $fullname;
    }

    public function get_tobereplacedusers($query = null,$replacinguserid = null, $rootid = null, $fieldid = null){
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
        $displaying_name = $this->user_fullname_case();
        $enrollmentrecord= $DB->get_record('program_enrollments',[
                                                                        'programid'=>$rootid,
                                                                        'courseid'=>$courseid,
                                                                        'offeringid'=>$fieldid,
                                                                        'userid'=>$replacinguserid
                                                                    ]);                                                         
        $enrolleduserroleinfo = $DB->get_record_sql('SELECT rol.* FROM {role} rol 
        JOIN {role_assignments} rola ON rola.roleid = rol.id
        WHERE rola.userid =:userid and contextid =:contextid',['userid'=>$enrollmentrecord->usercreated,'contextid'=>$systemcontext->id]);

        if($enrollmentrecord->organization > 0) {
            $organization =(int) $enrollmentrecord->organization;
        } elseif($enrolleduserroleinfo->shortname == 'organizationofficial') {
            $organization = $DB->get_field('local_users','organization',array('userid'=>$enrollmentrecord->usercreated));
        } elseif(!is_siteadmin() && has_capability  ('local/organization:manage_organizationofficial',$systemcontext)){
            $user_id = $USER->id;
            $organization = $DB->get_field('local_users','organization',array('userid'=>$user_id));
        } else {
            $organization  = 0;
        }

        if($organization > 0) {
        $where = " WHERE  lc.organization = $organization ";
        } else {
        $where = " WHERE  1=1 ";
        }
        $sql = "SELECT u.id,$displaying_name
                FROM {user} u 
                JOIN {local_users} lc ON lc.userid = u.id
                JOIN {role_assignments} rla ON rla.userid=u.id  
                JOIN {course} as c ON c.id=$courseid
                $where  AND  u.id > 2 AND u.deleted = 0 AND  rla.contextid = $systemcontext->id AND rla.roleid =$traineeroleid AND u.id NOT IN 
                (SELECT pe.userid FROM {program_enrollments} AS pe                         
                WHERE  pe.programid = $rootid  AND pe.courseid = $courseid) AND u.id $concatsql ";

        $order = " ORDER BY u.id DESC limit 50";
        $data = $DB->get_records_sql($sql.$order, $sqlparams);
        $return = array_values(json_decode(json_encode(($data)), true));
        return $return;
    }

    public function program_replacement_process($data) {
        global $DB,$USER;
        $context = context_system::instance();
        $sendingdata =new stdClass();
        $traineeroleid = $DB->get_field('role', 'id', array('shortname' => 'trainee'));
        $courseid=$DB->get_field('local_trainingprogram','courseid', array('id' => $data->rootid));
        $get_existingenrollment_record= $DB->get_record('program_enrollments',[
                                                                               'programid'=>$data->rootid,
                                                                               'offeringid'=>$data->fieldid,
                                                                               'courseid'=>$courseid,
                                                                               'userid'=>$data->fromuserid,
        ]);
        $accessstatus = (new exams)->access_fast_service('replaceservice');
        if(!$accessstatus || $data->costtype == 0 || $data->cangenerateinvoice == 0 || ($data->costtype == 1 && is_siteadmin() && $data->enrollinguserid <= 0 && $data->policyconfirm == 0)){
            $this->program_unenrollment($data->rootid,$data->fieldid,$courseid,$data->fromuserid,$traineeroleid,'replace');
            $this->program_enrollment($data->fieldid,$data->touserid,$traineeroleid,false,$data->enrollinguserid,'replace',false,$get_existingenrollment_record->organization,$get_existingenrollment_record->productid);
            if($get_existingenrollment_record->orgofficial > 0) {
                $DB->execute("UPDATE {program_enrollments} SET orgofficial = $get_existingenrollment_record->orgofficial,enrolltype = $get_existingenrollment_record->enrolltype  WHERE programid=$data->rootid AND offeringid = $data->fieldid AND courseid = $courseid AND userid = $data->touserid");
            }
            return true;
        } else {
            $sendingdata->productid = $data->productid;
            $sendingdata->programid = $data->rootid;
            $sendingdata->offeringid = $data->fieldid;
            $sendingdata->userid =$data->enrollinguserid;
            $sendingdata->productid = $data->productid;
            $sendingdata->entitytype = 'trainingprogram';
            $sendingdata->type = 'replacement';
            $sendingdata->seats = 1;
            $sendingdata->total = $data->replacementfee;
            $sendingdata->payableamount = $data->replacementfee;
            $productdata =  base64_encode(serialize((array)$sendingdata));
            $returndata = (new product)->insert_update_sadad_invoice_record($productdata);
            if($returndata){
                $this->program_unenrollment($data->rootid,$data->fieldid,$courseid,$data->fromuserid,$traineeroleid,'replace');
                $this->program_enrollment($data->fieldid,$data->touserid,$traineeroleid,false,$data->enrollinguserid,'replace',false,$get_existingenrollment_record->organization,$get_existingenrollment_record->productid);
               
                if($get_existingenrollment_record->orgofficial > 0) {
                    $DB->execute("UPDATE {program_enrollments} SET orgofficial = $get_existingenrollment_record->orgofficial,enrolltype = $get_existingenrollment_record->enrolltype  WHERE programid=$data->rootid AND offeringid = $data->fieldid AND courseid = $courseid AND userid = $data->touserid");
                }

            }

            return true;
        }
    }

    public function program_cancel_user($sdata) {
        global $DB,$USER, $CFG;
        $context = context_system::instance();
        $referanceid = (int)$DB->get_field('tool_products','referenceid',['id'=>$sdata->productid]);
        $offering =$DB->get_record('tp_offerings',['id'=>$referanceid]);
        $data =new stdClass();
        $data->programid =$offering->trainingid;
        $data->offeringid = $offering->id;
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
        $data->enrolltype = $sdata->enrolltype;
        $data->actiontype = $sdata->actiontype;

        $policies = new \local_trainingprogram\local\policies('trainingprogram', $sdata->examdate, 'cancel');
        $enrolledroleinfo =  $policies->enrolled_by($sdata->userid,$sdata->productid);
        $sdata->enrolleduserid = $enrolledroleinfo->enrolleduserid;
        $costtype =(int) $DB->get_field('local_trainingprogram','price',['id'=>$offering->trainingid]);

        if($costtype == 0 || $sdata->cangenerateinvoice == 0 || ($enrolledroleinfo->shortname =='organizationofficial' && $sdata->newinvoiceamount <= 0) || $sdata->refundamount <= 0) {

            $traineeroleid = $DB->get_field('role', 'id', array('shortname' => 'trainee'));
            $courseid=$DB->get_field('local_trainingprogram','courseid', array('id' => $offering->trainingid));
            $offeringid = (int)$DB->get_field('tool_products','referenceid',['id'=>$sdata->productid]);
            $offeringrecord =$DB->get_record('tp_offerings',['id'=>$offeringid]);
            $enrolleduserid =(int) $DB->get_field('program_enrollments','usercreated',['programid'=>$offeringrecord->trainingid,'offeringid'=>$offeringid,'courseid'=>$courseid,'userid'=>$sdata->userid]);            
            if ($costtype > 0) {
                $data = (array)$data;
                $data['userid'] =(is_siteadmin() ||  $enrolledroleinfo->shortname =='organizationofficial' ||  $enrolledroleinfo->shortname =='co' ||  $enrolledroleinfo->shortname =='to' ) ? $enrolleduserid  :  $USER->id;

                (new product)->update_org_order_seats_for_cancellation((array)$data);
            }
            $this->program_unenrollment($offering->trainingid,$offering->id,$courseid,$sdata->userid,$traineeroleid,'cancel');
        } else {

            $policies = new \local_trainingprogram\local\policies($sdata->entitytype, $sdata->examdate, 'cancel');
            $policies->cancel_process($data);
            
        }

        if(($sdata->cangenerateinvoice != 0) && ($sdata->newinvoiceamount == 0) && ($sdata->newamount == 0) && ($enrolledroleinfo->shortname =='organizationofficial')){
           (new product)->invoice_record_for_0_cancellation($sdata);
        }
        $returnurl = (is_siteadmin() || has_capability('local/organization:manage_organizationofficial', context_system::instance())) ? $CFG->wwwroot.'/local/trainingprogram/programenrolleduserslist.php?programid='.$offering->trainingid.'&offeringid='.$offering->id:  $CFG->wwwroot.'/local/trainingprogram/index.php'; 
        return $returnurl;

    }  
    
     public function cpd_data($stable, $filterdata) {
        global $DB, $PAGE, $OUTPUT;
        
        $systemcontext = context_system::instance();
        $get_cpdid=$DB->get_fieldset_sql('select cpdid from {local_cpd_training_programs} where programid= '.$stable->programid.'');
        //$cpdids=implode(",", $get_cpdid);
         $cpdids = (implode(",", $get_cpdid)) ? implode(",", $get_cpdid) : 0;
        $lang = current_language();
         $examfullname = ($lang == 'ar') ? 'ex.examnamearabic' : 'ex.exam';
               $selectsql ="SELECT ctp.id as ctpid,cpd.id,cpd.examid,$examfullname as cpdname,ctp.creditedhours as creditedhrs
               FROM {local_cpd} as cpd
               JOIN {local_exams} as ex
               ON cpd.examid=ex.id
               JOIN {local_cpd_training_programs} as ctp
               ON cpd.id = ctp.cpdid
                " ;
               $countsql  = "SELECT COUNT(ctp.id) FROM {local_cpd} as cpd
               JOIN {local_exams} as ex
                ON cpd.examid=ex.id
               JOIN {local_cpd_training_programs} as ctp
               ON cpd.id = ctp.cpdid 
               ";
        $formsql  = "  WHERE ex.status=1 AND cpd.id IN($cpdids) AND ctp.programid = $stable->programid ";
        if(isset($filterdata->search_query) && trim($filterdata->search_query) != ''){
            $formsql .= " AND (ex.exam LIKE :exam OR ex.examnamearabic LIKE :examnamearabic)";
            $searchparams = array('exam' => '%'.trim($filterdata->search_query).'%','examnamearabic' => '%'.trim($filterdata->search_query).'%');
        }else{
            $searchparams = array();
        }   
        $params = array_merge($searchparams);
        $totalcompetency = $DB->count_records_sql($countsql.$formsql, $params);
        $formsql .=" ORDER BY ctp.id DESC";
        $addedcompetencies = $DB->get_records_sql($selectsql.$formsql, $params, $stable->start,$stable->length);  
        //print_r($addedcompetencies);
        //exit;   
        foreach ($addedcompetencies AS $competenciestype) {
            $competenciestype->cpdname = $competenciestype->cpdname;
            $competenciestype->creditedhrs = $competenciestype->creditedhrs;
            $competenciestype->ctpid = $competenciestype->ctpid;
        }
        $coursesContext = array(
            "acompetencies" => $addedcompetencies,
            "totalcount" => $totalcompetency,
            "length" => $totalcompetency
        );        
        return $coursesContext;
    }

    public function from_unixtime_for_live_entities($field) {

        $field = trim($field);
        $record = "
        CASE
            WHEN $field < 2147483647  THEN  $field >= UNIX_TIMESTAMP(NOW()) 
            ELSE $field > 2147483647
        END ";

        return $record;
    }

    public function from_unixtime_for_expired_entities($field) {

        $field = trim($field);
        $record = "
        CASE
            WHEN $field < 2147483647  THEN  $field < UNIX_TIMESTAMP(NOW()) 
            ELSE CURDATE()  >  2147483647
        END ";

        return $record;
    }

    public function publishorunpublish_enitity($fieldid,$code,$entitytype,$actiontype) {
        global $DB;
        $table = ($entitytype == 'offering') ? 'tp_offerings' : 'local_exam_profiles' ;
        $updatingdata = new stdClass();
        $updatingdata->id = $fieldid;
        $updatingdata->published = ($actiontype == 'publish') ? 1 : 0;
        try{
            $record = $DB->update_record($table, $updatingdata);;
            return $record;
        } catch(dml_exception $e){
            print_r($e);
        }
    }

    public function cancel_entity($data) {
        global $DB;
        if ($data->entitytype == 'offering') {
            $data->courseid=$DB->get_field('local_trainingprogram','courseid', array('id' => $data->rootid));
        }
        $response = $this->process_cancel_entity($data);
        return $response;
    }
    public function process_cancel_entity($data) {
        global $DB,$USER;
        $systemcontext = context_system::instance();
        $inserttable = ($data->entitytype == 'offering') ? 'tp_offerings' : 'local_events';

        if($data->currentuser == 'admin' || (($data->currentuser == 'ts' || $data->currentuser == 'eventmanager') && $data->requesttype == 'approvecancelrequest')) {
            if($data->entitytype == 'offering') { 
                $sql_query = "SELECT * FROM {program_enrollments} WHERE programid=:programid AND offeringid =:offeringid AND courseid =:courseid AND enrolstatus =:enrolstatus";
                $enrolledrecords = $DB->get_records_sql( $sql_query,['programid'=>$data->rootid,'offeringid'=>$data->fieldid,'courseid'=>$data->courseid,'enrolstatus'=>1]);
            } else {
                $sql = "SELECT * 
                          FROM {local_event_attendees} lea
                         WHERE lea.eventid = $data->fieldid AND lea.enrolstatus = 1 ";
                $enrolledrecords = $DB->get_records_sql($sql);
            }
            $count = 0;
            $amount = 0;
            if($enrolledrecords){
                foreach($enrolledrecords AS $enrolledrecord) {
                    ++$count;
                    if($data->costtype == 0) {
                        if($data->entitytype == 'offering') { 
                           $this->program_unenrollment($data->rootid,$data->fieldid,$data->courseid,$enrolledrecord->userid,$enrolledrecord->roleid,'cancelentity');
                        } elseif($data->entitytype == 'event') {
                            (new \local_events\events())->unenroll_event($data->fieldid,$enrolledrecord->userid,'cancelentity');
                        }
                        $updatingdata = new stdClass();
                        $updatingdata->id = $data->fieldid;
                        $updatingdata->cancelled = (new tp)->get_cancelledstatus($data);
                        $updatingdata->cancelledby = $USER->id;
                        $updatingdata->cancelledate = time();
                        try{
                            $record = $DB->update_record($inserttable, $updatingdata);
                        } catch(dml_exception $e){
                            print_r($e);
                        }
                    } else {
                        $etype = 'cancel'.$data->entitytype;
                        if($data->entitytype == 'offering') { 
                            $enrolleduser =(int) $DB->get_field('program_enrollments','usercreated',['programid'=>$data->rootid,
                            'courseid'=>$data->courseid,
                            'offeringid'=>$data->fieldid,
                            'userid'=>$enrolledrecord->userid
                            ]);
                        } elseif($data->entitytype == 'event') {
                            $enrolleduser =(int) $DB->get_field('local_event_attendees','usercreated',['eventid'=>$data->fieldid, 'userid'=>$enrolledrecord->userid]);
                        }
                        $enrolleduserroleinfo = $DB->get_record_sql('SELECT rol.* FROM {role} rol 
                                                JOIN {role_assignments} rola ON rola.roleid = rol.id
                                                WHERE rola.userid =:userid and contextid =:contextid',['userid'=>$enrolleduser,'contextid'=>$systemcontext->id]);
                        $traineeroleid = $DB->get_field('role', 'id', array('shortname' => 'trainee'));
                        $orgofficialroleid = $DB->get_field('role', 'id', array('shortname' => 'organizationofficial'));
                        if (user_has_role_assignment($enrolleduser,$traineeroleid,$systemcontext->id)) {
    
                            $productid = $data->productid;
                            $transaction = $DB->get_record('tool_user_order_payments', ['productid' => $productid,'fieldid' => $data->fieldid, 'userid' => $enrolledrecord->userid, 'tablename' =>  $inserttable]);
                            $transactionid = $transaction->telrid;
                            $amount = $transaction->amount;
                            if($amount > 0) {
                                $response = (new telr)->refund($transactionid, $amount);
                                $DB->insert_record('local_cancel_logs', ['entitytype'=> $etype, 'productid'=> $productid, 'refundamount'=> $amount, 'userid'=> $enrolledrecord->userid, 'policy'=> 1, 'usercreated' => $USER->id, 'timecreated'=> time()]);
                                if($response) {
                                    if($data->entitytype == 'offering') { 
                                        $this->program_unenrollment($data->rootid,$data->fieldid,$data->courseid,$enrolledrecord->userid,$enrolledrecord->roleid,'cancelentity');
                                    } elseif($data->entitytype == 'event') {
                                        (new \local_events\events())->unenroll_event($data->fieldid,$enrolledrecord->userid,'cancelentity');
                                    }
                                    $updatingdata = new stdClass();
                                    $updatingdata->id = $data->fieldid;
                                    $updatingdata->cancelled = (new tp)->get_cancelledstatus($data);
                                    $updatingdata->cancelledby = $USER->id;
                                    $updatingdata->cancelledate = time();
                                    try{
                                        $record = $DB->update_record($inserttable, $updatingdata);
                                    } catch(dml_exception $e){
                                        print_r($e);
                                    }
                                }
                            }
                        } else if(user_has_role_assignment($enrolleduser,$orgofficialroleid,$systemcontext->id)) {
                            $user_id = $enrolleduser;
                            $invoice  = (new telr)->get_pending_invoice($data->productid,$user_id);
                            if($invoice) {
                               $sadadrecord = $DB->get_record('tool_product_sadad_invoice', ['productid' => $data->productid, 'invoice_number' => $invoice->invoice_number]);
                               if($sadadrecord) {
                                    $amount = 0;
                                    $new_invoice_number = time().$data->productid.$invoice->id;
                                    if ((int)$invoice->amount >= $amount) {
                                        if($invoice->invoice_number) {
                                            $record = new stdClass();
                                            $record->productid =$data->productid;
                                            $record->userid = $enrolledrecord->usercreated;
                                            $record->telrid = 0;
                                            $record->invoiceNumber =$new_invoice_number;
                                            $record->invoice_number =$new_invoice_number;
                                            $record->type = 'cancel';
                                            $record->amount =$amount;
                                            $record->payableamount =$amount;
                                            $record->status =-1;
                                            $record->payment_status =0;
                                            $record->timecreated = time();
                                            $record->usercreated = $USER->id;
                                            $record->registrationNo =0;
                                            $record->companyName =null;
                                            $record->commisionerName =null;
                                            $record->commisionerId =null;
                                            $record->commissionerMobileNo =null;
                                            $record->commissionerEmail =null;
                                            try{
                                                $insertid = $DB->insert_record('tool_product_sadad_invoice', $record);
                                                if($insertid) {
                                                    $record->sadadid = $insertid;
                                                    (new product)->insert_update_sada_invoice_logs($record);
                                                    (new trainingprogram)->insert_cancel_record_in_org_order_payments($record,$data);
                                                    (new telr)->void_invoice($invoice->invoice_number);
                                                    if($data->entitytype == 'offering') { 
                                                        $this->program_unenrollment($data->rootid,$data->fieldid,$data->courseid,$enrolledrecord->userid,$enrolledrecord->roleid,'cancelentity');
                                                    } elseif($data->entitytype == 'event') {
                                                        (new \local_events\events())->unenroll_event($data->fieldid,$enrolledrecord->userid,'cancelentity');
                                                    }
                                                    $updatingdata = new stdClass();
                                                    $updatingdata->id = $data->fieldid;
                                                    $updatingdata->cancelled = (new tp)->get_cancelledstatus($data);
                                                    $updatingdata->cancelledby = $USER->id;
                                                    $updatingdata->cancelledate = time();
                                                    try{
                                                        $record = $DB->update_record($inserttable, $updatingdata);
                                                    } catch(dml_exception $e){
                                                        print_r($e);
                                                    }
                                                }
                                                $count = 0;
                                            } catch(dml_exception $e){
                                                print_r($e);
                                            }
                                        } else {
                                            return get_string('nogeneratedinvoice','local_trainingprogram');
                                        }
                                        
                                    } else {
                                        $count = 0;
                                        if($data->entitytype == 'offering') { 
                                            $this->program_unenrollment($data->rootid,$data->fieldid,$data->courseid,$enrolledrecord->userid,$enrolledrecord->roleid,'cancelentity');
                                        } elseif($data->entitytype == 'event') {
                                            (new \local_events\events())->unenroll_event($data->fieldid,$enrolledrecord->userid,'cancelentity');
                                        }
                                        // Triggering event for orgoff refund
                                        $eventparams = array('context' => context_system::instance(),
                                        'objectid'=>$sadadrecord->id,
                                        'other'=>array('oldsadadid' => $sadadrecord->id,
                                                        'oldinvoiceamount' => $sadadrecord->amount,
                                                        'newinvoiceamount' => $amount,
                                                        'orguserid' => $sadadrecord->userid,
                                                        'productid' => $sadadrecord->productid,
                                                        'type' => $etype)
                                        );
                                        $event = \local_trainingprogram\event\orgoff_refundlogs_created::create($eventparams);
                                        $event->trigger();

                                        $updatingdata = new stdClass();
                                        $updatingdata->id = $data->fieldid;
                                        $updatingdata->cancelled = (new tp)->get_cancelledstatus($data);                                  
                                        $updatingdata->cancelledby = $USER->id;
                                        $updatingdata->cancelledate = time();
                                        try{
                                            $record = $DB->update_record($inserttable, $updatingdata);
                                        } catch(dml_exception $e){
                                            print_r($e);
                                        }
                                    }
                                } else {
                                    return get_string('notransactionfound','local_trainingprogram');
                                }
                            } else {

                                $h_sql = "SELECT * FROM {tool_product_sadad_invoice} WHERE productid =:productid AND
                                                                                           userid =:userid AND
                                                                                           status =:sstatus AND
                                                                                           seats =:sseats AND
                                                                                           payableamount =:payableamount AND
                                                                                           amount =:amount";
                                $hascancelledinvoice = $DB->get_record_sql($h_sql, ['productid' => $data->productid, 
                                                                                    'userid' => $user_id,
                                                                                    'sstatus'=>-1,
                                                                                    'sseats'=>0,
                                                                                    'payableamount'=>0,
                                                                                    'amount'=>0,
                                                                                    ]);
                                if($hascancelledinvoice){

                                    if($data->entitytype == 'offering') { 
                                        $this->program_unenrollment($data->rootid,$data->fieldid,$data->courseid,$enrolledrecord->userid,$enrolledrecord->roleid,'cancelentity');
                                    } elseif($data->entitytype == 'event') {
                                        (new \local_events\events())->unenroll_event($data->fieldid,$enrolledrecord->userid,'cancelentity');
                                    }  
                                    $updatingdata = new stdClass();
                                    $updatingdata->id = $data->fieldid;
                                    $updatingdata->cancelled = (new tp)->get_cancelledstatus($data);                   
                                    $updatingdata->cancelledby = $USER->id;
                                    $updatingdata->cancelledate = time();
                                    try{
                                        $record = $DB->update_record($inserttable, $updatingdata);
                                    } catch(dml_exception $e){
                                        print_r($e);
                                    } 
                                }  else {
                                    return get_string('nopendinginovice','local_trainingprogram');
                                }                                               
                            }
                        } else {
                            if($data->entitytype == 'offering') { 
                                $this->program_unenrollment($data->rootid,$data->fieldid,$data->courseid,$enrolledrecord->userid,$enrolledrecord->roleid,'cancelentity');
                            } elseif($data->entitytype == 'event') {
                                (new \local_events\events())->unenroll_event($data->fieldid,$enrolledrecord->userid,'cancelentity');
                            }  
                            $updatingdata = new stdClass();
                            $updatingdata->id = $data->fieldid;
                            $updatingdata->cancelled = (new tp)->get_cancelledstatus($data);                   
                            $updatingdata->cancelledby = $USER->id;
                            $updatingdata->cancelledate = time();
                            try{
                                $record = $DB->update_record($inserttable, $updatingdata);
                            } catch(dml_exception $e){
                                print_r($e);
                            } 
                        }
                    }
                }
                    
            } else {
                $updatingdata = new stdClass();
                $updatingdata->id = $data->fieldid;
                $updatingdata->cancelled = (new tp)->get_cancelledstatus($data);
                $updatingdata->cancelledby = $USER->id;
                $updatingdata->cancelledate = time();
                try{
                    $record = $DB->update_record($inserttable, $updatingdata);
                } catch(dml_exception $e){
                    print_r($e);
                }
            }
        } else {
            $updatingdata = new stdClass();
            $updatingdata->id = $data->fieldid;
            $updatingdata->cancelled = (new tp)->get_cancelledstatus($data);          
                if($updatingdata->cancelled == -1){
                    $trainingofficial = $DB->get_record('local_users',array('userid'=>$USER->id));
                    $tpid = $DB->get_field('tp_offerings','trainingid',array('id'=>$data->fieldid));
                    $trainingprogram = $DB->get_record('local_trainingprogram',array('id'=>$tpid));

                    $updatingdata->program_tofullnameen =  $trainingofficial->firstname.''. $trainingofficial->middlenameen.''.$trainingofficial->thirdnameen.'' .$trainingofficial->lastname;
                    $updatingdata->program_tofullnamear =  $trainingofficial->firstnamearabic.''. $trainingofficial->middlenamearabic.''.$trainingofficial->thirdnamearabic.'' .$trainingofficial->lastnamearabic;
                    $updatingdata->program_nameen =  $trainingprogram->name;
                    $updatingdata->program_namear = $trainingprogram->namear;
                    (new \local_trainingprogram\notification())->trainingprogram_notification('trainingprogram_cancelrequest', $touser= null,$fromuser=$USER,$updatingdata,$waitinglistid=0);
    
                }

             

            $updatingdata->cancelledby = $USER->id;
            $updatingdata->cancelledate = time();
            try{
                $record = $DB->update_record($inserttable, $updatingdata);
            } catch(dml_exception $e){
                print_r($e);
            }
           
        }
    }
    public function get_cancelledstatus($data) {

        $status = 0;
        if($data->requesttype == 'cancelentity') {
            if($data->currentuser == 'admin') {
                $status = 2;
            } else {
                if($data->currentuser == 'to' && $data->hasenrollments == 1){
                    $status = -1;
                } else {
                    $status = 1;
                }
            }
        } elseif($data->requesttype == 'approvecancelrequest') {
            if($data->currentuser == 'financial_manager') {
                $status = 1;
            } else {
                $status = 2;
            }
        } else {
            $status = 3;
        }
        return $status;

    }

    public function entitycancellationrequests($entitytype) {
        global $PAGE;
        $renderer = $PAGE->get_renderer('local_trainingprogram');
        $filterparams  = $renderer->get_entitycancellationrequests(true);
        $filterparams['submitid'] = 'form#filteringform';
        $filterparams['widthclass'] = 'col-md-12';
        $filterparams['entitytype'] = $entitytype;
        $filterparams['placeholder'] = get_string('serch_request','local_trainingprogram');
        $globalinput=$renderer->global_filter($filterparams);
        $entitycancellationrequests = $renderer->get_entitycancellationrequests();
        $filterparams['entitycancellationrequests'] = $entitycancellationrequests;
        $filterparams['globalinput'] = $globalinput;
        $renderer->listofcellationrequests($filterparams);

    } 

    public function get_listof_listofcellationrequests($stable, $filterdata,$dataoptions) {
        global $DB, $PAGE, $OUTPUT, $CFG, $USER;
        $systemcontext = context_system::instance();
        $currentlang = current_language();
        $entitytype = json_decode($dataoptions)->entitytype;
        if($entitytype == 'offering') {
            $selectsql = "SELECT tpo.id,tpo.trainingid AS rootid, tpo.code AS entitycode, tp.price AS costtype,tp.sellingprice,
            tp.name AS rootnameen,tp.namearabic AS rootnamear,
            tpo.cancelled,tpo.cancelledby,tpo.cancelledate
            FROM {tp_offerings} tpo JOIN {local_trainingprogram} tp ON tp.id = tpo.trainingid "; 
            $countsql  = "SELECT COUNT(tpo.id)
            FROM {tp_offerings} tpo JOIN {local_trainingprogram} tp ON tp.id = tpo.trainingid ";
            $formsql = " WHERE 1=1 ";
            if(!is_siteadmin() &&  has_capability('local/organization:manage_financial_manager', context_system::instance())) {
                $formsql .= " AND tpo.cancelled = -1 ";
            }
            if(!is_siteadmin() &&  has_capability('local/organization:training_supervisor', context_system::instance())) {
                $formsql .= " AND tpo.cancelled = 1 ";
            }
           
        } else {
            $selectsql = "SELECT le.id, le.id AS rootid, le.code AS entitycode,le.price AS costtype,le.sellingprice,
            le.title AS rootnameen,le.titlearabic AS rootnamear, le.cancelled,le.cancelledby,le.cancelledate
                            FROM {local_events} le "; 
            $countsql  = "SELECT COUNT(le.id)
                            FROM {local_events} le ";
            $formsql = " WHERE 1=1 AND le.cancelled = 1 ";
        }
        if  (isset($filterdata->search_query) && trim($filterdata->search_query) != ''){
            $code = ($entitytype == 'offering') ? 'tpo.code': 'le.code';
            $rootnameen = ($entitytype == 'offering') ? 'tp.name': 'le.title';
            $rootnamear = ($entitytype == 'offering') ? 'tp.namearabic': 'le.titlearabic';
            $formsql .= " AND ( $code LIKE :entitycode  OR $rootnameen LIKE :rootnameen  OR  $rootnamear LIKE :rootnamear ) ";
            $searchparams = array(
                'entitycode' => '%'.trim($filterdata->search_query).'%',
                'rootnameen' => '%'.trim($filterdata->search_query).'%',
                'rootnamear' => '%'.trim($filterdata->search_query).'%',
            );
        } else {
            $searchparams = array();
        }
        $params = array_merge($searchparams);
        $totalrequests = $DB->count_records_sql($countsql.$formsql,$params);
        $formsql .=" ORDER BY id DESC";
        $cancelrequests = $DB->get_records_sql($selectsql.$formsql, $params, $stable->start,$stable->length);
        $cancelrequestslist = array();
        $count = 0;
        foreach($cancelrequests as $cancelrequest) {
            $userrecord = $DB->get_record('local_users',['userid'=>$cancelrequest->cancelledby]);
            if( $currentlang == 'ar' && !empty($userrecord->firstnamearabic)){
                $firstname = $userrecord->firstnamearabic;
            }else{
                $firstname = $userrecord->firstname;
            }
            if( $currentlang == 'ar' && !empty($userrecord->lastnamearabic)){
                $lastname = $userrecord->lastnamearabic;
            }else{
                $lastname = $userrecord->lastname;
            }
            if( $currentlang == 'ar' && !empty($userrecord->middlenamearabic)){
                $middlename = $userrecord->middlenamearabic;
            }else{
                $middlename = $userrecord->middlenameen;
            }
            if( $currentlang == 'ar' && !empty($userrecord->thirdnamearabic)){
                $thirdname = $userrecord->thirdnamearabic;
            }else{
                $thirdname = $userrecord->thirdnameen;
            }
            $category = ($entitytype == 'offering')? 1: 3;
            $productid = (int)$DB->get_field_sql('SELECT id FROM {tool_products} WHERE referenceid = '.$cancelrequest->id.' AND category = '.$category.'');
            $reasonforcancel = $DB->get_field_sql('SELECT reason FROm {local_cancel_logs} WHERE entitytype =:etype AND productid =:productid AND userid =:userid  ORDER BY id DESC LIMIT 1 ',['etype'=>$entitytype,'productid'=>$productid ,'userid'=>0]);
            $cancelrequestslist[$count]["entityid"] = $cancelrequest->id;
            $cancelrequestslist[$count]["rootid"] =$cancelrequest->rootid;
            $cancelrequestslist[$count]["productid"] =$productid;
            $cancelrequestslist[$count]["reasonforcancel"] =($reasonforcancel) ? wordwrap(strip_tags($reasonforcancel),50,"<br>\n"):'';
            $entityname = ($currentlang == 'ar') ? $cancelrequest->rootnamear:$cancelrequest->rootnameen;
            $currentuserroleinfo = $DB->get_record_sql('SELECT rol.* FROM {role} rol 
            JOIN {role_assignments} rola ON rola.roleid = rol.id
            WHERE rola.userid =:userid and contextid =:contextid',['userid'=>$USER->id,'contextid'=>$systemcontext->id]);
            $cancelrequestslist[$count]['currentuser'] =(is_siteadmin()) ? 'admin' : $currentuserroleinfo->shortname;
            $cancelrequestslist[$count]["entityname"] =$entityname;
            $cancelrequestslist[$count]["entitycode"] =$cancelrequest->entitycode;
            $cancelrequestslist[$count]["requestat"] = userdate($cancelrequest->cancelledate,'%Y-%m-%d %H:%M:%S');
            $cancelrequestslist[$count]["requestby"] =  $firstname.' '.$middlename.' '.$thirdname.' '.$lastname;
            $cancelrequestslist[$count]["costtype"] = ($cancelrequest->costtype > 0 && $cancelrequest->sellingprice > 0) ? 1 : 0;
            $cancelrequestslist[$count]["actionview"]=($cancelrequest->cancelled == -1 || $cancelrequest->cancelled == 1) ? true : false;
            $count++;
        }
        $coursesContext = array(
            "hascourses" => $cancelrequestslist,
            "totalrequests" => $totalrequests,
            "length" => count($cancelrequestslist)
        );
        return $coursesContext;
    }

    public function unpublishedentitieslist($entitytype) {
        global $PAGE;
        $renderer = $PAGE->get_renderer('local_trainingprogram');
        $filterparams  = $renderer->get_unpublishedentitieslist(true);
        $filterparams['submitid'] = 'form#filteringform';
        $filterparams['widthclass'] = 'col-md-12';
        $filterparams['entitytype'] = $entitytype;
        $filterparams['placeholder'] = get_string('serch_request','local_trainingprogram');
        $globalinput=$renderer->global_filter($filterparams);
        $unpublishedentitieslist = $renderer->get_unpublishedentitieslist();
        $filterparams['unpublishedentitieslist'] = $unpublishedentitieslist;
        $filterparams['globalinput'] = $globalinput;
        $renderer->listofunpublishedentitieslist($filterparams);

    } 

    public function programenrollmentsview($programid,$offeringid,$roleid,$cusers,$organization,$orgofficial) {
        global $DB, $PAGE, $OUTPUT;
        $renderer = $PAGE->get_renderer('local_trainingprogram');
        $filterparams  = $renderer->get_enrollprogram(true);
        $filterparams['submitid'] = 'form#filteringform';
        $filterparams['widthclass'] = 'col-md-12';
        $filterparams['placeholder'] = get_string('serch_identityno','local_trainingprogram');
        $globalinput=$renderer->global_filter($filterparams);
        $bulkenrolldata = $renderer->get_enrollprogram();
        $filterparams['bulkenrolldata'] = $bulkenrolldata;
        $filterparams['globalinput'] = $globalinput;
        $renderer->listofenrollenrolledprograms($filterparams);

    }

     public function get_listof_programenrollments($stable, $filterdata,$dataoptions) {
        global $DB, $PAGE, $OUTPUT, $CFG, $USER;
        $currentlang= current_language();
        $cusers =  (json_decode($dataoptions)->cusers) ? base64_decode(json_decode($dataoptions)->cusers) : 0;
        $offeringid =  (json_decode($dataoptions)->offeringid) ? json_decode($dataoptions)->offeringid : 0;
        $programid =  (json_decode($dataoptions)->programid) ? json_decode($dataoptions)->programid : 0;
        $roleid =  (json_decode($dataoptions)->roleid) ? json_decode($dataoptions)->roleid : 0;
        $organization =  (json_decode($dataoptions)->organization) ? json_decode($dataoptions)->organization : 0;
        $orgofficial =  (json_decode($dataoptions)->orgofficial) ? json_decode($dataoptions)->orgofficial : 0;
        $lang = current_language();
        $displaying_name = (new trainingprogram)->user_fullname_case();
        $selectsql = "SELECT u.id,$displaying_name,u.idnumber  
        FROM {user} u 
        JOIN {local_users} lc ON lc.userid = u.id "; 
        $countsql  = " SELECT COUNT(u.id) 
        FROM {user} u JOIN {local_users} lc ON lc.userid = u.id ";
        $formsql = " WHERE 1=1 AND lc.approvedstatus = 2 AND u.deleted = 0 AND lc.deleted = 0 AND 
        lc.bulkenrolltype ='program' AND (lc.bulkenrollstatus = 0 || lc.bulkenrollstatus = 2)  AND u.id IN($cusers)";
        if(isset($filterdata->search_query) && trim($filterdata->search_query) != ''){
            $formsql .= " AND (u.idnumber LIKE :idnumber) ";
            $searchparams = array(
                'idnumber' => '%'.trim($filterdata->search_query).'%'
            );
        } else {
            $searchparams = array();
        }
        $params = array_merge($searchparams);
        $totalrequests = $DB->count_records_sql($countsql.$formsql,$params);
        $formsql .=" ORDER BY u.id DESC";
        $requests = $DB->get_records_sql($selectsql.$formsql, $params, $stable->start,$stable->length);
        $confirmrequestslist = array();
        $count = 0;
        foreach($requests as $request) {
            $program = $DB->get_record('local_trainingprogram',['id'=>$programid]);
            $offering = $DB->get_record('tp_offerings',['id'=>$offeringid]);
            $confirmrequestslist[$count]["userid"] = $request->id;
            $confirmrequestslist[$count]["identityno"] = $request->idnumber;
            $confirmrequestslist[$count]["fullname"] =$request->fullname;
            $confirmrequestslist[$count]["programname"] =($currentlang == 'ar') ? $program->namearabic  : $program->name;
            $confirmrequestslist[$count]["programcode"] =$program->code;
            $confirmrequestslist[$count]["offeringcode"] =$offering->code;
            $confirmrequestslist[$count]["fee"] =$offering->sellingprice;
            $confirmrequestslist[$count]["startdate"]  = userdate($offering->startdate,'%Y-%m-%d ').' '.gmdate("H:i",$offering->time);
            $confirmrequestslist[$count]["enddate"] = userdate($offering->enddate,'%Y-%m-%d ').' '.gmdate("H:i",($offering->time+$offering->duration));
            $errormessage = $this->bulk_enroll_user_validations($programid,$offeringid,$request->id,$organization);
            if(!$errormessage) {
                $DB->execute("UPDATE {local_users} SET bulkenrollstatus = 0 WHERE userid = $request->id AND bulkenrollstatus IN(1,2)");
            }
            $confirmrequestslist[$count]["actionview"]=(!$errormessage) ? true : false;
            $confirmrequestslist[$count]["errormessage"] =($errormessage) ? array_values($errormessage) : [];
            $count++;
        }
        $coursesContext = array(
            "hascourses" => $confirmrequestslist,
            "totalprogramenroll" => $totalrequests,
            "length" => count($confirmrequestslist)
        );
        return $coursesContext;
    } 

    public function bulk_enroll_user_validations($programid,$offeringid,$userid,$organization) {
        global $DB;
        $errormessage =[];
        $systemcontext = context_system::instance();
        $offering = $DB->get_record('tp_offerings',['id'=>$offeringid]);
         //User exist but belong to another organization or empty organization
        if(!is_siteadmin() && has_capability('local/organization:manage_organizationofficial',$systemcontext)) {
            $traineeuserorg =(int) $DB->get_field('local_users','organization',['userid'=>$userid]);
            if($traineeuserorg == 0 || ($traineeuserorg > 0 && ($traineeuserorg != $organization))) {
                $errormessage[]['message']= get_string('noturorganization','local_exams');
                $DB->execute("UPDATE {local_users} SET bulkenrollstatus = 2 WHERE userid = $userid.");
            }
        }
        //Usee Already has a certificate in this program
         $alreadyhascertificate = $DB->record_exists_sql('SELECT * FROM {tool_certificate_issues} WHERE userid=:userid AND moduleid=:moduleid  AND moduletype =:moduletype',['userid'=>(int)$userid,'moduleid'=>$offeringid,'moduletype'=>'trainingprogram']);
         if($alreadyhascertificate) {
             $errormessage[]['message']= get_string('alreadyhascertificate','local_trainingprogram');
             $DB->execute("UPDATE {local_users} SET bulkenrollstatus = 2 WHERE userid = $userid.");
   
         }   
        //User already enrolled
        $alreadyenrolled = $DB->record_exists_sql('SELECT * FROM {program_enrollments} WHERE programid=:programid AND userid=:userid  AND enrolstatus = 1',['programid'=>$programid,'userid'=>(int)$userid]);
        if($alreadyenrolled) {
            $errormessage[]['message']= get_string('alreadyenrolled','local_trainingprogram');
            $DB->execute("UPDATE {local_users} SET bulkenrollstatus = 2 WHERE userid = $userid.");
  
        }  
        
        //User already enrolled in another activity in same date and time
        if($offering->trainingmethod !='elearning') {
            $date = ($offering->startdate+$offering->time);
            $isuserenrolled = $this->user_enrolled_another_activity($userid,$date);
            if($isuserenrolled) {
                $errormessage[]['message']= get_string('alreadyenrolledtoanother','local_trainingprogram');
                $DB->execute("UPDATE {local_users} SET bulkenrollstatus = 2 WHERE userid = $userid.");
      
            }  
            
        }

        return $errormessage;
        
    }

    public function user_enrolled_another_activity($userid,$date) {
        global $DB;

        $result = 0;
        $records = $DB->get_records_sql("SELECT * FROM {local_exam_userhallschedules} WHERE userid =:userid AND enrolstatus =:enrolstatus AND enrolltype !=1 ",['userid'=>$userid,'enrolstatus'=>1]);
        foreach($records AS $record) {
            $scheduleid =(int) $record->hallscheduleid;
            if($scheduleid > 0) {
                $scheduledetails = $DB->get_record('hallschedule', ['id'=>$scheduleid]);
                $schedulestarttime = $scheduledetails->starttime;
                $startedtime = (strtotime(userdate($record->examdate, '%d-%m-%Y'))+userdate((($schedulestarttime)), '%H')*3600 + userdate(($schedulestarttime), '%M')*60);
                if($startedtime == $date) {
                    $result = 1;
                }else {
                    $result = 0;
                }
            }else {
                $result = 0;
            }
        }
        //programs

        $records = $DB->get_records_sql("SELECT * FROM {program_enrollments} WHERE userid =:userid AND enrolstatus =:enrolstatus AND enrolltype !=1",['userid'=>$userid,'enrolstatus'=>1]);

        foreach($records AS $record) {
            $offering = $DB->get_record('tp_offerings',['id'=>$record->offeringid]);
           
            if($offering) {
                $offeringdate = ($offering->startdate+$offering->time);
     
                if($offeringdate == $date) {
                    $result = 1;
                } else {
                    $result = 0;
                }
            } else {
                $result = 0;
            }
        }

        //events
        $records = $DB->get_records_sql('SELECT * FROM {local_event_attendees} WHERE userid =:userid AND enrolstatus =:enrolstatus',['userid'=>$userid,'enrolstatus'=>1]);
        foreach($records AS $record) {
            $event = $DB->get_record('local_events',['id'=>$record->eventid]);
            if($event) {
                $eventdate = ($event->startdate+$event->slot);
                if($eventdate == $date) {
                    $result = 1;
                }else {
                    $result = 0;
                }
            }else {
                $result = 0;
            }
        }

        return $result;

    }
    public function add_update_program_activities($data) {
        global $DB, $USER;
        $offering = $DB->get_record('tp_offerings',['id' => $data->offeringid]);
        $program = $DB->get_record('local_trainingprogram',['id' => $offering->trainingid]);
        $offering->prequiz = $data->prequiz;
        $offering->postquiz = $data->postquiz;
        if($data->attendance) {
            $sessionidsql =" SELECT fs.sessionid FROM {offering_sessions} fs
            JOIN {attendance_sessions} ass ON ass.id = fs.sessionid 
            JOIN {course_modules} cm ON cm.course = fs.courseid
            JOIN {course_sections} cs ON cs.id = cm.section
            JOIN {attendance} a ON a.id = cm.instance AND ass.attendanceid = a.id
            WHERE cs.id = $offering->sections AND fs.offeringid = $offering->id 
            AND cm.course = $program->courseid AND cm.module = (SELECT id FROM {modules} WHERE name = 'attendance')";
            $sessionids = $DB->get_records_sql($sessionidsql);
            if(empty($sessionids)) {
                $start = $offering->startdate + $offering->time;
                $end = $offering->startdate + $offering->endtime;
                $duration =($offering->trainingmethod == 'elearning') ? 0 : abs($start-$end);
                $starttime = ceil($offering->startdate+$offering->time);
                $date_duration = $offering->enddate - $offering->startdate;
                $days_between = ceil(abs($date_duration) / 86400);
                for($i=0; $i <= $days_between; $i++){
                    $attendance = $DB->get_record('attendance', ['id' =>$data->attendance]);
                    $cm = $DB->get_record_sql(" SELECT cm.* FROM {course_modules} AS cm JOIN {modules} AS m ON m.name = 'attendance' AND cm.module = m.id WHERE cm.instance = $data->attendance ");
                    $course = get_course($cm->course);
                    $session_structure = new \mod_attendance_structure($attendance, $cm, $course);
                    $sessioninfo = new stdClass();
                    $sessioninfo->sessdate= $starttime;
                    $sessioninfo->duration= $duration;
                    $sessioninfo->description= $offering->code;
                    $sessioninfo->groupid=0;
                    $sessionid= $session_structure->add_session($sessioninfo);
                    (new trainingprogram)->insert_offering_session_record($sessionid,$offering->id,$starttime,$offering->trainingid,$program->courseid);
                    $starttime += 86400;
                }
            }
        }
        $offering->usermodified = $USER->id;
        $offering->timemodified = time();
        $DB->update_record('tp_offerings', $offering);

    }


    public function checktermsandconditions($programid)
    {
        global $DB,$USER;
        $query = "SELECT termsconditions from {local_trainingprogram} where id = $programid";
        $termsandconditions = $DB->get_field_sql($query);
        return $termsandconditions;

    }
    /**
     * get_programs_users_basedon_roles
     * @param $roleshortname: Short name of the role you want to get the users
     * @return stdObject users
     */
    public function get_programs_users_basedon_roles($programid, $roleshortname, $offeringid = null) {
        global $DB;
        if (!$roleshortname) {
            throw new Exception('Argument $roleshortname cannot be empty..!');
        }else{
            $offering = '';
            if ($offeringid) {
                $offering = " AND offeringid = ". $offeringid;
            }
            $roleid = $DB->get_field('role', 'id', ['shortname' => $roleshortname]);
            $displaying_name = (new trainingprogram)->user_fullname_case();
            $users = $DB->get_records_sql("SELECT u.id,$displaying_name
                FROM {user} u 
                JOIN {local_users} lc ON lc.userid = u.id
                JOIN {program_enrollments} pe ON pe.userid = lc.userid
                WHERE pe.programid = :programid AND pe.roleid = :roleid  AND pe.offeringid = :offeringid", ['roleid' => $roleid, 'programid' => $programid,'offeringid'=>$offeringid]
            );
            return $users;
        }
    }
    /**
     * Generate string for the confirmation for the trainer to make him/her an Editing Trainer
     * @param INT $userid will be the id of trainer
     */
    public function generate_confirmation_string($userid) {
        global $DB;
        if (!$userid || $userid == '') {
            throw new Exception("This function will not take null or empty argument. Please provide a valid arguments.", 1);
        }else{
            $displaying_name = (new trainingprogram)->user_fullname_case();
            $userrecord = $DB->get_record_sql("SELECT u.id, $displaying_name FROM {user} u JOIN {local_users} lc ON lc.userid = u.id WHERE u.id = :userid ", ['userid' => $userid]);
        }
        $label = html_writer::tag('label', get_string('confirm_edditingtrainer', 'local_trainingprogram' ,$userrecord->fullname), ['class' => "d-inline word-break ", 'for' => 'label for ' . $userrecord->fullname, 'id' => 'lebel_assignedditingtrainer_'.$userrecord->id]);
        $lbldiv = html_writer::div('', "form-label-addon d-flex align-items-center align-self-start");
        $input = html_writer::tag('input', '', ['type' => 'checkbox', 'class' => 'form-control editing_trainer assignedditingtrainer_'.$userrecord->id, 'name' => 'assignedditingtrainer_'.$userrecord->id, 'id' => 'assignedditingtrainer_'.$userrecord->id, 'value' => $userrecord->id]);
        $input .= '<div class="form-defaultinfo text-muted mt-1 ml-1 ">'.get_string('default').': '.get_string('no').'</div>';
        $indiv = html_writer::div('', 'form-control-feedback invalid-feedback');
        $labeldiv = html_writer::div($label.$lbldiv, 'col-md-3 col-form-label d-flex pb-0 pr-md-0');
        $inputdiv = html_writer::div($input.$indiv, 'col-md-9 form-inline align-items-start felement');
        $row = html_writer::div($labeldiv.$inputdiv, 'form-group row  fitem   assignedditingtrainer_'.$userrecord->id, ['id' => 'fitem_id_assignedditingtrainer_'.$userrecord->id, ]);
        return $row;
    }

    public function generate_newjobfamily_options($sectorid, $is_checked=false) {
        global $DB;
        if (!$sectorid || $sectorid == '') {
            throw new \Exception("This function will not take null or empty argument. Please provide a valid arguments.", 1);
        }else{
            $record = $DB->get_record_sql(" SELECT id, title FROM {local_sector} WHERE id = $sectorid");
        }
        $title = strtolower(str_replace(' ', '', $record->title));
        $label = html_writer::tag('label', get_string(trim($title).'_jobfamilies', 'local_trainingprogram'), ['class' => "d-inline word-break mt-1 ml-1", 'for' => 'label for ' . $record->title, 'id' => 'lebel_newjobfamilyoptions_'.$record->id]);
        $lbldiv = html_writer::div('', "form-label-addon d-flex align-items-center align-self-start");
        if($is_checked) {
            $input = html_writer::tag('input', '', ['type' => 'checkbox', 'class' => 'form-control newjobfamily_options  newjobfamilyoptions_'.$record->id, 'name' => 'newjobfamilyoptions_'.$record->id, 'id' => 'newjobfamilyoptions_'.$record->id, 'value' => $record->id, 'checked'=>true]);
        } else {
            $input = html_writer::tag('input', '', ['type' => 'checkbox', 'class' => 'form-control newjobfamily_options  newjobfamilyoptions_'.$record->id, 'name' => 'newjobfamilyoptions_'.$record->id, 'id' => 'newjobfamilyoptions_'.$record->id, 'value' => $record->id]);
        }
      
      //  $input .= '<div class="d-inline word-break mt-1 ml-1 ">'.get_string(trim($title).'_jobfamilies', 'local_trainingprogram').'</div>';
        $indiv = html_writer::div('', 'form-control-feedback invalid-feedback');
        $labeldiv = html_writer::div($lbldiv, 'col-md-3 col-form-label d-flex pb-0 pr-md-0');
        $inputdiv = html_writer::div($input.$indiv.$label, 'col-md-9 form-inline align-items-start felement');
        $row = html_writer::div($labeldiv.$inputdiv, 'form-group row  fitem newjobfamilyoptions_'.$record->id, ['id' => 'fitem_id_newjobfamilyoptions_'.$record->id, ]);
        return $row;
    }
    public function update_financially_closed_status($offeringid,$code,$actiontype) {
        global $DB,$USER;
        $updatingdata = new stdClass();
        $updatingdata->id = $offeringid;
        $updatingdata->fc_status_added_by = $USER->id;
        $updatingdata->fc_status_modified_at = time();
        $updatingdata->financially_closed_status = ($actiontype == 'open_fc_offering') ? 0 : 1;

        try{
            $record = $DB->update_record('tp_offerings',$updatingdata);
            return $record;
        } catch(dml_exception $e){
            print_r($e);
        }
    }
    /**
     * Check if the user has the specified role
     * @param $userid (INT)
     * @return (object)
     */
    public function if_user_has_role($userid, $role_shortname) {
        global $DB;
        if (!$userid || $userid == '') {
            print_error('missinguserid', 'local_exams');
        }
        if (!$role_shortname || $role_shortname == '') {
            print_error('missingroleshortname', 'theme_academy');
        }
        $user_assigned_role = $DB->get_record_sql(" SELECT u.id, r.shortname 
            FROM {user} u
            JOIN {role_assignments} ra ON ra.userid = u.id
            JOIN {context} ctx ON ctx.id = ra.contextid AND ctx.contextlevel = 10
            JOIN {role} r ON r.id = ra.roleid
            WHERE u.id = :userid  AND r.shortname = :shortname
            ORDER BY ra.id DESC
        ", ['userid' => $userid, 'shortname' => $role_shortname]);
        return $user_assigned_role;
    }
    /**
     * Get the creator of a Module
     * @param $cmid
     * @return object $creator
     */
    function get_module_creator(int $cmid) {
        global $DB;
        if (!$cmid) {
            print_error('missingcmid', 'local_trainingprogram');
        }
        $sql = "SELECT * FROM {logstore_standard_log} WHERE `eventname` LIKE '%course_module_created%' AND `objectid` = :cmid ORDER BY `id` DESC ";
        $creator = $DB->get_record_sql($sql, ['cmid' => $cmid]);
        
        if ($creator) {
            $creatorRole = (new \local_exams\local\exams())->get_user_role($creator->userid);
            return $creatorRole;
        }
    }

    public function insert_cancel_record_in_org_order_payments($invoicedata,$productdata) {
        global $DB, $USER,$CFG;
        $record = new stdClass();
        $tablename = ($productdata->entitytype == 'offering')? 'tp_offerings' : 'local_events';
        $record->productid=$productdata->productid;
        $record->tablename =$tablename;
        $record->fieldname ='id';
        $record->fieldid =(int)$DB->get_field('tool_products','referenceid',['id'=>$productdata->productid]);
        $record->orguserid =$invoicedata->userid;
        $record->realuser =($USER->realuser) ? $USER->realuser :0;
        $record->purchasedseats =1;
        $record->usercreated =$USER->id;
        $record->timecreated =time();
        $record->organization =0;
        $record->paymenttype ='postpaid';
        $record->paymenton =time();
        $record->amount =$invoicedata->amount;
        $record->approvalon =time();
        $record->transactionid =$invoicedata->invoice_number;
        $record->originalprice =$invoicedata->amount;
        $record->taxes =0;
        $record->payableamount =$invoicedata->amount;
        try{
           $DB->insert_record('tool_org_order_payments', $record); 

        }catch(dml_exception $e){
            print_error($e);
        }

    }


    public  function viewprogramtopics($programid) { 
        global $DB, $PAGE, $OUTPUT;
        $systemcontext = context_system::instance();
        $PAGE->set_context($systemcontext);
        $data = $DB->get_record('local_trainingprogram', ['id' => $programid], '*', MUST_EXIST);
        $renderer = $PAGE->get_renderer('local_trainingprogram');
        $coursedata  = $renderer->programtopics_view($data);
        return $coursedata;
    }

    public  function add_update_official_schedule_program($data) { 
        global $DB, $USER;
        $row = array();
        if($data->halllocation){
            $row['halllocation'] = $data->halllocation;
        }
        if($data->halllocation1){
            $row['halllocation'] = $data->halllocation1;
        }

        $row['type'] = $data->type;
        $row['offeringpricing'] = ($data->type == 1) ? (($data->offeringpricing) ? $data->offeringpricing : 0) : 0 ;
        $row['organization'] = (is_array($data->organization)) ? implode(" ",$data->organization) : $data->organization;
        $row['sellingprice'] = $data->sellingprice?$data->sellingprice:0;
        $row['actualprice'] = $data->actualprice?$data->actualprice:0;
        $row['trainingid'] = $data->trainingid;
        $row['meetingtype'] = $data->meetingtype ? $data->meetingtype: 0;
        $row['trainingmethod'] = $data->trainingmethod;
        $row['published'] = 0;
        if ($data->trainingmethod == 'online' || $data->trainingmethod == 'elearning' || $data->halllocation1=='clientheadquarters'){
            $row['halladdress'] = 0;

        } else {
            $row['halladdress'] = $data->halladdress? $data->halladdress : 0;
            $row['halllocation'] = NULL;
        }

        $row['officials'] = implode(',', array_filter($data->officials));

        $row['availableseats'] = $data->availableseats;
        $row['startdate'] = ($data->trainingmethod == 'elearning') ? 0 : $data->startdate;
        $row['enddate'] = ($data->trainingmethod == 'elearning') ? 0 : $data->enddate;
        $row['time'] = ($data->trainingmethod == 'elearning') ? 0 : (($data->starttime['hours'] * 3600) + ($data->starttime['minutes'] * 60));
        $row['endtime'] = ($data->trainingmethod == 'elearning') ? 0 : (($data->endtime['hours'] * 3600) + ($data->endtime['minutes'] * 60));
        $start = $data->startdate+$row['time'];
        $end = $data->startdate+$row['endtime'];
        $duration =($data->trainingmethod == 'elearning') ? 0 : abs($start-$end);
        $row['duration'] = $duration;
        if(!empty($data->code) && !is_null($data->code)) {
           $row['code']  = $data->code;
        } else {
          $row['code']  = (new tp) :: generate_offering_code($data);
        }

        $row['groupid'] = 0;
        $row['sections'] =0;
        $row['meetingid'] = 0;
        $row['classification'] = $data->classification; 
        $row['estimatedbudget'] = ($data->estimatedbudget) ? $data->estimatedbudget : 0 ; 
        $row['proposedcost'] = ($data->proposedcost) ? $data->proposedcost : 0 ;  
        $row['finalamount'] = ($data->finalpoamount) ? $data->finalpoamount : 0 ;
        $row['tagrement'] =($data->traingagrrement) ? $data->traingagrrement : 0 ; $data->traingagrrement;
        $row['trainingcost'] = ($data->tcost) ? $data->tcost : 0 ;
        if (isset($data->attachmentpdf)) { 
            $row['attachmentpdf']= $data->attachmentpdf;
        }
        if (isset($data->officialproposal)) {
            $row['officialproposal']= $data->officialproposal;
        }
        if (isset($data->officialpo)) {
            $row['officialpo']= $data->officialpo;
        }
        if (isset($data->tagrrement)) {
            $row['tagrrement']= $data->tagrrement;
        }
        $row['languages'] = $data->language;
        $row['externallink']    = $data->externallink;
        $row['externallinkcheck']     = $data->externallinkcheck;
        try{
          $offeringid = $DB->insert_record('official_tp_offerings', $row);
          $offeringrequest = new stdClass();
          $offeringrequest->referenceid = $offeringid;
          $offeringrequest->code = $row['code'];
          $offeringrequest->entity = 'offering';
          $offeringrequest->type = 'create';
          $offeringrequest->timecreated = time();
          $offeringrequest->usercreated = $USER->id;
          $DB->insert_record('offering_program_requests', $offeringrequest);
        } catch(moodle_exception $e){
            print_r($e);
        }

    }
    

    public function offering_program_requests() {
        global $PAGE;
        $renderer = $PAGE->get_renderer('local_trainingprogram');
        $filterparams  = $renderer->get_offering_program_requests(true);
        $filterparams['submitid'] = 'form#filteringform';
        $filterparams['widthclass'] = 'col-md-12';
        $filterparams['placeholder'] = get_string('serch_request','local_trainingprogram');
        $globalinput=$renderer->global_filter($filterparams);
        $offering_program_requests = $renderer->get_offering_program_requests();
        $filterparams['offering_program_requests'] = $offering_program_requests;
        $filterparams['globalinput'] = $globalinput;
        $renderer->listofofferingprogramrequests($filterparams);

    } 

    public function get_listof_offering_program_requests($stable, $filterdata) {
        global $DB;
        $currentlang = current_language();
        $selectsql = "SELECT * FROM {offering_program_requests} "; 
        $countsql  = "SELECT COUNT(id) FROM {offering_program_requests} ";
        $formsql = " WHERE 1=1 ";
        if  (isset($filterdata->search_query) && trim($filterdata->search_query) != ''){
            $formsql .= " AND ( code LIKE :entitycode) ";
            $searchparams = array(
                'entitycode' => '%'.trim($filterdata->search_query).'%',
            );
        } else {
            $searchparams = array();
        }
        $params = array_merge($searchparams);
        $totalrequests = $DB->count_records_sql($countsql.$formsql,$params);
        $formsql .=" ORDER BY id DESC";
        $requests = $DB->get_records_sql($selectsql.$formsql, $params, $stable->start,$stable->length);
        $requestslist = array();
        $count = 0;
        foreach($requests as $request) {
            if($request->type == 'update' || $request->type == 'delete') {
                $table = ($request->entity == 'program') ? 'local_trainingprogram' : 'tp_offerings';
                $recordexists = $DB->record_exists($table,['id'=>$request->referenceid,'code'=>$request->code]);
                if(!$recordexists){
                    $DB->delete_records('offering_program_requests',['id'=>$request->id,'referenceid'=>$request->referenceid,'code'=>$request->code,'type'=>$request->type]);
                }
            }
            $userid = ($request->type == 'update') ? $request->usermodified : $request->usercreated;
            $requestat = ($request->type == 'update') ? $request->timemodified : $request->timecreated;
            $userrecord = $DB->get_record('local_users',['userid'=>$userid]);
            if( $currentlang == 'ar' && !empty($userrecord->firstnamearabic)){
                $firstname = $userrecord->firstnamearabic;
            }else{
                $firstname = $userrecord->firstname;
            }
            if( $currentlang == 'ar' && !empty($userrecord->lastnamearabic)){
                $lastname = $userrecord->lastnamearabic;
            }else{
                $lastname = $userrecord->lastname;
            }
            if( $currentlang == 'ar' && !empty($userrecord->middlenamearabic)){
                $middlename = $userrecord->middlenamearabic;
            }else{
                $middlename = $userrecord->middlenameen;
            }
            if( $currentlang == 'ar' && !empty($userrecord->thirdnamearabic)){
                $thirdname = $userrecord->thirdnamearabic;
            }else{
                $thirdname = $userrecord->thirdnameen;
            }
            $requestslist[$count]["id"] = $request->id;
            $requestslist[$count]["entityid"] = $request->referenceid;
            $requestslist[$count]["entitycode"] =$request->code;
            $requestslist[$count]["requestbyname"] =$firstname.' '.$middlename.' '.$thirdname.' '.$lastname;
            
            $requestslist[$count]["entity"] =($request->entity == 'offering') ? get_string('offerings','local_trainingprogram') : get_string('program','local_trainingprogram');
            $requestslist[$count]["requesttype"] = ($request->type == 'create') ? get_string('create','local_trainingprogram') : (($request->type == 'update') ? get_string('update','local_trainingprogram') : get_string('delete','local_trainingprogram'));
            $requestslist[$count]["requestat"] = userdate($requestat,'%Y-%m-%d %H:%M:%S');
            $requestslist[$count]["requestby"] = $userid;
            if($request->entity == 'program') {
                $programid = $request->referenceid;
            } else {
                $table  = ($request->entity == 'offering' && $request->type == 'create') ? 'official_tp_offerings' : 'tp_offerings';
                $programid  = $DB->get_field($table,'trainingid',['id'=>$request->referenceid]);

            }
            $requestslist[$count]["programid"] = $programid;
            $requestslist[$count]["viewrequestedoffering"] = ($request->entity == 'offering') ? true : false;
            $requestslist[$count]["viewrequestedprogram"] = ($request->entity == 'program') ? true : false;

            $count++;
        }
        $coursesContext = array(
            "hascourses" => $requestslist,
            "totalrequests" => $totalrequests,
            "length" => count($requestslist)
        );
        return $coursesContext;
    }

    public function offering_program_action_view($data) {
        global $DB;
        $response = '';
        $systemcontext = context_system::instance();
        if($data->actiontype == 'approve') {
            if ($data->requesttype == 'Create') {
                $offeringrecord = $DB->get_record('official_tp_offerings',['id'=>$data->entityid]);

                $offeringrecord->id = 0;
                if($offeringrecord->time > 0) {
                    $dur_min = $offeringrecord->time/60;
                } else {

                    $dur_min = $offeringrecord->time/60;
                }
                if($dur_min){
                    $hours = floor($dur_min / 60);
                    $minutes = ($dur_min % 60);
                }
                $offeringrecord->starttime['hours'] = $hours;
                $offeringrecord->starttime['minutes'] = $minutes;
                if($offeringrecord->endtime > 0) {
                    $dur__min = $offeringrecord->endtime/60;
                }  else {
                    $dur__min = $offeringrecord->endtime/60;
                }
                if($dur__min){
                    $hours = floor($dur__min / 60);
                    $minutes = ($dur__min % 60);
                } 
                $offeringrecord->endtime =[];
                $offeringrecord->endtime['hours'] = $hours;
                $offeringrecord->endtime['minutes'] = $minutes;
                $offeringrecord->finalpoamount = $offeringrecord->finalamount;
                $offeringrecord->traingagrrement = $offeringrecord->tagrement;
                $offeringrecord->tcost = $offeringrecord->trainingcost;
                $offeringrecord->language = $offeringrecord->languages;
                $record  = (new tp)->add_update_schedule_program($offeringrecord);
                if($record) {
                    $DB->delete_records('official_tp_offerings',['id' =>$data->entityid]);
                    $response=  $DB->delete_records('offering_program_requests',['id' =>$data->rootid,'referenceid' =>$data->entityid]);
                }
         
            } elseif($data->requesttype == 'Update') {
                $offeringrecord = $DB->get_record('tp_offerings',['id'=>$data->entityid]);
                $offeringpaststartdate =  $offeringrecord->startdate;
                $offeringpastenddate =  $offeringrecord->enddate;
                $offeringpaststarttime =  $offeringrecord->time;
                $offeringpastendtime =  $offeringrecord->endtime;

                $requestrecord  = $DB->get_record('offering_program_requests',['id' =>$data->rootid,'referenceid' =>$data->entityid]);

                $offeringrecord->startdate = ($requestrecord->startdate > 0) ? $requestrecord->startdate : $offeringrecord->startdate;
                $offeringrecord->enddate = ($requestrecord->enddate > 0) ? $requestrecord->enddate : $offeringrecord->enddate;
                if($requestrecord->starttime > 0) {
                    $dur_min = $requestrecord->starttime/60;
                } else {

                    $dur_min = $offeringrecord->time/60;
                }
                if($dur_min){
                    $hours = floor($dur_min / 60);
                    $minutes = ($dur_min % 60);
                }
                $offeringrecord->starttime['hours'] = $hours;
                $offeringrecord->starttime['minutes'] = $minutes;
                if($requestrecord->endtime > 0) {
                    $dur__min = $requestrecord->endtime/60;
                }  else {
                    $dur__min = $offeringrecord->endtime/60;
                }
                if($dur__min){
                    $hours = floor($dur__min / 60);
                    $minutes = ($dur__min % 60);
                } 
                $offeringrecord->endtime =[];
                $offeringrecord->endtime['hours'] = $hours;
                $offeringrecord->endtime['minutes'] = $minutes;
                $offeringrecord->sellingprice = ($requestrecord->sellingprice > 0) ? $requestrecord->sellingprice : $offeringrecord->sellingprice;
                $offeringrecord->actualprice = ($requestrecord->actualprice > 0) ? $requestrecord->actualprice : $offeringrecord->actualprice;
                $offeringrecord->offeringtype = 'approved';
                $offeringrecord->trainingmethod = (!is_null($requestrecord->trainingmethod)) ?  $requestrecord->trainingmethod :$offeringrecord->trainingmethod; 
                $record  = (new tp)->add_update_schedule_program($offeringrecord);
                if($record) {                  
                      if($offeringpaststartdate !=  $requestrecord->startdate || $offeringpastenddate !=  $requestrecord->enddate ||  $offeringpaststarttime !=  $requestrecord->starttime ||  $offeringpastendtime != $requestrecord->endtime ){
                        $offeringenrolrecord = $DB->get_records_sql("SELECT pe.id,lt.name,lt.namearabic,lu.*
                        FROM {program_enrollments} pe 
                        JOIN {local_trainingprogram} lt ON 
                        pe.programid = lt.id
                        JOIN {local_users} lu ON 
                        pe.userid = lu.userid WHERE pe.offeringid = $data->entityid");            
                    foreach($offeringenrolrecord as $offeringenrolrecords){                       
                        $tpuser = $DB->get_record('user',array('id'=>$offeringenrolrecords->userid));
                        $paststarttimemeridian = gmdate('a', $offeringpaststarttime );
                        $pastendtimemeridian = gmdate('a',$offeringpastendtime);
                        $presentstarttimemeridian = ($requestrecord->starttime != 0) ? gmdate('a', $requestrecord->starttime):  $paststarttimemeridian;
                        $presentendtimemeridian = ($requestrecord->endtime != 0) ?gmdate('a',$requestrecord->endtime) :$pastendtimemeridian ;
                        if($tpuser->lang == 'ar'){
                            $offeringenrolrecords->program_userfullname =  $offeringenrolrecords->firstnamearabic.''.$offeringenrolrecords->lastnamearabic.''.$offeringenrolrecords->middlenamearabic.''. $offeringenrolrecords->thirdnamearabic;
                            $offeringenrolrecords->program_name = $offeringenrolrecords->namearabic;
                            $paststartmeridian = ($paststarttimemeridian == 'am')? 'صباحًا' : 'مساءً';
                            $pastendmeridian = ( $pastendtimemeridian == 'am')? 'صباحًا' : 'مساءً';
                            $presentstartmeridian = ($presentstarttimemeridian == 'am')? 'صباحًا' : 'مساءً';
                            $presentendmeridian= ($presentendtimemeridian  == 'am')? 'صباحًا' : 'مساءً';                          

                        } else{
                            $offeringenrolrecords->program_userfullname = $offeringenrolrecords->firstname.''.$offeringenrolrecords->lastname.''.$offeringenrolrecords->middlenameen.''.$offeringenrolrecords->thirdnameen;
                            $offeringenrolrecords->program_name = $offeringenrolrecords->name;
                            $paststartmeridian = ($paststarttimemeridian == 'am')? 'AM' : 'PM';
                            $pastendmeridian = ( $pastendtimemeridian == 'am')? 'AM' : 'PM';
                            $presentstartmeridian = ($presentstarttimemeridian == 'am')? 'AM' : 'PM';
                            $presentendmeridian = ($presentendtimemeridian  == 'am')? 'AM' : 'PM';

                        }

                        $offeringenrolrecords->offering_pastdate =   userdate($offeringpaststartdate, '%d/%m/%Y') .''.'-'. userdate($offeringpastenddate, '%d/%m/%Y').' ';
                        $offeringenrolrecords->offering_pasttime = gmdate("H:i", $offeringpaststarttime) .''.$paststartmeridian.''.'-'.gmdate("H:i",  $offeringpastendtime).''. $pastendmeridian; 
                        $offeringstartdate =  ($requestrecord->startdate != 0) ? $requestrecord->startdate : $offeringpaststartdate;
                        $offeringenddate = ($requestrecord->enddate != 0) ? $requestrecord->enddate : $offeringpastenddate;
                        $offeringstarttime = ($requestrecord->starttime != 0) ? gmdate("H:i", $requestrecord->starttime).''.$presentstartmeridian: gmdate("H:i", $offeringpaststarttime) .''.$paststartmeridian;
                        $offeringendtime = ($requestrecord->endtime != 0) ? gmdate("H:i", $requestrecord->endtime).''.$presentendmeridian:gmdate("H:i",  $offeringpastendtime).''. $pastendmeridian;
                        $offeringenrolrecords->offering_presentdate =   userdate($offeringstartdate, '%d/%m/%Y') .''.'-'. userdate($offeringenddate, '%d/%m/%Y').' ';
                        $offeringenrolrecords->offering_presenttime = $offeringstarttime .''.'-'. $offeringendtime;
                   
                        (new \local_trainingprogram\notification())->trainingprogram_notification('trainingprogram_reschedule', $touser= $tpuser,$fromuser=$USER,$offeringenrolrecords,$waitinglistid=0);

                    }
                }                
                $response =  $DB->delete_records('offering_program_requests',['id' =>$data->rootid,'referenceid' =>$data->entityid]);
                }
            } else {
               
                if ($data->type == 'Offering') {
                    (new tp)->remove_schedule_program($data->entityid,1);
                    $response =  $DB->delete_records('offering_program_requests',['id' =>$data->rootid,'referenceid' =>$data->entityid]);
                    $event = \local_trainingprogram\event\tpofferings_deleted::create(array( 'context'=>$systemcontext, 'objectid' =>$data->entityid));
                    $event->trigger();
                } else {
                    $courseid =(int) $DB->get_field('local_trainingprogram','courseid',array('id'=>$data->entityid));
                    (new trainingprogram)->remove_all_program_offerings($data->entityid,$courseid,1);

                    if($courseid) {
                      delete_course($courseid,false);  
                    }
                    $response =  $DB->delete_records('offering_program_requests',['id' =>$data->rootid,'referenceid' =>$data->entityid]);
                    $data = $DB->delete_records('local_trainingprogram', array('id' => $data->entityid));
                    $event = \local_trainingprogram\event\trainingprogram_deleted::create(array( 'context'=>$systemcontext, 'objectid' => $data->entityid));
                    $event->trigger();
                }
            }

        } else {
            $response =  $DB->delete_records('offering_program_requests',['id' =>$data->rootid]);
        }
        try{
            return $response;
        }catch(dml_exception $e){
            print_error($e);
        }
        
    }
    public function t_official_deleteaction($data) {
        global $DB,$USER;
        $response = '';
        $table = ($data->etype == 'program') ? 'local_trainingprogram' : 'tp_offerings';
        $code = $DB->get_field($table,'code',['id'=>$data->rootid]);
        $sdata = new stdClass();
        $sdata->referenceid = $data->rootid;
        $sdata->code = $code;
        $sdata->entity = $data->etype;
        $sdata->type = 'delete';
        $record =$DB->get_record('offering_program_requests',[
            'referenceid'=>$data->rootid,
            'code'=>$code,
            'entity'=>$data->etype,
            'type'=>'delete']);
        if($record) {
            $sdata->timemodified = time();
            $sdata->usermodified = $USER->id;
        } else {
            $sdata->timecreated = time();
            $sdata->usercreated = $USER->id;
        }
        try{
            if($record) {
                $sdata->id = $record->id;
                $response =$DB->update_record('offering_program_requests',$sdata);
            } else {
                $response =$DB->insert_record('offering_program_requests',$sdata);
            }
            return $response;
        }catch(dml_exception $e){
            print_error($e);
        }
        
    }
    public  function view_currentoffering($rootid,$offeringid,$requesttype) { 
        global $PAGE;
        $systemcontext = context_system::instance();
        $PAGE->set_context($systemcontext);
        $renderer = $PAGE->get_renderer('local_trainingprogram');
        $data  = $renderer->view_currentoffering($rootid,$offeringid,$requesttype);
        return $data;
   
    }
    public  function view_managementdiscountdata($type) { 
        global $PAGE;
        $systemcontext = context_system::instance();
        $PAGE->set_context($systemcontext);
        $renderer = $PAGE->get_renderer('local_trainingprogram');
        $data  = $renderer->view_managementdiscountdata($type);
        return $data;
   
    }
    public  function useraccessingsameactivity($data) { 
        global $DB;
        $notalowed = false;
        $errormessage = '';
        $trainerRoleId = $DB->get_field('role', 'id', ['shortname' => 'trainer']);
        $currentofferingrecord = $DB->get_record('tp_offerings',['id'=>$data['offeringid']]);
       
        if($currentofferingrecord->trainingmethod == 'online') {
            $users = (empty($data['orguser'])) ? $data['users'] :$data['orguser'] ;
            $mettingtype = (int) $currentofferingrecord->meetingtype;
            if($mettingtype == 1) {
                $allowedacount =(int)get_config('mod_zoom','concurrentmeeting');
            } else if($mettingtype == 2) {
                $allowedacount = (int)get_config('mod_webexactivity','concurrentmeeting');
            } else {
                $allowedacount = (int)get_config('mod_teamsmeeting','concurrentmeeting');
            }
            if(!empty($data['users'])) {
                foreach ($users AS $user) {
                    $localuserrecord = $DB->get_record('local_users',['userid'=>$user]);
                    $userfullname_en=$localuserrecord->firstname .' '. $localuserrecord->middlenameen.' '. $localuserrecord->thirdnameen.' '. $localuserrecord->lastname;
                    $userarabicfullname_ar=$localuserrecord->firstnamearabic .' '. $localuserrecord->middlenamearabic.' '. $localuserrecord->thirdnamearabic.' '. $localuserrecord->lastnamearabic;
                    $fullname= (current_language() == 'ar') ? $userarabicfullname_ar : $userfullname_en;

                    $lastenrolledofferingrecord =$DB->get_record_sql("SELECT tpo.* FROM {tp_offerings} tpo JOIN {program_enrollments} pr ON pr.offeringid = tpo.id AND pr.programid = tpo.trainingid  WHERE tpo.trainingmethod = 'online' AND tpo.meetingtype = $mettingtype AND pr.userid = $user AND pr.roleid = $trainerRoleId  ORDER BY pr.id DESC LIMIT 1") ;
                    if($lastenrolledofferingrecord) {
                        $astartdate =$currentofferingrecord->startdate; 

                        $estarttime = $currentofferingrecord->time;
                        $eendtime =$currentofferingrecord->endtime;

                        $offeringshavingsametime =$DB->count_records_sql("SELECT COUNT(DISTINCT id) FROM {tp_offerings} WHERE trainingmethod = 'online' AND startdate = $astartdate AND (($estarttime BETWEEN time AND endtime)  OR ($eendtime BETWEEN time AND endtime) OR (time < $estarttime AND endtime > $eendtime)) AND  meetingtype = $mettingtype AND id IN (SELECT offeringid FROM {program_enrollments} WHERE userid = $user AND roleid = $trainerRoleId)") ;

                        if($offeringshavingsametime >= $allowedacount) {
                            $notalowed = true;
                            $displayparams = new stdClass();
                            $displayparams->user = ($localuserrecord->id > 0) ? $fullname : fullname($DB->get_record('user', ['id' => $user]));
                            $displayparams->count = $allowedacount;
                            $errormessage = get_string('cannotaccesssameactivity','local_trainingprogram',$displayparams);
                        } else {
                            $notalowed = false;
                            $errormessage = '';
                        }
                    } else {
                        $notalowed = false;
                        $errormessage = '';
                    }
                }
            }
        }
        return ['notalowed'=>$notalowed,'errormessage'=>$errormessage];
    }
    public function get_listof_entities($query = null,$ctype = null) {
        global $DB;
        $currentlang= current_language();
        if($ctype =='programs') {

            $fields = array("en.name","en.namearabic","en.code");
            if($currentlang == 'ar') {
                $displaying_name = "en.namearabic";
            } else {
                $displaying_name = "en.name";
            }
            $table ="{local_trainingprogram}";

        } else if($ctype =='exams') {

            $fields = array("en.exam","en.examnamearabic","en.code");
            if($currentlang == 'ar') {
                $displaying_name = "en.examnamearabic";
            } else {
                $displaying_name = "en.exam";
            }
            $table ="{local_exams}";

        } else if($ctype =='events') {
            $fields = array("en.title","en.titlearabic","en.code");
            if($currentlang == 'ar') {
                $displaying_name = "en.titlearabic";
            } else {
                $displaying_name = "en.title";
            }
            $table ="{local_events}";

        } else {
            $fields = array("en.fullname","en.fullnameinarabic","en.shortname");
            if($currentlang == 'ar') {
                $displaying_name = "en.fullnameinarabic";
            } else {
                $displaying_name = "en.fullname";
            }
            $table ="{local_organization}";
        }
        $likesql = array();
        $i = 0;
        foreach ($fields as $field) {
            $i++;
            $likesql[] = $DB->sql_like($field, ":queryparam$i", false);
            $sqlparams["queryparam$i"] = "%$query%";
        }
        $sqlfields = implode(" OR ", $likesql);
        $concatsql = " AND ($sqlfields) ";

        $sql = " SELECT en.id,$displaying_name AS fullname
        FROM $table en
        WHERE 1=1 AND en.id $concatsql ";
        $order = " ORDER BY en.id DESC limit 50";
        $data = $DB->get_records_sql($sql.$order,$sqlparams);
        $return = array_values(json_decode(json_encode(($data)), true));
        return $return;
    }

    public static function get_entitydetails($entities,$id = 0,$type= null,$roottype = null) {
        global $DB;
        $currentlang = current_language();
        if($type =='programs') {
            if($currentlang == 'ar') {
                $displaying_name = "en.namearabic";
            } else {
                $displaying_name = "en.name";
            }
            $table ="{local_trainingprogram}";
            $type = 'programs';

        } else if($type =='exams') {
            if($currentlang == 'ar') {
                $displaying_name = "en.examnamearabic";
            } else {
                $displaying_name = "en.exam";
            }
            $table ="{local_exams}";
            $type = 'exams';

        } else if($type =='events') {
            if($currentlang == 'ar') {
                $displaying_name = "en.titlearabic";
            } else {
                $displaying_name = "en.title";
            }
            $table ="{local_events}";
            $type = 'events';
        } else {
            if($currentlang == 'ar') {
                $displaying_name = "en.fullnameinarabic";
            } else {
                $displaying_name = "en.fullname";
            }
            $table ="{local_organization}";
            $type = 'organizations';
        }
        if(!empty($entities)){
            list($enititysql, $entityparams) = $DB->get_in_or_equal($entities);
            $sql = " SELECT en.id,$displaying_name AS fullname FROM $table AS en
            WHERE en.id $enititysql ";
            $data = $DB->get_records_sql_menu($sql, $entityparams);
        }
        if($id > 0) {
            if($roottype == 'earlyregistration') {
                $roottable = 'earlyregistration_management';
            } else if($roottype == 'coupon') {
                $roottable = 'coupon_management';
            } else  {
                $roottable = 'groupdiscounts';
            }
            $allentities = $DB->get_field($roottable,$type,['id' => $id]);
            if($allentities){
                $sql = " SELECT en.id,$displaying_name AS fullname FROM $table AS en WHERE en.id IN ($allentities)";
                $data = $DB->get_records_sql_menu($sql);
            }
        }
        return $data;
    }

    public function get_listof_groupsdiscounts($stable, $filterdata) {
        global $DB;
        $currentlang= current_language();
        $selectsql = "SELECT *
        FROM {groupdiscounts} "; 
        $countsql  = "SELECT COUNT(id)
        FROM {groupdiscounts} ";
        $formsql = " WHERE 1=1 ";
        if  (isset($filterdata->search_query) && trim($filterdata->search_query) != ''){
            $formsql .= " AND group_count LIKE :countsearch  ";
            $searchparams = array(
                'countsearch' => '%'.trim($filterdata->search_query).'%',
            );
        } else {
            $searchparams = array();
        }
        if (!empty($filterdata->discountgroupstatus)){

            $currentdate = strtotime(date('Y-m-d'));

            if( in_array('1', explode(',', $filterdata->discountgroupstatus)) && in_array('2', explode(',', $filterdata->discountgroupstatus))) {
                $formsql .= " ";
            }elseif($filterdata->discountgroupstatus == 1) {

                $formsql .= " AND expired_date  >= $currentdate  AND status = 1 ";

            } elseif($filterdata->discountgroupstatus == 2) {

                $formsql .= " AND expired_date  <  $currentdate ";
            }
        }
        if($filterdata->{'expired_date[enabled]'} == 1 ){
            $start_year = $filterdata->{'expired_date[year]'};
            $start_month = $filterdata->{'expired_date[month]'};
            $start_day = $filterdata->{'expired_date[day]'};
            $filter_starttime_con = mktime(0,0,0, $start_month, $start_day, $start_year);
            $formsql.= " AND expired_date <= '$filter_starttime_con' ";
        }
        $params = array_merge($searchparams);

        $totalgroupsdiscounts = $DB->count_records_sql($countsql.$formsql,$params);
        $formsql .=" ORDER BY id DESC";
        $groupsdiscounts = $DB->get_records_sql($selectsql.$formsql, $params, $stable->start,$stable->length);
        $groupdiscountslist = array();
        $count = 0;
        foreach($groupsdiscounts as $groupdiscount) {
            $groupdiscountslist[$count]["id"] = $groupdiscount->id;
            $groupdiscountslist[$count]["group_count"] =$groupdiscount->group_count;
            $groupdiscountslist[$count]["discount"] =$groupdiscount->discount;
            $groupdiscountslist[$count]["created_date"] = userdate($groupdiscount->timecreated,get_string('strftimedatemonthabbr', 'core_langconfig'));
            $groupdiscountslist[$count]["expired_date"] =  userdate($groupdiscount->expired_date,get_string('strftimedatemonthabbr', 'core_langconfig'));;
            $expired_date = date('Y-m-d',$groupdiscount->expired_date);
            $currdate=date('Y-m-d'); 
            if(!is_null($groupdiscount->organizations)){   

                $total_organizations_count = $DB->count_records_sql('SELECT COUNT(id) FROM {local_organization} WHERE id IN('.$groupdiscount->organizations.')');

                if( $currentlang == 'ar'){
                    $limitedyquery = 'SELECT fullnameinarabic AS fullname FROM {local_organization} WHERE fullnameinarabic <> "" AND fullnameinarabic IS NOT NULL AND id IN('.$groupdiscount->organizations.') LIMIT 2';

                    $allquery = 'SELECT fullnameinarabic AS fullname FROM {local_organization} WHERE fullnameinarabic <> "" AND fullnameinarabic IS NOT NULL AND id IN('.$groupdiscount->organizations.') LIMIT '.$total_organizations_count.' OFFSET 2';
                } else {
                    $limitedyquery = 'SELECT fullname FROM {local_organization} WHERE id IN('.$groupdiscount->organizations.') LIMIT 2';
                    $allquery = 'SELECT fullname FROM {local_organization} WHERE id IN('.$groupdiscount->organizations.') LIMIT '.$total_organizations_count.' OFFSET 2';
                } 
                $limited = $DB->get_fieldset_sql($limitedyquery);
                $all = $DB->get_fieldset_sql($allquery);
                $groupdiscountslist[$count]['limitedorganization'] = implode(',', $limited);
                $groupdiscountslist[$count]['allorganization'] = implode(',', $all);
                $groupdiscountslist[$count]['moreorganization'] = ($total_organizations_count > 2) ? true : false;
                $groupdiscountslist[$count]['all'] = false;
            } else {

                $groupdiscountslist[$count]['limitedorganization'] = ''; 
                $groupdiscountslist[$count]['allorganization'] = '';
                $groupdiscountslist[$count]['moreorganization'] = false;
                $groupdiscountslist[$count]['all'] = true;
            }

            if ($expired_date >= $currdate && $groupdiscount->status == 1) {
                $groupdiscountslist[$count]["status"] = get_string('available', 'local_trainingprogram');
            } else {
                $groupdiscountslist[$count]["status"]=  get_string('expired', 'local_trainingprogram');
            }
            $groupdiscountslist[$count]["actionview"]=  true  ;
            $count++;
        }
        $coursesContext = array(
            "hascourses" => $groupdiscountslist,
            "totalgroupsdiscounts" => $totalgroupsdiscounts,
            "length" => count($groupdiscountslist)
        );
        return $coursesContext;
    }

    public function remove_groupdiscount($groupid){
        global $DB;
        try{
            $record=$DB->delete_records('groupdiscounts',['id' =>$groupid]);
            return $record;
        } catch(moodle_exception $e){
          print_r($e);
        } 
        
    } 

    public function create_update_groupdiscounts($sdata){
        global $DB, $USER;
        $data = new stdClass();
        $data->group_count = $sdata->group_count;
        $data->discount = $sdata->discount;
        $data->created_date =time();
        $data->expired_date = $sdata->earlyregistration_expired_date;
        $data->programs = (!empty($sdata->programs)) ? ((is_array($sdata->programs)) ?  implode(',', array_filter($sdata->programs)) : $sdata->programs) : null;
        $data->exams =  (!empty($sdata->exams)) ? ((is_array($sdata->exams)) ?  implode(',', array_filter($sdata->exams)) : $sdata->exams) : null;
        $data->events = (!empty($sdata->events)) ? ((is_array($sdata->events)) ?  implode(',', array_filter($sdata->events)) : $sdata->events) : null;
        $data->organizations = (!empty($sdata->organizations)) ? ((is_array($sdata->organizations)) ?  implode(',', array_filter($sdata->organizations)) : $sdata->organizations) : null;

        if($sdata->id > 0){
            $data->id = $sdata->id;
            $data->usermodified = $USER->id;
            $data->timemodified =time();
            $DB->update_record('groupdiscounts', $data);

        }else{

            $data->usercreated = $USER->id;
            $data->timecreated =time();
            $DB->insert_record('groupdiscounts', $data);

        }
    }
    public  function view_discountentity($entityid,$entitytype) { 
        global $PAGE;
        $systemcontext = context_system::instance();
        $PAGE->set_context($systemcontext);
        $renderer = $PAGE->get_renderer('local_trainingprogram');
        $data  = $renderer->view_discountentity($entityid,$entitytype);
        return $data;
   
    }
}
