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
 * @subpackage local_cpd
 */
require_once(dirname(__FILE__) . '/../../config.php');
global $CFG;
$systemcontext = context_system::instance();
require_login();
$renderer = $PAGE->get_renderer('local_events');
if(!(is_siteadmin() || has_capability('local/events:edit', $systemcontext) || has_capability('local/events:manage', $systemcontext)  
|| has_capability('local/organization:manage_event_manager', $systemcontext) )){
print_error("You don't have permissions to view this page.");
}
$id = optional_param('id', 0, PARAM_INT);
$sesskey = $_SESSION['USER']->sesskey;
// To associate the entry with a module instance.
$PAGE->set_context($systemcontext);
$PAGE->set_title(get_string('pluginname', 'local_events'));
if ($id) {
    $PAGE->set_heading(get_string('editevent', 'local_events'));
    $PAGE->set_url('/local/events/addevent.php', array('id' => $id));
} else {
    $PAGE->set_heading(get_string('addingnewevent', 'local_events'));
    $PAGE->set_url('/local/events/addevent.php');
}
$PAGE->navbar->add(get_string('manage', 'local_events'), new moodle_url('/local/events/index.php'));
if ($id) {
    $PAGE->navbar->add(get_string('editevent', 'local_events'));
} else {
    $PAGE->navbar->add(get_string('addingnewevent', 'local_events'));
}
$PAGE->requires->js_call_amd('local_hall/hall', 'init');
$PAGE->requires->js_call_amd('local_hall/hallevents', 'init');
echo $OUTPUT->header();
$returnurl = new moodle_url('/local/events/index.php');
$editoroptions = array('maxfiles' => EDITOR_UNLIMITED_FILES, 
                       'maxbytes' => $CFG->maxbytes, 
                       'trusttext' => false, 
                       'noclean' => true);
if ($id) {
    $editoroptions['context'] = $systemcontext;
    $editoroptions['subdirs'] = file_area_contains_subdirs($systemcontext, 'local_events', 'description', 0);
    $eventdetails = $DB->get_record('local_events', array('id' => $id), '*', MUST_EXIST);

    $lang->$key = explode(',', $eventdetails->language);
    array_unshift($lang->$key, null);
    $eventdetails->language = array_flip($lang->$key);

    $gender->$key = explode(',', $eventdetails->audiencegender);
    array_unshift($gender->$key, null);
    $eventdetails->audiencegender = array_flip($gender->$key);

    $eventdetails->eventslothour = date('G', mktime(0, 0, $eventdetails->slot));
    $eventdetails->eventslotmin = date('i', mktime(0, 0, $eventdetails->slot));

    $eventdetails->description = ['text' => $eventdetails->description];
    $eventdetails->targetaudience = ['text' => $eventdetails->targetaudience];
    $eventdetails->cost = $eventdetails->price;
    if($eventdetails->startdate <= time()) {
        $eventdetails->eventstartdate = 1;
    } else {
        $eventdetails->eventstartdate = 0;
    }
    $current_date = date('Y-m-d H:i:s');
    $event_endttime = date('Y-m-d H:i:s', $eventdetails->enddate + $eventdetails->slot + $eventdetails->eventduration);
    if ($event_endttime < $current_date) {
        $eventdetails->eventenddate = 1;
        $eventdetails->event_duration = $eventdetails->eventduration;
    } else {
        $eventdetails->eventenddate = 0;
    }
    $draftitemid = file_get_submitted_draft_itemid('logo');
    file_prepare_draft_area($draftitemid, $systemcontext->id, 'local_events', 'logo', $eventdetails->logo, null);
    $eventdetails->logo = $draftitemid;
} else {
    $eventdetails = '';
}
$eventform = new local_events\form(null, array(
    'eventdetails' => $eventdetails,
    'editoroptions' => $editoroptions,
    'eventid' => $id,
    'eventcode' => $sesskey,
 ), 'post', '', null, true, (array)data_submitted());
if ($eventform->is_cancelled()) {
    (new local_hall\hall)->remove_reservation($sesskey, 'event');
    redirect($returnurl);
} else if ($data = $eventform->get_data()) {
    //var_dump($data); exit;
    $eventdata = (new local_events\events)->create_update_event($data, $editoroptions);
    redirect($returnurl);
}
$eventform->display();
echo $OUTPUT->footer();
