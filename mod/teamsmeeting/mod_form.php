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
 * Teams Meeting configuration form
 *
 * @package    mod_teamsmeeting
 * @copyright  2022 eAbyas Info Solutions Pvt Ltd (www.eabyas.com)
 * @author     Ranga Reddy<rangareddy@eabyas.com>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die();

require_once($CFG->dirroot.'/course/moodleform_mod.php');
require_once($CFG->dirroot.'/mod/teamsmeeting/lib.php');

 class mod_teamsmeeting_mod_form extends moodleform_mod{

    public function definition(){
        global $CFG, $DB, $PAGE, $USER;

        $mform = $this->_form;
        $mform->addElement('header', 'general', get_string('general', 'form'));

        $mform->addElement('date_time_selector', 'start_time', get_string('meeting_start_date_time', 'teamsmeeting'));
        $mform->addRule('start_time', get_string('missing_meeting_start_date_time', 'teamsmeeting'), 'required');

        $mform->addElement('duration', 'duration', get_string('duration', 'teamsmeeting'),['units'=> [MINSECS]]);
        $mform->addRule('duration', get_string('missing_duration', 'teamsmeeting'), 'required');
        
        $mform->addElement('text', 'name', get_string('title', 'teamsmeeting'));

        $mform->addElement('checkbox', 'isrecuring', get_string('isrecuring', 'teamsmeeting'));
        $mform->addElement('date_selector', 'occurs_until', get_string('occurs_until', 'teamsmeeting'));
        $mform->hideIf('occurs_until', 'isrecuring');

        $this->standard_coursemodule_elements();
        $this->add_action_buttons();
   }

   function validation($data, $files) {
      global $DB;
      $errors = parent::validation($data, $files);
   
      if(!empty($data['isrecuring']) && date("Y-m-d",$data['occurs_until']) < date("Y-m-d",$data['start_time'])) {
         $errors['occurs_until'] = get_string('occursuntilerror', 'teamsmeeting');
      }
    return $errors;
  }
 }
