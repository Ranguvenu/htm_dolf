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
use question_bank;
use qbank_previewquestion\question_preview_options;
require_once(__DIR__ . '/../../config.php');
require_once($CFG->libdir.'/formslib.php');
require_once($CFG->dirroot.'/local/questionbank/lib.php');
require_once($CFG->dirroot.'/question/editlib.php');
global $CFG, $PAGE, $OUTPUT, $DB;
$workshopid      = optional_param('wid', -1, PARAM_INT);
$lastchanged      = optional_param('lastchanged', -1, PARAM_INT);
$PAGE->requires->jquery();
require_login();
$PAGE->set_url(new moodle_url('/local/questionbank/questionbank_view.php'));
$PAGE->set_context(context_system::instance());

$PAGE->set_title(get_string('questionbank', 'local_questionbank'));
$PAGE->navbar->add(get_string('questionbank', 'local_questionbank'), new moodle_url('/local/quesstionbank/questionbank_view.php'));
echo $OUTPUT->header();
$qcategory = $DB->get_record('local_questionbank',array('id'=>$workshopid));
// $qcid = explode(',',$qcategory->quesionbankid);
$courseid = explode(',',$qcategory->course);
$returnurl ='/local/questionbank/questionbank_view.php?wid='.$workshopid;
// if($lastchanged > 0){
//     $question = $DB->get_record('question', ['id' => $lastchanged]);
//     $qtypeobj = question_bank::get_qtype($question->qtype);
//     array_shift($qcid);
//     foreach($qcid as $key =>$value){
//       $qc = $DB->get_record('question_categories',array('id'=>$value));
//       if($qc){
//           // $thiscontext = context_course::instance($courseid);
//           // $contexts = new core_question\local\bank\question_edit_contexts($thiscontext);
//           $qtypeobj->get_question_options($question, true, [$courseid]);
//           $question->formoptions = new stdClass();

//           $categorycontext = context::instance_by_id($qc->contextid);
//           $question->contextid = $qc->contextid;
//           $addpermission = has_capability('moodle/question:add', $categorycontext);

//           if ($question) {
//               $question->formoptions->canedit = question_has_capability_on($question, 'edit');
//               $question->formoptions->canmove = $addpermission && question_has_capability_on($question, 'move');
//               $question->formoptions->cansaveasnew = $addpermission &&
//                       (question_has_capability_on($question, 'view') || $question->formoptions->canedit);
//               $question->formoptions->repeatelements = $question->formoptions->canedit || $question->formoptions->cansaveasnew;
//               $formeditable = $question->formoptions->canedit || $question->formoptions->cansaveasnew || $question->formoptions->canmove;
//               if (!$formeditable) {
//                   question_require_capability_on($question, 'view');
//               }
//              // $question->beingcopied = false;
//               //if ($makecopy) {
//                   // If we are duplicating a question, add some indication to the question name.
//               $question->name = $question->name;
//               $question->idnumber = isset($question->idnumber) ?
//                       core_question_find_next_unused_idnumber($question->idnumber, $category->id) : '';
//                   $question->beingcopied = true;
//               //}

//           } 
//           $qdata = fullclone($question);
//           $question =$qdata;
//           $question->category =$value;
//           $fromform = $question;
//           // $fromform->category =$value.','.$qc->contextid;
//           $questionobject = question_bank::load_question($question->id);
//           $fromform->status = $questionobject->status;
//           $question->makecopy = 1;
//           $question->answer = $question->options->answers;
//           // $trueanswer = $qdata->options->answers[$qdata->options->trueanswer];
//           // $question->correctanswer = ($trueanswer->fraction != 0);
//           $question->id = 0;

//            print_r($question);
//      echo '<br> ---------------------------------<br>';
//      // print_r($fromform);
//           //exit;
//           // print_r($question);
//           $question = (new local_questionbank\local\createquestion)->clone($question, $qc);
//       }
//     }
    
//     //redirect($CFG->wwwroot . '/local/questionbank/index.php');
// }elseif(!empty($qcategory->quesionbankid) && $workshopid){
    $question = question_bank::load_question($lastchanged);
    $maxvariant = min($question->get_num_variants(), QUESTION_PREVIEW_MAX_VARIANTS);
    $options = new question_preview_options($question);
    $options->load_user_defaults();
    $options->set_from_request();
    $quba = question_engine::make_questions_usage_by_activity(
            'core_question_preview', context_user::instance($USER->id));
    $quba->set_preferred_behaviour($options->behaviour);
    $slot = $quba->add_question($question, $options->maxmark);

    if ($options->variant) {
        $options->variant = min($maxvariant, max(1, $options->variant));
    } else {
        $options->variant = rand(1, $maxvariant);
    }

    $quba->start_question($slot, $options->variant);

    $transaction = $DB->start_delegated_transaction();
    question_engine::save_questions_usage_by_activity($quba);
    $transaction->allow_commit();
   $options->behaviour = $quba->get_preferred_behaviour();
   $options->maxmark = $quba->get_question_max_mark($slot);

    $res =  $quba->render_question($slot, $options, $displaynumber);
    // $renderer = $PAGE->get_renderer('local_questionbank');
    // $qdata  = $renderer->question($data);
    echo '<div class="createnewquestion">';
         $editquestionurl = new \moodle_url('/question/question.php',
                    array('returnurl' => $returnurl));
         $editquestionurl->param('courseid', 1);
        create_new_question_button($qcategory->quesionbankid, $editquestionurl->params(),
                        get_string('createnewquestion', 'question'));

    echo '</div>';
    echo $res;
// }


echo $OUTPUT->footer();