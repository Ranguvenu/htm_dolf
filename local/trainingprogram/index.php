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
 * Defines the version of Training program
 *
 * @package    local_trainingprogram
 * @copyright  2022 Naveen kumar <naveen@eabyas.com>
 */
require_once('../../config.php');
require_once("$CFG->dirroot/mod/webexactivity/lib.php");

global $CFG, $PAGE, $OUTPUT, $DB;
$PAGE->set_url('/local/trainingprogram/index.php');
$systemcontext = context_system::instance();
$PAGE->set_context($systemcontext);
$PAGE->requires->js('/blocks/learnerscript/amd/src/report.js',true);
// $PAGE->requires->js_call_amd('local_exams/exams', 'init');
// require_capability('local/trainingprogram:view',$systemcontext);
if(!is_siteadmin() && !has_capability('local/organization:manage_trainingofficial',$systemcontext)  && !has_capability('local/organization:training_supervisor', $systemcontext) && !has_capability('local/organization:manage_trainee',$systemcontext) && !has_capability('local/organization:manage_trainer',$systemcontext) && !has_capability('local/organization:manage_organizationofficial',$systemcontext)  && !has_capability('local/organization:manage_financial_manager',$systemcontext) && !has_capability('local/trainingprogram:view',$systemcontext) &&  !has_capability('local/organization:manage_communication_officer',$systemcontext)) {

    redirect($CFG->wwwroot);

}
require_login();
$PAGE->set_title(get_string('pluginname','local_trainingprogram'));
if(!is_siteadmin() &&(has_capability('local/organization:manage_trainee',$systemcontext) 
    || has_capability('local/organization:manage_trainer',$systemcontext))) {
    $PAGE->set_heading(get_string('mytrainings','local_trainingprogram'));
} else {
    $PAGE->set_heading(get_string('trainings','local_trainingprogram'));
}
$PAGE->requires->js_call_amd('local_hall/hall', 'init');
$PAGE->requires->js_call_amd('local_hall/hallevents', 'init');
// $PAGE->requires->js_call_amd('local_trainingprogram/schedule', 'init');
$PAGE->requires->js_call_amd('local_exams/cancelreschedule', 'init');
$PAGE->requires->js_call_amd('local_trainingprogram/entityaction', 'init');
$PAGE->requires->js_call_amd('local_exams/fav', 'init');
$PAGE->requires->js_call_amd($CFG->wwwroot.'block_learnerscript/report', 'init');

$PAGE->requires->jquery_plugin('ui-css');

//Webex New Refresh Token
$code = optional_param('code', false, PARAM_RAW);
if($code){
    $refresh = tokens($code);
    $objdata = new stdClass();
    $objdata->value = $refresh;
    $objdata->name = 'webexrefreshtoken';
    if(!($DB->record_exists('config', ['name' => 'webexrefreshtoken']))){
        $DB->insert_record('config', $objdata);
    }else{
        $objdata->id =$DB->get_field('config', 'id', ['name' => 'webexrefreshtoken']);
        $DB->update_record('config', $objdata);
    }
}
echo $OUTPUT->header();

echo "<script src='https://learnerscript.com/wp-content/plugins/learnerscript/js/highcharts.js'></script>";
$renderer = $PAGE->get_renderer('local_trainingprogram');
$renderable = new \local_trainingprogram\output\trainingprogram();
echo $renderer->render($renderable);
echo $OUTPUT->footer();
