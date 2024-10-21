<?php
namespace local_exams\form;
use core;
use moodleform;
use context_system;
use DatePeriod;
use DateTime;
use html_writer;

require_once($CFG->dirroot . '/lib/formslib.php');
class listofhallsform extends moodleform {
	function definition() {
		global $CFG, $DB, $OUTPUT;
		$mform = & $this->_form;
        $typeid = & $this->_customdata['typeid'];

        $mform->addElement('hidden', 'typeid', '', ['class' => 'typeid']);
        $mform->setType('typeid',PARAM_INT);

        $halls = $DB->get_records_sql_menu("SELECT id, name FROM {hall}");
		$sectorelement =$mform->addElement('autocomplete','halls', get_string('selecthalls', 'local_exams'),$halls, ['class' => 'el_halllist']);

        $examdates = $DB->get_record_sql("SELECT examdatetime, enddate FROM {local_exams} WHERE id = ".$typeid);
        $period = new DatePeriod(
             new DateTime(date( 'Y-m-d', $examdates->examdatetime)),
             new \DateInterval('P1D'),
             new DateTime(date( 'Y-m-d', $examdates->enddate))
        );
        $exams = [];
        foreach ($period as $value) {
        	$key = strtotime($value->format('Y-m-d'));
            $examslist[$key] =  $value->format('Y-m-d');
        }
        $mform->addElement('select', 'moduledates',get_string('examdates','local_exams'), array(null=>get_string('selectdate','local_exams')) + $examslist, ['class' => 'halldate']);
        $mform->addRule('moduledates', get_string('missingexamdates', 'local_exams'), 'required', null);

		$mform->addElement('button', 'hallbtn', get_string("apply", "local_exams"), ['class' => 'hallbtn', 'data-action' => 'hallbtn']);
	}

	function validation($data, $files) {
		$errors = parent :: validation($data, $files);
		return $errors;
	}
}
