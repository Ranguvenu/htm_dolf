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
 *
 * @package    auth_iam
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
namespace auth_iam;
defined('MOODLE_INTERNAL') || die;

use curl;
/**
 * Facademy service API
 */
class fa{
    public function get_userinfo() {
        global $CFG;
        require_once($CFG->libdir.'/filelib.php');
        $Identity = $guid = false;
        if ($_SERVER["REQUEST_METHOD"] == "GET") {
            // collect value of input field
            $guid = $_REQUEST['guid'];
            $Identity = $_REQUEST['Identity'];
            if (empty($guid)) {
                // print_error('Missing GUID');
            }
            if (empty($Identity)) {
                // print_error("Identity is empty");
            }
        }
        if($guid && $Identity){
            $c = new curl();
            $options = array(
                'httpheader' => array('application/json'),
                // 'timeout' => 30,
                'CURLOPT_RETURNTRANSFER' => true/*,
                'CURLOPT_HTTP_VERSION' => CURL_HTTP_VERSION_1_1,
                'CURLOPT_HTTPHEADER' => array('Authorization:Bearer '. $this->token)*/
            );
            $config = get_config('auth_iam');
            // var_dump($config->request_url);
            $location = $config->request_url."/$guid/$Identity";
            $params = array();
            $result = $c->get($location, $params, $options);
            if ($c->get_errno()) {
                return false;
            }

            $jResult = json_decode($result);
            if(isset($jResult->error)) {
                return false;
            }
            if(!empty($jResult)){
                 return $jResult;
            } else {
                return false;
            }
        }
        return false;
    }
}