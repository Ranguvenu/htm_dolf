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
 * Definition of trainingprogram tasks.
 *
 * @package   local_trainingprogram
 * @category  task
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die();

/* List of handlers */

$tasks = array(

	array(
		'classname' => 'local_trainingprogram\task\send_trainingprogram_before_7_days',
		'blocking' => 0,
		'minute' => '*',
		'hour' => '*',
		'day' => '*/1',
		'dayofweek' => '*',
		'month' => '*',
	),
	array(
		'classname' => 'local_trainingprogram\task\send_trainingprogram_before_48_hours',
		'blocking' => 0,
		'minute' => '*',
		'hour' => '*',
		'day' => '*/1',
		'dayofweek' => '*',
		'month' => '*',
	),
	array(
		'classname' => 'local_trainingprogram\task\send_trainingprogram_before_72_hours',
		'blocking' => 0,
		'minute' => '*',
		'hour' => '*',
		'day' => '*/1',
		'dayofweek' => '*',
		'month' => '*',
	),
	array(
		'classname' => 'local_trainingprogram\task\send_trainingprogram_before_24_hours',
		'blocking' => 0,
		'minute' => '*',
		'hour' => '*',
		'day' => '*/1',
		'dayofweek' => '*',
		'month' => '*',
	),
	array(
		'classname' => 'local_trainingprogram\task\send_trainingprogram_after_session',
		'blocking' => 0,
		'minute' => '*',
		'hour' => '*',
		'day' => '*/1',
		'dayofweek' => '*',
		'month' => '*',
	),
	array(
		'classname' => 'local_trainingprogram\task\send_trainingprogram_send_conclusion',
		'blocking' => 0,
		'minute' => '*',
		'hour' => '*',
		'day' => '*/1',
		'dayofweek' => '*',
		'month' => '*',
	),
	array(
		'classname' => 'local_trainingprogram\task\send_trainingprogram_before_30_minutes',
		'blocking' => 0,
		'minute' => '*',
		'hour' => '*',
		'day' => '*/1',
		'dayofweek' => '*',
		'month' => '*',
	),
	array(
		'classname' => 'local_trainingprogram\task\send_trainingprogram_enrolled_inactive_accounts',
		'blocking' => 0,
		'minute' => '*',
		'hour' => '*',
		'day' => '*/1',
		'dayofweek' => '*',
		'month' => '*',
	),
	array(
		'classname' => 'local_trainingprogram\task\send_trainingprogram_pre_assessment_opened',
		'blocking' => 0,
		'minute' => '*',
		'hour' => '*',
		'day' => '*/1',
		'dayofweek' => '*',
		'month' => '*',
	),
	array(
		'classname' => 'local_trainingprogram\task\send_trainingprogram_post_assessment_opened',
		'blocking' => 0,
		'minute' => '*',
		'hour' => '*',
		'day' => '*/1',
		'dayofweek' => '*',
		'month' => '*',
	),
	array(
		'classname' => 'local_trainingprogram\task\send_trainingprogram_post_assessment_closed',
		'blocking' => 0,
		'minute' => '*',
		'hour' => '*',
		'day' => '*/1',
		'dayofweek' => '*',
		'month' => '*',
	),
	array(
		'classname' => 'local_trainingprogram\task\send_trainingprogram_pre_assessment_closed',
		'blocking' => 0,
		'minute' => '*',
		'hour' => '*',
		'day' => '*/1',
		'dayofweek' => '*',
		'month' => '*',
	),
	array(
		'classname' => 'local_trainingprogram\task\send_trainingprogram_assignment_deadline_4_hours',
		'blocking' => 0,
		'minute' => '*',
		'hour' => '*',
		'day' => '*/1',
		'dayofweek' => '*',
		'month' => '*',
	),
	array(
		'classname' => 'local_trainingprogram\task\send_trainingprogram_assignment_deadline_24_hours',
		'blocking' => 0,
		'minute' => '*',
		'hour' => '*',
		'day' => '*/1',
		'dayofweek' => '*',
		'month' => '*',
	),
	array(
		'classname' => 'local_trainingprogram\task\delete_bulk_upload_log',
		'blocking' => 0,
		'minute' => '*',
		'hour' => '*/1',
		'day' => '*',
		'dayofweek' => '*',
		'month' => '*',
	),

	array(
		'classname' => 'local_trainingprogram\task\send_trainingprogram_before_10_days',
		'blocking' => 0,
		'minute' => '*',
		'hour' => '*',
		'day' => '*/1',
		'dayofweek' => '*',
		'month' => '*',
	),
	array(
		'classname' => 'local_trainingprogram\task\update_offering_status', 
		'blocking' => 0,
		'minute' => '*',
		'hour' => '*/1',
		'day' => '*',
		'dayofweek' => '*',
		'month' => '*',
	),
	array(
		'classname' => 'local_trainingprogram\task\update_addedincart_coupon_status', 
		'blocking' => 0,
		'minute' => '*',
		'hour' => '*/1',
		'day' => '*',
		'dayofweek' => '*',
		'month' => '*',
	)
);
