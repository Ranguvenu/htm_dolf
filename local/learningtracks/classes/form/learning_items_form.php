<?php
namespace local_learningtracks\form;
use core_form\dynamic_form ;
use moodle_url;
use context;
use context_system;
use local_learningtracks;

class learning_items_form extends dynamic_form{

    /** @var profile_define_base $field */
    public $field;
    /** @var \stdClass */
    protected $fieldrecord;

    /**
     * Define the form
     */
    public function definition () {
        global $CFG, $DB;
        $mform = $this->_form;
        $id = $this->optional_param('id', 0, PARAM_INT);
        $trackid = $this->optional_param('trackid', 0, PARAM_INT);
        $orgid = $DB->get_field('local_learningtracks','organization',['id' => $trackid]);
        $mform->addElement('hidden', 'id');
        $mform->setType('int', PARAM_INT);
        $mform->setDefault('id', $id);

        $mform->addElement('hidden', 'trackid');
        $mform->setType('int', PARAM_INT);
        $mform->setDefault('trackid', $trackid); 

        $mform->addElement('hidden', 'orgid', $orgid);
        
        $attributes = array('1' => 'trainingprograms','2' => 'exams');
        $radioarray = array();
        $radioarray[] = $mform->createElement('radio', 'itemtype', '', get_string('trainingprograms','local_learningtracks'), 1, $attributes);
        $radioarray[] = $mform->createElement('radio', 'itemtype', '', get_string('exams','local_learningtracks'), 2, $attributes);

        $mform->addGroup($radioarray, 'itemtype','', array('class' => 'itemtype'), false);
        $mform->setDefault('itemtype', 1);

        $programoptions = array(
            'ajax' => 'local_learningtracks/form_selector_datasource',
            'data-type' => 'programlist',
            'id' => 'el_program',
            'data-programid' => '',
            'data-trackid' => $trackid,
            'data-orgid' => $orgid,
            'multiple' => true,
        );
        $selectprogram = [null =>get_string('selectprogram', 'local_learningtracks')];
        $mform->addElement('autocomplete','program', '' , $selectprogram, $programoptions);
        $mform->setType('program', PARAM_RAW);
        $mform->hideIf('program', 'itemtype', 'eq', 2); 
        
        $examoptions = array(
            'ajax' => 'local_learningtracks/form_selector_datasource',
            'data-type' => 'examlist',
            'id' => 'el_exam',
            'data-examid' => '',
            'data-trackid' => $trackid,
            'multiple' => true,
        );
        $selectexam = [null => get_string('selectexam', 'local_learningtracks')];
        $mform->addElement('autocomplete','exam', '', $selectexam, $examoptions);
        $mform->setType('exam', PARAM_RAW);
        $mform->hideIf('exam', 'itemtype', 'eq', 1);
        
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
        $itemtype = $data['itemtype'];
        if ($itemtype == "1") {
            if (empty($data['program'])) {
                $errors['program'] = get_string('pleaseselectpro', 'local_learningtracks');
            }
        }
        if ($itemtype == "2") {
            if (empty($data['exam'])) {
                $errors['exam'] = get_string('pleaseselectexam', 'local_learningtracks');
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
        (new local_learningtracks\learningtracks)->add_learning_items($data);
    }

    /**
     * Load in existing data as form defaults
     */
    public function set_data_for_dynamic_submission(): void {
        global $DB;
        if ($id = $this->optional_param('id', 0, PARAM_INT)) {
            $data = $DB->get_record('local_learning_items', ['id' => $id], '*', MUST_EXIST);
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
        return new moodle_url('/local/learningtracks/index.php',
            ['id' => $id]);
    }
}
