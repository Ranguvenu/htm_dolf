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
class event_observer extends product_observer
{
    
    public static function create(\local_events\event\events_created $event) {
        parent::create($event->objectid);
    }

    public static function update(\local_events\event\events_updated $event) {
        parent::update($event->objectid);
    }

    public static function delete(\local_events\event\events_deleted $event) {
        parent::delete($event->objectid);
    }

    public static function get_details($eventid){
        global $DB;
        $sql = "SELECT id,title as name,code,sellingprice as price,description, status
                  FROM {local_events} WHERE id =:id";

        $eventrecord = $DB->get_record_sql($sql, ['id' => $eventid]);
        $productdetails = (array)$eventrecord;

        $productdetails['category'] = product::EVENTS;
        $productdetails['referenceid'] = $eventrecord->id;
        return $productdetails;
    }
}