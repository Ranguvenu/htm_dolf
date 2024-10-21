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
 * Strings for auth_userkey.
 * @package    auth_wslogin
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
defined('MOODLE_INTERNAL') || die;

$string['pluginname'] = 'Web service based SSO';
$string['auth_userkeydescription'] = 'Log in to Moodle using web service.';
$string['mappingfield'] = 'Mapping field';
$string['mappingfield_desc'] = 'This user field will be used to find relevant user in the LMS.';
$string['keylifetime'] = 'User key life time';
$string['keylifetime_desc'] = 'Life time in seconds of the each user login key.';
$string['incorrectkeylifetime'] = 'User key life time should be a number';
$string['createuser'] = 'Create user?';
$string['createuser_desc'] = 'If enabled, a new user will be created if fail to find one in LMS.';
$string['updateuser'] = 'Update user?';
$string['updateuser_desc'] = 'If enabled, users will be updated with the properties supplied when the webservice is called.';
$string['wslogin:generatekey'] = 'Generate login user key';
$string['pluginisdisabled'] = 'The wslogin authentication plugin is disabled.';
$string['redirecterrordetected'] = 'Unsupported redirect to {$a} detected, execution terminated.';
$string['noip'] = 'Unable to fetch IP address of client.';
$string['privacy:metadata'] = 'User key authentication plugin does not store any personal data.';
$string['incorrectlogout'] = 'Incorrect logout request';
