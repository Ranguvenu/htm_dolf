<?php
/**
 * Defines the version of Training program
 * @package    tool_certificate
 * @copyright  2023 Mallikarjun
 */
require_once('../../../config.php');
global $CFG, $PAGE, $OUTPUT, $DB;
$PAGE->set_context(context_system::instance());
$PAGE->set_url(new moodle_url('/admin/tool/certificate/view_certificates.php'));
$systemcontext = context_system::instance();
$PAGE->set_context($systemcontext);
require_login();
$PAGE->set_title(get_string('certificates', 'tool_certificate'));
$PAGE->set_heading(get_string('certificates', 'tool_certificate'));
$renderer = $PAGE->get_renderer('tool_certificate');
$PAGE->requires->js_call_amd('tool_certificate/issues-list', 'init');
echo $OUTPUT->header();

// echo $renderer->view_certificates($filterparams);
    (new tool_certificate\local\certificate)->certificates_view();
echo $OUTPUT->footer();
