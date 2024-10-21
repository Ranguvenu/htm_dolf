<?php
/**
 * @package    tool_product
 * @copyright  2022 Naveen kumar <naveen@eabyas.com>
 */
namespace tool_product\observer;

use dml_exception;
use tool_product\product;
use stdClass;
use context_system;

/**
 * Training program observer 
 */
class product_observer
{
    public static function create($objectid){
        global $DB, $USER;
         
        if(method_exists(get_called_class(), 'get_details')){
           $currenclass = get_called_class();
           $productdetails =  call_user_func($currenclass.'::get_details',$objectid);
        }else{
            return false;
        }
        $productdetails['timecreated'] = time();
        $productdetails['timemodified'] = time();
        $productdetails['usercreated'] = $USER->id;
        try{
            $DB->insert_record('tool_products', $productdetails);
        }
        catch(dml_exception $e){
            print_r($e->debuginfo);
        }
    }

    public static function update($objectid){
        global $DB, $USER;
         if(method_exists(get_called_class(), 'get_details')){
            $currenclass = get_called_class();
           $productdetails =  call_user_func($currenclass.'::get_details',$objectid);
        }else{
            return false;
        }
        $productdetails['id'] = $DB->get_field('tool_products', 'id', ['referenceid' => $productdetails['id'], 'category' => $productdetails['category']]);
        $productdetails['timemodified'] = time();
        $productdetails['usermodified'] = $USER->id;
        
        try{
            $DB->update_record('tool_products', $productdetails);
        }
        catch(dml_exception $e){
            print_r($e->debuginfo);
        }
    }
    public static function delete($objectid){
        global $DB, $USER;
         
        if(method_exists(get_called_class(), 'get_details')){
           $currenclass = get_called_class();
           $productdetails =  call_user_func($currenclass.'::get_details',$objectid);
           if(empty($productdetails) || !$DB->record_exists('tool_products', ['referenceid' => $productdetails['id'], 'category' => $productdetails['category']])){
             return false;
           }

        }else{
            return false;
        }

        $productdetails['id'] = $DB->get_field('tool_products', 'id', ['referenceid' => $productdetails['id'], 'category' => $productdetails['category']]);
        $productdetails['timemodified'] = time();
        $productdetails['usermodified'] = $USER->id;
        $productdetails['deleted'] = 1;
        try{
            $DB->update_record('tool_products', $productdetails);
        }
        catch(dml_exception $e){
            print_r($e->debuginfo);
        }
    }

    public static function traineewalletcreate($event)
    {
        global $DB, $USER;
        $systemcontext = context_system::instance();
        $walletdetails = $DB->get_record('trainee_wallet', ['id' => $event->objectid]);
        $data = new stdClass();
        $data->walletid = $walletdetails->id;
        $data->userid = $walletdetails->userid;
        $data->usercreated = $USER->id;
        if (!is_siteadmin() && has_capability('local/organization:manage_financial_manager',$systemcontext)) {
            $data->roleid = $DB->get_field('role', 'id', ['shortname'=> 'financial_manager']);
        } elseif(!is_siteadmin() && has_capability('local/organization:manage_trainee',$systemcontext)) {
            $data->roleid = $DB->get_field('role', 'id', ['shortname'=> 'trainee']);
        }
        $data->paymentstatus = $event->other['paymentstatus'];
        $data->entitytype = !empty($event->other['entitytype']) ? $event->other['entitytype'] : NULL;
        $data->entityid = !empty($event->other['entityid']) ? $event->other['entityid'] : 0;
        $data->timecreated = time();
        $id = $DB->insert_record('trainee_walletlog', $data);

        return true;
    }
}
