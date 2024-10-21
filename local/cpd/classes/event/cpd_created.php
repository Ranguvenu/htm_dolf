<?php
/**
 * This file is part of eAbyas
 *
 * Copyright eAbyas Info Solutons Pvt Ltd, India
 *
 * This program is free software; you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation; either version 3 of the License, or
 * (at your option) any later version.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with this program.  If not, see <http://www.gnu.org/licenses/>.
 *
 * @author eabyas  <info@eabyas.in>
 * @package Bizlms 
 * @subpackage local_projects
 */
namespace local_cpd\event;
defined('MOODLE_INTERNAL') || die();

class cpd_created extends \core\event\base {
    protected function init() {
        $this->data['crud'] = 'c'; // c(reate), r(ead), u(pdate), d(elete)
        $this->data['edulevel'] = self::LEVEL_PARTICIPATING;
        $this->data['objecttable'] = 'local_cpd';
    }

    public static function get_name() {
        return get_string('cpd_created', 'local_cpd');
    }

    public function get_description() {
        return "The user with id {$this->userid} added the cpd with CPD id {$this->objectid}.";
    }
     public function get_url() {
            $url = new \moodle_url('/local/cpd/index.php');
            return $url;
        }

    // public function get_legacy_logdata() {
    //     // Override if you are migrating an add_to_log() call.
    //     return array($this->courseid, 'local_course', 'LOGACTION',
    //         '...........',
    //         $this->objectid, $this->contextinstanceid);
    // }

    // public static function get_legacy_eventname() {
    //     // Override ONLY if you are migrating events_trigger() call.
    //     return 'MYPLUGIN_OLD_EVENT_NAME';
    // }

    // protected function get_legacy_eventdata() {
    //     // Override if you migrating events_trigger() call.
    //     $data = new \stdClass();
    //     $data->id = $this->objectid;
    //     $data->userid = $this->relateduserid;
    //     return $data;
    // }
    
}
