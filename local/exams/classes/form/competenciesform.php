<?php
namespace local_exams\form;

use core_form\dynamic_form;
use moodle_url;
use context;
use context_system;
use local_exams;
use local_trainingprogram\local\trainingprogram as tp;

class competenciesform extends dynamic_form { 
    public function definition() {
        global $USER, $CFG,$DB;
        $corecomponent = new \core_component();
        $id = $this->optional_param('id', 0, PARAM_INT);
        $type = $this->optional_param('type', '', PARAM_RAW);

        $mform = $this->_form;

        $mform->addElement('hidden', 'id', $id);
        $mform->setType('id',PARAM_INT);

        $mform->addElement('hidden', 'type', $type);
        $mform->setType('type',PARAM_RAW);
       
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
        $mform->addRule('clevels', get_string('missinglevel', 'local_exams'), 'required', null);

        $competencytypes = tp::constcompetency_types();
        
        $competencytypeoptions = [
            'ajax' => 'local_trainingprogram/dynamic_dropdown_ajaxdata',
            'data-type' => 'program_competency',
            'class' => 'el_competencytype',
            'multiple'=>true,
            'onchange' => "(function(e){ require(['local_trainingprogram/dynamic_dropdown_ajaxdata'], function(s) {s.ctype();}) }) (event)",            
        ];
        $mform->addElement('autocomplete', 'ctype', get_string('competency_type', 'local_exams'),$competencytypes,$competencytypeoptions);
        $mform->setType('ctype', PARAM_ALPHANUMEXT);
        $mform->addRule('ctype', get_string('missingcompetencytype', 'local_exams'), 'required', null);

        $competencies = array();
        $competencieslist = $this->_ajaxformdata['competencylevel'];
        if (!empty($competencieslist)) {
            $competencieslist = is_array($competencieslist)?$competencieslist:array($competencieslist);
            $competencies = (new local_exams\local\exams)->trainingprogram_competencylevels($competencieslist,$id);
        } elseif ($id > 0) {
            $competencies = (new local_exams\local\exams)->trainingprogram_competencylevels(array(),$id);
        }

        $clattributes = array(
            'ajax' => 'local_trainingprogram/dynamic_dropdown_ajaxdata',
            'data-type' => 'program_competencylevel',
            'class' => 'el_competencieslist',
            'data-ctype' => $competencietypes,
            'data-programid' =>$id,
            'data-offeringid' =>1
        );

        $competencyelemet= $mform->addElement('autocomplete', 'competencylevel',get_string('competencies', 'local_exams'),$competencies,$clattributes);
        $competencyelemet->setMultiple(true);
        $mform->addRule('competencylevel', get_string('missingcompetencylevel', 'local_exams'), 'required', null);
    }
    public function validation($data, $files) {
        global $DB, $CFG;
        $errors = array();
        if(empty($data['competencylevel'])) {
            $errors['competencylevel'] = get_string('competenciesnotbeempty','local_exams');
        }
        if(empty($data['ctype'])) {
            $errors['ctype'] = get_string('ctypenotbeempty','local_exams');                
        }
        if(empty($data['ctype'])) {
            $errors['ctype'] = get_string('ctypenotbeempty','local_exams');                
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
         has_capability('local/exams:create', $this->get_context_for_dynamic_submission()) 
        || has_capability('local/organization:manage_examofficial', $this->get_context_for_dynamic_submission());
        // require_capability('moodle/site:config', $this->get_context_for_dynamic_submission());
    }

    /**
     * Process the form submission, used if form was submitted via AJAX
     */
    public function process_dynamic_submission() {
        global $CFG, $DB;
        require_once($CFG->dirroot.'/user/profile/definelib.php');
        $data = $this->get_data();
        $row['id'] = $data->id;
        $row['competencyandlevels'] = implode(',', array_filter($data->competencylevel));
        $row['clevels'] = $data->clevels;
        $row['ctype'] = implode(',', $data->ctype);
        $row['competencies'] = implode(',', array_filter($data->competencylevel));
        if ($data->type == 'program') {
            return $DB->update_record('local_trainingprogram', $row);
        } else {
            return $DB->update_record('local_exams', $row);            
        }

    }

    /**
     * Load in existing data as form defaults
     */
    public function set_data_for_dynamic_submission(): void {
        global $DB;
        $id = $this->optional_param('id', 0, PARAM_INT);
        $type = $this->optional_param('type', 0, PARAM_RAW);
        if ($id) {
            $exam = (new local_exams\local\exams)->set_exam($id, $type);
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
