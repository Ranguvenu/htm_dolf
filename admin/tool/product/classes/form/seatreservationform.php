<?php
namespace tool_product\form;
use core_form\dynamic_form ;
use moodle_url;
use context;
use context_system;
use coding_exception;
use MoodleQuickForm_autocomplete;

class seatreservationform extends dynamic_form { 
    public function definition() {
        global $USER, $CFG,$DB;

        $mform = $this->_form;


        if(isset($this->_ajaxformdata['seatsdata']) && !empty($this->_ajaxformdata['seatsdata'])){

            $seatsdata=$this->_ajaxformdata['seatsdata'];

            $data=(object)unserialize(base64_decode($seatsdata));

            $availableseats=$this->check_product_availableseats($data);

        }else{

            $availableseats=$this->_ajaxformdata['availableseats'];

        }

        $systemcontext = context_system::instance();

        $mform->addElement('hidden', 'tablename');
        $mform->setType('tablename',PARAM_RAW);

        $mform->addElement('hidden', 'fieldname');
        $mform->setType('fieldname',PARAM_RAW);

        $mform->addElement('hidden', 'fieldid');
        $mform->setType('fieldid',PARAM_INT);

        $mform->addElement('hidden', 'parentfieldid');
        $mform->setType('parentfieldid',PARAM_INT);

        $mform->addElement('hidden', 'availableseats');
        $mform->setType('availableseats',PARAM_RAW);

        $mform->addElement('hidden', 'autoapproval');
        $mform->setType('autoapproval', PARAM_INT);

        $field_one = array();
        $field_one[] =& $mform->createElement('text', 'selectedseats', '', array('size' => 5));

        if($availableseats && $availableseats != -9999){

            $field_one[] =& $mform->createElement('static', '','','/');
            $field_one[] =& $mform->createElement('static', 'availableseats', '');

        }

        $mform->addGroup($field_one, 'selectedseats', get_string('selectedseats', 'tool_product'), array(' '), false);
        $mform->addRule('selectedseats', get_string('required'), 'required', null, 'server');

    }
    public function validation($data, $files) {
        $errors = array();
        global $DB, $CFG;
        $selectedseats=$data['selectedseats'];
        $availableseats=(int)$data['availableseats'];
        if (empty(trim($selectedseats))){
           $errors['selectedseats'] = get_string('enternumofseats','tool_product');
        }
        elseif (!empty(trim($selectedseats)) && !is_numeric($selectedseats)){
            $errors['selectedseats'] = get_string('requirednumeric','tool_product');
        }
        elseif ( (int) $selectedseats<=0){
            $errors['selectedseats'] = get_string('invalidnumericformat','tool_product');
        }
        elseif ($availableseats && $availableseats !=-9999 && !empty(trim($selectedseats)) && is_numeric($selectedseats) && (int) $selectedseats > $availableseats){
            $errors['selectedseats'] = get_string('cantexceedavailableseats','tool_product');
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
    public function process_dynamic_submission()
    {
        global $CFG, $DB, $OUTPUT, $USER;
        $data = $this->get_data();
        if ($data) {

            $category = 0;

            switch ($data->tablename) {

                case 'tp_offerings':

                    $category = 1;

                    break;

                case 'local_exam_profiles':

                    $category = 2;

                    break;

                case 'local_events':

                    $category = 3;

                    break;
            }

            $productparams = (new \tool_product\product)->get_product_attributes($data->fieldid, $category, 'addtocart', 0, 0, $data->selectedseats, true);

            $price = $DB->get_field('tool_products', 'price', ['id' => $productparams['product']]);

            $formparams = ['tablename' => $data->tablename, 'fieldname' => $data->fieldname, 'fieldid' => $data->fieldid, 'parentfieldid' => $data->parentfieldid, 'selectedseats' => $data->selectedseats, 'sesskey' => sesskey(), 'quantity' => $data->selectedseats, 'product_id' => $productparams['product'], 'total' => $price];

            $params['items'][0] = array_merge($formparams, $productparams);
            $params['formdata'] = $formparams;
            $params['total'] = $price * $data->selectedseats;
            $params['total_price'] = $price;
            $params['total_discount'] = 0;
            $params['taxes'] = 0;

            $buttons = $OUTPUT->render_from_template('tool_product/add_order_seats_loader', $params);

            return ['returnparams' => base64_encode(serialize($params))];
        }

        return null;
    }

    /**
     * Load in existing data as form defaults
     */
    public function set_data_for_dynamic_submission(): void {
        global $DB;


        $seatsdata=$this->_ajaxformdata['seatsdata'];

        if(isset($seatsdata) && !empty($seatsdata)){

            $data=(object)unserialize(base64_decode($seatsdata));

            $data->availableseats=$this->check_product_availableseats($data);


            // if($data->tablename != 'tp_offerings'){

            //     $purchasedseats=(new \tool_product\product)->purchasedseats_check($data->tablename, $data->fieldname, $data->fieldid);

            //     if($purchasedseats){

            //         $data->availableseats=$data->availableseats-$purchasedseats;

            //     }
            // }



        } else {

            $data = $this->_ajaxformdata;

            $data->availableseats = $this->check_product_availableseats($data);
        }
        $data->autoapproval=$this->_ajaxformdata['autoapproval'];

        $this->set_data($data);
    }

    /**
     * Returns url to set in $PAGE->set_url() when form is being rendered or submitted via AJAX
     *
     * @return moodle_url
     */
     protected function get_page_url_for_dynamic_submission(): \moodle_url {

        $params = ['tablename' => $data->tablename,'fieldname' => $data->fieldname,'fieldid' => $data->fieldid,'parentfieldid' => $data->parentfieldid,'selectedseats' => $data->selectedseats];
        $url = new moodle_url('/admin/tool/product/checkout.php', $params);

        return  $url;
    }

    public function check_product_availableseats($inputdata){

        global $DB;


        $availableseats=$inputdata->availableseats;

        if($inputdata->tablename == 'local_events'){

            $eventmethod = $DB->get_field('local_events','method', ['id' => $inputdata->fieldid]);

            if($eventmethod == 1){
                $availableseats=-9999;
            }

        }

        return $availableseats;

    }
}
