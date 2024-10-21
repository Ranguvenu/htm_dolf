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

$mode = optional_param('mode', 1, PARAM_INT);


$context=context_system::instance();

$PAGE->set_url('/admin/tool/product/financialpayments.php');
$PAGE->set_pagelayout('standard');

$blockinstanceid=$DB->get_field_sql("SELECT id FROM {block_instances} ORDER BY id DESC LIMIT 1");

$PAGE->set_context(context_block::instance($blockinstanceid));

if (!is_siteadmin() && (has_capability('local/organization:manage_communication_officer',$context))) {
    $PAGE->set_title(get_string('comfinancialpayments', 'tool_product'));
    $PAGE->set_heading(get_string('comfinancialpayments', 'tool_product'));
} else {
    $PAGE->set_title(get_string('financialpayments', 'tool_product'));
    $PAGE->set_heading(get_string('financialpayments', 'tool_product'));
}
$PAGE->requires->jquery_plugin('ui-css');
$PAGE->navbar->add(get_string("financialpayments", 'tool_product'), new moodle_url('/tool/product/financialpayments.php',array('mode'=>1)));
$renderer = $PAGE->get_renderer('tool_product');


echo $OUTPUT->header();

if(!has_capability('tool/products:managefinancialpayments', $context) && !has_capability('local/organization:manage_communication_officer', $context) && !has_capability('local/organization:manage_financial_manager',$context)){

    throw new required_capability_exception($context, 'tool/product:managefinancialpayments', 'nopermissions', '');
}


$selectedtab = $mode;
if($mode==3 || $mode==4){
    $selectedtab = 2;  
}
$tabs = array();

$tabs[] = new tabobject(1, new moodle_url('/admin/tool/product/financialpayments.php',array('mode'=>1)), get_string('orgpayments', 'tool_product'));
$tabs[] = new tabobject(2, new moodle_url('/admin/tool/product/financialpayments.php',array('mode'=>2)), get_string('traineepayments', 'tool_product'));
echo $OUTPUT->tabtree($tabs, $selectedtab);
    
if($mode==2 || $mode==3 || $mode==4){
    echo $renderer->get_traineepayments();
} elseif ($mode==1){
    echo $renderer->get_post_financialpayments();
}
echo $OUTPUT->footer();
