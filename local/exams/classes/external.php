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
 * local local_exams
 *
 * @package    local_exams
 * @copyright  2022 Revanth kumar grandhi <revanth.g@eabyas.com>
 */
 
defined('MOODLE_INTERNAL') || die;
use core_favourites\local\repository\favourite_repository;
use local_exams\local\cisi_services;
use local_notifications\notification;
use \core_favourites\local\entity\favourite;
use \core_favourites\local\repository\favourite_repository_interface;
use \core_favourites\local\service\user_favourite_service;
use local_trainingprogram\local\trainingprogram;
use tool_product\product;
require_once("$CFG->libdir/externallib.php");
require_once("$CFG->dirroot/local/exams/lib.php");
require_once("$CFG->dirroot/favourites/classes/local/service/user_favourite_service.php");
require_once("$CFG->dirroot/favourites/classes/local/repository/favourite_repository.php");
class local_exams_external extends external_api {

    public const TRAINING_PROGRAM = 1;
    public const EXAMS = 2;
    public const EVENTS = 3;
    public const GRIEVANCE = 4;
    public const LEARNINGTRACKS = 5;
    public const EXAMATTEMPT = 6;

    public static function exams_view_parameters() {
        return new external_function_parameters([
            'status' => new external_value(PARAM_RAW, 'The paging data for the service', VALUE_DEFAULT, 0),
            'options' => new external_value(PARAM_RAW, 'The paging data for the service', VALUE_OPTIONAL),
            'dataoptions' => new external_value(PARAM_RAW, 'The data for the service', VALUE_OPTIONAL),
            'offset' => new external_value(PARAM_INT, 'Number of items to skip from the begging of the result set',
                VALUE_DEFAULT, 0),
            'limit' => new external_value(PARAM_INT, 'Maximum number of results to return',
                VALUE_DEFAULT, 0),
            'contextid' => new external_value(PARAM_INT, 'contextid', VALUE_OPTIONAL),
            'filterdata' => new external_value(PARAM_RAW, 'filters applied', VALUE_OPTIONAL),
        ]);
    }

    public static function exams_view($status=false, $options=false, $dataoptions=false, $offset = 0, $limit = 0,$contextid=1, $filterdata=false) {
        global $DB, $PAGE, $CFG, $USER;
        require_login();
        $systemcontext = context_system::instance();
        $params = self::validate_parameters(
            self::exams_view_parameters(),
            [
                'options' => $options,
                'dataoptions' => $dataoptions,
                'offset' => $offset,
                'limit' => $limit,
                'filterdata' => $filterdata,
                'status' => $status,
            ]
        );
        $offset = $params['offset'];
        $limit = $params['limit'];
        $filtervalues = json_decode($params['filterdata']);
        $settings = external_settings::get_instance();
     
        if ($status==1) {
            $filtervalues->status = 1;
        } elseif($status==2) {
            $filtervalues->status = 2;
            $filtervalues->type = 'mobile';
        } elseif($status==3) {
            $filtervalues->status = 3;
        }

        $stable = new \stdClass();
        $stable->thead = false;
        $stable->start = $offset;
        $stable->length = $limit;
        $stable->mlang =  ($settings->get_lang()) ?  $settings->get_lang() :(($isArabic)?(($isArabic == 'true') ? 'ar' :'en') : null);

        // Parameters for My Exams tab
        $stable->orguserid = $USER->id;
        $stable->tablename = 'tool_org_order_seats';
        $stable->selectparams = ',tppmnt.approvalseats,tppmnt.availableseats';

        if(!is_siteadmin() && has_capability('local/organization:manage_trainee', $systemcontext)) {
            $data = (new local_exams\local\exams)->get_listof_userexams($stable, $filtervalues);
        } else {
            $data = (new local_exams\local\exams)->get_listof_exams($stable, $filtervalues);
        }

        $totalcount = !empty($data['totalexams']) ? $data['totalexams'] : 0;
        $manageactions = $data['manageactions'];
        $userview = $data['userview'];
        $examcompleted = $data['examcompleted'];
        // $grievance = $data['grievance'];

        if(!is_siteadmin() && has_capability('local/organization:manage_organizationofficial',$systemcontext)) {
            $orgoff = true;
        } else {
            $orgoff = false;
        }
        return [
            'totalcount' => $totalcount,
            'length' => $totalcount,
            'records' =>$data,
            'url' => $CFG->wwwroot,
            'manageactions' => $manageactions,
            'view_schedules' => has_capability('local/exams:veiw_exam_schedules', $systemcontext) ? true : false,
            'userview' => $userview,
            'userid' => $USER->id,
            // 'grievance' => $grievance,
            'orgoff' => $orgoff,
            'exams' => $data['exams'],
            'products' => !empty($data['products']) ? $data['products'] : [],
            'recommendedexams' => !empty($data['recommendedexams']) ? $data['recommendedexams'] : [],
        ];
    }

  /**
   * Returns description of method result value
   * @return external_description
   */

    public static function exams_view_returns() {
        return new external_single_structure([
           'recommendedexams' => new external_multiple_structure(
                new external_single_structure(
                    array(
                        'entityid' => new external_value(PARAM_INT, 'entityid'),
                        'entityname' => new external_value(PARAM_RAW, 'reservationid'),
                        'entitycode' => new external_value(PARAM_RAW, 'slotstart', VALUE_OPTIONAL),
                        'entitystart' => new external_value(PARAM_INT, 'startdate', VALUE_OPTIONAL),
                        'entityend' => new external_value(PARAM_INT, 'enddate', VALUE_OPTIONAL),
                        'price' => new external_value(PARAM_RAW, 'price', VALUE_OPTIONAL),
                    )
                )
            ),
            'products' => new external_multiple_structure(
                new external_single_structure(
                    array(
                        'id' => new external_value(PARAM_INT, 'payment id'),
                        'name' => new external_value(PARAM_RAW, 'training name'),
                        'courseid' => new external_value(PARAM_INT, 'courseid'),
                        'datebegin' => new external_value(PARAM_INT, 'startdate'),
                        'dateend' => new external_value(PARAM_INT, 'enddate'),
                        'purchasedseats' => new external_value(PARAM_INT, 'purchased seats'),
                        'approvalseats' => new external_value(PARAM_INT, 'approval seats'),
                        'availableseats' => new external_value(PARAM_INT, 'available seats'),
                        'enrollbtn' => new external_value(PARAM_RAW, 'training enrollbtn'),
                        'timelimit' => new external_value(PARAM_RAW, 'Time Limit'),
                        'offeringview' => new external_value(PARAM_RAW, 'offeringview'),
                        'referenceid' => new external_value(PARAM_INT, 'offeringview'),
                        'entityid' => new external_value(PARAM_INT, 'offeringview'),
                        'enrolledseats' => new external_value(PARAM_INT, 'enrolledseats'),
                    )
                )
            ),            
            'exams' => new external_multiple_structure(
                new external_single_structure(
                    array(
                        'id' => new external_value(PARAM_INT, 'id', VALUE_OPTIONAL),
                        'exam' => new external_value(PARAM_TEXT, 'exam', VALUE_OPTIONAL),
                        'code' => new external_value(PARAM_TEXT, 'code', VALUE_OPTIONAL),
                        'profilecode' => new external_value(PARAM_RAW, 'profilecode', VALUE_OPTIONAL),
                        'examduration' => new external_value(PARAM_RAW, 'examduration', VALUE_OPTIONAL),
                        'pricetype' => new external_value(PARAM_RAW, 'pricetype', VALUE_OPTIONAL),
                        'sellingprice' => new external_value(PARAM_INT, 'sellingprice', VALUE_OPTIONAL),
                        'examdescription' => new external_value(PARAM_RAW, 'Exam Description', VALUE_OPTIONAL),
                        'courseid' => new external_value(PARAM_INT, 'courseid', VALUE_OPTIONAL),
                        'language' => new external_value(PARAM_RAW, 'language', VALUE_OPTIONAL),
                        'userexamdate' => new external_value(PARAM_RAW, 'userexamdate', VALUE_OPTIONAL),
                        //'examstarttime' => new external_value(PARAM_RAW, 'userexamdate', VALUE_OPTIONAL),
                        'quizid' => new external_value(PARAM_INT, 'quizid', VALUE_OPTIONAL),
                        'locationstatus' => new external_value(PARAM_RAW, 'locationstatus', VALUE_OPTIONAL),
                        'maplocation' => new external_value(PARAM_RAW, 'certificateid', VALUE_OPTIONAL),
                        'name' => new external_value(PARAM_RAW, 'name', VALUE_OPTIONAL),
                        'completedon' => new external_value(PARAM_RAW, 'completedon', VALUE_OPTIONAL),
                        'usercompletedon' => new external_value(PARAM_RAW, 'completedon', VALUE_OPTIONAL),
                        'passinggrade' => new external_value(PARAM_RAW, 'passinggrade', VALUE_OPTIONAL),
                        'reservationid' => new external_value(PARAM_INT, 'reservationid', VALUE_OPTIONAL),
                        'type' => new external_value(PARAM_TEXT, 'type', VALUE_OPTIONAL),
                        //'noofquestions' => new external_value(PARAM_INT, 'noofquestions', VALUE_OPTIONAL),
                        'grievanceexist' => new external_value(PARAM_BOOL, 'grievanceexist', VALUE_OPTIONAL),
                        //'examstartdate' => new external_value(PARAM_INT, 'examstartdate', VALUE_OPTIONAL),
                        //'examenddate' => new external_value(PARAM_INT, 'examenddate', VALUE_OPTIONAL),
                        'certificateid' => new external_value(PARAM_RAW, 'certificateid', VALUE_OPTIONAL),
                        'level' => new external_value(PARAM_RAW, 'level', VALUE_OPTIONAL),
                        'ownedby' => new external_value(PARAM_RAW, 'ownedby', VALUE_OPTIONAL),
                        'profilescount' => new external_value(PARAM_RAW, 'profilescount', VALUE_OPTIONAL),
                        'purchase' => new external_value(PARAM_BOOL,'purchase',VALUE_OPTIONAL),
                        'gradeachieved' => new external_value(PARAM_BOOL,'gradeachieved',VALUE_OPTIONAL),
                        'showgrade' => new external_value(PARAM_INT,'showgrade',VALUE_OPTIONAL),
                        'showexamduration' => new external_value(PARAM_INT,'showexamdration',VALUE_OPTIONAL),
                        'examcompleted' => new external_value(PARAM_RAW, 'examcompleted', VALUE_OPTIONAL),
                        'completedon' => new external_value(PARAM_RAW, 'completedon', VALUE_OPTIONAL),
                        'checkfavornot' => new external_value(PARAM_RAW, 'checkfavornot', VALUE_OPTIONAL),
                        // 'sectorsinfo' => new external_multiple_structure(
                        //     new external_single_structure(
                        //         array(
                        //             'id' => new external_value(PARAM_INT, 'id'),
                        //             'name' => new external_value(PARAM_TEXT, 'name'),
                        //         )
                        //     )
                        // ), '', VALUE_OPTIONAL,
                        'grievance2' => new external_value(PARAM_RAW, 'user grievance', VALUE_OPTIONAL),
                    )
                )
            ),
            'url' => new external_value(PARAM_RAW, 'url'),
            'orgoff' => new external_value(PARAM_RAW, 'orgoff', VALUE_OPTIONAL),
            'manageactions' => new external_value(PARAM_BOOL, 'total number of challenges in result set', VALUE_OPTIONAL),
            'view_schedules' => new external_value(PARAM_BOOL, 'Access to view exam schedules', VALUE_OPTIONAL),
            'totalcount' => new external_value(PARAM_INT, 'total number of challenges in result set'),
            'userview' => new external_value(PARAM_RAW, 'user view', VALUE_OPTIONAL),
            'userid' => new external_value(PARAM_INT, 'userid', VALUE_OPTIONAL),
            // 'grievance' => new external_value(PARAM_RAW, 'user grievance', VALUE_OPTIONAL),
            'length' => new external_value(PARAM_RAW, 'total number of challenges in result set'),
            'records' => new external_single_structure(
                array(
                   'exams' => new external_multiple_structure(
                        new external_single_structure(
                            array(
                                'slno' => new external_value(PARAM_INT, 'slno', VALUE_OPTIONAL),
                                'id' => new external_value(PARAM_INT, 'id', VALUE_OPTIONAL),
                                'exam' => new external_value(PARAM_RAW, 'exam', VALUE_OPTIONAL),
                                'code' => new external_value(PARAM_RAW, 'code', VALUE_OPTIONAL),
                                'profilecode' => new external_value(PARAM_RAW, 'profilecode', VALUE_OPTIONAL),
                                'noofquestions' => new external_value(PARAM_RAW, 'noofquestions', VALUE_OPTIONAL),
                                'type' => new external_value(PARAM_RAW, 'type', VALUE_OPTIONAL),
                                'examprice' => new external_value(PARAM_RAW, 'examprice', VALUE_OPTIONAL),
                                'actualprice' => new external_value(PARAM_RAW, 'actualprice', VALUE_OPTIONAL),
                                'sellingprice' => new external_value(PARAM_RAW, 'sellingprice', VALUE_OPTIONAL),
                                'todayexam' => new external_value(PARAM_RAW, 'todayexam', VALUE_OPTIONAL),
                                'examduration' => new external_value(PARAM_RAW, 'examduration', VALUE_OPTIONAL),
                                'language' => new external_value(PARAM_RAW, 'language', VALUE_OPTIONAL),
                                'enddate' => new external_value(PARAM_RAW, 'enddate', VALUE_OPTIONAL),
                                'examdatetime' => new external_value(PARAM_RAW, 'examdatetime', VALUE_OPTIONAL),
                                'examleftdays' => new external_value(PARAM_RAW, 'examleftdays', VALUE_OPTIONAL),
                                'quizid' => new external_value(PARAM_INT, 'quizid', VALUE_OPTIONAL),
                                'examseats' => new external_value(PARAM_RAW, 'examseats', VALUE_OPTIONAL),
                                'certificateid' => new external_value(PARAM_RAW, 'certificateid', VALUE_OPTIONAL),
                                'maplocation' => new external_value(PARAM_RAW, 'certificateid', VALUE_OPTIONAL),
                                'name' => new external_value(PARAM_RAW, 'name', VALUE_OPTIONAL),
                                'quizpassgrade' => new external_value(PARAM_RAW, 'quizpassgrade', VALUE_OPTIONAL),
                                'grievanceexist' => new external_value(PARAM_BOOL, 'grievanceexist', VALUE_OPTIONAL),
                                'examdatetime' => new external_value(PARAM_RAW, 'examdatetime', VALUE_OPTIONAL),
                                'hasreservation' => new external_value(PARAM_BOOL, 'hasreservation', VALUE_OPTIONAL),
                                'examstarttime' => new external_value(PARAM_RAW, 'examstarttime', VALUE_OPTIONAL),
                                'examcompleted' => new external_value(PARAM_RAW, 'examcompleted', VALUE_OPTIONAL),
                                'completedon' => new external_value(PARAM_RAW, 'completedon', VALUE_OPTIONAL),
                                'locationstatus' => new external_value(PARAM_RAW, 'locationstatus', VALUE_OPTIONAL),
                                'passinggrade' => new external_value(PARAM_RAW, 'passinggrade', VALUE_OPTIONAL),
                                'completedtab' => new external_value(PARAM_RAW, 'completedtab', VALUE_OPTIONAL),
                                'orgoff' => new external_value(PARAM_RAW, 'orgoff', VALUE_OPTIONAL),
                                'examdatetimestamp' => new external_value(PARAM_RAW, 'examdatetimestamp', VALUE_OPTIONAL),
                                'reservationid' => new external_value(PARAM_INT, 'reservationid', VALUE_OPTIONAL),
                                'enrolled' => new external_value(PARAM_INT, 'enrolled', VALUE_OPTIONAL),
                                'level' => new external_value(PARAM_RAW, 'level', VALUE_OPTIONAL),
                                'ownedby' => new external_value(PARAM_RAW, 'ownedby', VALUE_OPTIONAL),
                                'profileid' => new external_value(PARAM_INT, 'profileid', VALUE_OPTIONAL),
                                'profilescount' => new external_value(PARAM_RAW, 'profilescount', VALUE_OPTIONAL),
                                'instructions' => new external_value(PARAM_RAW, 'instructions', VALUE_OPTIONAL),
                                'hascertificate' => new external_value(PARAM_BOOL,'hascertificate',VALUE_OPTIONAL),
                                'gradeachieved' => new external_value(PARAM_BOOL,'gradeachieved',VALUE_OPTIONAL),
                                'purchase' => new external_value(PARAM_BOOL,'purchase',VALUE_OPTIONAL),
                                'showgrade' => new external_value(PARAM_INT,'showgrade',VALUE_OPTIONAL),
                                'showexamduration' => new external_value(PARAM_INT,'showexamdration',VALUE_OPTIONAL),
                                'purchasedprofile' => new external_value(PARAM_RAW,'purchasedprofile',VALUE_OPTIONAL),
                                'attemptnumber'  => new external_value(PARAM_RAW,'attempt',VALUE_OPTIONAL),
                                'result'  => new external_value(PARAM_RAW,'attempt',VALUE_OPTIONAL),
                                'scheduleid' => new external_value(PARAM_INT,'scheduleid',VALUE_OPTIONAL),
                                'scheduleaction' => new external_value(PARAM_BOOL,'scheduleaction',VALUE_OPTIONAL),
                                'checkfavornot' => new external_value(PARAM_RAW,'checkfavornot',VALUE_OPTIONAL),
                                'courseid' => new external_value(PARAM_RAW,'courseid',VALUE_OPTIONAL),
                                'grievance2' => new external_value(PARAM_RAW, 'user grievance', VALUE_OPTIONAL),
                            )
                        )
                    ),
                   'request_view' => new external_value(PARAM_INT, 'request_view', VALUE_OPTIONAL),
                   'nocourses' => new external_value(PARAM_BOOL, 'nocourses', VALUE_OPTIONAL),
                   'totalexams' => new external_value(PARAM_INT, 'totalexams', VALUE_OPTIONAL),
                   'length' => new external_value(PARAM_INT, 'length', VALUE_OPTIONAL),
                )
            ),
        ]);
    }

    public static function deleteexam_parameters(){
        return new external_function_parameters(
            array(
                'examid' => new external_value(PARAM_INT, 'examid', 0)
                )
        );

    }  
    public static function deleteexam($examid){
        global $DB;
        $params = self::validate_parameters(self::deleteexam_parameters(),
                                    ['examid' => $examid]);
        $context = context_system::instance();
        if($examid){
            $event = \local_exams\event\exam_deleted::create(array( 'context'=>$context, 'objectid' =>$examid));
            $event->trigger();

            $cpdid = $DB->get_field('local_cpd', 'id', ['examid' => $examid]);
            if (!empty($cpdid)) {
                $DB->delete_records('local_cpd', array('id' => $cpdid));
                $eventparams = array('context' => $context, 'objectid' => $cpdid);
                $event = local_cpd\event\cpd_deleted::create($eventparams);
                $event->trigger();
            }
            $examid = (new local_exams\local\exams)->delete_exam($examid);
        } else {
            throw new moodle_exception('Error in deleting');
            return false;
        }
    }   
    public static function deleteexam_returns() {
        return new external_value(PARAM_BOOL, 'return');
    }

    public static function publishexam_parameters(){
        return new external_function_parameters(
            array(
                'examid' => new external_value(PARAM_INT, 'examid', 0)
                )
        );

    }  
    public static function publishexam($examid){
        global $DB;
        $params = self::validate_parameters(self::publishexam_parameters(),
                                    ['examid' => $examid]);
        if($examid){
            $id = $DB->update_record('local_exams', array('status' => 1, 'id' => $examid));
            return true;
        }else {
            throw new moodle_exception('Error in deleting');
            return false;
        }
    }   
    public static function publishexam_returns() {
        return new external_value(PARAM_BOOL, 'return');
    }

    public static function exam_info_parameters() {
        return new external_function_parameters(
            array(
                'examid' => new external_value(PARAM_INT, 'examid', 0),
                )
        );
    }
    public static function exam_info($examid) {
        global $DB;
        require_login();
        $params = self::validate_parameters(self::exam_info_parameters(),
                                    ['examid' => $examid]);
        $data = (new local_exams\local\exams)->exam_info($examid);
        return [
            'options' => $data,
        ];
    }

  /**
   * Returns description of method result value
   * @return external_description
   */

    public static function exam_info_returns() {
        return new external_single_structure([
          'options' => new external_value(PARAM_RAW, 'The paging data for the service'),
        ]);
    }

    public static function sectors_info_parameters() {
        return new external_function_parameters(
            array(
                'examid' => new external_value(PARAM_INT, 'examid', 0),
                )
        );
    }
    public static function sectors_info($examid) {
        global $DB;
        require_login();
        $params = self::validate_parameters(self::sectors_info_parameters(),
                                    ['examid' => $examid]);
        $data = (new local_exams\local\exams)->sectors_info($examid);
        return [
            'options' => $data,
        ];
    }

  /**
   * Returns description of method result value
   * @return external_description
   */

    public static function sectors_info_returns() {
        return new external_single_structure([
          'options' => new external_value(PARAM_RAW, 'The paging data for the service'),
        ]);
    }

    public static function reviewexams_view_parameters() {
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

    public static function reviewexams_view($options, $dataoptions, $offset = 0, $limit = 0, $contextid, $filterdata) {
        global $DB, $PAGE, $CFG;
        require_login();
        $params = self::validate_parameters(
            self::reviewexams_view_parameters(),
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
        $data = (new local_exams\local\exams)->get_listof_reviewmode_exams($stable, $filtervalues);
        $totalcount = $data['totalexams'];
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

    public static function reviewexams_view_returns() {
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
                                  'slno' => new external_value(PARAM_INT, 'slno'),
                                  'id' => new external_value(PARAM_INT, 'id'),
                                  'exam' => new external_value(PARAM_RAW, 'exam'),
                                  'publishexams' => new external_value(PARAM_RAW, 'publishexams'),
                              )
                          )
                      ),                         
                      'request_view' => new external_value(PARAM_INT, 'request_view', VALUE_OPTIONAL),
                      'nocourses' => new external_value(PARAM_BOOL, 'nocourses', VALUE_OPTIONAL),
                      'totalexams' => new external_value(PARAM_INT, 'totalexams', VALUE_OPTIONAL),
                      'length' => new external_value(PARAM_INT, 'length', VALUE_OPTIONAL),
                  )
              )
        ]);
    }

    public static function userexams_view_parameters() {
        return new external_function_parameters([
            'options' => new external_value(PARAM_RAW, 'The paging data for the service'),
            'dataoptions' => new external_value(PARAM_RAW, 'The data for the service'),
            'offset' => new external_value(PARAM_INT, 'Number of items to skip from the begging of the result set',
                VALUE_DEFAULT, 0),
            'limit' => new external_value(PARAM_INT, 'Maximum number of results to return',
                VALUE_DEFAULT, 0),
            // 'contextid' => new external_value(PARAM_INT, 'contextid'),
            'filterdata' => new external_value(PARAM_RAW, 'filters applied'),
        ]);
    }

    public static function userexams_view($options, $dataoptions, $offset = 0, $limit = 0, $filterdata) {
        global $DB, $PAGE, $CFG;
        require_login();
        $params = self::validate_parameters(
            self::userexams_view_parameters(),
            [
                'options' => $options,
                'dataoptions' => $dataoptions,
                'offset' => $offset,
                'limit' => $limit,
                // 'contextid' => $contextid,
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
        $stable->class = 'userexams';
        $data = (new local_exams\local\exams)->get_listof_exams($stable, $filtervalues);
        $totalcount = $data['totalexams'];
        $userview = $data['userview'];
        return [
            'totalcount' => $totalcount,
            'length' => $totalcount,
            'filterdata' => $filterdata,
            'records' =>$data,
            'options' => $options,
            'dataoptions' => $dataoptions,
            'url' => $CFG->wwwroot,
            'userview' => $userview,
        ];
    }

  /**
   * Returns description of method result value
   * @return external_description
   */

    public static function userexams_view_returns() {
        return new external_single_structure([
          'options' => new external_value(PARAM_RAW, 'The paging data for the service'),
          'url' => new external_value(PARAM_RAW, 'url'),
          'userview' =>  new external_value(PARAM_BOOL, 'userview'),
          'dataoptions' => new external_value(PARAM_RAW, 'The data for the service'),
          'totalcount' => new external_value(PARAM_INT, 'total number of challenges in result set'),
          'filterdata' => new external_value(PARAM_RAW, 'total number of challenges in result set'),
          'length' => new external_value(PARAM_RAW, 'total number of challenges in result set'),
          'records' => new external_single_structure(
                  array(
                      'ahascourses' => new external_multiple_structure(
                          new external_single_structure(
                              array(
                                  'slno' => new external_value(PARAM_INT, 'slno'),
                                  'id' => new external_value(PARAM_INT, 'id'),
                                  'exam' => new external_value(PARAM_RAW, 'exam'),
                                  'noofquestions' => new external_value(PARAM_RAW, 'noofquestions'),
                                  'type' => new external_value(PARAM_RAW, 'type'),
                                  'actualprice' => new external_value(PARAM_RAW, 'actualprice'),
                                  'examduration' => new external_value(PARAM_RAW, 'examduration'),
                                  'status' => new external_value(PARAM_RAW, 'status'),
                                  'language' => new external_value(PARAM_RAW, 'language'),
                                  'enddate' => new external_value(PARAM_RAW, 'enddate'),
                                  'examleftdays' => new external_value(PARAM_RAW, 'examleftdays'),
                                  'quizid' => new external_value(PARAM_INT, 'quizid'),
                                  'examprice' => new external_value(PARAM_RAW, 'examprice'),
                                  'certificateid' => new external_value(PARAM_RAW, 'certificate', VALUE_OPTIONAL),
                                  'courseid' => new external_value(PARAM_RAW, 'courseid', VALUE_OPTIONAL),
                                  'checkfavornot' => new external_value(PARAM_RAW, 'checkfavornot', VALUE_OPTIONAL),

                              )
                          )
                      ),
                      'request_view' => new external_value(PARAM_INT, 'request_view', VALUE_OPTIONAL),
                      'nocourses' => new external_value(PARAM_BOOL, 'nocourses', VALUE_OPTIONAL),
                      'totalexams' => new external_value(PARAM_INT, 'totalexams', VALUE_OPTIONAL),
                      'length' => new external_value(PARAM_INT, 'length', VALUE_OPTIONAL),
                  )
              )
        ]);
    }
  /**
     * Returns description of method parameters.
     *
     * @return external_function_parameters.
     */
    public static function competencies_info_parameters() {
        return new external_function_parameters([
                'options' => new external_value(PARAM_RAW, 'The paging data for the service'),
                'dataoptions' => new external_value(PARAM_RAW, 'The data for the service'),
                'offset' => new external_value(PARAM_INT, 'Number of items to skip from the begging of the result set',
                    VALUE_DEFAULT, 0),
                'limit' => new external_value(PARAM_INT, 'Maximum number of results to return',
                    VALUE_DEFAULT, 0),
                 'filterdata' => new external_value(PARAM_RAW, 'The data for the service'),
        ]);
    }
 
    public static function competencies_info($options, $dataoptions, $offset = 0, $limit = 0, $filterdata) {
        global $OUTPUT, $CFG, $DB,$USER,$PAGE;
        $sitecontext = context_system::instance();
        require_login();
        $PAGE->set_url('/local/exams/index.php', array());
        $PAGE->set_context($sitecontext);
        // Parameter validation.
        $params = self::validate_parameters(
            self::competencies_info_parameters(),
            [
                'options' => $options,
                'dataoptions' => $dataoptions,
                'offset' => $offset,
                'limit' => $limit,
                'filterdata' => $filterdata
            ]
        );
        $data_object = (json_decode($dataoptions));
        $offset = $params['offset'];
        $limit = $params['limit'];
        $filtervalues = json_decode($filterdata);
        $stable = new \stdClass();
        $stable->thead = false;
        $stable->start = $offset;
        $stable->length = $limit;
        $stable->typeid = $data_object->typeid;
        $stable->type = $data_object->type;
        $competencies = (new local_exams\local\exams)->competency_info($stable,$filtervalues);
        return [
            'totalcount' => $competencies['totalcount'],
            'records' => $competencies['acompetencies'],
            'options' => $options,
            'dataoptions' => $dataoptions,
            'filterdata' => $filterdata,
            'examid' => $data_object->typeid,
            'type' => $data_object->type,
            'nodata' => get_string('nocompetencys','local_competency')
        ];
    }

    /**
     * Returns description of method result value.
     */
    public static function  competencies_info_returns() {
        return new external_single_structure([
            'options' => new external_value(PARAM_RAW, 'The paging data for the service'),
            'examid' => new external_value(PARAM_INT, 'Exam id'),
            'type' => new external_value(PARAM_RAW, 'Type'),
            'dataoptions' => new external_value(PARAM_RAW, 'The data for the service'),
            'totalcount' => new external_value(PARAM_INT, 'total number of competencies in result set'),
            'filterdata' => new external_value(PARAM_RAW, 'The data for the service'),
            'nodata' => new external_value(PARAM_RAW, 'The data for the service'),
            'records' => new external_multiple_structure(
                new external_single_structure(
                    array(
                        'id' => new external_value(PARAM_INT, 'competency id'),
                        'competencyname' => new external_value(PARAM_TEXT, 'competency name'),
                        'code' => new external_value(PARAM_TEXT, 'competency name'),
                        'fullname' => new external_value(PARAM_TEXT, 'competency type')
                    )
                )
            ),
        ]);
    }

    public static function exam_qualifications_parameters() {
        return new external_function_parameters([
            'options' => new external_value(PARAM_RAW, 'The paging data for the service', VALUE_OPTIONAL),
            'dataoptions' => new external_value(PARAM_RAW, 'The data for the service', VALUE_OPTIONAL),
            'offset' => new external_value(PARAM_INT, 'Number of items to skip from the begging of the result set',
                VALUE_DEFAULT, 0),
            'limit' => new external_value(PARAM_INT, 'Maximum number of results to return',
                VALUE_DEFAULT, 0),
            'contextid' => new external_value(PARAM_INT, 'contextid', VALUE_OPTIONAL),
            'filterdata' => new external_value(PARAM_RAW, 'filters applied', VALUE_OPTIONAL),
        ]);
      }

      public static function exam_qualifications($options=false, $dataoptions=false, $offset = 0, $limit = 0, $contextid=1, $filterdata=false) {
        global $DB, $PAGE;       
        
        // Parameter validation.
        $params = self::validate_parameters(
            self::exam_qualifications_parameters(),
            [
                'options' => $options,
                'dataoptions' => $dataoptions,
                'offset' => $offset,    
                'limit' => $limit,
                'contextid' => $contextid,
                'filterdata' => $filterdata
            ]
        );
        $offset = $params['offset'];
        $limit = $params['limit'];
        $filtervalues = json_decode($filterdata);
        
        $settings = external_settings::get_instance();
        
        $stable = new \stdClass();
        $stable->thead = false;
        $stable->start = $offset;
        $stable->length = $limit;
        $stable->mlang =  $settings->get_lang();  
        $data =  (new local_exams\local\exams)->get_listof_exam_qualification($stable, $filtervalues);
        $totalcount = $data['totalexams'];

        return [
            'totalcount' => $totalcount,
            'length' => $totalcount,
            'filterdata' => $filterdata,
            'numberofexams' => $data['totalexams'],
            'records' =>$data,
            'options' => $options,
            'dataoptions' => $dataoptions,
            'exams' => $data['hascourses'],
        ];
      }

      public static function exam_qualifications_returns() {
        return new external_single_structure([
            'exams' => new external_multiple_structure(
                new external_single_structure(
                    array(
                        'id' => new external_value(PARAM_INT, 'id'),
                        'exam' => new external_value(PARAM_RAW, 'exam'),
                        'sellingprice' => new external_value(PARAM_INT, 'sellingprice'),
                        'description'=>new external_value(PARAM_RAW,'description'),
                        'examdescription'=>new external_value(PARAM_RAW,'description'),
                        'hours'=>new external_value(PARAM_RAW,'hours'),
                        'durationstatus' => new external_value(PARAM_RAW,'durationstatus', VALUE_OPTIONAL),
                        'banking'=>new external_value(PARAM_RAW,'banking'),
                        'capitalmarket'=>new external_value(PARAM_RAW,'capitalmarket'),
                        'finance'=>new external_value(PARAM_RAW,'finance'),
                        'insurance'=>new external_value(PARAM_RAW,'insurance'),
                        'isenrolled'=>new external_value(PARAM_BOOL,'isenrolled'),
                        'examsectors' => new external_multiple_structure(
                            new external_single_structure(
                                array(
                                    'id' => new external_value(PARAM_INT, 'id'),
                                    'name' => new external_value(PARAM_RAW, 'name'),
                                )
                            )
                        ),
                    )
                )
            ),
            'options' => new external_value(PARAM_RAW, 'The paging data for the service'),
            'dataoptions' => new external_value(PARAM_RAW, 'The data for the service'),
            'totalcount' => new external_value(PARAM_INT, 'total number of challenges in result set'),
            'filterdata' => new external_value(PARAM_RAW, 'total number of challenges in result set'),
            'numberofexams' => new external_value(PARAM_INT, 'total number of challenges in result set', VALUE_OPTIONAL),
            'length' => new external_value(PARAM_RAW, 'total number of challenges in result set'),
            'records' => new external_single_structure(
                    array(
                        'hascourses' => new external_multiple_structure(
                            new external_single_structure(
                                array(
                                    'id' => new external_value(PARAM_INT, 'id'),
                                    'exam' => new external_value(PARAM_RAW, 'exam'),
                                    'actualprice' => new external_value(PARAM_RAW, 'actualprice'),
                                    'sellingprice' => new external_value(PARAM_RAW, 'sellingprice'),
                                    'city' => new external_value(PARAM_RAW, 'city'),
                                    'buildingname' => new external_value(PARAM_RAW, 'buildingname'),
                                    'seatingcapacity'=>new external_value(PARAM_RAW,'seatingcapacity'),
                                    'hall'=>new external_value(PARAM_RAW,'hall'),
                                    'description'=>new external_value(PARAM_RAW,'description'),
                                    'examdescription'=>new external_value(PARAM_RAW,'description'),
                                    'type'=>new external_value(PARAM_RAW,'type'),
                                    'hours'=>new external_value(PARAM_RAW,'hours'),
                                    'banking'=>new external_value(PARAM_RAW,'banking'),
                                    'capitalmarket'=>new external_value(PARAM_RAW,'capitalmarket'),
                                    'finance'=>new external_value(PARAM_RAW,'finance'),
                                    'insurance'=>new external_value(PARAM_RAW,'insurance'),
                                    'startdate'=>new external_value(PARAM_RAW,'startdate'),
                                    'enddate'=>new external_value(PARAM_RAW,'enddate'),
                                    'durationstatus' => new external_value(PARAM_RAW,'durationstatus', VALUE_OPTIONAL),
                                    'viewdetails' => new external_value(PARAM_URL,'viewdetails', VALUE_OPTIONAL),
                                )
                            )
                        ),
                        'request_view' => new external_value(PARAM_INT, 'request_view', VALUE_OPTIONAL),
                        'nocourses' => new external_value(PARAM_BOOL, 'nocourses', VALUE_OPTIONAL),
                        'noloadmore' => new external_value(PARAM_BOOL, 'noloadmore', VALUE_OPTIONAL),
                        'totalexams' => new external_value(PARAM_INT, 'totalposts', VALUE_OPTIONAL),
                        'length' => new external_value(PARAM_INT, 'length', VALUE_OPTIONAL),
                    )
                )

        ]);
    }
    //Vinod - Exams fake block for exam official - Starts//

    public static function apiexam_qualifications_parameters() {
        return new external_function_parameters([
            'isArabic' => new external_value(PARAM_RAW, 'isArabic', VALUE_DEFAULT, false),
            'pageNumber' => new external_value(PARAM_INT, 'pageNumber', VALUE_DEFAULT, 1),
            'pageSize' => new external_value(PARAM_INT, 'pageSize', VALUE_DEFAULT, 5),
            'query' => new external_value(PARAM_RAW, 'query', VALUE_DEFAULT, NULL),
            'sectorIds' => new external_value(PARAM_RAW, 'sectorIds', VALUE_DEFAULT, null),
            'isDescending' => new external_value(PARAM_RAW, 'isDescending', VALUE_DEFAULT, false),
            'CompetencyLevelId' => new external_value(PARAM_INT, 'CompetencyLevelId', VALUE_DEFAULT, null),
            'JobFamilyIds' => new external_value(PARAM_RAW, 'JobFamilyIds', VALUE_DEFAULT, null),
            'CompetencyId' => new external_value(PARAM_INT, 'CompetencyId', VALUE_DEFAULT, null),
        ]);
      }

      public static function apiexam_qualifications($isArabic, $pageNumber, $pageSize, $query, $sectorIds, $isDescending, $CompetencyLevelId, $JobFamilyIds, $CompetencyId) {
        global $DB, $PAGE;       
        $context = context_system::instance();
        // Parameter validation.
        $params = self::validate_parameters(
            self::apiexam_qualifications_parameters(),
            [
                'isArabic' => $isArabic,
                'pageNumber' => $pageNumber,
                'pageSize' => $pageSize,
                'query' => $query,    
                'sectorIds' => $sectorIds,
                'isDescending' => $isDescending,
                'CompetencyLevelId' => $CompetencyLevelId,    
                'JobFamilyIds' => $JobFamilyIds,
                'CompetencyId' => $CompetencyId
            ]
        );

        $offset = ($pageNumber > 1) ? (($pageNumber-1)* $pageSize) : 0;
        $limit = $pageSize;
        $filtervalues = json_decode($filterdata);
    
        $stable = new \stdClass();
        $stable->thead = false;
        $stable->start = $offset;
        $stable->length = $limit;
        $stable->isarabic = $isArabic;
        $stable->query = $query;
        $stable->sectorids = $sectorIds;
        $stable->isdescending = $isDescending;
        $stable->competencylevelid = $CompetencyLevelId;
        $stable->jobfamilyids = $JobFamilyIds;
        $stable->competencyid = $CompetencyId;
        $data =  (new local_exams\local\exams)->get_listof_exam_qualification($stable, $filtervalues);

        if($data) {
            $event = \local_exams\event\success_response::create(
                array(
                'context'=>$context,
                'objectid' => 1,
                'other'=>array(
                    'Function'=>'local_exams_get_exams',
                    'Success'=>'Successfully Fetched the Response'
                )
                )
            );
            $event->trigger(); 

        } else {
            $event = \local_exams\event\error_response::create(
                array( 
                    'context'=>$context,
                    'objectid' =>0,
                    'other'=>array(
                        'Function'=>'local_exams_get_exams',
                        'Error'=>'Invalid Response Value Detected'
        
                    )
                    )
                );  
            $event->trigger();
        }

        return ['pageData'=>$data['hascourses'],'totalexamscount'=> $data['totalexams'],'pageSize'=>$pageSize,'pageNumber'=>$pageNumber];
      }

      public static function apiexam_qualifications_returns() {
        return new external_single_structure([
            'pageData' => new external_multiple_structure(
                new external_single_structure(
                    array(
                        'activityType' => new external_value(PARAM_RAW, 'activityType'),
                        'activityID' => new external_value(PARAM_INT, 'activityID'),
                        'name' => new external_value(PARAM_RAW, 'exam'),
                        'description' => new external_value(PARAM_RAW,'description'),
                        // 'date' => new external_value(PARAM_RAW,'date'),
                        // 'location' => new external_value(PARAM_RAW,'location'),
                        'detailsPageURL' => new external_value(PARAM_RAW, 'detailsPageURL'),
                        'competencyLevelId' => new external_value(PARAM_INT, 'competencyLevelId'),
                        'competencyLevelName' => new external_value(PARAM_RAW, 'competencyLevelName'),
                    )
                )
            ),
            'totalexamscount' => new external_value(PARAM_INT, 'totalexamscount', VALUE_OPTIONAL),
            'pageSize' => new external_value(PARAM_INT, 'pageSize', VALUE_OPTIONAL),
            'pageNumber' => new external_value(PARAM_INT, 'pageNumber', VALUE_OPTIONAL),
        ]);
    }

    public static function apiexam_qualificationdetails_parameters() {
        return new external_function_parameters([
            'examId' => new external_value(PARAM_INT, 'Exam id'),
            'isArabic' => new external_value(PARAM_RAW, 'isArabic', VALUE_DEFAULT),
            'type' => new external_value(PARAM_RAW, 'This parameter is for Mobile App', VALUE_DEFAULT, ''),
        ]);
      }

      public static function apiexam_qualificationdetails($examId, $isArabic = NULL, $type) {
        global $DB, $PAGE, $SESSION;
        // Parameter validation.
        $params = self::validate_parameters(
            self::apiexam_qualificationdetails_parameters(),
            [
                'examId' => $examId,
                'isArabic' => $isArabic,
                'type' => $type
            ]
        );
        
        $id = $examId;     
        $settings = external_settings::get_instance();
        
       
        $mlang =  ($settings->get_lang()) ?  $settings->get_lang() :(($isArabic)?(($isArabic == 'true') ? 'ar' :'en') : null);
       
        $data = (new local_exams\local\exams)->exam_qualification_details($id, 'api',$mlang);
        $examinfo = (new local_exams\local\exams)->exam_details($data, $mlang, $type);

        return $examinfo;
    }

    public static function apiexam_qualificationdetails_returns() {
        $type = optional_param('type', null, PARAM_RAW);

        if ($type != 'mobile') {
            return new external_single_structure([
                'id' => new external_value(PARAM_INT, 'id'),
                'examDescription' => new external_value(PARAM_RAW, 'examDescription'),
                'prerequisitesOfExams' => new external_multiple_structure(
                    new external_single_structure(
                        array(
                           
                            'name' => new external_value(PARAM_RAW, 'examname'),
                            'description' => new external_value(PARAM_RAW, 'description'),
                            'value' => new external_value(PARAM_INT, 'value'),
                        )
                    )
                ), '', VALUE_OPTIONAL,
                'prerequisitesOfCourses' => new external_multiple_structure(
                    new external_single_structure(
                        array(
                            'name' => new external_value(PARAM_RAW, 'examname'),
                            'description' => new external_value(PARAM_RAW, 'description'),
                            'value' => new external_value(PARAM_INT, 'value'),
                        )
                    )
                ), '', VALUE_OPTIONAL,
                'releasedate' => new external_value(PARAM_RAW, 'startdate'),
                'targetaudience' => new external_value(PARAM_RAW, 'targetaudience'),
                'isorgofficialortrainee' => new external_value(PARAM_BOOL, 'isorgofficialortrainee'),
                'checkfavornot' => new external_value(PARAM_BOOL, 'checkfavornot'),
                'userid' => new external_value(PARAM_BOOL, 'userid'),
                'examProfiles' => new external_multiple_structure(
                    new external_single_structure(
                        array(
                            'id' => new external_value(PARAM_INT, 'competency id'),
                            'code' => new external_value(PARAM_RAW, 'code'),
                            'name' => new external_value(PARAM_RAW, 'name'),
                            'title' => new external_value(PARAM_RAW, 'title'),
                            'successGrade' => new external_value(PARAM_FLOAT, 'successGrade'),
                            'totalScore' => new external_value(PARAM_INT, 'totalScore'),
                            'noOfQuestions' => new external_value(PARAM_INT, 'title'),
                            'durationInMinutes' => new external_value(PARAM_RAW, 'durationInMinutes'),
                            'durationstatus' => new external_value(PARAM_RAW, 'durationstatus'),
                            'language' => new external_value(PARAM_RAW, 'language'),
                            'profileOwners' => new external_value(PARAM_RAW, 'profileOwners'),
                            'targetAudience' => new external_value(PARAM_RAW, 'targetAudience'),
                            'attachmentLink' => new external_value(PARAM_RAW, 'attachmentLink'),
                            'attachmentId' => new external_value(PARAM_RAW, 'attachmentId'),
                            'certificateExpirationYears' => new external_value(PARAM_INT, 'certificateExpirationYears'),
                            'isExternalRegistration' => new external_value(PARAM_RAW, 'isExternalRegistration'),
                            'externalRegistrationUrl' => new external_value(PARAM_RAW, 'externalRegistrationUrl'),
                            'ownerOrganization' => new external_value(PARAM_RAW, 'ownerOrganization'),
                        )
                    )
                ), '', VALUE_OPTIONAL,
                'code' => new external_value(PARAM_RAW, 'code'),
                'name' => new external_value(PARAM_RAW, 'name'),
                'targetCategories' => new external_multiple_structure(
                    new external_single_structure(
                        array(
                            'familyname' => new external_value(PARAM_RAW, 'familyname'),
                            'description' => new external_value(PARAM_RAW, 'description'),
                            'value' => new external_value(PARAM_INT, 'value'),
                        )
                    )
                ), '', VALUE_OPTIONAL,
                'competenciesText' => new external_value(PARAM_RAW, 'competenciesText'),
                'targetCategoriesName' => new external_value(PARAM_RAW, 'targetCategoriesName'),
                'additionalPrerequisites' => new external_value(PARAM_RAW, 'additionalPrerequisites'),
                'sectors' => new external_multiple_structure(
                    new external_single_structure(
                        array(
                            'sectorname' => new external_value(PARAM_RAW, 'sectorname'),
                            'description' => new external_value(PARAM_RAW, 'description'),
                            'value' => new external_value(PARAM_INT, 'value'),
                        )
                    )
                ), '', VALUE_OPTIONAL,
                'examFees' => new external_value(PARAM_INT, 'examFees'),
                'certificateExpirationYears' => new external_value(PARAM_INT, 'certificateExpirationYears'),

                'examCompetecies' => new external_multiple_structure(
                    new external_single_structure(
                        array(
                            'code' => new external_value(PARAM_RAW, 'code'),
                            'description' => new external_value(PARAM_RAW, 'description'),
                            'name' => new external_value(PARAM_TEXT, 'name'),
                            'parentValue' => new external_value(PARAM_TEXT, 'parentValue'),
                            'typeId' => new external_value(PARAM_INT, 'typeId'),
                            'typeName' => new external_value(PARAM_RAW, 'typeName'),
                            'value' => new external_value(PARAM_INT, 'value'),
                        )
                    )
                ), '', VALUE_OPTIONAL,
                'sectorImage' => new external_value(PARAM_RAW, 'sectorImage'),
                'examJobFamily' => new external_multiple_structure(
                    new external_single_structure(
                        array(
                            'familyname' => new external_value(PARAM_RAW, 'familyname'),
                            'description' => new external_value(PARAM_RAW, 'description'),
                            'value' => new external_value(PARAM_INT, 'value'),
                        )
                    )
                ), '', VALUE_OPTIONAL,
                'competencyLevelCode' => new external_value(PARAM_RAW, 'competencyLevelCode'),
                'competencyLevelId' => new external_value(PARAM_RAW, 'competencyLevelId'),
                'competencyweights' => new external_value(PARAM_RAW, 'competencyweights'),
                'reservations' => new external_multiple_structure(
                    new external_single_structure(
                        array(
                            'reservationid' => new external_value(PARAM_INT, 'reservationid'),
                            'slotstart' => new external_value(PARAM_RAW, 'slotstart'),
                            'slotend' => new external_value(PARAM_RAW, 'slotend'),
                            'startdate' => new external_value(PARAM_RAW, 'startdate'),
                            'enddate' => new external_value(PARAM_RAW, 'enddate'),
                            'seats' => new external_value(PARAM_INT, 'seats'),
                            'hallname' => new external_value(PARAM_RAW, 'hallname'),
                            'fees' => new external_value(PARAM_INT, 'fees'),
                            'enrolled' => new external_value(PARAM_INT, 'enrolled'),
                            'product_variations' => new external_single_structure(
                                array(
                                    'product' => new external_value(PARAM_INT, 'product'),
                                    'variation' => new external_value(PARAM_INT, 'variation'),
                                    'category' => new external_value(PARAM_INT, 'category'),
                                )
                            ),
                        )
                    )
                ), '', VALUE_OPTIONAL,
                 'detailsPageURL' => new external_value(PARAM_URL, 'Exam detailsPageURL'),
            ]);
        } else {
            return new external_single_structure([
                'id' => new external_value(PARAM_INT, 'id'),
                'examDescription' => new external_value(PARAM_RAW, 'examDescription'),
                'examhalls' => new external_multiple_structure(
                    new external_single_structure(
                        array(
                            'id' => new external_value(PARAM_INT, 'id'),
                            'fullname' => new external_value(PARAM_RAW, 'fullname'),
                        )
                    )
                ), '', VALUE_OPTIONAL,
                'prerequisitesOfExams' => new external_multiple_structure(
                    new external_single_structure(
                        array(
                            'name' => new external_value(PARAM_RAW, 'examname'),
                            'description' => new external_value(PARAM_RAW, 'description'),
                            'value' => new external_value(PARAM_INT, 'value'),
                        )
                    )
                ), '', VALUE_OPTIONAL,
                'prerequisitesOfCourses' => new external_multiple_structure(
                    new external_single_structure(
                        array(
                            'name' => new external_value(PARAM_RAW, 'examname'),
                            'description' => new external_value(PARAM_RAW, 'description'),
                            'value' => new external_value(PARAM_INT, 'value'),
                        )
                    )
                ), '', VALUE_OPTIONAL,
                'releasedate' => new external_value(PARAM_RAW, 'startdate'),
                'targetaudience' => new external_value(PARAM_RAW, 'targetaudience'),
                'isorgofficialortrainee' => new external_value(PARAM_BOOL, 'isorgofficialortrainee'),
                 'checkfavornot' => new external_value(PARAM_BOOL, 'checkfavornot'),
                 'userid' => new external_value(PARAM_BOOL, 'userid'),
                'examProfiles' => new external_multiple_structure(
                    new external_single_structure(
                        array(
                            'id' => new external_value(PARAM_INT, 'competency id'),
                            'code' => new external_value(PARAM_RAW, 'code'),
                            'name' => new external_value(PARAM_RAW, 'name'),
                            'title' => new external_value(PARAM_RAW, 'title'),
                            'successGrade' => new external_value(PARAM_FLOAT, 'successGrade'),
                            'totalScore' => new external_value(PARAM_INT, 'totalScore'),
                            'noOfQuestions' => new external_value(PARAM_INT, 'title'),
                            'durationInMinutes' => new external_value(PARAM_RAW, 'durationInMinutes'),
                            'durationstatus' => new external_value(PARAM_RAW, 'durationstatus'),
                            'language' => new external_value(PARAM_RAW, 'language'),
                            'profileOwners' => new external_value(PARAM_RAW, 'profileOwners'),
                            'targetAudience' => new external_value(PARAM_RAW, 'targetAudience'),
                            'attachmentLink' => new external_value(PARAM_RAW, 'attachmentLink'),
                            'attachmentId' => new external_value(PARAM_RAW, 'attachmentId'),
                            'certificateExpirationYears' => new external_value(PARAM_INT, 'certificateExpirationYears'),
                            'isExternalRegistration' => new external_value(PARAM_RAW, 'isExternalRegistration'),
                            'externalRegistrationUrl' => new external_value(PARAM_RAW, 'externalRegistrationUrl'),
                            'ownerOrganization' => new external_value(PARAM_RAW, 'ownerOrganization'),
                        )
                    )
                ), '', VALUE_OPTIONAL,
                'code' => new external_value(PARAM_RAW, 'code'),
                'name' => new external_value(PARAM_RAW, 'name'),
                'targetCategories' => new external_multiple_structure(
                    new external_single_structure(
                        array(
                            'familyname' => new external_value(PARAM_RAW, 'familyname'),
                            'description' => new external_value(PARAM_RAW, 'description'),
                            'value' => new external_value(PARAM_INT, 'value'),
                        )
                    )
                ), '', VALUE_OPTIONAL,
                'competenciesText' => new external_value(PARAM_RAW, 'competenciesText'),
                'targetCategoriesName' => new external_value(PARAM_RAW, 'targetCategoriesName'),
                'additionalPrerequisites' => new external_value(PARAM_RAW, 'additionalPrerequisites'),
                'sectors' => new external_multiple_structure(
                    new external_single_structure(
                        array(
                            'sectorname' => new external_value(PARAM_RAW, 'sectorname'),
                            'description' => new external_value(PARAM_RAW, 'description'),
                            'value' => new external_value(PARAM_INT, 'value'),
                        )
                    )
                ), '', VALUE_OPTIONAL,
                'examFees' => new external_value(PARAM_INT, 'examFees'),
                'certificateExpirationYears' => new external_value(PARAM_INT, 'certificateExpirationYears'),

                'examCompetecies' => new external_multiple_structure(
                    new external_single_structure(
                        array(
                            'type' => new external_value(PARAM_RAW, 'typeId'),
                            'data' => new external_multiple_structure(
                                new external_single_structure(
                                    array(
                                    'typeId' => new external_value(PARAM_INT, 'typeId'),
                                    'name' => new external_value(PARAM_TEXT, 'name'),
                                    'description' => new external_value(PARAM_RAW, 'description'),
                                    'id' => new external_value(PARAM_INT, 'value'),
                                    'level' => new external_value(PARAM_INT, 'level'),
                                    )
                                )
                            ), '', VALUE_OPTIONAL,
                        )
                    )
                ), '', VALUE_OPTIONAL,
                'sectorImage' => new external_value(PARAM_RAW, 'sectorImage'),
                'examJobFamily' => new external_multiple_structure(
                    new external_single_structure(
                        array(
                            'familyname' => new external_value(PARAM_RAW, 'familyname'),
                            'description' => new external_value(PARAM_RAW, 'description'),
                            'value' => new external_value(PARAM_INT, 'value'),
                        )
                    )
                ),
                'competencyLevelCode' => new external_value(PARAM_RAW, 'competencyLevelCode'),
                'competencyLevelId' => new external_value(PARAM_RAW, 'competencyLevelId'),
                'competencyweights' => new external_value(PARAM_RAW, 'competencyweights'),
                'reservations' => new external_multiple_structure(
                    new external_single_structure(
                        array(
                            'reservationid' => new external_value(PARAM_INT, 'reservationid'),
                            'slotstart' => new external_value(PARAM_RAW, 'slotstart'),
                            'slotend' => new external_value(PARAM_RAW, 'slotend'),
                            'startdate' => new external_value(PARAM_RAW, 'startdate'),
                            'enddate' => new external_value(PARAM_RAW, 'enddate'),
                            'seats' => new external_value(PARAM_INT, 'seats'),
                            'hallname' => new external_value(PARAM_RAW, 'hallname'),
                            'fees' => new external_value(PARAM_INT, 'fees'),
                            'enrolled' => new external_value(PARAM_INT, 'enrolled'),
                            'product_variations' => new external_single_structure(
                                array(
                                    'product' => new external_value(PARAM_INT, 'product'),
                                    'variation' => new external_value(PARAM_INT, 'variation'),
                                    'category' => new external_value(PARAM_INT, 'category'),
                                )
                            ),
                        )
                    )
                ), '', VALUE_OPTIONAL,
                 'detailsPageURL' => new external_value(PARAM_URL, 'Exam detailsPageURL'),
            ]);
        }
    }

    public static function examofficialblock_parameters() {
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
    public static function examofficialblock($options, $dataoptions, $offset = 0, $limit = 0, $contextid, $filterdata) {
        global $DB, $PAGE, $CFG;
        require_login();
        $params = self::validate_parameters(
            self::examofficialblock_parameters(),
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
        $data = (new local_exams\local\exams)->listof_examofficial_blockdata($stable, $filtervalues);
        $totalcount = $data['totalexams'];
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
    public static function examofficialblock_returns() {
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
                                'examid' => new external_value(PARAM_INT, 'id'),
                                'examname' => new external_value(PARAM_RAW, 'examname'),
                                'examdatetime' => new external_value(PARAM_RAW, 'examdatetime'),
                                'location' => new external_value(PARAM_RAW, 'location'),
                            )
                        )
                    ),
                    'completedexams' => new external_multiple_structure(
                        new external_single_structure(
                            array(
                                'examid' => new external_value(PARAM_INT, 'id'),
                                'examname' => new external_value(PARAM_RAW, 'examname'),
                                'examdatetime' => new external_value(PARAM_RAW, 'examdatetime'),
                                'location' => new external_value(PARAM_RAW, 'location'),
                            )
                        )
                    ),  

                    'request_view' => new external_value(PARAM_INT, 'request_view', VALUE_OPTIONAL),
                    'nocourses' => new external_value(PARAM_BOOL, 'nocourses', VALUE_OPTIONAL),
                    'totalprograms' => new external_value(PARAM_INT, 'totalprograms', VALUE_OPTIONAL),
                    'length' => new external_value(PARAM_INT, 'length', VALUE_OPTIONAL),
                )
            )
        ]);
    }
    //Vinod - Exams fake block for exam official - Ends//   

  
    public static function listofhalls_parameters() {
        return new external_function_parameters([
            'query' => new external_value(PARAM_RAW, 'search query'),   
            'hallid' => new external_value(PARAM_INT, 'hall id'),
            'type' => new external_value(PARAM_RAW, 'type of the organization')
        ]);
    }
    public static function listofhalls($query, $hallid, $type) {
        global $PAGE;
        $params = array(
            'hallid' => $hallid,          
            'type' => $type,
            'query' => $query
        );
        $params = self::validate_parameters(self::listofhalls_parameters(), $params);

        switch($params['type']) {
            case 'schedulehalls':
                $schedulehalls = (new local_exams\local\exams)->get_listofhalls($params['query'], $params['hallid'], ['id', 'fullname']);
            break;
            case 'exam_requirements':
                $schedulehalls = (new local_exams\local\exams)->get_listof_exam_requirements($params['query']);
            break;
            case 'preparations_programs':
                $schedulehalls = (new local_exams\local\exams)->get_listof_preparations_programs($params['query']);
            break;
        }
        return ['status' => true, 'data' => $schedulehalls];
    }
    public static function listofhalls_returns() {
        return new external_single_structure(
            array(
                'status' => new external_value(PARAM_INT, 'status: true if success'),
                'data' => new external_multiple_structure(new external_single_structure(
                    array(
                        'id' => new external_value(PARAM_INT, 'status: true if success'),
                        'fullname' => new external_value(PARAM_RAW, 'status: true if success',VALUE_OPTIONAL))
                   )) 
             )
        );
    }    
     public static function ctypeajaxdatalist_parameters() {
        return new external_function_parameters([
            'type' => new external_value(PARAM_RAW, 'type of the organization'),
            'ctype' => new external_value(PARAM_RAW, 'type of the competencies'),
            'programid' => new external_value(PARAM_INT, 'Program id'),
        ]);
    }
    public static function ctypeajaxdatalist($type,$ctype,$programid) {
        global $PAGE;
        $params = array(    
            'type' => $type,
            'ctype' => $ctype,
            'programid' => $programid

        );
        $params = self::validate_parameters(self::ctypeajaxdatalist_parameters(), $params);
        switch($params['type']) {
            case 'program_competencylevel':
                $data = (new local_exams\local\exams)->get_listof_competencies($params['programid'],$params['ctype'], ['id', 'fullname']);
            break;

            case 'programusers':
                $data = (new local_exams\local\exams)->get_listof_trainerusers($params['programid'], ['id', 'fullname']);
            break;
        }
        return ['status' => true, 'data' => $data];
    }
    public static function ctypeajaxdatalist_returns() {
        return new external_single_structure(
            array(
                'status' => new external_value(PARAM_INT, 'status: true if success'),
                'data' => new external_multiple_structure(new external_single_structure(
                    array(
                        'id' => new external_value(PARAM_INT, 'status: true if success'),
                        'fullname' => new external_value(PARAM_RAW, 'status: true if success'))
                   )) 
             )
        );
    }

    public function grievance_info_parameters() {
        return new external_function_parameters([
            'examid' => new external_value(PARAM_RAW, 'The paging data for the service', VALUE_DEFAULT, 0),
            'options' => new external_value(PARAM_RAW, 'The paging data for the service', VALUE_OPTIONAL),
            'dataoptions' => new external_value(PARAM_RAW, 'The data for the service', VALUE_OPTIONAL),
            'offset' => new external_value(PARAM_INT, 'Number of items to skip from the begging of the result set',
                VALUE_DEFAULT, 0),
            'limit' => new external_value(PARAM_INT, 'Maximum number of results to return',
                VALUE_DEFAULT, 0),
             'filterdata' => new external_value(PARAM_RAW, 'The data for the service', VALUE_OPTIONAL),
        ]);
    }

    public function grievance_info($examid=false, $options=false, $dataoptions=false, $offset = 0, $limit = 0, $filterdata=false, $context=false) {
        global $OUTPUT, $CFG, $DB,$USER,$PAGE;
        $sitecontext = context_system::instance();
        require_login();
        $PAGE->set_url('/local/exams/index.php', array());
        $PAGE->set_context($sitecontext);
        // Parameter validation.
        $params = self::validate_parameters(
            self::grievance_info_parameters(),
            [
                'options' => $options,
                'dataoptions' => $dataoptions,
                'offset' => $offset,
                'limit' => $limit,
                'filterdata' => $filterdata,
                'examid' => $examid
            ]
        );
        $data_object = (json_decode($dataoptions));
        $offset = $params['offset'];
        $limit = $params['limit'];
        $filtervalues = json_decode($filterdata);

        if ($examid>0) {
            $filtervalues->examid = $examid;
        }

        $stable = new \stdClass();
        $stable->thead = false;
        $stable->start = $offset;
        $stable->length = $limit;
        $grievance = (new local_exams\local\exams)->grievance_info($stable,$filtervalues);
        return [
            'totalcount' => $grievance['totalcount'],
            'records' => $grievance['records'],
            'options' => $options,
            'dataoptions' => $dataoptions,
            'filterdata' => $filterdata,
            'examid' => $filtervalues->examid,
            'nodata' =>  get_string('nogrievance','local_exams'),//$grievance['nodata'],
            'action' => (is_siteadmin() ||  has_capability('local/organization:manage_examofficial',$sitecontext) || has_capability('local/organization:manage_organizationofficial',$sitecontext)) ? true : false,
            'grievance' => $grievance['records'],
        ];
    }

    public function grievance_info_returns() {
        return new external_single_structure([
            'grievance' => new external_multiple_structure(
                new external_single_structure(
                    array(
                        'examid' => new external_value(PARAM_INT, 'examid'),
                        'userid' => new external_value(PARAM_INT, 'userid'),
                        'username' => new external_value(PARAM_RAW, 'username'),
                        'examname' => new external_value(PARAM_RAW, 'examname'),
                        'status' => new external_value(PARAM_RAW, 'status'),
                    )
                )
            ),
            'examid' => new external_value(PARAM_INT, 'Exam id'),
            'totalcount' => new external_value(PARAM_INT, 'total number of competencies in result set'),
            'nodata' => new external_value(PARAM_RAW, 'The data for the service'),
            'action' => new external_value(PARAM_BOOL, 'action'),
            'records' => new external_multiple_structure(
                new external_single_structure(
                    array(
                        'id' => new external_value(PARAM_INT, 'grievance id'),
                        'srno' => new external_value(PARAM_INT, 'srno'),
                        'examid' => new external_value(PARAM_INT, 'examid'),
                        'userid' => new external_value(PARAM_INT, 'userid'),
                        'username' => new external_value(PARAM_RAW, 'username'),
                        'examname' => new external_value(PARAM_RAW, 'examname'),
                        'status' => new external_value(PARAM_RAW, 'status'),
                        'action' => new external_value(PARAM_BOOL, 'action'),
                        'statusaction' => new external_value(PARAM_BOOL, 'action'),
                    )
                )
            ),
        ]);
    }

    public function grievancestatus_parameters(){
        return new external_function_parameters(
            array(
                'grievanceid' => new external_value(PARAM_INT, 'ID of the record', 0),
                'confirm' => new external_value(PARAM_BOOL, 'Confirm', false),
                'status' => new external_value(PARAM_INT, 'status of the record', 0),
            )
        );
    }

    public static function grievancestatus($grievanceid, $confirm, $status) {
        global $DB, $USER;
        try {
            if ($confirm) {
                $row['id'] = $grievanceid;
                $row['status'] = $status;
                $row['actionby'] = $USER->id;
                $row['realuser'] = ($USER->realuser) ? $USER->realuser :0; 
                $row['timemodified'] = time();
                $DB->update_record('local_exam_grievance', $row);
                $return = true;
            } else {
                $return = false;
            }
        } catch (dml_exception $ex) {
            print_error('statuserror', 'local_exams');
            $return = false;
        }
        return $return;
    }

    public static function grievancestatus_returns() {
        return new external_value(PARAM_BOOL, 'return');
    }

    public function view_grievance_parameters() {
        return new external_function_parameters(
            array(
                'greivanceid' => new external_value(PARAM_INT, 'greivanceid', 0),
                'examid' => new external_value(PARAM_INT, 'examid', 0),
                )
        );
    }

    public function view_grievance($greivanceid, $examid) {
        global $DB, $USER;
        $params = self::validate_parameters(self::view_grievance_parameters(),
                                    ['greivanceid' => $greivanceid, 'examid' => $examid]);                            
        $data = (new local_exams\local\exams)->grievance_details($greivanceid, $examid);
        return [
            'options' => $data,
        ];
    }

    public function view_grievance_returns() {
        return new external_single_structure([
            'options' => new external_value(PARAM_RAW, 'The paging data for the service'),
        ]);
    }

    public function enrollment_search_parameters() {
        return new external_function_parameters(
            array(
                'type' => new external_value(PARAM_RAW, 'Type of the record', 0),
                'profileid' => new external_value(PARAM_INT, 'ID of the record', 0),
                'query' => new external_value(PARAM_RAW, 'query', 0),
                'orgid' => new external_value(PARAM_INT, 'orgid', 0),
            )
        );
    }
    
    public function enrollment_search($type, $profileid, $query, $orgid) {
        global $DB, $PAGE;
        $sitecontext = context_system::instance();
        $PAGE->set_context($sitecontext);
        // Parameter validation.
        $params = self::validate_parameters(
            self::enrollment_search_parameters(),
            [
                'type' => $type,
                'profileid' => $profileid,
                'query' => $query,
                'orgid' => $orgid,
            ]
        );
        
        $records = (new local_exams\local\exams)->exam_enrolled_users_count($type, $profileid, $params);
        $options = (new local_exams\local\exams)->exam_enrolled_users($type, $profileid, $params);
       
        return['options' => json_encode($options),
                'count' => $records];
    }
    
    public function enrollment_search_returns() {
        return new external_single_structure([
            'options' => new external_value(PARAM_RAW, 'The paging data for the service'),
            'count' => new external_value(PARAM_RAW, 'The paging data for the service'),
        ]);
    }


    public static function viewexamusers_parameters() {
        return new external_function_parameters([
            'options' => new external_value(PARAM_RAW, 'The paging data for the service'),
            'dataoptions' => new external_value(PARAM_RAW, 'The data for the service'),
            'offset' => new external_value(PARAM_INT, 'Number of items to skip from the begging of the result set',
                VALUE_DEFAULT, 0),
            'limit' => new external_value(PARAM_INT, 'Maximum number of results to return',
                VALUE_DEFAULT, 0),
            'contextid' => new external_value(PARAM_INT, 'contextid', VALUE_OPTIONAL),
            'filterdata' => new external_value(PARAM_RAW, 'filters applied'),
        ]);
    }

    public static function viewexamusers($options, $dataoptions, $offset = 0, $limit = 0,$contextid=1, $filterdata) {
        global $DB, $PAGE, $CFG, $USER;
        require_login();
        $systemcontext = context_system::instance();
        $params = self::validate_parameters(
            self::exams_view_parameters(),
            [
                'options' => $options,
                'dataoptions' => $dataoptions,
                'offset' => $offset,
                'limit' => $limit,
                'filterdata' => $filterdata,
            ]
        );
        $offset = $params['offset'];
        $limit = $params['limit'];
        $filtervalues = json_decode($params['filterdata']);
        $filtervalues->examid = json_decode($params['dataoptions'])->examid;
        $filtervalues->profileid = json_decode($params['dataoptions'])->profileid;
        $stable = new \stdClass();
        $stable->thead = false;
        $stable->start = $offset;
        $stable->length = $limit;

        $data = (new local_exams\local\exams)->get_listof_examusers($stable, $filtervalues);

        $totalcount = $data['totalenrolements'];


        if(is_siteadmin() || has_capability('local/exams:exam_certificate_downloads',$systemcontext)) {
            $editt = true;
        } else {
            $editt = false;
        }
        return [
            'totalcount' => $totalcount,
            'length' => $totalcount,
            'filterdata' => $filterdata,
            'records' =>$data,
            'options' => $options,
            'dataoptions' => $dataoptions,
            'url' => $CFG->wwwroot,
            'entitytype' => 'exam',
            'editt'=>'editt',
            'examid' => $data['examid'],
            'certificateview' => (is_siteadmin() || has_capability('local/organization:manage_organizationofficial',$systemcontext) || has_capability('local/organization:manage_examofficial',$systemcontext)) ? true : false,
            'actionview' => (is_siteadmin() || has_capability('local/organization:manage_organizationofficial',$systemcontext) || has_capability('local/organization:assessment_operator_view', $systemcontext)) ? true : false
        ];
    }

  /**
   * Returns description of method result value
   * @return external_description
   */

    public static function viewexamusers_returns() {
        return new external_single_structure([
          'options' => new external_value(PARAM_RAW, 'The paging data for the service'),
          'url' => new external_value(PARAM_RAW, 'url'),
          'examid' => new external_value(PARAM_RAW, 'examid'),
          'entitytype' => new external_value(PARAM_RAW, 'entitytype'),
           'editt'  => new external_value(PARAM_RAW, 'editt'),
          'actionview' => new external_value(PARAM_RAW, 'actionview'),
          'dataoptions' => new external_value(PARAM_RAW, 'The data for the service'),
          'totalcount' => new external_value(PARAM_INT, 'total number of challenges in result set'),
          'filterdata' => new external_value(PARAM_RAW, 'total number of challenges in result set'),
          'length' => new external_value(PARAM_RAW, 'total number of challenges in result set'),
          'certificateview'=> new external_value(PARAM_RAW, 'entitytype'),
          'records' => new external_single_structure(
                array(
                   'hascourses' => new external_multiple_structure(
                        new external_single_structure(
                            array(
                                'id' => new external_value(PARAM_INT, 'id'),
                                'userid' => new external_value(PARAM_RAW, 'userid'),
                                'user' => new external_value(PARAM_RAW, 'examcompleted', VALUE_OPTIONAL),
                                'email' => new external_value(PARAM_RAW, 'email', VALUE_OPTIONAL),
                                'enrolledon' => new external_value(PARAM_RAW, 'enrolledon', VALUE_OPTIONAL),
                                'completeddate' => new external_value(PARAM_RAW, 'completeddate', VALUE_OPTIONAL),
                                'grade' => new external_value(PARAM_RAW, 'locationstatus', VALUE_OPTIONAL),
                                'profilecode' => new external_value(PARAM_RAW, 'profilecode', VALUE_OPTIONAL),
                                'examid' => new external_value(PARAM_INT, 'examid', VALUE_OPTIONAL),
                                'profileid' => new external_value(PARAM_INT, 'profileid', VALUE_OPTIONAL),
                                'examname' => new external_value(PARAM_RAW, 'examname', VALUE_OPTIONAL),
                                'replacorcanceluerid' => new external_value(PARAM_INT, 'replacorcanceluerid', VALUE_OPTIONAL),
                                'replacebuttonview' => new external_value(PARAM_RAW, 'replacebuttonview', VALUE_OPTIONAL),
                                'reschedulebuttonview' => new external_value(PARAM_RAW, 'replacebuttonview', VALUE_OPTIONAL),
                                'cancelbuttonview' => new external_value(PARAM_RAW, 'cancelbuttonview', VALUE_OPTIONAL),
                                'absentaction' => new external_value(PARAM_RAW, 'absentaction', VALUE_OPTIONAL),
                                'ownedby' => new external_value(PARAM_RAW, 'ownedby', VALUE_OPTIONAL),
                                'examprice' => new external_value(PARAM_RAW, 'examprice', VALUE_OPTIONAL),
                                'remainingdays' => new external_value(PARAM_INT, 'remainingdays', VALUE_OPTIONAL),
                                'replaceorcanceluseridnumber' => new external_value(PARAM_RAW, 'replaceorcanceluseridnumber', VALUE_OPTIONAL),  
                                'issiteadmin' => new external_value(PARAM_INT, 'issiteadmin', VALUE_OPTIONAL),
                                'replacementfee' => new external_value(PARAM_INT, 'replacementfee', VALUE_OPTIONAL),
                                'examdate' => new external_value(PARAM_RAW, 'examdate', VALUE_OPTIONAL),   
                                'productid' => new external_value(PARAM_INT, 'productid', VALUE_OPTIONAL),
                                'ispaidexam' => new external_value(PARAM_RAW, 'ispaidexam', VALUE_OPTIONAL),
                                'productprice' => new external_value(PARAM_RAW, 'productprice', VALUE_OPTIONAL),
                                'siteurl' => new external_value(PARAM_RAW, 'siteurl', VALUE_OPTIONAL),
                                'orgenrolled' => new external_value(PARAM_BOOL, 'orgenrolled', VALUE_OPTIONAL),
                                'orgofficialenrolled' => new external_value(PARAM_INT, 'orgofficialenrolled', VALUE_OPTIONAL),
                                'enrolledrole' => new external_value(PARAM_RAW, 'enrolledrole', VALUE_OPTIONAL),
                                'currentuserorgoff' => new external_value(PARAM_INT, 'currentuserorgoff', VALUE_OPTIONAL),
                                'enrolledbyadmin' => new external_value(PARAM_BOOL, 'enrolledbyadmin', VALUE_OPTIONAL),
                                'cancelbuttonoption' => new external_value(PARAM_BOOL, 'cancelbuttonoption', VALUE_OPTIONAL),
                                'certid' => new external_value(PARAM_RAW, 'certid', VALUE_OPTIONAL),
                                'scheduleoption' => new external_value(PARAM_BOOL, 'scheduleoption', VALUE_OPTIONAL),
                                'disablereschedulebutton' => new external_value(PARAM_BOOL, 'disablereschedulebutton', VALUE_OPTIONAL),
                                'hall' => new external_value(PARAM_RAW, 'hall', VALUE_OPTIONAL),
                                'policiesdisabled' => new external_value(PARAM_RAW, 'policiesdisabled', VALUE_OPTIONAL),
                                'disableallactions' => new external_value(PARAM_RAW, 'disableallactions', VALUE_OPTIONAL), 
                                'canapplypolicies' => new external_value(PARAM_RAW, 'canapplypolicies', VALUE_OPTIONAL),
                                'scheduleid' => new external_value(PARAM_INT, 'scheduleid', VALUE_OPTIONAL),
                                'waitingforapproval' => new external_value(PARAM_RAW, 'waitingforapproval', VALUE_OPTIONAL),
                                'attemptenrol' => new external_value(PARAM_BOOL, 'attemptenrol', VALUE_OPTIONAL),
                                'enrolltype' => new external_value(PARAM_INT, 'enrolltype', VALUE_OPTIONAL),
                                'havinggrade' => new external_value(PARAM_RAW, 'havinggrade', VALUE_OPTIONAL),
                                'scheduleid' => new external_value(PARAM_INT, 'scheduleid', VALUE_OPTIONAL), 
                                'cangenerateinvoice' => new external_value(PARAM_INT, 'cangenerateinvoice', VALUE_OPTIONAL), 
                                
                            )
                        )
                    ),
                   'request_view' => new external_value(PARAM_INT, 'request_view', VALUE_OPTIONAL),
                   'nocourses' => new external_value(PARAM_BOOL, 'nocourses', VALUE_OPTIONAL),
                   'totalexams' => new external_value(PARAM_INT, 'totalexams', VALUE_OPTIONAL),
                   'length' => new external_value(PARAM_INT, 'length', VALUE_OPTIONAL),
                )
            ),
        ]);
    }

    public static function exam_reservations_parameters() {
        return new external_function_parameters([
            'options' => new external_value(PARAM_RAW, 'The paging data for the service'),
            'dataoptions' => new external_value(PARAM_RAW, 'The data for the service'),
            'offset' => new external_value(PARAM_INT, 'Number of items to skip from the begging of the result set',
                VALUE_DEFAULT, 0),
            'limit' => new external_value(PARAM_INT, 'Maximum number of results to return',
                VALUE_DEFAULT, 0),
            'contextid' => new external_value(PARAM_INT, 'contextid', VALUE_OPTIONAL),
            'filterdata' => new external_value(PARAM_RAW, 'filters applied'),
        ]);
    }

    public static function exam_reservations($options, $dataoptions, $offset = 0, $limit = 0,$contextid=1, $filterdata) {
        global $DB, $PAGE, $CFG, $USER;
        require_login();
        $systemcontext = context_system::instance();
        $params = self::validate_parameters(
            self::exam_reservations_parameters(),
            [
                'options' => $options,
                'dataoptions' => $dataoptions,
                'offset' => $offset,
                'limit' => $limit,
                'filterdata' => $filterdata,
            ]
        );
        $offset = $params['offset'];
        $limit = $params['limit'];

        $filtervalues = (array)json_decode($params['filterdata']);
        $filtervalues[examid] = json_decode($params['dataoptions'])->examid;
        $filtervalues[reservationid] = json_decode($params['dataoptions'])->reservationid;

        $stable = new \stdClass();
        $stable->thead = false;
        $stable->start = $offset;
        $stable->length = $limit;

        $data = (new local_exams\local\exams)->get_examreservations($stable, $filtervalues);

        $totalcount = $data['totalreservations'];
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

    public static function exam_reservations_returns() {
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
                                'reservationid' => new external_value(PARAM_INT, 'reservationid'),
                                'slotstart' => new external_value(PARAM_RAW, 'slotstart', VALUE_OPTIONAL),
                                'slotend' => new external_value(PARAM_RAW, 'slotend', VALUE_OPTIONAL),
                                'reservestartdate' => new external_value(PARAM_RAW, 'reservestartdate', VALUE_OPTIONAL),
                                'reserveenddate' => new external_value(PARAM_RAW, 'reserveenddate', VALUE_OPTIONAL),
                                'seats' => new external_value(PARAM_RAW, 'seats', VALUE_OPTIONAL),

                                'buildingname' => new external_value(PARAM_RAW, 'buildingname', VALUE_OPTIONAL),
                                'hallname' => new external_value(PARAM_RAW, 'hallname', VALUE_OPTIONAL),
                                'locationstatus' => new external_value(PARAM_RAW, 'locationstatus', VALUE_OPTIONAL),
                                'location' => new external_value(PARAM_RAW, 'location', VALUE_OPTIONAL),
                                'enrolled' => new external_value(PARAM_RAW, 'enrolled', VALUE_OPTIONAL),
                                'available' => new external_value(PARAM_RAW, 'available', VALUE_OPTIONAL),
                                'availablestatus' => new external_value(PARAM_RAW, 'availablestatus', VALUE_OPTIONAL),
                                'examseats' => new external_value(PARAM_RAW, 'examseats', VALUE_OPTIONAL),
                                'examid' => new external_value(PARAM_RAW, 'examid', VALUE_OPTIONAL),
                                'fees' => new external_value(PARAM_RAW, 'fees', VALUE_OPTIONAL),
                                'purchasedseats' => new external_value(PARAM_RAW, 'purchasedseats', VALUE_OPTIONAL),
                                'availableseats' => new external_value(PARAM_RAW, 'availableseats', VALUE_OPTIONAL),
                                'approvedseats' => new external_value(PARAM_RAW, 'approvedseats', VALUE_OPTIONAL),
                                'examselectbtn' => new external_value(PARAM_RAW, 'examselectbtn', VALUE_OPTIONAL),
                                'exambooknowbtn' => new external_value(PARAM_RAW, 'exambooknowbtn', VALUE_OPTIONAL),
                                'examhallreservation' => new external_value(PARAM_RAW, 'exambooknowbtn', VALUE_OPTIONAL),
                            )
                        )
                    ),
                   'request_view' => new external_value(PARAM_INT, 'request_view', VALUE_OPTIONAL),
                   'nocourses' => new external_value(PARAM_BOOL, 'nocourses', VALUE_OPTIONAL),
                   'totalexams' => new external_value(PARAM_INT, 'totalexams', VALUE_OPTIONAL),
                   'length' => new external_value(PARAM_INT, 'length', VALUE_OPTIONAL),
                )
            ),
        ]);
    }

   public static function exam_userreservations_parameters() {
        return new external_function_parameters([
            'options' => new external_value(PARAM_RAW, 'The paging data for the service'),
            'dataoptions' => new external_value(PARAM_RAW, 'The data for the service'),
            'offset' => new external_value(PARAM_INT, 'Number of items to skip from the begging of the result set',
                VALUE_DEFAULT, 0),
            'limit' => new external_value(PARAM_INT, 'Maximum number of results to return',
                VALUE_DEFAULT, 0),
            'contextid' => new external_value(PARAM_INT, 'contextid', VALUE_OPTIONAL),
            'filterdata' => new external_value(PARAM_RAW, 'filters applied'),
        ]);
    }

    public static function exam_userreservations($options, $dataoptions, $offset = 0, $limit = 0,$contextid=1, $filterdata) {
        global $DB, $PAGE, $CFG, $USER;
        $systemcontext = context_system::instance();
        $params = self::validate_parameters(
            self::exam_userreservations_parameters(),
            [
                'options' => $options,
                'dataoptions' => $dataoptions,
                'offset' => $offset,
                'limit' => $limit,
                'filterdata' => $filterdata,
            ]
        );
        $offset = $params['offset'];
        $limit = $params['limit'];

        $filtervalues = (array)json_decode($params['filterdata']);
        $filtervalues[examid] = json_decode($params['dataoptions'])->examid;

        $stable = new \stdClass();
        $stable->thead = false;
        $stable->start = $offset;
        $stable->length = $limit;

        $data = (new local_exams\local\exams)->get_userexamreservations($stable, $filtervalues);

        $totalcount = $data['totalreservations'];
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

    public static function exam_userreservations_returns() {
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
                                'reservationid' => new external_value(PARAM_INT, 'reservationid'),
                                'slotstart' => new external_value(PARAM_RAW, 'slotstart', VALUE_OPTIONAL),
                                'slotend' => new external_value(PARAM_RAW, 'slotend', VALUE_OPTIONAL),
                                'reservestartdate' => new external_value(PARAM_RAW, 'reservestartdate', VALUE_OPTIONAL),
                                'reserveenddate' => new external_value(PARAM_RAW, 'reserveenddate', VALUE_OPTIONAL),
                                'seats' => new external_value(PARAM_RAW, 'seats', VALUE_OPTIONAL),
                                'buildingname' => new external_value(PARAM_RAW, 'buildingname', VALUE_OPTIONAL),
                                'hallname' => new external_value(PARAM_RAW, 'hallname', VALUE_OPTIONAL),
                                'locationstatus' => new external_value(PARAM_RAW, 'locationstatus', VALUE_OPTIONAL),
                                'location' => new external_value(PARAM_RAW, 'location', VALUE_OPTIONAL),
                                'enrolled' => new external_value(PARAM_RAW, 'enrolled', VALUE_OPTIONAL),
                                'available' => new external_value(PARAM_RAW, 'available', VALUE_OPTIONAL),
                                'fees' => new external_value(PARAM_RAW, 'fees', VALUE_OPTIONAL),
                                'availablestatus' => new external_value(PARAM_RAW, 'availablestatus', VALUE_OPTIONAL),
                                'examhallreservation' => new external_value(PARAM_RAW, 'examhallreservation', VALUE_OPTIONAL),
                                'reservationview' => new external_value(PARAM_RAW, 'reservationview', VALUE_OPTIONAL),
                                'userreservations' => new external_value(PARAM_RAW, 'userreservations', VALUE_OPTIONAL),

                                'product_variations' => new external_single_structure(
                                    array(
                                        'product' => new external_value(PARAM_RAW, 'product'),
                                        'variation' => new external_value(PARAM_RAW, 'variation'),
                                        'category' => new external_value(PARAM_RAW, 'category'),
                                    )
                                ),


                            )
                        )
                    ),
                   'request_view' => new external_value(PARAM_INT, 'request_view', VALUE_OPTIONAL),
                   'nocourses' => new external_value(PARAM_BOOL, 'nocourses', VALUE_OPTIONAL),
                   'totalexams' => new external_value(PARAM_INT, 'totalexams', VALUE_OPTIONAL),
                   'length' => new external_value(PARAM_INT, 'length', VALUE_OPTIONAL),
                )
            ),
        ]);
    }

    public static function exam_details_parameters() {
        return new external_function_parameters([
            'id' => new external_value(PARAM_INT, 'id'),
        ]);
      }

      public static function exam_details($id) {
        global $DB, $PAGE;       
        
        // Parameter validation.
        $params = self::validate_parameters(
            self::exam_details_parameters(),
            [
                'id' => $id
            ]
        );

        $settings = external_settings::get_instance();
        $mlang =  $settings->get_lang();
        $data = (new local_exams\local\exams)->trainee_exam_profile_view($id,$mlang);
        
        if($data) {
           return $data;
        } else {
            return 'No records found';
        }
      }

      public static function exam_details_returns() {
        return new external_single_structure([         
            'examid' => new external_value(PARAM_INT, 'examid'),
            'code' => new external_value(PARAM_RAW, 'code'),
            'examname' => new external_value(PARAM_RAW, 'name'),
            'description' => new external_value(PARAM_RAW, 'description'),
            'instructions' => new external_value(PARAM_RAW, 'instructions'),
            'nondisclosure' => new external_value(PARAM_RAW, 'nondisclosure'),
            'profileid' => new external_value(PARAM_INT, 'id'),
            'profilecode' => new external_value(PARAM_RAW, 'profilecode'),
            'cmid' => new external_value(PARAM_INT, 'cmid'),
            'registrationstartdate' => new external_value(PARAM_RAW, 'registrationstartdate'),
            'registrationenddate' => new external_value(PARAM_RAW, 'registrationenddate'),
            'passinggrade' => new external_value(PARAM_RAW, 'passinggrade'),
            'seatingcapacity' => new external_value(PARAM_RAW, 'seatingcapacity'),
            'duration' => new external_value(PARAM_RAW, 'duration'),
            'language' => new external_value(PARAM_RAW, 'language'),
            'todayexam' => new external_value(PARAM_BOOL, 'todayexam'),
            'examdate' => new external_value(PARAM_RAW, 'examdate'),
            'launch' => new external_value(PARAM_BOOL, 'examdate'),
            'showquestions' => new external_value(PARAM_INT, 'showquestions'),
            'questions' => new external_value(PARAM_INT, 'questions'),
            'showexamduration' => new external_value(PARAM_INT, 'showexamduration'),
            'launchbtnstatus' => new external_value(PARAM_RAW, 'launchbtnstatus'),
            'nextattempt' => new external_value(PARAM_RAW, 'launchbtnstatus', VALUE_OPTIONAL),
            'attemptid' => new external_value(PARAM_INT, 'attemptid', VALUE_OPTIONAL),
            'hall' => new external_value(PARAM_RAW, 'hall', VALUE_OPTIONAL),
            'attemptscompleted' => new external_multiple_structure(
                new external_single_structure(
                    array(
                        'attempt' => new external_value(PARAM_INT, 'attempt'),
                        'grade' => new external_value(PARAM_INT, 'grade'),
                        'hallname' => new external_value(PARAM_RAW, 'hallname'),
                        'timestart' => new external_value(PARAM_INT, 'timestart'),
                        'timefinish' => new external_value(PARAM_INT, 'timefinish'),
                    )
                )
            ), '', VALUE_OPTIONAL,
        ], '', VALUE_OPTIONAL,[]);
    }

    public static function exam_filters_parameters() {
        return new external_function_parameters([
            'isArabic' => new external_value(PARAM_RAW, 'isArabic', VALUE_DEFAULT, false),
            'Keyword' => new external_value(PARAM_RAW, 'Keyword', VALUE_DEFAULT, NULL),
            'sectorId' => new external_value(PARAM_RAW, 'sectorId', VALUE_DEFAULT, NULL),
            'JobFamilyId' => new external_value(PARAM_RAW, 'JobFamilyId', VALUE_DEFAULT, NULL),
            'JobRoleId' => new external_value(PARAM_RAW, 'JobRoleId', VALUE_DEFAULT, NULL),
            'CompetencyId' => new external_value(PARAM_RAW, 'CompetencyId', VALUE_DEFAULT, NULL),
        ]);
      }

      public static function exam_filters($isArabic, $Keyword, $sectorId, $JobFamilyId, $JobRoleId, $CompetencyId) {
        global $DB, $PAGE;       
        $context = context_system::instance();
        // Parameter validation.
        $params = self::validate_parameters(
            self::exam_filters_parameters(),
            [
                'isArabic' => $isArabic,
                'Keyword' => $Keyword,
                'sectorId' => $sectorId,
                'JobFamilyId' => $JobFamilyId,    
                'JobRoleId' => $JobRoleId,
                'CompetencyId' => $CompetencyId,
            ]
        );

        $stable = new \stdClass();
        $stable->thead = false;
        $stable->isarabic = $isArabic;
        $stable->sectorids = $sectorId;
        $stable->keyword = $Keyword;
        $stable->jobroleid = $JobRoleId;
        $stable->jobfamilyids = $JobFamilyId;
        $stable->competencyid = $CompetencyId;
        $data =  (new local_exams\local\exams)->get_exams($stable);
        if ($data){
            $event = \local_exams\event\success_response::create(
                array(
                'context'=>$context,
                'objectid' => 1,
                'other'=>array(
                    'Function'=>'local_exams_exam_filters',
                    'Success'=>'Successfully Fetched the Response'
                )
                )
            );
            $event->trigger(); 
            return $data['exams'];
        } else {
            $event = \local_exams\event\error_response::create(
                array( 
                    'context'=>$context,
                    'objectid' =>0,
                    'other'=>array(
                        'Function'=>'local_exams_exam_filters',
                        'Error'=>'Invalid Response Value Detected'
        
                    )
                    )
                );  
            $event->trigger();
        }

      
      }

      public static function exam_filters_returns() {
        return new external_multiple_structure(
            new external_single_structure(
                array(
                    'id' => new external_value(PARAM_INT, 'id'),
                    'name' => new external_value(PARAM_RAW, 'activityType'),
                    'Description' => new external_value(PARAM_RAW, 'activityID'),
                    'typeCode' => new external_value(PARAM_RAW, 'exam'),
                    'typeName' => new external_value(PARAM_RAW,'description'),
                    'navigators' => new external_value(PARAM_RAW,'description'),
                )
            )
        );
    }

    public static function exam_byjobfamily_parameters() {
        return new external_function_parameters([
            'isArabic' => new external_value(PARAM_RAW, 'isArabic', VALUE_DEFAULT, false),
            'JobFamilyID' => new external_value(PARAM_RAW, 'JobFamilyID', VALUE_DEFAULT, NULL),
        ]);
    }

    public static function exam_byjobfamily($isArabic, $JobFamilyID) {
        $context = context_system::instance();
        global $DB, $PAGE;       
        
        // Parameter validation.
        $params = self::validate_parameters(
            self::exam_byjobfamily_parameters(),
            [
                'isArabic' => $isArabic,
                'JobFamilyID' => $JobFamilyID
            ]
        );

        $filtervalues = json_decode($filterdata);
    
        $stable = new \stdClass();
        $stable->thead = false;
        $stable->isarabic = $isArabic;
        $stable->jobfamilyids = $JobFamilyID;
        $data =  (new local_exams\local\exams)->get_exams($stable);
        if($data) {
            $event = \local_exams\event\success_response::create(
                array(
                'context'=>$context,
                'objectid' => 1,
                'other'=>array(
                    'Function'=>'local_exams_exam_byjobfamily',
                    'Success'=>'Successfully Fetched the Response'
                )
                )
            );
            $event->trigger(); 
            return $data['exams'];

        } else{
            $event = \local_exams\event\error_response::create(
                array( 
                    'context'=>$context,
                    'objectid' =>0,
                    'other'=>array(
                        'Function'=>'local_exams_exam_byjobfamily',
                        'Error'=>'Invalid Response Value Detected'
        
                    )
                    )
                );  
            $event->trigger();
        }

       
    }

    public static function exam_byjobfamily_returns() {
        return new external_multiple_structure(
            new external_single_structure(
                array(
                    'name' => new external_value(PARAM_RAW, 'name'),
                    'Description' => new external_value(PARAM_RAW, 'Description'),
                    'value' => new external_value(PARAM_INT, 'value'),
                )
            )
        );
    }

    public static function exam_bycompetencyid_parameters() {
        return new external_function_parameters([
            'isArabic' => new external_value(PARAM_RAW, 'isArabic', VALUE_DEFAULT, false),
            'CompetencyID' => new external_value(PARAM_RAW, 'CompetencyID', VALUE_DEFAULT, NULL),
        ]);
      }

      public static function exam_bycompetencyid($isArabic, $CompetencyID) {
        global $DB, $PAGE;
        $context = context_system::instance();

        // Parameter validation.
        $params = self::validate_parameters(
            self::exam_bycompetencyid_parameters(),
            [
                'isArabic' => $isArabic,
                'CompetencyID' => $CompetencyID
            ]
        );

        $filtervalues = json_decode($filterdata);
    
        $stable = new \stdClass();
        $stable->thead = false;
        $stable->isarabic = $isArabic;
        $stable->competencyid = $CompetencyID;
        $data =  (new local_exams\local\exams)->get_exams($stable);

        if ($data) {
            $event = \local_exams\event\success_response::create(
                array(
                'context'=>$context,
                'objectid' => 1,
                'other'=>array(
                    'Function'=>'local_exams_exam_bycompetencies',
                    'Success'=>'Successfully Fetched the Response'
                )
                )
            );
            $event->trigger();             
            return $data['exams'];
            
        } else  {
            $event = \local_exams\event\error_response::create(
                array( 
                    'context'=>$context,
                    'objectid' =>0,
                    'other'=>array(
                        'Function'=>'local_exams_exam_bycompetencies',
                        'Error'=>'Invalid Response Value Detected'       
                    )
                    )
                );  
            $event->trigger();

        }

      }

      public static function exam_bycompetencyid_returns() {
        return new external_multiple_structure(
            new external_single_structure(
                array(
                    'name' => new external_value(PARAM_RAW, 'activityType'),
                    'Description' => new external_value(PARAM_RAW, 'activityID'),
                    'value' => new external_value(PARAM_INT, 'value'),
                )
            )
        );
    }

    public static function exam_centers_parameters() {
        return new external_function_parameters([
            'isArabic' => new external_value(PARAM_RAW, 'isArabic', VALUE_DEFAULT, false),
            'query' => new external_value(PARAM_RAW, 'query', VALUE_DEFAULT, NULL),
        ]);
    }

    public static function exam_centers($isArabic, $query) {
        global $DB, $PAGE;
        $context = context_system::instance();
        // Parameter validation.
        $params = self::validate_parameters(
            self::exam_centers_parameters(),
            [
                'isArabic' => $isArabic,
                'query' => $query
            ]
        );
        $stable = new \stdClass();
        $stable->thead = false;
        $stable->isarabic = $isArabic;
        $stable->query = $query;
        //$data =  (new local_exams\local\exams)->get_exams($stable);
        $data =  (new local_exams\local\exams)->get_exam_centers($stable);
        if($data){
            $event = \local_exams\event\success_response::create(
                array(
                'context'=>$context,
                'objectid' => 1,
                'other'=>array(
                    'Function'=>'local_exams_exam_centers',
                    'Success'=>'Successfully Fetched the Response'
                )
                )
            );
            $event->trigger();
            return $data;


        } else {
            $event = \local_exams\event\error_response::create(
                array( 
                    'context'=>$context,
                    'objectid' =>0,
                    'other'=>array(
                        'Function'=>'local_exams_exam_centers',
                        'Error'=>'Invalid Response Value Detected'       
                    )
                    )
                );  
            $event->trigger();
        }

        return $data;

    }

    public static function exam_centers_returns() {
        return new external_multiple_structure(
            new external_single_structure(
                array(
                    'examCenterID' => new external_value(PARAM_INT, 'examCenterID'),
                    'name' => new external_value(PARAM_RAW, 'name'),
                    'cityName' => new external_value(PARAM_RAW, 'cityName'),
                    'address' => new external_value(PARAM_RAW, 'address'),
                    'phone' => new external_value(PARAM_RAW, 'phone', VALUE_OPTIONAL),
                    'longitude' => new external_value(PARAM_FLOAT , 'longitude', VALUE_OPTIONAL),
                    'latitude' => new external_value(PARAM_FLOAT, 'latitude', VALUE_OPTIONAL),
                )
            )
        );
    }

    public static function exam_attachments_parameters() {
        return new external_function_parameters([
            'attachmentId' => new external_value(PARAM_RAW, 'attachmentId', VALUE_DEFAULT, false),
            'returnType' => new external_value(PARAM_RAW, 'returnType', VALUE_DEFAULT, NULL),
        ]);
    }

    public static function exam_attachments($attachmentId, $returnType) {
        global $DB, $PAGE;
        $context = context_system::instance();

        // Parameter validation.
        $params = self::validate_parameters(
            self::exam_attachments_parameters(),
            [
                'attachmentId' => $attachmentId,
                'returnType' => $returnType
            ]
        );

        $filtervalues = json_decode($filterdata);
    
        $stable = new \stdClass();
        $stable->thead = false;
        $stable->attachmentid = $attachmentId;
        $stable->returntype = $returnType;
        $data =  (new local_exams\local\exams)->get_exam_attachments($stable);
        if ($data){
            $event = \local_exams\event\success_response::create(
                array(
                'context'=>$context,
                'objectid' => 1,
                'other'=>array(
                    'Function'=>'local_exams_exam_attachments',
                    'Success'=>'Successfully Fetched the Response'
                )
                )
            );
            $event->trigger();
            return $data;
        } else{
            $event = \local_exams\event\error_response::create(
                array( 
                    'context'=>$context,
                    'objectid' =>0,
                    'other'=>array(
                        'Function'=>'local_exams_exam_attachments',
                        'Error'=>'Invalid Response Value Detected'       
                    )
                    )
                );  
            $event->trigger();

        }
       
    }

    public static function exam_attachments_returns() {
        return new external_multiple_structure(
            new external_single_structure(
                array(
                    'id' => new external_value(PARAM_INT, 'examCenterID'),
                    'fileName' => new external_value(PARAM_RAW, 'fileName'),
                    'extention' => new external_value(PARAM_RAW, 'extention'),
                    'contentType' => new external_value(PARAM_RAW, 'contentType'),
                    'thumbnail' => new external_value(PARAM_RAW, 'thumbnail', VALUE_OPTIONAL),
                    'content' => new external_value(PARAM_RAW, 'content', VALUE_OPTIONAL),
                )
            )
        );
    }

    public static function exam_statistics_parameters() {
        return new external_function_parameters([

        ]);
    }

    public static function exam_statistics() {
        global $DB, $PAGE;
        $context = context_system::instance();

        $data =  (new local_exams\local\exams)->get_exam_statistics();
        if ($data){
            $event = \local_exams\event\success_response::create(
                array(
                'context'=>$context,
                'objectid' => 1,
                'other'=>array(
                    'Function'=>'local_exams_exam_statistics',
                    'Success'=>'Successfully Fetched the Response'
                )
                )
            );
            $event->trigger();
            return $data;

        } else {
            $event = \local_exams\event\error_response::create(
                array( 
                    'context'=>$context,
                    'objectid' =>0,
                    'other'=>array(
                        'Function'=>'local_exams_exam_statistics',
                        'Error'=>'Invalid Response Value Detected'       
                    )
                    )
                );  
            $event->trigger();
        }

  
    }

    public static function exam_statistics_returns() {
        return new external_multiple_structure(
            new external_single_structure(
                array(
                    'year' => new external_value(PARAM_INT, 'examCenterID'),
                    'numberOfExams' => new external_value(PARAM_INT, 'numberOfExams'),
                    'numberOfExamTrials' => new external_value(PARAM_RAW, 'numberOfExamTrials', VALUE_OPTIONAL),
                    'femalesExamTrials' => new external_value(PARAM_RAW, 'femalesExamTrials', VALUE_OPTIONAL),
                    'malesExamTrials' => new external_value(PARAM_RAW, 'malesExamTrials', VALUE_OPTIONAL),
                )
            )
        );
    }

    public function examdetails_parameters() {
        return new external_function_parameters([
            'id' => new external_value(PARAM_INT, 'id'),
        ]);
    }

    public function examdetails($id) {
        global $PAGE;
        $sitecontext = context_system::instance();
        require_login();
        $PAGE->set_url('/local/exams/index.php', array());
        $PAGE->set_context($sitecontext);
        // Parameter validation.
        $params = self::validate_parameters(
            self::examdetails_parameters(),
            [
                'id' => $id
            ]
        );
        $settings = external_settings::get_instance();
        $mlang =  $settings->get_lang();       
        $examdetails = (new local_exams\local\exams)->orgoffexamdetails($id, $mlang);
        if($examdetails) {
           return $examdetails;
        } else {
            return 'No records found';
        }
    }

    public function examdetails_returns() {
        return new external_single_structure([
            'id' => new external_value(PARAM_INT, 'id'),
            'name' => new external_value(PARAM_TEXT, 'name'),
            'code' => new external_value(PARAM_TEXT, 'name'),
            'description' => new external_value(PARAM_RAW, 'description'),
            'profiles' => new external_multiple_structure(
                new external_single_structure(
                    array(
                        'id' => new external_value(PARAM_INT, 'id'),
                        'code' => new external_value(PARAM_RAW, 'profilecode'),
                        'cmid' => new external_value(PARAM_INT, 'cmid'),
                        'registrationstartdate' => new external_value(PARAM_INT, 'registrationstartdate'),
                        'registrationenddate' => new external_value(PARAM_INT, 'registrationenddate'),
                        'passinggrade' => new external_value(PARAM_INT, 'passinggrade'),
                        'seatingcapacity' => new external_value(PARAM_INT, 'seatingcapacity'),
                        'duration' => new external_value(PARAM_INT, 'duration'),
                        'examseats' => new external_value(PARAM_RAW, 'examseats'),
                        'questions' => new external_value(PARAM_INT, 'questions'),
                        'product_variations' => new external_single_structure(
                            array(
                                'product' => new external_value(PARAM_INT, 'product'),
                                'variation' => new external_value(PARAM_INT, 'variation'),
                                'category' => new external_value(PARAM_INT, 'category'),
                            )
                        ),
                        'product_attributes' => new external_single_structure(
                            array(
                                'product' => new external_value(PARAM_INT, 'product'),
                                'variation' => new external_value(PARAM_RAW, 'variation'),
                                'category' => new external_value(PARAM_INT, 'category'),
                                'label' => new external_value(PARAM_RAW, 'label'),
                                'quantity' => new external_value(PARAM_INT, 'quantity'),
                                'isloggedin' => new external_value(PARAM_INT, 'isloggedin'),
                                'hasvariations' => new external_value(PARAM_INT, 'hasvariations'),
                                'checkout' => new external_value(PARAM_RAW, 'checkout'),
                                'grouped' => new external_value(PARAM_INT, 'grouped'),
                                'errortext' => new external_value(PARAM_RAW, 'errortext', VALUE_OPTIONAL),
                            )
                        ),
                    )
                )
            ),

        ], '', VALUE_OPTIONAL,[]);
    }

    public static function exam_profiles_parameters() {
        return new external_function_parameters([
            'options' => new external_value(PARAM_RAW, 'The paging data for the service',VALUE_OPTIONAL),
            'dataoptions' => new external_value(PARAM_RAW, 'The data for the service',VALUE_OPTIONAL),
            'offset' => new external_value(PARAM_INT, 'Number of items to skip from the begging of the result set',
                VALUE_DEFAULT, 0),
            'limit' => new external_value(PARAM_INT, 'Maximum number of results to return',
                VALUE_DEFAULT, 0),
            'contextid' => new external_value(PARAM_INT, 'contextid',VALUE_OPTIONAL),
            'filterdata' => new external_value(PARAM_RAW, 'filters applied',VALUE_OPTIONAL),
        ]);
    }

    public static function exam_profiles($options=false, $dataoptions=false, $offset = 0, $limit = 0,$contextid=1, $filterdata=false) {
        global $DB, $PAGE, $CFG, $USER;
        $params = self::validate_parameters(
            self::exam_profiles_parameters(),
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
        $filtervalues = (object)json_decode($filterdata);
        $filtervalues->examid = json_decode($params['dataoptions'])->examid;
        $filtervalues->profileid = json_decode($params['dataoptions'])->profileid;
        $filtervalues->hallscheduleid = json_decode($params['dataoptions'])->hallscheduleid;
        $filtervalues->tuserid = json_decode($params['dataoptions'])->tuserid;

        $stable = new \stdClass();
        $stable->thead = false;
        $stable->start = $offset;
        $stable->length = $limit;
        $data = (new local_exams\local\exams)->get_listof_examprofiles($stable, $filtervalues);
        $totalcount = $data['totalprofiles'];

        return [
            'totalcount' => $totalcount,
            'length' => $totalcount,
            'records' =>$data,
            'url' => $CFG->wwwroot,
            'userid' => $USER->id,
            'usernextattempt' => $data['usernextattempt'],
        ];
    }

  /**
   * Returns description of method result value
   * @return external_description
   */

    public static function exam_profiles_returns() {
        return new external_single_structure([
          'url' => new external_value(PARAM_RAW, 'url'),
          'userid' => new external_value(PARAM_RAW, 'userid'),
          'totalcount' => new external_value(PARAM_INT, 'total number of challenges in result set'),
          'length' => new external_value(PARAM_RAW, 'total number of challenges in result set'),
          'usernextattempt'=> new external_value(PARAM_RAW, 'usernextattempt', VALUE_OPTIONAL),
          'records' => new external_single_structure(
                array(
                   'profiles' => new external_multiple_structure(
                        new external_single_structure(
                            array(
                                'id' => new external_value(PARAM_INT, 'id'),
                                'examid' => new external_value(PARAM_INT, 'examid'),
                                'cmid' => new external_value(PARAM_INT, 'cmid'),
                                'profilecode' => new external_value(PARAM_RAW, 'profilecode'),
                                'registrationstartdate' => new external_value(PARAM_RAW, 'registrationstartdate'),
                                'registrationenddate' => new external_value(PARAM_RAW, 'registrationenddate'),
                                'passinggrade' => new external_value(PARAM_RAW, 'passinggrade'),
                                'seatingcapacity' => new external_value(PARAM_RAW, 'seatingcapacity'),
                                'duration' => new external_value(PARAM_RAW, 'duration'),
                                'language' => new external_value(PARAM_RAW, 'language'),
                                'isadmin' => new external_value(PARAM_BOOL, 'isadmin'),
                                'is_assesmentoperation' => new external_value(PARAM_BOOL, 'is_assesmentoperation'),
                                'orgoff' => new external_value(PARAM_BOOL, 'orgoff'),
                                'trainee' => new external_value(PARAM_BOOL, 'orgoff'),
                                'examseats' => new external_value(PARAM_RAW, 'examseats', VALUE_OPTIONAL),
                                'todayexam' => new external_value(PARAM_BOOL, 'todayexam'),
                                'examdate' => new external_value(PARAM_RAW, 'examdate'),
                                'launch' => new external_value(PARAM_BOOL, 'examdate'),
                                'showquestions' => new external_value(PARAM_INT, 'showquestions'),
                                'questions' => new external_value(PARAM_INT, 'questions'),
                                'showexamduration' => new external_value(PARAM_INT, 'showexamduration'),
                                'launchbtnstatus' => new external_value(PARAM_RAW, 'launchbtnstatus'),
                                'nextattempt' => new external_value(PARAM_RAW, 'launchbtnstatus', VALUE_OPTIONAL),
                                'attemptid' => new external_value(PARAM_INT, 'attemptid', VALUE_OPTIONAL),
                                'hall' => new external_value(PARAM_RAW, 'hall', VALUE_OPTIONAL),
                                'product_profile' => new external_value(PARAM_INT, 'product_profile', VALUE_OPTIONAL),
                                'canceloption' => new external_value(PARAM_BOOL, 'canceloption', VALUE_OPTIONAL),
                                'selectedprofile' => new external_value(PARAM_BOOL,'selectedprofile',VALUE_OPTIONAL),
                                'examattemptid' => new external_value(PARAM_INT,'examattemptid',VALUE_OPTIONAL),
                                'attemptfee' => new external_value(PARAM_INT,'attemptfee',VALUE_OPTIONAL),
                                'examday' => new external_value(PARAM_BOOL,'examday',VALUE_OPTIONAL),
                                'canreschedule' => new external_value(PARAM_BOOL,'canreschedule',VALUE_OPTIONAL),
                                'disablereschedule' => new external_value(PARAM_BOOL,'disablereschedule',VALUE_OPTIONAL),
                                'ownedby' => new external_value(PARAM_RAW,'ownedby',VALUE_OPTIONAL),
                                'readytolaunch' => new external_value(PARAM_BOOL,'readytolaunch',VALUE_OPTIONAL),
                                'lastattemptprofileid' => new external_value(PARAM_INT,'lastattemptprofileid',VALUE_OPTIONAL),
                                'scheduleid' => new external_value(PARAM_INT, 'scheduleid', VALUE_OPTIONAL),
                                'attemptnumber' => new external_value(PARAM_INT, 'attemptnumber', VALUE_OPTIONAL),
                                'havinggrade' => new external_value(PARAM_BOOL, 'havinggrade', VALUE_OPTIONAL),
                                'disableallactions' => new external_value(PARAM_BOOL, 'disableallactions', VALUE_OPTIONAL),
                                'purchasenextattempt' => new external_value(PARAM_BOOL, 'purchasenextattempt', VALUE_OPTIONAL),
                                'tuserid' => new external_value(PARAM_RAW, 'tuserid', VALUE_OPTIONAL),
                                'examstatus' => new external_value(PARAM_BOOL, 'examstatus', VALUE_OPTIONAL),
                                'exampassed' => new external_value(PARAM_BOOL, 'exampassed', VALUE_OPTIONAL),
                                'enrolled' => new external_value(PARAM_INT, 'enrolled', VALUE_OPTIONAL),
                                'product_variations' => new external_single_structure(
                                    array(
                                        'product' => new external_value(PARAM_INT, 'product'),
                                        'variation' => new external_value(PARAM_INT, 'variation'),
                                        'category' => new external_value(PARAM_INT, 'category'),
                                    )
                                ),
                                'product_attributes' => new external_single_structure(
                                    array(
                                        'product' => new external_value(PARAM_INT, 'product'),
                                        'variation' => new external_value(PARAM_RAW, 'variation'),
                                        'category' => new external_value(PARAM_INT, 'category'),
                                        'label' => new external_value(PARAM_RAW, 'label'),
                                        'quantity' => new external_value(PARAM_INT, 'quantity'),
                                        'isloggedin' => new external_value(PARAM_INT, 'isloggedin'),
                                        'hasvariations' => new external_value(PARAM_INT, 'hasvariations'),
                                        'checkout' => new external_value(PARAM_RAW, 'checkout'),
                                        'grouped' => new external_value(PARAM_INT, 'grouped'),
                                        'errortext' => new external_value(PARAM_RAW, 'errortext', VALUE_OPTIONAL),
                                        'profileid' => new external_value(PARAM_RAW, 'profileid', VALUE_OPTIONAL),
                                        'processtype' => new external_value(PARAM_RAW, 'processtype', VALUE_OPTIONAL),
                                    )
                                ),                                
                            )
                        )
                    ),                         
                    'request_view' => new external_value(PARAM_INT, 'request_view', VALUE_OPTIONAL),
                    'nocourses' => new external_value(PARAM_BOOL, 'nocourses', VALUE_OPTIONAL),
                    'totalexams' => new external_value(PARAM_INT, 'totalexams', VALUE_OPTIONAL),
                    'length' => new external_value(PARAM_INT, 'length', VALUE_OPTIONAL),
                )
            )
        ]);
    }

    public static function delete_profile_parameters(){
        return new external_function_parameters(
            array(
                'id' => new external_value(PARAM_INT, 'id', 0)
                )
        );

    }  
    public static function delete_profile($id){
        global $DB;
        $params = self::validate_parameters(self::delete_profile_parameters(),
                                    ['id' => $id]);
        $context = context_system::instance();
        if($id){ 
            // $event = \local_exams\event\exam_deleted::create(array( 'context'=>$context, 'objectid' =>$examid));
            // $event->trigger();
            $examid = (new local_exams\local\exams)->delete_examprofile($id);
            
        } else {
            throw new moodle_exception('Error in deleting');
            return false;
        }
    }   
    public static function delete_profile_returns() {
        return new external_value(PARAM_BOOL, 'return');
    }

    public static function exam_profileinfo_parameters() {
        return new external_function_parameters(
            array(
                'profileid' => new external_value(PARAM_INT, 'profileid', 0),
                )
        );
    }
    public static function exam_profileinfo($profileid) {
        global $DB;
        require_login();
        $params = self::validate_parameters(self::exam_profileinfo_parameters(),
                                    ['profileid' => $profileid]);
        $data = (new local_exams\local\exams)->exam_profileinfo($profileid);
        
        return [
            'options' => $data,
        ];
    }
    public static function exam_profileinfo_returns() {
        return new external_single_structure([
          'options' => new external_value(PARAM_RAW, 'The paging data for the service'),
        ]);
    }


    public static function hall_schedules_parameters() {
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

    public static function hall_schedules($options, $dataoptions, $offset = 0, $limit = 0, $contextid, $filterdata) {
        global $DB, $PAGE, $CFG;
        $systemcontext = context_system::instance();
        $params = self::validate_parameters(
            self::hall_schedules_parameters(),
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
        $filtervalues = (object)json_decode($filterdata);
        $filtervalues->examid = json_decode($params['dataoptions'])->examid;
        $filtervalues->profileid = json_decode($params['dataoptions'])->profileid;

        $stable = new \stdClass();
        $stable->thead = false;
        $stable->start = $offset;
        $stable->length = $limit;
        $stable->type = json_decode($params['dataoptions'])->type;
        $stable->tuserid = json_decode($params['dataoptions'])->tuserid;
        $stable->status = json_decode($params['dataoptions'])->status;
        $stable->damount = json_decode($params['dataoptions'])->damount;
        $stable->organization = json_decode($params['dataoptions'])->organization;
        $stable->orgofficial = json_decode($params['dataoptions'])->orgofficial;
        $data = (new local_exams\local\exams)->get_listof_hallschedules($stable, $filtervalues);
        $totalschedules = $data['totalschedules'];
        if(!is_siteadmin() && has_capability('local/organization:manage_financial_manager', $systemcontext)) {
            $financemanager = true;
        } else {
            $financemanager = false;
        }

        return [
            'totalcount' => $totalschedules,
            'length' => $totalschedules,
            'records' => $data,
            'financemanager' => $financemanager,
            'contextid'=>$systemcontext->id,
        ];
    }

  /**
   * Returns description of method result value
   * @return external_description
   */

    public static function hall_schedules_returns() {
        return new external_single_structure([
          'totalcount' => new external_value(PARAM_INT, 'total number of challenges in result set'),
          'length' => new external_value(PARAM_RAW, 'total number of challenges in result set'),
          'financemanager' => new external_value(PARAM_BOOL, 'financemanager', VALUE_OPTIONAL),
          'contextid' => new external_value(PARAM_INT, 'contextid', VALUE_OPTIONAL),
          'records' => new external_single_structure(
                array(
                   'hallschedules' => new external_multiple_structure(
                        new external_single_structure(
                            array(
                                'id' => new external_value(PARAM_INT, 'id'),
                                'hallid' => new external_value(PARAM_INT, 'hallid'),
                                'startdate' => new external_value(PARAM_RAW, 'startdate'),
                                'starttime' => new external_value(PARAM_RAW, 'starttime'),
                                'endtime' => new external_value(PARAM_RAW, 'endtime'),
                                'days' => new external_value(PARAM_RAW, 'days'),
                                'isadmin' => new external_value(PARAM_BOOL,'admin'),
                                'orgoff' => new external_value(PARAM_BOOL,'orgoff'),
                                'examid' => new external_value(PARAM_INT, 'examid'),
                                'profileid' => new external_value(PARAM_INT, 'profileid'),
                                'isenrolled' => new external_value(PARAM_BOOL, 'isenrolled'),
                                'seatscompleted' => new external_value(PARAM_BOOL, 'seatscompleted'),
                                'type' => new external_value(PARAM_BOOL, 'type', VALUE_OPTIONAL),
                                'typename' => new external_value(PARAM_RAW, 'typename', VALUE_OPTIONAL),
                                'tuserid' => new external_value(PARAM_RAW, 'tuserid', VALUE_OPTIONAL),
                                'enrolbtn' => new external_value(PARAM_BOOL, 'enrolbtn', VALUE_OPTIONAL),
                                'productid' => new external_value(PARAM_INT, 'productid', VALUE_OPTIONAL),
                                'reschedulebtn' => new external_value(PARAM_BOOL, 'reschedulebtn', VALUE_OPTIONAL),
                                'damount' => new external_value(PARAM_RAW, 'damount', VALUE_OPTIONAL),
                                'organization' => new external_value(PARAM_INT, 'organization', VALUE_OPTIONAL), 
                                'orgofficial' => new external_value(PARAM_INT, 'orgofficial', VALUE_OPTIONAL), 
                                'product_variations' => new external_single_structure(
                                    array(
                                        'product' => new external_value(PARAM_INT, 'product'),
                                        'variation' => new external_value(PARAM_INT, 'variation'),
                                        'category' => new external_value(PARAM_INT, 'category'),
                                        'hallscheduleid' => new external_value(PARAM_INT, 'hallscheduleid'),
                                    )
                                ),
                                'product_attributes' => new external_single_structure(
                                    array(
                                        'product' => new external_value(PARAM_INT, 'product'),
                                        'variation' => new external_value(PARAM_RAW, 'variation'),
                                        'category' => new external_value(PARAM_INT, 'category'),
                                        'label' => new external_value(PARAM_RAW, 'label'),
                                        'quantity' => new external_value(PARAM_INT, 'quantity'),
                                        'isloggedin' => new external_value(PARAM_RAW, 'isloggedin'),
                                        'hasvariations' => new external_value(PARAM_INT, 'hasvariations'),
                                        'checkout' => new external_value(PARAM_RAW, 'checkout'),
                                        'grouped' => new external_value(PARAM_INT, 'grouped'),
                                        'errortext' => new external_value(PARAM_RAW, 'errortext', VALUE_OPTIONAL),
                                        'hallscheduleid' => new external_value(PARAM_RAW, 'errortext'),
                                        'profileid' => new external_value(PARAM_INT, 'profileid', VALUE_OPTIONAL),
                                        'tandc'   =>new external_value(PARAM_INT, 'tandc', VALUE_OPTIONAL),
                                    )
                                ),                                                      
                            )
                        )
                    ),                         
                    'request_view' => new external_value(PARAM_INT, 'request_view', VALUE_OPTIONAL),
                    'nocourses' => new external_value(PARAM_BOOL, 'nocourses', VALUE_OPTIONAL),
                    'totalexams' => new external_value(PARAM_INT, 'totalexams', VALUE_OPTIONAL),
                    'length' => new external_value(PARAM_INT, 'length', VALUE_OPTIONAL),
                )
            )
        ]);
    }

    public static function enrouser_parameters(){
        return new external_function_parameters(
            array(
                'productid' => new external_value(PARAM_RAW, 'productid',VALUE_OPTIONAL),
                'examid'    => new external_value(PARAM_INT, 'examid', 0),
                'profileid' => new external_value(PARAM_INT, 'profileid', 0),
                'scheduleid'=> new external_value(PARAM_INT, 'scheduleid', 0),
                'type'      => new external_value(PARAM_RAW, 'type',VALUE_OPTIONAL),
                'tuserid'   => new external_value(PARAM_RAW, 'tuserid',VALUE_OPTIONAL),
                'orderid'   => new external_value(PARAM_RAW, 'orderid',VALUE_OPTIONAL),
                'organization'=> new external_value(PARAM_INT, 'organization', 0),
                'discountprice' => new external_value(PARAM_RAW, 'discountprice',VALUE_OPTIONAL),
                'discounttype' => new external_value(PARAM_RAW, 'discounttype',VALUE_OPTIONAL),
                'discounttableid' => new external_value(PARAM_INT, 'orderid',VALUE_OPTIONAL),
                'autoapproval' => new external_value(PARAM_INT, 'autoapproval',VALUE_OPTIONAL),
            )
        );

    }  
    public static function enrouser($productid = 0,$examid, $profileid, $scheduleid, $type = false, $tuserid=0, $orderid=0, $organization = 0,$discountprice = 0,$discounttype = null,$discounttableid = 0,$autoapproval = 0){
        global $DB, $USER;
        $params = self::validate_parameters(
            self::enrouser_parameters(),
            [
                'examid' => $examid,
                'profileid' => $profileid,
                'scheduleid' => $scheduleid,
                'type' => $type,
                'tuserid' => $tuserid,
                'orderid' => $orderid,
                'productid'=>$productid,
                'organization'=>$organization,
                'discountprice' => $discountprice ,
                'discounttype' => $discounttype ,
                'discounttableid' => $discounttableid ,
                'autoapproval'=>$autoapproval
            ]);
        $exam = $DB->get_record('local_exams', ['id'=>$examid]);
        $profile = $DB->get_record('local_exam_profiles', ['id'=>$profileid]);
        if(empty($tuserid)) {
            $userid = $USER->id;
        } else {
            if (is_numeric($tuserid)) {
                $userid = $tuserid;
            } else {
                $traineeids = explode(',', base64_decode($tuserid));
                foreach ($traineeids as $traineeid) {
                    if($type == 'bulkenrollment') {
                        $validationerrors =(new \local_exams\local\exams)->bulk_enroll_user_validations($examid,$profileid,
                        $traineeid,$scheduleid,$organization);
                        $bulkenrollstatus = $DB->get_field('local_users','bulkenrollstatus',['userid'=>$traineeid]);
                        if(!$validationerrors) {
                            $hasuserenrolled = $DB->record_exists('exam_enrollments',['examid'=>$examid,'profileid'=>$profileid,'userid'=>$traineeid,'enrolstatus'=>1]);
                            $currenttime = time();
                            $productinstance = new \tool_product\product();
                            $firstattemptproductid =  $DB->get_field('tool_products', 'id', ['category' => $productinstance::EXAMS, 'referenceid' => $profileid]);
                            if($hasuserenrolled) {
                                $sql = "SELECT COUNT(id) 
                                FROM {local_exam_userhallschedules} leu 
                                WHERE leu.examid=$examid AND examdate !=0 AND leu.userid=".$traineeid;
                                $attempsnumber = $DB->count_records_sql($sql);
                                $nextattempt =  ($attempsnumber == 0)  ? 1 : ($attempsnumber+1);
                                $attemptid =  $DB->get_field('local_exam_attempts', 'id', ['examid' => $examid, 'attemptid' => $nextattempt]);
                                if($attemptid){
                                    $nextattempproductid =$DB->get_field('tool_products', 'id', ['category' => $productinstance::EXAMATTEMPT, 'referenceid' => $attemptid]);
                                }
                            } else {
                                $nextattempproductid = 0;
                                $attemptid = 0;
                            }
                            $productid = (!$hasuserenrolled) ? $firstattemptproductid : (($attemptid)?$nextattempproductid : 0);
                            $data=new stdClass;
                            $params['transactiontypes'] = 'register';
                            $params['userid'] = $traineeid;
                            $params['hallscheduelid'] = $scheduleid;
                            $params['examcode'] = $exam->code;
                            $params['profilecode'] = $profile->profilecode;
                            $params['product_id'] =0;

                            // Checking Hall Availability settings are Enabled or not
                            $accessstatus = (new \local_exams\local\exams)->access_fast_service('hallavailability');
                            if ($accessstatus) {
                                $apidata = (new \local_exams\local\exams)->prepare_fast_apidata($params, $data);
                                $apidata['validation'] = 1;
                                $fastapi = new \local_exams\local\fast_service($apidata);
                                $fastresponse = $fastapi->hall_availability();
                            } else {
                                $fastresponse = new stdClass();
                                $fastresponse->success = true;
                            }

                            //$fastresponse->success =true;
                            if(empty($fastresponse) || COUNT($fastresponse->messages) <= 0 || $fastresponse->success) {
                                if($productid > 0 && $bulkenrollstatus == 0) {
                                    $schedulerecordexists = $DB->record_exists('local_exam_userhallschedules',['examid'=>$examid,'profileid'=>$profileid,'userid'=>$traineeid,'productid'=>$productid]);
                                    if(!$schedulerecordexists){
                                        (new local_exams\local\exams)->exam_enrollmet($profileid, $traineeid, $scheduleid,'bulkenrollment',$USER->id,$orderid,$productid,$organization,null);
                                    }
                                }
                            }
                        }  else {
                            $fastresponse = $validationerrors;

                        }   
                        
                    } else {
                        $fastresponse  = (new local_exams\local\exams)->apischecking($traineeid, $scheduleid, $profileid, $examid, $type, $orderid,$productid,$organization,$discountprice,$discounttype,$discounttableid,$autoapproval);
                    }
                }
                if(is_array($fastresponse->messages)) {
                    foreach($fastresponse->messages as $error){
                        $errors[] = $error->message;
                    }
                    $errormessage = implode(',',$errors);
                } else {
                    $errormessage =$fastresponse->messages; 
                }
                $returndata['response']  = (empty($fastresponse) || COUNT($fastresponse->messages) <=0 || $fastresponse->success) ? 'success' : $errormessage;
                return $returndata;
            }
        }
        $context = context_system::instance();

        $info = new stdClass;
        $productinstance = new \tool_product\product();
        if($productid == 0){
            $productid = $DB->get_field('tool_products', 'id', ['category' => $productinstance::EXAMS, 'referenceid' => $profileid]);
        }
        $oldeshedulerecord = $DB->get_record('local_schedule_logs',['productid' => $productid, 'entitytype' => 'exam','userid' => $userid]);
        $userexamschedulehallid =(int) $DB->get_field('local_exam_userhallschedules','hallscheduleid',['examid' => $examid, 'profileid' => $profileid,'userid' => $userid]);
        $oldschid = (int)$oldeshedulerecord->oldhallscheduleid;
        $newschid = (int)$oldeshedulerecord->newhallscheduleid;
        $oldscheduleid = (!empty($oldeshedulerecord))  ? (($oldschid == $newschid)  ? $oldschid  : (($newschid > 0) ? $newschid  : $oldschid)) :  $userexamschedulehallid ;
        $schedulerecord = $DB->get_record('hallschedule',['id' => $oldscheduleid]);
        $info->oldexamdate = $schedulerecord->startdate;
        $info->oldexamtime = $schedulerecord->starttime;
        $info->userid = $userid;
        $info->hallscheduelid = $scheduleid;
        $hallid = $DB->get_field_sql("SELECT h.id FROM {hallschedule} as hs 
        JOIN {hall} as h ON hs.hallid=h.id where hs.id=:hallscheduleid",array('hallscheduleid'=> $oldscheduleid));

        $info->oldcentercode = $DB->get_field('hall', 'code', ['id'=>$hallid]);
// $oldhallcode = (new \local_hall\hall())->get_hallcode($hallid, $examid);
        // $info->oldcentercode = $oldhallcode;
        $exam_record = $exam_record = $DB->get_record_sql("SELECT ex.*, ep.profilecode examcode 
          FROM {local_exams} ex 
          JOIN {local_exam_profiles} ep ON ex.id = ep.examid 
          WHERE ex.id = '$examid' AND ep.id = '$profileid' ");

        $info->product_id = $productid;
        $enrolledbyuser =(int) $DB->get_field('exam_enrollments','usercreated',['examid'=>$examid,'profileid'=>$profileid,'userid'=>$userid]);        
        $enrolleduseroleinfo = $DB->get_record_sql('SELECT rol.* FROM {role} rol 
                                                JOIN {role_assignments} rola ON rola.roleid = rol.id
                                                WHERE rola.userid =:userid and contextid =:contextid',['userid'=>$enrolledbyuser,'contextid'=>$context->id]);
        if($type == 'reschedule') {
            $fastresponse = (new local_exams\local\exams)->reschedule_fastapi($info);
           if(empty($fastresponse) || COUNT($fastresponse->messages) <=0 || $fastresponse->success) {
               $response =  (new local_exams\local\exams)->exam_enrollmet($profileid, $userid, $scheduleid,$type);
           }
        } elseif(is_null($type) || $type == 'schedule') {

            // $attempsnumber = (new local_exams\local\exams)->quiz_attemptscount($examid);            
            // if ($exam->noofattempts > 0) {
            //     if ($exam->appliedperiod == 1) {
            //         if ($attempsnumber == 0) {
            //             $category = self::EXAMS;
            //             $toolproduct = $DB->get_record('tool_products', ['category' => $category, 'referenceid' => $record->id]);
            //         } else {
            //             $attemptnum = ++$attempsnumber;
            //             $category = self::EXAMATTEMPT;
            //             if ($attemptnum <= $exam->noofattempts) {
            //                 $attemptid = $DB->get_field('local_exam_attempts', 'id', ['examid' => $exam->id, 'attemptid' => $attemptnum]);
            //                 $toolproduct = $DB->get_record('tool_products', ['category' => $category, 'referenceid' => $attemptid]);
            //             }
            //         }
            //     } else {
            //         $attemptnum = ++$attempsnumber;
            //         $category = self::EXAMATTEMPT;
            //         if ($attemptnum <= $exam->noofattempts) {
            //             $attemptid = $DB->get_field('local_exam_attempts', 'id', ['examid' => $exam->id, 'attemptid' => $attemptnum]);
            //             $toolproduct = $DB->get_record('tool_products', ['category' => $category, 'referenceid' => $attemptid]);
            //         }
            //     }
            // }

            $examdatetime = $DB->get_field('hallschedule','startdate',['id'=>$scheduleid]);

            if($oldeshedulerecord && $oldscheduleid > 0) {
                if($examdatetime) {
                    $fastresponse = (new local_exams\local\exams)->reschedule_fastapi($info);
                    if(empty($fastresponse) || COUNT($fastresponse->messages) <=0 || $fastresponse->success) {

                        $attempsnumber = (new \local_exams\local\exams)->quiz_attemptscount($examid);
                        $profile = $DB->get_record('local_exam_profiles', ['id'=>$profileid]);
                        $userschedule = (new \local_exams\local\exams)->user_scheduledata($profile, $attempsnumber, $userid);
                        $DB->execute('UPDATE {local_exam_userhallschedules} SET hallscheduleid = '.$scheduleid.', examdate = '.$examdatetime.' 
                        WHERE id='.$userschedule->id.' AND examid ='.$examid.' AND profileid ='.$profileid.' AND userid ='.$userid.'');
                        $DB->execute('UPDATE {exam_enrollments} SET hallscheduleid = '.$scheduleid.', examdate = '.$examdatetime.' 
                        WHERE examid ='.$examid.' AND profileid ='.$profileid.' AND userid ='.$userid.'');
                        $id = $DB->get_field('local_exam_userhallschedules', 'id', ['examid' => $examid, 'profileid' => $profileid, 'userid' => $userid]);
                        $eventparams = array('context' => \context_system::instance(),
                        'objectid'=>$id,
                        'other'=>array('category' => 2,
                                        'entityid' => $profileid,    // profile id
                                        'examdate' => $examdatetime,
                                        'userid' => $userid,
                                        'hallscheduleid' => $scheduleid)
                        );
                        $event = \local_exams\event\trainee_schedules::create($eventparams);// ... code that may add some record snapshots
                        $event->trigger();
                        $notification = (new \local_exams\local\exams)->exams_enrol_notification($exam_record, $userid, $scheduleid);

                    }
                }
            } else {

                $data=new stdClass;
                $params['transactiontypes'] = 'register';
                $params['userid'] = $userid;
                $params['hallscheduelid'] = $scheduleid;
                // $params['product_id'] = $toolproduct->id;
                $params['examcode'] = $exam->code;
                $params['profilecode'] = $profile->profilecode;
                $params['product_id'] = $DB->get_field('tool_products', 'id', ['category' => $productinstance::EXAMS, 'referenceid' => $profileid]);

                // Checking Hall Availability settings are Enabled or not
                $accessstatus = (new \local_exams\local\exams)->access_fast_service('hallavailability');
                if ($accessstatus) {
                    $apidata = (new \local_exams\local\exams)->prepare_fast_apidata($params, $data);
                    $apidata['validation'] = 1;
                    $fastapi = new \local_exams\local\fast_service($apidata);
                    $fastresponse = $fastapi->hall_availability();
                } else {
                    $fastresponse = new stdClass();
                    $fastresponse->success = true; 
                }

                if(empty($fastresponse) || COUNT($fastresponse->messages) <=0 || $fastresponse->success) {

                    if($exam_record->ownedby == 'FA' || $exam_record->ownedby == 'CISI' ) {
                        // Checking Hall Availability settings are Enabled or not
                        $accessstatus = (new \local_exams\local\exams)->access_fast_service('examreservation');
                        if ($accessstatus) {
                            $examinstance = new local_exams\local\exams();
                            $examinfodata = $DB->get_record_sql('SELECT e.code,ep.profilecode,ep.language,ee.timecreated,ee.usercreated FROM {local_exams} as e 
                                                JOIN {local_exam_profiles} as ep ON ep.examid = e.id    
                                                JOIN {exam_enrollments} as ee ON ee.examid=e.id AND ee.profileid=ep.id
                                                WHERE e.id =:examid AND ep.id =:profileid
                                                ',['examid' => $examid, 'profileid' => $profileid]);
        
                            $hallinfodata = $DB->get_record_sql('SELECT hs.startdate as examdate,hs.starttime,h.code as hallcode, h.id as hallid FROM  {hall} as h  
                                                JOIN {hallschedule} as hs ON hs.hallid=h.id    
                                                WHERE hs.id =:scheduleid 
                                                ',['scheduleid' => $scheduleid]);

                            $examinfo = new stdClass();
                            $examinfo->code = $examinfodata->code;
                            $hallcode = $DB->get_field('hall', 'code', ['id'=>$hallinfodata->hallid]);
                            // $hallcode = (new \local_hall\hall())->get_hallcode($hallinfodata->hallid, $examid);
                            $examinfo->hallcode = $hallcode;
                            $examinfo->profilecode = $examinfodata->profilecode;
                            $examinfo->examdate = $hallinfodata->examdate;
                            $examinfo->starttime = $hallinfodata->starttime;
                            $examinfo->timecreated = time();
                            $examinfo->language = $examinfodata->language;
                            $examinfo->userid = $userid;
                            $examinfo->createdbyuserid = $examinfodata->usercreated;
                            $examinfo->userorganization = $apidata['userorganization'];
                            $fastresponse= $examinstance->fast_exam_api($examinfo);
                        } else {
                            $fastresponse = new stdClass();
                            $fastresponse->success = true;
                        }
                        if(empty($fastresponse) || COUNT($fastresponse->messages) <=0 || $fastresponse->success) {
                            if($examdatetime) {
                                $DB->execute('UPDATE {local_exam_userhallschedules} SET hallscheduleid = '.$scheduleid.', examdate = '.$examdatetime.' 
                                WHERE examid ='.$examid.' AND profileid ='.$profileid.' AND userid ='.$userid.'');
                                $DB->execute('UPDATE {exam_enrollments} SET hallscheduleid = '.$scheduleid.', examdate = '.$examdatetime.' 
                                WHERE examid ='.$examid.' AND profileid ='.$profileid.' AND userid ='.$userid.'');
    
                                $id = $DB->get_field('local_exam_userhallschedules', 'id', ['examid' => $examid, 'profileid' => $profileid, 'userid' => $userid]);
    
                                $eventparams = array('context' => \context_system::instance(),
                                'objectid'=>$id,
                                'other'=>array('category' => 2,
                                                'entityid' => $profileid,    // profile id
                                                'examdate' => $examdatetime,
                                                'userid' => $userid,
                                                'hallscheduleid' => $scheduleid)
                                );
                                $event = \local_exams\event\trainee_schedules::create($eventparams);// ... code that may add some record snapshots
                                $event->trigger();
                                $notification = (new \local_exams\local\exams)->exams_enrol_notification($exam_record, $userid, $scheduleid);
                            }
                        }
                    }

                }


            }
 
        } else {
            $response =  (new local_exams\local\exams)->exam_enrollmet($profileid, $userid, $scheduleid,$type);
        }
        // *************** IKRAM CODE STARTS ******************************
        /**
         * Check if user have already scheduled the hall or not
         * if not then call the CISI API otherwise don't
         * 
         */
        if ($exam_record->ownedby == 'CISI') {
            
            $is_already_scheduled = is_hall_already_scheduled($examid, $profileid, $scheduleid, $userid);
            if (!$is_already_scheduled->attemptid) {
                $hall_schedules = $DB->get_record_sql("SELECT h.code venueid, hs.*
                    FROM {hall} h
                    JOIN {hallschedule} hs ON hs.hallid = h.id 
                    WHERE hs.id = :hallscheduleid ", ['hallscheduleid' => $scheduleid]);
                schedule_cisi_exam_slot($exam_record, $hall_schedules, $userid);
            }
        }
        // *************** IKRAM CODE ENDS ******************************
        if($type == 'reschedule') {
            if(is_array($fastresponse->messages)) {
                foreach($fastresponse->messages as $error){
                    $errors[] = $error->message;
                }
                $errormessage = implode(',',$errors);
            } else {
                $errormessage =$fastresponse->messages; 
            }
            $returndata['response']  = (empty($fastresponse) || COUNT($fastresponse->messages) <=0 || $fastresponse->success) ? 'success' : $errormessage;

        } elseif(is_null($type) || $type == 'schedule') {

            if(is_array($fastresponse->messages)) {
                foreach($fastresponse->messages as $error){
                    $errors[] = $error->message;
                }
                $errormessage = implode(',',$errors);
            } else {
                $errormessage =$fastresponse->messages; 
            }
            $returndata['response']  = (empty($fastresponse) || COUNT($fastresponse->messages) <=0 || $fastresponse->success) ? 'success' : $errormessage;
        } else {
            $returndata['response'] = ($response == 'success') ? 'success' : 'failed';
        }

        return $returndata;
    }   
    public static function enrouser_returns() {
        return new external_single_structure(
            array(
                'response' => new external_value(PARAM_RAW, 'response'),
                'returnparams' => new external_value(PARAM_RAW, 'returnparams', VALUE_OPTIONAL),
                'autoapproval' => new external_value(PARAM_RAW, 'autoapproval', VALUE_OPTIONAL)
            )
        );
    }

    public static function exam_attempts_parameters() {
        return new external_function_parameters([
                'options' => new external_value(PARAM_RAW, 'The paging data for the service'),
                'dataoptions' => new external_value(PARAM_RAW, 'The data for the service'),
                'offset' => new external_value(PARAM_INT, 'Number of items to skip from the begging of the result set',
                    VALUE_DEFAULT, 0),
                'limit' => new external_value(PARAM_INT, 'Maximum number of results to return',
                    VALUE_DEFAULT, 0),
                'contextid' => new external_value(PARAM_INT, 'contextid'),
                'filterdata' => new external_value(PARAM_RAW, 'The data for the service'),
        ]);
    }
 
    public static function exam_attempts($options, $dataoptions, $offset = 0, $limit = 0, $contextid, $filterdata) {
        global $OUTPUT, $CFG, $DB,$USER,$PAGE;
        $sitecontext = context_system::instance();
        require_login();
        $PAGE->set_url('/local/exams/index.php', array());
        $PAGE->set_context($sitecontext);
        // Parameter validation.
        $params = self::validate_parameters(
            self::exam_attempts_parameters(),
            [
                'options' => $options,
                'dataoptions' => $dataoptions,
                'offset' => $offset,
                'limit' => $limit,
                'contextid' => $contextid,
                'filterdata' => $filterdata
            ]
        );

        $offset = $params['offset'];
        $limit = $params['limit'];
        $filtervalues = (array)json_decode($filterdata);
        $filtervalues['examid'] = json_decode($dataoptions)->examid;
        $stable = new \stdClass();
        $stable->thead = false;
        $stable->start = $offset;
        $stable->length = $limit;
        $attempts = (new local_exams\local\exams)->examattempts($stable,$filtervalues);

        return [
            'totalcount' => $attempts['totalattempts'],
            'records' => $attempts,
            'length' => $attempts['length'],
            'nodata' =>  get_string('noattemptsavailable','local_exams'),

        ];
    }

    /**
     * Returns description of method result value.
     */
    public static function  exam_attempts_returns() {
        return new external_single_structure([
            'totalcount' => new external_value(PARAM_INT, 'total number of attempts'),
            'length' => new external_value(PARAM_INT, 'total length of attempts'),
            'nodata' => new external_value(PARAM_RAW, 'The data for the service'),
            'records' => new external_single_structure(
                array(
                    'attempts' => new external_multiple_structure(
                        new external_single_structure(
                            array(
                                'id' => new external_value(PARAM_INT, 'id'),
                                'examid' => new external_value(PARAM_INT, 'examid'),
                                'attemptid' => new external_value(PARAM_INT, 'attemptid'),
                                'daysbeforeattempt' => new external_value(PARAM_INT, 'daysbeforeattempt', VALUE_OPTIONAL),
                                'fee' => new external_value(PARAM_INT, 'fee', VALUE_OPTIONAL),
                                'actions' => new external_value(PARAM_BOOL, 'actions', VALUE_OPTIONAL),
                            )
                        )
                    ),
                    'request_view' => new external_value(PARAM_INT, 'request_view', VALUE_OPTIONAL),
                    'nocourses' => new external_value(PARAM_BOOL, 'nocourses', VALUE_OPTIONAL),
                    'totalexams' => new external_value(PARAM_INT, 'totalexams', VALUE_OPTIONAL),
                    'length' => new external_value(PARAM_INT, 'length', VALUE_OPTIONAL),
                )
            )
        ]);
    }

    public static function exam_deleteattempt_parameters(){
        return new external_function_parameters(
            array(
                'attemptid' => new external_value(PARAM_INT, 'attemptid', 0)
            )
        );
    }
    public static function exam_deleteattempt($attemptid){
        global $DB;
        $params = self::validate_parameters(self::exam_deleteattempt_parameters(),
                                    ['attemptid' => $attemptid]);
        $context = context_system::instance();

        $attempt = $DB->record_exists('local_exam_attemptpurchases', ['referenceid' => $attemptid]);
        if (empty($attempt)) {
            $examid = $DB->get_field('local_exam_attempts', 'examid', ['id' => $attemptid]);
            $noofattempts = $DB->get_field('local_exams', 'noofattempts', ['id' => $examid]);
            $data = new stdClass();
            $data->id = $examid;
            $data->noofattempts = $noofattempts-1;
            $DB->update_record('local_exams', $data);
            $DB->delete_records('local_exam_attempts', array('id' => $attemptid));
            return true;
        } else {
            return false;
        }
    }   
    public static function exam_deleteattempt_returns() {
        return new external_value(PARAM_BOOL, 'return');
    }

    //fast exam enrol

    public static function fast_examenrolview_parameters() {
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
    public static function fast_examenrolview($options, $dataoptions, $offset = 0, $limit = 0, $contextid, $filterdata) {
        global $CFG;
        require_login();
        // Parameter validation.
        $params = self::validate_parameters(
            self::fast_examenrolview_parameters(),
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
        $data = (new local_exams\local\exams)->getfastexamenrol($stable, $filtervalues);
        $totalcount = $data['totalrecords'];
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

    public static function fast_examenrolview_returns() {
         return new external_single_structure([
            'options' => new external_value(PARAM_RAW, 'The paging data for the service'),
            'url' => new external_value(PARAM_RAW, 'url'),
            'dataoptions' => new external_value(PARAM_RAW, 'The data for the service'),
            'totalcount' => new external_value(PARAM_INT, 'total number of challenges in result set'),
            'filterdata' => new external_value(PARAM_RAW, 'total number of challenges in result set'),
            'length' => new external_value(PARAM_RAW, 'total number of challenges in result set'),
            'records' => new external_single_structure(
                    array(
                        'hasexamenrol' => new external_multiple_structure(
                            new external_single_structure(
                                array(
                                    'id' => new external_value(PARAM_INT, 'id'),
                                    'type' => new external_value(PARAM_RAW, 'type'),
                                    'username' => new external_value(PARAM_RAW, 'username'),
                                    'centercode' => new external_value(PARAM_RAW, 'centercode'),
                                    'examcode' => new external_value(PARAM_RAW, 'examcode'),
                                    'profilecode' => new external_value(PARAM_RAW, 'profilecode'),
                                    'examlanguage' => new external_value(PARAM_RAW, 'examlanguage'),
                                    'examdatetime' => new external_value(PARAM_RAW, 'examdatetime'),
                                    'purchasedatetime' => new external_value(PARAM_RAW, 'purchasedatetime'),
                                    'createdbyusername' => new external_value(PARAM_RAW, 'createdbyusername'),
                                    'billnumber' => new external_value(PARAM_RAW, 'billnumber'),
                                    'paymentrefid' => new external_value(PARAM_RAW, 'paymentrefid'),
                                    'payementtypes' => new external_value(PARAM_RAW, 'payementtypes'),
                                    'status' => new external_value(PARAM_RAW, 'status'),
                                    'errormessage' => new external_value(PARAM_RAW, 'errorcode'),
                                    'statusdisplay' => new external_value(PARAM_RAW, 'statusdisplay'),
                                )
                            )
                        ),
                        'noexamenrol' => new external_value(PARAM_BOOL, 'noexamenrol', VALUE_OPTIONAL),
                        'totalcount' => new external_value(PARAM_INT, 'totalexamenrol', VALUE_OPTIONAL),
                       'length' => new external_value(PARAM_RAW, 'total number of challenges in result set'),
                        
                    )
                )
        ]);
    }
    public static function tobereplacedusers_parameters() {
        $query = new external_value(PARAM_RAW, 'search query');
        $type = new external_value(PARAM_ALPHANUMEXT, 'Type of data', VALUE_REQUIRED);
        $replacinguserid =new external_value(PARAM_INT, 'replacinguserid', VALUE_OPTIONAL,0);
        $rootid = new external_value(PARAM_TEXT, 'rootid', VALUE_OPTIONAL,null);
        $fieldid = new external_value(PARAM_TEXT, 'fieldid', VALUE_OPTIONAL,null);
       

        $params = array(
            'query' => $query,
            'type' => $type,
            'replacinguserid' => $replacinguserid,
            'rootid' => $rootid,
            'fieldid' => $fieldid
            
        );
        return new external_function_parameters($params);
    }
    public static function tobereplacedusers($query, $type, $replacinguserid, $rootid = null,$fieldid = null) {
        global $PAGE;
        $params = array(
            'query' => $query,
            'type' => $type,
            'replacinguserid' => $replacinguserid,
            'rootid' => $rootid,
            'fieldid' => $fieldid
        );
        $params = self::validate_parameters(self::tobereplacedusers_parameters(), $params);

        switch($params['type']) {
            case 'exam':
                $tobereplacedusers =  (new local_exams\local\exams)->get_tobereplacedusers($params['query'],$params['replacinguserid'],$params['rootid'],$params['fieldid']);
            break;   
            case 'program':
                $tobereplacedusers =  (new local_trainingprogram\local\trainingprogram)->get_tobereplacedusers($params['query'],$params['replacinguserid'],$params['rootid'],$params['fieldid']);
            break; 
            default:
                $tobereplacedusers =  (new local_events\events)->get_tobereplacedusers($params['query'],$params['replacinguserid'],$params['rootid'],$params['fieldid']);
            break; 
        }

          
        return ['status' => true, 'data' => $tobereplacedusers];
    }
    public static function tobereplacedusers_returns() {
        return new external_single_structure(
            array(
                'status' => new external_value(PARAM_INT, 'status: true if success'),
                'data' => new external_multiple_structure(new external_single_structure(
                    array(
                        'id' => new external_value(PARAM_INT, 'status: true if success'),
                        'fullname' => new external_value(PARAM_RAW, 'status: true if success',VALUE_OPTIONAL)
                    )
                ), '', VALUE_OPTIONAL) 
            )
        );

    }

    // ***************** DL-304 IKRAM CODE STARTS HERE **************************
    /**
     * Functions Related to CISI Integration
     */
    /**
     * This funtion will authenticate the user for CISI Integration
     * 
     */
    public static function cisi_authenticate_signin_parameters() {
        return new external_function_parameters(array(
            'clientid' => new external_value(PARAM_RAW, 'CISI Client ID'),
            'clientsecret' => new external_value(PARAM_RAW, 'CISI Client secret'),
            'cisiusername' => new external_value(PARAM_RAW, 'CISI Username'),
            'cisipassword' => new external_value(PARAM_RAW, 'CISI User Password'),
            'cisiscope' => new external_value(PARAM_TEXT, 'Scope'),
        ));
    }

    /**
     * This service will return the auth token for CISI
     */
    public static function cisi_authenticate_signin($clientid, $clientsecret, $cisiusername, $cisipassword, $cisiscope) {
        global $DB;
        $params = self::validate_parameters(
            self::cisi_authenticate_signin_parameters(),
            [
                'clientid' => $clientid,
                'clientsecret' => $clientsecret,
                'cisiusername' => $cisiusername,
                'cisipassword' => $cisipassword,
                'cisiscope' => $cisiscope
            ]
        );
        $cisiservises = new cisi_services();
        $authtoken = $cisiservises->AuthenticationSigninAuthToken($clientid, $clientsecret, $cisiusername, $cisipassword, $cisiscope);
        if ($authtoken->success) {
            $return = [
                'isList' => $authtoken->isList ? $authtoken->isList : 0,
                'success' => $authtoken->success ? $authtoken->success : false,
                'records' => [
                    'authToken' => $authtoken->results->authToken,
                    'expiresAt' => $authtoken->results->expiresAt,
                    'state' => $authtoken->results->state ? $authtoken->results->state: '',
                ]
            ];
        }else{
            $return = [
                'isList' => $authtoken->isList ? $authtoken->isList : false,
                'success' =>  $authtoken->success,
                'records' => [
                    'message' => $authtoken->results[0]->message,
                    'extraInfo'=>  $authtoken->results[0]->extraInfo,
                    'traceID' => $authtoken->results[0]->traceID,
                    'when' => $authtoken->results[0]->when,
                    'httpCode' => $authtoken->results[0]->httpCode,
                    'exceptionName' => $authtoken->results[0]->exceptionName,
                    'target' => $authtoken->results[0]->target,
                    'host' => $authtoken->results[0]->host
                ]
            ];
        }
        return $return;
    }
    /**
     * Returns the data recived from the Authentication Sign In API
     * @return Array of data.
     */
    public static function cisi_authenticate_signin_returns() {
        return new external_single_structure([
            'isList' => new external_value(PARAM_RAW, 'True or False', VALUE_OPTIONAL),
            'success' => new external_value(PARAM_BOOL, 'True or False'),
            'records' => new external_single_structure(
                    array(
                        'authToken' => new external_value(PARAM_RAW, 'Auth Token', VALUE_OPTIONAL),
                        'expiresAt' => new external_value(PARAM_INT, 'Expiry of this access token', VALUE_OPTIONAL),
                        'state' => new external_value(PARAM_RAW, 'state', VALUE_OPTIONAL),
                        // Error Message.
                        'message' => new external_value(PARAM_TEXT, 'message', VALUE_OPTIONAL),
                        'extraInfo'=> new external_value(PARAM_TEXT, 'extraInfo', VALUE_OPTIONAL),
                        'traceID' => new external_value(PARAM_TEXT, 'traceID', VALUE_OPTIONAL),
                        'when' => new external_value(PARAM_TEXT, 'when', VALUE_OPTIONAL),
                        'httpCode' => new external_value(PARAM_RAW, 'httpCode', VALUE_OPTIONAL),
                        'exceptionName' => new external_value(PARAM_RAW, 'exceptionName', VALUE_OPTIONAL),
                        'target' => new external_value(PARAM_RAW, 'target', VALUE_OPTIONAL),
                        'host' => new external_value(PARAM_RAW, 'host', VALUE_OPTIONAL)
                    )
                )
            ]
        );
    }
    /**
     * This funtion will fetch the Access Token for CISI
     * 
     */
    public static function cisi_get_access_token_parameters() {
        return new external_function_parameters(array(
            'authtoken' => new external_value(PARAM_RAW, 'Auth Token recived while authenticating SignIn'),
            'clientid' => new external_value(PARAM_RAW, 'CISI Client ID'),
            'clientsecret' => new external_value(PARAM_RAW, 'CISI Client secret')
        ));
    }

    /**
     * This service will return the access token for CISI
     */
    public static function cisi_get_access_token($authToken, $clientid, $clientsecret) {
        global $DB;
        $params = self::validate_parameters(
            self::cisi_get_access_token_parameters(),
            [
                'authtoken' => $authToken,
                'clientid' => $clientid,
                'clientsecret' => $clientsecret
            ]
        );
        $cisiservises = new cisi_services();
        $accessToken = $cisiservises->AuthenticationTokenAccessToken($authToken, $clientid, $clientsecret);
        if ($accessToken->success) {
            $return = [
                'isList' => $accessToken->isList ? $accessToken->isList : false,
                'success' =>  $accessToken->success ? $accessToken->success : false,
                'records' => [
                    'accessToken' => $accessToken->results->accessToken,
                    'expiresAt'=>  $accessToken->results->expiresAt,
                    'tokenType' => $accessToken->results->tokenType,
                    'personID' => $accessToken->results->personID,
                    'sessionKey' => $accessToken->results->sessionKey,
                    'state' => $accessToken->results->state ? $accessToken->results->state : ''
                ]
            ];
        }else{
            $return = [
                'isList' => $accessToken->isList ? $accessToken->isList : false,
                'success' =>  $accessToken->success,
                'records' => [
                    'message' => $accessToken->results[0]->message,
                    'extraInfo'=>  $accessToken->results[0]->extraInfo,
                    'traceID' => $accessToken->results[0]->traceID,
                    'when' => $accessToken->results[0]->when,
                    'httpCode' => $accessToken->results[0]->httpCode,
                    'exceptionName' => $accessToken->results[0]->exceptionName,
                    'target' => $accessToken->results[0]->target,
                    'host' => $accessToken->results[0]->host
                ]
            ];
        }
        return $return;
    }
    /**
     * Returns the data recived from the Access Token API
     * @return Array of data.
     */
    public static function cisi_get_access_token_returns() {
        return new external_single_structure([
            'isList' => new external_value(PARAM_BOOL, 'True or False'),
            'success' => new external_value(PARAM_BOOL, 'True or False'),
            'records' => new external_single_structure(
                array(
                    'accessToken' => new external_value(PARAM_RAW, 'Auth Token', VALUE_OPTIONAL),
                    'expiresAt' => new external_value(PARAM_INT, 'Expiry of this access token', VALUE_OPTIONAL),
                    'tokenType' => new external_value(PARAM_TEXT, 'Type of the Token Received', VALUE_OPTIONAL),
                    'personID' => new external_value(PARAM_INT, 'Id of the person', VALUE_OPTIONAL),
                    'sessionKey' => new external_value(PARAM_RAW, 'current session', VALUE_OPTIONAL),
                    'state' => new external_value(PARAM_RAW, 'state', VALUE_OPTIONAL),
                    // Error Message.
                    'message' => new external_value(PARAM_TEXT, 'message', VALUE_OPTIONAL),
                    'extraInfo'=> new external_value(PARAM_TEXT, 'extraInfo', VALUE_OPTIONAL),
                    'traceID' => new external_value(PARAM_TEXT, 'traceID', VALUE_OPTIONAL),
                    'when' => new external_value(PARAM_TEXT, 'when', VALUE_OPTIONAL),
                    'httpCode' => new external_value(PARAM_RAW, 'httpCode', VALUE_OPTIONAL),
                    'exceptionName' => new external_value(PARAM_RAW, 'exceptionName', VALUE_OPTIONAL),
                    'target' => new external_value(PARAM_RAW, 'target', VALUE_OPTIONAL),
                    'host' => new external_value(PARAM_RAW, 'host', VALUE_OPTIONAL)
                )
            )]
        );
    }
    /**
     * Service to create CISI user
     * 
     */
    public static function cisi_create_user_parameters() {
        return new external_function_parameters(array(
            'accesstoken' => new external_value(PARAM_RAW, 'Access Token recived while authenticating SignIn'),
            'title' => new external_value(PARAM_TEXT, 'Title of the person'),
            'email' => new external_value(PARAM_RAW, 'Email of the user'),
            'lastname' => new external_value(PARAM_RAW, 'CISI Client ID'),
            'firstname' => new external_value(PARAM_RAW, 'CISI Client secret'),
            'dateofbirth' => new external_value(PARAM_RAW, 'CISI Client secret')
        ));
    }

    /**
     * This service will create a user record on CISI
     */
    public static function cisi_create_user($accessToken, $title, $email, $lastname, $firstname, $dateofbirth) {
        global $DB;
        $params = self::validate_parameters(
            self::cisi_create_user_parameters(),
            [
                'accesstoken' => $accessToken,
                'title' => $title,
                'email' => $email,
                'lastname' => $lastname,
                'firstname' => $firstname,
                'dateofbirth' => $dateofbirth
            ]
        );
        $cisiservises = new cisi_services();
        $newuserobj = $cisiservises->cisi_create_user($accessToken, $title, $email, $lastname, $firstname, $dateofbirth);
        if ($newuserobj->success) {
            $return = [
                'isList' => $newuserobj->isList ? $newuserobj->isList : false,
                'success' =>  $newuserobj->success,
                'records' => [
                    'id' => $newuserobj->results->id,
                    'title'=>  $newuserobj->results->title,
                    'firstName' => $newuserobj->results->firstName,
                    'lastName' => $newuserobj->results->lastName,
                    'email' => $newuserobj->results->email,
                    'personTypeID' => $newuserobj->results->personTypeID,
                    'dob' => $newuserobj->results->dob,
                    'countryID' => $newuserobj->results->countryID
                ]
            ];
        }else{
            $return = [
                'isList' => $newuserobj->isList ? $newuserobj->isList : false,
                'success' =>  $newuserobj->success,
                'records' => [
                    'message' => $newuserobj->results[0]->message,
                    'extraInfo'=>  $newuserobj->results[0]->extraInfo,
                    'traceID' => $newuserobj->results[0]->traceID,
                    'when' => $newuserobj->results[0]->when,
                    'httpCode' => $newuserobj->results[0]->httpCode,
                    'exceptionName' => $newuserobj->results[0]->exceptionName,
                    'target' => $newuserobj->results[0]->target,
                    'host' => $newuserobj->results[0]->host
                ]
            ];
        }
        return $return;
    }
    /**
     * Returns the User creation data.
     * @return Array of data.
     */
    public static function cisi_create_user_returns() {
        return new external_single_structure([
            'isList' => new external_value(PARAM_BOOL, 'True or False'),
            'success' => new external_value(PARAM_BOOL, 'True or False'),
            'records' => new external_single_structure(
                array(
                    'id' => new external_value(PARAM_INT, 'CISI ID of the new user', VALUE_OPTIONAL),
                    'title' => new external_value(PARAM_TEXT, 'Title of the user', VALUE_OPTIONAL),
                    'firstName' => new external_value(PARAM_TEXT, 'First Name', VALUE_OPTIONAL),
                    'lastName' => new external_value(PARAM_TEXT, 'Last Name', VALUE_OPTIONAL),
                    'email' => new external_value(PARAM_RAW, 'User Email', VALUE_OPTIONAL),
                    'personTypeID' => new external_value(PARAM_RAW, 'Person Type Id', VALUE_OPTIONAL),
                    'dob' => new external_value(PARAM_RAW, 'Date Of Birth', VALUE_OPTIONAL),
                    'countryID' => new external_value(PARAM_RAW, 'Country ID of the Person', VALUE_OPTIONAL),
                    // Error Message.
                    'message' => new external_value(PARAM_TEXT, 'message', VALUE_OPTIONAL),
                    'extraInfo'=> new external_value(PARAM_TEXT, 'extraInfo', VALUE_OPTIONAL),
                    'traceID' => new external_value(PARAM_TEXT, 'traceID', VALUE_OPTIONAL),
                    'when' => new external_value(PARAM_TEXT, 'when', VALUE_OPTIONAL),
                    'httpCode' => new external_value(PARAM_RAW, 'httpCode', VALUE_OPTIONAL),
                    'exceptionName' => new external_value(PARAM_RAW, 'exceptionName', VALUE_OPTIONAL),
                    'target' => new external_value(PARAM_RAW, 'target', VALUE_OPTIONAL),
                    'host' => new external_value(PARAM_RAW, 'host', VALUE_OPTIONAL)
                )
            )
        ]);
    }
    /**
     * Service to create CISI user
     * 
     */
    public static function cisi_update_user_parameters() {
        return new external_function_parameters(array(
            'accesstoken' => new external_value(PARAM_RAW, 'Access Token recived while authenticating SignIn'),
            'cisiuserid' => new external_value(PARAM_TEXT, 'CISI User Id'),
            'title' => new external_value(PARAM_TEXT, 'Title of the person'),
            'email' => new external_value(PARAM_RAW, 'Email of the user'),
            'lastname' => new external_value(PARAM_RAW, 'CISI Client ID'),
            'firstname' => new external_value(PARAM_RAW, 'CISI Client secret'),
            'dateofbirth' => new external_value(PARAM_RAW, 'CISI Client secret')
        ));
    }

    /**
     * This service will update the user details at CISI
     */
    public static function cisi_update_user($accessToken, $cisiuserid, $title, $email, $lastname, $firstname, $dateofbirth) {
        global $DB;
        $params = self::validate_parameters(
            self::cisi_update_user_parameters(),
            [
                'accesstoken' => $accessToken,
                'cisiuserid' => $cisiuserid,
                'title' => $title,
                'email' => $email,
                'lastname' => $lastname,
                'firstname' => $firstname,
                'dateofbirth' => $dateofbirth
            ]
        );
        $cisiservises = new cisi_services();
        $update_user = $cisiservises->cisi_update_user($accessToken, $cisiuserid, $title, $email, $lastname, $firstname, $dateofbirth);
         if ($update_user->success) {
            $return = [
                'isList' => $update_user->isList ? $update_user->isList : false,
                'success' =>  $update_user->success,
                'records' => [
                    'id' => $update_user->results->id,
                    'title'=>  $update_user->results->title,
                    'firstName' => $update_user->results->firstName,
                    'lastName' => $update_user->results->lastName,
                    'email' => $update_user->results->email,
                    'personTypeID' => $update_user->results->personTypeID,
                    'dob' => $update_user->results->dob,
                    'countryID' => $update_user->results->countryID
                ]
            ];
        }else{
            $return = [
                'isList' => $update_user->isList ? $update_user->isList : false,
                'success' =>  $update_user->success,
                'records' => [
                    'message' => $update_user->results[0]->message,
                    'extraInfo'=>  $update_user->results[0]->extraInfo,
                    'traceID' => $update_user->results[0]->traceID,
                    'when' => $update_user->results[0]->when,
                    'httpCode' => $update_user->results[0]->httpCode,
                    'exceptionName' => $update_user->results[0]->exceptionName,
                    'target' => $update_user->results[0]->target,
                    'host' => $update_user->results[0]->host
                ]
            ];
        }
        return $return;
    }
    /**
     * Returns the updated data of user
     * @return Array of data.
     */
    public static function cisi_update_user_returns() {
        return new external_single_structure([
            'isList' => new external_value(PARAM_BOOL, 'True or False'),
            'success' => new external_value(PARAM_BOOL, 'True or False'),
            'records' => new external_single_structure(
                array(
                    'id' => new external_value(PARAM_INT, 'CISI ID of the new user', VALUE_OPTIONAL),
                    'title' => new external_value(PARAM_TEXT, 'Title of the user', VALUE_OPTIONAL),
                    'firstName' => new external_value(PARAM_TEXT, 'First Name', VALUE_OPTIONAL),
                    'lastName' => new external_value(PARAM_TEXT, 'Last Name', VALUE_OPTIONAL),
                    'email' => new external_value(PARAM_RAW, 'User Email', VALUE_OPTIONAL),
                    'personTypeID' => new external_value(PARAM_RAW, 'Person Type Id', VALUE_OPTIONAL),
                    'dob' => new external_value(PARAM_RAW, 'Date Of Birth', VALUE_OPTIONAL),
                    'countryID' => new external_value(PARAM_RAW, 'Country ID of the Person', VALUE_OPTIONAL),
                    // Error Message.
                    'message' => new external_value(PARAM_TEXT, 'message', VALUE_OPTIONAL),
                    'extraInfo'=> new external_value(PARAM_TEXT, 'extraInfo', VALUE_OPTIONAL),
                    'traceID' => new external_value(PARAM_TEXT, 'traceID', VALUE_OPTIONAL),
                    'when' => new external_value(PARAM_TEXT, 'when', VALUE_OPTIONAL),
                    'httpCode' => new external_value(PARAM_RAW, 'httpCode', VALUE_OPTIONAL),
                    'exceptionName' => new external_value(PARAM_RAW, 'exceptionName', VALUE_OPTIONAL),
                    'target' => new external_value(PARAM_RAW, 'target', VALUE_OPTIONAL),
                    'host' => new external_value(PARAM_RAW, 'host', VALUE_OPTIONAL)
                )
            )
        ]);
    }
    /**
     * CISI Exam Mapping
     * 
     */
    public static function cisi_exam_mapping_parameters() {
        return new external_function_parameters(array(
            'accesstoken' => new external_value(PARAM_RAW, 'Access Token recived while authenticating SignIn'),
            'cisiuserid' => new external_value(PARAM_INT, 'CISI User Id'),
            'examcode' => new external_value(PARAM_INT, 'ID of Exam'),
            'hallcode' => new external_value(PARAM_RAW, 'Exam Venue ID or Hall Code'),
            'examdate' => new external_value(PARAM_RAW, 'Date on which the exam is scheduled')
        ));
    }

    /**
     * This service will schedule the exam for the given user.
     */
    public static function cisi_exam_mapping($accessToken, $cisiuserid, $examcode, $hallcode, $examdate) {
        global $DB;
        $params = self::validate_parameters(
            self::cisi_exam_mapping_parameters(),
            [
                'accesstoken' => $accessToken,
                'cisiuserid' => $cisiuserid,
                'examcode' => $examcode,
                'hallcode' => $hallcode,
                'examdate' => $examdate
            ]
        );
        $cisiservises = new cisi_services();
        $exam_mapping = $cisiservises->cisi_exam_mapping($accessToken, $cisiuserid, $examcode, $hallcode, $examdate);
        if ($exam_mapping->success) {
            $return = [
                'isList' => $exam_mapping->isList ? $exam_mapping->isList : false,
                'success' =>  $exam_mapping->success ? $exam_mapping->success : false,
                'records' => [
                    'orderID' => $exam_mapping->results->orderID,
                    'basketID'=>  $exam_mapping->results->basketID,
                    'sessionKey' => $exam_mapping->results->sessionKey
                ]
            ];
        }else{
           $return = [
                'isList' => $exam_mapping->isList ? $exam_mapping->isList : false,
                'success' =>  $exam_mapping->success,
                'records' => [
                    'message' => $exam_mapping->results[0]->message,
                    'extraInfo'=>  $exam_mapping->results[0]->extraInfo,
                    'traceID' => $exam_mapping->results[0]->traceID,
                    'when' => $exam_mapping->results[0]->when,
                    'httpCode' => $exam_mapping->results[0]->httpCode,
                    'exceptionName' => $exam_mapping->results[0]->exceptionName,
                    'target' => $exam_mapping->results[0]->target,
                    'host' => $exam_mapping->results[0]->host
                ]
            ];
        }
        return $return;
    }
    /**
     * Returns the exam booking details
     * @return Array of data.
     */
    public static function cisi_exam_mapping_returns() {
        return new external_single_structure([
            'isList' => new external_value(PARAM_BOOL, 'True or False'),
            'success' => new external_value(PARAM_BOOL, 'True or False'),
            'records' => new external_single_structure(
                array(
                    'orderID' => new external_value(PARAM_INT, 'Order ID of Exam', VALUE_OPTIONAL),
                    'basketID' => new external_value(PARAM_TEXT, 'Basket ID', VALUE_OPTIONAL),
                    'sessionKey' => new external_value(PARAM_TEXT, 'Session created', VALUE_OPTIONAL),
                    // Error Message.
                    'message' => new external_value(PARAM_TEXT, 'message', VALUE_OPTIONAL),
                    'extraInfo'=> new external_value(PARAM_TEXT, 'extraInfo', VALUE_OPTIONAL),
                    'traceID' => new external_value(PARAM_TEXT, 'traceID', VALUE_OPTIONAL),
                    'when' => new external_value(PARAM_TEXT, 'when', VALUE_OPTIONAL),
                    'httpCode' => new external_value(PARAM_RAW, 'httpCode', VALUE_OPTIONAL),
                    'exceptionName' => new external_value(PARAM_RAW, 'exceptionName', VALUE_OPTIONAL),
                    'target' => new external_value(PARAM_RAW, 'target', VALUE_OPTIONAL),
                    'host' => new external_value(PARAM_RAW, 'host', VALUE_OPTIONAL)
                )
            )
        ]);
    }

    public static function exam_calculations_parameters(){
        return new external_function_parameters(
            array(
                'productid' => new external_value(PARAM_INT, 'productid', 0),
                'userid' => new external_value(PARAM_RAW, 'userid', 0),
                'entitytype' => new external_value(PARAM_RAW, 'entitytype', 0),
                'refundtype' => new external_value(PARAM_RAW, 'refundtype', 0),
                'policyconfirm' => new external_value(PARAM_INT, 'policyconfirm', VALUE_OPTIONAL),
                'enrolltype' => new external_value(PARAM_RAW, 'PARAM_INT', VALUE_OPTIONAL),                
            )
        );
    }  
    public static function exam_calculations($productid, $userid, $entitytype, $refundtype, $policyconfirm = 1,$enrolltype = 0){
        global $DB, $USER,$CFG;
        $systemcontext = context_system::instance();
        $params = self::validate_parameters(self::exam_calculations_parameters(),
                                    ['productid' => $productid,
                                     'userid' => $userid,
                                     'entitytype' => $entitytype,
                                     'refundtype' => $refundtype,
                                     'policyconfirm' => $policyconfirm,
                                     'enrolltype' => $enrolltype,
                                    ]);
        $context = context_system::instance();

        if($productid){  
            $product = $DB->get_record('tool_products',['id'=>$productid]);
            if($entitytype == 'exam') {
                if($product->category == 6) {
                    $tablename = 'local_exam_attempts';
                    $profilerecord =$DB->get_record('local_exam_attempts',['id'=>$product->referenceid]);
                    $schedule = $DB->get_record('local_exam_userhallschedules', ['examid'=>$profilerecord->examid,'attemptid'=>$product->referenceid,'userid'=>$userid]);
                    $date = $schedule->examdate;
                    $attemptid = $profilerecord->attemptid;
                    $entityid =$profilerecord->examid;
                    $referenceid =$schedule->profileid;
                } else {
                    $profilerecord =$DB->get_record('local_exam_profiles',['id'=>$product->referenceid]);
                    $attempsnumber =(int) (new local_exams\local\exams)->quiz_attemptscount($profilerecord->examid);
                    $sql = "select id, attemptid from {local_exam_attempts} where id = (select min(id) from {local_exam_attempts} where examid = $profilerecord->examid AND  attemptid > $attempsnumber)";
                    $examattempt = $DB->get_record_sql($sql);
                    $tablename = 'local_exam_profiles';
                    $date = $DB->get_field_sql('SELECT examdate FROM {local_exam_userhallschedules} WHERE
                                examid ='.$profilerecord->examid.' AND
                                userid ='.$userid.' ORDER by id DESC '
                            );

                    $attemptid =$attempsnumber;
                    $entityid =$profilerecord->examid;
                    $referenceid =$profilerecord->id;
                }

                $ownedby = $DB->get_field('local_exams','ownedby',['id'=>$profilerecord->examid]);
                $policies=new \local_exams\local\policies($entitytype, $date, $refundtype,$tablename,$ownedby, $attemptid);
            } else if($entitytype == 'trainingprogram') {
                $record =$DB->get_record('tp_offerings',['id'=>$product->referenceid]);
                $date = $record->startdate;
                $policies=new \local_trainingprogram\local\policies($entitytype, $date, $refundtype);
                $entityid =$record->trainingid;
                $referenceid =$record->id;

            } else {
                $record =$DB->get_record('local_events',['id'=>$product->referenceid]);
                $date = $record->startdate;
                $policies=new \local_events\policies($entitytype, $date, $refundtype);
                $entityid =$record->id;
                $referenceid =$record->id;

            }
            $data =  $policies->refund_details($userid,$productid,$policyconfirm,(int)$enrolltype);
            $enrolled_by = $policies->enrolled_by($userid,$productid);
            $data->contextid = $context->id;
            $data->loginuseradmin = (is_siteadmin() || has_capability('local/organization:manage_examofficial', $systemcontext) || has_capability('local/organization:manage_communication_officer', $systemcontext) || has_capability('local/organization:manage_trainingofficial', $systemcontext)) ? 1 : 0;
            $data->examdate = $date;
            $data->trainee = ($enrolled_by) ? (($enrolled_by->shortname == 'trainee') ? 1 : 0) : 0;
            $data->entityid = $entityid;
            $data->referenceid = $referenceid;
            $data->tuserid = $userid;

            return $data;
        } else {
            throw new moodle_exception('Error in cancelling');            
        }
    }   
    public static function exam_calculations_returns() {
        return new external_single_structure([
            'refundamount' => new external_value(PARAM_RAW, 'refundamount', VALUE_OPTIONAL),
            'deductamount' => new external_value(PARAM_RAW, 'deductamount', VALUE_OPTIONAL),
            'productprice' => new external_value(PARAM_RAW, 'productprice', VALUE_OPTIONAL),
            'cannotcancel' => new external_value(PARAM_BOOL, 'cannotcancel', VALUE_OPTIONAL),
            'isorgofficial' => new external_value(PARAM_INT, 'isorgofficial', VALUE_OPTIONAL),
            'contextid' => new external_value(PARAM_INT, 'contextid', VALUE_OPTIONAL),
            'loginuseradmin' => new external_value(PARAM_INT, 'loginuseradmin', VALUE_OPTIONAL),
            'newinvoiceamount' => new external_value(PARAM_RAW, 'newinvoiceamount', VALUE_OPTIONAL),
            'invoicenumber' => new external_value(PARAM_RAW, 'invoicenumber', VALUE_OPTIONAL),
            'examdate' => new external_value(PARAM_RAW, 'examdate', VALUE_OPTIONAL),
            'trainee' => new external_value(PARAM_INT, 'trainee', VALUE_OPTIONAL),
            'newamount' => new external_value(PARAM_RAW, 'newinvoiceamount', VALUE_OPTIONAL),
            'seats' => new external_value(PARAM_INT, 'seats', VALUE_OPTIONAL),
            'entityid' => new external_value(PARAM_INT, 'entityid', VALUE_OPTIONAL),
            'referenceid' => new external_value(PARAM_INT, 'referenceid', VALUE_OPTIONAL),
            'tuserid' => new external_value(PARAM_RAW, 'tuserid', VALUE_OPTIONAL),
        ]);
    }
    
    public static function cancellationrefund_parameters(){
        return new external_function_parameters(
            array(
                'productid' => new external_value(PARAM_INT, 'productid', 0),
                'userid' => new external_value(PARAM_INT, 'userid', 0),
                'entitytype' => new external_value(PARAM_RAW, 'entitytype'),
                'refundtype' => new external_value(PARAM_RAW, 'refundtype'),
            )
        );
    }  
    public static function cancellationrefund($productid, $userid, $entitytype, $refundtype){
        global $DB;
        $params = self::validate_parameters(self::cancellationrefund_parameters(),
                                    ['productid' => $productid, 'userid' => $userid, 'entitytype' => $entitytype, 'refundtype' => $refundtype ]);
        $context = context_system::instance();

        if($productid){
            $data = (new local_exams\local\cancelentities)->trainee_refundinfo($entitytype, $productid,$refundtype, $userid, true);

            return $data;
        } else {
            throw new moodle_exception('Error in cancelling');
        }
    }   
    public static function cancellationrefund_returns() {
        return new external_value(PARAM_BOOL, 'return');
    }

    public static function canceluser_parameters(){
        return new external_function_parameters(
            array(
                'userid' => new external_value(PARAM_INT, 'userid'),
                'examprice' => new external_value(PARAM_INT, 'examprice'),
                'amount' => new external_value(PARAM_RAW, 'amount'),
                'refundamount' => new external_value(PARAM_RAW, 'refundamount'),
                'newinvoiceamount' => new external_value(PARAM_RAW, 'newinvoiceamount'),
                'newamount' => new external_value(PARAM_RAW, 'newamount'),
                'productid' => new external_value(PARAM_INT, 'productid'),
                'policyconfirm' => new external_value(PARAM_INT, 'policyconfirm'),
                'examdate' => new external_value(PARAM_RAW, 'examdate'),
                'invoicenumber' => new external_value(PARAM_RAW, 'examdate'),
                'entitytype' => new external_value(PARAM_RAW, 'entitytype'),
                'enrolltype' => new external_value(PARAM_INT, 'enrolltype',VALUE_OPTIONAL),
                'cangenerateinvoice' => new external_value(PARAM_INT, 'cangenerateinvoice',VALUE_OPTIONAL),
            )
        );
    }  
    public static function canceluser($userid, $examprice, $amount, $refundamount, $newinvoiceamount, $newamount, $productid, $policyconfirm, $examdate, $invoicenumber,$entitytype,$enrolltype = 0,$cangenerateinvoice = 1){
        global $DB, $USER, $CFG;
        $params = self::validate_parameters(self::canceluser_parameters(),
                                    ['userid' => $userid , 
                                    'examprice' => $examprice ,
                                    'amount' => $amount , 
                                    'refundamount' => $refundamount, 
                                    'newinvoiceamount' => $newinvoiceamount, 
                                    'newamount' => $newamount, 
                                    'productid' =>$productid ,
                                    'policyconfirm' => $policyconfirm,
                                    'examdate' =>$examdate,
                                    'invoicenumber' =>$invoicenumber,
                                    'entitytype' =>$entitytype,
                                    'enrolltype' =>$enrolltype,
                                    'cangenerateinvoice'=>$cangenerateinvoice,
                                    
                                  ]
                                );
        $context = context_system::instance();
        $data =new stdClass();
        $data->userid = $userid;
        $data->examprice = $examprice;
        $data->amount = $amount;
        $data->refundamount = $refundamount;
        $data->newinvoiceamount = $newinvoiceamount;
        $data->newamount = $newamount;
        $data->productid = $productid;
        $data->policyconfirm = $policyconfirm;
        $data->examdate = $examdate;
        $data->invoicenumber = $invoicenumber;
        $data->entitytype = $entitytype;
        $data->enrolltype = $enrolltype;
        $data->cangenerateinvoice = $cangenerateinvoice;

        if($data->productid && $data->userid) {
            if($entitytype == 'exam') {
                $referanceid = (int)$DB->get_field('tool_products','referenceid',['id'=>$productid]);
                $profile =$DB->get_record('local_exam_profiles',['id'=>$referanceid]);
                $userhallschedules =$DB->get_record('local_exam_userhallschedules',['examid'=>$profile->examid,'userid'=>$data->userid ]);
                if(!empty($userhallschedules->hallscheduleid) && ($userhallschedules->hallscheduleid > 0)){
                    $response = (new local_exams\local\exams)->exam_cancel_user($data);
                }else{
                    $response = (new local_exams\local\exams)->exam_unenrollmet($profile->id,$data->userid,'cancel');
                }
                $redirecturl['returnurl'] = (is_siteadmin() || has_capability('local/organization:manage_organizationofficial', context_system::instance())) ? $CFG->wwwroot.'/local/exams/examusers.php?id='.$profile->examid:  $CFG->wwwroot.'/local/exams/index.php' ; 
                $redirecturl['response'] = ($response == 'success') ? 'success' : $response;
            } else if($entitytype == 'trainingprogram') {
                $redirecturl['returnurl'] =  (new local_trainingprogram\local\trainingprogram)->program_cancel_user($data);
                $redirecturl['response'] = ($redirecturl['returnurl']) ?  'success' : 'noresponse';
            } else {
                $redirecturl['returnurl'] =  (new \local_events\events)->event_cancel_user($data);
                $redirecturl['response'] = ($redirecturl['returnurl']) ?  'success' : 'noresponse';
            }
        } else {
            throw new moodle_exception('Error in cancelling');
        }
        return $redirecturl;
    }   
    public static function canceluser_returns() {
        return new external_single_structure(
            array(
                'returnurl' => new external_value(PARAM_RAW, 'returnurl'),
                'response' => new external_value(PARAM_RAW, 'response')
            )
        );
    }

    public static function dataforexamcancellation_parameters() {
        return new external_function_parameters(
            array(
                'examid' => new external_value(PARAM_INT, 'examid'),
                'profileid' => new external_value(PARAM_INT, 'profileid'),
                'userid' => new external_value(PARAM_INT, 'userid'),
                'examdate' => new external_value(PARAM_INT, 'examdate'),
                'policyconfirm' => new external_value(PARAM_INT, 'policyconfirm', VALUE_OPTIONAL),
            )
        );
    }
    public static function dataforexamcancellation($examid,$profileid,$userid,$examdate, $policyconfirm){
        global $DB;
        $params = self::validate_parameters(self::dataforexamcancellation_parameters(),
                                    ['examid' => $examid,
                                     'profileid' => $profileid,
                                     'userid' => $userid,
                                     'examdate' => $examdate,
                                     'policyconfirm' => $policyconfirm,
                                    ]);
        $context = context_system::instance();
        if($examid && $profileid && $userid){
            $sdata =new stdClass();
            $sdata->examid = $examid;
            $sdata->profileid = $profileid;
            $sdata->userid = $userid;
            $sdata->examdate = $examdate;
            $sdata->policyconfirm = $policyconfirm;
            $returndata=(new local_exams\local\exams)->dataforexamcancellation($sdata);

            return $returndata;
        } else {
            throw new moodle_exception('Error while getting the data');
        }
    }   
    public static function dataforexamcancellation_returns() {
        return new external_single_structure([
            'productid' => new external_value(PARAM_INT, 'productid', VALUE_OPTIONAL),
            'orgofficialenrolled' => new external_value(PARAM_INT, 'orgofficialenrolled', VALUE_OPTIONAL),
            'trainee' => new external_value(PARAM_INT, 'trainee', VALUE_OPTIONAL),
            'enrolledbyadmin' => new external_value(PARAM_INT, 'enrolledbyadmin', VALUE_OPTIONAL),
            'amount' => new external_value(PARAM_RAW, 'amount', VALUE_OPTIONAL),
            'refundamount' => new external_value(PARAM_RAW, 'deductamount', VALUE_OPTIONAL),
            'newinvoiceamount' => new external_value(PARAM_RAW, 'newinvoiceamount', VALUE_OPTIONAL),
            'invoicenumber' => new external_value(PARAM_RAW, 'invoicenumber', VALUE_OPTIONAL),
            'deductamount' => new external_value(PARAM_RAW, 'deductamount', VALUE_OPTIONAL),
        ]);
    }

    public static function replaceuser_parameters(){
        return new external_function_parameters(
            array(
                'productid' => new external_value(PARAM_INT, 'productid'),
                'rootid' => new external_value(PARAM_INT, 'rootid'),
                'fieldid' => new external_value(PARAM_INT, 'fieldid'),
                'fromuserid' => new external_value(PARAM_INT, 'fromuserid'),
                'touserid' => new external_value(PARAM_INT, 'touserid'),
                'replacementfee' => new external_value(PARAM_INT, 'replacementfee'),
                'entitytype' => new external_value(PARAM_RAW, 'entitytype'),
                'policyconfirm' => new external_value(PARAM_INT,'policyconfirm'),
                'costtype' => new external_value(PARAM_INT,'costtype'),
                'enrollinguserid' => new external_value(PARAM_INT,'enrollinguserid'),
                'cangenerateinvoice' => new external_value(PARAM_INT,'cangenerateinvoice'),
                
            )
        );
    }  
    public static function replaceuser($productid,$rootid, $fieldid, $fromuserid, $touserid, $replacementfee,$entitytype,$policyconfirm,$costtype,$enrollinguserid,$cangenerateinvoice){
        global $DB;
        $params = self::validate_parameters(self::replaceuser_parameters(),
                                    [
                                    'productid' => $productid , 
                                    'rootid' => $rootid, 
                                    'fieldid' => $fieldid, 
                                    'fromuserid' => $fromuserid ,
                                    'touserid' => $touserid , 
                                    'replacementfee' => $replacementfee, 
                                    'entitytype' => $entitytype, 
                                    'policyconfirm' => $policyconfirm, 
                                    'costtype' => $costtype, 
                                    'enrollinguserid' => $enrollinguserid, 
                                    'cangenerateinvoice'=>$cangenerateinvoice
                                     
                                    
                                  ]
                                );
        $context = context_system::instance();
        if($productid && $rootid && $fieldid) {
            $data =new stdClass();
            $data->productid = $productid;
            $data->rootid = $rootid;
            $data->fieldid = $fieldid;
            $data->fromuserid = $fromuserid;
            $data->touserid = $touserid;
            $data->replacementfee = $replacementfee;
            $data->entitytype = $entitytype;
            $data->policyconfirm = $policyconfirm;
            $data->costtype = $costtype;
            $data->enrollinguserid = $enrollinguserid;
            $data->cangenerateinvoice = $cangenerateinvoice;
            if($entitytype == 'exam') {
                $response =  (new local_exams\local\exams)->exam_replacement_process($data);
                $returndata['response'] = ($response) ? 'success' : $response;
            } else if($entitytype == 'trainingprogram') {
                $response =  (new local_trainingprogram\local\trainingprogram)->program_replacement_process($data);
                $returndata['response'] = ($response) ? 'success' : $response;
            } else {
                $response = (new local_events\events)->event_replacement_process($data);
                $returndata['response'] = ($response) ? 'success' : $response;
            }
        } else {
            throw new moodle_exception('Error in cancelling');
        }
        return $returndata;
    }   
    public static function replaceuser_returns() {
        return new external_single_structure(
            array(
                'response' => new external_value(PARAM_RAW, 'response')
            )
        );
    }

    public static function rescheduleuser_parameters(){
        return new external_function_parameters(
            array(
                'productid' => new external_value(PARAM_INT, 'productid'),
                'userid' => new external_value(PARAM_INT, 'fromuserid'),
                'deductamount' => new external_value(PARAM_RAW, 'deductamount'),
                'scheduleid' => new external_value(PARAM_INT, 'scheduleid'),
            )
        );
    }  
    public static function rescheduleuser($productid,$userid,$deductamount,$scheduleid){
        global $DB;
        $params = self::validate_parameters(self::rescheduleuser_parameters(),
                                    ['productid' => $productid, 
                                    'userid' => $userid, 
                                    'deductamount' => $deductamount,
                                    'scheduleid' => $scheduleid
                                  ]
                                );
        $context = context_system::instance();
        if($productid && $userid) {
            $data =new stdClass();
            $data->productid = $productid;
            $data->userid = $userid;
            $data->deductamount = $deductamount;
            (new local_exams\local\exams)->exam_reschedule_process($data);
            $profileid = $DB->get_field('tool_products', 'referenceid',  ['id' => $productid]);
            $examid = $DB->get_field('local_exam_profiles', 'examid',  ['id' => $profileid]);
            (new local_exams\local\exams)->exam_enrollmet($profileid, $userid, $scheduleid, 'reschedule');
        } else {

            throw new moodle_exception('Error in cancelling');
        }
        return ['userid' => $userid, 'profileid' => $profileid, 'examid' => $examid];
    }   
    public static function rescheduleuser_returns() {
        return new external_single_structure([
            'userid' => new external_value(PARAM_INT, 'userid', VALUE_OPTIONAL),
            'profileid' => new external_value(PARAM_INT, 'profileid', VALUE_OPTIONAL),
            'examid' => new external_value(PARAM_INT, 'examid', VALUE_OPTIONAL)
        ]);
    }


    public static function validateexamschedule_parameters(){
        return new external_function_parameters(
            array(
                'product_id' => new external_value(PARAM_INT, 'productid'),
                'hallscheduelid' => new external_value(PARAM_INT, 'scheduleid'),
                'profileid' => new external_value(PARAM_INT, 'profileid',VALUE_OPTIONAL),
            )
        );
    }  
    public static function validateexamschedule($product_id,$hallscheduelid, $profileid = 0){
        global $DB;
        $params = self::validate_parameters(self::validateexamschedule_parameters(),
                                    ['product_id' => $product_id, 
                                    'hallscheduelid' => $hallscheduelid,
                                    'profileid' => $profileid,
                                  ]
                                );
        $context = context_system::instance();

        $accessstatus = (new \local_exams\local\exams)->access_fast_service('hallavailability');
        if ($accessstatus) {
            $data=new stdClass;
            $params['transactiontypes'] = 'register';
            $apidata = (new \local_exams\local\exams)->prepare_fast_apidata($params, $data);
            $apidata['validation'] = 1;
            $fastapi = new \local_exams\local\fast_service($apidata);
            $response = $fastapi->hall_availability();
            $returndata  = new stdClass();
            $returndata->data = ($response) ? $response->data : '';
            $returndata->success = ($response) ? $response->success : '';
            $returndata->messages = ($response) ? $response->messages : get_string('invalid_fast_user_details','local_exams');
        } else {
            $returndata  = new stdClass();
            $returndata->data = '';
            $returndata->success = true;
            $returndata->messages = '';
        }

        return $returndata;
    }   
    public static function validateexamschedule_returns() {
        return new external_single_structure([
            'data' => new external_value(PARAM_RAW, 'data', VALUE_OPTIONAL),
            'success' => new external_value(PARAM_RAW, 'success', VALUE_OPTIONAL),
            'messages' => new external_value(PARAM_RAW, 'messages', VALUE_OPTIONAL)
        ]);
    }
    public static function get_orgorderdetails_parameters(){
        return new external_function_parameters(
            array(
                'entityid' => new external_value(PARAM_INT, 'entityid', 0),
                'referenceid' => new external_value(PARAM_INT, 'referenceid', 0),
                'tuserid'   => new external_value(PARAM_RAW, 'tuserid',VALUE_OPTIONAL),
                'type'   => new external_value(PARAM_RAW, 'type',VALUE_OPTIONAL),
            )
        );

    }  
    public static function get_orgorderdetails($entityid, $referenceid, $tuserid=0, $type){
        global $DB, $USER;
        $context = context_system::instance();
        $params = self::validate_parameters(
            self::get_orgorderdetails_parameters(),
            [
                'entityid' => $entityid,
                'referenceid' => $referenceid,
                'tuserid' => $tuserid,
                'type'=>$type,

            ]);
        $traineeids = explode(',', base64_decode($tuserid));
        $orgorder = new stdClass();
        if ($type == 'exam') {
            $orgorder->tablename = 'local_exam_profiles';
            $orgorder->fieldid = $referenceid;
        } elseif($type == 'program') {
            $orgorder->tablename = 'tp_offerings';
            $orgorder->fieldid = $referenceid;
        } else {
            $orgorder->tablename = 'local_events';
            $orgorder->fieldid = $entityid;
        }

        $orgorder->selectedseats = sizeof($traineeids);
        $orgorder->fieldname = 'id';
        $orgorder->parentfieldid = $entityid;
        $orgorder->tuserid = $tuserid;
        $orgorderresponse = (new local_exams\local\exams)->get_orgorderinfo($orgorder);
        $returndata['response'] = 'success';
        $returndata['returnparams'] = $orgorderresponse['returnparams'];
        $returndata['autoapproval'] = $orgorderresponse['autoapproval'];
        $returndata['hasprivateandinvoice'] = $orgorderresponse['hasprivateandinvoice'];
        $returndata['existinginvoice_number'] = $orgorderresponse['existinginvoice_number'];
        $returndata['discountprice'] = $orgorderresponse['discountamount'];
        $returndata['discounttype'] = $orgorderresponse['discounttype'];
        $returndata['discounttableid'] = $orgorderresponse['discounttableid'];
        return $returndata;
    }   
    public static function get_orgorderdetails_returns() {
        return new external_single_structure(
            array(
                'response' => new external_value(PARAM_RAW, 'response'),
                'returnparams' => new external_value(PARAM_RAW, 'returnparams', VALUE_OPTIONAL),
                'autoapproval' => new external_value(PARAM_RAW, 'autoapproval', VALUE_OPTIONAL),
                'hasprivateandinvoice' => new external_value(PARAM_INT, 'hasprivateandinvoice', VALUE_OPTIONAL),
                'existinginvoice_number' => new external_value(PARAM_RAW, 'existinginvoice_number', VALUE_OPTIONAL),
                'discountprice' => new external_value(PARAM_RAW, 'discountprice', VALUE_OPTIONAL),
                'discounttype' => new external_value(PARAM_RAW, 'discounttype', VALUE_OPTIONAL),
                'discounttableid' => new external_value(PARAM_INT, 'discounttableid', VALUE_OPTIONAL)
            )
        );
    }
    public static function attempt_request_parameters(){
        return new external_function_parameters(
            array(
                'examid' => new external_value(PARAM_INT, 'examid'),
                'userid' => new external_value(PARAM_INT, 'userid'),
                'profileid' => new external_value(PARAM_INT, 'profileid'),
                'lastattemptprofileid' => new external_value(PARAM_INT, 'lastattemptprofileid'),
                'hallscheduleid' => new external_value(PARAM_INT, 'hallscheduleid')
            )
        );
    }
    public static function attempt_request($examid, $userid, $profileid, $lastattemptprofileid, $hallscheduleid){
        global $DB, $USER;
        $context = context_system::instance();
        $params = self::validate_parameters(
            self::attempt_request_parameters(),
            [
                'examid' => $examid,
                'userid' => $userid,
                'profileid' => $profileid,
                'lastattemptprofileid' => $lastattemptprofileid,
                'hallscheduleid' => $hallscheduleid,
            ]
        );

        $scheduleid = $hallscheduleid;
        $sql = "SELECT h.name, h.code as hallcode, leu.examdate, hs.starttime, hs.endtime, leu.attemptid
                FROM {hall} h 
                JOIN {hallschedule} hs ON hs.hallid = h.id
                JOIN {local_exam_userhallschedules} leu ON leu.hallscheduleid = hs.id 
                WHERE leu.id =".$scheduleid;
        $hallschedule = $DB->get_record_sql($sql);
        $params['previousgrade'] = 0;
        $sql = "SELECT le.code as examcode, lep.profilecode 
                  FROM {local_exams} le
                  JOIN {local_exam_profiles} lep ON lep.examid = le.id
                 WHERE lep.id =".$lastattemptprofileid;
        $codes = $DB->get_record_sql($sql);
        $params['examcode'] = $codes->examcode;
        $params['profilecode'] = $codes->profilecode;
        $params['username'] = $DB->get_field('user', 'username', ['id'=>$userid]);
        $params['hallcode'] = $hallschedule->hallcode;
        $params['grade'] = 0;
        $params['achievementdate'] = userdate($hallschedule->examdate, '%Y-%m-%d');
        if ($hallschedule->attemptid == 0) {
            $params['attemptcount'] = 1;
        } else {
            $examattemptid = $DB->get_field('local_exam_attempts', 'attemptid', ['id'=>$hallschedule->attemptid]);
            $params['attemptcount'] = $examattemptid;
        }
        $params['certificatecode'] = 'No';
        $params['examdate'] = userdate($hallschedule->examdate, '%Y-%m-%d');
        $params['starttime'] = userdate($hallschedule->examdate, '%X');
        $params['endtime'] = userdate($hallschedule->examdate, '%X');

        // $attemptinfo = unserialize(base64_decode($params['examinfo']));

        $accessstatus = (new local_exams\local\exams)->access_fast_service('userattemptstatus');
        if ($accessstatus) {
            $attemptapi = new \local_exams\local\attemptapi();
            $response = $attemptapi->attempt_api($params);
            $result = json_decode($response);
            if (empty($result)) {
                $status = false;
                $message = get_string('noresponse');
            } elseif ($result->debuginfo!='Null') {
                $status = false;
                $message = $result->debuginfo;
            } else {
                $status = true;
                $message = '';
            }
        } else {
            $status = true;
            $message = '';
        }

        return ['status' => $status, 'response' => $message];
    }   
    public static function attempt_request_returns() {
        return new external_function_parameters(
            array(
                'status' => new external_value(PARAM_BOOL, 'status'),
                'response' => new external_value(PARAM_RAW, 'response', VALUE_OPTIONAL)
            )
        );
    }

    public static function enrollmentconfirmations_parameters() {
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
    public static function enrollmentconfirmations($options, $dataoptions, $offset = 0, $limit = 0, $contextid, $filterdata) {
        global $DB, $PAGE, $CFG;
        require_login();
        $params = self::validate_parameters(
            self::enrollmentconfirmations_parameters(),
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
        $examid = json_decode($dataoptions)->examid;
        $profileid = json_decode($dataoptions)->profileid;
        $organization = json_decode($dataoptions)->organization;
        $orgofficial = json_decode($dataoptions)->orgofficial;
        $cusers =  json_decode($dataoptions)->cusers;
        $scheduleid =  json_decode($dataoptions)->scheduleid;
        
        $stable = new \stdClass();
        $stable->thead = false;
        $stable->start = $offset;
        $stable->length = $limit;
        $etype= 'bulkenrollment';
        $data = (new local_exams\local\exams)->get_listof_enrollmentconfirmations($stable, $filtervalues,$dataoptions);
        $totalcount = $data['totalrequests'];
        $uusers = base64_decode(json_decode($dataoptions)->cusers);
        $ausers =  $DB->get_fieldset_sql("SELECT userid from {local_users} WHERE FIND_IN_SET(userid,'$uusers') AND bulkenrollstatus = 0  AND bulkenrolltype = 'exam' ");
        $users = implode(',',$ausers);
        $invoicerecords = $DB->get_records_sql("SELECT productid,COUNT(id) AS userscount FROM {local_exam_userhallschedules} WHERE examid =$examid  AND FIND_IN_SET(userid,'$users')AND productid > 0 AND enrolstatus IN (0,1) AND enrolltype IN (1,2) GROUP BY productid ");

        foreach($invoicerecords AS $record) {
            $productrecord = $DB->get_record('tool_products',['id'=>$record->productid]);
            $tax_free = $DB->get_field('local_exams','tax_free',['id' => $examid]);
            $tax_slab = get_config('tool_product', 'tax_percentage');
            $total_price = $productrecord->price*$record->userscount;

            $discountparams= new stdClass();
            $discountparams->tablename = 'local_exam_profiles';
            $discountparams->fieldid = $profileid;
            $discountparams->selectedseats = $record->userscount;
            $discountparams->organization = $organization;
            $discountdata =(new product)->get_orgofficial_discountdata($discountparams);
            $discount =  $discountdata->discount > 0 ? round(($total_price * (($discountdata->discount/100))),2) : 0;
            $record->discount =$discount;
            $priceafterdiscount = $total_price - $record->discount;
            $taxes = (new local_exams\local\exams)->caluculate_taxes($priceafterdiscount, $tax_slab);
            $item_taxes = ($tax_free == 0) ? $taxes :0;
            $record->fee = number_format($total_price,2);
            $record->vat =($item_taxes > 0) ? number_format($item_taxes,2) : 0;
            $record->total = number_format(($priceafterdiscount + $item_taxes),2);
        }  
             
        return [
           'totalcount' => $totalcount,
            'length' => $totalcount,
            'filterdata' => $filterdata,
            'records' =>$data,
            'options' => $options,
            'dataoptions' => $dataoptions,
            'url' => $CFG->wwwroot,
            'examid' => $examid,
            'profileid' => $profileid,
            'organization' => $organization,
            'orgofficial' => $orgofficial,
            'cusers' => $cusers,
            'scheduleid'=>$scheduleid,
            'roleid'=>(int)$DB->get_field('role','id',['shortname'=>'trainee']),
            'invoice'=>(!empty($invoicerecords)) ? array_values($invoicerecords) : array(),
            'hasinvoice'=> (COUNT($invoicerecords) > 0)? true :false,
            'discount' => $discount ? $discount : 0, 
            'discounttableid' =>($discountdata->id > 0)  ? (int)$discountdata->id  : 0, 
            'discounttype' => ($discountdata->type)  ? $discountdata->type  : '',  
            'confirmurl'=>$CFG->wwwroot.'/local/exams/hallschedule.php?examid='.$examid.'&profileid='.$profileid.'&type='.$etype.'&tuserid='.$cusers.'&organization='.$organization.'&orgofficial='.$orgofficial
        ];
    }
    public static function enrollmentconfirmations_returns() {
        return new external_single_structure([
          'options' => new external_value(PARAM_RAW, 'The paging data for the service'),
          'url' => new external_value(PARAM_RAW, 'url'),
          'examid' => new external_value(PARAM_INT, 'examid'),
          'profileid' => new external_value(PARAM_INT, 'profileid'),
          'organization' => new external_value(PARAM_INT, 'organization'),
          'orgofficial' => new external_value(PARAM_INT, 'orgofficial'),
          'cusers' => new external_value(PARAM_RAW, 'cusers'),
          'roleid' => new external_value(PARAM_INT, 'roleid'),
          'scheduleid' => new external_value(PARAM_RAW, 'scheduleid'),
          'invoice' => new external_multiple_structure(
                new external_single_structure(
                    array(
                    'fee' => new external_value(PARAM_RAW, 'fee'),
                    'vat' => new external_value(PARAM_RAW, 'vat'),
                    'total' => new external_value(PARAM_RAW, 'total'),
                    )
                )
            ), '', VALUE_OPTIONAL,        
          'hasinvoice' => new external_value(PARAM_RAW, 'hasinvoice'), 
          'discount' => new external_value(PARAM_RAW, 'discount', VALUE_OPTIONAL),
          'discounttableid' => new external_value(PARAM_INT, 'discounttableid', VALUE_OPTIONAL),
          'discounttype' => new external_value(PARAM_RAW, 'discounttype', VALUE_OPTIONAL),
          'confirmurl' => new external_value(PARAM_RAW, 'confirmurl'),  
          'dataoptions' => new external_value(PARAM_RAW, 'The data for the service'),
          'totalcount' => new external_value(PARAM_INT, 'total number of challenges in result set'),
          'filterdata' => new external_value(PARAM_RAW, 'total number of challenges in result set'),
          'length' => new external_value(PARAM_RAW, 'total number of challenges in result set'),
          'records' => new external_single_structure(
                  array(
                      'hascourses' => new external_multiple_structure(
                        new external_single_structure(
                            array(
                                'userid' => new external_value(PARAM_INT, 'userid'),
                                'identitynumber' => new external_value(PARAM_RAW, 'identitynumber'),
                                'fullname' => new external_value(PARAM_RAW, 'fullname'),
                                'examname' => new external_value(PARAM_RAW, 'examname'),
                                'examcode' => new external_value(PARAM_RAW, 'examcode'),
                                'profilecode' => new external_value(PARAM_RAW, 'profilecode'),
                                'profilelang' => new external_value(PARAM_RAW, 'profilelang'),
                                'attemptnumber' => new external_value(PARAM_INT, 'attemptnumber'),     
                                'actionview' => new external_value(PARAM_RAW, 'actionview'),
                                'attemptfee' => new external_value(PARAM_RAW, 'attemptfee'),
                                'examdateandtime' => new external_value(PARAM_RAW, 'examdateandtime'),
                                'errormessage' => new external_multiple_structure(
                                    new external_single_structure(
                                        array(
                                        'message' => new external_value(PARAM_RAW, 'total'),
                                        )
                                    )
                                ), '', VALUE_OPTIONAL,  
                                          
                            )
                        )
                    ),
                    'request_view' => new external_value(PARAM_INT, 'request_view', VALUE_OPTIONAL),
                    'nocourses' => new external_value(PARAM_BOOL, 'nocourses', VALUE_OPTIONAL),
                    'totalrequests' => new external_value(PARAM_INT, 'totalrequests', VALUE_OPTIONAL),
                    'length' => new external_value(PARAM_INT, 'length', VALUE_OPTIONAL),
                )
            )
        ]);
    } 

    public static function removeenrollmentconfirmation_parameters(){
        return new external_function_parameters(
            array(
                'rootid' => new external_value(PARAM_INT, 'rootid', 0),
                'fieldid' => new external_value(PARAM_INT, 'fieldid', 0),
                'userid' => new external_value(PARAM_INT, 'userid', 0),
                'type' => new external_value(PARAM_RAW, 'type', VALUE_OPTIONAL)
            )
        );
    }  
    public static function removeenrollmentconfirmation($rootid,$fieldid,$userid,$type){
        global $DB;
        $params = self::validate_parameters(self::removeenrollmentconfirmation_parameters(),
                                    ['rootid' => $rootid,
                                    'fieldid' => $fieldid,
                                    'userid' => $userid,
                                    'type' => $type
                                    ]);
        $context = context_system::instance();
        if($rootid && $fieldid && $userid && $type) {
            $DB->execute('UPDATE {local_users} SET bulkenrollstatus = 1 WHERE userid = '.$userid.'');
            if($type == 'program') {
                $DB->delete_records('program_enrollments',['programid'=>(int)$rootid,'offeringid'=>(int)$fieldid,'userid'=>(int)$userid,'enrolltype'=>1]);
            } else {
                $DB->delete_records('exam_enrollments',['examid'=>(int)$rootid,'profileid'=>(int)$fieldid,'userid'=>(int)$userid,'enrolltype'=>1]);
                $DB->delete_records('local_exam_userhallschedules',['examid'=>(int)$rootid,'profileid'=>(int)$fieldid,'userid'=>(int)$userid,'enrolltype'=>1]);
            }
         } else {
            throw new moodle_exception('Error');
         }
         return true;
    }   
    public static function removeenrollmentconfirmation_returns() {
        return new external_value(PARAM_BOOL, 'return');
    }

    public static function registerbulkenrollusers_parameters(){
        return new external_function_parameters(
            array(
                'rootid' => new external_value(PARAM_INT, 'rootid', 0),
                'fieldid' => new external_value(PARAM_INT, 'fieldid', 0),
                'roleid' => new external_value(PARAM_INT, 'roleid', 0),
                'cusers' => new external_value(PARAM_RAW, 'cusers', VALUE_OPTIONAL),
                'organization' => new external_value(PARAM_INT, 'organization', 0),
                'scheduleid' => new external_value(PARAM_INT, 'scheduleid', 0),
                'orgofficial' => new external_value(PARAM_INT, 'orgofficial', 0),
                'actionfor' => new external_value(PARAM_RAW, 'actionfor',VALUE_OPTIONAL),
                
            )
        );
    }  
    public static function registerbulkenrollusers($rootid,$fieldid,$roleid,$cusers,$organization,$scheduleid,$orgofficial,$actionfor){
        global $DB,$USER;
        $params = self::validate_parameters(self::registerbulkenrollusers_parameters(),
                                    [
                                    'rootid' => $rootid,
                                    'fieldid' => $fieldid,
                                    'roleid'=>$roleid,
                                    'cusers' => $cusers,
                                    'organization' => $organization,
                                    'scheduleid' => $scheduleid,
                                    'orgofficial' => $orgofficial,
                                    'actionfor'=>$actionfor
                                    ]);
        $context = context_system::instance();
        $fastresponse = new stdClass();
        $role_id = ($roleid > 0) ? $roleid :(int)$DB->get_field('role','id',['shortname'=>'trainee']);
        if($rootid && $fieldid && $role_id && $scheduleid && $cusers && $organization && $orgofficial && $actionfor) {
            $traineeids = base64_decode($cusers);
            $ausers =  $DB->get_fieldset_sql("SELECT userid from {local_users} WHERE FIND_IN_SET(userid,'$traineeids') AND bulkenrollstatus = 0 ");
            $ausers= explode(',',(implode(',',$ausers)));
           
            foreach ($ausers as $traineeid) {
                $traineeid = (int) $traineeid;
                if($actionfor == 'exams') {
                    // Checking Hall Availability settings are Enabled or not
                    $accessstatus = (new \local_exams\local\exams)->access_fast_service('examreservation');
                    if ($accessstatus) {
                        $examinstance = new local_exams\local\exams();
                        $examinfodata = $DB->get_record_sql('SELECT e.code,ep.profilecode,ep.language,ee.timecreated,ee.usercreated FROM {local_exams} as e 
                                            JOIN {local_exam_profiles} as ep ON ep.examid = e.id    
                                            JOIN {exam_enrollments} as ee ON ee.examid=e.id AND ee.profileid=ep.id
                                            WHERE e.id =:examid AND ep.id =:profileid
                                            ',['examid' => $rootid, 'profileid' => $fieldid]);
                        $hallinfodata = $DB->get_record_sql('SELECT hs.startdate as examdate,hs.starttime,h.code as hallcode, h.id as hallid FROM  {hall} as h  
                                            JOIN {hallschedule} as hs ON hs.hallid=h.id    
                                            WHERE hs.id =:scheduleid 
                                            ',['scheduleid' => $scheduleid]);
                        $examinfo = new stdClass();
                        $examinfo->code = $examinfodata->code;
                        $hallcode = $DB->get_field('hall', 'code', ['id'=>$hallinfodata->hallid]);
                        $examinfo->hallcode = $hallcode;
                        $examinfo->profilecode = $examinfodata->profilecode;
                        $examinfo->examdate = $hallinfodata->examdate;
                        $examinfo->starttime = $hallinfodata->starttime;
                        $examinfo->timecreated = time();
                        $examinfo->language = $examinfodata->language;
                        $examinfo->userid = $traineeid;
                        $examinfo->createdbyuserid = $examinfodata->usercreated;
                        $examinfo->userorganization = $DB->get_field('local_organization','licensekey',['id'=>$organization]);
                        $fastresponse= $examinstance->fast_exam_api($examinfo);
                    } else {
                        $fastresponse->success = true;
                    }

                    $autoapproval =(int) $DB->get_field('local_organization','autoapproval',['id'=>$organization]);
                    if(empty($fastresponse) || COUNT($fastresponse->messages) <=0 || $fastresponse->success) {
                        
                        $DB->execute("UPDATE {local_exam_userhallschedules} SET enrolstatus = 0 WHERE examid = $rootid AND profileid = $fieldid  AND  hallscheduleid= $scheduleid  AND productid != 0 AND userid = $traineeid AND organization = $organization AND  enrolltype = 1 ");
    
                        $DB->execute("UPDATE {exam_enrollments} SET enrolstatus = 0 WHERE examid = $rootid AND profileid = $fieldid AND hallscheduleid= $scheduleid   AND userid = $traineeid AND organization = $organization AND enrolltype = 1");
                         
                    }

                } else {
                    $fastresponse->success= true;
                }
            }
        } else {
            throw new moodle_exception('Error');
        }
        if(is_array($fastresponse->messages)) {
            foreach($fastresponse->messages as $error){
                $errors[] = $error->message;
            }
            $errormessage = implode(',',$errors);
        } else {
            $errormessage =$fastresponse->messages; 
        }
        $returndata['response']  = (empty($fastresponse) || COUNT($fastresponse->messages) <=0 || $fastresponse->success) ? 'success' : $errormessage;
        return $returndata;
    }   
    public static function registerbulkenrollusers_returns() {
        return new external_single_structure(
            array(
                'response' => new external_value(PARAM_RAW, 'response'),
            )
        );
    }

    public static function generate_sadad_for_bulkenrollusers_parameters(){
        return new external_function_parameters(
            array(
                'rootid' => new external_value(PARAM_INT, 'rootid', 0),
                'fieldid' => new external_value(PARAM_INT, 'fieldid', 0),
                'roleid' => new external_value(PARAM_INT, 'roleid', 0),
                'scheduleid' => new external_value(PARAM_INT, 'scheduleid', 0),
                'type' => new external_value(PARAM_RAW, 'type', VALUE_OPTIONAL),
                'tuserid' => new external_value(PARAM_RAW, 'tuserid', VALUE_OPTIONAL),
                'organization' => new external_value(PARAM_INT, 'organization', 0),
                'orgofficial' => new external_value(PARAM_INT, 'orgofficial', 0),
                'actionfor' => new external_value(PARAM_RAW, 'actionfor',VALUE_OPTIONAL),
                'discount' => new external_value(PARAM_RAW, 'discount',VALUE_OPTIONAL),
                'discounttableid' => new external_value(PARAM_RAW, 'discounttableid',VALUE_OPTIONAL),
                'discounttype' => new external_value(PARAM_RAW, 'discounttype',VALUE_OPTIONAL),
                
            )
        );
    }  
    public static function generate_sadad_for_bulkenrollusers($rootid,$fieldid,$roleid,$scheduleid,$type,$tuserid,$organization,$orgofficial,$actionfor,$discount = 0,$discounttableid = 0,$discounttype = null){
        global $DB,$CFG;
        $params = self::validate_parameters(self::generate_sadad_for_bulkenrollusers_parameters(),
                                    [
                                    'rootid' => $rootid,
                                    'fieldid' => $fieldid,
                                    'roleid'=>$roleid,
                                    'scheduleid' => $scheduleid,
                                    'type' => $type,
                                    'tuserid' => $tuserid,
                                    'organization' => $organization,
                                    'orgofficial' => $orgofficial,
                                    'actionfor'=>$actionfor,
                                    'discount'=>$discount,
                                    'discounttableid'=>$discounttableid,
                                    'discounttype'=>$discounttype,
                                    ]);
        $context = context_system::instance();
        $role_id = ($roleid > 0) ? $roleid :(int)$DB->get_field('role','id',['shortname'=>'trainee']);
        if($rootid && $fieldid && $role_id && $scheduleid && $tuserid && $organization && $orgofficial && $actionfor) {
            $sdata = new stdClass();
            $sdata->rootid = $rootid;
            $sdata->fieldid = $fieldid;
            $sdata->roleid = $role_id;
            $sdata->scheduleid = $scheduleid;
            $sdata->type = $type;
            $sdata->tuserid = $tuserid;
            $sdata->organization = $organization;
            $sdata->orgofficial = $orgofficial;
            $sdata->actionfor = $actionfor;
            $sdata->discount = $discount;
            $sdata->discounttableid = $discounttableid;
            $sdata->discounttype = $discounttype;
            $returndata=  (new local_exams\local\exams)->sadad_for_bulkenrollusers($sdata);
        } else {
            throw new moodle_exception('Error');
        }
        return $returndata;
    }   
    public static function generate_sadad_for_bulkenrollusers_returns() {
        return new external_single_structure(
            array(
                'response' => new external_value(PARAM_RAW, 'response'),
                'returnurl' => new external_value(PARAM_RAW, 'returnurl'),
                
            )
        );
    }

    /**
     * Fetch the List of exam Schedules
     * 
     */
    public static function list_exam_schedules_parameters() {
        return new external_function_parameters([
            'options' => new external_value(PARAM_RAW, 'The paging data for the service', VALUE_OPTIONAL),
            'dataoptions' => new external_value(PARAM_RAW, 'The data for the service', VALUE_OPTIONAL),
            'offset' => new external_value(PARAM_INT, 'Number of items to skip from the begging of the result set',
                VALUE_DEFAULT, 0),
            'limit' => new external_value(PARAM_INT, 'Maximum number of results to return',
                VALUE_DEFAULT, 0),
            'contextid' => new external_value(PARAM_INT, 'contextid', VALUE_OPTIONAL),
            'filterdata' => new external_value(PARAM_RAW, 'filters applied', VALUE_OPTIONAL),
        ]);
    }
      public static function list_exam_schedules($options=false, $dataoptions=false, $offset = 0, $limit = 0,$contextid=1, $filterdata=false) {
           global $DB, $CFG, $PAGE;
           $params = self::validate_parameters(
            self::list_exam_schedules_parameters(),
            [
                'options' => $options,
                'dataoptions' => $dataoptions,
                'offset' => $offset,
                'limit' => $limit,
                'contextid' => $contextid,
                'filterdata' => $filterdata,
            ]
        );
        $PAGE->set_context(context_system::instance());
        require_once($CFG->dirroot. '/local/exams/lib.php');
        $offset = $params['offset'];
        $limit = $params['limit'];
        $filtervalues = json_decode($filterdata);
        $dataoptions = json_decode($dataoptions);
        $stable = new \stdClass();
        $stable->thead = false;
        $stable->start = $offset;
        $stable->length = $limit;
        $renderer = $PAGE->get_renderer('local_exams');
        $schedules = get_exam_schedules($dataoptions->examid, $stable, $filtervalues);
        $totalcount = $schedules['totalrecords'];
        $return = [
            'totalcount' => $totalcount,
            'length' => $totalcount,
            'filterdata' => $filterdata,
            'records' => array_values($schedules['schedules']),
            'options' => $options,
            'dataoptions' => json_encode($dataoptions)
        ];
        return $return;
    }
    public static function list_exam_schedules_returns(){
        return new external_single_structure([
            'options' => new external_value(PARAM_RAW, 'The paging data for the service'),
            'dataoptions' => new external_value(PARAM_RAW, 'The data for the service'),
            'totalcount' => new external_value(PARAM_INT, 'total number of challenges in result set'),
            'filterdata' => new external_value(PARAM_RAW, 'total number of challenges in result set'),
            'length' => new external_value(PARAM_RAW, 'total number of challenges in result set'),
            'records' => new external_multiple_structure(
                new external_single_structure(
                        array(
                            'row_num' => new external_value(PARAM_INT, 'row_num', VALUE_OPTIONAL),
                            'scheduleid' => new external_value(PARAM_INT, 'scheduleid', VALUE_OPTIONAL),
                            'examid' => new external_value(PARAM_INT, 'examid', VALUE_OPTIONAL),
                            'hallid' => new external_value(PARAM_INT, 'hallid', VALUE_OPTIONAL),
                            'hallname' => new external_value(PARAM_RAW, 'hallname', VALUE_OPTIONAL),
                            'startdate' => new external_value(PARAM_RAW, 'startdate', VALUE_OPTIONAL),
                            'city' => new external_value(PARAM_RAW, 'city', VALUE_OPTIONAL),
                            'maplocation' => new external_value(PARAM_RAW, 'maplocation', VALUE_OPTIONAL),
                            'entrancegate' => new external_value(PARAM_RAW, 'entrancegate', VALUE_OPTIONAL),
                            'buildingname' => new external_value(PARAM_RAW, 'buildingname', VALUE_OPTIONAL),
                            'hallstarttime' => new external_value(PARAM_RAW, 'hallstarttime', VALUE_OPTIONAL),
                            'hallendtime' => new external_value(PARAM_RAW, 'hallendtime', VALUE_OPTIONAL),
                            'code' => new external_value(PARAM_RAW, 'code', VALUE_OPTIONAL),
                        )
                    )
                )
        ]);
    }
    public static function delete_schedule_parameters() {
        return new external_function_parameters([
            'scheduleid' => new external_value(PARAM_INT, 'Schedule id')
        ]);
    }
    public static function delete_schedule($scheduleid) {
           global $DB, $CFG, $PAGE;
           $params = self::validate_parameters(
            self::delete_schedule_parameters(),
            [
                'scheduleid' => $scheduleid
            ]
        );
        $PAGE->set_context(context_system::instance());
        require_once($CFG->dirroot. '/local/exams/lib.php');
        
        $isdelete = delete_exam_schedule($scheduleid);
        
        $return = [
            'status' => $isdelete
        ];
        return $return;
    }
    public static function delete_schedule_returns(){
        return new external_single_structure([
            'status' => new external_value(PARAM_BOOL, 'The paging data for the service'),
        ]);
    }
    /**
     * Fetch the List of exam Schedules
     * 
     */
    public static function view_reservations_parameters() {
        return new external_function_parameters([
            'options' => new external_value(PARAM_RAW, 'The paging data for the service', VALUE_OPTIONAL),
            'dataoptions' => new external_value(PARAM_RAW, 'The data for the service', VALUE_OPTIONAL),
            'offset' => new external_value(PARAM_INT, 'Number of items to skip from the begging of the result set',
                VALUE_DEFAULT, 0),
            'limit' => new external_value(PARAM_INT, 'Maximum number of results to return',
                VALUE_DEFAULT, 0),
            'contextid' => new external_value(PARAM_INT, 'contextid', VALUE_OPTIONAL),
            'filterdata' => new external_value(PARAM_RAW, 'filters applied', VALUE_OPTIONAL),
        ]);
    }
    public static function view_reservations($options=false, $dataoptions=false, $offset = 0, $limit = 0,$contextid=1, $filterdata=false) {
           global $DB, $CFG, $PAGE;
           $params = self::validate_parameters(
            self::view_reservations_parameters(),
            [
                'options' => $options,
                'dataoptions' => $dataoptions,
                'offset' => $offset,
                'limit' => $limit,
                'contextid' => $contextid,
                'filterdata' => $filterdata,
            ]
        );
        $PAGE->set_context(context_system::instance());
        require_once($CFG->dirroot. '/local/exams/lib.php');
        $offset = $params['offset'];
        $limit = $params['limit'];
        $filtervalues = json_decode($filterdata);
        $dataoptions = json_decode($dataoptions);
        $stable = new \stdClass();
        $stable->thead = false;
        $stable->start = $offset;
        $stable->length = $limit;
        $renderer = $PAGE->get_renderer('local_exams');
        $reservations = fetch_reservations_for_exam($dataoptions->examid, $filtervalues, $stable);
        // print_r($reservations);die;
        $totalcount = $reservations['totalrecords'];
        $return = [
            'totalcount' => $totalcount,
            'length' => $totalcount,
            'filterdata' => $filterdata,
            'records' => array_values($reservations['reservations']),
            'options' => $options,
            'globalinput' => $renderer->global_filter($options),
            'dataoptions' => json_encode($dataoptions)
        ];
        return $return;
    }
    public static function view_reservations_returns(){
        return new external_single_structure([
            'options' => new external_value(PARAM_RAW, 'The paging data for the service'),
            'dataoptions' => new external_value(PARAM_RAW, 'The data for the service'),
            'totalcount' => new external_value(PARAM_INT, 'total number of challenges in result set'),
            'filterdata' => new external_value(PARAM_RAW, 'total number of challenges in result set'),
            'length' => new external_value(PARAM_RAW, 'total number of challenges in result set'),
            'records' => new external_multiple_structure(
                new external_single_structure(
                        array(
                            'row_num' => new external_value(PARAM_INT, 'row_num', VALUE_OPTIONAL),
                            'examid' => new external_value(PARAM_INT, 'examid', VALUE_OPTIONAL),
                            'reservationid' => new external_value(PARAM_INT, 'reservationid', VALUE_OPTIONAL),
                            'fullname' => new external_value(PARAM_RAW, 'fullname', VALUE_OPTIONAL),
                            'hallid' => new external_value(PARAM_INT, 'hallid', VALUE_OPTIONAL),
                            'hallname' => new external_value(PARAM_RAW, 'hallname', VALUE_OPTIONAL),
                            'profilecode' => new external_value(PARAM_RAW, 'profilecode', VALUE_OPTIONAL),
                            'date' => new external_value(PARAM_RAW, 'date', VALUE_OPTIONAL),
                            'reservationstart' => new external_value(PARAM_RAW, 'reservationstart', VALUE_OPTIONAL),
                            'reservationend' => new external_value(PARAM_RAW, 'reservationend', VALUE_OPTIONAL),
                            'id_number' => new external_value(PARAM_RAW, 'id_number', VALUE_OPTIONAL),
                            'attemptid' => new external_value(PARAM_RAW, 'attemptid', VALUE_OPTIONAL),
                        )
                    )
                )
        ]);
    }
    /**
     * Enrolment By Assesment Operation
     * 
     */
    public static function calculate_invoice_parameters() {
        return new external_function_parameters([
            'productprice' => new external_value(PARAM_INT, 'Product Price'),
            'num_users' => new external_value(PARAM_INT, 'Number of users'),
            'orgid' => new external_value(PARAM_INT, 'ID of the organization'),
            'org_officialid' => new external_value(PARAM_INT, 'ID of the organization Official'),
            'examid' => new external_value(PARAM_INT, 'ID of the Exam'),
        ]);
    }
    public static function calculate_invoice($productprice, $num_users, $orgid, $org_officialid,$examid) {
       global $OUTPUT, $PAGE, $CFG, $DB;
       $PAGE->set_context(context_system::instance());
       $params = self::validate_parameters( self::calculate_invoice_parameters(),[
                'productprice' => $productprice,
                'num_users' => $num_users,
                'orgid' => $orgid,
                'org_officialid' => $org_officialid,
                'examid' => $examid,
            ]
        );
        $lang= current_language();
        $fieldname = ($lang == 'ar') ? 'fullnameinarabic' : 'fullname' ;
        $orgname = $DB->get_field('local_organization', $fieldname, ['id' => $params['orgid']]);
        // Officials
        $tax_free = $DB->get_field('local_exams','tax_free',['id' => $params['examid']]);
        $newamt = $params['productprice'] * $params['num_users'];
        $tax_slab = get_config('tool_product', 'tax_percentage');
        $taxes = ($newamt * $tax_slab/100);
        $item_taxes = ($tax_free == 0) ? $taxes :0;
        $totalamount = round(($newamt + $item_taxes),2);
        $data = [
            'productprice' => $params['productprice'],
            'tax_slab' => $tax_slab,
            'newamount' => $newamt,
            'totalamount' => $totalamount,
            'taxes' => $taxes,
            'org_officials' => $form,
            'num_users' => $num_users,
            'orgname' => $orgname,
            'orgid' => $params['orgid']
        ];

        $invoice = ['invoice' => $OUTPUT->render_from_template('local_exams/render_invoice_amt', $data)];
        
        return $invoice;
    }
    public static function calculate_invoice_returns(){
        return new external_single_structure([
            'invoice' => new external_value(PARAM_RAW, 'Invoice Generated')
        ]);
    }/**
     * Get List Of Organisation Officials
     * 
     */
    public static function get_org_officials_parameters() {
        return new external_function_parameters([
            'orgid' => new external_value(PARAM_INT, 'ID of the organization')
        ]);
    }
    public static function get_org_officials($orgid) {
       global $OUTPUT, $PAGE, $CFG, $DB;
       $PAGE->set_context(context_system::instance());
       $params = self::validate_parameters( self::get_org_officials_parameters(),[
                'orgid' => $orgid
            ]
        );
        $lang= current_language();
        
        $fieldname = ($lang == 'ar') ? 'fullnameinarabic' : 'fullname' ;
        $orgname = $DB->get_field('local_organization', $fieldname, ['id' => $params['orgid']]);
        // Officials
        $fullname = (new \local_trainingprogram\local\trainingprogram())->user_fullname_case();
        $orgroleid = $DB->get_field('role', 'id', ['shortname' => 'organizationofficial']);

        $orgofficials = $DB->get_records_sql("SELECT u.id, $fullname 
            FROM {local_users} lc 
            JOIN {user} u ON u.id = lc.userid 
            JOIN {role_assignments} ra ON ra.userid = u.id
            WHERE lc.organization = :orgid AND ra.roleid = :roleid ", ['orgid' => $params['orgid'], 'roleid' => $orgroleid]);

        $options = '<option value="">Select officials</option>';
        foreach ($orgofficials as $officials) { 
            $options .= '<option value="'.$officials->id.'">'.$officials->fullname.'</option>';
        }
        $data = [
            'orgid' => $params['orgid'],
            'options' => $options
        ];

        $officials = ['officials' => $OUTPUT->render_from_template('local_exams/render_officials', $data)];
        
        return $officials;
    }
    public static function get_org_officials_returns(){
        return new external_single_structure([
            'officials' => new external_value(PARAM_RAW, 'Invoice Generated')
        ]);
    }
    /**
     * Create invoice for the trainees selected and send it to the organization official
     * @param $productprice
     * @param $users
     * @param $profileid
     * @param $examid
     * @param $productid
     * @param $orgid
     * @param $orgofficial
     * 
     */
    public static function send_invoice_parameters() {
        return new external_function_parameters([
            'productprice' => new external_value(PARAM_INT, 'price of the product'),
            'users' => new external_value(PARAM_RAW, 'ID of the users selected in comma separated'),
            'profileid' => new external_value(PARAM_RAW, 'ID of the profile'),
            'examid' => new external_value(PARAM_INT, 'ID of the exam'),
            'productid' => new external_value(PARAM_INT, 'ID of the product'),
            'orgid' => new external_value(PARAM_INT, 'ID of the organization'),
            'orgofficial' => new external_value(PARAM_INT, 'ID of the organization Official')
        ]);
    }
    public static function send_invoice($productprice, $users, $profileid, $examid, $productid, $orgid, $orgofficial) {
       global $OUTPUT, $PAGE, $CFG, $DB;
       $PAGE->set_context(context_system::instance());
       $params = self::validate_parameters( self::send_invoice_parameters(),[
                'productprice' => $productprice,
                'users' => $users,
                'profileid' => $profileid,
                'examid' => $examid,
                'productid' => $productid,
                'orgid' => $orgid,
                'orgofficial' => $orgofficial
            ]
        );
        $productdata = new \stdClass();
        $productdata->users = explode(',',$users);
        $productdata->examid = $examid;
        $productdata->productid = $productid;
        $productdata->profileid = $profileid;
        $productdata->organization = $orgid;
        $productdata->orgofficial = $orgofficial;
        $productdata->enrolementtype = 'assessment_operation_enrolments';
        $data = (new local_exams\local\exams())->generate_sadad_for_bulkenrollusers($productdata, $productdata->productid, count($productdata->users));
        if ($data['invoice_id']) {
            $button = html_writer::tag('button', 'x', ['type' => 'button', 'class' => 'close', 'data-dismiss' => 'alert']);
            $successmsg = get_string('invoicegenerated', 'local_exams');
            $msg = html_writer::div($button. $successmsg, 'alert alert-success alert-block fade in ', ['role' => "alert"]);
            $return = [
                    'status' => 'success',
                    'invoice_id' => $data['invoice_id'],
                    'msg' => $msg
            ];
        }
        return $return;
    }
    public static function send_invoice_returns(){
        return new external_single_structure([
            'status' => new external_value(PARAM_RAW, 'status'),
            'msg' => new external_value(PARAM_RAW, 'message', VALUE_OPTIONAL),
            'invoice_id' => new external_value(PARAM_INT, 'invoice_id', VALUE_OPTIONAL),
        ]);
    }

    public static function revert_enroluser_parameters(){
        return new external_function_parameters(
            array(
                'productid' => new external_value(PARAM_RAW, 'productid',VALUE_OPTIONAL),
                'examid'    => new external_value(PARAM_INT, 'examid', 0),
                'profileid' => new external_value(PARAM_INT, 'profileid', 0),
                'scheduleid'=> new external_value(PARAM_INT, 'scheduleid', 0),
                'type'      => new external_value(PARAM_RAW, 'type',VALUE_OPTIONAL),
                'tuserid'   => new external_value(PARAM_RAW, 'tuserid',VALUE_OPTIONAL),
                'orderid'   => new external_value(PARAM_RAW, 'orderid',VALUE_OPTIONAL),
                'organization'=> new external_value(PARAM_INT, 'organization', 0),
            )
        );

    }  
    public static function revert_enroluser($productid,$examid, $profileid, $scheduleid, $type = false, $tuserid=0, $orderid=0, $organization = 0){
        global $DB, $USER;
        $params = self::validate_parameters(
            self::enrouser_parameters(),
            [
                'examid' => $examid,
                'profileid' => $profileid,
                'scheduleid' => $scheduleid,
                'type' => $type,
                'tuserid' => $tuserid,
                'orderid' => $orderid,
                'productid'=>$productid,
                'organization'=>$organization
            ]);
        $getdata=$DB->get_record_sql('SELECT id,purchasedseats,approvalseats,tablename,fieldname,fieldid,orguserid,paymentid, organization FROM {tool_order_approval_seats} 
                         WHERE id=:id', ['id' => $orderid]);

        $data = new \stdClass();
        if(isset($getdata->purchasedseats)){
                    $getdatast=$DB->get_record('tool_org_order_seats',array('tablename'=>$getdata->tablename,
                                                                            'fieldname'=>$getdata->fieldname,
                                                                            'fieldid'=>$getdata->fieldid,
                                                                            'orguserid'=>$getdata->orguserid,
                                                                            'organization'=>$getdata->organization,
                                                                            )
                                                                    ,'id,purchasedseats,availableseats,approvalseats, organization');
                $data->id=$getdatast->id;
                $data->purchasedseats=($getdatast->purchasedseats-$getdata->purchasedseats);
                $DB->update_record('tool_org_order_seats', $data);
                $paymentid = $DB->get_field('tool_order_approval_seats', 'paymentid', ['id' => $orderid]);
                $transactionid = $DB->get_field('tool_org_order_payments', 'transactionid', ['id' => $paymentid]);
                if($DB->record_exists('tool_product_sadad_invoice',['invoice_number' => $transactionid])) {
                    (new \tool_product\telr)->void_invoice($transactionid);
                }
                $DB->delete_records('tool_org_order_payments', array('id' => $paymentid));
                $DB->delete_records('tool_order_approval_seats', array('id' => $orderid));
                return true;
        }else{
                return false;
        }
    }
    public static function revert_enroluser_returns() {
        return new external_value(PARAM_BOOL, 'return');
    }

    /**
     * Exam enrolled users
     * 
     */
    public static function get_exam_users_parameters() {
        return new external_function_parameters([
            // 'status' => new external_value(PARAM_BOOL, 'bool', VALUE_OPTIONAL),
            'options' => new external_value(PARAM_RAW, 'The paging data for the service', VALUE_OPTIONAL),
            'dataoptions' => new external_value(PARAM_RAW, 'The data for the service', VALUE_OPTIONAL),
            'offset' => new external_value(PARAM_INT, 'Number of items to skip from the begging of the result set',
                VALUE_DEFAULT, 0),
            'limit' => new external_value(PARAM_INT, 'Maximum number of results to return',
                VALUE_DEFAULT, 0),
            'contextid' => new external_value(PARAM_INT, 'contextid', VALUE_OPTIONAL),
            'filterdata' => new external_value(PARAM_RAW, 'filters applied', VALUE_OPTIONAL),
        ]);
    }
    public static function get_exam_users($options=false, $dataoptions=false, $offset = 0, $limit = 0,$contextid=1, $filterdata=false) {
       global $OUTPUT, $PAGE, $CFG, $DB;
       $PAGE->set_context(context_system::instance());
       $params = self::validate_parameters( self::get_exam_users_parameters(),[
                // 'status' => $status,
                'options' => $options,
                'dataoptions' => $dataoptions,
                'offset' => $offset,
                'limit' => $limit,
                'contextid' => $contextid,
                'filterdata' => $filterdata,
            ]
        );
        $filtervalues = json_decode($filterdata);
        $dataoptions = json_decode($dataoptions);
        $stable = new \stdClass();
        $stable->thead = false;
        $stable->start = $offset;
        $stable->length = $limit;
        $data = (new local_exams\local\exams())->user_get_exam_users($stable,$filtervalues);
        $return = [
            'records' => $data['users'],
            'totalcount' => $data['totalcount'],
            'templateid' => $data['templateid'],
            'options' => $options,
            'dataoptions' => json_encode($dataoptions),
            'length' => $usercount,
            'filterdata' => $filterdata,
            'url' => $CFG->wwwroot,
        ];
        return $return;
    }
    public static function get_exam_users_returns(){
        return new external_single_structure([
            'totalcount' => new external_value(PARAM_INT, 'total number of record set'),
            'templateid' => new external_value(PARAM_INT, 'ID of the Template'),
            'records' => new external_multiple_structure(
                new external_single_structure(
                        array(
                            'enrolid' => new external_value(PARAM_INT, 'enrolid'),
                            'examid' => new external_value(PARAM_INT, 'examid', VALUE_OPTIONAL),
                            'templateid' => new external_value(PARAM_INT, 'templateid', VALUE_OPTIONAL),
                            'userid' => new external_value(PARAM_INT, 'userid'),
                            'id_number' => new external_value(PARAM_RAW, 'id_number'),
                            'fullname' => new external_value(PARAM_RAW, 'fullname'),
                            'examname' => new external_value(PARAM_RAW, 'examname', VALUE_OPTIONAL),
                            'certificate_code' => new external_value(PARAM_RAW, 'certificate_code', VALUE_OPTIONAL),
                            // 'profilecode' => new external_value(PARAM_RAW, 'profilecode', VALUE_OPTIONAL),
                            'name' => new external_value(PARAM_RAW, 'name', VALUE_OPTIONAL),
                            'examdate' => new external_value(PARAM_RAW, 'examdate', VALUE_OPTIONAL),
                            'examtime' => new external_value(PARAM_RAW, 'examtime', VALUE_OPTIONAL),
                            'cer_id' => new external_value(PARAM_INT, 'cer_id', VALUE_OPTIONAL),
                            'hallscheduleid' => new external_value(PARAM_INT, 'hallscheduleid', VALUE_OPTIONAL)
                        )
                    )
                ),
            'options' => new external_value(PARAM_RAW, 'The paging data for the service'),
            'dataoptions' => new external_value(PARAM_RAW, 'The data for the service'),
            'filterdata' => new external_value(PARAM_RAW, 'total number of challenges in result set'),
            'length' => new external_value(PARAM_RAW, 'total number of challenges in result set'),
            'url' => new external_value(PARAM_RAW, 'Site config object'),
        ]);
    }
    /**
     * fetch_filterdata
     * 
     */
    public static function fetch_filterdata_parameters() {
        return new external_function_parameters([
            'query' => new external_value(PARAM_RAW, 'Query of the filter', VALUE_OPTIONAL),
            'action' => new external_value(PARAM_RAW, 'Action will tell you what the user wants to search', VALUE_OPTIONAL),
        ]);
    }
    public static function fetch_filterdata($query=false, $action=false) {
       global $OUTPUT, $PAGE, $CFG, $DB;
       $PAGE->set_context(context_system::instance());
       $params = self::validate_parameters( self::fetch_filterdata_parameters(),[
                'query' => $query,
                'action' => $action
            ]
        );
        
        $filteroptions = (new local_exams\local\exams())->certificate_fetch_filterdata($query, $action);
        
        $return = [
            'data' => $filteroptions,
        ];
        return $return;
    }
    public static function fetch_filterdata_returns(){
        return new external_single_structure([
            'data' => new external_multiple_structure(
                new external_single_structure(
                    array(
                        'id' => new external_value(PARAM_INT, 'id', VALUE_OPTIONAL),
                        'name' => new external_value(PARAM_RAW, 'Name', VALUE_OPTIONAL)
                    )
                )
            ),
        ]);
    }
      public static function addtofavourites_parameters() {
         return new external_function_parameters(
             array(
                 'component'  => new external_value(PARAM_RAW, 'component',0),
                 'itemtype'  => new external_value(PARAM_RAW, 'itemtype',0),
                 'itemid'  => new external_value(PARAM_INT, 'itemid',0),
                 'userid'  => new external_value(PARAM_INT, 'userid',0),
                 'courseid'  => new external_value(PARAM_INT, 'courseid',0),
                 
             )
         );
     }
     public static function addtofavourites($component,$itemtype,$itemid,$userid,$courseid) {
         global $DB;
           if($component == "local_events"){
              $coursecontext = context_system::instance();
              }else{
              $coursecontext = context_course::instance($courseid);
            }    

          $usercontext = context_user::instance($userid);
          $repository=new favourite_repository();
          $favourites = new user_favourite_service($usercontext,$repository);
          $params = self::validate_parameters (
                self::addtofavourites_parameters(),array('component'=>$component,'itemtype'=>$itemtype,'itemid'=>$itemid,'userid'=>$userid,'courseid'=>$courseid)
        );

         $context = context_system::instance();
         self::validate_context($context);
         if($userid) {
           $result =  $favourites->create_favourite($component,$itemtype,$itemid,$coursecontext,$ordering = null);
         } else {
             throw new moodle_exception('Error');
         }

      }
      public static function addtofavourites_returns(){

         return $result;
      }
      public static function removefavourites_parameters() {
          return new external_function_parameters(
              array(
                    'component'  => new external_value(PARAM_RAW, 'component',0),
                 'itemtype'  => new external_value(PARAM_RAW, 'itemtype',0),
                 'itemid'  => new external_value(PARAM_INT, 'itemid',0),
                 'userid'  => new external_value(PARAM_INT, 'userid',0),
                 'courseid'  => new external_value(PARAM_INT, 'courseid',0),
              )
          );
      }
      public static function removefavourites($component,$itemtype,$itemid,$userid,$courseid) {
           global $DB;
           if($component == "local_events"){
              $coursecontext = context_system::instance();
              }else{
              $coursecontext = context_course::instance($courseid);
            } 
          $usercontext = context_user::instance($userid);
          $repository=new favourite_repository();
          $favourites = new user_favourite_service($usercontext,$repository);
          $params = self::validate_parameters (
                 self::removefavourites_parameters(),array('component'=>$component,'itemtype'=>$itemtype,'itemid'=>$itemid,'userid'=>$userid,'courseid'=>$courseid)
         );
          $context = context_system::instance();
          self::validate_context($context);
          if($userid) {
            $result =$favourites->delete_favourite($component,$itemtype,$itemid,$coursecontext,$ordering = null);
          } else {
              throw new moodle_exception('Error');
          }

       }
       public static function removefavourites_returns(){

          return $result;
       }

}
