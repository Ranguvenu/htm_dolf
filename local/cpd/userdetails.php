<?php
/**
 * This file is part of eAbyas
 *
 * Copyright eAbyas Info Solutons Pvt Ltd, India
 *
 * This program is free software; you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation; either version 3 of the License, or
 * (at your option) any later version.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with this program.  If not, see <http://www.gnu.org/licenses/>.
 *
 * @author eabyas  <info@eabyas.in>
 * @package F-academy
 * @subpackage local_cpd
 */
require_once(dirname(__FILE__) . '/../../config.php');
global $CFG;
$systemcontext = context_system::instance();
require_login();
$id = optional_param('id', 0, PARAM_INT);
$ceid = optional_param('ceid', 0, PARAM_INT);
$evdtype = optional_param('evdtype', 0, PARAM_INT);
$PAGE->set_pagelayout('admin');
$PAGE->set_context($systemcontext);
$PAGE->set_url('/local/cpd/userdetails.php');
$PAGE->set_title(get_string('cpd', 'local_cpd'));
$PAGE->set_heading(get_string('manage', 'local_cpd'));
$PAGE->navbar->add(get_string('manage', 'local_cpd'));

$PAGE->requires->js_call_amd('local_cpd/cpdform', 'init');

$renderer = $PAGE->get_renderer('local_cpd');
$content = $renderer->get_userdetails($id, $ceid, $evdtype);
echo $OUTPUT->header();
echo $content;
echo $OUTPUT->footer();