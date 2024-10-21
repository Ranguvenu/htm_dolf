<?php
/**
 * A form for users upload.
 *
 * @package    local_userapproval
 * 
 */
namespace local_userapproval\form;
defined('MOODLE_INTERNAL') || die();

require_once($CFG->libdir.'/formslib.php');
require_once($CFG->libdir.'/csvlib.class.php');

use csv_import_reader;
use core_text;
use moodleform;
use context_user;
use html_table;
use html_writer;
//use local_kpi\local\kpiupload as kpiupload;
/**
 * kpi upload form class
 *
 * @package    local_kpi
 * @copyright  2018 Naveen
 */
class uploadusers_form extends moodleform {

    /**
     * Form definition
     */
    public function definition() {
        $mform = $this->_form;
        $data  = (object)$this->_customdata;

        $mform->addElement('hidden', 'returnurl');
        $mform->setType('returnurl', PARAM_URL);

        $mform->addElement('header', 'usersuploadform', get_string('uploadafile', 'local_userapproval'));

        $filepickeroptions = array();
        $filepickeroptions['filetypes'] = 'csv';
        $filepickeroptions['accepted_types'] = 'csv';
        $filepickeroptions['maxbytes'] = get_max_upload_file_size();
        $mform->addElement('filepicker', 'usersfile', get_string('file'), null, $filepickeroptions);
        $mform->addRule('usersfile', get_string('usersfilerequired','local_userapproval'), 'required', null);
          $mform->addHelpButton('usersfile', 'uploaddoc', 'local_userapproval');

        $this->add_action_buttons(false);
    }

    public function validation($data, $files) {
        $errors = parent::validation($data, $files);
        return $errors;
    }
}
