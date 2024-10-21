<?php
namespace local_sector\form;

    
use core_form\dynamic_form ;
use moodle_url;
use context;
use context_system;
use html_writer;
use local_sector\controller as sector;
class customsegment extends  dynamic_form{
    //Add elements to form
    public function definition() {
        global $CFG,$DB;

          $mform = $this->_form; // Don't forget the underscore!
          $id = $this->_customdata['id'];
          $sectorid = $this->_ajaxformdata['sectorid'];
       
          $mform->addElement('hidden', 'id');
          $mform->setType('int', PARAM_INT);
          $mform->setDefault('id', $id);

          $mform->addElement('hidden', 'sectorid');
          $mform->setType('int', PARAM_INT);
          $mform->setDefault('sectorid', $sectorid);
          

          $mform->addElement('text', 'title', get_string('title_segment', 'local_sector')); // Add elements to your form.
          $mform->addRule('title', get_string('title_segerr', 'local_sector'), 'required', null);
          $mform->setType('text', PARAM_ALPHANUM);

          $mform->addElement('text', 'titlearabic', get_string('title_segmentarabic', 'local_sector')); // Add elements to your form.
          $mform->addRule('titlearabic', get_string('title_segerr', 'local_sector'), 'required', null);
          $mform->setType('text', PARAM_ALPHANUM);  

          $mform->addElement('text', 'code', get_string('code_segment', 'local_sector')); // Add elements to your form.
          $mform->addRule('code', get_string('title_segcodeerr', 'local_sector'), 'required', null);
          $mform->addRule('code', get_string('onlynumberandletters', 'local_sector'), 'alphanumeric', null);
          $mform->setType('text', PARAM_ALPHANUM);

          $mform->addElement('editor', 'description', get_string('description', 'local_sector')); // Add elements to your form.
          //$mform->addRule('description', get_string('descriptionerr', 'local_sector'), 'required', null);
          $mform->setType('description', PARAM_RAW);   

         

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
        $sectorid = $data['sectorid'];
        if (strrpos($code,' ') !== false){
            $errors['code'] = get_string('jobrole_codespaceerr', 'local_sector');
        }
        $segmentcode = $DB->get_record_sql("SELECT id,code FROM {local_segment} where code ='{$code}' ");
         if ($segmentcode && (empty($data['id']) || $segmentcode->id != $data['id'])) {
            $errors['code'] = get_string('seg_codeerr', 'local_sector');
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
                $sectordata = (new sector)->update_segment($data);
            }else{
                $sectordata = (new sector)->create_segment($data);
            }
        }
           }
 public function set_data_for_dynamic_submission(): void {
        global $DB;
        if ($id = $this->optional_param('id', 0, PARAM_INT)) {
            $data = $DB->get_record('local_segment', ['id' => $id], '*', MUST_EXIST);
            $this->set_data(['id'=>$data->id,'title' => $data->title,'titlearabic' => $data->titlearabic, 'code' => $data->code , 'description' =>['text' => $data->description]]);
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
            ['action' => 'editsectors', 'id' => $id]);
    }

}

