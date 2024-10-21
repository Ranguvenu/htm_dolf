<?php
namespace local_learningtracks\form;

use core_form\dynamic_form ;
use moodle_url;
use context;
use context_system;
use local_learningtracks;
use local_learningtracks\learningtracks as lt;
use local_learningtracks_external;

class learningtrackform extends dynamic_form{

    /** @var profile_define_base $field */
    public $field;
    /** @var \stdClass */
    protected $fieldrecord;

    /**
     * Define the form
     */
    public function definition () {
        global $CFG, $DB;
        $mform = $this->_form;

        $id = $this->optional_param('id', 0, PARAM_INT);
       
        $mform->addElement('hidden', 'id', $id, array('id' => 'id'));
        $mform->setType('id', PARAM_INT);

        $mform->addElement('text','name', get_string('name', 'local_learningtracks'),'maxlength="254" size="50"');
        $mform->addRule('name', get_string('missingname','local_learningtracks'), 'required', null, 'server');
        $mform->setType('name', PARAM_TEXT);

        $mform->addElement('text','namearabic', get_string('namearabic', 'local_learningtracks'),'maxlength="254" size="50"');
        $mform->addRule('namearabic', get_string('missingnamearabic','local_learningtracks'), 'required', null, 'server');
        $mform->setType('namearabic', PARAM_TEXT);
        
        $mform->addElement('text','code', get_string('lpcode', 'local_learningtracks'),'maxlength="254" size="50"');
        $mform->addRule('code', get_string('missinglpcode', 'local_learningtracks'), 'required', null, 'server');
        $mform->setType('code', PARAM_TEXT);
        $competencies = array();
        $competencylist = $this->_ajaxformdata['competency'];
        if (!empty($competencylist)) {
            $competencies = lt::track_competency($competencylist, $id);
        } else if ($id > 0) {
            $competencies =  lt::track_competency(array(), $id);
        }
        $competencyoptions = array(
            'ajax' => 'local_learningtracks/form_selector_datasource',
            'data-type' => 'competency',
            'id' => 'el_competency',
            'data-comp' => '',
            'multiple' => true,
            'noselectionstring' => get_string('selectcompetency', 'local_learningtracks'),
        );
        $selectOrg = [0 => get_string('all', 'local_learningtracks')];
        $mform->addElement('autocomplete','competency', get_string('mapcompetency', 'local_learningtracks'), $competencies, $competencyoptions);
        $mform->addRule('competency', get_string('pleaseselectcompetency', 'local_learningtracks'), 'required', null, 'server');

        $mform->addElement('editor','description', get_string('description', 'local_learningtracks'));
        $mform->addRule('description', get_string('required'), 'required', null, 'server');
        $mform->addHelpButton('idnumber', 'idnumbergrouping');
        $mform->setType('idnumber', PARAM_RAW);


        $filemanageroptions = array(
            'accepted_types' => array(get_string('jpg','local_events'), get_string('png','local_events')),
            'maxbytes' => 0,
            'maxfiles' => 1,
        );
        $mform->addElement('filemanager', 'logo', get_string('logo', 'local_learningtracks'), '', $filemanageroptions);
        $mform->addRule('logo', get_string('required'), 'required', null, 'server');
        $mform->setType('logo', PARAM_RAW);

        $selectOrg = [0 => get_string('all', 'local_learningtracks')];
        $organization = array();
        $organizationlist = $this->_ajaxformdata['organization'];
        if (!empty($organizationlist) || $organizationlist == '0') {
            $organization = $selectOrg + lt::track_organizations(array($organizationlist), $id);
        } else if ($id > 0) {
            $organization = $selectOrg + lt::track_organizations(array(), $id);
        } else {
            $organization = $selectOrg;
        }
        $selectOrg = [0 => get_string('all', 'local_learningtracks')];
        $options = array(
            'ajax' => 'local_learningtracks/form_selector_datasource',
            'data-type' => 'orglist',
            'id' => 'el_organization',
            'data-orgid' => '',
            'multiple' => false,
        );
        $mform->addElement('autocomplete','organization', get_string('organization', 'local_learningtracks'), $organization, $options);
        
    }

        /**
     * Perform some moodle validation.
     *
     * @param array $datas
     * @param array $files
     * @return array
     */
    public function validation($data, $files) {
        global $DB;
        $errors = parent::validation($data, $files);
        if ($track = $DB->get_record('local_learningtracks', array('code' => $data['code']), '*', IGNORE_MULTIPLE)) {
            if (empty($data['id']) || $track->id != $data['id']) {
                $errors['code'] = get_string('shortnametaken', 'local_learningtracks', $track->name);
            }
        }
        if(empty($data['competency'])) {
            $errors['competency'] = get_string('pleaseselectcompetency', 'local_learningtracks');
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
        require_capability('moodle/site:config', $this->get_context_for_dynamic_submission());
    }

    /**
     * Process the form submission, used if form was submitted via AJAX
     */
    public function process_dynamic_submission() {
        global $CFG, $DB;
        $data = $this->get_data();
        (new local_learningtracks\learningtracks)->add_update_track($data);
    
    }

    /**
     * Load in existing data as form defaults
     */
    public function set_data_for_dynamic_submission(): void {
        global $DB;
        if ($id = $this->optional_param('id', 0, PARAM_INT)) {
            $data = (new local_learningtracks\learningtracks)->set_data($id);
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
        return new moodle_url('/local/learningtracks/index.php',
            [ 'id' => $id]);
    }
}