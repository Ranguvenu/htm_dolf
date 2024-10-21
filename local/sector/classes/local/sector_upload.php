<?php

/**
 * Sector upload masterdata.
 *
 * @package    local_sector
 * 
 */
namespace local_sector\local;
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


class sector_upload  {

    protected $columns;

    protected $columnsmapping = array();


    protected $allowedcolumns = array('OLD_ID', 'EN_Name', 'AR_Name', 'Description', 'TYPE', 'CODE', 'PARENT_CODE', 'SHARED', 'LEVEL','ctype','Competencies');
    
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

        $uploadid = csv_import_reader::get_new_iid('sectorupload');
        $cir = new csv_import_reader($uploadid, 'sectorupload');


        //Where the magic happens, removed summary content from CSV to start with actual upload.
        $length = strpos($content, 'date');
        $content = substr_replace($content, '', 0, $length);

        /**
         * Actual upload starts from here
         */
        $uploadsector = new \local_sector\form\uploadsector_form();
        $content = $uploadsector->get_file_content('sectorfile');

        $readcount = $cir->load_csv_content($content, 'utf-8', 'comma');
        unset($content);
        if (!$readcount) {
            throw new moodle_exception('csvloaderror', 'error',$PAGE->url,  $cir->get_error());
        }
        if(!is_siteadmin()){
            throw new moodle_exception('youdonthabepermissiontouploaddata', 'local_sector');
        }


        $this->columns = $cir->get_columns();
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
            } else {

                if(strtolower($masterdata->TYPE) == 'sector') {

                    $code = trim($masterdata->CODE);
                    $sectorcode = $DB->get_record_sql("SELECT * FROM {local_sector} WHERE code ='{$code}'");
                    if(empty($sectorcode)){
                       $sector->id = $DB->insert_record('local_sector', ['old_id' => $masterdata->OLD_ID, 'title' => $masterdata->EN_Name, 'titlearabic' => $masterdata->AR_Name,'code' => trim($masterdata->CODE),'timemodified'=>time(),'usermodified'=>$USER->id]); 
                       $eventparams = array('context' => \context_system::instance(),'objectid'=>$sector->id,'other'=>array('name'=>$masterdata->EN_Name,'family'=>'Sector'));
                        $event = \local_sector\event\row_inserted::create($eventparams);// ... code that may add some record snapshots
                        $event->trigger();
                    }else{
                        $sectorname = !empty($masterdata->EN_Name) ? $masterdata->EN_Name : $sectorcode->title;
                        $sector->id =  $DB->update_record('local_sector', ['id'=>$sectorcode->id, 'old_id' => $masterdata->OLD_ID, 'title' => $sectorname,'titlearabic' => $masterdata->AR_Name, 'code' => trim($masterdata->CODE),'timemodified'=>time(),'usermodified'=>$USER->id]);
                        $sector->id = $sectorcode->id;
                    }

                } elseif(strtolower($masterdata->TYPE) == 'segment') {
                    $parent_code = trim($masterdata->PARENT_CODE);
                    $code = trim($masterdata->CODE);
                    $sectorid = $DB->get_field_sql("SELECT id FROM {local_sector} WHERE code ='{$parent_code}'");
                    $recordexists = $DB->record_exists_sql("SELECT id FROM {local_sector} WHERE code ='{$parent_code}'");

                    if($recordexists) {

                        $segmentcode = $DB->get_record_sql("SELECT * FROM {local_segment} WHERE TRIM(code)='{$code}' AND sectorid = {$sectorid}");
                        if(empty($segmentcode)){
                           $segment->id = $DB->insert_record('local_segment', ['old_id' => $masterdata->OLD_ID, 'title' => $masterdata->EN_Name, 'titlearabic' => $masterdata->AR_Name,'code' => trim($masterdata->CODE), 'sectorid' => $sectorid, 'timemodified'=>time(),'usermodified'=>$USER->id]); 
                           $eventparams = array('context' => \context_system::instance(),'objectid'=>$segment->id,'other'=>array('name'=>$masterdata->EN_Name,'family'=>'Segment'));
                            $event = \local_sector\event\row_inserted::create($eventparams);// ... code that may add some record snapshots
                            $event->trigger();
                        }else{
                            $segmentname = !empty($masterdata->EN_Name) ? $masterdata->EN_Name : $segmentcode->title;
                            $segment->id =  $DB->update_record('local_segment', ['id'=>$segmentcode->id, 'old_id' => $masterdata->OLD_ID, 'title' => $segmentname,'titlearabic' => $masterdata->AR_Name, 'code' => trim($masterdata->CODE), 'sectorid' => $sectorid,'timemodified'=>time(),'usermodified'=>$USER->id]);
                            $segment->id = $segmentcode->id;
                        }

                    }
                    
                } elseif(strtolower($masterdata->TYPE) == 'jobfamily') {
                    $parent_code = trim($masterdata->PARENT_CODE);
                    $segmentids = array();
                    $segmentid = $DB->get_field_sql("SELECT id FROM {local_segment} WHERE code='{$parent_code}'");

                    $recordexists = $DB->record_exists_sql("SELECT id FROM {local_segment} WHERE code ='{$parent_code}'");
                    if($segmentid){
                        $segmentids[] = $segmentid;
                    }
                    if($masterdata->SHARED > 0){

                        if($recordexists) {


                             $sectorcode = $DB->get_field_sql("SELECT s.code FROM {local_sector} as s JOIN {local_segment} as sg ON sg.sectorid=s.id WHERE sg.id=$segmentid");
                            $segmentid = 0;
                            $pos = strpos(trim($masterdata->CODE), $sectorcode);
                            if ($pos !== false) {
                                $code = substr_replace(trim($masterdata->CODE), "",0, strlen($sectorcode));
                            }


                        } else {

                            $code = trim($masterdata->CODE);
                        }

                       
                    }else{
                        $code = trim($masterdata->CODE);
                    }

                    //$code = trim($masterdata->CODE);

                    $jobfamilycode = $DB->get_record_sql("SELECT * FROM {local_jobfamily} WHERE TRIM(code)='{$code}'");
                    if($jobfamilycode->segmentid != $segmentid){
                        $segmentids[] = $jobfamilycode->segmentid;
                    }
                    $segmentids = array_filter($segmentids);

            
                      if(($masterdata->SHARED > 0) || ($masterdata->SHARED  == 0 &&  $recordexists)) {
                        if(empty($jobfamilycode)){

                               $jobfamilydata = new stdClass();
                               $jobfamilydata->old_id = $masterdata->OLD_ID; 
                               $jobfamilydata->familyname = $masterdata->EN_Name; 
                               $jobfamilydata->familynamearabic = $masterdata->AR_Name;
                               $jobfamilydata->code = $code; 
                               $jobfamilydata->segmentid = ($masterdata->SHARED) ? 0: implode(',', $segmentids); 
                               $jobfamilydata->description = $masterdata->Description; 
                               $jobfamilydata->shared = ($masterdata->SHARED > 0) ? 1 : 0; 
                               $jobfamilydata->timemodified=time();
                               $jobfamilydata->usermodified=$USER->id;
                               $jobfamily->id = $DB->insert_record('local_jobfamily', $jobfamilydata); 
                               $eventparams = array('context' => \context_system::instance(),'objectid'=>$jobfamily->id,'other'=>array('name'=>$jobfamily->EN_Name,'family'=>'Jobfamily'));
                                $event = \local_sector\event\row_inserted::create($eventparams);// ... code that may add some record snapshots
                                $event->trigger();
                          

                        }else{

                            
                            $sectorname = !empty($masterdata->EN_Name) ? $masterdata->EN_Name : $jobfamilycode->familyname;
                            $jobfamilydata = new stdClass();

                            $jobfamilydata->id=$jobfamilycode->id; 
                            $jobfamilydata->old_id = $masterdata->OLD_ID; 
                            $jobfamilydata->familyname = $sectorname;
                            $jobfamilydata->familynamearabic = $masterdata->AR_Name; 
                            $jobfamilydata->code = $code; 
                            $jobfamilydata->segmentid = ($masterdata->SHARED) ? 0: implode(',', $segmentids); 
                            $jobfamilydata->description = $masterdata->Description; 
                            $jobfamilydata->shared = ($masterdata->SHARED > 0) ? 1 : 0; 
                            $jobfamilydata->timemodified = time();
                            $jobfamilydata->usermodified = $USER->id;
                            $DB->update_record('local_jobfamily', $jobfamilydata);
                            $jobfamily->id = $jobfamilycode->id;
                        }

                      }  
                    

                } elseif(strtolower($masterdata->TYPE) == 'jobrole') {
                    $parent_code = trim($masterdata->PARENT_CODE);
                    $code = trim($masterdata->CODE);
                    $jobfamily = $DB->get_record_sql("SELECT id,shared FROM {local_jobfamily} WHERE code='{$parent_code}'");
                    if(!$jobfamily){
                        $sectorcodes = $DB->get_fieldset_select('local_sector', 'code', '');
                        foreach($sectorcodes as $scode){
                           $pos = strpos($parent_code, $scode);
                          if ($pos !== false && $pos === 0 ) {
                                $parent_code = substr_replace(trim($parent_code), "",0, strlen($scode));
                                $jobfamily = $DB->get_record_sql("SELECT id,code,shared FROM {local_jobfamily} WHERE TRIM(code)='{$parent_code}'");
                                break;
                            }
                        }
                    }
                    
                    if($jobfamily->shared){
                        $sectorcode = $DB->get_fieldset_sql("SELECT s.code FROM {local_sector} as s  
                                                            JOIN {local_segment} as sg ON sg.sectorid=s.id 
                                                            JOIN {local_jobfamily} as jf ON (FIND_IN_SET(sg.id, jf.segmentid) OR jf.segmentid=0)
                                                           WHERE jf.code='$jobfamily->code' group by s.code");
                         
                            foreach($sectorcodes as $scode){
                        $pos = strpos($code, $scode);
                        if ($pos !== false && $pos === 0 ) {
                            echo "here";
                           echo $code = substr_replace($code, "",0, strlen($scode));
                           break;
                        }
                    }
                    }

                    if($jobfamily->id != ''){
                        $jobrolecode = $DB->get_record_sql("SELECT * FROM {local_jobrole_level} WHERE TRIM(code)='{$code}' AND jobfamily = $jobfamily->id ");
                     
                    
                        if(empty($jobrolecode)){
                           $jobrole = new stdClass();
                           $jobrole->old_id = $masterdata->OLD_ID; 
                           $jobrole->title = $masterdata->EN_Name; 
                           $jobrole->titlearabic = $masterdata->AR_Name;
                           $jobrole->code = $code; 
                           $jobrole->jobfamily = $jobfamily->id; 
                           $jobrole->description = $masterdata->Description; 
                           $jobrole->shared = $masterdata->SHARED; 
                           $jobrole->ctypes = str_replace("*",",",$masterdata->ctype);
                           $jobrole->level = 'level'.$masterdata->LEVEL; 
                           $jobrole->timemodified = time();
                           $jobrole->usermodified = $USER->id;

                           /*$competencyids = $this->get_competency_ids(explode('*',$masterdata->competencies));
                           $masterdata->competencies = implode(',',$competencyids);*/
                           
                           $competencyids = $this->get_competency_ids(explode('*',$masterdata->Competencies),$masterdata->ctype,$masterdata->LEVEL);
                           
                            $jobrole->competencies = implode(',',$competencyids);

                           $jobrole_level->id = $DB->insert_record('local_jobrole_level', $jobrole);
                           $eventparams = array('context' => \context_system::instance(),'objectid'=>$jobrole_level->id,'other'=>array('name'=>$masterdata->EN_Name,'family'=>'Jobrole'));
                            $event = \local_sector\event\row_inserted::create($eventparams);// ... code that may add some record snapshots
                            $event->trigger(); 
                        }else{
                            $jobrole = new stdClass();
                            $sectorname = !empty($masterdata->EN_Name) ? $masterdata->EN_Name : $jobrolecode->title;
                            $jobrole->id=$jobrolecode->id; 
                            $jobrole->old_id = $masterdata->OLD_ID; 
                            $jobrole->title = $sectorname;
                            $jobrole->titlearabic = $masterdata->AR_Name; 
                            $jobrole->code = $code; 
                            $jobrole->jobfamily = $jobfamily->id; 
                            $jobrole->description = $masterdata->Description; 
                            $jobrole->shared = $masterdata->SHARED; 
                            $jobrole->ctypes = str_replace("*",",",$masterdata->ctype);
                            $jobrole->level = 'level'.$masterdata->LEVEL; 
                            $jobrole->timemodified = time();
                            $jobrole->usermodified = $USER->id;

                            /*$competencyids = $this->get_competency_ids(explode('*',$masterdata->Competencies));
                            $jobrole->competencies = implode(',',$competencyids);*/
                            $competencyids = $this->get_competency_ids(explode('*',$masterdata->Competencies),$masterdata->ctype,$masterdata->LEVEL);
                            $jobrole->competencies = implode(',',$competencyids);
                            $DB->update_record('local_jobrole_level', $jobrole);
                            $jobrole_level->id = $jobrolecode->id;
                        }
                    }
                }
            }            
        }
       
        $return .= $OUTPUT->notification(get_string('uploadsectorsheet', 'local_sector'),'info');
        $return .= html_writer::start_tag('div', array('class'=> 'w-100 mt-5')).$OUTPUT->continue_button(new moodle_url('/local/sector/uploadsector.php')).html_writer::end_tag('div');
        echo $return;
    }
    

    private function required_fields_validations($excel,$option=0){
        global $DB;
       
        // check OLD_ID
        if (array_key_exists('OLD_ID', $excel) ) {
            if (empty($excel->OLD_ID)) {
                $strings = new stdClass;
                $strings->excel_line_number = $this->excel_line_number;
                $strings->column = 'OLD_ID';                
                echo '<div class="local_sector_sync_error">'.get_string('emptymsg','local_sector', $strings).'</div>'; 
                $this->errors[] = get_string('emptymsg','local_sector', $strings);
                $this->mfields[] = 'OLD_ID';
                $this->errorcount++;
                $eventparams = array('context' => \context_system::instance(),'objectid'=>$strings->excel_line_number,'other'=>array('name'=>'OLD_ID'));
                $event = \local_sector\event\missing_field::create($eventparams);// ... code that may add some record snapshots
                $event->trigger();
            }
        } else {
           echo '<div class="local_sector_sync_error">'.get_string('error_oldidcolumn_heading', 'local_sector').'</div>'; 
           $this->errormessage = get_string('error_oldidcolumn_heading', 'local_sector');
           $this->errorcount++;
            $eventparams = array('context' => \context_system::instance(),'other'=>array('name'=>'OLD_ID'));
            $event = \local_sector\event\header_missing::create($eventparams);// ... code that may add some record snapshots
            $event->trigger();
        }

        // check EN_Name
        if (array_key_exists('EN_Name', $excel) ) {
            
            if (empty($excel->EN_Name)) {
                $strings = new stdClass;
                $strings->excel_line_number = $this->excel_line_number;
                $strings->column = 'EN_Name';                
                echo '<div class="local_sector_sync_error">'.get_string('emptymsg','local_sector', $strings).'</div>'; 
                $this->errors[] = get_string('emptymsg','local_sector', $strings);
                $this->mfields[] = 'EN_Name';
                $this->errorcount++;
                $eventparams = array('context' => \context_system::instance(),'objectid'=>$strings->excel_line_number,'other'=>array('name'=>'EN_Name'));
                $event = \local_sector\event\missing_field::create($eventparams);// ... code that may add some record snapshots
                $event->trigger();
            }

        } else {
           echo '<div class="local_sector_sync_error">'.get_string('error_ennamecolumn_heading', 'local_sector').'</div>'; 
           $this->errormessage = get_string('error_ennamecolumn_heading', 'local_sector');
           $this->errorcount++;
            $eventparams = array('context' => \context_system::instance(),'other'=>array('name'=>'EN_Name'));
            $event = \local_sector\event\header_missing::create($eventparams);// ... code that may add some record snapshots
            $event->trigger();
        }

        // check AR_Name
        if (array_key_exists('AR_Name', $excel) ) {
            if (empty($excel->AR_Name)) {
                $strings = new stdClass;
                $strings->excel_line_number = $this->excel_line_number;
                $strings->column = 'AR_Name';                
                echo '<div class="local_sector_sync_error">'.get_string('emptymsg','local_sector', $strings).'</div>'; 
                $this->errors[] = get_string('emptymsg','local_sector', $strings);
                $this->mfields[] = 'AR_Name';
                $this->errorcount++;
                $eventparams = array('context' => \context_system::instance(),'objectid'=>$strings->excel_line_number,'other'=>array('name'=>'AR_Name'));
                $event = \local_sector\event\missing_field::create($eventparams);// ... code that may add some record snapshots
                $event->trigger();
            }
        } else {
           echo '<div class="local_sector_sync_error">'.get_string('error_arnamecolumn_heading', 'local_sector').'</div>'; 
           $this->errormessage = get_string('error_arnamecolumn_heading', 'local_sector');
           $this->errorcount++;
            $eventparams = array('context' => \context_system::instance(),'other'=>array('name'=>'AR_Name'));
            $event = \local_sector\event\header_missing::create($eventparams);// ... code that may add some record snapshots
            $event->trigger();
        }

        // check Description
        if (array_key_exists('Description', $excel) ) {

            if($excel->TYPE == 'Jobfamily' || $excel->TYPE == 'Jobrole') {
                
                if (empty($excel->Description)) {
                    $strings = new stdClass;
                    $strings->excel_line_number = $this->excel_line_number;
                    $strings->column = 'Description';                
                    echo '<div class="local_sector_sync_error">'.get_string('emptymsg','local_sector', $strings).'</div>'; 
                    $this->errors[] = get_string('emptymsg','local_sector', $strings);
                    $this->mfields[] = 'Description';
                    $this->errorcount++;
                    $eventparams = array('context' => \context_system::instance(),'objectid'=>$strings->excel_line_number,'other'=>array('name'=>'Description'));
                    $event = \local_sector\event\missing_field::create($eventparams);// ... code that may add some record snapshots
                    $event->trigger();
                }

            }

        } else {
           echo '<div class="local_sector_sync_error">'.get_string('error_descriptioncolumn_heading', 'local_sector').'</div>'; 
           $this->errormessage = get_string('error_descriptioncolumn_heading', 'local_sector');
           $this->errorcount++;
            $eventparams = array('context' => \context_system::instance(),'other'=>array('name'=>'Description'));
            $event = \local_sector\event\header_missing::create($eventparams);// ... code that may add some record snapshots
            $event->trigger();
        }

        // check TYPE
        if (array_key_exists('TYPE', $excel) ) {
            if (empty($excel->TYPE)) {
                $strings = new stdClass;
                $strings->excel_line_number = $this->excel_line_number;
                $strings->column = 'TYPE';                
                echo '<div class="local_sector_sync_error">'.get_string('emptymsg','local_sector', $strings).'</div>'; 
                $this->errors[] = get_string('emptymsg','local_sector', $strings);
                $this->mfields[] = 'TYPE';
                $this->errorcount++;
                $eventparams = array('context' => \context_system::instance(),'objectid'=>$strings->excel_line_number,'other'=>array('name'=>'TYPE'));
                $event = \local_sector\event\missing_field::create($eventparams);// ... code that may add some record snapshots
                $event->trigger();
            }
        } else {
           echo '<div class="local_sector_sync_error">'.get_string('error_typecolumn_heading', 'local_sector').'</div>'; 
           $this->errormessage = get_string('error_typecolumn_heading', 'local_sector');
           $this->errorcount++;
            $eventparams = array('context' => \context_system::instance(),'other'=>array('name'=>'TYPE'));
            $event = \local_sector\event\header_missing::create($eventparams);// ... code that may add some record snapshots
            $event->trigger();
        }

        // check CODE
        if (array_key_exists('CODE', $excel) ) {

            if(strtolower($excel->TYPE) == 'sector') {

                if (empty($excel->CODE)) {
                    $strings = new stdClass;
                    $strings->excel_line_number = $this->excel_line_number;
                    $strings->column = 'CODE';
                    echo '<div class="local_sector_sync_error">'.get_string('codeexisted','local_sector', $strings).'</div>'; 
                    $this->errors[] = get_string('codeexisted','local_sector', $strings);
                    $this->mfields[] = 'CODE';
                    $this->errorcount++;
                    $eventparams = array('context' => \context_system::instance(),'objectid'=>$strings->excel_line_number,'other'=>array('name'=>'CODE'));
                    $event = \local_sector\event\field_exists::create($eventparams);// ... code that may add some record snapshots
                    $event->trigger();
                }

            } elseif(strtolower($excel->TYPE) == 'segment') {
            
                if (empty($excel->CODE)) {
                    $strings = new stdClass;
                    $strings->excel_line_number = $this->excel_line_number;
                    $strings->column = 'CODE';
                    echo '<div class="local_sector_sync_error">'.get_string('codeexisted','local_sector', $strings).'</div>'; 
                    $this->errors[] = get_string('codeexisted','local_sector', $strings);
                    $this->mfields[] = 'CODE';
                    $this->errorcount++;
                    $eventparams = array('context' => \context_system::instance(),'objectid'=>$strings->excel_line_number,'other'=>array('name'=>'CODE'));
                    $event = \local_sector\event\field_exists::create($eventparams);// ... code that may add some record snapshots
                    $event->trigger();
                }

            } elseif(strtolower($excel->TYPE) == 'jobfamily') {

                if (empty($excel->CODE)) {
                    $strings = new stdClass;
                    $strings->excel_line_number = $this->excel_line_number;
                    $strings->column = 'CODE';
                    echo '<div class="local_sector_sync_error">'.get_string('codeexisted','local_sector', $strings).'</div>'; 
                    $this->errors[] = get_string('codeexisted','local_sector', $strings);
                    $this->mfields[] = 'CODE';
                    $this->errorcount++;
                    $eventparams = array('context' => \context_system::instance(),'objectid'=>$strings->excel_line_number,'other'=>array('name'=>'CODE'));
                    $event = \local_sector\event\field_exists::create($eventparams);// ... code that may add some record snapshots
                    $event->trigger();
                }

            } elseif(strtolower($excel->TYPE) == 'jobrole') {

                if (empty($excel->CODE)) {
                    $strings = new stdClass;
                    $strings->excel_line_number = $this->excel_line_number;
                    $strings->column = 'CODE';
                    echo '<div class="local_sector_sync_error">'.get_string('codeexisted','local_sector', $strings).'</div>'; 
                    $this->errors[] = get_string('codeexisted','local_sector', $strings);
                    $this->mfields[] = 'CODE';
                    $this->errorcount++;
                    $eventparams = array('context' => \context_system::instance(),'objectid'=>$strings->excel_line_number,'other'=>array('name'=>'CODE'));
                    $event = \local_sector\event\field_exists::create($eventparams);// ... code that may add some record snapshots
                    $event->trigger();
                }


            }

        } else {
           echo '<div class="local_sector_sync_error">'.get_string('error_codecolumn_heading', 'local_sector').'</div>'; 
           $this->errormessage = get_string('error_codecolumn_heading', 'local_sector');
           $this->errorcount++;
           $eventparams = array('context' => \context_system::instance(),'other'=>array('name'=>'CODE'));
            $event = \local_sector\event\header_missing::create($eventparams);// ... code that may add some record snapshots
            $event->trigger();
        }

        // check PARENT_CODE
        if (array_key_exists('PARENT_CODE', $excel) ) {
            $excel->PARENT_CODE = trim($excel->PARENT_CODE);

            if(strtolower($excel->TYPE) == 'segment') {
                
                $sector = $DB->get_field_sql("SELECT id FROM {local_sector} WHERE code = '{$excel->PARENT_CODE}' ");
                if (empty($sector)) {
                    $strings = new stdClass;
                    $strings->excel_line_number = $this->excel_line_number;
                    $strings->column = $excel->PARENT_CODE;                
                    echo '<div class="local_sector_sync_error">'.get_string('codenotavailable','local_sector', $strings).'</div>'; 
                    $this->errors[] = get_string('codenotavailable','local_sector', $strings);
                    $this->mfields[] = 'PARENT_CODE';
                    $this->errorcount++;
                    $eventparams = array('context' => \context_system::instance(),'objectid'=>$strings->excel_line_number,'other'=>array('name'=>'PARENT_CODE'));
                    $event = \local_sector\event\missing_field::create($eventparams);// ... code that may add some record snapshots
                    $event->trigger();
                }

            // } elseif(strtolower($excel->TYPE) == 'segment') {
                
            //     $sector = $DB->get_field_sql("SELECT id FROM {local_sector} WHERE code = '{$excel->PARENT_CODE}' ");
            //     if (empty($sector)) {
            //         $strings = new stdClass;
            //         $strings->excel_line_number = $this->excel_line_number;
            //         $strings->column = $excel->PARENT_CODE;                
            //         echo '<div class="local_sector_sync_error">'.get_string('codenotavailable','local_sector', $strings).'</div>'; 
            //         $this->errors[] = get_string('codenotavailable','local_sector', $strings);
            //         $this->mfields[] = 'PARENT_CODE';
            //         $this->errorcount++;
            //         $eventparams = array('context' => \context_system::instance(),'objectid'=>$strings->excel_line_number,'other'=>array('name'=>'PARENT_CODE'));
            //         $event = \local_sector\event\missing_field::create($eventparams);// ... code that may add some record snapshots
            //         $event->trigger();
            //     }

            } elseif(strtolower($excel->TYPE) == 'jobfamily') {

                $segment = $DB->get_field_sql("SELECT id FROM {local_segment} WHERE code = '{$excel->PARENT_CODE}' ");
                if (empty($segment) && trim($excel->SHARED) == 0) {
                    $strings = new stdClass;
                    $strings->excel_line_number = $this->excel_line_number;
                    $strings->column = $excel->PARENT_CODE;                
                    echo '<div class="local_sector_sync_error">'.get_string('codenotavailable','local_sector', $strings).'</div>'; 
                    $this->errors[] = get_string('codenotavailable','local_sector', $strings);
                    $this->mfields[] = 'PARENT_CODE';
                    $this->errorcount++;
                    $eventparams = array('context' => \context_system::instance(),'objectid'=>$strings->excel_line_number,'other'=>array('name'=>'PARENT_CODE'));
                    $event = \local_sector\event\missing_field::create($eventparams);// ... code that may add some record snapshots
                    $event->trigger();
                }

            } elseif(strtolower($excel->TYPE) == 'jobrole') {
                $parent_code = trim($excel->PARENT_CODE);
                $jobfamily = $DB->get_record_sql("SELECT id, shared FROM {local_jobfamily} WHERE code = '{$parent_code}' ");

                if (empty($jobfamily)) {
                    $sectorcodes = $DB->get_fieldset_select('local_sector', 'code', '');
                    
                    foreach($sectorcodes as $scode){
                            $pos = strpos($parent_code, $scode);
                            if ($pos !== false ) {
                                 $parent_code = substr_replace(trim($parent_code), "",0, strlen($scode));
                                 $jobfamily = $DB->get_field_sql("SELECT id FROM {local_jobfamily} WHERE TRIM(code)='{$parent_code}' ");
                                break;
                            }
                    }
                    if(empty($jobfamily)){
                        $strings = new stdClass;
                        $strings->excel_line_number = $this->excel_line_number;
                        $strings->column = $excel->PARENT_CODE;                
                        echo '<div class="local_sector_sync_error">'.get_string('codenotavailable','local_sector', $strings).'</div>'; 
                        $this->errors[] = get_string('codenotavailable','local_sector', $strings);
                        $this->mfields[] = 'PARENT_CODE';
                        $this->errorcount++;
                        $eventparams = array('context' => \context_system::instance(),'objectid'=>$strings->excel_line_number,'other'=>array('name'=>'PARENT_CODE'));
                        $event = \local_sector\event\missing_field::create($eventparams);// ... code that may add some record snapshots
                        $event->trigger();
                    }
                }
                if ( array_key_exists('Competencies', $excel) ) {
                     $strings = new stdClass;
                    $strings->excel_line_number = $this->excel_line_number;
                    // if (empty($excel->Competencies)) {
                    //     echo '<div class="local_sector_sync_error">'.get_string('competencymissing','local_sector', $strings).'</div>'; 
                    //     $this->errors[] = get_string('competencymissing','local_sector', $excel);
                    //     $this->mfields[] = 'Competencies';
                    //     $this->errorcount++;
                    // } 
                    if (!empty($excel->Competencies)) {
                        $competencies = $this->get_competencies();

                        $ctypes= explode('*',$excel->Competencies);

                        foreach ($ctypes AS $type) {
                            if(!in_array($type,$competencies)) {
                               $typeerror = true;
                            } else {
                                $typeerror = false;
                                break;
                            }
                            
                        }
                        if($typeerror){

                            echo '<div class="local_sector_sync_error">'.get_string('competenciesarenotvalid','local_trainingprogram', $strings).'</div>'; 
                            $this->errors[] = get_string('competenciesarenotvalid','local_trainingprogram', $excel);
                            $this->mfields[] = 'competencies';
                            $this->errorcount++;
                            $eventparams = array('context' => \context_system::instance(),'objectid'=>$strings->excel_line_number,'other'=>array('name'=>'competencies'));
                            $event = \local_sector\event\invalid_field::create($eventparams);// ... code that may add some record snapshots
                            $event->trigger();
                        }

                
                    }
                }

            }

        } else {
           echo '<div class="local_sector_sync_error">'.get_string('error_parentcodecolumn_heading', 'local_sector').'</div>'; 
           $this->errormessage = get_string('error_parentcodecolumn_heading', 'local_sector');
           $this->errorcount++;
            $eventparams = array('context' => \context_system::instance(),'other'=>array('name'=>'PARENT_CODE'));
            $event = \local_sector\event\header_missing::create($eventparams);// ... code that may add some record snapshots
            $event->trigger();
        }




        //-------- check program competency types-------------------------------------

        
        
        if (array_key_exists('ctype', $excel) ) {
            
            if (!empty($excel->ctype)) {


                $is_given_level_valid = $this->get_level($excel->LEVEL);

                if($is_given_level_valid) {

                   $programlevel =  $excel->LEVEL;
                }

                $get_competencytypes = $this->get_competencytypes($programlevel);

                $ctypes= explode('*',trim($excel->ctype));

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
                    $this->mfields[] = 'ctype';
                    $this->errorcount++;
                   $eventparams = array('context' => \context_system::instance(),'other'=>array('name'=>'ctype'));
                   $event = \local_sector\event\header_missing::create($eventparams);// ... code that may add some record snapshots
                   $event->trigger();
                }

            }


        }




        // check SHARED
        if (array_key_exists('SHARED', $excel) ) {

        } else {
           echo '<div class="local_sector_sync_error">'.get_string('error_sharedcolumn_heading', 'local_sector').'</div>'; 
           $this->errormessage = get_string('error_sharedcolumn_heading', 'local_sector');
           $this->errorcount++;
            $eventparams = array('context' => \context_system::instance(),'objectid'=>$strings->excel_line_number,'other'=>array('name'=>'SHARED'));
            $event = \local_sector\event\missing_field::create($eventparams);// ... code that may add some record snapshots
            $event->trigger();
        }

        // check LEVEL
        if (array_key_exists('LEVEL', $excel) ) {

            /*if(strtolower($excel->TYPE) == 'Jobrole') {

                if (empty($excel->LEVEL)) {
                    $strings = new stdClass;
                    $strings->excel_line_number = $this->excel_line_number;
                    $strings->column = 'LEVEL';                
                    echo '<div class="local_sector_sync_error">'.get_string('missinglevel','local_sector', $strings).'</div>'; 
                    $this->errors[] = get_string('missinglevel','local_sector', $strings);
                    $this->mfields[] = 'LEVEL';
                    $this->errorcount++;
                    $eventparams = array('context' => \context_system::instance(),'objectid'=>$strings->excel_line_number,'other'=>array('name'=>'LEVEL'));
                    $event = \local_sector\event\missing_field::create($eventparams);// ... code that may add some record snapshots
                    $event->trigger();
                }

            }

        } else {
           echo '<div class="local_sector_sync_error">'.get_string('error_levelcolumn_heading', 'local_sector').'</div>'; 
           $this->errormessage = get_string('error_levelcolumn_heading', 'local_sector');
           $this->errorcount++;
            $eventparams = array('context' => \context_system::instance(),'other'=>array('name'=>'LEVEL'));
            $event = \local_sector\event\header_missing::create($eventparams);// ... code that may add some record snapshots
            $event->trigger();
        }*/

        if (!empty($excel->programlevel)) {

                $is_given_level_valid = $this->get_level($excel->programlevel);

                if(!$is_given_level_valid) {

                    echo '<div class="local_trainingprogram_sync_error">'.get_string('clevelsnotmatchedwithrecords','local_trainingprogram', $strings).'</div>'; 
                    $this->errors[] = get_string('clevelsnotmatchedwithrecords','local_trainingprogram', $excel);
                    $this->mfields[] = 'ProgramLevel';
                    $this->errorcount++;
                    $eventparams = array('context' => \context_system::instance(),'objectid'=>$strings->excel_line_number,'other'=>array('name'=>'ProgramLevel'));
                    $event = \local_trainingprogram\event\invalid_field::create($eventparams);// ... code that may add some record snapshots
                    $event->trigger();
                } 

            }
        }
 
    } // end of required_fields_validations function
    /**
     * @method get_sector_file
     * @todo Returns the uploaded file if it is present.
     * @param int $draftid
     * @return stored_file|null
     */
    public function get_sector_file($draftid) {
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
     public function get_competencies() {
       global $DB,$USER;

        $sql = "SELECT lc.code
                  FROM {local_competencies} lc 
                  WHERE 1=1";

        $competencieslist = $DB->get_fieldset_sql($sql.$formsql);

        return $competencieslist;
    }
    public function get_competency_ids($competencies,$competencytypes,$level) {
       global $DB,$USER; 

        $competencies = array_filter($competencies);

        if(!empty($competencies)) {
            $ctypes= str_replace('*', '\',\'', $competencytypes);
            $ctypequery = array();
            foreach ($competencies as $competency) {
                $ctypequery[] = " CONCAT(',',lc.code,',') LIKE CONCAT('%,','$competency',',%') "; 
            }
            $ctypequeeryparams =implode('OR',$ctypequery);
            $formsql = ' AND ('.$ctypequeeryparams.') ';

            $sql = "SELECT lc.id
                  FROM {local_competencies} lc 
                  WHERE FIND_IN_SET('$level', REPLACE(lc.level,'level','')) AND lc.type IN ('{$ctypes}') ";

            $competencyids = $DB->get_fieldset_sql($sql.$formsql);

           return $competencyids;
       }
        
    }

    /**
     * Displays the preview of the uploaded file
     */
    protected function preview_uploaded() {
        global $OUTPUT;
        $return = '';
        $return .= $OUTPUT->notification(get_string('uploadsectorsheet', 'local_sector'),'info');
        $return .= html_writer::start_tag('div', array('class'=> 'w-100 mt-5')).$OUTPUT->continue_button(new moodle_url('/local/sector/uploadsector.php')).html_writer::end_tag('div');
        return $return;
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


}

