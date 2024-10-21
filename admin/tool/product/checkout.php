<?php
require_once('../../../config.php');
global $CFG, $PAGE, $OUTPUT, $DB;
$systemcontext = context_system::instance();
$PAGE->set_context($systemcontext);
require_login();
$PAGE->set_url('/admin/tool/product/checkout.php');
$PAGE->set_title(get_string('checkout', 'tool_product'));
//$PAGE->set_heading(get_string('checkout', 'tool_product'));
$PAGE->navbar->add(get_string('checkout', 'tool_product'), new moodle_url('/admin/tool/product/checkout.php'));

$renderer = $PAGE->get_renderer('tool_product');

echo $OUTPUT->header();


echo $renderer->checkout_page();

echo $OUTPUT->footer();
