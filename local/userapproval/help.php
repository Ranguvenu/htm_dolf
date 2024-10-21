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
require_capability('local/userapproval:view', $systemcontext);
$PAGE->set_context($systemcontext);
$PAGE->set_url('/local/userapproval/help.php');
//$PAGE->set_pagelayout('admin');
$strheading = get_string('manual', 'local_userapproval');
$PAGE->set_title($strheading);
if ($CFG->forcelogin) {
    require_login();
} else {
    user_accesstime_log();
}

$PAGE->set_heading($strheading);

$PAGE->navbar->add(get_string('users', 'local_userapproval'), new moodle_url('/local/userapproval/index.php'));
$PAGE->navbar->add(get_string('uploadusers', 'local_userapproval'), new moodle_url('local/userapproval/uploadusers.php'));
// $PAGE->navbar->add(get_string('manual', 'local_users'));
echo $OUTPUT->header();
echo html_writer::tag('a',get_string('back_upload','local_userapproval'),array('href'=>$CFG->wwwroot. '/local/userapproval/uploadusers.php','class'=>"btn btn-secondary ml-2 float-right")).'<br><br>';
//echo '<div class="pull-right mb-3"><a href="/uploadsector.php" class="btn">' . get_string('back_upload', 'local_sector') . '</a></div>';
// echo $OUTPUT->heading(get_string('pluginname', 'local_sector'));
// if (isset($CFG->allowframembedding) and ! $CFG->allowframembedding) {
//     echo $OUTPUT->box(get_string('helpmanual', 'local_sector'));
//     echo '<div class="pull-right mb-3"><a href="local/sector/uploadsector.php" class="btn">' . get_string('back_upload', 'local_users') . '</a></div>';
// }
if(!is_siteadmin() && has_capability('local/organization:manage_organizationofficial', $systemcontext)) {
  echo get_string('org_help_1', 'local_userapproval');
} else {
  echo get_string('help_1', 'local_userapproval');
}


echo $OUTPUT->footer();
?>
