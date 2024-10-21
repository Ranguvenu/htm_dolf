<?php
require_once('../../../config.php');
global $CFG, $PAGE, $OUTPUT, $DB;
$systemcontext = context_system::instance();
$PAGE->set_context($systemcontext);
$PAGE->set_url('/admin/tool/product/cart.php');
$PAGE->set_title(get_string('cart', 'tool_product'));
// $PAGE->set_heading(get_string('cart', 'tool_product'));
$PAGE->navbar->add(get_string('cart', 'tool_product'), new moodle_url('/admin/tool/product/cart.php'));

$renderer = $PAGE->get_renderer('tool_product');

echo $OUTPUT->header();


echo $renderer->cart_page();

// $products = (new product)->getproducts([10, 12]);
// print_r($products);
echo $OUTPUT->footer();
