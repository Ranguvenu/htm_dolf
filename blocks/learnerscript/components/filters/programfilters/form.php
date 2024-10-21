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
 * LearnerScript Reports
 * A Moodle block for creating customizable reports
 * @package blocks
 * @author: eAbyas Info Solutions
 * @date: 2022
 */
if (!defined('MOODLE_INTERNAL')) {
    //  It must be included from a Moodle page.
    die(get_string('nodirectaccess','block_learnerscript'));
}

require_once($CFG->libdir . '/formslib.php');

class programfilters_form extends moodleform {

    public function definition() {
        global $DB, $USER, $CFG;

        $mform = & $this->_form;

        $mform->addElement('header', 'crformheader', get_string('programfilters', 'block_learnerscript'), '');

        $columns = $DB->get_columns('local_trainingprogram');

        $tpcolumns = array();
        foreach ($columns as $c) {
            $tpcolumns[$c->name] = ucfirst($c->name);
        }

        
        unset($tpcolumns['oldid']);
        unset($tpcolumns['price']);
        unset($tpcolumns['tax_free']);
        unset($tpcolumns['sellingprice']);
        unset($tpcolumns['actualprice']);
        unset($tpcolumns['program_agenda']);
        unset($tpcolumns['hour']);
        unset($tpcolumns['deleted']);
        unset($tpcolumns['attendancecmpltn']);
        unset($tpcolumns['attendancepercnt']);
        unset($tpcolumns['trainingtopics']);
        unset($tpcolumns['usercreated']);
        unset($tpcolumns['usermodified']);
        unset($tpcolumns['timecreated']);
        unset($tpcolumns['timemodified']);
        unset($tpcolumns['image']);
        unset($tpcolumns['deleted']);
        unset($tpcolumns['preexam']);
        unset($tpcolumns['postexam']);
        unset($tpcolumns['trainingtopics']);
        unset($tpcolumns['prerequirementsprograms']);
        unset($tpcolumns['postrequirementsprograms']);
        unset($tpcolumns['published']);
        unset($tpcolumns['duration']);

        $mform->addElement('select', 'column', get_string('column', 'block_learnerscript'), $tpcolumns);

        $this->_customdata['compclass']->add_form_elements($mform, $this);

        // Buttons.
        $this->add_action_buttons(true, get_string('add'));
    }

    public function validation($data, $files) {
        $errors = parent::validation($data, $files);
        $errors = $this->_customdata['compclass']->validate_form_elements($data, $errors);
        return $errors;
    }

    public function advanced_columns() {
        global $DB;
        $columns = $DB->get_columns('local_trainingprogram');

        $tpcolumns = array();
        foreach ($columns as $c) {
            $tpcolumns[$c->name] = ucfirst($c->name);
        }

        unset($tpcolumns['sectors']);
        unset($tpcolumns['image']);
        unset($tpcolumns['deleted']);
        unset($tpcolumns['preexam']);
        unset($tpcolumns['postexam']);
        unset($tpcolumns['trainingtopics']);
        unset($tpcolumns['prerequirementsprograms']);
        unset($tpcolumns['postrequirementsprograms']);
        unset($tpcolumns['published']);
        unset($tpcolumns['duration']);

        return $tpcolumns;
    }

}
