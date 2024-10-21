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
 *
 * @package    local_questionbank
 * @copyright  Moodle India
 * @author     Ikram Ahmad
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
// use local_questionbank;
require_once(__DIR__ . '/../../config.php');
require_once('lib.php');
$expert_id = optional_param('expert_id', 0, PARAM_INT);
$jsonformdata = optional_param('jsonformdata', '', PARAM_RAW);

require_login();
if ($expert_id) {
	$fullname = (new \local_trainingprogram\local\trainingprogram())->user_fullname_case();
    $expertsnames=$DB->get_record_sql("SELECT u.id, $fullname 
        FROM {user} as u 
        JOIN {local_users} AS lc ON lc.userid = u.id
        WHERE u.id = $expert_id ");

	$label = html_writer::tag('label', get_string('noofquestionsforexperts', 'local_questionbank' ,$expertsnames->fullname), ['class' => "d-inline word-break ", 'for' => 'label for ' . $expertsnames->fullname, 'id' => 'noofquestionfor_'.$expertsnames->id]);
    $lbldiv = html_writer::div('', "form-label-addon d-flex align-items-center align-self-start");
    $onfucusout = "(function(e){ require(['local_questionbank/assignexperts'], function(s) {s.checkvalue(noofquestionsfor_$expertsnames->id);}) }) (event)";
	$input = html_writer::tag('input', '', ['class' => 'form-control noofquestionallowed noofquestionsfor_'.$expertsnames->id, 'name' => 'noofquestionsfor_'.$expertsnames->id, 'id' => 'noofquestionsfor_'.$expertsnames->id, 'onfocusout' => $onfucusout]);
    $indiv = html_writer::div('', 'form-control-feedback invalid-feedback');
	$labeldiv = html_writer::div($label.$lbldiv, 'col-md-3 col-form-label d-flex pb-0 pr-md-0');
	$inputdiv = html_writer::div($input.$indiv, 'col-md-9 form-inline align-items-start felement');
    $row = html_writer::div($labeldiv.$inputdiv, 'form-group row  fitem   noofquestionsfor_'.$expertsnames->id, ['id' => 'fitem_id_noofquestionsfor_'.$expertsnames->id, ]);

    echo json_encode(['allowedquestionfields' => $row]);
    die;
}
if ($jsonformdata) {
    $arrayData = json_decode($jsonformdata);
    // $expertid = 'expertid';
    foreach ($arrayData as $item) {
        $name = $item->name;
        $value = $item->value;
        if ($name == 'expertid') {
            unset($name);
        }
        if ($name == 'expertid[]') {
            $name = 'expertid';
        }
        if (!isset($expertformdata[$name])) {
            $expertformdata[$name] = $value;
        } else {
            if (!is_array($expertformdata[$name])) {
                $expertformdata[$name] = [$expertformdata[$name]];
            }
            
            $expertformdata[$name][] = $value;
        }

    }
    echo json_encode(['status' => (new \questionbank)->create_qb_experts((object)$expertformdata)]);
    die;
}