<?php
// This file is part of Moodle - http://moodle.org/
//
// Moodle is free software: you can redistribute it and/or modify
// it under the terms of the GNU General Public License as published by
// the Free Software Foundation, either version 3 of the License, or
// (at your option) any later version.
//
// Moodle is distributed in the hope that it will be useful,
// but WITHOUT ANY WARRANTY; without even the implied warranty of
// MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
// GNU General Public License for more details.
//
// You should have received a copy of the GNU General Public License
// along with Moodle.  If not, see <http://www.gnu.org/licenses/>.

/**
 * Bulk user schedule script from a comma separated file
 *
 * @package    local
 * @subpackage Exams
 * @copyright  eAbyas Info Solutions
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
require_once('../../config.php');
require_once($CFG->libdir . '/adminlib.php');
require_once($CFG->libdir . '/csvlib.class.php');

$iid = optional_param('iid', 0, PARAM_INT);
$orgid = optional_param('id', 0, PARAM_INT);
$previewrows = optional_param('previewrows', 10, PARAM_INT);

@set_time_limit(60 * 60); // 1 hour should be enough.
raise_memory_limit(MEMORY_HUGE);

$errorstr = get_string('error');
$stryes = get_string('yes');
$strno = get_string('no');
$stryesnooptions = array(0 => $strno, 1 => $stryes);

global $USER, $DB, $PAGE, $OUTPUT;
$systemcontext = context_system::instance();
$PAGE->set_context($systemcontext);
$PAGE->set_url('/local/exams/bulkupload.php');
$strheading = get_string('pluginname', 'local_exams') . ' : ' . get_string('uploadexams', 'local_exams');
$PAGE->set_title($strheading);
$PAGE->requires->jquery_plugin('ui-css');
require_login();
if(!has_capability('local/organization:manage_examofficial', $systemcontext) && !is_siteadmin() && !has_capability('local/exams:view', $systemcontext)) {
	throw new required_capability_exception($systemcontext, 'local/exams:view', 'nopermissions', '');
}

$PAGE->navbar->add(get_string('exams','local_exams'),new moodle_url('/local/exams/index.php'));
$PAGE->navbar->add(get_string('bulkupload', 'local_exams'));
$returnurl = new moodle_url('/local/exams/index.php');
$stdfields = array('old_id', 'exam', 'examnamearabic', 'code','examprice','sellingprice','actualprice','description','targetaudience','sectors', 'taxfree', 'jobfamilies', 'clevels','ctype','competencies','competencyweights', 'preparationprograms','requirements', 'additionalrequirements', 'type','certificatevalidity','ownedby', 'attachedmsg',	'noofattempts',	'appliedon');
$prffields = array();

$mform = new local_exams\form\exambulkuploadform($CFG->wwwroot . '/local/exams/bulkupload.php');
if ($mform->is_cancelled()) {
	redirect($returnurl);
} elseif ($formdata = $mform->get_data()) {
	echo $OUTPUT->header();
	$iid = csv_import_reader::get_new_iid('uploadexams');
	$cir = new csv_import_reader($iid, 'uploadexams');
	$content = $mform->get_file_content('userfile');
	$readcount = $cir->load_csv_content($content,  'utf-8', ',');

	unset($content);
	if ($readcount === false) {
		print_error('csvloaderror', '', $returnurl);
	} else if ($readcount == 0) {
		print_error('csvemptyfile', 'error', $returnurl);
	}
	// Test if columns ok(to validate the csv file content).
	$linenum = 1;
	$subline = 1;
	$errorscount = 0;
	$mfieldscount = 0;
	$successcreatedcount = 0;
	$filecolumns = (new local_exams\local\exams)->uu_validate_user_upload_columns($cir, $stdfields, $prffields, $returnurl);
	$cir->init();
	loop:
	while ($line = $cir->next()) {
		$linenum++;
		$schedule_data = new stdClass();
		foreach ($line as $keynum => $value) {
			if (!isset($filecolumns[$keynum])) {
				// This should not happen.
				continue;
			}
			$k = $filecolumns[$keynum];
			$key = array_search($k, $stdfields);
			$schedule_data->$k = $value;
		}
		// Add default values for remaining fields.
		$formdefaults = array();
		foreach ($stdfields as $field) {
			if (isset($schedule_data->$field)) {
				continue;
			}
			// All validation moved to form2.
			if (isset($formdata->$field)) {
				// Process templates.
				$formdefaults[$field] = true;
			}
		}
		foreach ($prffields as $field) {
			if (isset($schedule_data->$field)) {
				continue;
			}
			if (isset($formdata->$field)) {
				// Process templates.
				$formdefaults[$field] = true;
			}
		}
		$validations = (new local_exams\local\exams)->formatdata_validation($orgid, $schedule_data, $linenum, $formatteddata);
		$createdcount = (new local_exams\local\exams)->add_bulkuploadexams($validations, $schedule_data);
		if($createdcount == 1) {
			$successcreatedcount++;
		}
	}
	$cir->cleanup(true);
	echo $OUTPUT->box_start('boxwidthnarrow boxaligncenter generalbox', 'uploadresults');
	echo '<div class="panel panel-primary">';
	if ($successcreatedcount > 0) {
		$success->count = $successcreatedcount;
		$success->linenum = $linenum - 1;
		echo get_string('recordsupdated', 'local_exams', $success);
	} else {
		echo get_string('zerorecordsupdated', 'local_exams');
	}
	if ($mfieldscount > 0) {
		echo '<div class="panel-body">' . get_string('uploaderrors', 'local_exams') . ': ' . $mfieldscount . '</div>';
	}
	echo '</div>';
	if ($mfieldscount > 0) {
		echo get_string('fillwithouterrors', 'local_exams');
	}

	echo $OUTPUT->box_end();
	echo '<div><a href="' . $CFG->wwwroot . '/local/exams/index.php"   role="button" class="btn btn-primary mr-2">Continue</a></div>' . '<br />';
	echo $OUTPUT->footer();
	die;
} else {
	echo $OUTPUT->header();

	echo $OUTPUT->heading(get_string('uploadexams', 'local_exams'));

	echo html_writer::tag('a',get_string('help','local_exams'),array('href'=>$CFG->wwwroot. '/local/exams/bulkupload_help.php','class'=>"btn btn-secondary ml-2 float-right"));

	echo html_writer::tag('a',get_string('sample_csv','local_exams'),array('href'=>$CFG->wwwroot. '/local/exams/sample.php', 'class'=>"btn btn-secondary float-right"));
	$mform->display();

	echo $OUTPUT->footer();
	die;
}
