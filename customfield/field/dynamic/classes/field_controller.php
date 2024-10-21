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


namespace customfield_dynamic;

defined('MOODLE_INTERNAL') || die;

class field_controller extends \core_customfield\field_controller {
    /**
     * Customfield type
     */
    const TYPE = 'dynamic';

    /**
     * Add fields for editing a dynamic field.
     *
     * @param \MoodleQuickForm $mform
     */
    public function config_form_definition(\MoodleQuickForm $mform) {
        global $DB;

    }

    /**
     * Returns the options available as an array.
     *
     * @param \core_customfield\field_controller $field
     * @return array
     */
    public static function get_options_array(\core_customfield\field_controller $field) : array {
        global $DB;
        // if ($field->get_configdata_property('dynamicsql')) {
        //     $resultset = $DB->get_records_sql($field->get_configdata_property('dynamicsql'));
        //     $options = array();
        //     foreach ($resultset as $key => $option) {
        //         $options[format_string($key)] = format_string($option->data);// Multilang formatting.
        //     }
        // } else {
        //     $options = array();
        // }
        // return array('' => get_string('choose')) + $options;
    }

    /**
     * Validate the data from the config form.
     * Sub classes must reimplement it.
     *
     * @param array $data from the add/edit profile field form
     * @param array $files
     * @return array associative array of error messages
     */
    public function config_form_validation(array $data, $files = array()) : array {
        global $DB;
        $err = array();
        
        return $err;
    }
}
