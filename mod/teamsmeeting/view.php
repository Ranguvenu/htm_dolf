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
 * Teams Meeting Content view
 *
 * @package    mod_teamsmeeting
 * @copyright  2022 eAbyas Info Solutions Pvt Ltd (www.eabyas.com)
 * @author     Ranga Reddy<rangareddy@eabyas.com>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

require('../../config.php');
require_login();
require_once($CFG->libdir.'/completionlib.php');
$id = optional_param('id', 0, PARAM_INT); // Course module ID.
$error = false;
$cm = get_coursemodule_from_id('teamsmeeting', $id, 0, false, MUST_EXIST);
$teamsrecord = $DB->get_record('teamsmeeting', array('id' => $cm->instance), '*', MUST_EXIST);
$course = $DB->get_record('course', array('id' => $cm->course), '*', MUST_EXIST);

// Basic completion tracking.
$completion = new completion_info($course);
$completion->set_module_viewed($cm);

require_course_login($course, true, $cm);
$context = context_module::instance($cm->id);
require_capability('mod/teamsmeeting:view', $context);

$returnurl = new moodle_url('/mod/teamsmeeting/view.php', array('id' => $id));
$PAGE->set_url($returnurl);

// Basic page setup.
$PAGE->set_title($course->shortname.': '.$teamsrecord->name);
$PAGE->set_heading($course->fullname);
$PAGE->set_activity_record($teamsrecord);

// Start output.
echo $OUTPUT->header();
echo $OUTPUT->heading(format_string($teamsrecord->name), 2);
echo $OUTPUT->box_start();
    $table = new html_table();
    $table->attributes['class'] = 'generaltable';
    $table->align = array('center', 'left');
    $table->size = array('35%', '65%');
    $numcolumns = 2;

    // $table->data[] = array(get_string('description', 'mod_teamsmeeting'),format_text($teamsrecord->intro,FORMAT_HTML));
    $table->data[] = array(get_string('starttime', 'mod_teamsmeeting'),userdate($teamsrecord->start_time)) ;
    $table->data[] = ($teamsrecord->isrecuring == 1) ? array(get_string('endtime', 'mod_teamsmeeting'),userdate($teamsrecord->end_time)) : array(get_string('endtime', 'mod_teamsmeeting'),userdate($teamsrecord->start_time + $teamsrecord->duration)) ;
    $table->data[] = array(get_string('duration', 'mod_teamsmeeting'), $teamsrecord->duration ? format_time($teamsrecord->duration) : null);
    $lanuchmeetingbutton = html_writer::div(get_string('launch_metting', 'teamsmeeting'),'btn btn-primary');
    $lanuchmeetingurl = html_writer::link((string) $teamsrecord->meetingurl,$lanuchmeetingbutton, array('target' => '_blank'));
    $table->data[] = array(get_string('joinlink', 'teamsmeeting'), $lanuchmeetingurl);
    
    echo html_writer::table($table);
echo $OUTPUT->box_end();

echo $OUTPUT->footer();
