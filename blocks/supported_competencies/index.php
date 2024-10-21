<?php
/*
* This file is a part of e abyas Info Solutions.
*
* Copyright e abyas Info Solutions Pvt Ltd, India.
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
* @author e abyas  <info@eabyas.com>
*/
/**
 * Defines message my supported competencies
 *
 * @package    block_supported_competencies
 * @copyright  e abyas  <info@eabyas.com>
 */
require_once('../../config.php');
require_login();

use local_competency\competency;

$competencyid = optional_param('id', 0, PARAM_INT);


require_once($CFG->dirroot.'/blocks/supported_competencies/lib.php');


$url = new moodle_url('/blocks/supported_competencies/index.php');

$context = context_system::instance();

$PAGE->set_context($context);
$PAGE->set_url($url);
$PAGE->set_pagelayout('standard');
$PAGE->set_title(get_string('mysupported_competencies', 'block_supported_competencies'));
$PAGE->set_heading(get_string('mysupported_competencies', 'block_supported_competencies'));
$PAGE->requires->jquery_plugin('ui-css');
$PAGE->requires->css('/blocks/supported_competencies/css/jquery.dataTables.min.css');
$PAGE->navbar->add(get_string('mysupported_competencies', 'block_supported_competencies'));

$renderer = $PAGE->get_renderer('block_supported_competencies');


echo $OUTPUT->header();

competency::is_competence_trainee();


if($competencyid){

    $competencyrenderer = $PAGE->get_renderer('local_competency');

    echo $competencyrenderer->view_competency($competencyid,'supportedcompetencies');

}else{

    echo $renderer->mysupported_competencies('page');
}

echo $OUTPUT->footer();
