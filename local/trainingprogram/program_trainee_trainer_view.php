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
 * TODO describe file program_trainee_trainer_view
 *
 * @package    local_trainingprogram
 * @copyright  2023 YOUR NAME <your@email.com>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
require_once(dirname(__FILE__) . '/../../config.php');
global $DB, $CFG, $OUTPUT,$USER, $PAGE;
$PAGE->set_url('/local/trainingprogram/program_trainee_trainer_view.php');
$systemcontext = context_system::instance();
require_capability('local/trainingprogram:view',$systemcontext);
$PAGE->set_context($systemcontext);
$programid= optional_param('programid',0,PARAM_INT);
$offeringid= optional_param('offeringid',0,PARAM_INT);
$offeringcode = $DB->get_field('tp_offerings','code',['id'=>$offeringid]);
$lang = current_language();
if($lang == 'ar'){
    $programname = $DB->get_field('local_trainingprogram','namearabic',array('id'=>$programid));
}
else{
    $programname = $DB->get_field('local_trainingprogram','name',array('id'=>$programid));
}
// $PAGE->requires->js_call_amd('local_trainingprogram/assigneditingtrainer', 'init');
$title = get_string('assign_traineeortrainers', 'local_trainingprogram');
$courseid=$DB->get_field('local_trainingprogram','courseid',array('id'=>$programid));

$PAGE->set_title($title .': '.$programname);

$PAGE->set_heading($programname.' - '.$offeringcode);

$PAGE->set_url('/local/trainingprogram/program_trainee_trainer_view.php', array('programid' =>$programid,'offeringid'=>$offeringid));
$PAGE->navbar->add(get_string('manage_programs','local_trainingprogram'),new moodle_url('/local/trainingprogram/index.php'));
$PAGE->navbar->add($programname);
$PAGE->navbar->add($title, new moodle_url('/local/trainingprogram/program_trainee_trainer_view.php?programid='.$programid.'&offeringid='.$offeringid));

$returnurl = new moodle_url('/local/trainingprogram/program_trainee_trainer_view.php', array('programid' =>$programid));
echo $OUTPUT->header();
$offering = local_trainingprogram\local\dataprovider::get_offering($offeringid);

$tpoffering = local_trainingprogram\local\trainingprogram::get_offering($offering, true, false);


echo '<div id = "programdetailscontainer">';
echo $OUTPUT->render_from_template('local_trainingprogram/offering', $tpoffering);
echo '</div>';
(new local_trainingprogram\local\trainingprogram)->get_listof_traineeusers($programid,$offeringid);
echo $OUTPUT->footer();
