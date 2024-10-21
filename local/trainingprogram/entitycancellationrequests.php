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
 * TODO describe file entitycancellationrequests
 *
 * @package    local_trainingprogram
 * @copyright  2023 YOUR NAME <your@email.com>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
require_once('../../config.php');
global $CFG, $PAGE, $OUTPUT, $DB;
$PAGE->set_url('/local/trainingprogram/entitycancellationrequests.php');
$systemcontext = context_system::instance();
require_capability('local/trainingprogram:manage_entitycancellationrequests',$systemcontext);
$PAGE->set_context($systemcontext);
$entitytype= required_param('entitytype',PARAM_RAW);
require_login();
$title = ($entitytype == 'event') ? get_string('eventcancellationrequests','local_trainingprogram'): (($entitytype == 'profile') ?  get_string('profilecancellationrequests','local_trainingprogram')  : get_string('offeringcancellationrequests','local_trainingprogram'));
$PAGE->set_title($title);
$PAGE->set_heading($title);
$PAGE->navbar->add($title,new moodle_url('/local/trainingprogram/entitycancellationrequest.php'));
$PAGE->requires->js_call_amd('local_trainingprogram/entityaction', 'init');
if(($entitytype == 'offering' && !is_siteadmin() && !has_capability('local/organization:training_supervisor',$systemcontext) && !has_capability('local/organization:manage_financial_manager',$systemcontext)) ||
($entitytype == 'profile' && !is_siteadmin() && !has_capability('local/organization:manage_exammanager',$systemcontext)) || ($entitytype == 'event' && !is_siteadmin() && !has_capability('local/organization:manage_eventmanager',$systemcontext))
){
    throw new moodle_exception(get_string('accessdenied','local_trainingprogram'));
}
echo $OUTPUT->header();
    (new local_trainingprogram\local\trainingprogram)->entitycancellationrequests($entitytype);
echo $OUTPUT->footer();
