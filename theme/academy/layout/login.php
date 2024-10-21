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

defined('MOODLE_INTERNAL') || die();

/**
 * A login page layout for the academy theme.
 *
 * @package   theme_academy
 * @copyright 2016 Damyon Wiese
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

if (isloggedin()) {
    $navdraweropen = false; //(get_user_preferences('drawer-open-nav', 'true') == 'true');
} else {
    $navdraweropen = false;
}

$is_loggedin = isloggedin();
$is_loggedin = empty($is_loggedin) ? false : true;

$bodyattributes = $OUTPUT->body_attributes();
$templatecontext = [
    'sitename' => format_string($SITE->shortname, true, ['context' => context_course::instance(SITEID), "escape" => false]),
    'output' => $OUTPUT,
    'notice' => get_string('notifythemessage','theme_academy'),
    'url' => $CFG->wwwroot.'/theme/academy/help.php',
    'bodyattributes' => $bodyattributes,
    'iamregistrationurl' => get_auth_plugin('iam')->get_login_url(),
];
echo $OUTPUT->render_from_template('theme_academy/newlayoutdesigns/login', $templatecontext);

