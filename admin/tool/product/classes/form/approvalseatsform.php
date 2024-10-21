<?php
namespace tool_product\form;
use core_form\dynamic_form ;
use moodle_url;
use context;
use context_system;
use coding_exception;
use MoodleQuickForm_autocomplete;

class approvalseatsform extends dynamic_form { 
    public function definition() {
        global $USER, $CFG,$DB;


        $mform = $this->_form;
        $systemcontext = context_system::instance();


        $mform->addElement('hidden', 'id');
        $mform->setType('id',PARAM_INT);


        $mform->addElement('hidden', 'purchasedseatschk');
        $mform->setType('purchasedseatschk',PARAM_RAW);

 
        $field_one = array();
        $field_one[] =& $mform->createElement('text', 'approvalseats', '', array('size' => 5));
        $field_one[] =& $mform->createElement('static', '','','/');
        $field_one[] =& $mform->createElement('static', 'purchasedseatschk', '');
        $mform->addGroup($field_one, 'approvalseats', get_string('approvalseats', 'tool_product'), array(' '), false);

    }
    public function validation($data, $files) {
        $errors = array();
        global $DB, $CFG;
        $approvalseats=$data['approvalseats'];
        $purchasedseats=(int)$data['purchasedseatschk'];
        
        if (empty(trim($approvalseats))){
           $errors['approvalseats'] = get_string('enternumofseats','tool_product');
        }
        elseif (!empty(trim($approvalseats)) && !is_numeric($approvalseats)){
            $errors['approvalseats'] = get_string('requirednumeric','tool_product');
        }
        elseif ( (int) $approvalseats<=0){
            $errors['approvalseats'] = get_string('invalidnumericformat','tool_product');
        }
        elseif (!empty(trim($approvalseats)) && is_numeric($approvalseats) && (int) $approvalseats > $purchasedseats){
            $errors['approvalseats'] = get_string('cantexceedpurchasedseats','tool_product');
        }

        
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
        require_capability('local/organization:manage_organizationofficial', $this->get_context_for_dynamic_submission());
    }

    /**
     * Process the form submission, used if form was submitted via AJAX
     */
    public function process_dynamic_submission() {
        global $CFG, $DB,$OUTPUT;
        $data = $this->get_data();

        if($data) {
            $paymentinfo = $DB->get_record_sql("SELECT oop.amount, oos.approvalseats, oos.purchasedseats, oos.orguserid,oop.id as paymentid
                                                  FROM {tool_org_order_payments} AS oop
                                                  JOIN {tool_order_approval_seats} AS oos ON oop.id=oos.paymentid
                                                  WHERE oos.id=:id", ['id' => $data->id]);
            
            if($paymentinfo->amount == 0) {
                $productdata = (new \tool_product\product)->insert_update_org_order_approvals($data);
            }
            $data->userid = $paymentinfo->orguserid;
            $data->paymenttype = 'postpaid';
            $data->paymentid = $paymentinfo->paymentid;
            $data->total = ($paymentinfo->amount / $paymentinfo->purchasedseats) * $data->approvalseats;

            return base64_encode(serialize((array)$data));
        }
    }

    /**
     * Load in existing data as form defaults
     */
    public function set_data_for_dynamic_submission(): void {
        global $DB;

        $id = $this->optional_param('id', 0, PARAM_INT);

        $data=$DB->get_record('tool_order_approval_seats',array('id'=>$id),'id,purchasedseats,approvalseats');
       
        $data->purchasedseatschk=$data->purchasedseats-$data->approvalseats;

        $data->approvalseats=NULL;

        $this->set_data($data);
    }

    /**
     * Returns url to set in $PAGE->set_url() when form is being rendered or submitted via AJAX
     *
     * @return moodle_url
     */
     protected function get_page_url_for_dynamic_submission(): \moodle_url {

        $id = $this->optional_param('id', 0, PARAM_INT);

        return new moodle_url('/product/orderapproval.php', ['id' => $id]);
    }
}
