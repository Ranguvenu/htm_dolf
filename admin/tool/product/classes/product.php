<?php
/**
 * 
 *
 * @package    tool_product
 * @copyright  2022 Naveen kumar <naveen@eabyas.com>
 */
namespace tool_product;
use context_system;
use dml_exception;
use moodle_exception;
use stdClass;
use moodle_url;
use local_events\events as events;
use local_exams\local\exams as exams;
use local_trainingprogram\local\trainingprogram as trainingprogram;
use tool_product\telr as telr;
use \tool_product\notification;
use local_learningtracks\learningtracks as learningtracks;

require_once($CFG->dirroot.'/lib/moodlelib.php');
require_once($CFG->dirroot.'/group/lib.php');
/**
 * Product manager
 */
class product
{
    public const TRAINING_PROGRAM = 1;
    public const EXAMS = 2;
    public const EVENTS = 3;
    public const GRIEVANCE = 4;
    public const LEARNINGTRACKS = 5;
    public const EXAMATTEMPT = 6;

    public $categories;

    public $errors;


    public $supported_payment_methods;

    private $tax_slab;

    public function __construct() {
        $this->categories = [self::TRAINING_PROGRAM => get_string('pluginname', 'local_trainingprogram'),
                             self::EXAMS => get_string('pluginname', 'local_exams'),
                             self::EVENTS => get_string('pluginname', 'local_events'),
                             self::GRIEVANCE => get_string('pluginname', 'local_events'),
                             self::LEARNINGTRACKS => get_string('pluginname', 'local_learningtracks'),
                             self::EXAMATTEMPT => get_string('attempts', 'local_exams')];
        $this->tax_slab = 0;

        $this->errors = [
            self::TRAINING_PROGRAM => get_string('noprogramvariation', 'tool_product'),
            self::EXAMS => get_string('noexamvariation', 'tool_product'),
            self::EVENTS => get_string('noeventvariation', 'tool_product'),
            self::GRIEVANCE => get_string('nogrievance', 'tool_product'),
            self::LEARNINGTRACKS => get_string('nolearningtracks', 'tool_product'),
            self::EXAMATTEMPT => get_string('noexamattempt', 'tool_product')
        ];

        $this->supported_payment_methods = array(
            array(
                'slug'          =>  'telr',
                'capabilities'  =>  'local/organization:manage_trainee'
            ),
            // array(
            //     'slug'          =>  'prepaid',
            //     'capabilities'  =>  'local/organization:manage_organizationofficial'
            // ),
            // array(
            //     'slug'          =>  'prepaid',
            //     'capabilities'  =>  'local/organization:manage_trainee'
            // ),            
            array(
                'slug'          =>  'postpaid',
                'capabilities'  =>  'local/organization:manage_organizationofficial'
            )
        );

    }

    public function store($data) {
        global $DB, $USER;
        
        $record = $this->prepare($data);
        $record->timecreated = time();
        $record->usercreated = $USER->id;
        try{
            $productid = $DB->insert_record('tool_products', $record);
            return $productid;
        }
        catch(dml_exception $e){
            print_error($e->debuginfo);
        }
    }

    public function update($data) {
        global $DB, $USER;
        if(!isset($data->id)){
            print_error('ID mandatory to update the record');
        }
        $record = $this->prepare($data);
        
        $record->timemodified = time();
        $record->usermodified = $USER->id;
        try{
            $DB->update_record('tool_products', $data);
        }
        catch(dml_exception $e){
            print_error($e->debuginfo);
        }
    }

    public function delete($productid){
        global $DB;
        try{
            $DB->delete_records('tool_products', ['id' => $productid]);
            return true;
        } catch(dml_exception $e){
            print_error($e->debuginfo);
        }
    }
    public function getproducts($products) {
        global $DB, $USER;
     
        if(empty($products)){
            return;
        }
        try{
            $productlist = $DB->get_records_sql("SELECT * FROM {tool_products} WHERE id IN ($products)");
            return $productlist;
        }
        catch(dml_exception $e){
            print_error($e->debuginfo);
        }
    }

    public function is_user_enrolled( $product ){
        global $DB, $USER;
        
        $isenrolled = false;

        switch($product->category){
            case self::TRAINING_PROGRAM :
                $programid = $DB->get_field('tp_offerings', 'trainingid', ['id' => $product->referenceid]);
                if(!$programid){
                    return false;
                }
                $isenrolled = (new trainingprogram)->is_enrolled( $programid, $USER->id);
            break;
            case self::EXAMS;
                $examid = $DB->get_field('local_exam_profiles','examid',['id'=>$product->referenceid]);
                $isenrolled = (new exams)->is_enrolled($examid,$USER->id);
            break;
            case self::EVENTS:
                $isenrolled = (new events)->is_enrolled($product->referenceid, $USER->id);
            break;
            case self::GRIEVANCE:
                $isenrolled = (new exams)->is_enrolled_grievance( $product->referenceid, $USER->id);
            break;
        }

        return $isenrolled;
    }

    public function userprofilecountry($product, $userid=false)
    {
        global $DB, $USER;
        $systemcontext = context_system::instance();
        $profile = $DB->get_record('local_exam_profiles', ['id' => $product['referenceid']]);
        if( ((!is_siteadmin() && has_capability('local/organization:manage_trainee',$systemcontext)) && $product['category'] == 2) || !empty($userid) ) {

            if (empty($userid)) {
                $userid = $USER->id;
            }

            $usernationality = $DB->get_field('local_users', 'nationality',  ['userid' => $userid]);
            if(is_numeric($usernationality)) {
                $countries = (new \local_userapproval\action\manageuser)->get_list_of_nationalities();
                $usercountry=$countries[$usernationality];
            } else {
                $countries = get_string_manager()->get_list_of_countries();
                $usercountry = $countries[$usernationality];
            }
            
            // Changes for Ticket TRK164_12008
            if (($profile->targetaudience == 1 && ($usercountry == 'Saudi Arabia' || $usercountry == 'السعودية')) || ($profile->targetaudience == 2 && ($usercountry != 'Saudi Arabia' || $usercountry == 'السعودية')) ||  ($profile->targetaudience == 3) ) {
                $profileaccess = false;
            } else {
                $profileaccess = true;
            }

            $req = (new exams)->userrequirements($profile->examid, $userid);
            if (!empty($req)) {
                return get_string('requireexamreq', 'tool_product');
            } elseif(!empty($profileaccess)) {
                if ($usercountry == 113) {
                    return get_string('profileisnonsaudhi', 'tool_product');
                } else {
                    return get_string('profileissaudhi', 'tool_product');
                }
            } else {
                return false;
            }
        } else {
            return false;
        }
    }

    public function get_product( $product ){
        global $DB, $USER,$CFG;
        $curren_language = current_language();

        if(empty($product)){
            return;
        }

        try{
            $data = $DB->get_record('tool_products', ['id' => $product]);
         
            if($data->category == 1){

                $training = $DB->get_record_sql("SELECT tp.id,tp.name,tp.namearabic,tp.sectors,tpo.trainingid,tp.discount,tpo.startdate,tp.duration,tp.description,tp.tax_free 
                                                   FROM {local_trainingprogram} AS tp 
                                                   JOIN {tp_offerings} AS tpo ON tpo.trainingid = tp.id 
                                                  WHERE tpo.id = $data->referenceid");

                if($curren_language == 'ar') {
            
                    $data->name = $training->namearabic;
                } else {

                    $data->name = $training->name;
                }

                $data->description = $training->description;
                $data->tax_free = $training->tax_free;
                $data->programid = $training->id;

                if($data->tax_free == 0) {

                    $data->tax_percentage = get_config('tool_product','tax_percentage');
                    $data->tax_amount  = round(($data->price * (($data->tax_percentage/100))),2);
                } else {

                    $data->tax_percentage = 0;
                    $data->tax_amount  = 0;

                }

                if($training->sectors) {
                    list($sectorsql,$sectorparams) = $DB->get_in_or_equal(explode(',',$training->sectors));
                    if($curren_language == 'ar') {
            
                        $sectorquerysql = "SELECT id AS sectorid,titlearabic AS sectorname FROM {local_sector} WHERE id $sectorsql";
                    } else {

                        $sectorquerysql = "SELECT id AS sectorid, title AS sectorname FROM {local_sector} WHERE id $sectorsql";
                    }
                    $sectorslists= $DB->get_records_sql($sectorquerysql,$sectorparams);

                    if(!empty($sectorslists)){
                        $data->sectors = array_values($sectorslists);
                    } else {
                        $data->sectors = array();
                    }

                } 

                $data->viewcourseurl = $CFG->wwwroot.'/local/trainingprogram/programcourseoverview.php?programid='.$training->trainingid;
                if($training->trainingid!="")
                {
                    $lecturessql = "SELECT COUNT(id) FROM {offering_sessions} WHERE programid = $training->trainingid";
                    $data->lectures = $DB->count_records_sql($lecturessql);
                }
               
                $data->programduration =round($training->duration / 86400); 


                $data->early_registration_discount =0;

                $data->early_registration_discount_amount =0;


                $data->groups_organization_discount =0;

                $data->groups_organization_discount_amount =0;


                if($training->discount == 1) {

                    $offeringstartdate = strtotime(userdate($training->startdate,'%Y-%m-%d'));
                    $curr_date = strtotime(userdate(time(),'%Y-%m-%d'));
                    $days = floor(($offeringstartdate - $curr_date) / (60 * 60 * 24));
                    $registration_discount =$this->get_early_registration_discount($days);

                    if($registration_discount){

                        $data->early_registration_discount = ceil($registration_discount);

                        $data->early_registration_discount_amount = round(($data->price * (($data->early_registration_discount/100))),2);
                    }

                    $data->discount_type = 'early_registration';
                    
                } elseif($training->discount == 0) {
                    $data->discount_type = 'coupon';
                } else {
                    $data->discount_type = 'groups';
                    $groupsdiscount = $DB->get_field_sql("SELECT org.discount_percentage 
                                                            FROM {local_users} as ud 
                                                            JOIN {local_organization} as org ON org.id=ud.organization
                                                            WHERE ud.userid=$USER->id");
                    if($groupsdiscount > 0 ){


                        $data->groups_organization_discount = $groupsdiscount;

                        $data->groups_organization_discount_amount = round(($data->price * (($data->groups_organization_discount/100))),2);

                    }
                }
                

            } elseif ($data->category == 2){
                $profile = $DB->get_record('local_exam_profiles', ['id' => $data->referenceid]);
                $exam = $DB->get_record_sql("SELECT le.id,le.exam,le.examnamearabic,le.programdescription,le.tax_free 
                                               FROM {local_exams} AS le 
                                              WHERE le.id = $profile->examid ");

                if($curren_language == 'ar') {            
                    $data->name = $exam->examnamearabic;
                } else {
                    $data->name = $exam->exam;
                }

                $data->description = $exam->programdescription;
                $data->tax_free = $exam->tax_free;

                if($data->tax_free == 0) {
                    $data->tax_percentage = get_config('tool_product','tax_percentage');
                    $data->tax_amount  = round(($data->price * (($data->tax_percentage/100))),2);
                } else {
                    $data->tax_percentage = 0;
                    $data->tax_amount  = 0;
                }

                $data->viewcourseurl = $CFG->wwwroot.'/local/exams/exams_qualification_details.php?id='.$profile->examid;
                $data->early_registration_discount =0;
                $data->early_registration_discount_amount =0;
                $data->groups_organization_discount =0;
                $data->groups_organization_discount_amount =0;

                if($profile->discount == 2) {
                    $profilestartdate = strtotime(userdate($profile->registrationstartdate,'%Y-%m-%d'));
                    $curr_date = strtotime(userdate(time(),'%Y-%m-%d'));
                    $days = floor(($profilestartdate - $curr_date) / (60 * 60 * 24));
                    $registration_discount =$this->get_early_registration_discount($days);
                    if($registration_discount){
                        $data->early_registration_discount = ceil($registration_discount);
                        $data->early_registration_discount_amount = round(($data->price * (($data->early_registration_discount/100))),2);
                    }
                    $data->discount_type = 'early_registration';
                } elseif($profile->discount == 1) {
                    $data->discount_type = 'coupon';
                }
            } elseif($data->category == 3) {
               $eventid = $data->referenceid;
               $event = $DB->get_record_sql("SELECT le.id,le.title,le.titlearabic,le.description,le.taxfree FROM {local_events} AS le WHERE le.id =$eventid");

                if($curren_language == 'ar') {
            
                    $data->name = $event->titlearabic;
                } else {

                    $data->name = $event->title;
                }

                $data->description = $event->description;
                $data->tax_free = $event->taxfree;

                if($data->tax_free == 0) {
                    $data->tax_percentage = get_config('tool_product','tax_percentage');
                    $data->tax_amount  = round(($data->price * (($data->tax_percentage/100))),2);
                } else {
                    $data->tax_percentage = 0;
                    $data->tax_amount  = 0;
                }
               $data->viewcourseurl = '';//$CFG->wwwroot.'/local/events/alleventsview.php?id='.$eventid;
            } elseif ($data->category == 6){
                $profile = $DB->get_record('local_exam_attempts', ['id' => $data->referenceid]);
                $exam = $DB->get_record_sql("SELECT le.id,le.exam,le.examnamearabic,le.programdescription,le.tax_free FROM {local_exams} AS le WHERE le.id = $profile->examid ");

                if($curren_language == 'ar') {            
                    $data->name = $exam->examnamearabic;
                } else {
                    $data->name = $exam->exam;
                }

                $data->description = $exam->programdescription;
                $data->tax_free = $exam->tax_free;

                if($data->tax_free == 0) {
                    $data->tax_percentage = get_config('tool_product','tax_percentage');
                    $data->tax_amount  = round(($data->price * (($data->tax_percentage/100))),2);
                } else {
                    $data->tax_percentage = 0;
                    $data->tax_amount  = 0;
                }

                $data->viewcourseurl = $CFG->wwwroot.'/local/exams/exams_qualification_details.php?id='.$profile->examid;
                $data->early_registration_discount =0;
                $data->early_registration_discount_amount =0;
                $data->groups_organization_discount =0;
                $data->groups_organization_discount_amount =0;
            } elseif ($data->category == 4){ 
                $data->tax_percentage = get_config('tool_product','tax_percentage');
                $data->tax_amount  = round(($data->price * (($data->tax_percentage/100))),2);
            } else {

               $grievanceid = $data->referenceid;
               $data->viewcourseurl = '';


            }
            return $data;
        }catch(dml_exception $e){
            print_error($e->debuginfo);
        }
    }

    public function dateDiffInDays($date1, $date2) {
      $diff = strtotime($date2) - strtotime($date1);
      return abs(round($diff / 86400));
    }

    /** 
     *  int $reference 
     *  int $category
     *  string $label
     *  boolean $hasvariations
     *  int $variation Offering Id
     *  int $quantity
     *  boolean $checkout
     **/

    public function get_product_attributes( $reference, $category, $label, $hasvariations, $variation = 0, $quantity = 1, $checkout="false", $grouped=0){

        global $DB;
        $product = ''; 
        if(!$hasvariations && $reference != 0){
            $product = $this->get_product_from_reference($reference, $category);
        }

        $productinfo=array(
            'product'       =>  $product ? $product->id : $reference,
            'category'      =>  $category,
            'variation'     =>  $variation,
            'label'         =>  get_string($label, 'tool_product'),
            'quantity'      =>  $quantity,
            'isloggedin'    =>  isloggedin(),
            'hasvariations' =>  $hasvariations ,
            'checkout'      =>  $checkout,
            'grouped'       =>  $grouped,
            'errortext'     =>  $this->errors[$category]
        );

        return $productinfo;
    }

    public function get_product_variations( $reference, $variation, $category,$tptandc = null){
       
        if($variation){
            $product = $this->get_product_from_reference($variation, $category);
        } else {
            $product = 0;
        }

        return array(
            'product'       =>  $reference,
            'variation'     =>  $product ? $product->id : 0,
            'tptandc'         =>  $tptandc,
            'isloggedin'         =>  isloggedin(),
            'category'      =>  $category
        );
    }

    public function get_product_thumbnail($reference_id, $category){
        global $CFG, $DB;
        $thumbnail = '';
        switch($category){
            case self::TRAINING_PROGRAM:
                require_once($CFG->dirroot . '/local/trainingprogram/lib.php');
                $programid = $DB->get_field('tp_offerings','trainingid',array('id'=>$reference_id));
                $image = $DB->get_field('local_trainingprogram','image',array('id'=>$programid));
                $thumbnail = trainingprogramlogo_url($image);
            break;

            case self::EXAMS:
                require_once($CFG->dirroot . '/local/exams/lib.php');
                // $image = $DB->get_field('local_exams','learningmaterial',array('id'=>$reference_id));
                $thumbnail = examlearningmaterial_url($image=0, 'learningmaterial');
            break;

            case self::EVENTS:
                require_once($CFG->dirroot . '/local/events/lib.php');
                $image = $DB->get_field('local_events','logo',array('id'=>$reference_id));
                $thumbnail = logo_url($image);
            break;
        }

        return $thumbnail;
    }


    public function get_product_from_reference($reference_id, $category){
        global $DB;

        $sql = "SELECT * 
                  FROM {tool_products} 
                 WHERE referenceid = $reference_id AND category = $category 
                 ORDER BY id DESC ";
        $product = $DB->get_record_sql($sql);

        return $product;
    }

    private function prepare($data){
        global $DB;
        $record = new stdClass;
        if(!array_key_exists($data->category, $this->categories)){
            print_error('Invalide category');
        }
        $record->category = $data->category;
        if($DB->record_exists('tool_products', ['category' => $data->category, 'code' => $data->code])){
            print_error('product code already exist');
        }
        $record->code = $data->code;
        $record->name = $data->name;
        $record->price = $data->price;
        if($data->discount > 100){
            print_error('Invalid discount');
        }
        $record->description = $data->description;
        $record->discount = $data->discount;
        $record->units = $data->units;
        $record->stock = $data->stock;
        $record->type = $data->type;
        $record->status = 1;
        return $record;
    }

    public function get_payment_methods(){
        $context = \context_system::instance();

        $payment_methods = [];
        foreach($this->supported_payment_methods as $key => $payment_method){
            if($payment_method['capabilities']){
                if(has_capability($payment_method['capabilities'], $context)){
                    $payment_methods[] = $this->format_payment_method($payment_method);
                }
            }else{
                $payment_methods[] = $this->format_payment_method($payment_method);
            }
        }

        return $payment_methods;
    }

    private function format_payment_method( $method ){
        return array(
                'slug'  =>  $method['slug'],
                'name'  =>  get_string($method['slug'], 'tool_product')
            );
    }

    public function get_cart_summary( $products ){
        $discount_price = 0;
        $total_price = 0;
        $total_discount_amount = 0;

        foreach($products as $key => $product){
            $total_price = $total_price + ($product['actual_price']*$product['quantity']);
            $total_discount_amount = ($product['early_registration_discount_amount'] + $product['groups_organization_discount'] + $product['groups_organization_discount_amount']);
            $discount_price = $discount_price + ($product['sale_price']*$product['quantity']);

        }

        return array(
            'total_price'   =>  round($total_price,2),
            'discount_price'=>  round($discount_price,2),
            'total_discount_amount'=>  round($total_discount_amount,2),
            'total_discount_amount_display'=>  ($total_discount_amount > 0) ? true : false,
        );
    }

    public function get_order_summary( $products ){
        $total_purchases = sizeof($products);
        $total_price = $discount_price = $taxes = $discount = 0;
        $items = [];
        $tax = [];
        $discountprice = [];
        $taxslab = [];
        $productinfo = [];
        foreach($products as $key => $value){
            $discount_price ='';
            $item_discount_price = '';
            $item_total = '';
            if(!$value['is_enrolled']){
                $total_price = $total_price+($value['actual_price']*$value['quantity']);
                $discount_price = ($value['sale_price']*$value['quantity']);
            }

            $this->tax_slab = $value['tax_percentage'];
            $taxes = $this->caluculate_taxes($discount_price, $this->tax_slab);
            $tax[] = $taxes;
            $discountprice[] = $discount_price;
            $taxslab[] = $this->tax_slab;

            if (!empty($value['usercountryaccess'])) {
                $total = 0;
                $tax = 0;
                $total_price = 0;
                $discountprice = 0;
            } else {
                $total = round(($value['actual_price']*$value['quantity']),2);
            }
            $item_discount_price = round($discount_price,2);

            $item_total = $taxes + $item_discount_price;
           // $item_total_price = $discount_price = $taxes = $discount = 0;
            $productinfo[] = $value['name'];
            $items[] = array(
                'product_id'=>  $value['id'],
                'name'      =>  $value['name'],
                'referenceid' => $value['referenceid'],
                'quantity'  =>  $value['quantity'],
                'is_enrolled' => $value['is_enrolled'],
                'total'     =>  $total,
                'item_tax' => $taxes,
                'item_discount_price' =>  $total - round($discount_price,2),
                'item_total_price' => $item_total,
                'category'  =>  $value['category'],
                'grouped'  => $value['grouped'],
                'language'  => $value['language'],
                'hallscheduleid'  => $value['hallscheduleid'],
                'profileid'  => $value['profileid'],
                'processtype'  => $value['processtype'],
                'usercountryaccess' => $value['usercountryaccess'],
                'early_registration_discount'=>  $value['early_registration_discount'],
                'early_registration_discount_amount'      =>  $value['early_registration_discount_amount'],
                'discount_type'  =>  $value['discount_type'],
                'couponactionview' => ($value['roles']) ? ($value['discount_type'] == 'coupon' ? true: false) : false,
                'couponid' => $value['couponid'],
                'couponcode' => $value['couponcode'],
                'couponcode_discount_amount' => round($value['couponcode_discount_amount'],2),
               'groups_organization_discount' => $value['groups_organization_discount'],
               'groups_organization_discount_amount' => $value['groups_organization_discount_amount'],
               'tandcconfirm' =>  $value['tandcconfirm'],

            );
        } 

        $total_discount = $total_price - array_sum($discountprice);
        $taxes =  array_sum($tax);
        $total = array_sum($discountprice) + $taxes;

        return array(
            
            'items'             =>  $items,
            'total_price'       =>  round($total_price,2),
            'discount_price'    =>  round($discount_price,2),
            'total_discount'    =>  round($total_discount,2),
            'total'             =>  round($total,2),
            'taxes'             =>  round($taxes,2),
            'taxdisplay'   =>  $taxes > 0 ? true : false,
            'tax_slab'          =>  $this->tax_slab,//array_sum($taxslab),
            'total_purchases'   =>  $total_purchases,
            'productinfo'       => implode(',',$productinfo)
           
        );
    }

    public function format_product($product,$userid = false){
        global $DB, $USER, $OUTPUT, $CFG,$PAGE;

        $sitecontext = context_system::instance();

        $PAGE->set_context($sitecontext);

        $product->discount=$product->discount ? $product->discount : 0;

        if($product->discount_type == 'early_registration' && $product->early_registration_discount > 0) {

            $total_discount = $product->discount + $product->early_registration_discount;

            if($total_discount){
               
                $product->discount = $total_discount;

            } else {
                $discount = $product->discount;

                $product->discount = $discount;
            }

            
        }
        if($product->discount_type == 'groups' && $product->groups_organization_discount > 0) {

            $total_discount = $product->discount + $product->groups_organization_discount;

            if($total_discount){
               
                $product->discount = $total_discount;

            } else {

                $discount = $product->discount;

                $product->discount = $discount;
            }

            
        }
        if ($userid){
            $userlang = $DB->get_field('user','lang',array('id'=>$userid->id));
            if($userlang == 'ar'){
                
        switch ($product->category) {

            case 1:

            $productname = $DB->get_field('local_trainingprogram','namearabic',array('code'=>$product->code));

            break;

            case 2:


            $profile = $DB->get_field('local_exam_profiles','examid',array('profilecode'=>$product->code));
            $productname = $DB->get_field('local_exams','examnamearabic',array('id'=>$profile));

            break;

            case 3:        
            $productname = $DB->get_field('local_events','titlearabic',array('code'=>$product->code));

            break;
        }

            } else{
                $productname = $product->name;
            }            

        } else{
            $productname = $product->name;
        }
      
        $discount = $product->discount;
        $saleprice = $this->caluculate_price_after_discount($product->price, $discount);
        $actualprice = $product->price;
       
        return array(
            'id'            =>  $product->id,
            'name'          =>  $productname,
            'description'   =>  format_text($product->description),
            'category'      =>  $product->category,
            'thumbnail'     =>  $this->get_product_thumbnail($product->referenceid, $product->category),
            'actual_price'  =>  $actualprice,
            'sale_price'    =>  $saleprice,
            'referenceid'   =>  $product->referenceid,
            'is_enrolled'   =>  $product->is_enrolled,
            'code'          =>  $product->code,
            'discount'      =>  $discount,
            'stock'         =>  $product->stock,
            'type'          =>  $product->type,
            'status'        =>  $product->status,
            'units'         =>  $product->units,
            'early_registration_discount'  =>  $product->early_registration_discount ? $product->early_registration_discount : 0,        
            'early_registration_discount_amount'  =>  $product->early_registration_discount_amount ? $product->early_registration_discount_amount : 0,        
            'early_registration_discount_view'  => $product->early_registration_discount_amount > 0 ? true : false, 
            'viewcourseurl' => $product->viewcourseurl,
            'couponactionview' => $product->discount_type == 'coupon' ? true: false,
            'discount_type' => $product->discount_type ? $product->discount_type: false,
            'sectors' => $product->sectors ? $product->sectors : array(),       
            'programduration' => $product->programduration ? $product->programduration : 0,       
            'lectures' => $product->lectures ? $product->lectures : 0,   
            'couponcode_discount_amount' => $product->couponcode_discount_amount ? $product->couponcode_discount_amount : 0,  
            'couponid'=>  $product->couponid ? $product->couponid : 0,  
            'couponcode'=> $product->couponcode ? $product->couponcode : '',  
            'couponvalid' => $product->couponvalid ? $product->couponvalid : false,  
            'couponmsg' => $product->couponmsg ? $product->couponmsg : '',  
            'groups_organization_discount'  =>  $product->groups_organization_discount ? $product->groups_organization_discount : 0,        
            'groups_organization_discount_amount'  =>  $product->groups_organization_discount_amount ? $product->groups_organization_discount_amount : 0,        
            'groups_organization_discount_view'  => $product->groups_organization_discount_view > 0 ? true : false,  
             'tax_percentage' => $product->tax_percentage,
             'tax_amount'  => $product->tax_amount,
             'userlang'=>$userlang,
                   
        );
    }

    private function caluculate_price_after_discount( $total, $discount){
        return round($total - ($total * ($discount/100)),2);
    }

    private function caluculate_taxes( $amount, $tax ){
        return ($amount * ($tax/100));
    }
    public function insert_update_org_order_payments($data,$product=array()) {
        global $DB, $USER;
        try {
            $data->timemodified=time();
            $data->usermodified=$USER->id;
            $organizationid = (new \tool_product\product)->get_user_organization($data->orguserid);
            $data->organization = $organizationid?$organizationid:0;  
            if($data->id > 0){
                $data->paymentapprovalstatus=1;
                $data->approvaluserid=$USER->id;
                $data->approvalon=time();
                $data->timemodified=time();
                $data->usermodified=$USER->id;
                $data->realuser = ($USER->realuser) ? $USER->realuser :0;

                $oldinvoice  = $DB->get_field("tool_org_order_payments",'transactionid',array('id'=>$data->id));       
                $invoiceid = $DB->get_field("tool_product_sadad_invoice",'id',array("invoice_number" => $oldinvoice));
                $invoicedetails = new stdClass();
                $invoicedetails->id = $invoiceid;
                $invoicedetails->invoice_number = $data->transactionid;
                $invoicedetails->payment_status = $data->amountrecived;
                $DB->update_record('tool_product_sadad_invoice',$invoicedetails);               

                $id=$DB->update_record('tool_org_order_payments', $data);

                $paymentorder=clone $data;
                $touser=$DB->get_record('user',array('id'=>$data->orguserid));
                $localuserrecord = $DB->get_record('local_users',['userid'=>  $data->approvaluserid]);
                $fullname = ($localuserrecord)? (($localuserrecord->lang == 'ar') ? $localuserrecord->firstnamearabic.' '.$localuserrecord->middlenamearabic.' '.$localuserrecord->thirdnamearabic.' '.$localuserrecord->lastnamearabic  : $localuserrecord->firstname.' '.$localuserrecord->middlenameen.' '.$localuserrecord->thirdnameen.' '.$localuserrecord->lastname) :fullname($touser);
                $paymentorder->payment_userfullname=$fullname;
                $paymentorder->quantity=$data->purchasedseats;
                $paymentorder->payment_details=$this->get_payment_details($paymentorder, true, $data->orguserid);
                $paymentorder->invoiceno=$data->id;
                (new \tool_product\notification())->product_notification('payment_completion', $touser,$fromuser=get_admin(), $paymentorder,$waitinglistid=0);
                self::insert_update_org_order_seats($data);
            }else{

                $data->timecreated=time();
                $data->usercreated=$USER->id;
                $data->realuser=($USER->realuser) ? $USER->realuser :0;
                $data->approvalon=(!isset($data->approvalon)) ? 0 : $data->approvalon;
                $data->paymentid=$DB->insert_record('tool_org_order_payments', $data);
                if(!empty($product)){
                    $product->orgorderid=$data->paymentid;
                    $product->userid=$data->orguserid;
                    $product->productid=$data->productid;
                    self::insert_update_order_discount_usage($product);
                }

                self::insert_update_org_order_seats($data);
                $systemcontext = context_system::instance();
                if($data->paymenttype == 'prepaid' || $data->paymenttype == 'telr'){
                    $paymentorder=clone $data;
                    $touser=$DB->get_record('user',array('id'=>$data->orguserid));
                    $localuserrecord = $DB->get_record('local_users',['userid'=> $data->orguserid]);
                    $fullname = ($localuserrecord)? (( $localuserrecord->lang== 'ar') ? $localuserrecord->firstnamearabic.' '.$localuserrecord->middlenamearabic.' '.$localuserrecord->thirdnamearabic.' '.$localuserrecord->lastnamearabic  : $localuserrecord->firstname.' '.$localuserrecord->middlenameen.' '.$localuserrecord->thirdnameen.' '.$localuserrecord->lastname) :fullname($touser);
                    $paymentorder->payment_userfullname=$fullname;
                    $paymentorder->payment_details=$this->get_payment_details($paymentorder, true, $touser);
                    $paymentorder->order=$id;
                    (new \tool_product\notification())->product_notification('pre_payment', $touser,$fromuser=get_admin(), $paymentorder,$waitinglistid=0);
                    $paymentorder->invoiceno=$id;
                    (new \tool_product\notification())->product_notification('payment_completion', $touser,$fromuser=get_admin(), $paymentorder,$waitinglistid=0);
                    $organization =(int) $DB->get_field('local_users','organization',['userid'=>$data->orguserid]);
                    if($data->tablename == 'local_exam_profiles') {
                        $examid = $DB->get_field('local_exam_profiles', 'examid', ['id' => $data->fieldid]);
                        $courseid = $DB->get_field('local_exams', 'courseid', ['id' => $examid]);
                        if($organization) {
                            $allorgusers = $DB->get_records_sql("SELECT u.id,u.firstname FROM {user} as u 
                                                                      JOIN {local_users} as lc ON lc.userid = u.id 
                                                                      JOIN {role_assignments} as  ra on ra.userid=u.id
                                                                      JOIN {role} as r ON ra.roleid = r.id  AND r.shortname = 'organizationofficial'
                                                                      WHERE lc.organization =:organization AND  ra.contextid =:contextid",['organization'=>$organization,'contextid'=>$systemcontext->id]);

                            foreach($allorgusers AS $orguser){

                                $timestart = time();
                                $timeend = 0;
                                $manual = enrol_get_plugin('manual');
                                $roleid=$DB->get_field('role','id',array('shortname'=>'teacher'));
                                $instance = $DB->get_record('enrol', array('courseid' => $courseid, 'enrol' => 'manual'), '*', MUST_EXIST);
                                $manual->enrol_user($instance, $orguser->id, $roleid, $timestart, $timeend);     

                            }                
                        }
                    }    
                    if($data->tablename == 'tp_offerings') {
                        $programid = $DB->get_field('tp_offerings', 'trainingid', ['id' => $data->fieldid]);
                        $courseid = $DB->get_field('local_trainingprogram', 'courseid', ['id' => $programid]);
                        if($organization) {
                            $allorgusers = $DB->get_records_sql("SELECT u.id FROM {user} as u 
                                                                      JOIN {local_users} as lc ON lc.userid = u.id 
                                                                      JOIN {role_assignments} as  ra on ra.userid=u.id
                                                                      JOIN {role} as r ON ra.roleid = r.id  AND r.shortname = 'organizationofficial'
                                                                      WHERE lc.organization =:organization AND  ra.contextid =:contextid",['organization'=>$organization,'contextid'=>$systemcontext->id]);
                            foreach($allorgusers AS $orguser){
                                $timestart = time();
                                $timeend = 0;
                                $manual = enrol_get_plugin('manual');
                                $roleid=$DB->get_field('role','id',array('shortname'=>'teacher'));
                                $instance = $DB->get_record('enrol', array('courseid' => $courseid, 'enrol' => 'manual'), '*', MUST_EXIST);
                                $manual->enrol_user($instance, $orguser->id, $roleid, $timestart, $timeend); 
                                // Creating seperate group for organization - Starts//
                                $offeringrecord =$DB->get_record('tp_offerings',['id' => $data->fieldid]);  
                                $orgshortcode=$DB->get_field_sql('
                                    SELECT lorg.shortname FROM {local_organization} AS lorg 
                                    JOIN {local_users} AS lou ON lou.organization = lorg.id 
                                    WHERE lou.userid ='.$orguser->id.''
                                ); 
                                $groupdata = new stdClass;
                                if($orgshortcode)   {
                                    $groupdata->name = $orgshortcode.$offeringrecord->code;
                                    $groupdata->idnumber = $orgshortcode.$offeringrecord->code;
                                } else {
                                    $groupdata->name = $USER->id.'_'.$offeringrecord->code;
                                    $groupdata->idnumber =  $USER->id.'_'.$offeringrecord->code;
                                }
                                $groupdata->courseid= $courseid;
                                if(!groups_get_group_by_idnumber($courseid, $groupdata->idnumber)){
                                    $orggroupid = groups_create_group($groupdata);
                                }
        
                                if($orggroupid) {
                                    groups_add_member($orggroupid,$orguser->id,null,0);
                                }
        
                                // Creating seperate group for organization - Ends//
        
                                $group = $DB->get_record_sql("SELECT grop.id FROM {groups} as grop JOIN {tp_offerings} as tpo ON tpo.code = grop.idnumber WHERE tpo.id = $data->fieldid");
                                $groupid = (int) $group->id;
                                if ($groupid) {
                                    groups_add_member($groupid,$orguser->id,null,0);
                                }
                            


                            }                                          
                        }
                                              
                    }
                }else{
                    $paymentorder=clone $data;
                    $touser=$DB->get_record('user',array('id'=>$data->orguserid));
                    $localuserrecord = $DB->get_record('local_users',['userid'=> $data->orguserid]);
                    $fullname = ($localuserrecord)? (($localuserrecord->lang == 'ar') ? $localuserrecord->firstnamearabic.' '.$localuserrecord->middlenamearabic.' '.$localuserrecord->thirdnamearabic.' '.$localuserrecord->lastnamearabic  : $localuserrecord->firstname.' '.$localuserrecord->middlenameen.' '.$localuserrecord->thirdnameen.' '.$localuserrecord->lastname) :fullname($touser);
                    $paymentorder->payment_userfullname=$fullname;
                    (new \tool_product\notification())->product_notification('post_payment', $touser,$fromuser=get_admin(), $paymentorder,$waitinglistid=0);
                }
            }
        } catch (dml_exception $e) {
            print_error($e);
        }
        return true;
    }
    public function insert_update_order_discount_usage($data) {
        global $DB, $USER;
        
        try {

            $discounttype='';

            switch ($data->discount_type) {
                case 'groups':

                    $discounttype=$data->discount_type;

                    $discountid=0;

                    $discountuse=$data->groups_organization_discount_amount;

                    break;

                case 'early_registration':

                    $discounttype=$data->discount_type;

                    $discountid=0;

                    $discountuse=$data->early_registration_discount_amount;

                    break;
                case 'coupon':

                    $discounttype=$data->discount_type;

                    $discountid=$data->couponid;

                    $discountuse=$data->couponcode_discount_amount;

                    break;
    
            }

            if(!empty($discounttype)){

                $data->discounttype=$discounttype;

                $data->discountid=$discountid;

                $data->discountuse=$discountuse;

                $data->status=1;

                $data->timecreated=time();
                $data->usercreated=$USER->id;

                $data->realuser =($USER->realuser) ? $USER->realuser :0;

                $id=$DB->insert_record('tool_order_discount_usage', $data);
            }


        } catch (dml_exception $e) {
            print_error($e);

        }
        return true;
    }
    public function insert_update_org_order_seats($data) {
        global $DB, $USER;

        $organization = ($data->organization)? $data->organization :(int) $DB->get_field('local_users','organization',['userid'=>$data->orguserid]);
        
        try {
            $purchasedseats=$data->purchasedseats;


                if($organization > 0) {

                    $getdata=$DB->get_record_sql('SELECT id,purchasedseats,availableseats,approvalseats
                    FROM {tool_org_order_seats} 
                    WHERE tablename=:tablename 
                    AND fieldname=:fieldname AND fieldid=:fieldid 
                    AND orguserid=:orguserid
                    AND organization =:organization', 
                    array('tablename'=>$data->tablename,
                            'fieldname'=>$data->fieldname,
                            'fieldid'=>$data->fieldid,
                            'orguserid'=>$data->orguserid,
                            'organization'=>$organization
                        ));
                } else {

                    $getdata=$DB->get_record_sql('SELECT id,purchasedseats,availableseats,approvalseats
                    FROM {tool_org_order_seats} 
                    WHERE tablename=:tablename 
                    AND fieldname=:fieldname AND fieldid=:fieldid 
                    AND orguserid=:orguserid', 
                    array('tablename'=>$data->tablename,
                            'fieldname'=>$data->fieldname,
                            'fieldid'=>$data->fieldid,
                            'orguserid'=>$data->orguserid
                        ));

                }
                if($getdata){
                    $data->id=$getdata->id;
                    $data->purchasedseats=$getdata->purchasedseats+$purchasedseats;
                   // $data->purchasedseats=$purchasedseats;
                    if($data->paymenttype == 'prepaid' || $data->paymenttype == 'telr'){
                        $data->availableseats=$getdata->availableseats+$purchasedseats;
                        $data->approvalseats=$getdata->approvalseats+$purchasedseats;
                    } else {
                        $data->availableseats = 0;                    
                    }
                    $data->timemodified=time();
                    $data->usermodified=$USER->id;
                    // TRK164_12004
                   /* if($getdata->approvalseats > 0 || $data->autoapproval ==1 ) {
                        $data->purchasedseats = $getdata->purchasedseats+$purchasedseats;
                    } else {
                        $data->purchasedseats = $getdata->approvalseats+$purchasedseats;
                    }*/
                    $data->organization = $organization;
                    $data->realuser = ($USER->realuser) ? $USER->realuser :0;
                    $id=$DB->update_record('tool_org_order_seats', $data);
              
                // }
               
                if($data->paymenttype == 'postpaid'){
                    $getdata=$DB->get_record('tool_order_approval_seats',  array('tablename'=>$data->tablename,
                                                                                'fieldname'=>$data->fieldname,
                                                                                'fieldid'=>$data->fieldid,
                                                                                 'orguserid'=>$data->orguserid,
                                                                                'paymentid'=>$data->paymentid),
                                                                             'id,purchasedseats,approvalseats');
                    $data->id=$getdata->id;
                    $data->purchasedseats =$purchasedseats;
                    self::insert_update_org_order_approvals($data);
                }
           }else{
                $data->timecreated=time();
                $data->usercreated=$USER->id;
                if($data->paymenttype == 'prepaid' || $data->paymenttype == 'telr'){
                    $data->availableseats=$purchasedseats;
                    $data->approvalseats=$purchasedseats;
                }else{
                    $data->availableseats=0;
                    $data->approvalseats=0;
                }
                $data->realuser =($USER->realuser) ? $USER->realuser :0;
                $id=$DB->insert_record('tool_org_order_seats', $data);
                if($data->paymenttype == 'postpaid'){
                    self::insert_update_org_order_approvals($data);
                }
            }
        } catch (dml_exception $e) {
            print_error($e);
        }
        return true;
    }

    public function insert_update_org_order_approvals($data) {
        global $DB, $USER;
    
        try {
            $getdata=$DB->get_record_sql('SELECT id,purchasedseats,approvalseats,tablename,fieldname,fieldid,orguserid,paymentid, organization 
                                            FROM {tool_order_approval_seats} 
                                           WHERE id=:id', ['id' => $data->id]);
            if($getdata){
                $data->id=$getdata->id;
                if(isset($data->approvalseats)){
                    $approvalseats=$data->approvalseats;
                    $data->approvalseats = $approvalseats; //$getdata->approvalseats+
                }
                // if(isset($data->purchasedseats)){
                //    $purchasedseats=$data->purchasedseats;
                //    $data->purchasedseats=$getdata->purchasedseats+$purchasedseats;
                
                // }
                $data->timemodified=time();
                $data->usermodified=$USER->id;
                $data->realuser = ($USER->realuser) ? $USER->realuser :0;
                $id=$DB->update_record('tool_order_approval_seats', $data);

                if(isset($data->approvalseats)){
                    $getdatast=$DB->get_record('tool_org_order_seats',array('tablename'=>$getdata->tablename,
                                                                            'fieldname'=>$getdata->fieldname,
                                                                            'fieldid'=>$getdata->fieldid,
                                                                            'orguserid'=>$getdata->orguserid,
                                                                            'organization'=>$getdata->organization,
                                                                            )
                                                                    ,'id,purchasedseats,availableseats,approvalseats, organization');
                    
                    $data->availableseats=0;
                    $data->approvalseats=$getdatast->approvalseats+$approvalseats;
                    $data->purchasedseats=$getdatast->purchasedseats;
                    $data->id=$getdatast->id;
                    $data->timemodified=time();
                    $data->usermodified=$USER->id;
                    $data->organization=$getdatast->organization;
                    
                    $data->realuser = ($USER->realuser) ? $USER->realuser :0;
                    $id=$DB->update_record('tool_org_order_seats', $data);
                    if($getdata->tablename == 'local_exam_profiles') {
                        $examid = $DB->get_field('local_exam_profiles', 'examid', ['id' => $getdata->fieldid]);
                        $courseid = $DB->get_field('local_exams', 'courseid', ['id' => $examid]);
                        $timestart = time();
                        $timeend = 0;
                        $manual = enrol_get_plugin('manual');
                        $roleid=$DB->get_field('role','id',array('shortname'=>'teacher'));
                        $instance = $DB->get_record('enrol', array('courseid' => $courseid, 'enrol' => 'manual'), '*', MUST_EXIST);
                        $manual->enrol_user($instance, $getdata->orguserid, $roleid, $timestart, $timeend);

                       // $orderid = $DB->get_field('tool_order_approval_seats', 'id', ['paymentid'=>$data->id]);
                        $orderid = $getdata->id;
                        $DB->execute('UPDATE {exam_enrollments} SET enrolstatus=1 WHERE orderid ='.$orderid);
                        $DB->execute('UPDATE {local_exam_userhallschedules} SET enrolstatus=1 WHERE orderid ='.$orderid);


                        
                            $examenrol = $DB->get_record('exam_enrollments',array('orderid'=> $orderid,'profileid'=> $getdata->fieldid));
                            $exam_record= $DB->get_record('local_exams', array('id'=>$examenrol->examid));
                            $userid = $examenrol->userid;                        
                           (new \local_exams\local\exams)->exams_enrol_notification($exam_record, $userid, $examenrol->hallscheduleid);

                    }
                    if($getdata->tablename == 'tp_offerings') {

                        $orderid = $getdata->id;
                        $DB->execute('UPDATE {program_enrollments} SET enrolstatus=1 WHERE orderid ='.$orderid);
                        $tps = $DB->get_record('program_enrollments',array('orderid'=> $orderid,'offeringid'=> $getdata->fieldid));
                        $tpdata = $DB->get_record('local_trainingprogram',array('id'=> $tps->programid));
                        $tpdata->program_name = $tpdata->name;                
                        $tpdata->program_arabicname = $tpdata->namearabic;
                        $tpusers = $DB->get_record('local_users',array('userid'=>$tps->userid));
                        $tpdata->program_userfullname=$tpusers->firstname .' '. $tpusers->middlenameen.' '. $tpusers->thirdnameen.' '. $tpusers->lastname;
                        $tpdata->program_userarabicfullname = $tpusers->firstnamearabic .' '. $tpusers->middlenamearabic.' '. $tpusers->thirdnamearabic.' '. $tpusers->lastnamearabic;
                        $tpdata->roleid = $tps->roleid;
                        $trainee = $DB->get_record('user',array('id'=>$tps->userid)); 
                        $fromuser = $DB->get_record('user',array('id'=> $getdata->orguserid)); 
                        $tpdata->orgoff = 'yes';
                        (new \local_trainingprogram\notification())->trainingprogram_notification('trainingprogram_enroll', $touser=$trainee,$fromuser = $fromuser,$tpdata,$waitinglistid=0);


                        $programid = $DB->get_field('tp_offerings', 'trainingid', ['id' => $getdata->fieldid]);
                        $courseid = $DB->get_field('local_trainingprogram', 'courseid', ['id' => $programid]);
                        $timestart = time();
                        $timeend = 0;
                        $manual = enrol_get_plugin('manual');
                        $roleid=$DB->get_field('role','id',array('shortname'=>'teacher'));
                        $instance = $DB->get_record('enrol', array('courseid' => $courseid, 'enrol' => 'manual'), '*', MUST_EXIST);
                        $manual->enrol_user($instance, $getdata->orguserid, $roleid, $timestart, $timeend); 

                        // Creating seperate group for organization - Starts//
                        $offeringrecord =$DB->get_record('tp_offerings',['id' => $getdata->fieldid]);  
                        $orgshortcode=$DB->get_field_sql('
                            SELECT lorg.shortname FROM {local_organization} AS lorg 
                            JOIN {local_users} AS lou ON lou.organization = lorg.id 
                            WHERE lou.userid ='.$getdata->orguserid.''
                        ); 
                        $groupdata = new stdClass;
                        if($orgshortcode)   {
                            $groupdata->name = $orgshortcode.$offeringrecord->code;
                            $groupdata->idnumber = $orgshortcode.$offeringrecord->code;
                        } else {
                            $groupdata->name = $USER->id.'_'.$offeringrecord->code;
                            $groupdata->idnumber =  $USER->id.'_'.$offeringrecord->code;
                        }
                        $groupdata->courseid= $courseid;
                        if(!groups_get_group_by_idnumber($courseid, $groupdata->idnumber)){
                            $orggroupid = groups_create_group($groupdata);
                        }
                        

                        if($orggroupid) {
                            groups_add_member($orggroupid,$getdata->orguserid,null,0);
                        } 

                        $group = $DB->get_record_sql("SELECT grop.id FROM {groups} as grop JOIN {tp_offerings} as tpo ON tpo.code = grop.idnumber WHERE tpo.id = $getdata->fieldid");
                        $groupid = (int) $group->id;
                        if ($groupid) {
                            groups_add_member($groupid,$getdata->orguserid,null,0);
                        }                    
                    }
                    if($getdata->tablename == 'local_events') {
                        $orderid = $getdata->id;
                        $DB->execute('UPDATE {local_event_attendees} SET enrolstatus=1 WHERE orderid ='.$orderid);
                      
                        $eventenrollment = $DB->get_record('local_event_attendees',array('orderid'=> $orderid,'eventid'=> $getdata->fieldid));
                        $events = $DB->get_record('local_events',array('id'=> $eventenrollment->eventid));
         
                        $localuserrecord = $DB->get_record('local_users',array('userid'=>$eventenrollment->userid));
                        if ($localuserrecord->lang == 'ar') {
                            $events->event_name = $DB->get_field('local_events','titlearabic',array('id'=> $events->id));               
                   
                        } else {
                            $events->event_name=$DB->get_field('local_events','title',array('id'=>$events->id));               
                           
                        } 
                        $events->event_userfullname= ($localuserrecord)? (($localuserrecord->lang == 'ar') ? $localuserrecord->firstnamearabic.' '.$localuserrecord->middlenamearabic.' '.$localuserrecord->thirdnamearabic.' '.$localuserrecord->lastnamearabic  : $localuserrecord->firstname.' '.$localuserrecord->middlenameen.' '.$localuserrecord->thirdnameen.' '.$localuserrecord->lastname) :fullname($userrecord);       
                        $user = $DB->get_record('user', ['id' => $eventenrollment->userid]);
                        $fromuser =  $DB->get_record('user', ['id' => $getdata->orguserid]);                   

                        (new \local_events\notification())->event_notification('events_registration', $touser=$user,$fromuser , $events,$waitinglistid=0);

                    }
                }
                return $data;
            }else{
                $data->timecreated=time();
                $data->usercreated=$USER->id;
                $data->purchasedseats = $data->purchasedseats;
                $data->approvalseats = 0;
                $data->realuser = ($USER->realuser) ? $USER->realuser :0;
                $id=$DB->insert_record('tool_order_approval_seats', $data);
                if($data->tablename=='local_exam_profiles') {
                    $traineeids = explode(',', base64_decode($data->tuserid));
                    foreach ($traineeids as $traineeid) {
                        $examid = $DB->get_field('local_exam_profiles', 'examid', ['id'=>$data->fieldid]);
                        $recordid = $DB->get_field('exam_enrollments', 'id', ['examid'=>$examid, 'userid'=>$traineeid]);

                        $sql = "SELECT leu.id  
                                FROM {local_exam_userhallschedules} leu
                                WHERE leu.profileid = $data->fieldid AND leu.userid = $traineeid 
                                ORDER BY leu.id DESC ";
                        $scheduleid = $DB->get_field_sql($sql);
                        if($data->autoapproval==0) {
                            $DB->update_record('exam_enrollments', ['id'=>$recordid, 'orderid'=>$id, 'enrolstatus'=> 0]);
                            $DB->update_record('local_exam_userhallschedules', ['id'=>$scheduleid, 'orderid'=>$id, 'enrolstatus'=> 0]);
                        }
                        if($data->autoapproval==1) {
                            $DB->update_record('exam_enrollments', ['id'=>$recordid, 'orderid'=>$id]);
                            $DB->update_record('local_exam_userhallschedules', ['id'=>$scheduleid, 'orderid'=>$id]);
                        }
                    }
                }

                return $data;
            }
        } catch (dml_exception $e) {
            print_error($e);
        }
        return true;
    }
    public function get_button_order_seats($label, $tablename, $fieldname, $fieldid, $availableseats,$parentfieldid=0, $action='booknow',$grouped=0){

        global $DB, $USER, $OUTPUT, $CFG,$PAGE;

        $sitecontext = \context_system::instance();

        $PAGE->set_context($sitecontext);

        $renderer = $PAGE->get_renderer('tool_product');


        if((!is_siteadmin()) && (has_capability('local/organization:manage_organizationofficial', $sitecontext))) {

            require_login();
            $PAGE->set_context($sitecontext);

            if (!$label) {
                $label = get_string('book','tool_product');
            } 

            if($grouped){

               
                $summary =['label'=>$label,'action'=>"seatreservation",'tablename'=>$tablename,'fieldname'=>$fieldname,'fieldid'=>$fieldid,'availableseats'=>$availableseats,'grouped'=>$grouped];

                $params['seatsdata'] =  base64_encode(serialize($summary));
                $params['label'] =  $label;
                $params['action'] =  $summary['action'].$summary['fieldid'];
                $params['id'] =  $fieldid;

                $button=$OUTPUT->render_from_template('tool_product/learningtrack_button_order_seats', $params);
            }else{
      

                $getdata=$DB->get_record('tool_org_order_seats',  array('tablename'=>$tablename,'fieldname'=>$fieldname,'fieldid'=>$fieldid,'orguserid'=>$USER->id),'id,purchasedseats,availableseats,approvalseats');

                if($getdata && $action=='enrol'){

                    if($getdata->purchasedseats >= $availableseats){

                        $button=get_string('limitreached','tool_product');
                    }


                    if($getdata->approvalseats > 0){

                        $traineeeid = $DB->get_field('role', 'id', array('shortname' => 'trainee'));

                        $getdata->tablename=$parentfieldid ? 'local_trainingprogram' : $tablename;

                        $getdata->trainingid=$parentfieldid ? $parentfieldid : $fieldid;

                        $getdata->traineeeid=$traineeeid;

                        $getdata->fieldid=$fieldid;
                        $button =$renderer->product_enrollseats($getdata);

                    }


                }else{

                    if($availableseats < 1){

                        $button=get_string('noseatsavailable','tool_product');

                    }else{
                        
                        $summary =['label'=>$label,'action'=>"seatreservation",'tablename'=>$tablename,'fieldname'=>$fieldname,'fieldid'=>$fieldid,'availableseats'=>$availableseats,'parentfieldid'=>$parentfieldid];

                        $params['seatsdata'] =  base64_encode(serialize($summary));
                        $params['label'] =  $label;
                        $params['action'] =  $summary['action'].$summary['fieldid'];
                        $params['id'] =  $fieldid;

                        $sql = "SELECT lc.autoapproval 
                                  FROM {local_organization} lc
                                  JOIN {local_users} lu ON lu.organization = lc.id 
                                 WHERE lu.userid =". $USER->id;
                        $params['autoapproval'] = $DB->get_field_sql($sql);
                        $button=$OUTPUT->render_from_template('tool_product/button_order_seats', $params);
                        
                    }
                 }
            }


            return $button;
        }

        return '';
    }
    public function availableseats_check($tablename, $fieldname, $fieldid){

        global $DB, $USER;

        $sitecontext = \context_system::instance();
        
        if((!is_siteadmin()) && (has_capability('local/organization:manage_organizationofficial', $sitecontext))) {
            $get_all_orgofficials = (new \local_exams\local\exams())->get_all_orgofficials($USER->id);
            $orgusers = implode(',',$get_all_orgofficials);
            $getdata=$DB->get_field_sql(" SELECT availableseats as totalavailableseats FROM {tool_org_order_seats} 
            JOIN {local_users} lu ON lu.userid = orguserid WHERE tablename =:tablename AND fieldname =:fieldname
            AND fieldid =:fieldid AND orguserid IN ($orgusers) AND lu.deleted = 0", array('tablename'=>$tablename,'fieldname'=>$fieldname,'fieldid'=>$fieldid/*,'orguserid'=>$organization*/));

            // $orgid = $DB->get_field('local_users', 'organization', ['userid' => $USER->id]);
            // $getdata = $DB->get_field_sql(" SELECT toas.availableseats as totalavailableseats FROM {tool_org_order_seats} as toas
            // JOIN {local_users} lu ON lu.userid = toas.orguserid WHERE toas.tablename = :tablename AND toas.fieldname = :fieldname
            // AND toas.fieldid = :fieldid AND toas.organization = '$orgid' AND lu.deleted = 0", array('tablename'=>$tablename,'fieldname'=>$fieldname,'fieldid'=>$fieldid));
            return $getdata ? $getdata : 0;

        }else{

            $getdata=$DB->get_field_sql("SELECT sum(availableseats) as totalavailableseats FROM {tool_org_order_seats} WHERE tablename =:tablename AND fieldname =:fieldname AND fieldid =:fieldid", array('tablename'=>$tablename,'fieldname'=>$fieldname,'fieldid'=>$fieldid));
        
            return $getdata ? $getdata : 0;

        }
    }
    public function approvedseats_check($tablename, $fieldname, $fieldid,$userid=0){

        global $DB, $USER;

        $sitecontext = \context_system::instance();

        if(((!is_siteadmin()) && (has_capability('local/organization:manage_organizationofficial', $sitecontext))) || $userid) {
            if($userid == 0){

                $userid=$USER->id;
                
            }
            $orgs = $DB->get_fieldset_sql("SELECT DISTINCT organization FROM {tool_order_approval_seats} 
                            WHERE orguserid = $USER->id AND organization != (SELECT organization FROM {local_users} WHERE userid = $USER->id ) ");
            $orgs = implode(',', $orgs);
            $is_org = '';
            if ($orgs) {
                $is_org = "  OR toas.organization IN ($orgs)";
            }
            $get_all_orgofficials = (new \local_exams\local\exams())->get_all_orgofficials($userid);
            $orgusers = implode(',',$get_all_orgofficials);
            $getdata=$DB->get_field_sql(" SELECT sum(toas.approvalseats) as totalapprovalseats FROM {tool_order_approval_seats} toas
            JOIN {local_users} lu ON lu.userid = toas.orguserid 
            WHERE toas.tablename =:tablename AND toas.fieldname =:fieldname AND toas.fieldid =:fieldid 
            AND (toas.orguserid IN ($orgusers) $is_org) AND lu.deleted = 0", array('tablename'=>$tablename,'fieldname'=>$fieldname,'fieldid'=>$fieldid/*'orguserid'=>$userid*/));

            // $orgid = $DB->get_field('local_users', 'organization', ['userid' => $USER->id]);
            // $getdata = $DB->get_field_sql(" SELECT toas.availableseats as totalavailableseats FROM {tool_org_order_seats} as toas
            // JOIN {local_users} lu ON lu.userid = toas.orguserid WHERE toas.tablename = :tablename AND toas.fieldname = :fieldname
            // AND toas.fieldid = :fieldid AND toas.orguserid IN ($orgusers) AND lu.deleted = 0", array('tablename'=>$tablename,'fieldname'=>$fieldname,'fieldid'=>$fieldid));

            return $getdata ? $getdata : 0;

        }else{

            $getdata=$DB->get_field_sql("SELECT sum(approvalseats) as totalapprovalseats FROM {tool_org_order_seats} WHERE tablename =:tablename AND fieldname =:fieldname AND fieldid =:fieldid", array('tablename'=>$tablename,'fieldname'=>$fieldname,'fieldid'=>$fieldid));

        
            return $getdata ? $getdata : 0;

        }
    }    
    public function purchasedseats_check($tablename, $fieldname, $fieldid){
        global $DB, $USER;
        $sitecontext = context_system::instance();
        if((!is_siteadmin()) && (has_capability('local/organization:manage_organizationofficial', $sitecontext))) {
            $get_all_orgofficials = (new exams())->get_all_orgofficials($USER->id);
            $orgs = $DB->get_fieldset_sql("SELECT DISTINCT organization FROM {tool_order_approval_seats} 
                            WHERE orguserid = $USER->id AND organization != (SELECT organization FROM {local_users} WHERE userid = $USER->id ) ");
            $orgs = implode(',', $orgs);
            $is_org = '';
            if ($orgs) {
                $is_org = "  OR toas.organization IN ($orgs)";
            }
            $orgusers = implode(',',$get_all_orgofficials);
            $getdata = $DB->get_field_sql(" SELECT sum(toas.purchasedseats) as totalpurchasedseats FROM {tool_order_approval_seats} as toas
            JOIN {local_users} lu ON lu.userid = toas.orguserid
            WHERE toas.tablename =:tablename AND toas.fieldname =:fieldname AND toas.fieldid =:fieldid
            AND (toas.orguserid IN ($orgusers) $is_org) AND lu.deleted = 0", array('tablename'=>$tablename,'fieldname'=>$fieldname,'fieldid'=>$fieldid/*,'orguserid'=>$USER->id*/));
            // print_r($orgusers);die;
            // $orgid = $DB->get_field('local_users', 'organization', ['userid' => $USER->id]);
            // $getdata = $DB->get_field_sql(" SELECT toas.availableseats as totalavailableseats FROM {tool_org_order_seats} as toas
            // JOIN {local_users} lu ON lu.userid = toas.orguserid WHERE toas.tablename = :tablename AND toas.fieldname = :fieldname
            // AND toas.fieldid = :fieldid AND toas.orguserid IN ($orgusers) OR toas.orguserid = $USER->id  AND lu.deleted = 0", array('tablename'=>$tablename,'fieldname'=>$fieldname,'fieldid'=>$fieldid));

            return $getdata ? $getdata : 0;

        }else{
            $getdata=$DB->get_field_sql("SELECT sum(purchasedseats) as totalpurchasedseats FROM {tool_org_order_seats} WHERE tablename =:tablename AND fieldname =:fieldname AND fieldid =:fieldid", array('tablename'=>$tablename,'fieldname'=>$fieldname,'fieldid'=>$fieldid));

            return $getdata ? $getdata : 0;
        }
    }
    public function upadte_availableseats($tablename, $fieldname, $fieldid, $redeemseats, $enrolled_by_user = null){


        global $DB, $USER;

        if(!is_null($enrolled_by_user) && !empty($enrolled_by_user)) {

            $orguser = $enrolled_by_user;

        } else {

            $orguser = $USER->id;

        }

        try {

            $getdata=$DB->get_record('tool_org_order_seats',  array('tablename'=>$tablename,'fieldname'=>$fieldname,'fieldid'=>$fieldid,'orguserid'=>$orguser),'id,purchasedseats,availableseats');

            if($getdata){
                $data = new stdClass;
                $data->id=$getdata->id;
                $data->availableseats = $getdata->availableseats+($redeemseats);
                $data->timemodified=time();
                $data->usermodified=$USER->id;
                $data->realuser = ($USER->realuser) ? $USER->realuser :0;
                $id=$DB->update_record('tool_org_order_seats', $data);
 
            }
        } catch (dml_exception $e) {
            print_error($e);

        }
        return true;
    }
    public function orders_prepaid_paymentsupdate($data) {
        global $DB, $USER;
        $context = context_system::instance();
        try {
            $userwallet=(new \local_userapproval\action\manageuser)::get_user_wallet($data->orguserid);
            if($userwallet->id > 0 && $userwallet->wallet >= $data->amount){
                $tabledata=new \stdClass();
                $tabledata->wallet =$userwallet->wallet-$data->amount;
                $tabledata->id=$userwallet->id;
                $tabledata->timemodified=time();
                $tabledata->usermodified=$USER->id;
                
                if (!is_siteadmin() && has_capability('local/organization:manage_trainee', $context)) {
                    $tabledata->roleid=$DB->get_field('role', 'id', ['shortname' => 'trainee']);
                    $id = $DB->update_record('trainee_wallet', $tabledata);

                    $eventparams = array('context' => \context_system::instance(),'objectid'=>$id,'other'=>array('paymentstatus'=>'deduct'));
                    $event = \tool_product\event\trainee_wallet::create($eventparams);// ... code that may add some record snapshots
                    $event->trigger();
                } elseif(!is_siteadmin() && has_capability('local/organization:manage_organizationofficial', $context)) {
                    $DB->update_record('local_orgofficial_wallet', $tabledata);
                }

                return true;
            }else{
                return false;
            }
        } catch (dml_exception $e) {
            print_error($e);
        }
        return false;
    }
    public static function get_post_financialpayments($stable,$filterdata=null) {
        global $DB, $USER;

        $params          = array();
        $payments      = array();
        $paymentscount = 0;
        $totalcost = 0;
 
        $countsql = "SELECT COUNT(*) ";

        $totalcostql = "SELECT SUM(ordrpmnt.payableamount) ";


        $fromsql = " SELECT ordrpmnt.id,ordrpmnt.trainingname,ordrpmnt.organization,ordrpmnt.tablename,ordrpmnt.fieldname,ordrpmnt.fieldid,ordrpmnt.trainingid,ordrpmnt.availablefrom,ordrpmnt.purchasedseats,ordrpmnt.orguserid,ordrpmnt.availableto,ordrpmnt.payableamount  ";

        $fromsql.=',ordrpmnt.amount,ordrpmnt.amountrecived,ordrpmnt.transactionid,ordrpmnt.transactionref,ordrpmnt.checkid,ordrpmnt.transactionnote,ordrpmnt.paymenttype';


        $sql = " FROM ( ";

        $stable->selectparams=',tppmnt.amount,tppmnt.amountrecived,tppmnt.transactionid,tppmnt.transactionref,tppmnt.checkid,tppmnt.transactionnote,tppmnt.paymenttype';


        $stable->tablename='tool_org_order_payments';


        $plugins = get_plugins_with_function('product_orders');

        $i=1;

        foreach ($plugins as $plugintype => $funcs) {
            foreach ($funcs as $pluginname => $functionname) {


                $comporders = call_user_func_array($functionname, [$stable,$filterdata]);

                if (!$comporders) {
                    continue;
                }

                $params = array_merge($params,$comporders['params']);

                if($i > 1){


                    $sql.="UNION ";

                }

                $sql.="(".$comporders['sql'].")";

                $i++;

            }
        } 

        $sql.= " ) AS ordrpmnt WHERE ordrpmnt.id > 0 AND EXISTS (SELECT ordrapr.id FROM {tool_org_order_seats} AS ordrapr WHERE ordrapr.tablename=ordrpmnt.tablename AND ordrapr.fieldname=ordrpmnt.fieldname AND ordrapr.fieldid=ordrpmnt.fieldid AND ordrapr.orguserid=ordrpmnt.orguserid AND ordrapr.approvalseats > 0) > 0";

        if (!empty($filterdata->search_query)) {

            $sql .= " AND (ordrpmnt.englishname LIKE :search1 OR ordrpmnt.arabicname LIKE :search2 OR ordrpmnt.amount LIKE :search3 OR ordrpmnt.ofrcode LIKE :search4 OR ordrpmnt.tpcode LIKE :search5 OR (EXISTS (SELECT lc.userid 
                FROM {local_users} lc 
                JOIN {local_organization} org ON org.id = lc.organization
                WHERE org.shortname LIKE :search6 AND lc.userid=ordrpmnt.orguserid) > 0) ) ";

            $params['search1'] = '%'.trim($filterdata->search_query).'%';
            $params['search2'] = '%'.trim($filterdata->search_query).'%';
            $params['search3'] = '%'.trim($filterdata->search_query).'%';
            $params['search4'] = '%'.trim($filterdata->search_query).'%';
            $params['search5'] = '%'.trim($filterdata->search_query).'%';
            $params['search6'] = '%'.trim($filterdata->search_query).'%';

        }


        if (isset($stable->id) && $stable->id > 0) {
            $sql .= " AND ordrpmnt.id = :ordrpmnt ";
            $params['ordrpmnt'] = $stable->id;
        }  

        if(!empty($filterdata->trainingprograms)){

            if(!empty($filterdata->trainingprograms)){
                $trainingprogramslist = explode(',',$filterdata->trainingprograms);
            }

            list($relatedtrainingprogramslistsql, $relatedtrainingprogramslistparams) = $DB->get_in_or_equal($trainingprogramslist, SQL_PARAMS_NAMED, 'trainingprogramslist');
            $params = array_merge($params,$relatedtrainingprogramslistparams);


            $sql .= " AND EXISTS (SELECT tpofr.id 
             FROM {tp_offerings} AS tpofr 
             JOIN {local_trainingprogram} AS tp ON tpofr.trainingid=tp.id
             WHERE tp.id $relatedtrainingprogramslistsql AND tpofr.id=ordrpmnt.fieldid) > 0 AND ordrpmnt.tablename='tp_offerings'";


        }    


        if(!empty($filterdata->offeringtype)){

            if(!empty($filterdata->offeringtype)){
                $trainingprogramslist = explode(',',$filterdata->offeringtype);
            }

            list($relatedtrainingprogramslistsql, $relatedtrainingprogramslistparams) = $DB->get_in_or_equal($trainingprogramslist, SQL_PARAMS_NAMED, 'trainingprogramslist');
            $params = array_merge($params,$relatedtrainingprogramslistparams);


            $sql .= " AND EXISTS (SELECT tpofr.id 
             FROM {tp_offerings} AS tpofr 
             JOIN {local_trainingprogram} AS tp ON tpofr.trainingid=tp.id
             WHERE tpofr.trainingmethod $relatedtrainingprogramslistsql AND tpofr.id=ordrpmnt.fieldid) > 0 AND ordrpmnt.tablename='tp_offerings'";


        }  

        if (!empty($filterdata->offeringstatus)){ 
            $trainings = explode(',',$filterdata->offeringstatus);
             if(!empty($trainings)){
                $trainingquery = array();
                foreach ($trainings as $training) {

                   if($training == 'completed') {

                     $trainingquery[] = " EXISTS (SELECT tpofr.id 
                     FROM {tp_offerings} AS tpofr 
                     JOIN {local_trainingprogram} AS tp ON tpofr.trainingid=tp.id
                     JOIN {program_completions} AS pc ON pc.programid=tp.id
                     WHERE pc.completion_status =1 AND tpofr.id=ordrpmnt.fieldid) > 0 "; 

                   } elseif($training == 'financially_closed') {
                    $expired =  (new trainingprogram())->from_unixtime_for_expired_entities('tpofr.enddate');
                     $trainingquery[] = "  EXISTS (SELECT tpofr.id 
                     FROM {tp_offerings} AS tpofr 
                     JOIN {local_trainingprogram} AS tp ON tpofr.trainingid=tp.id
                     WHERE $expired AND tpofr.id=ordrpmnt.fieldid) > 0 "; 
                   }

                }
                $trainingqueeryparams =implode('OR',$trainingquery);
                $sql .= ' AND ('.$trainingqueeryparams.') AND ordrpmnt.tablename="tp_offerings" ';
            }
        } 

        if(!empty($filterdata->offeringlocation)){

            if(!empty($filterdata->offeringlocation)){
                $trainingprogramslist = explode(',',$filterdata->offeringlocation);
            }

            list($relatedtrainingprogramslistsql, $relatedtrainingprogramslistparams) = $DB->get_in_or_equal($trainingprogramslist, SQL_PARAMS_NAMED, 'trainingprogramslist');
            $params = array_merge($params,$relatedtrainingprogramslistparams);


            $sql .= " AND EXISTS (SELECT tpofr.id 
             FROM {tp_offerings} AS tpofr 
             JOIN {local_trainingprogram} AS tp ON tpofr.trainingid=tp.id
             WHERE tpofr.halllocation $relatedtrainingprogramslistsql AND tpofr.id=ordrpmnt.fieldid) > 0 AND ordrpmnt.tablename='tp_offerings'";


        }

        if(!empty($filterdata->useridnumber)){

            if(!empty($filterdata->useridnumber)){
                $organizationlist = explode(',',$filterdata->useridnumber);
            }

            list($relatedorganizationlistsql, $relatedorganizationlistparams) = $DB->get_in_or_equal($organizationlist, SQL_PARAMS_NAMED, 'organizationlist');
            $params = array_merge($params,$relatedorganizationlistparams);

            $sql .= " AND EXISTS (SELECT lc.userid 
                FROM {local_users} lc 
                JOIN {local_organization} org ON org.id = lc.organization
                WHERE lc.userid $relatedorganizationlistsql AND lc.userid=ordrpmnt.orguserid) > 0";

        }

        if(!empty($filterdata->exams)){

            if(!empty($filterdata->exams)){
                $examslist = explode(',',$filterdata->exams);
            }

            list($relatedexamslistsql, $relatedexamslistparams) = $DB->get_in_or_equal($examslist, SQL_PARAMS_NAMED, 'examslist');
            $params = array_merge($params,$relatedexamslistparams);
        

            $sql .= " AND EXISTS (SELECT lep.id 
             FROM {local_exam_profiles}  lep
             JOIN {local_exams} le ON le.id = lep.examid
             WHERE le.id $relatedexamslistsql AND lep.id=ordrpmnt.fieldid) > 0 AND ordrpmnt.tablename='local_exam_profiles'";

        }  

        if(!empty($filterdata->events)){

            if(!empty($filterdata->events)){
                $eventslist = explode(',',$filterdata->events);
            }

            list($relatedeventslistsql, $relatedeventslistparams) = $DB->get_in_or_equal($eventslist, SQL_PARAMS_NAMED, 'eventslist');
            $params = array_merge($params,$relatedeventslistparams);
            $sql .= " AND (ordrpmnt.fieldid $relatedeventslistsql AND ordrpmnt.tablename='local_events') ";

        } 

        if(!empty($filterdata->paymentmode)){

            if(!empty($filterdata->paymentmode)){
                $paymentmodelist = explode(',',$filterdata->paymentmode);
            }
    
            list($relatedpaymentmodelistsql, $relatedpaymentmodelistparams) = $DB->get_in_or_equal($paymentmodelist, SQL_PARAMS_NAMED, 'paymentmodelist');
            $params = array_merge($params,$relatedpaymentmodelistparams);
            $sql .= " AND ordrpmnt.paymenttype $relatedpaymentmodelistsql";

        }

        if(!empty($filterdata->organization)){

            if(!empty($filterdata->organization)){
                $organizationlist = explode(',',$filterdata->organization);
            }

            list($relatedorganizationlistsql, $relatedorganizationlistparams) = $DB->get_in_or_equal($organizationlist, SQL_PARAMS_NAMED, 'organizationlist');
            $params = array_merge($params,$relatedorganizationlistparams);

            $sql .= " AND EXISTS (SELECT lc.userid 
                FROM {local_users} lc 
                JOIN {local_organization} org ON org.id = lc.organization
                WHERE lc.organization $relatedorganizationlistsql AND lc.userid=ordrpmnt.orguserid) > 0";

        }

        if($filterdata->{'betweendaterangefrom[enabled]'} == 1 ){

            $start_year = $filterdata->{'betweendaterangefrom[year]'};
            $start_month = $filterdata->{'betweendaterangefrom[month]'};
            $start_day = $filterdata->{'betweendaterangefrom[day]'};
            $filter_starttime_con = mktime(0,0,0, $start_month, $start_day, $start_year);
            $sql.= " AND ordrpmnt.availablefrom >= '$filter_starttime_con' ";

        }
        if($filterdata->{'betweendaterangeto[enabled]'} == 1 ){
            $start_year = $filterdata->{'betweendaterangeto[year]'};
            $start_month = $filterdata->{'betweendaterangeto[month]'};
            $start_day = $filterdata->{'betweendaterangeto[day]'};
            $filter_endtime_con=mktime(23,59,59, $start_month, $start_day, $start_year);
            $sql.=" AND ordrpmnt.availablefrom <= '$filter_endtime_con' ";
        }

        if (isset($stable->id) && $stable->id > 0) {
            $payments = $DB->get_record_sql($fromsql . $sql, $params);
        } else {
            try {

                $sql.= "  ORDER BY ordrpmnt.id DESC";


                $paymentscount = $DB->get_field_sql($countsql. $sql, $params);

                $paymentscount = $paymentscount ? $paymentscount : 0;


                $totalcost = $DB->get_field_sql($totalcostql. $sql, $params);

                $totalcost = $totalcost ? $totalcost : 0;


                if ($stable->thead == false) {


                    $payments = $DB->get_records_sql($fromsql . $sql, $params, $stable->start, $stable->length);

                }
            } catch (dml_exception $ex) {
                $paymentscount = 0;
            }
        }
        if (isset($stable->id) && $stable->id > 0) {
            return $payments;
        } else {
            return compact('payments', 'paymentscount','totalcost');
        }
    }
    public static function get_orders_approval($stable,$filterdata=null) {
        global $DB, $USER;
        $params = array();
        $orders = array();
        $orderscount = 0;
        $countsql = "SELECT COUNT(*) ";

        $fromsql = " SELECT ordrpmnt.id,ordrpmnt.trainingname,ordrpmnt.organization,ordrpmnt.tablename,ordrpmnt.fieldname,ordrpmnt.ofrcode as offercode,
                            ordrpmnt.fieldid,ordrpmnt.trainingid,ordrpmnt.availablefrom,ordrpmnt.purchasedseats,ordrpmnt.orguserid, ordrpmnt.paymentid ";
        $fromsql.=',ordrpmnt.approvalseats';
        $sql = " FROM ( ";
        $stable->selectparams=',tppmnt.approvalseats';
        $stable->tablename='tool_order_approval_seats';
        $plugins = get_plugins_with_function('product_orders');

        $i=1;

        foreach ($plugins as $plugintype => $funcs) {
            foreach ($funcs as $pluginname => $functionname) {
                $comporders = call_user_func_array($functionname, [$stable,$filterdata]);
                if (!$comporders) {
                    continue;
                }
                $params = array_merge($params,$comporders['params']);
                if($i > 1){
                    $sql.="UNION ";
                }
                $sql.="(".$comporders['sql'].")";
                $i++;
            }
        } 

        $sql.= " ) AS ordrpmnt WHERE ordrpmnt.id > 0 ";
        if (!empty($filterdata->search_query)) {
            $sql .= " AND (ordrpmnt.englishname LIKE :search1 OR ordrpmnt.arabicname LIKE :search2  OR ordrpmnt.ofrcode LIKE :search4 OR ordrpmnt.tpcode LIKE :search5) ";
            $params['search1'] = '%'.trim($filterdata->search_query).'%';
            $params['search2'] = '%'.trim($filterdata->search_query).'%';
            $params['search4'] = '%'.trim($filterdata->search_query).'%';
            $params['search5'] = '%'.trim($filterdata->search_query).'%';
           
        }


        if (isset($stable->id) && $stable->id > 0) {
            $sql .= " AND ordrpmnt.id = :ordrpmnt ";
            $params['ordrpmnt'] = $stable->id;
        }  

        if(!empty($filterdata->trainingprograms)){

            if(!empty($filterdata->trainingprograms)){
                $trainingprogramslist = explode(',',$filterdata->trainingprograms);
            }

            list($relatedtrainingprogramslistsql, $relatedtrainingprogramslistparams) = $DB->get_in_or_equal($trainingprogramslist, SQL_PARAMS_NAMED, 'trainingprogramslist');
            $params = array_merge($params,$relatedtrainingprogramslistparams);
        

               $sql .= " AND EXISTS (SELECT tpofr.id 
             FROM {tp_offerings} AS tpofr 
             JOIN {local_trainingprogram} AS tp ON tpofr.trainingid=tp.id
             WHERE tp.id $relatedtrainingprogramslistsql AND tpofr.id=ordrpmnt.fieldid) > 0 AND ordrpmnt.tablename='tp_offerings'";

        }     

        if(!empty($filterdata->exams)){

            if(!empty($filterdata->exams)){
                $examslist = explode(',',$filterdata->exams);
            }

            list($relatedexamslistsql, $relatedexamslistparams) = $DB->get_in_or_equal($examslist, SQL_PARAMS_NAMED, 'examslist');
            $params = array_merge($params,$relatedexamslistparams);
            

            $sql .= " AND EXISTS (SELECT lep.id 
             FROM {local_exam_profiles}  lep
             JOIN {local_exams} le ON le.id = lep.examid
             WHERE le.id $relatedexamslistsql AND lep.id=ordrpmnt.fieldid) > 0 AND ordrpmnt.tablename='local_exam_profiles'";

        }  

        if(!empty($filterdata->events)){

            if(!empty($filterdata->events)){
                $eventslist = explode(',',$filterdata->events);
            }

            list($relatedeventslistsql, $relatedeventslistparams) = $DB->get_in_or_equal($eventslist, SQL_PARAMS_NAMED, 'eventslist');
            $params = array_merge($params,$relatedeventslistparams);
            $sql .= " AND (ordrpmnt.fieldid $relatedeventslistsql AND ordrpmnt.tablename='local_events') ";

        } 

        if(!empty($filterdata->paymentmode)){

            if(!empty($filterdata->paymentmode)){
                $paymentmodelist = explode(',',$filterdata->paymentmode);
            }
    
            list($relatedpaymentmodelistsql, $relatedpaymentmodelistparams) = $DB->get_in_or_equal($paymentmodelist, SQL_PARAMS_NAMED, 'paymentmodelist');
            $params = array_merge($params,$relatedpaymentmodelistparams);
            $sql .= " AND ordrpmnt.paymenttype $relatedpaymentmodelistsql";

        }

        if(!empty($filterdata->organization)){

            if(!empty($filterdata->organization)){
                $organizationlist = explode(',',$filterdata->organization);
            }

            list($relatedorganizationlistsql, $relatedorganizationlistparams) = $DB->get_in_or_equal($organizationlist, SQL_PARAMS_NAMED, 'organizationlist');
            $params = array_merge($params,$relatedorganizationlistparams);

            $sql .= " AND EXISTS (SELECT lc.userid 
                FROM {local_users} lc 
                JOIN {local_organization} org ON org.id = lc.organization
                WHERE lc.organization $relatedorganizationlistsql AND lc.userid=ordrpmnt.orguserid) > 0";

        }

        if($filterdata->{'betweendaterangefrom[enabled]'} == 1 ){

            $start_year = $filterdata->{'betweendaterangefrom[year]'};
            $start_month = $filterdata->{'betweendaterangefrom[month]'};
            $start_day = $filterdata->{'betweendaterangefrom[day]'};
            $filter_starttime_con = mktime(0,0,0, $start_month, $start_day, $start_year);
            $sql.= " AND ordrpmnt.availablefrom >= '$filter_starttime_con' ";

        }
        if($filterdata->{'betweendaterangeto[enabled]'} == 1 ){
            $start_year = $filterdata->{'betweendaterangeto[year]'};
            $start_month = $filterdata->{'betweendaterangeto[month]'};
            $start_day = $filterdata->{'betweendaterangeto[day]'};
            $filter_endtime_con=mktime(23,59,59, $start_month, $start_day, $start_year);
            $sql.=" AND ordrpmnt.availablefrom <= '$filter_endtime_con' ";
        }


        if (isset($stable->id) && $stable->id > 0) {
            $orders = $DB->get_record_sql($fromsql . $sql, $params);
        } else {
            try {

                $sql.= "  ORDER BY ordrpmnt.id DESC";

                $orderscount = $DB->get_field_sql($countsql. $sql, $params);

                $orderscount = $orderscount ? $orderscount : 0;


                if ($stable->thead == false) {

                    $orders = $DB->get_records_sql($fromsql . $sql, $params, $stable->start, $stable->length);
                }
            } catch (dml_exception $ex) {
                $orderscount = 0;
            }
        }
        if (isset($stable->id) && $stable->id > 0) {
            return $orders;
        } else {
            return compact('orders', 'orderscount');
        }
    }
    public static function get_org_purchases($stable,$filterdata=null) {
        global $DB, $USER;

        $params          = array();
        $orders      = array();
        $orderscount = 0;
 
        $countsql = "SELECT COUNT(DISTINCT(ordrpmnt.fieldid)) ";


        $fromsql = " SELECT ordrpmnt.id,ordrpmnt.trainingname,ordrpmnt.organization,ordrpmnt.tablename,ordrpmnt.fieldname,ordrpmnt.fieldid,ordrpmnt.trainingid,ordrpmnt.availablefrom,ordrpmnt.availableto,ordrpmnt.purchasedseats,ordrpmnt.orguserid ";

        $fromsql.=',ordrpmnt.approvalseats,ordrpmnt.availableseats';

        $sql = " FROM ( ";

        $stable->selectparams=',tppmnt.approvalseats,tppmnt.availableseats';

        $stable->orguserid=$USER->id;


        $stable->tablename='tool_org_order_seats';


        $plugins = get_plugins_with_function('product_orders');

        $i=1;

        foreach ($plugins as $plugintype => $funcs) {
            foreach ($funcs as $pluginname => $functionname) {

                if($filterdata->status === ($plugintype._.$pluginname)){

                    $comporders = call_user_func_array($functionname, [$stable,$filterdata]);

                    if (!$comporders) {
                        continue;
                    }

                    $params = array_merge($params,$comporders['params']);

                    if($i > 1){


                        $sql.="UNION ";

                    }

                    $sql.="(".$comporders['sql'].")";

                    $i++;

                }else{
                    continue;
                }


            }
        }

        // $sql .= "
        //      UNION  

        //      SELECT tpofr.id,tp.name as trainingname,tpofr.organization,'tp_offerings' as tablename,'id' as fieldname,tpofr.id as fieldid,tp.id as trainingid, (tpofr.startdate + tpofr.time) as availablefrom,tpofr.enddate as availableto,tpofr.availableseats as purchasedseats,$USER->id as orguserid,tp.name as englishname,tp.namearabic as arabicname,tpofr.code as ofrcode,tp.code as tpcode ,tpofr.availableseats as approvalseats,tpofr.availableseats as availableseats FROM  {tp_offerings} AS tpofr 
        //      JOIN {local_trainingprogram} AS tp ON tpofr.trainingid=tp.id
        //      WHERE 1=1 AND IF(tp.price=1, tp.id = -1 , tp.id IN (SELECT programid FROM mdl_program_enrollments WHERE programid = tp.id AND offeringid = tpofr.id AND usercreated = $USER->id)) " ;
      
        $sql.= " ) AS ordrpmnt " ;
        if($filterdata->status == 'local_exams') {
           if(!empty($filterdata->favourites) ){
            $sql .= " JOIN {local_exam_profiles} ep ON ep.id = ordrpmnt.fieldid
            JOIN {favourite} AS fav ON fav.itemid = ep.examid AND fav.component= 'local_exams' AND fav.userid = ordrpmnt.orguserid";
            }
        }

        if($filterdata->status == 'local_trainingprogram') {
           if(!empty($filterdata->favourites) ){
            $sql .= " JOIN {tp_offerings} tof ON tof.id = ordrpmnt.fieldid
            JOIN {favourite} AS fav ON fav.itemid = tof.trainingid AND fav.component = 'local_trainingprogram' AND fav.userid = ordrpmnt.orguserid";
            }
        }

        if($filterdata->status == 'local_events') {
           if(!empty($filterdata->favourites) ){
            $sql .= " JOIN {favourite} AS fav ON fav.itemid = ordrpmnt.fieldid AND fav.component='local_events' AND fav.userid = ordrpmnt.orguserid";
            }
        }
        
        $sql .=" WHERE ordrpmnt.id > 0 ";


        if (!empty($filterdata->search_query)) {

            $sql .= " AND (ordrpmnt.englishname LIKE :search1 OR ordrpmnt.arabicname LIKE :search2 OR ordrpmnt.ofrcode LIKE :search4 OR ordrpmnt.tpcode LIKE :search5) ";

            $params['search1'] = '%'.trim($filterdata->search_query).'%';
            $params['search2'] = '%'.trim($filterdata->search_query).'%';
            $params['search4'] = '%'.trim($filterdata->search_query).'%';
            $params['search5'] = '%'.trim($filterdata->search_query).'%';
           
        }


        if (isset($stable->id) && $stable->id > 0) {
            $sql .= " AND ordrpmnt.id = :ordrpmnt ";
            $params['ordrpmnt'] = $stable->id;
        }


        if (!empty($filterdata->sectors)){


             $sectorids = explode(',', $filterdata->sectors);
             if(!empty($sectorids)){
                $sectorquery = array();
                foreach ($sectorids as $sector) {
                    $sectorquery[] = " CONCAT(',',tp.sectors,',') LIKE CONCAT('%,',$sector,',%') "; 
                }
                $sectoqueeryparams =implode('OR',$sectorquery);

                $sql .= " AND EXISTS (SELECT tpofr.id 
                FROM {tp_offerings} AS tpofr 
                JOIN {local_trainingprogram} AS tp ON tpofr.trainingid=tp.id
                WHERE ($sectoqueeryparams) AND tpofr.id=ordrpmnt.fieldid) > 0 AND ordrpmnt.tablename='tp_offerings'";
            }

        
        }

        
        if(!empty($filterdata->targetgroup)){

            $jobfamilyids = explode(',', $filterdata->targetgroup);

            if(!empty($jobfamilyids)){
                $jobfamilyquery = array();
                foreach ($jobfamilyids as $jobfamily) {
                    $jobfamilyquery[] = " CONCAT(',',tp.targetgroup,',') LIKE CONCAT('%,',$jobfamily,',%') "; 
                }
                $jobfamilyparams =implode('OR',$jobfamilyquery);
                $sql .= " AND EXISTS (SELECT tpofr.id 
                FROM {tp_offerings} AS tpofr 
                JOIN {local_trainingprogram} AS tp ON tpofr.trainingid=tp.id
                WHERE (($jobfamilyparams) OR tp.targetgroup = -1) AND tpofr.id=ordrpmnt.fieldid) > 0 AND ordrpmnt.tablename='tp_offerings'";
            }
 
        }

        if(!empty($filterdata->program_competencylevel)){


            $competencylevelsids = explode(',', $filterdata->program_competencylevel);
            if(!empty($competencylevelsids)){
                $competencylevelquery = array();
                foreach ($competencylevelsids as $competencylevel) {
                    $competencylevelquery[] = " CONCAT(',',tp.competencyandlevels,',') LIKE CONCAT('%,',$competencylevel,',%') "; 
                }
                $competencylevelparams =implode('OR',$competencylevelquery);
                
                $sql .= " AND EXISTS (SELECT tpofr.id 
                FROM {tp_offerings} AS tpofr 
                JOIN {local_trainingprogram} AS tp ON tpofr.trainingid=tp.id
                WHERE ($competencylevelparams) AND tpofr.id=ordrpmnt.fieldid) > 0 AND ordrpmnt.tablename='tp_offerings'";
            }

        }
    
        if($filterdata->{'availablefrom[enabled]'} == 1 ){

            $start_year = $filterdata->{'availablefrom[year]'};
            $start_month = $filterdata->{'availablefrom[month]'};
            $start_day = $filterdata->{'availablefrom[day]'};
            $filter_starttime_con = mktime(0,0,0, $start_month, $start_day, $start_year);
           
            $sql .= " AND EXISTS (SELECT tpofr.id 
            FROM {tp_offerings} AS tpofr 
            JOIN {local_trainingprogram} AS tp ON tpofr.trainingid=tp.id
            WHERE tp.availablefrom >= '$filter_starttime_con' AND tpofr.id=ordrpmnt.fieldid) > 0 AND ordrpmnt.tablename='tp_offerings'";

        }
        if($filterdata->{'availableto[enabled]'} == 1 ){
            $start_year = $filterdata->{'availableto[year]'};
            $start_month = $filterdata->{'availableto[month]'};
            $start_day = $filterdata->{'availableto[day]'};
            $filter_endtime_con=mktime(23,59,59, $start_month, $start_day, $start_year);

            $sql .= " AND EXISTS (SELECT tpofr.id 
            FROM {tp_offerings} AS tpofr 
            JOIN {local_trainingprogram} AS tp ON tpofr.trainingid=tp.id
            WHERE tp.availableto <= '$filter_endtime_con' AND tpofr.id=ordrpmnt.fieldid) > 0 AND ordrpmnt.tablename='tp_offerings'";
        }


        if(!empty($filterdata->training_name)){

 
            $trainingprogramslist = explode(',',$filterdata->training_name);
            list($relatedtrainingprogramslistsql, $relatedtrainingprogramslistparams) = $DB->get_in_or_equal($trainingprogramslist, SQL_PARAMS_NAMED, 'trainingprogramslist');
            $params = array_merge($params,$relatedtrainingprogramslistparams);
 

            $sql .= " AND EXISTS (SELECT tpofr.id 
             FROM {tp_offerings} AS tpofr 
             JOIN {local_trainingprogram} AS tp ON tpofr.trainingid=tp.id
             WHERE tp.id $relatedtrainingprogramslistsql AND tpofr.id=ordrpmnt.fieldid) > 0 AND ordrpmnt.tablename='tp_offerings'";

        }  

        if (!empty($filterdata->offering_status)){ 
            $trainings = explode(',',$filterdata->offering_status);
             if(!empty($trainings)){
                $trainingquery = array();
                foreach ($trainings as $training) {

                   if($training == 'active') {
                     
                        $sql .= " AND EXISTS (SELECT tpofr.id 
                        FROM {tp_offerings} AS tpofr 
                        JOIN {local_trainingprogram} AS tp ON tpofr.trainingid=tp.id
                        WHERE tp.published = 1 AND tpofr.id=ordrpmnt.fieldid) > 0 AND ordrpmnt.tablename='tp_offerings'";

                   }elseif($training == 'completed') {
                   
                        $sql .= " AND EXISTS (SELECT tpofr.id 
                        FROM {tp_offerings} AS tpofr 
                        JOIN {local_trainingprogram} AS tp ON tpofr.trainingid=tp.id
                        WHERE tp.id IN (SELECT programid FROM {program_completions} WHERE  completion_status = 1) AND tpofr.id=ordrpmnt.fieldid) > 0 AND ordrpmnt.tablename='tp_offerings'";

                   } elseif($training == 'financially_closed') {
                        $expired =  (new trainingprogram())->from_unixtime_for_expired_entities('tpofr.enddate');
                        $sql .= " AND EXISTS (SELECT tpofr.id 
                        FROM {tp_offerings} AS tpofr 
                        JOIN {local_trainingprogram} AS tp ON tpofr.trainingid=tp.id
                        WHERE tp.id IN (SELECT trainingid FROM {tp_offerings} WHERE  $expired AND tpofr.id=ordrpmnt.fieldid) > 0 AND ordrpmnt.tablename='tp_offerings'";
                   }

                }
               
            }
        } 

        if(!empty($filterdata->offering_type)){
        
            $offering_typelist = explode(',',$filterdata->offering_type);
            list($relatedtrainingprogramslistsql, $relatedtrainingprogramslistparams) = $DB->get_in_or_equal($offering_typelist, SQL_PARAMS_NAMED, 'offering_typelist');
            $params = array_merge($params,$relatedtrainingprogramslistparams);

            $sql .= " AND EXISTS (SELECT tpofr.id 
            FROM {tp_offerings} AS tpofr 
            JOIN {local_trainingprogram} AS tp ON tpofr.trainingid=tp.id
            WHERE tpofr.trainingmethod $relatedtrainingprogramslistsql AND tpofr.id=ordrpmnt.fieldid) > 0 AND ordrpmnt.tablename='tp_offerings'";
        }
        if (!empty($filterdata->offering_creator)){ 
      
            $trainings_typelist = explode(',',$filterdata->offering_creator);
            list($relatedtrainingprogramslistsql, $relatedtrainingprogramslistparams) = $DB->get_in_or_equal($trainings_typelist, SQL_PARAMS_NAMED, 'trainings_typelist');
            $params = array_merge($params,$relatedtrainingprogramslistparams);


             $sql .= " AND EXISTS (SELECT tpofr.id 
            FROM {tp_offerings} AS tpofr 
            JOIN {local_trainingprogram} AS tp ON tpofr.trainingid=tp.id
            WHERE tpofr.usercreated $relatedtrainingprogramslistsql AND tpofr.id=ordrpmnt.fieldid) > 0 AND ordrpmnt.tablename='tp_offerings'";
        } 

        if(!empty($filterdata->trainingprograms)){

            if(!empty($filterdata->trainingprograms)){
                $trainingprogramslist = explode(',',$filterdata->trainingprograms);
            }

            list($relatedtrainingprogramslistsql, $relatedtrainingprogramslistparams) = $DB->get_in_or_equal($trainingprogramslist, SQL_PARAMS_NAMED, 'trainingprogramslist');
            $params = array_merge($params,$relatedtrainingprogramslistparams);
 

            $sql .= " AND EXISTS (SELECT tpofr.id 
             FROM {tp_offerings} AS tpofr 
             JOIN {local_trainingprogram} AS tp ON tpofr.trainingid=tp.id
             WHERE tp.id $relatedtrainingprogramslistsql AND tpofr.id=ordrpmnt.fieldid) > 0 AND ordrpmnt.tablename='tp_offerings'";

        }     

        if(!empty($filterdata->exams)){

            if(!empty($filterdata->exams)){
                $examslist = explode(',',$filterdata->exams);
            }

            list($relatedexamslistsql, $relatedexamslistparams) = $DB->get_in_or_equal($examslist, SQL_PARAMS_NAMED, 'examslist');
            $params = array_merge($params,$relatedexamslistparams);
            
            $sql .= " AND EXISTS (SELECT hr.id 
             FROM {local_exam_profiles}  hr
             JOIN {local_exams} le ON le.id = hr.examid
             WHERE le.id $relatedexamslistsql AND hr.id=ordrpmnt.fieldid) > 0 AND ordrpmnt.tablename='local_exam_profiles'";

        }  

        if(!empty($filterdata->events)){

            if(!empty($filterdata->events)){
                $eventslist = explode(',',$filterdata->events);
            }

            list($relatedeventslistsql, $relatedeventslistparams) = $DB->get_in_or_equal($eventslist, SQL_PARAMS_NAMED, 'eventslist');
            $params = array_merge($params,$relatedeventslistparams);
            $sql .= " AND (ordrpmnt.fieldid $relatedeventslistsql AND ordrpmnt.tablename='local_events') ";

        } 

        if(!empty($filterdata->paymentmode)){

            if(!empty($filterdata->paymentmode)){
                $paymentmodelist = explode(',',$filterdata->paymentmode);
            }
    
            list($relatedpaymentmodelistsql, $relatedpaymentmodelistparams) = $DB->get_in_or_equal($paymentmodelist, SQL_PARAMS_NAMED, 'paymentmodelist');
            $params = array_merge($params,$relatedpaymentmodelistparams);
            $sql .= " AND ordrpmnt.paymenttype $relatedpaymentmodelistsql";

        }

        if(!empty($filterdata->organization)){

            if(!empty($filterdata->organization)){
                $organizationlist = explode(',',$filterdata->organization);
            }

            list($relatedorganizationlistsql, $relatedorganizationlistparams) = $DB->get_in_or_equal($organizationlist, SQL_PARAMS_NAMED, 'organizationlist');
            $params = array_merge($params,$relatedorganizationlistparams);


            $sql .= " AND EXISTS (SELECT lc.userid 
                FROM {local_users} lc 
                JOIN {local_organization} org ON org.id = lc.organization
                WHERE lc.organization $relatedorganizationlistsql AND lc.userid=ordrpmnt.orguserid) > 0";

        }

        if($filterdata->{'betweendaterangefrom[enabled]'} == 1 ){

            $start_year = $filterdata->{'betweendaterangefrom[year]'};
            $start_month = $filterdata->{'betweendaterangefrom[month]'};
            $start_day = $filterdata->{'betweendaterangefrom[day]'};
            $filter_starttime_con = mktime(0,0,0, $start_month, $start_day, $start_year);
            $sql.= " AND ordrpmnt.availablefrom >= '$filter_starttime_con' ";

        }
        if($filterdata->{'betweendaterangeto[enabled]'} == 1 ){
            $start_year = $filterdata->{'betweendaterangeto[year]'};
            $start_month = $filterdata->{'betweendaterangeto[month]'};
            $start_day = $filterdata->{'betweendaterangeto[day]'};
            $filter_endtime_con=mktime(23,59,59, $start_month, $start_day, $start_year);
            $sql.=" AND ordrpmnt.availablefrom <= '$filter_endtime_con' ";
        }

        if (isset($stable->id) && $stable->id > 0) {
            $orders = $DB->get_record_sql($fromsql . $sql, $params);
        } else {
            try {
                $orderscount = $DB->get_field_sql($countsql. $sql, $params);
                $sql.= "  GROUP BY ordrpmnt.fieldid ORDER BY ordrpmnt.id DESC";
                $orderscount = $orderscount ? $orderscount : 0;

                if ($stable->thead == false) {
                    if ($filterdata->type == 'mobile') {
                        $orders = $DB->get_records_sql($fromsql . $sql, $params);
                    } else {
                        $orders = $DB->get_records_sql($fromsql . $sql, $params, $stable->start, $stable->length);
                    }
                }
            } catch (dml_exception $ex) {
                $orderscount = 0;
            }
        }
        if (isset($stable->id) && $stable->id > 0) {
            return $orders;
        } else {
            return compact('orders', 'orderscount');
        }
    }

    public function seatexceed_check($list) {

        global $USER,$DB;

        switch ($list->tablename) {


            case 'tp_offerings':

                    $availableseats=$DB->get_field('tp_offerings','availableseats',array('id'=>$list->fieldid));
                
                break;

            case 'local_exam_profiles':

                $availableseats=$DB->get_field('local_exam_profiles','seatingcapacity',array('id'=>$list->fieldid));
                break;

            case 'local_events':

                $availableseats=0;

                break;
            default:
                $availableseats=0;
        }

        return $availableseats;

    }
    public function get_payment_details($data, $status=false, $userid=false){

        global $DB, $USER, $OUTPUT, $CFG,$PAGE;

        $sitecontext = context_system::instance();

        $PAGE->set_context($sitecontext);

        $renderer = $PAGE->get_renderer('tool_product');

        $category=0;

        switch ($data->tablename) {

            case 'tp_offerings':

            $category=1;

            break;

            case 'local_exam_profiles':

            $category=2;

            break;

            case 'local_events':

            $category=3;

            break;
        }

        $product =$this->get_product_from_reference($data->fieldid, $category);


        if($product){
        
            $value = (new product)->format_product($product,$userid);

            $total_purchases = 1;
            $total_price = $discount_price = $taxes = $discount = 0;
            $items = array();
            $total_price = $total_price+($value['actual_price']*$data->quantity);
            $discount_price = $discount_price+($value['sale_price']*$data->quantity);

            if (empty($status)) {
                $discountdetails = $DB->get_record('tool_user_order_payments', ['productid' => $product->id], 'discountprice, taxes, amount');
            } else {
                $discountdetails = $DB->get_record('tool_org_order_payments', ['productid' => $product->id], 'discountprice, taxes, amount');
            }

            $items[] = array(
                'product_id'=>  $value['id'],
                'name'      =>  $value['name'],
                'quantity'  =>  $data->quantity,
                'total'     =>  ($value['actual_price']*$data->quantity)
            );
           
            $total_discount = $total_price - $discount_price;
            $taxes = $this->caluculate_taxes($discount_price, $this->tax_slab);
            $total = $discount_price + $taxes;
            $discountprice = $discountdetails->discountprice;
            $taxvalue = $discountdetails->taxes;
            $totalprice = $discountdetails->amount;
         
            
            $return =array(
                'items'             =>  $items,
                'total_price'       =>  $totalprice,
                'discount_price'    =>  $discountprice,
                'total_discount'    =>  $total_discount,
                'total'             =>  $total,
                'taxes'             =>  $taxvalue,
                'tax_slab'          =>  $this->tax_slab,
                'total_purchases'   =>  $total_purchases
            );

            return $OUTPUT->render_from_template('tool_product/checkout/payment_details', $return);
        }
    }
    public function get_couponinfo($couponcode=0,$couponid=0){

        global $DB;

        try{

            $params=array();

            if($couponcode){

                $params['code']=$couponcode;

            }
            if($couponid){

                $params['id']=$couponid;

            }

            $couponinfo=$DB->get_record('coupon_management',$params);

            return $couponinfo;

        } catch(moodle_exception $e){

          print_r($e);

        } 
        
    }
    public function coupon_validationcheck($coupon,$couponappliedto=0){

        $return = array(
            'couponid'=> 0,
            'couponvalid'=> false,
            'couponmsg'   =>  get_string('invalidcoupon','tool_product'),
        );

        if($coupon){

            $return["couponid"] = $coupon->id;

            $coupon_expired_date = date('Y-m-d',$coupon->coupon_expired_date);

            $currdate=date('Y-m-d'); 

            if($coupon->coupon_applied_to !=0 ){

                $return["couponmsg"] = get_string('coupon_nametaken', 'tool_product');

            }elseif($coupon_expired_date >= $currdate && $coupon->coupon_status == 1) {

               $return["couponvalid"] = true;


                if($couponappliedto > 0 ){

                    $couponinfo=(new product)->coupon_applied_to($coupon->id,$products['couponcode'],$couponappliedto);

                    if($couponinfo){

                        $return["couponmsg"] = get_string('couponavailable', 'tool_product');

                    }else{

                        $return["couponvalid"] = false;

                        $return["couponmsg"] = get_string('coupon_nametaken', 'tool_product');

                    }
                }else{

                    $return["couponmsg"] = get_string('couponavailable', 'tool_product');

                }

            }elseif ($coupon_expired_date < $currdate){

                $return["couponmsg"] = get_string('couponexpired', 'tool_product');

            }
        }

        return $return;
        
    }
    public function coupon_applied_to($couponid,$couponcode,$couponappliedto){

        global $DB, $USER;

        try {

            $data=new stdClass();

            $data->id=$couponid;
            $data->coupon_status=2;
            $data->coupon_applied_to=$couponappliedto;
            $data->timemodified=time();
            $data->usermodified=$couponappliedto;

            $id=$DB->update_record('coupon_management', $data);
 
        } catch (dml_exception $e) {
            print_error($e);

        }

        return true;
        
    }
    public function get_early_registration_discount($days){
        global $DB;
        try {

            $expirydatecondition = (new trainingprogram)->from_unixtime_for_live_entities('earlyregistration_expired_date');
            $sql = 'SELECT *
            FROM {earlyregistration_management} 
            WHERE dayto >= :days1 AND dayfrom <= :days2 AND '.$expirydatecondition.'';
            $early_registrationinfo = $DB->get_record_sql($sql,
                                        ['days1' => $days, 'days2' => $days], IGNORE_MISSING);
            if($early_registrationinfo) {
                $discount_percentage =(int) $early_registrationinfo->discount;
            }
            return ($discount_percentage) ? $discount_percentage : 0;

        } catch(moodle_exception $e){
    
          print_r($e);
    
        }
        
    } 

    public function enrol($products , $userid) {
        global $DB;
        $roleid = $DB->get_field('role', 'id',array('shortname' => 'trainee'));
        $products = (array)$products;
        foreach($products['items'] as $product){
            if( !$product['is_enrolled'] ) {

                $productdetails = $DB->get_record('tool_products', ['id' => $product['product_id']], 'category, referenceid');
                switch ($productdetails->category) {
                    case self::TRAINING_PROGRAM:
                         if($product['grouped']){
                            $learningtracks[] = $product['grouped'];
                            $programlist[] = $product['product_id'];
                            continue 2;
                          }
                          (new trainingprogram)->program_enrollment($productdetails->referenceid, $userid);

                           $offering =$DB->get_record('tp_offerings',['id'=>$productdetails->referenceid]);
                
                            if($offering->type == 2){

                              (new learningtracks)->program_enrolment($offering->trainingid, $productdetails->referenceid, $roleid, $userid);
                            }
                        break;
                    case self::EXAMS:
                        if($product['grouped']){
                            $learningtracks[] = $product['grouped'];
                            $examslist[] = $product['product_id'];
                            continue 2;
                        }
                              
                        $tancconfirm = $products['items'][0]['tandcconfirm'];

                        (new exams)->exam_enrollmet($productdetails->referenceid, $userid, $product['hallscheduleid'], null, $userid, null, $product['product_id'],false,$tancconfirm);

                         $examid =$DB->get_field('local_exam_profiles','examid',['id'=>$productdetails->referenceid]);

                         (new learningtracks)->exam_enrolment($examid, $productdetails->referenceid, $roleid,$userid);
                        break;
                    case self::EVENTS:
                        (new events)->enrol_event($productdetails->referenceid, $userid);
                        break;
                    case self::GRIEVANCE:
                        (new exams)->enrol_grievance($productdetails->referenceid, $userid);
                        break;                
                    default:
                    continue 2;
                        break;
                }

            }
        }
        $lts = array_unique($learningtracks);
        if(!empty($lts)){
            foreach($lts as $learningtrack){
                (new learningtracks)->learningtrack_enrollmet($learningtrack, $userid, $roleid,$examslist, $programlist);
            }
        }    
    }

    public function add_to_wallet($product, $userid=false) {
        global $DB, $USER;
        if(!$userid){
            $userid = $USER->id;
        }
        $context = context_system::instance();
        $userwallet=\local_userapproval\action\manageuser::get_user_wallet($userid);
        $formdata = new stdClass;
        $formdata->userid = $userid;
        if($userwallet->id > 0){
            $formdata->wallet =$userwallet->wallet+$product['total'];
            $formdata->id=$userwallet->id;
            $formdata->timemodified=time();
            $formdata->usermodified=$userid;

            if (!is_siteadmin() && has_capability('local/organization:manage_trainee', $context)) {
                $formdata->roleid=$DB->get_field('role', 'id', ['shortname' => 'trainee']);
                $id=$DB->update_record('trainee_wallet', $formdata);

                $eventparams = array('context' => \context_system::instance(),'objectid'=>$id,'other'=>array('paymentstatus'=>'added', 'entitytype' => $product['entitytype'], 'entityid' => $product['entityid']));
                $event = \tool_product\event\trainee_wallet::create($eventparams);// ... code that may add some record snapshots
                $event->trigger();

            } elseif(!is_siteadmin() && has_capability('local/organization:manage_organizationofficial', $context)) {
                $id=$DB->update_record('local_orgofficial_wallet', $formdata);
            }
        }else{
            $formdata->wallet =$product['total'];
            $formdata->timecreated=time();
            $formdata->usercreated=$userid;
            if (!is_siteadmin() && has_capability('local/organization:manage_trainee', $context)) {
                $formdata->roleid=$DB->get_field('role', 'id', ['shortname' => 'trainee']);
                $id=$DB->insert_record('trainee_wallet', $formdata);
                
                $eventparams = array('context' => \context_system::instance(),'objectid'=>$id,'other'=>array('paymentstatus'=>'added', 'entitytype' => $product['entitytype'], 'entityid' => $product['entityid']));
                $event = \tool_product\event\trainee_wallet::create($eventparams);// ... code that may add some record snapshots
                $event->trigger();

            } elseif(!is_siteadmin() && has_capability('local/organization:manage_organizationofficial', $context)) {
                $id=$DB->insert_record('local_orgofficial_wallet', $formdata);
            }
        }

        $formdata->walletlog =$product['total'];
        \local_userapproval\action\manageuser::add_user_wallet_logs($formdata);

        $paymentorder=new \stdClass();

        $touser=$DB->get_record('user',array('id'=>$formdata->userid));

        $localuserrecord = $DB->get_record('local_users',['userid'=> $formdata->orguserid]);
        $fullname = ($localuserrecord)? (($localuserrecord->lang == 'ar') ? $localuserrecord->firstnamearabic.' '.$localuserrecord->middlenamearabic.' '.$localuserrecord->thirdnamearabic.' '.$localuserrecord->lastnamearabic  : $localuserrecord->firstname.' '.$localuserrecord->middlenameen.' '.$localuserrecord->thirdnameen.' '.$localuserrecord->lastname) :fullname($touser);

        $paymentorder->payment_userfullname=$fullname;

        (new \tool_product\notification)->product_notification('wallet_update', $touser,$fromuser=get_admin(), $paymentorder,$waitinglistid=0);
    }

    public static function get_listof_mypayments($stable,$filtervalues) {
        global $DB, $USER;
        $params          = array();
        $payments      = array();
        $paymentscount = 0;
        $concatsql       = '';
        if (isset($stable->paymentid) && $stable->paymentid > 0) {
            $concatsql .= " AND lpt.id = :paymentid";
            $params['paymentid'] = $stable->paymentid;
        }
        
        $countsql = "SELECT COUNT(lpt.id) ";
        $fromsql = "SELECT lpt.* ";
        $sql = " FROM {tool_product_telr} AS lpt
                 WHERE 1=1 ";

        if(!is_siteadmin()){

            $concatsql .= " AND lpt.userid =:userid ";
            $params['userid'] = $USER->id;

        }    

        $sql .= $concatsql;
        if (isset($stable->trackid) && $stable->trackid > 0) {
            $payments = $DB->get_record_sql($fromsql . $sql, $params);
        } else {
            try {
                $paymentscount = $DB->count_records_sql($countsql . $sql, $params);
                if ($stable->thead == false) {
                    $sql .= " ORDER BY lpt.id ASC";
                    $payments = $DB->get_records_sql($fromsql . $sql, $params, $stable->start, $stable->length);
                }
            } catch (\dml_exception $ex) {
                $paymentscount = 0;
            }
        }
        if (isset($stable->paymentid) && $stable->paymentid > 0) {
            return $payments;
        } else {
            return compact('payments', 'paymentscount');
        }
    }

     public static function sendemail_to_orgofficial($data) {

        global $USER,$DB,$CFG;

        $record = $DB->get_record('tool_org_order_payments',array('id'=>$data->productid));

        $userdata = $DB->get_record('user',array('id'=>$record->orguserid));
        $subject = $data->subject;
        $textmessage = strip_tags($data->message['text']);
        $htmlmessage = $data->message['text'];
        $fromuser = $USER; 
        try{
            $sendmail = email_to_user($userdata, $fromuser, $subject, $textmessage,$htmlmessage, '','',true, $fromuser->email, fullname($fromuser));
            return $sendmail;
        } catch(moodle_exception $e){
          print_r($e);
        }

    }

    public static function get_mywallet($stable, $filterdata) {
        global $DB, $USER;
        $countsql = " SELECT COUNT(lwl.id)";
        $sql = " SELECT lwl.*";
        $fromsql = " FROM {local_wallet_logs} AS lwl WHERE lwl.userid = $USER->id";
        $searchparams = array();
        $params = array_merge($searchparams);
        $totalcount = $DB->count_records_sql($countsql.$fromsql, $params);
        $fromsql .=" ORDER BY lwl.id DESC";
        $wallet_list = $DB->get_records_sql($sql.$fromsql, $params, $stable->start,$stable->length);
        $record = [];
        $count = 0;
        if($wallet_list) {
            foreach($wallet_list as $list) {
                $record[$count]['id'] = $list->id;
                if($list->addedwallet) {
                    $wallet = $list->addedwallet;
                } else {
                    $wallet = '--';
                }
                $record[$count]['addedwallet'] = $wallet;
                $record[$count]['timecreated'] = userdate($list->timecreated, get_string('strftimedatemonthabbr', 'core_langconfig'));
                $record[$count]['paymentdate'] = $list->timecreated;
                $count++;
            }
            $nodata = false;
        } else {
            $nodata = true;
        }
        $walletContent = [
            'nodata' => $nodata,
            'totalcount' => $totalcount,
            'haswalletdata' => $record,
            "length" => count($record),
        ];

        return $walletContent;
    }

    public static function get_listof_orgpayments($stable,$filterdata) {
        global $DB, $USER;
        $params          = array();
        $payments      = array();
        $paymentscount = 0;
        $concatsql       = '';
        $stable->orguserid = $USER->id;
        $fromsql = " SELECT ordrpmnt.id,ordrpmnt.trainingname, ordrpmnt.amount,  ordrpmnt.payableamount, ordrpmnt.purchasedseats, ordrpmnt.originalprice, ordrpmnt.taxes, ordrpmnt.discountprice, ordrpmnt.paymenttype, ordrpmnt.paymentapprovalstatus, ordrpmnt.timecreated";
       // $fromsql.=',ordrpmnt.approvalseats,ordrpmnt.availableseats';
        $sql = " FROM ( ";
        $stable->selectparams=',tppmnt.paymenttype, tppmnt.amount, tppmnt.paymentapprovalstatus, tppmnt.originalprice, tppmnt.timecreated, tppmnt.taxes, tppmnt.discountprice';
        $stable->orguserid=$USER->id;
        $stable->tablename='tool_org_order_payments';
        $plugins = get_plugins_with_function('product_orders');
        $i=1;
        foreach ($plugins as $plugintype => $funcs) {
            foreach ($funcs as $pluginname => $functionname) {
               // if($filterdata->status === ($plugintype._.$pluginname)){
                    $comporders = call_user_func_array($functionname, [$stable,$filterdata]);
                    if (!$comporders) {
                        continue;
                    }
                    $params = array_merge($params,$comporders['params']);
                    if($i > 1){
                        $sql.="UNION ";
                    }
                    $sql.="(".$comporders['sql'].")";
                    $i++;
               /* }else{
                    continue;
                }*/
            }
        } 
        $sql.= " ) AS ordrpmnt WHERE ordrpmnt.id > 0 ";
        //var_dump($fromsql.$sql); exit;
       // var_dump($fromsql.$sql); exit;
        if (isset($stable->paymentid) && $stable->paymentid > 0) {
            $concatsql .= " AND tppmnt.id = :paymentid";
            $params['paymentid'] = $stable->paymentid;
        }
        $countsql = " SELECT COUNT(*) ";
       /* $fromsql = " SELECT DISTINCT(lop.id), lop.*, tp.name AS pname";
        $sql = " FROM {tool_org_order_payments} AS lop";
        $sql .= " JOIN {tool_products} AS tp ON tp.id = lop.productid";
        $sql .= " WHERE lop.orguserid = $USER->id ";*/
        $sql .= $concatsql;
        $sql .= " ORDER BY ordrpmnt.timecreated DESC ";
        if (isset($stable->paymentid) && $stable->paymentid > 0) {
            $payments = $DB->get_record_sql($fromsql . $sql, $params);
        } else {
            try {
                $paymentscount = $DB->count_records_sql($countsql . $sql, $params);
                if ($stable->thead == false) {
                   // $sql .= " ORDER BY lop.id DESC";
                   //var_dump($fromsql.$sql); exit;
                    $payments = $DB->get_records_sql($fromsql . $sql, $params, $stable->start, $stable->length);
                }
            } catch (\dml_exception $ex) {
                $paymentscount = 0;
            }
        }
        if (isset($stable->paymentid) && $stable->paymentid > 0) {
            return $payments;
        } else {
            return compact('payments', 'paymentscount');
        }
    }
    public function insert_update_user_order_payments($data,$product=array()) {
        global $DB, $USER;
        
        try {

            $getdata=$DB->get_record('tool_user_order_payments',  array('tablename'=>$data->tablename,'fieldname'=>$data->fieldname,'fieldid'=>$data->fieldid,'userid'=>$data->userid),'id');

            if($getdata && $data->processtype !='reschedule'){

                $data->id=$getdata->id;


                if($data->timeupdated){

                    $data->timemodified=$data->timeupdated;

                }else{
                    
                    $data->timemodified=time();

                }

                $data->usermodified=$data->userid;

                $data->realuser = ($USER->realuser) ? $USER->realuser :0;

                $id=$DB->update_record('tool_user_order_payments', $data);

            }else{

                if($data->timeupdated){

                    $data->timecreated=$data->timeupdated;

                }else{

                    $data->timecreated=time();

                }

                $data->usercreated=$data->userid;

                $data->realuser = ($USER->realuser) ? $USER->realuser :0;

                $id=$DB->insert_record('tool_user_order_payments', $data);


                if(!empty($product)){

                    $product->orgorderid=$id;

                    $product->userid=$data->userid;

                    $product->productid=$data->productid;

                    self::insert_update_order_discount_usage($product);
                }

                if(!is_siteadmin()){

                    $paymentorder=clone $data;

                    $touser=$DB->get_record('user',array('id'=>$data->userid));

                    $localuserrecord = $DB->get_record('local_users',['userid'=> $data->userid]);
                    $fullname = ($localuserrecord)? (($localuserrecord->lang == 'ar') ? $localuserrecord->firstnamearabic.' '.$localuserrecord->middlenamearabic.' '.$localuserrecord->thirdnamearabic.' '.$localuserrecord->lastnamearabic  : $localuserrecord->firstname.' '.$localuserrecord->middlenameen.' '.$localuserrecord->thirdnameen.' '.$localuserrecord->lastname) :fullname($touser);

                    $paymentorder->payment_userfullname=$fullname;

                    $paymentorder->payment_details=$this->get_payment_details($paymentorder, false, $touser);

                    $paymentorder->order=$id;

                    (new \tool_product\notification())->product_notification('pre_payment', $touser,$fromuser=get_admin(), $paymentorder,$waitinglistid=0);


                    $paymentorder->invoiceno=$id;

                    (new \tool_product\notification())->product_notification('payment_completion', $touser,$fromuser=get_admin(), $paymentorder,$waitinglistid=0);
                }
            }



        } catch (dml_exception $e) {
            print_error($e);

        }
        return true;
    }
    public static function get_my_financialpayments($stable,$filterdata=null) {

        global $DB, $USER;

        $systemcontext = context_system::instance();

        $params          = array();
        $payments      = array();
        $paymentscount = 0;
        $totalcost = 0;
 
        $countsql = "SELECT COUNT(*) ";

        $totalcostql = "SELECT SUM(ordrpmnt.amount) ";


        $fromsql = " SELECT ordrpmnt.id,ordrpmnt.productid,ordrpmnt.trainingname,ordrpmnt.organization,ordrpmnt.tablename,ordrpmnt.fieldname,ordrpmnt.fieldid,ordrpmnt.trainingid,ordrpmnt.availablefrom,ordrpmnt.purchasedseats,ordrpmnt.userid ,ordrpmnt.availableto, ordrpmnt.amount, ordrpmnt.telrid  ";

        $fromsql.=',ordrpmnt.amount,ordrpmnt.originalprice,ordrpmnt.discountprice,ordrpmnt.taxes,ordrpmnt.paymenttype,ordrpmnt.timecreated , ordrpmnt.sellingprice';


        $sql = " FROM ( ";

        $stable->selectparams=',tppmnt.productid,tppmnt.amount,tppmnt.originalprice,tppmnt.discountprice,tppmnt.taxes,tppmnt.paymenttype,tppmnt.timecreated, tppmnt.telrid ';


        $stable->tablename='tool_user_order_payments';


        $plugins = get_plugins_with_function('product_userorders');

        $i=1;

        foreach ($plugins as $plugintype => $funcs) {
            foreach ($funcs as $pluginname => $functionname) {


                $comporders = call_user_func_array($functionname, [$stable,$filterdata]);

                if (!$comporders) {
                    continue;
                }

                $params = array_merge($params,$comporders['params']);

                if($i > 1){


                    $sql.="UNION ";

                }

                $sql.="(".$comporders['sql'].")";

                $i++;

            }
        } 

        $sql.= " ) AS ordrpmnt WHERE 1=1 ";

        if (isset($stable->id) && $stable->id > 0) {
            $sql .= " AND ordrpmnt.id = :ordrpmnt ";
            $params['ordrpmnt'] = $stable->id;
        }


        if (!empty($filterdata->search_query)) {

            $sql .= " AND (ordrpmnt.englishname LIKE :search1 OR ordrpmnt.arabicname LIKE :search2 OR ordrpmnt.amount LIKE :search3 OR ordrpmnt.ofrcode LIKE :search4 OR ordrpmnt.tpcode LIKE :search5 OR (EXISTS (SELECT lc.userid 
                FROM {local_users} lc 
                JOIN {local_organization} org ON org.id = lc.organization
                WHERE org.shortname LIKE :search6 AND lc.userid=ordrpmnt.userid) > 0) ) ";

            $params['search1'] = '%'.trim($filterdata->search_query).'%';
            $params['search2'] = '%'.trim($filterdata->search_query).'%';
            $params['search3'] = '%'.trim($filterdata->search_query).'%';
            $params['search4'] = '%'.trim($filterdata->search_query).'%';
            $params['search5'] = '%'.trim($filterdata->search_query).'%';
            $params['search6'] = '%'.trim($filterdata->search_query).'%';
        }

        if (isset($stable->id) && $stable->id > 0) {
            $sql .= " AND ordrpmnt.id = :ordrpmnt ";
            $params['ordrpmnt'] = $stable->id;
        }  

        if(!empty($filterdata->prgevntexams) && $filterdata->prgevntexams == 1){
            $qry = " SELECT tp.id  
             FROM {tp_offerings} AS tpofr  
             JOIN {local_trainingprogram} AS tp ON tpofr.trainingid=tp.id WHERE tp.published = 1 GROUP by tp.id ORDER BY tp.id DESC";         
            $trainingprogramslist = $DB->get_fieldset_sql($qry);
            list($relatedtrainingprogramslistsql, $relatedtrainingprogramslistparams) = $DB->get_in_or_equal($trainingprogramslist, SQL_PARAMS_NAMED, 'trainingprogramslist');
            $params = array_merge($params,$relatedtrainingprogramslistparams);
            $sql .= " AND EXISTS (SELECT tpofr.id 
             FROM {tp_offerings} AS tpofr 
             JOIN {local_trainingprogram} AS tp ON tpofr.trainingid=tp.id
             WHERE tp.id $relatedtrainingprogramslistsql AND tpofr.id=ordrpmnt.fieldid) > 0 AND ordrpmnt.tablename='tp_offerings'";
         } 

        if(!empty($filterdata->prgevntexams) && $filterdata->prgevntexams ==3){
            $qry = "SELECT le.id  
                    FROM {local_exams} le WHERE le.status = 1 GROUP by le.id ORDER BY le.id DESC";
            $examslist = $DB->get_fieldset_sql($qry);
            list($relatedexamslistsql, $relatedexamslistparams) = $DB->get_in_or_equal($examslist, SQL_PARAMS_NAMED, 'examslist');
            $params = array_merge($params,$relatedexamslistparams);
            $sql .= " AND EXISTS (SELECT hr.id 
             FROM {local_exam_profiles}  hr
             JOIN {local_exams} le ON le.id = hr.examid
             WHERE le.id $relatedexamslistsql AND hr.id=ordrpmnt.fieldid) > 0 AND ordrpmnt.tablename='local_exam_profiles'";

        }  
       
        if(!empty($filterdata->prgevntexams) && $filterdata->prgevntexams ==2){
            $qry = "SELECT evnt.id  
                        FROM {local_events} AS evnt GROUP by evnt.id ORDER BY evnt.id DESC";       
            $eventslist = $DB->get_fieldset_sql($qry);
            list($relatedeventslistsql, $relatedeventslistparams) = $DB->get_in_or_equal($eventslist, SQL_PARAMS_NAMED, 'eventslist');
            $params = array_merge($params,$relatedeventslistparams);
            $sql .= " AND (ordrpmnt.fieldid $relatedeventslistsql AND ordrpmnt.tablename='local_events') ";
        }  

        $trainingpurchase = self::trainingpayments_filter($filterdata);

        $purchaseparams = array_merge($params,$trainingpurchase['params']);

        if (isset($stable->id) && $stable->id > 0) {

            $fsql = $sql.$trainingpurchase['sql'].$osql;
        
            $payments = $DB->get_record_sql($fromsql . $fsql.$trainingpurchase['sql'],$purchaseparams);

        } else {
           
            try {

                $osql.= "  ORDER BY ordrpmnt.id DESC ";

                $fsql = $sql.$trainingpurchase['sql'].$osql;

                $paymentscount = $DB->get_field_sql($countsql. $fsql,$purchaseparams);
                $paymentscount = $paymentscount ? $paymentscount : 0;

                $totalcost = $DB->get_field_sql($totalcostql. $fsql, $purchaseparams);

                $totalcost = $totalcost ? $totalcost : 0;
 
                
                if ($stable->thead == false) {

                    $payments = $DB->get_records_sql($fromsql . $fsql, $purchaseparams, $stable->start, $stable->length);

                }
            } catch (dml_exception $ex) {
                $paymentscount = 0;
            }
        }                

        if (isset($stable->id) && $stable->id > 0) {
            return $payments;
        } else {
            return compact('payments', 'paymentscount','totalcost');
        }
    }

     public function get_product_info($recordid,$fieldid,$category,$quantity) {
        global $DB, $PAGE, $OUTPUT, $CFG, $SESSION;
        
        if($category == 1) {
           $recordcode = $DB->get_field('local_trainingprogram','code',['id'=>$recordid]);
           $productdata = $DB->get_record_sql('SELECT * FROM {tool_products} WHERE category=:categoryid AND referenceid=:fieldid AND code = :recordcode',['categoryid'=>$category,'fieldid'=>$fieldid,'recordcode'=>$recordcode]);
        } elseif($category == 2 ) {

           $recordcode = $DB->get_field('local_exams','code',['id'=>$recordid]);
           $productdata = $DB->get_record_sql('SELECT * FROM {tool_products} WHERE category=:categoryid AND referenceid=:fieldid AND code = :recordcode',['categoryid'=>$category,'fieldid'=>$fieldid,'recordcode'=>$recordcode]);
        }elseif($category == 3) {

            $recordcode = $DB->get_field('local_events','code',['id'=>$recordid]);
            $productdata = $DB->get_record_sql('SELECT * FROM {tool_products} WHERE category=:categoryid AND referenceid=:fieldid AND code = :recordcode',['categoryid'=>$category,'fieldid'=>$fieldid,'recordcode'=>$recordcode]);

        } else {

            $productdata = null;
        }



       if($productdata) {

          $product = new \stdClass();

            $product->productid = $productdata->id;
            $product->name = $productdata->name;
            $product->code = $productdata->code;
            $product->tablename = ($category == 1)?'tp_offerings':(($category == 2) ? 'local_exam_profiles' : 'local_events');
            $product->fieldname = 'id';
            $product->fieldid = $productdata->referenceid;
            $product->parentfieldid = $recordid;
            $product->quantity = $quantity;


          return $product;

        }
    }
    public function get_ordersummary_data($id) {
        global $DB, $PAGE, $OUTPUT, $CFG, $SESSION;

       $orderdata = $DB->get_record('tool_product_telr_pending',['id'=>$id]);
       if($orderdata) {
            $product = new stdClass();
            $summary = unserialize(base64_decode($orderdata->productdata));
            if(!empty($summary['items'])){
                $product->items = array_values($summary['items']);
            } else {
                $product->items = array();
            }
            $product->total_price = $summary['total_price'];
            $product->discount_price = $summary['discount_price'];
            $product->total_discount = $summary['total_discount'];
            $product->total = $summary['total'];
            $product->taxes = $summary['taxes'];
            $product->taxdisplay = $summary['taxdisplay'];
            $product->tax_slab = $summary['tax_slab'];
            $product->total_purchases = $summary['total_purchases'];
            if(!empty($summary['payment_methods'])){
                $product->payment_methods = array_values($summary['payment_methods']);
            } else {
                $product->payment_methods = array();
            }
            $product->category = $summary['category'];
          return $product;

        }
    }

    public function insert_update_sadad_invoice_record($productdataen,$insertmode =false) {
        global $DB, $USER, $CFG;
        $productdata = unserialize(base64_decode($productdataen));
       
        if($productdata['total'] > 0) {
            $response = (new telr)->generate_sadad_bill($productdataen);
            if(!empty($response)) {
                $responsedata = $response[0];
                if($responsedata->invoiceNumber) {
                    if($insertmode == 'new') {
                        $orgpayment = $DB->get_record('tool_org_order_payments', ['id' => $productdata['paymentid']]);
                        $orgpayment->payableamount = $responsedata->amount;
                        $orgpayment->transactionid = $responsedata->invoiceNumber;
                        $orgpayment->taxes = $productdata['taxes'];
                        $orgpayment->realuser = ($USER->realuser) ? $USER->realuser :0;
                        $DB->update_record('tool_org_order_payments', $orgpayment);
                        $orderdata = $DB->get_record('tool_order_approval_seats', ['paymentid' => $productdata['paymentid']]);
                        $orderdata->approvalseats = $productdata['approvalseats'];
                        (new product)->insert_update_org_order_approvals($orderdata);
                        $productid = $orderdata->productid;
                        $approvalseats =  $productdata['approvalseats'];
                        $type = 'purchase';
                        $payableamount = $responsedata->amount;
                    } else {
    
                        $productid = $productdata['productid'];
                        $approvalseats = $productdata['seats'];
                        $type = $productdata['type'];
                        $payableamount = $productdata['payableamount'];
                       
                    }
                    $organizationid = $this->get_user_organization($productdata['userid']);
                    $record = new stdClass();
                    $record->productid =$productid;
                    $record->userid = $productdata['userid'];
                    $record->realuser = ($USER->realuser) ? $USER->realuser :0;
                    $record->telrid = 0;
                    $record->invoice_number =$responsedata->invoiceNumber;
                    $record->seats =$approvalseats;
                    $record->type = $type;
                    $record->amount =$responsedata->amount;
                    $record->payableamount =$payableamount;
                    $record->status =1;
                    $record->payment_status =0;
                    $record->timecreated = time();
                    $record->usercreated = $USER->id;
                    $record->organization = $organizationid?$organizationid:0;
                    try{
                        $insertid = $DB->insert_record('tool_product_sadad_invoice', $record);
                        if($type == 'replacement') {
                            $this->insert_replacement_record_in_org_order_payments($record,$productdata);
                        }
                        $orderpaymentid = $DB->get_field('tool_org_order_payments', 'id', ['transactionid'=>$record->invoice_number, 'productid'=>$record->productid]);
                        if ($orderpaymentid) {
                            $DB->update_record('tool_org_order_payments', ['id' => $orderpaymentid, 'orderstatus' => 1]);

                          $entitydata = $DB->get_record_sql("  SELECT toop.id,toop.productid AS productid,toop.orguserid AS orguserid,tp.referenceid AS referenceid,tp.category AS category,tp.code AS code
                          FROM {tool_org_order_payments} AS toop
                          JOIN {tool_products} as tp ON tp.id = toop.productid WHERE toop.id = $orderpaymentid AND orderstatus = 1");
                           if($entitydata->category ==1 ){
                            
                            $orderdata = $DB->get_field('tool_order_approval_seats', 'id',['paymentid' => $productdata['paymentid']]);
                            $tps = $DB->get_record('program_enrollments',array('orderid'=> $orderdata,'offeringid'=> $entitydata->referenceid));
                            $tpdata = $DB->get_record('local_trainingprogram',array('id'=> $tps->programid));
                            $tpdata->program_name = $tpdata->name;                
                            $tpdata->program_arabicname = $tpdata->namearabic;
                            $tpusers = $DB->get_record('local_users',array('userid'=>$tps->userid));
                            $tpdata->program_userfullname=$tpusers->firstname .' '. $tpusers->middlenameen.' '. $tpusers->thirdnameen.' '. $tpusers->lastname;
                            $tpdata->program_userarabicfullname = $tpusers->firstnamearabic .' '. $tpusers->middlenamearabic.' '. $tpusers->thirdnamearabic.' '. $tpusers->lastnamearabic;
                            $tpdata->roleid =  $tps->roleid;
                            $trainee = $DB->get_record('user',array('id'=>$tps->userid)); 
                            $fromuser = $DB->get_record('user',array('id'=> $entitydata->orguserid)); 
                            $tpdata->orgoff = 'yes';
                           


                            (new \local_trainingprogram\notification())->trainingprogram_notification('trainingprogram_enroll', $touser=$trainee,$fromuser = $fromuser,$tpdata,$waitinglistid=0);


                           }elseif($entitydata->category ==2){
                            $orderdata = $DB->get_field('tool_order_approval_seats', 'id',['paymentid' => $productdata['paymentid']]);
                            $examenrol = $DB->get_record('exam_enrollments',array('orderid'=> $orderdata,'profileid'=> $entitydata->referenceid));
                            $exam_record= $DB->get_record('local_exams', array('id'=>$examenrol->examid));
                            $userid = $examenrol->userid;                        
                           (new \local_exams\local\exams)->exams_enrol_notification($exam_record, $userid, $examenrol->hallscheduleid);




                           }elseif($entitydata->category == 3){
                         

                            $orderdata = $DB->get_field('tool_order_approval_seats', 'id',['paymentid' => $productdata['paymentid']]);
                            $eventenrollment = $DB->get_record('local_event_attendees',array('orderid'=> $orderdata,'eventid'=> $entitydata->referenceid));
                            $events = $DB->get_record('local_events',array('id'=> $eventenrollment->eventid));
             
                            $localuserrecord = $DB->get_record('local_users',array('userid'=>$eventenrollment->userid));
                            if ($localuserrecord->lang == 'ar') {
                                $events->event_name = $DB->get_field('local_events','titlearabic',array('id'=> $events->id));               
                       
                            } else {
                                $events->event_name=$DB->get_field('local_events','title',array('id'=>$events->id));               
                               
                            } 
                            $events->event_userfullname= ($localuserrecord)? (($localuserrecord->lang == 'ar') ? $localuserrecord->firstnamearabic.' '.$localuserrecord->middlenamearabic.' '.$localuserrecord->thirdnamearabic.' '.$localuserrecord->lastnamearabic  : $localuserrecord->firstname.' '.$localuserrecord->middlenameen.' '.$localuserrecord->thirdnameen.' '.$localuserrecord->lastname) :fullname($userrecord);       
                            $user = $DB->get_record('user', ['id' => $eventenrollment->userid]);
                            $fromuser =  $DB->get_record('user', ['id' => $entitydata->orguserid]);                   

                            (new \local_events\notification())->event_notification('events_registration', $touser=$user,$fromuser , $events,$waitinglistid=0);

                              
                           }
                        } else {
                            $orderpaayments = $this->tool_org_order_payments($record);

                        }
                        if($insertid) {
                        
                            $responsedata->sadadid = $insertid;
    
                            (new product)->insert_update_sada_invoice_logs($responsedata);
    
                            if($productdata['type'] == 'cancel') {
                               (new product)->update_org_order_seats_for_cancellation($productdata);
                            }
    
                        }
                        $redirecturl['returnurl'] = $CFG->wwwroot.'/admin/tool/product/financialpayments.php';
                        return $redirecturl;
                        
                    }
                    catch(dml_exception $e){
                        print_error($e);
                    }
                }
            }
        } else {
            
            $orderdata = $DB->get_record('tool_order_approval_seats', ['paymentid' => $productdata['paymentid']]);
            $orderdata->approvalseats = $productdata['approvalseats'];
            (new product)->insert_update_org_order_approvals($orderdata);



            $redirecturl['returnurl'] = $CFG->wwwroot.'/admin/tool/product/orderapproval.php';
            return $redirecturl;
        }

    }

    public function tool_org_order_payments($record) {
        global $DB, $USER;
        $data = new stdClass();
        $data->productid        = $record->productid;
        $productitems           = $this->get_tablename($record->productid);
        $data->tablename        = $productitems['tablename'];
        $data->fieldname        = 'id';
        $data->fieldid          = $productitems['referenceid'];
        $data->organization     = $DB->get_field('local_users', 'organization', ['userid'=>$record->userid]);
        $data->orguserid        = $record->userid;
        $data->paymenttype      = 'postpaid';
        $data->paymenton        = $record->timecreated;
        $data->originalprice    = $record->amount;
        $data->discountprice    = 0;
        $data->taxes            = 0;
        $data->amount           = $record->amount;
        $data->payableamount    = $record->payableamount;
        $data->purchasedseats   = $record->seats;
        $data->paymentapprovalstatus = 1;
        $data->approvaluserid   = $record->userid;
        $data->approvalon       = $record->timecreated;
        $data->amountrecived    = 0;
        $data->transactionid    = $record->invoice_number;
        $data->checkid          = 0;
        $data->transactionnote  = 0;
        $data->usercreated      = $record->usercreated;
        $data->timecreated      = $record->timecreated;
        $data->usermodified     = 0;
        $data->timemodified     = 0;
        $data->orderstatus      = 0;
        $data->transactionref   = 0;
        $data->approvalseats    = $record->seats;
        $data->organization     = $record->organization;
        $paymntid = $DB->insert_record('tool_org_order_payments', $data);        
    }

    public function get_tablename($productid) {
        global $DB;
        $product = $DB->get_record('tool_products', ['id'=>$productid]);
        if ($product->category == 2) {
            $tablename = 'local_exam_profiles';
        } else {
            $tablename = 'local_exam_attempts';
        }
        return ['tablename'=>$tablename, 'referenceid'=>$product->referenceid];
    }

    public function insert_update_sada_invoice_logs($data) {
        global $DB, $USER;
        $record = new stdClass();
        $record->sadadid =$data->sadadid;
        $record->invoice_number =$data->invoiceNumber;
        $record->cardtid =$data->cardtId;
        $record->amount =$data->amount;
        $record->is_enterprise =$data->isEnterprise;
        $record->registration_no =$data->registrationNo;
        $record->company_name =$data->companyName;
        $record->commisioner_name =$data->commisionerName;
        $record->commisioner_id =$data->commisionerId;
        $record->commissioner_mobile_no =$data->commissionerMobileNo;
        $record->commissioner_email =$data->commissionerEmail;
        $record->payment_status =($data->paymentStatus == 'Paid') ? 1 : 0;
        $record->issue_date =$data->issueDate? strtotime($data->issueDate) : 0;
        $record->expiry_date =$data->expiryDate ? strtotime($data->expiryDate) : 0;
        $record->timecreated = time();
        $record->usercreated = $USER->id;
        try{
            $insertid = $DB->insert_record('sadad_invoice_logs', $record);
            return $insertid;
        }
        catch(dml_exception $e){
            print_error($e);
        }

    }
    public function update_org_order_seats_for_cancellation($data) {
        global $DB, $USER;



        if($data['entitytype'] == 'trainingprogram') {
           $data['tablename'] = 'tp_offerings';
           $data['fieldid'] = $data['offeringid'];
        } else if($data['entitytype'] == 'exam'){
            $data['tablename'] = 'local_exam_profiles';
            $data['fieldid'] = $data['profileid'];
        } else {
            $data['tablename'] = 'local_events';
            $data['fieldid'] = $data['eventid'];
        }
        $orderseatsrecord = $DB->get_record('tool_org_order_seats',[
                                            'productid'=>$data['productid'],
                                            'tablename'=>$data['tablename'] ,
                                            'fieldname'=>'id',
                                            'fieldid'=>$data['fieldid'],
                                            'orguserid'=>$data['userid'],
                                            ]);
        if($orderseatsrecord) {
            if($data['enrolltype'] == 2) {
                $orderseatsrecord->purchasedseats = ($orderseatsrecord->purchasedseats - 1);
                $orderseatsrecord->availableseats = ($orderseatsrecord->availableseats - 1);
            }
            $orderseatsrecord->approvalseats = ($orderseatsrecord->approvalseats - 1);
            try{
                $orderseatsrecord->realuser = ($USER->realuser) ? $USER->realuser :0;
                $updatedrecordid = $DB->update_record('tool_org_order_seats', $orderseatsrecord);
                if($updatedrecordid) {
                    (new product)->update_org_approval_seats_for_cancellation($data);
                }
            }
            catch(dml_exception $e){
                print_error($e);
            }
            
        }
        
    }
    public function update_org_approval_seats_for_cancellation($data) {
        global $DB, $USER;    

        $approvalseatsrecord = $DB->get_record_sql('SELECT * FROM {tool_order_approval_seats} 
                       WHERE approvalseats <> 0 AND 
                        productid = :productid AND 
                        tablename = :tablename AND 
                        fieldname = :fieldname AND 
                        fieldid = :fieldid AND 
                        orguserid = :orguserid ORDER By id DESC',
                        [
                            'productid'=>$data['productid'],
                            'tablename'=>$data['tablename'] ,
                            'fieldname'=>'id',
                            'fieldid'=>$data['fieldid'],
                            'orguserid'=>$data['userid'],
        ]);

        if($approvalseatsrecord) {
           $approvalseatsrecord->approvalseats = ($approvalseatsrecord->approvalseats - 1);
            try{
                $approvalseatsrecord->realuser = ($USER->realuser) ? $USER->realuser :0;
                $updatedrecordid = $DB->update_record('tool_order_approval_seats', $approvalseatsrecord);
                return $updatedrecordid;
            }
            catch(dml_exception $e){
                print_error($e);
            }
           
        }     
    }

    public function get_my_refundpayments($stable,$filterdata) {
        
        global $DB, $USER;
        $params =[];
        $systemcontext = context_system::instance();
        $countsql = "SELECT COUNT(tpr.id) ";
        $wheresql = "SELECT tpr.* ";
        $sql = " FROM {tool_product_refund} tpr
                 JOIN {tool_products} top ON top.id = tpr.productid
                 WHERE 1 = 1 ";
        if(!is_siteadmin() && has_capability('local/organization:manage_trainee',$systemcontext)) {
            $sql .= " AND tpr.userid =:userid";
            $params['userid'] = $USER->id;
        }

        $refundpayments = self::trainingpayments_filter($filterdata);
   
        $purchaseparams = array_merge($params,$refundpayments['params']);
    
        $refundsql = $sql.$refundpayments['sql'];       
        $refundsql .=" ORDER BY tpr.id DESC";  
      
        $refunds = $DB->get_records_sql($wheresql.$refundsql,$purchaseparams,$stable->start, $stable->length);
        $totalcount = $DB->count_records_sql($countsql.$refundsql, $purchaseparams);
        $refundrecords = [];
        $totalcost=0;
        foreach($refunds as $refund) {
            $telr = $DB->get_record_sql("SELECT tpt.*
                                           FROM {tool_product_telr} tpt
                                          WHERE tpt.id =".$refund->transactionid);
                     
                        
            $products = unserialize(base64_decode($telr->productdata))['items'];
            foreach($products as $product) {

                $refundamount = $refund->amount;
                $timecreated = userdate($refund->timecreated, get_string('strftimedatemonthabbr', 'core_langconfig'));
                $transactionid = $refund->transactionid;
                $productrecord = $DB->get_record('tool_products', ['id' => $product['product_id']]);
                if((int)$refund->userid) {
                    $sql = " SELECT lo.fullname as organization, CONCAT(lu.firstname, ' ', lu.lastname) as username FROM {local_organization} lo JOIN {local_users} lu ON lu.organization = lo.id WHERE lu.userid = ".(int)$refund->userid;
                    $userinfo = $DB->get_record_sql($sql);
                }
                $refund = [];
                $refund['id'] = $transactionid;
                $refund['trainingname'] = $product['name'];
                $refund['taxes'] = 0;
                $refund['discountprice'] = round($refundamount, 2);
                $amountwithtaxes = $DB->get_field('tool_user_order_payments', 'amount', ['telrid' =>$telr->id]);
                $refund['originalprice'] = $amountwithtaxes;
                $refund['finalprice'] = $product['total'];
                $refund['transcationref'] = $telr->transactionref;
                $refund['transcationnumber'] = $telr->transactioncode;
                $totalcost=$totalcost+$refundamount;
                $refund['paymentstatus'] = get_string('refund','tool_product');
                $refund['duedate'] = '--';
                $refund['paymentdate'] = $timecreated;
                $refund['organization'] = 0;
                $refund['fieldname'] = 'id';
                $refund['trainingstatus'] = 'alert alert-success';
                $refund['referenceid'] = $productrecord->referenceid;
                $refund['name'] = $product['name'];
                $refund['refund'] = true;
                $refund['fieldid'] = $productrecord->referenceid;
                $refund['organizationname'] = ($userinfo) ? $userinfo->organization : '' ;
                $refund['username'] = ($userinfo) ? $userinfo->username : '';
                switch($productrecord->category){
                    case self::TRAINING_PROGRAM :
                        $refund['tablename'] = 'tp_offerings';
                        $programid = $DB->get_field('tp_offerings', 'trainingid', ['id' => $productrecord->referenceid]);
                        $refund['entityid'] = $programid;
                        break;
                    case self::EXAMS;
                        $refund['tablename'] = 'local_exam_profiles';
                        $examid = $DB->get_field('local_exam_profiles', 'examid', ['id' => $productrecord->referenceid]);
                        $refund['entityid'] = $examid;
                        break;
                    case self::EVENTS:
                        $refund['tablename'] = 'local_events';
                        $refund['entityid'] = $productrecord->referenceid;
                    break;           
                }
            }
            $data[] = $refund;
             
        }
         
        $records = ['data' => $data, 'totalcost'=> $totalcost, 'totalcount'=> $totalcount];
        return $records;
        //return compact('data', 'totalcost','totalrefund');
    }

    public function get_orgpayments($stable,$filtervalues) {
        global $DB, $USER, $CFG;
        $lang = current_language();
        $systemcontext = context_system::instance();
        $countsql = " SELECT COUNT(si.id) ";
        $selectsql = " SELECT si.*, oop.id AS transactionid, si.type, si.userid, si.invoice_number, si.payableamount, oas.approvalseats, oop.orguserid";
        $fromsql = " FROM {tool_product_sadad_invoice} si
                     LEFT JOIN {tool_org_order_payments} AS oop ON oop.transactionid = si.invoice_number
                     LEFT JOIN {tool_order_approval_seats} oas ON oas.paymentid = oop.id
                     JOIN {tool_products} tp ON tp.id = si.productid
                    " ;
        $params = [];
        if((!is_siteadmin()) && (has_capability('local/organization:manage_organizationofficial', $systemcontext))) {
            $orgid = $DB->get_field('local_users', 'organization', ['userid' => $USER->id]);
            $orguserid = (new exams)->get_all_orgofficials($USER->id);
            $orguserid = implode(',',$orguserid);
            $oldertransactions = $DB->get_field_sql("SELECT DISTINCT userid FROM {tool_product_sadad_invoice} WHERE organization != $orgid AND userid = $USER->id ");
            // Get Old officials if any..
            $old_officials = $DB->get_fieldset_sql("SELECT DISTINCT userid FROM {tool_product_sadad_invoice} WHERE organization = $orgid AND userid NOT IN($orguserid) ");
            if ($old_officials) {
                $odl_officialids = implode(',', $old_officials);
                $all_officials = $orguserid.','.$odl_officialids;
                $fromsql .= " AND  FIND_IN_SET(si.userid, '$all_officials')";
            }elseif ($oldertransactions == $USER->id) {
                $all_officials = $orguserid.','.$oldertransactions;
                $fromsql .= " AND  FIND_IN_SET(si.userid, '$all_officials')";
            }
            else{
                $all_officials = $orguserid;
                $fromsql .= " AND  FIND_IN_SET(si.userid, '$all_officials') AND si.organization = $orgid";
            }
        }
        $wheresql = " WHERE 1=1 "; 
        if (!empty($filtervalues->search_query)) {
            $fromsql .= " AND ( si.invoice_number LIKE '%$filtervalues->search_query%' ) ";
        }
        $examsid = array();
        $examsidonly = array();
        if(!empty($filtervalues->exams)){
            $profileid = $DB->get_fieldset_sql("SELECT id FROM {local_exam_profiles} WHERE examid IN($filtervalues->exams)");
            $i = implode(',',$profileid);
            $examsid = $DB->get_fieldset_sql("SELECT id FROM {tool_products} as top WHERE top.category = 2 AND top.referenceid IN($i)");    
        }
         if(!empty($filtervalues->prgevntexams) && $filtervalues->prgevntexams==3){

            $profileids = $DB->get_fieldset_sql("SELECT id FROM {local_exam_profiles} ");
            $i = implode(',',$profileids);
            $examsidonly = $DB->get_fieldset_sql("SELECT id FROM {tool_products} as top WHERE top.category = 2 AND top.referenceid IN($i)");          

        }
        $trainingprogramid = array();  
        $trainingprogramidonly = array();  

        if(!empty($filtervalues->trainingprograms)){

           $profileid = $DB->get_fieldset_sql("SELECT id FROM {tp_offerings} WHERE trainingid IN($filtervalues->trainingprograms)");
            $j = implode(',',$profileid);
            $trainingprogramid = $DB->get_fieldset_sql("SELECT id FROM {tool_products} as top WHERE top.category = 1 AND top.referenceid IN($j)");
                   
        }

          if(!empty($filtervalues->prgevntexams) && $filtervalues->prgevntexams==1){
            $profileid = $DB->get_fieldset_sql("SELECT id FROM {tp_offerings}");
            $j = implode(',',$profileid);
            $trainingprogramidonly = $DB->get_fieldset_sql("SELECT id FROM {tool_products} as top WHERE top.category = 1 AND top.referenceid IN($j)");
                   
        }

        $eventsid = array();
        $eventsidonly = array();

        if(!empty($filtervalues->events)){

            $eventsid = $DB->get_fieldset_sql("SELECT id FROM {tool_products} as top WHERE top.category = 3 AND top.referenceid IN($filtervalues->events)");
          
        }  
      if(!empty($filtervalues->prgevntexams) && $filtervalues->prgevntexams==2){
            $sql = "SELECT evnt.id,evnt.title  
             FROM {local_events} AS evnt GROUP by evnt.id ORDER BY evnt.id DESC";         
            $events = $DB->get_fieldset_sql($sql);
            $eid = implode(',',$events);
            $eventsidonly = $DB->get_fieldset_sql("SELECT id FROM {tool_products} as top WHERE top.category = 3 AND top.referenceid IN($eid)");
        } 

        $offeringlocationid = array();
        if(!empty($filtervalues->offeringlocation)){

            $offeringlocation = $DB->get_fieldset_sql("SELECT id FROM {tp_offerings} WHERE halllocation IN($filtervalues->offeringlocation)");
            $l = implode(',',$offeringlocation);

            $offeringlocationid = $DB->get_fieldset_sql("SELECT id FROM {tool_products} as top WHERE  top.referenceid IN($l)");

        }

        $paymentmode = array();
        if(!empty($filtervalues->paymentmode)){

            $paymentmode = $DB->get_fieldset_sql("SELECT productid FROM {tool_org_order_payments} AS tp WHERE tp.paymenttype LIKE '%$filtervalues->paymentmode%'");

        }

        $ids = array_merge($examsid,$trainingprogramid,$eventsid,$offeringlocationid,$paymentmode,$trainingprogramidonly,$eventsidonly,$examsidonly);

        $whereids = implode(',',$ids);

        if ($whereids) {      
            $wheresql .= " AND tp.id IN($whereids) ";
        }
     
        if(!empty($filtervalues->organization)){

        if(!empty($filtervalues->organization)){
            $organizationlist = explode(',',$filtervalues->organization);
        }

        list($relatedorganizationlistsql, $relatedorganizationlistparams) = $DB->get_in_or_equal($organizationlist, SQL_PARAMS_NAMED, 'organizationlist');
        $params = array_merge($params,$relatedorganizationlistparams);
        $wheresql .= " AND EXISTS (SELECT lc.userid 
            FROM {local_users} lc 
            JOIN {local_organization} org ON org.id = lc.organization
            WHERE lc.organization $relatedorganizationlistsql AND lc.userid=si.userid AND lc.deleted != 1) > 0 AND  si.organization IN ($filtervalues->organization)";
        
        }


        if(!empty($filtervalues->useridnumber)){
            if(!empty($filtervalues->useridnumber)){
                $organizationlist = explode(',',$filtervalues->useridnumber);
            }
            
            list($relatedorganizationlistsql, $relatedorganizationlistparams) = $DB->get_in_or_equal($organizationlist, SQL_PARAMS_NAMED, 'organizationlist');
            $params = array_merge($params,$relatedorganizationlistparams);
            
            $wheresql .= " AND EXISTS (SELECT lc.userid 
                FROM {local_users} lc 
                JOIN {local_organization} org ON org.id = lc.organization
                WHERE lc.id $relatedorganizationlistsql AND lc.userid=si.userid) > 0";
            
        }
        
        if (!empty($filtervalues->offeringstatus)){
            $trainings = explode(',',$filtervalues->offeringstatus);
            if(!empty($trainings)){
            $trainingquery = array();
            foreach ($trainings as $training) {
                if($training == 'completed') {
                    $trainingquery[] = " si.productid IN (SELECT tp.id 
                     FROM {tool_products} AS tp 
                     JOIN {tp_offerings} AS tpofr ON tpofr.id = tp.referenceid
                     JOIN {local_trainingprogram} AS ltp ON ltp.id = tpofr.trainingid 
                     JOIN {program_completions} AS pc ON pc.programid = ltp.id AND pc.offeringid = tpofr.id
                     WHERE pc.completion_status = 1 AND tp.category= 1 ) ";
                   } elseif($training == 'financially_closed') {
                    $expired =  (new trainingprogram())->from_unixtime_for_expired_entities('tpofr.enddate');
                    $trainingquery[] = " si.productid IN (SELECT tp.id 
                     FROM mdl_tool_products AS tp 
                     JOIN mdl_tp_offerings AS tpofr ON tpofr.id = tp.referenceid
                     JOIN mdl_local_trainingprogram AS ltp ON ltp.id = tpofr.trainingid 
                     WHERE $expired  AND tp.category= 1 ) ";
                    }
                }
                $trainingqueeryparams =implode('OR',$trainingquery);
                $wheresql .= ' AND '.$trainingqueeryparams.' ';
            }
        }
        if($filtervalues->{'betweendaterangefrom[enabled]'} == 1 ){
                $start_year = $filtervalues->{'betweendaterangefrom[year]'};
                $start_month = $filtervalues->{'betweendaterangefrom[month]'};
                $start_day = $filtervalues->{'betweendaterangefrom[day]'};
                $filter_starttime_con = mktime(0,0,0, $start_month, $start_day, $start_year);
                $wheresql .= " AND si.timecreated >= '$filter_starttime_con' ";
        }
        if($filtervalues->{'betweendaterangeto[enabled]'} == 1 ){

            $start_year = $filtervalues->{'betweendaterangeto[year]'};
            $start_month = $filtervalues->{'betweendaterangeto[month]'};
            $start_day = $filtervalues->{'betweendaterangeto[day]'};
            $filter_endtime_con=mktime(23,59,59, $start_month, $start_day, $start_year);
            $wheresql .= " AND si.timecreated <= '$filter_endtime_con' ";

        }
        
        if(!empty($filtervalues->offeringtype)){

            if(!empty($filtervalues->offeringtype)){
                $trainingprogramslist = explode(',',$filtervalues->offeringtype);
            }
            list($relatedtrainingprogramslistsql, $relatedtrainingprogramslistparams) = $DB->get_in_or_equal($trainingprogramslist, SQL_PARAMS_NAMED, 'trainingprogramslist');
            $params = array_merge($params,$relatedtrainingprogramslistparams);
            $wheresql .= " AND EXISTS (SELECT tpofr.id 
            FROM {tp_offerings} AS tpofr 
            JOIN {local_trainingprogram} AS ltp ON tpofr.trainingid=ltp.id
            WHERE tpofr.trainingmethod $relatedtrainingprogramslistsql AND tpofr.id=tp.referenceid) > 0 AND tp.category = 1";
        } 

        $ordersql = " ORDER BY si.id DESC ";
        $payments = $DB->get_records_sql($selectsql.$fromsql.$wheresql.$ordersql,$params, $stable->start,$stable->length );  
         
        $totalpayments = $DB->count_records_sql($countsql. $fromsql.$wheresql.$ordersql,$params);
        $totalcost=0;
        $data = [];
        foreach($payments as $payment) {
            $row = [];
            $transcationid = $DB->get_field('tool_product_sadad_invoice', 'invoice_number', ['productid' => $payment->productid, 'userid' => $payment->userid, 'type' => 'purchase']);
           if($payment->type == 'cancel' || $payment->type == 'replacement' || $payment->type == 'reschedule'){
                $row['purchasedseats'] = 1;
                $row['usedseats'] = 0;
                $row['approvalseats'] = $payment->seats;
                $row['approvedseats'] = 0;
                $sql = " SELECT op.id, op.purchasedseats, op.purchasedseats AS approvalseats,op.fieldid,op.orguserid, op.originalprice, op.amount, op.id as orgorderid, op.taxes, op.discountprice
                FROM {tool_org_order_payments} op 
                WHERE op.productid = $payment->productid AND op.transactionid = ".$payment->invoice_number;  

            } else {
                $row['purchasedseats'] = $payment->seats;
                $row['usedseats'] = $payment->approvalseats;//!empty($entity['usedseats']) ? $entity['usedseats'] : '0';
                $row['approvalseats'] = $payment->seats;
                $row['approvedseats'] = number_format($payment->approvalseats);
                if($payment->type == 'replacement'){
                    $sql = " SELECT op.id, op.purchasedseats, op.purchasedseats AS approvalseats,op.fieldid,op.orguserid, op.originalprice, op.amount, op.id as orgorderid, op.taxes, op.discountprice
                    FROM {tool_org_order_payments} op 
                    WHERE op.productid = $payment->productid AND op.transactionid = ".$payment->invoice_number;  
                } else {
                    $sql = " SELECT op.id, op.purchasedseats, oas.approvalseats,oas.fieldid,oas.orguserid, op.originalprice, op.amount, op.id as orgorderid, op.taxes, op.discountprice
                    FROM {tool_order_approval_seats} oas
                    JOIN {tool_org_order_payments} op ON op.id = oas.paymentid 
                    WHERE op.productid = $payment->productid AND op.transactionid = ".$payment->invoice_number;                 
                }
               
            }
            $transcations = $DB->get_record_sql($sql);
            $row['id'] = $payment->transactionid;//$transcations->id;
            $product = $DB->get_record('tool_products', ['id' => $payment->productid]);

            $entity = self::entityinformation($product, $transcations->orguserid);
            $row['referenceid'] = $payment->id;
            if ($payment->type == 'purchase' || $payment->type == 'programsbulkenrollment' || $payment->type == 'examsbulkenrollment'|| $payment->type == 'assessment_operation_enrolments') {
                $discount_price = !empty($transcations->discountprice) ? $transcations->discountprice : 0;
                $taxes = !empty($transcations->taxes) ? ($transcations->taxes/$transcations->purchasedseats)*$payment->seats : 0;
            } else {
                $discount_price = 0;
                $taxes = 0;
            }
            $row['discount_price'] = $discount_price;
            $row['taxes'] = $taxes;
            $row['paymentdate'] = $payment->timecreated;
            $row['paymentmethod'] = get_string('postpaid', 'tool_product');
            if ($payment->payment_status == 0) {
                $paymentstatus = get_string('unpaidlabel', 'tool_product');
            } else {
                $paymentstatus = get_string('paidlabel', 'tool_product');
            }

            if($payment->status == -1){
                $status = get_string('cancelled', 'tool_product');
            }else{
                $status = ($payment->status == 1) ? get_string('active', 'tool_product') : get_string('cancelled', 'tool_product');
            }

            if ($payment->status == -1) {
                $invoicecode = get_string('inactive', 'tool_product');
            } else {
                $invoicecode = get_string('active', 'tool_product');
            }
            if(($payment->type == 'cancel') && ($payment->amount == 0)){
                $status = '--';
                $paymentstatus = '--';
                $payment->invoice_number = null;
                $invoicecode = '--';

            }
            $row['status'] = $status;
            $row['invoicestatuscode'] = $invoicecode;
            $row['timecreated'] = userdate($payment->timecreated, get_string('strftimedatemonthabbr', 'core_langconfig'));
            $row['paymentmethod'] = get_string('postpaid', 'tool_product');
            $approval_price = $transcations->originalprice/$transcations->purchasedseats;
            $total_price = $approval_price*$payment->seats;
            $row['total_price'] = ($total_price) ? ($this->is_decimal($total_price)? number_format($total_price,2) : number_format($total_price)) : 0;
            $total = $total_price + $discount_price + $taxes;
            $row['total'] = ($total > 0) ? (($this->is_decimal($total)? number_format($total,2) : number_format($total))): 0;
            $totalcost=$totalcost+$transcations->amount;
            $row['finaltotalamout'] =  ($total > 0) ?  $total : 0;
            $row['itemcode'] =  $product->code;
            if(!is_siteadmin() && has_capability('local/organization:manage_communication_officer',$systemcontext)) {
                $row['costview']=false;
            } else {
                $row['costview']=true;
            }
            $row['cost'] = round($payment->payableamount,2);
            $fullname = (new trainingprogram())->user_fullname_case();
            $orgfullname=($lang == 'ar') ? 'org.fullnameinarabic as orgname' : 'org.fullname as orgname';
            $sql="SELECT u.id,$fullname,$orgfullname, u.email, org.fullnameinarabic as orgnamear, org.fullname as orgnameen, concat(lc.firstname,' ',lc.lastname) as usernameen, concat(lc.firstnamearabic,' ',lc.lastnamearabic) as usernamear 
                    FROM {user} AS u 
                    JOIN {local_users} lc ON lc.userid = u.id
                    JOIN {local_organization} org ON org.id = lc.organization
                   WHERE  u.id=:orguserid ";
            $user=$DB->get_record_sql($sql,array('orguserid'=>$payment->userid));
            $row['productid']= $payment->productid;
            $row['orgnamear']=($user) ? $user->orgnamear : '--';
            $row['username']=($user) ? $user->usernameen : '--';
            $row['usernamear']=($user) ? $user->usernamear : '--';
            $row['organizationcode']=($user) ? $user->shortname : 'NA';
            if($user){
                $row['organizationname']=($payment->userid) ? $DB->get_field_sql('SELECT org.fullname FROM {local_organization} org JOIN {local_users} lou ON lou.organization = org.id WHERE lou.userid=:orguserid',['orguserid'=>$payment->userid])  : '--';
                $row['orgoffcialname']= ($user) ? $user->fullname : 'NA';
                $row['email']= ($user) ? $user->email : 'NA';
            }else{
                $sql="SELECT u.id,concat(u.firstname,' ',u.lastname) as fullname, u.email
                FROM {user} AS u 
                WHERE  u.id=:userid ";
                $user=$DB->get_record_sql($sql,array('userid'=>$payment->userid));
                $row['orgoffcialname']= ($user) ? $user->fullname : fullname($user);
                $row['email']= ($user) ? $user->email : 'NA';
                $row['organizationname']= '--';
            }
            $row['orguserid']= ($user) ? $payment->orguserid : 0;

            if(is_siteadmin() || has_capability('local/organization:manage_communication_officer',$systemcontext) || has_capability('local/organization:manage_financial_manager',$systemcontext) && !empty($user->fullname)) {
                $row['sendemailactionview']=true;
            } else {
                $row['sendemailactionview']=false;
            }

            $row['mode']=get_string('postpaid','tool_product');
            $row['duedate']= !empty($entity['entity']->availablefrom) ? userdate($entity['entity']->availablefrom, get_string('strftimedatefullshort', 'langconfig')) : '--';
            $row['pdftimecreated'] = userdate($payment->timecreated, get_string('strftimedatefullshort', 'langconfig'));
            $row['paymentduedate']=$entity['entity']->availablefrom;
            $row['entityoldid']= !empty($entity['entity']->oldid) ? $entity['entity']->oldid : '--';
            $row['description']= !empty($entity['entity']->description) ? base64_encode(format_text($entity['entity']->description)) : '--';

            if((has_capability('tool/products:managefinancialpayments', $systemcontext) || has_capability('local/organization:manage_financial_manager',$systemcontext))){
                $row['paymentsupdate']=true;
            } else {
                $row['paymentsupdate']=false;
            }
            $row['paymentstartdate'] = $entity['entity']->availablefrom;
            $row['paymentenddate'] = $entity['entity']->availableto;
            $row['transactionid'] = !empty($payment->invoice_number) ? $payment->invoice_number : '--';

            $row['startdate'] = userdate($entity['entity']->availablefrom, get_string('strftimedatefullshort', 'langconfig'));
            $row['enddate'] = userdate($entity['entity']->availableto, get_string('strftimedatefullshort', 'langconfig'));
            $row['type'] = $entity['entity']->type;

            if( userdate($entity['entity']->availablefrom, '%Y-%M-%d') < userdate(time(), '%Y-%M-%d') ) {
                $row['trainingstatus'] = 'alert alert-warning';
            }elseif( userdate($entity['entity']->availablefrom, '%Y-%M-%d') > userdate(time(), '%Y-%M-%d') ) {
                $row['trainingstatus'] = 'alert alert-info';
            }elseif( userdate($entity['entity']->availablefrom, '%Y-%M-%d') == userdate(time(), '%Y-%M-%d')) {
                $row['trainingstatus'] = 'alert alert-danger';
            }else{
                $row['trainingstatus'] =  'bg-theme_dark';
            }
            // Setting the name of the column dynamically.
            if ($row['type'] == 'exam') {
                $row['productname'] = get_string('examname', 'tool_product');
            }elseif ($row['type'] == 'event') {
                $row['productname'] = get_string('eventname', 'tool_product');
            }else{
                $row['productname'] = get_string('programname', 'tool_product');
                $row['pname'] = true;
            }
            
            // renu
            if(has_capability('local/organization:manage_financial_manager',$systemcontext)){
                $program = $DB->get_record_sql("SELECT * FROM {tool_products} as product JOIN {tp_offerings} as tpo ON product.referenceid=tpo.id WHERE product.id=$payment->productid");
                if($program->tagrement==1){ 
                    $row['tagreement'] =  true;      
                    $itemid=$program->tagrrement;
                    $fs = get_file_storage();
                    $files = $fs->get_area_files($systemcontext->id, 'local_trainingprogram', 'tagrrement', $itemid);
                    foreach($files as $file){
                        $url = moodle_url::make_pluginfile_url($file->get_contextid(), $file->get_component(), $file->get_filearea(),
                        $file->get_itemid(), $file->get_filepath(), $file->get_filename(), false);
                        $downloadurl = $url->get_scheme() . '://' . $url->get_host() . ':' . $url->get_port() . $url->get_path();
                        $row['trainingagreement'] = $downloadurl;
                    }
                }
            }
            $row['unitprice'] = !empty($transcations->originalprice/$row['purchasedseats']) ? (int)($transcations->originalprice/$row['purchasedseats']) : 0;
            $discountpercentage = (int)(($row['discount_price']/$row['unitprice'])*100);
            $row['discount_percentage'] = !empty($discountpercentage) ? $discountpercentage : 0;
            $taxpercentage = (int)(($row['taxes']/$transcations->originalprice)*100);
            $row['taxes_percentage'] = !empty($taxpercentage) ? $taxpercentage : 0;
            $row['name'] = base64_encode(format_text($entity['entity']->enname)); // For Invoice
            $row['orderinfo'] = base64_encode(serialize($row));
            $row['invoicetype'] = ($payment->type == 'purchase') ? get_string('purchased', 'tool_product') : (($payment->type) ? get_string($payment->type, 'tool_product') : '--');
            $row['trainingname'] = $entity['entity']->enname;
            $row['name'] = $entity['entity']->enname;
            $row['statuscode'] = $paymentstatus;
            $row['sendemailurl'] = $CFG->wwwroot.'/admin/tool/product/sendemailtoorgofficial.php?id='.$payment->orgorderid;
            if(is_siteadmin() || has_capability('local/organization:manage_financial_manager',$systemcontext)) {
                $row['invoiceaction']=true;
            } else {
                $row['invoiceaction']=false;
            }
            $data[] = $row;
        }
        $records = ['totalcount' => $totalpayments, 'data'=> $data, 'totalcost'=> $totalcost];
        return $records;
    }
    public function is_decimal($val){ 
        return is_numeric( $val ) && floor( $val ) != $val;
    }
    public function entityinformation($product, $orguserid=false) {
        global $DB;
        $lang = current_language();
        $entity = '';
        switch($product->category){
            case self::TRAINING_PROGRAM :
                $offringlabel=get_string('offeringid','local_trainingprogram');
                if( $lang == 'ar'){
                    $tpname='CONCAT(tp.namearabic," </br> ('.$offringlabel.' ",tpo.code,")") as enname';
                }else{
                    $tpname='CONCAT(tp.name," </br> ('.$offringlabel.' ",tpo.code,")") as enname';
                }
                $sql = "SELECT tp.id, $tpname, (tpo.startdate + tpo.time) as availablefrom, (tpo.enddate + tpo.endtime) as availableto, 'trainingprogram' as type, tp.oldid as oldid, tp.description
                        FROM {local_trainingprogram} tp 
                        JOIN {tp_offerings} tpo ON tpo.trainingid = tp.id 
                        WHERE tpo.id = ".$product->referenceid;
                        $entity = $DB->get_record_sql($sql);
                if($orguserid) {
                    $usedseats=(new \local_trainingprogram\local\trainingprogram())->get_erolled_seats($product->referenceid, true, $orguserid);
                }
            break;
            case self::EXAMS :
                $profilelabel=get_string('profile','local_exams');
                if( $lang == 'ar'){
                    $examname='CONCAT(e.examnamearabic," </br> ('.$profilelabel.' : ",ep.profilecode,")") as enname';
                }else{
                    $examname='CONCAT(e.exam," </br> ('.$profilelabel.' : ",ep.profilecode,")") as enname';
                }

                $sql = "SELECT $examname, 0 as availablefrom, 0 as availableto, 'exam' as type, e.old_id as oldid, e.programdescription as description
                          FROM {local_exams} e
                          JOIN {local_exam_profiles} ep ON ep.examid = e.id 
                         WHERE ep.id =".$product->referenceid;
                         $entity = $DB->get_record_sql($sql);
                if($orguserid) {
                    $usedseats = (new \local_exams\local\exams())->entity_enrolled($product->referenceid, $orguserid);
                }
                break;
            case self::EVENTS :
                if( $lang == 'ar'){
                    $eventname='e.titlearabic as enname';
                }else{
                    $eventname='e.title as enname';
                }
                $sql = "SELECT $eventname, (e.startdate + e.slot) as availablefrom, e.enddate as availableto, 'event' as type, e.description
                            FROM {local_events} e 
                        WHERE e.id =".$product->referenceid;
                        $entity = $DB->get_record_sql($sql);
                if($orguserid) {
                    $usedseats = (new \local_events\events())->get_erolled_seats($product->referenceid, $orguserid);
                }
                     
            break;
            case self::EXAMATTEMPT :
                $attemptlabel=get_string('attempt','local_exams');

                if( $lang == 'ar'){
                    $attemptname='CONCAT(e.examnamearabic," </br> ('.$attemptlabel.' : ",ep.attemptid,")") as enname';
                }else{
                    $attemptname='CONCAT(e.exam," </br> ('.$attemptlabel.' : ",ep.attemptid,")") as enname';
                }

                $sql = "SELECT $attemptname, 0 as availablefrom, 0 as availableto, 'exam' as type, e.old_id as oldid, e.programdescription as description
                          FROM {local_exams} e
                          JOIN {local_exam_attempts} ep ON ep.examid = e.id 
                         WHERE ep.id =".$product->referenceid;
                         $entity = $DB->get_record_sql($sql);
                if($orguserid) {
                    $usedseats = (new \local_exams\local\exams())->entity_enrolled($product->referenceid, $orguserid);
                }
                break;            
        }
        return ['entity' => $entity, 'usedseats' => $usedseats];
    }

    public function get_orgoff_orderdata($orderid) {
        global $DB, $USER;
        $seatsinfo = '';
        $productinfo = '';
        $availableseats = $this->product_availableseats($orderid);
        $sql = " SELECT toas.id, toas.paymentid, toop.purchasedseats as seats, toop.productid, toop.orguserid as userid, toop.amount, toas.organization,toop.fieldid, toop.tablename
                  FROM {tool_order_approval_seats} toas 
                  JOIN {tool_org_order_payments} toop ON toop.id = toas.paymentid 
                 WHERE toas.id = ".$orderid;
        $orderdetails = $DB->get_record_sql($sql);
        if($availableseats['eventmethod'] && $orderdetails->seats > $availableseats['availableseats']) {
            $details['seatnotexist'] = true;
            $details['availableseats'] = $availableseats['availableseats'];
            $seatsinfo = json_encode($details);
        } else {
            $price = $DB->get_field('tool_products', 'price', ['id' => $orderdetails->productid]);
           
            // Tax calculation for old data START.
            switch ($orderdetails->tablename) {
                case 'tp_offerings':
                    $entityid = $DB->get_field('tp_offerings','	trainingid',['id' => $orderdetails->fieldid]);
                    break;
                case 'local_exam_profiles':
                    $entityid = $DB->get_field('local_exam_profiles','examid',['id' => $orderdetails->fieldid]);
                    break;
                case 'local_events':
                    $entityid = $DB->get_field('local_events','	id',['id' => $orderdetails->fieldid]);
                    break;
            }
            $orgorder = new stdClass();
            $orgorder->selectedseats = $orderdetails->seats;
            $orgorder->fieldname = 'id';
            $orgorder->tablename = $orderdetails->tablename;
            $orgorder->fieldid = $orderdetails->fieldid;
            $orgorder->parentfieldid = $entityid;
            $orgorderresponse = (new \local_exams\local\exams)->get_orgorderinfo($orgorder);
            $productdata = unserialize(base64_decode($orgorderresponse['returnparams']));
            // Tax calculation for old data END.
            $productname = $DB->get_field('tool_products', 'name', ['id' => $orderdetails->productid]);
            $details['productname'] = $productname;
            $details['paymentid'] = $orderdetails->paymentid;
            $details['approvalseats'] = $orderdetails->seats;
            $details['productid'] = $orderdetails->productid;
            $details['seats'] = $orderdetails->seats;
            $details['type'] = 'postpaid';
            $details['total'] = $productdata['total'];//$price*$orderdetails->seats;
            $details['userid'] = $orderdetails->userid;
            $details['approvaluserid'] = $USER->id;
            $details['organization'] = $orderdetails->organization;
            $details['taxes'] = $productdata['taxes'];
            $productinfo = base64_encode(serialize($details));
        }
        return ['productinfo' => $productinfo, 'seatsinfo' => $seatsinfo];
    }

    public function product_availableseats($orderid) {
        global $DB, $USER;
        $eventmethod = true;
        $get_order_data = $DB->get_record('tool_order_approval_seats',['id' => $orderid]);
        switch($get_order_data->tablename) {
            case 'tp_offerings':
                $availableseats = (new \local_trainingprogram\local\trainingprogram)->get_available_seats($get_order_data->fieldid);
            break;
            case 'local_exam_profiles':
                $seats = (new \local_exams\local\exams)->get_available_seats($get_order_data->fieldid);
                if($seats['seatstatus']) {
                    $eventmethod = false; 
                } else {
                    $availableseats = $seats['availableseats'];
                    $eventmethod = true;
                }
            break;
            case 'local_events';
                $eventmethod = $DB->get_field('local_events','method',['id' =>$get_order_data->fieldid]);
                if($eventmethod==0) {
                    $seats = (new \local_events\events)->events_available_seats($get_order_data->fieldid);
                    $availableseats = $seats['availableseats'];
                    $eventmethod = true;
                } else {
                    $eventmethod = false;
                }
                
            break;
        } 
        return ['availableseats'=>$availableseats, 'eventmethod' => $eventmethod];
    }

    public function get_my_cancelpayments($stable,$filterdata=null) {
        global $DB;

        $systemcontext = context_system::instance();
        $countsql = "SELECT COUNT(lcl.id) ";
        $selectsql = "SELECT lcl.* ";
        $fromsql = " FROM {local_cancel_logs} lcl 
                     JOIN {tool_products} top ON lcl.productid = top.id ";
                     
        $orderby = " ORDER BY lcl.id DESC ";

        $trainingpurchase = self::trainingpayments_filter($filterdata);
        // print_r($fromsql.$trainingpurchase['sql']);exit;
        $sql = $fromsql.$trainingpurchase['sql'];
    
        $records = $DB->get_records_sql($selectsql.$sql.$orderby,$trainingpurchase['params'], $stable->start,$stable->length );
        $totalpayments = $DB->count_records_sql($countsql. $sql,$trainingpurchase['params']);
        $totalcost=0;
        $data = [];

        foreach($records as $record) {
            $row = [];
            $row['approvedseats'] = 0;
            $row['cost'] = $record->refundamount;
            $row['costview'] = true;
            $totalcost = $totalcost+$record->refundamount;
            $row['id'] = $record->id;
            $sql = " SELECT lo.fullname as organization, CONCAT(lu.firstname, ' ', lu.lastname) as username FROM {local_organization} lo JOIN {local_users} lu ON lu.organization = lo.id WHERE lu.userid = ".$record->userid;
            $userinfo = $DB->get_record_sql($sql);
            $row['organizationname'] = $userinfo->organization;

            $product = $DB->get_record('tool_products', ['id'=> $record->productid]);
            switch ($product->category) {
                case 1:    
                    $offeringid = $DB->get_field('tp_offerings', 'trainingid', ['id'=>$product->referenceid]);
                    $productname = $DB->get_record('local_trainingprogram',array('code'=>$product->code), 'name as title, namearabic as titlearabic');
                break;    
                case 2:
                    $profile = $DB->get_field('local_exam_profiles','examid',['id'=>$product->referenceid]);
                    $productname = $DB->get_record('local_exams',array('id'=>$profile), 'exam as title, examnamearabic as titlearabic');       
                break;    
                case 3:       
                    $productname = $DB->get_record('local_events', ['id'=>$product->referenceid], 'title, titlearabic'); 
                break;
            }
            $lang = current_language();
            $row['trainingname'] = ($lang=='ar') ? $productname->titlearabic : $productname->title;
            $row['trainingstatus'] = "alert alert-warning";
            $row['username'] = $userinfo->username;
            $data[] = $row;
        }

        $records = ['totalcount' => $totalpayments, 'records'=> $data, 'totalcost'=> $totalcost];
        return $records;
    }
    public function delete_rejected_erollments($order) {
        global $DB;
        switch ($order->tablename) {
            case 'tp_offerings':
                $record = $DB->record_exists('program_enrollments',array('offeringid'=>$order->fieldid,'usercreated' => $order->orguserid,'orderid'=>$order->id));
                if($record) {
                    $DB->delete_records('program_enrollments',array('offeringid'=>$order->fieldid,'usercreated' => $order->orguserid,'orderid'=>$order->id));
                }
            break;
            case 'local_exam_profiles':
                $record = $DB->record_exists('exam_enrollments',array('profileid'=>$order->fieldid,'usercreated' => $order->orguserid,'orderid'=>$order->id));
                if($record) {
                    $DB->delete_records('exam_enrollments',array('profileid'=>$order->fieldid,'usercreated' => $order->orguserid,'orderid'=>$order->id));
                }
            break;
            case 'local_events':
                $record = $DB->record_exists('local_event_attendees',array('eventid'=>$order->fieldid,'usercreated' => $order->orguserid,'orderid'=>$order->id));
                if($record) {
                    $DB->delete_records('local_event_attendees',array('eventid'=>$order->fieldid,'usercreated' => $order->orguserid,'orderid'=>$order->id));
                }
            break;
        }
    }

    public function get_user_organization($userid) {
        global $DB;
        $admin = $DB->record_exists('user',['username' => 'admin','id' => $userid]);
        if(!$admin) {
            $organization  = $DB->get_field('local_users','organization',['userid' => $userid]);
        } else {
            $organization = false;
        }
        return $organization;
    }

    public function trainingpayments_filter($filterdata){
        global $DB,$CFG, $USER;
        $params         = array();
        $payments      = array();
        $paymentscount = 0;
        $totalcost = 0;
        $systemcontext = context_system::instance();
        if(!is_siteadmin() && has_capability('local/organization:manage_trainee',$systemcontext)) {
          
            if ($filterdata->mode == 'paid') {                
                $sql .= " AND ordrpmnt.userid =:userid";
                $params['userid'] = $USER->id;            
            }

            if($filterdata->{'betweendaterangefrom[enabled]'} == 1 ){

                $start_year = $filterdata->{'betweendaterangefrom[year]'};
                $start_month = $filterdata->{'betweendaterangefrom[month]'};
                $start_day = $filterdata->{'betweendaterangefrom[day]'};
                $filter_starttime_con = mktime(0,0,0, $start_month, $start_day, $start_year);

                if ($filterdata->mode == 'paid') {
                    $sql.= " AND ordrpmnt.timecreated >= '$filter_starttime_con' ";
                }elseif($filterdata->mode == 'refund'){

                    $sql.= " AND tpr.timecreated >= '$filter_starttime_con' ";
                }
                

            }
            if($filterdata->{'betweendaterangeto[enabled]'} == 1 ){
                $start_year = $filterdata->{'betweendaterangeto[year]'};
                $start_month = $filterdata->{'betweendaterangeto[month]'};
                $start_day = $filterdata->{'betweendaterangeto[day]'};
                $filter_endtime_con=mktime(23,59,59, $start_month, $start_day, $start_year);

                if ($filterdata->mode == 'paid') {

                    $sql.=" AND ordrpmnt.timecreated <= '$filter_endtime_con' ";
                    
                }elseif($filterdata->mode == 'refund'){

                    $sql.=" AND tpr.timecreated <= '$filter_endtime_con' ";
                }
                

            }

        }else{

            if($filterdata->{'betweendaterangefrom[enabled]'} == 1 ){

                $start_year = $filterdata->{'betweendaterangefrom[year]'};
                $start_month = $filterdata->{'betweendaterangefrom[month]'};
                $start_day = $filterdata->{'betweendaterangefrom[day]'};
                $filter_starttime_con = mktime(0,0,0, $start_month, $start_day, $start_year);

                if ($filterdata->mode == 3) {                        
                    $sql .= " AND lcl.timecreated >= '$filter_starttime_con' ";
                }elseif($filterdata->mode == 4){
                    $sql.= " AND tpr.timecreated >= '$filter_starttime_con' ";
                }
                else{
                    $sql.= " AND ordrpmnt.availablefrom >= '$filter_starttime_con' "; 
                }    

            }
            if($filterdata->{'betweendaterangeto[enabled]'} == 1 ){

                $start_year = $filterdata->{'betweendaterangeto[year]'};
                $start_month = $filterdata->{'betweendaterangeto[month]'};
                $start_day = $filterdata->{'betweendaterangeto[day]'};
                $filter_endtime_con=mktime(23,59,59, $start_month, $start_day, $start_year);

                if ($filterdata->mode == 3) { 
                    $sql .= " AND lcl.timecreated <= '$filter_endtime_con' ";
                }elseif($filterdata->mode == 4){
                    $sql.=" AND tpr.timecreated <= '$filter_endtime_con' ";
                }
                else{
                    $sql.=" AND ordrpmnt.availablefrom <= '$filter_endtime_con' ";
                }

            }

        }   

        if(!empty($filterdata->trainingprograms)){

            if(!empty($filterdata->trainingprograms)){
                $trainingprogramslist = explode(',',$filterdata->trainingprograms);
            }

            list($relatedtrainingprogramslistsql, $relatedtrainingprogramslistparams) = $DB->get_in_or_equal($trainingprogramslist, SQL_PARAMS_NAMED, 'trainingprogramslist');
            $params = array_merge($params,$relatedtrainingprogramslistparams);


            if ($filterdata->mode == 3 || $filterdata->mode == 4) { 
                $sql .= " AND top.referenceid IN (SELECT tpofr.id
             FROM {tp_offerings} AS tpofr  
             JOIN {local_trainingprogram} AS tp ON tpofr.trainingid = tp.id
             WHERE tp.id  $relatedtrainingprogramslistsql AND top.category = 1) ";
            }else{
                $sql .= " AND EXISTS (SELECT tpofr.id 
                 FROM {tp_offerings} AS tpofr 
                 JOIN {local_trainingprogram} AS tp ON tpofr.trainingid=tp.id
                 WHERE tp.id $relatedtrainingprogramslistsql AND tpofr.id=ordrpmnt.fieldid) > 0 AND ordrpmnt.tablename='tp_offerings'";

            } 


        } 

           if(!empty($filterdata->prgevntexams && $filterdata->prgevntexams ==1)){
            
            $qry = "SELECT tp.id
             FROM {tp_offerings} AS tpofr  
             JOIN {local_trainingprogram} AS tp ON tpofr.trainingid=tp.id WHERE tp.published = 1 GROUP by tp.id ORDER BY tp.id DESC";         

            $trainingprogramslist = $DB->get_fieldset_sql($qry);


            list($relatedtrainingprogramslistsql, $relatedtrainingprogramslistparams) = $DB->get_in_or_equal($trainingprogramslist, SQL_PARAMS_NAMED, 'trainingprogramslist');
          
            $params = array_merge($params,$relatedtrainingprogramslistparams);
              
            if ($filterdata->mode == 3 || $filterdata->mode == 4) { 

                $sql .= " AND top.referenceid IN (SELECT tpofr.id
             FROM {tp_offerings} AS tpofr  
             JOIN {local_trainingprogram} AS tp ON tpofr.trainingid = tp.id
             WHERE tp.id  $relatedtrainingprogramslistsql AND top.category = 1) ";
            }else{
                
                $sql .= " AND EXISTS (SELECT tpofr.id 
                 FROM {tp_offerings} AS tpofr 
                 JOIN {local_trainingprogram} AS tp ON tpofr.trainingid=tp.id
                 WHERE tp.id $relatedtrainingprogramslistsql AND tpofr.id=ordrpmnt.fieldid) > 0 AND ordrpmnt.tablename='tp_offerings'";

            } 


        }  

        if(!empty($filterdata->events)){

            if(!empty($filterdata->events)){
                $eventslist = explode(',',$filterdata->events);
            }

            list($relatedeventslistsql, $relatedeventslistparams) = $DB->get_in_or_equal($eventslist, SQL_PARAMS_NAMED, 'eventslist');
            $params = array_merge($params,$relatedeventslistparams);

            if ($filterdata->mode == 3) {
                $sql .= " AND lcl.productid IN (SELECT id FROM {tool_products} as top WHERE top.category = 3 AND top.referenceid $relatedeventslistsql ) ";
            } elseif($filterdata->mode == 4){
                $sql .= " AND tpr.productid IN (SELECT id FROM {tool_products} as top WHERE top.category = 3 AND top.referenceid $relatedeventslistsql ) ";
            } else {
                $sql .= " AND (ordrpmnt.fieldid $relatedeventslistsql AND ordrpmnt.tablename='local_events') ";
            }

        } 

         if(!empty($filterdata->prgevntexams && $filterdata->prgevntexams ==2)){

            $qry = "SELECT evnt.id  
                     FROM {local_events} AS evnt GROUP by evnt.id ORDER BY evnt.id DESC";       
            $eventslist = $DB->get_fieldset_sql($qry);

            list($relatedeventslistsql, $relatedeventslistparams) = $DB->get_in_or_equal($eventslist, SQL_PARAMS_NAMED, 'eventslist');
            $params = array_merge($params,$relatedeventslistparams);

            if ($filterdata->mode == 3) {
                $sql .= " AND lcl.productid IN (SELECT id FROM {tool_products} as top WHERE top.category = 3 AND top.referenceid $relatedeventslistsql ) ";
            } elseif($filterdata->mode == 4){
                $sql .= " AND tpr.productid IN (SELECT id FROM {tool_products} as top WHERE top.category = 3 AND top.referenceid $relatedeventslistsql ) ";
            } else {
                $sql .= " AND (ordrpmnt.fieldid $relatedeventslistsql AND ordrpmnt.tablename='local_events') ";
            }

        }

        if(!empty($filterdata->offeringlocation)){

            if(!empty($filterdata->offeringlocation)){
                $trainingprogramslist = explode(',',$filterdata->offeringlocation);
            }

            list($relatedtrainingprogramslistsql, $relatedtrainingprogramslistparams) = $DB->get_in_or_equal($trainingprogramslist, SQL_PARAMS_NAMED, 'trainingprogramslist');

            $params = array_merge($params,$relatedtrainingprogramslistparams);

            
            if ($filterdata->mode == 3 || $filterdata->mode == 4) {

                $sql .= " AND top.referenceid IN (SELECT tpofr.id
                          FROM {tp_offerings} AS tpofr 
                          JOIN {local_trainingprogram} AS tp ON tpofr.trainingid=tp.id
                          WHERE tpofr.halllocation $relatedtrainingprogramslistsql) "; 
            } else {

                $sql .= " AND EXISTS (SELECT tpofr.id 
                          FROM {tp_offerings} AS tpofr 
                          JOIN {local_trainingprogram} AS tp ON tpofr.trainingid=tp.id
                          WHERE tpofr.halllocation $relatedtrainingprogramslistsql AND tpofr.id=ordrpmnt.fieldid) > 0 AND ordrpmnt.tablename='tp_offerings'";

            }              


        }

        if(!empty($filterdata->paymentmode)){

            if(!empty($filterdata->paymentmode)){
                $paymentmodelist = explode(',',$filterdata->paymentmode);
            }
    
            list($relatedpaymentmodelistsql, $relatedpaymentmodelistparams) = $DB->get_in_or_equal($paymentmodelist, SQL_PARAMS_NAMED, 'paymentmodelist');
            $params = array_merge($params,$relatedpaymentmodelistparams);

            
            if ($filterdata->mode == 3) {

                $sql .= " AND lcl.productid IN (SELECT productid FROM {tool_org_order_payments} AS toop WHERE toop.paymenttype $relatedpaymentmodelistsql ) ";
            }elseif($filterdata->mode == 4){
                $sql .= " AND tpr.productid IN (SELECT productid FROM {tool_org_order_payments} AS toop WHERE toop.paymenttype $relatedpaymentmodelistsql ) ";
            }
            else{

                $sql .= " AND ordrpmnt.paymenttype $relatedpaymentmodelistsql";    
            }

        }

        if(!empty($filterdata->exams)){
            if(!empty($filterdata->exams)){
                $examslist = explode(',',$filterdata->exams);
            }

            list($relatedexamslistsql, $relatedexamslistparams) = $DB->get_in_or_equal($examslist, SQL_PARAMS_NAMED, 'examslist');
            $params = array_merge($params,$relatedexamslistparams);
        

           
            if ($filterdata->mode == 3 || $filterdata->mode == 4) {

                $sql .= "  AND top.referenceid IN (SELECT hr.id 
                           FROM {local_exam_profiles}  hr
                           JOIN {local_exams} le ON le.id = hr.examid
                           WHERE le.id $relatedexamslistsql AND top.category = 2) "; 
            } else {

                $sql .= " AND EXISTS (SELECT hr.id 
                        FROM {local_exam_profiles}  hr
                        JOIN {local_exams} le ON le.id = hr.examid
                        WHERE le.id $relatedexamslistsql AND hr.id=ordrpmnt.fieldid) > 0 AND ordrpmnt.tablename='local_exam_profiles'"; 

            } 

        } 

           if(!empty($filterdata->prgevntexams && $filterdata->prgevntexams == 3)){
             $qry = "SELECT le.id  
                    FROM {local_exams} le WHERE le.status = 1 GROUP by le.id ORDER BY le.id DESC";
            $examslist = $DB->get_fieldset_sql($qry);

            list($relatedexamslistsql, $relatedexamslistparams) = $DB->get_in_or_equal($examslist, SQL_PARAMS_NAMED, 'examslist');
            $params = array_merge($params,$relatedexamslistparams);
        

           
            if ($filterdata->mode == 3 || $filterdata->mode == 4) {

                $sql .= "  AND top.referenceid IN (SELECT hr.id 
                           FROM {local_exam_profiles}  hr
                           JOIN {local_exams} le ON le.id = hr.examid
                           WHERE le.id $relatedexamslistsql AND top.category = 2) "; 
            } else {

                $sql .= " AND EXISTS (SELECT hr.id 
                        FROM {local_exam_profiles}  hr
                        JOIN {local_exams} le ON le.id = hr.examid
                        WHERE le.id $relatedexamslistsql AND hr.id=ordrpmnt.fieldid) > 0 AND ordrpmnt.tablename='local_exam_profiles'"; 

            } 

        } 

        if (!empty($filterdata->offeringstatus)){ 
            $trainings = explode(',',$filterdata->offeringstatus);
             if(!empty($trainings)){
                $trainingquery = array();
                foreach ($trainings as $training) {

                   if($training == 'completed') {

                     if ($filterdata->mode == 3 || $filterdata->mode == 4) {

                         $trainingquery[] = " top.referenceid IN (SELECT tpofr.id
                          FROM {tp_offerings} AS tpofr 
                          JOIN {local_trainingprogram} AS tp ON tpofr.trainingid=tp.id
                          JOIN {program_completions} AS pc ON pc.programid=tp.id
                         WHERE pc.completion_status =1) "; 

                     } else {

                         $trainingquery[] = " EXISTS (SELECT tpofr.id 
                         FROM {tp_offerings} AS tpofr 
                         JOIN {local_trainingprogram} AS tp ON tpofr.id=tp.trainingid
                         JOIN {program_completions} AS pc ON pc.programid=tp.id
                         WHERE pc.completion_status =1 AND tpofr.id=ordrpmnt.fieldid) > 0 "; 
                     }

                   } elseif($training == 'financially_closed') {

                    $expired =  (new trainingprogram())->from_unixtime_for_expired_entities('tpofr.enddate');

                     if ($filterdata->mode == 3 || $filterdata->mode == 4) {

                         $trainingquery[] = "  top.referenceid IN (SELECT tpofr.id
                         FROM {tp_offerings} AS tpofr 
                         JOIN {local_trainingprogram} AS tp ON tpofr.trainingid=tp.id
                         WHERE $expired ) ";

                     }else{

                          $trainingquery[] = "  EXISTS (SELECT tpofr.id 
                         FROM {tp_offerings} AS tpofr 
                         JOIN {local_trainingprogram} AS tp ON tpofr.trainingid=tp.id
                         WHERE $expired AND tpofr.id=ordrpmnt.fieldid) > 0 ";
                     }
                     
                    }

                }
                $trainingqueeryparams =implode('OR',$trainingquery);

                 if ($filterdata->mode == 3 || $filterdata->mode == 4) {

                    $sql .= ' AND ('.$trainingqueeryparams.') ';
                 }else{
                    $sql .= ' AND ('.$trainingqueeryparams.') AND ordrpmnt.tablename="tp_offerings" ';
                 }
                
            }
        } 


        if(!empty($filterdata->offeringtype)){

            if(!empty($filterdata->offeringtype)){
                $trainingprogramslist = explode(',',$filterdata->offeringtype);
            }

            list($relatedtrainingprogramslistsql, $relatedtrainingprogramslistparams) = $DB->get_in_or_equal($trainingprogramslist, SQL_PARAMS_NAMED, 'trainingprogramslist');
            $params = array_merge($params,$relatedtrainingprogramslistparams);



            if ($filterdata->mode == 3 || $filterdata->mode == 4) {

                $sql .= " AND top.referenceid IN (SELECT tpofr.id
                          FROM {tp_offerings} AS tpofr 
                          JOIN {local_trainingprogram} AS tp ON tpofr.trainingid=tp.id
                          WHERE tpofr.trainingmethod $relatedtrainingprogramslistsql )";
            } else {


                $sql .= " AND EXISTS (SELECT tpofr.id 
                          FROM {tp_offerings} AS tpofr 
                          JOIN {local_trainingprogram} AS tp ON tpofr.trainingid=tp.id
                          WHERE tpofr.trainingmethod $relatedtrainingprogramslistsql AND tpofr.id=ordrpmnt.fieldid) > 0 AND ordrpmnt.tablename='tp_offerings'";

            }


        }  


        if(!empty($filterdata->organization)){

            if(!empty($filterdata->organization)){
                $organizationlist = explode(',',$filterdata->organization);
            }

            list($relatedorganizationlistsql, $relatedorganizationlistparams) = $DB->get_in_or_equal($organizationlist, SQL_PARAMS_NAMED, 'organizationlist');
            $params = array_merge($params,$relatedorganizationlistparams);


            if ($filterdata->mode == 3) {

                $sql .= " AND lcl.userid IN (SELECT lc.userid 
                          FROM {local_users} lc 
                          WHERE lc.organization $relatedorganizationlistsql )";
            }elseif($filterdata->mode == 4){
                $sql .= " AND tpr.userid IN (SELECT lc.userid 
                          FROM {local_users} lc 
                          WHERE lc.organization $relatedorganizationlistsql )";
            } else { 
            
                $sql .= " AND EXISTS (SELECT lc.userid 
                          FROM {local_users} lc 
                          JOIN {local_organization} org ON org.id = lc.organization
                          WHERE lc.organization $relatedorganizationlistsql AND lc.userid=ordrpmnt.userid) > 0";

            }    

        }
       
        if(!empty($filterdata->useridnumber)){
            $userslist = explode(',',$filterdata->useridnumber);
            list($relateduserslistsql, $relateduserslistparams) = $DB->get_in_or_equal($userslist, SQL_PARAMS_NAMED, 'userslist');
            $params = array_merge($params,$relateduserslistparams);
            $sql .= " AND EXISTS (SELECT lc.userid 
                FROM {local_users} lc 
                WHERE lc.id $relateduserslistsql AND lc.userid=ordrpmnt.userid) > 0";
        }
         
        return ['sql' => $sql,'params' => $params];
    }
    public function invoice_record_for_0_cancellation($data) {
        global $DB,$CFG, $USER;
        (new \tool_product\telr)->void_invoice($data->invoicenumber);
        $organizationid = (new \tool_product\product)->get_user_organization($USER->id);
        $inv_num = $data->userid.time();
        $record = new stdClass();
        $record->productid =$data->productid;
        $record->userid = $data->enrolleduserid;
        $record->realuser = ($USER->realuser) ? $USER->realuser :0;
        $record->telrid = 0;
        $record->invoice_number = $inv_num;
        $record->seats =0;
        $record->type = 'cancel';
        $record->amount =0;
        $record->payableamount =0;
        $record->status =1;
        $record->payment_status =0;
        $record->timecreated = time();
        $record->usercreated = $USER->id;
        $record->organization = $organizationid?$organizationid:0;
        $insertid = $DB->insert_record('tool_product_sadad_invoice', $record);

        $prod_data = $DB->get_record('tool_products', ['id' => $data->productid]);
        switch($prod_data->category){
            case self::TRAINING_PROGRAM :
                $tablename = 'tp_offerings';
            break;
            case self::EXAMS;
                $tablename = 'local_exam_profiles';
            break;
            case self::EVENTS:
                $tablename = 'local_events';
            break;
            case self::GRIEVANCE:
                $tablename = 'local_exam_grievance';
            break;
            case self::LEARNINGTRACKS:
                $tablename = 'local_learningtracks';
            break;
            case self::EXAMATTEMPT:
                $tablename = 'local_exam_attempts';
            break;
        }

        $record->tablename = $tablename;
        $record->fieldname = 'id';
        $record->fieldid = $prod_data->referenceid;
        $record->orguserid = $USER->id;
        $record->realuser=($USER->realuser) ? $USER->realuser :0;
        $record->paymenttype = 'postpaid';
        $record->paymenton = time();
        $record->approvalon = time();
        $record->purchasedseats = 1;
        $record->transactionid = $inv_num;
        $record->usercreated = $USER->id;
        $record->timecreated = time();
        $record->usermodified = $USER->id;
        $record->timemodified = time();
        $record->originalprice = 0;
        $record->orderstatus = 1;
        $record->organization = $organizationid?$organizationid:0;

        $insertid = $DB->insert_record('tool_org_order_payments', $record);

        // $orderpaymentid = $DB->get_field('tool_org_order_payments', 'id', ['transactionid'=>$data->invoice_number, 'productid'=>$record->productid]);
        // if ($orderpaymentid) {
        //     $DB->update_record('tool_org_order_payments', ['id' => $orderpaymentid, 'orderstatus' => 1]);
        // }

    }
    public function seats_availability($product) {
        global $DB;
        $productdata = unserialize(base64_decode($product['products']));
        //print_r($productdata); exit;
        $noseats = [];
        $productname = [];
        $products = [];
        $count = 0;
        $currentlang= current_language();
        foreach($productdata['items']  as $product){
            if($product['category'] == self::EXAMS) {
                $examseats =  $this->check_exam_seatingcapacity($product['referenceid'], $product['hallscheduleid']);
                $examid = $DB->get_field('local_exam_profiles','examid',['id' => $product['referenceid']]);
                if($examseats) {
                    $products[$count]['productid'] = $examid;
                    $products[$count]['category'] = $product['category'];
                    $noseats[] = true;
                    $productname[] = $product['name'];
                }
            } else if($product['category'] == self::EXAMATTEMPT ) {
                $examseats =  $this->check_exam_seatingcapacity($product['referenceid'], $product['hallscheduleid']);
                $examid = $DB->get_field('local_exam_attempts','examid',['id' => $product['referenceid']]);
                if($examseats) {
                    $products[$count]['productid'] = $examid;//$product['product_id'];
                    $products[$count]['category'] = $product['category'];
                    $noseats[] = true;
                    $productname[] = $product['name'];
                }
            } else if($product['category'] == self::TRAINING_PROGRAM) {
                $programseats = $this->check_program_seatingcapacity($product['referenceid']);
                $programid = $DB->get_field('tp_offerings','trainingid',['id' => $product['referenceid']]);
                if($programseats) {
                    $products[$count]['productid'] = $programid;
                    $products[$count]['category'] = $product['category'];

                    $noseats[] = true;
                    $productname[] = $product['name'];
                }
            } else if($product['category'] == self::EVENTS) {
                $eventseats = $this->check_event_seatingcapacity($product['referenceid']);
                if($eventseats) {
                    $products[$count]['productid'] =  $product['referenceid'];
                    $products[$count]['category'] = $product['category'];
                    $noseats[] = true;
                    $productname[] = $product['name'];
                }  
            }
            $count++;
        }
        return ['noseats' => $noseats?implode(', ',$noseats):'',
                'productname' => $productname?implode(', ',$productname):'',
                'returnurl' => '',
                'products' => $products,
            ];
    }

    public function check_event_seatingcapacity($referenceid) {
            global $DB;
            $eventmethod = $DB->get_field('local_events','method',['id' => $referenceid]);
            if($eventmethod == 0) {
            $hall_sql = " SELECT SUM(hr.seats) FROM {hall} h JOIN {local_events} e ON concat(',', e.halladdress, ',')
            LIKE concat('%,',h.id,',%')
            JOIN {hall_reservations} hr ON hr.hallid = h.id
            WHERE e.id = $referenceid AND hr.type='event' AND hr.typeid = e.id" ;
            $hall = $DB->get_field_sql($hall_sql);
                    $enrolled_sql= " SELECT count(userid) AS total
                    FROM {local_event_attendees} AS ea WHERE ea.enrolstatus = 1 AND ea.eventid = $referenceid";
                    $seats =  $DB->get_field_sql($enrolled_sql);
                    return ($hall <= $seats)?true:false;
                }
    }

    public function check_program_seatingcapacity($referenceid) {
        global $DB;
        $offering_seats = $DB->get_field('tp_offerings','availableseats',['id' => $referenceid]);           
        $enrolled_sql= " SELECT count(userid) AS total
        FROM {program_enrollments} AS pe WHERE  pe.enrolstatus = 1 AND pe.offeringid = $referenceid";
        $seats =  $DB->get_field_sql($enrolled_sql);
        return ($offering_seats <= $seats)?true:false;
    }

    public function check_exam_seatingcapacity($referenceid, $hallscheduleid) {
        global $DB;
        $hallschedule_capacity = $DB->get_field('hallschedule','seatingcapacity',['id' => $hallscheduleid]);
        $exam_profile_schedule = $DB->count_records('local_exam_userhallschedules',['profileid' => $referenceid]);

        $exam_hallschedule = $DB->count_records('local_exam_userhallschedules',['hallscheduleid' => $hallscheduleid]);

        $profile_seating_capacity = $DB->get_field('local_exam_profiles','seatingcapacity',['id' => $referenceid]);
        if($profile_seating_capacity > 0 ) {
            if($profile_seating_capacity > $exam_profile_schedule) {
               
                $profile['seatingcapacity'] = true;
            } else {
                $profile['seatingcapacity'] = false;
            }
        } else {
            $profile['seatingcapacity'] = true;
        }
        if($hallschedule_capacity > 0 ) {
           
            if($hallschedule_capacity > $exam_hallschedule) {
                $hallschedule['seatingcapacity'] = true;
            } else {
                $hallschedule['seatingcapacity'] = false;
            }
        } else {
            $hall_sql = " SELECT h.seatingcapacity FROM {hall} h JOIN {hallschedule} hs ON hs.hallid = h.id 
            WHERE hs.id = $hallscheduleid";
            $hall = $DB->get_field_sql($hall_sql);
            if($hall > $exam_hallschedule) {
                $hallschedule['seatingcapacity'] = true;
            } else {
                $hallschedule['seatingcapacity'] = false;
            }
        }
        return (!$profile['seatingcapacity'] || !$hallschedule['seatingcapacity'])? true:false;
    }

    
    public function insert_replacement_record_in_org_order_payments($invoicedata,$productdata) {
        global $DB, $USER,$CFG;
        $record = new stdClass();
        $tablename = ($productdata['entitytype'] == 'exams')? 'local_exam_profiles' : (($productdata['entitytype'] == 'trainingprogram') ?'tp_offerings' : 'local_events');
        $record->productid=$productdata['productid'];
        $record->tablename =$tablename;
        $record->fieldname ='id';
        $record->fieldid =(int)$DB->get_field('tool_products','referenceid',['id'=>$productdata['productid']]);
        $record->orguserid =$productdata['userid'];
        $record->realuser =($USER->realuser) ? $USER->realuser :0;
        $record->purchasedseats =1;
        $record->usercreated =$USER->id;
        $record->timecreated =time();
        $record->organization =0;
        $record->paymenttype ='postpaid';
        $record->paymenton =time();
        $record->amount =$invoicedata->amount;
        $record->approvalon =time();
        $record->transactionid =$invoicedata->invoice_number;
        $record->originalprice =$invoicedata->amount;
        $record->taxes =0;
        $record->payableamount =$invoicedata->amount;
        try{
           $DB->insert_record('tool_org_order_payments', $record); 

        }catch(dml_exception $e){
            print_error($e);
        }

    }
}
