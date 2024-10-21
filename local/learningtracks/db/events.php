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
        'eventname'   => '\local_learningtracks\event\learning_track_deleted',
        'callback'    => 'local_learningtracks_observer::lerningtrack_dependency_deleted',
    ),

    array(
        'eventname'   => '\local_exams\event\exam_completion_updated',
        'callback'    => 'local_learningtracks_observer::trainingprogram_exam_completion',
    ),

    array(
        'eventname'   => '\local_trainingprogram\event\trainingprogram_completion_updated',
        'callback'    => 'local_learningtracks_observer::trainingprogram_program_completion',
    ),
);
