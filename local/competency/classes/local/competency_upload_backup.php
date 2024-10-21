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
            'PerformanceCriteria' => 'PerformanceCriteria' ,
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
        if(!is_siteadmin()){
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

        while ($row = $cir->next()) {


            if($linenum > 8){

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

                $this->competencyrow = $competencyrow;
                $this->performancecriteriarow = $performancecriteriarow;

                $this->required_fields_validations($masterdata,$errorcount,$competencyrow,$performancecriteriarow);

                $competencyrow = array_merge($competencyrow,$this->competencyrow);
                $performancecriteriarow = array_merge($performancecriteriarow,$this->performancecriteriarow);

            }

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
            return  $this->errors;
        }
        if (!in_array('EN_Name', $this->columnsmapping)) {
            $this->errors[] = get_string('nameenglishmissing', 'local_competency');
            return  $this->errors;
        }

        if (!in_array('AR_Name', $this->columnsmapping)) {
            $this->errors[] = get_string('namearabicmissing', 'local_competency');
            return  $this->errors;
        }

        if (!in_array('Description', $this->columnsmapping)) {
            
            $this->errors[] = get_string('descriptionmissing', 'local_competency');
            return  $this->errors;
        }
        if (!in_array('TYPE', $this->columnsmapping)) {
           
            $this->errors[] = get_string('typemissing', 'local_competency');
            return  $this->errors;
        }

        if (!in_array('CODE', $this->columnsmapping)) {

            $this->errors[] = get_string('codemissing', 'local_competency');
            return  $this->errors;
        }

        if (!in_array('PARENT_CODE', $this->columnsmapping)) {

            $this->errors[] = get_string('parentcodemissing', 'local_competency');
            return  $this->errors;
        }

        if (!in_array('EXAM_CODE', $this->columnsmapping)) { 
            $this->errors[] = get_string('examcodemissing', 'local_competency');
            return  $this->errors;
        }

        if (!in_array('PROGRAM_CODE', $this->columnsmapping)) { 
            $this->errors[] = get_string('programcodemissing', 'local_competency');
            return  $this->errors;
        }

        if (!in_array('QUESTION_CODE', $this->columnsmapping)) { 
            $this->errors[] = get_string('questioncodemissing', 'local_competency');
            return  $this->errors;
        }

        if (!in_array('LEVEL', $this->columnsmapping)) {
           
            $this->errors[] = get_string('levelmissing', 'local_competency');
            return  $this->errors;
        }

        return false;
    }

    private function required_fields_validations($excel,$errorcount,$competencyrow,$performancecriteriarow){
        global $DB;
    
        //-----------check competency oldid-----------------------------------
        if (array_key_exists('OLD_ID', $excel) ) {

            if (empty($excel->OLD_ID)) {

                echo '<div class="local_competency_sync_error">'.get_string('oldidempty','local_competency', $excel).'</div>'; 
                $this->errors[] =  get_string('oldidempty', 'local_competency',$excel);
                $this->mfields[] = 'OLD_ID';
                $this->errorcount++;

            }
        }

        //-----------check competency name english-----------------------------------
        if (array_key_exists('EN_Name', $excel) ) {

            if (empty($excel->EN_Name)) {
                echo '<div class="local_competency_sync_error">'.get_string('nameenglishempty','local_competency', $excel).'</div>'; 
                $this->errors[] =  get_string('nameenglishempty', 'local_competency',$excel);
                $this->mfields[] = 'EN_Name';
                $this->errorcount++;
            }
        }

        //-----------check competency name arabic-----------------------------------
        if (array_key_exists('AR_Name', $excel) && $excel->TYPE == 'Competency') {

            if (empty($excel->AR_Name)) {

                echo '<div class="local_competency_sync_error">'.get_string('namearabicempty','local_competency', $excel).'</div>'; 
                $this->errors[] =  get_string('namearabicempty', 'local_competency',$excel);
                $this->mfields[] = 'AR_Name';
                $this->errorcount++;

            }
        }
            
        //-------- check competency description-------------------------------------
        if ( array_key_exists('Description', $excel) && $excel->TYPE == 'Competency' ) {

            if (empty($excel->Description)) {

                echo '<div class="local_competency_sync_error">'.get_string('descriptionempty','local_competency', $excel).'</div>'; 
                $this->warnings[] = get_string('descriptionempty','local_competency', $excel);
                $this->wmfields[] = 'Description';

            }
        }


        //-------- check competency type-------------------------------------
        if (array_key_exists('TYPE', $excel) ) {

            if (empty($excel->TYPE)) {

                echo '<div class="local_competency_sync_error">'.get_string('typeempty','local_competency', $excel).'</div>'; 
                $this->errors[] = get_string('typeempty','local_competency', $excel);
                $this->mfields[] = 'TYPE';
                $this->errorcount++;

            }else{

                $uploadsheetcolumntypecheck=$this->check_uploadsheet_column_typecheck($excel->TYPE);

                 if(!$uploadsheetcolumntypecheck){

                    echo '<div class="local_competency_sync_error">'.get_string('typeenotmatchedwithuploadedsheet','local_competency', $excel).'</div>'; 
                    $this->errors[] = get_string('typeenotmatchedwithuploadedsheet','local_competency', $excel);
                    $this->mfields[] = 'TYPE';
                    $this->errorcount++;

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

            }else{

                $levelcheck=$this->check_competency_levelcheck($excel->LEVEL);

                 if(empty($levelcheck['existinglevels'])){

                    echo '<div class="local_competency_sync_error">'.get_string('levelnotmatchedwithuploadedsheet','local_competency', $excel).'</div>'; 
                    $this->errors[] = get_string('levelnotmatchedwithuploadedsheet','local_competency', $excel);
                    $this->mfields[] = 'LEVEL';
                    $this->errorcount++;

                 }elseif(!empty($levelcheck['nonexistinglevels'])){

                    $excel->somelevels=implode(',',array_keys($levelcheck['nonexistinglevels']));

                    echo '<div class="local_competency_sync_error">'.get_string('somelevelnotmatchedwithuploadedsheet','local_competency', $excel).'</div>'; 

                    $this->mfields[] = 'LEVEL';
                    $this->errorcount++;

                }elseif(!empty($levelcheck['existinglevels'])){

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

            }
        }

        //-----------check competency code-----------------------------------
        if (array_key_exists('PARENT_CODE', $excel) ) {

            if (empty($excel->PARENT_CODE)) {

                echo '<div class="local_competency_sync_error">'.get_string('parentcodeempty','local_competency', $excel).'</div>'; 
                $this->errors[] =  get_string('parentcodeempty', 'local_competency',$excel);
                $this->mfields[] = 'PARENT_CODE';
                $this->errorcount++;

            }elseif($excel->TYPE == 'Competency'){

                $this->competencyrow[$excel->CODE]=clone $excel;

                $typecheck=$this->check_competency_typecheck($excel->PARENT_CODE);

                 if(!$typecheck){

                    echo '<div class="local_competency_sync_error">'.get_string('parentcodetypenotmatchedwithuploadedsheet','local_competency', $excel).'</div>'; 
                    $this->errors[] = get_string('parentcodetypenotmatchedwithuploadedsheet','local_competency', $excel);
                    $this->mfields[] = 'PARENT_CODE';
                    $this->errorcount++;

                }

            }elseif($excel->TYPE == 'PerformanceCriteria'){


                $this->performancecriteriarow[$excel->CODE]= clone $excel;

                $typecompetencyid=$this->get_competency($excel->PARENT_CODE);

                 if(!$typecompetencyid){

                    echo '<div class="local_competency_sync_error">'.get_string('parentcodecompetencynotmatchedwithrecords','local_competency', $excel).'</div>'; 
                    $this->errors[] = get_string('parentcodecompetencynotmatchedwithrecords','local_competency', $excel);
                    $this->mfields[] = 'PARENT_CODE';
                    $this->errorcount++;

                }

            }elseif($excel->TYPE == 'PerformanceObjective'){


                $criteriacode=$performancecriteriarow[$excel->PARENT_CODE];


                if($criteriacode){

                        $typecompetencyid=$this->get_competency($criteriacode->PARENT_CODE);

                        if(!$typecompetencyid){

                            echo '<div class="local_competency_sync_error">'.get_string('parentcodecompetencycodenotmatchedwithrecords','local_competency', $excel).'</div>'; 
                            $this->errors[] = get_string('parentcodecompetencycodenotmatchedwithrecords','local_competency', $excel);
                            $this->mfields[] = 'PARENT_CODE';
                            $this->errorcount++;

                        }else{

                            $criterianame =strtolower(preg_replace('/\s+/', '', $criteriacode->EN_Name));

                            $excelkpiname =str_replace('performancecriteria', 'kpi', $criterianame);

                            $excelobjectiveid =str_replace('performancecriteria', 'objective', $criterianame);

                            $typecompetencypcid=$this->get_competencypc($typecompetencyid, $criterianame,$excelkpiname,$excelobjectiveid);

                             if(!$typecompetencypcid){

                                echo '<div class="local_competency_sync_error">'.get_string('parentcodecompetencypccodenotmatchedwithrecords','local_competency', $excel).'</div>'; 
                                $this->errors[] = get_string('parentcodecompetencypccodenotmatchedwithrecords','local_competency', $excel);
                                $this->mfields[] = 'PARENT_CODE';
                                $this->errorcount++;

                            } 
                        }

                }else{

                    echo '<div class="local_competency_sync_error">'.get_string('parentcodecriterianotmatchedwithuploadedsheet','local_competency', $excel).'</div>'; 

                    $this->errors[] = get_string('parentcodecriterianotmatchedwithuploadedsheet','local_competency', $excel);
                    $this->mfields[] = 'PARENT_CODE';
                    $this->errorcount++;

                }

            }
        }

        if (count($this->errors) > 0) { 

                return $errorcount++;


        }elseif($excel->TYPE == 'Competency'){


            $excel->code = $excel->CODE;
            $excel->name = $excel->EN_Name;
            $excel->arabicname = $excel->AR_Name;
            $excel->description['text']= $excel->Description;
            $excel->type =$typecheck;
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


                //     //-----------check competency Exams-----------------------------------
                //     if (array_key_exists('EXAM_CODE', $excel)) {

                //         if (empty($excel->EXAM_CODE)) {

                //             echo '<div class="local_competency_sync_error">'.get_string('examcodeempty','local_competency', $excel).'</div>'; 
                //             $this->errors[] =  get_string('examcodeempty', 'local_competency',$excel);
                //             $this->mfields[] = 'EXAM_CODE';
                //             $this->errorcount++;

                //         }else {


                //             $exams =  $this->check_exams($excel->competencyid,$excel->EXAM_CODE,$excel->LEVEL);

                //             if(empty($exams['existingexams'])){

                //                echo '<div class="local_competency_sync_error">'.get_string('examcodenotmatchedwithrecords','local_competency', $excel).'</div>'; 
                //                 $this->errors[] = get_string('examcodenotmatchedwithrecords','local_competency', $excel);
                //                 $this->mfields[] = 'EXAM_CODE';
                //                 $this->errorcount++;

                //             }elseif(!empty($exams['nonexistingexams'])){

                //                 $excel->someexams=implode(',',array_keys($exams['nonexistingexams']));

                //                 echo '<div class="local_competency_sync_error">'.get_string('someexamcodenotmatchedwithrecords','local_competency', $excel).'</div>'; 

                //                 $this->mfields[] = 'EXAM_CODE';
                //                 $this->errorcount++;

                //             }   
                //         }      
                //         if(empty($exams['existingexams'])) { 

                //             $errorcount++;

                //         }else{


                //             $excel->id =0;

                //             $excel->exams =$exams['existingexams'];

                //             self::competencyexams_datasubmit($excel);

                //             $excel->EXAM_CODE =implode(',',array_keys($exams['existingexams']));

                //             $competencyexamscrud=get_string('sucesscompetencyexamscreated','local_competency', $excel);
                            

                //             echo $competencyexamscrud; 

                //             $this->success[]=$competencyexamscrud;

                //             $this->successcount++;

                //         }
                //     }
          
                // //-----------check competency Training Programs-----------------------------------
                //     if (array_key_exists('PROGRAM_CODE', $excel) ) {

                //         if (empty($excel->PROGRAM_CODE)) {

                //             echo '<div class="local_competency_sync_error">'.get_string('programcodeempty','local_competency', $excel).'</div>'; 
                //             $this->errors[] =  get_string('programcodeempty', 'local_competency',$excel);
                //             $this->mfields[] = 'PROGRAM_CODE';
                //             $this->errorcount++;

                //         }else {

                //             $trainingprograms =  $this->check_trainingprograms($excel->competencyid,$excel->PROGRAM_CODE,$excel->LEVEL);

                //             if(empty($trainingprograms['existingprograms'])){

                //                echo '<div class="local_competency_sync_error">'.get_string('programcodenotmatchedwithrecords','local_competency', $excel).'</div>'; 
                //                 $this->errors[] = get_string('programcodenotmatchedwithrecords','local_competency', $excel);
                //                 $this->mfields[] = 'PROGRAM_CODE';
                //                 $this->errorcount++;

                //             }elseif(!empty($trainingprograms['nonexistingprograms'])){


                //                 $excel->sometrainingprograms=implode(',',array_keys($trainingprograms['nonexistingprograms']));

                //                 echo '<div class="local_competency_sync_error">'.get_string('someprogramcodenotmatchedwithrecords','local_competency', $excel).'</div>'; 
                //                 $this->mfields[] = 'PROGRAM_CODE';
                //                 $this->errorcount++;

                //             } 
                //         }        
                //         if(empty($trainingprograms['existingprograms'])) { 

                //             $errorcount++;

                //         }else{

                //             $excel->id =0;

                //             $excel->trainingprograms =$trainingprograms['existingprograms'];

                //             self::competencyprograms_datasubmit($excel);

                //             $excel->PROGRAM_CODE =implode(',',array_keys($trainingprograms['existingprograms']));

                //             $competencprogramscrud=get_string('sucesscompetencyprogramscreated','local_competency', $excel);
                            

                //             echo $competencprogramscrud; 

                //             $this->success[]=$competencprogramscrud;

                //             $this->successcount++;

                //         }
                //     }

                // //-----------check competency Questions-----------------------------------
                //     if (array_key_exists('QUESTION_CODE', $excel) ) {

                //         if (empty($excel->QUESTION_CODE)) {

                //             echo '<div class="local_competency_sync_error">'.get_string('questioncodeempty','local_competency', $excel).'</div>'; 
                //             $this->errors[] =  get_string('questioncodeempty', 'local_competency',$excel);
                //             $this->mfields[] = 'QUESTION_CODE';
                //             $this->errorcount++;

                //         }else {

                //             $questions =  $this->check_questions($excel->competencyid,$excel->QUESTION_CODE);

                //             if(empty($questions['existingquestions'])){

                //                echo '<div class="local_competency_sync_error">'.get_string('questioncodenotmatchedwithrecords','local_competency', $excel).'</div>'; 
                //                 $this->errors[] = get_string('questioncodenotmatchedwithrecords','local_competency', $excel);
                //                 $this->mfields[] = 'QUESTION_CODE';
                //                 $this->errorcount++;

                //             }elseif(!empty($questions['nonexistingquestions'])){

                //                 $excel->somequestions=implode(',',array_keys($questions['nonexistingquestions']));

                //                 echo '<div class="local_competency_sync_error">'.get_string('somequestioncodenotmatchedwithrecords','local_competency', $excel).'</div>'; 
                //                 $this->mfields[] = 'QUESTION_CODE';
                //                 $this->errorcount++;

                //             }    
                //         }
                //         if(empty($questions['existingquestions'])) { 

                //             $errorcount++;

                //         }else{

                //             $excel->id =0;

                //             $excel->questions = $questions['existingquestions'];

                //             self::competencyquestions_datasubmit($excel);

                //             $excel->QUESTION_CODE =implode(',',array_keys($questions['existingquestions']));

                //             $competencyquestionscrud=get_string('sucesscompetencyquestionscreated','local_competency', $excel);
                            

                //             echo $competencyquestionscrud; 

                //             $this->success[]=$competencyquestionscrud;

                //             $this->successcount++;

                //         }
                //     } 

                // return $errorcount++;   

           }

           

        }elseif($excel->TYPE == 'PerformanceCriteria'){


            $excel->id =0;

            $excel->competency = $typecompetencyid;

            $excel->criterianame =strtolower(preg_replace('/\s+/', '', $excel->EN_Name));

            $kpis = array_keys(self::constkpis());

            $excel->kpiname =$kpis[0];

            $objectives = array_keys(self::constobjectives());

            $excel->objectiveid =$objectives[0];


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
                   
            
        }elseif($excel->TYPE == 'PerformanceObjective'){


            $excel->competency = $typecompetencyid;

            $excel->competencypc = $typecompetencypcid;

            //-----------check competency Objective Exams-----------------------------------
            if (array_key_exists('EXAM_CODE', $excel) ) {

                    if (empty($excel->EXAM_CODE)) {

                        echo '<div class="local_competency_sync_error">'.get_string('examcodeempty','local_competency', $excel).'</div>'; 
                        $this->errors[] =  get_string('examcodeempty', 'local_competency',$excel);
                        $this->mfields[] = 'EXAM_CODE';
                        $this->errorcount++;

                    }else {

                        $exams =  $this->check_competency_objexams($typecompetencyid,$excel->EXAM_CODE);

                        if(empty($exams['existingexams'])){

                           echo '<div class="local_competency_sync_error">'.get_string('examcodenotmatchedwithrecords','local_competency', $excel).'</div>'; 
                            $this->errors[] = get_string('examcodenotmatchedwithrecords','local_competency', $excel);
                            $this->mfields[] = 'EXAM_CODE';
                            $this->errorcount++;

                        }elseif(!empty($exams['nonexistingexams'])){

                            $excel->someexams=implode(',',array_keys($exams['nonexistingexams']));

                            echo '<div class="local_competency_sync_error">'.get_string('someexamcodenotmatchedwithrecords','local_competency', $excel).'</div>'; 

                            $this->mfields[] = 'EXAM_CODE';
                            $this->errorcount++;

                        }   
                }      
                if(empty($exams['existingexams'])) { 

                    $errorcount++;

                }else{

          
                    $excel->id =0;

                    $excel->oldid =$excel->OLD_ID;

                    $excel->objexams =$exams['existingexams'];

                    self::competencyobjective_datasubmit($excel);

                    $excel->EXAM_CODE =implode(',',array_keys($exams['existingexams']));

                    $competencypccontentexamscrud=get_string('sucesscompetencypccontentexamscreated','local_competency', $excel);
                    

                    echo $competencypccontentexamscrud; 

                    $this->success[]=$competencypccontentexamscrud;

                    $this->successcount++;

                }
            }
      
            //-----------check competency Objective Training Programs-----------------------------------
            if (array_key_exists('PROGRAM_CODE', $excel) ) {

                if (empty($excel->PROGRAM_CODE)) {

                    echo '<div class="local_competency_sync_error">'.get_string('programcodeempty','local_competency', $excel).'</div>'; 
                    $this->errors[] =  get_string('programcodeempty', 'local_competency',$excel);
                    $this->mfields[] = 'PROGRAM_CODE';
                    $this->errorcount++;

                }else {

                    $trainingprograms =  $this->check_competency_objtrainingprograms($typecompetencyid,$excel->PROGRAM_CODE);

                    if(empty($trainingprograms['existingprograms'])){

                       echo '<div class="local_competency_sync_error">'.get_string('programcodenotmatchedwithrecords','local_competency', $excel).'</div>'; 
                        $this->errors[] = get_string('programcodenotmatchedwithrecords','local_competency', $excel);
                        $this->mfields[] = 'PROGRAM_CODE';
                        $this->errorcount++;

                    }elseif(!empty($trainingprograms['nonexistingprograms'])){

                        $excel->sometrainingprograms=implode(',',array_keys($trainingprograms['nonexistingprograms']));

                        echo '<div class="local_competency_sync_error">'.get_string('someprogramcodenotmatchedwithrecords','local_competency', $excel).'</div>'; 
                        $this->mfields[] = 'PROGRAM_CODE';
                        $this->errorcount++;

                    }    
                }        
                if(empty($trainingprograms['existingprograms'])) { 

                   $errorcount++;

                }else{

                    $excel->id =0;

                    $excel->oldid =$excel->OLD_ID;

                    $excel->objtrainingprograms =$trainingprograms['existingprograms'];

                    self::competencyobjective_datasubmit($excel);

                    $excel->PROGRAM_CODE =implode(',',array_keys($trainingprograms['existingprograms']));

                    $competencypccontentprogramscrud=get_string('sucesscompetencypccontentprogramscreated','local_competency', $excel);
                    

                    echo $competencypccontentprogramscrud; 

                    $this->success[]=$competencypccontentprogramscrud;

                    $this->successcount++;

                }
            }

            //-----------check competency Objective Questions-----------------------------------
            if (array_key_exists('QUESTION_CODE', $excel) ) {

                if (empty($excel->QUESTION_CODE)) {

                    echo '<div class="local_competency_sync_error">'.get_string('questioncodeempty','local_competency', $excel).'</div>'; 
                    $this->errors[] =  get_string('questioncodeempty', 'local_competency',$excel);
                    $this->mfields[] = 'QUESTION_CODE';
                    $this->errorcount++;

                }else {

                    $questions =  $this->check_competency_objquestions($typecompetencyid,$excel->QUESTION_CODE);

                    if(empty($questions['existingquestions'])){

                       echo '<div class="local_competency_sync_error">'.get_string('questioncodenotmatchedwithrecords','local_competency', $excel).'</div>'; 
                        $this->errors[] = get_string('questioncodenotmatchedwithrecords','local_competency', $excel);
                        $this->mfields[] = 'QUESTION_CODE';
                        $this->errorcount++;

                    }elseif(!empty($questions['nonexistingquestions'])){

                        $excel->somequestions=implode(',',array_keys($questions['nonexistingquestions']));

                        echo '<div class="local_competency_sync_error">'.get_string('somequestioncodenotmatchedwithrecords','local_competency', $excel).'</div>'; 
                        $this->mfields[] = 'QUESTION_CODE';
                        $this->errorcount++;

                    }    
                }
                if(empty($questions['existingquestions'])) { 

                    $errorcount++;

                }else{

                    $excel->id =0;

                    $excel->oldid =$excel->OLD_ID;

                    $excel->objquestions = $questions['existingquestions'];

                    self::competencyobjective_datasubmit($excel);

                    $excel->QUESTION_CODE =implode(',',array_keys($questions['existingquestions']));

                    $competencypccontentquestionscrud=get_string('sucesscompetencypccontentquestionscreated','local_competency', $excel);
                    

                    echo $competencypccontentquestionscrud; 

                    $this->success[]=$competencypccontentquestionscrud;

                    $this->successcount++;

                }
            }

            return $errorcount++;
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

            $criterianame=trim($criterianame);

            $kpiname=trim($kpiname);

            $objectiveid=trim($objectiveid);

            $sql = "SELECT id FROM {local_competency_pc}
                    WHERE competency = ? AND ".$DB->sql_compare_text('criterianame')." = ".$DB->sql_compare_text('?')." AND ".$DB->sql_compare_text('kpiname')." = ".$DB->sql_compare_text('?')." AND ".$DB->sql_compare_text('objectiveid')." = ".$DB->sql_compare_text('?')."";
      

          $competencypc = $DB->get_field_sql($sql,  array($competency,$criterianame,$kpiname,$objectiveid), 'id', IGNORE_MULTIPLE);


          return $competencypc;
       }else{

            return 0;
       }
        
    }
    public function check_competency_objexams($competencyid,$exams) {

       global $DB,$USER;

        $existingexams=array();

        $nonexistingexams=array();


        if(!empty($competencyid) && !empty($exams)) {

            $exams = explode(',',$exams);

            foreach ($exams as $examcode) {

                $examcode=trim($examcode);

                $sql = "SELECT exm.id as examid
                    FROM {local_exams} AS exm 
                    WHERE exm.code = '$examcode' AND concat(',',exm.competencies,',' ) like '%,$competencyid,%' ";  
                                     
                $exam = $DB->get_field_sql($sql);


                if($exam){

                    $existingexams[$examcode]=$exam;

                    unset($nonexistingexams[$examcode]);

                 }elseif(!isset($existingexams[$examcode])){

                    $nonexistingexams[$examcode]=$examcode;
                }
                
            }

        }

        return compact('existingexams', 'nonexistingexams');
        
    }
    public function check_competency_objtrainingprograms($competencyid,$trainingprograms) {

        global $DB,$USER;

        $existingprograms=array();

        $nonexistingprograms=array();


        if(!empty($competencyid) && !empty($trainingprograms)) {


            $trainingprograms = explode(',',$trainingprograms);

            foreach ($trainingprograms as $programcode) {

                $programcode=trim($programcode);

                $sql = "SELECT trgprgm.id as programid
                    FROM {local_trainingprogram} AS trgprgm 
                    WHERE trgprgm.code = '$programcode' AND concat(',',trgprgm.competencyandlevels,',' ) like '%,$competencyid,%' ";  

                                     
                $program = $DB->get_field_sql($sql);



                if($program){

                    $existingprograms[$programcode]=$program;

                    unset($nonexistingprograms[$programcode]);

                }elseif(!isset($existingprograms[$programcode])){

                    $nonexistingprograms[$programcode]=$programcode;
                }
                
            }
            
        }

        return compact('existingprograms', 'nonexistingprograms');
        
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
                    WHERE q.name = '$questioncode' AND concat(',',qbcmt.competency,',' ) like '%,$competencyid,%'";  
                                     
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
}

