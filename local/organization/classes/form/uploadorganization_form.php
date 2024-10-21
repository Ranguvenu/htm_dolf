<?php
/**
 * A form for organization upload.
 *
 * @package local_organization
 * 
 */
namespace local_organization\form;
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
class uploadorganization_form extends moodleform {

    /**
     * Form definition
     */
    public function definition() {
        $mform = $this->_form;
        $data  = (object)$this->_customdata;

        $mform->addElement('hidden', 'returnurl');
        $mform->setType('returnurl', PARAM_URL);

        $mform->addElement('header', 'orguploadform', get_string('uploadorg', 'local_organization'));

        $filepickeroptions = array(
            'accepted_types' => array(get_string('csv', 'local_organization')),
            'maxbytes' => 0,
            'maxfiles' => 1,
        );
        $mform->addElement('filepicker', 'organizationfile', get_string('file'), null, $filepickeroptions);
        $mform->addRule('organizationfile', null, 'required');
        $mform->addHelpButton('organizationfile', 'uploaddoc', 'local_organization');

        $choices = csv_import_reader::get_delimiter_list();
        $mform->addElement('hidden', 'delimiter_name', get_string('csvdelimiter', 'local_exams'), $choices);
		if (array_key_exists('cfg', $choices)) {
			$mform->setDefault('delimiter_name', 'cfg');
		} else if (get_string('listsep', 'langconfig') == ';') {
			$mform->setDefault('delimiter_name', 'semicolon');
		} else {
			$mform->setDefault('delimiter_name', 'comma');
		}
		$mform->setType('delimiter_name', PARAM_RAW);

		$choices = core_text::get_encodings();
		$mform->addElement('hidden', 'encoding', get_string('encoding', 'local_exams'), $choices);
		$mform->setDefault('encoding', 'UTF-8');
		$mform->setType('encoding', PARAM_RAW);

		$choices = array('10' => 10, '20' => 20, '100' => 100, '1000' => 1000, '100000' => 100000);
		$mform->addElement('hidden', 'previewrows', get_string('rowpreviewnum', 'local_exams'), $choices);
		$mform->setType('previewrows', PARAM_INT);

        $this->add_action_buttons(false);
    }
}
