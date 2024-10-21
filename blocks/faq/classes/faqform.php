<?php
namespace block_faq;

use core_form\dynamic_form;
use moodle_url;
use context;
use context_system;
    /**
     * 
     */
    class faqform extends dynamic_form {
    public function definition () {
        global $CFG,$DB;
        $mform = $this->_form;
        $id = $this->optional_param('id', 0, PARAM_INT);
        $systemcontext = context_system::instance();

        $mform->addElement('hidden', 'id', $id);
        $mform->setType('id', PARAM_INT);

        $mform->addElement('text','faqrank', get_string('rank', 'block_faq'));
        $mform->addRule('faqrank', get_string('required','block_faq'), 'required', null, 'server');
        $mform->setType('faqrank', PARAM_TEXT);

        $mform->addElement('text','titlearabic', get_string('titlearabic', 'block_faq'),'maxlength="254" size="50"');
        $mform->addRule('titlearabic', get_string('required','block_faq'), 'required', null, 'server');
        $mform->setType('titlearabic', PARAM_TEXT);

        $mform->addElement('text','title', get_string('titleenglish', 'block_faq'),'maxlength="254" size="50"');
        $mform->addRule('title', get_string('required','block_faq'), 'required', null, 'server');
        $mform->setType('title', PARAM_TEXT);

        $mform->addElement('editor','description', get_string('description','block_faq'));
        $mform->addRule('description', get_string('required','block_faq'), 'required', null, 'server');
        $mform->setType('description', PARAM_RAW);

        

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

        $rank = trim($data['faqrank']);

        if(!empty($rank) && !is_numeric($rank)) {

            $errors['faqrank'] = get_string('notnumeric', 'block_faq'); 
        }
        if(!empty($rank) && is_numeric($rank) && !preg_match('/^[0-9]*$/',$rank)) {
            $errors['faqrank'] = get_string('validseatsrequired', 'block_faq'); 
        }

        $rankmapped = $DB->record_exists('faq',array('faqrank' =>$rank));
        if (!empty($rank) && is_numeric($rank) && preg_match('/^[0-9]*$/',$rank) && $rankmapped) {
            $faqdata = $DB->get_record('faq', array('faqrank'=>$rank));
            if (empty($data['id']) || $faqdata->id != $data['id']) {
               $errors['faqrank'] = get_string('rankexist', 'block_faq',$faqdata->title);
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
        $data = $this->get_data();
        if($data) {
            (new faq)->add_update_faq($data);
        }
      
    }

    /**
     * Load in existing data as form defaults
     */
    public function set_data_for_dynamic_submission(): void {
        global $DB;
        if ($id = $this->optional_param('id', 0, PARAM_INT)) {
            $faqdata = (new faq)->set_faq($id);
            $str = $faqdata->title;
            
            // Setting title for enlish title field
            preg_match('/{mlang en}(.*?){mlang}/', $str, $match);
            $englishtitle =  $match[1];

            // Setting title for arabic title field
             preg_match('/{mlang ar}(.*?){mlang}/', $str, $match);
            $arabictitle =  $match[1];
            $faqdata->title = $englishtitle;
            $faqdata->titlearabic = $arabictitle;
            $faqdata->description = ['text' =>$faqdata->description];
            $this->set_data($faqdata);
        }
    }

    /**
     * Returns url to set in $PAGE->set_url() when form is being rendered or submitted via AJAX
     *
     * @return moodle_url
     */
    protected function get_page_url_for_dynamic_submission(): moodle_url {
        $id = $this->optional_param('id', 0, PARAM_INT);
        return new moodle_url('/blocks/faq/index.php',
            ['action' => 'editfaq', 'id' => $id]);
    }
    }
