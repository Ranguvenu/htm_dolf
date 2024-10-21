<?php
namespace local_exams\form;

use core_form\dynamic_form;
use moodle_url;
use context;
use context_system;
use local_exams;
use local_trainingprogram\local\trainingprogram as tp;

class examsform extends dynamic_form { 
    public function definition() {
        global $USER, $CFG,$DB;
        $corecomponent = new \core_component();

        $mform = $this->_form;
        $id = $this->optional_param('id', 0, PARAM_INT);
        $editoroptions = $this->_customdata['editoroptions'];
         
        $systemcontext = context_system::instance();
    
        $mform->addElement('hidden', 'id', $id);
        $mform->setType('id',PARAM_INT);

        $mform->addElement('text', 'exam', get_string('examnameenglish', 'local_exams'));
        $mform->setType('exam', PARAM_TEXT);
        $mform->addRule('exam', get_string('missingexam', 'local_exams'), 'required', null);

        $mform->addElement('text', 'examnamearabic', get_string('examnamearabic', 'local_exams'));
        $mform->setType('examnamearabic', PARAM_TEXT);
        $mform->addRule('examnamearabic', get_string('missingexam', 'local_exams'), 'required', null);

        if($id > 0)  {
            $code = $DB->get_field('local_exams','code',array('id'=>$id));
            $mform->addElement('static', 'exam_code', get_string('code', 'local_exams'),$code);                
            $mform->addElement('hidden', 'code',$code);
        } else {
            $mform->addElement('text', 'code', get_string('code', 'local_exams'));
            $mform->setType('code', PARAM_TEXT);
            $mform->addRule('code', get_string('missingcode', 'local_exams'), 'required', null);

        }      

        $radioarray=array();
        $radioarray[] = $mform->createElement('radio', 'examprice', '', get_string('complimentary', 'local_exams'), 0, $attributes);
        $radioarray[] = $mform->createElement('radio', 'examprice', '', get_string('paid', 'local_exams'), 1, $attributes);
        $mform->addGroup($radioarray, 'radioar', get_string('examprice', 'local_exams'), array(' '), false);

        $mform->addElement('text', 'sellingprice', get_string('sellingprice', 'local_exams'));
        $mform->setType('sellingprice', PARAM_TEXT);

        $mform->addElement('text', 'actualprice', get_string('actualprice', 'local_exams'));
        $mform->setType('actualprice', PARAM_TEXT);

        $mform->hideIf('sellingprice', 'examprice', 'eq', 0);
        $mform->hideIf('actualprice', 'examprice', 'eq', 0);

        $taxfreeformgroup=array();
        $taxfreeformgroup[] =& $mform->createElement('radio', 'tax', '',get_string('no', 'local_trainingprogram'), 1);
        $taxfreeformgroup[] =& $mform->createElement('radio', 'tax', '',get_string('yes', 'local_trainingprogram'), 0);
        $mform->addGroup($taxfreeformgroup, 'tax_free', get_string('tax_free', 'local_exams'), '&nbsp&nbsp', false);
        $mform->hideif('tax_free', 'examprice', 'eq', 0);

        $mform->addElement('editor', 'programdescription', get_string('description', 'local_exams'), null, $editoroptions);
        $mform->setType('programdescription', PARAM_RAW);
        $mform->addRule('programdescription', get_string('missingprogramdescription', 'local_exams'), 'required', null);

        $mform->addElement('editor', 'targetaudience', get_string('targetaudience', 'local_exams'));
        $mform->setType('targetaudience', PARAM_RAW);
        $mform->addRule('targetaudience', get_string('missingtargetaudience', 'local_exams'), 'required', null);

        $sectoroptions = array(
            'multiple' => true,
            'class' => 'el_sectorlist',
            'onchange' => "(function(e){ require(['local_trainingprogram/dynamic_dropdown_ajaxdata'], function(s) {s.sectorschanged();}) }) (event)",
        );

        $lang= current_language();
        if( $lang == 'ar'){
            $sectors = $DB->get_records_sql_menu("SELECT id, titlearabic as title FROM {local_sector}");
        } else{
            $sectors = $DB->get_records_sql_menu("SELECT id, title FROM {local_sector}");
        }

        $select = $mform->addElement('autocomplete', 'sectors', get_string('sectors','local_exams'), $sectors, $sectoroptions);
        $mform->addRule('sectors', get_string('missingsectors', 'local_exams'), 'required', null);

        $mform->addElement('advcheckbox', 'alltargetgroup', get_string('all_jobfamilies', 'local_trainingprogram'),null,null,[0,1]);
        $mform->setType('alltargetgroup', PARAM_BOOL);

        $jobfamilies = array();
        $jobfamilieslist = $this->_ajaxformdata['targetgroup'];


        if (!empty($jobfamilieslist)) {

            $jobfamilieslist = is_array($jobfamilieslist)?$jobfamilieslist:array($jobfamilieslist);

            $jobfamilies = tp::trainingprogram_jobfamily(0,$jobfamilieslist,$id);

        } elseif ($id > 0) {

            $jobfamilies = tp::trainingprogram_jobfamily(0,array(),$id, 'exam');
            
        }
  
        $jfdattributes = array(
            'ajax' => 'local_trainingprogram/sector_datasource',
            'data-type' => 'jobfamily',
            'data-sectorid' => 0,
            'multiple'=>true
        );
        $targetgroup = $mform->addElement('autocomplete', 'targetgroup',get_string('targetgroup', 'local_trainingprogram'),['0' => get_string('targetgroup', 'local_trainingprogram')]+$jobfamilies, $jfdattributes);
        $targetgroup->setMultiple(true);
        $mform->hideIf('targetgroup', 'alltargetgroup', 'checked');

        $clevels = [];
        $clevels[''] = '';
        $clevels['level1'] =  get_string('level1','local_competency');
        $clevels['level2'] =  get_string('level2','local_competency');
        $clevels['level3'] = get_string('level3','local_competency');
        $clevels['level4'] =  get_string('level4','local_competency');
        $clevels['level5'] = get_string('level5','local_competency') ; 

        $leveloptions = [
           
            'onchange' => "(function(e){ require(['local_trainingprogram/dynamic_dropdown_ajaxdata'], function(s) {s.clevels();}) }) (event)",
        ];
        
        $mform->addElement('autocomplete', 'clevels', get_string('clevels', 'local_exams'),$clevels,$leveloptions);
        $mform->setType('clevels', PARAM_ALPHANUMEXT);


        $competencytypes = tp::constcompetency_types();
        
        $competencytypeoptions = [
            'ajax' => 'local_trainingprogram/dynamic_dropdown_ajaxdata',
            'data-type' => 'program_competency',
            'class' => 'el_competencytype',
            'multiple'=>true,
            'onchange' => "(function(e){ require(['local_trainingprogram/dynamic_dropdown_ajaxdata'], function(s) {s.ctype();}) }) (event)",
        ];
        $mform->addElement('autocomplete', 'ctype', get_string('competency_type', 'local_exams'), $competencytypes, $competencytypeoptions);
        $mform->setType('ctype', PARAM_ALPHANUMEXT);

        $competencies = array();
        $competencieslist = $this->_ajaxformdata['competencylevel'];


        // if (!empty($competencieslist)) {

        //     $competencieslist = is_array($competencieslist)?$competencieslist:array($competencieslist);

        //     $competencies = (new local_exams\local\exams)->trainingprogram_competencylevels($competencieslist,$id);

        // } elseif ($id > 0) {

        //     $competencies = (new local_exams\local\exams)->trainingprogram_competencylevels(array(),$id);

        // }

        if (!empty($competencieslist)) {

            $competencies =(new local_exams\local\exams)->trainingprogram_competencylevels($competencieslist ,$id);

        } elseif ($id > 0) {

            $competencies = (new local_exams\local\exams)->trainingprogram_competencylevels(array(),$id);

        }
        $clattributes = array(
            'ajax' => 'local_trainingprogram/dynamic_dropdown_ajaxdata',
            'data-type' => 'program_competencylevel',
            'class' => 'el_competencieslist',
            'data-ctype' => $competencietypes,
            'data-programid' =>1,
            'data-offeringid' =>1,
            'noselectionstring' => get_string('noselection', 'local_trainingprogram'),
        );

        $competencyelemet= $mform->addElement('autocomplete', 'competencylevel',get_string('competencies', 'local_exams'),$competencies,$clattributes);
        $competencyelemet->setMultiple(true);

        $mform->addElement('editor', 'competencyweights', get_string('competencyweights', 'local_exams'), null, $editoroptions);
        $mform->setType('competencyweights', PARAM_RAW);
        $mform->addRule('competencyweights', get_string('missingcompetencyweights', 'local_exams'), 'required', null);

        $programs = array();
        $programslist = $this->_ajaxformdata['programs'];
        if (!empty($programslist)) {
            $programs =(new local_exams\local\exams)->programlist($programslist ,$id);
        } elseif ($id > 0) {
            $programs = (new local_exams\local\exams)->programlist(array(),$id);
        }
        $programattributes = array(
            'ajax' => 'local_exams/hall_datasource',
            'data-type' => 'preparations_programs',
            'class' => 'programslist',
            'data-programid' => 1,
            'noselectionstring' => get_string('noselection', 'local_trainingprogram'),
        );
        $select = $mform->addElement('autocomplete', 'programs', get_string('programs','local_exams'), $programs,$programattributes);
        $select->setMultiple(true);

        $exams = array();
        $examslist = $this->_ajaxformdata['requirements'];
        if (!empty($examslist)) {
            $exams =(new local_exams\local\exams)->examslist($examslist ,$id);
        } elseif ($id > 0) {
            $exams = (new local_exams\local\exams)->examslist(array(),$id);
        }
        $examattributes = array(
            'ajax' => 'local_exams/hall_datasource',
            'data-type' => 'exam_requirements',
            'class' => 'examslist',
            'data-programid' => 1,
            'noselectionstring' => get_string('noselection', 'local_trainingprogram'),
        );
        
        $examreq = $mform->addElement('autocomplete', 'requirements', get_string('requirements','local_exams'),$exams,$examattributes);
        // $examreq = $mform->setType('requirements', PARAM_TEXT);
        $examreq->setMultiple(true);

        // examshouldpass .. renu
        $radioarray = [];
        $attributes = []; 
        $radioarray[] = $mform->createElement('radio', 'examineeshouldpass', '', get_string('oneprerequisiteexams','local_exams'), 0, $attributes);
        $radioarray[] = $mform->createElement('radio', 'examineeshouldpass', '', get_string('allprerequisiteexams','local_exams'), 1, $attributes);
        $mform->addGroup($radioarray, 'examineeshouldpass', get_string('examineeshouldpass', 'local_exams'), [''], false);

        $mform->addElement('editor', 'additionalrequirements', get_string('additionalrequirements', 'local_exams'));
        $mform->setType('additionalrequirements', PARAM_RAW);

        $types = [1 => get_string('professionaltest','local_exams'), 2 => get_string('other','local_exams')];
        $mform->addElement('select', 'type', get_string('type', 'local_exams'), array(null=>get_string('selecttype','local_exams')) + $types);
        $mform->addRule('type', get_string('missingtype', 'local_exams'), 'required', null);

        $mform->addElement('text', 'typename', get_string('typename', 'local_exams'), ['class' => 'typename']);
        $mform->setType('typename', PARAM_TEXT);
        $mform->hideIf('typename', 'type', 'neq', 2);

        $validity = [];
        $validity[] = $mform->createElement('text', 'certificatevalidity', get_string('hour', 'form'), ['size' => 3]);
        $validity[] = $mform->createElement('select', 'year', get_string('year', 'local_form'), ['1' => get_string('years', 'local_exams')]);
        $mform->addGroup($validity, 'certificatevalidity', get_string('certificatevalidity', 'local_exams'), array(' '), false);

        $radioarray=array();
        $radioarray[] = $mform->createElement('radio', 'ownedbystatus', '', get_string('existing', 'local_exams'), 0, $attributes);
        $radioarray[] = $mform->createElement('radio', 'ownedbystatus', '', get_string('new', 'local_exams'), 1, $attributes);
        $mform->addGroup($radioarray, 'ownedbystatus', get_string('ownedbystatus', 'local_exams'), array(' '), false);


        $previousownedby = $DB->get_fieldset_sql("SELECT ownedby FROM {local_exams} WHERE status = 1 ");
        $previousownedbyvalues = implode(',',$previousownedby);
        $previousownedbyarray = array_unique(explode(',',$previousownedbyvalues));

        $previousownedbyfields = array_combine($previousownedbyarray, $previousownedbyarray);

        foreach ($previousownedbyfields as $key => $value) {
            $ownedbyresults[$key] = format_string($value);
        }
        $ownedby = $mform->addElement('autocomplete', 'previousownedby', get_string('ownedby','local_exams'), [null => get_string('selectownedby','local_exams')] + $ownedbyresults);   
      //  $ownedby->setMultiple(true); 
        // $mform->addRule('previousownedby', get_string('missingownedby', 'local_exams'), 'required', null);
        $mform->hideif('previousownedby', 'ownedbystatus', 'eq', 1);

        $mform->addElement('text', 'ownedby', get_string('ownedby', 'local_exams'));
        // $mform->addRule('ownedby', get_string('missingownedby', 'local_exams'), 'required', null);
        $mform->hideif('ownedby', 'ownedbystatus', 'eq', 0);

        $mform->addElement('editor', 'attachedmessage', get_string('attachedmessage', 'local_exams'), null, $editoroptions);
        $mform->setType('attachedmessage', PARAM_RAW);

        $mform->addElement('editor', 'termsconditions', get_string('tandc', 'local_exams'), null, $editoroptions);
        $mform->setType('termsconditions', PARAM_RAW);
        $mform->addRule('termsconditions', get_string('termsconditionnotempty', 'local_exams'), 'required', null);

        //add classification.. renu
        $classification_options = [];
        $classification_options['1'] = get_string('confidentials','local_exams');
        $classification_options['2']= get_string('public','local_exams');
        $mform->addElement('select','classification', get_string('classification', 'local_exams'),$classification_options);
        

        $mform->addElement('header', 'attemptsettings', get_string('attemptsettings', 'local_exams'));

        $mform->addElement('text', 'noofattempts', get_string('noofattempts', 'local_exams'), ['size' => 3]);
        $mform->setDefault('noofattempts', 1);
        if ($id > 0) {
            $mform->disabledIf('noofattempts', 'id', 'eq', $id);
        }

        $radioarray=array();
        $radioarray[] = $mform->createElement('radio', 'appliedperiod', '', get_string('oneyear', 'local_exams'), 0, $attributes);
        $radioarray[] = $mform->createElement('radio', 'appliedperiod', '', get_string('registrationperiod', 'local_exams'), 1, $attributes);
        $mform->addGroup($radioarray, 'radioar', get_string('appliedperiod', 'local_exams'), array(' '), false);


    }
    public function validation($data, $files) {
        global $DB, $CFG;
        $errors = array();
        $record = $DB->get_record('local_exams', ['exam' => $data['exam']]);
        $ownedby = trim($data['ownedby']);

        $examcode = trim($data['code']);
        if(empty($data['exam'])) {
            $errors['exam'] = get_string('examnotbeempty','local_exams');
        } elseif(strlen($data['exam']) < 3) {
            $errors['exam'] = get_string('examvalidate','local_exams');
        } else if(!empty($record) && $data['id'] == 0 ) {
            $errors['exam'] = get_string('examavailable','local_exams', $data['exam']);
        }

        if(empty($data['examnamearabic'])) {
            $errors['examnamearabic'] = get_string('examarabicnotbeempty','local_exams');
        }  else if(!empty($record) && $data['id'] == 0 ) {
            $errors['examnamearabic'] = get_string('examavailable','local_exams', $data['examnamearabic']);
        }

        if(empty(trim($data['noofattempts'])) || trim($data['noofattempts']) == 0){
                $errors['noofattempts'] = get_string('noofattemptscannotbeempty', 'local_exams');
        }
        if(!empty(trim($data['noofattempts'])) && !preg_match('/^[0-9]*$/',trim($data['noofattempts']))) {
            $errors['noofattempts'] = get_string('validattemptsrequired', 'local_exams'); 
        }
        if(empty($examcode)) {
            $errors['code'] = get_string('codenotbeempty','local_exams');
        } elseif(!empty($examcode) && $data['id'] <= 0 && $DB->record_exists_sql("SELECT id FROM {local_exams}  WHERE code = '$examcode'")) {
            $examrecord = $DB->get_record_sql("SELECT * FROM {local_exams}  WHERE code = '$examcode'");
            $examrecord->code = $examcode;
            $examrecord->codetakenexam = (current_language() == 'ar') ? $examrecord->examnamearabic : $examrecord->exam;
            $errors['code'] = get_string('codeistaken','local_exams',$examrecord);
        }
        if(!is_numeric($data['certificatevalidity']) && $data['certificatevalidity'] != '') {
            $errors['certificatevalidity'] = get_string('certificatevaliditynumaric','local_exams');
        }
        if($data['examprice'] == 1) {
            $sprice = $data['sellingprice'];
            $aprice = $data['actualprice'];

            if(empty($sprice)) {
                $errors['sellingprice'] = get_string('sellingpricemissing','local_exams');
            } elseif(!is_numeric($sprice)) {
                $errors['sellingprice'] = get_string('sellingpricenumaric','local_exams');
            }
            if(empty($aprice)) {
                $errors['actualprice'] = get_string('actualpricemissing','local_exams');
            } elseif(!is_numeric($aprice)) {
                $errors['actualprice'] = get_string('actualpricenumaric','local_exams');   
            } elseif($aprice > $sprice) {
                $errors['actualprice'] = get_string('apricemore','local_exams');
            }                        
        }

        if(empty($data['programdescription'])) {
            $errors['programdescription'] = get_string('programdescriptionnotbeempty','local_exams');
        }
        

        $termncondition = strip_tags($data['termsconditions']['text']);
        $text = str_replace("&nbsp;", '', $termncondition);
        if(ctype_space($text)) {
            $errors['termsconditions'] = get_string('termsconditionnoblankspace', 'local_exams');
        }
        else if (empty($data['termsconditions'])) {
            $errors['termsconditions'] = get_string('termsconditionnotempty', 'local_exams');
         } 
       

        if(empty($data['sectors'])) {
            $errors['sectors'] = get_string('sectorsnotbeempty','local_exams');
        }
        if(empty($data['targetaudience'])) {
            $errors['targetaudience'] = get_string('targetaudiencenotbeempty','local_exams');
        }
        if(empty($data['competencyweights'])) {
            $errors['competencyweights'] = get_string('competencyweightsnotbeempty','local_exams');
        }
        if(empty($data['type'])) {
            $errors['type'] = get_string('typenotbeempty','local_exams');
        }
        if($data['type'] == 2) {
            if(empty($data['typename'])) {
                $errors['typename'] = get_string('typenamenotbeempty','local_exams');
            }
        }        
       
        if( !empty($data['fee']) ){
            if ( !is_numeric ($data['fee'])) {
                $errors['fee']= get_string('feenumaric', 'local_exams');
            }            
        }
        if (!empty($record)) {
            if ($data['noofattempts'] < $record->noofattempts) {
                $errors['noofattempts']= get_string('noofattemptsshouldbemore', 'local_exams', $record->noofattempts);
            }
        }

        if($data['ownedbystatus'] == 0){
            
            if(empty($data['previousownedby'])){
            $errors['previousownedby'] = get_string('missingownedby','local_exams');
            }        
       }
    else{
        
        if(empty($data['ownedby'])){
            $errors['ownedby'] = get_string('missingownedby','local_exams');
        } elseif(!empty($data['ownedby']) && !preg_match('/^\S*$/',$data['ownedby'])) {
            $errors['ownedby'] = get_string('spacessacantaccepted','local_exams');
        }else{
            $examownedby = $DB->record_exists('local_exams',array('ownedby' =>$ownedby));
            if ($examownedby) {
               
                $getdata = $DB->get_record('local_exams', array('ownedby'=>$ownedby));
                if ($getdata->id != $data['id']) {
                   
                   $errors['ownedby'] = get_string('ownedbyfieldexists','local_exams',$ownedby);
                   
                }
            }
        }
      }

    
      return $errors;
    }
    /**
     * Returns context where this form is used
     *
     * @return context
     */
    protected function get_context_for_dynamic_submission(): context {
        return \context_system::instance();
    }

    /**
     * Checks if current user has access to this form, otherwise throws exception
     */
    protected function check_access_for_dynamic_submission(): void {
        // require_capability('moodle/site:config', $this->get_context_for_dynamic_submission());
         has_capability('local/exams:create', $this->get_context_for_dynamic_submission()) 
                || has_capability('local/organization:manage_examofficial', $this->get_context_for_dynamic_submission());
    }

    /**
     * Process the form submission, used if form was submitted via AJAX
     */
    public function process_dynamic_submission() {
        global $CFG, $DB;
        require_once($CFG->dirroot.'/user/profile/definelib.php');
        $data = $this->get_data();
       
        $systemcontext = context_system::instance();
        (new local_exams\local\exams)->add_update_exam($data);
    }

    /**
     * Load in existing data as form defaults
     */
    public function set_data_for_dynamic_submission(): void {
        global $DB;
        if ($id = $this->optional_param('id', 0, PARAM_INT)) {
            $exam = (new local_exams\local\exams)->set_exam($id);
            $this->set_data($exam);
        }
    }

    /**
     * Returns url to set in $PAGE->set_url() when form is being rendered or submitted via AJAX
     *
     * @return moodle_url
     */
    protected function get_page_url_for_dynamic_submission(): moodle_url {
        $id = $this->optional_param('id', 0, PARAM_INT);
        return new moodle_url('/local/exams/index.php',
            ['action' => 'editcategory', 'id' => $id]);
    }    
}
