<?php
namespace tool_product\form;
use core_form\dynamic_form ;
use moodle_url;
use context;
use context_system;
use coding_exception;
use MoodleQuickForm_autocomplete;

class sendemailform extends dynamic_form { 
    public function definition() {
        global $USER, $CFG,$DB;

        $corecomponent = new \core_component();
        $mform = $this->_form;
        $productid = $this->optional_param('productid',0,PARAM_INT);

        //echo "productid ".$productid ;


        $mform->addElement('hidden', 'productid', $productid);
        $mform->setType('productid',PARAM_INT);

        $record = $DB->get_record('tool_org_order_payments',array('id' => $productid));
        $lang  = current_language();
        if( $lang == 'ar'){

            $orgfullname='org.fullnameinarabic as orgname';

        }else{

            $orgfullname='org.fullname as orgname';
        }

        $fullname = (new \local_trainingprogram\local\trainingprogram())->user_fullname_case();
        $sql="SELECT u.id,$fullname,u.email 
            FROM {user} AS u 
            JOIN {local_users} lc ON lc.userid = u.id
            WHERE  u.id=:orguserid ";

        $user= $DB->get_record_sql($sql,array('orguserid'=>$record->orguserid));

        $touseremail =$user->email.'('.$user->fullname.')'; 

        $mform->addElement('static', 'sender_email', get_string('sender_email', 'local_organization'));
        $mform->setDefault('sender_email', $USER->email);


        $mform->addElement('static', 'touser', get_string('touser', 'tool_product'), $touseremail);

        $mform->addElement('text', 'subject', get_string('subject', 'tool_product'),'size="100" ');
        $mform->addRule('subject', get_string('subjectrequired','tool_product'), 'required');
        $mform->setType('subject', PARAM_RAW);

        $mform->addElement('editor','message', get_string('message', 'tool_product'));
        $mform->addRule('message', get_string('messagerequired','tool_product'), 'required');
        $mform->setType('message', PARAM_RAW);

        $mform->addElement('hidden', 'to_email', $user->email);

        $mform->addElement('hidden', 'organization',$user->orgname);


    }
    public function validation($data, $files) {
        $errors = array();
        global $DB, $CFG;
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
        //require_capability('moodle/site:config', $this->get_context_for_dynamic_submission());
     has_capability('local/organization:manage_communication_officer', $this->get_context_for_dynamic_submission())  ||  has_capability('local/organization:manage_financial_manager', $this->get_context_for_dynamic_submission()) ;
    }

    /**
     * Process the form submission, used if form was submitted via AJAX
     */
    public function process_dynamic_submission() {
        global $CFG, $DB;
        require_once($CFG->dirroot.'/user/profile/definelib.php');
        $data = $this->get_data();
        (new \tool_product\product)::sendemail_to_orgofficial($data);
    }

    /**
     * Load in existing data as form defaults
     */
    public function set_data_for_dynamic_submission(): void {
        global $DB;

    }

    /**
     * Returns url to set in $PAGE->set_url() when form is being rendered or submitted via AJAX
     *
     * @return moodle_url
     */
    protected function get_page_url_for_dynamic_submission(): moodle_url {
        $productid = $this->optional_param('productid', 0, PARAM_INT);
        return new moodle_url('/admin/tool/product/financialpayments.php');
    }    
}
