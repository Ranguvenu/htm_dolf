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
 * Defines message providers (types of messages being sent)
 *
 * @package    local_exams
 * @copyright  e abyas  <info@eabyas.com>
 */
$messageproviders = array(
    'exams_create' => array(
        'defaults' => [
            'popup' => MESSAGE_PERMITTED + MESSAGE_DEFAULT_ENABLED ,
           
        ],
    ),
    'exams_update' => array(
        'defaults' => [
            'popup' => MESSAGE_PERMITTED +MESSAGE_DEFAULT_ENABLED ,
          
        ],
    ),
    'exams_enrolment' => array(
        'defaults' => [
            'popup' => MESSAGE_PERMITTED + MESSAGE_DEFAULT_ENABLED ,
            'email' => MESSAGE_PERMITTED + MESSAGE_DEFAULT_ENABLED ,
        ],
    ),
    'exams_completion' => array(
        'defaults' => [
            'popup' => MESSAGE_PERMITTED +MESSAGE_DEFAULT_ENABLED ,
            'email' => MESSAGE_PERMITTED +MESSAGE_DEFAULT_ENABLED ,
        ],
    ),
    'exams_certificate_assignment' => array(
        'defaults' => [
            'popup' => MESSAGE_PERMITTED + MESSAGE_DEFAULT_ENABLED ,
            'email' => MESSAGE_PERMITTED + MESSAGE_DEFAULT_ENABLED ,
        ],
    ),
    'exams_before_7_days' => array(
        'defaults' => [
            'popup' => MESSAGE_PERMITTED + MESSAGE_DEFAULT_ENABLED,
            'email' => MESSAGE_PERMITTED + MESSAGE_DEFAULT_ENABLED,
        ],
    ),
    'exams_before_48_hours' => array(
        'defaults' => [
            'popup' => MESSAGE_PERMITTED + MESSAGE_DEFAULT_ENABLED,
            'email' => MESSAGE_PERMITTED + MESSAGE_DEFAULT_ENABLED,
        ],
    ),
    'exams_before_24_hours' => array(
        'defaults' => [
            'popup' => MESSAGE_PERMITTED + MESSAGE_DEFAULT_ENABLED,
            'email' => MESSAGE_PERMITTED + MESSAGE_DEFAULT_ENABLED,
        ],
    ),
    'exams_send_conclusion' => array(
        'defaults' => [
            'popup' => MESSAGE_PERMITTED + MESSAGE_DEFAULT_ENABLED,
            'email' => MESSAGE_PERMITTED + MESSAGE_DEFAULT_ENABLED,
        ],
    ),
    'exams_after_session' => array(
        'defaults' => [
            'popup' => MESSAGE_PERMITTED + MESSAGE_DEFAULT_ENABLED,
            'email' => MESSAGE_PERMITTED + MESSAGE_DEFAULT_ENABLED,
        ],
    ),
    'exams_payment' => array(
        'defaults' => [
            'popup' => MESSAGE_PERMITTED + MESSAGE_DEFAULT_ENABLED,
            'email' => MESSAGE_PERMITTED + MESSAGE_DEFAULT_ENABLED,
        ],
    ),
    'exams_pre_post_payment' => array(
        'defaults' => [
            'popup' => MESSAGE_PERMITTED + MESSAGE_DEFAULT_ENABLED,
            'email' => MESSAGE_PERMITTED + MESSAGE_DEFAULT_ENABLED,
        ],
    ),
    'exam_unenroll' => array(
        'defaults' => [
            'popup' => MESSAGE_PERMITTED + MESSAGE_DEFAULT_ENABLED ,
            'email' => MESSAGE_PERMITTED + MESSAGE_DEFAULT_ENABLED ,
        ],
    ),
    'exam_reschedule' => array(
        'defaults' => [
            'popup' => MESSAGE_PERMITTED + MESSAGE_DEFAULT_ENABLED ,
            'email' => MESSAGE_PERMITTED + MESSAGE_DEFAULT_ENABLED ,
        ],
    ),
    'cisi_booking_failed' => array(
        'defaults' => [
            'popup' => MESSAGE_PERMITTED + MESSAGE_DEFAULT_ENABLED ,
            'email' => MESSAGE_PERMITTED + MESSAGE_DEFAULT_ENABLED ,
        ],
    ),   
    'other_exam_enrollment' => array(
        'defaults' => [
            'popup' => MESSAGE_PERMITTED + MESSAGE_DEFAULT_ENABLED ,
            'email' => MESSAGE_PERMITTED + MESSAGE_DEFAULT_ENABLED ,
        ],
    ),
    'exam_service_provider' => array(
        'defaults' => [
            'popup' => MESSAGE_PERMITTED + MESSAGE_DEFAULT_ENABLED ,
            'email' => MESSAGE_PERMITTED + MESSAGE_DEFAULT_ENABLED ,
        ],
    ),
    'other_exam_enrollment' => array(
        'defaults' => [
            'popup' => MESSAGE_PERMITTED + MESSAGE_DEFAULT_ENABLED ,
            'email' => MESSAGE_PERMITTED + MESSAGE_DEFAULT_ENABLED ,
        ],
    ),
    'exam_service_provider' => array(
        'defaults' => [
            'popup' => MESSAGE_PERMITTED + MESSAGE_DEFAULT_ENABLED ,
            'email' => MESSAGE_PERMITTED + MESSAGE_DEFAULT_ENABLED ,
        ],
    ),
    'exam_result_objection' => array(
        'defaults' => [
            'popup' => MESSAGE_PERMITTED + MESSAGE_DEFAULT_ENABLED ,
            'email' => MESSAGE_PERMITTED + MESSAGE_DEFAULT_ENABLED ,
        ],
    ),
    'bulkenrol_exam' => array(
        'defaults' => [
            'popup' => MESSAGE_PERMITTED + MESSAGE_DEFAULT_ENABLED ,
            'email' => MESSAGE_PERMITTED + MESSAGE_DEFAULT_ENABLED ,
        ],
    ),


);