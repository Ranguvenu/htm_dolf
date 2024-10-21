<?php
namespace local_trainingprogram\form;
use core_form\dynamic_form ;
use moodle_url;
use context;
use context_system;
use local_trainingprogram\local\refundsettings as refundsetting;
require_once($CFG->libdir . '/formslib.php');

class refundsettingsform extends dynamic_form { 
    public function definition() {
        global $USER, $CFG,$DB;

        $mform = $this->_form;
        $id = $this->optional_param('id', 0, PARAM_RAW);
        $systemcontext = context_system::instance();

        $mform->addElement('hidden', 'id', $id);
        $mform->setType('id',PARAM_INT);

        $reundentitytype = [];
        $reundentitytype['trainingprogram'] = get_string('trainingprogram','local_trainingprogram');
        $reundentitytype['exam'] = get_string('exam','local_trainingprogram');
        $reundentitytype['event'] = get_string('event','local_trainingprogram');

        $mform->addElement('select', 'entitytype',get_string('refund_entitytype','local_trainingprogram'),$reundentitytype);

        $reundtype = [];
        $reundtype['cancel'] = get_string('cancel_offering','local_trainingprogram');
        $reundtype['replace'] = get_string('replace','local_trainingprogram');
        $reundtype['reschedule'] = get_string('reschedule','local_trainingprogram');

        $mform->addElement('select', 'type',get_string('setting_type','local_trainingprogram'),$reundtype);

        $mform->addElement('advcheckbox', 'ownedbycisi', get_string('ownedby', 'local_trainingprogram'), get_string('cisi', 'local_trainingprogram'));
        $mform->setDefault('ownedbycisi', 0);
        $mform->hideIf('ownedbycisi', 'entitytype', 'neq', 'exam');
        $mform->hideIf('ownedbycisi', 'type', 'neq', 'reschedule');

        $attemptgroup=array();
        $attemptgroup[] =& $mform->createElement('radio', 'moreattempts', '',get_string('firstattempt', 'local_trainingprogram'), 0);
        $attemptgroup[] =& $mform->createElement('radio', 'moreattempts', '',get_string('morethanfirst', 'local_trainingprogram'), 1);
        $mform->addGroup($attemptgroup, 'entityattempt', get_string('attempt', 'local_trainingprogram'), '&nbsp&nbsp', false);
        $mform->hideif('entityattempt', 'ownedbycisi', 'neq', 1);

        $mform->addElement('text', 'dayfrom', get_string('refund_dayfrom','local_trainingprogram'));
        $mform->addRule('dayfrom',get_string('required_field','local_trainingprogram'), 'required');
       $mform->addRule('dayfrom',get_string('acceptsnumeric','local_trainingprogram'), 'numeric');

        $mform->addElement('text', 'dayto', get_string('refund_dayto','local_trainingprogram'));
        $mform->addRule('dayto',get_string('required_field','local_trainingprogram'), 'required');
        $mform->addRule('dayto',get_string('acceptsnumeric','local_trainingprogram'), 'numeric');


        $debtypegroup=array();
        $debtypegroup[] =& $mform->createElement('radio', 'dedtype', '',get_string('attendancepercnt', 'local_trainingprogram'), refundsetting::PERCENTAGE);
        $debtypegroup[] =& $mform->createElement('radio', 'dedtype', '',get_string('amount', 'local_trainingprogram'), refundsetting::AMOUNT);
        $mform->addGroup($debtypegroup, 'dtype', get_string('price', 'local_trainingprogram'), '&nbsp&nbsp', false);
        $mform->setDefault('dedtype', 0);

        $mform->addElement('text', 'dedpercentage', get_string('dedpercentage','local_trainingprogram'));
        $mform->hideif('dedpercentage', 'dedtype', 'eq', 1);

        $mform->addElement('text', 'dedamount', get_string('dedamount','local_trainingprogram'));
        $mform->hideIf('dedamount','dedtype','eq',0);
        

    }
    public function validation($data, $files) {
        $errors = array();
        global $DB, $CFG;

      
        // if(empty(trim($data['dedamount'])) && $data['dedtype'] == 1) {
        //     $errors['dedamount'] = get_string('dedamountcannotbeempty', 'local_trainingprogram');
        // } 

        // if(empty(trim($data['dedpercentage'])) && $data['dedtype'] == 0) {
        //     $errors['dedpercentage'] = get_string('dedpercentagecannotbeempty', 'local_trainingprogram');
        // } 

        if(!preg_match('/^[0-9]*$/',trim($data['dedamount']))) {
            $errors['dedamount'] = get_string('validseatsrequired', 'local_trainingprogram'); 
        }

        if(!preg_match('/^[0-9]*$/',trim($data['dedpercentage']))) {
            $errors['dedpercentage'] = get_string('validseatsrequired', 'local_trainingprogram'); 
        }

        if(trim($data['dayfrom']) > trim($data['dayto'])) {
            $errors['dayfrom'] = get_string('dayfromcannotexceeddayto', 'local_trainingprogram'); 
        }

        if(trim($data['dayfrom'])  == trim($data['dayto'])) {
            $errors['dayto'] = get_string('daytomustbehigher', 'local_trainingprogram'); 
        }


        


        return $errors;
    }
    /**
     * Returns context where this form is used
     *
     * @return context
     */
    protected function get_context_for_dynamic_submission(): context {
        return context_system::instance();
    }

    /**
     * Checks if current user has access to this form, otherwise throws exception
     */
    protected function check_access_for_dynamic_submission(): void {
        //require_capability('moodle/site:config', $this->get_context_for_dynamic_submission());
       has_capability('local/trainingprogram:manage_refundsettings', $this->get_context_for_dynamic_submission());
    }

    /**
     * Process the form submission, used if form was submitted via AJAX
     */
    public function process_dynamic_submission() {
        global $CFG, $DB;
        require_once($CFG->dirroot.'/user/profile/definelib.php');
        $data = $this->get_data();
        ($data->id > 0) ? (new refundsetting)::update_setting($data)  :  (new refundsetting)::create_setting($data)  ;
    }
    /**
     * Load in existing data as form defaults
     */
    public function set_data_for_dynamic_submission(): void {
              global $DB;

        if ($id = $this->optional_param('id', 0, PARAM_INT)) {
            
            $data=$DB->get_record('refund_settings',array ('id' =>$id));
            $this->set_data($data);
        }
    }
    /**
     * Returns url to set in $PAGE->set_url() when form is being rendered or submitted via AJAX
     *
     * @return moodle_url
     */
    protected function get_page_url_for_dynamic_submission(): moodle_url {
        $id = $this->optional_param('id', 0, PARAM_INT);
        return new moodle_url('/local/trainingprogram/viewrefundsettings.php');
    }    
}
