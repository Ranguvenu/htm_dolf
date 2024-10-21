<?php
/**
 * This file is part of eAbyas
 *
 * Copyright eAbyas Info Solutons Pvt Ltd, India
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
 * @author eabyas  <info@eabyas.in>
 * @subpackage local_events
 */
defined('MOODLE_INTERNAL') || die;
$services = array(
    'Event Listing' => array(                                                // the name of the web service
        'functions' => array ('local_events_listing'), // web service functions of this service
        'requiredcapability' => '',                // if set, the web service user need this capability to access 
                                                                        // any function of this service. For example: 'some/capability:specified'                 
        'restrictedusers' => 0,                                             // if enabled, the Moodle administrator must link some user to this service                                                                 // into the administration
        'enabled' => 1,                                                       // if enabled, the service can be reachable on a default installation
        'shortname' =>  'eventlisting',       // optional – but needed if restrictedusers is set so as to allow logins.
        'downloadfiles' => 0,    // allow file downloads.
        'uploadfiles'  => 0      // allow file uploads.
    ),
    'Event Listing Details' => array(                                                // the name of the web service
        'functions' => array ('local_events_listing_details'), // web service functions of this service
        'requiredcapability' => '',                // if set, the web service user need this capability to access 
                                                                        // any function of this service. For example: 'some/capability:specified'                 
        'restrictedusers' => 0,                                             // if enabled, the Moodle administrator must link some user to this service                                                                 // into the administration
        'enabled' => 1,                                                       // if enabled, the service can be reachable on a default installation
        'shortname' =>  'eventlistingdetails',       // optional – but needed if restrictedusers is set so as to allow logins.
        'downloadfiles' => 0,    // allow file downloads.
        'uploadfiles'  => 0      // allow file uploads.
    )
);
$functions = array(
    'local_events_listing' => array(
        'classname' => 'local_events_external',
        'methodname' => 'events_listing',
        'classpath' => 'local/events/classes/external.php',
        'description' => 'List of events',
        'services'      => array(MOODLE_OFFICIAL_MOBILE_SERVICE),
        'ajax' => true,
        'type' => 'read'
    ),
    'local_events_listing_details' => array(
        'classname' => 'local_events_external',
        'methodname' => 'event_listing_details',
        'classpath' => 'local/events/classes/external.php',
        'description' => 'event_listing_details',
        'services'      => array(MOODLE_OFFICIAL_MOBILE_SERVICE),
        'ajax' => true,
        'type' => 'read'
    ),
    
    'local_events_deleteevent' => array(
        'classname' => 'local_events_external',
        'methodname' => 'deleteevent',
        'classpath' => 'local/events/classes/external.php',
        'description' => 'delete event',
        'ajax' => true,
        'type' => 'write'
    ),

    'local_events_view_events' => array(
        'classname' =>  'local_events_external',
        'methodname' => 'view_events',
        'classpath' => 'local/events/classes/external.php',
        'description' => 'View Events',
        'services'      => array(MOODLE_OFFICIAL_MOBILE_SERVICE),
        'ajax' => true,
        'type' => 'read'
    ),

    'local_events_agenda' => array(
        'classname' =>  'local_events_external',
        'methodname' => 'agenda_list',
        'classpath' => 'local/events/classes/external.php',
        'description' => 'View Agenda',
        'ajax' => true,
        'type' => 'read'
    ),

    'local_events_view_agenda' => array(
        'classname' =>  'local_events_external',
        'methodname' => 'view_agenda',
        'classpath' => 'local/events/classes/external.php',
        'description' => 'View Agenda',
        'ajax' => true,
        'type' => 'read'
    ),


    'local_events_delete_agenda' => array(
        'classname' =>  'local_events_external',
        'methodname' => 'delete_agenda',
        'classpath' => 'local/events/classes/external.php',
        'description' => 'Delete Agenda',
        'ajax' => true,
        'type' => 'write'
    ),

    'local_events_attendees_list' => array(
        'classname' =>  'local_events_external',
        'methodname' => 'attendees_list',
        'classpath' => 'local/events/classes/external.php',
        'description' => 'View Attesndees',
        'ajax' => true,
        'type' => 'read'
    ),

    'local_events_view_attendee' => array(
        'classname' =>  'local_events_external',
        'methodname' => 'view_attendee',
        'classpath' => 'local/events/classes/external.php',
        'description' => 'View Attesndees',
        'ajax' => true,
        'type' => 'read'
    ),

    'local_events_delete_attendees' => array(
        'classname' =>  'local_events_external',
        'methodname' => 'delete_attendees',
        'classpath' => 'local/events/classes/external.php',
        'description' => 'Delete Attesndees',
        'ajax' => true,
        'type' => 'write'
    ),

    'local_events_speakers_list' => array(
        'classname' =>  'local_events_external',
        'methodname' => 'speakers_list',
        'classpath' => 'local/events/classes/external.php',
        'description' => 'View Speakers',
        'ajax' => true,
        'type' => 'read'
    ), 
    'local_events_delete_speaker' => array(
        'classname' =>  'local_events_external',
        'methodname' => 'delete_speaker',
        'classpath' => 'local/events/classes/external.php',
        'description' => 'Delete Speaker',
        'ajax' => true,
        'type' => 'write'
    ),

    'local_events_view_speaker' => array(
        'classname' =>  'local_events_external',
        'methodname' => 'view_speaker',
        'classpath' => 'local/events/classes/external.php',
        'description' => 'View Speaker',
        'ajax' => true,
        'type' => 'read'
    ),

    'local_events_delete_sponsor' => array(
        'classname' =>  'local_events_external',
        'methodname' => 'delete_sponsor',
        'classpath' => 'local/events/classes/external.php',
        'description' => 'Delete Sponsor',
        'ajax' => true,
        'type' => 'write'
    ),

    'local_events_sponsors_list' => array(
        'classname' =>  'local_events_external',
        'methodname' => 'sponsors_list',
        'classpath' => 'local/events/classes/external.php',
        'description' => 'View Sponsors',
        'ajax' => true,
        'type' => 'read'
    ),

    'local_events_view_sponsor' => array(
        'classname' =>  'local_events_external',
        'methodname' => 'view_sponsor',
        'classpath' => 'local/events/classes/external.php',
        'description' => 'View Sponsors',
        'ajax' => true,
        'type' => 'read',
        'loginrequired' => false,
    ),

    'local_events_partners_list' => array(
        'classname' =>  'local_events_external',
        'methodname' => 'partners_list',
        'classpath' => 'local/events/classes/external.php',
        'description' => 'View Event Partners',
        'ajax' => true,
        'type' => 'read'
    ),

    'local_events_view_partner' => array(
        'classname' =>  'local_events_external',
        'methodname' => 'view_partner',
        'classpath' => 'local/events/classes/external.php',
        'description' => 'View Event Partners',
        'ajax' => true,
        'type' => 'read',
        'loginrequired' => false,
    ),
    
    'local_events_delete_partner' => array(
        'classname' =>  'local_events_external',
        'methodname' => 'delete_partner',
        'classpath' => 'local/events/classes/external.php',
        'description' => 'Delete event_partners',
        'ajax' => true,
        'type' => 'write'
    ),

    'local_events_form_selector' => array(
        'classname' =>  'local_events_external',
        'methodname' => 'form_selector',
        'classpath' => 'local/events/classes/external.php',
        'description' => 'form_selector',
        'ajax' => true,
        'type' => 'read'
    ),

    'local_events_speaker_formdata' => array(
        'classname' =>  'local_events_external',
        'methodname' => 'speaker_formdata',
        'classpath' => 'local/events/classes/external.php',
        'description' => 'speaker_formdata',
        'ajax' => true,
        'type' => 'read'
    ),

    'local_events_eventincome' => array(
        'classname' =>  'local_events_external',
        'methodname' => 'eventincome',
        'classpath' => 'local/events/classes/external.php',
        'description' => 'eventincome',
        'ajax' => true,
        'type' => 'read'
    ),
    'local_events_eventexpenses' => array(
        'classname' =>  'local_events_external',
        'methodname' => 'eventexpenses',
        'classpath' => 'local/events/classes/external.php',
        'description' => 'eventexpenses',
        'ajax' => true,
        'type' => 'read'
    ),
    
    /*'local_events_totalestimated' => array(
        'classname' =>  'local_events_external',
        'methodname' => 'total_estimated',
        'classpath' => 'local/events/classes/external.php',
        'description' => 'total_estimated',
        'ajax' => true,
        'type' => 'read'
    ),*/

    /*'local_events_actualrevenue' => array(
        'classname' =>  'local_events_external',
        'methodname' => 'actual_revenue',
        'classpath' => 'local/events/classes/external.php',
        'description' => 'actual_revenue',
        'ajax' => true,
        'type' => 'read'
    ),*/
    'local_events_viewevents' => array(
        'classname' =>  'local_events_external',
        'methodname' => 'viewevents',
        'classpath' => 'local/events/classes/external.php',
        'description' => 'View Events Before login',
        'services'=> array(MOODLE_OFFICIAL_MOBILE_SERVICE),
        'ajax' => true,
        'type' => 'read',
        'loginrequired' => false,
    ),
    'local_events_financeamount' => array(
        'classname' =>  'local_events_external',
        'methodname' => 'financeamount',
        'classpath' => 'local/events/classes/external.php',
        'description' => 'financeamount',
        'ajax' => true,
        'type' => 'read',
    ),
);
