<?php
// This file is part of Moodle - http://moodle.org/
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
 * @package    local_userapproval
 * @copyright  2022 eAbyas Info Solutions<info@eabyas.com>
 * @author     Vinod Kumar  <vinod.p@eabyas.com>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
require_once('../../config.php');
require_login();
global $CFG, $PAGE, $OUTPUT, $DB;
$systemcontext = context_system::instance();
$PAGE->set_context($systemcontext);
require_capability('local/userapproval:visibility', $systemcontext);
$PAGE->requires->js_call_amd('local_userapproval/dynamicForm', 'init');
$PAGE->set_url('/local/userapproval/index.php');
$PAGE->set_title(get_string('myorgpendingrequests', 'local_userapproval'));
$PAGE->set_heading(get_string('myorgpendingrequests', 'local_userapproval'));
$renderer = $PAGE->get_renderer('local_userapproval');
echo $OUTPUT->header();
 (new local_userapproval\action\manageuser)->myorgpendingrequestsinfo();
echo $OUTPUT->footer();
