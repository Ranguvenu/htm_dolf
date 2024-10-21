<?php
/**
 * 
 *
 * @package    tool_product
 * @copyright  2022 Naveen kumar <naveen@eabyas.com>
 */
namespace tool_product\observer;

use tool_product\product as product;
/**
 * Training program observer 
 */
class tp_observer extends product_observer
{
    
    public static function create(\local_trainingprogram\event\tpofferings_created $event) {
        parent::create($event->objectid);
    }


    public static function update(\local_trainingprogram\event\tpofferings_updated $event) {
        parent::update($event->objectid);
    }

    public static function delete(\local_trainingprogram\event\tpofferings_deleted $event) {
        parent::delete($event->objectid);
    }

    public static function get_details($offeringid){
        global $DB;
        $sql = "SELECT tof.id,tp.name,tp.code,tof.sellingprice as price,tof.availableseats as units, 
                       tp.description,tp.published as status
                  FROM {local_trainingprogram} as tp 
                  JOIN {tp_offerings} as tof ON tp.id=tof.trainingid
                 WHERE tof.id =:id";

        $tprecord = $DB->get_record_sql($sql, ['id' => $offeringid]);
        $productdetails = (array)$tprecord;

        $productdetails['category'] = product::TRAINING_PROGRAM;
        $productdetails['referenceid'] = $tprecord->id;
        $productdetails['stock'] = $tprecord->units;
        return $productdetails;
    }
}