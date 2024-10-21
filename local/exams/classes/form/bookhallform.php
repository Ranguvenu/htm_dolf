<?php
namespace local_exams\form;

use core_form\dynamic_form;
use moodle_url;
use context;
use context_system;
use local_exams;

class bookhallform extends dynamic_form { 
    public function definition() {
        global $USER, $CFG,$DB;
        $corecomponent = new \core_component();

        $mform = $this->_form;
        $id = $this->_customdata['id'];
        $editoroptions = $this->_customdata['editoroptions'];
         
        $systemcontext = context_system::instance();
    
        $mform->addElement('hidden', 'id', '');
        $mform->setType('id',PARAM_INT);

        $mform->addElement('text', 'exam', get_string('exam', 'local_exams'));
        $mform->setType('exam', PARAM_TEXT);
        $mform->addRule('exam', get_string('missingexam', 'local_exams'), 'required', null, 'client');

        $mform->addElement('text', 'code', get_string('code', 'local_exams'));
        $mform->setType('code', PARAM_TEXT);
        $mform->addRule('code', get_string('missingcode', 'local_exams'), 'required', null, 'client');

        $radioarray=array();
        $radioarray[] = $mform->createElement('radio', 'examprice', '', get_string('complimentary', 'local_exams'), 1, $attributes);
        $radioarray[] = $mform->createElement('radio', 'examprice', '', get_string('paid', 'local_exams'), 0, $attributes);
        $mform->addGroup($radioarray, 'radioar', get_string('examprice', 'local_exams'), array(' '), false);

        $mform->addElement('text', 'sellingprice', get_string('sellingprice', 'local_exams'));
        $mform->setType('sellingprice', PARAM_TEXT);
        $mform->addRule('sellingprice', get_string('missingexamprice', 'local_exams'), 'required', null, 'client');

        $mform->addElement('text', 'actualprice', get_string('actualprice', 'local_exams'));
        $mform->setType('actualprice', PARAM_TEXT);
        $mform->addRule('actualprice', get_string('missingactualprice', 'local_exams'), 'required', null, 'client');        

        $mform->addElement('textarea', 'programdescription', get_string('programdescription', 'local_exams'), null, $editoroptions);
        $mform->setType('programdescription', PARAM_TEXT);

        $sectors = ['1' => 'Insurance', '2' => 'Banks', '3' => 'Money bills'];
        $select = $mform->addElement('autocomplete', 'sectors', get_string('sectors','local_exams'), $sectors);
        $select->setMultiple(true);
        $mform->addRule('sectors', get_string('missingsectors', 'local_exams'), 'required', null, 'client');


        $mform->addElement('text', 'targetaudience', get_string('targetaudience', 'local_exams'));
        $mform->setType('targetaudience', PARAM_TEXT);
        $mform->addRule('targetaudience', get_string('missingtargetaudience', 'local_exams'), 'required', null, 'client');

        $mform->addElement('textarea', 'competencies', get_string('competencies', 'local_exams'), null, $editoroptions);
        $mform->setType('competencies', PARAM_TEXT);

        $programs = ['1' => 'Venture capital and investment in start-ups', '2' => 'Work ethics'];
        $select = $mform->addElement('autocomplete', 'programs', get_string('programs','local_exams'), $programs);
        $select->setMultiple(true);

        $mform->addElement('text', 'requirements', get_string('requirements', 'local_exams'));
        $mform->setType('requirements', PARAM_TEXT);
        $mform->addRule('requirements', get_string('missingrequirements', 'local_exams'), 'required', null, 'client');
        
        $types = ['1' => 'Professional test', '2' => 'other'];
        $mform->addElement('select', 'type', get_string('type', 'local_exams'), array(null=>get_string('selecttype','local_exams')) + $types);

        $languages = ['1' => 'Arabic', '2' => 'English'];
        $select = $mform->addElement('autocomplete', 'language', get_string('language','local_exams'), $languages);
        $select->setMultiple(true);

        $mform->addElement('text', 'certificatevalidity', get_string('certificatevalidity', 'local_exams'));
        $mform->setType('certificatevalidity', PARAM_TEXT);
        $mform->addRule('certificatevalidity', get_string('missingcertificatevalidity', 'local_exams'), 'required', null, 'client');

        $mform->addElement('text', 'ownedby', get_string('ownedby', 'local_exams'));
        $mform->setType('ownedby', PARAM_TEXT);
        $mform->addRule('ownedby', get_string('missingownedby', 'local_exams'), 'required', null, 'client');

        $mform->addElement('duration', 'examduration', get_string('examduration', 'local_exams'),  ['units'=> [MINSECS]]);

        $noofquestions = ['1' => 10, '2' => 50];
        $mform->addElement('select', 'noofquestions', get_string('noofquestions', 'local_exams'), array(null=>get_string('selectnoofquestions','local_exams')) + $noofquestions);

        $mform->addElement('date_selector', 'enddate', get_string('enddate', 'local_exams'));
        $mform->addRule('enddate', get_string('missingenddate', 'local_exams'), 'required', null, 'client');

        $halladdress = ['1' => 'Hall1', '2' => 'Hall2'];
        $mform->addElement('select', 'halladdress', get_string('halladdress', 'local_exams'), array(null=>get_string('selecthalladdress','local_exams')) + $halladdress);

        $mform->addElement('filemanager', 'learningmaterial', get_string('learningmaterial', 'local_exams'), null, array('maxbytes' => $maxbytes, 'accepted_types' => '*'));
        // $mform->addElement('filepicker', 'learningmaterial', get_string('learningmaterial', 'local_exams'), null, array('maxbytes' => $maxbytes, 'accepted_types' => '*'));
    }
    public function validation($data, $files) {
        global $DB, $CFG;
        $errors = array();
        $record = $DB->get_record('local_exams', ['exam' => $data['exam']]);
        if(strlen($data['exam']) < 3) {
            $errors['exam'] = get_string('examvalidate','local_exams');
        } else if(!empty($record) && $data['id'] == 0 ) {
            $errors['exam'] = get_string('examavailable','local_exams', $data['exam']);
        } 
        $sprice = $data['sellingprice'];
        $aprice = $data['actualprice'];
        if($sprice > $aprice) {
            $errors['sellingprice'] = get_string('spricemore','local_exams');
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
        require_capability('moodle/site:config', $this->get_context_for_dynamic_submission());
    }

    /**
     * Process the form submission, used if form was submitted via AJAX
     */
    public function process_dynamic_submission() {
        global $CFG, $DB;
        require_once($CFG->dirroot.'/user/profile/definelib.php');
        $data = $this->get_data();
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
