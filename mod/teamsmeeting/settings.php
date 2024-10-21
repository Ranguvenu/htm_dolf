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
 * Teams Meeting Settings form
 *
 * @package    mod_teamsmeeting
 * @copyright  2022 eAbyas Info Solutions Pvt Ltd (www.eabyas.com)
 * @author     Ranga Reddy<rangareddy@eabyas.com>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die;

if ($ADMIN->fulltree) {

    $settings = new admin_settingpage('modsettingteamsmeeting', get_string('pluginname', 'mod_teamsmeeting'));

    $tenantid = new admin_setting_configtext('mod_teamsmeeting/tenantid', get_string('tenantid', 'mod_teamsmeeting'),
            get_string('tenantid_desc', 'mod_teamsmeeting'), '', PARAM_ALPHANUMEXT);
    $settings->add($tenantid);

    $clientid = new admin_setting_configtext('mod_teamsmeeting/clientid', get_string('clientid', 'mod_teamsmeeting'),
    get_string('clientid_desc', 'mod_teamsmeeting'), '', PARAM_ALPHANUMEXT);
    $settings->add($clientid);

    $clientid = new admin_setting_configtext('mod_teamsmeeting/clientid', get_string('clientid', 'mod_teamsmeeting'),
    get_string('clientid_desc', 'mod_teamsmeeting'), '', PARAM_RAW);
    $settings->add($clientid);

    $clientsecret = new admin_setting_configtext('mod_teamsmeeting/clientsecret', get_string('clientsecret', 'mod_teamsmeeting'),
    get_string('clientsecret_desc', 'mod_teamsmeeting'), '', PARAM_RAW);
    $settings->add($clientsecret);

    $userid = new admin_setting_configtext('mod_teamsmeeting/userid', get_string('userid', 'mod_teamsmeeting'),
    get_string('userid_desc', 'mod_teamsmeeting'), '', PARAM_ALPHANUMEXT);
    $settings->add($userid);

    $settings->add(new admin_setting_configtext('mod_teamsmeeting/concurrentmeeting', get_string('concurrent_meetings', 'mod_teamsmeeting'),
    get_string('host_concurrent_meetings', 'mod_teamsmeeting'), '', PARAM_INT));


}
