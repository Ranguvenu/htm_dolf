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
 * List of Tasks
 * 
 * @package    mod_teamsmeeting
 * @copyright  2022 eAbyas Info Solutions Pvt Ltd (www.eabyas.com)
 * @author     Ranga Reddy<rangareddy@eabyas.com>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
defined('MOODLE_INTERNAL') || die();

$tasks = array(
	array(
		'classname' => 'mod_teamsmeeting\task\get_attendance_report_after_meeting',
		'blocking' => 0,
		'minute' => '*',
		'hour' => '*/1',
		'day' => '*',
		'dayofweek' => '*',
		'month' => '*',
	)
);

