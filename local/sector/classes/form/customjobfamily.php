<?php
namespace local_sector\form;

    
use core_form\dynamic_form ;
use moodle_url;
use context;
use context_system;
use html_writer;
use local_sector\controller as sector;
use local_userapproval\action\manageuser as manageuser;
use cache;
require_once($CFG->libdir . '/formslib.php');

class customjobfamily extends  dynamic_form{
    //Add elements to form
    public function definition() {
        global $CFG,$DB;

          $mform = $this->_form; // Don't forget the underscore!
          
          $id = $this->optional_param('id', 0, PARAM_INT);
        
          $shared = $this->optional_param('shared', 0, PARAM_BOOL);

          $mform->addElement('hidden', 'id', $id);
          $mform->setType('id', PARAM_INT);
         

           $mform->addElement('advcheckbox', 'shared', get_string('common', 'local_sector'),null,null,[0,1]);
           $mform->setType('shared', PARAM_BOOL);
           $mform->disabledIf('shared', 'id', 'neq', 0);


           $currentlang= current_language();
        if($id > 0) {
            if($currentlang == 'ar') {
                $title = 'sec.titlearabic';
            } else {
                $title = 'sec.title';
            }

            $selectedsegments = $DB->get_field('local_jobfamily','segmentid',['id'=>$id]);

            $sector = $DB->get_record_sql(" SELECT DISTINCT sec.id, $title as name FROM {local_sector} AS sec 
                JOIN {local_segment} AS seg ON seg.sectorid = sec.id
                WHERE FIND_IN_SET(seg.id,'$selectedsegments')");
            if(!$shared){
                $mform->addElement('static', 'sectorname',get_string('sector','local_sector'),$sector->name);
                  $mform->addElement('hidden', 'sectors',$sector->id);
                  $mform->setType('sectors', PARAM_INT);  
            }
            

        } else {
            if( $currentlang == 'ar'){

               $sectors = $DB->get_records_sql_menu("SELECT id,titlearabic AS fullname FROM {local_sector} WHERE  titlearabic <> '' AND titlearabic IS NOT NULL ");

            } else {

               $sectors = $DB->get_records_sql_menu("SELECT id,title AS fullname FROM {local_sector} ORDER BY title ASC");
            }
             $sectoroptions = array(

            'class' => 'el_sectorlist',
             'onchange' => "(function(e){ require(['local_trainingprogram/dynamic_dropdown_ajaxdata'], function(s) {s.sectorschanged();}) }) (event)",
            );

            $sectorelement =$mform->addElement('select','sectors', get_string('sectors', 'local_sector'),$sectors,$sectoroptions);
        }

        $segments = array();
        $segmentlist = $this->_ajaxformdata['segments'];
             
        if (!empty($segmentlist)) {

            $segmentlist = is_array($segmentlist)?$segmentlist:array($segmentlist);


            $segments = (new sector)->segments_list($segmentlist ,$id);

        } elseif ($id > 0) {

            $segments = (new sector)->segments_list(array(),$id);

        }

        if($id > 0) {
            $lang = current_language();
            if($lang == 'ar') {
                $title = 'sec.titlearabic';
            } else {
                $title = 'sec.title';
            }
      
            $segmentdattributes = array(
             
              'ajax' => 'local_trainingprogram/sector_datasource',
              'data-type' => 'segment',
              'id' => 'el_segmentlist',
              'data-sectorid' => $sector->id,
              'multiple' => true,
             'onchange' => "(function(e){ require(['local_trainingprogram/dynamic_dropdown_ajaxdata'], function(s) {s.segmentschanged();}) }) (event)",
            );
       
          $mform->addElement('autocomplete', 'segments',get_string('segment','local_sector'),$segments, $segmentdattributes);
          //$mform->addRule('segments', get_string('segment', 'local_sector'), 'required', null);

        } else {
            $segmentdattributes = array(
             
              'ajax' => 'local_trainingprogram/sector_datasource',
              'data-type' => 'segment',
              'id' => 'el_segmentlist',
              'data-sectorid' => 0,
              'multiple' => true,
             'onchange' => "(function(e){ require(['local_trainingprogram/dynamic_dropdown_ajaxdata'], function(s) {s.segmentschanged();}) }) (event)",
            );
            $mform->addElement('autocomplete', 'segments',get_string('segment','local_sector'),$segments,$segmentdattributes);
            //$mform->addRule('segments', get_string('segments_codeerr', 'local_sector'), 'required', null);
        }
 
        
        $mform->hideIf('sectors', 'shared', 'checked');
        $mform->hideIf('segments', 'shared', 'checked');

          $mform->addElement('text', 'familyname', get_string('familynameeng', 'local_sector')); // Add elements to your form.
          $mform->addRule('familyname', get_string('title_joberr', 'local_sector'), 'required', null);
          $mform->setType('text', PARAM_ALPHANUM);  

          $mform->addElement('text', 'familynamearabic', get_string('familynamearabic', 'local_sector')); // Add elements to your form.
          $mform->addRule('familynamearabic', get_string('title_joberr', 'local_sector'), 'required', null);
          $mform->setType('text', PARAM_ALPHANUM); 

          $mform->addElement('text', 'code', get_string('jobcode', 'local_sector')); // Add elements to your form.
          $mform->addRule('code', get_string('jobcodeerr', 'local_sector'), 'required', null);
          $mform->addRule('code', get_string('onlynumberandletters', 'local_sector'), 'alphanumeric', null);
          $mform->setType('text', PARAM_ALPHANUM);  

          $mform->addElement('editor', 'description', get_string('description', 'local_sector')); // Add elements to your form.
          //$mform->addRule('description', get_string('descriptionerr', 'local_sector'), 'required', null);
          $mform->setType('description', PARAM_RAW); 

           $filemanageroptions = array(
            'accepted_types' => array(get_string('png_format', 'local_trainingprogram'), 
                get_string('jpg_format', 'local_trainingprogram'))
           );
           
          $mform->addElement('filemanager','careerpath',get_string('careerpath', 'local_sector'),null,$filemanageroptions);

          $arfilemanageroptions = array(
            'accepted_types' => array(get_string('png_format', 'local_trainingprogram'), 
                get_string('jpg_format', 'local_trainingprogram'))
           );
           
          $mform->addElement('filemanager','careerpath_ar',get_string('careerpathar', 'local_sector'),null,$arfilemanageroptions);

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

        $segmentcode = $DB->get_record_sql("SELECT id,code FROM {local_jobfamily} where code ='{$code}' ");
        if ($segmentcode && (empty($data['id']) || $segmentcode->id != $data['id'])) {
            $errors['code'] = get_string('jobcode_codeerr', 'local_sector');
        }

        if($data['shared'] == 0  && !empty($data['sectors']) && empty($data['segments'])) {

            $errors['segments'] = get_string('segments_codeerr', 'local_sector');
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
            $context = context_system::instance();
            if($data->id >0){
                $sectordata = (new sector)->update_jobfamily($data);
                 //$this->save_stored_file('careerpath', $context->id, 'local_sector', 'jobfamilycareerpath',  $data->careerpath, '/', null, true);
            } else {
                $sectordata = (new sector)->create_jobfamily($data);
                //$this->save_stored_file('careerpath', $context->id, 'local_sector', 'jobfamilycareerpath',  $data->careerpath, '/', null, true);
            
            }
            file_save_draft_area_files($data->careerpath,$context->id, 'local_sector', 'jobfamilycareerpath',$data->careerpath,);
            if($data->careerpath_ar)
            {
                file_save_draft_area_files($data->careerpath_ar,$context->id, 'local_sector', 'jobfamilycareerpath',$data->careerpath_ar);
            }
             $cache = cache::make('local_sector', 'jobfamilies');
             $cache->delete('jobfamilylist_ar');
             $cache->delete('jobfamilylist_en');
        }
    }
 public function set_data_for_dynamic_submission(): void {
        global $DB;
        if ($id = $this->optional_param('id', 0, PARAM_INT)) {
            $data = $DB->get_record('local_jobfamily', ['id' => $id], '*', MUST_EXIST);
            $data->description = ['text' => $data->description];
            $data->segments = $data->segmentid;
            $data->careerpath = $data->careerpath;
            $data->careerpath_ar = $data->careerpath_ar;
          
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
            ['action' => 'editsectors', 'id' => $id]);
    }

}

