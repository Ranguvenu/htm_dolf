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

namespace local_competency\external;

use local_competency\competency;

/**
 * Provides the local_competency_search_identity external function.
 * @package    local_competency
 * @category    external
 * @copyright  2022 e abyas  <info@eabyas.com>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class search_identity extends \external_api {

    /**
     * Describes the external function parameters.
     *
     * @return external_function_parameters
     */
    public static function execute_parameters(): \external_function_parameters {

        return new \external_function_parameters([
            'query' => new \external_value(PARAM_TEXT, 'The search query', VALUE_REQUIRED),
            'action' => new \external_value(PARAM_TEXT, 'The search action', VALUE_REQUIRED),
            'parentid' => new \external_value(PARAM_INT, 'The search parent id', VALUE_OPTIONAL),
            'parentchildid' => new \external_value(PARAM_INT, 'The search parent child id', VALUE_OPTIONAL),
        ]);
    }

    /**
     * Finds competencys with the identity matching the given query.
     *
     * @param string $query The search request.
     * @return array
     */
    public static function execute(string $query,string $action,$parentid=0,$parentchildid=0): array {
        global $DB, $CFG;

        $params = \external_api::validate_parameters(self::execute_parameters(), [
            'query' => $query,
            'action' => $action,
            'parentid'=>$parentid,
            'parentchildid'=>$parentchildid
        ]);
        $query = $params['query'];
        $action = $params['action'];
        $parentid = $params['parentid'];
        $parentchildid = $params['parentchildid'];

        // Validate context.
        $context = \context_system::instance();
        self::validate_context($context);

        $rs =competency::$action($query, 0,11,$parentid,$parentchildid);

        $count = 0;
        $list = [];

        foreach ($rs as $key=>$record) {
            $competency = (object)[
                'id' => $key,
                'fullname' => $record,
                'extrafields' => [],
            ];

            $count++;

            if ($count <= 10) {
                $list[$key] = $competency;
            }
        }

        return [
            'list' => $list,
            'maxcompetencysperpage' => 10,
            'overflow' => ($count > 10),
        ];
    }

    /**
     * Describes the external function result value.
     *
     * @return external_description
     */
    public static function execute_returns(): \external_description {

        return new \external_single_structure([
            'list' => new \external_multiple_structure(
                new \external_single_structure([
                    'id' => new \external_value(PARAM_RAW, 'The id of the competency'),
                    // The output of the {@see fullname()} can contain formatting HTML such as <ruby> tags.
                    // So we need PARAM_RAW here and the caller is supposed to render it appropriately.
                    'fullname' => new \external_value(PARAM_RAW, 'The fullname of the competency'),
                    'extrafields' => new \external_multiple_structure(
                        new \external_single_structure([
                            'name' => new \external_value(PARAM_TEXT, 'Name of the extrafield.'),
                            'value' => new \external_value(PARAM_TEXT, 'Value of the extrafield.'),
                        ]), 'List of extra fields', VALUE_OPTIONAL)
                ])
            ),
            'maxcompetencysperpage' => new \external_value(PARAM_INT, 'Configured maximum competencys per page.'),
            'overflow' => new \external_value(PARAM_BOOL, 'Were there more records than maxcompetencysperpage found?'),
        ]);
    }
}
