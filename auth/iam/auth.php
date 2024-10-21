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
 * User key auth method.
 *
 * @package    auth_iam
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die();

use local_userapproval\action\manageuser;
use auth_iam\fa;

require_once($CFG->libdir . "/externallib.php");
require_once($CFG->libdir.'/authlib.php');
require_once($CFG->dirroot . '/user/lib.php');

/**
 * User key authentication plugin.
 */
class auth_plugin_iam extends auth_plugin_base {

    /**
     * Default mapping field.
     */
    const DEFAULT_MAPPING_FIELD = 'username';



    /**
     * Defaults for config form.
     *
     * @var array
     */
    protected $defaults = array(
        'mappingfield' => self::DEFAULT_MAPPING_FIELD,
        'keylifetime' => 60,
        'iprestriction' => 0,
        'ipwhitelist' => '',
        'redirecturl' => '',
        'ssourl' => '',
        'createuser' => false,
        'updateuser' => false,
    );

    /**
     * Constructor.
     */
    public function __construct() {
        $this->authtype = 'iam';
        $this->config = get_config('auth_iam');
    }

    /**
     * All the checking happens before the login page in this hook.
     *
     * It redirects a user if required or return true.
     */
    public function pre_loginpage_hook() {
        return $this->loginpage_hook();
    }

    /**
     * All the checking happens before the login page in this hook.
     *
     * It redirects a user if required or return true.
     */
    public function loginpage_hook() {
        if ($this->should_login_redirect()) {
            $this->redirect($this->get_login_url());
        }

        return true;
    }

    /**
     * Redirects the user to provided URL.
     *
     * @param $url URL to redirect to.
     *
     * @throws \moodle_exception If gets running via CLI or AJAX call.
     */
    protected function redirect($url) {
        if (CLI_SCRIPT or AJAX_SCRIPT) {
            throw new moodle_exception('redirecterrordetected', 'auth_iam', '', $url);
        }

        redirect($url);
    }

    /**
     * Don't allow login using login form.
     *
     * @param string $username The username (with system magic quotes)
     * @param string $password The password (with system magic quotes)
     *
     * @return bool Authentication success or failure.
     */
    public function user_login($username=null, $password=null) {
        global $CFG,$DB;
        $fa = new auth_iam\fa();
        $data = $fa->get_userinfo();
        //  $data2 = '{"id":106473,"guid":"cba937a3-6b03-48a0-b187-af81fb3be64e","englishName":"YUVARAJ BALUSAMY","arabicFatherName":"","englishFatherName":"","sub":2518075235,"gender":"Male",
        //     "iss":"https:\/\/www.iam.gov.sa\/userauth","cardIssueDateGregorian":"Tue Apr 26 03:00:00 AST 2022","englishGrandFatherName":"",
        //     "userid":2518075235,"idVersionNo":1,"arabicNationality":"الهند","arabicName":"يوفاراج بالو سامي","arabicFirstName":"يوفاراج","nationalityCode":321,"iqamaExpiryDateHijri":6.2510822510822512,
        //     "lang":"en","exp":1669288387,"iat":1669288237,"iqamaExpiryDateGregorian":"Sat Jun 10 03:00:00 AST 2023",
        //     "idExpiryDateGregorian":"Sat Jun 10 03:00:00 AST 2023","jti":"https:\/\/iam.elm.sa,fa9407b5-ad4a-42ae-954a-2e9d1f11edde","issueLocationAr":"شركة العلم لامن المعلومات",
        //     "dobHijri":"1404\/08\/11","cardIssueDateHijri":"1443\/09\/25","englishFirstName":"YUVARAJ","issueLocationEn":"Company Science Information Security","arabicGrandFatherName":"",
        //     "aud":"https:\/\/Auth.fa.gov.sa","nbf":1669288087,"nationality":"India","dob":"Sat May 12 03:00:00 AST 1984","englishFamilyName":"BALUSAMY","idExpiryDateHijri":6.2510822510822512,"assurance_level":"","arabicFamilyName":"بالو سامي","isUsed":1,"createdOn":"2022-11-24T14:10:36.9715828","returnUrl":"https:\/\/lmsstaging.fa.gov.sa\/auth\/iam\/login.php"}';
        // $data = json_decode($data);
        //$d1 = '{ "id": 106485, "guid": "8ed89165-5ddf-4a8e-accb-e2c0b3bc238c" , "englishName": "New Test A User " , "arabicFatherName": "محمد", "englishFatherName": "KARAN", "sub": "1120496953", "gender": "Female", "iss": "https://www.iam.gov.sa/userauth", "cardIssueDateGregorian": "Thu Aug 26 03:00:00 AST 2021", "englishGrandFatherName": "A", "userid": "1120496955", "idVersionNo": "2", "arabicNationality": "المملكة العربية السعودية", "arabicName": "أثير بنت محمد بن عبدالهادي الحارثي", "arabicFirstName": "أثير" , "nationalityCode": "113", "iqamaExpiryDateHijri": "1448/01/17", "lang": "en" , "exp": "1669290753", "iat": "1669290603", "iqamaExpiryDateGregorian": "Thu Jul 02 03:00:00 AST 2026", "idExpiryDateGregorian": "Thu Jul 02 03:00:00 AST 2026", "jti": "https://iam.elm.sa,9a4ac0b4-d1ec-4c65-a8fb-d91480f3d0b1", "issueLocationAr": "أحوال الدمام-نساء", "dobHijri": "1420/11/06", "cardIssueDateHijri": "1443/01/18", "englishFirstName": "Test" , "issueLocationEn": "", "arabicGrandFatherName": "بن عبدالهادي", "aud": "https://Auth.fa.gov.sa" , "nbf": 1669290453, "nationality": "Kingdom of Saudi Arabia", "dob": "Sat Feb 12 03:00:00 AST 2000", "englishFamilyName": "Testing", "idExpiryDateHijri": "1448/01/17", "assurance_level": "", "arabicFamilyName": "الحارثي" , "isUsed": true, "createdOn": "2022-11-24T14:50:03.8896777" , "returnUrl": "http://localhost/fa/sourcecode/Home/index.php" }';
        // $data2 = '{ "id": 106486, "guid": "8ed89165-5ddf-4a8e-accb-e2c0b3bc239c" , "englishName": "Sample Test A User" , "arabicFatherName": "محمد", "englishFatherName": "DANIEL", "sub": "1111111525", "gender": "Female", "iss": "https://www.iam.gov.sa/userauth", "cardIssueDateGregorian": "Thu Aug 26 03:00:00 AST 2021", "englishGrandFatherName": "A", "userid": "1111111525", "idVersionNo": "2", "arabicNationality": "المملكة العربية السعودية", "arabicName": "أثير بنت محمد بن عبدالهادي الحارثي", "arabicFirstName": "أثير" , "nationalityCode": "113", "iqamaExpiryDateHijri": "1448/01/17", "lang": "en" , "exp": "1669290753", "iat": "1669290603", "iqamaExpiryDateGregorian": "Thu Jul 02 03:00:00 AST 2026", "idExpiryDateGregorian": "Thu Jul 02 03:00:00 AST 2026", "jti": "https://iam.elm.sa,9a4ac0b4-d1ec-4c65-a8fb-d91480f3d0b1", "issueLocationAr": "أحوال الدمام-نساء", "dobHijri": "1420/11/06", "cardIssueDateHijri": "1443/01/18", "englishFirstName": "DANIEL" , "issueLocationEn": "", "arabicGrandFatherName": "بن عبدالهادي", "aud": "https://Auth.fa.gov.sa" , "nbf": 1669290689, "nationality": "Kingdom of Saudi Arabia", "dob": "Sat Feb 12 03:00:00 AST 2000", "englishFamilyName": "ALHARTHI", "idExpiryDateHijri": "1448/01/17", "assurance_level": "", "arabicFamilyName": "الحارثي" , "isUsed": true, "createdOn": "2022-11-24T14:50:03.8896777" , "returnUrl": "http://localhost/fa/sourcecode/Home/index.php" }';
       // $data3 = '{ "id": 106487, "guid": "8ed89165-5ddf-4a8e-accb-e2c0b3bc240c" , "englishName": Sample Test A User" , "arabicFatherName": "محمد", "englishFatherName": "DANIEL", "sub": "1120496955", "gender": "Female", "iss": "https://www.iam.gov.sa/userauth", "cardIssueDateGregorian": "Thu Aug 26 03:00:00 AST 2021", "englishGrandFatherName": "A", "userid": "2261455032", "idVersionNo": "2", "arabicNationality": "المملكة العربية السعودية", "arabicName": "أثير بنت محمد بن عبدالهادي الحارثي", "arabicFirstName": "أثير" , "nationalityCode": "113", "iqamaExpiryDateHijri": "1448/01/17", "lang": "en" , "exp": "1669290753", "iat": "1669290603", "iqamaExpiryDateGregorian": "Thu Jul 02 03:00:00 AST 2026", "idExpiryDateGregorian": "Thu Jul 02 03:00:00 AST 2026", "jti": "https://iam.elm.sa,9a4ac0b4-d1ec-4c65-a8fb-d91480f3d0b1", "issueLocationAr": "أحوال الدمام-نساء", "dobHijri": "1420/11/06", "cardIssueDateHijri": "1443/01/18", "englishFirstName": "Mehar" , "issueLocationEn": "", "arabicGrandFatherName": "بن عبدالهادي", "aud": "https://Auth.fa.gov.sa" , "nbf": 1669290394, "nationality": "Kingdom of Saudi Arabia", "dob": "Sat Feb 12 03:00:00 AST 2000", "englishFamilyName": "ALHARTHI", "idExpiryDateHijri": "1448/01/17", "assurance_level": "", "arabicFamilyName": "الحارثي" , "isUsed": true, "createdOn": "2022-11-24T14:50:03.8896777" , "returnUrl": "http://localhost/fa/sourcecode/Home/index.php" }';
       //$data3 = '{ "id": 106486, "guid": "8ed89165-5ddf-4a8e-accb-e2c0b3bc239c" , "englishName": "Nandan" , "arabicFatherName": "Nandan", "englishFatherName": "Nandan", "sub": "1254734625", "gender": "Male", "iss": "https://www.iam.gov.sa/userauth", "cardIssueDateGregorian": "Thu Aug 26 03:00:00 AST 2021", "englishGrandFatherName": "Nandan", "userid": "1071770604", "idVersionNo": "2", "arabicNationality": "المملكة العربية السعودية", "arabicName": "أثير بنت محمد بن عبدالهادي الحارثي", "arabicFirstName": "أثير" , "nationalityCode": "113", "iqamaExpiryDateHijri": "1448/01/17", "lang": "en" , "exp": "1669290753", "iat": "1669290603", "iqamaExpiryDateGregorian": "Thu Jul 02 03:00:00 AST 2026", "idExpiryDateGregorian": "Thu Jul 02 03:00:00 AST 2026", "jti": "https://iam.elm.sa,9a4ac0b4-d1ec-4c65-a8fb-d91480f3d0b1", "issueLocationAr": "أحوال الدمام-نساء", "dobHijri": "1420/11/06", "cardIssueDateHijri": "1443/01/18", "englishFirstName": "Nandan" , "issueLocationEn": "", "arabicGrandFatherName": "بن عبدالهادي", "aud": "https://Auth.fa.gov.sa" , "nbf": 1669290689, "nationality": "Kingdom of Saudi Arabia", "dob": "Sat Feb 12 03:00:00 AST 2000", "englishFamilyName": "Jr", "idExpiryDateHijri": "1448/01/17", "assurance_level": "", "arabicFamilyName": "الحارثي" , "isUsed": true, "createdOn": "2022-11-24T14:50:03.8896777" , "returnUrl": "https://lmsstaging.fa.gov.sa/auth/iam/login.php" }';
      // $data = json_decode($data3);
     
        if(!$data){
            return false;
        }
        try{
            $eventdata = array(
                'other' => array('data' => $data),
            );
            $event = \auth_iam\event\data_synced::create($eventdata);
            $event->trigger();
            $mappeddata = $this->map_data($data);
            $mapedusername = $mappeddata['username'];
            $mapedidnumber = $mappeddata['idnumber'];
            $userrecordexists = $DB->record_exists_sql("SELECT * FROM mdl_user WHERE username ='$mapedusername' AND idnumber = '$mapedidnumber' AND email !='' AND phone1 !=''");
            if($userrecordexists) {
                $user = $this->get_user($mappeddata);
                $user->existinsystem = 1;
            } else {
                
                $user = $this->get_user($mappeddata);
                $user->existinsystem = 0;
            }
            if($user){
                complete_user_login($user);
                redirect($CFG->wwwroot);
            }
        }catch(\Exception $e){
            print_object($e);
        }

        return true;
    }

    private function map_data($data){
        global $USER;

        $gender = ['male' => 1, 'female' => 2];
        $userrecord = array();
        $userrecord['firstname'] = ucfirst($data->englishFirstName);
        $userrecord['lastname'] = ucfirst($data->englishFamilyName);
        $userrecord['firstnamearabic'] = $data->arabicFirstName;
        $userrecord['lastnamearabic'] = $data->arabicFamilyName;
        $userrecord['middlenamearabic'] = $data->arabicFatherName;
        $userrecord['middlenameen'] = $data->englishFatherName;
        $userrecord['thirdnamearabic'] = $data->arabicGrandFatherName;
        $userrecord['thirdnameen'] = $data->englishGrandFatherName;
        $userrecord['gender'] = isset($gender[strtolower($data->gender)]) ? $gender[strtolower($data->gender)] : 1;//Male by default.
        $userrecord['lang'] = $data->lang;
        $countries = get_string_manager()->get_list_of_countries(false, 'en');
        $nationality = array_search(strtolower(trim($data->nationality)), array_map('strtolower', $countries));
        $userrecord['nationality'] = $nationality === false ? 'SA' : $nationality;
        $userrecord['country'] = $nationality === false ? 'SA' : $nationality;
        $userrecord['nationalitycountryid'] = $data->nationalityCode;
        if($userrecord['nationality'] == 'SA' ){
            $userrecord['id_type'] = 3; //Saudi Id.
        }else{
            $userrecord['id_type'] = 4; //Residential id
        }
        $userrecord['idnumber'] = $data->userid;
        $userrecord['dateofbirth'] = strtotime($data->dob);
        $userrecord['username'] = $data->userid;
        $userrecord['email'] = strtolower($data->email);
        $userrecord['password'] = 'Welcome123$';
        $userrecord['phone1'] = $data->phone;

        $userrecord['timecreated'] =time();
        $userrecord['usercreated'] = $USER->id;
        $userrecord['approvedstatus'] = 2;
        $userrecord['authmethod'] = 'manual';
        $userrecord['usersource'] = 'IAM';

        return $userrecord;
    }

    /**
     * Don't store local passwords.
     *
     * @return bool True.
     */
    public function prevent_local_passwords() {
        return false;
    }

    /**
     * Returns true if this authentication plugin is external.
     *
     * @return bool False.
     */
    public function is_internal() {
        return false;
    }

    /**
     * The plugin can't change the user's password.
     *
     * @return bool False.
     */
    public function can_change_password() {
        return true;
    }

    /**
     * Return mapping field to find a lms user.
     *
     * @return string
     */
    public function get_mapping_field() {
        if (isset($this->config->mappingfield) && !empty($this->config->mappingfield)) {
            return $this->config->mappingfield;
        }
        return self::DEFAULT_MAPPING_FIELD;
    }

    /**
     * Check if we need to create a new user.
     *
     * @return bool
     */
    protected function should_create_user() {
        if (isset($this->config->createuser) && $this->config->createuser == true) {
            return true;
        }
        return false;
    }

    /**
     * Create a new user.
     *
     * @param array $data Validated user data from web service.
     *
     * @return object User object.
     */
    protected function create_user(array $data) {
        global $DB, $CFG;

        $user = $data;
        unset($user['ip']);
        $user['auth'] = 'iam';
        $user['confirmed'] = 1;
        $user['mnethostid'] = $CFG->mnet_localhost_id;

        $requiredfieds = ['username', 'email', 'firstname', 'lastname'];
        $missingfields = [];
        foreach ($requiredfieds as $requiredfied) {
            if (empty($user[$requiredfied])) {
                $missingfields[] = $requiredfied;
            }
        }
        if (!empty($missingfields)) {
            throw new invalid_parameter_exception('Unable to create user, missing value(s): ' . implode(',', $missingfields));
        }

        if ($DB->record_exists('user', array('username' => $user['username'], 'mnethostid' => $CFG->mnet_localhost_id))) {
            throw new invalid_parameter_exception('Username already exists: '.$user['username']);
        }
        if (!empty($user['email']) && !validate_email($user['email'])) {
            throw new invalid_parameter_exception('Email address is invalid: '.$user['email']);
        } else if (empty($CFG->allowaccountssameemail) &&
            $DB->record_exists('user', array('email' => $user['email'], 'mnethostid' => $user['mnethostid']))) {
            throw new invalid_parameter_exception('Email address already exists: '.$user['email']);
        }
        $userid = user_create_user($user);
        return $DB->get_record('user', ['id' => $userid]);
    }

    /**
     * Validate user data from web service.
     *
     * @param mixed $data User data from web service.
     *
     * @return array
     *
     * @throws \invalid_parameter_exception If provided data is invalid.
     */
    protected function validate_user_data($data) {
        $data = (array)$data;

        $mappingfield = $this->get_mapping_field();

        if (!isset($data[$mappingfield]) || empty($data[$mappingfield])) {
            throw new invalid_parameter_exception('Required field "' . $mappingfield . '" is not set or empty.');
        }

        if ($this->is_ip_restriction_enabled() && !isset($data['ip'])) {
            throw new invalid_parameter_exception('Required parameter "ip" is not set.');
        }

        return $data;
    }

    /**
     * Return user object.
     *
     * @param array $data Validated user data.
     *
     * @return object A user object.
     *
     * @throws \invalid_parameter_exception If user is not exist and we don't need to create a new.
     */
    protected function get_user(array $data) {
        global $DB, $CFG;
        $data = (object)$data;
        $systemcontext = context_system::instance();
        $mappingfield = $this->get_mapping_field();
        $params = array(
            $mappingfield => $data->$mappingfield,
            'mnethostid' => $CFG->mnet_localhost_id
        );
        $user = $DB->get_record('user', $params);
        $data->id_number = $data->idnumber;
        
        if (empty($user)) {
            $usernameexistis = $DB->record_exists_sql('SELECT id FROM {user} WHERE username = '.$data->username.' AND idnumber <> '.$data->idnumber.'');
            $data->username = ($usernameexistis) ? 0 : $data->username;
            $data->password = hash_internal_user_password($data->password);
            $userid = (new manageuser)->create_user($data);
            (new manageuser)->create_custom_user($data, $userid);
        }else{
            $userid = $user->id;
            $data->username = $user->username;
            $data->existinsystem = 1;
            (new manageuser)->user_update_user($data, $userid);
            $data->id = $DB->get_field('local_users', 'id', array('userid' => $userid));
            (new manageuser)->update_custom_user($data, $userid);
        }
        $user = \core_user::get_user($userid);
        return $user;
    }



    /**
     * Return login URL.
     *
     * @param array|stdClass $data User data from web service.
     *
     * @return string Login URL.
     *
     * @throws \invalid_parameter_exception
     */
    public function get_login_url() {
        global $CFG;
        return $this->config->login_url.'?returnUrl='.$CFG->wwwroot . '/auth/iam/login.php';
    }

    /**
     * Return a list of mapping fields.
     *
     * @return array
     */
    public function get_allowed_mapping_fields() {
        return array(
            'username' => get_string('username'),
            'email' => get_string('email'),
            'idnumber' => get_string('idnumber'),
        );
    }

    /**
     * Return a mapping parameter for request_login_url_parameters().
     *
     * @return array
     */
    protected function get_mapping_parameter() {
        $mappingfield = $this->get_mapping_field();

        switch ($mappingfield) {
            case 'username':
                $parameter = array(
                    'username' => new external_value(
                        PARAM_USERNAME,
                        'Username'
                    ),
                );
                break;

            case 'email':
                $parameter = array(
                    'email' => new external_value(
                        PARAM_EMAIL,
                        'A valid email address'
                    ),
                );
                break;

            case 'idnumber':
                $parameter = array(
                    'idnumber' => new external_value(
                        PARAM_RAW,
                        'An arbitrary ID code number perhaps from the institution'
                    ),
                );
                break;

            default:
                $parameter = array();
                break;
        }

        return $parameter;
    }

    /**
     * Return user fields parameters for request_login_url_parameters().
     *
     * @return array
     */
    protected function get_user_fields_parameters() {
        $parameters = array();
        if ($this->is_ip_restriction_enabled()) {
            $parameters['ip'] = new external_value(
                PARAM_HOST,
                'User IP address'
            );
        }
        $mappingfield = $this->get_mapping_field();
        return $parameters;
    }

    /**
     * Return parameters for request_login_url_parameters().
     *
     * @return array
     */
    public function get_request_login_url_user_parameters() {
        $parameters = array_merge($this->get_mapping_parameter(), $this->get_user_fields_parameters());
        return $parameters;
    }

    /**
     * Check if we should redirect a user as part of login.
     *
     * @return bool
     */
    protected function should_login_redirect() {
        return false;
    }

    /**
     * Check if we should redirect a user after logout.
     *
     * @return bool
     */
    protected function should_logout_redirect() {
        return true;
    }


    /**
     * Logout page hook.
     *
     * Override redirect URL after logout.
     *
     * @see auth_plugin_base::logoutpage_hook()
     */
    public function logoutpage_hook() {
        global $redirect;
        if ($this->should_logout_redirect()) {
            $redirect = $this->config->redirecturl;
        }
    }

    /**
     * Log out user and redirect.
     */
    public function user_logout_iam() {
        global $CFG, $USER;
        $redirect = required_param('return', PARAM_URL);
        // We redirect when user's session in Moodle already has expired
        // or the user is still logged in using "iam" auth type.
        if (!isloggedin() || $USER->auth == 'iam') {
            require_logout();
            $this->redirect($redirect);
        } else {
            // If logged in with different auth type, then display an error.
            print_error('incorrectlogout', 'auth_iam', $CFG->wwwroot);
        }
    }
}
