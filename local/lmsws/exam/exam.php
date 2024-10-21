<?php
require_once(dirname(__FILE__) . '/../lms_webservice.php');
require_once($CFG->dirroot . '/local/trainingprogram/lib.php');
require_once($CFG->dirroot .'/course/lib.php');
require_once($CFG->libdir . '/completionlib.php');
require_once($CFG->dirroot .'/mod/attendance/externallib.php');
require_once($CFG->dirroot . '/mod/zoom/lib.php');
require_once($CFG->dirroot.'/mod/zoom/locallib.php');
require_once($CFG->dirroot.'/user/lib.php');
require_once($CFG->dirroot.'/group/lib.php');
require_once($CFG->dirroot.'/lib/moodlelib.php');
require_once($CFG->dirroot.'/admin/tool/certificate/classes/template.php');
use stdClass;
use dml_exception;
use html_writer;
use moodle_url;
use context_system;
use tabobject;
use user_create_user;
use context_user;
use core_user;
use filters_form;
use mod_attendance_external;
use local_trainingprogram\local\createoffering;
use local_trainingprogram\local\trainingprogram as tp;
use single_button;
require_once("$CFG->libdir/externallib.php");
// require_once($CFG->dirroot . '/local/trainingprogram/filters_form.php');
class lms_webservice_exam extends lms_webservice
{

    /**
     * Returns description of method parameters
     *
     * @return external_function_parameters
     * @since Moodle 2.2
     */

    public function ValidateTestCenterUser_parameters()
    {
        return new external_function_parameters([
            'userName' => new external_value(PARAM_RAW, 'userName of exam official'),
            'password' => new external_value(PARAM_RAW, 'password of exam official'),
            'testCenterId' => new external_value(PARAM_RAW, 'testCenterId exam center code'),
            'cultureName' => new external_value(PARAM_RAW, 'cultureName default value')
        ]);
    }

    /**
     * Return the list of page question attempts in a given lesson.
     *
     * @param int $lessonid lesson instance id
     * @param int $attempt the lesson attempt number
     * @param bool $correct only fetch correct attempts
     * @param int $pageid only fetch attempts at the given page
     * @param int $userid only fetch attempts of the given user
     * @return array of warnings and page attempts
     * @since Moodle 3.3
     * @throws moodle_exception
     */
    public static function ValidateTestCenterUser($userName = false, $password = false, $testCenterId = false, $cultureName = false)
    {
        global $OUTPUT, $CFG, $DB, $USER, $PAGE;

        $params = self::validate_parameters(
            self::ValidateTestCenterUser_parameters(),
            [
                'userName' => $userName,
                'password' => $password,
                'testCenterId' => $testCenterId,
                'cultureName' => $cultureName,
            ]
        );

        $userName = $params['userName'];
        $password = $params['password'];
        $testCenterId = $params['testCenterId'];
        $cultureName = $params['cultureName'];

        $testcenteruser = (new \local_lmsws\lib)->centerinfo($testCenterId, $userName);


        return [
            'confirm' => false,
            'message' => '',
            'value' => $testcenteruser['records'],
            'modelStateErrors' => '[]',
            'success' => true,
        ];

    }

    public function ValidateTestCenterUser_returns()
    {
        return new external_single_structure([
            'confirm' => new external_value(PARAM_RAW, 'The paging data for the service'),
            'message' => new external_value(PARAM_RAW, 'Exam id'),
            'modelStateErrors' => new external_value(PARAM_RAW, 'The data for the service'),
            'success' => new external_value(PARAM_RAW, 'total number of competencies in result set'),
            'value' => new external_multiple_structure(
                new external_single_structure(
                    array(
                        'accessFailedCount' => new external_value(PARAM_RAW, 'accessFailedCount default value 0'),
                        'concurrencyStamp' => new external_value(PARAM_RAW, 'concurrencyStamp default value 20d69690-6af9-46b6-be1e-f901090c5e48'),
                        'dateOfBirth' => new external_value(PARAM_RAW, 'dateOfBirth of examiner'),
                        'email' => new external_value(PARAM_RAW, 'examiner user eamil id'),
                        'emailConfirmed' => new external_value(PARAM_RAW, 'emailConfirmed default value true '),
                        'firstName' => new external_value(PARAM_RAW, 'exam centre name'),
                        'firstNameAr' => new external_value(PARAM_RAW, 'fullNameEn exam Center name exam Center name arabic'),
                        'fullName' => new external_value(PARAM_RAW, 'fullName exam Center name exam Center name arabic'),
                        'fullNameAr' => new external_value(PARAM_RAW, 'fullNameAr exam Center name exam Center name arabic'),
                        'fullNameEn' => new external_value(PARAM_RAW, 'fullNameEn exam Center name exam center name english'),
                        'isActive' => new external_value(PARAM_RAW, 'isActive default value true'),
                        'isApproved' => new external_value(PARAM_RAW, 'isApproved default value null'),
                        'isEmployee' => new external_value(PARAM_RAW, 'isEmployee default value false'),
                        'jobTitle' => new external_value(PARAM_RAW, 'jobTitle default value null'),
                        'lastName' => new external_value(PARAM_RAW, 'lastNameEn default value الرياض'),
                        'lastNameAr' => new external_value(PARAM_RAW, 'lastNameEn default value الرياض'),
                        'lastNameEn' => new external_value(PARAM_RAW, 'lastNameEn default value Center'),
                        'lockoutEnabled' => new external_value(PARAM_RAW, 'lockoutEnd default value true'),
                        'lockoutEnd' => new external_value(PARAM_RAW, 'lockoutEnd default value 2022-10-25T13:55:45.367154+00:00'),
                        'lockoutEndDateUtc' => new external_value(PARAM_RAW, 'lockoutEndDateUtc default value null'),
                        'middleName' => new external_value(PARAM_RAW, 'middleName default value اختبار'),
                        'middleNameAr' => new external_value(PARAM_RAW, 'middleNameAr default value اختبار'),
                        'middleNameEn' => new external_value(PARAM_RAW, 'middleNameEn default value exam'),
                        'mobileNotificationToken' => new external_value(PARAM_RAW, 'mobileNotificationToken default value null'),
                        'normalizedEmail' => new external_value(PARAM_RAW, 'normalizedEmail is user email'),
                        'normalizedUserName' => new external_value(PARAM_RAW, 'normalizedUserName is username  field'),
                        'password' => new external_value(PARAM_RAW, 'password default value null'),
                        'passwordHash' => new external_value(PARAM_RAW, 'passwordHash default value AQAAAAEAACcQAAAAEF+6rNGS6nl70TwptBJ9tHEzJcJHG3MUPezgWDNTMPn3Kb6UUJrUd981ODmoIF7Spg=='),
                        'phoneNumber' => new external_value(PARAM_RAW, 'phoneNumber default value null'),
                        'phoneNumberConfirmed' => new external_value(PARAM_RAW, 'phoneNumberConfirmed default value false'),
                        'preferredCommunicationLanguage' => new external_value(PARAM_RAW, 'preferredCommunicationLanguage user Preferred language language'),
                        'preferredUiLanguage' => new external_value(PARAM_RAW, 'preferredUiLanguage user Preferred  language'),
                        'securityStamp' => new external_value(PARAM_RAW, 'securityStamp default value BDTBOHQYY7TKLUY2RWMAV7NLU6TJ37IR'),
                        'thirdName' => new external_value(PARAM_RAW, 'thirdName default value null'),
                        'thirdNameAr' => new external_value(PARAM_RAW, 'thirdNameAr default value null'),
                        'thirdNameEn' => new external_value(PARAM_RAW, 'thirdNameEn default value null'),
                        'twoFactorEnabled' => new external_value(PARAM_RAW, 'twoFactorEnabled default value false'),
                        'userName' => new external_value(PARAM_RAW, 'userName'),
                        'deactivationReason' => new external_value(PARAM_RAW, 'deactivationReason default value null'),
                        'userIDPortal' => new external_value(PARAM_RAW, 'userIDPortal default value null'),
                        'isSSOUpdated' => new external_value(PARAM_RAW, 'isSSOUpdated default value null'),
                        'id' => new external_value(PARAM_INT, 'user_id'),
                    )
                )
            ),
        ]);
    }

    public function GetTestCenterTodayPeriods_parameters()
    {
        return new external_function_parameters([
            'testCenterId' => new external_value(PARAM_RAW, 'testCenterId of FA exam ID')
        ]);
    }

    /**
     * Return the list of page question attempts in a given lesson.
     *
     * @param int $lessonid lesson instance id
     * @param int $attempt the lesson attempt number
     * @param bool $correct only fetch correct attempts
     * @param int $pageid only fetch attempts at the given page
     * @param int $userid only fetch attempts of the given user
     * @return array of warnings and page attempts
     * @since Moodle 3.3
     * @throws moodle_exception
     */
    public static function GetTestCenterTodayPeriods($testCenterId)
    {
        global $OUTPUT, $CFG, $DB, $USER, $PAGE;

        $params = self::validate_parameters(
            self::GetTestCenterTodayPeriods_parameters(),
            [
                'testCenterId' => $testCenterId
            ]
        );
        $testCenterId = $params['testCenterId'];
        $testcenteruser = (new \local_lmsws\lib)->gettestcentertodayperiods($testCenterId);


        //  print_r($testcenteruser);
        //  echo $testcenteruser['totalhalls'];

        //print_r($testcenteruser);

        return [
            'confirm' => false,
            'message' => null,
            'modelStateErrors' => "[]",
            'value' =>$testcenteruser,
            'success' => true,
        ];

        // return $testcenteruser;

    }

    public function GetTestCenterTodayPeriods_returns()
    {
        return new external_single_structure([
            'confirm' => new external_value(PARAM_RAW, 'The paging data for the service'),
            'message' => new external_value(PARAM_RAW, 'url'),
            'modelStateErrors' => new external_value(PARAM_RAW, 'total number of challenges in result set'),
            'success' => new external_value(PARAM_RAW, 'The data for the service'),
            'value' => new external_single_structure(
                array(
                    'testCenterId' => new external_value(PARAM_RAW, 'request_view', VALUE_OPTIONAL),
                    'periodExaminees' => new external_multiple_structure(
                        new external_single_structure(
                            array(
                                'dayPeriodId' => new external_value(PARAM_RAW, 'id'),
                                'periodFromTo' => new external_value(PARAM_RAW, 'itemname'),
                                'confirmedExamineesCount' => new external_value(PARAM_RAW, 'itemname'),
                                'cancelledExamineesCount' => new external_value(PARAM_RAW, 'type'),
                            )
                        )
                    ),
                   /* 'request_view' => new external_value(PARAM_INT, 'request_view', VALUE_OPTIONAL),
                    'norecords' => new external_value(PARAM_BOOL, 'norecords', VALUE_OPTIONAL),
                    'totalcount' => new external_value(PARAM_INT, 'totalcount', VALUE_OPTIONAL),
                    'length' => new external_value(PARAM_INT, 'length', VALUE_OPTIONAL),*/
                )
            )
        ]);

    }

    public function GenerateDayPeriodExams_parameters()
    {
        return new external_function_parameters([
            'testCenterId' => new external_value(PARAM_RAW, 'testCenterId of FA exam ID'),
            'dayPeriodId' => new external_value(PARAM_RAW, 'dayPeriodId of FA exam ID'),
            'isForcedRegeneration' => new external_value(PARAM_RAW, 'isForcedRegeneration of FA exam ID')            
        ]);
    }

    /**
     * Return the list of page question attempts in a given lesson.
     *
     * @param int $lessonid lesson instance id
     * @param int $attempt the lesson attempt number
     * @param bool $correct only fetch correct attempts
     * @param int $pageid only fetch attempts at the given page
     * @param int $userid only fetch attempts of the given user
     * @return array of warnings and page attempts
     * @since Moodle 3.3
     * @throws moodle_exception
     */
    public static function GenerateDayPeriodExams($testCenterId,$dayPeriodId,$isForcedRegeneration)
    {
        global $OUTPUT, $CFG, $DB, $USER, $PAGE;


        $params = self::validate_parameters(
            self::GenerateDayPeriodExams_parameters(),
            [
                'testCenterId' => $testCenterId,
                'dayPeriodId' => $dayPeriodId,
                'isForcedRegeneration' => $isForcedRegeneration,

            ]
        );
       echo $testCenterId = $params['testCenterId'];
       echo $dayPeriodId = $params['dayPeriodId'];
      echo  $isForcedRegeneration = $params['isForcedRegeneration'];

        $testcenteruser = (new \local_lmsws\lib)->gettestcentertoenrol($testCenterId,$dayPeriodId,$isForcedRegeneration);


        //  print_r($testcenteruser);
        //  echo $testcenteruser['totalhalls'];

        print_r($testcenteruser);
        die;

        return [
            'confirm' => false,
            'message' => null,
            'modelStateErrors' => "[]",
            'value' =>$testcenteruser,
            'success' => true,
        ];

        // return $testcenteruser;

    }

    public function GenerateDayPeriodExams_returns()
    {
        return new external_single_structure([
            'confirm' => new external_value(PARAM_RAW, 'The paging data for the service'),
            'message' => new external_value(PARAM_RAW, 'url'),
            'modelStateErrors' => new external_value(PARAM_RAW, 'total number of challenges in result set'),
            'success' => new external_value(PARAM_RAW, 'The data for the service'),
            'value' => new external_single_structure(
                array(
                    'attemptsCount' => new external_value(PARAM_RAW, 'request_view', VALUE_OPTIONAL),
                    'examineesIdentification' => new external_multiple_structure(
                        new external_single_structure(
                            array(
                                'examineesIdentification' => new external_value(PARAM_RAW, 'id'),
                               
                            )
                        )
                    ),
                   /* 'request_view' => new external_value(PARAM_INT, 'request_view', VALUE_OPTIONAL),
                    'norecords' => new external_value(PARAM_BOOL, 'norecords', VALUE_OPTIONAL),
                    'totalcount' => new external_value(PARAM_INT, 'totalcount', VALUE_OPTIONAL),
                    'length' => new external_value(PARAM_INT, 'length', VALUE_OPTIONAL),*/
                )
            )
        ]);

    }
}
