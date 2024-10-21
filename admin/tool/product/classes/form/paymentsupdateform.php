<?php
namespace tool_product\form;
use core_form\dynamic_form ;
use moodle_url;
use context;
use context_system;
use coding_exception;
use MoodleQuickForm_autocomplete;

class paymentsupdateform extends dynamic_form { 
    public function definition() {
        global $USER, $CFG,$DB;
       
        $id = $this->optional_param('id', 0, PARAM_INT);

        $stable = new \stdClass();
        $stable->thead = false;
        $stable->id = $id;
        $stable->start = 0;
        $stable->length = -1;

        $paymentdata = (new \tool_product\product)->get_post_financialpayments($stable);
        $transactionref = $paymentdata->transactionref ? $paymentdata->transactionref  : 'N/A';
        /*$transactionref = $DB->get_field('tool_product_telr', 'transactionref', ['orgpaymentid' => $paymentdata->id]); 
        if(!$transactionref){
            $transactionref = 'N/A';
        }*/
        $currentlang= current_language();

        if( $currentlang == 'ar'){
            $orgfullname='org.fullnameinarabic as orgname';
        }else{
            $orgfullname='org.fullname as orgname';
        }


        $sql="SELECT u.id,$orgfullname,org.licensekey
            FROM {user} AS u 
            JOIN {local_users} lc ON lc.userid = u.id
            JOIN {local_organization} org ON org.id = lc.organization
            WHERE  u.id=:orguserid ";

        $user=$DB->get_record_sql($sql,array('orguserid'=>$paymentdata->orguserid));      


        $organiname = ($user) ? $user->orgname : 'N/A';
        $organiname = ($user) ? $user->orgname : 'N/A';
        $licensekey = ($user && !empty($user->licensekey)) ? $user->licensekey : 'N/A';

    

        $mform = $this->_form;
        $systemcontext = context_system::instance();
        
        $orgorderpayments = $DB->get_record('tool_org_order_payments',['id'=>$id]);



        $mform->addElement('hidden', 'productid',$orgorderpayments->productid);
        $mform->setType('productid',PARAM_INT);
        $mform->setDefault('productid',$orgorderpayments->productid);


        $mform->addElement('hidden', 'paymenton',$orgorderpayments->paymenton);
        $mform->setType('paymenton',PARAM_RAW);
        $mform->setDefault('paymenton',$orgorderpayments->paymenton);


        $mform->addElement('hidden', 'id', $id);
        $mform->setType('id',PARAM_INT);

        $mform->addElement('hidden', 'orguserid',$paymentdata->orguserid);
        $mform->setType('orguserid',PARAM_INT);

        $mform->addElement('hidden', 'tablename',$paymentdata->tablename);
        $mform->setType('tablename',PARAM_RAW);

        $mform->addElement('hidden', 'fieldname',$paymentdata->fieldname);
        $mform->setType('fieldname',PARAM_RAW);

        $mform->addElement('hidden', 'fieldid',$paymentdata->fieldid);
        $mform->setType('fieldid',PARAM_INT);

        $mform->addElement('hidden', 'purchasedseats',$paymentdata->purchasedseats);
        $mform->setType('purchasedseats',PARAM_INT);

 
        $groupelemnts=array();
        $groupelemnts[] = $mform->createElement('html', \html_writer::tag('b',$organiname,array('class' => 'dynamic_form_id_orginfo')));
        $mform->addGroup($groupelemnts, 'orginfo',get_string('organiname', 'tool_product'), array('class' => 'orginfo'), false);

        $groupelemnts=array();
        $groupelemnts[] = $mform->createElement('html', \html_writer::tag('b', $licensekey,array('class' => 'dynamic_form_id_licenseinfo')));
        $mform->addGroup($groupelemnts, 'licenseinfo',get_string('licenseid', 'tool_product'), array('class' => 'licenseinfo'), false);

        $groupelemnts=array();
        $groupelemnts[] = $mform->createElement('html', \html_writer::tag('b', $transactionref,array('class' => 'dynamic_form_id_purchaseinfo')));
        $mform->addGroup($groupelemnts, 'purchaseinfo',get_string('purchaseid', 'tool_product'), array('class' => 'purchaseinfo'), false);

        $groupelemnts=array();
        $groupelemnts[] = $mform->createElement('html', \html_writer::tag('b', number_format($paymentdata->payableamount). ' '.get_string('sa_currency','local_trainingprogram'),array('class' => 'dynamic_form_id_amountdueinfo')));
        $mform->addGroup($groupelemnts, 'amountdueinfo',get_string('amountdue', 'tool_product'), array('class' => 'amountdueinfo'), false);

        $groupelemnts = array();
        $groupelemnts[0] = $mform->createElement('radio','amountrecived','',get_string('yes'),1);
        $groupelemnts[1] = $mform->createElement('radio','amountrecived','',get_string('no'),0);
        $mform->addGroup( $groupelemnts,'recivedamount',get_string('amountrecived', 'tool_product'), array('class' => 'amountdueinfo'), false);
        $mform->setDefault('amountrecived', $paymentdata->amountrecived);



        $mform->addElement('text', 'transactionid',get_string('transactionid', 'tool_product'));
        $mform->setType('transactionid', PARAM_RAW);
        $mform->setDefault('transactionid', $paymentdata->transactionid);

        $mform->addElement('advcheckbox', 'checkid',get_string('checkid', 'tool_product'));
        $mform->setDefault('checkid', $paymentdata->checkid);


        $mform->addElement('textarea', 'transactionnote',get_string('transactionnote', 'tool_product'),array('rows'=>3));
        $mform->setType('transactionnote', PARAM_RAW);
        $mform->setDefault('transactionnote', $paymentdata->transactionnote);



    }
    public function validation($data, $files) {
        $errors = array();
        global $DB, $CFG;

        $amountrecived=$data['amountrecived'];

        // if($amountrecived == 1){

        //     $transactionid=$data['transactionid'];
        //     $transactionnote=$data['transactionnote'];
        //     $checkid=$data['checkid'];

        //     if (empty(trim($transactionid)) && empty(trim($transactionid))){
        //        $errors['transactionid'] = get_string('entertransactionid','tool_product');
        //     }
        //     if (empty(trim($transactionnote)) && empty(trim($transactionnote))){
        //         $errors['transactionnote'] = get_string('entertransactionnote','tool_product');
        //     }
        //     if (empty(trim($checkid)) && $checkid == 0){
        //         $errors['checkid'] = get_string('checkcheckid','tool_product');
        //     }
        // }

        
        return $errors;
    }
       /**
     * Returns context where this form is used
     *
     * @return context
     */
    protected function get_context_for_dynamic_submission(): context {
        return \context_system::instance();
    }

    /**
     * Checks if current user has access to this form, otherwise throws exception
     */
    protected function check_access_for_dynamic_submission(): void {
        //require_capability('local/organization:manage', $this->get_context_for_dynamic_submission()) || ;
         has_capability('tool/products:managefinancialpayments', $this->get_context_for_dynamic_submission()) 
        || has_capability('local/organization:manage_financial_manager', $this->get_context_for_dynamic_submission());
    }

    /**
     * Process the form submission, used if form was submitted via AJAX
     */
    public function process_dynamic_submission() {
        global $CFG, $DB;
        $data = $this->get_data();

        if($data) {
             (new \tool_product\product)->insert_update_org_order_payments($data);
        }
        
    }

    /**
     * Load in existing data as form defaults
     */
    public function set_data_for_dynamic_submission(): void {
    
    }

    /**
     * Returns url to set in $PAGE->set_url() when form is being rendered or submitted via AJAX
     *
     * @return moodle_url
     */
    protected function get_page_url_for_dynamic_submission(): moodle_url {
        $id = $this->optional_param('id', 0, PARAM_INT);

        return new moodle_url('/product/financialpayments.php', ['id' => $id]);
    } 
}
