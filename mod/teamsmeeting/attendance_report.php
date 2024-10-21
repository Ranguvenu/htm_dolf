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
require_once("$CFG->dirroot/mod/teamsmeeting/classes/graph-teams-client.php");
require_once("$CFG->dirroot/mod/teamsmeeting/lib.php");

$id = required_param('id', PARAM_INT); // Course module ID.
$cm = get_coursemodule_from_id('teamsmeeting', $id, 0, false, MUST_EXIST);

$pageurl = new moodle_url('/mod/teamsmeeting/attendance_report.php', array('id' => $id));
$PAGE->set_url($pageurl);

echo $OUTPUT->header();
sync_attendance_report($cm->instance);
echo $OUTPUT->footer();
