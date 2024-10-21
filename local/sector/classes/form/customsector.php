<?php
namespace local_sector\form;

    
use core_form\dynamic_form ;
use moodle_url;
use context;
use context_system;
use html_writer;
use local_sector\controller as sector;
class customsector extends  dynamic_form{
    //Add elements to form
    public function definition() {
        global $CFG,$DB,$USER;

          $mform = $this->_form; // Don't forget the underscore!
          $id = $this->optional_param('id', 0, PARAM_INT);
          $mform->addElement('hidden', 'id', $id);
          $mform->setType('id', PARAM_INT);



          $mform->addElement('text', 'title', get_string('title_sector', 'local_sector')); // Add elements to your form.
          $mform->addRule('title', get_string('title_secerr','local_sector'), 'required', null);
          $mform->setType('text', PARAM_ALPHANUM);

          $mform->addElement('text', 'titlearabic', get_string('title_sectorarabic', 'local_sector')); // Add elements to your form.
          $mform->addRule('titlearabic', get_string('title_secerr','local_sector'), 'required', null);
          $mform->setType('text', PARAM_ALPHANUM);

          $cansectoredit = $DB->record_exists_sql("SELECT id FROM {local_sector} WHERE  id = $id  AND (code ='B' OR  code ='F' OR code ='I'/* OR code ='V'*/)"); 

          if($id > 0  && $cansectoredit ){
            $sectorcode = $DB->get_field_sql('SELECT code FROM {local_sector} WHERE id = '.$id.'');
            $mform->addElement('static', 'seccode', get_string('code_sector', 'local_sector'),$sectorcode);
            $mform->addElement('hidden', 'code',$sectorcode);

          } else {

            $mform->addElement('text', 'code', get_string('code_sector', 'local_sector')); // Add elements to your form.
            $mform->addRule('code', get_string('title_codeerr','local_sector'), 'required', null);
            $mform->addRule('code', get_string('onlynumberandletters', 'local_sector'), 'alphanumeric', null);
            $mform->setType('text', PARAM_ALPHANUM);  
          }

 

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
        $sectorcode = $DB->get_record_sql("SELECT id,code FROM {local_sector} where code ='{$code}'");
         if ($sectorcode && (empty($data['id']) || $sectorcode->id != $data['id'])) {
            $errors['code'] = get_string('sec_codeerr', 'local_sector');
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
        global $CFG, $DB,$USER;
    

        $data = $this->get_data();
        $usermodified=$USER->id;

        if($data){
            if($data->id >0){
        $sectordata = (new sector)->update_sectors($data);

            }
            else{
        $sectordata = (new sector)->create_sectors($data);
            }
        }
           }
 public function set_data_for_dynamic_submission(): void {
        global $DB;
        if ($id = $this->optional_param('id', 0, PARAM_INT)) {
            $data = $DB->get_record('local_sector', ['id' => $id], '*', MUST_EXIST);
            $this->set_data(['id'=>$data->id,'title' => $data->title,'titlearabic' => $data->titlearabic, 'code' => $data->code]);
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

