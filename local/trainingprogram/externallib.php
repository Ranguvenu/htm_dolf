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
 * Defines the version of Training program
 *
 * @package    local_trainingprogram
 * @copyright  2022 Naveen kumar <naveen@eabyas.com>
 */

 
defined('MOODLE_INTERNAL') || die;

require_once("$CFG->libdir/externallib.php");
use tool_product\product;
/**
 * Training program external functions
 *
 */
class local_trainingprogram_external extends external_api
{
    public static function segmentlist_parameters() {
        $query = new external_value(PARAM_RAW, 'Query');
        $sectortype = new external_value(PARAM_ALPHANUMEXT, 'Type of sectordata', VALUE_REQUIRED);
        $selected_sectorlist = new external_value(PARAM_RAW, 'Get all job families in sector', VALUE_OPTIONAL);
        $sectorid = new external_value(PARAM_RAW, 'The sector id', VALUE_OPTIONAL,0);
        $sectorlist =  new external_value(PARAM_RAW, 'The sector id', VALUE_OPTIONAL);
        $allsegments = new external_value(PARAM_INT, 'Get all job families in sector', VALUE_OPTIONAL);

        $params = array(
            'query' => $query,
            'type' => $sectortype,
            'selected_sectorlist' => $selected_sectorlist,
            'sectorlist' => $sectorlist,
            'allsegments' => $allsegments,
            'sectorid' => $sectorid,
           
        );
        return new external_function_parameters($params);
    }


    public static function segmentlist($query, $type, $selected_sectorlist,$sectorlist, $allsegments, $sectorid=0) {
        global $PAGE, $DB;
        $params = array(
            'query' => $query,
            'type' => $type,
            'selected_sectorlist' => $selected_sectorlist,
            'sectorid' => $sectorid,
            'sectorlist' => $sectorlist,
            'allsegments' => $allsegments,
           
        );
        $params = self::validate_parameters(self::segmentlist_parameters(), $params);

        $currentlang= current_language();


        switch($params['type']) {
            case 'segment':


                if( $currentlang == 'ar'){

                    $title='titlearabic as title';

                }else{
                    $title='title as title';
                }

                if($params['sectorid'] == ''){

                    if(!empty($params['sectorlist']) && $params['sectorlist'] !=0) {

                    $segments = (new local_trainingprogram\local\sectors)->get_segments_from_sectorslist($params['sectorlist'],$query, ['id', $title, 'code']);
                    } else {
                       
                       $segments = array();

                    }
                }elseif($params['sectorid'] == '0'){

    
                    if(!empty($params['sectorlist']) && $params['sectorlist'] !=0) {

                    $segments = (new local_trainingprogram\local\sectors)->get_segments_from_sectorslist($params['sectorlist'],$query, ['id', $title, 'code']);
                    } else {
                       
                       $segments = array();

                    }

                } else {

                    $segments = (new local_trainingprogram\local\sectors)->get_segments($params['sectorid'],$query, ['id', $title, 'code']);

                }


            break;
            case 'jobfamily':
                if($params['sectorlist'] == 0 && $params['sectorid'] == 0){
                    $segments = array();
                    
                }else if($allsegments){

                    if($params['selected_sectorlist']) {
                        $selected_sectors = explode(',',$params['selected_sectorlist']);
                        $sectorslist =  explode(',',$params['sectorlist']);
                        $list = array_diff($sectorslist,$selected_sectors);
                        $sectors = implode(',',$list);
                    } else {
                        $sectors = $params['sectorlist']; 
                    }
                    if($sectors) {
                        $segmentdata = $DB->get_fieldset_sql('select id from {local_segment} where sectorid in ('.$sectors.')');
                    
                        $currentlang= current_language();
    
                        $query = trim($query);
    
                        if(COUNT($segmentdata) > 0) {
    
                            $segmentlist = implode(',', $segmentdata);
                           
                            if( $currentlang == 'ar'){
                                
                                if(!empty($query)) {
                                    $query = " AND familynamearabic LIKE '%$query%' ";
                                }
    
                               $segments = $DB->get_records_sql("select id,familynamearabic as title,code from {local_jobfamily} where segmentid IN($segmentlist,0)". $query);
    
                            } else {
                                
                                if(!empty($query)) {
                                    $query = " AND familyname LIKE '%$query%' ";
                                }
    
                                $segments = $DB->get_records_sql("select id,familyname as title,code from {local_jobfamily} where  segmentid IN($segmentlist,0) ". $query);
    
                            }
    
                        } else {
    
                            $segments = array();
                        }

                    } else {
    
                        $segments = array();
                    }

                   
                    
                }else{


                    if( $currentlang == 'ar'){

                        $title='familynamearabic as title';

                    }else{
                        $title='familyname as title';
                    }


                    $segments = (new local_trainingprogram\local\sectors)->get_jobfamilies($params['sectorid'],$query, ['id', $title, 'code']);
                }
               
            break;
            case 'jobrole':
            if($params['sectorlist'] == 0 && $params['sectorid'] == 0){
                    $segments = array();
                }else{


                    if( $currentlang == 'ar'){

                        $title='titlearabic as title';

                    }else{
                        $title='title as title';
                    }

                    $segments = (new local_trainingprogram\local\sectors)->get_jobroles($params['sectorid'],$query, ['id', $title, 'code']);
                }
                
            break;

        }

        return ['status' => true, 'data' => $segments];

    }

    public static function segmentlist_returns() {
        return new external_single_structure(
            array(
                'status' => new external_value(PARAM_INT, 'status: true if success'),
                'data' => new external_multiple_structure(new external_single_structure(
                    array(
                        'id' => new external_value(PARAM_INT, 'status: true if success',VALUE_OPTIONAL),
                        'title' => new external_value(PARAM_RAW, 'status: true if success',VALUE_OPTIONAL),
                        'familyname' => new external_value(PARAM_RAW, 'status: true if success',VALUE_OPTIONAL),
                        'code' => new external_value(PARAM_RAW, 'status: true if success',VALUE_OPTIONAL))
                   ),'',VALUE_OPTIONAL) 
             ));
    }

    public static function viewprograms_parameters() {
        return new external_function_parameters([
            'isArabic' => new external_value(PARAM_RAW, 'isArabic', VALUE_DEFAULT),
            'status' => new external_value(PARAM_RAW, 'The paging data for the service', VALUE_DEFAULT, 0),
            'options' => new external_value(PARAM_RAW, 'The paging data for the service', VALUE_OPTIONAL),
            'dataoptions' => new external_value(PARAM_RAW, 'The data for the service', VALUE_OPTIONAL),
            'offset' => new external_value(PARAM_INT, 'Number of items to skip from the begging of the result set',
                VALUE_DEFAULT, 0),
            'limit' => new external_value(PARAM_INT, 'Maximum number of results to return',
                VALUE_DEFAULT, 0),
            'filterdata' => new external_value(PARAM_RAW, 'filters applied', VALUE_OPTIONAL),
            'contextid' => new external_value(PARAM_INT, 'Maximum number of results to return',
                VALUE_DEFAULT, 1)

        ]);
    }
    public static function viewprograms($isArabic = NULL, $status=false, $options=false, $dataoptions=false, $offset = 0, $limit = 0, $filterdata=false,  $context=false ) {
        global $DB, $PAGE, $CFG, $USER;
        require_login();
        $params = self::validate_parameters(
            self::viewprograms_parameters(),
            [   'isArabic' => $isArabic,
                'options' => $options,
                'dataoptions' => $dataoptions,
                'offset' => $offset,
                'limit' => $limit,
                'filterdata' => $filterdata,
                'status' => $status,
            ]
        );
     
            $settings = external_settings::get_instance();
      
        $offset = $params['offset'];
        $limit = $params['limit'];
        $filtervalues = json_decode($filterdata);

        if ($status == 1) {
            $filtervalues->status = 1;
        } elseif($status == 2) {
            $filtervalues->status = 2;
            $filtervalues->type = 'mobile';
        } elseif($status == 3) {
            $filtervalues->status = 3;
        }

        $decodeddataoptions = json_decode($dataoptions);

        $stable = new \stdClass();
        $stable->thead = false;
        $stable->start = $offset;
        $stable->length = $limit;
        $stable->class = 'allinprogressprograms';
        $stable->type = $decodeddataoptions->type;

        // Parameters for My Trainings tab
        $stable->orguserid = $USER->id;
        $stable->tablename = 'tool_org_order_seats';
        $stable->selectparams = ',tppmnt.approvalseats,tppmnt.availableseats';
        $stable->mlang = ($settings->get_lang()) ?  $settings->get_lang() :(($isArabic)?(($isArabic == 'true') ? 'ar' :'en') : null);

        $data = (new local_trainingprogram\local\trainingprogram)->get_listof_programs($stable, $filtervalues);
        $totalcount = $data['totalprograms'];
        $data['actionview'] = $decodeddataoptions->actionview;
        return [
            'totalcount' => $totalcount,
            'length' => $totalcount,
            'records' =>$data,
            'url' => $CFG->wwwroot,
            'programs' => $data['hascourses'],
            'products' => !empty($data['products']) ? $data['products'] : [],
            'recommendedprograms' => !empty($data['recommendedprograms']) ? $data['recommendedprograms'] : [],
        ];
    }
    public static function viewprograms_returns() {
        return new external_single_structure([

           'recommendedprograms' => new external_multiple_structure(
                new external_single_structure(
                    array(
                        'entityid' => new external_value(PARAM_INT, 'entityid'),
                        'entityname' => new external_value(PARAM_RAW, 'reservationid'),
                        'entitycode' => new external_value(PARAM_RAW, 'slotstart', VALUE_OPTIONAL),
                        'entitystart' => new external_value(PARAM_INT, 'startdate', VALUE_OPTIONAL),
                        'entityend' => new external_value(PARAM_INT, 'enddate', VALUE_OPTIONAL),
                        'price' => new external_value(PARAM_RAW, 'price', VALUE_OPTIONAL),
                        'image' => new external_value(PARAM_URL, 'image', VALUE_OPTIONAL),
                    )
                )
            ),            
            'products' => new external_multiple_structure(
                new external_single_structure(
                    array(
                        'id' => new external_value(PARAM_INT, 'payment id'),
                        'name' => new external_value(PARAM_RAW, 'training name'),
                        'datebegin' => new external_value(PARAM_INT, 'startdate'),
                        'dateend' => new external_value(PARAM_INT, 'enddate'),
                        'purchasedseats' => new external_value(PARAM_INT, 'purchased seats'),
                        'approvalseats' => new external_value(PARAM_INT, 'approval seats'),
                        'availableseats' => new external_value(PARAM_INT, 'available seats'),
                        'enrolledseats' => new external_value(PARAM_INT, 'Enrolled seats'),
                        'action' => new external_value(PARAM_RAW, 'action'),
                        'timelimit' => new external_value(PARAM_RAW, 'Time Limit'),
                        'offeringview' => new external_value(PARAM_RAW, 'offeringview'),
                        'referenceid' => new external_value(PARAM_INT, 'offeringview'),
                        'entityid' => new external_value(PARAM_INT, 'offeringview'),
                        'courseid' => new external_value(PARAM_INT, 'courseid'),
                    )
                )
            ),
           'programs' => new external_multiple_structure(
                new external_single_structure(
                    array(
                        'trainingid' => new external_value(PARAM_INT, 'id'),
                        'name' => new external_value(PARAM_RAW, 'name'),
                        'timefrom' => new external_value(PARAM_INT, 'availablefrom'),
                        'timeto' => new external_value(PARAM_INT, 'availableto'),
                        'limitedjobfamily' => new external_value(PARAM_TEXT, 'limitedjobfamily'),
                        'published' => new external_value(PARAM_BOOL, 'published'),
                        'courseid' => new external_value(PARAM_INT, 'courseid'),
                        'image' => new external_value(PARAM_RAW, 'image', VALUE_OPTIONAL),
                        'duration' => new external_value(PARAM_RAW, 'duration', VALUE_OPTIONAL),
                        'fee' => new external_value(PARAM_RAW, 'fee', VALUE_OPTIONAL),
                        'sectors' => new external_multiple_structure(
                            new external_single_structure(
                                array(
                                    'sectorid' => new external_value(PARAM_INT, 'sectorid'),
                                    'sectorname' => new external_value(PARAM_RAW, 'sectorname'),
                                )
                            )
                        ), '', VALUE_OPTIONAL,
                        'jobfamily' => new external_multiple_structure(
                            new external_single_structure(
                                array(
                                    'name' => new external_value(PARAM_TEXT, 'name', VALUE_OPTIONAL),
                                )
                            )
                        ), '', VALUE_OPTIONAL,
                        'trainerslist' => new external_multiple_structure(
                            new external_single_structure(
                                array(
                                    'name' => new external_value(PARAM_TEXT, 'name', VALUE_OPTIONAL),
                                )
                            )
                        ), '', VALUE_OPTIONAL,
                        'certid' => new external_value(PARAM_ALPHANUMEXT, 'certid'),
                    )
                )
            ),'', VALUE_OPTIONAL,
          'url' => new external_value(PARAM_RAW, 'url'),
          'totalcount' => new external_value(PARAM_INT, 'total number of challenges in result set'),
          'length' => new external_value(PARAM_RAW, 'total number of challenges in result set'),
          'records' => new external_single_structure(
                  array(
                      'hascourses' => new external_multiple_structure(
                        new external_single_structure(
                            array(
                                'trainingid' => new external_value(PARAM_INT, 'id'),
                                'tainingprogramname' => new external_value(PARAM_RAW, 'tainingprogramname'),
                                'programcode' => new external_value(PARAM_RAW, 'programcode'),
                                'availablefrom' => new external_value(PARAM_RAW, 'availablefrom'),
                                'availableto' => new external_value(PARAM_RAW, 'availableto'),
                                'limitedjobfamily' => new external_value(PARAM_RAW, 'limitedjobfamily'),
                                'alljobbfamilies' => new external_value(PARAM_RAW, 'alljobbfamilies'),
                                'published' => new external_value(PARAM_RAW, 'published'),
                                'courseid' => new external_value(PARAM_INT, 'courseid'),
                                'sectorexists' => new external_value(PARAM_RAW, 'sectorexists'),
                                'currentofferingsexists' => new external_value(PARAM_RAW, 'currentofferingsexists'),
                                'competenciesexists' => new external_value(PARAM_RAW, 'competenciesexists'),
                                'triners' => new external_value(PARAM_RAW, 'triners'),
                                'datedisplay' => new external_value(PARAM_RAW, 'datedisplay'),
                                'deleteaction' => new external_value(PARAM_RAW, 'deleteaction'),
                                'programstatus' => new external_value(PARAM_RAW, 'programstatus'),
                                'manageprogramactions' => new external_value(PARAM_RAW, 'manageprogramactions'),
                                'viewcurentofferingbutton' => new external_value(PARAM_RAW, 'viewcurentofferingbutton'),
                                'detailedprogramviewurl' => new external_value(PARAM_RAW, 'detailedprogramviewurl'),
                                'certid' => new external_value(PARAM_TEXT, 'certid'),
                                'viewcertificateurl' => new external_value(PARAM_RAW, 'viewcertificateurl'),
                                'certificateview' => new external_value(PARAM_RAW, 'certificateview'),
                                'completedprofileactionview' => new external_value(PARAM_RAW, 'completedprofileactionview'),
                                'programagendaview' => new external_value(PARAM_RAW, 'programagendaview'),
                                'moreactinview' => new external_value(PARAM_RAW, 'moreactinview'),
                                'isorgofficial' => new external_value(PARAM_RAW, 'isorgofficial'),
                                'programdate' => new external_value(PARAM_INT, 'programdate'),
                                'isnotelearningmethod' => new external_value(PARAM_INT, 'isnotelearningmethod'),
                                'remainingdays' => new external_value(PARAM_INT, 'remainingdays'),
                                'cancelbuttonview' => new external_value(PARAM_RAW, 'cancelbuttonview'),
                                'istrainee' => new external_value(PARAM_RAW, 'istrainee'),
                                'traineeuserid' => new external_value(PARAM_INT, 'traineeuserid'),
                                'offeringid' => new external_value(PARAM_INT, 'offeringid'),
                                'traineefullname' => new external_value(PARAM_RAW, 'traineefullname'),
                                'programprice' => new external_value(PARAM_INT, 'programprice'),
                                'programdisplayname' => new external_value(PARAM_RAW, 'programdisplayname'),
                                'adminenrolled' => new external_value(PARAM_INT, 'adminenrolled'),
                                'productid' => new external_value(PARAM_INT, 'productid'),
                                'disableallactions' => new external_value(PARAM_RAW, 'disableallactions', VALUE_OPTIONAL),
                                'checkfavornot' => new external_value(PARAM_RAW,'checkfavornot',VALUE_OPTIONAL),
                                'isorgofficialortrainee' => new external_value(PARAM_RAW,'isorgofficialortrainee',VALUE_OPTIONAL),
                                'sorting' => new external_value(PARAM_RAW,'sorting',VALUE_OPTIONAL),
                                'isfinancialmanager' => new external_value(PARAM_RAW,'isfinancialmanager',VALUE_OPTIONAL),
                                'userroleshortname' => new external_value(PARAM_RAW,'userroleshortname',VALUE_OPTIONAL),
                                'sectors' => new external_multiple_structure(
                                    new external_single_structure(
                                        array(
                                            'sectorid' => new external_value(PARAM_INT, 'sectorid'),
                                            'sectorname' => new external_value(PARAM_RAW, 'sectorname'),
                                        )
                                    )
                                ), '', VALUE_OPTIONAL
                            )
                        )
                    ),'', VALUE_OPTIONAL,
                    'request_view' => new external_value(PARAM_INT, 'request_view', VALUE_OPTIONAL),
                    'totalprograms' => new external_value(PARAM_INT, 'totalprograms', VALUE_OPTIONAL),
                    'length' => new external_value(PARAM_INT, 'length', VALUE_OPTIONAL),
                    //'actionview' => new external_value(PARAM_BOOL, 'actionview', VALUE_OPTIONAL)
                   
                )

            )

        ]);
    }
    public static function viewprogramsectors_parameters() {
        return new external_function_parameters(
            array(
               'programid' => new external_value(PARAM_INT,'Program id',0),
            )
        );
    }
    public static function viewprogramsectors($programid) {
        global $DB,$CFG;
        $params = self::validate_parameters(self::viewprogramsectors_parameters(),
        ['programid'=>$programid]);
        $data = (new local_trainingprogram\local\trainingprogram)->viewprogramsectors($programid);
        return [
            'options' => $data,
        ];
    }
    public static function viewprogramsectors_returns() {
        return new external_single_structure([
          'options' => new external_value(PARAM_RAW, 'The paging data for the service'),
        ]);
    }  

    public static function competenciesinfo_parameters() {
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
 
    public static function competenciesinfo($options, $dataoptions, $offset = 0, $limit = 0, $filterdata) {
        global $OUTPUT, $CFG, $DB,$USER,$PAGE;
        $sitecontext = context_system::instance();
        $PAGE->set_context($sitecontext);
        $params = self::validate_parameters(
            self::competenciesinfo_parameters(),
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
        $stable->start = $offset;
        $stable->length = $limit;
        $stable->programid = $data_object->programid;
        $competencies = (new local_trainingprogram\local\trainingprogram)->competency_data($stable,$filtervalues);
        return [
            'totalcount' => $competencies['totalcount'],
            'records' => $competencies['acompetencies'],
            'options' => $options,
            'dataoptions' => $dataoptions,
            'filterdata' => $filterdata,
            'programid' => $data_object->programid,
            'nodata' => get_string('nocompetencys','local_competency')
        ];
    }

    /**
     * Returns description of method result value.
     */
    public static function  competenciesinfo_returns() {
        return new external_single_structure([
            'options' => new external_value(PARAM_RAW, 'The paging data for the service'),
            'programid' => new external_value(PARAM_INT, 'Program id'),
            'dataoptions' => new external_value(PARAM_RAW, 'The data for the service'),
            'totalcount' => new external_value(PARAM_INT, 'total number of competencies in result set'),
            'filterdata' => new external_value(PARAM_RAW, 'The data for the service'),
            'nodata' => new external_value(PARAM_RAW, 'The data for the service'),
            'records' => new external_multiple_structure(
                new external_single_structure(
                    array(
                        'id' => new external_value(PARAM_INT, 'competency id'),
                        'competencyname' => new external_value(PARAM_RAW, 'competency name'),
                        'code' => new external_value(PARAM_RAW, 'competency name'),
                        'fullname' => new external_value(PARAM_RAW, 'fullname')
                    )
                )
            ),
        ]);
    } 
     public static function ajaxdatalist_parameters() {
        $query = new external_value(PARAM_RAW, 'search query');
        $type = new external_value(PARAM_ALPHANUMEXT, 'Type of data', VALUE_REQUIRED);
        $ctype = new external_value(PARAM_RAW, 'The competency type id', VALUE_OPTIONAL);
        $programid = new external_value(PARAM_RAW, 'The program id', VALUE_OPTIONAL,0);
        $offeringid = new external_value(PARAM_INT, 'The offering id', VALUE_OPTIONAL,0);
        $sectors = new external_value(PARAM_RAW, 'Selected sectors', VALUE_OPTIONAL,0);
        $level = new external_value(PARAM_RAW, 'Selected level', VALUE_OPTIONAL,0);
        $params = array(
            'query' => $query,
            'type' => $type,
            'ctype' => $ctype,
            'programid' => $programid,
            'offeringid' => $offeringid,
            'sectors' => $sectors,
            'level' => $level,
           
            
        );
        return new external_function_parameters($params);
    }
    public static function ajaxdatalist($query,$type,$ctype,$programid = 0,$offeringid = 0, $sectors = 0, $level = 0) {
        global $PAGE;
        $params = array(    
            'query' => $query,
            'type' => $type,
            'ctype' => $ctype,
            'programid' => $programid,
            'offeringid' => $offeringid,
            'sectors' => $sectors,
            'level' => $level,

        );
        $params = self::validate_parameters(self::ajaxdatalist_parameters(), $params);

        switch($params['type']) {
            case 'program_competencylevel':

                if (str_replace('"', "", $params['ctype']) == 'All' ||  $params['ctype'] == 'All' ) {
                  $data = (new local_trainingprogram\local\trainingprogram)->get_listof_competencies_for_filters($params['query'],$params['programid'],$params['ctype'],$params['offeringid']);
                } else {
                   $data = (new local_trainingprogram\local\trainingprogram)->get_listof_competencies($params['query'],$params['programid'],$params['ctype'],$params['offeringid'], $params['level']);
                }

            break;
            case 'programusers':
                $data = (new local_trainingprogram\local\trainingprogram)->get_listof_trainerusers($params['query'],$params['programid'],$params['ctype'],$params['offeringid']);
            break;
            case 'loginasusers':
                $data = (new local_trainingprogram\local\trainingprogram)->get_listof_loginasusers($params['query'],$params['programid'],$params['ctype'],$params['offeringid']);
            break;
            case 'orgofficial':
                $data = (new local_trainingprogram\local\trainingprogram)->get_listof_orgofficial($params['query'],$params['programid'],$params['ctype'],$params['offeringid']);
            break;
            case 'officials':
                $data = (new local_trainingprogram\local\trainingprogram)->get_listof_officials($params['query'],$params['programid'],$params['ctype'],$params['offeringid']);
            break;
            case 'program_competency':
                $data = (new local_trainingprogram\local\trainingprogram)->get_listof_competencytypes($params['query'], $params['level']);
            break;
            case 'levels':
                $data = (new local_trainingprogram\local\trainingprogram)->get_listof_levels($params['query']);
            break;
            case 'allentities':
                $data = (new local_trainingprogram\local\trainingprogram)->get_listof_entities($params['query'],$params['ctype']);
            break;


        }
        return ['status' => true, 'data' => $data];
    }
    public static function ajaxdatalist_returns() {
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

     public static function cardviewprograms_parameters() {
        return new external_function_parameters([

            'isArabic' => new external_value(PARAM_RAW, 'isArabic', VALUE_DEFAULT),
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
    public static function cardviewprograms($isArabic = NULL, $options=false, $dataoptions=false, $offset = 0, $limit = 0,$contextid=1, $filterdata=false) {
        global $OUTPUT, $CFG, $DB,$USER,$PAGE;
        $sitecontext = context_system::instance();
        $PAGE->set_context($sitecontext);

        $params = self::validate_parameters(
            self::cardviewprograms_parameters(),
            [   'isArabic' => $isArabic,
                'options' => $options,
                'dataoptions' => $dataoptions,
                'offset' => $offset,
                'limit' => $limit,
                'contextid' => $contextid,
                'filterdata' => $filterdata,
            ]
        );
        $settings = external_settings::get_instance();
        $offset = $params['offset'];
        $limit = $params['limit'];
        $filtervalues = json_decode($filterdata);
        $stable = new \stdClass();
        $stable->thead = false;
        $stable->start = $offset;
        $stable->length = $limit;
	    $stable->mlang = ($settings->get_lang()) ?  $settings->get_lang() :(($isArabic)?(($isArabic == 'true') ? 'ar' :'en') : null);       
        $data = (new local_trainingprogram\local\trainingprogram)->card_view_programs($stable, $filtervalues);

        $numberofprograms = $data['totalprograms'];
        $totalcount = $data['totalprograms'];
        if($totalcount > 0){
            $nodata=true;
        } else {
            $nodata=false;
        }

        if($numberofprograms > 6){
            $noloadmore = 'test';
        }
        if($numberofprograms == $data['length']){
            $noloadmore = false;
        }

        return [
            'programs' => $data['programs'],
            'totalcount' => $totalcount,
            'length' => $totalcount,
            'numberofprograms' => $numberofprograms,
            'filterdata' => $filterdata,
            'records' =>$data,
            'options' => $options,
            'dataoptions' => $dataoptions,
            'url' => $CFG->wwwroot,
            'nodata' => $nodata,
            'noloadmore' => $noloadmore,
            
        ];
    }
    public static function cardviewprograms_returns() {
        return new external_single_structure([
            'programs' => new external_multiple_structure(
                new external_single_structure(
                    array(
                        'trainingid' => new external_value(PARAM_INT, 'id'),
                        'programname' => new external_value(PARAM_TEXT, 'programname'),
                        'sellingprice' => new external_value(PARAM_TEXT, 'sellingprice'),
                        'description' => new external_value(PARAM_RAW, 'description'),
                        'courseid' => new external_value(PARAM_INT, 'courseid'),
                        'capitalmarket' => new external_value(PARAM_RAW, 'capitalmarket'),
                        'finance' => new external_value(PARAM_RAW, 'finance'),
                        'insurance' => new external_value(PARAM_RAW, 'insurance'),
                        'banking' => new external_value(PARAM_RAW, 'banking'),
                        'image' => new external_value(PARAM_RAW, 'image',VALUE_OPTIONAL),
                        'durationindays' => new external_value(PARAM_INT, 'durationindays'),
                        'isenrolled' => new external_value(PARAM_BOOL, 'isenrolled'),
                        'sectors' => new external_multiple_structure(
                            new external_single_structure(
                                array(
                                    'sectorid' => new external_value(PARAM_INT, 'id'),
                                    'sectorname' => new external_value(PARAM_TEXT, 'name'),
                                )
                            )
                        ),
                    )
                )
            ),
          'nodata' => new external_value(PARAM_BOOL, 'No data flag'),
          'options' => new external_value(PARAM_RAW, 'The paging data for the service'),
          'url' => new external_value(PARAM_RAW, 'url'),
          'dataoptions' => new external_value(PARAM_RAW, 'The data for the service'),
          'totalcount' => new external_value(PARAM_INT, 'total number of challenges in result set', VALUE_OPTIONAL),
          'numberofprograms' => new external_value(PARAM_INT, 'total number of challenges in result set', VALUE_OPTIONAL),
          'filterdata' => new external_value(PARAM_RAW, 'total number of challenges in result set'),
          'length' => new external_value(PARAM_RAW, 'total number of challenges in result set'),
          'records' => new external_single_structure(
                  array(
                      'programs' => new external_multiple_structure(
                        new external_single_structure(
                            array(
                                'trainingid' => new external_value(PARAM_INT, 'id'),
                                'programname' => new external_value(PARAM_RAW, 'programname'),
                                'sellingprice' => new external_value(PARAM_RAW, 'sellingprice'),
                                'actualprice' => new external_value(PARAM_RAW, 'actualprice'),
                                'description' => new external_value(PARAM_RAW, 'description'),
                                'courseid' => new external_value(PARAM_INT, 'courseid'),
                                'capitalmarket' => new external_value(PARAM_RAW, 'capitalmarket'),
                                'finance' => new external_value(PARAM_RAW, 'finance'),
                                'insurance' => new external_value(PARAM_RAW, 'insurance'),
                                'banking' => new external_value(PARAM_RAW, 'banking'),
                                'imageurl' => new external_value(PARAM_RAW, 'imageurl',VALUE_OPTIONAL),
                                'durationindays' => new external_value(PARAM_RAW, 'durationindays'),
                                'actionview' => new external_value(PARAM_RAW, 'actionview'),
                                'hours' => new external_value(PARAM_RAW, 'hours'),
                                'programdetailsurl' => new external_value(PARAM_URL, 'hours'),
                            )
                        )
                    ),
                   
                    'request_view' => new external_value(PARAM_INT, 'request_view', VALUE_OPTIONAL),
                    'nocourses' => new external_value(PARAM_BOOL, 'nocourses', VALUE_OPTIONAL),
                    'noloadmore' => new external_value(PARAM_BOOL, 'noloadmore', VALUE_OPTIONAL),
                    'totalprograms' => new external_value(PARAM_INT, 'totalprograms', VALUE_OPTIONAL),
                    'length' => new external_value(PARAM_INT, 'length', VALUE_OPTIONAL),
                )
            )
        ]);
    }

    public static function deleteshedule_parameters(){
        return new external_function_parameters(
            array(
                'sheduleid' => new external_value(PARAM_INT,'Shedule id',0),
            )
        );
    }
    public static  function deleteshedule($sheduleid){
        global $CFG;
        global $USER;
        global $DB;
        $params=self::validate_parameters(
            self::deleteshedule_parameters(),
            array('sheduleid'=>$sheduleid)
        );
        $systemcontext = context_system::instance();
        self::validate_context($systemcontext);
       if ($sheduleid) {
           $event = \local_trainingprogram\event\tpofferings_deleted::create(array( 'context'=>$systemcontext, 'objectid' =>$sheduleid));
           $event->trigger();
           (new local_trainingprogram\local\trainingprogram)->remove_schedule_program($sheduleid);
        } else {
          throw new moodle_exception('Error in submission');
        }
        return true;    
    }
    public static function deleteshedule_returns() {
        return new external_value(PARAM_BOOL, 'return');
    } 

    public static function deleteprogram_parameters(){
        return new external_function_parameters(
            array(
                'programid' => new external_value(PARAM_INT,'Program id',0),
            )
        );
    }
    public static  function deleteprogram($programid){
        global $CFG;
        global $USER;
        global $DB;
        $params=self::validate_parameters(
            self::deleteprogram_parameters(),
            array('programid'=>$programid)
        );
        $systemcontext = context_system::instance();
        self::validate_context($systemcontext);
       if ($programid) {
           (new local_trainingprogram\local\trainingprogram)->remove_training_program($programid);
        } else {
          throw new moodle_exception('Error in submission');
        }
        return true;    
    }
    public static function deleteprogram_returns() {
        return new external_value(PARAM_BOOL, 'return');
    } 

     public static function viewmyprograms_parameters() {
        return new external_function_parameters([
            'options' => new external_value(PARAM_RAW, 'The paging data for the service'),
            'dataoptions' => new external_value(PARAM_RAW, 'The data for the service'),
            'offset' => new external_value(PARAM_INT, 'Number of items to skip from the begging of the result set',
                VALUE_DEFAULT, 0),
            'limit' => new external_value(PARAM_INT, 'Maximum number of results to return',
                VALUE_DEFAULT, 0),
            'contextid' => new external_value(PARAM_INT, 'contextid',
                VALUE_DEFAULT, 1),
            'filterdata' => new external_value(PARAM_RAW, 'filters applied'),
        ]);
    }
    public static function viewmyprograms($options, $dataoptions, $offset = 0, $limit = 0, $contextid = 1, $filterdata) {
        global $DB, $PAGE, $CFG;
        require_login();
        $params = self::validate_parameters(
            self::viewprograms_parameters(),
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
        $data = (new local_trainingprogram\local\trainingprogram)->get_listof_my_programs($stable, $filtervalues);
        $totalcount = $data['totalprograms'];
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
    public static function viewmyprograms_returns() {
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
                                'sno' => new external_value(PARAM_INT, 'sno'),
                                'trainingid' => new external_value(PARAM_INT, 'id'),
                                'tainingprogramname' => new external_value(PARAM_RAW, 'tainingprogramname'),
                                'availablefrom' => new external_value(PARAM_RAW, 'availablefrom'),
                                'availableto' => new external_value(PARAM_RAW, 'availableto'),
                                'published' => new external_value(PARAM_RAW, 'published'),
                                'courseid' => new external_value(PARAM_INT, 'courseid'),
                                'enrolledusers' => new external_value(PARAM_INT, 'enrolledusers'),
                                'competencies' => new external_value(PARAM_RAW, 'competencies'),
                                'certificateid' => new external_value(PARAM_RAW, 'certificate', VALUE_OPTIONAL),
                                
                            )
                        )
                    ),

                    'completedprograms' => new external_multiple_structure(
                        new external_single_structure(
                            array(
                                'sno' => new external_value(PARAM_INT, 'sno'),
                                'trainingid' => new external_value(PARAM_INT, 'id'),
                                'tainingprogramname' => new external_value(PARAM_RAW, 'tainingprogramname'),
                                'availablefrom' => new external_value(PARAM_RAW, 'availablefrom'),
                                'availableto' => new external_value(PARAM_RAW, 'availableto'),
                                'published' => new external_value(PARAM_RAW, 'published'),
                                'courseid' => new external_value(PARAM_INT, 'courseid'),
                                'enrolledusers' => new external_value(PARAM_INT, 'enrolledusers'),
                                'competencies' => new external_value(PARAM_RAW, 'competencies'),
                                'certificateid' => new external_value(PARAM_RAW, 'certificate', VALUE_OPTIONAL),
                                
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

     public static function viewdashboardprograms_parameters() {
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
    public static function viewdashboardprograms($options, $dataoptions, $offset = 0, $limit = 0, $contextid, $filterdata) {
        global $DB, $PAGE, $CFG;
        require_login();
        $params = self::validate_parameters(
            self::viewprograms_parameters(),
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
        $data = (new local_trainingprogram\local\trainingprogram)->get_listof_dashboard_programs($stable, $filtervalues);
        $totalcount = $data['totalprograms'];
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
    public static function viewdashboardprograms_returns() {
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
                                'trainingid' => new external_value(PARAM_INT, 'id'),
                                'programname' => new external_value(PARAM_RAW, 'programname'),
                                'availablefrom' => new external_value(PARAM_RAW, 'availablefrom'),
                                'availableto' => new external_value(PARAM_RAW, 'availableto'),
                                'description' => new external_value(PARAM_RAW, 'description'),
                                'courseid' => new external_value(PARAM_INT, 'courseid'),
                                'imageurl' => new external_value(PARAM_RAW, 'imageurl'),
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

    public static function publishprogram_parameters(){
        return new external_function_parameters(
            array(
                'programid' => new external_value(PARAM_INT,'Program id',0),
            )
        );
    }
    public static  function publishprogram($programid){
        global $CFG;
        global $USER;
        global $DB;
        $params=self::validate_parameters(
            self::publishprogram_parameters(),
            array('programid'=>$programid)
        );
        $systemcontext = context_system::instance();
        self::validate_context($systemcontext);
       if ($programid) {
           (new local_trainingprogram\local\trainingprogram)->publish_current_program($programid);
        } else {
          throw new moodle_exception('Error in submission');
        }
        return true;    
    }
    public static function publishprogram_returns() {
        return new external_value(PARAM_BOOL, 'return');
    } 

     public static function unpublishprogram_parameters(){
        return new external_function_parameters(
            array(
                'programid' => new external_value(PARAM_INT,'Program id',0),
            )
        );
    }
    public static  function  unpublishprogram($programid){
        global $CFG;
        global $USER;
        global $DB;
        $params=self::validate_parameters(
            self:: unpublishprogram_parameters(),
            array('programid'=>$programid)
        );
        $systemcontext = context_system::instance();
        self::validate_context($systemcontext);
       if ($programid) {
           (new local_trainingprogram\local\trainingprogram)-> unpublish_current_program($programid);
        } else {
          throw new moodle_exception('Error in submission');
        }
        return true;    
    }
    public static function  unpublishprogram_returns() {
        return new external_value(PARAM_BOOL, 'return');
    }

    public static function viewprogramenrolledusers_parameters() {
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

    public static function viewprogramenrolledusers($options, $dataoptions, $offset = 0, $limit = 0, $contextid, $filterdata) {
        global $DB, $PAGE, $CFG;
        require_login();
         $systemcontext = context_system::instance();
        $params = self::validate_parameters(
            self::viewprogramenrolledusers_parameters(),
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
        $data = (new local_trainingprogram\local\trainingprogram)->get_listof_programenrolledusers($stable, $filtervalues, $dataoptions);
        $totalcount = $data['totalusers'];
        return [
            'totalcount' => $totalcount,
            'length' => $totalcount,
            'programname' => $data['programname'],
            'completiontabs' => (is_siteadmin() || has_capability('local/organization:manage_organizationofficial',$systemcontext)) ? true : false,
            'hideofferingstatustraoff' => (!is_siteadmin() && has_capability('local/organization:manage_trainingofficial',$systemcontext)) ? true : false,
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

    public static function viewprogramenrolledusers_returns() {
        return new external_single_structure([
          'options' => new external_value(PARAM_RAW, 'The paging data for the service'),
          'url' => new external_value(PARAM_RAW, 'url'),
          'nodata' => new external_value(PARAM_BOOL, 'nodata'),
          'dataoptions' => new external_value(PARAM_RAW, 'The data for the service'),
          'totalcount' => new external_value(PARAM_INT, 'total number of challenges in result set'),
          'filterdata' => new external_value(PARAM_RAW, 'total number of challenges in result set'),
          'length' => new external_value(PARAM_RAW, 'total number of challenges in result set'),
          'programname' => new external_value(PARAM_RAW, 'name of the program'),
          'completiontabs' => new external_value(PARAM_RAW, 'completiontabs'),
          'hideofferingstatustraoff' => new external_value(PARAM_RAW, 'hideofferingstatustraoff'),
          'records' => new external_single_structure(
                array(
                        'hascourses' => new external_multiple_structure(
                        new external_single_structure(
                            array(
                                'id' => new external_value(PARAM_INT, 'id'),
                                'fullname' => new external_value(PARAM_RAW, 'fullname'),
                                'email' => new external_value(PARAM_RAW, 'email'),
                                'id_number' => new external_value(PARAM_RAW, 'id_number'),
                                'phone' => new external_value(PARAM_RAW, 'phone'),
                                'offeringid' => new external_value(PARAM_INT, 'offeringid'),
                                'roleid' => new external_value(PARAM_INT, 'roleid'),
                                'rolename' => new external_value(PARAM_RAW, 'rolename'),
                                'offeringcode' => new external_value(PARAM_RAW, 'offeringcode'),
                                'enrolledat' => new external_value(PARAM_RAW, 'enrolledat'),
                                'roleshortname' => new external_value(PARAM_RAW, 'roleshortname'),
                                'unassignuser' => new external_value(PARAM_RAW, 'unassignuser'),
                                'programid' => new external_value(PARAM_INT, 'programid'),
                                'organization' => new external_value(PARAM_RAW, 'organization'),
                                'offering_status' => new external_value(PARAM_RAW, 'offering_status'),
                                'certid' => new external_value(PARAM_RAW, 'certid'),
                                'certificateview' => new external_value(PARAM_RAW, 'certificateview'),
                                'programprice' => new external_value(PARAM_INT, 'programprice'),
                                'replacementfee' => new external_value(PARAM_INT, 'replacementfee'),
                                'remainingdays' => new external_value(PARAM_INT, 'remainingdays'),
                                'replacebuttonview' => new external_value(PARAM_RAW, 'replacebuttonview'),
                                'cancelbuttonview' => new external_value(PARAM_RAW, 'cancelbuttonview'),
                                'absentviewaction' => new external_value(PARAM_RAW, 'absentviewaction'),
                                'programname' => new external_value(PARAM_RAW, 'programname'),
                                'currentuserisadmin' => new external_value(PARAM_INT, 'currentuserisadmin'),
                                'programdate' => new external_value(PARAM_INT, 'programdate'),
                                'isnotelearningmethod' => new external_value(PARAM_RAW, 'isnotelearningmethod'),
                                'adminenrolled' => new external_value(PARAM_INT, 'adminenrolled'),
                                'productid' => new external_value(PARAM_INT, 'productid'),
                                'orgofficialenrolled' => new external_value(PARAM_INT, 'orgofficialenrolled'),
                                'enrolledrole' => new external_value(PARAM_RAW, 'enrolledrole', VALUE_OPTIONAL),
                                'currentuserorgoff' => new external_value(PARAM_INT, 'currentuserorgoff', VALUE_OPTIONAL),
                                'sellingprice' => new external_value(PARAM_INT, 'sellingprice'),
                                'disableallactions' => new external_value(PARAM_RAW, 'disableallactions', VALUE_OPTIONAL),
                                'istrainer' => new external_value(PARAM_RAW, 'istrainer', VALUE_OPTIONAL),     
                                'iswaitingforapproval' => new external_value(PARAM_RAW, 'iswaitingforapproval', VALUE_OPTIONAL), 
                                'isfinaciallyclosed' => new external_value(PARAM_RAW, 'isfinaciallyclosed', VALUE_OPTIONAL), 
                                'enrolltype' => new external_value(PARAM_INT, 'enrolltype', VALUE_OPTIONAL), 
                                'enrolledby' => new external_value(PARAM_RAW, 'enrolledby', VALUE_OPTIONAL), 
                                'cangenerateinvoice'=> new external_value(PARAM_INT, 'cangenerateinvoice', VALUE_OPTIONAL), 
                                'hasofferingcompleted'=> new external_value(PARAM_INT, 'hasofferingcompleted', VALUE_OPTIONAL), 
                            )
                        )
                    ),                   
                    'request_view' => new external_value(PARAM_INT, 'request_view', VALUE_OPTIONAL),
                    'totalusers' => new external_value(PARAM_INT, 'totalhalls', VALUE_OPTIONAL),
                    'length' => new external_value(PARAM_INT, 'length', VALUE_OPTIONAL),
                    'programname' => new external_value(PARAM_TEXT, 'programname', VALUE_OPTIONAL),
                )
            )
        ]);
    }


    public static function viewptrainers_parameters() {
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

    public static function viewptrainers($options, $dataoptions, $offset = 0, $limit = 0, $contextid, $filterdata) {
        global $DB, $PAGE, $CFG;
        require_login();
        $params = self::validate_parameters(
            self::viewptrainers_parameters(),
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
        $data = (new local_trainingprogram\local\trainingprogram)->get_listof_traineeusers($stable, $filtervalues, $dataoptions);
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

    public static function viewptrainers_returns() {
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

                                   'assignuser' => new external_value(PARAM_INT, 'assignuser'),
                                  'trainerid' => new external_value(PARAM_RAW, 'trainerid'),
                                  'traineeid' => new external_value(PARAM_RAW, 'traineeid'),
                                  'programoffusers' => new external_value(PARAM_RAW, 'programoffusers'),
                                  'traineeusers' => new external_value(PARAM_INT, 'traineeusers'),
                                  'programid' => new external_value(PARAM_INT, 'programid'),
                                  'enrollid' => new external_value(PARAM_INT, 'enrollid'),
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

public static function unassignuser_parameters(){
        return new external_function_parameters(
            array(
                'programid' => new external_value(PARAM_INT,'Program id',0),
                'offeringid' => new external_value(PARAM_INT,'Program id',0),
                'userid' => new external_value(PARAM_INT,'User id',0),
                'roleid' => new external_value(PARAM_INT,'Role id',0),
            )
        );
    }
    public static  function unassignuser($programid,$offeringid,$userid,$roleid){
        global $CFG;
        global $USER;
        global $DB;
        $params=self::validate_parameters(
            self::unassignuser_parameters(),
            array('programid'=>$programid,'offeringid'=>$offeringid,'userid'=>$userid,'roleid'=>$roleid)
        );
        $systemcontext = context_system::instance();
        self::validate_context($systemcontext);
       if (!empty($programid) && !empty($offeringid) && !empty($userid)) {
           (new local_trainingprogram\local\trainingprogram)->unassignuser($programid,$offeringid,$userid,$roleid);
        } else {
          throw new moodle_exception('Error in submission');
        }
        return true;    
    }
    public static function unassignuser_returns() {
        return new external_value(PARAM_BOOL, 'return');
    } 


     public static function otherprogramscourseview_parameters() {
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

    public static function otherprogramscourseview($options, $dataoptions, $offset = 0, $limit = 0, $contextid, $filterdata) {
        global $DB, $PAGE, $CFG;
        require_login();
        $params = self::validate_parameters(
            self::otherprogramscourseview_parameters(),
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
        $data = (new local_trainingprogram\local\trainingprogram)->get_others_program_course_overview($stable, $filtervalues, $dataoptions);
        $totalcount = $data['totalofferings'];
        $programname = $data['programname'];
        return [
            'totalcount' => $totalcount,
            'length' => $totalcount,
            'programname' => $programname,
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

    public static function otherprogramscourseview_returns() {
        return new external_single_structure([
          'options' => new external_value(PARAM_RAW, 'The paging data for the service'),
          'url' => new external_value(PARAM_RAW, 'url'),
          'nodata' => new external_value(PARAM_BOOL, 'nodata'),
          'dataoptions' => new external_value(PARAM_RAW, 'The data for the service'),
          'totalcount' => new external_value(PARAM_INT, 'total number of challenges in result set'),
          'filterdata' => new external_value(PARAM_RAW, 'total number of challenges in result set'),
          'length' => new external_value(PARAM_RAW, 'total number of challenges in result set'),
          'programname' => new external_value(PARAM_RAW, 'programname'),
          'records' => new external_single_structure(
                  array(
                         'hascourses' => new external_multiple_structure(
                          new external_single_structure(
                              array(

                                  'offeringid' => new external_value(PARAM_INT, 'offeringid'),
                                  'coid' => new external_value(PARAM_INT, 'coid'),
                                  'code' => new external_value(PARAM_RAW, 'code'),
                                  'offeringmethod' => new external_value(PARAM_RAW, 'offeringmethod'),
                                  'programid' => new external_value(PARAM_INT, 'programid'),
                                  'seatingcapacity' => new external_value(PARAM_RAW, 'seatingcapacity'),
                                  'totalseats' => new external_value(PARAM_INT, 'totalseats'),
                                  'availableseats' => new external_value(PARAM_INT, 'availableseats'),
                                  'buildingname' => new external_value(PARAM_RAW, 'buildingname'),
                                  'hallname' => new external_value(PARAM_RAW, 'hallname'),
                                  'datedisplay' => new external_value(PARAM_RAW, 'datedisplay'),
                                  'halldisplayaction' => new external_value(PARAM_RAW, 'halldisplayaction'),
                                  'action' => new external_value(PARAM_RAW, 'action'),
                                  'city' => new external_value(PARAM_RAW, 'city'),
                                  'programname' => new external_value(PARAM_RAW, 'programname'),
                                  'sellingprice' => new external_value(PARAM_RAW, 'sellingprice'),
                                  'seatingcapacity' => new external_value(PARAM_RAW, 'seatingcapacity'),
                                  'startdate' => new external_value(PARAM_RAW, 'startdate'),
                                  'enddate' => new external_value(PARAM_RAW, 'enddate'),
                                  'starttime' => new external_value(PARAM_RAW, 'starttime'),
                                  'endtime' => new external_value(PARAM_RAW, 'endtime'),
                                  'bookseats'=> new external_value(PARAM_RAW, 'bookseats', VALUE_OPTIONAL),
                                  'orderseats' => new external_value(PARAM_INT, 'orderseats'),
                                  'approvalseats' => new external_value(PARAM_INT, 'approvalseats'),
                                  'enavailableseats' => new external_value(PARAM_INT, 'enavailableseats'),
                                  'enrolledseats' => new external_value(PARAM_INT, 'enrolledseats'),
                                  'courselanguage' => new external_value(PARAM_RAW, 'courselanguage'),
                                
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

     public static function currentofferingsdisplay_parameters() {
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
 
    public static function currentofferingsdisplay($options, $dataoptions, $offset = 0, $limit = 0, $filterdata) {
        global $OUTPUT, $CFG, $DB,$USER,$PAGE;
        $reportid = $DB->get_field('block_learnerscript', 'id', ['type' => 'meetingparticipants']);
        // print_r($reportid);exit;

        $sitecontext = context_system::instance();
        $PAGE->set_context($sitecontext);
        $params = self::validate_parameters(
            self::currentofferingsdisplay_parameters(),
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
        $stable->start = $offset;
        $stable->length = $limit;
        $offerings = (new local_trainingprogram\local\trainingprogram)->display_lisfof_current_offerings($stable,$filtervalues);
        return [
            'totalcount' => $offerings['totalofferings'],
            'records' => $offerings['currentofferings'],
            'options' => $options,
            'dataoptions' => $dataoptions,
            'filterdata' => $filterdata,
            'programid' => $filtervalues->programid,
            'nodata' => get_string('noofferings','local_trainingprogram'),
            'url'=>$CFG->wwwroot,


        ];
    }

    /**
     * Returns description of method result value.
     */
    public static function  currentofferingsdisplay_returns() {
        return new external_single_structure([
            'options' => new external_value(PARAM_RAW, 'The paging data for the service'),
            'programid' => new external_value(PARAM_INT, 'Program id'),
            'dataoptions' => new external_value(PARAM_RAW, 'The data for the service'),
            'totalcount' => new external_value(PARAM_INT, 'total number of competencies in result set'),
            'filterdata' => new external_value(PARAM_RAW, 'The data for the service'),
            'nodata' => new external_value(PARAM_RAW, 'The data for the service'),
            'records' => new external_multiple_structure(
                new external_single_structure(
                    array(
                      'coid' => new external_value(PARAM_INT, 'coid'),
                      'offeringid' => new external_value(PARAM_INT, 'offeringid'),
                      'tainingprogramname' => new external_value(PARAM_RAW, 'tainingprogramname'),
                      'code' => new external_value(PARAM_RAW, 'code'),
                      'programid' => new external_value(PARAM_INT, 'programid'),
                      'availableseats' => new external_value(PARAM_RAW, 'availableseats'),
                      'buildingname' => new external_value(PARAM_RAW, 'buildingname'),
                      'hallname' => new external_value(PARAM_RAW, 'hallname'),
                      'city' => new external_value(PARAM_RAW, 'city'),
                      'enrolled' => new external_value(PARAM_INT, 'enrolled', VALUE_OPTIONAL),
                      'sellingprice' => new external_value(PARAM_RAW, 'sellingprice'),
                      'startdate' => new external_value(PARAM_RAW, 'startdate'),
                      'type' => new external_value(PARAM_RAW, 'type'),
                      'time' => new external_value(PARAM_RAW, 'time'),
                      'offeringmethod' => new external_value(PARAM_RAW, 'offeringmethod'),
                      'trainerprovider' => new external_value(PARAM_RAW, 'trainerprovider'),
                      'trainerproviderlogo' => new external_value(PARAM_RAW, 'trainerproviderlogo'),
                      'trainertype'=> new external_value(PARAM_RAW, 'trainertype'),
                      'datedisplay' => new external_value(PARAM_RAW, 'datedisplay'),
                      'halldisplayaction' => new external_value(PARAM_RAW, 'halldisplayaction'),
                      'privateorg' => new external_value(PARAM_RAW, 'privateorg'),
                      'orgname' => new external_value(PARAM_RAW, 'orgname'),
                      'hastrainernamelistmore' => new external_value(PARAM_RAW, 'hastrainernamelistmore'),
                      'limitedtrainers' => new external_value(PARAM_TEXT, 'limitedtrainers'),
                      'trainername' => new external_value(PARAM_RAW, 'trainername'),
                      'sessiondatadisplay' => new external_value(PARAM_RAW, 'sessiondatadisplay'),
                      'enddate' => new external_value(PARAM_RAW, 'enddate'),
                      'endtime' => new external_value(PARAM_RAW, 'endtime'),
                      'maplocation' => new external_value(PARAM_RAW, 'maplocation'),
                      'assignurl' => new external_value(PARAM_RAW, 'assignurl'),
                      'published' => new external_value(PARAM_RAW, 'published'),
                      'currentuser' => new external_value(PARAM_RAW, 'currentuser'),
                      'costtype' => new external_value(PARAM_INT, 'costtype'),
                      'productid' => new external_value(PARAM_INT, 'productid'),
                      'cancelledstatus' => new external_value(PARAM_INT, 'cancelledstatus'),
                      'iscancelled' => new external_value(PARAM_RAW, 'iscancelled'),
                      'cancelledstatustext' => new external_value(PARAM_RAW, 'cancelledstatustext'),
                      'offeringcancelaction' => new external_value(PARAM_RAW, 'offeringcancelaction'),
                      //'enrolltraineeactionview' => new external_value(PARAM_RAW, 'enrolltraineeactionview'),
                      'offeringdeleteaction' => new external_value(PARAM_RAW, 'offeringdeleteaction'),
                      'deleteaction' => new external_value(PARAM_RAW, 'deleteaction'),
                      'offeringenroll_edit_action' => new external_value(PARAM_RAW, 'offeringenroll_edit_action'),
                      'issiteadmin' => new external_value(PARAM_RAW, 'issiteadmin'),
                      'offeringhasenrollments' => new external_value(PARAM_RAW, 'offeringhasenrollments'),
                      'offcourselanguage' => new external_value(PARAM_RAW, 'offcourselanguage'),
                      'todoactivities' => new external_value(PARAM_RAW, 'todoactivities'),
                      'evaluationmethods' => new external_value(PARAM_RAW, 'evaluationmethods'),
                      'isfinancialmanager' => new external_value(PARAM_RAW,'isfinancialmanager',VALUE_OPTIONAL),
                      'isfinanciallyclosed' => new external_value(PARAM_RAW,'isfinanciallyclosed',VALUE_OPTIONAL),
                      'canupdatefinanciallstatus' => new external_value(PARAM_RAW,'canupdatefinanciallstatus',VALUE_OPTIONAL),
                      'onlyadmin' => new external_value(PARAM_RAW,'onlyadmin',VALUE_OPTIONAL),
                      'hasenrollments' => new external_value(PARAM_INT,'hasenrollments',VALUE_OPTIONAL),
                      'cancelledrequestpending' => new external_value(PARAM_BOOL,'cancelledrequestpending',VALUE_OPTIONAL),
                      'hastagreement' => new external_value(PARAM_BOOL,'hastagreement',VALUE_OPTIONAL),
                      'agreementurl' => new external_value(PARAM_RAW,'agreementurl',VALUE_OPTIONAL),
                      'sessiondata' => new external_multiple_structure(
                        new external_single_structure(
                            array(
                            'cid' => new external_value(PARAM_INT, 'Session No'),
                            'sessiondate' => new external_value(PARAM_RAW, 'sessiondate'),
                            'url' => new external_value(PARAM_RAW, 'url'),
                            'filters' => new external_value(PARAM_RAW, 'filters'),
                            'attendanceurl' => new external_value(PARAM_RAW, 'attendanceurl'),
                            'virtual' => new external_value(PARAM_BOOL, 'virtual')
                            )
                            )
                        ), '', VALUE_OPTIONAL
                    )
                )
            ), '', VALUE_OPTIONAL,
        ]);
    } 
    public static function programcards_parameters() {
        return new external_function_parameters([
                'sector' => new external_value(PARAM_RAW, 'The paging data for the service')
        ]);
    }
 
    public static function programcards($sector) {
        global $OUTPUT, $CFG, $DB,$USER,$PAGE;
        $sitecontext = context_system::instance();
        $PAGE->set_context($sitecontext);
        $params = self::validate_parameters(
            self::programcards_parameters(),
            [
                'sector' => $sector
            ]
        );
        $pagination = new stdClass();
        $pagination->start = 0;
        $pagination->length = 6;
        $filterdata = new stdClass();
        $filterdata->sector = $params['sector'];
        $programs = (new local_trainingprogram\local\trainingprogram)->get_programdata($pagination, $filterdata);
    
        return $programs;
    }

    /**
     * Returns description of method result value.
     */
    public static function  programcards_returns() {
        return  new external_multiple_structure(
                new external_single_structure(
                    array(
                        'id' => new external_value(PARAM_INT, 'Id'),
                        'title' => new external_value(PARAM_TEXT, 'Program title'),
                        'description' => new external_value(PARAM_RAW, 'program description'),
                        'sellingprice' => new external_value(PARAM_INT, 'Selling price', VALUE_OPTIONAL),
                        'imageurl'=> new external_value(PARAM_RAW, 'Program image', VALUE_OPTIONAL),
                        'capitalmarket' => new external_value(PARAM_BOOL, 'sector', VALUE_OPTIONAL),
                        'finance' => new external_value(PARAM_BOOL, 'sector', VALUE_OPTIONAL),
                        'insurance' => new external_value(PARAM_BOOL, 'sector', VALUE_OPTIONAL),
                        'banking' => new external_value(PARAM_BOOL, 'sector', VALUE_OPTIONAL),
                        'hours' =>new external_value(PARAM_INT, 'Hours', VALUE_OPTIONAL),
                        'durationindays' => new external_value(PARAM_INT, 'Program duration', VALUE_OPTIONAL),
                    )
                )
            );
    } 


    // Hall reservation 
    public static function tphall_data_parameters() {
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

      public static function tphall_data($options, $dataoptions, $offset = 0, $limit = 0, $filterdata) {
        global $DB, $PAGE;       
        // Parameter validation.
        $contextid = 1;
        $params = self::validate_parameters(
            self::tphall_data_parameters(),
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
    
        return [
            'totalcount' => $totalcount,
            'length' => $totalcount,
            'filterdata' => $filterdata,
            'records' =>$data,
            'options' => $options,
            'dataoptions' => $dataoptions,
        ];
      }

      public static function tphall_data_returns() {
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
                                    'slid' => new external_value(PARAM_RAW, 'slid'),
                                    'start' => new external_value(PARAM_RAW, 'start'),
                                    'end' => new external_value(PARAM_RAW, 'end'),
                                    'start_time' => new external_value(PARAM_RAW, 'start_time'),
                                    'end_time' => new external_value(PARAM_RAW, 'end_time'),
                                    'hallid' => new external_value(PARAM_RAW, 'hallid'),
                                    'typeid'=>new external_value(PARAM_RAW,'typeid'),
                                    'examdate'=>new external_value(PARAM_RAW,'examdate'),
                                    'booked'=>new external_value(PARAM_BOOL,'booked'),
                                    'examname'=>new external_value(PARAM_RAW,'examname'),
                                    'availableseats'=>new external_value(PARAM_RAW,'availableseats'),
                                    'type'=>new external_value(PARAM_RAW,'type'),                                      
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
    public static function tprogram_slotbooking_parameters() {
        return new external_function_parameters(
            array(
                'hallid' => new external_value(PARAM_INT, 'hallid', 0),
                'examdate' => new external_value(PARAM_RAW, 'examdate', 0),
                'start' => new external_value(PARAM_RAW, 'start', 0),
                'end' => new external_value(PARAM_RAW, 'end', 0),
                'typeid' => new external_value(PARAM_INT, 'typeid', 0),
                'type' => new external_value(PARAM_TEXT, 'type', ''),
                'seats' => new external_value(PARAM_INT, 'seats', 0),
                )
        );
    }
    public static function tprogram_slotbooking($hallid, $examdate, $start, $end, $typeid, $type, $seats) {
        global $DB, $USER;
        require_login();
        $params = self::validate_parameters(self::tprogram_slotbooking_parameters(),
                        ['hallid' => $hallid, 'examdate' => $examdate, 'start' => $start, 'end' => $end, 'typeid' => $typeid, 'type' => $type, 'seats' => $seats]);
        $row = []; 
        $row['hallid'] = $hallid;
        $row['examdate'] = $examdate;
        $row['slotstart'] = $start;
        $row['slotend'] = $end;
        $row['typeid'] = $typeid;
        $row['type'] = $type;
        $row['seats']  = $seats;
        $record = $DB->insert_record('hall_reservations', $row);
        // $data = (new local_trainingprogram\local\trainingprogram)->hall_($stable, $filtervalues);
        return [
            'options' => $record,
        ];
    }
    public static function tprogram_slotbooking_returns() {
        return new external_single_structure([
          'options' => new external_value(PARAM_RAW, 'The paging data for the service'),
        ]);
    }

    public static function remove_reservations_parameters(){
        return new external_function_parameters(
            array(
                'sessionkey' => new external_value(PARAM_RAW, 'session key', ''),
                'halls' => new external_value(PARAM_RAW, 'hall id', ''),
                'type' => new external_value(PARAM_RAW, 'type', ''),
            )
        );
    }
  
    public static function remove_reservations($sessionkey, $halls=false, $type){
        global $DB;
        $params = self::validate_parameters(self::remove_reservations_parameters(),
                                    ['sessionkey' => $sessionkey, 'halls' => $halls, 'type' => $type]);
        if($sessionkey) {
            $record_exist = $DB->get_records('reservations_draft', ['entitycode' => $sessionkey, 'type' => $type]);
            if(!empty($record_exist)) {
                $DB->delete_records('reservations_draft', ['entitycode' => $sessionkey, 'type' => $type]);
            }
        }                 
        if(!empty($halls)) {
            $sql = "SELECT rd.id, rd.seats, h.name, rd.date FROM {reservations_draft} rd JOIN {hall} h ON h.id = rd.hallid WHERE entitycode = '{$sessionkey}' AND hallid = $halls ";
            $draftrecords = $DB->get_records_sql($sql);

            $data = [];
            foreach($draftrecords as $draftrecord) {
                $row = [];
                $row['name'] = $draftrecord->name;
                $row['seats'] = $draftrecord->seats;
                $row['examdate'] = $draftrecord->date;
                $data[] = $row;
            }            
        } else {
            $data = false;
        }
        return ['options' => json_encode($data)];
    }
   
    public static function remove_reservations_returns() {
        return new external_single_structure([
          'options' => new external_value(PARAM_RAW, 'The paging data for the service'),
        ]);
    }

    public static function viewcoupondata_parameters() {
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
    public static function viewcoupondata($options, $dataoptions, $offset = 0, $limit = 0, $filterdata) {
        global $DB, $PAGE, $CFG;
        require_login();
        $params = self::validate_parameters(
            self::viewcoupondata_parameters(),
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
        $filtervalues = json_decode($filterdata);
        $stable = new \stdClass();
        $stable->thead = false;
        $stable->start = $offset;
        $stable->length = $limit;
        $data = (new local_trainingprogram\local\trainingprogram)->get_listof_coupons($stable, $filtervalues);
        $totalcount = $data['totalcoupons'];
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
    public static function viewcoupondata_returns() {
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
                                'code' => new external_value(PARAM_RAW, 'code'),
                                'number_of_codes' => new external_value(PARAM_RAW, 'number_of_codes'),
                                'discount' => new external_value(PARAM_INT, 'discount'),
                                'coupon_created_date' => new external_value(PARAM_RAW, 'coupon_created_date'),
                                'coupon_expired_date' => new external_value(PARAM_RAW, 'coupon_expired_date'),
                                'coupon_status' => new external_value(PARAM_RAW, 'coupon_status'),
                                'actionview' => new external_value(PARAM_RAW, 'actionview'),
                                'deletecouponview' => new external_value(PARAM_RAW, 'deletecouponview'),
                                'caneditcoupon' => new external_value(PARAM_RAW, 'caneditcoupon'),
                            )
                        )
                    ),
                    'request_view' => new external_value(PARAM_INT, 'request_view', VALUE_OPTIONAL),
                    'nocourses' => new external_value(PARAM_BOOL, 'nocourses', VALUE_OPTIONAL),
                    'totalcoupons' => new external_value(PARAM_INT, 'totalcoupons', VALUE_OPTIONAL),
                    'length' => new external_value(PARAM_INT, 'length', VALUE_OPTIONAL),
                )
            )
        ]);
    } 

     public static function deletecoupon_parameters(){
        return new external_function_parameters(
            array(
                'couponid' => new external_value(PARAM_INT,'Coupon id',0),
                'couponcode' => new external_value(PARAM_RAW,'Coupon Code',0),
            )
        );
    }
    public static  function deletecoupon($couponid, $couponcode){
        global $CFG;
        global $USER;
        global $DB;
        $params=self::validate_parameters(
            self::deletecoupon_parameters(),
            array('couponid' => $couponid, 'couponcode' => $couponcode)
        );
        $systemcontext = context_system::instance();
       if ($couponid) {
           (new local_trainingprogram\local\trainingprogram)->remove_coupon($couponid, $couponcode);
        } else {
          throw new moodle_exception('Error in submission');
        }
        return true;    
    }
    public static function deletecoupon_returns() {
        return new external_value(PARAM_BOOL, 'return');
    } 

    public function enrollment_search_parameters() {
        return new external_function_parameters(
            array(
                'type' => new external_value(PARAM_RAW, 'Type of the record', 0),
                'programid' => new external_value(PARAM_INT, 'Type of the record', 0),
                'offeringid' => new external_value(PARAM_INT, 'Type of the record', 0),
                'courseid' => new external_value(PARAM_INT, 'ID of the record', 0),
                'query' => new external_value(PARAM_RAW, 'query', 0),
            )
        );
    }
    
    public function enrollment_search($type, $programid, $offeringid, $courseid, $query) {
        global $DB, $PAGE;
        $sitecontext = context_system::instance();
        $PAGE->set_context($sitecontext);
        // Parameter validation.
        $params = self::validate_parameters(
            self::enrollment_search_parameters(),
            [
                'type' => $type,
                'programid' => $programid,
                'offeringid' => $offeringid,
                'courseid' => $courseid,
                'query' => $query,
            ]
        );
        $records = (new \local_trainingprogram\local\trainingprogram)->program_enrolled_users(  $offeringid,  $params);
        $totalcount = count($records);
        if($type=='add'){
            return ['options' => json_encode($records['availableusers']),
                'count' => count($records['availableusers'])];
        }
        if($type=='remove'){
            return ['options' => json_encode($records['enrolledusers']),
                'count' => count($records['enrolledusers'])];
        }
    }
    
    public function enrollment_search_returns() {
        return new external_single_structure([
            'options' => new external_value(PARAM_RAW, 'The paging data for the service'),
            'count' => new external_value(PARAM_RAW, 'The paging data for the service'),
        ]);
    }
    public static function viewearlyregistrationdata_parameters() {
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
    public static function viewearlyregistrationdata($options, $dataoptions, $offset = 0, $limit = 0, $filterdata) {
        global $DB, $PAGE, $CFG;
        require_login();
        $params = self::validate_parameters(
            self::viewearlyregistrationdata_parameters(),
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
        $filtervalues = json_decode($filterdata);
        $stable = new \stdClass();
        $stable->thead = false;
        $stable->start = $offset;
        $stable->length = $limit;
        $data = (new local_trainingprogram\local\trainingprogram)->get_listof_earlyregistrations($stable, $filtervalues);

        $totalcount = $data['totalearlyregistrations'];
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
    public static function viewearlyregistrationdata_returns() {
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
                                'days' => new external_value(PARAM_INT, 'days'),
                                'discount' => new external_value(PARAM_INT, 'discount'),
                                'earlyregistration_created_date' => new external_value(PARAM_RAW, 'earlyregistration_created_date'),
                                'earlyregistration_expired_date' => new external_value(PARAM_RAW, 'earlyregistration_expired_date'),
                                'earlyregistration_status' => new external_value(PARAM_RAW, 'earlyregistration_status'),
                                'actionview' => new external_value(PARAM_RAW, 'actionview'),
                                'deleteearlyregistrationview' => new external_value(PARAM_RAW, 'deleteearlyregistrationview'),
                            )
                        )
                    ),
                    'request_view' => new external_value(PARAM_INT, 'request_view', VALUE_OPTIONAL),
                    'nocourses' => new external_value(PARAM_BOOL, 'nocourses', VALUE_OPTIONAL),
                    'totalearlyregistrations' => new external_value(PARAM_INT, 'totalearlyregistrations', VALUE_OPTIONAL),
                    'length' => new external_value(PARAM_INT, 'length', VALUE_OPTIONAL),
                )
            )
        ]);
    } 
    public static function deleteearlyregistration_parameters(){
        return new external_function_parameters(
            array(
                'earlyregistrationid' => new external_value(PARAM_INT,'earlyregistration id',0),
                'days' => new external_value(PARAM_RAW,'days',0),
                'discount' => new external_value(PARAM_RAW,'discount',0),
            )
        );
    }
    public static  function deleteearlyregistration($earlyregistrationid,$days,$discount){
        global $CFG;
        global $USER;
        global $DB;
        $params=self::validate_parameters(
            self::deleteearlyregistration_parameters(),
            array('earlyregistrationid' => $earlyregistrationid, 'days' => $days,'discount' => $discount)
        );
        $systemcontext = context_system::instance();
       if ($earlyregistrationid) {
           (new local_trainingprogram\local\trainingprogram)->remove_earlyregistration($earlyregistrationid,$days,$discount);
        } else {
          throw new moodle_exception('Error in submission');
        }
        return true;    
    }
    public static function deleteearlyregistration_returns() {
        return new external_value(PARAM_BOOL, 'return');
    } 

    public static function viewallprogramsservice_parameters() {
        return new external_function_parameters([
            'offset' => new external_value(PARAM_INT, 'Number of items to skip from the begging of the result set',
                VALUE_DEFAULT, 0),
            'limit' => new external_value(PARAM_INT, 'Maximum number of results to return',
                VALUE_DEFAULT, 0),
            'filterdata' => new external_value(PARAM_RAW, 'filters applied'),
            'langinuse' => new external_value(PARAM_RAW, 'Language in use'),
            'pagesize' => new external_value(PARAM_RAW, 'Page Size'),
            'pagenumber' => new external_value(PARAM_RAW, 'Page Number'),
        ]);
    }
    public static function viewallprogramsservice($offset = 0, $limit = 0, $filterdata,$langinuse,$pagesize,$pagenumber) {
        global $DB, $PAGE, $CFG;
        $params = self::validate_parameters(
            self::viewallprogramsservice_parameters(),
            [
                'offset' => $offset,
                'limit' => $limit,
                'filterdata' => $filterdata,
                'langinuse' => $langinuse,
                'pagesize' => $pagesize,
                'pagenumber' => $pagenumber,
            ]
        );
        $offset = $params['offset'];
        $limit = $params['limit'];
        $langinuse = $params['langinuse'];
        $pagesize = $params['pagesize'];
        $pagenumber = ($params['pagenumber'] > 1) ? (($pageNumber-1)* $pageSize) : 0;
        $filtervalues = json_decode($filterdata);
        $stable = new \stdClass();
        $stable->thead = false;
        $stable->start = $offset;
        $stable->length = $limit;
        $stable->langinuse = $langinuse;
        $data = (new local_trainingprogram\local\trainingprogram)->all_programs_for_api_listing($stable, $filtervalues);


       return ['programslist' =>$data['programs'],'totalitemcount'=>$data['totalprograms']];
    }
    public static function viewallprogramsservice_returns() {
        return new external_single_structure([
          'totalitemcount' => new external_value(PARAM_INT, 'total number of programs in result set'),
          'programslist' => new external_multiple_structure(
               new external_single_structure(
                   array(
                        'arabicprogramname' => new external_value(PARAM_RAW, 'Arabic Program Name'),
                        'programname' => new external_value(PARAM_RAW, 'English Program Name'),
                        'programcode' => new external_value(PARAM_RAW, 'Program Code'),
                        'imageurl' => new external_value(PARAM_RAW, 'Program Image URL'),
                        'pricetype' => new external_value(PARAM_RAW, 'Price Type '),
                        'sellingprice' => new external_value(PARAM_RAW, 'Selling Price'),
                        'actualprice' => new external_value(PARAM_RAW, 'Actual Price'),
                        'taxfree' => new external_value(PARAM_RAW, 'Tax Free'),
                        'description' => new external_value(PARAM_RAW, 'Program Description'),
                        'programgoals' => new external_value(PARAM_RAW, 'Program Goals'),
                        'programlangauge' => new external_value(PARAM_RAW, 'Program Langauge'),
                        'programmethods' => new external_value(PARAM_RAW, 'Program Methods'),
                        'evaluationmethods' => new external_value(PARAM_RAW, 'Evaluation Methods'),
                        'attendancecompletion' => new external_value(PARAM_RAW, 'Attendance Completion'),
                        'attendancepercentage' => new external_value(PARAM_RAW, 'Attendance percentage'),
                        'durationindays' => new external_value(PARAM_RAW, 'Duration'),
                        'availablefrom' => new external_value(PARAM_RAW, ' Program Startdate'),
                        'availableto' => new external_value(PARAM_RAW, ' Program Enddate'),
                        'hour' => new external_value(PARAM_RAW, 'Hour'),
                        'sectors' => new external_value(PARAM_RAW, 'Program Sectors'),
                        'targetgroup' => new external_value(PARAM_RAW, 'Targeted Group'),
                        'level' => new external_value(PARAM_RAW, 'Level'),
                        'competencytype' => new external_value(PARAM_RAW, 'Competency Type'),
                        'competencies' => new external_value(PARAM_RAW, 'Competencies'),
                        'discount' => new external_value(PARAM_RAW, 'Discount'),
                        'courseid' => new external_value(PARAM_INT, 'course ID'),
                        'coursecode' => new external_value(PARAM_RAW, 'Course Code'),
                        'trainingid' => new external_value(PARAM_INT, 'id'),
                    )
                )
            )
        ]);
    }
    public static function detailedprogramviewservice_parameters() {
        return new external_function_parameters([
            'id' => new external_value(PARAM_INT, 'id',
                VALUE_DEFAULT, 0),
            'isArabic' => new external_value(PARAM_RAW, 'isArabic', VALUE_DEFAULT),
        ]);
    }
    public static function detailedprogramviewservice($id,$isArabic = NULL) {
        global $DB, $PAGE, $CFG;
        require_login();
        $params = self::validate_parameters(
            self::detailedprogramviewservice_parameters(),
            [
                'id' => $id,
                'isArabic' => $isArabic,

            ]
        );
        $settings = external_settings::get_instance(); 
        $mlang = ($settings->get_lang()) ?  $settings->get_lang() :(($isArabic)?(($isArabic == 'true') ? 'ar' :'en') : null);
      
        $courseid=$DB->get_field('local_trainingprogram','courseid',array('id'=>$id));
        $data =(new local_trainingprogram\local\trainingprogram)->detailed_program_view_for_api_listing($id,$mlang);

       if($data) {
      
           return ['programrecord' =>$data];
       } else {
           return null;
       }
    }
    public static function detailedprogramviewservice_returns() {
        return new external_single_structure([
            'programrecord'=> new external_single_structure([
                'arabicprogramname' => new external_value(PARAM_RAW, 'Arabic Program Name'),
                'programname' => new external_value(PARAM_RAW, 'English Program Name'),
                'durationindays' => new external_value(PARAM_RAW, 'Duration In Days'),
                'durationinhours' => new external_value(PARAM_RAW, 'Duration In Hours'),
                'programcode' => new external_value(PARAM_RAW, 'Program Code'),
                'image' => new external_value(PARAM_RAW, 'Program Image URL'),
                'assignedtrainers' => new external_multiple_structure(
                    new external_single_structure(
                        array(
                        'name' => new external_value(PARAM_TEXT, 'name'),
                        )
                    )
                ), '', VALUE_OPTIONAL,

                'totalitemcount' => new external_value(PARAM_INT, 'total number of offerings in result set'),
                'offerings' => new external_multiple_structure(
                    new external_single_structure(
                        array(
                        'offeringcode' => new external_value(PARAM_RAW, 'PARAM_RAW'),
                        'startdate' => new external_value(PARAM_INT, 'startdate'),
                        'enddate' => new external_value(PARAM_INT, 'enddate'),
                        'starttime' => new external_value(PARAM_RAW, 'starttime'),
                        'endtime' => new external_value(PARAM_RAW, 'endtime'),
                        'seats' => new external_value(PARAM_INT, 'seats'),
                        'type' => new external_value(PARAM_INT, 'type'),
                        'typename' => new external_value(PARAM_TEXT, 'type'),
                        'organization' => new external_value(PARAM_RAW, 'organization'),
                        'offeringmethod' => new external_value(PARAM_RAW, 'offeringmethod'),
                        'hallname' => new external_value(PARAM_RAW, 'hallname'),
                        'buildingname' => new external_value(PARAM_RAW, 'buildingname'),
                        'city' => new external_value(PARAM_RAW, 'city'),
                        'assignedtrainers' => new external_value(PARAM_RAW, 'assignedtrainers'),
                        'nooftraineesenrolled' => new external_value(PARAM_RAW, 'nooftraineesenrolled'),
                        'sellingprice' => new external_value(PARAM_RAW, 'sellingprice'),
                        'offeringcreater' => new external_value(PARAM_RAW, 'offeringcreater'),
                        'offeringupdater' => new external_value(PARAM_RAW, 'offeringupdater'),
                        'offeringcreateddate' => new external_value(PARAM_INT, 'offeringcreateddate'),
                        'offeringmodifieddate' => new external_value(PARAM_INT, 'offeringmodifieddate'),
                        'productdetails' => new external_single_structure(
                            array(
                                'product' => new external_value(PARAM_INT, 'product'),
                                'variation' => new external_value(PARAM_INT, 'variation'),
                                'category' => new external_value(PARAM_INT, 'category'),
                            )
                        ),
                        )
                    )
                ), '', VALUE_OPTIONAL,
                'pricetype' => new external_value(PARAM_RAW, 'Price Type '),
                'sellingprice' => new external_value(PARAM_RAW, 'Selling Price'),
                'description' => new external_value(PARAM_RAW, 'description'),
                'programgoals' => new external_value(PARAM_RAW, 'programgoals'),
                'programlangauge' => new external_value(PARAM_RAW, 'Program Langauge'),
                'programmethods' => new external_value(PARAM_RAW, 'Program Methods'),
                'evaluationmethods'=> new external_value(PARAM_RAW, 'Evaluation Methods'),
                'attendancecompletion' => new external_value(PARAM_RAW, 'Attendance Completion'),
                'attendancepercentage' => new external_value(PARAM_RAW, 'Attendance percentage'),
                'availablefrom' => new external_value(PARAM_INT, ' Program Startdate'),
                'availableto' => new external_value(PARAM_INT, ' Program Enddate'),
                'certificatename' => new external_value(PARAM_RAW, 'certificatename'),
                'programagenda' => new external_multiple_structure(
                    new external_single_structure(
                        array(
                        'name' => new external_value(PARAM_TEXT, 'name'),
                        'description' => new external_value(PARAM_RAW, 'description'),
                        'id' => new external_value(PARAM_INT, 'id'),
                        'parentValue' => new external_value(PARAM_INT, 'Program Id'),
                        )
                    )
                ), '', VALUE_OPTIONAL,
                'hour' => new external_value(PARAM_RAW, 'Hour'),
                'sectors' => new external_multiple_structure(
                    new external_single_structure(
                        array(
                        'name' => new external_value(PARAM_TEXT, 'name'),
                        'id' => new external_value(PARAM_INT, 'ID'),
                        )
                    )
                ), '', VALUE_OPTIONAL,
                'targetgroup' => new external_multiple_structure(
                    new external_single_structure(
                        array(
                        'name' => new external_value(PARAM_TEXT, 'name'),
                        'description' => new external_value(PARAM_RAW, 'description'),
                        'id' => new external_value(PARAM_INT, 'value'),
     
                        )
                    )
                ), '', VALUE_OPTIONAL,
                'level' => new external_value(PARAM_INT, 'Level'),
                'competencytype' => new external_multiple_structure(
                    new external_single_structure(
                        array(
                        'name' => new external_value(PARAM_TEXT, 'name'),
                        )
                    )
                ), '', VALUE_OPTIONAL,

                'competencies' => new external_multiple_structure(
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
                'discount' => new external_value(PARAM_RAW, 'Discount'),
                'courseid' => new external_value(PARAM_INT, 'course ID'),
                'published' => new external_value(PARAM_INT, 'published'),
                'programcreator' => new external_value(PARAM_RAW, 'programcreator'),
                'programupdator' => new external_value(PARAM_RAW, 'programupdator'),
                'createdat' => new external_value(PARAM_INT, 'createdat'),
                'modifiedat' => new external_value(PARAM_INT, 'modifiedat'),
                
            ])

        ]);
    }

    public static function get_programdetails_parameters() {
        return new external_function_parameters([
            'id' => new external_value(PARAM_INT, 'programid',
                VALUE_DEFAULT, 0),
            'isArabic' => new external_value(PARAM_RAW, 'isArabic', VALUE_DEFAULT),
        ]);
    }
    public static function get_programdetails($id,$isArabic = NULL) {
        global $DB, $PAGE, $CFG;
        require_login();
        $params = self::validate_parameters(
            self::get_programdetails_parameters(),
            [
                'id' => $id,
                'isArabic' => $isArabic,
            ]
        );
      
            $settings = external_settings::get_instance();
       
            $mlang =  ($settings->get_lang()) ?  $settings->get_lang() :(($isArabic)?(($isArabic == 'true') ? 'ar' :'en') : null);
            
        $courseid=$DB->get_field('local_trainingprogram','courseid',array('id'=>$id));
        $data = (new local_trainingprogram\local\trainingprogram)->trainee_program_course_overview($id,$courseid,NULL,$mlang);

        $data['offerings'][0]->competencieslists = $data['competencieslists'];
        $data['offerings'][0]->image = $data['image'];
        if (empty($data['offerings'][0]->trainerslist)) {
            $data['offerings'][0]->trainerslist = [];
        }
        if (empty($data['offerings'][0]->todoactivities)) {
            $data['offerings'][0]->todoactivities = [];
        }
        if (empty($data['offerings'][0]->sessiondata)) {
            $data['offerings'][0]->sessiondata = [];
        }

        return $data['offerings'][0];
    }
    public static function get_programdetails_returns() {
        return new external_single_structure([
            'id' => new external_value(PARAM_INT, 'id', VALUE_OPTIONAL),
            'image' => new external_value(PARAM_RAW, 'image', VALUE_OPTIONAL),
            'startdate' => new external_value(PARAM_INT, 'startdate', VALUE_OPTIONAL),
            'enddate' => new external_value(PARAM_INT, 'enddate', VALUE_OPTIONAL),
            'availableseats' => new external_value(PARAM_INT, 'availableseats', VALUE_OPTIONAL),
            'sellingprice' => new external_value(PARAM_INT, 'sellingprice', VALUE_OPTIONAL),
            'prequiz' => new external_value(PARAM_RAW, 'prequiz', VALUE_OPTIONAL),
            'postquiz' => new external_value(PARAM_RAW, 'postquiz', VALUE_OPTIONAL),
            'trainingmethod' => new external_value(PARAM_TEXT, 'trainingmethod', VALUE_OPTIONAL),
            'hallname' => new external_value(PARAM_TEXT, 'hallname', VALUE_OPTIONAL),
            'locationstatus' => new external_value(PARAM_BOOL, 'hallname', VALUE_OPTIONAL),
            'maplocation' => new external_value(PARAM_RAW, 'maplocation', VALUE_OPTIONAL),
            'seatingcapacity' => new external_value(PARAM_INT, 'seatingcapacity', VALUE_OPTIONAL),
            'city' => new external_value(PARAM_TEXT, 'city', VALUE_OPTIONAL),
            'coid' => new external_value(PARAM_INT, 'coid', VALUE_OPTIONAL),
            'offeringstatus' => new external_value(PARAM_RAW, 'offeringstatus', VALUE_OPTIONAL),
            'trainerslist' => new external_multiple_structure(
                new external_single_structure(
                    array(
                        'name' => new external_value(PARAM_RAW, 'name', VALUE_OPTIONAL),
                    )
                )
            ), '', VALUE_OPTIONAL,
            'todoactivities' => new external_multiple_structure(
                new external_single_structure(
                    array(
                        'name' => new external_value(PARAM_TEXT, 'name', VALUE_OPTIONAL),
                        'link' => new external_value(PARAM_RAW, 'link', VALUE_OPTIONAL),
                    )
                )
            ), '', VALUE_OPTIONAL,
            'sessiondata' => new external_multiple_structure(
                new external_single_structure(
                    array(
                        'cid' => new external_value(PARAM_INT, 'cid', VALUE_OPTIONAL),
                        'status' => new external_value(PARAM_RAW, 'status', VALUE_OPTIONAL),
                        'sessiondate' => new external_value(PARAM_RAW, 'sessiondate', VALUE_OPTIONAL),
                        'sessiontime' => new external_value(PARAM_RAW, 'sessiontime', VALUE_OPTIONAL),
                    )
                )
            ), '', VALUE_OPTIONAL,
            'competencieslists' => new external_multiple_structure(
                new external_single_structure(
                    array(
                    'competencyid' => new external_value(PARAM_INT, 'competencyid', VALUE_OPTIONAL),
                    'competencyname' => new external_value(PARAM_TEXT, 'competencyname', VALUE_OPTIONAL),
                    'competencylevel' => new external_value(PARAM_RAW, 'competencylevel', VALUE_OPTIONAL),
                    'level' => new external_value(PARAM_INT, 'level'),                        
                    )
                )
            ), '', VALUE_OPTIONAL,
        ]);
    }

    public static function getprograminfo_parameters() {
        return new external_function_parameters([
            'programId' => new external_value(PARAM_INT, 'programid',
                VALUE_DEFAULT, 0),
            'isArabic' => new external_value(PARAM_RAW, 'Language',VALUE_DEFAULT,false),
        ]);
    }
    public static function getprograminfo($programId,$isArabic) {
        global $DB, $PAGE, $CFG;
        require_login();
        $context = context_system::instance();
        $params = self::validate_parameters(
            self::getprograminfo_parameters(),
            [
                'programId' => $programId,
                'isArabic' => $isArabic,
            ]
        );

        $courseid=$DB->get_field('local_trainingprogram','courseid',array('id'=>$programId));
        $data =(new local_trainingprogram\local\trainingprogram)->get_programinfo($programId,$isArabic);

        if($data) {
            $event = \local_trainingprogram\event\success_response::create(
                array(
                'context'=>$context,
                'objectid' => 1,
                'other'=>array(
                    'Function'=>'local_trainingprogram_getprograminfo',
                    'Success'=>'Successfully Fetched the Response'
                )
                )
            );
            $event->trigger();
            return $data;
           } else {
            $event = \local_trainingprogram\event\error_response::create(
                array( 
                    'context'=>$context,
                    'objectid' =>0,
                    'other'=>array(
                        'Function'=>'local_trainingprogram_getprograminfo',
                        'Error'=>'Invalid Response Value Detected'       
                    )  
                )
                    );  
               return null;
           }

    }



    public static function getprograminfo_returns() {
       return new external_single_structure([
                'sectorsList' => new external_multiple_structure(
                    new external_single_structure(
                        array(
                        'name' => new external_value(PARAM_TEXT, 'name'),
                        'description' => new external_value(PARAM_RAW, 'description'),
                        'value' => new external_value(PARAM_INT, 'ID'),
                        )
                    )
                ), '', VALUE_OPTIONAL,
              
                'id' => new external_value(PARAM_INT, ' Program ID'),
                'name' => new external_value(PARAM_TEXT, ' Program Name'),
                'brief' => new external_value(PARAM_RAW, ' Program Description'),
                'programType' => new external_value(PARAM_TEXT, 'programType'),
                'trainingType' => new external_value(PARAM_TEXT, 'trainingType'),
                'language' => new external_value(PARAM_RAW, ' Program language'),
                'imgDataUrl' => new external_value(PARAM_RAW, ' Program Image URL'),
                'plans' => new external_multiple_structure(
                    new external_single_structure(
                        array(
                        'id' => new external_value(PARAM_INT, 'ID'),
                        'programID' => new external_value(PARAM_INT, 'Program ID'),
                        'programFees' => new external_value(PARAM_RAW, 'Offering Fees'),
                        'duration' => new external_value(PARAM_RAW, 'Offering duration'),
                        'planLocation' => new external_value(PARAM_RAW, 'Location'),
                        'city' => new external_value(PARAM_RAW, 'City'),
                        'planScheduleDayStartDate' => new external_value(PARAM_RAW, 'planScheduleDayStartDate'),
                        'planScheduleDayEndDate' => new external_value(PARAM_RAW, 'planScheduleDayEndDate'),
                        'planScheduleDayStartTime' => new external_value(PARAM_RAW, 'planScheduleDayStartTime'),
                        'planScheduleDayEndTime' => new external_value(PARAM_RAW, 'planScheduleDayEndTime'),
                        'programlanguageId' => new external_value(PARAM_RAW, 'programlanguageId'),
                        'programLanguage' => new external_value(PARAM_RAW, 'programLanguage'),
                        'targetGender' => new external_value(PARAM_RAW, 'targetGender'),
                        'roomId' => new external_value(PARAM_RAW, 'roomId'),
                        'roomName' => new external_value(PARAM_RAW, 'roomName'),
                        
                    
                        )
                    )
                ), '', VALUE_OPTIONAL,
                'programAgenda' => new external_multiple_structure(
                    new external_single_structure(
                        array(
                        'name' => new external_value(PARAM_TEXT, 'name'),
                        'description' => new external_value(PARAM_RAW, 'description'),
                        'value' => new external_value(PARAM_INT, 'id'),
                        'parentValue' => new external_value(PARAM_INT, 'Program Id'),
                        )
                    )
                ), '', VALUE_OPTIONAL,

                'programCompetencies' => new external_multiple_structure(
                    new external_single_structure(
                        array(
                        'typeId' => new external_value(PARAM_INT, 'typeId'),
                        'code' => new external_value(PARAM_TEXT, 'code'),
                        'typeName' => new external_value(PARAM_TEXT, 'typeName'),
                        'name' => new external_value(PARAM_TEXT, 'name'),
                        'description' => new external_value(PARAM_RAW, 'description'),
                        'value' => new external_value(PARAM_INT, 'value'),
     
                        )
                    )
                ), '', VALUE_OPTIONAL,

                'preProgramPath' => new external_multiple_structure(
                    new external_single_structure(
                        array(
                        'name' => new external_value(PARAM_TEXT, 'name'),
                        'description' => new external_value(PARAM_RAW, 'description'),
                        'value' => new external_value(PARAM_INT, 'value'),
     
                        )
                    )
                ), '', VALUE_OPTIONAL,

                'postProgramPath' => new external_multiple_structure(
                    new external_single_structure(
                        array(
                        'name' => new external_value(PARAM_TEXT, 'name'),
                        'description' => new external_value(PARAM_RAW, 'description'),
                        'value' => new external_value(PARAM_INT, 'value'),
     
                        )
                    )
                ), '', VALUE_OPTIONAL,

                'integratedProgramPath' => new external_multiple_structure(
                    new external_single_structure(
                        array(
                        'name' => new external_value(PARAM_TEXT, 'name'),
                        'description' => new external_value(PARAM_RAW, 'description'),
                        'value' => new external_value(PARAM_INT, 'value'),
     
                        )
                    )
                ), '', VALUE_OPTIONAL,
                'programMains' => new external_multiple_structure(
                    new external_single_structure(
                        array(
                        'name' => new external_value(PARAM_RAW, 'name'),
                        'description' => new external_value(PARAM_RAW, 'description'),
                        'value' => new external_value(PARAM_INT, 'value'),
     
                        )
                    )
                ), '', VALUE_OPTIONAL,
                'programTargetCategories' => new external_multiple_structure(
                    new external_single_structure(
                        array(
                        'code' => new external_value(PARAM_TEXT, 'code'),
                        'name' => new external_value(PARAM_TEXT, 'name'),
                        'description' => new external_value(PARAM_RAW, 'description'),
                        'value' => new external_value(PARAM_INT, 'value'),
     
                        )
                    )
                ), '', VALUE_OPTIONAL,

                'programSectors' => new external_multiple_structure(
                    new external_single_structure(
                        array(
                        'code' => new external_value(PARAM_TEXT, 'code'),
                        'name' => new external_value(PARAM_TEXT, 'name'),
                        'description' => new external_value(PARAM_RAW, 'description'),
                        'value' => new external_value(PARAM_INT, 'value'),
     
                        )
                    )
                ), '', VALUE_OPTIONAL,    

                'programRequirements' => new external_multiple_structure(
                    new external_single_structure(
                        array(
                        'name' => new external_value(PARAM_TEXT, 'name'),
                        'description' => new external_value(PARAM_RAW, 'description'),
                        'value' => new external_value(PARAM_INT, 'value'),
     
                        )
                    )
                ), '', VALUE_OPTIONAL,  
                'trainingTopics' => new external_multiple_structure(
                    new external_single_structure(
                        array(
                        'name' => new external_value(PARAM_RAW, 'name'),
                        'description' => new external_value(PARAM_RAW, 'description'),
                        'value' => new external_value(PARAM_INT, 'value'),
                        'parentValue' => new external_value(PARAM_INT, 'value'),
     
                        )
                    )
                ), '', VALUE_OPTIONAL,         
                'competencyLevelCode' => new external_value(PARAM_RAW, 'competencyLevelCode'),
                'competencyLevelId' => new external_value(PARAM_INT, 'competencyLevelId'),
                'competencyLevelName' => new external_value(PARAM_RAW, 'competencyLevelName'),
                'actualProgramFees' => new external_value(PARAM_RAW, 'actualProgramFees'),
                'actualNumberOfDayesAndHours' => new external_value(PARAM_RAW, 'actualNumberOfDayesAndHours'),
                'actualProgramLang' => new external_value(PARAM_RAW, 'actualProgramLang'),
                'actualTrainingMethod' => 
                    new external_multiple_structure(
                        new external_value(PARAM_TEXT, 'name'), 'Array of course module IDs'
                ), '', VALUE_OPTIONAL,

                'actualTrainingType' => 
                    new external_multiple_structure(
                        new external_value(PARAM_TEXT, 'name'), 'Array of course module IDs'
                ), '', VALUE_OPTIONAL,
                
                'actualLanguage' => 
                    new external_multiple_structure(
                        new external_value(PARAM_TEXT, 'name'), 'Array of course module IDs'
                ), '', VALUE_OPTIONAL,
                
                'actualEvaluationMethod' => 
                    new external_multiple_structure(
                        new external_value(PARAM_TEXT, 'name'), 'Array of course module IDs'
                ), '', VALUE_OPTIONAL,
                'externalRegistrationURL' => new external_value(PARAM_URL, 'externalRegistrationURL'),
                'organizationPartner' => new external_multiple_structure(
                    new external_single_structure(
                        array(
                        'name' => new external_value(PARAM_TEXT, 'name'),
                        'description' => new external_value(PARAM_RAW, 'description'),
                        'value' => new external_value(PARAM_INT, 'ID'),
                    
                        )
                    )
                ), '', VALUE_OPTIONAL,
                'programFees' => new external_value(PARAM_RAW, 'Selling price'),
                'amount' => new external_value(PARAM_RAW, 'Selling price'),
                'discountPenalty' => new external_value(PARAM_RAW, 'discountPenalty'),
                'discountAmount' => new external_value(PARAM_RAW, 'discountAmount'),
                'vat' => new external_value(PARAM_RAW, 'vat'),
                'totalAmount' => new external_value(PARAM_RAW, 'totalAmount'),
                'isPercentage' => new external_value(PARAM_RAW, 'isPercentage'),
                'detailsPageURL' => new external_value(PARAM_URL, 'Program detailsPageURL'),

       
         ]);
    }

    public static function getallprogramsbyjobfamilyid_parameters() {
        return new external_function_parameters([
            'JobFamilyID' => new external_value(PARAM_INT, 'JobFamilyID'),
            'isArabic' => new external_value(PARAM_RAW, 'Language',VALUE_DEFAULT,false),
        ]);
    }
    public static function getallprogramsbyjobfamilyid($JobFamilyID,$isArabic) {
        global $DB, $PAGE, $CFG;
        $context = context_system::instance();   
        $params = self::validate_parameters(
            self::getallprogramsbyjobfamilyid_parameters(),
            [
                'JobFamilyID' => $JobFamilyID,
                'isArabic' => $isArabic,
            ]
        );
        $data =(new local_trainingprogram\local\trainingprogram)->get_allprogramsbyjobfamilyid($JobFamilyID,$isArabic);

        if($data) {
            $event = \local_trainingprogram\event\success_response::create(
                array(
                'context'=>$context,
                'objectid' => 1,
                'other'=>array(
                    'Function'=>'local_trainingprogram_getallprogramsbyjobfamilyid',
                    'Success'=>'Successfully Fetched the Response'
                )
                )
            );
            $event->trigger();

           return $data['programs'];
        } else {
            $event = \local_trainingprogram\event\error_response::create(
                array( 
                    'context'=>$context,
                    'objectid' =>0,
                    'other'=>array(
                        'Function'=>'local_trainingprogram_getallprogramsbyjobfamilyid',
                        'Error'=>'Invalid Response Value Detected'       
                    )
                    )
                );  
            $event->trigger();
           return null;
        }
    }
    public static function getallprogramsbyjobfamilyid_returns() {
        return  new external_multiple_structure(
            new external_single_structure(
                array(
                'name'=> new external_value(PARAM_TEXT, ' Program Name'),
                'description'=> new external_value(PARAM_RAW, ' Program Description'),
                'value'=> new external_value(PARAM_INT, ' Program ID'),
               )
            )
        );
    }

    public static function getallprogramsbycompetencyid_parameters() {
        return new external_function_parameters([
            'CompetencyID' => new external_value(PARAM_INT, 'competencyID'),
            'isArabic' => new external_value(PARAM_RAW, 'Language',VALUE_DEFAULT,false),
        ]);
    }
    public static function getallprogramsbycompetencyid($CompetencyID,$isArabic) {
        global $DB, $PAGE, $CFG;
        $context = context_system::instance();   
      
        $params = self::validate_parameters(
            self::getallprogramsbycompetencyid_parameters(),
            [
                'CompetencyID' => $CompetencyID,
                'isArabic' => $isArabic,
            ]
        );
        $data =(new local_trainingprogram\local\trainingprogram)->get_allprogramsbycompetencyid($CompetencyID,$isArabic);

        if($data) {
            $event = \local_trainingprogram\event\success_response::create(
                array(
                'context'=>$context,
                'objectid' => 1,
                'other'=>array(
                    'Function'=>'local_trainingprogram_getallprogramsbycompetencyid',
                    'Success'=>'Successfully Fetched the Response'
                )
                )
            );
            $event->trigger();
           return $data['programs'];
        } else {

            $event = \local_trainingprogram\event\error_response::create(
                array( 
                    'context'=>$context,
                    'objectid' =>0,
                    'other'=>array(
                        'Function'=>'local_trainingprogram_getallprogramsbycompetencyid',
                        'Error'=>'Invalid Response Value Detected'       
                    )
                    )
                );  
            $event->trigger();

           return null;
        }
    }
    public static function getallprogramsbycompetencyid_returns() {
        return  new external_multiple_structure(
            new external_single_structure(
                array(
                'name'=> new external_value(PARAM_TEXT, ' Program Name'),
                'description'=> new external_value(PARAM_RAW, ' Program Description'),
                'value'=> new external_value(PARAM_INT, ' Program ID'),
                'imageurl'=> new external_value(PARAM_RAW, ' imageurl'),
               )
            )
        );
    }

     public static function getallprograms_parameters() {
        return new external_function_parameters([

            'isArabic' => new external_value(PARAM_RAW, 'Language in use',VALUE_DEFAULT,false),
            'pageNumber' => new external_value(PARAM_INT, 'Page Number',VALUE_DEFAULT,1),
            'pageSize' => new external_value(PARAM_INT, 'Page Size',VALUE_DEFAULT,5),
            'startDate' => new external_value(PARAM_RAW, 'startDate',VALUE_DEFAULT,null),
            'endDate' => new external_value(PARAM_RAW, 'endDate',VALUE_DEFAULT,null),
            'query' => new external_value(PARAM_RAW, 'query',VALUE_DEFAULT,null),
            'SectorIds' => new external_value(PARAM_RAW, 'SectorIds',VALUE_DEFAULT,null),
            'JobFamilyIds' => new external_value(PARAM_RAW, 'JobFamilyIds',VALUE_DEFAULT,null),
            'TrainingTypeId' => new external_value(PARAM_TEXT, 'TrainingTypeId',VALUE_DEFAULT,null),
            'isDescending' => new external_value(PARAM_RAW, 'isDescending',VALUE_DEFAULT,false),
            'CompetencyLevelId' => new external_value(PARAM_TEXT, 'CompetencyLevelId',VALUE_DEFAULT,null),
            'CompetencyId' => new external_value(PARAM_TEXT, 'CompetencyId',VALUE_DEFAULT,null),
            'OrganizationPartnerId' => new external_value(PARAM_TEXT, 'OrganizationPartnerId',VALUE_DEFAULT,null),
            'isSponsored' => new external_value(PARAM_RAW, 'isSponsored',VALUE_DEFAULT,null),
        ]);
    }
    public static function getallprograms($isArabic,$pageNumber,$pageSize,$startDate,$endDate,$query,$SectorIds,$JobFamilyIds,$TrainingTypeId,$isDescending,$CompetencyLevelId,$CompetencyId,$OrganizationPartnerId,$isSponsored) {
        global $DB, $PAGE, $CFG;
        $context = context_system::instance();   
        $params = self::validate_parameters(
            self::getallprograms_parameters(),
            [
                'isArabic' => $isArabic,
                'pageNumber' => $pageNumber,
                'pageSize' => $pageSize,
                'startDate' => $startDate,
                'endDate' => $endDate,
                'query' => $query,
                'SectorIds' => $SectorIds,
                'JobFamilyIds' => $JobFamilyIds,
                'TrainingTypeId' => $TrainingTypeId,
                'isDescending' => $isDescending,
                'CompetencyLevelId' => $CompetencyLevelId,
                'CompetencyId' => $CompetencyId,
                'OrganizationPartnerId' => $OrganizationPartnerId,
                'isSponsored' => $isSponsored,
            ]
        );
        $stable = new \stdClass();
        $stable->thead = false;
        $stable->start = ($pageNumber > 1)? (($pageNumber-1)* $pageSize) : 0;
        $stable->length = $pageSize;
        $stable->isArabic = $isArabic;
        $stable->startDate = $startDate;
        $stable->endDate = $endDate;
        $stable->query = $query;
        $stable->SectorIds = $SectorIds;
        $stable->JobFamilyIds = $JobFamilyIds;
        $stable->TrainingTypeId = $TrainingTypeId;
        $stable->isDescending = $isDescending;
        $stable->CompetencyLevelId = $CompetencyLevelId;
        $stable->CompetencyId = $CompetencyId;

        
        $data = (new local_trainingprogram\local\trainingprogram)->get_allprograms($stable);
        if ($data){
            $event = \local_trainingprogram\event\success_response::create(
                array(
                'context'=>$context,
                'objectid' => 1,
                'other'=>array(
                    'Function'=>'local_trainingprogram_getallprograms',
                    'Success'=>'Successfully Fetched the Response'
                )
                )
            );
            $event->trigger();
            return ['pageData' =>$data['programs'],'totalItemCount'=>$data['totalprograms'],'pageSize'=>$pageSize,'pageNumber'=>$pageNumber];
        } else{
            $event = \local_trainingprogram\event\error_response::create(
                array( 
                    'context'=>$context,
                    'objectid' =>0,
                    'other'=>array(
                        'Function'=>'local_trainingprogram_getallprograms',
                        'Error'=>'Invalid Response Value Detected'       
                    )
                    )
                );  
            $event->trigger();
        }
    }
    public static function getallprograms_returns() {
        return new external_single_structure([
          'pageData' => new external_multiple_structure(
               new external_single_structure(
                   array(
                        'activityType' => new external_value(PARAM_TEXT, 'activityType'),
                        'activityID' => new external_value(PARAM_INT, 'activityID'),
                        'name' => new external_value(PARAM_TEXT, 'Program Name'),
                        'description' => new external_value(PARAM_RAW, 'Program Description'),
                        'date' => new external_value(PARAM_RAW, 'Program Date'),
                        'location' => new external_value(PARAM_TEXT, 'Program location'),
                        'detailsPageURL' => new external_value(PARAM_URL, 'Program detailsPageURL'),
                        'imgDataURL' => new external_value(PARAM_URL, 'Program imgDataURL'),
                        'competencyLevelId' => new external_value(PARAM_INT, 'Program competencyLevelId'),
                        'competencyLevelName' => new external_value(PARAM_TEXT, 'Program competencyLevelIName'),
                        'organizationPartner' => new external_multiple_structure(
                            new external_single_structure(
                                array(
                                'name' => new external_value(PARAM_TEXT, 'name'),
                                'description' => new external_value(PARAM_RAW, 'description'),
                                'value' => new external_value(PARAM_INT, 'ID'),
                            
                                )
                            )
                        ), '', VALUE_OPTIONAL,
                        'programFees' => new external_value(PARAM_RAW, 'Program Fee'),
                        'amount' => new external_value(PARAM_RAW, 'Program amount'),
                        'discountPenalty' => new external_value(PARAM_RAW, 'discountPenalty'),
                        'discountAmount' => new external_value(PARAM_RAW, 'discountAmount'),
                        'vat' => new external_value(PARAM_RAW, 'vat'),
                        'totalAmount' => new external_value(PARAM_RAW, 'totalAmount'),
                        'isPercentage' => new external_value(PARAM_RAW, 'isPercentage'),
                        'isSponsored' => new external_value(PARAM_RAW, 'isSponsored'),
                        'firstofferingdate' => new external_value(PARAM_RAW, 'firstofferingdate'),
                    )
                )
            ),
          'totalItemCount' => new external_value(PARAM_INT, 'total number of programs in result set'),
          'pageSize' => new external_value(PARAM_INT, 'pageSize'),
          'pageNumber' => new external_value(PARAM_INT, 'pageNumber'),
        ]);
    }


    public static function trainingattachment_parameters(){
        return new external_function_parameters([
            'attachmentId' => new external_value(PARAM_INT, 'attachmentId'),
            'returnType' => new external_value(PARAM_INT, 'returnType',
                VALUE_DEFAULT, 1), 

        ]);
    }

  public static  function trainingattachment($attachmentId , $returnType = 1){
      global $DB;  
      $context = context_system::instance();      
      $params = self::validate_parameters(
        self::trainingattachment_parameters(),
        [
            'attachmentId' => $attachmentId,
            'returnType' => $returnType,         
        ]
      );      
        $stable = new \stdClass();
        $stable->thead = false;
        $stable->attachmentId = $attachmentId;
        $stable->returnType = $returnType;       
        $data = (new local_trainingprogram\local\trainingprogram)->training_attachment($stable);  
        if($data){
            $event = \local_trainingprogram\event\success_response::create(
                array(
                'context'=>$context,
                'objectid' => 1,
                'other'=>array(
                    'Function'=>'local_trainingprogram_trainingattachment',
                    'Success'=>'Successfully Fetched the Response'
                )
                )
            );
            $event->trigger();
            return  $data;     
        } else{
            $event = \local_trainingprogram\event\error_response::create(
                array( 
                    'context'=>$context,
                    'objectid' =>0,
                    'other'=>array(
                        'Function'=>'local_trainingprogram_trainingattachment',
                        'Error'=>'Invalid Response Value Detected'       
                    )
                    )
                );  
            $event->trigger();
        }

        

    }
    
    public static function trainingattachment_returns() {
        return new external_single_structure([

                'id' => new external_value(PARAM_INT, 'id'), 
                'fileName' => new external_value(PARAM_RAW, 'fileName'), 
                'extention' => new external_value(PARAM_RAW, 'extention'), 
                'contentType' => new external_value(PARAM_RAW, 'contentType'),
                'Thumbnail' => new external_value(PARAM_RAW, 'Thumbnail'),
                'Content' => new external_value(PARAM_RAW, 'Content'),
            
            
        ]);
    }

    public static function eventattachment_parameters(){
        return new external_function_parameters([
            'attachmentId' => new external_value(PARAM_INT, 'attachmentId'),
            'returnType' => new external_value(PARAM_INT, 'returnType',
                VALUE_DEFAULT, 1), 

        ]);
    }

  public static  function eventattachment($attachmentId , $returnType = 1){
      global $DB;     
      $context = context_system::instance(); 
      $params = self::validate_parameters(
        self::eventattachment_parameters(),
        [
            'attachmentId' => $attachmentId,
            'returnType' => $returnType,         
        ]
      );      
        $stable = new \stdClass();
        $stable->thead = false;
        $stable->attachmentId = $attachmentId;
        $stable->returnType = $returnType;       
        $data = (new local_trainingprogram\local\trainingprogram)->event_attachment($stable);  
        if( $data) {
            $event = \local_trainingprogram\event\success_response::create(
                array(
                'context'=>$context,
                'objectid' => 1,
                'other'=>array(
                    'Function'=>'local_trainingprogram_eventattachment',
                    'Success'=>'Successfully Fetched the Response'
                )
                )
            );
            $event->trigger();
            return  $data;   
        } else {
            $event = \local_trainingprogram\event\error_response::create(
                array( 
                    'context'=>$context,
                    'objectid' =>0,
                    'other'=>array(
                        'Function'=>'local_trainingprogram_eventattachment',
                        'Error'=>'Invalid Response Value Detected'       
                    ) 
                    )
                );  
            $event->trigger();
        }
          
    }
    
    public static function eventattachment_returns() {
        return new external_single_structure([

                'id' => new external_value(PARAM_INT, 'id'), 
                'fileName' => new external_value(PARAM_RAW, 'fileName'), 
                'extention' => new external_value(PARAM_RAW, 'extention'), 
                'contentType' => new external_value(PARAM_RAW, 'contentType'),
                'Thumbnail' => new external_value(PARAM_RAW, 'Thumbnail'),
                'Content' => new external_value(PARAM_RAW, 'Content'),
            
            
        ]);
    }


    public static function checkcertificate_parameters(){
        return new external_function_parameters([
            'issueNumber' => new external_value(PARAM_TEXT, 'issueNumber'),
            'isArabic' => new external_value(PARAM_RAW, 'Language in use',VALUE_DEFAULT,false),

        ]);
    }

  public static  function checkcertificate($issueNumber, $isArabic){
      global $DB;  
      $context = context_system::instance();      
      $params = self::validate_parameters(
        self::checkcertificate_parameters(),
        [
            'issueNumber' => $issueNumber,
            'isArabic' => $isArabic,         
        ]
      );      
        $stable = new \stdClass();
        $stable->thead = false;
        $stable->issueNumber = $issueNumber;
        $stable->isArabic = $isArabic;       
        $data = (new local_trainingprogram\local\trainingprogram)->check_certificate($stable); 
        if($data) {
            $event = \local_trainingprogram\event\success_response::create(
                array(
                'context'=>$context,
                'objectid' => 1,
                'other'=>array(
                    'Function'=>'local_trainingprogram_checkcertificate',
                    'Success'=>'Successfully Fetched the Response'
                )
                )
            );
            $event->trigger();
            return  $data;        
        } else{
            $event = \local_trainingprogram\event\error_response::create(
                array( 
                    'context'=>$context,
                    'objectid' =>0,
                    'other'=>array(
                        'Function'=>'local_trainingprogram_checkcertificate',
                        'Error'=>'Invalid Response Value Detected'       
                    )
                    )
                );  
            $event->trigger();
        } 

              
    }
    
    public static function checkcertificate_returns() {
        return new external_single_structure([

                'fullUserName' => new external_value(PARAM_TEXT, 'fullUserName'), 
                'issueNumber' => new external_value(PARAM_TEXT, 'issueNumber'), 
                'expirationTimeInYears' => new external_value(PARAM_INT, 'expirationTimeInYears'), 
                'issueDate' => new external_value(PARAM_TEXT, 'issueDate'), 
                'titleAr' => new external_value(PARAM_TEXT, 'titleAr'), 
                'titleEn' => new external_value(PARAM_TEXT, 'titleEn'), 
                'certificateTypeId' => new external_value(PARAM_INT, 'certificateTypeId'), 
                'certificateTypeName' => new external_value(PARAM_TEXT, 'certificateTypeName'), 
                'prerequisiteCertificateExams' => new external_multiple_structure(
                            new external_single_structure(
                                array(
                                'name' => new external_value(PARAM_TEXT, 'name'),
                                'description' => new external_value(PARAM_RAW, 'description'),
                                'value' => new external_value(PARAM_INT, 'ID'),
                            
                                )
                            )
                ), '', VALUE_OPTIONAL,

                'prerequisiteCertificateTrainingCourses' => new external_multiple_structure(
                            new external_single_structure(
                                array(
                                'name' => new external_value(PARAM_TEXT, 'name'),
                                'description' => new external_value(PARAM_RAW, 'description'),
                                'value' => new external_value(PARAM_INT, 'ID'),
                            
                                )
                            )
                ), '', VALUE_OPTIONAL,

                'prerequisiteCertificateEvents' => new external_multiple_structure(
                            new external_single_structure(
                                array(
                                'name' => new external_value(PARAM_TEXT, 'name'),
                                'description' => new external_value(PARAM_RAW, 'description'),
                                'value' => new external_value(PARAM_INT, 'ID'),
                            
                                )
                            )
                ), '', VALUE_OPTIONAL,
               
            
            
        ]);
    }

    public static function get_trainingstatistic_parameters(){

        return new external_function_parameters([

            'offset' => new external_value(PARAM_INT, 'Number of items to skip from the begging of the result set',

                VALUE_DEFAULT, 0),

            'limit' => new external_value(PARAM_INT, 'Maximum number of results to return',

                VALUE_DEFAULT, 0),



        ]);

  }


  public static  function get_trainingstatistic($offset = 0, $limit = 0){

      global $DB; 
      $context = context_system::instance();    

      $params = self::validate_parameters(

        self::get_trainingstatistic_parameters(),

        [

            'offset' => $offset,

            'limit' => $limit,         

        ]

    );      

        $offset = $params['offset'];

        $limit =$params['limit'];      

        $stable = new \stdClass();

        $stable->thead = false;

        $stable->start = $offset;

        $stable->length = $limit;       

        $data = (new local_trainingprogram\local\trainingprogram)->trainingstatistic($stable);            
        if($data){
            $event = \local_trainingprogram\event\success_response::create(
                array(
                'context'=>$context,
                'objectid' => 1,
                'other'=>array(
                    'Function'=>'local_trainingprogram_get_training_statistic',
                    'Success'=>'Successfully Fetched the Response'
                )
                )
            );
            $event->trigger();
            return  $data; 
        } else{
            $event = \local_trainingprogram\event\error_response::create(
                array( 
                    'context'=>$context,
                    'objectid' =>0,
                    'other'=>array(
                        'Function'=>'local_trainingprogram_get_training_statistic',
                        'Error'=>'Invalid Response Value Detected'       
                    )
                    )
                );  
            $event->trigger();
        }
            



    }

    public static function get_trainingstatistic_returns() {

    return new external_multiple_structure(

        new external_single_structure(

            array(

                'year' => new external_value(PARAM_INT, 'year'),

                'noOfPlans' => new external_value(PARAM_INT, 'numberOfEvents'),

                'inClassTrainingPlans' => new external_value(PARAM_INT, 'onLineTrainingPlans'),

                'onLineTrainingPlans' => new external_value(PARAM_INT, 'onLineTrainingPlans'),

                 'numberOfTrainees' => new external_value(PARAM_INT, 'numberOfTrainees'),

       

             )

         )

            );

        }





    public static function get_competencies_parameters(){
        return new external_function_parameters([
            'level' => new external_value(PARAM_RAW, 'Level',VALUE_DEFAULT,0),
            'ctypes' => new external_value(PARAM_RAW, 'Competency types'),
            'competencies' => new external_value(PARAM_RAW, 'competencies'),
        ]);
    }

    public static  function get_competencies($level=0, $ctypes, $competencies){
        global $DB; 
        $context = context_system::instance();    

        $params = self::validate_parameters(
            self::get_competencies_parameters(),
            [
                'level' => $level,
                'ctypes' => $ctypes,
                'competencies' => $competencies
            ]
        );

        $data = (new local_trainingprogram\local\trainingprogram)->get_competencies($params['level'], $params['ctypes'], $params['competencies']);
        return $data;
    }

    public static function get_competencies_returns() {
        return new external_multiple_structure(
            new external_single_structure(
                array(
                    'id' => new external_value(PARAM_INT, 'id', VALUE_OPTIONAL),
                )
            )
        );
    } 


    //get organization users ...renu


     public static function get_organization_users_parameters(){
        return new external_function_parameters([
            'organizations' => new external_value(PARAM_RAW, 'organizations',VALUE_DEFAULT,0),
            'programid' => new external_value(PARAM_RAW, 'programid',VALUE_DEFAULT,0),
            'offeringid' => new external_value(PARAM_RAW, 'offeringid',VALUE_DEFAULT,0),
            
        ]);
    }

    public static  function get_organization_users($organizations,$programid,$offeringid){
        global $DB; 

        $context = context_system::instance();    
        $fullname = (new \local_trainingprogram\local\trainingprogram())->user_fullname_case();
        $get_orgtrainer=$DB->get_records_sql("SELECT u.id, $fullname FROM {local_users} lc JOIN {user} u ON u.id = lc.userid JOIN {role_assignments} ra ON ra.userid=u.id JOIN {role} r ON r.id=ra.roleid WHERE lc.organization=$organizations AND r.shortname='trainer'");
        $data = array();
        foreach($get_orgtrainer as $trainer){  
            $useridexists = $DB->record_exists_sql("SELECT * FROM {program_enrollments} WHERE programid=$programid AND offeringid= $offeringid AND userid= $trainer->id ");
            if($useridexists){
                continue;
            }
            else{
            $data[] =['id' => $trainer->id, 'name' => $trainer->fullname];  
            }
        }
  

        return ['status' => true, 'data' => $data];

    }

    public static function get_organization_users_returns() {
          
         return new external_single_structure(
            array(
                'status' => new external_value(PARAM_INT, 'status: true if success'),
                'data' => new external_multiple_structure(new external_single_structure(
                    array(
                        'id' => new external_value(PARAM_INT, 'status: true if success',VALUE_OPTIONAL),
                        'name' => new external_value(PARAM_RAW, 'status: true if success',VALUE_OPTIONAL)
                      )
                   ),'',VALUE_OPTIONAL) 
             ));
     

    } 


    public static function orgofficialprogramview_parameters(){
        return new external_function_parameters([
            'programID' => new external_value(PARAM_INT, 'programID'),
            'isArabic' => new external_value(PARAM_RAW, 'Language',VALUE_DEFAULT,false),

        ]);    
            
    }
    public static  function orgofficialprogramview($programID, $isArabic){
        global $DB; 
        $context = context_system::instance();    

        $params = self::validate_parameters(
            self::orgofficialprogramview_parameters(),
            [
               
                'programID' => $programID,
                'isArabic' => $isArabic
            ]
        );

        $data = (new local_trainingprogram\local\trainingprogram)->orgofficialprogram_view($programID, $isArabic);
        return $data;
    }
    public static function orgofficialprogramview_returns() {
        return new external_single_structure([
            'programid' => new external_value(PARAM_INT, 'programid'), 
            'programname' => new external_value(PARAM_TEXT, 'programname'), 
            'programcode' => new external_value(PARAM_TEXT, 'programcode'), 
            'offerings' => new external_multiple_structure(
                new external_single_structure(
                    array(
                    'id' => new external_value(PARAM_INT, 'id'),
                    'code' => new external_value(PARAM_TEXT, 'code'),
                    'totalseats' => new external_value(PARAM_INT, 'totalseats'),
                    'availableseats' => new external_value(PARAM_INT, 'availableseats'),
                    'sellingprice' => new external_value(PARAM_INT, 'sellingprice'),
                    'actualprice' => new external_value(PARAM_INT, 'actualprice'),
                    'type' => new external_value(PARAM_TEXT, 'type'),
                    'method' => new external_value(PARAM_TEXT, 'method'),
                    'startdate' => new external_value(PARAM_TEXT, 'startdate'),
                    'enddate' => new external_value(PARAM_TEXT, 'enddate'),
                    'starttime' => new external_value(PARAM_TEXT, 'starttime'),
                    'endtime' => new external_value(PARAM_TEXT, 'endtime'),
                    'hallinfo' => new external_single_structure([
                                    'id' => new external_value(PARAM_INT, 'value'),
                                    'hallname' => new external_value(PARAM_TEXT, 'hallname'),                                       
                                    'buildingname' => new external_value(PARAM_TEXT, 'buildingname'),
                                    'city' => new external_value(PARAM_TEXT, 'city'),
                                    'seatingcapacity' => new external_value(PARAM_INT, 'seatingcapacity'),
                                    'seats' => new external_value(PARAM_INT, 'seats'),
                                    'servicefor' => new external_value(PARAM_TEXT, 'servicefor'),
        
                                    
                    ]),  
                    'action' => new external_value(PARAM_TEXT, 'action'),
                    'seatsdata' => new external_value(PARAM_TEXT, 'seatsdata'),
                    )
                )
            )

        ]);
    } 


    public static function get_trainingtypes_parameters() {
        return new external_function_parameters(
            array(
                'isArabic' => new external_value(PARAM_RAW, 'isArabic', VALUE_DEFAULT, false),
            )
        );
    } 

    public static function get_trainingtypes($isArabic){
        global $DB, $CFG, $USER, $PAGE, $SESSION;
        
        
        $params = self::validate_parameters(self::get_trainingtypes_parameters(),
                                            ['isArabic' => $isArabic]); 
        $SESSION->lang = ($isArabic == 'true') ? 'ar' : 'en';

        $strings = array("1"=>"{mlang en}Virtual Training (Synchronous){mlang}{mlang ar}تدريب إفتراضي (تزامني{mlang}","2"=>"{mlang en}In-class Training'{mlang}{mlang ar}تدريب صفي{mlang}","3"=>"{mlang en}Digital Training (self-paced){mlang}{mlang ar}تدريب رقمي (تعلم ذاتي){mlang}");
        $data = array();
        $count = 0;
        foreach($strings as $key => $string)
        {
            $data[$count]['value'] = $key;
            $data[$count]['name'] = format_string($string);
            $data[$count]['description']= '';
            $count++;
        }
        
        return $data;
        
    }

    public static function get_trainingtypes_returns() {
        return new external_multiple_structure(
            new external_single_structure(
                array(
                    'name' => new external_value(PARAM_TEXT, 'name', VALUE_OPTIONAL),
                    'description' => new external_value(PARAM_TEXT, 'description', VALUE_OPTIONAL),
                    'value' =>new external_value(PARAM_INT, 'id', VALUE_OPTIONAL),
                )

            )
        );

    }

    public static function viewetrainingtopicsdata_parameters() {
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
    public static function viewetrainingtopicsdata($options, $dataoptions, $offset = 0, $limit = 0, $contextid, $filterdata) {
        global $DB, $PAGE, $CFG;
        require_login();
        $params = self::validate_parameters(
            self::viewetrainingtopicsdata_parameters(),
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
        $data = (new local_trainingprogram\local\trainingprogram)->get_listof_trainingtopics($stable, $filtervalues);

        $totalcount = $data['totaltrainingtopics'];
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

    public static function viewetrainingtopicsdata_returns() {
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
                                'name' => new external_value(PARAM_RAW, 'name'),
                                'displayname' => new external_value(PARAM_RAW, 'displayname'),
                                'usercreated' => new external_value(PARAM_TEXT, 'usercreated'),
                                'timecreated' => new external_value(PARAM_TEXT, 'timecreated'),
                            )
                        )
                    ),
                    'request_view' => new external_value(PARAM_INT, 'request_view', VALUE_OPTIONAL),
                    'nocourses' => new external_value(PARAM_BOOL, 'nocourses', VALUE_OPTIONAL),
                    'totalearlyregistrations' => new external_value(PARAM_INT, 'totalearlyregistrations', VALUE_OPTIONAL),
                    'length' => new external_value(PARAM_INT, 'length', VALUE_OPTIONAL),
                )
            )
        ]);
    }

     public static function deletetrainingtopic_parameters(){
        return new external_function_parameters(
            array(
                'id' => new external_value(PARAM_INT,'Topic id',0),
            )
        );
    }
    public static  function deletetrainingtopic($id){
        global $CFG;
        global $USER;
        global $DB;
        $params=self::validate_parameters(
            self::deletetrainingtopic_parameters(),
            array('id'=>$id)
        );
        $systemcontext = context_system::instance();
        self::validate_context($systemcontext);
       if ($id) {
           (new local_trainingprogram\local\trainingprogram)->remove_trainingtopics($id);
        } else {
          throw new moodle_exception('Error in submission');
        }
        return true;    
    }
    public static function deletetrainingtopic_returns() {
        return new external_value(PARAM_BOOL, 'return');
    } 

    public static function get_trainingtopics_parameters(){

        return new external_function_parameters([
            'isArabic' => new external_value(PARAM_RAW, 'isArabic', VALUE_DEFAULT, false),
            'programid' => new external_value(PARAM_RAW, 'programid', VALUE_DEFAULT, false),

        ]);
    }
        
    public static  function get_trainingtopics($isArabic,$programid = null){
      global $DB;
      require_login();
      $context = context_system::instance();
      $params = self::validate_parameters(
            self::get_trainingtopics_parameters(),
            [
                
                'isArabic' => $isArabic,
                'programid' => $programid,
              
            ]
        );    

        $stable = new \stdClass();
        $stable->thead = false;
        $stable->isArabic = $isArabic;
        $stable->programid = $programid;
        
        $data = (new local_trainingprogram\local\trainingprogram)->getalltrainingtopics($stable); 
         
            return $data;
        

    }
    public static function get_trainingtopics_returns() {
        return new external_multiple_structure(
            new external_single_structure(
                array(

                    'name' => new external_value(PARAM_TEXT, 'Name'), 
                    'value' => new external_value(PARAM_INT, 'Value'),  

                )

            )
        );

    }
    public static function gettrainingflyer_parameters(){
        return new external_function_parameters([
            'isArabic' => new external_value(PARAM_RAW, 'isArabic', VALUE_DEFAULT, false),
            'programCode' => new external_value(PARAM_RAW, 'programCode'),
            'offeringCode' => new external_value(PARAM_RAW, 'offeringCode', VALUE_DEFAULT, null),
        ]);
    } 
    public static  function gettrainingflyer($isArabic,$programCode,$offeringCode){
      global $DB;
      require_login();
      $context = context_system::instance();
      $params = self::validate_parameters(
            self::gettrainingflyer_parameters(),
            [
                'isArabic' => $isArabic,
                'programCode' => $programCode,
                'offeringCode' => $offeringCode,
            ]
        );  

        $iFilter = new \stdClass();
        $iFilter->isArabic = $isArabic;
        $iFilter->programCode = $programCode;  
        $iFilter->offeringCode = $offeringCode;  
        $data = (new local_trainingprogram\local\trainingprogram)->get_trainingflyer($iFilter); 
        return $data;
    }
    public static function gettrainingflyer_returns() {
        return new external_value(PARAM_RAW, 'return');
    }
    public static function viewprogramgoals_parameters() {
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
 
    public static function viewprogramgoals($options, $dataoptions, $offset = 0, $limit = 0, $filterdata) {
        global $OUTPUT, $CFG, $DB,$USER,$PAGE;
        $sitecontext = context_system::instance();
        $PAGE->set_context($sitecontext);
        $params = self::validate_parameters(
            self::viewprogramgoals_parameters(),
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
        $stable->start = $offset;
        $stable->length = $limit;
        $programgoalsdata = (new local_trainingprogram\local\trainingprogram)->get_all_program_goals($stable,$filtervalues);
        return [
            'totalcount' => $programgoalsdata['totalcount'],
            'records' => $programgoalsdata['goals'],
            'options' => $options,
            'dataoptions' => $dataoptions,
            'filterdata' => $filterdata,
            'programid' => $filtervalues->programid,
            'nodata' => get_string('nogoals','local_trainingprogram')
        ];
    }

    /**
     * Returns description of method result value.
     */
    public static function  viewprogramgoals_returns() {
        return new external_single_structure([
            'options' => new external_value(PARAM_RAW, 'The paging data for the service'),
            'programid' => new external_value(PARAM_INT, 'Program id'),
            'dataoptions' => new external_value(PARAM_RAW, 'The data for the service'),
            'totalcount' => new external_value(PARAM_INT, 'total number of goals in result set'),
            'filterdata' => new external_value(PARAM_RAW, 'The data for the service'),
            'nodata' => new external_value(PARAM_RAW, 'The data for the service'),
            'records' => new external_multiple_structure(
                new external_single_structure(
                    array(
                        'id' => new external_value(PARAM_INT, 'id'),
                        'programid' => new external_value(PARAM_INT, 'programid'),   
                        'programgoal' => new external_value(PARAM_RAW, 'programgoal'),   
                    )
                )
            ),
        ]);
    } 

    public static function deleteprogramgoals_parameters(){

          return new external_function_parameters(
            array(
                'responseid' => new external_value(PARAM_INT,'responseid',0),
            )
        );
    }

    public static  function deleteprogramgoals($responseid){
        global $DB;
        $params = self::validate_parameters (
               self:: deleteprogramgoals_parameters(),array('responseid'=>$responseid));
        $context = context_system::instance();
        self::validate_context($context);
        if($responseid) {
           $delete = $DB->delete_records('program_goals',array('id' => $responseid));
        } else {
            throw new moodle_exception('Error');
        }
        return true;
    }

    public static function deleteprogramgoals_returns() {
         return new external_value(PARAM_BOOL, 'return');
    }

       public static function viewrefundsetting_parameters() {
        return new external_function_parameters([
            'options' => new external_value(PARAM_RAW, 'The paging data for the service'),
            'dataoptions' => new external_value(PARAM_RAW, 'The data for the service'),
            'offset' => new external_value(PARAM_INT, 'Number of items to skip from the begging of the result set',
                VALUE_DEFAULT, 0),
            'limit' => new external_value(PARAM_INT, 'Maximum number of results to return',
                VALUE_DEFAULT, 0),
            'contextid' => new external_value(PARAM_INT, 'contextid',VALUE_DEFAULT, 1),
            'filterdata' => new external_value(PARAM_RAW, 'filters applied'),
        ]);
    }
    public static function viewrefundsetting($options, $dataoptions, $offset = 0, $limit = 0, $contextid, $filterdata) {
        global $DB, $PAGE, $CFG;
        require_login();
        $params = self::validate_parameters(
            self::viewrefundsetting_parameters(),
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
        $data = (new local_trainingprogram\local\refundsettings)->get_listof_refundsettings($stable, $filtervalues);


        $totalcount = $data['totalrefundsettings'];
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
    public static function viewrefundsetting_returns() {
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
                                'type' => new external_value(PARAM_RAW, 'type'),
                                'entitytype' => new external_value(PARAM_RAW, 'entitytype'),
                                'dayfrom' => new external_value(PARAM_INT, 'dayfrom'),
                                'dayto' => new external_value(PARAM_INT, 'dayto'),
                                'dedtype' => new external_value(PARAM_RAW, 'dedtype'),
                                'dedvalue' => new external_value(PARAM_RAW, 'dedvalue'),
                                'action' => new external_value(PARAM_RAW, 'action'),
                                'ownedby' => new external_value(PARAM_RAW, 'ownedby', VALUE_OPTIONAL),
                                'moreattempts' => new external_value(PARAM_RAW, 'moreattempts', VALUE_OPTIONAL),
                            )
                        )
                    ),
                    'request_view' => new external_value(PARAM_INT, 'request_view', VALUE_OPTIONAL),
                    'nocourses' => new external_value(PARAM_BOOL, 'nocourses', VALUE_OPTIONAL),
                    'totalrefundsettings' => new external_value(PARAM_INT, 'totalrefundsettings', VALUE_OPTIONAL),
                    'length' => new external_value(PARAM_INT, 'length', VALUE_OPTIONAL),
                ),'',VALUE_OPTIONAL
            )
        ]);
    }

    public static function deleterefundsetting_parameters(){

          return new external_function_parameters(
            array(
                'id' => new external_value(PARAM_INT,'id',0),
            )
        );
    }

    public static  function deleterefundsetting($id){
        global $DB;
        $params = self::validate_parameters (
               self:: deleterefundsetting_parameters(),array('id'=>$id));
        $context = context_system::instance();
        self::validate_context($context);
        if($id) {
           $delete = $DB->delete_records('refund_settings',array('id' => $id));
        } else {
            throw new moodle_exception('Error');
        }
        return true;
    }

    public static function deleterefundsetting_returns() {
         return new external_value(PARAM_BOOL, 'return');
    } 

    public static function replaceprogramuser_parameters(){
        return new external_function_parameters(
            array(
                'programid' => new external_value(PARAM_INT, 'programid'),
                'offeringid' => new external_value(PARAM_INT, 'offeringid'),
                'productid' => new external_value(PARAM_INT, 'productid'),
                'fromuserid' => new external_value(PARAM_INT, 'fromuserid'),
                'touserid' => new external_value(PARAM_INT, 'touserid'),
                'replacementfee' => new external_value(PARAM_RAW, 'replacementfee'),
            )
        );
    }  
    public static function replaceprogramuser($programid, $offeringid,$productid, $fromuserid, $touserid, $replacementfee){
        global $DB;
        $params = self::validate_parameters(self::replaceprogramuser_parameters(),
                                    ['programid' => $programid, 
                                    'offeringid' => $offeringid, 
                                    'productid' => $productid , 
                                    'fromuserid' => $fromuserid ,
                                    'touserid' => $touserid , 
                                    'replacementfee' => $replacementfee, 
                                  ]
                                );
        $context = context_system::instance();
        if($programid && $offeringid && $productid) {
            $data =new stdClass();
            $data->programid = $programid;
            $data->offeringid = $offeringid;
            $data->productid = $productid;
            $data->fromuserid = $fromuserid;
            $data->touserid = $touserid;
            $data->replacementfee = $replacementfee;
           (new local_trainingprogram\local\trainingprogram)->program_replacement_process($data);
        } else {

            throw new moodle_exception('Error in replacing the user');
        }
        return true;
    }   
    public static function replaceprogramuser_returns() {
        return new external_value(PARAM_BOOL, 'return');
    }
    public static function getdataforprogramcancellation_parameters() {
        return new external_function_parameters(
            array(
                'productid' => new external_value(PARAM_INT, 'programid'),
                'userid' => new external_value(PARAM_INT, 'userid'),
                'date' => new external_value(PARAM_INT, 'date'),
                'policyconfirm' => new external_value(PARAM_INT, 'policyconfirm', VALUE_OPTIONAL),
            )
        );
    }
    public static function getdataforprogramcancellation($productid,$userid,$date,$policyconfirm = 0){
        global $DB;
        $params = self::validate_parameters(self::getdataforprogramcancellation_parameters(),
                                    ['productid' => $productid,
                                     'userid' => $userid,
                                     'date' => $date,
                                     'policyconfirm'=>$policyconfirm
                                    ]);
        $context = context_system::instance();
        if($productid && $userid){
            $sdata =new stdClass();
            $sdata->productid = $productid;
            $sdata->userid = $userid;
            $sdata->programdate = $date;
            $sdata->policyconfirm = $policyconfirm;
            $returndata=(new local_trainingprogram\local\trainingprogram)->get_dataforprogramcancellation($sdata);

            return $returndata;
        } else {
            throw new moodle_exception('Error while getting the data');
        }
    }   
    public static function getdataforprogramcancellation_returns() {
        return new external_single_structure([
            'productid' => new external_value(PARAM_INT, 'productid', VALUE_OPTIONAL),
            'orgofficialenrolled' => new external_value(PARAM_INT, 'orgofficialenrolled', VALUE_OPTIONAL),
            'traineeenrolled' => new external_value(PARAM_INT, 'traineeenrolled', VALUE_OPTIONAL),
            'amount' => new external_value(PARAM_RAW, 'amount', VALUE_OPTIONAL),
            'refundamount' => new external_value(PARAM_RAW, 'refundamount', VALUE_OPTIONAL),
            'deductamount' => new external_value(PARAM_RAW, 'deductamount', VALUE_OPTIONAL),
            'invoicenumber' => new external_value(PARAM_RAW, 'invoicenumber', VALUE_OPTIONAL),
        ]);
    }
    public static function cancelprogramuser_parameters(){
        return new external_function_parameters(
            array(
                'programid' => new external_value(PARAM_INT, 'programid'),
                'offeringid' => new external_value(PARAM_INT, 'offeringid'),
                'userid' => new external_value(PARAM_INT, 'userid'),
                'programprice' => new external_value(PARAM_INT, 'programprice'),
                'amount' => new external_value(PARAM_RAW, 'amount'),
                'refundamount' => new external_value(PARAM_RAW, 'refundamount'),
                'newinvoiceamount' => new external_value(PARAM_RAW, 'newinvoiceamount'),
                'productid' => new external_value(PARAM_INT, 'productid'),
                'policyconfirm' => new external_value(PARAM_INT, 'policyconfirm'),
                'programdate' => new external_value(PARAM_INT, 'programdate'),
                'invoicenumber' => new external_value(PARAM_INT, 'examdate'),
                'isadmin' => new external_value(PARAM_INT, 'isadmin'),
                'traineeenrolled' => new external_value(PARAM_INT, 'traineeenrolled'),
            )
        );
    }  
    public static function cancelprogramuser($programid, $offeringid, $userid, $programprice, $amount, $refundamount, $newinvoiceamount, $productid, $policyconfirm, $programdate, $invoicenumber,$isadmin,$traineeenrolled){
        global $DB;
        $params = self::validate_parameters(self::cancelprogramuser_parameters(),
                                    ['programid' => $programid, 
                                    'offeringid' => $offeringid, 
                                    'userid' => $userid , 
                                    'programprice' => $programprice ,
                                    'amount' => $amount , 
                                    'refundamount' => $refundamount, 
                                    'newinvoiceamount' => $newinvoiceamount, 
                                    'productid' =>$productid ,
                                    'policyconfirm' => $policyconfirm,
                                    'programdate' =>$programdate,
                                    'invoicenumber' =>$invoicenumber,
                                    'isadmin' =>$isadmin,
                                    'traineeenrolled' =>$traineeenrolled,
                                  ]
                                );
        $context = context_system::instance();

        if($programid && $offeringid && $userid) {
            $data =new stdClass();
            $data->programid = $programid;
            $data->offeringid = $offeringid;
            $data->userid = $userid;
            $data->programprice = $programprice;
            $data->amount = $amount;
            $data->refundamount = $refundamount;
            $data->newinvoiceamount = $newinvoiceamount;
            $data->productid = $productid;
            $data->policyconfirm = $policyconfirm;
            $data->programdate = $programdate;
            $data->invoicenumber = $invoicenumber;
            $data->entitytype = 'trainingprogram';
            $data->isadmin = $isadmin;
            $data->traineeenrolled = $traineeenrolled;
            $traineeroleid = $DB->get_field('role', 'id', array('shortname' => 'trainee'));
            $courseid=$DB->get_field('local_trainingprogram','courseid', array('id' => $programid));
            $policies = new \local_trainingprogram\local\policies('trainingprogram', $programdate, 'cancel');

            if($programprice == 0 || $refundamount <= 0) {
                (new \local_trainingprogram\local\trainingprogram)->program_unenrollment($programid,$offeringid,$courseid,$userid,$traineeroleid,'cancel');
            } else {
                if($traineeenrolled == 1) {
                    $policies->refund_amount($userid, $productid, false, $policyconfirm);
                    (new \local_trainingprogram\local\trainingprogram)->program_unenrollment($programid,$offeringid,$courseid,$userid,$traineeroleid,'cancel');
                } else {
                    $policies->cancel_process($data);
                }
            }
        } else {

            throw new moodle_exception('Error in cancelling');
        }
        return true;
    }   
    public static function cancelprogramuser_returns() {
        return new external_value(PARAM_BOOL, 'return');
    }
    public static function org_enroluser_parameters(){
        return new external_function_parameters(
            array(
                'entityid' => new external_value(PARAM_INT, 'entityid'),
                'referenceid' => new external_value(PARAM_INT, 'referenceid', VALUE_OPTIONAL),
                'type' => new external_value(PARAM_RAW, 'type'),
                'tuserid' => new external_value(PARAM_RAW, 'tuserid'),
                'orderid' => new external_value(PARAM_INT, 'orderid', VALUE_OPTIONAL),
                'discountprice' => new external_value(PARAM_RAW, 'discountprice',VALUE_OPTIONAL),
                'discounttype' => new external_value(PARAM_RAW, 'discounttype',VALUE_OPTIONAL),
                'discounttableid' => new external_value(PARAM_INT, 'orderid',VALUE_OPTIONAL),
                'autoapproval' => new external_value(PARAM_INT, 'autoapproval',VALUE_OPTIONAL),
            )
        );
    }  
    public static function org_enroluser($entityid, $referenceid, $type, $tuserid, $orderid,$discountprice = 0,$discounttype = null,$discounttableid = 0,$autoapproval = 0){
        global $DB,$USER;
        $params = self::validate_parameters(self::org_enroluser_parameters(),
                                    ['entityid' => $entityid, 
                                    'referenceid' => $referenceid, 
                                    'type' => $type , 
                                    'tuserid' => $tuserid ,
                                    'orderid' => $orderid ,
                                    'discountprice' => $discountprice ,
                                    'discounttype' => $discounttype ,
                                    'discounttableid' => $discounttableid ,
                                    'autoapproval'=>$autoapproval
                                  ]
                                );
        $context = context_system::instance();
        $traineeids = explode(',', base64_decode($tuserid));
        if($type == 'program') {
            foreach ($traineeids as $traineeid) {
                $traineeroleid = $DB->get_field('role', 'id', array('shortname' => 'trainee'));
                //Remove below line and add program enrollment function
                (new local_trainingprogram\local\trainingprogram)->program_enrollment($referenceid,$traineeid,$traineeroleid,false,$USER->id,false,$orderid,false,false);
                if($autoapproval > 0) {
                    $productid =(int) $DB->get_field('tool_products','id',['category'=>1,'referenceid'=>$referenceid]);
                    (new product)->update_discount_status($productid,$discounttableid,$discounttype);
                }
                
            }    
            $returndata['response'] = 'success';
            return $returndata;
        } elseif($type == 'event') {
            foreach ($traineeids as $traineeid) {
                //Remove below line and add Event enrollment function
                (new local_events\events)->enrol_event($entityid,$traineeid,$USER->id,null,$orderid);
                if($autoapproval > 0) {
                    $productid =(int) $DB->get_field('tool_products','id',['category'=>3,'referenceid'=>$entityid]);
                    (new product)->update_discount_status($productid,$discounttableid,$discounttype);
                }
            }
            $returndata['response'] = 'success';
            return $returndata;
        } else {

            throw new moodle_exception('Error in cancelling');
        }
    }   
    public static function org_enroluser_returns() {
        return new external_single_structure(
            array(
                'response' => new external_value(PARAM_RAW, 'response'),
            )
        );
    }
    ///Ramanjaneyulu Added
    public static function addcpdinfo_parameters() {
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
 
    public static function addcpdinfo($options, $dataoptions, $offset = 0, $limit = 0, $filterdata) {
       // print_r($dataoptions);
       // exit;
        global $OUTPUT, $CFG, $DB,$USER,$PAGE;
        $sitecontext = context_system::instance();
        $PAGE->set_context($sitecontext);
        $params = self::validate_parameters(
            self::addcpdinfo_parameters(),
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
        $stable->start = $offset;
        $stable->length = $limit;
        $stable->programid = $data_object->programid;
        $competencies = (new local_trainingprogram\local\trainingprogram)->cpd_data($stable,$filtervalues);
        return [
            'totalcount' => $competencies['totalcount'],
            'records' => $competencies['acompetencies'],
            'options' => $options,
            'dataoptions' => $dataoptions,
            'filterdata' => $filterdata,
            'programid' => $data_object->programid,
            'nodata' => get_string('nocpds','local_trainingprogram')
        ];
    }

    /**
     * Returns description of method result value.
     */
    public static function  addcpdinfo_returns() {
        return new external_single_structure([
            'options' => new external_value(PARAM_RAW, 'The paging data for the service'),
            'programid' => new external_value(PARAM_INT, 'Program id'),
            'dataoptions' => new external_value(PARAM_RAW, 'The data for the service'),
            'totalcount' => new external_value(PARAM_INT, 'total number of competencies in result set'),
            'filterdata' => new external_value(PARAM_RAW, 'The data for the service'),
            'nodata' => new external_value(PARAM_RAW, 'The data for the service'),
            'records' => new external_multiple_structure(
                new external_single_structure(
                    array(
                        'id' => new external_value(PARAM_INT, 'competency id'),
                        'cpdname' => new external_value(PARAM_RAW, 'cpdname'),
                        'creditedhrs' => new external_value(PARAM_RAW, 'creditedhrs'),
                        'ctpid' => new external_value(PARAM_RAW, 'ctpid')
                     
                    )
                )
            ),
        ]);
    } 
    ///

     public static function publishorunpublishoffering_parameters(){
        return new external_function_parameters(
            array(
                'id' => new external_value(PARAM_INT,'id'),
                'code' => new external_value(PARAM_RAW,'code'),
                'entitytype' => new external_value(PARAM_RAW,'entitytype'),
                'actiontype' => new external_value(PARAM_RAW,'actiontype'),
            )
        );
    }
    public static  function  publishorunpublishoffering($id,$code,$entitytype,$actiontype){
        $params=self::validate_parameters(
            self:: publishorunpublishoffering_parameters(),
            array(
                'id'=>$id,
                'code'=>$code,
                'entitytype'=>$entitytype,
                'actiontype'=>$actiontype,
            )
        );
        $systemcontext = context_system::instance();
        self::validate_context($systemcontext);
       if ($id && $code && $entitytype && $actiontype) {
          (new local_trainingprogram\local\trainingprogram)->publishorunpublish_enitity($id,$code,$entitytype,$actiontype);
        } else {
          throw new moodle_exception('Error in submission');
        }
        return true;    
    }
    public static function  publishorunpublishoffering_returns() {
        return new external_value(PARAM_BOOL, 'return');
    }

    public static function cancelentity_parameters(){
        return new external_function_parameters(
            array(
                'rootid' => new external_value(PARAM_INT,'rootid'),
                'fieldid' => new external_value(PARAM_INT,'fieldid'),
                'productid' => new external_value(PARAM_INT,'productid'),
                'fieldcode' => new external_value(PARAM_RAW,'fieldcode'),
                'entitytype' => new external_value(PARAM_RAW,'entitytype'),
                'costtype' => new external_value(PARAM_INT,'costtype'),
                'currentuser' => new external_value(PARAM_RAW,'currentuser'),  
                'requesttype' => new external_value(PARAM_RAW,'requesttype'),  
                'hasenrollments' => new external_value(PARAM_INT,'hasenrollments'),  
                              
                
            )
        );
    }
    public static  function  cancelentity($rootid,$fieldid,$productid,$fieldcode,$entitytype,$costtype,$currentuser,$requesttype,$hasenrollments){
        global $DB,$CFG;
        $params=self::validate_parameters(
            self:: cancelentity_parameters(),
            array(
                'rootid'=>$rootid,
                'fieldid'=>$fieldid,
                'productid'=>$productid,
                'fieldcode'=>$fieldcode,
                'entitytype'=>$entitytype,
                'costtype'=>$costtype,
                'currentuser'=>$currentuser,
                'requesttype'=>$requesttype,
                'hasenrollments'=>$hasenrollments
            )
        );
        $systemcontext = context_system::instance();
        self::validate_context($systemcontext);
       if ($rootid && $fieldid && $productid && $entitytype) {
            $data =new stdClass();
            $data->rootid = $rootid;
            $data->fieldid = $fieldid;
            $data->productid = $productid;
            $data->fieldcode = $fieldcode;
            $data->entitytype = $entitytype;
            $data->costtype = $costtype;
            $data->currentuser = $currentuser;
            $data->requesttype = $requesttype;
            $data->hasenrollments = $hasenrollments;
            $response =  (new local_trainingprogram\local\trainingprogram)->cancel_entity($data);
            if($data->entitytype == 'offering') {
                $returnurl = ($data->requesttype == 'cancelentity') ? $CFG->wwwroot.'/local/trainingprogram/index.php' : $CFG->wwwroot.'/local/trainingprogram/entitycancellationrequests.php?entitytype='.$data->entitytype;
            } else {
                $returnurl = ($data->requesttype == 'cancelentity') ? $CFG->wwwroot.'/local/events/index.php' : $CFG->wwwroot.'/local/trainingprogram/entitycancellationrequests.php?entitytype='.$data->entitytype;
            }
            $returndata['returnurl'] = $returnurl;
            $returndata['response'] = ($response) ? $response : 'success';
        } else {
          throw new moodle_exception('Error in submission');
        }
        return $returndata;  
    }
    public static function cancelentity_returns() {
        return new external_single_structure(
            array(
                'returnurl' => new external_value(PARAM_RAW, 'returnurl'),
                'response' => new external_value(PARAM_RAW, 'response')
            )
        );
    }

    public static function entitycancellationrequests_parameters() {
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
    public static function entitycancellationrequests($options, $dataoptions, $offset = 0, $limit = 0, $contextid, $filterdata) {
        global $DB, $PAGE, $CFG;
        require_login();
        $params = self::validate_parameters(
            self::entitycancellationrequests_parameters(),
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
        $entitytype = json_decode($dataoptions)->entitytype;
        $stable = new \stdClass();
        $stable->thead = false;
        $stable->start = $offset;
        $stable->length = $limit;
        $data = (new local_trainingprogram\local\trainingprogram)->get_listof_listofcellationrequests($stable, $filtervalues,$dataoptions);

        $totalcount = $data['totalrequests'];
        return [
           'totalcount' => $totalcount,
            'length' => $totalcount,
            'filterdata' => $filterdata,
            'records' =>$data,
            'options' => $options,
            'dataoptions' => $dataoptions,
            'url' => $CFG->wwwroot,
            'entitytype' => $entitytype,
        ];
    }
    public static function entitycancellationrequests_returns() {
        return new external_single_structure([
          'options' => new external_value(PARAM_RAW, 'The paging data for the service'),
          'url' => new external_value(PARAM_RAW, 'url'),
          'entitytype' => new external_value(PARAM_RAW, 'entitytype'),
          'dataoptions' => new external_value(PARAM_RAW, 'The data for the service'),
          'totalcount' => new external_value(PARAM_INT, 'total number of challenges in result set'),
          'filterdata' => new external_value(PARAM_RAW, 'total number of challenges in result set'),
          'length' => new external_value(PARAM_RAW, 'total number of challenges in result set'),
          'records' => new external_single_structure(
                  array(
                      'hascourses' => new external_multiple_structure(
                        new external_single_structure(
                            array(
                                'entityid' => new external_value(PARAM_INT, 'entityid'),
                                'rootid' => new external_value(PARAM_INT, 'rootid'),
                                'productid' => new external_value(PARAM_INT, 'productid'),
                                'entityname' => new external_value(PARAM_RAW, 'entityname'),
                                'entitycode' => new external_value(PARAM_RAW, 'entitycode'),
                                'requestby' => new external_value(PARAM_RAW, 'requestby'),
                                'reasonforcancel' => new external_value(PARAM_RAW, 'reasonforcancel'),
                                'requestat' => new external_value(PARAM_RAW, 'requestat'),
                                'actionview' => new external_value(PARAM_RAW, 'actionview'),
                                'costtype' => new external_value(PARAM_INT, 'costtype'),
                                'currentuser' => new external_value(PARAM_RAW, 'currentuser'),
                                
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
    public static function unpublishedentitieslist_parameters() {
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
     * Edditingtrainer_confirmation
     * Generate String for the selected trainer confirmation
     * 
     */
    public static function edditingtrainer_confirmation_parameters() {
        return new external_function_parameters([
            'trainer_id' => new external_value(PARAM_INT, 'ID of the selected trainer'),
        ]);
    }
    public static function edditingtrainer_confirmation($trainerid) {
        global $DB, $PAGE, $CFG;
        require_login();
        $params = self::validate_parameters(
            self::edditingtrainer_confirmation_parameters(),
            [
                'trainer_id' => $trainerid,
            ]
        );

        $str = (new local_trainingprogram\local\trainingprogram)->generate_confirmation_string($trainerid);

        return ['str' => $str];
    }
    public static function edditingtrainer_confirmation_returns() {
        return new external_single_structure([
                'str' => new external_value(PARAM_RAW, 'Generated String for the selected trainer confirmation', VALUE_OPTIONAL),
        ]);
    }
    /**
     * Assign Editing Trainer
     * Generate String for the selected trainer confirmation
     * 
     */
    public static function assign_edditingtrainer_parameters() {
        return new external_function_parameters([
            'formdata' => new external_value(PARAM_RAW, 'Form data'),
        ]);
    }
    public static function assign_edditingtrainer($formdata) {
        global $DB, $PAGE, $CFG;
        require_login();
        $params = self::validate_parameters(
            self::assign_edditingtrainer_parameters(),
            [
                'formdata' => $formdata,
            ]
        );
        $arrdata = json_decode($formdata);
        foreach ($arrdata as $item) {
            $name = $item->name;
            $value = $item->value;
            if ($name == 'users') {
                unset($name);
            }
            if ($name == 'users[]') {
                $name = 'users';
            }
            if (!isset($trainerformdata[$name])) {
                $trainerformdata[$name] = $value;
            } else {
                if (!is_array($trainerformdata[$name])) {
                    $trainerformdata[$name] = [$trainerformdata[$name]];
                }
                
                $trainerformdata[$name][] = $value;
            }
        }
        // Get Editing Trainer Role ID
        $edittingroleid = $DB->get_field('role', 'id', ['shortname' => 'editingtrainer']);
        $nonedittingroleid = $DB->get_field('role', 'id', ['shortname' => 'trainer']);
        // Get Editing Trainer users
        if (!is_array($trainerformdata['users'])) {
            $trainerformdata['users'] = [$trainerformdata['users']];
        }
        for ($i=0; $i < count($trainerformdata['users']); $i++) { 
            if ($trainerformdata['assignedditingtrainer_'.$trainerformdata['users'][$i]]) {
                $id = $trainerformdata['assignedditingtrainer_'.$trainerformdata['users'][$i]];
                $trainers[$i]['id'] = $id;
                $trainers[$i]['roleid'] = $edittingroleid;
            }else{
                $id = $trainerformdata['users'][$i];
                $trainers[$i]['id'] = $id;
                $trainers[$i]['roleid'] = $nonedittingroleid;
            }
        }
        $trainertype = 0;
        foreach($trainers as $trainer){
           $records[] =  (new local_trainingprogram\local\trainingprogram)->program_enrollment($trainerformdata['offeringid'],$trainer['id'],$trainer['roleid'],$trainertype);

        }
        if ($records) {
            return ['status' => 'success'];
        }
        return ['status' => 'false'];
    }
    public static function assign_edditingtrainer_returns() {
        return new external_function_parameters([
            'status' => new external_value(PARAM_RAW, 'Status'),
        ]);

    } 

    public static function programenrollmentsviewdata_parameters() {
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
    public static function programenrollmentsviewdata($options, $dataoptions, $offset = 0, $limit = 0, $contextid, $filterdata) {

        global $DB, $PAGE, $CFG;
        require_login();
        $params = self::validate_parameters(
            self::programenrollmentsviewdata_parameters(),
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
        $cusers =  (json_decode($dataoptions)->cusers) ? json_decode($dataoptions)->cusers : 0;
        $offeringid =  (json_decode($dataoptions)->offeringid) ? json_decode($dataoptions)->offeringid : 0;
        $programid =  (json_decode($dataoptions)->programid) ? json_decode($dataoptions)->programid : 0;
        $roleid =  (json_decode($dataoptions)->roleid) ? json_decode($dataoptions)->roleid : 0;
        $organization =  (json_decode($dataoptions)->organization) ? json_decode($dataoptions)->organization : 0;
        $orgofficial =  (json_decode($dataoptions)->orgofficial) ? json_decode($dataoptions)->orgofficial : 0;
        $data = (new local_trainingprogram\local\trainingprogram)->get_listof_programenrollments($stable, $filtervalues,$dataoptions);
        $totalcount = $data['totalprogramenroll'];
        $uusers = base64_decode(json_decode($dataoptions)->cusers);
        $ausers =  $DB->get_fieldset_sql("SELECT userid from {local_users} WHERE FIND_IN_SET(userid,'$uusers') AND bulkenrollstatus = 0 AND bulkenrolltype = 'program'  ");
        $users = implode(',',$ausers);
        $invoicerecords = $DB->get_records_sql("SELECT productid,COUNT(id) AS userscount FROM {program_enrollments} WHERE programid =$programid AND offeringid=$offeringid  AND FIND_IN_SET(userid,'$users')AND productid > 0 AND enrolstatus = 0 AND enrolltype = 1 GROUP BY productid ");
        $productid = 0;
        $offeringrecord = $DB->get_record('tp_offerings',['id'=>$offeringid]);
        if((int)$offeringrecord->type == 1 && (int)$offeringrecord->offeringpricing == 1) {
            $existinginvoice_number = $DB->get_field_sql("SELECT invoice_number FROM {tool_product_sadad_invoice}  WHERE  productid =:productid AND organization =:organization  AND status =:status AND type IN ('programsbulkenrollment','purchase')",['productid'=>$productid,'organization'=>$organization,'status'=>1]);
            if($existinginvoice_number) {
            }
        }
        foreach($invoicerecords AS $record) {
            $productid = $record->productid;
            $productrecord = $DB->get_record('tool_products',['id'=>$record->productid]);
            $program = $DB->get_record('local_trainingprogram', ['id' => $programid]);
            $tax_slab = get_config('tool_product', 'tax_percentage');
            if((int)$offeringrecord->type == 1 && (int)$offeringrecord->offeringpricing == 1) {
                $total_price = $productrecord->price;
            } else {
                $total_price = $productrecord->price*$record->userscount;
            }

            $discountparams= new stdClass();
            $discountparams->tablename = 'tp_offerings';
            $discountparams->fieldid = $offeringid;
            $discountparams->selectedseats = $record->userscount;
            $discountparams->organization = $organization;

            $discountdata =(new product)->get_orgofficial_discountdata($discountparams);

            $discount =  $discountdata->discount > 0 ? round(($total_price * (($discountdata->discount/100))),2) : 0;
            $record->discount =$discount;
            $priceafterdiscount = $total_price - $record->discount;
            $taxes = (new local_exams\local\exams)->caluculate_taxes($priceafterdiscount, $tax_slab);
            $item_taxes = ($program->tax_free == 0) ? $taxes :0;
            $record->fee = number_format($total_price,2);
            $record->vat =($item_taxes > 0) ? number_format($item_taxes,2) : 0;
            $record->total = number_format(($priceafterdiscount + $item_taxes),2);
        }  
        $productprice =  $DB->get_field('tool_products','price',['id'=>$productid]);

        $orginfo = $DB->get_record('local_organization',['id'=>$organization]);

        $existinginvoice_number = $DB->get_field_sql("SELECT invoice_number FROM {tool_product_sadad_invoice}  WHERE  productid =:productid AND organization =:organization  AND status =:status AND type IN ('programsbulkenrollment','purchase')",['productid'=>$productid,'organization'=>$organization,'status'=>1]);
           

        return [
           'totalcount' => $totalcount,
            'length' => $totalcount,
            'filterdata' => $filterdata,
            'records' =>$data,
            'options' => $options,
            'dataoptions' => $dataoptions,
            'url' => $CFG->wwwroot,
            'programid' => $programid,
            'offeringid' => $offeringid,
            'organization' => $organization,
            'orgofficial' => $orgofficial,
            'cusers' => $cusers,
            'roleid'=>$roleid,
            'invoice'=>(!empty($invoicerecords) && $productprice > 0) ? array_values($invoicerecords) : array(),
            'hasinvoice'=> (COUNT($invoicerecords) > 0)? 1 :0,
            'isprivate'=>((int)$offeringrecord->type == 1) ? 1 : 0,
            'orgfullname'=> (current_language() == 'ar') ? $orginfo->fullnameinarabic: $orginfo->fullname,
            'hasinvoicealready'=> ((int)$offeringrecord->type == 1 && (int)$offeringrecord->offeringpricing == 1 &&   $existinginvoice_number > 0) ? 1 : 0,
            'existinginvoice_number'=> ($existinginvoice_number > 0) ? $existinginvoice_number : 0,
            'discount' => $discount ? $discount : 0, 
            'discounttableid' =>($discountdata->id > 0)  ? (int)$discountdata->id  : 0, 
            'discounttype' => ($discountdata->type)  ? $discountdata->type  : '',  
        ];
    }

    public static function programenrollmentsviewdata_returns() {
        return new external_single_structure([
          'options' => new external_value(PARAM_RAW, 'The paging data for the service'),
          'url' => new external_value(PARAM_RAW, 'url'),
          'dataoptions' => new external_value(PARAM_RAW, 'The data for the service'),
          'programid' => new external_value(PARAM_INT, 'programid'),
          'discount' => new external_value(PARAM_RAW, 'discount', VALUE_OPTIONAL),
          'discounttableid' => new external_value(PARAM_INT, 'discounttableid', VALUE_OPTIONAL),
          'discounttype' => new external_value(PARAM_RAW, 'discounttype', VALUE_OPTIONAL),
          'offeringid' => new external_value(PARAM_INT, 'offeringid'),
          'organization' => new external_value(PARAM_INT, 'organization'),
          'orgofficial' => new external_value(PARAM_INT, 'orgofficial'),
          'orgfullname' => new external_value(PARAM_RAW, 'orgfullname'),
          'hasinvoicealready' => new external_value(PARAM_INT, 'hasinvoicealready'),
          'existinginvoice_number' => new external_value(PARAM_RAW, 'existinginvoice_number'),
          'isprivate' => new external_value(PARAM_INT, 'isprivate',),
          'roleid' => new external_value(PARAM_INT, 'roleid'),
          'cusers' => new external_value(PARAM_RAW, 'cusers'),
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
          'totalcount' => new external_value(PARAM_INT, 'total number of challenges in result set'),
          'filterdata' => new external_value(PARAM_RAW, 'total number of challenges in result set'),
          'length' => new external_value(PARAM_RAW, 'total number of challenges in result set'),
          'totalprogramenroll' => new external_value(PARAM_INT, 'totalprogramenroll', VALUE_OPTIONAL),
          'records' => new external_single_structure(
                  array(
                      'hascourses' => new external_multiple_structure(
                        new external_single_structure(
                            array(
                                'userid' => new external_value(PARAM_INT, 'userid'),
                                'identityno' => new external_value(PARAM_RAW, 'identityno'),
                                'fullname' => new external_value(PARAM_RAW, 'fullname'),
                                'programname' => new external_value(PARAM_RAW, 'programname'),
                                'programcode' => new external_value(PARAM_RAW, 'programcode'),
                                'offeringcode' => new external_value(PARAM_RAW, 'offeringcode'),
                                'fee' => new external_value(PARAM_RAW, 'fee'),
                                'startdate' => new external_value(PARAM_RAW, 'startdate'),
                                'enddate' => new external_value(PARAM_RAW, 'enddate'),
                                'actionview' => new external_value(PARAM_RAW, 'actionview'),
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
                    'totalearlyregistrations' => new external_value(PARAM_INT, 'totalearlyregistrations', VALUE_OPTIONAL),
                    'length' => new external_value(PARAM_INT, 'length', VALUE_OPTIONAL),
                )
            )
        ]);
    }

    /**
     * newjobfamily_options
     * Generate String for the selected trainer confirmation
     * 
     */
    public static function newjobfamily_options_parameters() {
        return new external_function_parameters([
            'sector_id' => new external_value(PARAM_INT, 'ID of the selected sector'),
        ]);
    }
    public static function newjobfamily_options($sectorid) {
        global $DB, $PAGE, $CFG;
        require_login();
        $params = self::validate_parameters(
            self::newjobfamily_options_parameters(),
            [
                'sector_id' => $sectorid,
            ]
        );

        $str = (new local_trainingprogram\local\trainingprogram)->generate_newjobfamily_options($sectorid);

        return ['str' => $str];
    }
    public static function newjobfamily_options_returns() {
        return new external_single_structure([
                'str' => new external_value(PARAM_RAW, 'Generated String for the selected trainer confirmation', VALUE_OPTIONAL),
        ]);
    } 



    public static function update_offering_financially_closed_status_parameters(){
        return new external_function_parameters(
            array(
                'id' => new external_value(PARAM_INT,'id'),
                'code' => new external_value(PARAM_RAW,'code'),
                'actiontype' => new external_value(PARAM_RAW,'actiontype'),
            )
        );
    }
    public static  function  update_offering_financially_closed_status($id,$code,$actiontype){
        $params=self::validate_parameters(
            self:: update_offering_financially_closed_status_parameters(),
            array(
                'id'=>$id,
                'code'=>$code,
                'actiontype'=>$actiontype,
            )
        );
        $systemcontext = context_system::instance();
        self::validate_context($systemcontext);
       if ($id && $code && $actiontype) {
          (new local_trainingprogram\local\trainingprogram)->update_financially_closed_status($id,$code,$actiontype);
        } else {
          throw new moodle_exception('Error in submission');
        }
        return true;    
    }
    public static function  update_offering_financially_closed_status_returns() {
        return new external_value(PARAM_BOOL, 'return');
    }
    /**
     * Activity aprooved/Reject
     * 
     */
    public static function activity_approoved_parameters() {
        return new external_function_parameters([
            'cmid' => new external_value(PARAM_INT, 'ID of the selected activity'),
            'status' => new external_value(PARAM_RAW, 'aproove or reject'),
        ]);
    }
    public static function activity_approoved($cmid, $status) {
        global $DB, $PAGE, $CFG;
        require_login();
        require_once($CFG->libdir.'/adminlib.php');
        require_once($CFG->dirroot.'/course/lib.php');
        $params = self::validate_parameters(
            self::activity_approoved_parameters(),
            [
                'cmid' => $cmid,
                'status' => $status,
            ]
        );
        $modcontext = context_module::instance($cmid);
        require_capability('moodle/course:manageactivities', $modcontext);
        $status = $params['status'] == 'approve' ? 1 : 0;
        
        $cm = get_coursemodule_from_id('', $params['cmid'], 0, false, MUST_EXIST);
        try {
            if ($status == 0) {
                // Get the course module object
                // Delete the module
                course_delete_module($cm->id, true);
            }else{
                set_coursemodule_visible($params['cmid'], $status);
                \core\event\course_module_updated::create_from_cm($cm, $modcontext)->trigger();
            }
            purge_caches();
            return ['status' => true];
        } catch (Exception $e) {
            throw new Exception($e->getMessage());
        }
    }
    public static function activity_approoved_returns() {
        return new external_single_structure([
                'status' => new external_value(PARAM_BOOL, 'Boolean Value', VALUE_OPTIONAL),
        ]);
    }
    /**
     * Activity creator
     * 
     */
    public static function getActivityCreator_parameters() {
        return new external_function_parameters([
            'cmid' => new external_value(PARAM_INT, 'ID of the selected activity'),
        ]);
    }
    public static function getActivityCreator($cmid) {
        global $DB, $PAGE, $CFG, $COURSE;
        require_login();
        require_once($CFG->libdir.'/adminlib.php');
        require_once($CFG->dirroot.'/course/lib.php');
        $params = self::validate_parameters(
            self::getActivityCreator_parameters(),
            [
                'cmid' => $cmid,
            ]
        );
        // Get the course module object
        $cm = get_coursemodule_from_id('', $cmid, 0, false, MUST_EXIST);
        $COURSE->id = $cm->course;
        $sql = "SELECT * FROM {logstore_standard_log} WHERE `eventname` LIKE '%course_module_created%' AND `objectid` = :cmid ORDER BY `id` DESC ";
        $creator = $DB->get_record_sql($sql, ['cmid' => $cmid]);
        if ($creator) {
            $creatorRole = (new \local_exams\local\exams())->get_user_role($creator->userid);
            return ['activityCreator' => $creatorRole];
        }
    }
    public static function getActivityCreator_returns() {
        return new external_single_structure([
                'activityCreator' => new external_single_structure([
                    'id' => new external_value(PARAM_TEXT, 'ID of the user', VALUE_OPTIONAL),
                    'firstname' => new external_value(PARAM_TEXT, 'Firsname of the user', VALUE_OPTIONAL),
                    'lastname' => new external_value(PARAM_TEXT, 'Lastname of the user', VALUE_OPTIONAL),
                    'shortname' => new external_value(PARAM_TEXT, 'Role of the user', VALUE_OPTIONAL),
                ])
        ]);
    }
 
    // program methods ........renu


    public static function get_programmethod_parameters() {
        return new external_function_parameters([
            'options' => new external_value(PARAM_RAW, 'The paging data for the service'),
            'dataoptions' => new external_value(PARAM_RAW, 'The data for the service'),
            'offset' => new external_value(PARAM_INT, 'Number of items to skip from the begging of the result set',
                VALUE_DEFAULT, 0),
            'limit' => new external_value(PARAM_INT, 'Maximum number of results to return',
                VALUE_DEFAULT, 0),
            'contextid' => new external_value(PARAM_INT, 'contextid',VALUE_DEFAULT, 1),
            'filterdata' => new external_value(PARAM_RAW, 'filters applied'),
        ]);
    }
    public static function get_programmethod($options, $dataoptions, $offset = 0, $limit = 0, $contextid, $filterdata) {
        global $DB, $PAGE, $CFG;
        require_login();
        $params = self::validate_parameters(
            self::get_programmethod_parameters(),
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
        $data = (new local_trainingprogram\local\programmethod)->get_listof_program_methods($stable, $filtervalues);


        $totalcount = $data['totalprogrammethod'];
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
    public static function get_programmethod_returns() {
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
                                'name' => new external_value(PARAM_RAW, 'name'),
                                
                            )
                        )
                    ),
                   
                    'nocourses' => new external_value(PARAM_BOOL, 'nocourses', VALUE_OPTIONAL),
                    'totalprogrammethod' => new external_value(PARAM_INT, 'totalprogrammethod', VALUE_OPTIONAL),
                    'length' => new external_value(PARAM_INT, 'length', VALUE_OPTIONAL),
                ),'',VALUE_OPTIONAL
            )
        ]);
    }

    // evalution methods ........renu


    public static function get_evalutionmethod_parameters() {
        return new external_function_parameters([
            'options' => new external_value(PARAM_RAW, 'The paging data for the service'),
            'dataoptions' => new external_value(PARAM_RAW, 'The data for the service'),
            'offset' => new external_value(PARAM_INT, 'Number of items to skip from the begging of the result set',
                VALUE_DEFAULT, 0),
            'limit' => new external_value(PARAM_INT, 'Maximum number of results to return',
                VALUE_DEFAULT, 0),
            'contextid' => new external_value(PARAM_INT, 'contextid',VALUE_DEFAULT, 1),
            'filterdata' => new external_value(PARAM_RAW, 'filters applied'),
        ]);
    }
    public static function get_evalutionmethod($options, $dataoptions, $offset = 0, $limit = 0, $contextid, $filterdata) {
        global $DB, $PAGE, $CFG;
        require_login();
        $params = self::validate_parameters(
            self::get_evalutionmethod_parameters(),
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
        $data = (new local_trainingprogram\local\evalutionmethod)->get_listof_evaluation_methods($stable, $filtervalues);


        $totalcount = $data['totalevalutionmethod'];
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
    public static function get_evalutionmethod_returns() {
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
                                'name' => new external_value(PARAM_RAW, 'name'),
                                
                            )
                        )
                    ),
                
                    'nocourses' => new external_value(PARAM_BOOL, 'nocourses', VALUE_OPTIONAL),
                    'totalevalutionmethod' => new external_value(PARAM_INT, 'totalevalutionmethod', VALUE_OPTIONAL),
                    'length' => new external_value(PARAM_INT, 'length', VALUE_OPTIONAL),
                ),'',VALUE_OPTIONAL
            )
        ]);
    }

    // renu..........delete program method
    public static function delete_programmethod_parameters(){

        return new external_function_parameters(
          array(
              'id' => new external_value(PARAM_INT,'id',0),
          )
        );
    }

    public static  function delete_programmethod($id){

        
        global $DB;
        $params = self::validate_parameters (
                self:: delete_programmethod_parameters(),array('id'=>$id));
        $context = context_system::instance();
        self::validate_context($context);
        if($id) {
            $delete = $DB->delete_records('program_methods',array('id' => $id));
        } else {
            throw new moodle_exception('Error');
        }
        return true;
    }

    public static function delete_programmethod_returns() {
        return new external_value(PARAM_BOOL, 'return');
    } 

    // renu..........delete evaluation method
    public static function delete_evaluationmethod_parameters(){

        return new external_function_parameters(
          array(
              'id' => new external_value(PARAM_INT,'id',0),
          )
      );
    }

    public static  function delete_evaluationmethod($id){
        global $DB;
        $params = self::validate_parameters (
                self:: delete_evaluationmethod_parameters(),array('id'=>$id));
        $context = context_system::instance();
        self::validate_context($context);
        if($id) {
            $delete = $DB->delete_records('evalution_methods',array('id' => $id));
        } else {
            throw new moodle_exception('Error');
        }
        return true;
    }

    public static function delete_evaluationmethod_returns() {
        return new external_value(PARAM_BOOL, 'return');
    } 


    public static function viewprogramtopics_parameters() {
        return new external_function_parameters(
            array(
               'programid' => new external_value(PARAM_INT,'Program id',0),
            )
        );
    }
    public static function viewprogramtopics($programid) {
        global $DB,$CFG;
        $params = self::validate_parameters(self::viewprogramtopics_parameters(),
        ['programid'=>$programid]);
        $data = (new local_trainingprogram\local\trainingprogram)->viewprogramtopics($programid);
        return [
            'options' => $data,
        ];
    }
    public static function viewprogramtopics_returns() {
        return new external_single_structure([
          'options' => new external_value(PARAM_RAW, 'The paging data for the service'),
        ]);
    }
    public static function checkofficial_availibility_parameters() {
        return new external_function_parameters(
          array(
              'officialid' => new external_value(PARAM_INT,'officialid'),
              'officialname' => new external_value(PARAM_RAW,'officialname'),
              'trainingid' => new external_value(PARAM_INT,'trainingid'),
              'meetingtype' => new external_value(PARAM_RAW,'meetingtype'),
              // Start date
              'startday' => new external_value(PARAM_RAW,'startday'),
              'startmonth' => new external_value(PARAM_RAW,'startmonth'),
              'startyear' => new external_value(PARAM_RAW,'startyear'),
              'starttimehrs' => new external_value(PARAM_RAW,'starttimehrs'),
              'starttimemin' => new external_value(PARAM_RAW,'starttimemin'),
              // End date
              'endday' => new external_value(PARAM_RAW,'endday'),
              'endmonth' => new external_value(PARAM_RAW,'endmonth'),
              'endyear' => new external_value(PARAM_RAW,'endyear'),
              'endtimehurs' => new external_value(PARAM_RAW,'endtimehurs'),
              'endtimemin' => new external_value(PARAM_RAW,'endtimemin'),
            )
        );
    }
    public static function checkofficial_availibility($officialid,$officialname,$trainingid,$meetingtype, $startday,$startmonth,$startyear,$starttimehrs,$starttimemin, $endday,$endmonth,$endyear,$endtimehurs,$endtimemin) {
        global $DB,$CFG;
        $params = self::validate_parameters(self::checkofficial_availibility_parameters(),
        [
            'officialid'=>$officialid,
            'officialname'=>$officialname,
            'trainingid'=>$trainingid,
            'meetingtype'=>$meetingtype,
            // start date time
            'startday'=>$startday,
            'startmonth'=>$startmonth,
            'startyear'=>$startyear,
            'starttimehrs'=>$starttimehrs,
            'starttimemin'=>$starttimemin,
            // end date time
            'endday'=>$endday,
            'endmonth'=>$endmonth,
            'endyear'=>$endyear,
            'endtimehurs'=>$endtimehurs,
            'endtimemin'=>$endtimemin,
        ]);
        $stdate = $params['startmonth'].'/'. $params['startday'].'/'. $params['startyear'];
        $startdate = strtotime($stdate);
        $endate = $params['endmonth'].'/'. $params['endday'].'/'. $params['endyear'];
        $enddate = strtotime($endate);
        $starttime = ($params['starttimehrs'] * 3600) + ($params['starttimemin'] * 60);
        $endtime = ($params['endtimehurs'] * 3600) + ($params['endtimemin'] * 60);
        $sql = "SELECT count(id) FROM {tp_offerings} WHERE officials = $officialid AND trainingid = $trainingid AND startdate = $startdate AND enddate = $enddate AND (time >= $starttime OR time <= $endtime) AND (endtime >= $starttime OR endtime <= $endtime)";
        $countsessions = $DB->get_field_sql($sql);
        if ($countsessions >= 4 ) {
            $return = ['status' => false, 'official_name' => $officialname];
        }else{
            $return = ['status' => true, 'official_name' => $officialname];
        };
        return $return;
    }
    public static function checkofficial_availibility_returns() {
        return new external_single_structure([
          'status' => new external_value(PARAM_BOOL, 'Status of the avaibility whether the official can be assigned to current session or not'),
          'official_name' => new external_value(PARAM_RAW, 'Name of the selected Training Official'),
        ]);
    }
    public static function offeringprogramrequests_parameters() {
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
    public static function offeringprogramrequests($options, $dataoptions, $offset = 0, $limit = 0, $contextid, $filterdata) {
        global $CFG;
        require_login();
        $params = self::validate_parameters(
            self::offeringprogramrequests_parameters(),
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
        $data = (new local_trainingprogram\local\trainingprogram)->get_listof_offering_program_requests($stable, $filtervalues,$dataoptions);

        $totalcount = $data['totalrequests'];
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
    public static function offeringprogramrequests_returns() {
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
                                'entityid' => new external_value(PARAM_INT, 'entityid'),
                                'entity' => new external_value(PARAM_RAW, 'entity'),
                                'entitycode' => new external_value(PARAM_RAW, 'entitycode'),
                                'requestbyname' => new external_value(PARAM_RAW, 'requestbyname'),
                                'requestby' => new external_value(PARAM_RAW, 'requestby'),
                                'requestat' => new external_value(PARAM_RAW, 'requestat'),
                                'requesttype' => new external_value(PARAM_RAW, 'requesttype'),
                                'programid' => new external_value(PARAM_INT, 'programid'),
                                'viewrequestedoffering' => new external_value(PARAM_RAW, 'viewrequestedoffering'),
                                'viewrequestedprogram' => new external_value(PARAM_RAW, 'viewrequestedprogram'),
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

    public static function offering_program_action_parameters(){
        return new external_function_parameters(
            array(
                'rootid' => new external_value(PARAM_INT,'rootid'),
                'entityid' => new external_value(PARAM_INT,'entityid'),
                'entitycode' => new external_value(PARAM_RAW,'entitycode'),
                'etype' => new external_value(PARAM_RAW,'etype'),
                'requestby' => new external_value(PARAM_RAW,'requestby'),
                'requesttype' => new external_value(PARAM_RAW,'requesttype'),
                'actiontype' => new external_value(PARAM_RAW,'actiontype'),  
            )
        );
    }
    public static  function  offering_program_action($rootid,$entityid,$entitycode,$etype,$requestby,$requesttype,$actiontype){
        global $DB,$CFG;
        $params=self::validate_parameters(
            self:: offering_program_action_parameters(),
            array(
                'rootid'=>$rootid,
                'entityid'=>$entityid,
                'entitycode'=>$entitycode,
                'etype'=>$etype,
                'requestby'=>$requestby,
                'requesttype'=>$requesttype,
                'actiontype'=>$actiontype,
            )
        );
        $systemcontext = context_system::instance();
        self::validate_context($systemcontext);

       if ($rootid && $entityid && $etype && $requesttype && $actiontype) {
            $data =new stdClass();
            $data->rootid = $rootid;
            $data->entityid = $entityid;
            $data->entitycode = $entitycode;
            $data->type = $etype;
            $data->requestby = $requestby;
            $data->requesttype = $requesttype;
            $data->actiontype = $actiontype;
            $response =  (new local_trainingprogram\local\trainingprogram)->offering_program_action_view($data);
            $returndata['returnurl'] =$CFG->wwwroot.'/local/trainingprogram/offering_program_requests.php';
            $returndata['response'] = ($response) ? 'success' :'failed' ;
        } else {
          throw new moodle_exception('Error in submission');
        }
        return $returndata;  
    }
    public static function offering_program_action_returns() {
        return new external_single_structure(
            array(
                'returnurl' => new external_value(PARAM_RAW, 'returnurl'),
                'response' => new external_value(PARAM_RAW, 'response')
            )
        );
    }

    public static function tofficialdeleteaction_parameters(){
        return new external_function_parameters(
            array(
                'rootid' => new external_value(PARAM_INT,'rootid'),
                'etype' => new external_value(PARAM_RAW,'etype'),
            )
        );
    }
    public static  function  tofficialdeleteaction($rootid,$etype){
        global $DB,$CFG;
        $params=self::validate_parameters(
            self:: tofficialdeleteaction_parameters(),
            array(
                'rootid'=>$rootid,
                'etype'=>$etype,
            )
        );
        $systemcontext = context_system::instance();
        self::validate_context($systemcontext);

       if ($rootid && $etype) {
            $data =new stdClass();
            $data->rootid = $rootid;
            $data->etype = $etype;
            $response =  (new local_trainingprogram\local\trainingprogram)->t_official_deleteaction($data);
            $returndata['response'] = ($response) ? 'success' :'failed' ;
        } else {
          throw new moodle_exception('Error in submission');
        }
        return $returndata;  
    }
    public static function tofficialdeleteaction_returns() {
        return new external_single_structure(
            array(
                'response' => new external_value(PARAM_RAW, 'response')
            )
        );
    }
    public static function viewcurrentoffering_parameters() {
        return new external_function_parameters(
            array(
               'rootid' => new external_value(PARAM_INT,'rootid',0),
               'offeringid' => new external_value(PARAM_INT,'offerinid',0),
               'requesttype' => new external_value(PARAM_RAW, 'requesttype')
            )
        );
    }
    public static function viewcurrentoffering($rootid,$offeringid,$requesttype) {
        self::validate_parameters(self::viewcurrentoffering_parameters(),
        ['rootid'=>$rootid,'offeringid'=>$offeringid,'requesttype'=>$requesttype]);
        $data = (new local_trainingprogram\local\trainingprogram)->view_currentoffering($rootid,$offeringid,$requesttype);
        return [
            'options' => $data,
        ];
    }
    public static function viewcurrentoffering_returns() {
        return new external_single_structure([
          'options' => new external_value(PARAM_RAW, 'The paging data for the service'),
        ]);
    }

    public static function managementdiscountdata_parameters() {
        return new external_function_parameters(
            array(
               'objtype' => new external_value(PARAM_RAW, 'objecttype')
            )
        );
    }
    public static function managementdiscountdata($objtype) {
        self::validate_parameters(
            self::managementdiscountdata_parameters(),
            [
                'objtype' => $objtype,
            ]
        );
        $data = (new local_trainingprogram\local\trainingprogram)->view_managementdiscountdata($objtype);
        return [
            'management' =>$data,
        ];
    }
    public static function managementdiscountdata_returns() {
        return new external_single_structure([
            'management' => new external_value(PARAM_RAW, 'management'),
        ]);
    }

    public static function groupsdiscountdata_parameters() {
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
    public static function groupsdiscountdata($options, $dataoptions, $offset = 0, $limit = 0, $filterdata) {
        global $DB, $PAGE, $CFG;
        require_login();
        $params = self::validate_parameters(
            self::groupsdiscountdata_parameters(),
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
        $filtervalues = json_decode($filterdata);
        $stable = new \stdClass();
        $stable->thead = false;
        $stable->start = $offset;
        $stable->length = $limit;
        $data = (new local_trainingprogram\local\trainingprogram)->get_listof_groupsdiscounts($stable, $filtervalues);

        $totalcount = $data['totalgroupsdiscounts'];
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
    
    public static function groupsdiscountdata_returns() {
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
                                'group_count' => new external_value(PARAM_INT, 'days'),
                                'discount' => new external_value(PARAM_INT, 'discount'),
                                'created_date' => new external_value(PARAM_RAW, 'created_date'),
                                'expired_date' => new external_value(PARAM_RAW, 'expired_date'),
                                'status' => new external_value(PARAM_RAW, 'status'),
                                'limitedorganization' => new external_value(PARAM_RAW, 'limitedorganization'), 
                                'allorganization' => new external_value(PARAM_RAW, 'allorganization'), 
                                'moreorganization' => new external_value(PARAM_BOOL, 'moreorganization'), 
                                'all' => new external_value(PARAM_BOOL, 'all'),   
                                'actionview' => new external_value(PARAM_BOOL, 'actionview'),  
                        
                           )
                        )
                    ),
                    'request_view' => new external_value(PARAM_INT, 'request_view', VALUE_OPTIONAL),
                    'nocourses' => new external_value(PARAM_BOOL, 'nocourses', VALUE_OPTIONAL),
                    'totalgroupsdiscounts' => new external_value(PARAM_INT, 'totalgroupsdiscounts', VALUE_OPTIONAL),
                    'length' => new external_value(PARAM_INT, 'length', VALUE_OPTIONAL),
                )
            )
        ]);
    } 

    public static function deletegroupdiscount_parameters(){
        return new external_function_parameters(
            array(
                'id' => new external_value(PARAM_INT,'Group id',0),
            )
        );
    }
    public static  function deletegroupdiscount($id){
        $params=self::validate_parameters(
            self::deletegroupdiscount_parameters(),
            array('id' => $id)
        );
        $systemcontext = context_system::instance();
       if ($id) {
           (new local_trainingprogram\local\trainingprogram)->remove_groupdiscount($id);
        } else {
          throw new moodle_exception('Error in submission');
        }
        return true;    
    }
    public static function deletegroupdiscount_returns() {
        return new external_value(PARAM_BOOL, 'return');
    } 

    public static function viewdiscountentity_parameters() {
        return new external_function_parameters(
            array(
               'entityid' => new external_value(PARAM_INT,'entityid',0),
               'entitytype' => new external_value(PARAM_RAW, 'entitytype')
            )
        );
    }
    public static function viewdiscountentity($entityid,$entitytype) {
        self::validate_parameters(self::viewdiscountentity_parameters(),
        ['entityid'=>$entityid,'entitytype'=>$entitytype]);
        $data = (new local_trainingprogram\local\trainingprogram)->view_discountentity($entityid,$entitytype);
        return [
            'options' => $data,
        ];
    }
    public static function viewdiscountentity_returns() {
        return new external_single_structure([
          'options' => new external_value(PARAM_RAW, 'The paging data for the service'),
        ]);
    }
    
}
