<?php
namespace local_events;
use local_sector\local\policies as corepolicies;
use local_events\events as event;
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

    public function __construct($entitytype, $purchasedate, $type,$entitytable='local_events',$ownedby=false, $attemptsnum=false) {
        parent::__construct($entitytype,$purchasedate,$type,$entitytable,$ownedby,$attemptsnum);
    }

    public function enrolled_by($userid,$productid) {
        global $DB;
        $systemcontext = \context_system::instance();
        $productinfo = $DB->get_record('tool_products',['id'=>$productid]);
        $data = new stdClass();
        $enrolleduserid =(int) $DB->get_field('local_event_attendees','usercreated',['eventid'=>$productinfo->referenceid,'userid'=>$userid]);
        
        $roleinfo = $DB->get_record_sql('SELECT rol.* FROM {role} rol 
                                                JOIN {role_assignments} rola ON rola.roleid = rol.id
                                                WHERE rola.userid =:userid and contextid =:contextid',['userid'=>$enrolleduserid,'contextid'=>$systemcontext->id]);
        $data->role =(int) $roleinfo->id;
        $data->shortname = $roleinfo->shortname;
        $data->enrolleduserid = $enrolleduserid;
        $data->orgofficialid = 0;

        return $data;
    }
    /**
     * Summary of cancel_process
     * @param mixed $data
     * @return void
     */
    public function cancel_process($data) {
        global $DB, $USER;
        $enroleduserrole = $this->enrolled_by($data->userid, $data->productid);
        switch ($enroleduserrole->shortname){
            case 'trainee':
                $this->refund_amount($data->userid, $data->productid, false, $data->policyconfirm);
                (new event)->unenroll_event($data->eventid,$data->userid);
                break;
            case 'organizationofficial':
                $sendingdata =new stdClass();
                $sadadrecord = $DB->get_record('tool_product_sadad_invoice',['productid'=>$data->productid,'invoice_number'=>$data->invoicenumber]);
                $sendingdata->eventid = $data->eventid;
                $sendingdata->userid =$sadadrecord->userid;
                $sendingdata->type = 'cancel';
                $sendingdata->entitytype = 'event';
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
                    (new event)->unenroll_event($data->eventid,$data->userid,'cancel');
                }

            break;
        }
    }
	
}
