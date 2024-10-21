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
    'registration' => array(
        'defaults' => [
            'popup' => MESSAGE_PERMITTED + MESSAGE_DEFAULT_ENABLED ,
            'email' => MESSAGE_PERMITTED + MESSAGE_DEFAULT_ENABLED ,
        ],
    ),
    'approve' => array(
        'defaults' => [
            'popup' => MESSAGE_PERMITTED + MESSAGE_DEFAULT_ENABLED ,
            'email' => MESSAGE_PERMITTED + MESSAGE_DEFAULT_ENABLED ,
        ],
    ),
    'reject' => array(
        'defaults' => [
            'popup' => MESSAGE_PERMITTED + MESSAGE_DEFAULT_ENABLED ,
            'email' => MESSAGE_PERMITTED +MESSAGE_DEFAULT_ENABLED ,
        ],
    ),
    'organizational_approval' => array(
        'defaults' => [
            'popup' => MESSAGE_PERMITTED + MESSAGE_DEFAULT_ENABLED ,
            'email' => MESSAGE_PERMITTED +MESSAGE_DEFAULT_ENABLED ,
        ],
    ),
    'individual_registration' => array(
        'defaults' => [
            'popup' => MESSAGE_PERMITTED + MESSAGE_DEFAULT_ENABLED ,
            'email' => MESSAGE_PERMITTED +MESSAGE_DEFAULT_ENABLED ,
        ],
    ),


  
    
    
);