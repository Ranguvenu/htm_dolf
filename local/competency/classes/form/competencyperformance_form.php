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
/**
 * Competency modal form
 *
 * @package    local_competency
 * @copyright  2022 e abyas  <info@eabyas.com>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class competencyperformance_form extends dynamic_form {

    /**
     * Form definition
     */
    protected function definition() {
        global $CFG;

        $mform = $this->_form;

        $competency=$this->optional_param('competency', 0, PARAM_INT);

        $id = $this->optional_param('id', 0, PARAM_INT);

        $mform->addElement('hidden', 'id');
        $mform->setType('id', PARAM_INT);

        $mform->addElement('hidden', 'competency');
        $mform->setDefault('competency',$competency);
        $mform->setType('competency', PARAM_INT);


        $performancecriterias =array();

        if($this->_ajaxformdata['criterianame'] || $id > 0){

            $performancecriterias = competency::constperformancecriterias();

        }  

        $competencycriteriaoptions = [
                'ajax' => 'local_competency/form_competency_selector',
                'data-action' => 'competency_criteria',
        ];
 
        $mform->addElement('autocomplete', 'criterianame', get_string('competency_criteria', 'local_competency'),$performancecriterias,$competencycriteriaoptions);
        $mform->setType('criterianame', PARAM_ALPHANUMEXT);
        $mform->addRule('criterianame', null, 'required', null);

        $mform->addElement('text', 'add_criterianame', get_string('titleeng', 'local_competency'), array());
        $mform->setType('add_criterianame', PARAM_TEXT);
        
        $mform->hideif('add_criterianame', 'criterianame', 'neq', competency::OTHER);


        $mform->addElement('text', 'criterianamearabic', get_string('titlearabic', 'local_competency'), array());
        $mform->setType('criterianamearabic', PARAM_TEXT);
        $mform->hideif('criterianamearabic', 'criterianame', 'neq', competency::OTHER);


        $kpis =array();

        if($this->_ajaxformdata['kpiname'] || $id > 0){

            $kpis = competency::constkpis();

        }

        $competencykpioptions = [
                'ajax' => 'local_competency/form_competency_selector',
                'data-action' => 'competency_kpi',
        ];
 
        $mform->addElement('autocomplete', 'kpiname', get_string('competency_kpi', 'local_competency'),$kpis,$competencykpioptions);
        $mform->setType('kpiname', PARAM_ALPHANUMEXT);
        $mform->addRule('kpiname', null, 'required', null);

        $mform->addElement('text', 'add_kpiname', get_string('titleeng', 'local_competency'), array());
        $mform->setType('add_kpiname', PARAM_TEXT);
        $mform->hideif('add_kpiname', 'kpiname', 'neq', competency::OTHER);

        $mform->addElement('text', 'kpinamearabic', get_string('titlearabic', 'local_competency'), array());
        $mform->setType('kpinamearabic', PARAM_TEXT);
        $mform->hideif('kpinamearabic', 'kpiname', 'neq', competency::OTHER);



        $objectives =array();

        if($this->_ajaxformdata['objectiveid'] || $id > 0){

            $objectives = competency::constobjectives();

        }
        
        $competencyobjectiveoptions = [
                'ajax' => 'local_competency/form_competency_selector',
                'data-action' => 'competency_objective',
        ];

        $mform->addElement('autocomplete', 'objectiveid', get_string('competency_objective', 'local_competency'),$objectives,$competencyobjectiveoptions);
        $mform->setType('objectiveid', PARAM_ALPHANUMEXT);
        $mform->addRule('objectiveid', null, 'required', null);

        $mform->addElement('text', 'add_objectiveid', get_string('titleeng', 'local_competency'), array());
        $mform->setType('add_objectiveid', PARAM_TEXT);
        $mform->hideif('add_objectiveid', 'objectiveid', 'neq', competency::OTHER);

        $mform->addElement('text', 'objectiveidarabic', get_string('titlearabic', 'local_competency'), array());
        $mform->setType('objectiveidarabic', PARAM_TEXT);
        $mform->hideif('objectiveidarabic', 'objectiveid', 'neq', competency::OTHER);
            
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

        $criterianame=$data['criterianame'];

        $kpiname=$data['kpiname'];

        $objectiveid=$data['objectiveid'];


        if(isset($data['criterianame']) && empty(trim($data['criterianame']))){

            $errors['criterianame'] = get_string('valcriterianamerequired','local_competency');

        }elseif((isset($data['criterianame']) && $data['criterianame'] === competency::OTHER) && (isset($data['add_criterianame']) && empty(trim($data['add_criterianame'])))){

            $errors['add_criterianame'] = get_string('valaddcriterianamerequired','local_competency');

        }elseif((isset($data['criterianame']) && $data['criterianame'] === competency::OTHER) && (isset($data['add_criterianame']) && !empty(trim($data['add_criterianame'])))){

                $sql = "SELECT id FROM {local_competency_pc}
                    WHERE ".$DB->sql_compare_text('criterianame')." = ".$DB->sql_compare_text('?')."";

            if ($competencypc = $DB->get_record_sql($sql, array($data['add_criterianame']), IGNORE_MULTIPLE)) {

                if (empty($data['id']) || $competencypc->id != $data['id']) {
                    $errors['add_criterianame'] = get_string('addcriterianametaken', 'local_competency');
                }
            }

             $criterianame=$data['add_criterianame'];

        }

        if(isset($data['kpiname']) && empty(trim($data['kpiname']))){

            $errors['kpiname'] = get_string('valkpinamerequired','local_competency');

        }elseif((isset($data['kpiname']) && $data['kpiname'] === competency::OTHER) && (isset($data['add_kpiname']) && empty(trim($data['add_kpiname'])))){

            $errors['add_kpiname'] = get_string('valaddkpinamerequired','local_competency');

        }elseif((isset($data['kpiname']) && $data['kpiname'] === competency::OTHER) && (isset($data['add_kpiname']) && !empty(trim($data['add_kpiname'])))){

                $sql = "SELECT id FROM {local_competency_pc}
                    WHERE ".$DB->sql_compare_text('kpiname')." = ".$DB->sql_compare_text('?')."";

            if ($competencypckpi = $DB->get_record_sql($sql, array($data['add_kpiname']), IGNORE_MULTIPLE)) {

                if (empty($data['id']) || $competencypckpi->id != $data['id']) {
                    $errors['add_kpiname'] = get_string('addkpinametaken', 'local_competency');
                }
            }

            $kpiname=$data['add_kpiname'];
        }

        if(isset($data['objectiveid']) && empty(trim($data['objectiveid']))){

            $errors['objectiveid'] = get_string('valobjectiveidrequired','local_competency');

        }elseif((isset($data['objectiveid']) && $data['objectiveid'] === competency::OTHER) && (isset($data['add_objectiveid']) && empty(trim($data['add_objectiveid'])))){

            $errors['add_objectiveid'] = get_string('valaddobjectiveidrequired','local_competency');

        }elseif((isset($data['objectiveid']) && $data['objectiveid'] === competency::OTHER) && (isset($data['add_objectiveid']) && !empty(trim($data['add_objectiveid'])))){

                $sql = "SELECT id FROM {local_competency_pc}
                    WHERE ".$DB->sql_compare_text('objectiveid')." = ".$DB->sql_compare_text('?')."";

            if ($competencypcobjectiveid = $DB->get_record_sql($sql, array($data['add_objectiveid']), IGNORE_MULTIPLE)) {

                if (empty($data['id']) || $competencypcobjectiveid->id != $data['id']) {
                    $errors['add_objectiveid'] = get_string('addobjectiveidtaken', 'local_competency');
                }
            }

            $objectiveid=$data['add_objectiveid'];

        }

        if(empty($errors)){

            $sql = "SELECT id FROM {local_competency_pc}
                    WHERE competency = ? AND ".$DB->sql_compare_text('criterianame')." = ".$DB->sql_compare_text('?')." AND ".$DB->sql_compare_text('kpiname')." = ".$DB->sql_compare_text('?')." AND ".$DB->sql_compare_text('objectiveid')." = ".$DB->sql_compare_text('?')."";

            // Add field validation check for duplicate code.
            if ($competencypc = $DB->get_record_sql($sql,  array($data['competency'],$criterianame,$kpiname,$objectiveid), 'id', IGNORE_MULTIPLE)) {

                if (empty($data['id']) || $competencypc->id != $data['id']) {
                    $errors['criterianame'] = get_string('competencypctaken', 'local_competency');
                }
            }

        }

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
        if (!competency::can_competencyperformance_datasubmit()) {
            throw new moodle_exception('errorcompetencyperformancedisabled', 'local_competency');
        }
    }
    /**
     * Process the form submission, used if form was submitted via AJAX
     *
     * @return array
     */
    public function process_dynamic_submission() {

        return competency::competencypc_datasubmit($this->get_data());
    }

    /**
     * Load in existing data as form defaults (not applicable)
     */
    public function set_data_for_dynamic_submission(): void {

        if ($id = $this->optional_param('id', 0, PARAM_INT)) {

            $stable = new \stdClass();
            $stable->id = $id;
            $stable->thead = false;
            $stable->start = 0;
            $stable->length = 1;
            $data=competency::get_competency_performances($stable);

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
