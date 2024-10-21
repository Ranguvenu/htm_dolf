<?php
// This file is part of Moodle - http://moodle.org/
//
// Moodle is free software: you can redistribute it and/or modify
// it under the terms of the GNU General Public License as published by
// the Free Software Foundation, either version 3 of the License, or
// (at your option) any later version.
//
// Moodle is distributed in the hope that it will be useful,
// but WITHOUT ANY WARRANTY; without even the implied warranty of
// MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
// GNU General Public License for more details.
//
// You should have received a copy of the GNU General Public License
// along with Moodle.  If not, see <http://www.gnu.org/licenses/>.

namespace local_competency\form;

use context;
use context_system;
use moodle_exception;
use moodle_url;
use core_form\dynamic_form;
use local_competency\competency;
use local_competency\external;
use html_writer;
/**
 * Competency modal form
 *
 * @package    local_competency
 * @copyright  2022 e abyas  <info@eabyas.com>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class competencyobjective_form extends dynamic_form {

    /**
     * Form definition
     */
    protected function definition() {
        global $CFG,$OUTPUT,$PAGE;

        $mform = $this->_form;

        $systemcontext = context_system::instance();

        $competency=$this->optional_param('competency', 0, PARAM_INT);

        $competencypc=$this->optional_param('competencypc', 0, PARAM_INT);

        $mform->addElement('hidden', 'id');
        $mform->setType('id', PARAM_INT);

        $mform->addElement('hidden', 'competency');
        $mform->setDefault('competency',$competency);
        $mform->setType('competency', PARAM_INT);


        $mform->addElement('hidden', 'competencypc');
        $mform->setDefault('competencypc',$competencypc);
        $mform->setType('competencypc', PARAM_INT);

        if((has_capability('local/competency:managecompetencyobjectives', $systemcontext)) || (has_capability('local/competency:viewcompetencyobjectives', $systemcontext))){

            $tabs=array(['active' =>'active','type'=>'exams','name'=>get_string('learningcontentexam', 'local_competency')],['active' =>'','type'=>'trainingprograms','name'=>get_string('learningcontenttrainingprogram', 'local_competency')],['active' =>'','type'=>'questions','name'=>get_string('learningcontentquestion', 'local_competency')],/*['active' =>'','type'=>'levels','name'=>get_string('learningcontentlevels', 'local_competency')]*/);

            $mform->addElement('html',html_writer::tag('div',$OUTPUT->render_from_template('local_competency/learningcontent_display',array('tabs'=>$tabs,'competencyid'=>$competencypc,'contenttype'=>'competencypc')),array('id'=>'targetcompetencyobj'.$competencypc.'')));
        }

        $mform->addElement('html',html_writer::tag('h5',get_string('linkexamsprogramsquestions', 'local_competency')));

        $competencyjobrolelevels =array();

        if($this->_ajaxformdata['objjobrolelevels']){

            $competencyobjjobrolelevels = competency::competency_const_objjobrolelevels($this->_ajaxformdata['objjobrolelevels']);

        }  

        $competencyobjjobrolelevelsoptions = [
                'ajax' => 'local_competency/form_competency_selector',
                'data-action' => 'competency_obj_jobrolelevels',
                'data-parentid'=>$competency,
                'data-parentchildid'=>$competencypc,
                'multiple' => true
        ];
 
        /*$mform->addElement('autocomplete', 'objjobrolelevels', get_string('objjobrolelevels', 'local_competency'),$competencyobjjobrolelevels,$competencyobjjobrolelevelsoptions);
        $mform->setType('objjobrolelevels', PARAM_ALPHANUMEXT);*/
       


        $competencyobjexams =array();

        if($this->_ajaxformdata['objexams']){

            $competencyobjexams = competency::competency_const_exams($this->_ajaxformdata['objexams']);

        }  

        $competencyobjexamsoptions = [
                'ajax' => 'local_competency/form_competency_selector',
                'data-action' => 'competency_obj_exams',
                'data-parentid'=>$competency,
                'data-parentchildid'=>$competencypc,
                'multiple' => true
        ];
 
        $mform->addElement('autocomplete', 'objexams', get_string('objexams', 'local_competency'),$competencyobjexams,$competencyobjexamsoptions);
        $mform->setType('objexams', PARAM_ALPHANUMEXT);

        $competencyobjtrainingprograms =array();

        if($this->_ajaxformdata['objtrainingprograms']){

            $competencyobjtrainingprograms = competency::competency_const_trainingprograms($this->_ajaxformdata['objtrainingprograms']);

        }  

        $competencyobjtrainingprogramsoptions = [
                'ajax' => 'local_competency/form_competency_selector',
                'data-action' => 'competency_obj_trainingprograms',
                'data-parentid'=>$competency,
                'data-parentchildid'=>$competencypc,
                'multiple' => true
        ];
 
        $mform->addElement('autocomplete', 'objtrainingprograms', get_string('objtrainingprograms', 'local_competency'),$competencyobjtrainingprograms,$competencyobjtrainingprogramsoptions);
        $mform->setType('objtrainingprograms', PARAM_ALPHANUMEXT);


        $competencyobjquestions =array();

        if($this->_ajaxformdata['objquestions']){

            $competencyobjquestions = competency::competency_const_questions($this->_ajaxformdata['objquestions']);

        }  

        $competencyobjquestionsoptions = [
                'ajax' => 'local_competency/form_competency_selector',
                'data-action' => 'competency_obj_questions',
                'data-parentid'=>$competency,
                'data-parentchildid'=>$competencypc,
                'multiple' => true
        ];
 
        $mform->addElement('autocomplete', 'objquestions', get_string('objquestions', 'local_competency'),$competencyobjquestions,$competencyobjquestionsoptions);
        $mform->setType('objquestions', PARAM_ALPHANUMEXT);
            
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

        return $errors;
    }

    /**
     * Return form context
     *
     * @return context
     */
    protected function get_context_for_dynamic_submission(): context {
        return \context_system::instance();
    }

    /**
     * Check if current user has access to this form, otherwise throw exception
     *
     * @throws moodle_exception
     */
    protected function check_access_for_dynamic_submission(): void {
        if (!competency::can_competencyobjective_datasubmit()) {
            throw new moodle_exception('errorcompetencyobjectivedisabled', 'local_competency');
        }
    }
    /**
     * Process the form submission, used if form was submitted via AJAX
     *
     * @return array
     */
    public function process_dynamic_submission() {

        return competency::competencyobjective_datasubmit($this->get_data());
    }

    /**
     * Load in existing data as form defaults (not applicable)
     */
    public function set_data_for_dynamic_submission(): void {

        if ($id = $this->optional_param('id', 0, PARAM_INT)) {

            $stable = new \stdClass();
            $stable->id = $id;
            $stable->competency = $competency;
            $stable->competencypc = $competencypc;
            $stable->thead = false;
            $stable->start = 0;
            $stable->length = 1;
            $data=competency::get_competency_objective($stable);

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

        return new moodle_url('/competency/index.php', ['id' => $id]);
    }
}
