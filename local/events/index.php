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
require_login();
$systemcontext = context_system::instance();
$PAGE->set_url('/local/events/index.php');
$PAGE->set_context($systemcontext);
$PAGE->requires->js_call_amd('local_exams/exams', 'init');
$PAGE->requires->js_call_amd('local_exams/fav', 'init');
$PAGE->requires->js_call_amd('local_trainingprogram/entityaction', 'init');
if(is_siteadmin() || has_capability('local/organization:manage_event_manager', $systemcontext)) {
    $PAGE->set_title(get_string('pluginname','local_events'));
    $PAGE->set_heading(get_string('manage','local_events'));
    $PAGE->navbar->add(get_string('manage', 'local_events'));
} else if(!is_siteadmin() && has_capability('local/organization:manage_organizationofficial', $systemcontext)) {
    $PAGE->set_title(get_string('pluginname','local_events'));
    $PAGE->set_heading(get_string('pluginname','local_events'));
    $PAGE->navbar->add(get_string('pluginname', 'local_events'));
} else if(!is_siteadmin() && has_capability('local/organization:manage_trainee', $systemcontext)) {
    $PAGE->set_title(get_string('myevents','local_events'));
    $PAGE->set_heading(get_string('myevents','local_events'));
    $PAGE->navbar->add(get_string('myevents', 'local_events'));
}
$renderer = $PAGE->get_renderer('local_events');
echo $OUTPUT->header();
// (new local_events\events)->eventinfo();

if(has_capability('local/events:manage', $systemcontext) || is_siteadmin() || has_capability('local/organization:manage_event_manager', $systemcontext) || has_capability('local/organization:manage_organizationofficial', $systemcontext) 
|| has_capability('local/organization:manage_trainee', $systemcontext) || has_capability('local/organization:manage_communication_officer', $systemcontext) || has_capability('local/organization:manage_eventmanager',$systemcontext)) {
    $renderable = new \local_events\output\events();
    echo $renderer->render($renderable);    
}
echo $OUTPUT->footer();
