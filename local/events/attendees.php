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
$systemcontext = context_system::instance();
$eventid = required_param('id', PARAM_INT);
$tuserids=optional_param('tuserids','', PARAM_RAW);
$event = $DB->get_record('local_events',['id'=>$eventid]);
require_login();
if (!empty($tuserids)) {
    $PAGE->requires->js_call_amd('local_trainingprogram/tpview', 'load', ['tuserids' => $tuserids, 'entityid'=>$eventid, 'referenceid'=> 0, 'type'=> 'event']);
}
$renderer = $PAGE->get_renderer('local_events');
if(!(is_siteadmin() || has_capability('local/events:manage', $systemcontext) || has_capability('local/organization:manage_event_manager', $systemcontext)
    || has_capability('local/organization:manage_organizationofficial',$systemcontext) || has_capability('local/organization:manage_communication_officer',$systemcontext)) ){
    print_error(get_string('permissionerror', 'local_events'));
}
if(!is_siteadmin() && $event->cancelled == 2) {
    throw new moodle_exception(get_string('eventhascancelled','local_events'));
}
//$renderer->manage_capability();
$event = $renderer->event_check($eventid);
$PAGE->set_url('/local/events/attendees.php', array('id' => $eventid));
$PAGE->set_context($systemcontext);
$PAGE->set_title(get_string('pluginname','local_events'));
$PAGE->set_heading(get_string('attendeelist','local_events', $event));
$PAGE->navbar->add(get_string("eventdetails", 'local_events'), new moodle_url("/local/events/view.php?id=".$eventid.""));
$PAGE->navbar->add(get_string('attendeelist', 'local_events', $event));
$PAGE->requires->js_call_amd('local_exams/replaceuser', 'init');
$PAGE->requires->js_call_amd('local_exams/cancelreschedule', 'init');
echo $OUTPUT->header();
(new local_events\events)->attendeesinfo();
echo $OUTPUT->footer();
