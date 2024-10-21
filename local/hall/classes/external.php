<?php
defined('MOODLE_INTERNAL') || die;
require_once("$CFG->libdir/externallib.php");
require_once($CFG->dirroot.'/local/notifications/notification.php');

use local_hall;

class local_hall_external extends external_api {
    public static function delete_hall_parameters(){
        return new external_function_parameters(
            array(
                'hallid' => new external_value(PARAM_INT, 'hallid', 0)
            )
        );
    }
  
    public static function delete_hall($hallid){
        global $DB;
        $params = self::validate_parameters(self::delete_hall_parameters(),
                                    ['hallid' => $hallid]);
        if($hallid){
           $hallid = $DB->delete_records('hall', array('id' => $hallid));
            return true;
        }else {
            throw new moodle_exception('Error in deleting');
            return false;
        }
    }
   
    public static function delete_hall_returns() {
        return new external_value(PARAM_BOOL, 'return');
    }    
    
    public static function halls_view_parameters() {
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

    /**
     * lists all halls
     *
     * @param array $options
     * @param array $dataoptions
     * @param int $offset
     * @param int $limit
     * @param int $contextid
     * @param array $filterdata
     * @return array courses list.
     */
    public static function halls_view($options=false, $dataoptions=false, $offset = 0, $limit = 0, $contextid=1, $filterdata=false) {
        global $DB, $PAGE;
        require_login();
        // Parameter validation.
        $params = self::validate_parameters(
            self::halls_view_parameters(),
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
        $data = (new local_hall\hall)->get_listof_halls($stable, $filtervalues);
        $totalcount = $data['totalhalls'];

        return [
            'totalcount' => $totalcount,
            'length' => $totalcount,
            'filterdata' => $filterdata,
            'records' =>$data,
            'options' => $options,
            'dataoptions' => $dataoptions,
        ];
    }

    /**
     * Returns description of method result value
     * @return external_description
     */

    public static function halls_view_returns() {
        return new external_single_structure([
            'totalcount' => new external_value(PARAM_INT, 'total number of challenges in result set'),
            'length' => new external_value(PARAM_RAW, 'total number of challenges in result set'),
            'records' => new external_single_structure(
                    array(
                        'hascourses' => new external_multiple_structure(
                            new external_single_structure(
                                array(
                                    'slno' => new external_value(PARAM_INT, 'slno'),
                                    'id' => new external_value(PARAM_INT, 'id'),
                                    'name' => new external_value(PARAM_RAW, 'name'),
                                    'hallcode' => new external_value(PARAM_RAW, 'hallcode'),
                                    'city' => new external_value(PARAM_RAW, 'city'),
                                    'type' => new external_value(PARAM_RAW, 'type'),
                                    'buildingname' => new external_value(PARAM_RAW, 'buildingname'),
                                    'seatingcapacity' => new external_value(PARAM_INT, 'seatingcapacity'),
                                    'maplocation' => new external_value(PARAM_RAW, 'maplocation'),
                                    'roomshape' => new external_value(PARAM_RAW, 'roomshape'),
                                    'availability' => new external_value(PARAM_RAW, 'availability'),
                                    'equipmentavailable' => new external_value(PARAM_RAW, 'equipmentavailable'),
                                    'description' => new external_value(PARAM_RAW, 'description'),
                                    'locationstatus' => new external_value(PARAM_RAW, 'locationstatus'),
                                    'actionsview' => new external_value(PARAM_RAW, 'actionsview'),
                                    'viewhallurl' => new external_value(PARAM_RAW, 'viewhallurl'),
                                )
                            )
                        ),
                        'request_view' => new external_value(PARAM_INT, 'request_view', VALUE_OPTIONAL),
                        'nocourses' => new external_value(PARAM_BOOL, 'nocourses', VALUE_OPTIONAL),
                        'totalhalls' => new external_value(PARAM_INT, 'totalhalls', VALUE_OPTIONAL),
                        'length' => new external_value(PARAM_INT, 'length', VALUE_OPTIONAL),
                        'managehall' => new external_value(PARAM_RAW, 'managehall', VALUE_OPTIONAL),
                    )
                )
        ]);
    }
    public static function hall_info_parameters() {
        return new external_function_parameters(
            array(
                'hallid' => new external_value(PARAM_INT, 'hallid', 0),
                )
        );
    }
    public static function hall_info($hallid) {
        global $DB;
        require_login();
        $params = self::validate_parameters(self::hall_info_parameters(),
                                    ['hallid' => $hallid]);
        $data = (new local_hall\hall)->hall_info($hallid);
        return [
            'options' => $data,
        ];
    }

  /**
   * Returns description of method result value
   * @return external_description
   */

    public static function hall_info_returns() {
        return new external_single_structure([
          'options' => new external_value(PARAM_RAW, 'The paging data for the service'),
        ]);
    }

    public static function reservation_info_parameters() {
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

    public static function reservation_info($options, $dataoptions, $offset = 0, $limit = 0, $contextid, $filterdata) {
        global $DB, $PAGE;
        require_login();
        // Parameter validation.
        $params = self::validate_parameters(
            self::reservation_info_parameters(),
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
        $data = (new local_hall\hall)->get_listof_reservations($stable, $filtervalues);
        $totalcount = $data['totalexams'];
        switch($filtervalues->type){
            /*case 'tprogram':
                $type = get_string('pluginname', 'local_trainingprogram');
                break;
            case 'event':
                $type = get_string('title', 'local_events');
                break;
            case 'exam':
            default:
                $type = get_string('exam', 'local_exams');
                break;*/

            case 'exam':
            $type = get_string('exam', 'local_exams');
            break;
            case 'event':
            $type = get_string('title', 'local_events');
            break;
            case 'tprogram':
            default:
            $type = get_string('pluginname', 'local_trainingprogram');
            
            break;
        }
        $tpofferings = $data['tpofferings']; 
        return [
            'totalcount' => $totalcount,
            'length' => $totalcount,
            'filterdata' => $filterdata,
            'records' =>$data,
            'options' => $options,
            'dataoptions' => $dataoptions,
            'type' => $type,
            'tpofferings' => $tpofferings,
        ];
    }

    /**
     * Returns description of method result value
     * @return external_description
     */

    public static function reservation_info_returns() {
        return new external_single_structure([
            'options' => new external_value(PARAM_RAW, 'The paging data for the service'),
            'nocourses' => new external_value(PARAM_BOOL, 'nocourses', VALUE_OPTIONAL),
            'type' => new external_value(PARAM_TEXT, 'type', VALUE_OPTIONAL),
            'tpofferings' => new external_value(PARAM_BOOL, 'tpofferings', VALUE_OPTIONAL),            
            'dataoptions' => new external_value(PARAM_RAW, 'The data for the service'),
            'totalcount' => new external_value(PARAM_INT, 'total number of challenges in result set'),
            'filterdata' => new external_value(PARAM_RAW, 'total number of challenges in result set', VALUE_OPTIONAL),
            'length' => new external_value(PARAM_RAW, 'total number of challenges in result set'),
            'records' => new external_single_structure(
                    array(
                        'exams' => new external_multiple_structure(
                            new external_single_structure(
                                array(
                                    'id' => new external_value(PARAM_INT, 'id', VALUE_OPTIONAL),
                                    'contextid' => new external_value(PARAM_INT, 'contextid', VALUE_OPTIONAL),
                                    'exam' => new external_value(PARAM_RAW, 'exam', VALUE_OPTIONAL),
                                    'startdate' => new external_value(PARAM_RAW, 'startdate', VALUE_OPTIONAL),
                                    'enddate' => new external_value(PARAM_RAW, 'enddate', VALUE_OPTIONAL),
                                    'tprogram' => new external_value(PARAM_BOOL, 'tprogram', VALUE_OPTIONAL),
                                    'code' => new external_value(PARAM_RAW, 'code', VALUE_OPTIONAL),                                   
                                    'time' => new external_value(PARAM_RAW, 'time', VALUE_OPTIONAL),
                                    'seats' => new external_value(PARAM_RAW, 'seats', VALUE_OPTIONAL),
                                    'hallid' => new external_value(PARAM_RAW, 'hallid', VALUE_OPTIONAL),
                                    'type' =>  new external_value(PARAM_TEXT, 'type', VALUE_OPTIONAL),
                                    'hallseats' =>  new external_value(PARAM_TEXT, 'hallseats', VALUE_OPTIONAL),
                                    'starttime' =>  new external_value(PARAM_RAW, 'starttime', VALUE_OPTIONAL),
                                ),'', VALUE_OPTIONAL
                            )
                        ,'', VALUE_OPTIONAL),
                        'request_view' => new external_value(PARAM_INT, 'request_view', VALUE_OPTIONAL),
                        'nocourses' => new external_value(PARAM_BOOL, 'nocourses', VALUE_OPTIONAL),
                        'totalhalls' => new external_value(PARAM_INT, 'totalhalls', VALUE_OPTIONAL),
                        'length' => new external_value(PARAM_INT, 'length', VALUE_OPTIONAL),
                        'managehall' => new external_value(PARAM_RAW, 'managehall', VALUE_OPTIONAL),
                    )
                )
        ]);
    }

    public static function hall_reservation_parameters() {
        return new external_function_parameters(
            array(
                'contextid' => new external_value(PARAM_INT, 'The context id for the category'),
                'jsonformdata' => new external_value(PARAM_RAW, 'The data from the create course form, encoded as a json array')
            )
        );
    }

    /**
     * Submit the create category form.
     *
     * @param int $contextid The context id for the category.
     * @param string $jsonformdata The data from the form, encoded as a json array.
     * @return int new category id.
     */
    public static function hall_reservation($contextid, $jsonformdata) {
        global $DB, $CFG, $USER;
        // require_once($CFG->dirroot.'/course/lib.php');
        // require_once($CFG->libdir.'/coursecatlib.php');
        // require_once($CFG->dirroot . '/local/courses/lib.php');

        // We always must pass webservice params through validate_parameters.
        $params = self::validate_parameters(self::hall_reservation_parameters(),
                                            ['contextid' => $contextid, 'jsonformdata' => $jsonformdata]);

        $context = context::instance_by_id($params['contextid'], MUST_EXIST);
        // We always must call validate_context in a webservice.
        self::validate_context($context);
        $serialiseddata = json_decode($params['jsonformdata']);

        $data = array();
        parse_str($serialiseddata, $data);

        $warnings = array();
        $id = $data['id'];
        if ($id) {
            $coursecat = coursecat::get($id, MUST_EXIST, true);
            $category = $coursecat->get_db_record();
            $context = context_coursecat::instance($id);
            $itemid = 0; // Initialise itemid, as all files in category description has item id 0.
        } else {
            $parent = $data['parent'];
            if ($parent) {
                $DB->record_exists('course_categories', array('id' => $parent), '*', MUST_EXIST);
                $context = context_coursecat::instance($parent);
            } else {
                $context = context_system::instance();
            }
            $category = new stdClass();
            $category->id = 0;
            $category->parent = $parent;
            $itemid = null; // Set this explicitly, so files for parent category should not get loaded in draft area.
        }

        // The last param is the ajax submitted data.
        $mform = new local_hall\form\listofhallsform(null, array(), 'post', '', null, true, $data);
        $validateddata = $mform->get_data();


        return $validateddata->halls;
    }

    /**
     * Returns description of method result value.
     *
     * @return external_description
     * @since Moodle 3.0
     */
    public static function hall_reservation_returns() {
        return new external_value(PARAM_INT, 'category id');
    }

    public static function hall_data_parameters() {
        return new external_function_parameters([
            'options' => new external_value(PARAM_RAW, 'The paging data for the service'),
            'dataoptions' => new external_value(PARAM_RAW, 'The data for the service'),
            'offset' => new external_value(PARAM_INT, 'Number of items to skip from the begging of the result set',
                VALUE_DEFAULT, 0),
            'limit' => new external_value(PARAM_INT, 'Maximum number of results to return',
                VALUE_DEFAULT, 0),
            'filterdata' => new external_value(PARAM_RAW, 'filters applied'),
        ]);
      }

      public static function hall_data($options, $dataoptions, $offset = 0, $limit = 0, $filterdata) {
        global $DB, $PAGE;
        // Parameter validation.
        $context = context_system::instance();
        $contextid = $context->id;

        $params = self::validate_parameters(
            self::hall_data_parameters(),
            [
                'options' => $options,
                'dataoptions' => $dataoptions,
                'offset' => $offset,    
                'limit' => $limit,
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
        $data =  (new local_hall\hall)->hall_data($stable, $filtervalues);
        $totalcount = $data['totalexams'];
        $totalhallseats = $data['totalhallseats'];
        $totalentityseats = $data['totalentityseats'];
        $type = $data['type'];
        $entityname = $data['entityname'] ? $data['entityname']: $filtervalues->examdate;
        $entityid = $data['entityid'];
    
        return [
            'totalcount' => $totalcount,
            'length' => $totalcount,
            'filterdata' => $filterdata,
            'records' =>$data,
            'options' => $options,
            'dataoptions' => $dataoptions,
            "totalhallseats" => $totalhallseats,
            "totalentityseats" => $totalentityseats,
            "type" => $type,
            "entityname" => $entityname,
            "entityid" => $entityid,
        ];
      }

      public static function hall_data_returns() {
        return new external_single_structure([
            'options' => new external_value(PARAM_RAW, 'The paging data for the service'),
            'dataoptions' => new external_value(PARAM_RAW, 'The data for the service'),
            'totalcount' => new external_value(PARAM_INT, 'total number of challenges in result set'),
            'filterdata' => new external_value(PARAM_RAW, 'total number of challenges in result set'),
            'totalhallseats' => new external_value(PARAM_INT, 'total number of challenges in result set'),
            'entityid' => new external_value(PARAM_RAW, 'total number of challenges in result set', VALUE_OPTIONAL),            
            'totalentityseats' => new external_value(PARAM_RAW, 'total number of challenges in result set'),
            'type' => new external_value(PARAM_RAW, 'total number of challenges in result set'),
            'entityname' =>  new external_value(PARAM_RAW, 'total number of challenges in result set'),
            'length' => new external_value(PARAM_RAW, 'total number of challenges in result set'),
            'records' => new external_single_structure(
                    array(
                        'hascourses' => new external_multiple_structure(
                            new external_single_structure(
                                array(
                                    'slid' => new external_value(PARAM_INT, 'slid', VALUE_OPTIONAL),
                                    'start' => new external_value(PARAM_RAW, 'start', VALUE_OPTIONAL),
                                    'end' => new external_value(PARAM_RAW, 'end', VALUE_OPTIONAL),
                                    'start_time' => new external_value(PARAM_RAW, 'start_time', VALUE_OPTIONAL),
                                    'end_time' => new external_value(PARAM_RAW, 'end_time', VALUE_OPTIONAL),
                                    'hallid' => new external_value(PARAM_RAW, 'hallid', VALUE_OPTIONAL),
                                    'typeid'=>new external_value(PARAM_RAW,'typeid', VALUE_OPTIONAL),
                                    'submit_type'=>new external_value(PARAM_RAW,'submit_type', VALUE_OPTIONAL),
                                    'examdate'=>new external_value(PARAM_RAW,'examdate', VALUE_OPTIONAL),
                                    'booked'=>new external_value(PARAM_BOOL,'booked', VALUE_OPTIONAL),
                                    'examname'=>new external_value(PARAM_RAW,'examname', VALUE_OPTIONAL),
                                    'availableseats'=>new external_value(PARAM_RAW,'availableseats', VALUE_OPTIONAL),
                                    'reservedseats'=>new external_value(PARAM_RAW,'reservedseats', VALUE_OPTIONAL),
                                    'totalseats'=>new external_value(PARAM_RAW,'totalseats', VALUE_OPTIONAL),
                                    'type'=>new external_value(PARAM_RAW,'type', VALUE_OPTIONAL), 
                                    'entitiesseats'=>new external_value(PARAM_RAW,'entitiesseats', VALUE_OPTIONAL),
                                    'reservationid'=>new external_value(PARAM_RAW,'reservationid', VALUE_OPTIONAL),
                                    'currentbooking'=>new external_value(PARAM_RAW,'currentbooking', VALUE_OPTIONAL),
                                )
                            )
                        ),
                        'request_view' => new external_value(PARAM_INT, 'request_view', VALUE_OPTIONAL),
                        'nocourses' => new external_value(PARAM_BOOL, 'nocourses', VALUE_OPTIONAL),
                        'totalexams' => new external_value(PARAM_INT, 'totalposts', VALUE_OPTIONAL),
                        'length' => new external_value(PARAM_INT, 'length', VALUE_OPTIONAL),
                    )
                )

        ]);
    }
    public static function schedule_view_parameters() {
        return new external_function_parameters([
            'options' => new external_value(PARAM_RAW, 'The paging data for the service'),
            'dataoptions' => new external_value(PARAM_RAW, 'The data for the service'),
            'offset' => new external_value(PARAM_INT, 'Number of items to skip from the begging of the result set',
                VALUE_DEFAULT, 0),
            'limit' => new external_value(PARAM_INT, 'Maximum number of results to return',
                VALUE_DEFAULT, 0),
            'filterdata' => new external_value(PARAM_RAW, 'filters applied'),
        ]);
    }

    public static function schedule_view($options, $dataoptions, $offset = 0, $limit = 0, $filterdata) {
        global $DB, $PAGE, $CFG;       
        // Parameter validation.
        $contextid = 1;
        $params = self::validate_parameters(
            self::schedule_view_parameters(),
            [
                'options' => $options,
                'dataoptions' => $dataoptions,
                'offset' => $offset,    
                'limit' => $limit,
                'filterdata' => $filterdata
            ]
        );
        $offset = $params['offset'];
        $limit = $params['limit'];
        $filtervalues = json_decode($filterdata);
        $dataoption = json_decode($dataoptions);
        
        if($dataoption->examid > 0) {
            $filtervalues[examid] = $dataoption->examid;
            $examid = $dataoption->examid;
        }

        $stable = new \stdClass();
        $stable->thead = false;
        $stable->start = $offset;
        $stable->length = $limit;
        $data =  (new local_hall\hall)->schedule_view($stable, $filtervalues);       
        $totalcount = $data['totalhalls'];
        $halladdress = $data['halladdress'];
        $city = $data['city'];
        $buildingname = $data['buildingname'];
        if(empty($city) && empty($buildingname) && empty($halladdress)) {
            $filters = true;
        }
        return [
            'totalcount' => $totalcount,
            'length' => $totalcount,
            'filterdata' => $filterdata,
            'records' =>$data,
            'options' => $options,
            'dataoptions' => $dataoptions,
            "halladdress" => $halladdress,
            "city" => $city,
            "buildingname" => $buildingname,
            "filters" => $filters,
            'url' => $CFG->wwwroot,
            "examid" => $examid,
        ];
    }

    public static function schedule_view_returns() {
        return new external_single_structure([
            'options' => new external_value(PARAM_RAW, 'The paging data for the service'),
            'halladdress' => new external_value(PARAM_RAW, 'The paging data for the service'),
            'url' => new external_value(PARAM_RAW, 'url'),
            'examid' => new external_value(PARAM_RAW, 'examid', VALUE_OPTIONAL),
            'filters' => new external_value(PARAM_RAW, 'The paging data for the service', VALUE_OPTIONAL),
            'city' => new external_value(PARAM_RAW, 'The paging data for the service'),
            'buildingname' => new external_value(PARAM_RAW, 'The paging data for the service'),
            'dataoptions' => new external_value(PARAM_RAW, 'The data for the service'),
            'totalcount' => new external_value(PARAM_INT, 'total number of challenges in result set'),
            'filterdata' => new external_value(PARAM_RAW, 'total number of challenges in result set'),
            'length' => new external_value(PARAM_RAW, 'total number of challenges in result set'),
            'records' => new external_single_structure(
                    array(
                        'hascourses' => new external_multiple_structure(
                            new external_single_structure(
                                array(
                                    'hallname' => new external_value(PARAM_RAW, 'hallname', VALUE_OPTIONAL),
                                    'results' => new external_multiple_structure(
                                        new external_single_structure(
                                            array(
                                                'id' => new external_value(PARAM_INT, 'id', VALUE_OPTIONAL),
                                                'hall' => new external_value(PARAM_RAW, 'hall', VALUE_OPTIONAL),
                                                'date' => new external_value(PARAM_RAW, 'date', VALUE_OPTIONAL),
                                                'type' => new external_value(PARAM_RAW, 'type', VALUE_OPTIONAL),
                                                'duration' => new external_value(PARAM_RAW, 'duration', VALUE_OPTIONAL),
                                                'examdate' => new external_value(PARAM_RAW, 'examdate', VALUE_OPTIONAL),
                                            )
                                        )
                                    ),
                                )
                            )
                        ),
                        'request_view' => new external_value(PARAM_INT, 'request_view', VALUE_OPTIONAL),
                        'nocourses' => new external_value(PARAM_BOOL, 'nocourses', VALUE_OPTIONAL),
                        'totalexams' => new external_value(PARAM_INT, 'totalposts', VALUE_OPTIONAL),
                        'length' => new external_value(PARAM_INT, 'length', VALUE_OPTIONAL),
                    )
                )

        ]);
    }
    public static function hall_slotbooking_parameters() {
        return new external_function_parameters(
            array(
                'hallid' => new external_value(PARAM_INT, 'hallid', 0),
                'examid' => new external_value(PARAM_INT, 'examid', 0),
                'examdate' => new external_value(PARAM_RAW, 'examdate', 0),
                'start' => new external_value(PARAM_RAW, 'start', 0),
                'end' => new external_value(PARAM_RAW, 'end', 0),
                'typeid' => new external_value(PARAM_INT, 'typeid', 0),
                'type' => new external_value(PARAM_TEXT, 'type', ''),
                'seats' => new external_value(PARAM_INT, 'seats', 0),
                'referencecode' => new external_value(PARAM_RAW, 'referencecode', 0),
                'entityid' => new external_value(PARAM_RAW, 'entityid', 0),
                'starttime' => new external_value(PARAM_RAW, 'entityid', 0),
                'reservationid' =>  new external_value(PARAM_RAW, 'reservationid', 0),
                'submit_type' =>  new external_value(PARAM_RAW, 'submit_type', 0),
                )
        );
    }
    public static function hall_slotbooking($hallid, $examid, $examdate, $start, $end, $typeid, $type, $seats, $referencecode, $entityid, $starttime, $reservationid, $submit_type) {
        global $DB, $USER,$CFG;
        require_login();
        $systemcontext = context_system::instance();
        $params = self::validate_parameters(self::hall_slotbooking_parameters(),
                        ['hallid' => $hallid, 'examid' => $examid, 'examdate' => $examdate, 'start' => $start, 'end' => $end, 'typeid' => $typeid, 'type' => $type, 'seats' => $seats, 'referencecode' => $referencecode, 'entityid' => $entityid, 'reservationid' => $reservationid]);
        
        $halls = $DB->get_fieldset_sql("SELECT hallid FROM {hall_reservations} WHERE typeid = $typeid AND type = '{$type}'");

        if( !in_array($hallid, $halls) && $type != 'exam') {
            $DB->delete_records('hall_reservations', ['typeid' => $typeid, 'type' => $type]);
        }

        $row = []; 
        $row['hallid'] = $hallid;
        $row['examdate'] = $examdate;
        $row['date'] = $examdate;
        $row['slotstart'] = $start;
        $row['slotend'] = $end;
        $row['typeid'] = $typeid;
        $row['type'] = $type;
        $row['userid'] = $USER->id;
        $row['seats']  = $seats;
        $row['entitycode']  = $referencecode;
        $row['entityid']  = $entityid;
        $row['status']  = 1;

        $record = $DB->get_record('hall_reservations', ['hallid' => $hallid, 'examdate' => $examdate, 'slotstart' => $start, 'slotend' => $end, 'typeid' => $typeid, 'type' => $type, 'slotstart' => $start, 'slotend' => $end ]);
        if($record->id > 0 || $reservationid > 0) {
            if($record->id > 0) {
                $row['id'] = $record->id;
            } else {
                $row['id'] = $reservationid;
            }
            $row['seats'] = $seats + $record->seats;
            $DB->update_record('hall_reservations', $row);

            //$event =  \local_hall\event\reservation_update::create(array( 'context'=>$systemcontext, 'objectid' => $record->id));
           //$event->trigger();
           
            $entitydata = [];
            $entitydata['id'] = $typeid;
            $entitydata['halladdress'] = $hallid;
            switch($type){
                case 'tprogram':
                    $DB->update_record('tp_offerings', $entitydata);
                    break;
                case 'event':
                    $DB->update_record('local_events', $entitydata);
                    break;
                case 'exam':
                    $DB->update_record('local_exams', $entitydata);
                    break;
            }
        } else {
            if($type == 'tprogram' && $submit_type == 'form' || $type == 'event' && $submit_type == 'form' || $type == 'questionbank' && $submit_type == 'form') {

                $draftid = $DB->get_record('reservations_draft', ['type' => $type,'entitycode' => "{$row["entitycode"]}", 'entityid' => "{$row["entityid"]}"]);
                //'hallid' => $row['hallid'], 'date' => "{$row['date']}", 'slotstart' => $row['slotstart'], 'slotend' => $row['slotend']
                if($draftid->id) {

                    $row[id] = $draftid->id;
                    $row[seats] = $row['seats']; //+ $draftid->seats;
                    $record = $DB->update_record('reservations_draft', $row);

                } else {

                    $record = $DB->insert_record('reservations_draft', $row);

                }

                $hall = $DB->get_record('hall', ['id' => $hallid]);
                $hallname = $hall->name;
                $hallseats = $row['seats'];
                $hallexamdate = $row['examdate'];
            } else {
                $record = $DB->insert_record('hall_reservations', $row);

               // $event =  \local_hall\event\hall_reserved::create(array( 'context'=>$systemcontext, 'objectid' =>$record));
               // $event->trigger();

                $hallname = $DB->get_field('hall', 'name', ['id' => $hallid]); 
                $hallseats = $seats;
                $hallexamdate = $examdate;
                   $thispageurl = new moodle_url('/local/hall/schedule.php');
                $row1=[];           
                $row1['HallName']=$hallname;           
                $row1['RelatedModuleName'] = $type;
                $row1['reservationLink'] = $thispageurl;
                $myobject=(new \local_hall\notification);
                $myobject->hall_reservation_notification('hall_reservation',$touser=null, $USER,$row1,$waitinglistid=0);
            }
        }

        return [
            'name' => $hallname,
            'seats' => $hallseats,
            'examdate' => $hallexamdate,
        ];
    }
    public static function hall_slotbooking_returns() {
        return new external_single_structure([
            'name' => new external_value(PARAM_RAW, 'The paging data for the service'),
            'seats' => new external_value(PARAM_RAW, 'The paging data for the service'),
            'examdate' => new external_value(PARAM_RAW, 'The paging data for the service'),
        ]);
    }

    public static function hallreservation_view_parameters() {
        return new external_function_parameters([
            'options' => new external_value(PARAM_RAW, 'The paging data for the service'),
            'dataoptions' => new external_value(PARAM_RAW, 'The data for the service'),
            'offset' => new external_value(PARAM_INT, 'Number of items to skip from the begging of the result set',
                VALUE_DEFAULT, 0),
            'limit' => new external_value(PARAM_INT, 'Maximum number of results to return',
                VALUE_DEFAULT, 0),
            'filterdata' => new external_value(PARAM_RAW, 'filters applied'),
        ]);
      }

      public static function hallreservation_view($options, $dataoptions, $offset = 0, $limit = 0, $filterdata) {
        global $DB, $PAGE;       
        // Parameter validation.
        $contextid = 1;
        $params = self::validate_parameters(
            self::hallreservation_view_parameters(),
            [
                'options' => $options,
                'dataoptions' => $dataoptions,
                'offset' => $offset,    
                'limit' => $limit,
                'filterdata' => $filterdata
            ]
        );
        $filtervalues = json_decode($filterdata);

        $stable = new \stdClass();
        $stable->thead = false;
        $stable->start = $offset;
        $stable->length = $limit;
        $data =  (new local_hall\hall)->hallreservationsdetails($stable, $filtervalues);
        $totalcount = $data['totalhalls'];
    
        return [
            'totalcount' => $totalcount,
            'length' => $totalcount,
            'filterdata' => $filterdata,
            'records' =>$data,
            'options' => $options,
            'dataoptions' => $dataoptions,
        ];
      }

      public static function hallreservation_view_returns() {
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
                               'aa' => new external_multiple_structure(
                                    new external_single_structure(
                                        array(
                                            'id' => new external_value(PARAM_INT, 'id', VALUE_OPTIONAL),
                                            'type' => new external_value(PARAM_RAW, 'type', VALUE_OPTIONAL),
                                            'modulename' => new external_value(PARAM_RAW, 'modulename', VALUE_OPTIONAL),
                                            'code' => new external_value(PARAM_RAW, 'code', VALUE_OPTIONAL),
                                            'bookedseats' => new external_value(PARAM_RAW, 'bookedseats', VALUE_OPTIONAL),     
                                            'date' => new external_value(PARAM_RAW, 'date', VALUE_OPTIONAL),
                                            'entityslotstart' => new external_value(PARAM_RAW, 'entityslotstart', VALUE_OPTIONAL),
                                            'entityslotend' => new external_value(PARAM_RAW, 'entityslotend', VALUE_OPTIONAL),
                                        )
                                    )
                                ),

                               'rr' => new external_single_structure(
                                    array(
                                        'reservationdate' => new external_value(PARAM_RAW, 'reservationdate', VALUE_OPTIONAL),
                                        'entity' => new external_value(PARAM_RAW, 'entity', VALUE_OPTIONAL),
                                        'slotstart' => new external_value(PARAM_RAW, 'slotstart', VALUE_OPTIONAL),
                                        'slotend' => new external_value(PARAM_RAW, 'slotend', VALUE_OPTIONAL),
                                        'totalhallseats' => new external_value(PARAM_RAW, 'totalhallseats', VALUE_OPTIONAL),
                                        'reservedseats' => new external_value(PARAM_RAW, 'reservedseats', VALUE_OPTIONAL),
                                        'availableseats' => new external_value(PARAM_RAW, 'availableseats', VALUE_OPTIONAL),
                                    )
                                ),
                            )
                        )
                    ),
                    'request_view' => new external_value(PARAM_INT, 'request_view', VALUE_OPTIONAL),
                    'nocourses' => new external_value(PARAM_BOOL, 'nocourses', VALUE_OPTIONAL),
                    'totalexams' => new external_value(PARAM_INT, 'totalposts', VALUE_OPTIONAL),
                    'length' => new external_value(PARAM_INT, 'length', VALUE_OPTIONAL),
                )
            )
        ]);
    }

    public static function hallinfo_view_parameters() {
        return new external_function_parameters([
            'id' => new external_value(PARAM_RAW, 'The paging data for the service', VALUE_DEFAULT, 0),
            'options' => new external_value(PARAM_RAW, 'The paging data for the service', VALUE_OPTIONAL),
            'dataoptions' => new external_value(PARAM_RAW, 'The data for the service', VALUE_OPTIONAL),
            'offset' => new external_value(PARAM_INT, 'Number of items to skip from the begging of the result set',
                VALUE_DEFAULT, 0),
            'limit' => new external_value(PARAM_INT, 'Maximum number of results to return',
                VALUE_DEFAULT, 0),
            'filterdata' => new external_value(PARAM_RAW, 'filters applied', VALUE_OPTIONAL),
        ]);
    }

    public static function hallinfo_view($id=false, $options=false, $dataoptions=false, $offset = 0, $limit = 0, $filterdata=false) {
        global $DB, $PAGE;       
        // Parameter validation.
        $contextid = 1;
        $params = self::validate_parameters(
            self::hallinfo_view_parameters(),
            [
                'options' => $options,
                'dataoptions' => $dataoptions,
                'offset' => $offset,    
                'limit' => $limit,
                'filterdata' => $filterdata,
                'id' => $id,
            ]
        );
        $filtervalues = json_decode($filterdata);

        if (!empty($id)) {
            $filtervalues->hallid = $id;
        } else {
            $filtervalues->hallid = json_decode($dataoptions)->hallid;
        }

        $stable = new \stdClass();
        $stable->thead = false;
        $stable->start = $offset;
        $stable->length = $limit;
        $data =  (new local_hall\hall)->hallinfo_view($stable, $filtervalues);
        $totalcount = $data['totalhalls'];

        return [
            'totalcount' => $totalcount,
            'length' => $totalcount,
            'filterdata' => $filterdata,
            'records' =>$data,
            'options' => $options,
            'dataoptions' => $dataoptions,
        ];
    }

    /**
     * Returns description of method result value
     * @return external_description
     */

    public static function hallinfo_view_returns() {
        return new external_single_structure([
            'totalcount' => new external_value(PARAM_INT, 'total number of challenges in result set'),
            'length' => new external_value(PARAM_RAW, 'total number of challenges in result set'),
            'records' => new external_single_structure(
                    array(
                        'hascourses' => new external_multiple_structure(
                            new external_single_structure(
                                array(
                                    'id' => new external_value(PARAM_INT, 'id'),
                                    'name' => new external_value(PARAM_RAW, 'name'),
                                    'city' => new external_value(PARAM_RAW, 'city'),
                                    'type' => new external_value(PARAM_RAW, 'type'),
                                    'buildingname' => new external_value(PARAM_RAW, 'buildingname'),
                                    'seatingcapacity' => new external_value(PARAM_INT, 'seatingcapacity'),
                                    'maplocation' => new external_value(PARAM_RAW, 'maplocation'),
                                    'roomshape' => new external_value(PARAM_RAW, 'roomshape'),
                                    'availability' => new external_value(PARAM_RAW, 'availability'),
                                    'equipmentavailable' => new external_value(PARAM_RAW, 'equipmentavailable'),
                                    'description' => new external_value(PARAM_RAW, 'description'),
                                    'locationstatus' => new external_value(PARAM_RAW, 'locationstatus'),
                                )
                            )
                        ),
                        'hallreservations' => new external_multiple_structure(
                            new external_single_structure(
                                array(
                                    'id' => new external_value(PARAM_INT, 'id'),
                                    'typeid' => new external_value(PARAM_INT, 'typeid'),
                                    'modulename' => new external_value(PARAM_RAW, 'modulename'),
                                    'code' => new external_value(PARAM_RAW, 'code'),
                                    'type' => new external_value(PARAM_RAW, 'type'),
                                    'entityslotstart' => new external_value(PARAM_RAW, 'slotstart'),
                                    'entityslotend' => new external_value(PARAM_RAW, 'slotend'),
                                    'date' => new external_value(PARAM_RAW, 'date'),
                                    'reservationdate' => new external_value(PARAM_INT, 'reservationdate'),
                                    'bookedseats' => new external_value(PARAM_INT, 'seats'),
                                    'entitydate' => new external_value(PARAM_RAW, 'entitydate'),
                                    'slotstart' => new external_value(PARAM_INT, 'slotstart'),
                                    'slotend' => new external_value(PARAM_INT, 'slotend'),
                                    'hallid' => new external_value(PARAM_INT, 'hallid'),
                                    'hallseats' => new external_value(PARAM_INT, 'hallseats'),
                                    'entitytotalseats' => new external_value(PARAM_INT, 'entitytotalseats'),
                                    'entityname' => new external_value(PARAM_RAW, 'entityname'),
                                    'starttime' => new external_value(PARAM_INT, 'starttime'),
                                    'entitytype' => new external_value(PARAM_RAW, 'entitytype'),
                                    'status' => new external_value(PARAM_BOOL, 'status'),
                                )
                            )
                        ),
                        'request_view' => new external_value(PARAM_INT, 'request_view', VALUE_OPTIONAL),
                        'nocourses' => new external_value(PARAM_BOOL, 'nocourses', VALUE_OPTIONAL),
                        'totalhalls' => new external_value(PARAM_INT, 'totalhalls', VALUE_OPTIONAL),
                        'length' => new external_value(PARAM_INT, 'length', VALUE_OPTIONAL),
                        'managehall' => new external_value(PARAM_RAW, 'managehall', VALUE_OPTIONAL),
                    )
                )
        ]);
    }

    //Vinod - Hall fake block for communication officer - Starts//

   public static function hallsblock_parameters() {
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

    /**
     * lists all halls
     *
     * @param array $options
     * @param array $dataoptions
     * @param int $offset
     * @param int $limit
     * @param int $contextid
     * @param array $filterdata
     * @return array courses list.
     */
    public static function hallsblock($options, $dataoptions, $offset = 0, $limit = 0, $contextid, $filterdata) {
        global $DB, $PAGE, $CFG;
        require_login();
        // Parameter validation.
        $params = self::validate_parameters(
            self::hallsblock_parameters(),
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
        $data = (new local_hall\hall)->get_listof_halls_for_block($stable, $filtervalues);
        $totalcount = $data['totalhalls'];

        return [
            'totalcount' => $totalcount,
            'length' => $totalcount,
            'filterdata' => $filterdata,
            'records' =>$data,
            'options' => $options,
            'dataoptions' => $dataoptions,
        ];
    }

    /**
     * Returns description of method result value
     * @return external_description
     */

    public static function hallsblock_returns() {
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
                                    'city' => new external_value(PARAM_RAW, 'city'),
                                    'type' => new external_value(PARAM_RAW, 'type'),
                                    'buildingname' => new external_value(PARAM_RAW, 'buildingname'),
                                    'seatingcapacity' => new external_value(PARAM_RAW, 'seatingcapacity'),
                                    'maplocation' => new external_value(PARAM_RAW, 'maplocation'),
                                    'roomshape' => new external_value(PARAM_RAW, 'roomshape'),
                                    'availability' => new external_value(PARAM_RAW, 'availability'),
                                    'equipmentavailable' => new external_value(PARAM_RAW, 'equipmentavailable'),
                                    'description' => new external_value(PARAM_RAW, 'description'),
                                    'locationstatus' => new external_value(PARAM_RAW, 'locationstatus'),
                                    'actionsview' => new external_value(PARAM_RAW, 'actionsview'),
                                    'viewhallurl' => new external_value(PARAM_RAW, 'viewhallurl'),
                                    
                                )
                            )
                        ),
                        'request_view' => new external_value(PARAM_INT, 'request_view', VALUE_OPTIONAL),
                        'nocourses' => new external_value(PARAM_BOOL, 'nocourses', VALUE_OPTIONAL),
                        'totalhalls' => new external_value(PARAM_INT, 'totalhalls', VALUE_OPTIONAL),
                        'length' => new external_value(PARAM_INT, 'length', VALUE_OPTIONAL),
                        'managehall' => new external_value(PARAM_RAW, 'managehall', VALUE_OPTIONAL),
                        'viewmoreurl' => new external_value(PARAM_RAW, 'viewmoreurl', VALUE_OPTIONAL),
                    )
                )
        ]);
    }
    //Vinod - Hall fake block for communication officer - Ends//
    public static function currenthalls_parameters() {
        return new external_function_parameters([         
            'query' => new external_value(PARAM_RAW, 'search query'),   
            'hallid' => new external_value(PARAM_RAW, 'type of the organization'),
            'type' => new external_value(PARAM_RAW, 'type of the organization'),
            'city' => new external_value(PARAM_RAW, 'type of the competencies'),
            'buildingname' => new external_value(PARAM_RAW, 'Program id'),
            'halllocation' => new external_value(PARAM_RAW, 'halllocation', VALUE_OPTIONAL),
        ]);
    }
    public static function currenthalls($query, $hallid=false, $type=false,$city=false,$buildingname=false, $halllocation=false) {
        global $PAGE;
        $params = array(
            'hallid' => $hallid,    
            'type' => $type,
            'city' => $city,
            'buildingname' => $buildingname,
            'query' => $query,
            'halllocation' => $halllocation,

        );
      
        $params = self::validate_parameters(self::currenthalls_parameters(), $params);
        switch($params['type']) {
            case 'schedulehalls':
                $data = (new local_hall\hall)->get_listof_currenthalls($params['buildingname'],$params['city'], $params['halllocation'],$params['query'],['id', 'fullname']);
            break;
            case 'buildingname':
                $data = (new local_hall\hall)->get_buildingname('buildingname', $params['city'], $params['query'],['id', 'fullname']);
            break;
            case 'city':
                $data = (new local_hall\hall)->get_buildingname('city',$params['query'], ['id', 'fullname']);
            break;
            case 'examhalls':
                $data = (new local_hall\hall)->get_examhalls($params['query'], ['id', 'fullname']);
            break;            
        }
        return ['status' => true, 'data' => $data];
    }
    public static function currenthalls_returns() {
        return new external_single_structure(
            array(
                'status' => new external_value(PARAM_INT, 'status: true if success'),
                'data' => new external_multiple_structure(new external_single_structure(
                    array(
                        'id' => new external_value(PARAM_RAW, 'status: true if success'),
                        'fullname' => new external_value(PARAM_RAW, 'status: true if success'))
                   )) 
             )
        );
    }

    public static function entities_search_parameters() {
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

    public static function entities_search($options=false, $dataoptions=false, $offset = 0, $limit = 0, $contextid=1, $filterdata=false) {
        global $DB, $PAGE;
        $sitecontext = context_system::instance();
        require_login();
        $PAGE->set_url('/local/hall/globalentitysearch.php');
        $PAGE->set_context($sitecontext);
        // Parameter validation.
        $params = self::validate_parameters(
            self::entities_search_parameters(),
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
        $data = (new local_hall\hall)->get_listof_entities($stable, $filtervalues);
        $totalcount = $data['totalentities'];

        return [
            'totalcount' => $totalcount,
            'length' => $totalcount,
            'records' => $data,
        ];
    }

    public static function entities_search_returns() {
        return new external_single_structure([
            'totalcount' => new external_value(PARAM_INT, 'total number of challenges in result set'),
            'length' => new external_value(PARAM_INT, 'length'),
            'records' => new external_single_structure(
                array(
                    'entities' => new external_multiple_structure(
                        new external_single_structure(
                            array(
                                'id' => new external_value(PARAM_INT, 'id', VALUE_OPTIONAL),
                                'name' => new external_value(PARAM_RAW, 'name', VALUE_OPTIONAL),
                                'type' => new external_value(PARAM_RAW, 'type', VALUE_OPTIONAL),
                                'Description' => new external_value(PARAM_RAW, 'description', VALUE_OPTIONAL),
                            ),'', VALUE_OPTIONAL
                        )
                    ,'', VALUE_OPTIONAL),
                    'request_view' => new external_value(PARAM_INT, 'request_view', VALUE_OPTIONAL),
                    'nocourses' => new external_value(PARAM_BOOL, 'nocourses', VALUE_OPTIONAL),
                    'totalcount' => new external_value(PARAM_INT, 'totalcount', VALUE_OPTIONAL),
                    'length' => new external_value(PARAM_INT, 'length', VALUE_OPTIONAL),
                )
            )
        ]);
    }


    public static function schedulehall_view_parameters() {
    return new external_function_parameters([
        
        'options' => new external_value(PARAM_RAW, 'The paging data for the service', VALUE_OPTIONAL),
        'dataoptions' => new external_value(PARAM_RAW, 'The data for the service', VALUE_OPTIONAL),
        'offset' => new external_value(PARAM_INT, 'Number of items to skip from the begging of the result set',
            VALUE_DEFAULT, 0),
        'limit' => new external_value(PARAM_INT, 'Maximum number of results to return',
            VALUE_DEFAULT, 0),
        'contextid' => new external_value(PARAM_INT, 'contextid', VALUE_OPTIONAL),
        'filterdata' => new external_value(PARAM_RAW, 'filters applied', VALUE_OPTIONAL),
        'id' => new external_value(PARAM_RAW, 'The paging data for the service', VALUE_OPTIONAL),

    ]);
  }

    /**
     * lists all halls
     *
     * @param array $options
     * @param array $dataoptions
     * @param int $offset
     * @param int $limit
     * @param int $contextid
     * @param array $filterdata
     * @return array courses list.
     */
    public static function schedulehall_view($options=false, $dataoptions=false, $offset = 0, $limit = 0, $contextid=1, $filterdata=false,$id=false) {
        global $DB, $PAGE;
        require_login();
        // Parameter validation.
        $params = self::validate_parameters(
            self::schedulehall_view_parameters(),
            [
                'id' => $id,
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
        if (!empty($id)) {
            $filtervalues->hallid = $id;
        } else {
            $filtervalues->hallid = json_decode($dataoptions)->hallid;
        }

        $stable = new \stdClass();
        $stable->thead = false;
        $stable->start = $offset;
        $stable->length = $limit;
        $data = (new local_hall\hall)->getschedulehalls($stable, $filtervalues);
        $totalcount = $data['totalhalls'];

        return [
            'totalcount' => $totalcount,
            'length' => $totalcount,
            'filterdata' => $filterdata,
            'records' =>$data,
            'options' => $options,
            'dataoptions' => $dataoptions,
        ];
    }

    /**
     * Returns description of method result value
     * @return external_description
     */

    public static function schedulehall_view_returns() {
         return new external_single_structure([
            'totalcount' => new external_value(PARAM_INT, 'total number of challenges in result set'),
            
            'records' => new external_single_structure(
                    array(
                        'hascourses' => new external_multiple_structure(
                            new external_single_structure(
                                array(
                                    'id' => new external_value(PARAM_INT, 'id'),
                                    'name' => new external_value(PARAM_RAW, 'name'),
                                    'city' => new external_value(PARAM_RAW, 'city'),
                                    'type' => new external_value(PARAM_RAW, 'type'),
                                    'buildingname' => new external_value(PARAM_RAW, 'buildingname'),
                                    'seatingcapacity' => new external_value(PARAM_INT, 'seatingcapacity'),
                                    'maplocation' => new external_value(PARAM_RAW, 'maplocation'),
                                    'roomshape' => new external_value(PARAM_RAW, 'roomshape'),
                                    'availability' => new external_value(PARAM_RAW, 'availability'),
                                    'equipmentavailable' => new external_value(PARAM_RAW, 'equipmentavailable'),
                                    'description' => new external_value(PARAM_RAW, 'description'),
                                    'locationstatus' => new external_value(PARAM_RAW, 'locationstatus'),
                                )
                            )
                        ),
                       
                       


                    'hashalllist' => new external_multiple_structure(
                    new external_single_structure(
                        array(
                            'startdate' => new external_value(PARAM_RAW, 'startdate'),
                            'data' => new external_multiple_structure(
                                new external_single_structure(
                                    array(
                                   'id' => new external_value(PARAM_INT, 'id'),
                                    'starttime' => new external_value(PARAM_RAW, 'starttime'),
                                    'endtime' => new external_value(PARAM_RAW, 'endtime'),
                                    'days' => new external_value(PARAM_RAW, 'days'),
                                    'hallurl' => new external_value(PARAM_RAW, 'hallurl'),
                                    'dedicatedfor' => new external_value(PARAM_RAW, 'dedicatedfor', VALUE_OPTIONAL),
                                    )
                                )
                            ), '', VALUE_OPTIONAL,
                        )
                    )
                ), '', VALUE_OPTIONAL,


                        'nocourses' => new external_value(PARAM_BOOL, 'nocourses', VALUE_OPTIONAL),
                        'totalhalls' => new external_value(PARAM_INT, 'totalhalls', VALUE_OPTIONAL),
                       'length' => new external_value(PARAM_RAW, 'total number of challenges in result set'),
                        
                    )
                )
        ]);
    }


    public static function deleteschedulehall_parameters(){
        return new external_function_parameters(
            array(
                'id' => new external_value(PARAM_INT, 'id', 0)
            )
        );
    }
  
    public static function deleteschedulehall($id){
        global $DB;
        $params = self::validate_parameters(self::deleteschedulehall_parameters(),
                                    ['id' => $id]);
        if($id){
           $id = $DB->delete_records('hallschedule', array('id' => $id));
            return true;
        } else {
            throw new moodle_exception('Error in deleting');
            return false;
        }
    }
   
    public static function deleteschedulehall_returns() {
        return new external_value(PARAM_BOOL, 'return');
    }
}
