<?php
namespace local_sector\form;

    
use core_form\dynamic_form ;
use moodle_url;
use context;
use context_system;
use html_writer;
use local_sector\controller as sector;
use local_trainingprogram\local\trainingprogram as tp;
class jobrole_level extends  dynamic_form{
    //Add elements to form
    public function definition() {
        global $CFG,$DB; 

           $mform = $this->_form; // Don't forget the underscore!
         
           $id = $this->optional_param('id', 0, PARAM_INT);
           $jobid = $this->optional_param('jobfamilyid', 0, PARAM_INT);

           $mform->addElement('hidden', 'id', $id);
           $mform->setType('id', PARAM_INT);

          $mform->addElement('hidden', 'jobid',$jobid);
          $mform->setType('jobid', PARAM_INT);

          $mform->addElement('text', 'title', get_string('jobroletitleeng', 'local_sector')); // Add elements to your form.
          $mform->addRule('title', get_string('titleerr', 'local_sector'), 'required', null);
          $mform->setType('text', PARAM_ALPHANUM);  

          $mform->addElement('text', 'titlearabic', get_string('jobroletitlearabic', 'local_sector')); // Add elements to your form.
          $mform->addRule('titlearabic', get_string('titleerr', 'local_sector'), 'required', null);
          $mform->setType('text', PARAM_ALPHANUM);  

          $mform->addElement('text', 'code', get_string('jobrolecode', 'local_sector')); // Add elements to your form.
          $mform->addRule('code', get_string('jobcodeerrr', 'local_sector'), 'required', null);
          $mform->addRule('code', get_string('onlynumberandletters', 'local_sector'), 'alphanumeric', null);
          $mform->setType('text', PARAM_ALPHANUM);  

         
          $mform->addElement('editor', 'description', get_string('description', 'local_sector')); // Add elements to your form.
          $mform->addRule('description', get_string('enterdescription', 'local_sector'), 'required', null);
          $mform->setType('description', PARAM_RAW); 
       
        $clevels = [];
        $clevels[null] = get_string('select_level','local_sector');
        $clevels['level1'] =  get_string('level1','local_sector');
        $clevels['level2'] =  get_string('level2','local_sector');
        $clevels['level3'] = get_string('level3','local_sector');
        $clevels['level4'] =  get_string('level4','local_sector');
        $clevels['level5'] = get_string('level5','local_sector') ; 

         $leveloptions = [
            'onchange' => "(function(e){ require(['local_trainingprogram/dynamic_dropdown_ajaxdata'], function(s) {s.clevels();}) }) (event)",
        ];
         $mform->addElement('select', 'clevels', get_string('level','local_sector'), $clevels,$leveloptions);
         $mform->addRule('clevels', get_string('selectlevel','local_sector'), 'required', null);
         $mform->setType('clevels', PARAM_RAW);

         $competencytypes = tp::constcompetency_types();

        $competencytypeoptions = [
            'ajax' => 'local_trainingprogram/dynamic_dropdown_ajaxdata',
            'data-type' => 'program_competency',
            'class' => 'el_competencytype',
            'multiple'=>true,
            'onchange' => "(function(e){ require(['local_trainingprogram/dynamic_dropdown_ajaxdata'], function(s) {s.ctype();}) }) (event)",
        ];
        $mform->addElement('autocomplete', 'ctype', get_string('competency_type', 'local_trainingprogram'),$competencytypes,$competencytypeoptions);
        $mform->setType('ctype', PARAM_ALPHANUMEXT);
        $mform->addRule('ctype', get_string('missingcompetencytype', 'local_exams'), 'required', null);


        if ($id = $this->optional_param('id', 0, PARAM_INT)) {
            $data = $DB->get_record('local_jobrole_level', ['id' => $id], '*', MUST_EXIST);
            $data->competencylevel=$data->competencies;
            $this->set_data($data);
           if($data->competencylevel) {
               $competencieslist = $data->competencylevel;
            } else {
                $competencieslist = $this->_ajaxformdata['competencylevel']; 
            }
           
        } else {

            $competencieslist = $this->_ajaxformdata['competencylevel']; 
        }



       if (!empty($competencieslist)) {
                  
            $competencies = (new sector)->competencies_data(0,$competencieslist);

        } elseif ($id > 0) {

            $competencies = (new sector)->competencies_data($id,array());

        }

        $clattributes = array(
            'ajax' => 'local_trainingprogram/dynamic_dropdown_ajaxdata',
            'data-type' => 'program_competencylevel',
            'class' => 'el_competencieslist',
            'data-ctype' =>'',
             'multiple'=>true,
            'data-programid' =>1,
            'data-offeringid' =>1
        );

        $mform->addElement('autocomplete', 'competencylevel',get_string('competencies', 'local_trainingprogram'),$competencies,$clattributes);
        $mform->addRule('competencylevel', get_string('missingcompetencylevel', 'local_exams'), 'required', null);


        $mform->addElement('hidden', 'status');
        $mform->setType('int', PARAM_INT);  
                         // Set type of element.
    }

           /**
     * Perform some moodle validation.
     *
     * @param array $datas
     * @param array $files
     * @return array
     */
    public function validation($data, $files) {
        global $DB;

        $errors = parent::validation($data, $files);
    
        $code =$data['code'];
        
        if (strrpos($code,' ') !== false){
            $errors['code'] = get_string('jobrole_codespaceerr', 'local_sector');
        }
        $jobrolecode = $DB->get_record_sql("SELECT id,code FROM {local_jobrole_level} where code ='{$code}'");
        if ( $jobrolecode && (empty($data['id']) ||  $jobrolecode->id != $data['id'])) {
            $errors['code'] = get_string('jobrole_codeerr', 'local_sector');
        }
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
        require_capability('moodle/site:config', $this->get_context_for_dynamic_submission());    
    }

    public function process_dynamic_submission() {
        $data = $this->get_data();
        if($data){
            if($data->id >0){
                $sectordata = (new sector)->update_jobrole_level($data);
            }else{
                $sectordata = (new sector)->create_jobrole_level($data);
            }
        }
    }

    public function set_data_for_dynamic_submission(): void {
        global $DB;
        if ($id = $this->optional_param('id', 0, PARAM_INT)) {
            $data = $DB->get_record('local_jobrole_level', ['id' => $id], '*', MUST_EXIST);
            $data->description = ['text' => $data->description];
            $data->clevels=$data->level;
            $data->ctype=$data->ctypes;
            //$data->competencylevel=$data->competencies;
            $this->set_data($data);
        }
    }
      /**
     * Returns url to set in $PAGE->set_url() when form is being rendered or submitted via AJAX
     *
     * @return moodle_url
     */
    protected function get_page_url_for_dynamic_submission(): moodle_url {
        $id = $this->optional_param('id', 0, PARAM_INT);
        return new moodle_url('/local/sector/index.php',
            ['action' => 'addjobrole', 'id' => $id]);
    }

}

