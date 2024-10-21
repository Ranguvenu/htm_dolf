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
 */
require_once(dirname(__FILE__) . '/../../config.php');
global $CFG, $DB;
$systemcontext = get_context_instance(CONTEXT_SYSTEM);
$PAGE->set_context($systemcontext);
$PAGE->set_url('/local/organization/help.php');
require_capability('local/organization:visible',$systemcontext);
//$PAGE->set_pagelayout('admin');
$strheading = get_string('pluginname', 'local_organization');
$PAGE->set_title($strheading);
if(!(is_siteadmin() || has_capability('local/organization:manage_communication_officer',$systemcontext))){
    echo print_error('no permission');
}
if ($CFG->forcelogin) {
    require_login();
} else {
    user_accesstime_log();
}
// $PAGE->set_heading($SITE->fullname);
$PAGE->navbar->add(get_string('pluginname', 'local_organization'), new moodle_url('/local/organization/index.php'));
$PAGE->navbar->add(get_string('uploadorg', 'local_organization'), new moodle_url('local/organization/uploadorganization.php'));
// $PAGE->navbar->add(get_string('manual', 'local_users'));
echo $OUTPUT->header();
echo html_writer::tag('a',get_string('back_upload','local_organization'),array('href'=>$CFG->wwwroot. '/local/organization/uploadorganization.php','class'=>"btn btn-secondary ml-2 my-4 float-right"));
echo get_string('help_1', 'local_organization');

echo $OUTPUT->footer();
?>
