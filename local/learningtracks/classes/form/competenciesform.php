<?php
namespace local_learningtracks\form;

use core_form\dynamic_form;
use moodle_url;
use context;
use context_system;
use local_learningtracks;
use local_learningtracks\learningtracks as lt;

class competenciesform extends dynamic_form { 
    public function definition() {
        global $USER, $CFG,$DB;
        $corecomponent = new \core_component();
        $id = $this->optional_param('id', 0, PARAM_INT);
        $mform = $this->_form;
        $mform->addElement('hidden', 'id', $id);
        $mform->setType('id',PARAM_INT);
        $select = [null => get_string('selectcompetency','local_learningtracks')];
   
        $competencies = array();
        $competencylist = $this->_ajaxformdata['competency'];
        if (!empty($competencylist)) {
            $competencies = lt::track_competency(array($competencylist), $id);
        } else if ($id > 0) {
            $competencies = lt::track_competency(array(), $id);
        }
        $competencyoptions = array(
            'ajax' => 'local_learningtracks/form_selector_datasource',
            'data-type' => 'competency',
            'id' => 'el_competency',
            'data-comp' => '',
            'multiple' => true,
        );

        $mform->addElement('autocomplete','competency', get_string('competency','local_learningtracks'), $competencies, $competencyoptions);
    }
    /**
     * Perform some moodle validation.
     * @param array $data
     * @param array $files
     * @return array
     */
    public function validation($data, $files) {

        global $DB;

        $errors = parent::validation($data, $files);

        //print_r($data);

        if(isset($data['competency']) && empty(array_filter($data['competency']))){

            $errors['competency'] = get_string('valcompetencyrequired','local_learningtracks');

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
        (new local_learningtracks\learningtracks)->update_competency($data);
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
            ['id' => $id]);
    }    
}
