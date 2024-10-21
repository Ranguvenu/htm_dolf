<?php
/**
 * This file is part of eAbyas
 *
 * Copyright eAbyas Info Solutons Pvt Ltd, India
 *
 * This program is free software; you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation; either version 3 of the License, or
 * (at your option) any later version.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with this program.  If not, see <http://www.gnu.org/licenses/>.
 *
 * @author eabyas  <info@eabyas.in>
 * @package local_events
 * @subpackage local_events
 */
require_once('../../config.php');
global $CFG, $PAGE, $OUTPUT, $DB;
require_login();
$eventid = required_param('id', PARAM_INT);
$renderer = $PAGE->get_renderer('local_events');
//$renderer->manage_capability();
$event = $renderer->event_check($eventid);
$PAGE->set_url('/local/events/view.php?id='.$eventid);
$PAGE->set_context(context_system::instance());
//$PAGE->set_title(get_string('pluginname','local_events'));
//$PAGE->set_heading(get_string('eventdetails','local_events'));
//$PAGE->navbar->add(get_string('manage', 'local_events'), new moodle_url('/local/events/index.php'));
$capability = $renderer->hascapability();
$PAGE->navbar->add(get_string('eventdetails', 'local_events'), new moodle_url('/local/events/view.php?id='.$eventid));
$content = $renderer->get_eventContent($eventid);
echo $OUTPUT->header();
echo $content;
echo $OUTPUT->footer();
