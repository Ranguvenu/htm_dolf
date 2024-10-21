<?php

/**
 * Sector upload masterdata.
 *
 * @package    local_sector
 * 
 */
namespace local_userapproval\local;
use MongoDB\Exception\Exception;
defined('MOODLE_INTERNAL') || die;

use context_user;
use core\session\exception as SessionException;
use core_user;
use csv_import_reader;
use core_text;
use lang_string;
use moodle_exception;
use stdClass;
use dml_transaction_exception;
use html_table;
use html_table_cell;
use html_writer;
use html_table_row;
use moodle_url;
use local_userapproval\action\manageuser as manageuser;

class users_upload  {

    protected $columns;

    protected $columnsmapping = array();

    private $users = array();

    protected $allowedcolumns = array('oldid','firstname', 'lastname','firstnamearabic','lastnamearabic','middlenameenglish','thirdnameenglish', 'middlenamearabic','thirdnamearabic','dateofbirth','username','password', 'email','gender','language','nationality','countrycode','mobileno','organization','idtype','idnumber','sector','segment','jobfamily','jobrole','city','role');
    
    /**
     * @method process_upload_file
     * @todo To process the uploaded CSV file and return the data
     * @param stored_file $file
     * @param string $encoding
     * @param string $delimiter
     * @param context $defaultcontext
     * @return array
     */
    public function process_upload_file($file, $defaultcontext) {
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

        //print_object($content);
        $uploadid = csv_import_reader::get_new_iid('Usersupload');
        $cir = new csv_import_reader($uploadid, 'Usersupload');


        //Where the magic happens, removed summary content from CSV to start with actual upload.
        // $length = strpos($content, 'date');
        // $content = substr_replace($content, '', 0, $length);
        /**
         * Actual upload starts from here
         */
        $readcount = $cir->load_csv_content($content, 'utf-8', 'comma');

        unset($content);

        if ($readcount <= 1 ) {
            throw new moodle_exception('csvloaderror', 'error',$PAGE->url,  get_string('validfile','local_userapproval'));
        }
        if(!is_siteadmin() && !has_capability('local/organization:manage_communication_officer',$systemcontext) && !has_capability('local/organization:manage_organizationofficial',$systemcontext)){
            throw new moodle_exception('youdonthavepermissiontouploaddata', 'local_userapproval');
        }


        $this->columns = $cir->get_columns();
        $column_validation = $this->validate_columns();
        if(is_array($column_validation) && count($column_validation) > 0){
            $string = $column_validation[0];
            $return =  '<div class="local_userapproval_sync_error">'.get_string('validsheet','local_userapproval',$string).'</div>'; 
            $return .= html_writer::start_tag('div', array('class'=> 'w-100 pull-left')).$OUTPUT->continue_button(new moodle_url('/local/userapproval/uploadusers.php')).html_writer::end_tag('div');
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
       
        // $progressbar    = new \core\progress\display_if_slow(get_string('uploadsector', 'local_sector'),0);
        // $progressbar->start_html();
        // $progressbar->start_progress('', $readcount - 1); 
        $data = array();
        $linenum = 1;   
        $errorcount= 0;
        // if (empty( $cir->next())) {
        //     throw new moodle_exception('csvemptyfile', 'error');
        // }  

        while ($row = $cir->next()) {
			$linenum++;

            // $progressbar->progress($progress);
            // $progress++;
            
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
                $confirm_password = $masterdata->password;
                $masterdata->oldid = $masterdata->oldid;
                $masterdata->gender = (strtolower($masterdata->gender) == 'male')? 1: 2; 
                $masterdata->idtype = !empty($masterdata->idtype) ? $masterdata->idtype : 3;
                $idtype =array('1'=>'id','2'=>'passport','3'=>'saudiid','4'=>'residentialid');
                $masterdata_idtype = array_keys($idtype, strtolower($masterdata->idtype));
                $masterdata->id_type = $masterdata_idtype[0]?$masterdata_idtype[0]:0;
                $masterdata_idnumber = !empty($masterdata->idnumber) ? $masterdata->idnumber : 0;
                $masterdata->id_number =   $masterdata_idnumber;
                $masterdata->timecreated =time();
                $masterdata->email =trim(str_replace('Â','',$masterdata->email));
                $masterdata->usercreated =$USER->id;
                $masterdata->dateofbirth =strtotime($masterdata->dateofbirth);;
                $masterdata->approvedstatus =2;
                $masterdata->jobrole_level =1;
                $masterdata->firstnamearabic = $masterdata->firstnamearabic;
                $masterdata->lastnamearabic = $masterdata->lastnamearabic;
                $masterdata->middlenameen = $masterdata->middlenameenglish;
                $masterdata->thirdnameen = $masterdata->thirdnameenglish;
                $masterdata->middlenamearabic = $masterdata->middlenamearabic;
                $masterdata->thirdnamearabic = $masterdata->thirdnamearabic;

                $masterdata->lang =(strlen(trim($masterdata->language)) > 2 || empty(trim($masterdata->language)))? 'ar' :strtolower(trim($masterdata->language));
                $masterdata->city =(strlen(trim($masterdata->city)) > 120 || empty(trim($masterdata->city))) ? 'Riyad' : $masterdata->city;
                $masterdata->nationality =(strlen(trim($masterdata->nationality)) > 2 || empty(trim($masterdata->nationality)))? 'SA' : strtoupper(trim($masterdata->nationality));
                $masterdata->country = (strlen($masterdata->country) > 2 || empty($masterdata->country)) ? 'SA' :strtoupper($masterdata->country);
                $givenpassword = $masterdata->password;
                $masterdata->password = hash_internal_user_password($masterdata->password);
                $masterdata->confirm_password = $masterdata->password;
                $masterdata->country_code =(empty(trim($masterdata->countrycode)) || !is_numeric(trim($masterdata->countrycode)))? 0 : trim($masterdata->countrycode);
                $masterdata->phone1 =(strlen(trim($masterdata->mobileno)) > 20 || empty(trim($masterdata->mobileno)) || !is_numeric(trim($masterdata->mobileno)))? 0 : trim($masterdata->mobileno);
                if(!is_siteadmin() && has_capability('local/organization:manage_organizationofficial', $systemcontext)) {
                    $officialroganization = $DB->get_field('local_users','organization',['userid'=>$USER->id]);
                    if($officialroganization > 0){
                        $masterdata->organization =  $officialroganization;
                    }else{
                        $masterdata->organization = 0;
                    }
                } else {
                    if(!empty($masterdata->organization)){
                        $organization = $DB->get_field_sql("SELECT id FROM {local_organization} where lower(shortname) ='".$masterdata->organization."'");
                        $masterdata->organization =  $organization;
                    }else{
                        $masterdata->organization = 0;
                    }
               }
                if(!empty($masterdata->sector)){
                    $sector = $DB->get_field_sql("SELECT id FROM {local_sector} where lower(code) ='".$masterdata->sector."'");
                    $masterdata->sectors =  $sector;
                }else{
                    $masterdata->sectors = null;
                }
                if(!empty($masterdata->segment)){
                    $segment = $DB->get_field_sql("SELECT id FROM {local_segment} where lower(code) ='".$masterdata->segment."'");
                    $masterdata->segment =  $segment;
                }else{
                    $masterdata->segment = null;
                }
                if(!empty($masterdata->jobfamily)){
                    $jobfamily = $DB->get_field_sql("SELECT id FROM {local_jobfamily} where lower(code) ='".$masterdata->jobfamily."'");
                    $masterdata->jobfamily =  $jobfamily;
                }else{
                    $masterdata->jobfamily = null;
                }
                if(!empty($masterdata->jobrole)){
                    $jobrole = $DB->get_field_sql("SELECT id FROM {local_jobrole_level} where lower(code) ='".$masterdata->jobrole."'");
                    $masterdata->jobrole =  $jobrole;
                }else{
                    $masterdata->jobrole = null;
                }
                $userinfo = $DB->get_record("user",array('username'=>trim($masterdata->username),'deleted' => 0));
                if(!is_null($masterdata->role) && !empty($masterdata->role)) {
                   $shortname =trim($masterdata->role);
                   $role_id = $DB->get_field('role','id',array('shortname' =>$shortname));
                   if($role_id) {
                    $roleid = (int) $role_id;
                   } else {
                      $roleid = null;
                   }
                } else {

                    $roleid = null;
                }

                if  ($userinfo->id > 0) {
                    $masterdata->id =  $DB->get_field('local_users','id',array('userid' =>$userinfo->id));;
                    $approveduserid=$userinfo->id;
                    $description= get_string('update_descption','local_userapproval',$userinfo);
                    if($approveduserid > 0) {
                        $updatecustomrecord = (new \local_userapproval\action\manageuser)->update_custom_user($masterdata,$approveduserid);
                        $updaterecord = (new \local_userapproval\action\manageuser)->user_update_user($masterdata,$approveduserid,$roleid); 
                        $insert_user_logs =(new \local_userapproval\action\manageuser)->local_users_logs('updated', 'userapproval', $description, $approveduserid);
                    } else {
                        $updaterecord = (new \local_userapproval\action\manageuser)->update_register_user($masterdata);
                        $insert_user_logs =(new \local_userapproval\action\manageuser)->local_users_logs('updated', 'userapproval', $description, $data->id);
                    } 
                    $successstring = get_string('userupdatedusccess', 'local_userapproval',$masterdata->username);
                    $return .= $OUTPUT->notification($successstring,'info');
                }else{
                    try{
                        $e= new stdClass;
                        $failed_masterdata = array();
                        $custom_ntionalities = get_string_manager()->get_list_of_countries();
                        $localusers_ntionalities =  array_flip((new manageuser)->get_list_of_nationalities());
                        $submitted_nationality = $custom_ntionalities[$masterdata->nationality];
                        $masterdata->nationalitycountryid =  $localusers_ntionalities[$submitted_nationality];
                        $transaction = $DB->start_delegated_transaction();
                        $role_id = (is_siteadmin() || has_capability('local/organization:manage_communication_officer',$systemcontext)) ? $roleid : null;
                        $userid = (new \local_userapproval\action\manageuser)->create_user($masterdata,$role_id);
                        $insertrecord = (new \local_userapproval\action\manageuser)->create_custom_user($masterdata,$userid);
                        $description= get_string('insert_descption','local_userapproval',$masterdata);
                        $insert_user_logs =(new \local_userapproval\action\manageuser)->local_users_logs('registered', 'userapproval', $description, $userid);
                        $eventparams = array('context' => \context_system::instance(),'objectid'=>$insertrecord,'other'=>array('name'=>$masterdata->firstname." ".$masterdata->lastname,'username'=>$masterdata->username));
                        $event = \local_userapproval\event\row_inserted::create($eventparams);// ... code that may add some record snapshots
                        $event->trigger();
                        if($userid){
                            $orgdata = (new manageuser)->get_user_org_info($userid);  
                            $masterdata->orgcode = $orgdata->orgcode;
                            $masterdata->licensekey = $orgdata->licensekey;
                            $masterdata->confirm_password = $givenpassword;

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
                        if (empty($e) || !($e instanceof moodle_exception)) {
                       
                            $transaction->allow_commit();
                            $successstring = get_string('usercreatedsuccess', 'local_userapproval',$masterdata->username);
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
                        // return false;
                    }
                }

               
            }
		}
        $return .= html_writer::start_tag('div', array('class'=> 'w-100 pull-left')).$OUTPUT->continue_button(new moodle_url('/local/userapproval/uploadusers.php')).html_writer::end_tag('div');
       
       if(!empty($failed_masterdata)){
        foreach($failed_masterdata as $mdata){
            (new \local_userapproval\local\fast_service)->add_update_service($mdata);
           }
       } 
       echo $return;
        // $progressbar->end_html();
        //return $this->preview_uploaded();
    }
    private function validate_columns() {
        global $DB;

        $systemcontext = \context_system::instance();
        foreach ($this->columns as $i => $columnname) {
            if (in_array(strtolower($columnname), $this->allowedcolumns)) {
                $this->columnsmapping[$i] = strtolower($columnname);
            }
        }
        if (!in_array('oldid', $this->columnsmapping)) {
            $this->errors[] = get_string('oldid_missing', 'local_userapproval');
            $eventparams = array('context' => \context_system::instance(),'other'=>array('name'=>'oldid'));
            $event = \local_userapproval\event\header_missing::create($eventparams);// ... code that may add some record snapshots
            $event->trigger();
            return  $this->errors;
        }

        if (!in_array('email', $this->columnsmapping)) {
             //echo '<div class="local_userapproval_sync_error">'.get_string('emailmissing', 'local_userapproval').'</div>'; 
            $this->errors[] = get_string('emailmissing', 'local_userapproval');
            $eventparams = array('context' => \context_system::instance(),'other'=>array('name'=>'email'));
            $event = \local_userapproval\event\header_missing::create($eventparams);// ... code that may add some record snapshots
            $event->trigger();
            return  $this->errors;
        }
        if (!in_array('firstname', $this->columnsmapping)) {
            // echo '<div class="local_userapproval_sync_error">'.get_string('firstnamemissing', 'local_userapproval').'</div>'; 
             $this->errors[] = get_string('firstnamemissing', 'local_userapproval');
            $eventparams = array('context' => \context_system::instance(),'other'=>array('name'=>'firstname'));
            $event = \local_userapproval\event\header_missing::create($eventparams);// ... code that may add some record snapshots
            $event->trigger();
            return  $this->errors;
        }
        if (!in_array('lastname', $this->columnsmapping)) {
            //echo '<div class="local_userapproval_sync_error">'.get_string('lastnamemissing', 'local_userapproval').'</div>'; 
            $this->errors[] = get_string('lastnamemissing', 'local_userapproval');
            $eventparams = array('context' => \context_system::instance(),'other'=>array('name'=>'lastname'));
            $event = \local_userapproval\event\header_missing::create($eventparams);// ... code that may add some record snapshots
            $event->trigger();
            return  $this->errors;
        }
        if (!in_array('firstnamearabic', $this->columnsmapping)) {
            //echo '<div class="local_userapproval_sync_error">'.get_string('lastnamemissing', 'local_userapproval').'</div>'; 
            $this->errors[] = get_string('firstnamearabicmissing', 'local_userapproval');
            $eventparams = array('context' => \context_system::instance(),'other'=>array('name'=>'firstnamearabic'));
            $event = \local_userapproval\event\header_missing::create($eventparams);// ... code that may add some record snapshots
            $event->trigger();
            return  $this->errors;
        }
        if (!in_array('lastnamearabic', $this->columnsmapping)) {
            //echo '<div class="local_userapproval_sync_error">'.get_string('lastnamemissing', 'local_userapproval').'</div>'; 
            $this->errors[] = get_string('lastnamearabicmissing', 'local_userapproval');
            $eventparams = array('context' => \context_system::instance(),'other'=>array('name'=>'lastnamearabic'));
            $event = \local_userapproval\event\header_missing::create($eventparams);// ... code that may add some record snapshots
            $event->trigger();
            return  $this->errors;
        }
        if (!in_array('middlenameenglish', $this->columnsmapping)) {
            //echo '<div class="local_userapproval_sync_error">'.get_string('lastnamemissing', 'local_userapproval').'</div>'; 
            $this->errors[] = get_string('middlenameenglishmissing', 'local_userapproval');
            $eventparams = array('context' => \context_system::instance(),'other'=>array('name'=>'middlenameenglish'));
            $event = \local_userapproval\event\header_missing::create($eventparams);// ... code that may add some record snapshots
            $event->trigger();
            return  $this->errors;
        }
        if (!in_array('thirdnameenglish', $this->columnsmapping)) {
            //echo '<div class="local_userapproval_sync_error">'.get_string('lastnamemissing', 'local_userapproval').'</div>'; 
            $this->errors[] = get_string('thirdnameenglishmissing', 'local_userapproval');
            $eventparams = array('context' => \context_system::instance(),'other'=>array('name'=>'thirdnameenglish'));
            $event = \local_userapproval\event\header_missing::create($eventparams);// ... code that may add some record snapshots
            $event->trigger();
            return  $this->errors;
        }

        if (!in_array('middlenamearabic', $this->columnsmapping)) {
            //echo '<div class="local_userapproval_sync_error">'.get_string('lastnamemissing', 'local_userapproval').'</div>'; 
            $this->errors[] = get_string('middlenamearabicmissing', 'local_userapproval');
            $eventparams = array('context' => \context_system::instance(),'other'=>array('name'=>'middlenamearabic'));
            $event = \local_userapproval\event\header_missing::create($eventparams);// ... code that may add some record snapshots
            $event->trigger();
            return  $this->errors;
        }

        if (!in_array('thirdnamearabic', $this->columnsmapping)) {
            //echo '<div class="local_userapproval_sync_error">'.get_string('lastnamemissing', 'local_userapproval').'</div>'; 
            $this->errors[] = get_string('thirdnamearabicmissing', 'local_userapproval');
            $eventparams = array('context' => \context_system::instance(),'other'=>array('name'=>'thirdnamearabic'));
            $event = \local_userapproval\event\header_missing::create($eventparams);// ... code that may add some record snapshots
            $event->trigger();
            return  $this->errors;
        }

        if (!in_array('dateofbirth', $this->columnsmapping)) {
            //echo '<div class="local_userapproval_sync_error">'.get_string('lastnamemissing', 'local_userapproval').'</div>'; 
            $this->errors[] = get_string('dateofbirthmissing', 'local_userapproval');
            $eventparams = array('context' => \context_system::instance(),'other'=>array('name'=>'dateofbirth'));
            $event = \local_userapproval\event\header_missing::create($eventparams);// ... code that may add some record snapshots
            $event->trigger();
            return  $this->errors;
        }
        if (!in_array('gender', $this->columnsmapping)) { 
            $this->errors[] = get_string('gendermissing', 'local_userapproval');
            $eventparams = array('context' => \context_system::instance(),'other'=>array('name'=>'gender'));
            $event = \local_userapproval\event\header_missing::create($eventparams);// ... code that may add some record snapshots
            $event->trigger();
            return  $this->errors;
        }
        if (!in_array('nationality', $this->columnsmapping)) { 
            $this->errors[] = get_string('nationalitymissing', 'local_userapproval');
            $eventparams = array('context' => \context_system::instance(),'other'=>array('name'=>'nationality'));
            $event = \local_userapproval\event\header_missing::create($eventparams);// ... code that may add some record snapshots
            $event->trigger();
            return  $this->errors;
        }

        if (!in_array('countrycode', $this->columnsmapping)) { 
            $this->errors[] = get_string('countrycodemissing', 'local_userapproval');
            $eventparams = array('context' => \context_system::instance(),'other'=>array('name'=>'countrycode'));
            $event = \local_userapproval\event\header_missing::create($eventparams);// ... code that may add some record snapshots
            $event->trigger();
            return  $this->errors;
        }

        if (!in_array('mobileno', $this->columnsmapping)) { 
            $this->errors[] = get_string('mobilenomissing', 'local_userapproval');
            $eventparams = array('context' => \context_system::instance(),'other'=>array('name'=>'mobileno'));
            $event = \local_userapproval\event\header_missing::create($eventparams);// ... code that may add some record snapshots
            $event->trigger();
            return  $this->errors;
        }
        if (!in_array('username', $this->columnsmapping)) { 
            $this->errors[] = get_string('usernamemissing', 'local_userapproval');
            $eventparams = array('context' => \context_system::instance(),'other'=>array('name'=>'username'));
            $event = \local_userapproval\event\header_missing::create($eventparams);// ... code that may add some record snapshots
            $event->trigger();
            return  $this->errors;
        }
        if (!in_array('password', $this->columnsmapping)) { 
            $this->errors[] = get_string('passwordmissing', 'local_userapproval');
            $eventparams = array('context' => \context_system::instance(),'other'=>array('name'=>'password'));
            $event = \local_userapproval\event\header_missing::create($eventparams);// ... code that may add some record snapshots
            $event->trigger();
            return  $this->errors;
        }
        // if (!in_array('organization', $this->columnsmapping)) { 
        //     $this->errors[] = get_string('organizationmissing', 'local_userapproval');
        //     return  $this->errors;
        // }
       if (!in_array('idtype', $this->columnsmapping)) { 
            $this->errors[] = get_string('idtypemissing', 'local_userapproval');
            $eventparams = array('context' => \context_system::instance(),'other'=>array('name'=>'idtype'));
            $event = \local_userapproval\event\header_missing::create($eventparams);// ... code that may add some record snapshots
            $event->trigger();
            return  $this->errors;
        }
        if (!in_array('idnumber', $this->columnsmapping)) { 
            $this->errors[] = get_string('idnumbermissing', 'local_userapproval');
            $eventparams = array('context' => \context_system::instance(),'other'=>array('name'=>'idnumber'));
            $event = \local_userapproval\event\header_missing::create($eventparams);// ... code that may add some record snapshots
            $event->trigger();
            return  $this->errors;
        }
        if (!in_array('sector', $this->columnsmapping)) { 
            $this->errors[] = get_string('sectormissing', 'local_userapproval');
            $eventparams = array('context' => \context_system::instance(),'other'=>array('name'=>'sector'));
            $event = \local_userapproval\event\header_missing::create($eventparams);// ... code that may add some record snapshots
            $event->trigger();
            return  $this->errors;
        }
        if (!in_array('segment', $this->columnsmapping)) { 
            $this->errors[] = get_string('segmentmissing', 'local_userapproval');
            $eventparams = array('context' => \context_system::instance(),'other'=>array('name'=>'segment'));
            $event = \local_userapproval\event\header_missing::create($eventparams);// ... code that may add some record snapshots
            $event->trigger();
            return  $this->errors;
        }
       if (!in_array('jobfamily', $this->columnsmapping)) { 
            $this->errors[] = get_string('jobfamilymissing', 'local_userapproval');
            $eventparams = array('context' => \context_system::instance(),'other'=>array('name'=>'jobfamily'));
            $event = \local_userapproval\event\header_missing::create($eventparams);// ... code that may add some record snapshots
            $event->trigger();
            return  $this->errors;
        }
        if (!in_array('jobrole', $this->columnsmapping)) { 
            $this->errors[] = get_string('jobrolemissing', 'local_userapproval');
            $eventparams = array('context' => \context_system::instance(),'other'=>array('name'=>'jobrole'));
            $event = \local_userapproval\event\header_missing::create($eventparams);// ... code that may add some record snapshots
            $event->trigger();
            return  $this->errors;
        }
        if (!in_array('city', $this->columnsmapping)) { 
            $this->errors[] = get_string('citymissing', 'local_userapproval');
            $eventparams = array('context' => \context_system::instance(),'other'=>array('name'=>'city'));
            $event = \local_userapproval\event\header_missing::create($eventparams);// ... code that may add some record snapshots
            $event->trigger();
            return  $this->errors;
        }
         if(is_siteadmin() || has_capability('local/organization:manage_communication_officer',$systemcontext)){
            if (!in_array('role', $this->columnsmapping)) { 
                $this->errors[] = get_string('rolemissing', 'local_userapproval');
                $eventparams = array('context' => \context_system::instance(),'other'=>array('name'=>'role'));
                $event = \local_userapproval\event\header_missing::create($eventparams);// ... code that may add some record snapshots
                $event->trigger();
                return  $this->errors;
            }
        }
        return false;
    }

    private function required_fields_validations($excel,$option=0){
        global $DB;
        $systemcontext = \context_system::instance();
        $strings = new stdClass;
        $strings->excel_line_number = $this->excel_line_number;
        //-----------check old id-----------------------------------
        if (array_key_exists('oldid', $excel) ) {
            if (empty($excel->oldid)) {
                echo '<div class="local_userapproval_sync_error">'.get_string('oldidmissing','local_userapproval', $strings).'</div>'; 
                $this->errors[] =  get_string('oldidmissing', 'local_userapproval',$strings);
                $this->mfields[] = 'OldID';
                $this->errorcount++;
                $eventparams = array('context' => \context_system::instance(),'objectid'=>$strings->excel_line_number,'other'=>array('name'=>'OldID'));
                $event = \local_userapproval\event\missing_field::create($eventparams);// ... code that may add some record snapshots
                $event->trigger();
            }
        }
        //-----------check FirstName-----------------------------------
        if (array_key_exists('firstname', $excel) ) {
            if (empty($excel->firstname)) {
                echo '<div class="local_userapproval_sync_error">'.get_string('first_nameempty','local_userapproval', $strings).'</div>'; 
                $this->errors[] =  get_string('first_nameempty', 'local_userapproval',$strings);
                $this->mfields[] = 'FirstName';
                $this->errorcount++;
                $eventparams = array('context' => \context_system::instance(),'objectid'=>$strings->excel_line_number,'other'=>array('name'=>'FirstName'));
                $event = \local_userapproval\event\missing_field::create($eventparams);// ... code that may add some record snapshots
                $event->trigger();
            }
        }   
        //-------- check LastName-------------------------------------
        if ( array_key_exists('lastname', $excel) ) {
            if (empty($excel->lastname)) {
                echo '<div class="local_userapproval_sync_error">'.get_string('last_nameempty','local_userapproval', $strings).'</div>'; 
                $this->errors[] = get_string('last_nameempty','local_userapproval', $excel);
                $this->mfields[] = 'LastName';
                $this->errorcount++;
                $eventparams = array('context' => \context_system::instance(),'objectid'=>$strings->excel_line_number,'other'=>array('name'=>'LastName'));
                $event = \local_userapproval\event\missing_field::create($eventparams);// ... code that may add some record snapshots
                $event->trigger();
            }
        }
        //-------- check FirstNameArabic-------------------------------------
        if ( array_key_exists('firstnamearabic', $excel) ) {
            // if (empty($excel->firstnamearabic)) {
            //     echo '<div class="local_userapproval_sync_error">'.get_string('firstnamearabic_nameempty','local_userapproval', $strings).'</div>'; 
            //     $this->errors[] = get_string('firstnamearabic_nameempty','local_userapproval', $excel);
            //     $this->mfields[] = 'FirstNameArabic';
            //     $this->errorcount++;
            //     $eventparams = array('context' => \context_system::instance(),'objectid'=>$strings->excel_line_number,'other'=>array('name'=>'FirstNameArabic'));
            //     $event = \local_userapproval\event\missing_field::create($eventparams);// ... code that may add some record snapshots
            //     $event->trigger();
            // }
        }
        //-------- check LastNameArabic-------------------------------------
        if ( array_key_exists('lastnamearabic', $excel) ) {
            // if (empty($excel->lastnamearabic)) {
            //     echo '<div class="local_userapproval_sync_error">'.get_string('lastnamearabic_nameempty','local_userapproval', $strings).'</div>'; 
            //     $this->errors[] = get_string('lastnamearabic_nameempty','local_userapproval', $excel);
            //     $this->mfields[] = 'LastNameArabic';
            //     $this->errorcount++;
            //     $eventparams = array('context' => \context_system::instance(),'objectid'=>$strings->excel_line_number,'other'=>array('name'=>'LastNameArabic'));
            //     $event = \local_userapproval\event\missing_field::create($eventparams);// ... code that may add some record snapshots
            //     $event->trigger();
            // }
        }
        //-------- check Username-------------------------------------
        if ( array_key_exists('username', $excel) ) {
            if (empty($excel->username)) {
                echo '<div class="local_userapproval_sync_error">'.get_string('user_nameempty','local_userapproval', $strings).'</div>'; 
                $this->errors[] = get_string('user_nameempty','local_userapproval', $excel);
                $this->mfields[] = 'UserName';
                $this->errorcount++;
                $eventparams = array('context' => \context_system::instance(),'objectid'=>$strings->excel_line_number,'other'=>array('name'=>'UserName'));
                $event = \local_userapproval\event\missing_field::create($eventparams);// ... code that may add some record snapshots
                $event->trigger();
            }if (!empty($excel->username) && !preg_match('/^[A-Za-z0-9_$%&#@.]+$/',$excel->username)) {
                echo '<div class="local_userapproval_sync_error">'.get_string('validusername','local_userapproval', $strings).'</div>'; 
                $this->errors[] = get_string('validusername','local_userapproval', $excel);
                $this->mfields[] = 'UserName';
                $this->errorcount++;
                $eventparams = array('context' => \context_system::instance(),'objectid'=>$strings->excel_line_number,'other'=>array('name'=>'UserName'));
                $event = \local_userapproval\event\invalid_field::create($eventparams);// ... code that may add some record snapshots
                $event->trigger();
            }

           

        }
         //-------- check Email-------------------------------------
        if (array_key_exists('email', $excel) ) {
            if (empty(trim($excel->email))) {
                echo '<div class="local_userapproval_sync_error">'.get_string('emailempty','local_userapproval', $strings).'</div>'; 
                $this->errors[] = get_string('emailempty','local_userapproval', $strings);
                $this->mfields[] = 'Email';
                $this->errorcount++;
                $eventparams = array('context' => \context_system::instance(),'objectid'=>$strings->excel_line_number,'other'=>array('name'=>'Email'));
                $event = \local_userapproval\event\missing_field::create($eventparams);// ... code that may add some record snapshots
                $event->trigger();
            }

            if (!empty(trim($excel->email)) && !validate_email(trim(str_replace('Â','',$excel->email)))) {

                $strings->email = $excel->email;
                echo '<div class="local_userapproval_sync_error">'.get_string('emailnotvalid','local_userapproval', $strings).'</div>'; 
                $this->errors[] = get_string('emailnotvalid','local_userapproval', $strings);
                $this->mfields[] = 'Email';
                $this->errorcount++;
                $eventparams = array('context' => \context_system::instance(),'objectid'=>$strings->excel_line_number,'other'=>array('name'=>'Email'));
                $event = \local_userapproval\event\invalid_field::create($eventparams);// ... code that may add some record snapshots
                $event->trigger();
            }
            if(!empty(trim($excel->email)) && !empty(trim($excel->username))){
                $exist = $DB->get_record_select('user', 'username <> :username AND email = :email', ['username' => $excel->username, 'email' => $excel->email]);
                if($exist) {
                   echo '<div class="local_userapproval_sync_error">'.get_string('emailexists','local_userapproval', $strings).'</div>'; 
                   $this->errors[] = get_string('emailexists','local_userapproval', $strings);
                   $this->mfields[] = 'Email';
                   $this->errorcount++;
                $eventparams = array('context' => \context_system::instance(),'objectid'=>$strings->excel_line_number,'other'=>array('name'=>'Email'));
                $event = \local_userapproval\event\field_exists::create($eventparams);// ... code that may add some record snapshots
                $event->trigger();
                }
            }

        }
           //-------- check Gender-------------------------------------
        if ( array_key_exists('gender', $excel) ) {
            
            // if (empty($excel->gender)) {
            //     echo '<div class="local_userapproval_sync_error">'.get_string('genderempty','local_userapproval', $strings).'</div>'; 
            //     $this->errors[] = get_string('genderempty','local_userapproval', $excel);
            //     $this->mfields[] = 'Gender';
            //     $this->errorcount++;
            // }else
           if(!empty($excel->gender) && strtolower($excel->gender)!= 'male' && strtolower($excel->gender) != 'female') {
                echo '<div class="local_userapproval_sync_error">'.get_string('validgender','local_userapproval',$strings).'</div>'; 
                $this->errors[] = get_string('validgender','local_userapproval', $excel);
                $this->mfields[] = 'Gender';
                $this->errorcount++;
            }
        }
              //-------- check Password-------------------------------------
        if ( array_key_exists('password', $excel) ) {
              $errmsg = '';
            if (empty($excel->password)) {
                // echo '<div class="local_userapproval_sync_error">'.get_string('passwordempty','local_userapproval', $strings).'</div>'; 
                // $this->errors[] = get_string('passwordempty','local_userapproval', $excel);
                // $this->mfields[] = 'Password';
                // $this->errorcount++;
                $eventparams = array('context' => \context_system::instance(),'objectid'=>$strings->excel_line_number,'other'=>array('name'=>'Password'));
                $event = \local_userapproval\event\missing_field::create($eventparams);// ... code that may add some record snapshots
                $event->trigger();
            }elseif(strlen($excel->password) < 6){

                // echo '<div class="local_userapproval_sync_error">'.get_string('passwordmusthavemin6','local_userapproval',$strings).'</div>'; 
                // $this->errors[] = get_string('passwordmusthavemin6','local_userapproval', $excel);
                // $this->mfields[] = 'Password';
                // $this->errorcount++;
                $eventparams = array('context' => \context_system::instance(),'objectid'=>$strings->excel_line_number,'other'=>array('name'=>'Password'));
                $event = \local_userapproval\event\invalid_field::create($eventparams);// ... code that may add some record snapshots
                $event->trigger();
            }
        }
        if (array_key_exists('language', $excel) ) {
            // $languages = get_string_manager()->get_list_of_languages();
            // $lang = strtolower($excel->language);
            // if (empty($excel->language)) {
            //     echo '<div class="local_userapproval_sync_error">'.get_string('languageempty','local_userapproval', $strings).'</div>'; 
            //     $this->errors[] = get_string('languageempty','local_userapproval', $excel);
            //     $this->mfields[] = 'Language';
            //     $this->errorcount++;
            // }else{
            
            //     if(!array_key_exists($lang,$languages)) {
                  
            //         echo '<div class="local_userapproval_sync_error">'.get_string('validlanguage','local_userapproval', $strings).'</div>'; 
            //         $this->errors[] = get_string('validlanguage','local_userapproval', $excel);
            //         $this->mfields[] = 'Language';
            //         $this->errorcount++;

            //     }
                
            // }

            if (!empty($excel->language)) {

                $languages = get_string_manager()->get_list_of_languages();
                $lang = strtolower($excel->language);
                if(!array_key_exists($lang,$languages)) {
                    // echo '<div class="local_userapproval_sync_error">'.get_string('validlanguage','local_userapproval', $strings).'</div>'; 
                    // $this->errors[] = get_string('validlanguage','local_userapproval', $excel);
                    // $this->mfields[] = 'Language';
                    // $this->errorcount++;
                $eventparams = array('context' => \context_system::instance(),'objectid'=>$strings->excel_line_number,'other'=>array('name'=>'Language'));
                $event = \local_userapproval\event\invalid_field::create($eventparams);// ... code that may add some record snapshots
                $event->trigger();

                }
            }   
        }

        if(array_key_exists('nationality', $excel) ) {
            $countries = get_string_manager()->get_list_of_countries();
            $nationality = strtoupper($excel->nationality);
            // if (empty($excel->nationality)) {
            //     echo '<div class="local_userapproval_sync_error">'.get_string('nationalityempty','local_userapproval', $strings).'</div>'; 
            //     $this->errors[] = get_string('nationalityempty','local_userapproval', $excel);
            //     $this->mfields[] = 'Nationality';
            //     $this->errorcount++;
            // }
            // else{

                // if(!empty($excel->nationality) &&  !array_key_exists($nationality,$countries) ) {
                
                //    echo '<div class="local_userapproval_sync_error">'.get_string('validnationality','local_userapproval', $strings).'</div>'; 
                //     $this->errors[] = get_string('validnationality','local_userapproval', $excel);
                //     $this->mfields[] = 'Nationality';
                //     $this->errorcount++;

                // }

            // }

            if (!empty($excel->nationality)) {

                $countries = get_string_manager()->get_list_of_countries();
                $nationality = strtoupper($excel->nationality);
                if(!array_key_exists($nationality,$countries)) {
                    // echo '<div class="local_userapproval_sync_error">'.get_string('validnationality','local_userapproval', $strings).'</div>'; 
                    // $this->errors[] = get_string('validnationality','local_userapproval', $excel);
                    // $this->mfields[] = 'Nationality';
                    // $this->errorcount++;
                $eventparams = array('context' => \context_system::instance(),'objectid'=>$strings->excel_line_number,'other'=>array('name'=>'Nationality'));
                $event = \local_userapproval\event\invalid_field::create($eventparams);// ... code that may add some record snapshots
                $event->trigger();
                }
            }   
        }
        if ( array_key_exists('countrycode', $excel) ) {
            if (empty(trim($excel->countrycode))) {
                $eventparams = array('context' => \context_system::instance(),'objectid'=>$strings->excel_line_number,'other'=>array('name'=>'CountryCode'));
                $event = \local_userapproval\event\missing_field::create($eventparams);// ... code that may add some record snapshots
                $event->trigger();
            }elseif(!is_numeric(trim($excel->countrycode))){
                $eventparams = array('context' => \context_system::instance(),'objectid'=>$strings->excel_line_number,'other'=>array('name'=>'CountryCode'));
                $event = \local_userapproval\event\invalid_field::create($eventparams);// ... code that may add some record snapshots
                $event->trigger();
            }

        }
        if ( array_key_exists('mobileno', $excel) ) {
            if (empty(trim($excel->mobileno))) {
                // echo '<div class="local_userapproval_sync_error">'.get_string('mobilenoempty','local_userapproval', $strings).'</div>'; 
                // $this->errors[] = get_string('mobilenoempty','local_userapproval', $excel);
                // $this->mfields[] = 'Mobileno';
                // $this->errorcount++;
                $eventparams = array('context' => \context_system::instance(),'objectid'=>$strings->excel_line_number,'other'=>array('name'=>'Mobileno'));
                $event = \local_userapproval\event\missing_field::create($eventparams);// ... code that may add some record snapshots
                $event->trigger();
            }elseif(!is_numeric(trim($excel->mobileno))){
                // echo '<div class="local_userapproval_sync_error">'.get_string('validmobileno','local_userapproval', $strings).'</div>'; 
                // $this->errors[] = get_string('validmobileno','local_userapproval', $excel);
                // $this->mfields[] = 'Mobileno';
                // $this->errorcount++;
                $eventparams = array('context' => \context_system::instance(),'objectid'=>$strings->excel_line_number,'other'=>array('name'=>'Mobileno'));
                $event = \local_userapproval\event\invalid_field::create($eventparams);// ... code that may add some record snapshots
                $event->trigger();
            }
            // elseif((strlen(trim($excel->mobileno)) < 5  || strlen(trim($excel->mobileno)) > 10 )) {
            //     echo '<div class="local_userapproval_sync_error">'.get_string('minimum5digitsallowederr','local_userapproval', $strings).'</div>'; 
            //     $this->errors[] = get_string('minimum5digitsallowederr','local_userapproval', $excel);
            //     $this->mfields[] = 'Mobileno';
            //     $this->errorcount++;
            // }
            // elseif (!empty(trim($excel->mobileno))  && (strlen(trim($excel->mobileno)) >= 5  &&  strlen(trim($excel->mobileno)) <= 10) &&  !preg_match('/^[5-9][0-9]/',trim($excel->mobileno))) {
            //     echo '<div class="local_userapproval_sync_error">'.get_string('startswith5err','local_userapproval', $strings).'</div>'; 
            //     $this->errors[] = get_string('startswith5err','local_userapproval', $excel);
            //     $this->mfields[] = 'Mobileno';
            //     $this->errorcount++;
            // }

        }

         if(is_siteadmin() || has_capability('local/organization:manage_communication_officer',$systemcontext)){
            //-------- check Organization-------------------------------------
            if ( array_key_exists('organization', $excel) ) {
                $organisationlist=$DB->get_fieldset_sql('SELECT LOWER(shortname) FROM {local_organization} where visible=1 and status=2');
                $org = strtolower($excel->organization);
                  
                // if (empty($excel->organization)) {
                //     echo '<div class="local_userapproval_sync_error">'.get_string('organizationempty','local_userapproval', $strings).'</div>'; 
                //     $this->errors[] = get_string('organizationempty','local_userapproval', $excel);
                //     $this->mfields[] = 'Organization';
                //     $this->errorcount++;
                // }
                if(!empty($excel->organization) && !in_array(trim($org),$organisationlist)){
                    // echo '<div class="local_userapproval_sync_error">'.get_string('validorganization','local_userapproval', $strings).'</div>'; 
                    // $this->errors[] = get_string('validorganization','local_userapproval', $excel);
                    // $this->mfields[] = 'Organization';
                    // $this->errorcount++;
                    $eventparams = array('context' => \context_system::instance(),'objectid'=>$strings->excel_line_number,'other'=>array('name'=>'Organization'));
                    $event = \local_userapproval\event\invalid_field::create($eventparams);// ... code that may add some record snapshots
                    $event->trigger();
                }
            }
        }
       
        //-------- check idtype-------------------------------------
        if ( array_key_exists('idtype', $excel) ) {
            $idtype = array('id','passport','saudiid','residentialid');
            // if (empty($excel->idtype)) {
            //     echo '<div class="local_userapproval_sync_error">'.get_string('idtypeempty','local_userapproval', $strings).'</div>'; 
            //     $this->errors[] = get_string('idtypeempty','local_userapproval', $excel);
            //     $this->mfields[] = 'IDtype';
            //     $this->errorcount++;
            // }else
            // if(!in_array(strtolower($excel->idtype),$idtype)){
            //     echo '<div class="local_userapproval_sync_error">'.get_string('valididtype','local_userapproval',$strings).'</div>'; 
            //     $this->errors[] = get_string('valididtype','local_userapproval', $excel);
            //     $this->mfields[] = 'IDtype';
            //     $this->errorcount++;
            // }
        }
          //-------- check idnumber------------------------------------
        if ( array_key_exists('idnumber', $excel) ) {
            // if (empty($excel->idnumber)) {
            //     echo '<div class="local_userapproval_sync_error">'.get_string('idnumberempty','local_userapproval', $strings).'</div>'; 
            //     $this->errors[] = get_string('idnumberempty','local_userapproval', $excel);
            //     $this->mfields[] = 'IDnumber';
            //     $this->errorcount++;
            // }else

             if(!empty(trim($excel->idnumber)) && strlen($excel->idnumber) < 10){

                echo '<div class="local_userapproval_sync_error">'.get_string('idnumbermusthavemin10','local_userapproval',$strings).'</div>'; 
                $this->errors[] = get_string('idnumbermusthavemin10','local_userapproval', $excel);
                $this->mfields[] = 'IDnumber';
                $this->errorcount++;
                $eventparams = array('context' => \context_system::instance(),'objectid'=>$strings->excel_line_number,'other'=>array('name'=>'IDnumber'));
                $event = \local_userapproval\event\invalid_field::create($eventparams);// ... code that may add some record snapshots
                $event->trigger();
            }
            if (!empty(trim($excel->idnumber)) && !preg_match('/^[a-zA-Z0-9,]*$/',trim($excel->idnumber))) {
                // echo '<div class="local_userapproval_sync_error">'.get_string('acceptsstringsandnumericonlyerr','local_userapproval', $strings).'</div>'; 
                // $this->errors[] = get_string('acceptsstringsandnumericonlyerr','local_userapproval', $excel);
                // $this->mfields[] = 'IDnumber';
                // $this->errorcount++;
                $eventparams = array('context' => \context_system::instance(),'objectid'=>$strings->excel_line_number,'other'=>array('name'=>'IDnumber'));
                $event = \local_userapproval\event\invalid_field::create($eventparams);// ... code that may add some record snapshots
                $event->trigger();
            }
        }
         //-------- check Sector-------------------------------------
        if ( array_key_exists('sector', $excel) ) {
           
            if (!empty($excel->sector) && $excel->sector !='NULL') {
                $is_sector_exists =  $this->sector_exists($excel->sector);
                if(!$is_sector_exists) {
                    // echo '<div class="local_userapproval_sync_error">'.get_string('validsector','local_userapproval', $strings).'</div>'; 
                    // $this->errors[] = get_string('validsector','local_userapproval', $excel);
                    // $this->mfields[] = 'Sector';
                    // $this->errorcount++;
                $eventparams = array('context' => \context_system::instance(),'objectid'=>$strings->excel_line_number,'other'=>array('name'=>'Sector'));
                $event = \local_userapproval\event\invalid_field::create($eventparams);// ... code that may add some record snapshots
                $event->trigger();
                }
                
            }
        }
        //-------- check Segment-------------------------------------
        if (array_key_exists('segment', $excel)) {
        
            if (!empty($excel->segment) && $excel->segment !='NULL') {
            
                $is_sector_exists =  $this->sector_exists($excel->sector);

                if($is_sector_exists) {

                    $is_segment_exists =  $this->segment_exists($excel->segment,$excel->sector);

                    if(!$is_segment_exists) {

                        // echo '<div class="local_userapproval_sync_error">'.get_string('validsegment','local_userapproval', $strings).'</div>'; 
                        // $this->errors[] = get_string('validsegment','local_userapproval', $excel);
                        // $this->mfields[] = 'Segment';
                        // $this->errorcount++;
                $eventparams = array('context' => \context_system::instance(),'objectid'=>$strings->excel_line_number,'other'=>array('name'=>'Segment'));
                $event = \local_userapproval\event\invalid_field::create($eventparams);// ... code that may add some record snapshots
                $event->trigger();
                    }
                }

            
            }

        }
          //-------- check Jobfamily-------------------------------------
        if (array_key_exists('jobfamily', $excel) ) {
        
            if (!empty($excel->jobfamily) && $excel->jobfamily !='NULL') {
            
                $is_sector_exists =  $this->sector_exists($excel->sector);
                $is_segment_exists =  $this->segment_exists($excel->segment,$excel->sector);

                if($is_sector_exists && $is_segment_exists) {

                  $is_jobfamily_exists =  $this->jobfamily_exists($excel->jobfamily,$excel->segment);

                  if(!$is_jobfamily_exists) {
                    
                    // echo '<div class="local_userapproval_sync_error">'.get_string('validjobfamily','local_userapproval', $strings).'</div>'; 
                    // $this->errors[] = get_string('validjobfamily','local_userapproval', $excel);
                    // $this->mfields[] = 'Jobfamily';
                    // $this->errorcount++;
                $eventparams = array('context' => \context_system::instance(),'objectid'=>$strings->excel_line_number,'other'=>array('name'=>'Jobfamily'));
                $event = \local_userapproval\event\invalid_field::create($eventparams);// ... code that may add some record snapshots
                $event->trigger();

                  }


                }

             
            }
            
        }
           //-------- check Jobrole-------------------------------------
        if (array_key_exists('jobrole', $excel) ) {
        
            if (!empty($excel->jobrole) && $excel->jobrole !='NULL') {
    
              $is_sector_exists =  $this->sector_exists($excel->sector);
              $is_segment_exists =  $this->segment_exists($excel->segment,$excel->sector);
              $is_jobfamily_exists =  $this->jobfamily_exists($excel->jobfamily,$excel->segment);
              if($is_sector_exists &&  $is_segment_exists && $is_jobfamily_exists) {
                $is_jobrole_exists =  $this->jobrole_exists($excel->jobrole,$excel->jobfamily);
                if(!$is_jobrole_exists) {
                    // echo '<div class="local_userapproval_sync_error">'.get_string('validjobrole','local_userapproval', $strings).'</div>'; 
                    // $this->errors[] = get_string('validjobfamily','validjobrole', $excel);
                    // $this->mfields[] = 'jobrole';
                    // $this->errorcount++;
                $eventparams = array('context' => \context_system::instance(),'objectid'=>$strings->excel_line_number,'other'=>array('name'=>'Jobrole'));
                $event = \local_userapproval\event\invalid_field::create($eventparams);// ... code that may add some record snapshots
                $event->trigger();

                }

              }

            }
    
        }
        //-------- check City-------------------------------------
        // if ( array_key_exists('city', $excel) ) {
        //     if (empty($excel->city)) {
        //         echo '<div class="local_userapproval_sync_error">'.get_string('cityempty','local_userapproval', $strings).'</div>'; 
        //         $this->errors[] = get_string('cityempty','local_userapproval', $excel);
        //         $this->mfields[] = 'City';
        //         $this->errorcount++;
        //     }
        // }
        if(is_siteadmin() || has_capability('local/organization:manage_communication_officer',$systemcontext)){
          //-------- check role-------------------------------------
            if ( array_key_exists('role', $excel) ) {
                if (!empty($excel->role)) {
                    $rolelist = $DB->get_fieldset_sql("SELECT shortname FROM {role}");
                    $role = strtolower($excel->role);
                    if(!in_array(trim($role),$rolelist)){
                           // echo '<div class="local_userapproval_sync_error">'.get_string('validrole','local_userapproval', $strings).'</div>'; 
                           // $this->errors[] = get_string('validrole','local_userapproval', $excel);
                           // $this->mfields[] = 'Role';
                           // $this->errorcount++;
                           $eventparams = array('context' => \context_system::instance(),'objectid'=>$strings->excel_line_number,'other'=>array('name'=>'Role'));
                           $event = \local_userapproval\event\invalid_field::create($eventparams);// ... code that may add some record snapshots
                           $event->trigger();
                    }
               }
           
            }
        }
       
       
 
    } // end of required_fields_validations function
    /**
     * @method get_users_file
     * @todo Returns the uploaded file if it is present.
     * @param int $draftid
     * @return stored_file|null
     */
    public function get_users_file($draftid) {
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

    public function sector_exists($usersector = null) {
       global $DB,$USER;

        if(!empty($usersector)) {
            $sql = " SELECT id FROM {local_sector} 
                        WHERE code IN ('$usersector') ";
            $sector = $DB->record_exists_sql($sql);
            return $sector;
       }
        
    }

    public function segment_exists($usersegment = null, $usersector = null) {
       global $DB,$USER;


        if(!empty($usersector) && !empty($usersegment)) {
            $sectorid = $DB->get_field_sql("SELECT id FROM {local_sector} 
                        WHERE code IN ('$usersector')");


            if($sectorid) {

                $sql = "SELECT seg.id
                    FROM {local_segment} as seg
                    JOIN {local_sector} as sec ON sec.id = seg.sectorid WHERE seg.sectorid = $sectorid AND seg.code IN ('$usersegment') ";
               $sector = $DB->record_exists_sql($sql);

                return $sector;
            }
        
           
           
       }
        
    }

    public function jobfamily_exists($userjobfamily = null, $usersegment = null) {
       global $DB,$USER;

        if(!empty($userjobfamily) && !empty($usersegment)) {
            $segmentid = $DB->get_field_sql("SELECT id FROM {local_segment} 
                        WHERE code IN ('$usersegment')");

            if($segmentid){

                $sql = "SELECT ljob.id
                        FROM {local_jobfamily} as ljob
                        JOIN {local_segment} as seg ON seg.id = ljob.segmentid WHERE ljob.segmentid = $segmentid AND ljob.code IN ('$userjobfamily') ";
                $sector = $DB->record_exists_sql($sql);
                return $sector;
            }
        
            
       }
        
    }

    public function jobrole_exists($userjobrole = null, $userjobfamily = null) {
       global $DB,$USER;

        if(!empty($userjobrole) && !empty($userjobfamily)) {
            $jobfamilytid = $DB->get_field_sql("SELECT id FROM {local_jobfamily} 
                        WHERE code IN ('$userjobfamily')");

            if($jobfamilytid) {
               
                $sql = "SELECT ljobl.id
                        FROM {local_jobrole_level} as ljobl
                        JOIN {local_jobfamily} as ljob ON ljob.id = ljobl.jobfamily WHERE ljobl.jobfamily = $jobfamilytid AND ljobl.code IN ('$userjobrole') ";
                $sector = $DB->record_exists_sql($sql);
                return $sector;

            }
        
           
       }
        
    }

    


}

