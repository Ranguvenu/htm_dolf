<?php
/**
 * This file is part of eAbyas
 *
 * Copyright eAbyas Info Solutons Pvt Ltd, India
 *
 * This program is free software; you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation; either version 3 of the License, or
 * (at your option) any later version.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with this program.  If not, see <http://www.gnu.org/licenses/>.
 *
 * @author eabyas  <info@eabyas.in>
 * @package local_learningtracks
 * @subpackage local_classroom
 */
defined('MOODLE_INTERNAL') || die();
global $PAGE, $OUTPUT;
require_once("$CFG->libdir/externallib.php");

use local_learningtracks\learningtracks as learningtracks;

class local_learningtracks_external extends external_api {


    public function get_learningtracks_parameters() {
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

    public function get_learningtracks($options, $dataoptions, $offset = 0, $limit = 0, $contextid, $filterdata) {
        global $DB, $PAGE;
        require_login();
        $sitecontext = context_system::instance();
        $PAGE->set_context($sitecontext);
        // Parameter validation.
        $params = self::validate_parameters(
            self::get_learningtracks_parameters(),
            [
                'options' => $options,
                'dataoptions' => $dataoptions,
                'offset' => $offset,
                'limit' => $limit,
                'contextid' => $contextid,
                'filterdata' => $filterdata,
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
        $tracks = learningtracks::get_listof_learningtracks($stable, $filtervalues); 
        $totalcount = $tracks['trackscount'];
        $data = array();
        if($totalcount>0){
            $renderer = $PAGE->get_renderer('local_learningtracks');
            $data = array_merge($data, $renderer->lis_tracks($tracks['tracks']));
        }
        return [
            'totalcount' => $totalcount,
            'length' => $totalcount,
            'filterdata' => $filterdata,
            'records' => $data,
            'options' => $options,
            'dataoptions' => $dataoptions,
        ];
    }

    public function get_learningtracks_returns() {
        return new external_single_structure([
            'options' => new external_value(PARAM_RAW, 'The paging data for the service'),
            'dataoptions' => new external_value(PARAM_RAW, 'The data for the service'),
            'totalcount' => new external_value(PARAM_INT, 'total number of competencies in result set'),
            'filterdata' => new external_value(PARAM_RAW, 'The data for the service'),
            'records' => new external_multiple_structure(
                            new external_single_structure(
                                array(
                                    'id' => new external_value(PARAM_INT, 'id'),
                                    'name' => new external_value(PARAM_RAW, 'name'),
                                    'count' => new external_value(PARAM_INT, 'count'),
                                    'status' => new external_value(PARAM_RAW, 'status'),
                                    'viewurl' => new external_value(PARAM_RAW, 'viewurl'),
                                    'action' => new external_value(PARAM_BOOL, 'action'),
                                    'nolearningitems' =>  new external_value(PARAM_INT, 'nolearningitems'),
                                    'enrollcount' => new external_value(PARAM_INT, 'enrollcount'),
                                    'edit'  => new external_value(PARAM_BOOL, 'action'),
                                    'completed_count' => new external_value(PARAM_RAW, 'completed_count'),
                                    'delete' => new external_value(PARAM_BOOL, 'delete'),
                                )
                            )
            )
        ]);
    }

    public function form_selector_parameters() {
        $query = new external_value(PARAM_RAW, 'search query');
        $type = new external_value(PARAM_RAW, 'type of the organization');
        $orgid  = new external_value(PARAM_RAW, 'type of the organization');
        $trackid  = new external_value(PARAM_RAW, 'type of the Ltrackid');
        $params = array(
            'query' => $query,
            'type' => $type,
            'orgid' => $orgid,
            'trackid' => $trackid
        );
        return new external_function_parameters($params); 
    }

    public function form_selector($query,$type,$orgid = 0, $trackid) {
        global $PAGE;
        $params = array(
            'query' => $query,
            'type' => $type,
            'orgid' => $orgid,
            'trackid' => $trackid
        );
        $params = self::validate_parameters(self::form_selector_parameters(), $params);

        switch($params['type']) {
            case 'orglist':
                $list = (new local_learningtracks\learningtracks)->get_organizationslist($params['query']);
            break;
            case 'competency':
                $list = (new local_learningtracks\learningtracks)->get_competencylist($params['query']);
            break;
            case 'examlist':
                $list = (new local_learningtracks\learningtracks)->get_examlist($params['trackid'],$params['query']);
            break;
            case 'programlist':
                $list = (new local_learningtracks\learningtracks)->get_programlistt($params['trackid'],$params['orgid'],$params['query']);
            break;
            
        }
        return ['status' => true, 'data' => $list];
    }

    public function form_selector_returns() {
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

    public function competency_list_parameters() {
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

    public function competency_list($options, $dataoptions, $offset = 0, $limit = 0, $filterdata) {
        global $OUTPUT, $CFG, $DB,$USER,$PAGE;
        $sitecontext = context_system::instance();
        $PAGE->set_context($sitecontext);
        $params = self::validate_parameters(
            self::competency_list_parameters(),
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
        $stable->thead = true;
        $competencies = (new local_learningtracks\learningtracks)->competency_list($stable,$filtervalues);
        $lang= current_language();
        if($lang == 'ar'){
            $arabic = true;
        } else {
            $arabic = false;
        }
        return [
            'totalcount' => $competencies['totalcount'],
            'records' => $competencies['acompetencies'],
            'options' => $options,
            'dataoptions' => $dataoptions,
            'filterdata' => $filterdata,
            'trackid' => $filtervalues->trackid,
            'arabic' => $arabic,
            'nodata' => get_string('nocompetencys','local_competency')
        ];
    }

    public function competency_list_returns() {
        return new external_single_structure([
            'options' => new external_value(PARAM_RAW, 'The paging data for the service'),
            'trackid' => new external_value(PARAM_INT, 'trackid'),
            'arabic' => new external_value(PARAM_BOOL, 'arabic'),
            'dataoptions' => new external_value(PARAM_RAW, 'The data for the service'),
            'totalcount' => new external_value(PARAM_INT, 'total number of competencies in result set'),
            'filterdata' => new external_value(PARAM_RAW, 'The data for the service'),
            'nodata' => new external_value(PARAM_RAW, 'The data for the service'),
            'records' => new external_multiple_structure(
                new external_single_structure(
                    array(
                        'id' => new external_value(PARAM_INT, 'competency id'),
                        'fullname' => new external_value(PARAM_TEXT, 'competency name'),
                        'code' => new external_value(PARAM_TEXT, 'competency name'),
                        'type' => new external_value(PARAM_TEXT, 'competency type'),
                        'noperformance' => new external_value(PARAM_INT, 'competency noperformance'),
                    ) 
                )
            ),
        ]);
    }
   
    public function trackview_courses_parameters() {
        return new external_function_parameters([
            'contextid' => new external_value(PARAM_INT, 'The context id', false),
            'options' => new external_value(PARAM_RAW, 'The paging data for the service'),
            'dataoptions' => new external_value(PARAM_RAW, 'The data for the service'),

            'offset' => new external_value(PARAM_INT, 'Number of items',
                VALUE_DEFAULT, 0),
            'limit' => new external_value(PARAM_INT, 'Maximum number of results to return',
                VALUE_DEFAULT, 0),
            'filterdata' => new external_value(PARAM_RAW, 'The data for the service')
        ]);
    }

    public function trackview_courses($contextid, $options,
    $dataoptions,
    $offset = 0,
    $limit = 0,
    $filterdata
    ) {
    global $DB, $PAGE;
    // Parameter validation.
    $sitecontext = context_system::instance();
   
    self::validate_context($sitecontext);
    $params = self::validate_parameters(
        self::trackview_courses_parameters(),
        [
            'contextid' => $contextid,
            'options' => $options,
            'dataoptions' => $dataoptions,
            'offset' => $offset,
            'limit' => $limit,
            'filterdata' => $filterdata,
        ]
    );
    $offset = $params['offset'];
    $limit = $params['limit'];

    $decodeddataoptions = json_decode($dataoptions);
    $stable = new \stdClass();
    $stable->search = false;
    $stable->thead = false;
    $stable->start = $offset;
    $stable->length = $limit;
    $stable->userid = $decodeddataoptions->userid;
    $renderer = $PAGE->get_renderer('local_learningtracks');
    $courses =  learningtracks::get_listof_learningitems($decodeddataoptions->trackid, $stable);
    $totalcount = $courses['learningitemscount'];
    $functinname = 'trackview_'.$decodeddataoptions->tabname;
    if(method_exists($renderer, $functinname)){
        $coursesdata = $renderer->$functinname($courses['learningitems'], $decodeddataoptions->trackid,$decodeddataoptions->userid);
        
    }
    $return = [
        'totalcount' => $totalcount,
        'records' => $coursesdata['data'],
        'options' => $options,
        'trackid' => $decodeddataoptions->trackid,
        'dataoptions' => $dataoptions,
    ];
    return $return;
    }

    public function trackview_courses_returns() {
        return new external_single_structure([
            'options' => new external_value(PARAM_RAW, 'The paging data for the service'),
            'dataoptions' => new external_value(PARAM_RAW, 'The data for the service'),
            'totalcount' => new external_value(PARAM_INT, 'totalcount'),
            'trackid' => new external_value(PARAM_INT, 'trackid'),
            'records' => new external_multiple_structure(
                            new external_single_structure(
                                array(
                                    'id' => new external_value(PARAM_INT, 'id'),
                                    'name' => new external_value(PARAM_RAW, 'name'),
                                    'description' => new external_value(PARAM_RAW, 'description'),
                                    'descriptionstring' => new external_value(PARAM_RAW, 'description'),
                                    'isdescription'  => new external_value(PARAM_BOOL, 'description'),
                                    'viewitemurl' => new external_value(PARAM_RAW, 'viewitemurl'),
                                    'imageurl'  => new external_value(PARAM_RAW, 'imageurl'),
                                    'startdate' => new external_value(PARAM_RAW, 'startdate'),
                                    'ownedby' =>  new external_value(PARAM_RAW, 'ownedby'),
                                    'viewdetails'  => new external_value(PARAM_BOOL, 'viewdetails'),
                                    'is_progressbar'  => new external_value(PARAM_BOOL, 'is_progressbar'),
                                    'progress_status' => new external_value(PARAM_RAW, 'progress_status'),
                                    'item_url' => new external_value(PARAM_RAW, 'item_url'),
                                    'type' => new external_value(PARAM_RAW, 'type'),
                                    'itemtype'  => new external_value(PARAM_INT, 'id'),
                                    'itemid'  => new external_value(PARAM_INT, 'id'),
                                    'usercount'  => new external_value(PARAM_INT, 'usercount'),
                                    'exams'  => new external_value(PARAM_BOOL, 'exams'),
                                    'trainingprograms'  => new external_value(PARAM_BOOL, 'trainingprograms'),
                                    'complete_status'  => new external_value(PARAM_BOOL, 'complete_status'),
                                    'enrolbutton' => new external_value(PARAM_RAW, 'enrolbutton'),
                                    'enddate' => new external_value(PARAM_RAW, 'enddate'),
                                    'datedisplay' => new external_value(PARAM_RAW, 'datedisplay'),
                                )
                            )
            )
        ]);
    }

    public static function get_learning_path_parameters() {
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

      public static function get_learning_path($options, $dataoptions, $offset = 0, $limit = 0, $contextid, $filterdata) {
        global $DB, $PAGE;
        // Parameter validation.
        $params = self::validate_parameters(
            self::get_learning_path_parameters(),
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
        $stable = new \stdClass();
        $stable->thead = false;
        $stable->start = $offset;
        $stable->length = $limit;
        $data =   learningtracks::get_listof_learningpath($stable, $filtervalues);
        $totalcount = $data['totallearningpath'];
        return [
            'totalcount' => $totalcount,
            'length' => $totalcount,
            'filterdata' => $filterdata,
            'records' =>$data,
            'options' => $options,
            'dataoptions' => $dataoptions,
        ];
          }

      public static function get_learning_path_returns() {
        return new external_single_structure([
            'options' => new external_value(PARAM_RAW, 'The paging data for the service'),
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
                                     'name' => new external_value(PARAM_RAW, 'name'),
                                     'description' => new external_value(PARAM_RAW, 'description'),
                                    'logo' => new external_value(PARAM_RAW, 'logo'),
                                    'course'=> new external_multiple_structure(
                                        new external_single_structure(
                                        array(
                                            'name' => new external_value(PARAM_RAW, 'name'),
                                            'description' => new external_value(PARAM_RAW, 'description'),
                                            'image'=>new external_value(PARAM_RAW, 'image'),
                                            'coursecount'=>new external_value(PARAM_RAW, 'coursecount'),
                                            'code'=>new external_value(PARAM_RAW, 'code'),
                                        ))
                                    ),
                                )
                            )
                        ),
                        'request_view' => new external_value(PARAM_INT, 'request_view', VALUE_OPTIONAL),
                        'nocourses' => new external_value(PARAM_BOOL, 'nocourses', VALUE_OPTIONAL),
                        'totallearningpath' => new external_value(PARAM_INT, 'totalposts', VALUE_OPTIONAL),
                        'length' => new external_value(PARAM_INT, 'length', VALUE_OPTIONAL),
                    )
                )

        ]);
    }

 public function trackview_users_parameters() {
    return new external_function_parameters([
        'contextid' => new external_value(PARAM_INT, 'The context id', false),
        'options' => new external_value(PARAM_RAW, 'The paging data for the service'),
        'dataoptions' => new external_value(PARAM_RAW, 'The data for the service'),

        'offset' => new external_value(PARAM_INT, 'Number of items',
            VALUE_DEFAULT, 0),
        'limit' => new external_value(PARAM_INT, 'Maximum number of results to return',
            VALUE_DEFAULT, 0),
        'filterdata' => new external_value(PARAM_RAW, 'The data for the service')
    ]);
 }

 public function trackview_users($contextid, $options, $dataoptions, $offset = 0, $limit = 0, $filterdata) {
    global $DB, $PAGE;
    // Parameter validation.
    $sitecontext = context_system::instance();
   
    self::validate_context($sitecontext);
    $params = self::validate_parameters(
        self::trackview_courses_parameters(),
        [
            'contextid' => $contextid,
            'options' => $options,
            'dataoptions' => $dataoptions,
            'offset' => $offset,
            'limit' => $limit,
            'filterdata' => $filterdata,
        ]
    );
    $offset = $params['offset'];
    $limit = $params['limit'];
    $decodeddataoptions = json_decode($dataoptions);
    $stable = new \stdClass();
    $stable->search = false;
    $stable->thead = false;
    $stable->start = $offset;
    $stable->length = $limit;
    $renderer = $PAGE->get_renderer('local_learningtracks');
    $users =  learningtracks::get_listof_users($decodeddataoptions->trackid, $stable);
    $totalcount = $users['userscount'];
    $functinname = 'trackview_'.$decodeddataoptions->tabname;
    if(method_exists($renderer, $functinname)){
        $usersdata = $renderer->$functinname($users['users'], $decodeddataoptions->trackid);
    }
    $return = [
        'totalcount' => $totalcount,
        'records' => $usersdata['data'],
        'options' => $options,
        'trackid' => $decodeddataoptions->trackid,
        'dataoptions' => $dataoptions,
    ];
    return $return;
 }

 public function trackview_users_returns() {
    return new external_single_structure([
        'options' => new external_value(PARAM_RAW, 'The paging data for the service'),
        'dataoptions' => new external_value(PARAM_RAW, 'The data for the service'),
        'totalcount' => new external_value(PARAM_INT, 'totalcount'),
        'trackid' => new external_value(PARAM_INT, 'trackid'),
        'records' => new external_multiple_structure(
                        new external_single_structure(
                            array(
                                'id' => new external_value(PARAM_INT, 'id'),
                                'username' => new external_value(PARAM_RAW, 'name'),
                                'email'  => new external_value(PARAM_RAW, 'email'),
                                'enrollcount' => new external_value(PARAM_INT, 'enrollcount'),
                                'viewurl' => new external_value(PARAM_RAW, 'viewurl'),
                                'completed_count' => new external_value(PARAM_RAW, 'completed_count'),
                                'remaining_count'  => new external_value(PARAM_RAW, 'remaining_count'),
                                'completedstatus' => new external_value(PARAM_BOOL, 'completedstatus'),
                                'totalcourses' =>  new external_value(PARAM_INT, 'totalcourses'),
                            )
                        )
        )
    ]);
 }

 public function viewlearningpath_parameters() {
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

public function viewlearningpath($options, $dataoptions, $offset = 0, $limit = 0, $contextid, $filterdata) {
    global $DB, $PAGE;
    $sitecontext = context_system::instance();
    // Parameter validation.
    $PAGE->set_context($sitecontext);
    $params = self::validate_parameters(
        self::viewlearningpath_parameters(),
        [
            'options' => $options,
            'dataoptions' => $dataoptions,
            'offset' => $offset,
            'limit' => $limit,
            'contextid' => $contextid,
            'filterdata' => $filterdata,
        ]
    );
    $data_object = (json_decode($dataoptions));
    $offset = $params['offset'];
    $limit = $params['limit'];
    $filtervalues = json_decode($filterdata);

    $stable = new \stdClass();
    $stable->thead = true;
    $tracks = learningtracks::get_listof_learningtracks($stable, $filtervalues); 
    // var_dump($tracks); exit;
    $totalcount = $tracks['trackscount'];
    $stable->thead = false;
    $stable->start = $offset;
    $stable->length = $limit;
    $data = array();
    if($totalcount>0){
        $renderer = $PAGE->get_renderer('local_learningtracks');
        $data = array_merge($data, $renderer->learningpath_list($stable,$filtervalues));
    }
    if($totalcount > 5){
        $noloadmore = true;
    } else {
        $noloadmore = false;
    }
    return [
        'totalcount' => $totalcount,
        'length' => $totalcount,
        'filterdata' => $filterdata,
        'records' => $data,
        'options' => $options,
        'dataoptions' => $dataoptions,
        'noloadmore' => $noloadmore,
        'numberofprograms' => $totalcount,
    ];
}

public function viewlearningpath_returns() {
    return new external_single_structure([
        'options' => new external_value(PARAM_RAW, 'The paging data for the service'),
        'dataoptions' => new external_value(PARAM_RAW, 'The data for the service'),
        'totalcount' => new external_value(PARAM_INT, 'total number of competencies in result set'),
        'filterdata' => new external_value(PARAM_RAW, 'The data for the service'),
        'length' => new external_value(PARAM_RAW, 'total number of challenges in result set'),
        'records' => new external_multiple_structure(
                        new external_single_structure(
                            array(
                                'id' => new external_value(PARAM_INT, 'id'),
                                'name' => new external_value(PARAM_RAW, 'name'),
                                'count' => new external_value(PARAM_INT, 'count'),
                                'status' => new external_value(PARAM_RAW, 'status'),
                                'nolearningitems' =>  new external_value(PARAM_INT, 'nolearningitems'),
                                'enrollcount' => new external_value(PARAM_INT, 'enrollcount'),
                                'description' => new external_value(PARAM_RAW, 'description'),
                                'isdescription' => new external_value(PARAM_BOOL, 'isdescription'),
                                'descriptionstring' => new external_value(PARAM_RAW, 'descriptionstring'),
                                'trackimg' => new external_value(PARAM_RAW, 'trackimg'),
                                'courses_count'  =>  new external_value(PARAM_INT, 'courses_count'),
                                'exams_count'  =>  new external_value(PARAM_INT, 'exams_count'),
                            )
                        )
        ),
        'request_view' => new external_value(PARAM_INT, 'request_view', VALUE_OPTIONAL),
        'noloadmore' => new external_value(PARAM_BOOL, 'noloadmore', VALUE_OPTIONAL),
        'numberofprograms' => new external_value(PARAM_INT, 'numberofprograms', VALUE_OPTIONAL),
    ]);
}

public function enrolledlearningpath_parameters() {
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

public function enrolledlearningpath($options, $dataoptions, $offset = 0, $limit = 0, $contextid, $filterdata) {
    global $DB, $PAGE;
    $sitecontext = context_system::instance();
    $PAGE->set_url('/local/learningtracks/learningpath.php', array());
    $PAGE->set_context($sitecontext);
    // Parameter validation.
    $params = self::validate_parameters(
        self::viewlearningpath_parameters(),
        [
            'options' => $options,
            'dataoptions' => $dataoptions,
            'offset' => $offset,
            'limit' => $limit,
            'contextid' => $contextid,
            'filterdata' => $filterdata,
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
    $tracks = learningtracks::get_enrolled_learningpath($stable, $filtervalues); 
    $totalcount = $tracks['trackscount'];
    $data = array();
    if($totalcount > 0){
        $renderer = $PAGE->get_renderer('local_learningtracks');
        $data = array_merge($data, $renderer->my_learning_tracks($tracks['tracks']));
    }
    return [
        'totalcount' => $totalcount,
        'length' => $totalcount,
        'filterdata' => $filterdata,
        'records' => $data,
        'options' => $options,
        'dataoptions' => $dataoptions,
    ];

}

public function enrolledlearningpath_returns() {
    return new external_single_structure([
        'options' => new external_value(PARAM_RAW, 'The paging data for the service'),
        'dataoptions' => new external_value(PARAM_RAW, 'The data for the service'),
        'totalcount' => new external_value(PARAM_INT, 'total number of competencies in result set'),
        'filterdata' => new external_value(PARAM_RAW, 'The data for the service'),
        'records' => new external_multiple_structure(
                        new external_single_structure(
                            array(
                                'id' => new external_value(PARAM_INT, 'id'),
                                'name' => new external_value(PARAM_RAW, 'name'),
                                'count' => new external_value(PARAM_INT, 'count'),
                                'status' => new external_value(PARAM_RAW, 'status'),
                                'nolearningitems' =>  new external_value(PARAM_INT, 'nolearningitems'),
                                'enrollcount' => new external_value(PARAM_INT, 'enrollcount'),
                                'viewurl' => new external_value(PARAM_RAW, 'viewurl'),
                                'completed_count' => new external_value(PARAM_RAW, 'completed_count'),
                                'remaining_count'  => new external_value(PARAM_RAW, 'remaining_count'),
                                'completedstatus' => new external_value(PARAM_BOOL, 'completedstatus'),
                                'totalcourses' =>  new external_value(PARAM_INT, 'totalcourses'),
                                'certid'  => new external_value(PARAM_RAW, 'certid'),
                            )
                        )
        )
    ]);
}

public function delete_learningtrack_parameters(){
    return new external_function_parameters(
        array(
            'trackid' => new external_value(PARAM_INT, 'ID of the record', 0),
            'confirm' => new external_value(PARAM_BOOL, 'Confirm', false),
        )
    );
}

public static function delete_learningtrack($trackid, $confirm) {
    global $DB,$CFG,$USER;
    $systemcontext = context_system::instance();
    try {
        if ($confirm) {
            // notification learningtrack cancel
            $sql="SELECT u.* FROM {user} u
				 JOIN {local_lts_enrolment} le ON le.userid = u.id
                 WHERE le.trackid = $trackid AND u.confirmed = 1 AND u.suspended = 0 AND u.deleted = 0 AND u.id > 2";
            $touser = $DB->get_records_sql($sql);
            $trackinfo = $DB->get_record('local_learningtracks', array('id' => $trackid));


            $row1=[];
            $row1['RelatedModuleName']=$trackinfo->name;
            $myobject=(new \local_learningtracks\notification);
            $myobject->learningtracks_notification('learningtrack_cancel',$touser, get_admin(),$row1,$waitinglistid=0);

            $DB->delete_records('local_learningtracks', array('id' => $trackid));

            $DB->delete_records('local_learning_items', array('trackid' => $trackid));

            $DB->delete_records('local_lts_enrolment', array('trackid' => $trackid));

            $DB->delete_records('local_lts_item_enrolment', array('trackid' => $trackid));


            $event = \local_learningtracks\event\learning_track_deleted::create(array('context' => $systemcontext, 'objectid' => $trackid));// ... code that may add some record snapshots
            $event->trigger();
            return true;
        } else {
            $return = false;
        }
    } catch (dml_exception $ex) {
        print_error('deleteerror', 'local_learningtracks');
        $return = false;
    }
    return $return;
}

public static function delete_learningtrack_returns() {
    return new external_value(PARAM_BOOL, 'return');
}

public function learningtrack_enrollment_parameters() {
    return new external_function_parameters(
        array(
            'type' => new external_value(PARAM_RAW, 'Type of the record', 0),
            'trackid' => new external_value(PARAM_INT, 'ID of the record', 0),
            'query' => new external_value(PARAM_RAW, 'query', 0),
        )
    );
}

public function learningtrack_enrollment($type, $trackid, $query) {
    global $DB, $PAGE;
    $sitecontext = context_system::instance();
    $PAGE->set_context($sitecontext);
    // Parameter validation.
    $params = self::validate_parameters(
        self::learningtrack_enrollment_parameters(),
        [
            'type' => $type,
            'trackid' => $trackid,
            'query' => $query,
        ]
    );
    
    $query = $params['query'];
    $tracks = learningtracks::learningtrack_enrolled_users($trackid,$params);

    if($params['type']=='remove') {
        $options = $tracks['enrolledusers'];
        $totalcount = $tracks['availableuserscount'];
    }else if($params['type']=='add') {
        $options = $tracks['availableusers'];
        $totalcount = $tracks['enrolleduserscount'];
    }
    return['options' => json_encode($options),
            'count' => $totalcount];
}

public function learningtrack_enrollment_returns() {
    return new external_single_structure([
        'options' => new external_value(PARAM_RAW, 'The paging data for the service'),
        'count' => new external_value(PARAM_RAW, 'The paging data for the service'),
    ]);
}
public function deleteitems_parameters(){
    return new external_function_parameters(
        array(
            'trackid' => new external_value(PARAM_INT, 'ID of the record', 0),
            'confirm' => new external_value(PARAM_BOOL, 'Confirm', false),
        )
    );
}

public static function deleteitems($trackid, $confirm) {
    global $DB,$CFG,$USER;
    $systemcontext = context_system::instance();
    try {
        if ($confirm) {

            $learning_item = $DB->get_record_sql("SELECT * FROM {local_learning_items} WHERE id = $trackid");

            $getdata = $DB->get_records_sql("SELECT * FROM {local_lts_item_enrolment} WHERE trackid = $learning_item->trackid");

            if($learning_item) {
           
                    $DB->delete_records('local_learning_items', array('id' => $learning_item->id));

                    $DB->delete_records('local_lts_item_enrolment', array('trackid' => $learning_item->trackid,'itemid' => $learning_item->itemid,'itemtype' => $learning_item->itemtype));

                    $countitem_enrolment=$DB->count_records('local_lts_item_enrolment', array('trackid' => $learning_item->trackid,'itemid' => $learning_item->itemid,'itemtype' => $learning_item->itemtype));


                    if($countitem_enrolment == 0 || !$countitem_enrolment){
                       
                        foreach($getdata as $value)
                        {
                            $countusers=$DB->count_records('local_lts_item_enrolment',array('userid'=>$value->userid,'trackid'=>$value->trackid));
                            if($countusers==0)
                            {
                                $DB->delete_records('local_lts_enrolment', array('trackid'=>$value->trackid,'userid' => $value->userid));
                            }
                        }

                        
                    }
                   
            }
                                
            return true;
        } else {
            $return = false;
        }
    } catch (dml_exception $ex) {
        print_error('deleteerror', 'local_learningtracks');
        $return = false;
    }
    return $return;
}

public static function deleteitems_returns() {
    return new external_value(PARAM_BOOL, 'return');
}


    public static function get_learningtracksinfo_parameters(){
        return new external_function_parameters(
            array(
                'trackid' => new external_value(PARAM_INT, 'ID of the record'),
                'isArabic' => new external_value(PARAM_RAW, 'isArabic', false),
            )
        );
    }

    public static function get_learningtracksinfo($trackid, $isArabic) {
        global $DB;
        // Parameter validation.
        $params = self::validate_parameters(
            self::get_learningtracksinfo_parameters(),
            [
                'trackid' => $trackid,
                'isArabic' => $isArabic,
            ]
        );
        $learningtrack = learningtracks::learningtracksinfo($trackid, $isArabic);

        return $learningtrack;
    }

    public static function get_learningtracksinfo_returns() {
        return new external_single_structure([
            'id' => new external_value(PARAM_INT, 'id'),
            'titleAr' => new external_value(PARAM_RAW, 'titleAr'),
            'titleEn' => new external_value(PARAM_RAW, 'titleEn'),
            'code' => new external_value(PARAM_RAW, 'code'),
            'expirationTimeInYears' => new external_value(PARAM_RAW, 'expirationTimeInYears'),
            'name' => new external_value(PARAM_RAW, 'name'),
            'certificatesExams' => new external_multiple_structure(
                new external_single_structure(
                    array(
                        'name' => new external_value(PARAM_RAW, 'name', VALUE_OPTIONAL),
                        'description' => new external_value(PARAM_RAW, 'description', VALUE_OPTIONAL),
                        'value' => new external_value(PARAM_INT, 'value', VALUE_OPTIONAL),
                    )
                )
            ),
            'certificatesTrainingCourses' => new external_multiple_structure(
                new external_single_structure(
                    array(
                        'name' => new external_value(PARAM_RAW, 'name', VALUE_OPTIONAL),
                        'description' => new external_value(PARAM_RAW, 'description', VALUE_OPTIONAL),
                        'value' => new external_value(PARAM_INT, 'value', VALUE_OPTIONAL),
                        'attachmentId' => new external_value(PARAM_INT, 'value', VALUE_OPTIONAL),
                    )
                )
            ),
        ]);
    }

    public static function getalllearningtracks_parameters(){
        return new external_function_parameters(
            array(
                'isArabic' => new external_value(PARAM_RAW, 'Language in use',VALUE_DEFAULT,false),
                'pageNumber' => new external_value(PARAM_INT, 'Page Number',VALUE_DEFAULT,1),
                'pageSize' => new external_value(PARAM_INT, 'Page Size',VALUE_DEFAULT,5),
            )
        );
    }
    public static function getalllearningtracks($isArabic,$pageNumber,$pageSize) {
        global $DB, $PAGE, $CFG;
        $context = context_system::instance();   
        $params = self::validate_parameters(
            self::getalllearningtracks_parameters(),
            [
                'isArabic' => $isArabic,
                'pageNumber' => $pageNumber,
                'pageSize' => $pageSize,
            ]
        );
        $stable = new \stdClass();
        $stable->thead = false;
        $stable->start = ($pageNumber > 1)? $pageSize : $offset;
        $stable->length = $pageSize;
        $stable->isArabic = $isArabic;
        $data = learningtracks::get_alllearningtracks($stable);

        return [
            'pageData' =>$data['learningtracks'],
            'totalItemCount'=>$data['totallearningtracks'],
            'pageSize'=>$pageSize,
            'pageNumber'=>$pageNumber
       ];
    
    }
    public static function getalllearningtracks_returns() {
        return new external_single_structure([
          'pageData' => new external_multiple_structure(
               new external_single_structure(
                   array(
                        'id' => new external_value(PARAM_INT, 'id'),
                        'Name' => new external_value(PARAM_TEXT, 'Name'),
                        'Logo' => new external_value(PARAM_RAW, ' Logo'),
                        'Description' => new external_value(PARAM_RAW, 'description'),
                        'LearningItems' => new external_value(PARAM_INT, 'LearningItems'),
                        'detailsPageURL' => new external_value(PARAM_RAW, 'detailsPageURL'),
                    )
                )
            ),
          'totalItemCount' => new external_value(PARAM_INT, 'totalItemCount'),
          'pageSize' => new external_value(PARAM_INT, 'pageSize'),
          'pageNumber' => new external_value(PARAM_INT, 'pageNumber'),
        ]);
    }
}
