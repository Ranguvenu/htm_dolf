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
 * TODO describe file offering_creation_updation_requests
 *
 * @package    local_trainingprogram
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

require('../../config.php');
require_login();
global $USER,$OUTPUT,$PAGE;

$url = new moodle_url('/local/trainingprogram/offering_program_requests.php', []);
$PAGE->set_url($url);
$systemcontext = context_system::instance();
$PAGE->set_context($systemcontext);
require_capability('local/trainingprogram:manage_offering_program_requests',$systemcontext);
$title =  get_string('offering_program_requests','local_trainingprogram');
$PAGE->set_title($title);
$PAGE->set_heading($title);
$PAGE->navbar->add($title,new moodle_url('/local/trainingprogram/offering_program_requests.php'));
$PAGE->set_heading($SITE->fullname);
$PAGE->requires->js_call_amd('local_trainingprogram/offering_program_requests', 'init');
if(!is_siteadmin() && !has_capability('local/organization:training_supervisor',$systemcontext)){
    throw new moodle_exception(get_string('accessdenied','local_trainingprogram'));
}
echo $OUTPUT->header();
(new local_trainingprogram\local\trainingprogram)->offering_program_requests();
echo $OUTPUT->footer();
