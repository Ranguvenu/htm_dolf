<?php
namespace local_exams\local;
defined('MOODLE_INTERNAL') || die;
use context_user;
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
//use local_trainingprogram\local\exams as tp;

class profile_upload  {

    protected $columns;

    protected $columnsmapping = array();

    private $users = array();

    protected $allowedcolumns = array('profilecode','examcode', 'seats','profileduration','profilestartdate','profileenddate','passingpercentage','active','decisionflow','published','language', 'numberofquestions','trialquestions','materialurl','audience','nda','instructions','hascertificate','showpreexampage','showremainingduration','showsuccessrequirements','shownumberofquestions','showexamduration','allowexaminertowriteacommentoneachquestion','allowexaminertowriteacommentaftersubmission','showexamresult','showexamgrade','showcompetenciesresult','showresultforeachcompetency','examinermustfillevaluationformafterexamsubmission','notifytheexaminerbeforeexamendsby');


    /**
     * @method upload_program_file file
     * @todo To process the uploaded CSV file and return the data
     * @param stored_file $file
     * @param string $encoding
     * @param string $delimiter
     * @param context $defaultcontext
     * @return array
     */

    public function upload_profile_file($file, $defaultcontext) {
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
        $uploadid = csv_import_reader::get_new_iid('ProfileUpload');
        $cir = new csv_import_reader($uploadid, 'ProfileUpload');

        /**
         * Actual upload starts from here
         */
        $readcount = $cir->load_csv_content($content, 'utf-8', 'comma');

        unset($content);
        if (!$readcount ) {
            throw new moodle_exception('csvloaderror', 'error',$PAGE->url,  $cir->get_error());
        }
        if(!is_siteadmin() AND !has_capability('local/organization:manage_trainingofficial', $systemcontext)){
            throw new moodle_exception('youdonthavepermissiontouploaddata', 'local_exams');
        }
        

        if($readcount == 1) {

          throw new moodle_exception('filecannotbehaveemptydata', 'local_exams');
        }    
            $this->columns = $cir->get_columns();
            $column_validation = $this->validate_columns();
            if(is_array($column_validation) && count($column_validation) > 0){
                $string = $column_validation[0];
                $return =  '<div class="local_trainingprogram_sync_error">'.get_string('validsheet','local_exams',$string).'</div>'; 
                $return .= html_writer::start_tag('div', array('class'=> 'w-100 mt-5')).$OUTPUT->continue_button(new moodle_url('/local/exams/profileupload.php')).html_writer::end_tag('div');
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
        
        while ($row = $cir->next()) {
			$linenum++;
            
            $hash = array();
            $masterdata = new stdClass();
            $currentprofile = new stdClass();
            $masterinsertdata = new stdClass();
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
                $masterinsertdata->profilecode = $masterdata->profilecode;

                $masterinsertdata->seatingcapacity = $masterdata->seats;

                $masterinsertdata->duration = ($masterdata->profileduration * 60);

                $masterinsertdata->registrationstartdate = strtotime($masterdata->profilestartdate);

                $masterinsertdata->registrationenddate = strtotime($masterdata->profileenddate);

                $masterinsertdata->passinggrade = $masterdata->passingpercentage;

                $masterinsertdata->activestatus = $masterdata->active;

                $masterinsertdata->decision = $masterdata->decisionflow;

                $masterinsertdata->publishstatus = $masterdata->published;

                $masterinsertdata->language = $masterdata->language;

                $masterinsertdata->questions = $masterdata->numberofquestions;

                $masterinsertdata->trailquestions = $masterdata->trialquestions;

                $masterinsertdata->materialurl = $masterdata->materialurl;

                $masterinsertdata->material = !empty($masterdata->materialurl) ? 1 : 0;

                $masterinsertdata->targetaudience = $masterdata->audience;

                $masterinsertdata->nondisclosure['text'] = $masterdata->nda;

                $masterinsertdata->instructions['text'] = $masterdata->instructions;

                $masterinsertdata->hascertificate = !empty($masterdata->hascertificate) ? $masterdata->hascertificate : 0;

                $masterinsertdata->preexampage = $masterdata->showpreexampage;

                $masterinsertdata->showremainingduration = $masterdata->showremainingduration;

                $masterinsertdata->successrequirements = $masterdata->showsuccessrequirements;

                $masterinsertdata->showquestions = $masterdata->shownumberofquestions;

                $masterinsertdata->showexamduration = $masterdata->showexamduration;

                $masterinsertdata->commentsoneachque = $masterdata->allowexaminertowriteacommentoneachquestion;

                $masterinsertdata->showexamresult = $masterdata->showexamresult;

                $masterinsertdata->showexamgrade = $masterdata->showexamgrade;

                $masterinsertdata->competencyresult = $masterdata->showcompetenciesresult;

                $masterinsertdata->resultofeachcompetency = $masterdata->showresultforeachcompetency;
                
                $masterinsertdata->commentsaftersub = $masterdata->allowexaminertowriteacommentaftersubmission;

                $masterinsertdata->evaluationform = $masterdata->examinermustfillevaluationformafterexamsubmission;

                $masterinsertdata->notifybeforeexam = $masterdata->notifytheexaminerbeforeexamendsby;
                $examcode = $masterdata->examcode;
                $examid =$DB->get_record('local_exams',['code'=>$examcode]);
                $masterinsertdata->examid = $examid->id;

                $currentprofile = $DB->get_record('local_exam_profiles',['profilecode'=>$masterdata->profilecode]);
                if ($currentprofile) {
                    $masterinsertdata->id = $currentprofile->id;
                }else{
                    $masterinsertdata->id = -1;
                }
                $changediu = (new \local_exams\local\exams)->add_update_profile($masterinsertdata);
                
                if($changediu=="2")
                {
                    $message = get_string('updated','local_exams', $masterdata->profilecode);
                    $return .= $message;
                }
                elseif($changediu=="1"){
                    $message =  get_string('success', 'local_exams');
                    $return .= $message;
                }
            }
	    }        
        $return .= html_writer::start_tag('div', array('class'=> 'w-100 mt-5')).$OUTPUT->continue_button(new moodle_url('/local/exams/profileupload.php')).html_writer::end_tag('div');
        
        echo $return;
    }
    private function validate_columns() {
        global $DB;

        foreach ($this->columns as $i => $columnname) {
        
            if (in_array(strtolower($columnname), $this->allowedcolumns)) {
                $this->columnsmapping[$i] = strtolower($columnname);
                
            }

        }
        //print_r($this->columnsmapping);
        if (!in_array('profilecode', $this->columnsmapping)) {
            $this->errors[] = get_string('profilecode_missing', 'local_exams');
            $eventparams = array('context' => \context_system::instance(),'other'=>array('name'=>'profilecode'));
            $event = \local_exams\event\header_missing::create($eventparams);// ... code that may add some record snapshots
            $event->trigger();
            return  $this->errors;
        }

        if (!in_array('profileduration', $this->columnsmapping)) {
            $this->errors[] = get_string('profileduration_missing', 'local_exams');
            $eventparams = array('context' => \context_system::instance(),'other'=>array('name'=>'profileduration'));
            $event = \local_exams\event\header_missing::create($eventparams);// ... code that may add some record snapshots
            $event->trigger();
            return  $this->errors;
        }

        if (!in_array('profilestartdate', $this->columnsmapping)) {
            $this->errors[] = get_string('profilestartdate_missing', 'local_exams');
            $eventparams = array('context' => \context_system::instance(),'other'=>array('name'=>'profilestartdate'));
            $event = \local_exams\event\header_missing::create($eventparams);// ... code that may add some record snapshots
            $event->trigger();
            return  $this->errors;
        }

        if (!in_array('profileenddate', $this->columnsmapping)) {
            $this->errors[] = get_string('profileenddate_missing', 'local_exams');
            $eventparams = array('context' => \context_system::instance(),'other'=>array('name'=>'profileenddate'));
            $event = \local_exams\event\header_missing::create($eventparams);// ... code that may add some record snapshots
            $event->trigger();
            return  $this->errors;
        }

        if (!in_array('examcode', $this->columnsmapping)) {
            $this->errors[] = get_string('examcode_missing', 'local_exams');
            $eventparams = array('context' => \context_system::instance(),'other'=>array('name'=>'examcode'));
            $event = \local_exams\event\header_missing::create($eventparams);// ... code that may add some record snapshots
            $event->trigger();
            return  $this->errors;
        }

        if (!in_array('seats', $this->columnsmapping)) {
            $this->errors[] = get_string('seats_missing', 'local_exams');
            $eventparams = array('context' => \context_system::instance(),'other'=>array('name'=>'seats'));
            $event = \local_exams\event\header_missing::create($eventparams);// ... code that may add some record snapshots
            $event->trigger();
            return  $this->errors;
        }

        if (!in_array('passingpercentage', $this->columnsmapping)) {
            $this->errors[] = get_string('passingpercentage_missing', 'local_exams');
            $eventparams = array('context' => \context_system::instance(),'other'=>array('name'=>'passingpercentage'));
            $event = \local_exams\event\header_missing::create($eventparams);// ... code that may add some record snapshots
            $event->trigger();
            return  $this->errors;
        }

        if (!in_array('active', $this->columnsmapping)) {
            $this->errors[] = get_string('active_missing', 'local_exams');
            $eventparams = array('context' => \context_system::instance(),'other'=>array('name'=>'active'));
            $event = \local_exams\event\header_missing::create($eventparams);// ... code that may add some record snapshots
            $event->trigger();
            return  $this->errors;
        }

        if (!in_array('decisionflow', $this->columnsmapping)) {
            $this->errors[] = get_string('decisionflow_missing', 'local_exams');
            $eventparams = array('context' => \context_system::instance(),'other'=>array('name'=>'decisionflow'));
            $event = \local_exams\event\header_missing::create($eventparams);// ... code that may add some record snapshots
            $event->trigger();
            return  $this->errors;
        }

        if (!in_array('published', $this->columnsmapping)) {
            $this->errors[] = get_string('published_missing', 'local_exams');
            $eventparams = array('context' => \context_system::instance(),'other'=>array('name'=>'published'));
            $event = \local_exams\event\header_missing::create($eventparams);// ... code that may add some record snapshots
            $event->trigger();
            return  $this->errors;
        }

        if (!in_array('language', $this->columnsmapping)) {
            $this->errors[] = get_string('language_missing', 'local_exams');
            $eventparams = array('context' => \context_system::instance(),'other'=>array('name'=>'language'));
            $event = \local_exams\event\header_missing::create($eventparams);// ... code that may add some record snapshots
            $event->trigger();
            return  $this->errors;
        }

        if (!in_array('numberofquestions', $this->columnsmapping)) {
            $this->errors[] = get_string('numberofquestions_missing', 'local_exams');
            $eventparams = array('context' => \context_system::instance(),'other'=>array('name'=>'numberofquestions'));
            $event = \local_exams\event\header_missing::create($eventparams);// ... code that may add some record snapshots
            $event->trigger();
            return  $this->errors;
        }

        if (!in_array('trialquestions', $this->columnsmapping)) {
            $this->errors[] = get_string('trialquestions_missing', 'local_exams');
            $eventparams = array('context' => \context_system::instance(),'other'=>array('name'=>'trialquestions'));
            $event = \local_exams\event\header_missing::create($eventparams);// ... code that may add some record snapshots
            $event->trigger();
            return  $this->errors;
        }

        if (!in_array('materialurl', $this->columnsmapping)) {
            $this->errors[] = get_string('materialurl_missing', 'local_exams');
            $eventparams = array('context' => \context_system::instance(),'other'=>array('name'=>'materialurl'));
            $event = \local_exams\event\header_missing::create($eventparams);// ... code that may add some record snapshots
            $event->trigger();
            return  $this->errors;
        }

        if (!in_array('audience', $this->columnsmapping)) {
            $this->errors[] = get_string('audience_missing', 'local_exams');
            $eventparams = array('context' => \context_system::instance(),'other'=>array('name'=>'audience'));
            $event = \local_exams\event\header_missing::create($eventparams);// ... code that may add some record snapshots
            $event->trigger();
            return  $this->errors;
        }

        if (!in_array('nda', $this->columnsmapping)) {
            $this->errors[] = get_string('NDA_missing', 'local_exams');
            $eventparams = array('context' => \context_system::instance(),'other'=>array('name'=>'nda'));
            $event = \local_exams\event\header_missing::create($eventparams);// ... code that may add some record snapshots
            $event->trigger();
            return  $this->errors;
        }

        if (!in_array('instructions', $this->columnsmapping)) {
            $this->errors[] = get_string('instructions_missing', 'local_exams');
            $eventparams = array('context' => \context_system::instance(),'other'=>array('name'=>'instructions'));
            $event = \local_exams\event\header_missing::create($eventparams);// ... code that may add some record snapshots
            $event->trigger();
            return  $this->errors;
        }

        if (!in_array('hascertificate', $this->columnsmapping)) {
            $this->errors[] = get_string('hascertificate_missing', 'local_exams');
            $eventparams = array('context' => \context_system::instance(),'other'=>array('name'=>'hascertificate'));
            $event = \local_exams\event\header_missing::create($eventparams);// ... code that may add some record snapshots
            $event->trigger();
            return  $this->errors;
        }

        if (!in_array('showpreexampage', $this->columnsmapping)) {
            $this->errors[] = get_string('showpreexamPage_missing', 'local_exams');
            $eventparams = array('context' => \context_system::instance(),'other'=>array('name'=>'showpreexampage'));
            $event = \local_exams\event\header_missing::create($eventparams);// ... code that may add some record snapshots
            $event->trigger();
            return  $this->errors;
        }

        if (!in_array('showremainingduration', $this->columnsmapping)) {
            $this->errors[] = get_string('showremainingduration_missing', 'local_exams');
            $eventparams = array('context' => \context_system::instance(),'other'=>array('name'=>'showremainingduration'));
            $event = \local_exams\event\header_missing::create($eventparams);// ... code that may add some record snapshots
            $event->trigger();
            return  $this->errors;
        }

        if (!in_array('showsuccessrequirements', $this->columnsmapping)) {
            $this->errors[] = get_string('showsuccessrequirements_missing', 'local_exams');
            $eventparams = array('context' => \context_system::instance(),'other'=>array('name'=>'showsuccessrequirements'));
            $event = \local_exams\event\header_missing::create($eventparams);// ... code that may add some record snapshots
            $event->trigger();
            return  $this->errors;
        }

        if (!in_array('shownumberofquestions', $this->columnsmapping)) {
            $this->errors[] = get_string('shownumberofquestions_missing', 'local_exams');
            $eventparams = array('context' => \context_system::instance(),'other'=>array('name'=>'shownumberofquestions'));
            $event = \local_exams\event\header_missing::create($eventparams);// ... code that may add some record snapshots
            $event->trigger();
            return  $this->errors;
        }

        if (!in_array('showexamduration', $this->columnsmapping)) {
            $this->errors[] = get_string('showexamduration_missing', 'local_exams');
            $eventparams = array('context' => \context_system::instance(),'other'=>array('name'=>'showexamduration'));
            $event = \local_exams\event\header_missing::create($eventparams);// ... code that may add some record snapshots
            $event->trigger();
            return  $this->errors;
        }

        if (!in_array('allowexaminertowriteacommentoneachquestion', $this->columnsmapping)) {
            $this->errors[] = get_string('commentoneachquestion_missing', 'local_exams');
            $eventparams = array('context' => \context_system::instance(),'other'=>array('name'=>'allowexaminertowriteacommentoneachquestion'));
            $event = \local_exams\event\header_missing::create($eventparams);// ... code that may add some record snapshots
            $event->trigger();
            return  $this->errors;
        }

        if (!in_array('allowexaminertowriteacommentaftersubmission', $this->columnsmapping)) {
            $this->errors[] = get_string('commentaftersubmission_missing', 'local_exams');
            $eventparams = array('context' => \context_system::instance(),'other'=>array('name'=>'allowexaminertowriteacommentaftersubmission'));
            $event = \local_exams\event\header_missing::create($eventparams);// ... code that may add some record snapshots
            $event->trigger();
            return  $this->errors;
        }

        if (!in_array('showexamresult', $this->columnsmapping)) {
            $this->errors[] = get_string('showexamresult_missing', 'local_exams');
            $eventparams = array('context' => \context_system::instance(),'other'=>array('name'=>'showexamresult'));
            $event = \local_exams\event\header_missing::create($eventparams);// ... code that may add some record snapshots
            $event->trigger();
            return  $this->errors;
        }

        if (!in_array('showexamgrade', $this->columnsmapping)) {
            $this->errors[] = get_string('showexamgrade_missing', 'local_exams');
            $eventparams = array('context' => \context_system::instance(),'other'=>array('name'=>'showexamgrade'));
            $event = \local_exams\event\header_missing::create($eventparams);// ... code that may add some record snapshots
            $event->trigger();
            return  $this->errors;
        }

        if (!in_array('showcompetenciesresult', $this->columnsmapping)) {
            $this->errors[] = get_string('showcompetenciesresult_missing', 'local_exams');
            $eventparams = array('context' => \context_system::instance(),'other'=>array('name'=>'showcompetenciesresult'));
            $event = \local_exams\event\header_missing::create($eventparams);// ... code that may add some record snapshots
            $event->trigger();
            return  $this->errors;
        }

        if (!in_array('showresultforeachcompetency', $this->columnsmapping)) {
            $this->errors[] = get_string('eachcomeptency_missing', 'local_exams');
            $eventparams = array('context' => \context_system::instance(),'other'=>array('name'=>'showresultforeachcompetency'));
            $event = \local_exams\event\header_missing::create($eventparams);// ... code that may add some record snapshots
            $event->trigger();
            return  $this->errors;
        }

        if (!in_array('examinermustfillevaluationformafterexamsubmission', $this->columnsmapping)) {
            $this->errors[] = get_string('evaluationformaftersubmission_missing', 'local_exams');
            $eventparams = array('context' => \context_system::instance(),'other'=>array('name'=>'examinermustfillevaluationformafterexamsubmission'));
            $event = \local_exams\event\header_missing::create($eventparams);// ... code that may add some record snapshots
            $event->trigger();
            return  $this->errors;
        }

        if (!in_array('notifytheexaminerbeforeexamendsby', $this->columnsmapping)) {
            $this->errors[] = get_string('beforeexamsendby_missing', 'local_exams');
            $eventparams = array('context' => \context_system::instance(),'other'=>array('name'=>'notifytheexaminerbeforeexamendsby'));
            $event = \local_exams\event\header_missing::create($eventparams);// ... code that may add some record snapshots
            $event->trigger();
            return  $this->errors;
        }



        return false;
    }

    private function required_fields_validations($excel,$option=0){
        global $DB;
        //print_r($excel->active);exit;
        $strings = new stdClass;
        $strings->excel_line_number = $this->excel_line_number;

        // -----------check Active Status-----------------------------------
        if (array_key_exists('active', $excel) ) {

            if (!is_numeric($excel->active) || $excel->active > 2) {
                echo '<div class="local_trainingprogram_sync_error">'.get_string('activestatusmissing','local_exams', $strings).'</div>'; 
                $this->errors[] =  get_string('activestatusmissing', 'local_exams',$strings);
                $this->mfields[] = 'Active';
                $this->errorcount++;

                $eventparams = array('context' => \context_system::instance(),'objectid'=>$strings->excel_line_number,'other'=>array('name'=>'Active'));
                $event = \local_exams\event\missing_field::create($eventparams);// ... code that may add some record snapshots
                $event->trigger();
            }
        }

        if (array_key_exists('profilestartdate', $excel)) {
         
             if ($excel->profilestartdate !=='' && !strtotime($excel->profilestartdate)) {
                echo '<div class="local_trainingprogram_sync_error">'.get_string('startdatenotvalid','local_exams', $strings).'</div>'; 
                $this->errors[] =  get_string('startdatenotvalid', 'local_exams',$strings);
                $this->mfields[] = 'Profile startdate';
                $this->errorcount++;

                $eventparams = array('context' => \context_system::instance(),'objectid'=>$strings->excel_line_number,'other'=>array('name'=>'Profile startdate not valid'));
                $event = \local_exams\event\profilefields_validation::create($eventparams);// ... code that may add some record snapshots
                $event->trigger();
            }
        }

        if (array_key_exists('profileenddate', $excel)) {
           
            if ($excel->profileenddate !=='' && !strtotime($excel->profileenddate)) {
                echo '<div class="local_trainingprogram_sync_error">'.get_string('enddatenotvalid','local_exams', $strings).'</div>'; 
                $this->errors[] =  get_string('enddatenotvalid', 'local_exams',$strings);
                $this->mfields[] = 'Profile enddate';
                $this->errorcount++;

                $eventparams = array('context' => \context_system::instance(),'objectid'=>$strings->excel_line_number,'other'=>array('name'=>'Profile enddate not valid'));
                $event = \local_exams\event\profilefields_validation::create($eventparams);// ... code that may add some record snapshots
                $event->trigger();
            }
        }

        // -----------check Published Status-----------------------------------
       if (array_key_exists('published', $excel) ) {
            if (!is_numeric($excel->active) || $excel->active > 2) {
                echo '<div class="local_trainingprogram_sync_error">'.get_string('publishedstatusmissing','local_exams', $strings).'</div>'; 
                $this->errors[] =  get_string('publishedstatusmissing', 'local_exams',$strings);
                $this->mfields[] = 'Published';
                $this->errorcount++;

                $eventparams = array('context' => \context_system::instance(),'objectid'=>$strings->excel_line_number,'other'=>array('name'=>'Published'));
                $event = \local_exams\event\missing_field::create($eventparams);// ... code that may add some record snapshots
                $event->trigger();


            }
        }

        // -----------check DecisionFlow-----------------------------------
        if (array_key_exists('decisionflow', $excel) ) {
            if ($excel->decisionflow == 0 || $excel->decisionflow > 4) {
                echo '<div class="local_trainingprogram_sync_error">'.get_string('decisionflowmissing','local_exams', $strings).'</div>'; 
                $this->errors[] =  get_string('decisionflowmissing', 'local_exams',$strings);
                $this->mfields[] = 'DecisionFlow';
                $this->errorcount++;

                $eventparams = array('context' => \context_system::instance(),'objectid'=>$strings->excel_line_number,'other'=>array('name'=>'DecisionFlow'));
                $event = \local_exams\event\missing_field::create($eventparams);// ... code that may add some record snapshots
                $event->trigger();

            }
        }

        // -----------check Language-----------------------------------
        if (array_key_exists('language', $excel) ) {
            if (trim($excel->language) > 1) {
                echo '<div class="local_trainingprogram_sync_error">'.get_string('languagecantexceedone','local_exams', $strings).'</div>'; 
                $this->errors[] =  get_string('languagecantexceedone', 'local_exams',$strings);
                $this->mfields[] = 'Language';
                $this->errorcount++;

                $eventparams = array('context' => \context_system::instance(),'objectid'=>$strings->excel_line_number,'other'=>array('name'=>'Language cannot be more than 1'));
                $event = \local_exams\event\profilefields_validation::create($eventparams);// ... code that may add some record snapshots
                $event->trigger();
            }
        }

        // -----------check Profile Duration-----------------------------------
        if (array_key_exists('profileduration', $excel) ) {
            if (empty($excel->profileduration)) {
                echo '<div class="local_trainingprogram_sync_error">'.get_string('profiledurationmissing','local_exams', $strings).'</div>'; 
                $this->errors[] =  get_string('profiledurationmissing', 'local_exams',$strings);
                $this->mfields[] = 'ProfileDuration';
                $this->errorcount++;

                $eventparams = array('context' => \context_system::instance(),'objectid'=>$strings->excel_line_number,'other'=>array('name'=>'ProfileDuration'));
                $event = \local_exams\event\missing_field::create($eventparams);// ... code that may add some record snapshots
                $event->trigger();
            }
        }

        // -----------check NumberOfQuestions-----------------------------------
        if (array_key_exists('numberofquestions', $excel) ) {
            if (empty($excel->numberofquestions)) {
                echo '<div class="local_trainingprogram_sync_error">'.get_string('numberofquestionsmissing','local_exams', $strings).'</div>'; 
                $this->errors[] =  get_string('numberofquestionsmissing', 'local_exams',$strings);
                $this->mfields[] = 'NumberOfQuestions';
                $this->errorcount++;

                $eventparams = array('context' => \context_system::instance(),'objectid'=>$strings->excel_line_number,'other'=>array('name'=>'NumberOfQuestions'));
                $event = \local_exams\event\missing_field::create($eventparams);// ... code that may add some record snapshots
                $event->trigger();
            }
        }

        // -----------check Audience-----------------------------------
        if (array_key_exists('audience', $excel) ) {
            if (empty($excel->audience)) {
                echo '<div class="local_trainingprogram_sync_error">'.get_string('audiencemissing','local_exams', $strings).'</div>'; 
                $this->errors[] =  get_string('audiencemissing', 'local_exams',$strings);
                $this->mfields[] = 'Audience';
                $this->errorcount++;

                $eventparams = array('context' => \context_system::instance(),'objectid'=>$strings->excel_line_number,'other'=>array('name'=>'Audience'));
                $event = \local_exams\event\missing_field::create($eventparams);// ... code that may add some record snapshots
                $event->trigger();
            }
        }

        // -----------check NDA-----------------------------------
        if (array_key_exists('nda', $excel) ) {
            if (empty($excel->nda)) {
                echo '<div class="local_trainingprogram_sync_error">'.get_string('ndamissing','local_exams', $strings).'</div>'; 
                $this->errors[] =  get_string('ndamissing', 'local_exams',$strings);
                $this->mfields[] = 'NDA';
                $this->errorcount++;

                $eventparams = array('context' => \context_system::instance(),'objectid'=>$strings->excel_line_number,'other'=>array('name'=>'NDA'));
                $event = \local_exams\event\missing_field::create($eventparams);// ... code that may add some record snapshots
                $event->trigger();
            }
        }

        // -----------check Instructions-----------------------------------
        if (array_key_exists('instructions', $excel) ) {
            if (empty($excel->instructions)) {
                echo '<div class="local_trainingprogram_sync_error">'.get_string('instructionsmissing','local_exams', $strings).'</div>'; 
                $this->errors[] =  get_string('instructionsmissing', 'local_exams',$strings);
                $this->mfields[] = 'Instructions';
                $this->errorcount++;

                $eventparams = array('context' => \context_system::instance(),'objectid'=>$strings->excel_line_number,'other'=>array('name'=>'Instructions'));
                $event = \local_exams\event\missing_field::create($eventparams);// ... code that may add some record snapshots
                $event->trigger();
            }
        }

        // -----------check PassingPercentage-----------------------------------
        if (array_key_exists('passingPercentage', $excel) ) {
            if (empty($excel->passingPercentage)) {
                echo '<div class="local_trainingprogram_sync_error">'.get_string('passingpercentagemissing','local_exams', $strings).'</div>'; 
                $this->errors[] =  get_string('passingpercentagemissing', 'local_exams',$strings);
                $this->mfields[] = 'PassingPercentage';
                $this->errorcount++;

                $eventparams = array('context' => \context_system::instance(),'objectid'=>$strings->excel_line_number,'other'=>array('name'=>'PassingPercentage'));
                $event = \local_exams\event\missing_field::create($eventparams);// ... code that may add some record snapshots
                $event->trigger();
            }
        }

        if (array_key_exists('examcode', $excel) ) {
            $checkexamcodeexist = $DB->get_record('local_exams',['code'=>$excel->examcode]);
            if(empty($checkexamcodeexist))
            {
                echo '<div class="local_trainingprogram_sync_error">'.get_string('examcodesdoesnotexist','local_exams', $strings).'</div>'; 
                $this->errors[] =  get_string('examcodesdoesnotexist', 'local_exams',$strings);
                $this->mfields[] = 'ExamCode';
                $this->errorcount++;

                $eventparams = array('context' => \context_system::instance(),'objectid'=>$strings->excel_line_number,'other'=>array('name'=>'ExamCode does not exist'));
                $event = \local_exams\event\profilefields_validation::create($eventparams);// ... code that may add some record snapshots
                $event->trigger();
            }
        }
        
        
    
    } // end of required_fields_validations function
    /**
     * @method get_program_file
     * @todo Returns the uploaded file if it is present.
     * @param int $draftid
     * @return stored_file|null
     */

    /**
     * Displays the preview of the uploaded file
     */

     public function get_profile_file($draftid) {
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
    protected function preview_uploaded() {
        global $OUTPUT;
        $return = '';
        $return .= $OUTPUT->notification(get_string('uploadprogramssheet', 'local_trainingprogram'),'info');
        $return .= html_writer::start_tag('div', array('class'=> 'w-100 mt-5')).$OUTPUT->continue_button(new moodle_url('/local/exams/profileupload.php')).html_writer::end_tag('div');
        return $return;
    }
    
}

