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
// namespace local_questionbank;
// use moodle_url;
// use context_system;
require_once(__DIR__ . '/../../config.php');
require_once($CFG->libdir.'/formslib.php');
require_once($CFG->dirroot.'/local/questionbank/lib.php');
require_once($CFG->dirroot.'/question/editlib.php');
global $CFG, $PAGE, $OUTPUT, $DB;
$PAGE->requires->jquery();
$cancelled  = optional_param('c', -1, PARAM_INT);
$PAGE->set_url(new moodle_url('/local/questionbank/index.php'));
$PAGE->set_context(context_system::instance());
require_login();

$PAGE->set_title(get_string('questionbank', 'local_questionbank'));
$PAGE->set_heading(get_string('questionbank', 'local_questionbank'));
$PAGE->navbar->add(get_string('questionbank', 'local_questionbank'), new moodle_url('/local/quesstionbank/index.php'));
echo $OUTPUT->header();
$systemcontext = context_system::instance();
if(!(is_siteadmin() || has_capability('local/questionbank:assignreviewer',$systemcontext) || has_capability('local/organization:manage_organizationofficial',$systemcontext) || has_capability('local/questionbank:createquestion',$systemcontext))){
    print_error("You don't have permissions to view this page.");
}
//if($cancelled > 0){
    $empquestionbank =  $DB->get_fieldset_sql("SELECT id FROM {local_questionbank} WHERE qcategoryid IS NULL");
    if(!empty($empquestionbank)){
        $questionbank = implode(",",$empquestionbank);
        $DB->execute("DELETE FROM {local_questionbank} WHERE id IN (".$questionbank .")");
    }

//}


if(is_siteadmin() || has_capability('local/questionbank:assignreviewer',$systemcontext)){
$prodqb = $CFG->wwwroot.'/question/bank/managecategories/category.php?courseid=1';
echo $OUTPUT->render_from_template('local_questionbank/createquestionbankbutton', ['createquestionbank'=>true,'prodqb'=>$prodqb]);
}
$questionbank = new local_questionbank\form\questionbank_form();
if($questionbank->is_cancelled()){
    redirect($CFG->wwwroot . '/local/questionbank/index.php');
}
$renderer= $PAGE->get_renderer('local_questionbank');
$renderable = new \local_questionbank\output\questionbank();
echo $renderer->render($renderable);
$PAGE->requires->js_call_amd('local_questionbank/assignexperts', 'removeexpert');
echo $OUTPUT->footer();