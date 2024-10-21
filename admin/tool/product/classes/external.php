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
 * This is the external API for this component.
 *
 * @package    tool_product
 * @copyright  2022 Naveen Kumar <naveen@eabyas.com>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die();

require_once("$CFG->libdir/externallib.php");

use external_api;
use external_function_parameters;
use external_value;
use external_single_structure;
use external_multiple_structure;
// use tool_product\orders as order;
use tool_product\product;
use local_exams\local\exams;
/**
 * This is the external API for this component.
 *
 * @copyright  2022 Naveen Kumar <naveen@eabyas.com>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class tool_product_external extends external_api {
    
    public static function view_cart_parameters(){
        return new external_function_parameters(
            array(
                'products' =>   new external_multiple_structure(
                    new external_single_structure(
                        array(
                            'product'       =>  new external_value(PARAM_INT, 'product id'),
                            'variation'     =>  new external_value(PARAM_INT, 'variation id'),
                            'hasvariations' =>  new external_value(PARAM_BOOL, 'product has variations'),
                            'quantity'      =>  new external_value(PARAM_INT, 'product quantity'),
                            'category'      =>  new external_value(PARAM_INT, 'product category'),
                            'couponid'=>  new external_value(PARAM_INT, 'product couponid',VALUE_OPTIONAL),
                            'couponcode'=>  new external_value(PARAM_RAW, 'product couponcode',VALUE_OPTIONAL),
                            'roles' => new external_value(PARAM_BOOL, 'roles',VALUE_OPTIONAL), 
                            'grouped' => new external_value(PARAM_INT, 'grouped product',VALUE_OPTIONAL),
                            'language' => new external_value(PARAM_RAW, 'language',VALUE_OPTIONAL),
                            'hallscheduleid' => new external_value(PARAM_RAW, 'hallscheduleid',VALUE_OPTIONAL),
                            'profileid' => new external_value(PARAM_RAW, 'profileid',VALUE_OPTIONAL),
                            'processtype' => new external_value(PARAM_RAW, 'processtype',VALUE_OPTIONAL),
                            'tandcconfirm' => new external_value(PARAM_RAW, 'tandcconfirm',VALUE_OPTIONAL),
                        )
                    )
                )
            )
        );
    }

    public function view_cart( $products ){

        global $DB,$USER;

        $return = array(
            'items'         =>  [],
            'tracks'        =>  [],
            'total_items'   =>  0,
            'cart_summary'  =>  [],
            'isloggedin'    =>  isloggedin()
        );

        foreach( $products as $key => $value){

            $product_id = $value['hasvariations'] ? $value['variation'] : $value['product'];
           
            $product = (new product)->get_product($product_id);

            if($value['couponid'] > 0){

                $couponinfo = (new product)->get_couponinfo($value['couponcode'],$value['couponid']);

                if($couponinfo){

                    $product=(array)$product;
                
                    $coupon = (new product)->coupon_validationcheck($couponinfo);

                    $product['couponid'] = $value['couponid'];

                    $product['couponcode'] = $value['couponcode'];

                    $product=array_merge($product,$coupon);


                    $product['couponcode_discount_amount'] = ($product['price'] * (($couponinfo->discount/100)));

                    $total_discount = $product['discount'] + $couponinfo->discount;

                    if($total_discount){
                       
                        $product['discount'] = $total_discount;

                    } 

                    $product=(object)$product;

                }
            }
            $product->processtype = $value['processtype'];
            $product = (new product)->format_product( $product );
            $product['quantity'] = $value['quantity'];
            $product['product'] =  $value['product'];
            $product['grouped'] = $value['grouped'];
            $product['hasvariations'] = $value['hasvariations'];
            $product['language'] = $value['language'];
            $product['hallscheduleid'] = $value['hallscheduleid'];
            $product['processtype'] = $value['processtype'];
            $product['usercountryaccess'] = (new product)->userprofilecountry( $product );
            $product['tandcconfirm'] = $value['tandcconfirm'];
            
            if($product['grouped']){
                $lt = new stdClass;
                $lt->trackid = $product['grouped'];
                
                $learningtrack =  (new local_learningtracks\learningtracks)->get_listof_learningtracks($lt);
                // $product['grouped'] = $learningtrack->name;
                $return["tracks"][$learningtrack->id] = array(
                   "name"   =>  $learningtrack->name,
                   "id"     =>  $learningtrack->id,
                );
            }     
            
            $return['items'][] = $product;
        }

        $products = $return['items'];
        foreach($products as $productsinfo) {
            if (!empty($productsinfo['usercountryaccess'])) {
                $return['userprofileaccess'] = $productsinfo['usercountryaccess'];
            }
        }
        $return['total_items'] = sizeof($return['items']);
        $return['cart_summary'] = (new product)->get_cart_summary( $return['items'] );

        return $return;
    }

    public function view_cart_returns(){
        return new external_single_structure(
            array(
                'cart_summary'  => new external_single_structure(
                    array(
                        'total_price'  => new external_value(PARAM_RAW, 'cart_total'),
                        'discount_price' => new external_value(PARAM_RAW, 'discount_price'),
                        'total_discount_amount' => new external_value(PARAM_RAW, 'total_discount_amount'),
                        'total_discount_amount_display' => new external_value(PARAM_RAW, 'total_discount_amount_display'),
                    )
                ),
                'total_items' => new external_value(PARAM_INT, 'total_items'),
                'userprofileaccess' => new external_value(PARAM_RAW, 'userprofileaccess', VALUE_OPTIONAL),
                'tracks' => new external_multiple_structure(
                    new external_single_structure(
                        array(
                            'id' => new external_value(PARAM_INT, 'id', VALUE_OPTIONAL),
                            'name' => new external_value(PARAM_TEXT, 'name', VALUE_OPTIONAL)
                        )
                    )
                ),
                'items' => new external_multiple_structure(
                    new external_single_structure(
                        array(
                            'id' => new external_value(PARAM_INT, 'id'),
                            'category' => new external_value(PARAM_INT, 'category'),
                            'referenceid' => new external_value(PARAM_INT, 'referenceid'),
                            'code' => new external_value(PARAM_TEXT, 'code'),
                            'name' => new external_value(PARAM_TEXT, 'name'),
                            'sale_price' => new external_value(PARAM_FLOAT, 'sale_price'),
                            'actual_price' => new external_value(PARAM_FLOAT, 'actual_price'),
                            'discount' => new external_value(PARAM_INT, 'discount'),
                            'units' => new external_value(PARAM_INT, 'units'),
                            'stock' => new external_value(PARAM_INT, 'stock'),
                            'type' => new external_value(PARAM_INT, 'type'),
                            'status' => new external_value(PARAM_INT, 'status'),
                            'description' => new external_value(PARAM_RAW, 'description'),
                            'quantity' => new external_value(PARAM_INT, 'quantity'),
                            'thumbnail' =>  new external_value(PARAM_RAW, 'thumbnail'),
                            'product'  => new external_value(PARAM_INT, 'product'),
                            'hasvariations' => new external_value(PARAM_BOOL, 'hasvariations'),
                            'early_registration_discount' => new external_value(PARAM_INT, 'early_registration_discount',VALUE_OPTIONAL),
                            'early_registration_discount_amount' => new external_value(PARAM_RAW, 'early_registration_discount_amount',VALUE_OPTIONAL),
                            'early_registration_discount_view' => new external_value(PARAM_BOOL, 'early_registration_discount_view',VALUE_OPTIONAL),
                            'viewcourseurl' => new external_value(PARAM_RAW, 'viewcourseurl'),
                            'discount_type' => new external_value(PARAM_RAW, 'discount_type',VALUE_OPTIONAL),
                            'couponactionview' => new external_value(PARAM_RAW, 'couponactionview',VALUE_OPTIONAL),
                            'programduration' => new external_value(PARAM_INT, 'programduration',VALUE_OPTIONAL),
                            'lectures' => new external_value(PARAM_INT, 'lectures',VALUE_OPTIONAL),
                            'couponcode_discount_amount' => new external_value(PARAM_RAW, 'couponcode_discount_amount',VALUE_OPTIONAL),
                            'couponid'=>  new external_value(PARAM_INT, 'product couponid',VALUE_OPTIONAL),
                            'couponcode'=>  new external_value(PARAM_RAW, 'product couponcode',VALUE_OPTIONAL),
                            'couponvalid' => new external_value(PARAM_BOOL, 'couponvalid',VALUE_OPTIONAL),
                            'couponmsg' => new external_value(PARAM_RAW, 'couponvalid',VALUE_OPTIONAL),
                            'groups_organization_discount' => new external_value(PARAM_INT, 'groups_organization_discount',VALUE_OPTIONAL),
                            'groups_organization_discount_amount' => new external_value(PARAM_RAW, 'groups_organization_discount_amount',VALUE_OPTIONAL),
                            'groups_organization_discount_view' => new external_value(PARAM_BOOL, 'groups_organization_discount_view',VALUE_OPTIONAL),
                            'grouped' => new external_value(PARAM_INT, 'is grouped', VALUE_OPTIONAL),
                            'language' => new external_value(PARAM_RAW, 'language', VALUE_OPTIONAL),
                            'hallscheduleid' => new external_value(PARAM_RAW, 'hallscheduleid', VALUE_OPTIONAL),
                            'usercountryaccess' => new external_value(PARAM_RAW, 'usercountryaccess', VALUE_OPTIONAL),
                            'sectors' => new external_multiple_structure(
                                new external_single_structure(
                                    array(
                                    'sectorid' => new external_value(PARAM_INT, 'sectorid'),
                                    'sectorname' => new external_value(PARAM_RAW, 'sectorname'),
                                    )
                                    )
                                ), 'sectors', VALUE_OPTIONAL,
                            )
                    )
                ),
                'isloggedin' => new external_value(PARAM_BOOL, 'isloggedin') 
            )
        );
    }

    public static function checkout_summary_parameters(){
        return new external_function_parameters(
            array(
                'products' =>   new external_multiple_structure(
                    new external_single_structure(
                        array(
                            'product'       =>  new external_value(PARAM_INT, 'product id'),
                            'variation'     =>  new external_value(PARAM_INT, 'variation id'),
                            'hasvariations' =>  new external_value(PARAM_BOOL, 'product has variations'),
                            'quantity'      =>  new external_value(PARAM_INT, 'product quantity'),
                            'grouped'       =>  new external_value(PARAM_INT, 'product grouped'),    
                            'category'      =>  new external_value(PARAM_INT, 'product category'),
                            'couponid'=>  new external_value(PARAM_INT, 'product couponid',VALUE_OPTIONAL),
                            'couponcode'=>  new external_value(PARAM_RAW, 'product couponcode',VALUE_OPTIONAL),
                            'roles' =>  new external_value(PARAM_BOOL, 'product has roles',VALUE_OPTIONAL),
                            'language' =>  new external_value(PARAM_RAW, 'language', VALUE_OPTIONAL),
                            'hallscheduleid' =>  new external_value(PARAM_INT, 'hallscheduleid', VALUE_OPTIONAL),
                            'profileid' =>  new external_value(PARAM_INT, 'profileid', VALUE_OPTIONAL),
                            'processtype' =>  new external_value(PARAM_RAW, 'processtype', VALUE_OPTIONAL),
                            'tandcconfirm' => new external_value(PARAM_RAW, 'tandcconfirm',VALUE_OPTIONAL),
                        )
                    )
                ),
                'formdata' =>new external_single_structure(
                        array(
                            'tablename'=> new external_value(PARAM_RAW, 'tablename',VALUE_OPTIONAL),
                            'fieldname'=> new external_value(PARAM_RAW, 'fieldname',VALUE_OPTIONAL),
                            'fieldid' => new external_value(PARAM_INT, 'fieldid',VALUE_OPTIONAL),
                            'selectedseats' => new external_value(PARAM_INT, 'selectedseats',VALUE_OPTIONAL),
                            'sesskey' => new external_value(PARAM_RAW, 'total_discount',VALUE_OPTIONAL),
                            'parentfieldid' => new external_value(PARAM_INT, 'parentfieldid',VALUE_OPTIONAL),
                        ), 'formdata',VALUE_OPTIONAL
                )

            )
        );
    }

    public static function checkout_summary( $products,$formdata=array()){

        global $DB,$USER;

        $return = array(
            'total_items'   =>  0,
            'products'      =>  [],
            'order_summary' =>  []
        );

        $systemcontext = \context_system::instance();


        $sql = "SELECT ra.id
                  FROM {role_assignments} ra, {role} r, {context} c
                 WHERE ra.userid =:userid
                       AND ra.roleid = r.id
                       AND ra.contextid = c.id
                       AND ra.contextid =:contextid AND r.shortname !='trainee' ";

        $roles=$DB->record_exists_sql($sql ,array('userid'=>$USER->id,'contextid'=>$systemcontext->id));

        foreach($products as $key => $value){
            $product_id = $value['hasvariations'] ? $value['variation'] : $value['product'];
            $product = (new product)->get_product($product_id);

            if($value['couponid']){

                $couponinfo = (new product)->get_couponinfo($value['couponcode'],$value['couponid']);


                if($couponinfo){

                    $product=(array)$product;
                
                    $coupon = (new product)->coupon_validationcheck($couponinfo);

                    $product['couponid'] = $value['couponid'];

                    $product['couponcode'] = $value['couponcode'];


                    $product=array_merge($product,$coupon);


                    $product['couponcode_discount_amount'] = ($product['price'] * (($couponinfo->discount/100)));

                    $total_discount = $product['discount'] + $couponinfo->discount;

                    if($total_discount){
                       
                        $product['discount'] = $total_discount;

                    } 

                    $product=(object)$product;

                }
            }
            $product->is_enrolled = (new product)->is_user_enrolled( $product );
            $product->processtype = $value['processtype'];

            $product = (new product)->format_product($product);

            $product['usercountryaccess'] = (new product)->userprofilecountry( $product );
            $product['quantity'] = $value['quantity'];
            $product['grouped'] = $value['grouped'];
            $product['language'] = $value['language'];
            $product['hallscheduleid'] = $value['hallscheduleid'];
            $product['profileid'] = $value['profileid'];
            $product['processtype'] = $value['processtype'];
            $product['roles'] = $roles;
            $product['tandcconfirm'] = $value['tandcconfirm'];


            $return['products'][] = $product;
        }
        $summary = (new product)->get_order_summary( $return['products']);
        $products = $summary['items'];
        foreach($products as $productsinfo) {
            if (!empty($productsinfo['usercountryaccess'])) {
                $summary['items'] = [];
                $summary['userprofileaccess'] = $productsinfo['usercountryaccess'];
            }
        }

        $summary['payment_methods'] = (new product)->get_payment_methods();

        $summary['formdata'] = $formdata; 
        $summary['category'] = 'booking';
        $summary['productdata'] =  base64_encode(serialize($summary));

        return $summary;
    }

    public static function checkout_summary_returns(){
        return new external_single_structure(
            array(
                'userprofileaccess' => new external_value(PARAM_RAW, 'user profile access based on country', VALUE_OPTIONAL), 
                'total_purchases' => new external_value(PARAM_INT, 'total_purchases'),
                'total_price' => new external_value(PARAM_FLOAT, 'total_price'),
                'discount_price' => new external_value(PARAM_FLOAT, 'discount_price'),
                'taxdisplay' => new external_value(PARAM_RAW, 'taxdisplay'),
                'total_discount' => new external_value(PARAM_FLOAT, 'total_discount'),
                'total' => new external_value(PARAM_FLOAT, 'total_purchases'),
                'taxes' => new external_value(PARAM_FLOAT, 'taxes'),
                'tax_slab' => new external_value(PARAM_INT, 'tax_slab'),
                'category' => new external_value(PARAM_TEXT, 'category', VALUE_OPTIONAL),
                'productdata' => new external_value(PARAM_RAW, 'Product data'),
                'productinfo' => new external_value(PARAM_RAW, 'Product data'),
               
                'items' => new external_multiple_structure(
                    new external_single_structure(
                        array(
                            'product_id'=> new external_value(PARAM_INT, 'product_id'),
                            'name'      => new external_value(PARAM_TEXT, 'name'),
                            'referenceid'  => new external_value(PARAM_TEXT, 'name'),
                            'quantity'  => new external_value(PARAM_INT, 'quantity'),
                            'total'     =>  new external_value(PARAM_FLOAT, 'total'),
                            'item_tax'     =>  new external_value(PARAM_RAW, 'item_discount_price',VALUE_OPTIONAL),
                            'item_discount_price'     =>  new external_value(PARAM_RAW, 'item_tax',VALUE_OPTIONAL),
                            'item_total_price'     =>  new external_value(PARAM_RAW, 'item_total_price',VALUE_OPTIONAL),
                            'is_enrolled'     =>  new external_value(PARAM_BOOL, 'is_enrolled'),
                            'early_registration_discount' => new external_value(PARAM_RAW, 'early_registration_discount',VALUE_OPTIONAL),
                            'early_registration_discount_amount' => new external_value(PARAM_RAW, 'early_registration_discount_amount',VALUE_OPTIONAL),
                            'discount_type' => new external_value(PARAM_RAW, 'discount_type',VALUE_OPTIONAL),
                            'couponcode_discount_amount' => new external_value(PARAM_RAW, 'couponcode_discount_amount',VALUE_OPTIONAL),
                            'couponid'=>  new external_value(PARAM_INT, 'product couponid',VALUE_OPTIONAL),
                            'couponcode'=>  new external_value(PARAM_RAW, 'product couponcode',VALUE_OPTIONAL),
                            'couponvalid' => new external_value(PARAM_BOOL, 'couponvalid',VALUE_OPTIONAL),
                            'couponmsg' => new external_value(PARAM_RAW, 'couponvalid',VALUE_OPTIONAL),
                            'groups_organization_discount' => new external_value(PARAM_INT, 'groups_organization_discount',VALUE_OPTIONAL),
                            'groups_organization_discount_amount' => new external_value(PARAM_RAW, 'groups_organization_discount_amount',VALUE_OPTIONAL),
                            'groups_organization_discount_view' => new external_value(PARAM_BOOL, 'groups_organization_discount_view',VALUE_OPTIONAL),
                            'couponactionview' => new external_value(PARAM_RAW, 'couponactionview',VALUE_OPTIONAL),
                            'category'      =>  new external_value(PARAM_INT, 'product category',VALUE_OPTIONAL),
                            'roles' => new external_value(PARAM_BOOL, 'roles',VALUE_OPTIONAL), 
                            'grouped' => new external_value(PARAM_INT, 'product category',VALUE_OPTIONAL),
                            'language' => new external_value(PARAM_RAW, 'language', VALUE_OPTIONAL),
                            'hallscheduleid' => new external_value(PARAM_RAW, 'hallscheduleid', VALUE_OPTIONAL),
                            'profileid' => new external_value(PARAM_RAW, 'profileid', VALUE_OPTIONAL),
                            'processtype' => new external_value(PARAM_RAW, 'processtype', VALUE_OPTIONAL),
                        )
                    )
                ),
                'payment_methods' =>    new external_multiple_structure(
                    new external_single_structure(
                        array(
                            'slug'  =>  new external_value(PARAM_TEXT, 'slug'),
                            'name'  =>  new external_value(PARAM_TEXT, 'name'),
                        )
                    )
                ) ,
                'formdata' =>new external_single_structure(
                    array(
                        'tablename'=> new external_value(PARAM_RAW, 'tablename',VALUE_OPTIONAL),
                        'fieldname'=> new external_value(PARAM_RAW, 'fieldname',VALUE_OPTIONAL),
                        'fieldid' => new external_value(PARAM_INT, 'fieldid',VALUE_OPTIONAL),
                        'selectedseats' => new external_value(PARAM_INT, 'selectedseats',VALUE_OPTIONAL),
                        'sesskey' => new external_value(PARAM_RAW, 'total_discount',VALUE_OPTIONAL),
                        'parentfieldid' => new external_value(PARAM_INT, 'parentfieldid',VALUE_OPTIONAL),
                    ), 'formdata',VALUE_OPTIONAL
                )
            )
        );
    }

    public static function user_login_parameters(){
        return new external_function_parameters(
            array(
                'username'  =>  new external_value(PARAM_TEXT,  'Username'),
                'password'  =>  new external_value(PARAM_TEXT, 'Password')
            )
        );
    }

    public static function user_login($username, $password){
        global $CFG;
        // $loggedin = false;
        // $userlogin = get_auth_plugin('manual');
        // $isloggedin = $userlogin->user_login($username, $password);

        if(!isloggedin()){
            $failurereason = null;
            $user = authenticate_user_login($username, $password, false, $failurereason);
            if ($failurereason) {
                switch($failurereason) {
                    case AUTH_LOGIN_NOUSER:
                        $reason = get_string('invalidlogin', 'tool_product');
                        break;
                    case AUTH_LOGIN_SUSPENDED:
                        $reason = get_string('usersuspended', 'tool_product');
                        break;
                    case AUTH_LOGIN_FAILED:
                        $reason = get_string('invalidlogindetails', 'tool_product');
                        break;
                    case AUTH_LOGIN_LOCKOUT:
                        $reason = get_string('accountlocked', 'tool_product');
                        break;
                    case AUTH_LOGIN_UNAUTHORISED:
                        $reason = get_string('unauthorisedlogin', 'core', $username);
                        break;
                    default:
                        $reason = get_string('unknownloginfailure', 'tool_product', $failurereason);
                        break;

                }
            }
            if(!$user){
                return array(
                    'error'   => $reason
                );
            }
            $login = complete_user_login($user);
            \core\session\manager::apply_concurrent_login_limit($user->id, session_id());
            if (!empty($CFG->nolastloggedin)) {
            
            } else if (empty($CFG->rememberusername)) {
                set_moodle_cookie('');
            } else {
                set_moodle_cookie($USER->username);
            }
            $loggedin = true;
        }

        return array(
            'success'   => $loggedin
        );
    }

    public static function user_login_returns(){
        return new external_single_structure(
            array(
                'success'  => new external_value(PARAM_RAW, 'success', VALUE_OPTIONAL),
                'error'  => new external_value(PARAM_RAW, 'error', VALUE_OPTIONAL),
            )
        );
    }
    public static function org_order_payments_parameters(){
           return new external_function_parameters(
            array(
                'products' =>new external_single_structure(
                                array(
                                    'paymenttype'=> new external_value(PARAM_TEXT, 'paymenttype'),
                                    'total_purchases' => new external_value(PARAM_INT, 'total_purchases'),
                                    'total_price' => new external_value(PARAM_FLOAT, 'total_price'),
                                    'discount_price' => new external_value(PARAM_FLOAT, 'discount_price'),
                                    'total_discount' => new external_value(PARAM_FLOAT, 'total_discount'),
                                    'category' => new external_value(PARAM_TEXT, 'category', VALUE_OPTIONAL),
                                    'total' => new external_value(PARAM_FLOAT, 'total_purchases'),
                                    'taxes' => new external_value(PARAM_FLOAT, 'taxes'),
                                    'tax_slab' => new external_value(PARAM_INT, 'tax_slab'),
                                    'items' => new external_multiple_structure(
                                        new external_single_structure(
                                            array(
                                                'product_id'=> new external_value(PARAM_INT, 'product_id'),
                                                'name'      => new external_value(PARAM_TEXT, 'name'),
                                                'quantity'  => new external_value(PARAM_INT, 'quantity'),
                                                'total'     =>  new external_value(PARAM_FLOAT, 'total'),
                                                'early_registration_discount' => new external_value(PARAM_INT, 'early_registration_discount',VALUE_OPTIONAL),
                                                'early_registration_discount_amount' => new external_value(PARAM_RAW, 'early_registration_discount_amount',VALUE_OPTIONAL),
                                                'discount_type' => new external_value(PARAM_RAW, 'discount_type',VALUE_OPTIONAL),
                                                'couponcode_discount_amount' => new external_value(PARAM_RAW, 'couponcode_discount_amount',VALUE_OPTIONAL),
                                                'couponid'=>  new external_value(PARAM_INT, 'product couponid',VALUE_OPTIONAL),
                                                'couponcode'=>  new external_value(PARAM_RAW, 'product couponcode',VALUE_OPTIONAL),
                                                'couponvalid' => new external_value(PARAM_BOOL, 'couponvalid',VALUE_OPTIONAL),
                                                'couponmsg' => new external_value(PARAM_RAW, 'couponvalid',VALUE_OPTIONAL),
                                                'groups_organization_discount' => new external_value(PARAM_INT, 'groups_organization_discount',VALUE_OPTIONAL),
                                                'groups_organization_discount_amount' => new external_value(PARAM_RAW, 'groups_organization_discount_amount',VALUE_OPTIONAL),
                                                'groups_organization_discount_view' => new external_value(PARAM_BOOL, 'groups_organization_discount_view',VALUE_OPTIONAL),
                                                'couponactionview' => new external_value(PARAM_RAW, 'couponactionview',VALUE_OPTIONAL),
                                                'category' =>  new external_value(PARAM_INT, 'product category'),
                                            )
                                        )
                                    ),
                                    'payment_methods' =>    new external_multiple_structure(
                                        new external_single_structure(
                                            array(
                                                'slug'  =>  new external_value(PARAM_TEXT, 'slug'),
                                                'name'  =>  new external_value(PARAM_TEXT, 'name'),
                                            )
                                        )
                                    ),
                                    'formdata' =>new external_single_structure(
                                        array(
                                            'tablename'=> new external_value(PARAM_RAW, 'tablename',VALUE_OPTIONAL),
                                            'fieldname'=> new external_value(PARAM_RAW, 'fieldname',VALUE_OPTIONAL),
                                            'fieldid' => new external_value(PARAM_INT, 'fieldid',VALUE_OPTIONAL),
                                            'selectedseats' => new external_value(PARAM_INT, 'selectedseats',VALUE_OPTIONAL),
                                            'sesskey' => new external_value(PARAM_RAW, 'total_discount',VALUE_OPTIONAL),
                                            'parentfieldid' => new external_value(PARAM_INT, 'parentfieldid',VALUE_OPTIONAL),
                                        ), 'formdata',VALUE_OPTIONAL
                                    )  
                                )
                            )
                
                )
            );

    }

    public static function org_order_payments($products){
        global $OUTPUT, $CFG, $DB,$USER,$PAGE;
        $context = context_system::instance();
        // Parameter validation.
        $coupondiscounfailed=$seatexceed=$return=false;
        $products=(object)$products;
        $formdata=(object)$products->formdata;
        $productitems=(object)$products->items;
        foreach($productitems as $key =>$product){
            if($product['couponid'] > 0 && $product['discount_type']== 'coupon'){
                $couponinfo = (new product)->get_couponinfo($product['couponcode'],$product['couponid']);
                if($couponinfo){
                    $coupon = (new product)->coupon_validationcheck($couponinfo,$USER->id);
                    if($coupon['couponvalid'] == false){
                        return array(
                            'seatexceed'   => false,
                            'success'   => false,
                            'paymenttype' => 'nopaymenttype',
                            'coupondiscounfailed'=>true
                        );

                    }

                }
            }
            $product=(object)$product;
            $tabledata=new \stdClass();
            $tabledata->productid=$product->product_id;
            $tabledata->tablename=$formdata->tablename;
            $tabledata->fieldname=$formdata->fieldname;
            $tabledata->fieldid=$formdata->fieldid;
            $tabledata->tuserid=$formdata->tuserid;
            $tabledata->autoapproval=$formdata->autoapproval;
            $tabledata->orguserid=$USER->id;
            $tabledata->paymenttype=$products->paymenttype;
            $tabledata->paymenton=time();
            $tabledata->amount=$products->total;
            $tabledata->purchasedseats=$product->quantity;
            $tabledata->quantity=$product->quantity;
            $tabledata->originalprice=$products->total_price;
            $tabledata->discountprice=$products->total_discount;
            $tabledata->taxes=$products->taxes;
            if($products->paymenttype == 'prepaid' || $products->paymenttype == 'telr'){
                if($products->paymenttype == 'prepaid'){
                    $checkpoint=(new product)->orders_prepaid_paymentsupdate($tabledata);

                    if(!is_siteadmin() && has_capability('local/organization:manage_trainee', $context)) {
                        if($checkpoint){
                            (new tool_product\traineewallet)->userwalletpayments($tabledata,$product->product_id);
                            (new product)->enrol($products, $USER->id);
                            $return = true;
                            break;
                        } else {
                            $return = false;
                            break;
                        }
                    } elseif (!$checkpoint) {
                        $return=false;
                        continue ;
                    }
                }
                $tabledata->paymentapprovalstatus=1;
                $tabledata->approvaluserid=$USER->id;
                $tabledata->approvalon=time();
            }
            $return = (new product)->insert_update_org_order_payments($tabledata,$product);
            $orgorderid = $DB->get_field_sql("SELECT oos.id
                                                FROM {tool_order_approval_seats} AS oos
                                                WHERE oos.productid= $tabledata->productid AND oos.usercreated=:id 
                                                ORDER BY id DESC ", ['id' => $USER->id]);
        }

        $paymenttype=$products->paymenttype ? $products->paymenttype : 'nopaymenttype';

        return array(
            'seatexceed'   => $seatexceed,
            'success'   => $return,
            $paymenttype => $paymenttype,
            'coupondiscounfailed'=>$coupondiscounfailed,
            'paymentid' => $orgorderid,
        );
    }

    public static function org_order_payments_returns(){
       return new external_single_structure(
            array(
                'seatexceed'=> new external_value(PARAM_RAW, 'seatexceed'),
                'success'  => new external_value(PARAM_RAW, 'success'),
                'postpaid'  => new external_value(PARAM_RAW, 'postpaid',VALUE_OPTIONAL),
                'prepaid'  => new external_value(PARAM_RAW, 'prepaid',VALUE_OPTIONAL),
                'telr'  => new external_value(PARAM_RAW, 'telr',VALUE_OPTIONAL),
                'nopaymenttype'  => new external_value(PARAM_RAW, 'nopaymenttype',VALUE_OPTIONAL),
                'coupondiscounfailed'=> new external_value(PARAM_RAW, 'coupondiscounfailed'),
                'paymentid'=> new external_value(PARAM_INT, 'orderid', VALUE_OPTIONAL),
            )
        );
    }
    /**
     * Returns description of method parameters.
     *
     * @return external_function_parameters.
     */
    public static function get_post_financialpayments_parameters() {
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
     * Gets the list of post_financialpayments for the given criteria. The post_financialpayments
     * will be exported in a summaries format and won't include all of the
     * post_financialpayments data.
     *
     * @param int $userid Userid id to find post_financialpayments
     * @param int $contextid The context id where the post_financialpayments will be rendered
     * @param int $limit Maximum number of results to return
     * @param int $offset Number of items to skip from the beginning of the result set.
     * @return array The list of post_financialpayments and total competency count.
     */
    public static function get_post_financialpayments(
        $options=false,
        $dataoptions=false,
        $offset = 0,
        $limit = 0,
        $filterdata=false
    ) {
        global $OUTPUT, $CFG, $DB,$USER,$PAGE;
        $sitecontext = context_system::instance();
        require_login();
        $PAGE->set_url('/tool/product/financialpayments.php', array());
        $PAGE->set_context($sitecontext);
        // Parameter validation.
        $params = self::validate_parameters(
            self::get_post_financialpayments_parameters(),
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

        // $stable = new \stdClass();
        // $stable->thead = true;
        // $post_financialpayments=(new product)::get_post_financialpayments($stable,$filtervalues);
        // $totalcount=$post_financialpayments['paymentscount'];
        // $stable->thead = false;
        // $stable->start = $offset;
        // $stable->length = $limit;

        // $data = array();

        // if($totalcount>0){

        //     $renderer = $PAGE->get_renderer('tool_product');

        //     $renderdata=$renderer->lis_post_financialpayments($stable,$filtervalues);

        //     $data = array_merge($data,$renderdata['data']);
        // }




        $stable = new \stdClass();
        $stable->thead = false;
        $stable->start = $offset;
        $stable->length = $limit;

        $data = (new tool_product\product)->get_orgpayments($stable,$filtervalues);

        $totalcount = $data['totalcount'];
        if($data) {
            $nodata = false;
        } else {
            $nodata = true;
        }

        return [
            'totalcost' => round($data['totalcost'],2),
            'totalcount' => $totalcount,
            'records' =>$data['data'],
            'options' => $options,
            'dataoptions' => $dataoptions,
            'filterdata' => $filterdata,
            'nodata' => get_string('nopayments','tool_product')
        ];
    }

    /**
     * Returns description of method result value.
     */
    public static function  get_post_financialpayments_returns() {
        return new external_single_structure([
            'options' => new external_value(PARAM_RAW, 'The paging data for the service', VALUE_OPTIONAL),
            'dataoptions' => new external_value(PARAM_RAW, 'The data for the service', VALUE_OPTIONAL),
            'totalcost' => new external_value(PARAM_RAW, 'total cost of post_financialpayments in result set', VALUE_OPTIONAL),
            'totalcount' => new external_value(PARAM_INT, 'total number of post_financialpayments in result set', VALUE_OPTIONAL),
            'filterdata' => new external_value(PARAM_RAW, 'The data for the service', VALUE_OPTIONAL),
            'nodata' => new external_value(PARAM_RAW, 'The data for the service', VALUE_OPTIONAL),
            'records' => new external_multiple_structure(
                new external_single_structure(
                    array(
                        'id' => new external_value(PARAM_INT, 'payment id'),
                        'sendemailurl' => new external_value(PARAM_RAW, 'sendemailurl'),
                        'trainingname' => new external_value(PARAM_RAW, 'training name'),
                        'name' => new external_value(PARAM_RAW, 'name'),
                        'referenceid' => new external_value(PARAM_INT, 'referenceid'),
                        'cost' => new external_value(PARAM_RAW, 'training cost'),
                        'costview' => new external_value(PARAM_RAW, 'Cost View'),
                        'sendemailactionview' => new external_value(PARAM_RAW, 'sendemailactionview'),
                        'organizationname' => new external_value(PARAM_RAW, 'training organization'),
                        'orgoffcialname'=> new external_value(PARAM_RAW, 'training orgoffcial'),
                        'mode' => new external_value(PARAM_RAW, 'training payment mode'),
                        'duedate' => new external_value(PARAM_RAW, 'training duedate'),
                        'paymentduedate' => new external_value(PARAM_INT, 'training duedate'),
                        'paymentstartdate' => new external_value(PARAM_INT, 'paymentstartdate'),
                        'paymentenddate' => new external_value(PARAM_INT, 'paymentenddate'),
                        'paymentsupdate' => new external_value(PARAM_RAW, 'training payments update'),
                        'purchasedseats' => new external_value(PARAM_INT, 'purchased seats'),
                        'startdate' => new external_value(PARAM_RAW, 'training startdate'),
                        'enddate' => new external_value(PARAM_RAW, 'training enddate'),
                        'usedseats' => new external_value(PARAM_RAW, 'used seats', VALUE_OPTIONAL),
                        'trainingstatus' => new external_value(PARAM_RAW, 'training status'),
                        'approvedseats' => new external_value(PARAM_INT, 'approved seats'),
                        'type' => new external_value(PARAM_RAW, 'type', VALUE_OPTIONAL),
                        'transactionid' => new external_value(PARAM_RAW, 'transactionid', VALUE_OPTIONAL),
                        'invoicetype' => new external_value(PARAM_RAW, 'invoicetype', VALUE_OPTIONAL),
                        'status' => new external_value(PARAM_RAW, 'status', VALUE_OPTIONAL),
                        'productname' => new external_value(PARAM_RAW, 'productname', VALUE_OPTIONAL),
                        'orderinfo' => new external_value(PARAM_RAW, 'orderinfo', VALUE_OPTIONAL),
                        'entityoldid' => new external_value(PARAM_RAW, 'entityoldid', VALUE_OPTIONAL),
                        'approvalseats' => new external_value(PARAM_INT, 'approval seats'),
                        // 'description' => new external_value(PARAM_RAW, 'description', VALUE_OPTIONAL),
                        'invoiceaction' => new external_value(PARAM_BOOL, 'invoiceaction', VALUE_OPTIONAL),
                        'pname' => new external_value(PARAM_RAW, 'pname', VALUE_OPTIONAL),
                        'productid' => new external_value(PARAM_INT, 'productid', VALUE_OPTIONAL),
                        'trainingagreement' => new external_value(PARAM_RAW, 'trainingagreement', VALUE_OPTIONAL),
                        'tagree' => new external_value(PARAM_RAW, 'tagree', VALUE_OPTIONAL),
                        'tagreement' => new external_value(PARAM_RAW, 'tagreement', VALUE_OPTIONAL),

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
    public static function get_orders_approval_parameters() {
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
     * Gets the list of post_financialpayments for the given criteria. The post_financialpayments
     * will be exported in a summaries format and won't include all of the
     * post_financialpayments data.
     *
     * @param int $userid Userid id to find post_financialpayments
     * @param int $contextid The context id where the post_financialpayments will be rendered
     * @param int $limit Maximum number of results to return
     * @param int $offset Number of items to skip from the beginning of the result set.
     * @return array The list of post_financialpayments and total competency count.
     */
    public static function get_orders_approval(
        $options,
        $dataoptions,
        $offset = 0,
        $limit = 0,
        $filterdata
    ) {
        global $OUTPUT, $CFG, $DB,$USER,$PAGE;
        $sitecontext = context_system::instance();
        require_login();
        $PAGE->set_url('/tool/product/orderapproval.php', array());
        $PAGE->set_context($sitecontext);
        // Parameter validation.
        $params = self::validate_parameters(
            self::get_orders_approval_parameters(),
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
        $post_financialpayments=(new product)::get_orders_approval($stable,$filtervalues);
        $totalcount=$post_financialpayments['orderscount'];
        $stable->thead = false;
        $stable->start = $offset;
        $stable->length = $limit;
        $data = array();

        if($totalcount>0){
            $renderer = $PAGE->get_renderer('tool_product');
            $data = array_merge($data,$renderer->lis_order_approval($stable,$filtervalues));
        }
        return [
            'totalcount' => $totalcount,
            'records' =>$data,
            'options' => $options,
            'dataoptions' => $dataoptions,
            'filterdata' => $filterdata,
            'nodata' => get_string('nopayments','tool_product')
        ];
    }

    /**
     * Returns description of method result value.
     */
    public static function  get_orders_approval_returns() {
        return new external_single_structure([
            'options' => new external_value(PARAM_RAW, 'The paging data for the service'),
            'dataoptions' => new external_value(PARAM_RAW, 'The data for the service'),
            'totalcount' => new external_value(PARAM_INT, 'total number of post_financialpayments in result set'),
            'filterdata' => new external_value(PARAM_RAW, 'The data for the service'),
            'nodata' => new external_value(PARAM_RAW, 'The data for the service'),
            'records' => new external_multiple_structure(
                            new external_single_structure(
                                array(
                                    'id' => new external_value(PARAM_INT, 'payment id'),
                                    'trainingname' => new external_value(PARAM_RAW, 'training name'),
                                    'organizationname' => new external_value(PARAM_RAW, 'training organization'),
                                    'orgoffcialname'=> new external_value(PARAM_RAW, 'training orgoffcial'),
                                    'duedate' => new external_value(PARAM_RAW, 'training duedate'),
                                    'offercode' => new external_value(PARAM_RAW, 'Offering code'),
                                    'approvalsupdate' => new external_value(PARAM_RAW, 'training approvals update'),
                                    'purchasedseats' => new external_value(PARAM_INT, 'purchased seats'),
                                    'approvalseats' => new external_value(PARAM_INT, 'approval seats'),
                                    'productdata' => new external_value(PARAM_RAW, 'Product related data'),
                                    'ordername' => new external_value(PARAM_RAW, 'Name of the oder')

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
    public static function viewpurchases_parameters() {
        return new external_function_parameters([
            'type' => new external_value(PARAM_RAW, 'The paging data for the service', VALUE_DEFAULT, ''),
            'options' => new external_value(PARAM_RAW, 'The paging data for the service', VALUE_OPTIONAL),
            'dataoptions' => new external_value(PARAM_RAW, 'The data for the service', VALUE_OPTIONAL),
            'offset' => new external_value(PARAM_RAW, 'Number of items to skip from the begging of the result set',
                VALUE_DEFAULT, 0),
            'limit' => new external_value(PARAM_RAW, 'Maximum number of results to return',
                VALUE_DEFAULT, 0),
            'filterdata' => new external_value(PARAM_RAW, 'The data for the service', VALUE_OPTIONAL),
            'contextid' => new external_value(PARAM_RAW, 'Maximum number of results to return', VALUE_DEFAULT, 1)
        ]);
    }
    /**
     * Gets the list of post_financialpayments for the given criteria. The post_financialpayments
     * will be exported in a summaries format and won't include all of the
     * post_financialpayments data.
     *
     * @param int $userid Userid id to find post_financialpayments
     * @param int $contextid The context id where the post_financialpayments will be rendered
     * @param int $limit Maximum number of results to return
     * @param int $offset Number of items to skip from the beginning of the result set.
     * @return array The list of post_financialpayments and total competency count.
     */
    public static function viewpurchases(
        $type=false,
        $options=false,
        $dataoptions=false,
        $offset = 0,
        $limit = 0,
        $filterdata=false,
        $context=false     
    ) {
        global $OUTPUT, $CFG, $DB,$USER,$PAGE;
        $sitecontext = context_system::instance();
        require_login();
        $PAGE->set_url('/my/index.php', array());
        $PAGE->set_context($sitecontext);
        // Parameter validation.
        $params = self::validate_parameters(
            self::viewpurchases_parameters(),
            [
                'options' => $options,
                'dataoptions' => $dataoptions,
                'offset' => $offset,
                'limit' => $limit,
                'filterdata' => $filterdata,
                'type' => $type
            ]
                );

        $data_object = (json_decode($dataoptions));
        $offset = $params['offset'];
        $limit = $params['limit'];
        $filtervalues = json_decode($filterdata);

        if ($type=='exams') {
            $filtervalues->status = 'local_exams';
            $filtervalues->type = 'mobile';
        } elseif ($type=='programs') {
            $filtervalues->status = 'local_trainingprogram';
            $filtervalues->type = 'mobile';
        } elseif ($type=='events') {
            $filtervalues->status = 'local_events';
            $filtervalues->type = 'mobile';
        }

        $stable = new \stdClass();
        $stable->thead = true;
        $post_financialpayments=(new product)::get_org_purchases($stable,$filtervalues);
        $totalcount=$post_financialpayments['orderscount'];
        $stable->thead = false;
        $stable->start = $offset;
        $stable->length = $limit;

        $data = array();

        if($totalcount>0){

            $renderer = $PAGE->get_renderer('tool_product');

            $data = array_merge($data,$renderer->lis_org_purchases($stable,$filtervalues));
        }
        $isevents = false;
        if($filtervalues->status == 'local_events') {
           $isevents = true;
        }
        return [
            'totalcount' => $totalcount,
            'records' =>$data,
            'options' => $options,
            'dataoptions' => $dataoptions,
            'filterdata' => $filterdata,
            'isevents' => $isevents,
            'nodata' => get_string('nopurchases','tool_product'),
            'products' => $data,
        ];
    }

    /**
     * Returns description of method result value.
     */
    public static function  viewpurchases_returns() {
        return new external_single_structure([
            'products' => new external_multiple_structure(
                new external_single_structure(
                    array(
                        'id' => new external_value(PARAM_INT, 'payment id'),
                        'name' => new external_value(PARAM_RAW, 'training name'),
                        'datebegin' => new external_value(PARAM_INT, 'startdate'),
                        'dateend' => new external_value(PARAM_INT, 'enddate'),
                        'purchasedseats' => new external_value(PARAM_RAW, 'purchased seats'),
                        'approvalseats' => new external_value(PARAM_RAW, 'approval seats'),
                        'availableseats' => new external_value(PARAM_RAW, 'available seats'),
                        'usedseats' => new external_value(PARAM_RAW, 'used seats'),
                        'enrollbtn' => new external_value(PARAM_RAW, 'training enrollbtn'),
                        'timelimit' => new external_value(PARAM_RAW, 'Time Limit'),
                        'offeringview' => new external_value(PARAM_RAW, 'offeringview'),
                        'referenceid' => new external_value(PARAM_INT, 'offeringview'),
                        'entityid' => new external_value(PARAM_INT, 'offeringview'),
                    )
                )
            ),
            'options' => new external_value(PARAM_RAW, 'The paging data for the service'),
            'isevents' => new external_value(PARAM_RAW, 'The paging data for the service'),
            'dataoptions' => new external_value(PARAM_RAW, 'The data for the service'),
            'totalcount' => new external_value(PARAM_INT, 'total number of post_financialpayments in result set'),
            'filterdata' => new external_value(PARAM_RAW, 'The data for the service'),
            'nodata' => new external_value(PARAM_RAW, 'The data for the service'),
            'records' => new external_multiple_structure(
                            new external_single_structure(
                                array(
                                    'id' => new external_value(PARAM_INT, 'payment id'),
                                    'trainingname' => new external_value(PARAM_RAW, 'training name'),
                                    'startdate' => new external_value(PARAM_RAW, 'training startdate'),
                                    'enddate' => new external_value(PARAM_RAW, 'training enddate'),
                                    'purchasedseats' => new external_value(PARAM_RAW, 'purchased seats'),
                                    'approvalseats' => new external_value(PARAM_RAW, 'approval seats'),
                                    'availableseats' => new external_value(PARAM_RAW, 'available seats'),
                                    'usedseats' => new external_value(PARAM_RAW, 'used seats'),
                                    'enrollbtn' => new external_value(PARAM_RAW, 'training enrollbtn'),
                                    'timelimit' => new external_value(PARAM_RAW, 'Time Limit'),
                                    'offeringview' => new external_value(PARAM_RAW, 'offeringview'),
                                    'action' => new external_value(PARAM_RAW, 'action'),
                                    'enrolledusersurl' => new external_value(PARAM_RAW, 'enrolledusersurl', VALUE_OPTIONAL),
                                    'courseid' => new external_value(PARAM_INT, 'courseid'),
                                    'userid' => new external_value(PARAM_INT, 'userid'),
                                    'trainingid' => new external_value(PARAM_INT, 'trainingid'),
                                     'checkfavornot' => new external_value(PARAM_RAW,'checkfavornot',VALUE_OPTIONAL),
                                     'checkcomponent' => new external_value(PARAM_RAW,'checkcomponent',VALUE_OPTIONAL),
                                     'checkcomponenttype' => new external_value(PARAM_RAW,'checkcomponenttype',VALUE_OPTIONAL),
                                     'hidefavexamsview' => new external_value(PARAM_RAW,'hidefavexamsview',VALUE_OPTIONAL),

                                )
                            )
            )
        ]);
    }
    public static function validate_couponcode_parameters(){
        return new external_function_parameters(
            array(
                'products' => new external_single_structure(
                            array(
                                'product'       =>  new external_value(PARAM_INT, 'product id'),
                                'category'      =>  new external_value(PARAM_INT, 'product category'),
                                'couponcode' =>  new external_value(PARAM_RAW, 'couponcode'),
                            )
                        )
                
            )
        );
    }

    public function validate_couponcode( $products ){

        global $DB,$USER;

        $systemcontext = \context_system::instance();


        $sql = "SELECT ra.id
                  FROM {role_assignments} ra, {role} r, {context} c
                 WHERE ra.userid =:userid
                       AND ra.roleid = r.id
                       AND ra.contextid = c.id
                       AND ra.contextid =:contextid AND r.shortname !='trainee' ";

        $roles=$DB->record_exists_sql($sql ,array('userid'=>$USER->id,'contextid'=>$systemcontext->id));


        if(empty(trim($products['couponcode']))){

            $return = array(
            'couponid'=> 0,
            'couponvalid'=> false,
            'couponmsg'   =>  get_string('emptycoupon','tool_product'),
            'couponcode'=> $products['couponcode'],
            );

        }else{

    
            $coupon = (new product)->get_couponinfo($products['couponcode']);

            $return=(new product)->coupon_validationcheck($coupon);

            $return['couponcode']=$products['couponcode'];

            $return['couponid']=($coupon) ? $coupon->id : 0;

        }

        $return['roles'] = ($roles) ? true : false;

        return $return;
    }

    public function validate_couponcode_returns(){
        return new external_single_structure(
            array(
                'couponvalid' => new external_value(PARAM_BOOL, 'couponvalid'),
                'couponmsg' => new external_value(PARAM_RAW, 'couponvalid'),
                'couponid' => new external_value(PARAM_INT, 'couponid'),
                'couponcode' => new external_value(PARAM_RAW, 'couponcode'), 
                'roles' => new external_value(PARAM_BOOL, 'roles'), 
            )
        );
    }

    public static function telr_begin_trans_parameters(){
        return new external_function_parameters(
            array(
                'products' => new external_value(PARAM_RAW, 'Products')
                // 'paymenttype' => new external_value(PARAM_TEXT, 'Payment type', VALUE_OPTIONAL)
            )
        );
    }

    public function telr_begin_trans( $products){

        global $DB,$USER;

        $systemcontext = \context_system::instance();

        $params = self::validate_parameters(
                    self::telr_begin_trans_parameters(),
                    [
                        'products' => $products
                        // 'paymenttype' => $paymenttype
                    ]
                );
        $seats_availability = (new tool_product\product)->seats_availability($params);
        $noseats = $seats_availability['noseats'];
        $productname = $seats_availability['productname'];
        $returnurl = $seats_availability['returnurl'];
        if(empty($seats_availability['noseats'])) {
            $return = (new tool_product\orders)->begin_transaciton($params);
            $noseats = $return['noseats'];
            $productname = $return['productname'];
            $returnurl = $return ? $return['returnurl']:$return;
        }
       
        return ['noseats' => $noseats,
        'productname' => $productname,
        'returnurl' => $returnurl,
        'products' => $seats_availability['products']?$seats_availability['products']:[]
        ];
    }

    public function telr_begin_trans_returns(){
        return new external_single_structure(
            array(
                'returnurl' => new external_value(PARAM_URL, 'returnurl',VALUE_OPTIONAL),
                'noseats' => new external_value(PARAM_RAW, 'noseats',VALUE_OPTIONAL),
                'productname' => new external_value(PARAM_RAW, 'productname',VALUE_OPTIONAL),
                'products' => new external_multiple_structure(
                    new external_single_structure(
                        array(
                            'productid' => new external_value(PARAM_RAW, 'productid', VALUE_OPTIONAL),        
                            'category' => new external_value(PARAM_RAW, 'category', VALUE_OPTIONAL),
                        )
                    )
                ),
        ));
    }

    public static function generate_sadadbill_parameters(){
        return new external_function_parameters(
            array(
                'products' => new external_value(PARAM_RAW, 'Products')
            )
        );
    }
    
    public function generate_sadadbill( $products){

        global $DB,$USER;

        $systemcontext = \context_system::instance();

        $params = self::validate_parameters(
                    self::generate_sadadbill_parameters(),
                    [
                        'products' => $products
                    ]
                );
        $data = (new product)->insert_update_sadad_invoice_record($params['products'],'new');
        return $data;
    }

    public function generate_sadadbill_returns(){
        return new external_single_structure(
            array(
                'returnurl' => new external_value(PARAM_URL, 'returnurl')
            )
        );
    }

    public static function postpaid_payments_parameters(){
        return new external_function_parameters(
            array(
                'products' => new external_value(PARAM_RAW, 'Products')
            )
        );
    }

    public function postpaid_payments( $products){

        global $DB,$USER;

        $systemcontext = \context_system::instance();

        $params = self::validate_parameters(
                    self::postpaid_payments_parameters(),
                    [
                        'products' => $products
                    ]
                );
        $productsdata=unserialize(base64_decode($params['products']));

        $productsdata['paymenttype']='postpaid';

        $return = self::org_order_payments($productsdata);
       

        return $return;
    }

    public function postpaid_payments_returns(){

        return self::org_order_payments_returns();
        
    }
    public static function prepaid_payments_parameters(){
        return new external_function_parameters(
            array(
                'products' => new external_value(PARAM_RAW, 'Products')
            )
        );
    }

    public function prepaid_payments( $products){

        global $DB,$USER;

        $systemcontext = \context_system::instance();

        $params = self::validate_parameters(
                    self::prepaid_payments_parameters(),
                    [
                        'products' => $products
                    ]
                );
        $productsdata=unserialize(base64_decode($params['products']));
        $productsdata['paymenttype']='prepaid';
        // $productsdata['paymenttype']=$params['paymenttype'];

        $return = self::org_order_payments($productsdata);
       

        return $return;
    }

    public function prepaid_payments_returns(){

        return self::org_order_payments_returns();
    }
     /**
     * Returns description of method parameters.
     *
     * @return external_function_parameters.
     */
    public static function get_mypayments_parameters() {
        return new external_function_parameters([
                'contextid' => new external_value(PARAM_INT, 'The context id', false),
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
     * Gets the list of post_financialpayments for the given criteria. The post_financialpayments
     * will be exported in a summaries format and won't include all of the
     * post_financialpayments data.
     *
     * @param int $uerid Userid id to find post_financialpayments
     * @param int $contextid The context id where the post_financialpayments will be rendered
     * @param int $limit Maximum number of results to return
     * @param int $offset Number of items to skip from the beginning of the result set.
     * @return array The list of post_financialpayments and total competency count.
     */
    public static function get_mypayments(
        $contextid=false,
        $options=false,
        $dataoptions=false,
        $offset = 0,
        $limit = 0,
        $filterdata=false
    ) {
        global $OUTPUT, $CFG, $DB,$USER,$PAGE;
        $sitecontext = context_system::instance();
        require_login();
        $PAGE->set_url('/tool/product/financialpayments.php', array());
        $PAGE->set_context($sitecontext);
        // Parameter validation.
        $params = self::validate_parameters(
            self::get_mypayments_parameters(),
            [
                'contextid' => $contextid,    
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
        $stable->start = $offset;
        $stable->length = $limit;

        $renderer = $PAGE->get_renderer('tool_product');
        $data = array();
        if ($data_object->mode == 2 || $data_object->mode == 'paid') {
            $post_financialpayments=(new product)::get_my_financialpayments($stable,$filtervalues);
            $totalcount=$post_financialpayments['paymentscount'];
    
            $totalcost = ($post_financialpayments['totalcost']) ? (($renderer->is_decimal($post_financialpayments['totalcost'])) ? number_format($post_financialpayments['totalcost'],2) : number_format($post_financialpayments['totalcost'])) : 0;
            $stable->thead = false;
            $stable->start = $offset;
            $stable->length = $limit;
            if($totalcount > 0){
               /*if ($filtervalues->mode == 'refund') {
                } else {*/
                    $renderdata=$renderer->lis_my_financialpayments($stable,$filtervalues);
                //}
                $data = array_merge($data,$renderdata['data']);
            }
        } else if($data_object->mode == 4|| $data_object->mode == 'refund') {
            $renderdata = (new product)::get_my_refundpayments($stable,$filtervalues);
          
            $totalcost = ($renderdata['totalcost']) ? (($renderer->is_decimal($renderdata['totalcost'])) ? number_format($renderdata['totalcost'],2) : number_format($renderdata['totalcost'])) : 0;
            $totalcount = $renderdata['totalcount'];
            $data = array_merge($data,$renderdata['data']);
        } else {
            if(is_siteadmin() ) {
                $data=(new product)::get_my_cancelpayments($stable,$filtervalues);
                $totalcost = ($data['totalcost']) ? (($renderer->is_decimal($data['totalcost'])) ? number_format($data['totalcost'],2) : number_format($data['totalcost'])) : 0;
                $totalcount = $data['totalcount'];
                $data = $data['records'];
            }
        }
        $data = !empty($data) ? $data : [];
        $iscancelchecked =  $data_object->mode == 3 ?true:false;
        $isrefundchecked = $data_object->mode ==4?true:false;
        $ispurchasechecked =$data_object->mode==2?true:false;
       // var_dump($data_object->mode); exit;
        if ($data_object->mode == 2) {
            $mode = true;
        } else {
            $mode = false;
        }
        return [
            'totalcost' => $totalcost,
            'totalcount' => $totalcount,
            'records' => $data,
            'options' => $options,
            'dataoptions' => $dataoptions,
            'filterdata' => $filterdata,
            'nodata' => get_string('nopayments','tool_product'),
            'purchaseurl' => $CFG->wwwroot.'/admin/tool/product/financialpayments.php?mode=2',
            'cancelurl' => $CFG->wwwroot.'/admin/tool/product/financialpayments.php?mode=3',
            'refundurl' => $CFG->wwwroot.'/admin/tool/product/financialpayments.php?mode=4',
            'mode' => $mode,
            'iscancelchecked' => $iscancelchecked,
            'isrefundchecked' => $isrefundchecked,
            'ispurchasechecked' => $ispurchasechecked


        ];
    }

    /**
     * Returns description of method result value.
     */
    public static function  get_mypayments_returns() {

        $systemcontext = context_system::instance();

        if(!is_siteadmin() && has_capability('local/organization:manage_trainee',$systemcontext)) {
            return new external_single_structure([
                'options' => new external_value(PARAM_RAW, 'The paging data for the service', VALUE_OPTIONAL),
                'mode' => new external_value(PARAM_BOOL, 'The mode', VALUE_OPTIONAL),
                'dataoptions' => new external_value(PARAM_RAW, 'The data for the service', VALUE_OPTIONAL),
                'totalcost' => new external_value(PARAM_RAW, 'total cost of post_financialpayments in result set', VALUE_OPTIONAL),
                'totalcount' => new external_value(PARAM_RAW, 'total number of post_financialpayments in result set', VALUE_OPTIONAL),
                'filterdata' => new external_value(PARAM_RAW, 'The data for the service', VALUE_OPTIONAL),
                'nodata' => new external_value(PARAM_RAW, 'The data for the service', VALUE_OPTIONAL),
                'records' => new external_multiple_structure(
                    new external_single_structure(
                        array(
                            'id' => new external_value(PARAM_INT, 'payment id', VALUE_OPTIONAL),        
                            'trainingname' => new external_value(PARAM_RAW, 'training name', VALUE_OPTIONAL),
                            'taxes' => new external_value(PARAM_RAW, 'taxes', VALUE_OPTIONAL),
                            'discountprice' => new external_value(PARAM_RAW, 'discountprice', VALUE_OPTIONAL),
                            'originalprice' => new external_value(PARAM_RAW, 'originalprice', VALUE_OPTIONAL),
                            'finalprice' => new external_value(PARAM_RAW, 'finalprice', VALUE_OPTIONAL),
                            'paymentstatus'=> new external_value(PARAM_RAW, 'paymentstatus', VALUE_OPTIONAL),
                            'paymentdate' => new external_value(PARAM_RAW, 'paymentdate', VALUE_OPTIONAL),
                            'trainingstatus' => new external_value(PARAM_RAW, 'trainingstatus', VALUE_OPTIONAL),
                            'duedate' => new external_value(PARAM_RAW, 'training duedate', VALUE_OPTIONAL),
                            'entityid' => new external_value(PARAM_INT, 'entityid id', VALUE_OPTIONAL),
                            'referenceid' => new external_value(PARAM_INT, 'reference id', VALUE_OPTIONAL),
                            'name' => new external_value(PARAM_RAW, 'name', VALUE_OPTIONAL),
                            'refund' => new external_value(PARAM_BOOL, 'refund', VALUE_OPTIONAL),
                            'transcationref' => new external_value(PARAM_RAW, 'transcationref', VALUE_OPTIONAL),
                            'transcationnumber' => new external_value(PARAM_RAW, 'transcationnumber', VALUE_OPTIONAL),
                            'cisi_enrolment' => new external_value(PARAM_RAW, 'cisi_enrolment', VALUE_OPTIONAL),
                            'productname' => new external_value(PARAM_RAW, 'productname', VALUE_OPTIONAL),
                            'purchasedseats' => new external_value(PARAM_RAW, 'purchased seats', VALUE_OPTIONAL),
                            'invoicetype' => new external_value(PARAM_RAW, 'purchase type', VALUE_OPTIONAL),
                        )
                    )
                )
            ]);
        }else{

            return new external_single_structure([
                'options' => new external_value(PARAM_RAW, 'The paging data for the service'),
                'dataoptions' => new external_value(PARAM_RAW, 'The data for the service'),
                'mode' => new external_value(PARAM_BOOL, 'The mode', VALUE_OPTIONAL),
                'totalcost' => new external_value(PARAM_RAW, 'total cost of post_financialpayments in result set'),
                'totalcount' => new external_value(PARAM_INT, 'total number of post_financialpayments in result set'),
                'filterdata' => new external_value(PARAM_RAW, 'The data for the service'),
                'nodata' => new external_value(PARAM_RAW, 'The data for the service'),
                'purchaseurl' => new external_value(PARAM_RAW, 'The data for the service', VALUE_OPTIONAL),
                'cancelurl' => new external_value(PARAM_RAW, 'The data for the service', VALUE_OPTIONAL),
                'refundurl'  => new external_value(PARAM_RAW, 'The data for the service', VALUE_OPTIONAL),

                'iscancelchecked' => new external_value(PARAM_RAW, 'The data for the service', VALUE_OPTIONAL),
                'isrefundchecked'  => new external_value(PARAM_RAW, 'The data for the service', VALUE_OPTIONAL),
                'ispurchasechecked' => new external_value(PARAM_RAW, 'The data for the service', VALUE_OPTIONAL),
               
                'records' => new external_multiple_structure(
                    new external_single_structure(
                        array(
                            'id' => new external_value(PARAM_INT, 'payment id', VALUE_OPTIONAL),
                            'trainingname' => new external_value(PARAM_RAW, 'training name', VALUE_OPTIONAL),
                            'cost' => new external_value(PARAM_RAW, 'training cost', VALUE_OPTIONAL),
                            'costview' => new external_value(PARAM_RAW, 'Cost View', VALUE_OPTIONAL),
                            'organizationname' => new external_value(PARAM_RAW, 'training organization', VALUE_OPTIONAL),
                            'username'=> new external_value(PARAM_RAW, 'training username', VALUE_OPTIONAL),
                            'mode' => new external_value(PARAM_RAW, 'training payment mode', VALUE_OPTIONAL),
                            'duedate' => new external_value(PARAM_RAW, 'training duedate', VALUE_OPTIONAL),
                            'paymentsupdate' => new external_value(PARAM_RAW, 'training payments update', VALUE_OPTIONAL),
                            'purchasedseats' => new external_value(PARAM_INT, 'purchased seats', VALUE_OPTIONAL),
                            'usedseats' => new external_value(PARAM_RAW, 'used seats', VALUE_OPTIONAL),
                            'trainingstatus' => new external_value(PARAM_RAW, 'training status', VALUE_OPTIONAL),
                            'approvedseats' => new external_value(PARAM_INT, 'approved seats', VALUE_OPTIONAL),
                            'transcationref' => new external_value(PARAM_RAW, 'transcationref', VALUE_OPTIONAL),
                            'transcationnumber' => new external_value(PARAM_RAW, 'transcationnumber', VALUE_OPTIONAL), 
                            'cisi_enrolment' => new external_value(PARAM_RAW, 'cisi_enrolment', VALUE_OPTIONAL), 
                            'refund' => new external_value(PARAM_BOOL, 'refund', VALUE_OPTIONAL),
                            'transcationref' => new external_value(PARAM_RAW, 'transcationref', VALUE_OPTIONAL),
                            'transcationnumber' => new external_value(PARAM_RAW, 'transcationnumber', VALUE_OPTIONAL),
                            'paymentstatus'=> new external_value(PARAM_RAW, 'paymentstatus', VALUE_OPTIONAL),
                            'discountprice' => new external_value(PARAM_RAW, 'discountprice', VALUE_OPTIONAL),
                            'paymentdate' => new external_value(PARAM_RAW, 'paymentdate', VALUE_OPTIONAL),
                            'cisi_enrolment' => new external_value(PARAM_RAW, 'cisi_enrolment', VALUE_OPTIONAL),
                            'productname' => new external_value(PARAM_RAW, 'productname', VALUE_OPTIONAL),
                            'invoicetype' => new external_value(PARAM_RAW, 'purchase type', VALUE_OPTIONAL),
                            'orderinfo' => new external_value(PARAM_RAW, 'orderinfo', VALUE_OPTIONAL),
                            'invoiceaction' => new external_value(PARAM_BOOL, 'invoiceaction', VALUE_OPTIONAL),
                            'pname' => new external_value(PARAM_RAW, 'pname', VALUE_OPTIONAL),
                            'productid' => new external_value(PARAM_INT, 'productid', VALUE_OPTIONAL),
                            'trainingagreement' => new external_value(PARAM_RAW, 'trainingagreement', VALUE_OPTIONAL),
                            'tagree' => new external_value(PARAM_RAW, 'tagree', VALUE_OPTIONAL),
                            'tagreement' => new external_value(PARAM_RAW, 'tagreement', VALUE_OPTIONAL),
                        )
                    )
                )
            ]);
        }
    }
    public static function sendemailtoorgofficial_parameters(){
        return new external_function_parameters(
            array(
                'productid' => new external_value(PARAM_INT,'Product id',0),
            )
        );
    }
    public static  function sendemailtoorgofficial($productid){
        global $CFG;
        global $USER;
        global $DB;
        $params=self::validate_parameters(
            self::sendemailtoorgofficial_parameters(),
            array('productid'=>$productid)
        );
        $systemcontext = context_system::instance();
        self::validate_context($systemcontext);
        if ($productid) {
            $is_record_exists = $DB->record_exists('tool_org_order_payments', array('id'=>$productid));
            if($is_record_exists) {

                $record = $DB->get_record('tool_org_order_payments', array('id'=>$productid));
                   (new tool_product\product)::sendemail_to_orgofficial($record);
             } else {

                throw new moodle_exception('Product id is not valid.');
             }
        } else {
          throw new moodle_exception('Product id can not be empty.');
        }
        return true;    
    }
    public static function sendemailtoorgofficial_returns() {
        return new external_value(PARAM_BOOL, 'return');
    }

    public function get_mywallet_parameters() {
        return new external_function_parameters([
             'contextid' => new external_value(PARAM_INT, 'The context id',
                VALUE_DEFAULT, 1),
            'options' => new external_value(PARAM_RAW, 'The paging data for the service', VALUE_OPTIONAL),
            'dataoptions' => new external_value(PARAM_RAW, 'The data for the service', VALUE_OPTIONAL),
            'offset' => new external_value(PARAM_INT, 'Number of items',
                VALUE_DEFAULT, 0),
            'limit' => new external_value(PARAM_INT, 'Maximum number of results to return',
                VALUE_DEFAULT, 0),
            'filterdata' => new external_value(PARAM_RAW, 'filters applied', VALUE_OPTIONAL),
        ]);
    }

    public function get_mywallet($contextid = null, $options = null,
    $dataoptions = null,
    $offset = 0,
    $limit = 0,
    $filterdata = null
    ) {
    global $DB, $PAGE;
    // Parameter validation.
    $sitecontext = context_system::instance();
    self::validate_context($sitecontext);
    $params = self::validate_parameters(
        self::get_mywallet_parameters(),
        [
            'contextid' => $contextid,
            'options' => $options,
            'dataoptions' => $dataoptions,
            'offset' => $offset,
            'limit' => $limit,
            'filterdata' => $filterdata,
        ]
    );
  
    $data_object = (json_decode($dataoptions));
    $offset = $params['offset'];
    $limit = $params['limit'];
    $filtervalues = json_decode($filterdata);

    $stable = new \stdClass();
    $stable->thead = false;
    $stable->start = $offset;
    $stable->length = $limit;
    $data = (new product)::get_mywallet($stable,$filtervalues);
    $totalcount = $data['totalcount'];
    return [
        'totalcount' => $totalcount,
        'records' => $data,
        'options' => $options,
        'length' => $totalcount,
        'dataoptions' => $dataoptions,
        'filterdata' => $filterdata,
        //'nodata' => $nodata,
        'nodatatext' => get_string('nopayments','tool_product')
    ];
    }

    public function get_mywallet_returns() {
        return new external_single_structure([
            'options' => new external_value(PARAM_RAW, 'The paging data for the service'),
            'dataoptions' => new external_value(PARAM_RAW, 'The data for the service'),
            'totalcount' => new external_value(PARAM_INT, 'total number of challenges in result set'),
            'filterdata' => new external_value(PARAM_RAW, 'total number of challenges in result set'),
            'length' => new external_value(PARAM_RAW, 'total number of challenges in result set'),
            'nodatatext' => new external_value(PARAM_RAW, 'The data for the service'),
            'records' => new external_single_structure(
                    array(
                        'haswalletdata' => new external_multiple_structure(
                            new external_single_structure(
                                array(
                                    'id' => new external_value(PARAM_INT, 'id'),
                                    'addedwallet'=> new external_value(PARAM_RAW, 'addedwallet'),
                                    'timecreated'=> new external_value(PARAM_RAW, 'timecreated'),
                                    'paymentdate'=> new external_value(PARAM_RAW, 'paymentdate'),
                                )
                            )
                        ),
                        'request_view' => new external_value(PARAM_INT, 'request_view', VALUE_OPTIONAL),
                        'nodata' => new external_value(PARAM_BOOL, 'nocourses', VALUE_OPTIONAL),
                        'totalcount' => new external_value(PARAM_INT, 'totalhalls', VALUE_OPTIONAL),
                        'length' => new external_value(PARAM_INT, 'length', VALUE_OPTIONAL),
                    )
                )
        ]);
    }

     /**
     * Returns description of method parameters.
     *
     * @return external_function_parameters.
     */
    public static function get_orgpayments_parameters() {
        return new external_function_parameters([
            'contextid' => new external_value(PARAM_INT, 'Maximum number of results to return',
                VALUE_DEFAULT, 1),
            'options' => new external_value(PARAM_RAW, 'The paging data for the service', VALUE_OPTIONAL),
            'dataoptions' => new external_value(PARAM_RAW, 'The data for the service', VALUE_OPTIONAL),

            'offset' => new external_value(PARAM_INT, 'Number of items',
                VALUE_DEFAULT, 0),
            'limit' => new external_value(PARAM_INT, 'Maximum number of results to return',
                VALUE_DEFAULT, 0),
            'filterdata' => new external_value(PARAM_RAW, 'The data for the service', VALUE_OPTIONAL)
        ]);
    }
    public static function get_orgpayments($contextid=false,$options=false, $dataoptions=false, $offset = 0, $limit = 0, $filterdata=false) {
        global $OUTPUT, $CFG, $DB,$USER,$PAGE;
        $sitecontext = context_system::instance();
        require_login();
        $PAGE->set_url('/tool/product/mypayments.php', array());
        $PAGE->set_context($sitecontext);
        // Parameter validation.
        self::validate_context($sitecontext);
        $params = self::validate_parameters(
            self::get_orgpayments_parameters(),
            [
                'contextid' => $contextid,
                'options' => $options,
                'dataoptions' => $dataoptions,
                'offset' => $offset,
                'limit' => $limit,
                'filterdata' => $filterdata,
            ]
        );
        $data_object = (json_decode($dataoptions));
        $offset = $params['offset'];
        $limit = $params['limit'];
        $filtervalues = json_decode($filterdata);
        $stable = new \stdClass();
        // $stable->thead = false;
        // // $payments = (new tool_product\product)->get_listof_orgpayments($stable,$filtervalues);
        // $payments = (new tool_product\product)->get_orgpayments($stable,$filtervalues);
        // $totalcount = $payments['paymentscount'];
        $stable->thead = false;
        $stable->start = $offset;
        $stable->length = $limit;
        // $data = array();

        // if($totalcount>0){
        //     $renderer = $PAGE->get_renderer('tool_product');
        //     $data = array_merge($data,$renderer->list_orgpayments($stable,$filterdata));
        // }

        $data = (new tool_product\product)->get_orgpayments($stable,$filtervalues);
        $totalcount = $data['totalcount'];
        if($data) {
            $nodata = false;
        } else {
            $nodata = true;
        }
        return [
            'totalcount' => $totalcount,
            'records' => $data['data'],
            'options' => $options,
            'dataoptions' => $dataoptions,
            'filterdata' => $filterdata,
            'nodata' => $nodata,
            'nodatatext' => get_string('nopayments','tool_product')
        ];
    }

    public static function get_orgpayments_returns() {
        return new external_single_structure([
            'options' => new external_value(PARAM_RAW, 'The paging data for the service', VALUE_OPTIONAL),
            'dataoptions' => new external_value(PARAM_RAW, 'The data for the service', VALUE_OPTIONAL),
            'totalcount' => new external_value(PARAM_INT, 'total number of post_financialpayments in result set', VALUE_OPTIONAL),
            'filterdata' => new external_value(PARAM_RAW, 'The data for the service', VALUE_OPTIONAL),
            'nodata' => new external_value(PARAM_BOOL, 'The data for the service', VALUE_OPTIONAL),
            'nodatatext' => new external_value(PARAM_RAW, 'The data for the service', VALUE_OPTIONAL),
            'records' => new external_multiple_structure(
                            new external_single_structure(
                                array(
                                    'id' => new external_value(PARAM_INT, 'paymentid', VALUE_OPTIONAL),
                                    'trainingname' => new external_value(PARAM_RAW, 'trainingname', VALUE_OPTIONAL),
                                    'total_price' => new external_value(PARAM_RAW, 'total_price', VALUE_OPTIONAL),
                                    'discount_price' => new external_value(PARAM_RAW, 'discount_price', VALUE_OPTIONAL),
                                    'taxes' => new external_value(PARAM_RAW, 'taxes', VALUE_OPTIONAL),
                                    'total' => new external_value(PARAM_RAW, 'total', VALUE_OPTIONAL),
                                    //'total_purchases' => new external_value(PARAM_INT, 'total_purchases'),
                                    'timecreated' => new external_value(PARAM_RAW, 'timeupdated', VALUE_OPTIONAL),
                                    'paymentdate' => new external_value(PARAM_INT, 'paymentdate', VALUE_OPTIONAL),
                                    //'quantity' => new external_value(PARAM_RAW, 'quantity'),
                                    'approvalseats'  => new external_value(PARAM_RAW, 'approvalseats', VALUE_OPTIONAL),
                                    'purchasedseats'  => new external_value(PARAM_RAW, 'purchasedseats', VALUE_OPTIONAL),
                                    'statuscode' => new external_value(PARAM_RAW, 'purchased seats', VALUE_OPTIONAL),
                                    'paymentmethod' => new external_value(PARAM_RAW, 'paymentmethod', VALUE_OPTIONAL),
                                    'invoicetype' => new external_value(PARAM_RAW, 'invoicetype', VALUE_OPTIONAL),
                                    'cost' => new external_value(PARAM_RAW, 'cost', VALUE_OPTIONAL),
                                    'transactionid' => new external_value(PARAM_RAW, 'transactionid', VALUE_OPTIONAL),
                                    'status' => new external_value(PARAM_RAW, 'status', VALUE_OPTIONAL),
                                    'orgoffcialname' => new external_value(PARAM_RAW, 'orgoffcialname', VALUE_OPTIONAL),
                                    'invoicestatuscode' => new external_value(PARAM_RAW, 'invoicestatuscode', VALUE_OPTIONAL),

                                    )
                            )
            )
        ]);
    }
    public static function user_order_payments_parameters(){
           return new external_function_parameters(
            array(
                'products' =>new external_single_structure(
                                array(
                                    'paymenttype'=> new external_value(PARAM_TEXT, 'paymenttype'),
                                    'total_purchases' => new external_value(PARAM_INT, 'total_purchases'),
                                    'total_price' => new external_value(PARAM_FLOAT, 'total_price'),
                                    'discount_price' => new external_value(PARAM_FLOAT, 'discount_price'),
                                    'total_discount' => new external_value(PARAM_FLOAT, 'total_discount'),
                                    'category' => new external_value(PARAM_TEXT, 'category', VALUE_OPTIONAL),
                                    'total' => new external_value(PARAM_FLOAT, 'total_purchases'),
                                    'taxes' => new external_value(PARAM_FLOAT, 'taxes'),
                                    'tax_slab' => new external_value(PARAM_INT, 'tax_slab'),
                                    'items' => new external_multiple_structure(
                                        new external_single_structure(
                                            array(
                                                'product_id'=> new external_value(PARAM_INT, 'product_id'),
                                                'name'      => new external_value(PARAM_TEXT, 'name'),
                                                'quantity'  => new external_value(PARAM_INT, 'quantity'),
                                                'total'     =>  new external_value(PARAM_FLOAT, 'total'),
                                                'early_registration_discount' => new external_value(PARAM_INT, 'early_registration_discount',VALUE_OPTIONAL),
                                                'early_registration_discount_amount' => new external_value(PARAM_RAW, 'early_registration_discount_amount',VALUE_OPTIONAL),
                                                'discount_type' => new external_value(PARAM_RAW, 'discount_type',VALUE_OPTIONAL),
                                                'couponcode_discount_amount' => new external_value(PARAM_RAW, 'couponcode_discount_amount',VALUE_OPTIONAL),
                                                'couponid'=>  new external_value(PARAM_INT, 'product couponid',VALUE_OPTIONAL),
                                                'couponcode'=>  new external_value(PARAM_RAW, 'product couponcode',VALUE_OPTIONAL),
                                                'couponvalid' => new external_value(PARAM_BOOL, 'couponvalid',VALUE_OPTIONAL),
                                                'couponmsg' => new external_value(PARAM_RAW, 'couponvalid',VALUE_OPTIONAL),
                                                'groups_organization_discount' => new external_value(PARAM_INT, 'groups_organization_discount',VALUE_OPTIONAL),
                                                'groups_organization_discount_amount' => new external_value(PARAM_RAW, 'groups_organization_discount_amount',VALUE_OPTIONAL),
                                                'groups_organization_discount_view' => new external_value(PARAM_BOOL, 'groups_organization_discount_view',VALUE_OPTIONAL),
                                                'couponactionview' => new external_value(PARAM_RAW, 'couponactionview',VALUE_OPTIONAL),
                                                'category' =>  new external_value(PARAM_INT, 'product category'),
                                            )
                                        )
                                    ),
                                    'payment_methods' =>    new external_multiple_structure(
                                        new external_single_structure(
                                            array(
                                                'slug'  =>  new external_value(PARAM_TEXT, 'slug'),
                                                'name'  =>  new external_value(PARAM_TEXT, 'name'),
                                            )
                                        )
                                    ),
                                    'formdata' =>new external_single_structure(
                                        array(
                                            'tablename'=> new external_value(PARAM_RAW, 'tablename',VALUE_OPTIONAL),
                                            'fieldname'=> new external_value(PARAM_RAW, 'fieldname',VALUE_OPTIONAL),
                                            'fieldid' => new external_value(PARAM_INT, 'fieldid',VALUE_OPTIONAL),
                                            'selectedseats' => new external_value(PARAM_INT, 'selectedseats',VALUE_OPTIONAL),
                                            'sesskey' => new external_value(PARAM_RAW, 'total_discount',VALUE_OPTIONAL),
                                            'parentfieldid' => new external_value(PARAM_INT, 'parentfieldid',VALUE_OPTIONAL),
                                        ), 'formdata',VALUE_OPTIONAL
                                    )  
                                )
                            )
                
                )
            );

    }

    public static function user_order_payments($products){

        global $OUTPUT, $CFG, $DB,$USER,$PAGE;
        // Parameter validation.

        $coupondiscounfailed=$seatexceed=$return=false;

        $products=(object)$products;


        $formdata=(object)$products->formdata;

        $productitems=(object)$products->items;

        foreach($productitems as $key =>$product){


            if($product['couponid'] > 0 && $product['discount_type']== 'coupon' && !is_siteadmin()){

                $couponinfo = (new product)->get_couponinfo($product['couponcode'],$product['couponid']);


                if($couponinfo){

                    $coupon = (new product)->coupon_validationcheck($couponinfo,$products->userid);

                    if($coupon['couponvalid'] == false){

                        return array(
                            'seatexceed'   => false,
                            'success'   => false,
                            'paymenttype' => 'nopaymenttype',
                            'coupondiscounfailed'=>true
                        );

                    }

                }
            }
            // $availableseats=(new \tool_product\product)->seatexceed_check($formdata->tablename, $formdata->fieldname, $formdata->fieldid);

            // if($availableseats){

            //     $purchasedseats=(new \tool_product\product)->purchasedseats_check($formdata->tablename, $formdata->fieldname, $formdata->fieldid);

            //     if($purchasedseats){

            //         $currentavailableseats=$availableseats-$purchasedseats;

            //         if($product->quantity > $currentavailableseats){

            //             $seatexceed=true;

            //             break;

            //         }

            //     }
            // }else{
            //     $seatexceed=true;
            // }


            $product=(object)$product;

            $tabledata=new \stdClass();

            $tabledata->productid=$product->product_id;

            $category=$product->category;

            switch ($category) {

                case 1:

                $tabledata->tablename='tp_offerings';

                break;

                case 2:

                $tabledata->tablename='local_exam_profiles';

                break;

                case 3:

                $tabledata->tablename='local_events';

                break;
                case 4:

                $tabledata->tablename='local_exam_grievance';
                break;
                case 5:
                $tabledata->tablename='local_learningtracks';
                break;
                case 6:
                $tabledata->tablename='local_exam_attempts';
                break;                
            }

            $tabledata->fieldname='id';

            $tabledata->fieldid=$DB->get_field('tool_products','referenceid',  array('id'=>$tabledata->productid));

            $tabledata->userid=$products->userid;
            $tabledata->paymenttype=$products->paymenttype;
            $tabledata->paymenton=time();
            $tabledata->amount = $product->item_total_price; //$products->total;
            $tabledata->quantity=$product->quantity;
            $tabledata->discountprice = $product->item_discount_price;//$products->total_discount;
            $tabledata->telrid = $products->telrid;
            $tabledata->taxes = $product->item_tax;
            if($product->processtype == 'reschedule') {
                $tabledata->processtype=$product->processtype;
                $tabledata->purchasedseats=0;
                $tabledata->originalprice=$products->total;
                $tabledata->amount = $products->total;
                $tabledata->discountprice = 0;
                $tabledata->taxes = 0;
            } else {
                $tabledata->originalprice = $product->total;//$products->total_price;
                $tabledata->purchasedseats = $product->quantity;
            }
            if($products->timeupdated){
                $tabledata->timeupdated=$products->timeupdated;
            }

            $return = (new product)->insert_update_user_order_payments($tabledata,$product);

        }

        $paymenttype=$products->paymenttype ? $products->paymenttype : 'nopaymenttype';

        return array(
            'seatexceed'   => $seatexceed,
            'success'   => $return,
            $paymenttype => $paymenttype,
            'coupondiscounfailed'=>$coupondiscounfailed
        );
    }

    public static function user_order_payments_returns(){
       return new external_single_structure(
            array(
                'seatexceed'=> new external_value(PARAM_RAW, 'seatexceed'),
                'success'  => new external_value(PARAM_RAW, 'success'),
                'postpaid'  => new external_value(PARAM_RAW, 'postpaid',VALUE_OPTIONAL),
                'prepaid'  => new external_value(PARAM_RAW, 'prepaid',VALUE_OPTIONAL),
                'telr'  => new external_value(PARAM_RAW, 'telr',VALUE_OPTIONAL),
                'nopaymenttype'  => new external_value(PARAM_RAW, 'nopaymenttype',VALUE_OPTIONAL),
                'coupondiscounfailed'=> new external_value(PARAM_RAW, 'coupondiscounfailed'),
            )
        );
    }


    public static function getproductinfo_parameters() {
        return new external_function_parameters([
            'recordid' => new external_value(PARAM_INT, 'recordid',VALUE_DEFAULT, 0),
            'fieldid' => new external_value(PARAM_INT, 'fieldid',VALUE_DEFAULT, 0),
            'category' => new external_value(PARAM_INT, 'category',VALUE_DEFAULT, 0),
            'quantity' => new external_value(PARAM_INT, 'quantity',VALUE_DEFAULT, 0),
        ]);
    }
    public static function getproductinfo($recordid,$fieldid,$category,$quantity) {
        global $DB, $PAGE, $CFG;
        require_login();
        $params = self::validate_parameters(
            self::getproductinfo_parameters(),
            [
                'recordid' => $recordid,
                'fieldid' => $fieldid,
                'category' => $category,
                'quantity' => $quantity,
            ]
        );
        $data =(new product)->get_product_info($recordid,$fieldid,$category,$quantity);
       if($data) {
           return $data;
       } else {
           return null;
       }
    }
    public static function getproductinfo_returns() {
        return new external_single_structure(
            array(
                'productid' => new external_value(PARAM_INT, 'productid'),
                'name' => new external_value(PARAM_TEXT, 'name'),
                'code' => new external_value(PARAM_TEXT, 'code'),
                'tablename' => new external_value(PARAM_TEXT, 'tablename'),
                'fieldname' => new external_value(PARAM_TEXT, 'fieldname'),
                'fieldid' => new external_value(PARAM_INT, 'fieldid'),
                'parentfieldid' => new external_value(PARAM_INT, 'parentfieldid'),
                'quantity' => new external_value(PARAM_INT, 'quantity'),
            )

        );
    }

    public static function ordersummary_parameters() {
        return new external_function_parameters([
            'id' => new external_value(PARAM_INT, 'id'),
        ]);
    }
    public static function ordersummary($id) {
        global $DB, $PAGE, $CFG;
        require_login();
        $params = self::validate_parameters(
            self::ordersummary_parameters(),
            [
                'id' => $id,
            ]
        );
        $data =(new product)->get_ordersummary_data($id);
       if($data) {
           return $data;
       } else {
           return null;
       }
    }
    public static function ordersummary_returns() {
        return new external_single_structure(
            array(
                'items' => new external_multiple_structure(
                    new external_single_structure(
                        array(
                        'product_id' => new external_value(PARAM_INT, 'product_id'),
                        'name' => new external_value(PARAM_RAW, 'name'),
                        'quantity' => new external_value(PARAM_INT, 'quantity'),
                        'is_enrolled' => new external_value(PARAM_RAW, 'is_enrolled'),
                        'total' => new external_value(PARAM_RAW, 'total'),
                        'category' => new external_value(PARAM_INT, 'category'),
                        'grouped' => new external_value(PARAM_RAW, 'grouped'),
                        'language' => new external_value(PARAM_RAW, 'language'),
                        'early_registration_discount' => new external_value(PARAM_RAW, 'early_registration_discount'),
                        'early_registration_discount_amount' => new external_value(PARAM_RAW, 'early_registration_discount_amount'),
                        'discount_type' => new external_value(PARAM_RAW, 'discount_type'),
                        'couponactionview' => new external_value(PARAM_RAW, 'couponactionview'),
                        'couponid' => new external_value(PARAM_INT, 'couponid'),
                        'couponcode' => new external_value(PARAM_RAW, 'couponcode'),
                        'couponcode_discount_amount' => new external_value(PARAM_RAW, 'couponcode_discount_amount'),
                        'groups_organization_discount' => new external_value(PARAM_RAW, 'groups_organization_discount'),
                        'groups_organization_discount_amount' => new external_value(PARAM_RAW, 'groups_organization_discount_amount'),
                        )
                    )
                ), '', VALUE_OPTIONAL,
                'total_price' => new external_value(PARAM_RAW, 'total_price'),
                'discount_price' => new external_value(PARAM_RAW, 'discount_price'),
                'total_discount' => new external_value(PARAM_RAW, 'total_discount'),
                'total' => new external_value(PARAM_RAW, 'total'),
                'taxes' => new external_value(PARAM_RAW, 'taxes'),
                'taxdisplay' => new external_value(PARAM_RAW, 'taxdisplay'),
                'tax_slab' => new external_value(PARAM_RAW, 'tax_slab'),
                'total_purchases' => new external_value(PARAM_RAW, 'total_purchases'),
                'payment_methods' => new external_multiple_structure(
                    new external_single_structure(
                        array(
                        'slug' => new external_value(PARAM_RAW, 'product_id'),
                        'name' => new external_value(PARAM_RAW, 'name'),
                        )
                    )
                ), '', VALUE_OPTIONAL,
                'category' => new external_value(PARAM_RAW, 'category'),
            )

        );
    }

    public static function trainee_walletamount_parameters() {
        return new external_function_parameters([
                'walletid' => new external_value(PARAM_INT, 'walletid'),
                'walletamount' => new external_value(PARAM_FLOAT, 'walletamount'),
                'deductedamount' => new external_value(PARAM_INT, 'deductedamount'),
        ]);
    }

    public static function trainee_walletamount($walletid, $walletamount, $deductedamount) {
        global $OUTPUT, $CFG, $DB,$USER,$PAGE;
        $sitecontext = context_system::instance();
        require_login();
        $PAGE->set_url('/tool/product/financialpayments.php', array());
        $PAGE->set_context($sitecontext);
        // Parameter validation.
        $params = self::validate_parameters(
            self::trainee_walletamount_parameters(),
            [
                'walletid' => $walletid,
                'walletamount' => $walletamount,
                'deductedamount' => $deductedamount
            ]
        );

        $status = (new tool_product\traineewallet)::trainee_walletamount($params);

        return $status;
    }

    /**
     * Returns description of method result value.
     */
    public static function  trainee_walletamount_returns() {
        return new external_value(PARAM_BOOL, 'return');
    }

    public static function rejectorder_parameters(){
        return new external_function_parameters(
            array(
                'orderid' => new external_value(PARAM_INT, 'orderid', 0)
            )
        );

    }  
    public static function rejectorder($orderid){
        global $DB,$USER;
        $params = self::validate_parameters(self::rejectorder_parameters(),
                                    ['orderid' => $orderid]);
        $context = context_system::instance();
        if($orderid){
            //$paymentid = $DB->get_field('tool_order_approval_seats', 'paymentid', ['id'=>$orderid]);
            $order = $DB->get_record('tool_order_approval_seats', ['id'=>$orderid]);
            if($order->tablename =='local_exam_profiles') {
                $records = $DB->get_records_sql('SELECT * FROM {local_exam_userhallschedules} WHERE orderid ='.$orderid.'');
                foreach($records AS $record) {
                    $response =(new exams)->cancel_fastapi($record->productid,$record->userid,$record->hallscheduleid);
                    if($response =='success') {
                        (new product)->delete_rejected_erollments($order);
                        $realuser = ($USER->realuser) ? $USER->realuser :0;
                        $DB->update_record('tool_org_order_payments', ['id' => $order->paymentid, 'orderstatus' => 2,'realuser'=>$realuser]);
                    }
                    
                }
                $returndata['response'] =$response; 
            } else {
                (new product)->delete_rejected_erollments($order);
                $realuser = ($USER->realuser) ? $USER->realuser :0;
                $response = $DB->update_record('tool_org_order_payments', ['id' => $order->paymentid, 'orderstatus' => 2,'realuser'=>$realuser]);
                $returndata['response'] = ($response) ? 'success' : 'failed';
            }
        } else {
            throw new moodle_exception('Error in deleting');
        }
        return $returndata;
    }   
    public static function rejectorder_returns() {
        return new external_single_structure(
            array(
                'response' => new external_value(PARAM_RAW, 'response'),
            )
        );
    }

    public static function get_orderinfo_parameters(){
        return new external_function_parameters(
            array(
                'orderid' => new external_value(PARAM_INT, 'orderid', 0)
            )
        );

    }  
    public static function get_orderinfo($orderid){
        global $DB;
        $params = self::validate_parameters(self::get_orderinfo_parameters(),
                                    ['orderid' => $orderid]);
        $context = context_system::instance();
        $data =(new product)->get_orgoff_orderdata($orderid);
        return ['info' => $data['productinfo'], 'seatsinfo' =>  $data['seatsinfo']];
    }   
    public static function get_orderinfo_returns() {
        return new external_single_structure(
            array(
                'info' => new external_value(PARAM_RAW, 'info'),
                'seatsinfo' => new external_value(PARAM_RAW, 'info'),
            )
        );
    }
}
