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
use dml_transaction_exception;
use MongoDB\Exception\Exception;
defined('MOODLE_INTERNAL') || die;

use context_user;
use core_user;
use csv_import_reader;
use core_text;
use lang_string;
use moodle_exception;
use stdClass;
use html_table;
use html_table_cell;
use html_writer;
use html_table_row;
use moodle_url;
use local_userapproval\action\manageuser as manageuser;


/**
 * TODO describe file programenrollusers_upload
 *
 * @package    local_trainingprogram
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class programenrollusers_upload  {

    protected $columns;

    protected $columnsmapping = array();

    private $users = array();

    protected $allowedcolumns = array(
        'identitynumber','identitytype', 
        'firstnameen','middlenameen','thirdnameen','lastnameen',
        'firstnamear','middlenamear','thirdnamear','lastnamear',
        'birthdate','email','nationality','gender','countrycode',
        'phonenumber'
    );
    
    public function upload_enrollment_file($file,$sdata) {
        global $CFG, $DB, $PAGE,$USER,$OUTPUT;
        require_once($CFG->libdir . '/csvlib.class.php');
        $systemcontext = \context_system::instance();
        
        $content = $file->get_content();
       
        $filename = $file->get_filename();
        /**
         * Extracting account,lob and role information from CSV
         * and removed it from CSV for uploading
         */
        $content = core_text::convert($content, 'utf-8');
        $content = core_text::trim_utf8_bom($content);
        $content = preg_replace('!\r\n?!', "\n", $content);
        $content = trim($content);

        $tempfile = tempnam(make_temp_directory('/csvimport'), 'tmp');

        if (!$fp = fopen($tempfile, 'w+b')) {
            $this->_error = get_string('cannotsavedata', 'error');
            @unlink($tempfile);
            return false;
        }
        fwrite($fp, $content);
        fseek($fp, 0);
        $uploadid = csv_import_reader::get_new_iid('enrollmentupload');
        $cir = new csv_import_reader($uploadid, 'enrollmentupload');
        $readcount = $cir->load_csv_content($content, 'utf-8', 'comma');

        unset($content);

        if ($readcount <= 1 ) {
            throw new moodle_exception('csvloaderror', 'error',$PAGE->url,  get_string('validfile','local_exams'));
        }
        if(!is_siteadmin() && !has_capability('local/organization:manage_trainingofficial',$systemcontext) && !has_capability('local/organization:manage_organizationofficial',$systemcontext)){
            throw new moodle_exception('youdonthavepermissiontouploaddata', 'local_exams');
        }
        $this->columns = $cir->get_columns();
        $column_validation = $this->validate_columns();
        if(is_array($column_validation) && count($column_validation) > 0){
            $string = $column_validation[0];
            $return =  '<div class="local_exams_sync_error">'.get_string('validsheet','local_exams',$string).'</div>'; 
            $return .= html_writer::start_tag('div', array('class'=> 'w-100 pull-left')).$OUTPUT->continue_button(new moodle_url('/local/trainingprogram/bulkenrollments.php',['programid'=> $sdata->programid, 'roleid'=> $sdata->roleid, 'offeringid' => $sdata->offeringid])).html_writer::end_tag('div');
           echo $return;
           return false;
        }
        foreach ($this->columns as $i => $columnname) {
            $columnnamelower = preg_replace('/ /', '', core_text::strtolower($columnname));
            if (in_array($columnnamelower, $this->allowedcolumns)) {
                $this->columnsmapping[$i] = $columnnamelower;
            } else {
                $this->columnsmapping[] = $columnname;
            }
        }
        $cir->init();
        $rownum = 0;
        $progress       = 0;
        $data = array();
        $linenum = 1;   
        $errorcount= 0;
        $registereduserid = '';
        $freshenrollmentusers = 0;
        while ($row = $cir->next()) {
			$linenum++;
            $hash = array();
            $masterdata = new stdClass();
            foreach ($row as $i => $value) {
                if (!isset($this->columnsmapping[$i])) {
                    continue;
                }
                $column=$this->columnsmapping[$i];
                $masterdata->$column = $value;

            }
            $masterdata->excel_line_number=$linenum;  
            //print_r($masterdata);
            $this->data[]=$masterdata;  
            $this->errors = array();
            $this->warnings = array();
            $this->mfields = array();
            $this->wmfields = array();
            $this->excel_line_number = $linenum;
            $stringhelpers = new stdClass();
            $stringhelpers->linenumber = $this->excel_line_number; 

            $this->required_fields_validations($masterdata);
            if (count($this->errors) > 0) { 
               $errorcount++;
            }else{
                $masterdata->username =  $masterdata->identitynumber;
                $masterdata->country_code = $masterdata->countrycode;
                $masterdata->bulkenrollstatus =  0;
                $roleid =(int) $DB->get_field('role','id',['shortname'=>'trainee']);
                $userinfo = $DB->get_record("user",array('idnumber'=>trim($masterdata->identitynumber),'deleted' => 0));
                if($userinfo->id > 0) {
                    $masterdata->username =  $userinfo->username;
                    $traineeorganization =(int) $DB->get_field('local_users','organization',['userid'=>$userinfo->id]);
                    if(!is_siteadmin() && has_capability('local/organization:manage_organizationofficial', $systemcontext)) {
                        if($traineeorganization > 0) {
                            $masterdata->organization =($sdata->organization != $traineeorganization) ?  $traineeorganization : $sdata->organization;
                        } else {
                            $masterdata->organization = 0;
                        }
                    } else {
                        $masterdata->organization = $sdata->organization;
                    }
                    $localuserdata = $DB->get_record("local_users",array('username'=>trim($masterdata->username),'deleted' => 0));
                    $masterdata->oldid = $localuserdata->oldid;
                    $masterdata->gender = !empty($masterdata->gender)?((strtolower($masterdata->gender) == 'male')? 1: 2): $localuserdata->gender; 
                    $masterdata->idtype = !empty($masterdata->identitytype) ? $masterdata->identitytype : $localuserdata->id_type;
                    $idtype =array('1'=>'id','2'=>'passport','3'=>'saudiid','4'=>'residentialid');
                    $masterdata_idtype = array_keys($idtype, strtolower($masterdata->idtype));
                    $masterdata->id_type = $masterdata_idtype[0]?$masterdata_idtype[0]:0;
                    $masterdata_idnumber = !empty($masterdata->identitynumber) ? $masterdata->identitynumber : $localuserdata->id_number;
                    $masterdata->id_number =   $masterdata_idnumber;
                    $masterdata->timecreated =time();
                    $masterdata->email =!empty($masterdata->email) ? trim(str_replace('Â','',$masterdata->email)) : $localuserdata->email;
                    $masterdata->usercreated =$USER->id;
                    $masterdata->dateofbirth =!empty($masterdata->birthdate) ? strtotime($masterdata->birthdate) :$localuserdata->dateofbirth ;
                    $masterdata->firstname = !empty($masterdata->firstnameen) ? $masterdata->firstnameen :$localuserdata->firstname ;
                    $masterdata->lastname = !empty($masterdata->lastnameen) ? $masterdata->lastnameen : $localuserdata->lastname ;
                    $masterdata->firstnamearabic =!empty($masterdata->firstnamear) ? $masterdata->firstnamear :$localuserdata->firstnamearabic ;
                    $masterdata->lastnamearabic = !empty($masterdata->lastnamear) ? $masterdata->lastnamear :$localuserdata->lastnamearabic ;
                    $masterdata->middlenameen = !empty($masterdata->middlenameen) ? $masterdata->middlenameen :$localuserdata->middlenameen ;
                    $masterdata->middlenamearabic = !empty($masterdata->middlenamear) ? $masterdata->middlenamear :$localuserdata->middlenamearabic ;
                    $masterdata->thirdnameen = !empty($masterdata->thirdnameen) ? $masterdata->thirdnameen :$localuserdata->thirdnameen ;
                    $masterdata->thirdnamearabic =!empty($masterdata->thirdnamear) ? $masterdata->thirdnamear :$localuserdata->thirdnamearabic ;
                    $masterdata->lang =$localuserdata->lang;
                    $masterdata->city =$localuserdata->city;
                    $masterdata->nationality =empty($masterdata->nationality)? $localuserdata->nationality :((is_string($masterdata->nationality) && strlen(trim($masterdata->nationality)) == 2) ? $masterdata->nationality  : 'SA');
                    $masterdata->country =empty($masterdata->nationality)? $localuserdata->country :((is_string($masterdata->nationality) && strlen(trim($masterdata->nationality)) == 2) ? $masterdata->nationality  : 'SA');
                    $masterdata->password =$userinfo->password;
                    $masterdata->phone1 =!empty($masterdata->phonenumber) ? ((strlen(trim($masterdata->phonenumber)) > 20 || empty(trim($masterdata->phonenumber)) || !is_numeric(trim($masterdata->phonenumber)))? 0 : trim($masterdata->phonenumber)) : $localuserdata->phone1;
                    $masterdata->sectors = $localuserdata->sectors;
                    $masterdata->segment = $localuserdata->segment;
                    $masterdata->jobfamily = $localuserdata->jobfamily;
                    $masterdata->jobrole = $localuserdata->jobrole;
                    $masterdata->orgofficial  =  $sdata->orgofficial;
                   
                    $masterdata->id =  $DB->get_field('local_users','id',array('userid' =>$userinfo->id));;
                    $userid=$userinfo->id;
                    $description= get_string('update_descption','local_userapproval',$userinfo);
                    if(!is_siteadmin() && has_capability('local/organization:manage_organizationofficial', $systemcontext)) {
                        $masterdata = ((int)$sdata->organization == $traineeorganization) ? $masterdata : $DB->get_record('local_users',['userid'=>$userinfo->id]);
                        $updatedidnumber =  $masterdata->id_number;
                    } else {

                        $masterdata = $masterdata;
                        $updatedidnumber =  $masterdata->identitynumber;
                    }
                    $masterdata->bulkenrolltype =  'program';
                    if($userid > 0) {

                        (new manageuser)->update_custom_user($masterdata,$userid,$type = 'bulkenroll');
                        (new manageuser)->user_update_user($masterdata,$userid,$roleid); 
                        (new manageuser)->local_users_logs('updated', 'userapproval', $description, $userid);
                    } else {
                        (new manageuser)->update_register_user($masterdata);
                        (new manageuser)->local_users_logs('updated', 'userapproval', $description, $userid);
                    } 
                    $registereduserid.= $userid.',';
                    if(!$DB->record_exists('program_enrollments',['programid'=>$sdata->programid,'offeringid'=>$sdata->offeringid,'userid'=>$userid])) {
                        $freshenrollmentusers++;
                    }
                    if($masterdata->organization > 0) {
                        $successstring = get_string('userupdatedusccess', 'local_userapproval',$updatedidnumber);
                        $return .= $OUTPUT->notification($successstring,'info');
                    }
                }else{
                    $masterdata->oldid = null;
                    $masterdata->gender = (strtolower($masterdata->gender) == 'male')? 1: 2; 
                    $masterdata->idtype = !empty($masterdata->identitytype) ? $masterdata->identitytype : 3;
                    $idtype =array('1'=>'id','2'=>'passport','3'=>'saudiid','4'=>'residentialid');
                    $masterdata_idtype = array_keys($idtype, strtolower($masterdata->idtype));
                    $masterdata->id_type = $masterdata_idtype[0]?$masterdata_idtype[0]:0;
                    $masterdata_idnumber = !empty($masterdata->identitynumber) ? $masterdata->identitynumber : 0;
                    $masterdata->id_number =   $masterdata_idnumber;
                    $masterdata->timecreated =time();
                    $masterdata->email =trim(str_replace('Â','',$masterdata->email));
                    $masterdata->usercreated =$USER->id;
                    $masterdata->dateofbirth =strtotime($masterdata->birthdate);
                    $masterdata->firstname = $masterdata->firstnameen;
                    $masterdata->lastname = $masterdata->lastnameen;
                    $masterdata->firstnamearabic = $masterdata->firstnamear;
                    $masterdata->lastnamearabic = $masterdata->lastnamear;
                    $masterdata->middlenameen = $masterdata->middlenameen;
                    $masterdata->middlenamearabic = $masterdata->middlenamear;
                    $masterdata->thirdnameen = $masterdata->thirdnameen;
                    $masterdata->thirdnamearabic = $masterdata->thirdnamear;
                    $masterdata->lang ='ar';
                    $masterdata->city ='Riyad';
                    $masterdata->nationality =(strlen(trim($masterdata->nationality)) > 2 || empty(trim($masterdata->nationality)))? 'SA' : strtoupper(trim($masterdata->nationality));
                    $masterdata->country =(is_string($masterdata->nationality) && strlen(trim($masterdata->nationality)) == 2) ? $masterdata->nationality  : 'SA';
                    $randomstring = (new manageuser)->generate_randon_string();
                    $newpassword = $randomstring.uniqid();
                    $masterdata->password = hash_internal_user_password($newpassword);
                    $masterdata->confirm_password = $newpassword;
                    $masterdata->givenpassword = $newpassword ;
                    $masterdata->phone1 =(strlen(trim($masterdata->phonenumber)) > 20 || empty(trim($masterdata->phonenumber)) || !is_numeric(trim($masterdata->phonenumber)))? 0 : trim($masterdata->phonenumber);
                    $masterdata->sectors = null;
                    $masterdata->segment = null;
                    $masterdata->jobfamily = null;
                    $masterdata->jobrole = null;
                    $masterdata->username =  $masterdata->identitynumber;
                    $masterdata->organization = $sdata->organization;
                    $masterdata->orgofficial  =  $sdata->orgofficial;
                    $masterdata->bulkenrolltype =  'program';
                    try{
                        $e= new stdClass;
                        $failed_masterdata = array();
                        $custom_ntionalities = get_string_manager()->get_list_of_countries();
                        $localusers_ntionalities =  array_flip((new manageuser)->get_list_of_nationalities());
                        $submitted_nationality = $custom_ntionalities[$masterdata->nationality];
                        $masterdata->nationalitycountryid =  $localusers_ntionalities[$submitted_nationality];
                        $transaction = $DB->start_delegated_transaction();
                        $userid = (new manageuser)->create_user($masterdata,$roleid);
                        $insertrecord = (new manageuser)->create_custom_user($masterdata,$userid,$type = 'bulkenroll');
                        $description= get_string('insert_descption','local_userapproval',$masterdata);
                        $insert_user_logs =(new manageuser)->local_users_logs('registered', 'userapproval', $description, $userid);
                        $eventparams = array('context' => \context_system::instance(),'objectid'=>$insertrecord,'other'=>array('name'=>$masterdata->firstname." ".$masterdata->lastname,'username'=>$masterdata->username));
                        $event = \local_userapproval\event\row_inserted::create($eventparams);// ... code that may add some record snapshots
                        $event->trigger();
                        if($userid){
                            $orgdata = (new manageuser)->get_user_org_info($userid);  
                            $masterdata->orgcode = $orgdata->orgcode;
                            $masterdata->licensekey = $orgdata->licensekey;
                            $masterdata->confirm_password = $newpassword;

                            // Checking Fast Settings(User Registration) is Enabled or not
                            $accessstatus = (new \local_userapproval\local\fast_service)->access_resisteruser_service();
                            if ($accessstatus) {
                                $response = (new \local_userapproval\local\fast_service)->register_user($masterdata);  
                                if(COUNT($response->errors) > 0 ){
                                   $error = json_encode($response->errors, JSON_UNESCAPED_UNICODE);
                                   $this->errors[]  = $error;
                                   $e = new moodle_exception($error);
                                  
                                } 
                                if(COUNT($response->messages) > 0 ){
                                   $messages = json_encode($response->messages, JSON_UNESCAPED_UNICODE);
                                   $this->errors[]  = $messages;
                                   $e = new moodle_exception($messages);
                                   
                                }
                            }
                        }
                        //$e = '';
                        if (empty($e) || !($e instanceof moodle_exception)) {
                            $registereduserid.= $userid.',';
                            if(!$DB->record_exists('program_enrollments',['programid'=>$sdata->programid,'offeringid'=>$sdata->offeringid,'userid'=>$userid])) {

                                $freshenrollmentusers++;
                            }
                            $transaction->allow_commit();
                            $successstring = get_string('usercreatedsuccess', 'local_userapproval',$masterdata->identitynumber);
                            set_user_preference('auth_forcepasswordchange', 1, $userid);
                            $return .= $OUTPUT->notification($successstring,'info');

                        }else{
                            $return .= $OUTPUT->notification(implode(',', $this->errors),'error');
                            $masterdata->status = "-1";
                            $failed_masterdata[] = $masterdata;//(new \local_userapproval\local\fast_service)->add_update_service($masterdata);
                            $transaction->rollback($e);
                            continue;

                        }
                    } catch(moodle_exception | Exception | dml_transaction_exception $e){
                        if(!$transaction->is_disposed()){
                            $masterdata->status = "-1";
                            $failed_masterdata[] = $masterdata;//(new \local_userapproval\local\fast_service)->add_update_service($masterdata);
                            $transaction->rollback($e);
                        }

                    }
                }
            }
		}
        $availableseats =(new trainingprogram)->get_available_seats($sdata->offeringid);
        if($freshenrollmentusers > $availableseats) {
            $return .= $OUTPUT->notification(get_string('cantuploadmorethanavailableseats', 'local_exams',$availableseats),'error');
        }
        $createdusers = base64_encode(ltrim(rtrim(implode(',',array_unique(explode(',',$registereduserid))),','),','));
        $returnurl = (COUNT(explode(',',$registereduserid)) >= 1 && $createdusers && $freshenrollmentusers <= $availableseats) ? new moodle_url('/local/trainingprogram/programenrollmentconfirmation.php', ['programid'=> $sdata->programid, 'offeringid' =>$sdata->offeringid,'roleid'=> $sdata->roleid, 'organization' =>$sdata->organization,'orgofficial' =>$sdata->orgofficial,'cusers'=> $createdusers]) : new moodle_url('/local/trainingprogram/bulkenrollments.php', ['programid'=> $sdata->programid,'roleid'=> $sdata->roleid,'offeringid' =>  $sdata->offeringid]) ;
    
        if((COUNT(explode(',',$registereduserid)) >= 1 && $createdusers)) {
            $traineeids = base64_decode($createdusers);
            $ausers =  $DB->get_fieldset_sql("SELECT userid from {local_users} WHERE FIND_IN_SET(userid,'$traineeids') AND bulkenrollstatus = 0  AND bulkenrolltype ='program' ");
            $ausers= explode(',',(implode(',',$ausers)));
            $productid =  $DB->get_field('tool_products', 'id', ['category' => 1, 'referenceid' => $sdata->offeringid]);
            if($productid && COUNT($ausers) > 0) {
                foreach ($ausers as $traineeid) {
                    (new trainingprogram)->program_enrollment($sdata->offeringid,$traineeid,$sdata->roleid,false,$USER->id,'bulkenrollment',false,$sdata->organization,$productid);
                }
            }
        }
        $return .= html_writer::start_tag('div', array('class'=> 'w-100 pull-left')).$OUTPUT->continue_button($returnurl).html_writer::end_tag('div');
        if(!empty($failed_masterdata)){
            foreach($failed_masterdata as $mdata){
                (new \local_userapproval\local\fast_service)->add_update_service($mdata);
            }
        } 
        echo $return;
    }
    private function validate_columns() {
        global $DB;

        $systemcontext = \context_system::instance();
        foreach ($this->columns as $i => $columnname) {
            if (in_array(strtolower($columnname), $this->allowedcolumns)) {
                $this->columnsmapping[$i] = strtolower($columnname);
            }
        }
        if (!in_array('identitynumber', $this->columnsmapping)) {
            $this->errors[] = get_string('identitynumbermissing', 'local_exams');
            $eventparams = array('context' => \context_system::instance(),'other'=>array('name'=>'identitynumber'));
            $event = \local_userapproval\event\header_missing::create($eventparams);// ... code that may add some record snapshots
            $event->trigger();
            return  $this->errors;
        }
        if (!in_array('identitytype', $this->columnsmapping)) {
             $this->errors[] = get_string('identitytypemissing', 'local_exams');
            $eventparams = array('context' => \context_system::instance(),'other'=>array('name'=>'identitytype'));
            $event = \local_userapproval\event\header_missing::create($eventparams);// ... code that may add some record snapshots
            $event->trigger();
            return  $this->errors;
        }
        if (!in_array('firstnameen', $this->columnsmapping)) {
            $this->errors[] = get_string('firstnameenmissing', 'local_exams');
            $eventparams = array('context' => \context_system::instance(),'other'=>array('name'=>'firstnameen'));
            $event = \local_userapproval\event\header_missing::create($eventparams);// ... code that may add some record snapshots
            $event->trigger();
            return  $this->errors;
        }
        if (!in_array('middlenameen', $this->columnsmapping)) {
            //echo '<div class="local_userapproval_sync_error">'.get_string('lastnamemissing', 'local_userapproval').'</div>'; 
            $this->errors[] = get_string('middlenameenmissing', 'local_exams');
            $eventparams = array('context' => \context_system::instance(),'other'=>array('name'=>'middlenameen'));
            $event = \local_userapproval\event\header_missing::create($eventparams);// ... code that may add some record snapshots
            $event->trigger();
            return  $this->errors;
        }
        if (!in_array('thirdnameen', $this->columnsmapping)) {
            //echo '<div class="local_userapproval_sync_error">'.get_string('lastnamemissing', 'local_userapproval').'</div>'; 
            $this->errors[] = get_string('thirdnameenmissing', 'local_exams');
            $eventparams = array('context' => \context_system::instance(),'other'=>array('name'=>'thirdnameen'));
            $event = \local_userapproval\event\header_missing::create($eventparams);// ... code that may add some record snapshots
            $event->trigger();
            return  $this->errors;
        }
        if (!in_array('lastnameen', $this->columnsmapping)) {
            //echo '<div class="local_userapproval_sync_error">'.get_string('lastnamemissing', 'local_userapproval').'</div>'; 
            $this->errors[] = get_string('lastnameenmissing', 'local_userapproval');
            $eventparams = array('context' => \context_system::instance(),'other'=>array('name'=>'lastnameen'));
            $event = \local_userapproval\event\header_missing::create($eventparams);// ... code that may add some record snapshots
            $event->trigger();
            return  $this->errors;
        }
        if (!in_array('firstnamear', $this->columnsmapping)) {
            $this->errors[] = get_string('firstnamearmissing', 'local_exams');
            $eventparams = array('context' => \context_system::instance(),'other'=>array('name'=>'firstnamear'));
            $event = \local_userapproval\event\header_missing::create($eventparams);// ... code that may add some record snapshots
            $event->trigger();
            return  $this->errors;
        }
        if (!in_array('middlenamear', $this->columnsmapping)) {
            //echo '<div class="local_userapproval_sync_error">'.get_string('lastnamemissing', 'local_userapproval').'</div>'; 
            $this->errors[] = get_string('middlenamearmissing', 'local_exams');
            $eventparams = array('context' => \context_system::instance(),'other'=>array('name'=>'middlenamear'));
            $event = \local_userapproval\event\header_missing::create($eventparams);// ... code that may add some record snapshots
            $event->trigger();
            return  $this->errors;
        }
        if (!in_array('thirdnamear', $this->columnsmapping)) {
            //echo '<div class="local_userapproval_sync_error">'.get_string('lastnamemissing', 'local_userapproval').'</div>'; 
            $this->errors[] = get_string('thirdnamearmissing', 'local_exams');
            $eventparams = array('context' => \context_system::instance(),'other'=>array('name'=>'thirdnamear'));
            $event = \local_userapproval\event\header_missing::create($eventparams);// ... code that may add some record snapshots
            $event->trigger();
            return  $this->errors;
        }
        if (!in_array('lastnamear', $this->columnsmapping)) {
            //echo '<div class="local_userapproval_sync_error">'.get_string('lastnamemissing', 'local_userapproval').'</div>'; 
            $this->errors[] = get_string('lastnamearmissing', 'local_exams');
            $eventparams = array('context' => \context_system::instance(),'other'=>array('name'=>'lastnamear'));
            $event = \local_userapproval\event\header_missing::create($eventparams);// ... code that may add some record snapshots
            $event->trigger();
            return  $this->errors;
        }

        if (!in_array('birthdate', $this->columnsmapping)) {
            //echo '<div class="local_userapproval_sync_error">'.get_string('lastnamemissing', 'local_userapproval').'</div>'; 
            $this->errors[] = get_string('birthdatemissing', 'local_exams');
            $eventparams = array('context' => \context_system::instance(),'other'=>array('name'=>'birthdate'));
            $event = \local_userapproval\event\header_missing::create($eventparams);// ... code that may add some record snapshots
            $event->trigger();
            return  $this->errors;
        }

        if (!in_array('email', $this->columnsmapping)) {
            //echo '<div class="local_userapproval_sync_error">'.get_string('lastnamemissing', 'local_userapproval').'</div>'; 
            $this->errors[] = get_string('emailmissing', 'local_exams');
            $eventparams = array('context' => \context_system::instance(),'other'=>array('name'=>'email'));
            $event = \local_userapproval\event\header_missing::create($eventparams);// ... code that may add some record snapshots
            $event->trigger();
            return  $this->errors;
        }
        if (!in_array('nationality', $this->columnsmapping)) { 
            $this->errors[] = get_string('nationalitymissing', 'local_exams');
            $eventparams = array('context' => \context_system::instance(),'other'=>array('name'=>'nationality'));
            $event = \local_userapproval\event\header_missing::create($eventparams);// ... code that may add some record snapshots
            $event->trigger();
            return  $this->errors;
        }
        if (!in_array('gender', $this->columnsmapping)) { 
            $this->errors[] = get_string('gendermissing', 'local_exams');
            $eventparams = array('context' => \context_system::instance(),'other'=>array('name'=>'gender'));
            $event = \local_userapproval\event\header_missing::create($eventparams);// ... code that may add some record snapshots
            $event->trigger();
            return  $this->errors;
        }
        if (!in_array('countrycode', $this->columnsmapping)) { 
            $this->errors[] = get_string('countrycodemissing', 'local_exams');
            $eventparams = array('context' => \context_system::instance(),'other'=>array('name'=>'countrycode'));
            $event = \local_userapproval\event\header_missing::create($eventparams);// ... code that may add some record snapshots
            $event->trigger();
            return  $this->errors;
        }
        if (!in_array('phonenumber', $this->columnsmapping)) { 
            $this->errors[] = get_string('phonenumbermissing', 'local_exams');
            $eventparams = array('context' => \context_system::instance(),'other'=>array('name'=>'phonenumber'));
            $event = \local_userapproval\event\header_missing::create($eventparams);// ... code that may add some record snapshots
            $event->trigger();
            return  $this->errors;
        }
        return false;
    }

    private function required_fields_validations($excel,$option=0){
        global $DB;
        $systemcontext = \context_system::instance();
        $strings = new stdClass;
        $strings->excel_line_number = $this->excel_line_number;

         //-------- check Username-------------------------------------
         if (array_key_exists('identitynumber', $excel) ) {
            if (empty($excel->identitynumber)) {
                echo '<div class="local_userapproval_sync_error" style = "color:red">'.get_string('user_nameempty','local_exams', $strings).'</div>'; 
                $this->errors[] = get_string('local_exams','local_userapproval', $excel);
                $this->mfields[] = 'IdentityNumber';
                $this->errorcount++;
                $eventparams = array('context' => \context_system::instance(),'objectid'=>$strings->excel_line_number,'other'=>array('name'=>'IdentityNumber'));
                $event = \local_userapproval\event\missing_field::create($eventparams);// ... code that may add some record snapshots
                $event->trigger();
            }
            // if(!empty($excel->identitynumber) && $DB->record_exists_sql('SELECT * FROM {user} WHERE username !='.$excel->identitynumber.' AND idnumber = '.$excel->identitynumber.'')) {
            //     $strings->existsusername = $DB->get_field_sql('SELECT username FROM {user} WHERE username !='.$excel->identitynumber.' AND idnumber = '.$excel->identitynumber.' ORDER BY id DESC limit 1');
            //     $strings->identitynumber = $excel->identitynumber;
            //     echo '<div class="local_userapproval_sync_error" style = "color:red">'.get_string('identitynumberalreadymapped','local_exams', $strings).'</div>'; 
            //     $this->errors[] = get_string('identitynumberalreadymapped','local_exams', $excel);
            //     $this->mfields[] = 'IdentityNumber';
            //     $this->errorcount++;
            //     $eventparams = array('context' => \context_system::instance(),'objectid'=>$strings->excel_line_number,'other'=>array('name'=>'IdentityNumber'));
            //     $event = \local_userapproval\event\invalid_field::create($eventparams);// ... code that may add some record snapshots
            //     $event->trigger();
            // }

        }
        //----------- Check identitytype  ----------
        if (array_key_exists('identitytype', $excel) ) {
            if (!empty($excel->identitytype)) {
               $idtype = array('id','passport','saudiid','residentialid');
               if(!in_array(strtolower($excel->identitytype),$idtype)){
                    $strings->identitytype = $excel->identitytype;
                    echo '<div class="local_userapproval_sync_error" style = "color:red">'.get_string('idtypeisnotvalid','local_exams', $strings).'</div>'; 
                    $this->errors[] = get_string('idtypeisnotvalid','local_exams', $excel);
                    $this->mfields[] = 'Identitytype';
                    $this->errorcount++;
                    $eventparams = array('context' => \context_system::instance(),'objectid'=>$strings->excel_line_number,'other'=>array('name'=>'IdentityNumber'));
                    $event = \local_userapproval\event\missing_field::create($eventparams);// ... code that may add some record snapshots
                    $event->trigger();
               }
            }
        }
        //-----------check FirstName-----------------------------------
        if (array_key_exists('firstnameen', $excel) ) {
            if (!$DB->record_exists('user',['idnumber'=>$excel->identitynumber,'deleted'=>0]) && empty($excel->firstnameen)) {
                echo '<div class="local_userapproval_sync_error" style = "color:red">'.get_string('firstnameenmpty','local_exams', $strings).'</div>'; 
                $this->errors[] =  get_string('firstnameenempty', 'local_exams',$strings);
                $this->mfields[] = 'FirstnameEn';
                $this->errorcount++;
                $eventparams = array('context' => \context_system::instance(),'objectid'=>$strings->excel_line_number,'other'=>array('name'=>'FirstnameEn'));
                $event = \local_userapproval\event\missing_field::create($eventparams);// ... code that may add some record snapshots
                $event->trigger();
            }
        }   
        //-------- check LastName-------------------------------------
        if ( array_key_exists('lastnameen', $excel) ) {
            if (!$DB->record_exists('user',['idnumber'=>$excel->identitynumber,'deleted'=>0]) && empty($excel->lastnameen)) {
                echo '<div class="local_userapproval_sync_error" style = "color:red">'.get_string('lastnameenempty','local_exams', $strings).'</div>'; 
                $this->errors[] = get_string('lastnameenempty','local_exams', $excel);
                $this->mfields[] = 'LastnameEn';
                $this->errorcount++;
                $eventparams = array('context' => \context_system::instance(),'objectid'=>$strings->excel_line_number,'other'=>array('name'=>'LastnameEn'));
                $event = \local_userapproval\event\missing_field::create($eventparams);// ... code that may add some record snapshots
                $event->trigger();
            }
        }
         //-------- check FirstNameArabic-------------------------------------
         if (array_key_exists('firstnamear', $excel) ) {
            if (!$DB->record_exists('user',['idnumber'=>$excel->identitynumber,'deleted'=>0]) && empty($excel->firstnamear)) {
                echo '<div class="local_userapproval_sync_error" style = "color:red">'.get_string('firstnamearabic_nameempty','local_userapproval', $strings).'</div>'; 
                $this->errors[] = get_string('firstnamearabic_nameempty','local_userapproval', $excel);
                $this->mfields[] = 'FirstnameAr';
                $this->errorcount++;
                $eventparams = array('context' => \context_system::instance(),'objectid'=>$strings->excel_line_number,'other'=>array('name'=>'FirstnameAr'));
                $event = \local_userapproval\event\missing_field::create($eventparams);// ... code that may add some record snapshots
                $event->trigger();
             }
        }
        //-------- check LastNameArabic-------------------------------------
        if ( array_key_exists('lastnamear', $excel) ) {
            if (!$DB->record_exists('user',['idnumber'=>$excel->identitynumber,'deleted'=>0]) && empty($excel->lastnamear)) {
                echo '<div class="local_userapproval_sync_error" style = "color:red">'.get_string('lastnamearabic_nameempty','local_userapproval', $strings).'</div>'; 
                $this->errors[] = get_string('lastnamearabic_nameempty','local_userapproval', $excel);
                $this->mfields[] = 'LastnameAr';
                $this->errorcount++;
                $eventparams = array('context' => \context_system::instance(),'objectid'=>$strings->excel_line_number,'other'=>array('name'=>'LastNameArabic'));
                $event = \local_userapproval\event\missing_field::create($eventparams);// ... code that may add some record snapshots
                $event->trigger();
            }
        }
        //-------- check Email-------------------------------------
        if (array_key_exists('email', $excel) ) {
            if (empty(trim($excel->email))) {
                echo '<div class="local_userapproval_sync_error" style = "color:red">'.get_string('emailempty','local_exams', $strings).'</div>'; 
                $this->errors[] = get_string('emailempty','local_exams', $strings);
                $this->mfields[] = 'Email';
                $this->errorcount++;
                $eventparams = array('context' => \context_system::instance(),'objectid'=>$strings->excel_line_number,'other'=>array('name'=>'Email'));
                $event = \local_userapproval\event\missing_field::create($eventparams);// ... code that may add some record snapshots
                $event->trigger();
            }

            if (!empty(trim($excel->email)) && !validate_email(trim(str_replace('Â','',$excel->email)))) {

                $strings->email = $excel->email;
                echo '<div class="local_userapproval_sync_error" style = "color:red">'.get_string('emailnotvalid','local_exams', $strings).'</div>'; 
                $this->errors[] = get_string('emailnotvalid','local_exams', $strings);
                $this->mfields[] = 'Email';
                $this->errorcount++;
                $eventparams = array('context' => \context_system::instance(),'objectid'=>$strings->excel_line_number,'other'=>array('name'=>'Email'));
                $event = \local_userapproval\event\invalid_field::create($eventparams);// ... code that may add some record snapshots
                $event->trigger();
            }

            $email = trim($excel->email);
            $emailrecord = $DB->get_record_sql("SELECT * FROM {user} WHERE email = '$email' AND idnumber !='$excel->identitynumber'");
            if ($emailrecord->id > 0) {
                $strings->existingidnumber = $emailrecord->idnumber;
                $strings->email = trim($excel->email);
                echo '<div class="local_userapproval_sync_error" style = "color:red">'.get_string('emailidalreadyexists','local_exams', $strings).'</div>'; 
                $this->errors[] = get_string('emailidalreadyexists','local_exams', $strings);
                $this->mfields[] = 'Email';
                $this->errorcount++;
                $eventparams = array('context' => \context_system::instance(),'objectid'=>$strings->excel_line_number,'other'=>array('name'=>'Email'));
                $event = \local_userapproval\event\invalid_field::create($eventparams);// ... code that may add some record snapshots
                $event->trigger();
            }

        }

        //-------- check phonenumber-------------------------------------
        if (array_key_exists('phonenumber', $excel) ) {
            $phonenumber = trim($excel->phonenumber);
            if (!empty(trim($excel->phonenumber))){
                $phonenumberrecord = $DB->get_record_sql("SELECT * FROM {user} WHERE phone1 = $phonenumber AND idnumber !='$excel->identitynumber'");
                if ($phonenumberrecord){
                    $strings->existingidnumber = $phonenumberrecord->idnumber;
                    $strings->phonenumber = trim($excel->phonenumber);
                    echo '<div class="local_userapproval_sync_error" style = "color:red">'.get_string('phonenumberalreadyexists','local_exams', $strings).'</div>'; 
                    $this->errors[] = get_string('phonenumberalreadyexists','local_exams', $strings);
                    $this->mfields[] = 'PhoneNumber';
                    $this->errorcount++;
                    $eventparams = array('context' => \context_system::instance(),'objectid'=>$strings->excel_line_number,'other'=>array('name'=>'Email'));
                    $event = \local_userapproval\event\invalid_field::create($eventparams);// ... code that may add some record snapshots
                    $event->trigger();
                }
            }
        }

         //-------- check dateofbirth-------------------------------------
         if (array_key_exists('birthdate', $excel) ) {
            
            if(empty($excel->birthdate)) {
                 echo '<div class="local_userapproval_sync_error" style = "color:red">'.get_string('validatebirthdate','local_exams',$strings).'</div>'; 
                 $this->errors[] = get_string('validatebirthdate','local_exams', $excel);
                 $this->mfields[] = 'Birthdate';
                 $this->errorcount++;
             }
         }
        //-------- check Gender-------------------------------------
        if (array_key_exists('gender', $excel) ) {
            
           if(!$DB->record_exists('user',['idnumber'=>$excel->identitynumber,'deleted'=>0]) && !empty($excel->gender) && strtolower($excel->gender)!= 'male' && strtolower($excel->gender) != 'female') {
                echo '<div class="local_userapproval_sync_error" style = "color:red">'.get_string('validgender','local_exams',$strings).'</div>'; 
                $this->errors[] = get_string('validgender','local_exams', $excel);
                $this->mfields[] = 'Gender';
                $this->errorcount++;
            }
        }

        if (array_key_exists('countrycode', $excel) ) {
            
            if(!$DB->record_exists('user',['idnumber'=>$excel->identitynumber,'deleted'=>0]) && empty($excel->countrycode)) {
                 echo '<div class="local_userapproval_sync_error" style = "color:red">'.get_string('countrycodeempty','local_exams',$strings).'</div>'; 
                 $this->errors[] = get_string('countrycodeempty','local_exams', $excel);
                 $this->mfields[] = 'CountryCode';
                 $this->errorcount++;
            }
            if(!empty($excel->countrycode) && !is_numeric($excel->countrycode)){

                echo '<div class="local_userapproval_sync_error" style = "color:red">'.get_string('countrycodeacceptsnumeric','local_exams',$strings).'</div>'; 
                $this->errors[] = get_string('countrycodeacceptsnumeric','local_exams', $excel);
                $this->mfields[] = 'CountryCode';
                $this->errorcount++;
            }
         }


   
 
    } // end of required_fields_validations function
    /**
     * @method get_enrollment_file
     * @todo Returns the uploaded file if it is present.
     * @param int $draftid
     * @return stored_file|null
     */
    public function get_enrollment_file($draftid) {
        global $USER;
        // We can not use moodleform::get_file_content() method because we need the content before the form is validated.
        if (!$draftid) {
            return null;
        }
        $fs = get_file_storage();
        $context = context_user::instance($USER->id);
        if (!$files = $fs->get_area_files($context->id, 'user', 'draft', $draftid, 'id DESC', false)) {
            return null;
        }
        $file = reset($files);

        return $file;
    }

    /**
     * Displays the preview of the uploaded file
     */
    protected function preview_uploaded() {
        global $OUTPUT;
        $return = '';
        $return .= $OUTPUT->notification(get_string('uploadsectorsheet', 'local_sector'),'info');
        $return .= html_writer::start_tag('div', array('class'=> 'w-100 pull-left')).$OUTPUT->continue_button(new moodle_url('/local/sector/uploadsector.php')).html_writer::end_tag('div');
        return $return;
    }

}

