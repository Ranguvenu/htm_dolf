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
// MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
// GNU General Public License for more details.
//
// You should have received a copy of the GNU General Public License
// along with Moodle.  If not, see <http://www.gnu.org/licenses/>.

require_once(__DIR__ . '/../../config.php');
require_once($CFG->libdir.'/formslib.php');

global $CFG, $PAGE, $OUTPUT, $DB;
// $PAGE->requires->jquery();
$jobid = required_param('jobid', PARAM_INT);
require_login();
$systemcontext = context_system::instance();
require_capability('local/sector:manage', $systemcontext);
$PAGE->set_url(new moodle_url('/local/sector/jobfamily.php'), array('jobid' => $jobid));
$PAGE->set_context(context_system::instance());

$PAGE->navbar->add(get_string('sector', 'local_sector'), new moodle_url('/local/sector/index.php') );
$PAGE->navbar->add(get_string('jobfamily', 'local_sector'), $PAGE->url);
$jobfamily = $DB->get_record_select('local_jobfamily', 'id=:jobid',['jobid' =>$jobid], 'familyname,familynamearabic,description');

$lang=current_language();
if($lang=='ar'){
    $jobfamily->family=$jobfamily->familynamearabic;
}
else{
     $jobfamily->family=$jobfamily->familyname;
}
$PAGE->set_title(get_string('jobfamily','local_sector') . ' - '. $jobfamily->family);
$PAGE->set_heading(get_string('jobfamily','local_sector') . ' - '. $jobfamily->family);

echo $OUTPUT->header();
$PAGE->requires->js_call_amd('local_sector/jobrole_level', 'init');
$sectorrender= $PAGE->get_renderer('local_sector');
$filterparams = $sectorrender->get_jobrole_level_view(true,$jobid);
$data=[
    'jobid'=>$jobid,
];
echo $OUTPUT->box(format_text($jobfamily->description,FORMAT_HTML));
echo $OUTPUT->render_from_template('local_sector/create_jobrole',$data );
echo $OUTPUT->render_from_template('theme_academy/global_filter', $filterparams);
echo $sectorrender->get_jobrole_level_view(false,$jobid);
echo $OUTPUT->footer();



