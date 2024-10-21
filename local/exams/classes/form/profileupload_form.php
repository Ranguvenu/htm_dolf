<?php
namespace local_exams\form;
defined('MOODLE_INTERNAL') || die();
require_once($CFG->libdir.'/formslib.php');
require_once($CFG->libdir.'/csvlib.class.php');
use csv_import_reader;
use core_text;
use moodleform;
use context_user;
use html_table;
use html_writer;

class profileupload_form extends moodleform {

    /**
     * Form definition
     */
    public function definition() {
        $mform = $this->_form;
        $data  = (object)$this->_customdata;

        // $mform->addElement('hidden', 'returnurl');
        // $mform->setType('returnurl', PARAM_URL);

        $mform->addElement('header', 'profileuploadform', get_string('profileuploadform', 'local_exams'));
        /*$filepickeroptions = array(
                    'accepted_types' => array(get_string('csv', 'local_exams')),
                    'maxbytes' => 0,
                    'maxfiles' => 1,
        );*/
        $mform->addElement('filepicker', 'profilefile', get_string('file'), null, null);
        $mform->addRule('profilefile', get_string('profilefilerequired','local_exams'), 'required', null);
        $mform->addHelpButton('profilefile', 'uploaddoc', 'local_exams');

        $this->add_action_buttons(true, get_string('upload'));
    }

    public function validation($data, $files) {
        $errors = parent::validation($data, $files);
        return $errors;
    }
}
