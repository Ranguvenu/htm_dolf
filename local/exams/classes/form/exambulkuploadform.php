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
//use local_kpi\local\kpiupload as kpiupload;
/**
 * kpi upload form class
 *
 * @package    local_exams
 * @copyright  2022 Revanth Kumar Grandhi
 */
class exambulkuploadform extends moodleform {
	public function definition() {
		$mform = $this->_form;

	        $filemanageroptions = array(
	                'maxbytes'=>10240,
	                'accepted_types'=>'csv',
	                'maxfiles'=>1
                );
        
		$mform->addElement('filepicker', 'userfile', get_string('file'), null, $filemanageroptions);
		$mform->addHelpButton('userfile', 'uploaddec', 'local_exams');
		$mform->addRule('userfile', null, 'required');

		$this->add_action_buttons(true, get_string('upload'));
	}
}
