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
 * Definitions of constants for gradebook
 *
 * @package   local_exams
 * @author    Ikram Ahmad (ikram.ahmad@moodle.com)
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
require_once('../../config.php');
require_login();
$systemcontext = context_system::instance();
$PAGE->set_context($systemcontext);
require_login();
$PAGE->set_url('/local/exams/certificates.php');
$title = get_string('issuecertificatesnav', 'local_exams');
$PAGE->set_title($title);
$PAGE->set_heading($title);
$PAGE->navbar->add(get_string('exams', 'local_exams'), new moodle_url('/local/exams/index.php'));
$PAGE->navbar->add($title);
$PAGE->requires->js_call_amd('tool_certificate/issues-list', 'init');
$PAGE->requires->js_call_amd('local_exams/issue_certificates', 'init');

echo $OUTPUT->header();
$examobj = new local_exams\local\exams();
if (has_capability('local/exams:canissuecertificate',$systemcontext)) {
    $examusers = $examobj->render_examusers();
}

echo $OUTPUT->footer();
