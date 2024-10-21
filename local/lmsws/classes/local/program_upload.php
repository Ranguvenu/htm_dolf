<?php
namespace local_trainingprogram\local;
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
use local_trainingprogram\local\trainingprogram as tp;

class program_upload  {

    protected $columns;

    protected $columnsmapping = array();

    private $users = array();

    protected $allowedcolumns = array('programname','programcode','oldid','programnamearabic','programdescription', 'programstartdate', 'programenddate','programgoals','programcost','programsellingprice','programactualprice','programtrainingmethods','programevolutionmethods','programlanguages', 'programduration','programrunningtime','programsectors','programlevel','competencytypes','programcompetencies','programjobfamilies','programdiscount');
    /**
     * @method upload_program_file file
     * @todo To process the uploaded CSV file and return the data
     * @param stored_file $file
     * @param string $encoding
     * @param string $delimiter
     * @param context $defaultcontext
     * @return array
     */

    public function upload_program_file($file, $defaultcontext) {
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
       // print_object($content);


        $tempfile = tempnam(make_temp_directory('/csvimport'), 'tmp');

        if (!$fp = fopen($tempfile, 'w+b')) {
            $this->_error = get_string('cannotsavedata', 'error');
            @unlink($tempfile);
            return false;
        }
        fwrite($fp, $content);
        fseek($fp, 0);
        $uploadid = csv_import_reader::get_new_iid('Programupload');
        $cir = new csv_import_reader($uploadid, 'Programupload');

        /**
         * Actual upload starts from here
         */
        $readcount = $cir->load_csv_content($content, 'utf-8', 'comma');

        unset($content);
        if (!$readcount ) {
            throw new moodle_exception('csvloaderror', 'error',$PAGE->url,  $cir->get_error());
        }
        if(!is_siteadmin() AND !has_capability('local/organization:manage_trainingofficial', $systemcontext)){
            throw new moodle_exception('youdonthavepermissiontouploaddata', 'local_trainingprogram');
        }
        

        if($readcount == 1) {

          throw new moodle_exception('filecannotbehaveemptydata', 'local_trainingprogram');
        }    
          
          

            $this->columns = $cir->get_columns();
            $column_validation = $this->validate_columns();
            if(is_array($column_validation) && count($column_validation) > 0){
                $string = $column_validation[0];
                $return =  '<div class="local_trainingprogram_sync_error">'.get_string('validsheet','local_trainingprogram',$string).'</div>'; 
                $return .= html_writer::start_tag('div', array('class'=> 'w-100 mt-5')).$OUTPUT->continue_button(new moodle_url('/local/trainingprogram/uploadprogram.php')).html_writer::end_tag('div');
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
                $masterdata->name = ucfirst($masterdata->programname);
                $masterdata->namearabic = $masterdata->programnamearabic;
                $masterdata->code = $masterdata->programcode;
                $masterdata->oldid = $masterdata->oldid;
                $masterdata->description['text']= $masterdata->programdescription;
                $masterdata->program_goals['text'] = $masterdata->programgoals;
                $masterdata->duration = ceil($masterdata->programduration * 86400);
                $masterdata->hour = ceil($masterdata->programrunningtime * 3600);
                $masterdata->availablefrom = strtotime($masterdata->programstartdate);
                $masterdata->availableto= strtotime($masterdata->programenddate);
                $masterdata->clevels ='level'.$masterdata->programlevel;
                $sectors =  $this->get_sector_ids(explode('*',$masterdata->programsectors));
                $masterdata->sectors = $sectors;
                $masterdata->cost =$masterdata->programcost;
                $masterdata->sellingprice =$masterdata->programsellingprice;
                $masterdata->actualprice =$masterdata->programactualprice;
                $masterdata->language =explode('*',$masterdata->programlanguages);
                $masterdata->programmethod =explode('*',$masterdata->programtrainingmethods);
                $masterdata->evaluationmethod =explode('*',$masterdata->programevolutionmethods);
                $masterdata->discount =$masterdata->programdiscount;
                $jobfamilies =  $this->get_jobfamily_ids(explode('*',$masterdata->programjobfamilies));
                $masterdata->targetgroup = $jobfamilies;

        
                $competencyids = $this->get_competency_ids(explode('*',$masterdata->programcompetencies));
                $masterdata->competencylevel = $competencyids;

                if(!empty($masterdata->programcode) && $DB->record_exists('local_trainingprogram',array('code' => $masterdata->programcode))) {

                  
                    $programid = $DB->get_field('local_trainingprogram','id',array('code' => $masterdata->programcode));

                    $courseid = $DB->get_field('local_trainingprogram','courseid',array( 'id' => $programid,'code' => $masterdata->programcode));

                    $masterdata->id = $programid;
                    $masterdata->courseid = $courseid;
                   
                   $createdid = (new tp)->update_program($masterdata);
                } else {

                    $createdid = (new tp)->add_new($masterdata);
                }
                
                $event = \local_trainingprogram\event\trainingprogram_created::create(array( 'context'=>$systemcontext, 'objectid' =>$createdid));
                $event->trigger();
                $masterdata->program_name=$masterdata->name;                  
                    (new \local_trainingprogram\notification())->trainingprogram_notification('trainingprogram_create', $touser=null,$fromuser=$USER,$masterdata,$waitinglistid=0);

                $return .= $OUTPUT->notification(get_string('uploadprogramsheet', 'local_trainingprogram'),'info');
                
            
            }

	}
        
        $return .= html_writer::start_tag('div', array('class'=> 'w-100 mt-5')).$OUTPUT->continue_button(new moodle_url('/local/trainingprogram/uploadprogram.php')).html_writer::end_tag('div');
        
        echo $return;
    }
    private function validate_columns() {
        global $DB;


        foreach ($this->columns as $i => $columnname) {
            if (in_array(strtolower($columnname), $this->allowedcolumns)) {
                $this->columnsmapping[$i] = strtolower($columnname);
            }
        }
        if (!in_array('programname', $this->columnsmapping)) {
            $this->errors[] = get_string('name_missing', 'local_trainingprogram');
            return  $this->errors;
        }
        if (!in_array('programnamearabic', $this->columnsmapping)) {
            $this->errors[] = get_string('arabicname_missing', 'local_trainingprogram');
            return  $this->errors;
        }

        if (!in_array('oldid', $this->columnsmapping)) {
            $this->errors[] = get_string('oldid_missing', 'local_trainingprogram');
            return  $this->errors;
        }

        if (!in_array('programdescription', $this->columnsmapping)) {
            
             $this->errors[] = get_string('description_missing', 'local_trainingprogram');
            return  $this->errors;
        }
        if (!in_array('programstartdate', $this->columnsmapping)) {
           
             $this->errors[] = get_string('startdate_missing', 'local_trainingprogram');
            return  $this->errors;
        }

        if (!in_array('programenddate', $this->columnsmapping)) {
            
             $this->errors[] = get_string('enddate_missing', 'local_trainingprogram');
            return  $this->errors;
        }
        if (!in_array('programgoals', $this->columnsmapping)) {
        
            $this->errors[] = get_string('program_goals_missing', 'local_trainingprogram');
            return  $this->errors;
        }
         if (!in_array('programcost', $this->columnsmapping)) {
        
            $this->errors[] = get_string('programcost_missing', 'local_trainingprogram');
            return  $this->errors;
        }

        if (!in_array('programlanguages', $this->columnsmapping)) { 
            $this->errors[] = get_string('languages_missing', 'local_trainingprogram');
            return  $this->errors;
        }
        if (!in_array('programduration', $this->columnsmapping)) { 
            $this->errors[] = get_string('duration_missing', 'local_trainingprogram');
            return  $this->errors;
        }
        if (!in_array('programrunningtime', $this->columnsmapping)) { 
            $this->errors[] = get_string('hour_missing', 'local_trainingprogram');
            return  $this->errors;
        }
         if (!in_array('programsectors', $this->columnsmapping)) { 
            $this->errors[] = get_string('sectors_memissing', 'local_trainingprogram');
            return  $this->errors;
        }
        if (!in_array('programlevel', $this->columnsmapping)) { 
            $this->errors[] = get_string('clevels_missing', 'local_trainingprogram');
            return  $this->errors;
        }
       if (!in_array('competencytypes', $this->columnsmapping)) { 
            $this->errors[] = get_string('competency_types_missing', 'local_trainingprogram');
            return  $this->errors;
        }
        if (!in_array('programcompetencies', $this->columnsmapping)) { 
            $this->errors[] = get_string('competencyandlevels_missing', 'local_trainingprogram');
            return  $this->errors;
        }
        if (!in_array('programdiscount', $this->columnsmapping)) { 
            $this->errors[] = get_string('programdiscount_missing', 'local_trainingprogram');
            return  $this->errors;
        }
        return false;
    }

    private function required_fields_validations($excel,$option=0){
        global $DB;
        $strings = new stdClass;
        $strings->excel_line_number = $this->excel_line_number;
        //-----------check program name-----------------------------------
        if (array_key_exists('programname', $excel) ) {
            if (empty($excel->programname)) {
                echo '<div class="local_trainingprogram_sync_error">'.get_string('namemissing','local_trainingprogram', $strings).'</div>'; 
                $this->errors[] =  get_string('namemissing', 'local_trainingprogram',$strings);
                $this->mfields[] = 'ProgramName';
                $this->errorcount++;
            } /*else {

                $record = $DB->record_exists_sql("SELECT id FROM {local_trainingprogram} WHERE name = '$excel->programname' AND code <> '$excel->programcode'");

                if($record) {
                  
                echo '<div class="local_trainingprogram_sync_error">'.get_string('namealreadyexists','local_trainingprogram', $strings).'</div>'; 
                $this->errors[] =  get_string('namealreadyexists', 'local_trainingprogram',$strings);
                $this->mfields[] = 'ProgramName';
                $this->errorcount++;

                }
            }*/
        }

        //-----------check program name in arabic-----------------------------------
        if (array_key_exists('programnamearabic', $excel) ) {
            if (empty($excel->programnamearabic)) {
                echo '<div class="local_trainingprogram_sync_error">'.get_string('arabicnamemissing','local_trainingprogram', $strings).'</div>'; 
                $this->errors[] =  get_string('arabicnamemissing', 'local_trainingprogram',$strings);
                $this->mfields[] = 'ProgramNameArabic';
                $this->errorcount++;
            }
        }

         //-----------check old id-----------------------------------
        // if (array_key_exists('oldid', $excel) ) {
        //     if (empty($excel->oldid)) {
        //         echo '<div class="local_trainingprogram_sync_error">'.get_string('oldidmissing','local_trainingprogram', $strings).'</div>'; 
        //         $this->errors[] =  get_string('oldidmissing', 'local_trainingprogram',$strings);
        //         $this->mfields[] = 'OldId';
        //         $this->errorcount++;
        //     }
        // }
            
        //-------- check program description-------------------------------------
        // if ( array_key_exists('programdescription', $excel) ) {
        //     if (empty($excel->programdescription)) {
        //         echo '<div class="local_trainingprogram_sync_error">'.get_string('descriptionmissing','local_trainingprogram', $strings).'</div>'; 
        //         $this->errors[] = get_string('descriptionmissing','local_trainingprogram', $excel);
        //         $this->mfields[] = 'ProgramDescription';
        //         $this->errorcount++;
        //     }
        // }


        //-------- check program goals-------------------------------------
        // if ( array_key_exists('programgoals', $excel) ) {
        //     if (empty($excel->programgoals)) {
        //         echo '<div class="local_trainingprogram_sync_error">'.get_string('programgoalsmissing','local_trainingprogram', $strings).'</div>'; 
        //         $this->errors[] = get_string('programgoalsmissing','local_trainingprogram', $excel);
        //         $this->mfields[] = 'ProgramGoals';
        //         $this->errorcount++;
        //     }
        // }

        //-------- check program startdate-------------------------------------
        if ( array_key_exists('programstartdate', $excel) ) {
            if (empty($excel->programstartdate)) {
                echo '<div class="local_trainingprogram_sync_error">'.get_string('startdatemissing','local_trainingprogram', $strings).'</div>'; 
                $this->errors[] = get_string('startdatemissing','local_trainingprogram', $excel);
                $this->mfields[] = 'ProgramStartdate';
                $this->errorcount++;
            } /*else {

               $currdate = date('Y-m-d');
               if(!strtotime($excel->programstartdate)){

                   echo '<div class="local_trainingprogram_sync_error">'.get_string('validstartdate','local_trainingprogram', $strings).'</div>'; 
                    $this->errors[] = get_string('validstartdate','local_trainingprogram', $excel);
                    $this->mfields[] = 'ProgramStartdate';
                    $this->errorcount++;

               } else {

                    $strtdate = $excel->programstartdate;
               }

                if(!strtotime($excel->programenddate)){

                   echo '<div class="local_trainingprogram_sync_error">'.get_string('validenddate','local_trainingprogram', $strings).'</div>'; 
                    $this->errors[] = get_string('validenddate','local_trainingprogram', $excel);
                    $this->mfields[] = 'ProgramEnddate';
                    $this->errorcount++;

               } else {

                    $enddate = $excel->programenddate;
               }
              
               if($strtdate < $currdate) {

                    echo '<div class="local_trainingprogram_sync_error">'.get_string('startdterror','local_trainingprogram', $strings).'</div>'; 
                    $this->errors[] = get_string('startdterror','local_trainingprogram', $excel);
                    $this->mfields[] = 'ProgramStartdate';
                    $this->errorcount++;
                }

                if($strtdate > $enddate) {

                    echo '<div class="local_trainingprogram_sync_error">'.get_string('stratdateexceedenddterror','local_trainingprogram', $strings).'</div>'; 
                    $this->errors[] = get_string('stratdateexceedenddterror','local_trainingprogram', $excel);
                    $this->mfields[] = 'ProgramStartdate';
                    $this->errorcount++;
                }

            }*/

        }

        //-------- check program enddate-------------------------------------
        if ( array_key_exists('programenddate', $excel) ) {
            if (empty($excel->programenddate)) {
                echo '<div class="local_trainingprogram_sync_error">'.get_string('enddatemissing','local_trainingprogram', $strings).'</div>'; 
                $this->errors[] = get_string('enddatemissing','local_trainingprogram', $excel);
                $this->mfields[] = 'ProgramEnddate';
                $this->errorcount++;
            } /*else {

                $strtdate = $excel->programstartdate;
                $enddate = $excel->programenddate;
                $currdate = date('Y-m-d');  
                if($enddate < $strtdate) {
                    echo '<div class="local_trainingprogram_sync_error">'.get_string('enddterror','local_trainingprogram', $strings).'</div>'; 
                    $this->errors[] = get_string('enddterror','local_trainingprogram', $excel);
                    $this->mfields[] = 'ProgramStartdate';
                    $this->errorcount++;
                }
            }*/
        }

         //-------- check program cost-------------------------------------
        // if ( array_key_exists('programcost', $excel) ) {
        //     if (empty($excel->programcost) && $excel->programcost !='0') {
        //         echo '<div class="local_trainingprogram_sync_error">'.get_string('programcostmissing','local_trainingprogram', $strings).'</div>'; 
        //         $this->errors[] = get_string('programcostmissing','local_trainingprogram', $excel);
        //         $this->mfields[] = 'ProgramCost';
        //         $this->errorcount++;
        //     } else {

        //         if($excel->programcost != '1' && $excel->programcost != '0') {
                  
        //            echo '<div class="local_trainingprogram_sync_error">'.get_string('programcostmustbeeitherzeroorone','local_trainingprogram', $strings).'</div>'; 
        //             $this->errors[] = get_string('programcostmustbeeitherzeroorone','local_trainingprogram', $excel);
        //             $this->mfields[] = 'ProgramCost';
        //             $this->errorcount++;

        //         }

        //         if($excel->programcost == '1') {

        //             if (empty($excel->programsellingprice) ||  $excel->programsellingprice =='0') {

        //                 echo '<div class="local_trainingprogram_sync_error">'.get_string('programsellingpricemissing','local_trainingprogram', $strings).'</div>'; 
        //                 $this->errors[] = get_string('programsellingpricemissing','local_trainingprogram', $excel);
        //                 $this->mfields[] = 'ProgramSellingPrice';
        //                 $this->errorcount++;

        //             }else if (empty($excel->programactualprice) ||  $excel->programactualprice =='0') {

        //                 echo '<div class="local_trainingprogram_sync_error">'.get_string('programactualpricemissing','local_trainingprogram', $strings).'</div>'; 
        //                 $this->errors[] = get_string('programactualpricemissing','local_trainingprogram', $excel);
        //                 $this->mfields[] = 'ProgramActualPrice';
        //                 $this->errorcount++;

        //             } elseif(!empty(trim($excel->programsellingprice)) && !preg_match('/^[0-9]*$/',trim($excel->programsellingprice))) {


        //                 echo '<div class="local_trainingprogram_sync_error">'.get_string('programsellingpricemissingacceptsnumeric','local_trainingprogram', $strings).'</div>'; 
        //                 $this->errors[] = get_string('programsellingpricemissingacceptsnumeric','local_trainingprogram', $excel);
        //                 $this->mfields[] = 'ProgramSellingPrice';
        //                 $this->errorcount++;


        //             } elseif(!empty(trim($excel->programactualprice)) && !preg_match('/^[0-9]*$/',trim($excel->programactualprice))) {

        //                 echo '<div class="local_trainingprogram_sync_error">'.get_string('programactualpricemissingacceptsnumeric','local_trainingprogram', $strings).'</div>'; 
        //                 $this->errors[] = get_string('programactualpricemissingacceptsnumeric','local_trainingprogram', $excel);
        //                 $this->mfields[] = 'ProgramActualPrice';
        //                 $this->errorcount++;


        //             } elseif($excel->programsellingprice < $excel->programactualprice) {

                        
        //                  echo '<div class="local_trainingprogram_sync_error">'.get_string('sellingpricecannotbelowerthanactual','local_trainingprogram', $strings).'</div>'; 
        //                 $this->errors[] = get_string('sellingpricecannotbelowerthanactual','local_trainingprogram', $excel);
        //                 $this->mfields[] = 'ProgramSellingPrice';
        //                 $this->errorcount++;

        //             }

        //        }
        //     }
        // }

        //-------- check program language-------------------------------------
        // if ( array_key_exists('programlanguages', $excel) ) {


        //     if (empty($excel->programlanguages) && $excel->programlanguages !='0') {
        //         echo '<div class="local_trainingprogram_sync_error">'.get_string('languagesmissing','local_trainingprogram', $strings).'</div>'; 
        //         $this->errors[] = get_string('languagesmissing','local_trainingprogram', $excel);
        //         $this->mfields[] = 'ProgramLanguages';
        //         $this->errorcount++;
        //     } else  {


        //         $languages = explode('*',$excel->programlanguages);

        //         foreach ($languages AS $language) {

        //             if($language != '0' && $language != '1') {
                       
        //                echo '<div class="local_trainingprogram_sync_error">'.get_string('needvalidlanguage','local_trainingprogram', $strings).'</div>'; 
        //                 $this->errors[] = get_string('needvalidlanguage','local_trainingprogram', $excel);
        //                 $this->mfields[] = 'ProgramLanguages';
        //                 $this->errorcount++;

        //             }
                    
                    
        //         }

        //     }
        // }
        
        //-------- check program trainingmethods-------------------------------------
        // if (array_key_exists('programtrainingmethods', $excel) ) {

        //     if (!empty($excel->programtrainingmethods) || $excel->programtrainingmethods =='0') {

        //         $programtrainingmethods = explode('*',$excel->programtrainingmethods);

        //         foreach ($programtrainingmethods AS $trainingmethod) {

        //             if($trainingmethod != '0' && $trainingmethod != '1' && $trainingmethod != '2' && $trainingmethod != '3') {
                       
        //                echo '<div class="local_trainingprogram_sync_error">'.get_string('needvalidtrainingmethod','local_trainingprogram', $strings).'</div>'; 
        //                 $this->errors[] = get_string('needvalidtrainingmethod','local_trainingprogram', $excel);
        //                 $this->mfields[] = 'ProgramTrainingMethods';
        //                 $this->errorcount++;

        //             }
                        
        //         }

        //     }
        // }


        //-------- check program eveolution method-------------------------------------


        // if (array_key_exists('programevolutionmethods', $excel) ) {

           
        //     if (!empty($excel->programevolutionmethods) || $excel->programevolutionmethods =='0') {

        //         $programevolutionmethods = explode('*',$excel->programevolutionmethods);

        //         foreach ($programevolutionmethods AS $evolutionmethod) {

        //             if($evolutionmethod != '0' && $evolutionmethod != '1') {
                       
        //                echo '<div class="local_trainingprogram_sync_error">'.get_string('needvalidevolutionmethod','local_trainingprogram', $strings).'</div>'; 
        //                 $this->errors[] = get_string('needvalidevolutionmethod','local_trainingprogram', $excel);
        //                 $this->mfields[] = 'ProgramEvolutionMethods';
        //                 $this->errorcount++;

        //             }
                        
        //         }

        //     }
        // }

         //-------- check program duration-------------------------------------
        // if ( array_key_exists('programduration', $excel) ) {
        //     if (empty($excel->programduration)  || $excel->programduration == '0') {
        //         echo '<div class="local_trainingprogram_sync_error">'.get_string('durationmissing','local_trainingprogram', $strings).'</div>'; 
        //         $this->errors[] = get_string('durationmissing','local_trainingprogram', $excel);
        //         $this->mfields[] = 'ProgramDuration';
        //         $this->errorcount++;
            
        //         if ($data['hour'] > 86400) {
        //             $errors['hour'] = get_string('hoursrestriction', 'local_trainingprogram');
        //         }
        //     } else {

        //         if($excel->programduration > '15') {
    
        //             echo '<div class="local_trainingprogram_sync_error">'.get_string('duration_err_notmorethan15missing','local_trainingprogram', $strings).'</div>'; 
        //             $this->errors[] = get_string('duration_err_notmorethan15missing','local_trainingprogram', $excel);
        //             $this->mfields[] = 'ProgramDuration';
        //             $this->errorcount++;
        //         } 
        //     }
        // } 

         //-------- check program running time (sec)-------------------------------------
        // if ( array_key_exists('programrunningtime', $excel) ) {
        //     if (empty($excel->programrunningtime) || $excel->programrunningtime == '0') {

        //         echo '<div class="local_trainingprogram_sync_error">'.get_string('hourmissing','local_trainingprogram', $strings).'</div>'; 
        //         $this->errors[] = get_string('hourmissing','local_trainingprogram', $excel);
        //         $this->mfields[] = 'ProgramRunningTime';
        //         $this->errorcount++;

        //     } else {
                

        //         if($excel->programrunningtime > '10') {
   
        //             echo '<div class="local_trainingprogram_sync_error">'.get_string('hourexceederror','local_trainingprogram', $strings).'</div>'; 
        //             $this->errors[] = get_string('hourexceederror','local_trainingprogram', $excel);
        //             $this->mfields[] = 'ProgramRunningTime';
        //             $this->errorcount++;
        //         } 
        //    }
        // } 

         //-------- check program sectors-------------------------------------
        if ( array_key_exists('programsectors', $excel) ) {
            // if (empty($excel->programsectors)) {
            //     echo '<div class="local_trainingprogram_sync_error">'.get_string('sectorsmemissing','local_trainingprogram', $strings).'</div>'; 
            //     $this->errors[] = get_string('sectorsmemissing','local_trainingprogram', $excel);
            //     $this->mfields[] = 'ProgramSectors';
            //     $this->errorcount++;
            // } else {

            //     $sectors =  $this->get_sectors($excel->programsectors);

            //     if(!$sectors){

            //        echo '<div class="local_trainingprogram_sync_error">'.get_string('sectorsnotmatchedwithrecords','local_trainingprogram', $strings).'</div>'; 
            //         $this->errors[] = get_string('sectorsnotmatchedwithrecords','local_trainingprogram', $excel);
            //         $this->mfields[] = 'CompetencyTypes';
            //         $this->errorcount++;
            //     }   
            // }

            if (!empty($excel->programsectors)) {

                $sectors =  $this->get_sectors($excel->programsectors);

                if(!$sectors){

                   echo '<div class="local_trainingprogram_sync_error">'.get_string('sectorsnotmatchedwithrecords','local_trainingprogram', $strings).'</div>'; 
                    $this->errors[] = get_string('sectorsnotmatchedwithrecords','local_trainingprogram', $excel);
                    $this->mfields[] = 'CompetencyTypes';
                    $this->errorcount++;
                }  

            }
        }

         //-------- check program level-------------------------------------
        if ( array_key_exists('programlevel', $excel) ) {
            /*if (empty($excel->programlevel)) {
                echo '<div class="local_trainingprogram_sync_error">'.get_string('clevelsmissing','local_trainingprogram', $strings).'</div>'; 
                $this->errors[] = get_string('clevelsmissing','local_trainingprogram', $excel);
                $this->mfields[] = 'ProgramLevel';
                $this->errorcount++;
            } else {

                $is_given_level_valid = $this->get_level($excel->programlevel);

                if(!$is_given_level_valid) {

                    echo '<div class="local_trainingprogram_sync_error">'.get_string('clevelsnotmatchedwithrecords','local_trainingprogram', $strings).'</div>'; 
                    $this->errors[] = get_string('clevelsnotmatchedwithrecords','local_trainingprogram', $excel);
                    $this->mfields[] = 'ProgramLevel';
                    $this->errorcount++;
                }

            }*/

            if (!empty($excel->programlevel)) {

                $is_given_level_valid = $this->get_level($excel->programlevel);

                if(!$is_given_level_valid) {

                    echo '<div class="local_trainingprogram_sync_error">'.get_string('clevelsnotmatchedwithrecords','local_trainingprogram', $strings).'</div>'; 
                    $this->errors[] = get_string('clevelsnotmatchedwithrecords','local_trainingprogram', $excel);
                    $this->mfields[] = 'ProgramLevel';
                    $this->errorcount++;
                } 

            }
        }

         //-------- check program competency types-------------------------------------
        if ( array_key_exists('competencytypes', $excel) ) {
            
           /* if (empty($excel->competencytypes)) {
               echo '<div class="local_trainingprogram_sync_error">'.get_string('competencytypesmissing','local_trainingprogram', $strings).'</div>'; 
                $this->errors[] = get_string('competencytypesmissing','local_trainingprogram', $excel);
                $this->mfields[] = 'CompetencyTypes';
                $this->errorcount++;
            } else {
            
                $is_given_level_valid = $this->get_level($excel->programlevel);

                if($is_given_level_valid) {

                   $programlevel =  $excel->programlevel;
                }

                $get_competencytypes = $this->get_competencytypes($programlevel);

                $ctypes= explode('*',$excel->competencytypes);

                foreach ($ctypes AS $type) {
                    if(!in_array($type,$get_competencytypes)) {
                       $typeerror = true;
                    } else {
                        $typeerror = false;
                        break;
                    }
                    
                }
                if($typeerror){

                   echo '<div class="local_trainingprogram_sync_error">'.get_string('competencytynotmatched','local_trainingprogram', $strings).'</div>'; 
                    $this->errors[] = get_string('competencytynotmatched','local_trainingprogram', $excel);
                    $this->mfields[] = 'CompetencyTypes';
                    $this->errorcount++;
                }
     
            }*/

            if (!empty($excel->competencytypes)) {

                $is_given_level_valid = $this->get_level($excel->programlevel);

                if($is_given_level_valid) {

                   $programlevel =  $excel->programlevel;
                }

                $get_competencytypes = $this->get_competencytypes($programlevel);

                $ctypes= explode('*',$excel->competencytypes);

                foreach ($ctypes AS $type) {
                    if(!in_array($type,$get_competencytypes)) {
                       $typeerror = true;
                    } else {
                        $typeerror = false;
                        break;
                    }
                    
                }
                if($typeerror){

                   echo '<div class="local_trainingprogram_sync_error">'.get_string('competencytynotmatched','local_trainingprogram', $strings).'</div>'; 
                    $this->errors[] = get_string('competencytynotmatched','local_trainingprogram', $excel);
                    $this->mfields[] = 'CompetencyTypes';
                    $this->errorcount++;
                }

            }


        }

        //-------- check program competencies-------------------------------------

        if ( array_key_exists('programcompetencies', $excel) ) {
           /* if (empty($excel->programcompetencies)) {
                echo '<div class="local_trainingprogram_sync_error">'.get_string('competencyandlevelsmissing','local_trainingprogram', $strings).'</div>'; 
                $this->errors[] = get_string('competencyandlevelsmissing','local_trainingprogram', $excel);
                $this->mfields[] = 'ProgramCompetencies';
                $this->errorcount++;
            } else {

        
                $is_given_level_valid = $this->get_level($excel->programlevel);
                if($is_given_level_valid) {
                   $programlevel =  $excel->programlevel;
                }
                $competencies = $this->get_competencies($programlevel, $excel->competencytypes);

                $ctypes= explode('*',$excel->programcompetencies);

                foreach ($ctypes AS $type) {
                    if(!in_array($type,$competencies)) {
                       $typeerror = true;
                    } else {
                        $typeerror = false;
                        break;
                    }
                    
                }
                if($typeerror){

                    echo '<div class="local_trainingprogram_sync_error">'.get_string('competenciesarenotvalid','local_trainingprogram', $strings).'</div>'; 
                    $this->errors[] = get_string('competenciesarenotvalid','local_trainingprogram', $excel);
                    $this->mfields[] = 'ProgramCompetencies';
                    $this->errorcount++;
                }

                
            }*/

            if (!empty($excel->programcompetencies)) {

                $is_given_level_valid = $this->get_level($excel->programlevel);

                if($is_given_level_valid) {
                   $programlevel =  $excel->programlevel;
                }
                $competencies = $this->get_competencies($programlevel, $excel->competencytypes);

                $ctypes= explode('*',$excel->programcompetencies);

                foreach ($ctypes AS $type) {
                    if(!in_array($type,$competencies)) {
                       $typeerror = true;
                    } else {
                        $typeerror = false;
                        break;
                    }
                    
                }
                if($typeerror){

                    echo '<div class="local_trainingprogram_sync_error">'.get_string('competenciesarenotvalid','local_trainingprogram', $strings).'</div>'; 
                    $this->errors[] = get_string('competenciesarenotvalid','local_trainingprogram', $excel);
                    $this->mfields[] = 'ProgramCompetencies';
                    $this->errorcount++;
                }

            }


        }

        if ( array_key_exists('programjobfamilies', $excel)) {

            if (!empty($excel->programjobfamilies)) {
    
                $sectors = $this->get_sectors($excel->programsectors);
                $jobfamilies = $this->get_jobfamilies($sectors);

                $givenjobfamilies= explode('*',$excel->programjobfamilies);
                foreach ($givenjobfamilies AS $jobfamily) {
                    if(!in_array($jobfamily,$jobfamilies)) {
                       $typeerror = true;
                    } else {
                        $typeerror = false;
                        break;
                    }
                    
                }
                if($typeerror){

                    echo '<div class="local_trainingprogram_sync_error">'.get_string('jobfamiliesarenotvalid','local_trainingprogram', $strings).'</div>'; 
                    $this->errors[] = get_string('jobfamiliesarenotvalid','local_trainingprogram', $excel);
                    $this->mfields[] = 'ProgramJobfamilies';
                    $this->errorcount++;
                }

                
            }
        }

        //-------- check program eveolution method-------------------------------------


        if (array_key_exists('programdiscount', $excel)) {

           /*if (empty($excel->programdiscount) &&  $excel->programdiscount !='0') {
                echo '<div class="local_trainingprogram_sync_error">'.get_string('programdiscountneed','local_trainingprogram', $strings).'</div>'; 
                $this->errors[] = get_string('programdiscountneed','local_trainingprogram', $excel);
                $this->mfields[] = 'ProgramDiscount';
                $this->errorcount++;

            } else {*/


            if(!empty($excel->programdiscount) && $excel->programdiscount != '0' && $excel->programdiscount != '1'  && $excel->programdiscount != '2') {
               
               echo '<div class="local_trainingprogram_sync_error">'.get_string('needvaliddiscountmethod','local_trainingprogram', $strings).'</div>'; 
                $this->errors[] = get_string('needvaliddiscountmethod','local_trainingprogram', $excel);
                $this->mfields[] = 'ProgramDiscount';
                $this->errorcount++;

            }
                        
            //}
        }
    
    } // end of required_fields_validations function
    /**
     * @method get_program_file
     * @todo Returns the uploaded file if it is present.
     * @param int $draftid
     * @return stored_file|null
     */
    public function get_program_file($draftid) {
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
        $return .= $OUTPUT->notification(get_string('uploadprogramssheet', 'local_trainingprogram'),'info');
        $return .= html_writer::start_tag('div', array('class'=> 'w-100 mt-5')).$OUTPUT->continue_button(new moodle_url('/local/trainingprogram/uploadprogram.php')).html_writer::end_tag('div');
        return $return;
    }

    public function get_sectors($programsectors = null) {
       global $DB,$USER;

       if(!empty($programsectors)) {
            $sectorsql = 'SELECT id FROM {local_sector} WHERE 1=1 ';
            $programsectors = explode('*',$programsectors);
            $sectorquery =array();
            foreach ($programsectors as $sector) {
                $sectorquery[] = "  code = '$sector' "; 
            }
            $sectorqueryparams =implode('OR',$sectorquery);
            $formsql = ' AND ('.$sectorqueryparams.')';
            $sectors = $DB->get_fieldset_sql($sectorsql.$formsql);
            return $sectors;
       }
        
    }

     public function get_level($level = null) {
       global $DB,$USER;

       if(!empty($level)) {
            $sql = " SELECT lc.id , lc.level AS fullname
                      FROM {local_competencies} lc 
                      WHERE FIND_IN_SET('$level', REPLACE(lc.level,'level',''))";
            $level = $DB->record_exists_sql($sql);


            return $level;
       }
        
    }

    public function get_jobroleid($programlevel) {
       global $DB,$USER;

   

       if(!empty($programlevel)) {
           
            $sql = " SELECT ljbl.id
                        FROM {local_jobrole_level} ljbl
                        JOIN {local_jobfamily} ljbf ON ljbf.id = ljbl.jobfamily
                        JOIN {local_segment} lsg ON lsg.id = ljbf.segmentid
                        JOIN {local_sector} as ls ON ls.id = lsg.sectorid 
                        WHERE REPLACE(ljbl.level,'Level ','') IN ('$programlevel') ";
            $jobroleids = $DB->get_fieldset_sql($sql);
            return $jobroleids;
       }
        
    }

    public function get_competencytypes($programlevel) {
       global $DB,$USER;

       if(!empty($programlevel)) {
               $sql = "SELECT lc.type
                  FROM {local_competencies} lc 
                  WHERE  FIND_IN_SET('$programlevel',REPLACE(lc.level,'level','')) ";
            $competenciestypesdata = $DB->get_fieldset_sql($sql);
            return $competenciestypesdata;
       }
        
    }


    public function get_competencies($programlevel, $competencytypes) {
       global $DB,$USER;

        if(!empty($programlevel) && !empty($competencytypes)) {

            $ctypes = str_replace('*', '\',\'',$competencytypes);
            $sql = "SELECT lc.code
                  FROM {local_competencies} lc 
                  WHERE FIND_IN_SET('$programlevel', REPLACE(lc.level,'level','')) AND lc.type IN ('{$ctypes}') ";

            $competencieslist = $DB->get_fieldset_sql($sql.$formsql);

           return $competencieslist;
       }
        
    }


    public function get_sector_ids($sectors) {
       global $DB,$USER; 

        $sectors = array_filter($sectors);

        if(!empty($sectors)) {

            $ctypequery = array();
            foreach ($sectors as $sector) {
                $ctypequery[] = " CONCAT(',',lc.code,',') LIKE CONCAT('%,','$sector',',%') "; 
            }
            $ctypequeeryparams =implode('OR',$ctypequery);
            $formsql = ' AND ('.$ctypequeeryparams.') ';

            $sql = "SELECT lc.id
                  FROM {local_sector} lc 
                  WHERE 1=1 ";
            $sectorids = $DB->get_fieldset_sql($sql.$formsql);

           return $sectorids;
       }
        
    }


    public function get_competency_ids($competencies) {
       global $DB,$USER; 

        $competencies = array_filter($competencies);

        if(!empty($competencies)) {

            $ctypequery = array();
            foreach ($competencies as $competency) {
                $ctypequery[] = " CONCAT(',',lc.code,',') LIKE CONCAT('%,','$competency',',%') "; 
            }
            $ctypequeeryparams =implode('OR',$ctypequery);
            $formsql = ' AND ('.$ctypequeeryparams.') ';

            $sql = "SELECT lc.id
                  FROM {local_competencies} lc 
                  WHERE 1=1 ";
            $competencyids = $DB->get_fieldset_sql($sql.$formsql);

           return $competencyids;
       }
        
    }

    public function get_jobfamilies($sectors) {
       global $DB,$USER;

        $sectors = array_filter($sectors);

       if(!empty($sectors)) {
            $sectorquery = array();
            foreach ($sectors as $sector) {
                $sectorquery[] = " CONCAT(',',ls.id,',') LIKE CONCAT('%,',$sector,',%') "; 
            }
            $sectoqueeryparams =implode('OR',$sectorquery);
            $formsql = ' AND ('.$sectoqueeryparams.') ';
            $sql = " SELECT ljbf.code
                        FROM {local_jobfamily} ljbf
                        JOIN {local_segment} lsg ON lsg.id = ljbf.segmentid
                        JOIN {local_sector} as ls ON ls.id = lsg.sectorid 
                        WHERE 1=1 ";
            $jobfamilyids = $DB->get_fieldset_sql($sql.$formsql);
            return $jobfamilyids;
       }
        
    }

    public function get_jobfamily_ids($jobfamilies) {
       global $DB,$USER; 

        $jobfamilies = array_filter($jobfamilies);

        if(!empty($jobfamilies)) {

            $ctypequery = array();
            foreach ($jobfamilies as $jobfamily) {
                $ctypequery[] = " CONCAT(',',lc.code,',') LIKE CONCAT('%,','$jobfamily',',%') "; 
            }
            $ctypequeeryparams =implode('OR',$ctypequery);
            $formsql = ' AND ('.$ctypequeeryparams.') ';

            $sql = "SELECT lc.id
                  FROM {local_jobfamily} lc 
                  WHERE 1=1 ";
            $jobfamilyids = $DB->get_fieldset_sql($sql.$formsql);

           return $jobfamilyids;
       }
        
    }



    
}

