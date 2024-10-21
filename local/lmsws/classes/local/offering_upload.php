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

class offering_upload  {

    protected $columns;

    protected $columnsmapping = array();

    private $users = array();

    protected $allowedcolumns = array('programcode','offeringstartdate','offeringenddate','offeringstarttime', 'offeringduration', 'offeringtype','seats','organization','sellingprice','actualprice','trainingmethod','meetingtype');
    /**
     * @method upload_offering_file file
     * @todo To process the uploaded CSV file and return the data
     * @param stored_file $file
     * @param string $encoding
     * @param string $delimiter
     * @param context $defaultcontext
     * @return array
     */

    public function upload_offering_file($file, $defaultcontext) {
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
        $uploadid = csv_import_reader::get_new_iid('offeringupload');
        $cir = new csv_import_reader($uploadid, 'offeringupload');

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
                $return .= html_writer::start_tag('div', array('class'=> 'w-100 mt-5')).$OUTPUT->continue_button(new moodle_url('/local/trainingprogram/uploadofferings.php')).html_writer::end_tag('div');
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
      
                $trainingid = $DB->get_field_sql("SELECT id FROM {local_trainingprogram} WHERE code = '$masterdata->programcode'");
                $masterdata->trainingid = (int) $trainingid;
                $masterdata->startdate = strtotime($masterdata->offeringstartdate);
                $masterdata->enddate= strtotime($masterdata->offeringenddate);
                $masterdata->type  = $masterdata->offeringtype;
                $masterdata->duration  = $masterdata->offeringduration * 60;
                $masterdata->availableseats  = $masterdata->seats;
                $masterdata->organization  = $masterdata->organization;
                $masterdata->sellingprice  = $masterdata->sellingprice;
                $masterdata->actualprice  = $masterdata->actualprice;
                $masterdata->trainingmethod  = $masterdata->trainingmethod;
                $masterdata->meetingtype  = $masterdata->meetingtype;
                $time = str_replace(':', ',', $masterdata->offeringstarttime);
                $star_ttime = explode(',', $time);
                $masterdata->starttime['hours'] = $star_ttime[0];
                $masterdata->starttime['minutes'] = $star_ttime[1];

              if($masterdata->trainingmethod == 'elearning') {
   
                   (new tp)->add_update_elearning_schedule_program($masterdata);

                } else {

                    (new tp)->add_update_schedule_program($masterdata);
                }
                

                $return .= $OUTPUT->notification(get_string('uploadofferingsheet', 'local_trainingprogram'),'info');
                
            
            }

	}
        
        $return .= html_writer::start_tag('div', array('class'=> 'w-100 mt-5')).$OUTPUT->continue_button(new moodle_url('/local/trainingprogram/uploadofferings.php')).html_writer::end_tag('div');
        
        return $return;
    }
    private function validate_columns() {
        global $DB;


        foreach ($this->columns as $i => $columnname) {
            if (in_array(strtolower($columnname), $this->allowedcolumns)) {
                $this->columnsmapping[$i] = strtolower($columnname);
            }
        }
        if (!in_array('programcode', $this->columnsmapping)) {
            $this->errors[] = get_string('code_missing', 'local_trainingprogram');
            return  $this->errors;
        }
    
        if (!in_array('offeringstartdate', $this->columnsmapping)) {
           
             $this->errors[] = get_string('offeringstartdate_missing', 'local_trainingprogram');
            return  $this->errors;
        }

        if (!in_array('offeringenddate', $this->columnsmapping)) {
            
             $this->errors[] = get_string('offeringenddate_missing', 'local_trainingprogram');
            return  $this->errors;
        }
        if (!in_array('offeringstarttime', $this->columnsmapping)) {
        
            $this->errors[] = get_string('offeringstarttime_missing', 'local_trainingprogram');
            return  $this->errors;
        }
         if (!in_array('offeringduration', $this->columnsmapping)) {
        
            $this->errors[] = get_string('offeringduration_missing', 'local_trainingprogram');
            return  $this->errors;
        }

        if (!in_array('offeringtype', $this->columnsmapping)) { 
            $this->errors[] = get_string('offeringtype_missing', 'local_trainingprogram');
            return  $this->errors;
        }
        if (!in_array('seats', $this->columnsmapping)) { 
            $this->errors[] = get_string('seats_missing', 'local_trainingprogram');
            return  $this->errors;
        }
        if (!in_array('organization', $this->columnsmapping)) { 
            $this->errors[] = get_string('organization_missing', 'local_trainingprogram');
            return  $this->errors;
        }
         if (!in_array('sellingprice', $this->columnsmapping)) { 
            $this->errors[] = get_string('sellingprice_memissing', 'local_trainingprogram');
            return  $this->errors;
        }
        if (!in_array('actualprice', $this->columnsmapping)) { 
            $this->errors[] = get_string('actualprice_missing', 'local_trainingprogram');
            return  $this->errors;
        }
       if (!in_array('trainingmethod', $this->columnsmapping)) { 
            $this->errors[] = get_string('trainingmethod_missing', 'local_trainingprogram');
            return  $this->errors;
        }
        if (!in_array('meetingtype', $this->columnsmapping)) { 
            $this->errors[] = get_string('meetingtype_missing', 'local_trainingprogram');
            return  $this->errors;
        }
        return false;
    }

    private function required_fields_validations($excel,$option=0){
        global $DB;
        $strings = new stdClass;
        $strings->excel_line_number = $this->excel_line_number;
        //-----------check program code-----------------------------------
        if (array_key_exists('programcode', $excel) ) {
            if (empty($excel->programcode)) {
                echo '<div class="local_trainingprogram_sync_error">'.get_string('programcodemissing','local_trainingprogram', $strings).'</div>'; 
                $this->errors[] =  get_string('programcodemissing', 'local_trainingprogram',$strings);
                $this->mfields[] = 'ProgramCode';
                $this->errorcount++;
            } else {

                $record = $DB->record_exists_sql("SELECT id FROM {local_trainingprogram} WHERE code = '$excel->programcode'");

                if(!$record) {
                  
                echo '<div class="local_trainingprogram_sync_error">'.get_string('programcodeisnotmatched','local_trainingprogram', $strings).'</div>'; 
                $this->errors[] =  get_string('programcodeisnotmatched', 'local_trainingprogram',$strings);
                $this->mfields[] = 'ProgramCode';
                $this->errorcount++;

                }
            }
        }

        //-------- check offering startdate-------------------------------------
        if ( array_key_exists('offeringstartdate', $excel) ) {

            
            if (empty($excel->offeringstartdate)) {
                echo '<div class="local_trainingprogram_sync_error">'.get_string('offeringstartdatemissing','local_trainingprogram', $strings).'</div>'; 
                $this->errors[] = get_string('offeringstartdatemissing','local_trainingprogram', $excel);
                $this->mfields[] = 'OfferingStartdate';
                $this->errorcount++;
            } 
            // } else {

            //    $currdate = date('Y-m-d');
            //    if(!strtotime($excel->offeringstartdate)){

            //        echo '<div class="local_trainingprogram_sync_error">'.get_string('validofferingstartdate','local_trainingprogram', $strings).'</div>'; 
            //         $this->errors[] = get_string('validofferingstartdate','local_trainingprogram', $excel);
            //         $this->mfields[] = 'OfferingStartdate';
            //         $this->errorcount++;

            //    } else {

            //         $strtdate = $excel->offeringstartdate;
            //    }

            //     if(!strtotime($excel->offeringenddate)){

            //        echo '<div class="local_trainingprogram_sync_error">'.get_string('validofferingenddate','local_trainingprogram', $strings).'</div>'; 
            //         $this->errors[] = get_string('validofferingenddate','local_trainingprogram', $excel);
            //         $this->mfields[] = 'OfferingEnddate';
            //         $this->errorcount++;

            //    } else {

            //         $enddate = $excel->offeringenddate;
            //    }

            //    $programrecord = $DB->get_record_sql("SELECT * FROM {local_trainingprogram} WHERE code = '$excel->programcode'");

            //    $programstartdate = date('Y-m-d',$programrecord->availablefrom);
            //    $programenddate = date('Y-m-d',$programrecord->availableto);


            //     if($strtdate < $programstartdate || $strtdate > $programenddate) {
            //        echo '<div class="local_trainingprogram_sync_error">'.get_string('buofferingstartlessthanprogram','local_trainingprogram', $strings).'</div>'; 
            //         $this->errors[] = get_string('buofferingstartlessthanprogram','local_trainingprogram', $excel);
            //         $this->mfields[] = 'OfferingStartdate';
            //         $this->errorcount++;

            //     }
              
            //    if($strtdate < $currdate) {

            //         echo '<div class="local_trainingprogram_sync_error">'.get_string('startdterror','local_trainingprogram', $strings).'</div>'; 
            //         $this->errors[] = get_string('offeringstartdaterror','local_trainingprogram', $excel);
            //         $this->mfields[] = 'OfferingStartdate';
            //         $this->errorcount++;
            //     }

            //     if($strtdate > $enddate) {

            //         echo '<div class="local_trainingprogram_sync_error">'.get_string('stratdateexceedenddterror','local_trainingprogram', $strings).'</div>'; 
            //         $this->errors[] = get_string('offeringstartdaterrorexceedenddterror','local_trainingprogram', $excel);
            //         $this->mfields[] = 'OfferingStartdate';
            //         $this->errorcount++;
            //     }

            // }

        }

        //-------- check offering enddate-------------------------------------
        if ( array_key_exists('offeringenddate', $excel) ) {
            if (empty($excel->offeringenddate)) {
                echo '<div class="local_trainingprogram_sync_error">'.get_string('enddatemissing','local_trainingprogram', $strings).'</div>'; 
                $this->errors[] = get_string('offeringenddatemissing','local_trainingprogram', $excel);
                $this->mfields[] = 'OfferingEnddate';
                $this->errorcount++;
            } /*else {

                $strtdate = $excel->offeringstartdate;
                $enddate = $excel->offeringenddate;
                $currdate = date('Y-m-d');  
                if($enddate < $strtdate) {
                    echo '<div class="local_trainingprogram_sync_error">'.get_string('offeringenddterror','local_trainingprogram', $strings).'</div>'; 
                    $this->errors[] = get_string('offeringenddterror','local_trainingprogram', $excel);
                    $this->mfields[] = 'OfferingStartdate';
                    $this->errorcount++;
                }
            }*/
        }

         //-------- check offering start time-------------------------------------
        // if ( array_key_exists('offeringstarttime', $excel) ) {
        //     if (empty($excel->offeringstarttime)) {
        //         echo '<div class="local_trainingprogram_sync_error">'.get_string('offeringstarttimemissing','local_trainingprogram', $strings).'</div>'; 
        //         $this->errors[] = get_string('offeringstarttimemissing','local_trainingprogram', $excel);
        //         $this->mfields[] = 'OfferingStartTime';
        //         $this->errorcount++;
        //     }  else {

        //         $time = str_replace(':', ',', $excel->offeringstarttime);
        //         $star_ttime = explode(',', $time);
        //         if($star_ttime['0'] > '23') {

        //             echo '<div class="local_trainingprogram_sync_error">'.get_string('offeringstarttimehournotmorethan24','local_trainingprogram', $strings).'</div>'; 
        //             $this->errors[] = get_string('offeringstarttimehournotmorethan24','local_trainingprogram', $excel);
        //             $this->mfields[] = 'OfferingStartTime';
        //             $this->errorcount++;
        //         }

        //         if($star_ttime['1'] > '59') {

        //             echo '<div class="local_trainingprogram_sync_error">'.get_string('offeringstarttimeminutenotmorethan24','local_trainingprogram', $strings).'</div>'; 
        //             $this->errors[] = get_string('offeringstarttimeminutenotmorethan24','local_trainingprogram', $excel);
        //             $this->mfields[] = 'OfferingStartTime';
        //             $this->errorcount++;
        //         }
        //     }
        // }
         //-------- check offering duration-------------------------------------
        // if ( array_key_exists('offeringduration', $excel) ) {
        //     if (empty($excel->offeringduration)  || $excel->programduration == '0') {
        //         echo '<div class="local_trainingprogram_sync_error">'.get_string('offeringduration','local_trainingprogram', $strings).'</div>'; 
        //         $this->errors[] = get_string('offeringdurationmissing','local_trainingprogram', $excel);
        //         $this->mfields[] = 'OfferingDuration';
        //         $this->errorcount++;
            
        //     } else {

        //         $durationinsecodns = $excel->offeringduration * 60;

        //         if($durationinsecodns > 86400) {
    
        //             echo '<div class="local_trainingprogram_sync_error">'.get_string('budurationexceed','local_trainingprogram', $strings).'</div>'; 
        //             $this->errors[] = get_string('budurationexceed','local_trainingprogram', $excel);
        //             $this->mfields[] = 'OfferingDuration';
        //             $this->errorcount++;
        //         } 
        //     }
        // } 

        //-------- check offering type-------------------------------------
       /* if ( array_key_exists('offeringtype', $excel) ) {


            if (empty($excel->offeringtype) && $excel->offeringtype !='0') {
                echo '<div class="local_trainingprogram_sync_error">'.get_string('offeringtypemissing','local_trainingprogram', $strings).'</div>'; 
                $this->errors[] = get_string('offeringtypemissing','local_trainingprogram', $excel);
                $this->mfields[] = 'OfferingType';
                $this->errorcount++;
            } else  {


                if($excel->offeringtype != '0' && $excel->offeringtype != '1' && $excel->offeringtype != '2') {
                   
                   echo '<div class="local_trainingprogram_sync_error">'.get_string('needvalidofferingtype','local_trainingprogram', $strings).'</div>'; 
                    $this->errors[] = get_string('needvalidofferingtype','local_trainingprogram', $excel);
                    $this->mfields[] = 'OfferingType';
                    $this->errorcount++;

                }
                
            }
        }*/
        
        //-------- check offering seats-------------------------------------
        // if (array_key_exists('seats', $excel)) {
        //     if (empty($excel->seats) && $excel->seats =='0') {
        //         echo '<div class="local_trainingprogram_sync_error">'.get_string('offeringseatsmissing','local_trainingprogram', $strings).'</div>'; 
        //         $this->errors[] = get_string('offeringseatsmissing','local_trainingprogram', $excel);
        //         $this->mfields[] = 'Seats';
        //         $this->errorcount++;
        //     } else  {


        //     if(!empty(trim($excel->seats)) && !preg_match('/^[0-9]*$/',trim($excel->seats))) {
                
        //           echo '<div class="local_trainingprogram_sync_error">'.get_string('validseatsrequired','local_trainingprogram', $strings).'</div>'; 
        //         $this->errors[] = get_string('validseatsrequired','local_trainingprogram', $excel);
        //         $this->mfields[] = 'Seats';
        //         $this->errorcount++;
        //     }

        // }


        //-------- check organization-------------------------------------


        // if (array_key_exists('organization', $excel) ) {
           
        //     if (!empty($excel->organization)) {

        //        $record = $DB->record_exists_sql("SELECT id FROM {local_organization} WHERE shortname = '$excel->organization'");

        //         if(!$record) {
                       
        //                echo '<div class="local_trainingprogram_sync_error">'.get_string('needvalidorganization','local_trainingprogram', $strings).'</div>'; 
        //                 $this->errors[] = get_string('needvalidorganization','local_trainingprogram', $excel);
        //                 $this->mfields[] = 'Organization';
        //                 $this->errorcount++;

        //             }
                        
        //         }

        //     }
        // }

        

         //-------- check offering sellingprice (sec)-------------------------------------
        // if ( array_key_exists('sellingprice', $excel) ) {
        //     if (empty($excel->sellingprice)) {

        //         echo '<div class="local_trainingprogram_sync_error">'.get_string('sellingpricemissing','local_trainingprogram', $strings).'</div>'; 
        //         $this->errors[] = get_string('sellingpricemissing','local_trainingprogram', $excel);
        //         $this->mfields[] = 'SellingPrice';
        //         $this->errorcount++;

        //     } else {

        //         if(!empty(trim($excel->sellingprice)) && !preg_match('/^[0-9]*$/',trim($excel->sellingprice))) {


        //             echo '<div class="local_trainingprogram_sync_error">'.get_string('buvalidsellingpricerequired','local_trainingprogram', $strings).'</div>'; 
        //             $this->errors[] = get_string('buvalidsellingpricerequired','local_trainingprogram', $excel);
        //             $this->mfields[] = 'SellingPrice';
        //             $this->errorcount++;


        //         }

        //         if(trim($excel->actualprice) >  trim($excel->sellingprice)){


        //             echo '<div class="local_trainingprogram_sync_error">'.get_string('busellingpricepricehigher','local_trainingprogram', $strings).'</div>'; 
        //             $this->errors[] = get_string('busellingpricepricehigher','local_trainingprogram', $excel);
        //             $this->mfields[] = 'SellingPrice';
        //             $this->errorcount++;

        //         }
           
    
        //    }
        // } 

         //-------- check offering actualprice (sec)-------------------------------------
        // if ( array_key_exists('actualprice', $excel) ) {
        //     if (empty($excel->actualprice)) {

        //         echo '<div class="local_trainingprogram_sync_error">'.get_string('actualpricemissing','local_trainingprogram', $strings).'</div>'; 
        //         $this->errors[] = get_string('actualpricemissing','local_trainingprogram', $excel);
        //         $this->mfields[] = 'ActualPrice';
        //         $this->errorcount++;

        //     } else {

        //         if(!empty(trim($excel->actualprice)) && !preg_match('/^[0-9]*$/',trim($excel->actualprice))) {


        //             echo '<div class="local_trainingprogram_sync_error">'.get_string('buvalidactualpricerequired','local_trainingprogram', $strings).'</div>'; 
        //             $this->errors[] = get_string('buvalidactualpricerequired','local_trainingprogram', $excel);
        //             $this->mfields[] = 'ActualPrice';
        //             $this->errorcount++;


        //         }
    
        //    }
        // } 

        //-------- check offering training method-------------------------------------
        if ( array_key_exists('trainingmethod', $excel) ) {
            if (empty($excel->trainingmethod)) {

                echo '<div class="local_trainingprogram_sync_error">'.get_string('trainingmethodmissing','local_trainingprogram', $strings).'</div>'; 
                $this->errors[] = get_string('trainingmethodmissing','local_trainingprogram', $excel);
                $this->mfields[] = 'TrainingMethod';
                $this->errorcount++;

            } /*else {

               
                if($excel->trainingmethod != 'online' && $excel->trainingmethod != 'offline' && $excel->trainingmethod != 'elearning') {
                   
                   echo '<div class="local_trainingprogram_sync_error">'.get_string('needvalidtrainingmethod','local_trainingprogram', $strings).'</div>'; 
                    $this->errors[] = get_string('needvalidtrainingmethod','local_trainingprogram', $excel);
                    $this->mfields[] = 'TrainingMethod';
                    $this->errorcount++;

                }

               $programrecord = $DB->get_record_sql("SELECT * FROM {local_trainingprogram} WHERE code = '$excel->programcode'");

               $programstartdate = date('Y-m-d',$programrecord->availablefrom);
               $programenddate = date('Y-m-d',$programrecord->availableto);

                if($excel->trainingmethod != 'elearning') {

                     if($excel->offeringenddate < $programstartdate || $excel->offeringenddate  > $programenddate) {
                    
                        echo '<div class="local_trainingprogram_sync_error">'.get_string('buofferingendlessthanprogram','local_trainingprogram', $strings).'</div>'; 
                        $this->errors[] = get_string('buofferingendlessthanprogram','local_trainingprogram', $excel);
                        $this->mfields[] = 'OfferingEnddate';
                        $this->errorcount++;
                    }

                    if($excel->offeringstartdate > $excel->offeringenddate){

                        echo '<div class="local_trainingprogram_sync_error">'.get_string('todatelower','local_trainingprogram', $strings).'</div>'; 
                        $this->errors[] = get_string('todatelower','local_trainingprogram', $excel);
                        $this->mfields[] = 'OfferingEnddate';
                        $this->errorcount++;
                    }
                }
           
    
           }*/
        } 

        //-------- check offering meeting type-------------------------------------
        if ( array_key_exists('meetingtype', $excel) ) {
            if (!empty($excel->trainingmethod) && $excel->trainingmethod == 'online') {

                if($excel->meetingtype != '1' && $excel->meetingtype != '2' && $excel->meetingtype != '3') {
                   

                    echo '<div class="local_trainingprogram_sync_error">'.get_string('needvalidmeetingtype','local_trainingprogram', $strings).'</div>'; 
                    $this->errors[] = get_string('needvalidmeetingtype','local_trainingprogram', $excel);
                    $this->mfields[] = 'MeetingType';
                    $this->errorcount++;
                }

            } 
        } 

       
        



    
    } // end of required_fields_validations function
    /**
     * @method get_offering_file
     * @todo Returns the uploaded file if it is present.
     * @param int $draftid
     * @return stored_file|null
     */
    public function get_offering_file($draftid) {
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
        $return .= $OUTPUT->notification(get_string('uploadofferingsheet', 'local_trainingprogram'),'info');
        $return .= html_writer::start_tag('div', array('class'=> 'w-100 mt-5')).$OUTPUT->continue_button(new moodle_url('/local/trainingprogram/uploadofferings.php')).html_writer::end_tag('div');
        return $return;
    }

   
    


    
}

