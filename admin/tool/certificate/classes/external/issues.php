<?php
// This file is part of the tool_certificate plugin for Moodle - http://moodle.org/
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
 * Class issues
 *
 * @package     tool_certificate
 * @copyright   2018 Daniel Neis
 * @license     http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace tool_certificate\external;

defined('MOODLE_INTERNAL') || die();

require_once("$CFG->libdir/externallib.php");
use tool_certificate\customfield\issue_handler;

/**
 * Class issues
 *
 * @package     tool_certificate
 * @copyright   2018 Daniel Neis
 * @license     http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class issues extends \external_api {

    /**
     * Returns the delete_issue() parameters.
     *
     * @return \external_function_parameters
     */
    public static function revoke_issue_parameters() {
        return new \external_function_parameters(
            array(
                'id' => new \external_value(PARAM_INT, 'The issue id'),
            )
        );
    }

    /**
     * Handles deleting a certificate issue.
     *
     * @param int $issueid The issue id.
     */
    public static function revoke_issue($issueid) {
        global $DB;

        $params = self::validate_parameters(self::revoke_issue_parameters(), ['id' => $issueid]);

        $issue = $DB->get_record('tool_certificate_issues', ['id' => $params['id']], '*', MUST_EXIST);
        $template = \tool_certificate\template::instance($issue->templateid);

        // Make sure the user has the required capabilities.
        $context = \context_course::instance($issue->courseid, IGNORE_MISSING) ?: $template->get_context();
        self::validate_context($context);

        if (!$template->can_revoke($issue->userid, $context)) {
            throw new \required_capability_exception($template->get_context(), 'tool/certificate:issue', 'nopermissions', 'error');
        }

        // Delete the issue.
        $template->revoke_issue($issueid);
    }

    /**
     * Returns the revoke_issue result value.
     *
     * @return \external_value
     */
    public static function revoke_issue_returns() {
        return null;
    }

    /**
     * Returns the regenerate_issue_file() parameters.
     *
     * @return \external_function_parameters
     */
    public static function regenerate_issue_file_parameters() {
        return new \external_function_parameters(
            array(
                'id' => new \external_value(PARAM_INT, 'The issue id'),
            )
        );
    }

    /**
     * Handles regenerating a certificate issue file.
     *
     * @param int $issueid The issue id.
     */
    public static function regenerate_issue_file($issueid) {
        global $DB;

        $params = self::validate_parameters(self::regenerate_issue_file_parameters(), ['id' => $issueid]);

        $issue = $DB->get_record('tool_certificate_issues', ['id' => $params['id']], '*', MUST_EXIST);
// -----------Certificate Data update issue -------------
        // Make sure the user has the required capabilities.
        $context = \context_system::instance();
        self::validate_context($context);
        $template = \tool_certificate\template::instance($issue->templateid);
        if (!$template->can_issue($issue->userid)) {
            throw new \required_capability_exception($template->get_context(), 'tool/certificate:issue', 'nopermissions', 'error');
        }

        $reissue = new \stdClass();
        $reissue->userid = $issue->userid;
        $reissue->templateid = $issue->templateid;
        $reissue->code = $issue->code;
        $reissue->emailed = $issue->emailed;
        $reissue->timecreated = $issue->timecreated;
        $reissue->expires = $issue->expires;
        $reissue->component = $issue->component;
        $reissue->courseid = $issue->courseid;
        $reissue->moduleid = $issue->moduleid;
        $reissue->moduletype = $issue->moduletype;

        // Store user fullname.
        $data['userfullname'] = fullname($DB->get_record('user', ['id' => $issue->userid]));
        $reissue->data = json_encode($data);
        $template->revoke_issue($issue->id);
        // Insert the record into the database.
        $reissue->id = $DB->insert_record('tool_certificate_issues', $reissue);
        issue_handler::create()->save_additional_data($reissue, $data);
        // Regenerate the issue file.
        $template->create_issue_file($reissue, true);
// ------------------------

/*
        // Make sure the user has the required capabilities.
        $context = \context_system::instance();
        self::validate_context($context);
        $template = \tool_certificate\template::instance($issue->templateid);
        if (!$template->can_issue($issue->userid)) {
            throw new \required_capability_exception($template->get_context(), 'tool/certificate:issue', 'nopermissions', 'error');
        }

        // Regenerate the issue file.
        $template->create_issue_file($issue, true);
        // Update issue userfullname data.
        if ($user = $DB->get_record('user', ['id' => $issue->userid])) {
            $issuedata = @json_decode($issue->data, true);
            $issuedata['userfullname'] = fullname($user);
            $issue->data = json_encode($issuedata);
            $DB->update_record('tool_certificate_issues', $issue); 
        }*/
    }

    /**
     * Returns the regenerate_issue_file result value.
     *
     * @return \external_value
     */
    public static function regenerate_issue_file_returns() {
        return null;
    }

    /**
     * Parameters for the users selector WS.
     * @return \external_function_parameters
     */
    public static function potential_users_selector_parameters(): \external_function_parameters {
        return new \external_function_parameters([
            'search' => new \external_value(PARAM_NOTAGS, 'Search string', VALUE_REQUIRED),
            'itemid' => new \external_value(PARAM_INT, 'Item id', VALUE_OPTIONAL),
            'examid' => new \external_value(PARAM_INT, 'ID of exam', VALUE_OPTIONAL),
        ]);
    }

    /**
     * User selector.
     *
     * @param string $search
     * @param int $itemid
     * @return array
     */
    public static function potential_users_selector(string $search, int $itemid, int $examid = 0): array {
        global $DB, $CFG;

        $params = self::validate_parameters(self::potential_users_selector_parameters(),
            ['search' => $search, 'itemid' => $itemid, 'examid' => $examid]);
        $search = $params['search'];
        $itemid = $params['itemid'];
        $examid = $params['examid'];

        $context = \context_system::instance();
        self::validate_context($context);

        $template = \tool_certificate\template::instance($itemid);
        \external_api::validate_context($template->get_context());
        $join = '';
        $where = ' 1=1';
        if ($template->can_issue_to_anybody()) {
            if ($examid) {
                $join = ' JOIN {exam_enrollments} ee ON ee.userid = u.id ';
                $where .= ' AND ee.examid = '. $examid;
            }else{
                $where = \tool_certificate\certificate::get_users_subquery();
                $where .= ' AND (ci.id IS NULL OR (ci.expires > 0 AND ci.expires < :now))';
            }
        } else {
            throw new \required_capability_exception($context, 'tool/certificate:issue', 'nopermissions', 'error');
        }
        if (!$examid) {
            $join .= ' LEFT JOIN {tool_certificate_issues} ci ON u.id = ci.userid AND ci.templateid = :templateid';
        }

        $params = [];
        $params['templateid'] = $itemid;
        $params['now'] = time();

        if ($CFG->version < 2021050700) {
            // Moodle 3.9-3.10.
            $fields = get_all_user_name_fields(true, 'u');
            $extrasearchfields = [];
            if (!empty($CFG->showuseridentity) && has_capability('moodle/site:viewuseridentity', $context)) {
                $extrasearchfields = explode(',', $CFG->showuseridentity);
            }
        } else {
            // Moodle 3.11 and above.
            $fields = \core_user\fields::for_name()->get_sql('u', false, '', '', false)->selects;
            // TODO Does not support custom user profile fields (MDL-70456).
            $extrasearchfields = \core_user\fields::get_identity_fields($context, false);
        }

        if (in_array('email', $extrasearchfields)) {
            $fields .= ', u.email';
        } else {
            $fields .= ', null AS email';
        }

        list($wheresql, $whereparams) = users_search_sql($search, 'u', true, $extrasearchfields);
        $query = "SELECT u.id, $fields
            FROM {user} u $join
            WHERE ($where) AND $wheresql";
        $params += $whereparams;

        list($sortsql, $sortparams) = users_order_by_sql('u', $search, $context);
        $query .= " ORDER BY {$sortsql} LIMIT 100";
        $params += $sortparams;
        $result = $DB->get_records_sql($query, $params);
        $viewfullnames = has_capability('moodle/site:viewfullnames', $context);
        if ($result) {
            $result = array_map(function($record) use ($viewfullnames) {
                return (object)['id' => $record->id, 'fullname' => fullname($record, $viewfullnames), 'email' => $record->email];
            }, $result);
        }
        return $result;
    }

    /**
     * Return for User selector.
     * @return \external_multiple_structure
     */
    public static function potential_users_selector_returns(): \external_multiple_structure {
        global $CFG;
        require_once($CFG->dirroot . '/user/externallib.php');
        return new \external_multiple_structure(new \external_single_structure([
            'id' => new \external_value(\core_user::get_property_type('id'),
                'ID of the user'),
            'fullname' => new \external_value(\core_user::get_property_type('firstname'),
                'The fullname of the user'),
            'email' => new \external_value(\core_user::get_property_type('email'),
                'An email address', VALUE_OPTIONAL),
        ]));
    }

    public static function view_all_certificates_parameters(): \external_function_parameters {
        return new \external_function_parameters([
                'options' => new \external_value(PARAM_RAW, 'The paging data for the service'),
                'dataoptions' => new \external_value(PARAM_RAW, 'The data for the service'),
                'offset' => new \external_value(PARAM_INT, 'Number of items to skip from the begging of the result set',
                    VALUE_DEFAULT, 0),
                'limit' => new \external_value(PARAM_INT, 'Maximum number of results to return',
                    VALUE_DEFAULT, 0),
                'contextid' => new \external_value(PARAM_INT, 'contextid'),
                'filterdata' => new \external_value(PARAM_RAW, 'The data for the service'),
            ]);
    }

    /**
     * Gets the list of users based on the login user
     *
     * @param int $options need to give options targetid,viewtype,perpage,cardclass
     * @param int $dataoptions need to give data which you need to get records
     * @param int $limit Maximum number of results to return
     * @param int $offset Number of items to skip from the beginning of the result set.
     * @param int $filterdata need to pass filterdata.
     * @return array The list of users and total users count.
     */
    public static function view_all_certificates(
        $options,
        $dataoptions,
        $offset = 0,
        $limit = 0,
        $contextid,
        $filterdata
    ) {
        global $OUTPUT, $CFG, $DB,$USER,$PAGE;
        require_once($CFG->dirroot.'/'.$CFG->admin.'/tool/certificate/lib.php');
        require_login();
        $PAGE->set_url('/admin/tool/certificate/view_certificates.php', array());
        $PAGE->set_context($contextid);
        // Parameter validation.
        $params = self::validate_parameters(
            self::view_all_certificates_parameters(),
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
        $decodedata = json_decode($params['dataoptions']);
        $filtervalues = json_decode($filterdata);

        // $lib = new \tool_certificate\certificate_details();

        $stable = new \stdClass();
        $stable->thead = true;
       
        $stable->thead = false;
        $stable->start = $offset;
        $stable->length = $limit;
        $result_skill = \certificates_details($stable,$filtervalues);
        $totalcount = $result_skill['count'];
        $data = $result_skill['data'];

        return [
            'totalcount' => $totalcount,
            'records' =>$data,
            'options' => $options,
            'dataoptions' => $dataoptions,
            'filterdata' => $filterdata,
        ];

    }

    /**
     * Returns description of method result value.
     */ 
    public static function view_all_certificates_returns(): \external_single_structure {
        global $CFG;
        return new \external_single_structure([
            'options' => new \external_value(PARAM_RAW, 'The paging data for the service'),
            'dataoptions' => new \external_value(PARAM_RAW, 'The data for the service'),
            'totalcount' => new \external_value(PARAM_INT, 'total number of skills in result set'),
            'filterdata' => new \external_value(PARAM_RAW, 'The data for the service'),
            'records' => new \external_multiple_structure(
                            new \external_single_structure(
                                array(
                                    
                                    'moduletype'=>new \external_value(PARAM_RAW, 'moduletype', VALUE_OPTIONAL),
                                    'contextid'=>new \external_value(PARAM_RAW, 'context id', VALUE_OPTIONAL),
                                    'entityname' => new \external_value(PARAM_RAW, 'entityname', VALUE_OPTIONAL),
        
                                    'username' => new \external_value(PARAM_RAW, 'username', VALUE_OPTIONAL),
                                    'useremail' => new \external_value(PARAM_RAW, 'useremail', VALUE_OPTIONAL),
                                    'code' => new \external_value(PARAM_RAW, 'code', VALUE_OPTIONAL),
                                    'issueddate' => new \external_value(PARAM_RAW, 'issueddate', VALUE_OPTIONAL),
                                    'exp_date' => new \external_value(PARAM_RAW, 'exp_date', VALUE_OPTIONAL),
                                    'view_certificate' => new \external_value(PARAM_RAW, 'view_certificate', VALUE_OPTIONAL),
                                    'id' => new \external_value(PARAM_RAW, 'id', VALUE_OPTIONAL),
                                    'viewcodeurl' => new \external_value(PARAM_RAW, 'viewcodeurl', VALUE_OPTIONAL),
                                    
                                    
                                ), 'individual records', VALUE_OPTIONAL
                            ), 'records info', VALUE_OPTIONAL
                        )
        ]);
    }

}
