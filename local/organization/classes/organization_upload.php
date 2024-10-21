<?php

/**
 * Sector upload masterdata.
 *
 * @package local_organization
 * 
 */
namespace local_organization;
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
class organization_upload  {
    protected $columns;
    protected $columnsmapping = array();
    protected $allowedcolumns = array('oldid','licensekey','organizationname','organizationarabicname', 'organizationcode', 'organizationdescription', 'sector', 'segment','fieldofwork','hrname',
    'hremail','hrjobrole', 'hrmobile','alternativename', 'alternativejobrole', 'alternativeemail', 'alternativemobile', 'discountpercentage');
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
        if (!$readcount) {
            throw new moodle_exception('csvloaderror', 'error',$PAGE->url, $cir->get_error());
        }
        if(!(is_siteadmin() || has_capability('local/organization:manage_communication_officer',$systemcontext))){
            throw new moodle_exception('youdonthabepermissiontouploaddata', 'local_organization');
        }


        $this->columns = $cir->get_columns();
        $column_validation = $this->validate_columns();
        if(is_array($column_validation) && count($column_validation) > 0){
            $string = $column_validation[0];
            $return =  '<div class="local_organization_sync_error">'.get_string('validsheet','local_organization',$string).'</div>'; 
            $return .= html_writer::start_tag('div', array('class'=> 'w-100 mt-5')).$OUTPUT->continue_button(new moodle_url('/local/organization/uploadorganization.php')).html_writer::end_tag('div');
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
        $rownum = 0;
        $progress = 0;
        // $progressbar    = new \core\progress\display_if_slow(get_string('uploadsector', 'local_sector'),0);
        // $progressbar->start_html();
        // $progressbar->start_progress('', $readcount - 1); 
        $data = array();
        $linenum = 1;   
        $errorcount= 0;
        $successcreatedcount = 0;
        $cir->init();
        while($row = $cir->next()) {
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
                
                $masterdata->oldid = $masterdata->oldid;
                $masterdata->licensekey = $masterdata->licensekey;
                $masterdata->fullname = $masterdata->organizationname;
                $masterdata->fullnameinarabic = $masterdata->organizationarabicname;
                $masterdata->shortname = $masterdata->organizationcode;
                $masterdata->description = ['text' => $masterdata->organizationdescription];
                $masterdata->orgfieldofwork = preg_replace('/ /', '', core_text::strtolower($masterdata->fieldofwork));
                $sectors =  $this->get_sector_ids(explode('*',$masterdata->sector));
                $masterdata->sectors = $sectors;

                $segments =  $this->get_segment_ids(explode('*',$masterdata->segment));
                $masterdata->segment = $segments;

                $masterdata->hrfullname = $masterdata->hrname;
                $masterdata->hrjobrole = $masterdata->hrjobrole;
                $masterdata->hremail = $masterdata->hremail;
                $masterdata->hrmobile = $masterdata->hrmobile;
                $masterdata->alfullname = $masterdata->alternativename;
                $masterdata->aljobrole =  $masterdata->alternativejobrole;
                $masterdata->alemail = $masterdata->alternativeemail;
                $masterdata->almobile = $masterdata->alternativemobile;
                $masterdata->discount_percentage = $masterdata->discountpercentage ? $masterdata->discountpercentage : 0;
                $orgcode = $DB->get_record_sql("SELECT * FROM {local_organization} WHERE shortname='{$masterdata->organizationcode}'");
                if(empty($orgcode)) {
                    $createdcount = (new \local_organization\organization)->add_update_organization($masterdata);
                } else {
                    $masterdata->id = $orgcode->id;
                    $createdcount = (new \local_organization\organization)->add_update_organization($masterdata);
                }
                if($createdcount == 1) {
                    $successcreatedcount++;
                    $eventparams = array('context' => \context_system::instance(),'objectid'=>$createdcount->id,'other'=>array('name'=>$masterdata->organizationnam));
                    $event = \local_organization\event\row_inserted::create($eventparams);// ... code that may add some record snapshots
                    $event->trigger();
                }
            }
		}
        if ($successcreatedcount > 0) {
            $success->count = $successcreatedcount;
            $return = $OUTPUT->notification(get_string('uploadorgsheet', 'local_organization', $success),'info');
            echo $return;
        } else {
            $return = $OUTPUT->notification(get_string('notuploadorgsheet', 'local_organization'),'danger');
            echo $return;
        }
        // $progressbar->end_html();
        return $this->preview_uploaded();
    }
    private function validate_columns() {
        global $DB;
        foreach ($this->columns as $i => $columnname) {
            if (in_array(strtolower(str_replace(' ','',$columnname)), $this->allowedcolumns)) {
                $this->columnsmapping[$i] = strtolower(str_replace(' ','',$columnname));
            }
        }
        if (!in_array('oldid', $this->columnsmapping)) {
            $this->errors[] = get_string('oldidmissing', 'local_organization');
            $eventparams = array('context' => \context_system::instance(),'other'=>array('name'=>'oldid'));
            $event = \local_organization\event\header_missing::create($eventparams);// ... code that may add some record snapshots
            $event->trigger();
            return  $this->errors;
        }
        if (!in_array('licensekey', $this->columnsmapping)) {
            $this->errors[] = get_string('licensekeymissing', 'local_organization');
            $eventparams = array('context' => \context_system::instance(),'other'=>array('name'=>'licensekey'));
            $event = \local_organization\event\header_missing::create($eventparams);// ... code that may add some record snapshots
            $event->trigger();
            return  $this->errors;
        }
        if (!in_array('organizationname', $this->columnsmapping)) {
            $this->errors[] = get_string('organizationnamemissing', 'local_organization');
            $eventparams = array('context' => \context_system::instance(),'other'=>array('name'=>'organizationname'));
            $event = \local_organization\event\header_missing::create($eventparams);// ... code that may add some record snapshots
            $event->trigger();
            return  $this->errors;
        }
        if (!in_array('organizationarabicname', $this->columnsmapping)) {
            $this->errors[] = get_string('organizationarabicnamemissing', 'local_organization');
            $eventparams = array('context' => \context_system::instance(),'other'=>array('name'=>'organizationarabicname'));
            $event = \local_organization\event\header_missing::create($eventparams);// ... code that may add some record snapshots
            $event->trigger();
            return  $this->errors;
        }
        if (!in_array('organizationcode', $this->columnsmapping)) {
             $this->errors[] = get_string('organizationcodemissing', 'local_organization');
             $eventparams = array('context' => \context_system::instance(),'other'=>array('name'=>'organizationcode'));
            $event = \local_organization\event\header_missing::create($eventparams);// ... code that may add some record snapshots
            $event->trigger();
            return  $this->errors;
        }
        if (!in_array('organizationdescription', $this->columnsmapping)) {
            $this->errors[] = get_string('descriptionmissing', 'local_organization');
            $eventparams = array('context' => \context_system::instance(),'other'=>array('name'=>'organizationdescription'));
            $event = \local_organization\event\header_missing::create($eventparams);// ... code that may add some record snapshots
            $event->trigger();
            return  $this->errors;
        }
        if (!in_array('sector', $this->columnsmapping)) { 
            $this->errors[] = get_string('sectorcodemissing', 'local_organization');
            $eventparams = array('context' => \context_system::instance(),'other'=>array('name'=>'sector'));
            $event = \local_organization\event\header_missing::create($eventparams);// ... code that may add some record snapshots
            $event->trigger();
            return  $this->errors;
        }
        if (!in_array('segment', $this->columnsmapping)) { 
            $this->errors[] = get_string('segmentcodemissing', 'local_organization');
            $eventparams = array('context' => \context_system::instance(),'other'=>array('name'=>'segment'));
            $event = \local_organization\event\header_missing::create($eventparams);// ... code that may add some record snapshots
            $event->trigger();
            return  $this->errors;
        }
        if (!in_array('fieldofwork', $this->columnsmapping)) { 
            $this->errors[] = get_string('fieldofworkmissing', 'local_organization');
            $eventparams = array('context' => \context_system::instance(),'other'=>array('name'=>'fieldofwork'));
            $event = \local_organization\event\header_missing::create($eventparams);// ... code that may add some record snapshots
            $event->trigger();
            return  $this->errors;
        }
        if (!in_array('hrname', $this->columnsmapping)) { 
            $this->errors[] = get_string('hrnamemissing', 'local_organization');
            $eventparams = array('context' => \context_system::instance(),'other'=>array('name'=>'hrname'));
            $event = \local_organization\event\header_missing::create($eventparams);// ... code that may add some record snapshots
            $event->trigger();
            return  $this->errors;
        }
        if (!in_array('hrjobrole', $this->columnsmapping)) { 
            $this->errors[] = get_string('hrjobrolemissing', 'local_organization');
            $eventparams = array('context' => \context_system::instance(),'other'=>array('name'=>'hrjobrole'));
            $event = \local_organization\event\header_missing::create($eventparams);// ... code that may add some record snapshots
            $event->trigger();
            return  $this->errors;
        }
        if (!in_array('hremail', $this->columnsmapping)) { 
            $this->errors[] = get_string('hremailmissing', 'local_organization');
            $eventparams = array('context' => \context_system::instance(),'other'=>array('name'=>'hremail'));
            $event = \local_organization\event\header_missing::create($eventparams);// ... code that may add some record snapshots
            $event->trigger();
            return  $this->errors;
        }
        if (!in_array('hrmobile', $this->columnsmapping)) { 
            $this->errors[] = get_string('hrmobilemissing', 'local_organization');
            $eventparams = array('context' => \context_system::instance(),'other'=>array('name'=>'hrmobile'));
            $event = \local_organization\event\header_missing::create($eventparams);// ... code that may add some record snapshots
            $event->trigger();
            return  $this->errors;
        }
        if (!in_array('alternativename', $this->columnsmapping)) { 
            $this->errors[] = get_string('alternativenamemissing', 'local_organization');
            $eventparams = array('context' => \context_system::instance(),'other'=>array('name'=>'alternativename'));
            $event = \local_organization\event\header_missing::create($eventparams);// ... code that may add some record snapshots
            $event->trigger();
            return  $this->errors;
        }
        if (!in_array('alternativejobrole', $this->columnsmapping)) { 
            $this->errors[] = get_string('alternativejobrolemissing', 'local_organization');
            $eventparams = array('context' => \context_system::instance(),'other'=>array('name'=>'alternativejobrole'));
            $event = \local_organization\event\header_missing::create($eventparams);// ... code that may add some record snapshots
            $event->trigger();
            return  $this->errors;
        }
        if (!in_array('alternativeemail', $this->columnsmapping)) { 
            $this->errors[] = get_string('alternativeemailmissing', 'local_organization');
            $eventparams = array('context' => \context_system::instance(),'other'=>array('name'=>'alternativeemail'));
            $event = \local_organization\event\header_missing::create($eventparams);// ... code that may add some record snapshots
            $event->trigger();
            return  $this->errors;
        }
        if (!in_array('alternativemobile', $this->columnsmapping)) { 
            $this->errors[] = get_string('alternativemobilemissing', 'local_organization');
            $eventparams = array('context' => \context_system::instance(),'other'=>array('name'=>'alternativemobile'));
            $event = \local_organization\event\header_missing::create($eventparams);// ... code that may add some record snapshots
            $event->trigger();
            return  $this->errors;
        }
        if (!in_array('discountpercentage', $this->columnsmapping)) { 
            $this->errors[] = get_string('discountpercentagemissing', 'local_organization');
            $eventparams = array('context' => \context_system::instance(),'other'=>array('name'=>'discountpercentage'));
            $event = \local_organization\event\header_missing::create($eventparams);// ... code that may add some record snapshots
            $event->trigger();
            return  $this->errors;
        }
        return false;
    }

    private function required_fields_validations($excel,$option=0){
        global $DB;

         //-----------check OrganizationOldid-----------------------------------
         if (array_key_exists('oldid', $excel) ) {
            // if (empty($excel->oldid)) {
            //     $strings = new stdClass;
            //     $strings->excel_line_number = $this->excel_line_number;
                
            //     echo '<div class="local_organization_sync_error">'.get_string('oldid_emptymsg','local_organization', $strings).'</div>'; 
            //     $this->errors[] = get_string('oldid_emptymsg','local_organization', $strings);
            //     $this->mfields[] = 'oldid';
            //     $this->errorcount++;
            //     $eventparams = array('context' => \context_system::instance(),'objectid'=>$strings->excel_line_number,'other'=>array('name'=>'OLD_ID'));
            //     $event = \local_organization\event\missing_field::create($eventparams);// ... code that may add some record snapshots
            //     $event->trigger();
            // }
        } 

        if (array_key_exists('licensekey', $excel)) {
            if (empty(trim($excel->licensekey))) {
                $strings = new stdClass;
                $strings->excel_line_number = $this->excel_line_number;
                
                echo '<div class="local_organization_sync_error">'.get_string('licensekey_emptymsg','local_organization', $strings).'</div>'; 
                $this->errors[] = get_string('licensekey_emptymsg','local_organization', $strings);
                $this->mfields[] = 'LicenseKey';
                $this->errorcount++;
                $eventparams = array('context' => \context_system::instance(),'objectid'=>$strings->excel_line_number,'other'=>array('name'=>'LicenseKey'));
                $event = \local_organization\event\missing_field::create($eventparams);// ... code that may add some record snapshots
                $event->trigger();
            } else {

                if(!empty(trim($excel->licensekey))) {
                    $licensekey = trim($excel->licensekey);
                    $orgcode = trim($excel->organizationcode);
                    $licensekeyexists = $DB->record_exists_sql("SELECT id FROM {local_organization} WHERE licensekey = '$licensekey'");
                    if($licensekeyexists && !empty($orgcode)){
                        $record = $DB->record_exists_sql("SELECT id FROM {local_organization} WHERE shortname = '$orgcode' AND licensekey = '$licensekey' ") ;
                        $licensekeymappedorganization = $DB->get_field_sql("SELECT shortname FROM {local_organization} WHERE licensekey = '$licensekey' ");
                        if(!$record) {
                            $strings->licensekey =  $licensekey;
                            $strings->licensekeymappedorganization =  $licensekeymappedorganization;
                            $strings->excel_line_number = $this->excel_line_number;
                            echo '<div class="local_organization_sync_error">'.get_string('licensekeycodemismatched','local_organization', $strings).'</div>'; 
                            $this->errors[] =  get_string('licensekeycodemismatched', 'local_organization',$strings);
                            $this->mfields[] = 'LicenseKey';
                            $this->errorcount++;
                            $eventparams = array('context' => \context_system::instance(),'objectid'=>$strings->excel_line_number,'other'=>array('name'=>'LicenseKey'));
                            $event = \local_organization\event\invalid_field::create($eventparams);// ... code that may add some record snapshots
                            $event->trigger();
                        }

                    }
                    
                }

            }
        } 
        //-----------check OrganizationName-----------------------------------
        if (array_key_exists('organizationname', $excel) ) {
            if (empty($excel->organizationname)) {
                $strings = new stdClass;
                $strings->excel_line_number = $this->excel_line_number;
                echo '<div class="local_organization_sync_error">'.get_string('org_emptymsg','local_organization', $strings).'</div>'; 
                $this->errors[] = get_string('org_emptymsg','local_organization', $strings);
                $this->mfields[] = 'OrganizationName';
                $this->errorcount++;
                $eventparams = array('context' => \context_system::instance(),'objectid'=>$strings->excel_line_number,'other'=>array('name'=>'OrganizationName'));
                $event = \local_organization\event\missing_field::create($eventparams);// ... code that may add some record snapshots
                $event->trigger();
            }
        } 

        //-----------check OrganizationCode-----------------------------------
        if (array_key_exists('organizationcode', $excel) ) {
            $organisationlist = $DB->get_fieldset_sql('SELECT LOWER(shortname) FROM {local_organization}');
            $org = strtolower($excel->organizationcode);
            $excel->name_err= $excel->organizationcode;
            if (empty(trim($excel->organizationcode))) {
                $strings = new stdClass;
                $strings->excel_line_number = $this->excel_line_number;
                echo '<div class="local_organization_sync_error">'.get_string('orgcode_emptymsg','local_organization', $strings).'</div>'; 
                $this->errors[] = get_string('orgcode_emptymsg','local_organization', $strings);
                $this->mfields[] = 'OrganizationCode';
                $this->errorcount++;
                $eventparams = array('context' => \context_system::instance(),'objectid'=>$strings->excel_line_number,'other'=>array('name'=>'OrganizationCode'));
                $event = \local_organization\event\missing_field::create($eventparams);// ... code that may add some record snapshots
                $event->trigger();
            }
            /*else if(in_array(trim($org),$organisationlist)){
                echo '<div class="local_organization_sync_error">'.get_string('shortname_exist','local_organization', $excel).'</div>'; 
                $this->errors[] = get_string('shortname_exist','local_organization', $excel);
                $this->mfields[] = 'OrganizationCode';
                $this->errorcount++;
            }*/

        }

        //--------Check OrganizationArabicName-----------------
        if (array_key_exists('organizationarabicname', $excel) ) {
            $organisationlist = $DB->get_fieldset_sql('SELECT trim(fullnameinarabic) FROM {local_organization}');
            $org = $excel->organizationarabicname;
            $excel->name_err = $excel->organizationarabicname;
            if (empty($excel->organizationarabicname)) {
                $strings = new stdClass;
                $strings->excel_line_number = $this->excel_line_number;
                echo '<div class="local_organization_sync_error">'.get_string('orgarabic_emptymsg','local_organization', $strings).'</div>'; 
                $this->errors[] = get_string('orgarabic_emptymsg','local_organization', $strings);
                $this->mfields[] = 'OrganizationArabicName';
                $this->errorcount++;
                $eventparams = array('context' => \context_system::instance(),'objectid'=>$strings->excel_line_number,'other'=>array('name'=>'OrganizationArabicName'));
                $event = \local_organization\event\missing_field::create($eventparams);// ... code that may add some record snapshots
                $event->trigger();
            }
            /*elseif(in_array(trim($org),$organisationlist)){
                echo '<div class="local_organization_sync_error">'.get_string('arabicname_exist','local_organization', $excel).'</div>'; 
                $this->errors[] = get_string('arabicname_exist','local_organization', $excel);
                $this->mfields[] = 'OrganizationArabicName';
                $this->errorcount++;
            }*/

        }
        //-----------check OrganizationDescription-----------------------------------
        if (array_key_exists('organizationdescription', $excel) ) {
            // if (empty($excel->organizationdescription)) {
            //     $strings = new stdClass;
            //     $strings->excel_line_number = $this->excel_line_number;
                
            //     echo '<div class="local_organization_sync_error">'.get_string('orgdescription_emptymsg','local_organization', $strings).'</div>'; 
            //     $this->errors[] = get_string('orgdescription_emptymsg','local_organization', $strings);
            //     $this->mfields[] = 'OrganizationDescription';
            //     $this->errorcount++;
            //     $eventparams = array('context' => \context_system::instance(),'objectid'=>$strings->excel_line_number,'other'=>array('name'=>'OrganizationDescription'));
            //     $event = \local_organization\event\missing_field::create($eventparams);// ... code that may add some record snapshots
            //     $event->trigger();
                
            // }
        }
        //-----------check sector-----------------------------------
        if (array_key_exists('sector', $excel) ) {
            // if (empty($excel->sector)) {
            //     $strings = new stdClass;
            //     $strings->excel_line_number = $this->excel_line_number;
            //     echo '<div class="local_organization_sync_error">'.get_string('sector_emptymsg','local_organization', $strings).'</div>'; 
            //     $this->errors[] = get_string('sector_emptymsg','local_organization', $strings);
            //     $this->mfields[] = 'Sector';
            //     $this->errorcount++;
            //     $eventparams = array('context' => \context_system::instance(),'objectid'=>$strings->excel_line_number,'other'=>array('name'=>'Sector'));
            //     $event = \local_organization\event\missing_field::create($eventparams);// ... code that may add some record snapshots
            //     $event->trigger();
            // }
            if (!empty($excel->sector)) {

                $sectors =  $this->get_sectors($excel->sector);

                if(!$sectors){

                    $strings->excel_line_number = $this->excel_line_number;

                   echo '<div class="local_organization_sync_error">'.get_string('sectorsnotmatchedwithrecords','local_trainingprogram', $strings).'</div>'; 
                    $this->errors[] = get_string('sectorsnotmatchedwithrecords','local_organization', $excel);
                    $this->mfields[] = 'Sector';
                    $this->errorcount++;
                    $eventparams = array('context' => \context_system::instance(),'objectid'=>$strings->excel_line_number,'other'=>array('name'=>'Sector'));
                    $event = \local_organization\event\invalid_field::create($eventparams);// ... code that may add some record snapshots
                    $event->trigger();
                }  

            }
        }
        //-------- check segment-------------------------------------
        if (array_key_exists('segment', $excel) ) {
            // if (empty($excel->segment)) {
            //     $strings = new stdClass;
            //     $strings->excel_line_number = $this->excel_line_number;
            //     echo '<div class="local_organization_sync_error">'.get_string('segment_emptymsg','local_organization', $strings).'</div>'; 
            //     $this->errors[] = get_string('segment_emptymsg','local_organization', $strings);
            //     $this->mfields[] = 'Segment';
            //     $this->errorcount++;
            //     $eventparams = array('context' => \context_system::instance(),'objectid'=>$strings->excel_line_number,'other'=>array('name'=>'Segment'));
            //     $event = \local_organization\event\missing_field::create($eventparams);// ... code that may add some record snapshots
            //     $event->trigger();
            // }
            if (!empty($excel->segment)) {
    
                $sectors = $this->get_sectors($excel->sector);
                $segments = $this->get_segments($sectors);
                $givensegments= explode('*',trim($excel->segment));
                foreach ($givensegments AS $givensegment) {
                    if(!in_array($givensegment,$segments)) {
                       $typeerror = true;
                    } else {
                        $typeerror = false;
                        break;
                    }
                    
                }
                if($typeerror){

                    $strings->excel_line_number = $this->excel_line_number;

                    echo '<div class="local_organization_sync_error">'.get_string('segmentsnotmatchedwithrecords','local_organization', $strings).'</div>'; 
                    $this->errors[] = get_string('segmentsnotmatchedwithrecords','local_organization', $excel);
                    $this->mfields[] = 'Segment';
                    $this->errorcount++;
                    $eventparams = array('context' => \context_system::instance(),'objectid'=>$strings->excel_line_number,'other'=>array('name'=>'Segment'));
                    $event = \local_organization\event\invalid_field::create($eventparams);// ... code that may add some record snapshots
                    $event->trigger();
                }
            }

        }
        //-------- check FieldOfWork-------------------------------------
        if (array_key_exists('fieldofwork', $excel) ) {
            // if (empty($excel->fieldofwork)) {
            //     $strings = new stdClass;
            //     $strings->excel_line_number = $this->excel_line_number;
            //     echo '<div class="local_organization_sync_error">'.get_string('fieldwork_emptymsg','local_organization', $strings).'</div>'; 
            //     $this->errors[] = get_string('fieldwork_emptymsg','local_organization', $strings);
            //     $this->mfields[] = 'FieldOfWork';
            //     $this->errorcount++;
            //     $eventparams = array('context' => \context_system::instance(),'objectid'=>$strings->excel_line_number,'other'=>array('name'=>'FieldOfWork'));
            //     $event = \local_organization\event\missing_field::create($eventparams);// ... code that may add some record snapshots
            //     $event->trigger();
            // }
        }
        //-------- check HRName-------------------------------------
        if (array_key_exists('hrname', $excel) ) {
            /*if (empty($excel->hrname)) {
                $strings = new stdClass;
                $strings->excel_line_number = $this->excel_line_number;
                
                echo '<div class="local_organization_sync_error">'.get_string('hr_emptymsg','local_organization', $strings).'</div>'; 
                $this->errors[] = get_string('hr_emptymsg','local_organization', $strings);
                $this->mfields[] = 'HRName';
                $this->errorcount++;
            }*/
        }
        //-------- check HREmail-------------------------------------
        if (array_key_exists('hremail', $excel) ) {
           /* if (empty($excel->hremail)) {
                $strings = new stdClass;
                $strings->excel_line_number = $this->excel_line_number;
                echo '<div class="local_organization_sync_error">'.get_string('hremail_emptymsg','local_organization', $strings).'</div>'; 
                $this->errors[] = get_string('hremail_emptymsg','local_organization', $strings);
                $this->mfields[] = 'HREmail';
                $this->errorcount++;
            } */
             if(!empty($excel->hremail) && !validate_email($excel->hremail) && $excel->hremail!='NULL' ){
                $strings = new stdClass;
                $strings->excel_line_number = $this->excel_line_number;
                echo '<div class="local_organization_sync_error">'.get_string('requiredvalidhremail','local_organization', $strings).'</div>'; 
                $this->errors[] = get_string('requiredvalidhremail','local_organization', $strings);
                $this->mfields[]  = 'HREmail';
                $this->errorcount++;
                $eventparams = array('context' => \context_system::instance(),'objectid'=>$strings->excel_line_number,'other'=>array('name'=>'HREmail'));
                $event = \local_organization\event\invalid_field::create($eventparams);// ... code that may add some record snapshots
                $event->trigger();
            }
        }
        //-------- check HRJobrole-------------------------------------
        if (array_key_exists('hrjobrole', $excel) ) {
            /*if (empty($excel->hrjobrole)) {
                $strings = new stdClass;
                $strings->excel_line_number = $this->excel_line_number;
                
                echo '<div class="local_organization_sync_error">'.get_string('hrjobrole_emptymsg','local_organization', $strings).'</div>'; 
                $this->errors[] = get_string('hrjobrole_emptymsg','local_organization', $strings);
                $this->mfields[] = 'HRJobrole';
                $this->errorcount++;
            }*/
        } 
        //-------- check HRMobile-------------------------------------
        if (array_key_exists('hrmobile', $excel) ) {
           /* if (empty($excel->hrmobile)) {
                $strings = new stdClass;
                $strings->excel_line_number = $this->excel_line_number;
                echo '<div class="local_organization_sync_error">'.get_string('hrmobile_emptymsg','local_organization', $strings).'</div>'; 
                $this->errors[] = get_string('hrmobile_emptymsg','local_organization', $strings);
                $this->mfields[] = 'HRMobile';
                $this->errorcount++;
            }*/
            if (!empty($excel->hrmobile) && !is_numeric($excel->hrmobile) && $excel->hrmobile!='NULL') {
                $strings = new stdClass;
                $strings->excel_line_number = $this->excel_line_number;
                $excel->name_err= 'HR Mobile';
                echo '<div class="local_organization_sync_error">'.get_string('mobile_requirednumeric','local_organization', $excel).'</div>'; 
                $this->errors[] = get_string('mobile_requirednumeric','local_organization', $excel);
                $this->mfields[] = 'HRMobile';
                $this->errorcount++;
                $eventparams = array('context' => \context_system::instance(),'objectid'=>$strings->excel_line_number,'other'=>array('name'=>'HRMobile'));
                $event = \local_organization\event\invalid_field::create($eventparams);// ... code that may add some record snapshots
                $event->trigger();
            }

            if (!empty(trim($excel->hrmobile)) && $excel->hrmobile !='NULL' && is_numeric(trim($excel->hrmobile)) && (strlen(trim($excel->hrmobile)) >= 5  &&  strlen(trim($excel->hrmobile)) <= 12) &&  !preg_match('/^[5-9][0-9]/',trim($excel->hrmobile))) {
                $excel->name_err= 'HR Mobile';
                $strings = new stdClass;
                $strings->excel_line_number = $this->excel_line_number;
                echo '<div class="local_organization_sync_error">'.get_string('mobilestartswith5','local_organization', $excel).'</div>'; 
                $this->errors[] = get_string('mobilestartswith5','local_organization', $excel);
                $this->mfields[] = 'HRMobile';
                $this->errorcount++;
                $eventparams = array('context' => \context_system::instance(),'objectid'=>$strings->excel_line_number,'other'=>array('name'=>'HRMobile'));
                $event = \local_organization\event\invalid_field::create($eventparams);// ... code that may add some record snapshots
                $event->trigger();
            }

            if  (!empty(trim($excel->hrmobile)) && $excel->hrmobile !='NULL' && is_numeric(trim($excel->hrmobile)) && (strlen(trim($excel->hrmobile)) < 5  || strlen(trim($excel->hrmobile)) > 12 )) {
                $excel->name_err= 'HR Mobile';
                $strings = new stdClass;
                $strings->excel_line_number = $this->excel_line_number;
                echo '<div class="local_organization_sync_error">'.get_string('mobileminimum5digitsallowed','local_organization', $excel).'</div>'; 
                $this->errors[] = get_string('mobileminimum5digitsallowed','local_organization', $excel);
                $this->mfields[] = 'HRMobile';
                $this->errorcount++;
                $eventparams = array('context' => \context_system::instance(),'objectid'=>$strings->excel_line_number,'other'=>array('name'=>'HRMobile'));
                $event = \local_organization\event\invalid_field::create($eventparams);// ... code that may add some record snapshots
                $event->trigger();
            }
        }
        //-------- check AlternativeName-------------------------------------
        if (array_key_exists('alternativename', $excel) ) {
           /* if (empty($excel->alternativename)) {
                $strings = new stdClass;
                $strings->excel_line_number = $this->excel_line_number;
                
                echo '<div class="local_organization_sync_error">'.get_string('altrname_emptymsg','local_organization', $strings).'</div>'; 
                $this->errors[] = get_string('altrname_emptymsg','local_organization', $strings);
                $this->mfields[] = 'AlternativeName';
                $this->errorcount++;
            }*/
        } 
        //-------- check AlternativeEmail-------------------------------------
        if (array_key_exists('alternativeemail', $excel) ) {
            /*if (empty($excel->alternativeemail)) {
                $strings = new stdClass;
                $strings->excel_line_number = $this->excel_line_number;
                echo '<div class="local_organization_sync_error">'.get_string('altremail_emptymsg','local_organization', $strings).'</div>'; 
                $this->errors[] = get_string('altremail_emptymsg','local_organization', $strings);
                $this->mfields[] = 'AlternativeEmail';
                $this->errorcount++;
            }*/
            if(!empty($excel->alternativeemail) && !validate_email($excel->alternativeemail) && $excel->alternativeemail!='NULL'){
                $strings = new stdClass;
                $strings->excel_line_number = $this->excel_line_number;
                echo '<div class="local_organization_sync_error">'.get_string('requiredvalidaltremail','local_organization', $strings).'</div>'; 
                $this->errors[] = get_string('requiredvalidaltremail','local_organization', $strings);
                $this->mfields[]  = 'AlternativeEmail';
                $this->errorcount++;
                $eventparams = array('context' => \context_system::instance(),'objectid'=>$strings->excel_line_number,'other'=>array('name'=>'AlternativeEmail'));
                $event = \local_organization\event\invalid_field::create($eventparams);// ... code that may add some record snapshots
                $event->trigger();
            }
        }
        //-------- check AlternativeJobrole-------------------------------------
        if (array_key_exists('alternativejobrole', $excel) ) {
            /*if (empty($excel->alternativejobrole)) {
                $strings = new stdClass;
                $strings->excel_line_number = $this->excel_line_number;
                
                echo '<div class="local_organization_sync_error">'.get_string('altrjobrole_emptymsg','local_organization', $strings).'</div>'; 
                $this->errors[] = get_string('altrjobrole_emptymsg','local_organization', $strings);
                $this->mfields[] = 'AlternativeJobrole';
                $this->errorcount++;
            }*/
        }
         //-------- check AlternativeMobile-------------------------------------
         if (array_key_exists('alternativemobile', $excel) ) {
            /*if (empty($excel->alternativemobile)) {
                $strings = new stdClass;
                $strings->excel_line_number = $this->excel_line_number;
                echo '<div class="local_organization_sync_error">'.get_string('altrmobile_emptymsg','local_organization', $strings).'</div>'; 
                $this->errors[] = get_string('altrmobile_emptymsg','local_organization', $strings);
                $this->mfields[] = 'AlternativeMobile';
                $this->errorcount++;
            }*/
            if (!empty($excel->alternativemobile) && $excel->alternativemobile!='NULL' && !is_numeric($excel->alternativemobile)) {
                $strings = new stdClass;
                $strings->excel_line_number = $this->excel_line_number;
                $excel->name_err= 'Alternative Mobile';
                echo '<div class="local_organization_sync_error">'.get_string('mobile_requirednumeric','local_organization', $excel).'</div>'; 
                $this->errors[] = get_string('mobile_requirednumeric','local_organization', $excel);
                $this->mfields[] = 'AlternativeMobile';
                $this->errorcount++;
                $eventparams = array('context' => \context_system::instance(),'objectid'=>$strings->excel_line_number,'other'=>array('name'=>'AlternativeEmail'));
                $event = \local_organization\event\invalid_field::create($eventparams);// ... code that may add some record snapshots
                $event->trigger();
            }
            if (!empty(trim($excel->alternativemobile)) && $excel->alternativemobile!='NULL' && is_numeric(trim($excel->alternativemobile)) && (strlen(trim($excel->alternativemobile)) >= 5  &&  strlen(trim($excel->alternativemobile)) <= 12) &&  !preg_match('/^[5-9][0-9]/',trim($excel->alternativemobile))) {
                $strings = new stdClass;
                $strings->excel_line_number = $this->excel_line_number;
                $excel->name_err= 'Alternative Mobile';
                echo '<div class="local_organization_sync_error">'.get_string('mobilestartswith5','local_organization', $excel).'</div>'; 
                $this->errors[] = get_string('mobilestartswith5','local_organization', $excel);
                $this->mfields[] = 'AlternativeMobile';
                $this->errorcount++;
                $eventparams = array('context' => \context_system::instance(),'objectid'=>$strings->excel_line_number,'other'=>array('name'=>'AlternativeEmail'));
                $event = \local_organization\event\invalid_field::create($eventparams);// ... code that may add some record snapshots
                $event->trigger();
            }

            if  (!empty(trim($excel->alternativemobile)) && $excel->alternativemobile!='NULL' && is_numeric(trim($excel->alternativemobile)) && (strlen(trim($excel->alternativemobile)) < 5  || strlen(trim($excel->alternativemobile)) > 12 )) {
                $strings = new stdClass;
                $strings->excel_line_number = $this->excel_line_number;
                $excel->name_err= 'Alternative Mobile';
                echo '<div class="local_organization_sync_error">'.get_string('mobileminimum5digitsallowed','local_organization', $excel).'</div>'; 
                $this->errors[] = get_string('mobileminimum5digitsallowed','local_organization', $excel);
                $this->mfields[] = 'AlternativeMobile';
                $this->errorcount++;
                $eventparams = array('context' => \context_system::instance(),'objectid'=>$strings->excel_line_number,'other'=>array('name'=>'AlternativeEmail'));
                $event = \local_organization\event\invalid_field::create($eventparams);// ... code that may add some record snapshots
                $event->trigger();
            }
        }

         if (array_key_exists('discountpercentage', $excel) ) {

            if (!empty(trim($excel->discountpercentage))) {

                $discount = trim($excel->discountpercentage);
                $strings = new stdClass;
                $strings->excel_line_number = $this->excel_line_number;

                if(!is_numeric($discount)) {
                    echo '<div class="local_organization_sync_error">'.get_string('discount_neednumeric','local_organization', $strings).'</div>'; 
                    $this->errors[] = get_string('discount_neednumeric','local_organization', $strings);
                    $this->mfields[] = 'Discount Percentage';
                    $this->errorcount++;
                }
                if(is_numeric($discount) && $discount > 100) {

                    echo '<div class="local_organization_sync_error">'.get_string('discount_cant_exceed_100','local_organization', $strings).'</div>'; 
                    $this->errors[] = get_string('discount_cant_exceed_100','local_organization', $strings);
                    $this->mfields[] = 'Discount Percentage';
                    $this->errorcount++;
                }
            }
        }


        

    } // end of required_fields_validations function
    /**
     * @method get_organization_file
     * @todo Returns the uploaded file if it is present.
     * @param int $draftid
     * @return stored_file|null
     */
    public function get_organization_file($draftid) {
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
       // $return .= $OUTPUT->notification(get_string('uploadorgsheet', 'local_organization'),'info');
        $return .= html_writer::start_tag('div', array('class'=> 'w-100 mt-5')).$OUTPUT->continue_button(new moodle_url('/local/organization/uploadorganization.php')).html_writer::end_tag('div');
        return $return;
    }

    public function get_sectors($sectors = null) {
       global $DB,$USER;


       if(!empty($sectors)) {
            $sectorsql = 'SELECT id FROM {local_sector} WHERE 1=1 ';
            $sectors = explode('*',$sectors);
            $sectorquery =array();
            foreach ($sectors as $sector) {
                $sectorquery[] = "  code = '$sector' "; 
            }
            $sectorqueryparams =implode('OR',$sectorquery);
            $formsql = ' AND ('.$sectorqueryparams.')';
            $sectors = $DB->get_fieldset_sql($sectorsql.$formsql);
            return $sectors;
       }
        
    }


    public function get_segments($sectors) {
       global $DB,$USER;

       $sectors = array_filter($sectors);
       $sectors = implode(',', $sectors);
       if(!empty($sectors)) {
            $segmentdata = $DB->get_fieldset_sql('select code from {local_segment} where sectorid in ('.$sectors.')');
            return $segmentdata;
        
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


    public function get_segment_ids($segments) {
       global $DB,$USER; 

        $segments = array_filter($segments);

        if(!empty($segments)) {

            $ctypequery = array();
            foreach ($segments as $segment) {
                $ctypequery[] = " CONCAT(',',lc.code,',') LIKE CONCAT('%,','$segment',',%') "; 
            }
            $ctypequeeryparams =implode('OR',$ctypequery);
            $formsql = ' AND ('.$ctypequeeryparams.') ';

            $sql = "SELECT lc.id
                  FROM {local_segment} lc 
                  WHERE 1=1 ";
            $segmentids = $DB->get_fieldset_sql($sql.$formsql);

           return $segmentids;
       }
        
    }
}
