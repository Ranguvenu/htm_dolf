<?php
namespace local_trainingprogram\form;
use core_form\dynamic_form ;
use moodle_url;
use context;
use context_system;
use local_trainingprogram\local\trainingprogram as tp;
use coding_exception;
use MoodleQuickForm_autocomplete;

class coupon_mail_form extends dynamic_form { 
    public function definition() {
        global $USER, $CFG,$DB;

        $corecomponent = new \core_component();
        $mform = $this->_form;
        $couponid = $this->optional_param('couponid', 0, PARAM_INT);
        $couponcode = $this->optional_param('couponcode', 0, PARAM_RAW);
        $systemcontext = context_system::instance();

        $mform->addElement('hidden', 'couponid', $couponid);
        $mform->setType('couponid',PARAM_INT);

        $mform->addElement('hidden', 'couponcode', $couponcode);
        $mform->setType('couponcode',PARAM_RAW);

        $mform->addElement('static', 'coupon_code', get_string('code', 'local_trainingprogram'));
        $mform->setDefault('coupon_code',$couponcode);

        $attributes = array('0' => get_string('internal_users','local_trainingprogram'),'1' => get_string('external_users','local_trainingprogram'));
	    $radioarray=array();
	    $radioarray[] = $mform->createElement('radio', 'usertype','', get_string('internal_user','local_trainingprogram'), 0, $attributes);
	    $radioarray[] = $mform->createElement('radio', 'usertype', '', get_string('external_user','local_trainingprogram'), 1, $attributes);
        $mform->addGroup($radioarray, 'usertype',get_string('usertype','local_trainingprogram'), array('class' => 'usertype'), false);

        // $fullname = tp::user_fullname_case();
        // $users=$DB->get_records_sql("SELECT u.id,$fullname  FROM {user} AS u JOIN {local_users} lc ON lc.userid = u.id  WHERE u.id NOT IN (1,2)" );
        // $options=[];
        // $options[null]=get_string('selectuseremail','local_trainingprogram');
        // foreach ($users AS $user){
        //     $options[$user->id]=$user->fullname;
        // }
        // $mform->addElement('select', 'internaluser', get_string('email', 'local_trainingprogram'), $options);
        // $mform->hideIf('internaluser',  'usertype',  'eq', 1);

        $userattributes = array(
            'ajax' => 'local_organization/organization_datasource',
            'data-type' => 'all_users',
            'class' => 'all_users',
            'id' => 'all_users',
            'data-org' => 1,
            'multiple' => false,
            );
        $mform->addElement('autocomplete', 'internaluser', get_string('email', 'local_trainingprogram'),[], $userattributes);
        $mform->hideIf('internaluser',  'usertype',  'eq', 1);

        $mform->addElement('text', 'email', get_string('email', 'local_trainingprogram'));
        $mform->setType('email', PARAM_RAW);
        $mform->hideIf('email',  'usertype',  'eq',  0);

        $mform->addElement('text', 'name', get_string('name', 'local_trainingprogram'));
        $mform->setType('name', PARAM_RAW);
        $mform->hideIf('name',  'usertype',  'eq',  0);

    }
    public function validation($data, $files) {
        $errors = array();
        global $DB, $CFG;


        if($data['usertype'] == 0) {
                   
               if(empty($data['internaluser']))  {

               	   $errors['internaluser'] = get_string('required');
               }  

        } else {

        	if(empty($data['email']))  {

               	$errors['email'] = get_string('required');
            } 
            if (!empty($data['email']) && !validate_email($data['email'])) {

                $errors['email'] = get_string('requiredvalidemail', 'local_trainingprogram');
            }

            if(empty($data['name']))  {

               	$errors['name'] = get_string('required');
            } 


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
         has_capability('local/trainingprogram:manage', $this->get_context_for_dynamic_submission()) 
        || has_capability('local/organization:manage_trainingofficial', $this->get_context_for_dynamic_submission());
    }

    /**
     * Process the form submission, used if form was submitted via AJAX
     */
    public function process_dynamic_submission() {
        global $CFG, $DB;
        require_once($CFG->dirroot.'/user/profile/definelib.php');
        $data = $this->get_data();
        (new tp)->send_coupon_mail($data);
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
        $id = $this->optional_param('id', 0, PARAM_INT);
        return new moodle_url('/local/trainingprogram/discount_management.php');
    }    
}
