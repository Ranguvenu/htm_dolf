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
class competencylevel_form extends dynamic_form {

    /**
     * Form definition
     */
    protected function definition() {
        global $CFG,$OUTPUT,$PAGE;

        $mform = $this->_form;

        $systemcontext = context_system::instance();

        $competencyid = $this->optional_param('competencyid', 0, PARAM_INT);

        $levelid = $this->optional_param('levelid','', PARAM_TEXT);

        $mform->addElement('hidden', 'id');
        $mform->setType('id', PARAM_INT);

        $mform->addElement('hidden', 'competencyid');
        $mform->setDefault('competencyid',$competencyid);
        $mform->setType('competencyid', PARAM_INT);


        $mform->addElement('hidden', 'levelid');
        $mform->setDefault('levelid',$levelid);
        $mform->setType('levelid', PARAM_TEXT);


        $mform->addElement('editor', 'description', get_string('competency_description', 'local_competency'), null);
        $mform->setType('description', PARAM_RAW);
     
            
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
        if (!competency::can_competencylevel_datasubmit()) {
            throw new moodle_exception('errorcompetencyleveldisabled', 'local_competency');
        }
    }
    /**
     * Process the form submission, used if form was submitted via AJAX
     *
     * @return array
     */
    public function process_dynamic_submission() {

        return competency::competencylevel_datasubmit($this->get_data());
    }

    /**
     * Load in existing data as form defaults (not applicable)
     */
    public function set_data_for_dynamic_submission(): void {

        if ($levelid = $this->optional_param('levelid','', PARAM_TEXT)) {

            $competencyid = $this->optional_param('competencyid', 0, PARAM_INT);

            $stable = new \stdClass();
            $stable->levelid = $levelid;
            $stable->competencyid = $competencyid;
            $stable->thead = false;
            $stable->start = 0;
            $stable->length = 1;

            $data=competency::get_competency_levelinfo($stable);

            $competencyld=$data['competencyld'];

            $competencyld->description = array('text' => $competencyld->description);


            $this->set_data($competencyld);
        }
    }

    /**
     * Returns url to set in $PAGE->set_url() when form is being rendered or submitted via AJAX
     *
     * @return moodle_url
     */
    protected function get_page_url_for_dynamic_submission(): moodle_url {

        $competencyid = $this->optional_param('competencyid', 0, PARAM_INT);

        return new moodle_url('/competency/index.php', ['id' => $competencyid]);
    }
}
