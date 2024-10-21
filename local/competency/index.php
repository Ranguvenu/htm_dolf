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
 * Competency view page
 *
 * @package    local_competency
 * @copyright  2022 e abyas  <info@eabyas.com>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

require_once('../../config.php');

use local_competency\competency;

global $CFG, $PAGE, $OUTPUT, $DB;

require_login();

$competencyid = optional_param('id', 0, PARAM_INT);

$context=context_system::instance();

$PAGE->set_url(new moodle_url('/local/competency/index.php', array('id' => $id)));
$PAGE->set_pagelayout('standard');
$PAGE->set_context($context);
$PAGE->set_title(get_string('competencymanagement', 'local_competency'));
$PAGE->set_heading(get_string('competencymanagement', 'local_competency'));
$PAGE->navbar->add(get_string("mycompetencies", 'local_competency'), new moodle_url('/local/competency/index.php'));
$renderer = $PAGE->get_renderer('local_competency');


echo $OUTPUT->header();

if(is_siteadmin() || has_capability('local/competency:managecompetencies', $context) || has_capability('local/organization:manage_competencies_official', $context)){


    if($competencyid){

        echo $renderer->view_competency($competencyid);

    }else{

        echo $renderer->get_competencies();
    }

}else{

    throw new required_capability_exception($context, 'local/competency:managecompetencies', 'nopermissions', '');
}


echo $OUTPUT->footer();
