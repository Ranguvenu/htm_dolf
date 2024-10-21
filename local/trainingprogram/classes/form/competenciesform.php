<?php
namespace local_trainingprogram\form;

use core_form\dynamic_form ;
use moodle_url;
use context;
use context_system;
use local_trainingprogram\local\trainingprogram as tp;
use local_trainingprogram\local\dataprovider as dataprovider;

class competenciesform extends dynamic_form { 
    public function definition() {
        global $USER, $CFG,$DB;
        $corecomponent = new \core_component();
        $id = $this->optional_param('id', 0, PARAM_INT);

        $mform = $this->_form;

        $mform->addElement('hidden', 'id', $id);
        $mform->setType('id',PARAM_INT);

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

            $competencies = tp::trainingprogram_competencylevels(array($competencieslist ),$id);

        } elseif ($id > 0) {

            $competencies = tp::trainingprogram_competencylevels(array(),$id);

        }

        $competencietypes = json_encode($competencytypes);

       /* var_dump($competencietypes);
        exit;*/


        $clattributes = array(
            'ajax' => 'local_trainingprogram/dynamic_dropdown_ajaxdata',
            'data-type' => 'program_competencylevel',
            'class' => 'el_competencieslist',
            'data-ctype' => $competencietypes,
            'data-programid' =>$id,
            'data-offeringid' =>1
        );

        $competencyelemet= $mform->addElement('autocomplete', 'competencylevel',get_string('competencies', 'local_trainingprogram'),$competencies,$clattributes);
        $competencyelemet->setMultiple(true);
        $mform->addRule('competencylevel', get_string('missingcompetencylevel', 'local_exams'), 'required', null);        
        
    }
    public function validation($data, $files) {
        global $DB, $CFG;
        $errors = array();
        if(empty($data['ctype'])) {
            $errors['ctype'] = get_string('ctypenotbeempty','local_exams');                
        }
        if(empty($data['competencylevel'])) {
            $errors['competencylevel'] = get_string('competenciesnotbeempty','local_exams');
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
         has_capability('local/trainingprogram:manage', $this->get_context_for_dynamic_submission()) 
        || has_capability('local/organization:manage_trainingofficial', $this->get_context_for_dynamic_submission());
    }

    /**
     * Process the form submission, used if form was submitted via AJAX
     */
    public function process_dynamic_submission() {
        global $CFG, $DB;
        require_once($CFG->dirroot.'/user/profile/definelib.php');
        $data = $this->get_data();
        // var_dump($data);
        // exit;

        $row['id'] = $data->id;
        $row['clevels'] = $data->clevels;
        $row['competencyandlevels'] = implode(',', array_filter($data->competencylevel));
        return $DB->update_record('local_trainingprogram', $row);
    }

    /**
     * Load in existing data as form defaults
     */
    public function set_data_for_dynamic_submission(): void {
        global $DB;
         if ($id = $this->optional_param('id', 0, PARAM_INT)) {
            $data = $DB->get_record('local_trainingprogram', ['id' => $id], '*', MUST_EXIST);
            $data->competencylevel=$data->competencyandlevels;
            if(!empty($data->competencyandlevels)){
            $sql = "SELECT cmt.type,cmt.type as fullname FROM {local_competencies} AS cmt WHERE cmt.id IN ($data->competencyandlevels)";
            $competencietypes=$DB->get_records_sql_menu($sql);
            $data->ctype=$competencietypes;
            }
            /*$data->ctype=tp::constcompetencytypes();*/
            $this->set_data($data);
        }
    }

    /**
     * Returns url to set in $PAGE->set_url() when form is being rendered or submitted via AJAX
     *
     * @return moodle_url
     */
    protected function get_page_url_for_dynamic_submission(): moodle_url {
        return new moodle_url('/local/trainingprogram/index.php');
    }    
}
