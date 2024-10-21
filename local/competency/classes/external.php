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
 * Class containing helper methods for processing data requests.
 *
 * @package    local_competency
 * @copyright  2022 e abyas  <info@eabyas.com>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
namespace local_competency;

defined('MOODLE_INTERNAL') || die();

require_once("$CFG->libdir/externallib.php");
require_once($CFG->dirroot . '/local/competency/lib.php');

use local_competency\competency as competency;
use coding_exception;
use context_helper;
use context_system;
use context_user;
use core\invalid_persistent_exception;
use core\notification;
use core_user;
use dml_exception;
use external_api;
use external_description;
use external_function_parameters;
use external_multiple_structure;
use external_single_structure;
use external_value;
use external_warnings;
use invalid_parameter_exception;
use moodle_exception;
use required_capability_exception;
use restricted_context_exception;
use external_settings;

/**
 * Class external.
 *
 * The external API for the Data Privacy tool.
 *
 * @copyright  2017 Jun Pataleta
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class external extends external_api {

     /**
     * Returns description of method parameters.
     *
     * @return external_function_parameters.
     */
    public static function get_competencies_parameters() {
        return new external_function_parameters([
            'options' => new external_value(PARAM_RAW, 'The paging data for the service', VALUE_OPTIONAL),
            'dataoptions' => new external_value(PARAM_RAW, 'The data for the service', VALUE_OPTIONAL),
            'offset' => new external_value(PARAM_INT, 'Number of items to skip from the begging of the result set',
                VALUE_DEFAULT, 0),
            'limit' => new external_value(PARAM_INT, 'Maximum number of results to return',
                VALUE_DEFAULT, 0),
            'filterdata' => new external_value(PARAM_RAW, 'filters applied', VALUE_OPTIONAL),
        ]);
    }

    /**
     * Gets the list of competencies for the given criteria. The competencies
     * will be exported in a summaries format and won't include all of the
     * competencies data.
     *
     * @param int $userid Userid id to find competencies
     * @param int $contextid The context id where the competencies will be rendered
     * @param int $limit Maximum number of results to return
     * @param int $offset Number of items to skip from the beginning of the result set.
     * @return array The list of competencies and total competency count.
     */
    public static function get_competencies(
        $options = false,
        $dataoptions = false,
        $offset = 0,
        $limit = 0,
        $filterdata = false
    ) {
        global $OUTPUT, $CFG, $DB,$USER,$PAGE;
        $sitecontext = context_system::instance();
        require_login();
        $PAGE->set_url('/local/competency/index.php', array());
        $PAGE->set_context($sitecontext);
        // Parameter validation.
        $params = self::validate_parameters(
            self::get_competencies_parameters(),
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
        $competencies=competency::get_competencies($stable,$filtervalues);
        $totalcount=$competencies['competenciescount'];
        $stable->thead = false;
        $stable->start = $offset;
        $stable->length = $limit;

        $data = array();

        if($totalcount>0){

            $renderer = $PAGE->get_renderer('local_competency');

            $data = array_merge($data,$renderer->list_competencies($stable,$filtervalues));
        }
        return [
            'totalcount' => $totalcount,
            'records' =>$data,
            'options' => $options,
            'dataoptions' => $dataoptions,
            'filterdata' => $filterdata,
            'nodata' => get_string('nocompetencys','local_competency')
        ];
    }

    /**
     * Returns description of method result value.
     */
    public static function  get_competencies_returns() {
        return new external_single_structure([
            'options' => new external_value(PARAM_RAW, 'The paging data for the service'),
            'dataoptions' => new external_value(PARAM_RAW, 'The data for the service'),
            'totalcount' => new external_value(PARAM_INT, 'total number of competencies in result set'),
            'filterdata' => new external_value(PARAM_RAW, 'The data for the service'),
            'nodata' => new external_value(PARAM_RAW, 'The data for the service'),
            'records' => new external_multiple_structure(
                new external_single_structure(
                    array(
                        'competencyid' => new external_value(PARAM_INT, 'competency id'),
                        'competencyfullname' => new external_value(PARAM_TEXT, 'competencyfullname'),
                        'name' => new external_value(PARAM_RAW, 'competency name'),
                        'code' => new external_value(PARAM_RAW, 'competency name'),
                        'level' => new external_value(PARAM_RAW, 'competency level'),
                        'type' => new external_value(PARAM_RAW, 'competency type'),
                        'noperformance' => new external_value(PARAM_INT, 'competency level'),
                        'action' => new external_value(PARAM_RAW, 'competency actions'),
                        'edit' => new external_value(PARAM_RAW, 'competency edit'),
                        'delete' => new external_value(PARAM_RAW, 'competency delete'),
                        'assignperformance' => new external_value(PARAM_RAW, 'competency assignperformance'),
                        'viewperformance' => new external_value(PARAM_RAW, 'competency viewperformance')
                    )
                )
            )
        ]);
    }
    /**
     * Returns description of method parameters.
     *
     * @return external_function_parameters.
     */
    public static function get_competencypc_parameters() {
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
    /**
     * Gets the list of competencies for the given criteria. The competencies
     * will be exported in a summaries format and won't include all of the
     * competencies data.
     *
     * @param int $userid Userid id to find competencies
     * @param int $contextid The context id where the competencies will be rendered
     * @param int $limit Maximum number of results to return
     * @param int $offset Number of items to skip from the beginning of the result set.
     * @return array The list of competencies and total competency count.
     */
    public static function get_competencypc(
        $options,
        $dataoptions,
        $offset = 0,
        $limit = 0,
        $filterdata
    ) {
        global $OUTPUT, $CFG, $DB,$USER,$PAGE;
        $sitecontext = context_system::instance();
        require_login();
        $PAGE->set_url('/local/competency/index.php', array());
        $PAGE->set_context($sitecontext);
        // Parameter validation.
        $params = self::validate_parameters(
            self::get_competencypc_parameters(),
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
        $stable->competencyid = $data_object->competencyid;
        $stable->questionid = isset($data_object->questionid) ? $data_object->questionid : 0 ;
        $competencies=competency::get_competency_performances($stable,$filtervalues);

        $competencytypes=(new competency)::constcompetencytypes();


        $totalcount=$competencies['competencypccount'];
        $stable->thead = false;
        $stable->start = $offset;
        $stable->length = $limit;
        $data = array();

        if($totalcount>0){

            $renderer = $PAGE->get_renderer('local_competency');

            $data = array_merge($data,$renderer->lis_competency_performances($stable,$filtervalues));
        }

        $competency=competency::get_competencies($stable,$filtervalues);

        $questionexperts=competency::get_questionexperts($stable->questionid);

        $chunk=array_chunk($questionexperts, 2);

        $questionexpertsdata='N/A';

        if(!empty($questionexperts)){

            $count=count($questionexperts);

            if($count >2){
                $count=' (<strong>+'.($count-2).'</strong>)';
            }else{
                $count='';
            }

            $questionexpertsdata=implode(',',$chunk[0]).$count;
        }

        return [
            'totalcount' => $totalcount,
            'records' =>$data,
            'options' => $options,
            'dataoptions' => $dataoptions,
            'filterdata' => $filterdata,
            'nodata' => get_string('nocompetencypcs','local_competency'),
            'competencyname' => $competency->name,
            'competencytype' => ($type=$competencytypes[$competency->type]) ? $type : $competency->type,
            'questionbankid' =>$competency->questionbankid,
            'questiontext' => mb_convert_encoding(clean_text(html_to_text(html_entity_decode($competency->questiontext))), 'UTF-8'),
            'questionexperts' => $questionexpertsdata
        ];
    }

    /**
     * Returns description of method result value.
     */
    public static function  get_competencypc_returns() {
        return new external_single_structure([
            'options' => new external_value(PARAM_RAW, 'The paging data for the service'),
            'dataoptions' => new external_value(PARAM_RAW, 'The data for the service'),
            'totalcount' => new external_value(PARAM_INT, 'total number of competencies in result set'),
            'filterdata' => new external_value(PARAM_RAW, 'The data for the service'),
            'nodata' => new external_value(PARAM_RAW, 'The data for the service'),
            'competencyname' => new external_value(PARAM_TEXT, 'The data for competency name'),
            'competencytype' => new external_value(PARAM_RAW, 'The data for competency type'),
            'questionbankid' => new external_value(PARAM_INT, 'questionbankid competencies in result set',VALUE_OPTIONAL),
            'questiontext' => new external_value(PARAM_RAW, 'The data for competency question',VALUE_OPTIONAL),
            'questionexperts' => new external_value(PARAM_RAW, 'The data for competency question experts',VALUE_OPTIONAL),
            'records' => new external_multiple_structure(
                            new external_single_structure(
                                array(
                                    'competencyid' => new external_value(PARAM_INT, 'competency id'),
                                    'id' => new external_value(PARAM_INT, 'competency pc id'),
                                    'criterianame' => new external_value(PARAM_RAW, 'competency criterianame'),
                                    'kpiname' => new external_value(PARAM_RAW, 'competency pc kpiname'),
                                    'objectiveid' => new external_value(PARAM_RAW, 'competency pc objective'),
                                    'action' => new external_value(PARAM_RAW, 'competency pc actions'),
                                    'edit' => new external_value(PARAM_RAW, 'competency pc edit'),
                                    'delete' => new external_value(PARAM_RAW, 'competency pc delete'),
                                    'assignobjectives' => new external_value(PARAM_RAW, 'competency assign objectives'),
                                    'viewobjectives' => new external_value(PARAM_RAW, 'competency view objectives'),
                                )
                            )
            )
        ]);
    }
    /**
     * Parameter description for delete_data_competency().
     *
     * @since Moodle 3.5
     * @return external_function_parameters
     */
    public static function delete_data_competency_parameters() {
        return new external_function_parameters([
            'competencyid' => new external_value(PARAM_INT, 'The competency ID', VALUE_REQUIRED)
        ]);
    }

    /**
     * Delete a data competencyid.
     *
     * @since Moodle 3.5
     * @param int $competencyid The competencyid ID.
     * @return array
     * @throws invalid_persistent_exception
     * @throws coding_exception
     * @throws invalid_parameter_exception
     * @throws restricted_context_exception
     */
    public static function delete_data_competency($competencyid) {
        global $USER;

        $warnings = [];
        $params = external_api::validate_parameters(self::delete_data_competency_parameters(), [
            'competencyid' => $competencyid
        ]);
        $competencyid = $params['competencyid'];

        // Validate context and access to manage the registry.
        $context = context_system::instance();
        self::validate_context($context);

        $stable = new \stdClass();
        $stable->competencyid = $competencyid;
        $stable->thead = false;
        $stable->start = 0;
        $stable->length = -1;
        $competencies=competency::get_competencies($stable);
        $competencyexists = count($competencies) === 1;

        $result = false;
        if ($competencyexists) {
           
            $result = competency::delete_competency($competencyid);
            
        } else {
            $warnings[] = [
                'item' => $competencyid,
                'warningcode' => 'errorcompetencynotfound',
                'message' => get_string('errorcompetencynotfound', 'local_competency')
            ];
        }

        return [
            'result' => $result,
            'warnings' => $warnings
        ];
    }

    /**
     * Parameter description for delete_data_competency().
     *
     * @since Moodle 3.5
     * @return external_description
     */
    public static function delete_data_competency_returns() {
        return new external_single_structure([
            'result' => new external_value(PARAM_BOOL, 'The processing result'),
            'warnings' => new external_warnings()
        ]);
    }
    /**
     * Returns description of method parameters.
     *
     * @return external_function_parameters.
     */
    public static function get_mycompetencies_parameters() {
        return new external_function_parameters([

                'supported' => new external_value(PARAM_INT, 'supported',VALUE_OPTIONAL,1),
                'options' => new external_value(PARAM_RAW, 'The paging data for the service',VALUE_OPTIONAL),
                'dataoptions' => new external_value(PARAM_RAW, 'The data for the service',VALUE_OPTIONAL),
                'offset' => new external_value(PARAM_INT, 'Number of items to skip from the begging of the result set',
                    VALUE_DEFAULT, 0),
                'limit' => new external_value(PARAM_INT, 'Maximum number of results to return',
                    VALUE_DEFAULT, 0),
                 'filterdata' => new external_value(PARAM_RAW, 'The data for the service',VALUE_OPTIONAL),
                 
        ]);
    }

    /**
     * Gets the list of competencies for the given criteria. The competencies
     * will be exported in a summaries format and won't include all of the
     * competencies data.
     *
     * @param int $userid Userid id to find competencies
     * @param int $contextid The context id where the competencies will be rendered
     * @param int $limit Maximum number of results to return
     * @param int $offset Number of items to skip from the beginning of the result set.
     * @return array The list of competencies and total competency count.
     */
    public static function get_mycompetencies(


        $options = false,
        $dataoptions = false,
        $offset = 0,
        $limit = 0,
        $filterdata = false,
        $supported = false
        

    ) {
        global $OUTPUT, $CFG, $DB,$USER,$PAGE;
        $sitecontext = context_system::instance();
        require_login();
        $PAGE->set_url('/local/competency/mycompetency.php', array());
        $PAGE->set_context($sitecontext);
        // Parameter validation.
        $params = self::validate_parameters(
            self::get_mycompetencies_parameters(),
            [
                'options' => $options,
                'dataoptions' => $dataoptions,
                'offset' => $offset,
                'limit' => $limit,
                'filterdata' => $filterdata,
                'supported' => $supported ? $supported:0
                
            ]
        );
        $settings = external_settings::get_instance();
        $data_object = (json_decode($dataoptions));
        $offset = $params['offset'];
        $limit = $params['limit'];
        $supported = $params['supported'];
        $filtervalues = json_decode($filterdata);

        $stable = new \stdClass();
        $stable->nextlevel = isset($data_object->nextlevel) ? true : false ;
        $stable->supportedcompetencies = isset($data_object->supportedcompetencies) ? true : (($supported == 1)?true :false) ;
        $stable->thead = true;

        $competencies=competency::get_mycompetencies($stable,$filtervalues);
        $totalcount=$competencies['competenciescount'];
        $stable->thead = false;
        $stable->start = $offset;
        $stable->length = $limit;
        $stable->mlang =  $settings->get_lang();
        $data = array();

        if($totalcount>0){

            $renderer = $PAGE->get_renderer('local_competency');

            $data = array_merge($data,$renderer->lis_mycompetencies_bytype($stable,$filtervalues));

            $competenciesdata = (new competency)->get_my_competenciesdata($stable,$filtervalues);

        }
        return [
            'totalcount' => $totalcount,
            'records' =>$data,
            'options' => $options,
            'dataoptions' => $dataoptions,
            'filterdata' => $filterdata,
           'competencies' => $competenciesdata['competencies'] ? $competenciesdata['competencies'] : [],
            'nodata' => get_string('nocompetencys','local_competency'),
            'supportedcompetencies'=>$stable->supportedcompetencies,
            'jobrolecompetencies'=> (empty($data_object->nextlevel) && empty($data_object->supportedcompetencies)) ? true : false,
        ];
    }

    /**
     * Returns description of method result value.
     */
    public static function  get_mycompetencies_returns() {
        return new external_single_structure([
            'options' => new external_value(PARAM_RAW, 'The paging data for the service'),
            'dataoptions' => new external_value(PARAM_RAW, 'The data for the service'),
            'totalcount' => new external_value(PARAM_INT, 'total number of competencies in result set'),
            'filterdata' => new external_value(PARAM_RAW, 'The data for the service'),
            'nodata' => new external_value(PARAM_RAW, 'The data for the service'),
            'supportedcompetencies' => new external_value(PARAM_RAW, 'The data for the service'),
            'jobrolecompetencies' => new external_value(PARAM_BOOL, 'The data for the service'),
            'competencies' => new external_multiple_structure(
                new external_single_structure(
                    array(
                        'type' => new external_value(PARAM_RAW, 'typeId'),
                        'data' => new external_multiple_structure(
                            new external_single_structure(
                                array(
                                'id' => new external_value(PARAM_INT, 'value'),
                                'name' => new external_value(PARAM_TEXT, 'name'),
                                'code' => new external_value(PARAM_TEXT, 'code'),
                                'description' => new external_value(PARAM_RAW, 'description'),
                                'level' => new external_value(PARAM_TEXT, 'level'),
                                'typeId' => new external_value(PARAM_INT, 'typeId'),
                                )
                            )
                        ), '', VALUE_OPTIONAL,     
                    )
                )
            ), '', VALUE_OPTIONAL,
            'records' => new external_multiple_structure(
                new external_single_structure(
                    array(
                        'competencyid' => new external_value(PARAM_INT, 'competency id'),
                        'competencyfullname' => new external_value(PARAM_TEXT, 'competencyfullname'),
                        'code' => new external_value(PARAM_TEXT, 'competency name'),
                        'description' => new external_value(PARAM_RAW, 'description'),
                        'level' => new external_value(PARAM_RAW, 'competency level'),
                        'type' => new external_value(PARAM_RAW, 'competency type'),
                        'name' => new external_value(PARAM_RAW, 'competency name'),

                    )
                )
            )
        ]);
    }
    /**
     * Returns description of method parameters.
     *
     * @return external_function_parameters.
     */
    public static function get_myallcompetencies_parameters() {
        return new external_function_parameters([
                'options' => new external_value(PARAM_RAW, 'The paging data for the service', VALUE_OPTIONAL),
                'dataoptions' => new external_value(PARAM_RAW, 'The data for the service', VALUE_OPTIONAL),
                'offset' => new external_value(PARAM_INT, 'Number of items to skip from the begging of the result set',
                    VALUE_DEFAULT, 0),
                'limit' => new external_value(PARAM_INT, 'Maximum number of results to return',
                    VALUE_DEFAULT, 0),
                 'filterdata' => new external_value(PARAM_RAW, 'The data for the service', VALUE_OPTIONAL),
        ]);
    }

    /**
     * Gets the list of competencies for the given criteria. The competencies
     * will be exported in a summaries format and won't include all of the
     * competencies data.
     *
     * @param int $userid Userid id to find competencies
     * @param int $contextid The context id where the competencies will be rendered
     * @param int $limit Maximum number of results to return
     * @param int $offset Number of items to skip from the beginning of the result set.
     * @return array The list of competencies and total competency count.
     */
    public static function get_myallcompetencies(
        $options=false,
        $dataoptions=false,
        $offset = 0,
        $limit = 0,
        $filterdata=false
    ) {
        global $OUTPUT, $CFG, $DB,$USER,$PAGE;
        $sitecontext = context_system::instance();
        require_login();
        $PAGE->set_url('/local/competency/myallcompetency.php', array());
        $PAGE->set_context($sitecontext);
        // Parameter validation.
        $params = self::validate_parameters(
            self::get_myallcompetencies_parameters(),
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
        $competencies=competency::get_myallcompetencies($stable,$filtervalues);
        $totalcount=$competencies['competenciescount'];
        $stable->thead = false;
        $stable->start = $offset;
        $stable->length = $limit;

        $data = array();

        if($totalcount>0){

            $renderer = $PAGE->get_renderer('local_competency');

            $data = array_merge($data,$renderer->lis_myallcompetencies($stable,$filtervalues));
        }
        return [
            'totalcount' => $totalcount,
            'records' =>$data,
            'nodata' => get_string('nocompetencys','local_competency')
        ];
    }

    /**
     * Returns description of method result value.
     */
    public static function  get_myallcompetencies_returns() {
        return new external_single_structure([
            'totalcount' => new external_value(PARAM_INT, 'total number of competencies in result set'),
            'nodata' => new external_value(PARAM_RAW, 'The data for the service'),
            'records' => new external_multiple_structure(
                            new external_single_structure(
                                array(
                                    'competencyid' => new external_value(PARAM_INT, 'competency id'),
                                    'name' => new external_value(PARAM_RAW, 'competency name'),
                                    'code' => new external_value(PARAM_RAW, 'competency name'),
                                    'level' => new external_value(PARAM_RAW, 'competency level'),
                                    'type' => new external_value(PARAM_RAW, 'competency type'),
                                    'competencyname' => new external_value(PARAM_RAW, 'competency type'),
                                )
                            )
            )
        ]);
    }
    /**
     * Returns description of method parameters.
     *
     * @return external_function_parameters.
     */
    public static function get_objectivesinfo_parameters() {
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

    /**
     * Gets the list of competencies for the given criteria. The competencies
     * will be exported in a summaries format and won't include all of the
     * competencies data.
     *
     * @param int $userid Userid id to find competencies
     * @param int $contextid The context id where the competencies will be rendered
     * @param int $limit Maximum number of results to return
     * @param int $offset Number of items to skip from the beginning of the result set.
     * @return array The list of competencies and total competency count.
     */
    public static function get_objectivesinfo(
        $options,
        $dataoptions,
        $offset = 0,
        $limit = 0,
        $filterdata
    ) {
        global $OUTPUT, $CFG, $DB,$USER,$PAGE;
        $sitecontext = context_system::instance();
        require_login();
        $PAGE->set_url('/local/competency/index.php', array());
        $PAGE->set_context($sitecontext);
        // Parameter validation.
        $params = self::validate_parameters(
            self::get_objectivesinfo_parameters(),
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
        $stable->objtype = isset($data_object->objtype) ? $data_object->objtype : 'exams' ;
        $stable->thead = true;

        if($data_object->allobjectives == 'supported'){

            $stable->allobjectives = $data_object->allobjectives ;
            $stable->competencyid = $data_object->competencyid;
            $getobjtype='get_supportedcompetency_'.$stable->objtype.'_info';

        }elseif($data_object->allobjectives == 'all'){

            $stable->allobjectives = $data_object->allobjectives ;
            $stable->competencyid = $data_object->competencyid;
            $getobjtype='get_competency_'.$stable->objtype.'_info';

        }elseif($data_object->allobjectives == 'competencypc'){

            $filtervalues->competencypcid = $data_object->competencyid;

            $getobjtype='get_objectives_'.$stable->objtype.'_info';

        }else{
            $getobjtype='get_objectives_'.$stable->objtype.'_info';
        }

        $exams=competency::$getobjtype($stable,$filtervalues);
        $totalcount=$exams[''.$stable->objtype.'count'];
        $stable->thead = false;
        $stable->start = $offset;
        $stable->length = $limit;

        $data = array();

        if($totalcount>0){

            $setobjtype='list_objectives_'.$stable->objtype.'info';

            $renderer = $PAGE->get_renderer('local_competency');

            $data = array_merge($data,$renderer->$setobjtype($stable,$filtervalues));
        }
        return [
            'totalcount' => $totalcount,
            'records' =>$data,
            'options' => $options,
            'dataoptions' => $dataoptions,
            'filterdata' => $filterdata,
            'nodata' => get_string('nocompetency'.$stable->objtype.'','local_competency')
        ];
    }

    /**
     * Returns description of method result value.
     */
    public static function  get_objectivesinfo_returns() {
        return new external_single_structure([
            'options' => new external_value(PARAM_RAW, 'The paging data for the service'),
            'dataoptions' => new external_value(PARAM_RAW, 'The data for the service'),
            'totalcount' => new external_value(PARAM_INT, 'total number of competencies in result set'),
            'filterdata' => new external_value(PARAM_RAW, 'The data for the service'),
            'nodata' => new external_value(PARAM_RAW, 'The data for the service'),
            'records' => new external_multiple_structure(
                            new external_single_structure(
                                array(
                                    'competencypcid' => new external_value(PARAM_INT, ' competency pc id'),
                                    'id' => new external_value(PARAM_INT, ' id'),
                                    'name' => new external_value(PARAM_RAW, ' name'),
                                    'code' => new external_value(PARAM_RAW, ' code'),
                                    'delete' => new external_value(PARAM_RAW, 'competency pc actions',VALUE_OPTIONAL),
                                    'objectiveurl' => new external_value(PARAM_RAW, ' objective url'),
                                )
                            )
            )
        ]);
    }
    /**
     * Parameter description for delete_data_competencypc().
     *
     * @since Moodle 3.5
     * @return external_function_parameters
     */
    public static function delete_data_competencypc_parameters() {
        return new external_function_parameters([
            'competencypcid' => new external_value(PARAM_INT, 'The competency pc ID', VALUE_REQUIRED),
            'competencyid' => new external_value(PARAM_INT, 'The competency ID', VALUE_REQUIRED)
        ]);
    }

    /**
     * Delete a data competencypcid.
     *
     * @since Moodle 3.5
     * @param int $competencypcid The competencypcid ID.
     * @return array
     * @throws invalid_persistent_exception
     * @throws coding_exception
     * @throws invalid_parameter_exception
     * @throws restricted_context_exception
     */
    public static function delete_data_competencypc($competencypcid,$competencyid) {
        global $USER;

        $warnings = [];
        $params = external_api::validate_parameters(self::delete_data_competencypc_parameters(), [
            'competencypcid' => $competencypcid,
            'competencyid' => $competencyid
        ]);
        $competencypcid = $params['competencypcid'];
        $competencyid = $params['competencyid'];

        // Validate context and access to manage the registry.
        $context = context_system::instance();
        self::validate_context($context);

        $stable = new \stdClass();
        $stable->id = $competencypcid;
        $stable->competencyid = $competencyid;
        $stable->thead = false;
        $stable->start = 0;
        $stable->length = -1;
        $competencies=competency::get_competency_performances($stable);
        $competencyexists = count($competencies) === 1;

        $result = false;
        if ($competencyexists) {
           
            $result = competency::delete_competencypc($competencypcid,$competencyid);
            
        } else {
            $warnings[] = [
                'item' => $competencypcid,
                'warningcode' => 'errorcompetencynotfound',
                'message' => get_string('errorcompetencynotfound', 'local_competency')
            ];
        }

        return [
            'result' => $result,
            'warnings' => $warnings
        ];
    }

    /**
     * Parameter description for delete_data_competencypc().
     *
     * @since Moodle 3.5
     * @return external_description
     */
    public static function delete_data_competencypc_returns() {
        return new external_single_structure([
            'result' => new external_value(PARAM_BOOL, 'The processing result'),
            'warnings' => new external_warnings()
        ]);
    }
    /**
     * Parameter description for delete_competencypcobjectives().
     *
     * @since Moodle 3.5
     * @return external_function_parameters
     */
    public static function delete_competencypcobjectives_parameters() {
        return new external_function_parameters([
            'competencypcid' => new external_value(PARAM_INT, 'The competency pc ID', VALUE_REQUIRED),
            'competencypcobjectiveid' => new external_value(PARAM_INT, 'The competency pc objective ID', VALUE_REQUIRED),
            'competencypcobjectivetype' => new external_value(PARAM_TEXT, 'The competency pc objective type', VALUE_REQUIRED)
        ]);
    }
    /**
     * Delete a data competencypcid.
     *
     * @since Moodle 3.5
     * @param int $competencypcid The competencypcid ID.
     * @return array
     * @throws invalid_persistent_exception
     * @throws coding_exception
     * @throws invalid_parameter_exception
     * @throws restricted_context_exception
     */
    public static function delete_competencypcobjectives($competencypcid,$competencypcobjectiveid,$competencypcobjectivetype) {
        global $USER;

        $warnings = [];
        $params = external_api::validate_parameters(self::delete_competencypcobjectives_parameters(), [
            'competencypcid' => $competencypcid,
            'competencypcobjectiveid' => $competencypcobjectiveid,
            'competencypcobjectivetype' => $competencypcobjectivetype
        ]);
        $competencypcid = $params['competencypcid'];
        $competencypcobjectiveid = $params['competencypcobjectiveid'];
        $competencypcobjectivetype = $params['competencypcobjectivetype'];

        // Validate context and access to manage the registry.
        $context = context_system::instance();
        self::validate_context($context);

        $stable = new \stdClass();
        $stable->id = $competencypcid;
        $stable->competencypcobjectiveid = $competencypcobjectiveid;
        $stable->thead = false;
        $stable->start = 0;
        $stable->length = -1;
        $competencies=competency::get_competency_performances($stable);
        $competencyexists = count($competencies) === 1;

        $result = false;
        if ($competencyexists) {
           
            $result = competency::delete_competencypcobjective($competencypcid,$competencypcobjectiveid,$competencypcobjectivetype);
            
        } else {
            $warnings[] = [
                'item' => $competencypcobjectiveid,
                'warningcode' => 'errorcompetencynotfound',
                'message' => get_string('errorcompetencynotfound', 'local_competency')
            ];
        }

        return [
            'result' => $result,
            'warnings' => $warnings
        ];
    }

    /**
     * Parameter description for delete_competencypcobjectives().
     *
     * @since Moodle 3.5
     * @return external_description
     */
    public static function delete_competencypcobjectives_returns() {
        return new external_single_structure([
            'result' => new external_value(PARAM_BOOL, 'The processing result'),
            'warnings' => new external_warnings()
        ]);
    }
    /**
     * Returns description of method parameters.
     *
     * @return external_function_parameters.
     */
    public static function get_competencyquestions_parameters() {
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
    /**
     * Gets the list of competencies for the given criteria. The competencies
     * will be exported in a summaries format and won't include all of the
     * competencies data.
     *
     * @param int $userid Userid id to find competencies
     * @param int $contextid The context id where the competencies will be rendered
     * @param int $limit Maximum number of results to return
     * @param int $offset Number of items to skip from the beginning of the result set.
     * @return array The list of competencies and total competency count.
     */
    public static function get_competencyquestions(
        $options,
        $dataoptions,
        $offset = 0,
        $limit = 0,
        $filterdata
    ) {
        global $OUTPUT, $CFG, $DB,$USER,$PAGE;
        $sitecontext = context_system::instance();
        require_login();
        $PAGE->set_url('/local/competency/index.php', array());
        $PAGE->set_context($sitecontext);
        // Parameter validation.
        $params = self::validate_parameters(
            self::get_competencyquestions_parameters(),
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
        $stable->questionid = isset($data_object->questionid) ? $data_object->questionid : 0 ;
        $competencies=competency::get_competencies($stable,$filtervalues);
        $totalcount=$competencies['competenciescount'];
        $stable->thead = false;
        $stable->start = $offset;
        $stable->length = $limit;

        $data = array();

        if($totalcount>0){

            $renderer = $PAGE->get_renderer('local_competency');

            $data = array_merge($data,$renderer->list_competencies($stable,$filtervalues));
        }
        return [
            'totalcount' => $totalcount,
            'records' =>$data,
            'options' => $options,
            'dataoptions' => $dataoptions,
            'filterdata' => $filterdata,
            'nodata' => get_string('nocompetencys','local_competency'),
            'questionid' => $stable->questionid
        ];
    }

    /**
     * Returns description of method result value.
     */
    public static function  get_competencyquestions_returns() {
        return new external_single_structure([
            'options' => new external_value(PARAM_RAW, 'The paging data for the service'),
            'dataoptions' => new external_value(PARAM_RAW, 'The data for the service'),
            'totalcount' => new external_value(PARAM_INT, 'total number of competencies in result set'),
            'filterdata' => new external_value(PARAM_RAW, 'The data for the service'),
            'nodata' => new external_value(PARAM_RAW, 'The data for the service'),
            'questionid' => new external_value(PARAM_INT, 'questionid competencies in result set'),
            'records' => new external_multiple_structure(
                            new external_single_structure(
                                array(
                                    'competencyid' => new external_value(PARAM_INT, 'competency id'),
                                    'name' => new external_value(PARAM_RAW, 'competency name'),
                                    'code' => new external_value(PARAM_RAW, 'competency name')     
                                )
                            )
            )
        ]);
    }
    public static function viewallcompetencies_service_parameters() {
        return new external_function_parameters([
            'offset' => new external_value(PARAM_INT, 'Number of items to skip from the begging of the result set',
                VALUE_DEFAULT, 0),
            'limit' => new external_value(PARAM_INT, 'Maximum number of results to return',
                VALUE_DEFAULT, -1),
            'filterdata' => new external_value(PARAM_RAW, 'filters applied',array()),
            'contextid' => new external_value(PARAM_INT, 'Maximum number of results to return',
                VALUE_DEFAULT, 1)
        ]);
    }
    public static function viewallcompetencies_service($offset = 0, $limit = -1, $filterdata=array(),$contextid=1) {
        global $DB, $PAGE, $CFG;

        $sitecontext = context_system::instance();
        require_login();
        $PAGE->set_url('/local/competency/index.php', array());
        $PAGE->set_context($sitecontext);

        $params = self::validate_parameters(
            self::viewallcompetencies_service_parameters(),
            [
                'offset' => $offset,
                'limit' => $limit,
                'filterdata' => $filterdata,
                'contextid' => $contextid
            ]
        );
        $offset = $params['offset'];
        $limit = $params['limit'];
        $filtervalues = json_decode($filterdata);

        $stable = new \stdClass();
        $stable->thead = true;

        $competencies=competency::get_competencies($stable,$filtervalues);
        $totalcount=$competencies['competenciescount'];
        $stable->thead = false;
        $stable->start = $offset;
        $stable->length = $limit;

        $data = array();

        if($totalcount>0){

            $renderer = $PAGE->get_renderer('local_competency');

            $data = array_merge($data,$renderer->list_allcompetencies_service($stable,$filtervalues));
        }
        return [
            'competencieslist' =>$data,
        ];
    }

    /**
     * Returns description of method result value.
     */
    public static function  viewallcompetencies_service_returns() {
        return new external_single_structure([
            'competencieslist' => new external_multiple_structure(
                            new external_single_structure(
                                array(
                                    'competencyid' => new external_value(PARAM_INT, 'competency id'),
                                    'competencyname' => new external_value(PARAM_RAW, 'competency name'),
                                    'competencycode' => new external_value(PARAM_RAW, 'competency name'),
                                    'competencylevels' => new external_value(PARAM_RAW, 'competency levels'),
                                    'competencytype' => new external_value(PARAM_RAW, 'competency type'),
                                    'noperformancecriterias' => new external_value(PARAM_INT, 'competency no performance criterias'),
                                )
                            )
            )
        ]);
    }
    public static function detailedcompetencyview_service_parameters() {
        return new external_function_parameters([
            'id' => new external_value(PARAM_INT, 'competencyid',
                VALUE_DEFAULT, 0),
        ]);
    }
    public static function detailedcompetencyview_service($id) {
        global $DB, $PAGE, $CFG;
        $sitecontext = context_system::instance();
        require_login();
        $PAGE->set_url('/local/competency/index.php', array());
        $PAGE->set_context($sitecontext);
        $params = self::validate_parameters(
            self::detailedcompetencyview_service_parameters(),
            [
                'id' => $id,
            ]
        );
        $settings = external_settings::get_instance();
        $stable = new \stdClass();
        $stable->mlang =  $settings->get_lang();
        $data =(new competency)->detailed_competencyview($id, $stable);
        if($data) {
            return ['competencyData' =>$data];
        } else {
            return null;
        }
    }
    public static function detailedcompetencyview_service_returns() {
         return new external_single_structure([
            'competencyData'=> new external_single_structure([
                'name' => new external_value(PARAM_TEXT, 'competencyname'),
                'code' => new external_value(PARAM_TEXT, 'English Program Name'),
                'description' => new external_value(PARAM_RAW, 'Duration In Days'),
                'level' => new external_value(PARAM_TEXT, 'Duration In Hours'),
                'type' => new external_value(PARAM_TEXT, 'Duration In Hours'),
                'enrolledprograms' => new external_multiple_structure(
                    new external_single_structure(
                        array(
                        'id' => new external_value(PARAM_INT, 'id'),
                        'courseid' => new external_value(PARAM_INT, 'courseid'),
                        'name' => new external_value(PARAM_TEXT, 'name'),
                        'code' => new external_value(PARAM_TEXT, 'code'),
                        'description' => new external_value(PARAM_RAW, 'description'),
                        'starttime' => new external_value(PARAM_INT, 'starttime'),
                        'endtime' => new external_value(PARAM_INT, 'endtime'),
                        'langauge' => new external_value(PARAM_TEXT, 'seats'),
                        'sectorsList' => new external_multiple_structure(
                            new external_single_structure(
                                array(
                                'name' => new external_value(PARAM_TEXT, 'name'),
                                'description' => new external_value(PARAM_RAW, 'description'),
                                'value' => new external_value(PARAM_INT, 'ID'),
                                )
                            )
                        ), '', VALUE_OPTIONAL,
                      
                        )
                    )
                ), '', VALUE_OPTIONAL,
                'enrolledexams' => new external_multiple_structure(
                    new external_single_structure(
                        array(
                            'id' => new external_value(PARAM_INT, 'id'),
                            'courseid' => new external_value(PARAM_INT, 'courseid'),
                            'name' => new external_value(PARAM_TEXT, 'name'),
                            'code' => new external_value(PARAM_TEXT, 'code'),
                            'description' => new external_value(PARAM_RAW, 'description'),
                            'starttime' => new external_value(PARAM_INT, 'starttime'),
                            'endtime' => new external_value(PARAM_INT, 'endtime'),
                            'langauge' => new external_value(PARAM_TEXT, 'seats'),
                            'sectorsList' => new external_multiple_structure(
                                new external_single_structure(
                                    array(
                                    'name' => new external_value(PARAM_TEXT, 'name'),
                                    'description' => new external_value(PARAM_RAW, 'description'),
                                    'value' => new external_value(PARAM_INT, 'ID'),
                                    )
                                )
                            ), '', VALUE_OPTIONAL,
                        )
                    )
                ), '', VALUE_OPTIONAL,
                'levels' => new external_multiple_structure(
                    new external_single_structure(
                        array(
                            'levelname' => new external_value(PARAM_TEXT, 'levelname'),
                            'leveldescription' => new external_value(PARAM_RAW, 'levelname'),
                            'mappedprograms' => new external_multiple_structure(
                                new external_single_structure(
                                    array(
                                        'id' => new external_value(PARAM_INT, 'id'),
                                        'courseid' => new external_value(PARAM_INT, 'courseid'),
                                        'name' => new external_value(PARAM_TEXT, 'name'),
                                        'code' => new external_value(PARAM_TEXT, 'code'),
                                        'isenrolled' => new external_value(PARAM_RAW, 'isenrolled'),
                                        'description' => new external_value(PARAM_RAW, 'description'),
                                        'starttime' => new external_value(PARAM_INT, 'starttime'),
                                        'endtime' => new external_value(PARAM_INT, 'endtime'),
                                        'langauge' => new external_value(PARAM_TEXT, 'seats'),
                                        'sectorsList' => new external_multiple_structure(
                                            new external_single_structure(
                                                array(
                                                'name' => new external_value(PARAM_TEXT, 'name'),
                                                'description' => new external_value(PARAM_RAW, 'description'),
                                                'value' => new external_value(PARAM_INT, 'ID'),
                                                )
                                            )
                                        ), '', VALUE_OPTIONAL
                                    )
                                )
                            ), '', VALUE_OPTIONAL,
                            'mappedexams' => new external_multiple_structure(
                                new external_single_structure(
                                    array(
                                        'id' => new external_value(PARAM_INT, 'id'),
                                        'courseid' => new external_value(PARAM_INT, 'courseid'),
                                        'name' => new external_value(PARAM_TEXT, 'name'),
                                        'code' => new external_value(PARAM_TEXT, 'code'),
                                        'isenrolled' => new external_value(PARAM_RAW, 'isenrolled'),
                                        'description' => new external_value(PARAM_RAW, 'description'),
                                        'starttime' => new external_value(PARAM_INT, 'starttime'),
                                        'endtime' => new external_value(PARAM_INT, 'endtime'),
                                        'langauge' => new external_value(PARAM_TEXT, 'seats'),
                                        'sectorsList' => new external_multiple_structure(
                                            new external_single_structure(
                                                array(
                                                'name' => new external_value(PARAM_TEXT, 'name'),
                                                'description' => new external_value(PARAM_RAW, 'description'),
                                                'value' => new external_value(PARAM_INT, 'ID'),
                                                )
                                            )
                                        ), '', VALUE_OPTIONAL
                                    )
                                )
                            ), '', VALUE_OPTIONAL,        
                        )
                    )
                ), '', VALUE_OPTIONAL,
            ])

        ]);
    }

    public static function getcompetency_jobroleid_parameters(){
        return new external_function_parameters([
            'isArabic' => new external_value(PARAM_RAW, 'Language in use',VALUE_DEFAULT,false),
            'pageNumber' => new external_value(PARAM_INT, 'Page Number',VALUE_DEFAULT,1),
            'pageSize' => new external_value(PARAM_INT, 'Page Size',VALUE_DEFAULT,5),
            'JobRoleId' => new external_value(PARAM_INT, 'JobRoleId'),
     
        ]);
    }
        
    public static  function getcompetency_jobroleid($isArabic,$pageNumber,$pageSize,$JobRoleId){
        global $DB;
        $context = context_system::instance();
              $params = self::validate_parameters(
                self::getcompetency_jobroleid_parameters(),
                [  
                    'isArabic'=> $isArabic,
                    'pageNumber'=> $pageNumber,
                    'pageSize'=>$pageSize,
                    'JobRoleId'=> $JobRoleId,
                ]
            );      
            $stable = new \stdClass();
            $stable->thead = false;
            $stable->start = ($pageNumber > 1) ? (($pageNumber-1)* $pageSize) : 0;
            $stable->length = $pageSize;
            $stable->isArabic = $isArabic;
            $stable->JobRoleId = $JobRoleId;
            $jobroles = competency::get_jobrole($JobRoleId,$isArabic);   
            $data = competency::get_competencies_jobroleid($stable);       
        if( $jobroles && $data)  { 
            $event = \local_competency\event\success_response::create(
                array(
                'context'=>$context,
                'objectid' => 1,
                'other'=>array(
                    'Function'=>'local_competency_getcompetency_jobroleid',
                    'Success'=>'Successfully Fetched the Response'
                )
                )
            );
            $event->trigger();    
        return [ 'mainData'=> $jobroles['jobroles'],'pageData' =>$data['competencies'],'totalItemCount' =>$data['totalcompetencies'],'pageSize'=>$pageSize ,'pageNumber'=> $pageNumber];
        } else {
            $event = \local_competency\event\error_response::create(
                array( 
                    'context'=>$context,
                    'objectid' =>0,
                    'other'=>array(
                        'Function'=>'local_competency_getcompetency_jobroleid',
                        'Error'=>'Invalid Response Value Detected'

                    )
                    )
                );
            $event->trigger();

        }
}

public static function getcompetency_jobroleid_returns() {

        return new external_single_structure([  
            'mainData' => new external_multiple_structure(
                new external_single_structure(
                    array(
                        'responsibilities' => new external_value(PARAM_RAW, 'responsibilities'),
                        'code' => new external_value(PARAM_RAW, 'Code'),  
                        'name' => new external_value(PARAM_RAW, 'Name'),
                        'description' => new external_value(PARAM_RAW, 'Description'), 
                        'value' => new external_value(PARAM_INT, 'Value'),                          
                        'parentvalue' => new external_value(PARAM_INT, 'Parent Value'),                        
                        )
                )
            ),     
            'pageData' => new external_multiple_structure(
                new external_single_structure(
                    array(
                        'typeId' => new external_value(PARAM_RAW, 'Code',NULL),  
                        'typeName' => new external_value(PARAM_RAW, 'Name'), 
                        'code' => new external_value(PARAM_RAW, 'Code'),  
                        'name' => new external_value(PARAM_RAW, 'Name'), 
                        'description' => new external_value(PARAM_RAW, 'Description'),
                        'value' => new external_value(PARAM_INT, 'Value'),
                        'parentValue' => new external_value(PARAM_INT, 'Parent Value'),                         
                        )
                )
            ),
            'totalItemCount'=>new external_value(PARAM_INT, 'total count'),
            'pageSize' => new external_value(PARAM_INT, 'Page Size'),
            'pageNumber' => new external_value(PARAM_INT, 'Page Number'),
        ]);
    }
    public static function getcompetencylevel_cid_parameters(){

                return new external_function_parameters([
                     
                    'isArabic'=>new external_value(PARAM_RAW, 'Is arabic or not'),
                    'pageSize' => new external_value(PARAM_INT, 'Page Size',
                        VALUE_DEFAULT, 5),
                    'pageNumber' => new external_value(PARAM_INT, 'Page Number',
                        VALUE_DEFAULT, 1),
                    'competencyid' => new external_value(PARAM_INT, 'JobRole Id')
             
                ]);
    }
        
    public static  function getcompetencylevel_cid($isArabic,$pageSize,$pageNumber,$competencyid){
              global $DB;
              require_login();
	       $context = context_system::instance();
              $params = self::validate_parameters(
                self::getcompetencylevel_cid_parameters(),
                [  
                    'isArabic' => $isArabic,
                    'pageSize'=>$pageSize,
                    'pageNumber' => $pageNumber,
                    'competencyid'  => $competencyid
                  
                ]
            );      
                $offset = $params['offset'];
                $limit =$pagesize;      
                $stable = new \stdClass();
                $stable->thead = false;
                $stable->start = ($pageNumber > 1) ? (($pageNumber-1)* $pageSize) : 0;
                $stable->length = $limit;
                $stable->isArabic = $isArabic;
                $stable->competencyid = $competencyid;
                $cdata = competency::get_competenciesinfo($stable);   
                $data = competency::get_competencylevel($stable);       
        if ($cdata &&  $data)  {
            $event = \local_competency\event\success_response::create(
                array(
                'context'=>$context,
                'objectid' => 1,
                'other'=>array(
                    'Function'=>'local_competency_getcompetencylevel_cid',
                    'Success'=>'Successfully Fetched the Response'
                )
                )
            );
            $event->trigger();      
        return ['mainData'=> $cdata['typeinfo'],'pageData' =>$data['levels'],'totalItemCount' =>$data['totallevels'],'pageSize'=>$pageSize ,'pageNumber'=> $pageNumber];
        }else {
            $event = \local_competency\event\error_response::create(
                array( 
                    'context'=>$context,
                    'objectid' =>0,
                    'other'=>array(
                        'Function'=>'local_competency_getcompetencylevel_cid',
                        'Error'=>'Invalid Response Value Detected'

                    )
                    )
                );  
            $event->trigger();

            
        }
    }

    public static function getcompetencylevel_cid_returns() {

        return new external_single_structure([ 
            'mainData' => new external_multiple_structure(
                new external_single_structure(
                    array(
                        'typeId' => new external_value(PARAM_INT, 'Value',NULL), 
                        'typeName' => new external_value(PARAM_TEXT, 'Value'), 
                        'code' => new external_value(PARAM_TEXT, 'Code'),  
                        'name' => new external_value(PARAM_TEXT, 'Name'),
                        'description' => new external_value(PARAM_RAW, 'Description'), 
                        'value' => new external_value(PARAM_INT, 'Value'),                          
                        'parentValue' => new external_value(PARAM_RAW, 'Parent Value'),                        
                        )
                )
            ),           
            'pageData' => new external_multiple_structure(
                new external_single_structure(
                    array(
                        'levelId' => new external_value(PARAM_INT, 'levelId'),  
                        'code' => new external_value(PARAM_TEXT, 'Code'),  
                        'name' => new external_value(PARAM_TEXT, 'Name'), 
                        'description' => new external_value(PARAM_RAW, 'Description'),
                        'value' => new external_value(PARAM_INT, 'Value'),                          
                        'parentValue' => new external_value(PARAM_INT, 'Parent Value'),                                           
                    )
                )
            ),
            'totalItemCount'=>new external_value(PARAM_INT, 'total count'),
            'pageSize' => new external_value(PARAM_INT, 'Page Size'),
            'pageNumber' => new external_value(PARAM_INT, 'Page Number'),
        ]);
    }
public static function getcompetencytypes_parameters(){

                return new external_function_parameters([
                    'isArabic'=>new external_value(PARAM_RAW, 'Is arabic or not'),
                ]);
}
        
public static  function getcompetencytypes($isArabic){
              global $DB;
              require_login();
      $context = context_system::instance();
              $params = self::validate_parameters(
                self::getcompetencytypes_parameters(),
                [
                    'isArabic' => $isArabic,
                  
                ]
            );      
                $stable = new \stdClass();
                $stable->isArabic = $isArabic;
                $data = competency::get_allcompetencytypes($stable);
        if($data){
            $event = \local_competency\event\success_response::create(
                array(
                'context'=>$context,
                'objectid' => 1,
                'other'=>array(
                    'Function'=>'local_competency_getcompetencytypes',
                    'Success'=>'Successfully Fetched the Response'
                )
                )
            );
            $event->trigger(); 
            return $data;
            
        }else{
            $event = \local_competency\event\error_response::create(
                array( 
                    'context'=>$context,
                    'objectid' =>0,
                    'other'=>array(
                        'Function'=>'local_competency_getcompetencytypes',
                        'Error'=>'Invalid Response Value Detected'

                    )
                    )
                );  
            $event->trigger();

        }
      

}

public static function getcompetencytypes_returns() {
    return new external_multiple_structure(
        new external_single_structure(
            array(
                'value' => new external_value(PARAM_INT, 'Value'),
                'type' => new external_value(PARAM_TEXT, 'type'),                      
                'name' => new external_value(PARAM_RAW, 'name'),                      
            )
        )
    );
}

    public static function competency_search_parameters() {
        return new external_function_parameters([
            'isArabic' => new external_value(PARAM_RAW, 'isArabic', VALUE_DEFAULT, false),
            'Keyword' => new external_value(PARAM_RAW, 'Keyword', VALUE_DEFAULT, NULL),
            'SectorId' => new external_value(PARAM_RAW, 'SectorId', VALUE_DEFAULT, 0),
            'JobRoleId' => new external_value(PARAM_RAW, 'JobRoleId', VALUE_DEFAULT, 0),
            'JobFamilyId' => new external_value(PARAM_RAW, 'JobFamilyId', VALUE_DEFAULT, 0),
            'CompetencyId' => new external_value(PARAM_RAW, 'CompetencyId', VALUE_DEFAULT, 0),
        ]);
    }

    public static function competency_search($isArabic, $Keyword, $SectorId, $JobRoleId, $JobFamilyId, $CompetencyId) {
        global $DB, $PAGE;
        // Parameter validation.
        $context = context_system::instance();
        $params = self::validate_parameters(
            self::competency_search_parameters(),
            [
                'isArabic' => $isArabic,
                'Keyword' => $Keyword,
                'SectorId' => $SectorId,
                'JobRoleId' => $JobRoleId,    
                'JobFamilyId' => $JobFamilyId,
                'CompetencyId' => $CompetencyId
            ]
        );

        $stable = new \stdClass();
        $stable->isarabic = $isArabic;
        $stable->keyword = $Keyword;
        $stable->sectorids = $SectorId;
        $stable->jobroleid = $JobRoleId;
        $stable->jobfamilyids = $JobFamilyId;
        $stable->competencyid = $CompetencyId;
        $data = competency::competency_search($stable);

        if($data){
            $event = \local_competency\event\success_response::create(
                array(
                'context'=>$context,
                'objectid' => 1,
                'other'=>array(
                    'Function'=>'local_competency_competencysearch',
                    'Success'=>'Successfully Fetched the Response'
                )
                )
            );
            $event->trigger(); 
            return $data;
        } else{
            $event = \local_competency\event\error_response::create(
                array( 
                    'context'=>$context,
                    'objectid' =>0,
                    'other'=>array(
                        'Function'=>'local_competency_competencysearch',
                        'Error'=>'Invalid Response Value Detected'

                    )
                    )
                );  
            $event->trigger();
        }
        return $data;
    }

    public static function competency_search_returns() {
        return new external_multiple_structure(
            new external_single_structure(
                array(
                    'id' => new external_value(PARAM_INT, 'id'),
                    'name' => new external_value(PARAM_RAW, 'name'),
                    'description' => new external_value(PARAM_RAW, 'description'),
                    'typeCode' => new external_value(PARAM_RAW, 'typeCode'),
                    'typeName' => new external_value(PARAM_RAW, 'typeName'),
                    'navigators' => new external_multiple_structure(
                        new external_single_structure(
                            array(
                            'id' => new external_value(PARAM_INT, 'id', VALUE_OPTIONAL),
                            'name' => new external_value(PARAM_RAW, 'name', VALUE_OPTIONAL),
                            'typeCode' => new external_value(PARAM_RAW, 'typeCode', VALUE_OPTIONAL),
                            'typeName' => new external_value(PARAM_RAW, 'typeName', VALUE_OPTIONAL),
                            ),'', VALUE_OPTIONAL
                        )
                    ,'', VALUE_OPTIONAL),
                )
            )
        );
    }


 public static function getcompetencybytypeid_parameters(){

    return new external_function_parameters([
         
        'TypeID'=>new external_value(PARAM_INT, 'competency type id'),
        'isArabic' => new external_value(PARAM_RAW, 'Language in use',VALUE_DEFAULT,false),
        'pageNumber' => new external_value(PARAM_INT, 'Page Number',VALUE_DEFAULT,1),
        'pageSize' => new external_value(PARAM_INT, 'Page Size',VALUE_DEFAULT,5),
        'query' => new external_value(PARAM_RAW, 'query',VALUE_DEFAULT,null),
 
    ]);
}
        
public static  function getcompetencybytypeid($TypeID,$isArabic,$pageNumber,$pageSize,$query){
    global $DB;
   require_login();
       $context = context_system::instance();
          $params = self::validate_parameters(
            self::getcompetencybytypeid_parameters(),
            [  
                'TypeID' => $TypeID,
                'isArabic' => $isArabic,
                'pageNumber'=>$pageNumber,
                'pageSize' => $pageSize,
                'query'  => $query
            ]
        );      
        $stable = new \stdClass();
        $stable->thead = false;
        $stable->TypeID = $TypeID;
        $stable->start = ($pageNumber > 1) ? (($pageNumber-1)* $pageSize) : 0;
        $stable->length = $pageSize;
        $stable->isArabic = $isArabic;
        $stable->query = $query;

        $mainData = competency::get_competencybytypeinfobyid($stable);  
        $data = competency::get_competencydatabytypeid($stable,$mainData['typename']); 

    return[
        'mainData'=> $mainData,
        'pageData' =>($mainData['typename']) ? $data['competencies'] : [],
        'totalItemCount' =>$data['totalcompetencies'],
        'pageSize'=>$pageSize,
        'pageNumber'=> $pageNumber
    ];
}


public static function getcompetencybytypeid_returns() {

    return new external_single_structure([ 
        'mainData' => new external_single_structure(
            array(
            'value' => new external_value(PARAM_INT, 'Value'), 
            'name' => new external_value(PARAM_TEXT, 'Value'), 
          )
        ),           
        'pageData' => new external_multiple_structure(
            new external_single_structure(
                array(
                'typeID' => new external_value(PARAM_INT, 'Code'),  
                'typeName' => new external_value(PARAM_TEXT, 'Code'),  
                'code' => new external_value(PARAM_TEXT, 'Code'),  
                'name' => new external_value(PARAM_TEXT, 'Name'), 
                'description' => new external_value(PARAM_RAW, 'Description'),
                'value' => new external_value(PARAM_INT, 'value'),                        
                'parentValue' => new external_value(PARAM_TEXT, 'parentValue'),                        
                )
            )
        ), '', VALUE_OPTIONAL,
        'totalItemCount'=>new external_value(PARAM_INT, 'total count'),
        'pageSize' => new external_value(PARAM_INT, 'Page Size'),
        'pageNumber' => new external_value(PARAM_INT, 'Page Number'),
    ]);
}
}
