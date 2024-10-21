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
 * exams view page
 *
 * @package    local_exams
 * @copyright  2022 e abyas  <info@eabyas.com>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

require_once('../../config.php');

use local_exams\local\exams;

global $CFG, $PAGE, $OUTPUT, $DB;

require_login();

$context=context_system::instance();

$profileid = required_param('id', PARAM_INT);
// $examsinfo=$DB->get_record('local_exams', ['id' => $examid], 'id,exam,examnamearabic,noofquestions,courseid,quizid');

$sql = "SELECT lep.id, lep.questions as noofquestions, lep.quizid, le.courseid, le.exam, le.examnamearabic
        FROM {local_exam_profiles} lep 
        JOIN {local_exams} le ON lep.examid = le.id
        WHERE lep.id =". $profileid;
$examsinfo = $DB->get_record_sql($sql);
$lang= current_language();
if( $lang == 'ar'){
    $examsinfo->examname=$examsinfo->examnamearabic;
}else{
    $examsinfo->examname=$examsinfo->exam;
}
$PAGE->set_context($systemcontext);
$PAGE->set_url('/local/exams/questionbank.php',array('id'=>$profileid));
$PAGE->set_pagelayout('standard');
$PAGE->set_context($context);
$PAGE->set_title(get_string('questionbank', 'local_exams',$examsinfo->examname));

if(!has_capability('local/organization:manage_examofficial', $context)){

    throw new required_capability_exception($context, 'local/exams:manage_examofficial', 'nopermissions', '');

}
$PAGE->set_heading(get_string('questionbank', 'local_exams',$examsinfo->examname));
$PAGE->requires->jquery_plugin('ui-css');
$PAGE->requires->css('/local/exams/css/jquery.dataTables.min.css');
$PAGE->navbar->add(get_string('exams', 'local_exams'), new moodle_url('/local/exams/index.php'));
$PAGE->navbar->add(get_string("mapquestions", 'local_exams'), new moodle_url('/local/exams/examdetails.php',array('id'=>$profileid)));
$renderer = $PAGE->get_renderer('local_exams');

echo $OUTPUT->header();
echo $renderer->get_questionbanks_exams($examsinfo);
echo $OUTPUT->footer();
