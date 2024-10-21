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
 * @package F-academy
 * @subpackage local_events
 */
require_once('../../config.php');
global $CFG, $PAGE, $OUTPUT, $DB;
$systemcontext = context_system::instance();

$eventid = required_param('id', PARAM_INT);
require_login();
$renderer = $PAGE->get_renderer('local_events');
$renderer->manage_capability();
$eventtitle = $DB->get_field('local_events', 'title', array('id' => $eventid));
$PAGE->set_url('/local/events/agendatopics.php', array('id' => $eventid));
$PAGE->set_context($systemcontext);
$PAGE->set_title(get_string('pluginname','local_events'));
$PAGE->set_heading(get_string('agendalist','local_events', $eventtitle));
$PAGE->navbar->add(get_string("eventdetails", 'local_events'), new moodle_url("/local/events/view.php?id=".$eventid.""));
$PAGE->navbar->add(get_string('agendalist', 'local_events', $eventtitle));

echo $OUTPUT->header();
(new local_events\events)->agendainfo($eventid);
echo $OUTPUT->footer();