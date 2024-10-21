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
 * @package    local_competency
 * @copyright  e abyas  <info@eabyas.com>
 */
$messageproviders = array(
    'trainingprogram_create' => array(
        'defaults' => [
            'popup' => MESSAGE_PERMITTED + MESSAGE_DEFAULT_ENABLED ,
           
        ],
    ),
    'trainingprogram_update' => array(
        'defaults' => [
            'popup' => MESSAGE_PERMITTED + MESSAGE_DEFAULT_ENABLED ,
       
        ],
    ),
    'trainingprogram_enroll' => array(
        'defaults' => [
            'popup' => MESSAGE_PERMITTED +MESSAGE_DEFAULT_ENABLED ,
            'email' => MESSAGE_PERMITTED + MESSAGE_DEFAULT_ENABLED ,
        ],
    ),
    'trainingprogram_completion' => array(
        'defaults' => [
            'popup' => MESSAGE_PERMITTED + MESSAGE_DEFAULT_ENABLED ,
            'email' => MESSAGE_PERMITTED + MESSAGE_DEFAULT_ENABLED ,
        ],
    ),
    'trainingprogram_certificate_assignment' => array(
        'defaults' => [
            'popup' => MESSAGE_PERMITTED + MESSAGE_DEFAULT_ENABLED ,
            'email' => MESSAGE_PERMITTED + MESSAGE_DEFAULT_ENABLED ,
        ],
    ),
    'trainingprogram_before_7_days' => array(
        'defaults' => [
            'popup' => MESSAGE_PERMITTED + MESSAGE_DEFAULT_ENABLED,
            'email' => MESSAGE_PERMITTED + MESSAGE_DEFAULT_ENABLED,
        ],
    ),
    'trainingprogram_before_48_hours' => array(
        'defaults' => [
            'popup' => MESSAGE_PERMITTED + MESSAGE_DEFAULT_ENABLED,
            'email' => MESSAGE_PERMITTED + MESSAGE_DEFAULT_ENABLED,
        ],
    ),
    'trainingprogram_before_24_hours' => array(
        'defaults' => [
            'popup' => MESSAGE_PERMITTED + MESSAGE_DEFAULT_ENABLED,
            'email' => MESSAGE_PERMITTED + MESSAGE_DEFAULT_ENABLED,
        ],
    ),
    'trainingprogram_before_30_minutes' => array(
        'defaults' => [
            'popup' => MESSAGE_PERMITTED + MESSAGE_DEFAULT_ENABLED,
            'email' => MESSAGE_PERMITTED + MESSAGE_DEFAULT_ENABLED,
        ],
    ),
    'trainingprogram_enrolled_inactive_accounts' => array(
        'defaults' => [
            'popup' => MESSAGE_PERMITTED + MESSAGE_DEFAULT_ENABLED,
            'email' => MESSAGE_PERMITTED + MESSAGE_DEFAULT_ENABLED,
        ],
    ),
    'trainingprogram_pre_assessment_opened' => array(
        'defaults' => [
            'popup' => MESSAGE_PERMITTED + MESSAGE_DEFAULT_ENABLED,
            'email' => MESSAGE_PERMITTED + MESSAGE_DEFAULT_ENABLED,
        ],
    ),
    'trainingprogram_post_assessment_opened' => array(
        'defaults' => [
            'popup' => MESSAGE_PERMITTED + MESSAGE_DEFAULT_ENABLED,
            'email' => MESSAGE_PERMITTED + MESSAGE_DEFAULT_ENABLED,
        ],
    ),
    'trainingprogram_pre_assessment_closed' => array(
        'defaults' => [
            'popup' => MESSAGE_PERMITTED + MESSAGE_DEFAULT_ENABLED,
            'email' => MESSAGE_PERMITTED + MESSAGE_DEFAULT_ENABLED,
        ],
    ),
    'trainingprogram_post_assessment_closed' => array(
        'defaults' => [
            'popup' => MESSAGE_PERMITTED + MESSAGE_DEFAULT_ENABLED,
            'email' => MESSAGE_PERMITTED + MESSAGE_DEFAULT_ENABLED,
        ],
    ),
    'trainingprogram_assignment_deadline_4_hours' => array(
        'defaults' => [
            'popup' => MESSAGE_PERMITTED + MESSAGE_DEFAULT_ENABLED,
            'email' => MESSAGE_PERMITTED + MESSAGE_DEFAULT_ENABLED,
        ],
    ),
    'trainingprogram_assignment_deadline_24_hours' => array(
        'defaults' => [
            'popup' => MESSAGE_PERMITTED + MESSAGE_DEFAULT_ENABLED,
            'email' => MESSAGE_PERMITTED + MESSAGE_DEFAULT_ENABLED,
        ],
    ),
    'trainingprogram_after_session' => array(
        'defaults' => [
            'popup' => MESSAGE_PERMITTED + MESSAGE_DEFAULT_ENABLED,
            'email' => MESSAGE_PERMITTED + MESSAGE_DEFAULT_ENABLED,
        ],
    ),
    'trainingprogram_send_conclusion' => array(
        'defaults' => [
            'popup' => MESSAGE_PERMITTED + MESSAGE_DEFAULT_ENABLED,
            'email' => MESSAGE_PERMITTED + MESSAGE_DEFAULT_ENABLED,
        ],
    ),
    'trainingprogram_unenroll' => array(
        'defaults' => [
            'popup' => MESSAGE_PERMITTED +MESSAGE_DEFAULT_ENABLED ,
            'email' => MESSAGE_PERMITTED + MESSAGE_DEFAULT_ENABLED ,
        ],
    ),

    'trainingprogram_before_10_days' => array(
        'defaults' => [
            'popup' => MESSAGE_PERMITTED +MESSAGE_DEFAULT_ENABLED ,
            'email' => MESSAGE_PERMITTED + MESSAGE_DEFAULT_ENABLED ,
        ],
    ),
    'trainee_tp_enrollment' => array(
        'defaults' => [
            'popup' => MESSAGE_PERMITTED +MESSAGE_DEFAULT_ENABLED ,
            'email' => MESSAGE_PERMITTED + MESSAGE_DEFAULT_ENABLED ,
        ],
    ),
    'bulkenrol_program' => array(
        'defaults' => [
            'popup' => MESSAGE_PERMITTED +MESSAGE_DEFAULT_ENABLED ,
            'email' => MESSAGE_PERMITTED + MESSAGE_DEFAULT_ENABLED ,
        ],
    ),
    'tp_org_traineeenroll' => array(
        'defaults' => [
            'popup' => MESSAGE_PERMITTED +MESSAGE_DEFAULT_ENABLED ,
            'email' => MESSAGE_PERMITTED + MESSAGE_DEFAULT_ENABLED ,
        ],
    ),

    'trainer_tp_enrollment' => array(
        'defaults' => [
            'popup' => MESSAGE_PERMITTED +MESSAGE_DEFAULT_ENABLED ,
            'email' => MESSAGE_PERMITTED + MESSAGE_DEFAULT_ENABLED ,
        ],
    ),
    'trainingprogram_cancelrequest' => array(
        'defaults' => [
            'popup' => MESSAGE_PERMITTED +MESSAGE_DEFAULT_ENABLED ,
            'email' => MESSAGE_PERMITTED + MESSAGE_DEFAULT_ENABLED ,
        ],
    ),
    'trainingprogram_reschedule' => array(
        'defaults' => [
            'popup' => MESSAGE_PERMITTED +MESSAGE_DEFAULT_ENABLED ,
            'email' => MESSAGE_PERMITTED + MESSAGE_DEFAULT_ENABLED ,
        ],
    ),



    
    
);
