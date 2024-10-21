<?php
namespace local_competency\form;
defined('MOODLE_INTERNAL') || die();
require_once($CFG->libdir.'/formslib.php');
require_once($CFG->libdir.'/csvlib.class.php');
use csv_import_reader;
use core_text;
use moodleform;
use context_user;
use html_table;
use html_writer;

class uploadcompetency_form extends moodleform {

    /**
     * Form definition
     */
    public function definition() {
        $mform = $this->_form;
        $data  = (object)$this->_customdata;

        // $mform->addElement('hidden', 'returnurl');
        // $mform->setType('returnurl', PARAM_URL);

        $mform->addElement('header', 'competencyuploadform', get_string('competencyuploadform', 'local_competency'));

        $filepickeroptions = array();
        $filepickeroptions['filetypes'] = '*';
        $filepickeroptions['maxbytes'] = get_max_upload_file_size();
        $mform->addElement('filepicker', 'competencyfile', get_string('file'), null, $filepickeroptions);
        $mform->addRule('competencyfile', get_string('competencyfilerequired','local_competency'), 'required', null);

        $this->add_action_buttons(false);
    }

    public function validation($data, $files) {
        $errors = parent::validation($data, $files);
        return $errors;
    }
}