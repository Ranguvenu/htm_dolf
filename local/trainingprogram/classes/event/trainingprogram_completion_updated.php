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
 * training program completion event.
 *
 * @package    local_trainingprogram
 * @copyright  2022 e abyas  <info@eabyas.com>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace local_trainingprogram\event;

defined('MOODLE_INTERNAL') || die();

class trainingprogram_completion_updated extends \core\event\base {

    /**
     * Initialise required event data properties.
     */
    protected function init() {
        $this->data['objecttable'] = 'trainingprogram_completion';
        $this->data['crud'] = 'u';
        $this->data['edulevel'] = self::LEVEL_PARTICIPATING;
    }

    /**
     * Returns localised event name.
     *
     * @return string
     */
    public static function get_name() {
        return get_string('eventtrainingprogramcompletionupdated', 'local_trainingprogram');
    }

    /**
     * Returns non-localised event description with id's for admin use only.
     *
     * @return string
     */
    public function get_description() {

        return "The user with id '{$this->userid}' updated the completion state to '{$this->other['completion_status']}' ".
                "for the training program with id '{$this->other['programid']}' for the user with id '{$this->relateduserid}'.";
       
    }

    /**
     * Returns relevant URL.
     *
     * @return \moodle_url
     */
    public function get_url() {
        return new \moodle_url('/course/view.php', array('id' => $this->courseid));
    }

    /**
     * Return name of the legacy event, which is replaced by this event.
     *
     * @return string legacy event name
     */
    public static function get_legacy_eventname() {
        return 'trainingprogram_completion_changed';
    }

    /**
     * Return training program completion legacy event data.
     *
     * @return \stdClass completion data.
     */
    protected function get_legacy_eventdata() {
        return $this->get_record_snapshot('program_completions', $this->objectid);
    }

    /**
     * Custom validation.
     *
     * @throws \coding_exception in case of a problem.
     */
    protected function validate_data() {
        parent::validate_data();

        if (!isset($this->relateduserid)) {
            throw new \coding_exception('The \'relateduserid\' must be set.');
        }
    }

    public static function get_objectid_mapping() {
        // Sorry mapping info is not available for trainin gprogram completion records.
        return array('db' => 'program_completions', 'restore' => base::NOT_MAPPED);
    }

    public static function get_other_mapping() {
        $othermapped = array();
        $othermapped['relateduserid'] = array('db' => 'user', 'restore' => 'user');
        $othermapped['overrideby'] = array('db' => 'user', 'restore' => 'user');

        return $othermapped;
    }
}
