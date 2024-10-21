<?php
namespace local_exams\form;
use context_system;
use local_userapproval\action\manageuser;
defined('MOODLE_INTERNAL') || die();
require_once($CFG->libdir.'/formslib.php');
require_once($CFG->libdir.'/csvlib.class.php');
use csv_import_reader;
use core_text;
use moodleform;
use context_user;
use html_table;
use html_writer;

class bulkenrollment_form extends moodleform {
    public function definition() {
        global $USER,$DB;
        $corecomponent = new \core_component();
        $mform = $this->_form;
        $systemcontext = context_system::instance();
        $examid = $this->_customdata['examid'];
        $profileid = $this->_customdata['profileid'];

        $mform->addElement('hidden', 'examid', $examid);
        $mform->setType('examid',PARAM_INT);
        $mform->addElement('hidden', 'profileid', $profileid);
        $mform->setType('profileid',PARAM_INT);

        if(is_siteadmin() || has_capability('local/organization:manage_examofficial', $systemcontext)) {
            $organization = $this->_customdata['organization'];
            $organizations = array();
            if (!empty($organization)) {
                $organizations = manageuser::get_user_organization(array($organization),0);
            }
           $orgattributes = array(
            'ajax' => 'local_organization/organization_datasource',
            'data-type' => 'organization_list',
            'data-org' => 1,
            'multiple' => false,
            'id' => 'bulkorgselect',
            'class' => 'femptylabel',
            'placeholder' => get_string('selectorganisation','local_userapproval'),
            'onchange' => "(function(e){ require(['local_trainingprogram/dynamic_dropdown_ajaxdata'], function(s) {s.organizationchanged();}) }) (event)",

           );
            $mform->addElement('autocomplete','organization',get_string('organization','local_exams'),$organizations,$orgattributes);
            $mform->addRule('organization', get_string('selectorganization','local_exams'), 'required');
           
            $orgofficial = $this->_customdata['orgofficial'];
            $orgofficials= array();
            if (!empty($orgofficial)) {
                $orgofficials= manageuser::get_orgofficial($orgofficial);
            }
            $trattributes = array(
            'ajax' => 'local_trainingprogram/dynamic_dropdown_ajaxdata',
            'data-type' => 'orgofficial',
            'id' => 'program_users',
            'data-ctype' => 1,
            'multiple' => false,
            'data-programid' => $organization,
            'data-offeringid' => 0,
            );
            $mform->addElement('autocomplete', 'orgofficial', get_string('official', 'local_exams'),$orgofficials, $trattributes);
            $mform->addRule('orgofficial', get_string('selectorgofficial','local_exams'), 'required');
            $mform->hideIf('orgofficial', 'organization', 'eq', '');
        }
        if(!is_siteadmin() && has_capability('local/organization:manage_organizationofficial', $systemcontext)) {
            
            $organization = $DB->get_field('local_users','organization',['userid'=>$USER->id]);
            $organization  = ($organization) ? (int) $organization  : 0;

            $mform->addElement('hidden', 'organization', $organization);
            $mform->setType('organization',PARAM_INT);

            $mform->addElement('hidden', 'orgofficial', $USER->id);
            $mform->setType('orgofficial',PARAM_INT);
        }
        $filepickeroptions = array(
                    'accepted_types' => array(get_string('csv', 'local_exams')),
                    'maxbytes' => 0,
                    'maxfiles' => 1,
        );
        $mform->addElement('filepicker', 'enrollmentfile', get_string('enrollmentfile','local_exams'), null, $filepickeroptions);
        $mform->addRule('enrollmentfile', null, 'required', null);
        $mform->addHelpButton('enrollmentfile', 'uploaddoc', 'local_exams');

        $this->add_action_buttons(true, get_string('upload'));

    }

    public function validation($data, $files) {
        $errors = parent::validation($data, $files);
		//print_r($data);exit;
        return $errors;
    }
}

