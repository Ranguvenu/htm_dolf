<?php
require_once('../../../config.php');
global $CFG, $PAGE, $OUTPUT, $DB;
$systemcontext = context_system::instance();
$PAGE->set_context($systemcontext);
require_login();
$PAGE->set_url('/admin/tool/product/index.php');

$renderer = $PAGE->get_renderer('tool_product');
echo $OUTPUT->header();

echo $renderer->add_to_cart(5, 1);

// echo $renderer->add_to_cart(6, 1);

echo $OUTPUT->footer();
