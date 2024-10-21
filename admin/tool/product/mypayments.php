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
$mode = optional_param('mode', 'paid', PARAM_RAW);
$competencyid = optional_param('id', 0, PARAM_INT);
$PAGE->set_url('/admin/tool/product/mypayments.php?mode=paid');
$PAGE->set_pagelayout('standard');
$PAGE->set_context(context_system::instance());
$PAGE->set_title(get_string('mypayments', 'tool_product'));
$PAGE->set_heading(get_string('mypayments', 'tool_product'));
$PAGE->requires->jquery_plugin('ui-css');
$PAGE->navbar->add(get_string("mypayments", 'tool_product'), new moodle_url('/tool/product/mypayments.php'));
$renderer = $PAGE->get_renderer('tool_product');
echo $OUTPUT->header();

$tabs = array();
$tabs[] = new tabobject('paid', new moodle_url('/admin/tool/product/mypayments.php',array('mode'=>'paid')), get_string('mypayments', 'tool_product'),true);
$tabs[] = new tabobject('refund', new moodle_url('/admin/tool/product/mypayments.php',array('mode'=>'refund')), get_string('myrefunds', 'tool_product'));
echo $OUTPUT->tabtree($tabs, $mode);
$filter['mode'] = $mode;
echo $renderer->get_mypayments($filter);

echo $OUTPUT->footer();
