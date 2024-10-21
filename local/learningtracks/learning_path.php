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
 * learningtracks index page
 *
 * @package    local_learningtracks
 * @copyright  2022 e abyas  <info@eabyas.com>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

require_once('../../config.php');

global $CFG, $PAGE, $OUTPUT, $DB;

$PAGE->set_url('/local/learningtracks/learning_path.php');
$PAGE->set_context(context_system::instance());
$PAGE->requires->js_call_amd('local_learningtracks/learningtracksform', 'init');
$PAGE->set_title(get_string('pluginname', 'local_learningtracks'));
$PAGE->set_heading(get_string('pluginname', 'local_learningtracks'));
$PAGE->navbar->add(get_string("pluginname", 'local_learningtracks'), new moodle_url('/local/learningtracks/index.php'));
$renderer = $PAGE->get_renderer('local_learningtracks');
echo $OUTPUT->header();
echo $renderer->get_learningpath();
echo $OUTPUT->footer(); 