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
 * Defines the strings of Products
 *      
 * @package    tool_product
 * @copyright  2022 Naveen kumar <naveen@eabyas.com>
 */


defined('MOODLE_INTERNAL') || die();

$observers = array(

    array(
        'eventname' => '\local_trainingprogram\event\tpofferings_created',
        'callback' => '\tool_product\observer\tp_observer::create',
    ),
    array(
        'eventname' => '\local_trainingprogram\event\tpofferings_updated',
        'callback' => '\tool_product\observer\tp_observer::update',
    ),
    array(
        'eventname' => '\local_trainingprogram\event\tpofferings_deleted',
        'callback' => '\tool_product\observer\tp_observer::delete',
    ),
    array(
        'eventname' => '\local_hall\event\hall_reserved',
        'callback' => '\tool_product\observer\exam_observer::create',
    ),
    array(
        'eventname' => '\local_hall\event\reservation_update',
        'callback' => '\tool_product\observer\exam_observer::update',
    ),
    array(
        'eventname' => '\local_exams\event\exam_deleted',
        'callback' => '\tool_product\observer\exam_observer::delete',
    ),
        array(
        'eventname' => '\local_events\event\events_created',
        'callback' => '\tool_product\observer\event_observer::create',
    ),
    array(
        'eventname' => '\local_events\event\events_updated',
        'callback' => '\tool_product\observer\event_observer::update',
    ),
    array(
        'eventname' => '\local_events\event\events_deleted',
        'callback' => '\tool_product\observer\event_observer::delete',
    ),
    array(
        'eventname' => '\local_exams\event\exam_attempt',
        'callback' => '\tool_product\observer\examattempt_observer::create',
    ),
    array(
        'eventname' => '\local_exams\event\exam_attemptupdated',
        'callback' => '\tool_product\observer\examattempt_observer::update',
    ),
    array(
        'eventname' => '\tool_product\event\trainee_wallet',
        'callback' => '\tool_product\observer\traineewallet_observer::create',
    ),
);
