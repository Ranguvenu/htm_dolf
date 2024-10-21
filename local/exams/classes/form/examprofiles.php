<?php
namespace local_exams\form;

use core_form\dynamic_form;
use moodle_url;
use context;
use context_system;
use local_exams;
use local_trainingprogram\local\trainingprogram as tp;

class examprofiles extends dynamic_form { 
    public function definition() {
        global $USER, $CFG,$DB;
        $corecomponent = new \core_component();

        $mform = $this->_form;
        $id = $this->optional_param('id', 0, PARAM_INT);
        $examid = $this->optional_param('examid', 0, PARAM_INT);
        $editoroptions = $this->_customdata['editoroptions'];
         
        $systemcontext = context_system::instance();
    
        $mform->addElement('hidden', 'id', $id);
        $mform->setType('id',PARAM_INT);

        $mform->addElement('hidden', 'examid', $examid);
        $mform->setType('examid',PARAM_INT);

        $enrolledtraineessql=" SELECT COUNT(id) FROM {exam_enrollments} WHERE examid = $examid 
        AND profileid = $id AND enrolstatus=1";
        $enrolledcount = $DB->count_records_sql($enrolledtraineessql);

        $mform->addElement('hidden', 'enrolledcount', $enrolledcount);
        $mform->setType('enrolledcount', PARAM_INT);

        $radioarray=array();
        $radioarray[] = $mform->createElement('radio', 'activestatus', '', get_string('no', 'local_exams'), 0, $attributes);
        $radioarray[] = $mform->createElement('radio', 'activestatus', '', get_string('yes', 'local_exams'), 1, $attributes);
        $mform->addGroup($radioarray, 'activestatus', get_string('activestatus', 'local_exams'), array(' '), false);
        $mform->addRule('activestatus', get_string('missingactivestatus', 'local_exams'), 'required', null);

        $radioarray=array();
        $radioarray[] = $mform->createElement('radio', 'publishstatus', '', get_string('no', 'local_exams'), 0, $attributes);
        $radioarray[] = $mform->createElement('radio', 'publishstatus', '', get_string('yes', 'local_exams'), 1, $attributes);
        $mform->addGroup($radioarray, 'publishstatus', get_string('publishstatus', 'local_exams'), array(' '), false);
        $mform->addRule('publishstatus', get_string('missingpublishstatus', 'local_exams'), 'required', null);

        $decision = [1 => get_string('approved','local_exams'), 2 => get_string('rejected','local_exams'), 3 => get_string('underreview','local_exams'), 4 => get_string('draft','local_exams')];
        $mform->addElement('select', 'decision', get_string('decision', 'local_exams'), array(null=>get_string('selecttype','local_exams')) + $decision);
        $mform->addRule('decision', get_string('missingdecision', 'local_exams'), 'required', null);

        $types = [0 => get_string('arabic','local_exams'), 1 => get_string('english','local_exams')];
        $mform->addElement('select', 'language', get_string('language', 'local_exams'), array(null=>get_string('selectlanguage','local_exams')) + $types);
        $mform->addRule('language', get_string('missinglanguage', 'local_exams'), 'required', null);
        
        $mform->addElement('duration', 'duration', get_string('duration', 'local_exams'),  ['units'=> [MINSECS], 'class' => 'duration']);
        $mform->addRule('duration', get_string('missingduration', 'local_exams'), 'required', null);

        $mform->addElement('text', 'seatingcapacity', get_string('seatingcapacity', 'local_exams'), ['size' => 4]);
        $mform->setType('seatingcapacity', PARAM_TEXT);
        if($id > 0)  {
            $code = $DB->get_field('local_exam_profiles','profilecode',array('id'=>$id));
            $mform->addElement('text', 'profilecode', get_string('profilecode', 'local_exams'),$code);                
            $mform->addElement('hidden', 'profilecode',$code);
            $mform->addElement('hidden', 'profilecode');
            $mform->setDefault('profilecode', $code);
            $mform->setType('profilecode', PARAM_TEXT);


        } else {

            $mform->addElement('text', 'profilecode', get_string('profilecode', 'local_exams'));
            $mform->setType('profilecode', PARAM_TEXT);
            $mform->addRule('profilecode', get_string('missingprofilecode', 'local_exams'), 'required', null);        

        } 

        $mform->addElement('text', 'questions', get_string('questions', 'local_exams'), ['size' => 4]);
        $mform->addRule('questions', get_string('missingquestions', 'local_exams'), 'required', null);

        $mform->addElement('text', 'trailquestions', get_string('trailquestions', 'local_exams'), ['size' => 4]);

        $materialstate=array();
        $materialstate[] = $mform->createElement('radio', 'material', '', get_string('uploadfile', 'local_exams'), 0, $attributes);
        $materialstate[] = $mform->createElement('radio', 'material', '', get_string('url', 'local_exams'), 1, $attributes);
        $mform->addGroup($materialstate, 'material', get_string('materialstate', 'local_exams'), array(' '), false);

        $filemanageroptions = array(
            'accepted_types' => '*',
            'maxbytes' => 0,
            'maxfiles' => 1,
        );
        $mform->addElement('filepicker','materialfile',get_string('material', 'local_exams'),null,$filemanageroptions);
        $mform->hideIf('materialfile', 'material', 'eq', 1);

        $mform->addElement('text', 'materialurl', get_string('material', 'local_exams'));
        $mform->setType('materialurl', PARAM_TEXT);
        $mform->hideIf('materialurl','material', 'eq', 0);

        $targetaudience = [1 => get_string('saudi','local_exams'), 2 => get_string('nonsaudi','local_exams'), 3 => get_string('both','local_exams')];
        $mform->addElement('select', 'targetaudience', get_string('targetaudienceprofile', 'local_exams'), array(null=>get_string('selecttargetaudience','local_exams')) + $targetaudience);
        $mform->addRule('targetaudience', get_string('missingtargetaudience', 'local_exams'), 'required', null);

        $mform->addElement('editor', 'nondisclosure', get_string('nondisclosure', 'local_exams'), null, $editoroptions);
        $mform->setType('nondisclosure', PARAM_RAW);
        $mform->addRule('nondisclosure', get_string('missingnondisclosure', 'local_exams'), 'required', null);

        $mform->addElement('editor', 'instructions', get_string('instructions', 'local_exams'), null, $editoroptions);
        $mform->setType('instructions', PARAM_RAW);
        $mform->addRule('instructions', get_string('missinginstructions', 'local_exams'), 'required', null);

        $mform->addElement('date_selector', 'registrationstartdate', get_string('registrationstartdate', 'local_exams'), ['optional' => true]);

        $mform->addElement('date_selector', 'registrationenddate', get_string('registrationenddate', 'local_exams'), ['optional' => true] );

        $mform->addElement('text', 'password', get_string('password', 'local_exams'), ['class' => 'password']);
        $mform->setType('password', PARAM_TEXT);

        $mform->addElement('text', 'passinggrade', get_string('passinggrade', 'local_exams'), ['class' => 'passinggrade', 'size' => 4], );
        $mform->setType('passinggrade', PARAM_TEXT);
        $mform->addRule('passinggrade', get_string('missingpassinggrade', 'local_exams'), 'required', null);

        //add classification.. renu
        $classification_options = [];
        $classification_options['1'] = get_string('confidentials','local_exams');
        $classification_options['2']= get_string('public','local_exams');
        $mform->addElement('select','classification', get_string('classification', 'local_exams'),$classification_options);

        $radioarray=array();
        $radioarray[] = $mform->createElement('radio', 'hascertificate', '', get_string('no', 'local_exams'), 0, $attributes);
        $radioarray[] = $mform->createElement('radio', 'hascertificate', '', get_string('yes', 'local_exams'), 1, $attributes);
        $mform->addGroup($radioarray, 'radioar', get_string('hascertificate', 'local_exams'), array(' '), false);
        $mform->setDefault('hascertificate', 1);

        $radioarray=array();
        $radioarray[] = $mform->createElement('radio', 'preexampage', '', get_string('no', 'local_exams'), 0, $attributes);
        $radioarray[] = $mform->createElement('radio', 'preexampage', '', get_string('yes', 'local_exams'), 1, $attributes);
        $mform->addGroup($radioarray, 'radioar', get_string('preexampage', 'local_exams'), array(' '), false);
	    $mform->setDefault('preexampage', 1);

        $radioarray=array();
        $radioarray[] = $mform->createElement('radio', 'successrequirements', '', get_string('no', 'local_exams'), 0, $attributes);
        $radioarray[] = $mform->createElement('radio', 'successrequirements', '', get_string('yes', 'local_exams'), 1, $attributes);
        $mform->addGroup($radioarray, 'radioar', get_string('successrequirements', 'local_exams'), array(' '), false);
        $mform->setDefault('successrequirements', 1);

        $radioarray=array();
        $radioarray[] = $mform->createElement('radio', 'showquestions', '', get_string('no', 'local_exams'), 0, $attributes);
        $radioarray[] = $mform->createElement('radio', 'showquestions', '', get_string('yes', 'local_exams'), 1, $attributes);
        $mform->addGroup($radioarray, 'radioar', get_string('showquestions', 'local_exams'), array(' '), false);
        $mform->setDefault('showquestions', 1);

        $radioarray=array();
        $radioarray[] = $mform->createElement('radio', 'showexamduration', '', get_string('no', 'local_exams'), 0, $attributes);
        $radioarray[] = $mform->createElement('radio', 'showexamduration', '', get_string('yes', 'local_exams'), 1, $attributes);
        $mform->addGroup($radioarray, 'radioar', get_string('showexamduration', 'local_exams'), array(' '), false);
        $mform->setDefault('showexamduration', 1);

        $radioarray=array();
        $radioarray[] = $mform->createElement('radio', 'showremainingduration', '', get_string('no', 'local_exams'), 0, $attributes);
        $radioarray[] = $mform->createElement('radio', 'showremainingduration', '', get_string('yes', 'local_exams'), 1, $attributes);
        $mform->addGroup($radioarray, 'radioar', get_string('showremainingduration', 'local_exams'), array(' '), false);
        $mform->setDefault('showremainingduration', 1);

        $radioarray=array();
        $radioarray[] = $mform->createElement('radio', 'commentsoneachque', '', get_string('no', 'local_exams'), 0, $attributes);
        $radioarray[] = $mform->createElement('radio', 'commentsoneachque', '', get_string('yes', 'local_exams'), 1, $attributes);
        $mform->addGroup($radioarray, 'radioar', get_string('commentsoneachque', 'local_exams'), array(' '), false);
        $mform->setDefault('commentsoneachque', 1);

        $radioarray=array();
        $radioarray[] = $mform->createElement('radio', 'commentsaftersub', '', get_string('no', 'local_exams'), 0, $attributes);
        $radioarray[] = $mform->createElement('radio', 'commentsaftersub', '', get_string('yes', 'local_exams'), 1, $attributes);
        $mform->addGroup($radioarray, 'radioar', get_string('commentsaftersub', 'local_exams'), array(' '), false);
        $mform->setDefault('commentsaftersub', 1);

        $radioarray=array();
        $radioarray[] = $mform->createElement('radio', 'showexamresult', '', get_string('no', 'local_exams'), 0, $attributes);
        $radioarray[] = $mform->createElement('radio', 'showexamresult', '', get_string('yes', 'local_exams'), 1, $attributes);
        $mform->addGroup($radioarray, 'radioar', get_string('showexamresult', 'local_exams'), array(' '), false);
        $mform->setDefault('showexamresult', 1);

        $radioarray=array();
        $radioarray[] = $mform->createElement('radio', 'showexamgrade', '', get_string('no', 'local_exams'), 0, $attributes);
        $radioarray[] = $mform->createElement('radio', 'showexamgrade', '', get_string('yes', 'local_exams'), 1, $attributes);
        $mform->addGroup($radioarray, 'radioar', get_string('showexamgrade', 'local_exams'), array(' '), false);
        $mform->setDefault('showexamgrade', 1);

        $radioarray=array();
        $radioarray[] = $mform->createElement('radio', 'competencyresult', '', get_string('no', 'local_exams'), 0, $attributes);
        $radioarray[] = $mform->createElement('radio', 'competencyresult', '', get_string('yes', 'local_exams'), 1, $attributes);
        $mform->addGroup($radioarray, 'radioar', get_string('competencyresult', 'local_exams'), array(' '), false);
        $mform->setDefault('competencyresult', 1);

        $radioarray=array();
        $radioarray[] = $mform->createElement('radio', 'resultofeachcompetency', '', get_string('no', 'local_exams'), 0, $attributes);
        $radioarray[] = $mform->createElement('radio', 'resultofeachcompetency', '', get_string('yes', 'local_exams'), 1, $attributes);
        $mform->addGroup($radioarray, 'radioar', get_string('resultofeachcompetency', 'local_exams'), array(' '), false);
        $mform->setDefault('resultofeachcompetency', 1);

        $radioarray=array();
        $radioarray[] = $mform->createElement('radio', 'evaluationform', '', get_string('no', 'local_exams'), 0, $attributes);
        $radioarray[] = $mform->createElement('radio', 'evaluationform', '', get_string('yes', 'local_exams'), 1, $attributes);
        $mform->addGroup($radioarray, 'radioar', get_string('evaluationform', 'local_exams'), array(' '), false);
        $mform->setDefault('evaluationform', 1);

        $radioarray=array();
        $radioarray[] = $mform->createElement('radio', 'notifybeforeexam', '', get_string('no', 'local_exams'), 0, $attributes);
        $radioarray[] = $mform->createElement('radio', 'notifybeforeexam', '', get_string('yes', 'local_exams'), 1, $attributes);
        $mform->addGroup($radioarray, 'radioar', get_string('notifybeforeexam', 'local_exams'), array(' '), false);
        $mform->setDefault('notifybeforeexam', 1);


        
    }
    public function validation($data, $files) {
        global $DB, $CFG;
        $errors = array();
        if(!empty($data['id'])) {
            $existingseats = $DB->get_field('local_exam_profiles','seatingcapacity',array('id' => $data['id']));
            if($existingseats > 0 && $data['enrolledcount'] > 0) {
                if($data['seatingcapacity'] < $data['enrolledcount']) {
                     $errors['seatingcapacity'] = get_string('cannotbelowerthanexistng', 'local_trainingprogram',$data['enrolledcount']);
                } 
            }
        }
        if(empty($data['profilecode']) && empty($data['id'])){
            $errors['profilecode'] = get_string('profilecodenotbeempty','local_exams');
        } elseif($DB->record_exists('local_exam_profiles',array('profilecode'=>$data['profilecode'])) && empty($data['id'])){
            $errors['profilecode'] = get_string('profilecodeisavailable','local_exams');
        }
        $examuserhall = $DB->get_record('local_exam_userhallschedules',['profileid' => $data['id']]);

        if(empty($data['profilecode']) && empty($data['id'])){
            $errors['profilecode'] = get_string('examprofilecodenotbeempty','local_exams');
        } elseif($DB->record_exists('local_exam_profiles',array('profilecode'=>$data['profilecode'])) && !empty($data['id']) && $examuserhall){
            $errors['profilecode'] = get_string('examprofilecodeisavailable','local_exams');
        }
        if(empty($data['duration'])) {
            $errors['duration'] = get_string('missingduration','local_exams');
        } elseif(!is_numeric($data['duration'])) {
            $errors['duration'] = get_string('durationshouldbenumaric','local_exams');
        }

        if( !empty($data['passinggrade']) ){
            if ( !is_numeric ($data['passinggrade'])) {
                $errors['passinggrade']= get_string('quizpassgradenumaric', 'local_exams');
            } elseif($data['passinggrade'] > 100) {
                $errors['passinggrade']= get_string('passgradebelowhundered', 'local_exams');            
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
        return context_system::instance();
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
        
        if($data->material == 0) {
           $f2 = $this->save_stored_file('materialfile', $systemcontext->id, 'local_exams', 'materialfile',  $data->materialfile, '/', null, true);
           $data->materialurl = NULL;
        } else {
            $data->materialfile = 0;
            $f2 = $this->save_stored_file('materialfile', $systemcontext->id, 'local_exams', 'materialfile',  0, '/', null, true);
        }

        (new local_exams\local\exams)->add_update_profile($data);
    }

    /**
     * Load in existing data as form defaults
     */
    public function set_data_for_dynamic_submission(): void {
        global $DB;
        $id = $this->optional_param('id', 0, PARAM_INT);
        if ($id) {
            $profile = (new local_exams\local\exams)->set_examprofile($id);
            $this->set_data($profile);
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
