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

/**
 * LearnerScript
 * A Moodle block for creating customizable reports
 * @package blocks
 * @author: eAbyas Info Solutions
 * @date: 2017
 */
use block_learnerscript\local\componentbase;
class component_filters extends componentbase {
    public function init() {
        $this->plugins = true;
        $this->ordering = true;
        $this->form = false;
        $this->help = true;
    }
    public function validate_form_elements($data, $errors) {
        // if (!empty($data['size']) && !preg_match("/^\d+$/i", trim($data['size']))) {
        //     $errors['size'] = get_string('badsize', 'block_learnerscript');
        // }
        // return $errors;
    }
    public function form_process_data(&$cform) {
        global $DB;
        if ($this->form) {
            $data = $cform->get_data();
            // Function cr_serialize() will add slashes.
            $components = (new ls)->cr_unserialize($this->config->components);
            $components['filters']['config'] = $data;
            $this->config->components = (new ls)->cr_serialize($components);
            $DB->update_record('block_learnerscript', $this->config);
        }
    }

    public function form_set_data(&$cform) {
        if ($this->form) {
            $fdata = new stdclass;
            $components = (new ls)->cr_unserialize($this->config->components);
            $fdata = (isset($components['filters']['config'])) ? $components['filters']['config'] : $fdata;
            $cform->set_data($fdata);
        }
    }
}
