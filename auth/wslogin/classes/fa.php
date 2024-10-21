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
 * @package    auth_wslogin
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
namespace auth_wslogin;

require_once($CFG->libdir.'/filelib.php');

defined('MOODLE_INTERNAL') || die;

use curl;
/**
 * Facademy service API
 */
class fa
{
    private $username;

    private $password;

    private $token;

    private static $domain = 'eservices.fa.gov.sa';

    public function __construct($username, $password) {
        $this->username = $username;
        $this->password = $password;

        $this->get_authkey();
    }

    private function get_authkey() {
        $c = new curl();
      
        $options = array(
            'CURLOPT_HTTPHEADER' => array('Content-Type: application/json'),
            'timeout' => 30,
            'CURLOPT_HTTP_VERSION' => CURL_HTTP_VERSION_1_1,
        );
        $location = "https://".self::$domain."/api/MobileAuthenticate/Login?isArabic=false";
        $params = json_encode([
            'username'    => $this->username,
            'password'     => $this->password,
        ]);

        $result = $c->post($location, $params, $options);

        if ($c->get_errno()) {
            throw new moodle_exception('errtelr', 'tool_product_telr', '', null, $result);
        }

        $jResult = json_decode($result);
        if(isset($jResult->error)) {
            throw new moodle_exception('errtelr', 'tool_product_telr', '', null, $result);
        }
       
        $this->token = $jResult->token;
    }

    public function get_userinfo() {
        if($this->token == ''){
            return false;
        }
        $c = new curl();
   
        $options = array(
            'httpheader' => array('application/json'),
            'timeout' => 30,
            'CURLOPT_HTTP_VERSION' => CURL_HTTP_VERSION_1_1,
            'CURLOPT_HTTPHEADER' => array('Authorization:Bearer '. $this->token)
        );
        $location = "https://".self::$domain."/api/Mobileaccount/GetUserInfo?isArabic=true";
        $params = array();
        $result = $c->get($location, $params, $options);
        if ($c->get_errno()) {
            return false;
        }

        $jResult = json_decode($result);
       
        if(isset($jResult->error)) {
            return false;
        }
        if($jResult->success){
             return $jResult->data;
         } else {
            return false;
         }
    }
}