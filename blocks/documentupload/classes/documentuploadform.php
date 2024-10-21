<?php
namespace block_documentupload;

use core_form\dynamic_form;
use moodle_url;
use context;
use context_system;
use block_documentupload;
use block_documentupload\documentupload as documentupload;
    /**
     * 
     */
    class documentuploadform extends dynamic_form
    {

    /** @var profile_define_base $field */
    public $field;
    /** @var \stdClass */
    protected $fieldrecord;

    /**
     * Define the form
     */
    public function definition () {
        global $CFG,$DB;
        $mform = $this->_form;
        $systemcontext = context_system::instance();
        $id = $this->optional_param('id', 0, PARAM_INT);
   
        $mform->addElement('hidden','id', $id);
        $mform->addElement('header', 'general', get_string('general', 'form'));

        $mform->addElement('text', 'docrank', get_string('rank','block_documentupload'), 'maxlength="100" size="10"');
        $mform->addRule('docrank', get_string('acceptsnumeric', 'block_documentupload'), 'numeric');
        $mform->setType('docrank', PARAM_TEXT);

       /* $options = array("0"=>get_string('English','block_documentupload') ,"1"=>get_string('Arabic','block_documentupload'));
        $mform->addElement('select', 'langauge', get_string('language','block_documentupload'), $options);
        $mform->addRule('language', get_string('required'), 'required',null,'server');
        $mform->setType('select',PARAM_NOTAGS);*/


        $mform->addElement('text','title', get_string('titleenglish', 'block_documentupload'),'maxlength="254" size="50"');
        $mform->addRule('title', get_string('required'), 'required', null, 'server');
        $mform->setType('title', PARAM_TEXT);

        $mform->addElement('text','titlearabic', get_string('titlearabic', 'block_documentupload'),'maxlength="254" size="50"');
        $mform->addRule('titlearabic', get_string('required'), 'required', null, 'server');
        $mform->setType('titlearabic', PARAM_TEXT);

         $mform->addElement('editor','description', get_string('description','block_documentupload'));
        $mform->addRule('description', get_string('description','block_documentupload'), 'required', null, 'server');
        $mform->setType('description', PARAM_TEXT);


        $options = array("1"=>get_string('documentdrop','block_documentupload') ,"2"=>get_string('videodrop','block_documentupload'));
        $mform->addElement('select', 'media', get_string('media','block_documentupload'), $options);
        $mform->addRule('media', get_string('required'), 'required',null,'server');
        $mform->setType('select',PARAM_NOTAGS);


        $filemanageroptions = array(
            'accepted_types' => array(get_string('pdf','block_documentupload')),
            'maxbytes' => 0,
            'maxfiles' => 1,
            ); 
            
     
            $mform->addElement('filepicker', 'document', get_string('document', 'block_documentupload'), '', $filemanageroptions);
            $mform->hideif('document', 'media', 'eq', '2');

            $mform->addElement('filepicker', 'arabicdocument', get_string('arabicdocument', 'block_documentupload'), '', $filemanageroptions);
            $mform->hideif('arabicdocument', 'media', 'eq', '2');

            $videovalidations = array(
                'accepted_types' => array(get_string('mp4','block_documentupload')),
                'maxbytes' => 0,
                'maxfiles' => 1,
                );

            $mform->addElement('filepicker', 'video', get_string('video', 'block_documentupload'), '', $videovalidations);
            $mform->hideif('video', 'media', 'eq', '1');

            $mform->addElement('filepicker', 'videoar', get_string('videoar', 'block_documentupload'), '', $videovalidations);
            $mform->hideif('videoar', 'media', 'eq', '1');
        
        
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

        $rank = trim($data['docrank']);
        $errors = parent::validation($data, $files);
        if ($dupfound ) {
            $errors['name'] = get_string('profilecategorynamenotunique', 'admin');
        }

        $rankmapped = $DB->record_exists('documentupload',array('docrank' =>$rank));
        if (!empty($rank) && is_numeric($rank) && preg_match('/^[0-9]*$/',$rank) && $rankmapped) {
            $docdata = $DB->get_record('documentupload', array('docrank'=>$rank));
            if (empty($data['id']) || $docdata->id != $data['id']) {
               $errors['docrank'] = get_string('rankexist', 'block_documentupload',$faqdata->title);
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
         require_capability('moodle/site:config', $this->get_context_for_dynamic_submission());
    }

    /**
     * Process the form submission, used if form was submitted via AJAX
     */
    public function process_dynamic_submission() {
        global $CFG, $DB;
        require_once($CFG->dirroot.'/user/profile/definelib.php');

        $data = $this->get_data();

        $context = context_system::instance();
        
            $document = (new documentupload)->add_update_documentupload($data);
            if($data->media == 1){
            if($document){

                $this->save_stored_file('document', $context->id, 'block_documentupload', 'documentupload',  $data->document, '/', null, true);
                $this->save_stored_file('arabicdocument', $context->id, 'block_documentupload', 'documentupload',  $data->arabicdocument, '/', null, true);

                           
               
            }
        } else{
          
                $this->save_stored_file('video', $context->id, 'block_documentupload', 'documentupload',  $data->video, '/', null, true);
                $this->save_stored_file('videoar', $context->id, 'block_documentupload', 'documentupload',  $data->videoar, '/', null, true);

           
        }
        

       

        /*$data = $this->get_data();
        (new block_documentupload\documentupload)->add_update_documentupload($data);*/
        
        
    }



    /**
     * Load in existing data as form defaults
     */
    public function set_data_for_dynamic_submission(): void {
        global $DB;
        $id = $this->optional_param('id', 0, PARAM_INT);
        if ($id = $this->optional_param('id', 0, PARAM_INT)) {
            $data = $DB->get_record('documentupload', ['id' => $id], '*', MUST_EXIST);
           // print_r($data);exit;
            $str = $data->title;
            // Setting title for enlish title field
            preg_match('/{mlang en}(.*?){mlang}/', $str, $match);
            $englishtitle =  $match[1];

            // Setting title for arabic title field
             preg_match('/{mlang ar}(.*?){mlang}/', $str, $match);
            $arabictitle =  $match[1];

            $this->set_data(['id'=>$data->id,'title' => $englishtitle,'titlearabic' => $arabictitle,'document' => $data->document,'video' => $data->video,'arabicdocument'=>$data->arabicdocument,'videoar'=>$data->videoar,'description' => ['text' => $data->description],'media' => $data->mediatype,'docrank'=>$data->docrank]);
        }
    }

    /**
     * Returns url to set in $PAGE->set_url() when form is being rendered or submitted via AJAX
     *
     * @return moodle_url
     */
    protected function get_page_url_for_dynamic_submission(): moodle_url {
        $id = $this->optional_param('id', 0, PARAM_INT);
        return new moodle_url('/blocks/documentupload/index.php',
            ['action' => 'editdocumentupload', 'id' => $id]);
    }
    }
