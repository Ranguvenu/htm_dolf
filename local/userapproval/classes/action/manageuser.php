<?php
// This file is part of Moodle - http://moodle.org/
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
 * @package    local_userapproval
 * @copyright  2022 eAbyas Info Solutions<info@eabyas.com>
 * @author     Vinod Kumar  <vinod.p@eabyas.com>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
namespace local_userapproval\action;
use local_trainingprogram\local\trainingprogram;
use moodle_exception;
use local_exams\local\exams;
defined('MOODLE_INTERNAL') or die;
use html_writer;
use moodle_url;
use context_system;
use tabobject;
use user_create_user;
use local_userapproval\local\fast_service;
use context_user;
use core_user;
use filters_form;
use stdClass;
use tool_product\orders as orders;
use tool_product\telr as telr;


require_once($CFG->dirroot.'/user/lib.php');
require_once($CFG->dirroot.'/course/lib.php');
require_once($CFG->dirroot.'/local/userapproval/lib.php');
require_once($CFG->dirroot.'/user/profile/definelib.php');
require_once($CFG->libdir.'/accesslib.php');
require_once($CFG->dirroot . '/local/trainingprogram/lib.php');
// require_once('../../config.php');

class manageuser{
    public function usersinfo() {
        global $DB, $PAGE, $OUTPUT;
        $systemcontext = context_system::instance();
        $renderer = $PAGE->get_renderer('local_userapproval');
        $filterparams  = $renderer->get_catalog_manageusers(true);
        $filterparams['submitid'] = 'form#filteringform';
        $filterparams['widthclass'] = 'col-md-12';
        $filterparams['placeholder'] = get_string('search_users','local_userapproval');
        $globalinput=$renderer->global_filter($filterparams);
        $userdetails = $renderer->get_catalog_manageusers();
        $fform = userapproval_filters_form($filterparams);
        $filterparams['userdetails'] = $userdetails;
        $filterparams['filterform'] = $fform->render();
        $filterparams['globalinput'] = $globalinput;
        $renderer->listofusers($filterparams);
        
    }

    public function get_filters() {
        global $DB;
        $filters = $DB->get_records_sql_menu('SELECT id, firstname FROM {local_users} WHERE id > 2');
        foreach($filters as $key => $filter) {
            $filtersinfo[] = ['id' => $key, 'name' => $filter];
        };
        return $filtersinfo;        
    }

    public function get_listof_users($stable, $filterdata) {
        global $DB, $CFG, $USER;
        $systemcontext = context_system::instance();
        if(!is_siteadmin() && has_capability('local/organization:manage_organizationofficial', $systemcontext) && $DB->record_exists('local_users',array('userid'=>$USER->id))) {
            $roleid = $DB->get_field('role', 'id', array('shortname' => 'trainee'));
            $orgofficalorganization= $DB->get_field('local_users','organization',array('userid'=>$USER->id));
                $selectsql = "SELECT lo.* FROM {local_users} AS lo "; 
                $countsql  = "SELECT COUNT(lo.id) FROM {local_users} AS lo ";
                $formsql  = " WHERE 1=1 AND
                            CASE
                            WHEN  lo.organization > 0 THEN lo.organization = $orgofficalorganization
                            ELSE lo.organization <> 0
                            END AND
                            lo.userid != $USER->id AND 
                            lo.userid IN (SELECT userid FROM {role_assignments} WHERE roleid = $roleid 
                            AND contextid = $systemcontext->id) ";
            } else {
            $selectsql = "SELECT * FROM {local_users} lo "; 
            $countsql  = "SELECT COUNT(lo.id) FROM {local_users} lo  ";
            $formsql =" WHERE 1=1 ";
        }
        if  (isset($filterdata->search_query) && trim($filterdata->search_query) != ''){
            $formsql .= " AND (lo.firstname LIKE :firstnamesearch OR 
                            lo.lastname LIKE :lastnamesearch  OR 
                            lo.firstnamearabic LIKE :firstnamearabicsearch OR 
                            lo.lastnamearabic LIKE :lastnamearabicsearch  OR
                            lo.middlenameen LIKE :middlenameensearch OR
                            lo.middlenamearabic LIKE :middlenamearabicsearch OR
                            lo.thirdnameen LIKE :thirdnameensearch OR 
                            lo.thirdnamearabic LIKE :thirdnamearabicsearch OR 
                            lo.email LIKE :emailsearch  OR lo.phone1 LIKE :mobilesearch OR
                            lo.id_number LIKE :id_numbersearch OR 
                            lo.username LIKE :usernamesearch
                        ) ";
            $searchparams = array(
                  'firstnamesearch' => '%'.trim($filterdata->search_query).'%',
                  'lastnamesearch' => '%'.trim($filterdata->search_query).'%',
                  'firstnamearabicsearch' => '%'.trim($filterdata->search_query).'%',
                  'lastnamearabicsearch' => '%'.trim($filterdata->search_query).'%',
                  'middlenameensearch' => '%'.trim($filterdata->search_query).'%',
                  'middlenamearabicsearch' => '%'.trim($filterdata->search_query).'%',
                  'thirdnameensearch' => '%'.trim($filterdata->search_query).'%',
                  'thirdnamearabicsearch' => '%'.trim($filterdata->search_query).'%',
                  'emailsearch' => '%'.trim($filterdata->search_query).'%' ,
                  'mobilesearch' => '%'.trim($filterdata->search_query).'%',
                  'id_numbersearch' => '%'.trim($filterdata->search_query).'%',
                  'usernamesearch' => '%'.trim($filterdata->search_query).'%',
           );
        } else {
            $searchparams = array();
        }
        if (!empty($filterdata->email)){
            $formsql .= " AND lo.id IN ($filterdata->email)";  
        } 
        if (!empty($filterdata->status)){
            $formsql .= " AND lo.approvedstatus IN ($filterdata->status)"; 
        }
        if (!empty($filterdata->role)){
             $roleids = explode(',', $filterdata->role);
             if(!empty($roleids)){
                $rolequery = array();
                foreach ($roleids as $role) {
                    $rolequery[] = " lo.userid IN(SELECT userid FROM {role_assignments} WHERE contextid = $systemcontext->id AND roleid = $role) "; 
                }
                $roleparams =implode('OR',$rolequery);
                $formsql .= ' AND ('.$roleparams.') ';
            }
        }  
        if ($filterdata->deletedusers == 1){
           $formsql .= " AND lo.deleted = 1 ";
        } else {
           $formsql .= " AND lo.deleted = 0 ";
        } 
        $params = array_merge($searchparams);
        $totalusers = $DB->count_records_sql($countsql.$formsql,$params);         
        $formsql .=" ORDER BY lo.id DESC";
        $registeredusers = $DB->get_records_sql($selectsql.$formsql, $params, $stable->start,$stable->length);
        $registereduserslist = array();
        $count = 0;
        $lang= current_language();
        $examenrolments = $DB->get_field('block_learnerscript', 'id', array('type' => 'examenrol'), IGNORE_MULTIPLE);
        
        $programenrolments = $DB->get_field('block_learnerscript', 'id', array('type' => 'programenrol'), IGNORE_MULTIPLE);
        $eventenrolments = $DB->get_field('block_learnerscript', 'id', array('type' => 'eventenrol'), IGNORE_MULTIPLE);
        foreach($registeredusers as $registereduser) {
            $registereduserslist[$count]["id"] = $registereduser->id;
            if( $lang == 'ar' && !empty($registereduser->firstnamearabic)){
                $firstname = $registereduser->firstnamearabic;
            }else{
                $firstname = $registereduser->firstname;
            }
            if( $lang == 'ar' && !empty($registereduser->lastnamearabic)){
                $lastname = $registereduser->lastnamearabic;
            }else{
                $lastname = $registereduser->lastname;
            }
            
            if( $lang == 'ar' && !empty($registereduser->middlenamearabic)){
                $middlename = $registereduser->middlenamearabic;
            }else{
                $middlename = $registereduser->middlenameen;
            }

             if( $lang == 'ar' && !empty($registereduser->thirdnamearabic)){
                $thirdname = $registereduser->thirdnamearabic;
            }else{
                $thirdname = $registereduser->thirdnameen;
            }
             $tobeapproveduserfullname=$firstname.' '.$middlename.' '.$thirdname.' '.$lastname;
             $approveduserfullname=html_writer::tag('a', $firstname.' '.$middlename.' '.$thirdname.' '.$lastname,array('href' =>$CFG->wwwroot. '/local/userapproval/userprofile.php?id='.$registereduser->userid));
            $registereduserslist[$count]["displayfullname"] = ($registereduser->approvedstatus == 2 && is_siteadmin())? $approveduserfullname : $tobeapproveduserfullname;
            $registereduserslist[$count]["fullname"] =$tobeapproveduserfullname;

            $registereduserslist[$count]["username"] = $registereduser->username;
            $registereduserslist[$count]["email"] = $registereduser->email;
            $registereduserslist[$count]["mobile"] = $registereduser->phone1;
            $registereduserslist[$count]["id_number"] = $registereduser->id_number;
            if ($registereduser->approvedstatus == 1) {
               $registereduserslist[$count]["status"] = get_string('pending', 'local_userapproval');
            } else if($registereduser->approvedstatus == 2) {
                $registereduserslist[$count]["status"] = get_string('approved', 'local_userapproval');
            } else {
                $registereduserslist[$count]["status"]=  get_string('rejected', 'local_userapproval');
            }
            $registereduserslist[$count]["sector"]=$registereduser->sector?(($lang == 'ar') ? $DB->get_field('local_sector','titlearabic',array('id'=>$registereduser->sector)):$DB->get_field('local_sector','title',array('id'=>$registereduser->sector))):'';
            $registereduserslist[$count]["organization"]=$registereduser->organization? (($lang == 'ar')? $DB->get_field('local_organization','fullnameinarabic',array('id'=>$registereduser->organization)): $DB->get_field('local_organization','fullname',array('id'=>$registereduser->organization))):'';
            $registereduserslist[$count]["approvedstatus"]= $registereduser->approvedstatus == 1 ? true:false;
            if ($registereduser->approvedstatus == 1 || $registereduser->approvedstatus == 2) {
                $registereduserslist[$count]["rejectedactiondisplay"] =true;
            } else  {
               $registereduserslist[$count]["rejectedactiondisplay"] =false;
            }
            $registereduserslist[$count]["deletedstatus"]= $registereduser->deleted == 1 ? true:false;
            $registereduserslist[$count]["segmentid"]=$registereduser->segment?$registereduser->segment:'';
            $registereduserslist[$count]["jobfamilyid"]=$registereduser->jobfamily?$registereduser->jobfamily:'';
             $registereduserslist[$count]["jobroleid"]=$registereduser->jobrole?$registereduser->jobrole:'';
        
            $registereduserslist[$count]["manageuser"] =(is_siteadmin() ||  has_capability('local/organization:manage_communication_officer',$systemcontext))? true : false;
            $registereduserslist[$count]["view"] =get_string('view', 'local_userapproval');
            $registereduserslist[$count]["approve"] =get_string('approve', 'local_userapproval');
            $registereduserslist[$count]["reject"] =get_string('reject', 'local_userapproval');
            $registereduserslist[$count]["delete"] =get_string('delete', 'local_userapproval');
            $registereduserslist[$count]["edit"] =get_string('edit', 'local_userapproval');
            $registereduserslist[$count]["sendemail"] =get_string('sendmail', 'local_userapproval');
            $registereduserslist[$count]["userid"] = $registereduser->userid;
            $registereduserslist[$count]["isorgofficial"] = (!is_siteadmin() && has_capability('local/organization:manage_organizationofficial', $systemcontext)) ? true: false;
            if ($registereduser->userid) {
                if ($examenrolments) {
                    $exurl = $examenrolments ? html_writer::link("{$CFG->wwwroot}/blocks/learnerscript/viewreport.php?id=$examenrolments", $this->entity_enrolledusers('exam', $registereduser->userid), array("target" => "_blank", 'data-action' => 'enrolled_exams', 'data-value' => $registereduser->userid)) : $this->entity_enrolledusers('exam', $registereduser->userid);
                    $examlink = $this->entity_enrolledusers('exam', $registereduser->userid);//$exurl;
                }else{
                    $examlink = $this->entity_enrolledusers('exam', $registereduser->userid);
                }
                if ($programenrolments) {
                    $prurl = $programenrolments ? html_writer::link("{$CFG->wwwroot}/blocks/learnerscript/viewreport.php?id=$programenrolments", $this->entity_enrolledusers('tprogram', $registereduser->userid), array("target" => "_blank", 'data-action' => 'enrolled_exams', 'data-value' => $registereduser->userid)) : $this->entity_enrolledusers('tprogram', $registereduser->userid);
                    $progurl = $this->entity_enrolledusers('tprogram', $registereduser->userid); //$prurl;
                }else{
                    $progurl = $this->entity_enrolledusers('tprogram', $registereduser->userid);
                }
                if ($eventenrolments) {
                    $eventurl = $eventenrolments ? html_writer::link("{$CFG->wwwroot}/blocks/learnerscript/viewreport.php?id=$eventenrolments", $this->entity_enrolledusers('event', $registereduser->userid), array("target" => "_blank", 'data-action' => 'enrolled_exams', 'data-value' => $registereduser->userid)) : $this->entity_enrolledusers('event', $registereduser->userid);
                    $eventlink = $this->entity_enrolledusers('event', $registereduser->userid);//$eventurl;
                }else{
                    $eventlink = $this->entity_enrolledusers('event', $registereduser->userid);
                }
            }
            
            // $registereduserslist[$count]["examenrolledusers"] = ($registereduser->userid) ?  : 0;
            $registereduserslist[$count]["examenrolledusers"] = $registereduser->userid ? $examlink : 0;
            
            $registereduserslist[$count]["tpenrolledusers"] = ($registereduser->userid) ?  $progurl: 0;
            
            $registereduserslist[$count]["eventenrolledusers"] =  ($registereduser->userid) ? $eventlink : 0;
            $count++;


        }
        
        $coursesContext = array(
            "hascourses" => $registereduserslist,
            "totalusers" => $totalusers,
            "length" => count($registereduserslist)
        );
        return $coursesContext;
    }

    public function entity_enrolledusers($type, $userid)
    {
        global $DB, $USER;
        $context = context_system::instance();
        switch ($type) {
            case 'exam':
                $countsql = "SELECT COUNT(id) 
                               FROM {exam_enrollments} 
                              WHERE userid =". $userid . "  AND enrolstatus = 1";

               /*if(!is_siteadmin() && has_capability('local/organization:manage_organizationofficial',$context)) {
                    $formsql = " AND usercreated =". $USER->id;
               }*/
               break;
            case 'tprogram':
                $traineeid = $DB->get_field('role', 'id', ['shortname' => 'trainee']);
                $orgid = $DB->get_field('role', 'id', ['shortname' => 'organizationofficial']);

                $sql = "SELECT lu.organization 
                          FROM {local_users} lu
                          JOIN {role_assignments} ra ON ra.userid = lu.userid
                         WHERE ra.roleid = {$orgid} AND lu.userid = ". $USER->id;
                $organization = $DB->get_field_sql($sql);

                $countsql = "SELECT COUNT(pe.id) 
                               FROM {program_enrollments} pe
                               JOIN {tp_offerings} tpo ON tpo.trainingid = pe.programid AND tpo.id = pe.offeringid 
                              WHERE pe.roleid = $traineeid  AND enrolstatus = 1 AND pe.userid =". $userid;
                
               /* if(!is_siteadmin() && has_capability('local/organization:manage_organizationofficial',$context)) {
                    $formsql = " AND pe.usercreated =". $USER->id;
                }*/
                break;
            case 'event':
                $countsql = "SELECT COUNT(id) 
                               FROM {local_event_attendees}
                              WHERE userid =".$userid ." AND enrolstatus = 1";
                /*if(!is_siteadmin() && has_capability('local/organization:manage_organizationofficial',$context)) {
                    $formsql = " AND usercreated =". $USER->id;
                }*/
                break;
            default:
                return 0;
                break;
        }
        $totalenrolements = $DB->count_records_sql($countsql.$formsql);

        return $totalenrolements;
    }

    function create_user($data, $roleid = null){
        global $DB, $USER, $CFG;      
        $systemcontext = context_system::instance();
        $userdata = (object)$data;
        foreach($data as $key => $value){
            $userdata->$key = trim($value);
        } 
        if($data->authmethod){
            $userdata->auth = $data->authmethod;
        }else{
            $userdata->auth = 'manual';
        }
        
        $userdata->confirmed = 1;
        $userdata->deleted = 0;
        $userdata->mnethostid = 1;
        $userdata->username = strtolower($data->username);
        $userdata->firstname = ucfirst($data->firstname);
        $userdata->lastname =ucfirst($data->lastname);
        $userdata->city = ucfirst($data->city);
        $userdata->password = $data->password;
        $userdata->idnumber =$data->id_number;
        $userdata->passportnumber = ($data->id_type == 2) ? $data->id_number : "" ;
        try{
            $createduserid = user_create_user($userdata, false);
            if(!is_null($roleid) && $roleid != 0) {
                $role_id = (int) $roleid ;
                if($role_id) {
                   role_assign($role_id, $createduserid, $systemcontext->id);
                }
            }
            if(!is_siteadmin() && has_capability('local/organization:manage_organizationofficial',$systemcontext)) {
                $trineeroleid = $DB->get_field('role','id',['shortname'=>'trainee']);
                role_assign($trineeroleid, $createduserid, $systemcontext->id);
            }
            return $createduserid;               
        } catch(moodle_exception $e){
          print_r($e);
        }
    }   
    function create_custom_user($data,$userid,$type = null){
        global $DB, $USER, $CFG;
        if(!$USER->id){
            $user = get_admin();
        } else{
            $user = $USER;
        }
        $context = context_system::instance();

        $userrecord   = new stdClass(); 
        $userrecord->userid = $userid;
        $userrecord->firstname = ucfirst($data->firstname);
        $userrecord->lastname =ucfirst($data->lastname);
        $userrecord->firstnamearabic = $data->firstnamearabic;
        $userrecord->lastnamearabic =$data->lastnamearabic;
        $userrecord->middlenameen =$data->middlenameen;
        $userrecord->middlenamearabic =$data->middlenamearabic;
        $userrecord->thirdnameen =$data->thirdnameen;
        $userrecord->thirdnamearabic =$data->thirdnamearabic;
        $userrecord->dateofbirth = $data->dateofbirth?$data->dateofbirth:null;
        $userrecord->gender = $data->gender?$data->gender:null;
        $userrecord->lang = $data->lang;
        $userrecord->nationality = $data->nationality;
        $userrecord->id_type = $data->id_type;
        $userrecord->id_number = $data->id_number;
        $userrecord->organization = $data->organization;
        $userrecord->sector = $data->sectors?$data->sectors:null;
        $userrecord->segment = $data->segment?$data->segment:null;
        $userrecord->jobfamily = $data->jobfamily?$data->jobfamily:null;
        $userrecord->jobrole = $data->jobrole?$data->jobrole:null;
        $userrecord->jobrole_level = $data->jobrole_level?$data->jobrole_level:null;
        $userrecord->username = $data->username;
        $userrecord->email = $data->email;//strtolower($data->email);
        $userrecord->password = $data->password;
        $userrecord->phone1 = $data->phone1;
        $userrecord->country_code = $data->country_code;
        $userrecord->country = $data->country;
        $userrecord->city = ucfirst($data->city);
        $userrecord->timecreated =time();
        $userrecord->usercreated =$user->id;
        $userrecord->approvedstatus = 2;
        $userrecord->usersource = $data->usersource ? $data->usersource : null;
        $userrecord->bulkenrolltype = $data->bulkenrolltype ? $data->bulkenrolltype : null;
        $userrecord->passportnumber = ($data->id_type == 2) ? $data->id_number : "" ;
        $userrecord->bulkenrollstatus = $data->bulkenrollstatus ? $data->bulkenrollstatus : 0; 
        if($data->usersource == 'IAM'){
            $userrecord->middlenamearabic = $data->middlenamearabic;
            $userrecord->middlenameen = $data->middlenameen;
            $userrecord->thirdnamearabic = $data->thirdnamearabic;
            $userrecord->thirdnameen = $data->thirdnameen;

       
             $userrecord->nationalitycountryid = $data->nationalitycountryid ? $data->nationalitycountryid : 2;

        }else{
           // $data->middlenamearabic = ".";
           // $data->middlenameen = ".";
           // $data->thirdnamearabic = ".";
           // $data->thirdnameen = ".";
            $userrecord->nationalitycountryid = $data->nationalitycountryid ? $data->nationalitycountryid : 113;
        }
       

        try{
            $createdid =$DB->insert_record('local_users', $userrecord);
            if($createdid){
                // if($data->usersource != 'IAM'){
                //     $orgdata = $this->get_user_org_info($userid);  
                //     $data->orgcode = $orgdata->orgcode;
                //     $data->licensekey = $orgdata->licensekey;
                //     (new \local_userapproval\local\fast_service)->register_user($data);
                // }
                        
            }
           if($data->usersource != 'IAM' && (!is_siteadmin() && $type != 'bulkenroll' && !has_capability('local/organization:manage_organizationofficial', $context) &&  !has_capability('local/organization:manage_communication_officer',$context))){
                if ( $userrecord->lang == 'ar'){
                    $userrecord->user_fullname = $userrecord->firstnamearabic." ". $userrecord->lastnamearabic;
                } else{
                    $userrecord->user_fullname = $userrecord->firstname." ". $userrecord->lastname;
                }       
                $userdetails = $DB->get_record('user',array('id'=>$userid));  
                
                (new \local_userapproval\notification())->userapproval_notification('individual_registration', $touser=$userdetails,$fromuser=$user, $userrecord,$waitinglistid=0); 
            }elseif($data->usersource != 'IAM' && $type != 'bulkenroll' && (is_siteadmin() || has_capability('local/organization:manage_organizationofficial', $context) ||  has_capability('local/organization:manage_communication_officer',$context))){
                if ( $userrecord->lang == 'ar'){
                    $userrecord->user_fullname = $userrecord->firstnamearabic." ". $userrecord->lastnamearabic;
                } else{
                    $userrecord->user_fullname = $userrecord->firstname." ". $userrecord->lastname;
                }       
                $userdetails = $DB->get_record('user',array('id'=>$userid)); 
                $userrecord->givenpassword = $data->givenpassword;
                
                
                (new \local_userapproval\notification())->userapproval_notification('registration', $touser=$userdetails,$fromuser=$user, $userrecord,$waitinglistid=0); 

            }



            
     
            if(($type == 'bulkenroll')){

                if ( $userrecord->lang == 'ar'){
                    $userrecord->user_fullname = $userrecord->firstnamearabic." ". $userrecord->lastnamearabic;
                } else{
                    $userrecord->user_fullname = $userrecord->firstname." ". $userrecord->lastname;
                }       
                $userdetails = $DB->get_record('user',array('id'=>$userid)); 
                $userrecord->givenpassword = $data->givenpassword;  
                (new \local_userapproval\notification())->userapproval_notification('registration', $touser=$userdetails,$fromuser=$user, $userrecord,$waitinglistid=0); 
                
                $orgdetails = $DB->get_record('local_organization',array('id'=>$data->organization));
 
                $trainee = $DB->get_record('user',array('id'=>$userid));

             
                $orgofficial = $DB->get_record('local_users',array('userid'=>$data->orgofficial));
                if(!empty($orgofficial)){            
              

                    $orgdata = new stdClass();
                    if($orgofficial->lang = 'ar'){
                        $orgdata->user_fullname = $orgofficial->firstnamearabic. ' '. $orgofficial->lastnamearabic. ' '. $orgofficial->middlenamearabic . ' '.$orgofficial->thirdnamearabic;
                        $orgdata->organization_name =  $orgdetails->fullnameinarabic;
                        $orgdata->organization_trainee_name = $userrecord->firstnamearabic. ' '.$userrecord->lastnamearabic;
    
                    } else{
                        $orgdata->user_fullname = $orgofficial->firstname. ' '. $orgofficial->lastname. ' '. $orgofficial->middlenameen. ' '.$orgofficial->thirdnameen; 
                        $orgdata->organization_name =  $orgdetails->fullname;
                        $orgdata->organization_trainee_name = $userrecord->firstname. ' '.$userrecord->lastname;
                    }
    
                $orguser = $DB->get_record('user',array('id'=>$orgofficial->userid));
                (new \local_organization\notification())->organization_notification('organization_assigning_trainee', $touser = $orguser,$fromuser = $USER, $userrecord,$waitinglistid=0,$orgdata);           
           
                }

               
             

            }

            return $createdid;
        } catch(moodle_exception $e){
          print_r($e);
        }
       
    }
    function update_custom_user($data,$userid,$type = null){

        global $DB, $USER, $CFG;    
        if(!$USER->id){
            $user = $DB->get_record('user', ['id' => 2]);
        } else{
            $user = $USER;
        }
         if(!empty($data->jobfamily)){
            if($data->jobrole=='-1'){
               //add new job role
               $addjobrole= new stdClass();
               $addjobrole->title = $data->title;
               $addjobrole->titlearabic = $data->titlearabic;
               $addjobrole->code = $data->code;
               $addjobrole->description = $data->description;
               $addjobrole->jobfamily = $data->jobfamily;
               $addjobrole->level = $data->clevels;
               $addjobrole->ctypes = $data->ctypes;
               $addjobrole->competencies = $data->competencies;
               $addjobrole->timemodified = time();
               $addjobrole->usermodified = $USER->id;
         
               $jobrole_id = $DB->insert_record('local_jobrole_level', $addjobrole);
            }  
         }
        $data->cjobroleid =  $jobrole_id; 
        $context =context_system::instance();
        $userrecord   = new stdClass(); 
        $existingrecord = $DB->get_record('local_users',['id'=>$data->id]);
        $userrecord->id = $data->id;
        $userrecord->userid = $userid;
        $userrecord->firstname = ucfirst($data->firstname);
        $userrecord->lastname =ucfirst($data->lastname);
        $userrecord->firstnamearabic = $data->firstnamearabic;
        $userrecord->lastnamearabic =$data->lastnamearabic;
        $userrecord->middlenameen =$data->middlenameen;
        $userrecord->middlenamearabic =$data->middlenamearabic;
        $userrecord->thirdnameen =$data->thirdnameen;
        $userrecord->thirdnamearabic =$data->thirdnamearabic;
        $userrecord->dateofbirth = $data->dateofbirth?$data->dateofbirth:null;
        $userrecord->gender = $data->gender?$data->gender:null;
        $userrecord->lang = $data->lang;
        $userrecord->nationality = $data->nationality;
        $userrecord->id_type = $data->id_type;
        $userrecord->id_number = $data->id_number;
        $organization = $data->organization ? $data->organization : 0;
        $sectors = $data->sectors ? $data->sectors : null;
        $segment = $data->segment ? $data->segment : null;
        $jobfamily = $data->jobfamily ? $data->jobfamily : null;
        $data->jobrole = ($data->cjobroleid) ? $data->cjobroleid : $data->jobrole;
        $jobrole_level = $data->jobrole_level ? $data->jobrole_level : null;
        $userrecord->organization =($data->existinsystem == 0)  ? $organization : $existingrecord->organization;
        $userrecord->sector = ($data->existinsystem == 0)  ? $sectors : $existingrecord->sector;
        $userrecord->segment = ($data->existinsystem == 0)  ? $segment : $existingrecord->segment;
        $userrecord->jobfamily =($data->existinsystem == 0)  ? $jobfamily : $existingrecord->jobfamily; 
        $userrecord->jobrole = ($data->cjobroleid) ? $data->cjobroleid : $data->jobrole;
        $userrecord->jobrole_level =($data->existinsystem == 0)  ? $jobrole_level : $existingrecord->jobrole_level;
        $userrecord->username = $data->username;
        $userrecord->email = ($data->existinsystem == 0) ? $data->email : $existingrecord->email;//strtolower($data->email);
        $userrecord->password = ($data->existinsystem == 0) ? $data->password: $existingrecord->password;
        $userrecord->phone1 = ($data->existinsystem == 0) ? $data->phone1 : $existingrecord->phone1;
        $userrecord->country_code = ($data->existinsystem == 0) ? $data->country_code : $existingrecord->country_code;
        $userrecord->country = $data->country;
        $userrecord->city =($data->existinsystem == 0) ? ucfirst($data->city) : $existingrecord->city;
        $userrecord->timemodified =time();
        $userrecord->usermodified =$USER->id; 
        $userrecord->usersource = $data->usersource ? $data->usersource : null;
        $nationalitycountryid = $data->nationalitycountryid ? $data->nationalitycountryid : 2;
        $userrecord->addresscountryid = ($data->existinsystem == 0) ?  $nationalitycountryid :  (($data->nationalitycountryid == $existingrecord->nationalitycountryid) ? $existingrecord->nationalitycountryid : $data->nationalitycountryid);
        $userrecord->nationalitycountryid = ($data->existinsystem == 0) ?  $nationalitycountryid :  (($data->nationalitycountryid == $existingrecord->nationalitycountryid) ? $existingrecord->nationalitycountryid : $data->nationalitycountryid);
        // $data->nationalitycountryid = $data->nationalitycountryid ? $data->nationalitycountryid : 2;
        $userrecord->bulkenrolltype = $data->bulkenrolltype ? $data->bulkenrolltype : null; 
        $userrecord->bulkenrollstatus = $data->bulkenrollstatus ? $data->bulkenrollstatus : 0; 
        try{
           $result =$DB->update_record('local_users', $userrecord);
           if($data->notify == 'yes'){

                if ( $userrecord->lang == 'ar'){
                    $userrecord->user_fullname = $userrecord->firstnamearabic." ". $userrecord->lastnamearabic;
                } else{
                    $userrecord->user_fullname = $userrecord->firstname." ". $userrecord->lastname;
                }       
                $userdetails = get_admin();
                $userdetails->firstname =  $userrecord->user_fullname;
                $userdetails->lastname  = $userrecord->user_fullname;
                $userdetails->email = $userrecord->email;       

                (new \local_userapproval\notification())->userapproval_notification('individual_registration', $touser = $userdetails,$fromuser = get_admin(), $userrecord,$waitinglistid=0,);
           }

          if($type == 'bulkenroll'){            
                if((is_siteadmin() && !has_capability('local/organization:manage_organizationofficial', $context)) || (has_capability('local/organization:manage_examofficial', $context)) || (has_capability('local/organization:manage_trainingofficial', $context))){
                    if($existingrecord->organization != $data->organization){


                        $orgdetails = $DB->get_record('local_organization',array('id'=>$data->organization));
 
                        $trainee = $DB->get_record('user',array('id'=>$userid));        
    
                        $orgofficial =  $DB->get_record('local_users',array('userid'=>$data->orgofficial));
             
              

                    $orgdata = new stdClass();
                    if($orgofficial->lang = 'ar'){
                        $orgdata->user_fullname = $orgofficial->firstnamearabic. ' '. $orgofficial->lastnamearabic. ' '. $orgofficial->middlenamearabic . ' '.$orgofficial->thirdnamearabic;
                        $orgdata->organization_name =  $orgdetails->fullnameinarabic;
                        $orgdata->organization_trainee_name = $userrecord->firstnamearabic. ' '.$userrecord->lastnamearabic;
    
                    } else{
                        $orgdata->user_fullname = $orgofficial->firstname. ' '. $orgofficial->lastname. ' '. $orgofficial->middlenameen. ' '.$orgofficial->thirdnameen; 
                        $orgdata->organization_name =  $orgdetails->fullname;
                        $orgdata->organization_trainee_name = $userrecord->firstname. ' '.$userrecord->lastname;
                    }
    
                    $orguser = $DB->get_record('user',array('id'=>$orgofficial->userid));   
                    (new \local_organization\notification())->organization_notification('organization_assigning_trainee', $touser = $orguser,$fromuser = $USER, $userrecord,$waitinglistid=0,$orgdata);           
                



           


                    } 


               

                }
                // if ( $userrecord->lang == 'ar'){
                //     $userrecord->user_fullname = $userrecord->firstnamearabic." ". $userrecord->lastnamearabic;
                // } else{
                //     $userrecord->user_fullname = $userrecord->firstname." ". $userrecord->lastname;
                // }
                //     $userdetails = $DB->get_record('user',array('id'=>$userid));   
                //     $userrecord->password = $data->givenpassword
    
                //     (new \local_userapproval\notification())->userapproval_notification('registration', $touser = $userdetails,$fromuser = $USER, $userrecord,$waitinglistid=0,);               
           
            }






           return $result;
        } catch(moodle_exception $e){
          print_r($e);
        }
    } 
    function user_update_user($data,$approveduserid, $roleid = null){
        global $DB, $USER, $CFG;  

        $systemcontext = context_system::instance();
        $userdata = (object)$data;
        $userdata->id=$approveduserid;
        $existingrecord = $DB->get_record('user',['id'=>$approveduserid]);
        $existingpw = $DB->get_field('local_users','password',['userid'=>$approveduserid]);
          foreach($data as $key => $value){
            $userdata->$key = trim($value);
        } 
        $password = ($data->existinsystem == 0) ? $userdata->password : $existingpw;
        if(empty($password)){
            unset($password);
        }else{
            $userdata->password = $password;
        }
        if($data->authmethod){
            $userdata->auth = $data->authmethod;
        }else{
            $userdata->auth = 'manual';
        }
        $userdata->confirmed = 1;
        $userdata->deleted = 0;
        $userdata->mnethostid = 1;
        $userdata->username = strtolower($data->username);
        $userdata->firstname = ucfirst($data->firstname);
        $userdata->lastname =ucfirst($data->lastname);
        $userdata->city =  ($data->existinsystem == 0) ? ucfirst($data->city) : $existingrecord->city;
        $userdata->phone1 = ($data->existinsystem == 0) ? $data->phone1 : $existingrecord->phone1;
        $userdata->country_code = ($data->existinsystem == 0) ? $data->country_code : $existingrecord->country_code;
        $userdata->email = ($data->existinsystem == 0) ? $data->email : $existingrecord->email;//strtolower($data->email);
        $userdata->idnumber = ($data->existinsystem == 0) ? $data->id_number : $existingrecord->idnumber;
        $userdata->country = ($data->existinsystem == 0) ? $data->country : (($existingrecord->country == $data->country)  ? $existingrecord->country:$data->country);
        
        try{
           $result = user_update_user($userdata, false);
            if($userdata->id == $USER->id){
               $user = core_user::get_user($userdata->id);
               \core\session\manager::set_user($user);
            }
            if(!is_null($roleid)) {
                $role_id = $roleid;
                $manager_role = (int)$DB->get_field('role','id',array('shortname' => 'manager'));

                // un asssign training official role 
                $to_role_id = (int)$DB->get_field('role','id',array('shortname' => 'to'));
                $programcatagoryid = $DB->get_field('course_categories','id',array('idnumber' => 'trainingprogram'));
                $programcontext = \context_coursecat::instance($programcatagoryid);
                $is_existing_role_is_training_official = $DB->record_exists_sql('SELECT id FROM {role_assignments} WHERE roleid = '.$to_role_id.' AND userid = '.$approveduserid.' AND contextid = '.$programcontext->id.'');
                if($is_existing_role_is_training_official){
                    
                    role_unassign($manager_role, $approveduserid, $programcontext->id);
                }
                
                // un assign exam official role
                $eo_role_id = (int)$DB->get_field('role','id',array('shortname' => 'examofficial'));
                $examcatagoryid = $DB->get_field('course_categories','id',array('idnumber' => 'exams'));
                $examcontext = \context_coursecat::instance($examcatagoryid);
                $is_existing_role_is_exam_official = $DB->record_exists_sql('SELECT id FROM {role_assignments} WHERE roleid = '.$eo_role_id.' AND userid = '.$approveduserid.' AND contextid = '.$examcontext->id.'');
                if($is_existing_role_is_exam_official){
                    role_unassign($manager_role, $approveduserid, $examcontext->id);
                }
                
                // un assign apart from exam/training official role
                $is_not_training_or_exam_official = $DB->record_exists_sql('SELECT id FROM {role_assignments} WHERE  userid = '.$approveduserid.' AND contextid = '.$systemcontext->id.'');
                if($is_not_training_or_exam_official) {
                    $existingroleid = (int) $DB->get_field_sql('SELECT roleid  FROM {role_assignments} WHERE  userid = '.$approveduserid.' AND contextid = '.$systemcontext->id.'');
                    if($existingroleid) {
                        role_unassign($existingroleid, $approveduserid, $systemcontext->id);
                    }
                }
                // assign new role
                role_assign($role_id, $approveduserid, $systemcontext->id); 
           
            }
           
           return $result;
        } catch(moodle_exception $e){
          print_r($e);
        }
        
    } 
     function update_register_user($data){
        global $DB, $USER, $CFG; 

        $userrecord   = new \stdClass(); 
        $userrecord->id = $data->id;
        $userrecord->firstname = ucfirst($data->firstname);
        $userrecord->lastname =ucfirst($data->lastname);
        $userrecord->firstnamearabic = $data->firstnamearabic;
        $userrecord->lastnamearabic =$data->lastnamearabic;
        $userrecord->middlenameen =$data->middlenameen;
        $userrecord->middlenamearabic =$data->middlenamearabic;
        $userrecord->thirdnameen =$data->thirdnameen;
        $userrecord->thirdnamearabic =$data->thirdnamearabic;
        $userrecord->dateofbirth = $data->dateofbirth?$data->dateofbirth:null;
        $userrecord->gender = $data->gender;
        $userrecord->lang = $data->lang;
        $userrecord->nationality = $data->nationality;
        $userrecord->id_type = $data->id_type;
        $userrecord->id_number = $data->id_number;
        $userrecord->organization = $data->organization;
        $userrecord->sector = $data->sectors?$data->sectors:null;
        $userrecord->segment = $data->segment?$data->segment:null;
        $userrecord->jobfamily = $data->jobfamily?$data->jobfamily:null;
        $userrecord->jobrole = $data->jobrole?$data->jobrole:null;
        $userrecord->jobrole_level = $data->jobrole_level?$data->jobrole_level:null;
        $userrecord->username = $data->username;
        $userrecord->email = $data->email;//strtolower($data->email);
        $userrecord->password =$data->password;
        $userrecord->phone1 = $data->phone1;
        $userrecord->country_code = $data->country_code;
        $userrecord->country = $data->country;
        $userrecord->city = ucfirst($data->city);
        $userrecord->timemodified =time();
        $userrecord->usermodified =$USER->id;
        $userrecord->bulkenrolltype = $data->bulkenrolltype ? $data->bulkenrolltype : null; 
        $userrecord->bulkenrollstatus = $data->bulkenrollstatus ? $data->bulkenrollstatus : 0; 
        try{
           $result =$DB->update_record('local_users', $userrecord);
           return $result;
        } catch(moodle_exception $e){
          print_r($e);
        }
        
    }  
    function approve_custom_user($data,$userid){
        global $DB, $USER, $CFG;      
        $systemcontext = context_system::instance();
        $userrecord   = new \stdClass(); 
        $userrecord->id = $data->id;
        $userrecord->userid =$userid;
        $userrecord->firstname = ucfirst($data->firstname);
        $userrecord->lastname =ucfirst($data->lastname);
        $userrecord->firstnamearabic = $data->firstnamearabic;
        $userrecord->lastnamearabic =$data->lastnamearabic;
        $userrecord->middlenameen =$data->middlenameen;
        $userrecord->middlenamearabic =$data->middlenamearabic;
        $userrecord->thirdnameen =$data->thirdnameen;
        $userrecord->thirdnamearabic =$data->thirdnamearabic;
        $userrecord->dateofbirth = $data->dateofbirth?$data->dateofbirth:null;
        $userrecord->gender = $data->gender;
        $userrecord->lang = $data->lang;
        $userrecord->nationality = $data->nationality;
        $userrecord->id_type = $data->id_type;
        $userrecord->id_number = $data->id_number;
        $userrecord->organization = $data->organization;
        $userrecord->sector = $data->sector?$data->sector:null;
        $userrecord->segment = $data->segment?$data->segment:null;
        $userrecord->jobfamily = $data->jobfamily?$data->jobfamily:null;
        $userrecord->jobrole = $data->jobrole?$data->jobrole:null;
        $userrecord->jobrole_level = $data->jobrole_level?$data->jobrole_level:null;
        $userrecord->username = $data->username;
        $userrecord->email = strtolower($data->email);
        $userrecord->password = $data->password;
        $userrecord->phone1 = $data->phone1;
        $userrecord->country_code = $data->country_code;
        $userrecord->country = $data->country;
        $userrecord->city = ucfirst($data->city);
        $userrecord->timecreated =time();
        $userrecord->usercreated =$USER->id;
        $userrecord->approvedstatus =2;
        $data->nationalitycountryid = $data->nationalitycountryid ? $data->nationalitycountryid : 2;
        try{
           $result =$DB->update_record('local_users', $userrecord);
           if($result) {
          //  (new \local_userapproval\local\fast_service)->register_user($data);
		   $DB->execute("UPDATE {organization_requests} SET userid = $userid, userstatus = 2 WHERE userid = $data->id AND userstatus = 1");
		    $role_id = (int) $DB->get_field('role', 'id', array('shortname' => 'trainee'));
		    if($role_id) {
		       role_assign($role_id, $userid, $systemcontext->id);
		    }
            $registereduser = get_admin();
            $registereduser->firstname = $userrecord->firstname;
            $registereduser->lastname =$userrecord->lastname;
            $registereduser->email = $userrecord->email;
            $registereduser->lang = $userrecord->lang ;
            
 
 
            if($userrecord->lang == 'ar'){
                   $userrecord->user_fullname =   $userrecord->firstnamearabic . " ".  $userrecord->lastnamearabic;
            }else{
              $userrecord->user_fullname = $userrecord->firstname." ". $userrecord->lastname;
            }
                       
            (new \local_userapproval\notification())->userapproval_notification('individual_registration', $touser=$registereduser,$fromuser=$USER, $userrecord,$waitinglistid=0);
                
           }
          $approveduser=get_admin();
          $approveduser->firstname=$userrecord->firstname;
          $approveduser->lastname=$userrecord->lastname;
          $approveduser->email=$userrecord->email;
          $userrecord->user_fullname= $userrecord->firstname. $userrecord->lastname;
          (new \local_userapproval\notification())->userapproval_notification('approve', $touser= $approveduser,$fromuser=$USER, $userrecord,$waitinglistid=0);
           
           return $result;
        } catch(moodle_exception $e){
          print_r($e);
        }
        
    }
    public function organization_request_update_record($organization,$approveduserid){
        global $DB, $USER;


        if($organization) {
        
            if($DB->record_exists('organization_requests',  array('orgid'=> $organization,'userid'=>$approveduserid))){

                try {

                    $orgrequestresult=$DB->execute("UPDATE {organization_requests} SET status = 1 WHERE orgid = $organization AND userid = $approveduserid"); 
                    // if($orgrequestresult){
                    //    $result=$DB->execute("UPDATE {local_users} SET organization = 0 WHERE userid = $approveduserid"); 
                    // }
                    return $orgrequestresult;
               } catch(moodle_exception $e){
                  print_r($e);
               }
                
            } else {
    
                $userrecord   = new \stdClass(); 
                $userrecord->orgid =$organization ? $organization : 0;
                $userrecord->userid =$approveduserid;
                $userrecord->timecreated =time();
                $userrecord->usercreated =$USER->id; 
                $userrecord->userstatus = 2; 
                try{
                    $orgrequestresult =$DB->insert_record('organization_requests', $userrecord);
                    // if($orgrequestresult){
                    //     $result=$DB->execute("UPDATE {local_users} SET organization = $organization WHERE userid = $approveduserid"); 
                    // }
                    return $orgrequestresult;
                } catch(moodle_exception $e){
                  print_r($e);
                } 
            }  
        }
          
    }  
    function delete_user($userid){
        global $DB, $USER, $CFG;
        try{
            $result=$DB->execute('UPDATE {local_users} SET deleted = 1 WHERE id = '.$userid); 
           // return $result;
        } catch(moodle_exception $e){
          print_r($e);
        } 
        $approveduserid=$DB->get_field('local_users','userid',array('id'=>$userid));
        if($approveduserid) {
           $user = $DB->get_record('user', ['id' => $approveduserid]);
        //    $approveduserdeleteresult=$DB->execute('UPDATE {user} SET deleted = 1 WHERE id = '.$approveduserid); 
           delete_user($user);
        }

        return $result;
        
    } 
    function reject_user_registration($userid){
    
        global $DB, $USER, $CFG;
        try{
            $result=$DB->execute('UPDATE {local_users} SET approvedstatus = 3 WHERE id = '.$userid); 
            $userrejected=$DB->get_record('local_users',array('id'=>$userid)); 
            $usereject=get_admin();
            $usereject->firstname= $userrejected->firstname;
            $usereject->lastname=$userrejected->lastname;
            $usereject->email= $userrejected->email;
            $userrejected->user_fullname= $userrejected->firstname.$userrejected->lastname;
            (new \local_userapproval\notification())->userapproval_notification('reject', $touser= $usereject,$fromuser=$USER, $userrejected,$waitinglistid=0); 
            return $result;
        } catch(moodle_exception $e){
          print_r($e);
        }
       
    }  
    function local_users_logs($event, $module, $description, $userid){
        global $DB, $USER, $CFG;       
        $log_data               = new \stdClass();
        $log_data->event        = $event;
        $log_data->module       = $module;
        $log_data->description  = $description;
        $log_data->timecreated  = time();
        if($userid > 0) {
            $log_data->userid  = $userid;
        }
        try{
            $result = $DB->insert_record('local_users_logs', $log_data);
            return $result;
        } catch(moodle_exception $e){
          print_r($e);
        } 
        
    }  
    public function registrationinfo($requestid,$userid,$username,$requesttype=null) {
        global $DB, $PAGE, $OUTPUT;
        $systemcontext = context_system::instance();
        $PAGE->set_context($systemcontext);
        $data = $DB->get_record('local_users', ['id' => $userid], '*', MUST_EXIST);
        $renderer = $PAGE->get_renderer('local_userapproval');
        if($requesttype !=null){
            $data->requesttype = $requesttype;
            $data->requestid = $requestid;
        }
        $regdata  = $renderer->registration_view($data);
       
        return $regdata;
    }

   public static function org_sector($jobroleid=0) {

        global $DB, $USER;

        $current_lang = current_language();

        if($jobroleid){

            if($current_lang == 'ar') {

                $sql = 'SELECT sect.id,sect.titlearabic as title
                                            FROM {local_sector} as sect 
                                            JOIN {local_segment} as seg ON seg.sectorid=sect.id 
                                            JOIN  {local_jobfamily} as jbfm ON jbfm.segmentid=seg.id 
                                            JOIN {local_jobrole_level} as jbrl ON jbrl.jobfamily=jbfm.id 
                                            WHERE jbrl.id=:jobroleid';
            } else {

                $sql = 'SELECT sect.id,sect.title as title
                                            FROM {local_sector} as sect 
                                            JOIN {local_segment} as seg ON seg.sectorid=sect.id 
                                            JOIN  {local_jobfamily} as jbfm ON jbfm.segmentid=seg.id 
                                            JOIN {local_jobrole_level} as jbrl ON jbrl.jobfamily=jbfm.id 
                                            WHERE jbrl.id=:jobroleid';
            }

            

          $sector= $DB->get_record_sql($sql,['jobroleid' => $jobroleid]);

        } else {
             if($current_lang == 'ar') {

                $sector= $DB->get_records_sql_menu("SELECT id,titlearabic as title FROM {local_sector}");


             } else {

                $sector= $DB->get_records_sql_menu("SELECT id,title as title FROM {local_sector}");


             }


            $sector = array(null => get_string('choosesector', 'auth_registration')) + $sector ;
            
        }


        return $sector;
            
    } 
    public static function org_segment($jobroleid=0,$segments= array(),$userid=0) {

        global $DB, $USER;

        $current_lang = current_language();

        $segment=array();

        if($jobroleid){

            if($current_lang == 'ar') {

                $segment= $DB->get_record_sql('SELECT seg.id,seg.titlearabic as title
                                                FROM {local_segment} as seg 
                                                JOIN  {local_jobfamily} as jbfm ON jbfm.segmentid=seg.id 
                                                JOIN {local_jobrole_level} as jbrl ON jbrl.jobfamily=jbfm.id 
                                                 WHERE jbrl.id=:jobroleid',['jobroleid' => $jobroleid]);

            } else {

                $segment= $DB->get_record_sql('SELECT seg.id,seg.title as title
                                                FROM {local_segment} as seg 
                                                JOIN  {local_jobfamily} as jbfm ON jbfm.segmentid=seg.id 
                                                JOIN {local_jobrole_level} as jbrl ON jbrl.jobfamily=jbfm.id 
                                                 WHERE jbrl.id=:jobroleid',['jobroleid' => $jobroleid]);


            }

           

        }elseif(!empty($segments)){

            $params = array();

            list($segmentssql, $segmentparams) = $DB->get_in_or_equal($segments, SQL_PARAMS_NAMED, 'sgmnt');
            $params = array_merge($params, $segmentparams);
            if($current_lang == 'ar') {
            
               $segment= $DB->get_records_sql_menu("SELECT id,titlearabic as title FROM {local_segment} WHERE id $segmentssql",$params);
            } else {

                $segment= $DB->get_records_sql_menu("SELECT id,title as title FROM {local_segment} WHERE id $segmentssql",$params);
            }

        }elseif($userid){

            if($current_lang == 'ar') {

                $segment= $DB->get_records_sql_menu('SELECT seg.id, seg.titlearabic as title
                                                FROM {local_segment} as seg 
                                                JOIN {local_users} as cmtc ON cmtc.segment=seg.id
                                                 WHERE cmtc.id=:userid',['userid' => $userid]);
            } else {

                $segment= $DB->get_records_sql_menu('SELECT seg.id,seg.title as title
                                                FROM {local_segment} as seg 
                                                JOIN {local_users} as cmtc ON cmtc.segment=seg.id
                                                 WHERE cmtc.id=:userid',['userid' => $userid]);


           }


        }

        return $segment;   
    }
    
    public static function org_jobfamily($jobroleid=0,$jobfamilies= array(),$id=0) {

        global $DB, $USER;
        $current_lang = current_language();

        if($jobroleid){

            if($current_lang == 'ar') {
 
                $jobfamily= $DB->get_record_sql('SELECT jbfm.id,jbfm.familynamearabic as title 
                                                    FROM {local_jobfamily} as jbfm 
                                                    JOIN {local_jobrole_level} as jbrl ON jbrl.jobfamily=jbfm.id 
                                                     WHERE jbrl.id=:jobroleid',['jobroleid' => $jobroleid]);
           } else {


               $jobfamily= $DB->get_record_sql('SELECT jbfm.id,jbfm.familyname as title 
                                                    FROM {local_jobfamily} as jbfm 
                                                    JOIN {local_jobrole_level} as jbrl ON jbrl.jobfamily=jbfm.id 
                                                     WHERE jbrl.id=:jobroleid',['jobroleid' => $jobroleid]);

           }

        }elseif(!empty($jobfamilies)){

            $params = array();

            list($jobfamiliessql, $jobfamiliesparams) = $DB->get_in_or_equal($jobfamilies, SQL_PARAMS_NAMED, 'jobfml');
            $params = array_merge($params, $jobfamiliesparams);
            

            if($current_lang == 'ar') {

                $jobfamily= $DB->get_records_sql_menu("SELECT id,familynamearabic as title FROM {local_jobfamily} WHERE id $jobfamiliessql",$params);
            } else {
                
                $jobfamily= $DB->get_records_sql_menu("SELECT id,familyname as title FROM {local_jobfamily} WHERE id $jobfamiliessql",$params);

            }

        }elseif($id){

           
               
            if($current_lang == 'ar') {


               $jobfamily= $DB->get_records_sql_menu('SELECT jbfm.id,jbfm.familynamearabic as title 
                                                    FROM {local_jobfamily} as jbfm 
                                                    JOIN {local_users} as cmtc ON cmtc.jobfamily=jbfm.id
                                                 WHERE cmtc.id=:userid',['userid' => $id]);
            } else {


                $jobfamily= $DB->get_records_sql_menu('SELECT jbfm.id,jbfm.familyname as title 
                                                    FROM {local_jobfamily} as jbfm 
                                                    JOIN {local_users} as cmtc ON cmtc.jobfamily=jbfm.id
                                                 WHERE cmtc.id=:userid',['userid' => $id]);


            } 


        }

        return $jobfamily;
            
    }
    public static function org_jobrole($jobroleid=0,$jobroles= array(),$userid=0) {

        global $DB, $USER;

        $current_lang = current_language();

        if($jobroleid){
            if($current_lang == 'ar') {

                $jobrole= $DB->get_record_sql('SELECT id,titlearabic as title,level,description FROM {local_jobrole_level} WHERE id=:jobroleid',['jobroleid' => $jobroleid]);
            } else {

                $jobrole= $DB->get_record_sql('SELECT id,title as title,level,description FROM {local_jobrole_level} WHERE id=:jobroleid',['jobroleid' => $jobroleid]);
            }

        }elseif(!empty($jobroles)){

            $params = array();

            list($jobrolessql, $jobrolesparams) = $DB->get_in_or_equal($jobroles, SQL_PARAMS_NAMED, 'jobrl');
            $params = array_merge($params, $jobrolesparams);
            
            if($current_lang == 'ar') {

                $jobrole= $DB->get_records_sql_menu("SELECT id,titlearabic as title FROM {local_jobrole_level} WHERE id $jobrolessql",$params);

            } else {

                $jobrole= $DB->get_records_sql_menu("SELECT id,title as title FROM {local_jobrole_level} WHERE id $jobrolessql",$params);


            }

        }elseif($userid){

            if($current_lang == 'ar') {

            $jobrole= $DB->get_records_sql_menu('SELECT jbrl.id,jbrl.titlearabic as title 
                                                    FROM {local_jobrole_level} as jbrl
                                                    JOIN {local_users} as cmtc ON cmtc.jobrole=jbrl.id
                                                 WHERE cmtc.id=:userid',['userid' => $userid]);
            } else {


                $jobrole= $DB->get_records_sql_menu('SELECT jbrl.id,jbrl.title as title 
                                                    FROM {local_jobrole_level} as jbrl
                                                    JOIN {local_users} as cmtc ON cmtc.jobrole=jbrl.id
                                                 WHERE cmtc.id=:userid',['userid' => $userid]);


            }

        }

        return $jobrole;
            
    }

    public function create_customization($data){
        global $USER,$DB;
        $context = context_system::instance();
        $userdata= new \stdClass();
        $userdata->id=$data->userid;
        $userdata->picture=$data->profileimages;
        $userdata->address=$data->address;
        $userdata->email =$data->email;
        $userdata->phone1=$data->contact;
        $userdata->city=$data->city;
       
        $userdata->country=$data->country;
        $userdata->firstname=$data->firstname;
        $userdata->lastname=$data->lastname;
        if($data->password) {
            $userdata->password=hash_internal_user_password($data->password);
        }
        try{
            $result = user_update_user($userdata, false);
        } catch(moodle_exception $e){
          print_r($e);
        } 
        $localuserdata= new \stdClass();
        $localuserdata->id=$data->id;
        $localuserdata->firstname=$data->firstname;
        $localuserdata->lastname=$data->lastname;
        $localuserdata->bannerimage=$data->bannerimages;
      
        $localuserdata->sector=$data->sectors?$data->sectors:null;
        $localuserdata->segment=$data->segment?$data->segment:null;
        $localuserdata->jobfamily=$data->jobfamily?$data->jobfamily:null;
        $localuserdata->jobrole=$data->jobrole?$data->jobrole:null;

        $localuserdata->certificates=$data->certificates ? $data->certificates : 0;
        $localuserdata->linkedinprofile=$data->linkedin;
        $localuserdata->id_number=$data->idnumber;
        $localuserdata->gender=$data->gender;
        $localuserdata->email =$data->email;
        $localuserdata->phone1=$data->contact;
        $localuserdata->city=$data->city;
        $localuserdata->nationality=$data->country;
        if($data->password) {
            $localuserdata->password=hash_internal_user_password($data->password);
        }
        
        try{
            $result =$DB->update_record('local_users', $localuserdata);
        } catch(moodle_exception $e){
          print_r($e);
        } 

        file_save_draft_area_files($data->profileimages,  $context->id,  'local_userapproval',  'image',  $data->profileimages);
        file_save_draft_area_files($data->bannerimages,  $context->id,  'local_userapproval',  'image',  $data->bannerimages);
    }
    public function orgrequestsfakeblock() { 
        global  $PAGE,$USER,$DB;
        $systemcontext = context_system::instance();
        if(!is_siteadmin() && has_capability('local/organization:manage_organizationofficial', $systemcontext)) {

            $organization = $DB->get_field('local_users', 'organization', ['userid' => $USER->id]);

            if(!is_null($organization) && $organization != 0) {
                $bc = new \block_contents();
                $bc->title = get_string('myorgpendingrequests', 'local_userapproval');
                $bc->attributes['class'] = 'my_requests';
                $bc->content =(new manageuser)->orgrequest_data();
                $PAGE->requires->js_call_amd('local_userapproval/dynamicForm', 'init');
                $PAGE->blocks->add_fake_block($bc, 'content');
            }

           
        } 
    }
   public function orgrequest_data() {
      global $DB, $PAGE, $OUTPUT, $CFG, $USER;
    $systemcontext = context_system::instance();
    $renderer = $PAGE->get_renderer('local_userapproval');
    $filterparams  = $renderer->get_orgrequest_cardview(true);
    $filterparams['submitid'] = 'form#filteringform';
    $filterparams['widthclass'] = 'col-md-6';
    $filterparams['placeholder'] = get_string('search_requests','local_userapproval');
    $globalinput=$renderer->global_filter($filterparams);
    $request_block = $renderer->get_orgrequest_cardview();
    $filterparams['request_block_view'] = $request_block;
    $filterparams['globalinput'] = $globalinput;
    return $renderer->listoforgrequest_data($filterparams);

   }
   public function orgrequest_dashboard_data($stable, $filterdata) {
        global $DB, $PAGE, $OUTPUT, $CFG, $USER;
        $systemcontext = context_system::instance();
        if(!is_siteadmin() && has_capability('local/organization:manage_organizationofficial', $systemcontext)) {
            $organization = $DB->get_field('local_users', 'organization', ['userid' => $USER->id]);
            if($organization){
                $selectsql = "SELECT orgr.id as requestid,u.firstname,u.lastname,u.id,concat(u.firstname,' ',u.lastname) as fullname,
                                     orgr.orgid,orgr.status,orgr.timecreated 
                                FROM {user} AS u 
                                JOIN {local_users} AS lc ON lc.userid = u.id
                                JOIN {organization_requests} AS orgr ON orgr.userid = u.id 
                               WHERE u.id > 2 AND u.deleted = 0  AND orgr.status= 1 AND orgr.userstatus = 2 AND orgr.orgid='$organization' "; 
                $countsql  = "SELECT COUNT(u.id) 
                                FROM {user} u 
                                JOIN {local_users} AS lc ON lc.userid = u.id
                                JOIN {organization_requests} orgr ON orgr.userid = u.id 
                               WHERE u.id > 2 AND u.deleted = 0  AND orgr.status= 1 AND orgr.userstatus = 2 AND orgr.orgid='$organization'";
                if  (isset($filterdata->search_query) && trim($filterdata->search_query) != ''){
                    $formsql = " AND (u.firstname LIKE :firstnamesearch OR u.lastname LIKE :lastnamesearch OR lc.firstnamearabic LIKE :firstnamearabicsearch OR lc.lastnamearabic LIKE :lastnamearabicsearch) ";
                    $searchparams = array(
                        'firstnamesearch' => '%'.trim($filterdata->search_query).'%',
                        'lastnamesearch' => '%'.trim($filterdata->search_query).'%',
                        'firstnamearabicsearch' => '%'.trim($filterdata->search_query).'%',
                        'lastnamearabicsearch' => '%'.trim($filterdata->search_query).'%'
                    );
                } else {
                    $searchparams = array();
                }
                $params = array_merge($searchparams);
                $totalusers = $DB->count_records_sql($countsql.$formsql,$params);
                $formsql .=" ORDER BY orgr.timecreated DESC";
                $users = $DB->get_records_sql($selectsql.$formsql, $params, $stable->start,$stable->length);

                 $userslist = array();
            $count = 0;
            $i = 1;
            foreach($users as $registereduser) {
                $userslist[$count]['coid']=$i++;
                $userslist[$count]['userid']=$registereduser->id;
                $userslist[$count]['orgid']=$registereduser->orgid;
                $userslist[$count]['orgname']=$DB->get_field('local_organization','fullname',array('id'=>$registereduser->orgid));
                $userslist[$count]['requestid']=$registereduser->requestid;
                $userslist[$count]['fullname'] =$registereduser->fullname;
                $userslist[$count]['timecreated'] = userdate($registereduser->timecreated,get_string('strftimedatemonthabbr', 'core_langconfig'));

        
                $count++;
            }
            $coursesContext = array(
                "hascourses" => $userslist,
                "nocourses" => $nocourse,
                "totalusers" => $totalusers,
                "length" => count($userslist),
            );
            return $coursesContext;
                 
            } 
        }
       
       
    }   


   public function add_field_expertise($data){
        global $USER,$DB, $OUTPUT, $PAGE;
          $context= context_system::instance();
         $traineeroleid = $DB->get_field('role', 'id', array('shortname' => 'trainee'));

       if ($data->fieldexpertisetype == 0){
            $localtrainerdata= (object)$data;
            $localtrainerdata->userid=$data->userid;     
            $localtrainerdata->qualifications=$data->qualification;
            $localtrainerdata->yearsofexperience=$data->yearsofexperience;
            $localtrainerdata->fieldoftraining=$data->fieldoftraining?$data->fieldoftraining:0;
            $localtrainerdata->certificates=$data->certificates;
            $localtrainerdata->training_programs=$data->training_programs;
            $localtrainerdata->requestdate=time();
            
            try{
              $result =$DB->insert_record('local_trainer_request', $localtrainerdata);
            } catch(moodle_exception $e){
              print_r($e);
            } 
            
        } else if($data->fieldexpertisetype == 1){

            $localexpertdata= (object)$data;
            $localexpertdata->userid=$data->userid;     
            $localexpertdata->qualifications=$data->qualification;
            $localexpertdata->yearsofexperience=$data->yearsofexperience;
            $localexpertdata->fieldoftraining=$data->fieldoftraining?$data->fieldoftraining:0;
            $localexpertdata->fieldofexperience=$data->fieldofexperience?$data->fieldofexperience:0;
            $localexpertdata->certificates=$data->certificates;
            $localexpertdata->requestdate=time();
            try{
              $result =$DB->insert_record('local_expert_request', $localexpertdata);
            } catch(moodle_exception $e){
              print_r($e);
            }
        }
        file_save_draft_area_files($data->qualification,  $context->id,  'local_userapproval',  'qualification',  $data->qualification);
        return $result; 
    }
    public function delete_bannerimage($userid){
        global $DB, $USER, $CFG;
       
        $result=$DB->execute('UPDATE {local_users} SET bannerimage = 0 WHERE id = '.$userid); 
        
    }
    public function myorgpendingrequestsinfo() {
        global $DB, $PAGE, $OUTPUT, $CFG, $USER;
        $systemcontext = context_system::instance();
        $renderer = $PAGE->get_renderer('local_userapproval');
        $filterparams  = $renderer->get_catalog_totalorgrequests(true);
        $filterparams['submitid'] = 'form#filteringform';
        $filterparams['widthclass'] = 'col-md-6';
        $filterparams['placeholder'] = get_string('search_requests','local_userapproval');
        $globalinput=$renderer->global_filter($filterparams);
        $userdetails = $renderer->get_catalog_totalorgrequests();
        $filterparams['userdetails'] = $userdetails;
        $filterparams['globalinput'] = $globalinput;
        $renderer->listofpendingorgrequests($filterparams);
    
    }
    public function total_orgrequest_data($stable, $filterdata) {
        global $DB, $PAGE, $OUTPUT, $CFG, $USER;
        $systemcontext = context_system::instance();

        $currentlang= current_language();

        $selectsql = "SELECT orgr.id as requestid,u.firstname,u.lastname,lu.firstnamearabic,lu.lastnamearabic,lu.middlenameen,lu.middlenamearabic,lu.thirdnameen,lu.thirdnamearabic,u.id,orgr.orgid,
                        orgr.status as requeststatus,orgr.timecreated,u.email,u.phone1 
                        FROM {user} u 
                        JOIN {local_users} lu ON lu.userid = u.id 
                        JOIN {organization_requests} orgr ON orgr.userid = u.id 
                       WHERE u.id > 2 AND u.deleted = 0  AND lu.deleted = 0 AND orgr.userstatus = 2"; 
        $countsql  = "SELECT COUNT(u.id) 
                        FROM {user} u 
                        JOIN {local_users} lu ON lu.userid = u.id 
                        JOIN {organization_requests} orgr ON orgr.userid = u.id 
                       WHERE u.id > 2 AND u.deleted = 0  AND lu.deleted = 0 AND orgr.userstatus = 2 ";

        if(is_siteadmin ()) {
            $condition = " ";
        }elseif(!is_siteadmin() && has_capability('local/organization:manage_organizationofficial', $systemcontext)) {
            $organization = $DB->get_field('local_users', 'organization', ['userid' => $USER->id]);
            $condition = " AND orgr.orgid = $organization ";
        }
        if  (isset($filterdata->search_query) && trim($filterdata->search_query) != ''){
            $formsql .= " AND (u.firstname LIKE :firstnamesearch OR 
                               u.lastname LIKE :lastnamesearch  OR 
                               lu.firstnamearabic LIKE :firstnamearabicsearch OR
                               lu.lastnamearabic LIKE :lastnamearabicsearch  OR
                               lu.middlenameen LIKE :middlenameensearch OR
                               lu.middlenamearabic LIKE :middlenamearabicsearch OR
                               lu.thirdnameen LIKE :thirdnameensearch OR 
                               lu.thirdnamearabic LIKE :thirdnamearabicsearch OR 
                               u.email LIKE :emailsearch  OR 
                               u.phone1 LIKE :mobilesearch) ";
            $searchparams = array(
                'firstnamesearch' => '%'.trim($filterdata->search_query).'%',
                'lastnamesearch' => '%'.trim($filterdata->search_query).'%',
                'firstnamearabicsearch' => '%'.trim($filterdata->search_query).'%',
                'lastnamearabicsearch' => '%'.trim($filterdata->search_query).'%',
                'middlenameensearch' => '%'.trim($filterdata->search_query).'%',
                'middlenamearabicsearch' => '%'.trim($filterdata->search_query).'%',
                'thirdnameensearch' => '%'.trim($filterdata->search_query).'%',
                'thirdnamearabicsearch' => '%'.trim($filterdata->search_query).'%',
                'emailsearch' => '%'.trim($filterdata->search_query).'%',
                'mobilesearch' => '%'.trim($filterdata->search_query).'%'
            );
        } else {
            $searchparams = array();
        }
        $params = array_merge($searchparams);
        $totalusers = $DB->count_records_sql($countsql.$condition.$formsql,$params);
        $formsql .=" ORDER BY orgr.timecreated DESC";
        $users = $DB->get_records_sql($selectsql.$condition.$formsql, $params, $stable->start,$stable->length);
        $userslist = array();
        $count = 0;
        $i = 1;
        foreach($users as $registereduser) {

            if( $currentlang == 'ar' && !empty($registereduser->firstnamearabic)){
                $firstname = $registereduser->firstnamearabic;
            }else{
                $firstname = $registereduser->firstname;
            }
            if( $currentlang == 'ar' && !empty($registereduser->lastnamearabic)){
                $lastname = $registereduser->lastnamearabic;
            }else{
                $lastname = $registereduser->lastname;
            }
            if( $currentlang == 'ar' && !empty($registereduser->middlenamearabic)){
                $middlename = $registereduser->middlenamearabic;
            }else{
                $middlename = $registereduser->middlenameen;
            }

            if( $currentlang == 'ar' && !empty($registereduser->thirdnamearabic)){
                $thirdname = $registereduser->thirdnamearabic;
            }else{
                $thirdname = $registereduser->thirdnameen;
            }
    
            $userslist[$count]['coid']=$i++;
            $userslist[$count]['userid']=$registereduser->id;
            $userslist[$count]['orgid']=$registereduser->orgid;
            $userslist[$count]['orgname']=$registereduser->orgid ? (($currentlang == 'ar')? $DB->get_field('local_organization','fullnameinarabic',array('id'=>$registereduser->orgid)): $DB->get_field('local_organization','fullname',array('id'=>$registereduser->orgid))):'';
            $userslist[$count]['requestid']=$registereduser->requestid;
            $userslist[$count]['fullname'] =$firstname.' '.$middlename.' '.$thirdname.' '.$lastname;
            $userslist[$count]['email'] =$registereduser->email;
            $userslist[$count]['actions'] =$registereduser->requeststatus == 1 ?true :false;
            if($registereduser->requeststatus == 1){
                $userslist[$count]['requeststatus'] = get_string('pending','local_userapproval');
            } elseif ($registereduser->requeststatus == 2) {
                $userslist[$count]['requeststatus'] =get_string('approved','local_userapproval');
            } else {
               $userslist[$count]['requeststatus'] =get_string('rejected','local_userapproval');
            }


            $userslist[$count]['timecreated'] =userdate($registereduser->timecreated,get_string('strftimedatemonthabbr', 'core_langconfig'));
            $count++;
        }
        $coursesContext = array(
            "hascourses" => $userslist,
            "nocourses" => $nocourse,
            "totalusers" => $totalusers,
            "length" => count($userslist)
        );
        return $coursesContext;
    }

    public function trainingofficals_view(){
        global $DB, $USER, $CFG;
       
        $renderer = $PAGE->get_renderer('local_userapproval'); 
        $renderer->get_training_officals_view();  
        
    }
    public  function  get_listof_trainer_expert_request_data($stable, $filterdata){

        global $DB, $PAGE, $OUTPUT, $CFG;
        $context = context_system::instance();

        $lang= current_language();
        
        $selectsql = 'SELECT customtable.* FROM (';
        $countsql  = 'SELECT COUNT(customtable.identifier) FROM (';
        if(has_capability('local/organization:manage_trainingofficial', $context)){

            $selectsql .= "SELECT concat(trainer.id,'_trainer') AS id, trainer.id as expertid,trainer.userid as expertuserid,trainer.status as expertstatus ,trainer.qualifications as expertqualifications,
                                 trainer.fieldoftraining as expertfot,trainer.fieldoftrainingothers as expertfoto, 'trainer' as requesttype 
                            FROM {local_trainer_request} as trainer"; 
            $countsql  .= "SELECT trainer.id as identifier, trainer.userid AS expertuserid, 'trainer' as requesttype 
                            FROM  {local_trainer_request} as trainer ";
        }
        if(is_siteadmin()){
            $selectsql .=' UNION ';
            $countsql .=' UNION ';
        }

        if( has_capability('local/organization:manage_examofficial', $context)){
            $selectsql .= "SELECT concat(expert.id,'_expert') AS id, expert.id AS expertid,expert.userid AS expertuserid,expert.status as expertstatus,
                                 expert.qualifications as expertqualifications,expert.fieldofexperience as expertfot,expert.fieldoftrainingothers as expertfoto, 
                                 'expert' as requesttype 
                            FROM {local_expert_request} as expert 
                            "; 
            $countsql  .= "SELECT expert.id AS identifier, expert.userid AS expertuserid, 'expert' as requesttype  
                            FROM {local_expert_request} as expert ";
        }
   
            $joinsql = " )AS customtable JOIN {user} AS u ON u.id = customtable.expertuserid 
                            JOIN {local_users} AS lu ON lu.userid = u.id ";
     
        if(isset($filterdata->search_query) && trim($filterdata->search_query) != ''){
            $searchsql = " AND (u.firstname LIKE :search OR  
                               u.lastname LIKE :search2 OR 
                               lu.firstnamearabic LIKE :firstnamearabicsearch OR
                               lu.lastnamearabic LIKE :lastnamearabicsearch OR
                                lu.middlenameen LIKE :middlenameensearch OR
                                lu.middlenamearabic LIKE :middlenamearabicsearch OR
                                lu.thirdnameen LIKE :thirdnameensearch OR 
                                lu.thirdnamearabic LIKE :thirdnamearabicsearch OR 
                                customtable.requesttype LIKE :search3 OR
                                lu.id_number LIKE :id_numbersearch)";
            $searchparams = array(
                'search' => '%'.trim($filterdata->search_query).'%',
                'search2' => '%'.trim($filterdata->search_query).'%',
                'firstnamearabicsearch' => '%'.trim($filterdata->search_query).'%',
                'lastnamearabicsearch' => '%'.trim($filterdata->search_query).'%',
                'middlenameensearch' => '%'.trim($filterdata->search_query).'%',
                'middlenamearabicsearch' => '%'.trim($filterdata->search_query).'%',
                'thirdnameensearch' => '%'.trim($filterdata->search_query).'%',
                'thirdnamearabicsearch' => '%'.trim($filterdata->search_query).'%',
                'search3' => '%'.trim($filterdata->search_query).'%',
                'id_numbersearch' => '%'.trim($filterdata->search_query).'%'
                
            );
        }else{

            $searchsql = "";
            $searchparams = array();
        }

        $expertRoleName = 'expert';
        $trainerRoleName = 'trainer';

        $expertRoleId = $DB->get_field(
            'role',
            'id',
            ['shortname' => $expertRoleName]
        );

     

        // Get trainer role ID
        $trainerRoleId = $DB->get_field(
            'role',
            'id',
            ['shortname' => $trainerRoleName]
        );
       if($filterdata->requesttype==$expertRoleId){
            $requestby='expert';
        }
        if($filterdata->requesttype==$trainerRoleId){
            $requestby='trainer';
        }

         if ($requestby) {
           $fromsql .= " AND  requesttype = '$requestby' ";
          
         }
        


        $selectorderby = " ORDER BY customtable.expertid DESC, customtable.requesttype ASC ";
        $countorderby = " ORDER BY customtable.identifier DESC, customtable.requesttype ASC";

        // echo $countsql.$searchsql;
        // exit;
        $params = array_merge($searchparams);
      // echo $countsql.$fromsql.$searchsql.$countorderby;
      // exit;
        $totalrequests = $DB->count_records_sql($countsql.$fromsql.$joinsql.$searchsql.$countorderby,$params);
        $trainerrequests = $DB->get_records_sql($selectsql.$fromsql.$joinsql.$searchsql.$selectorderby,$params,$stable->start,$stable->length);
        $totalrequestslist = array();
        $count = 0;
        foreach($trainerrequests as $trainerrequest) {
            if ($trainerrequest->requesttype == 'trainer') {
                if(has_capability('local/organization:manage_trainingofficial', $context)){   
                    $totalrequestslist [$count]["id"] =  $trainerrequest->expertid;
                    $totalrequestslist [$count]["expertuserid"] =  $trainerrequest->expertuserid;
                    $requesteduser = $DB->get_record_sql('SELECT * FROM {local_users} WHERE userid=:id',array('id'=> $trainerrequest->expertuserid));
                    $fullname = ($lang == 'ar') ? $requesteduser->firstnamearabic.' '.$requesteduser->middlenamearabic.' '.$requesteduser->thirdnamearabic.' '.$requesteduser->lastnamearabic : $requesteduser->firstname.' '.$requesteduser->middlenameen.' '.$requesteduser->thirdnameen.' '.$requesteduser->lastname;
                    $totalrequestslist [$count]["trainername"] =$fullname;


                    $trainerid = $DB->get_field('role', 'id', ['shortname' => 'trainer']);
                    $enrolstatus = $DB->get_field('program_enrollments', 'id', ['roleid' => $trainerid, 'userid' => $trainerrequest->expertuserid]);
                    if ($enrolstatus) {
                        $totalrequestslist[$count]["enrolled"] = true;
                    } else {
                        $totalrequestslist[$count]["enrolled"] = false;
                    }

                    $totalrequestslist [$count]["id_number"] =$requesteduser->id_number;
                    $totalrequestslist [$count]["requesttype"]= 'Trainer';
                    $totalrequestslist [$count]["role"] = get_string('trainer','local_userapproval');
                    $totalrequestslist [$count]["fieldoftraining"]=$trainerrequest->expertfot ;
                    $totalrequestslist[$count]["luserid"] =  $requesteduser->id;
                    $trainer_document_string =  get_string('trainer_document','local_userapproval');
                    $itemid =$trainerrequest->expertqualifications;
                            $fs = get_file_storage();
                        $files = $fs->get_area_files($context->id, 'local_userapproval', 'qualification', $itemid);
                        foreach($files as $file){
                            $url = moodle_url::make_pluginfile_url($file->get_contextid(), $file->get_component(), $file->get_filearea(),
                            $file->get_itemid(), $file->get_filepath(), $file->get_filename(), false);
                            $downloadurl = $url->get_scheme() . '://' . $url->get_host() . ':' . $url->get_port() . $url->get_path();
                            $shortenurl = '<a href="' . $downloadurl . '" download>' .$trainer_document_string. '</a>';
                            $trainerrequest->expertqualifications=$shortenurl ;
                        }
        
                    $totalrequestslist [$count]["qualification"]=$trainerrequest->expertqualifications ;
                    
                
                    if ($trainerrequest->expertstatus == 1) {
   
                        $alreadyexist=$DB->get_record('role_assignments', array('roleid'=>$trainerRoleId,'userid'=>$trainerrequest->expertuserid));
                        if($alreadyexist){
                            $updatestatus= new stdClass();
                            $updatestatus->id =  $trainerrequest->expertid;
                            $updatestatus->status= 5;
                        
                            $updated=$DB->update_record('local_trainer_request', $updatestatus );
                            if($updated){

                                $totalrequestslist[$count]["status"] = get_string('assignalerady', 'local_userapproval');
                               
                           
                            }
                        } 
                        else{
                            $totalrequestslist[$count]["status"] = get_string('pending', 'local_userapproval');
                        }
                        
                    }
                    else if($trainerrequest->expertstatus == 2) {
                        $totalrequestslist[$count]["status"] = get_string('approved', 'local_userapproval');
                    } else if($trainerrequest->expertstatus == 3){
                        $totalrequestslist[$count]["status"] =  get_string('rejected', 'local_userapproval');
                    } else if($trainerrequest->expertstatus == 4){
                        $totalrequestslist[$count]["status"] = get_string('cancelled', 'local_userapproval');
                    }else {
                        $totalrequestslist[$count]["status"]=  get_string('assignalerady', 'local_userapproval'); ;
                    }
                   
                    //if($updated->status != 5){
                    $totalrequestslist[$count]["actionview"]=($trainerrequest->expertstatus == 1) ? true: false;
                   // }
                   
                    $totalrequestslist[$count]["actionviewapprove"]=($trainerrequest->expertstatus == 2) ? true: false;
                
                    $totalrequestslist[$count]["actionreject"]=($trainerrequest->expertstatus == 3) ? true: false;

                    $totalrequestslist[$count]["actioncancelled"]=($trainerrequest->expertstatus == 4) ? true: false;
                    $totalrequestslist[$count]["disableaction"]=($updatestatus->status == 5) ? true: false;
                   
                }
             
            } 
        
        
            else {
                // if ($trainerrequest->expertfot == 0) {
                //     $trainerrequest->expertfot=get_string('finance','local_userapproval');
                // } elseif ($trainerrequest->expertfot == 1) {
                //     $trainerrequest->expertfot=get_string('corporate','local_userapproval');
                // } elseif ($trainerrequest->expertfot == 2)  {
                //     $trainerrequest->expertfot=get_string('banking','local_userapproval');
                // } elseif ($trainerrequest->expertfot == 3)  {
                //     $trainerrequest->expertfot  =   $trainerrequest->expertfoto;
                // }
                if(has_capability('local/organization:manage_examofficial', $context)){
                $totalrequestslist [$count]["id"] =  $trainerrequest->expertid;
                $totalrequestslist [$count]["expertuserid"] =  $trainerrequest->expertuserid;
                $totalrequestslist [$count]["requesttype"] =  'Expert';  
                $totalrequestslist [$count]["role"] = get_string('expert','local_userapproval');        

                $requested_user = $DB->get_record_sql('SELECT * FROM {local_users} WHERE userid=:id',array('id'=> $trainerrequest->expertuserid));

                 $fullname = ($lang == 'ar') ? $requested_user->firstnamearabic.' '.$requested_user->middlenamearabic.' '.$requested_user->thirdnamearabic.' '.$requested_user->lastnamearabic : $requested_user->firstname.' '.$requested_user->middlenameen.' '.$requested_user->thirdnameen.' '.$requested_user->lastname;
                $totalrequestslist [$count]["trainername"] = $fullname;
                $totalrequestslist [$count]["id_number"] = $requested_user->id_number;
                $totalrequestslist [$count]["fieldoftraining"]=$trainerrequest->expertfot ;
                $totalrequestslist[$count]["luserid"] =  $requested_user->id;
                $itemid =$trainerrequest->expertqualifications;

                $fs = get_file_storage();
                $files = $fs->get_area_files($context->id, 'local_userapproval', 'qualification', $itemid);
                $expert_document_string =  get_string('expert_document','local_userapproval');
                foreach($files as $file){
                    $url = moodle_url::make_pluginfile_url($file->get_contextid(), $file->get_component(), $file->get_filearea(),
                    $file->get_itemid(), $file->get_filepath(), $file->get_filename(), false);

                    // $download_url = $url->get_port() ? $url->get_scheme() . '://' . $url->get_host() . $url->get_path() . ':' . $url->get_port() : $url->get_scheme() . '://' . $url->get_host() . $url->get_path();

                    $downloadurl = $url->get_scheme() . '://' . $url->get_host() . ':' . $url->get_port() . $url->get_path();

                    $shortenurl = '<a href="' . $downloadurl . '" download>' . $expert_document_string. '</a>';

                    $trainerrequest->expertqualifications=$shortenurl ;
                }
               
                $totalrequestslist [$count]["qualification"]=$trainerrequest->expertqualifications ;
                if ($trainerrequest->expertstatus == 1) {
                    $alreadyexist=$DB->get_record('role_assignments', array('roleid'=>$expertRoleId,'userid'=>$trainerrequest->expertuserid));
                        if($alreadyexist){
                            $updatestatus= new stdClass();
                            $updatestatus->id =  $trainerrequest->expertid;
                            $updatestatus->status= 5;
                        
                            $updated=$DB->update_record('local_trainer_request', $updatestatus );
                            if($updated){

                                $totalrequestslist[$count]["status"] = get_string('assignalerady', 'local_userapproval');
                            }
                        } 
                        else{
                            $totalrequestslist[$count]["status"] = get_string('pending', 'local_userapproval');
                        }
                  //$totalrequestslist[$count]["status"] = get_string('pending', 'local_userapproval');
                } else if($trainerrequest->expertstatus == 2) {
                    $totalrequestslist[$count]["status"] = get_string('approved', 'local_userapproval');
                } else if($trainerrequest->expertstatus == 3){
                    $totalrequestslist[$count]["status"] =  get_string('rejected', 'local_userapproval');
                } else if($trainerrequest->expertstatus == 4){
                    $totalrequestslist[$count]["status"] =  get_string('cancelled', 'local_userapproval');
                }else{
                    $totalrequestslist[$count]["status"]=  get_string('assignalerady', 'local_userapproval');
                }

                $totalrequestslist[$count]["actionview"]=($trainerrequest->expertstatus == 1) ? true: false;
             
                $totalrequestslist[$count]["actionviewapprove"]=( $trainerrequest->expertstatus == 2) ? true: false;
                $totalrequestslist[$count]["actionreject"]=($trainerrequest->expertstatus == 3) ? true: false;
                $totalrequestslist[$count]["actioncancelled"]=($trainerrequest->expertstatus == 4) ? true: false;
                $totalrequestslist[$count]["disableaction"]=($updatestatus->status == 5) ? true: false;
                }  
            }

            $count++;
        }
             
        $coursesContext = array(
            "hascourses" =>  $totalrequestslist,
            "nocourses" => $nocourse,
            "totalusers" => $totalrequests,
            "length" => count( $totalrequestslist)
        );  
        return $coursesContext;
    }
    public function approve_request($requestid,$requesttype){
        global $DB, $USER, $CFG;
        $context = context_system::instance();
        $traineeroleid = $DB->get_field('role', 'id', array('shortname' => 'trainee'));


        if ($requesttype == 'Trainer' || $requesttype == 'مدرب' ){
          $approverequest=$DB->execute('UPDATE {local_trainer_request} SET status = 2 WHERE id = '.$requestid);
          if($approverequest) {
            $roleid = (int) $DB->get_field('role', 'id', array('shortname' => 'trainer'));
            $userid =  (int) $DB->get_field('local_trainer_request', 'userid', array('id' => $requestid));
            role_assign($roleid, $userid, $context->id);
          }
        } else {


          $approverequest=$DB->execute('UPDATE {local_expert_request} SET status = 2 WHERE id = '.$requestid);
           if($approverequest) {
             $roleid = (int) $DB->get_field('role', 'id', array('shortname' => 'expert'));
             $userid = (int) $DB->get_field('local_expert_request', 'userid', array('id' => $requestid));
            role_assign($roleid, $userid, $context->id);
           }

        }
        
    }
    public function reject_request($rejectid,$requesttype){
        global $DB, $USER, $CFG;
        $context = context_system::instance();
        if ($requesttype == 'Trainer' || $requesttype == 'مدرب' ){
          $rejectrequest=$DB->execute('UPDATE {local_trainer_request} SET status = 3 WHERE id = '.$rejectid);
        } else {
          $rejectrequest=$DB->execute('UPDATE {local_expert_request} SET status = 3 WHERE id = '.$rejectid);
        }
    }
     public function cancel_request($cancelid,$requesttype,$trainer_or_expert){
         global $DB, $USER, $CFG;
         $systemcontext = context_system::instance();
                $is_trainer_or_expert = $DB->record_exists_sql('SELECT ra.id FROM {role_assignments} AS ra 
                     JOIN {role} AS r ON ra.roleid = r.id
                     WHERE  ra.userid = '.$trainer_or_expert.' 
                     AND ra.contextid = '.$systemcontext->id.' 
                     AND (r.shortname = "trainer" OR r.shortname = "expert") ');

                if($is_trainer_or_expert) {
                    $existingroleid = (int) $DB->get_field_sql('SELECT ra.roleid  FROM {role_assignments} AS ra 
                        JOIN {role} AS r ON ra.roleid = r.id
                        WHERE  ra.userid = '.$trainer_or_expert.' 
                        AND  ra.contextid = '.$systemcontext->id.'
                        AND (r.shortname = "trainer" OR r.shortname = "expert") ');
                    if($existingroleid) {
                        role_unassign($existingroleid, $trainer_or_expert, $systemcontext->id);

                        if($requesttype == "Trainer"){
                      $cancelreq=$DB->delete_records('role_assignments',  array ('userid' =>$trainer_or_expert, 'roleid' =>$existingroleid));
                      $userrecord= new \stdClass();
                      $userrecord->userid=$trainer_or_expert;
                      $userrecord->id=$cancelid;
                      $userrecord->status=4;

                      $result =$DB->update_record('local_trainer_request', $userrecord);
                    }else{
                      $cancelreq=$DB->delete_records('role_assignments',  array ('userid' =>$trainer_or_expert, 'roleid' =>$existingroleid));
                       $userrecord= new \stdClass();
                      $userrecord->userid=$trainer_or_expert;
                      $userrecord->id=$cancelid;
                      $userrecord->status=4;

                      $result =$DB->update_record('local_expert_request', $userrecord);
                    }
                    }
                }else{
                    if($requesttype == "Trainer"){
                      $userrecord= new \stdClass();
                      $userrecord->userid=$trainer_or_expert;
                      $userrecord->id=$cancelid;
                      $userrecord->status=4;

                      $result =$DB->update_record('local_trainer_request', $userrecord);
                    }else{
                       $userrecord= new \stdClass();
                      $userrecord->userid=$trainer_or_expert;
                      $userrecord->id=$cancelid;
                      $userrecord->status=4;

                      $result =$DB->update_record('local_expert_request', $userrecord);
                    }
                }
    }

    public static function add_user_wallet_data($formdata) {
        global $DB, $USER;

        try {

            $formdata->userid=$USER->id;
            $formdata->walletlog=$formdata->wallet;
            $productdata = array();
            $productdata['products'] = base64_encode(serialize(['category' => 'wallet', 'userid' => $USER->id, 'total' => $formdata->wallet]));
            $returnurl = (new orders)->begin_transaciton($productdata); 
            return $returnurl;

        } catch (dml_exception $e) {
            print_error($e);

        }
        return true;
    }
    public static function add_user_wallet_logs($formdata) {
        global $DB, $USER;

        try {

            $formdata->userid=$USER->id;
            $formdata->addedwallet =$formdata->walletlog;

            $formdata->timecreated=time();
            $formdata->usercreated=$USER->id;

            $id=$DB->insert_record('local_wallet_logs', $formdata);
            

        } catch (dml_exception $e) {
            print_error($e);

        }
        return true;
    }   
    public static function get_user_wallet($userid) {
        global $DB, $USER;
        $context = context_system::instance();
        $params          = array();
        $userwallet      = array();
        $params['userid'] = $userid;
        if (!is_siteadmin() && has_capability('local/organization:manage_trainee', $context)) {
            $sql = "SELECT tw.*
                     FROM {trainee_wallet} AS tw
                    WHERE tw.userid =:userid";
            try {  
                $userwallet = $DB->get_record_sql($sql, $params);
                return $userwallet;
            } catch (dml_exception $e) {
                print_error($e);
            }
           
        } elseif(!is_siteadmin() && has_capability('local/organization:manage_organizationofficial', $context)) {
            $sql = "SELECT uwt.* FROM {local_orgofficial_wallet} AS uwt
                     WHERE uwt.userid =:userid";
            try {  
                $userwallet = $DB->get_record_sql($sql, $params);
                return $userwallet;
            } catch (dml_exception $e) {
                print_error($e);
            }
            
        }
              
    }

    public static function remove_org_request($orgid,$userid) {
      global $DB, $USER;
        try {
           $rejectrequest=$DB->delete_records('organization_requests',  array ('orgid' =>$orgid, 'userid' =>$userid));
           if($rejectrequest) {
              $result=$DB->execute("UPDATE {local_users} SET organization = 0 WHERE userid = $userid AND organization = $orgid"); 
            }
          return $rejectrequest;
        } catch (dml_exception $ex) {
            $userwallet = array();
        }
    } 

    public function trainer_expert_request_data() {
        global $DB, $PAGE, $OUTPUT;
        $systemcontext = context_system::instance();
        $renderer = $PAGE->get_renderer('local_userapproval');
        $filterparams  = $renderer->get_trainingofficalsrequests_view(true);
        $filterparams['submitid'] = 'form#filteringform';
        if(is_siteadmin()){
            $onlyadmin=true;
        } else {
            $onlyadmin=false;
        }
        $filterparams['widthclass'] = 'col-md-6';
        $filterparams['placeholder'] = get_string('search_users','local_userapproval');
        $globalinput=$renderer->global_filter($filterparams);
        $userdetails = $renderer->get_trainingofficalsrequests_view();
        $filterparams['userdetails'] = $userdetails;
        $filterparams['onlyadmin'] = $onlyadmin;
        $filterparams['globalinput'] = $globalinput;
        $fform = local_trainerexpert_req_filters_form($filterparams);
        $filterparams['filterform'] = $fform->render();
       echo $renderer->listof_trainer_expert_requests($filterparams);
    }

    public function my_org_requests() {
        global $DB,$USER,$PAGE;

        $systemcontext = context_system::instance();
        if(!is_siteadmin() && has_capability('local/organization:manage_organizationofficial', $systemcontext)) {

            $organization = $DB->get_field('local_users', 'organization', ['userid' => $USER->id]);

            $sql = 'SELECT orgr.id 
                            FROM {user} u 
                            JOIN {organization_requests} orgr ON orgr.userid = u.id 
                           WHERE u.id > 2 AND u.deleted = 0  AND orgr.userstatus = 1 AND orgr.orgid=:organization';

            $orgrequests = $DB->record_exists_sql($sql, ['organization' => $organization]);
            if($orgrequests){
                return true;
            }
            return false;

        }

    }  

    public function recommendedentities($stable, $filterdata)
    {
        global $DB, $PAGE, $OUTPUT, $USER;

        $type = $filterdata['type'];

        // $sql = "SELECT jbl.level
        //           FROM {local_jobrole_level} jbl 
        //           JOIN {local_users} lu ON lu.jobrole = jbl.id
        //          WHERE lu.userid = ". $USER->id;
        // $level = $DB->get_field_sql($sql);
        // $levelid = str_replace("level","",$level);
        // $level = $levelid+1;
        $Jobrole = $DB->get_field('local_users','Jobrole',['userid' => $USER->id]); 
       
        if(!is_null($Jobrole) && $Jobrole != 0) {
          
        $level = $DB->get_field_sql('SELECT  
                    RIGHT(jbl.level,1)
                    FROM {local_jobrole_level} jbl 
                    JOIN {local_users} lu ON lu.jobrole = jbl.id
                    WHERE lu.userid =:userid',['userid'=>$USER->id]);
                $level = $level+1;
        // print_r($level);exit;
        $searchparams = [];
        switch ($type) {
            case 'exam':
           
                $selectsql = "SELECT le.id,le.courseid as courseid, le.exam as name, le.examnamearabic as arabicname, le.code, le.examprice as price, le.sellingprice "; 
                $countsql  = "SELECT COUNT(le.id) ";
                $formsql = "  FROM {local_exams} le ";
                 if(!empty($filterdata['favourites']) ){
                $formsql .=" LEFT JOIN {favourite} fav ON le.id = fav.itemid ";
                }
                $formsql .= " WHERE le.status = 1 AND REPLACE(le.clevels, 'level', '') = $level AND le.id NOT IN (SELECT examid FROM {exam_enrollments} WHERE userid = $USER->id) ";
                
                if(isset($filterdata['search_query']) && trim($filterdata['search_query']) != ''){
                    $formsql .= " AND le.exam LIKE :search";
                    $searchparams = array('search' => '%'.trim($filterdata['search_query']).'%');
                }
                if(!empty($filterdata['favourites']) ){
                 $formsql.=" AND fav.component = 'local_exams' AND  fav.userid='$USER->id' ";
                 }
             $component='local_exams';
             $componenttype ="exams"; 
             $hidefavexamsview = false;
                break;
            case 'program':

                $selectsql = "SELECT le.id,le.courseid as courseid, le.name as name, le.namearabic as arabicname, le.code, le.availablefrom as startdate, le.availableto as enddate, le.price, le.sellingprice, le.availablefrom as entitystart, le.availableto as entityend, le.image "; 
                $countsql  = "SELECT COUNT(le.id) ";
                $live =  (new \local_trainingprogram\local\trainingprogram)->from_unixtime_for_live_entities('le.availableto');
               $formsql = "  FROM {local_trainingprogram} le ";
                if(!empty($filterdata['favourites']) ){
                $formsql .=" LEFT JOIN {favourite} fav ON le.id = fav.itemid ";
                }
                $formsql .= "  WHERE le.published=1 AND $live
                                AND 
                                RIGHT(le.clevels,1) = $level
                                AND le.id 
                       NOT IN (SELECT pc.programid FROM {program_enrollments} pc 
                                 WHERE pc.userid =$USER->id) "; 
                                 
                if(isset($filterdata['search_query']) && trim($filterdata['search_query']) != ''){
                    $formsql .= " AND (le.name LIKE :search OR le.namearabic LIKE :namearabicsearch ) ";
                    $searchparams = array(
                                          'search' => '%'.trim($filterdata['search_query']).'%',
                                          'namearabicsearch' => '%'.trim($filterdata['search_query']).'%'
                                        );
                }
                 if(!empty($filterdata['favourites']) ){
                 $formsql.=" AND fav.component = 'local_trainingprogram' AND  fav.userid='$USER->id' ";
                 }
                 $formsql .= " ORDER BY le.availablefrom DESC ";
                 $component='local_trainingprogram';
                 $componenttype ="trainingprogram";
                 $hidefavexamsview = true;
                break;
            default:
                // code...
                break;
        }
        $params = array_merge($searchparams);
        $totalentities = $DB->count_records_sql($countsql.$formsql, $params);

        if ($filterdata['view'] == 'mobile') {
            $records = $DB->get_records_sql($selectsql.$formsql, $params);
        } else {
            $records = $DB->get_records_sql($selectsql.$formsql, $params, $stable->start,$stable->length);
        }
    }
        $recommendedentities = [];
        $count = 0;
        foreach($records  AS $record) {

            $recommendedentities[$count]["entityid"] = $record->id;
            $recommendedentities[$count]["courseid"] = $record->courseid;
             $recommendedentities[$count]['userid'] = $USER->id; 
        $recommendedentities[$count]['checkcomponent'] = $component;
        $recommendedentities[$count]['checkcomponenttype'] = $componenttype;
            $recommendedentities[$count]['checkfavornot'] =(new exams)->checkfavourites($record->id,$USER->id,$component); 
            $recommendedentities[$count]['hidefavexamsview'] = $hidefavexamsview;

            //$recommendedentities[$count]["entityid"] = $record->id;
            if(current_language() == 'ar') {
            
                $recommendedentities[$count]["entityname"] = $record->arabicname;

            } else {

                $recommendedentities[$count]["entityname"] = $record->name;

            }

            if($record->price == 1) {
                $recommendedentities[$count]["price"] = $record->sellingprice;
            } else {
                $recommendedentities[$count]["price"] = get_string('free', 'local_userapproval');
            }

            $recommendedentities[$count]["entitycode"] = $record->code;
            $recommendedentities[$count]["entitystart"] = $record->entitystart;
            $recommendedentities[$count]["entityend"] = $record->entityend;
            $recommendedentities[$count]["startdate"] = !empty($record->startdate) ? userdate($record->startdate,get_string('strftimedatemonthabbr', 'core_langconfig')) : '--';
            $recommendedentities[$count]["enddate"] = !empty($record->enddate) ? userdate($record->enddate,get_string('strftimedatemonthabbr', 'core_langconfig')) : '--';
            if ($type == 'program') {
                $recommendedentities[$count]['image']=trainingprogramlogo_url($record->image);
            }
            $count++;
        }

        if(count($recommendedentities) == 0) {
            $nocourse = true;
        }
        $coursesContext = array(
            "hascourses" => $recommendedentities,
            "nocourses" => $nocourse,
            "totalentities" => $totalentities,
            "length" =>  count($recommendedentities)
        );

        return $coursesContext;
    }
    public function get_user_org_info($userid=false) {
        global $DB, $USER;
        if(!$userid){
            $userid = $USER->id;
        } 
        $sql ='SELECT org.shortname as orgcode, org.licensekey 
                 FROM {local_organization} as org 
                 JOIN {local_users} as u ON u.organization = org.id
                WHERE u.userid=:userid';
        $orgdata = $DB->get_record_sql($sql, ['userid' => $userid]);
        
        return $orgdata;
    }
    public function get_list_of_nationalities($returnall = false, $lang = null) {
        global $CFG;

        if ($lang === null) {
            $lang = current_language();
        }
        $stringman  = get_string_manager();
        $nationalities = $stringman->load_component_strings('local_userapproval', $lang);
        
        $nationalitylist = array();
        foreach($nationalities as $code => $nationality){
            if(is_int($code)){
                $nationalitylist[$code] = $nationality;
            }
        }

        return $nationalitylist;
    }


    public static function get_user_organization($organization, $id, $type = null) {
        global $DB, $USER;
        $lang = current_language();
        if($lang == 'ar') {
            $sql = " SELECT lo.id, lo.fullnameinarabic as fullname " ;
        } else {
            $sql = " SELECT lo.id, lo.fullname " ;
        }
        if(!empty($organization)){
            list($orgsql, $orgparams) = $DB->get_in_or_equal($organization);
            $sql .= " FROM {local_organization} lo WHERE lo.id $orgsql ";
            $org = $DB->get_records_sql_menu($sql, $orgparams);
        } else if(!empty($id)) {
            if($type == 'offering') {
                $sql .= " FROM {local_organization} lo JOIN {tp_offerings} tpo ON tpo.organization = lo.id
                WHERE tpo.id = :id AND tpo.organization > 0 ";
            } else {
                $sql .= " FROM {local_organization} lo JOIN {local_users} lc ON lc.organization = lo.id
                WHERE lc.id = :id ";
            }
           
            $org = $DB->get_records_sql_menu($sql, ['id' => $id]);
        }
       
        return $org;
    }
    public static function get_orgofficial($official,$offeringid = 0) {
        global $DB, $USER;
        $lang = current_language();
    
            $displaying_name = (new trainingprogram)->user_fullname_case();
       
        if(!empty($official)){
            list($orgsql, $orgparams) = $DB->get_in_or_equal($official);
            $sql = " SELECT u.id,$displaying_name FROM {user} AS u 
            JOIN {local_users} lc ON lc.userid = u.id  WHERE u.id $orgsql ";
            $officials = $DB->get_records_sql_menu($sql, $orgparams);
        }
        if($offeringid > 0) {
            $offeringofficials = $DB->get_field('tp_offerings','officials',['id' => $offeringid]);
            if($offeringofficials){
                $sql = " SELECT u.id,$displaying_name  FROM {user} AS u 
                JOIN {local_users} lc ON lc.userid = u.id  WHERE u.id IN ($offeringofficials)";
                $officials = $DB->get_records_sql_menu($sql);
            }
        }
        return $officials;
    }

     public static function get_loginasusers($loginasuser,$id = 0) {
        global $DB, $USER;
        $lang = current_language();
    
            $displaying_name = (new trainingprogram)->user_fullname_case();
       
        if(!empty($loginasuser)){
            list($orgsql, $orgparams) = $DB->get_in_or_equal($loginasuser);
            $sql = " SELECT u.id,$displaying_name FROM {user} AS u 
            JOIN {local_users} lc ON lc.userid = u.id  WHERE u.id $orgsql ";
            $loginasusers = $DB->get_records_sql_menu($sql, $orgparams);
        }
        return $loginasusers;
    }

    

    public function itemenrolledlist($stable, $filterdata) {
        global $DB, $CFG;
        $currentlang= current_language();
        if($stable->type == 'exams') {
            $itemurl = "local/exams/examdetails.php";
            $reservationlabel=get_string('profile','local_exams');
            if( $currentlang == 'ar'){
                $tpname='CONCAT(e.examnamearabic," </br> ('.$reservationlabel.' : ",lep.profilecode,")") as itemname';
            }else{
                $tpname='CONCAT(e.exam," </br> ('.$reservationlabel.' : ",lep.profilecode,")") as itemname';
            }
            $countsql = " SELECT COUNT(e.id) ";
            $selectsql = " SELECT e.* , $tpname, e.examprice AS price,lep.profilecode";
            $fromsql = " FROM {local_exams} AS e 
            JOIN {local_exam_profiles} AS lep ON lep.examid = e.id 
            JOIN {local_exam_userhallschedules} AS hs ON hs.examid = e.id AND lep.id = hs.profileid
            WHERE hs.userid = $stable->userid AND hs.enrolstatus = 1 ";
            if (isset($filterdata->search_query) && trim($filterdata->search_query) != ''){
                $fromsql .= " AND (e.exam LIKE :firstnamesearch OR e.examnamearabic LIKE :arabicnamesearch 
                OR lep.profilecode LIKE :profilecodesearch OR e.code LIKE :codesearch) ";
                $searchparams = array('firstnamesearch' => '%'.trim($filterdata->search_query).'%', 
                'arabicnamesearch' => '%'.trim($filterdata->search_query).'%',
                'profilecodesearch' => '%'.trim($filterdata->search_query).'%',
                'codesearch' => '%'.trim($filterdata->search_query).'%',
            );
            } else {
                $searchparams = array();
            }
            $fromsql .= " ORDER BY e.id DESC";
        } else if($stable->type == 'programs') {
            $itemurl = "local/trainingprogram/programcourseoverview.php";
            $offringlabel=get_string('offeringid','local_trainingprogram');
            if( $currentlang == 'ar'){
                $tpname='CONCAT(tp.namearabic," </br> ('.$offringlabel.' ",tpofr.code,")") as itemname';
            }else{
                $tpname='CONCAT(tp.name," </br> ('.$offringlabel.' ",tpofr.code,")") as itemname';
            }
            $countsql = " SELECT COUNT(tp.id) ";
            $selectsql = " SELECT tp.*,$tpname, tpofr.startdate, tpofr.enddate";
            $fromsql = " FROM {local_trainingprogram} AS tp 
            JOIN {tp_offerings} AS tpofr ON tpofr.trainingid = tp.id
            JOIN {program_enrollments} AS pe ON pe.programid = tp.id AND pe.offeringid = tpofr.id
            WHERE pe.userid = $stable->userid AND pe.enrolstatus = 1";
            if (isset($filterdata->search_query) && trim($filterdata->search_query) != ''){
                $fromsql .= " AND (tp.name LIKE :firstnamesearch OR tp.namearabic LIKE :arabicnamesearch
                 OR tpofr.code LIKE :offeringsearch OR tp.code LIKE :codesearch) ";
                $searchparams = array('firstnamesearch' => '%'.trim($filterdata->search_query).'%',
                 'arabicnamesearch' => '%'.trim($filterdata->search_query).'%',
                 'offeringsearch' => '%'.trim($filterdata->search_query).'%',
                 'codesearch' => '%'.trim($filterdata->search_query).'%',
                );
            } else {
                $searchparams = array();
            }
            $fromsql .= " ORDER BY tp.id DESC";
        } else if($stable->type == 'events') {
            $itemurl = "local/events/view.php";
            if( $currentlang == 'ar'){
                $tpname='le.titlearabic as itemname';
            }else{
                $tpname='le.title as itemname';
            }
            $countsql = " SELECT COUNT(le.id) ";
            $selectsql = " SELECT le.*,$tpname";
            $fromsql = " FROM {local_events} AS le JOIN {local_event_attendees} AS ea ON ea.eventid = le.id 
            WHERE ea.userid = $stable->userid ";
            if (isset($filterdata->search_query) && trim($filterdata->search_query) != ''){
                $fromsql .= " AND (le.title LIKE :firstnamesearch OR le.titlearabic LIKE :arabicnamesearch  OR le.code LIKE :codesearch) ";
                $searchparams = array('firstnamesearch' => '%'.trim($filterdata->search_query).'%', 
                'arabicnamesearch' => '%'.trim($filterdata->search_query).'%',
                'codesearch' => '%'.trim($filterdata->search_query).'%',
            );
            } else {
                $searchparams = array();
            }
            $fromsql .= " ORDER BY le.id DESC";
        }
        $params = array_merge($searchparams);
        $totalrecords = $DB->count_records_sql($countsql.$fromsql, $params);
        $itemlist = $DB->get_records_sql($selectsql.$fromsql, $params, $stable->start,$stable->length);
        $list = array();
        $count = 0;
        foreach($itemlist as $item) {
            $list[$count]['id'] = $item->id;
            $list[$count]['itemname'] = html_writer::link("{$CFG->wwwroot}/".$itemurl."?id=$item->id", $item->itemname) ;//$item->itemname;
            $list[$count]['code'] = $item->code;
            $list[$count]['startdate'] = ($item->startdate)?userdate($item->startdate, get_string('strftimedatemonthabbr', 'langconfig')):'--';
            $list[$count]['enddate'] = ($item->enddate)?userdate($item->enddate, get_string('strftimedatemonthabbr', 'langconfig')):'--';
            $list[$count]['cost'] = ($item->price == 1)?$item->sellingprice:get_string('free','local_events');
            $count++;
        }

      //  var_dump($totalrecords); exit;

        $competencyContext = array(
            "records" => $list,
            "nodata" => $nodata,
            "totalcount" => $totalrecords,
            "length" => $totalrecords
        );        
        return $competencyContext;

    }

    public function assigntrainer($stable, $filterdata)
    {
        global $DB, $PAGE, $OUTPUT;
      
        $expertRoleName = 'expert';
        $trainerRoleName = 'trainer';

        $expertRoleId = $DB->get_field(
            'role',
            'id',
            ['shortname' => $expertRoleName]
        );

     

        // Get trainer role ID
        $trainerRoleId = $DB->get_field(
            'role',
            'id',
            ['shortname' => $trainerRoleName]
        );

    

        $systemcontext = context_system::instance();
        $searchparams = [];

        
        $selectsql = " SELECT u.id,ra.roleid,u.firstname,u.lastname,r.name,u.email,ra.modifierid,ra.timemodified,ra.userid";
        $countsql = "SELECT COUNT(ra.id) ";
        $fromsql=" FROM {local_users} as u
             INNER JOIN {role_assignments} AS ra ON u.userid = ra.userid 
             INNER JOIN {role} as r ON r.id=ra.roleid
             WHERE u.deleted=0 AND ra.userid NOT IN(SELECT userid FROM mdl_local_expert_request WHERE status IN (1,2) UNION SELECT userid FROM mdl_local_trainer_request WHERE status IN (1,2)) AND ra.contextid=$systemcontext->id  AND ra.roleid IN($expertRoleId,$trainerRoleId) ";
    

        $params=[];

        if(isset($filterdata->search_query) && trim($filterdata->search_query) != ''){
            $fromsql .= " AND (u.firstname LIKE :search OR r.name LIKE :roless OR u.email LIKE :email)";
                   
            $searchparams = array(
                'search' => '%'.trim($filterdata->search_query).'%',
                'roless' => '%'.trim($filterdata->search_query).'%', 
                'email'  => '%'.trim($filterdata->search_query).'%', 
            );
        }else{

            $searchsql = "";
            $searchparams = array();
        }

        if (isset($filterdata->role) && trim($filterdata->role) != '') {
            $fromsql .= " AND ra.roleid = $filterdata->role";
          
        }
    
        if (isset($filterdata->email) && trim($filterdata->email) != '') {
            $fromsql .= " AND u.id =  $filterdata->email";
           
        }
 
        if($filterdata->{'availablefrom[enabled]'} == 1 ){

            $start_year = $filterdata->{'availablefrom[year]'};
            $start_month = $filterdata->{'availablefrom[month]'};
            $start_day = $filterdata->{'availablefrom[day]'};
            $filter_starttime_con = mktime(0,0,0, $start_month, $start_day, $start_year);
            $sql.= " AND ra..timemodified >= '$filter_starttime_con' ";

        }
        if($filterdata->{'availableto[enabled]'} == 1 ){
            $start_year = $filterdata->{'availableto[year]'};
            $start_month = $filterdata->{'availableto[month]'};
            $start_day = $filterdata->{'availableto[day]'};
            $filter_endtime_con=mktime(23,59,59, $start_month, $start_day, $start_year);
            $sql.=" AND ra.timemodified <= '$filter_endtime_con' ";
        }
     
       
        $params= array_merge($searchparams);
        $totalcount= $DB->count_records_sql($countsql.$fromsql, $params);
        
        $getdata = $DB->get_recordset_sql( $selectsql.' '.$fromsql, $params,$stable->start, $stable->length);
        
        $count=0;
        $contextArray = []; 
    
        foreach ($getdata as $activity) {
            if($activity->roleid==$expertRoleId){
                $role='expertp';
            }
            elseif($activity->roleid==$trainerRoleId){
                $role='trainerp';
            }  

            $contextArray[$count]['trainername'] = $activity->firstname .' '. $activity->lastname;
            $contextArray[$count]['email'] = $activity->email;
            $contextArray[$count]['requesttype'] =  get_string($role,'local_userapproval');
            $contextArray[$count]['assigndate'] =  userdate($activity->timemodified, get_string('strftimedaydate', 'langconfig'));   
            $count++;
        }

      
        $allrecords = array(
            "hascourses" =>  $contextArray,
            "nodata" => $nodata,
            "totalcount" => $totalcount,
            "length" => count($contextArray)
        );   

        return $allrecords;
    
    }


    public function assign_trainerexpert_byadmin() {
       
        global  $PAGE;
        $renderer = $PAGE->get_renderer('local_userapproval');
       
        $filterparams  = $renderer->admin_assin_role_users(true);
        $filterparams['submitid'] = 'form#filteringform';
        $filterparams['widthclass'] = 'col-md-12';
        $filterparams['placeholder'] = get_string('search','local_exams');
        $globalinput=$renderer->global_filter($filterparams);
        $details = $renderer->admin_assin_role_users(null);
        $fform = local_userapproval_trainerexpert_filters_form($filterparams);
        $filterparams['assign_by_admin'] = $details;
        $filterparams['filterform'] = $fform->render();
        $filterparams['globalinput'] = $globalinput;
        echo $renderer->listofadminassign($filterparams);
       
       
    }
//Ramanjaneyulu Added 
     public function fast_userenrolservices() {
        global  $PAGE;
        $renderer = $PAGE->get_renderer('local_userapproval');
        $filterparams  = $renderer->get_catalog_fast_userenrol(true);
        $filterparams['submitid'] = 'form#filteringform';
        $filterparams['widthclass'] = 'col-md-12';
        $filterparams['placeholder'] = get_string('search','local_userapproval');
        $globalinput=$renderer->global_filter($filterparams);
        $details = $renderer->get_catalog_fast_userenrol(null);
        $fform = exams_fastuserenroll_filters_form($filterparams);
        $filterparams['fastuserenrol'] = $details;
        $filterparams['filterform'] = $fform->render();
        $filterparams['globalinput'] = $globalinput;
        echo $renderer->listoffuserenrol($filterparams);
    } 
       public function getfastuserapprovalenrol($stable,$filterdata) {
        global $CFG, $PAGE, $OUTPUT, $DB;
        $selectsql = "SELECT ee.* FROM {local_fast_user} as ee";
        $formsql =" WHERE 1=1 ";
       if  (isset($filterdata->search_query) && trim($filterdata->search_query) != ''){
            $lang=current_language();
            if($lang == "ar"){
                    $formsql .= " AND (ee.phonenumber LIKE :phonenumber 
                           OR ee.email LIKE :email 
                           OR ee.firstnamearabic LIKE :usernamesearch
                           OR ee.middlenamearabic LIKE :middlenameen
                           OR ee.thirdnamearabic LIKE :thirdnameen
                           OR ee.lastnamearabic LIKE :lastname
                           OR ee.id_number LIKE :idnumber) ";
                      $searchparams = array(
                      'phonenumber' => '%'.trim($filterdata->search_query).'%',
                      'email' => '%'.trim($filterdata->search_query).'%',
                      'usernamesearch' => '%'.trim($filterdata->search_query).'%',
                      'middlenameen' => '%'.trim($filterdata->search_query).'%',
                      'thirdnameen' => '%'.trim($filterdata->search_query).'%',
                      'lastname' => '%'.trim($filterdata->search_query).'%',
                      'idnumber' => '%'.trim($filterdata->search_query).'%');
            }else{
                    $formsql .= " AND (ee.phonenumber LIKE :phonenumber 
                           OR ee.email LIKE :email 
                           OR ee.firstname LIKE :usernamesearch
                           OR ee.middlenameen LIKE :middlenameen
                           OR ee.thirdnameen LIKE :thirdnameen
                           OR ee.lastname LIKE :lastname
                           OR ee.id_number LIKE :idnumber) ";
                      $searchparams = array(
                      'phonenumber' => '%'.trim($filterdata->search_query).'%',
                      'email' => '%'.trim($filterdata->search_query).'%',
                      'usernamesearch' => '%'.trim($filterdata->search_query).'%',
                      'middlenameen' => '%'.trim($filterdata->search_query).'%',
                      'thirdnameen' => '%'.trim($filterdata->search_query).'%',
                      'lastname' => '%'.trim($filterdata->search_query).'%',
                      'idnumber' => '%'.trim($filterdata->search_query).'%');
        }
        } else {
            $searchparams = array();
        }

          if($filterdata->{'userenroltimefrom[enabled]'} == 1 ){
            $start_year = $filterdata->{'userenroltimefrom[year]'};
            $start_month = $filterdata->{'userenroltimefrom[month]'};
            $start_day = $filterdata->{'userenroltimefrom[day]'};
            $start_hour = $filterdata->{'userenroltimefrom[hour]'};
            $start_minute = $filterdata->{'userenroltimefrom[minute]'};
            $filter_starttime_con = mktime($start_hour,$start_minute,0,$start_month,$start_day,$start_year);
            //$formsql.= " AND UNIX_TIMESTAMP(ee.timemodified)   >= $filter_starttime_con ";
            $formsql.= " AND ee.timemodified   >= $filter_starttime_con ";

        }
        if($filterdata->{'userenroltimeto[enabled]'} == 1 ){
            $start_year = $filterdata->{'userenroltimeto[year]'};
            $start_month = $filterdata->{'userenroltimeto[month]'};
            $start_day = $filterdata->{'userenroltimeto[day]'};
            $start_hour = $filterdata->{'userenroltimeto[hour]'};
            $start_minute = $filterdata->{'userenroltimeto[minute]'};
            $filter_endtime_con=mktime( $start_hour, $start_minute,59, $start_month, $start_day, $start_year);
           // $formsql.= " AND UNIX_TIMESTAMP(ee.timemodified)  <= $filter_endtime_con ";
           $formsql.= " AND ee.timemodified  <= $filter_endtime_con ";
            
        }

        if (!empty($filterdata->username)){ 
            $usernames = explode(',',$filterdata->username);
             if(!empty($usernames)){
                $usernamesquery = array();
                foreach ($usernames as $username) {
                    $usernamesquery[] = " CONCAT(',',ee.username,',') LIKE CONCAT('%,','$username',',%') "; 
                }
                $usernamesqueryparams =implode('OR',$usernamesquery);
                $formsql .= ' AND ('.$usernamesqueryparams.') ';
            }
        } 
        if (!empty($filterdata->email)){ 
            $emails = explode(',',$filterdata->email);
             if(!empty($emails)){
                $emailsquery = array();
                foreach ($emails as $email) {
                    $emailsquery[] = " CONCAT(',',ee.email,',') LIKE CONCAT('%,','$email',',%') "; 
                }
                $emailsqueryparams =implode('OR',$emailsquery);
                $formsql .= ' AND ('.$emailsqueryparams.') ';
            }
        } 
        if (!empty($filterdata->phonenumber)){ 
            $phonenumbers = explode(',',$filterdata->phonenumber);
             if(!empty($phonenumbers)){
                $phonenumbersquery = array();
                foreach ($phonenumbers as $phonenumber) {
                    $phonenumbersquery[] = " CONCAT(',',ee.phonenumber,',') LIKE CONCAT('%,','$phonenumber',',%') "; 
                }
                $phonenumberqueryparams =implode('OR',$phonenumbersquery);
                $formsql .= ' AND ('.$phonenumberqueryparams.') ';
            }
        } 
        if (!empty($filterdata->idnumber)){ 
            $idnumbers = explode(',',$filterdata->idnumber);
             if(!empty($idnumbers)){
                $idnumbersquery = array();
                foreach ($idnumbers as $idnumber) {
                    $idnumbersquery[] = " CONCAT(',',ee.id_number,',') LIKE CONCAT('%,','$idnumber',',%') "; 
                }
                $idnumbersqueryparams =implode('OR',$idnumbersquery);
                $formsql .= ' AND ('.$idnumbersqueryparams.') ';
            }
        }
        $formsql .= " ORDER BY id DESC ";
        //$formsql .= " ORDER BY UNIX_TIMESTAMP(ee.purchasedatetime) DESC ";
        $params = array_merge($searchparams);
        $totalrecords = $DB->get_records_sql($selectsql. $formsql, $params);
        $fastuserenrol = $DB->get_records_sql($selectsql. $formsql, $params,$stable->start,$stable->length);
        $list = array();
        $count = 0;
        foreach ($fastuserenrol as $userenrol) {
            $list[$count]["id"] = $userenrol->id;
            $lang= current_language();
            if($lang == "ar"){
               $list[$count]["username"] = $userenrol->firstnamearabic.' '.$userenrol->middlenamearabic.' '.$userenrol->thirdnamearabic.' '.$userenrol->lastnamearabic;
            }else{
                $list[$count]["username"] = $userenrol->firstname." ".$userenrol->middlenameen." ".$userenrol->thirdnameen." ".$userenrol->lastname;  
            }
            $list[$count]["email"] = $userenrol->email;
            $list[$count]["phonenumber"] = $userenrol->phonenumber;
            $list[$count]["idnumber"] = $userenrol->id_number;
            $list[$count]["timecreated"] = ($userenrol->timecreated) ? date('Y-m-d H:i:s',$userenrol->timecreated) : '--';
            $list[$count]["timemodified"] =($userenrol->timemodified) ? date('Y-m-d H:i:s',$userenrol->timemodified) : '--';
            if($userenrol->errormessage == "" || $userenrol->errormessage == "null"){
             $list[$count]["actionbtn"] =false;
             $list[$count]["errormessage"] = "";
            }else{
             $list[$count]["actionbtn"] =true;
             $list[$count]["errormessage"] = $userenrol->errormessage;  
            }
            $count++;
        }
        $userenrolContext = array(
            "hasuserenrol" => $list,
            "totalrecords" => count($totalrecords),
            "length" => count($list),
        );
        return $userenrolContext;
    }
  ///

     public function set_userenrol($id)
    {
        global $DB;
        $data = $DB->get_record('local_fast_user', ['id' => $id]);
        $sql = "SELECT *,right(phonenumber ,9) as phone1 FROM {local_fast_user} WHERE id =$id";
        $data = $DB->get_record_sql($sql);
        // $data->profilecode = $data->examprofilecode;
        // $data->examcode = $data->code;
        return $data;
    }

    public static function sendemail_to_orgorind($data) {
        global $USER,$DB,$CFG;
        //$record = $DB->get_record('user',array('id'=>$data->userid));
        $userdata = $DB->get_record('user',array('id'=>$data->userid));
        $subject = $data->subject;
        $textmessage = strip_tags($data->message['text']);
        $htmlmessage = $data->message['text'];
        $fromuser = $USER;
        try{
            $sendmail = email_to_user($userdata, $fromuser, $subject, $textmessage,$htmlmessage, '','',true, $fromuser->email, fullname($fromuser));
            return $sendmail;
        } catch(moodle_exception $e){
          print_r($e);
        }

    }
    public function get_user_switchrols($roles) {
        global $PAGE, $USER, $DB, $SESSION, $CFG;
        $systemcontext = context_system::instance();
        $renderer = $PAGE->get_renderer('local_userapproval');
        $context = context_system::instance();

        if ( !empty($SESSION->orole) && ($USER->access['rsw'][$context->path] == $SESSION->orole) && ((!is_siteadmin() && has_capability('local/organization:manage_trainee',$systemcontext)) || (!is_siteadmin() && has_capability('local/organization:manage_organizationofficial',$systemcontext)) || (!is_siteadmin() && has_capability('local/organization:manage_expert',$systemcontext)) ) ) {
            $USER->access['rsw'] = [];
        }

        $data = [];
        if ((!empty($USER->access['rsw'][$context->path]))) {
            $USER->access['rsw'][$context->path] = 0;
            $row = [];
            $row['id'] = 1; // Default
            $row['rolename'] = get_string('switchrolereturn');
            $row['sesskey'] = sesskey();
            $row['switchrole'] = true;
            $data[] = $row;
        } else {
            if (!is_siteadmin() && has_capability('local/organization:manage_organizationofficial',$systemcontext)) {
                $roleid = $DB->get_field('role', 'id', ['shortname' => 'organizationofficial']);
                $SESSION->orole = $roleid;
            } else if (!is_siteadmin() && has_capability('local/organization:manage_trainee',$systemcontext)) {
                $roleid = $DB->get_field('role', 'id', ['shortname' => 'trainee']);
                $SESSION->orole = $roleid;
            } else if (!is_siteadmin() && has_capability('local/organization:manage_trainer',$systemcontext)) {
                $roleid = $DB->get_field('role', 'id', ['shortname' => 'trainer']);
                $SESSION->orole = $roleid;
            } else if (!is_siteadmin() && has_capability('local/organization:manage_expert',$systemcontext)) {
                $roleid = $DB->get_field('role', 'id', ['shortname' => 'expert']);
                $SESSION->orole = $roleid;
            }

            $croles = self::get_currentroles();
            $roles = array_intersect($croles, $roles);
            foreach($roles as $key => $role) {
                $row = [];
                $row['id'] = $key;
                $row['rolename'] = $role;
                $row['sesskey'] = sesskey();
                $row['originalrole'] = true;
                $data[] = $row;
            }
        }
        $rolescontent = $renderer->get_switchrolescontent($data);

        return $rolescontent;
    }
    
    public function get_currentroles() {
        global $DB, $USER;
        $sql = "SELECT r.id, r.name 
                  FROM {role} r
                  JOIN {role_assignments} rs ON rs.roleid = r.id 
                 WHERE rs.userid = ".$USER->id;
        $uroles = $DB->get_records_sql($sql);
        foreach($uroles as $key => $crole) {
            $croles[$key] = format_string($crole->name);
        }

        return $croles;
    }


//Ramanjaneyulu Added 


public static function hiding_countries($nationalities){

    unset($nationalities["AW"],
          $nationalities["IL"],
          $nationalities["TF"],
          $nationalities["VG"],
          $nationalities["VI"],
          $nationalities["EH"],
          $nationalities["AQ"],
          $nationalities["CD"],
          $nationalities["AG"],
          $nationalities["SZ"],
          $nationalities["PW"],
          $nationalities["BT"],
          $nationalities["PF"],
          $nationalities["TW"],
          $nationalities["TT"],
          $nationalities["GI"],
          $nationalities["AX"],
          $nationalities["UM"],
          $nationalities["TC"],
          $nationalities["BV"],
          $nationalities["IM"],
          $nationalities["JE"],
          $nationalities["PM"],
          $nationalities["BL"],
          $nationalities["VC"],
          $nationalities["MF"],
          $nationalities["CW"],
          $nationalities["CC"],
          $nationalities["MD"],
          $nationalities["NL"]
       );
       return $nationalities;
   }
    public  function generate_randon_string(){
        $digits    = array_flip(range('0','9'));
        $lowercase = array_flip(range('a','z'));
        $uppercase = array_flip(range('A','Z')); 
        $special   = array_flip(str_split('!@#$%&*'));
        $combined  = array_merge($digits, $lowercase, $uppercase, $special);
        $password  = str_shuffle(array_rand($digits) .
                                array_rand($lowercase) .
                                array_rand($uppercase) . 
                                array_rand($special) . 
                                implode(array_rand($combined, rand(6, 8))));
       return $password;
  
   }
   public function list_of_individualsrequest($stable, $filterdata) {
        global $DB,$PAGE, $USER;
        $context = context_system::instance();
            $selectsql = "SELECT customtable.* FROM (SELECT concat(expert.id,'_expert') AS id, expert.id AS expertid,expert.userid AS expertuserid,expert.status as expertstatus,
                                  expert.qualifications as expertqualifications,expert.fieldofexperience as expertfot,expert.fieldoftrainingothers as expertfoto, expert.requestdate as requestdate, 
                                  'expert' as requesttype 
                             FROM {local_expert_request} as expert 
                     UNION SELECT concat(trainer.id,'_trainer') AS id, trainer.id as expertid,trainer.userid as expertuserid,trainer.status as expertstatus ,trainer.qualifications as expertqualifications,
                                  trainer.fieldoftraining as expertfot,trainer.fieldoftrainingothers as expertfoto, trainer.requestdate as requestdate,'trainer' as requesttype  
                             FROM {local_trainer_request} as trainer) AS customtable 
                            JOIN {user} AS u ON u.id = customtable.expertuserid 
                             JOIN {local_users} AS lu ON lu.userid = u.id 
                             "; 
            $countsql  = "SELECT COUNT(customtable.identifier) FROM (SELECT expert.id AS identifier, expert.userid AS expertuserid, 'expert' as requesttype, expert.requestdate as requestdate 
                             FROM {local_expert_request} as expert 
                     UNION SELECT trainer.id as identifier, trainer.userid AS expertuserid,trainer.requestdate as requestdate, 'trainer' as requesttype
                             FROM  {local_trainer_request} as trainer) AS customtable 
                             JOIN {user} AS u ON u.id = customtable.expertuserid 
                             JOIN {local_users} AS lu ON lu.userid = u.id  ";
            
          
           
            $expertRoleName = 'expert';
            $trainerRoleName = 'trainer';
    
            $expertRoleId = $DB->get_field(
                'role',
                'id',
                ['shortname' => $expertRoleName]
            );

            $trainerRoleId = $DB->get_field(
                'role',
                'id',
                ['shortname' => $trainerRoleName]
            );
           if($filterdata->requesttype==$expertRoleId){
                $requestby='expert';
            }
            if($filterdata->requesttype==$trainerRoleId){
                $requestby='trainer';
            }
    
             if ($requestby) {
               $fromsql .= " AND  requesttype = '$requestby' ";
              
             }
            
            $selectorderby = " WHERE lu.userid=$USER->id ORDER BY customtable.expertid DESC, customtable.requesttype ASC ";
            $countorderby = "  WHERE lu.userid=$USER->id ORDER BY customtable.identifier DESC, customtable.requesttype ASC";
    
            $params = array_merge($searchparams);
       
            $totalrequests = $DB->count_records_sql($countsql.$fromsql.$countorderby,$params);
            $trainerrequests = $DB->get_records_sql($selectsql.$fromsql.$selectorderby,$params,$stable->start,$stable->length);
            $trainee_requested_data= array();
            $count = 0;
            
            foreach($trainerrequests as $trainerrequest) {
                
                if ($trainerrequest->requesttype == 'trainer') {
     
                    $trainee_requested_data [$count]["id"] =  $trainerrequest->expertid;
                    $trainee_requested_data [$count]["role"]  = get_string('trainer','local_userapproval');
                    $trainee_requested_data [$count]["fieldoftraining"]=$trainerrequest->expertfot ;
                    
                    $requested_user = $DB->get_record_sql('SELECT * FROM {local_users} WHERE userid=:id',array('id'=> $trainerrequest->expertuserid));

                    
                    $trainee_requested_data[$count]["luserid"] =  $trainerrequest->expertuserid;
          
                    $trainer_document_string =  get_string('trainer_document','local_userapproval');
                    $itemid =$trainerrequest->expertqualifications;
                        $fs = get_file_storage();
                        $files = $fs->get_area_files($context->id, 'local_userapproval', 'qualification', $itemid);
                        foreach($files as $file){
                            $url = moodle_url::make_pluginfile_url($file->get_contextid(), $file->get_component(), $file->get_filearea(),
                            $file->get_itemid(), $file->get_filepath(), $file->get_filename(), false);        
                            $downloadurl = $url->get_scheme() . '://' . $url->get_host() . ':' . $url->get_port() . $url->get_path();
                            $shortenurl = '<a href="' . $downloadurl . '" download>' . $trainer_document_string. '</a>';
                            $trainerrequest->expertqualifications1=$shortenurl ;
                        }
            
                    $trainee_requested_data [$count]["document"]=$trainerrequest->expertqualifications1 ;
                    if(empty($trainerrequest->requestdate)){
                        $trainee_requested_data [$count]["requestdate"]="-";
                    }
                    else{
                        $trainee_requested_data [$count]["requestdate"]=userdate($trainerrequest->requestdate, get_string('strftimedaydate', 'langconfig'));
                    }
                    if ($trainerrequest->expertstatus == 1) {
                        $trainee_requested_data[$count]["status"] = get_string('pending', 'local_userapproval');
                    }
                    else if($trainerrequest->expertstatus == 2) {
                        $trainee_requested_data[$count]["status"] = get_string('approved', 'local_userapproval');
                    } else if($trainerrequest->expertstatus == 3){
                        $trainee_requested_data[$count]["status"] =  get_string('rejected', 'local_userapproval');
                    } else if($trainerrequest->expertstatus == 4){
                        $trainee_requested_data[$count]["status"] = get_string('cancelled', 'local_userapproval');
                    } else if($trainerrequest->expertstatus == 5){
                        $trainee_requested_data[$count]["status"] =  get_string('assignalerady', 'local_userapproval');
                    }
                    else{
                        $trainee_requested_data[$count]["status"] =''; 
                    }
                } 
                else {
                  
                    $trainee_requested_data [$count]["id"] =  $trainerrequest->expertid;
                    $trainee_requested_data [$count]["role"] = get_string('expert','local_userapproval');
            
                    $requested_user = $DB->get_record_sql('SELECT * FROM {local_users} WHERE userid=:id',array('id'=> $trainerrequest->expertuserid));
                   
                    $trainee_requested_data [$count]["fieldoftraining"]=$trainerrequest->expertfot ;
                    $trainee_requested_data[$count]["luserid"] =  $trainerrequest->expertuserid;
                    $itemid =$trainerrequest->expertqualifications;
    
                    $fs = get_file_storage();
                    $files = $fs->get_area_files($context->id, 'local_userapproval', 'qualification', $itemid);
                    $expert_document_string =  get_string('expert_document','local_userapproval');
                    foreach($files as $file){
                        $url = moodle_url::make_pluginfile_url($file->get_contextid(), $file->get_component(), $file->get_filearea(),
                        $file->get_itemid(), $file->get_filepath(), $file->get_filename(), false);    
                        $downloadurl = $url->get_scheme() . '://' . $url->get_host() . ':' . $url->get_port() . $url->get_path();
    
                        $shortenurl = '<a href="' . $downloadurl . '" download>' . $expert_document_string. '</a>';
                        $trainerrequest->expertqualifications1=$shortenurl;
                    }
                   
                    $trainee_requested_data [$count]["document"]=$trainerrequest->expertqualifications1;
                    if(empty($trainerrequest->requestdate)){
                        $trainee_requested_data [$count]["requestdate"]="-";
                    }
                    else{
                        $trainee_requested_data [$count]["requestdate"]=userdate($trainerrequest->requestdate, get_string('strftimedaydate', 'langconfig'));
                    }
                    if ($trainerrequest->expertstatus == 1) {
                        $trainee_requested_data[$count]["status"] = get_string('pending', 'local_userapproval');
                    } else if($trainerrequest->expertstatus == 2) {
                        $trainee_requested_data[$count]["status"] = get_string('approved', 'local_userapproval');
                    } else if($trainerrequest->expertstatus == 3){
                        $trainee_requested_data[$count]["status"] =  get_string('rejected', 'local_userapproval');
                    } else if($trainerrequest->expertstatus == 4){
                        $trainee_requested_data[$count]["status"] =  get_string('cancelled', 'local_userapproval');
                    } else if($trainerrequest->expertstatus == 5){
                        $trainee_requested_data[$count]["status"] =  get_string('assignalerady', 'local_userapproval');
                    }
                    else{
                        $trainee_requested_data[$count]["status"] =''; 
                    }
                  
                }
    
                $count++;
            }

                 
            $coursesContext = array(
                "hascourses" =>  $trainee_requested_data,
                "nocourses" => $nocourse,
                "totalusers" => $totalrequests,
    
                "length" => count( $trainee_requested_data),
            ); 
            
            return $coursesContext;
    }

    public function individual_requests($userid, $id) {
        global $PAGE;
        $renderer = $PAGE->get_renderer('local_userapproval');
        // $userid = optional_param('userid', 0, PARAM_INT);
        $filterparams  = $renderer->individual_requestdata_page(true);
        $filterparams['submitid'] = 'form#filteringform';
        $filterparams['widthclass'] = 'col-md-12';
        $filterparams['placeholder'] = get_string('search','local_userapproval');
        $globalinput=$renderer->global_filter($filterparams);
        $details = $renderer->individual_requestdata_page();
        $filterparams['requesteddata'] = $details;
        $filterparams['userid'] = $userid;
        $filterparams['id'] = $id;
        $filterparams['globalinput'] = $globalinput;
        
        $renderer->listofrequesteddata($filterparams);
        
    } 
}
