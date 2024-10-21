<?php
// This file is part of Moodle - http://moodle.org/
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
 * @package    local_userapproval
 * @copyright  2022 eAbyas Info Solutions<info@eabyas.com>
 * @author     Vinod Kumar  <vinod.p@eabyas.com>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
use core_component;
if (!defined('MOODLE_INTERNAL')) {
    die('Direct access to this script is forbidden.');
}
require_once($CFG->libdir.'/formslib.php');
class filters_form extends moodleform {
    function definition() {
        global $CFG;
        $mform    = $this->_form;
        $filterlist        = $this->_customdata['filterlist']; // this contains the data of this form
        $filterparams      = $this->_customdata['filterparams'];
        $action      = $this->_customdata['action'];
        $options           = $filterparams['options'];
        $dataoptions       = $filterparams['dataoptions'];
        $submitid = $this->_customdata['submitid'] ? $this->_customdata['submitid'] : 'filteringform';
        $this->_form->_attributes['id'] = $submitid;
        $mform->addElement('hidden', 'options', $options);
        $mform->setType('options', PARAM_RAW);
        $mform->addElement('hidden', 'dataoptions', $dataoptions);
        $mform->setType('dataoptions', PARAM_RAW);     
        foreach ($filterlist as $key => $value) {
            if($value === 'email' || $value === 'status' || $value === 'deletedusers'){
                $filter = 'userapproval';
            } else {
                $filter = $value;
            }
            $core_component = new \core_component();
            $courses_plugin_exist = $core_component::get_plugin_directory('local', $filter);
            if ($courses_plugin_exist) {
                require_once($CFG->dirroot . '/local/' . $filter . '/lib.php');
                $functionname = $value.'_filter';
                $functionname($mform);
            }
        }
        $buttonarray = array();
        $applyclassarray = array('class' => 'form-submit','onclick' => '(function(e){ require("theme_academy/cardPaginate").filteringData(e,"'.$submitid.'") })(event)');
        $cancelclassarray = array('class' => 'form-submit','onclick' => '(function(e){ require("theme_academy/cardPaginate").resetingData(e,"'.$submitid.'") })(event)');
        $buttonarray[] = &$mform->createElement('button', 'filter_apply', get_string('apply','local_userapproval'), $applyclassarray);
        $buttonarray[] = &$mform->createElement('button', 'cancel', get_string('reset','local_userapproval'), $cancelclassarray);
        
        $mform->addGroup($buttonarray, 'buttonar', '', array(' '), false);
        $mform->disable_form_change_checker();        
    }
    function validation($data, $files) {
        global $DB;
        $errors = parent::validation($data, $files);
        return $errors;
    }
}