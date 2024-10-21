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
 * financial payments view page
 *
 * @package    tool_product
 * @copyright  2022 e abyas  <info@eabyas.com>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

require_once('../../../config.php');

use tool_product\product;


global $CFG, $PAGE, $OUTPUT, $DB;

require_login();

$context=context_system::instance();


$PAGE->set_url('/admin/tool/product/orderapproval.php');
$PAGE->set_pagelayout('standard');


$blockinstanceid=$DB->get_field_sql("SELECT id FROM {block_instances} ORDER BY id DESC LIMIT 1");

$PAGE->set_context(context_block::instance($blockinstanceid));
$PAGE->set_title(get_string('orderapproval', 'tool_product'));
$PAGE->set_heading(get_string('orderapproval', 'tool_product'));
$PAGE->requires->jquery_plugin('ui-css');
$PAGE->navbar->add(get_string("orderapproval", 'tool_product'), new moodle_url('/tool/product/orderapproval.php'));
$renderer = $PAGE->get_renderer('tool_product');


echo $OUTPUT->header();

if(!has_capability('tool/products:manageorderapproval', $context)){

    throw new required_capability_exception($context, 'tool/product:manageorderapproval', 'nopermissions', '');
}


echo $renderer->get_orders_approval();



echo $OUTPUT->footer();
