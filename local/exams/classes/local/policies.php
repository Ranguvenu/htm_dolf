<?php
namespace local_exams\local;
use local_sector\local\policies as corepolicies;
use tool_product\product;
use context_system;
use stdClass;

class policies extends corepolicies
{
    public const EXAMATTEMPT = 6;

    public $refundamount;

    public $entitytable;

    private $userid;

    private $productid;

    private $invoicenumber;
    
    private $entityid;

    private $transactionid;

    private $productamount;

    private $refundpolicy;

    private $role;

    public function __construct($entitytype, $purchasedate, $type,$entitytable='local_exam_profiles',$ownedby=false, $attemptsnum=false) {
        parent::__construct($entitytype,$purchasedate,$type,$entitytable,$ownedby,$attemptsnum);
    }
    public function enrolled_by($userid,$productid) {
        global $DB;
        $systemcontext = context_system::instance();
        $productinfo = $DB->get_record('tool_products',['id'=>$productid]);
        $data = new stdClass();

        if($productinfo->category == self::EXAMATTEMPT){
            $profile =$DB->get_record('local_exam_attempts',['id'=>$productinfo->referenceid]);
        } else {
            $profile =$DB->get_record('local_exam_profiles',['id'=>$productinfo->referenceid]);
        }

        $courseid = $DB->get_field('local_exams','courseid',['id'=>$profile->examid]);
        $enrolleduserid =(int) $DB->get_field('exam_enrollments','usercreated',['examid'=>$profile->examid,'courseid'=>$courseid,'userid'=>$userid]);   
        $orgofficialid =(int) $DB->get_field('exam_enrollments','orgofficial',['examid'=>$profile->examid,'courseid'=>$courseid,'userid'=>$userid]);             
        $roleinfo = $DB->get_record_sql('SELECT rol.* FROM {role} rol 
                                                JOIN {role_assignments} rola ON rola.roleid = rol.id
                                                WHERE rola.userid =:userid and contextid =:contextid',['userid'=>$enrolleduserid,'contextid'=>$systemcontext->id]);

        $data->role =(int) $roleinfo->id;
        $data->shortname = $roleinfo->shortname;
        $data->enrolleduserid = $enrolleduserid;
        $data->orgofficialid = ($orgofficialid) ? $orgofficialid : 0;
        return $data;
    }
    public function cancel_process($data) {
        global $DB, $USER;
        $enroleduserrole = $this->enrolled_by($data->userid, $data->productid);
        switch ($enroleduserrole->shortname){
            case 'trainee':
                $data = (new \local_exams\local\cancelentities)->trainee_refundinfo('exam', $data->productid,'cancel', $data->userid, true, $data->policyconfirm,$data->actiontype);
                return $data;

            break;
            case 'organizationofficial':

                $sendingdata =new stdClass();
                $sadadrecord = $DB->get_record('tool_product_sadad_invoice',['productid'=>$data->productid,'invoice_number'=>$data->invoicenumber]);
                $sendingdata->examid = $data->examid;
                $sendingdata->profileid = $data->profileid;
                $sendingdata->userid =$sadadrecord->userid;
                $sendingdata->type = 'cancel';
                $sendingdata->entitytype = 'exam';
                $sendingdata->productid = $data->productid;
                $sendingdata->seats = ($sadadrecord->seats - 1);
                $sendingdata->odl_invoicenumber = $data->invoicenumber;
                $sendingdata->total = $data->newinvoiceamount;
                $sendingdata->payableamount = $data->newinvoiceamount;
                $sendingdata->amount = $data->newamount;
                $productdata =  base64_encode(serialize((array)$sendingdata));
                if($data->newinvoiceamount <= 0){
                    $returndata = true;
                }else{
                    $returndata = (new product)->insert_update_sadad_invoice_record($productdata);
                }
                if($returndata){
                    (new \tool_product\telr)->void_invoice($data->invoicenumber);
                    (new exams)->exam_unenrollmet($data->profileid,$data->userid,'cancel');

                }
                break;
            default:

            if($data->enrolltype == 2) {

                $sendingdata =new stdClass();
                $sadadrecord = $DB->get_record('tool_product_sadad_invoice',['productid'=>$data->productid,'invoice_number'=>$data->invoicenumber]);
                $sendingdata->examid = $data->examid;
                $sendingdata->profileid = $data->profileid;
                $sendingdata->userid =$sadadrecord->userid;
                $sendingdata->type = 'cancel';
                $sendingdata->entitytype = 'exam';
                $sendingdata->productid = $data->productid;
                $sendingdata->seats = ($sadadrecord->seats - 1);
                $sendingdata->odl_invoicenumber = $data->invoicenumber;
                $sendingdata->total = $data->newinvoiceamount;
                $sendingdata->payableamount = $data->newinvoiceamount;
                $sendingdata->amount = $data->newamount;
                $sendingdata->enrolltype = $data->enrolltype;
                $productdata =  base64_encode(serialize((array)$sendingdata));
                if($data->newinvoiceamount <= 0){
                    $returndata = true;
                }else{
                    $returndata = (new product)->insert_update_sadad_invoice_record($productdata);
                }
                if($returndata){
                    (new \tool_product\telr)->void_invoice($data->invoicenumber);
                    (new exams)->exam_unenrollmet($data->profileid,$data->userid,'cancel');

                }
            }
            break;
        }
    }

    public function reschedule_calculated_data($productid,$userid,$currentuserroleinfo,$refundpolicy=false) {
        global $DB,$USER;
        $context = context_system::instance();
        $returndata =new stdClass();
        $productrecord = $DB->get_record('tool_products',['id'=>$productid]);
        $profilerecord =$DB->get_record('local_exam_profiles',['id'=>$productrecord->referenceid]);
        $examdate = $DB->get_field('local_exam_userhallschedules','examdate',
                                      ['examid'=>$profilerecord->examid,'profileid'=>$productrecord->referenceid,'userid'=>$userid]);
                                      
        if($currentuserroleinfo->shortname == 'trainee') {
            $this->get_refundamount((int)$productrecord->price, 1);
            $returndata->productprice =(int)$productrecord->price;
            $refundamount = ($this->refundamount) ? $this->refundamount :0;
            $returndata->refundamount=($this->is_decimal($refundamount)? round($refundamount,2) : round($refundamount));
            $deductamount = ((int)$productrecord->price - $refundamount);
            $returndata->deductamount = ($this->is_decimal($deductamount)? round($deductamount,2) : round($deductamount));            
        } else {
            $user_id = (is_siteadmin() || ($currentuserroleinfo->shortname == 'organizationofficial') || ($currentuserroleinfo->shortname == 'examofficial')) ? $currentuserroleinfo->enrolleduserid : $USER->id;
            $invoice  = (new \tool_product\telr)->get_pending_invoice($productid,$user_id);
            $oneseatamount = ($invoice) ? (($invoice->amount)/$invoice->seats) : 0;
            $remainingamount =  $invoice->amount-$oneseatamount;                                         
            $returndata->productprice =($invoice) ? $invoice->amount : 0;
            if(!is_siteadmin() && has_capability('local/organization:manage_organizationofficial',$context)) {
                $this->refund_amount($userid,$productid,$invoice->invoice_number, 1); // For Orgoff Policies will apply
            } else {
                $this->get_refundamount($oneseatamount, $refundpolicy);
            }
 
            $refundamount = ($invoice) ? (($this->refundamount) ? $this->refundamount :0): 0;
            $returndata->refundamount=($this->is_decimal($refundamount)? number_format($refundamount,2) : number_format($refundamount));
            $deductamount = ($oneseatamount - $refundamount);
            $returndata->deductamount = ($this->is_decimal($deductamount)? number_format($deductamount,2) : number_format($deductamount));

        }
       
        return $returndata;
    }



 

}
