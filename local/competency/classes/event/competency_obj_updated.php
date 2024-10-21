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
 * The EVENTNAME event.
 *
 * @package    local_competency
 * @copyright  Eabyas
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
namespace local_competency\event;
defined('MOODLE_INTERNAL') || die();

class competency_obj_updated extends \core\event\base {
    protected function init() {
        $this->data['crud'] = 'u'; // c(reate), r(ead), u(pdate), d(elete)
        $this->data['edulevel'] = self::LEVEL_PARTICIPATING;
        $this->data['objecttable'] = 'local_competencypc_obj';
    }

    public static function get_name() {
        return get_string('eventcompetencyobjupdation', 'local_competency');
    }

    public function get_description() {
        return "The user with id {$this->userid} Competency Mapping objective Updated  with id {$this->objectid}.";
    }
    // public function get_url() {
    //     global $DB;
    //     //$project_name=$DB->get_field_sql("SELECT name FROM {local_projects} where id=$this->objectid");
    //     $url = new \moodle_url('/local/competency/index.php');
    //     return $url;
    // }

}
