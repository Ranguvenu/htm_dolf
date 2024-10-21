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
class traineewallet_observer extends product_observer
{
    
    public static function create(\tool_product\event\trainee_wallet $event) {
        parent::traineewalletcreate($event);
    }

    public static function update(\tool_product\event\trainee_wallet $event) {
        parent::traineewalletupdate($event->objectid);
    }

    public static function delete(\tool_product\event\trainee_wallet $event) {
        parent::traineewalletdelete($event->objectid);
    }
}