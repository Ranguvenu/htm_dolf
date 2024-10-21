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


//// List of observers.
$observers = array(
    array(
        'eventname'   => '\core\event\course_module_completion_updated',
        'callback'    => 'local_trainingprogram_observer::trainingprogram_exams_completion_updated',
    ),
    array(
        'eventname'   => '\mod_attendance\event\attendance_taken',
        'callback'    => 'local_trainingprogram_observer::trainingprogram_attendance_taken',
    ),

    array(
        'eventname'   => '\core\event\role_assigned',
        'callback'    => 'local_trainingprogram_observer::assign_trainingofficla_as_manager_to_program_category',
    ),
    array(
        'eventname'   => '\core\event\role_unassigned',
        'callback'    => 'local_trainingprogram_observer::unassign_trainingofficla_as_manager_to_program_category',
    ),

    array(
        'eventname'   => '\core\event\course_deleted',
        'callback'    => 'local_trainingprogram_observer::delete_program_based_on_course_deleted',
    ),

    array(
        'eventname'   => '\core\event\role_assigned',
        'callback'    => 'local_trainingprogram_observer::restrict_user_to_be_enrolled_multiple_roles',
    ),
    array(
        'eventname'   => '\core\event\course_completed',
        'callback'    => 'local_trainingprogram_observer::trainingprogram_course_completion_updated',
    ),
    array(
        'eventname'   => '\local_trainingprogram\event\orgoff_refundlogs_created',
        'callback'    => 'local_trainingprogram_observer::orgoff_refundlogs',
    ),
    array(
        'eventname'   => '\core\event\course_module_deleted',
        'callback'    => 'local_trainingprogram_observer::trainingprogram_activity_deletion_update',
    ),      
    [
        'eventname' => '\core\event\course_module_created',
        'callback'  => 'local_trainingprogram_observer::create_module_observer',
    ],
    [
        'eventname' => '\core\event\course_module_updated',
        'callback'  => 'local_trainingprogram_observer::update_module_observer',
    ],
);
