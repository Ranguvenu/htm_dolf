<?php
/**
 * 
 *
 * @package    tool_product
 * @copyright  2022 Naveen kumar <naveen@eabyas.com>
 */
namespace tool_product;
use context_system;
use local_events\events as events;
use local_exams\local\exams as exams;
use local_trainingprogram\local\trainingprogram as trainingprogram;
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

    public $categories;

    private $tax_slab;

    public function __construct() {
        $this->categories = [self::TRAINING_PROGRAM => get_string('pluginname', 'local_trainingprogram'),
                             self::EXAMS => get_string('pluginname', 'local_exams'),
                             self::EVENTS => get_string('pluginname', 'local_events'),
                             self::GRIEVANCE => get_string('pluginname', 'local_events'),
                             self::LEARNINGTRACKS => get_string('pluginname', 'local_learningtracks')];
        $this->tax_slab = 0;

        $this->errors = [
            self::TRAINING_PROGRAM => get_string('noprogramvariation', 'tool_product'),
            self::EXAMS => get_string('noexamvariation', 'tool_product'),
            self::EVENTS => get_string('noeventvariation', 'tool_product'),
            self::GRIEVANCE => get_string('nogrievance', 'tool_product'),
            self::LEARNINGTRACKS => get_string('nolearningtracks', 'tool_product')
        ];

        $this->supported_payment_methods = array(
            array(
                'slug'          =>  'telr',
                'capabilities'  =>  ''
            ),
            array(
                'slug'          =>  'prepaid',
                'capabilities'  =>  'local/organization:manage_organizationofficial'
            ),
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
                $isenrolled = (new \local_trainingprogram\local\trainingprogram)->is_enrolled( $programid, $USER->id);
            break;
            case self::EXAMS;
                $isenrolled = (new \local_exams\local\exams)->is_enrolled( $product->referenceid, $USER->id);
            break;
            case self::EVENTS:
                $isenrolled = (new \local_events\events)->is_enrolled($product->referenceid, $USER->id);
            break;
            case self::GRIEVANCE:
                $isenrolled = (new \local_exams\local\exams)->is_enrolled_grievance( $product->referenceid, $USER->id);
            break;
        }

        return $isenrolled;
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

                $training = $DB->get_record_sql("SELECT tp.id,tp.name,tp.namearabic,tp.sectors,tpo.trainingid,tp.discount,tpo.startdate,tp.duration,tp.description,tp.tax_free FROM {local_trainingprogram} AS tp JOIN {tp_offerings} AS tpo ON tpo.trainingid = tp.id WHERE tpo.id = $data->referenceid");

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

                $lecturessql = "SELECT COUNT(id) FROM {offering_sessions} WHERE programid = $training->trainingid";
                $data->lectures = $DB->count_records_sql($lecturessql);
                $data->programduration =round($training->duration / 86400); 


                $data->early_registration_discount =0;

                $data->early_registration_discount_amount =0;


                $data->groups_organization_discount =0;

                $data->groups_organization_discount_amount =0;


                if($training->discount == 1) {

                    $offeringstartdate = date('Y-m-d',$training->startdate);
                    $currdate=date('Y-m-d'); 

                    $days = $this->dateDiffInDays($offeringstartdate, $currdate);


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
                

            }elseif ($data->category == 2){
                $examid = $DB->get_field('hall_reservations', 'typeid', ['type' => 'exam', 'id' => $data->referenceid]);

                $exam = $DB->get_record_sql("SELECT le.id,le.exam,le.examnamearabic,le.programdescription,le.tax_free FROM {local_exams} AS le WHERE le.id = $examid ");

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

               $data->viewcourseurl = $CFG->wwwroot.'/local/exams/exams_qualification_details.php?id='.$examid;
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

                if($data->tax_free == 1) {

                    $data->tax_percentage = get_config('tool_product','tax_percentage');
                    $data->tax_amount  = round(($data->price * (($data->tax_percentage/100))),2);

                } else {

                    $data->tax_percentage = 0;
                    $data->tax_amount  = 0;

                }
               $data->viewcourseurl = '';//$CFG->wwwroot.'/local/events/alleventsview.php?id='.$eventid;
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

    public function get_product_attributes( $reference, $category, $label, $hasvariations, $variation = 0, $quantity = 1, $checkout="false", $grouped=0 ){

        global $DB;
         
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

    public function get_product_variations( $reference, $variation, $category){

        if($variation){
            $product = $this->get_product_from_reference($variation, $category);
        }

        return array(
            'product'       =>  $reference,
            'variation'     =>  $product ? $product->id : 0,
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

        $product = $DB->get_record('tool_products', array('referenceid' => $reference_id, 'category' => $category), '*', IGNORE_MULTIPLE);
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
        foreach($products as $key => $value){
            if(!$value['is_enrolled']){
                $total_price = $total_price+($value['actual_price']*$value['quantity']);
                $discount_price = $discount_price+($value['sale_price']*$value['quantity']);
            }


            $items[] = array(
                'product_id'=>  $value['id'],
                'name'      =>  $value['name'],
                'quantity'  =>  $value['quantity'],
                'is_enrolled' => $value['is_enrolled'],
                'total'     =>  round(($value['actual_price']*$value['quantity']),2),

                'category'  =>  $value['category'],
                'grouped'  => $value['grouped'],
                'early_registration_discount'=>  $value['early_registration_discount'],
                'early_registration_discount_amount'      =>  $value['early_registration_discount_amount'],
                'discount_type'  =>  $value['discount_type'],

                'couponactionview' => ($value['roles']) ? ($value['discount_type'] == 'coupon' ? true: false) : false,

                'couponid' => $value['couponid'],

                'couponcode' => $value['couponcode'],

                'couponcode_discount_amount' => round($value['couponcode_discount_amount'],2),

               'groups_organization_discount' => $value['groups_organization_discount'],
               'groups_organization_discount_amount' => $value['groups_organization_discount_amount'],

            );

            $this->tax_slab = $value['tax_percentage'];
            $taxes = $this->caluculate_taxes($discount_price, $this->tax_slab);
        } 
        $total_discount = $total_price - $discount_price;
        $taxes =  $taxes;
        $total = $discount_price + $taxes;

        return array(
            
            'items'             =>  $items,
            'total_price'       =>  round($total_price,2),
            'discount_price'    =>  round($discount_price,2),
            'total_discount'    =>  round($total_discount,2),
            'total'             =>  round($total,2),
            'taxes'             =>  round($taxes,2),
            'taxdisplay'   =>  $taxes > 0 ? true : false,
            'tax_slab'          =>  $this->tax_slab,
            'total_purchases'   =>  $total_purchases
           
        );
    }

    public function format_product($product){

        global $DB, $USER, $OUTPUT, $CFG,$PAGE;

        $sitecontext = \context_system::instance();

        $PAGE->set_context($sitecontext);


        $product->discount=$product->discount ? $product->discount : 0;

        if($product->discount_type == 'early_registration' && $product->early_registration_discount > 0) {

            $total_discount = $product->discount + $product->early_registration_discount;

            if($total_discount){
               
                $product->discount = $total_discount;

            } else {

               $product->discount = $product->discount;
            }

            
        }
        if($product->discount_type == 'groups' && $product->groups_organization_discount > 0) {

            $total_discount = $product->discount + $product->groups_organization_discount;

            if($total_discount){
               
                $product->discount = $total_discount;

            } else {

               $product->discount = $product->discount;
            }

            
        }

        $discount = $product->discount; 

        return array(
            'id'            =>  $product->id,
            'name'          =>  $product->name,
            'description'   =>  format_text($product->description),
            'category'      =>  $product->category,
            'thumbnail'     =>  $this->get_product_thumbnail($product->referenceid, $product->category),
            'actual_price'  =>  $product->price,
            'sale_price'    =>  $this->caluculate_price_after_discount($product->price, $discount),
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



            if($data->id > 0){

                
                $data->paymentapprovalstatus=1;
                $data->approvaluserid=$USER->id;
                $data->approvalon=time();

                $data->timemodified=time();
                $data->usermodified=$USER->id;

                $id=$DB->update_record('tool_org_order_payments', $data);

                $paymentorder=clone $data;

                $touser=$DB->get_record('user',array('id'=>$data->orguserid));

                $paymentorder->payment_userfullname=$touser->firstname.' '.$touser->lastname;
                $paymentorder->quantity=$data->purchasedseats;
                $paymentorder->payment_details=$this->get_payment_details($paymentorder);
                $paymentorder->invoiceno=$data->id;

                (new \tool_product\notification())->product_notification('payment_completion', $touser,$fromuser=get_admin(), $paymentorder,$waitinglistid=0);

                self::insert_update_org_order_seats($data);


            }else{

                $data->timecreated=time();
                $data->usercreated=$USER->id;
                $data->approvalon=(!isset($data->approvalon)) ? 0 : $data->approvalon;

                $id=$DB->insert_record('tool_org_order_payments', $data);


                if(!empty($product)){

                    $product->orgorderid=$id;

                    $product->userid=$data->orguserid;

                    $product->productid=$data->productid;

                    self::insert_update_order_discount_usage($product);
                }

                self::insert_update_org_order_seats($data);

                if($data->paymenttype == 'prepaid' || $data->paymenttype == 'telr'){

                    $paymentorder=clone $data;

                    $touser=$DB->get_record('user',array('id'=>$data->orguserid));

                    $paymentorder->payment_userfullname=$touser->firstname.' '.$touser->lastname;

                    $paymentorder->payment_details=$this->get_payment_details($paymentorder);

                    $paymentorder->order=$id;

                    (new \tool_product\notification())->product_notification('pre_payment', $touser,$fromuser=get_admin(), $paymentorder,$waitinglistid=0);


                    $paymentorder->invoiceno=$id;

                    (new \tool_product\notification())->product_notification('payment_completion', $touser,$fromuser=get_admin(), $paymentorder,$waitinglistid=0);




                    if($data->tablename == 'hall_reservations') {
                        
                        $examid = $DB->get_field('hall_reservations', 'typeid', ['id' => $data->fieldid]);
                        $courseid = $DB->get_field('local_exams', 'courseid', ['id' => $examid]);
                        $timestart = time();
                        $timeend = 0;
                        $manual = enrol_get_plugin('manual');
                        $roleid=$DB->get_field('role','id',array('shortname'=>'teacher'));
                        $instance = $DB->get_record('enrol', array('courseid' => $courseid, 'enrol' => 'manual'), '*', MUST_EXIST);
                        $manual->enrol_user($instance, $data->orguserid, $roleid, $timestart, $timeend);                        
                    }
                    if($data->tablename == 'tp_offerings') {
                        
                        $programid = $DB->get_field('tp_offerings', 'trainingid', ['id' => $data->fieldid]);
                        $courseid = $DB->get_field('local_trainingprogram', 'courseid', ['id' => $programid]);
                        $timestart = time();
                        $timeend = 0;
                        $manual = enrol_get_plugin('manual');
                        $roleid=$DB->get_field('role','id',array('shortname'=>'teacher'));
                        $instance = $DB->get_record('enrol', array('courseid' => $courseid, 'enrol' => 'manual'), '*', MUST_EXIST);
                        $manual->enrol_user($instance, $data->orguserid, $roleid, $timestart, $timeend);  
                        $group = $DB->get_record_sql("SELECT grop.id FROM {groups} as grop JOIN {tp_offerings} as tpo ON tpo.code = grop.idnumber WHERE tpo.id = $data->fieldid");
                        $groupid = (int) $group->id;
                        if ($groupid) {
                            groups_add_member($groupid,$data->orguserid,null,0);
                        }                      
                    }

                    
                }else{

                    $paymentorder=clone $data;

                    $touser=$DB->get_record('user',array('id'=>$data->orguserid));

                    $paymentorder->payment_userfullname=$touser->firstname.' '.$touser->lastname;

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

                $id=$DB->insert_record('tool_order_discount_usage', $data);
            }


        } catch (dml_exception $e) {
            print_error($e);

        }
        return true;
    }
    public function insert_update_org_order_seats($data) {
        global $DB, $USER;
        
        try {

            $purchasedseats=$data->purchasedseats;

            $getdata=$DB->get_record('tool_org_order_seats',  array('tablename'=>$data->tablename,'fieldname'=>$data->fieldname,'fieldid'=>$data->fieldid,'orguserid'=>$data->orguserid),'id,purchasedseats,availableseats,approvalseats');

            if($getdata){


                $data->id=$getdata->id;
                $data->purchasedseats=$getdata->purchasedseats+$purchasedseats;

                if($data->paymenttype == 'prepaid' || $data->paymenttype == 'telr'){

                    $data->availableseats=$getdata->availableseats+$purchasedseats;

                    $data->approvalseats=$getdata->approvalseats+$purchasedseats;
                
                }
                $data->timemodified=time();
                $data->usermodified=$USER->id;

                $id=$DB->update_record('tool_org_order_seats', $data);
      

                if($data->paymenttype == 'postpaid'){

                    $getdata=$DB->get_record('tool_order_approval_seats',  array('tablename'=>$data->tablename,'fieldname'=>$data->fieldname,'fieldid'=>$data->fieldid,'orguserid'=>$data->orguserid),'id,purchasedseats,approvalseats');

                    $data->id=$getdata->id;

                    $data->purchasedseats=$purchasedseats;

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

        
            $getdata=$DB->get_record('tool_order_approval_seats',  array('id'=>$data->id),'id,purchasedseats,approvalseats,tablename,fieldname,fieldid,orguserid');

            if($getdata){

                $data->id=$getdata->id;

                if(isset($data->approvalseats)){

                    $approvalseats=$data->approvalseats;

                    $data->approvalseats=$getdata->approvalseats+$approvalseats;

                }
                if(isset($data->purchasedseats)){

                    $purchasedseats=$data->purchasedseats;

                    $data->purchasedseats=$getdata->purchasedseats+$purchasedseats;

                }
                $data->timemodified=time();
                $data->usermodified=$USER->id;

                $id=$DB->update_record('tool_order_approval_seats', $data);

                if(isset($data->approvalseats)){


                    $getdatast=$DB->get_record('tool_org_order_seats',  array('tablename'=>$getdata->tablename,'fieldname'=>$getdata->fieldname,'fieldid'=>$getdata->fieldid,'orguserid'=>$getdata->orguserid),'id,purchasedseats,availableseats,approvalseats');

                    $data->availableseats=$getdatast->availableseats+$approvalseats;

                    $data->approvalseats=$getdatast->approvalseats+$approvalseats;

                    $data->id=$getdatast->id;
                    $data->timemodified=time();
                    $data->usermodified=$USER->id;

                    $id=$DB->update_record('tool_org_order_seats', $data);

                    if($getdata->tablename == 'hall_reservations') {
                        
                        $examid = $DB->get_field('hall_reservations', 'typeid', ['id' => $getdata->fieldid]);
                        $courseid = $DB->get_field('local_exams', 'courseid', ['id' => $examid]);
                        $timestart = time();
                        $timeend = 0;
                        $manual = enrol_get_plugin('manual');
                        $roleid=$DB->get_field('role','id',array('shortname'=>'teacher'));
                        $instance = $DB->get_record('enrol', array('courseid' => $courseid, 'enrol' => 'manual'), '*', MUST_EXIST);
                        $manual->enrol_user($instance, $getdata->orguserid, $roleid, $timestart, $timeend);                        
                    }
                    if($getdata->tablename == 'tp_offerings') {
                        
                        $programid = $DB->get_field('tp_offerings', 'trainingid', ['id' => $getdata->fieldid]);
                        $courseid = $DB->get_field('local_trainingprogram', 'courseid', ['id' => $programid]);
                        $timestart = time();
                        $timeend = 0;
                        $manual = enrol_get_plugin('manual');
                        $roleid=$DB->get_field('role','id',array('shortname'=>'teacher'));
                        $instance = $DB->get_record('enrol', array('courseid' => $courseid, 'enrol' => 'manual'), '*', MUST_EXIST);
                        $manual->enrol_user($instance, $getdata->orguserid, $roleid, $timestart, $timeend);  

                        $group = $DB->get_record_sql("SELECT grop.id FROM {groups} as grop JOIN {tp_offerings} as tpo ON tpo.code = grop.idnumber WHERE tpo.id = $getdata->fieldid");
                        $groupid = (int) $group->id;
                        if ($groupid) {
                            groups_add_member($groupid,$getdata->orguserid,null,0);
                        }                      
                    }
                }

            }else{

                $data->timecreated=time();
                $data->usercreated=$USER->id;
                $data->approvalseats=0;

                $id=$DB->insert_record('tool_order_approval_seats', $data);

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


            $getdata=$DB->get_field_sql("SELECT availableseats as totalavailableseats FROM {tool_org_order_seats} WHERE tablename =:tablename AND fieldname =:fieldname AND fieldid =:fieldid AND orguserid =:orguserid", array('tablename'=>$tablename,'fieldname'=>$fieldname,'fieldid'=>$fieldid,'orguserid'=>$USER->id));

        
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

            $getdata=$DB->get_field_sql("SELECT approvalseats as totalapprovalseats FROM {tool_org_order_seats} WHERE tablename =:tablename AND fieldname =:fieldname AND fieldid =:fieldid AND orguserid =:orguserid", array('tablename'=>$tablename,'fieldname'=>$fieldname,'fieldid'=>$fieldid,'orguserid'=>$userid));

        
            return $getdata ? $getdata : 0;

        }else{

            $getdata=$DB->get_field_sql("SELECT sum(approvalseats) as totalapprovalseats FROM {tool_org_order_seats} WHERE tablename =:tablename AND fieldname =:fieldname AND fieldid =:fieldid", array('tablename'=>$tablename,'fieldname'=>$fieldname,'fieldid'=>$fieldid));

        
            return $getdata ? $getdata : 0;

        }
    }    
    public function purchasedseats_check($tablename, $fieldname, $fieldid){

        global $DB, $USER;


        $sitecontext = \context_system::instance();

        if((!is_siteadmin()) && (has_capability('local/organization:manage_organizationofficial', $sitecontext))) {


            $getdata=$DB->get_field_sql("SELECT purchasedseats as totalpurchasedseats FROM {tool_org_order_seats} WHERE tablename =:tablename AND fieldname =:fieldname AND fieldid =:fieldid AND orguserid =:orguserid", array('tablename'=>$tablename,'fieldname'=>$fieldname,'fieldid'=>$fieldid,'orguserid'=>$USER->id));

        
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

                $data->id=$getdata->id;
                $data->availableseats = $getdata->availableseats+($redeemseats);
                $data->timemodified=time();
                $data->usermodified=$USER->id;

                $id=$DB->update_record('tool_org_order_seats', $data);
 
            }
        } catch (dml_exception $e) {
            print_error($e);

        }
        return true;
    }
    public function orders_prepaid_paymentsupdate($data) {
        global $DB, $USER;

        try {

            $userwallet=(new \local_userapproval\action\manageuser)::get_user_wallet($data->orguserid);

            if($userwallet->id > 0 && $userwallet->wallet >= $data->amount){

                $tabledata=new \stdClass();

                $tabledata->wallet =$userwallet->wallet-$data->amount;

                $tabledata->id=$userwallet->id;

                $tabledata->timemodified=time();
                $tabledata->usermodified=$USER->id;

                $DB->update_record('local_orgofficial_wallet', $tabledata);

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

        $totalcostql = "SELECT SUM(ordrpmnt.amount) ";


        $fromsql = " SELECT ordrpmnt.id,ordrpmnt.trainingname,ordrpmnt.organization,ordrpmnt.tablename,ordrpmnt.fieldname,ordrpmnt.fieldid,ordrpmnt.trainingid,ordrpmnt.availablefrom,ordrpmnt.purchasedseats,ordrpmnt.orguserid,ordrpmnt.availableto  ";

        $fromsql.=',ordrpmnt.amount,ordrpmnt.amountrecived,ordrpmnt.transactionid,ordrpmnt.checkid,ordrpmnt.transactionnote,ordrpmnt.paymenttype';


        $sql = " FROM ( ";

        $stable->selectparams=',tppmnt.amount,tppmnt.amountrecived,tppmnt.transactionid,tppmnt.checkid,tppmnt.transactionnote,tppmnt.paymenttype';


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

            $sql .= " AND (ordrpmnt.englishname LIKE :search1 OR ordrpmnt.arabicname LIKE :search2 OR ordrpmnt.amount LIKE :search3) ";

            $params['search1'] = '%'.trim($filterdata->search_query).'%';
            $params['search2'] = '%'.trim($filterdata->search_query).'%';
            $params['search3'] = '%'.trim($filterdata->search_query).'%';
           
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
        

            $sql .= " AND EXISTS (SELECT hr.id 
             FROM {hall_reservations}  hr
             JOIN {local_exams} le ON le.id = hr.typeid
             WHERE le.id $relatedexamslistsql AND hr.id=ordrpmnt.fieldid) > 0 AND ordrpmnt.tablename='hall_reservations'";

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


        $params          = array();
        $orders      = array();
        $orderscount = 0;
 
        $countsql = "SELECT COUNT(*) ";


        $fromsql = " SELECT ordrpmnt.id,ordrpmnt.trainingname,ordrpmnt.organization,ordrpmnt.tablename,ordrpmnt.fieldname,ordrpmnt.fieldid,ordrpmnt.trainingid,ordrpmnt.availablefrom,ordrpmnt.purchasedseats,ordrpmnt.orguserid ";

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

            $sql .= " AND (ordrpmnt.englishname LIKE :search1 OR ordrpmnt.arabicname LIKE :search2) ";

            $params['search1'] = '%'.trim($filterdata->search_query).'%';
            $params['search2'] = '%'.trim($filterdata->search_query).'%';
           
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
            

            $sql .= " AND EXISTS (SELECT hr.id 
             FROM {hall_reservations}  hr
             JOIN {local_exams} le ON le.id = hr.typeid
             WHERE le.id $relatedexamslistsql AND hr.id=ordrpmnt.fieldid) > 0 AND ordrpmnt.tablename='hall_reservations'";

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
 
        $countsql = "SELECT COUNT(*) ";


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
      
        $sql.= " ) AS ordrpmnt WHERE ordrpmnt.id > 0 ";


        if (!empty($filterdata->search_query)) {

            $sql .= " AND (ordrpmnt.englishname LIKE :search1 OR ordrpmnt.arabicname LIKE :search2) ";

            $params['search1'] = '%'.trim($filterdata->search_query).'%';
            $params['search2'] = '%'.trim($filterdata->search_query).'%';
           
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
            
            $sql .= " AND EXISTS (SELECT hr.id 
             FROM {hall_reservations}  hr
             JOIN {local_exams} le ON le.id = hr.typeid
             WHERE le.id $relatedexamslistsql AND hr.id=ordrpmnt.fieldid) > 0 AND ordrpmnt.tablename='hall_reservations'";

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

    public function seatexceed_check($list) {

        global $USER,$DB;

        switch ($list->tablename) {


            case 'tp_offerings':

                    $availableseats=$DB->get_field('tp_offerings','availableseats',array('id'=>$list->fieldid));
                
                break;

            case 'hall_reservations':

                $availableseats=$DB->get_field('local_exams','seatingcapacity',array('id'=>$list->fieldid));
                break;

            case 'local_events':

                $availableseats=0;

                break;
            default:
                $availableseats=0;
        }

        return $availableseats;

    }
    public function get_payment_details($data){

        global $DB, $USER, $OUTPUT, $CFG,$PAGE;

        $sitecontext = \context_system::instance();

        $PAGE->set_context($sitecontext);

        $renderer = $PAGE->get_renderer('tool_product');

        $category=0;

        switch ($data->tablename) {

            case 'tp_offerings':

            $category=1;

            break;

            case 'hall_reservations':

            $category=2;

            break;

            case 'local_events':

            $category=3;

            break;
        }
  
        $product =$this->get_product_from_reference($data->fieldid, $category);


        if($product){
        
            $value = (new product)->format_product($product);


            $total_purchases = 1;
            $total_price = $discount_price = $taxes = $discount = 0;
            $items = array();

    

            $total_price = $total_price+($value['actual_price']*$data->quantity);
            $discount_price = $discount_price+($value['sale_price']*$data->quantity);
        
            $items[] = array(
                'product_id'=>  $value['id'],
                'name'      =>  $value['name'],
                'quantity'  =>  $data->quantity,
                'total'     =>  ($value['actual_price']*$data->quantity)
            );
           
            $total_discount = $total_price - $discount_price;
            $taxes = $this->caluculate_taxes($discount_price, $this->tax_slab);
            $total = $discount_price + $taxes;
            $return =array(
                'items'             =>  $items,
                'total_price'       =>  $total_price,
                'discount_price'    =>  $discount_price,
                'total_discount'    =>  $total_discount,
                'total'             =>  $total,
                'taxes'             =>  $taxes,
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

            $data=new \stdClass();

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

        try{

            $params=array('days'=>$days);

            $early_registrationinfo=$DB->get_record('earlyregistration_management',$params);

            $return["couponid"] = $coupon->id;

            $early_registrationinfo_expired_date = date('Y-m-d',$early_registrationinfo->earlyregistration_expired_date);

            $currdate=date('Y-m-d'); 

            if($early_registrationinfo_expired_date >= $currdate && $early_registrationinfo->earlyregistration_status == 1) {

                return $early_registrationinfo->discount;

            }else{

                return 0;

            }


        } catch(moodle_exception $e){

          print_r($e);

        } 
        
    }  

    public function enrol($products , $userid) {
        global $DB;
        $roleid = $DB->get_field('role', 'id',array('shortname' => 'trainee'));
        foreach($products['items'] as $product){
            if( !$product['is_enrolled'] ) {

                $productdetails = $DB->get_record('tool_products', ['id' => $product['product_id']], 'category, referenceid');
                switch ($productdetails->category) {
                    case self::TRAINING_PROGRAM:
                         if($product['grouped']){
                            $learningtracks[] = $product['grouped'];
                            $programlist[] = $product['product_id'];
                            continue;
                        }
                         (new trainingprogram)->program_enrollment($productdetails->referenceid, $userid);
                        break;
                    case self::EXAMS:
                        if($product['grouped']){
                            $learningtracks[] = $product['grouped'];
                            $examslist[] = $product['product_id'];
                            continue;
                        }
                        (new exams)->exam_enrollmet($productdetails->referenceid, $userid);
                        break;
                    case self::EVENTS:
                        (new events)->enrol_event($productdetails->referenceid, $userid);
                        break;
                    case self::GRIEVANCE:
                        (new exams)->enrol_grievance($productdetails->referenceid, $userid);
                        break;                
                    default:
                        continue;
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

    public function add_to_wallet($product) {
        global $DB, $USER;

        $userwallet=\local_userapproval\action\manageuser::get_user_wallet($USER->id);
        $formdata->userid = $USER->id;
        if($userwallet->id > 0){
                $formdata->wallet =$userwallet->wallet+$product['total'];
                $formdata->id=$userwallet->id;
                $formdata->timemodified=time();
                $formdata->usermodified=$USER->id;
                $id=$DB->update_record('local_orgofficial_wallet', $formdata);

        }else{
                $formdata->wallet =$product['total'];
                $formdata->timecreated=time();
                $formdata->usercreated=$USER->id;
                $id=$DB->insert_record('local_orgofficial_wallet', $formdata);
        }

        $formdata->walletlog =$product['total'];
        \local_userapproval\action\manageuser::add_user_wallet_logs($formdata);

        $paymentorder=new \stdClass();

        $touser=$DB->get_record('user',array('id'=>$formdata->userid));

        $paymentorder->payment_userfullname=$touser->firstname.' '.$touser->lastname;

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
        $message = $data->description['text'];
        $fromuser = $USER;

        try{
            //$sendmail = email_to_user($userdata, $fromuser, $subject, $message);
            $sendmail = email_to_user($userdata, $fromuser, $subject, $message, '', '','',true, $fromuser->email, fullname($fromuser));

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
        $fromsql = " SELECT ordrpmnt.id,ordrpmnt.trainingname, ordrpmnt.amount, ordrpmnt.purchasedseats, ordrpmnt.originalprice, ordrpmnt.taxes, ordrpmnt.discountprice, ordrpmnt.paymenttype, ordrpmnt.paymentapprovalstatus, ordrpmnt.timecreated";
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
                    //$params = array_merge($params,$comporders['params']);
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

            if($getdata){

                $data->id=$getdata->id;

                $data->timemodified=time();
                $data->usermodified=$data->userid;

                $id=$DB->update_record('tool_user_order_payments', $data);

            }else{

                $data->timecreated=time();
                $data->usercreated=$data->userid;

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

                    $paymentorder->payment_userfullname=$touser->firstname.' '.$touser->lastname;

                    $paymentorder->payment_details=$this->get_payment_details($paymentorder);

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
}
