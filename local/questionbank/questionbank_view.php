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
 *
 * @package    local_questionbank
 * @copyright  Moodle India
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

use qbank_previewquestion\question_preview_options;
use qbank_editquestion\editquestion_helper;
use local_questionbank\local\view;

require_once(__DIR__ . '/../../config.php');
require_once($CFG->libdir.'/formslib.php');
require_once($CFG->dirroot.'/local/questionbank/lib.php');
require_once($CFG->dirroot.'/question/editlib.php');


$PAGE->add_body_class('questioncreation_page');
$workshopid      = optional_param('wid', -1, PARAM_INT);
$lastchanged      = optional_param('lastchanged', -1, PARAM_INT);
$courseid      = optional_param('courseid', 1, PARAM_INT);
$cat      = optional_param('cat', -1, PARAM_RAW);
$qperpage      = optional_param('qperpage', 5, PARAM_INT);

list($thispageurl, $contexts, $cmid, $cm, $module, $pagevars) =
        question_edit_setup('questions', '/local/questionbank/questionbank_view.php');

$PAGE->set_url(new moodle_url('/local/questionbank/questionbank_view.php?wid='.$workshopid.'&courseid=1&cat='.$cat));
$PAGE->set_context(context_system::instance());
$PAGE->set_pagelayout('standard');
$title = get_string('viewquestions', 'local_questionbank');
$PAGE->set_title($title);
$PAGE->set_heading($title);
$PAGE->navbar->add(get_string('questionbank', 'local_questionbank'), new moodle_url('/local/quesstionbank/index.php'));

require_login();

echo $OUTPUT->header();
$PAGE->requires->jquery();
$PAGE->requires->js_call_amd('local_questionbank/questionBank', 'init', array());
$systemcontext = context_system::instance();
if(!(is_siteadmin() || has_capability('local/questionbank:assignreviewer',$systemcontext) || has_capability('local/organization:manage_organizationofficial',$systemcontext) || has_capability('local/questionbank:createquestion',$systemcontext))){
    print_error("You don't have permissions to view this page.");
}
if($lastchanged >0){
  $qversion =  $DB->get_field('question_versions','id',array('questionid'=>$lastchanged));
  $qreview =  $DB->get_field('local_qb_questionreview','qstatus',array('questionid'=>$lastchanged));
  if($qversion && $qreview != 'publish'){
      $DB->update_record('question_versions',array('id'=> $qversion,'questionid'=>$lastchanged ,'status'=>'draft'));
  }
   //notification
  $category = explode(',',$cat);
  $questionbankname = $DB->get_record('local_questionbank', array('qcategoryid'=>$category[0]));
  $PAGE->navbar->add($questionbankname, new moodle_url('/local/quesstionbank/questionbank_view.php'));
  $questiontext = $DB->get_record('question', array('id'=>$lastchanged));

 $row=[];

 $row['QuestionText']=$questiontext->questiontext;
 $row['QuestionBank']=$questionbankname->workshopname;
 $emailtype='questionbank_question_added';
 $myobject=(new \local_questionbank\notification);
 $myobject->questionbank_notification($emailtype,$touser=null, $USER,$row,$waitinglistid=0);

}

$questionbank = new local_questionbank\local\view($contexts, $thispageurl, $COURSE, $cm);
$questionbank->enablefilters =false;

$pagevars['qperpage'] = $qperpage;
$questionbank->display($pagevars, 'questions');

echo $OUTPUT->footer();
