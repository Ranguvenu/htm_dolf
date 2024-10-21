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
/**
 * Course list block caps.
 *
 * @author eabyas  <info@eabyas.in>
 * @package    Bizlms
 * @subpackage block_courselister
 */

defined('MOODLE_INTERNAL') || die;
require_once("$CFG->libdir/externallib.php");
require_once($CFG->dirroot.'/course/lib.php');
require_once( '../../config.php' );

class block_faq_external  extends external_api{


/**
     * Returns description of method parameters.
     *
     * @return external_function_parameters.
     */
    public static function getfaq_parameters() {
        return new external_function_parameters([
            'options' => new external_value(PARAM_RAW, 'The paging data for the service'),
            'dataoptions' => new external_value(PARAM_RAW, 'The data for the service'),
            'offset' => new external_value(PARAM_INT, 'Number of items to skip from the begging of the result set',
                VALUE_DEFAULT, 0),
            'limit' => new external_value(PARAM_INT, 'Maximum number of results to return',
                VALUE_DEFAULT, 0),
            'filterdata' => new external_value(PARAM_RAW, 'filters applied'),
            'contextid' => new external_value(PARAM_INT, 'Maximum number of results to return',
                VALUE_DEFAULT, 1),
            'isArabic' => new external_value(PARAM_RAW, 'isArabic', VALUE_DEFAULT, false),
        ]);
    }

    /**
     * Gets the list of courselister for the given criteria. The courselister
     * will be exported in a summaries format and won't include all of the
     * courselister data.
     *
     * @param int $userid Userid id to find courselister
     * @param int $limit Maximum number of results to return
     * @param int $offset Number of items to skip from the beginning of the result set.
     */
    public static function getfaq($options, $dataoptions, 
        $offset = 0, $limit = 0,$filterdata,$contextid,$isArabic) {
        global $OUTPUT, $CFG, $DB,$USER,$PAGE;
        $sitecontext = context_system::instance();
        $PAGE->set_context($sitecontext);
        $params = self::validate_parameters(
            self::getfaq_parameters(),
             [
                'options' => $options,
                'dataoptions' => $dataoptions,
                'offset' => $offset,
                'contextid' => $contextid,
                'limit' => $limit,
                'filterdata' => $filterdata,
            ]
        );
     
        $offset = $params['offset'];
        $limit = $params['limit'];
        $filtervalues = json_decode($filterdata);

        $stable = new \stdClass();
        $stable->sort = 'visible DESC, sortorder ASC';
        $stable->thead = true;
        $stable->search =$data_object->search_query;
        $stable->thead = false;
        $stable->start = $offset;
        $stable->length = $limit;
        $stable->isarabic = $isArabic;
        $data = (new block_faq\faq)->getlistoffaq($stable,$filterdata);
        $totalcount = $data['totalfaqs'];
        return [
            'totalcount' => $totalcount,
            'length' => $totalcount,
            'records' =>$data,
            'filterdata' => $filterdata,
            'options' => $options,
            'dataoptions' => $dataoptions,
            'url' => $CFG->wwwroot,
            'isArabic' => $isArabic,
        ];

    }

    /**
     * Returns description of method result value.
     */
    public static function getfaq_returns() {
        return new external_single_structure([
          'totalcount' => new external_value(PARAM_INT, 'total number of challenges in result set'),
          'records' => new external_single_structure(
                  array(
                      'hasfaqs' => new external_multiple_structure(
                          new external_single_structure(
                              array(
                                  'id' => new external_value(PARAM_INT, 'id'),
                                  'title' => new external_value(PARAM_RAW, 'title'),
                                  'description' => new external_value(PARAM_RAW, 'description'),
                                  'rank' => new external_value(PARAM_RAW, 'rank'),
                                  'actions' => new external_value(PARAM_RAW, 'actions'),
                                  'managefaq' => new external_value(PARAM_BOOL, 'managefaq'),
                                  
                              )
                          )
                      ),
                      
                    'length' => new external_value(PARAM_INT, 'length', VALUE_OPTIONAL),
                  )
              )
        ]);
    }


      public function create_faq_parameters() {
        return new external_function_parameters(
            array(
                'faq' => new external_multiple_structure(
                    new external_single_structure(
                        array(
                            'title' => new external_value(PARAM_ALPHANUM, 'faq title'),
                            'description' => new external_value(PARAM_RAW, 'faq description'),
                            'rank' => new external_value(PARAM_RAW, 'faq rank'),
                            
                        )
                    )
                )
            )
        );
    }

    public function create_faq($faq) {
        global $DB;
        $context = context_system::instance();
        self::validate_context($context);
        $params = self::validate_parameters(self::create_organization_parameters(), array('faq' => $faq));
        $requiredparams = ['title','description','rank'];
        try {
           // $object = json_decode(json_encode($params['organization'][0]), FALSE);
            foreach($requiredparams as $param) {
                if(empty($params['faq'][0][$param])) {
                    throw new moodle_exception(get_string('invalidvalue', 'block_faq', $param));
                }
            }
            foreach($params['faq'] as $faq) {
                $faq = (object)$faq;
                $faq->title = $faq->title;
                $faq->description = $faq->description;
                $faq->rank = $faq->faqrank;
                $return = (new block_faq\faq)->add_update_faq($faq);
                $status = get_string('faqcreatedsuccessfully', 'block_faq');
            }
        } catch(Exception $e){
            // throw new moodle_exception('Error in creating the organization');
            $return = 0;
            $status = $e->getMessage();
        }

        return ['id' => $return->id, 'message' => $status];
    }

    public function create_faq_returns() {
        return new external_function_parameters(
            array(
                'id' => new external_value(PARAM_INT, 'faq id'),
                'message' => new external_value(PARAM_TEXT, 'status')
            )
        );
        return new external_value(PARAM_INT, 'return');
    }


    public static function faq_info_parameters() {
        return new external_function_parameters(
            array(
                'id' => new external_value(PARAM_INT, 'id', 0),
                )
        );
    }
    public static function faq_info($id) {
        global $DB;
        require_login();
        $params = self::validate_parameters(self::faq_info_parameters(),
                                    ['id' => $id]);
        $data = (new block_faq\faq)->faq_info($id);
        return [
            'options' => $data,
        ];
    }

  /**
   * Returns description of method result value
   * @return external_description
   */

    public static function faq_info_returns() {
        return new external_single_structure([
          'options' => new external_value(PARAM_RAW, 'The paging data for the service'),
        ]);
    }


    public static function deletefaq($id){
        global $DB;
        $params = self::validate_parameters(self::deletefaq_parameters(),
                                    ['id' => $id]);
        if($id){
           $id = $DB->delete_records('faq', array('id' => $id));
          
            return true;
        }else {
            throw new moodle_exception('Error in deleting');
            return false;
        }
    }
   
    public static function deletefaq_returns() {
        return new external_value(PARAM_BOOL, 'return');
    }

    public static function deletefaq_parameters(){
        return new external_function_parameters(
            array(
                'id' => new external_value(PARAM_INT, 'id', 0),
                
                )
        );
    }


    //service

    public static function getfaqser_parameters() {
        return new external_function_parameters([
            
            'isArabic' => new external_value(PARAM_RAW, 'isArabic', VALUE_DEFAULT, false),
        ]);
    }

    /**
     * Gets the list of courselister for the given criteria. The courselister
     * will be exported in a summaries format and won't include all of the
     * courselister data.
     *
     * @param int $userid Userid id to find courselister
     * @param int $limit Maximum number of results to return
     * @param int $offset Number of items to skip from the beginning of the result set.
     */
    public static function getfaqser($isArabic) {
        global $OUTPUT, $CFG, $DB,$USER,$PAGE;
        $sitecontext = context_system::instance();
        $PAGE->set_context($sitecontext);
        $params = self::validate_parameters(
            self::getfaqser_parameters(),
            [
                
            ]
        );
        $stable = new \stdClass();
        $stable->isArabic = $isArabic;
        $data = (new block_faq\faq)->getlistoffaqser($stable);
        $totalcount = $data['totalfaqs'];
        return $data['hasfaqs'];

    }

    /**
     * Returns description of method result value.
     */
    public static function getfaqser_returns() {
        return new external_multiple_structure(
            new external_single_structure(
                array(
                     'id' => new external_value(PARAM_INT, 'id'),
                      'rank' => new external_value(PARAM_INT, 'rank'),
                      'questionTitle' => new external_value(PARAM_RAW, 'title'),
                      'questionAnswer' => new external_value(PARAM_RAW, 'description'),
                )

            )
        );

    }  
    

}
