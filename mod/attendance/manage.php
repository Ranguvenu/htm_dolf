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
 * Manage attendance sessions
 *
 * @package    mod_attendance
 * @copyright  2011 Artem Andreev <andreev.artem@gmail.com>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

require_once(dirname(__FILE__).'/../../config.php');
require_once(dirname(__FILE__).'/locallib.php');

$pageparams = new mod_attendance_manage_page_params();

$id                         = required_param('id', PARAM_INT);
$from                       = optional_param('from', null, PARAM_ALPHANUMEXT);
//vinod - Starts//
//$pageparams->view           = optional_param('view', null, PARAM_INT);
$pageparams->view           = optional_param('view', 5, PARAM_INT);
//vinod - Ends//
$pageparams->curdate        = optional_param('curdate', null, PARAM_INT);
$pageparams->perpage        = get_config('attendance', 'resultsperpage');

$cm             = get_coursemodule_from_id('attendance', $id, 0, false, MUST_EXIST);
$course         = $DB->get_record('course', array('id' => $cm->course), '*', MUST_EXIST);
$att            = $DB->get_record('attendance', array('id' => $cm->instance), '*', MUST_EXIST);

require_login($course, true, $cm);

$context = context_module::instance($cm->id);
$capabilities = array(
    'mod/attendance:manageattendances',
    'mod/attendance:takeattendances',
    'mod/attendance:changeattendances'
);
if (!has_any_capability($capabilities, $context)) {
    $url = new moodle_url('/mod/attendance/view.php', array('id' => $cm->id));
    redirect($url);
}

$pageparams->init($cm);
$att = new mod_attendance_structure($att, $cm, $course, $context, $pageparams);

// If teacher is coming from block, then check for a session exists for today.
if ($from === 'block') {
    $sessions = $att->get_today_sessions();
    $size = count($sessions);
    if ($size == 1) {
        $sess = reset($sessions);
        $nottaken = !$sess->lasttaken && has_capability('mod/attendance:takeattendances', $context);
        $canchange = $sess->lasttaken && has_capability('mod/attendance:changeattendances', $context);
        if ($nottaken || $canchange) {
            redirect($att->url_take(array('sessionid' => $sess->id, 'grouptype' => $sess->groupid)));
        }
    } else if ($size > 1) {
        $att->curdate = $today;
        // Temporarily set $view for single access to page from block.
        $att->view = ATT_VIEW_DAYS;
    }
}

$PAGE->set_url($att->url_manage());
$PAGE->set_title($course->shortname. ": ".$att->name);
$PAGE->set_heading($course->fullname);
$PAGE->set_cacheable(true);
$PAGE->force_settings_menu(true);
$PAGE->navbar->add(format_text($att->name,FORMAT_HTML));

$output = $PAGE->get_renderer('mod_attendance');
$tabs = new attendance_tabs($att, attendance_tabs::TAB_SESSIONS);
$filtercontrols = new attendance_filter_controls($att);
$sesstable = new attendance_manage_data($att);
$current_lanuage = current_language();
$coursefullnameinarabic = $DB->get_field_sql('SELECT namearabic FROM {local_trainingprogram}  WHERE courseid = '.$course->id.'');
if($current_lanuage == 'ar' && !empty($coursefullnameinarabic) && !is_null($coursefullnameinarabic)) {

   $displayfullname = $coursefullnameinarabic;
    
} else {

    $displayfullname = format_string($course->fullname);
}

$title = get_string('attendanceforthecourse', 'attendance').' :: ' .$displayfullname;
$header = new mod_attendance_header($att, $title);

// Output starts here.

echo $output->header();
echo $output->render($header);
mod_attendance_notifyqueue::show();

// Vinod - Hiding tabs  iltercontrols - Starts// 
echo $output->render($tabs);
// echo $output->render($filtercontrols);
// Vinod - Hiding tabs  iltercontrols - Ends// 
echo $output->render($sesstable);

echo $output->footer();

