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
 * my payments view page
 *
 * @package    tool_product
 * @copyright  2022 e abyas  <info@eabyas.com>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

require_once('../../../config.php');

use tool_product\product;
global $CFG, $PAGE, $OUTPUT, $DB;
require_login();
$competencyid = optional_param('id', 0, PARAM_INT);

$PAGE->set_url('/admin/tool/product/payments.php');
//$PAGE->set_pagelayout('standard');
$PAGE->set_context(context_system::instance());
$PAGE->set_title(get_string('payments', 'tool_product'));
$PAGE->set_heading(get_string('payments', 'tool_product'));
//$PAGE->requires->jquery_plugin('ui-css');
$PAGE->navbar->add(get_string("payments", 'tool_product'), new moodle_url('/admin/tool/product/payments.php'));
$renderer = $PAGE->get_renderer('tool_product');
echo $OUTPUT->header();

echo $renderer->get_mypayments();

echo $OUTPUT->footer();
