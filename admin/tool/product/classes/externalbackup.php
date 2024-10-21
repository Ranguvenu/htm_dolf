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
                            'grouped' => new external_value(PARAM_INT, 'grouped product',VALUE_OPTIONAL)
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


            $product = (new product)->format_product( $product );

            $product['quantity'] = $value['quantity'];
            $product['product'] =  $value['product'];
            $product['grouped'] = $value['grouped'];
            $product['hasvariations'] = $value['hasvariations'];
            
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

            $product = (new product)->format_product($product);

            $product['quantity'] = $value['quantity'];
            $product['grouped'] = $value['grouped'];
            
            $product['roles'] = $roles;


            $return['products'][] = $product;
        }
        $summary = (new product)->get_order_summary( $return['products']);

        $summary['payment_methods'] = (new product)->get_payment_methods();

        $summary['formdata'] = $formdata; 
        $summary['category'] = 'booking';
        $summary['productdata'] =  base64_encode(serialize($summary));

        return $summary;
    }

    public static function checkout_summary_returns(){
        return new external_single_structure(
            array(
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
               
                'items' => new external_multiple_structure(
                    new external_single_structure(
                        array(
                            'product_id'=> new external_value(PARAM_INT, 'product_id'),
                            'name'      => new external_value(PARAM_TEXT, 'name'),
                            'quantity'  => new external_value(PARAM_INT, 'quantity'),
                            'total'     =>  new external_value(PARAM_FLOAT, 'total'),
                            'is_enrolled'     =>  new external_value(PARAM_BOOL, 'is_enrolled'),
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
                            'category'      =>  new external_value(PARAM_INT, 'product category',VALUE_OPTIONAL),
                            'roles' => new external_value(PARAM_BOOL, 'roles',VALUE_OPTIONAL), 
                            'grouped' => new external_value(PARAM_INT, 'product category',VALUE_OPTIONAL),
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

            $tabledata->tablename=$formdata->tablename;
            $tabledata->fieldname=$formdata->fieldname;
            $tabledata->fieldid=$formdata->fieldid;
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

                    if(!$checkpoint){

                        $return=false;

                        continue ;
                    }

                }

                $tabledata->paymentapprovalstatus=1;
                $tabledata->approvaluserid=$USER->id;
                $tabledata->approvalon=time();

            }

            $return = (new product)->insert_update_org_order_payments($tabledata,$product);

        }

        $paymenttype=$products->paymenttype ? $products->paymenttype : 'nopaymenttype';

        return array(
            'seatexceed'   => $seatexceed,
            'success'   => $return,
            $paymenttype => $paymenttype,
            'coupondiscounfailed'=>$coupondiscounfailed
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
    public static function get_post_financialpayments(
        $options,
        $dataoptions,
        $offset = 0,
        $limit = 0,
        $filterdata
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

        $stable = new \stdClass();
        $stable->thead = true;
        $post_financialpayments=(new product)::get_post_financialpayments($stable,$filtervalues);
        $totalcount=$post_financialpayments['paymentscount'];
        $stable->thead = false;
        $stable->start = $offset;
        $stable->length = $limit;

        $data = array();

        if($totalcount>0){

            $renderer = $PAGE->get_renderer('tool_product');

            $renderdata=$renderer->lis_post_financialpayments($stable,$filtervalues);

            $data = array_merge($data,$renderdata['data']);
        }
        return [
            'totalcost' => number_format($post_financialpayments['totalcost']),
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
    public static function  get_post_financialpayments_returns() {
        return new external_single_structure([
            'options' => new external_value(PARAM_RAW, 'The paging data for the service'),
            'dataoptions' => new external_value(PARAM_RAW, 'The data for the service'),
            'totalcost' => new external_value(PARAM_RAW, 'total cost of post_financialpayments in result set'),
            'totalcount' => new external_value(PARAM_INT, 'total number of post_financialpayments in result set'),
            'filterdata' => new external_value(PARAM_RAW, 'The data for the service'),
            'nodata' => new external_value(PARAM_RAW, 'The data for the service'),
            'records' => new external_multiple_structure(
                            new external_single_structure(
                                array(
                                    'id' => new external_value(PARAM_INT, 'payment id'),
                                    'sendemailurl' => new external_value(PARAM_RAW, 'sendemailurl'),
                                    'trainingname' => new external_value(PARAM_RAW, 'training name'),
                                    'cost' => new external_value(PARAM_RAW, 'training cost'),
                                    'costview' => new external_value(PARAM_RAW, 'Cost View'),
                                    'sendemailactionview' => new external_value(PARAM_RAW, 'sendemailactionview'),
                                    'organizationname' => new external_value(PARAM_RAW, 'training organization'),
                                    'orgoffcialname'=> new external_value(PARAM_RAW, 'training orgoffcial'),
                                    'mode' => new external_value(PARAM_RAW, 'training payment mode'),
                                    'duedate' => new external_value(PARAM_RAW, 'training duedate'),
                                    'paymentsupdate' => new external_value(PARAM_RAW, 'training payments update'),
                                    'purchasedseats' => new external_value(PARAM_INT, 'purchased seats'),
                                    'startdate' => new external_value(PARAM_RAW, 'training startdate'),
                                    'enddate' => new external_value(PARAM_RAW, 'training enddate'),
                                    'usedseats' => new external_value(PARAM_RAW, 'used seats'),
                                    'trainingstatus' => new external_value(PARAM_RAW, 'training status'),
                                    'approvedseats' => new external_value(PARAM_INT, 'approved seats'),                               
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
                                    'approvalsupdate' => new external_value(PARAM_RAW, 'training approvals update'),
                                    'purchasedseats' => new external_value(PARAM_INT, 'purchased seats'),
                                    'approvalseats' => new external_value(PARAM_INT, 'approval seats')

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
                'options' => new external_value(PARAM_RAW, 'The paging data for the service'),
                'dataoptions' => new external_value(PARAM_RAW, 'The data for the service'),
                'offset' => new external_value(PARAM_INT, 'Number of items to skip from the begging of the result set',
                    VALUE_DEFAULT, 0),
                'limit' => new external_value(PARAM_INT, 'Maximum number of results to return',
                    VALUE_DEFAULT, 0),
                 'filterdata' => new external_value(PARAM_RAW, 'The data for the service'),
                 'contextid' => new external_value(PARAM_INT, 'Maximum number of results to return',
                VALUE_DEFAULT, 1)
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
        $options,
        $dataoptions,
        $offset = 0,
        $limit = 0,
        $filterdata
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
                'filterdata' => $filterdata
            ]
                );
        $data_object = (json_decode($dataoptions));
        $offset = $params['offset'];
        $limit = $params['limit'];
        $filtervalues = json_decode($filterdata);

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
            'nodata' => get_string('nopurchases','tool_product')
        ];
    }

    /**
     * Returns description of method result value.
     */
    public static function  viewpurchases_returns() {
        return new external_single_structure([
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
                                    'purchasedseats' => new external_value(PARAM_INT, 'purchased seats'),
                                    'approvalseats' => new external_value(PARAM_INT, 'approval seats'),
                                    'availableseats' => new external_value(PARAM_INT, 'available seats'),
                                    'usedseats' => new external_value(PARAM_RAW, 'used seats'),
                                    'enrollbtn' => new external_value(PARAM_RAW, 'training enrollbtn'),
                                    'timelimit' => new external_value(PARAM_RAW, 'Time Limit'),
                                    'offeringview' => new external_value(PARAM_RAW, 'offeringview'),
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
                    ]
                );
        $return['returnurl'] = (new tool_product\orders)->begin_transaciton($params);
       

        return $return;
    }

    public function telr_begin_trans_returns(){
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
                'options' => new external_value(PARAM_RAW, 'The paging data for the service'),
                'dataoptions' => new external_value(PARAM_RAW, 'The data for the service'),
                'offset' => new external_value(PARAM_INT, 'Number of items to skip from the begging of the result set',
                    VALUE_DEFAULT, 0),
                'limit' => new external_value(PARAM_INT, 'Maximum number of results to return',
                    VALUE_DEFAULT, 0),
                'filterdata' => new external_value(PARAM_RAW, 'The data for the service'),
        ]);
    }
    public static function get_mypayments($contextid,$options, $dataoptions, $offset = 0, $limit = 0, $filterdata) {
    global $OUTPUT, $CFG, $DB,$USER,$PAGE;
    $sitecontext = context_system::instance();
    require_login();
    $PAGE->set_url('/tool/product/mypayments.php', array());
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
            'filterdata' => $filterdata,
        ]
    );
    $data_object = (json_decode($dataoptions));
    $offset = $params['offset'];
    $limit = $params['limit'];
    $filtervalues = json_decode($filterdata);

    $stable = new \stdClass();
    $stable->thead = true;
    $payments = (new product)::get_listof_mypayments($stable,$filtervalues);
    $totalcount = $payments['paymentscount'];
    $stable->thead = false;
    $stable->start = $offset;
    $stable->length = $limit;
    $data = array();
   // if($totalcount>0){
    $renderer = $PAGE->get_renderer('tool_product');
    $data = array_merge($data,$renderer->lis_mypayments($stable,$filtervalues));
   // }
    if(!is_siteadmin() && has_capability('local/organization:manage_organizationofficial',$sitecontext)) {
        $quantity = true;
    } else{
        $quantity = false;
    }
    if($data) {
        $nodata = false;
    } else {
        $nodata = true;
    }
    return [
        'totalcount' => count($data),
        'records' => $data,
        'options' => $options,
        'dataoptions' => $dataoptions,
        'filterdata' => $filterdata,
        'quantity' => $quantity,
        'nodata' => $nodata,
        'nodatatext' => get_string('nopayments','tool_product')
    ];
    }

    public static function get_mypayments_returns() {
        return new external_single_structure([
            'options' => new external_value(PARAM_RAW, 'The paging data for the service'),
            'dataoptions' => new external_value(PARAM_RAW, 'The data for the service'),
            'totalcount' => new external_value(PARAM_INT, 'total number of post_financialpayments in result set'),
            'filterdata' => new external_value(PARAM_RAW, 'The data for the service'),
            'quantity' => new external_value(PARAM_BOOL, 'The data for the service'),
            'nodata' => new external_value(PARAM_BOOL, 'The data for the service'),
            'nodatatext' => new external_value(PARAM_RAW, 'The data for the service'),
            'records' => new external_multiple_structure(
                            new external_single_structure(
                                array(
                                    'id' => new external_value(PARAM_INT, 'payment id'),
                                    'trainingname' => new external_value(PARAM_RAW, 'training name'),
                                    'total_price' => new external_value(PARAM_RAW, 'total_price'),
                                    'discount_price' => new external_value(PARAM_RAW, 'discount_price'),
                                    'total_discount'=> new external_value(PARAM_RAW, 'total_discount'),
                                    'taxes' => new external_value(PARAM_RAW, 'taxes'),
                                    'total' => new external_value(PARAM_RAW, 'total'),
                                    'total_purchases' => new external_value(PARAM_INT, 'total_purchases'),
                                    'timeupdated' => new external_value(PARAM_RAW, 'timeupdated'),
                                    'quantity' => new external_value(PARAM_RAW, 'quantity'),
                                    'purchasedseats'  => new external_value(PARAM_RAW, 'purchasedseats'),
                                    'statuscode' => new external_value(PARAM_RAW, 'purchased seats'))
                            )
            )
        ]);
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
            'contextid' => new external_value(PARAM_INT, 'The context id', false),
            'options' => new external_value(PARAM_RAW, 'The paging data for the service'),
            'dataoptions' => new external_value(PARAM_RAW, 'The data for the service'),

            'offset' => new external_value(PARAM_INT, 'Number of items',
                VALUE_DEFAULT, 0),
            'limit' => new external_value(PARAM_INT, 'Maximum number of results to return',
                VALUE_DEFAULT, 0),
            'filterdata' => new external_value(PARAM_RAW, 'The data for the service')
        ]);
    }

    public function get_mywallet($contextid, $options,
    $dataoptions,
    $offset = 0,
    $limit = 0,
    $filterdata
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
            'contextid' => new external_value(PARAM_INT, 'The context id', false),
            'options' => new external_value(PARAM_RAW, 'The paging data for the service'),
            'dataoptions' => new external_value(PARAM_RAW, 'The data for the service'),

            'offset' => new external_value(PARAM_INT, 'Number of items',
                VALUE_DEFAULT, 0),
            'limit' => new external_value(PARAM_INT, 'Maximum number of results to return',
                VALUE_DEFAULT, 0),
            'filterdata' => new external_value(PARAM_RAW, 'The data for the service')
        ]);
    }
    public static function get_orgpayments($contextid,$options, $dataoptions, $offset = 0, $limit = 0, $filterdata) {
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
        $stable->thead = false;
        $payments = (new tool_product\product)->get_listof_orgpayments($stable,$filtervalues);
        $totalcount = $payments['paymentscount'];
        $stable->thead = false;
        $stable->start = $offset;
        $stable->length = $limit;
        $data = array();
        if($totalcount>0){
            $renderer = $PAGE->get_renderer('tool_product');
            $data = array_merge($data,$renderer->list_orgpayments($stable,$filterdata));
        }
        if($data) {
            $nodata = false;
        } else {
            $nodata = true;
        }
        return [
            'totalcount' => $totalcount,
            'records' => $data,
            'options' => $options,
            'dataoptions' => $dataoptions,
            'filterdata' => $filterdata,
            'nodata' => $nodata,
            'nodatatext' => get_string('nopayments','tool_product')
        ];
    }

    public static function get_orgpayments_returns() {
        return new external_single_structure([
            'options' => new external_value(PARAM_RAW, 'The paging data for the service'),
            'dataoptions' => new external_value(PARAM_RAW, 'The data for the service'),
            'totalcount' => new external_value(PARAM_INT, 'total number of post_financialpayments in result set'),
            'filterdata' => new external_value(PARAM_RAW, 'The data for the service'),
            'nodata' => new external_value(PARAM_BOOL, 'The data for the service'),
            'nodatatext' => new external_value(PARAM_RAW, 'The data for the service'),
            'records' => new external_multiple_structure(
                            new external_single_structure(
                                array(
                                    'id' => new external_value(PARAM_INT, 'payment id'),
                                    'trainingname' => new external_value(PARAM_RAW, 'training name'),
                                    'total_price' => new external_value(PARAM_RAW, 'total_price'),
                                    'discount_price' => new external_value(PARAM_RAW, 'discount_price'),
                                    'taxes' => new external_value(PARAM_RAW, 'taxes'),
                                    'total' => new external_value(PARAM_RAW, 'total'),
                                    //'total_purchases' => new external_value(PARAM_INT, 'total_purchases'),
                                    'timecreated' => new external_value(PARAM_RAW, 'timeupdated'),
                                    //'quantity' => new external_value(PARAM_RAW, 'quantity'),
                                    'purchasedseats'  => new external_value(PARAM_RAW, 'purchasedseats'),
                                    'statuscode' => new external_value(PARAM_RAW, 'purchased seats'),
                                    'paymentmethod' => new external_value(PARAM_RAW, 'paymentmethod'),
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

                $tabledata->tablename='hall_reservations';

                break;

                case 3:

                $tabledata->tablename='local_events';

                case 4:

                $tabledata->tablename='local_exam_grievance';

                case 5:

                $tabledata->tablename='local_learningtracks';

                break;
            }

            $tabledata->fieldname='id';

            $tabledata->fieldid=$DB->get_field('tool_products','referenceid',  array('id'=>$tabledata->productid));

            $tabledata->userid=$products->userid;
            $tabledata->paymenttype=$products->paymenttype;
            $tabledata->paymenton=time();
            $tabledata->amount=$products->total;
            $tabledata->purchasedseats=$product->quantity;
            $tabledata->quantity=$product->quantity;

            $tabledata->originalprice=$products->total_price;
            $tabledata->discountprice=$products->total_discount;
            $tabledata->taxes=$products->taxes;

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
}
