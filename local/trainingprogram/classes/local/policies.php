<?php
namespace local_trainingprogram\local;
use local_sector\local\policies as corepolicies;
use local_trainingprogram\local\trainingprogram as program;
use tool_product\product;
use stdClass;
class policies extends corepolicies
{
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
    public function __construct($entitytype, $purchasedate, $type,$entitytable='tp_offerings',$ownedby=false, $attemptsnum=false) {
        parent::__construct($entitytype, $purchasedate,$type,$entitytable,$ownedby,$attemptsnum);
    }
    public function enrolled_by($userid,$productid) {
        global $DB;
        $systemcontext = \context_system::instance();
        $productinfo = $DB->get_record('tool_products',['id'=>$productid]);
        $data = new stdClass();
        $offering =$DB->get_record('tp_offerings',['id'=>$productinfo->referenceid]);
        $courseid = $DB->get_field('local_trainingprogram','courseid',['id'=>$offering->trainingid]);
        $enrolleduserid =(int) $DB->get_field('program_enrollments','usercreated',['programid'=>$offering->trainingid,'offeringid'=>$productinfo->referenceid,'courseid'=>$courseid,'userid'=>$userid]);
        $orgofficialid =(int) $DB->get_field('program_enrollments','orgofficial',['programid'=>$offering->trainingid,'offeringid'=>$productinfo->referenceid,'courseid'=>$courseid,'userid'=>$userid]);

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
                $this->refund_amount($data->userid, $data->productid, false, $data->policyconfirm);
                $traineeroleid = $DB->get_field('role', 'id', array('shortname' => 'trainee'));
                $courseid=$DB->get_field('local_trainingprogram','courseid', array('id' =>(int)$data->programid));
                (new program)->program_unenrollment($data->programid,$data->offeringid,$courseid,$data->userid,$traineeroleid,'cancel');
            break;
            case 'organizationofficial':

                $sendingdata =new stdClass();
                $sadadrecord = $DB->get_record('tool_product_sadad_invoice',['productid'=>$data->productid,'invoice_number'=>$data->invoicenumber]);
                $sendingdata->programid = $data->programid;
                $sendingdata->offeringid = $data->offeringid;
                $sendingdata->userid =$sadadrecord->userid;
                $sendingdata->type = 'cancel';
                $sendingdata->entitytype = 'trainingprogram';
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
                    $traineeroleid = $DB->get_field('role', 'id', array('shortname' => 'trainee'));
                    $courseid=$DB->get_field('local_trainingprogram','courseid', array('id' => $data->programid));
                    (new program)->program_unenrollment($data->programid,$data->offeringid,$courseid,$data->userid,$traineeroleid,'cancel');
                }

            break;
            default:

                if($data->enrolltype == 2) {
                    $sendingdata =new stdClass();
                    $sadadrecord = $DB->get_record('tool_product_sadad_invoice',['productid'=>$data->productid,'invoice_number'=>$data->invoicenumber]);
                    $sendingdata->programid = $data->programid;
                    $sendingdata->offeringid = $data->offeringid;
                    $sendingdata->userid =$sadadrecord->userid;
                    $sendingdata->type = 'cancel';
                    $sendingdata->entitytype = 'trainingprogram';
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
                        $traineeroleid = $DB->get_field('role', 'id', array('shortname' => 'trainee'));
                        $courseid=$DB->get_field('local_trainingprogram','courseid', array('id' => $data->programid));
                        (new program)->program_unenrollment($data->programid,$data->offeringid,$courseid,$data->userid,$traineeroleid,'cancel');
                    }
               }
            break;
        }
    }
	
}
