<?php
/*
* This file is a part of e abyas Info Solutions.
*
* Copyright e abyas Info Solutions Pvt Ltd, India.
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
* @author e abyas  <info@eabyas.com>
*/
/**
 * Plugin version and other meta-data are defined here.
 *
 * @package    block_supported_competencies
 * @copyright  e abyas  <info@eabyas.com>
 */

defined('MOODLE_INTERNAL') || die();
use block_supported_competencies\local\supported_competencies as supportedcompetencies;
use core_user;

require_once('../../config.php');
require_once($CFG->libdir . '/externallib.php');
require_once($CFG->dirroot.'/blocks/supported_competencies/lib.php'); 


class block_supported_competencies_external extends external_api {

    /**
     * Returns description of method parameters.
     *
     * @return external_function_parameters.
     */
    public static function get_mysupportedcompetencies_parameters() {
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
    public static function get_mysupportedcompetencies(
        $options,
        $dataoptions,
        $offset = 0,
        $limit = 0,
        $filterdata
    ) {
        global $OUTPUT, $CFG, $DB,$USER,$PAGE;
        $sitecontext = context_system::instance();
        require_login();
        $PAGE->set_url('/local/competency/mycompetency.php', array());
        $PAGE->set_context($sitecontext);
        // Parameter validation.
        $params = self::validate_parameters(
            self::get_mysupportedcompetencies_parameters(),
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
        $stable->supportedcompetencies =true;
        $stable->thead = true;

        $competencies=supportedcompetencies::get_mysupportedcompetencies($stable,$filtervalues);
        $totalcount=$competencies['competenciescount'];
        $stable->thead = false;
        $stable->start = $offset;
        $stable->length = $limit;

        $data = array();

        if($totalcount>0){

            $renderer = $PAGE->get_renderer('block_supported_competencies');

            $data = array_merge($data,$renderer->lis_mysupportedcompetencies_bytype($stable,$filtervalues));
        }
        return [
            'totalcount' => $totalcount,
            'records' =>$data,
            'options' => $options,
            'dataoptions' => $dataoptions,
            'filterdata' => $filterdata,
            'nodata' => get_string('nocompetencys','block_supported_competencies')
        ];
    }

    /**
     * Returns description of method result value.
     */
    public static function  get_mysupportedcompetencies_returns() {
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
                                    'name' => new external_value(PARAM_RAW, 'competency name'),
                                    'code' => new external_value(PARAM_RAW, 'competency name'),
                                    'level' => new external_value(PARAM_RAW, 'competency level'),
                                    'type' => new external_value(PARAM_RAW, 'competency type')
                                )
                            )
            )
        ]);
    }
    
}
