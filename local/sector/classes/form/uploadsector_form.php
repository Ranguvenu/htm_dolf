<?php
/**
 * A form for sector upload.
 *
 * @package    local_sector
 * 
 */
namespace local_sector\form;
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
class uploadsector_form extends moodleform {

    /**
     * Form definition
     */
    public function definition() {
        $mform = $this->_form;
        $data  = (object)$this->_customdata;

        $mform->addElement('hidden', 'returnurl');
        $mform->setType('returnurl', PARAM_URL);

        $mform->addElement('header', 'sectoruploadform', get_string('uploadafile', 'local_sector'));

        $filepickeroptions = array(
                    'accepted_types' => array(get_string('csv', 'local_trainingprogram')),
                    'maxbytes' => 0,
                    'maxfiles' => 1,
        );
        $mform->addElement('filepicker', 'sectorfile', get_string('file'), null, $filepickeroptions);
        $mform->addRule('sectorfile', get_string('sectorfilerequired','local_sector'), 'required', null);
        $mform->addHelpButton('sectorfile', 'uploaddoc', 'local_sector');

        $this->add_action_buttons(false);

    }

    public function validation($data, $files) {
        $errors = parent::validation($data, $files);
        return $errors;
    }
}
