<?php
namespace local_trainingprogram\form;
defined('MOODLE_INTERNAL') || die();
require_once($CFG->libdir.'/formslib.php');
require_once($CFG->libdir.'/csvlib.class.php');
use csv_import_reader;
use core_text;
use moodleform;
use context_user;
use html_table;
use html_writer;

class uploadprogram_form extends moodleform {

    /**
     * Form definition
     */
    public function definition() {
        $mform = $this->_form;
        $data  = (object)$this->_customdata;

        // $mform->addElement('hidden', 'returnurl');
        // $mform->setType('returnurl', PARAM_URL);

        $mform->addElement('header', 'programuploadform', get_string('programuploadform', 'local_trainingprogram'));
        $filepickeroptions = array(
                    'accepted_types' => array(get_string('csv', 'local_trainingprogram')),
                    'maxbytes' => 0,
                    'maxfiles' => 1,
        );
        $mform->addElement('filepicker', 'programfile', get_string('file'), null, $filepickeroptions);
        $mform->addRule('programfile', get_string('programfilefilerequired','local_trainingprogram'), 'required', null);
        $mform->addHelpButton('programfile', 'uploaddoc', 'local_trainingprogram');

        $this->add_action_buttons(true, get_string('upload'));
    }

    public function validation($data, $files) {
        $errors = parent::validation($data, $files);
        return $errors;
    }
}
