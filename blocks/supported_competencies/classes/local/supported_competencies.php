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
 * Defines  leveluptarget logical functions
 *
 * @package    block_supported_competencies
 * @copyright  e abyas  <info@eabyas.com>
 */
namespace block_supported_competencies\local;
defined('MOODLE_INTERNAL') || die;

require_once($CFG->dirroot.'/blocks/supported_competencies/lib.php');

use stdClass;
use moodle_exception;
use context_system;
use context_user;
use local_competency\competency as competency;
/**
 * supported_competencies defination
 */
class supported_competencies extends competency{

    public static function get_mysupportedcompetencies($stable,$filterdata=null) {
        global $DB, $USER;

        $params          = array();
        $competencies      = array();
        $competenciescount = 0;
        $concatsql       = '';

    

        $concatsql .= " AND (EXISTS (SELECT trgprgm.id FROM {local_trainingprogram} AS trgprgm
                                    JOIN {program_enrollments} AS penrl ON penrl.programid=trgprgm.id WHERE FIND_IN_SET(cmt.id,trgprgm.competencyandlevels) > 0 AND penrl.userid=:penrluserid) > 0 OR EXISTS (SELECT exm.id FROM {local_exams} AS exm
                                    JOIN {exam_enrollments} AS exmnrl ON exmnrl.examid=exm.id WHERE FIND_IN_SET(cmt.id,exm.competencies) > 0 AND exmnrl.userid=:exmnrluserid) > 0)";

         $params['penrluserid'] = $USER->id;  
         $params['exmnrluserid'] = $USER->id;    

        $currentlang= current_language();

        if($currentlang == 'ar'){

          $titlefield='cmt.arabicname';

        }else{

           $titlefield='cmt.name';
        }
                      


        if (!empty($filterdata->search_query)) {
            $fields = array(
                $titlefield,"cmt.code"
            );
            $fields = implode(" LIKE :search1 OR ", $fields);
            $fields .= " LIKE :search2 ";
            $params['search1'] = '%' . $filterdata->search_query . '%';
            $params['search2'] = '%' . $filterdata->search_query . '%';
            $concatsql .= " AND ($fields) ";
        }

        if (isset($stable->competencyid) && $stable->competencyid > 0) {
            $concatsql .= " AND cmt.id = :competencyid";
            $params['competencyid'] = $stable->competencyid;
        }

        $countsql = "SELECT COUNT(cmt.id) ";
        $fromsql = "SELECT cmt.* ,$titlefield as name";
        $sql = " FROM {local_competencies} AS cmt
                WHERE cmt.id > 0 ";


        $sql .= $concatsql;


        if (isset($stable->competencyid) && $stable->competencyid > 0) {
            $competencies = $DB->get_record_sql($fromsql . $sql, $params);
        } else {
            try {

                $competenciescount = $DB->count_records_sql($countsql . $sql, $params);
                if ($stable->thead == false) {
                    $sql .= " ORDER BY cmt.id DESC";

                    $competencies = $DB->get_records_sql($fromsql . $sql, $params, $stable->start, $stable->length);
                }
            } catch (dml_exception $ex) {
                $competenciescount = 0;
            }
        }
        if (isset($stable->competencyid) && $stable->competencyid > 0) {
            return $competencies;
        } else {
            return compact('competencies', 'competenciescount');
        }
    }
}
