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


namespace customfield_coursetopics;

defined('MOODLE_INTERNAL') || die;
use stdClass;
use question_bank;

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

        global $DB;

        $categoryid = optional_param('category', 0, PARAM_INT);

        $questionid = optional_param('id', 0 , PARAM_INT);
        if(!$questionid){
            $id = optional_param('qcategory', 0 , PARAM_INT);
            
            if(!$id){
                $id = optional_param('category', 0 , PARAM_INT);
            }
        }else{
            $questioninfo = question_bank::load_question($questionid);

            $id = $questioninfo->category;
        }

        $questionbankid = $DB->get_field('local_questionbank','id',array('qcategoryid'=>$id));

        if(!$questionbankid){

            $questionbankid=0;
        }


        
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

        $options = array(
            'ajax' => 'local_questionbank/coursetopics',
            'data-type' => 'topicslist',
            'id' => 'id_customfield_coursetopics',
            'data-questionbankid' => $questionbankid,
            'class' => 'topics',
            'multiple' => true,
            'placeholder' => get_string('topics', 'local_questionbank')
        );

        $coursetopicarray=array();

        $topicids =     $this->get_value();

        if($topicids != 0 && $topicids != '' && $topicids != 'data-topics'){
            $topics = $DB->get_records_sql_menu("select id,(CASE WHEN name IS NULL THEN CONCAT('Topic',section) ELSE name END) as fullname from {course_sections} where id in($topicids)");
            $coursetopicarray = $topics;
        }


        $course = $mform->addElement('autocomplete', $elementname, $this->get_field()->get_formatted_name(),$coursetopicarray, $options);
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

    public function instance_form_before_set_data(\stdClass $instance) {
        global $DB;
        $topicids =     $this->get_value();
        if($topicids != 0 && $topicids != '' && $topicids != 'data-topics'){
            $topics = $DB->get_records_sql_menu("select id,(CASE WHEN name IS NULL THEN CONCAT('Topic',section) ELSE name END) as fullname from {course_sections} where id in($topicids)");
             $instance->{$this->get_form_element_name()} = $topics;
        }
       
    }

    public function instance_form_save(\stdClass $datanew) {
        global $USER, $DB;
        $elementname = $this->get_form_element_name();
        if (!property_exists($datanew, $elementname)) {
            return;
        }
        $options = array_filter($datanew->$elementname);
        $value = implode(',', $options);

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
