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


namespace customfield_course;

defined('MOODLE_INTERNAL') || die;
use stdClass;

class data_controller extends \core_customfield\data_controller {

    /**
     * Return the name of the field where the information is stored
     * @return string
     */
    public function datafield() : string {
        return 'charvalue';
    }

    /**
     * Returns the default value as it would be stored in the database (not in human-readable format).
     *
     * @return mixed
     */
    public function get_default_value() {
        // $defaultvalue = $this->get_field()->get_configdata_property('defaultvalue');
        if ('' . $defaultvalue !== '') {
            $key = array_search($defaultvalue, $this->get_field()->get_options());
            if ($key !== false) {
                return $key;
            }
        }
        return 0;
    }

    /**
     * Add fields for editing a textarea field.
     *
     * @param \MoodleQuickForm $mform
     */
    public function instance_form_definition(\MoodleQuickForm $mform) {
        
        $field = $this->get_field();
        $config = $field->get('configdata');
        $options = $field->get_options();
        $formattedoptions = array();
        $context = $this->get_field()->get_handler()->get_configuration_context();
        foreach ($options as $key => $option) {
            // Multilang formatting with filters.
            $formattedoptions[$key] = format_string($option, true, ['context' => $context]);
        }

        $elementname = $this->get_form_element_name();
        $course = $mform->addElement('autocomplete', $elementname, $this->get_field()->get_formatted_name(), $formattedoptions, 
                    ['placeholder' => get_string('select_courses', 'local_questionbank'),'onChange' => "require(['jquery'], function($){   $('#id_customfield_coursetopics').val('');  $('#id_customfield_coursetopics').trigger('change');  })"]);
        $course->setMultiple(true);
        if (($defaultkey = array_search($config['defaultvalue'], $options)) !== false) {
            $mform->setDefault($elementname, $defaultkey);
        }
        if ($field->get_configdata_property('required')) {
            $mform->addRule($elementname, null, 'required', null, 'client');
        }
        // $mform->addElement('hidden','qcategory', $id);
    }
    // public function get_form_element_name() : string {
    //     return 'course_competency';
    // }

    //     public function instance_form_before_set_data(\stdClass $instance) {
    //     print_r($instance); exit;
    //     $instance->{$this->get_form_element_name()} = $this->get_value();
    // }

    public function instance_form_save(\stdClass $datanew) {
        global $USER, $DB;

        $elementname = $this->get_form_element_name();
        if (!property_exists($datanew, $elementname)) {
            return;
        }
        $options = array_filter($datanew->$elementname);
        $value = implode(',', $options);
        foreach($options as $course){
            $courses = new stdClass;
            $courses->questionbankid = $datanew->category;
            $courses->questionid = $datanew->id;
            $courses->course = $course;
            $courses->usercreated = $USER->id;
            if($DB->record_exists('local_qb_questioncourses', 
                ['questionbankid' => $datanew->category, 
                 'questionid' => $datanew->id, 
                 'course' => $course])){
               continue;
            }
            $coursetopics->timecreated = time();
            $coursetopics->usercreated = $USER->id;
            $newid = $DB->insert_record('local_qb_questioncourses', $courses);
            $coursetopic = array_filter($datanew->customfield_coursetopics);
            $coursetopics = implode(',', $coursetopic);

            $topics = $DB->get_fieldset_sql('select id from {course_sections} as cs WHERE FIND_IN_SET(id, "'.$coursetopics.'") AND course='.$course.'');
            $topiclist = implode(',', $topics);
            $DB->update_record('local_qb_questioncourses', ['id' => $newid, 'topic' => $topiclist]);

        }

        $this->data->set($this->datafield(), $value);
        $this->data->set('value', $value);
        $this->save();


    }

    /**
     * Validates data for this field.
     *
     * @param array $data
     * @param array $files
     * @return array
     */
    public function instance_form_validation(array $data, array $files) : array {
        $errors = parent::instance_form_validation($data, $files);
        if ($this->get_field()->get_configdata_property('required')) {
            // Standard required rule does not work on select element.
            $elementname = $this->get_form_element_name();
            if (empty($data[$elementname])) {
                $errors[$elementname] = get_string('err_required', 'form');
            }
        }
        return $errors;
    }

    /**
     * Returns value in a human-readable format
     *
     * @return mixed|null value or null if empty
     */
    public function export_value() {
        $value = $this->get_value();

        if ($this->is_empty($value)) {
            return null;
        }

        $options = $this->get_field()->get_options();
        if (array_key_exists($value, $options)) {
            return format_string($options[$value], true,
                ['context' => $this->get_field()->get_handler()->get_configuration_context()]);
        }

        return null;
    }
}
