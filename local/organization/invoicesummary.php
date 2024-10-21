<?php
require_once('../../config.php');
global $CFG, $PAGE, $OUTPUT, $DB;
require_login();
$systemcontext = context_system::instance();
$PAGE->set_context($systemcontext);
$PAGE->set_url(new moodle_url('/local/organization/invoicesummary.php'));
$PAGE->set_title(get_string('invoice_summary', 'local_organization'));
$PAGE->set_heading(get_string('invoice_summary', 'local_organization'));


echo $OUTPUT->header();

(new local_organization\organization)->invoicesummaryinfo();

echo $OUTPUT->footer();
