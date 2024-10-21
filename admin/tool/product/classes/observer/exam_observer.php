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
class exam_observer extends product_observer
{
    
    public static function create(\local_hall\event\hall_reserved $event) {
        parent::create($event->objectid);
    }

    public static function update(\local_hall\event\reservation_update $event) {
        parent::update($event->objectid);
    }

    public static function delete(\local_exams\event\exam_deleted $event) {
        parent::delete($event->objectid);
    }

    public static function get_details($reservationid){
        global $DB;
        $sql = "SELECT lep.id, le.exam as name, lep.profilecode as code, le.sellingprice as price, lep.seatingcapacity as units, 
                       le.programdescription as description
                  FROM {local_exam_profiles} lep
                  JOIN {local_exams} le ON le.id = lep.examid
                  WHERE lep.id =:id";
        $examrecord = $DB->get_record_sql($sql, ['id' => $reservationid]);

        $productdetails = (array)$examrecord;
        $productdetails['status'] = 1;
        $productdetails['category'] = product::EXAMS;
        $productdetails['referenceid'] = $examrecord->id;
        $productdetails['stock'] = $examrecord->units;

        return $productdetails;
    }
}
