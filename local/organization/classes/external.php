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
 * local local_organization
 *
 * @package    local_organization
 * @copyright  2022 eAbyas <eAbyas.in>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
 
defined('MOODLE_INTERNAL') || die;
require_once("$CFG->libdir/externallib.php");
require_once($CFG->dirroot.'/course/lib.php');
require_once( '../../config.php' );
class local_organization_external extends external_api {
    public static function organization_view_parameters() {
        return new external_function_parameters([
            'options' => new external_value(PARAM_RAW, 'The paging data for the service'),
            'dataoptions' => new external_value(PARAM_RAW, 'The data for the service'),
            'offset' => new external_value(PARAM_INT, 'Number of items to skip from the begging of the result set',
                VALUE_DEFAULT, 0),
            'limit' => new external_value(PARAM_INT, 'Maximum number of results to return',
                VALUE_DEFAULT, 0),
            'contextid' => new external_value(PARAM_INT, 'contextid'),
            'filterdata' => new external_value(PARAM_RAW, 'filters applied'),
        ]);
    }

    public static function organization_view($options, $dataoptions, $offset = 0, $limit = 0, $contextid, $filterdata) {
        global $DB, $PAGE, $CFG;
        require_login();
        $params = self::validate_parameters(
            self::organization_view_parameters(),
            [
                'options' => $options,
                'dataoptions' => $dataoptions,
                'offset' => $offset,
                'limit' => $limit,
                'contextid' => $contextid,
                'filterdata' => $filterdata,
            ]
        );
        $offset = $params['offset'];
        $limit = $params['limit'];
        $filtervalues = json_decode($filterdata);

        $stable = new \stdClass();
        $stable->thead = false;
        $stable->start = $offset;
        $stable->length = $limit;
        $data = (new local_organization\organization)->get_listof_organizations($stable, $filtervalues);
        $totalcount = $data['totalorganizations'];
        return [
            'totalcount' => $totalcount,
            'length' => $totalcount,
            'filterdata' => $filterdata,
            'records' =>$data,
            'options' => $options,
            'dataoptions' => $dataoptions,
            'url' => $CFG->wwwroot,
        ];
    }

  /**
   * Returns description of method result value
   * @return external_description
   */

    public static function organization_view_returns() {
        return new external_single_structure([
        'options' => new external_value(PARAM_RAW, 'The paging data for the service'),
        'url' => new external_value(PARAM_RAW, 'url'),
        'dataoptions' => new external_value(PARAM_RAW, 'The data for the service'),
        'totalcount' => new external_value(PARAM_INT, 'total number of challenges in result set'),
        'filterdata' => new external_value(PARAM_RAW, 'total number of challenges in result set'),
        'length' => new external_value(PARAM_RAW, 'total number of challenges in result set'),
        'records' => new external_single_structure(
                array( 
                    'hascourses' => new external_multiple_structure(
                        new external_single_structure(
                            array(
                                'id' => new external_value(PARAM_INT, 'id'),
                                'fullname' => new external_value(PARAM_RAW, 'fullname'),
                                'code' => new external_value(PARAM_RAW, 'code'),
                                'hrfullname' => new external_value(PARAM_RAW, 'hrfullname'),
                                'hremail' => new external_value(PARAM_RAW, 'hremail'),
                                'status' => new external_value(PARAM_RAW, 'status'),
                                'actions' => new external_value(PARAM_RAW, 'actions'),
                                'statustext' => new external_value(PARAM_RAW, 'statustext'),
                                'nooftrainees' => new external_value(PARAM_INT, 'nooftrainees'),
                                'manageorg' => new external_value(PARAM_BOOL, 'manageorg'),
                                'sendmail' => new external_value(PARAM_BOOL, 'sendmail', VALUE_OPTIONAL),
                            )
                        )
                    ),
                    'request_view' => new external_value(PARAM_INT, 'request_view', VALUE_OPTIONAL),
                    'totalorganizations' => new external_value(PARAM_INT, 'totalorganizations', VALUE_OPTIONAL),
                    'length' => new external_value(PARAM_INT, 'length', VALUE_OPTIONAL),
                )
            )
        ]);
    }
    public static function deleteorganization_parameters(){
        return new external_function_parameters(
            array(
                'organizationid' => new external_value(PARAM_INT, 'organizationid', 0)
                )
        );

    }
  
    public static function deleteorganization($organizationid){
        global $DB;
        $params = self::validate_parameters(self::deleteorganization_parameters(),
                                    ['organizationid' => $organizationid]);
        if($organizationid){
           $organizationid = $DB->delete_records('local_organization', array('id' => $organizationid));
           $userid = $DB->delete_records('local_users', array('organization' => $organizationid));           
            return true;
        }else {
            throw new moodle_exception('Error in deleting');
            return false;
        }
    }
   
    public static function deleteorganization_returns() {
        return new external_value(PARAM_BOOL, 'return');
    }

    public static function deleteorguser_parameters(){
        return new external_function_parameters(
            array(
                'id' => new external_value(PARAM_INT, 'id', 0),
                'roleid' => new external_value(PARAM_INT, 'roleid', 0),
                'orgid' => new external_value(PARAM_INT, 'orgid', 0),
                )
        );
    }  
    public static function deleteorguser($id, $roleid, $orgid){
        global $DB;
        $params = self::validate_parameters(self::deleteorguser_parameters(),
                                    ['id' => $id, 'roleid' => $roleid, 'orgid' => $orgid]);
        $systemcontext = context_system::instance();
        $orgofficialroleid = $DB->get_field('role', 'id', array('shortname' => 'organizationofficial'));
        $traineeeid = $DB->get_field('role', 'id', array('shortname' => 'trainee'));
        $userid = $DB->get_field('local_users', 'userid', array('id' => $id));
        if($id){
            if($DB->record_exists('role_assignments',array('roleid'=>$orgofficialroleid,'userid'=>$userid))) {
              role_unassign($orgofficialroleid, $userid, $systemcontext->id);
              role_assign($traineeeid, $userid, $systemcontext->id);
            } else {
                $updaterecord = $DB->update_record('local_users', array('organization' => 0, 'id' => $id));
            }


            return true;
        }else {
            throw new moodle_exception('Error in deleting');
            return false;
        }
    }
   
    public static function deleteorguser_returns() {
        return new external_value(PARAM_BOOL, 'return');
    }

    public static function approve_organization_parameters(){
        return new external_function_parameters(
            array(
                'orgid' => new external_value(PARAM_INT, 'orgid', 0),
                )
        );

    }  
    public static function approve_organization($orgid){
        global $DB;
        $params = self::validate_parameters(self::approve_organization_parameters(),
                                    ['orgid' => $orgid]);
        if($orgid){
            $row = array();
            $row['id'] = $orgid;
            $row['status'] = 2;
            $row['timemodified'] = time();
            $record->id = $DB->update_record('local_organization', $row);
            $licensekey=$DB->get_field('local_organization','licensekey',array('id'=>$orgid));
            if (!empty($licensekey) && $DB->record_exists('organization_draft',array('licensekey'=>$licensekey))){
            $deletedraftrecord = $DB->delete_records('organization_draft', array('licensekey' => $licensekey));
            }            
            return true;
        } else {
            throw new moodle_exception('Error in deleting');
            return false;
        }
    }
   
    public static function approve_organization_returns() {
        return new external_value(PARAM_BOOL, 'return');
    }

    public static function reject_organization_parameters(){
        return new external_function_parameters(
            array(
                'orgid' => new external_value(PARAM_INT, 'orgid', 0),
                )
        );

    }  
    public static function reject_organization($orgid){
        global $DB;
        $params = self::validate_parameters(self::reject_organization_parameters(),
                                    ['orgid' => $orgid]);
        if($orgid){
            $row = array();
            $row['id'] = $orgid;
            $row['status'] = 0;
            $row['timemodified'] = time();
            $record->id = $DB->update_record('local_organization', $row);
            return true;
        } else {
            throw new moodle_exception('Error in deleting');
            return false;
        }
    }
   
    public static function reject_organization_returns() {
        return new external_value(PARAM_BOOL, 'return');
    }
    public static function enrolledusers_view_parameters() {
        return new external_function_parameters([
            'options' => new external_value(PARAM_RAW, 'The paging data for the service'),
            'dataoptions' => new external_value(PARAM_RAW, 'The data for the service'),
            'offset' => new external_value(PARAM_INT, 'Number of items to skip from the begging of the result set',
                VALUE_DEFAULT, 0),
            'limit' => new external_value(PARAM_INT, 'Maximum number of results to return',
                VALUE_DEFAULT, 0),
            'contextid' => new external_value(PARAM_INT, 'contextid'),
            'filterdata' => new external_value(PARAM_RAW, 'filters applied')
        ]);
    }

    public static function enrolledusers_view($options, $dataoptions, $offset = 0, $limit = 0, $contextid, $filterdata) {
        global $DB, $PAGE, $CFG;
        require_login();
        $params = self::validate_parameters(
            self::enrolledusers_view_parameters(),
            [
                'options' => $options,
                'dataoptions' => $dataoptions,
                'offset' => $offset,
                'limit' => $limit,
                'contextid' => $contextid,
                'filterdata' => $filterdata,
            ]
        );
        $offset = $params['offset'];
        $limit = $params['limit'];
        $filtervalues = json_decode($filterdata);

        $stable = new \stdClass();
        $stable->thead = false;
        $stable->start = $offset;
        $stable->length = $limit;
        $data = (new local_organization\organization)->get_listof_enrolledusers($stable, $filtervalues, $dataoptions);
        $totalcount = $data['totalusers'];
        return [
            'totalcount' => $totalcount,
            'length' => $totalcount,
            'filterdata' => $filterdata,
            'records' =>$data,
            'options' => $options,
            'dataoptions' => $dataoptions,
            'url' => $CFG->wwwroot,
            'nodata' => true,
        ];
    }

  /**
   * Returns description of method result value
   * @return external_description
   */

    public static function enrolledusers_view_returns() {
        return new external_single_structure([
          'options' => new external_value(PARAM_RAW, 'The paging data for the service'),
          'url' => new external_value(PARAM_RAW, 'url'),
          'nodata' => new external_value(PARAM_BOOL, 'nodata'),
          'dataoptions' => new external_value(PARAM_RAW, 'The data for the service'),
          'totalcount' => new external_value(PARAM_INT, 'total number of challenges in result set'),
          'filterdata' => new external_value(PARAM_RAW, 'total number of challenges in result set'),
          'length' => new external_value(PARAM_RAW, 'total number of challenges in result set'),
          'records' => new external_single_structure(
                  array(                 
                      'hascourses' => new external_multiple_structure(
                          new external_single_structure(
                              array(
                                  'id' => new external_value(PARAM_INT, 'id'),
                                  'fullname' => new external_value(PARAM_RAW, 'firstname'),
                                  'email' => new external_value(PARAM_RAW, 'email'),
                                  'id_number' => new external_value(PARAM_RAW, 'id_number'),
                                  'phone' => new external_value(PARAM_RAW, 'phone'),
                                  'roleid' => new external_value(PARAM_RAW, 'roleid'),
                                  'rolename' => new external_value(PARAM_RAW, 'rolename'),
                                  'roleshortname' => new external_value(PARAM_RAW, 'roleshortname'),
                                  'unassignuser' => new external_value(PARAM_BOOL, 'unassignuser'),
                                  'orgid' => new external_value(PARAM_INT, 'orgid'),
                                  'organizationname' => new external_value(PARAM_RAW, 'organizationname'),
                              )
                          )
                      ),
                      'request_view' => new external_value(PARAM_INT, 'request_view', VALUE_OPTIONAL),
                      'nocourses' => new external_value(PARAM_BOOL, 'nocourses', VALUE_OPTIONAL),
                      'totalusers' => new external_value(PARAM_INT, 'totalhalls', VALUE_OPTIONAL),
                      'length' => new external_value(PARAM_INT, 'length', VALUE_OPTIONAL),
                  )
              )
        ]);
    }


    public static function authusers_view_parameters() {
        return new external_function_parameters([
            'options' => new external_value(PARAM_RAW, 'The paging data for the service'),
            'dataoptions' => new external_value(PARAM_RAW, 'The data for the service'),
            'offset' => new external_value(PARAM_INT, 'Number of items to skip from the begging of the result set',
                VALUE_DEFAULT, 0),
            'limit' => new external_value(PARAM_INT, 'Maximum number of results to return',
                VALUE_DEFAULT, 0),
            'contextid' => new external_value(PARAM_INT, 'contextid'),
            'filterdata' => new external_value(PARAM_RAW, 'filters applied')
        ]);
    }

    public static function authusers_view($options, $dataoptions, $offset = 0, $limit = 0, $contextid, $filterdata) {
        global $DB, $PAGE, $CFG;
        require_login();
        $params = self::validate_parameters(
            self::authusers_view_parameters(),
            [
                'options' => $options,
                'dataoptions' => $dataoptions,
                'offset' => $offset,
                'limit' => $limit,
                'contextid' => $contextid,
                'filterdata' => $filterdata,
            ]
        );
        $offset = $params['offset'];
        $limit = $params['limit'];
        $filtervalues = json_decode($filterdata);

        $stable = new \stdClass();
        $stable->thead = false;
        $stable->start = $offset;
        $stable->length = $limit;
        $data = (new local_organization\organization)->get_listof_authusers($stable, $filtervalues, $dataoptions);
        $totalcount = $data['totalusers'];
        return [
            'totalcount' => $totalcount,
            'length' => $totalcount,
            'filterdata' => $filterdata,
            'records' =>$data,
            'options' => $options,
            'dataoptions' => $dataoptions,
            'url' => $CFG->wwwroot,
            'nodata' => true,
        ];
    }

  /**
   * Returns description of method result value
   * @return external_description
   */

    public static function authusers_view_returns() {
        return new external_single_structure([
          'options' => new external_value(PARAM_RAW, 'The paging data for the service'),
          'url' => new external_value(PARAM_RAW, 'url'),
          'nodata' => new external_value(PARAM_BOOL, 'nodata'),
          'dataoptions' => new external_value(PARAM_RAW, 'The data for the service'),
          'totalcount' => new external_value(PARAM_INT, 'total number of challenges in result set'),
          'filterdata' => new external_value(PARAM_RAW, 'total number of challenges in result set'),
          'length' => new external_value(PARAM_RAW, 'total number of challenges in result set'),
          'records' => new external_single_structure(
                  array(
                      'authusers' => new external_multiple_structure(
                          new external_single_structure(
                              array(
                                  'id' => new external_value(PARAM_INT, 'id'),
                                  'authfirstname' => new external_value(PARAM_RAW, 'firstname'),
                                  'authemail' => new external_value(PARAM_RAW, 'email'),
                                  'authid_number' => new external_value(PARAM_RAW, 'id_number'),
                                  'authphone' => new external_value(PARAM_RAW, 'phone'),
                                  'authroleid' => new external_value(PARAM_RAW, 'roleid'),
                                  'authrolename' => new external_value(PARAM_RAW, 'rolename'),
                                  'unassignuser' => new external_value(PARAM_INT, 'unassignuser'),
                                  'orgid' => new external_value(PARAM_INT, 'orgid'),
                              )
                          )
                      ),
                      'request_view' => new external_value(PARAM_INT, 'request_view', VALUE_OPTIONAL),
                      'nocourses' => new external_value(PARAM_BOOL, 'nocourses', VALUE_OPTIONAL),
                      'totalusers' => new external_value(PARAM_INT, 'totalhalls', VALUE_OPTIONAL),
                      'length' => new external_value(PARAM_INT, 'length', VALUE_OPTIONAL),
                  )
              )
        ]);
    }

    public static function organization_info_parameters() {
        return new external_function_parameters(
            array(
                'orgid' => new external_value(PARAM_INT, 'orgid', 0),
                )
        );
    }
    public static function organization_info($orgid) {
        global $DB;
        require_login();
        $params = self::validate_parameters(self::organization_info_parameters(),
                                    ['orgid' => $orgid]);
        $data = (new local_organization\organization)->org_info($orgid);
        return [
            'options' => $data,
        ];
    }

  /**
   * Returns description of method result value
   * @return external_description
   */

    public static function organization_info_returns() {
        return new external_single_structure([
          'options' => new external_value(PARAM_RAW, 'The paging data for the service'),
        ]);
    }

    public static function organization_userslist_parameters() {
        // return new external_function_parameters([
        //     'orgid' => new external_value(PARAM_INT, 'organization id'),
        //     'type' => new external_value(PARAM_RAW, 'type of the organization')
        // ]);
        $query = new external_value(PARAM_RAW, 'search query');
        $type = new external_value(PARAM_ALPHANUMEXT, 'Type of data', VALUE_REQUIRED);
        $org = new external_value(PARAM_TEXT, 'Organization', VALUE_OPTIONAL,null);
       

        $params = array(
            'query' => $query,
            'type' => $type,
            'org' => $org
            
        );
        return new external_function_parameters($params);
    }
    public static function organization_userslist($query, $type, $org = null) {
        global $PAGE;
        $params = array(
            'query' => $query,
            'type' => $type,
            'org' => $org
        );
        $params = self::validate_parameters(self::organization_userslist_parameters(), $params);

        switch($params['type']) {
            case 'orgusers':
                $orgusers = (new local_organization\organization)->get_orgusers($params['query'],$params['org']);
            break;
            case 'usersemail':
                $orgusers = (new local_organization\organization)->get_usersemail($params['query'],$params['org']);
            break;
            case 'usersidnumber':
                $orgusers = (new local_organization\organization)->get_usersidnumber($params['query'],$params['org']);
            break;
            case 'all_users':
                $orgusers = (new local_organization\organization)->get_all_users($params['query'],$params['org']);
            break;
            case 'organization_list':
                $orgusers = (new local_organization\organization)->get_all_organizations($params['query'],$params['org']);
            break;

        }
        
        return ['status' => true, 'data' => $orgusers];
    }
    public static function organization_userslist_returns() {
        return new external_single_structure(
            array(
                'status' => new external_value(PARAM_INT, 'status: true if success'),
                'data' => new external_multiple_structure(new external_single_structure(
                    array(
                        'id' => new external_value(PARAM_INT, 'status: true if success'),
                        'fullname' => new external_value(PARAM_RAW, 'status: true if success',VALUE_OPTIONAL))
                   ), '', VALUE_OPTIONAL) 
             )
        );
    }

    public function create_organization_parameters() {
        return new external_function_parameters(
            array(
                'organization' => new external_multiple_structure(
                    new external_single_structure(
                        array(
                            'licensekey' => new external_value(PARAM_ALPHANUM, 'organization licensekey'),
                            'fullname' => new external_value(PARAM_RAW, 'organization fullname'),
                            'shortname' => new external_value(PARAM_RAW, 'organization code'),
                            'orgsector' => new external_value(PARAM_INT, 'organization sector'),
                            'orgsegment' => new external_value(PARAM_INT, 'orgsegment'),
                            'orgfieldofwork' => new external_value(PARAM_RAW, 'organization fieldofwork'),
                            'hrfullname' => new external_value(PARAM_RAW, 'organization hrfullname'),
                            'hrjobrole' => new external_value(PARAM_RAW, 'organization hrjobrole'),
                            'hremail' => new external_value(PARAM_RAW, 'organization hremail'),
                            'hrmobile' => new external_value(PARAM_RAW, 'organization hrmobile', VALUE_OPTIONAL),
                            'alfullname' => new external_value(PARAM_RAW, 'Alternative name', VALUE_OPTIONAL),
                            'aljobrole' => new external_value(PARAM_RAW, 'Alternative jobrole', VALUE_OPTIONAL),
                            'alemail' => new external_value(PARAM_RAW, 'Alternative email', VALUE_OPTIONAL),
                            'almobile' => new external_value(PARAM_RAW, 'Alternative mobile', VALUE_OPTIONAL),
                        )
                    )
                )
            )
        );
    }

    public function create_organization($organization) {
        global $DB;
        $context = context_system::instance();
        self::validate_context($context);
        $params = self::validate_parameters(self::create_organization_parameters(), array('organization' => $organization));
        $requiredparams = ['licensekey','fullname','shortname','orgsector','orgsegment','orgfieldofwork','hrfullname','hrjobrole','hremail'];
        try {
           // $object = json_decode(json_encode($params['organization'][0]), FALSE);
            foreach($requiredparams as $param) {
                if(empty($params['organization'][0][$param])) {
                    throw new moodle_exception(get_string('invalidvalue', 'local_organization', $param));
                }
            }
            foreach($params['organization'] as $organization) {
                $organization = (object)$organization;
                if ($DB->record_exists('local_organization', array('shortname' => trim($organization->shortname)), '*', IGNORE_MULTIPLE)) {
                    throw new moodle_exception(get_string('orgshortnametakenlp', 'local_organization', $organization->shortname));
                }
                if (!empty($organization->hremail) && !validate_email($organization->hremail)) {
                    throw new moodle_exception(get_string('hrrequiredvalidemail','local_organization'));
                }
                if (!empty(trim($organization->hrmobile)) && !is_numeric($organization->hrmobile)){
                    throw new moodle_exception(get_string('hrmobilerequirednumeric','local_organization'));
                }
                if (!empty(trim($organization->hrmobile)) && is_numeric(trim($organization->hrmobile)) && ( strlen(trim($organization->hrmobile)) < 5  || strlen(trim($organization->hrmobile)) > 10 )) {
                    throw new moodle_exception(get_string('hrmobileminimum5digitsallowed','local_organization'));
                }
                if (!empty(trim($organization->hrmobile)) && is_numeric(trim($organization->hrmobile)) && (strlen(trim($organization->hrmobile)) >= 5  &&  strlen(trim($organization->hrmobile)) <= 10) &&  !preg_match('/^[5-9][0-9]/',trim($organization->hrmobile))) {
                    throw new moodle_exception(get_string('hrmobilestartswith5','local_organization'));
                }
                if (!empty($organization->alemail) && !validate_email($organization->alemail)) {
                    throw new moodle_exception(get_string('alrequiredvalidemail', 'local_organization'));
                }
                if (!empty(trim($organization->almobile)) && !is_numeric($organization->almobile)){
                    throw new moodle_exception(get_string('almobilerequirednumeric','local_organization'));
                }
                if (!empty(trim($organization->almobile)) && is_numeric(trim($organization->almobile)) && ( strlen(trim($organization->almobile)) < 5  || strlen(trim($organization->almobile)) > 10 )) {
                    throw new moodle_exception(get_string('almobileminimum5digitsallowed','local_organization'));
                }
                if (!empty(trim($organization->almobile)) && is_numeric(trim($organization->almobile)) && (strlen(trim($organization->almobile)) >= 5  &&  strlen(trim($organization->almobile)) <= 10) &&  !preg_match('/^[5-9][0-9]/',trim($organization->almobile))) {
                    throw new moodle_exception(get_string('almobilestartswith5','local_organization'));
                }
                $organization->sectors = $organization->orgsector;
                $organization->segment = $organization->orgsegment;
                $organization->discount_percentage = 0;
                $return = (new local_organization\organization)->add_update_organization($organization);
                $status = get_string('orgcreatedsuccessfully', 'local_organization');
            }
        } catch(Exception $e){
			// throw new moodle_exception('Error in creating the organization');
			$return = 0;
            $status = $e->getMessage();
		}

		return ['id' => $return->id, 'message' => $status];
    }

    public function create_organization_returns() {
        return new external_function_parameters(
            array(
                'id' => new external_value(PARAM_INT, 'organization id'),
                'message' => new external_value(PARAM_TEXT, 'status')
            )
        );
        return new external_value(PARAM_INT, 'return');
    }

    public static function orgitems_list_parameters() {
        return new external_function_parameters([
            'options' => new external_value(PARAM_RAW, 'The paging data for the service'),
            'dataoptions' => new external_value(PARAM_RAW, 'The data for the service'),
            'offset' => new external_value(PARAM_INT, 'Number of items to skip from the begging of the result set',
                VALUE_DEFAULT, 0),
            'limit' => new external_value(PARAM_INT, 'Maximum number of results to return',
                VALUE_DEFAULT, 0),
            'contextid' => new external_value(PARAM_INT, 'contextid'),
            'filterdata' => new external_value(PARAM_RAW, 'filters applied')
        ]);
    }

    public static function orgitems_list($options, $dataoptions, $offset = 0, $limit = 0, $contextid, $filterdata) {
        global $DB, $PAGE, $CFG;
        require_login();
        $sitecontext = context_system::instance();
        $PAGE->set_context($sitecontext);
        $params = self::validate_parameters(
            self::orgitems_list_parameters(),
            [
                'options' => $options,
                'dataoptions' => $dataoptions,
                'offset' => $offset,
                'limit' => $limit,
                'contextid' => $contextid,
                'filterdata' => $filterdata,
            ]
        );
        $offset = $params['offset'];
        $limit = $params['limit'];
        $filtervalues = json_decode($filterdata);
        $orgid = json_decode($dataoptions)->orgid;
        $catid = json_decode($dataoptions)->catid;

        $stable = new \stdClass();
        $stable->thead = false;
        $stable->start = $offset;
        $stable->length = $limit;
        $itemlist = (new local_organization\organization)->get_listof_orgitems($stable, $filtervalues, $orgid, $catid);
        $totalcount = $itemlist['totalcount'];
        $data = array();
        if($totalcount > 0 ){
            $renderer = $PAGE->get_renderer('local_organization');
            $data = array_merge($data, $renderer->listof_orgitems($itemlist['itemslist'],$orgid,$catid));
            $nodata = false;
        } else {
            $nodata = true;
        }
       //var_dump($data); exit;
        return [
            'totalcount' => $totalcount,
            'length' => $totalcount,
            'filterdata' => $filterdata,
            'records' => $data,
            'options' => $options,
            'dataoptions' => $dataoptions,
            'url' => $CFG->wwwroot,
            'nodata' => $nodata,
            'nodatatext' => get_string('no_data_available', 'theme_academy')
        ];
    }

  /**
   * Returns description of method result value
   * @return external_description
   */
    public function orgitems_list_returns() {
        return new external_single_structure([
            'options' => new external_value(PARAM_RAW, 'The paging data for the service'),
            'dataoptions' => new external_value(PARAM_RAW, 'The data for the service'),
            'nodata' => new external_value(PARAM_BOOL, 'nodata'),
            'nodatatext' => new external_value(PARAM_RAW, 'nodatatext'),
            'totalcount' => new external_value(PARAM_INT, 'total number of competencies in result set'),
            'filterdata' => new external_value(PARAM_RAW, 'The data for the service'),
            'length' => new external_value(PARAM_RAW, 'total number of challenges in result set'),
            'records' => new external_multiple_structure(
                            new external_single_structure(
                                array(
                                    'id' => new external_value(PARAM_INT, 'id'),
                                    'name' => new external_value(PARAM_RAW, 'firstname'),
                                    'code' => new external_value(PARAM_RAW, 'code'),
                                    'startdate' => new external_value(PARAM_RAW, 'startdate'),
                                    'enddate' => new external_value(PARAM_RAW, 'enddate'),
                                    'enrolledcount'  => new external_value(PARAM_RAW, 'enrolledcount'),
                                )
                            )
            ),
            'request_view' => new external_value(PARAM_INT, 'request_view', VALUE_OPTIONAL),
        ]);
    }

    public function enrollment_search_parameters() {
        return new external_function_parameters(
            array(
                'type' => new external_value(PARAM_RAW, 'Type of the record', 0),
                'orgid' => new external_value(PARAM_INT, 'Organization ID', 0),
                'query' => new external_value(PARAM_RAW, 'query', 0),
            )
        );
    }
    
    public function enrollment_search($type,$orgid,$query) {
        global $DB, $PAGE;
        $sitecontext = context_system::instance();
        $PAGE->set_context($sitecontext);
        // Parameter validation.
        $params = self::validate_parameters(
            self::enrollment_search_parameters(),
            [
                'type' => $type,
                'orgid' => $orgid,
                'query' => $query,
            ]
        );
        $records = (new local_organization\organization)->org_enrolled_users($type,$orgid,$params);
       
        $totalcount = count($records);
        if($type=='add'){
           // var_dump($records); exit; 
            return ['options' => json_encode($records),
                'count' => count($records)];
        }
        if($type=='remove'){
            return ['options' => json_encode($records),
                'count' => count($records)];
        }
    }
    
    public function enrollment_search_returns() {
        return new external_single_structure([
            'options' => new external_value(PARAM_RAW, 'The paging data for the service'),
            'count' => new external_value(PARAM_RAW, 'The paging data for the service'),
        ]);
    }

    public static function partner_types_parameters() {
        return new external_function_parameters([
            'options' => new external_value(PARAM_RAW, 'The paging data for the service'),
            'dataoptions' => new external_value(PARAM_RAW, 'The data for the service'),
            'offset' => new external_value(PARAM_INT, 'Number of items to skip from the begging of the result set',
                VALUE_DEFAULT, 0),
            'limit' => new external_value(PARAM_INT, 'Maximum number of results to return',
                VALUE_DEFAULT, 0),
            'contextid' => new external_value(PARAM_INT, 'contextid'),
            'filterdata' => new external_value(PARAM_RAW, 'filters applied'),
        ]);
    }

    public static function partner_types($options, $dataoptions, $offset = 0, $limit = 0, $contextid, $filterdata) {
        global $DB, $PAGE, $CFG;
        require_login();
        $systemcontext = context_system::instance();
        $PAGE->set_context($systemcontext);
        $params = self::validate_parameters(
            self::partner_types_parameters(),
            [
                'options' => $options,
                'dataoptions' => $dataoptions,
                'offset' => $offset,
                'limit' => $limit,
                'contextid' => $contextid,
                'filterdata' => $filterdata,
            ]
        );
        $offset = $params['offset'];
        $limit = $params['limit'];
        $filtervalues = json_decode($filterdata);

        $stable = new \stdClass();
        $stable->thead = false;
        $stable->start = $offset;
        $stable->length = $limit;
        $data = (new local_organization\partnertypes)->getlistofpartnertypes($stable, $filtervalues);
        $totalcount = $data['totalorganizations'];
        return [
            'totalcount' => $totalcount,
            'length' => $totalcount,
            'filterdata' => $filterdata,
            'records' =>$data,
            'options' => $options,
            'dataoptions' => $dataoptions,
            'url' => $CFG->wwwroot,
        ];
    }

  /**
   * Returns description of method result value
   * @return external_description
   */

    public static function partner_types_returns() {
        return new external_single_structure([
          'totalcount' => new external_value(PARAM_INT, 'total number of challenges in result set'),
          'length' => new external_value(PARAM_RAW, 'total number of challenges in result set'),
          'records' => new external_single_structure(
                array(
                  'hastypes' => new external_multiple_structure(
                        new external_single_structure(
                            array(
                                'id' => new external_value(PARAM_INT, 'id'),
                                'Name' => new external_value(PARAM_RAW, 'name'),
                                'description' => new external_value(PARAM_RAW, 'description'),
                            )
                        )
                    ),
                    'request_view' => new external_value(PARAM_INT, 'request_view', VALUE_OPTIONAL),
                    'nocourses' => new external_value(PARAM_BOOL, 'nocourses', VALUE_OPTIONAL),
                    'totalorganizations' => new external_value(PARAM_INT, 'totalorganizations', VALUE_OPTIONAL),
                    'length' => new external_value(PARAM_INT, 'length', VALUE_OPTIONAL),
                )
            )
        ]);
    }

    public static function partnertypes_info_parameters() {
        return new external_function_parameters([
            'partnerid' => new external_value(PARAM_INT, 'partner id'),
        ]);
    }

    public static function partnertypes_info($partnerid) {
        global $DB, $PAGE, $CFG;
        require_login();
        $systemcontext = context_system::instance();
        $PAGE->set_context($systemcontext);
        $params = self::validate_parameters(
            self::partnertypes_info_parameters(),
            [
                'partnerid' => $partnerid,
            ]
        );

        $data = (new local_organization\partnertypes)->partnertypes_info($partnerid);

        return [
            'partnerinfo' => $data,
        ];
    }

  /**
   * Returns description of method result value
   * @return external_description
   */

    public static function partnertypes_info_returns() {
        return new external_single_structure([
          'partnerinfo' => new external_value(PARAM_RAW, 'Partner Types'),
        ]);
    }

    public static function deletepartnertypes_parameters() {
        return new external_function_parameters([
            'partnerid' => new external_value(PARAM_INT, 'partner id'),
        ]);
    }

    public static function deletepartnertypes($partnerid) {
        global $DB, $PAGE, $CFG;
        require_login();
        $systemcontext = context_system::instance();
        $PAGE->set_context($systemcontext);
        $params = self::validate_parameters(
            self::deletepartnertypes_parameters(),
            [
                'partnerid' => $partnerid,
            ]
        );

        if($DB->record_exists('local_organization',['partnertype'=>$partnerid])) {
            $update = $DB->execute('UPDATE {local_organization} SET partnertype = 0 WHERE partnertype = '.$partnerid);
        }
        $partnerid = $DB->delete_records('local_org_partnertypes', array('id' => $partnerid));



        return true;
    }

  /**
   * Returns description of method result value
   * @return external_description
   */

    public static function deletepartnertypes_returns() {
        return new external_value(PARAM_BOOL, 'return');
    }

    public static function get_partnertypes_parameters() {
        return new external_function_parameters([
            'isArabic' => new external_value(PARAM_RAW, 'isArabic', false),
        ]);
    }

    public static function get_partnertypes($isArabic) {
        global $DB, $PAGE, $CFG;
        require_login();
        $systemcontext = context_system::instance();
        $PAGE->set_context($systemcontext);
        $params = self::validate_parameters(
            self::get_partnertypes_parameters(),
            [
                'isArabic' => $isArabic,
            ]
        );

        $data = (new local_organization\partnertypes)->get_partnertypes($isArabic);

        return $data;
    }

  /**
   * Returns description of method result value
   * @return external_description
   */

    public static function get_partnertypes_returns() {
        return new external_multiple_structure(
            new external_single_structure(
                array(
                  'id' => new external_value(PARAM_INT, 'id'),
                  'Name' => new external_value(PARAM_RAW, 'Name'),
                  'Desc' => new external_value(PARAM_RAW, 'Desc'),
                  'AttachmentId' => new external_value(PARAM_RAW, 'AttachmentId'),
                )
            )
        );
    }

    public static function get_partners_parameters() {
        return new external_function_parameters([
            'isArabic' => new external_value(PARAM_RAW, 'isArabic', false),
            'partnerTypeId' => new external_value(PARAM_RAW, '0', VALUE_OPTIONAL),
        ]);
    }

    public static function get_partners($isArabic, $partnerTypeId=false) {
        global $DB, $PAGE, $CFG;
        require_login();
        $systemcontext = context_system::instance();
        $PAGE->set_context($systemcontext);
        $params = self::validate_parameters(
            self::get_partners_parameters(),
            [
                'isArabic' => $isArabic,
                'partnerTypeId' => $partnerTypeId,
            ]
        );

        $data = (new local_organization\partnertypes)->get_partners($isArabic, $partnerTypeId);

        return $data;
    }

  /**
   * Returns description of method result value
   * @return external_description
   */

    public static function get_partners_returns() {
        return new external_multiple_structure(
            new external_single_structure(
                array(
                  'id' => new external_value(PARAM_INT, 'id'),
                  'Rank' => new external_value(PARAM_INT, 'Rank', VALUE_OPTIONAL),
                  'Name' => new external_value(PARAM_RAW, 'Name'),
                  'Desc' => new external_value(PARAM_RAW, 'Desc'),
                  'AttachmentId' => new external_value(PARAM_RAW, 'AttachmentId'),
                  'PartnerTypeId' => new external_value(PARAM_RAW, 'PartnerTypeId', VALUE_OPTIONAL),
                  'PartnerTypeName' => new external_value(PARAM_RAW, 'PartnerTypeName'),
                )
            )
        );       
    }

    public static function partnerstatistics_parameters() {
        return new external_function_parameters([

        ]);
    }

    public static function partnerstatistics() {
        global $DB, $PAGE, $CFG;
        require_login();
        $systemcontext = context_system::instance();
        $PAGE->set_context($systemcontext);

        $data = (new local_organization\partnertypes)->get_partnertypes();
        // print_r(COUNT($data));exit;
        return ['cnt' => COUNT($data)];
    }

  /**
   * Returns description of method result value
   * @return external_description
   */

    public static function partnerstatistics_returns() {
        return new external_single_structure([
            'cnt' => new external_value(PARAM_INT, 'cnt', VALUE_OPTIONAL),
        ]);
    }


    public static function view_invoicesummary_parameters() {
        return new external_function_parameters([
            'options' => new external_value(PARAM_RAW, 'The paging data for the service'),
            'dataoptions' => new external_value(PARAM_RAW, 'The data for the service'),
            'offset' => new external_value(PARAM_INT, 'Number of items to skip from the begging of the result set',
                VALUE_DEFAULT, 0),
            'limit' => new external_value(PARAM_INT, 'Maximum number of results to return',
                VALUE_DEFAULT, 0),
            'contextid' => new external_value(PARAM_INT, 'contextid'),
            'filterdata' => new external_value(PARAM_RAW, 'filters applied'),
        ]);
    }
    public static function view_invoicesummary($options, $dataoptions, $offset = 0, $limit = 0, $contextid, $filterdata) {
        global $DB,$PAGE;
        require_login();
        $systemcontext = context_system::instance();
        $PAGE->set_context($systemcontext);
        $params = self::validate_parameters(
            self::view_invoicesummary_parameters(),
            [
                'options' => $options,
                'dataoptions' => $dataoptions,
                'offset' => $offset,
                'limit' => $limit,
                'contextid' => $contextid,
                'filterdata' => $filterdata,
            ]
        );
        $offset = $params['offset'];
        $limit = $params['limit'];
        $filtervalues = json_decode($filterdata);

        $stable = new \stdClass();
        $stable->thead = false;
        $stable->start = $offset;
        $stable->length = $limit;
        $data = (new local_organization\organization)->get_list_invoicesummary($stable, $filtervalues);
        $totalcount = $data['totalinvoices'];
        return [
            'totalcount' => $totalcount,
            'length' => $totalcount,
            'filterdata' => $filterdata,
            'records' =>$data,
            'options' => $options,
            'dataoptions' => $dataoptions,
            'nodata' => $nodata,
           
        ];
    }

  /**
   * Returns description of method result value
   * @return external_description
   */

    public static function view_invoicesummary_returns() {
        return new external_single_structure([
         'options' => new external_value(PARAM_RAW, 'The paging data for the service'),          
          'nodata' => new external_value(PARAM_BOOL, 'nodata'),
          'dataoptions' => new external_value(PARAM_RAW, 'The data for the service'),
          'totalcount' => new external_value(PARAM_INT, 'total number of challenges in result set'),
          'filterdata' => new external_value(PARAM_RAW, 'total number of challenges in result set'),
          'length' => new external_value(PARAM_RAW, 'total number of challenges in result set'),
          'records' => new external_single_structure(
                  array(
                      'hasinvoices' => new external_multiple_structure(
                          new external_single_structure(
                              array(
                                  'invoicenumber' => new external_value(PARAM_RAW, 'invoicenumber'),
                                  'invoicetype' => new external_value(PARAM_RAW, 'invoicetype'),
                                  'orgofficial' => new external_value(PARAM_RAW, 'orgofficial'),
                                  'learningtype' => new external_value(PARAM_RAW, 'learningtype'),
                                  'learningitem' => new external_value(PARAM_RAW, 'learningitem'),
                                  'amount' => new external_value(PARAM_RAW, 'amount'),
                                  'seats' => new external_value(PARAM_RAW, 'seats'),
                                  'invoicestatus' => new external_value(PARAM_RAW, 'invoicestatus'),
                                  'paymentstatus' => new external_value(PARAM_RAW, 'paymentstatus'),
                              )
                          )
                      ),
                      'request_view' => new external_value(PARAM_INT, 'request_view', VALUE_OPTIONAL),
                      'nocourses' => new external_value(PARAM_BOOL, 'nocourses', VALUE_OPTIONAL),
                      'totalinvoices' => new external_value(PARAM_INT, 'totalinvoices', VALUE_OPTIONAL),
                      'length' => new external_value(PARAM_INT, 'length', VALUE_OPTIONAL),
                  )
              )
        ]);
    }
}
