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
 * Webservices for auth_wslogin.
 *
 * @package    auth_wslogin
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die();

require_once($CFG->libdir . "/externallib.php");
require_once($CFG->dirroot . "/webservice/lib.php");
require_once($CFG->dirroot . "/auth/wslogin/auth.php");

class auth_wslogin_external extends external_api {

    /**
     * Return request_login_url webservice parameters.
     *
     * @return \external_function_parameters
     */
    public static function request_login_url_parameters() {
        return new external_function_parameters(
            array(
                'user' => new external_single_structure(
                    get_auth_plugin('wslogin')->get_request_login_url_user_parameters()
                )
            )
        );
    }

    /**
     * Return login url array.
     *
     * @param array $user
     *
     * @return array
     * @throws \dml_exception
     * @throws \required_capability_exception
     * @throws \webservice_access_exception
     */
    public static function request_login_url($user) {

        if (!is_enabled_auth('wslogin')) {
            throw new webservice_access_exception(get_string('pluginisdisabled', 'auth_wslogin'));
        }

        $context = context_system::instance();
        require_capability('auth/wslogin:generatekey', $context);

        $auth = get_auth_plugin('wslogin');
        $loginurl = $auth->get_login_url($user);

        return array(
            'loginurl' => $loginurl,
        );
    }

    /**
     * Describe request_login_url webservice return structure.
     *
     * @return \external_single_structure
     */
    public static function request_login_url_returns() {
        return new external_single_structure(
            array(
                'loginurl' => new external_value(PARAM_RAW, 'Login URL for a user to log in'),
            )
        );
    }

}
