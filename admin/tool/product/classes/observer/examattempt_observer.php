<?php
/**
 * @package    tool_product
 * @copyright  2022 Naveen kumar <naveen@eabyas.com>
 */
namespace tool_product\observer;

use tool_product\product as product;

/**
 * Exams observer 
 */
class examattempt_observer extends product_observer
{
    
    public static function create(\local_exams\event\exam_attempt $attempt) {
        parent::create($attempt->objectid);
    }

    public static function update(\local_exams\event\exam_attemptupdated $attempt) {
        parent::update($attempt->objectid);
    }

    public static function delete(\local_events\event\events_deleted $event) {
        parent::delete($event->objectid);
    }

    public static function get_details($attempt){
        global $DB;
        $sql = "SELECT lep.id, le.exam as name, le.code as code, lep.fee as price, 
                       le.programdescription as description
                  FROM {local_exam_attempts} lep
                  JOIN {local_exams} le ON le.id = lep.examid
                  WHERE lep.id =:id";

        $attempt = $DB->get_record_sql($sql, ['id' => $attempt]);

        $productdetails = (array)$attempt;
        $productdetails['status'] = 1;
        $productdetails['category'] = product::EXAMATTEMPT;
        $productdetails['referenceid'] = $attempt->id;
        $productdetails['stock'] = 1;

        return $productdetails;
    }
}