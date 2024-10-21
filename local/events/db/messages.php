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
 * @package    local_events
 * @copyright  e abyas  <info@eabyas.com>
 */
$messageproviders = array(
    'events_create' => array(
        'defaults' => [
            'popup' => MESSAGE_PERMITTED +MESSAGE_DEFAULT_ENABLED ,

        ],
    ),
    'events_update' => array(
        'defaults' => [
            'popup' => MESSAGE_PERMITTED + MESSAGE_DEFAULT_ENABLED ,
    
        ],
    ),
    'events_registration' => array(
        'defaults' => [
            'popup' => MESSAGE_PERMITTED + MESSAGE_DEFAULT_ENABLED ,
            'email' => MESSAGE_PERMITTED + MESSAGE_DEFAULT_ENABLED ,
        ],
    ),
    'events_speakers' => array(
        'defaults' => [
            'popup' => MESSAGE_PERMITTED +MESSAGE_DEFAULT_ENABLED ,
            'email' => MESSAGE_PERMITTED + MESSAGE_DEFAULT_ENABLED ,
        ],
    ),
   
    'events_sponsors' => array(
        'defaults' => [
            'popup' => MESSAGE_PERMITTED +MESSAGE_DEFAULT_ENABLED ,
            'email' => MESSAGE_PERMITTED +MESSAGE_DEFAULT_ENABLED ,
        ],
    ),
    'events_partners' => array(
        'defaults' => [
            'popup' => MESSAGE_PERMITTED + MESSAGE_DEFAULT_ENABLED ,
            'email' => MESSAGE_PERMITTED +MESSAGE_DEFAULT_ENABLED ,
        ],
    ),
    'events_completion' => array(
        'defaults' => [
            'popup' => MESSAGE_PERMITTED +MESSAGE_DEFAULT_ENABLED ,
            'email' => MESSAGE_PERMITTED + MESSAGE_DEFAULT_ENABLED ,
        ],
    ),
    'events_before_7_days' => array(
        'defaults' => [
            'popup' => MESSAGE_PERMITTED + MESSAGE_DEFAULT_ENABLED,
            'email' => MESSAGE_PERMITTED + MESSAGE_DEFAULT_ENABLED,
        ],
    ),
    'events_before_48_hours' => array(
        'defaults' => [
            'popup' => MESSAGE_PERMITTED + MESSAGE_DEFAULT_ENABLED,
            'email' => MESSAGE_PERMITTED + MESSAGE_DEFAULT_ENABLED,
        ],
    ),
    'events_before_24_hours' => array(
        'defaults' => [
            'popup' => MESSAGE_PERMITTED + MESSAGE_DEFAULT_ENABLED,
            'email' => MESSAGE_PERMITTED + MESSAGE_DEFAULT_ENABLED,
        ],
    ),
    'events_send_conclusion' => array(
        'defaults' => [
            'popup' => MESSAGE_PERMITTED + MESSAGE_DEFAULT_ENABLED,
            'email' => MESSAGE_PERMITTED + MESSAGE_DEFAULT_ENABLED,
        ],
    ),
    'events_after_session' => array(
        'defaults' => [
            'popup' => MESSAGE_PERMITTED + MESSAGE_DEFAULT_ENABLED,
            'email' => MESSAGE_PERMITTED + MESSAGE_DEFAULT_ENABLED,
        ],
    ),
    'events_onchange' => array(
        'defaults' => [
            'popup' => MESSAGE_PERMITTED + MESSAGE_DEFAULT_ENABLED,
            'email' => MESSAGE_PERMITTED + MESSAGE_DEFAULT_ENABLED,
        ],
    ),
    'events_cancel' => array(
        'defaults' => [
            'popup' => MESSAGE_PERMITTED + MESSAGE_DEFAULT_ENABLED,
            'email' => MESSAGE_PERMITTED + MESSAGE_DEFAULT_ENABLED,
        ],
    ),
    'events_reschedule' => array(
        'defaults' => [
            'popup' => MESSAGE_PERMITTED + MESSAGE_DEFAULT_ENABLED,
            'email' => MESSAGE_PERMITTED + MESSAGE_DEFAULT_ENABLED,
        ],
    ),
    'event_unregister' => array(
        'defaults' => [
            'popup' => MESSAGE_PERMITTED + MESSAGE_DEFAULT_ENABLED ,
            'email' => MESSAGE_PERMITTED + MESSAGE_DEFAULT_ENABLED ,
        ],
    ),
);