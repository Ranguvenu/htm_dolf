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
 * settings page.
 *
 * @package    auth_iam
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die;

if ($ADMIN->fulltree) {
    $yesno = array(get_string('no'), get_string('yes'));
    $fields = get_auth_plugin('iam')->get_allowed_mapping_fields();

    $settings->add(new admin_setting_configselect('auth_iam/mappingfield',
        new lang_string('mappingfield', 'auth_iam'),
        new lang_string('mappingfield_desc', 'auth_iam'), 0, $fields));

    $settings->add(new admin_setting_configtext('auth_iam/request_url', get_string('request_url', 'auth_iam'),
            get_string('request_url_desc', 'auth_iam', 'auth'),
            'https://auth.fa.gov.sa/api/SSO/SSOData', PARAM_RAW));


    $settings->add(new admin_setting_configtext('auth_iam/login_url', get_string('login_url', 'auth_iam'),
            get_string('login_url_desc', 'auth_iam', 'auth'),
            'https://auth.fa.gov.sa/Home/Login', PARAM_RAW));

    // $settings->add(new admin_setting_configtext('auth_iam/keylifetime', get_string('keylifetime', 'auth_iam'),
    //         get_string('keylifetime_desc', 'auth_iam', 'auth'),
    //         '60', PARAM_INT));

    // $settings->add(new admin_setting_configselect('auth_iam/createuser',
    //         new lang_string('createuser', 'auth_iam'),
    //         new lang_string('createuser_desc', 'auth_iam'), 0, $yesno));

    // $settings->add(new admin_setting_configselect('auth_iam/updateuser',
    //         new lang_string('updateuser', 'auth_iam'),
    //         new lang_string('updateuser_desc', 'auth_iam'), 0, $yesno));
}
