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
 * msteams module main user interface
 *
 * @package    mod_msteams
 * @copyright  2020 Robert Schrenk
 *             based on 2009 Petr Skoda  {@link http://skodak.org}
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

require('../../config.php');
require_once("$CFG->dirroot/mod/webexactivity/lib.php");
// require_once("$CFG->dirroot/mod/url/locallib.php");
// require_once($CFG->libdir . '/completionlib.php');
// require_once('/lib.php');
global $CFG, $DB;

$id = optional_param('id', 0, PARAM_INT); // Course module ID.

$code = optional_param('code', false, PARAM_RAW);
if($code){
    $refresh = tokens($code);
}else{
}

$objdata = new stdClass();
$objdata->value = $refresh;
$objdata->name = 'webexrefreshtoken';
if(!($DB->record_exists('config', ['name' => 'webexrefreshtoken']))){
    $DB->insert_record('config', $objdata);
}else{
    $objdata->id =$DB->get_field('config', 'id', ['name' => 'webexrefreshtoken']);
    $DB->update_record('config', $objdata);
}
// redirect($CFG->wwwroot.'/mod/webexactivity/view.php?id=93763');
