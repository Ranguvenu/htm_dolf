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
require_login();
$evidid = required_param('id', PARAM_INT);
$userid = optional_param('uid',0, PARAM_INT);
$systemcontext = context_system::instance();

$PAGE->set_context($systemcontext);
if($userid) {
  $PAGE->set_url('/local/cpd/view.php', array('id' => $evidid, 'uid' => $userid));
} else {
  $PAGE->set_url('/local/cpd/view.php',array('id' => $evidid));
}
//$PAGE->set_url('/local/cpd/view.php', array('id' => $evidid, 'uid' => $userid));

$output = $PAGE->get_renderer('local_cpd');
$output->hascapability();
$cpd = $output->cpd_check($evidid);

echo $output->header();

$data = (new local_cpd\local\cpd)->get_cpdcontent($evidid, $userid);
$renderable = new \local_cpd\output\cpdview($data);

echo $output->render($renderable);
$capability = $output->manage_capability();
if ($capability || has_capability('local/organization:manage_trainingofficial',$systemcontext)) {
  echo $output->get_cpd_users();
} else if(!is_siteadmin() && has_capability('local/organization:manage_trainee', $systemcontext) || has_capability('local/organization:manage_organizationofficial',$systemcontext)) {
  echo $output->get_catalog_reportedhrs();
}

echo $output->get_catalog_training_programs();

echo $output->footer();
