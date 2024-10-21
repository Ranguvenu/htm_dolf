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

$PAGE->set_url('/local/competency/mycompetency.php');
$PAGE->set_pagelayout('standard');
$PAGE->set_context(context_system::instance());
$PAGE->set_title(get_string('mycompetencies', 'local_competency'));
$PAGE->set_heading(get_string('mycompetencies', 'local_competency'));
$PAGE->requires->jquery_plugin('ui-css');
$PAGE->requires->css('/local/competency/css/jquery.dataTables.min.css');
$PAGE->navbar->add(get_string("mycompetencies", 'local_competency'), new moodle_url('/local/competency/mycompetency.php'));
$renderer = $PAGE->get_renderer('local_competency');


echo $OUTPUT->header();

competency::is_competence_trainee();


if($competencyid){

    echo $renderer->view_competency($competencyid);

}else{

    echo $renderer->get_mycompetencies();

}

echo $OUTPUT->footer();