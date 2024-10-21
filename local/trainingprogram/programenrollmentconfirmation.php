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
 * Defines the version of Training program
 * @package    local_trainingprogram
 * @copyright  2022 Naveen kumar <naveen@eabyas.com>
 */
require_once('../../config.php');
global $CFG, $PAGE, $OUTPUT, $DB;
$PAGE->set_url('/local/trainingprogram/programenrollmentconfirmation.php');
$systemcontext = context_system::instance();
$programid     = required_param('programid', PARAM_INT);
$offeringid     = required_param('offeringid', PARAM_INT);
$roleid     = required_param('roleid',PARAM_INT);
$organization = required_param('organization', PARAM_INT);
$orgofficial = required_param('orgofficial', PARAM_INT);
$cusers     = required_param('cusers',PARAM_RAW);
$systemcontext = context_system::instance();
$PAGE->set_context($systemcontext);
require_login();
$PAGE->set_url('/local/trainingprogram/programenrollmentconfirmation.php', ['programid'=> $programid, 'offeringid' => $offeringid,'roleid'=>$roleid,'organization' => $organization, 'orgofficial' => $orgofficial,'cusers' => $cusers]);
$PAGE->set_title(get_string('pluginname', 'local_trainingprogram'));
$PAGE->set_heading(get_string('enrollmentconfirmation', 'local_exams'));
if( !is_siteadmin() && !has_capability('local/organization:manage_trainingofficial', $systemcontext) && !has_capability('local/organization:manage_organizationofficial', $systemcontext)){
    throw new exception(get_string('nopermissions','local_trainingprogram'));
}
$PAGE->navbar->add(get_string('pluginname', 'local_trainingprogram'), new moodle_url('/local/trainingprogram/index.php'));
$PAGE->navbar->add(get_string('enrollment', 'local_trainingprogram'), new moodle_url('/local/trainingprogram/programenrollment.php', ['programid'=> $programid, 'roleid' => $roleid, 'offeringid' => $offeringid]));
$PAGE->navbar->add(get_string('bulkenrollment', 'local_exams'), new moodle_url('/local/trainingprogram/bulkenrollments.php', ['programid'=> $programid, 'roleid' => $roleid, 'offeringid' => $offeringid]));
$PAGE->navbar->add(get_string('enrollmentconfirmation', 'local_exams'));
$renderer = $PAGE->get_renderer('local_exams');
$PAGE->requires->js_call_amd('local_exams/bulkenrollment', 'init');
$renderer = $PAGE->get_renderer('local_trainingprogram');
echo $OUTPUT->header();
    (new local_trainingprogram\local\trainingprogram)->programenrollmentsview($programid,$offeringid,$roleid,$cusers,$organization,$orgofficial);
echo $OUTPUT->footer();
