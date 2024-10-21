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
$PAGE->set_context($systemcontext);
require_login();
if (!(is_siteadmin() || has_capability('local/cpd:manage', $systemcontext) || has_capability('local/organization:manage_cpd', $systemcontext)
|| has_capability('local/cpd:create', $systemcontext) || has_capability('local/organization:manage_trainee', $systemcontext) || has_capability('local/organization:manage_organizationofficial',$systemcontext) || has_capability('local/organization:manage_trainingofficial',$systemcontext)) ) {
    print_error(get_string('permissionerror', 'local_cpd'));
}
$output = $PAGE->get_renderer('local_cpd');

$PAGE->set_pagelayout('standard');

$PAGE->set_url('/local/cpd/index.php');

$capability = $output->manage_capability();
$PAGE->set_title(get_string('cpd', 'local_cpd'));
if ($capability) {
    $PAGE->set_heading(get_string('manage', 'local_cpd'));
} else if(!is_siteadmin() && has_capability('local/organization:manage_organizationofficial',$systemcontext) || has_capability('local/organization:manage_trainingofficial',$systemcontext)) {
    $PAGE->set_heading(get_string('cpd', 'local_cpd'));
} else {
    $PAGE->set_title(get_string('my_cpd', 'local_cpd'));
    $PAGE->set_heading(get_string('my_cpd', 'local_cpd'));  
}
echo $OUTPUT->header();
if ($capability || has_capability('local/organization:manage_trainingofficial',$systemcontext)) {
   (new local_cpd\lib)->cpdinfo();
} else if (!is_siteadmin() && has_capability('local/organization:manage_organizationofficial',$systemcontext)  ) {
    (new local_cpd\lib)->orgcpdinfo();
} else {
    (new local_cpd\lib)->mycpd_evidenceinfo();
}
echo $OUTPUT->footer();
