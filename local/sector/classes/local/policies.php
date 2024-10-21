<?php
namespace local_sector\local;

use local_trainingprogram\local\refundsettings;
use stdClass;
use context_system;

abstract class policies
{  
    //Type and value for refund
    protected $perctage=array();

    //Defined settings for the entity
    protected $settings;

    protected $type;

    //Entity type will be learning item i.e Training programs, exams and events
    protected  $entitytype;

    //What is the purchase date of entity
    protected  $purchasedate;
    //Exam Ownedby
    protected  $ownedby;
    //User attempt number for reschedule
    protected  $attemptsnum;

    public $refundamount;

    public $deductamount;

    public $entitytable;

    private $userid;

    private $productid;

    private $invoicenumber;
    
    private $entityid;

    private $transactionid;

    private $productamount;

    private $refundpolicy;

    private $role;


    public function __construct($entitytype, $purchasedate, $type='cancel', $entitytable='local_exam_profiles', $ownedby=false, $attemptsnum=false){
        $this->entitytype = $entitytype;
        $this->purchasedate = $purchasedate;
        $this->type = $type;
        $this->ownedby = $ownedby;
        $this->attemptsnum = $attemptsnum;
        $this->entitytable = $entitytable;
        $this->settings = new stdClass;
    }

    public function get_refund_settings(){
        global $DB;
        $dayto = $this->purchasedate;
        $ownedbysql = ''; 
        if( $this->entitytype == 'exam') {
              
            if($this->ownedby == 'CISI') {
                $ownedbysql .=  " AND ownedbycisi=1 ";
                if($this->attemptsnum > 0 ) {
                    $ownedbysql .=  " AND moreattempts=1 ";
                } else {
                    $ownedbysql .=  " AND moreattempts=0 ";
                }
            } else {
                $ownedbysql .=  " AND ownedbycisi=0 ";
            }

        }

        //$days = floor(($dayto - time()) / (60 * 60 * 24));

        $fromdate = strtotime(userdate($dayto,'%Y-%m-%d'));
        $currentdate = strtotime(userdate(time(),'%Y-%m-%d'));
        $days = floor(($fromdate - $currentdate) / (60 * 60 * 24));
       
        $sql = 'SELECT dedtype, dedpercentage,dedamount 
                  FROM {refund_settings} 
                 WHERE entitytype=:entitytype AND type=:type AND dayto >= :days1 AND dayfrom <= :days2'. $ownedbysql;
          
        $this->settings = $DB->get_record_sql($sql,
                                              ['entitytype' => $this->entitytype, 'type' => $this->type ,'days1' => $days, 'days2' => $days], IGNORE_MISSING);
    }


    public function get_refund_percentage(){
        $this->get_refund_settings();
        if($this->settings->dedtype == refundsettings::PERCENTAGE){
            $this->perctage = ['type' =>refundsettings::PERCENTAGE, 'value' => $this->settings->dedpercentage];
        }else{
            $this->perctage = ['type' =>refundsettings::AMOUNT, 'value' => $this->settings->dedamount];
        }
        return $this->perctage;
    }

    public function refund_amount($userid, $productid, $invoicenumber = false, $refundpolicy=false,$enrolltype=0) {
        global $DB;

        $this->userid = $userid;
        $this->productid = $productid;
        $this->invoicenumber = $invoicenumber;
        $this->refundpolicy = $refundpolicy;

        $enroleduserrole = $this->enrolled_by($userid, $productid);
        $this->role = $enroleduserrole->shortname;

        switch ($enroleduserrole->shortname){
            case 'trainee':
                $transaction = $DB->get_record('tool_user_order_payments', ['productid' => $productid, 'userid' => $userid, 'tablename' =>  $this->entitytable]);
                $this->entityid = $transaction->fieldid;
                $this->transactionid = $transaction->telrid;
                $this->productamount = $transaction->amount;
                $this->get_refundamount($transaction->amount, $this->refundpolicy);
                $response = $this->originalprice_refund();
                return $response;
                break;
            case 'organizationofficial':

                $transaction = $DB->get_record('tool_product_sadad_invoice', ['productid' => $productid, 'invoice_number' => $invoicenumber]);
                $this->entityid = $transaction->id;
                $this->transactionid = $transaction->invoice_number;
                $this->productamount = $transaction->amount;
                $transactionamount = (($transaction->amount)/(int)$transaction->seats);
                $this->refundamount = $this->get_refundamount($transactionamount, $this->refundpolicy);
                break;

            default:

              if($enrolltype == 2) {

                $transaction = $DB->get_record('tool_product_sadad_invoice', ['productid' => $productid, 'invoice_number' => $invoicenumber]);
                $this->entityid = $transaction->id;
                $this->transactionid = $transaction->invoice_number;
                $this->productamount = $transaction->amount;
                $transactionamount = (($transaction->amount)/(int)$transaction->seats);
                $this->refundamount = $this->get_refundamount($transactionamount, $this->refundpolicy);
                break;

              }

            break;
        }
    }
    public function get_refundamount($transactionamount, $policy=false)
    {
        if($policy) {
            $this->get_refund_percentage();
            if($this->perctage['type'] == refundsettings::AMOUNT){
                $this->deductamount = $this->perctage['value'];
                
            }else if($this->perctage['type'] == refundsettings::PERCENTAGE){ 
                $this->deductamount = ($transactionamount / 100) * $this->perctage['value'];
            }
            $this->refundamount = $transactionamount - $this->deductamount;
        } else {      
            $this->refundamount = $transactionamount;
        }

        return $this->refundamount;
    }

    public function originalprice_refund(){
        global $DB, $USER;
        $productinfo = array();

        $productinfo['entitytype'] = $this->entitytype;
        $productinfo['entityid'] = $this->entityid;
        $productinfo['total'] = $this->refundamount;

        if ($this->type != 'reschedule') {

            if ($this->refundpolicy && $this->role != 'trainee') {
            $amount = $this->refundamount;
            } elseif($this->role == 'trainee') {
                $amount = $this->refundamount;
            } else {
                $amount = $this->productamount;
            } 
            if($amount > 0) {
                $response = (new \tool_product\telr)->refund($this->transactionid, $amount);
               
                $sql = "SELECT id 
                        FROM {local_cancel_logs} 
                        WHERE entitytype = '{$this->entitytype}' AND  productid=$this->productid AND userid=$this->userid
                        ORDER BY id DESC ";
                $cancelid = $DB->get_field_sql($sql);
                if ($cancelid) {
                    $DB->update_record('local_cancel_logs', ['id'=>$cancelid, 'refundamount'=> $amount, 'timemodified'=> time()]);
                } else {
                    $DB->insert_record('local_cancel_logs', ['entitytype'=> $this->entitytype, 'productid'=> $this->productid, 'refundamount'=> $amount, 'userid'=> $USER->id, 'policy'=> 1, 'usercreated' => $USER->id, 'timecreated'=> time()]);
                }  
                return $response;
                
            }
        }
    }

    public function is_decimal($val){
 
        return is_numeric( $val ) && floor( $val ) != $val;

    }

   

    public function refund_details($userid, $productid, $refundpolicy=false,$enrolltype = 0) {
        global $DB;
        $systemcontext = context_system::instance();
        $enroleduserrole = $this->enrolled_by($userid, $productid);

        switch ($enroleduserrole->shortname){
            case 'trainee':
                $transaction = $DB->get_record('tool_user_order_payments', ['productid' => $productid, 'userid' => $userid, 'tablename' => $this->entitytable]);
                $this->get_refundamount($transaction->amount, $refundpolicy); // 1 means policy will apply for trainee
                $data = new stdClass();
                $data->refundamount = ($this->is_decimal($this->refundamount)? round($this->refundamount,2) : round($this->refundamount));
                $deductamount = $transaction->amount - $this->refundamount;
                $data->deductamount = ($this->is_decimal($deductamount)? round($deductamount,2) : round($deductamount));
                $data->productprice = !empty($transaction->amount) ? $transaction->amount : 0;
                $data->isorgofficial = 0;
                break;
            case 'organizationofficial':
                // if($this->type == 'cancel'){
                    $data = $this->orgofficial_enrolled_cancel_calculated_data($productid,$userid,$enroleduserrole->enrolleduserid,$this->purchasedate,$refundpolicy);
                // }else if($this->type == 'reschedule'){
                //     $data = $this->reschedule_calculated_data($productid,$userid,$enroleduserrole,$refundpolicy);
                // }
                
                $data->isorgofficial = 1;
                break;

            default:
                if($enrolltype == 2) {
                    $data = $this->orgofficial_enrolled_cancel_calculated_data($productid,$userid,$enroleduserrole->enrolleduserid,$this->purchasedate,$refundpolicy,2,$enroleduserrole->orgofficialid);
                } else {

                    if((!is_siteadmin() && has_capability('local/organization:manage_trainee',$systemcontext))) {
                        $transaction = $DB->get_record('tool_products', ['id' => $productid]);
                        $transaction->amount = $transaction->price;
                        $this->get_refundamount($transaction->amount, $refundpolicy); // 1 means policy will apply for trainee
                        $data = new stdClass();
                        $data->refundamount = ($this->is_decimal($this->refundamount)? round($this->refundamount,2) : round($this->refundamount));
                        $deductamount = $transaction->amount - $this->refundamount;
                        $data->deductamount = ($this->is_decimal($deductamount)? round($deductamount,2) : round($deductamount));
                        $data->productprice = !empty($transaction->amount) ? $transaction->amount : 0;
                        $data->isorgofficial = 0;
                    } else {
                        $data = new stdClass();
                        $data->refundamount = 0;
                        $data->deductamount = 0;
                        $data->productprice = 0;
                        $data->isorgofficial = 0;
                    }

                }
            break;
        }       

        return $data;
    }

    public function orgofficial_enrolled_cancel_calculated_data($productid,$userid,$enrolleduserid,$examdate,$refundpolicy=false,$enrolltype=0,$orgofficialid = 0) {
        global $DB,$USER;

        $user_id = ($enrolltype == 2) ? (($orgofficialid > 0) ? $orgofficialid : 0):$enrolleduserid;
        $returndata =new stdClass();
        $invoice = new stdClass();

        if($this->type =='reschedule') {
            $price = $DB->get_field('tool_products', 'price', ['id' => $productid]);
           
            $this->get_refund_settings();
            if($this->settings->dedtype == refundsettings::PERCENTAGE){
                $this->perctage = ['type' =>refundsettings::PERCENTAGE, 'value' => $this->settings->dedpercentage];
                
            }else{
                $this->perctage = ['type' =>refundsettings::AMOUNT, 'value' => $this->settings->dedamount];
               
            }
            $price = $DB->get_field('tool_products', 'price', ['id' => $productid]);
            $reschedulingamount =  ($this->settings->dedtype == refundsettings::PERCENTAGE) ? self::get_refundamount($price, 1) : $this->perctage;
            $finalrechedulingamount = (is_array($reschedulingamount)) ? (int) $reschedulingamount['value'] : $reschedulingamount;
            $returndata->newamount = $finalrechedulingamount;
            $returndata->refundamount = 0;
            $returndata->productprice = $price;
            $returndata->refundamount = $finalrechedulingamount;
            $returndata->refundamount = $finalrechedulingamount;           
            $returndata->deductamount = $finalrechedulingamount;
            $returndata->invoicenumber =  0;
            $returndata->newinvoiceamount = ($finalrechedulingamount)? ($this->is_decimal($finalrechedulingamount)? round($finalrechedulingamount,2) : round($finalrechedulingamount)) : 0;
            $returndata->seats = 1;
            return  $returndata;

        } else {


            if($this->attemptsnum > 1) {
                $price = $DB->get_field('tool_products', 'price', ['id' => $productid]);
                $invoice->seats = 1;
                $returndata->newamount = ($price) ? ($this->is_decimal($price)? round($price,2) : round($price)):0;                          
                $returndata->productprice =($price) ? $price : 0;
                self::get_refundamount($price, 1);
                $refundamount = ($this->refundamount) ? $this->refundamount : 0;    
                $returndata->refundamount=($this->is_decimal($refundamount)? round($refundamount,2) : round($refundamount));
                $deductamount = $returndata->productprice - $returndata->refundamount;
            } else {
                $invoice  = (new \tool_product\telr)->get_pending_invoice($productid,$user_id);
                $oneseatamount = ($invoice) ? (($invoice->amount)/$invoice->seats) : 0;
                $remainingamount =  $invoice->payableamount-$oneseatamount;               

                $newamount = $oneseatamount * ($invoice->seats-1);         
                $returndata->newamount = ($invoice) ? ($this->is_decimal($newamount)? round($newamount,2) : round($newamount)):0;                          
                $returndata->productprice =($invoice) ? $invoice->payableamount : 0; ;
        
                $this->refund_amount($userid,$productid,$invoice->invoice_number,$refundpolicy,$enrolltype);
                $refundamount = ($invoice) ? (($this->refundamount) ? $this->refundamount :0): 0;
        
                $returndata->refundamount=($this->is_decimal($refundamount)? round($refundamount,2) : round($refundamount));
        
                $deductamount = $remainingamount + ($oneseatamount - $refundamount);
            }

        
            $returndata->deductamount = ($this->is_decimal($deductamount)? round($deductamount,2) : round($deductamount));
            $newinvoiceamount = $deductamount;
            $returndata->invoicenumber = ($invoice) ? $invoice->invoice_number : 0;
            $returndata->newinvoiceamount = ($newinvoiceamount)? ($this->is_decimal($newinvoiceamount)? round($newinvoiceamount,2) : round($newinvoiceamount)) : 0;
            $returndata->seats = $invoice->seats-1;
            return  $returndata;
        }

        
    }
}
