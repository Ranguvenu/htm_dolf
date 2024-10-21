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
 * @package    local_exams
 * @copyright  2022 e abyas  <info@eabyas.com>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

define('AJAX_SCRIPT', true);
require_once(dirname(__FILE__) . '/../../config.php');

use local_exams\local\exams;

global $DB, $CFG, $USER, $PAGE;
$examid = required_param('examid', PARAM_INT);

$context=context_system::instance();

if(!has_capability('local/organization:manage_examofficial', $context)){

    throw new required_capability_exception($context, 'local/exams:manage_examofficial', 'nopermissions', '');

}

$selectquestionbanks = optional_param_array('selectquestionbanks', null, PARAM_TEXT);
$selectnoofquestions = optional_param_array('selectnoofquestions', null, PARAM_INT);
$noofquestionsresult = array_diff($selectnoofquestions, [0]); 
$questionbanksresult = array_diff($selectquestionbanks, [0]);

if($noofquestionsresult && $questionbanksresult){
    echo exams::set_questionbanks_exams($examid,$questionbanksresult,$noofquestionsresult);
} else {
    echo get_string('nobanknoquestion', 'local_exams');
}
