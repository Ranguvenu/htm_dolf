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
require_login();

$systemcontext = context_system::instance();
if(!(is_siteadmin() || has_capability('local/learningtracks:managelearningtracks',$systemcontext) || has_capability('local/organization:manage_trainingofficial',$systemcontext) || has_capability('local/organization:manage_organizationofficial',$systemcontext))){
    print_error(get_string('permissionerror', 'local_learningtracks'));
}
$PAGE->set_url('/local/learningtracks/index.php');
$PAGE->set_context(context_system::instance());
$PAGE->set_title(get_string('pluginname', 'local_learningtracks'));
$PAGE->set_heading(get_string('pluginname', 'local_learningtracks'));
$PAGE->navbar->add(get_string("pluginname", 'local_learningtracks'), new moodle_url('/local/learningtracks/index.php'));
echo $OUTPUT->header();
$renderer = $PAGE->get_renderer('local_learningtracks');
$renderable = new \local_learningtracks\output\learningtracks();
echo $renderer->render($renderable);
//(new local_learningtracks\learningtracks)->alltracks();
echo $OUTPUT->footer();
