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
 * @package   local_exams
 * @author    Ikram Ahmad  {ikram.ahmad@moodle.com}
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

require_once('../../config.php');

$examid = required_param('id', PARAM_INT);
$context = context_system::instance();
$PAGE->set_context($context);
$PAGE->set_url('/local/exams/viewschedules.php');
$title = get_string('exam_schedules', 'local_exams');
$PAGE->set_title($title);
$PAGE->set_heading($title);
$PAGE->navbar->add(get_string('exams', 'local_exams'), new moodle_url('/local/exams/index.php'));
$PAGE->navbar->add($title, new moodle_url('/local/exams/viewschedules.php', ['id' => $examid]));

require_login();

$renderer = $PAGE->get_renderer('local_exams');
// $renderer->action_btn($examid);

echo $OUTPUT->header();

if(has_capability('local/exams:veiw_exam_reservations',$context)) {
    (new local_exams\local\exams)->get_exam_schedules($examid);
} else {
    throw new required_capability_exception($context, 'local/exams:veiw_exam_reservations', 'nopermissions', '');
}
$PAGE->requires->js_call_amd('local_exams/examschedules', 'init');
echo $OUTPUT->footer();
