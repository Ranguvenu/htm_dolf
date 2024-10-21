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
 * TODO describe file apistructure
 *
 * @package    local_exams
 * @copyright  2023 YOUR NAME <your@email.com>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

require('../../config.php');

require_login();
$structure = required_param('structure', PARAM_RAW);
$url = new moodle_url('/local/exams/apistructure.php', ['structure' => $structure]);
$PAGE->set_url($url);
$PAGE->set_context(context_system::instance());
$PAGE->set_heading(get_string('apistructure','local_exams'));

echo $OUTPUT->header();
echo html_writer::tag('a',get_string('back','local_userapproval'),array('href'=>$CFG->wwwroot. '/local/exams/fastsettings.php','class'=>"btn btn-secondary ml-2 float-right")).'<br><br>';

$host = get_config('local_exams', 'fastapihosturl');
$replacestatus = (new \local_exams\local\exams)->access_fast_service('replacefast');



switch($structure){
    case 'generatetoken':
        if ($replacestatus) {
            $replaceuserregistrationurl = (new \local_exams\local\exams)->access_fast_service('replacegeneratetoken');
            if ($replacestatus) {
                $rurl = $replaceuserregistrationurl;
            }
        }
        $url = $host.$rurl;

        echo get_string('rtokenreqparams','local_exams', $url);
        echo '<br/>';
        echo get_string('rtokenexpectedparams','local_exams');
        break;
    case 'userregistration':
        $userhost = get_config('local_userapproval', 'userlogapiauthenticateurl');
        if ($replacestatus) {
            $replaceuserregistrationurl = (new \local_exams\local\exams)->access_fast_service('replaceuserregistration');
            if ($replacestatus) {
                $rurl = $replaceuserregistrationurl;
            }
        }
        $url = $userhost.$rurl;

        echo get_string('requiredparams','local_exams', $url);
        echo '<br/>';
        echo get_string('expectedparams','local_exams');
        break;
    case 'getfaschedules':
        if ($replacestatus) {
            $replaceuserregistrationurl = (new \local_exams\local\exams)->access_fast_service('replacegetfaschedules');
            if ($replacestatus) {
                $rurl = $replaceuserregistrationurl;
            }
        }
        $url = $host.$rurl;

        echo get_string('rrequiredparams','local_exams', $url);
        echo '<br/>';
        echo get_string('rexpectedparams','local_exams');
        break;
    case 'examreservation':
        if ($replacestatus) {
            $replaceuserregistrationurl = (new \local_exams\local\exams)->access_fast_service('replaceexamreservation');
            if ($replacestatus) {
                $rurl = $replaceuserregistrationurl;
            }
        }
        $url = $host.$rurl;

        echo get_string('rrequiredparams','local_exams', $url);
        echo '<br/>';
        echo get_string('rexpectedparams','local_exams');
        break;
    case 'hallavailability':
        if ($replacestatus) {
            $replaceuserregistrationurl = (new \local_exams\local\exams)->access_fast_service('replacehallavailability');
            if ($replacestatus) {
                $rurl = $replaceuserregistrationurl;
            }
        }
        $url = $host.$rurl;

        echo get_string('rrequiredparams','local_exams', $url);
        echo '<br/>';
        echo get_string('rexpectedparams','local_exams');
        break;
    case 'rescheduleservice':
        if ($replacestatus) {
            $replaceuserregistrationurl = (new \local_exams\local\exams)->access_fast_service('replacerescheduleservice');
            if ($replacestatus) {
                $rurl = $replaceuserregistrationurl;
            }
        }
        $url = $host.$rurl;

        echo get_string('rrequiredparams','local_exams', $url);
        echo '<br/>';
        echo get_string('rexpectedparams','local_exams');
        break;
    case 'cancelservice':
        if ($replacestatus) {
            $replaceuserregistrationurl = (new \local_exams\local\exams)->access_fast_service('replacecancelservice');
            if ($replacestatus) {
                $rurl = $replaceuserregistrationurl;
            }
        }
        $url = $host.$rurl;

        echo get_string('rrequiredparams','local_exams', $url);
        echo '<br/>';
        echo get_string('rexpectedparams','local_exams');
        break;
    case 'replaceservice':
        if ($replacestatus) {
            $replaceuserregistrationurl = (new \local_exams\local\exams)->access_fast_service('replacereplaceservice');
            if ($replacestatus) {
                $rurl = $replaceuserregistrationurl;
            }
        }
        $url = $host.$rurl;

        echo get_string('rrequiredparams','local_exams', $url);
        echo '<br/>';
        echo get_string('rexpectedparams','local_exams');
        break;

}

echo $OUTPUT->footer();
