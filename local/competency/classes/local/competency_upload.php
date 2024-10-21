<?php
namespace local_competency\local;
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
use local_competency\competency;

class competency_upload  extends competency{

    protected $columns;

    protected $columnsmapping = array();

    protected $competencyallowedcolumns = array('OLD_ID', 'EN_Name', 'AR_Name', 'Description','TYPE','CODE','PARENT_CODE','EXAM_CODE','PROGRAM_CODE','QUESTION_CODE','LEVEL');


    protected $competencytypes = array(
            'T' => self::TECHNICALCOMPETENCIES,
            'C' => self::CORECOMPETENCIES ,
            'B' => self::BEHAVIORALCOMPETENCIES
            );

    protected $competencylevels = array(
            'L1' => self::LEVEL1,
            'L2' => self::LEVEL2,
            'L3' => self::LEVEL3,
            'L4' => self::LEVEL4,
            'L5' => self::LEVEL5
            );

    protected $competencylevelnames = array(
            self::LEVEL1 => 'Level 1',
            self::LEVEL2 => 'Level 2',
            self::LEVEL3 => 'Level 3',
            self::LEVEL4 => 'Level 4',
            self::LEVEL5 => 'Level 5',
            );

      protected $uploadsheetcolumntypes = array(
            'Competency' => 'Competency',
            'PerformanceCriteria' => 'PerformanceCriteria',
            'PerformanceKPI' => 'PerformanceKPI',
            'PerformanceObjective' => 'PerformanceObjective'
            );
    /**
     * @method upload_competency_file file
     * @todo To process the uploaded CSV file and return the data
     * @param stored_file $file
     * @param string $encoding
     * @param string $delimiter
     * @param context $defaultcontext
     * @return array
     */

    public function upload_competency_file($file, $defaultcontext) {
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
        $uploadid = csv_import_reader::get_new_iid('competencyupload');
        $cir = new csv_import_reader($uploadid, 'competencyupload');

        /**
         * Actual upload starts from here
         */
        $readcount = $cir->load_csv_content($content, 'utf-8', 'comma');

        unset($content);
        if (!$readcount ) {
            throw new moodle_exception('csvloaderror', 'error',$PAGE->url,  $cir->get_error());
        }
        if(!is_siteadmin() && !has_capability('local/competency:canbulkuploadcompetency', $systemcontext)) {
            throw new moodle_exception('youdonthavepermissiontouploaddata', 'local_competency');
        }

        $this->columns = $cir->get_columns();


        $column_validation = $this->validate_columns();
        if(is_array($column_validation) && count($column_validation) > 0){
            $string = $column_validation[0];
            $return =  '<div class="local_competency_sync_error">'.get_string('validsheet','local_competency',$string).'</div>'; 
            $return .= html_writer::start_tag('div', array('class'=> 'w-100 pull-left')).$OUTPUT->continue_button(new moodle_url('/local/competency/uploadcompetency.php')).html_writer::end_tag('div');
           echo $return;
           return false;
        }


        foreach ($this->columns as $i => $columnname) {
            $columnnamelower = preg_replace('/ /', '', $columnname);
            if (in_array($columnnamelower, $this->competencyallowedcolumns)) {
                $this->columnsmapping[$i] = $columnnamelower;
            }else {
                $this->columnsmapping[] = $columnname;
            }
        }
        
        $cir->init();
        $rownum = 0;
        $progress       = 0;
        $data = array();
        $linenum = 1;   
        $errorcount= 0;

        $return='';

        $competencyrow=array();

        $performancecriteriarow=array();

        $performancekpirow=array();

        while ($row = $cir->next()) {


           // if($linenum > 8){

                foreach ($row as $i => $value) {

                    if (!isset($this->columnsmapping[$i])) {
                        continue;
                    }

                    $column=$this->columnsmapping[$i];
                    $masterdata->$column = $value;

                }

                $masterdata->excel_line_number=$linenum+1;  
                $this->data[]=$masterdata;  
                $this->success = array();
                $this->successcount=0;
                $this->errors = array();
                $this->errorcount=0;
                $this->warnings = array();
                $this->mfields = array();
                $this->wmfields = array();
                $this->excel_line_number = $linenum;

                $this->competencyrow = $competencyrow;
                $this->performancecriteriarow = $performancecriteriarow;
                $this->performancekpirow = $performancekpirow;

                $this->required_fields_validations($masterdata,$errorcount,$competencyrow,$performancecriteriarow,$performancekpirow);

                $competencyrow = array_merge($competencyrow,$this->competencyrow);
                $performancecriteriarow = array_merge($performancecriteriarow,$this->performancecriteriarow);
                $performancekpirow = array_merge($performancekpirow,$this->performancekpirow);

            // }

            $linenum++;
            
		}
        $return.= html_writer::start_tag('div', array('class'=> 'w-100 pull-left')).$OUTPUT->continue_button(new moodle_url('/local/competency/uploadcompetency.php')).html_writer::end_tag('div');
        echo $return;
    }
    private function validate_columns() {
        global $DB;
        foreach ($this->columns as $i => $columnname) {

            if (in_array($columnname, $this->competencyallowedcolumns)) {
                $this->columnsmapping[$i] = $columnname;
            }
        }

        if (!in_array('OLD_ID', $this->columnsmapping)) {
            $this->errors[] = get_string('oldidmissing', 'local_competency');
            $eventparams = array('context' => \context_system::instance(),'other'=>array('name'=>'OLD_ID'));
            $event = \local_competency\event\header_missing::create($eventparams);// ... code that may add some record snapshots
            $event->trigger();
            return  $this->errors;
        }
        if (!in_array('EN_Name', $this->columnsmapping)) {
            $this->errors[] = get_string('nameenglishmissing', 'local_competency');
            $eventparams = array('context' => \context_system::instance(),'other'=>array('name'=>'EN_Name'));
            $event = \local_competency\event\header_missing::create($eventparams);// ... code that may add some record snapshots
            $event->trigger();
            return  $this->errors;
        }

        if (!in_array('AR_Name', $this->columnsmapping)) {
            $this->errors[] = get_string('namearabicmissing', 'local_competency');
            $eventparams = array('context' => \context_system::instance(),'other'=>array('name'=>'AR_Name'));
            $event = \local_competency\event\header_missing::create($eventparams);// ... code that may add some record snapshots
            $event->trigger();
            return  $this->errors;
        }

        if (!in_array('Description', $this->columnsmapping)) {
            
            $this->errors[] = get_string('descriptionmissing', 'local_competency');
            $eventparams = array('context' => \context_system::instance(),'other'=>array('name'=>'Description'));
            $event = \local_competency\event\header_missing::create($eventparams);// ... code that may add some record snapshots
            $event->trigger();
            return  $this->errors;
        }
        if (!in_array('TYPE', $this->columnsmapping)) {
           
            $this->errors[] = get_string('typemissing', 'local_competency');
            $eventparams = array('context' => \context_system::instance(),'other'=>array('name'=>'TYPE'));
            $event = \local_competency\event\header_missing::create($eventparams);// ... code that may add some record snapshots
            $event->trigger();
            return  $this->errors;
        }

        if (!in_array('CODE', $this->columnsmapping)) {

            $this->errors[] = get_string('codemissing', 'local_competency');
            $eventparams = array('context' => \context_system::instance(),'other'=>array('name'=>'CODE'));
            $event = \local_competency\event\header_missing::create($eventparams);// ... code that may add some record snapshots
            $event->trigger();
            return  $this->errors;
        }

        if (!in_array('PARENT_CODE', $this->columnsmapping)) {

            $this->errors[] = get_string('parentcodemissing', 'local_competency');
            $eventparams = array('context' => \context_system::instance(),'other'=>array('name'=>'PARENT_CODE'));
            $event = \local_competency\event\header_missing::create($eventparams);// ... code that may add some record snapshots
            $event->trigger();
            return  $this->errors;
        }

        if (!in_array('EXAM_CODE', $this->columnsmapping)) { 
            $this->errors[] = get_string('examcodemissing', 'local_competency');
            $eventparams = array('context' => \context_system::instance(),'other'=>array('name'=>'EXAM_CODE'));
            $event = \local_competency\event\header_missing::create($eventparams);// ... code that may add some record snapshots
            $event->trigger();
            return  $this->errors;
        }

        if (!in_array('PROGRAM_CODE', $this->columnsmapping)) { 
            $this->errors[] = get_string('programcodemissing', 'local_competency');
            $eventparams = array('context' => \context_system::instance(),'other'=>array('name'=>'PROGRAM_CODE'));
            $event = \local_competency\event\header_missing::create($eventparams);// ... code that may add some record snapshots
            $event->trigger();
            return  $this->errors;
        }

        if (!in_array('QUESTION_CODE', $this->columnsmapping)) { 
            $this->errors[] = get_string('questioncodemissing', 'local_competency');
            $eventparams = array('context' => \context_system::instance(),'other'=>array('name'=>'QUESTION_CODE'));
            $event = \local_competency\event\header_missing::create($eventparams);// ... code that may add some record snapshots
            $event->trigger();
            return  $this->errors;
        }

        if (!in_array('LEVEL', $this->columnsmapping)) {
           
            $this->errors[] = get_string('levelmissing', 'local_competency');
            $eventparams = array('context' => \context_system::instance(),'other'=>array('name'=>'LEVEL'));
            $event = \local_competency\event\header_missing::create($eventparams);// ... code that may add some record snapshots
            $event->trigger();
            return  $this->errors;
        }

        return false;
    }

    private function required_fields_validations($excel,$errorcount,$competencyrow,$performancecriteriarow,$performancekpirow){
        global $DB;
        $strings = new stdClass;
        $strings->excel_line_number = $this->excel_line_number+1;
        //-----------check competency oldid-----------------------------------
        if (array_key_exists('OLD_ID', $excel) ) {

            if (empty($excel->OLD_ID)) {

                echo '<div class="local_competency_sync_error">'.get_string('oldidempty','local_competency', $excel).'</div>'; 
                $this->errors[] =  get_string('oldidempty', 'local_competency',$excel);
                $this->mfields[] = 'OLD_ID';
                $this->errorcount++;
                $eventparams = array('context' => \context_system::instance(),'objectid'=>$strings->excel_line_number,'other'=>array('name'=>'OLD_ID'));
                $event = \local_competency\event\missing_field::create($eventparams);// ... code that may add some record snapshots
                $event->trigger();

                return $errorcount++;

            }
        }

        //-----------check competency name english-----------------------------------
        if (array_key_exists('EN_Name', $excel) ) {

            if (empty($excel->EN_Name)) {
                echo '<div class="local_competency_sync_error">'.get_string('nameenglishempty','local_competency', $excel).'</div>'; 
                $this->errors[] =  get_string('nameenglishempty', 'local_competency',$excel);
                $this->mfields[] = 'EN_Name';
                $this->errorcount++;
                $eventparams = array('context' => \context_system::instance(),'objectid'=>$strings->excel_line_number,'other'=>array('name'=>'EN_Name'));
                $event = \local_competency\event\missing_field::create($eventparams);// ... code that may add some record snapshots
                $event->trigger();
                 return $errorcount++;

            }
        }

        //-----------check competency name arabic-----------------------------------
        if (array_key_exists('AR_Name', $excel)) {

            if (empty($excel->AR_Name)) {

                echo '<div class="local_competency_sync_error">'.get_string('namearabicempty','local_competency', $excel).'</div>'; 
                $this->errors[] =  get_string('namearabicempty', 'local_competency',$excel);
                $this->mfields[] = 'AR_Name';
                $this->errorcount++;
                $eventparams = array('context' => \context_system::instance(),'objectid'=>$strings->excel_line_number,'other'=>array('name'=>'AR_Name'));
                $event = \local_competency\event\missing_field::create($eventparams);// ... code that may add some record snapshots
                $event->trigger();
                 return $errorcount++;


            }
        }
            
        //-------- check competency description-------------------------------------
        if ( array_key_exists('Description', $excel) && $excel->TYPE == 'Competency' ) {

            if (empty($excel->Description)) {

                echo '<div class="local_competency_sync_error">'.get_string('descriptionempty','local_competency', $excel).'</div>'; 

                $this->warnings[] = get_string('descriptionempty','local_competency', $excel);
                $this->wmfields[] = 'Description';
                 $this->errorcount++;
                return $errorcount++;
             


            }
        }


        //-------- check competency type-------------------------------------
        if (array_key_exists('TYPE', $excel) ) {

            if (empty($excel->TYPE)) {

                echo '<div class="local_competency_sync_error">'.get_string('typeempty','local_competency', $excel).'</div>'; 
                $this->errors[] = get_string('typeempty','local_competency', $excel);
                $this->mfields[] = 'TYPE';
                $this->errorcount++;
                $eventparams = array('context' => \context_system::instance(),'objectid'=>$strings->excel_line_number,'other'=>array('name'=>'TYPE'));
                $event = \local_competency\event\missing_field::create($eventparams);// ... code that may add some record snapshots
                $event->trigger();
                return $errorcount++;


            }else{

                $uploadsheetcolumntypecheck=$this->check_uploadsheet_column_typecheck($excel->TYPE);

                 if(!$uploadsheetcolumntypecheck){

                    echo '<div class="local_competency_sync_error">'.get_string('typenotmatchedwithuploadedsheet','local_competency', $excel).'</div>'; 
                    $this->errors[] = get_string('typenotmatchedwithuploadedsheet','local_competency', $excel);
                    $this->mfields[] = 'TYPE';
                    $this->errorcount++;
                    $eventparams = array('context' => \context_system::instance(),'objectid'=>$strings->excel_line_number,'other'=>array('name'=>'TYPE'));
                    $event = \local_competency\event\invalid_field::create($eventparams);// ... code that may add some record snapshots
                    $event->trigger();
                    return $errorcount++;

                 }else{

                    $excel->TYPE=$uploadsheetcolumntypecheck;

                 }

            }
        }

        //-------- check competency type-------------------------------------
        if ( array_key_exists('LEVEL', $excel) && $excel->TYPE == 'Competency') {

            if (empty($excel->LEVEL)) {

                echo '<div class="local_competency_sync_error">'.get_string('levelempty','local_competency', $excel).'</div>'; 
                $this->errors[] = get_string('levelempty','local_competency', $excel);
                $this->mfields[] = 'LEVEL';
                $this->errorcount++;
                $eventparams = array('context' => \context_system::instance(),'objectid'=>$strings->excel_line_number,'other'=>array('name'=>'LEVEL'));
                $event = \local_competency\event\missing_field::create($eventparams);// ... code that may add some record snapshots
                $event->trigger();
                return $errorcount++;
            }else{

                $levelcheck=$this->check_competency_levelcheck($excel->LEVEL);

                if(!empty($levelcheck['nonexistinglevels'])){

                    $excel->somelevels=implode(',',array_keys($levelcheck['nonexistinglevels']));

                    echo '<div class="local_competency_sync_error">'.get_string('somelevelnotmatchedwithuploadedsheet','local_competency', $excel).'</div>';

                    $this->warnings[] = get_string('somelevelnotmatchedwithuploadedsheet','local_competency', $excel);
                    $this->wmfields[] = 'LEVEL';
                    return $errorcount++;

                }

                if(empty($levelcheck['existinglevels'])){

                    echo '<div class="local_competency_sync_error">'.get_string('levelnotmatchedwithuploadedsheet','local_competency', $excel).'</div>'; 
                    $this->errors[] = get_string('levelnotmatchedwithuploadedsheet','local_competency', $excel);
                    $this->mfields[] = 'LEVEL';
                    $this->errorcount++;
                    $eventparams = array('context' => \context_system::instance(),'objectid'=>$strings->excel_line_number,'other'=>array('name'=>'LEVEL'));
                    $event = \local_competency\event\missing_field::create($eventparams);// ... code that may add some record snapshots
                    $event->trigger();
                    return $errorcount++;

                }else{

                    $excel->LEVEL=implode(',',$levelcheck['existinglevels']);
                }

            }
        }

        //-----------check competency code-----------------------------------
        if (array_key_exists('CODE', $excel) ) {

            if (empty($excel->CODE)) {

                echo '<div class="local_competency_sync_error">'.get_string('codeempty','local_competency', $excel).'</div>'; 
                $this->errors[] =  get_string('codeempty', 'local_competency',$excel);
                $this->mfields[] = 'CODE';
                $this->errorcount++;
                $eventparams = array('context' => \context_system::instance(),'objectid'=>$strings->excel_line_number,'other'=>array('name'=>'CODE'));
                $event = \local_competency\event\missing_field::create($eventparams);// ... code that may add some record snapshots
                $event->trigger();
                 return $errorcount++;


            }
        }

        //-----------check competency code-----------------------------------
        if (array_key_exists('PARENT_CODE', $excel) ) {

            if (empty($excel->PARENT_CODE)) {

                echo '<div class="local_competency_sync_error">'.get_string('parentcodeempty','local_competency', $excel).'</div>'; 
                $this->errors[] =  get_string('parentcodeempty', 'local_competency',$excel);
                $this->mfields[] = 'PARENT_CODE';
                $this->errorcount++;
                $eventparams = array('context' => \context_system::instance(),'objectid'=>$strings->excel_line_number,'other'=>array('name'=>'PARENT_CODE'));
                $event = \local_competency\event\missing_field::create($eventparams);// ... code that may add some record snapshots
                $event->trigger();
                 return $errorcount++;

            }elseif($excel->TYPE == 'Competency'){

                $this->competencyrow[$excel->CODE]=clone $excel;

                $typecheck=$this->check_competency_typecheck($excel->PARENT_CODE);

                 if(!$typecheck){

                    echo '<div class="local_competency_sync_error">'.get_string('parentcodetypenotmatchedwithuploadedsheet','local_competency', $excel).'</div>'; 

                    $this->warnings[] = get_string('parentcodetypenotmatchedwithuploadedsheet','local_competency', $excel);
                    $this->wmfields[] = 'PARENT_CODE';
                     $this->errorcount++;
                     return $errorcount++;

                }else{

                    $excel->PARENT_CODE=$typecheck;

                }

            }elseif($excel->TYPE == 'PerformanceCriteria'){

                $excel->typecompetencyid=$this->get_competency($excel->PARENT_CODE);

                 if(!$excel->typecompetencyid){

                    echo '<div class="local_competency_sync_error">'.get_string('parentcodecriterianotmatchedwithrecords','local_competency', $excel).'</div>'; 
                    $this->errors[] = get_string('parentcodecriterianotmatchedwithrecords','local_competency', $excel);
                    $this->mfields[] = 'PARENT_CODE';
                    $this->errorcount++;
                    $eventparams = array('context' => \context_system::instance(),'objectid'=>$strings->excel_line_number,'other'=>array('name'=>'PARENT_CODE'));
                    $event = \local_competency\event\invalid_field::create($eventparams);// ... code that may add some record snapshots
                    $event->trigger();
                    return $errorcount++;

                }else{

                    $excel->typecompetencyid =$excel->typecompetencyid;

                    $excel->criterianame =$excel->EN_Name;

                    $excel->criterianamearabic =$excel->AR_Name;

                    $this->performancecriteriarow[$excel->CODE]= clone $excel;

                    
                }

            }elseif($excel->TYPE == 'PerformanceKPI'){

                $criteriacode=$performancecriteriarow[$excel->PARENT_CODE];

                if(!$criteriacode){

                    echo '<div class="local_competency_sync_error">'.get_string('parentcodekpinotmatchedwithuploadedsheet','local_competency', $excel).'</div>'; 

                    $this->errors[] = get_string('parentcodekpinotmatchedwithuploadedsheet','local_competency', $excel);
                    $this->mfields[] = 'PARENT_CODE';
                    $this->errorcount++;
                    $eventparams = array('context' => \context_system::instance(),'objectid'=>$strings->excel_line_number,'other'=>array('name'=>'PARENT_CODE'));
                    $event = \local_competency\event\invalid_field::create($eventparams);// ... code that may add some record snapshots
                    $event->trigger();
                    return $errorcount++;

                }else{

                    $excel->criterianame =$criteriacode->criterianame;

                    $excel->criterianamearabic =$criteriacode->criterianamearabic;

                    $excel->kpiname =$excel->EN_Name;

                    $excel->kpinamearabic =$excel->AR_Name;

                    $excel->typecompetencyid =$criteriacode->typecompetencyid;

                    $this->performancekpirow[$excel->CODE]= clone $excel;

                } 

            }elseif($excel->TYPE == 'PerformanceObjective'){


                $kpicode=$performancekpirow[$excel->PARENT_CODE];

                if($kpicode){


                        $excel->criterianame =$kpicode->criterianame;

                        $excel->criterianamearabic =$kpicode->criterianamearabic;

                        $excel->kpiname =$kpicode->kpiname;

                        $excel->kpinamearabic =$kpicode->kpinamearabic;

                        $excel->objectiveid =$excel->EN_Name;

                        $excel->objectiveidarabic =$excel->AR_Name;


                        $excel->typecompetencypcid=$this->get_competencypc($kpicode->typecompetencyid, $excel->criterianame,$excel->kpiname,$excel->objectiveid);
                
                }else{

                    echo '<div class="local_competency_sync_error">'.get_string('parentcodeobjectivenotmatchedwithuploadedsheet','local_competency', $excel).'</div>'; 

                    $this->errors[] = get_string('parentcodeobjectivenotmatchedwithuploadedsheet','local_competency', $excel);
                    $this->mfields[] = 'PARENT_CODE';
                    $this->errorcount++;
                    $eventparams = array('context' => \context_system::instance(),'objectid'=>$strings->excel_line_number,'other'=>array('name'=>'PARENT_CODE'));
                    $event = \local_competency\event\invalid_field::create($eventparams);// ... code that may add some record snapshots
                    $event->trigger();
                    return $errorcount++;

                }

            }
        }

        if (count($this->errors) > 0) { 

                return $errorcount++;


        }elseif($excel->TYPE == 'Competency'){


            $excel->code = $excel->CODE;
            $excel->name = $excel->EN_Name;
            $excel->arabicname = $excel->AR_Name;
            $excel->description= array('text'=>$excel->Description);
            $excel->type =$excel->PARENT_CODE;
            $excel->level =explode(',',$excel->LEVEL);
            $excel->id = $this->get_competency($excel->CODE);

            if($excel->id > 0){

                $is_competence_mapped = competency::is_competence_mapped($excel->id);


                if($is_competence_mapped) {

                    $record = $DB->get_record('local_competencies',array('id'=>$excel->id));

                    $excel->existinglevel=$record->level;

                    $excel->existingtype=$record->type;

                    echo '<div class="local_competency_sync_error">'.get_string('iscompetencematchedwithrecords','local_competency', $excel).'</div>'; 

                    $this->errors[] = get_string('iscompetencematchedwithrecords','local_competency', $excel);
                    $this->mfields[] = 'CODE';
                    $this->errorcount++;
                    $eventparams = array('context' => \context_system::instance(),'objectid'=>$strings->excel_line_number,'other'=>array('name'=>'CODE'));
                    $event = \local_competency\event\invalid_field::create($eventparams);// ... code that may add some record snapshots
                    $event->trigger();
                    return $errorcount++;

                }else{

                    $competencycrud=get_string('successcompetencyupdated','local_competency', $excel);

                }

           
            }else{

                $competencycrud=get_string('sucesscompetencycreated','local_competency', $excel);
            }

            if (count($this->errors) > 0) { 

                return $errorcount++;

            }else{

                $excel->oldid =$excel->OLD_ID;

                $excel->competencyid =self::competency_datasubmit($excel);


                echo $competencycrud; 

                $this->success[]=$competencycrud;

                $this->successcount++;


                 //-----------check competency Objective Exams-----------------------------------
            if (array_key_exists('EXAM_CODE', $excel) ) {

                    if (empty($excel->EXAM_CODE)) {

                        echo '<div class="local_competency_sync_error">'.get_string('examcodeempty','local_competency', $excel).'</div>'; 

                        $this->warnings[] = get_string('examcodeempty','local_competency', $excel);
                        $this->wmfields[] = 'EXAM_CODE';
                         $this->errorcount++;

                        return $errorcount++;
                 

                     }else {

                        $examsexists =  $this->check_competency_objexams($excel->EXAM_CODE);

                        if(!$examsexists){

                           echo '<div class="local_competency_sync_error">'.get_string('examcodenotmatchedwithrecords','local_competency', $excel).'</div>'; 
                            $this->warnings[] = get_string('examcodenotmatchedwithrecords','local_competency', $excel);
                            $this->wmfields[] = 'EXAM_CODE';
                            $this->errorcount++;
                            return $errorcount++;

                        }

                        // if(!empty($exams['nonexistingexams'])){

                        //     $excel->someexams=implode(',',array_keys($exams['nonexistingexams']));

                        //     echo '<div class="local_competency_sync_error">'.get_string('someexamcodenotmatchedwithrecords','local_competency', $excel).'</div>'; 

                        //     $this->warnings[] = get_string('someexamcodenotmatchedwithrecords','local_competency', $excel);
                        //     $this->wmfields[] = 'EXAM_CODE';
                        //     $this->errorcount++;
                        //     return $errorcount++;

                        // }   
                }      
                if(!$examsexists) { 

                    $errorcount++;

                }else{

          
                    $excel->id =0;

                    $excel->oldid =$excel->OLD_ID;

                    $excel->objexams =$exams['existingexams'];

                    if(!empty($excel->EXAM_CODE)) {
                        $exams = explode(',',$excel->EXAM_CODE);
                        foreach ($exams as $examcode) {
                            $examcode=trim($examcode);
                            $examid = $DB->get_field_sql("SELECT id FROM {local_exams} WHERE code = '$examcode' ");

                            if($examid) {
                              $mapping  = self::map_competency($excel->LEVEL,$excel->PARENT_CODE,$excel->competencyid,$examid,$type ='exam');

                               if($mapping) {

                                   $competencypccontentexamscrud=get_string('sucesscompetencypccontentexamscreated','local_competency', $excel);
                    
                                    echo $competencypccontentexamscrud; 

                                    $this->success[]=$competencypccontentexamscrud;

                                     $this->$successcount++;
                               }

                            }
                            
                        }

                    }

                }
            }
      
            //-----------check competency Objective Training Programs-----------------------------------
            if (array_key_exists('PROGRAM_CODE', $excel) ) {


                if (empty($excel->PROGRAM_CODE)) {

                    echo '<div class="local_competency_sync_error">'.get_string('programcodeempty','local_competency', $excel).'</div>'; 
                    $this->errors[] =  get_string('programcodeempty', 'local_competency',$excel);
                    $this->mfields[] = 'PROGRAM_CODE';
                    $this->errorcount++;
                    $eventparams = array('context' => \context_system::instance(),'objectid'=>$strings->excel_line_number,'other'=>array('name'=>'PROGRAM_CODE'));
                    $event = \local_competency\event\missing_field::create($eventparams);// ... code that may add some record snapshots
                    $event->trigger();
                     return $errorcount++;

                }else {

                    $programexists =  $this->check_competency_objtrainingprograms($excel->PROGRAM_CODE);




                     if(!$programexists){

                       echo '<div class="local_competency_sync_error">'.get_string('programcodenotmatchedwithrecords','local_competency', $excel).'</div>';

                        $this->warnings[] = get_string('programcodenotmatchedwithrecords','local_competency', $excel);
                        $this->wmfields[] = 'PROGRAM_CODE';
                         $this->errorcount++;
                         return $errorcount++;

                    }
                    // if(!empty($trainingprograms['nonexistingprograms'])){

                    //     $excel->sometrainingprograms=implode(',',array_keys($trainingprograms['nonexistingprograms']));

                    //     echo '<div class="local_competency_sync_error">'.get_string('someprogramcodenotmatchedwithrecords','local_competency', $excel).'</div>'; 

                    //     $this->warnings[] = get_string('someprogramcodenotmatchedwithrecords','local_competency', $excel);
                    //     $this->wmfields[] = 'PROGRAM_CODE';
                    //      $this->errorcount++;
                    //      return $errorcount++;

                    // }    
                }        
                if(!$programexists) { 

                   $errorcount++;

                }else{

                    $excel->id =0;

                    $excel->oldid =$excel->OLD_ID;

                    $excel->objtrainingprograms =$trainingprograms['existingprograms'];

                    if(!empty($excel->PROGRAM_CODE)) {


                        $programs = explode(',',$excel->PROGRAM_CODE);

                        foreach ($programs as $programcode) {
                            $programcode=trim($programcode);
                            $programid = $DB->get_field_sql("SELECT id FROM {local_trainingprogram} WHERE code = '$programcode' ");

                            if($programid) {
                              $mapping  = self::map_competency($excel->LEVEL,$excel->PARENT_CODE,$excel->competencyid,$programid,$type ='program');

                               if($mapping) {

                                   $competencypccontentexamscrud=get_string('sucesscompetencypccontentprogramscreated','local_competency', $excel);
                    
                                    echo $competencypccontentexamscrud; 

                                    $this->success[]=$competencypccontentexamscrud;

                                    $this->successcount++;
                               }

                            }
                            
                        }

                    }

    

                }
            }

            //-----------check competency Objective Questions-----------------------------------
            // if (array_key_exists('QUESTION_CODE', $excel) ) {

            //     if (empty($excel->QUESTION_CODE)) {

            //         echo '<div class="local_competency_sync_error">'.get_string('questioncodeempty','local_competency', $excel).'</div>'; 
            //         $this->errors[] =  get_string('questioncodeempty', 'local_competency',$excel);
            //         $this->mfields[] = 'QUESTION_CODE';
            //         $this->errorcount++;
            //         $eventparams = array('context' => \context_system::instance(),'objectid'=>$strings->excel_line_number,'other'=>array('name'=>'QUESTION_CODE'));
            //         $event = \local_competency\event\missing_field::create($eventparams);// ... code that may add some record snapshots
            //         $event->trigger();
                    
            //          return $errorcount++;

            //     }else {

            //         $questions =  $this->check_competency_objquestions($excel->typecompetencyid,$excel->QUESTION_CODE);

            //         if(empty($questions['existingquestions'])){

            //            echo '<div class="local_competency_sync_error">'.get_string('questioncodenotmatchedwithrecords','local_competency', $excel).'</div>'; 

            //             $this->warnings[] = get_string('questioncodenotmatchedwithrecords','local_competency', $excel);
            //             $this->wmfields[] = 'QUESTION_CODE';
            //             $this->errorcount++;
            //              return $errorcount++;

            //         }
            //         if(!empty($questions['nonexistingquestions'])){

            //             $excel->somequestions=implode(',',array_keys($questions['nonexistingquestions']));

            //             echo '<div class="local_competency_sync_error">'.get_string('somequestioncodenotmatchedwithrecords','local_competency', $excel).'</div>'; 

            //             $this->warnings[] = get_string('somequestioncodenotmatchedwithrecords','local_competency', $excel);
            //             $this->wmfields[] = 'QUESTION_CODE';
            //             $this->errorcount++;
            //             return $errorcount++;

            //         }    
            //     }
            //     if(empty($questions['existingquestions'])) { 

            //         $errorcount++;

            //     }else{

            //         $excel->id =0;

            //         $excel->oldid =$excel->OLD_ID;

            //         $excel->objquestions = $questions['existingquestions'];

            //         // self::competencyobjective_datasubmit($excel);

            //         // $excel->QUESTION_CODE =implode(',',array_keys($questions['existingquestions']));

            //         $competencypccontentquestionscrud=get_string('sucesscompetencypccontentquestionscreated','local_competency', $excel);
                    

            //         echo $competencypccontentquestionscrud; 

            //         $this->success[]=$competencypccontentquestionscrud;

            //         $this->successcount++;

            //     }
            // }

            return $errorcount++;

           } 

        }elseif($excel->TYPE == 'PerformanceObjective'){


            $excel->id =0;

            $excel->competency = $excel->typecompetencyid;


            $excel->id =$this->get_competencypc($excel->competency, $excel->criterianame,$excel->kpiname,$excel->objectiveid);

            if($excel->id > 0){

                $competencypccrud=get_string('sucesscompetencypcupdated','local_competency', $excel);
       
            }else{

                $competencypccrud=get_string('sucesscompetencypccreated','local_competency', $excel);
            }

            $excel->oldid =$excel->OLD_ID;

            $excel->competencypc = self::competencypc_datasubmit($excel);
            

            echo $competencypccrud; 

            $this->success[]=$competencypccrud;

            $this->successcount++;
                   
        
        }

    } // end of required_fields_validations function
    /**
     * @method get_competency_file
     * @todo Returns the uploaded file if it is present.
     * @param int $draftid
     * @return stored_file|null
     */
    public function get_competency_file($draftid) {
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
        $return .= $OUTPUT->notification(get_string('uploadsectorsheet', 'local_competency'),'info');
        $return .= html_writer::start_tag('div', array('class'=> 'w-100 pull-left')).$OUTPUT->continue_button(new moodle_url('/local/sector/uploadsector.php')).html_writer::end_tag('div');
        return $return;
    }
    public function check_uploadsheet_column_typecheck($type) {
       global $DB,$USER;


       if(!empty($type)) {

            $type=trim($type);

            $types=$this->uploadsheetcolumntypes[$type];

            if($types){

                return $types;

            }else{

                return 0;

            }

       }else{

            return 0;
       }
        
    }
    public function check_competency_typecheck($parentcode) {
       global $DB,$USER;

       if(!empty($parentcode)) {

            $parentcode=trim($parentcode);

            $types=$this->competencytypes[$parentcode];

            if($types){

                return $types;

            }else{

                return 0;

            }

       }else{

            return 0;
       }
        
    }
    public function check_competency_levelcheck($levelscode) {
        global $DB,$USER;

        $existinglevels=array();

        $nonexistinglevels=array();

       if(!empty($levelscode)) {

            $levelscode = explode(',',$levelscode);

            foreach($levelscode as $levelcode){

                $levelcode=trim($levelcode);

                $level=$this->competencylevels[$levelcode];

                if($level){

                   $existinglevels[$levelcode]=$level;

                }else{

                  $nonexistinglevels[$levelcode]=$levelcode;

                }
            }
       }

       return compact('existinglevels', 'nonexistinglevels');
        
    }
    public function get_competency($code) {
       global $DB,$USER;

       if(!empty($code)) {

            $code=trim($code);

            $competencysql = "SELECT id FROM {local_competencies} WHERE code = '$code' ";
       
            $competency = $DB->get_field_sql($competencysql);

            return $competency;
       }else{

            return 0;
       }
        
    }
    public function get_competencypc($competency,$criterianame,$kpiname,$objectiveid) {
       global $DB,$USER;

       if(!empty($competency) && !empty($criterianame) && !empty($kpiname) && !empty($objectiveid)) {

    

            $sql = "SELECT id FROM {local_competency_pc}
                    WHERE competency = ? AND ".$DB->sql_compare_text('criterianame')." = ".$DB->sql_compare_text('?')." AND ".$DB->sql_compare_text('kpiname')." = ".$DB->sql_compare_text('?')." AND ".$DB->sql_compare_text('objectiveid')." = ".$DB->sql_compare_text('?')."";
      

          $competencypc = $DB->get_field_sql($sql,  array($competency,$criterianame,$kpiname,$objectiveid), 'id', IGNORE_MULTIPLE);


          return $competencypc;
       }else{

            return 0;
       }
        
    }
    public function check_competency_objexams($exams) {

       global $DB,$USER;

        $existingexams=array();

        $nonexistingexams=array();


        if(!empty($exams)) {

            $exams = explode(',',$exams);

            foreach ($exams as $examcode) {

                $examcode=trim($examcode);

                $sql = "SELECT exm.id as examid
                    FROM {local_exams} AS exm 
                    WHERE exm.code = '$examcode' ";  
                                     
                $exam = $DB->record_exists_sql($sql);

                return $exam;
                
            }

        }

       
        
    }
    public function check_competency_objtrainingprograms($trainingprograms) {

        global $DB,$USER;

        $existingprograms=array();

        $nonexistingprograms=array();


        if(!empty($trainingprograms)) {


            $trainingprograms = explode(',',$trainingprograms);

            foreach ($trainingprograms as $programcode) {

                $programcode=trim($programcode);

                $sql = "SELECT trgprgm.id as programid
                    FROM {local_trainingprogram} AS trgprgm 
                    WHERE trgprgm.code = '$programcode' ";  
                                     
                $program = $DB->record_exists_sql($sql);

                return $program;

                
                
            }
            
        }

        
    }
    public function check_competency_objquestions($competencyid,$questions) {

        global $DB,$USER;

        $existingquestions=array();

        $nonexistingquestions=array();


        if(!empty($competencyid) && !empty($questions)) {


            $questions = explode(',',$questions);

            foreach ($questions as $questioncode) {

                $questioncode=trim($questioncode);

                $sql = "SELECT qbcmt.questionid
                    FROM {local_questioncompetencies} AS qbcmt
                    JOIN {question} q ON q.id=qbcmt.questionid
                    WHERE q.name = '$questioncode'";  
                                     
                $question = $DB->get_field_sql($sql);


                if($question){

                    $existingquestions[$questioncode]=$question;

                    unset($nonexistingquestions[$questioncode]);

                }elseif(!isset($existingquestions[$questioncode])){

                    $nonexistingquestions[$questioncode]=$questioncode;
                }
                
            }
        }

        return compact('existingquestions', 'nonexistingquestions');
        
    }
    public function check_exams($competencyid,$exams,$levels) {

       global $DB,$USER;

        $existingexams=array();

        $nonexistingexams=array();


        if(!empty($competencyid) && !empty($exams) && !empty($levels)) {

            $levels = explode(',',$levels);

            $exams = explode(',',$exams);

            foreach($levels as $levelcode){

                $level=trim($levelcode);


                foreach ($exams as $examcode) {

                    $examcode=trim($examcode);

                    $sql = "SELECT exm.id as examid
                        FROM {local_exams} AS exm 
                        WHERE exm.code = '$examcode' AND FIND_IN_SET('$level', exm.clevels) > 0 ";  
                                         
                    $exam = $DB->get_field_sql($sql);


                    if($exam){

                        $existingexams[$examcode]=$exam;

                        unset($nonexistingexams[$examcode]);

                    }elseif(!isset($existingexams[$examcode])){

                        $nonexistingexams[$examcode]=$examcode;
                    }
                    
                }
            }

        }

        return compact('existingexams', 'nonexistingexams');
        
    }
    public function check_trainingprograms($competencyid,$trainingprograms,$levels) {

        global $DB,$USER;


        $existingprograms=array();

        $nonexistingprograms=array();


        if(!empty($competencyid) && !empty($trainingprograms) && !empty($levels)) {

            $levels = explode(',',$levels);

            $trainingprograms = explode(',',$trainingprograms);

            foreach($levels as $levelcode){

                $level=trim($levelcode);

                foreach ($trainingprograms as $programcode) {

                    $programcode=trim($programcode);

                    $sql = "SELECT trgprgm.id as programid
                        FROM {local_trainingprogram} AS trgprgm 
                        WHERE trgprgm.code = '$programcode' AND FIND_IN_SET('$level', trgprgm.clevels) > 0 ";  
                                         
                    $program = $DB->get_field_sql($sql);


                    if($program){

                        $existingprograms[$programcode]=$program;

                        unset($nonexistingprograms[$programcode]);

                    }elseif(!isset($existingprograms[$programcode])){

                        $nonexistingprograms[$programcode]=$programcode;
                    }
                    
                }
            }

        }

        return compact('existingprograms', 'nonexistingprograms');
        
    }
    public function check_questions($competencyid,$questions) {

        global $DB,$USER;

        $existingquestions=array();

        $nonexistingquestions=array();


        if(!empty($competencyid) && !empty($questions)) {


            $questions = explode(',',$questions);

            foreach ($questions as $questioncode) {


                $questioncode=trim($questioncode);

                $sql = "SELECT q.id, qc.id as category
                        FROM {question} q
                        JOIN {question_versions} qv ON qv.questionid = q.id
                        JOIN {question_bank_entries} qbe ON qbe.id = qv.questionbankentryid
                        JOIN {question_categories} qc ON qc.id = qbe.questioncategoryid
                        WHERE q.name = '$questioncode'";
                                     
                $question = $DB->get_record_sql($sql);


                if($question){

                    $existingquestions[$questioncode]=$question;

                    unset($nonexistingquestions[$questioncode]);

                 }elseif(!isset($existingquestions[$questioncode])){

                    $nonexistingquestions[$questioncode]=$questioncode;
                }
                
            }
        }

        return compact('existingquestions', 'nonexistingquestions');
        
    }


     public function map_competency($level,$parentcode,$competencyid,$id,$type) {
        
       global $DB,$USER;

           if($type == 'exam') {

            if($level) {

                $levels = explode(',',$level);
               
                $levelarray = array();
                foreach ($levels as $level) {
                    $levelarray[] = " FIND_IN_SET('$level', clevels) "; 
                }
                $levelqueryparams =implode('OR',$levelarray);
                $formsql = ' AND ('.$levelqueryparams.') ';
                $sql = "SELECT competencies FROM {local_exams} WHERE FIND_IN_SET('$parentcode', ctype) AND id = $id ";
                $competencies = $DB->get_field_sql($sql.$formsql);
                if($competencies) {
                    $competencies = explode(',',$competencies) ;
                    array_push($competencies,$competencyid);

                    $competencies = implode(',',$competencies);


                    $record = $DB->execute("UPDATE {local_exams} SET competencies = '$competencies' WHERE id = $id ");
                }
                elseif(empty($competencies))
                {

                    $sql = "SELECT ctype FROM {local_exams} WHERE FIND_IN_SET('$parentcode', ctype) AND id = $id ";
                    $recordexists = $DB->get_field_sql($sql.$formsql);

                    if(empty($recordexists))
                    {
                        $ctypessql = "SELECT ctype FROM {local_exams} WHERE  id = $id ";
                        $getctypes = $DB->get_field_sql($ctypessql);
                        if($getctypes)
                        {
                            $getctypes = explode(',',$getctypes) ;
                            array_push($getctypes,$parentcode);
                            $getctypes = implode(',',$getctypes);
                        }
                        else
                        {
                            $getctypes =$parentcode;
                        }
                        
                    }
                    $competenciessql = "SELECT competencies FROM {local_exams} WHERE id = $id ";
                    $getcompetencies = $DB->get_field_sql($competenciessql.$formsql);
                    if($getcompetencies)
                    {
                        $getcompetencies = explode(',',$getcompetencies) ;
                        array_push($getcompetencies,$competencyid);
                        $getcompetencies = implode(',',$getcompetencies);
                    }
                    else
                    {
                        $getcompetencies = $competencyid;
                    }
                    

                    $record = $DB->execute("UPDATE {local_exams} SET ctype = '$getctypes',competencies = '$getcompetencies' WHERE id = $id ");
                   
                }
                else
                {
                    $record = $DB->execute("UPDATE {local_exams} SET  clevels='$levels[0]', ctype ='$parentcode', competencies = $competencyid WHERE id = $id ");
                }

            }
           
           } elseif ($type == 'program') {

                if($level) {

                    $levels = explode(',',$level);
                   
                    $levelarray = array();
                    foreach ($levels as $level) {
                        $levelarray[] = " FIND_IN_SET('$level', clevels) "; 
                    }
                    $levelqueryparams =implode('OR',$levelarray);
                    $formsql = ' AND ('.$levelqueryparams.') ';

                    $sql = "SELECT competencyandlevels FROM {local_trainingprogram} WHERE  id = $id ";

                    $competencies = $DB->get_field_sql($sql.$formsql);

                    if($competencies) {
                        $competencies = explode(',',$competencies) ;
                        array_push($competencies,$competencyid);

                        $competencies = implode(',',$competencies);


                        $record = $DB->execute("UPDATE {local_trainingprogram} SET competencyandlevels = '$competencies' WHERE id = $id ");
                    } else {

                        $record = $DB->execute("UPDATE {local_trainingprogram} SET  clevels='$levels[0]', competencyandlevels = $competencyid WHERE id = $id ");
                    }
                }   

           } else {

            // $competencies = $DB->get_field('local_questionbank','competency',['id'=>$id]);
            // if($competencies) {

            //     $competencies = explode(',',$competencies) ;
            //     array_push($record,$competencyid);
            // } else {

            //     $record = $DB->execute('UPDATE {local_questionbank} SET competency = '.$competencyid.' WHERE id = '.$id.' ');


            // }
        }

        return ($record)? true : false; 
    
    }
}

