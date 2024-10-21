<?php
defined('MOODLE_INTERNAL') || die();

require_once($CFG->libdir.'/externallib.php');
require_once($CFG->libdir.'/filelib.php');

require_once($CFG->dirroot.'/local/sector/lib.php');

use local_sector\local\sectors as sector;
use local_sector\controller as controller;
use cache;

class local_sector_external extends external_api {

     public static function sectors_view_parameters() {
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
      public static function sectors_view($options, $dataoptions, $offset = 0, $limit = 0, $contextid, $filterdata) {
        global $DB, $PAGE;
        require_login();
        
        // Parameter validation.
        $params = self::validate_parameters(
            self::sectors_view_parameters(),
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
       
        $sector = new sector();
        $sectorslist = $sector->get_sectors(array(), $offset, $limit,$filtervalues);
        $lang= current_language();
        foreach($sectorslist as $sectors){
            $segmentdata = array();
            $segments = $sector->get_segments($sectors->id, ['id','title','titlearabic']);
            foreach($segments as $segment){
                $jobfamilydata = array();
                
                $cache = cache::make('local_sector', 'jobfamilies');
                // if( $lang == 'ar'){
                //     $jobfamilydata = $cache->get('jobfamilylist_ar');
                // }else{
                //     $jobfamilydata = $cache->get('jobfamilylist_en');
                // }
              
                if(empty($jobfamilydata)){
                    $jobfamilies = $sector->get_jobfamilies($segment->id, ['id','code', 'familyname','familynamearabic','description','shared']);
                    foreach($jobfamilies as $jobfamily){
                        if( $lang == 'ar' && !empty($jobfamily->familynamearabic)){
                             $familyname = $jobfamily->familynamearabic;
                        }else{
                            $familyname = $jobfamily->familyname;
                        }

                        $jobfamilydata[] = [   'jobfamilyid' => $jobfamily->id,
                                               'familyname' => $familyname ,
                                               'code' => $jobfamily->code,
                                               'shared' => $jobfamily->shared,
                                               'description' => $jobfamily->description
                                           ];

                        
                    }
                        // if( $lang == 'ar'){
                        //     $cache->set('jobfamilylist_ar', $jobfamilydata);
                        // }else{
                        //     $cache->set('jobfamilylist_en', $jobfamilydata);
                        // }
                        
                }

                if( $lang == 'ar' && !empty($segment->titlearabic)){
                    $segmenttitle = $segment->titlearabic;
                }else{
                    $segmenttitle = $segment->title;
                }
                $segmentdata[] = ['segmentid' => $segment->id,
                                  'title' =>  $segmenttitle,
                                  'jobfamily' => $jobfamilydata];

            }
           
            if( $lang == 'ar' && !empty($sectors->titlearabic)){
                $title = $sectors->titlearabic;
            }else{
                $title = $sectors->title;
            }
            $records['sector'][] = ['sectorid' => $sectors->id,
                                    'title' => $title,
                                    'sectorcode' => $sectors->code,
                                    'segment' => $segmentdata];
        }
        $records['length'] = sizeof($sectorslist);
        $totalcount = sizeof($sector->get_sectors());
        $return = [
            'totalcount' => $totalcount,
            'length' => $totalcount,
            'filterdata' => $filterdata,
            'records' =>$records,
            'options' => $options,
            'dataoptions' => $dataoptions,
        ];
        return $return;
      }

      public static function sectors_view_returns() {
        return new external_single_structure([
            'options' => new external_value(PARAM_RAW, 'The paging data for the service'),
            'dataoptions' => new external_value(PARAM_RAW, 'The data for the service'),
            'totalcount' => new external_value(PARAM_INT, 'total number of challenges in result set'),
            'filterdata' => new external_value(PARAM_RAW, 'total number of challenges in result set'),
            'length' => new external_value(PARAM_RAW, 'total number of challenges in result set'),
            'records' => new external_single_structure(
                    array(
                        'sector' => new external_multiple_structure(
                            new external_single_structure(
                                array(
                                    'sectorid' => new external_value(PARAM_RAW, 'id', VALUE_OPTIONAL),
                                    'title' => new external_value(PARAM_RAW, 'title', VALUE_OPTIONAL),
                                    'sectorcode' => new external_value(PARAM_RAW, 'sectorcode', VALUE_OPTIONAL),
                                    'segment' => new external_multiple_structure(
                                        new external_single_structure(
                                            array(
                                                'segmentid' => new external_value(PARAM_RAW, 'id'),
                                                'title' => new external_value(PARAM_RAW, 'title'),
                                                'jobfamily' => new external_multiple_structure(
                                                    new external_single_structure(
                                                        array(
                                                            'jobfamilyid' => new external_value(PARAM_RAW, 'id'),
                                                            'code' => new external_value(PARAM_RAW, 'code'),
                                                            'familyname' => new external_value(PARAM_RAW, 'familyname'),
                                                            'shared' => new external_value(PARAM_BOOL, 'shared'),
                                                            'description' => new external_value(PARAM_RAW, 'description' ,VALUE_OPTIONAL))
                                                    ), '', VALUE_OPTIONAL)
                                            )
                                        ), '', VALUE_OPTIONAL)
                                )
                                )
                            , '', VALUE_OPTIONAL),
                        'length' => new external_value(PARAM_INT, 'length', VALUE_OPTIONAL),
                    )
                )
            

        ]);
    }

    public static function get_segments_parameters() {
        return new external_function_parameters(
            array(
                'id'  => new external_value(PARAM_INT, 'id'),
           
            )
        );
    }
    public static function  get_segments($id) {
        global $DB;

        $params = self::validate_parameters (

               self:: get_segments_parameters(),array('id'=>$id)
               
       );
        $context = context_system::instance();
        self::validate_context($context);
        if($id) {
             $segments = $DB->get_records('segment',array('sectorid'=>$id));
             return json_encode(array_values($segments));
           
        } else {
            throw new moodle_exception('Error');
        }
     }
     public static function  get_segments_returns(){

        return new  external_value(PARAM_RAW,'the segment values');
     }

     public static function jobrole_level_view_parameters() {
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
      public static function jobrole_level_view($options, $dataoptions, $offset = 0, $limit = 0, $contextid, $filterdata) {
        global $DB, $PAGE;
        require_login();
        
        // Parameter validation.
        $params = self::validate_parameters(
            self::jobrole_level_view_parameters(),
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
        $jobfamilyid = json_decode($dataoptions);
    
        $stable = new \stdClass();
        $stable->thead = false;
        $stable->start = $offset;
        $stable->length = $limit;
        $data = get_jobrole_level_view($stable, $filtervalues,$jobfamilyid);
        $totalcount = $data['totalsectors'];
    
        return [
            'totalcount' => $totalcount,
            'length' => $totalcount,
            'filterdata' => $filterdata,
            'records' =>$data,
            'options' => $options,
            'dataoptions' => $dataoptions,
        ];
      }

      public static function jobrole_level_view_returns() {
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
                                     'jobfamily' => new external_value(PARAM_RAW, 'jobfamily'),
                                     'title' => new external_value(PARAM_RAW, 'title'),
                                    'code' => new external_value(PARAM_RAW, 'code'),
                                    'timemodified' => new external_value(PARAM_RAW, 'timemodified'),
                                    'usermodified'=>new external_value(PARAM_RAW,'usermodified')

                                )
                            )
                        ),
                        'request_view' => new external_value(PARAM_INT, 'request_view', VALUE_OPTIONAL),
                        'nocourses' => new external_value(PARAM_BOOL, 'nocourses', VALUE_OPTIONAL),
                        'totalsectors' => new external_value(PARAM_INT, 'totalposts', VALUE_OPTIONAL),
                        'length' => new external_value(PARAM_INT, 'length', VALUE_OPTIONAL),
                    )
                )

        ]);
    }
    /**
     * Returns description of get_files parameters
     *
     * @return external_function_parameters
     * @since Moodle 2.2
     */

    public static function deletesector_parameters() {
        return new external_function_parameters(
            array(
                'id'  => new external_value(PARAM_INT, 'id',0),
           
            )
        );
    }
    public static function  deletesector($id) {
        $params = self::validate_parameters (
               self:: deletesector_parameters(),array('id'=>$id));
        $context = context_system::instance();
        self::validate_context($context);
        if($id) {
            (new controller)->delete_sector($id);
        } else {
            throw new moodle_exception('Error');
        }
     }
     public static function  deletesector_returns(){
        return null;
     }
    public static function deletesegment_parameters() {
        return new external_function_parameters(
            array(
                'id'  => new external_value(PARAM_INT, 'id',0),
           
            )
        );
    }
    public static function  deletesegment($id) {
        $params = self::validate_parameters (
               self:: deletesegment_parameters(),array('id'=>$id));
        $context = context_system::instance();
        self::validate_context($context);
        if($id) {
             (new controller)->delete_segment($id);
        } else {
            throw new moodle_exception('Error');
        }
     }
    public static function  deletesegment_returns(){
        return null;
    }
    public static function deletejobfamily_parameters() {
        return new external_function_parameters(
            array(
                'id'  => new external_value(PARAM_INT, 'id',0)
            )
        );
    }
    public static function  deletejobfamily($id) {
        $params = self::validate_parameters (
               self:: deletesegment_parameters(),array('id'=>$id));
        $context = context_system::instance();
        self::validate_context($context);
        if($id) {
            (new controller)->delete_jobfamily($id);
        } else {
            throw new moodle_exception('Error');
        }
     }
    public static function  deletejobfamily_returns(){
        return null;
     }

    public static function deletejobrole_level_parameters(){
        return new external_function_parameters(
            array(
                'roleid' => new external_value(PARAM_INT,'Role id',0),
            )
        );
    }
    public static function deletejobrole_level($roleid){
        $params=self::validate_parameters(
            self::deletejobrole_level_parameters(),
            array('roleid'=>$roleid)
        );
        $systemcontext = context_system::instance();
        self::validate_context($systemcontext);
       if ($roleid) {
            (new controller)->delete_jobrole($roleid);
        } else {
          throw new moodle_exception('Error in submission');
        }
        return true;    
    }
    public static function deletejobrole_level_returns() {
        return new external_value(PARAM_BOOL, 'return');
    } 

    public static function deleteresponsibility_parameters(){

          return new external_function_parameters(
            array(
                'responseid' => new external_value(PARAM_INT,'responseid',0),
            )
        );
    }

    public static  function deleteresponsibility($responseid){
        global $DB;
        $params = self::validate_parameters (
               self:: deleteresponsibility_parameters(),array('responseid'=>$responseid));
        $context = context_system::instance();
        self::validate_context($context);
        if($responseid) {
           $delete = $DB->delete_records('local_jobrole_responsibility',array('id' => $responseid));
        } else {
            throw new moodle_exception('Error');
        }
        return true;
    }

    public static function deleteresponsibility_returns() {
         return new external_value(PARAM_BOOL, 'return');
    }

    public static function responsibilitydisplay_parameters() {
       return new external_function_parameters([
            'options' => new external_value(PARAM_RAW, 'The paging data for the service'),
            'dataoptions' => new external_value(PARAM_RAW, 'The data for the service'),
            'offset' => new external_value(PARAM_INT, 'Number of items to skip from the begging of the result set',
                VALUE_DEFAULT, 0),
            'limit' => new external_value(PARAM_INT, 'Maximum number of results to return',
                VALUE_DEFAULT, 0),
            'filterdata' => new external_value(PARAM_RAW, 'filters applied'),
            'contextid' => new external_value(PARAM_INT, 'Maximum number of results to return',
                VALUE_DEFAULT, 1)
        ]);
    }
 
    public static function responsibilitydisplay($options, $dataoptions, $offset = 0, $limit = 0, $filterdata) {
        global $OUTPUT, $CFG, $DB,$USER,$PAGE;
        $sitecontext = context_system::instance();
        $PAGE->set_context($sitecontext);
        $params = self::validate_parameters(
            self::responsibilitydisplay_parameters(),
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
        $decodeddataoptions = json_decode($dataoptions);
        $stable = new \stdClass();
        $stable->thead = false;
        $stable->start = $offset;
        $stable->length = $limit;
        $stable->class = 'allresponsibilities';
        $responsibilities = display_lisfof_responsibilities($stable,$filtervalues);
        $totalcount = $responsibilities['totalresponsibilities'];
        return [
            'totalcount' => $totalcount,
            'length' => $totalcount,
            'filterdata' => $filterdata,
            'records' =>$responsibilities,
            'options' => $options,
            'dataoptions' => $dataoptions,
            'url' => $CFG->wwwroot
        ];
    }

    /**
     * Returns description of method result value.
     */
    public static function  responsibilitydisplay_returns() {
        return new external_single_structure([
          'options' => new external_value(PARAM_RAW, 'The paging data for the service'),
          'url' => new external_value(PARAM_RAW, 'url'),
          'dataoptions' => new external_value(PARAM_RAW, 'The data for the service'),
          'totalcount' => new external_value(PARAM_INT, 'total number of challenges in result set'),
          'filterdata' => new external_value(PARAM_RAW, 'total number of challenges in result set'),
          'length' => new external_value(PARAM_RAW, 'total number of challenges in result set'),
          'records' => new external_single_structure([
                          'hascourses' => new external_multiple_structure(
                              new external_single_structure([
                                  'id' => new external_value(PARAM_INT, 'id'),
                                  'roleid' => new external_value(PARAM_INT, 'roleid'),
                                  'jobid' => new external_value(PARAM_INT, 'jobid'),
                                  'jobrolename' => new external_value(PARAM_RAW, 'jobrolename'),
                                  'jobfamilyname' => new external_value(PARAM_RAW, 'jobfamilyname'),
                                  'responsibility' => new external_value(PARAM_RAW, 'responsibility'),   
                              ])
                          ),
                          'request_view' => new external_value(PARAM_INT, 'request_view', VALUE_OPTIONAL),
                          'nocourses' => new external_value(PARAM_BOOL, 'nocourses', VALUE_OPTIONAL),
                          'length' => new external_value(PARAM_INT, 'length', VALUE_OPTIONAL),
                              //'actionview' => new external_value(PARAM_BOOL, 'actionview', VALUE_OPTIONAL)
                             
                      ])
            
        ]);
    } 


    public static function getmainsectors_parameters(){

        return new external_function_parameters([
            'offset' => new external_value(PARAM_INT, 'Number of items to skip from the begging of the result set',
                VALUE_DEFAULT, 0),
            'limit' => new external_value(PARAM_INT, 'Maximum number of results to return',
                VALUE_DEFAULT, 0),       
            'isArabic'=>new external_value(PARAM_RAW, 'Is arabic or not', VALUE_DEFAULT, false),
            'pageSize' => new external_value(PARAM_INT, 'Page Size', VALUE_DEFAULT, 5),
            'pageNumber' => new external_value(PARAM_INT, 'Page Number',VALUE_DEFAULT, 1),
     
        ]);
  }

  public static  function getmainsectors($offset = 0, $limit = 0,$isarabic,$pagesize,$pagenumber){
      global $DB;

      $context = context_system::instance();
      $params = self::validate_parameters(
        self::getmainsectors_parameters(),
        [
            'offset' => $offset,
            'limit' => $limit,    
            'isArabic' => $isarabic,
            'pageSize'=>$pagesize,
            'pageNumber' => $pagenumber,
          
        ]
    );      
        $offset = $params['offset'];
        $limit =$pagesize;      
        $stable = new \stdClass();
        $stable->thead = false;
        $stable->start = ($pageNumber > 1) ? (($pageNumber-1)* $pageSize) : 0;
        $stable->length = $limit;
        $stable->language = $isarabic;
        $data = (new local_sector\local\sectors)->get_main_sectors($stable); 
        if($data){           
            $event = \local_sector\event\success_response::create(
                array(
                'context'=>$context,
                'objectid' => 1,
                'other'=>array(
                    'Function'=>'local_sector_get_main_sectors',
                    'Success'=>'Successfully Fetched the Response'
                )
                )
            );
            $event->trigger();
            return [
           'pageData' => $data['sectors'],
           'totalItemCount' => $data['totalsectors'],
           'pageSize'=> $pagesize ,
           'pageNumber'=> $pagenumber
        ];
    }
    else {              
        $event = \local_sector\event\error_response::create(
            array( 
                'context'=>$context,
                'objectid' =>0,
                'other'=>array(
                    'Function'=>'local_sector_get_main_sectors',
                    'Error'=>'Invalid Response Value Detected'

                )
                )
            );
        $event->trigger();

    }
}

  public static function getmainsectors_returns() {
    return new external_single_structure([       
        'pageData' => new external_multiple_structure(
            new external_single_structure(
                array(
                    'code' => new external_value(PARAM_RAW, 'Code'),  
                    'name' => new external_value(PARAM_TEXT, 'Name'), 
                    'description' => new external_value(PARAM_RAW, 'Name'),                                        
                    'value' => new external_value(PARAM_INT, 'Value'),
                    'parentValue' => new external_value(PARAM_RAW, 'parentValue'),                    
                    )
            )
        ),
                'totalItemCount'=>new external_value(PARAM_INT, 'total count'),
                'pageSize' => new external_value(PARAM_INT, 'Page Size'),
                'pageNumber' => new external_value(PARAM_INT, 'Page Number'),
            ]);
        }




        public static function getjobfamilies_sectorid_parameters(){

            return new external_function_parameters([
                'offset' => new external_value(PARAM_INT, 'Number of items to skip from the begging of the result set',
                    VALUE_DEFAULT, 0),
                'limit' => new external_value(PARAM_INT, 'Maximum number of results to return',
                    VALUE_DEFAULT, 0),       
                'isArabic'=>new external_value(PARAM_RAW, 'Is arabic or not', VALUE_DEFAULT, false),
                'pageSize' => new external_value(PARAM_INT, 'Page Size', VALUE_DEFAULT, 5),
                'pageNumber' => new external_value(PARAM_INT, 'Page Number',VALUE_DEFAULT, 1),
                'sectorId' => new external_value(PARAM_TEXT, 'sectorId',VALUE_DEFAULT,null),
                'segmentId' =>new external_value(PARAM_TEXT, 'segmentId',VALUE_DEFAULT,null),
         
            ]);
      }
    
      public static  function getjobfamilies_sectorid($offset = 0, $limit = 0,$isArabic,$pageSize,$pageNumber,$sectorId, $segmentId){
          global $DB;
          $context = context_system::instance();
          $params = self::validate_parameters(
            self::getjobfamilies_sectorid_parameters(),
            [
                'offset' => $offset,
                'limit' => $limit,    
                'isArabic' => $isArabic,
                'pageSize'=>$pageSize,
                'pageNumber' => $pageNumber,
                'sectorId'  => $sectorId,
                'segmentId'  => $segmentId,
              
            ]
        );      
            $offset = $params['offset'];
            $limit =$pagesize;      
            $stable = new \stdClass();
            $stable->thead = false;
            $stable->start = ($pageNumber > 1) ? (($pageNumber-1)* $pageSize) : 0;
            $stable->length = $limit;
            $stable->isArabic = $isArabic;
            $stable->sectorId = $sectorId;
            $stable->segmentId = $segmentId;
            $mainData = (new local_sector\local\sectors)->get_maindata($stable); 
            $pageData = (new local_sector\local\sectors)->get_jobfamilies_sectorid($stable);  


            if( $mainData &&  $pageData){
                $event = \local_sector\event\success_response::create(
                    array(
                    'context'=>$context,
                    'objectid' => 1,
                    'other'=>array(
                        'Function'=>'local_sector_get_jobfamiles_sectorid',
                        'Success'=>'Successfully Fetched the Response'
                    )
                    )
                );
                $event->trigger();              
            return [
                'mainData' => $mainData['mainData'],
                'pageData' => $pageData['pageData'] ,
                'totalItemCount' => $pageData['totaljobfamilies'] ? $pageData['totaljobfamilies'] : 0,
                'pageSize' => $pageSize ,
                'pageNumber'=> $pageNumber
            ];
        } else{
            $event = \local_sector\event\error_response::create(
                array( 
                    'context'=>$context,
                    'objectid' =>0,
                    'other'=>array(
                        'Function'=>'local_sector_get_jobfamiles_sectorid',
                        'Error'=>'Invalid Response Value Detected'
    
                    )
                    )
                );
            $event->trigger();
    

        }

    }
    
    public static function getjobfamilies_sectorid_returns() {
        return new external_single_structure([ 
            'mainData' => new external_multiple_structure(
                new external_single_structure(
                    array(                        
                        'code' => new external_value(PARAM_RAW, 'Code'),  
                        'name' => new external_value(PARAM_TEXT, 'Name'),
                        'description' => new external_value(PARAM_RAW, 'Description'), 
                        'value' => new external_value(PARAM_INT, 'Value'),                          
                        'parentvalue' => new external_value(PARAM_TEXT, 'Parent Value'),                        
                    )
                )
            ), '', VALUE_OPTIONAL,       
            'pageData' => new external_multiple_structure(
                new external_single_structure(
                    array(
                        'iscommon' => new external_value(PARAM_RAW, 'iscommon'), 
                        'code' => new external_value(PARAM_RAW, 'Code'),  
                        'name' => new external_value(PARAM_TEXT, 'Name'), 
                        'value' => new external_value(PARAM_INT, 'Value'),
                        'description' => new external_value(PARAM_RAW, 'Description'),
                        'parentvalue' => new external_value(PARAM_TEXT, 'Parent Value'),                         
                    )
                )
            ), '', VALUE_OPTIONAL,
            'totalItemCount'=>new external_value(PARAM_INT, 'total count'),
            'pageSize' => new external_value(PARAM_INT, 'Page Size'),
            'pageNumber' => new external_value(PARAM_INT, 'Page Number'),
        ]);
    }
    public static function getjobrole_jobfamilyid_parameters(){

                return new external_function_parameters([
                    'offset' => new external_value(PARAM_INT, 'Number of items to skip from the begging of the result set',
                        VALUE_DEFAULT, 0),
                    'limit' => new external_value(PARAM_INT, 'Maximum number of results to return',
                        VALUE_DEFAULT, 0),       
                        'isArabic'=>new external_value(PARAM_RAW, 'Is arabic or not', VALUE_DEFAULT, false),
                        'pageSize' => new external_value(PARAM_INT, 'Page Size', VALUE_DEFAULT, 5),
                        'pageNumber' => new external_value(PARAM_INT, 'Page Number',VALUE_DEFAULT, 1),
                        'familyId' => new external_value(PARAM_INT, 'Family Id',VALUE_DEFAULT, 0)
             
                ]);
    }
        
    public static  function getjobrole_jobfamilyid($offset = 0, $limit = 0,$isArabic,$pagesize,$pagenumber,$jobfamilyid){
              global $DB;
              require_login();
              $context = context_system::instance();
              $params = self::validate_parameters(
                self::getjobrole_jobfamilyid_parameters(),
                [
                    'offset' => $offset,
                    'limit' => $limit,    
                    'isArabic' => $isArabic,
                    'pageSize'=>$pagesize,
                    'pageNumber' => $pagenumber,
                    'familyId'  => $jobfamilyid
                  
                ]
            );      
                $offset = $params['offset'];
                $limit =$pagesize;      
                $stable = new \stdClass();
                $stable->thead = false;
                $stable->start = ($pageNumber > 1) ? (($pageNumber-1)* $pageSize) : 0;
                $stable->length = $limit;
                $stable->language = $isArabic;
                $jobfamilies = (new local_sector\local\sectors)->getjobfamily($isArabic,$jobfamilyid);               
                $data = (new local_sector\local\sectors)->get_jobrole_jobfamilyid($stable, $jobfamilyid); 
                if($data && $jobfamilies  ){
                    $event = \local_sector\event\success_response::create(
                        array(
                        'context'=>$context,
                        'objectid' => 1,
                        'other'=>array(
                            'Function'=>'local_sector_get_jobrole_jobfamilyid',
                            'Success'=>'Successfully Fetched the Response'
                        )
                        )
                    );
                    $event->trigger();            
                    return[
                        'mainData'=> $jobfamilies['jobfamily'],
                        'pageData'=>$data['jobroles'],
                        'totalItemCount' =>$data['totaljobroles'],
                        'pageSize'=>$pagesize ,
                        'pageNumber'=> $pagenumber,
                    ]; 
                
            } else {              
                $event = \local_sector\event\error_response::create(
                    array( 
                        'context'=>$context,
                        'objectid' =>0,
                        'other'=>array(
                            'Function'=>'local_sector_get_jobrole_jobfamilyid',
                            'Error'=>'Invalid Response Value Detected'

                        )
                        )
                    );
                $event->trigger();

            }
      
            

         
        }
        
    public static function getjobrole_jobfamilyid_returns() {

        return new external_single_structure([       
            'mainData' => new external_multiple_structure(
                new external_single_structure(
                    array(
                        'iscommon' => new external_value(PARAM_RAW, 'Code'), 
                        'code' => new external_value(PARAM_RAW, 'Code'),  
                        'name' => new external_value(PARAM_TEXT, 'Name'),
                        'description' => new external_value(PARAM_RAW, 'Description'), 
                        'value' => new external_value(PARAM_INT, 'Value'),                          
                        'parentvalue' => new external_value(PARAM_RAW, 'Parent Value'),                         
                        )
                )
            ),
            'pageData' => new external_multiple_structure(
                    new external_single_structure(
                        array(
                            'responsibilities'  =>  new external_multiple_structure(
                                new external_single_structure(
                                array(
                                    'responsibility' =>new external_value(PARAM_RAW, 'responsibility')
                                )
                                )
                            ),
                            'description' => new external_value(PARAM_RAW, 'Description'),
                            'code' => new external_value(PARAM_RAW, 'Code'),  
                            'name' => new external_value(PARAM_TEXT, 'Name'), 
                            'value' => new external_value(PARAM_INT, 'Value'),                           
                            'parentvalue' => new external_value(PARAM_RAW, 'Parent Value'), 
                                              
                            )
                    )
            ),
            'totalItemCount'=>new external_value(PARAM_INT, 'total count'),
            'pageSize' => new external_value(PARAM_INT, 'Page Size'),
            'pageNumber' => new external_value(PARAM_INT, 'Page Number'),
        ]);
    }

    public static function getsectorbyparentid_parameters(){

        return new external_function_parameters([
            'parentId' => new external_value(PARAM_INT, 'parentId',VALUE_DEFAULT, 0),
            'isArabic'=>new external_value(PARAM_RAW, 'isArabic', VALUE_DEFAULT, false),
            'pageSize' => new external_value(PARAM_INT, 'Page Size', VALUE_DEFAULT, 5),
            'pageNumber' => new external_value(PARAM_INT, 'Page Number',VALUE_DEFAULT, 1),
            'query' => new external_value(PARAM_RAW, 'query',VALUE_DEFAULT,null),
                
     
        ]);
    }
        
    public static  function getsectorbyparentid($parentId,$isArabic,$pageSize,$pageNumber,$query){
      global $DB;
      require_login();
      $context = context_system::instance();
      $params = self::validate_parameters(
            self::getsectorbyparentid_parameters(),
            [
                'parentId' => $parentId,
                'isArabic' => $isArabic,
                'pageSize'=>$pageSize,
                'pageNumber' => $pageNumber,
                'query'  => $query
              
            ]
        );    
        $stable = new \stdClass();  
        $stable->thead = false;
        $stable->start = ($pageNumber > 1) ? (($pageNumber-1)* $pageSize) : 0;
        $stable->length = $pageSize;
        $stable->isArabic = $isArabic;
        $stable->query = $query;
        $stable->parentId = $parentId;

        $sectordata = (new local_sector\local\sectors)->getsectorinfo($isArabic,$parentId);               
        $data = (new local_sector\local\sectors)->getsegmentsbysectorid($stable); 
        if( $sectordata &&  $data ) {
            $event = \local_sector\event\success_response::create(
                array(
                'context'=>$context,
                'objectid' => 1,
                'other'=>array(
                    'Function'=>'local_sector_get_sector_by_parentid',
                    'Success'=>'Successfully Fetched the Response'
                )
                )
            );
            $event->trigger(); 
            return[
                'mainData'=> $sectordata,
                'pageData'=>$data['segments'],
                'totalItemCount' =>$data['totalsegments'],
                'pageSize'=>$pageSize,
                'pageNumber'=> $pageNumber
            ];
        } else{
            $event = \local_sector\event\error_response::create(
                array( 
                    'context'=>$context,
                    'objectid' =>0,
                    'other'=>array(
                        'Function'=>'local_sector_get_sector_by_parentid',
                        'Error'=>'Invalid Response Value Detected'

                    )
                    )
                );
            $event->trigger();

        }
      
 
    }
        
    public static function getsectorbyparentid_returns() {

        return new external_single_structure([       
            'mainData' => new external_single_structure(
                 array(

                    'code' => new external_value(PARAM_RAW, 'Code'),  
                    'name' => new external_value(PARAM_TEXT, 'Name'),
                    'description' => new external_value(PARAM_RAW, 'Description'), 
                    'value' => new external_value(PARAM_INT, 'Value'),                          
                    'parentvalue' => new external_value(PARAM_RAW, 'Parent Value')       

                )
              
            ),
            'pageData' => new external_multiple_structure(
                new external_single_structure(
                    array(
                        'code' => new external_value(PARAM_RAW, 'Code'),  
                        'name' => new external_value(PARAM_TEXT, 'Name'), 
                        'description' => new external_value(PARAM_RAW, 'Description'),
                        'value' => new external_value(PARAM_INT, 'Value'),                           
                        'parentvalue' => new external_value(PARAM_RAW, 'Parent Value'), 
                                              
                    )
                )
            ),
            'totalItemCount'=>new external_value(PARAM_INT, 'total count'),
            'pageSize' => new external_value(PARAM_INT, 'Page Size'),
            'pageNumber' => new external_value(PARAM_INT, 'Page Number'),
        ]);
    }

     public static function getjobfamilycareerpathsimages_parameters(){

        return new external_function_parameters([
            'isArabic' => new external_value(PARAM_RAW, 'isArabic', VALUE_DEFAULT, false),
            'JobFamilyCode' => new external_value(PARAM_RAW, 'JobFamilyCode', VALUE_DEFAULT, null),

        ]);
    }
        
    public static  function getjobfamilycareerpathsimages($isArabic,$JobFamilyCode = null){
      global $DB;
      require_login();
      $context = context_system::instance();
      $params = self::validate_parameters(
            self::getjobfamilycareerpathsimages_parameters(),
            [
                
                'isArabic' => $isArabic,
                'JobFamilyCode' => $JobFamilyCode,
                
              
            ]
        );    
        $data = (new local_sector\local\sectors)->get_jobfamilycareerpathsimages($isArabic,$JobFamilyCode);
        if($data) {
            return $data;
        } 

    }
    public static function getjobfamilycareerpathsimages_returns() {
        return new external_multiple_structure(
            new external_single_structure(
                array(
                    'id' => new external_value(PARAM_INT, 'id'), 
                    'Rank' => new external_value(PARAM_TEXT, 'rank'), 
                    'familyCode' => new external_value(PARAM_TEXT, 'FamilyCode'), 
                    'creerPathImages' => new external_value(PARAM_RAW, 'Url'), 
                    'creerPathImages' => new external_multiple_structure(
                        new external_single_structure(
                            array(
                            'url' => new external_value(PARAM_RAW, 'url'),
                            )
                        )
                    ), '', VALUE_OPTIONAL,
                )
            )
        );

    }

    public static function candeleteelement_parameters() {
        return new external_function_parameters(
            array(
                'elementid' => new external_value(PARAM_INT, 'elemntid'),
                'elementtype' => new external_value(PARAM_RAW, 'elementtype'),
               
            )
        );
    }
    public static function candeleteelement($elementid,$elementtype){
        global $DB;
        $params = self::validate_parameters(self::candeleteelement_parameters(),
                                    ['elementid' => $elementid,
                                     'elementtype' => $elementtype,
                                    ]);
        if($elementid && $elementtype){
            if($elementtype == 'sector') {
                $returndata = (new controller)->is_sector_mapped($elementid);
            } else if($elementtype == 'segment') {
                $returndata = (new controller)->is_segment_mapped($elementid);
            } else if($elementtype == 'jobfamily') {
                $returndata = (new controller)->is_jobfamily_mapped($elementid);
            } else if($elementtype == 'jobrole') {
                $returndata = (new controller)->is_jobrole_mapped($elementid);
            } else if($elementtype == 'organization') {
                $returndata = (new \local_organization\organization)->is_organization_mapped($elementid);
            } else if($elementtype == 'competency') {
                $returndata = (new \local_competency\competency)::is_competence_mapped($elementid);
            }else if($elementtype == 'trainingtopic') {
               $returndata = (new \local_trainingprogram\local\trainingprogram)->is_trainingtopic_mapped($elementid);
            }
            $data = new stdClass();
            $data->candelete = $returndata;
            return $data;
        } else {
            throw new moodle_exception('Error while getting the data');
        }
    }   
    public static function candeleteelement_returns() {
        return new external_single_structure([
            'candelete' => new external_value(PARAM_INT, 'candelete'),

        ]);
    }

    
}
